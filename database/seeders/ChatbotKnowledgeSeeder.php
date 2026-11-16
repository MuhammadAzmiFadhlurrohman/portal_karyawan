<?php

namespace Database\Seeders;

use App\Models\ChatbotIntent;
use Illuminate\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    /**
     * Get the complete array of built-in chatbot intents.
     * Used by both Database Seeder and ChatbotService auto-bootstrapper.
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
                'keywords' => ['halo', 'hai', 'hi', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'assalamualaikum', 'permisi', 'ping', 'tes', 'test', 'p'],
                'response_text' => "Halo! 👋 Saya Asisten Pintar Portal Karyawan & Operasional PT MSN.\n\nSaya memahami seluruh alur kerja di aplikasi ini. Anda bisa bertanya langsung dengan bahasa sehari-hari, misalnya:\n• \"Gimana cara absen masuk dan pulang?\"\n• \"Bagaimana cara mengajukan dana atau barang?\"\n• \"Apakah saya sudah absen hari ini?\"\n• \"Berapa sisa cuti saya?\"\n• \"Gimana cara klaim lembur?\"\n• \"Siapa yang approve pengajuan absen / dana?\"\n• \"Cara tukar atau backup jadwal shift?\"\n• \"Bagaimana cara buka tiket gangguan?\"",
            ],
            [
                'intent_key' => 'ucapan_terima_kasih',
                'category' => 'umum',
                'keywords' => ['terima kasih', 'makasih', 'thanks', 'thank you', 'nuhun', 'mantap', 'oke siap', 'ok makasih', 'sip'],
                'response_text' => "Sama-sama! Senang bisa membantu Anda. 😊 Jika ada hal lain terkait absensi, pengajuan dana, lembur, jadwal, atau tiketing yang ingin ditanyakan, silakan ketik kapan saja ya!",
            ],
            [
                'intent_key' => 'siapa_kamu_menu',
                'category' => 'umum',
                'keywords' => ['siapa kamu', 'kamu siapa', 'bisa bantu apa', 'fitur apa saja', 'menu apa saja', 'panduan aplikasi', 'cara pakai aplikasi', 'bantuan', 'help', 'info aplikasi'],
                'response_text' => "Saya adalah Asisten Virtual Resmi PT Media Cepat Indonesia (MSN).\n\nBerikut modul utama di aplikasi ini yang bisa saya bantu jelaskan:\n1. 📍 Absensi & Biometrik (Clock-in wajah, GPS radius 50m, Koreksi Absen)\n2. 📅 Jadwal Kerja & Shift (Tukar shift, Backup shift, Aturan jeda 11 jam)\n3. 🌴 Cuti & Izin (Cek kuota cuti, Pengajuan cuti tahunan/sakit)\n4. ⏰ Lembur / Overtime (Klaim lembur, Approval Atasan & Pencairan Finance)\n5. 💰 Pengajuan Operasional GOMSN (Kasbon Dana, Beli Barang, Stok Gudang)\n6. 🎫 Tiketing Gangguan & KPI Teknisi (Backbone BDG, Broadband BRD, Stop Clock)\n7. 🌐 Jaringan FTTH (Data OLT, PON, ODP & Pelanggan)\n8. 💵 Keuangan Absen & Leaderboard Kedisiplinan",
            ],

            // ==========================================
            // 1. ABSENSI & BIOMETRIK
            // ==========================================
            [
                'intent_key' => 'status_absen_hari_ini',
                'category' => 'absensi',
                'keywords' => ['sudah absen', 'status absen', 'absen hari ini', 'clock in hari ini', 'presensi hari ini', 'cek absen', 'apakah saya sudah absen', 'absen saya hari ini', 'jam berapa saya absen'],
                'response_text' => 'Status kehadiran hari ini dapat Anda cek langsung di menu Absen.',
            ],
            [
                'intent_key' => 'alur_cara_absen',
                'category' => 'absensi',
                'keywords' => ['cara absen', 'alur absen', 'cara presensi', 'absen masuk', 'absen pulang', 'clock in', 'clock out', 'tata cara absen', 'gimana cara absen', 'bagaimana absen'],
                'response_text' => "Alur Absensi Karyawan:\n1. Buka menu 'Absen' di sidebar.\n2. Pastikan izin lokasi GPS aktif dan Anda berada di dalam radius kantor (maks. 50 meter).\n3. Absen Masuk: Klik 'Absen Masuk Sekarang', nyalakan kamera, posisikan wajah di lingkaran hingga terdeteksi cocok (%), lalu klik 'Verifikasi Wajah & Absen Masuk'. Saat berhasil akan terdengar bunyi 'Ting!'.\n4. Absen Pulang: Klik tombol 'Absen Pulang Sekarang' setelah jam kerja berakhir.",
            ],
            [
                'intent_key' => 'radius_geofencing',
                'category' => 'absensi',
                'keywords' => ['radius', 'geofence', 'jarak kantor', 'lokasi kantor', 'titik koordinat', 'di luar radius', 'gps tidak akurat', 'lokasi tidak sesuai', 'jarak absen'],
                'response_text' => "Aturan Radius Lokasi (Geofencing) Absensi:\n• Batas radius absensi kantor adalah 50 meter dari titik koordinat kantor terdaftar (Soreang / Bandung).\n• Jika muncul peringatan di luar radius, pastikan GPS ponsel/browser dalam mode Akurasi Tinggi (High Accuracy) dan refresh halaman.",
            ],
            [
                'intent_key' => 'liveness_wajah',
                'category' => 'absensi',
                'keywords' => ['verifikasi wajah', 'wajah tidak cocok', 'liveness', 'biometrik', 'kamera absen', 'daftar wajah', 'wajah gagal', 'selfie absen', 'tidak terdeteksi wajah'],
                'response_text' => "Solusi Kendala Verifikasi Wajah (Biometrik):\n1. Pastikan pencahayaan ruangan terang dan wajah menghadap lurus ke kamera.\n2. Lepaskan masker, topi, atau kacamata hitam.\n3. Jika belum mendaftarkan wajah atau wajah tidak kunjung cocok, hubungi Admin untuk mendaftarkan/mereset foto biometrik Anda di menu Data Karyawan.",
            ],
            [
                'intent_key' => 'koreksi_absensi',
                'category' => 'absensi',
                'keywords' => ['koreksi absen', 'pengajuan absen', 'lupa absen', 'salah absen', 'ajukan koreksi', 'ubah jam absen', 'tidak bisa absen', 'lupa clock in', 'lupa clock out', 'absen manual'],
                'response_text' => "Cara Mengajukan Koreksi / Pengajuan Absen (Jika Lupa atau Kendala Teknis):\n1. Buka menu 'Riwayat & Laporan' atau 'Keuangan Absen'.\n2. Pilih tanggal kehadiran yang ingin dikoreksi.\n3. Isi jam masuk/pulang yang sebenarnya, alasan pengajuan, dan lampirkan foto bukti.\n4. Pengajuan akan masuk ke menu 'Pengajuan Absen' untuk disetujui oleh Atasan atau Admin.",
            ],
            [
                'intent_key' => 'approval_pengajuan_absen',
                'category' => 'absensi',
                'keywords' => ['approve absen', 'persetujuan absen', 'siapa approve absen', 'approval koreksi', 'acc absen', 'yang menyetujui absen'],
                'response_text' => "Persetujuan Pengajuan Absen:\n• Pengajuan koreksi absen karyawan disetujui oleh Atasan Divisi masing-masing atau Admin/Super Admin melalui menu 'Pengajuan Absen'.\n• Khusus Admin juga dapat menyetujui pengajuan koreksi absennya sendiri.",
            ],

            // ==========================================
            // 2. JADWAL KERJA & SHIFT
            // ==========================================
            [
                'intent_key' => 'tukar_shift_aturan',
                'category' => 'jadwal',
                'keywords' => ['tukar shift', 'tukar jadwal', 'swap jadwal', 'swap shift', 'ganti shift', 'cara tukar', 'pindah shift', 'jadwal kerja'],
                'response_text' => "Alur Tukar Jadwal / Shift Kerja:\n1. Buka menu 'Jadwal Kerja'.\n2. Pada baris tanggal yang akan datang (masa depan), klik tombol panah bolak-balik (⇄).\n3. Pilih opsi 'Tukar', pilih tanggal target dan rekan kerja dari divisi yang sama.\n4. Tukar jadwal berlaku setelah disetujui oleh rekan target dan Atasan.\n*Catatan: Tanggal yang sudah lewat tidak bisa ditukar, dan role Finance tidak memiliki menu tukar/backup jadwal.",
            ],
            [
                'intent_key' => 'backup_shift_aturan',
                'category' => 'jadwal',
                'keywords' => ['backup shift', 'backup jadwal', 'gantikan shift', 'cara backup', 'minta backup'],
                'response_text' => "Alur Backup Shift:\n1. Buka menu 'Jadwal Kerja', klik ikon (⇄) pada tanggal yang ingin di-backup.\n2. Pilih opsi 'Backup' dan pilih rekan kerja satu divisi yang akan menggantikan di tanggal tersebut.\n3. Setelah rekan dan Atasan menyetujui, jadwal otomatis diperbarui.",
            ],
            [
                'intent_key' => 'aturan_jeda_shift',
                'category' => 'jadwal',
                'keywords' => ['jeda shift', '11 jam', 'istirahat shift', 'aturan shift'],
                'response_text' => "Aturan Jeda Shift 11 Jam:\nDemi kesehatan dan keselamatan kerja, sistem membatasi pertukaran shift apabila jeda waktu istirahat antar shift kurang dari 11 jam.",
            ],

            // ==========================================
            // 3. CUTI & IZIN
            // ==========================================
            [
                'intent_key' => 'sisa_cuti',
                'category' => 'cuti',
                'keywords' => ['sisa cuti', 'kuota cuti', 'saldo cuti', 'berapa cuti', 'jatah cuti', 'cek cuti'],
                'response_text' => 'Anda dapat mengecek sisa kuota cuti tahunan Anda di menu Cuti & Izin.',
            ],
            [
                'intent_key' => 'cara_ajukan_cuti',
                'category' => 'cuti',
                'keywords' => ['ajukan cuti', 'mengajukan cuti', 'cara cuti', 'izin cuti', 'form cuti', 'syarat cuti', 'alur cuti', 'minta cuti', 'cuti sakit', 'cuti tahunan', 'izin tidak masuk'],
                'response_text' => "Cara Mengajukan Cuti & Izin:\n1. Buka menu 'Cuti & Izin' di sidebar.\n2. Klik tombol 'Ajukan Cuti'.\n3. Pilih tipe cuti (Cuti Tahunan, Sakit, atau Izin Khusus), isi tanggal mulai & selesai, serta keterangan.\n4. Untuk cuti sakit wajib melampirkan foto surat keterangan dokter.\n5. Alur Persetujuan: Disetujui oleh Atasan Langsung -> diverifikasi oleh HRD.",
            ],

            // ==========================================
            // 4. LEMBUR (OVERTIME)
            // ==========================================
            [
                'intent_key' => 'cara_ajukan_lembur',
                'category' => 'lembur',
                'keywords' => ['ajukan lembur', 'mengajukan lembur', 'cara lembur', 'klaim lembur', 'form lembur', 'alur lembur', 'persetujuan lembur', 'overtime', 'buat lembur', 'input lembur'],
                'response_text' => "Alur Pengajuan & Klaim Lembur:\n1. Buka menu 'Lembur Saya' (atau 'Persetujuan Lembur' -> Buat Pengajuan).\n2. Pilih tanggal lembur, jam mulai & jam selesai, tuliskan detail pekerjaan, dan unggah foto bukti kegiatan lembur.\n3. Atasan Divisi akan memeriksa dan melakukan Approve.\n4. Setelah disetujui Atasan, data diteruskan ke Finance untuk pencairan insentif lembur.",
            ],
            [
                'intent_key' => 'pencairan_lembur',
                'category' => 'lembur',
                'keywords' => ['bayar lembur', 'cair lembur', 'uang lembur', 'kapan lembur', 'gaji lembur', 'hitungan lembur'],
                'response_text' => 'Pencairan Uang Lembur:\nPengajuan lembur yang telah berstatus Disetujui Atasan akan diverifikasi oleh bagian Finance dan dibayarkan bersama rekapitulasi bulanan.',
            ],

            // ==========================================
            // 5. GOMSN PENGAJUAN OPERASIONAL (DANA, BELI, STOK)
            // ==========================================
            [
                'intent_key' => 'alur_pengajuan_operasional',
                'category' => 'gomsn',
                'keywords' => [
                    'ajukan dana', 'mengajukan dana', 'pengajuan dana', 'cara pengajuan dana', 'alur pengajuan dana',
                    'minta dana', 'kasbon', 'reimburse', 'klaim dana', 'pencairan dana',
                    'pengajuan barang', 'mengajukan barang', 'beli barang', 'pengadaan barang',
                    'ambil stok', 'permintaan barang', 'cara buat pengajuan', 'buat pengajuan baru',
                ],
                'response_text' => "Alur Pengajuan Dana & Barang Operasional:\n1. Buka menu 'Pengajuan Saya' di sidebar, lalu klik '+ Buat Pengajuan Baru'.\n2. Pilih Jenis Pengajuan:\n   • DANA: Permohonan dana operasional / kasbon.\n   • BELI: Pengadaan pembelian barang baru.\n   • STOK: Permintaan pengeluaran barang dari stok gudang.\n3. Isi rincian item, jumlah, estimasi harga, dan keperluan.\n4. Alur Approval:\n   • Tahap 1: Persetujuan Atasan Divisi.\n   • Tahap 2: Otorisasi Direksi/Eksekutif (khusus nominal di atas batas tertentu).\n   • Tahap 3: Pencairan oleh Finance (pilih metode Transfer atau Cash). Begitu Finance mencairkan, status otomatis menjadi SELESAI.\n5. Anda dapat mengunggah foto nota/struk belanja secara opsional pada detail pengajuan.",
            ],
            [
                'intent_key' => 'status_pengajuan_saya',
                'category' => 'gomsn',
                'keywords' => ['status pengajuan', 'pengajuan saya', 'cek pengajuan', 'pengajuan belum cair', 'kenapa masih menunggu'],
                'response_text' => 'Untuk memeriksa status pengajuan dana/barang Anda, buka menu "Pengajuan Saya". Anda bisa melihat apakah statusnya masih Menunggu Atasan, Menunggu Finance, Ditolak, atau sudah Selesai.',
            ],
            [
                'intent_key' => 'tahapan_approval_pengajuan',
                'category' => 'gomsn',
                'keywords' => ['siapa approve pengajuan', 'approve dana', 'persetujuan dana', 'yang menyetujui pengajuan', 'pencairan finance', 'metode pencairan', 'transfer atau cash'],
                'response_text' => "Pihak yang Menyetujui Pengajuan Dana/Barang:\n1. Atasan Divisi: Menyetujui pengajuan anggota timnya di menu 'Persetujuan Karyawan'.\n2. Direksi/Eksekutif: Menyetujui pengajuan bernominal besar.\n3. Finance: Mencairkan dana dengan metode Transfer (bukti transfer opsional) atau Cash (tanpa perlu upload bukti). Setelah dicairkan Finance, status langsung berubah menjadi SELESAI.",
            ],
            [
                'intent_key' => 'export_excel_pengajuan',
                'category' => 'gomsn',
                'keywords' => ['export excel', 'download laporan pengajuan', 'rekap pengajuan', 'unduh excel'],
                'response_text' => "Fitur Export Excel Pengajuan:\n• Klik tombol hijau 'Export Excel' di halaman Pengajuan.\n• Laporan yang di-export secara otomatis hanya mencakup pengajuan yang sudah disetujui/selesai sesuai filter bulan dan tahun yang dipilih.",
            ],
            [
                'intent_key' => 'stok_barang_gudang',
                'category' => 'gomsn',
                'keywords' => ['stok barang', 'material gudang', 'stok habis', 'min stok', 'sisa material', 'inventori', 'barang gudang'],
                'response_text' => "Manajemen Stok Barang Gudang:\n• Diakses melalui menu 'Stok Barang' di sidebar.\n• Menampilkan stok tersedia untuk perangkat seperti ONT/Modem, Kabel Dropcore, Patchcord, Adaptor, dll.\n• Angka kuning pada menu menunjukkan jumlah item yang stoknya menipis (di bawah batas minimum).",
            ],

            // ==========================================
            // 6. TIKETING & GANGGUAN JARINGAN
            // ==========================================
            [
                'intent_key' => 'tiket_aktif_saya',
                'category' => 'tiket',
                'keywords' => ['tiket aktif', 'tiket saya', 'tiket gangguan', 'tugas tiket', 'daftar tiket', 'tiket belum selesai'],
                'response_text' => 'Tiket gangguan aktif Anda dapat dilihat pada menu Tiketing.',
            ],
            [
                'intent_key' => 'buka_tiket_gangguan',
                'category' => 'tiket',
                'keywords' => ['buka tiket', 'buat tiket', 'lapor gangguan', 'cara buat tiket', 'tiket baru', 'input tiket', 'tiket backbone', 'tiket broadband'],
                'response_text' => "Cara Membuat & Menangani Tiket Gangguan:\n1. Buka menu 'Tiketing' -> klik 'Buat Tiket Baru'.\n2. Pilih kategori tiket:\n   • Backbone (Kode BDG-): Gangguan jalur utama/distribusi.\n   • Broadband (Kode BRD-): Gangguan pelanggan FTTH/rumahan.\n3. Pilih tingkat prioritas SLA (P1 Kritis s/d P4 Rendah) dan tugaskan teknisi.\n4. Teknisi wajib melakukan update progress berkala hingga tiket ditutup (CLOSED) dan Berita Acara dapat diunduh.",
            ],
            [
                'intent_key' => 'stop_clock_aturan',
                'category' => 'tiket',
                'keywords' => ['stop clock', 'jeda sla', 'pause tiket', 'sla tiket', 'pending tiket'],
                'response_text' => "Fitur Stop Clock Tiket:\nDigunakan untuk menghentikan sementara argometer waktu SLA apabila teknisi mengalami kendala eksternal di lapangan (seperti hujan deras/petir, menunggu izin akses gedung, atau mati listrik PLN).",
            ],
            [
                'intent_key' => 'kpi_teknisi',
                'category' => 'tiket',
                'keywords' => ['kpi teknisi', 'penilaian teknisi', 'skor kpi', 'bobot nilai', 'performa teknisi'],
                'response_text' => "Rumus Penilaian KPI Teknisi (Skor 0 - 100):\n• 45% Ketepatan penyelesaian sebelum batas SLA habis.\n• 35% Kepatuhan update laporan progress tiap 30 menit.\n• 20% Kecepatan respon awal sejak tiket didisposisikan.",
            ],

            // ==========================================
            // 7. JARINGAN FIBER OPTIK (OLT, PON, ODP)
            // ==========================================
            [
                'intent_key' => 'manajemen_jaringan_odp',
                'category' => 'jaringan',
                'keywords' => ['olt', 'pon', 'odp', 'jaringan fiber', 'port odp', 'titik odp', 'kapasitas odp', 'data pelanggan odp'],
                'response_text' => "Manajemen Jaringan OLT, PON & ODP:\n• Klik foto profil Anda di kanan atas -> pilih 'Jaringan OLT, PON & ODP'.\n• Di halaman ini Anda dapat menelusuri daftar OLT, melihat port PON, memantau kapasitas port ODP yang terpakai/kosong, titik koordinat Maps ODP, serta daftar pelanggan di tiap ODP.",
            ],

            // ==========================================
            // 8. GAJI, POTONGAN & REKAPITULASI
            // ==========================================
            [
                'intent_key' => 'potongan_keuangan_absen',
                'category' => 'keuangan',
                'keywords' => ['potongan gaji', 'potongan absen', 'denda telat', 'potongan alpa', 'keuangan absen', 'terlambat potong berapa', 'telat absen'],
                'response_text' => "Informasi Keuangan & Potongan Absen:\n• Dapat dilihat secara transparan di menu 'Keuangan Absen'.\n• Menampilkan rincian keterlambatan, ketidakhadiran (alpa), dan estimasi potongan berjalan pada periode bulan tersebut.",
            ],
            [
                'intent_key' => 'leaderboard_karyawan',
                'category' => 'hris',
                'keywords' => ['leaderboard', 'peringkat hadir', 'karyawan teladan', 'top absensi', 'juara absen', 'ranking karyawan'],
                'response_text' => "Menu Leaderboard:\nMenampilkan peringkat kedisiplinan karyawan PT MSN secara real-time berdasarkan tingkat kehadiran tertinggi, ketepatan waktu clock-in, dan konsistensi bulanan.",
            ],

            // ==========================================
            // 9. AKUN, PROFIL, LOGIN & BANTUAN
            // ==========================================
            [
                'intent_key' => 'ganti_foto_profil',
                'category' => 'profil',
                'keywords' => ['ganti foto', 'ubah foto', 'upload foto profil', 'foto profil', 'cara ganti avatar', 'edit profil', 'ubah password', 'ganti password'],
                'response_text' => "Pengaturan Profile & Foto:\n• Anda dapat membuka halaman 'Profile' dari menu kanan atas untuk memperbarui informasi kontak dan mengubah password akun.\n• Khusus perubahan Foto Profil Karyawan hanya dapat dilakukan oleh Admin melalui menu 'Data Karyawan' agar selaras dengan data biometrik.",
            ],
            [
                'intent_key' => 'cara_login_sidik_jari',
                'category' => 'profil',
                'keywords' => ['login', 'username', 'sidik jari', 'fingerprint', 'biometrik login', 'passkey', 'webauthn', 'lupa password'],
                'response_text' => "Informasi Login Portal:\n• Login menggunakan Username dan Password akun Anda.\n• Tombol login cepat dengan Sidik Jari / Biometrik (WebAuthn) di halaman login akan otomatis muncul apabila Anda sudah mendaftarkan perangkat/sidik jari di halaman Profile.",
            ],
            [
                'intent_key' => 'kontak_bantuan_helpdesk',
                'category' => 'bantuan',
                'keywords' => ['kontak', 'helpdesk', 'noc', 'telepon', 'whatsapp', 'admin', 'hrd', 'nomor wa'],
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
