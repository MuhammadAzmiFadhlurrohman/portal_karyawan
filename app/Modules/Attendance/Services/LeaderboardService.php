<?php

namespace App\Modules\Attendance\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\User;
use Carbon\Carbon;

class LeaderboardService
{
    /**
     * Compute attendance leaderboard for monthly, quarterly, or yearly range (§5.7).
     * Sort order:
     * 1. present_pct DESC
     * 2. total_late_minutes ASC
     * 3. on_time_days DESC
     *
     * @return array<int, array{
     *   rank: int,
     *   user_id: int,
     *   employee_code: string,
     *   display_name: string,
     *   raw_name: string,
     *   division: string,
     *   is_opted_out: bool,
     *   scheduled_workdays: int,
     *   excused_leave_days: int,
     *   effective_workdays: int,
     *   attended_days: int,
     *   on_time_days: int,
     *   total_late_minutes: int,
     *   present_pct: float
     * }>
     */
    public function getRankings(
        string $periodType = 'monthly',
        int $year = 2026,
        int $monthOrQuarter = 11,
        ?int $viewerUserId = null,
        bool $maskOptOut = true
    ): array {
        [$startDate, $endDate] = $this->resolveDateRange($periodType, $year, $monthOrQuarter);
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $holidays = NationalHoliday::query()
            ->whereBetween('date', [$startStr, $endStr])
            ->pluck('date')
            ->map(fn ($d): string => Carbon::parse((string) $d)->toDateString())
            ->flip()
            ->all();

        $users = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $schedulesByUser = MonthlySchedule::query()
            ->with('shift')
            ->whereDate('date', '>=', $startStr)
            ->whereDate('date', '<=', $endStr)
            ->get()
            ->groupBy('user_id');

        $attendancesByUser = Attendance::query()
            ->whereDate('date', '>=', $startStr)
            ->whereDate('date', '<=', $endStr)
            ->get()
            ->groupBy('user_id');

        $approvedLeavesByUser = Leave::query()
            ->where('status', 'approved')
            ->where('is_alpa_conversion', false)
            ->whereDate('start_date', '<=', $endStr)
            ->whereDate('end_date', '>=', $startStr)
            ->get()
            ->groupBy('user_id');

        $rows = [];

        foreach ($users as $user) {
            $userSchedules = $schedulesByUser->get($user->id, collect());
            $userAttendances = $attendancesByUser->get($user->id, collect())
                ->keyBy(fn (Attendance $a): string => $a->date->toDateString());
            $userLeaves = $approvedLeavesByUser->get($user->id, collect());

            $scheduledWorkdays = 0;
            $excusedLeaveDays = 0;
            $attendedDays = 0;
            $onTimeDays = 0;
            $totalLateMinutes = 0;

            foreach ($userSchedules as $sched) {
                $dtStr = $sched->date->toDateString();
                if (! $sched->shift || $sched->shift->is_day_off || isset($holidays[$dtStr])) {
                    continue;
                }

                if ($user->join_date && $sched->date->lt($user->join_date->copy()->startOfDay())) {
                    continue;
                }

                $scheduledWorkdays++;

                // Check if covered by approved annual/special leave or documented sick leave (§5.7)
                $excusedLeave = $userLeaves->first(function (Leave $lv) use ($dtStr): bool {
                    if ($dtStr < $lv->start_date->toDateString() || $dtStr > $lv->end_date->toDateString()) {
                        return false;
                    }
                    if (in_array($lv->leave_type, ['annual', 'special'], true)) {
                        return true;
                    }
                    if ($lv->leave_type === 'sick' && ! empty($lv->attachment_path)) {
                        return true;
                    }

                    return false;
                });

                if ($excusedLeave) {
                    $excusedLeaveDays++;

                    continue;
                }

                /** @var Attendance|null $att */
                $att = $userAttendances->get($dtStr);
                if ($att && in_array($att->status, ['present', 'late'], true)) {
                    $attendedDays++;
                    $totalLateMinutes += (int) $att->late_minutes;
                    if ($att->status === 'present' && (int) $att->late_minutes === 0) {
                        $onTimeDays++;
                    }
                }
            }

            $effectiveWorkdays = max(0, $scheduledWorkdays - $excusedLeaveDays);
            $presentPct = $effectiveWorkdays > 0
                ? round(($attendedDays / $effectiveWorkdays) * 100.0, 2)
                : ($scheduledWorkdays > 0 && $excusedLeaveDays === $scheduledWorkdays ? 100.0 : 0.0);

            $isOptedOut = (bool) $user->leaderboard_opt_out;
            $shouldMask = $maskOptOut && $isOptedOut && $viewerUserId !== $user->id;

            $rows[] = [
                'rank' => 0,
                'user_id' => $user->id,
                'foto' => $shouldMask ? null : $user->foto,
                'employee_code' => $shouldMask ? 'MSN-***' : $user->employee_code,
                'display_name' => $shouldMask ? 'Karyawan Anonim (Opt-Out Privasi)' : $user->name,
                'raw_name' => $user->name,
                'division' => $user->division ?? 'Umum',
                'is_opted_out' => $isOptedOut,
                'scheduled_workdays' => $scheduledWorkdays,
                'excused_leave_days' => $excusedLeaveDays,
                'effective_workdays' => $effectiveWorkdays,
                'attended_days' => $attendedDays,
                'on_time_days' => $onTimeDays,
                'total_late_minutes' => $totalLateMinutes,
                'present_pct' => $presentPct,
            ];
        }

        // Sort: present_pct DESC -> total_late_minutes ASC -> on_time_days DESC (§5.7)
        usort($rows, function (array $a, array $b): int {
            if (abs($a['present_pct'] - $b['present_pct']) > 0.0001) {
                return $b['present_pct'] <=> $a['present_pct'];
            }
            if ($a['total_late_minutes'] !== $b['total_late_minutes']) {
                return $a['total_late_minutes'] <=> $b['total_late_minutes'];
            }
            if ($a['on_time_days'] !== $b['on_time_days']) {
                return $b['on_time_days'] <=> $a['on_time_days'];
            }

            return strcmp($a['raw_name'], $b['raw_name']);
        });

        foreach ($rows as $idx => &$r) {
            $r['rank'] = $idx + 1;
        }
        unset($r);

        return $rows;
    }

    /**
     * Resolve start & end Carbon dates for monthly, quarterly, or yearly period (§5.7).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function resolveDateRange(string $periodType, int $year, int $monthOrQuarter): array
    {
        if ($periodType === 'yearly') {
            $start = Carbon::createFromDate($year, 1, 1, 'Asia/Jakarta')->startOfYear();
            $end = $start->copy()->endOfYear();

            return [$start, $end];
        }

        if ($periodType === 'quarterly') {
            $quarter = max(1, min(4, $monthOrQuarter));
            $startMonth = (($quarter - 1) * 3) + 1;
            $start = Carbon::createFromDate($year, $startMonth, 1, 'Asia/Jakarta')->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();

            return [$start, $end];
        }

        $month = max(1, min(12, $monthOrQuarter));
        $start = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [$start, $end];
    }
}
