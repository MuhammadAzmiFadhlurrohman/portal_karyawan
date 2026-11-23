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
    protected $signature = 'fcm:test {user? : User ID or email to send the test notification to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test push notification via FCM HTTP v1 to registered user devices';

    /**
     * Execute the console command.
     */
    public function handle(FcmService $fcmService): int
    {
        $this->info('== Test FCM HTTP v1 Push Notification ==');

        if (! $fcmService->isConfigured()) {
            $this->error('FCM is not configured properly. Check FCM_PROJECT_ID and credentials file.');
            return Command::FAILURE;
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
            // Find all users who have at least one registered device token
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
            $this->line("- User: {$user->name} ({$user->email}) [{$tokenCount} device token(s)]");

            $result = $fcmService->sendToUsers(
                $user->id,
                'pengajuan',
                '🔔 Tes Push Notification GoMSN',
                'Notifikasi FCM berhasil terhubung ke perangkat Anda!',
                url('/portal/dashboard'),
                ['test' => true, 'timestamp' => now()->toIso8601String()]
            );

            $this->info("  Result: Sent: {$result['sent']}, Failed: {$result['failed']}");
        }

        $this->info('Test completed successfully.');
        return Command::SUCCESS;
    }
}
