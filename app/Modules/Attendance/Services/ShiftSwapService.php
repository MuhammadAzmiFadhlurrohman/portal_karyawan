<?php

namespace App\Modules\Attendance\Services;

use App\Models\MonthlySchedule;
use App\Models\Shift;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShiftSwapService
{
    /**
     * Create a shift swap or backup request (§5.3).
     * - Mode tukar (is_backup=false): supports same-date or 4-way cross-date swap
     * - Mode backup (is_backup=true): only Piket <-> Libur on the same date
     * - Validations:
     *   1. Same division
     *   2. Dates not in the past
     *   3. No duplicate active swap request on any of the involved (user, date) slots
     *   4. Resulting schedule does not violate the 11-hour rest gap rule (§5.2)
     * - Expiration: min(now + 48h, H-1 23:59:59 of earliest swap date)
     *
     * @param  array{
     *   target_user_id: int,
     *   requester_date: string,
     *   target_date: string,
     *   is_backup?: bool,
     *   reason: string
     * }  $payload
     */
    public function createRequest(User $requester, array $payload): ShiftSwap
    {
        $targetUser = User::query()->findOrFail((int) $payload['target_user_id']);
        if ($requester->id === $targetUser->id) {
            throw new InvalidArgumentException('Tidak dapat mengajukan tukar shift dengan diri sendiri.');
        }

        if ($requester->division !== $targetUser->division) {
            throw new InvalidArgumentException('Tukar shift hanya diizinkan antar-karyawan dalam divisi yang sama (§5.3).');
        }

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $d1 = Carbon::parse($payload['requester_date'], 'Asia/Jakarta')->startOfDay();
        $d2 = Carbon::parse($payload['target_date'], 'Asia/Jakarta')->startOfDay();
        $isBackup = (bool) ($payload['is_backup'] ?? false);

        if ($d1->lt($today) || $d2->lt($today)) {
            throw new InvalidArgumentException('Tanggal tukar shift tidak boleh berada di masa lalu (§5.3).');
        }

        if ($isBackup && ! $d1->isSameDay($d2)) {
            throw new InvalidArgumentException('Mode Backup hanya diizinkan pada tanggal yang sama (§5.3).');
        }

        $d1Str = $d1->toDateString();
        $d2Str = $d2->toDateString();

        // Check active duplicate requests on any of the involved slots (§5.3)
        $this->expireStaleRequests();

        $involvedUsers = [$requester->id, $targetUser->id];
        $involvedDates = array_unique([$d1Str, $d2Str]);

        $activeDuplicateExists = ShiftSwap::query()
            ->whereIn('status', ['pending_target', 'pending_supervisor'])
            ->where(function ($q) use ($involvedUsers): void {
                $q->whereIn('requester_id', $involvedUsers)
                    ->orWhereIn('target_user_id', $involvedUsers);
            })
            ->where(function ($q) use ($involvedDates): void {
                foreach ($involvedDates as $dt) {
                    $q->orWhereDate('requester_date', $dt)
                        ->orWhereDate('target_date', $dt);
                }
            })
            ->exists();

        if ($activeDuplicateExists) {
            throw new InvalidArgumentException('Terdapat permintaan tukar shift aktif lain pada slot tanggal/karyawan yang sama (§5.3).');
        }

        // Fetch existing schedules on D1 and D2
        $reqD1 = MonthlySchedule::query()->with('shift')->where('user_id', $requester->id)->whereDate('date', $d1Str)->first();
        $tarD1 = MonthlySchedule::query()->with('shift')->where('user_id', $targetUser->id)->whereDate('date', $d1Str)->first();

        if (! $reqD1 || ! $tarD1 || ! $reqD1->shift || ! $tarD1->shift) {
            throw new InvalidArgumentException("Jadwal shift pada tanggal {$d1Str} belum tersedia untuk kedua karyawan.");
        }

        $reqD2 = $d1->isSameDay($d2)
            ? $reqD1
            : MonthlySchedule::query()->with('shift')->where('user_id', $requester->id)->whereDate('date', $d2Str)->first();

        $tarD2 = $d1->isSameDay($d2)
            ? $tarD1
            : MonthlySchedule::query()->with('shift')->where('user_id', $targetUser->id)->whereDate('date', $d2Str)->first();

        if (! $reqD2 || ! $tarD2 || ! $reqD2->shift || ! $tarD2->shift) {
            throw new InvalidArgumentException("Jadwal shift pada tanggal {$d2Str} belum tersedia untuk kedua karyawan.");
        }

        // Backup mode rule: Piket <-> Libur on same date
        if ($isBackup) {
            $reqIsPiket = (bool) $reqD1->shift->is_piket;
            $tarIsPiket = (bool) $tarD1->shift->is_piket;
            $reqIsOff = (bool) $reqD1->shift->is_day_off;
            $tarIsOff = (bool) $tarD1->shift->is_day_off;

            $validBackupPair = ($reqIsPiket && $tarIsOff) || ($reqIsOff && $tarIsPiket);
            if (! $validBackupPair) {
                throw new InvalidArgumentException('Mode Backup hanya berlaku untuk pertukaran jadwal Piket (PK/PA) dengan Libur (L) di tanggal yang sama (§5.3).');
            }
        }

        // Validate 11-hour rest gap rule for resulting shifts (§5.2, §5.3)
        // After swap:
        // On D1: Requester gets tarD1->shift, Target gets reqD1->shift
        // On D2 (if cross-date): Requester gets tarD2->shift, Target gets reqD2->shift
        $simulatedRequesterShifts = [$d1Str => $tarD1->shift];
        $simulatedTargetShifts = [$d1Str => $reqD1->shift];

        if (! $d1->isSameDay($d2)) {
            $simulatedRequesterShifts[$d2Str] = $tarD2->shift;
            $simulatedTargetShifts[$d2Str] = $reqD2->shift;
        }

        $this->assertNoRestConflict($requester, $simulatedRequesterShifts);
        $this->assertNoRestConflict($targetUser, $simulatedTargetShifts);

        // Calculate expiration: min(now + 48h, H-1 23:59:59 of earliest swap date, or now + 2h if swap is today/tomorrow)
        $earliestDate = $d1->lt($d2) ? $d1->copy() : $d2->copy();
        $hMinusOneEnd = $earliestDate->copy()->subDay()->endOfDay();
        $plus48h = Carbon::now('Asia/Jakarta')->addHours(48);

        $expiresAt = $hMinusOneEnd->gt(Carbon::now('Asia/Jakarta'))
            ? ($hMinusOneEnd->lt($plus48h) ? $hMinusOneEnd : $plus48h)
            : $plus48h;

        $swap = ShiftSwap::query()->create([
            'requester_id' => $requester->id,
            'target_user_id' => $targetUser->id,
            'requester_date' => $d1Str,
            'target_date' => $d2Str,
            'is_backup' => $isBackup,
            'req_d1_shift_id' => $reqD1->shift_id,
            'tar_d1_shift_id' => $tarD1->shift_id,
            'req_d2_shift_id' => $d1->isSameDay($d2) ? null : $reqD2->shift_id,
            'tar_d2_shift_id' => $d1->isSameDay($d2) ? null : $tarD2->shift_id,
            'status' => 'pending_target',
            'reason' => $payload['reason'],
            'expires_at' => $expiresAt,
        ]);

        AuditLogger::record(
            action: 'shift_swap.requested',
            auditable: $swap,
            newValues: [
                'requester_id' => $requester->id,
                'target_user_id' => $targetUser->id,
                'requester_date' => $d1Str,
                'target_date' => $d2Str,
                'is_backup' => $isBackup,
            ],
            reason: $payload['reason'],
            actorId: $requester->id
        );

        return $swap;
    }

    /**
     * Step 1 Approval: Target employee approves the swap request (§5.3).
     */
    public function approveByTarget(ShiftSwap $swap, User $actor): ShiftSwap
    {
        $this->expireStaleRequests();
        $swap->refresh();

        if ($swap->status !== 'pending_target') {
            throw new InvalidArgumentException("Permintaan tukar shift tidak dalam status menunggu persetujuan rekan (status saat ini: {$swap->status}).");
        }

        if ($swap->target_user_id !== $actor->id) {
            throw new InvalidArgumentException('Hanya rekan target tukar shift yang dapat menyetujui tahap ini.');
        }

        $swap->update([
            'status' => 'pending_supervisor',
            'target_approved_at' => now(),
        ]);

        AuditLogger::record(
            action: 'shift_swap.target_approved',
            auditable: $swap,
            newValues: ['status' => 'pending_supervisor'],
            reason: 'Disetujui oleh rekan target tukar shift',
            actorId: $actor->id
        );

        return $swap;
    }

    /**
     * Step 2 Final Approval: Supervisor / Manager / Admin approves and executes the 4-way swap
     * inside a single DB::transaction with SELECT ... FOR UPDATE (`lockForUpdate`),
     * verifying schedule state has not changed since request creation (§5.3).
     */
    public function approveBySupervisor(ShiftSwap $swap, User $actor): ShiftSwap
    {
        $this->expireStaleRequests();
        $swap->refresh();

        if ($swap->status !== 'pending_supervisor') {
            throw new InvalidArgumentException("Permintaan tukar shift harus disetujui rekan terlebih dahulu (status saat ini: {$swap->status}).");
        }

        if ($actor->id === $swap->requester_id || $actor->id === $swap->target_user_id) {
            throw new InvalidArgumentException('Pelaku tukar shift tidak boleh menyetujui sendiri pada tahap Atasan/Admin (§4.3, AC-10).');
        }

        return DB::transaction(function () use ($swap, $actor): ShiftSwap {
            /** @var ShiftSwap $lockedSwap */
            $lockedSwap = ShiftSwap::query()->lockForUpdate()->findOrFail($swap->id);

            $d1Str = $lockedSwap->requester_date->toDateString();
            $d2Str = $lockedSwap->target_date->toDateString();
            $isCrossDate = $d1Str !== $d2Str;

            // Lock all involved schedule rows with lockForUpdate() in deterministic order (§5.3)
            $lockedSchedules = MonthlySchedule::query()
                ->whereIn('user_id', [$lockedSwap->requester_id, $lockedSwap->target_user_id])
                ->where(function ($q) use ($d1Str, $d2Str): void {
                    $q->whereDate('date', $d1Str)
                        ->orWhereDate('date', $d2Str);
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $findRow = function (int $userId, string $dateStr) use ($lockedSchedules): ?MonthlySchedule {
                /** @var MonthlySchedule|null $found */
                $found = $lockedSchedules->first(
                    fn (MonthlySchedule $row): bool => $row->user_id === $userId && $row->date->toDateString() === $dateStr
                );

                return $found;
            };

            $reqD1Row = $findRow($lockedSwap->requester_id, $d1Str);
            $tarD1Row = $findRow($lockedSwap->target_user_id, $d1Str);
            $reqD2Row = $isCrossDate ? $findRow($lockedSwap->requester_id, $d2Str) : null;
            $tarD2Row = $isCrossDate ? $findRow($lockedSwap->target_user_id, $d2Str) : null;

            // Verify state has not changed since request creation (§5.3)
            $d1Unchanged = $reqD1Row
                && $tarD1Row
                && $reqD1Row->shift_id === $lockedSwap->req_d1_shift_id
                && $tarD1Row->shift_id === $lockedSwap->tar_d1_shift_id;

            $d2Unchanged = ! $isCrossDate || (
                $reqD2Row
                && $tarD2Row
                && $reqD2Row->shift_id === $lockedSwap->req_d2_shift_id
                && $tarD2Row->shift_id === $lockedSwap->tar_d2_shift_id
            );

            if (! $d1Unchanged || ! $d2Unchanged) {
                $lockedSwap->update([
                    'status' => 'rejected',
                    'rejected_by' => $actor->id,
                    'rejection_reason' => 'Ditolak otomatis oleh sistem: isi jadwal telah berubah sejak permintaan tukar shift dibuat (§5.3).',
                ]);

                AuditLogger::record(
                    action: 'shift_swap.auto_rejected_stale_schedule',
                    auditable: $lockedSwap,
                    newValues: ['status' => 'rejected'],
                    reason: 'Jadwal telah berubah sejak pengajuan dibuat (§5.3)',
                    actorId: $actor->id
                );

                return $lockedSwap;
            }

            // Execute atomic swap on D1
            $reqD1Row->update([
                'shift_id' => $lockedSwap->tar_d1_shift_id,
                'updated_by' => $actor->id,
            ]);
            $tarD1Row->update([
                'shift_id' => $lockedSwap->req_d1_shift_id,
                'updated_by' => $actor->id,
            ]);

            // Execute atomic swap on D2 if 4-way cross-date swap
            if ($isCrossDate && $reqD2Row && $tarD2Row && $lockedSwap->tar_d2_shift_id && $lockedSwap->req_d2_shift_id) {
                $reqD2Row->update([
                    'shift_id' => $lockedSwap->tar_d2_shift_id,
                    'updated_by' => $actor->id,
                ]);
                $tarD2Row->update([
                    'shift_id' => $lockedSwap->req_d2_shift_id,
                    'updated_by' => $actor->id,
                ]);
            }

            $lockedSwap->update([
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);

            AuditLogger::record(
                action: 'shift_swap.approved',
                auditable: $lockedSwap,
                oldValues: [
                    'req_d1_shift_id' => $lockedSwap->req_d1_shift_id,
                    'tar_d1_shift_id' => $lockedSwap->tar_d1_shift_id,
                    'req_d2_shift_id' => $lockedSwap->req_d2_shift_id,
                    'tar_d2_shift_id' => $lockedSwap->tar_d2_shift_id,
                ],
                newValues: [
                    'status' => 'approved',
                    'req_d1_shift_id' => $lockedSwap->tar_d1_shift_id,
                    'tar_d1_shift_id' => $lockedSwap->req_d1_shift_id,
                    'req_d2_shift_id' => $lockedSwap->tar_d2_shift_id,
                    'tar_d2_shift_id' => $lockedSwap->req_d2_shift_id,
                ],
                reason: 'Tukar shift disetujui dan jadwal diperbarui secara atomik (§5.3)',
                actorId: $actor->id
            );

            return $lockedSwap;
        });
    }

    /**
     * Reject a shift swap request with mandatory reason.
     */
    public function reject(ShiftSwap $swap, User $actor, string $reason): ShiftSwap
    {
        if (! in_array($swap->status, ['pending_target', 'pending_supervisor'], true)) {
            throw new InvalidArgumentException('Permintaan tukar shift sudah selesai diproses.');
        }

        $swap->update([
            'status' => 'rejected',
            'rejected_by' => $actor->id,
            'rejection_reason' => $reason,
        ]);

        AuditLogger::record(
            action: 'shift_swap.rejected',
            auditable: $swap,
            newValues: ['status' => 'rejected'],
            reason: $reason,
            actorId: $actor->id
        );

        return $swap;
    }

    /**
     * Mark expired pending shift swap requests as `expired` (§5.3).
     */
    public function expireStaleRequests(): int
    {
        return ShiftSwap::query()
            ->whereIn('status', ['pending_target', 'pending_supervisor'])
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
                'rejection_reason' => 'Kedaluwarsa otomatis (melewati batas 48 jam atau H-1 tanggal tukar).',
            ]);
    }

    /**
     * Ensure simulated shifts for a user do not violate the < 11h rest gap rule (§5.2, §5.3).
     *
     * @param  array<string, Shift>  $simulatedShiftsByDate
     */
    private function assertNoRestConflict(User $user, array $simulatedShiftsByDate, float $minRestHours = 11.0): void
    {
        foreach ($simulatedShiftsByDate as $dateStr => $newShift) {
            if ($newShift->is_day_off || ! $newShift->start_time || ! $newShift->end_time) {
                continue;
            }

            $currentDate = Carbon::parse($dateStr, 'Asia/Jakarta');
            $prevDateStr = $currentDate->copy()->subDay()->toDateString();
            $nextDateStr = $currentDate->copy()->addDay()->toDateString();

            $prevShift = $simulatedShiftsByDate[$prevDateStr]
                ?? MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $prevDateStr)->first()?->shift;

            if ($prevShift && ! $prevShift->is_day_off && $prevShift->end_time) {
                $prevEnd = Carbon::parse($prevDateStr.' '.$prevShift->end_time, 'Asia/Jakarta');
                $currStart = Carbon::parse($dateStr.' '.$newShift->start_time, 'Asia/Jakarta');
                $restHours = round($prevEnd->diffInMinutes($currStart, false) / 60.0, 1);
                if ($restHours < $minRestHours) {
                    throw new InvalidArgumentException(
                        "Tukar shift ditolak: melanggar aturan jeda istirahat minimum 11 jam untuk {$user->name} antara {$prevDateStr} ({$prevShift->code}) dan {$dateStr} ({$newShift->code}), jeda hanya {$restHours} jam (§5.2, §5.3)."
                    );
                }
            }

            $nextShift = $simulatedShiftsByDate[$nextDateStr]
                ?? MonthlySchedule::query()->with('shift')->where('user_id', $user->id)->whereDate('date', $nextDateStr)->first()?->shift;

            if ($nextShift && ! $nextShift->is_day_off && $nextShift->start_time) {
                $currEnd = Carbon::parse($dateStr.' '.$newShift->end_time, 'Asia/Jakarta');
                $nextStart = Carbon::parse($nextDateStr.' '.$nextShift->start_time, 'Asia/Jakarta');
                $restHours = round($currEnd->diffInMinutes($nextStart, false) / 60.0, 1);
                if ($restHours < $minRestHours) {
                    throw new InvalidArgumentException(
                        "Tukar shift ditolak: melanggar aturan jeda istirahat minimum 11 jam untuk {$user->name} antara {$dateStr} ({$newShift->code}) dan {$nextDateStr} ({$nextShift->code}), jeda hanya {$restHours} jam (§5.2, §5.3)."
                    );
                }
            }
        }
    }
}
