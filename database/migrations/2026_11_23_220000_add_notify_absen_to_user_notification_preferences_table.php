<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_notification_preferences') && ! Schema::hasColumn('user_notification_preferences', 'notify_absen')) {
            Schema::table('user_notification_preferences', function (Blueprint $table) {
                $table->boolean('notify_absen')->default(true)->after('notify_tiket');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_notification_preferences') && Schema::hasColumn('user_notification_preferences', 'notify_absen')) {
            Schema::table('user_notification_preferences', function (Blueprint $table) {
                $table->dropColumn('notify_absen');
            });
        }
    }
};
