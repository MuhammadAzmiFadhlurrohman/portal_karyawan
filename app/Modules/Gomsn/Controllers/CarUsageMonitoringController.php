<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CarUsageMonitoringController extends Controller
{
    /**
     * Display car usage monitoring for HRD & Admin.
     * Sourced from approved fuel/car requests (approved by Atasan & Finance).
     */
    public function index(Request $request): View|StreamedResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['hrd', 'super_admin', 'admin'])) {
            abort(403, 'Akses pemantauan mobil hanya untuk HRD dan Administrator.');
        }

        $curMonth = (int) ($request->input('month', now('Asia/Jakarta')->month));
        $curYear = (int) ($request->input('year', now('Asia/Jakarta')->year));
        $divisionFilter = trim($request->input('division', 'all'));
        $searchTerm = trim($request->input('q', ''));
        $periodMode = $request->input('period_mode', 'month');

        $baseQuery = Pengajuan::query()
            ->with(['user', 'atasan', 'transferUser', 'details.material', 'approvals.approver'])
            // Filter keywords bensin / BBM / mobil operasional
            ->where(function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('judul', 'like', '%mobil%')
                        ->orWhere('judul', 'like', '%bensin%')
                        ->orWhere('judul', 'like', '%bbm%')
                        ->orWhere('judul', 'like', '%pertalite%')
                        ->orWhere('judul', 'like', '%pertamax%')
                        ->orWhere('judul', 'like', '%solar%')
                        ->orWhere('keperluan', 'like', '%mobil%')
                        ->orWhere('keperluan', 'like', '%bensin%')
                        ->orWhere('keperluan', 'like', '%bbm%')
                        ->orWhereHas('details', function ($qd): void {
                            $qd->where('deskripsi', 'like', '%mobil%')
                                ->orWhere('deskripsi', 'like', '%bensin%')
                                ->orWhere('deskripsi', 'like', '%bbm%')
                                ->orWhere('deskripsi', 'like', '%pertalite%')
                                ->orWhere('deskripsi', 'like', '%pertamax%')
                                ->orWhere('deskripsi', 'like', '%solar%');
                        });
                });
            })
            // Status sudah disetujui atasan dan finance
            ->where('status', '!=', 'ditolak')
            ->where(function ($q): void {
                $q->whereIn('status', ['selesai', 'dicairkan', 'ditransfer', 'menunggu_bukti', 'terealisasi'])
                    ->orWhere(function ($sub): void {
                        $sub->whereHas('approvals', fn ($qa) => $qa->where('stage', 'finance')->where('action', 'approved'));
                    });
            });

        // Filter divisi
        if ($divisionFilter && $divisionFilter !== 'all') {
            $baseQuery->whereHas('user', fn ($qu) => $qu->where('division', $divisionFilter));
        }

        // Filter pencarian teks
        if ($searchTerm !== '') {
            $baseQuery->where(function ($q) use ($searchTerm): void {
                $q->where('nomor_pengajuan', 'like', "%{$searchTerm}%")
                    ->orWhere('judul', 'like', "%{$searchTerm}%")
                    ->orWhere('keperluan', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($qu) use ($searchTerm): void {
                        $qu->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('employee_code', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // KPI Ringkasan
        $allApproved = (clone $baseQuery)->get();
        $totalUsages = $allApproved->count();
        $totalNominal = (float) $allApproved->sum('nominal_total');
        $uniqueDriversCount = $allApproved->pluck('user_id')->unique()->count();

        // Filter periode jika mode per-bulan
        $filteredQuery = clone $baseQuery;
        if ($periodMode === 'month') {
            $filteredQuery->where(function ($q) use ($curYear, $curMonth): void {
                $q->whereYear('transfer_at', $curYear)->whereMonth('transfer_at', $curMonth)
                    ->orWhere(function ($sub) use ($curYear, $curMonth): void {
                        $sub->whereNull('transfer_at')
                            ->whereYear('created_at', $curYear)
                            ->whereMonth('created_at', $curMonth);
                    });
            });
        }

        // Export CSV
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($filteredQuery->latest('created_at')->get(), $curMonth, $curYear);
        }

        $carUsages = $filteredQuery->latest('created_at')->paginate(15)->withQueryString();

        $divisions = User::query()
            ->whereNotNull('division')
            ->where('division', '!=', '')
            ->distinct()
            ->orderBy('division')
            ->pluck('division');

        return view('gomsn.car_usage.index', [
            'carUsages' => $carUsages,
            'totalUsages' => $totalUsages,
            'totalNominal' => $totalNominal,
            'uniqueDriversCount' => $uniqueDriversCount,
            'selectedMonth' => $curMonth,
            'selectedYear' => $curYear,
            'periodMode' => $periodMode,
            'divisionFilter' => $divisionFilter,
            'searchTerm' => $searchTerm,
            'divisions' => $divisions,
        ]);
    }

    private function exportCsv($items, int $month, int $year): StreamedResponse
    {
        $filename = "laporan-penggunaan-mobil-{$year}-" . sprintf('%02d', $month) . ".csv";

        return response()->streamDownload(function () use ($items): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'No',
                'No. Pengajuan',
                'Tanggal Pengajuan',
                'Nama Karyawan',
                'NIK / Kode',
                'Divisi',
                'Perihal / Judul',
                'Keperluan & Rute',
                'Personel / Tim',
                'Nominal Bensin (Rp)',
                'Disetujui Atasan',
                'Dicairkan Finance',
                'Status',
            ]);

            $no = 1;
            foreach ($items as $item) {
                $atasanApproval = $item->approvals->first(fn ($a) => $a->stage === 'atasan' && $a->action === 'approved');
                $financeApproval = $item->approvals->first(fn ($a) => $a->stage === 'finance' && $a->action === 'approved');
                $personel = implode(', ', $item->getPersonelList());

                fputcsv($handle, [
                    $no++,
                    $item->nomor_pengajuan,
                    $item->created_at?->format('d/m/Y H:i') ?? '-',
                    $item->user?->name ?? '-',
                    $item->user?->employee_code ?? '-',
                    $item->user?->division ?? '-',
                    $item->judul,
                    $item->keperluan,
                    $personel ?: ($item->user?->name ?? '-'),
                    $item->nominal_total,
                    $atasanApproval?->approver?->name ?? ($item->atasan?->name ?? 'Disetujui Atasan'),
                    $financeApproval?->approver?->name ?? ($item->transferUser?->name ?? 'Disetujui Finance'),
                    strtoupper(str_replace('_', ' ', $item->status)),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
