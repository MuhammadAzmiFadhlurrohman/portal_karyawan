<?php

namespace App\Modules\Attendance\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaveService
{
    /**
     * Get or initialize annual leave balance for a user and year, applying prorata for mid-year hires (§5.4, §9.3).
     */
    public function getOrCreateBalance(User $user, int $year): LeaveBalance
    {
        $existing = LeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        if ($existing) {
            return $existing;
        }

        $defaultAnnualQuota = SystemSettingService::getInt('leave.annual_quota_days', 12);
        $quota = $defaultAnnualQuota;

        if ($user->join_date && (int) $user->join_date->format('Y') === $year) {
            $joinMonth = (int) $user->join_date->format('n');
            $activeMonths = max(1, 12 - ($joinMonth - 1));
            $quota = (int) floor(($activeMonths * $defaultAnnualQuota) / 12);
        } elseif ($user->join_date && (int) $user->join_date->format('Y') > $year) {
            $quota = 0;
        }

        return LeaveBalance::query()->create([
            'user_id' => $user->id,
            'year' => $year,
            'annual_quota' => $quota,
            'carried_over' => 0,
            'used_days' => 0,
            'alpa_deducted_days' => 0,
            'remaining_days' => $quota,
            'is_locked_override' => false,
        ]);
    }

    /**
     * Calculate working days between start_date and end_date (inclusive) for a user (§5.4).
     */
    public function calculateWorkingDays(User $user, Carbon $start, Carbon $end): int
    {
        $holidays = NationalHoliday::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($d): string => Carbon::parse((string) $d)->toDateString())
            ->flip()
            ->all();

        $schedules = MonthlySchedule::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get()
            ->keyBy(fn (MonthlySchedule $s): string => $s->date->toDateString());

        $workingDays = 0;
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        while ($cursor->lte($endDay)) {
            $dateStr = $cursor->toDateString();
            if (! isset($holidays[$dateStr])) {
                /** @var MonthlySchedule|null $sched */
                $sched = $schedules->get($dateStr);
                if ($sched && $sched->shift) {
                    if (! $sched->shift->is_day_off) {
                        $workingDays++;
                    }
                } elseif (! $cursor->isWeekend()) {
                    $workingDays++;
                }
            }
            $cursor->addDay();
        }

        return $workingDays;
    }

    /**
     * Create a new leave application with full §5.4 validations:
     * - Annual quota (12 days/year) & Monthly cap (max 3 working days/month)
     * - Overlap check for the user
     * - Division concurrent leave capacity check
     * - Mandatory medical attachment for sick leave > N days
     *
     * @param  array{
     *   leave_type: string,
     *   start_date: string,
     *   end_date: string,
     *   reason: string
     * }  $payload
     */
    public function requestLeave(User $user, array $payload, ?UploadedFile $attachment = null): Leave
    {
        $start = Carbon::parse($payload['start_date'], 'Asia/Jakarta')->startOfDay();
        $end = Carbon::parse($payload['end_date'], 'Asia/Jakarta')->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException('Tanggal selesai cuti tidak boleh sebelum tanggal mulai.');
        }

        $leaveType = $payload['leave_type'];
        $workingDays = $this->calculateWorkingDays($user, $start, $end);
        if ($workingDays <= 0) {
            throw new InvalidArgumentException('Rentang tanggal yang dipilih hanya berisi hari libur / tidak memiliki hari kerja aktif.');
        }

        // 1. Check overlapping leaves for the same user (§5.4)
        $hasOverlap = Leave::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->exists();

        if ($hasOverlap) {
            throw new InvalidArgumentException('Pengajuan cuti ditolak: terdapat pengajuan atau jadwal cuti lain yang tumpang tindih pada tanggal tersebut (§5.4).');
        }

        // 2. Check division concurrent leave capacity (§5.4)
        $maxConcurrentPerDivision = SystemSettingService::getInt('leave.division_max_concurrent', 2);
        if ($user->division) {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dtStr = $cursor->toDateString();
                $concurrentCount = Leave::query()
                    ->whereHas('user', fn ($q) => $q->where('division', $user->division))
                    ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
                    ->where('is_alpa_conversion', false)
                    ->whereDate('start_date', '<=', $dtStr)
                    ->whereDate('end_date', '>=', $dtStr)
                    ->count();

                if ($concurrentCount >= $maxConcurrentPerDivision) {
                    throw new InvalidArgumentException(
                        "Pengajuan cuti ditolak: kapasitas maksimum cuti bersamaan untuk divisi {$user->division} ({$maxConcurrentPerDivision} orang) pada tanggal {$dtStr} telah penuh (§5.4)."
                    );
                }
                $cursor->addDay();
            }
        }

        // 3. Check mandatory medical attachment for sick leave > N days (§5.4)
        $sickMinDaysForAttachment = SystemSettingService::getInt('leave.sick_attachment_min_days', 1);
        if ($leaveType === 'sick' && $workingDays > $sickMinDaysForAttachment && ! $attachment) {
            throw new InvalidArgumentException(
                "Cuti sakit lebih dari {$sickMinDaysForAttachment} hari kerja wajib melampirkan surat keterangan dokter (§5.4)."
            );
        }

        // 4. Annual leave quota & 3 working days/month cap (§5.4)
        if ($leaveType === 'annual') {
            $year = (int) $start->format('Y');
            $balance = $this->getOrCreateBalance($user, $year);

            if ($balance->remaining_days < $workingDays) {
                throw new InvalidArgumentException(
                    "Sisa kuota cuti tahunan Anda ({$balance->remaining_days} hari) tidak mencukupi untuk pengajuan {$workingDays} hari kerja (§5.4)."
                );
            }

            $monthlyMaxDays = SystemSettingService::getInt('leave.monthly_max_days', 3);
            $monthUsedOrPending = (int) Leave::query()
                ->where('user_id', $user->id)
                ->whereIn('leave_type', ['annual', 'alpa_conversion'])
                ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
                ->whereYear('start_date', (int) $start->format('Y'))
                ->whereMonth('start_date', (int) $start->format('n'))
                ->sum('working_days');

            if (($monthUsedOrPending + $workingDays) > $monthlyMaxDays) {
                throw new InvalidArgumentException(
                    "Pengajuan cuti ditolak: batas maksimum cuti tahunan per bulan adalah {$monthlyMaxDays} hari kerja (bulan ini sudah terpakai/diajukan {$monthUsedOrPending} hari, termasuk konversi alpa)."
                );
            }
        }

        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('leaves/'.$user->id, 'private');
        }

        $leave = Leave::query()->create([
            'user_id' => $user->id,
            'leave_type' => $leaveType,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'working_days' => $workingDays,
            'reason' => $payload['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending_supervisor',
            'is_alpa_conversion' => false,
        ]);

        AuditLogger::record(
            action: 'leave.requested',
            auditable: $leave,
            newValues: [
                'leave_type' => $leaveType,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'working_days' => $workingDays,
            ],
            reason: $payload['reason'],
            actorId: $user->id
        );

        return $leave;
    }

    /**
     * Step 1 Approval by Supervisor (or delegated supervisor) (§4.3, §5.4).
     */
    public function approveBySupervisor(Leave $leave, User $actor): Leave
    {
        if ($leave->user_id === $actor->id) {
            throw new InvalidArgumentException('Pengaju tidak diperbolehkan menyetujui pengajuan cutinya sendiri (AC-10).');
        }

        if ($leave->status !== 'pending_supervisor') {
            throw new InvalidArgumentException("Pengajuan cuti tidak dalam status menunggu persetujuan Atasan (status: {$leave->status}).");
        }

        $leave->update([
            'status' => 'pending_hrd',
            'supervisor_approved_by' => $actor->id,
            'supervisor_approved_at' => now(),
        ]);

        AuditLogger::record(
            action: 'leave.supervisor_approved',
            auditable: $leave,
            newValues: ['status' => 'pending_hrd'],
            reason: 'Disetujui oleh Atasan',
            actorId: $actor->id
        );

        return $leave;
    }

    /**
     * Step 2 Final Approval by HRD (§5.4).
     */
    public function approveByHrd(Leave $leave, User $actor): Leave
    {
        if ($leave->user_id === $actor->id) {
            throw new InvalidArgumentException('Pengaju tidak diperbolehkan menyetujui pengajuan cutinya sendiri (AC-10).');
        }

        if (! in_array($leave->status, ['pending_supervisor', 'pending_hrd'], true)) {
            throw new InvalidArgumentException("Pengajuan cuti sudah diproses sebelumnya (status: {$leave->status}).");
        }

        return DB::transaction(function () use ($leave, $actor): Leave {
            /** @var User $employee */
            $employee = User::query()->findOrFail($leave->user_id);

            if ($leave->leave_type === 'annual') {
                $year = (int) $leave->start_date->format('Y');
                $balance = $this->getOrCreateBalance($employee, $year);

                if ($balance->remaining_days < $leave->working_days) {
                    throw new InvalidArgumentException('Saldo cuti tahunan karyawan tidak mencukupi saat persetujuan final.');
                }

                $balance->update([
                    'used_days' => $balance->used_days + $leave->working_days,
                    'remaining_days' => $balance->remaining_days - $leave->working_days,
                ]);
            }

            $leave->update([
                'status' => 'approved',
                'hrd_approved_by' => $actor->id,
                'hrd_approved_at' => now(),
                'supervisor_approved_by' => $leave->supervisor_approved_by ?? $actor->id,
                'supervisor_approved_at' => $leave->supervisor_approved_at ?? now(),
            ]);

            // Mark attendance records for those dates as `leave`
            $cursor = $leave->start_date->copy()->startOfDay();
            $endDay = $leave->end_date->copy()->startOfDay();
            while ($cursor->lte($endDay)) {
                $dtStr = $cursor->toDateString();
                $existingAtt = Attendance::query()
                    ->where('user_id', $employee->id)
                    ->whereDate('date', $dtStr)
                    ->first();

                if ($existingAtt) {
                    $existingAtt->update([
                        'status' => 'leave',
                        'notes' => "Cuti ({$leave->leave_type}): {$leave->reason}",
                    ]);
                } else {
                    Attendance::query()->create([
                        'user_id' => $employee->id,
                        'date' => $dtStr,
                        'status' => 'leave',
                        'source' => 'manual',
                        'notes' => "Cuti ({$leave->leave_type}): {$leave->reason}",
                    ]);
                }
                $cursor->addDay();
            }

            AuditLogger::record(
                action: 'leave.hrd_approved',
                auditable: $leave,
                newValues: ['status' => 'approved', 'working_days' => $leave->working_days],
                reason: 'Disetujui final oleh HRD (§5.4)',
                actorId: $actor->id
            );

            return $leave;
        });
    }

    /**
     * Cancel a leave before its start_date and automatically restore annual leave balance (§5.4).
     */
    public function cancelLeave(Leave $leave, User $actor, string $reason): Leave
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        if ($today->gte($leave->start_date->copy()->startOfDay())) {
            throw new InvalidArgumentException('Pembatalan cuti hanya dapat dilakukan sebelum tanggal mulai cuti (§5.4).');
        }

        if (! in_array($leave->status, ['pending_supervisor', 'pending_hrd', 'approved'], true)) {
            throw new InvalidArgumentException("Cuti dengan status {$leave->status} tidak dapat dibatalkan.");
        }

        return DB::transaction(function () use ($leave, $actor, $reason): Leave {
            $wasApproved = $leave->status === 'approved';

            if ($wasApproved && $leave->leave_type === 'annual') {
                /** @var User $employee */
                $employee = User::query()->findOrFail($leave->user_id);
                $year = (int) $leave->start_date->format('Y');
                $balance = $this->getOrCreateBalance($employee, $year);

                $balance->update([
                    'used_days' => max(0, $balance->used_days - $leave->working_days),
                    'remaining_days' => $balance->remaining_days + $leave->working_days,
                ]);
            }

            if ($wasApproved) {
                Attendance::query()
                    ->where('user_id', $leave->user_id)
                    ->where('status', 'leave')
                    ->whereDate('date', '>=', $leave->start_date->toDateString())
                    ->whereDate('date', '<=', $leave->end_date->toDateString())
                    ->forceDelete();
            }

            $leave->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            AuditLogger::record(
                action: 'leave.cancelled',
                auditable: $leave,
                newValues: [
                    'status' => 'cancelled',
                    'balance_restored' => $wasApproved && $leave->leave_type === 'annual',
                ],
                reason: $reason,
                actorId: $actor->id
            );

            return $leave;
        });
    }

    /**
     * Manual override of leave balance with optional lock so `leave:sync` will not overwrite it (§5.4).
     */
    public function overrideBalance(
        User $targetUser,
        int $year,
        int $newRemainingDays,
        bool $lockOverride,
        User $actor,
        string $reason
    ): LeaveBalance {
        $balance = $this->getOrCreateBalance($targetUser, $year);
        $oldValues = [
            'remaining_days' => $balance->remaining_days,
            'is_locked_override' => $balance->is_locked_override,
        ];

        $balance->update([
            'remaining_days' => $newRemainingDays,
            'is_locked_override' => $lockOverride,
            'override_by' => $actor->id,
            'override_at' => now(),
            'override_reason' => $reason,
        ]);

        AuditLogger::record(
            action: 'leave_balance.overridden',
            auditable: $balance,
            oldValues: $oldValues,
            newValues: [
                'remaining_days' => $newRemainingDays,
                'is_locked_override' => $lockOverride,
            ],
            reason: $reason,
            actorId: $actor->id
        );

        return $balance;
    }

    /**
     * Idempotent daily Alpa synchronization (`leave:sync`, §5.4):
     * - Detects scheduled workdays (after user.join_date) without Attendance and without approved Leave
     * - Marks them as `absent` (Alpa)
     * - If `leave.alpa_converts_to_leave` is active and user's balance is NOT `is_locked_override`:
     *   converts up to 3 Alpa per month into annual leave deduction with a 3-working-day dispute window.
     *
     * @return array{alpa_detected: int, converted_to_leave: int, skipped_locked: int}
     */
    public function syncDailyAlpa(?string $targetDate = null): array
    {
        $date = $targetDate
            ? Carbon::parse($targetDate, 'Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->subDay()->startOfDay();

        $dateStr = $date->toDateString();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        $isHoliday = NationalHoliday::query()->whereDate('date', $dateStr)->exists();
        if ($isHoliday) {
            return ['alpa_detected' => 0, 'converted_to_leave' => 0, 'skipped_locked' => 0];
        }

        $alpaConvertsToLeave = SystemSettingService::getBool('leave.alpa_converts_to_leave', true, $date);
        $monthlyMaxDays = SystemSettingService::getInt('leave.monthly_max_days', 3, $date);

        $schedules = MonthlySchedule::query()
            ->with(['user', 'shift'])
            ->whereDate('date', $dateStr)
            ->get();

        $alpaDetected = 0;
        $convertedToLeave = 0;
        $skippedLocked = 0;

        foreach ($schedules as $schedule) {
            $user = $schedule->user;
            $shift = $schedule->shift;

            if (! $user || ! $user->is_active || ! $shift || $shift->is_day_off) {
                continue;
            }

            if ($user->join_date && $date->lt($user->join_date->copy()->startOfDay())) {
                continue;
            }

            // Check if user has an approved leave covering this date
            $hasApprovedLeave = Leave::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->exists();

            if ($hasApprovedLeave) {
                continue;
            }

            // Check if user has a non-absent attendance record on this date
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $dateStr)
                ->first();

            if ($attendance && $attendance->status !== 'absent') {
                continue;
            }

            // Ensure Attendance row with status = 'absent' exists (idempotent)
            if (! $attendance) {
                $attendance = Attendance::query()->create([
                    'user_id' => $user->id,
                    'date' => $dateStr,
                    'shift_id' => $shift->id,
                    'office_location_id' => $schedule->office_location_id,
                    'status' => 'absent',
                    'source' => 'manual',
                    'notes' => 'Alpa terdeteksi otomatis oleh leave:sync (§5.4).',
                ]);
            }

            $alpaDetected++;

            if (! $alpaConvertsToLeave) {
                continue;
            }

            $balance = $this->getOrCreateBalance($user, $year);
            if ($balance->is_locked_override) {
                $skippedLocked++;

                continue;
            }

            // Check how many cuti days (annual + alpa conversion) already occurred this month
            $monthlyCutiUsed = (int) Leave::query()
                ->where('user_id', $user->id)
                ->whereIn('leave_type', ['annual', 'alpa_conversion'])
                ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->sum('working_days');

            if ($monthlyCutiUsed >= $monthlyMaxDays || $balance->remaining_days <= 0) {
                // Alpa ke-4 dst dalam bulan yang sama, atau jatah cuti habis -> tetap sebagai alpa murni (potong gaji)
                continue;
            }

            // Create idempotent alpa_conversion Leave with 3-working-day dispute window (§5.4)
            $disputeDeadline = $this->addWorkingDays(Carbon::now('Asia/Jakarta'), 3);

            DB::transaction(function () use ($user, $dateStr, $disputeDeadline, $balance): void {
                Leave::query()->create([
                    'user_id' => $user->id,
                    'leave_type' => 'alpa_conversion',
                    'start_date' => $dateStr,
                    'end_date' => $dateStr,
                    'working_days' => 1,
                    'reason' => "Konversi otomatis Alpa tanggal {$dateStr} ke kuota cuti (Masa sanggah 3 hari kerja s.d. {$disputeDeadline->format('Y-m-d H:i')} WIB, §5.4).",
                    'status' => 'approved',
                    'is_alpa_conversion' => true,
                    'dispute_deadline_at' => $disputeDeadline,
                ]);

                $balance->update([
                    'alpa_deducted_days' => $balance->alpa_deducted_days + 1,
                    'remaining_days' => $balance->remaining_days - 1,
                ]);
            });

            $convertedToLeave++;
        }

        return [
            'alpa_detected' => $alpaDetected,
            'converted_to_leave' => $convertedToLeave,
            'skipped_locked' => $skippedLocked,
        ];
    }

    /**
     * Synchronize and convert unhandled Alpa attendances for a specific user and month:
     * - In 1 month, up to 3 days of cuti (annual + alpa conversion) can be absorbed from annual quota.
     * - If alpa count exceeds 3 in the month OR annual leave quota is exhausted (0),
     *   any subsequent alpa stays as pure absent (potong gaji sesuai tarif potongan alpa karyawan).
     */
    public function syncUserAlpaForMonth(User $user, int $year, int $month): void
    {
        $balance = $this->getOrCreateBalance($user, $year);
        if ($balance->is_locked_override) {
            return;
        }

        $monthlyMaxDays = SystemSettingService::getInt('leave.monthly_max_days', 3);
        $alpaConvertsToLeave = SystemSettingService::getBool('leave.alpa_converts_to_leave', true);
        if (! $alpaConvertsToLeave) {
            return;
        }

        // Get all attendances with status = 'absent' for this user and month, ordered chronologically
        $absents = Attendance::query()
            ->where('user_id', $user->id)
            ->where('status', 'absent')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'asc')
            ->get();

        if ($absents->isEmpty()) {
            return;
        }

        foreach ($absents as $att) {
            $dateStr = $att->date->toDateString();

            // Check if already covered by an approved leave
            $existingLeave = Leave::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->first();

            if ($existingLeave) {
                continue;
            }

            // Count how many cuti days (annual + alpa conversion) are currently in this month
            $monthlyCutiUsed = (int) Leave::query()
                ->where('user_id', $user->id)
                ->whereIn('leave_type', ['annual', 'alpa_conversion'])
                ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->sum('working_days');

            $balance->refresh();

            // If monthly cap of 3 is reached OR remaining annual quota is 0, DO NOT convert -> stays potong gaji
            if ($monthlyCutiUsed >= $monthlyMaxDays || $balance->remaining_days <= 0) {
                continue;
            }

            $disputeDeadline = $this->addWorkingDays(Carbon::now('Asia/Jakarta'), 3);

            DB::transaction(function () use ($user, $dateStr, $disputeDeadline, $balance): void {
                Leave::query()->create([
                    'user_id' => $user->id,
                    'leave_type' => 'alpa_conversion',
                    'start_date' => $dateStr,
                    'end_date' => $dateStr,
                    'working_days' => 1,
                    'reason' => "Konversi otomatis Alpa tanggal {$dateStr} ke kuota cuti (Masa sanggah 3 hari kerja s.d. {$disputeDeadline->format('Y-m-d H:i')} WIB).",
                    'status' => 'approved',
                    'is_alpa_conversion' => true,
                    'dispute_deadline_at' => $disputeDeadline,
                ]);

                $balance->update([
                    'alpa_deducted_days' => $balance->alpa_deducted_days + 1,
                    'remaining_days' => $balance->remaining_days - 1,
                ]);
            });
        }
    }

    /**
     * Add N working days (skipping Saturday/Sunday) for the 3-working-day Alpa dispute window (§5.4).
     */
    private function addWorkingDays(Carbon $from, int $workingDays): Carbon
    {
        $cursor = $from->copy();
        $added = 0;

        while ($added < $workingDays) {
            $cursor->addDay();
            if (! $cursor->isWeekend()) {
                $added++;
            }
        }

        return $cursor->endOfDay();
    }
}
