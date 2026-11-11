<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Lembur;
use App\Modules\Gomsn\Services\OvertimeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function __construct(
        protected OvertimeService $overtimeService,
    ) {}

    /**
     * Check if user has supervisor role for overtime approval (§8.2).
     */
    public function isAtasanUser(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('gomsn.lembur.approve')
            || $user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin', 'hrd']);
    }

    /**
     * Display list of overtime requests (§8.2).
     * For Atasan: Displays overtime submissions from employees of the same division that need approval.
     * For regular employee: Displays their own overtime submissions.
     */
    /**
     * Display list of overtime requests (§8.2).
     * For Atasan: Displays overtime submissions from employees of the same division.
     * For HRD & Finance: Reviews & approves overtime that has been approved by atasan.
     * For HRD: Can also create and view overtime for themselves.
     * For regular employee: Displays their own overtime submissions.
     */
    public function index(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = $request->user();
        $isAtasan = $this->isAtasanUser($user);
        $isFinance = $user->hasRole('finance');
        $isHrd = $user->hasRole('hrd');
        $isSuperAdmin = $user->hasRole(['super_admin', 'admin']);

        $selectedBulan = (int) $request->input('bulan', Carbon::now()->month);
        $selectedTahun = (int) $request->input('tahun', Carbon::now()->year);
        $selectedUserId = $request->input('user_id');
        $searchKaryawan = $request->input('cari_karyawan');
        $selectedDivisi = $request->input('divisi');
        $viewMode = $request->input('view_mode', 'karyawan'); // 'karyawan' atau 'saya' (khusus HRD)

        $baseQuery = Lembur::query()->with(['user', 'atasan', 'approver', 'payer']);

        if ($isFinance && ! $isSuperAdmin) {
            // Finance mengapprove / membayar pengajuan lembur yang sudah disetujui atasan
            $baseQuery->whereIn('status', ['approved_atasan', 'paid']);
        } elseif ($isHrd && ! $isSuperAdmin) {
            // HRD mengapprove pengajuan lembur karyawan yang sudah disetujui atasan,
            // atau jika mode 'saya' menampilkan lembur miliknya sendiri
            if ($viewMode === 'saya') {
                $baseQuery->where('user_id', $user->id);
            } else {
                $baseQuery->where(function ($q) use ($user) {
                    $q->whereIn('status', ['approved_atasan', 'paid'])
                      ->orWhere('user_id', $user->id);
                });
            }
        } elseif ($isAtasan && ! $isSuperAdmin && ! $isFinance) {
            // Atasan HANYA melihat pengajuan lembur milik bawahan
            $baseQuery->where('user_id', '!=', $user->id);
            $baseQuery->where(function ($q) use ($user) {
                $q->where('atasan_id', $user->id);
                if (! empty($user->division)) {
                    $q->orWhereHas('user', function ($qu) use ($user) {
                        $qu->whereRaw('LOWER(division) = ?', [strtolower($user->division)]);
                    });
                }
            });
        } elseif (! $isSuperAdmin) {
            // Karyawan biasa hanya melihat lembur miliknya sendiri
            $baseQuery->where('user_id', $user->id);
        }

        // Filter Cari Karyawan
        if ($request->filled('cari_karyawan')) {
            $baseQuery->whereHas('user', function ($q) use ($searchKaryawan) {
                $q->where('name', 'like', "%{$searchKaryawan}%")
                  ->orWhere('username', 'like', "%{$searchKaryawan}%");
            });
        }

        // Filter Divisi
        if ($request->filled('divisi') && $selectedDivisi !== 'all' && $selectedDivisi !== 'Semua Divisi') {
            $baseQuery->whereHas('user', function ($q) use ($selectedDivisi) {
                $q->whereRaw('LOWER(division) = ?', [strtolower($selectedDivisi)]);
            });
        }

        // Filter status jika dipilih eksplisit
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $baseQuery->where('status', $request->input('status'));
        }

        // Filter Periode Bulan & Tahun
        $monthBaseQuery = (clone $baseQuery)
            ->whereYear('tanggal', $selectedTahun)
            ->whereMonth('tanggal', $selectedBulan);

        // Export Excel / CSV jika diminta
        if ($request->has('export') && $request->input('export') === 'excel') {
            return $this->exportOvertime(clone $monthBaseQuery, $selectedBulan, $selectedTahun);
        }

        // Summary counts
        if ($selectedUserId) {
            $userMonthQuery = (clone $monthBaseQuery)->where('user_id', $selectedUserId);
            $totalLemburCount = (clone $userMonthQuery)->count();
            $menungguCount = (clone $userMonthQuery)->where('status', 'pending')->count();
            $disetujuiCount = (clone $userMonthQuery)->whereIn('status', ['approved_atasan', 'paid'])->count();
            $ditolakCount = (clone $userMonthQuery)->where('status', 'rejected')->count();
        } else {
            $totalLemburCount = (clone $monthBaseQuery)->count();
            $menungguCount = (clone $monthBaseQuery)->where('status', 'pending')->count();
            $disetujuiCount = (clone $monthBaseQuery)->whereIn('status', ['approved_atasan', 'paid'])->count();
            $ditolakCount = (clone $monthBaseQuery)->where('status', 'rejected')->count();
        }

        // Calculate employee summary cards (Untuk Finance, HRD, Atasan, SuperAdmin)
        $employeeStats = [];
        if ($isAtasan || $isFinance || $isHrd || $isSuperAdmin) {
            $monthLemburs = (clone $monthBaseQuery)->get();
            $groupedByEmployee = $monthLemburs->groupBy('user_id');
            foreach ($groupedByEmployee as $uid => $items) {
                $employeeUser = $items->first()->user;
                if (! $employeeUser) {
                    continue;
                }
                $totalNominal = $items->sum(fn ($i) => round(($i->durasi_menit / 60) * 5000));
                $employeeStats[] = [
                    'user' => $employeeUser,
                    'total_days' => $items->pluck('tanggal')->map(fn ($d) => Carbon::parse($d)->toDateString())->unique()->count(),
                    'total_count' => $items->count(),
                    'total_nominal' => $totalNominal,
                    'pending_count' => $items->where('status', 'pending')->count(),
                    'approved_count' => $items->whereIn('status', ['approved_atasan', 'paid'])->count(),
                    'rejected_count' => $items->where('status', 'rejected')->count(),
                ];
            }
        }

        // Detail list query
        $query = clone $monthBaseQuery;
        $detailEmployee = null;
        $totalNominalLembur = 0;
        $totalCountLembur = 0;

        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
            $detailEmployee = \App\Models\User::find($selectedUserId);
            $allSelectedUserLemburs = (clone $query)->get();
            $totalNominalLembur = $allSelectedUserLemburs->sum(fn ($i) => round(($i->durasi_menit / 60) * 5000));
            $totalCountLembur = $allSelectedUserLemburs->count();
        }

        $lemburList = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        $pendingLemburCount = $menungguCount;

        // Daftar divisi untuk filter dropdown
        $divisions = \App\Models\User::whereNotNull('division')
            ->where('division', '!=', '')
            ->pluck('division')
            ->unique()
            ->values()
            ->toArray();
        if (empty($divisions)) {
            $divisions = ['Teknik', 'NOC', 'Umum', 'Finance', 'HRD'];
        }

        return view('gomsn.overtime.index', compact(
            'lemburList',
            'isAtasan',
            'isFinance',
            'isHrd',
            'isSuperAdmin',
            'pendingLemburCount',
            'selectedBulan',
            'selectedTahun',
            'selectedUserId',
            'detailEmployee',
            'totalLemburCount',
            'menungguCount',
            'disetujuiCount',
            'ditolakCount',
            'employeeStats',
            'searchKaryawan',
            'selectedDivisi',
            'divisions',
            'totalNominalLembur',
            'totalCountLembur',
            'viewMode'
        ));
    }

    /**
     * Export overtime data to CSV (Excel compatible).
     */
    protected function exportOvertime($query, int $bulan, int $tahun): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = "Lembur_{$tahun}_{$bulan}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'NO', 'NOMOR LEMBUR', 'NAMA KARYAWAN', 'DIVISI', 'TANGGAL', 'JENIS',
                'JAM MULAI', 'JAM SELESAI', 'DURASI (MENIT)', 'DURASI (JAM)', 'ALASAN',
                'STATUS', 'UANG LEMBUR (RP)', 'DISETUJUI OLEH', 'TANGGAL DISETUJUI'
            ]);

            $no = 1;
            $query->orderBy('tanggal', 'asc')->chunk(200, function ($items) use ($handle, &$no) {
                foreach ($items as $item) {
                    $hours = floor($item->durasi_menit / 60);
                    $mins = $item->durasi_menit % 60;
                    $durasiStr = "{$hours} jam " . sprintf('%02d menit', $mins);
                    $uangLembur = round(($item->durasi_menit / 60) * 5000);

                    fputcsv($handle, [
                        $no++,
                        $item->nomor_lembur,
                        $item->user?->name ?? '-',
                        $item->user?->division ?? '-',
                        $item->tanggal?->format('Y-m-d') ?? '-',
                        $item->jenis_lembur ?? 'Lembur',
                        $item->jam_mulai?->format('H:i') ?? '-',
                        $item->jam_selesai?->format('H:i') ?? '-',
                        $item->durasi_menit,
                        $durasiStr,
                        $item->alasan,
                        $item->status,
                        $uangLembur,
                        $item->approver?->name ?? '-',
                        $item->approved_at?->format('Y-m-d H:i') ?? '-'
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Update overtime by supervisor (§8.2).
     */
    public function update(Request $request, Lembur $lembur): RedirectResponse
    {
        $user = $request->user();

        if (! $this->isAtasanUser($user) && ! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk mengedit lembur karyawan.');
        }

        if (! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            $isSameDivision = $lembur->user && $lembur->user->division && $user->division && strtolower($lembur->user->division) === strtolower($user->division);
            $isDesignated = $lembur->atasan_id === $user->id;

            if (! $isSameDivision && ! $isDesignated) {
                abort(403, 'Pengajuan lembur ini berasal dari divisi yang berbeda.');
            }
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jenis_lembur' => ['nullable', 'string', 'max:80'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'alasan' => ['required', 'string', 'max:255'],
        ], [
            'alasan.required' => 'Alasan lembur wajib diisi.',
            'alasan.max' => 'Alasan lembur maksimal 255 karakter.',
        ]);

        try {
            $this->overtimeService->updateOvertime(
                lembur: $lembur,
                tanggal: $validated['tanggal'],
                jamMulai: $validated['jam_mulai'],
                jamSelesai: $validated['jam_selesai'],
                alasan: $validated['alasan'],
                jenisLembur: $validated['jenis_lembur'] ?? 'Lembur Hari Kerja',
                actor: $user
            );

            return back()->with('status', "Pengajuan lembur {$lembur->nomor_lembur} berhasil disesuaikan.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show create overtime form (§8.2).
     * Atasan is strictly blocked from creating overtime (only employees submit overtime).
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($this->isAtasanUser($user) && ! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            return redirect()->route('gomsn.overtime.index')
                ->with('error', 'Sebagai Atasan, Anda tidak mengajukan lembur. Halaman ini digunakan untuk memeriksa dan menyetujui pengajuan lembur karyawan.');
        }

        return view('gomsn.overtime.create');
    }

    /**
     * Check if user attended on the selected date (§8.2).
     */
    public function checkAttendance(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal');
        if (! $tanggal) {
            return response()->json([
                'has_attendance' => false,
                'message' => 'Tanggal belum dipilih',
                'subtext' => 'Pilih tanggal lembur yang valid',
            ]);
        }

        try {
            $date = Carbon::parse($tanggal)->toDateString();
        } catch (Exception) {
            return response()->json([
                'has_attendance' => false,
                'message' => 'Format tanggal tidak valid',
                'subtext' => 'Gunakan format tanggal YYYY-MM-DD',
            ]);
        }

        $user = $request->user();

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where(function ($q) {
                $q->whereIn('status', ['present', 'late'])
                  ->orWhereNotNull('clock_in_at');
            })
            ->first();

        if (! $attendance) {
            return response()->json([
                'has_attendance' => false,
                'message' => 'Tidak ditemukan absensi pada tanggal tersebut',
                'subtext' => 'Pastikan Anda sudah melakukan absensi dengan status Hadir/Tepat Waktu',
            ]);
        }

        $clockIn = $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '-';
        $clockOut = $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : 'Belum checkout';
        $statusLabel = match ($attendance->status) {
            'present' => 'Hadir / Tepat Waktu',
            'late' => 'Hadir (Terlambat ' . ($attendance->late_minutes ?? 0) . ' mnt)',
            default => 'Hadir'
        };

        return response()->json([
            'has_attendance' => true,
            'status' => $attendance->status,
            'status_label' => $statusLabel,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'message' => 'Absensi terverifikasi',
            'subtext' => "Ditemukan absensi pada tanggal ini (Masuk: {$clockIn} - Pulang: {$clockOut})",
        ]);
    }

    /**
     * Store overtime request (§8.2).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($this->isAtasanUser($user) && ! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            return redirect()->route('gomsn.overtime.index')
                ->with('error', 'Atasan tidak diizinkan membuat pengajuan lembur.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jenis_lembur' => ['nullable', 'string', 'max:80'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
            'alasan' => ['required', 'string', 'max:100'],
        ], [
            'alasan.max' => 'Alasan lembur maksimal 100 karakter.',
        ]);

        try {
            $lembur = $this->overtimeService->createOvertime(
                user: $user,
                tanggal: $validated['tanggal'],
                jamMulai: $validated['jam_mulai'],
                jamSelesai: $validated['jam_selesai'],
                alasan: $validated['alasan'],
                jenisLembur: $validated['jenis_lembur'] ?? 'Lembur Hari Kerja',
                requireAttendance: true
            );

            return redirect()->route('gomsn.overtime.index')
                ->with('success', "Pengajuan lembur {$lembur->nomor_lembur} berhasil diajukan.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Supervisor approval (§8.2, AC-10).
     */
    public function approve(Request $request, Lembur $lembur): RedirectResponse
    {
        $user = $request->user();

        if (! $user->can('gomsn.lembur.approve') && ! $user->hasRole(['super_admin', 'admin', 'hrd', 'atasan', 'atasan_teknik', 'atasan_noc', 'atasan_umum', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak otorisasi persetujuan lembur.');
        }

        if (! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            $isSameDivision = $lembur->user && $lembur->user->division && $user->division && strtolower($lembur->user->division) === strtolower($user->division);
            $isDesignated = $lembur->atasan_id === $user->id;

            if (! $isSameDivision && ! $isDesignated) {
                abort(403, 'Pengajuan lembur ini berasal dari divisi yang berbeda.');
            }
        }

        try {
            $this->overtimeService->approveSupervisor($lembur, $user);

            return redirect()->route('gomsn.overtime.index')
                ->with('success', "Lembur {$lembur->nomor_lembur} disetujui. Rekonsiliasi presensi: {$lembur->reconciliation_flag}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Supervisor rejection (§8.2).
     */
    public function reject(Request $request, Lembur $lembur): RedirectResponse
    {
        $user = $request->user();

        if (! $user->can('gomsn.lembur.approve') && ! $user->hasRole(['super_admin', 'admin', 'hrd', 'atasan', 'atasan_teknik', 'atasan_noc', 'atasan_umum', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak otorisasi penolakan lembur.');
        }

        if (! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            $isSameDivision = $lembur->user && $lembur->user->division && $user->division && strtolower($lembur->user->division) === strtolower($user->division);
            $isDesignated = $lembur->atasan_id === $user->id;

            if (! $isSameDivision && ! $isDesignated) {
                abort(403, 'Pengajuan lembur ini berasal dari divisi yang berbeda.');
            }
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->overtimeService->rejectSupervisor($lembur, $user, $validated['reason'] ?? null);

            return redirect()->route('gomsn.overtime.index')
                ->with('success', "Pengajuan lembur {$lembur->nomor_lembur} telah ditolak.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Finance payment confirmation (§8.2).
     */
    public function pay(Request $request, Lembur $lembur): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole(['super_admin', 'finance'])) {
            return back()->with('error', 'Hanya bagian Finance yang berwenang mencatat pembayaran lembur.');
        }

        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:100'],
        ]);

        try {
            $this->overtimeService->payOvertime($lembur, $user, $validated['payment_reference']);

            return redirect()->route('gomsn.overtime.index')
                ->with('success', "Lembur {$lembur->nomor_lembur} berhasil ditandai telah dibayar.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

