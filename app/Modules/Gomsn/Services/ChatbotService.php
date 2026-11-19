<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\ChatbotIntent;
use App\Models\ChatbotLog;
use App\Models\LeaveBalance;
use App\Models\Pengajuan;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\ChatbotKnowledgeSeeder;
use Illuminate\Support\Facades\Cache;

class ChatbotService
{
    /**
     * Ensure knowledge base intents are always up-to-date in database
     * when KNOWLEDGE_VERSION changes, without needing manual seeder runs.
     */
    protected function ensureKnowledgeInitialized(): void
    {
        $versionKey = 'chatbot_knowledge_synced_version';
        $currentVersion = ChatbotKnowledgeSeeder::KNOWLEDGE_VERSION;

        if (Cache::get($versionKey) !== $currentVersion || ChatbotIntent::query()->count() < count(ChatbotKnowledgeSeeder::getDefaultIntents())) {
            foreach (ChatbotKnowledgeSeeder::getDefaultIntents() as $in) {
                ChatbotIntent::query()->updateOrCreate(
                    ['intent_key' => $in['intent_key']],
                    array_merge($in, ['is_active' => true])
                );
            }
            Cache::forever($versionKey, $currentVersion);
        }
    }

    /**
     * Check if user has supervisor/manager/atasan authority.
     */
    protected function isAtasanOrManager(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'admin', 'ceo', 'manager_teknisi', 'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum'])) {
            return true;
        }

        if ($user->can('gomsn.pengajuan.approve_atasan') || $user->can('attendance.correction.approve') || $user->can('gomsn.lembur.approve')) {
            return true;
        }

        return User::query()->where('supervisor_id', $user->id)->exists();
    }

    /**
     * Check if user can approve attendance corrections.
     */
    protected function canApproveAbsen(?User $user): bool
    {
        return $this->isAtasanOrManager($user) || ($user && $user->can('attendance.correction.approve'));
    }

    /**
     * Check if user can approve leave requests.
     */
    protected function canApproveCuti(?User $user): bool
    {
        return $this->isAtasanOrManager($user) || ($user && ($user->hasRole('hrd') || $user->can('attendance.leave.approve')));
    }

    /**
     * Check if user can approve overtime.
     */
    protected function canApproveLembur(?User $user): bool
    {
        return $this->isAtasanOrManager($user) || ($user && ($user->hasRole('finance') || $user->can('gomsn.lembur.approve')));
    }

    /**
     * Check if user can approve fund/goods requests.
     */
    protected function canApprovePengajuanDana(?User $user): bool
    {
        return $this->isAtasanOrManager($user) || ($user && ($user->hasRole(['finance', 'direksi', 'ceo']) || $user->can('gomsn.pengajuan.approve_finance')));
    }

    /**
     * Check if user can manage employee data.
     */
    protected function canManageEmployee(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd']) || $user->can('attendance.employee.manage'));
    }

    /**
     * Resolve action button (Label & URL) for a matched intent so the user can jump directly to the page.
     *
     * @return ?array{label: string, url: string}
     */
    protected function resolveActionLink(string $intentKey, ?User $user = null): ?array
    {
        return match ($intentKey) {
            'status_absen_hari_ini',
            'alur_cara_absen',
            'liveness_wajah',
            'radius_geofencing' => [
                'label' => '📍 Buka Halaman Absen',
                'url' => route('attendance.index'),
            ],
            'koreksi_absensi' => [
                'label' => '📝 Buka Riwayat & Koreksi Absen',
                'url' => route('attendance.history'),
            ],
            'approval_pengajuan_absen' => $this->canApproveAbsen($user)
                ? [
                    'label' => '✅ Buka Persetujuan Pengajuan Absen',
                    'url' => route('attendance.corrections.approvals'),
                ]
                : [
                    'label' => '📝 Buka Riwayat Absen Saya',
                    'url' => route('attendance.history'),
                ],
            'tukar_shift_aturan',
            'backup_shift_aturan',
            'aturan_jeda_shift' => [
                'label' => '📅 Buka Jadwal Kerja',
                'url' => route('attendance.schedules'),
            ],
            'sisa_cuti',
            'cara_ajukan_sakit',
            'cara_ajukan_cuti' => [
                'label' => '🌴 Buka Halaman Cuti & Izin',
                'url' => route('attendance.leaves'),
            ],
            'cara_approve_cuti' => $this->canApproveCuti($user)
                ? [
                    'label' => '✅ Buka Persetujuan Cuti Karyawan',
                    'url' => route('attendance.leaves'),
                ]
                : [
                    'label' => '🌴 Ajukan Cuti & Izin Saya',
                    'url' => route('attendance.leaves'),
                ],
            'cara_ajukan_lembur' => [
                'label' => '⏰ Buat Pengajuan Lembur',
                'url' => route('gomsn.overtime.create'),
            ],
            'cara_approve_lembur' => $this->canApproveLembur($user)
                ? [
                    'label' => '💼 Buka Persetujuan Lembur',
                    'url' => route('gomsn.overtime.index'),
                ]
                : [
                    'label' => '⏰ Buat Pengajuan Lembur',
                    'url' => route('gomsn.overtime.create'),
                ],
            'pencairan_lembur' => [
                'label' => '💼 Buka Halaman Lembur',
                'url' => route('gomsn.overtime.index'),
            ],
            'alur_pengajuan_operasional' => [
                'label' => '💰 Buat Pengajuan Dana / Barang',
                'url' => route('gomsn.pengajuan.create'),
            ],
            'status_pengajuan_saya',
            'export_excel_pengajuan' => [
                'label' => '📋 Buka Pengajuan Saya',
                'url' => route('gomsn.pengajuan.index'),
            ],
            'tahapan_approval_pengajuan' => $this->canApprovePengajuanDana($user)
                ? [
                    'label' => '✅ Buka Persetujuan Karyawan',
                    'url' => route('gomsn.pengajuan.approvals'),
                ]
                : [
                    'label' => '📋 Buka Pengajuan Saya',
                    'url' => route('gomsn.pengajuan.index'),
                ],
            'stok_barang_gudang' => [
                'label' => '📦 Buka Stok Barang Gudang',
                'url' => route('gomsn.inventory.index'),
            ],
            'tugas_operasional_tasks' => [
                'label' => '🎫 Buka Halaman Tiketing & Penugasan',
                'url' => route('tickets.index'),
            ],
            'tiket_aktif_saya',
            'buka_tiket_gangguan',
            'resume_pekerjaan_tiket',
            'penanganan_core_joint_closure',
            'pemakaian_material_tiket',
            'chat_koordinasi_tiket',
            'closing_dan_berita_acara_bast',
            'dispatch_penugasan_tim',
            'stop_clock_aturan' => [
                'label' => '🎫 Buka Halaman Tiketing',
                'url' => route('tickets.index'),
            ],
            'tipe_absen_kantor_dinas_luar' => [
                'label' => '📍 Buka Halaman Absen',
                'url' => route('attendance.index'),
            ],
            'kalender_absensi_dashboard' => [
                'label' => '🏠 Buka Dashboard',
                'url' => route('portal.hub'),
            ],
            'rekap_absensi_karyawan' => [
                'label' => '📊 Buka Rekap Absensi',
                'url' => route('attendance.employee-recap'),
            ],
            'kpi_teknisi' => [
                'label' => '📊 Buka Analitik KPI Teknisi',
                'url' => route('gomsn.analytics.kpi'),
            ],
            'manajemen_jaringan_odp' => [
                'label' => '🌐 Buka Jaringan OLT, PON & ODP',
                'url' => route('gomsn.network.index'),
            ],
            'potongan_keuangan_absen' => [
                'label' => '💵 Buka Keuangan Absen',
                'url' => route('attendance.deductions'),
            ],
            'leaderboard_karyawan',
            'warna_divisi_badge' => [
                'label' => '🏆 Buka Leaderboard Kehadiran',
                'url' => route('attendance.leaderboard'),
            ],
            'ganti_foto_profil',
            'cara_login_sidik_jari' => [
                'label' => '👤 Buka Halaman Profile',
                'url' => route('portal.profile'),
            ],
            'manajemen_data_karyawan' => $this->canManageEmployee($user)
                ? [
                    'label' => '👥 Buka Data Karyawan',
                    'url' => route('portal.employees.index'),
                ]
                : [
                    'label' => '👤 Buka Halaman Profile',
                    'url' => route('portal.profile'),
                ],
            default => null,
        };
    }

    /**
     * Normalize Indonesian words (synonym mapping + typo fixes) without conflating distinct concepts.
     */
    protected function normalizeText(string $text): string
    {
        $clean = mb_strtolower(trim($text));
        // Hapus tanda baca menjadi spasi
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean) ?? $clean;

        // Perbaikan typo umum & sinonim kata per kata
        $synonyms = [
            'gimana' => 'bagaimana',
            'gmn' => 'bagaimana',
            'bgmn' => 'bagaimana',
            'caranya' => 'cara',
            'ngajuin' => 'mengajukan',
            'pengajuan' => 'pengajuan',
            'ajukan' => 'mengajukan',
            'bikin' => 'membuat',
            'buat' => 'membuat',
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
            'vertifikasi' => 'verifikasi',
            'verif' => 'verifikasi',
            'diverifikasi' => 'verifikasi',
            'divertifikasi' => 'verifikasi',
            'poto' => 'foto',
            'photo' => 'foto',
            'profile' => 'profil',
            'sandi' => 'password',
            'brp' => 'berapa',
            'kpn' => 'kapan',
            'udh' => 'sudah',
            'udah' => 'sudah',
            'blm' => 'belum',
            'gak' => 'tidak',
            'ga' => 'tidak',
            'enggak' => 'tidak',
            'tdk' => 'tidak',
            'dl' => 'dinas luar',
            'dinasluar' => 'dinas luar',
            'jointclosure' => 'joint closure',
            'jointing' => 'jointing',
            'splising' => 'splicing',
            'beritaacara' => 'berita acara',
            'todo' => 'tasks',
            'todolist' => 'tasks',
            'subtugas' => 'subtask',
            'stt' => 'voice to text',
        ];

        foreach ($synonyms as $from => $to) {
            $clean = preg_replace('/\b' . preg_quote($from, '/') . '\b/u', $to, $clean) ?? $clean;
        }

        return trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);
    }

    /**
     * Extract meaningful tokens (excluding filler/question words so scoring focuses on core subjects).
     *
     * @return list<string>
     */
    protected function extractTokens(string $normalizedText): array
    {
        $stopwords = [
            'yang', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan', 'dan', 'atau',
            'ini', 'itu', 'sih', 'dong', 'deh', 'ya', 'yah', 'kak', 'min',
            'pak', 'bu', 'bos', 'apakah', 'kalau', 'kalo', 'bila', 'jika', 'mau',
            'ingin', 'pengen', 'tanya', 'nanya', 'tolong', 'mohon', 'bantu',
            'bagaimana', 'cara', 'bisa', 'ada', 'masih',
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
     * High-value domain subject words that carry 3x weight when matching intents.
     */
    protected function isSubjectKeyword(string $token): bool
    {
        $subjects = [
            'cuti', 'sakit', 'lembur', 'dana', 'kasbon', 'barang', 'stok', 'absen',
            'wajah', 'verifikasi', 'radius', 'gps', 'koreksi', 'lupa', 'tukar', 'backup',
            'shift', 'jadwal', 'tiket', 'gangguan', 'backbone', 'broadband', 'sla',
            'kpi', 'olt', 'pon', 'odp', 'potongan', 'alpa', 'telat', 'leaderboard',
            'foto', 'profil', 'password', 'sidik', 'jari', 'approve', 'excel', 'sisa',
            'kuota', 'dokter', 'resume', 'joint', 'closure', 'jc', 'core', 'splicing',
            'manuver', 'material', 'bast', 'berita', 'acara', 'dispatch', 'dinas', 'luar',
            'kalender', 'task', 'tugas', 'subtask', 'mic', 'suara', 'voice', 'divisi',
        ];

        return in_array($token, $subjects, true);
    }

    /**
     * Process message from user and return personalized response + navigation button (§5.8).
     *
     * @return array{response: string, confidence: float, matched_intent: ?string, action_url: ?string, action_label: ?string, suggestions: list<string>}
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

                // Untuk intent sapaan pendek (seperti "hi", "hai", "tes", "halo"), wajib whole-word boundary!
                // Agar "hai" TIDAK nyangkut di dalam kata "masih" atau "tes" di dalam kata lain.
                if ($intent->category === 'umum' && mb_strlen($kwNorm) <= 6) {
                    if (preg_match('/\b' . preg_quote($kwRaw, '/') . '\b/u', $rawClean)) {
                        // Hanya menang jika kalimat user memang pendek (sapaan murni)
                        if (count($userTokens) <= 2) {
                            $intentScore = max($intentScore, 2.5);
                        } else {
                            $intentScore = max($intentScore, 0.3);
                        }
                    }
                    continue;
                }

                // 1. Exact phrase match (whole words) in raw or normalized message
                if (
                    preg_match('/\b' . preg_quote($kwRaw, '/') . '\b/u', $rawClean)
                    || preg_match('/\b' . preg_quote($kwNorm, '/') . '\b/u', $normMessage)
                ) {
                    $kwTokens = $this->extractTokens($kwNorm);
                    $subjectBonus = 0.0;
                    foreach ($kwTokens as $kt) {
                        if ($this->isSubjectKeyword($kt)) {
                            $subjectBonus += 1.2;
                        } else {
                            $subjectBonus += 0.4;
                        }
                    }
                    $phraseScore = 1.5 + $subjectBonus + (count(explode(' ', $kwNorm)) * 0.35);
                    $intentScore = max($intentScore, $phraseScore);
                    continue;
                }

                // 2. Weighted Token & Subject Overlap
                $kwTokens = $this->extractTokens($kwNorm);
                if (! empty($kwTokens) && ! empty($userTokens)) {
                    $matchedWeight = 0.0;
                    $totalKwWeight = 0.0;
                    $matchedSubjectCount = 0;

                    foreach ($kwTokens as $kt) {
                        $weight = $this->isSubjectKeyword($kt) ? 2.5 : 1.0;
                        $totalKwWeight += $weight;

                        $found = false;
                        foreach ($userTokens as $ut) {
                            if ($kt === $ut) {
                                $found = true;
                                break;
                            }
                            // Stem match for longer words (>= 5 chars, e.g. "mengajukan" <-> "pengajuan")
                            if (mb_strlen($kt) >= 5 && mb_strlen($ut) >= 5 && (str_starts_with($ut, $kt) || str_starts_with($kt, $ut))) {
                                $found = true;
                                break;
                            }
                            // Typo tolerance (distance 1 for words >= 5 chars)
                            if (mb_strlen($kt) >= 5 && mb_strlen($ut) >= 5 && levenshtein($kt, $ut) <= 1) {
                                $found = true;
                                $weight *= 0.9;
                                break;
                            }
                        }

                        if ($found) {
                            $matchedWeight += $weight;
                            if ($this->isSubjectKeyword($kt)) {
                                $matchedSubjectCount++;
                            }
                        }
                    }

                    if ($matchedWeight > 0 && $totalKwWeight > 0) {
                        $coverage = $matchedWeight / $totalKwWeight;
                        // Bonus besar jika semua kata penting di keyword cocok dengan pertanyaan user
                        if ($coverage >= 0.95) {
                            $tokenScore = 1.4 + ($matchedSubjectCount * 1.1) + (count($kwTokens) * 0.25);
                            $intentScore = max($intentScore, $tokenScore);
                        } elseif ($matchedSubjectCount >= 1 && $coverage >= 0.55) {
                            $tokenScore = ($coverage * 1.1) + ($matchedSubjectCount * 0.6);
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

        // Suggestions for quick follow-up based on user role
        $isApprover = $this->isAtasanOrManager($user);
        $suggestions = $isApprover ? [
            'Bagaimana cara approve pengajuan cuti?',
            'Cara approve pengajuan absen karyawan',
            'Bagaimana cara approve pengajuan dana?',
            'Cara approve pengajuan lembur',
            'Cara isi resume pekerjaan tiket',
            'Bagaimana cara absen dinas luar?',
            'Status pengajuan saya',
        ] : [
            'Bagaimana cara absen masuk & pulang?',
            'Bagaimana cara absen dinas luar?',
            'Cara isi resume pekerjaan tiket',
            'Bagaimana cara mengajukan dana?',
            'Bagaimana cara membuat pengajuan lembur?',
            'Apakah cuti saya masih ada?',
            'Cara catat penanganan core & joint closure',
            'Bagaimana cara membuat tugas operasional (tasks)?',
        ];

        // Jika tidak ada kecocokan yang memadai
        if ($bestScore < 0.75 || ! $bestIntent) {
            $fallback = $isApprover
                ? "Halo! Maaf, saya belum menangkap maksud pertanyaan tersebut secara spesifik. 🙏\n\nCoba tanyakan topik seperti:\n• \"Bagaimana cara approve pengajuan cuti?\"\n• \"Cara approve pengajuan absen karyawan\"\n• \"Bagaimana cara approve pengajuan dana?\"\n• \"Cara isi resume pekerjaan tiket\"\n• \"Bagaimana cara absen dinas luar?\"\n• \"Status pengajuan saya\"\n\nJika butuh bantuan teknis darurat, silakan hubungi Helpdesk NOC."
                : "Halo! Maaf, saya belum menangkap maksud pertanyaan tersebut secara spesifik. 🙏\n\nCoba tanyakan topik seperti:\n• \"Bagaimana cara absen dinas luar?\"\n• \"Cara isi resume pekerjaan tiket\"\n• \"Apakah cuti saya masih ada?\"\n• \"Bagaimana cara mengajukan dana?\"\n• \"Cara catat penanganan core & joint closure\"\n• \"Bagaimana cara membuat tugas operasional?\"\n\nJika butuh bantuan segera, silakan hubungi Helpdesk NOC.";

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
                'action_url' => null,
                'action_label' => null,
                'suggestions' => array_slice($suggestions, 0, 4),
            ];
        }

        // Personalized dynamic response for logged-in user data (§5.8) & Role-Based Access Restrictions
        $reply = $bestIntent->response_text;
        if ($user) {
            // Batasan Hak Akses (Role-Based Restriction): Jika karyawan biasa menanyakan approval / tugas manajerial
            if ($bestIntent->intent_key === 'approval_pengajuan_absen' && ! $this->canApproveAbsen($user)) {
                $reply = "Mohon maaf {$user->name}, fitur persetujuan (approval) koreksi absen hanya dapat dilakukan oleh Atasan Divisi atau Admin.\n\nSebagai karyawan, Anda tidak memiliki akses untuk menyetujui pengajuan. Anda dapat mengajukan koreksi kehadiran Anda sendiri melalui menu 'Riwayat Absen' dan menunggu persetujuan dari atasan.";
            } elseif ($bestIntent->intent_key === 'cara_approve_cuti' && ! $this->canApproveCuti($user)) {
                $reply = "Mohon maaf {$user->name}, proses persetujuan (approval) cuti karyawan merupakan kewenangan dari Atasan Langsung dan HRD.\n\nSebagai karyawan, Anda tidak memiliki wewenang untuk menyetujui pengajuan cuti. Anda cukup mengajukan permohonan cuti atau izin sakit melalui menu 'Cuti & Izin' dan menunggu persetujuan dari atasan Anda.";
            } elseif ($bestIntent->intent_key === 'cara_approve_lembur' && ! $this->canApproveLembur($user)) {
                $reply = "Mohon maaf {$user->name}, fitur persetujuan (approval) lembur hanya dapat diakses oleh Atasan Divisi (pengesahan tugas) dan Finance (pencairan dana).\n\nSebagai karyawan, Anda tidak memiliki wewenang menyetujui lembur rekan lain. Anda dapat membuat pengajuan lembur Anda sendiri melalui menu 'Lembur'.";
            } elseif ($bestIntent->intent_key === 'tahapan_approval_pengajuan' && ! $this->canApprovePengajuanDana($user)) {
                $reply = "Mohon maaf {$user->name}, proses persetujuan (approval) dan pencairan pengajuan operasional dilakukan oleh Atasan Divisi, Direksi, dan Finance.\n\nSebagai karyawan/pemohon, Anda tidak memiliki wewenang untuk menyetujui pengajuan. Anda dapat membuat pengajuan baru atau memantau status pengajuan Anda melalui menu 'Pengajuan Saya'.";
            } elseif ($bestIntent->intent_key === 'manajemen_data_karyawan' && ! $this->canManageEmployee($user)) {
                $reply = "Mohon maaf {$user->name}, pengelolaan data karyawan (tambah karyawan, ubah divisi, reset password atau biometrik) merupakan hak akses khusus Admin & HRD.\n\nUntuk melihat atau memperbarui profil pribadi Anda serta mendaftarkan sidik jari, silakan kunjungi menu 'Profile'.";
            } elseif ($bestIntent->intent_key === 'sapaan_halo') {
                $reply = "Halo {$user->name}! 👋 Saya Asisten Pintar Portal Karyawan & Operasional PT MSN.\n\nAda yang bisa saya bantu hari ini? Anda bisa bertanya berbagai hal, misalnya:\n• \"Apakah saya sudah absen hari ini?\"\n• \"Bagaimana cara mengajukan dana?\"\n• \"Bagaimana cara membuat pengajuan lembur?\"\n• \"Apakah cuti saya masih ada?\"\n• \"Bagaimana cara mengajukan sakit?\"\n• \"Cara tukar jadwal shift\"";
            } elseif ($bestIntent->intent_key === 'sisa_cuti') {
                $balance = LeaveBalance::query()
                    ->where('user_id', $user->id)
                    ->where('year', (int) date('Y'))
                    ->first();
                $sisa = $balance ? ($balance->remaining_days ?? $balance->remaining_balance ?? 12) : 12;
                $statusCuti = $sisa > 0 ? "Masih tersedia ({$sisa} hari kerja)" : 'Sudah habis (0 hari)';
                $reply = "Halo {$user->name}, kuota cuti tahunan Anda untuk tahun ".date('Y').":\n• Status: {$statusCuti}\n• Sisa Kuota: {$sisa} hari kerja.\n\nKlik tombol di bawah untuk menuju halaman Cuti & Izin.";
            } elseif ($bestIntent->intent_key === 'status_absen_hari_ini') {
                $att = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::now('Asia/Jakarta')->toDateString())
                    ->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                if ($att && $clockIn) {
                    $clockInTime = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $clockOutStr = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i').' WIB' : 'Belum Clock-Out';
                    $reply = "Halo {$user->name}, status presensi Anda hari ini:\n• Absen Masuk: {$clockInTime} WIB\n• Absen Pulang: {$clockOutStr}\n• Status: ".strtoupper($att->status ?? 'HADIR');
                } else {
                    $reply = "Halo {$user->name}, Anda tercatat BELUM melakukan Absen Masuk untuk hari ini. Silakan klik tombol di bawah untuk menuju halaman Absen.";
                }
            } elseif ($bestIntent->intent_key === 'tiket_aktif_saya') {
                $count = Tiket::query()
                    ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                    ->count();
                $reply = "Halo {$user->name}, saat ini terdapat {$count} tiket gangguan aktif yang ditugaskan kepada Anda.";
            } elseif ($bestIntent->intent_key === 'status_pengajuan_saya') {
                $latestPengajuan = Pengajuan::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(3)
                    ->get();

                if ($latestPengajuan->isEmpty()) {
                    $reply = "Halo {$user->name}, Anda belum memiliki riwayat pengajuan dana/barang.";
                } else {
                    $lines = ["Halo {$user->name}, berikut 3 status pengajuan terbaru Anda:"];
                    foreach ($latestPengajuan as $p) {
                        $nom = $p->jenis === 'STOK' ? 'Stok Gudang' : 'Rp '.number_format((float) $p->total_nominal, 0, ',', '.');
                        $st = strtoupper(str_replace('_', ' ', $p->status));
                        $lines[] = "• #{$p->id} ({$p->jenis} - {$nom}): {$st}";
                    }
                    $reply = implode("\n", $lines);
                }
            }
        }

        $actionLink = $this->resolveActionLink($bestIntent->intent_key, $user);

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
            'action_url' => $actionLink['url'] ?? null,
            'action_label' => $actionLink['label'] ?? null,
            'suggestions' => array_slice($suggestions, 0, 4),
        ];
    }
}
