<?php

namespace App\Modules\Ticketing\Services;

use App\Models\Tiket;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TicketStateMachine
{
    public function __construct(
        protected TechnicalAssetService $assetService,
        protected StopClockService $stopClockService,
    ) {}

    /**
     * Transition ticket to PENDING_VERIFIKASI (Closing Awal by technician) (§7.2, §7.6, AC-06, AC-07).
     *
     * @param  array<string, mixed>  $closingData
     * @return array{tiket: Tiket, prerequisites: array{passed: bool, missing: list<string>}}
     */
    public function submitClosingAwal(Tiket $tiket, User $technician, array $closingData = []): array
    {
        if ($tiket->status !== 'PROSES') {
            throw new Exception("Closing Awal hanya dapat dilakukan dari status PROSES (Status saat ini: {$tiket->status}).");
        }

        // Active Stop Clock check (§7.2)
        if ($tiket->activeStopClock()->exists()) {
            throw new Exception('Tidak dapat melakukan Closing Awal saat Stop Clock masih aktif.');
        }

        // Stop Clock > 120 minutes approval check (AC-07)
        if ($this->stopClockService->hasUnapprovedLongStopClock($tiket)) {
            throw new Exception('Terdapat Stop Clock berdurasi lebih dari 120 menit yang belum disetujui oleh Manager Teknis/Helpdesk (AC-07).');
        }

        // Populate any closing attributes provided
        if (isset($closingData['problem_temuan'])) {
            $tiket->problem_temuan = $closingData['problem_temuan'];
        }
        if (isset($closingData['action_taken'])) {
            $tiket->action_taken = $closingData['action_taken'];
        }
        if (isset($closingData['redaman_sebelum'])) {
            $tiket->redaman_sebelum = $closingData['redaman_sebelum'];
        }
        if (isset($closingData['redaman_sesudah'])) {
            $tiket->redaman_sesudah = $closingData['redaman_sesudah'];
        }
        if (isset($closingData['has_link_up'])) {
            $tiket->has_link_up = (bool) $closingData['has_link_up'];
        }
        $tiket->save();

        // Check closing prerequisites (§7.6, AC-06)
        $prereqs = $this->assetService->checkClosingPrerequisites($tiket);
        if (! $prereqs['passed']) {
            return [
                'tiket' => $tiket,
                'prerequisites' => $prereqs,
            ];
        }

        DB::transaction(function () use ($tiket, $technician): void {
            $now = Carbon::now('Asia/Jakarta');
            $tiket->status = 'PENDING_VERIFIKASI';
            $tiket->pending_verification_at = $now;
            $tiket->closing_awal_by = $technician->id;
            $tiket->closing_awal_at = $now;
            $tiket->save();

            $tiket->statusHistories()->create([
                'from_status' => 'PROSES',
                'to_status' => 'PENDING_VERIFIKASI',
                'changed_by' => $technician->id,
                'reason' => 'Closing Awal disubmit oleh teknisi lapangan',
                'created_at' => $now,
            ]);

            AuditLogger::record(
                action: 'ticket.closing_awal.submit',
                auditable: $tiket,
                newValues: ['status' => 'PENDING_VERIFIKASI'],
                reason: "Closing Awal diajukan oleh {$technician->name}",
                actorId: $technician->id
            );
        });

        return [
            'tiket' => $tiket->fresh(),
            'prerequisites' => $prereqs,
        ];
    }

    /**
     * Reject Closing Awal and return to PROSES (§7.2).
     */
    public function rejectClosing(Tiket $tiket, User $verifier, string $reason): Tiket
    {
        if ($tiket->status !== 'PENDING_VERIFIKASI') {
            throw new Exception("Hanya tiket berstatus PENDING_VERIFIKASI yang dapat ditolak (Status saat ini: {$tiket->status}).");
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan penolakan closing verifikasi wajib diisi.');
        }

        return DB::transaction(function () use ($tiket, $verifier, $reason): Tiket {
            $now = Carbon::now('Asia/Jakarta');
            $tiket->status = 'PROSES';
            $tiket->pending_verification_at = null;
            $tiket->rejection_reason = $reason;
            $tiket->save();

            $tiket->statusHistories()->create([
                'from_status' => 'PENDING_VERIFIKASI',
                'to_status' => 'PROSES',
                'changed_by' => $verifier->id,
                'reason' => "Penolakan verifikasi: {$reason}",
                'created_at' => $now,
            ]);

            AuditLogger::record(
                action: 'ticket.closing.reject',
                auditable: $tiket,
                newValues: ['status' => 'PROSES', 'rejection_reason' => $reason],
                reason: "Verifikasi ditolak oleh {$verifier->name}",
                actorId: $verifier->id
            );

            return $tiket;
        });
    }

    /**
     * Final verify and close ticket, computing net MTTR and SLA status (§7.2, §7.7, AC-08).
     */
    public function verifyAndClose(Tiket $tiket, User $verifier, ?Carbon $closeTime = null): Tiket
    {
        if ($tiket->status !== 'PENDING_VERIFIKASI') {
            throw new Exception("Hanya tiket berstatus PENDING_VERIFIKASI yang dapat diverifikasi (Status saat ini: {$tiket->status}).");
        }

        // Separation of duties check (§4.3)
        if ($tiket->closing_awal_by && $tiket->closing_awal_by === $verifier->id) {
            throw new Exception('Pemisahan tugas (§4.3): Pelaku verifikasi akhir Helpdesk tidak boleh sama dengan teknisi yang melakukan closing awal.');
        }

        return DB::transaction(function () use ($tiket, $verifier, $closeTime): Tiket {
            $now = $closeTime ?? Carbon::now();
            $openTime = $tiket->tanggal_open ?? $tiket->created_at;

            // MTTR bersih = max(0, menit(tanggal_close - tanggal_open) - total_stop_clock_minutes) (§7.7)
            $grossMinutes = (int) abs($openTime->diffInMinutes($now));
            $netMttr = max(0, $grossMinutes - (int) $tiket->total_stop_clock_minutes);

            // SLA status: TEPAT bila net_mttr <= sla_target_minutes, LEBIH bila melebihi (AC-08)
            $slaStatus = ($netMttr <= $tiket->sla_target_minutes) ? 'TEPAT' : 'LEBIH';

            $tiket->status = 'CLOSE';
            $tiket->tanggal_close = $now;
            $tiket->net_mttr_minutes = $netMttr;
            $tiket->sla_status = $slaStatus;
            $tiket->verified_by = $verifier->id;
            $tiket->verified_at = $now;
            $tiket->save();

            $tiket->statusHistories()->create([
                'from_status' => 'PENDING_VERIFIKASI',
                'to_status' => 'CLOSE',
                'changed_by' => $verifier->id,
                'reason' => "Verifikasi akhir selesai: MTTR {$netMttr}m, SLA {$slaStatus}",
                'created_at' => $now,
            ]);

            AuditLogger::record(
                action: 'ticket.verify_close',
                auditable: $tiket,
                newValues: [
                    'status' => 'CLOSE',
                    'net_mttr_minutes' => $netMttr,
                    'sla_status' => $slaStatus,
                ],
                reason: "Tiket ditutup dan diverifikasi oleh {$verifier->name}",
                actorId: $verifier->id
            );

            return $tiket;
        });
    }

    /**
     * Reopen a closed ticket (§7.2).
     */
    public function reopen(Tiket $tiket, User $user, string $reason): Tiket
    {
        if ($tiket->status !== 'CLOSE') {
            throw new Exception("Hanya tiket berstatus CLOSE yang dapat di-reopen (Status saat ini: {$tiket->status}).");
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan reopen tiket wajib diisi.');
        }

        // Limit reopen window to 7 days (§7.2)
        if ($tiket->tanggal_close && $tiket->tanggal_close->diffInDays(now()) > 7) {
            throw new Exception('Batas waktu reopen tiket adalah maksimal 7 hari sejak tanggal penutupan.');
        }

        return DB::transaction(function () use ($tiket, $user, $reason): Tiket {
            $now = Carbon::now('Asia/Jakarta');
            $tiket->status = 'REOPENED';
            $tiket->reopened_count = (int) $tiket->reopened_count + 1;
            $tiket->reopened_at = $now;
            $tiket->reopened_reason = $reason;
            $tiket->save();

            $tiket->statusHistories()->create([
                'from_status' => 'CLOSE',
                'to_status' => 'REOPENED',
                'changed_by' => $user->id,
                'reason' => "Reopen tiket: {$reason}",
                'created_at' => $now,
            ]);

            AuditLogger::record(
                action: 'ticket.reopen',
                auditable: $tiket,
                newValues: ['status' => 'REOPENED', 'reopened_count' => $tiket->reopened_count],
                reason: "Tiket di-reopen oleh {$user->name}",
                actorId: $user->id
            );

            return $tiket;
        });
    }

    /**
     * Cancel an OPEN or PROSES ticket (§7.2).
     */
    public function cancel(Tiket $tiket, User $user, string $reason): Tiket
    {
        if (! in_array($tiket->status, ['OPEN', 'PROSES'], true)) {
            throw new Exception("Tiket berstatus {$tiket->status} tidak dapat dibatalkan.");
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan pembatalan tiket wajib diisi.');
        }

        return DB::transaction(function () use ($tiket, $user, $reason): Tiket {
            $now = Carbon::now('Asia/Jakarta');
            $from = $tiket->status;

            $tiket->status = 'CANCELLED';
            $tiket->sla_status = 'NA';
            $tiket->cancelled_at = $now;
            $tiket->cancelled_reason = $reason;
            $tiket->save();

            $tiket->statusHistories()->create([
                'from_status' => $from,
                'to_status' => 'CANCELLED',
                'changed_by' => $user->id,
                'reason' => "Pembatalan tiket: {$reason}",
                'created_at' => $now,
            ]);

            AuditLogger::record(
                action: 'ticket.cancel',
                auditable: $tiket,
                newValues: ['status' => 'CANCELLED', 'reason' => $reason],
                reason: "Tiket dibatalkan oleh {$user->name}",
                actorId: $user->id
            );

            return $tiket;
        });
    }
}
