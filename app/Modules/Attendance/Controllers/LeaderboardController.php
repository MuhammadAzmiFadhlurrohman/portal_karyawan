<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Attendance\Services\LeaderboardService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaderboardController extends Controller
{
    public function index(Request $request, LeaderboardService $leaderboardService): View
    {
        /** @var User $user */
        $user = $request->user();

        $periodType = (string) $request->string('period_type', 'monthly');
        if (! in_array($periodType, ['monthly', 'quarterly', 'yearly'], true)) {
            $periodType = 'monthly';
        }

        $year = (int) $request->integer('year', (int) now('Asia/Jakarta')->format('Y'));
        $monthOrQuarter = (int) $request->integer(
            'period_value',
            $periodType === 'quarterly'
                ? (int) ceil(((int) now('Asia/Jakarta')->format('n')) / 3)
                : (int) now('Asia/Jakarta')->format('n')
        );

        $rankings = $leaderboardService->getRankings(
            periodType: $periodType,
            year: $year,
            monthOrQuarter: $monthOrQuarter,
            viewerUserId: $user->id,
            maskOptOut: true
        );

        $monthNum = max(1, min(12, $monthOrQuarter));
        $monthDate = \Carbon\Carbon::createFromDate($year, $monthNum, 1, 'Asia/Jakarta')->locale('id');
        $monthName = $monthDate->translatedFormat('F');
        $myRanking = collect($rankings)->firstWhere('user_id', $user->id);

        return view('attendance.leaderboard', [
            'user' => $user,
            'periodType' => $periodType,
            'year' => $year,
            'periodValue' => $monthOrQuarter,
            'monthName' => $monthName,
            'myRanking' => $myRanking,
            'rankings' => $rankings,
        ]);
    }

    public function toggleOptOut(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $newState = ! $user->leaderboard_opt_out;
        $user->update([
            'leaderboard_opt_out' => $newState,
        ]);

        AuditLogger::record(
            action: 'leaderboard.privacy_opt_out_toggled',
            auditable: $user,
            newValues: ['leaderboard_opt_out' => $newState],
            reason: $newState
                ? 'Karyawan mengaktifkan opt-out privasi nama pada Leaderboard (§5.7)'
                : 'Karyawan menonaktifkan opt-out privasi nama pada Leaderboard (§5.7)',
            actorId: $user->id
        );

        return back()->with(
            'status',
            $newState
                ? 'Opt-out privasi aktif: Nama Anda kini disamarkan bagi rekan lain pada papan peringkat (§5.7).'
                : 'Opt-out privasi dinonaktifkan: Nama Anda ditampilkan kembali pada papan peringkat (§5.7).'
        );
    }

    public function exportCsv(Request $request, LeaderboardService $leaderboardService): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        $periodType = (string) $request->string('period_type', 'monthly');
        $year = (int) $request->integer('year', (int) now('Asia/Jakarta')->format('Y'));
        $periodValue = (int) $request->integer('period_value', (int) now('Asia/Jakarta')->format('n'));

        $isHrdOrAdmin = $user->can('attendance.employee.manage') || $user->hasRole('super_admin');

        $rankings = $leaderboardService->getRankings(
            periodType: $periodType,
            year: $year,
            monthOrQuarter: $periodValue,
            viewerUserId: $user->id,
            maskOptOut: ! $isHrdOrAdmin
        );

        $filename = "leaderboard-kehadiran-{$periodType}-{$year}-{$periodValue}.csv";

        return response()->streamDownload(function () use ($rankings): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            // UTF-8 BOM for Microsoft Excel compatibility (§5.7)
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Peringkat',
                'Kode Karyawan',
                'Nama Karyawan',
                'Divisi',
                'Hari Kerja Berjadwal',
                'Cuti/Sakit Sah',
                'Hari Kerja Efektif',
                'Hari Hadir',
                'Hari Tepat Waktu',
                'Total Menit Terlambat',
                'Persentase Kehadiran (%)',
            ]);

            foreach ($rankings as $row) {
                fputcsv($out, [
                    $row['rank'],
                    $row['employee_code'],
                    $row['display_name'],
                    $row['division'],
                    $row['scheduled_workdays'],
                    $row['excused_leave_days'],
                    $row['effective_workdays'],
                    $row['attended_days'],
                    $row['on_time_days'],
                    $row['total_late_minutes'],
                    number_format($row['present_pct'], 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
