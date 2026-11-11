<?php

namespace App\Modules\Gomsn\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Gomsn\Services\KpiAndReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // 2. Technicians KPI Ranking
        $technicians = User::query()
            ->role('teknisi')
            ->where('is_active', true)
            ->get();

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
    public function beritaAcara(Request $request, Tiket $tiket): View
    {
        $payload = $this->kpiService->generateBeritaAcaraData($tiket);

        return view('gomsn.berita_acara.pdf', $payload);
    }
}

