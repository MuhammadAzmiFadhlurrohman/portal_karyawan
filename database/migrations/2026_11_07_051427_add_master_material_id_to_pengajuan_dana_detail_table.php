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
        Schema::table('pengajuan_dana_detail', function (Blueprint $table) {
            $table->foreignId('master_material_id')->nullable()->after('pengajuan_id')->constrained('master_materials')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_dana_detail', function (Blueprint $table) {
            $table->dropForeign(['master_material_id']);
            $table->dropColumn('master_material_id');
        });
    }
};
