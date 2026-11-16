# Dokumentasi Spesifikasi Fitur & Arsitektur Backend

Dokumen ini merangkum seluruh fitur, arsitektur backend, skema database, relasi model, aturan bisnis (*business logic*), *service layer*, sistem notifikasi, integrasi eksternal, serta daftar endpoint/routes pada aplikasi **Work Report / Tiketing Gangguan Jaringan** (Backbone Transmission & Broadband Maintenance).

Dokumen ini sengaja disusun **tanpa membahas tampilan/UI (HTML, CSS, maupun Blade view)** agar dapat dijadikan cetak biru (*blueprint*) untuk menambahkan atau memigrasikan fitur-fitur aplikasi ini ke aplikasi lain.

---

## 1. Ringkasan Sistem & Dependensi Backend

### 1.1 Tech Stack & Dependensi (`composer.json`)
| Komponen | Spesifikasi / Paket | Kegunaan di Aplikasi |
| :--- | :--- | :--- |
| **Runtime** | PHP `^8.2` | Bahasa pemrograman utama |
| **Framework** | `laravel/framework` `^12.0` | Arsitektur MVC, Eloquent ORM, Queue, Notifications, Routing, Auth |
| **Database Utama** | MySQL / MariaDB (kompatibel SQLite untuk testing) | Penyimpanan operasional tiketing (`work_report`) |
| **Database Sekunder** | MySQL (`ims` connection -> `ims_v3`) | Lookup data pelanggan broadband eksisting |
| **Export PDF** | `barryvdh/laravel-dompdf` `^3.1` | Generate Berita Acara internal/pelanggan, rekap tiket, rekap shift, & laporan KPI |
| **Export Excel** | `maatwebsite/excel` `^3.1` | Generate spreadsheet `.xlsx` untuk data tiket, KPI, dan handover shift |
| **Web Push Notification** | `minishlink/web-push` `^11.0` | Pengiriman notifikasi push berbasis VAPID ke browser & mobile PWA |
| **Ekstensi PHP Wajib** | `gd`, `exif`, `pdo_mysql` | Kompresi/resize gambar di server & ekstraksi koordinat GPS dari metadata EXIF foto |
| **Timezone Default** | `Asia/Jakarta` (`config/app.php`) | Seluruh kalkulasi timestamp, SLA, dan penomoran tiket menggunakan WIB |

### 1.2 Dual Kategori Tiket
Aplikasi menangani dua kategori tiket gangguan dalam satu tabel dan alur terpadu:
1. **`BACKBONE` (Backbone Transmission)**
   - Format nomor tiket: `BDG-YYYYMMDD-XXX` (contoh: `BDG-20260914-001`).
   - Fokus pada gangguan segmen jaringan transmisi/kabel fiber optik backbone, pencatatan *Joint Closure* (JC), matriks *core*, *manuver core*, titik koordinat perbaikan, dan material.
   - Default target SLA diambil dari tabel `master_sla` berdasarkan `backbone_segment` (fallback `360` menit / 6 jam).
2. **`BROADBAND` (Maintenance User Broadband)**
   - Format nomor tiket: `BRD-YYYYMMDD-XXX` (contoh: `BRD-20260914-001`).
   - Fokus pada gangguan layanan pelanggan broadband retail/korporat, terintegrasi dengan database pelanggan IMS (`id_pelanggan`/CID, ODP, POP, bandwidth, SN ONT, koordinat, serta pengukuran redaman optik sebelum & sesudah dalam dBm).
   - Default target SLA: `240` menit (4 jam), dikelompokkan berdasarkan `wilayah` (`Bandung` / `Soreang`).

### 1.3 Service Container Binding (`AppServiceProvider`)
Di `app/Providers/AppServiceProvider.php`, `TiketService` dan `KronologisService` di-bind secara eksplisit ke Service Container agar menerima `NotificationService` melalui *constructor injection*.

---

## 2. Role, Autentikasi, & Hak Akses (RBAC)

### 2.1 Mekanisme Autentikasi (`AuthController`)
- **Dual Login Field** (`LoginRequest`): Input `email` menerima alamat **email** ataupun **username**. Sistem mendeteksi apakah input valid sebagai email (`FILTER_VALIDATE_EMAIL`), lalu mencoba autentikasi ke kolom `email` atau `username`, dan fallback ke kolom alternatif jika percobaan pertama gagal.
- **Validasi Status Aktif**: User dengan `is_active = false` ditolak saat login (HTTP `403` untuk AJAX / redirect error untuk web) dan otomatis di-logout oleh `RoleMiddleware` jika dinonaktifkan saat sesi berjalan.
- **Session & CSRF**: Logout menggunakan route `GET`/`POST /logout` dengan pengecualian CSRF pada `/logout` dan `logout` (`bootstrap/app.php`), serta `trustProxies(at: '*')`.

### 2.2 Daftar Role (`users.role`)
Berdasarkan migrasi terbaru (`2026_09_23_000001` & `2026_09_28_000001`), terdapat **5 role aktif** (role `client` telah dihapus):

| Role | Label Indonesia | Hak Akses Utama |
| :--- | :--- | :--- |
| `admin` | Admin Sistem | Full access: CRUD master data (Segment, SLA, Material, User), hapus tiket (`destroy`), open/edit/close/reject tiket, dispatch tim, stop clock, handover shift, input semua modul, & monitoring seluruh user. |
| `manager_teknisi` | Manager / Koordinator Teknis | Dispatch/menugaskan tim teknisi (`assignTeknisi`), closing awal (`closingAwal`), stop clock, handover shift, input koordinasi/kronologis/resume/material/JC/manuver/dokumentasi, serta monitoring seluruh data & KPI karyawan. |
| `helpdesk` | HelpDesk NOC / SA & CS | Open tiket (`create`/`store`), edit tiket saat masih `OPEN`, verifikasi & closing akhir (`close` / `rejectClosingAwal`), stop clock, handover shift, input koordinasi/teknis, & monitoring tiket yang dibuat/ditutup/dikoordinasikannya. |
| `teknis` | Team Teknis Lapangan | Melihat tiket yang sudah di-dispatch (`assigned_lead_id IS NOT NULL`) atau sudah `CLOSE`, mengirim update kronologis (khusus tiket yang ditugaskan padanya), mengisi resume, material, titik perbaikan, joint closure, manuver core, dokumentasi, stop clock, handover shift, & melakukan `closingAwal`. |
| `sa_cs` | HelpDesk NOC / SA & CS | Memantau semua tiket (read), input kronologis/koordinasi, export PDF/Excel, dan melihat laporan dengan scope monitoring miliknya (`created_by`, `closed_by`, atau yang berisi kronologisnya). |

### 2.3 Middleware `RoleMiddleware` (`alias: role`)
Terdaftar di `bootstrap/app.php` dengan signature `role:role1,role2` atau `role:role1|role2`:
1. Jika belum login (`!Auth::check()`), redirect ke route `login`.
2. Jika `!$user->is_active`, logout paksa, invalidate session, dan redirect ke `login`.
3. Jika parameter `$roles` kosong, izinkan semua user yang login & aktif.
4. Jika `$roles` diisi, periksa `$user->hasRole($allowedRoles)`. Jika gagal, `abort(403)`.

### 2.4 Data Visibility & Scoping pada Model `Tiket`
Terdapat tiga mekanisme pembatasan akses data pada `App\Models\Tiket`:

1. **`scopeVisibleForUser(?User $user)`** (Daftar Tiket & Dashboard):
   - Jika role user adalah `teknis`: hanya menampilkan tiket yang **sudah ditugaskan oleh Manager Teknis** (`assigned_lead_id IS NOT NULL`) **ATAU** tiket berstatus `CLOSE`. Tiket baru yang belum di-dispatch disembunyikan dari teknisi.
   - Pada `TiketController@index`, jika user `teknis` tidak memilih filter `status=AKTIF`, status otomatis dipaksa menjadi `CLOSE` (mode History).
   - Pada `TiketController@show`, jika user `teknis` mengakses tiket yang belum di-dispatch dan belum `CLOSE`, sistem me-redirect kembali ke `tiket.index` dengan pesan warning.
   - Role lain (`admin`, `manager_teknisi`, `helpdesk`, `sa_cs`): melihat semua tiket.

2. **`scopeForUserMonitoring(?User $user)`** (Laporan, Statistik MTTR, KPI, Export):
   - `admin` dan `manager_teknisi`: melihat **seluruh** data tiket & evaluasi seluruh karyawan.
   - `teknis`: hanya tiket di mana user menjadi `assigned_lead_id`, `resolved_by`, tercantum dalam JSON array `assigned_team` (`whereJsonContains` integer/string), atau pernah mengirim `kronologis`.
   - `helpdesk` dan `sa_cs`: hanya tiket di mana user menjadi `created_by`, `closed_by`, atau pernah mengirim `kronologis`.

3. **`canUserUpdateKoordinasi(?User $user)`** (Otorisasi Input Kronologis):
   - `admin`, `helpdesk`, `sa_cs`, `manager_teknisi`: selalu diizinkan (`true`).
   - `teknis`: diizinkan (`true`) **hanya jika** tiket sudah di-dispatch (`!empty($this->assigned_lead_id)`) **DAN** user merupakan Lead atau Anggota Tim (`in_array($user->id, $this->getAllAssignedUserIds(), true)`). Teknisi lain hanya berstatus *read-only* (HTTP `403` saat mencoba `POST` kronologis).

---

## 3. Skema Database & Relasi Model

Database utama menggunakan **16 tabel aplikasi** + tabel `notifications` bawaan Laravel (serta tabel infrastruktur Laravel: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`).

### 3.1 Daftar Tabel & Struktur Kolom

#### 1. `users`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID pengguna |
| `name` | `VARCHAR(255)` | NOT NULL | Nama lengkap |
| `username` | `VARCHAR(100)` | UNIQUE, NULLABLE | Username opsional untuk login |
| `email` | `VARCHAR(255)` | UNIQUE, NOT NULL | Alamat email |
| `email_verified_at` | `TIMESTAMP` | NULLABLE | Waktu verifikasi email |
| `password` | `VARCHAR(255)` | NOT NULL | Hash password (cast `hashed`) |
| `role` | `ENUM` | `'admin','helpdesk','teknis','sa_cs','manager_teknisi'`, default `'teknis'`, INDEX | Peran RBAC |
| `phone` | `VARCHAR(20)` | NULLABLE | Nomor HP/WhatsApp |
| `avatar` | `VARCHAR(255)` | NULLABLE | Path relatif di disk `public` (`avatars/...`) |
| `is_active` | `BOOLEAN` | DEFAULT `true` | Status aktif akun |
| `remember_token` | `VARCHAR(100)` | NULLABLE | Token remember me |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 2. `tiket`
Merupakan tabel inti hasil penggabungan migrasi awal (`2026_09_18_000001`) dengan migrasi fase lanjutan (`2026_09_24_000001`, `2026_09_28_000001`, `2026_09_28_145616`, `2026_09_30_103422`):

| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID tiket |
| `no_tiket` | `VARCHAR(20)` | UNIQUE, NOT NULL | `BDG-YYYYMMDD-XXX` atau `BRD-YYYYMMDD-XXX` |
| `kategori_tiket` | `VARCHAR(30)` | DEFAULT `'BACKBONE'`, INDEX | `'BACKBONE'` atau `'BROADBAND'` |
| `wilayah` | `VARCHAR(50)` | DEFAULT `'Bandung'`, INDEX | Wilayah operasional (`Bandung` / `Soreang`) |
| `id_pelanggan` | `VARCHAR(50)` | NULLABLE, INDEX | CID / Nomor Internet pelanggan (Broadband) |
| `nama_pelanggan` | `VARCHAR(200)` | NULLABLE, INDEX | Nama pelanggan (Broadband) |
| `no_kontak_pelanggan` | `VARCHAR(50)` | NULLABLE | No HP pelanggan (Broadband) |
| `alamat_pelanggan` | `TEXT` | NULLABLE | Alamat lengkap instalasi pelanggan |
| `titik_odp` | `VARCHAR(100)` | NULLABLE | Info OLT / ODP pelanggan |
| `kode_bandwith` | `VARCHAR(50)` | NULLABLE | Kode/nominal bandwidth |
| `kode_pop` | `VARCHAR(50)` | NULLABLE | Kode POP asal |
| `lon_lat_pelanggan` | `TEXT` | NULLABLE | Koordinat / link lokasi pelanggan |
| `sn_ont` | `VARCHAR(100)` | NULLABLE | Serial Number ONT/Modem |
| `jenis_kendala_broadband` | `VARCHAR(100)` | NULLABLE | Kategori kendala broadband |
| `redaman_sebelum` | `VARCHAR(20)` | NULLABLE | Pengukuran redaman optik sebelum (dBm) |
| `redaman_sesudah` | `VARCHAR(20)` | NULLABLE | Pengukuran redaman optik sesudah (dBm) |
| `status_link_impact` | `VARCHAR(150)` | NOT NULL | Ringkasan dampak link/layanan |
| `backbone_segment` | `VARCHAR(100)` | NOT NULL, INDEX | Nama segment backbone / POP |
| `deskripsi` | `TEXT` | NULLABLE | Deskripsi gangguan awal |
| `closing_notes_teknisi` | `TEXT` | NULLABLE | Catatan closing awal dari teknisi |
| `tipe_penanganan` | `VARCHAR(50)` | DEFAULT `'JOINTING_LURUS'` | `JOINTING_LURUS`, `MANUVER_CORE`, `KEDUA`/`KOMBINASI`/`SEMUA`, `MAINTENANCE_BROADBAND`, `LAINNYA` |
| `tanggal_open` | `DATETIME` | NOT NULL, INDEX | Waktu mulai gangguan |
| `first_response_at` | `DATETIME` | NULLABLE | Waktu respon pertama oleh teknisi |
| `response_time_minutes` | `INT` | NULLABLE | Selisih menit `tanggal_open` -> `first_response_at` |
| `resolved_at` | `DATETIME` | NULLABLE, INDEX | Waktu Closing Awal oleh teknisi |
| `tanggal_close` | `DATETIME` | NULLABLE, INDEX | Waktu Closing Akhir oleh HelpDesk/Admin |
| `status` | `VARCHAR(30)` | DEFAULT `'OPEN'`, INDEX | `'OPEN'`, `'PROSES'`, `'PENDING_VERIFIKASI'`, `'CLOSE'` |
| `assigned_lead_id` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | PIC / Lead teknisi lapangan |
| `assigned_team` | `JSON` | NULLABLE | Array ID user anggota tim lapangan |
| `assigned_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | Manager Teknis / Admin yang menugaskan |
| `assigned_at` | `TIMESTAMP` | NULLABLE | Waktu penugasan |
| `catatan_dispatch` | `TEXT` | NULLABLE | Instruksi penugasan dari Manager Teknis |
| `dispatch_status` | `VARCHAR(20)` | DEFAULT `'unassigned'`, INDEX | `'unassigned'`, `'assigned'`, `'in_progress'`, `'resolved'` |
| `mttr_minutes` | `INT` | NULLABLE | Durasi MTTR bersih (setelah dikurangi Stop Clock) |
| `total_stop_clock_minutes` | `INT` | DEFAULT `0` | Akumulasi total menit jeda SLA |
| `is_stop_clock` | `BOOLEAN` | DEFAULT `false`, INDEX | Flag apakah tiket sedang dalam mode Stop Clock |
| `sla_target_minutes` | `INT` | NULLABLE | Target SLA dalam menit |
| `sla_status` | `ENUM` | `'TEPAT','LEBIH','NA'`, default `'NA'`, INDEX | Hasil evaluasi kepatuhan SLA |
| `created_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | Pembuat tiket |
| `resolved_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | Teknisi yang melakukan Closing Awal |
| `closed_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | HelpDesk/Admin yang melakukan Closing Akhir |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 3. `kronologis`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID entri kronologis |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `timestamp` | `DATETIME` | NOT NULL, INDEX | Waktu kejadian/update |
| `user_id` | `BIGINT UNSIGNED` | FK `users(id)` | Pengirim update |
| `kategori` | `ENUM` | `'IZIN','OTDR','TRACING','MATERIAL','JOINTING','LINK_UP','SELESAI','LAIN'`, INDEX | Kategori aktivitas |
| `informasi` | `TEXT` | NOT NULL | Isi laporan koordinasi |
| `foto_url` | `VARCHAR(255)` | NULLABLE | Path relatif di `public/uploads/kronologis` |
| `video_url` | `VARCHAR(255)` | NULLABLE | Path relatif di `public/uploads/kronologis/videos` |
| `latitude` | `DECIMAL(10,8)` | NULLABLE | Koordinat latitude GPS |
| `longitude` | `DECIMAL(11,8)` | NULLABLE | Koordinat longitude GPS |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 4. `resume_pekerjaan`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID resume |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi 1-to-1 ke tiket |
| `tipe_penanganan` | `VARCHAR(50)` | NULLABLE | Tipe penanganan fisik |
| `joint_closure_type` | `VARCHAR(100)` | NULLABLE | Jenis joint closure yang digunakan |
| `core_count_jointed` | `INT` | NULLABLE | Jumlah core yang disambung |
| `team_om` | `TEXT` | NULLABLE | JSON array nama-nama teknisi pelaksana |
| `problem_temuan` | `TEXT` | NULLABLE | Temuan akar masalah di lapangan |
| `action` | `TEXT` | NULLABLE | Tindakan perbaikan yang dilakukan |
| `catatan_tambahan` | `TEXT` | NULLABLE | Catatan tambahan opsional |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

> **Catatan Migrasi**: Model `ResumePekerjaan` juga mencantumkan `'redaman_sebelum'` dan `'redaman_sesudah'` di `$fillable` (disimpan juga di tabel `tiket`). Jika ingin menyimpannya di tabel `resume_pekerjaan` pada aplikasi target, tambahkan kedua kolom `VARCHAR(20) NULL` tersebut pada migrasi `resume_pekerjaan`.

#### 5. `material`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID pemakaian material |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `nama_material` | `VARCHAR(100)` | NOT NULL | Nama material yang digunakan |
| `jumlah` | `INT` | NOT NULL | Kuantitas terpakai |
| `satuan` | `VARCHAR(20)` | NOT NULL | Satuan (pcs, meter, unit, dll.) |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 6. `titik_perbaikan`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID titik perbaikan |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `nama_titik` | `VARCHAR(50)` | NOT NULL | Nama titik (mis. `JC1`, `JC2`, `Span 12`) |
| `latitude` | `DECIMAL(10,8)` | NOT NULL | Latitude lokasi perbaikan |
| `longitude` | `DECIMAL(11,8)` | NOT NULL | Longitude lokasi perbaikan |
| `keterangan` | `VARCHAR(255)` | NULLABLE | Keterangan titik |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 7. `tiket_joint_closures`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID Joint Closure |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `nama_closure` | `VARCHAR(100)` | NOT NULL | Kode/Nama closure (mis. `JC-BBLU-04A`) |
| `tipe_closure` | `ENUM` | `'DOME','INLINE','BOX_FAT','OTB','CLOSURE_STANDAR'`, default `'DOME'` | Tipe enclosure fisik |
| `status_aset` | `ENUM` | `'EKSISTING','ASET_BARU'`, default `'EKSISTING'` | Penanda aset baru atau eksisting |
| `kapasitas_kabel_asal` | `SMALLINT UNSIGNED` | DEFAULT `96` | Kapasitas core kabel asal (`2..288`) |
| `jumlah_tube_asal` | `SMALLINT UNSIGNED` | DEFAULT `8` | Jumlah tube kabel asal |
| `kapasitas_kabel_jumper` | `SMALLINT UNSIGNED` | DEFAULT `48` | Kapasitas core kabel jumper (`2..288`) |
| `jumlah_tube_jumper` | `SMALLINT UNSIGNED` | DEFAULT `4` | Jumlah tube kabel jumper |
| `jenis_sambungan` | `ENUM` | `'LURUS_STRAIGHT','PERCABANGAN_BRANCH','LOOP_MANUVER'`, default `'LURUS_STRAIGHT'` | Topologi sambungan |
| `lokasi_penempatan` | `VARCHAR(150)` | NULLABLE | Lokasi fisik (Tiang, Manhole, Handhole, Pole) |
| `latitude` | `DECIMAL(10,8)` | NULLABLE | Koordinat latitude |
| `longitude` | `DECIMAL(11,8)` | NULLABLE | Koordinat longitude |
| `catatan` | `TEXT` | NULLABLE | Catatan tambahan |
| `created_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | User pencatat |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 8. `tiket_joint_closure_cores`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID sambungan core |
| `id_joint_closure` | `BIGINT UNSIGNED` | FK `tiket_joint_closures(id)` `ON DELETE CASCADE` | Relasi parent closure |
| `tube_asal` | `VARCHAR(30)` | NOT NULL | Nomor/nama tube kabel asal |
| `core_asal` | `VARCHAR(30)` | NOT NULL | Nomor/nama core kabel asal |
| `tube_tujuan` | `VARCHAR(30)` | NULLABLE | Nomor/nama tube kabel tujuan/jumper |
| `core_tujuan` | `VARCHAR(30)` | NULLABLE | Nomor/nama core kabel tujuan/jumper |
| `status_core` | `ENUM` | `'TERHUBUNG','SPARE','LOSS_PUTUS','MANUVER'`, default `'TERHUBUNG'` | Status core |
| `loss_db` | `DECIMAL(5,2)` | NULLABLE | Redaman sambungan (dB) |
| `keterangan` | `VARCHAR(255)` | NULLABLE | Alokasi link / keterangan port |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 9. `manuver_core`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID record manuver |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `lokasi_tipe` | `ENUM` | `'POP','OTB','CLOSURE_LAPANGAN','FAT_FDT','LAINNYA'`, default `'CLOSURE_LAPANGAN'` | Tipe titik lokasi manuver |
| `titik` | `VARCHAR(50)` | NOT NULL | Nama titik (mis. `JC1`, `OTB-POP`) |
| `core_asal` | `VARCHAR(50)` | NOT NULL | Mapping core asal |
| `core_tujuan` | `VARCHAR(50)` | NOT NULL | Mapping core tujuan |
| `core_dialihkan` | `VARCHAR(50)` | NULLABLE | Core cadangan yang dipakai |
| `titik_kembali` | `VARCHAR(100)` | NULLABLE | Titik normalisasi jalur |
| `tipe` | `ENUM` | `'SEBELUM','SESUDAH'` | Kondisi sebelum atau sesudah manuver |
| `status_manuver` | `ENUM` | `'TEMPORARY','PERMANENT','RESTORED'`, default `'TEMPORARY'` | Status keberlanjutan manuver |
| `status_core_aset` | `ENUM` | `'OCCUPIED_MANUVER','BROKEN_LOSS','SPARE_AVAILABLE'`, default `'OCCUPIED_MANUVER'` | Status aset core |
| `keterangan` | `TEXT` | NULLABLE | Catatan manuver |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 10. `dokumentasi`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID dokumentasi |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `kategori` | `VARCHAR(100)` | NOT NULL | Mis. `Bukti Masalah`, `Hasil Jointing`, `Closure Terpasang`, `Kondisi Lokasi`, `Hasil OTDR`, `Lain-lain` |
| `file_path` | `VARCHAR(255)` | NOT NULL | Path relatif di disk `public` (`dokumentasi/{id_tiket}/...`) |
| `timestamp` | `DATETIME` | NOT NULL, INDEX | Waktu dokumentasi |
| `latitude` | `DECIMAL(10,8)` | NULLABLE | Latitude GPS (input manual atau auto-EXIF) |
| `longitude` | `DECIMAL(11,8)` | NULLABLE | Longitude GPS (input manual atau auto-EXIF) |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 11. `tiket_stop_clocks`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID histori Stop Clock |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `start_time` | `DATETIME` | NOT NULL, INDEX | Waktu mulai jeda SLA |
| `end_time` | `DATETIME` | NULLABLE, INDEX | Waktu selesai jeda SLA |
| `duration_minutes` | `INT` | DEFAULT `0` | Durasi jeda dalam menit |
| `alasan_kategori` | `VARCHAR(100)` | NOT NULL | `IZIN_AKSES`, `PIHAK_KETIGA_PLN`, `CUACA_EKSTRIM`, `MENUNGGU_MATERIAL`, `PERMINTAAN_PELANGGAN`, `FORCE_MAJEURE`, dll. |
| `alasan_detail` | `TEXT` | NULLABLE | Keterangan rinci alasan jeda |
| `requested_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | User yang memulai jeda |
| `stopped_by` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | User yang menghentikan jeda |
| `is_active` | `BOOLEAN` | DEFAULT `true`, INDEX | Menandai Stop Clock yang sedang berjalan |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 12. `tiket_handover_shifts`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID histori oper shift |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `shift_from` | `VARCHAR(50)` | NOT NULL | Nama shift asal |
| `shift_to` | `VARCHAR(50)` | NOT NULL | Nama shift tujuan |
| `user_from_id` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | Petugas yang menyerahkan |
| `user_to_id` | `BIGINT UNSIGNED` | NULLABLE, FK `users(id)` `NULL ON DELETE` | Petugas penerima |
| `catatan_handover` | `TEXT` | NOT NULL | Kondisi lapangan & tindak lanjut |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 13. `master_sla`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID master segment & SLA |
| `backbone_segment` | `VARCHAR(100)` | UNIQUE, NOT NULL | Nama segmen backbone |
| `sla_target_minutes` | `INT` | NOT NULL | Target SLA dalam menit |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 14. `master_materials`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID master material |
| `kode_material` | `VARCHAR(50)` | UNIQUE, NOT NULL | Kode SKU material (disimpan uppercase) |
| `nama_material` | `VARCHAR(150)` | NOT NULL | Nama material |
| `satuan` | `VARCHAR(50)` | DEFAULT `'Pcs'` | Satuan default |
| `stok_tersedia` | `INT` | DEFAULT `0` | Stok gudang |
| `keterangan` | `TEXT` | NULLABLE | Deskripsi material |
| `is_active` | `BOOLEAN` | DEFAULT `true` | Status aktif master material |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 15. `notifikasi_log`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID log notifikasi |
| `id_tiket` | `BIGINT UNSIGNED` | FK `tiket(id)` `ON DELETE CASCADE` | Relasi tiket |
| `tipe` | `ENUM` | `'WA','EMAIL','INAPP'`, INDEX | Channel notifikasi |
| `penerima` | `VARCHAR(100)` | NOT NULL | Nama/role atau nomor telepon tujuan |
| `pesan` | `TEXT` | NOT NULL | Konten pesan terkirim |
| `status` | `ENUM` | `'SENT','FAILED'`, INDEX | Status pengiriman |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 16. `push_subscriptions`
| Kolom | Tipe Data | Constraint / Default | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | PK, AI | ID subscription perangkat |
| `user_id` | `BIGINT UNSIGNED` | FK `users(id)` `ON DELETE CASCADE`, INDEX | Pemilik perangkat |
| `endpoint` | `VARCHAR(500)` | UNIQUE, NOT NULL | Push service endpoint URL |
| `public_key` | `VARCHAR(255)` | NULLABLE | Client key `p256dh` |
| `auth_token` | `VARCHAR(255)` | NULLABLE | Client key `auth` |
| `content_encoding` | `VARCHAR(20)` | DEFAULT `'aesgcm'` | Encoding payload Web Push |
| `device_type` | `VARCHAR(30)` | DEFAULT `'desktop'` | `'mobile'`, `'desktop'`, `'pwa'`, `'app'` |
| `is_mobile` | `BOOLEAN` | DEFAULT `false` | Penanda perangkat HP untuk priority routing |
| `user_agent` | `TEXT` | NULLABLE | User-Agent browser (dipotong maks 500 karakter) |
| `last_active_at` | `TIMESTAMP` | NULLABLE | Terakhir kali registrasi/aktif |
| `created_at`, `updated_at` | `TIMESTAMP` | NULLABLE | Timestamps |

#### 17. `notifications` (Bawaan Laravel Database Notification)
Tabel standar Laravel (`uuid` primary key, `type`, `morphs('notifiable')`, `data` text, `read_at`, `timestamps`) untuk notifikasi *in-app* pada model `User` (`Notifiable`).

### 3.2 Index Performa Tambahan (`2026_09_23_000003`)
Index komposit ditambahkan untuk mempercepat query dashboard, reporting, dan timeline:
- `tiket`: `['status', 'tanggal_open']`, `['backbone_segment', 'status']`, `created_by`, `closed_by`
- `kronologis`: `['id_tiket', 'timestamp']`, `['user_id', 'created_at']`
- `dokumentasi`: `['id_tiket', 'timestamp']`, `['id_tiket', 'kategori']`
- `notifikasi_log`: `['id_tiket', 'created_at']`
- `titik_perbaikan`, `manuver_core`, `material`: `['id_tiket', 'created_at']`
- `tiket_joint_closures`: `['id_tiket', 'status_aset']`
- `tiket_joint_closure_cores`: `['id_joint_closure', 'status_core']`

### 3.3 Relasi Model Eloquent
- `User` -> `hasMany(Tiket, 'created_by')`, `hasMany(Tiket, 'closed_by')`, `hasMany(Kronologis, 'user_id')`, `hasMany(PushSubscription, 'user_id')`.
- `Tiket` ->
  - `belongsTo(User)`: `creator` (`created_by`), `resolver` (`resolved_by`), `closer` (`closed_by`), `assignedLead` (`assigned_lead_id`), `assignedBy` (`assigned_by`).
  - `hasOne(ResumePekerjaan, 'id_tiket')` (`resume`).
  - `hasOne(TiketStopClock, 'id_tiket')->where('is_active', true)->latestOfMany()` (`activeStopClock`).
  - `hasMany`: `kronologis` (ordered `timestamp asc`), `materials`, `titikPerbaikans`, `manuverCores`, `jointClosures`, `dokumentasis`, `notifikasiLogs`, `stopClocks` (ordered `start_time asc`), `handoverShifts` (`latest`).
- `TiketJointClosure` -> `belongsTo(Tiket, 'id_tiket')`, `belongsTo(User, 'created_by')`, `hasMany(TiketJointClosureCore, 'id_joint_closure')` (`cores`).

---

## 4. Alur Kerja & Business Logic (Service Layer)

### 4.1 Siklus Hidup Tiket (`TiketService` & `TiketController`)

```text
[OPEN] ──(First Response / Input Kronologis / Resume / Manuver / Dok)──▶ [PROSES]
   │                                                                        │ ▲
   │ (Dapat diedit oleh HelpDesk/Admin)                                     │ │ (Reject Closing Awal
   │                                                                        │ │  oleh HelpDesk/Admin)
   ▼                                                                        ▼ │
[Dispatch oleh Manager Teknis/Admin: assignTeknisi()]                [PENDING_VERIFIKASI]
                                                                            │
                                                      (Closing Awal oleh Teknisi/Mgr/Admin)
                                                                            │
                                                                 (Verifikasi Akhir: closeTiket)
                                                                            ▼
                                                                         [CLOSE]
```

#### A. Penomoran Tiket Otomatis (`TiketService::generateNoTiket`)
- Prefix kode kategori: `BRD` jika `kategori === 'BROADBAND'`, selain itu `BDG`.
- Format: `{CODE}-{YYYYMMDD}-{XXX}` (menggunakan `$tanggalOpen->format('Ymd')`).
- Urutan `XXX`: mencari nomor tiket terakhir pada tanggal & prefix tersebut (`where('no_tiket', 'like', $prefix . '%')->orderBy('no_tiket', 'desc')->first()`), mengambil 3 digit terakhir, lalu `+ 1` dengan `str_pad(..., 3, '0', STR_PAD_LEFT)`.

#### B. Pembuatan Tiket (`TiketService::createTiket`)
1. Berjalan dalam `DB::transaction()`.
2. Menentukan `sla_target_minutes`:
   - Jika diisi manual di request, gunakan nilai tersebut.
   - Jika kategori `BROADBAND`: default **`240` menit** (4 jam).
   - Jika kategori `BACKBONE`: lookup `MasterSla::where('backbone_segment', $segment)->first()`, fallback **`360` menit** (6 jam).
3. Untuk tiket `BROADBAND`, jika `status_link_impact` kosong, sistem otomatis membentuk string: `"Broadband: {$nama_pelanggan} ({$id_pelanggan}) - {$jenis_kendala_broadband}"`.
4. Status awal selalu `'OPEN'`, `sla_status = 'NA'`, `total_stop_clock_minutes = 0`, `is_stop_clock = false`.
5. Jika ada upload `foto_masalah[]` saat open tiket (`TiketController@store`), file disimpan melalui `DokumentasiService::upload()` dengan kategori `'Bukti Masalah'` dan `timestamp = $tiket->tanggal_open`. (Catatan: foto berkategori `'Bukti Masalah'` dikecualikan saat pengecekan syarat foto hasil perbaikan pada Closing Awal).
6. Memicu `NotificationService::notifyTiketBaru($tiket)`.

#### C. Penugasan / Dispatch Tim Teknisi (`TiketController@assignTeknisi`)
- Hanya dapat dijalankan oleh `admin` dan `manager_teknisi`.
- Mengisi `assigned_lead_id`, `assigned_team` (array ID user), `assigned_by`, `assigned_at = now()`, `catatan_dispatch`, dan `dispatch_status = 'assigned'`.
- Mencatat entri `Kronologis` otomatis dengan kategori `LAIN` berisi nama PIC dan anggota tim.
- Memanggil `NotificationService::notifyTiketAssigned($tiket, $manager)` yang membedakan isi notifikasi antara teknisi yang ditugaskan (*high priority*) dan teknisi lain (*mode pantau*).

#### D. Pencatatan Respon Pertama (*First Response Tracking*)
- Terjadi otomatis di `KronologisService::addKronologis()` (dan tersedia pula helper `TiketService::recordFirstResponse()`) ketika user ber-role `teknis` mengirim kronologis pertama dan `first_response_at` masih `null`.
- Mengisi `first_response_at = now()`, menghitung `response_time_minutes = max(0, $tiket->tanggal_open->diffInMinutes($firstResponseAt))`, dan mengubah `status` dari `OPEN` menjadi `PROSES`.
- Selain itu, penambahan kronologis (oleh role mana pun), simpan resume, manuver core, atau upload dokumentasi saat tiket berstatus `OPEN` akan otomatis mengubah status menjadi `PROSES`.

#### E. Jeda SLA / Stop Clock (`startStopClock` & `stopStopClock`)
Digunakan ketika penanganan terhenti karena faktor eksternal di luar kendali tim teknis.
- **Kategori Alasan (`TiketStopClock`)**:
  - `IZIN_AKSES` / `MENUNGGU_AKSES_PELANGGAN`
  - `PIHAK_KETIGA_PLN` / `KENDALA_PIHAK_KETIGA`
  - `CUACA_EKSTRIM` / `CUACA_BURUK`
  - `MENUNGGU_MATERIAL`
  - `PERMINTAAN_PELANGGAN`
  - `FORCE_MAJEURE`
- **Mulai Stop Clock (`startStopClock`)**:
  - Ditolak jika tiket sudah `CLOSE` atau sudah dalam kondisi `is_stop_clock = true`.
  - Membuat baris `tiket_stop_clocks` (`is_active = true`), mengubah `tiket.is_stop_clock = true`, dan mencatat log kronologis kategori `LAIN`.
- **Hentikan Stop Clock (`stopStopClock`)**:
  - Mengisi `end_time = now()`, `duration_minutes = max(1, $start_time->diffInMinutes($end_time))`, `stopped_by`, `is_active = false`.
  - Menghitung ulang `tiket.total_stop_clock_minutes` dari jumlah seluruh `duration_minutes` di `tiket_stop_clocks`.
  - Mengubah `tiket.is_stop_clock = false` dan mencatat log kronologis otomatis.
- **Auto-Stop**: Stop Clock yang masih aktif akan dihentikan secara otomatis oleh sistem ketika teknisi melakukan `closingAwal()` atau HelpDesk melakukan `closeTiket()`.

#### F. Oper Shift / Handover (`TiketService::handoverShift`)
- Mencatat riwayat serah terima ke `tiket_handover_shifts` (`shift_from`, `shift_to`, `user_from_id`, `user_to_id`, `catatan_handover`).
- Pada `TiketController@handoverShift`, input opsional `kendala_pending` dan `alokasi_team` digabungkan ke dalam `catatan_handover`.
- Membuat entri `Kronologis` otomatis (kategori `LAIN`) dan mengirim notifikasi melalui `NotificationService::notifyHandoverShift()`.

#### G. Validasi Prasyarat & Closing Dua Tahap (*Two-Stage Closing*)
1. **Pengecekan Prasyarat (`Tiket::checkClosingPrerequisites`)**:
   Tersedia juga via endpoint AJAX `GET /tiket/{tiket}/prerequisites`.
   - **Untuk Tiket `BROADBAND`**:
     1. `resume` terisi: `$tiket->resume !== null` serta `problem_temuan` dan `action` tidak kosong.
     2. `dokumentasi` hasil perbaikan terupload: minimal 1 record di `dokumentasi` dengan `kategori != 'Bukti Masalah'`.
     3. `redaman_sesudah` terisi: `!empty($tiket->redaman_sesudah) || !empty($tiket->resume?->redaman_sesudah)`.
   - **Untuk Tiket `BACKBONE`**:
     1. `resume` terisi (`problem_temuan` & `action` wajib ada).
     2. `dokumentasi` hasil perbaikan terupload: minimal 1 record di `dokumentasi` dengan `kategori != 'Bukti Masalah'`.
     3. `titik_perbaikan` terisi: minimal 1 record di `titik_perbaikan` **ATAU** minimal 1 record di `tiket_joint_closures`.
     4. `tipe_penanganan` / bukti teknis core terisi: minimal 1 record di `tiket_joint_closures` **ATAU** minimal 1 record di `manuver_core`.

2. **Tahap 1 — Closing Awal (`TiketService::closingAwal` — Role: `admin`, `manager_teknisi`, `teknis`)**:
   - Memvalidasi tiket belum `CLOSE` dan seluruh prasyarat `checkClosingPrerequisites()['is_eligible']` bernilai `true`.
   - Jika `is_stop_clock` aktif, hentikan otomatis.
   - Mengubah status menjadi `'PENDING_VERIFIKASI'`, mengisi `resolved_by`, `resolved_at`, `closing_notes_teknisi`, dan `tipe_penanganan` (serta `redaman_sebelum`/`redaman_sesudah` untuk Broadband).
   - Menyinkronkan data ke `resume_pekerjaan` jika sudah ada.
   - Mencatat kronologis otomatis kategori `'SELESAI'` dan memicu `notifyTiketUpdated`.

3. **Tahap 2A — Reject Closing Awal (`TiketService::rejectClosingAwal` — Role: `admin`, `helpdesk`)**:
   - Hanya berlaku jika status tiket `'PENDING_VERIFIKASI'`.
   - Mengembalikan status tiket ke `'PROSES'`, mencatat kronologis kategori `'LAIN'` beserta alasan penolakan, dan mengirim `notifyTiketUpdated`.

4. **Tahap 2B — Closing Akhir (`TiketService::closeTiket` — Role: `admin`, `helpdesk`)**:
   - Hanya berlaku jika status tiket `'PENDING_VERIFIKASI'`.
   - Menghentikan Stop Clock jika masih aktif.
   - Menghitung **MTTR Bersih** (`hitungMttr`):
     $$\text{MTTR (menit)} = \max\left(0,\; \text{diffInMinutes}(\text{tanggal\_open}, \text{tanggal\_close}) - \text{total\_stop\_clock\_minutes}\right)$$
   - Menentukan **Status SLA** (`cekSla`):
     - `'NA'` jika `sla_target_minutes` null atau `<= 0`.
     - `'TEPAT'` jika `mttr_minutes <= sla_target_minutes`.
     - `'LEBIH'` jika `mttr_minutes > sla_target_minutes`.
   - Mengubah status ke `'CLOSE'`, mengisi `tanggal_close`, `mttr_minutes`, `total_stop_clock_minutes`, `sla_status`, dan `closed_by`.
   - Mencatat kronologis otomatis kategori `'LINK_UP'` dan memicu `notifyTiketClosed`.

#### H. Pemantauan Kepatuhan Update 30 Menit & Stepper SLA (Accessors di `Tiket`)
- `minutes_since_last_update`: selisih menit antara kronologis terakhir (atau `tanggal_open` jika belum ada kronologis) hingga `Carbon::now()`.
- `field_update_status`:
  - `'CLOSED'` jika status `'CLOSE'`
  - `'STOP_CLOCK'` jika `is_stop_clock == true`
  - `'NORMAL'` jika `minutes_since_last_update <= 20`
  - `'WARNING'` jika `21..30` menit
  - `'OVERDUE'` jika `> 30` menit
- `average_report_interval_minutes`: rata-rata selisih menit antar kronologis berurutan pada tiket tersebut.
- `verification_duration_minutes`: selisih menit antara `resolved_at` (Closing Awal) hingga `tanggal_close` (Closing Akhir NOC).
- `sla_timeline_stages`: menghasilkan 5 tahapan terstruktur (`OPEN`, `FIRST_RESPONSE`, `STOP_CLOCK`, `CLOSING_AWAL`, `CLOSING_AKHIR`).

---

### 4.2 Modul Kronologis & Read Receipts (`KronologisService` & `KronologisController`)

1. **Kategori Kronologis (`KronologisService::KATEGORI_LIST`)**:
   `IZIN`, `OTDR`, `TRACING`, `MATERIAL`, `JOINTING`, `LINK_UP`, `SELESAI`, `LAIN`.
2. **Upload Media Foto & Video (`uploadMediaFile`)**:
   - Mendeteksi otomatis apakah file berjenis video (ekstensi `mp4`, `webm`, `mov`, `m4v`, `3gp`, `avi` atau mime `video/*`) atau gambar.
   - Foto disimpan di `public/uploads/kronologis/` (`krono_{id}_{Ymd_His}_{uniqid}.{ext}`), sedangkan video di `public/uploads/kronologis/videos/` (`krono_vid_...`).
   - **Kompresi Server-Side (GD)** (`compressImageOnServer`): Jika ukuran file gambar `>= 1MB` (`1048576` bytes) atau dimensi `> 1920px`, gambar otomatis di-resize proporsional maksimal `1920px` dan dikompresi ulang (JPEG kualitas `80`, PNG level `7`, WebP kualitas `80`, dengan menjaga alpha channel pada PNG/WebP).
3. **Cursor Pagination & Polling (`getTimeline` & `KronologisController@index`)**:
   - Mendukung parameter `after_id` (mengambil update lebih baru untuk polling real-time), `before_id` (mengambil batch lama untuk *load more*, default `limit = 40`, clamp `5..100`), atau initial load `40` pesan terbaru yang diurutkan menaik (`timestamp asc, id asc`).
4. **Read Receipts / Ceklis Biru Berbasis Cache**:
   - Saat user membuka `TiketController@show` atau melakukan polling JSON `KronologisController@index`, sistem menyimpan jejak kehadiran user ke cache key `"tiket_{$tiket->id}_user_views"` selama **7 hari** (`user_id`, `name`, `role`, `avatar`, `viewed_at`).
   - API mengembalikan `max_read_timestamp` (nilai maksimum antara `viewed_at` user lain dan `timestamp` pesan terakhir dari user lain) serta `views` untuk menentukan status baca.
   - Saat membuka detail tiket (`TiketController@show`), semua `unreadNotifications` milik user yang terkait `id_tiket` atau `no_tiket` tersebut otomatis ditandai telah dibaca (`markAsRead()`).
5. **Aturan Edit & Hapus Pesan**:
   - **Update (`PUT /tiket/{tiket}/kronologis/{kronologis}`)**: Hanya diizinkan bagi `admin` atau pembuat pesan (`user_id`), dengan batas waktu maksimal **5 menit** setelah pesan dikirim (`$sentTime->addMinutes(5)->isPast()`).
   - **Delete (`DELETE`)**: Diizinkan bagi `admin`, atau pembuat pesan selama tiket belum berstatus `CLOSE`. File fisik `foto_url` dan `video_url` ikut dihapus dari `public_path`.

---

### 4.3 Modul Resume Pekerjaan, Material, & Titik Perbaikan (`ResumeService`)

1. **Resume Pekerjaan (`saveOrUpdateResume`)**:
   - `team_om` menerima array atau string dipisah koma/baris baru (`preg_split('/[\r\n,]+/')`), lalu dinormalisasi menjadi JSON array.
   - Menggunakan `ResumePekerjaan::updateOrCreate(['id_tiket' => $tiket->id], ...)`.
   - Menyinkronkan `tipe_penanganan`, `redaman_sebelum`, `redaman_sesudah` ke tabel `tiket`, serta mengubah status `OPEN` -> `PROSES`.
2. **Material Tiket (`addMaterial` & `deleteMaterial`)**:
   - Mencatat pemakaian material (`nama_material`, `jumlah`, `satuan`) pada tiket. Penghapusan diizinkan untuk `admin`, `helpdesk`, `teknis` (di `ResumeService::deleteMaterial`).
3. **Titik Perbaikan (`addTitikPerbaikan` & `deleteTitikPerbaikan`)**:
   - Mencatat koordinat titik perbaikan (`nama_titik`, `latitude`, `longitude`, `keterangan`) beserta accessor `google_maps_url`.

---

### 4.4 Modul Joint Closure & Manuver Core (`JointClosureService` & `ManuverCoreService`)

1. **Joint Closure (`JointClosureService`)**:
   - Mendukung pencatatan spesifikasi closure fisik (`DOME`, `INLINE`, `BOX_FAT`, `OTB`, `CLOSURE_STANDAR`), status aset (`EKSISTING` vs `ASET_BARU`), kapasitas & jumlah tube kabel asal maupun kabel jumper, jenis sambungan (`LURUS_STRAIGHT`, `PERCABANGAN_BRANCH`, `LOOP_MANUVER`), lokasi penempatan, dan koordinat GPS.
   - Mendukung input baris sambungan core (`TiketJointClosureCore`) dalam 2 format payload sekaligus saat `store`:
     - Format JSON nested array `cores[]` (`tube_asal`, `core_asal`, `tube_tujuan`/`tube_jumper`, `core_tujuan`/`core_jumper`, `status_core`/`status`, `loss_db`, `keterangan`).
     - Format parallel form arrays (`tube_asal[]`, `core_asal[]`, `tube_jumper[]`/`tube_tujuan[]`, `core_jumper[]`/`core_tujuan[]`, `core_status[]`, `loss_db[]`, `core_keterangan[]`).
   - Mendukung penambahan/penghapusan core satuan pada closure yang sudah ada (`addCore`, `deleteCore`).
   - Model `TiketJointClosure` menyediakan accessor `core_summary` yang menghitung jumlah `total`, `terhubung`, `spare`, `loss` (`LOSS_PUTUS`), dan `manuver`.
2. **Manuver Core (`ManuverCoreService`)**:
   - `ManuverCoreController@store` mendukung input single maupun array paralel pada `core_asal` dan `core_tujuan` (loop membuat multiple records dalam satu submit).
   - Mencatat `lokasi_tipe` (`POP`, `OTB`, `CLOSURE_LAPANGAN`, `FAT_FDT`, `LAINNYA`), `titik` (dinormalisasi uppercase), `core_asal`, `core_tujuan`, `core_dialihkan`, `titik_kembali`, `tipe` (`SEBELUM`/`SESUDAH`), `status_manuver` (`TEMPORARY`/`PERMANENT`/`RESTORED`), `status_core_aset` (`OCCUPIED_MANUVER`/`BROKEN_LOSS`/`SPARE_AVAILABLE`), dan `keterangan`.
   - Otomatis mengubah status tiket dari `OPEN` menjadi `PROSES`.
   - `getGroupedManuver(Tiket $tiket)` mengelompokkan record berdasarkan `titik` lalu diurutkan berdasarkan `tipe` dan `id`.

---

### 4.5 Modul Dokumentasi & Ekstraksi EXIF GPS (`DokumentasiService`)

1. **Sumber Input Fleksibel (`DokumentasiService::upload`)**:
   - **Upload file tunggal/banyak**: input `foto` atau `photos[]` disimpan pada disk `public` di folder `dokumentasi/{tiket_id}/` dengan format nama `doc_{tiket_id}_{Ymd_His}_{6chars}.{ext}`.
   - **Teruskan dari Chat Kronologis (`source_photo_url`)**: menyalin file gambar yang sudah ada dari kronologis (`public/` atau `storage/app/public/`) langsung menjadi record `Dokumentasi` baru tanpa user perlu upload ulang.
   - **Path manual (`file_path`)**: dukungan untuk seeder/testing.
2. **Ekstraksi Geotag GPS Otomatis dari EXIF (`getGpsCoordinatesFromExif`)**:
   - Jika parameter `latitude` atau `longitude` tidak diisi saat upload, backend membaca metadata EXIF file gambar menggunakan `exif_read_data()`.
   - Mengonversi format rasional DMS (*Degrees, Minutes, Seconds*) dan referensi hemisphere (`GPSLatitudeRef` `S`, `GPSLongitudeRef` `W` menjadi negatif) ke koordinat desimal presisi 8 angka di belakang koma.
3. **Kompresi Server-Side & Auto Status**:
   - Menerapkan kompresi/resize GD (`maxDim = 1920`, threshold `1MB`).
   - Otomatis mengubah status tiket `OPEN` -> `PROSES`.
   - Saat record dihapus (`delete`), file fisik di `Storage::disk('public')` ikut dihapus dalam transaksi database.

---

### 4.6 Modul Analitik MTTR, KPI, & Reporting (`MttrService`, `KpiService`, `ReportService`)

#### A. Analitik MTTR & SLA (`MttrService`)
Seluruh query menerapkan `Tiket::forUserMonitoring($user)`:
- `getMttrAverage($startDate, $endDate, $user)`: rata-rata `mttr_minutes` dan jumlah tiket `CLOSE`.
- `getSlaComplianceStats($startDate, $endDate, $user)`: menghitung `total_closed`, `total_tepat`, `total_lebih`, dan `compliance_rate` (%).
- `getMonthlyMttrTrend($year, $user)`: agregasi rata-rata `mttr_minutes` dan jumlah tiket per bulan (Jan–Des), dengan ekspresi SQL adaptif untuk `sqlite` (`strftime('%m', ...)`) dan `mysql` (`MONTH(...)`).
- `getTopSegmentsByIncident($limit, $user)`: top segment berdasarkan jumlah gangguan, rata-rata MTTR, dan jumlah over-SLA.
- `getCategoryBreakdown($startDate, $endDate, $user)`: pengelompokan total & rata-rata MTTR per `tipe_penanganan`.
- `getStopClockImpactAnalytics($startDate, $endDate, $user)`: agregasi total kejadian dan total menit jeda berdasarkan `alasan_kategori`.
- `getHourlyDistribution($startDate, $endDate, $user)`: distribusi jumlah kejadian gangguan per jam (`00:00`–`23:00`), adaptif untuk SQLite & MySQL.

#### B. Evaluasi KPI Teknisi & HelpDesk (`KpiService`)
1. **Ringkasan Eksekutif (`getExecutiveKpiSummary`)**:
   - `total_tiket`, `total_closed`, `sla_compliance_rate`, `total_tepat_sla`, `total_lebih_sla`, `avg_mttr_minutes`, `avg_response_minutes`, `total_stop_clock_minutes`, `avg_verification_minutes`.
2. **KPI Teknisi Lapangan (`getTeknisiKpiList`)**:
   - Dihitung per user ber-role `teknis` (Helpdesk/SA-CS tidak melihat daftar ini; Teknisi hanya melihat dirinya sendiri; Admin & Manager Teknis melihat semua teknisi).
   - Metrik per teknisi:
     - `total_resolved`: tiket di mana `resolved_by = user_id` atau `assigned_lead_id = user_id`.
     - `sla_compliance_rate`: persentase tiket `CLOSE` milik teknisi tersebut yang berstatus `TEPAT`.
     - `avg_mttr_minutes` & `avg_response_minutes`.
     - `interval_compliance_rate`: memeriksa selisih waktu antar pesan kronologis berurutan yang dikirim teknisi pada setiap tiket; jika selisih `> 30` menit dihitung `overdue_updates`.
     - **Formula Skor KPI Akhir (`0 - 100`)**:
       $$\text{kpi\_score} = \text{round}\Big((\text{sla\_rate} \times 0.45) + (\text{interval\_compliance\_rate} \times 0.35) + \big(\max(0,\; 100 - \min(100,\; \text{avg\_response} \times 2)) \times 0.20\big),\; 1\Big)$$
3. **KPI HelpDesk & NOC (`getHelpdeskKpiList`)**:
   - Dihitung untuk user ber-role `helpdesk`, `admin`, `sa_cs` (Teknisi tidak melihat daftar ini).
   - Metrik: `total_created`, `total_closed` / `total_verified`, `sla_compliance_rate`, dan `avg_verification_minutes` (durasi dari `resolved_at` ke `tanggal_close`).

#### C. Export Laporan (`ReportService` & `App\Exports\*`)
- **PDF (`barryvdh/laravel-dompdf`)**:
  1. `generateSingleTiketPdf($tiket, 'internal')`: Berita Acara Internal lengkap (A4 Portrait) memuat kronologis, resume, material, titik perbaikan, joint closures + cores, manuver core, handover shifts, dan dokumentasi.
  2. `generateSingleTiketPdf($tiket, 'customer')`: Berita Acara untuk Pelanggan (A4 Portrait) hanya memuat ringkasan tiket, resume, titik perbaikan, dan dokumentasi.
  3. `generateSummaryPdf($filters, $user)`: Rekap banyak tiket + metrik (A4 Landscape).
  4. `generateHandoverShiftsPdf($filters, $user)`: Rekap audit handover shift (A4 Landscape).
  5. `generateKpiPdf($startDate, $endDate, $user)`: Laporan KPI Eksekutif (A4 Portrait).
- **Excel (`maatwebsite/excel`)**:
  1. `TiketExport`: Export daftar tiket terfilter + ringkasan Joint Closure, status aset baru, problem, action, & team OM.
  2. `KpiExport`: Export multi-section (Ringkasan Eksekutif, Peringkat Teknisi, Kinerja HelpDesk/NOC).
  3. `HandoverShiftExport`: Export riwayat serah terima shift antar petugas.

---

## 5. Sistem Multi-Channel Notification

Orkestrasi notifikasi ditangani oleh `App\Services\NotificationService` melalui **4 mekanisme** secara paralel:

### 5.1 Event Trigger & Penerima
| Method Trigger | Penerima | Channel |
| :--- | :--- | :--- |
| `notifyTiketBaru($tiket)` | User aktif role `admin`, `manager_teknisi`, `helpdesk`, `sa_cs` (kecuali pembuat tiket). *Catatan: Teknisi tidak dinotifikasi sebelum di-dispatch.* | Database + Email (`TiketBaruNotification`), Web Push, WhatsApp, `notifikasi_log` |
| `notifyTiketAssigned($tiket, $manager)` | Semua user aktif role `admin`, `manager_teknisi`, `helpdesk`, `teknis`, `sa_cs` (kecuali `$manager`). Dibedakan antara teknisi yang ditunjuk (`TIKET_ASSIGNED_DIRECT`) vs observer (`TIKET_ASSIGNED_INFO`). | Database (`TiketAssignedNotification`), Web Push (prioritas tinggi untuk yang ditunjuk, info monitoring untuk observer), `notifikasi_log` |
| `notifyKronologisBaru($tiket, $kronologis)` | User aktif role `admin`, `manager_teknisi`, `helpdesk`, `teknis`, `sa_cs` (kecuali pengirim). | Database + Email (`KronologisBaruNotification`), Web Push (menggunakan nama & avatar pengirim ala WhatsApp), WhatsApp, `notifikasi_log` |
| `notifyTiketUpdated($tiket, $updater, $ket)` | User aktif role `admin`, `helpdesk`, `teknis`, `sa_cs` (kecuali `$updater`). | Database + Email (`TiketUpdateNotification`), `notifikasi_log` |
| `notifyProgresPekerjaan($tiket, $ket, $user)` | Wrapper ke `notifyTiketUpdated` (dipanggil saat simpan resume, material, JC, manuver, atau upload dokumentasi). | Database + Email, `notifikasi_log` |
| `notifyTiketClosed($tiket)` | User aktif role `admin`, `helpdesk`, `teknis`, `sa_cs` (kecuali penutup tiket). | Database + Email (`TiketClosedNotification`), Web Push, WhatsApp, `notifikasi_log` |
| `notifyHandoverShift($handover)` | Khusus `$handover->userTo` (petugas penerima shift). | Database + Email (`HandoverShiftNotification`), Web Push, `notifikasi_log` |

### 5.2 Web Push Notification dengan Smart Device Routing (`WebPushService`)
Menggunakan `minishlink/web-push` dengan VAPID (`VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`):
- **Konfigurasi Delivery**: `TTL = 86400` (24 jam), `urgency = 'high'` (menembus mode Doze Android/APNs), `batchSize = 200`, `setReuseVAPIDHeaders(true)`.
- **Smart Device Priority Routing (`sendToUsers`)**:
  - Sistem mengelompokkan `PushSubscription` per `user_id`.
  - Perangkat diklasifikasikan sebagai *mobile* jika `is_mobile == true`, `device_type` termasuk `['mobile', 'pwa', 'app', 'android', 'ios']`, atau `user_agent` cocok dengan regex mobile.
  - **Jika user memiliki minimal 1 perangkat mobile terdaftar, Web Push HANYA dikirim ke perangkat mobile-nya (tidak dikirim ke browser desktop)** guna menghindari duplikasi notifikasi. Jika belum ada perangkat mobile, barulah dikirim ke seluruh perangkat desktop miliknya.
- **Auto-Cleanup Token Kadaluarsa**: Apabila endpoint mengembalikan HTTP status `404` atau `410`, endpoint tersebut otomatis dihapus dari tabel `push_subscriptions`.

### 5.3 WhatsApp Gateway (`WhatsAppService`)
- Menggunakan HTTP POST ke `WA_API_URL` (default `https://api.fonnte.com/send`) dengan header `Authorization: {WA_API_KEY}` dan payload `['target' => $phone, 'message' => $message]`.
- Mendukung mode simulasi (`WA_SIMULATE=true` atau `WA_API_KEY` kosong): menulis pesan ke `Log::info()` tanpa menembak API eksternal.
- **Normalisasi Nomor HP (`formatPhoneNumber`)**: membersihkan karakter non-digit dan mengubah awalan `0` menjadi `62`.
- Seluruh hasil pengiriman dicatat ke tabel `notifikasi_log` dengan status `'SENT'` atau `'FAILED'`.

---

## 6. Integrasi Eksternal & Helper API

### 6.1 Integrasi Database Pelanggan IMS (`CustomerImsController`)
Menggunakan koneksi database kedua bernama `'ims'` (didefinisikan di `config/database.php` dengan variabel `DB_IMS_HOST`, `DB_IMS_PORT`, `DB_IMS_DATABASE`, `DB_IMS_USERNAME`, `DB_IMS_PASSWORD`, `strict => false`).

- **Query Join pada Database `ims_v3`**:
  - Tabel utama: `trx_batchjob_register as r` (`hide != '1'`)
  - Left join:
    - `m_pelanggan as m` (`r.nik_penduduk = m.nik_penduduk`)
    - `m_bandwith as b` (`r.kode_bandwith = b.kode_bandwith`)
    - `m_pop as p` (`r.kode_pop = p.kode_pop`)
- **Endpoint**:
  1. `GET /api/ims/customers/search?q={keyword}` (minimal 2 karakter, limit 25 hasil): mencari berdasarkan `nomor_internet`, `nama_pelanggan`, `alamat_pasang`, `nik_penduduk`, atau `nomor_hp`.
  2. `GET /api/ims/customers/{cid}`: mengambil detail 1 pelanggan berdasarkan `nomor_internet`.
- **Format Field Output JSON**:
  `id_pelanggan`, `nama_pelanggan`, `no_kontak`, `alamat_pelanggan` (gabungan `alamat_pasang` + RT/RW/No bangunan), `titik_odp` (`olt + index_olt` fallback `kode_pop`), `kode_bandwith` (ditambah suffix ` Mbps` jika numerik), `kode_pop` (`nama_pop` fallback `kode_pop`), `lon_lat`, `media_akses`, `olt`, `index_olt`.
- **Graceful Degradation**: Endpoint search mengembalikan HTTP `200` dengan `success: false` dan `data: []` jika koneksi ke server IMS bermasalah agar tidak memecah alur form. Selain itu, `TiketController@show` juga melakukan lookup nama POP dan nominal bandwidth dari koneksi `ims` secara opsional (fail silently).

### 6.2 Reverse Geocoding Proxy & Cache (`GeocodeController`)
- **Endpoint**: `GET /api/reverse-geocode?lat={lat}&lng={lng}`
- Memvalidasi `lat` dan `lng` harus numerik (HTTP `422` jika tidak valid).
- Membulatkan koordinat hingga **5 angka desimal** (`round(..., 5)`).
- Menggunakan `Cache::remember("geocode_{$lat}_{$lng}", 86400, ...)` (**cache 24 jam**) untuk menghemat kuota API eksternal.
- Memanggil `https://nominatim.openstreetmap.org/reverse` (`format=jsonv2`, `zoom=18`, `addressdetails=1`, `accept-language=id`, `timeout=6s`).
- Memformat komponen alamat unik (`road`, `village`, `district` dengan prefix `'Kecamatan '`, `city` dengan prefix `'Kota '`, `state`) ke dalam `formatted_lines[]`, serta menyediakan fallback statis jika API OpenStreetMap tidak dapat dijangkau.

---

## 7. Daftar Lengkap Endpoint / Routes & Validasi Input

### 7.1 Daftar Routes (`routes/web.php`)

#### A. Autentikasi & Profil
| Method | URI | Route Name | Controller@Method | Middleware |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | - | Redirect ke `dashboard` / `login` | - |
| `GET` | `/login` | `login` | `AuthController@showLoginForm` | `guest` |
| `POST` | `/login` | `login.post` | `AuthController@login` | `guest` |
| `GET\|POST` | `/logout` | `logout` | `AuthController@logout` | `auth`, `role` |
| `GET` | `/dashboard` | `dashboard` | `DashboardController@index` | `auth`, `role` |
| `GET` | `/profile` | `profile` | `AuthController@profile` | `auth`, `role` |
| `PUT` | `/profile` | `profile.update` | `AuthController@updateProfile` | `auth`, `role` |

#### B. Modul Tiket & Operasional Workflow
| Method | URI | Route Name | Controller@Method | Role Diizinkan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/tiket` | `tiket.index` | `TiketController@index` | Semua role login |
| `GET` | `/tiket/create` | `tiket.create` | `TiketController@create` | `admin`, `helpdesk` |
| `POST` | `/tiket` | `tiket.store` | `TiketController@store` | `admin`, `helpdesk` |
| `GET` | `/tiket/preview-number` | `tiket.preview-number` | `TiketController@previewNumber` | `admin`, `helpdesk` |
| `GET` | `/tiket/{tiket}/edit` | `tiket.edit` | `TiketController@edit` | `admin`, `helpdesk` |
| `PUT` | `/tiket/{tiket}` | `tiket.update` | `TiketController@update` | `admin`, `helpdesk` |
| `POST` | `/tiket/{tiket}/close` | `tiket.close` | `TiketController@close` | `admin`, `helpdesk` |
| `POST` | `/tiket/{tiket}/reject-closing-awal` | `tiket.reject-closing-awal` | `TiketController@rejectClosingAwal` | `admin`, `helpdesk` |
| `POST` | `/tiket/{tiket}/closing-awal` | `tiket.closing-awal` | `TiketController@closingAwal` | `admin`, `manager_teknisi`, `teknis` |
| `POST` | `/tiket/{tiket}/assign-teknisi` | `tiket.assign-teknisi` | `TiketController@assignTeknisi` | `admin`, `manager_teknisi` |
| `POST` | `/tiket/{tiket}/stop-clock/start` | `tiket.stop-clock.start` | `TiketController@startStopClock` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/stop-clock/stop` | `tiket.stop-clock.stop` | `TiketController@stopStopClock` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/handover-shift` | `tiket.handover-shift` | `TiketController@handoverShift` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/tipe-penanganan` | `tiket.tipe-penanganan.update` | `TiketController@updateTipePenanganan` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `GET` | `/tiket/{tiket}/prerequisites` | `tiket.prerequisites` | `TiketController@checkPrerequisites` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `GET` | `/tiket/{tiket}` | `tiket.show` | `TiketController@show` | Semua role login |
| `DELETE` | `/tiket/{tiket}` | `tiket.destroy` | `TiketController@destroy` | `admin` |

#### C. Modul Kronologis, Resume, Material, Titik Perbaikan, Dokumentasi, JC & Manuver Core
| Method | URI | Route Name | Controller@Method | Role Diizinkan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/tiket/{tiket}/kronologis` | `tiket.kronologis.index` | `KronologisController@index` | Semua role login |
| `POST` | `/tiket/{tiket}/kronologis` | `tiket.kronologis.store` | `KronologisController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `PUT` | `/tiket/{tiket}/kronologis/{kronologis}` | `tiket.kronologis.update` | `KronologisController@update` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/tiket/{tiket}/kronologis/{kronologis}` | `tiket.kronologis.destroy` | `KronologisController@destroy` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/resume` | `tiket.resume.store` | `ResumeController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `PUT` | `/tiket/{tiket}/resume` | `tiket.resume.update` | `ResumeController@update` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/material` | `tiket.material.store` | `MaterialController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/tiket/{tiket}/material/{material}` | `tiket.material.destroy` | `MaterialController@destroy` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/titik-perbaikan` | `tiket.titik-perbaikan.store` | `TitikPerbaikanController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/tiket/{tiket}/titik-perbaikan/{titikPerbaikan}` | `tiket.titik-perbaikan.destroy` | `TitikPerbaikanController@destroy` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/dokumentasi` | `tiket.dokumentasi.store` | `DokumentasiController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/dokumentasi/{dokumentasi}` | `tiket.dokumentasi.destroy` | `DokumentasiController@destroy` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/manuver-core` | `tiket.manuver-core.store` | `ManuverCoreController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/manuver-core/{manuverCore}` | `tiket.manuver-core.destroy` | `ManuverCoreController@destroy` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/tiket/{tiket}/joint-closure` | `tiket.joint-closure.store` | `JointClosureController@store` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/joint-closure/{jointClosure}` | `tiket.joint-closure.destroy` | `JointClosureController@destroy` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `POST` | `/joint-closure/{jointClosure}/core` | `tiket.joint-closure.add-core` | `JointClosureController@addCore` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |
| `DELETE` | `/joint-closure-core/{core}` | `tiket.joint-closure.delete-core` | `JointClosureController@deleteCore` | `admin`, `manager_teknisi`, `helpdesk`, `teknis` |

#### D. Helper API, Reporting, Notifikasi, Web Push & Master Data
| Method | URI | Route Name | Controller@Method | Role Diizinkan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/reverse-geocode` | `api.reverse-geocode` | `GeocodeController@reverse` | Semua role login |
| `GET` | `/api/ims/customers/search` | `api.ims.customers.search` | `Api\CustomerImsController@search` | Semua role login |
| `GET` | `/api/ims/customers/{cid}` | `api.ims.customers.show` | `Api\CustomerImsController@show` | Semua role login |
| `GET` | `/reports` | `reports.index` | `ReportController@index` | Semua role login |
| `GET` | `/reports/kpi` | `reports.kpi` | `ReportController@kpi` | Semua role login |
| `GET` | `/reports/shifts` | `reports.shifts` | `ReportController@shifts` | Semua role login |
| `GET` | `/reports/mttr-data` | `reports.mttr` | `ReportController@mttr` | Semua role login |
| `GET` | `/reports/sla-data` | `reports.sla` | `ReportController@sla` | Semua role login |
| `GET` | `/reports/export/pdf` | `reports.export.pdf` | `ReportController@exportPdf` | Semua role login |
| `GET` | `/reports/export/excel` | `reports.export.excel` | `ReportController@exportExcel` | Semua role login |
| `GET` | `/reports/export-kpi/pdf` | `reports.export.kpi.pdf` | `ReportController@exportKpiPdf` | Semua role login |
| `GET` | `/reports/export-kpi/excel` | `reports.export.kpi.excel` | `ReportController@exportKpiExcel` | Semua role login |
| `GET` | `/reports/export-shifts/pdf` | `reports.export.shifts.pdf` | `ReportController@exportShiftsPdf` | Semua role login |
| `GET` | `/reports/export-shifts/excel` | `reports.export.shifts.excel` | `ReportController@exportShiftsExcel` | Semua role login |
| `GET` | `/tiket/{tiket}/export/pdf` | `reports.export.tiket.pdf` | `ReportController@exportTiketPdf` | Semua role login |
| `GET` | `/notifications` | `notifications.index` | `NotificationController@index` | Semua role login |
| `GET` | `/notifications/unread-json` | `notifications.unread_json` | `NotificationController@unreadJson` | Semua role login |
| `GET\|POST` | `/notifications/{id}/read` | `notifications.read` | `NotificationController@markAsRead` | Semua role login |
| `GET\|POST` | `/notifications/read-all` | `notifications.read_all` | `NotificationController@markAllAsRead` | Semua role login |
| `DELETE` | `/notifications/destroy-all` | `notifications.destroy_all` | `NotificationController@destroyAll` | Semua role login |
| `DELETE` | `/notifications/{id}` | `notifications.destroy` | `NotificationController@destroy` | Semua role login |
| `GET` | `/push/vapid-public-key` | `push.vapid` | `PushSubscriptionController@vapidPublicKey` | Semua role login |
| `POST` | `/push/subscribe` | `push.subscribe` | `PushSubscriptionController@store` | Semua role login |
| `POST` | `/push/unsubscribe` | `push.unsubscribe` | `PushSubscriptionController@destroy` | Semua role login |
| `POST` | `/push/test` | `push.test` | `PushSubscriptionController@testPush` | Semua role login |
| `GET/POST/PUT/DELETE` | `/master/segments` | `master.segments.*` | `Master\MasterSegmentController` | `admin` |
| `GET/POST/PUT/DELETE` | `/master/materials` | `master.materials.*` | `Master\MasterMaterialController` | `admin` |
| `GET/POST/PUT/DELETE` | `/master/sla` | `master.sla.*` | `Master\MasterSlaController` | `admin` |
| `GET/POST/PUT/DELETE/PATCH` | `/master/users` (+ `toggle-active`, `reset-password`) | `master.users.*` | `Master\MasterUserController` | `admin` |

### 7.2 Ringkasan Aturan Validasi Input (Form Requests)
1. **`StoreTiketRequest` / `UpdateTiketRequest`**:
   - `kategori_tiket`: nullable, `in:BACKBONE,BROADBAND`
   - `wilayah`: wajib jika Broadband, `in:Bandung,Soreang,BANDUNG,SOREANG`
   - `status_link_impact`: wajib jika Backbone (`max:150`)
   - `backbone_segment`: wajib jika Backbone (`max:100`)
   - `id_pelanggan`, `nama_pelanggan`: wajib jika Broadband
   - `tanggal_open`: `required|date`
   - `sla_target_minutes`: `nullable|integer|min:1`
   - `foto_masalah.*` (khusus store): `nullable|image|mimes:jpeg,png,jpg,webp,heic|max:12288` (12 MB)
2. **`CloseTiketRequest`**:
   - `tanggal_close`: `nullable|date`
   - `catatan_closing`: `nullable|string|max:2000`
3. **`StoreKronologisRequest`**:
   - `kategori`: `nullable|in:IZIN,OTDR,TRACING,MATERIAL,JOINTING,LINK_UP,SELESAI,LAIN` (otomatis default `'LAIN'` di `prepareForValidation`)
   - `informasi`: `required|string|max:5000`
   - `foto`: `nullable|file|mimes:jpeg,png,jpg,webp,mp4,webm,mov,m4v,3gp,avi|max:51200` (50 MB)
   - `video`: `nullable|file|mimes:mp4,webm,mov,m4v,3gp,avi|max:51200`
   - `latitude` / `longitude`: `nullable|numeric|between:-90,90` / `-180,180`
4. **`StoreResumeRequest`**:
   - `team_om`: `required`
   - `problem_temuan`: `required|string|max:5000`
   - `action`: `required|string|max:5000`
   - `tipe_penanganan`: `nullable|in:JOINTING_LURUS,MANUVER_CORE,KEDUA,KOMBINASI,SEMUA,LAINNYA`
   - `redaman_sebelum`, `redaman_sesudah`: `nullable|numeric|between:-60,10`
5. **`StoreMaterialRequest`**: `nama_material` (`required|max:100`), `jumlah` (`required|integer|min:1`), `satuan` (`required|max:20`).
6. **`StoreTitikPerbaikanRequest`**: `nama_titik` (`required|max:50`), `latitude` (`required|numeric|between:-90,90`), `longitude` (`required|numeric|between:-180,180`), `keterangan` (`nullable|max:255`).
7. **`StoreJointClosureRequest`**: `nama_closure` (`required|max:100`), `kapasitas_kabel_asal` & `kapasitas_kabel_jumper` (`required|integer|in:2,4,6,8,12,24,48,96,144,288`), `tipe_closure` (`DOME,INLINE,BOX_FAT,OTB,CLOSURE_STANDAR`), `status_aset` (`EKSISTING,ASET_BARU`), `jenis_sambungan` (`LURUS_STRAIGHT,PERCABANGAN_BRANCH,LOOP_MANUVER`), serta array opsional `cores` / `tube_asal` / `core_asal` / dll.
8. **`StoreJointClosureCoreRequest`**: `tube_asal` (`required|max:30`), `core_asal` (`required|max:30`), `status_core`/`status` (`nullable|in:TERHUBUNG,SPARE,LOSS_PUTUS,MANUVER`), `loss_db` (`nullable|numeric|between:0,100`).
9. **`StoreManuverCoreRequest`**: `titik` (`required|max:50`), `core_asal` (`required`), `core_tujuan` (`required`), `tipe` (`required|in:SEBELUM,SESUDAH`), `lokasi_tipe` (`nullable|in:POP,OTB,CLOSURE_LAPANGAN,FAT_FDT,ODC`), `status_manuver` (`nullable|in:TEMPORARY,PERMANENT`), `status_core_aset` (`nullable|in:OCCUPIED_MANUVER,BROKEN_LOSS,SPARE_AVAILABLE`).
10. **`StoreDokumentasiRequest`**: `kategori` (`required|max:100`), `foto` & `photos.*` (`image|mimes:jpeg,jpg,png,webp|max:25600` — 25 MB), `source_photo_url` (`nullable|string`).

---

## 8. Panduan Checklist Porting Fitur ke Aplikasi Lain

Jika ingin memindahkan modul-modul ini ke project Laravel lain:

1. **Instalasi Paket & Ekstensi PHP**:
   - Install `barryvdh/laravel-dompdf`, `maatwebsite/excel`, dan `minishlink/web-push`.
   - Aktifkan ekstensi PHP `gd` dan `exif` di `php.ini`.
2. **Konfigurasi Environment (`.env`) & Config**:
   - Atur `APP_TIMEZONE=Asia/Jakarta` di `config/app.php` / `.env`.
   - Jika memakai fitur Broadband Lookup, tambahkan koneksi `'ims'` di `config/database.php` beserta variabel `DB_IMS_HOST`, `DB_IMS_PORT`, `DB_IMS_DATABASE`, `DB_IMS_USERNAME`, `DB_IMS_PASSWORD`.
   - Tambahkan konfigurasi `'vapid'` (`VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`) dan `'whatsapp'` (`WA_API_URL`, `WA_API_KEY`, `WA_SIMULATE`) di `config/services.php`.
3. **Database Migrations & Storage**:
   - Jalankan migrasi secara berurutan (pastikan tabel `users` sudah memiliki kolom `username`, `role`, `phone`, `avatar`, `is_active` sebelum tabel `tiket` dibuat).
   - Jalankan `php artisan storage:link` dan pastikan direktori `public/uploads/kronologis` serta `public/uploads/kronologis/videos` writable oleh web server.
4. **Salin Komponen Inti Backend**:
   - **Middleware**: `RoleMiddleware` -> daftarkan alias `'role'` di `bootstrap/app.php`.
   - **Models**: Salin seluruh 16 model di `app/Models/`.
   - **Services**: Salin seluruh service di `app/Services/` dan daftarkan binding constructor `TiketService` & `KronologisService` di `AppServiceProvider`.
   - **Requests, Controllers, Notifications, Exports**: Salin sesuai modul yang ingin diaktifkan dan petakan endpoint-nya di `routes/web.php` (atau adaptasi ke `routes/api.php` dengan Sanctum jika aplikasi target berupa REST API/SPA).
5. **Catatan Penyesuaian Teknis Saat Porting (Temuan Kode Saat Ini)**:
   Beberapa detail implementasi pada codebase saat ini yang sebaiknya langsung dirapikan saat diadopsi ke aplikasi baru:
   - **Sinkronisasi `FormRequest::authorize()` vs Middleware Route**: Route mengizinkan `manager_teknisi` untuk `POST` kronologis, resume, material, titik perbaikan, dokumentasi, manuver core, dan joint closure, sedangkan `authorize()` pada beberapa `FormRequest` (`StoreKronologisRequest`, `StoreResumeRequest`, `StoreMaterialRequest`, `StoreTitikPerbaikanRequest`, `StoreJointClosureRequest`, `StoreJointClosureCoreRequest`, `StoreManuverCoreRequest`, `StoreDokumentasiRequest`) serta `ResumeService::deleteMaterial`/`deleteTitikPerbaikan` saat ini hanya memeriksa `['admin', 'helpdesk', 'teknis']`. Tambahkan `'manager_teknisi'` jika role tersebut juga harus dapat mengisi/menghapus data lapangan.
   - **Atribut Template WhatsApp (`WhatsAppService`)**: `formatTiketBaruMessage()` mengakses `$tiket->keterangan` (kolom sebenarnya `deskripsi`), dan `formatKronologisMessage()` mengakses `$kronologis->jam_kronologis` & `$kronologis->keterangan` (kolom sebenarnya `timestamp` & `informasi`). Pastikan accessor atau nama field disamakan saat porting.
   - **Kolom Redaman di `resume_pekerjaan`**: Model `ResumePekerjaan` mencantumkan `redaman_sebelum` & `redaman_sesudah` di `$fillable` dan `ResumeService` mengirimkannya saat `updateOrCreate`, namun kolom fisiknya baru ada di tabel `tiket` (`2026_09_28_145616`). Tambahkan dua kolom tersebut di migrasi `resume_pekerjaan` atau hapus dari payload `ResumePekerjaan` agar tidak gagal jika `strict` SQL mode memengaruhi query.
   - **Kolom `user_id` di `manuver_core`**: `ManuverCoreService::addManuver` mengirim `'user_id' => auth()->id()` saat `create()`, namun kolom `user_id` belum ada di migrasi `manuver_core` maupun di `$fillable` `ManuverCore`. Tambahkan kolom `user_id` jika audit user per manuver diperlukan.
   - **Nilai `lokasi_tipe` & `status_manuver` pada `ManuverCore`**: Validasi `StoreManuverCoreRequest` memakai `lokasi_tipe in:POP,OTB,CLOSURE_LAPANGAN,FAT_FDT,ODC` dan `status_manuver in:TEMPORARY,PERMANENT`, sedangkan migrasi `manuver_core` mendefinisikan enum `['POP','OTB','CLOSURE_LAPANGAN','FAT_FDT','LAINNYA']` dan `['TEMPORARY','PERMANENT','RESTORED']`. Samakan kedua daftar enum tersebut di aplikasi target.

