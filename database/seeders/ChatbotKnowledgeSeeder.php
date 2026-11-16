<?php

namespace Database\Seeders;

use App\Models\ChatbotIntent;
use Illuminate\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    public const KNOWLEDGE_VERSION = 'v3.0';

    /**
     * Get the complete array of built-in chatbot intents with specific topics,
     * high-precision keywords, and action links.
     *
     * @return list<array{intent_key: string, category: string, keywords: list<string>, response_text: string}>
     */
    public static function getDefaultIntents(): array
    {
        return [
            // ==========================================
            // 0. SAPAAN, PERKENALAN & BANTUAN UMUM
            // ==========================================
            [
                'intent_key' => 'sapaan_halo',
                'category' => 'umum',
                'keywords' => ['halo', 'hai', 'hi', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'assalamualaikum', 'permisi', 'ping', 'tes'],
                'response_text' => "Halo! 👋 Saya Asisten Pintar Portal Karyawan & Operasional PT MSN.\n\nSaya memahami seluruh alur kerja di aplikasi ini. Anda bisa bertanya langsung dengan bahasa sehari-hari, misalnya:\n• \"Gimana cara absen masuk dan pulang?\"\n• \"Bagaimana cara mengajukan dana atau barang?\"\n• \"Bagaimana cara membuat pengajuan lembur?\"\n• \"Apakah cuti saya masih ada?\"\n• \"Bagaimana cara mengajukan sakit?\"\n• \"Bagaimana cara approve pengajuan cuti dari karyawan?\"\n• \"Bagaimana jika absen wajah tidak bisa diverifikasi?\"",
            ],
            [
                'intent_key' => 'ucapan_terima_kasih',
                'category' => 'umum',
                'keywords' => ['terima kasih', 'makasih', 'thanks', 'thank you', 'nuhun', 'mantap', 'oke siap', 'ok makasih'],
                'response_text' => "Sama-sama! Senang bisa membantu Anda. 😊 Jika ada hal lain terkait absensi, pengajuan dana, lembur, cuti, jadwal, atau tiketing yang ingin ditanyakan, silakan ketik kapan saja ya!",
            ],
            [
                'intent_key' => 'siapa_kamu_menu',
                'category' => 'umum',
                'keywords' => ['siapa kamu', 'kamu siapa', 'bisa bantu apa', 'fitur apa saja', 'menu apa saja', 'panduan aplikasi', 'cara pakai aplikasi', 'bantuan aplikasi', 'info aplikasi'],
                'response_text' => "Saya adalah Asisten Virtual Resmi PT Media Cepat Indonesia (MSN).\n\nBerikut modul utama di aplikasi ini yang bisa saya bantu jelaskan & arahkan:\n1. 📍 Absensi & Biometrik (Clock-in wajah, GPS radius 50m, Koreksi Absen)\n2. 📅 Jadwal Kerja & Shift (Tukar shift, Backup shift, Aturan jeda 11 jam)\n3. 🌴 Cuti & Izin (Cek sisa cuti, Ajukan cuti/sakit, Approval cuti)\n4. ⏰ Lembur / Overtime (Buat pengajuan lembur, Approval Atasan & Pencairan Finance)\n5. 💰 Pengajuan Operasional GOMSN (Kasbon Dana, Beli Barang, Stok Gudang)\n6. 🎫 Tiketing Gangguan & KPI Teknisi (Backbone BDG, Broadband BRD, Stop Clock)\n7. 🌐 Jaringan FTTH (Data OLT, PON, ODP & Pelanggan)\n8. 💵 Keuangan Absen & Leaderboard Kedisiplinan",
            ],

            // ==========================================
            // 1. ABSENSI & BIOMETRIK
            // ==========================================
            [
                'intent_key' => 'status_absen_hari_ini',
                'category' => 'absensi',
                'keywords' => [
                    'apakah saya sudah absen', 'sudah absen hari ini', 'status absen hari ini',
                    'presensi hari ini', 'cek absen hari ini', 'absen saya hari ini',
                    'jam berapa saya absen', 'saya sudah clock in belum',
                ],
                'response_text' => 'Status kehadiran hari ini dapat Anda cek langsung di halaman Absen.',
            ],
            [
                'intent_key' => 'alur_cara_absen',
                'category' => 'absensi',
                'keywords' => [
                    'cara absen masuk', 'cara absen pulang', 'alur absen', 'tata cara absen',
                    'cara melakukan absen', 'cara presensi', 'cara clock in', 'cara clock out',
                    'bagaimana cara absen',
                ],
                'response_text' => "Alur Absensi Masuk & Pulang:\n1. Buka halaman 'Absen'.\n2. Pastikan izin lokasi GPS aktif dan Anda berada di dalam radius kantor (maks. 50 meter).\n3. Absen Masuk: Klik 'Absen Masuk Sekarang', nyalakan kamera, posisikan wajah di tengah lingkaran hingga terdeteksi cocok (%), lalu klik 'Verifikasi Wajah & Absen Masuk'. Saat berhasil akan terdengar bunyi 'Ting!'.\n4. Absen Pulang: Klik tombol 'Absen Pulang Sekarang' setelah jam kerja selesai.",
            ],
            [
                'intent_key' => 'liveness_wajah',
                'category' => 'absensi',
                'keywords' => [
                    'absen tidak bisa diverifikasi', 'absen tidak bisa di verifikasi', 'absen tidak bisa di vertifikasi',
                    'tidak bisa verifikasi', 'tidak bisa vertifikasi', 'gagal verifikasi', 'gagal vertifikasi',
                    'wajah tidak cocok', 'wajah tidak terdeteksi', 'verifikasi wajah gagal', 'biometrik gagal',
                    'kamera absen error', 'selfie absen gagal', 'wajah belum terdaftar', 'reset wajah',
                ],
                'response_text' => "Solusi Jika Absen / Wajah Tidak Bisa Diverifikasi:\n1. Pastikan pencahayaan ruangan terang (tidak membelakangi cahaya lampu/matahari) dan wajah menghadap lurus ke kamera.\n2. Lepaskan masker, topi, atau kacamata hitam yang menutupi area wajah.\n3. Pastikan GPS perangkat Anda aktif dan berada di dalam titik radius kantor.\n4. Jika wajah tetap tidak cocok atau belum terdaftar, hubungi Admin/HRD untuk melakukan Reset / Pendaftaran Ulang Wajah di menu 'Data Karyawan'.\n5. Jika terkendala darurat sehingga terlewat jam absen, Anda dapat mengajukan 'Koreksi Absensi' dengan melampirkan bukti kehadiran.",
            ],
            [
                'intent_key' => 'radius_geofencing',
                'category' => 'absensi',
                'keywords' => [
                    'radius kantor', 'geofence', 'jarak kantor', 'lokasi kantor',
                    'di luar radius', 'gps tidak akurat', 'lokasi tidak sesuai', 'jarak absen', 'titik koordinat',
                ],
                'response_text' => "Aturan Radius Lokasi (Geofencing) Absensi:\n• Batas radius absensi kantor adalah 50 meter dari titik koordinat kantor terdaftar (Soreang / Bandung).\n• Jika muncul peringatan di luar radius padahal sudah di kantor, aktifkan mode Akurasi Tinggi (High Accuracy GPS) pada pengaturan lokasi HP/Browser Anda lalu muat ulang halaman Absen.",
            ],
            [
                'intent_key' => 'koreksi_absensi',
                'category' => 'absensi',
                'keywords' => [
                    'koreksi absen', 'lupa absen', 'salah absen', 'ajukan koreksi absen',
                    'ubah jam absen', 'lupa clock in', 'lupa clock out', 'absen manual',
                    'cara pengajuan absen', 'mengajukan koreksi absen',
                ],
                'response_text' => "Cara Mengajukan Koreksi Absen (Jika Lupa Absen / Terkendala Teknis):\n1. Buka menu 'Keuangan Absen' atau 'Riwayat & Laporan'.\n2. Pilih tanggal kehadiran yang ingin dikoreksi.\n3. Masukkan jam masuk/pulang usulan yang sebenarnya, tulis alasan yang jelas, dan unggah foto bukti pendukung.\n4. Pengajuan Anda akan diteruskan ke menu 'Pengajuan Absen' untuk disetujui oleh Atasan Divisi atau Admin.",
            ],
            [
                'intent_key' => 'approval_pengajuan_absen',
                'category' => 'absensi',
                'keywords' => [
                    'approve pengajuan absen', 'approve koreksi absen', 'persetujuan absen',
                    'siapa approve absen', 'cara approve absen', 'menyetujui pengajuan absen',
                    'approval koreksi absensi', 'acc pengajuan absen',
                ],
                'response_text' => "Cara Approve Pengajuan Koreksi Absen (Khusus Atasan & Admin):\n1. Buka menu 'Pengajuan Absen' di sidebar.\n2. Tinjau daftar pengajuan koreksi kehadiran dari anggota divisi Anda (lihat jam asal, jam usulan, alasan, serta bukti lampiran).\n3. Klik tombol 'Setujui & Perbarui Absen' untuk menyetujui (data absensi karyawan akan otomatis diperbarui), atau klik 'Tolak Pengajuan' disertai alasan penolakan.",
            ],

            // ==========================================
            // 2. JADWAL KERJA & SHIFT
            // ==========================================
            [
                'intent_key' => 'tukar_shift_aturan',
                'category' => 'jadwal',
                'keywords' => [
                    'tukar shift', 'tukar jadwal', 'swap jadwal', 'swap shift',
                    'ganti shift', 'cara tukar jadwal', 'pindah shift',
                ],
                'response_text' => "Cara Tukar Jadwal / Shift Kerja:\n1. Buka halaman 'Jadwal Kerja'.\n2. Pada baris tanggal yang akan datang (masa depan), klik tombol ikon panah bolak-balik (⇄) di kolom Aksi.\n3. Pilih opsi 'Tukar', tentukan tanggal shift target dan pilih rekan kerja dari divisi yang sama.\n4. Permintaan akan diproses setelah disetujui oleh rekan yang bersangkutan dan disahkan oleh Atasan.\n*Catatan: Tanggal yang sudah lewat tidak dapat ditukar, dan khusus role Finance tidak terdapat fitur tukar/backup jadwal.",
            ],
            [
                'intent_key' => 'backup_shift_aturan',
                'category' => 'jadwal',
                'keywords' => [
                    'backup shift', 'backup jadwal', 'gantikan shift', 'cara backup jadwal', 'minta backup shift',
                ],
                'response_text' => "Cara Backup Jadwal Shift:\n1. Buka halaman 'Jadwal Kerja', lalu klik tombol ikon (⇄) pada tanggal yang ingin di-backup.\n2. Pilih opsi 'Backup' dan pilih rekan kerja satu divisi yang akan menggantikan di tanggal yang sama.\n3. Setelah rekan pengganti dan Atasan menyetujui di menu Permintaan Tukar Shift, jadwal otomatis diperbarui.",
            ],
            [
                'intent_key' => 'aturan_jeda_shift',
                'category' => 'jadwal',
                'keywords' => [
                    'jeda shift', '11 jam', 'istirahat shift', 'aturan jeda',
                ],
                'response_text' => "Aturan Jeda Shift 11 Jam:\nDemi keselamatan dan kesehatan kerja, sistem mewajibkan jeda waktu istirahat minimal 11 jam di antara dua jadwal shift kerja berturut-turut.",
            ],

            // ==========================================
            // 3. CUTI, IZIN & SAKIT
            // ==========================================
            [
                'intent_key' => 'sisa_cuti',
                'category' => 'cuti',
                'keywords' => [
                    'sisa cuti', 'kuota cuti', 'saldo cuti', 'berapa cuti saya',
                    'jatah cuti', 'cek cuti', 'apakah cuti saya masih ada',
                    'cuti saya masih ada', 'cuti saya tinggal berapa', 'masih punya cuti',
                ],
                'response_text' => 'Anda dapat mengecek sisa kuota cuti tahunan Anda secara langsung di halaman Cuti & Izin.',
            ],
            [
                'intent_key' => 'cara_ajukan_sakit',
                'category' => 'cuti',
                'keywords' => [
                    'mengajukan sakit', 'ajukan sakit', 'izin sakit', 'cuti sakit',
                    'cara izin sakit', 'kalau sakit gimana', 'lapor sakit', 'surat dokter',
                    'sedang sakit', 'tidak masuk karena sakit',
                ],
                'response_text' => "Cara Mengajukan Izin / Cuti Sakit:\n1. Buka halaman 'Cuti & Izin' di sidebar.\n2. Pada formulir pengajuan, pilih Jenis Pengajuan: 'Sakit (Dengan Surat Dokter)'.\n3. Pilih tanggal mulai dan tanggal selesai istirahat sakit.\n4. Tuliskan keterangan kondisi sakit Anda dan unggah foto Surat Keterangan Dokter (SKD).\n5. Klik 'Kirim Pengajuan'. Pengajuan sakit akan ditinjau oleh Atasan dan HRD tanpa memotong kuota cuti tahunan Anda.",
            ],
            [
                'intent_key' => 'cara_ajukan_cuti',
                'category' => 'cuti',
                'keywords' => [
                    'cara mengajukan cuti', 'ajukan cuti', 'cara cuti', 'izin cuti',
                    'form cuti', 'syarat cuti', 'alur cuti', 'minta cuti',
                    'cuti tahunan', 'izin tidak masuk kerja',
                ],
                'response_text' => "Cara Mengajukan Cuti Tahunan / Izin:\n1. Buka halaman 'Cuti & Izin' di sidebar.\n2. Pilih tipe cuti (Cuti Tahunan atau Izin Khusus), tentukan tanggal mulai & selesai, serta isi alasan pengajuan.\n3. Klik tombol 'Ajukan Cuti'.\n4. Alur Persetujuan: Pengajuan akan masuk ke tahap Persetujuan Atasan Langsung, lalu disahkan oleh HRD.",
            ],
            [
                'intent_key' => 'cara_approve_cuti',
                'category' => 'cuti',
                'keywords' => [
                    'approve pengajuan cuti', 'approve cuti', 'menyetujui cuti',
                    'persetujuan cuti', 'acc cuti karyawan', 'cara setujui cuti',
                    'verifikasi cuti', 'tolak cuti karyawan',
                ],
                'response_text' => "Cara Approve Pengajuan Cuti Karyawan (Atasan & HRD):\n1. Buka halaman 'Cuti & Izin' di sidebar.\n2. Gulir ke bagian tabel 'Daftar Pengajuan Cuti Karyawan / Menunggu Persetujuan'.\n3. Periksa detail tanggal, jenis cuti/sakit, alasan, dan lampiran surat dokter (jika ada).\n4. Klik tombol 'Setujui (Approve)' untuk menyetujui tahap Atasan/HRD, atau klik 'Tolak' jika permohonan belum dapat diberikan.",
            ],

            // ==========================================
            // 4. LEMBUR (OVERTIME)
            // ==========================================
            [
                'intent_key' => 'cara_ajukan_lembur',
                'category' => 'lembur',
                'keywords' => [
                    'membuat pengajuan lembur', 'buat pengajuan lembur', 'cara mengajukan lembur',
                    'ajukan lembur', 'cara lembur', 'klaim lembur', 'form lembur',
                    'alur lembur', 'buat lembur', 'input lembur', 'cara klaim uang lembur',
                ],
                'response_text' => "Cara Membuat Pengajuan Lembur (Overtime):\n1. Buka halaman 'Lembur' di sidebar, lalu klik tombol '+ Buat Pengajuan Lembur'.\n2. Pilih Tanggal Lembur, masukkan Jam Mulai dan Jam Selesai lembur.\n3. Tuliskan uraian pekerjaan/tugas yang dikerjakan saat lembur secara rinci.\n4. Unggah foto bukti pekerjaan lembur, lalu klik 'Simpan & Ajukan'.\n5. Pengajuan akan diperiksa dan di-approve oleh Atasan Divisi, kemudian diverifikasi oleh Finance untuk pencairan.",
            ],
            [
                'intent_key' => 'cara_approve_lembur',
                'category' => 'lembur',
                'keywords' => [
                    'approve lembur', 'persetujuan lembur', 'menyetujui lembur',
                    'acc lembur karyawan', 'verifikasi lembur', 'cara approve pengajuan lembur',
                ],
                'response_text' => "Cara Approve Pengajuan Lembur Karyawan (Atasan & Finance):\n1. Buka menu 'Persetujuan Lembur' di sidebar.\n2. Klik karyawan atau tinjau daftar pengajuan lembur yang berstatus 'Menunggu Persetujuan'.\n3. Klik tombol 'Detail' untuk melihat jam kerja, uraian tugas, dan foto bukti lembur.\n4. Bagi Atasan: Klik 'Setujui' untuk mengesahkan lembur.\n5. Bagi Finance: Klik tombol verifikasi/pencairan setelah disetujui Atasan.",
            ],
            [
                'intent_key' => 'pencairan_lembur',
                'category' => 'lembur',
                'keywords' => [
                    'bayar lembur', 'cair lembur', 'uang lembur kapan cair',
                    'kapan lembur dibayar', 'gaji lembur', 'hitungan lembur',
                ],
                'response_text' => "Pencairan Uang Lembur:\nPengajuan lembur yang telah berstatus 'Disetujui Atasan' akan diverifikasi oleh bagian Finance dan dibayarkan bersama rekapitulasi tutup buku bulanan.",
            ],

            // ==========================================
            // 5. GOMSN PENGAJUAN OPERASIONAL (DANA, BELI, STOK)
            // ==========================================
            [
                'intent_key' => 'alur_pengajuan_operasional',
                'category' => 'gomsn',
                'keywords' => [
                    'cara mengajukan dana', 'ajukan dana', 'pengajuan dana', 'alur pengajuan dana',
                    'minta dana operasional', 'kasbon operasional', 'reimburse dana', 'klaim dana',
                    'cara mengajukan barang', 'pengajuan barang', 'beli barang operasional', 'pengadaan barang',
                    'ambil stok gudang', 'permintaan barang gudang', 'buat pengajuan dana',
                ],
                'response_text' => "Cara Mengajukan Dana & Barang Operasional:\n1. Buka menu 'Pengajuan Saya' di sidebar, lalu klik tombol '+ Buat Pengajuan Baru'.\n2. Pilih Jenis Pengajuan:\n   • DANA: Permohonan dana operasional / kasbon.\n   • BELI: Pengadaan pembelian barang baru.\n   • STOK: Permintaan pengeluaran barang dari stok gudang.\n3. Isi rincian item, jumlah, estimasi harga satuan, dan keterangan keperluan.\n4. Alur Persetujuan:\n   • Tahap 1: Disetujui oleh Atasan Divisi.\n   • Tahap 2: Otorisasi Direksi/Eksekutif (jika nominal melebihi ambang batas).\n   • Tahap 3: Pencairan oleh Finance (metode Transfer atau Cash) hingga status otomatis SELESAI.",
            ],
            [
                'intent_key' => 'tahapan_approval_pengajuan',
                'category' => 'gomsn',
                'keywords' => [
                    'approve pengajuan dana', 'cara approve pengajuan karyawan', 'persetujuan karyawan',
                    'siapa approve pengajuan dana', 'pencairan finance', 'cara mencairkan dana',
                    'metode pencairan transfer cash', 'acc pengajuan dana',
                ],
                'response_text' => "Cara Approve & Pencairan Pengajuan Operasional:\n1. Untuk Atasan Divisi: Buka menu 'Persetujuan Karyawan', klik 'Detail' pada pengajuan bawahan, lalu klik 'Setujui Pengajuan'.\n2. Untuk Direksi/Eksekutif: Menyetujui pengajuan yang membutuhkan otorisasi nominal besar.\n3. Untuk Finance: Buka daftar pengajuan berstatus 'Menunggu Finance', klik 'Detail', pilih metode pencairan 'Transfer' (upload bukti opsional) atau 'Cash' (tanpa upload bukti), lalu konfirmasi pencairan. Status otomatis menjadi SELESAI.",
            ],
            [
                'intent_key' => 'status_pengajuan_saya',
                'category' => 'gomsn',
                'keywords' => [
                    'status pengajuan saya', 'cek pengajuan saya', 'pengajuan dana saya sudah sampai mana',
                    'riwayat pengajuan saya', 'apakah pengajuan saya sudah disetujui',
                ],
                'response_text' => 'Anda dapat memeriksa status pengajuan dana/barang terbaru Anda di halaman Pengajuan Saya.',
            ],
            [
                'intent_key' => 'export_excel_pengajuan',
                'category' => 'gomsn',
                'keywords' => [
                    'export excel pengajuan', 'download laporan pengajuan', 'rekap pengajuan excel', 'unduh excel pengajuan',
                ],
                'response_text' => "Fitur Export Excel Pengajuan:\n• Klik tombol hijau 'Export Excel' pada halaman Pengajuan.\n• File Excel yang diunduh secara otomatis hanya berisi daftar pengajuan yang sudah disetujui/selesai sesuai periode bulan dan tahun yang difilter.",
            ],
            [
                'intent_key' => 'stok_barang_gudang',
                'category' => 'gomsn',
                'keywords' => [
                    'stok barang', 'material gudang', 'stok habis', 'min stok',
                    'sisa material', 'inventori gudang', 'cek stok ont kabel',
                ],
                'response_text' => "Manajemen Stok Barang Gudang:\n• Buka menu 'Stok Barang' di sidebar.\n• Menampilkan sisa stok material (ONT, Kabel Dropcore, Patchcord, Adaptor, Closure, dll).\n• Finance dan Admin dapat melakukan penambahan stok (Restock), penyesuaian (Adjust), serta melihat riwayat keluar-masuk barang.",
            ],

            // ==========================================
            // 6. TIKETING & GANGGUAN JARINGAN
            // ==========================================
            [
                'intent_key' => 'tiket_aktif_saya',
                'category' => 'tiket',
                'keywords' => [
                    'tiket aktif saya', 'tiket saya', 'tugas tiket saya',
                    'tiket gangguan saya', 'tiket yang belum selesai',
                ],
                'response_text' => 'Tiket gangguan aktif yang ditugaskan kepada Anda dapat dilihat pada halaman Tiketing.',
            ],
            [
                'intent_key' => 'buka_tiket_gangguan',
                'category' => 'tiket',
                'keywords' => [
                    'buka tiket', 'buat tiket gangguan', 'lapor gangguan', 'cara buat tiket',
                    'tiket baru', 'input tiket', 'tiket backbone', 'tiket broadband',
                ],
                'response_text' => "Cara Membuat & Menangani Tiket Gangguan:\n1. Buka halaman 'Tiketing', lalu klik '+ Buat Tiket Baru'.\n2. Pilih kategori tiket:\n   • Backbone (Kode BDG-): Gangguan infrastruktur jalur utama/distribusi.\n   • Broadband (Kode BRD-): Gangguan pelanggan FTTH/rumahan.\n3. Tentukan prioritas SLA (P1 s/d P4) dan pilih teknisi yang ditugaskan.\n4. Teknisi wajib mengisi update kronologi berkala hingga perbaikan selesai (Closing Tiket).",
            ],
            [
                'intent_key' => 'stop_clock_aturan',
                'category' => 'tiket',
                'keywords' => [
                    'stop clock', 'jeda sla', 'pause tiket', 'sla tiket', 'pending tiket gangguan',
                ],
                'response_text' => "Fitur Stop Clock pada Tiketing:\nDigunakan untuk menjeda perhitungan waktu SLA sementara ketika penanganan di lapangan terkendala faktor eksternal (seperti hujan lebat/petir, kendala izin akses lokasi, atau pemadaman listrik PLN).",
            ],
            [
                'intent_key' => 'kpi_teknisi',
                'category' => 'tiket',
                'keywords' => [
                    'kpi teknisi', 'penilaian teknisi', 'skor kpi', 'bobot nilai kpi', 'performa teknisi', 'analitik mttr',
                ],
                'response_text' => "Perhitungan Skor KPI Teknisi (0 - 100):\n• 45% Ketepatan penyelesaian tiket sebelum target SLA berakhir.\n• 35% Kepatuhan update kronologi progress setiap 30 menit.\n• 20% Kecepatan respon awal saat tiket ditugaskan.",
            ],

            // ==========================================
            // 7. JARINGAN FIBER OPTIK (OLT, PON, ODP)
            // ==========================================
            [
                'intent_key' => 'manajemen_jaringan_odp',
                'category' => 'jaringan',
                'keywords' => [
                    'olt', 'pon', 'odp', 'jaringan fiber', 'port odp',
                    'titik odp', 'kapasitas odp', 'data pelanggan odp',
                ],
                'response_text' => "Manajemen Jaringan OLT, PON & ODP:\n• Klik profil di kanan atas -> pilih 'Jaringan OLT, PON & ODP'.\n• Menampilkan daftar perangkat OLT, port PON, kapasitas port ODP (terpakai vs tersedia), titik lokasi Google Maps ODP, dan daftar pelanggan yang terhubung.",
            ],

            // ==========================================
            // 8. GAJI, POTONGAN & REKAPITULASI
            // ==========================================
            [
                'intent_key' => 'potongan_keuangan_absen',
                'category' => 'keuangan',
                'keywords' => [
                    'potongan gaji', 'potongan absen', 'denda telat', 'potongan alpa',
                    'keuangan absen', 'terlambat potong berapa', 'potongan keterlambatan',
                ],
                'response_text' => "Informasi Keuangan & Potongan Absen:\n• Buka menu 'Keuangan Absen' di sidebar.\n• Menampilkan transparansi rincian menit keterlambatan, ketidakhadiran (alpa), dan rekapitulasi potongan berjalan pada bulan aktif.",
            ],
            [
                'intent_key' => 'leaderboard_karyawan',
                'category' => 'hris',
                'keywords' => [
                    'leaderboard', 'peringkat hadir', 'karyawan teladan',
                    'top absensi', 'juara absen', 'ranking karyawan',
                ],
                'response_text' => "Leaderboard Kehadiran Karyawan:\nMenampilkan peringkat kedisiplinan karyawan PT MSN berdasarkan persentase kehadiran tertinggi, ketepatan waktu clock-in, dan konsistensi bulanan.",
            ],

            // ==========================================
            // 9. AKUN, PROFIL, DATA KARYAWAN & BANTUAN
            // ==========================================
            [
                'intent_key' => 'ganti_foto_profil',
                'category' => 'profil',
                'keywords' => [
                    'ganti foto profil', 'ubah foto profil', 'upload foto profil',
                    'cara ganti avatar', 'edit profil', 'ubah password', 'ganti password',
                ],
                'response_text' => "Pengaturan Profile & Password:\n• Buka halaman 'Profile' dari menu dropdown di pojok kanan atas untuk memperbarui biodata, mendaftarkan biometrik sidik jari, atau mengganti password.\n• Khusus perubahan Foto Profil Karyawan hanya dapat diubah oleh Admin melalui menu 'Data Karyawan'.",
            ],
            [
                'intent_key' => 'manajemen_data_karyawan',
                'category' => 'profil',
                'keywords' => [
                    'data karyawan', 'tambah karyawan', 'reset password karyawan',
                    'reset wajah karyawan', 'kelola karyawan', 'daftar karyawan',
                ],
                'response_text' => "Manajemen Data Karyawan (Khusus Admin & HRD):\n• Buka menu 'Data Karyawan' di sidebar.\n• Digunakan untuk menambah akun karyawan baru, mengubah divisi/jabatan, mengganti foto profil karyawan, mereset password, serta mereset template biometrik wajah.",
            ],
            [
                'intent_key' => 'cara_login_sidik_jari',
                'category' => 'profil',
                'keywords' => [
                    'sidik jari', 'fingerprint', 'biometrik login', 'passkey', 'webauthn', 'cara daftar sidik jari',
                ],
                'response_text' => "Cara Mengaktifkan Login Sidik Jari / Biometrik (WebAuthn):\n1. Buka halaman 'Profile' dari pojok kanan atas.\n2. Gulir ke bagian 'Keamanan & Biometrik Perangkat', lalu klik 'Daftarkan Sidik Jari / Face ID'.\n3. Setelah terdaftar, tombol login sidik jari akan otomatis tampil di halaman Login perangkat Anda.",
            ],
            [
                'intent_key' => 'kontak_bantuan_helpdesk',
                'category' => 'bantuan',
                'keywords' => [
                    'kontak helpdesk', 'nomor noc', 'hubungi admin', 'nomor whatsapp noc', 'kontak hrd',
                ],
                'response_text' => "Kontak Bantuan Internal PT MSN:\n• Helpdesk NOC: WhatsApp 08112000006 / noc@msn.co.id\n• HRD & Personalia: hrd@msn.co.id\n• Admin Sistem: Hubungi Divisi IT & Sistem PT MSN.",
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::getDefaultIntents() as $in) {
            ChatbotIntent::query()->updateOrCreate(
                ['intent_key' => $in['intent_key']],
                array_merge($in, ['is_active' => true])
            );
        }
    }
}
