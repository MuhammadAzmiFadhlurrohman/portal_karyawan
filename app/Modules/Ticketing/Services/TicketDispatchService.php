<?php

namespace App\Modules\Ticketing\Services;

use App\Models\TicketAssignee;
use App\Models\Tiket;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class TicketDispatchService
{
    /**
     * Dispatch ticket by assigning Lead and team members (§7.2).
     *
     * @param  list<int>  $memberIds
     * @return array{tiket: Tiket, overload_warnings: list<string>}
     */
    public function dispatch(Tiket $tiket, int $leadId, array $memberIds, User $dispatcher): array
    {
        if (in_array($tiket->status, ['CLOSE', 'CANCELLED'], true)) {
            throw new Exception("Tiket berstatus {$tiket->status} tidak dapat di-dispatch.");
        }

        $allUserIds = array_unique(array_merge([$leadId], $memberIds));
        $overloadWarnings = [];

        // Check technician overload (§7.2): warning if technician has >= 3 active tickets
        foreach ($allUserIds as $uId) {
            $user = User::query()->find($uId);
            if (! $user) {
                continue;
            }

            $activeCount = TicketAssignee::query()
                ->where('user_id', $uId)
                ->whereHas('ticket', function ($q) use ($tiket) {
                    $q->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                        ->where('id', '!=', $tiket->id);
                })
                ->count();

            if ($activeCount >= 3) {
                $overloadWarnings[] = "Teknisi {$user->name} ({$user->employee_code}) saat ini menangani {$activeCount} tiket aktif (Beban tinggi).";
            }
        }

        DB::transaction(function () use ($tiket, $leadId, $memberIds, $dispatcher): void {
            $now = Carbon::now('Asia/Jakarta');

            // Clear previous assignees if re-dispatching
            $tiket->ticketAssignees()->delete();

            // Insert Lead
            TicketAssignee::query()->create([
                'tiket_id' => $tiket->id,
                'user_id' => $leadId,
                'role_in_team' => 'lead',
                'assigned_by' => $dispatcher->id,
                'assigned_at' => $now,
            ]);

            // Insert Members
            foreach ($memberIds as $mId) {
                if ($mId === $leadId) {
                    continue;
                }
                TicketAssignee::query()->create([
                    'tiket_id' => $tiket->id,
                    'user_id' => $mId,
                    'role_in_team' => 'member',
                    'assigned_by' => $dispatcher->id,
                    'assigned_at' => $now,
                ]);
            }

            AuditLogger::record(
                action: 'ticket.dispatch',
                auditable: $tiket,
                newValues: ['lead_id' => $leadId, 'members' => $memberIds],
                reason: "Tiket di-dispatch oleh {$dispatcher->name}",
                actorId: $dispatcher->id
            );
        });

        return [
            'tiket' => $tiket->fresh(['assignees']),
            'overload_warnings' => $overloadWarnings,
        ];
    }
}
