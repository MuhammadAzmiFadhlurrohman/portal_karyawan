<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'office')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('office', 50)->nullable()->after('division');
            });
        }

        if (! Schema::hasColumn('users', 'office_location_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('office_location_id')->nullable()->after('office');
            });
        }

        // Mapping office data from attendance_db:
        // Soreang Technicians: KR46054, KR50880, KR50881, KR55039, KR90666
        $soreangCodes = ['KR46054', 'KR50880', 'KR50881', 'KR55039', 'KR90666'];

        DB::table('users')
            ->where('division', 'Teknik')
            ->whereIn('employee_code', $soreangCodes)
            ->update([
                'office' => 'soreang',
                'office_location_id' => 2,
            ]);

        DB::table('users')
            ->where('division', 'Teknik')
            ->whereNotIn('employee_code', $soreangCodes)
            ->update([
                'office' => 'bandung',
                'office_location_id' => 1,
            ]);

        DB::table('users')
            ->where('division', 'NOC')
            ->update([
                'office' => 'bandung',
                'office_location_id' => 1,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'office_location_id')) {
                $table->dropColumn('office_location_id');
            }
            if (Schema::hasColumn('users', 'office')) {
                $table->dropColumn('office');
            }
        });
    }
};
