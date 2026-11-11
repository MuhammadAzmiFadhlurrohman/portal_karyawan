<?php

namespace App\Modules\Ticketing\Services;

use App\Models\TicketAssignee;
use App\Models\TicketHandover;
use App\Models\Tiket;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HandoverService
{
    /**
     * Create a shift handover for a ticket (§7.5).
     */
    public function createHandover(
        Tiket $tiket,
        string $shiftFrom,
        string $shiftTo,
        User $sender,
        User $recipient,
        string $notes
    ): TicketHandover {
        if (empty(trim($notes))) {
            throw new InvalidArgumentException('Catatan handover wajib diisi.');
        }

        if (in_array($tiket->status, ['CLOSE', 'CANCELLED'], true)) {
            throw new Exception("Tidak dapat melakukan handover untuk tiket berstatus {$tiket->status}.");
        }

        return DB::transaction(function () use ($tiket, $shiftFrom, $shiftTo, $sender, $recipient, $notes): TicketHandover {
            $now = Carbon::now('Asia/Jakarta');

            // Build auto-summary (§7.5)
            $lastChronology = $tiket->chronologies()->latest('created_at')->first();
            $activeStop = $tiket->activeStopClock;
            $stopStatus = $activeStop ? "Stop Clock AKTIF ({$activeStop->reason})" : 'Tidak ada Stop Clock aktif';
            $lastAction = $lastChronology ? "Kronologis terakhir [{$lastChronology->category}]: {$lastChronology->message}" : 'Belum ada kronologis';

            $summary = "Ringkasan Sistem: Status={$tiket->status}, {$stopStatus}. {$lastAction}. Target SLA={$tiket->sla_target_minutes}m.";

            $handover = TicketHandover::query()->create([
                'tiket_id' => $tiket->id,
                'shift_from' => $shiftFrom,
                'shift_to' => $shiftTo,
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'notes' => $notes,
                'auto_summary' => $summary,
            ]);

            // Automatically add recipient to ticket_assignees if not already assigned (§7.5)
            $isAssigned = TicketAssignee::query()
                ->where('tiket_id', $tiket->id)
                ->where('user_id', $recipient->id)
                ->exists();

            if (! $isAssigned) {
                TicketAssignee::query()->create([
                    'tiket_id' => $tiket->id,
                    'user_id' => $recipient->id,
                    'role_in_team' => 'member',
                    'assigned_by' => $sender->id,
                    'assigned_at' => $now,
                ]);
            }

            AuditLogger::record(
                action: 'ticket.handover.create',
                auditable: $handover,
                newValues: ['shift_from' => $shiftFrom, 'shift_to' => $shiftTo, 'recipient' => $recipient->name],
                reason: "Handover dibuat oleh {$sender->name}",
                actorId: $sender->id
            );

            return $handover;
        });
    }

    /**
     * Acknowledge handover by the recipient (§7.5).
     */
    public function acknowledge(TicketHandover $handover, User $recipient): TicketHandover
    {
        if ($handover->recipient_id !== $recipient->id) {
            throw new Exception('Hanya penerima shift yang dapat mengonfirmasi handover.');
        }

        if ($handover->acknowledged_at !== null) {
            return $handover;
        }

        $handover->acknowledged_at = Carbon::now('Asia/Jakarta');
        $handover->save();

        AuditLogger::record(
            action: 'ticket.handover.acknowledge',
            auditable: $handover,
            newValues: ['acknowledged_at' => $handover->acknowledged_at->toDateTimeString()],
            reason: "Handover dikonfirmasi oleh {$recipient->name}",
            actorId: $recipient->id
        );

        return $handover;
    }

    /**
     * Check and escalate handovers unconfirmed > 15 minutes (§7.5).
     */
    public function escalatePendingHandovers(): int
    {
        $cutoff = Carbon::now('Asia/Jakarta')->subMinutes(15);
        $pending = TicketHandover::query()
            ->whereNull('acknowledged_at')
            ->whereNull('escalated_at')
            ->where('created_at', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($pending as $item) {
            $item->escalated_at = Carbon::now('Asia/Jakarta');
            $item->save();

            AuditLogger::record(
                action: 'ticket.handover.escalate',
                auditable: $item,
                newValues: ['escalated_at' => $item->escalated_at->toDateTimeString()],
                reason: 'Eskalasi handover tidak dikonfirmasi dalam 15 menit ke Manager Teknis',
                actorId: null
            );
            $count++;
        }

        return $count;
    }
}
