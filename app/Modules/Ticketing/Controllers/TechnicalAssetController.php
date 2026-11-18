<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClosureCore;
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

        $namaTitik = $request->input('nama_titik') ?: $request->input('location_name');
        $request->merge(['nama_titik' => $namaTitik]);

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
     * Delete Repair Point.
     */
    public function destroyRepairPoint(Request $request, RepairPoint $repairPoint): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $repairPoint->ticket);
        $tiket = $repairPoint->ticket;
        $repairPoint->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Titik perbaikan berhasil dihapus.']);
        }

        return redirect()->route('tickets.show', $tiket)->with('success', 'Titik perbaikan berhasil dihapus.');
    }

    /**
     * Add Joint Closure (§7.6).
     */
    public function storeJointClosure(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $tiket);

        $namaClosure = $request->input('nama_closure') ?: $request->input('closure_code');
        $kapasitasCore = $request->input('kapasitas_core') ?: $request->input('capacity_cores') ?: 24;
        $tipeClosure = $request->input('tipe_closure') ?: $request->input('closure_type') ?: 'DOME';

        $request->merge([
            'nama_closure' => $namaClosure,
            'kapasitas_core' => $kapasitasCore,
            'tipe_closure' => $tipeClosure,
        ]);

        $validated = $request->validate([
            'repair_point_id' => ['nullable', 'exists:repair_points,id'],
            'nama_closure' => ['required', 'string', 'max:150'],
            'kapasitas_core' => ['required', 'integer', 'min:1'],
            'tipe_closure' => ['nullable', 'string', 'max:50'],
            'penempatan_fisik' => ['nullable', 'string', 'max:50'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'cores' => ['nullable'],
        ]);

        $repairPointId = $validated['repair_point_id'] ?? null;
        if (! $repairPointId && $request->filled('latitude') && $request->filled('longitude')) {
            $point = RepairPoint::query()->create([
                'tiket_id' => $tiket->id,
                'nama_titik' => 'Titik ' . $validated['nama_closure'],
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'jenis_kerusakan' => 'Joint Closure (' . ($validated['penempatan_fisik'] ?? 'POLE') . ')',
            ]);
            $repairPointId = $point->id;
        }

        $closure = JointClosure::query()->create([
            'tiket_id' => $tiket->id,
            'repair_point_id' => $repairPointId,
            'nama_closure' => $validated['nama_closure'],
            'kapasitas_core' => (int) $validated['kapasitas_core'],
            'tipe_closure' => $validated['tipe_closure'] ?? 'DOME',
        ]);

        // Simpan matrix cores jika dikirim dari visual patcher
        $coresInput = $request->input('cores');
        if (is_string($coresInput)) {
            $coresInput = json_decode($coresInput, true);
        }
        if (! empty($coresInput) && is_array($coresInput)) {
            foreach ($coresInput as $c) {
                if (! empty($c['tube_asal']) && ! empty($c['core_asal'])) {
                    ClosureCore::query()->updateOrCreate([
                        'joint_closure_id' => $closure->id,
                        'tube_asal' => (string) $c['tube_asal'],
                        'core_asal' => (int) $c['core_asal'],
                    ], [
                        'tube_tujuan' => (string) ($c['tube_tujuan'] ?? $c['tube_jumper'] ?? 'Tube 1'),
                        'core_tujuan' => (int) ($c['core_tujuan'] ?? $c['core_jumper'] ?? 1),
                        'status_core' => $c['status'] ?? $c['status_core'] ?? 'TERHUBUNG',
                        'loss_db' => isset($c['loss_db']) ? (float) $c['loss_db'] : (isset($c['loss']) ? (float) $c['loss'] : 0.02),
                        'notes' => $c['notes'] ?? null,
                    ]);
                }
            }
        }

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

        $segmen = $request->input('segmen') ?: $request->input('titik_lokasi') ?: ('Titik Manuver Tiket #' . $tiket->nomor_tiket);
        $coreAsal = $request->input('core_asal');
        $coreTujuan = $request->input('core_tujuan');
        $connectionsInput = $request->input('connections');

        if (is_string($connectionsInput)) {
            $connectionsInput = json_decode($connectionsInput, true);
        }

        if (empty($coreAsal) && ! empty($connectionsInput) && is_array($connectionsInput)) {
            $first = reset($connectionsInput);
            $coreAsal = $first['core_asal'] ?? 1;
            $coreTujuan = $first['core_tujuan'] ?? $coreAsal;
        }

        $request->merge([
            'segmen' => $segmen,
            'core_asal' => $coreAsal ?: 1,
            'core_tujuan' => $coreTujuan ?: 1,
        ]);

        $validated = $request->validate([
            'segmen' => ['required', 'string', 'max:150'],
            'core_asal' => ['required', 'integer', 'min:1'],
            'core_tujuan' => ['required', 'integer', 'min:1'],
            'tipe_lokasi' => ['nullable', 'string', 'max:50'],
            'status_alokasi' => ['nullable', 'string', 'max:50'],
            'sifat_manuver' => ['nullable', 'string', 'max:50'],
            'titik_normalisasi' => ['nullable', 'string', 'max:150'],
            'status_core_aset' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
        ]);

        // Metadata pelengkap
        $metaParts = [];
        if ($request->filled('tipe_lokasi')) {
            $metaParts[] = 'Tipe: ' . $request->input('tipe_lokasi');
        }
        if ($request->filled('status_alokasi')) {
            $metaParts[] = 'Alokasi: ' . $request->input('status_alokasi');
        }
        if ($request->filled('sifat_manuver')) {
            $metaParts[] = 'Sifat: ' . $request->input('sifat_manuver');
        }
        if ($request->filled('titik_normalisasi')) {
            $metaParts[] = 'Normalisasi: ' . $request->input('titik_normalisasi');
        }
        if ($request->filled('status_core_aset')) {
            $metaParts[] = 'Status Core: ' . $request->input('status_core_aset');
        }

        $userKeterangan = $validated['keterangan'] ?? $request->input('reason') ?? $request->input('alasan') ?? '';
        $prefix = implode(' • ', $metaParts);
        $fullKeterangan = trim(implode(' | ', array_filter([$prefix, $userKeterangan])));

        $createdManeuvers = [];

        if (! empty($connectionsInput) && is_array($connectionsInput)) {
            foreach ($connectionsInput as $conn) {
                $cAsal = (int) ($conn['core_asal'] ?? 1);
                $cTujuan = (int) ($conn['core_tujuan'] ?? $cAsal);
                $tAsal = $conn['tube_asal'] ?? 'Tube 1';
                $tTujuan = $conn['tube_tujuan'] ?? 'Tube 1';
                $connKet = "({$tAsal}:C{$cAsal} -> {$tTujuan}:C{$cTujuan}) " . $fullKeterangan;

                $createdManeuvers[] = CoreManeuver::query()->create([
                    'tiket_id' => $tiket->id,
                    'segmen' => $validated['segmen'],
                    'core_asal' => $cAsal,
                    'core_tujuan' => $cTujuan,
                    'keterangan' => trim($connKet) ?: null,
                ]);
            }
        } else {
            $createdManeuvers[] = CoreManeuver::query()->create([
                'tiket_id' => $tiket->id,
                'segmen' => $validated['segmen'],
                'core_asal' => (int) $validated['core_asal'],
                'core_tujuan' => (int) $validated['core_tujuan'],
                'keterangan' => $fullKeterangan ?: null,
            ]);
        }

        $lastManeuver = end($createdManeuvers);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Manuver core berhasil dicatat (' . count($createdManeuvers) . ' sambungan).',
                'core_maneuver' => $lastManeuver,
            ]);
        }

        return redirect()->route('tickets.show', $tiket)->with('success', 'Manuver core berhasil dicatat (' . count($createdManeuvers) . ' sambungan).');
    }
}
