<?php

namespace App\Policies;

use App\Models\Tiket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view the ticket (§7.2, AC-05).
     */
    public function view(User $user, Tiket $tiket): bool
    {
        if ($user->hasRole(['super_admin', 'manager_teknisi', 'helpdesk', 'sa_cs', 'ceo', 'noc'])) {
            return true;
        }

        // Technicians can view if assigned to the ticket, or if the ticket is already CLOSED (§7.2)
        if ($tiket->status === 'CLOSE') {
            return true;
        }

        return $tiket->ticketAssignees()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update/write to the ticket (§7.2, AC-05).
     * AC-05: Non-assigned technicians must receive 403 on write attempts!
     */
    public function update(User $user, Tiket $tiket): bool
    {
        if ($user->hasRole(['super_admin', 'manager_teknisi', 'helpdesk', 'noc'])) {
            return true;
        }

        // Technicians can only write if they are assigned to this ticket's team (AC-05)
        return $tiket->ticketAssignees()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the technician can submit Closing Awal (§7.2, §7.6).
     */
    public function closingAwal(User $user, Tiket $tiket): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $tiket->ticketAssignees()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can verify and close the ticket (§7.2, §4.3).
     */
    public function verifyClose(User $user, Tiket $tiket): bool
    {
        if (! $user->can('ticket.verify_close')) {
            return false;
        }

        // Separation of duties (§4.3): cannot verify own closing awal
        if ($tiket->closing_awal_by && $tiket->closing_awal_by === $user->id) {
            return false;
        }

        return true;
    }
}
