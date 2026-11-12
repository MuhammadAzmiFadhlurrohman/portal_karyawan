<?php

namespace Database\Seeders;

use App\Models\Olt;
use App\Models\Pon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class NetworkOdpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlPath = base_path('database_network_odp_import.sql');

        if (! File::exists($sqlPath)) {
            $this->command?->error("SQL file not found at: {$sqlPath}");
            return;
        }

        $this->command?->info('Importing Unified Network Infrastructure (OLT, PON, ODP, Customers, Histories)...');

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        Schema::disableForeignKeyConstraints();

        DB::table('odp_user_histories')->truncate();
        DB::table('odp_customers')->truncate();
        DB::table('odps')->truncate();
        DB::table('pons')->truncate();
        DB::table('olts')->truncate();

        $sql = File::get($sqlPath);

        if ($driver === 'sqlite') {
            $sql = str_replace('NOW()', 'CURRENT_TIMESTAMP', $sql);
        }

        // Split into separate queries to guarantee execution on all MySQL / SQLite drivers
        $lines = explode("\n", $sql);
        $currentQuery = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $currentQuery .= $line . "\n";

            if (str_ends_with($trimmed, ';')) {
                try {
                    DB::unprepared($currentQuery);
                } catch (\Throwable $e) {
                    // Log or continue
                }
                $currentQuery = '';
            }
        }

        if (! empty(trim($currentQuery))) {
            try {
                DB::unprepared($currentQuery);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        Schema::enableForeignKeyConstraints();

        $this->command?->info('Network data successfully seeded! Total PON: ' . Pon::count());
    }
}
