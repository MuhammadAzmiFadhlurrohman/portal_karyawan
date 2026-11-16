<?php

namespace Database\Seeders;

use App\Models\ChatbotIntent;
use Illuminate\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi pengetahuan lengkap chatbot untuk seluruh alur aplikasi Portal Karyawan MSN & GOMSN.
     */
    public function run(): void
    {
        $intents = [
            // ==========================================
            // 1. ABSENSI & BIOMETRIK
            // ==========================================
            [
                'intent_key' => 'status_absen_hari_ini',
                'category' => 'absensi',
                'keywords' => ['sudah absen', 'status absen', 'absen hari ini', 'clock in hari ini', 'presensi hari ini', 'cek absen', 'apakah saya sudah absen'],
                'response_text' => 'Status kehadiran hari ini dapat Anda cek langsung di menu Absensi & Biometrik.',
            ],
            [
                'intent_key' => 'alur_cara_absen',
                'category' => 'absensi',
                'keywords' => ['cara absen', 'alur absen', 'cara presensi', 'absen masuk', 'absen pulang', 'clock in', 'clock out', 'tata cara absen'],
                'response_text' => "Alur Absensi Karyawan:\n1. Buka menu 'Absen' di sidebar.\n2. Pastikan GPS aktif dan berada dalam radius kantor (geofencing).\n3. Untuk Absen Masuk: Nyalakan kamera, hadapkan wajah ke tengah lingkaran hingga terdeteksi cocok dengan data biometrik, lalu klik 'Verifikasi Wajah & Absen Masuk' (akan berbunyi nada Ting saat sukses).\n4. Untuk Absen Pulang: Cukup klik 'Absen Pulang Sekarang' dan konfirmasi.",
            ],
            [
                'intent_key' => 'radius_geofencing',
                'category' => 'absensi',
                'keywords' => ['radius', 'geofence', 'jarak', 'lokasi kantor', 'titik koordinat', 'di luar radius', 'gps'],
                'response_text' => "Aturan Geofencing Absensi:\nAbsensi tipe Office memiliki batas radius toleransi 50 meter dari titik koordinat kantor terdaftar (Kantor Soreang / Bandung). Pastikan izin lokasi GPS di browser/ponsel Anda diaktifkan (High Accuracy).",
            ],
            [
                'intent_key' => 'liveness_wajah',
                'category' => 'absensi',
                'keywords' => ['verifikasi wajah', 'wajah tidak cocok', 'liveness', 'biometrik gagal', 'kamera absen', 'daftar wajah'],
                'response_text' => "Verifikasi Wajah Biometrik:\n1. Pastikan pencahayaan cukup dan wajah tegak lurus ke kamera tanpa masker atau kacamata hitam.\n2. Wajah harus sesuai dengan template yang didaftarkan oleh Admin.\n3. Jika mengalami kendala biometrik berulang, silakan hubungi Admin/HRD untuk reset atau pendaftaran ulang template wajah di menu Data Karyawan.",
            ],
            [
                'intent_key' => 'koreksi_absensi',
                'category' => 'absensi',
                'keywords' => ['koreksi absen', 'pengajuan absen', 'lupa absen', 'salah absen', 'ajukan koreksi', 'ubah jam absen'],
                'response_text' => "Alur Pengajuan Koreksi Absen:\n1. Buka menu 'Riwayat & Laporan' atau menu 'Pengajuan Absen'.\n2. Klik tombol pengajuan koreksi pada tanggal yang ingin disesuaikan.\n3. Masukkan jam masuk/pulang usulan, alasan yang jelas, serta unggah bukti pendukung.\n4. Pengajuan akan ditinjau dan disetujui oleh Atasan divisi Anda atau Admin.",
            ],
            [
                'intent_key' => 'approval_pengajuan_absen',
                'category' => 'absensi',
                'keywords' => ['approve absen', 'persetujuan absen', 'siapa approve absen', 'approval koreksi'],
                'response_text' => "Persetujuan Pengajuan Absen:\nPengajuan koreksi absen disetujui oleh Atasan Divisi masing-masing atau Admin/Super Admin melalui menu 'Pengajuan Absen' di sidebar.",
            ],

            // ==========================================
            // 2. JADWAL KERJA & SHIFT
            // ==========================================
            [
                'intent_key' => 'tukar_shift_aturan',
                'category' => 'jadwal',
                'keywords' => ['tukar shift', 'swap jadwal', 'ganti shift', 'cara tukar jadwal'],
                'response_text' => "Alur Tukar Shift (Jadwal Kerja):\n1. Buka menu 'Jadwal Kerja'.\n2. Pada tanggal mendatang yang ingin ditukar, klik tombol panah ⇄ (Tukar/Backup).\n3. Pilih 'Tukar', tentukan tanggal shift dan pilih rekan satu divisi.\n4. Permintaan tukar akan menunggu konfirmasi dari rekan target dan persetujuan Atasan.\n*Catatan: Fitur tukar shift hanya berlaku untuk divisi lapangan/operasional dan tanggal mendatang.",
            ],
            [
                'intent_key' => 'backup_shift_aturan',
                'category' => 'jadwal',
                'keywords' => ['backup shift', 'backup jadwal', 'gantikan shift', 'cara backup'],
                'response_text' => "Alur Backup Shift:\n1. Buka menu 'Jadwal Kerja'.\n2. Klik tombol panah ⇄ pada tanggal yang ingin di-backup.\n3. Pilih opsi 'Backup' dan tentukan rekan kerja pengganti di tanggal yang sama.\n4. Jadwal akan dialihkan setelah rekan menyetujui dan disahkan oleh Atasan.",
            ],
            [
                'intent_key' => 'aturan_jeda_shift',
                'category' => 'jadwal',
                'keywords' => ['jeda shift', '11 jam', 'istirahat shift', 'aturan jadwal'],
                'response_text' => 'Sistem menerapkan aturan keselamatan kerja dengan jeda waktu istirahat minimum 11 jam di antara dua shift kerja berturut-turut untuk menjaga stamina karyawan.',
            ],

            // ==========================================
            // 3. CUTI & IZIN
            // ==========================================
            [
                'intent_key' => 'sisa_cuti',
                'category' => 'cuti',
                'keywords' => ['sisa cuti', 'kuota cuti', 'saldo cuti', 'berapa cuti saya', 'jatah cuti'],
                'response_text' => 'Sisa kuota cuti tahunan Anda dapat dilihat di menu Cuti & Izin.',
            ],
            [
                'intent_key' => 'cara_ajukan_cuti',
                'category' => 'cuti',
                'keywords' => ['ajukan cuti', 'cara cuti', 'izin cuti', 'form cuti', 'syarat cuti', 'alur cuti'],
                'response_text' => "Alur Pengajuan Cuti:\n1. Buka menu 'Cuti & Izin' di sidebar.\n2. Klik 'Ajukan Cuti Baru'.\n3. Pilih jenis cuti (Tahunan, Sakit, Khusus), tentukan rentang tanggal, alasan, dan unggah surat dokter jika cuti sakit.\n4. Pengajuan cuti diproses dengan persetujuan bertingkat: Atasan Langsung -> HRD.",
            ],

            // ==========================================
            // 4. LEMBUR (OVERTIME)
            // ==========================================
            [
                'intent_key' => 'cara_ajukan_lembur',
                'category' => 'lembur',
                'keywords' => ['ajukan lembur', 'cara lembur', 'klaim lembur', 'form lembur', 'alur lembur', 'persetujuan lembur'],
                'response_text' => "Alur Pengajuan & Klaim Lembur:\n1. Karyawan mengajukan lembur via menu 'Lembur Saya' atau tombol 'Buat Pengajuan Lembur'.\n2. Masukkan tanggal, jam mulai, jam selesai, uraian pekerjaan/tugas, dan unggah foto bukti kerja lembur.\n3. Atasan divisi meninjau dan menyetujui lembur di menu 'Persetujuan Lembur'.\n4. Finance memverifikasi kesesuaian dan mencairkan pembayaran uang lembur pada periode rekapitulasi gaji.",
            ],
            [
                'intent_key' => 'pencairan_lembur',
                'category' => 'lembur',
                'keywords' => ['bayar lembur', 'cair lembur', 'uang lembur', 'kapan lembur dibayar'],
                'response_text' => 'Pembayaran uang lembur diproses oleh Finance setelah lembur disetujui Atasan dan diverifikasi dengan log absensi pada saat cut-off rekapitulasi bulanan.',
            ],

            // ==========================================
            // 5. GOMSN PENGAJUAN OPERASIONAL (DANA, BELI, STOK)
            // ==========================================
            [
                'intent_key' => 'alur_pengajuan_operasional',
                'category' => 'gomsn',
                'keywords' => ['pengajuan dana', 'pengajuan barang', 'kasbon', 'pengadaan', 'stok barang', 'alur pengajuan', 'cara buat pengajuan'],
                'response_text' => "Alur Pengajuan Operasional (GOMSN):\n1. Buat permohonan di menu 'Pengajuan Saya' -> 'Buat Pengajuan Baru'.\n2. Pilih jenis: DANA (kasbon operasional), BELI (pengadaan barang baru), atau STOK (pengambilan barang gudang).\n3. Tahap Persetujuan:\n   - Diapprove oleh Atasan Divisi.\n   - Jika nominal di atas ambang batas standar, memerlukan otorisasi Direksi/Eksekutif.\n   - Finance mencairkan dana (via Transfer atau Cash) dan status langsung SELESAI.\n4. Bukti nota/struk belanja bersifat opsional untuk diunggah sebagai arsip.",
            ],
            [
                'intent_key' => 'tahapan_approval_pengajuan',
                'category' => 'gomsn',
                'keywords' => ['siapa approve pengajuan', 'tahap persetujuan pengajuan', 'approval pengajuan', 'otorisasi dana'],
                'response_text' => "Hierarki Approval Pengajuan Dana:\n1. Atasan Divisi: Melakukan peninjauan teknis & urgensi kebutuhan tim.\n2. Direksi/Eksekutif: Melakukan approval khusus permohonan dengan nominal besar.\n3. Finance: Melakukan validasi final, transfer/penyerahan dana, dan menyelesaikan status pengajuan.",
            ],
            [
                'intent_key' => 'stok_barang_gudang',
                'category' => 'gomsn',
                'keywords' => ['stok barang', 'material gudang', 'stok habis', 'min stok', 'sisa material'],
                'response_text' => "Manajemen Stok Barang:\nDikelola di menu 'Stok Barang'. Menampilkan daftar material kabel, ONT, adaptor, patchcord, dll. Jika stok mendekati atau di bawah batas minimum (min_stok), sistem akan memberikan indikator peringatan untuk restock.",
            ],

            // ==========================================
            // 6. TIKETING & GANGGUAN JARINGAN
            // ==========================================
            [
                'intent_key' => 'tiket_aktif_saya',
                'category' => 'tiket',
                'keywords' => ['tiket aktif', 'tiket saya', 'tiket gangguan', 'tugas tiket', 'daftar tiket'],
                'response_text' => 'Tiket gangguan aktif Anda dapat dilihat di menu Tiketing Gangguan pada status OPEN atau PROSES.',
            ],
            [
                'intent_key' => 'buka_tiket_gangguan',
                'category' => 'tiket',
                'keywords' => ['buka tiket', 'buat tiket', 'lapor gangguan', 'cara buat tiket', 'tiket baru'],
                'response_text' => "Alur Pembuatan Tiket Gangguan:\n1. Buka menu 'Tiketing' -> 'Buat Tiket Baru'.\n2. Pilih kategori: Backbone (kode BDG-) untuk jalur utama atau Broadband (kode BRD-) untuk gangguan pelanggan/FTTH.\n3. Tentukan tingkat urgensi (P1 Urgent hingga P4 Low) dan input deskripsi gangguan.\n4. Helpdesk/NOC mendisposisikan tiket kepada teknisi lapangan.",
            ],
            [
                'intent_key' => 'stop_clock_aturan',
                'category' => 'tiket',
                'keywords' => ['stop clock', 'jeda sla', 'pause tiket', 'sla tiket', 'pending tiket'],
                'response_text' => 'Stop Clock digunakan untuk menjeda perhitungan SLA sementara saat penanganan terhambat kendala di luar kendali teknisi (misal: cuaca ekstrem, perizinan gedung, pemadaman PLN). Stop Clock wajib mencantumkan alasan yang valid.',
            ],
            [
                'intent_key' => 'kpi_teknisi',
                'category' => 'tiket',
                'keywords' => ['kpi teknisi', 'penilaian teknisi', 'skor kpi', 'bobot nilai'],
                'response_text' => "Perhitungan Skor KPI Teknisi:\n1. 45% Ketepatan waktu penyelesaian sesuai target SLA.\n2. 35% Kepatuhan update progress berkala setiap 30 menit.\n3. 20% Kecepatan respon awal saat tiket ditugaskan.",
            ],

            // ==========================================
            // 7. JARINGAN FIBER OPTIK (OLT, PON, ODP)
            // ==========================================
            [
                'intent_key' => 'manajemen_jaringan_odp',
                'category' => 'jaringan',
                'keywords' => ['olt', 'pon', 'odp', 'jaringan fiber', 'port odp', 'titik odp', 'kapasitas odp'],
                'response_text' => "Modul Jaringan FTTH:\nDiakses via menu Profil -> 'Jaringan OLT, PON & ODP'. Digunakan teknisi dan NOC untuk memetakan perangkat OLT, port PON, titik koordinat ODP, kapasitas port terisi vs kosong, serta riwayat pelanggan yang terkoneksi.",
            ],

            // ==========================================
            // 8. GAJI, POTONGAN & REKAPITULASI
            // ==========================================
            [
                'intent_key' => 'potongan_keuangan_absen',
                'category' => 'keuangan',
                'keywords' => ['potongan gaji', 'potongan absen', 'denda telat', 'potongan alpa', 'keuangan absen'],
                'response_text' => "Aturan Potongan Keterlambatan & Alpa:\nSistem menghitung potongan otomatis berdasarkan menit keterlambatan dan status ketidakhadiran (Alpa). Rincian potongan dapat dipantau di menu 'Keuangan Absen' di portal masing-masing karyawan.",
            ],
            [
                'intent_key' => 'leaderboard_karyawan',
                'category' => 'hris',
                'keywords' => ['leaderboard', 'peringkat hadir', 'karyawan teladan', 'top absensi'],
                'response_text' => "Leaderboard Kehadiran:\nMenampilkan pemeringkatan kedisiplinan karyawan berdasarkan persentase kehadiran tertinggi, menit keterlambatan paling minim, dan ketepatan waktu kerja.",
            ],

            // ==========================================
            // 9. AKUN, PROFIL & BANTUAN
            // ==========================================
            [
                'intent_key' => 'ganti_foto_profil',
                'category' => 'profil',
                'keywords' => ['ganti foto', 'ubah foto profil', 'upload foto profil', 'foto profil'],
                'response_text' => 'Untuk menjaga validitas biometrik absensi dan administrasi resmi, perubahan foto profil karyawan hanya dapat dilakukan oleh Admin melalui menu Data Karyawan.',
            ],
            [
                'intent_key' => 'kontak_bantuan_helpdesk',
                'category' => 'bantuan',
                'keywords' => ['kontak', 'helpdesk', 'bantuan', 'noc', 'telepon', 'whatsapp', 'admin kontak'],
                'response_text' => "Kontak Bantuan Internal PT MSN:\n- Helpdesk NOC: WhatsApp 08112000006 / noc@msn.co.id\n- HRD & GA: hrd@msn.co.id\n- Support Portal: Hubungi Administrator IT MSN.",
            ],
        ];

        foreach ($intents as $in) {
            ChatbotIntent::query()->updateOrCreate(['intent_key' => $in['intent_key']], $in);
        }
    }
}
