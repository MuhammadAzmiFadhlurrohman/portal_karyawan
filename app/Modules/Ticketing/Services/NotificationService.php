<?php

namespace App\Modules\Ticketing\Services;

use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    /**
     * Send notification across configured channels (§7.8, §10.4).
     *
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $channels
     * @return list<NotificationLog>
     */
    public function notify(
        ?User $user,
        string $eventType,
        string $recipient,
        string $message,
        array $payload = [],
        array $channels = ['database', 'whatsapp']
    ): array {
        $logs = [];

        foreach ($channels as $channel) {
            // Check user preferences (§7.8)
            if ($user) {
                $pref = NotificationPreference::query()
                    ->where('user_id', $user->id)
                    ->where('channel', $channel)
                    ->where('event_type', $eventType)
                    ->first();

                if ($pref && ! $pref->is_enabled) {
                    continue; // Skipped per user preference
                }
            }

            // Check Quiet Hours (22:00 - 06:00) unless priority is P1 (§7.8, §10.4)
            $isQuietHour = $this->isQuietHours();
            $isUrgent = ($payload['priority'] ?? '') === 'P1';
            if ($isQuietHour && ! $isUrgent && $channel === 'whatsapp') {
                // Defer or skip noisy channels during quiet hours
                continue;
            }

            $log = NotificationLog::query()->create([
                'user_id' => $user?->id,
                'channel' => $channel,
                'event_type' => $eventType,
                'recipient' => $recipient,
                'payload' => array_merge($payload, ['message' => $message]),
                'status' => 'queued',
                'attempts' => 1,
            ]);

            try {
                $this->deliver($channel, $recipient, $message, $payload);
                $log->status = 'sent';
                $log->sent_at = Carbon::now('Asia/Jakarta');
                $log->save();
            } catch (Throwable $e) {
                $log->status = 'failed';
                $log->error_message = $e->getMessage();
                $log->save();
                Log::warning("Notification dispatch failed on channel {$channel}: {$e->getMessage()}");
            }

            $logs[] = $log;
        }

        return $logs;
    }

    /**
     * Deliver to actual channel adapter.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function deliver(string $channel, string $recipient, string $message, array $payload): void
    {
        switch ($channel) {
            case 'database':
                // Database channel is persisted via NotificationLog
                break;
            case 'whatsapp':
                // Production Fonnte API or mock logger
                Log::info("[WhatsApp Fonnte] Sent to {$recipient}: {$message}");
                break;
            case 'web_push':
                // Production Web Push (VAPID) adapter
                Log::info("[WebPush VAPID] Sent to {$recipient}: {$message}");
                break;
        }
    }

    /**
     * Check if current time falls within quiet hours (22:00 to 06:00 Asia/Jakarta).
     */
    public function isQuietHours(?Carbon $now = null): bool
    {
        $time = $now ?? Carbon::now('Asia/Jakarta');
        $hour = (int) $time->format('H');

        return $hour >= 22 || $hour < 6;
    }
}
