<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShiftSwap;
use App\Models\User;
use App\Modules\Attendance\Services\ShiftSwapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ShiftSwapController extends Controller
{
    public function index(Request $request, ShiftSwapService $shiftSwapService): View
    {
        /** @var User $user */
        $user = $request->user();
        $shiftSwapService->expireStaleRequests();

        $divisionPeers = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->when($user->division, fn ($q) => $q->where('division', $user->division))
            ->orderBy('name')
            ->get();

        $mySwaps = ShiftSwap::query()
            ->with(['requester', 'targetUser', 'reqD1Shift', 'tarD1Shift', 'reqD2Shift', 'tarD2Shift', 'approver'])
            ->where(function ($q) use ($user): void {
                $q->where('requester_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->latest()
            ->limit(25)
            ->get();

        $supervisorQueue = collect();
        if ($user->can('attendance.leave.approve') || $user->can('attendance.schedule.generate')) {
            $supervisorQueue = ShiftSwap::query()
                ->with(['requester', 'targetUser', 'reqD1Shift', 'tarD1Shift', 'reqD2Shift', 'tarD2Shift'])
                ->where('status', 'pending_supervisor')
                ->latest()
                ->get();
        }

        return view('attendance.swaps', [
            'user' => $user,
            'divisionPeers' => $divisionPeers,
            'mySwaps' => $mySwaps,
            'supervisorQueue' => $supervisorQueue,
        ]);
    }

    public function store(Request $request, ShiftSwapService $shiftSwapService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'requester_date' => ['required', 'date'],
            'target_date' => ['required', 'date'],
            'is_backup' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $shiftSwapService->createRequest($user, [
                'target_user_id' => (int) $validated['target_user_id'],
                'requester_date' => (string) $validated['requester_date'],
                'target_date' => (string) $validated['target_date'],
                'is_backup' => $request->boolean('is_backup'),
                'reason' => (string) $validated['reason'],
            ]);

            return back()->with('status', 'Permintaan tukar shift berhasil diajukan. Menunggu persetujuan rekan target (§5.3).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['swap' => $e->getMessage()])->withInput();
        }
    }

    public function approveTarget(Request $request, ShiftSwap $shiftSwap, ShiftSwapService $shiftSwapService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $shiftSwapService->approveByTarget($shiftSwap, $user);

            return back()->with('status', 'Anda telah menyetujui permintaan tukar shift. Diteruskan ke Atasan/Admin (§5.3).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['swap' => $e->getMessage()]);
        }
    }

    public function approveSupervisor(Request $request, ShiftSwap $shiftSwap, ShiftSwapService $shiftSwapService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->can('attendance.leave.approve') || $user->can('attendance.schedule.generate'), 403);

        try {
            $updated = $shiftSwapService->approveBySupervisor($shiftSwap, $user);
            if ($updated->status === 'rejected') {
                return back()->withErrors(['swap' => (string) $updated->rejection_reason]);
            }

            return back()->with('status', 'Tukar shift berhasil disetujui dan 4 slot jadwal telah diperbarui secara atomik (§5.3).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['swap' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, ShiftSwap $shiftSwap, ShiftSwapService $shiftSwapService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $shiftSwapService->reject($shiftSwap, $user, (string) $validated['reason']);

            return back()->with('status', 'Permintaan tukar shift telah ditolak.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['swap' => $e->getMessage()]);
        }
    }
}
