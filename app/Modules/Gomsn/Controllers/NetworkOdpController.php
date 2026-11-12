<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Odp;
use App\Models\OdpCustomer;
use App\Models\OdpUserHistory;
use App\Models\Olt;
use App\Models\Pon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NetworkOdpController extends Controller
{
    /**
     * Level 1: Daftar PON berdasarkan OLT yang dipilih.
     */
    public function index(Request $request): View
    {
        $olts = Olt::query()->orderBy('id')->get();

        if ($olts->isEmpty()) {
            // Fallback default olts if empty
            $olts = collect([
                Olt::create(['nama_olt' => 'OLT MSN', 'kode' => 'MSN']),
                Olt::create(['nama_olt' => 'OLT BAGONG', 'kode' => 'BAGONG']),
                Olt::create(['nama_olt' => 'OLT SOREANG', 'kode' => 'SOREANG']),
                Olt::create(['nama_olt' => 'OLT KOPO', 'kode' => 'KOPO']),
            ]);
        }

        $oltId = $request->query('olt_id', $olts->first()->id);
        $selectedOlt = $olts->firstWhere('id', (int) $oltId) ?? $olts->first();

        $query = $selectedOlt->pons()->withCount('odps');

        if ($search = $request->query('search')) {
            $query->where('nama_pon', 'like', "%{$search}%");
        }

        $pons = $query->orderBy('id', 'asc')->paginate(20)->withQueryString();

        // Calculate usage for each PON dynamically
        foreach ($pons as $pon) {
            $pon->calculated_usage = $pon->odps_count;
        }

        return view('gomsn.network.pons.index', compact('olts', 'selectedOlt', 'pons'));
    }

    /**
     * Tambah PON baru.
     */
    public function storePon(Request $request, Olt $olt): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pon' => ['required', 'string', 'max:150'],
            'port_max' => ['required', 'integer', 'in:2,4,8,16,24,32'],
        ]);

        $olt->pons()->create([
            'nama_pon' => $validated['nama_pon'],
            'port_max' => (int) $validated['port_max'],
            'port_terisi' => 0,
        ]);

        return redirect()->route('gomsn.network.index', ['olt_id' => $olt->id])
            ->with('success', "PON {$validated['nama_pon']} berhasil ditambahkan pada {$olt->nama_olt}.");
    }

    /**
     * Update PON.
     */
    public function updatePon(Request $request, Pon $pon): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pon' => ['required', 'string', 'max:150'],
            'port_max' => ['required', 'integer', 'in:2,4,8,16,24,32'],
        ]);

        $pon->update([
            'nama_pon' => $validated['nama_pon'],
            'port_max' => (int) $validated['port_max'],
        ]);

        return back()->with('success', "PON {$pon->nama_pon} berhasil diperbarui.");
    }

    /**
     * Hapus PON.
     */
    public function destroyPon(Pon $pon): RedirectResponse
    {
        $oltId = $pon->olt_id;
        $namaPon = $pon->nama_pon;
        $pon->delete();

        return redirect()->route('gomsn.network.index', ['olt_id' => $oltId])
            ->with('success', "PON {$namaPon} berhasil dihapus.");
    }

    /**
     * Level 2: Daftar ODP pada PON terpilih.
     */
    public function showPon(Request $request, Olt $olt, Pon $pon): View
    {
        $query = $pon->odps()->withCount(['customers' => function ($q) {
            $q->where('status', 'aktif');
        }]);

        if ($search = $request->query('search')) {
            $query->where('nama_odp', 'like', "%{$search}%");
        }

        $odps = $query->orderBy('id', 'asc')->paginate(25)->withQueryString();

        return view('gomsn.network.odps.index', compact('olt', 'pon', 'odps'));
    }

    /**
     * Tambah ODP baru pada PON.
     */
    public function storeOdp(Request $request, Pon $pon): RedirectResponse
    {
        $validated = $request->validate([
            'nama_odp' => ['required', 'string', 'max:150'],
            'port_max' => ['required', 'integer', 'in:4,8,16,20,24,32'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $odp = $pon->odps()->create([
            'nama_odp' => $validated['nama_odp'],
            'port_max' => (int) $validated['port_max'],
            'latitude' => $validated['latitude'] ?? 0.0,
            'longitude' => $validated['longitude'] ?? 0.0,
            'port_terpakai' => 0,
        ]);

        $pon->recalculateUsage();

        return redirect()->route('gomsn.network.pons.show', ['olt' => $pon->olt_id, 'pon' => $pon->id])
            ->with('success', "ODP {$odp->nama_odp} berhasil ditambahkan pada {$pon->nama_pon}.");
    }

    /**
     * Update ODP.
     */
    public function updateOdp(Request $request, Odp $odp): RedirectResponse
    {
        $validated = $request->validate([
            'nama_odp' => ['required', 'string', 'max:150'],
            'port_max' => ['required', 'integer', 'in:4,8,16,20,24,32'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $odp->update([
            'nama_odp' => $validated['nama_odp'],
            'port_max' => (int) $validated['port_max'],
            'latitude' => $validated['latitude'] ?? $odp->latitude,
            'longitude' => $validated['longitude'] ?? $odp->longitude,
        ]);

        return back()->with('success', "ODP {$odp->nama_odp} berhasil diperbarui.");
    }

    /**
     * Hapus ODP.
     */
    public function destroyOdp(Odp $odp): RedirectResponse
    {
        $pon = $odp->pon;
        $namaOdp = $odp->nama_odp;
        $odp->delete();

        if ($pon) {
            $pon->recalculateUsage();
            return redirect()->route('gomsn.network.pons.show', ['olt' => $pon->olt_id, 'pon' => $pon->id])
                ->with('success', "ODP {$namaOdp} berhasil dihapus.");
        }

        return back()->with('success', "ODP {$namaOdp} berhasil dihapus.");
    }

    /**
     * Level 3: Daftar Pelanggan/User pada ODP terpilih.
     */
    public function showOdp(Request $request, Olt $olt, Pon $pon, Odp $odp): View
    {
        $query = $odp->customers();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_user', 'like', "%{$search}%")
                    ->orWhere('nomor_internet', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('id', 'asc')->paginate(30)->withQueryString();
        $histories = $odp->histories()->take(40)->get();

        return view('gomsn.network.customers.index', compact('olt', 'pon', 'odp', 'customers', 'histories'));
    }

    /**
     * Tambah Pelanggan / User baru ke ODP.
     */
    public function storeCustomer(Request $request, Odp $odp): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_internet' => ['required', 'string', 'max:100'],
            'nama_user' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'layanan' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $oltName = $odp->pon?->olt?->kode ?? $odp->pon?->olt?->nama_olt ?? 'MSN';

        $customer = $odp->customers()->create([
            'nomor_internet' => $validated['nomor_internet'],
            'nama_user' => $validated['nama_user'],
            'keterangan' => $validated['keterangan'] ?? null,
            'layanan' => $validated['layanan'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'nomor' => $validated['nomor'] ?? null,
            'olt_name' => $oltName,
            'note' => $validated['note'] ?? null,
            'status' => 'aktif',
        ]);

        // Catat riwayat
        OdpUserHistory::create([
            'odp_id' => $odp->id,
            'pon_id' => $odp->pon_id,
            'user_id' => auth()->id(),
            'nomor_internet' => $customer->nomor_internet,
            'nama_pon' => $odp->pon?->nama_pon,
            'nama_odp' => $odp->nama_odp,
            'nama_user' => $customer->nama_user,
            'aksi' => 'tambah',
            'keterangan' => $customer->keterangan ?? 'Ditambahkan via Portal GoMSN2',
            'waktu_input' => now(),
        ]);

        $odp->recalculateUsage();

        return back()->with('success', "Pelanggan {$customer->nama_user} ({$customer->nomor_internet}) berhasil ditambahkan ke ODP {$odp->nama_odp}.");
    }

    /**
     * Update Pelanggan.
     */
    public function updateCustomer(Request $request, OdpCustomer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_internet' => ['required', 'string', 'max:100'],
            'nama_user' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'layanan' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:aktif,isolir,nonaktif'],
        ]);

        $customer->update($validated);

        // Catat riwayat
        OdpUserHistory::create([
            'odp_id' => $customer->odp_id,
            'pon_id' => $customer->odp?->pon_id,
            'user_id' => auth()->id(),
            'nomor_internet' => $customer->nomor_internet,
            'nama_pon' => $customer->odp?->pon?->nama_pon,
            'nama_odp' => $customer->odp?->nama_odp,
            'nama_user' => $customer->nama_user,
            'aksi' => 'edit',
            'keterangan' => $customer->keterangan ?? 'Perubahan data pelanggan',
            'waktu_input' => now(),
        ]);

        $customer->odp?->recalculateUsage();

        return back()->with('success', "Data pelanggan {$customer->nama_user} berhasil diperbarui.");
    }

    /**
     * Hapus Pelanggan dari ODP.
     */
    public function destroyCustomer(OdpCustomer $customer): RedirectResponse
    {
        $odp = $customer->odp;
        $namaUser = $customer->nama_user;
        $nomor = $customer->nomor_internet;

        // Catat riwayat sebelum hapus
        OdpUserHistory::create([
            'odp_id' => $customer->odp_id,
            'pon_id' => $odp?->pon_id,
            'user_id' => auth()->id(),
            'nomor_internet' => $nomor,
            'nama_pon' => $odp?->pon?->nama_pon,
            'nama_odp' => $odp?->nama_odp,
            'nama_user' => $namaUser,
            'aksi' => 'hapus',
            'keterangan' => 'Dihapus dari port ODP',
            'waktu_input' => now(),
        ]);

        $customer->delete();

        if ($odp) {
            $odp->recalculateUsage();
        }

        return back()->with('success', "Pelanggan {$namaUser} ({$nomor}) berhasil dihapus dari ODP.");
    }

    /**
     * API / JSON History logs for an ODP.
     */
    public function histories(Odp $odp): JsonResponse
    {
        $histories = $odp->histories()->take(50)->get();
        return response()->json([
            'odp' => $odp->nama_odp,
            'histories' => $histories,
        ]);
    }
}
