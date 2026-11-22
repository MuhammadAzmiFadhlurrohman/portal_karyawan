<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Lembur;
use App\Models\MasterMaterial;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\Odp;
use App\Models\OdpCustomer;
use App\Models\Olt;
use App\Models\Pengajuan;
use App\Models\Pon;
use App\Models\Task;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use App\Services\DashboardStatsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Intelligent NLU, Conversation Context, Live Data, and App Guide Resolver for GOMSN Chatbot.
 * Works 100% locally with zero external API dependencies, understanding short (1-2 word)
 * and long (multi-sentence/paragraph) queries in Indonesian, casual slang, and Basa Sunda.
 */
class ChatbotSmartResolver
{
    public const PENGAJUAN_APPROVED_STATUSES = [
        'disetujui', 'ditransfer', 'terealisasi', 'dicairkan', 'menunggu_bukti', 'selesai',
    ];

    public const PENGAJUAN_PENDING_STATUSES = [
        'draft', 'menunggu_atasan', 'menunggu_finance', 'menunggu_executive', 'menunggu_direksi', 'pending', 'menunggu_approval',
    ];

    public const LEMBUR_APPROVED_STATUSES = ['approved_atasan', 'approved', 'paid'];

    public const ATTENDANCE_PRESENT_STATUSES = ['present', 'late', 'HADIR', 'TERLAMBAT'];
    public const ATTENDANCE_ONTIME_STATUSES = ['present', 'HADIR'];
    public const ATTENDANCE_LATE_STATUSES = ['late', 'TERLAMBAT'];
    public const ATTENDANCE_ALPA_STATUSES = ['alpa', 'ALPA'];

    /**
     * Domain keyword map for semantic domain detection (Indonesian, English, Basa Sunda).
     *
     * @var array<string, list<string>>
     */
    protected array $domainKeywords = [
        'aplikasi' => ['gomsn', 'aplikasi', 'portal', 'sistem', 'fitur', 'modul', 'panduan', 'bantuan', 'help', 'fungsi', 'kegunaan', 'menu', 'layanan', 'semua fitur', 'cara kerja'],
        'dashboard' => ['dashboard', 'beranda', 'home', 'ringkasan', 'statistik', 'summary', 'keseluruhan', 'operasional'],
        'absensi' => ['absen', 'absensi', 'presensi', 'kehadiran', 'checkin', 'checkout', 'clock', 'biometrik', 'wajah', 'beungeut', 'gps', 'radius', 'geofence', 'koreksi', 'terlambat', 'telat', 'alpa', 'dinas', 'hadir', 'kamera', 'selfie'],
        'jadwal' => ['jadwal', 'shift', 'piket', 'kalender', 'tukar', 'schedule', 'jam kerja', 'jam masuk', 'jam pulang', 'libur nasional', 'tanggal merah'],
        'cuti' => ['cuti', 'izin', 'sakit', 'gering', 'saldo', 'jatah', 'kuota', 'dokter', 'skd', 'melahirkan', 'leave'],
        'potongan' => ['potongan', 'denda', 'sanggahan', 'payroll', 'deduction', 'waiver'],
        'pengajuan' => ['pengajuan', 'kasbon', 'dana', 'beli', 'pembelian', 'pengadaan', 'permintaan', 'reimburse', 'reimbursement', 'nota', 'lpj', 'settlement', 'cair', 'pencairan', 'nominal', 'anggaran', 'plafon'],
        'stok' => ['stok', 'stock', 'gudang', 'material', 'inventaris', 'inventory', 'barang', 'dropcore', 'kabel', 'closure', 'patch', 'utp', 'ont', 'restock'],
        'lembur' => ['lembur', 'lemburan', 'overtime', 'spk'],
        'tiket' => ['tiket', 'ticket', 'gangguan', 'trouble', 'noc', 'backbone', 'broadband', 'sla', 'mttr', 'eskalasi', 'closing', 'reopen', 'redaman', 'opm', 'otdr', 'splicing', 'jc', 'core', 'manuver', 'dispatch', 'resume', 'bast', 'berita acara'],
        'jaringan' => ['olt', 'pon', 'odp', 'ftth', 'jaringan', 'network', 'port', 'koordinat', 'pelanggan', 'customer', 'topologi', 'internet'],
        'tugas' => ['tugas', 'task', 'tasks', 'kerjaan', 'subtask'],
        'kpi' => ['kpi', 'performa', 'kinerja', 'nilai teknisi', 'skor teknisi', 'analytics', 'analitik'],
        'leaderboard' => ['leaderboard', 'peringkat', 'ranking', 'juara', 'disiplin', 'terbaik', 'top'],
        'karyawan' => ['karyawan', 'pegawai', 'staf', 'staff', 'headcount', 'onboarding', 'resign', 'nonaktif', 'mutasi', 'divisi', 'kontak', 'nik', 'kode karyawan'],
        'profil' => ['profil', 'profile', 'akun', 'password', 'sandi', 'foto', 'passkey', 'atasan', 'jabatan', 'role', 'hak akses', 'wewenang'],
        'sesi' => ['sesi', 'session', 'perangkat', 'device', 'otp', 'login', 'logout', 'hp', 'blokir'],
    ];

    /**
     * Light Indonesian & Sundanese stemmer for semantic token matching.
     */
    public function stemToken(string $word): string
    {
        $w = mb_strtolower(trim($word));
        if (mb_strlen($w) <= 3) {
            return $w;
        }

        // Preserve exact core domain tokens so stemming never corrupts them
        static $protected = [
            'absen', 'cuti', 'izin', 'sakit', 'lembur', 'tiket', 'kasbon', 'dana', 'beli', 'stok',
            'shift', 'jadwal', 'telat', 'alpa', 'denda', 'gaji', 'nota', 'saldo', 'kuota', 'jatah',
            'dinas', 'luar', 'wajah', 'muka', 'foto', 'profil', 'akun', 'sandi', 'pass', 'login',
            'logout', 'reset', 'edit', 'hapus', 'batal', 'cair', 'lunas', 'tolak', 'selesai',
            'aktif', 'hadir', 'pulang', 'masuk', 'kerja', 'kantor', 'libur', 'minggu', 'sabtu',
            'bulan', 'tahun', 'hari', 'besok', 'kemarin', 'kabel', 'gudang', 'barang', 'material',
            'tugas', 'task', 'divisi', 'atasan', 'karyawan', 'teknisi', 'admin', 'finance', 'direksi',
            'pelanggan', 'jaringan', 'redaman', 'port', 'core', 'closure', 'splicing', 'koordinat',
            'gomsn', 'portal', 'menu', 'fitur', 'modul', 'data', 'rekap', 'status', 'total',
            'jumlah', 'daftar', 'cara', 'alur', 'proses', 'syarat', 'batas', 'aturan', 'biometrik',
        ];
        if (in_array($w, $protected, true)) {
            return $w;
        }

        // Strip common enclitics/possessive suffixes (-nya, -na, -ku, -mu, -lah, -kah, -pun)
        foreach (['nya', 'lah', 'kah', 'pun', 'na', 'ku', 'mu'] as $suf) {
            if (mb_strlen($w) > mb_strlen($suf) + 3 && str_ends_with($w, $suf)) {
                $w = mb_substr($w, 0, -mb_strlen($suf));
                break;
            }
        }

        if (in_array($w, $protected, true)) {
            return $w;
        }

        // Normalize common morphological derivations to canonical roots
        $derivations = [
            'keterlambatan' => 'telat',
            'terlambat' => 'telat',
            'kehadiran' => 'hadir',
            'ketidakhadiran' => 'alpa',
            'perizinan' => 'izin',
            'mengajukan' => 'ajukan',
            'diajukan' => 'ajukan',
            'pengajuan' => 'pengajuan',
            'persetujuan' => 'setuju',
            'disetujui' => 'setuju',
            'menyetujui' => 'setuju',
            'disatujuan' => 'setuju',
            'penolakan' => 'tolak',
            'ditolak' => 'tolak',
            'pembatalan' => 'batal',
            'dibatalkan' => 'batal',
            'membatalkan' => 'batal',
            'pencairan' => 'cair',
            'dicairkan' => 'cair',
            'mencairkan' => 'cair',
            'pembelian' => 'beli',
            'membeli' => 'beli',
            'pengadaan' => 'beli',
            'permintaan' => 'permintaan',
            'meminta' => 'permintaan',
            'realisasi' => 'realisasi',
            'terealisasi' => 'realisasi',
            'pelunasan' => 'lunas',
            'dilunasi' => 'lunas',
            'lemburan' => 'lembur',
            'potongan' => 'potongan',
            'dipotong' => 'potongan',
            'memotong' => 'potongan',
            'absensi' => 'absen',
            'presensi' => 'absen',
            'penjadwalan' => 'jadwal',
            'terjadwal' => 'jadwal',
            'pertukaran' => 'tukar',
            'menukar' => 'tukar',
            'tukeran' => 'tukar',
            'perbaikan' => 'perbaikan',
            'penanganan' => 'penanganan',
            'pemakaian' => 'pakai',
            'penggunaan' => 'pakai',
            'menggunakan' => 'pakai',
            'penggantian' => 'ganti',
            'mengganti' => 'ganti',
            'perubahan' => 'ubah',
            'mengubah' => 'ubah',
            'penghapusan' => 'hapus',
            'menghapus' => 'hapus',
            'pembuatan' => 'buat',
            'membuat' => 'buat',
            'pengecekan' => 'cek',
            'mengecek' => 'cek',
            'pencarian' => 'cari',
            'mencari' => 'cari',
            'pelaporan' => 'lapor',
            'melaporkan' => 'lapor',
            'pengisian' => 'isi',
            'mengisi' => 'isi',
            'pengunggahan' => 'upload',
            'mengunggah' => 'upload',
            'diupload' => 'upload',
            'diunggah' => 'upload',
            'pengunduhan' => 'unduh',
            'mengunduh' => 'unduh',
            'terhubung' => 'hubung',
            'menyambung' => 'hubung',
            'nyambung' => 'hubung',
            'terdeteksi' => 'deteksi',
            'mendeteksi' => 'deteksi',
            'memindai' => 'pindai',
            'pemindaian' => 'pindai',
            'pekerjaan' => 'kerja',
            'bekerja' => 'kerja',
            'pagawean' => 'kerja',
            'kahadiran' => 'hadir',
        ];
        if (isset($derivations[$w])) {
            return $derivations[$w];
        }

        // Light prefix stripping for longer inflected words
        foreach (['meng', 'meny', 'men', 'mem', 'ber', 'ter', 'per', 'nge', 'nga', 'di', 'me'] as $pre) {
            if (mb_strlen($w) > mb_strlen($pre) + 3 && str_starts_with($w, $pre)) {
                $candidate = mb_substr($w, mb_strlen($pre));
                if (in_array($candidate, $protected, true)) {
                    return $candidate;
                }
            }
        }

        // Light suffix stripping (-kan, -an)
        foreach (['kan', 'an'] as $suf) {
            if (mb_strlen($w) > mb_strlen($suf) + 3 && str_ends_with($w, $suf)) {
                $candidate = mb_substr($w, 0, -mb_strlen($suf));
                if (in_array($candidate, $protected, true)) {
                    return $candidate;
                }
            }
        }

        return $w;
    }

    /**
     * Get short-term conversation context for multi-turn follow-ups.
     *
     * @return array{domain?: string, intent?: string, entity?: string}
     */
    public function getContext(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $ctx = Cache::get("chatbot_ctx:user:{$user->id}");

        return is_array($ctx) ? $ctx : [];
    }

    /**
     * Save conversation context for multi-turn follow-ups (15 minutes TTL).
     */
    public function saveContext(?User $user, ?string $domain, ?string $intent = null, ?string $entity = null): void
    {
        if (! $user || ! $domain) {
            return;
        }

        Cache::put("chatbot_ctx:user:{$user->id}", [
            'domain' => $domain,
            'intent' => $intent,
            'entity' => $entity,
            'updated_at' => now()->timestamp,
        ], now()->addMinutes(15));
    }

    /**
     * Infer primary domain from an intent_key or text.
     */
    public function inferDomainFromIntentKey(?string $intentKey): ?string
    {
        if (! $intentKey) {
            return null;
        }

        if (str_contains($intentKey, 'absen') || str_contains($intentKey, 'checkin') || str_contains($intentKey, 'gps') || str_contains($intentKey, 'biometrik') || str_contains($intentKey, 'terlambat') || str_contains($intentKey, 'kehadiran') || str_contains($intentKey, 'alpa')) {
            return 'absensi';
        }
        if (str_contains($intentKey, 'shift') || str_contains($intentKey, 'jadwal') || str_contains($intentKey, 'libur')) {
            return 'jadwal';
        }
        if (str_contains($intentKey, 'cuti') || str_contains($intentKey, 'izin') || str_contains($intentKey, 'sakit')) {
            return 'cuti';
        }
        if (str_contains($intentKey, 'potongan') || str_contains($intentKey, 'denda') || str_contains($intentKey, 'payroll')) {
            return 'potongan';
        }
        if (str_contains($intentKey, 'kasbon') || str_contains($intentKey, 'pengajuan') || str_contains($intentKey, 'beli') || str_contains($intentKey, 'pembelian')) {
            return 'pengajuan';
        }
        if (str_contains($intentKey, 'stok') || str_contains($intentKey, 'material') || str_contains($intentKey, 'gudang')) {
            return 'stok';
        }
        if (str_contains($intentKey, 'lembur') || str_contains($intentKey, 'spk')) {
            return 'lembur';
        }
        if (str_contains($intentKey, 'tiket') || str_contains($intentKey, 'sla') || str_contains($intentKey, 'redaman') || str_contains($intentKey, 'closure') || str_contains($intentKey, 'noc')) {
            return 'tiket';
        }
        if (str_contains($intentKey, 'olt') || str_contains($intentKey, 'pon') || str_contains($intentKey, 'odp') || str_contains($intentKey, 'network')) {
            return 'jaringan';
        }
        if (str_contains($intentKey, 'leaderboard')) {
            return 'leaderboard';
        }
        if (str_contains($intentKey, 'karyawan') || str_contains($intentKey, 'headcount') || str_contains($intentKey, 'nik') || str_contains($intentKey, 'onboarding')) {
            return 'karyawan';
        }
        if (str_contains($intentKey, 'profil') || str_contains($intentKey, 'password') || str_contains($intentKey, 'akses')) {
            return 'profil';
        }

        return null;
    }

    /**
     * Detect scored domains present in user message.
     *
     * @param list<string> $userTokens
     * @return array<string, float>
     */
    public function detectDomains(string $normMessage, array $userTokens): array
    {
        $stemmedTokens = array_map(fn ($t) => $this->stemToken($t), $userTokens);
        $allTokens = array_values(array_unique(array_merge($userTokens, $stemmedTokens)));
        $scores = [];

        foreach ($this->domainKeywords as $domain => $keywords) {
            $score = 0.0;
            foreach ($keywords as $kw) {
                if (str_contains($kw, ' ')) {
                    if (str_contains($normMessage, $kw)) {
                        $score += 3.0;
                    }
                } else {
                    foreach ($allTokens as $tok) {
                        if ($tok === $kw) {
                            $score += 2.0;
                            break;
                        }
                        if (mb_strlen($kw) >= 4 && mb_strlen($tok) >= 4 && levenshtein($tok, $kw) <= 1) {
                            $score += 1.5;
                            break;
                        }
                    }
                }
            }
            if ($score > 0) {
                $scores[$domain] = $score;
            }
        }

        arsort($scores);

        return $scores;
    }

    /**
     * Detect user's high-level query intent type.
     *
     * @return array{is_howto: bool, is_data: bool, is_troubleshoot: bool, is_policy: bool, is_concept: bool, is_company_scope: bool, is_personal_scope: bool}
     */
    public function detectQueryStyle(string $normMessage, string $rawClean): array
    {
        $isHowTo = (bool) preg_match('/\b(bagaimana|gimana|kumaha|cara|carana|langkah|tutorial|tutor|panduan|alur|proses|mekanisme|step|tahapan|cara kerja|cara pakai|cara menggunakan)\b/iu', $normMessage);
        $isData = (bool) preg_match('/\b(berapa|sabaraha|total|jumlah|rekap|ringkasan|summary|statistik|stat|laporan|data|daftar|list|cek|lihat|tampilkan|tampilin|sebutkan|sebutin|status|siapa|saha|sudah|geus|belum|acan|hari ini|poe ieu|kemarin|kamari|besok|isukan|bulan ini|bulan ieu|minggu ini|minggu ieu|tahun ini|terakhir|terbaru|aktif|pending|menunggu|disetujui|ditolak|sisa|saldo|ada berapa|kapan|iraha)\b/iu', $normMessage);
        $isTroubleshoot = (bool) preg_match('/\b(kenapa|mengapa|kunaon|naha|error|gagal|tidak bisa|teu bisa|kendala|masalah|trouble|bug|lupa|poho|kelupaan|hilang|leungit|mati|pareum|kosong|macet|blank|hideung|belum masuk|tidak terdeteksi)\b/iu', $normMessage);
        $isPolicy = (bool) preg_match('/\b(syarat|aturan|kebijakan|ketentuan|batas|maksimal|minimal|deadline|sla|boleh|wajib|toleransi|tarif|rate|formula|rumus|hitung|beda|perbedaan|bedana|hangus|plafon|berapa lama|berapa menit toleransi)\b/iu', $normMessage);
        $isConcept = (bool) preg_match('/\b(apa itu|naon eta|apa sih|fungsi|kegunaan|manfaat|arti|hartina|maksud|kepanjangan|singkatan|fitur|modul|menu apa saja|layanan apa saja|tentang aplikasi|tentang gomsn|bisa bantu apa|kamu bisa apa|bisa apa aja|bantuan|help)\b/iu', $normMessage . ' ' . $rawClean);
        $isCompanyScope = (bool) preg_match('/\b(seluruh karyawan|semua karyawan|sakabeh karyawan|semua pegawai|seluruh pegawai|perusahaan|company|company wide|semua divisi|sa perusahaan|seluruh divisi|per divisi|antar divisi|karyawan hari ini|karyawan bulan ini|semua staf|tim saya|bawahan|divisi saya)\b/iu', $normMessage);
        $isPersonalScope = (bool) preg_match('/\b(saya|aku|urang|abdi|pribadi|milik saya|punya saya|akun saya)\b/iu', $normMessage);

        return [
            'is_howto' => $isHowTo,
            'is_data' => $isData,
            'is_troubleshoot' => $isTroubleshoot,
            'is_policy' => $isPolicy,
            'is_concept' => $isConcept,
            'is_company_scope' => $isCompanyScope,
            'is_personal_scope' => $isPersonalScope,
        ];
    }

    /**
     * Check if message is a short referential follow-up relying on previous conversation context.
     *
     * @param list<string> $userTokens
     */
    public function augmentWithConversationContext(?User $user, string $normMessage, array $userTokens, array $detectedDomains): ?string
    {
        if (! empty($detectedDomains) || ! $user) {
            return null;
        }

        $ctx = $this->getContext($user);
        if (empty($ctx['domain'])) {
            return null;
        }

        $isFollowUp = (bool) preg_match('/\b(caranya|cara|carana|syaratnya|syarat|aturannya|aturan|batasnya|batas|maksimalnya|minimalnya|berapa lama|terus|lalu|kalau ditolak|mun ditolak|kalau lupa|mun poho|dimana menunya|menu mana|dimana|punya saya|milik saya|datanya|statusnya|bulan ini|hari ini|detailnya|selengkapnya|jelaskan|maksudnya|contohnya|siapa yang approve|siapa yang acc|kapan cair|gimana|bagaimana|kumaha)\b/iu', $normMessage);

        return $isFollowUp ? $ctx['domain'] : null;
    }

    /**
     * Safe route helper that never throws if a route name is absent.
     */
    protected function safeRoute(string $name, mixed $params = []): ?string
    {
        return Route::has($name) ? route($name, $params) : null;
    }

    protected function isCompanyViewer(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'admin', 'hrd', 'finance', 'ceo'])
            || $user->can('attendance.employee.manage');
    }

    protected function isDivisionApprover(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'manager', 'supervisor']);
    }

    /**
     * Step 1: Direct lookup for specific record IDs/codes (Pengajuan code, Tiket code, Material/Stok).
     *
     * @return ?array{response: string, confidence: float, matched_intent: string, action_url: ?string, action_label: ?string, suggestions: ?list<string>, domain: string}
     */
    public function resolveDirectEntityQuery(?User $user, string $rawClean, string $normMessage, bool $isSunda): ?array
    {
        // A. Specific Pengajuan Number Lookup (e.g. PND-20261122-0001, PBL-..., PST-..., PRM-...)
        if (preg_match('/\b((?:PND|PBL|PST|PRM)-[A-Z0-9\-]+)\b/i', $rawClean, $m)) {
            $kode = strtoupper($m[1]);
            $p = Pengajuan::with(['user', 'atasan'])->where('nomor_pengajuan', 'LIKE', "%{$kode}%")->first();
            if (! $p) {
                return [
                    'response' => $isSunda
                        ? "Data pengajuan kalayan nomor {$kode} teu kapanggih dina database sistem GOMSN."
                        : "Data pengajuan dengan nomor {$kode} tidak ditemukan di database sistem GOMSN.",
                    'confidence' => 95.0,
                    'matched_intent' => 'smart_pengajuan_code_not_found',
                    'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                    'action_label' => '📋 Buka Daftar Pengajuan',
                    'suggestions' => null,
                    'domain' => 'pengajuan',
                ];
            }

            // RBAC check
            $canView = ! $user
                || $p->user_id === $user->id
                || $p->atasan_id === $user->id
                || $this->isCompanyViewer($user)
                || ($this->isDivisionApprover($user) && strcasecmp((string) $p->user?->division, (string) $user->division) === 0);

            if (! $canView) {
                return [
                    'response' => $isSunda
                        ? "Akses Ditolak. Anjeun teu gaduh wewenang kanggo ningali rincian pengajuan {$p->nomor_pengajuan} milik karyawan divisi sejen."
                        : "Akses Ditolak. Anda tidak memiliki wewenang untuk melihat rincian pengajuan {$p->nomor_pengajuan} milik karyawan divisi lain.",
                    'confidence' => 95.0,
                    'matched_intent' => 'smart_pengajuan_rbac_denied',
                    'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                    'action_label' => '📋 Buka Pengajuan Saya',
                    'suggestions' => null,
                    'domain' => 'pengajuan',
                ];
            }

            $nom = in_array(strtoupper($p->jenis), ['STOK', 'PERMINTAAN'], true)
                ? 'Non-Kas (' . strtoupper($p->jenis) . ')'
                : 'Rp ' . number_format((float) $p->nominal_total, 0, ',', '.');
            $st = strtoupper(str_replace('_', ' ', (string) $p->status));
            $tgl = $p->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') ?? '-';

            return [
                'response' => "Detail Pengajuan {$p->nomor_pengajuan}:\n"
                    . "• Pemohon: " . ($p->user?->name ?? '-') . ' (' . ($p->user?->division ?? '-') . ")\n"
                    . "• Jenis: " . strtoupper($p->jenis) . "\n"
                    . "• Keperluan: " . ($p->judul ?: $p->keperluan ?: '-') . "\n"
                    . "• Nominal: {$nom}\n"
                    . "• Status: {$st}\n"
                    . "• Tanggal Pengajuan: {$tgl} WIB",
                'confidence' => 98.0,
                'matched_intent' => 'smart_pengajuan_code_lookup',
                'action_url' => $this->safeRoute('gomsn.pengajuan.show', $p->id),
                'action_label' => "📋 Buka Detail {$p->nomor_pengajuan}",
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // B. Specific Ticket Number Lookup (e.g. BDG-20261106-901, BRD-..., TKT-...)
        if (preg_match('/\b((?:BDG|BRD|TKT)-[A-Z0-9\-]+)\b/i', $rawClean, $m)) {
            $noTiket = strtoupper($m[1]);
            $t = Tiket::with(['ticketAssignees.user'])->where('no_tiket', 'LIKE', "%{$noTiket}%")->first();
            if (! $t) {
                return [
                    'response' => $isSunda
                        ? "Tiket gangguan kalayan nomor {$noTiket} teu kapanggih dina database GOMSN."
                        : "Tiket gangguan dengan nomor {$noTiket} tidak ditemukan di database GOMSN.",
                    'confidence' => 95.0,
                    'matched_intent' => 'smart_tiket_code_not_found',
                    'action_url' => $this->safeRoute('tickets.index'),
                    'action_label' => '🎫 Buka Tiket Gangguan',
                    'suggestions' => null,
                    'domain' => 'tiket',
                ];
            }

            $assignees = $t->ticketAssignees->map(fn ($a) => $a->user?->name)->filter()->implode(', ');
            $tech = $assignees !== '' ? $assignees : 'Belum di-dispatch';
            $openAt = $t->tanggal_open?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') ?? '-';
            $target = $t->customer_name ?: ($t->backbone_segment ?: '-');

            return [
                'response' => "Detail Tiket Gangguan #{$t->no_tiket}:\n"
                    . "• Kategori / Prioritas: {$t->kategori_tiket} ({$t->priority})\n"
                    . "• Pelanggan / Segmen: {$target}\n"
                    . "• Status: " . strtoupper((string) $t->status) . ($t->sla_status ? " (SLA: {$t->sla_status})" : '') . "\n"
                    . "• Teknisi: {$tech}\n"
                    . "• Dibuka: {$openAt} WIB",
                'confidence' => 98.0,
                'matched_intent' => 'smart_tiket_code_lookup',
                'action_url' => $this->safeRoute('tickets.show', $t->id),
                'action_label' => "🎫 Buka Detail #{$t->no_tiket}",
                'suggestions' => null,
                'domain' => 'tiket',
            ];
        }

        // C. Live Master Material / Stok Gudang Lookup
        if (
            preg_match('/\b(stok|stock|gudang|material|inventaris|inventory)\b/iu', $normMessage)
            && ! preg_match('/\b(bagaimana cara|cara permintaan|cara minta|prosedur|langkah|membatalkan|pembatalan|dari tiket|pengajuan stok)\b/iu', $normMessage)
            && preg_match('/\b(cek|berapa|sabaraha|daftar|list|lihat|tampilkan|tersedia|sisa|menipis|habis|kosong|kabel|dropcore|utp|closure|patch|ont|barang|semua)\b/iu', $normMessage)
        ) {
            return $this->buildInventoryLiveResponse($normMessage, $isSunda);
        }

        return null;
    }

    /**
     * Step 2: Live Data & Analytics Resolver for natural/free-form data queries (short OR long).
     *
     * @param list<string> $userTokens
     * @param array<string, float> $domains
     * @param array<string, bool> $style
     * @return ?array{response: string, confidence: float, matched_intent: string, action_url: ?string, action_label: ?string, suggestions: ?list<string>, domain: string}
     */
    public function resolveSmartDataQuery(?User $user, string $rawClean, string $normMessage, array $userTokens, array $domains, array $style, bool $isSunda): ?array
    {
        if (! $user) {
            return null;
        }

        // Skip pure how-to / procedural / policy / troubleshooting questions so static SOP/QA answers them accurately
        if ($style['is_howto'] || $style['is_troubleshoot']) {
            return null;
        }

        // 1. Profil Saya / Siapa Atasan Saya / Kode Karyawan / Divisi / Jabatan Saya
        if (
            preg_match('/\b(siapa atasan saya|atasan saya siapa|atasan langsung saya|supervisor saya|saha atasan urang)\b/iu', $normMessage)
        ) {
            $approver = $user->resolveEffectiveApprover('attendance');
            $approverName = $approver?->name ?? 'Belum ditetapkan (hubungi HRD)';
            $approverPos = $approver?->position ? " ({$approver->position})" : '';

            return [
                'response' => $isSunda
                    ? "Atasan langsung / approver resmi anjeun dina sistem GOMSN ayeuna nyaeta: {$approverName}{$approverPos}."
                    : "Atasan langsung / approver resmi Anda di sistem GOMSN saat ini adalah: {$approverName}{$approverPos}.",
                'confidence' => 95.0,
                'matched_intent' => 'smart_my_supervisor',
                'action_url' => $this->safeRoute('portal.profile'),
                'action_label' => '👤 Buka Profil Saya',
                'suggestions' => null,
                'domain' => 'profil',
            ];
        }

        if (
            preg_match('/\b(profil saya|data saya|info akun saya|id karyawan saya|kode karyawan saya|jabatan saya|divisi saya|role saya|siapa saya|akun saya)\b/iu', $normMessage)
            && ! preg_match('/\b(ganti|ubah|edit|reset|foto|password|sandi)\b/iu', $normMessage)
        ) {
            $roles = $user->getRoleNames()->implode(', ') ?: 'karyawan';
            $code = $user->employee_code ?: ($user->nik ?: '-');
            $approver = $user->resolveEffectiveApprover('attendance');

            return [
                'response' => "Informasi Profil Akun GOMSN Anda:\n"
                    . "• Nama: {$user->name}\n"
                    . "• ID Karyawan: {$code}\n"
                    . "• Divisi / Jabatan: " . ($user->division ?: '-') . ' / ' . ($user->position ?: '-') . "\n"
                    . "• Role Sistem: {$roles}\n"
                    . "• Atasan / Approver: " . ($approver?->name ?? '-'),
                'confidence' => 95.0,
                'matched_intent' => 'smart_my_profile',
                'action_url' => $this->safeRoute('portal.profile'),
                'action_label' => '👤 Buka Profil Saya',
                'suggestions' => null,
                'domain' => 'profil',
            ];
        }

        // 2. Hari Libur Nasional / Tanggal Merah
        if (
            preg_match('/\b(hari libur|libur nasional|tanggal merah|cuti bersama)\b/iu', $normMessage)
            && preg_match('/\b(kapan|iraha|daftar|list|bulan ini|tahun ini|terdekat|ada berapa|berapa|cek|apa saja)\b/iu', $normMessage)
        ) {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            $holidays = NationalHoliday::query()
                ->whereDate('date', '>=', $today)
                ->orderBy('date')
                ->take(6)
                ->get();

            if ($holidays->isEmpty()) {
                $resp = "Belum ada daftar hari libur nasional mendatang yang tercatat di kalender sistem GOMSN.";
            } else {
                $lines = ["Daftar Hari Libur Nasional / Tanggal Merah mendatang:"];
                foreach ($holidays as $h) {
                    $dt = Carbon::parse($h->date)->locale('id')->translatedFormat('d M Y (l)');
                    $tag = $h->is_collective_leave ? ' [Cuti Bersama]' : '';
                    $lines[] = "• {$dt}: {$h->name}{$tag}";
                }
                $resp = implode("\n", $lines);
            }

            return [
                'response' => $resp,
                'confidence' => 93.0,
                'matched_intent' => 'smart_national_holidays',
                'action_url' => $this->safeRoute('attendance.schedules'),
                'action_label' => '📅 Buka Jadwal & Kalender',
                'suggestions' => null,
                'domain' => 'jadwal',
            ];
        }

        // 3. Dashboard / Comprehensive Monthly Summary (Scoped automatically by user role!)
        if (
            preg_match('/\b(ringkasan dashboard|statistik dashboard|rekap semua|ringkasan bulan ini|statistik bulan ini|statistik perusahaan|statistik divisi|laporan bulan ini|data saya bulan ini|rekap lengkap)\b/iu', $normMessage)
        ) {
            $stats = app(DashboardStatsService::class)->getStats($user);
            $scopeTitle = $stats['scope']['title'] ?? 'Statistik Bulan Ini';
            $bln = Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('F Y');
            $p = $stats['pengajuan'];
            $a = $stats['absensi'];
            $l = $stats['lembur'];
            $nomApp = number_format((float) ($p['nominal_disetujui'] ?? 0), 0, ',', '.');

            $resp = "{$scopeTitle} (Periode {$bln}):\n"
                . "• Absensi: Hadir {$a['month_present_days']} hari (Tepat Waktu: {$a['month_ontime_days']}, Terlambat: {$a['month_late_days']}, Alpa: {$a['month_absent_days']}) — Rasio {$a['month_rate']}%\n"
                . "• Pengajuan Dana/Barang: Total {$p['total']} (Disetujui/Selesai: {$p['approved']}, Menunggu: {$p['pending']}, Ditolak: {$p['rejected']}) — Nominal Disetujui: Rp {$nomApp}\n"
                . "• Lembur: Total {$l['total']} pengajuan (Disetujui: {$l['approved']}, Menunggu: {$l['pending']}, Ditolak: {$l['rejected']}) — {$l['total_hours']} jam";

            return [
                'response' => $resp,
                'confidence' => 94.0,
                'matched_intent' => 'smart_dashboard_summary',
                'action_url' => $this->safeRoute('portal.hub'),
                'action_label' => '🏠 Buka Dashboard',
                'suggestions' => null,
                'domain' => 'dashboard',
            ];
        }

        // 4. Pengajuan Live Data (count, approved, pending, rejected, nominal — personal OR company/division)
        if (
            isset($domains['pengajuan'])
            && ($style['is_data'] || preg_match('/\b(disetujui|selesai|dicairkan|ditolak|pending|menunggu|nominal|total|jumlah|berapa|sabaraha|rekap|statistik|antrean)\b/iu', $normMessage))
            && ! $style['is_policy']
            && ! preg_match('/\b(batas maksimal|plafon|revisi|diedit|edit|nota|lpj|settlement|hierarki|siapa yang acc)\b/iu', $normMessage)
        ) {
            return $this->buildPengajuanLiveResponse($user, $normMessage, $style, $isSunda);
        }

        // 5. Lembur Live Data (company-wide, division, or personal)
        if (
            isset($domains['lembur'])
            && ($style['is_data'] || preg_match('/\b(total|jumlah|berapa|sabaraha|rekap|status|pending|menunggu|disetujui|dibayar|paid|jam)\b/iu', $normMessage))
            && ! $style['is_policy']
            && ! preg_match('/\b(minimal|maksimal|tarif|rate|hari libur|spk|syarat|batas waktu|klaim|alasan ditolak|remote)\b/iu', $normMessage)
        ) {
            return $this->buildLemburLiveResponse($user, $normMessage, $style, $isSunda);
        }

        // 6. Tasks / Tugas Operasional Live Data
        if (
            isset($domains['tugas'])
            && ($style['is_data'] || preg_match('/\b(daftar|list|ada|punya saya|saya|pending|belum selesai|aktif)\b/iu', $normMessage))
        ) {
            $tasks = Task::query()
                ->where('assignee_id', $user->id)
                ->whereNotIn('status', ['Completed', 'Cancelled'])
                ->latest()
                ->take(5)
                ->get();

            if ($tasks->isEmpty()) {
                $resp = $isSunda
                    ? "Ayeuna teu aya tugas operasional (Task) aktif nu ditugaskeun ka anjeun."
                    : "Saat ini tidak ada tugas operasional (Task) aktif yang ditugaskan kepada Anda.";
            } else {
                $lines = ["Daftar Tugas Operasional aktif Anda ({$tasks->count()} tugas):"];
                foreach ($tasks as $idx => $t) {
                    $due = $t->due_date ? $t->due_date->translatedFormat('d M Y') : '-';
                    $lines[] = ($idx + 1) . ". {$t->title} [{$t->status} | Deadline: {$due}]";
                }
                $resp = implode("\n", $lines);
            }

            return [
                'response' => $resp,
                'confidence' => 92.0,
                'matched_intent' => 'smart_my_tasks',
                'action_url' => $this->safeRoute('tickets.index'),
                'action_label' => '🎫 Buka Modul Tiket & Tugas',
                'suggestions' => null,
                'domain' => 'tugas',
            ];
        }

        // 7. Tiket Gangguan Live Data (counts, active, closed, by category, or my assigned)
        if (
            isset($domains['tiket'])
            && ($style['is_data'] || preg_match('/\b(berapa|sabaraha|total|jumlah|rekap|statistik|daftar|list|aktif|open|proses|close|closed|selesai|sla|punya saya|ditugaskan)\b/iu', $normMessage))
            && ! $style['is_policy']
            && ! preg_match('/\b(perbedaan|beda|syarat|closing|eskalasi|redaman|opm|spk|reopen|material|kontak|export)\b/iu', $normMessage)
        ) {
            return $this->buildTiketLiveResponse($user, $normMessage, $style, $isSunda);
        }

        // 8. Karyawan / Divisi Headcount Breakdown (HRD/Admin)
        if (
            isset($domains['karyawan'])
            && preg_match('/\b(per divisi|tiap divisi|jumlah divisi|daftar divisi|berapa karyawan|total karyawan|berapa pegawai|berapa orang)\b/iu', $normMessage)
        ) {
            if (! $this->isCompanyViewer($user)) {
                return [
                    'response' => $isSunda
                        ? "Akses Ditolak. Data jumlah karyawan sa-perusahaan ngan tiasa diakses ku HRD, Finance, Direksi, atawa Administrator."
                        : "Akses Ditolak. Data jumlah karyawan seluruh perusahaan hanya dapat diakses oleh HRD, Finance, Direksi, atau Administrator.",
                    'confidence' => 94.0,
                    'matched_intent' => 'smart_headcount_rbac_denied',
                    'action_url' => $this->safeRoute('portal.profile'),
                    'action_label' => '👤 Buka Profil Saya',
                    'suggestions' => null,
                    'domain' => 'karyawan',
                ];
            }

            $totalActive = User::query()->where('is_active', true)->count();
            $byDiv = User::query()
                ->where('is_active', true)
                ->selectRaw("COALESCE(NULLIF(division, ''), 'Umum') as div_name, COUNT(*) as total")
                ->groupBy('div_name')
                ->orderByDesc('total')
                ->get();

            $lines = ["Ringkasan Karyawan Aktif PT Media Solusi Network (Total: {$totalActive} orang):"];
            foreach ($byDiv as $row) {
                $lines[] = "• Divisi {$row->div_name}: {$row->total} orang";
            }

            return [
                'response' => implode("\n", $lines),
                'confidence' => 94.0,
                'matched_intent' => 'smart_headcount_by_division',
                'action_url' => $this->safeRoute('portal.employees.index'),
                'action_label' => '👥 Buka Data Karyawan',
                'suggestions' => null,
                'domain' => 'karyawan',
            ];
        }

        return null;
    }

    /**
     * Step 3: App Guide ("Apa itu GOMSN / Cara kerja aplikasi ini / Fitur apa saja")
     * AND Smart Broad-Topic Hub (for 1-3 word broad queries like "jadwal kerja", "soal cuti", "soal tiket").
     *
     * @param list<string> $userTokens
     * @param array<string, float> $domains
     * @param array<string, bool> $style
     * @return ?array{response: string, confidence: float, matched_intent: string, action_url: ?string, action_label: ?string, suggestions: ?list<string>, domain: string}
     */
    public function resolveAppGuideOrBroadTopic(?User $user, string $normMessage, array $userTokens, array $domains, array $style, bool $isSunda, bool $forceFallback = false): ?array
    {
        // A. Full App Overview / "Cara kerja aplikasi ini" / "Fitur apa saja" / "Kamu bisa apa"
        $isAppOverview = (bool) preg_match(
            '/\b(apa itu gomsn|tentang gomsn|tentang aplikasi|cara kerja aplikasi|cara kerja sistem|cara kerja gomsn|aplikasi ini buat apa|aplikasi ini untuk apa|fitur apa saja|fitur gomsn|menu apa saja|modul apa saja|layanan apa saja|panduan aplikasi|kamu bisa apa|bisa bantu apa|bisa apa aja|bisa nanya apa|cara pakai aplikasi|jelaskan aplikasi)\b/iu',
            $normMessage
        ) || (isset($domains['aplikasi']) && count($userTokens) <= 3 && count($domains) === 1);

        if ($isAppOverview) {
            return $this->buildAppOverviewResponse($user, $isSunda);
        }

        // Do not intercept explicit personal/dashboard live-data queries in broad hub (unless forced fallback)
        if (! $forceFallback && preg_match('/\b(siapa atasan|atasan saya|supervisor saya|profil saya|data saya|info akun saya|id karyawan saya|kode karyawan saya|ringkasan dashboard|statistik dashboard|ringkasan bulan ini|statistik bulan ini|statistik perusahaan|statistik divisi|hari libur|libur nasional|tanggal merah)\b/iu', $normMessage)) {
            return null;
        }

        // B. Broad Short Topic Query (1-3 meaningful tokens without specific sub-action) OR Smart Domain Fallback
        $topDomain = ! empty($domains) ? array_key_first($domains) : null;
        if (! $topDomain) {
            return null;
        }

        // If user asked a specific howto/policy/troubleshoot question (e.g. via follow-up "caranya gimana?" / "syaratnya apa?"), let exact QA intents answer first
        if (! $forceFallback && ($style['is_howto'] || $style['is_policy'] || $style['is_troubleshoot']) && ! preg_match('/^(soal|perkawis|tentang|masalah|kendala)\s+\w+$/iu', $normMessage)) {
            return null;
        }

        // Specific sub-action keywords that should be handled by exact QA intents instead of broad hub
        $hasSpecificSubTopic = (bool) preg_match(
            '/\b(lupa|poho|koreksi|gagal|error|toleransi|telat|terlambat|alpa|offline|radius|gps|biometrik|kamera|wajah|tukar|batal|batalkan|hangus|melahirkan|khusus|dokter|skd|sakit|gering|revisi|edit|settlement|lpj|nota|plafon|maksimal|minimal|tarif|rate|spk|hari libur|eskalasi|sla|redaman|opm|otdr|closing|reopen|closure|splicing|manuver|koordinat|penuh|kapasitas|ekspansi|reset|password|sandi|foto|resign|nonaktif|onboarding|import|mutasi|broadcast|waiver|sanggahan|payroll|export|unduh|kemarin|kamari|7 hari|seminggu|bulan lalu|top 5|5 besar|beda|perbedaan|kepanjangan|singkatan)\b/iu',
            $normMessage
        );

        $isBroadShortQuery = count($userTokens) <= 3 && ! $hasSpecificSubTopic && $topDomain !== 'dashboard';

        if ($isBroadShortQuery || $forceFallback) {
            return $this->buildDomainHubResponse($topDomain, $user, $isSunda, $forceFallback);
        }

        return null;
    }

    /**
     * Build comprehensive overview of the GOMSN portal tailored to the user's role.
     */
    protected function buildAppOverviewResponse(?User $user, bool $isSunda): array
    {
        $roleExtra = '';
        if ($user && $this->isCompanyViewer($user)) {
            $roleExtra = "\n• Manajemen HRD / Finance / Admin: Data Karyawan, Rekap Absen & Potongan Seluruh Karyawan, Approval Pengajuan & Lembur, Stok Gudang, Pengaturan Sistem, serta Monitoring KPI.";
        } elseif ($user && $this->isDivisionApprover($user)) {
            $roleExtra = "\n• Menu Atasan / Supervisor: Persetujuan (Approval) Cuti, Koreksi Absen, Lembur, dan Pengajuan Operasional anggota divisi Anda.";
        }

        $resp = $isSunda
            ? "GOMSN nyaeta Portal Operasional & HRIS Terpadu milik PT Media Solusi Network. Fitur utamana ngawengku:\n"
                . "• Presensi & Absensi: Check-in/Check-out Biometrik Wajah & GPS (Kantor / Dinas Luar), Riwayat Absen, Koreksi Absen, Potongan Denda, sarta Leaderboard Disiplin.\n"
                . "• Jadwal & Cuti: Kalender Jadwal Shift, Tukar Shift, Pengajuan Cuti Taunan/Khusus, Izin, jeung Sakit (SKD).\n"
                . "• Pengajuan & Lembur: Kasbon Dana, Pembelian Barang, Permintaan Stok Gudang, sarta Pengajuan Lembur & SPK.\n"
                . "• Tiket & Jaringan FTTH: Manajemen Tiket Gangguan (Backbone/Broadband), SLA, Resume Pekerjaan, sarta Database OLT, PON, ODP & Pelanggan.{$roleExtra}\n\n"
                . "Anjeun tiasa naroskeun cara kerja fitur, aturan perusahaan, atawa langsung ngecek data live (sapertos sisa cuti, status absen, pengajuan, tiket, stok, atawa ODP)!"
            : "GOMSN adalah Portal Operasional & HRIS Terpadu PT Media Solusi Network. Berikut cara kerja dan fitur utamanya:\n"
                . "• Presensi & Absensi: Check-in/Check-out Biometrik Wajah & GPS (Kantor / Dinas Luar), Riwayat Absen, Koreksi Absen, Potongan Denda, serta Leaderboard Disiplin.\n"
                . "• Jadwal & Cuti: Kalender Jadwal Shift, Tukar Shift, Cuti Tahunan/Khusus, Izin, dan Sakit (SKD).\n"
                . "• Pengajuan & Lembur: Kasbon Dana, Pengadaan/Beli Barang, Permintaan Stok Gudang, serta Klaim Lembur & SPK.\n"
                . "• Tiket & Jaringan FTTH: Tiket Gangguan (Backbone/Broadband), Monitoring SLA, Resume Pekerjaan, serta Database OLT, PON, ODP & Pelanggan.{$roleExtra}\n\n"
                . "Anda bisa menanyakan cara kerja/prosedur fitur apa pun maupun data langsung (misal: cek absen, sisa cuti, pengajuan, lembur, tiket, stok gudang, atau ODP pelanggan)!";

        return [
            'response' => $resp,
            'confidence' => 94.0,
            'matched_intent' => 'smart_app_overview',
            'action_url' => $this->safeRoute('portal.hub'),
            'action_label' => '🏠 Buka Dashboard GOMSN',
            'suggestions' => [
                'Ringkasan data saya bulan ini',
                'Bagaimana cara absen masuk dan pulang?',
            ],
            'domain' => 'aplikasi',
        ];
    }

    /**
     * Build a smart, data-enriched hub response for any GOMSN domain.
     */
    protected function buildDomainHubResponse(string $domain, ?User $user, bool $isSunda, bool $isFallback = false): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $cMonth = $now->month;
        $cYear = $now->year;
        $todayStr = $now->toDateString();

        return match ($domain) {
            'jadwal' => $this->buildJadwalHub($user, $isSunda, $todayStr),
            'absensi' => $this->buildAbsensiHub($user, $isSunda, $todayStr, $cMonth, $cYear),
            'cuti' => $this->buildCutiHub($user, $isSunda, $cYear),
            'potongan' => $this->buildPotonganHub($user, $isSunda, $cMonth, $cYear),
            'pengajuan' => $this->buildPengajuanLiveResponse($user, 'ringkasan pengajuan', ['is_company_scope' => false, 'is_personal_scope' => true], $isSunda, true),
            'lembur' => $this->buildLemburLiveResponse($user, 'ringkasan lembur', ['is_company_scope' => false, 'is_personal_scope' => true], $isSunda, true),
            'tiket' => $this->buildTiketLiveResponse($user, 'ringkasan tiket', ['is_company_scope' => false, 'is_personal_scope' => false], $isSunda, true),
            'stok' => $this->buildInventoryLiveResponse('stok gudang', $isSunda),
            'jaringan' => $this->buildNetworkHub($isSunda),
            'kpi' => [
                'response' => "Modul Analitik & KPI mengukur performa penanganan gangguan teknisi secara objektif berdasarkan 3 komponen:\n"
                    . "• Kepatuhan SLA (45%): Ketepatan penyelesaian tiket terhadap target MTTR.\n"
                    . "• Konsistensi Update Kronologi / Interval (35%): Kepatuhan pembaruan progres berkala.\n"
                    . "• Produktivitas & Kualitas Closing (20%): Jumlah tiket selesai tanpa reopen.\n"
                    . "Minimal 5 tiket selesai diperlukan agar skor KPI tampil penuh (jika kurang berstatus DATA_TIDAK_CUKUP).",
                'confidence' => $isFallback ? 78.0 : 90.0,
                'matched_intent' => 'smart_hub_kpi',
                'action_url' => $this->safeRoute('gomsn.analytics.kpi'),
                'action_label' => '📈 Buka Analitik KPI',
                'suggestions' => null,
                'domain' => 'kpi',
            ],
            'leaderboard' => [
                'response' => "Leaderboard Disiplin menampilkan peringkat karyawan paling disiplin setiap bulan berdasarkan:\n"
                    . "• Frekuensi kehadiran tepat waktu (on-time check-in).\n"
                    . "• Minimnya total menit keterlambatan.\n"
                    . "• Zero Alpa (tanpa ketidakhadiran tanpa keterangan).\n"
                    . "Buka menu Leaderboard untuk melihat peringkat terbaru bulan ini.",
                'confidence' => $isFallback ? 78.0 : 90.0,
                'matched_intent' => 'smart_hub_leaderboard',
                'action_url' => $this->safeRoute('attendance.leaderboard'),
                'action_label' => '🏆 Buka Leaderboard',
                'suggestions' => null,
                'domain' => 'leaderboard',
            ],
            'karyawan' => [
                'response' => $this->isCompanyViewer($user)
                    ? "Menu Data Karyawan (khusus HRD & Admin) digunakan untuk mengelola seluruh akun karyawan PT MSN (Total aktif saat ini: " . User::where('is_active', true)->count() . " orang), termasuk tambah karyawan baru, ubah divisi/jabatan, nonaktifkan akun resign, reset password, dan reset biometrik wajah."
                    : "Untuk melihat atau memperbarui informasi data diri Anda (foto profil, email, password, serta biometrik/passkey), buka menu Profil Saya. Perubahan data kepegawaian/divisi dikelola oleh tim HRD.",
                'confidence' => $isFallback ? 78.0 : 90.0,
                'matched_intent' => 'smart_hub_karyawan',
                'action_url' => $this->isCompanyViewer($user) ? $this->safeRoute('portal.employees.index') : $this->safeRoute('portal.profile'),
                'action_label' => $this->isCompanyViewer($user) ? '👥 Buka Data Karyawan' : '👤 Buka Profil Saya',
                'suggestions' => null,
                'domain' => 'karyawan',
            ],
            'profil' => [
                'response' => "Menu Profil Saya digunakan untuk mengelola akun GOMSN Anda:\n"
                    . "• Melihat ID Karyawan, Divisi, Jabatan, dan Atasan Langsung.\n"
                    . "• Mengganti Foto Profil dan memperbarui Password akun.\n"
                    . "• Mendaftarkan Login Biometrik (Sidik Jari / Face ID Passkey) untuk masuk lebih cepat.",
                'confidence' => $isFallback ? 78.0 : 90.0,
                'matched_intent' => 'smart_hub_profil',
                'action_url' => $this->safeRoute('portal.profile'),
                'action_label' => '👤 Buka Profil Saya',
                'suggestions' => null,
                'domain' => 'profil',
            ],
            'sesi' => [
                'response' => "Keamanan Sesi & Perangkat di GOMSN melindungi akun karyawan menggunakan verifikasi perangkat (Device Approval / OTP) dan log sesi aktif. Jika HP hilang atau ganti perangkat, segera hubungi Admin IT / HRD untuk mencabut sesi (Force Logout) dan mereset perangkat/biometrik.",
                'confidence' => $isFallback ? 78.0 : 90.0,
                'matched_intent' => 'smart_hub_sesi',
                'action_url' => $this->isCompanyViewer($user) ? $this->safeRoute('portal.sessions') : $this->safeRoute('portal.profile'),
                'action_label' => $this->isCompanyViewer($user) ? '🔒 Buka Sesi & Perangkat' : '👤 Buka Profil Saya',
                'suggestions' => null,
                'domain' => 'sesi',
            ],
            'tugas' => [
                'response' => "Tugas operasional lapangan di GOMSN terintegrasi langsung dengan modul Tiket Gangguan (Dispatch Teknisi, Kronologi Progres, Pemakaian Material, hingga Berita Acara). Buka menu Tiket Gangguan untuk memantau penugasan Anda.",
                'confidence' => $isFallback ? 78.0 : 88.0,
                'matched_intent' => 'smart_hub_tugas',
                'action_url' => $this->safeRoute('tickets.index'),
                'action_label' => '🎫 Buka Tiket & Tugas',
                'suggestions' => null,
                'domain' => 'tugas',
            ],
            default => $this->buildAppOverviewResponse($user, $isSunda),
        };
    }

    protected function buildJadwalHub(?User $user, bool $isSunda, string $todayStr): array
    {
        $tomorrowStr = Carbon::tomorrow('Asia/Jakarta')->toDateString();
        $scheduleText = '';

        if ($user) {
            $schToday = MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $todayStr)->first();
            $schTomorrow = MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $tomorrowStr)->first();
            $fmt = function ($sch): string {
                if (! $sch || ! $sch->shift) {
                    return 'Shift Pagi (08:00 - 17:00 WIB)';
                }
                if ($sch->shift->is_day_off) {
                    return 'Libur (OFF)';
                }
                return "{$sch->shift->name} (" . substr((string) $sch->shift->start_time, 0, 5) . ' - ' . substr((string) $sch->shift->end_time, 0, 5) . ' WIB)';
            };

            $scheduleText = "\n\nJadwal Shift Anda saat ini:\n"
                . "• Hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmt($schToday) . "\n"
                . "• Besok (" . Carbon::tomorrow('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmt($schTomorrow);
        }

        $resp = ($isSunda
            ? "Menu Jadwal Kerja Saya nampilkeun kalender shift gawe sabulan pinuh lengkep jeung jam asup/balik sarta fitur Tukar Shift jeung rekan sa-divisi (perlu ACC Atasan)."
            : "Menu Jadwal Kerja Saya menampilkan kalender shift bulanan lengkap dengan jam masuk/pulang serta fitur Tukar Shift dengan rekan satu divisi (dengan persetujuan Atasan).")
            . $scheduleText;

        return [
            'response' => $resp,
            'confidence' => 91.0,
            'matched_intent' => 'smart_hub_jadwal',
            'action_url' => $this->safeRoute('attendance.schedules'),
            'action_label' => '📅 Buka Jadwal Kerja Saya',
            'suggestions' => null,
            'domain' => 'jadwal',
        ];
    }

    protected function buildAbsensiHub(?User $user, bool $isSunda, string $todayStr, int $cMonth, int $cYear): array
    {
        $live = '';
        if ($user) {
            $attToday = Attendance::query()->where('user_id', $user->id)->whereDate('date', $todayStr)->first();
            $clockIn = $attToday?->clock_in_at ?? $attToday?->clock_in;
            $clockOut = $attToday?->clock_out_at ?? $attToday?->clock_out;

            $todayInfo = $clockIn
                ? 'Sudah Check-In pukul ' . Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' . ($clockOut ? ', Check-Out ' . Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : ' (Belum Check-Out)')
                : 'Belum Check-In';

            $monthPresent = Attendance::query()
                ->where('user_id', $user->id)
                ->whereMonth('date', $cMonth)
                ->whereYear('date', $cYear)
                ->whereIn('status', self::ATTENDANCE_PRESENT_STATUSES)
                ->count();
            $monthLate = Attendance::query()
                ->where('user_id', $user->id)
                ->whereMonth('date', $cMonth)
                ->whereYear('date', $cYear)
                ->where(function ($q) {
                    $q->whereIn('status', self::ATTENDANCE_LATE_STATUSES)->orWhere('late_minutes', '>', 0);
                })
                ->count();

            $live = "\n\nRingkasan Presensi Anda:\n"
                . "• Status Hari Ini: {$todayInfo}\n"
                . "• Bulan Ini: Hadir {$monthPresent} hari (Terlambat: {$monthLate}x)";
        }

        $resp = ($isSunda
            ? "Fitur Absensi di GOMSN nganggo verifikasi Biometrik Wajah & GPS (Kantor atawa Dinas Luar) kalayan batas toleransi telat 15 menit. Upami poho check-out, tiasa ngajukeun Koreksi Absen dina menu Riwayat Absen Saya."
            : "Fitur Absensi di GOMSN menggunakan verifikasi Biometrik Wajah & GPS (Kantor maupun Dinas Luar) dengan batas toleransi keterlambatan 15 menit. Jika lupa check-out, Anda dapat mengajukan Koreksi Absen melalui menu Riwayat Absen Saya.")
            . $live;

        return [
            'response' => $resp,
            'confidence' => 90.0,
            'matched_intent' => 'smart_hub_absensi',
            'action_url' => $this->safeRoute('attendance.index'),
            'action_label' => '📍 Buka Halaman Absen',
            'suggestions' => null,
            'domain' => 'absensi',
        ];
    }

    protected function buildCutiHub(?User $user, bool $isSunda, int $cYear): array
    {
        $live = '';
        if ($user) {
            $balance = LeaveBalance::query()->where('user_id', $user->id)->where('year', $cYear)->first();
            $sisa = $balance ? ($balance->remaining_days ?? 12) : 12;
            $pendingCount = Leave::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'pending'])
                ->count();

            $live = "\n\nData Cuti Anda ({$cYear}):\n"
                . "• Sisa Saldo Cuti Tahunan: {$sisa} hari kerja\n"
                . "• Pengajuan Cuti/Izin Menunggu Persetujuan: {$pendingCount}";
        }

        $resp = ($isSunda
            ? "Layanan Cuti & Izin di GOMSN ngawengku:\n• Cuti Taunan: Jatah 12 poe/taun, diajukeun minimal H-3.\n• Cuti Khusus: Teu motong saldo cuti (kawin, ngalahirkeun, kulawarga inti maot).\n• Izin Sakit: Wajib lampiran Surat Keterangan Dokter (SKD)."
            : "Layanan Cuti & Izin di GOMSN mencakup:\n• Cuti Tahunan: Kuota 12 hari/tahun, diajukan minimal H-3 sebelum tanggal cuti.\n• Cuti Khusus: Tidak memotong saldo cuti tahunan (menikah, melahirkan, duka keluarga inti).\n• Izin Sakit: Wajib melampirkan foto/PDF Surat Keterangan Dokter (SKD).")
            . $live;

        return [
            'response' => $resp,
            'confidence' => 91.0,
            'matched_intent' => 'smart_hub_cuti',
            'action_url' => $this->safeRoute('attendance.leaves'),
            'action_label' => '🌴 Buka Cuti & Izin Saya',
            'suggestions' => null,
            'domain' => 'cuti',
        ];
    }

    protected function buildPotonganHub(?User $user, bool $isSunda, int $cMonth, int $cYear): array
    {
        $live = '';
        if ($user) {
            try {
                $report = app(DeductionService::class)->calculateUserMonthlyReport($user, $cYear, $cMonth);
                $grand = number_format((float) ($report['grand_total_deduction'] ?? 0), 0, ',', '.');
                $late = number_format((float) ($report['total_late_deduction'] ?? 0), 0, ',', '.');
                $alpa = number_format((float) ($report['total_alpa_deduction'] ?? 0), 0, ',', '.');
                $bln = Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('F Y');
                $live = "\n\nPotongan Anda ({$bln}):\n• Total Potongan: Rp {$grand} (Terlambat: Rp {$late} | Alpa: Rp {$alpa})";
            } catch (\Throwable) {
                $live = '';
            }
        }

        $resp = ($isSunda
            ? "Potongan Absensi diitung otomatis upami check-in ngaleuwihan toleransi 15 menit atawa aya status Alpa dina bulan berjalan. Upami aya kasalahan itungan, ajukeun Koreksi Absen samemeh Lock Period HRD."
            : "Potongan Absensi dihitung otomatis apabila check-in melewati toleransi 15 menit atau terdapat status Alpa pada bulan berjalan. Jika ada ketidaksesuaian, ajukan Koreksi Absen sebelum Lock Period akhir bulan oleh HRD.")
            . $live;

        return [
            'response' => $resp,
            'confidence' => 90.0,
            'matched_intent' => 'smart_hub_potongan',
            'action_url' => $this->safeRoute('attendance.deductions'),
            'action_label' => '💵 Buka Potongan Absen Saya',
            'suggestions' => null,
            'domain' => 'potongan',
        ];
    }

    protected function buildPengajuanLiveResponse(?User $user, string $normMessage, array $style, bool $isSunda, bool $includeGuide = false): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $cMonth = $now->month;
        $cYear = $now->year;
        $bln = $now->locale('id')->translatedFormat('F Y');

        $askAll = $user && ($this->isCompanyViewer($user) && ($style['is_company_scope'] || ! $style['is_personal_scope'] && preg_match('/\b(antrean|seluruh|semua|perusahaan|karyawan|approval|keseluruhan)\b/iu', $normMessage)));

        $query = Pengajuan::query()
            ->whereYear('created_at', $cYear)
            ->whereMonth('created_at', $cMonth);

        $scopeText = 'Anda';
        if ($askAll) {
            $scopeText = 'Seluruh Karyawan';
        } elseif ($user && $this->isDivisionApprover($user) && $style['is_company_scope']) {
            $div = $user->division;
            $subIds = User::query()
                ->where(function ($q) use ($user, $div) {
                    $q->where('supervisor_id', $user->id);
                    if (! empty($div)) {
                        $q->orWhereRaw('LOWER(division) = ?', [strtolower($div)]);
                    }
                })
                ->pluck('id')
                ->push($user->id)
                ->unique()
                ->all();
            $query->whereIn('user_id', $subIds);
            $scopeText = 'Divisi ' . ($div ?: 'Anda');
        } elseif ($user) {
            $query->where('user_id', $user->id);
        }

        $items = $query->latest()->get();
        $total = $items->count();
        $approved = $items->whereIn('status', self::PENGAJUAN_APPROVED_STATUSES)->count();
        $pending = $items->whereIn('status', self::PENGAJUAN_PENDING_STATUSES)->count();
        $rejected = $items->where('status', 'ditolak')->count();
        $nomApproved = number_format((float) $items->whereIn('status', self::PENGAJUAN_APPROVED_STATUSES)->sum('nominal_total'), 0, ',', '.');

        $lines = [];
        if ($includeGuide) {
            $lines[] = "Modul Pengajuan Operasional GOMSN mendukung 4 jenis: Kasbon Dana (PND), Pembelian/Beli Barang (PBL), Permintaan Stok Gudang (PST), dan Permintaan Umum (PRM) dengan alur approval Atasan → Executive (>Rp 5 Jt) → Finance.\n";
        }
        $lines[] = "Ringkasan Pengajuan {$scopeText} ({$bln}):";
        $lines[] = "• Total Pengajuan: {$total}";
        $lines[] = "• Disetujui / Selesai / Cair: {$approved} (Nominal: Rp {$nomApproved})";
        $lines[] = "• Menunggu Persetujuan: {$pending}";
        $lines[] = "• Ditolak: {$rejected}";

        if ($items->isNotEmpty() && $scopeText === 'Anda') {
            $lines[] = "\n3 Pengajuan Terakhir Anda:";
            foreach ($items->take(3) as $p) {
                $nom = in_array(strtoupper($p->jenis), ['STOK', 'PERMINTAAN'], true) ? strtoupper($p->jenis) : 'Rp ' . number_format((float) $p->nominal_total, 0, ',', '.');
                $st = strtoupper(str_replace('_', ' ', (string) $p->status));
                $lines[] = "• {$p->nomor_pengajuan} ({$nom}): {$st}";
            }
        }

        return [
            'response' => implode("\n", $lines),
            'confidence' => 93.0,
            'matched_intent' => 'smart_pengajuan_live_stats',
            'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
            'action_label' => '📋 Buka Menu Pengajuan',
            'suggestions' => null,
            'domain' => 'pengajuan',
        ];
    }

    protected function buildLemburLiveResponse(?User $user, string $normMessage, array $style, bool $isSunda, bool $includeGuide = false): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $cMonth = $now->month;
        $cYear = $now->year;
        $bln = $now->locale('id')->translatedFormat('F Y');

        $askAll = $user && ($this->isCompanyViewer($user) && ($style['is_company_scope'] || preg_match('/\b(antrean|seluruh|semua|perusahaan|karyawan|approval)\b/iu', $normMessage)));

        $query = Lembur::query()
            ->whereYear('tanggal', $cYear)
            ->whereMonth('tanggal', $cMonth);

        $scopeText = 'Anda';
        if ($askAll) {
            $scopeText = 'Seluruh Karyawan';
        } elseif ($user && $this->isDivisionApprover($user) && $style['is_company_scope']) {
            $div = $user->division;
            $subIds = User::query()
                ->where(function ($q) use ($user, $div) {
                    $q->where('supervisor_id', $user->id);
                    if (! empty($div)) {
                        $q->orWhereRaw('LOWER(division) = ?', [strtolower($div)]);
                    }
                })
                ->pluck('id')
                ->push($user->id)
                ->unique()
                ->all();
            $query->whereIn('user_id', $subIds);
            $scopeText = 'Divisi ' . ($div ?: 'Anda');
        } elseif ($user) {
            $query->where('user_id', $user->id);
        }

        $items = $query->get();
        $total = $items->count();
        $approvedItems = $items->whereIn('status', self::LEMBUR_APPROVED_STATUSES);
        $approved = $approvedItems->count();
        $pending = $items->whereIn('status', ['pending', 'submitted', 'menunggu_approval'])->count();
        $rejected = $items->where('status', 'rejected')->count();
        $paid = $items->where('status', 'paid')->count();
        $totalMenit = (int) $approvedItems->sum('durasi_menit');
        $totalJam = round($totalMenit / 60, 1);

        $lines = [];
        if ($includeGuide) {
            $lines[] = "Menu Lembur digunakan untuk klaim jam kerja tambahan (minimal 60 menit, maksimal 4 jam/hari, harus memiliki absensi pada tanggal lembur) dengan approval Atasan dan pencairan Finance.\n";
        }
        $lines[] = "Ringkasan Lembur {$scopeText} ({$bln}):";
        $lines[] = "• Total Pengajuan: {$total} (Disetujui: {$approved}, Dibayar/Paid: {$paid}, Menunggu: {$pending}, Ditolak: {$rejected})";
        $lines[] = "• Total Jam Disetujui: {$totalJam} jam ({$totalMenit} menit)";

        return [
            'response' => implode("\n", $lines),
            'confidence' => 93.0,
            'matched_intent' => 'smart_lembur_live_stats',
            'action_url' => $this->safeRoute('gomsn.overtime.index'),
            'action_label' => '💼 Buka Halaman Lembur',
            'suggestions' => null,
            'domain' => 'lembur',
        ];
    }

    protected function buildTiketLiveResponse(?User $user, string $normMessage, array $style, bool $isSunda, bool $includeGuide = false): array
    {
        $open = Tiket::query()->where('status', 'OPEN')->count();
        $proses = Tiket::query()->whereIn('status', ['PROSES', 'PENDING_VERIFIKASI', 'REOPENED'])->count();
        $closedMonth = Tiket::query()
            ->where('status', 'CLOSE')
            ->whereMonth('updated_at', Carbon::now('Asia/Jakarta')->month)
            ->whereYear('updated_at', Carbon::now('Asia/Jakarta')->year)
            ->count();

        $myActive = 0;
        if ($user) {
            $myActive = Tiket::query()
                ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI', 'REOPENED'])
                ->count();
        }

        $lines = [];
        if ($includeGuide) {
            $lines[] = "Modul Tiket Gangguan digunakan NOC & Teknisi untuk mencatat, menugaskan (dispatch), memantau SLA, mengisi kronologi + redaman OPM/OTDR, hingga closing & Berita Acara.\n";
        }
        $lines[] = "Ringkasan Tiket Gangguan Live:";
        $lines[] = "• Tiket Terbuka (OPEN): {$open}";
        $lines[] = "• Sedang Ditangani / Verifikasi: {$proses}";
        $lines[] = "• Selesai (CLOSE) Bulan Ini: {$closedMonth}";
        if ($user) {
            $lines[] = "• Tiket Aktif Ditugaskan ke Anda: {$myActive}";
        }

        return [
            'response' => implode("\n", $lines),
            'confidence' => 92.0,
            'matched_intent' => 'smart_tiket_live_stats',
            'action_url' => $this->safeRoute('tickets.index'),
            'action_label' => '🎫 Buka Tiket Gangguan',
            'suggestions' => null,
            'domain' => 'tiket',
        ];
    }

    protected function buildInventoryLiveResponse(string $normMessage, bool $isSunda): array
    {
        $materials = MasterMaterial::query()->where('is_active', true)->orderBy('nama')->get();

        // Check if asking specifically about low stock
        if (preg_match('/\b(menipis|habis|kosong|kurang|minimum|min stok|restock)\b/iu', $normMessage)) {
            $low = $materials->filter(fn ($m) => (int) $m->stok_tersedia <= (int) $m->min_stok);
            if ($low->isEmpty()) {
                $resp = "Saat ini seluruh stok material aktif di gudang masih dalam kondisi aman (di atas batas minimum).";
            } else {
                $lines = ["Daftar Material Gudang dengan Stok Menipis / Habis ({$low->count()} item):"];
                foreach ($low->take(12) as $m) {
                    $lines[] = "• {$m->nama} ({$m->kode}): Sisa {$m->stok_tersedia} {$m->satuan} (Min: {$m->min_stok})";
                }
                $resp = implode("\n", $lines);
            }

            return [
                'response' => $resp,
                'confidence' => 94.0,
                'matched_intent' => 'smart_inventory_low_stock',
                'action_url' => $this->safeRoute('gomsn.inventory.index'),
                'action_label' => '📦 Buka Stok Barang Gudang',
                'suggestions' => null,
                'domain' => 'stok',
            ];
        }

        // Check if user mentioned a specific material name
        $matched = $materials->filter(function ($m) use ($normMessage) {
            $nameLower = mb_strtolower($m->nama);
            $codeLower = mb_strtolower((string) $m->kode);
            if ($codeLower !== '' && str_contains($normMessage, $codeLower)) {
                return true;
            }
            $words = array_filter(preg_split('/[\s\-\/]+/u', $nameLower) ?: [], fn ($w) => mb_strlen($w) >= 3);
            foreach ($words as $w) {
                if (! in_array($w, ['pcs', 'roll', 'meter', 'unit', 'box', 'core'], true) && str_contains($normMessage, $w)) {
                    return true;
                }
            }
            return false;
        });

        $list = $matched->isNotEmpty() ? $matched : $materials;
        if ($list->isEmpty()) {
            $resp = "Belum ada data material aktif yang terdaftar di gudang sistem GOMSN.";
        } else {
            $header = $matched->isNotEmpty()
                ? "Data Stok Material yang Dicari ({$matched->count()} item):"
                : "Daftar Stok Material Gudang Aktif ({$materials->count()} item):";
            $lines = [$header];
            foreach ($list->take(12) as $m) {
                $flag = ((int) $m->stok_tersedia <= (int) $m->min_stok) ? ' ⚠️ Menipis' : '';
                $lines[] = "• {$m->nama}: {$m->stok_tersedia} {$m->satuan}{$flag}";
            }
            if ($list->count() > 12) {
                $lines[] = "...dan " . ($list->count() - 12) . " item lainnya.";
            }
            $resp = implode("\n", $lines);
        }

        return [
            'response' => $resp,
            'confidence' => 93.0,
            'matched_intent' => 'smart_inventory_lookup',
            'action_url' => $this->safeRoute('gomsn.inventory.index'),
            'action_label' => '📦 Buka Stok Barang Gudang',
            'suggestions' => null,
            'domain' => 'stok',
        ];
    }

    protected function buildNetworkHub(bool $isSunda): array
    {
        $totalOlts = Olt::count();
        $totalPons = Pon::count();
        $totalOdps = Odp::count();
        $totalCusts = OdpCustomer::where('status', 'aktif')->count();

        $resp = ($isSunda
            ? "Modul Infrastruktur Jaringan FTTH GOMSN ngokolakeun topologi OLT → PON → ODP → Pelanggan (lengkep jeung titik koordinat GPS sarta kapasitas slot port).\n\n"
            : "Modul Infrastruktur Jaringan FTTH GOMSN mengelola topologi OLT → PON → ODP → Pelanggan (lengkap dengan koordinat GPS dan kapasitas slot port).\n\n")
            . "Ringkasan Database Jaringan Saat Ini:\n"
            . "• {$totalOlts} OLT | {$totalPons} PON | {$totalOdps} Titik ODP | {$totalCusts} Pelanggan Aktif\n"
            . "Tips: Anda bisa langsung menanyakan data spesifik seperti \"detail OLT Bagong\", \"ODP penuh\", atau \"user [nama] di ODP mana?\".";

        return [
            'response' => $resp,
            'confidence' => 91.0,
            'matched_intent' => 'smart_hub_jaringan',
            'action_url' => $this->safeRoute('gomsn.network.index'),
            'action_label' => '🌐 Buka Jaringan OLT, PON & ODP',
            'suggestions' => null,
            'domain' => 'jaringan',
        ];
    }
}
