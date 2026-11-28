<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_notification_preferences') && ! Schema::hasColumn('user_notification_preferences', 'quick_replies')) {
            Schema::table('user_notification_preferences', function (Blueprint $table) {
                $table->json('quick_replies')->nullable()->after('notify_absen');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_notification_preferences') && Schema::hasColumn('user_notification_preferences', 'quick_replies')) {
            Schema::table('user_notification_preferences', function (Blueprint $table) {
                $table->dropColumn('quick_replies');
            });
        }
    }
};
