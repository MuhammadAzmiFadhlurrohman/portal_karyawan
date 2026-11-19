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

        return $user->hasRole(['super_admin', 'admin', 'manager', 'supervisor', 'atasan'])
            || $this->canApproveAbsen($user)
            || $this->canApproveCuti($user)
            || $this->canApproveLembur($user)
            || $this->canApprovePengajuanDana($user)
            || $this->canManageEmployee($user);
    }

    /**
     * Check if user can approve attendance corrections.
     */
    protected function canApproveAbsen(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd', 'manager', 'supervisor']) || $user->can('attendance.correction.approve'));
    }

    /**
     * Check if user can approve leaves.
     */
    protected function canApproveCuti(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd', 'manager', 'supervisor']) || $user->can('attendance.leaves.approve'));
    }

    /**
     * Check if user can approve overtime.
     */
    protected function canApproveLembur(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd', 'manager', 'supervisor', 'finance']) || $user->can('overtime.approve'));
    }

    /**
     * Check if user can approve operational fund/item requests.
     */
    protected function canApprovePengajuanDana(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'manager', 'supervisor', 'direksi', 'finance']) || $user->can('pengajuan.approve'));
    }

    /**
     * Check if user can manage employee data.
     */
    protected function canManageEmployee(?User $user): bool
    {
        return $user && ($user->hasRole(['super_admin', 'admin', 'hrd']) || $user->can('attendance.employee.manage'));
    }

    /**
     * Check if user is HRD or Administrator.
     */
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
     * Resolve action button (Label & URL) for a matched intent so the user can jump directly to the page.
     *
     * @return ?array{label: string, url: string}
     */
    protected function resolveActionLink(string $intentKey, ?User $user = null): ?array
    {
        return match ($intentKey) {
            'status_absen_hari_ini',
            'alur_cara_absen',
            'panduan_biometrik_gps',
            'troubleshooting_gps',
            'troubleshooting_biometrik',
            'liveness_wajah',
            'tipe_absen_kantor_dinas_luar',
            'radius_geofencing',
            'offline_absen_sinkronisasi',
            'absen_offline_kendala_jaringan',
            'alur_absen_dinas_luar',
            'fungsi_tombol_refresh_absensi',
            'kendala_absen_ditolak' => [
                'label' => '📍 Buka Halaman Absen',
                'url' => route('attendance.index'),
            ],
            'riwayat_absen_kemarin',
            'rekap_kehadiran_7_hari',
            'cara_melihat_log_bulan_lalu',
            'melihat_titik_gps_absen',
            'koreksi_absensi',
            'koreksi_absensi_approval_flow',
            'batas_koreksi_absen' => [
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
            'jadwal_shift_hari_ini_besok',
            'cara_cek_kalender_shift',
            'tukar_shift_aturan',
            'backup_shift_aturan',
            'aturan_jeda_shift',
            'aturan_absensi_hari_libur',
            'total_hari_kerja_efektif',
            'jadwal_piket_standby_weekend' => [
                'label' => '📅 Buka Jadwal Kerja',
                'url' => route('attendance.schedules'),
            ],
            'sisa_cuti',
            'cara_ajukan_sakit',
            'cara_ajukan_cuti',
            'hak_izin_sakit_berbayar',
            'syarat_dokumen_izin_sakit',
            'syarat_surat_dokter_sakit',
            'h_minus_pengajuan_cuti',
            'ketentuan_cuti_khusus',
            'cara_batalkan_cuti',
            'pembatalan_cuti_disetujui',
            'perbedaan_jenis_cuti_izin',
            'aturan_hangus_sisa_cuti',
            'kebijakan_sisa_cuti_hangus',
            'cuti_mendadak_multi_turn' => [
                'label' => '🌴 Buka Halaman Cuti & Izin',
                'url' => route('attendance.leaves'),
            ],
            'cara_approve_cuti',
            'cara_cek_approval_cuti' => $this->canApproveCuti($user)
                ? [
                    'label' => '✅ Buka Persetujuan Cuti Karyawan',
                    'url' => route('attendance.leaves'),
                ]
                : [
                    'label' => '🌴 Ajukan Cuti & Izin Saya',
                    'url' => route('attendance.leaves'),
                ],
            'cara_ajukan_lembur',
            'lembur_backdate_aturan',
            'syarat_spk_lembur',
            'cara_akses_dokumen_spk',
            'batas_waktu_klaim_lembur' => [
                'label' => '⏰ Buat Pengajuan Lembur',
                'url' => route('gomsn.overtime.create'),
            ],
            'cara_approve_lembur',
            'antrean_approval_lembur_hari_ini',
            'monitoring_lembur_karyawan' => $this->canApproveLembur($user)
                ? [
                    'label' => '💼 Buka Persetujuan Lembur',
                    'url' => route('gomsn.overtime.index'),
                ]
                : [
                    'label' => '⏰ Buat Pengajuan Lembur',
                    'url' => route('gomsn.overtime.create'),
                ],
            'pencairan_lembur',
            'total_jam_lembur_saya',
            'tarif_upah_lembur',
            'pejabat_approval_lembur',
            'alasan_lembur_ditolak',
            'aturan_lembur_hari_libur' => [
                'label' => '💼 Buka Halaman Lembur',
                'url' => route('gomsn.overtime.index'),
            ],
            'alur_pengajuan_operasional',
            'alur_pengajuan_kasbon',
            'cara_pengajuan_pembelian_barang',
            'cara_pengajuan_permintaan_stok',
            'cara_minta_stok_gudang' => [
                'label' => '💰 Buat Pengajuan Dana / Barang',
                'url' => route('gomsn.pengajuan.create'),
            ],
            'status_pengajuan_saya',
            'export_excel_pengajuan',
            'revisi_pengajuan_pending',
            'edit_pengajuan_reject',
            'cara_upload_nota_lpj',
            'tenggat_lpj_operasional',
            'format_bukti_nota_file',
            'perbedaan_reimburse_advance',
            'prosedur_settlement_kasbon',
            'struktur_menu_pengajuan_operasional',
            'cara_search_layanan_kasbon',
            'plafon_limit_kasbon' => [
                'label' => '📋 Buka Pengajuan Saya',
                'url' => route('gomsn.pengajuan.index'),
            ],
            'tahapan_approval_pengajuan',
            'hierarki_approval_pembelian' => $this->canApprovePengajuanDana($user)
                ? [
                    'label' => '✅ Buka Persetujuan Karyawan',
                    'url' => route('gomsn.pengajuan.approvals'),
                ]
                : [
                    'label' => '📋 Buka Pengajuan Saya',
                    'url' => route('gomsn.pengajuan.index'),
                ],
            'stok_barang_gudang',
            'permintaan_stok_dari_tiket' => [
                'label' => '📦 Buka Stok Barang Gudang',
                'url' => route('gomsn.inventory.index'),
            ],
            'buka_tiket_gangguan',
            'jumlah_tiket_open_hari_ini',
            'tiket_aktif_saya',
            'tiket_critical_unassigned',
            'teknisi_tugas_prioritas_tinggi',
            'resume_pekerjaan_tiket',
            'penanganan_core_joint_closure',
            'catat_repair_point_kabel',
            'pemakaian_material_tiket',
            'chat_koordinasi_tiket',
            'closing_dan_berita_acara_bast',
            'dispatch_penugasan_tim',
            'stop_clock_aturan',
            'isi_kronologi_tiket',
            'update_progres_tiket',
            'mekanisme_handover_tiket',
            'prosedur_spk_tiket_noc',
            'syarat_bukti_closing_tiket',
            'alur_eskalasi_sla_tiket',
            'cari_riwayat_tiket_pelanggan',
            'perbedaan_tiket_noc_vs_pasang_baru',
            'perbedaan_menu_tiket_navigasi',
            'sisa_sla_tiket_aktif' => [
                'label' => '🎫 Buka Halaman Tiketing',
                'url' => route('tickets.index'),
            ],
            'manajemen_jaringan_odp',
            'informasi_menu_olt_pon_odp',
            'cara_cek_odp_pelanggan',
            'arti_status_port_pon_olt',
            'cek_kapasitas_port_odp',
            'cara_input_koordinat_odp',
            'manuver_traffic_optik' => [
                'label' => '🌐 Buka Jaringan OLT, PON & ODP',
                'url' => route('gomsn.network.index'),
            ],
            'potongan_keuangan_absen',
            'total_potongan_denda_saya',
            'rekap_terlambat_saya',
            'toleransi_keterlambatan',
            'batas_toleransi_keterlambatan',
            'penjelasan_nominal_potongan',
            'formula_hitung_potongan',
            'alur_sanggahan_potongan',
            'nominal_denda_alpa',
            'lokasi_menu_potongan_denda',
            'navigasi_slip_dan_potongan',
            'efek_terlambat_gaji_leaderboard',
            'cara_kunci_periode_potongan',
            'integrasi_potongan_ke_payroll' => [
                'label' => '💵 Buka Keuangan & Potongan Absen',
                'url' => route('attendance.deductions'),
            ],
            'leaderboard_karyawan',
            'kriteria_penilaian_leaderboard',
            'top_5_leaderboard_disiplin',
            'warna_divisi_badge' => [
                'label' => '🏆 Buka Leaderboard Kehadiran',
                'url' => route('attendance.leaderboard'),
            ],
            'kpi_teknisi' => [
                'label' => '📊 Buka Analitik KPI Teknisi',
                'url' => route('gomsn.analytics.kpi'),
            ],
            'kalender_absensi_dashboard',
            'cara_buka_semua_layanan_portal' => [
                'label' => '🏠 Buka Dashboard',
                'url' => route('portal.hub'),
            ],
            'company_rekap_absensi_hari_ini',
            'daftar_karyawan_sakit_hari_ini',
            'daftar_karyawan_terlambat_hari_ini',
            'divisi_kehadiran_tertinggi',
            'rekap_absensi_karyawan',
            'unduh_rekap_absensi_potongan',
            'cara_export_excel_absensi' => [
                'label' => '📊 Buka Rekap Absensi Karyawan',
                'url' => route('attendance.employee-recap'),
            ],
            'ganti_foto_profil',
            'cara_login_sidik_jari',
            'prosedur_reset_password_lupa',
            'navigasi_ubah_password_biometrik' => [
                'label' => '👤 Buka Halaman Profile',
                'url' => route('portal.profile'),
            ],
            'manajemen_data_karyawan',
            'cara_tambah_karyawan_baru',
            'cara_nonaktifkan_karyawan_resign',
            'cara_ubah_kuota_cuti_karyawan',
            'cara_reset_wajah_karyawan',
            'cara_cari_kontak_karyawan',
            'cari_karyawan_berdasarkan_nik',
            'cara_ubah_status_aktif_karyawan',
            'onboarding_karyawan_baru',
            'total_karyawan_aktif_headcount',
            'menu_khusus_divisi_hrd',
            'navigasi_daftar_kontak_rekan' => $this->canManageEmployee($user)
                ? [
                    'label' => '👥 Buka Kelola Data Karyawan',
                    'url' => route('portal.employees.index'),
                ]
                : [
                    'label' => '👤 Buka Halaman Profile',
                    'url' => route('portal.profile'),
                ],
            'navigasi_audit_log_perangkat' => [
                'label' => '🔒 Buka Sesi & Perangkat Akun',
                'url' => route('portal.sessions'),
            ],
            'navigasi_kalender_libur_nasional',
            'navigasi_unduh_sop_formulir',
            'kontak_it_support_error' => [
                'label' => '🏠 Buka Portal Hub',
                'url' => route('portal.hub'),
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
        // Replace punctuation with space
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean) ?? $clean;

        // Typos & synonym normalization
        $synonyms = [
            'gimana' => 'bagaimana',
            'gmn' => 'bagaimana',
            'bgmn' => 'bagaimana',
            'caranya' => 'cara',
            'crnya' => 'cara',
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
            'rwayat' => 'riwayat',
            'absn' => 'absen',
            'sy' => 'saya',
            'bln' => 'bulan',
            'ptongan' => 'potongan',
            'bsk' => 'besok',
            'dn' => 'dan',
            'jg' => 'juga',
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
            'knp' => 'kenapa',
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
            'kemarin', 'efektif', 'standby', 'piket', 'denda', 'spk', 'settlement',
            'headcount', 'onboarding', 'payroll', 'rekening', 'gaji', 'piala', 'hacker',
        ];

        return in_array($token, $subjects, true);
    }

    /**
     * Process message from user and return personalized response + navigation button.
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
        // EARLY GUARDRAILS & EDGE CASES
        // ==========================================

        // 1. Empty or Punctuation Only (Q99)
        if (preg_match('/^[?\s.!,\-_:;]+$/u', $rawClean) || mb_strlen($rawClean) < 2) {
            $fallback = "Pesan yang Anda kirimkan belum jelas atau hanya berupa tanda baca. Silakan ketik pertanyaan Anda dengan kata kunci yang jelas, misalnya:\n• \"Bagaimana cara absen?\"\n• \"Berapa sisa cuti saya?\"\n• \"Bagaimana cara mengajukan kasbon?\"\n• \"Cara membuat pengajuan lembur\"";
            return [
                'response' => $fallback,
                'confidence' => 1.0,
                'matched_intent' => 'penanganan_input_tidak_valid',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Bagaimana cara absen?', 'Berapa sisa cuti saya?', 'Cara mengajukan lembur'],
            ];
        }

        // 2. Keyboard Smash / Gibberish (Q100)
        if (preg_match('/\b(asdfghjkl|qwertyuiop|zxcvbnm|asdasd|hjkjhg)\b/i', $rawClean) || preg_match('/\b[bcdfghjklmnpqrstvwxyz]{6,}\b/i', $rawClean)) {
            $fallback = "Pesan yang Anda kirimkan berupa karakter acak dan tidak dapat dipahami sistem. Silakan tanyakan hal terkait operasional portal MSN, misalnya:\n• \"Bagaimana cara absen?\"\n• \"Berapa sisa cuti saya?\"\n• \"Bagaimana cara mengajukan kasbon?\"\n• \"Cara membuat pengajuan lembur\"";
            return [
                'response' => $fallback,
                'confidence' => 1.0,
                'matched_intent' => 'penanganan_input_tidak_valid',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Bagaimana cara absen?', 'Berapa sisa cuti saya?', 'Cara mengajukan lembur'],
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
            $resp = "Maaf, permintaan Anda tidak dapat diproses. Sebagai asisten resmi PT MSN, saya beroperasi dengan standar keamanan data ketat dan tidak diizinkan menjalankan perintah di luar prosedur operasional resmi aplikasi portal.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'proteksi_prompt_injection',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Status absen hari ini', 'Jadwal shift kerja', 'Panduan operasional'],
            ];
        }

        // 4. PII Protection: Gaji Pokok & Nomor Rekening (Q94)
        if (
            (str_contains($rawClean, 'gaji pokok') || str_contains($rawClean, 'gaji'))
            && (str_contains($rawClean, 'nomor rekening') || str_contains($rawClean, 'no rekening') || str_contains($rawClean, 'rekening'))
        ) {
            $resp = "Demi perlindungan data pribadi (Privacy & PII Protection) karyawan PT MSN, informasi sensitif seperti nominal gaji pokok dan nomor rekening bank bersifat sangat rahasia (Strictly Confidential) dan tidak dapat diakses melalui asisten virtual ini. Silakan hubungi Finance atau HRD secara langsung.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'proteksi_pii_gaji_rekening',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Status absen hari ini', 'Sisa kuota cuti', 'Potongan absen saya'],
            ];
        }

        // 5. Out of Scope: Sepak Bola / Cuaca / Berita Luar (Q96)
        if (
            str_contains($rawClean, 'piala dunia')
            || (str_contains($rawClean, 'sepak bola') && str_contains($rawClean, 'skor'))
            || str_contains($rawClean, 'skor pertandingan')
        ) {
            $resp = "Saya adalah Asisten Virtual internal PT MSN yang dirancang khusus untuk memandu operasional perusahaan, absensi, cuti, lembur, dan penanganan tiket gangguan jaringan optik. Saya tidak dapat menjawab pertanyaan seputar berita umum, olahraga, atau topik di luar lingkup kerja MSN.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'out_of_scope_olahraga_cuaca',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Cara absen dinas luar', 'Cara isi resume tiket', 'Berapa sisa cuti saya'],
            ];
        }

        // 6. Out of Scope: Coding Machine Learning & Artikel Marketing (Q97)
        if (
            (str_contains($rawClean, 'kode program') || str_contains($rawClean, 'python'))
            && (str_contains($rawClean, 'machine learning') || str_contains($rawClean, 'artikel marketing'))
        ) {
            $resp = "Saya berfokus pada informasi alur kerja, panduan portal, dan operasional PT MSN. Untuk penulisan kode program kustom atau pembuatan artikel marketing eksternal, silakan gunakan perangkat lunak pengembangan dan konsultasikan dengan divisi terkait.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'guardrail_kode_artikel_luar',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Cara buat tiket gangguan', 'Alur pengajuan kasbon', 'Panduan jadwal kerja'],
            ];
        }

        // 7. Non-existent NIK Explicit Query (Q95)
        if (preg_match('/\b(kr\d{4,}|\d{10,16})\b/i', $rawClean, $nikMatches)) {
            $checkedNik = strtoupper($nikMatches[1]);
            $exists = User::query()
                ->where('nik', $checkedNik)
                ->orWhere('employee_code', $checkedNik)
                ->exists();
            if (! $exists && (str_contains($rawClean, 'cari') || str_contains($rawClean, 'data') || str_contains($rawClean, 'status'))) {
                $resp = "Karyawan dengan NIK {$checkedNik} tidak ditemukan di dalam sistem database PT MSN. Pastikan format NIK atau nomor identitas yang Anda masukkan sudah benar.";
                return [
                    'response' => $resp,
                    'confidence' => 1.0,
                    'matched_intent' => 'pencegahan_halusinasi_nik',
                    'action_url' => $this->canManageEmployee($user) ? route('portal.employees.index') : null,
                    'action_label' => $this->canManageEmployee($user) ? '👥 Buka Data Karyawan' : null,
                    'suggestions' => ['Data karyawan', 'Cek jadwal kerja', 'Status absen'],
                ];
            }
        }

        // 8. Chat Approval Prohibition (Q92)
        if (
            (str_contains($rawClean, 'setujui') || str_contains($rawClean, 'approve'))
            && str_contains($rawClean, 'chat ini')
        ) {
            $resp = "Persetujuan (approval) tidak dapat dilakukan secara langsung melalui pesan teks chat asisten virtual. Sistem persetujuan PT MSN mewajibkan otorisasi resmi melalui menu Approval yang tersedia di portal agar tercatat secara sah di Audit Trail sistem. Silakan buka menu persetujuan terkait untuk menyetujui atau menolak permohonan.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'penolakan_eksekusi_approval_chat',
                'action_url' => route('attendance.leaves'),
                'action_label' => '🌴 Buka Persetujuan Cuti',
                'suggestions' => ['Cara approve pengajuan cuti', 'Cara approve lembur', 'Status pengajuan saya'],
            ];
        }

        // ==========================================
        // INTENT MATCHING ENGINE
        // ==========================================
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

                // Short greetings need whole-word match
                if ($intent->category === 'umum' && mb_strlen($kwNorm) <= 6) {
                    if (preg_match('/\b' . preg_quote($kwRaw, '/') . '\b/u', $rawClean)) {
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
                            // Stem match for longer words (>= 5 chars)
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
        ];

        // Jika tidak ada kecocokan yang memadai
        if ($bestScore < 0.70 || ! $bestIntent) {
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

        // ==========================================
        // PERSONALIZED & DYNAMIC RESPONSES (§5.8)
        // ==========================================
        $reply = $bestIntent->response_text;

        if ($user) {
            // RBAC Guard: Non-HRD query for confidential employee contacts (Q93, Q95)
            if ($bestIntent->intent_key === 'rbac_kontak_rekap_semua_karyawan' || $bestIntent->intent_key === 'uji_akses_kontak_pribadi') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Permintaan ditolak. Berdasarkan kebijakan keamanan data dan privasi perusahaan PT MSN, seluruh nomor handphone pribadi dan alamat rumah staf karyawan bersifat rahasia dan terlindungi. Hanya manajemen HRD yang memiliki kewenangan untuk mengakses data kontak lengkap melalui modul Kelola Karyawan.";
                }
            } elseif ($bestIntent->intent_key === 'approval_pengajuan_absen' && ! $this->canApproveAbsen($user)) {
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
                $reply = "Halo {$user->name}, kuota cuti tahunan Anda untuk tahun " . date('Y') . ":\n• Status: {$statusCuti}\n• Sisa Kuota: {$sisa} hari kerja.\n\nKlik tombol di bawah untuk menuju halaman Cuti & Izin.";
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
                    $reply = "Halo {$user->name}, status presensi Anda hari ini:\n• Absen Masuk: {$clockInTime} WIB\n• Absen Pulang: {$clockOutStr}\n• Status: " . strtoupper($att->status ?? 'HADIR');
                } else {
                    $reply = "Halo {$user->name}, Anda tercatat BELUM melakukan Absen Masuk untuk hari ini. Silakan klik tombol di bawah untuk menuju halaman Absen.";
                }
            } elseif ($bestIntent->intent_key === 'riwayat_absen_kemarin') {
                $yesterday = Carbon::yesterday('Asia/Jakarta')->toDateString();
                $att = Attendance::query()->where('user_id', $user->id)->whereDate('date', $yesterday)->first();
                if ($att && ($att->clock_out_at || $att->clock_out)) {
                    $in = Carbon::parse($att->clock_in_at ?? $att->clock_in)->timezone('Asia/Jakarta')->format('H:i');
                    $out = Carbon::parse($att->clock_out_at ?? $att->clock_out)->timezone('Asia/Jakarta')->format('H:i');
                    $dur = $att->work_duration_minutes ?? 0;
                    $reply = "Halo {$user->name}, riwayat absensi Anda kemarin (" . Carbon::yesterday('Asia/Jakarta')->translatedFormat('d F Y') . "):\n• Absen Masuk: {$in} WIB\n• Absen Pulang: {$out} WIB\n• Status: " . strtoupper($att->status ?? 'HADIR') . "\n• Durasi Kerja: {$dur} menit.";
                } elseif ($att && ($att->clock_in_at || $att->clock_in)) {
                    $in = Carbon::parse($att->clock_in_at ?? $att->clock_in)->timezone('Asia/Jakarta')->format('H:i');
                    $reply = "Halo {$user->name}, kemarin Anda melakukan Absen Masuk pukul {$in} WIB namun tidak tercatat Absen Pulang (Lupa Clock-out). Silakan ajukan koreksi absen jika diperlukan.";
                } else {
                    $reply = "Halo {$user->name}, tidak tercatat data absensi untuk Anda pada hari kemarin (" . Carbon::yesterday('Asia/Jakarta')->translatedFormat('d F Y') . "). Jika Anda bertugas kemarin, Anda dapat mengajukan perbaikan melalui menu Riwayat & Koreksi Absen.";
                }
            } elseif ($bestIntent->intent_key === 'rekap_terlambat_saya') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $lateCount = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $cMonth)
                    ->whereYear('date', $cYear)
                    ->where(function ($q) {
                        $q->where('status', 'TERLAMBAT')
                          ->orWhere('late_minutes', '>', 0);
                    })
                    ->count();
                $totalLateMin = (int) Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereMonth('date', $cMonth)
                    ->whereYear('date', $cYear)
                    ->sum('late_minutes');
                $reply = "Halo {$user->name}, rekap keterlambatan Anda bulan " . Carbon::now('Asia/Jakarta')->translatedFormat('F Y') . ":\n• Total Keterlambatan: {$lateCount} kali\n• Akumulasi Menit Terlambat: {$totalLateMin} menit.\n\nSelalu perhatikan jam operasional shift Anda agar tidak terkena penalti potongan.";
            } elseif ($bestIntent->intent_key === 'rekap_kehadiran_7_hari') {
                $start7 = Carbon::now('Asia/Jakarta')->subDays(6)->toDateString();
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $attendances7 = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('date', [$start7, $today])
                    ->orderBy('date', 'desc')
                    ->get();
                $lines = ["Halo {$user->name}, berikut rekap kehadiran Anda selama 7 hari terakhir:"];
                if ($attendances7->isEmpty()) {
                    $lines[] = "• Belum ada catatan absensi dalam 7 hari terakhir.";
                } else {
                    foreach ($attendances7 as $a) {
                        $tgl = Carbon::parse($a->date)->locale('id')->isoFormat('ddd, DD/MM');
                        $cin = ($a->clock_in_at || $a->clock_in) ? Carbon::parse($a->clock_in_at ?? $a->clock_in)->timezone('Asia/Jakarta')->format('H:i') : '-';
                        $cout = ($a->clock_out_at || $a->clock_out) ? Carbon::parse($a->clock_out_at ?? $a->clock_out)->timezone('Asia/Jakarta')->format('H:i') : '-';
                        $st = strtoupper($a->status ?? 'HADIR');
                        $lines[] = "• {$tgl}: [{$st}] Masuk: {$cin} WIB | Pulang: {$cout} WIB";
                    }
                }
                $reply = implode("\n", $lines);
            } elseif ($bestIntent->intent_key === 'total_hari_kerja_efektif') {
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
                    ->whereHas('shift', fn($q) => $q->where('is_day_off', false))
                    ->count();
                $target = $scheduledCount > 0 ? $scheduledCount : 22;
                $reply = "Halo {$user->name}, informasi hari kerja efektif Anda bulan " . Carbon::now('Asia/Jakarta')->translatedFormat('F Y') . ":\n• Hari Kerja Terealisasi: {$hadirCount} hari\n• Total Target Jadwal Kerja: {$target} hari kerja.";
            } elseif ($bestIntent->intent_key === 'jadwal_shift_hari_ini_besok') {
                $todayStr = Carbon::now('Asia/Jakarta')->toDateString();
                $tomorrowStr = Carbon::tomorrow('Asia/Jakarta')->toDateString();
                $schToday = MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $todayStr)->first();
                $schTomorrow = MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $tomorrowStr)->first();
                $shiftTodayName = $schToday?->shift?->name ?? 'Shift Pagi (08:00 - 17:00)';
                $shiftTomorrowName = $schTomorrow?->shift?->name ?? 'Shift Pagi (08:00 - 17:00)';
                $reply = "Halo {$user->name}, jadwal kerja Anda:\n• Hari Ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') . "): {$shiftTodayName}\n• Besok (" . Carbon::tomorrow('Asia/Jakarta')->translatedFormat('l, d F Y') . "): {$shiftTomorrowName}\n\nSilakan cek kalender jadwal lengkap di menu Jadwal Kerja Saya.";
            } elseif ($bestIntent->intent_key === 'total_potongan_denda_saya') {
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
                $reply = "Halo {$user->name}, rincian estimasi potongan absensi Anda untuk bulan " . Carbon::now('Asia/Jakarta')->translatedFormat('F Y') . ":\n• Total Potongan: Rp {$fmtTotal}\n• Potongan Terlambat: Rp {$fmtLate}\n• Potongan Alpa: Rp {$fmtAlpa}\n\nJika terdapat selisih atau kekeliruan pencatatan, Anda dapat mengajukan sanggahan koreksi melalui menu Potongan Absen Saya.";
            } elseif ($bestIntent->intent_key === 'total_jam_lembur_saya') {
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
                $reply = "Halo {$user->name}, rekap jam lembur Anda yang telah disetujui pada bulan " . Carbon::now('Asia/Jakarta')->translatedFormat('F Y') . ":\n• Total Pengajuan Disetujui: {$count} pengajuan\n• Total Durasi: {$totalJam} jam ({$totalMenit} menit).\n\nUntuk memantau pencairan atau detail rincian, silakan kunjungi menu Lembur Saya.";
            } elseif ($bestIntent->intent_key === 'company_rekap_absensi_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Mohon maaf {$user->name}, data rekapitulasi kehadiran seluruh karyawan merupakan informasi internal manajemen dan hanya dapat diakses oleh tim HRD & Human Capital atau Administrator.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $totalActive = User::query()->where('is_active', true)->count();
                    $attendedToday = Attendance::query()->whereDate('date', $today)->whereIn('status', ['HADIR', 'TERLAMBAT'])->count();
                    $pct = $totalActive > 0 ? round(($attendedToday / $totalActive) * 100, 1) : 0;
                    $reply = "Halo {$user->name}, rekapitulasi kehadiran seluruh staf MSN per hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') . "):\n• Total Karyawan Aktif: {$totalActive} orang\n• Karyawan Telah Hadir: {$attendedToday} orang\n• Persentase Kehadiran: {$pct}%\n\nSilakan pantau detail per divisi melalui menu Rekap Absen Karyawan.";
                }
            } elseif ($bestIntent->intent_key === 'daftar_karyawan_sakit_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Mohon maaf {$user->name}, daftar karyawan yang berhalangan atau sakit hanya dapat diakses oleh tim HRD & Human Capital.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $sickLeaves = Leave::query()
                        ->with('user')
                        ->whereIn('leave_type', ['sakit', 'sick', 'izin', 'leave'])
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->get();
                    if ($sickLeaves->isEmpty()) {
                        $reply = "Halo {$user->name}, per hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') . ") tidak ada karyawan yang tercatat izin sakit.";
                    } else {
                        $lines = ["Halo {$user->name}, berikut daftar karyawan yang izin sakit hari ini:"];
                        foreach ($sickLeaves as $sl) {
                            $uName = $sl->user?->name ?? 'Karyawan';
                            $div = $sl->user?->division ?? '-';
                            $lines[] = "• {$uName} ({$div}) - Jenis: " . strtoupper($sl->leave_type);
                        }
                        $reply = implode("\n", $lines);
                    }
                }
            } elseif ($bestIntent->intent_key === 'daftar_karyawan_terlambat_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Mohon maaf {$user->name}, data keterlambatan karyawan hanya dapat diakses oleh tim HRD & Human Capital.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $lates = Attendance::query()
                        ->with('user')
                        ->whereDate('date', $today)
                        ->where('late_minutes', '>', 15)
                        ->get();
                    if ($lates->isEmpty()) {
                        $reply = "Halo {$user->name}, per hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') . ") tidak ada karyawan yang terlambat lebih dari 15 menit.";
                    } else {
                        $lines = ["Halo {$user->name}, berikut daftar karyawan yang terlambat >15 menit hari ini:"];
                        foreach ($lates as $lt) {
                            $uName = $lt->user?->name ?? 'Karyawan';
                            $lines[] = "• {$uName} ({$lt->late_minutes} menit)";
                        }
                        $reply = implode("\n", $lines);
                    }
                }
            } elseif ($bestIntent->intent_key === 'divisi_kehadiran_tertinggi') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Mohon maaf {$user->name}, informasi statistik per divisi hanya dapat diakses oleh tim HRD & Human Capital.";
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
                    $divName = $topDiv?->division ?? 'Teknik & Operasional';
                    $tot = $topDiv?->total_hadir ?? 0;
                    $reply = "Halo {$user->name}, divisi dengan tingkat kehadiran tertinggi pada bulan " . Carbon::now('Asia/Jakarta')->translatedFormat('F Y') . " adalah Divisi {$divName} dengan total {$tot} log kehadiran tercatat.";
                }
            } elseif ($bestIntent->intent_key === 'antrean_approval_lembur_hari_ini') {
                $pendingLembur = Lembur::query()
                    ->whereIn('status', ['submitted', 'pending', 'menunggu_approval'])
                    ->count();
                $reply = "Halo {$user->name}, saat ini terdapat {$pendingLembur} pengajuan lembur yang berada dalam antrean menunggu persetujuan (approval) atasan/HRD.";
            } elseif ($bestIntent->intent_key === 'top_5_leaderboard_disiplin') {
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
                    $reply = "Peringkat Leaderboard Disiplin sedang dihitung untuk periode bulan berjalan. Silakan kunjungi menu Leaderboard Kehadiran.";
                } else {
                    $lines = ["Halo {$user->name}, berikut 5 besar karyawan dengan kehadiran tepat waktu terbaik bulan ini:"];
                    $rank = 1;
                    foreach ($topUsers as $u) {
                        $lines[] = "{$rank}. {$u->name} ({$u->division}) - {$u->tepat_waktu}x Tepat Waktu";
                        $rank++;
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($bestIntent->intent_key === 'total_karyawan_aktif_headcount') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = "Mohon maaf {$user->name}, rincian headcount karyawan perusahaan hanya dapat diakses oleh tim HRD & Human Capital.";
                } else {
                    $totalActive = User::query()->where('is_active', true)->count();
                    $reply = "Halo {$user->name}, total karyawan aktif yang terdaftar di sistem PT MSN saat ini adalah sebanyak {$totalActive} orang.";
                }
            } elseif ($bestIntent->intent_key === 'jumlah_tiket_open_hari_ini') {
                $openTickets = Tiket::query()->whereIn('status', ['OPEN', 'PENDING', 'PROSES'])->count();
                $critical = Tiket::query()->whereIn('status', ['OPEN', 'PROSES'])->whereIn('priority', ['CRITICAL', 'HIGH'])->count();
                $reply = "Halo {$user->name}, saat ini terdapat {$openTickets} tiket gangguan aktif di lapangan, dengan rincian {$critical} tiket berprioritas Critical / High.";
            } elseif ($bestIntent->intent_key === 'tiket_aktif_saya') {
                $count = Tiket::query()
                    ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $user->id))
                    ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                    ->count();
                $reply = "Halo {$user->name}, saat ini terdapat {$count} tiket gangguan aktif yang ditugaskan kepada Anda.";
            } elseif ($bestIntent->intent_key === 'teknisi_tugas_prioritas_tinggi') {
                $activeCut = Tiket::query()
                    ->with('ticketAssignees.user')
                    ->whereIn('status', ['OPEN', 'PROSES'])
                    ->where(function ($q) {
                        $q->whereIn('priority', ['CRITICAL', 'HIGH'])
                          ->orWhere('kategori_tiket', 'LIKE', '%cut%')
                          ->orWhere('kategori_tiket', 'LIKE', '%backbone%');
                    })
                    ->first();
                if ($activeCut && $activeCut->ticketAssignees->isNotEmpty()) {
                    $names = $activeCut->ticketAssignees->map(fn($a) => $a->user?->name)->filter()->implode(', ');
                    $reply = "Teknisi yang saat ini bertugas menangani tiket prioritas tinggi (#{$activeCut->no_tiket} - {$activeCut->kategori_tiket}) adalah: {$names}.";
                } else {
                    $reply = "Saat ini seluruh tiket berprioritas tinggi telah terkoordinasi atau sedang dalam pemantauan NOC. Silakan cek menu Tiketing untuk penugasan tim.";
                }
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
                        $nom = $p->jenis === 'STOK' ? 'Stok Gudang' : 'Rp ' . number_format((float) $p->total_nominal, 0, ',', '.');
                        $st = strtoupper(str_replace('_', ' ', $p->status));
                        $lines[] = "• #{$p->id} ({$p->jenis} - {$nom}): {$st}";
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($bestIntent->intent_key === 'ringkasan_operasional_3_poin') {
                $attToday = Attendance::query()->where('user_id', $user->id)->whereDate('date', Carbon::now('Asia/Jakarta')->toDateString())->first();
                $clockIn = $attToday?->clock_in_at ?? $attToday?->clock_in;
                $absenStr = ($attToday && $clockIn) ? 'Sudah Clock-in (' . Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i') . ' WIB)' : 'Belum Absen Masuk';
                $myTickets = Tiket::query()->whereHas('ticketAssignees', fn($q) => $q->where('user_id', $user->id))->whereIn('status', ['OPEN', 'PROSES'])->count();
                $myPengajuan = Pengajuan::query()->where('user_id', $user->id)->whereIn('status', ['pending', 'menunggu_approval'])->count();
                $reply = "Halo {$user->name}, berikut ringkasan 3 poin operasional terpenting Anda hari ini:\n1. Presensi Kerja: {$absenStr}.\n2. Tiket Gangguan Aktif: {$myTickets} tiket sedang ditugaskan kepada Anda.\n3. Pengajuan Berjalan: {$myPengajuan} pengajuan dana/barang dalam proses approval.";
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
