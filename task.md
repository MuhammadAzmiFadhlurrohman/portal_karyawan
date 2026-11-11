# Task List — Portal Karyawan Terpadu PT MSN (PRD v2.0)

Dokumen ini memecah **PRD v2.0 — Portal Karyawan Terpadu PT MSN** menjadi daftar tugas bertahap (*incremental backlog*) mengikuti 6 Fase Rilis (§12.2) agar dapat dikerjakan, diuji, dan diverifikasi secara sistematis.

---

## Fase 0: Fondasi, Arsitektur, Auth, RBAC & Design System (Estimasi: 3–4 Minggu) — ✅ SELESAI
> **Target Fase 0:** Proyek monolith modular berjalan, skema dasar & audit siap, autentikasi multi-identifier + sesi multi-perangkat berfungsi, RBAC satu pintu aktif, dan komponen UI dasar tersedia.

### 0.1 Setup Proyek & Infrastruktur Dasar
- [x] Inisialisasi proyek **Laravel 12 (PHP 8.2+)** dengan struktur monolith modular: `app/Modules/{Portal,Attendance,Ticketing,Gomsn}`
- [x] Konfigurasi database utama (MySQL 8 / MariaDB / SQLite dev) dengan zona waktu penyimpanan `UTC` dan tampilan `Asia/Jakarta` (§2)
- [x] Konfigurasi koneksi database kedua `ims_v3` secara *read-only* (§2)
- [x] Setup **Redis** & antrean (*queue worker*) dan cache (§2)
- [x] Setup penyimpanan file S3-compatible / local private disk dengan *signed URL* dan penamaan file acak (§2, §10.2)
- [x] Setup CI/CD awal: Laravel Pint, PHPStan (level 6+), Pest/PHPUnit, dan *structured logging* dengan `request_id` (§10.5)

### 0.2 Design System & Layout Utama (§11)
- [x] Konfigurasi **Vite + Tailwind CSS** dengan palet token warna (`#1E40AF` Primary, `#3B82F6` Secondary, `#16A34A` Accent, `#EFF6FF` Background, `#DC2626` Danger, dll.)
- [x] Self-host font **Outfit** (heading) dan **Work Sans** (body)
- [x] Buat komponen UI dasar: Tabel, Form Input, Modal, Toast Notification, Empty State, Skeleton Loader, Status Badge (ikon + teks + warna)
- [x] Buat layout responsif *mobile-first* + PWA manifest & service worker dasar + indikator status *online/offline/syncing* (§6.4, §10.3)

### 0.3 Skema Fondasi, Konfigurasi & Audit (§4, §6.3, §9)
- [x] Buat migrasi & model `users` terpadu (unik: `phone`, `username`, `email`, `employee_code`; kolom `supervisor_id`, `join_date`, `potongan_alpa`, timestamps, soft deletes)
- [x] Integrasi `spatie/laravel-permission` (`roles`, `permissions`, `role_user`, `permission_role`) dan seeder 11 role (`super_admin`, `hrd`, `employee`, `manager_teknisi`, `helpdesk`, `sa_cs`, `teknisi`, `atasan`, `finance`, `ceo`, `noc`) beserta matriks permission `modul.sumber.aksi` (§4.1, §4.2)
- [x] Buat migrasi & layanan `audit_logs` (*append-only*, mencatat `user_id`, `action`, `auditable_type/id`, `old_values`, `new_values`, `ip`, `user_agent`, `reason`) (§4.3, §9.2)
- [x] Buat migrasi & UI Admin untuk `system_settings` (`key`, `value`, `type`, `effective_from`, `updated_by`) lengkap dengan pencatatan riwayat perubahan (§6.3)
- [x] Buat migrasi & fitur `approval_delegations` untuk delegasi persetujuan saat atasan cuti (§4.3)

### 0.4 Autentikasi, Sesi, Perangkat & Portal Hub Dasar (§6)
- [x] Implementasi login multi-identifier (`phone`, `username`, `email`, `employee_code`) + password policy (min. 10 karakter, Argon2id/bcrypt, wajib ganti password saat login pertama) (§6.1)
- [x] Implementasi *rate limiting* (5 percobaan/menit per identifier+IP, *lockout* bertahap, CAPTCHA) (§6.1)
- [x] Implementasi modul **OTP** (6 digit hash, TTL 5 menit, maks. 3 percobaan) untuk *reset password* dan persetujuan perangkat baru (§6.1)
- [x] Buat tabel & manajemen `user_sessions` (pantau sesi aktif per perangkat/IP, fitur *force logout* per sesi atau semua sesi oleh Admin) (§6.1)
- [x] Buat tabel & manajemen `user_devices` (*device binding* maks. 2 perangkat per pengguna, alur approval perangkat baru) (§5.1, §9.2)
- [x] Bangun halaman **Portal Hub** awal: kerangka kartu metrik *lazy-load* dengan cache singkat & seksi **"Perlu Tindakan Saya"** (§6.2)

---

## Fase 1: Absensi Inti (Biometrik Wajah, Geofencing, Jadwal Shift) (Estimasi: 5–6 Minggu) — ✅ SELESAI
> **Target Fase 1:** Layanan pengenalan wajah FastAPI `/v1` beroperasi aman, alur clock-in/out 2 tahap + anti-spoofing + geofencing multi-kantor + mode offline jalan, serta manajemen jadwal shift bulanan siap untuk pilot 1 divisi.

### 1.1 Layanan Mikro Pengenalan Wajah — Python FastAPI (`/v1`) (§5.1)
- [x] Setup proyek Python 3.11 + **FastAPI** + **InsightFace `buffalo_l` (ONNX)** dengan autentikasi *service-to-service* (token + IP allow-list) di `face-service/main.py`
- [x] Implementasi endpoint kontrak `/v1`:
  - [x] `GET /v1/health` (status engine, versi model, jumlah template)
  - [x] `POST /v1/detect` (jumlah wajah, bounding box, skor kualitas blur/cahaya/ukuran)
  - [x] `POST /v1/enroll` (ekstrak embedding 512-D dari 3 pose: depan, kiri, kanan)
  - [x] `POST /v1/verify` (verifikasi 1:1 terhadap `user_id` → `score`, `match`, `liveness_score`)
  - [x] `POST /v1/identify` (1:N opsional untuk deteksi duplikasi pendaftaran)
  - [x] `DELETE /v1/users/{user_id}` (hapus template biometrik)
- [x] Implementasi **Liveness Detection** pasif (deteksi layar/cetak/topeng) + validasi tantangan aktif acak (senyum, kedip, tengok) + cek konsistensi hash kedua foto (§5.1)
- [x] Standarisasi format error JSON (`{ "error": { "code": "...", "message": "..." } }`) dan integrasi *health check*

### 1.2 Pendaftaran Wajah, Privasi Biometrik & Master Lokasi (§5.1, §10.2)
- [x] Buat migrasi `face_templates` (simpan embedding terenkripsi di DB, `model_version`, `consent_at`, `enrolled_at`) (§9.2)
- [x] Buat UI & alur **Persetujuan Data Biometrik (UU PDP No. 27/2022)** dan pendaftaran wajah 3 pose (§10.2)
- [x] Implementasi fitur **Reset Wajah** (hapus template & foto terkait, catat alasan di `audit_logs`, wajibkan daftar ulang — **AC-12**)
- [x] Buat migrasi & CRUD `office_locations` (`name`, `lat`, `lng`, `radius_m`, `is_active`) + seeder kantor Soreang & Bandung (§5.1)

### 1.3 Proses Clock-In / Clock-Out & Offline Sync (§5.1)
- [x] Buat migrasi `attendances` dengan indeks unik `(user_id, date)`, kolom `source`, `flag_reason`, `gps_accuracy_in/out`, `device_id`, `client_uuid` unik (§9.1, §9.3)
- [x] Implementasi alur **Absensi 2 Tahap**:
  - [x] Tahap 1: Validasi deteksi wajah real-time & terbitkan `attempt_token` sekali pakai (TTL 2 menit)
  - [x] Tahap 2: Unggah 2 foto, koordinat GPS, `gps_accuracy`, flag `is_mock_location`, dan `attempt_token`; verifikasi ulang di server menggunakan waktu server
- [x] Implementasi aturan **Geofencing** (`jarak <= radius + min(accuracy, 15m)`) untuk tipe `office`, serta aturan tipe `field` (wajib catatan/referensi tiket) dan `remote` (kuota WFH & approval) (**AC-01**)
- [x] Implementasi *anti-fraud*: tandai `flagged` bila `gps_accuracy > 30m` atau *mock location* aktif; alihkan ke **jalur manual** (`pending_review` HRD) setelah 3x gagal verifikasi wajah (**AC-02**)
- [x] Implementasi **Offline Mode & Sync** idempoten berbasis `client_uuid` dengan validasi batas umur antrean (default 24 jam) (**AC-03**)
- [x] Buat *scheduled job* auto-close bagi karyawan yang lupa clock-out dengan status `incomplete` dan perhitungan usulan lembur (> 8 jam kerja bersih) (§5.1)

### 1.4 Jadwal Shift Bulanan & Pengingat Shift (§5.2, §5.9)
- [x] Buat migrasi & CRUD master `shifts` (`kode`, `nama`, `jam_mulai`, `jam_selesai`, `warna`, `late_tolerance_minutes`, `break_minutes`) + seeder shift (`P`, `S`, `SB`, `BJ`, `PK`, `PA`, `L`)
- [x] Buat migrasi `monthly_schedules` dengan indeks unik `(user_id, date)` & tabel hari libur nasional
- [x] Implementasi fitur Admin: **Auto-Generate Jadwal Bulanan** (dengan pratinjau & konfirmasi sebelum menimpa), kalender interaktif, deteksi konflik aturan (jeda minimum 11 jam antar-shift), dan ekspor CSV
- [x] Buat *scheduled job* **Pengingat Shift** (1 jam sebelum masuk & 20 menit sebelum pulang) (skip karyawan cuti/libur) (§5.9)

---

## Fase 2: Cuti, Tukar Shift, Koreksi, Potongan & Leaderboard (Estimasi: 4–5 Minggu)
> **Target Fase 2:** Siklus penuh administrasi kehadiran HRIS berjalan otomatis, dapat diaudit, dan transparan bagi karyawan maupun HRD.

### 2.1 Tukar Shift & Backup (4-Way Cross-Date Swap) (§5.3)
- [x] Buat migrasi tabel permintaan tukar/backup shift dengan masa kedaluwarsa otomatis (48 jam atau H-1)
- [x] Implementasi validasi tukar shift (divisi sama, bukan masa lalu, tidak ada request aktif ganda di slot sama, tidak melanggar aturan jeda shift)
- [x] Implementasi alur persetujuan berjenjang: Pemohon → Target → Atasan/Admin
- [x] Implementasi eksekusi pertukaran jadwal dalam `DB::transaction` dengan `lockForUpdate` pada 4 baris jadwal dan verifikasi *state* jadwal belum berubah

### 2.2 Manajemen Cuti & Sinkronisasi Alpa (§5.4)
- [x] Buat migrasi `leaves` dan `leave_balances` (unik `(user_id, year)`)
- [x] Implementasi aturan kuota cuti: 12 hari kerja/tahun, batas **3 hari kerja/bulan**, prorata karyawan baru, validasi tumpang tindih & kapasitas maksimum cuti bersamaan per divisi, wajib lampiran untuk cuti sakit > N hari
- [x] Implementasi fitur pembatalan cuti sebelum tanggal mulai (pengembalian saldo otomatis) & fitur kunci *override* saldo oleh Admin/HRD (wajib alasan + masuk `audit_logs`)
- [x] Buat *scheduled command* harian `leave:sync` (idempoten) untuk mendeteksi **Alpa** dan menjalankan opsi `leave.alpa_converts_to_leave` lengkap dengan notifikasi dan **masa sanggah 3 hari kerja**

### 2.3 Koreksi Absensi & Laporan Potongan (§5.5, §5.6)
- [x] Buat modul **Koreksi Status Absensi**: form pengajuan (maks. 7 hari kalender sejak kejadian), lampiran bukti, approval Atasan → HRD, update `Attendance` (`source = correction`), catat nilai sebelum/sesudah di `audit_logs`, dan hitung ulang potongan
- [x] Implementasi mesin hitung **Potongan Keterlambatan & Alpa** menggunakan `DECIMAL(15,2)` dan tarif historis berdasarkan `system_settings.effective_from` (**AC-09**)
- [x] Buat tampilan **Laporan Potongan Karyawan**: rincian harian, tautan ke absensi, tombol ajukan koreksi, dan grafik tren 6 bulan
- [x] Buat migrasi `period_closings` dan fitur **Penguncian Periode Bulanan** oleh HRD (perubahan setelah tutup periode menjadi *adjustment* bulan berikutnya — **AC-09**)

### 2.4 Leaderboard Kehadiran (§5.7)
- [x] Implementasi perhitungan peringkat bulanan, triwulanan, dan tahunan (Persentase Kehadiran DESC → Total Menit Terlambat ASC → Jumlah Hari Tepat Waktu DESC)
- [x] Tambahkan opsi *opt-out* privasi nama bagi karyawan dan fitur ekspor CSV (UTF-8 BOM)

---

## Fase 3: Modul Work Report & Tiketing Gangguan (Estimasi: 6–8 Minggu) — ✅ SELESAI
> **Target Fase 3:** Penanganan gangguan Backbone (`BDG-`) dan Broadband (`BRD-`) terstandarisasi dari buka tiket, dispatch, update 30 menit, Stop Clock, aset teknis, hingga 2-stage closing dan notifikasi multi-kanal.

### 3.1 Penomoran Tiket, Pembuatan & Integrasi IMS (§7.1)
- [x] Buat migrasi `ticket_sequences (prefix, date, last_number)`, `tiket`, `master_sla`, `ticket_assignees` (pivot), dan `ticket_status_history` (§9.2, §9.3)
- [x] Implementasi generator nomor tiket `BDG-YYYYMMDD-XXX` dan `BRD-YYYYMMDD-XXX` secara atomik dengan `lockForUpdate` (**AC-04**)
- [x] Implementasi form tiket **Backbone** (`backbone_segment`, `status_link_impact`, `wilayah`, `tipe_penanganan`, `priority` P1–P4, snapshot `sla_target_minutes` default 360 mnt)
- [x] Implementasi form tiket **Broadband** dengan pencarian pelanggan `ims_v3` (`/api/ims/customers/search` & `{cid}`), penyimpanan *snapshot* data pelanggan, serta *fallback* input manual (`unvalidated`) saat IMS gangguan (**AC-11**)
- [x] Implementasi deteksi tiket duplikat aktif (opsi tautkan sebagai *child ticket* via `parent_ticket_id` atau buat baru dengan alasan)

### 3.2 State Machine Siklus Hidup Tiket & Dispatch (§7.2)
- [x] Buat kelas terpusat `TicketStateMachine` untuk mengelola transisi status: `OPEN`, `PROSES`, `PENDING_VERIFIKASI`, `CLOSE`, `REOPENED`, `CANCELLED` beserta pencatatan otomatis ke `ticket_status_history`
- [x] Implementasi fitur **Dispatch** oleh Manager Teknis/Admin (pilih Lead & anggota tim di `ticket_assignees`, peringatan bila teknisi *overload*)
- [x] Terapkan kebijakan otorisasi (Policy): teknisi di luar tim hanya bisa *read-only* (**AC-05**), dan pemisahan tugas antara pelaku *Closing Awal* dan *Verifikasi Akhir* (§4.3)
- [x] Terapkan indikator warna *stripe* pada kartu tiket mobile (Merah: `LEBIH`/OVERDUE, Biru: `OPEN`, Kuning: `PROSES`/`PENDING_VERIFIKASI`, Hijau: `CLOSE`, Abu-abu: `CANCELLED`) (§11)

### 3.3 Kronologis, Kepatuhan 30 Menit, Stop Clock & Handover (§7.3, §7.4, §7.5)
- [x] Buat modul **Kronologis** (*append-only*) dengan kategori (`IZIN`, `OTDR`, `TRACING`, `MATERIAL`, `JOINTING`, `LINK_UP`, `SELESAI`, `LAIN`, `SYSTEM`), kompresi foto/video sisi klien, antrean offline, dan *reverse geocoding* Nominatim ber-cache
- [x] Implementasi mesin pemantau **Kepatuhan Interval 30 Menit** (`NORMAL` ≤ 20m, `WARNING` 21–30m, `OVERDUE` > 30m, `STOP_CLOCK`) dan pencatatan pelanggaran ke tabel `interval_breaches`
- [x] Implementasi modul **Stop Clock**: maksimal 1 aktif per tiket, wajib bukti lampiran untuk alasan tertentu (`IZIN_AKSES`, `PIHAK_KETIGA_PLN`, `PERMINTAAN_PELANGGAN`), wajib approval Manager Teknis/Helpdesk bila durasi > 120 menit (**AC-07**), pengingat tiap 30 menit, dan rekalkulasi `total_stop_clock_minutes` dari tabel
- [x] Implementasi fitur **Handover Shift** (`shift_from → shift_to`, ringkasan otomatis, konfirmasi `acknowledged_at`, eskalasi 15 menit ke Manager Teknis, otomatis tambah penerima ke `ticket_assignees`)

### 3.4 Aset Teknis, Material & 2-Stage Closing (§7.6)
- [x] Buat modul pemakaian **Material** dengan pengurangan stok `master_materials.stok_tersedia` secara atomik, log `material_movements`, dan peringatan stok minimum
- [x] Buat modul **Titik Perbaikan, Joint Closure, Matriks Core & Manuver Core** (validasi pasangan `tube_asal`/`core_asal` unik per closure, peringatan `loss_db > 0.3 dB`, ekspor matriks ke PDF/Excel)
- [x] Implementasi validasi foto *geotag* (watermark waktu + nomor tiket, tandai bila jarak di luar lokasi tiket) dan validasi rentang redaman dBm (-40 s.d. 0)
- [x] Implementasi layanan `checkClosingPrerequisites` yang mengembalikan daftar kekurangan rinci ke UI untuk Broadband dan Backbone (**AC-06**)
- [x] Implementasi alur **Closing Awal** (`PROSES → PENDING_VERIFIKASI`) oleh teknisi dan **Verifikasi Closing Akhir / Reject** oleh Helpdesk (`PENDING_VERIFIKASI → CLOSE / PROSES`) beserta perhitungan MTTR bersih & status SLA (`TEPAT` / `LEBIH` — **AC-08**)

### 3.5 Sistem Notifikasi Multi-Kanal & Eskalasi (§7.8, §10.4)
- [x] Buat migrasi `notification_logs`, `notification_preferences`, dan `message_templates`
- [x] Integrasi pengirim notifikasi via **Database (In-App)**, **Web Push (VAPID)**, dan **WhatsApp (Fonnte)** melalui antrean Redis dengan *exponential backoff retry*, *rate limit*, deduplikasi, dan pengaturan jam tenang (*quiet hours*)
- [x] Buat *scheduled job* eskalasi otomatis ke Manager Teknis untuk tiket tanpa dispatch > 10 menit atau `OVERDUE` berulang

---

## Fase 4: Modul GOMSN Operasional, Analitik KPI, Ekspor & Chatbot (Estimasi: 4–5 Minggu) — ✅ SELESAI
> **Target Fase 4:** Operasional pengajuan dana/barang, lembur, dan penugasan terintegrasi penuh; laporan KPI/MTTR dan Berita Acara siap diekspor; Chatbot asisten aktif.

### 4.1 Pengajuan Dana & Barang Berjenjang (§8.1)
- [x] Perbarui skema `pengajuan`, `pengajuan_dana_detail`, dan `pengajuan_approvals` dengan state machine tunggal: `draft → menunggu_atasan → menunggu_finance → dicairkan → menunggu_bukti → selesai` (+ `ditolak`, `dibatalkan`)
- [x] Implementasi kalkulasi otomatis total dari subtotal item dan aturan persetujuan berjenjang berdasarkan ambang nominal (Atasan + Finance, atau + HRD/CEO) serta pencegahan *self-approval* (**AC-10**)
- [x] Implementasi unggah **Bukti Transfer** (Finance) dan **Realisasi Nota Pembelian** (Karyawan) ke *private storage* (JPG/PNG/PDF maks. 4 MB)
- [x] Implementasi perhitungan selisih realisasi, pemblokiran pengajuan baru bila ada realisasi tertunggak > 7 hari, serta pengingat/eskalasi otomatis bila menggantung > 2 hari kerja

### 4.2 Manajemen Lembur & Rekonsiliasi Absensi (§8.2)
- [x] Perbarui skema `lembur` (`durasi_menit`, timestamps, soft deletes, approver)
- [x] Implementasi validasi pengajuan lembur: lintas tengah malam (+24 jam), bebas tumpang tindih, batas jam harian/mingguan/bulanan, dan batas pengajuan retroaktif (default 3 hari)
- [x] Implementasi fitur **Rekonsiliasi Lembur vs Clock-Out Absensi** (tandai selisih di atas toleransi untuk review Finance)
- [x] Implementasi fitur pencairan/pembayaran lembur oleh Finance (batch per periode & ekspor rekap)

### 4.3 Manajemen Penugasan Kerja / Tasks (§8.3)
- [x] Perbarui skema `tasks` (status `Pending Approval`, `In Progress`, `Completed`, `Rejected`, prioritas, `due_date`, subtugas sederhana, komentar/lampiran, dan tautan opsional ke tiket)
- [x] Implementasi aturan hak ubah status (pemberi tugas vs penerima) dan pengingat otomatis tugas mendekati/melewati `due_date`
- [x] Lengkapi seluruh kartu metrik **Portal Hub** dengan ringkasan tiket, pengajuan, lembur, dan tugas aktif (§6.2)

### 4.4 Analitik MTTR, SLA, KPI & Berita Acara (§7.7)
- [x] Implementasi kalkulator **Skor KPI Teknisi** (`0.45 × SLA + 0.35 × Interval + 0.20 × Respon`), pembagian kredit Lead vs Tim, penanganan *data tidak cukup* (< 5 tiket), dan **KPI Helpdesk**
- [x] Bangun Dashboard Analitik: tren MTTR 12 bulan, Top Segment gangguan, tipe penanganan, dampak Stop Clock, distribusi jam kejadian, handover, dan rekap material
- [x] Implementasi generator **Berita Acara PDF (DomPDF)** per tiket (kronologis, foto, matriks core, nama, waktu, dan *hash* pengesahan dokumen)
- [x] Implementasi ekspor laporan besar (PDF/Excel via **Maatwebsite Excel**) di latar belakang (*queue*) dengan notifikasi saat unduhan siap

### 4.5 Chatbot Asisten Internal (§5.8)
- [x] Buat tabel manajemen intent & respons chatbot (memuat 20+ intent bawaan yang dapat diubah Admin tanpa deploy)
- [x] Implementasi pencocokan intent dengan ambang skor minimum, saran intent + eskalasi ke Helpdesk bila di bawah ambang, *logging* pertanyaan tak terjawab, dan pembatasan akses data hanya milik pengguna yang sedang login

---

## Fase 5: Migrasi Data, Hardening, Uji Beban & Cutover (Estimasi: 2–3 Minggu)
> **Target Fase 5:** Data historis dari sistem lama termigrasi secara akurat, seluruh NFR & kriteria penerimaan (AC-01 s.d. AC-12) lulus, dan sistem siap *go-live*.

### 5.1 Skrip Migrasi & Rekonsiliasi Data (§12.1)
- [x] Buat skrip pemetaan dan penggabungan akun pengguna (`gomsn_user_id` & `work_report_user_id` → `users.id`) berdasarkan `employee_code` / NIK / `phone` beserta laporan rekonsiliasi duplikat
- [x] Buat alur migrasi `face_embeddings.pkl` ke tabel `face_templates` terenkripsi (atau mekanisme wajib *re-enroll* + *consent* baru saat login pertama)
- [x] Buat skrip migrasi tiket historis lengkap dengan *snapshot* `priority`, `sla_target_minutes`, konversi `assigned_team` JSON ke tabel pivot `ticket_assignees`, dan validasi kesesuaian total MTTR sebelum vs sesudah migrasi
- [x] Lakukan *Dry Run* migrasi di server *staging*, verifikasi jumlah baris & *checksum*, dan uji skrip *rollback*

### 5.2 Pengujian Menyeluruh & Security Hardening (§10, §13)
- [x] Tulis dan jalankan **Unit Tests** (cakupan aturan bisnis inti ≥ 80%): rumus potongan, kuota cuti & konversi alpa, MTTR bersih, dan rumus KPI (§10.5)
- [x] Tulis dan jalankan **Feature & Contract Tests**: transisi state machine tiket & pengajuan, otorisasi RBAC, serta kontrak API Laravel ↔ FastAPI Face Service (§10.5)
- [x] Jalankan **End-to-End (E2E) Verification** untuk seluruh Kriteria Penerimaan Kunci (**AC-01 s.d. AC-12**) (§13)
- [x] Lakukan **Load Testing** (3× kapasitas: simulasi lonjakan clock-in 07:30–08:30 WIB dan 150 pengguna konkuren) untuk memastikan target p95 tercapai (§10.1)
- [x] Audit keamanan: HTTPS/HSTS, CSP, cookie flags, daftar putih SSRF (Nominatim/IMS), penghapusan EXIF sensitif, rotasi secret/token, dan *scheduled job* retensi foto absensi 12 bulan (§10.2)

### 5.3 Pelatihan, Pilot & Cutover Produksi (§12.2)
- [x] Konfirmasi keputusan atas **7 Pertanyaan Terbuka Pemilik Proses** (§15) bersama HRD, Manager Teknis, dan Finance, lalu masukkan nilainya ke `system_settings`
- [x] Siapkan konfigurasi infrastruktur produksi: backup DB harian + binlog, monitoring/error tracking (Sentry), *health check* & auto-restart untuk FastAPI Face Service
- [x] Sosialisasi, panduan pengguna, *sign-off* UAT dari pemilik proses (HRD, Manager Teknis, Finance), dan pelaksanaan *cutover* bertahap

Point 6: Profesional & Elegan Design
Berikut panduan desain untuk mencapai tampilan profesional dan elegan:
- [x] Palet Warna Sophisticated: Navy Blue (#1B2A41), Charcoal (#2E2E2E), Ivory (#F8F6F2), Beige (#E8E1D5), Deep Emerald (#1F4E45), Subtle Gold (#C9A961)
- [x] Tipografi Berkelas: Playfair Display (Serif Headings, letter-spacing 1-2px) + Inter (Clean Sans-Serif Body, line-height 1.6-1.8)
- [x] White space & layout bersih: 12-column grid, generous padding
- [x] Garis & ornamen minimalis: divider 1px #E8E1D5, shadow 0 4px 20px rgba(0,0,0,0.06), border radius 8-12px
- [x] Kualitas visual & aksesibilitas WCAG AA (kontras min 4.5:1, text body min 16px)
- [x] Konsistensi brand across all routes & layouts

1. Palet Warna yang Sophisticated
Gunakan warna netral & muted: navy, charcoal, ivory, beige, atau deep emerald

Aksen emas/rose gold tipis untuk kesan premium

Hindari warna neon atau terlalu mencolok

Konsisten: maksimal 3 warna utama + 1 aksen

Warna Utama:
- Navy Blue    : #1B2A41
- Charcoal     : #2E2E2E
- Ivory        : #F8F6F2
- Beige        : #E8E1D5
- Deep Emerald : #1F4E45

Warna Aksen:
- Gold         : #C9A961
- Rose Gold    : #B76E79

Aturan:
- Maksimal 3 warna utama + 1 aksen
- Hindari warna neon / terlalu mencolok
- Gunakan aksen emas secukupnya (jangan berlebihan)

2. Tipografi yang Berkelas
Kombinasi font serif elegan (Playfair Display, Cormorant) + sans-serif bersih (Inter, Montserrat)

Hierarki jelas: heading besar, subheading medium, body readable

Spasi huruf (letter-spacing) longgar untuk heading

Hindari lebih dari 2 jenis font

Kombinasi Font:
- Heading  : Playfair Display / Cormorant Garamond (serif)
- Body     : Inter / Montserrat (sans-serif)

Hierarki Ukuran:
- H1        : 48–56px, bold, letter-spacing 1–2px
- H2        : 32–40px, semibold
- H3        : 24–28px, medium
- Body      : 16px, line-height 1.6–1.8
- Caption   : 13–14px, letter-spacing 0.5px

Aturan:
- Maksimal 2 jenis font
- Letter-spacing longgar untuk heading (elegan)
- Hindari font dekoratif berlebihan

3. White Space & Layout Bersih
Beri ruang napas antar elemen (padding/margin generous)

Grid konsisten dan alignment rapi

Hindari kepadatan berlebihan — less is more

Fokus pada satu pesan utama per section

Spacing:
- Section padding   : 80–120px (atas-bawah)
- Antar elemen      : 24–32px
- Padding card      : 32–40px
- Grid gap          : 24–32px

Aturan:
- Beri ruang napas antar elemen
- Alignment konsisten (grid 12 kolom)
- Satu pesan utama per section
- Prinsip: Less is More

4. Garis & Ornamen Minimalis
Gunakan divider tipis (1px) daripada border tebal

Ornamen geometris halus (garis emas, sudut membulat lembut)

Shadow subtle dan soft, bukan hard shadow

Ikon line-style tipis, konsisten ketebalan

Garis:
- Divider        : 1px solid #E8E1D5
- Border card    : 1px solid rgba(0,0,0,0.08)
- Aksen garis    : 1px–2px warna gold (#C9A961)

Shadow (subtle):
- Card   : 0 4px 20px rgba(0,0,0,0.06)
- Hover  : 0 8px 30px rgba(0,0,0,0.10)

Border Radius:
- Button  : 4–6px (tegas) atau 50px (pill, lembut)
- Card    : 8–12px

Ikon:
- Line-style, ketebalan konsisten (1.5–2px)
- Ukuran seragam (20–24px)

5. Kualitas Visual & Detail
Foto/gambar resolusi tinggi dengan tone konsisten

Animasi halus & subtle (fade, slide lambat) — bukan berlebihan

Hover state yang elegan (perubahan warna lembut, underline animasi)

Pastikan kontras teks nyaman dibaca (WCAG AA)

Gambar:
- Resolusi tinggi (min. 2x retina)
- Tone/warna konsisten di semua foto
- Gunakan overlay gelap tipis untuk teks di atas gambar

Animasi:
- Durasi   : 200–400ms
- Easing   : ease-in-out / cubic-bezier(0.4, 0, 0.2, 1)
- Efek     : fade, slide halus — hindari bounce/berlebihan

Interaksi:
- Hover button   : perubahan warna lembut + transisi 200ms
- Link           : underline animasi (bukan langsung muncul)

Aksesibilitas:
- Kontras teks min. 4.5:1 (WCAG AA)
- Ukuran teks body min. 16px

6. Konsistensi Brand
Checklist:
[ ] Style guide diterapkan di semua halaman
[ ] Button, form, card seragam (warna, radius, spacing)
[ ] Tone komunikasi formal namun ramah
[ ] Detail kecil on-brand: favicon, loading state, error message
[ ] Warna & font tidak berubah antar halaman
[ ] Semua ikon dari satu keluarga (mis. Lucide, Feather)

