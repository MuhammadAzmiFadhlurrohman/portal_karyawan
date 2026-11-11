<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\ChatbotIntent;
use App\Models\ChatbotLog;
use App\Models\LeaveBalance;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;

class ChatbotService
{
    /**
     * Process message from user and return personalized response (§5.8).
     *
     * @return array{response: string, confidence: float, matched_intent: ?string, suggestions: list<string>}
     */
    public function ask(?User $user, string $message): array
    {
        $messageClean = mb_strtolower(trim($message));
        $intents = ChatbotIntent::query()->where('is_active', true)->get();

        $bestIntent = null;
        $bestScore = 0.0;
        $matchedKeywords = [];

        foreach ($intents as $intent) {
            $keywords = $intent->keywords ?? [];
            $matchCount = 0;
            foreach ($keywords as $kw) {
                if (str_contains($messageClean, mb_strtolower($kw))) {
                    $matchCount++;
                }
            }

            if (! empty($keywords)) {
                $score = $matchCount / count($keywords);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestIntent = $intent;
                }
            }
        }

        // Suggestions for close matches or fallback
        $suggestions = [
            'Bagaimana cara mengajukan cuti?',
            'Berapa sisa cuti saya?',
            'Bagaimana cara klaim uang lembur?',
            'Cara membuka tiket gangguan baru',
            'Bagaimana aturan jeda shift 11 jam?',
        ];

        // Below threshold (0.50) -> Helpdesk escalation & suggestions (§5.8)
        if ($bestScore < 0.40 || ! $bestIntent) {
            $fallback = 'Maaf, saya belum memahami pertanyaan Anda. Anda dapat menghubungi Helpdesk NOC di WhatsApp 08112000006 atau memilih salah satu topik bantuan di bawah.';

            ChatbotLog::query()->create([
                'user_id' => $user?->id,
                'user_message' => $message,
                'matched_intent_key' => null,
                'confidence_score' => $bestScore,
                'bot_response' => $fallback,
                'is_answered' => false,
            ]);

            return [
                'response' => $fallback,
                'confidence' => round($bestScore, 2),
                'matched_intent' => null,
                'suggestions' => $suggestions,
            ];
        }

        // Personalized dynamic response for logged-in user data (§5.8)
        $reply = $bestIntent->response_text;
        if ($user) {
            if ($bestIntent->intent_key === 'sisa_cuti') {
                $balance = LeaveBalance::query()
                    ->where('user_id', $user->id)
                    ->where('year', (int) date('Y'))
                    ->first();
                $sisa = $balance ? ($balance->remaining_days ?? $balance->remaining_balance ?? 12) : 12;
                $reply = "Halo {$user->name}, sisa kuota cuti tahunan Anda untuk tahun ".date('Y')." adalah {$sisa} hari kerja.";
            } elseif ($bestIntent->intent_key === 'status_absen_hari_ini') {
                $att = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::now()->toDateString())
                    ->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                if ($att && $clockIn) {
                    $clockInTime = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $clockOutStr = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') : 'Belum';
                    $reply = "Halo {$user->name}, Anda sudah Clock-In hari ini pada pukul {$clockInTime} WIB. Clock-Out: {$clockOutStr}.";
                } else {
                    $reply = "Halo {$user->name}, Anda belum melakukan Clock-In untuk hari ini.";
                }
            } elseif ($bestIntent->intent_key === 'tiket_aktif_saya') {
                $count = Tiket::query()
                    ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                    ->count();
                $reply = "Halo {$user->name}, saat ini terdapat {$count} tiket gangguan aktif yang ditugaskan kepada Anda.";
            }
        }

        ChatbotLog::query()->create([
            'user_id' => $user?->id,
            'user_message' => $message,
            'matched_intent_key' => $bestIntent->intent_key,
            'confidence_score' => $bestScore,
            'bot_response' => $reply,
            'is_answered' => true,
        ]);

        return [
            'response' => $reply,
            'confidence' => round($bestScore, 2),
            'matched_intent' => $bestIntent->intent_key,
            'suggestions' => array_slice($suggestions, 0, 3),
        ];
    }
}
