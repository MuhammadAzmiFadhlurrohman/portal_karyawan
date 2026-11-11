<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceSessionService
{
    /**
     * Register or validate user device (max 2 approved devices per user, §5.1).
     */
    public function resolveAndValidateDevice(User $user, Request $request): UserDevice
    {
        $fingerprint = $request->input('device_fingerprint')
            ?: hash('sha256', ($request->userAgent() ?? 'unknown').'|'.($request->header('Accept-Language') ?? 'id'));

        $deviceName = $request->input('device_name')
            ?: $this->parseDeviceName((string) $request->userAgent());

        $platform = $request->input('platform', 'web');

        $existing = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if ($existing) {
            $existing->update([
                'last_used_at' => now(),
                'last_ip' => $request->ip(),
            ]);

            return $existing;
        }

        $maxDevices = (int) SystemSettingService::get('security.max_devices_per_user', 2);

        $approvedCount = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $status = $approvedCount < $maxDevices ? 'approved' : 'pending';

        $device = UserDevice::query()->create([
            'user_id' => $user->id,
            'device_fingerprint' => $fingerprint,
            'device_name' => $deviceName,
            'platform' => $platform,
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
            'last_used_at' => now(),
            'last_ip' => $request->ip(),
        ]);

        AuditLogger::record(
            action: $status === 'approved' ? 'device.registered_auto' : 'device.registered_pending_approval',
            auditable: $device,
            newValues: [
                'device_name' => $deviceName,
                'platform' => $platform,
                'status' => $status,
                'approved_count' => $approvedCount,
            ],
            actorId: $user->id
        );

        return $device;
    }

    /**
     * Track an active user session in `user_sessions` (§6.1).
     */
    public function recordSession(User $user, Request $request, ?UserDevice $device = null): UserSession
    {
        $sessionId = $request->session()->getId();

        return UserSession::query()->updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'device_id' => $device?->id,
                'device_name' => $device ? $device->device_name : $this->parseDeviceName((string) $request->userAgent()),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity_at' => now(),
                'revoked_at' => null,
                'revoked_by' => null,
                'revoke_reason' => null,
            ]
        );
    }

    /**
     * Force logout a specific session (§6.1).
     */
    public function revokeSession(UserSession $userSession, string $reason, ?int $actorId = null): void
    {
        $userSession->update([
            'revoked_at' => now(),
            'revoked_by' => $actorId,
            'revoke_reason' => $reason,
        ]);

        DB::table('sessions')->where('id', $userSession->session_id)->delete();

        AuditLogger::record(
            action: 'session.force_logout',
            auditable: $userSession,
            newValues: [
                'target_user_id' => $userSession->user_id,
                'device_name' => $userSession->device_name,
            ],
            reason: $reason,
            actorId: $actorId
        );
    }

    /**
     * Force logout all active sessions of a user (§6.1).
     */
    public function revokeAllUserSessions(User $targetUser, string $reason, ?int $actorId = null): int
    {
        $activeSessions = UserSession::query()
            ->where('user_id', $targetUser->id)
            ->whereNull('revoked_at')
            ->get();

        foreach ($activeSessions as $session) {
            $this->revokeSession($session, $reason, $actorId);
        }

        DB::table('sessions')->where('user_id', $targetUser->id)->delete();

        return $activeSessions->count();
    }

    private function parseDeviceName(string $userAgent): string
    {
        $os = 'Perangkat Web';
        if (stripos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        } elseif (stripos($userAgent, 'Windows') !== false) {
            $os = 'Windows PC';
        } elseif (stripos($userAgent, 'Macintosh') !== false) {
            $os = 'Mac';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (stripos($userAgent, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'Chrome/') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox/') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari/') !== false) {
            $browser = 'Safari';
        }

        return "{$browser} ({$os})";
    }
}
