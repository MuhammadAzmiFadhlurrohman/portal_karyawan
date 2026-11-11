<?php

namespace App\Modules\Ticketing\Services;

use App\Models\ClosureCore;
use App\Models\JointClosure;
use App\Models\MasterMaterial;
use App\Models\MaterialMovement;
use App\Models\TicketMaterial;
use App\Models\Tiket;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TechnicalAssetService
{
    /**
     * Atomically consume material for a ticket with inventory update (§7.6).
     *
     * @return array{material: MasterMaterial, movement: MaterialMovement, is_min_stock_warning: bool}
     */
    public function useMaterial(Tiket $tiket, int $materialId, int $quantity, User $user): array
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah material harus lebih dari 0.');
        }

        return DB::transaction(function () use ($tiket, $materialId, $quantity, $user): array {
            /** @var MasterMaterial $mat */
            $mat = MasterMaterial::query()->where('id', $materialId)->lockForUpdate()->firstOrFail();

            if ($mat->stok_tersedia < $quantity) {
                throw new Exception("Stok {$mat->nama} tidak mencukupi (Tersedia: {$mat->stok_tersedia}, Diminta: {$quantity}).");
            }

            $before = $mat->stok_tersedia;
            $after = $before - $quantity;

            $mat->stok_tersedia = $after;
            $mat->save();

            $movement = MaterialMovement::query()->create([
                'master_material_id' => $mat->id,
                'tiket_id' => $tiket->id,
                'user_id' => $user->id,
                'type' => 'OUT',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => "Pemakaian pada tiket {$tiket->no_tiket}",
            ]);

            TicketMaterial::query()->create([
                'tiket_id' => $tiket->id,
                'master_material_id' => $mat->id,
                'quantity' => $quantity,
                'used_by' => $user->id,
            ]);

            $isWarning = $after <= $mat->min_stok;

            return [
                'material' => $mat,
                'movement' => $movement,
                'is_min_stock_warning' => $isWarning,
            ];
        });
    }

    /**
     * Return unused material back to stock (§7.6).
     */
    public function returnMaterial(Tiket $tiket, int $materialId, int $quantity, User $user, ?string $reason = null): MaterialMovement
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah material kembali harus lebih dari 0.');
        }

        return DB::transaction(function () use ($tiket, $materialId, $quantity, $user, $reason): MaterialMovement {
            /** @var MasterMaterial $mat */
            $mat = MasterMaterial::query()->where('id', $materialId)->lockForUpdate()->firstOrFail();

            $before = $mat->stok_tersedia;
            $after = $before + $quantity;

            $mat->stok_tersedia = $after;
            $mat->save();

            return MaterialMovement::query()->create([
                'master_material_id' => $mat->id,
                'tiket_id' => $tiket->id,
                'user_id' => $user->id,
                'type' => 'IN_RETURN',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $reason ?? "Pengembalian material dari tiket {$tiket->no_tiket}",
            ]);
        });
    }

    /**
     * Add closure core mapping with duplicate prevention & loss warning (§7.6).
     *
     * @return array{closure_core: ClosureCore, is_loss_warning: bool}
     */
    public function addClosureCore(
        JointClosure $closure,
        string $tubeAsal,
        int $coreAsal,
        string $tubeTujuan,
        int $coreTujuan,
        float $lossDb = 0.0,
        ?string $notes = null
    ): array {
        $exists = ClosureCore::query()
            ->where('joint_closure_id', $closure->id)
            ->where('tube_asal', $tubeAsal)
            ->where('core_asal', $coreAsal)
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException("Pasangan Tube {$tubeAsal} Core {$coreAsal} sudah terdaftar pada Joint Closure ini.");
        }

        $core = ClosureCore::query()->create([
            'joint_closure_id' => $closure->id,
            'tube_asal' => $tubeAsal,
            'core_asal' => $coreAsal,
            'tube_tujuan' => $tubeTujuan,
            'core_tujuan' => $coreTujuan,
            'status_core' => 'TERHUBUNG',
            'loss_db' => $lossDb,
            'notes' => $notes,
        ]);

        $isLossWarning = $lossDb > 0.30;

        return [
            'closure_core' => $core,
            'is_loss_warning' => $isLossWarning,
        ];
    }

    /**
     * Check closing prerequisites (AC-06, §7.6).
     * Returns structured missing prerequisites list.
     *
     * @return array{passed: bool, missing: list<string>}
     */
    public function checkClosingPrerequisites(Tiket $tiket): array
    {
        $missing = [];

        // Common prerequisites
        if (empty(trim((string) $tiket->problem_temuan))) {
            $missing[] = 'Resume permasalahan / Problem Temuan wajib diisi.';
        }
        if (empty(trim((string) $tiket->action_taken))) {
            $missing[] = 'Tindakan penanganan / Action Taken wajib diisi.';
        }

        if ($tiket->kategori_tiket === 'BROADBAND') {
            // Broadband requirements (§7.6)
            // 1. Minimum 1 photo result (category SELESAI or photo uploaded)
            $hasResultPhoto = $tiket->chronologies()
                ->where(function ($q) {
                    $q->where('category', 'SELESAI')
                        ->orWhereNotNull('photo_path');
                })
                ->exists();

            if (! $hasResultPhoto) {
                $missing[] = 'Dokumentasi foto hasil perbaikan wajib diunggah pada kronologis.';
            }

            // 2. Redaman sesudah must be set and in valid dBm range (-40 to 0)
            if ($tiket->redaman_sesudah === null) {
                $missing[] = 'Nilai redaman sesudah (dBm) wajib diisi.';
            } else {
                $val = (float) $tiket->redaman_sesudah;
                if ($val < -40.0 || $val > 0.0) {
                    $missing[] = 'Nilai redaman sesudah tidak dalam rentang standar optical (-40.00 dBm s.d. 0.00 dBm).';
                }
            }
        } elseif ($tiket->kategori_tiket === 'BACKBONE') {
            // Backbone requirements (§7.6, AC-06)
            // 1. Minimum 1 photo OTDR or SELESAI
            $hasOtdrOrResult = $tiket->chronologies()
                ->where(function ($q) {
                    $q->whereIn('category', ['OTDR', 'SELESAI'])
                        ->orWhereNotNull('photo_path');
                })
                ->exists();

            if (! $hasOtdrOrResult) {
                $missing[] = 'Dokumentasi foto OTDR atau hasil penanganan wajib diunggah pada kronologis.';
            }

            // 2. Minimum 1 Titik Perbaikan or Joint Closure
            $hasRepairPoint = $tiket->repairPoints()->exists();
            $hasJointClosure = $tiket->jointClosures()->exists();
            if (! $hasRepairPoint && ! $hasJointClosure) {
                $missing[] = 'Wajib mencatat minimal 1 Titik Perbaikan atau 1 Joint Closure.';
            }

            // 3. Minimum 1 Joint Closure or Manuver Core (AC-06)
            $hasManeuver = $tiket->coreManeuvers()->exists();
            if (! $hasJointClosure && ! $hasManeuver) {
                $missing[] = 'Wajib mencatat minimal 1 Joint Closure atau 1 Manuver Core (AC-06).';
            }

            // 4. Link restoration confirmed
            if (! $tiket->has_link_up) {
                $missing[] = 'Konfirmasi pemulihan link (LINK UP) wajib dicentang.';
            }
        }

        return [
            'passed' => empty($missing),
            'missing' => $missing,
        ];
    }
}
