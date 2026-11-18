<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tiket', function (Blueprint $table): void {
            if (! Schema::hasColumn('tiket', 'team_members')) {
                $table->json('team_members')->nullable()->after('action_taken');
            }
            if (! Schema::hasColumn('tiket', 'catatan_tambahan')) {
                $table->text('catatan_tambahan')->nullable()->after('team_members');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiket', function (Blueprint $table): void {
            if (Schema::hasColumn('tiket', 'catatan_tambahan')) {
                $table->dropColumn('catatan_tambahan');
            }
            if (Schema::hasColumn('tiket', 'team_members')) {
                $table->dropColumn('team_members');
            }
        });
    }
};
