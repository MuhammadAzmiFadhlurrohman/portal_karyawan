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

        foreach ($intents as $intent) {
            $keywords = $intent->keywords ?? [];
            if (empty($keywords)) {
                continue;
            }

            $matches = 0;
            $maxKwLen = 0;
            foreach ($keywords as $kw) {
                $kwClean = mb_strtolower(trim($kw));
                if ($kwClean === '') continue;

                if (str_contains($messageClean, $kwClean)) {
                    $matches++;
                    $maxKwLen = max($maxKwLen, mb_strlen($kwClean));
                }
            }

            if ($matches > 0) {
                // Score berdasarkan jumlah kecocokan kata kunci dan bobot panjang kata kunci yang cocok
                $score = ($matches / count($keywords)) + ($maxKwLen / 100);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestIntent = $intent;
                }
            }
        }

        // Suggestions for close matches or fallback
        $suggestions = [
            'Bagaimana alur cara absen?',
            'Berapa sisa cuti saya?',
            'Bagaimana cara mengajukan lembur?',
            'Bagaimana alur pengajuan dana operasional?',
            'Cara membuka tiket gangguan baru',
            'Bagaimana cara tukar shift jadwal kerja?',
        ];

        // Jika tidak ada kata kunci yang cocok sama sekali
        if ($bestScore <= 0 || ! $bestIntent) {
            $fallback = "Halo! Maaf, saya belum menemukan jawaban yang tepat untuk pertanyaan tersebut.\n\nAnda dapat menanyakan hal seputar:\n• Presensi / Alur Absen Wajah & GPS\n• Sisa Cuti & Pengajuan Cuti\n• Jadwal Kerja & Tukar Shift\n• Pengajuan Dana & Pencairan Finance\n• Klaim Lembur\n• Tiketing & Gangguan Jaringan\n\nAtau hubungi Helpdesk NOC di WhatsApp 08112000006.";

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
