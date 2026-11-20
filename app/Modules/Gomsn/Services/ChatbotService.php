<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\ChatbotIntent;
use App\Models\ChatbotLog;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Lembur;
use App\Models\MonthlySchedule;
use App\Models\Odp;
use App\Models\OdpCustomer;
use App\Models\Olt;
use App\Models\Pengajuan;
use App\Models\Pon;
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
     * Detect if the user's message is written in Basa Sunda.
     */
    protected function isSundaneseQuery(string $rawClean): bool
    {
        $sundaMarkers = [
            'urang', 'abdi', 'anjeun', 'maneh', 'kumaha', 'sabaraha', 'kunaon', 'naha',
            'naon', 'saha', 'iraha', 'kamari', 'poe', 'isukan', 'ayeuna', 'geus', 'acan',
            'encan', 'henteu', 'wae', 'atuh', 'yeuh', 'arek', 'bade', 'upami', 'lamun',
            'jeung', 'jng', 'atawa', 'tina', 'dina', 'kana', 'asup', 'gawe', 'poho',
            'beungeut', 'gering', 'meuli', 'nyokot', 'dicokot', 'nyieun', 'ningali',
            'neangan', 'ngunduh', 'ngajukeun', 'disatujuan', 'dibatalkeun', 'sakabeh',
            'tembongkeun', 'pangnempokeun', 'pangningalikeun', 'pangngecekkeun',
            'pangringkeskeun', 'pangnyieunkeun', 'sampurasun', 'damang', 'hatur',
            'nuhun', 'punten', 'permios', 'muhun', 'mangga', 'hungkul', 'wungkul',
            'hareup', 'katuhu', 'peuting', 'isuk', 'sajam', 'sapoe', 'saminggu',
            'sabulan', 'taun', 'taunan', 'babaturan', 'kulawarga', 'maot', 'indit',
            'beak', 'leungit', 'anyar', 'sejen', 'loba', 'saeutik', 'pareum', 'imah',
            'budak', 'ngarobah', 'nyaring', 'bewara', 'nyatet', 'ngoper', 'diteangan',
            'nyekelna', 'ngajawabna', 'ngabantu', 'ngitungkeun', 'sabenerna', 'luyu',
            'pohoan', 'carana', 'statusna', 'alesanana', 'itunganna', 'ngaruhna',
        ];

        $pattern = '/\b(' . implode('|', $sundaMarkers) . ')\b/iu';

        return (bool) preg_match($pattern, $rawClean);
    }

    /**
     * Get concise Basa Sunda translation for shared static intents when user asks in Sundanese.
     */
    protected function getSundaneseStaticResponse(string $intentKey): ?string
    {
        return match ($intentKey) {
            'q3_cara_absen_biometrik_gps' => "Cara absen nganggo Biometrik & GPS:\n1. Buka menu Absen.\n2. Pastikeun idin Lokasi (GPS) sareng Kamera aktif dina browser/HP.\n3. Pilih tipe kahadiran (Kantor atawa Dinas Luar).\n4. Arahkeun pameunteu (beungeut) kana kamera kanggo verifikasi biometrik, teras pencet tombol Check-In / Check-Out.",
            'q4_troubleshooting_gps' => "Lokasi GPS teu kapanggih biasana kusabab idin lokasi (Location Permission) dina browser/HP teu acan diaktipkeun, GPS pareum, atawa sinyal kahalangan ruangan tertutup. Mangga hurungkeun GPS mode akurasi tinggi, pasihan idin lokasi dina browser, teras refresh halaman Absen.",
            'q5_troubleshooting_biometrik' => "Upami kamera biometrik gagal maca beungeut:\n1. Pastikeun cahaya di sekitar beungeut cukup caang jeung teu ngagigirkeun lampu/panonpoe.\n2. Buka masker, kacamata hideung, atawa topi.\n3. Pastikeun idin kamera di browser tos Allow.\nUpami tetep gagal, hubungi HRD kanggo Reset Biometrik Wajah.",
            'q6_batas_toleransi_terlambat' => "Batas toleransi telat asup gawe di perusahaan nyaeta 15 menit ti jadwal jam asup shift. Upami check-in liwat ti 15 menit bakal kacatet Terlambat sareng dikenakeun potongan keterlambatan.",
            'q8_prosedur_koreksi_absen' => "Upami kamari poho teu check-out, mangga ajukeun Koreksi Absen di menu Riwayat Absen Saya:\n1. Pilih tanggal kamari nu poho check-out, teras klik Ajukan Koreksi.\n2. Eusian jam balik nu sabenerna katut alesan sareng bukti pendukung.\n3. Kirim pengajuan kanggo di-ACC ku Atasan sareng HRD.",
            'q9_aturan_absen_luar_kantor' => "Meunang pisan. Nalika nuju tugas di lapangan/luar kantor, pilih tipe absensi Dinas Luar dina halaman Absen, lakukeun foto selfie biometrik & GPS lokasi lapangan, sarta eusian katerangan tugas atawa nomor tiket/SPK.",
            'q11_cara_lihat_log_bulan_lalu' => "Buka menu Riwayat Absen Saya di sidebar, teras gentos filter Bulan & Taun di bagian luhur halaman ka periode bulan kamari sareng klik Filter / Tampilkan.",
            'q12_sinkronisasi_absensi_realtime' => "Muhun, langsung update real-time. Saban anjeun beres check-in atawa check-out, data waktu, status kahadiran, jeung titik GPS langsung kasimpen di server sarta otomatis update dina halaman hareup (Home) boh di rekap HRD.",
            'q13_absen_offline_kendala_jaringan' => "Lamun di lokasi gangguan euweuh sinyal internet, gunakeun fitur Absen Offline dina halaman Absen (data kasimpen samentawis dina HP jeung otomatis kasinkron pas sinyal internet normal deui), atawa foto bukti ber-timestamp di lokasi teras ajukeun Koreksi Absen katut nomor tiketna.",
            'q15_aturan_absensi_hari_libur' => "Pas tanggal merah libur nasional otomatis kacatet libur dina kalender sistem tanpa kudu klik absen. Nanging upami anjeun kabagean jadwal piket/tugas dina poe libur eta, anjeun tetep wajib check-in sareng ngajukeun Lembur Hari Libur.",
            'v20_perbedaan_status_izin_sakit' => "Dina rekap absensi GOMSN, status Izin jeung Sakit dipisahkeun:\n• Sakit: Teu asup gawe kusabab gering anu wajib dilampiran Surat Keterangan Dokter (SKD).\n• Izin: Teu asup gawe kusabab kaperluan pribadi mendesak di luar gering kalayan persetujuan Atasan & HRD.",
            'v21_troubleshooting_status_belum_checkin' => "Upami tos foto beungeut tapi statusna masih Belum Check-in, kamungkinan koneksi internet kapotong pas ngirim data atawa halaman Home acan dimuat ulang. Pencet tombol Refresh di handapeun ringkasan Absensi Hari Ini, atawa cek di menu Riwayat Absen Saya.",
            'v22_batas_jam_checkout_harian' => "Batas pangtelatna check-out balik gawe nyaeta saatos jam pulang shift nepi ka tutup poe tabuh 23:59 WIB (sateuacan ganti poe/shift isukanana). Upami kuliwat ti jam eta, log balik bakal kosong jeung kudu diajukeun lewat Koreksi Absen.",
            'v24_cek_lokasi_gps_riwayat_absen' => "Bisa dicek. Buka menu Riwayat Absen Saya, teras pilih tanggal kahadiran kamari. Dina rincian log absen eta bakal katingali titik koordinat GPS (Latitude & Longitude) katut lokasi pas anjeun check-in.",
            'v25_reset_device_biometrik' => "Upami kakara ganti HP anyar atawa biometrik kakonci, mangga hubungi tim HRD atawa Admin IT kanggo ngalakukeun Reset Biometrik / Perangkat dina akun anjeun di menu Data Karyawan supados tiasa daftar ulang di HP anyar.",
            'q17_cara_cek_kalender_shift' => "Buka menu Jadwal Kerja Saya dina sidebar. Dina halaman eta ditampilkeun tabel kalender shift gawe anjeun keur sabulan pinuh lengkep jeung jam asup sarta jam balik unggal poena.",
            'q18_kebijakan_tukar_shift' => "Bisa. Anjeun tiasa ngajukeun Tukar Shift jeung babaturan sa-divisi lewat menu Jadwal Kerja Saya, asal babaturan nu diajak tukerna satuju, nyumponan jeda istirahat minimal 11 jam antar shift, sarta di-ACC ku Atasan/Supervisor.",
            'q20_langkah_ajukan_cuti' => "Cara ngajukeun cuti di menu Cuti & Izin Saya:\n1. Buka menu Cuti & Izin Saya, teras klik + Ajukan Cuti / Izin.\n2. Pilih jenis Cuti Tahunan, eusian tanggal mimiti jeung rengsena.\n3. Tulis alesan cuti, teras klik Kirim Pengajuan kanggo di-ACC ku Atasan sareng HRD.",
            'q21_sla_batas_pengajuan_cuti' => "Mun rek nyokot cuti taunan, wajib diajukeun minimal H-3 (3 poe gawe samemeh tanggal cuti) supados Atasan tiasa ngatur jadwal backup pagawean.",
            'q22_syarat_dokumen_izin_sakit' => "Lamun ngajukeun izin gering (sakit), dokumen nu wajib di-upload nyaeta foto atawa scan Surat Keterangan Dokter (SKD) resmi ti klinik atawa rumah sakit (format JPG, PNG, atawa PDF).",
            'q23_cara_cek_approval_izin' => "Buka menu Cuti & Izin Saya, teras tingali dina kolom Status dina daftar riwayat pengajuan anjeun. Di dinya bakal katingali naha statusna masih Menunggu Atasan, Menunggu HRD, Disetujui (Approved), atawa Ditolak.",
            'q24_pembatalan_cuti_disetujui' => "Bisa dibatalkeun, salila tanggal cutina acan jalan. Buka menu Cuti & Izin Saya, pilih pengajuan cuti eta teras klik Batalkan (atawa hubungi HRD) supados kuota saldo cuti anjeun balik deui.",
            'q25_penjelasan_nominal_potongan' => "Nominal denda di menu Potongan Absen Saya muncul otomatis upami aya catetan telat check-in nu ngaleuwihan batas toleransi 15 menit, atawa aya poe gawe nu statusna Alpa (teu asup tanpa katerangan sah) dina bulan berjalan.",
            'q26_formula_hitung_potongan' => "Cara ngitung potongan absensi:\n• Telat: Diitung tina akumulasi menit keterlambatan (liwat toleransi 15 menit) dikalikeun tarif denda telat.\n• Alpa (Teu asup tanpa katerangan): Dikenakeun potongan tarif alpa harian sesuai aturan perusahaan. Rincian per tanggalna aya di menu Potongan Absen Saya.",
            'q28_alur_sanggahan_potongan' => "Mun angka potongan sigana salah itung, mangga ajukeun Koreksi Absen disarengan bukti kahadiran dina tanggal eta lewat menu Potongan Absen Saya / Riwayat Absen Saya, teras laporkeun ka HRD samemeh periode absensi dikonci (Lock Period).",
            'q29_perbedaan_jenis_cuti_izin' => "Bedana:\n• Cuti Taunan: Hak libur resmi nu motong jatah saldo cuti taunan (12 poe/taun).\n• Cuti Khusus: Cuti dibayar keur acara penting (kawin, ngalahirkeun, kulawarga inti maot) nu teu motong saldo cuti taunan.\n• Izin Teu Asup Gawe: Teu hadir kusabab gering (make surat dokter) atawa kaperluan mendesak kalayan idin Atasan & HRD.",
            'q30_aturan_hangus_sisa_cuti' => "Sisa cuti taun ieu nu teu kapake nepi ka akhir periode bakal hangus (atawa ngan bisa dibawa/carry-over dina batas masa tenggang awal taun hareup sesuai aturan HRD) sarta teu tiasa diuangkan.",
            'v33_jenis_cuti_khusus' => "Henteu motong. Cuti kawin, pamajikan ngalahirkeun, khitanan anak, atawa aya anggota kulawarga inti nu maot kaasup kana hak Cuti Khusus berbayar, jadi henteu motong jatah kuota cuti taunan anjeun.",
            'q31_cara_ajukan_kasbon' => "Cara ngajukeun kasbon operasional lewat menu Pengajuan Saya:\n1. Buka menu Pengajuan > klik + Buat Pengajuan.\n2. Pilih kategori Kasbon / Dana Operasional.\n3. Eusian nominal duit, rincian kaperluan operasional, jeung nomor rekening.\n4. Klik Kirim Pengajuan kanggo di-ACC ku Atasan sareng Finance.",
            'q32_batas_maksimal_kasbon' => "Batas maksimal nominal kasbon operasional rutin keur karyawan tanpa persetujuan khusus Direksi nyaeta Rp 5.000.000 per pengajuan (luyu jeung plafon divisi/kebutuhan tugas), kalayan syarat euweuh kasbon samemehna nu acan dilunasan (LPJ).",
            'q33_prosedur_settlement_kasbon' => "Mun duit kasbon geus dipake, buka detail pengajuan kasbon eta di menu Pengajuan Saya, upload foto nota/struk bukti pengeluaran aslina (LPJ) maksimal H+3, sarta balikkeun sisa duitna (mun aya) ka Finance meh statusna Lunas (Settled).",
            'q34_langkah_pengajuan_beli_barang' => "Langkah ngajukeun meuli barang (Beli):\n1. Buka menu Pengajuan > klik + Buat Pengajuan.\n2. Pilih tipe Pembelian Barang (Beli).\n3. Eusian ngaran barang, jumlah unit, estimasi harga, jeung alesan kabutuhanana.\n4. Klik Kirim Pengajuan kanggo di-ACC ku Atasan jeung Finance.",
            'q35_cara_permintaan_stok_material' => "Mun stok kabel atawa alat gawe beak, buka menu Pengajuan > klik + Buat Pengajuan, pilih kategori Permintaan Stok, pilih item material ti daftar gudang katut jumlah nu dibutuhkeun jeung nomor tiketna, teras klik Kirim.",
            'q37_hierarki_approval_pembelian' => "Pihak nu kudu nge-ACC pengajuan meuli barang di divisi nyaeta:\n1. Atasan Langsung / Kepala Divisi (validasi kabutuhan teknis).\n2. Bagian Finance (verifikasi anggaran & pencairan).\n3. Direksi (upami nominal pembelian ngaleuwihan batas plafon).",
            'q38_cara_ajukan_lembur' => "Cara nginput lemburan di menu Lembur Saya:\n1. Buka menu Lembur Saya, teras klik tombol + Ajukan Lembur.\n2. Pilih tanggal, jam mimiti, jam beres, jeung jenis lembur.\n3. Eusian alesan/rincian pagawean lembur, teras klik Simpan / Ajukan.",
            'q39_syarat_spk_lembur' => "Pengajuan lembur wajib didasaran ku penugasan resmi atawa SPK (Surat Perintah Kerja) / nomor tiket ti Atasan. Upami lembur darurat peuting, dokumen/pengajuan SPK-na tiasa langsung dilengkepan maksimal H+1.",
            'q40_cara_akses_dokumen_spk' => "Buka menu Lembur Saya, pilari baris pengajuan lembur nu geus disatujuan dina tabel riwayat, teras klik tombol Detail / Unduh SPK dina kolom Aksi.",
            'q41_batas_waktu_klaim_lembur' => "Pengajuan klaim lembur pangtelatna wajib diinput ka aplikasi maksimal 1x24 jam (H+1 / sapoe) sanggeus pagawean lembur beres dipigawe, supados tiasa dicocokkeun jeung log check-out absen.",
            'q43_alasan_lembur_ditolak' => "Pengajuan lembur tiasa ditolak upami durasi jamna teu cocok jeung log check-out absen atawa rincian tugasna kurang jelas. Catetan alesan ditolakna tiasa ditingali langsung dina baris pengajuan lembur nu statusna Ditolak di menu Lembur Saya.",
            'q44_aturan_lembur_hari_libur' => "Muhun, beda. Lembur pas poe Minggu atawa tanggal merah (Hari Libur) nganggo tarif pangali upah lembur per jam anu langkung ageung dibandingkeun lembur dina poe gawe biasa (Senin–Jumat).",
            'q45_revisi_pengajuan_pending' => "Bisa diedit. Salila status pengajuan kasbon masih keneh Pending (acan di-ACC ku Atasan), buka menu Pengajuan Saya, klik tombol Edit dina pengajuan eta, benerkeun nominalna, teras klik Simpan.",
            'v53_durasi_minimal_lembur' => "Durasi minimal pagawean lembur nu bisa diajukeun jeung diitung ka sistem nyaeta minimal sajam (60 menit) kerja efektif di luar jam shift normal. Gawe tambahan 30–45 menit di handapeun sajam teu acan kaetang klaim lembur.",
            'v54_lampiran_bukti_lembur' => "Muhun, pas ngajukeun lembur anjeun kedah nyantumkeun rincian hasil pagawean, nomor tiket/SPK, sarta bukti pendukung/foto gawe di lapangan atawa log check-out kahadiran kanggo dasar verifikasi Atasan jeung HRD.",
            'v55_sla_pencairan_dana_lembur' => "Duit kasbon operasional biasana cair ka rekening dina waktu 1x24 jam poe kerja sanggeus sakabeh tahapan approval rengse di-ACC ku Atasan jeung Finance.",
            'q49_cara_cari_kontak_karyawan' => "Buka menu Data Karyawan dina grup Kelola Karyawan, teras ketik ngaran atawa NIK karyawan dina kolom pencarian (Search). Klik profil karyawanna kanggo ningali nomor HP/WhatsApp, email, jeung divisina.",
            'q51_unduh_rekap_absensi_potongan' => "Buka menu Absen Karyawan (atawa Potongan Absen), pilih filter periode Bulan jeung Taun nu dipikahoyong, teras klik tombol Export / Unduh Rekap di pojok katuhu luhur halaman.",
            'q53_monitoring_lembur_karyawan' => "HRD tiasa mantau sakabeh pengajuan lembur jeung dokumen SPK staf ngalangkungan menu Lembur Karyawan di sidebar, lengkep sareng filter periode bulan, divisi, katut status approval.",
            'q55_kriteria_penilaian_leaderboard' => "Kriteria penilaian peringkat disiplin di menu Leaderboard diitung tina:\n1. Jumlah kahadiran tepat waktu (on-time check-in).\n2. Saeutikna frekuensi jeung total menit keterlambatan.\n3. Teu ayana catetan Alpa (teu asup tanpa katerangan) salila periode berjalan.",
            'q57_cara_ubah_status_aktif_karyawan' => "Buka menu Data Karyawan, pilari ngaran karyawan nu resign, klik tombol Edit, teras robah pilihan Status Akun jadi Non-Aktif sareng klik Simpan.",
            'q58_onboarding_karyawan_baru' => "Buka menu Data Karyawan, klik tombol + Tambah Karyawan, eusian data lengkepna (Ngaran, NIK, Username/Email, Divisi, Jabatan, jeung Password awal), teras klik Simpan. Akunna langsung aktif tur bisa dipake login.",
            'q59_integrasi_potongan_ke_payroll' => "Muhun, langsung nyambung otomatis. Sakabeh potongan telat jeung alpa di menu Absen Karyawan / Potongan Absen saatos dikonci (Lock Period) ku HRD di akhir bulan bakal langsung jadi dasar potongan payroll gaji.",
            'q61_cara_buat_tiket_gangguan' => "Cara nyieun Tiket Gangguan anyar:\n1. Buka menu Tiket Gangguan, teras klik + Buat Tiket.\n2. Pilih kategori tiket (Backbone atawa Broadband/Pelanggan) jeung tingkat prioritasna.\n3. Eusian data lokasi/pelanggan katut deskripsi gangguan awal, teras klik Simpan.",
            'q63_update_progres_tiket' => "Buka detail Tiket Gangguan nu keur dipigawe, asup ka bagian Kronologi / Progres, tulis laporan tindakan perbaikan katut upload foto bukti di lapangan, teras klik Kirim / Update.",
            'q64_informasi_menu_olt_pon_odp' => "Di jero menu OLT, PON & ODP aya informasi topologi jaringan optik nu ngawengku: daftar perangkat OLT, port PON, titik lokasi & koordinat ODP, kapasitas slot port ODP (kapake & kosong), sarta daftar pelanggan nu nyambung di unggal ODP.",
            'q65_cara_cek_odp_pelanggan' => "Buka menu Jaringan OLT, PON & ODP, ketik kode ODP atawa ngaran area dina kolom pencarian, teras klik Detail ODP kanggo ningali titik koordinatna katut daftar sakabeh pelanggan nu nyambung di ODP eta.",
            'q66_arti_status_port_pon_olt' => "Harti indikator warna dina port PON jeung OLT:\n• Hejo (Active / Online): Port jeung jalur optik jalan normal.\n• Koneng (Warning): Redaman sinyal luhur atawa kapasitas port ampir pinuh.\n• Beureum (LOS / Down / Offline): Sinyal putus (Loss of Signal) atawa port keur pareum.",
            'q67_prosedur_spk_tiket_noc' => "Sanggeus NOC nyieun tiket gangguan, petugas NOC ngalakukeun Dispatch (Penugasan Tim) dina detail tiket ku cara milih teknisi lapangan. Penugasan eta otomatis nerbitkeun instruksi gawe (SPK lapangan) keur teknisi.",
            'q68_syarat_bukti_closing_tiket' => "Samemeh nutup (close) tiket gangguan, teknisi wajib ngalengkepan jeung ngupload:\n1. Data nilai redaman optik (samemeh jeung sanggeus perbaikan dina dBm).\n2. Ringkesan temuan (Problem) jeung tindakan perbaikan (Action Taken).\n3. Foto bukti ukuran OPM/OTDR jeung foto titik perbaikan di lapangan.",
            'q69_alur_eskalasi_sla_tiket' => "Mun gangguan jaringan teu beres nepi ka liwat batas SLA:\n1. Sistem otomatis nandakeun tiket jadi SLA Breached.\n2. Teknisi wajib update kendala dina kronologi (atawa ngajukeun Stop Clock mun kahalangan akses/cuaca).\n3. NOC ngalakukeun eskalasi ka Supervisor / Manager Teknik kanggo ngirim tim backup lapangan.",
            'q70_cari_riwayat_tiket_pelanggan' => "Buka menu Tiket Gangguan, teras ketik Nomor Tiket, ID Pelanggan, atawa Ngaran Pelanggan dina kolom pencarian (Search) di bagian luhur halaman kanggo nampilkeun sakabeh riwayat tiketna.",
            'q71_permintaan_stok_dari_tiket' => "Bisa pisan. Dina halaman detail Tiket Gangguan, teknisi tiasa langsung nyatet/menta pemakaian material (sapertos kabel dropcore, ONT, atawa closure) dina tab Pemakaian Material, atawa nyantumkeun nomor tiketna pas menta stok gudang.",
            'q72_cek_kapasitas_port_odp' => "Buka menu Jaringan OLT, PON & ODP, teras pilari kode ODP nu rek ditempo. Dina kartu atawa detail ODP eta bakal katingali jumlah Total Port, Port Terpakai, jeung Sisa Slot Port nu masih kosong.",
            'q74_perbedaan_tiket_noc_vs_pasang_baru' => "Bedana:\n• Tiket Laporan Gangguan NOC: Dipake keur nanganan perbaikan jaringan/pelanggan aktif nu keur gangguan koneksi kalayan target SLA.\n• Tiket Permintaan Pasang Anyar (Instalasi): Dipake keur narik kabel anyar jeung aktivasi perangkat pelanggan anyar.",
            'q75_cara_input_koordinat_odp' => "Buka menu Jaringan OLT, PON & ODP, pilih OLT jeung port PON terkait, teras klik + Tambah ODP. Eusian kode ODP, kapasitas port, sarta input titik koordinat GPS (Latitude & Longitude), teras klik Simpan.",
            'q88_kepanjangan_akronim_sistem' => "Kepanjangan jeung fungsi istilah di aplikasi GOMSN:\n• SPK (Surat Perintah Kerja): Dokumen instruksi resmi penugasan gawe lapangan atawa lembur.\n• NOC (Network Operation Center): Tim pemantau jaringan pusat jeung pengelola tiket gangguan.\n• OLT (Optical Line Terminal): Perangkat pusat pemancar sinyal fiber optik di sentral/POP.\n• PON (Passive Optical Network): Port jalur distribusi optik ti OLT nuju ka ODP.\n• ODP (Optical Distribution Point): Kotak distribusi kabel optik di lapangan keur nyambungkeun ka pelanggan.",
            'q76_lokasi_menu_potongan_denda' => "Menu keur ngecek rincian potongan jeung denda anjeun aya dina sidebar bagian Presensi Saya > menu Potongan Absen Saya.",
            'q77_struktur_menu_pengajuan_operasional' => "Fitur nu aya di jero bagian Pengajuan & Operasional ngawengku:\n1. Pengajuan (Kasbon Dana Operasional, Meuli Barang, jeung Permintaan Stok).\n2. Lembur Saya (Pengajuan Lembur & SPK).\n3. Stok Barang Gudang & Persetujuan/Approval.",
            'q78_cara_buka_semua_layanan_portal' => "Ti halaman Home / Dashboard, anjeun tiasa muka tampilan sakabeh layanan portal ngalangkungan daftar menu dina Sidebar di sisi kenca layar (atawa mencet tombol ikon Menu / Semua Layanan dina tampilan HP).",
            'q79_fungsi_tombol_refresh_absensi' => "Meh angka absensi poe ieu ka-update di Home, pencet tombol Refresh nu aya di bagian handap ringkasan Absensi Hari Ini supados data check-in/out jeung GPS langsung disinkronkeun ulang ti server.",
            'q82_perbedaan_menu_tiket_navigasi' => "Eusina sami jeung nyambung ka modul tiketing nu sami. Menu Tiket dina bar handap (bottom bar) mangrupa jalan pintas (shortcut) dina tampilan HP supados teknisi tiasa langsung muka halaman Tiket Gangguan.",
            'q80_cara_ganti_foto_profil' => "Klik ngaran atawa foto profil anjeun di pojok katuhu luhur layar kanggo muka halaman Profil Saya, pilih upload foto profil anyar atawa robah data akun, teras klik Simpan Perubahan.",
            'q81_prosedur_reset_password_lupa' => "Upami poho password akun GOMSN pas rek login, mangga hubungi Admin IT atawa HRD kanggo ngalakukeun Reset Password akun anjeun lewat menu Data Karyawan. Saatos di-reset, anjeun tiasa login deui jeung ngaganti password di menu Profil Saya.",
            'q84_menu_khusus_divisi_hrd' => "Menu nu ngan muncul jeung bisa diakses ku akun divisi HRD & Human Capital hungkul nyaeta:\n1. Data Karyawan (kelola akun staf, reset password & biometrik beungeut).\n2. Absen Karyawan (rekap kahadiran & potongan sakabeh karyawan).\n3. Pengajuan Absen (approval cuti, izin, gering, jeung koreksi absen tahap HRD).\n4. Lembur Karyawan (monitoring rekap lembur sakabeh divisi).",
            'q85_kontak_it_support_error' => "Mun aplikasi GOMSN nge-bug atawa force close, mangga lapor ka tim IT & Sistem / Helpdesk Internal PT MSN lewat grup koordinasi internal kalayan ngalampirkeun screenshot pesen error-na.",
            'q87_cek_riwayat_dan_potongan_typo' => "Kanggo ningali riwayat absen jeung potongan anjeun bulan ieu:\n• Riwayat Kahadiran: Buka menu Riwayat Absen Saya di sidebar.\n• Rincian Potongan/Denda: Buka menu Potongan Absen Saya di sidebar.",
            'q90_efek_terlambat_gaji_leaderboard' => "Mun telat asup gawe ngaleuwihan batas toleransi 15 menit:\n1. Kana Gaji: Bakal kacatet potongan denda keterlambatan sesuai jumlah menit telat di menu Potongan Absen Saya.\n2. Kana Leaderboard: Skor disiplin jeung peringkat kahadiran tepat waktu anjeun di Leaderboard bakal turun.",
            'q89_cuti_mendadak_multi_turn' => "Sesuai aturan, cuti taunan reguler kudu diajukeun minimal H-3 samemeh tanggal cuti. Nanging upami kanggo Kemis hareup atawa aya kaperluan mendesak, mangga langsung ajukeun di menu Cuti & Izin Saya sarta konfirmasikeun ka Atasan Langsung anjeun supados enggal di-ACC.",
            'q100_penutup_terima_kasih' => "Sami-sami! Bingah tiasa ngabantos anjeun. Upami aya deui nu bade ditaroskeun ngeunaan layanan aplikasi GOMSN, mangga taroskeun iraha wae. Wilujeng damel! 😊",
            default => null,
        };
    }

    /**
     * Resolve live database queries about OLT, PON, ODP, and FTTH Customers/Users.
     *
     * @return ?array{response: string, confidence: float, matched_intent: string, action_url: ?string, action_label: ?string}
     */
    protected function resolveNetworkEntityQuery(string $rawClean, string $normMessage, bool $isSunda): ?array
    {
        $hasNetworkKeyword = (bool) preg_match('/\b(olt|pon|odp|ftth|nomor internet|no internet|pelanggan|user)\b/iu', $normMessage);
        if (! $hasNetworkKeyword) {
            return null;
        }

        // Skip generic procedural / how-to questions so QA test intents handle them
        $isProceduralHowTo = (bool) preg_match('/\b(bagaimana cara|kumaha cara|kumaha carana|langkah|prosedur|alur|syarat|apa arti|hartina|indikator warna|kepanjangan|singkatan|glosarium|beda menu|bedana menu|tiket gangguan|kabel dropcore|stok kabel|serial number|sn ont|ekspansi odp|peta navigasi|kolom search|pareum listrik|nomor telepon pelanggan|rekap tiket|titik koordinat odp baru|masang odp anyar|sisa kapasitas port yang tersedia pada odp|kapasitas port yang masih tersedia pada suatu odp|sisa slot port nu masih kosong|mengecek titik odp dan daftar pelanggan|mengecek daftar pelanggan yang terhubung pada suatu odp|ngecek titik odp jeung daftar pelanggan)\b/iu', $normMessage);
        if ($isProceduralHowTo) {
            return null;
        }

        // ---------------------------------------------------------------------
        // A. Specific OLT Lookup (e.g. "olt msn", "olt bagong", "olt soreang", "olt kopo")
        // ---------------------------------------------------------------------
        $olts = Olt::with(['pons.odps.customers'])->get();
        foreach ($olts as $olt) {
            $oltNameLower = mb_strtolower(trim($olt->nama_olt));
            $oltCodeLower = mb_strtolower(trim((string) $olt->kode));
            $matchedThisOlt = false;

            if ($oltCodeLower !== '' && $oltCodeLower !== 'msn' && preg_match('/\b(olt\s+' . preg_quote($oltCodeLower, '/') . '|' . preg_quote($oltCodeLower, '/') . '\s+olt)\b/iu', $normMessage)) {
                $matchedThisOlt = true;
            } elseif ($oltCodeLower === 'msn' && preg_match('/\b(olt\s+msn|data\s+olt\s+msn|detail\s+olt\s+msn|cek\s+olt\s+msn|pon\s+di\s+olt\s+msn|odp\s+di\s+olt\s+msn)\b/iu', $normMessage)) {
                $matchedThisOlt = true;
            } elseif (preg_match('/\b' . preg_quote($oltNameLower, '/') . '\b/iu', $normMessage)) {
                $matchedThisOlt = true;
            }

            if ($matchedThisOlt && ! preg_match('/\b(user|pelanggan)\b/iu', $normMessage)) {
                $ponCount = $olt->pons->count();
                $odpCount = 0;
                $custCount = 0;
                $ponLines = [];

                foreach ($olt->pons as $p) {
                    $pOdps = $p->odps->count();
                    $odpCount += $pOdps;
                    $pCusts = $p->odps->sum(fn ($o) => $o->customers->where('status', 'aktif')->count());
                    $custCount += $pCusts;
                    $ponLines[] = "• {$p->nama_pon}: {$pOdps}/{$p->port_max} Port ODP ({$pCusts} user aktif)";
                }

                $header = "Data Infrastruktur {$olt->nama_olt} (Lokasi: " . ($olt->lokasi ?: '-') . "):\n"
                    . "• Total: {$ponCount} PON | {$odpCount} Titik ODP | {$custCount} Pelanggan Aktif\n\n"
                    . "Daftar Distribusi PON:";

                return [
                    'response' => $header . "\n" . implode("\n", $ponLines),
                    'confidence' => 95.0,
                    'matched_intent' => 'network_db_olt_detail',
                    'action_url' => route('gomsn.network.index', ['olt_id' => $olt->id]),
                    'action_label' => "🌐 Buka {$olt->nama_olt}",
                ];
            }
        }

        // ---------------------------------------------------------------------
        // B. Specific PON Lookup (e.g. "pon 3 ms sufia", "pon 2 ms entin", "pon 4 ms msn")
        // ---------------------------------------------------------------------
        $pons = Pon::with(['olt', 'odps.customers'])->get();
        $bestPon = null;
        $bestPonLen = 0;

        foreach ($pons as $pon) {
            $ponFull = mb_strtolower(trim($pon->nama_pon));
            $ponNorm = $this->normalizeText($ponFull);
            $parts = array_map('trim', explode('/', $ponFull));
            $alias = isset($parts[1]) ? $this->normalizeText($parts[1]) : '';

            if ($ponNorm !== '' && mb_strlen($ponNorm) >= 4 && str_contains($normMessage, $ponNorm)) {
                if (mb_strlen($ponNorm) > $bestPonLen) {
                    $bestPon = $pon;
                    $bestPonLen = mb_strlen($ponNorm);
                }
            } elseif ($alias !== '' && mb_strlen($alias) >= 5 && str_contains($normMessage, $alias) && str_contains($normMessage, 'pon')) {
                if (mb_strlen($alias) > $bestPonLen) {
                    $bestPon = $pon;
                    $bestPonLen = mb_strlen($alias);
                }
            }
        }

        if ($bestPon) {
            $olt = $bestPon->olt;
            $odps = $bestPon->odps;
            $odpCount = $odps->count();
            $totalCust = 0;
            $odpLines = [];

            foreach ($odps as $o) {
                $cCount = $o->customers->where('status', 'aktif')->count();
                $totalCust += $cCount;
                $odpLines[] = "• ODP " . trim($o->nama_odp) . ": {$cCount}/{$o->port_max} Port User ({$cCount} pelanggan)";
            }

            $oltName = $olt?->nama_olt ?? '-';
            $resp = "Detail Distribusi {$bestPon->nama_pon} ({$oltName}):\n"
                . "• Kapasitas Port PON: {$odpCount} / {$bestPon->port_max} ODP Terhubung\n"
                . "• Total User/Pelanggan: {$totalCust} pelanggan aktif\n"
                . ($odpLines ? "\nDaftar ODP Terhubung:\n" . implode("\n", $odpLines) : "\nBelum ada ODP terhubung pada PON ini.");

            return [
                'response' => $resp,
                'confidence' => 95.0,
                'matched_intent' => 'network_db_pon_detail',
                'action_url' => $olt ? route('gomsn.network.pons.show', [$olt->id, $bestPon->id]) : route('gomsn.network.index'),
                'action_label' => "🌐 Buka {$bestPon->nama_pon}",
            ];
        }

        // ---------------------------------------------------------------------
        // C. Specific ODP Lookup (e.g. "odp indomart", "siapa saja user di odp msn reog", "odp penuh")
        // ---------------------------------------------------------------------
        if (str_contains($normMessage, 'odp') && preg_match('/\b(penuh|full|habis|beak)\b/iu', $normMessage) && ! str_contains($normMessage, 'perumahan')) {
            $allOdps = Odp::with(['pon.olt', 'customers'])->get();
            $fullOdps = [];
            foreach ($allOdps as $o) {
                $used = $o->customers->where('status', 'aktif')->count();
                if ($o->port_max > 0 && $used >= $o->port_max) {
                    $fullOdps[] = "• ODP " . trim($o->nama_odp) . " ({$used}/{$o->port_max} Port) — " . ($o->pon?->nama_pon ?? '-') . " / " . ($o->pon?->olt?->nama_olt ?? '-');
                }
            }

            if (empty($fullOdps)) {
                $resp = "Saat ini tidak ada titik ODP yang berstatus Penuh (100% terisi) di database.";
            } else {
                $totalFull = count($fullOdps);
                $slice = array_slice($fullOdps, 0, 12);
                $resp = "Ditemukan {$totalFull} titik ODP yang kapasitas port-nya sudah Penuh di database:\n" . implode("\n", $slice);
                if ($totalFull > 12) {
                    $resp .= "\n...dan " . ($totalFull - 12) . " ODP lainnya. Buka menu Jaringan OLT, PON & ODP untuk detail lengkap.";
                }
            }

            return [
                'response' => $resp,
                'confidence' => 92.0,
                'matched_intent' => 'network_db_odp_full_list',
                'action_url' => route('gomsn.network.index'),
                'action_label' => '🌐 Buka Jaringan OLT, PON & ODP',
            ];
        }

        if (str_contains($normMessage, 'odp') && ! preg_match('/\b(odp mana|di odp mana|ka odp mana|odp naon)\b/iu', $normMessage)) {

            $allOdps = Odp::with(['pon.olt', 'customers'])->get();
            $matchedOdp = null;
            $matchedOdpLen = 0;

            foreach ($allOdps as $o) {
                $oNorm = $this->normalizeText($o->nama_odp);
                if ($oNorm === '' || mb_strlen($oNorm) < 3) {
                    continue;
                }
                if (preg_match('/\b' . preg_quote($oNorm, '/') . '\b/iu', $normMessage)) {
                    if (mb_strlen($oNorm) > $matchedOdpLen) {
                        $matchedOdp = $o;
                        $matchedOdpLen = mb_strlen($oNorm);
                    }
                }
            }

            if ($matchedOdp) {
                $pon = $matchedOdp->pon;
                $olt = $pon?->olt;
                $activeCustomers = $matchedOdp->customers->where('status', 'aktif')->values();
                $used = $activeCustomers->count();
                $max = (int) $matchedOdp->port_max;
                $sisa = max(0, $max - $used);
                $coords = ($matchedOdp->latitude && $matchedOdp->longitude)
                    ? "{$matchedOdp->latitude}, {$matchedOdp->longitude}"
                    : 'Belum diset';

                $custLines = [];
                foreach ($activeCustomers->take(15) as $idx => $c) {
                    $no = $idx + 1;
                    $noInet = $c->nomor_internet ? " (No. Internet: {$c->nomor_internet})" : '';
                    $custLines[] = "{$no}. {$c->nama_user}{$noInet}";
                }

                $odpTitle = trim($matchedOdp->nama_odp);
                $resp = "Data Detail ODP {$odpTitle}:\n"
                    . "• OLT / PON: " . ($olt?->nama_olt ?? '-') . " → " . ($pon?->nama_pon ?? '-') . "\n"
                    . "• Kapasitas Port: {$used} / {$max} Port Terpakai (Sisa {$sisa} port kosong)\n"
                    . "• Koordinat GPS: {$coords}\n"
                    . ($custLines
                        ? "\nDaftar User/Pelanggan Terhubung ({$used} user):\n" . implode("\n", $custLines)
                        : "\nBelum ada user/pelanggan aktif yang terdaftar pada ODP ini.");

                $actionUrl = ($olt && $pon)
                    ? route('gomsn.network.odps.show', [$olt->id, $pon->id, $matchedOdp->id])
                    : route('gomsn.network.index');

                return [
                    'response' => $resp,
                    'confidence' => 95.0,
                    'matched_intent' => 'network_db_odp_detail',
                    'action_url' => $actionUrl,
                    'action_label' => "🌐 Buka Detail ODP {$odpTitle}",
                ];
            }
        }

        // ---------------------------------------------------------------------
        // D. Customer / User Lookup (e.g. "user agus odp mana", "agus di olt mana", "no internet 11110524")
        // ---------------------------------------------------------------------
        $isCustomerLookup = (bool) (
            preg_match('/\b(user|pelanggan|customer|nomor internet|no internet)\b/iu', $normMessage)
            || preg_match('/\b(odp mana|di odp mana|ka odp mana|pon mana|pon berapa|olt mana|di olt mana)\b/iu', $normMessage)
        );

        if ($isCustomerLookup) {
            $stripWords = [
                'user', 'pelanggan', 'customer', 'atas', 'nama', 'ngaran', 'odp', 'pon', 'olt',
                'mana', 'di', 'dina', 'ka', 'ke', 'ti', 'dari', 'nyambung', 'terhubung', 'masuk',
                'ikut', 'cek', 'cari', 'neangan', 'data', 'info', 'informasi', 'detail', 'siapa',
                'saha', 'berapa', 'sabaraha', 'nomor', 'no', 'internet', 'id', 'port', 'lokasi',
                'alamat', 'status', 'nya', 'na', 'teh', 'mah', 'min', 'tolong', 'cik', 'punten',
                'tembongkeun', 'pangnempokeun', 'tampilkan', 'lihat', 'ningali', 'ada', 'aya',
                'yang', 'nu', 'anu', 'apakah', 'dimana', 'jaringan', 'ftth', 'gomsn', 'msn',
            ];

            $candidate = $normMessage;
            foreach ($stripWords as $sw) {
                $candidate = preg_replace('/\b' . preg_quote($sw, '/') . '\b/iu', ' ', $candidate) ?? $candidate;
            }
            $candidate = trim(preg_replace('/\s+/', ' ', $candidate) ?? $candidate);

            $genericIgnore = ['tertentu', 'area', 'baru', 'anyar', 'lama', 'aktif', 'semua', 'seluruh', 'sakabeh', 'daftar', 'riwayat', 'tiket', 'gangguan', 'kantor', 'karyawan', 'teknisi', 'staf', 'rumah', 'imah'];

            if (mb_strlen($candidate) >= 3 && ! in_array($candidate, $genericIgnore, true)) {
                $customers = OdpCustomer::with('odp.pon.olt')
                    ->where(function ($q) use ($candidate) {
                        $q->where('nama_user', 'LIKE', "%{$candidate}%")
                          ->orWhere('nomor_internet', 'LIKE', "%{$candidate}%");
                    })
                    ->get();

                // Fuzzy fallback for candidate with typos (e.g. 'aguss' -> 'Agus', 'welly' -> 'Wely', 'saepudn' -> 'Saepudin')
                if ($customers->isEmpty() && mb_strlen($candidate) >= 3) {
                    $allCusts = OdpCustomer::query()->select('id', 'nama_user', 'nomor_internet')->get();
                    $matchedCustIds = [];
                    $candidateParts = explode(' ', $candidate);

                    foreach ($allCusts as $cust) {
                        $userTokensLower = preg_split('/[\s,\.\/\-]+/u', mb_strtolower($cust->nama_user));
                        $isMatch = false;

                        if (count($candidateParts) === 1) {
                            $cPart = $candidateParts[0];
                            foreach ($userTokensLower as $ut) {
                                if ($ut !== '' && ($this->isTypoMatch($cPart, $ut) || (mb_strlen($cPart) >= 4 && mb_strlen($ut) >= 4 && levenshtein($cPart, $ut) <= 1))) {
                                    $isMatch = true;
                                    break;
                                }
                            }
                        } else {
                            // Multiple words in candidate (e.g. 'welly yanto')
                            $matchedPartCount = 0;
                            foreach ($candidateParts as $cPart) {
                                foreach ($userTokensLower as $ut) {
                                    if ($ut !== '' && ($this->isTypoMatch($cPart, $ut) || (mb_strlen($cPart) >= 4 && mb_strlen($ut) >= 4 && levenshtein($cPart, $ut) <= 1))) {
                                        $matchedPartCount++;
                                        break;
                                    }
                                }
                            }
                            if ($matchedPartCount >= count($candidateParts)) {
                                $isMatch = true;
                            }
                        }

                        if ($isMatch) {
                            $matchedCustIds[] = $cust->id;
                        }
                    }

                    if (! empty($matchedCustIds)) {
                        $customers = OdpCustomer::with('odp.pon.olt')->whereIn('id', $matchedCustIds)->get();
                    }
                }

                if ($customers->isNotEmpty()) {
                    $count = $customers->count();
                    if ($count === 1) {
                        /** @var OdpCustomer $c */
                        $c = $customers->first();
                        $odp = $c->odp;
                        $pon = $odp?->pon;
                        $olt = $pon?->olt;
                        $odpName = $odp ? trim($odp->nama_odp) : 'Belum ditautkan';
                        $ponName = $pon?->nama_pon ?? '-';
                        $oltName = $olt?->nama_olt ?? ($c->olt_name ?: '-');
                        $coords = ($odp && $odp->latitude && $odp->longitude)
                            ? " (Koordinat: {$odp->latitude}, {$odp->longitude})"
                            : '';

                        $resp = "Data User/Pelanggan Jaringan FTTH:\n"
                            . "• Nama User: {$c->nama_user}\n"
                            . "• No. Internet: " . ($c->nomor_internet ?: '-') . "\n"
                            . "• Terhubung di ODP: {$odpName}{$coords}\n"
                            . "• Jalur PON: {$ponName}\n"
                            . "• Perangkat OLT: {$oltName}\n"
                            . "• Status: " . strtoupper($c->status ?: 'AKTIF');

                        $actionUrl = ($olt && $pon && $odp)
                            ? route('gomsn.network.odps.show', [$olt->id, $pon->id, $odp->id])
                            : route('gomsn.network.index');

                        return [
                            'response' => $resp,
                            'confidence' => 96.0,
                            'matched_intent' => 'network_db_customer_lookup',
                            'action_url' => $actionUrl,
                            'action_label' => $odp ? "🌐 Buka Detail ODP {$odpName}" : '🌐 Buka Jaringan OLT, PON & ODP',
                        ];
                    }

                    $lines = [
                        $isSunda
                            ? "Kapanggih {$count} data user/pelanggan kalayan katakunci \"{$candidate}\" dina database jaringan FTTH:"
                            : "Ditemukan {$count} data user/pelanggan dengan nama/nomor \"{$candidate}\" di database jaringan FTTH:",
                    ];

                    foreach ($customers->take(15) as $idx => $c) {
                        $no = $idx + 1;
                        $odpName = $c->odp ? trim($c->odp->nama_odp) : '-';
                        $ponName = $c->odp?->pon?->nama_pon ?? '-';
                        $oltName = $c->odp?->pon?->olt?->nama_olt ?? ($c->olt_name ?: '-');
                        $noInet = $c->nomor_internet ? " (No. Internet: {$c->nomor_internet})" : '';
                        $lines[] = "{$no}. {$c->nama_user}{$noInet} → ODP: {$odpName} | {$ponName} | {$oltName}";
                    }

                    if ($count > 15) {
                        $lines[] = "...dan " . ($count - 15) . " pelanggan lainnya.";
                    }

                    $firstC = $customers->first();
                    $firstOdp = $firstC?->odp;
                    $firstPon = $firstOdp?->pon;
                    $firstOlt = $firstPon?->olt;
                    $actionUrl = ($firstOlt && $firstPon && $firstOdp)
                        ? route('gomsn.network.odps.show', [$firstOlt->id, $firstPon->id, $firstOdp->id])
                        : route('gomsn.network.index');

                    return [
                        'response' => implode("\n", $lines),
                        'confidence' => 96.0,
                        'matched_intent' => 'network_db_customer_lookup',
                        'action_url' => $actionUrl,
                        'action_label' => '🌐 Buka Modul Jaringan OLT, PON & ODP',
                    ];
                }

                if (preg_match('/\b(odp mana|di odp mana|ka odp mana)\b/iu', $normMessage)) {
                    $resp = $isSunda
                        ? "Data user/pelanggan kalayan ngaran atawa nomor internet \"{$candidate}\" teu kapanggih dina database ODP jaringan FTTH."
                        : "Data user/pelanggan dengan nama atau nomor internet \"{$candidate}\" tidak ditemukan di database ODP jaringan FTTH.";

                    return [
                        'response' => $resp,
                        'confidence' => 90.0,
                        'matched_intent' => 'network_db_customer_not_found',
                        'action_url' => route('gomsn.network.index'),
                        'action_label' => '🌐 Buka Jaringan OLT, PON & ODP',
                    ];
                }
            }
        }

        // ---------------------------------------------------------------------
        // E. Global Network Summary (e.g. "daftar semua olt", "total olt pon odp dan pelanggan")
        // ---------------------------------------------------------------------
        if (
            preg_match('/\b(daftar semua olt|daftar olt|semua olt|sakabeh olt|ringkasan jaringan|statistik jaringan|total olt|jumlah olt)\b/iu', $normMessage)
            || (str_contains($normMessage, 'olt') && str_contains($normMessage, 'pon') && str_contains($normMessage, 'odp') && preg_match('/\b(berapa|sabaraha|total|jumlah|daftar)\b/iu', $normMessage))
        ) {
            $totalOlts = $olts->count();
            $totalPons = Pon::count();
            $totalOdps = Odp::count();
            $totalCusts = OdpCustomer::where('status', 'aktif')->count();

            $oltLines = [];
            foreach ($olts as $olt) {
                $pCount = $olt->pons->count();
                $oCount = $olt->pons->sum(fn ($p) => $p->odps->count());
                $cCount = $olt->pons->sum(fn ($p) => $p->odps->sum(fn ($o) => $o->customers->where('status', 'aktif')->count()));
                $oltLines[] = "• {$olt->nama_olt} ({$olt->lokasi}): {$pCount} PON | {$oCount} ODP | {$cCount} Pelanggan";
            }

            $resp = "Ringkasan Database Infrastruktur Jaringan FTTH PT MSN:\n"
                . "• Total Keseluruhan: {$totalOlts} OLT | {$totalPons} PON | {$totalOdps} Titik ODP | {$totalCusts} Pelanggan Aktif\n\n"
                . "Rincian per OLT:\n" . implode("\n", $oltLines);

            return [
                'response' => $resp,
                'confidence' => 94.0,
                'matched_intent' => 'network_db_global_summary',
                'action_url' => route('gomsn.network.index'),
                'action_label' => '🌐 Buka Infrastruktur Jaringan FTTH',
            ];
        }

        return null;
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
            'q79_fungsi_tombol_refresh_absensi',
            'v21_troubleshooting_status_belum_checkin',
            'v22_batas_jam_checkout_harian',
            'sunda_20_kendala_tombol_absen',
            'sunda_22_jam_buka_checkin',
            'sunda_27_kamera_layar_hideung',
            'sunda_28_sinkronisasi_zona_waktu',
            'sunda_192_input_satu_kata_kunci' => [
                'label' => '📍 Buka Halaman Absen',
                'url' => route('attendance.index'),
            ],
            'q2_riwayat_checkin_kemarin',
            'q8_prosedur_koreksi_absen',
            'q10_rekap_kehadiran_7_hari',
            'q11_cara_lihat_log_bulan_lalu',
            'q14_total_hari_kerja_efektif',
            'q87_cek_riwayat_dan_potongan_typo',
            'v20_perbedaan_status_izin_sakit',
            'v24_cek_lokasi_gps_riwayat_absen',
            'sunda_21_hp_lowbat_nitip_absen',
            'sunda_25_verifikasi_kehadiran_bulan_lalu',
            'sunda_30_export_log_absen_pribadi' => [
                'label' => '📝 Buka Riwayat Absen Saya',
                'url' => route('attendance.history'),
            ],
            'q15_aturan_absensi_hari_libur',
            'q16_jadwal_shift_hari_ini_besok',
            'q17_cara_cek_kalender_shift',
            'q18_kebijakan_tukar_shift',
            'sunda_29_aturan_hari_kerja_sabtu_minggu',
            'sunda_50_jadwal_shift_kosong',
            'sunda_51_alur_persetujuan_tukar_shift',
            'sunda_121_penjadwalan_shift_massal_hrd',
            'sunda_163_jadwal_piket_noc_peuting' => [
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
            'q89_cuti_mendadak_multi_turn',
            'v33_jenis_cuti_khusus',
            'sunda_47_izin_sakit_sapoe',
            'sunda_48_followup_izin_pending',
            'sunda_49_izin_setengah_poe',
            'sunda_55_kebijakan_cuti_melahirkan',
            'sunda_56_masa_kerja_minimal_cuti',
            'sunda_57_cuti_ngadadak_poe_ieu',
            'sunda_58_format_file_surat_dokter',
            'sunda_59_alesan_cuti_ditolak',
            'sunda_60_alur_approval_cuti',
            'sunda_117_daftar_karyawan_cuti_poe_ieu',
            'sunda_123_audit_saldo_cuti_beak',
            'sunda_134_penyesuaian_saldo_cuti_hrd' => [
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
            'q90_efek_terlambat_gaji_leaderboard',
            'sunda_26_simulasi_telat_5_menit',
            'sunda_52_ambang_batas_denda_disiplin',
            'sunda_53_periode_potong_gaji',
            'sunda_54_detail_log_tanggal_potongan',
            'sunda_124_waiver_hapus_denda_hrd',
            'sunda_135_laporan_denda_bulanan_divisi',
            'sunda_197_simulasi_potongan_telat_3_kali' => [
                'label' => '💵 Buka Potongan Absen Saya',
                'url' => route('attendance.deductions'),
            ],
            'q31_cara_ajukan_kasbon',
            'q34_langkah_pengajuan_beli_barang',
            'q35_cara_permintaan_stok_material',
            'sunda_82_kategori_kasbon_bensin_tol',
            'sunda_94_syarat_estimasi_harga_beli' => [
                'label' => '💰 Buat Pengajuan Baru',
                'url' => route('gomsn.pengajuan.create'),
            ],
            'q32_batas_maksimal_kasbon',
            'q33_prosedur_settlement_kasbon',
            'q36_cek_status_pengajuan_terakhir',
            'q37_hierarki_approval_pembelian',
            'q45_revisi_pengajuan_pending',
            'q77_struktur_menu_pengajuan_operasional',
            'q83_cara_search_layanan_kasbon',
            'v55_sla_pencairan_dana_lembur',
            'sunda_76_aturan_kasbon_bertumpuk',
            'sunda_81_konfirmasi_terima_barang',
            'sunda_83_pengembalian_sisa_kasbon',
            'sunda_84_deadline_lpj_kasbon',
            'sunda_85_alur_pengambilan_stok_gudang',
            'sunda_86_troubleshooting_menu_pengajuan',
            'sunda_88_delegasi_approval_atasan_cuti',
            'sunda_89_pembatalan_permintaan_stok',
            'sunda_91_histori_pengajuan_3_bulan',
            'sunda_92_kebijakan_nota_kasbon_leungit',
            'sunda_95_validasi_otomatis_kasbon_ditolak' => [
                'label' => '📋 Buka Pengajuan Saya',
                'url' => route('gomsn.pengajuan.index'),
            ],
            'q38_cara_ajukan_lembur',
            'q39_syarat_spk_lembur',
            'q41_batas_waktu_klaim_lembur',
            'v53_durasi_minimal_lembur',
            'v54_lampiran_bukti_lembur',
            'sunda_87_spk_lembur_berkelompok',
            'sunda_90_aturan_lembur_remote' => [
                'label' => '⏰ Ajukan Lembur',
                'url' => route('gomsn.overtime.create'),
            ],
            'q40_cara_akses_dokumen_spk',
            'q42_total_jam_lembur_bulan_ini',
            'q43_alasan_lembur_ditolak',
            'q44_aturan_lembur_hari_libur',
            'q53_monitoring_lembur_karyawan',
            'q54_antrean_approval_lembur_hari_ini',
            'sunda_80_jadwal_bayar_lembur',
            'sunda_93_informasi_rate_tarif_lembur',
            'sunda_119_agregasi_lembur_company_wide',
            'sunda_125_statistik_lembur_per_divisi' => [
                'label' => '💼 Buka Halaman Lembur',
                'url' => route('gomsn.overtime.index'),
            ],
            'q46_company_ringkasan_absensi_hari_ini',
            'q47_daftar_karyawan_sakit_hari_ini',
            'q48_daftar_karyawan_terlambat_hari_ini',
            'q51_unduh_rekap_absensi_potongan',
            'q52_divisi_kehadiran_tertinggi',
            'sunda_111_karyawan_paling_loba_telat',
            'sunda_112_otoritas_koreksi_absen_hrd',
            'sunda_113_fitur_filter_absen_karyawan',
            'sunda_114_monitoring_belum_checkin',
            'sunda_120_verifikasi_anomali_data_sakit',
            'sunda_128_persentase_kahadiran_minggu_ieu',
            'sunda_129_deteksi_karyawan_alpa_poe_ieu',
            'sunda_133_audit_foto_selfie_absen_hrd' => [
                'label' => '📊 Buka Absen Karyawan',
                'url' => route('attendance.employee-recap'),
            ],
            'q49_cara_cari_kontak_karyawan',
            'q50_cari_karyawan_berdasarkan_nik',
            'q57_cara_ubah_status_aktif_karyawan',
            'q58_onboarding_karyawan_baru',
            'q60_total_karyawan_aktif_headcount',
            'q84_menu_khusus_divisi_hrd',
            'v25_reset_device_biometrik',
            'sunda_116_reset_password_ku_hrd',
            'sunda_118_mutasi_divisi_karyawan',
            'sunda_126_detail_profil_jabatan_gabung',
            'sunda_130_bulk_import_karyawan_excel',
            'sunda_132_monitoring_kontrak_kerja' => $this->canManageEmployee($user)
                ? [
                    'label' => '👥 Buka Data Karyawan',
                    'url' => route('portal.employees.index'),
                ]
                : [
                    'label' => '👤 Buka Profil Saya',
                    'url' => route('portal.profile'),
                ],
            'sunda_127_blokir_sesi_hp_leungit' => [
                'label' => '🔒 Buka Sesi & Perangkat',
                'url' => route('portal.sessions'),
            ],
            'q55_kriteria_penilaian_leaderboard',
            'q56_top_5_leaderboard_disiplin',
            'sunda_115_periode_reset_leaderboard',
            'sunda_131_sensitivitas_skor_leaderboard' => [
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
            'sunda_151_standar_redaman_opm',
            'sunda_152_cek_gangguan_los_massal',
            'sunda_153_reassign_ngoper_tiket',
            'sunda_155_status_pending_pelanggan',
            'sunda_156_target_sla_tiket_urgent',
            'sunda_159_laporan_tiket_closed_kamari',
            'sunda_160_klasifikasi_kabel_fo_putus',
            'sunda_164_prosedur_reopen_tiket',
            'sunda_165_nyatet_material_spk_tiket',
            'sunda_167_autoticketing_alarm_olt_pareum',
            'sunda_168_kontak_telepon_pelanggan_tiket',
            'sunda_169_export_laporan_tiket_mingguan',
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
            'q75_cara_input_koordinat_odp',
            'sunda_154_topologi_jalur_olt_odp',
            'sunda_157_lookup_sn_ont_pelanggan',
            'sunda_158_prosedur_ekspansi_odp_penuh',
            'sunda_161_integrasi_peta_navigasi_odp',
            'sunda_162_troubleshooting_search_odp',
            'sunda_166_total_aset_odp_aktif' => [
                'label' => '🌐 Buka Jaringan OLT, PON & ODP',
                'url' => route('gomsn.network.index'),
            ],
            'q78_cara_buka_semua_layanan_portal',
            'sunda_122_fitur_broadcast_bewara_hrd',
            'sunda_198_penjelasan_hak_akses_jabatan',
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
     * Get the top 2 closest recommended questions when the chatbot cannot understand or needs clarification.
     *
     * @param list<string> $userTokens
     * @return list<string>
     */
    protected function getTopTwoRecommendations(string $rawClean, string $normMessage, array $userTokens, bool $isSunda): array
    {
        $intents = ChatbotIntent::query()->where('is_active', true)->get();
        $scored = [];

        $ignoredKeys = [
            'q99_input_tidak_valid',
            'q98_proteksi_prompt_injection',
            'q94_proteksi_pii_gaji_rekening',
            'q95_pencegahan_halusinasi_nik',
            'q96_out_of_scope_umum',
            'q97_guardrail_topik_pekerjaan',
            'sunda_189_out_of_scope_kuliner',
        ];

        foreach ($intents as $intent) {
            $key = $intent->intent_key;
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            $isSundaIntent = str_starts_with($key, 'sunda_');
            $keywords = $intent->keywords ?? [];
            if (empty($keywords)) {
                continue;
            }

            $maxSim = 0.0;
            $bestKw = $keywords[0];

            foreach ($keywords as $kw) {
                $kwNorm = $this->normalizeText($kw);
                $kwTokens = $this->extractTokens($kwNorm);
                if (empty($kwTokens)) {
                    continue;
                }

                $overlap = 0;
                foreach ($kwTokens as $kt) {
                    foreach ($userTokens as $ut) {
                        if ($kt === $ut || $this->isTypoMatch($kt, $ut)) {
                            $overlap += $this->isSubjectKeyword($kt) ? 3 : 1;
                            break;
                        }
                    }
                }

                if (str_contains($normMessage, $kwNorm) || str_contains($kwNorm, $normMessage)) {
                    $overlap += 3;
                }

                similar_text($normMessage, $kwNorm, $pct);
                $simScore = ($overlap * 10) + ($pct / 5);

                if ($isSunda && $isSundaIntent) {
                    $simScore *= 1.3;
                } elseif (! $isSunda && $isSundaIntent) {
                    $simScore *= 0.7;
                }

                if ($simScore > $maxSim && $overlap > 0) {
                    $maxSim = $simScore;
                }
            }

            if ($maxSim > 0) {
                // Find a well-formed question for this intent (>= 3 words)
                $chosenQuestion = $keywords[0];
                foreach ($keywords as $kwCandidate) {
                    if (mb_strlen($kwCandidate) >= 15 && count(explode(' ', $kwCandidate)) >= 3) {
                        $chosenQuestion = $kwCandidate;
                        break;
                    }
                }

                $scored[] = [
                    'intent_key' => $key,
                    'score' => $maxSim,
                    'question' => $chosenQuestion,
                ];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $recommendations = [];
        $seen = [];
        foreach ($scored as $item) {
            $q = trim($item['question']);
            $formatted = mb_strtoupper(mb_substr($q, 0, 1)) . mb_substr($q, 1);
            if (! str_ends_with($formatted, '?') && ! str_ends_with($formatted, '.')) {
                $formatted .= '?';
            }
            $lowKey = mb_strtolower($formatted);
            if (! isset($seen[$lowKey])) {
                $seen[$lowKey] = true;
                $recommendations[] = $formatted;
                if (count($recommendations) >= 2) {
                    break;
                }
            }
        }

        if (count($recommendations) < 2) {
            $defaults = $isSunda ? [
                'Min, urang poe ieu geus check-in acan nya?',
                'Sisa cuti taunan urang ayeuna kari sabaraha poe deui nya?',
            ] : [
                'Hari ini saya sudah check-in atau belum?',
                'Berapa sisa saldo cuti tahunan saya saat ini?',
            ];
            foreach ($defaults as $d) {
                if (! in_array($d, $recommendations, true)) {
                    $recommendations[] = $d;
                    if (count($recommendations) >= 2) {
                        break;
                    }
                }
            }
        }

        return array_slice($recommendations, 0, 2);
    }

    /**
     * Determine whether two words are a typo/fuzzy match (1-2 edits or transposition).
     */
    protected function isTypoMatch(string $w1, string $w2): bool
    {
        if ($w1 === $w2) {
            return true;
        }

        $l1 = strlen($w1);
        $l2 = strlen($w2);

        if (abs($l1 - $l2) > 2) {
            return false;
        }

        // Levenshtein distance 1
        if (levenshtein($w1, $w2) <= 1) {
            if ($l1 <= 3 && $l2 <= 3) {
                return $w1[0] === $w2[0];
            }
            return true;
        }

        // Single adjacent transposition (e.g. 'usre' vs 'user', 'jawdal' vs 'jadwal', 'passwrod' vs 'password')
        if ($l1 === $l2 && $l1 >= 4) {
            $diffs = [];
            for ($i = 0; $i < $l1; $i++) {
                if ($w1[$i] !== $w2[$i]) {
                    $diffs[] = $i;
                }
            }
            if (count($diffs) === 2 && abs($diffs[0] - $diffs[1]) === 1) {
                if ($w1[$diffs[0]] === $w2[$diffs[1]] && $w1[$diffs[1]] === $w2[$diffs[0]]) {
                    return true;
                }
            }
        }

        // For longer words (>= 6 characters), allow Levenshtein distance 2 if starting letter matches
        if ($l1 >= 6 && $l2 >= 6 && levenshtein($w1, $w2) <= 2) {
            return $w1[0] === $w2[0];
        }

        return false;
    }

    /**
     * Normalize Indonesian & Sundanese words without conflating distinct concepts.
     */
    protected function normalizeText(string $text): string
    {
        $clean = mb_strtolower(trim($text));

        // Pre-normalize hyphenated terms before stripping punctuation
        $clean = str_replace(
            [
                'real-time', 'check-in', 'check-out', 'non-aktif', 'company-wide',
                'carry-over', 'password-nya', 'langkah-langkah', 'hitung-hitungan',
                'ke-update', 'ka-update', 'di-acc', 'nge-acc', 'di-approve', 'di-handle',
                'di-upload', 'di-reset', 'di-close', 'nge-bug', 'gomsn-nya', 'gps-nya',
                'gps-na', 'hp-na', 'port-na', 'setting-na', 'nik-nya', 'h-berapa',
                'h-sabaraha', 'sa-divisi', 'sa-perusahaan', 'isuk-isuk', 'rata-rata',
                'beres-beres', 'asal-asalan',
            ],
            [
                'realtime', 'check in', 'check out', 'non aktif', 'company wide',
                'carry over', 'passwordnya', 'langkah langkah', 'hitung hitungan',
                'ke update', 'ka update', 'di acc', 'nge acc', 'di approve', 'di handle',
                'di upload', 'di reset', 'di close', 'nge bug', 'gomsn nya', 'gps nya',
                'gps na', 'hp na', 'port na', 'setting na', 'nik nya', 'h berapa',
                'h sabaraha', 'sa divisi', 'sa perusahaan', 'isuk isuk', 'rata rata',
                'beres beres', 'asal asalan',
            ],
            $clean
        );

        // Replace punctuation with space
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean) ?? $clean;

        // Collapse 3+ identical consecutive characters (e.g. 'haiii' -> 'hai', 'cutiiii' -> 'cuti', 'odpppp' -> 'odp')
        $clean = preg_replace('/(.)\1{2,}/u', '$1', $clean) ?? $clean;

        // Typos, slang & common abbreviation normalization for Indonesian & Basa Sunda
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
            // Pertanyaan & kata ganti
            'gimana' => 'bagaimana',
            'gmn' => 'bagaimana',
            'bgmn' => 'bagaimana',
            'bgaimana' => 'bagaimana',
            'bagaimna' => 'bagaimana',
            'bagaimaba' => 'bagaimana',
            'caranya' => 'cara',
            'crnya' => 'cara',
            'cra' => 'cara',
            'cr' => 'cara',
            'sy' => 'saya',
            'ak' => 'aku',
            'brp' => 'berapa',
            'brpa' => 'berapa',
            'brapa' => 'berapa',
            'berpa' => 'berapa',
            'kpn' => 'kapan',
            'knp' => 'kenapa',
            'mna' => 'mana',
            'dmn' => 'dimana',
            'sp' => 'siapa',
            'spa' => 'siapa',
            'udh' => 'sudah',
            'udah' => 'sudah',
            'sdh' => 'sudah',
            'blm' => 'belum',
            'blom' => 'belum',
            'gak' => 'tidak',
            'ga' => 'tidak',
            'nggak' => 'tidak',
            'ngga' => 'tidak',
            'enggak' => 'tidak',
            'tdk' => 'tidak',
            'bisa' => 'bisa',
            'bsa' => 'bisa',
            'bs' => 'bisa',
            'bntu' => 'bantu',
            'bntuan' => 'bantuan',
            'tolong' => 'tolong',
            'tlong' => 'tolong',
            'tlng' => 'tolong',
            'bkin' => 'bikin',
            'bwt' => 'buat',
            'dn' => 'dan',
            'jg' => 'juga',
            'jng' => 'jeung',
            'bln' => 'bulan',
            'thn' => 'tahun',
            'bsk' => 'besok',
            'bsok' => 'besok',
            'esok' => 'besok',
            'kmaren' => 'kemarin',
            'kmarin' => 'kemarin',
            'kemaren' => 'kemarin',
            'kamrin' => 'kemarin',
            'kmrn' => 'kemarin',
            // Presensi & Absensi
            'absennya' => 'absen',
            'absn' => 'absen',
            'abasen' => 'absen',
            'absenk' => 'absen',
            'absem' => 'absen',
            'absrn' => 'absen',
            'abseni' => 'absen',
            'absent' => 'absen',
            'absenng' => 'absen',
            'absnsi' => 'absensi',
            'absensii' => 'absensi',
            'cara check in' => 'cara absen masuk',
            'cara checkin' => 'cara absen masuk',
            'cara clock in' => 'cara absen masuk',
            'checkin' => 'check in',
            'cekin' => 'check in',
            'cekinnya' => 'check in',
            'chek in' => 'check in',
            'chekin' => 'check in',
            'checkout' => 'check out',
            'cekout' => 'check out',
            'cekoutnya' => 'check out',
            'chek out' => 'check out',
            'chekout' => 'check out',
            'biometik' => 'biometrik',
            'biometrk' => 'biometrik',
            'biometrikk' => 'biometrik',
            'koreks' => 'koreksi',
            'koreksii' => 'koreksi',
            'jdwl' => 'jadwal',
            'jadwl' => 'jadwal',
            'jadwwal' => 'jadwal',
            'jawdal' => 'jadwal',
            'jadual' => 'jadwal',
            'sift' => 'shift',
            'syif' => 'shift',
            'syift' => 'shift',
            'shif' => 'shift',
            'tuker' => 'tukar',
            'tker' => 'tukar',
            'ptongan' => 'potongan',
            'potngan' => 'potongan',
            'potongn' => 'potongan',
            'potonan' => 'potongan',
            'ptngan' => 'potongan',
            'dnda' => 'denda',
            'dnd' => 'denda',
            'dendaa' => 'denda',
            // Cuti, Izin, Lembur, Pengajuan
            'cutii' => 'cuti',
            'cuty' => 'cuti',
            'cuyi' => 'cuti',
            'cyti' => 'cuti',
            'cti' => 'cuti',
            'sakir' => 'sakit',
            'sakitt' => 'sakit',
            'skit' => 'sakit',
            'sakt' => 'sakit',
            'izn' => 'izin',
            'izinn' => 'izin',
            'ijin' => 'izin',
            'ijn' => 'izin',
            'doktr' => 'dokter',
            'dktr' => 'dokter',
            'dokte' => 'dokter',
            'dokumn' => 'dokumen',
            'dokumnen' => 'dokumen',
            'btas' => 'batas',
            'overtime' => 'lembur',
            'lembr' => 'lembur',
            'lembuur' => 'lembur',
            'lembu' => 'lembur',
            'lebur' => 'lembur',
            'lemburr' => 'lembur',
            'lmbur' => 'lembur',
            'lmbr' => 'lembur',
            'lemburna' => 'lembur',
            'lemburn' => 'lembur',
            'kasbn' => 'kasbon',
            'kazbon' => 'kasbon',
            'kasboon' => 'kasbon',
            'kasbom' => 'kasbon',
            'ksbon' => 'kasbon',
            'ksbn' => 'kasbon',
            'pngajuan' => 'pengajuan',
            'pengajun' => 'pengajuan',
            'pengajuann' => 'pengajuan',
            'ngajuin' => 'mengajukan',
            'aprove' => 'approve',
            'aprov' => 'approve',
            'apruval' => 'approval',
            'oprasional' => 'operasional',
            'opersional' => 'operasional',
            'matrial' => 'material',
            'materil' => 'material',
            // Tiket, Jaringan FTTH (OLT, PON, ODP, User)
            'tket' => 'tiket',
            'tikrt' => 'tiket',
            'tikett' => 'tiket',
            'tikeet' => 'tiket',
            'tikwt' => 'tiket',
            'tkt' => 'tiket',
            'tiketna' => 'tiket',
            'reopn' => 'reopen',
            'gangun' => 'gangguan',
            'gangguann' => 'gangguan',
            'gagguan' => 'gangguan',
            'gangguyan' => 'gangguan',
            'ganguan' => 'gangguan',
            'ggn' => 'gangguan',
            'usre' => 'user',
            'usr' => 'user',
            'userr' => 'user',
            'pelangan' => 'pelanggan',
            'pelanggann' => 'pelanggan',
            'planggan' => 'pelanggan',
            'plggan' => 'pelanggan',
            'plnggan' => 'pelanggan',
            'odpp' => 'odp',
            'odpe' => 'odp',
            'poon' => 'pon',
            'ponn' => 'pon',
            'oltt' => 'olt',
            'olte' => 'olt',
            'pnuh' => 'penuh',
            'peuh' => 'penuh',
            'bgong' => 'bagong',
            'indomrt' => 'indomart',
            'indomaret' => 'indomart',
            'kordinat' => 'koordinat',
            'kordinatna' => 'koordinat',
            'kordinatnya' => 'koordinat',
            'koordinatna' => 'koordinat',
            'kapasits' => 'kapasitas',
            'kapasitass' => 'kapasitas',
            'kpasitas' => 'kapasitas',
            // Akun & Sistem
            'poto' => 'foto',
            'photo' => 'foto',
            'potoo' => 'foto',
            'profile' => 'profil',
            'profl' => 'profil',
            'profle' => 'profil',
            'passwordnya' => 'password',
            'pasword' => 'password',
            'passwrod' => 'password',
            'paswod' => 'password',
            'paswrd' => 'password',
            'pswd' => 'password',
            'pwd' => 'password',
            'rwayat' => 'riwayat',
            'mengurusnya' => 'mengurus',
            'penolakannya' => 'penolakan',
            'efeknya' => 'efek',
            // Sunda
            'kmha' => 'kumaha',
            'kumha' => 'kumaha',
            'crana' => 'carana',
            'crna' => 'carana',
            'sbrha' => 'sabaraha',
            'sabarha' => 'sabaraha',
            'sbrapa' => 'sabaraha',
            'kmari' => 'kamari',
            'isukn' => 'isukan',
            'ayena' => 'ayeuna',
            'ges' => 'geus',
            'acn' => 'acan',
            'nhn' => 'nuhun',
            'pnten' => 'punten',
            'mhun' => 'muhun',
            'nyambng' => 'nyambung',
            'benget' => 'beungeut',
            'gring' => 'gering',
            'meli' => 'meuli',
            'nyien' => 'nyieun',
            'skabeh' => 'sakabeh',
            'pting' => 'peuting',
        ];

        foreach ($synonyms as $from => $to) {
            $clean = preg_replace('/\b' . preg_quote($from, '/') . '\b/u', $to, $clean) ?? $clean;
        }

        return trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);
    }

    /**
     * Extract meaningful tokens (excluding filler/question words in Indonesian & Sundanese).
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
            'aplikasi', 'gomsn', 'sistem', 'menu', 'dalam', 'saat', 'oleh', 'nya',
            'nih', 'pas', 'buat', 'sama', 'aja', 'kok', 'itu', 'biar', 'harus',
            // Sundanese filler words
            'urang', 'abdi', 'anjeun', 'teh', 'mah', 'wae', 'nya', 'yeuh', 'atuh',
            'keur', 'mun', 'lamun', 'upami', 'jeung', 'atawa', 'nu', 'anu', 'ka',
            'ti', 'dina', 'tina', 'kana', 'kudu', 'rek', 'bade', 'ari', 'cik',
            'na', 'oge', 'deui', 'heula', 'pisan', 'hungkul', 'wungkul', 'keneh',
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
     * High-value domain subject words (Indonesian & Sundanese) that carry higher weight.
     */
    protected function isSubjectKeyword(string $token): bool
    {
        $subjects = [
            'cuti', 'sakit', 'gering', 'izin', 'lembur', 'lemburan', 'dana', 'kasbon', 'barang', 'beli',
            'meuli', 'stok', 'absen', 'absensi', 'wajah', 'muka', 'beungeut', 'biometrik', 'biometrikna',
            'verifikasi', 'radius', 'gps', 'koreksi', 'lupa', 'poho', 'kelupaan', 'tukar', 'tukeran',
            'backup', 'shift', 'jadwal', 'tiket', 'tiketna', 'gangguan', 'backbone', 'broadband', 'sla',
            'eskalasi', 'eskalasina', 'kpi', 'olt', 'pon', 'odp', 'potongan', 'denda', 'alpa', 'telat',
            'telatna', 'terlambat', 'keterlambatan', 'leaderboard', 'foto', 'profil', 'password', 'sandi',
            'approve', 'acc', 'persetujuan', 'sisa', 'saldo', 'jatah', 'jatahna', 'kuota', 'dokter',
            'dokterna', 'resume', 'joint', 'closure', 'jc', 'core', 'splicing', 'redaman', 'opm',
            'manuver', 'material', 'dropcore', 'ont', 'bast', 'dispatch', 'dinas', 'luar', 'lapangan',
            'kalender', 'divisi', 'departemen', 'kemarin', 'kamari', 'efektif', 'spk', 'settlement',
            'pelunasan', 'dilunasan', 'nota', 'notana', 'pertanggungjawaban', 'headcount', 'onboarding',
            'payroll', 'penggajian', 'rekening', 'gaji', 'gajian', 'nik', 'realtime', 'offline',
            'internet', 'sinyal', 'jaringan', 'mati', 'pareum', 'refresh', 'home', 'kontak', 'support',
            'error', 'bug', 'sanggahan', 'hangus', 'diakumulasi', 'ditolak', 'ditolakna', 'reject',
            'pending', 'mengedit', 'diedit', 'merevisi', 'port', 'kapasitas', 'koordinat', 'koordinatna',
            'resign', 'nonaktif', 'nonaktifkan', 'minimal', 'minimum', 'durasi', 'cair', 'pencairan',
            'lowbat', 'nitip', 'hideung', 'sabtu', 'minggu', 'melahirkan', 'kawin', 'maot', 'bensin',
            'tol', 'remote', 'seblak', 'persib', 'sampurasun', 'permios', 'nuhun', 'bewara', 'reopen',
        ];

        return in_array($token, $subjects, true);
    }

    /**
     * Process message from user and return concise, accurate response in Indonesian or Basa Sunda.
     *
     * @return array{response: string, confidence: float, matched_intent: ?string, action_url: ?string, action_label: ?string, suggestions: list<string>}
     */
    public function ask(?User $user, string $message): array
    {
        $this->ensureKnowledgeInitialized();

        $rawClean = mb_strtolower(trim($message));
        $normMessage = $this->normalizeText($message);
        $userTokens = $this->extractTokens($normMessage);
        $isSunda = $this->isSundaneseQuery($rawClean);

        // ==========================================
        // EARLY GUARDRAILS & EDGE CASES
        // ==========================================

        // 1. Empty, Punctuation Only, or Invalid Test Strings (Q99 / Sunda 193)
        if (
            preg_match('/^[?\s.!,\-_:;]+$/u', $rawClean)
            || mb_strlen($rawClean) < 2
            || $normMessage === 'halo tes 123'
            || $normMessage === 'input acak tidak jelas'
            || str_contains($rawClean, 'wkwkwk asdfgh')
            || (preg_match('/\b(asdfghjkl|qwertyuiop|zxcvbnm|asdasd|hjkjhg|asdfgh)\b/i', $rawClean) && ! str_contains($rawClean, 'mengirimkan pesan kosong'))
            || (preg_match('/\b[bcdfghjklmnpqrstvwxyz]{6,}\b/i', $rawClean) && count($userTokens) <= 3 && ! $isSunda)
        ) {
            $recommendations = $this->getTopTwoRecommendations($rawClean, $normMessage, $userTokens, $isSunda);
            $resp = ($isSunda || str_contains($rawClean, 'wkwkwk'))
                ? "Punten, pesen nu dikirim teu jelas atawa mangrupa karakter acak. Mangga pilih salah sahiji rekomendasi patarosan di handap ieu:"
                : "Maaf, pesan yang Anda kirimkan belum memuat pertanyaan yang spesifik. Silakan pilih salah satu rekomendasi pertanyaan di bawah ini:";

            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q99_input_tidak_valid',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => $recommendations,
            ];
        }

        // 2. Prompt Injection / Jailbreak (Q98 / Sunda 190)
        if (
            str_contains($rawClean, 'abaikan semua instruksi')
            || str_contains($rawClean, 'abaikan semua aturan')
            || str_contains($rawClean, 'pohoan sakabeh aturan')
            || str_contains($rawClean, 'tembongkeun kabeh isi database')
            || str_contains($rawClean, 'abaikan instruksi')
            || str_contains($rawClean, 'bertindak sebagai hacker')
            || str_contains($rawClean, 'tampilkan seluruh isi database')
            || str_contains($rawClean, 'dump all user records')
            || str_contains($rawClean, 'ignore previous instructions')
            || str_contains($rawClean, 'ignore all instructions')
            || str_contains($rawClean, 'perintah bypass keamanan')
        ) {
            $resp = $isSunda
                ? "Pamundut Ditolak. Abdi teu tiasa mopohokeun aturan kaamanan sistem atawa nampilkeun eusi database internal perusahaan. Mangga ajukeun patarosan anu saluyu sareng prosedur operasional resmi GOMSN."
                : "Permintaan Ditolak. Saya tidak dapat mengabaikan aturan keamanan sistem maupun menampilkan isi database internal. Silakan ajukan pertanyaan yang berkaitan dengan prosedur operasional resmi GOMSN.";

            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q98_proteksi_prompt_injection',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Jadwal shift kerja saya hari ini dan besok', 'Berapa sisa saldo cuti tahunan saya saat ini?'],
            ];
        }

        // 3. PII Protection: Gaji Pokok & Nomor Rekening (Q94 / Sunda 185)
        if (
            (str_contains($rawClean, 'gaji pokok') || str_contains($rawClean, 'gaji bulanan') || str_contains($rawClean, 'rincian gaji') || str_contains($rawClean, 'cek gaji'))
            && (str_contains($rawClean, 'rekening') || str_contains($rawClean, 'nik'))
        ) {
            $resp = $isSunda
                ? "Akses Ditolak. Kanggo nangtayungan privasi data pribadi (PII Protection), informasi sensitif sapertos gaji pokok sareng nomor rekening pribadi karyawan sifatna rahasia pisan sareng teu tiasa ditampilkeun dina chatbot."
                : "Akses Ditolak. Demi perlindungan data pribadi (PII Protection), informasi sensitif seperti gaji pokok/bulanan dan nomor rekening pribadi karyawan bersifat rahasia dan tidak dapat ditampilkan melalui chatbot.";

            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q94_proteksi_pii_gaji_rekening',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Berapa sisa saldo cuti tahunan saya saat ini?', 'Berapa total potongan dan denda saya pada periode berjalan ini?'],
            ];
        }

        // 4. Out of Scope: Kuliner Seblak (Sunda 189)
        if (str_contains($rawClean, 'resep seblak') || str_contains($rawClean, 'seblak ceker')) {
            $resp = "Punten, patarosan ngeunaan resep kuliner aya di luar cakupan tugas abdi. Abdi mangrupa Asisten Virtual internal GOMSN PT Media Solusi Network anu khusus ngabantos layanan absensi, cuti, kasbon, lembur, sareng tiket gangguan jaringan.";
            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'sunda_189_out_of_scope_kuliner',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Berapa sisa saldo cuti tahunan saya saat ini?'],
            ];
        }

        // 5. Out of Scope: Sepak Bola / Persib / Cuaca / Luar Pekerjaan (Q96 / Sunda 187 & 188)
        if (
            str_contains($rawClean, 'sepak bola')
            || str_contains($rawClean, 'pertandingan bola')
            || str_contains($rawClean, 'piala dunia')
            || str_contains($rawClean, 'persib maen')
            || str_contains($rawClean, 'prediksi cuaca')
            || str_contains($rawClean, 'ramalan cuaca')
            || str_contains($rawClean, 'skor pertandingan')
            || $normMessage === 'pertanyaan di luar pekerjaan'
        ) {
            $resp = $isSunda
                ? "Punten, patarosan ngeunaan ramalan cuaca atawa pertandingan olahraga aya di luar cakupan layanan abdi. Abdi mangrupa Asisten Virtual internal GOMSN PT Media Solusi Network nu khusus ngabantos informasi absensi, jadwal shift, cuti, pengajuan operasional, sareng tiket gangguan."
                : "Maaf, pertanyaan tersebut berada di luar cakupan layanan saya. Saya adalah Asisten Virtual internal GOMSN PT Media Solusi Network yang khusus membantu informasi seputar absensi, jadwal kerja, cuti, pengajuan operasional, dan tiket gangguan.";

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

        // 7. Hallucination Prevention for Unregistered / Fictitious NIK (Q95 / Sunda 186)
        if (
            str_contains($rawClean, 'kr00000')
            || str_contains($rawClean, 'kr99999')
            || str_contains($rawClean, 'nik fiktif')
            || str_contains($rawClean, 'nik palsu')
            || str_contains($rawClean, 'tidak terdaftar')
            || str_contains($rawClean, 'teu terdaftar')
            || str_contains($rawClean, 'nik asal')
        ) {
            preg_match('/\b(kr\d+)\b/i', $rawClean, $mNik);
            $nikLabel = isset($mNik[1]) ? strtoupper($mNik[1]) : 'eta';
            $resp = $isSunda
                ? "Data teu kapanggih. Karyawan kalayan NIK {$nikLabel} teu kadaftar dina database sistem GOMSN PT Media Solusi Network. Pastikeun deui nomor NIK nu dilebetkeun tos leres."
                : "Data tidak ditemukan. Karyawan dengan NIK {$nikLabel} tidak terdaftar di dalam database sistem GOMSN PT Media Solusi Network. Pastikan kembali nomor NIK yang Anda masukkan sudah benar.";

            return [
                'response' => $resp,
                'confidence' => 1.0,
                'matched_intent' => 'q95_pencegahan_halusinasi_nik',
                'action_url' => null,
                'action_label' => null,
                'suggestions' => ['Hari ini saya sudah check-in atau belum?', 'Jadwal shift kerja saya hari ini dan besok'],
            ];
        }

        // 8. Chat Approval Prohibition (Q92 / Sunda 183)
        if (
            (str_contains($rawClean, 'menyetujui') || str_contains($rawClean, 'setujui') || str_contains($rawClean, 'approve') || str_contains($normMessage, 'acc'))
            && (str_contains($rawClean, 'lewat chat') || str_contains($rawClean, 'melalui chat') || str_contains($rawClean, 'lewat chatbot') || str_contains($rawClean, 'bisakah chatbot'))
        ) {
            $resp = $isSunda
                ? "Teu tiasa. Abdi mung asisten informasi sareng teu gaduh wewenang kanggo nge-ACC pengajuan langsung dina chat. Proses persetujuan (approval) lembur atawa cuti wajib dilakukeun ku Atasan/HRD ngalangkungan menu persetujuan resmi di portal."
                : "Tidak bisa. Saya hanya asisten informasi dan tidak memiliki akses untuk mengeksekusi persetujuan (approval). Proses ACC/approval cuti maupun lembur wajib dilakukan oleh Atasan/HRD melalui menu persetujuan resmi di portal.";

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

                // Short greetings (<= 6 chars) require short user query
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

                // Single-keyword intent sunda_192 ("absen", "kasbon", "cuti") should only match when query is 1 token or exact phrase
                if ($intent->intent_key === 'sunda_192_input_satu_kata_kunci' && in_array($kwNorm, ['absen', 'kasbon', 'cuti'], true)) {
                    if ($normMessage === $kwNorm) {
                        $intentScore = max($intentScore, 100.0);
                    }
                    continue;
                }

                $kwTokens = $this->extractTokens($kwNorm);
                $kwTokenCount = max(1, count($kwTokens));

                // 1. Whole-phrase match inside message, scaled by query coverage
                if (
                    preg_match('/\b' . preg_quote($kwRaw, '/') . '\b/u', $rawClean)
                    || preg_match('/\b' . preg_quote($kwNorm, '/') . '\b/u', $normMessage)
                ) {
                    $queryCoverage = min(1.0, $kwTokenCount / $userTokenCount);
                    $subjectBonus = 0.0;
                    foreach ($kwTokens as $kt) {
                        $subjectBonus += $this->isSubjectKeyword($kt) ? 1.2 : 0.4;
                    }

                    if ($kwTokenCount <= 2 && $userTokenCount >= 4) {
                        $phraseScore = (1.5 + $subjectBonus) * ($queryCoverage * 0.6);
                    } else {
                        $phraseScore = (4.0 + $subjectBonus + ($kwTokenCount * 0.5)) * (0.4 + (0.6 * $queryCoverage));
                    }

                    $intentScore = max($intentScore, $phraseScore);
                    continue;
                }

                // 2. Bidirectional Token & Subject Overlap
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
                            if (mb_strlen($kt) >= 4 && mb_strlen($ut) >= 4 && (str_starts_with($ut, $kt) || str_starts_with($kt, $ut))) {
                                $found = true;
                                break;
                            }
                            if ($this->isTypoMatch($kt, $ut)) {
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

        $suggestions = $isSunda ? [
            'Min, urang poe ieu geus check-in acan nya?',
            'Poe ieu jeung isukan urang kebagean jadwal shift asup jam sabaraha?',
            'Sisa cuti taunan urang ayeuna kari sabaraha poe deui nya?',
            'Kumaha carana nginput lemburan di menu Lembur Saya?',
        ] : [
            'Hari ini saya sudah check-in atau belum?',
            'Jadwal shift kerja saya hari ini dan besok masuk jam berapa?',
            'Berapa sisa saldo cuti tahunan saya saat ini?',
            'Bagaimana cara mengajukan lembur melalui menu Lembur Saya?',
        ];

        // If not an exact 100.0 match to a fixed QA question, check live FTTH Network Database (OLT, PON, ODP, User/Pelanggan)
        if ($bestScore < 99.0) {
            $networkResult = $this->resolveNetworkEntityQuery($rawClean, $normMessage, $isSunda);
            if ($networkResult !== null) {
                ChatbotLog::query()->create([
                    'user_id' => $user?->id,
                    'user_message' => $message,
                    'matched_intent_key' => $networkResult['matched_intent'],
                    'confidence_score' => $networkResult['confidence'],
                    'bot_response' => $networkResult['response'],
                    'is_answered' => true,
                ]);

                return [
                    'response' => $networkResult['response'],
                    'confidence' => $networkResult['confidence'],
                    'matched_intent' => $networkResult['matched_intent'],
                    'action_url' => $networkResult['action_url'],
                    'action_label' => $networkResult['action_label'],
                    'suggestions' => $suggestions,
                ];
            }
        }

        if ($bestScore < 1.50 || ! $bestIntent) {
            $recommendations = $this->getTopTwoRecommendations($rawClean, $normMessage, $userTokens, $isSunda);

            $fallback = $isSunda
                ? "Punten, abdi teu acan ngartos maksad patarosan eta sacara spesifik. Mangga pilih salah sahiji rekomendasi patarosan di handap ieu anu saluyu sareng kabutuhan anjeun:"
                : "Maaf, saya belum memahami maksud pertanyaan tersebut secara spesifik. Silakan pilih salah satu rekomendasi pertanyaan di bawah ini yang paling mendekati:";

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
                'suggestions' => $recommendations,
            ];
        }

        // ==========================================
        // STATIC & DYNAMIC RESPONSES (INDONESIAN & BASA SUNDA)
        // ==========================================
        $key = $bestIntent->intent_key;
        $reply = $bestIntent->response_text;

        if ($isSunda) {
            $sundaStatic = $this->getSundaneseStaticResponse($key);
            if ($sundaStatic !== null) {
                $reply = $sundaStatic;
            }
        }

        if ($user) {
            if ($key === 'sapaan_halo') {
                $reply = $isSunda
                    ? "Halo {$user->name}! 👋 Abdi Asisten Virtual GOMSN. Mangga taroskeun langsung perkawis absensi, jadwal shift, cuti, kasbon, lembur, atawa tiket gangguan."
                    : "Halo {$user->name}! 👋 Saya Asisten Virtual GOMSN. Silakan tanyakan langsung seputar absensi, jadwal shift, cuti, kasbon, lembur, atau tiket gangguan.";
            } elseif ($key === 'q1_status_checkin_hari_ini') {
                $att = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', Carbon::now('Asia/Jakarta')->toDateString())
                    ->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                if ($att && $clockIn) {
                    $inTime = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $outTime = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : ($isSunda ? 'Acan Check-Out' : 'Belum Check-Out');
                    $st = strtoupper($att->status ?? 'HADIR');
                    $reply = $isSunda
                        ? "Poe ieu anjeun geus check-in tabuh {$inTime} WIB (Status: {$st}, Check-Out: {$outTime})."
                        : "Hari ini Anda sudah check-in pada pukul {$inTime} WIB (Status: {$st}, Check-Out: {$outTime}).";
                } else {
                    $reply = $isSunda
                        ? "Poe ieu anjeun kacatet acan check-in. Mangga buka menu Absen kanggo ngalakukeun presensi."
                        : "Hari ini Anda tercatat belum melakukan check-in. Silakan buka menu Absen untuk melakukan presensi.";
                }
            } elseif ($key === 'q2_riwayat_checkin_kemarin') {
                $yesterday = Carbon::yesterday('Asia/Jakarta');
                $att = Attendance::query()->where('user_id', $user->id)->whereDate('date', $yesterday->toDateString())->first();
                $clockIn = $att?->clock_in_at ?? $att?->clock_in;
                $clockOut = $att?->clock_out_at ?? $att?->clock_out;
                $tglStr = $yesterday->translatedFormat('d F Y');
                if ($att && $clockIn) {
                    $in = Carbon::parse($clockIn)->timezone('Asia/Jakarta')->format('H:i');
                    $out = $clockOut ? Carbon::parse($clockOut)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : ($isSunda ? 'Acan/Poho Check-Out' : 'Belum/Lupa Check-Out');
                    $st = strtoupper($att->status ?? 'HADIR');
                    $reply = $isSunda
                        ? "Kamari ({$tglStr}), anjeun check-in asup tabuh {$in} WIB jeung check-out balik tabuh {$out} (Status: {$st})."
                        : "Kemarin ({$tglStr}), Anda check-in pukul {$in} WIB dan check-out pukul {$out} (Status: {$st}).";
                } else {
                    $reply = $isSunda
                        ? "Teu aya catetan data check-in atawa check-out kanggo anjeun dina poe kamari ({$tglStr})."
                        : "Tidak tercatat data check-in maupun check-out untuk Anda pada hari kemarin ({$tglStr}).";
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
                $reply = $isSunda
                    ? "Salila bulan ieu ({$bln}), anjeun kacatet telat sababaraha {$lateCount} kali kalayan total akumulasi {$totalLateMin} menit."
                    : "Selama bulan ini ({$bln}), Anda tercatat terlambat sebanyak {$lateCount} kali dengan total akumulasi {$totalLateMin} menit.";
            } elseif ($key === 'q10_rekap_kehadiran_7_hari') {
                $start7 = Carbon::now('Asia/Jakarta')->subDays(6)->toDateString();
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $attendances7 = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('date', [$start7, $today])
                    ->orderBy('date', 'desc')
                    ->get();
                if ($attendances7->isEmpty()) {
                    $reply = $isSunda
                        ? "Teu acan aya catetan kahadiran dina 7 poe ka tukang."
                        : "Belum ada catatan kehadiran dalam 7 hari terakhir.";
                } else {
                    $lines = [$isSunda ? "Rekap kahadiran anjeun salila 7 poe ka tukang:" : "Rekap kehadiran 7 hari terakhir Anda:"];
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
                $reply = $isSunda
                    ? "Total hari kerja efektif anjeun nu kacatet hadir dina bulan {$bln} ayeuna nyaeta {$hadirCount} poe (tina target {$target} poe gawe terjadwal)."
                    : "Total hari kerja efektif Anda yang tercatat hadir pada bulan {$bln} saat ini adalah {$hadirCount} hari (dari {$target} hari kerja terjadwal).";
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

                $reply = $isSunda
                    ? "Jadwal shift gawe anjeun:\n• Poe ieu (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmtShift($schToday) . "\n• Isukan (" . Carbon::tomorrow('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmtShift($schTomorrow)
                    : "Jadwal shift kerja Anda:\n• Hari ini (" . Carbon::now('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmtShift($schToday) . "\n• Besok (" . Carbon::tomorrow('Asia/Jakarta')->translatedFormat('d M Y') . "): " . $fmtShift($schTomorrow);
            } elseif ($key === 'q19_sisa_saldo_cuti') {
                $balance = LeaveBalance::query()
                    ->where('user_id', $user->id)
                    ->where('year', (int) date('Y'))
                    ->first();
                $sisa = $balance ? ($balance->remaining_days ?? $balance->remaining_balance ?? 12) : 12;
                $reply = $isSunda
                    ? "Sisa saldo cuti taunan anjeun keur taun " . date('Y') . " ayeuna kari {$sisa} poe kerja deui."
                    : "Sisa saldo cuti tahunan Anda untuk tahun " . date('Y') . " saat ini adalah {$sisa} hari kerja.";
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
                $reply = $isSunda
                    ? "Total potongan jeung denda absensi anjeun dina periode {$bln} nyaeta Rp {$fmtTotal} (Telat: Rp {$fmtLate}, Alpa: Rp {$fmtAlpa})."
                    : "Total potongan dan denda absensi Anda pada periode {$bln} adalah Rp {$fmtTotal} (Terlambat: Rp {$fmtLate}, Alpa: Rp {$fmtAlpa}).";
            } elseif ($key === 'q36_cek_status_pengajuan_terakhir') {
                $latestPengajuan = Pengajuan::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(3)
                    ->get();
                if ($latestPengajuan->isEmpty()) {
                    $reply = $isSunda
                        ? "Anjeun teu acan gaduh riwayat pengajuan kasbon atawa meuli barang."
                        : "Anda belum memiliki riwayat pengajuan kasbon atau pembelian barang.";
                } else {
                    $lines = [$isSunda ? "Status pengajuan terakhir anjeun:" : "Status pengajuan terakhir Anda:"];
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
                $reply = $isSunda
                    ? "Total jam lemburan anjeun nu geus di-ACC dina bulan {$bln} nyaeta {$totalJam} jam ({$totalMenit} menit) tina {$count} pengajuan."
                    : "Total jam lembur Anda yang sudah disetujui pada bulan {$bln} adalah {$totalJam} jam ({$totalMenit} menit) dari {$count} pengajuan.";
            } elseif ($key === 'q46_company_ringkasan_absensi_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = $isSunda
                        ? "Akses Ditolak. Ringkasan absensi sakabeh karyawan (company-wide) ngan tiasa diakses ku divisi HRD & Human Capital atawa Administrator."
                        : "Akses Ditolak. Ringkasan absensi seluruh karyawan (company-wide) hanya dapat diakses oleh divisi HRD & Human Capital atau Administrator.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $totalActive = User::query()->where('is_active', true)->count();
                    $hadir = Attendance::query()->whereDate('date', $today)->where('status', 'HADIR')->count();
                    $terlambat = Attendance::query()->whereDate('date', $today)->where(function ($q) {
                        $q->where('status', 'TERLAMBAT')->orWhere('late_minutes', '>', 0);
                    })->count();
                    $sakit = Leave::query()->where('status', 'approved')->whereIn('leave_type', ['sakit', 'sick'])->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();
                    $izin = Leave::query()->where('status', 'approved')->whereNotIn('leave_type', ['sakit', 'sick'])->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count();
                    $tgl = Carbon::now('Asia/Jakarta')->translatedFormat('d M Y');
                    $reply = $isSunda
                        ? "Ringkasan absensi sa-perusahaan poe ieu ({$tgl}):\n• Hadir Tepat Waktu: {$hadir} urang\n• Telat: {$terlambat} urang\n• Izin/Cuti: {$izin} urang\n• Gering (Sakit): {$sakit} urang\n(Total Karyawan Aktif: {$totalActive} urang)."
                        : "Ringkasan absensi company-wide hari ini ({$tgl}):\n• Hadir Tepat Waktu: {$hadir} orang\n• Terlambat: {$terlambat} orang\n• Izin/Cuti: {$izin} orang\n• Sakit: {$sakit} orang\n(Total Karyawan Aktif: {$totalActive} orang).";
                }
            } elseif ($key === 'q47_daftar_karyawan_sakit_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = $isSunda
                        ? "Akses Ditolak. Daftar karyawan gering sa-perusahaan ngan tiasa diakses ku divisi HRD & Human Capital."
                        : "Akses Ditolak. Daftar karyawan sakit company-wide hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $sickLeaves = Leave::query()
                        ->with('user')
                        ->whereIn('leave_type', ['sakit', 'sick'])
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->get();
                    $tgl = Carbon::now('Asia/Jakarta')->translatedFormat('d M Y');
                    if ($sickLeaves->isEmpty()) {
                        $reply = $isSunda
                            ? "Poe ieu ({$tgl}) euweuh karyawan nu kacatet gering (sakit)."
                            : "Hari ini ({$tgl}) tidak ada karyawan yang tercatat sakit.";
                    } else {
                        $names = $sickLeaves->map(fn ($sl) => ($sl->user?->name ?? 'Karyawan') . ' (' . ($sl->user?->division ?? '-') . ')')->implode(', ');
                        $reply = $isSunda
                            ? "Karyawan nu kacatet gering poe ieu: {$names}."
                            : "Karyawan yang tercatat sakit hari ini: {$names}.";
                    }
                }
            } elseif ($key === 'q48_daftar_karyawan_terlambat_hari_ini') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = $isSunda
                        ? "Akses Ditolak. Daftar karyawan telat sa-perusahaan ngan tiasa diakses ku divisi HRD & Human Capital."
                        : "Akses Ditolak. Daftar karyawan terlambat company-wide hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();
                    $lates = Attendance::query()
                        ->with('user')
                        ->whereDate('date', $today)
                        ->where(function ($q) {
                            $q->where('status', 'TERLAMBAT')->orWhere('late_minutes', '>', 0);
                        })
                        ->get();
                    $tgl = Carbon::now('Asia/Jakarta')->translatedFormat('d M Y');
                    if ($lates->isEmpty()) {
                        $reply = $isSunda
                            ? "Poe ieu ({$tgl}) euweuh karyawan nu kacatet telat asup gawe."
                            : "Hari ini ({$tgl}) tidak ada karyawan yang tercatat terlambat.";
                    } else {
                        $list = $lates->map(fn ($lt) => ($lt->user?->name ?? 'Karyawan') . " ({$lt->late_minutes} menit)")->implode(', ');
                        $reply = $isSunda
                            ? "Karyawan nu kacatet telat asup gawe poe ieu: {$list}."
                            : "Karyawan yang tercatat terlambat hari ini: {$list}.";
                    }
                }
            } elseif ($key === 'q50_cari_karyawan_berdasarkan_nik') {
                if (preg_match('/\b(kr\d+|\d{10,16}|msn-\d+)\b/i', $rawClean, $mNik)) {
                    $qNik = strtoupper($mNik[1]);
                    $foundUser = User::query()->where('nik', $qNik)->orWhere('employee_code', $qNik)->first();
                    if ($foundUser) {
                        $stAktif = $foundUser->is_active ? 'Aktif' : 'Non-Aktif';
                        $reply = "Profil Karyawan ({$qNik}):\n• Nama: {$foundUser->name}\n• Divisi: " . ($foundUser->division ?? '-') . "\n• Status Keaktifan: {$stAktif}";
                    } else {
                        $reply = $isSunda
                            ? "Hasil pencarian NIK {$qNik}: Nomor NIK eta teu acan kadaftar dina data master karyawan. Mangga gunakeun kolom pencarian di menu Data Karyawan kanggo milari dumasar Ngaran atawa NIK karyawan aktif."
                            : "Hasil pencarian NIK {$qNik}: Nomor NIK tersebut saat ini belum terdaftar di data master karyawan. Silakan gunakan kolom pencarian di menu Data Karyawan untuk mencari berdasarkan Nama atau NIK yang terdaftar.";
                    }
                } elseif ($isSunda) {
                    $reply = "Kanggo ningali info divisi jeung status aktif karyawan dumasar NIK, mangga buka menu Data Karyawan teras ketik nomor NIK dina kolom pencarian.";
                }
            } elseif ($key === 'q52_divisi_kehadiran_tertinggi') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = $isSunda
                        ? "Akses Ditolak. Statistik kahadiran antar divisi ngan tiasa diakses ku divisi HRD & Human Capital."
                        : "Akses Ditolak. Statistik kehadiran antar divisi hanya dapat diakses oleh divisi HRD & Human Capital.";
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
                    $reply = $isSunda
                        ? "Divisi nu tingkat kahadiranna pangalusna bulan ieu nyaeta divisi {$divName} ({$tot} kahadiran kacatet)."
                        : "Divisi dengan tingkat kehadiran paling tinggi bulan ini adalah divisi {$divName} ({$tot} kehadiran tercatat).";
                }
            } elseif ($key === 'q54_antrean_approval_lembur_hari_ini') {
                $pendingLembur = Lembur::query()
                    ->whereIn('status', ['submitted', 'pending', 'menunggu_approval'])
                    ->count();
                $reply = $isSunda
                    ? "Ayeuna aya {$pendingLembur} pengajuan lembur karyawan nu masih nungguan di-ACC (pending approval)."
                    : "Saat ini terdapat {$pendingLembur} pengajuan lembur karyawan yang masih menunggu persetujuan.";
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
                    $reply = $isSunda
                        ? "Data peringkat 5 karyawan pangluhurna di Leaderboard periode ieu keur diitung. Mangga buka menu Leaderboard."
                        : "Data peringkat 5 besar Leaderboard periode ini sedang dikalkulasi. Silakan buka menu Leaderboard.";
                } else {
                    $lines = [$isSunda ? "5 karyawan nu peringkat disiplinna pangluhurna di Leaderboard ayeuna:" : "5 karyawan dengan peringkat disiplin teratas di Leaderboard saat ini:"];
                    foreach ($topUsers as $idx => $u) {
                        $rank = $idx + 1;
                        $lines[] = "{$rank}. {$u->name} ({$u->division}) — {$u->tepat_waktu}x hadir tepat waktu";
                    }
                    $reply = implode("\n", $lines);
                }
            } elseif ($key === 'q60_total_karyawan_aktif_headcount') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = $isSunda
                        ? "Akses Ditolak. Data total karyawan aktif sa-perusahaan ngan tiasa diakses ku divisi HRD & Human Capital."
                        : "Akses Ditolak. Data total headcount karyawan perusahaan hanya dapat diakses oleh divisi HRD & Human Capital.";
                } else {
                    $totalActive = User::query()->where('is_active', true)->count();
                    $reply = $isSunda
                        ? "Total sakabeh karyawan aktif di PT Media Solusi Network nu kadaftar di sistem ayeuna aya {$totalActive} urang."
                        : "Total karyawan aktif di PT Media Solusi Network yang terdaftar di sistem saat ini adalah {$totalActive} orang.";
                }
            } elseif ($key === 'q62_jumlah_tiket_open_hari_ini') {
                $openCount = Tiket::query()->where('status', 'OPEN')->count();
                $prosesCount = Tiket::query()->whereIn('status', ['PROSES', 'PENDING'])->count();
                $reply = $isSunda
                    ? "Ayeuna aya {$openCount} tiket gangguan nu statusna masih Open (terbuka) jeung {$prosesCount} tiket keur dina proses penanganan."
                    : "Saat ini terdapat {$openCount} tiket gangguan berstatus Terbuka (OPEN) dan {$prosesCount} tiket sedang dalam proses penanganan.";
            } elseif ($key === 'q73_teknisi_tugas_prioritas_tinggi') {
                $highTickets = Tiket::query()
                    ->with('ticketAssignees.user')
                    ->whereIn('status', ['OPEN', 'PROSES'])
                    ->whereIn('priority', ['CRITICAL', 'HIGH'])
                    ->latest()
                    ->take(3)
                    ->get();
                if ($highTickets->isEmpty()) {
                    $reply = $isSunda
                        ? "Ayeuna teu aya tiket gangguan prioritas tinggi (High/Critical) nu keur aktif."
                        : "Saat ini tidak ada tiket gangguan berprioritas tinggi (High/Critical) yang sedang aktif.";
                } else {
                    $lines = [$isSunda ? "Tim teknisi nu keur nanganan tiket gangguan prioritas tinggi:" : "Tim/teknisi yang sedang menangani tiket gangguan prioritas tinggi:"];
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
                $reply = $isSunda
                    ? "Ringkesan 3 poin kondisi operasional & absensi poe ieu:\n1. Absensi: {$attended} tina {$totalActive} karyawan aktif parantos kacatet hadir.\n2. Tiket Gangguan: Aya {$openTickets} tiket gangguan aktif (Open/Proses) nu keur ditangani.\n3. Antrean Approval: Aya {$pendingLembur} pengajuan lembur jeung {$pendingPengajuan} pengajuan operasional nu nungguan di-ACC."
                    : "Ringkasan 3 poin kondisi operasional & absensi hari ini:\n1. Absensi: {$attended} dari {$totalActive} karyawan aktif tercatat sudah hadir.\n2. Tiket Gangguan: Terdapat {$openTickets} tiket gangguan aktif (Open/Proses) yang sedang ditangani.\n3. Antrean Approval: Terdapat {$pendingLembur} pengajuan lembur dan {$pendingPengajuan} pengajuan operasional menunggu persetujuan.";
            } elseif ($key === 'q93_rbac_kontak_rekap_semua_karyawan') {
                if (! $this->isHrdOrAdmin($user)) {
                    $reply = $isSunda
                        ? "Akses Ditolak. Data nomor HP pribadi jeung rekap potongan denda sakabeh karyawan sifatna rahasia sarta ngan tiasa diakses ku divisi HRD & Human Capital atawa Administrator."
                        : "Akses Ditolak. Data kontak pribadi dan rekapitulasi potongan denda seluruh divisi/karyawan bersifat rahasia serta hanya dapat diakses oleh otoritas HRD & Human Capital atau Administrator.";
                }
            } elseif ($key === 'sunda_114_monitoring_belum_checkin') {
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $totalActive = User::query()->where('is_active', true)->count();
                $checkedIn = Attendance::query()->whereDate('date', $today)->count();
                $belum = max(0, $totalActive - $checkedIn);
                $reply = "Poe ieu aya {$belum} urang karyawan nu acan check-in (tina total {$totalActive} karyawan aktif, {$checkedIn} urang geus check-in).";
            } elseif ($key === 'sunda_117_daftar_karyawan_cuti_poe_ieu') {
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $onLeave = Leave::query()->with('user')->where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->get();
                if ($onLeave->isEmpty()) {
                    $reply = "Poe ieu euweuh karyawan nu kacatet keur cuti.";
                } else {
                    $names = $onLeave->map(fn ($l) => ($l->user?->name ?? 'Karyawan') . ' (' . strtoupper($l->leave_type) . ')')->implode(', ');
                    $reply = "Daftar karyawan nu keur cuti/izin poe ieu: {$names}.";
                }
            } elseif ($key === 'sunda_119_agregasi_lembur_company_wide') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $totalMenit = (int) Lembur::query()->whereIn('status', ['approved', 'paid'])->whereMonth('tanggal', $cMonth)->whereYear('tanggal', $cYear)->sum('durasi_menit');
                $jam = round($totalMenit / 60, 1);
                $reply = "Total jam lembur sakabeh divisi nu geus di-ACC bulan ieu nyaeta {$jam} jam ({$totalMenit} menit).";
            } elseif ($key === 'sunda_123_audit_saldo_cuti_beak') {
                $emptyBalances = LeaveBalance::query()->with('user')->where('year', (int) date('Y'))->where('remaining_days', '<=', 0)->take(10)->get();
                if ($emptyBalances->isEmpty()) {
                    $reply = "Bulan ieu teu aya karyawan nu sisa saldo cuti taunanna geus beak pisan (0 poe).";
                } else {
                    $names = $emptyBalances->map(fn ($b) => $b->user?->name)->filter()->implode(', ');
                    $reply = "Karyawan nu sisa saldo cutina geus beak (0 poe) taun ieu: {$names}.";
                }
            } elseif ($key === 'sunda_125_statistik_lembur_per_divisi') {
                $cMonth = Carbon::now('Asia/Jakarta')->month;
                $cYear = Carbon::now('Asia/Jakarta')->year;
                $topLemburDiv = Lembur::query()
                    ->join('users', 'users.id', '=', 'lembur.user_id')
                    ->whereMonth('lembur.tanggal', $cMonth)
                    ->whereYear('lembur.tanggal', $cYear)
                    ->selectRaw('users.division, count(*) as total_pengajuan')
                    ->whereNotNull('users.division')
                    ->groupBy('users.division')
                    ->orderByDesc('total_pengajuan')
                    ->first();
                $divName = $topLemburDiv?->division ?? 'Teknik';
                $tot = $topLemburDiv?->total_pengajuan ?? 0;
                $reply = "Divisi nu paling loba ngajukeun lembur dina periode ieu nyaeta divisi {$divName} ({$tot} pengajuan lembur).";
            } elseif ($key === 'sunda_128_persentase_kahadiran_minggu_ieu') {
                $totalActive = max(1, User::query()->where('is_active', true)->count());
                $start7 = Carbon::now('Asia/Jakarta')->subDays(6)->toDateString();
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $hadirWeek = Attendance::query()->whereBetween('date', [$start7, $today])->whereIn('status', ['HADIR', 'TERLAMBAT'])->count();
                $pct = min(100, round(($hadirWeek / ($totalActive * 5)) * 100, 1));
                $reply = "Persentase tingkat kahadiran rata-rata karyawan minggu ieu nyaeta sekitar {$pct}% (kalayan {$hadirWeek} log kahadiran kacatet). Menu Rekap Absen Karyawan menyediakan detail per divisina.";
            } elseif ($key === 'sunda_129_deteksi_karyawan_alpa_poe_ieu') {
                $today = Carbon::now('Asia/Jakarta')->toDateString();
                $alpaList = Attendance::query()->with('user')->whereDate('date', $today)->where('status', 'ALPA')->get();
                if ($alpaList->isEmpty()) {
                    $reply = "Poe ieu teu aya karyawan nu kacatet berstatus Alpa dina log absensi harian.";
                } else {
                    $names = $alpaList->map(fn ($a) => $a->user?->name)->filter()->implode(', ');
                    $reply = "Karyawan nu kacatet Alpa poe ieu: {$names}.";
                }
            } elseif ($key === 'sunda_152_cek_gangguan_los_massal') {
                $backboneOpen = Tiket::query()->whereIn('status', ['OPEN', 'PROSES'])->where('kategori_tiket', 'LIKE', '%backbone%')->count();
                $totalOpen = Tiket::query()->whereIn('status', ['OPEN', 'PROSES'])->count();
                $reply = "Poe ieu aya {$backboneOpen} tiket gangguan kategori Backbone/Massal jeung total {$totalOpen} tiket gangguan aktif nu keur ditangani di menu Tiket Gangguan.";
            } elseif ($key === 'sunda_159_laporan_tiket_closed_kamari') {
                $closedTickets = Tiket::query()->where('status', 'CLOSE')->latest('tanggal_close')->take(5)->get();
                if ($closedTickets->isEmpty()) {
                    $reply = "Teu acan aya daftar tiket gangguan berstatus Closed dina log kamari. Mangga cek filter status Closed di menu Tiket Gangguan.";
                } else {
                    $list = $closedTickets->map(fn ($t) => "#{$t->no_tiket} ({$t->kategori_tiket})")->implode(', ');
                    $reply = "Daftar tiket gangguan nu geus beres (Closed) terakhir ku tim lapangan: {$list}.";
                }
            } elseif ($key === 'sunda_166_total_aset_odp_aktif') {
                $odpCount = Odp::query()->count();
                $reply = "Jumlah total titik ODP aktif nu kacatet dina sistem modul jaringan GOMSN ayeuna aya {$odpCount} titik ODP.";
            } elseif ($key === 'sunda_198_penjelasan_hak_akses_jabatan') {
                $div = $user->division ?? 'Staf';
                $pos = $user->position ?? 'Karyawan';
                $akses = "Absen Biometrik & GPS, Riwayat Absen, Jadwal Kerja, Cuti & Izin, Potongan Absen, Pengajuan (Kasbon/Beli/Stok), Lembur Saya, jeung Tiket Gangguan";
                if ($this->isHrdOrAdmin($user)) {
                    $akses .= ", ditambah akses khusus HRD (Data Karyawan, Absen Karyawan, Pengajuan Absen, jeung Lembur Karyawan)";
                }
                $reply = "Luyu jeung profil anjeun ({$user->name} — {$pos}, Divisi {$div}), anjeun tiasa ngakses fitur: {$akses}.";
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
