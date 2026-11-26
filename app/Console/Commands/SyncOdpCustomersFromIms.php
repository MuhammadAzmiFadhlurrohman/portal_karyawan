<?php

namespace App\Console\Commands;

use App\Models\OdpCustomer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncOdpCustomersFromIms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gomsn:sync-ims-odp {--force : Timpa data meskipun kolom lokal sudah terisi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data pelanggan ODP dari database IMS (view_batchjob)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi data pelanggan ODP dari database IMS (view_batchjob)...');

        $force = $this->option('force');

        try {
            $allCusts = OdpCustomer::all();
            $total = $allCusts->count();
            $this->info("Total pelanggan di odp_customers: {$total}");

            $cids = $allCusts->pluck('nomor_internet')->filter()->unique()->toArray();
            $chunks = array_chunk($cids, 100);
            $updatedCount = 0;

            $bar = $this->output->createProgressBar(count($cids));
            $bar->start();

            foreach ($chunks as $chunk) {
                $imsRows = DB::connection('ims')
                    ->table('view_batchjob')
                    ->whereIn('nomor_internet', $chunk)
                    ->get([
                        'nomor_internet',
                        'alamat_pasang',
                        'nomor_hp',
                        'nama_kategori_bandwith',
                        'desc_registrasi',
                        'aktivasi_note_finish',
                    ])
                    ->keyBy('nomor_internet');

                foreach ($allCusts->whereIn('nomor_internet', $chunk) as $cust) {
                    $bar->advance();

                    if (isset($imsRows[$cust->nomor_internet])) {
                        $ims = $imsRows[$cust->nomor_internet];
                        $updates = [];

                        if (($force || empty($cust->alamat)) && !empty($ims->alamat_pasang)) {
                            $updates['alamat'] = $ims->alamat_pasang;
                        }
                        if (($force || empty($cust->nomor)) && !empty($ims->nomor_hp)) {
                            $updates['nomor'] = $ims->nomor_hp;
                        }
                        if (($force || empty($cust->layanan) || $cust->layanan === 'BROADBAND FREE') && !empty($ims->nama_kategori_bandwith)) {
                            $updates['layanan'] = $ims->nama_kategori_bandwith;
                        }
                        if (!empty($ims->desc_registrasi)) {
                            $updates['status'] = $ims->desc_registrasi;
                        }
                        if (($force || empty($cust->note)) && !empty($ims->aktivasi_note_finish)) {
                            $updates['note'] = $ims->aktivasi_note_finish;
                        }

                        if (!empty($updates)) {
                            $cust->updateQuietly($updates);
                            $updatedCount++;
                        }
                    }
                }
            }

            $bar->finish();
            $this->newLine();
            $this->info("Sukses! Sebanyak {$updatedCount} baris data pelanggan berhasil diperbarui dari IMS.");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Gagal melakukan sinkronisasi: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
