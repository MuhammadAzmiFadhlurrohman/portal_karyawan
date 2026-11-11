<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CoreManeuver;
use App\Models\JointClosure;
use App\Models\RepairPoint;
use App\Models\Tiket;
use App\Modules\Ticketing\Services\TechnicalAssetService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TechnicalAssetController extends Controller
{
    public function __construct(
        protected TechnicalAssetService $assetService,
    ) {}

    /**
     * Consume material for a ticket (§7.6, AC-05).
     */
    public function useMaterial(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'material_id' => ['required', 'exists:master_materials,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->assetService->useMaterial(
                tiket: $tiket,
                materialId: (int) $validated['material_id'],
                quantity: (int) $validated['quantity'],
                user: $request->user(),
            );

            $msg = "Material {$result['material']->nama} sebanyak {$validated['quantity']} {$result['material']->satuan} berhasil dicatat.";
            if ($result['is_min_stock_warning']) {
                $msg .= " (Peringatan: Stok tersisa {$result['material']->stok_tersedia} telah mencapai/di bawah batas minimum)";
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'result' => $result]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['material' => $e->getMessage()]);
        }
    }

    /**
     * Add Repair Point (§7.6).
     */
    public function storeRepairPoint(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'nama_titik' => ['required', 'string', 'max:150'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'jenis_kerusakan' => ['nullable', 'string', 'max:100'],
        ]);

        $point = RepairPoint::query()->create([
            'tiket_id' => $tiket->id,
            'nama_titik' => $validated['nama_titik'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'jenis_kerusakan' => $validated['jenis_kerusakan'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Titik perbaikan berhasil dicatat.', 'repair_point' => $point]);
        }

        return redirect()->route('tickets.show', $tiket)->with('success', 'Titik perbaikan berhasil dicatat.');
    }

    /**
     * Add Joint Closure (§7.6).
     */
    public function storeJointClosure(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'repair_point_id' => ['nullable', 'exists:repair_points,id'],
            'nama_closure' => ['required', 'string', 'max:150'],
            'kapasitas_core' => ['required', 'integer', 'min:1'],
            'tipe_closure' => ['nullable', 'string', 'max:50'],
        ]);

        $closure = JointClosure::query()->create([
            'tiket_id' => $tiket->id,
            'repair_point_id' => $validated['repair_point_id'] ?? null,
            'nama_closure' => $validated['nama_closure'],
            'kapasitas_core' => (int) $validated['kapasitas_core'],
            'tipe_closure' => $validated['tipe_closure'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Joint Closure berhasil dicatat.', 'joint_closure' => $closure]);
        }

        return redirect()->route('tickets.show', $tiket)->with('success', 'Joint Closure berhasil dicatat.');
    }

    /**
     * Add Closure Core Sambungan (§7.6).
     */
    public function storeClosureCore(Request $request, JointClosure $jointClosure): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $jointClosure->ticket);

        $validated = $request->validate([
            'tube_asal' => ['required', 'string', 'max:20'],
            'core_asal' => ['required', 'integer', 'min:1'],
            'tube_tujuan' => ['required', 'string', 'max:20'],
            'core_tujuan' => ['required', 'integer', 'min:1'],
            'loss_db' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $this->assetService->addClosureCore(
                closure: $jointClosure,
                tubeAsal: $validated['tube_asal'],
                coreAsal: (int) $validated['core_asal'],
                tubeTujuan: $validated['tube_tujuan'],
                coreTujuan: (int) $validated['core_tujuan'],
                lossDb: isset($validated['loss_db']) ? (float) $validated['loss_db'] : 0.0,
                notes: $validated['notes'] ?? null,
            );

            $msg = 'Sambungan core berhasil dicatat.';
            if ($result['is_loss_warning']) {
                $msg .= " (Peringatan: Redaman loss {$validated['loss_db']} dB melebihi batas standar 0.3 dB per sambungan)";
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'result' => $result]);
            }

            return redirect()->route('tickets.show', $jointClosure->ticket)->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $jointClosure->ticket)->withErrors(['core' => $e->getMessage()]);
        }
    }

    /**
     * Add Core Maneuver (§7.6).
     */
    public function storeCoreManeuver(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'segmen' => ['required', 'string', 'max:150'],
            'core_asal' => ['required', 'integer', 'min:1'],
            'core_tujuan' => ['required', 'integer', 'min:1'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $maneuver = CoreManeuver::query()->create([
            'tiket_id' => $tiket->id,
            'segmen' => $validated['segmen'],
            'core_asal' => (int) $validated['core_asal'],
            'core_tujuan' => (int) $validated['core_tujuan'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Manuver core berhasil dicatat.', 'core_maneuver' => $maneuver]);
        }

        return redirect()->route('tickets.show', $tiket)->with('success', 'Manuver core berhasil dicatat.');
    }
}
