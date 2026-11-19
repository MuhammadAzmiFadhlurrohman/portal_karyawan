<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Modules\Attendance\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

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

    public function approvals(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $isHrdAuthorized = $user->can('attendance.employee.manage') 
            || $user->can('attendance.leave.approve') 
            || $user->hasRole(['hrd', 'super_admin', 'admin']);

        abort_unless($isHrdAuthorized, 403, 'Akses khusus HRD dan Manajemen.');

        $status = (string) $request->input('status', 'pending');
        $search = (string) $request->input('search', '');
        $divisi = (string) $request->input('divisi', 'all');
        $leaveType = (string) $request->input('type', 'all');

        $query = Leave::query()
            ->with(['user', 'supervisorApprover', 'hrdApprover', 'rejectedBy']);

        if ($status === 'pending') {
            $query->whereIn('status', ['pending_hrd', 'pending_supervisor']);
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status === 'rejected') {
            $query->where('status', 'rejected');
        }

        if ($search !== '') {
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($divisi !== 'all' && $divisi !== '') {
            $query->whereHas('user', function ($uq) use ($divisi) {
                $uq->where('division', $divisi);
            });
        }

        if ($leaveType !== 'all' && $leaveType !== '') {
            $query->where('leave_type', $leaveType);
        }

        $leaves = $query->latest('id')->paginate(15)->withQueryString();

        $pendingCount = Leave::query()
            ->whereIn('status', ['pending_hrd', 'pending_supervisor'])
            ->count();

        $approvedCount = Leave::query()
            ->where('status', 'approved')
            ->count();

        $rejectedCount = Leave::query()
            ->where('status', 'rejected')
            ->count();

        $divisions = User::query()
            ->whereNotNull('division')
            ->where('division', '!=', '')
            ->distinct()
            ->pluck('division')
            ->sort()
            ->values();

        return view('attendance.leaves.approvals', [
            'user' => $user,
            'leaves' => $leaves,
            'status' => $status,
            'search' => $search,
            'divisi' => $divisi,
            'leaveType' => $leaveType,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'divisions' => $divisions,
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

            return back()->with('status', 'Pengajuan cuti/izin berhasil dikirim dan menunggu persetujuan HRD.');
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
        abort_unless($user->can('attendance.employee.manage') || $user->can('attendance.leave.approve') || $user->hasRole(['hrd', 'super_admin', 'admin']), 403);

        try {
            $leaveService->approveByHrd($leave, $user);

            return back()->with('status', 'Pengajuan cuti/izin berhasil disetujui dan saldo cuti telah diperbarui.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, Leave $leave, LeaveService $leaveService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->can('attendance.employee.manage') || $user->can('attendance.leave.approve') || $user->hasRole(['hrd', 'super_admin', 'admin']), 403);

        $validated = $request->validate([
            'rejected_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        try {
            $leaveService->rejectByHrd($leave, $user, (string) $validated['rejected_reason']);

            return back()->with('status', 'Pengajuan cuti/izin berhasil ditolak.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    public function downloadAttachment(Request $request, Leave $leave): Response
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless(
            $user->id === $leave->user_id || 
            $user->can('attendance.employee.manage') || 
            $user->can('attendance.leave.approve') ||
            $user->hasRole(['hrd', 'super_admin', 'admin']),
            403
        );

        if (! $leave->attachment_path) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        if (Storage::disk('private')->exists($leave->attachment_path)) {
            return Storage::disk('private')->response($leave->attachment_path);
        }

        if (Storage::disk('public')->exists($leave->attachment_path)) {
            return Storage::disk('public')->response($leave->attachment_path);
        }

        abort(404, 'File lampiran tidak tersedia di server storage.');
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
