<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_chronologies', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_chronologies', 'reply_to_id')) {
                $table->foreignId('reply_to_id')->nullable()->after('tiket_id')->constrained('ticket_chronologies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_chronologies', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_chronologies', 'reply_to_id')) {
                $table->dropForeign(['reply_to_id']);
                $table->dropColumn('reply_to_id');
            }
        });
    }
};

