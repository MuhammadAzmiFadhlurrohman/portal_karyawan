<?php

use App\Models\Shift;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_schedules', function (Blueprint $table) {
            $table->time('custom_start_time')->nullable()->after('shift_id');
            $table->time('custom_end_time')->nullable()->after('custom_start_time');
        });

        // Ensure CUST shift exists in master shifts table
        Shift::query()->firstOrCreate(
            ['code' => 'CUST'],
            [
                'name' => 'Custom Jam Kerja',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'color' => '#0D9488',
                'late_tolerance_minutes' => 15,
                'break_minutes' => 60,
                'is_day_off' => false,
                'is_piket' => false,
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Schema::table('monthly_schedules', function (Blueprint $table) {
            $table->dropColumn(['custom_start_time', 'custom_end_time']);
        });
    }
};

