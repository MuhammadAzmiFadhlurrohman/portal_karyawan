<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterMaterial;
use App\Models\Pengajuan;
use App\Models\User;
use App\Modules\Gomsn\Services\PengajuanService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengajuanController extends Controller
{
    public function __construct(
        protected PengajuanService $pengajuanService,
    ) {}

    /**
     * Check if user has Atasan privileges.
     */
    public function isAtasanUser(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('hrd') && ! $user->hasRole(['super_admin', 'admin', 'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi'])) {
            return false;
        }

        return $user->can('gomsn.pengajuan.approve_atasan')
            || $user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']);
    }

    /**
     * Check if user has Finance privileges.
     */
    public function isFinanceUser(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('gomsn.pengajuan.approve_finance')
            || $user->hasRole(['finance', 'super_admin']);
    }

    /**
     * Display list of pengajuan dana & barang (§8.1).
     * For Atasan: represents "Pengajuan Saya" (their own submissions).
     * For Finance: represents submissions approved by Atasan, ready for disbursement / stock release.
     */
    public function index(Request $request): View|StreamedResponse
    {
        $user = $request->user();
        $isAtasan = $this->isAtasanUser($user);
        $isFinance = $this->isFinanceUser($user);
        $isSuperAdmin = $user->hasRole(['super_admin', 'admin', 'ceo']);

        $query = Pengajuan::query()->with(['user', 'details.material', 'approvals.approver', 'atasan']);

        // Tab selection: 'my' (Pengajuan Saya) vs 'finance' / 'all'
        $tab = $request->input('tab');

        if ($isAtasan && ! $isSuperAdmin && ! $isFinance) {
            // Atasan view for this page is strictly "Pengajuan Saya" (mandiri)
            $query->where('user_id', $user->id);
            $tab = 'my';
        } elseif ($isFinance && ! $isSuperAdmin) {
            // Finance view is strictly for submissions approved by Atasan, ready for disbursement / stock release.
            // Finance tidak membuat pengajuan untuk dirinya sendiri.
            // PENTING: Pengajuan yang DITOLAK ATASAN TIDAK BOLEH MUNCUL di daftar Finance.
            $query->where(function ($q) {
                $q->whereIn('status', ['menunggu_finance', 'dicairkan', 'menunggu_bukti', 'selesai'])
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'ditolak')
                          ->whereHas('approvals', function ($qa) {
                              $qa->where('stage', 'finance')->where('action', 'rejected');
                          });
                  });
            })->whereDoesntHave('approvals', function ($qa) {
                $qa->where('stage', 'atasan')->where('action', 'rejected');
            });
            $tab = 'finance';
        } elseif (! $isSuperAdmin) {
            // Karyawan biasa
            $query->where('user_id', $user->id);
            $tab = 'my';
        } else {
            // Super Admin / Executive
            if ($tab === 'my') {
                $query->where('user_id', $user->id);
            } else {
                $tab = 'all';
            }
        }

        // Search filter (ID / Nomor / Username / Nama / Jenis / Keperluan)
        $search = trim($request->input('search', $request->input('q', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        // Division filter
        if ($request->filled('divisi')) {
            $divisi = $request->input('divisi');
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('division', $divisi);
            });
        }

        // Month & Year filter
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', (int) $request->input('bulan'));
        }
        if ($request->filled('tahun')) {
            $query->whereYear('created_at', (int) $request->input('tahun'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', strtoupper($request->input('jenis')));
        }

        // CSV Export handler
        if ($request->input('export') === 'csv' || $request->input('export') === '1') {
            $exportItems = (clone $query)->orderBy('created_at', 'desc')->get();
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="pengajuan_export_' . date('Ymd_His') . '.csv"',
            ];
            $callback = function () use ($exportItems) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID Pengajuan', 'Nomor Pengajuan', 'Tanggal', 'Jenis', 'Karyawan', 'Divisi', 'Status', 'Total Nominal (Rp)', 'Keperluan']);
                foreach ($exportItems as $item) {
                    fputcsv($file, [
                        $item->id,
                        $item->nomor_pengajuan,
                        $item->created_at->format('Y-m-d H:i:s'),
                        $item->jenis,
                        $item->user?->name ?? '-',
                        $item->user?->division ?? '-',
                        $item->status,
                        $item->total_nominal,
                        $item->keperluan,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Calculate metrics for Finance dashboard
        $financeMetrics = [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->whereIn('status', ['dicairkan', 'menunggu_bukti', 'selesai'])->count(),
            'rejected' => (clone $query)->where('status', 'ditolak')->count(),
            'pending' => (clone $query)->whereIn('status', ['menunggu_finance', 'menunggu_atasan', 'menunggu_executive'])->count(),
            'total_dana' => (clone $query)->whereIn('status', ['dicairkan', 'menunggu_bukti', 'selesai'])->sum('nominal_total'),
        ];

        $divisions = User::whereNotNull('division')
            ->where('division', '!=', '')
            ->distinct()
            ->orderBy('division')
            ->pluck('division');

        $pengajuanList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $hasOverdue = $this->pengajuanService->hasOverdueRealization($user);

        $pendingApprovalsCount = 0;
        if ($isAtasan) {
            $pQuery = Pengajuan::where('status', 'menunggu_atasan')->where('user_id', '!=', $user->id);
            if (! $user->hasRole(['super_admin', 'admin'])) {
                $pQuery->where('atasan_id', $user->id);
            }
            $pendingApprovalsCount = $pQuery->count();
        }

        return view('gomsn.pengajuan.index', compact(
            'pengajuanList',
            'hasOverdue',
            'isAtasan',
            'isFinance',
            'isSuperAdmin',
            'tab',
            'pendingApprovalsCount',
            'financeMetrics',
            'divisions'
        ));
    }

    /**
     * Halaman khusus Atasan untuk menyetujui pengajuan dari karyawan bawahan (§8.1).
     * Hanya menampilkan pengajuan yang ditujukan kepada atasan yang sedang login.
     */
    public function approvals(Request $request): View
    {
        $user = $request->user();
        if (! $this->isAtasanUser($user)) {
            abort(403, 'Akses terbatas hanya untuk Atasan yang berwenang menyetujui pengajuan.');
        }

        $baseQuery = Pengajuan::where('user_id', '!=', $user->id);
        if (! $user->hasRole(['super_admin', 'admin'])) {
            $baseQuery->where('atasan_id', $user->id);
        }

        // Metrics KPI sesuai Mockup
        $totalCount = (clone $baseQuery)->count();

        $approvedQuery = (clone $baseQuery)->whereHas('approvals', function ($q) {
            $q->where('stage', 'atasan')->where('action', 'approved');
        });
        $approvedCount = $approvedQuery->count();

        $rejectedQuery = (clone $baseQuery)->where('status', 'ditolak')->whereHas('approvals', function ($q) {
            $q->where('stage', 'atasan')->where('action', 'rejected');
        });
        $rejectedCount = $rejectedQuery->count();

        $pendingCount = (clone $baseQuery)->where('status', 'menunggu_atasan')->count();
        $totalDana = (clone $approvedQuery)->where('jenis', '!=', 'STOK')->sum('nominal_total');

        // Query untuk tabel pengajuan karyawan
        $query = (clone $baseQuery)->with(['user', 'details.material', 'approvals.approver']);

        // Search: Cari ID / Username / Jenis
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $cleanId = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanId) {
                if (is_numeric($cleanId)) {
                    $q->where('id', (int) $cleanId);
                }
                $q->orWhere('nomor_pengajuan', 'like', "%{$search}%")
                  ->orWhere('jenis', 'like', "%{$search}%")
                  ->orWhere('keperluan', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', strtoupper($request->input('jenis')));
        }

        if ($request->filled('month')) {
            $query->whereMonth('created_at', (int) $request->input('month'));
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', (int) $request->input('year'));
        }

        $statusFilter = $request->input('status', 'all');
        if ($statusFilter && $statusFilter !== 'all') {
            if ($statusFilter === 'menunggu_atasan') {
                $query->where('status', 'menunggu_atasan');
            } elseif ($statusFilter === 'approved') {
                $query->whereIn('status', ['menunggu_finance', 'menunggu_executive', 'dicairkan', 'menunggu_bukti', 'selesai']);
            } elseif ($statusFilter === 'ditolak') {
                $query->where('status', 'ditolak');
            } else {
                $query->where('status', $statusFilter);
            }
        }

        $pengajuanList = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('gomsn.pengajuan.approvals', compact(
            'pengajuanList',
            'statusFilter',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalDana'
        ));
    }

    /**
     * Show pengajuan creation form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Finance tidak membuat pengajuan untuk dirinya sendiri
        if ($this->isFinanceUser($user) && ! $user->hasRole(['super_admin', 'admin'])) {
            return redirect()->route('gomsn.pengajuan.index')
                ->with('error', 'Akun Finance bertugas memproses pencairan dan pengeluaran stok, tidak dapat membuat pengajuan untuk dirinya sendiri.');
        }

        $isAtasan = $this->isAtasanUser($user);
        $hasOverdue = $this->pengajuanService->hasOverdueRealization($user);
        $materials = MasterMaterial::where('is_active', true)->orderBy('nama')->get();
        
        $atasanRoles = ['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi'];
        $excludedRoles = ['ceo', 'finance', 'hrd', 'super_admin', 'admin'];

        $atasans = \App\Models\User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereHas('roles', function ($q) use ($atasanRoles) {
                $q->whereIn('name', $atasanRoles);
            })
            ->whereDoesntHave('roles', function ($q) use ($excludedRoles) {
                $q->whereIn('name', $excludedRoles);
            })
            ->orderBy('name')
            ->get();

        $employees = \App\Models\User::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('gomsn.pengajuan.create', compact('hasOverdue', 'materials', 'atasans', 'employees', 'isAtasan'));
    }

    /**
     * Store draft pengajuan with items (§8.1).
     * Supports: DANA (Kasbon), BELI (Pengadaan), STOK (Gudang/Inventaris), PERMINTAAN (Barang)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Finance tidak membuat pengajuan untuk dirinya sendiri
        if ($this->isFinanceUser($user) && ! $user->hasRole(['super_admin', 'admin'])) {
            return redirect()->route('gomsn.pengajuan.index')
                ->with('error', 'Akun Finance bertugas memproses pencairan dan pengeluaran stok, tidak dapat membuat pengajuan untuk dirinya sendiri.');
        }

        $validated = $request->validate([
            'jenis' => ['required', 'string', 'in:DANA,BELI,STOK,PERMINTAAN,BARANG,dana,beli,stok,permintaan,barang'],
            'judul' => ['nullable', 'string', 'max:200'],
            'keperluan' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'atasan_id' => ['nullable', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.master_material_id' => ['nullable', 'integer', 'exists:master_materials,id'],
            'items.*.nama_barang' => ['nullable', 'string', 'max:255'],
            'items.*.deskripsi' => ['nullable', 'string', 'max:255'],
            'items.*.keperluan_dana' => ['nullable', 'string', 'max:255'],
            'items.*.keperluan_lainnya' => ['nullable', 'string', 'max:255'],
            'items.*.jumlah' => ['nullable', 'integer', 'min:1'],
            'items.*.jumlah_dana' => ['nullable', 'numeric', 'min:0'],
            'items.*.harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'items.*.personel' => ['nullable', 'array'],
        ]);

        $rawJenis = strtoupper($validated['jenis']);
        $keperluan = $validated['keterangan'] ?? $validated['keperluan'] ?? $validated['judul'] ?? 'Pengajuan Operasional';

        // Normalize items according to Jenis
        $normalizedItems = $this->normalizeItems($rawJenis, $validated['items']);

        try {
            $pengajuan = $this->pengajuanService->createPengajuan(
                user: $request->user(),
                data: [
                    'jenis' => $rawJenis,
                    'judul' => $keperluan,
                    'keperluan' => $keperluan,
                    'atasan_id' => ! empty($validated['atasan_id']) ? (int) $validated['atasan_id'] : null,
                ],
                items: $normalizedItems
            );

            // If user is Atasan, automatically submitted directly to Finance (tidak ke atasan lain)
            $isAtasan = $this->isAtasanUser($request->user());
            try {
                $this->pengajuanService->submitPengajuan($pengajuan, $request->user(), directToFinance: $isAtasan);
                $successMsg = $isAtasan
                    ? "Pengajuan {$pengajuan->nomor_pengajuan} ({$pengajuan->jenis}) berhasil dibuat dan otomatis langsung diteruskan ke Finance."
                    : "Pengajuan {$pengajuan->nomor_pengajuan} ({$pengajuan->jenis}) berhasil diajukan ke Atasan.";
                return redirect()->route('gomsn.pengajuan.index')->with('success', $successMsg);
            } catch (Exception $e) {
                return redirect()->route('gomsn.pengajuan.index')->with('success', "Draft pengajuan {$pengajuan->nomor_pengajuan} ({$pengajuan->jenis}) berhasil dibuat.");
            }
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['pengajuan' => $e->getMessage()]);
        }
    }

    /**
     * Show form to edit an existing pengajuan (§8.1).
     * Only permitted for submissions not yet approved by atasan or finance.
     */
    public function edit(Request $request, Pengajuan $pengajuan): View|RedirectResponse
    {
        $user = $request->user();

        // Check ownership
        if ($pengajuan->user_id !== $user->id && ! $user->hasRole(['super_admin', 'admin'])) {
            abort(403, 'Hanya pemohon yang dapat mengedit pengajuan ini.');
        }

        // Check if already approved by atasan or finance
        $hasApproved = $pengajuan->approvals()->where('action', 'approved')->exists();
        if ($hasApproved || ! in_array($pengajuan->status, ['draft', 'menunggu_atasan'], true)) {
            return redirect()->route('gomsn.pengajuan.index')
                ->with('error', 'Pengajuan tidak dapat diedit karena sudah disetujui oleh Atasan/Finance atau sedang diproses.');
        }

        $isAtasan = $this->isAtasanUser($user);
        $hasOverdue = $this->pengajuanService->hasOverdueRealization($user);
        $materials = MasterMaterial::where('is_active', true)->orderBy('nama')->get();

        $atasanRoles = ['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi'];
        $excludedRoles = ['ceo', 'finance', 'hrd', 'super_admin', 'admin'];

        $atasans = \App\Models\User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereHas('roles', function ($q) use ($atasanRoles) {
                $q->whereIn('name', $atasanRoles);
            })
            ->whereDoesntHave('roles', function ($q) use ($excludedRoles) {
                $q->whereIn('name', $excludedRoles);
            })
            ->orderBy('name')
            ->get();

        $employees = \App\Models\User::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Prepare existing items formatted for Alpine.js
        $pengajuan->load(['details.material']);
        $initialItems = [];
        $standardKeperluan = [
            'Bensin Motor', 'Bensin Mobil', 'Tol & Parkir',
            'Konsumsi Lapangan', 'Penginapan / Hotel Dinas',
            'Transportasi Umum / Tiket', 'Pembelian Material Cito'
        ];

        foreach ($pengajuan->details as $detail) {
            $desc = $detail->deskripsi ?? '';
            $personel = [];
            if (preg_match('/\[Personel:\s*(.*?)\]/', $desc, $m)) {
                $personel = array_map('trim', explode(',', $m[1]));
                $desc = trim(preg_replace('/\[Personel:\s*.*?\]/', '', $desc));
            }

            if (in_array($desc, $standardKeperluan, true)) {
                $kDana = $desc;
                $kLainnya = '';
            } else {
                $kDana = 'Lainnya';
                $kLainnya = $desc;
            }

            $initialItems[] = [
                'nama_barang' => $detail->deskripsi ?? '',
                'jumlah' => $detail->jumlah ?? 1,
                'harga_satuan' => (float) ($detail->harga_satuan ?? 0),
                'master_material_id' => $detail->master_material_id ? (string) $detail->master_material_id : '',
                'satuan' => $detail->material?->satuan ?? '',
                'keperluan_dana' => $kDana,
                'keperluan_lainnya' => $kLainnya,
                'jumlah_dana' => (float) ($detail->harga_satuan > 0 ? $detail->harga_satuan : $detail->subtotal),
                'personel' => $personel,
            ];
        }

        if (empty($initialItems)) {
            $initialItems[] = [
                'nama_barang' => '',
                'jumlah' => 1,
                'harga_satuan' => 0,
                'master_material_id' => '',
                'satuan' => '',
                'keperluan_dana' => 'Bensin Motor',
                'keperluan_lainnya' => '',
                'jumlah_dana' => 0,
                'personel' => [],
            ];
        }

        return view('gomsn.pengajuan.edit', compact(
            'pengajuan',
            'hasOverdue',
            'materials',
            'atasans',
            'employees',
            'isAtasan',
            'initialItems'
        ));
    }

    /**
     * Update existing pengajuan (§8.1).
     */
    public function update(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();

        if ($pengajuan->user_id !== $user->id && ! $user->hasRole(['super_admin', 'admin'])) {
            abort(403, 'Hanya pemohon yang dapat mengedit pengajuan ini.');
        }

        $hasApproved = $pengajuan->approvals()->where('action', 'approved')->exists();
        if ($hasApproved || ! in_array($pengajuan->status, ['draft', 'menunggu_atasan'], true)) {
            return redirect()->route('gomsn.pengajuan.index')
                ->with('error', 'Pengajuan tidak dapat diedit karena sudah disetujui oleh Atasan/Finance atau sedang diproses.');
        }

        $validated = $request->validate([
            'jenis' => ['required', 'string', 'in:DANA,BELI,STOK,PERMINTAAN,BARANG,dana,beli,stok,permintaan,barang'],
            'judul' => ['nullable', 'string', 'max:200'],
            'keperluan' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'atasan_id' => ['nullable', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.master_material_id' => ['nullable', 'integer', 'exists:master_materials,id'],
            'items.*.nama_barang' => ['nullable', 'string', 'max:255'],
            'items.*.deskripsi' => ['nullable', 'string', 'max:255'],
            'items.*.keperluan_dana' => ['nullable', 'string', 'max:255'],
            'items.*.keperluan_lainnya' => ['nullable', 'string', 'max:255'],
            'items.*.jumlah' => ['nullable', 'integer', 'min:1'],
            'items.*.jumlah_dana' => ['nullable', 'numeric', 'min:0'],
            'items.*.harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'items.*.personel' => ['nullable', 'array'],
        ]);

        $rawJenis = strtoupper($validated['jenis']);
        $keperluan = $validated['keterangan'] ?? $validated['keperluan'] ?? $validated['judul'] ?? 'Pengajuan Operasional';

        $normalizedItems = $this->normalizeItems($rawJenis, $validated['items']);

        try {
            $this->pengajuanService->updatePengajuan(
                pengajuan: $pengajuan,
                user: $user,
                data: [
                    'jenis' => $rawJenis,
                    'judul' => $keperluan,
                    'keperluan' => $keperluan,
                    'atasan_id' => ! empty($validated['atasan_id']) ? (int) $validated['atasan_id'] : null,
                ],
                items: $normalizedItems
            );

            return redirect()->route('gomsn.pengajuan.index')
                ->with('success', "Pengajuan {$pengajuan->nomor_pengajuan} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['pengajuan' => $e->getMessage()]);
        }
    }

    /**
     * Helper to normalize items according to Jenis.
     */
    protected function normalizeItems(string $rawJenis, array $rawItems): array
    {
        $normalized = [];
        foreach ($rawItems as $rawItem) {
            if ($rawJenis === 'DANA') {
                $desc = ($rawItem['keperluan_dana'] ?? '') === 'Lainnya'
                    ? ($rawItem['keperluan_lainnya'] ?? 'Keperluan Lainnya')
                    : ($rawItem['keperluan_dana'] ?? $rawItem['deskripsi'] ?? 'Operasional');

                if (! empty($rawItem['personel']) && is_array($rawItem['personel'])) {
                    $desc .= ' [Personel: ' . implode(', ', $rawItem['personel']) . ']';
                }

                $nominal = (float) ($rawItem['jumlah_dana'] ?? $rawItem['harga_satuan'] ?? 0);
                $normalized[] = [
                    'deskripsi' => $desc,
                    'jumlah' => 1,
                    'harga_satuan' => $nominal,
                    'master_material_id' => null,
                ];
            } elseif ($rawJenis === 'STOK') {
                $material = ! empty($rawItem['master_material_id'])
                    ? MasterMaterial::find($rawItem['master_material_id'])
                    : null;

                $normalized[] = [
                    'master_material_id' => $rawItem['master_material_id'] ?? null,
                    'deskripsi' => $material ? $material->nama : ($rawItem['deskripsi'] ?? 'Barang Gudang'),
                    'jumlah' => (int) ($rawItem['jumlah'] ?? 1),
                    'harga_satuan' => 0,
                ];
            } elseif ($rawJenis === 'PERMINTAAN' || $rawJenis === 'BARANG') {
                $normalized[] = [
                    'master_material_id' => null,
                    'deskripsi' => $rawItem['nama_barang'] ?? $rawItem['deskripsi'] ?? 'Barang Permintaan',
                    'jumlah' => (int) ($rawItem['jumlah'] ?? 1),
                    'harga_satuan' => 0,
                ];
            } else { // BELI
                $qty = (int) ($rawItem['jumlah'] ?? 1);
                $price = (float) ($rawItem['harga_satuan'] ?? 0);
                $normalized[] = [
                    'master_material_id' => null,
                    'deskripsi' => $rawItem['nama_barang'] ?? $rawItem['deskripsi'] ?? 'Barang Pengadaan',
                    'jumlah' => $qty,
                    'harga_satuan' => $price,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Show detail of pengajuan.
     */
    public function show(Request $request, Pengajuan $pengajuan): View
    {
        $pengajuan->load(['user', 'atasan', 'details', 'approvals.approver', 'transferUser']);

        return view('gomsn.pengajuan.show', compact('pengajuan'));
    }

    /**
     * Submit draft to Atasan or direct to Finance if requester is Atasan (§8.1).
     */
    public function submit(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        try {
            $isAtasan = $this->isAtasanUser($request->user());
            $this->pengajuanService->submitPengajuan($pengajuan, $request->user(), directToFinance: $isAtasan);

            $msg = $isAtasan
                ? 'Pengajuan mandiri Anda berhasil diteruskan langsung ke Finance.'
                : 'Pengajuan berhasil diajukan ke Atasan.';

            return redirect()->route('gomsn.pengajuan.show', $pengajuan)->with('success', $msg);
        } catch (Exception $e) {
            return back()->withErrors(['submit' => $e->getMessage()]);
        }
    }

    /**
     * Approve by Atasan (AC-10: strictly blocks self-approval).
     */
    public function approveAtasan(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (! $this->isAtasanUser($user)) {
            abort(403, 'Anda tidak memiliki hak akses persetujuan atasan.');
        }

        // Hanya atasan yang dituju yang boleh menyetujui (kecuali super_admin / admin)
        if (! $user->hasRole(['super_admin', 'admin']) && $pengajuan->atasan_id && $pengajuan->atasan_id !== $user->id) {
            abort(403, 'Pengajuan ini ditujukan kepada atasan lain.');
        }

        try {
            $this->pengajuanService->approveAtasan($pengajuan, $user, $request->input('notes'));

            return redirect()->back()->with('success', "Pengajuan {$pengajuan->nomor_pengajuan} telah disetujui dan diteruskan ke Finance.");
        } catch (Exception $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        }
    }

    /**
     * Approve by Executive (CEO / HRD) for large amounts (§8.1, AC-10).
     */
    public function approveExecutive(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (! $user->can('gomsn.pengajuan.approve_executive') && ! $user->hasRole(['ceo', 'hrd', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki hak akses persetujuan eksekutif.');
        }

        try {
            $this->pengajuanService->approveExecutive($pengajuan, $user, $request->input('notes'));

            return redirect()->route('gomsn.pengajuan.show', $pengajuan)->with('success', 'Pengajuan telah disetujui oleh Eksekutif.');
        } catch (Exception $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        }
    }

    /**
     * Disburse funds or release stock by Finance (§8.1).
     * Finance regulates warehouse inventory and cash disbursement.
     */
    public function disburse(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $user = $request->user();
        if (! $user->can('gomsn.pengajuan.approve_finance') && ! $user->hasRole(['finance', 'super_admin'])) {
            abort(403, 'Hanya bagian Finance yang berwenang mengatur stok dan memproses pencairan.');
        }

        $redirectRoute = $this->isFinanceUser($user) ? 'gomsn.pengajuan.index' : 'gomsn.pengajuan.show';

        // If STOK: Finance releases goods directly from inventory
        if ($pengajuan->jenis === 'STOK') {
            try {
                $notes = $request->input('notes') ?: ($request->input('metode_pencairan') ? 'Metode: ' . $request->input('metode_pencairan') : null);
                $this->pengajuanService->releaseStockFinance($pengajuan, $user, $notes);

                return redirect()->route($redirectRoute, $redirectRoute === 'gomsn.pengajuan.show' ? $pengajuan : [])
                    ->with('success', 'Barang berhasil dikeluarkan dari stok gudang dan diserahkan oleh Finance.');
            } catch (Exception $e) {
                return back()->withErrors(['disburse' => $e->getMessage()]);
            }
        }

        // If DANA / BELI: Finance transfers funds with transfer proof
        $request->validate([
            'bukti_transfer' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:4096'],
        ]);

        $path = $request->file('bukti_transfer')->store('gomsn/transfer_proofs', 'public');

        try {
            $this->pengajuanService->disburseFinance($pengajuan, $user, $path);

            return redirect()->route($redirectRoute, $redirectRoute === 'gomsn.pengajuan.show' ? $pengajuan : [])
                ->with('success', 'Dana pengajuan telah dicairkan dan dicatat oleh Finance.');
        } catch (Exception $e) {
            return back()->withErrors(['disburse' => $e->getMessage()]);
        }
    }

    /**
     * Alias for routes calling disburseFinance
     */
    public function disburseFinance(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        return $this->disburse($request, $pengajuan);
    }

    /**
     * Upload realization receipts (§8.1).
     */
    public function uploadRealization(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $nominal = $request->input('nominal_realisasi') ?? $request->input('realization_amount');
        $notes = $request->input('realisasi_notes') ?? $request->input('realization_notes') ?? $request->input('notes');
        $file = $request->file('bukti_realisasi') ?? $request->file('realization_proof');

        if ($nominal !== null) {
            $request->merge(['nominal_realisasi' => $nominal]);
        }
        if ($notes !== null) {
            $request->merge(['realisasi_notes' => $notes]);
        }
        if ($file !== null && ! $request->hasFile('bukti_realisasi')) {
            $request->files->set('bukti_realisasi', $file);
        }

        $request->validate([
            'nominal_realisasi' => ['required', 'numeric', 'min:0'],
            'bukti_realisasi' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:4096'],
            'realisasi_notes' => ['nullable', 'string'],
        ]);

        $path = $request->file('bukti_realisasi')->store('gomsn/realizations', 'public');

        try {
            $this->pengajuanService->uploadRealization(
                pengajuan: $pengajuan,
                user: $request->user(),
                nominalRealisasi: (float) $request->input('nominal_realisasi'),
                receiptPath: $path,
                notes: $request->input('realisasi_notes')
            );

            return back()->with('success', 'Bukti foto realisasi berhasil diunggah. Pengajuan selesai.');
        } catch (Exception $e) {
            return back()->withErrors(['realization' => $e->getMessage()]);
        }
    }

    /**
     * Alias for routes calling submitRealization
     */
    public function submitRealization(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        return $this->uploadRealization($request, $pengajuan);
    }

    /**
     * Reject pengajuan (§8.1).
     */
    public function reject(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        $stage = $request->input('stage', $pengajuan->current_approver_stage ?? 'atasan');

        if (! $request->user()->hasRole(['super_admin', 'admin']) && $pengajuan->atasan_id && $pengajuan->atasan_id !== $request->user()->id && $stage === 'atasan') {
            abort(403, 'Pengajuan ini ditujukan kepada atasan lain.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->pengajuanService->rejectPengajuan(
                pengajuan: $pengajuan,
                approver: $request->user(),
                stage: $stage,
                reason: $request->input('reason')
            );

            return redirect()->back()->with('success', 'Pengajuan telah ditolak.');
        } catch (Exception $e) {
            return back()->withErrors(['reject' => $e->getMessage()]);
        }
    }
}
