<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterMaterial;
use App\Models\MaterialMovement;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Check if current user is authorized to manage inventory (Finance, Admin, Super Admin).
     */
    protected function authorizeFinance(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $canAccess = $user->hasRole(['finance', 'admin', 'super_admin'])
            || $user->can('gomsn.pengajuan.approve_finance');

        if (! $canAccess) {
            abort(403, 'Akses terbatas: Hanya divisi Finance dan Administrator yang dapat mengelola stok barang inventaris.');
        }
    }

    /**
     * Display inventory dashboard & list of office materials (§8.1).
     */
    public function index(Request $request): View
    {
        $this->authorizeFinance();

        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', 'all');
        $isActive = $request->input('is_active', 'all');

        $query = MasterMaterial::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        if ($status === 'aman') {
            $query->whereColumn('stok_tersedia', '>', 'min_stok');
        } elseif ($status === 'menipis') {
            $query->whereColumn('stok_tersedia', '<=', 'min_stok')
                  ->where('stok_tersedia', '>', 0);
        } elseif ($status === 'habis') {
            $query->where('stok_tersedia', '<=', 0);
        }

        if ($isActive !== 'all' && in_array($isActive, ['0', '1'], true)) {
            $query->where('is_active', (bool) $isActive);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'nama');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (in_array($sortBy, ['nama', 'kode', 'stok_tersedia', 'min_stok', 'updated_at'], true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('nama', 'asc');
        }

        $materials = $query->paginate(15)->withQueryString();

        // KPI Counts
        $totalItems = MasterMaterial::count();
        $safeCount = MasterMaterial::whereColumn('stok_tersedia', '>', 'min_stok')->count();
        $lowCount = MasterMaterial::whereColumn('stok_tersedia', '<=', 'min_stok')
            ->where('stok_tersedia', '>', 0)
            ->count();
        $emptyCount = MasterMaterial::where('stok_tersedia', '<=', 0)->count();

        // Recent 5 movements across all office stock
        $recentMovements = MaterialMovement::with(['material', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('gomsn.inventory.index', [
            'materials' => $materials,
            'totalItems' => $totalItems,
            'safeCount' => $safeCount,
            'lowCount' => $lowCount,
            'emptyCount' => $emptyCount,
            'search' => $search,
            'status' => $status,
            'isActive' => $isActive,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'recentMovements' => $recentMovements,
        ]);
    }

    /**
     * Store a newly created office inventory material.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeFinance();
        $user = auth()->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'satuan' => ['required', 'string', 'max:30'],
            'stok_tersedia' => ['required', 'integer', 'min:0'],
            'kode' => ['nullable', 'string', 'max:50', 'unique:master_materials,kode'],
            'min_stok' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $initialStock = (int) $validated['stok_tersedia'];
        $minStok = isset($validated['min_stok']) ? (int) $validated['min_stok'] : 2;

        if (empty($validated['kode'])) {
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $validated['nama']);
            $slug = strtoupper(substr($cleanName, 0, 4));
            if (empty($slug)) {
                $slug = 'BRG';
            }
            $kode = 'MAT-' . $slug . '-' . rand(100, 999);
            while (MasterMaterial::where('kode', $kode)->exists()) {
                $kode = 'MAT-' . $slug . '-' . rand(1000, 9999);
            }
        } else {
            $kode = strtoupper(trim($validated['kode']));
        }

        $material = DB::transaction(function () use ($validated, $initialStock, $minStok, $kode, $user) {
            $mat = MasterMaterial::create([
                'kode' => $kode,
                'nama' => trim($validated['nama']),
                'satuan' => strtolower(trim($validated['satuan'])),
                'stok_tersedia' => $initialStock,
                'min_stok' => $minStok,
                'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : true,
            ]);

            if ($initialStock > 0) {
                MaterialMovement::create([
                    'master_material_id' => $mat->id,
                    'user_id' => $user->id,
                    'type' => 'IN',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'notes' => $validated['notes'] ?? 'Stok awal barang baru di inventaris kantor',
                    'created_at' => now(),
                ]);
            }

            return $mat;
        });

        AuditLogger::record(
            action: 'inventory.material_created',
            auditable: $material,
            newValues: $material->toArray(),
            reason: 'Penambahan barang baru inventaris kantor oleh Finance',
            actorId: $user->id
        );

        return redirect()->route('gomsn.inventory.index')
            ->with('success', "Barang {$material->nama} ({$material->kode}) berhasil ditambahkan ke inventaris kantor.");
    }

    /**
     * Update material details (nama, satuan, min_stok, is_active).
     */
    public function update(Request $request, MasterMaterial $material): RedirectResponse
    {
        $this->authorizeFinance();
        $user = auth()->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'satuan' => ['required', 'string', 'max:30'],
            'kode' => ['nullable', 'string', 'max:50', Rule::unique('master_materials', 'kode')->ignore($material->id)],
            'stok_tersedia' => ['nullable', 'integer', 'min:0'],
            'min_stok' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $material->toArray();

        $kode = ! empty($validated['kode']) ? strtoupper(trim($validated['kode'])) : $material->kode;
        $minStok = isset($validated['min_stok']) ? (int) $validated['min_stok'] : $material->min_stok;
        $isActive = isset($validated['is_active']) ? (bool) $validated['is_active'] : $material->is_active;

        DB::transaction(function () use ($material, $validated, $kode, $minStok, $isActive, $user) {
            $stockBefore = $material->stok_tersedia;
            $newStock = isset($validated['stok_tersedia']) ? (int) $validated['stok_tersedia'] : $stockBefore;

            $material->update([
                'kode' => $kode,
                'nama' => trim($validated['nama']),
                'satuan' => strtolower(trim($validated['satuan'])),
                'stok_tersedia' => $newStock,
                'min_stok' => $minStok,
                'is_active' => $isActive,
            ]);

            if ($newStock !== $stockBefore) {
                $diff = $newStock - $stockBefore;
                $diffText = $diff > 0 ? "+{$diff}" : (string) $diff;
                MaterialMovement::create([
                    'master_material_id' => $material->id,
                    'user_id' => $user->id,
                    'type' => 'ADJUSTMENT',
                    'quantity' => abs($diff),
                    'stock_before' => $stockBefore,
                    'stock_after' => $newStock,
                    'notes' => "Pembaruan stok via Edit Barang (Selisih: {$diffText})",
                    'created_at' => now(),
                ]);
            }
        });

        AuditLogger::record(
            action: 'inventory.material_updated',
            auditable: $material,
            oldValues: $oldValues,
            newValues: $material->fresh()->toArray(),
            reason: 'Pembaruan data barang inventaris kantor oleh Finance',
            actorId: $user->id
        );

        return redirect()->route('gomsn.inventory.index')
            ->with('success', "Data barang {$material->nama} berhasil diperbarui.");
    }

    /**
     * Remove material from inventory.
     */
    public function destroy(MasterMaterial $material): RedirectResponse
    {
        $this->authorizeFinance();
        $user = auth()->user();
        $nama = $material->nama;

        AuditLogger::record(
            action: 'inventory.material_deleted',
            auditable: $material,
            oldValues: $material->toArray(),
            reason: "Penghapusan barang {$nama} dari inventaris stok kantor",
            actorId: $user->id
        );

        $material->delete();

        return redirect()->route('gomsn.inventory.index')
            ->with('success', "Barang '{$nama}' berhasil dihapus dari daftar stok.");
    }

    /**
     * Restock material (incoming stock by Finance).
     */
    public function restock(Request $request, MasterMaterial $material): RedirectResponse
    {
        $this->authorizeFinance();
        $user = auth()->user();

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (int) $validated['quantity'];

        DB::transaction(function () use ($material, $qty, $validated, $user) {
            $mat = MasterMaterial::lockForUpdate()->findOrFail($material->id);
            $stockBefore = $mat->stok_tersedia;
            $stockAfter = $stockBefore + $qty;

            $mat->stok_tersedia = $stockAfter;
            $mat->save();

            MaterialMovement::create([
                'master_material_id' => $mat->id,
                'user_id' => $user->id,
                'type' => 'IN',
                'quantity' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => ! empty($validated['notes']) ? $validated['notes'] : 'Restock stok barang kantor',
                'created_at' => now(),
            ]);
        });

        return redirect()->route('gomsn.inventory.index')
            ->with('success', "Berhasil menambahkan {$qty} {$material->satuan} stok untuk {$material->nama}.");
    }

    /**
     * Physical stock adjustment / opname.
     */
    public function adjust(Request $request, MasterMaterial $material): RedirectResponse
    {
        $this->authorizeFinance();
        $user = auth()->user();

        $validated = $request->validate([
            'actual_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $actual = (int) $validated['actual_stock'];

        DB::transaction(function () use ($material, $actual, $validated, $user) {
            $mat = MasterMaterial::lockForUpdate()->findOrFail($material->id);
            $stockBefore = $mat->stok_tersedia;
            $diff = $actual - $stockBefore;

            $mat->stok_tersedia = $actual;
            $mat->save();

            $diffText = $diff > 0 ? "+{$diff}" : (string) $diff;
            MaterialMovement::create([
                'master_material_id' => $mat->id,
                'user_id' => $user->id,
                'type' => 'ADJUSTMENT',
                'quantity' => abs($diff),
                'stock_before' => $stockBefore,
                'stock_after' => $actual,
                'notes' => "Penyesuaian stok opname fisik: {$validated['reason']} (Selisih: {$diffText})",
                'created_at' => now(),
            ]);
        });

        return redirect()->route('gomsn.inventory.index')
            ->with('success', "Penyesuaian stok fisik untuk {$material->nama} berhasil disimpan menjadi {$actual} {$material->satuan}.");
    }

    /**
     * Get movement history for a specific material via JSON (for modal viewing).
     */
    public function movements(MasterMaterial $material): JsonResponse
    {
        $this->authorizeFinance();

        $movements = $material->movements()
            ->with(['user:id,name,username', 'ticket:id,nomor_tiket'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'quantity' => $item->quantity,
                    'stock_before' => $item->stock_before,
                    'stock_after' => $item->stock_after,
                    'notes' => $item->notes,
                    'user_name' => $item->user?->name ?? 'Sistem',
                    'ticket_nomor' => $item->ticket?->nomor_tiket,
                    'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-',
                ];
            });

        return response()->json([
            'material' => [
                'id' => $material->id,
                'kode' => $material->kode,
                'nama' => $material->nama,
                'satuan' => $material->satuan,
                'stok_tersedia' => $material->stok_tersedia,
                'min_stok' => $material->min_stok,
            ],
            'movements' => $movements,
        ]);
    }
}

