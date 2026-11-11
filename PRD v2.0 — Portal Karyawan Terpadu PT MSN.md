# PRD v2.0 — Portal Karyawan Terpadu PT MSN

**Status:** Draft untuk review · **Versi:** 2.0 (revisi dari v1.0 Blueprint) · **Tanggal:** 4 November 2026 **Stack referensi:** Laravel 12 (PHP 8.2+), MySQL 8 / MariaDB, Redis, Python FastAPI + InsightFace `buffalo_l` (ONNX), Vite, Tailwind, DomPDF, Maatwebsite Excel, Web Push VAPID.

---

## 0. Ringkasan Perubahan dari v1.0

Revisi ini mempertahankan seluruh cakupan fungsional v1.0, lalu menutup celah yang ditemukan saat review: aturan bisnis yang saling bertentangan, area keamanan yang belum didefinisikan, skema data yang belum lengkap, dan tidak adanya persyaratan non-fungsional serta metrik keberhasilan.

| # | Temuan v1.0 | Perbaikan di v2.0 |
| --- | --- | --- |
| 1 | Tarif denda terlambat ganda: Rp 5.000/jam vs Rp 15.000/jam | Satu sumber kebenaran di `system_settings`, dengan riwayat perubahan tarif (§5.5) |
| 2 | Kuota cuti bulanan ditulis `3 kali/hari`, ambigu | Didefinisikan sebagai **3 hari kerja per bulan** (§5.4) |
| 3 | Alpa otomatis memotong kuota cuti tanpa persetujuan karyawan | Aturan eksplisit, bisa dikonfigurasi, dengan notifikasi dan masa sanggah (§5.4) |
| 4 | Face service: Flask vs FastAPI, parameter `photo`/`image`/`normal_photo` tidak seragam, threshold berbeda | Kontrak API tunggal berversi `/v1`, threshold dari konfigurasi (§5.1) |
| 5 | Anti-spoofing hanya mengandalkan deteksi senyum | Liveness pasif + aktif, deteksi mock location, device binding (§5.1) |
| 6 | Tiga kolom role terpisah (`role`, `work_report_role`, `gomsn_role`) | RBAC tunggal berbasis role + permission, bisa multi-role (§4) |
| 7 | SSO HMAC tanpa perlindungan replay | Dihapus setelah konsolidasi; bila masih perlu, token sekali pakai berumur pendek (§6.1) |
| 8 | Geofence 20 m tanpa mempertimbangkan akurasi GPS | Validasi `accuracy`, toleransi dinamis, mekanisme exception (§5.1) |
| 9 | Nomor tiket rawan duplikat saat konkuren | Tabel sequence dengan row lock (§7.1) |
| 10 | Status tiket tidak punya `CANCELLED`/`REOPENED`; Stop Clock tanpa kontrol | State machine lengkap + approval + batas Stop Clock (§7.2–7.4) |
| 11 | Rumus KPI tanpa definisi pembilang/penyebut | Definisi formal dan kasus tepi (§7.7) |
| 12 | Tabel GOMSN tanpa timestamp, approver, audit | Skema diperkaya + `audit_logs` global (§9) |
| 13 | Tidak ada NFR, metrik sukses, risiko, rencana rilis | Ditambahkan (§10–§14) |
| 14 | Data biometrik tanpa kebijakan privasi | Kepatuhan UU PDP No. 27/2022: consent, retensi, enkripsi (§10.2) |

---

## 1. Ringkasan Eksekutif

**Portal Karyawan MSN** adalah satu aplikasi enterprise dengan satu autentikasi yang menyatukan empat area:

1. **Portal Hub** — dashboard personal berisi metrik kehadiran, sisa cuti, tiket aktif, pengajuan, lembur, dan tugas.
2. **Modul Absensi & HRIS** — absensi wajah + geofencing, jadwal shift, tukar/backup shift, cuti, koreksi absensi, laporan potongan gaji, leaderboard, chatbot.
3. **Modul Work Report & Tiketing Gangguan** — tiket Backbone (`BDG-`) dan Broadband (`BRD-`), dispatch, kronologis, Stop Clock, handover, aset lapangan, 2-stage closing, analitik MTTR/SLA/KPI.
4. **Modul GOMSN Operasional** — pengajuan dana/barang berjenjang, lembur, dan penugasan kerja.

### 1.1 Masalah yang Diselesaikan

- Data karyawan, tiket, dan operasional tersebar di tiga database dengan identitas pengguna ganda.
- Absensi rentan manipulasi (foto, lokasi palsu, jam perangkat).
- SLA gangguan sulit diaudit karena bukti lapangan dan jeda SLA tidak terstandar.
- Proses pengajuan dan lembur manual dan tidak terlacak.

### 1.2 Tujuan dan Non-Tujuan

**Tujuan:** satu sumber data, aturan bisnis yang konsisten dan dapat dikonfigurasi, jejak audit lengkap, pengalaman mobile-first untuk teknisi lapangan. **Non-tujuan (rilis ini):** penggajian penuh (payroll), pengelolaan rekrutmen, billing pelanggan, pengganti sistem IMS.

### 1.3 Metrik Keberhasilan (Target 6 Bulan Setelah Rilis)

| Metrik | Baseline | Target |
| --- | --- | --- |
| Waktu proses clock-in (tombol tekan → sukses) | diukur saat pilot | p95 ≤ 8 detik |
| False Reject Rate pengenalan wajah | diukur saat pilot | ≤ 2% |
| False Accept Rate | — | ≤ 0,1% |
| Absensi dengan manipulasi lokasi lolos | — | 0 kasus terkonfirmasi |
| Kepatuhan update kronologis 30 menit | diukur saat pilot | ≥ 85% |
| Tiket Closed dengan SLA `TEPAT` | diukur saat pilot | ≥ 90% |
| Waktu rata-rata persetujuan pengajuan | diukur saat pilot | turun ≥ 40% |
| Pengguna aktif mingguan / karyawan aktif | — | ≥ 90% |

---

## 2. Arsitektur Sistem

- **Aplikasi utama:** Laravel 12 monolith modular (`app/Modules/{Attendance,Ticketing,Gomsn,Portal}`), satu database.
- **Face Service:** FastAPI (Python 3.11) + InsightFace `buffalo_l`, hanya dapat diakses dari jaringan internal, autentikasi service-to-service (token + allow-list IP).
- **Queue & cache:** Redis; Laravel Horizon untuk worker notifikasi, ekspor PDF/Excel, dan perhitungan berat.
- **Penyimpanan file:** disk S3-compatible (atau storage lokal dengan backup), nama file acak.
- **Database eksternal `ims_v3`:** koneksi read-only dengan akun terpisah (hanya `SELECT` pada tabel yang diperlukan); hasil pencarian di-cache singkat dan ada fallback input manual saat IMS tidak tersedia.
- **Scheduler:** satu entri cron `schedule:run`; job terjadwal didefinisikan di `routes/console.php`.
- **Zona waktu:** database menyimpan UTC, tampilan `Asia/Jakarta`; perhitungan hari kerja memakai zona waktu kantor.

Layanan eksternal: Fonnte (WhatsApp), Web Push VAPID, OpenStreetMap Nominatim (reverse geocode, dengan cache dan batas 1 request/detik sesuai kebijakan penggunaan; sediakan penyedia cadangan).

---

## 3. Persona

| Persona | Kebutuhan utama |
| --- | --- |
| Karyawan kantor | Absen cepat, lihat jadwal, ajukan cuti/tukar shift, pahami potongan |
| Teknisi lapangan | Input kronologis cepat di sinyal lemah, foto geotag, offline-first |
| Helpdesk NOC / SA-CS | Buka tiket cepat, pantau SLA dan kepatuhan, verifikasi closing |
| Manager Teknis | Dispatch, beban kerja tim, laporan KPI |
| Atasan / Finance | Persetujuan ringkas, bukti lengkap, tidak ada pengajuan tertahan |
| Admin / HRD | Kontrol master data, override terdokumentasi, laporan, audit |

---

## 4. RBAC dan Keamanan Akses

### 4.1 Model

Ganti tiga kolom role dengan tabel `roles`, `permissions`, `role_user` (disarankan `spatie/laravel-permission`). Satu pengguna dapat memiliki beberapa role.

**Role:** `super_admin`, `hrd`, `employee`, `manager_teknisi`, `helpdesk`, `sa_cs`, `teknisi`, `atasan`, `finance`, `ceo`, `noc`.

### 4.2 Contoh Permission (format `modul.sumber.aksi`)

`attendance.employee.manage`, `attendance.schedule.generate`, `attendance.leave.approve`, `ticket.create`, `ticket.dispatch`, `ticket.closing_awal`, `ticket.verify_close`, `ticket.report.view_all`, `gomsn.pengajuan.approve_atasan`, `gomsn.pengajuan.approve_finance`, `gomsn.lembur.pay`.

### 4.3 Aturan Tambahan

- **Hierarki atasan:** kolom `users.supervisor_id`; pengajuan otomatis diarahkan ke atasan langsung, bukan dipilih bebas lewat `atasan_username`. Delegasi saat atasan cuti melalui `approval_delegations`.
- **Pemisahan tugas:** pemohon tidak boleh menyetujui pengajuannya sendiri; yang menutup tiket tidak boleh sama dengan yang melakukan closing awal.
- **Super Admin:** setiap override (reset wajah, ubah saldo cuti, hapus tiket) wajib menyertakan alasan dan tercatat di `audit_logs`.

---

## 5. Modul Absensi & HRIS

### 5.1 Biometrik Wajah, Geofencing, dan Anti-Fraud

**Kontrak Face Service (`/v1`)** — request/response JSON dengan field baku `image` (Base64 JPEG, maks 1,5 MB) dan `request_id`.

| Endpoint | Fungsi |
| --- | --- |
| `GET /v1/health` | Status engine, versi model, jumlah template |
| `POST /v1/enroll` | Ekstrak embedding 512-D, simpan per `user_id`; minta 3 pose (depan, kiri, kanan) |
| `POST /v1/detect` | Jumlah wajah, bounding box, skor kualitas (blur, pencahayaan, ukuran) |
| `POST /v1/verify` | Verifikasi 1:1 terhadap `user_id`: `score`, `match`, `liveness_score` |
| `POST /v1/identify` | Identifikasi 1:N (opsional, untuk audit/deteksi pendaftaran ganda) |
| `DELETE /v1/users/{user_id}` | Hapus template (hak hapus data biometrik) |

Response error seragam: `{ "error": { "code": "NO_FACE", "message": "..." } }`.

**Aturan keputusan:**

- Absensi harian memakai **verifikasi 1:1**, bukan identifikasi 1:N. Threshold disimpan di `system_settings` (`face.verify_threshold`, default 0,5) dan dikalibrasi saat pilot.
- Tolak bila lebih dari satu wajah, skor kualitas di bawah batas, atau `liveness_score` di bawah batas.
- Template disimpan di database terenkripsi (kolom binary + enkripsi aplikasi), bukan berkas `.pkl` tanpa kontrol akses dan backup.
- Setelah 3 kali gagal berturut-turut, pengguna diarahkan ke **jalur manual**: foto + alasan, antrean verifikasi HRD, status absensi `pending_review`.

**Liveness / anti-spoofing:**

1. Pasif: model anti-spoof (layar, cetak, topeng) pada kedua foto.
2. Aktif: tantangan acak (senyum, kedip, atau tengok) menggantikan senyum tetap.
3. Konsistensi: kedua foto harus cocok dengan pengguna yang sama dan bukan foto identik (cek hash/perceptual).

**Anti-manipulasi lokasi dan perangkat:**

- Aplikasi mobile mengirim `gps_accuracy`, flag `is_mock_location`, dan identifier perangkat; web hanya cadangan dengan batasan.
- Absensi ditandai `flagged` untuk review bila `gps_accuracy` > 30 m atau mock location terdeteksi, tidak diam-diam diterima.
- **Device binding:** maksimal 2 perangkat terdaftar per pengguna; perangkat baru butuh persetujuan.
- Waktu absensi **selalu memakai waktu server**; waktu perangkat hanya untuk sinkronisasi offline dengan batas toleransi.

**Geofencing multi-kantor:**

- Lokasi kantor dikelola di `office_locations` (`name`, `lat`, `lng`, `radius_m`, `is_active`). Data awal: Soreang (-7.042215, 107.540981) dan Bandung (-6.937122, 107.631989).
- Radius default 20 m **dikonfigurasi per kantor**; lolos bila `jarak <= radius + min(accuracy, 15 m)`. Rekomendasi pilot: mulai 50 m lalu perketat berdasarkan data.
- Tipe kehadiran: `office` (wajib dalam radius), `field` (dinas luar, wajib catatan dan referensi tiket/penugasan bila ada), `remote` (wajib catatan; butuh persetujuan atasan bila melebihi kuota WFH yang dikonfigurasi). Keterlambatan `field` tetap dihitung; `remote` bebas denda.

**Alur dua tahap:** Tahap 1 memvalidasi deteksi wajah real-time dan mengeluarkan `attempt_token` (berlaku 2 menit, sekali pakai). Tahap 2 mengunggah dua foto, koordinat, dan token. Server memverifikasi ulang wajah dan lokasi (tidak mempercayai hasil tahap 1 dari klien). Clock-in aktif mulai 05:00 WIB; lupa clock-out di-auto-close pada jam tertentu dengan status `incomplete`.

**Offline mode:** antrean lokal menyimpan foto, koordinat, dan `client_uuid`. Sinkronisasi idempoten (`client_uuid` unik), mengirim `captured_at`; server menandai `flagged` bila selisih waktu tidak wajar atau umur antrean melewati batas (default 24 jam). Wajah divalidasi server saat sinkronisasi.

**Lembur dari absensi:** durasi kerja dikurangi jam istirahat shift; lembur di atas 8 jam kerja bersih dihitung **sebagai usulan**. Lembur yang dibayar wajib memiliki pengajuan disetujui (§8.2); selisih ditampilkan di laporan rekonsiliasi.

### 5.2 Jadwal Shift Bulanan

Master shift dipindah ke tabel `shifts` (kode, nama, jam, warna, `late_tolerance_minutes`, `break_minutes`) sehingga shift baru tidak butuh perubahan kode.

| Shift | Jam | Kode |
| --- | --- | --- |
| Pagi | 08:15–17:00 | P |
| Siang | 13:00–21:00 | S |
| Subuh | 06:00–15:00 | SB |
| BJS (Visit Soreang) | 08:15–17:00 | BJ |
| Piket Pagi | 08:15–17:00 | PK |
| Piket Siang | 13:00–21:00 | PA |
| Libur | – | L |

Catatan: notasi `13:00/13:15` dan `06:00/06:15` pada v1.0 ambigu; di v2.0 angka pertama adalah jam mulai resmi dan angka kedua menjadi batas toleransi (`late_tolerance_minutes = 15`).

Fitur admin: auto-generate bulanan (menandai Sabtu/Minggu dan libur nasional, mempertahankan piket, **dengan pratinjau dan konfirmasi sebelum menimpa**), kalender interaktif, impor/ekspor Excel dengan laporan galat per baris, cetak per divisi/kantor, manajemen hari libur dan cuti bersama. Perubahan jadwal pada bulan berjalan masuk audit dan memberi notifikasi ke karyawan. Tambahkan **deteksi konflik aturan** (mis. jeda istirahat minimum 11 jam antar-shift).

### 5.3 Tukar Shift dan Backup (4-Way Cross-Date Swap)

- Mode tukar (`is_backup=false`) mendukung tanggal sama dan lintas tanggal; mode backup (`is_backup=true`) hanya Piket ↔ Libur di tanggal sama.
- **Alur persetujuan:** pemohon → target menyetujui → atasan/admin menyetujui. Permintaan kedaluwarsa otomatis 48 jam atau pada H-1 tanggal tukar.
- Validasi: divisi sama; tanggal tidak di masa lalu; **tidak ada duplikasi permintaan aktif pada slot yang sama**; hasil tukar tidak melanggar aturan konflik shift (§5.2).
- Persetujuan berjalan dalam satu `DB::transaction` dengan `lockForUpdate` pada empat baris jadwal, dan memverifikasi isi jadwal **belum berubah** sejak permintaan dibuat. Gagal → permintaan ditolak dengan alasan sistem.

### 5.4 Cuti dan Sinkronisasi Alpa

- **Kuota tahunan:** 12 hari kerja/tahun; **kuota bulanan:** maksimal 3 hari kerja cuti per bulan (reset otomatis). Prorata karyawan baru dan carry-over dikonfigurasi di `system_settings`.
- Pengajuan memeriksa tumpang tindih, jadwal libur, dan **batas kapasitas divisi** (maks. orang cuti bersamaan). Lampiran wajib untuk cuti sakit lebih dari N hari.
- **Alpa:** hari kerja berjadwal tanpa absensi, tanpa cuti disetujui, setelah `join_date`. Proses `leave:sync` berjalan harian dini hari dan idempoten.
- **Konversi Alpa:** kebijakan v1.0 (3 alpa pertama per bulan memotong kuota cuti) dipertahankan **sebagai opsi** `leave.alpa_converts_to_leave`. Bila aktif: karyawan menerima notifikasi per alpa dan punya **masa sanggah 3 hari kerja** (ajukan koreksi, §5.6) sebelum konversi final; alpa di atas sisa kuota bulanan dikenai potongan.
- Kunci override manual: saldo terkunci tidak tertimpa sync; setiap override mencatat pelaku dan alasan, dan HRD menerima laporan bulanan saldo yang di-override.
- Pembatalan cuti dapat dilakukan sebelum tanggal mulai; saldo dikembalikan otomatis.

### 5.5 Laporan Potongan

- Tarif keterlambatan dan alpa disimpan di `system_settings` dengan **tanggal berlaku (`effective_from`)** sehingga perubahan tarif tidak mengubah bulan lampau. Rumus: `late_minutes × tarif_per_menit`, dengan toleransi, pembulatan, dan batas maksimum harian yang dapat dikonfigurasi. Tarif alpa default Rp 25.000/hari atau `users.potongan_alpa` bila terisi.
- Seluruh nilai uang memakai `DECIMAL(15,2)`, bukan float.
- Laporan karyawan menampilkan rincian per hari (bukan hanya agregat) dengan tautan ke absensi dan tombol ajukan koreksi; grafik tren 6 bulan.
- **Penguncian periode:** HRD menutup periode bulanan; setelah ditutup, perubahan hanya lewat koreksi `adjustment` di periode berikutnya.
- Catatan kepatuhan: kebijakan potongan upah perlu divalidasi HRD/legal terhadap peraturan ketenagakerjaan yang berlaku (mis. PP 36/2021) sebelum diaktifkan; sistem hanya menghitung.

### 5.6 Koreksi Status Absensi

Pengajuan memuat tanggal, status baru, jam masuk, alasan, dan lampiran bukti. Batas pengajuan: maksimal 7 hari kalender sejak kejadian (konfigurasi). Approver: atasan langsung, lalu HRD untuk koreksi di atas batas tertentu. Saat disetujui, sistem membuat/memperbarui `Attendance` **dengan menyimpan nilai sebelum dan sesudah** di `audit_logs`, menghitung ulang potongan, dan memberi penanda `source = correction`.

### 5.7 Leaderboard

Peringkat bulanan, triwulanan, tahunan berdasarkan persentase kehadiran (DESC), lalu total menit terlambat (ASC), lalu jumlah hari tepat waktu. Hanya hari kerja berjadwal yang dihitung; cuti disetujui dan sakit berdokumen tidak mengurangi persentase. Karyawan dapat opt-out dari tampilan nama pada leaderboard. Ekspor CSV UTF-8 BOM.

### 5.8 Chatbot Asisten

Mempertahankan 20+ intent v1.0. Perbaikan: ambang skor minimum (di bawah ambang → tawarkan saran intent dan jalur ke Helpdesk), log pertanyaan tak terjawab, akses data hanya milik pengguna yang login, dan basis intent di tabel agar dapat diubah tanpa deploy.

### 5.9 Pengingat Shift

Job terjadwal mengirim pengingat 1 jam sebelum masuk dan 20 menit sebelum pulang lewat Email, in-app, dan Web Push sesuai preferensi pengguna, melalui queue; tidak mengirim untuk karyawan yang cuti atau libur.

---

## 6. Autentikasi, Sesi, dan Portal Hub

### 6.1 Autentikasi

- Login dengan `phone`, `username`, `email`, atau `employee_code` + password. Keempat identifier harus **unik** (indeks unik) dan tidak saling bertabrakan.
- Kebijakan password: minimum 10 karakter, hash Argon2id/bcrypt, wajib ganti saat pertama login, pengecekan password umum.
- **Rate limiting:** 5 percobaan/menit per identifier+IP; penguncian sementara bertahap; CAPTCHA setelah ambang gagal.
- **OTP:** 6 digit, disimpan sebagai hash, berlaku 5 menit, sekali pakai, maksimum 3 percobaan, pengiriman ulang dibatasi. Dipakai untuk pemulihan password dan persetujuan perangkat baru.
- **Sesi:** tabel `user_sessions` (perangkat, IP, `last_activity`) menggantikan flag tunggal `is_login`, sehingga multi-perangkat dan *force logout* per sesi berfungsi benar. Admin dapat memaksa logout satu pengguna atau semua sesi; masa idle dikonfigurasi.
- Setelah konsolidasi database, **SSO HMAC antar-aplikasi tidak diperlukan**. Bila masih ada aplikasi pihak ketiga, gunakan token sekali pakai dengan `jti`, kedaluwarsa ≤ 60 detik, perbandingan `hash_equals`, dan daftar aplikasi yang diizinkan.

### 6.2 Portal Hub

Kartu metrik: absensi hari ini, kehadiran bulan berjalan, sisa cuti; total tiket per status dan 6 tiket terbaru **sesuai hak akses**; pengajuan, lembur, dan tugas aktif. Kartu dimuat dengan cache singkat per pengguna dan lazy load. Tambahkan kartu **Perlu Tindakan Saya** (persetujuan menunggu, tiket OVERDUE, jadwal berubah).

### 6.3 Konfigurasi Sistem (`system_settings`)

Parameter berikut dikelola Admin lewat UI dengan riwayat perubahan dan `effective_from`: tarif keterlambatan/alpa/lembur, toleransi, radius dan lokasi kantor, threshold wajah, batas akurasi GPS, SLA default, interval kronologis, kuota cuti, jendela koreksi, kebijakan retensi.

### 6.4 Mobile

PWA (service worker) dan aplikasi Android. Deep link `absenv2://app/...` dipertahankan; pemetaan rute divalidasi dengan daftar putih. Unduhan APK memeriksa hash/tanda tangan dan versi minimum; tersedia **paksa update** bila versi di bawah minimum.

---

## 7. Modul Tiketing Gangguan

### 7.1 Kategori dan Penomoran

- `BDG-YYYYMMDD-XXX` (Backbone) dan `BRD-YYYYMMDD-XXX` (Broadband). Nomor dibuat dari tabel `ticket_sequences (prefix, date, last_number)` dalam transaksi dengan `lockForUpdate`; kolom `no_tiket` unik. Format 3 digit diperluas otomatis bila lebih dari 999 per hari.
- **Backbone:** `backbone_segment`, `status_link_impact` (mis. TOTAL\_DOWN, DEGRADED, NO\_IMPACT), `wilayah`, `tipe_penanganan`, SLA dari `master_sla` (default 360 menit).
- **Broadband:** integrasi IMS (`/api/ims/customers/search`, `/api/ims/customers/{cid}`) mengisi data pelanggan. Salinan data pelanggan disimpan pada tiket (snapshot) agar laporan historis tidak berubah bila data IMS berubah. SLA default 240 menit. Tombol `wa.me` hanya untuk pengguna berhak.
- **Deteksi duplikat:** peringatan bila ada tiket aktif untuk pelanggan/segmen yang sama; Helpdesk dapat menautkan sebagai *child ticket* atau tetap membuat baru dengan alasan.
- **Prioritas:** tambahkan `priority` (P1–P4) dan matriks SLA per prioritas/segmen sebagai pengembangan dari SLA tunggal.

### 7.2 Siklus Hidup Tiket

| Dari | Ke | Pelaku | Syarat |
| --- | --- | --- | --- |
| – | `OPEN` | Helpdesk/Admin | Data wajib terisi |
| `OPEN` | `OPEN` (assigned) | Manager Teknis/Admin | Lead dan tim dipilih; peringatan bila teknisi overload |
| `OPEN` | `PROSES` | Teknisi tim | Kronologis pertama (respon pertama tercatat) |
| `PROSES` | `PENDING_VERIFIKASI` | Teknisi tim | Prasyarat closing awal terpenuhi (§7.6), Stop Clock tidak aktif |
| `PENDING_VERIFIKASI` | `PROSES` | Helpdesk | Reject dengan alasan wajib |
| `PENDING_VERIFIKASI` | `CLOSE` | Helpdesk | Verifikasi akhir, hitung MTTR/SLA |
| `CLOSE` | `REOPENED` | Helpdesk/Manager | Alasan wajib, dalam batas hari (default 7); MTTR dihitung kumulatif |
| `OPEN`/`PROSES` | `CANCELLED` | Helpdesk/Admin | Alasan wajib (salah buka, duplikat); dikecualikan dari KPI |

Setiap transisi dicatat di `ticket_status_history` (dari, ke, pelaku, waktu, alasan) dan dikontrol oleh satu kelas state machine, bukan if-else tersebar. Respon pertama dihitung dari `tanggal_open` ke `first_response_at`. Visibilitas: teknisi hanya melihat tiket di mana ia termasuk tim atau yang berstatus `CLOSE`; tim disimpan di pivot `ticket_assignees` (bukan JSON) agar dapat dikueri dan diindeks.

### 7.3 Kronologis dan Kepatuhan 30 Menit

- Kategori: `IZIN`, `OTDR`, `TRACING`, `MATERIAL`, `JOINTING`, `LINK_UP`, `SELESAI`, `LAIN`, ditambah `SYSTEM` untuk log otomatis.
- Entri **append-only**: tidak dapat diubah/dihapus selain oleh Admin dengan alasan tercatat.
- Media: foto/video dikompresi di sisi klien, dibatasi ukuran, divalidasi MIME, dengan antrean offline untuk sinyal lemah. Koordinat GPS menyertakan akurasi.
- Reverse geocoding lewat endpoint internal dengan cache per koordinat (dibulatkan) agar tidak melanggar batas Nominatim.
- Indikator interval: `NORMAL` ≤ 20 menit, `WARNING` 21–30, `OVERDUE` > 30, `STOP_CLOCK` selama jeda. **Nilai batas dikonfigurasi.** Pengukuran berjalan antara respon pertama dan closing awal, tidak menghitung Stop Clock. Setiap pelanggaran dicatat sebagai event (`interval_breaches`) agar KPI dapat diaudit.

### 7.4 Stop Clock

- Alasan: `IZIN_AKSES`, `PIHAK_KETIGA_PLN`, `CUACA_EKSTRIM`, `MENUNGGU_MATERIAL`, `PERMINTAAN_PELANGGAN`, `FORCE_MAJEURE`.
- **Kontrol penyalahgunaan:** hanya satu Stop Clock aktif per tiket; alasan detail dan minimal satu bukti (foto/surat/screenshot) wajib untuk `IZIN_AKSES`, `PIHAK_KETIGA_PLN`, `PERMINTAAN_PELANGGAN`; Stop Clock di atas ambang durasi (default 120 menit) memerlukan **persetujuan Manager Teknis/Helpdesk**; pengingat otomatis tiap 30 menit selama jeda.
- Penutupan menghitung `duration_minutes`; `total_stop_clock_minutes` dihitung ulang dari tabel (bukan inkremental) untuk mencegah selisih.

### 7.5 Handover Shift

Mencatat `shift_from → shift_to`, penerima, catatan wajib, dan **ringkasan otomatis** (status terakhir, Stop Clock aktif, tindak lanjut). Penerima mengonfirmasi (`acknowledged_at`); tanpa konfirmasi dalam 15 menit, notifikasi naik ke Manager Teknis. Penerima otomatis ditambahkan ke tim tiket.

### 7.6 Aset Teknis, Material, dan Dokumentasi

Struktur v1.0 (Resume Pekerjaan, Material, Titik Perbaikan, Joint Closure + matriks Core, Manuver Core, Dokumentasi) dipertahankan dengan perbaikan:

- **Material:** pemakaian mengurangi `master_materials.stok_tersedia` secara atomik; ada riwayat pergerakan (`material_movements`) dan peringatan stok minimum; koreksi mengembalikan stok.
- **Matriks core:** pasangan (`tube_asal`,`core_asal`) unik per closure, batas sesuai kapasitas kabel, `loss_db` divalidasi rentang (peringatan di atas 0,3 dB per sambungan), serta ekspor matriks ke PDF/Excel untuk dokumentasi as-built.
- **Foto geotag:** waktu, koordinat, dan akurasi diambil dari perangkat saat unggah, dibandingkan dengan lokasi tiket; foto di luar jarak wajar ditandai. Tanda air waktu dan nomor tiket.
- **Redaman:** nilai dBm divalidasi rentang (mis. -40 s.d. 0) dan diberi peringatan bila `redaman_sesudah` masih di luar standar yang dikonfigurasi.

**Prasyarat Closing Awal** (`checkClosingPrerequisites`) dikembalikan sebagai daftar kekurangan rinci ke UI, bukan sekadar gagal:

- *Broadband:* resume (`problem_temuan`, `action`), ≥ 1 foto hasil (selain Bukti Masalah), `redaman_sesudah`.
- *Backbone:* resume, ≥ 1 foto hasil/OTDR, ≥ 1 Titik Perbaikan atau Joint Closure, ≥ 1 Joint Closure atau Manuver Core, dan konfirmasi pemulihan link (`LINK_UP`).

### 7.7 MTTR, SLA, dan KPI

**MTTR bersih** = `max(0, menit(tanggal_close − tanggal_open) − total_stop_clock_minutes)`. Untuk tiket `REOPENED`, MTTR kumulatif lintas siklus. **Status SLA:** `TEPAT` bila MTTR ≤ `sla_target_minutes`, `LEBIH` bila melebihi, `NA` untuk `CANCELLED`. `sla_target_minutes` di-*snapshot* saat tiket dibuat sehingga perubahan `master_sla` tidak mengubah hasil lama.

**Skor KPI Teknisi (0–100):** `KPI = 0,45 × SLA + 0,35 × Interval + 0,20 × Respon`

- **SLA** = tiket `TEPAT` ÷ tiket `CLOSE` yang ditangani (persen).
- **Interval** = menit terpantau berstatus NORMAL/WARNING ÷ total menit terpantau × 100, di luar Stop Clock.
- **Respon** = `max(0, 100 − min(100, rata-rata menit respon × 2))`.
- Tiket dengan lead dan tim: kredit dibagi sesuai peran (konfigurasi). Periode dengan data kurang dari N tiket (default 5) ditampilkan sebagai *data tidak cukup*, bukan skor.
- Bobot dan batas disimpan di `system_settings`. KPI Helpdesk memakai definisi terpisah: waktu pembukaan tiket, akurasi data, dan waktu verifikasi akhir.

**Laporan:** tren MTTR 12 bulan, Top Segment, tipe penanganan, dampak Stop Clock, distribusi jam kejadian, handover, rekap material. Ekspor PDF/Excel dijalankan lewat queue untuk dataset besar dengan notifikasi saat selesai. Berita Acara PDF per tiket memuat kronologis, foto, matriks core, dan penanda pengesahan (nama, waktu, hash dokumen).

### 7.8 Notifikasi

Event: tiket baru, dispatch, kronologis, WARNING/OVERDUE, Stop Clock, handover, reject closing, tiket Close. Kanal: database, Web Push, WhatsApp (Fonnte). Pengiriman memakai queue dengan *retry* eksponensial, batas laju, deduplikasi, dan log di `notification_logs`. Template pesan dikelola di database. Eskalasi: tiket tanpa dispatch > 10 menit atau OVERDUE berulang memberi notifikasi ke Manager Teknis.

---

## 8. Modul GOMSN Operasional

### 8.1 Pengajuan Dana dan Barang

Status tunggal `status` berupa state machine: `draft → menunggu_atasan → menunggu_finance → dicairkan → menunggu_bukti → selesai`, plus `ditolak` dan `dibatalkan`. (v1.0 memiliki `status_atasan`, `status_finance`, dan `status` terpisah yang dapat tidak sinkron.)

- Pengajuan dana memakai rincian item (`pengajuan_dana_detail`); `jumlah` dihitung sistem dari subtotal, bukan input bebas.
- Persetujuan berjenjang berdasarkan nominal (konfigurasi): ≤ X atasan + Finance; > X ditambah HRD/CEO.
- Penolakan wajib beralasan; approver, waktu, dan catatan disimpan per tahap (`pengajuan_approvals`).
- Unggah Bukti Transfer (Finance) dan Bukti Pembelian (Karyawan): JPG/PNG/PDF, maks 4 MB, validasi tipe file, penyimpanan privat dengan tautan bertanda tangan sementara.
- **Realisasi:** nota wajib diunggah dalam batas hari (default 7); sistem menghitung selisih dana vs nota. Pengingat otomatis, dan pengajuan baru diblokir bila ada realisasi tertunggak.
- Pengajuan menunggu > 2 hari kerja memicu pengingat dan eskalasi; atasan yang cuti memakai delegasi (§4.3).

### 8.2 Lembur

- Input tanggal, jam mulai/selesai (lintas tengah malam otomatis +24 jam), jenis, alasan, atasan (otomatis dari hierarki).
- **Validasi:** tidak tumpang tindih dengan lembur lain; batas jam lembur per hari/minggu/bulan (konfigurasi); pengajuan retroaktif dibatasi (default 3 hari); durasi disimpan dalam **menit**.
- **Rekonsiliasi dengan absensi:** durasi disetujui dibandingkan data clock-out; selisih di atas toleransi ditandai untuk review Finance.
- Pembayaran: Finance menandai `sudah` dengan tanggal dan referensi; mendukung batch per periode dan rekap ekspor. Rumus upah lembur (pengali jam pertama/berikutnya, hari libur) disediakan sebagai parameter dan **harus divalidasi HRD** sebelum dipakai.

### 8.3 Penugasan (Tasks)

Status: `Pending Approval`, `In Progress`, `Completed`, `Rejected`. Tambahan: prioritas, `due_date` dengan pengingat dan penanda terlambat, komentar/lampiran, riwayat status, subtugas sederhana, dan tautan opsional ke tiket. Perubahan status punya aturan siapa yang boleh melakukan (pemberi tugas vs penerima).

---

## 9. Skema Database (Perubahan dari v1.0)

### 9.1 Prinsip

- Satu database; seluruh relasi FK ke `users.id` (hapus `gomsn_user_id` dan `work_report_user_id` setelah migrasi).
- Semua tabel memiliki `created_at`, `updated_at`; tabel transaksi penting memakai `deleted_at`. Uang `DECIMAL(15,2)`; koordinat `DECIMAL(10,7)`; enum sebagai kolom string + PHP Enum agar mudah diperluas.
- Indeks wajib: `attendances(user_id, date)` unik (tambahkan kolom `date`), `monthly_schedules(user_id, date)` unik, `tiket(status, kategori_tiket, tanggal_open)`, `kronologis(id_tiket, timestamp)`, `leaves(user_id, start_date)`, `ticket_assignees(user_id)`.

### 9.2 Tabel Baru

| Tabel | Isi utama |
| --- | --- |
| `roles`, `permissions`, `role_user`, `permission_role` | RBAC (§4) |
| `audit_logs` | `user_id`, `action`, `auditable_type/id`, `old_values`, `new_values`, `ip`, `user_agent`, `reason` |
| `system_settings` | `key`, `value`, `type`, `effective_from`, `updated_by` |
| `office_locations`, `shifts` | Kantor + radius; master shift |
| `user_sessions`, `user_devices` | Sesi dan perangkat terdaftar |
| `face_templates` | `user_id`, embedding terenkripsi, `model_version`, `consent_at`, `enrolled_at` |
| `ticket_sequences`, `ticket_status_history`, `ticket_assignees` | Penomoran, riwayat status, tim |
| `interval_breaches`, `material_movements` | Audit KPI dan stok |
| `pengajuan_approvals`, `approval_delegations` | Persetujuan per tahap, delegasi |
| `notification_logs`, `notification_preferences`, `message_templates` | Notifikasi |
| `period_closings` | Penguncian periode potongan |

### 9.3 Perbaikan Tabel yang Ada

- `pengajuan`, `lembur`, `tasks`: tambah `timestamps`, `deleted_at`, `approved_by`, `approved_at`, `rejected_reason`; `lembur.durasi_menit`.
- `attendances`: tambah `date`, `source` (`face`, `manual`, `offline_sync`, `correction`), `flag_reason`, `gps_accuracy_in/out`, `device_id`, `client_uuid` (unik).
- `tiket`: tambah `priority`, `reopened_count`, `cancelled_at`, `cancelled_reason`, `parent_ticket_id`; hapus `assigned_team` JSON setelah pivot tersedia.
- `leave_balances`: unik `(user_id, year)`.

---

## 10. Persyaratan Non-Fungsional

### 10.1 Kinerja dan Ketersediaan

- Halaman utama p95 ≤ 2 detik (4G); API p95 ≤ 500 ms (di luar face service); verifikasi wajah p95 ≤ 2,5 detik.
- Kapasitas awal: 500 pengguna terdaftar, 150 bersamaan, lonjakan clock-in 07:30–08:30; uji beban 3× kapasitas.
- Ketersediaan 99,5% pada jam kerja; RPO ≤ 15 menit, RTO ≤ 4 jam. Backup database harian + binlog, uji restore bulanan.

### 10.2 Keamanan dan Privasi

- HTTPS/HSTS; cookie `Secure`, `HttpOnly`, `SameSite`; CSP; proteksi CSRF, XSS, SQLi, SSRF (Nominatim/IMS hanya host daftar putih).
- Validasi unggahan (MIME, ukuran, pindai), file privat dengan tautan bertanda tangan, penghapusan metadata EXIF sensitif pada foto yang dibagikan.
- **Data biometrik (UU PDP No. 27/2022):** persetujuan eksplisit sebelum pendaftaran wajah, tujuan terbatas pada absensi, enkripsi saat disimpan, akses terbatas dan tercatat, hak penghapusan (Reset Wajah menghapus template dan foto terkait sesuai kebijakan), retensi foto absensi default 12 bulan lalu dihapus/anonimisasi.
- Rahasia (API key Fonnte, VAPID, token face service) di environment/secret manager, tidak di repositori; rotasi berkala.
- Audit log tidak dapat diubah dari UI; retensi minimal 2 tahun.

### 10.3 Aksesibilitas dan UX

Kontras minimal 4,5:1, target sentuh ≥ 44 px, dukungan pembaca layar untuk elemen utama, bahasa Indonesia sebagai bahasa utama (struktur i18n disiapkan), status offline/sinkron selalu terlihat, pesan kesalahan menyebut langkah perbaikan.

### 10.4 Notifikasi

Preferensi per pengguna (kanal dan jenis), jam tenang untuk notifikasi non-kritis, opt-in WhatsApp, dan pesan WhatsApp tanpa data sensitif berlebihan.

### 10.5 Observabilitas dan Kualitas

- Log terstruktur dengan `request_id`; error tracking (Sentry atau setara); metrik antrean, latensi face service, dan tingkat gagal notifikasi dengan peringatan ambang.
- Pengujian: unit untuk rumus (denda, cuti, MTTR, KPI), fitur untuk state machine dan RBAC, uji kontrak Laravel ↔ Face Service, E2E untuk alur kritis (clock-in, tiket sampai close, pengajuan sampai selesai). Target cakupan aturan bisnis inti ≥ 80%.
- CI/CD: lint (Pint, PHPStan level 6+), tes otomatis, build aset, migrasi terkontrol, staging sebelum produksi, *feature flag* untuk fitur berisiko.

### 10.6 Kompatibilitas

Chrome/Edge/Safari dua versi terakhir; Android 8+; iOS 15+ untuk PWA (batasan Web Push iOS didokumentasikan).

---

## 11. Design System

Gaya *Minimalism & Swiss Enterprise*, transisi 150–250 ms. Tipografi: **Outfit** (judul) dan **Work Sans** (isi), di-self-host untuk kinerja dan privasi. Token warna dipertahankan:

| Token | Nilai |
| --- | --- |
| Primary | `#1E40AF` |
| Secondary | `#3B82F6` |
| Accent/CTA | `#16A34A` |
| Background | `#EFF6FF` |
| Card | `#FFFFFF` |
| Foreground | `#1E3A8A` |
| Muted | `#475569` |
| Border | `#BFDBFE` |
| Danger | `#DC2626` |

Tambahan: token spasi/radius/bayangan, komponen baku (tabel, form, modal, toast, empty state, skeleton), mode gelap opsional di fase lanjutan, dan status tidak hanya dibedakan warna (selalu disertai ikon/teks). Kartu tiket mobile memiliki *stripe*: merah (`LEBIH`/OVERDUE), biru (`OPEN`), kuning (`PROSES`/`PENDING_VERIFIKASI`), hijau (`CLOSE`), abu-abu (`CANCELLED`).

---

## 12. Migrasi dan Rencana Rilis

### 12.1 Migrasi Data

1. Petakan `gomsn_user_id` dan `work_report_user_id` ke `users.id`; selesaikan duplikat berdasarkan `employee_code`/NIK/phone dan catat hasil rekonsiliasi.
2. Migrasi `face_embeddings.pkl` ke `face_templates` (atau minta pendaftaran ulang bila versi model berbeda); simpan persetujuan baru.
3. Migrasikan tiket historis dengan nilai `priority` dan `sla_target_minutes` snapshot; validasi total MTTR sebelum dan sesudah.
4. *Dry run* di staging, bandingkan jumlah baris dan checksum, siapkan skrip rollback.

### 12.2 Fase Rilis

| Fase | Cakupan | Estimasi |
| --- | --- | --- |
| 0 | Fondasi: auth, RBAC, `system_settings`, audit, CI/CD | 3–4 minggu |
| 1 | Absensi inti (wajah, geofence, jadwal) + pilot 1 divisi | 5–6 minggu |
| 2 | Cuti, tukar shift, koreksi, laporan potongan, leaderboard | 4–5 minggu |
| 3 | Tiketing lengkap, dispatch, Stop Clock, closing, notifikasi | 6–8 minggu |
| 4 | GOMSN, KPI/analitik, ekspor, chatbot | 4–5 minggu |
| 5 | Hardening, uji beban, pelatihan, cutover bertahap | 2–3 minggu |

Estimasi bersifat awal dan perlu dikonfirmasi tim. Kriteria keluar tiap fase: seluruh kriteria penerimaan lulus, tidak ada bug severity 1–2 terbuka, dan sign-off pemilik proses (HRD, Manager Teknis, Finance).

---

## 13. Kriteria Penerimaan Kunci (Contoh)

| ID | Skenario | Hasil yang Diharapkan |
| --- | --- | --- |
| AC-01 | Clock-in di luar radius kantor dengan tipe `office` | Ditolak, pesan menyebut jarak dan radius |
| AC-02 | Foto layar ponsel dipakai untuk clock-in | Ditolak oleh liveness, dicatat sebagai percobaan mencurigakan |
| AC-03 | Sinkronisasi offline dikirim dua kali | Hanya satu absensi tersimpan (idempoten) |
| AC-04 | Dua Helpdesk membuka tiket bersamaan | Nomor tiket unik berurutan, tanpa duplikat |
| AC-05 | Teknisi di luar tim mengakses tiket yang sudah di-dispatch | Hanya baca; aksi tulis ditolak (403) |
| AC-06 | Closing awal Backbone tanpa Joint Closure/Manuver | Ditolak dengan daftar prasyarat yang kurang |
| AC-07 | Stop Clock 3 jam tanpa persetujuan | Tidak dapat dilanjutkan; menunggu approval |
| AC-08 | Tiket ditutup dengan MTTR bersih melebihi SLA | `sla_status = LEBIH`, tampil di dashboard dengan stripe merah |
| AC-09 | Tarif denda diubah | Bulan berjalan memakai tarif baru; periode tertutup tidak berubah |
| AC-10 | Pengaju menyetujui pengajuannya sendiri | Ditolak oleh aturan pemisahan tugas |
| AC-11 | Layanan IMS tidak tersedia | Tiket Broadband dapat dibuat dengan input manual, ditandai belum tervalidasi |
| AC-12 | Reset Wajah oleh karyawan | Template dan foto terkait terhapus; tercatat di audit; pendaftaran ulang diwajibkan |

---

## 14. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Akurasi wajah rendah pada cahaya buruk | Banyak penolakan, keluhan | Skor kualitas, panduan di layar, jalur manual, kalibrasi threshold saat pilot |
| GPS tidak akurat di dalam gedung | Absen sah ditolak | Toleransi berbasis akurasi, radius per kantor, jalur exception |
| Sinyal lemah di lapangan | Kronologis terlambat | Antrean offline, kompresi media, pencatatan kondisi tanpa sinyal |
| Penolakan terhadap pengumpulan biometrik | Hambatan adopsi, risiko hukum | Consent jelas, alternatif prosedural, kebijakan retensi dan hapus |
| Migrasi data tidak konsisten | Laporan historis salah | Dry run, rekonsiliasi, rollback |
| Kebijakan potongan/lembur tidak sesuai regulasi | Sengketa dan risiko hukum | Validasi HRD/legal sebelum aktivasi; tarif dapat dikonfigurasi |
| Ketergantungan pada Fonnte/Nominatim/IMS | Notifikasi atau data tertunda | Queue + retry, cache, fallback manual, pemantauan |
| Face service menjadi titik kegagalan tunggal | Absensi berhenti | Health check, restart otomatis, replika, mode degradasi (manual review) |

---

## 15. Pertanyaan Terbuka untuk Pemilik Proses

1. Tarif keterlambatan resmi: Rp 250/menit atau Rp 83,33/menit? Apakah ada toleransi dan batas harian?
2. Apakah konversi alpa ke kuota cuti benar-benar kebijakan resmi, dan apakah karyawan boleh menyanggah?
3. Radius geofence final per kantor, dan apakah ada kantor/lokasi baru?
4. Nominal ambang persetujuan berjenjang pengajuan dana dan batas lembur.
5. Apakah SLA perlu dibedakan per prioritas dan segmen, atau tetap 360/240 menit?
6. Apakah data lama (foto, tiket) wajib dipertahankan seluruhnya, dan berapa lama?
7. Apakah perlu integrasi payroll atau ekspor ke sistem keuangan pada rilis awal?

---

## 16. Glosarium

**MTTR** (Mean Time To Repair): waktu pemulihan bersih tiket. **SLA**: target waktu layanan. **Stop Clock**: jeda sah penghitungan SLA. **Closing Awal/Akhir**: penutupan oleh teknisi / verifikasi oleh Helpdesk. **Joint Closure**: kotak sambungan kabel serat optik. **Manuver Core**: pengalihan jalur core serat. **OTDR**: alat ukur jarak dan redaman serat. **ODP/OLT**: titik distribusi / perangkat pusat jaringan akses. **Liveness**: pengecekan bahwa wajah berasal dari orang hidup. **Idempoten**: operasi aman diulang tanpa menghasilkan duplikat.
