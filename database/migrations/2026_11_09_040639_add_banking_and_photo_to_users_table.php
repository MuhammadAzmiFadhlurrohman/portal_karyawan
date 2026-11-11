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
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto', 255)->nullable()->after('position');
            $table->string('bank', 100)->nullable()->after('foto');
            $table->string('no_rekening', 50)->nullable()->after('bank');
            $table->string('atas_nama', 100)->nullable()->after('no_rekening');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto', 'bank', 'no_rekening', 'atas_nama']);
        });
    }
};
