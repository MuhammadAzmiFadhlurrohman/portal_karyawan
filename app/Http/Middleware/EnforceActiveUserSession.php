<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use App\Services\SystemSettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceActiveUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'identifier' => 'Akun Anda sedang dinonaktifkan. Hubungi HRD/Admin.',
            ]);
        }

        // Masa aktif login: 3 Bulan (90 Hari = 129.600 menit) kecuali logout manual
        $maxSessionMinutes = max(129600, (int) SystemSettingService::get('security.session_idle_timeout_minutes', 129600));

        $sessionId = $request->session()->getId();
        $userSession = UserSession::query()
            ->where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if ($userSession) {
            if ($userSession->revoked_at !== null) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'identifier' => 'Sesi Anda telah dihentikan: '.($userSession->revoke_reason ?: 'Keamanan Sesi'),
                ]);
            }

            $loginStart = $userSession->created_at ?? $userSession->last_activity_at;
            if ($loginStart && $loginStart->diffInMinutes(now()) > $maxSessionMinutes) {
                $userSession->update([
                    'revoked_at' => now(),
                    'revoke_reason' => 'Masa aktif login 3 bulan telah berakhir.',
                ]);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'identifier' => 'Masa aktif login 3 bulan Anda telah berakhir. Silakan login kembali.',
                ]);
            }

            $userSession->update(['last_activity_at' => now()]);
        } else {
            // Otomatis rekam sesi & perangkat untuk seluruh aktivitas pengguna yang login
            $deviceSessionService = app(\App\Services\DeviceSessionService::class);
            $device = $deviceSessionService->resolveAndValidateDevice($user, $request);
            $userSession = $deviceSessionService->recordSession($user, $request, $device);
        }

        if (
            $user->must_change_password
            && ! $request->routeIs('password.first-change', 'password.first-change.update', 'logout')
        ) {
            return redirect()->route('password.first-change');
        }

        return $next($request);
    }
}
