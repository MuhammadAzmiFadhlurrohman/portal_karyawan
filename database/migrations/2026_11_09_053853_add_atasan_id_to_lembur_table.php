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
        Schema::table('lembur', function (Blueprint $table) {
            $table->foreignId('atasan_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['atasan_id', 'status'], 'idx_lembur_atasan_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lembur', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropIndex('idx_lembur_atasan_status');
            $table->dropColumn('atasan_id');
        });
    }
};

