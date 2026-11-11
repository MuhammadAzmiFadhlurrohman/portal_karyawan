<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Roles & Divisi requested by user:
     * - Karyawan NOC (Divisi: NOC)
     * - Karyawan Teknik (Divisi: Teknik)
     * - Karyawan Umum (Divisi: Umum)
     * - Atasan NOC (Divisi: NOC)
     * - Atasan Teknik (Divisi: Teknik)
     * - Atasan Umum (Divisi: Umum)
     * - Finance (Divisi: Finance & Akuntansi)
     * - Admin (Divisi: IT & Sistem)
     * - HRD (Divisi: HRD & Human Capital)
     * - CEO (Divisi: Executive)
     */
    public function run(): void
    {
        // 1. Ensure all core permissions exist
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

        // 2. Define role permissions mapping
        $rolePermissions = [
            // Admin (Super Admin)
            'admin' => $permissions,
            'super_admin' => $permissions,

            // Executive & Manajerial
            'ceo' => [
                'ticket.report.view_all',
                'gomsn.pengajuan.approve_executive',
                'portal.audit.view',
            ],

            // HRD
            'hrd' => [
                'attendance.employee.manage',
                'attendance.schedule.generate',
                'attendance.leave.approve',
                'attendance.correction.approve',
                'attendance.deduction.lock_period',
                'attendance.face.reset',
                'gomsn.pengajuan.approve_executive',
                'portal.sessions.manage',
                'portal.audit.view',
            ],

            // Finance
            'finance' => [
                'gomsn.pengajuan.approve_finance',
                'gomsn.lembur.pay',
            ],

            // Atasan NOC (Supervisor NOC)
            'atasan_noc' => [
                'attendance.schedule.generate',
                'attendance.leave.approve',
                'attendance.correction.approve',
                'ticket.create',
                'ticket.dispatch',
                'ticket.verify_close',
                'ticket.reopen',
                'ticket.cancel',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
                'gomsn.pengajuan.approve_atasan',
                'gomsn.lembur.approve',
                'gomsn.tasks.manage',
            ],

            // Atasan Teknik (Supervisor / Manager Lapangan & Jaringan)
            'atasan_teknik' => [
                'attendance.schedule.generate',
                'attendance.leave.approve',
                'attendance.correction.approve',
                'ticket.dispatch',
                'ticket.verify_close',
                'ticket.reopen',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
                'gomsn.pengajuan.approve_atasan',
                'gomsn.lembur.approve',
                'gomsn.tasks.manage',
            ],

            // Atasan Umum (Supervisor Divisi Umum / GA)
            'atasan_umum' => [
                'attendance.leave.approve',
                'attendance.correction.approve',
                'gomsn.pengajuan.approve_atasan',
                'gomsn.lembur.approve',
                'gomsn.tasks.manage',
            ],

            // Karyawan NOC
            'karyawan_noc' => [
                'ticket.create',
                'ticket.verify_close',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
            ],

            // Karyawan Teknik (Teknisi Lapangan / FO)
            'karyawan_teknik' => [
                'ticket.closing_awal',
            ],

            // Karyawan Umum (Staff Umum / GA)
            'karyawan_umum' => [],

            // Legacy base roles preserved for backward compatibility
            'atasan' => [
                'attendance.leave.approve',
                'attendance.correction.approve',
                'gomsn.pengajuan.approve_atasan',
                'gomsn.lembur.approve',
                'gomsn.tasks.manage',
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
            'noc' => [
                'ticket.create',
                'ticket.verify_close',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
            ],
            'teknisi' => [
                'ticket.closing_awal',
            ],
            'helpdesk' => [
                'ticket.create',
                'ticket.verify_close',
                'ticket.reopen',
                'ticket.cancel',
                'ticket.stop_clock.approve',
                'ticket.report.view_all',
            ],
            'sa_cs' => [
                'ticket.create',
                'ticket.report.view_all',
            ],
            'employee' => [],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($perms);
        }

        // 3. Populate / Update representative users for each role and division
        $defaultPassword = Hash::make('MsnPortal#2026');

        $usersData = [
            // 1. CEO (Executive)
            [
                'employee_code' => 'MSN-001',
                'username' => 'direktur',
                'name' => 'Hendra Wijaya',
                'email' => 'ceo@msn.co.id',
                'phone' => '08112000001',
                'nik' => '3204010000000001',
                'division' => 'Executive',
                'position' => 'Chief Executive Officer',
                'roles' => ['ceo', 'atasan'],
                'supervisor_id' => null,
            ],
            // 2. Admin (IT & Sistem)
            [
                'employee_code' => 'MSN-002',
                'username' => 'admin',
                'name' => 'Rizky Pratama',
                'email' => 'admin@msn.co.id',
                'phone' => '08112000002',
                'nik' => '3204010000000002',
                'division' => 'IT & Sistem',
                'position' => 'System Administrator & IT Security',
                'roles' => ['admin', 'super_admin'],
                'supervisor_id' => 1,
            ],
            // 3. HRD (HRD & Human Capital)
            [
                'employee_code' => 'MSN-003',
                'username' => 'hrd',
                'name' => 'Siti Nurhaliza',
                'email' => 'hrd@msn.co.id',
                'phone' => '08112000003',
                'nik' => '3204010000000003',
                'division' => 'HRD & Human Capital',
                'position' => 'Head of Human Resources',
                'roles' => ['hrd', 'atasan'],
                'supervisor_id' => 1,
            ],
            // 4. Finance (Finance & Akuntansi)
            [
                'employee_code' => 'MSN-005',
                'username' => 'finance',
                'name' => 'Melani Putri',
                'email' => 'finance@msn.co.id',
                'phone' => '08112000005',
                'nik' => '3204010000000005',
                'division' => 'Finance & Akuntansi',
                'position' => 'Head of Finance & Accounting',
                'roles' => ['finance', 'atasan'],
                'supervisor_id' => 1,
            ],
            // 5. Atasan NOC (Divisi NOC)
            [
                'employee_code' => 'MSN-010',
                'username' => 'atasan_noc',
                'name' => 'Bambang Triyono',
                'email' => 'atasan.noc@msn.co.id',
                'phone' => '08112000010',
                'nik' => '3204010000000010',
                'division' => 'NOC',
                'position' => 'Supervisor NOC & Helpdesk',
                'roles' => ['atasan_noc', 'atasan', 'noc'],
                'supervisor_id' => 1,
            ],
            // 6. Atasan Teknik (Divisi Teknik)
            [
                'employee_code' => 'MSN-004',
                'username' => 'mgr_teknis',
                'name' => 'Dedi Kurniawan',
                'email' => 'teknis@msn.co.id',
                'phone' => '08112000004',
                'nik' => '3204010000000004',
                'division' => 'Teknik',
                'position' => 'Manager Teknik & Jaringan',
                'roles' => ['atasan_teknik', 'atasan', 'manager_teknisi'],
                'supervisor_id' => 1,
            ],
            // 7. Atasan Umum (Divisi Umum)
            [
                'employee_code' => 'MSN-020',
                'username' => 'atasan_umum',
                'name' => 'Wahyu Hidayat',
                'email' => 'atasan.umum@msn.co.id',
                'phone' => '08112000020',
                'nik' => '3204010000000020',
                'division' => 'Umum',
                'position' => 'Supervisor Umum & GA',
                'roles' => ['atasan_umum', 'atasan'],
                'supervisor_id' => 1,
            ],
            // 8. Karyawan NOC (Divisi NOC)
            [
                'employee_code' => 'MSN-006',
                'username' => 'karyawan_noc',
                'name' => 'Fajar Nugraha',
                'email' => 'noc@msn.co.id',
                'phone' => '08112000006',
                'nik' => '3204010000000006',
                'division' => 'NOC',
                'position' => 'Staff NOC & Monitoring',
                'roles' => ['karyawan_noc', 'noc', 'helpdesk', 'employee'],
                'supervisor_id' => null, // will link to atasan_noc
            ],
            // 9. Karyawan Teknik (Divisi Teknik)
            [
                'employee_code' => 'MSN-007',
                'username' => 'karyawan_teknik',
                'name' => 'Agus Setiawan',
                'email' => 'teknisi@msn.co.id',
                'phone' => '08112000007',
                'nik' => '3204010000000007',
                'division' => 'Teknik',
                'position' => 'Teknisi Lapangan & Fiber Optic',
                'roles' => ['karyawan_teknik', 'teknisi', 'employee'],
                'supervisor_id' => 4, // Dedi Kurniawan (Atasan Teknik)
            ],
            // 10. Karyawan Umum (Divisi Umum)
            [
                'employee_code' => 'MSN-030',
                'username' => 'karyawan_umum',
                'name' => 'Budi Santoso',
                'email' => 'umum@msn.co.id',
                'phone' => '08112000030',
                'nik' => '3204010000000030',
                'division' => 'Umum',
                'position' => 'Staf Operasional Umum & Logistik',
                'roles' => ['karyawan_umum', 'employee'],
                'supervisor_id' => null, // will link to atasan_umum
            ],
        ];

        $createdUsers = [];
        foreach ($usersData as $data) {
            $roles = $data['roles'];
            unset($data['roles']);

            $user = User::query()->updateOrCreate(
                ['employee_code' => $data['employee_code']],
                array_merge($data, [
                    'join_date' => '2024-01-01',
                    'must_change_password' => false,
                    'password_changed_at' => now(),
                    'is_active' => true,
                    'password' => $defaultPassword,
                ])
            );

            $user->syncRoles($roles);
            $createdUsers[$data['username']] = $user;
        }

        // Link hierarchical supervisors
        if (isset($createdUsers['atasan_noc'], $createdUsers['karyawan_noc'])) {
            $createdUsers['karyawan_noc']->update(['supervisor_id' => $createdUsers['atasan_noc']->id]);
        }
        if (isset($createdUsers['atasan_umum'], $createdUsers['karyawan_umum'])) {
            $createdUsers['karyawan_umum']->update(['supervisor_id' => $createdUsers['atasan_umum']->id]);
        }
    }
}
