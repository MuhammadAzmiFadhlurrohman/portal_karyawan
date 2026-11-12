<?php

namespace Database\Seeders;

use App\Models\ApprovalDelegation;
use App\Models\ChatbotIntent;
use App\Models\MasterMaterial;
use App\Models\MasterSla;
use App\Models\MessageTemplate;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for Fase 0 (§4, §6.3).
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Attendance & HRIS
            'attendance.employee.manage',
            'attendance.schedule.generate',
            'attendance.leave.approve',
            'attendance.correction.approve',
            'attendance.deduction.lock_period',
            'attendance.face.reset',
            // Ticketing
            'ticket.create',
            'ticket.dispatch',
            'ticket.closing_awal',
            'ticket.verify_close',
            'ticket.reopen',
            'ticket.cancel',
            'ticket.stop_clock.approve',
            'ticket.report.view_all',
            // GOMSN Operational
            'gomsn.pengajuan.approve_atasan',
            'gomsn.pengajuan.approve_finance',
            'gomsn.pengajuan.approve_executive',
            'gomsn.lembur.approve',
            'gomsn.lembur.pay',
            'gomsn.tasks.manage',
            // Portal & Security
            'portal.settings.manage',
            'portal.sessions.manage',
            'portal.audit.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $rolePermissions = [
            'super_admin' => $permissions,
            'ceo' => [
                'ticket.report.view_all',
                'gomsn.pengajuan.approve_executive',
                'portal.audit.view',
            ],
            'hrd' => [
                'attendance.employee.manage',
                'attendance.leave.approve',
                'attendance.correction.approve',
                'attendance.deduction.lock_period',
                'attendance.face.reset',
                'gomsn.pengajuan.approve_executive',
                'portal.sessions.manage',
                'portal.audit.view',
            ],
            'manager_teknisi' => [
                'attendance.schedule.generate',
                'attendance.leave.approve',
                'ticket.dispatch',
                'ticket.reopen',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
                'gomsn.pengajuan.approve_atasan',
                'gomsn.lembur.approve',
                'gomsn.tasks.manage',
            ],
            'helpdesk' => [
                'ticket.create',
                'ticket.verify_close',
                'ticket.reopen',
                'ticket.cancel',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
            ],
            'noc' => [
                'ticket.create',
                'ticket.verify_close',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
            ],
            'sa_cs' => [
                'ticket.create',
                'ticket.report.view_all',
            ],
            'teknisi' => [
                'ticket.closing_awal',
            ],
            'atasan' => [
                'attendance.leave.approve',
                'attendance.correction.approve',
                'gomsn.pengajuan.approve_atasan',
                'gomsn.lembur.approve',
                'gomsn.tasks.manage',
            ],
            'finance' => [
                'gomsn.pengajuan.approve_finance',
                'gomsn.lembur.pay',
            ],
            'employee' => [],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($perms);
        }

        // Seed Users with Supervisor Hierarchy (§4.3)
        $defaultPassword = Hash::make('MsnPortal#2026');

        $ceo = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-001'],
            [
                'username' => 'direktur',
                'name' => 'Hendra Wijaya',
                'email' => 'ceo@msn.co.id',
                'phone' => '08112000001',
                'nik' => '3204010000000001',
                'division' => 'Executive',
                'position' => 'Chief Executive Officer',
                'join_date' => '2020-01-10',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $ceo->syncRoles(['ceo', 'atasan']);

        $superAdmin = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-002'],
            [
                'username' => 'admin',
                'name' => 'Rizky Pratama (Super Admin)',
                'email' => 'admin@msn.co.id',
                'phone' => '08112000002',
                'nik' => '3204010000000002',
                'division' => 'IT & Sistem',
                'position' => 'System Administrator',
                'supervisor_id' => $ceo->id,
                'join_date' => '2021-03-01',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $superAdmin->syncRoles(['super_admin', 'employee']);

        $hrd = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-003'],
            [
                'username' => 'hrd',
                'name' => 'Siti Nurhaliza',
                'email' => 'hrd@msn.co.id',
                'phone' => '08112000003',
                'nik' => '3204010000000003',
                'division' => 'HRD & GA',
                'position' => 'HRD Manager',
                'supervisor_id' => $ceo->id,
                'join_date' => '2021-05-15',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $hrd->syncRoles(['hrd', 'atasan', 'employee']);

        $managerTeknis = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-004'],
            [
                'username' => 'mgr_teknis',
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.teknis@msn.co.id',
                'phone' => '08112000004',
                'nik' => '3204010000000004',
                'division' => 'Operasional Jaringan',
                'position' => 'Manager Teknis',
                'supervisor_id' => $ceo->id,
                'join_date' => '2021-02-01',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $managerTeknis->syncRoles(['manager_teknisi', 'atasan', 'employee']);

        $finance = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-005'],
            [
                'username' => 'finance',
                'name' => 'Melani Putri',
                'email' => 'finance@msn.co.id',
                'phone' => '08112000005',
                'nik' => '3204010000000005',
                'division' => 'Finance & Accounting',
                'position' => 'Finance Supervisor',
                'supervisor_id' => $ceo->id,
                'join_date' => '2022-01-10',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $finance->syncRoles(['finance', 'employee']);

        $helpdesk = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-006'],
            [
                'username' => 'helpdesk',
                'name' => 'Fajar Nugraha',
                'email' => 'noc@msn.co.id',
                'phone' => '08112000006',
                'nik' => '3204010000000006',
                'division' => 'NOC & Helpdesk',
                'position' => 'Helpdesk NOC Specialist',
                'supervisor_id' => $managerTeknis->id,
                'join_date' => '2023-04-01',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $helpdesk->syncRoles(['helpdesk', 'noc', 'employee']);

        $teknisi = User::query()->updateOrCreate(
            ['employee_code' => 'MSN-007'],
            [
                'username' => 'teknisi1',
                'name' => 'Agus Setiawan',
                'email' => 'agus.teknisi@msn.co.id',
                'phone' => '08112000007',
                'nik' => '3204010000000007',
                'division' => 'Operasional Jaringan',
                'position' => 'Teknisi Backbone & FO',
                'supervisor_id' => $managerTeknis->id,
                'join_date' => '2023-06-12',
                'must_change_password' => false,
                'password_changed_at' => now(),
                'is_active' => true,
                'password' => $defaultPassword,
            ]
        );
        $teknisi->syncRoles(['teknisi', 'employee']);

        // Seed System Settings (§6.3)
        $settings = [
            [
                'key' => 'attendance.late_fee_per_minute',
                'group' => 'attendance',
                'label' => 'Tarif Denda Keterlambatan per Menit (Rp)',
                'value' => '250',
                'type' => 'decimal',
                'description' => 'Tarif potongan keterlambatan per menit (Rp 15.000/jam = Rp 250/menit) sesuai §5.5.',
            ],
            [
                'key' => 'attendance.late_tolerance_minutes',
                'group' => 'attendance',
                'label' => 'Toleransi Keterlambatan Default (Menit)',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Batas menit toleransi sebelum denda keterlambatan dihitung (§5.2, §5.5).',
            ],
            [
                'key' => 'attendance.daily_max_late_fee',
                'group' => 'attendance',
                'label' => 'Batas Maksimum Denda Terlambat Harian (Rp)',
                'value' => '50000',
                'type' => 'decimal',
                'description' => 'Plafon maksimum potongan terlambat per hari kerja (§5.5).',
            ],
            [
                'key' => 'attendance.default_alpa_fee',
                'group' => 'attendance',
                'label' => 'Tarif Potongan Alpa Default per Hari (Rp)',
                'value' => '25000',
                'type' => 'decimal',
                'description' => 'Potongan alpa per hari bila users.potongan_alpa kosong (§5.5).',
            ],
            [
                'key' => 'attendance.correction_window_days',
                'group' => 'attendance',
                'label' => 'Batas Waktu Pengajuan Koreksi Absensi (Hari)',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Maksimal hari kalender sejak kejadian untuk mengajukan koreksi absensi (§5.6).',
            ],
            [
                'key' => 'face.verify_threshold',
                'group' => 'biometric',
                'label' => 'Ambang Skor Verifikasi Wajah 1:1',
                'value' => '0.50',
                'type' => 'float',
                'description' => 'Threshold cosine similarity InsightFace buffalo_l untuk verifikasi 1:1 (§5.1).',
            ],
            [
                'key' => 'face.max_failed_attempts',
                'group' => 'biometric',
                'label' => 'Maksimum Gagal Wajah Sebelum Jalur Manual',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Setelah 3x gagal berturut-turut, arahkan ke jalur manual pending_review HRD (§5.1).',
            ],
            [
                'key' => 'geofence.default_radius_m',
                'group' => 'geofence',
                'label' => 'Radius Geofence Kantor Default (Meter)',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Radius default geofence kantor (pilot 50m, dapat diperketat ke 20m sesuai §5.1).',
            ],
            [
                'key' => 'geofence.max_gps_accuracy_m',
                'group' => 'geofence',
                'label' => 'Batas Akurasi GPS Maksimum Tanpa Flag (Meter)',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Absensi dengan gps_accuracy > 30m otomatis ditandai flagged (§5.1).',
            ],
            [
                'key' => 'leave.annual_quota_days',
                'group' => 'leave',
                'label' => 'Kuota Cuti Tahunan (Hari Kerja)',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Total hak cuti tahunan karyawan (§5.4).',
            ],
            [
                'key' => 'leave.monthly_max_days',
                'group' => 'leave',
                'label' => 'Batas Maksimum Cuti Bulanan (Hari Kerja)',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Batas pengambilan cuti per bulan adalah 3 hari kerja (§5.4).',
            ],
            [
                'key' => 'leave.alpa_converts_to_leave',
                'group' => 'leave',
                'label' => 'Konversi Otomatis Alpa ke Kuota Cuti',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Opsi konversi 3 alpa pertama per bulan menjadi pemotongan cuti dengan masa sanggah 3 hari kerja (§5.4).',
            ],
            [
                'key' => 'leave.division_max_concurrent',
                'group' => 'leave',
                'label' => 'Batas Maksimum Cuti Bersamaan per Divisi',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Maksimal jumlah karyawan dalam divisi yang sama yang diizinkan cuti di hari yang sama (§5.4).',
            ],
            [
                'key' => 'leave.sick_attachment_min_days',
                'group' => 'leave',
                'label' => 'Batas Hari Cuti Sakit Wajib Surat Dokter',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Cuti sakit melebihi jumlah hari kerja ini wajib melampirkan surat keterangan dokter (§5.4).',
            ],
            [
                'key' => 'ticket.sla_backbone_minutes',
                'group' => 'ticketing',
                'label' => 'Target SLA Default Tiket Backbone (Menit)',
                'value' => '360',
                'type' => 'integer',
                'description' => 'Target penyelesaian gangguan Backbone BDG- (360 menit / 6 jam, §7.1).',
            ],
            [
                'key' => 'ticket.sla_broadband_minutes',
                'group' => 'ticketing',
                'label' => 'Target SLA Default Tiket Broadband (Menit)',
                'value' => '240',
                'type' => 'integer',
                'description' => 'Target penyelesaian gangguan Broadband BRD- (240 menit / 4 jam, §7.1).',
            ],
            [
                'key' => 'ticket.chronology_warning_minutes',
                'group' => 'ticketing',
                'label' => 'Batas Interval Kronologis OVERDUE (Menit)',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Wajib update kronologis setiap 30 menit saat tiket PROSES (§7.3).',
            ],
            [
                'key' => 'ticket.stop_clock_approval_threshold_minutes',
                'group' => 'ticketing',
                'label' => 'Ambang Durasi Stop Clock Wajib Approval (Menit)',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Stop Clock di atas 120 menit memerlukan persetujuan Manager Teknis/Helpdesk (§7.4, AC-07).',
            ],
            [
                'key' => 'gomsn.executive_approval_threshold',
                'group' => 'gomsn',
                'label' => 'Ambang Nominal Pengajuan Dana Wajib Approval HRD/CEO (Rp)',
                'value' => '5000000',
                'type' => 'decimal',
                'description' => 'Pengajuan di atas nominal ini memerlukan persetujuan tambahan HRD/CEO (§8.1).',
            ],
            [
                'key' => 'security.max_devices_per_user',
                'group' => 'security',
                'label' => 'Maksimal Perangkat Terdaftar per Pengguna',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Device binding maksimal 2 perangkat aktif per karyawan (§5.1).',
            ],
            [
                'key' => 'security.session_idle_timeout_minutes',
                'group' => 'security',
                'label' => 'Batas Waktu Idle Sesi Login (Menit)',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Sesi otomatis berakhir bila tidak aktif melebihi batas menit ini (§6.1).',
            ],
        ];

        foreach ($settings as $item) {
            if (! SystemSetting::query()->where('key', $item['key'])->exists()) {
                SystemSetting::query()->create(array_merge($item, [
                    'effective_from' => now()->startOfMonth(),
                    'updated_by' => $superAdmin->id,
                    'change_reason' => 'Inisialisasi parameter dasar PRD v2.0',
                ]));
            }
        }

        // Sample Active Delegation (§4.3)
        ApprovalDelegation::query()->firstOrCreate(
            [
                'delegator_id' => $ceo->id,
                'delegate_id' => $hrd->id,
                'module' => 'attendance',
            ],
            [
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
                'reason' => 'Delegasi persetujuan cuti & koreksi selama perjalanan dinas direksi.',
                'is_active' => true,
                'created_by' => $superAdmin->id,
            ]
        );

        // Fase 1: Master Office Locations (§5.1)
        $bandungOffice = OfficeLocation::query()->updateOrCreate(
            ['code' => 'BDG-HQ'],
            [
                'name' => 'Kantor Pusat Bandung',
                'address' => 'Bandung, Jawa Barat',
                'lat' => -6.9371220,
                'lng' => 107.6319890,
                'radius_m' => 50,
                'is_active' => true,
            ]
        );

        OfficeLocation::query()->updateOrCreate(
            ['code' => 'SRG-OPS'],
            [
                'name' => 'Kantor Operasional Soreang',
                'address' => 'Soreang, Kab. Bandung, Jawa Barat',
                'lat' => -7.0422150,
                'lng' => 107.5409810,
                'radius_m' => 50,
                'is_active' => true,
            ]
        );

        // Fase 1: Master Shifts (§5.2)
        $shiftsData = [
            ['code' => 'P', 'name' => 'Shift Pagi', 'start_time' => '08:15:00', 'end_time' => '17:00:00', 'color' => '#1E40AF', 'late_tolerance_minutes' => 15, 'break_minutes' => 60, 'is_day_off' => false, 'is_piket' => false],
            ['code' => 'S', 'name' => 'Shift Siang', 'start_time' => '13:00:00', 'end_time' => '21:00:00', 'color' => '#2563EB', 'late_tolerance_minutes' => 15, 'break_minutes' => 60, 'is_day_off' => false, 'is_piket' => false],
            ['code' => 'SB', 'name' => 'Shift Subuh', 'start_time' => '06:00:00', 'end_time' => '15:00:00', 'color' => '#0284C7', 'late_tolerance_minutes' => 15, 'break_minutes' => 60, 'is_day_off' => false, 'is_piket' => false],
            ['code' => 'BJ', 'name' => 'BJS (Visit Soreang)', 'start_time' => '08:15:00', 'end_time' => '17:00:00', 'color' => '#0D9488', 'late_tolerance_minutes' => 15, 'break_minutes' => 60, 'is_day_off' => false, 'is_piket' => false],
            ['code' => 'PK', 'name' => 'Piket Pagi', 'start_time' => '08:15:00', 'end_time' => '17:00:00', 'color' => '#D97706', 'late_tolerance_minutes' => 15, 'break_minutes' => 60, 'is_day_off' => false, 'is_piket' => true],
            ['code' => 'PA', 'name' => 'Piket Siang', 'start_time' => '13:00:00', 'end_time' => '21:00:00', 'color' => '#EA580C', 'late_tolerance_minutes' => 15, 'break_minutes' => 60, 'is_day_off' => false, 'is_piket' => true],
            ['code' => 'L', 'name' => 'Libur', 'start_time' => null, 'end_time' => null, 'color' => '#64748B', 'late_tolerance_minutes' => 0, 'break_minutes' => 0, 'is_day_off' => true, 'is_piket' => false],
        ];

        $shiftPagi = null;
        foreach ($shiftsData as $sData) {
            $shift = Shift::query()->updateOrCreate(['code' => $sData['code']], $sData);
            if ($sData['code'] === 'P') {
                $shiftPagi = $shift;
            }
        }

        $holiday = NationalHoliday::query()->whereDate('date', '2026-12-25')->first();
        if ($holiday) {
            $holiday->update([
                'name' => 'Hari Raya Natal',
                'is_collective_leave' => false,
                'description' => 'Libur Nasional',
            ]);
        } else {
            NationalHoliday::query()->create([
                'date' => '2026-12-25',
                'name' => 'Hari Raya Natal',
                'is_collective_leave' => false,
                'description' => 'Libur Nasional',
            ]);
        }

        // Seed today's schedule for active users so clock-in works immediately
        if ($shiftPagi) {
            $today = now('Asia/Jakarta')->toDateString();
            foreach ([$ceo, $superAdmin, $hrd, $managerTeknis, $finance, $helpdesk, $teknisi] as $u) {
                $existingSched = MonthlySchedule::query()
                    ->where('user_id', $u->id)
                    ->whereDate('date', $today)
                    ->first();

                if ($existingSched) {
                    $existingSched->update([
                        'shift_id' => $shiftPagi->id,
                        'office_location_id' => $bandungOffice->id,
                    ]);
                } else {
                    MonthlySchedule::query()->create([
                        'user_id' => $u->id,
                        'date' => $today,
                        'shift_id' => $shiftPagi->id,
                        'office_location_id' => $bandungOffice->id,
                        'created_by' => $superAdmin->id,
                    ]);
                }
            }
        }

        // Fase 3: Master SLA per Category and Priority (§7.1)
        $slas = [
            ['kategori_tiket' => 'BACKBONE', 'priority' => 'P1', 'sla_minutes' => 180, 'description' => 'Backbone Total Down P1'],
            ['kategori_tiket' => 'BACKBONE', 'priority' => 'P2', 'sla_minutes' => 360, 'description' => 'Backbone Degraded P2 (Default)'],
            ['kategori_tiket' => 'BACKBONE', 'priority' => 'P3', 'sla_minutes' => 480, 'description' => 'Backbone Warning P3'],
            ['kategori_tiket' => 'BACKBONE', 'priority' => 'P4', 'sla_minutes' => 720, 'description' => 'Backbone Planned/Low P4'],

            ['kategori_tiket' => 'BROADBAND', 'priority' => 'P1', 'sla_minutes' => 120, 'description' => 'Broadband VIP Total Down P1'],
            ['kategori_tiket' => 'BROADBAND', 'priority' => 'P2', 'sla_minutes' => 240, 'description' => 'Broadband Standar P2 (Default)'],
            ['kategori_tiket' => 'BROADBAND', 'priority' => 'P3', 'sla_minutes' => 360, 'description' => 'Broadband Intermittent P3'],
            ['kategori_tiket' => 'BROADBAND', 'priority' => 'P4', 'sla_minutes' => 480, 'description' => 'Broadband Minor Request P4'],
        ];
        foreach ($slas as $s) {
            MasterSla::query()->updateOrCreate(
                ['kategori_tiket' => $s['kategori_tiket'], 'priority' => $s['priority']],
                $s
            );
        }

        // Fase 3: Master Materials (§7.6)
        $materials = [
            ['kode' => 'KBL-FO-24C', 'nama' => 'Kabel FO 24 Core ADSS', 'satuan' => 'meter', 'stok_tersedia' => 500, 'min_stok' => 50],
            ['kode' => 'JC-24C', 'nama' => 'Joint Closure Dome 24 Core', 'satuan' => 'pcs', 'stok_tersedia' => 20, 'min_stok' => 5],
            ['kode' => 'PIGTAIL-SC', 'nama' => 'Pigtail SC-UPC 1.5m', 'satuan' => 'pcs', 'stok_tersedia' => 50, 'min_stok' => 10],
            ['kode' => 'PROTECTION-SLEEVE', 'nama' => 'Protection Sleeve 60mm', 'satuan' => 'pcs', 'stok_tersedia' => 200, 'min_stok' => 30],
            ['kode' => 'PATCHCORD-SC', 'nama' => 'Patchcord SC-SC Simplex 3m', 'satuan' => 'pcs', 'stok_tersedia' => 30, 'min_stok' => 5],
        ];
        foreach ($materials as $m) {
            MasterMaterial::query()->updateOrCreate(['kode' => $m['kode']], $m);
        }

        // Fase 3: Notification Message Templates (§7.8)
        $templates = [
            ['code' => 'TICKET_DISPATCHED', 'channel' => 'whatsapp', 'subject_template' => 'Penugasan Tiket', 'body_template' => 'Halo {name}, Anda telah ditugaskan menangani tiket {no_tiket} ({kategori}) dengan prioritas {priority}. SLA Target: {sla_minutes} menit.'],
            ['code' => 'STOP_CLOCK_REQUEST', 'channel' => 'whatsapp', 'subject_template' => 'Permintaan Stop Clock', 'body_template' => 'Tiket {no_tiket} meminta Stop Clock untuk alasan {reason}: {notes}. Menunggu persetujuan.'],
            ['code' => 'HANDOVER_PENDING', 'channel' => 'whatsapp', 'subject_template' => 'Handover Shift', 'body_template' => 'Shift handover tiket {no_tiket} menunggu konfirmasi Anda.'],
        ];
        foreach ($templates as $t) {
            MessageTemplate::query()->updateOrCreate(['code' => $t['code']], $t);
        }

        // Fase 4: Chatbot 20+ Intents (§5.8)
        $intents = [
            ['intent_key' => 'sisa_cuti', 'category' => 'cuti', 'keywords' => ['sisa', 'cuti', 'kuota', 'saldo'], 'response_text' => 'Anda dapat melihat sisa cuti Anda melalui menu Cuti di Portal Karyawan.'],
            ['intent_key' => 'cara_ajukan_cuti', 'category' => 'cuti', 'keywords' => ['ajukan', 'cuti', 'form', 'izin'], 'response_text' => 'Pengajuan cuti dapat dilakukan di menu Cuti maksimal 3 hari kerja berturut-turut per bulan, membutuhkan persetujuan Atasan dan HRD.'],
            ['intent_key' => 'status_absen_hari_ini', 'category' => 'absensi', 'keywords' => ['absen', 'hadir', 'clock', 'hari ini'], 'response_text' => 'Status kehadiran hari ini dapat dicek di menu Absensi & Biometrik.'],
            ['intent_key' => 'tiket_aktif_saya', 'category' => 'tiket', 'keywords' => ['tiket', 'aktif', 'saya', 'tugas'], 'response_text' => 'Tiket gangguan aktif Anda dapat dilihat di menu Tiketing Gangguan pada filter status OPEN/PROSES.'],
            ['intent_key' => 'aturan_jeda_shift', 'category' => 'jadwal', 'keywords' => ['jeda', 'shift', '11 jam', 'istirahat'], 'response_text' => 'Sistem mewajibkan jeda waktu istirahat minimum 11 jam di antara pergantian shift kerja untuk menjaga kesehatan karyawan.'],
            ['intent_key' => 'cara_tukar_shift', 'category' => 'jadwal', 'keywords' => ['tukar', 'shift', 'swap', 'ganti'], 'response_text' => 'Tukar shift 4-arah dapat diajukan di menu Tukar Shift maksimal 48 jam sebelum jadwal dan wajib disetujui rekan target dan atasan.'],
            ['intent_key' => 'cara_ajukan_lembur', 'category' => 'gomsn', 'keywords' => ['lembur', 'overtime', 'pengajuan', 'jam kerja'], 'response_text' => 'Lembur diajukan melalui modul GOMSN Operasional > Lembur, maksimal 4 jam per hari, dan wajib disetujui Atasan.'],
            ['intent_key' => 'pengajuan_dana', 'category' => 'gomsn', 'keywords' => ['dana', 'barang', 'kasbon', 'uang'], 'response_text' => 'Pengajuan dana atau pembelian barang dilakukan di modul Pengajuan Dana/Barang, dengan batas nominal bertingkat Atasan -> Finance -> Eksekutif.'],
            ['intent_key' => 'nota_realisasi', 'category' => 'gomsn', 'keywords' => ['nota', 'realisasi', 'bukti', 'struk'], 'response_text' => 'Nota realisasi pembelian wajib diunggah dalam batas 7 hari kalender setelah dana dicairkan. Keterlambatan akan memblokir pengajuan baru.'],
            ['intent_key' => 'geofencing_kantor', 'category' => 'absensi', 'keywords' => ['radius', 'geofence', 'jarak', 'kantor', 'lokasi'], 'response_text' => 'Radius absensi tipe Office adalah 50 meter dari titik koordinat kantor Soreang atau Bandung ditambah toleransi akurasi GPS perangkat.'],
            ['intent_key' => 'stop_clock_aturan', 'category' => 'tiket', 'keywords' => ['stop', 'clock', 'jeda', 'sla'], 'response_text' => 'Stop Clock digunakan untuk menghentikan hitungan SLA saat kendala di luar kendali teknisi. Stop Clock > 120 menit wajib persetujuan Atasan/Helpdesk.'],
            ['intent_key' => 'liveness_wajah', 'category' => 'absensi', 'keywords' => ['liveness', 'wajah', 'gagal', 'kamera'], 'response_text' => 'Pastikan pencahayaan cukup dan wajah tegak lurus ke kamera. Setelah 3 kali gagal berturut-turut, sistem akan membuka opsi pengajuan absensi manual ke HRD.'],
            ['intent_key' => 'koreksi_absensi', 'category' => 'absensi', 'keywords' => ['koreksi', 'salah', 'lupa', 'absen'], 'response_text' => 'Pengajuan koreksi status kehadiran dapat dilakukan maksimal 7 hari sejak tanggal kejadian dengan melampirkan bukti yang sah.'],
            ['intent_key' => 'potongan_alpa', 'category' => 'hris', 'keywords' => ['potongan', 'denda', 'gaji', 'alpa', 'terlambat'], 'response_text' => 'Perhitungan potongan keterlambatan dan alpa berjalan otomatis setiap malam dan dapat ditinjau pada menu Potongan & Koreksi.'],
            ['intent_key' => 'leaderboard_hadir', 'category' => 'hris', 'keywords' => ['leaderboard', 'peringkat', 'juara', 'rajin'], 'response_text' => 'Leaderboard kehadiran dihitung dari persentase kehadiran tertinggi, menit terlambat terendah, dan konsistensi tepat waktu.'],
            ['intent_key' => 'buka_tiket_backbone', 'category' => 'tiket', 'keywords' => ['backbone', 'bdg', 'kabel putus'], 'response_text' => 'Tiket Backbone menggunakan kode awalan BDG- dengan target SLA default 360 menit (P2) atau 180 menit (P1).'],
            ['intent_key' => 'buka_tiket_broadband', 'category' => 'tiket', 'keywords' => ['broadband', 'brd', 'pelanggan', 'los'], 'response_text' => 'Tiket Broadband memakai kode awalan BRD- dan terintegrasi dengan data pelanggan IMS dengan target SLA default 240 menit.'],
            ['intent_key' => 'kpi_teknisi', 'category' => 'kpi', 'keywords' => ['kpi', 'skor', 'nilai', 'bobot'], 'response_text' => 'Skor KPI Teknisi (0-100) dihitung dari rumus: 45% Ketepatan SLA + 35% Kepatuhan Interval Update 30 Menit + 20% Kecepatan Respon Awal.'],
            ['intent_key' => 'kontak_helpdesk', 'category' => 'bantuan', 'keywords' => ['kontak', 'helpdesk', 'noc', 'telepon'], 'response_text' => 'Helpdesk NOC PT MSN dapat dihubungi melalui WhatsApp 08112000006 atau email noc@msn.co.id.'],
            ['intent_key' => 'kontak_hrd', 'category' => 'bantuan', 'keywords' => ['kontak', 'hrd', 'personalia'], 'response_text' => 'Bagian HRD & GA dapat dihubungi melalui email hrd@msn.co.id.'],
            ['intent_key' => 'pencairan_lembur', 'category' => 'gomsn', 'keywords' => ['bayar', 'cair', 'gaji', 'lemburan'], 'response_text' => 'Pembayaran uang lembur dilakukan oleh bagian Finance pada periode tutup buku bulanan setelah diverifikasi dengan data absensi.'],
        ];

        foreach ($intents as $in) {
            ChatbotIntent::query()->updateOrCreate(['intent_key' => $in['intent_key']], $in);
        }

        // Lembur & Approval Seeder (§8.2)
        $this->call(LemburSeeder::class);
    }
}
