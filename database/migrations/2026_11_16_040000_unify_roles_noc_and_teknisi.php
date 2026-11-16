<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure NOC role exists with full permissions
        $nocRole = Role::findOrCreate('noc', 'web');
        $nocPermissions = [
            'ticket.create',
            'ticket.verify_close',
            'ticket.reopen',
            'ticket.cancel',
            'ticket.stop_clock.approve',
            'ticket.report.view_all',
            'ticket.dispatch',
        ];

        foreach ($nocPermissions as $permName) {
            $perm = Permission::findOrCreate($permName, 'web');
            if (! $nocRole->hasPermissionTo($perm)) {
                $nocRole->givePermissionTo($perm);
            }
        }

        // 2. Ensure Karyawan role exists
        $karyawanRole = Role::findOrCreate('karyawan', 'web');
        $karyawanPerm = Permission::findOrCreate('ticket.closing_awal', 'web');
        if (! $karyawanRole->hasPermissionTo($karyawanPerm)) {
            $karyawanRole->givePermissionTo($karyawanPerm);
        }

        // 3. Migrate users with legacy roles 'helpdesk' or 'sa_cs' -> assign 'noc'
        $legacyNocRoles = Role::whereIn('name', ['helpdesk', 'sa_cs'])->get();
        foreach ($legacyNocRoles as $oldRole) {
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $oldRole->id)
                ->where('model_type', User::class)
                ->pluck('model_id');

            foreach ($userIds as $uid) {
                $user = User::find($uid);
                if ($user) {
                    $user->assignRole('noc');
                    if (empty($user->division) || $user->division === '-') {
                        $user->division = 'NOC';
                        $user->save();
                    }
                }
            }
        }

        // 4. Migrate users with legacy roles 'teknisi' or 'teknis' -> assign 'karyawan' & set division 'Teknik'
        $legacyTechRoles = Role::whereIn('name', ['teknisi', 'teknis'])->get();
        foreach ($legacyTechRoles as $oldRole) {
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $oldRole->id)
                ->where('model_type', User::class)
                ->pluck('model_id');

            foreach ($userIds as $uid) {
                $user = User::find($uid);
                if ($user) {
                    $user->assignRole('karyawan');
                    if (empty($user->division) || $user->division === '-' || $user->division === 'Operasional Jaringan') {
                        $user->division = 'Teknik';
                        $user->save();
                    }
                }
            }
        }

        // 5. Delete unused roles from Spatie database tables
        $unusedRoleNames = ['helpdesk', 'sa_cs', 'teknisi', 'teknis'];
        $rolesToDelete = Role::whereIn('name', $unusedRoleNames)->get();
        foreach ($rolesToDelete as $r) {
            DB::table('model_has_roles')->where('role_id', $r->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $r->id)->delete();
            $r->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
