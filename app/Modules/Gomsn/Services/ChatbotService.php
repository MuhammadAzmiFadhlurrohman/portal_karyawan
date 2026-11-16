<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\ChatbotIntent;
use App\Models\ChatbotLog;
use App\Models\LeaveBalance;
use App\Models\Lembur;
use App\Models\Pengajuan;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\ChatbotKnowledgeSeeder;

class ChatbotService
{
    /**
     * Ensure knowledge base intents are synced in database automatically
     * so the user never has to run manual seeders after git pull.
     */
    protected function ensureKnowledgeInitialized(): void
    {
        $defaults = ChatbotKnowledgeSeeder::getDefaultIntents();
        $count = ChatbotIntent::query()->count();

        if ($count < count($defaults)) {
            foreach ($defaults as $in) {
                ChatbotIntent::query()->updateOrCreate(
                    ['intent_key' => $in['intent_key']],
                    array_merge($in, ['is_active' => true])
                );
            }
        }
    }

    /**
     * Normalize Indonesian words (stemming & synonym mapping) for smarter NLU matching.
     */
    protected function normalizeText(string $text): string
    {
        $clean = mb_strtolower(trim($text));
        // Hapus tanda baca menjadi spasi
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean) ?? $clean;

        // Sinonim sehari-hari ke kata baku aplikasi
        $synonyms = [
            'gimana' => 'bagaimana',
            'gmn' => 'bagaimana',
            'bgmn' => 'bagaimana',
            'cara' => 'cara',
            'caranya' => 'cara',
            'ngajuin' => 'ajukan',
            'mengajukan' => 'ajukan',
            'pengajuan' => 'ajukan',
            'ngelakuin' => 'cara',
            'bikin' => 'buat',
            'membuat' => 'buat',
            'uang' => 'dana',
            'duit' => 'dana',
            'reimbursement' => 'reimburse',
            'cutiku' => 'cuti saya',
            'absenku' => 'absen saya',
            'presensi' => 'absen',
            'kehadiran' => 'absen',
            'cekin' => 'clock in',
            'cekout' => 'clock out',
            'check in' => 'clock in',
            'check out' => 'clock out',
            'overtime' => 'lembur',
            'lemburan' => 'lembur',
            'tukeran' => 'tukar',
            'gantian' => 'tukar',
            'acc' => 'approve',
            'setujui' => 'approve',
            'disetujui' => 'approve',
            'menyetujui' => 'approve',
            'persetujuan' => 'approve',
            'poto' => 'foto',
            'photo' => 'foto',
            'profile' => 'profil',
            'sandi' => 'password',
            'kata sandi' => 'password',
            'brp' => 'berapa',
            'kpn' => 'kapan',
            'udh' => 'sudah',
            'udah' => 'sudah',
            'blm' => 'belum',
            'gak' => 'tidak',
            'ga' => 'tidak',
            'enggak' => 'tidak',
            'tdk' => 'tidak',
        ];

        foreach ($synonyms as $from => $to) {
            $clean = preg_replace('/\b' . preg_quote($from, '/') . '\b/u', $to, $clean) ?? $clean;
        }

        return trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);
    }

    /**
     * Extract meaningful tokens (excluding common filler stopwords).
     *
     * @return list<string>
     */
    protected function extractTokens(string $normalizedText): array
    {
        $stopwords = [
            'yang', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan', 'dan', 'atau',
            'ini', 'itu', 'sih', 'dong', 'deh', 'ya', 'yah', 'kak', 'min', 'admin',
            'pak', 'bu', 'bos', 'apakah', 'kalau', 'kalo', 'bila', 'jika', 'mau',
            'ingin', 'pengen', 'tanya', 'nanya', 'tolong', 'mohon', 'bantu',
        ];

        $words = explode(' ', $normalizedText);
        $tokens = [];
        foreach ($words as $w) {
            $w = trim($w);
            if ($w !== '' && ! in_array($w, $stopwords, true)) {
                $tokens[] = $w;
            }
        }

        return array_values(array_unique($tokens));
    }

    /**
     * Process message from user and return personalized response (§5.8).
     *
     * @return array{response: string, confidence: float, matched_intent: ?string, suggestions: list<string>}
     */
    public function ask(?User $user, string $message): array
    {
        $this->ensureKnowledgeInitialized();

        $rawClean = mb_strtolower(trim($message));
        $normMessage = $this->normalizeText($message);
        $userTokens = $this->extractTokens($normMessage);

        $intents = ChatbotIntent::query()->where('is_active', true)->get();

        $bestIntent = null;
        $bestScore = 0.0;

        foreach ($intents as $intent) {
            $keywords = $intent->keywords ?? [];
            if (empty($keywords)) {
                continue;
            }

            $intentScore = 0.0;

            foreach ($keywords as $kw) {
                $kwRaw = mb_strtolower(trim($kw));
                $kwNorm = $this->normalizeText($kw);
                if ($kwNorm === '') {
                    continue;
                }

                // 1. Exact phrase match in raw or normalized message (highest weight)
                if (str_contains($rawClean, $kwRaw) || str_contains($normMessage, $kwNorm)) {
                    $phraseWordsCount = count(explode(' ', $kwNorm));
                    $phraseScore = 1.0 + ($phraseWordsCount * 0.45) + (mb_strlen($kwNorm) / 50);
                    $intentScore = max($intentScore, $phraseScore);
                    continue;
                }

                // 2. Token-level & root word overlap (handles varied word order like "bagaimana cara mengajukan dana?")
                $kwTokens = $this->extractTokens($kwNorm);
                if (! empty($kwTokens) && ! empty($userTokens)) {
                    $matchedTokenCount = 0;
                    foreach ($kwTokens as $kt) {
                        foreach ($userTokens as $ut) {
                            // Exact token match or prefix/stem match (min 4 chars, e.g. "ajukan" <-> "mengajukan")
                            if ($kt === $ut || (mb_strlen($kt) >= 4 && mb_strlen($ut) >= 4 && (str_contains($ut, $kt) || str_contains($kt, $ut)))) {
                                $matchedTokenCount++;
                                break;
                            } elseif (mb_strlen($kt) >= 5 && mb_strlen($ut) >= 5 && levenshtein($kt, $ut) <= 1) {
                                // Toleransi typo 1 huruf (misal "lembr" -> "lembur", "absn" -> "absen")
                                $matchedTokenCount += 0.85;
                                break;
                            }
                        }
                    }

                    if ($matchedTokenCount > 0) {
                        $coverage = $matchedTokenCount / count($kwTokens);
                        // Jika semua kata kunci dalam frase keyword ditemukan di kalimat user (walau urutan acak)
                        if ($coverage >= 0.99) {
                            $tokenScore = 0.95 + (count($kwTokens) * 0.35);
                            $intentScore = max($intentScore, $tokenScore);
                        } elseif ($coverage >= 0.5 && $matchedTokenCount >= 1) {
                            $tokenScore = $coverage * 0.75;
                            $intentScore = max($intentScore, $tokenScore);
                        }
                    }
                }
            }

            if ($intentScore > $bestScore) {
                $bestScore = $intentScore;
                $bestIntent = $intent;
            }
        }

        // Suggestions for quick follow-up
        $suggestions = [
            'Bagaimana cara absen masuk & pulang?',
            'Bagaimana cara mengajukan dana?',
            'Berapa sisa cuti saya?',
            'Bagaimana cara klaim lembur?',
            'Siapa yang approve pengajuan absen?',
            'Cara buat tiket gangguan baru',
        ];

        // Jika tidak ada kecocokan yang memadai
        if ($bestScore < 0.45 || ! $bestIntent) {
            $fallback = "Halo! Maaf, saya belum menangkap maksud pertanyaan tersebut secara spesifik. 🙏\n\nCoba tanyakan dengan kata kunci seperti:\n• \"Cara absen masuk / pulang\"\n• \"Bagaimana cara mengajukan dana / barang?\"\n• \"Status pengajuan saya\"\n• \"Berapa sisa cuti saya?\"\n• \"Cara klaim lembur\"\n• \"Cara tukar shift jadwal\"\n• \"Cara buka tiket gangguan\"\n\nAtau hubungi Helpdesk NOC di WhatsApp 08112000006.";

            ChatbotLog::query()->create([
                'user_id' => $user?->id,
                'user_message' => $message,
                'matched_intent_key' => null,
                'confidence_score' => round($bestScore, 2),
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
            if ($bestIntent->intent_key === 'sapaan_halo') {
                $reply = "Halo {$user->name}! 👋 Saya Asisten Pintar Portal Karyawan & Operasional PT MSN.\n\nAda yang bisa saya bantu hari ini? Anda bisa bertanya berbagai hal, misalnya:\n• \"Apakah saya sudah absen hari ini?\"\n• \"Bagaimana cara mengajukan dana?\"\n• \"Status pengajuan dana saya\"\n• \"Berapa sisa cuti saya?\"\n• \"Bagaimana cara mengajukan lembur?\"\n• \"Cara tukar jadwal shift\"";
            } elseif ($bestIntent->intent_key === 'sisa_cuti') {
                $balance = LeaveBalance::query()
                    ->where('user_id', $user->id)
                    ->where('year', (int) date('Y'))
                    ->first();
                $sisa = $balance ? ($balance->remaining_days ?? $balance->remaining_balance ?? 12) : 12;
                $reply = "Halo {$user->name}, sisa kuota cuti tahunan Anda untuk tahun ".date('Y')." adalah {$sisa} hari kerja.\n\nUntuk mengajukan cuti, silakan buka menu 'Cuti & Izin' di sidebar.";
            } elseif ($bestIntent->intent_key === 'status_absen_hari_ini') {
                $att = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::now('Asia/Jakarta')->toDateString())
                    ->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                if ($att && $clockIn) {
                    $clockInTime = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $clockOutStr = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : 'Belum Clock-Out';
                    $reply = "Halo {$user->name}, status presensi Anda hari ini:\n• Absen Masuk: {$clockInTime} WIB\n• Absen Pulang: {$clockOutStr}\n• Status: ".strtoupper($att->status ?? 'HADIR');
                } else {
                    $reply = "Halo {$user->name}, Anda tercatat BELUM melakukan Absen Masuk untuk hari ini. Silakan buka menu 'Absen' untuk melakukan presensi wajah & GPS.";
                }
            } elseif ($bestIntent->intent_key === 'tiket_aktif_saya') {
                $count = Tiket::query()
                    ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                    ->count();
                $reply = "Halo {$user->name}, saat ini terdapat {$count} tiket gangguan aktif yang ditugaskan kepada Anda. Silakan cek detailnya di menu 'Tiketing'.";
            } elseif ($bestIntent->intent_key === 'status_pengajuan_saya') {
                $latestPengajuan = Pengajuan::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(3)
                    ->get();

                if ($latestPengajuan->isEmpty()) {
                    $reply = "Halo {$user->name}, Anda belum memiliki riwayat pengajuan dana/barang. Anda dapat membuat permohonan baru melalui menu 'Pengajuan Saya'.";
                } else {
                    $lines = ["Halo {$user->name}, berikut status pengajuan terbaru Anda:"];
                    foreach ($latestPengajuan as $p) {
                        $nom = $p->jenis === 'STOK' ? 'Stok Gudang' : 'Rp '.number_format((float) $p->total_nominal, 0, ',', '.');
                        $st = strtoupper(str_replace('_', ' ', $p->status));
                        $lines[] = "• #{$p->id} ({$p->jenis} - {$nom}): {$st}";
                    }
                    $lines[] = "\nSelengkapnya dapat dilihat di menu 'Pengajuan Saya'.";
                    $reply = implode("\n", $lines);
                }
            }
        }

        ChatbotLog::query()->create([
            'user_id' => $user?->id,
            'user_message' => $message,
            'matched_intent_key' => $bestIntent->intent_key,
            'confidence_score' => round($bestScore, 2),
            'bot_response' => $reply,
            'is_answered' => true,
        ]);

        return [
            'response' => $reply,
            'confidence' => round($bestScore, 2),
            'matched_intent' => $bestIntent->intent_key,
            'suggestions' => array_slice($suggestions, 0, 4),
        ];
    }
}
