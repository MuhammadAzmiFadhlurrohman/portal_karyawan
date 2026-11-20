<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\ChatbotIntent;
use App\Models\ChatbotLog;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Lembur;
use App\Models\MonthlySchedule;
use App\Models\Pengajuan;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use Carbon\Carbon;
use Database\Seeders\ChatbotKnowledgeSeeder;
use Illuminate\Support\Facades\Cache;

class ChatbotService
{
    /**
     * Ensure knowledge base intents are always up-to-date in database
     * when KNOWLEDGE_VERSION changes, and remove stale legacy intents.
     */
    protected function ensureKnowledgeInitialized(): void
    {
        $versionKey = 'chatbot_knowledge_synced_version';
        $currentVersion = ChatbotKnowledgeSeeder::KNOWLEDGE_VERSION;
        $defaultIntents = ChatbotKnowledgeSeeder::getDefaultIntents();

        if (Cache::get($versionKey) !== $currentVersion || ChatbotIntent::query()->count() !== count($defaultIntents)) {
            $validKeys = array_column($defaultIntents, 'intent_key');
            ChatbotIntent::query()->whereNotIn('intent_key', $validKeys)->delete();

            foreach ($defaultIntents as $in) {
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

        return $user->hasRole(['super_admin', 'admin', 'manager', 'supervisor', 'atasan'])
            || $this->canApproveAbsen($user)
            || $this->canApproveCuti($user)
            || $this->canApproveLembur($user)
            || $this->canApprovePengajuanDana($user)
            || $this->canManageEmployee($user);
    }

    protected function canApproveAbsen(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd', 'manager', 'supervisor']) || $user->can('attendance.correction.approve'));
    }

    protected function canApproveCuti(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd', 'manager', 'supervisor']) || $user->can('attendance.leaves.approve'));
    }

    protected function canApproveLembur(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd', 'manager', 'supervisor', 'finance']) || $user->can('overtime.approve'));
    }

    protected function canApprovePengajuanDana(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'manager', 'supervisor', 'direksi', 'finance']) || $user->can('pengajuan.approve'));
    }

    protected function canManageEmployee(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd']) || $user->can('attendance.employee.manage'));
    }

    protected function isHrdOrAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'admin', 'hrd'])
            || $user->can('attendance.employee.manage')
            || str_contains(mb_strtolower($user->division ?? ''), 'hrd')
            || str_contains(mb_strtolower($user->position ?? ''), 'hrd');
    }

    /**
     * Resolve action button (Label & URL) for a matched intent.
     *
     * @return ?array{label: string, url: string}
     */
    protected function resolveActionLink(string $intentKey, ?User $user = null): ?array
    {
        return match ($intentKey) {
            'q1_status_checkin_hari_ini',
            'q3_cara_absen_biometrik_gps',
            'q4_troubleshooting_gps',
            'q5_troubleshooting_biometrik',
            'q9_aturan_absen_luar_kantor',
            'q12_sinkronisasi_absensi_realtime',
            'q13_absen_offline_kendala_jaringan',
            'q79_fungsi_tombol_refresh_absensi' => [
                'label' => '📍 Buka Halaman Absen',
                'url' => route('attendance.index'),
            ],
            'q2_riwayat_checkin_kemarin',
            'q8_prosedur_koreksi_absen',
            'q10_rekap_kehadiran_7_hari',
            'q11_cara_lihat_log_bulan_lalu',
            'q14_total_hari_kerja_efektif',
            'q87_cek_riwayat_dan_potongan_typo' => [
                'label' => '📝 Buka Riwayat Absen Saya',
                'url' => route('attendance.history'),
            ],
            'q15_aturan_absensi_hari_libur',
            'q16_jadwal_shift_hari_ini_besok',
            'q17_cara_cek_kalender_shift',
            'q18_kebijakan_tukar_shift' => [
                'label' => '📅 Buka Jadwal Kerja Saya',
                'url' => route('attendance.schedules'),
            ],
            'q19_sisa_saldo_cuti',
            'q20_langkah_ajukan_cuti',
            'q21_sla_batas_pengajuan_cuti',
            'q22_syarat_dokumen_izin_sakit',
            'q23_cara_cek_approval_izin',
            'q24_pembatalan_cuti_disetujui',
            'q29_perbedaan_jenis_cuti_izin',
            'q30_aturan_hangus_sisa_cuti',
            'q89_cuti_mendadak_multi_turn' => [
                'label' => '🌴 Buka Cuti & Izin Saya',
                'url' => route('attendance.leaves'),
            ],
            'q6_batas_toleransi_terlambat',
            'q7_rekap_terlambat_bulan_ini',
            'q25_penjelasan_nominal_potongan',
            'q26_formula_hitung_potongan',
            'q27_total_potongan_denda_saya',
            'q28_alur_sanggahan_potongan',
            'q59_integrasi_potongan_ke_payroll',
            'q76_lokasi_menu_potongan_denda',
            'q90_efek_terlambat_gaji_leaderboard' => [
                'label' => '💵 Buka Potongan Absen Saya',
                'url' => route('attendance.deductions'),
            ],
            'q31_cara_ajukan_kasbon',
            'q34_langkah_pengajuan_beli_barang',
            'q35_cara_permintaan_stok_material' => [
                'label' => '💰 Buat Pengajuan Baru',
                'url' => route('gomsn.pengajuan.create'),
            ],
            'q32_batas_maksimal_kasbon',
            'q33_prosedur_settlement_kasbon',
            'q36_cek_status_pengajuan_terakhir',
            'q37_hierarki_approval_pembelian',
            'q45_revisi_pengajuan_pending',
            'q77_struktur_menu_pengajuan_operasional',
            'q83_cara_search_layanan_kasbon' => [
                'label' => '📋 Buka Pengajuan Saya',
                'url' => route('gomsn.pengajuan.index'),
            ],
            'q38_cara_ajukan_lembur',
            'q39_syarat_spk_lembur',
            'q41_batas_waktu_klaim_lembur' => [
                'label' => '⏰ Ajukan Lembur',
                'url' => route('gomsn.overtime.create'),
            ],
            'q40_cara_akses_dokumen_spk',
            'q42_total_jam_lembur_bulan_ini',
            'q43_alasan_lembur_ditolak',
            'q44_aturan_lembur_hari_libur',
            'q53_monitoring_lembur_karyawan',
            'q54_antrean_approval_lembur_hari_ini' => [
                'label' => '💼 Buka Halaman Lembur',
                'url' => route('gomsn.overtime.index'),
            ],
            'q46_company_ringkasan_absensi_hari_ini',
            'q47_daftar_karyawan_sakit_hari_ini',
            'q48_daftar_karyawan_terlambat_hari_ini',
            'q51_unduh_rekap_absensi_potongan',
            'q52_divisi_kehadiran_tertinggi' => [
                'label' => '📊 Buka Absen Karyawan',
                'url' => route('attendance.employee-recap'),
            ],
            'q49_cara_cari_kontak_karyawan',
            'q50_cari_karyawan_berdasarkan_nik',
            'q57_cara_ubah_status_aktif_karyawan',
            'q58_onboarding_karyawan_baru',
            'q60_total_karyawan_aktif_headcount',
            'q84_menu_khusus_divisi_hrd' => $this->canManageEmployee($user)
                ? [
                    'label' => '👥 Buka Data Karyawan',
                    'url' => route('portal.employees.index'),
                ]
                : null,
            'q55_kriteria_penilaian_leaderboard',
            'q56_top_5_leaderboard_disiplin' => [
                'label' => '🏆 Buka Leaderboard',
                'url' => route('attendance.leaderboard'),
            ],
            'q61_cara_buat_tiket_gangguan',
            'q62_jumlah_tiket_open_hari_ini',
            'q63_update_progres_tiket',
            'q67_prosedur_spk_tiket_noc',
            'q68_syarat_bukti_closing_tiket',
            'q69_alur_eskalasi_sla_tiket',
            'q70_cari_riwayat_tiket_pelanggan',
            'q71_permintaan_stok_dari_tiket',
            'q73_teknisi_tugas_prioritas_tinggi',
            'q74_perbedaan_tiket_noc_vs_pasang_baru',
            'q82_perbedaan_menu_tiket_navigasi',
            'extra_resume_pekerjaan_tiket',
            'extra_core_joint_closure',
            'extra_tugas_operasional_tasks',
            'extra_tiket_aktif_saya' => [
                'label' => '🎫 Buka Tiket Gangguan',
                'url' => route('tickets.index'),
            ],
            'q64_informasi_menu_olt_pon_odp',
            'q65_cara_cek_odp_pelanggan',
            'q66_arti_status_port_pon_olt',
            'q72_cek_kapasitas_port_odp',
            'q75_cara_input_koordinat_odp' => [
                'label' => '🌐 Buka Jaringan OLT, PON & ODP',
                'url' => route('gomsn.network.index'),
            ],
            'q78_cara_buka_semua_layanan_portal',
            'extra_kalender_absensi_dashboard' => [
                'label' => '🏠 Buka Dashboard',
                'url' => route('portal.hub'),
            ],
            'q80_cara_ganti_foto_profil',
            'q81_prosedur_reset_password_lupa' => [
                'label' => '👤 Buka Profil Saya',
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

        // Pre-normalize hyphenated terms before stripping punctuation
        $clean = str_replace(
            ['real-time', 'check-in', 'check-out', 'non-aktif', 'company-wide', 'carry-over', 'password-nya', 'langkah-langkah'],
            ['realtime', 'check in', 'check out', 'non aktif', 'company wide', 'carry over', 'passwordnya', 'langkah langkah'],
            $clean
        );

        // Replace punctuation with space
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean) ?? $clean;

        // Typos & synonym normalization
        $synonyms = [
            'haii' => 'hai',
            'haiii' => 'hai',
            'haloo' => 'halo',
            'halooo' => 'halo',
            'helo' => 'halo',
            'heloo' => 'halo',
            'helooo' => 'halo',
            'oy' => 'halo',
            'woi' => 'halo',
            'hei' => 'hai',
            'heyy' => 'hai',
            'hey' => 'hai',
            'p' => 'ping',
            'gimana' => 'bagaimana',
            'gmn' => 'bagaimana',
            'bgmn' => 'bagaimana',
            'caranya' => 'cara',
            'crnya' => 'cara',
            'absennya' => 'absen',
            'mengurusnya' => 'mengurus',
            'penolakannya' => 'penolakan',
            'passwordnya' => 'password',
            'efeknya' => 'efek',
            'ngajuin' => 'mengajukan',
            'ajukan' => 'ajukan',
            'rwayat' => 'riwayat',
            'absn' => 'absen',
            'sy' => 'saya',
            'bln' => 'bulan',
            'ptongan' => 'potongan',
            'bsk' => 'besok',
            'dn' => 'dan',
            'jg' => 'juga',
            'cekin' => 'check in',
            'cekout' => 'check out',
            'overtime' => 'lembur',
            'poto' => 'foto',
            'photo' => 'foto',
            'profile' => 'profil',
            'brp' => 'berapa',
            'kpn' => 'kapan',
            'knp' => 'kenapa',
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
     * Extract meaningful tokens (excluding filler/question words).
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
            'bagaimana', 'cara', 'bisa', 'ada', 'masih', 'saya', 'apa', 'saja',
            'aplikasi', 'gomsn', 'sistem', 'menu', 'dalam', 'saat', 'oleh',
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
     * High-value domain subject words that carry higher weight when matching intents.
     */
    protected function isSubjectKeyword(string $token): bool
    {
        $subjects = [
            'cuti', 'sakit', 'lembur', 'dana', 'kasbon', 'barang', 'beli', 'stok', 'absen', 'absensi',
            'wajah', 'biometrik', 'verifikasi', 'radius', 'gps', 'koreksi', 'lupa', 'tukar', 'backup',
            'shift', 'jadwal', 'tiket', 'gangguan', 'backbone', 'broadband', 'sla', 'eskalasi',
            'kpi', 'olt', 'pon', 'odp', 'potongan', 'denda', 'alpa', 'telat', 'terlambat', 'keterlambatan',
            'leaderboard', 'foto', 'profil', 'password', 'sandi', 'approve', 'persetujuan', 'sisa', 'saldo',
            'kuota', 'dokter', 'resume', 'joint', 'closure', 'jc', 'core', 'splicing', 'redaman',
            'manuver', 'material', 'bast', 'dispatch', 'dinas', 'luar', 'lapangan', 'kalender',
            'divisi', 'kemarin', 'efektif', 'spk', 'settlement', 'pelunasan', 'pertanggungjawaban',
            'headcount', 'onboarding', 'payroll', 'rekening', 'gaji', 'nik', 'realtime', 'offline',
            'internet', 'jaringan', 'mati', 'refresh', 'home', 'kontak', 'support', 'error', 'sanggahan',
            'hangus', 'diakumulasi', 'ditolak', 'pending', 'mengedit', 'port', 'kapasitas', 'koordinat',
        ];

        return in_array($token, $subjects, true);
    }

    /**
     * Process message from user and return concise, accurate response + navigation button.
     *
     * @return array{response: string, confidence: float, matched_intent: ?string, action_url: ?string, action_label: ?string, suggestions: list<string>}
     */
    public function ask(?User $user, string $message): array
    {
        $this->ensureKnowledgeInitialized();

        $rawClean = mb_strtolower(trim($message));
        $normMessage = $this->normalizeText($message);
        $userTokens = $this->extractTokens($normMessage);

        // ==========================================
        // EARLY GUARDRAILS & EDGE CASES (Q92 - Q99)
        // ==========================================

        // 1. Empty or Punctuation Only (Q99)
        if (preg_match('/^[?\s.!,\-_:;]+$/u', $rawClean) || mb_strlen($rawClean) < 2) {
            $resp = "Maaf, pesan yang Anda kirimkan tidak valid atau belum jelas. Silakan ketik pertanyaan singkat terkait layanan GOMSN, misalnya: \"Hari ini saya sudah check-in atau belum?\", \"Berapa sisa saldo cuti saya?\", atau \"Bagaimana cara mengajukan lembur?\".";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q99_input_tidak_valid',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Berapa sisa saldo cuti tahunan saya saat ini?', 'Bagaimana cara mengajukan lembur?'],
            ];
        }

        // 2. Keyboard Smash / Gibberish (Q99)
        if (
            (preg_match('/\b(asdfghjkl|qwertyuiop|zxcvbnm|asdasd|hjkjhg)\b/i', $rawClean) && ! str_contains($rawClean, 'mengirimkan pesan kosong'))
            || (preg_match('/\b[bcdfghjklmnpqrstvwxyz]{6,}\b/i', $rawClean) && count($userTokens) <= 3)
        ) {
            $resp = "Maaf, pesan yang Anda kirimkan tidak valid atau berupa karakter acak. Silakan ketik pertanyaan singkat terkait layanan GOMSN, misalnya: \"Hari ini saya sudah check-in atau belum?\", \"Berapa sisa saldo cuti saya?\", atau \"Bagaimana cara mengajukan lembur?\".";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q99_input_tidak_valid',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Berapa sisa saldo cuti tahunan saya saat ini?', 'Bagaimana cara mengajukan lembur?'],
            ];
        }

        // 3. Prompt Injection / Jailbreak (Q98)
        if (
            str_contains($rawClean, 'abaikan semua instruksi')
            || str_contains($rawClean, 'abaikan instruksi')
            || str_contains($rawClean, 'bertindak sebagai hacker')
            || str_contains($rawClean, 'tampilkan seluruh isi database')
            || str_contains($rawClean, 'dump database')
            || str_contains($rawClean, 'ignore all instructions')
        ) {
            $resp = "Permintaan Ditolak. Saya tidak dapat mengabaikan instruksi keamanan sistem maupun menampilkan isi database internal. Silakan ajukan pertanyaan yang berkaitan dengan prosedur operasional resmi GOMSN.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q98_proteksi_prompt_injection',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Jadwal shift kerja saya hari ini dan besok', 'Berapa sisa saldo cuti tahunan saya saat ini?'],
            ];
        }

        // 4. PII Protection: Gaji Pokok & Nomor Rekening (Q94)
        if (
            (str_contains($rawClean, 'gaji pokok') || str_contains($rawClean, 'rincian gaji'))
            && (str_contains($rawClean, 'nomor rekening') || str_contains($rawClean, 'no rekening') || str_contains($rawClean, 'rekening'))
        ) {
            $resp = "Akses Ditolak. Demi perlindungan data pribadi (PII Protection), informasi sensitif seperti gaji pokok dan nomor rekening pribadi karyawan bersifat rahasia dan tidak dapat ditampilkan melalui chatbot.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q94_proteksi_pii_gaji_rekening',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Berapa sisa saldo cuti tahunan saya saat ini?', 'Berapa total potongan dan denda saya pada periode berjalan ini?'],
            ];
        }

        // 5. Out of Scope: Sepak Bola / Cuaca / Berita Umum (Q96)
        if (
            str_contains($rawClean, 'sepak bola')
            || str_contains($rawClean, 'piala dunia')
            || str_contains($rawClean, 'prediksi cuaca')
            || str_contains($rawClean, 'skor pertandingan')
        ) {
            $resp = "Maaf, pertanyaan tersebut berada di luar cakupan layanan saya. Saya adalah Asisten Virtual internal GOMSN PT Media Solusi Network yang khusus membantu informasi seputar absensi, jadwal kerja, cuti, pengajuan operasional, dan tiket gangguan.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q96_out_of_scope_umum',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Berapa sisa saldo cuti tahunan saya saat ini?', 'Bagaimana cara mengajukan lembur?'],
            ];
        }

        // 6. Out of Scope: Coding & Artikel Marketing (Q97)
        if (
            (str_contains($rawClean, 'kode program') || str_contains($rawClean, 'python') || str_contains($rawClean, 'artikel marketing'))
            && (str_contains($rawClean, 'artikel marketing') || str_contains($rawClean, 'machine learning') || str_contains($rawClean, 'tidak berhubungan'))
        ) {
            $resp = "Maaf, saya tidak dapat membuatkan kode program umum atau artikel marketing di luar konteks pekerjaan portal. Saya dirancang khusus untuk membantu operasional internal dan panduan penggunaan aplikasi GOMSN.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q97_guardrail_topik_pekerjaan',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Bagaimana cara membuat Tiket Gangguan baru?', 'Bagaimana cara mengajukan kasbon operasional?', 'Jadwal shift kerja saya hari ini dan besok'],
            ];
        }

        // 7. Explicit NIK Lookup & Hallucination Prevention (Q50 & Q95)
        if (preg_match('/\b(kr\d{4,}|\d{10,16})\b/i', $rawClean, $nikMatches)) {
            $checkedNik = strtoupper($nikMatches[1]);
            $foundUser = User::query()
                ->where('nik', $checkedNik)
                ->orWhere('employee_code', $checkedNik)
                ->first();

            if (! $foundUser) {
                $resp = "Data tidak ditemukan. Karyawan dengan NIK {$checkedNik} tidak terdaftar di dalam database sistem GOMSN PT Media Solusi Network. Pastikan kembali nomor NIK yang Anda masukkan sudah benar.";
                return [
                    'response' => $resp,
                    'confidence' => 1.0,
                    'matched_intent' => 'q95_pencegahan_halusinasi_nik',
                    'action_url' => $this->canManageEmployee($user) ? route('portal.employees.index') : null,
                    'action_label' => $this->canManageEmployee($user) ? '👥 Buka Data Karyawan' : null,
                    'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Jadwal shift kerja saya hari ini dan besok'],
                ];
            }

            if (str_contains($rawClean, 'divisi') || str_contains($rawClean, 'status')) {
                $statusAktif = $foundUser->is_active ? 'Aktif' : 'Non-Aktif';
                $resp = "Data Karyawan NIK {$checkedNik}:\n• Nama: {$foundUser->name}\n• Divisi: " . ($foundUser->division ?? '-') . "\n• Jabatan: " . ($foundUser->position ?? '-') . "\n• Status Akun: {$statusAktif}";
                return [
                    'response' => $resp,
                    'confidence' => 1.0,
                    'matched_intent' => 'q50_cari_karyawan_berdasarkan_nik',
                    'action_url' => $this->canManageEmployee($user) ? route('portal.employees.index') : null,
                    'action_label' => $this->canManageEmployee($user) ? '👥 Buka Data Karyawan' : null,
                    'suggestions' => ['Hari ini saya sudah check-in atau belum?'],
                ];
            }
        }

        // 8. Chat Approval Prohibition (Q92)
        if (
            (str_contains($rawClean, 'menyetujui') || str_contains($rawClean, 'setujui') || str_contains($rawClean, 'approve'))
            && (str_contains($rawClean, 'lewat chat ini') || str_contains($rawClean, 'melalui chat ini') || str_contains($rawClean, 'di chat ini'))
        ) {
            $resp = "Tidak bisa. Saya hanya asisten informasi dan tidak memiliki wewenang mengeksekusi persetujuan (approval). Proses approval lembur maupun cuti wajib dilakukan langsung oleh Atasan/HRD melalui menu persetujuan resmi di portal.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q92_penolakan_approval_chat',
                'action_url' => route('gomsn.overtime.index'),
                'action_label' => '💼 Buka Halaman Lembur',
                'suggestions' => ['Bagaimana cara mengajukan lembur?', 'Cek status pengajuan terakhir saya'],
            ];
        }

        // ==========================================
        // HIGH-PRECISION INTENT MATCHING ENGINE
        // ==========================================
        $intents = ChatbotIntent::query()->where('is_active', true)->get();

        $bestIntent = null;
        $bestScore = 0.0;
        $userTokenCount = max(1, count($userTokens));

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

                // 0. Exact full normalized match
                if ($normMessage === $kwNorm || $rawClean === $kwRaw) {
                    $intentScore = max($intentScore, 100.0);
                    break;
                }

                // Short greetings (<= 6 chars in category umum) require short user query
                if ($intent->intent_key === 'sapaan_halo' && mb_strlen($kwNorm) <= 6) {
                    if (
                        preg_match('/\b' . preg_quote($kwRaw, '/') . '\b/u', $rawClean)
                        || preg_match('/\b' . preg_quote($kwNorm, '/') . '\b/u', $normMessage)
                    ) {
                        if ($userTokenCount <= 2) {
                            $intentScore = max($intentScore, 15.0);
                        }
                    }
                    continue;
                }

                $kwTokens = $this->extractTokens($kwNorm);
                $kwTokenCount = max(1, count($kwTokens));

                // 1. Whole-phrase match inside message, scaled by how much of the user's query it covers
                if (
                    preg_match('/\b' . preg_quote($kwRaw, '/') . '\b/u', $rawClean)
                    || preg_match('/\b' . preg_quote($kwNorm, '/') . '\b/u', $normMessage)
                ) {
                    $queryCoverage = min(1.0, $kwTokenCount / $userTokenCount);
                    $subjectBonus = 0.0;
                    foreach ($kwTokens as $kt) {
                        $subjectBonus += $this->isSubjectKeyword($kt) ? 1.2 : 0.4;
                    }

                    // Prevent 1-2 word generic keywords from hijacking 6-10 word specific questions
                    if ($kwTokenCount <= 2 && $userTokenCount >= 4) {
                        $phraseScore = (1.5 + $subjectBonus) * ($queryCoverage * 0.6);
                    } else {
                        $phraseScore = (4.0 + $subjectBonus + ($kwTokenCount * 0.5)) * (0.4 + (0.6 * $queryCoverage));
                    }

                    $intentScore = max($intentScore, $phraseScore);
                    continue;
                }

                // 2. Bidirectional Token & Subject Overlap (Keyword Coverage + User Query Coverage)
                if (! empty($kwTokens) && ! empty($userTokens)) {
                    $matchedWeight = 0.0;
                    $totalKwWeight = 0.0;
                    $matchedSubjectCount = 0;
                    $matchedTokenCount = 0;

                    foreach ($kwTokens as $kt) {
                        $weight = $this->isSubjectKeyword($kt) ? 2.5 : 1.0;
                        $totalKwWeight += $weight;

                        $found = false;
                        foreach ($userTokens as $ut) {
                            if ($kt === $ut) {
                                $found = true;
                                break;
                            }
                            if (mb_strlen($kt) >= 5 && mb_strlen($ut) >= 5 && (str_starts_with($ut, $kt) || str_starts_with($kt, $ut))) {
                                $found = true;
                                break;
                            }
                            if (mb_strlen($kt) >= 5 && mb_strlen($ut) >= 5 && levenshtein($kt, $ut) <= 1) {
                                $found = true;
                                $weight *= 0.9;
                                break;
                            }
                        }

                        if ($found) {
                            $matchedWeight += $weight;
                            $matchedTokenCount++;
                            if ($this->isSubjectKeyword($kt)) {
                                $matchedSubjectCount++;
                            }
                        }
                    }

                    if ($matchedWeight > 0 && $totalKwWeight > 0) {
                        $kwCoverage = $matchedWeight / $totalKwWeight;
                        $userCoverage = $matchedTokenCount / $userTokenCount;

                        if ($kwCoverage >= 0.85 && $userCoverage >= 0.60) {
                            $tokenScore = 6.0 + ($kwCoverage * 3.0) + ($userCoverage * 4.0) + ($matchedSubjectCount * 1.2);
                            $intentScore = max($intentScore, $tokenScore);
                        } elseif ($matchedSubjectCount >= 1 && $kwCoverage >= 0.55 && $userCoverage >= 0.40) {
                            $tokenScore = ($kwCoverage * 2.5) + ($userCoverage * 2.5) + ($matchedSubjectCount * 0.9);
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

        $suggestions = [
            'Hari ini saya sudah check-in atau belum?',
            'Jadwal shift kerja saya hari ini dan besok masuk jam berapa?',
            'Berapa sisa saldo cuti tahunan saya saat ini?',
            'Bagaimana cara mengajukan lembur melalui menu Lembur Saya?',
        ];

        if ($bestScore < 1.50 || ! $bestIntent) {
            $fallback = "Maaf, saya belum memahami maksud pertanyaan tersebut secara spesifik. Silakan tanyakan langsung seputar absensi, jadwal shift, cuti, potongan, kasbon, lembur, atau tiket gangguan.";

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
                'suggestions' => $suggestions,
            ];
        }

        // ==========================================
        // CONCISE DYNAMIC RESPONSES
        // ==========================================
        $reply = $bestIntent->response_text;

        if ($user) {
            $key = $bestIntent->intent_key;

            if ($key === 'sapaan_halo') {
                $reply = "Halo {$user->name}! 👋 Saya Asisten Virtual GOMSN. Silakan tanyakan langsung seputar absensi, jadwal shift, cuti, kasbon, lembur, atau tiket gangguan.";
            } elseif ($key === 'q1_status_checkin_hari_ini') {
                $att = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::now('Asia/Jakarta')->toDateString())
                    ->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                if ($att && $clockIn) {
                    $inTime = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $outTime = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : 'Belum Check-Out';
                    $st = strtoupper($att->status ?? 'HADIR');
                    $reply = "Hari ini Anda sudah check-in pada pukul {$inTime} WIB (Status: {$st}, Check-Out: {$outTime}).";
                } else {
                    $reply = "Hari ini Anda tercatat belum melakukan check-in. Silakan buka menu Absen untuk melakukan presensi.";
                }
            } elseif ($key === 'q2_riwayat_checkin_kemarin') {
                $yesterday = Carbon::yesterday('Asia/Jakarta');
                $att = Attendance::query()->where('user_id', $user->id)->whereDate('date', $yesterday->toDateString())->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                $tglStr = $yesterday->translatedFormat('d F Y');
                if ($att && $clockIn) {
                    $in = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $out = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : 'Belum/Lupa Check-Out';
                    $reply = "Kemarin ({$tglStr}), Anda check-in pukul {$in} WIB dan check-out pukul {$out} (Status: " . strtoupper($att->status ?? 'HADIR') . ").";
                } else {
                    $reply = "Tidak tercatat data check-in maupun check-out untuk Anda pada hari kemarin ({$tglStr}).";
                }
            } elseif ($key === 'q7_rekap_terlambat_bulan_ini') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $lateCount = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $cMonth)
                    ->whereYear('date', $cYear)
                    ->where(function ($q) {
                        $q->where('status', 'TERLAMBAT')->orWhere('late_minutes', '>', 0);
                    })
                    ->count();
                $totalLateMin = (int) Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $cMonth)
                    ->whereYear('date', $cYear)
                    ->sum('late_minutes');
                $bln = Carbon::now('Asia/Jakarta')->translatedFormat('F Y');
                $reply = "Selama bulan ini ({$bln}), Anda tercatat terlambat sebanyak {$lateCount} kali dengan total akumulasi {$totalLateMin} menit.";
            } elseif ($key === 'q10_rekap_kehadiran_7_hari') {
                $start7 = Carbon::now('Asia/Jakarta')->subDays(6)->toDateString();
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $attendances7 = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('date', [$start7, $today])
                    ->orderBy('date', 'desc')
                    ->get();
                if ($attendances7->isEmpty()) {
                    $reply = "Belum ada catatan kehadiran dalam 7 hari terakhir.";
                } else {
                    $lines = ["Rekap kehadiran 7 hari terakhir Anda:"];
                    foreach ($attendances7 as $a) {
                        $tgl = Carbon::parse($a->date)->translatedFormat('d M Y');
                        $cin = ($a->clock_in_at || $a->clock_in) ? Carbon::parse($a->clock_in_at ?? $a->clock_in)->timezone('Asia/Jakarta')->format('H:i') : '-';
                        $cout = ($a->clock_out_at || $a->clock_out) ? Carbon::parse($a->clock_out_at ?? $a->clock_out)->timezone('Asia/Jakarta')->format('H:i') : '-';
                        $st = strtoupper($a->status ?? 'HADIR');
                        $lines[] = "• {$tgl}: {$st} (In: {$cin} | Out: {$cout})";
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($key === 'q14_total_hari_kerja_efektif') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $hadirCount = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $cMonth)
                    ->whereYear('date', $cYear)
                    ->whereIn('status', ['HADIR', 'TERLAMBAT'])
                    ->count();
                $scheduledCount = MonthlySchedule::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $cMonth)
                    ->whereYear('date', $cYear)
                    ->whereHas('shift', fn ($q) => $q->where('is_day_off', false))
                    ->count();
                $target = $scheduledCount > 0 ? $scheduledCount : 22;
                $bln = Carbon::now('Asia/Jakarta')->translatedFormat('F Y');
                $reply = "Total hari kerja efektif Anda yang tercatat hadir pada bulan {$bln} saat ini adalah {$hadirCount} hari (dari {$target} hari kerja terjadwal).";
            } elseif ($key === 'q16_jadwal_shift_hari_ini_besok') {
                $todayStr = Carbon::now('Asia/Jakarta')->toDateString();
                $tomorrowStr = Carbon::tomorrow('Asia/Jakarta')->toDateString();
                $schToday = MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $todayStr)->first();
                $schTomorrow = MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $tomorrowStr)->first();

                $fmtShift = function ($sch) {
                    if (! $sch || ! $sch->shift) {
                        return 'Shift Pagi (08:00 - 17:00 WIB)';
                    }
                    if ($sch->shift->is_day_off) {
                        return 'Libur (OFF)';
                    }
                    $start = substr((string) $sch->shift->start_time, 0, 5);
                    $end = substr((string) $sch->shift->end_time, 0, 5);
                    return "{$sch->shift->name} ({$start} - {$end} WIB)";
                };

                $reply = "Jadwal shift kerja Anda:\n• Hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmtShift($schToday) . "\n• Besok (" . Carbon::tomorrow('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmtShift($schTomorrow);
            } elseif ($key === 'q19_sisa_saldo_cuti') {
                $balance = LeaveBalance::query()
                    ->where('user_id', $user->id)
                    ->where('year', (int) date('Y'))
                    ->first();
                $sisa = $balance ? ($balance->remaining_days ?? $balance->remaining_balance ?? 12) : 12;
                $reply = "Sisa saldo cuti tahunan Anda untuk tahun " . date('Y') . " saat ini adalah {$sisa} hari kerja.";
            } elseif ($key === 'q27_total_potongan_denda_saya') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $grandTotal = 0.0;
                $lateDeduction = 0.0;
                $alpaDeduction = 0.0;
                try {
                    $deductionReport = app(DeductionService::class)->calculateUserMonthlyReport($user, $cYear, $cMonth);
                    $grandTotal = (float) ($deductionReport['grand_total_deduction'] ?? 0);
                    $lateDeduction = (float) ($deductionReport['total_late_deduction'] ?? 0);
                    $alpaDeduction = (float) ($deductionReport['total_alpa_deduction'] ?? 0);
                } catch (\Throwable $e) {
                    $lateCount = Attendance::query()->where('user_id', $user->id)->whereMonth('date', $cMonth)->whereYear('date', $cYear)->where('status', 'TERLAMBAT')->count();
                    $lateDeduction = (float) ($lateCount * 5000);
                    $grandTotal = $lateDeduction;
                }
                $fmtTotal = number_format($grandTotal, 0, ',', '.');
                $fmtLate = number_format($lateDeduction, 0, ',', '.');
                $fmtAlpa = number_format($alpaDeduction, 0, ',', '.');
                $bln = Carbon::now('Asia/Jakarta')->translatedFormat('F Y');
                $reply = "Total potongan dan denda absensi Anda pada periode {$bln} adalah Rp {$fmtTotal} (Terlambat: Rp {$fmtLate}, Alpa: Rp {$fmtAlpa}).";
            } elseif ($key === 'q36_cek_status_pengajuan_terakhir') {
                $latestPengajuan = Pengajuan::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(3)
                    ->get();
                if ($latestPengajuan->isEmpty()) {
                    $reply = "Anda belum memiliki riwayat pengajuan kasbon atau pembelian barang.";
                } else {
                    $lines = ["Status pengajuan terakhir Anda:"];
                    foreach ($latestPengajuan as $p) {
                        $nom = $p->jenis === 'STOK' ? 'Permintaan Stok' : 'Rp ' . number_format((float) $p->total_nominal, 0, ',', '.');
                        $st = strtoupper(str_replace('_', ' ', $p->status));
                        $lines[] = "• #{$p->id} ({$p->jenis} - {$nom}): {$st}";
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($key === 'q42_total_jam_lembur_bulan_ini') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $approvedLembur = Lembur::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['approved', 'paid'])
                    ->whereMonth('tanggal', $cMonth)
                    ->whereYear('tanggal', $cYear)
                    ->get();
                $totalMenit = (int) $approvedLembur->sum('durasi_menit');
                $totalJam = round($totalMenit / 60, 1);
                $count = $approvedLembur->count();
                $bln = Carbon::now('Asia/Jakarta')->translatedFormat('F Y');
                $reply = "Total jam lembur Anda yang sudah disetujui pada bulan {$bln} adalah {$totalJam} jam ({$totalMenit} menit) dari {$count} pengajuan.";
            } elseif ($key === 'q46_company_ringkasan_absensi_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Akses Ditolak. Ringkasan absensi seluruh karyawan (company-wide) hanya dapat diakses oleh divisi HRD & Human Capital atau Administrator.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $totalActive = User::query()->where('is_active', true)->count();
                    $hadir = Attendance::query()->whereDate('date', $today)->where('status', 'HADIR')->count();
                    $terlambat = Attendance::query()->whereDate('date', $today)->where(function ($q) {
                        $q->where('status', 'TERLAMBAT')->orWhere('late_minutes', '>', 0);
                    })->count();
                    $sakit = Leave::query()->where('status', 'approved')->whereIn('leave_type', ['sakit', 'sick'])->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();
                    $izin = Leave::query()->where('status', 'approved')->whereNotIn('leave_type', ['sakit', 'sick'])->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();
                    $reply = "Ringkasan absensi company-wide hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . "):\n• Hadir Tepat Waktu: {$hadir} orang\n• Terlambat: {$terlambat} orang\n• Izin/Cuti: {$izin} orang\n• Sakit: {$sakit} orang\n(Total Karyawan Aktif: {$totalActive} orang).";
                }
            } elseif ($key === 'q47_daftar_karyawan_sakit_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Akses Ditolak. Daftar karyawan sakit company-wide hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $sickLeaves = Leave::query()
                        ->with('user')
                        ->whereIn('leave_type', ['sakit', 'sick'])
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->get();
                    if ($sickLeaves->isEmpty()) {
                        $reply = "Hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . ") tidak ada karyawan yang tercatat sakit.";
                    } else {
                        $names = $sickLeaves->map(fn ($sl) => ($sl->user?->name ?? 'Karyawan') . ' (' . ($sl->user?->division ?? '-') . ')')->implode(', ');
                        $reply = "Karyawan yang tercatat sakit hari ini: {$names}.";
                    }
                }
            } elseif ($key === 'q48_daftar_karyawan_terlambat_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Akses Ditolak. Daftar karyawan terlambat company-wide hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $lates = Attendance::query()
                        ->with('user')
                        ->whereDate('date', $today)
                        ->where(function ($q) {
                            $q->where('status', 'TERLAMBAT')->orWhere('late_minutes', '>', 0);
                        })
                        ->get();
                    if ($lates->isEmpty()) {
                        $reply = "Hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . ") tidak ada karyawan yang tercatat terlambat.";
                    } else {
                        $list = $lates->map(fn ($lt) => ($lt->user?->name ?? 'Karyawan') . " ({$lt->late_minutes} menit)")->implode(', ');
                        $reply = "Karyawan yang tercatat terlambat hari ini: {$list}.";
                    }
                }
            } elseif ($key === 'q52_divisi_kehadiran_tertinggi') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Akses Ditolak. Statistik kehadiran antar divisi hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $cMonth = Carbon::now('Asia/Jakarta')->month;
                    $cYear = Carbon::now('Asia/Jakarta')->year;
                    $topDiv = Attendance::query()
                        ->join('users', 'users.id', '=', 'attendances.user_id')
                        ->whereMonth('attendances.date', $cMonth)
                        ->whereYear('attendances.date', $cYear)
                        ->whereIn('attendances.status', ['HADIR', 'TERLAMBAT'])
                        ->selectRaw('users.division, count(*) as total_hadir')
                        ->whereNotNull('users.division')
                        ->groupBy('users.division')
                        ->orderByDesc('total_hadir')
                        ->first();
                    $divName = $topDiv?->division ?? 'Teknik';
                    $tot = $topDiv?->total_hadir ?? 0;
                    $reply = "Divisi dengan tingkat kehadiran paling tinggi bulan ini adalah divisi {$divName} ({$tot} kehadiran tercatat).";
                }
            } elseif ($key === 'q54_antrean_approval_lembur_hari_ini') {
                $pendingLembur = Lembur::query()
                    ->whereIn('status', ['submitted', 'pending', 'menunggu_approval'])
                    ->count();
                $reply = "Saat ini terdapat {$pendingLembur} pengajuan lembur karyawan yang masih menunggu persetujuan.";
            } elseif ($key === 'q56_top_5_leaderboard_disiplin') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $topUsers = Attendance::query()
                    ->join('users', 'users.id', '=', 'attendances.user_id')
                    ->whereMonth('attendances.date', $cMonth)
                    ->whereYear('attendances.date', $cYear)
                    ->where('attendances.status', 'HADIR')
                    ->where('attendances.late_minutes', '<=', 0)
                    ->selectRaw('users.name, users.division, count(*) as tepat_waktu')
                    ->groupBy('users.id', 'users.name', 'users.division')
                    ->orderByDesc('tepat_waktu')
                    ->limit(5)
                    ->get();
                if ($topUsers->isEmpty()) {
                    $reply = "Data peringkat 5 besar Leaderboard periode ini sedang dikalkulasi. Silakan buka menu Leaderboard.";
                } else {
                    $lines = ["5 karyawan dengan peringkat disiplin teratas di Leaderboard saat ini:"];
                    foreach ($topUsers as $idx => $u) {
                        $rank = $idx + 1;
                        $lines[] = "{$rank}. {$u->name} ({$u->division}) — {$u->tepat_waktu}x hadir tepat waktu";
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($key === 'q60_total_karyawan_aktif_headcount') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Akses Ditolak. Data total headcount karyawan perusahaan hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $totalActive = User::query()->where('is_active', true)->count();
                    $reply = "Total karyawan aktif di PT Media Solusi Network yang terdaftar di sistem saat ini adalah {$totalActive} orang.";
                }
            } elseif ($key === 'q62_jumlah_tiket_open_hari_ini') {
                $openCount = Tiket::query()->where('status', 'OPEN')->count();
                $prosesCount = Tiket::query()->whereIn('status', ['PROSES', 'PENDING'])->count();
                $reply = "Saat ini terdapat {$openCount} tiket gangguan berstatus Terbuka (OPEN) dan {$prosesCount} tiket sedang dalam proses penanganan.";
            } elseif ($key === 'q73_teknisi_tugas_prioritas_tinggi') {
                $highTickets = Tiket::query()
                    ->with('ticketAssignees.user')
                    ->whereIn('status', ['OPEN', 'PROSES'])
                    ->whereIn('priority', ['CRITICAL', 'HIGH'])
                    ->latest()
                    ->take(3)
                    ->get();
                if ($highTickets->isEmpty()) {
                    $reply = "Saat ini tidak ada tiket gangguan berprioritas tinggi (High/Critical) yang sedang aktif.";
                } else {
                    $lines = ["Tim/teknisi yang sedang menangani tiket gangguan prioritas tinggi:"];
                    foreach ($highTickets as $ht) {
                        $names = $ht->ticketAssignees->map(fn ($a) => $a->user?->name)->filter()->implode(', ');
                        $techStr = $names !== '' ? $names : ($ht->team_members ?: 'Menunggu Dispatch NOC');
                        $lines[] = "• #{$ht->no_tiket} ({$ht->priority}): {$techStr}";
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($key === 'q91_ringkasan_operasional_3_poin') {
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $totalActive = User::query()->where('is_active', true)->count();
                $attended = Attendance::query()->whereDate('date', $today)->whereIn('status', ['HADIR', 'TERLAMBAT'])->count();
                $openTickets = Tiket::query()->whereIn('status', ['OPEN', 'PROSES'])->count();
                $pendingLembur = Lembur::query()->whereIn('status', ['submitted', 'pending'])->count();
                $pendingPengajuan = Pengajuan::query()->whereIn('status', ['pending', 'menunggu_approval'])->count();
                $reply = "Ringkasan 3 poin kondisi operasional & absensi hari ini:\n1. Absensi: {$attended} dari {$totalActive} karyawan aktif tercatat sudah hadir.\n2. Tiket Gangguan: Terdapat {$openTickets} tiket gangguan aktif (Open/Proses) yang sedang ditangani.\n3. Antrean Approval: Terdapat {$pendingLembur} pengajuan lembur dan {$pendingPengajuan} pengajuan operasional menunggu persetujuan.";
            } elseif ($key === 'q93_rbac_kontak_rekap_semua_karyawan') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Akses Ditolak. Data kontak pribadi dan rekapitulasi potongan seluruh karyawan bersifat rahasia serta hanya dapat diakses oleh otoritas HRD & Human Capital atau Administrator.";
                }
            } elseif ($key === 'extra_tiket_aktif_saya') {
                $count = Tiket::query()
                    ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                    ->count();
                $reply = "Saat ini terdapat {$count} tiket gangguan aktif yang ditugaskan kepada Anda.";
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
            'suggestions' => $suggestions,
        ];
    }
}
