<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Gomsn\Services\KpiAndReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class KpiReportController extends Controller
{
    public function __construct(
        protected KpiAndReportService $kpiService,
    ) {}

    /**
     * Dashboard Analitik MTTR & KPI Teknisi (§7.7, §11.1).
     */
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // 1. Overall MTTR & SLA Statistics
        $closedTickets = Tiket::query()
            ->where('status', 'CLOSE')
            ->whereBetween('tanggal_open', [$startDate, $endDate])
            ->get();

        $totalClosed = $closedTickets->count();
        $onTimeCount = $closedTickets->where('sla_status', 'TEPAT')->count();
        $slaComplianceRate = $totalClosed > 0 ? round(($onTimeCount / $totalClosed) * 100, 1) : 0;
        $avgMttr = $totalClosed > 0 ? round($closedTickets->avg('net_mttr_minutes')) : 0;

        // 2. Technicians KPI Ranking safely without hardcoded Spatie role exception
        $technicians = User::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->whereIn('name', ['teknisi', 'teknis', 'karyawan_teknik', 'manager_teknisi']);
                })
                ->orWhere('division', 'like', '%Teknik%')
                ->orWhere('division', 'like', '%NOC%');
            })
            ->get();

        if ($technicians->isEmpty()) {
            $technicians = User::query()->where('is_active', true)->get();
        }

        $kpiList = [];
        foreach ($technicians as $tech) {
            $kpiData = $this->kpiService->calculateTechnicianKpi($tech, $startDate, $endDate);
            $kpiList[] = [
                'user' => $tech,
                'data' => $kpiData,
            ];
        }

        // Sort descending by score (nulls last)
        usort($kpiList, function ($a, $b) {
            $scoreA = $a['data']['score'] ?? -1;
            $scoreB = $b['data']['score'] ?? -1;

            return $scoreB <=> $scoreA;
        });

        return view('gomsn.analytics.kpi', compact(
            'totalClosed',
            'slaComplianceRate',
            'avgMttr',
            'kpiList',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Preview / Print Berita Acara Penyelesaian Gangguan (§7.7).
     */
    public function beritaAcara(Request $request, Tiket $tiket): View|Response
    {
        return $this->exportPdf($request, $tiket);
    }

    /**
     * Export Dokumen Berita Acara PDF (Internal Lengkap vs Pelanggan Customer).
     */
    public function exportPdf(Request $request, Tiket $tiket): View|Response
    {
        $type = $request->input('type', 'internal');

        $tiket->loadMissing([
            'creator',
            'closingAwalUser',
            'verifierUser',
            'materials.material',
            'repairPoints',
            'jointClosures.cores',
            'coreManeuvers',
            'assignees',
            'handovers.handoverUser',
            'handovers.receiverUser',
            'chronologies.user',
        ]);

        $viewName = $type === 'customer'
            ? 'reports.pdf_customer_tiket'
            : 'reports.pdf_single_tiket';

        // Base64 Logo untuk DomPDF
        $logoPath = public_path('assets/logo/logo-msn.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('assets/logo/logo-msn2.png');
        }
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        // Galeri Foto Dokumentasi Lapangan
        $photoDocs = $tiket->chronologies
            ->filter(fn ($c) => ! empty($c->photo_path))
            ->map(function ($c) {
                $base64 = null;
                $candidates = [
                    storage_path('app/public/' . $c->photo_path),
                    public_path('storage/' . $c->photo_path),
                    public_path($c->photo_path),
                    storage_path('app/' . $c->photo_path),
                ];
                foreach ($candidates as $cand) {
                    if (file_exists($cand) && ! is_dir($cand)) {
                        $mime = mime_content_type($cand) ?: 'image/jpeg';
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cand));
                        break;
                    }
                }

                return [
                    'category' => $c->category ?: 'Dokumentasi',
                    'message' => $c->message,
                    'created_at' => $c->created_at,
                    'user_name' => $c->user?->name ?? 'Teknisi',
                    'base64' => $base64,
                ];
            })
            ->filter(fn ($p) => ! empty($p['base64']))
            ->values();

        // Filter Kronologis Internal agar bersih dan informatif
        $cleanChronologies = $tiket->chronologies->filter(function ($c) {
            $msg = trim($c->message ?? '');
            if (empty($msg) && empty($c->photo_path)) {
                return false;
            }
            if (empty($c->photo_path) && in_array(strtolower($msg), ['p', 'tes', 'halo', 'cek', 'tes halo', 'hai', 'ini'], true)) {
                return false;
            }

            return true;
        })->values();

        if ($cleanChronologies->isEmpty()) {
            $cleanChronologies = $tiket->chronologies;
        }

        $data = [
            'tiket' => $tiket,
            'type' => $type,
            'logoBase64' => $logoBase64,
            'photoDocs' => $photoDocs,
            'chronologies' => $cleanChronologies,
            'printDate' => Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y H:i') . ' WIB',
        ];

        // Jika requested HTML preview
        if ($request->input('preview') === 'html') {
            return view($viewName, $data);
        }

        $pdf = Pdf::loadView($viewName, $data)->setPaper('a4', 'portrait');

        $ticketCode = $tiket->no_tiket ?: ('TIKET-' . $tiket->id);
        $filename = ($type === 'customer' ? 'Berita_Acara_Pelanggan_' : 'Berita_Acara_Internal_') . $ticketCode . '.pdf';

        if ($request->has('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}

