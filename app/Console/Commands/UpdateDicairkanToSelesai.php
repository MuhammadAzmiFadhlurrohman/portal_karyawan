<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateDicairkanToSelesai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gomsn:sync-selesai-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pengajuan dengan status dicairkan atau menunggu_bukti menjadi selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = DB::table('pengajuan')
            ->whereIn('status', ['dicairkan', 'menunggu_bukti'])
            ->update([
                'status' => 'selesai',
                'current_approver_stage' => null,
                'nominal_realisasi' => DB::raw('COALESCE(nominal_realisasi, nominal_total)'),
                'selisih_realisasi' => 0,
            ]);

        $this->info("Berhasil mengupdate {$updated} data pengajuan menjadi 'selesai'.");

        return 0;
    }
}
