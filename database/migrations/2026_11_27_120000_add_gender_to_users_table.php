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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->default('pria')->after('position');
            }
        });

        // Set default gender for known female users based on female names with word boundary
        $femaleKeywords = ['siti', 'nurhaliza', 'melani', 'putri', 'amelia', 'ida', 'karmeliandita', 'dewi'];
        
        $users = DB::table('users')->select('id', 'name', 'email')->get();
        foreach ($users as $u) {
            $lower = strtolower($u->name . ' ' . $u->email);
            $isFemale = false;
            foreach ($femaleKeywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                    $isFemale = true;
                    break;
                }
            }
            DB::table('users')
                ->where('id', $u->id)
                ->update(['gender' => $isFemale ? 'perempuan' : 'pria']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
