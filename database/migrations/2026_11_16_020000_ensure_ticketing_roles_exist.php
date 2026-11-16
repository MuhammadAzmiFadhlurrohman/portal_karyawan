<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = [
            'teknisi',
            'teknis',
            'manager_teknisi',
            'helpdesk',
            'sa_cs',
            'karyawan_teknik',
            'noc',
        ];

        foreach ($roles as $roleName) {
            try {
                Role::findOrCreate($roleName, 'web');
            } catch (\Throwable $e) {
                // Ignore if permission table is not yet set up
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep roles to prevent cascading data loss
    }
};
