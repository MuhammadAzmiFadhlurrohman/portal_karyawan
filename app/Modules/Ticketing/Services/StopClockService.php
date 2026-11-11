<?php

namespace App\Modules\Ticketing\Services;

use App\Models\StopClock;
use App\Models\Tiket;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

class StopClockService
{
    public const VALID_REASONS = [
        'IZIN_AKSES',
        'PIHAK_KETIGA_PLN',
        'CUACA_EKSTRIM',
        'MENUNGGU_MATERIAL',
        'PERMINTAAN_PELANGGAN',
        'FORCE_MAJEURE',
    ];

    public const EVIDENCE_MANDATORY_REASONS = [
        'IZIN_AKSES',
        'PIHAK_KETIGA_PLN',
        'PERMINTAAN_PELANGGAN',
    ];

    /**
     * Start a Stop Clock on a ticket (§7.4).
     */
    public function start(
        Tiket $tiket,
        string $reason,
        string $reasonDetail,
        ?string $evidencePath,
        User $user,
        ?Carbon $startedAt = null
    ): StopClock {
        if (! in_array($reason, self::VALID_REASONS, true)) {
            throw new InvalidArgumentException("Alasan Stop Clock '{$reason}' tidak valid.");
        }

        if (in_array($reason, self::EVIDENCE_MANDATORY_REASONS, true) && empty($evidencePath)) {
            throw new InvalidArgumentException("Lampiran bukti (foto/surat/dokumen) wajib untuk alasan Stop Clock '{$reason}'.");
        }

        if ($tiket->activeStopClock()->exists()) {
            throw new Exception("Tiket {$tiket->no_tiket} sudah memiliki Stop Clock yang sedang aktif.");
        }

        $now = $startedAt ?? Carbon::now();

        $stopClock = StopClock::query()->create([
            'tiket_id' => $tiket->id,
            'reason' => $reason,
            'reason_detail' => $reasonDetail,
            'evidence_path' => $evidencePath,
            'started_at' => $now,
            'started_by' => $user->id,
            'approval_status' => 'pending',
            'is_approved' => false,
        ]);

        AuditLogger::record(
            action: 'ticket.stop_clock.start',
            auditable: $stopClock,
            newValues: ['reason' => $reason, 'started_at' => $now->toDateTimeString()],
            reason: "Stop Clock dimulai oleh {$user->name}",
            actorId: $user->id
        );

        return $stopClock;
    }

    /**
     * End an active Stop Clock and recompute total_stop_clock_minutes from database (§7.4).
     */
    public function end(StopClock $stopClock, User $user, ?Carbon $endedAt = null): StopClock
    {
        if ($stopClock->ended_at !== null) {
            throw new Exception('Stop Clock ini sudah dihentikan sebelumnya.');
        }

        $stopClock->refresh();
        $now = $endedAt ?? Carbon::now();
        $durationMinutes = max(1, (int) abs($stopClock->started_at->diffInMinutes($now)));

        $stopClock->ended_at = $now;
        $stopClock->duration_minutes = $durationMinutes;
        $stopClock->ended_by = $user->id;
        $stopClock->save();

        // Recalculate total_stop_clock_minutes cleanly from sum of all completed stop clocks
        $tiket = $stopClock->ticket;
        $totalMinutes = (int) StopClock::query()
            ->where('tiket_id', $tiket->id)
            ->whereNotNull('ended_at')
            ->sum('duration_minutes');

        $tiket->total_stop_clock_minutes = $totalMinutes;
        $tiket->save();

        AuditLogger::record(
            action: 'ticket.stop_clock.end',
            auditable: $stopClock,
            newValues: [
                'ended_at' => $now->toDateTimeString(),
                'duration_minutes' => $durationMinutes,
                'total_stop_clock_minutes' => $totalMinutes,
            ],
            reason: "Stop Clock dihentikan oleh {$user->name}",
            actorId: $user->id
        );

        return $stopClock;
    }

    /**
     * Approve a Stop Clock (required if duration > 120 mins) (§7.4, AC-07).
     */
    public function approve(StopClock $stopClock, User $approver, ?string $notes = null): StopClock
    {
        $stopClock->is_approved = true;
        $stopClock->approval_status = 'approved';
        $stopClock->approved_by = $approver->id;
        $stopClock->approved_at = Carbon::now('Asia/Jakarta');
        $stopClock->approval_notes = $notes;
        $stopClock->save();

        AuditLogger::record(
            action: 'ticket.stop_clock.approve',
            auditable: $stopClock,
            newValues: ['approval_status' => 'approved', 'approved_by' => $approver->name],
            reason: $notes ?? 'Persetujuan Stop Clock',
            actorId: $approver->id
        );

        return $stopClock;
    }

    /**
     * Reject a Stop Clock.
     */
    public function reject(StopClock $stopClock, User $approver, string $reason): StopClock
    {
        $stopClock->is_approved = false;
        $stopClock->approval_status = 'rejected';
        $stopClock->approved_by = $approver->id;
        $stopClock->approved_at = Carbon::now('Asia/Jakarta');
        $stopClock->approval_notes = $reason;
        $stopClock->save();

        AuditLogger::record(
            action: 'ticket.stop_clock.reject',
            auditable: $stopClock,
            newValues: ['approval_status' => 'rejected', 'reason' => $reason],
            reason: 'Penolakan Stop Clock',
            actorId: $approver->id
        );

        return $stopClock;
    }

    /**
     * Check if ticket has an unapproved Stop Clock exceeding 120 minutes (AC-07).
     */
    public function hasUnapprovedLongStopClock(Tiket $tiket): bool
    {
        return $tiket->stopClocks()
            ->where(function ($q) {
                // If already ended with duration > 120
                $q->where('duration_minutes', '>', 120)
                    ->orWhere(function ($q2) {
                        // Or currently running > 120 minutes
                        $q2->whereNull('ended_at')
                            ->where('started_at', '<=', Carbon::now()->subMinutes(120));
                    });
            })
            ->where('approval_status', '!=', 'approved')
            ->exists();
    }
}
