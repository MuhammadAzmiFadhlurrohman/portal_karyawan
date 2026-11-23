<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\FcmService;
use Illuminate\Console\Command;

class SendTestFcmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:test {user? : User ID or email to send the test notification to} {--title= : Custom notification title} {--body= : Custom notification body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate FCM tokens and send a test push notification via FCM HTTP v1';

    /**
     * Execute the console command.
     */
    public function handle(FcmService $fcmService): int
    {
        $this->info('== Test & Cleanup FCM HTTP v1 Push Notification ==');

        if (! $fcmService->isConfigured()) {
            $this->error('FCM is not configured properly. Check FCM_PROJECT_ID and credentials file.');
            return Command::FAILURE;
        }

        // Automatically prune duplicate tokens per user in database
        $allUserIds = UserFcmToken::distinct()->pluck('user_id');
        $prunedCount = 0;
        foreach ($allUserIds as $uid) {
            $latestId = UserFcmToken::where('user_id', $uid)
                ->orderByDesc('last_used_at')
                ->orderByDesc('id')
                ->value('id');
            if ($latestId) {
                $prunedCount += UserFcmToken::where('user_id', $uid)->where('id', '!=', $latestId)->delete();
            }
        }
        if ($prunedCount > 0) {
            $this->info("Cleaned up {$prunedCount} duplicate/old FCM token(s) from database.");
        }

        $userInput = $this->argument('user');

        if ($userInput) {
            $user = is_numeric($userInput)
                ? User::find($userInput)
                : User::where('email', $userInput)->orWhere('username', $userInput)->first();

            if (! $user) {
                $this->error("User '{$userInput}' not found.");
                return Command::FAILURE;
            }
            $users = collect([$user]);
        } else {
            $userIdsWithTokens = UserFcmToken::distinct()->pluck('user_id');
            if ($userIdsWithTokens->isEmpty()) {
                $this->warn('No active device tokens found in the user_fcm_tokens table.');
                $this->line('To register a device token, open the web portal in your browser and allow notifications.');
                return Command::SUCCESS;
            }
            $users = User::whereIn('id', $userIdsWithTokens)->get();
        }

        $this->info("Found {$users->count()} target user(s).");

        foreach ($users as $user) {
            $tokenCount = $user->fcmTokens()->count();
            $this->line("- User: {$user->name} ({$user->email}) [{$tokenCount} active device token]");

            $title = $this->option('title') ?: '🔔 Tes Push Notification GoMSN';
            $body = $this->option('body') ?: 'Notifikasi FCM berhasil terhubung (1 notifikasi bersih)!';

            $sentCount = $fcmService->sendToUsers(
                $user->id,
                'absen',
                $title,
                $body,
                url('/'),
                ['test' => '1', 'tag' => 'fcm-test-' . $user->id . '-' . time()]
            );

            $this->info("  Result: Sent to {$sentCount} device(s).");
        }

        $this->info('Test completed successfully.');
        return Command::SUCCESS;
    }
}
