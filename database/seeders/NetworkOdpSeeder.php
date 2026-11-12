<?php

namespace Database\Seeders;

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
            $this->command->error("SQL file not found at: {$sqlPath}");
            return;
        }

        $this->command->info('Importing Unified Network Infrastructure (OLT, PON, ODP, Customers, Histories)...');

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

        // For SQLite compatibility, replace NOW() with CURRENT_TIMESTAMP
        if ($driver === 'sqlite') {
            $sql = str_replace('NOW()', 'CURRENT_TIMESTAMP', $sql);
        }

        DB::unprepared($sql);

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('Network data successfully seeded!');
    }
}
