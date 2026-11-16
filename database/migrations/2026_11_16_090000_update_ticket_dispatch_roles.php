<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Penugasan teknisi (ticket.dispatch) diberikan khusus oleh role:
     * - atasan_teknik
     * - atasan_umum
     * - atasan
     * - manager_teknisi (atasan teknisi)
     * - super_admin & admin
     * Dan dicabut dari role non-atasan (seperti noc).
     */
    public function up(): void
    {
        $dispatchPerm = Permission::findOrCreate('ticket.dispatch', 'web');

        // Roles yang berhak melakukan penugasan teknisi
        $atasanRoles = [
            'atasan_teknik',
            'atasan_umum',
            'atasan',
            'manager_teknisi',
            'admin',
            'super_admin',
        ];

        foreach ($atasanRoles as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            if (! $role->hasPermissionTo($dispatchPerm)) {
                $role->givePermissionTo($dispatchPerm);
            }
        }

        // Cabut ticket.dispatch dari role non-atasan (NOC)
        $nonAtasanRoles = ['noc', 'atasan_noc'];
        foreach ($nonAtasanRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && $role->hasPermissionTo($dispatchPerm)) {
                $role->revokePermissionTo($dispatchPerm);
            }
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

