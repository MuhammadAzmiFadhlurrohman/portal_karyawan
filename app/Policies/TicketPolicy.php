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
        if ($user->hasRole(['super_admin', 'admin', 'manager_teknisi', 'noc', 'atasan_teknik', 'atasan_noc', 'atasan', 'atasan_umum', 'ceo', 'helpdesk']) || $user->isAtasan()) {
            return true;
        }

        // Technicians can view if assigned to the ticket, or if the ticket is already CLOSED (§7.2)
        if ($tiket->status === 'CLOSE') {
            return true;
        }

        if ($tiket->ticketAssignees()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if (!empty($tiket->team_members) && is_array($tiket->team_members)) {
            foreach ($tiket->team_members as $member) {
                if (strcasecmp(trim((string)$member), trim($user->name)) === 0) {
                    return true;
                }
            }
        }

        return $user->division === 'Teknik' || $user->hasRole(['teknisi', 'karyawan_teknik']);
    }

    /**
     * Determine whether the user can update/write to the ticket (§7.2, AC-05).
     * AC-05: Non-assigned technicians must receive 403 on write attempts!
     * Management/Supervisors (Atasan Teknik, Manager, NOC, Admin) can coordinate in all tickets.
     */
    public function update(User $user, Tiket $tiket): bool
    {
        if ($user->hasRole(['super_admin', 'admin', 'manager_teknisi', 'noc', 'atasan_teknik', 'atasan_noc', 'atasan', 'atasan_umum', 'ceo', 'helpdesk']) || $user->isAtasan()) {
            return true;
        }

        // Technicians can only write if they are assigned to this ticket's team (AC-05)
        if ($tiket->ticketAssignees()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if (!empty($tiket->team_members) && is_array($tiket->team_members)) {
            foreach ($tiket->team_members as $member) {
                if (strcasecmp(trim((string)$member), trim($user->name)) === 0) {
                    return true;
                }
            }
        }

        // If ticket has no assignees yet, allow technician from Teknik division to write
        if ($tiket->ticketAssignees()->doesntExist()) {
            return $user->division === 'Teknik' || $user->hasRole(['teknisi', 'karyawan_teknik']);
        }

        return false;
    }

    /**
     * Determine whether the technician can submit Closing Awal (§7.2, §7.6).
     */
    public function closingAwal(User $user, Tiket $tiket): bool
    {
        // 1. Management, Supervisors, NOC, Helpdesk, and Admins can always coordinate and submit/save resume
        if ($user->hasRole(['super_admin', 'admin', 'manager_teknisi', 'noc', 'atasan_teknik', 'atasan_noc', 'atasan', 'atasan_umum', 'ceo', 'helpdesk']) || $user->isAtasan()) {
            return true;
        }

        // 2. Technicians assigned in ticket_assignees
        if ($tiket->ticketAssignees()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // 3. Technicians listed in team_members (saved or submitted)
        $allMembers = array_merge(
            is_array($tiket->team_members) ? $tiket->team_members : [],
            is_array(request('team_members')) ? request('team_members') : []
        );
        foreach ($allMembers as $member) {
            if (!empty($member) && strcasecmp(trim((string)$member), trim($user->name)) === 0) {
                return true;
            }
        }

        // 4. Any technician in Teknik division handling the ticket
        return $user->division === 'Teknik' || $user->hasRole(['teknisi', 'karyawan_teknik']);
    }

    /**
     * Determine whether the user can dispatch technicians to the ticket.
     * Penugasan teknisi diberikan oleh role atasan teknik dan atasan umum.
     * NOC yang membuka tiket dilarang menugaskan teknisi.
     */
    public function dispatch(User $user, Tiket $tiket): bool
    {
        // NOC dan pembuat tiket dilarang menugaskan teknisi
        if ($user->hasRole(['noc', 'atasan_noc']) || $user->division === 'NOC' || $user->id === $tiket->created_by) {
            return false;
        }

        return $user->hasRole(['super_admin', 'admin', 'atasan_teknik', 'atasan_umum', 'atasan', 'manager_teknisi']);
    }

    /**
     * Determine whether the user can verify and close the ticket (§7.2, §4.3).
     */
    public function verifyClose(User $user, Tiket $tiket): bool
    {
        if (! $user->can('ticket.verify_close') && ! $user->hasRole(['super_admin', 'admin', 'noc', 'manager_teknisi'])) {
            return false;
        }

        // Separation of duties (§4.3): cannot verify own closing awal
        if ($tiket->closing_awal_by && $tiket->closing_awal_by === $user->id) {
            return false;
        }

        return true;
    }
}
