<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\FaceTemplate;
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
        'absensi' => ['absen', 'absensi', 'presensi', 'kehadiran', 'checkin', 'checkout', 'clock', 'biometrik', 'wajah', 'muka', 'beungeut', 'gps', 'radius', 'geofence', 'peta', 'map', 'koreksi', 'terlambat', 'telat', 'kesiangan', 'alpa', 'bolos', 'mangkir', 'dinas', 'hadir', 'kamera', 'selfie', 'tap', 'finger', 'site', 'visit', 'lowbat', 'nitip'],
        'jadwal' => ['jadwal', 'shift', 'piket', 'kalender', 'tukar', 'barter', 'swap', 'schedule', 'jam kerja', 'jam masuk', 'jam pulang', 'libur nasional', 'tanggal merah', 'cuti bersama'],
        'cuti' => ['cuti', 'izin', 'sakit', 'gering', 'meriang', 'demam', 'flu', 'opname', 'saldo', 'jatah', 'kuota', 'dokter', 'skd', 'melahirkan', 'lahiran', 'nikah', 'menikah', 'kawin', 'duka', 'meninggal', 'wafat', 'leave', 'hangus'],
        'potongan' => ['potongan', 'denda', 'sanggahan', 'komplain', 'protes', 'payroll', 'deduction', 'waiver', 'penalti'],
        'pengajuan' => ['pengajuan', 'kasbon', 'dana', 'duit', 'uang', 'talangan', 'pinjam', 'beli', 'pembelian', 'pengadaan', 'permintaan', 'reimburse', 'reimbursement', 'nota', 'struk', 'bon', 'kwitansi', 'kuitansi', 'lpj', 'settlement', 'cair', 'pencairan', 'nominal', 'anggaran', 'plafon', 'limit', 'bensin', 'tol'],
        'stok' => ['stok', 'stock', 'gudang', 'material', 'inventaris', 'inventory', 'barang', 'dropcore', 'kabel', 'closure', 'patch', 'utp', 'ont', 'restock', 'pigtail', 'konektor'],
        'lembur' => ['lembur', 'lemburan', 'overtime', 'spk', 'ekstra', 'tambahan jam', 'kerja tambahan', 'jam tambahan'],
        'tiket' => ['tiket', 'ticket', 'gangguan', 'trouble', 'los', 'lampu merah', 'noc', 'backbone', 'broadband', 'sla', 'mttr', 'eskalasi', 'closing', 'reopen', 'redaman', 'opm', 'otdr', 'splicing', 'jc', 'core', 'manuver', 'dispatch', 'resume', 'bast', 'berita acara'],
        'jaringan' => ['olt', 'pon', 'odp', 'ftth', 'jaringan', 'network', 'port', 'koordinat', 'pelanggan', 'customer', 'topologi', 'internet'],
        'tugas' => ['tugas', 'task', 'tasks', 'kerjaan', 'subtask'],
        'kpi' => ['kpi', 'performa', 'kinerja', 'nilai teknisi', 'skor teknisi', 'analytics', 'analitik'],
        'leaderboard' => ['leaderboard', 'peringkat', 'ranking', 'juara', 'disiplin', 'rajin', 'terbaik', 'top'],
        'karyawan' => ['karyawan', 'pegawai', 'staf', 'staff', 'headcount', 'onboarding', 'resign', 'nonaktif', 'mutasi', 'divisi', 'kontak', 'nik', 'kode karyawan'],
        'profil' => ['profil', 'profile', 'biodata', 'akun', 'password', 'sandi', 'foto', 'avatar', 'pp', 'passkey', 'atasan', 'bos', 'supervisor', 'manager', 'jabatan', 'role', 'hak akses', 'wewenang'],
        'sesi' => ['sesi', 'session', 'perangkat', 'device', 'otp', 'login', 'logout', 'hp', 'blokir', 'hilang', 'crash', 'bug', 'helpdesk'],
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
        $combined = $normMessage . ' ' . $rawClean;
        $isHowTo = (bool) preg_match('/\b(bagaimana|gimana|kumaha|cara(?:nya|na)?|langkah|tutorial|tutor|panduan|alur(?:nya|na)?|proses(?:nya|na)?|mekanisme|step|tahapan|cara kerja|cara pakai|cara menggunakan|lewat mana|di mana|dimana|menu mana|ngurus(?:nya)?|lapor(?:nya)?)\b/iu', $combined);
        $isData = (bool) preg_match('/\b(berapa|sabaraha|total|jumlah|rekap|ringkasan|summary|statistik|stat|laporan|data|daftar|list|cek|lihat|tampilkan|tampilin|sebutkan|sebutin|status(?:nya|na)?|siapa|saha|sudah|udah|geus|belum|belom|acan|hari ini|poe ieu|kemarin|kamari|besok|isukan|bulan ini|bulan ieu|minggu ini|minggu ieu|tahun ini|terakhir|terbaru|aktif|pending|menunggu|disetujui|ditolak|sisa|saldo|tinggal berapa|ada berapa|kapan|iraha)\b/iu', $combined);
        $isTroubleshoot = (bool) preg_match('/\b(kenapa|mengapa|kunaon|naha|kok|error|gagal|tidak bisa|gak bisa|teu bisa|kendala|masalah|trouble|bug|crash|hang|lemot|force close|lupa|poho|kelupaan|kelewatan|hilang|leungit|mati|pareum|kosong|macet|muter|loading|blank|hitam|hideung|belum masuk|tidak terdeteksi|tidak kebaca|tidak dikenal|ditolak|ketolak|nyangkut|mentok)\b/iu', $combined);
        $isPolicy = (bool) preg_match('/\b(syarat(?:nya|na)?|aturan(?:nya|na)?|kebijakan(?:nya)?|ketentuan(?:nya)?|batas(?:nya|na)?|limit(?:nya)?|plafon(?:nya)?|maksimal(?:nya|na)?|minimal(?:nya|na)?|deadline|tenggat|sla|boleh|wajib|harus|perlu|toleransi(?:nya)?|tarif(?:nya)?|rate|formula|rumus(?:nya)?|hitung|beda(?:nya|na)?|perbedaan|hangus|efek(?:nya)?|dampak(?:nya)?|ngaruh|berapa lama|berapa menit|berapa hari sebelum|motong|dipotong)\b/iu', $combined);
        $isConcept = (bool) preg_match('/\b(apa itu|naon eta|apa sih|fungsi(?:nya)?|kegunaan|manfaat|arti(?:nya)?|hartina|maksud(?:nya)?|kepanjangan|singkatan|fitur|modul|menu apa saja|layanan apa saja|tentang aplikasi|tentang gomsn|bisa bantu apa|kamu bisa apa|bisa apa aja|bantuan|help)\b/iu', $combined);
        $isCompanyScope = (bool) preg_match('/\b(seluruh karyawan|semua karyawan|sakabeh karyawan|semua pegawai|seluruh pegawai|perusahaan|company|company wide|semua divisi|sa perusahaan|seluruh divisi|per divisi|antar divisi|karyawan hari ini|karyawan bulan ini|semua staf|tim saya|bawahan|divisi saya)\b/iu', $combined);
        $isPersonalScope = (bool) preg_match('/\b(saya|aku|urang|abdi|pribadi|milik saya|punya saya|akun saya)\b/iu', $combined);

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

        $isFollowUp = (bool) preg_match('/\b(caranya|cara|carana|syaratnya|syarat|aturannya|aturan|batasnya|batas|limitnya|limit|plafonnya|maksimalnya|maksimal|minimalnya|minimal|berapa lama|berapa hari|terus|lalu|kalau ditolak|mun ditolak|kalau lupa|mun poho|kalau batal|kalau hilang|dimana menunya|menu mana|dimana|punya saya|milik saya|datanya|statusnya|bulan ini|hari ini|detailnya|selengkapnya|jelaskan|maksudnya|contohnya|siapa yang approve|siapa yang acc|kapan cair|kapan dibayar|efeknya|dampaknya|gimana|bagaimana|kumaha)\b/iu', $normMessage);

        return $isFollowUp ? $ctx['domain'] : null;
    }

    /**
     * Extract canonical semantic concepts from any phrasing (formal, casual slang, idioms, English mix, Basa Sunda).
     *
     * @return array<string, bool>
     */
    public function extractSemanticConcepts(string $rawClean, string $normMessage): array
    {
        $text = ' ' . trim($normMessage . ' ' . $rawClean) . ' ';

        $patterns = [
            // Actions & Modifiers
            'howto' => '/\b(bagaimana|gimana|kumaha|cara(?:nya|na)?|langkah|prosedur|tutorial|tutor|panduan|alur(?:nya|na)?|tahapan|lewat mana|menu mana|di mana|dimana|ngajuin|mengajukan|ajukan|bikin|buat|daftar|ngurus(?:nya)?|mengurus)\b/iu',
            'troubleshoot' => '/\b(kenapa|mengapa|kunaon|naha|kok|gagal|error|ditolak|ketolak|tidak bisa|gak bisa|ga bisa|teu bisa|macet|muter|loading|blank|hitam|hideung|tidak terdeteksi|tidak muncul|tidak kebaca|gak kebaca|tidak dikenal|bug|crash|force close|hang|lemot|bermasalah|kendala|masalah|mentok|nyangkut|belum berubah|masih tertulis)\b/iu',
            'status_cek' => '/\b(sudah|udah|geus|belum|belom|acan|cek|lihat|tampilkan|berapa|sabaraha|total|jumlah|siapa|saha|rekap|daftar|list|sampai mana|sampe mana|tahap mana|tinggal berapa|sisa|saldo|status(?:nya|na)?)\b/iu',
            'syarat_aturan' => '/\b(syarat(?:nya|na)?|aturan(?:nya|na)?|ketentuan(?:nya)?|kebijakan(?:nya)?|wajib|harus|perlu|dokumen|lampiran|lampirkan|upload|unggah|bukti|boleh|diperbolehkan|emang harus|apakah harus)\b/iu',
            'batas_maksimal' => '/\b(maksimal(?:nya|na)?|max|paling besar|paling banyak|paling lama|paling lambat|paling telat|mentok|limit(?:nya)?|plafon(?:nya)?|tenggat|deadline|batas waktu|batas akhir|toleransi(?:nya)?|berapa menit|berapa lama|sampai jam berapa|batas(?:nya|na)?)\b/iu',
            'batas_minimal' => '/\b(minimal(?:nya|na)?|min|paling cepat|paling singkat|paling sedikit|berapa hari sebelum|h minus|h 3|h berapa|sebelum tanggal|masa kerja|30 menit|45 menit|kurang dari sejam|kurang dari 1 jam|minimal 1 jam)\b/iu',
            'lupa' => '/\b(lupa|poho|kelupaan|kelewatan|ketinggalan|tidak sempat|gak sempat|ga sempat|kelewatan|terlewat|kelewat)\b/iu',
            'koreksi_revisi' => '/\b(koreksi|revisi|edit|ubah|perbaiki|benerin|betulin|ganti nominal|salah input|salah ketik|salah nominal|salah pencet|salah klik|ubah keterangan|mengedit|merevisi|diedit)\b/iu',
            'batal' => '/\b(batal|batalkan|dibatalkan|membatalkan|pembatalan|cancel|gajadi|ga jadi|gak jadi|tidak jadi|urung|tarik kembali)\b/iu',
            'perbedaan' => '/\b(beda(?:nya|na)?|perbedaan|dibanding|dibandingkan|dipisah|terpisah|sama atau beda|sama nggak|sama tidak)\b/iu',
            'efek_dampak' => '/\b(efek(?:nya)?|dampak(?:nya)?|pengaruh(?:nya)?|ngaruh|akibat(?:nya)?|konsekuensi|risiko|resiko|kena apa)\b/iu',
            'ekspor_unduh' => '/\b(unduh|download|export|ekspor|cetak|print|excel|tarik data|tarik laporan)\b/iu',

            // Time
            'hari_ini' => '/\b(hari ini|sekarang|tadi|pagi ini|siang ini|sore ini|poe ieu|ayeuna)\b/iu',
            'kemarin' => '/\b(kemarin|kemaren|kmrn|kamari|semalam|tadi malam)\b/iu',
            'besok' => '/\b(besok|esok|bsk|isukan)\b/iu',
            'tujuh_hari' => '/\b(7 hari|tujuh hari|seminggu|sepekan|minggu terakhir|7 poe)\b/iu',
            'bulan_lalu' => '/\b(bulan lalu|bulan kemarin|bulan kamari|bulan sebelumnya)\b/iu',
            'bulan_ini' => '/\b(bulan ini|bulan ieu|periode ini|periode berjalan|sebulan ini)\b/iu',
            'hari_libur' => '/\b(hari libur|tanggal merah|libur nasional|cuti bersama|minggu|sabtu|weekend|akhir pekan)\b/iu',

            // Domain Concepts (rich everyday paraphrases!)
            'check_in' => '/\b(check in|checkin|cekin|clock in|tap masuk|tap pagi|absen masuk|absen datang|absen pagi|presensi masuk|masuk kerja|asup gawe)\b/iu',
            'check_out' => '/\b(check out|checkout|cekout|clock out|tap pulang|tap sore|absen pulang|absen balik|absen sore|presensi pulang|pulang kerja|balik gawe)\b/iu',
            'absensi' => '/\b(absen|absensi|presensi|kehadiran|hadir|tap|finger|masuk kerja|pulang kerja)\b/iu',
            'biometrik_wajah' => '/\b(biometrik|wajah|muka|beungeut|pameunteu|selfie|foto wajah|scan wajah|pindai wajah|kamera|face|layar hitam|layar hideung)\b/iu',
            'status_belum_checkin' => '/\b(masih belum check in|masih tertulis belum|padahal sudah foto|padahal udah absen|udah foto wajah tapi|sudah selfie tapi)\b/iu',
            'lokasi_gps' => '/\b(gps|lokasi|radius|geofence|koordinat|peta|map|maps|jarak kantor|luar jangkauan|out of range|titik lokasi)\b/iu',
            'terlambat' => '/\b(terlambat|telat|kesiangan|datang telat|masuk telat|lewat jam masuk|keterlambatan)\b/iu',
            'alpa' => '/\b(alpa|alpha|mangkir|bolos|tanpa keterangan|tanpa kabar|tidak masuk tanpa izin)\b/iu',
            'dinas_luar' => '/\b(dinas luar|luar kantor|di lapangan|tugas lapangan|di site|visit|kunjungan klien|kunjungan pelanggan|tugas luar|di jalan|kerja lapangan|lokasi pelanggan)\b/iu',
            'tanpa_internet' => '/\b(offline|tanpa internet|tidak ada sinyal|gak ada sinyal|ga ada sinyal|tidak ada jaringan|gak ada jaringan|ga ada jaringan|no signal|sinyal mati|jaringan mati|blank spot|blankspot|kuota habis|susah sinyal|koneksi putus|euweuh sinyal)\b|(?:sinyal|jaringan|internet|koneksi)(?:\w*)?(?:\s+\w+){0,3}\s+(?:mati|putus|hilang|pareum)/iu',
            'ganti_perangkat' => '/\b(ganti hp|hp baru|ponsel baru|ganti device|ganti perangkat|perangkat baru|reset device|reset perangkat|perangkat terkunci|biometrik terkunci|pindah hp|daftar ulang biometrik)\b/iu',
            'hp_hilang' => '/\b(hp hilang|ponsel hilang|hp leungit|hp kecurian|kehilangan hp|blokir sesi|force logout|keluarkan sesi)\b/iu',
            'hp_lowbat' => '/\b(lowbat|low batt|baterai habis|batre habis|hp mati|nitip absen|titip absen)\b/iu',
            'jadwal_shift' => '/\b(jadwal|shift|piket|jam kerja|jam masuk|jam pulang|kalender shift|kalender kerja)\b/iu',
            'tukar_shift' => '/\b(tukar shift|tuker shift|tukar jadwal|tuker jadwal|barter shift|barter jadwal|gantiin shift|gantian shift|tukeran shift|swap shift)\b/iu',
            'cuti' => '/\b(cuti|jatah libur|kuota libur|libur tahunan|ambil libur|leave)\b/iu',
            'izin' => '/\b(izin|ijin|perizinan)\b/iu',
            'sisa_saldo' => '/\b(sisa|saldo|jatah|kuota|tinggal berapa|masih ada berapa|kari sabaraha|tersisa)\b/iu',
            'sakit' => '/\b(sakit|gering|meriang|demam|flu|batuk|panas|pusing|opname|rawat inap|rumah sakit|tidak enak badan|gak enak badan|ga enak badan|kurang sehat|badan drop|lagi drop|izin sakit)\b/iu',
            'surat_dokter' => '/\b(surat dokter|skd|surat keterangan dokter|surat sakit|surat klinik|surat puskesmas|surat rs)\b/iu',
            'cuti_khusus' => '/\b(cuti khusus|nikah|menikah|kawin|istri melahirkan|istri lahiran|khitanan|sunatan|duka|meninggal|wafat|maot|kemalangan|keluarga meninggal|berduka)\b/iu',
            'cuti_melahirkan' => '/\b(cuti melahirkan|karyawati melahirkan|cuti hamil|cuti bersalin|jatah melahirkan)\b/iu',
            'izin_setengah_hari' => '/\b(setengah hari|setengah poe|half day|halfday)\b/iu',
            'hangus_carryover' => '/\b(hangus|kadaluarsa|expired|diakumulasi|akumulasi|carry over|dibawa tahun depan|tahun depan|diuangkan)\b/iu',
            'potongan_denda' => '/\b(potongan|denda|dipotong|potong gaji|potongan gaji|penalti|penalty|kena potong|pemotongan|deduction)\b/iu',
            'sanggahan' => '/\b(sanggahan|sanggah|komplain|protes|salah hitung|tidak sesuai|keliru|banding|keberatan)\b/iu',
            'formula_hitung' => '/\b(rumus|formula|cara hitung|ngitung|menghitung|perhitungan|hitungan|tarif denda|berapa rupiah|kalkulasi|simulasi)\b/iu',
            'payroll' => '/\b(payroll|penggajian|slip gaji|gajian)\b/iu',
            'kasbon_dana' => '/\b(kasbon|dana operasional|duit jalan|uang jalan|uang bensin|duit bensin|biaya tol|uang tol|pinjam dana|talangan|reimburse|reimbursement|pengajuan dana|uang operasional|duit operasional|duit kasbon|uang kasbon)\b/iu',
            'kasbon_bertumpuk' => '/\b(kasbon bertumpuk|kasbon double|kasbon dobel|dua kasbon|2 kasbon|kasbon baru padahal|belum lunas kasbon|belum dilunasi|belum lpj)\b/iu',
            'lpj_nota' => '/\b(lpj|settlement|pelunasan|pertanggungjawaban|nota(?:nya|na)?|struk(?:nya|na)?|bon(?:nya|na)?|kwitansi(?:nya|na)?|kuitansi(?:nya|na)?|bukti belanja|bukti pengeluaran|laporan nota|laporan bon|laporan struk|upload nota|upload struk)\b/iu',
            'nota_hilang' => '/\b(?:nota|struk|bon|kwitansi|kuitansi)(?:nya|na)?(?:\s+\w+){0,3}\s+(?:hilang|ilang|leungit)\b/iu',
            'sisa_kembalian' => '/\b(sisa kasbon|sisa duit kasbon|sisa uang kasbon|kembalian kasbon|balikin sisa|kembalikan sisa|lebih sedikit dari)\b/iu',
            'beli_barang' => '/\b(beli barang|pembelian|pengadaan|belanja barang|belanja alat|belanja perangkat|belanja kebutuhan|beli alat|beli perangkat|pengajuan beli|order barang|beli material|meuli barang|estimasi harga)\b/iu',
            'ambil_stok' => '/\b(stok|material|gudang|inventaris|dropcore|kabel|closure|patchcord|konektor|pigtail|ont|router|mikrotik|alat kerja|ambil barang gudang|minta stok|permintaan stok)\b/iu',
            'pencairan' => '/\b(cair|pencairan|transfer|ditransfer|masuk rekening|turun dana|dibayar|dibayarkan|pembayaran|bayar lembur|jadwal bayar)\b/iu',
            'lembur' => '/\b(lembur|lemburan|overtime|kerja tambahan|jam tambahan|jam ekstra|ekstra jam|nambah jam|kerja lembur)\b/iu',
            'lembur_remote' => '/\b(lembur remote|lembur wfh|lembur dari rumah|lembur ti imah|jarak jauh)\b/iu',
            'spk' => '/\b(spk|surat perintah kerja|perintah kerja|surat tugas)\b/iu',
            'alur_approval' => '/\b(approval|approve|acc|persetujuan|disetujui|siapa yang acc|siapa yang approve|siapa yang menyetujui|hierarki|alur persetujuan|tahap persetujuan|sampai tahap mana|pejabat berwenang)\b/iu',
            'password_akun' => '/\b(password|kata sandi|sandi|pass login|lupa login|gagal login|tidak bisa login|gak bisa login|reset password)\b/iu',
            'foto_profil' => '/\b(foto profil|poto profil|avatar|pp|gambar profil|ganti foto|ubah foto|ganti profil)\b/iu',
            'leaderboard' => '/\b(leaderboard|peringkat|ranking|juara|paling disiplin|paling rajin|top 5|lima besar|5 besar|skor disiplin)\b/iu',
            'bug_support' => '/\b(it support|helpdesk|kontak it|tim it|admin it|lapor bug|lapor error|aplikasi error|aplikasi crash|force close|nge bug|aplikasi bermasalah|web error|aplikasi lemot|aplikasi hang)\b/iu',
            'tiket_gangguan' => '/\b(tiket|ticket|gangguan|trouble|los|lampu merah|internet mati|inet mati|putus koneksi|backbone|broadband|noc|mttr|dispatch|eskalasi|reopen|closing|tutup tiket)\b/iu',
            'closing_tiket' => '/\b(closing|(?:di)?close tiket|(?:di)?tutup tiket|menutup tiket|menyelesaikan tiket|syarat tutup|bukti closing|bukti selesai tiket|redaman sebelum|tiket(?:\s+\w+){0,3}\s+(?:tidak bisa|gak bisa|ga bisa|gagal)\s+(?:di)?(?:close|closing|tutup))\b/iu',
            'eskalasi_sla' => '/\b(eskalasi|lewat sla|melewati sla|breach|breached|sla lewat|telat sla|stop clock)\b/iu',
            'indikator_port' => '/\b(indikator|warna port|lampu port|hijau kuning merah|hejo koneng beureum|arti status port|status port pon|status port olt|indikator warna)\b/iu',
            'jaringan_ftth' => '/\b(olt|pon|odp|ftth|topologi|port odp|kapasitas port|slot port|koordinat odp|ekspansi odp|sn ont|serial number ont|redaman opm|otdr)\b/iu',
            'karyawan_hrd' => '/\b(karyawan baru|pegawai baru|tambah karyawan|onboarding|resign|keluar kerja|nonaktif|nonaktifkan|mutasi divisi|pindah divisi|import excel|bulk import|broadcast|pengumuman massal|kontrak kerja|kontak karyawan|nomor hp karyawan|menu khusus hrd|fitur hrd|menu hrd)\b/iu',
            'perusahaan_scope' => '/\b(semua karyawan|seluruh karyawan|sakabeh karyawan|semua pegawai|seluruh pegawai|company wide|sa perusahaan|se perusahaan|semua divisi|seluruh divisi|antar divisi|per divisi)\b/iu',
            'akronim' => '/\b(kepanjangan|singkatan|arti spk|arti noc|arti olt|arti pon|arti odp|istilah)\b/iu',
        ];

        $concepts = [];
        foreach ($patterns as $name => $regex) {
            if (preg_match($regex, $text)) {
                $concepts[$name] = true;
            }
        }

        return $concepts;
    }

    /**
     * Resolve varied paraphrases (via canonical semantic concepts) to exact active ChatbotIntent intent_key.
     * Returns only bilingual-ready q* / v* / extra_* intent keys so both Indonesian and Sundanese responses work cleanly.
     *
     * @param array<string, bool> $c
     * @param array<string, float> $domains
     * @param array<string, bool> $style
     */
    public function resolveSemanticIntentKey(
        string $rawClean,
        string $normMessage,
        array $c,
        array $domains,
        array $style,
        bool $isSunda,
        ?string $contextDomain = null
    ): ?string {
        if (empty($c)) {
            return null;
        }

        $has = fn (string $k): bool => ! empty($c[$k]);
        $effDomain = $contextDomain ?? (! empty($domains) ? (string) array_key_first($domains) : null);

        // Skip if query is clearly asking for personal/dashboard/holiday live data handled by resolveSmartDataQuery
        if (preg_match('/\b(siapa atasan|atasan saya|bos saya|manager saya|supervisor saya|profil saya|biodata saya|data saya|info akun saya|id karyawan saya|kode karyawan saya|ringkasan dashboard|statistik dashboard|ringkasan bulan ini|statistik bulan ini|hari libur nasional|tanggal merah terdekat|kapan tanggal merah|kapan libur)\b/iu', $normMessage . ' ' . $rawClean)) {
            return null;
        }

        // 1. Akun, Profil, Keamanan, IT Support & Akronim
        if ($has('password_akun') && ($has('lupa') || $has('troubleshoot') || $has('howto') || str_contains($normMessage, 'reset') || str_contains($normMessage, 'pulih'))) {
            return 'q81_prosedur_reset_password_lupa';
        }
        if ($has('foto_profil') && ! $has('biometrik_wajah')) {
            return 'q80_cara_ganti_foto_profil';
        }
        if ($has('bug_support')) {
            return 'q85_kontak_it_support_error';
        }
        if ($has('ganti_perangkat')) {
            return 'v25_reset_device_biometrik';
        }
        if ($has('akronim') && ($has('jaringan_ftth') || $has('spk') || $has('tiket_gangguan'))) {
            return 'q88_kepanjangan_akronim_sistem';
        }

        // 2. Absensi & Presensi
        if ($has('status_belum_checkin')) {
            return 'v21_troubleshooting_status_belum_checkin';
        }
        if ($has('biometrik_wajah') && $has('troubleshoot') && ! $has('ganti_perangkat')) {
            return 'q5_troubleshooting_biometrik';
        }
        if ($has('lokasi_gps') && $has('troubleshoot')) {
            return 'q4_troubleshooting_gps';
        }
        if ($has('lokasi_gps') && ($has('kemarin') || str_contains($normMessage, 'riwayat') || str_contains($normMessage, 'tercatat')) && ! $has('troubleshoot') && ! $has('jaringan_ftth')) {
            return 'v24_cek_lokasi_gps_riwayat_absen';
        }
        if ($has('tanpa_internet') && ($has('absensi') || $has('check_in') || $has('check_out') || $has('dinas_luar') || $effDomain === 'absensi')) {
            return 'q13_absen_offline_kendala_jaringan';
        }
        if ($has('dinas_luar') && ($has('absensi') || $has('check_in') || $has('check_out')) && ! $has('kasbon_dana') && ! $has('troubleshoot') && ! $has('lembur') && ! $has('tanpa_internet')) {
            return 'q9_aturan_absen_luar_kantor';
        }
        if ($has('lupa') && ($has('check_out') || $has('check_in') || $has('absensi') || $effDomain === 'absensi') && ! $has('password_akun')) {
            return 'q8_prosedur_koreksi_absen';
        }
        if ($has('koreksi_revisi') && ($has('absensi') || $has('check_in') || $has('check_out') || $effDomain === 'absensi') && ! $has('potongan_denda')) {
            return 'q8_prosedur_koreksi_absen';
        }
        if ($has('check_out') && $has('batas_maksimal') && ! $has('lupa')) {
            return 'v22_batas_jam_checkout_harian';
        }
        if ($has('terlambat') && ($has('efek_dampak') || ($has('potongan_denda') && $has('leaderboard')))) {
            return 'q90_efek_terlambat_gaji_leaderboard';
        }
        if ($has('terlambat') && ($has('batas_maksimal') || $has('syarat_aturan')) && ! $has('status_cek') && ! $has('potongan_denda') && ! $has('formula_hitung')) {
            return 'q6_batas_toleransi_terlambat';
        }
        if ($has('terlambat') && $has('perusahaan_scope') && $has('status_cek')) {
            return 'q48_daftar_karyawan_terlambat_hari_ini';
        }
        if ($has('terlambat') && ($has('status_cek') || $has('bulan_ini')) && ! $has('batas_maksimal') && ! $has('potongan_denda') && ! $has('formula_hitung') && ! $has('efek_dampak')) {
            return 'q7_rekap_terlambat_bulan_ini';
        }
        if ($has('absensi') && $has('perusahaan_scope') && $has('status_cek') && $has('hari_ini')) {
            return 'q46_company_ringkasan_absensi_hari_ini';
        }
        if ($has('sakit') && $has('perusahaan_scope') && $has('status_cek')) {
            return 'q47_daftar_karyawan_sakit_hari_ini';
        }
        if ($has('absensi') && $has('hari_libur') && ! $has('lembur') && ! $has('jadwal_shift')) {
            return 'q15_aturan_absensi_hari_libur';
        }
        if (($has('absensi') || $has('check_in') || $has('check_out')) && $has('tujuh_hari')) {
            return 'q10_rekap_kehadiran_7_hari';
        }
        if (($has('absensi') || $has('check_in') || $has('check_out')) && $has('bulan_lalu')) {
            return 'q11_cara_lihat_log_bulan_lalu';
        }
        if (($has('absensi') || $has('check_in') || $has('check_out')) && $has('kemarin') && ! $has('lupa') && ! $has('lokasi_gps')) {
            return 'q2_riwayat_checkin_kemarin';
        }
        if (
            ($has('check_in') || ($has('absensi') && ! $has('check_out')))
            && ($has('hari_ini') || preg_match('/\b(sudah|udah|geus|belum|belom|acan)\b/iu', $normMessage . ' ' . $rawClean))
            && $has('status_cek')
            && ! $has('howto') && ! $has('troubleshoot') && ! $has('terlambat') && ! $has('dinas_luar') && ! $has('potongan_denda') && ! $has('perusahaan_scope') && ! $has('kemarin') && ! $has('tujuh_hari') && ! $has('bulan_lalu')
        ) {
            return 'q1_status_checkin_hari_ini';
        }
        if (($has('absensi') || $has('check_in') || $has('check_out')) && $has('howto') && ! $has('lupa') && ! $has('dinas_luar') && ! $has('tanpa_internet') && ! $has('terlambat') && ! $has('koreksi_revisi') && ! $has('potongan_denda') && ! $has('bulan_lalu') && ! $has('hp_lowbat')) {
            return 'q3_cara_absen_biometrik_gps';
        }

        // 3. Jadwal & Shift
        if ($has('tukar_shift')) {
            return 'q18_kebijakan_tukar_shift';
        }
        if ($has('jadwal_shift') && ($has('hari_ini') || $has('besok')) && ! $has('hari_libur') && ! $has('troubleshoot')) {
            return 'q16_jadwal_shift_hari_ini_besok';
        }
        if ($has('jadwal_shift') && ($has('howto') || str_contains($normMessage, 'kalender') || str_contains($normMessage, 'sebulan') || str_contains($normMessage, 'satu bulan')) && ! $has('hari_libur')) {
            return 'q17_cara_cek_kalender_shift';
        }

        // 4. Cuti, Izin & Sakit
        if ($has('sakit') && $has('perbedaan')) {
            return 'v20_perbedaan_status_izin_sakit';
        }
        if ($has('cuti') && $has('perbedaan')) {
            return 'q29_perbedaan_jenis_cuti_izin';
        }
        if ($has('sakit') && ($has('surat_dokter') || $has('syarat_aturan') || $has('howto') || count(explode(' ', $normMessage)) <= 5) && ! $has('perusahaan_scope') && ! $has('potongan_denda') && ! $has('status_cek')) {
            return 'q22_syarat_dokumen_izin_sakit';
        }
        if ($has('cuti_khusus') && ! $has('cuti_melahirkan')) {
            return 'v33_jenis_cuti_khusus';
        }
        if (($has('cuti') || $effDomain === 'cuti') && $has('batal')) {
            return 'q24_pembatalan_cuti_disetujui';
        }
        if (($has('cuti') || $effDomain === 'cuti') && $has('hangus_carryover')) {
            return 'q30_aturan_hangus_sisa_cuti';
        }
        if (($has('cuti') || $effDomain === 'cuti') && ($has('batas_minimal') || ($has('batas_maksimal') && ! $has('cuti_melahirkan'))) && ! $has('sisa_saldo') && ! $has('hangus_carryover') && ! $has('cuti_melahirkan')) {
            return 'q21_sla_batas_pengajuan_cuti';
        }
        if (($has('cuti') || $effDomain === 'cuti') && $has('sisa_saldo') && ! $has('hangus_carryover') && ! $has('perusahaan_scope') && ! $has('cuti_melahirkan')) {
            return 'q19_sisa_saldo_cuti';
        }
        if (($has('cuti') || $has('izin') || $effDomain === 'cuti') && $has('alur_approval') && ($has('status_cek') || $has('howto')) && ! $has('kasbon_dana') && ! $has('beli_barang') && ! $has('lembur')) {
            return 'q23_cara_cek_approval_izin';
        }
        if (($has('cuti') || $effDomain === 'cuti') && $has('howto') && ! $has('batal') && ! $has('sakit') && ! $has('sisa_saldo') && ! $has('cuti_melahirkan') && ! $has('izin_setengah_hari')) {
            return 'q20_langkah_ajukan_cuti';
        }

        // 5. Potongan & Denda
        if ($has('potongan_denda') && $has('sanggahan')) {
            return 'q28_alur_sanggahan_potongan';
        }
        if ($has('potongan_denda') && $has('formula_hitung')) {
            return 'q26_formula_hitung_potongan';
        }
        if ($has('potongan_denda') && $has('payroll')) {
            return 'q59_integrasi_potongan_ke_payroll';
        }
        if ($has('potongan_denda') && $has('ekspor_unduh')) {
            return 'q51_unduh_rekap_absensi_potongan';
        }
        if ($has('potongan_denda') && $has('troubleshoot') && ! $has('sanggahan')) {
            return 'q25_penjelasan_nominal_potongan';
        }
        if ($has('potongan_denda') && $has('status_cek') && ! $has('perusahaan_scope') && ! $has('formula_hitung') && ! $has('sanggahan') && ! $has('howto')) {
            return 'q27_total_potongan_denda_saya';
        }

        // 6. Pengajuan (Kasbon, Beli Barang, Permintaan Stok)
        if ($has('lpj_nota') && ! $has('nota_hilang') && ! $has('sisa_kembalian') && ! $has('kasbon_bertumpuk')) {
            return 'q33_prosedur_settlement_kasbon';
        }
        if (($has('kasbon_dana') || $effDomain === 'pengajuan') && $has('batas_maksimal') && ! $has('lpj_nota') && ! $has('pencairan') && ! $has('kasbon_bertumpuk') && ! $has('lembur')) {
            return 'q32_batas_maksimal_kasbon';
        }
        if (($has('kasbon_dana') || $effDomain === 'pengajuan') && $has('pencairan') && ($has('batas_maksimal') || preg_match('/\b(berapa lama|kapan|iraha|cepat|hari)\b/iu', $normMessage)) && ! $has('lembur')) {
            return 'v55_sla_pencairan_dana_lembur';
        }
        if (($has('kasbon_dana') || $has('beli_barang') || $effDomain === 'pengajuan') && $has('koreksi_revisi') && ! $has('absensi')) {
            return 'q45_revisi_pengajuan_pending';
        }
        if ($has('beli_barang') && $has('alur_approval')) {
            return 'q37_hierarki_approval_pembelian';
        }
        if ($has('beli_barang') && ($has('howto') || $has('syarat_aturan')) && ! $has('alur_approval') && ! $has('koreksi_revisi')) {
            return 'q34_langkah_pengajuan_beli_barang';
        }
        if ($has('ambil_stok') && $has('tiket_gangguan')) {
            return 'q71_permintaan_stok_dari_tiket';
        }
        if ($has('ambil_stok') && ! $has('beli_barang') && ($has('howto') || preg_match('/\b(permintaan stok|minta stok|ajukan stok|ambil barang gudang)\b/iu', $normMessage)) && ! $has('status_cek')) {
            return 'q35_cara_permintaan_stok_material';
        }
        if ($has('kasbon_dana') && $has('howto') && ! $has('lpj_nota') && ! $has('batas_maksimal') && ! $has('koreksi_revisi') && ! $has('pencairan') && ! $has('kasbon_bertumpuk') && ! $has('nota_hilang') && ! $has('sisa_kembalian')) {
            return 'q31_cara_ajukan_kasbon';
        }
        if (($has('kasbon_dana') || $has('beli_barang')) && $has('status_cek') && preg_match('/\b(terakhir|tahap mana|sampai mana|sampe mana|sudah di acc|udah di acc)\b/iu', $normMessage . ' ' . $rawClean)) {
            return 'q36_cek_status_pengajuan_terakhir';
        }

        // 7. Lembur & SPK
        if (($has('lembur') || $effDomain === 'lembur') && $has('spk') && ($has('ekspor_unduh') || preg_match('/\b(lihat|akses|cari|dimana|di mana)\b/iu', $normMessage)) && ! $has('syarat_aturan')) {
            return 'q40_cara_akses_dokumen_spk';
        }
        if (($has('lembur') || $effDomain === 'lembur') && $has('batas_minimal') && ! $has('lembur_remote')) {
            return 'v53_durasi_minimal_lembur';
        }
        if (($has('lembur') || $effDomain === 'lembur') && $has('hari_libur') && ! $has('absensi')) {
            return 'q44_aturan_lembur_hari_libur';
        }
        if (($has('lembur') || $effDomain === 'lembur') && $has('batas_maksimal') && preg_match('/\b(klaim|setelah|selesai|deadline|tenggat|batas waktu|paling lambat|paling telat|maksimal pengajuan|batas pengajuan)\b/iu', $normMessage . ' ' . $rawClean)) {
            return 'q41_batas_waktu_klaim_lembur';
        }
        if (($has('lembur') || $effDomain === 'lembur') && ($has('spk') || $has('syarat_aturan')) && ! $has('batas_minimal') && ! $has('hari_libur') && ! $has('lembur_remote') && ! $has('pencairan')) {
            return 'q39_syarat_spk_lembur';
        }
        if (($has('lembur') || $effDomain === 'lembur') && $has('troubleshoot') && preg_match('/\b(ditolak|ketolak|reject|alasan)\b/iu', $normMessage . ' ' . $rawClean)) {
            return 'q43_alasan_lembur_ditolak';
        }
        if (($has('lembur') || $effDomain === 'lembur') && $has('howto') && ! $has('spk') && ! $has('batas_minimal') && ! $has('hari_libur') && ! $has('lembur_remote') && ! $has('pencairan')) {
            return 'q38_cara_ajukan_lembur';
        }
        if ($has('lembur') && $has('status_cek') && $has('bulan_ini') && ! $has('perusahaan_scope') && ! $has('batas_minimal') && ! $has('batas_maksimal') && ! $has('syarat_aturan')) {
            return 'q42_total_jam_lembur_bulan_ini';
        }

        // 8. Leaderboard
        if ($has('leaderboard') && ($has('syarat_aturan') || $has('formula_hitung') || preg_match('/\b(kriteria|indikator|penilaian|nilai|cara masuk)\b/iu', $normMessage))) {
            return 'q55_kriteria_penilaian_leaderboard';
        }
        if ($has('leaderboard') && ($has('status_cek') || preg_match('/\b(siapa|juara|top|terbaik|rajin|5 besar|lima besar)\b/iu', $normMessage))) {
            return 'q56_top_5_leaderboard_disiplin';
        }

        // 9. Tiket Gangguan & Jaringan FTTH
        if ($has('closing_tiket')) {
            return 'q68_syarat_bukti_closing_tiket';
        }
        if ($has('eskalasi_sla')) {
            return 'q69_alur_eskalasi_sla_tiket';
        }
        if ($has('indikator_port')) {
            return 'q66_arti_status_port_pon_olt';
        }
        if ($has('tiket_gangguan') && $has('perbedaan')) {
            return 'q74_perbedaan_tiket_noc_vs_pasang_baru';
        }
        if ($has('tiket_gangguan') && $has('howto') && preg_match('/\b(buat|bikin|lapor|open|baru)\b/iu', $normMessage) && ! $has('closing_tiket')) {
            return 'q61_cara_buat_tiket_gangguan';
        }
        if ($has('tiket_gangguan') && $has('howto') && preg_match('/\b(update|progres|kronologi|perbarui)\b/iu', $normMessage)) {
            return 'q63_update_progres_tiket';
        }
        if ($has('jaringan_ftth') && $has('howto') && str_contains($normMessage, 'koordinat')) {
            return 'q75_cara_input_koordinat_odp';
        }
        if ($has('jaringan_ftth') && $has('howto') && preg_match('/\b(kapasitas|slot|sisa port)\b/iu', $normMessage)) {
            return 'q72_cek_kapasitas_port_odp';
        }

        // 10. HRD & Karyawan
        if ($has('karyawan_hrd')) {
            if (preg_match('/\b(baru|tambah|onboarding|daftarkan)\b/iu', $normMessage)) {
                return 'q58_onboarding_karyawan_baru';
            }
            if (preg_match('/\b(resign|nonaktif|nonaktifkan|keluar kerja)\b/iu', $normMessage)) {
                return 'q57_cara_ubah_status_aktif_karyawan';
            }
            if (preg_match('/\b(kontak|nomor hp|telepon|whatsapp|wa)\b/iu', $normMessage)) {
                return 'q49_cara_cari_kontak_karyawan';
            }
            if (preg_match('/\b(menu khusus hrd|fitur hrd|menu hrd)\b/iu', $normMessage)) {
                return 'q84_menu_khusus_divisi_hrd';
            }
        }

        return null;
    }

    /**
     * Bilingual semantic answers for common everyday operational questions whose seeded intents
     * were originally only written in Basa Sunda (so Indonesian users get crisp Indonesian answers too!).
     *
     * @param array<string, bool> $c
     * @return ?array{response: string, confidence: float, matched_intent: string, action_url: ?string, action_label: ?string, suggestions: ?list<string>, domain: string}
     */
    public function resolveSemanticBilingualPolicy(string $rawClean, string $normMessage, array $c, bool $isSunda, ?string $contextDomain = null): ?array
    {
        $has = fn (string $k): bool => ! empty($c[$k]);

        // 1. HP Hilang / Blokir Sesi / Force Logout
        if ($has('hp_hilang')) {
            return [
                'response' => $isSunda
                    ? "Upami HP karyawan leungit, segera hubungi Admin IT atawa HRD kanggo mencabut sesi login aktif (Force Logout) dina menu Sesi & Perangkat sarta Reset Password di menu Data Karyawan supados akun aman."
                    : "Jika HP hilang atau dicuri, segera hubungi Admin IT atau HRD untuk mencabut sesi login aktif (Force Logout) melalui menu Sesi & Perangkat serta melakukan Reset Password di menu Data Karyawan agar akun Anda tetap aman.",
                'confidence' => 92.0,
                'matched_intent' => 'smart_policy_hp_hilang',
                'action_url' => $this->safeRoute('portal.sessions') ?? $this->safeRoute('portal.profile'),
                'action_label' => '🔒 Buka Sesi & Perangkat',
                'suggestions' => null,
                'domain' => 'sesi',
            ];
        }

        // 2. HP Lowbat / Nitip Absen
        if ($has('hp_lowbat')) {
            return [
                'response' => $isSunda
                    ? "Teu kenging nitip absen ka rencangan kusabab sistem wajib verifikasi biometrik pameunteu (wajah) masing-masing. Upami HP pareum/lowbat pas rek pulang, pas HP hurung deui mangga ajukeun Koreksi Absen di menu Riwayat Absen Saya katut bukti pendukungna."
                    : "Tidak diperbolehkan menitip absen ke rekan kerja karena sistem mewajibkan verifikasi biometrik wajah masing-masing karyawan. Jika HP mati/lowbat saat jam pulang, silakan ajukan Koreksi Absen melalui menu Riwayat Absen Saya begitu HP menyala disertai bukti pendukung.",
                'confidence' => 92.0,
                'matched_intent' => 'smart_policy_nitip_absen',
                'action_url' => $this->safeRoute('attendance.history'),
                'action_label' => '📝 Buka Riwayat Absen Saya',
                'suggestions' => null,
                'domain' => 'absensi',
            ];
        }

        // 3. Nota / Struk Kasbon Hilang
        if ($has('nota_hilang')) {
            return [
                'response' => $isSunda
                    ? "Upami nota/struk belanja kasbon leungit, anjeun kedah nyuhunkeun salinan nota/invoice ti toko, atawa ngadamel Kwitansi Pengganti / Berita Acara Pengeluaran anu ditandatanganan ku Atasan Langsung kanggo dilampirkeun dina LPJ ka Finance."
                    : "Jika nota/struk belanja kasbon hilang, mintalah salinan nota/invoice dari toko tempat pembelian. Jika tidak memungkinkan, buat Kwitansi Pengganti atau Berita Acara Pengeluaran yang disetujui/ditandatangani Atasan Langsung untuk dilampirkan pada LPJ ke Finance.",
                'confidence' => 92.0,
                'matched_intent' => 'smart_policy_nota_hilang',
                'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                'action_label' => '📋 Buka Pengajuan Saya',
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // 4. Kasbon Bertumpuk / Belum Lunas LPJ
        if ($has('kasbon_bertumpuk')) {
            return [
                'response' => $isSunda
                    ? "Teu tiasa. Karyawan wajib ngarengsekeun laporan pertanggungjawaban (LPJ nota/struk) sareng pelunasan kasbon samemehna heula sateuacan ngajukeun kasbon operasional anyar."
                    : "Tidak bisa. Karyawan wajib menyelesaikan laporan pertanggungjawaban (LPJ nota/struk) dan pelunasan kasbon sebelumnya terlebih dahulu sebelum mengajukan kasbon operasional baru.",
                'confidence' => 92.0,
                'matched_intent' => 'smart_policy_kasbon_bertumpuk',
                'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                'action_label' => '📋 Buka Pengajuan Saya',
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // 5. Pengembalian Sisa Uang Kasbon
        if ($has('sisa_kembalian')) {
            return [
                'response' => $isSunda
                    ? "Upami pengeluaran langkung saeutik ti dana kasbon nu cair, lampirkeun nota pengeluaran aslina dina LPJ di menu Pengajuan Saya, teras transfer atawa setor sisa danana ka rekening Finance katut upload bukti transfer pengembalianana supados status kasbon Lunas (Settled)."
                    : "Jika total pengeluaran lebih kecil dari kasbon yang dicairkan, unggah nota pengeluaran asli pada menu Pengajuan Saya (LPJ), lalu kembalikan/transfer selisih sisa dananya ke bagian Finance beserta bukti transfer pengembalian agar status kasbon berubah menjadi Lunas (Settled).",
                'confidence' => 92.0,
                'matched_intent' => 'smart_policy_sisa_kasbon',
                'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                'action_label' => '📋 Buka Pengajuan Saya',
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // 6. Cuti Melahirkan (Karyawati)
        if ($has('cuti_melahirkan')) {
            return [
                'response' => $isSunda
                    ? "Hak cuti melahirkan kanggo karyawati nyaeta salila 3 bulan (umumna 1,5 bulan sateuacan perkiraan lahir sareng 1,5 bulan saatos ngalahirkeun) sarta henteu motong jatah cuti taunan."
                    : "Hak cuti melahirkan untuk karyawati adalah selama 3 bulan (umumnya 1,5 bulan sebelum Hari Perkiraan Lahir dan 1,5 bulan setelah melahirkan), berbayar penuh, dan tidak memotong kuota cuti tahunan. Ajukan melalui menu Cuti & Izin Saya dengan melampirkan surat keterangan dokter/bidan.",
                'confidence' => 92.0,
                'matched_intent' => 'smart_policy_cuti_melahirkan',
                'action_url' => $this->safeRoute('attendance.leaves'),
                'action_label' => '🌴 Buka Cuti & Izin Saya',
                'suggestions' => null,
                'domain' => 'cuti',
            ];
        }

        // 7. Izin Setengah Hari
        if ($has('izin_setengah_hari') && ($has('izin') || $has('cuti') || $contextDomain === 'cuti')) {
            return [
                'response' => $isSunda
                    ? "Bisa. Kanggo kaperluan mendesak setengah poe, anjeun tiasa ngajukeun Izin dina menu Cuti & Izin Saya kalayan nyantumkeun katerangan jam izinna sarta konfirmasi ka Atasan Langsung."
                    : "Bisa. Untuk keperluan mendesak setengah hari, ajukan permohonan melalui menu Cuti & Izin Saya dengan mencantumkan keterangan rentang jam izin secara jelas serta berkoordinasi dengan Atasan Langsung untuk persetujuan.",
                'confidence' => 91.0,
                'matched_intent' => 'smart_policy_izin_setengah_hari',
                'action_url' => $this->safeRoute('attendance.leaves'),
                'action_label' => '🌴 Buka Cuti & Izin Saya',
                'suggestions' => null,
                'domain' => 'cuti',
            ];
        }

        // 8. Jadwal Pembayaran Uang Lembur
        if (($has('lembur') || $contextDomain === 'lembur') && $has('pencairan')) {
            return [
                'response' => $isSunda
                    ? "Uang kompensasi lembur nu parantos disatujuan (Approved) ku Atasan jeung HRD bakal diakumulasikeun sareng dibayarkeun bareng jeung jadwal penggajian bulanan (payroll) atawa pencairan Finance sesuai periode cut-off."
                    : "Uang kompensasi lembur yang telah disetujui (Approved) oleh Atasan & HRD akan diakumulasikan dan dibayarkan oleh Finance bersamaan dengan periode penggajian bulanan (payroll) atau sesuai jadwal cut-off pencairan lembur.",
                'confidence' => 91.0,
                'matched_intent' => 'smart_policy_jadwal_bayar_lembur',
                'action_url' => $this->safeRoute('gomsn.overtime.index'),
                'action_label' => '💼 Buka Halaman Lembur',
                'suggestions' => null,
                'domain' => 'lembur',
            ];
        }

        // 9. Lembur Remote / WFH
        if ($has('lembur_remote')) {
            return [
                'response' => $isSunda
                    ? "Lembur jarak jauh (remote/WFH) kanggo nanganan gangguan darurat tiasa diklaim di menu Lembur Saya salami aya penugasan resmi/SPK ti Atasan sarta dilampirkeun bukti log aktivitas atawa nomor tiket penangananna."
                    : "Lembur jarak jauh (remote/WFH) untuk penanganan gangguan darurat dapat diklaim melalui menu Lembur Saya selama ada penugasan resmi/SPK dari Atasan serta melampirkan bukti log pekerjaan atau nomor tiket yang ditangani.",
                'confidence' => 91.0,
                'matched_intent' => 'smart_policy_lembur_remote',
                'action_url' => $this->safeRoute('gomsn.overtime.create'),
                'action_label' => '⏰ Ajukan Lembur',
                'suggestions' => null,
                'domain' => 'lembur',
            ];
        }

        return null;
    }

    /**
     * Live Diagnostic & Troubleshooting Resolver for "Kenapa tidak bisa...", "Kenapa belum sampai ke Finance...",
     * "Kenapa gagal...", "Kenapa tertahan..." across Absensi, Biometrik, Pengajuan, Lembur, Cuti, Shift, and Tiket.
     *
     * @param array<string, bool> $c
     * @param array<string, float> $domains
     * @param array<string, bool> $style
     * @return ?array{response: string, confidence: float, matched_intent: string, action_url: ?string, action_label: ?string, suggestions: ?list<string>, domain: string}
     */
    public function resolveTroubleshootingDiagnosticQuery(
        ?User $user,
        string $rawClean,
        string $normMessage,
        array $c,
        array $domains,
        array $style,
        bool $isSunda,
        ?string $contextDomain = null
    ): ?array {
        $text = ' ' . trim($normMessage . ' ' . $rawClean) . ' ';
        $has = fn (string $k): bool => ! empty($c[$k]);
        $effDomain = $contextDomain ?? (! empty($domains) ? (string) array_key_first($domains) : null);

        $isCannotOrBlocked = (bool) preg_match('/\b(tidak bisa|gak bisa|ga bisa|teu bisa|gagal|error|diblokir|terkunci|ditolak sistem|tidak muncul|belum muncul|tidak ada tombol)\b/iu', $text);
        $isNotReachingOrStuck = (bool) preg_match('/\b(tidak sampai|belum sampai|gak sampai|ga sampai|belum masuk|tidak masuk|tidak muncul|belum muncul|tertahan|nyangkut|mandek|stuck|lama banget|belum di acc|belum disetujui|masih pending|belum cair|tidak cair)\b/iu', $text);
        $isWhyQuestion = (bool) preg_match('/\b(kenapa|mengapa|kunaon|naha|kok|penyebab|solusi|masalah|kendala)\b/iu', $text);

        if (! $isCannotOrBlocked && ! $isNotReachingOrStuck && ! $isWhyQuestion && ! $has('troubleshoot')) {
            return null;
        }

        // =====================================================================
        // 1. KENAPA PENGAJUAN TIDAK SAMPAI KE FINANCE / TERTAHAN / BELUM MASUK FINANCE
        // =====================================================================
        $mentionsPengajuan = ($has('kasbon_dana') || $has('beli_barang') || $has('ambil_stok') || isset($domains['pengajuan']) || $effDomain === 'pengajuan')
            && ! $has('cuti') && ! $has('izin') && ! $has('sakit') && ! $has('lembur') && ! $has('absensi');
        $mentionsFinanceOrFlow = (bool) preg_match('/\b(finance|keuangan|pencairan|cair|atasan|direksi|executive|approval|acc|tahap)\b/iu', $text);

        if ($mentionsPengajuan && ! $has('koreksi_revisi') && ($isNotReachingOrStuck || ($isWhyQuestion && $mentionsFinanceOrFlow))) {
            $liveStatus = '';
            if ($user) {
                $latest = Pengajuan::with('atasan')->where('user_id', $user->id)->latest()->first();
                if ($latest) {
                    $st = strtolower((string) $latest->status);
                    $nom = 'Rp ' . number_format((float) $latest->nominal_total, 0, ',', '.');
                    $atasanName = $latest->atasan?->name ?? ($user->resolveEffectiveApprover('attendance')?->name ?? 'Atasan Divisi');
                    $diagReason = match ($st) {
                        'draft' => "Masih berstatus DRAFT (belum Anda klik tombol Submit/Ajukan, sehingga belum terkirim ke Atasan maupun Finance).",
                        'menunggu_atasan' => "Masih berstatus MENUNGGU ATASAN ({$atasanName}). Di sistem GOMSN, pengajuan baru masuk ke antrean Finance SETELAH disetujui oleh Atasan.",
                        'menunggu_executive' => "Masih berstatus MENUNGGU EXECUTIVE / DIREKSI karena nominalnya ({$nom}) di atas plafon Rp 5.000.000. Setelah Direksi menyetujui, pengajuan otomatis masuk ke Finance.",
                        'menunggu_finance' => "SUDAH masuk di antrean Finance (status: MENUNGGU FINANCE) dan sedang menunggu proses verifikasi/pencairan oleh tim Finance.",
                        'ditolak' => "Berstatus DITOLAK. Pengajuan yang ditolak oleh Atasan otomatis dihentikan dan tidak diteruskan ke daftar Finance.",
                        'dicairkan', 'menunggu_bukti' => "Sudah DICAIRKAN oleh Finance (status: " . strtoupper($st) . "). Jangan lupa unggah bukti nota realisasi (LPJ) maksimal 7 hari.",
                        'selesai' => "Sudah SELESAI (dana telah dicairkan dan LPJ sudah terverifikasi).",
                        default => "Saat ini berstatus: " . strtoupper($st) . ".",
                    };
                    $liveStatus = "\n\n🔍 Cek Live Pengajuan Terakhir Anda ({$latest->nomor_pengajuan} — {$nom}):\n• {$diagReason}";
                } else {
                    $liveStatus = "\n\n🔍 Cek Live Akun Anda: Saat ini Anda belum memiliki riwayat pengajuan di database.";
                }
            }

            $resp = ($isSunda
                ? "Penyebab pengajuan (Kasbon/Beli/Stok) teu acan nepi atawa teu muncul di halaman Finance dina sistem GOMSN:\n"
                    . "1. Masih Berstatus DRAFT: Pengajuan nembe disimpen tapi acan diklik tombol Submit / Ajukan.\n"
                    . "2. Masih Menunggu ACC Atasan (MENUNGGU_ATASAN): Halaman Finance ngan nampilkeun pengajuan nu GEUS di-ACC ku Atasan Langsung/Supervisor divisi.\n"
                    . "3. Menunggu Approval Executive/Direksi (MENUNGGU_EXECUTIVE): Kanggo pengajuan Dana/Beli di luhur Rp 5.000.000, saatos ACC Atasan wajib di-ACC Direksi heula sateuacan asup ka Finance.\n"
                    . "4. Ditolak ku Atasan (DITOLAK): Pengajuan nu ditolak di tahap Atasan otomatis eureun jeung moal muncul di Finance."
                : "Penyebab pengajuan (Kasbon/Beli/Stok) tidak sampai atau belum muncul di daftar Finance pada sistem GOMSN:\n"
                    . "1. Masih Berstatus DRAFT: Pengajuan baru disimpan sebagai draft dan belum ditekan tombol Submit / Ajukan.\n"
                    . "2. Belum Di-ACC Atasan (MENUNGGU_ATASAN): Di GOMSN, halaman Finance hanya menampilkan pengajuan yang SUDAH disetujui Atasan Divisi. Hubungi Atasan Langsung Anda untuk melakukan approval terlebih dahulu.\n"
                    . "3. Tertahan di Approval Executive / Direksi (MENUNGGU_EXECUTIVE): Jika nominal pengajuan Dana/Beli di atas Rp 5.000.000, setelah di-ACC Atasan pengajuan wajib disetujui Direksi/CEO terlebih dahulu sebelum masuk ke Finance.\n"
                    . "4. Ditolak oleh Atasan (DITOLAK): Pengajuan yang ditolak pada tahap Atasan otomatis dihentikan dan tidak akan muncul di halaman Finance.")
                . $liveStatus;

            return [
                'response' => $resp,
                'confidence' => 96.0,
                'matched_intent' => 'smart_diag_pengajuan_tidak_sampai_finance',
                'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                'action_label' => '📋 Cek Status Pengajuan Saya',
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // =====================================================================
        // 2. KENAPA PENGAJUAN TIDAK BISA DIEDIT / DIREVISI
        // =====================================================================
        if ($mentionsPengajuan && $has('koreksi_revisi') && ($isCannotOrBlocked || $isWhyQuestion) && preg_match('/\b(tidak bisa|gak bisa|ga bisa|teu bisa|gagal|terkunci|hilang|tidak ada)\b/iu', $text)) {
            return [
                'response' => $isSunda
                    ? "Pengajuan di GOMSN ngan tiasa diedit/direvisi ku pemohon salami statusna masih DRAFT atawa MENUNGGU_ATASAN (teu acan aya tindakan Approved ti Atasan). Upami statusna tos disatujuan Atasan (Menunggu Executive / Menunggu Finance), tos dicairkeun, rengse, atawa ditolak, tombol Edit otomatis dikonci demi kaamanan audit."
                    : "Di sistem GOMSN, pengajuan hanya dapat diedit atau direvisi oleh pemohon selama statusnya masih DRAFT atau MENUNGGU_ATASAN (belum disetujui Atasan). Apabila pengajuan sudah di-ACC Atasan (Menunggu Executive / Menunggu Finance), sudah dicairkan, selesai, atau ditolak, fitur Edit otomatis dikunci demi integritas audit.",
                'confidence' => 95.0,
                'matched_intent' => 'smart_diag_tidak_bisa_edit_pengajuan',
                'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                'action_label' => '📋 Buka Pengajuan Saya',
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // =====================================================================
        // 3. KENAPA TIDAK BISA BUAT PENGAJUAN BARU / PENGAJUAN DIBLOKIR
        // =====================================================================
        if ($mentionsPengajuan && $isCannotOrBlocked && ! $has('koreksi_revisi') && ! $has('lpj_nota') && ! $has('lembur')) {
            $overdueInfo = '';
            if ($user) {
                $overdueItems = Pengajuan::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['dicairkan', 'menunggu_bukti'])
                    ->get();
                if ($overdueItems->isNotEmpty()) {
                    $codes = $overdueItems->pluck('nomor_pengajuan')->implode(', ');
                    $overdueInfo = "\n\n🔍 Cek Live Akun Anda: Anda memiliki {$overdueItems->count()} pengajuan berstatus DICAIRKAN/MENUNGGU BUKTI ({$codes}) yang belum selesai LPJ/realisasi. Segera unggah bukti nota realisasinya agar blokir terbuka.";
                } else {
                    $overdueInfo = "\n\n🔍 Cek Live Akun Anda: Akun Anda bersih dari tunggakan LPJ (>7 hari), sehingga Anda dapat membuat pengajuan baru.";
                }
            }

            $resp = "Penyebab Anda tidak bisa membuat atau mengirim pengajuan baru di GOMSN:\n"
                . "1. Diblokir Otomatis karena Tunggakan Nota Realisasi (LPJ > 7 Hari): Jika ada pengajuan sebelumnya yang sudah cair (status DICAIRKAN / MENUNGGU BUKTI) namun belum diunggah bukti nota realisasinya melewati batas 7 hari, sistem otomatis memblokir pembuatan pengajuan baru.\n"
                . "2. Kasbon Sebelumnya Belum Lunas (Settled): Selesaikan dulu laporan nota pengeluaran pengajuan sebelumnya.\n"
                . "3. Rincian Item Belum Lengkap: Pastikan Keperluan, Nama Barang/Deskripsi, Jumlah (Qty minimal 1), dan Harga Satuan sudah terisi tanpa kolom wajib yang kosong."
                . $overdueInfo;

            return [
                'response' => $resp,
                'confidence' => 95.0,
                'matched_intent' => 'smart_diag_tidak_bisa_buat_pengajuan',
                'action_url' => $this->safeRoute('gomsn.pengajuan.index'),
                'action_label' => '📋 Buka Pengajuan Saya',
                'suggestions' => null,
                'domain' => 'pengajuan',
            ];
        }

        // =====================================================================
        // 4. KENAPA TIDAK BISA ABSEN (DIAGNOSTIK LIVE + 5 PENYEBAB UTAMA)
        // =====================================================================
        $mentionsAbsen = $has('absensi') || $has('check_in') || $has('check_out') || isset($domains['absensi']) || $effDomain === 'absensi';
        $isSpecificOtherAbsenIntent = $has('biometrik_wajah')
            || $has('lokasi_gps')
            || $has('tanpa_internet')
            || $has('lupa')
            || $has('ganti_perangkat')
            || $has('hp_lowbat')
            || $has('status_belum_checkin')
            || $has('terlambat')
            || $has('potongan_denda')
            || $has('hari_libur');

        if ($mentionsAbsen && ($isCannotOrBlocked || ($isWhyQuestion && $has('troubleshoot'))) && ! $isSpecificOtherAbsenIntent) {
            $liveDiag = '';
            if ($user) {
                $hasFace = FaceTemplate::query()->where('user_id', $user->id)->exists();
                $todayStr = Carbon::now('Asia/Jakarta')->toDateString();
                $attToday = Attendance::query()->where('user_id', $user->id)->whereDate('date', $todayStr)->first();
                $failures = (int) Cache::get("attendance:face_failures:{$user->id}", 0);

                $faceStatus = $hasFace
                    ? 'Sudah Terdaftar (Aktif)'
                    : '⚠️ BELUM TERDAFTAR (Anda wajib melakukan Pendaftaran Wajah 3 Pose terlebih dahulu di halaman Absen)';
                $attStatus = 'Belum Check-In hari ini';
                if ($attToday && ($attToday->clock_in_at || $attToday->clock_in)) {
                    $inTime = Carbon::parse($attToday->clock_in_at ?? $attToday->clock_in)->timezone('Asia/Jakarta')->format('H:i');
                    $outTime = ($attToday->clock_out_at || $attToday->clock_out)
                        ? Carbon::parse($attToday->clock_out_at ?? $attToday->clock_out)->timezone('Asia/Jakarta')->format('H:i')
                        : null;
                    $attStatus = $outTime
                        ? "⚠️ Sudah Check-In ({$inTime} WIB) & Sudah Check-Out ({$outTime} WIB) — presensi hari ini sudah selesai"
                        : "Sudah Check-In pukul {$inTime} WIB (silakan gunakan tombol Check-Out saat jam pulang)";
                }

                $liveDiag = "\n\n🔍 Diagnostik Live Akun Anda Saat Ini:\n"
                    . "• Status Biometrik Wajah: {$faceStatus}\n"
                    . "• Status Presensi Hari Ini: {$attStatus}"
                    . ($failures > 0 ? "\n• Gagal Verifikasi Wajah Berturut-turut: {$failures}x (Jalur Manual terbuka setelah 3x gagal)" : '');
            }

            $resp = ($isSunda
                ? "Penyebab utama teu tiasa absen (Check-In / Check-Out) di sistem GOMSN katut solusina:\n"
                    . "1. Acan Daftar Biometrik Wajah (3 Pose): Karyawan wajib ngadaftarkeun pameunteu 3 pose (Hareup, Kenca, Katuhu) jeung nyentang persetujuan UU PDP.\n"
                    . "2. Di Luar Radius GPS Kantor (Geofence): Kanggo tipe Kantor, posisi GPS kedah di jero radius kantor & teu meunang nganggo Fake GPS. Upami nuju tugas di lapangan, pilih tipe Dinas Luar / Remote sarta WAJIB ngeusian Catatan Keterangan.\n"
                    . "3. Idin Kamera & Lokasi Browser Diblokir: Pastikeun idin Camera & Location dina browser HP/Laptop disetel ka Allow (Izinkan).\n"
                    . "4. Geus Check-In / Token Liveness Kadaluwarsa (>2 Menit): Check-In ngan tiasa 1x sapoe, sarta tantangan liveness (seuri/kedip/nengok) kedah rengse dina 2 menit.\n"
                    . "5. Jalur Manual / Reset Wajah: Upami gagal verifikasi wajah 3x berturut-turut, sistem otomatis muka Jalur Manual (Pending Review HRD), atawa hubungi HRD kanggo Reset Wajah."
                : "Berikut 5 penyebab utama tidak bisa absen (Check-In / Check-Out) di aplikasi GOMSN beserta solusinya:\n"
                    . "1. Belum Daftar Biometrik Wajah (3 Pose): Sebelum absen pertama kali, Anda wajib melakukan Pendaftaran Wajah 3 Pose (Depan, Kiri, Kanan) dan mencentang persetujuan UU PDP.\n"
                    . "2. Lokasi di Luar Radius GPS Kantor (Geofence): Untuk tipe kehadiran Kantor (Office), GPS harus berada di dalam radius kantor dan tidak boleh menggunakan Fake GPS / Mock Location. Jika sedang bertugas di luar, ubah tipe kehadiran ke Dinas Luar (Field) / Remote dan WAJIB mengisi kolom Catatan Keterangan.\n"
                    . "3. Izin Kamera & Lokasi Browser Diblokir: Klik ikon gembok di address bar browser (Chrome/Safari), pastikan izin Camera & Location berstatus Allow (Izinkan), lalu refresh halaman.\n"
                    . "4. Sudah Check-In Hari Ini / Token Kedaluwarsa (>2 Menit): Check-In hanya bisa dilakukan 1x sehari, dan tantangan liveness (senyum/kedip/menoleh) harus diselesaikan dalam waktu maksimal 2 menit.\n"
                    . "5. Gagal Wajah 3x (Jalur Manual) / Ganti HP: Jika gagal verifikasi wajah 3x berturut-turut, tombol Jalur Manual (Pending Review HRD) otomatis terbuka. Jika ganti perangkat baru, minta HRD melakukan Reset Biometrik Wajah.")
                . $liveDiag;

            return [
                'response' => $resp,
                'confidence' => 96.0,
                'matched_intent' => 'smart_diag_tidak_bisa_absen',
                'action_url' => $this->safeRoute('attendance.index'),
                'action_label' => '📍 Buka Halaman Absen',
                'suggestions' => null,
                'domain' => 'absensi',
            ];
        }

        // =====================================================================
        // 5. KENAPA LEMBUR TIDAK SAMPAI KE FINANCE / HRD / BELUM DIBAYAR
        // =====================================================================
        $mentionsLembur = $has('lembur') || isset($domains['lembur']) || $effDomain === 'lembur';
        if ($mentionsLembur && ($isNotReachingOrStuck || ($isWhyQuestion && preg_match('/\b(finance|hrd|keuangan|dibayar|cair|masuk|muncul|sampai)\b/iu', $text)))) {
            $liveLembur = '';
            if ($user) {
                $latestLbr = Lembur::with('atasan')->where('user_id', $user->id)->latest('tanggal')->first();
                if ($latestLbr) {
                    $st = strtolower((string) $latestLbr->status);
                    $tgl = $latestLbr->tanggal ? Carbon::parse($latestLbr->tanggal)->translatedFormat('d M Y') : '-';
                    $atasanName = $latestLbr->atasan?->name ?? 'Atasan Divisi';
                    $lbrReason = match ($st) {
                        'pending' => "Masih berstatus PENDING (Menunggu ACC Atasan: {$atasanName}). Di sistem GOMSN, halaman Lembur Finance & HRD hanya menampilkan lembur yang SUDAH berstatus APPROVED_ATASAN.",
                        'approved_atasan', 'approved' => "Sudah Disetujui Atasan (APPROVED_ATASAN) dan SUDAH masuk di daftar monitoring HRD serta antrean pembayaran Finance.",
                        'paid' => "Sudah DIBAYAR / LUNAS (PAID) oleh Finance.",
                        'rejected' => "Berstatus DITOLAK (REJECTED) oleh Atasan, sehingga tidak diteruskan ke Finance.",
                        default => "Berstatus: " . strtoupper($st),
                    };
                    $liveLembur = "\n\n🔍 Cek Live Lembur Terakhir Anda ({$latestLbr->nomor_lembur} — {$tgl}):\n• {$lbrReason}";
                }
            }

            $resp = "Penyebab pengajuan lembur tidak sampai atau belum muncul di Finance / HRD pada sistem GOMSN:\n"
                . "1. Belum Di-ACC Atasan Divisi (Masih PENDING): Sesuai alur sistem GOMSN, halaman Lembur Finance dan HRD hanya menampilkan pengajuan lembur yang sudah berstatus APPROVED_ATASAN (Disetujui Atasan) atau PAID.\n"
                . "2. Ditolak oleh Atasan (REJECTED): Lembur yang ditolak Atasan otomatis berhenti dan tidak diteruskan ke Finance.\n"
                . "3. Filter Periode Bulan/Tahun: Jika lembur berada di bulan sebelumnya dan belum diproses, ubah filter bulan di halaman Lembur ke periode tersebut atau pilih filter 'Belum Diproses'."
                . $liveLembur;

            return [
                'response' => $resp,
                'confidence' => 95.0,
                'matched_intent' => 'smart_diag_lembur_tidak_sampai_finance',
                'action_url' => $this->safeRoute('gomsn.overtime.index'),
                'action_label' => '💼 Buka Halaman Lembur',
                'suggestions' => null,
                'domain' => 'lembur',
            ];
        }

        // =====================================================================
        // 6. KENAPA TIDAK BISA AJUKAN / INPUT LEMBUR
        // =====================================================================
        if ($mentionsLembur && $isCannotOrBlocked && ! preg_match('/\b(ditolak atasan|alasan penolakan|kenapa pengajuan lembur saya ditolak)\b/iu', $text)) {
            $resp = "Penyebab Anda tidak bisa mengajukan atau menyimpan data lembur di GOMSN:\n"
                . "1. Belum Ada Data Absensi pada Tanggal Lembur: Sistem mewajibkan Anda sudah tercatat Check-In (Hadir/Terlambat) pada tanggal lembur tersebut.\n"
                . "2. Durasi Melebihi 4 Jam (240 Menit) atau Kurang dari 1 Jam: Batas klaim lembur di sistem adalah minimal 60 menit dan maksimal 4 jam (240 menit) per hari.\n"
                . "3. Melewati Batas Retroaktif 3 Hari (H-3): Tanggal lembur yang sudah lewat lebih dari 3 hari ke belakang otomatis dikunci oleh sistem.\n"
                . "4. Jam Lembur Bertabrakan (Overlap): Rentang jam mulai & selesai tidak boleh bentrok dengan pengajuan lembur Anda yang lain di tanggal yang sama.\n"
                . "5. Kolom Alasan Kosong: Rincian/alasan pekerjaan lembur wajib diisi.";

            return [
                'response' => $resp,
                'confidence' => 95.0,
                'matched_intent' => 'smart_diag_tidak_bisa_ajukan_lembur',
                'action_url' => $this->safeRoute('gomsn.overtime.create'),
                'action_label' => '⏰ Ajukan Lembur',
                'suggestions' => null,
                'domain' => 'lembur',
            ];
        }

        // =====================================================================
        // 7. KENAPA TIDAK BISA AJUKAN CUTI / IZIN ATAU BELUM SAMPAI HRD
        // =====================================================================
        $mentionsCuti = $has('cuti') || $has('izin') || ($has('sakit') && $isCannotOrBlocked) || isset($domains['cuti']) || $effDomain === 'cuti';
        if ($mentionsCuti && ($isCannotOrBlocked || $isNotReachingOrStuck) && ! $has('tukar_shift') && ! $has('batal')) {
            $liveCuti = '';
            if ($user) {
                $bal = LeaveBalance::query()->where('user_id', $user->id)->where('year', (int) date('Y'))->first();
                $sisa = $bal ? ($bal->remaining_days ?? 12) : 12;
                $liveCuti = "\n\n🔍 Cek Live Saldo Cuti Tahunan Anda (" . date('Y') . "): Tersisa {$sisa} hari kerja.";
            }

            $resp = "Penyebab tidak bisa mengajukan Cuti/Izin atau pengajuan belum sampai ke HRD di GOMSN:\n"
                . "1. Sisa Saldo Cuti Tahunan Habis (0 Hari): Jika kuota cuti tahunan (12 hari/tahun) sudah habis, pengajuan cuti tahunan baru akan ditolak sistem.\n"
                . "2. Kurang dari Batas Minimal H-3: Cuti tahunan reguler wajib diajukan minimal 3 hari sebelum tanggal mulai cuti.\n"
                . "3. Belum Upload Surat Keterangan Dokter (SKD): Untuk kategori Izin Sakit, sistem mewajibkan lampiran foto/PDF Surat Dokter.\n"
                . "4. Tanggal Bertabrakan / Masih Menunggu ACC Atasan: Pengajuan cuti/izin harus disetujui Atasan Langsung terlebih dahulu (status PENDING_SUPERVISOR) sebelum diteruskan ke antrean verifikasi HRD (PENDING_HRD)."
                . $liveCuti;

            return [
                'response' => $resp,
                'confidence' => 94.0,
                'matched_intent' => 'smart_diag_tidak_bisa_cuti',
                'action_url' => $this->safeRoute('attendance.leaves'),
                'action_label' => '🌴 Buka Cuti & Izin Saya',
                'suggestions' => null,
                'domain' => 'cuti',
            ];
        }

        // =====================================================================
        // 8. KENAPA TIDAK BISA TUKAR SHIFT
        // =====================================================================
        if ($has('tukar_shift') && ($isCannotOrBlocked || $isNotReachingOrStuck)) {
            return [
                'response' => "Penyebab pengajuan Tukar Shift gagal atau tidak bisa diproses di GOMSN:\n"
                    . "1. Beda Divisi: Tukar shift hanya diperbolehkan dengan rekan kerja dalam satu divisi yang sama.\n"
                    . "2. Jeda Istirahat Kurang dari 11 Jam: Sistem menolak pertukaran shift yang mengakibatkan waktu istirahat antar shift kurang dari 11 jam (aturan K3).\n"
                    . "3. Belum Disetujui Rekan Pengganti / Atasan: Rekan yang diajak bertukar harus menyetujui permintaan tersebut terlebih dahulu, kemudian wajib di-ACC oleh Atasan/Supervisor.",
                'confidence' => 94.0,
                'matched_intent' => 'smart_diag_tidak_bisa_tukar_shift',
                'action_url' => $this->safeRoute('attendance.schedules'),
                'action_label' => '📅 Buka Jadwal Kerja Saya',
                'suggestions' => null,
                'domain' => 'jadwal',
            ];
        }

        // =====================================================================
        // 9. KENAPA TIDAK BISA CLOSE / TUTUP TIKET GANGGUAN
        // =====================================================================
        if (($has('closing_tiket') || ($has('tiket_gangguan') && preg_match('/\b((?:di)?close|closing|(?:di)?tutup|selesai)\b/iu', $text))) && ($isCannotOrBlocked || $isNotReachingOrStuck)) {
            return [
                'response' => "Penyebab Tiket Gangguan tidak bisa di-Close (ditutup) di GOMSN:\n"
                    . "1. Data Redaman Optik Belum Diisi: Teknisi wajib menginput nilai redaman optik (dBm) sebelum dan sesudah perbaikan.\n"
                    . "2. Kronologi & Resume Pekerjaan Belum Lengkap: Bagian temuan masalah (Problem) dan tindakan perbaikan (Action Taken) wajib diisi.\n"
                    . "3. Foto Bukti Lapangan Belum Diunggah: Wajib melampirkan foto hasil ukur OPM/OTDR dan foto titik perbaikan.\n"
                    . "4. Menunggu Verifikasi NOC (PENDING_VERIFIKASI): Setelah teknisi mengirim laporan selesai, penutupan akhir (status CLOSE) diverifikasi oleh tim NOC.",
                'confidence' => 94.0,
                'matched_intent' => 'smart_diag_tidak_bisa_close_tiket',
                'action_url' => $this->safeRoute('tickets.index'),
                'action_label' => '🎫 Buka Tiket Gangguan',
                'suggestions' => null,
                'domain' => 'tiket',
            ];
        }

        return null;
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
            preg_match('/\b(siapa atasan saya|atasan saya siapa|atasan langsung saya|supervisor saya|bos saya siapa|siapa bos saya|manager saya siapa|siapa manager saya|saha atasan urang)\b/iu', $normMessage . ' ' . $rawClean)
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
            preg_match('/\b(profil saya|biodata saya|data diri saya|data saya|info akun saya|id karyawan saya|kode karyawan saya|jabatan saya|divisi saya|role saya|siapa saya|akun saya)\b/iu', $normMessage . ' ' . $rawClean)
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
    public function resolveAppGuideOrBroadTopic(?User $user, string $normMessage, array $userTokens, array $domains, array $style, bool $isSunda, bool $forceFallback = false, bool $hasSemanticIntent = false): ?array
    {
        // A. Full App Overview / "Cara kerja aplikasi ini" / "Fitur apa saja" / "Kamu bisa apa"
        $isAppOverview = (bool) preg_match(
            '/\b(apa itu gomsn|tentang gomsn|tentang aplikasi|cara kerja aplikasi|cara kerja sistem|cara kerja gomsn|aplikasi ini buat apa|aplikasi ini untuk apa|fitur apa saja|fitur gomsn|menu apa saja|modul apa saja|layanan apa saja|panduan aplikasi|kamu bisa apa|bisa bantu apa|bisa apa aja|bisa nanya apa|cara pakai aplikasi|jelaskan aplikasi)\b/iu',
            $normMessage
        ) || (isset($domains['aplikasi']) && count($userTokens) <= 3 && count($domains) === 1);

        if ($isAppOverview) {
            return $this->buildAppOverviewResponse($user, $isSunda);
        }

        // If a specific semantic intent or bilingual policy already matched, never hijack it with a broad hub!
        if (! $forceFallback && $hasSemanticIntent) {
            return null;
        }

        // Do not intercept explicit personal/dashboard live-data queries in broad hub (unless forced fallback)
        if (! $forceFallback && preg_match('/\b(siapa atasan|atasan saya|bos saya|manager saya|supervisor saya|profil saya|biodata saya|data diri saya|data saya|info akun saya|id karyawan saya|kode karyawan saya|ringkasan dashboard|statistik dashboard|ringkasan bulan ini|statistik bulan ini|statistik perusahaan|statistik divisi|hari libur|libur nasional|tanggal merah)\b/iu', $normMessage)) {
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
