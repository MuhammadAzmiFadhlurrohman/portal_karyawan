<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use App\Modules\Attendance\Services\LeaveService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeAttendanceRecapController extends Controller
{
    /**
     * Display the employee attendance recap dashboard for HRD & Admin.
     * Replicates the layout in reference image 1.
     */
    public function index(Request $request, DeductionService $deductionService, LeaveService $leaveService): View
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['super_admin', 'admin', 'hrd'])) {
            abort(403, 'Akses rekap absensi karyawan hanya untuk HRD dan Administrator.');
        }

        $now = now('Asia/Jakarta');
        $year = (int) $request->integer('year', (int) $now->format('Y'));
        $month = (int) $request->integer('month', (int) $now->format('n'));
        $search = trim((string) $request->input('q', ''));

        // Query active employees
        $employeesQuery = User::query()
            ->where('is_active', true)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('division', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id')->all();

        // Calculate working days in selected month (count of distinct days where schedules exist or general work days)
        $scheduledDaysCount = MonthlySchedule::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->distinct('date')
            ->count('date');

        if ($scheduledDaysCount === 0) {
            $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;
            $scheduledDaysCount = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cDate = Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta');
                if (! $cDate->isSunday()) {
                    $scheduledDaysCount++;
                }
            }
        }

        // 1. Bulk Query Attendances for all employees this month
        $attendancesGrouped = Attendance::query()
            ->whereIn('user_id', $employeeIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('user_id');

        // 2. Bulk Query Approved Leaves for all employees
        $allLeaves = Leave::query()
            ->whereIn('user_id', $employeeIds)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get();

        $monthLeaves = $allLeaves->filter(function ($l) use ($month) {
            return Carbon::parse($l->start_date)->month == $month;
        });

        $leavesGroupedByUser = $allLeaves->groupBy('user_id');

        // 3. Bulk Query Leave Balances
        $balancesGrouped = \App\Models\LeaveBalance::query()
            ->whereIn('user_id', $employeeIds)
            ->where('year', $year)
            ->get()
            ->keyBy('user_id');

        // Summary metrics
        $totalEmployees = $employees->count();
        $totalPresentAll = 0;
        $totalCutiApprovedAll = $monthLeaves->where('is_alpa_conversion', false)->count();
        $totalPotonganAll = 0.0;

        // Rates
        $asOf = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->endOfMonth();
        $lateFeePerMinute = \App\Modules\Attendance\Services\SystemSettingService::getFloat('attendance.late_fee_per_minute', 250.0, $asOf);
        $dailyMaxLateFee = \App\Modules\Attendance\Services\SystemSettingService::getFloat('attendance.daily_max_late_fee', 50000.0, $asOf);
        $defaultAlpaFee = \App\Modules\Attendance\Services\SystemSettingService::getFloat('attendance.default_alpa_fee', 25000.0, $asOf);

        // Employee Cards Data
        $cards = [];
        foreach ($employees as $emp) {
            $userAttendances = $attendancesGrouped->get($emp->id, collect());
            $hadirCount = $userAttendances->where('status', 'present')->count();
            $terlambatCount = $userAttendances->where('status', 'late')->count();
            $alpaCount = $userAttendances->where('status', 'alpa')->count();

            // Calculate Fast Deductions
            $lateDeduction = 0.0;
            foreach ($userAttendances->where('status', 'late') as $lateAtt) {
                if ($lateAtt->late_minutes > 0) {
                    $lateDeduction += min($dailyMaxLateFee, round($lateAtt->late_minutes * $lateFeePerMinute, 2));
                }
            }

            $userAlpaRate = $emp->potongan_alpa !== null ? (float) $emp->potongan_alpa : $defaultAlpaFee;
            $alpaDeduction = round($alpaCount * $userAlpaRate, 2);
            $grandTotalPotongan = round($lateDeduction + $alpaDeduction, 2);

            // Leave Balance
            $userBal = $balancesGrouped->get($emp->id);
            $sisaCuti = $userBal ? max(0, (int) $userBal->remaining_days) : 12;

            // Cuti Terpakai in Year
            $userYearLeaves = $leavesGroupedByUser->get($emp->id, collect());
            $cutiTerpakai = (int) $userYearLeaves->sum('working_days');

            $totalPresentAll += ($hadirCount + $terlambatCount);
            $totalPotonganAll += $grandTotalPotongan;

            $cards[] = [
                'user' => $emp,
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'alpa' => $alpaCount,
                'sisa_cuti' => $sisaCuti,
                'cuti_terpakai' => $cutiTerpakai,
                'total_potongan' => $grandTotalPotongan,
                'alpa_potongan' => $alpaDeduction,
            ];
        }

        $avgKehadiran = $totalEmployees > 0 ? round($totalPresentAll / $totalEmployees, 1) : 0;

        return view('attendance.employee_recap.index', [
            'year' => $year,
            'month' => $month,
            'search' => $search,
            'scheduledDaysCount' => $scheduledDaysCount,
            'totalEmployees' => $totalEmployees,
            'avgKehadiran' => $avgKehadiran,
            'totalCutiApprovedAll' => $totalCutiApprovedAll,
            'totalPotonganAll' => $totalPotonganAll,
            'cards' => $cards,
        ]);
    }

    /**
     * Display the individual employee attendance detail page.
     * Replicates the layout in reference image 2.
     */
    public function show(Request $request, User $user, DeductionService $deductionService, LeaveService $leaveService): View
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['super_admin', 'admin', 'hrd'])) {
            abort(403, 'Akses detail absensi karyawan hanya untuk HRD dan Administrator.');
        }

        $now = now('Asia/Jakarta');
        $year = (int) $request->integer('year', (int) $now->format('Y'));
        $month = (int) $request->integer('month', (int) $now->format('n'));

        // Generate report
        $monthlyReport = $deductionService->calculateUserMonthlyReport($user, $year, $month);

        // Fetch attendances keyed by date Y-m-d
        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with(['shift', 'officeLocation'])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Schedules keyed by date
        $schedules = MonthlySchedule::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('shift')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Leaves for user
        $leaves = Leave::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get();

        $monthLeavesCount = $leaves->filter(function ($l) use ($month) {
            return Carbon::parse($l->start_date)->month == $month;
        })->count();

        // Metric stats
        $hadirCount = $attendances->where('status', 'present')->count();
        $terlambatCount = $attendances->where('status', 'late')->count();
        $alpaCount = (int) ($monthlyReport['total_alpa_days'] ?? 0);
        if ($alpaCount === 0) {
            $alpaCount = $attendances->where('status', 'alpa')->count();
        }

        $balance = $leaveService->getOrCreateBalance($user, $year);
        $sisaCuti = max(0, (int) ($balance->remaining_days ?? 0));
        $annualQuota = (int) ($balance->annual_quota ?? 12);
        $cutiTerpakaiAnnual = (int) $leaves->sum('working_days');

        $totalPotongan = (float) ($monthlyReport['grand_total_deduction'] ?? 0);
        $telatPotongan = (float) ($monthlyReport['total_late_deduction'] ?? 0);
        $alpaPotongan = (float) ($monthlyReport['total_alpa_deduction'] ?? 0);

        // Build Daily Items Table for the month
        $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;
        $dailyRows = [];

        // Daily item deduction mapping from $monthlyReport['daily_items']
        $deductionItemsByDate = [];
        foreach ($monthlyReport['daily_items'] ?? [] as $dItem) {
            if (isset($dItem['date'])) {
                $deductionItemsByDate[$dItem['date']] = $dItem;
            }
        }

        $alpaSequence = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $cDate = Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta');
            $dateStr = $cDate->format('Y-m-d');

            $att = $attendances->get($dateStr);
            $sched = $schedules->get($dateStr);
            $dItem = $deductionItemsByDate[$dateStr] ?? null;

            $isSunday = $cDate->isSunday();
            $dayName = $cDate->translatedFormat('l');
            $dateFormatted = $cDate->translatedFormat('d/m/Y');

            // Determine status and shift
            $shiftLabel = '-';
            if ($sched && $sched->shift) {
                $shiftLabel = $sched->shift->name;
            } elseif ($att && $att->shift) {
                $shiftLabel = $att->shift->name;
            } elseif ($isSunday) {
                $shiftLabel = 'Libur';
            }

            $status = 'Libur';
            $clockIn = '-';
            $clockOut = '-';
            $photoPath = null;
            $lateText = null;
            $potonganBadge = null;

            if ($att) {
                $status = $att->status; // present, late, alpa, leave, etc.
                if ($att->clock_in_at) {
                    $clockIn = $att->clock_in_at->timezone('Asia/Jakarta')->format('H:i:s');
                }
                if ($att->clock_out_at) {
                    $clockOut = $att->clock_out_at->timezone('Asia/Jakarta')->format('H:i:s');
                }
                if ($att->late_minutes > 0) {
                    $hours = intdiv($att->late_minutes, 60);
                    $mins = $att->late_minutes % 60;
                    $lateText = $hours > 0 ? "{$hours} jam {$mins} menit" : "{$mins} menit";
                }
                $photoPath = $att->photo_in_primary_path ?: $att->photo_out_path;
            } elseif ($isSunday) {
                $status = 'Libur';
            } elseif ($cDate->isPast() && ! $isSunday) {
                // If it was in the past and no attendance recorded
                if ($dItem && (float) $dItem['alpa_deduction'] > 0) {
                    $status = 'alpa';
                } else {
                    $status = $sched ? 'alpa' : 'Libur';
                }
            } else {
                $status = '-';
            }

            // Deductions badge
            if ($dItem) {
                $totD = (float) ($dItem['total_deduction'] ?? 0);
                if ($totD > 0) {
                    if ((float) $dItem['alpa_deduction'] > 0) {
                        $alpaSequence++;
                        $potonganBadge = [
                            'amount' => 'Rp ' . number_format((float) $dItem['alpa_deduction'], 0, ',', '.'),
                            'note' => "potongan alpa (alpa ke-{$alpaSequence})",
                            'type' => 'alpa',
                        ];
                    } elseif ((float) $dItem['late_deduction'] > 0) {
                        $potonganBadge = [
                            'amount' => 'Rp ' . number_format((float) $dItem['late_deduction'], 0, ',', '.'),
                            'note' => 'potongan terlambat',
                            'type' => 'late',
                        ];
                    }
                }
            }

            $dailyRows[] = [
                'day_name' => $dayName,
                'date_formatted' => $dateFormatted,
                'date_str' => $dateStr,
                'photo_path' => $photoPath,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'late_text' => $lateText,
                'shift_label' => $shiftLabel,
                'status' => $status,
                'potongan_badge' => $potonganBadge,
            ];
        }

        return view('attendance.employee_recap.show', [
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'hadirCount' => $hadirCount,
            'terlambatCount' => $terlambatCount,
            'alpaCount' => $alpaCount,
            'sisaCuti' => $sisaCuti,
            'annualQuota' => $annualQuota,
            'cutiTerpakaiAnnual' => $cutiTerpakaiAnnual,
            'monthLeavesCount' => $monthLeavesCount,
            'totalPotongan' => $totalPotongan,
            'telatPotongan' => $telatPotongan,
            'alpaPotongan' => $alpaPotongan,
            'dailyRows' => $dailyRows,
        ]);
    }
}

