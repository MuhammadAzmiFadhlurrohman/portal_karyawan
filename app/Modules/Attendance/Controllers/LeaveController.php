<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Modules\Attendance\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class LeaveController extends Controller
{
    public function index(Request $request, LeaveService $leaveService): View
    {
        /** @var User $user */
        $user = $request->user();
        $year = (int) $request->integer('year', (int) now('Asia/Jakarta')->format('Y'));

        $balance = $leaveService->getOrCreateBalance($user, $year);

        $myLeaves = Leave::query()
            ->with(['supervisorApprover', 'hrdApprover'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get();

        $approvalQueue = collect();
        $allBalances = collect();

        if ($user->can('attendance.leave.approve')) {
            $approvalQueue = Leave::query()
                ->with('user')
                ->whereIn('status', ['pending_supervisor', 'pending_hrd'])
                ->latest()
                ->get();
        }

        if ($user->can('attendance.employee.manage')) {
            $activeUsers = User::query()->where('is_active', true)->orderBy('name')->get();
            foreach ($activeUsers as $u) {
                $leaveService->getOrCreateBalance($u, $year);
            }
            $allBalances = LeaveBalance::query()
                ->with(['user', 'overrider'])
                ->where('year', $year)
                ->orderBy('user_id')
                ->get();
        }

        return view('attendance.leaves', [
            'user' => $user,
            'year' => $year,
            'balance' => $balance,
            'myLeaves' => $myLeaves,
            'approvalQueue' => $approvalQueue,
            'allBalances' => $allBalances,
        ]);
    }

    public function store(Request $request, LeaveService $leaveService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'leave_type' => ['required', 'in:annual,sick,special'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        try {
            $leaveService->requestLeave($user, [
                'leave_type' => (string) $validated['leave_type'],
                'start_date' => (string) $validated['start_date'],
                'end_date' => (string) $validated['end_date'],
                'reason' => (string) $validated['reason'],
            ], $request->file('attachment'));

            return back()->with('status', 'Pengajuan cuti berhasil dikirim dan menunggu persetujuan Atasan (§5.4).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['leave' => $e->getMessage()])->withInput();
        }
    }

    public function approveSupervisor(Request $request, Leave $leave, LeaveService $leaveService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->can('attendance.leave.approve'), 403);

        try {
            $leaveService->approveBySupervisor($leave, $user);

            return back()->with('status', 'Cuti disetujui oleh Atasan dan diteruskan ke HRD (§5.4).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    public function approveHrd(Request $request, Leave $leave, LeaveService $leaveService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->can('attendance.employee.manage') || $user->can('attendance.leave.approve'), 403);

        try {
            $leaveService->approveByHrd($leave, $user);

            return back()->with('status', 'Cuti disetujui final oleh HRD dan saldo cuti telah diperbarui (§5.4).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, Leave $leave, LeaveService $leaveService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($leave->user_id === $user->id || $user->can('attendance.employee.manage'), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $leaveService->cancelLeave($leave, $user, (string) $validated['reason']);

            return back()->with('status', 'Pengajuan cuti berhasil dibatalkan sebelum tanggal mulai dan saldo cuti dikembalikan (§5.4).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    public function overrideBalance(Request $request, LeaveService $leaveService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('attendance.employee.manage'), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'remaining_days' => ['required', 'integer', 'min:0', 'max:36'],
            'is_locked_override' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        /** @var User $targetUser */
        $targetUser = User::query()->findOrFail((int) $validated['user_id']);

        $leaveService->overrideBalance(
            targetUser: $targetUser,
            year: (int) $validated['year'],
            newRemainingDays: (int) $validated['remaining_days'],
            lockOverride: $request->boolean('is_locked_override'),
            actor: $actor,
            reason: (string) $validated['reason']
        );

        return back()->with('status', "Saldo cuti {$targetUser->name} berhasil di-override dan dicatat ke audit log (§5.4).");
    }
}
