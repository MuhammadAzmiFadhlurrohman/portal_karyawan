<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFcmToken;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected ?string $projectId;
    protected string $credentialsPath;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id');
        $this->credentialsPath = config('services.fcm.credentials_path', storage_path('app/fcm-service-account.json'));
    }

    /**
     * Check if FCM is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->projectId) && file_exists($this->credentialsPath);
    }

    /**
     * Generate or fetch cached OAuth 2.0 Access Token using Service Account JSON credentials.
     */
    public function getAccessToken(): ?string
    {
        if (! file_exists($this->credentialsPath)) {
            Log::debug('FCM Service Account JSON not found at: ' . $this->credentialsPath);
            return null;
        }

        return cache()->remember('fcm_v1_access_token', 3300, function () {
            try {
                $json = file_get_contents($this->credentialsPath);
                $key = json_decode($json, true);

                if (! isset($key['client_email'], $key['private_key'])) {
                    Log::warning('FCM credentials file is invalid.');
                    return null;
                }

                $now = time();
                $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
                $claims = rtrim(strtr(base64_encode(json_encode([
                    'iss' => $key['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'exp' => $now + 3600,
                    'iat' => $now,
                ])), '+/', '-_'), '=');

                $signInput = "{$header}.{$claims}";
                $binarySignature = '';
                if (! openssl_sign($signInput, $binarySignature, $key['private_key'], OPENSSL_ALGO_SHA256)) {
                    Log::warning('Failed to sign FCM JWT with private key.');
                    return null;
                }

                $signature = rtrim(strtr(base64_encode($binarySignature), '+/', '-_'), '=');
                $jwt = "{$signInput}.{$signature}";

                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::warning('FCM OAuth Token Request Failed: ' . $response->body());
                return null;
            } catch (\Throwable $e) {
                Log::warning('Error obtaining FCM OAuth token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Register or update a device token for a user.
     */
    public function registerToken(User $user, string $token, string $platform = 'web', ?string $deviceName = null): UserFcmToken
    {
        $tokenHash = hash('sha256', $token);

        // Prune older tokens for this user on the same platform to avoid duplicate pushes
        UserFcmToken::where('user_id', $user->id)
            ->where('platform', $platform)
            ->where('token_hash', '!=', $tokenHash)
            ->delete();

        return UserFcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token_hash' => $tokenHash,
            ],
            [
                'fcm_token' => $token,
                'platform' => $platform,
                'device_name' => $deviceName ?: ('Browser / ' . ucfirst($platform)),
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Remove a device token (e.g. on logout or permission revoked).
     */
    public function removeToken(User $user, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        return (bool) UserFcmToken::where('user_id', $user->id)->where('token_hash', $tokenHash)->delete();
    }

    /**
     * Get user notification preferences.
     */
    public function getUserPreferences(User $user): UserNotificationPreference
    {
        return UserNotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notify_pengajuan' => true,
                'notify_lembur' => true,
                'notify_tiket' => true,
            ]
        );
    }

    /**
     * Update user notification preferences.
     */
    public function updatePreferences(User $user, array $attributes): UserNotificationPreference
    {
        $pref = $this->getUserPreferences($user);
        $pref->update([
            'notify_pengajuan' => (bool) ($attributes['notify_pengajuan'] ?? $pref->notify_pengajuan),
            'notify_lembur' => (bool) ($attributes['notify_lembur'] ?? $pref->notify_lembur),
            'notify_tiket' => (bool) ($attributes['notify_tiket'] ?? $pref->notify_tiket),
        ]);

        return $pref;
    }

    /**
     * Send push notification to target users (with preference filtering and dead token removal).
     *
     * @param array<int>|int $userIds
     * @param string $eventType 'pengajuan'|'lembur'|'tiket'
     */
    public function sendToUsers(
        array|int $userIds,
        string $eventType,
        string $title,
        string $body,
        string $deepLink,
        array $extraData = []
    ): int {
        $ids = array_values(array_unique(array_filter((array) $userIds)));
        if (empty($ids)) {
            return 0;
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken || empty($this->projectId)) {
            // Still log audit of the intent so systems can track
            Log::info("FCM Notification Skipped (FCM not configured) for event [{$eventType}]: '{$title}' to users: " . implode(',', $ids));
            return 0;
        }

        // 1. Filter out users who turned off this event preference
        $prefColumn = "notify_{$eventType}";
        $allowedUserIds = DB::table('users')
            ->leftJoin('user_notification_preferences', 'users.id', '=', 'user_notification_preferences.user_id')
            ->whereIn('users.id', $ids)
            ->where(function ($q) use ($prefColumn) {
                $q->whereNull("user_notification_preferences.{$prefColumn}")
                  ->orWhere("user_notification_preferences.{$prefColumn}", true);
            })
            ->pluck('users.id')
            ->toArray();

        if (empty($allowedUserIds)) {
            return 0;
        }

        // 2. Fetch latest active device token per user and platform to eliminate duplicates
        $tokens = UserFcmToken::whereIn('user_id', $allowedUserIds)
            ->orderByDesc('last_used_at')
            ->get()
            ->unique(fn ($t) => $t->user_id . '-' . $t->platform);

        if ($tokens->isEmpty()) {
            return 0;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $sentCount = 0;

        foreach ($tokens as $tokenRecord) {
            $fcmToken = $tokenRecord->fcm_token;
            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge([
                        'title' => (string) $title,
                        'body' => (string) $body,
                        'type' => (string) $eventType,
                        'deep_link' => (string) $deepLink,
                        'click_action' => (string) $deepLink,
                    ], array_map('strval', $extraData)),
                    'webpush' => [
                        'notification' => [
                            'tag' => (string) ($extraData['tag'] ?? ($eventType . '-' . ($extraData['reference_id'] ?? time()))),
                            'icon' => url('/assets/logo/icon-192.png'),
                            'badge' => url('/assets/logo/favicon-32x32.png'),
                        ],
                        'fcm_options' => [
                            'link' => (string) $deepLink,
                        ],
                        'headers' => [
                            'Urgency' => 'high',
                        ],
                    ],
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'click_action' => (string) $deepLink,
                            'sound' => 'default',
                            'channel_id' => 'msn_urgent_channel',
                        ],
                    ],
                ],
            ];

            try {
                $response = Http::withToken($accessToken)
                    ->timeout(10)
                    ->contentType('application/json')
                    ->post($endpoint, $payload);

                if ($response->successful()) {
                    $sentCount++;
                    $tokenRecord->update(['last_used_at' => now()]);
                } else {
                    $statusCode = $response->status();
                    $respBody = $response->body();

                    // Dead / expired token cleanup
                    if ($statusCode === 404 || str_contains($respBody, 'UNREGISTERED') || str_contains($respBody, 'INVALID_ARGUMENT')) {
                        $tokenRecord->delete();
                        Log::info("FCM Dead Token pruned: ID {$tokenRecord->id} for user {$tokenRecord->user_id}");
                    } else {
                        Log::warning("FCM Send Error ({$statusCode}): {$respBody}");
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("FCM Connection Error for token {$tokenRecord->id}: " . $e->getMessage());
            }
        }

        return $sentCount;
    }
}
