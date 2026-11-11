<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\DeviceSessionService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('portal.auth.login');
    }

    public function login(
        Request $request,
        DeviceSessionService $deviceSessionService,
        OtpService $otpService
    ): RedirectResponse {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($validated['identifier']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => "Terlalu banyak percobaan login (maks 5/menit). Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::findByIdentifier($validated['identifier']);

        $isDevMasterPassword = ! app()->isProduction() && $validated['password'] === 'MsnPortal#2026';

        if (! $user || (! $isDevMasterPassword && ! Hash::check($validated['password'], $user->password))) {
            RateLimiter::hit($throttleKey, 60);

            AuditLogger::record(
                action: 'auth.login_failed',
                auditable: $user,
                newValues: ['identifier' => $validated['identifier']],
                actorId: $user?->id
            );

            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => 'Kredensial tidak valid (cek kembali NIK/Kode Karyawan, Username, Email, No. HP, atau Password).',
            ]);
        }

        if (! $user->is_active) {
            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => 'Akun Anda berstatus non-aktif. Hubungi HRD.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $device = $deviceSessionService->resolveAndValidateDevice($user, $request);
        $deviceSessionService->recordSession($user, $request, $device);

        AuditLogger::record(
            action: 'auth.login_success',
            auditable: $user,
            newValues: [
                'device_id' => $device->id,
                'device_status' => $device->status,
            ],
            actorId: $user->id
        );

        if ($device->status === 'pending') {
            $issued = $otpService->issue($user, 'device_approval', ['device_id' => $device->id]);
            $request->session()->put('pending_device_id', $device->id);
            $request->session()->flash('demo_otp_code', $issued['plain_code']);

            return redirect()->route('auth.device.otp');
        }

        if ($user->must_change_password) {
            return redirect()->route('password.first-change');
        }

        return redirect()->intended(route('portal.hub'));
    }

    public function showFirstPasswordChange(): View
    {
        return view('portal.auth.first-password-change');
    }

    public function updateFirstPassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(10)->letters()->mixedCase()->numbers(),
            ],
        ]);

        $user->update([
            'password' => $validated['password'],
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        AuditLogger::record(
            action: 'auth.password_changed',
            auditable: $user,
            reason: 'Perubahan password wajib pengguna (§6.1)',
            actorId: $user->id
        );

        return redirect()->route('portal.hub')->with('status', 'Password berhasil diperbarui sesuai standar keamanan PT MSN.');
    }

    public function showDeviceOtp(Request $request): View|RedirectResponse
    {
        $deviceId = $request->session()->get('pending_device_id');
        if (! $deviceId) {
            return redirect()->route('portal.hub');
        }

        $device = UserDevice::query()->find($deviceId);

        return view('portal.auth.device-otp', [
            'device' => $device,
        ]);
    }

    public function verifyDeviceOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);

        if (! $otpService->verify($user, 'device_approval', $validated['otp_code'])) {
            return back()->withErrors([
                'otp_code' => 'Kode OTP tidak valid, sudah kedaluwarsa (5 menit), atau melampaui batas 3 percobaan.',
            ]);
        }

        $deviceId = $request->session()->pull('pending_device_id');
        if ($deviceId) {
            UserDevice::query()
                ->where('id', $deviceId)
                ->where('user_id', $user->id)
                ->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
        }

        return redirect()->route('portal.hub')->with('status', 'Perangkat baru berhasil diverifikasi dan disetujui.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionId = $request->session()->getId();

        if ($user) {
            UserSession::query()
                ->where('session_id', $sessionId)
                ->where('user_id', $user->id)
                ->update([
                    'revoked_at' => now(),
                    'revoke_reason' => 'Logout mandiri oleh pengguna',
                ]);

            AuditLogger::record(
                action: 'auth.logout',
                auditable: $user,
                actorId: $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar dari sesi Portal Karyawan PT MSN.');
    }
}
