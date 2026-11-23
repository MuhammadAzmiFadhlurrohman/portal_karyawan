<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Lembur;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Determine user dashboard scope based on their role:
     * - admin/super_admin/hrd/finance/ceo: company-wide
     * - atasan/atasan_*: their division and subordinates
     * - others: personal only
     */
    public static function determineScope(User $user): array
    {
        if ($user->hasRole(['super_admin', 'admin', 'hrd', 'finance', 'ceo'])) {
            return [
                'type' => 'company',
                'badge' => 'Company-Wide',
                'title' => 'Statistik Seluruh Perusahaan',
                'subtitle' => 'Data gabungan seluruh karyawan dan divisi PT Media Solusi Network',
                'user_ids' => null,
            ];
        }

        if ($user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi'])) {
            $division = $user->division;
            $subordinateIds = User::query()
                ->where(function ($q) use ($user, $division) {
                    $q->where('supervisor_id', $user->id);
                    if (! empty($division)) {
                        $q->orWhereRaw('LOWER(division) = ?', [strtolower($division)]);
                    }
                })
                ->pluck('id')
                ->push($user->id)
                ->unique()
                ->values()
                ->all();

            return [
                'type' => 'division',
                'badge' => 'Divisi ' . ($division ?: 'Operasional'),
                'title' => 'Statistik Divisi ' . ($division ?: 'Operasional'),
                'subtitle' => 'Data bawahan dan anggota tim di divisi Anda (' . count($subordinateIds) . ' Karyawan)',
                'user_ids' => $subordinateIds,
            ];
        }

        return [
            'type' => 'personal',
            'badge' => 'Pribadi',
            'title' => 'Statistik Pribadi Saya',
            'subtitle' => 'Ringkasan kehadiran, pengajuan, dan jam lembur akun Anda',
            'user_ids' => [$user->id],
        ];
    }

    /**
     * Calculate comprehensive statistics for the given scope
     */
    public function getStats(User $user): array
    {
        $scope = self::determineScope($user);
        $userIds = $scope['user_ids'];

        $now = now('Asia/Jakarta');
        $todayStr = $now->toDateString();
        $currentMonth = (int) $now->format('n');
        $currentYear = (int) $now->format('Y');

        // ----------------------------------------------------
        // 1. STATISTIK ABSENSI (Hari Ini & Bulan Ini)
        // ----------------------------------------------------
        $todayAttQuery = Attendance::query()->whereDate('date', $todayStr);
        if ($userIds !== null) {
            $todayAttQuery->whereIn('user_id', $userIds);
        }
        $todayAttendances = $todayAttQuery->get();

        $todayScheduledQuery = MonthlySchedule::query()->whereDate('date', $todayStr);
        if ($userIds !== null) {
            $todayScheduledQuery->whereIn('user_id', $userIds);
        }
        $todayScheduledCount = $todayScheduledQuery->count();
        if ($userIds !== null && $scope['type'] === 'personal') {
            $todayScheduledCount = max(1, $todayScheduledCount);
        }

        $todayPresent = $todayAttendances->where('status', 'present')->where('late_minutes', 0)->count();
        $todayLate = $todayAttendances->where('late_minutes', '>', 0)->count();
        $todayLeave = $todayAttendances->whereIn('status', ['leave', 'sick'])->count();
        $todayAttendedTotal = $todayPresent + $todayLate;
        $todayAbsent = max(0, $todayScheduledCount - ($todayAttendedTotal + $todayLeave));

        // Month Attendances
        $monthAttQuery = Attendance::query()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth);
        if ($userIds !== null) {
            $monthAttQuery->whereIn('user_id', $userIds);
        }
        $monthAttendances = $monthAttQuery->get();

        $monthPresentDays = $monthAttendances->whereIn('status', ['present', 'late'])->count();
        $monthOntimeDays = $monthAttendances->where('status', 'present')->where('late_minutes', 0)->count();
        $monthLateDays = $monthAttendances->where('late_minutes', '>', 0)->count();
        $monthAbsentDays = $monthAttendances->where('status', 'alpa')->count();
        $monthLeaveDays = $monthAttendances->whereIn('status', ['leave', 'sick'])->count();
        $monthLateMinutes = (int) $monthAttendances->sum('late_minutes');

        // Total potongan bulan ini (untuk personal karyawan / scope)
        $monthTotalDeduction = 0.0;
        if ($scope['type'] === 'personal') {
            try {
                $deductionReport = app(\App\Modules\Attendance\Services\DeductionService::class)->calculateUserMonthlyReport($user, $currentYear, $currentMonth);
                $monthTotalDeduction = (float) ($deductionReport['grand_total_deduction'] ?? 0);
            } catch (\Throwable $e) {
                $monthTotalDeduction = 0.0;
            }
        }

        $monthSchedQuery = MonthlySchedule::query()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth);
        if ($userIds !== null) {
            $monthSchedQuery->whereIn('user_id', $userIds);
        }
        $monthScheduledDays = max(1, $monthSchedQuery->count());

        $attendanceRate = round(($monthPresentDays / $monthScheduledDays) * 100, 1);
        $attendanceRate = min(100.0, max(0.0, $attendanceRate));

        // ----------------------------------------------------
        // 2. STATISTIK PENGAJUAN (Dana & Barang)
        // ----------------------------------------------------
        $pengajuanQuery = Pengajuan::query()
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth);
        if ($userIds !== null) {
            $pengajuanQuery->whereIn('user_id', $userIds);
        }
        $pengajuans = $pengajuanQuery->get();

        $totalPengajuan = $pengajuans->count();
        $nominalTotalPengajuan = (float) $pengajuans->sum('nominal_total');

        $approvedStatuses = ['disetujui', 'ditransfer', 'terealisasi', 'dicairkan', 'menunggu_bukti', 'selesai'];
        $pendingStatuses = ['draft', 'menunggu_atasan', 'menunggu_finance', 'menunggu_executive', 'menunggu_direksi'];

        $pengajuanPending = $pengajuans->whereIn('status', $pendingStatuses)->count();
        $pengajuanApproved = $pengajuans->whereIn('status', $approvedStatuses)->count();
        $pengajuanRejected = $pengajuans->where('status', 'ditolak')->count();

        $nominalDisetujui = (float) $pengajuans->whereIn('status', $approvedStatuses)->sum('nominal_total');
        $nominalTerealisasi = (float) $pengajuans->whereIn('status', ['terealisasi', 'selesai'])->sum('nominal_realisasi');

        // ----------------------------------------------------
        // 3. STATISTIK LEMBUR
        // ----------------------------------------------------
        $lemburQuery = Lembur::query()
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth);
        if ($userIds !== null) {
            $lemburQuery->whereIn('user_id', $userIds);
        }
        $lemburs = $lemburQuery->get();

        $totalLembur = $lemburs->count();
        $totalDurasiMenit = (int) $lemburs->whereIn('status', ['approved_atasan', 'approved', 'paid'])->sum('durasi_menit');
        $totalJamLembur = round($totalDurasiMenit / 60, 1);

        $lemburPending = $lemburs->where('status', 'pending')->count();
        $lemburApproved = $lemburs->whereIn('status', ['approved_atasan', 'approved', 'paid'])->count();
        $lemburPaid = $lemburs->where('status', 'paid')->count();
        $lemburRejected = $lemburs->where('status', 'rejected')->count();

        // ----------------------------------------------------
        // 4. DATA GRAFIK 1: TREN KEHADIRAN & JAM KERJA 1 BULAN (30 HARI TERAKHIR)
        // ----------------------------------------------------
        $isHrdOrAdmin = $user->hasRole(['super_admin', 'admin', 'hrd', 'ceo']);

        $trendDays = 30; // 1 Bulan (30 Hari Terakhir)
        $trendDates = [];
        $trendLabels = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $dt = $now->copy()->subDays($i);
            $trendDates[] = $dt->toDateString();
            $trendLabels[] = $dt->locale('id')->isoFormat('D MMM');
        }

        // A. DATA UNTUK HRD & ADMIN: TREN KEHADIRAN SELURUH KARYAWAN (1 BULAN)
        $companyAttQuery = Attendance::query()->whereIn('date', $trendDates);
        $companyAttRecords = $companyAttQuery->get()->groupBy(fn ($a) => Carbon::parse($a->date)->toDateString());

        $chartAttendanceCompany = [
            'labels' => $trendLabels,
            'present' => [],
            'late' => [],
            'leave' => [],
            'absent' => [],
        ];

        foreach ($trendDates as $dtStr) {
            $records = $companyAttRecords->get($dtStr, collect());
            $chartAttendanceCompany['present'][] = $records->where('status', 'present')->where('late_minutes', 0)->count();
            $chartAttendanceCompany['late'][] = $records->where('late_minutes', '>', 0)->count();
            $chartAttendanceCompany['leave'][] = $records->whereIn('status', ['leave', 'sick'])->count();
            $chartAttendanceCompany['absent'][] = $records->where('status', 'alpa')->count();
        }

        // B. DATA UNTUK KARYAWAN: JAM KERJA, LEMBUR & KETERLAMBATAN PRIBADI (1 BULAN)
        $personalAttQuery = Attendance::query()
            ->where('user_id', $user->id)
            ->whereIn('date', $trendDates);
        $personalAttRecords = $personalAttQuery->get()->keyBy(fn ($a) => Carbon::parse($a->date)->toDateString());

        $personalLemburQuery = Lembur::query()
            ->where('user_id', $user->id)
            ->whereIn('tanggal', $trendDates)
            ->whereIn('status', ['approved_atasan', 'approved', 'paid']);
        $personalLemburs = $personalLemburQuery->get()->groupBy(fn ($l) => Carbon::parse($l->tanggal)->toDateString());

        $chartPersonal = [
            'labels' => $trendLabels,
            'work_hours' => [],
            'overtime_hours' => [],
            'late_minutes' => [],
            'clock_in_times' => [],
            'statuses' => [],
        ];

        $totalPersonalWorkHours = 0.0;
        $totalPersonalOvertimeHours = 0.0;
        $totalPersonalLateMinutes = 0;

        foreach ($trendDates as $dtStr) {
            $att = $personalAttRecords->get($dtStr);
            $lemburGroup = $personalLemburs->get($dtStr, collect());
            $otHours = round($lemburGroup->sum('durasi_menit') / 60, 1);

            $workHours = 0.0;
            $lateMins = 0;
            $clockInTime = '-';
            $statusStr = 'Libur / Tidak Hadir';

            if ($att) {
                if (in_array($att->status, ['present', 'late'])) {
                    if ($att->work_duration_minutes > 0) {
                        $workHours = round($att->work_duration_minutes / 60, 1);
                    } else {
                        $workHours = 8.0;
                    }
                    $statusStr = $att->late_minutes > 0 ? "Terlambat ({$att->late_minutes} mnt)" : 'Tepat Waktu';
                } elseif (in_array($att->status, ['leave', 'sick'])) {
                    $statusStr = 'Izin / Cuti';
                } elseif ($att->status === 'alpa') {
                    $statusStr = 'Alpa';
                }

                $lateMins = (int) $att->late_minutes;
                $clockInTime = $att->clock_in_at ? Carbon::parse($att->clock_in_at)->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-';
            }

            $chartPersonal['work_hours'][] = $workHours;
            $chartPersonal['overtime_hours'][] = $otHours;
            $chartPersonal['late_minutes'][] = $lateMins;
            $chartPersonal['clock_in_times'][] = $clockInTime;
            $chartPersonal['statuses'][] = $statusStr;

            $totalPersonalWorkHours += $workHours;
            $totalPersonalOvertimeHours += $otHours;
            $totalPersonalLateMinutes += $lateMins;
        }

        $chartPersonal['summary'] = [
            'total_work_hours' => round($totalPersonalWorkHours, 1),
            'total_overtime_hours' => round($totalPersonalOvertimeHours, 1),
            'total_late_minutes' => $totalPersonalLateMinutes,
        ];

        // ----------------------------------------------------
        // 5. DATA GRAFIK 2: KOMPOSISI PENGAJUAN & LEMBUR
        // ----------------------------------------------------
        $chartPengajuan = [
            'labels' => ['Disetujui/Cair', 'Menunggu Persetujuan', 'Ditolak'],
            'data' => [$pengajuanApproved, $pengajuanPending, $pengajuanRejected],
            'colors' => ['#0D9488', '#F59E0B', '#EF4444'],
        ];

        // ----------------------------------------------------
        // 6. DATA GRAFIK 3: TREN JAM LEMBUR 7 HARI TERAKHIR
        // ----------------------------------------------------
        $trendLemburQuery = Lembur::query()->whereIn('tanggal', $trendDates);
        if ($userIds !== null) {
            $trendLemburQuery->whereIn('user_id', $userIds);
        }
        $trendLemburRecords = $trendLemburQuery->get()->groupBy(fn ($l) => Carbon::parse($l->tanggal)->toDateString());

        $chartLembur = [
            'labels' => $trendLabels,
            'hours' => [],
            'count' => [],
        ];

        foreach ($trendDates as $dtStr) {
            $lRecords = $trendLemburRecords->get($dtStr, collect());
            $mins = (int) $lRecords->whereIn('status', ['approved_atasan', 'approved', 'paid'])->sum('durasi_menit');
            $chartLembur['hours'][] = round($mins / 60, 1);
            $chartLembur['count'][] = $lRecords->count();
        }

        // Recent Activity Lists
        $recentPengajuan = Pengajuan::query()
            ->with(['user'])
            ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds))
            ->latest()
            ->take(5)
            ->get();

        $recentLembur = Lembur::query()
            ->with(['user'])
            ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds))
            ->latest()
            ->take(5)
            ->get();

        return [
            'scope' => $scope,
            'absensi' => [
                'today_present' => $todayPresent,
                'today_late' => $todayLate,
                'today_leave' => $todayLeave,
                'today_absent' => $todayAbsent,
                'today_total' => $todayAttendedTotal,
                'month_rate' => $attendanceRate,
                'month_present_days' => $monthPresentDays,
                'month_ontime_days' => $monthOntimeDays,
                'month_late_days' => $monthLateDays,
                'month_absent_days' => $monthAbsentDays,
                'month_leave_days' => $monthLeaveDays,
                'month_late_minutes' => $monthLateMinutes,
                'month_total_deduction' => $monthTotalDeduction,
            ],
            'pengajuan' => [
                'total' => $totalPengajuan,
                'nominal_total' => $nominalTotalPengajuan,
                'pending' => $pengajuanPending,
                'approved' => $pengajuanApproved,
                'rejected' => $pengajuanRejected,
                'nominal_disetujui' => $nominalDisetujui,
                'nominal_realisasi' => $nominalTerealisasi,
            ],
            'lembur' => [
                'total' => $totalLembur,
                'total_hours' => $totalJamLembur,
                'pending' => $lemburPending,
                'approved' => $lemburApproved,
                'paid' => $lemburPaid,
                'rejected' => $lemburRejected,
            ],
            'charts' => [
                'is_admin_or_hrd' => $isHrdOrAdmin,
                'attendance' => $isHrdOrAdmin ? $chartAttendanceCompany : $chartAttendanceCompany,
                'company_attendance' => $chartAttendanceCompany,
                'personal_attendance' => $chartPersonal,
                'pengajuan' => $chartPengajuan,
                'lembur' => $chartLembur,
            ],
            'recent' => [
                'pengajuan' => $recentPengajuan,
                'lembur' => $recentLembur,
            ],
        ];
    }

    /**
     * Get monthly attendance calendar for the specified user
     */
    public function getMonthlyCalendar(User $user, ?int $year = null, ?int $month = null): array
    {
        $now = now('Asia/Jakarta');
        $year = $year ?: (int) $now->format('Y');
        $month = $month ?: (int) $now->format('n');
        $todayStr = $now->toDateString();

        $startOfMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfDay();
        $daysInMonth = $startOfMonth->daysInMonth;
        $monthName = $startOfMonth->locale('id')->translatedFormat('F Y');

        // ISO day of week: 1 (Senin/Mon) ... 7 (Minggu/Sun)
        // If day 1 is Monday (1), leading blank slots = 0
        // If day 1 is Sunday (7), leading blank slots = 6
        $emptySlots = $startOfMonth->dayOfWeekIso - 1;

        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($a) => $a->date instanceof Carbon ? $a->date->toDateString() : substr((string) $a->date, 0, 10));

        $schedules = MonthlySchedule::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($s) => $s->date instanceof Carbon ? $s->date->toDateString() : substr((string) $s->date, 0, 10));

        $holidays = NationalHoliday::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($h) => $h->date instanceof Carbon ? $h->date->toDateString() : substr((string) $h->date, 0, 10));

        $approvedLeaves = Leave::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($year, $month) {
                $q->whereYear('start_date', $year)->whereMonth('start_date', $month)
                    ->orWhere(function ($q2) use ($year, $month) {
                        $q2->whereYear('end_date', $year)->whereMonth('end_date', $month);
                    });
            })
            ->get();

        $presentCount = 0;
        $days = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta')->startOfDay();
            $dateStr = $dateObj->toDateString();
            $dayOfWeek = $dateObj->dayOfWeekIso; // 1 = Monday .. 7 = Sunday
            $isWeekend = ($dayOfWeek >= 6); // 6 = Saturday, 7 = Sunday
            $isToday = ($dateStr === $todayStr);
            $isFuture = ($dateStr > $todayStr);

            $att = $attendances->get($dateStr);
            $sched = $schedules->get($dateStr);
            $holiday = $holidays->get($dateStr);

            $hasApprovedLeave = $approvedLeaves->first(function ($l) use ($dateStr) {
                $s = $l->start_date instanceof Carbon ? $l->start_date->toDateString() : substr((string) $l->start_date, 0, 10);
                $e = $l->end_date instanceof Carbon ? $l->end_date->toDateString() : substr((string) $l->end_date, 0, 10);
                return $dateStr >= $s && $dateStr <= $e;
            });

            // Day off conditions: Holiday OR Shift set to Day Off OR Weekend (unless scheduled for piket)
            $isDayOff = $holiday !== null
                || ($sched && $sched->shift && $sched->shift->is_day_off)
                || ($isWeekend && (!$sched || !$sched->shift || $sched->shift->is_day_off || !$sched->shift->is_piket));

            if ($isFuture) {
                $status = 'future';
                $label = 'Belum terlewat';
            } elseif ($att && in_array(strtolower((string) $att->status), ['present', 'late', 'hadir', 'terlambat'])) {
                $isLate = ($att->late_minutes > 0) || (strtolower((string) $att->status) === 'late');
                if ($isLate) {
                    $status = 'late';
                    $label = 'Terlambat (' . $att->late_minutes . ' menit)';
                } else {
                    $status = 'present';
                    $label = 'Hadir';
                }
                $presentCount++;
            } elseif ($hasApprovedLeave) {
                $status = 'off';
                $label = 'Cuti / Izin Disetujui';
            } elseif ($isDayOff) {
                $status = 'off';
                $label = $holiday ? 'Libur: ' . $holiday->name : 'Libur Akhir Pekan / Off';
            } else {
                $status = 'absent';
                $label = 'Alpa (Tidak Hadir)';
            }

            $days[] = [
                'day' => $d,
                'date' => $dateStr,
                'status' => $status,
                'label' => $label,
                'is_today' => $isToday,
                'is_future' => $isFuture,
                'date_formatted' => $dateObj->locale('id')->isoFormat('dddd, D MMMM Y'),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => $monthName,
            'days_in_month' => $daysInMonth,
            'empty_slots' => $emptySlots,
            'present_count' => $presentCount,
            'days' => $days,
        ];
    }
}

