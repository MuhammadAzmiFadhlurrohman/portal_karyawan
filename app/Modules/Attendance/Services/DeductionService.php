<?php

namespace App\Modules\Attendance\Services;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\DeductionAdjustment;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\PeriodClosing;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeductionService
{
    /**
     * Calculate daily deduction for a single user and date using historical `effective_from` rates (§5.5, AC-09).
     *
     * @return array{
     *   date: string,
     *   shift_code: string,
     *   attendance_id: int|null,
     *   status: string,
     *   attendance_type: string,
     *   clock_in: string|null,
     *   clock_out: string|null,
     *   late_minutes: int,
     *   late_rate_per_minute: string,
     *   late_deduction: string,
     *   alpa_deduction: string,
     *   total_deduction: string,
     *   notes: string|null
     * }
     */
    public function calculateDailyUserDeduction(User $user, Carbon $date): array
    {
        $dateStr = $date->toDateString();

        $schedule = MonthlySchedule::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->first();

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $dateStr)
            ->first();

        $approvedLeave = Leave::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->first();

        // Lookup rates effective as of the end of that day (AC-09)
        $asOf = $date->copy()->endOfDay();
        $lateFeePerMinute = SystemSettingService::getFloat('attendance.late_fee_per_minute', 250.0, $asOf);
        $dailyMaxLateFee = SystemSettingService::getFloat('attendance.daily_max_late_fee', 50000.0, $asOf);
        $defaultAlpaFee = SystemSettingService::getFloat('attendance.default_alpa_fee', 25000.0, $asOf);

        $lateDeduction = 0.0;
        $alpaDeduction = 0.0;
        $lateMinutes = $attendance ? (int) $attendance->late_minutes : 0;
        $status = $attendance ? $attendance->status : ($approvedLeave ? 'leave' : 'unscheduled');
        $attendanceType = $attendance ? $attendance->attendance_type : 'office';

        if ($attendance && $attendance->status === 'late' && $attendance->attendance_type !== 'remote' && $lateMinutes > 0) {
            $lateDeduction = min($dailyMaxLateFee, round($lateMinutes * $lateFeePerMinute, 2));
        }

        $isAlpa = $attendance && $attendance->status === 'absent' && ! $approvedLeave;
        if ($isAlpa) {
            $userAlpaRate = $user->potongan_alpa !== null ? (float) $user->potongan_alpa : $defaultAlpaFee;
            $alpaDeduction = round($userAlpaRate, 2);
        }

        $totalDeduction = round($lateDeduction + $alpaDeduction, 2);

        return [
            'date' => $dateStr,
            'shift_code' => $schedule && $schedule->shift ? $schedule->shift->code : '-',
            'attendance_id' => $attendance?->id,
            'status' => $status,
            'attendance_type' => $attendanceType,
            'clock_in' => $attendance && $attendance->clock_in_at ? $attendance->clock_in_at->timezone('Asia/Jakarta')->format('H:i') : null,
            'clock_out' => $attendance && $attendance->clock_out_at ? $attendance->clock_out_at->timezone('Asia/Jakarta')->format('H:i') : null,
            'late_minutes' => $lateMinutes,
            'late_rate_per_minute' => number_format($lateFeePerMinute, 2, '.', ''),
            'late_deduction' => number_format($lateDeduction, 2, '.', ''),
            'alpa_deduction' => number_format($alpaDeduction, 2, '.', ''),
            'total_deduction' => number_format($totalDeduction, 2, '.', ''),
            'notes' => $approvedLeave
                ? ($approvedLeave->is_alpa_conversion ? 'Alpa dikonversi ke kuota cuti (§5.4)' : "Cuti ({$approvedLeave->leave_type})")
                : ($isAlpa ? 'Alpa (Potong Gaji Karyawan)' : $attendance?->notes),
        ];
    }

    /**
     * Calculate full monthly deduction report for a user (§5.5, AC-09).
     * If the month is locked in `period_closings`, returns the frozen snapshot so future rate changes never alter closed months!
     *
     * @return array{
     *   year: int,
     *   month: int,
     *   is_locked: bool,
     *   total_late_minutes: int,
     *   total_alpa_days: int,
     *   total_late_deduction: string,
     *   total_alpa_deduction: string,
     *   total_adjustment_amount: string,
     *   grand_total_deduction: string,
     *   daily_items: array<int, array<string, mixed>>,
     *   adjustments: array<int, array<string, mixed>>
     * }
     */
    public function calculateUserMonthlyReport(User $user, int $year, int $month): array
    {
        $closing = PeriodClosing::query()
            ->where('year', $year)
            ->where('month', $month)
            ->where('is_locked', true)
            ->first();

        if ($closing && is_array($closing->snapshot_data) && isset($closing->snapshot_data['users'][(string) $user->id])) {
            /** @var array<string, mixed> $frozen */
            $frozen = $closing->snapshot_data['users'][(string) $user->id];

            return [
                'year' => $year,
                'month' => $month,
                'is_locked' => true,
                'total_late_minutes' => (int) ($frozen['total_late_minutes'] ?? 0),
                'total_alpa_days' => (int) ($frozen['total_alpa_days'] ?? 0),
                'total_late_deduction' => (string) ($frozen['total_late_deduction'] ?? '0.00'),
                'total_alpa_deduction' => (string) ($frozen['total_alpa_deduction'] ?? '0.00'),
                'total_adjustment_amount' => (string) ($frozen['total_adjustment_amount'] ?? '0.00'),
                'grand_total_deduction' => (string) ($frozen['grand_total_deduction'] ?? '0.00'),
                'daily_items' => is_array($frozen['daily_items'] ?? null) ? $frozen['daily_items'] : [],
                'adjustments' => is_array($frozen['adjustments'] ?? null) ? $frozen['adjustments'] : [],
            ];
        }

        // Sync alpa conversions for this user and month up to 3 days limit & annual balance
        app(LeaveService::class)->syncUserAlpaForMonth($user, $year, $month);

        $startDate = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $dailyItems = [];
        $totalLateMinutes = 0;
        $totalAlpaDays = 0;
        $totalLateDeduction = 0.0;
        $totalAlpaDeduction = 0.0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta');
            $item = $this->calculateDailyUserDeduction($user, $currentDate);

            // Include day in daily breakdown if scheduled, has attendance, or has deduction
            if ($item['shift_code'] !== '-' || $item['attendance_id'] !== null || ((float) $item['total_deduction']) > 0) {
                $dailyItems[] = $item;
                $totalLateMinutes += $item['late_minutes'];
                if (((float) $item['alpa_deduction']) > 0) {
                    $totalAlpaDays++;
                }
                $totalLateDeduction = round($totalLateDeduction + (float) $item['late_deduction'], 2);
                $totalAlpaDeduction = round($totalAlpaDeduction + (float) $item['alpa_deduction'], 2);
            }
        }

        $adjustmentRows = DeductionAdjustment::query()
            ->where('user_id', $user->id)
            ->where('applied_year', $year)
            ->where('applied_month', $month)
            ->get();

        $totalAdjustment = 0.0;
        $adjustmentsList = [];
        foreach ($adjustmentRows as $adj) {
            $amt = (float) $adj->amount;
            $totalAdjustment = round($totalAdjustment + $amt, 2);
            $adjustmentsList[] = [
                'id' => $adj->id,
                'source_period' => sprintf('%04d-%02d', $adj->source_year, $adj->source_month),
                'amount' => number_format($amt, 2, '.', ''),
                'description' => $adj->description,
            ];
        }

        $grandTotal = max(0.0, round($totalLateDeduction + $totalAlpaDeduction + $totalAdjustment, 2));

        return [
            'year' => $year,
            'month' => $month,
            'is_locked' => false,
            'total_late_minutes' => $totalLateMinutes,
            'total_alpa_days' => $totalAlpaDays,
            'total_late_deduction' => number_format($totalLateDeduction, 2, '.', ''),
            'total_alpa_deduction' => number_format($totalAlpaDeduction, 2, '.', ''),
            'total_adjustment_amount' => number_format($totalAdjustment, 2, '.', ''),
            'grand_total_deduction' => number_format($grandTotal, 2, '.', ''),
            'daily_items' => $dailyItems,
            'adjustments' => $adjustmentsList,
        ];
    }

    /**
     * Get 6-month deduction trend for a user (§5.5).
     *
     * @return array<int, array{period: string, label: string, late_deduction: string, alpa_deduction: string, grand_total: string}>
     */
    public function getSixMonthTrend(User $user, int $anchorYear, int $anchorMonth): array
    {
        $anchor = Carbon::createFromDate($anchorYear, $anchorMonth, 1, 'Asia/Jakarta');
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $target = $anchor->copy()->subMonths($i);
            $y = (int) $target->format('Y');
            $m = (int) $target->format('n');
            $report = $this->calculateUserMonthlyReport($user, $y, $m);

            $trend[] = [
                'period' => sprintf('%04d-%02d', $y, $m),
                'label' => $target->translatedFormat('M Y'),
                'late_deduction' => $report['total_late_deduction'],
                'alpa_deduction' => $report['total_alpa_deduction'],
                'grand_total' => $report['grand_total_deduction'],
            ];
        }

        return $trend;
    }

    /**
     * Lock a monthly deduction period (`period_closings`) by HRD so future setting changes do not alter it (§5.5, AC-09).
     */
    public function lockMonthlyPeriod(int $year, int $month, User $actor, ?string $notes = null): PeriodClosing
    {
        $users = User::query()->where('is_active', true)->get();

        $usersSnapshot = [];
        $sumLate = 0.0;
        $sumAlpa = 0.0;
        $sumAdjustment = 0.0;

        // First ensure any existing lock is ignored while generating fresh snapshot
        PeriodClosing::query()->where('year', $year)->where('month', $month)->delete();

        foreach ($users as $u) {
            $report = $this->calculateUserMonthlyReport($u, $year, $month);
            $usersSnapshot[(string) $u->id] = $report;
            $sumLate = round($sumLate + (float) $report['total_late_deduction'], 2);
            $sumAlpa = round($sumAlpa + (float) $report['total_alpa_deduction'], 2);
            $sumAdjustment = round($sumAdjustment + (float) $report['total_adjustment_amount'], 2);
        }

        $closing = PeriodClosing::query()->create([
            'year' => $year,
            'month' => $month,
            'is_locked' => true,
            'total_late_deduction' => number_format($sumLate, 2, '.', ''),
            'total_alpa_deduction' => number_format($sumAlpa, 2, '.', ''),
            'total_adjustment_amount' => number_format($sumAdjustment, 2, '.', ''),
            'snapshot_data' => [
                'locked_at' => now()->toIso8601String(),
                'users' => $usersSnapshot,
            ],
            'closed_by' => $actor->id,
            'closed_at' => now(),
            'notes' => $notes ?? "Penutupan periode potongan bulanan {$year}-{$month} oleh HRD (§5.5, AC-09).",
        ]);

        AuditLogger::record(
            action: 'deduction.period_locked',
            auditable: $closing,
            newValues: [
                'year' => $year,
                'month' => $month,
                'total_late_deduction' => number_format($sumLate, 2, '.', ''),
                'total_alpa_deduction' => number_format($sumAlpa, 2, '.', ''),
            ],
            reason: $closing->notes ?? 'Penguncian periode potongan bulanan',
            actorId: $actor->id
        );

        return $closing;
    }

    /**
     * Submit an Attendance Status Correction request within the configured window (default 7 calendar days, §5.6).
     *
     * @param  array{
     *   date: string,
     *   requested_status: string,
     *   requested_attendance_type?: string,
     *   requested_clock_in_time?: string|null,
     *   requested_clock_out_time?: string|null,
     *   reason: string
     * }  $payload
     */
    public function submitCorrection(User $user, array $payload, ?UploadedFile $attachment = null): AttendanceCorrection
    {
        $targetDate = Carbon::parse($payload['date'], 'Asia/Jakarta')->startOfDay();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($targetDate->gt($today)) {
            throw new InvalidArgumentException('Koreksi absensi tidak dapat diajukan untuk tanggal di masa depan.');
        }

        $windowDays = SystemSettingService::getInt('attendance.correction_window_days', 7);
        $daysDiff = (int) $targetDate->diffInDays($today);

        // Check if there is an active Alpa conversion dispute window still open (§5.4)
        $hasActiveDisputeWindow = Leave::query()
            ->where('user_id', $user->id)
            ->where('is_alpa_conversion', true)
            ->where('status', 'approved')
            ->whereDate('start_date', $targetDate->toDateString())
            ->where('dispute_deadline_at', '>=', now())
            ->exists();

        if ($daysDiff > $windowDays && ! $hasActiveDisputeWindow) {
            throw new InvalidArgumentException(
                "Pengajuan koreksi absensi melewati batas maksimal {$windowDays} hari kalender sejak tanggal kejadian (§5.6)."
            );
        }

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $targetDate->toDateString())
            ->first();

        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('corrections/'.$user->id, 'private');
        }

        $correction = AttendanceCorrection::query()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance?->id,
            'date' => $targetDate->toDateString(),
            'requested_status' => $payload['requested_status'],
            'requested_attendance_type' => $payload['requested_attendance_type'] ?? 'office',
            'requested_clock_in_time' => $payload['requested_clock_in_time'] ?? '08:00:00',
            'requested_clock_out_time' => $payload['requested_clock_out_time'] ?? '17:00:00',
            'reason' => $payload['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending_supervisor',
        ]);

        AuditLogger::record(
            action: 'attendance.correction_submitted',
            auditable: $correction,
            newValues: [
                'date' => $targetDate->toDateString(),
                'requested_status' => $correction->requested_status,
                'requested_clock_in_time' => $correction->requested_clock_in_time,
            ],
            reason: $payload['reason'],
            actorId: $user->id
        );

        return $correction;
    }

    /**
     * Step 1 Approval of Attendance Correction by Supervisor (§5.6).
     * Langsung mengeksekusi pembaruan Attendance agar absensi karyawan berubah sesuai pengajuan.
     */
    public function approveCorrectionBySupervisor(AttendanceCorrection $correction, User $actor, ?LeaveService $leaveService = null): AttendanceCorrection
    {
        if ($correction->user_id === $actor->id) {
            throw new InvalidArgumentException('Pengaju tidak boleh menyetujui koreksi absensinya sendiri (AC-10).');
        }

        if ($correction->status === 'approved') {
            return $correction;
        }

        if (! in_array($correction->status, ['pending_supervisor', 'pending_hrd'], true)) {
            throw new InvalidArgumentException("Koreksi absensi tidak dalam status menunggu verifikasi (status: {$correction->status}).");
        }

        $leaveService = $leaveService ?? app(LeaveService::class);
        $updated = $this->approveCorrectionByHrd($correction, $actor, $leaveService);
        $updated->update([
            'supervisor_approved_by' => $actor->id,
            'supervisor_approved_at' => now(),
        ]);

        return $updated;
    }

    /**
     * Reject Attendance Correction by Supervisor or HRD.
     */
    public function rejectCorrection(AttendanceCorrection $correction, User $actor, string $reason): AttendanceCorrection
    {
        if ($correction->user_id === $actor->id) {
            throw new InvalidArgumentException('Pengaju tidak boleh menolak koreksi absensinya sendiri.');
        }

        if (! in_array($correction->status, ['pending_supervisor', 'pending_hrd'], true)) {
            throw new InvalidArgumentException("Koreksi absensi sudah diproses (status: {$correction->status}).");
        }

        $correction->update([
            'status' => 'rejected',
            'rejected_by' => $actor->id,
            'rejected_reason' => $reason,
        ]);

        AuditLogger::record(
            action: 'attendance.correction_rejected',
            auditable: $correction,
            newValues: ['status' => 'rejected', 'rejected_reason' => $reason],
            reason: $reason,
            actorId: $actor->id
        );

        return $correction;
    }

    /**
     * Step 2 Final Approval of Attendance Correction by HRD (§5.5, §5.6, AC-09):
     * - Updates/creates Attendance with `source = 'correction'`
     * - Records old & new values in `audit_logs`
     * - Reverses any `alpa_conversion` leave on that date and restores leave balance
     * - If the target period is already locked in `period_closings`, creates a `DeductionAdjustment` in the next open period!
     */
    public function approveCorrectionByHrd(AttendanceCorrection $correction, User $actor, LeaveService $leaveService): AttendanceCorrection
    {
        if ($correction->user_id === $actor->id) {
            throw new InvalidArgumentException('Pengaju tidak boleh menyetujui koreksi absensinya sendiri (AC-10).');
        }

        if ($correction->status === 'approved') {
            $correction->update([
                'hrd_approved_by' => $actor->id,
                'hrd_approved_at' => now(),
            ]);

            return $correction;
        }

        if (! in_array($correction->status, ['pending_supervisor', 'pending_hrd'], true)) {
            throw new InvalidArgumentException("Koreksi absensi sudah diproses (status: {$correction->status}).");
        }

        return DB::transaction(function () use ($correction, $actor, $leaveService): AttendanceCorrection {
            /** @var User $employee */
            $employee = User::query()->findOrFail($correction->user_id);
            $dateObj = $correction->date->copy()->startOfDay();
            $dateStr = $dateObj->toDateString();
            $year = (int) $dateObj->format('Y');
            $month = (int) $dateObj->format('n');

            // 1. Capture pre-correction daily deduction & old attendance state
            $oldDailyDeduction = (float) $this->calculateDailyUserDeduction($employee, $dateObj)['total_deduction'];

            $attendance = Attendance::query()
                ->where('user_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            $oldAttendanceValues = $attendance ? [
                'status' => $attendance->status,
                'source' => $attendance->source,
                'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                'late_minutes' => $attendance->late_minutes,
            ] : ['status' => 'none'];

            // 2. Reverse any Alpa-to-Leave conversion on that date (§5.4)
            $alpaLeave = Leave::query()
                ->where('user_id', $employee->id)
                ->where('is_alpa_conversion', true)
                ->where('status', 'approved')
                ->whereDate('start_date', $dateStr)
                ->first();

            if ($alpaLeave) {
                $alpaLeave->update([
                    'status' => 'reversed',
                    'cancel_reason' => 'Dibatalkan otomatis karena koreksi absensi disetujui (§5.4, §5.6).',
                ]);

                $balance = $leaveService->getOrCreateBalance($employee, $year);
                $balance->update([
                    'alpa_deducted_days' => max(0, $balance->alpa_deducted_days - 1),
                    'remaining_days' => $balance->remaining_days + 1,
                ]);
            }

            // 3. Calculate corrected late_minutes based on schedule shift
            $schedule = MonthlySchedule::query()
                ->with('shift')
                ->where('user_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            $clockInTimeStr = $correction->requested_clock_in_time ?? '08:00:00';
            $clockOutTimeStr = $correction->requested_clock_out_time ?? '17:00:00';
            $clockInAt = Carbon::parse($dateStr.' '.$clockInTimeStr, 'Asia/Jakarta');
            $clockOutAt = Carbon::parse($dateStr.' '.$clockOutTimeStr, 'Asia/Jakarta');

            $lateMinutes = 0;
            $finalStatus = $correction->requested_status;

            if ($schedule && $schedule->shift && ! $schedule->shift->is_day_off && $schedule->shift->start_time) {
                $shiftStart = Carbon::parse($dateStr.' '.$schedule->shift->start_time, 'Asia/Jakarta');
                $deadline = $shiftStart->copy()->addMinutes((int) $schedule->shift->late_tolerance_minutes);
                if ($clockInAt->gt($deadline)) {
                    $lateMinutes = (int) $shiftStart->diffInMinutes($clockInAt);
                    $finalStatus = 'late';
                } else {
                    $finalStatus = 'present';
                }
            }

            if ($attendance) {
                $attendance->update([
                    'status' => $finalStatus,
                    'attendance_type' => $correction->requested_attendance_type,
                    'source' => 'correction',
                    'clock_in_at' => $clockInAt->utc(),
                    'clock_out_at' => $clockOutAt->utc(),
                    'late_minutes' => $lateMinutes,
                    'reviewed_by' => $actor->id,
                    'reviewed_at' => now(),
                    'review_notes' => "Koreksi disetujui: {$correction->reason}",
                ]);
            } else {
                $attendance = Attendance::query()->create([
                    'user_id' => $employee->id,
                    'date' => $dateStr,
                    'shift_id' => $schedule?->shift_id,
                    'office_location_id' => $schedule?->office_location_id,
                    'attendance_type' => $correction->requested_attendance_type,
                    'status' => $finalStatus,
                    'source' => 'correction',
                    'clock_in_at' => $clockInAt->utc(),
                    'clock_out_at' => $clockOutAt->utc(),
                    'late_minutes' => $lateMinutes,
                    'reviewed_by' => $actor->id,
                    'reviewed_at' => now(),
                    'review_notes' => "Koreksi disetujui: {$correction->reason}",
                ]);
            }

            // 4. Check if target period is already locked in `period_closings` (§5.5, AC-09)
            $isPeriodLocked = PeriodClosing::query()
                ->where('year', $year)
                ->where('month', $month)
                ->where('is_locked', true)
                ->exists();

            $newDailyDeduction = (float) $this->calculateDailyUserDeduction($employee, $dateObj)['total_deduction'];
            $deltaAmount = round($newDailyDeduction - $oldDailyDeduction, 2);

            if ($isPeriodLocked && abs($deltaAmount) > 0.001) {
                $nextPeriod = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->addMonth();
                DeductionAdjustment::query()->create([
                    'user_id' => $employee->id,
                    'source_year' => $year,
                    'source_month' => $month,
                    'applied_year' => (int) $nextPeriod->format('Y'),
                    'applied_month' => (int) $nextPeriod->format('n'),
                    'attendance_correction_id' => $correction->id,
                    'amount' => number_format($deltaAmount, 2, '.', ''),
                    'description' => "Adjustment pasca-tutup buku atas koreksi absensi {$dateStr} (§5.5, AC-09).",
                    'created_by' => $actor->id,
                ]);
            }

            $correction->update([
                'attendance_id' => $attendance->id,
                'status' => 'approved',
                'is_post_closing_adjustment' => $isPeriodLocked,
                'hrd_approved_by' => $actor->id,
                'hrd_approved_at' => now(),
                'supervisor_approved_by' => $correction->supervisor_approved_by ?? $actor->id,
                'supervisor_approved_at' => $correction->supervisor_approved_at ?? now(),
            ]);

            AuditLogger::record(
                action: 'attendance.correction_hrd_approved',
                auditable: $attendance,
                oldValues: $oldAttendanceValues,
                newValues: [
                    'status' => $attendance->status,
                    'source' => 'correction',
                    'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                    'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                    'late_minutes' => $attendance->late_minutes,
                    'is_post_closing_adjustment' => $isPeriodLocked,
                    'delta_deduction' => number_format($deltaAmount, 2, '.', ''),
                ],
                reason: $correction->reason,
                actorId: $actor->id
            );

            return $correction;
        });
    }
}
