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
                    'identifier' => 'Sesi Anda telah dihentikan oleh Administrator: '.($userSession->revoke_reason ?: 'Keamanan Sesi'),
                ]);
            }

            $idleTimeoutMinutes = (int) SystemSettingService::get('security.session_idle_timeout_minutes', 120);
            if ($userSession->last_activity_at && $userSession->last_activity_at->diffInMinutes(now()) > $idleTimeoutMinutes) {
                $userSession->update([
                    'revoked_at' => now(),
                    'revoke_reason' => 'Sesi berakhir otomatis karena melewati batas idle ('.$idleTimeoutMinutes.' menit).',
                ]);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'identifier' => 'Sesi Anda telah berakhir karena tidak ada aktivitas.',
                ]);
            }

            $userSession->update(['last_activity_at' => now()]);
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
