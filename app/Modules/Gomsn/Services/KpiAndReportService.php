<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;

class KpiAndReportService
{
    /**
     * Calculate Technician KPI Score (0 - 100) (§7.7).
     * Formula: KPI = 0.45 * SLA + 0.35 * Interval + 0.20 * Respon
     *
     * @return array{status: string, score: ?float, sla_score: float, interval_score: float, response_score: float, total_tickets: int}
     */
    public function calculateTechnicianKpi(User $technician, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Tiket::query()
            ->whereHas('ticketAssignees', fn ($q) => $q->where('user_id', $technician->id))
            ->where('status', 'CLOSE');

        if ($startDate) {
            $query->where('tanggal_open', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('tanggal_open', '<=', $endDate);
        }

        $tickets = $query->with(['breaches', 'statusHistories'])->get();
        $totalTickets = $tickets->count();

        // Edge case (§7.7): Data less than 5 tickets returns 'DATA_TIDAK_CUKUP'
        if ($totalTickets < 5) {
            return [
                'status' => 'DATA_TIDAK_CUKUP',
                'score' => null,
                'sla_score' => 0.0,
                'interval_score' => 0.0,
                'response_score' => 0.0,
                'total_tickets' => $totalTickets,
            ];
        }

        // 1. SLA Component: % of tickets with SLA TEPAT
        $onTimeCount = $tickets->where('sla_status', 'TEPAT')->count();
        $slaScore = ($totalTickets > 0) ? ($onTimeCount / $totalTickets) * 100.0 : 0.0;

        // 2. Interval Component: % without 30-min interval breaches
        $ticketsWithBreach = $tickets->filter(fn ($t) => $t->breaches->isNotEmpty())->count();
        $intervalScore = (($totalTickets - $ticketsWithBreach) / $totalTickets) * 100.0;

        // 3. Response Component: max(0, 100 - min(100, avg_response_minutes * 2))
        $responseMins = [];
        foreach ($tickets as $t) {
            if ($t->tanggal_open && $t->first_response_at) {
                $responseMins[] = (int) abs($t->tanggal_open->diffInMinutes($t->first_response_at));
            }
        }
        $avgResponse = ! empty($responseMins) ? array_sum($responseMins) / count($responseMins) : 10.0;
        $responseScore = max(0.0, 100.0 - min(100.0, $avgResponse * 2.0));

        // Combined Weighted Score (§7.7)
        $finalScore = (0.45 * $slaScore) + (0.35 * $intervalScore) + (0.20 * $responseScore);

        return [
            'status' => 'OK',
            'score' => round($finalScore, 2),
            'sla_score' => round($slaScore, 2),
            'interval_score' => round($intervalScore, 2),
            'response_score' => round($responseScore, 2),
            'total_tickets' => $totalTickets,
        ];
    }

    /**
     * Generate Berita Acara payload with document verification hash (§7.7).
     *
     * @return array<string, mixed>
     */
    public function generateBeritaAcaraData(Tiket $tiket): array
    {
        $tiket->loadMissing([
            'assignees',
            'chronologies.user',
            'materials.material',
            'repairPoints',
            'jointClosures.cores',
            'creator',
            'closingAwalUser',
            'verifierUser',
        ]);

        $lead = $tiket->assignees->firstWhere('pivot.role_in_team', 'lead');

        // Document security verification hash (§7.7, §10.2)
        $hashString = "{$tiket->no_tiket}|{$tiket->status}|{$tiket->tanggal_open}|{$tiket->tanggal_close}|{$tiket->net_mttr_minutes}";
        $docHash = hash('sha256', $hashString);

        return [
            'ticket' => $tiket,
            'lead' => $lead,
            'document_hash' => $docHash,
            'generated_at' => Carbon::now('Asia/Jakarta')->format('d F Y H:i:s').' WIB',
        ];
    }
}

