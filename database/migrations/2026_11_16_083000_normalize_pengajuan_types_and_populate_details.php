<?php

use App\Models\MasterMaterial;
use App\Models\Pengajuan;
use App\Models\PengajuanDanaDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normalize 'jenis' in pengajuan table
        $legacyJsonPath = database_path('data/legacy_non_dana_types.json');
        $legacyMap = [];
        if (file_exists($legacyJsonPath)) {
            $decoded = json_decode(file_get_contents($legacyJsonPath), true);
            if (is_array($decoded)) {
                $legacyMap = $decoded;
            }
        }

        // Apply exact mapping for legacy IDs
        foreach ($legacyMap as $id => $legacyType) {
            $validType = strtoupper($legacyType);
            if (in_array($validType, ['STOK', 'PERMINTAAN', 'BELI', 'DANA'], true)) {
                DB::table('pengajuan')
                    ->where('id', (int) $id)
                    ->update(['jenis' => $validType]);
            }
        }

        // Normalize any remaining 'BARANG' or 'barang'
        $remainingBarang = DB::table('pengajuan')
            ->whereIn(DB::raw('UPPER(jenis)'), ['BARANG'])
            ->get(['id', 'judul', 'keperluan']);

        foreach ($remainingBarang as $row) {
            $text = strtolower(($row->judul ?? '') . ' ' . ($row->keperluan ?? ''));
            $newJenis = 'STOK';
            if (preg_match('/beli|pembelian|pengadaan/', $text)) {
                $newJenis = 'BELI';
            } elseif (preg_match('/permintaan/', $text)) {
                $newJenis = 'PERMINTAAN';
            }

            DB::table('pengajuan')
                ->where('id', $row->id)
                ->update(['jenis' => $newJenis]);
        }

        // Standardize any lowercase kinds to uppercase
        DB::table('pengajuan')->where('jenis', 'dana')->update(['jenis' => 'DANA']);
        DB::table('pengajuan')->where('jenis', 'beli')->update(['jenis' => 'BELI']);
        DB::table('pengajuan')->where('jenis', 'stok')->update(['jenis' => 'STOK']);
        DB::table('pengajuan')->where('jenis', 'permintaan')->update(['jenis' => 'PERMINTAAN']);

        // 2. Populate pengajuan_dana_detail for records that have 0 details
        $materials = MasterMaterial::all();

        $emptyPengajuanList = Pengajuan::doesntHave('details')->get();

        foreach ($emptyPengajuanList as $pengajuan) {
            $parsedItems = Pengajuan::parseLegacyItems(
                $pengajuan->judul ?? '',
                $pengajuan->keperluan ?? '',
                $pengajuan->jenis ?? 'STOK',
                (float) ($pengajuan->nominal_total ?? 0.0)
            );

            if (empty($parsedItems)) {
                // If completely empty, insert a single placeholder matching judul/keperluan
                $desc = trim($pengajuan->judul ?: ($pengajuan->keperluan ?: 'Pengajuan Operasional'));
                $nominal = (float) ($pengajuan->nominal_total ?? 0.0);
                PengajuanDanaDetail::create([
                    'pengajuan_id' => $pengajuan->id,
                    'master_material_id' => null,
                    'deskripsi' => $desc,
                    'jumlah' => 1,
                    'harga_satuan' => $nominal,
                    'subtotal' => $nominal,
                    'created_at' => $pengajuan->created_at ?? now(),
                    'updated_at' => $pengajuan->updated_at ?? now(),
                ]);
                continue;
            }

            foreach ($parsedItems as $item) {
                $matchedMaterial = null;
                $cleanName = strtolower(trim($item['name']));

                foreach ($materials as $mat) {
                    if (strtolower(trim($mat->nama)) === $cleanName) {
                        $matchedMaterial = $mat;
                        break;
                    }
                }

                $isStok = in_array(strtoupper($pengajuan->jenis), ['STOK', 'PERMINTAAN'], true);
                $qty = (int) ($item['qty'] ?? 1);
                $price = $isStok ? 0.0 : (count($parsedItems) === 1 ? (float)$pengajuan->nominal_total : 0.0);
                $subtotal = $qty * $price;

                PengajuanDanaDetail::create([
                    'pengajuan_id' => $pengajuan->id,
                    'master_material_id' => $matchedMaterial?->id,
                    'deskripsi' => $item['name'],
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'subtotal' => $subtotal,
                    'created_at' => $pengajuan->created_at ?? now(),
                    'updated_at' => $pengajuan->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to avoid destroying data
    }
};
