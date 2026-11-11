<?php

namespace App\Modules\Gomsn\Services;

use App\Models\MasterMaterial;
use App\Models\MaterialMovement;
use App\Models\Pengajuan;
use App\Models\PengajuanApproval;
use App\Models\PengajuanDanaDetail;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PengajuanService
{
    /**
     * Check if user has overdue pending realization > 7 days (§8.1).
     */
    public function hasOverdueRealization(User $user): bool
    {
        return Pengajuan::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['dicairkan', 'menunggu_bukti'])
            ->where('realisasi_deadline', '<', Carbon::now())
            ->exists();
    }

    /**
     * Generate unique pengajuan number.
     * DANA: PND-..., BELI: PBL-..., STOK: PST-...
     */
    public function generateNomor(string $jenis): string
    {
        $prefix = match (strtoupper($jenis)) {
            'BELI' => 'PBL',
            'STOK' => 'PST',
            'PERMINTAAN' => 'PRM',
            'BARANG' => 'PBG',
            default => 'PND',
        };
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(2)));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Create pengajuan with item details and calculate total (§8.1).
     * Supports kinds: DANA, BELI, STOK, PERMINTAAN
     *
     * @param  array{judul: string, keperluan: string, jenis: string}  $data
     * @param  list<array{deskripsi: string, jumlah: int, harga_satuan: float, master_material_id?: int|null}>  $items
     */
    public function createPengajuan(User $user, array $data, array $items = []): Pengajuan
    {
        // Block if user has overdue realization (§8.1)
        if ($this->hasOverdueRealization($user)) {
            throw new Exception('Pengajuan baru diblokir: Anda memiliki nota realisasi pengajuan sebelumnya yang tertunggak lebih dari 7 hari.');
        }

        return DB::transaction(function () use ($user, $data, $items): Pengajuan {
            $rawJenis = strtoupper($data['jenis'] ?? 'DANA');
            $jenis = in_array($rawJenis, ['DANA', 'BELI', 'STOK', 'PERMINTAAN', 'BARANG'], true) ? $rawJenis : 'DANA';
            $nomor = $this->generateNomor($jenis);

            $pengajuan = Pengajuan::query()->create([
                'nomor_pengajuan' => $nomor,
                'jenis' => $jenis,
                'user_id' => $user->id,
                'atasan_id' => ! empty($data['atasan_id']) ? (int) $data['atasan_id'] : null,
                'judul' => $data['judul'] ?? $data['keperluan'] ?? 'Pengajuan Operasional',
                'keperluan' => $data['keperluan'] ?? $data['judul'] ?? 'Keperluan Operasional',
                'status' => 'draft',
                'nominal_total' => 0.00,
            ]);

            $total = 0.0;
            foreach ($items as $item) {
                $qty = max(1, (int) ($item['jumlah'] ?? $item['qty'] ?? 1));
                $price = ($jenis === 'STOK') ? 0.0 : max(0.0, (float) ($item['harga_satuan'] ?? $item['estimated_unit_price'] ?? 0.0));
                $subtotal = $qty * $price;
                $total += $subtotal;
                $materialId = !empty($item['master_material_id']) ? (int) $item['master_material_id'] : null;

                // If material is selected, use its name if deskripsi is empty
                $deskripsi = $item['deskripsi'] ?? $item['item_name'] ?? null;
                if (!$deskripsi && $materialId) {
                    $mat = MasterMaterial::find($materialId);
                    $deskripsi = $mat ? "{$mat->nama} ({$mat->satuan})" : 'Material Stok';
                }

                PengajuanDanaDetail::query()->create([
                    'pengajuan_id' => $pengajuan->id,
                    'master_material_id' => $materialId,
                    'deskripsi' => $deskripsi ?? 'Item Pengajuan',
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'subtotal' => $subtotal,
                ]);
            }

            // Check executive approval threshold from system_settings (§8.1)
            $threshold = (float) SystemSetting::get('pengajuan.executive_threshold', 5000000);
            $requiresExecutive = ($jenis !== 'STOK') && ($total > $threshold);

            $pengajuan->nominal_total = $total;
            $pengajuan->requires_executive_approval = $requiresExecutive;
            $pengajuan->save();

            AuditLogger::record(
                action: 'pengajuan.create',
                auditable: $pengajuan,
                newValues: ['nomor' => $nomor, 'nominal_total' => $total],
                reason: "Pengajuan {$jenis} dibuat sebagai draft",
                actorId: $user->id
            );

            return $pengajuan;
        });
    }

    /**
     * Update an existing pengajuan by the owner (§8.1).
     * Only permitted if NOT yet approved by Atasan and Finance (status in draft / menunggu_atasan).
     */
    public function updatePengajuan(Pengajuan $pengajuan, User $user, array $data, array $items = []): Pengajuan
    {
        if ($pengajuan->user_id !== $user->id) {
            throw new Exception('Hanya pemilik pengajuan yang dapat mengubah data pengajuan.');
        }

        $hasApproved = $pengajuan->approvals()->where('action', 'approved')->exists();
        if ($hasApproved || ! in_array($pengajuan->status, ['draft', 'menunggu_atasan'], true)) {
            throw new Exception('Pengajuan tidak dapat diedit karena sudah disetujui atau sedang dalam proses finance / telah selesai / ditolak.');
        }

        return DB::transaction(function () use ($pengajuan, $user, $data, $items): Pengajuan {
            $rawJenis = strtoupper($data['jenis'] ?? $pengajuan->jenis);
            $jenis = in_array($rawJenis, ['DANA', 'BELI', 'STOK', 'PERMINTAAN', 'BARANG'], true) ? $rawJenis : $pengajuan->jenis;

            $pengajuan->jenis = $jenis;
            if (isset($data['atasan_id'])) {
                $pengajuan->atasan_id = ! empty($data['atasan_id']) ? (int) $data['atasan_id'] : null;
            }
            if (isset($data['judul']) || isset($data['keperluan'])) {
                $keperluan = $data['keperluan'] ?? $data['judul'] ?? $pengajuan->keperluan;
                $pengajuan->judul = $keperluan;
                $pengajuan->keperluan = $keperluan;
            }

            // Replace existing details with updated items
            $pengajuan->details()->delete();

            $total = 0.0;
            foreach ($items as $item) {
                $qty = max(1, (int) ($item['jumlah'] ?? $item['qty'] ?? 1));
                $price = ($jenis === 'STOK') ? 0.0 : max(0.0, (float) ($item['harga_satuan'] ?? $item['estimated_unit_price'] ?? 0.0));
                $subtotal = $qty * $price;
                $total += $subtotal;
                $materialId = ! empty($item['master_material_id']) ? (int) $item['master_material_id'] : null;

                $deskripsi = $item['deskripsi'] ?? $item['item_name'] ?? null;
                if (! $deskripsi && $materialId) {
                    $mat = MasterMaterial::find($materialId);
                    $deskripsi = $mat ? "{$mat->nama} ({$mat->satuan})" : 'Material Stok';
                }

                PengajuanDanaDetail::query()->create([
                    'pengajuan_id' => $pengajuan->id,
                    'master_material_id' => $materialId,
                    'deskripsi' => $deskripsi ?? 'Item Pengajuan',
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                    'subtotal' => $subtotal,
                ]);
            }

            $threshold = (float) SystemSetting::get('pengajuan.executive_threshold', 5000000);
            $requiresExecutive = ($jenis !== 'STOK') && ($total > $threshold);

            $pengajuan->nominal_total = $total;
            $pengajuan->requires_executive_approval = $requiresExecutive;
            $pengajuan->save();

            AuditLogger::record(
                action: 'pengajuan.update',
                auditable: $pengajuan,
                newValues: ['jenis' => $jenis, 'nominal_total' => $total, 'keperluan' => $pengajuan->keperluan],
                reason: "Pengajuan {$pengajuan->nomor_pengajuan} diperbarui oleh pemohon",
                actorId: $user->id
            );

            return $pengajuan;
        });
    }

    /**
     * Submit draft pengajuan to Atasan (§8.1).
     * If $directToFinance is true (e.g. self-submission by an Atasan), automatically bypasses atasan approval.
     */
    public function submitPengajuan(Pengajuan $pengajuan, User $user, bool $directToFinance = false): Pengajuan
    {
        if ($pengajuan->user_id !== $user->id) {
            throw new Exception('Hanya pemilik pengajuan yang dapat mengajukan.');
        }

        if ($pengajuan->status !== 'draft') {
            throw new Exception("Pengajuan berstatus {$pengajuan->status} tidak dapat diajukan.");
        }

        if ($this->hasOverdueRealization($user)) {
            throw new Exception('Pengajuan diblokir: Anda memiliki nota realisasi yang tertunggak.');
        }

        if ($directToFinance) {
            if ($pengajuan->requires_executive_approval) {
                $pengajuan->status = 'menunggu_executive';
                $pengajuan->current_approver_stage = 'executive';
            } else {
                $pengajuan->status = 'menunggu_finance';
                $pengajuan->current_approver_stage = 'finance';
            }
            $pengajuan->save();

            PengajuanApproval::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'stage' => 'atasan',
                'approver_id' => $user->id,
                'action' => 'approved',
                'notes' => 'Pengajuan mandiri atasan (otomatis langsung ke Finance)',
                'created_at' => Carbon::now(),
            ]);

            AuditLogger::record(
                action: 'pengajuan.submit.direct_finance',
                auditable: $pengajuan,
                newValues: ['status' => $pengajuan->status],
                reason: 'Pengajuan mandiri atasan otomatis diteruskan langsung ke Finance',
                actorId: $user->id
            );

            return $pengajuan;
        }

        $pengajuan->status = 'menunggu_atasan';
        $pengajuan->current_approver_stage = 'atasan';
        $pengajuan->save();

        AuditLogger::record(
            action: 'pengajuan.submit',
            auditable: $pengajuan,
            newValues: ['status' => 'menunggu_atasan'],
            reason: 'Pengajuan disubmit ke Atasan',
            actorId: $user->id
        );

        return $pengajuan;
    }

    /**
     * Approve atasan (§8.1, AC-10).
     */
    public function approveAtasan(Pengajuan $pengajuan, User $approver, ?string $notes = null): Pengajuan
    {
        // AC-10: Strict prevention of self-approval
        if ($pengajuan->user_id === $approver->id) {
            throw new Exception('Pemisahan tugas (AC-10): Pengaju tidak boleh menyetujui pengajuannya sendiri.');
        }

        if ($pengajuan->status !== 'menunggu_atasan') {
            throw new Exception("Pengajuan berstatus {$pengajuan->status} tidak sedang menunggu persetujuan atasan.");
        }

        return DB::transaction(function () use ($pengajuan, $approver, $notes): Pengajuan {
            PengajuanApproval::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'stage' => 'atasan',
                'approver_id' => $approver->id,
                'action' => 'approved',
                'notes' => $notes,
            ]);

            // Next stage: if requires executive approval (e.g. > 5jt), goes to executive, else finance
            if ($pengajuan->requires_executive_approval) {
                $pengajuan->status = 'menunggu_executive';
                $pengajuan->current_approver_stage = 'executive';
            } else {
                $pengajuan->status = 'menunggu_finance';
                $pengajuan->current_approver_stage = 'finance';
            }
            $pengajuan->save();

            AuditLogger::record(
                action: 'pengajuan.approve.atasan',
                auditable: $pengajuan,
                newValues: ['status' => $pengajuan->status],
                reason: "Disetujui Atasan {$approver->name}",
                actorId: $approver->id
            );

            return $pengajuan;
        });
    }

    /**
     * Approve executive (HRD / CEO) if amount > threshold (§8.1, AC-10).
     */
    public function approveExecutive(Pengajuan $pengajuan, User $approver, ?string $notes = null): Pengajuan
    {
        if ($pengajuan->user_id === $approver->id) {
            throw new Exception('Pemisahan tugas (AC-10): Pengaju tidak boleh menyetujui pengajuannya sendiri.');
        }

        if ($pengajuan->status !== 'menunggu_executive') {
            throw new Exception("Pengajuan berstatus {$pengajuan->status} tidak sedang menunggu persetujuan eksekutif.");
        }

        return DB::transaction(function () use ($pengajuan, $approver, $notes): Pengajuan {
            PengajuanApproval::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'stage' => 'executive',
                'approver_id' => $approver->id,
                'action' => 'approved',
                'notes' => $notes,
            ]);

            $pengajuan->status = 'menunggu_finance';
            $pengajuan->current_approver_stage = 'finance';
            $pengajuan->save();

            AuditLogger::record(
                action: 'pengajuan.approve.executive',
                auditable: $pengajuan,
                newValues: ['status' => 'menunggu_finance'],
                reason: "Disetujui Eksekutif {$approver->name}",
                actorId: $approver->id
            );

            return $pengajuan;
        });
    }

    /**
     * Release stock items by Finance for STOK pengajuan (§8.1).
     * Finance regulates warehouse inventory and approves stock release.
     */
    public function releaseStockFinance(Pengajuan $pengajuan, User $financeUser, ?string $notes = null): Pengajuan
    {
        if ($pengajuan->user_id === $financeUser->id) {
            throw new Exception('Pemisahan tugas (AC-10): Pengaju tidak boleh mengeluarkan stok untuk dirinya sendiri.');
        }

        if ($pengajuan->status !== 'menunggu_finance') {
            throw new Exception("Pengajuan berstatus {$pengajuan->status} tidak sedang menunggu persetujuan/penyerahan Finance.");
        }

        return DB::transaction(function () use ($pengajuan, $financeUser, $notes): Pengajuan {
            // Deduct stock for linked materials
            foreach ($pengajuan->details as $detail) {
                if ($detail->master_material_id) {
                    $material = MasterMaterial::lockForUpdate()->find($detail->master_material_id);
                    if ($material) {
                        $stockBefore = $material->stok_tersedia;
                        $stockAfter = max(0, $stockBefore - $detail->jumlah);
                        $material->stok_tersedia = $stockAfter;
                        $material->save();

                        MaterialMovement::create([
                            'master_material_id' => $material->id,
                            'user_id' => $pengajuan->user_id,
                            'type' => 'OUT',
                            'quantity' => $detail->jumlah,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'notes' => "Pengeluaran stok pengajuan {$pengajuan->nomor_pengajuan} disetujui Finance",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            $now = Carbon::now();
            $pengajuan->status = 'selesai';
            $pengajuan->current_approver_stage = null;
            $pengajuan->transfer_by = $financeUser->id;
            $pengajuan->transfer_at = $now;
            $pengajuan->realisasi_at = $now;
            $pengajuan->realisasi_notes = $notes ?? 'Barang telah dikeluarkan dari stok gudang dan diserahkan kepada pemohon oleh Finance.';
            $pengajuan->save();

            PengajuanApproval::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'stage' => 'finance',
                'approver_id' => $financeUser->id,
                'action' => 'approved',
                'notes' => $notes ?? 'Barang stok diserahkan dan dikeluarkan oleh Finance',
            ]);

            AuditLogger::record(
                action: 'pengajuan.stock.released',
                auditable: $pengajuan,
                newValues: ['status' => 'selesai'],
                reason: "Pengeluaran stok disetujui & diserahkan oleh Finance {$financeUser->name}",
                actorId: $financeUser->id
            );

            return $pengajuan;
        });
    }

    /**
     * Disburse funds by Finance (§8.1).
     */
    public function disburseFinance(Pengajuan $pengajuan, User $financeUser, string $transferProofPath): Pengajuan
    {
        if ($pengajuan->user_id === $financeUser->id) {
            throw new Exception('Pemisahan tugas (AC-10): Pengaju tidak boleh mencairkan dananya sendiri.');
        }

        if ($pengajuan->status !== 'menunggu_finance') {
            throw new Exception("Pengajuan berstatus {$pengajuan->status} tidak dapat dicairkan.");
        }

        return DB::transaction(function () use ($pengajuan, $financeUser, $transferProofPath): Pengajuan {
            $now = Carbon::now();
            $deadline = $now->copy()->addDays(7); // Default 7 days to upload realization receipt

            $pengajuan->status = 'dicairkan';
            $pengajuan->bukti_transfer_path = $transferProofPath;
            $pengajuan->transfer_at = $now;
            $pengajuan->transfer_by = $financeUser->id;
            $pengajuan->realisasi_deadline = $deadline;
            $pengajuan->save();

            PengajuanApproval::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'stage' => 'finance',
                'approver_id' => $financeUser->id,
                'action' => 'approved',
                'notes' => 'Dana dicairkan via transfer',
            ]);

            AuditLogger::record(
                action: 'pengajuan.disburse',
                auditable: $pengajuan,
                newValues: ['status' => 'dicairkan', 'transfer_at' => $now->toDateTimeString()],
                reason: "Dana dicairkan oleh Finance {$financeUser->name}",
                actorId: $financeUser->id
            );

            return $pengajuan;
        });
    }

    /**
     * Upload realization receipt and finish pengajuan (§8.1).
     */
    public function uploadRealization(
        Pengajuan $pengajuan,
        User $user,
        float $nominalRealisasi,
        string $receiptPath,
        ?string $notes = null
    ): Pengajuan {
        if ($pengajuan->user_id !== $user->id) {
            throw new Exception('Hanya pemohon yang dapat mengunggah nota realisasi.');
        }

        if (! in_array($pengajuan->status, ['dicairkan', 'menunggu_bukti'], true)) {
            throw new Exception("Pengajuan berstatus {$pengajuan->status} belum dalam tahap realisasi.");
        }

        $selisih = (float) $pengajuan->nominal_total - $nominalRealisasi;

        $pengajuan->status = 'selesai';
        $pengajuan->nominal_realisasi = $nominalRealisasi;
        $pengajuan->selisih_realisasi = $selisih;
        $pengajuan->bukti_realisasi_path = $receiptPath;
        $pengajuan->realisasi_at = Carbon::now();
        $pengajuan->realisasi_notes = $notes;
        $pengajuan->save();

        AuditLogger::record(
            action: 'pengajuan.realization',
            auditable: $pengajuan,
            newValues: [
                'nominal_realisasi' => $nominalRealisasi,
                'selisih' => $selisih,
                'status' => 'selesai',
            ],
            reason: "Realisasi nota pembelian diserahkan oleh {$user->name}",
            actorId: $user->id
        );

        return $pengajuan;
    }

    /**
     * Reject pengajuan (§8.1).
     */
    public function rejectPengajuan(Pengajuan $pengajuan, User $approver, string $stage, string $reason): Pengajuan
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan penolakan pengajuan wajib diisi.');
        }

        return DB::transaction(function () use ($pengajuan, $approver, $stage, $reason): Pengajuan {
            $pengajuan->status = 'ditolak';
            $pengajuan->save();

            PengajuanApproval::query()->create([
                'pengajuan_id' => $pengajuan->id,
                'stage' => $stage,
                'approver_id' => $approver->id,
                'action' => 'rejected',
                'notes' => $reason,
            ]);

            AuditLogger::record(
                action: 'pengajuan.reject',
                auditable: $pengajuan,
                newValues: ['status' => 'ditolak', 'reason' => $reason],
                reason: "Pengajuan ditolak pada tahap {$stage} oleh {$approver->name}",
                actorId: $approver->id
            );

            return $pengajuan;
        });
    }
}
