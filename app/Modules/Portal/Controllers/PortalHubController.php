<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApprovalDelegation;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\ShiftSwap;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserSession;
use App\Modules\Attendance\Services\LeaveService;
use App\Services\AuditLogger;
use App\Services\DashboardStatsService;
use App\Services\DeviceSessionService;
use App\Services\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PortalHubController extends Controller
{
    public function index(Request $request, LeaveService $leaveService, DashboardStatsService $dashboardStatsService): View
    {
        /** @var User $user */
        $user = $request->user();

        $stats = $dashboardStatsService->getStats($user);

        $hubMetrics = Cache::remember("portal_hub_metrics:user:{$user->id}", 15, function () use ($user, $leaveService) {
            $nowJkt = now('Asia/Jakarta');
            $todayStr = $nowJkt->toDateString();
            $year = (int) $nowJkt->format('Y');
            $month = (int) $nowJkt->format('n');

            $todaySchedule = MonthlySchedule::query()
                ->with(['shift', 'officeLocation'])
                ->where('user_id', $user->id)
                ->whereDate('date', $todayStr)
                ->first();

            $todayAttendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $todayStr)
                ->first();

            $monthlyQuota = SystemSettingService::getInt('leave.monthly_max_days', 3);
            $balance = $leaveService->getOrCreateBalance($user, $year);

            $monthlyUsedLeave = (int) Leave::query()
                ->where('user_id', $user->id)
                ->where('leave_type', 'annual')
                ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->sum('working_days');

            $monthAttendances = Attendance::query()
                ->where('user_id', $user->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $presentDays = $monthAttendances->whereIn('status', ['present', 'late'])->count();
            $lateMinutes = (int) $monthAttendances->sum('late_minutes');
            $scheduledDays = max(1, MonthlySchedule::query()
                ->where('user_id', $user->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->count());

            $pendingHrisApprovals = ShiftSwap::query()
                ->where(function ($q) use ($user): void {
                    $q->where('target_user_id', $user->id)->where('status', 'pending_target');
                })
                ->count()
                + Leave::query()->whereIn('status', ['pending_supervisor', 'pending_hrd'])->count()
                + AttendanceCorrection::query()->whereIn('status', ['pending_supervisor', 'pending_hrd'])->count();

            $shiftCode = $todaySchedule && $todaySchedule->shift ? $todaySchedule->shift->code : 'P';
            $shiftName = $todaySchedule && $todaySchedule->shift ? $todaySchedule->shift->name : 'Shift Pagi';
            $officeName = $todaySchedule && $todaySchedule->officeLocation ? $todaySchedule->officeLocation->name : 'Kantor Pusat Bandung / Soreang';
            $radiusM = $todaySchedule && $todaySchedule->officeLocation ? (int) $todaySchedule->officeLocation->radius_m : SystemSettingService::getInt('geofence.default_radius_m', 50);

            return [
                'attendance_today' => [
                    'status' => $todayAttendance ? strtoupper($todayAttendance->status) : 'BELUM CLOCK-IN',
                    'shift_code' => $shiftCode,
                    'shift_label' => "{$shiftName} ({$shiftCode})",
                    'office_name' => $officeName,
                    'geofence_radius_m' => $radiusM,
                ],
                'monthly_attendance' => [
                    'percentage' => min(100.0, round(($presentDays / $scheduledDays) * 100, 1)),
                    'present_days' => $presentDays,
                    'scheduled_days' => $scheduledDays,
                    'late_minutes' => $lateMinutes,
                ],
                'leave_balance' => [
                    'annual_remaining' => $balance->remaining_days,
                    'annual_total' => $balance->annual_quota,
                    'monthly_remaining' => max(0, $monthlyQuota - $monthlyUsedLeave),
                    'monthly_max' => $monthlyQuota,
                ],
                'pending_hris_approvals' => $pendingHrisApprovals,
                'tickets_summary' => [
                    'open' => 2,
                    'proses' => 3,
                    'pending_verifikasi' => 1,
                    'close_today' => 9,
                    'sla_tepat_rate' => 94.2,
                ],
                'gomsn_summary' => [
                    'active_pengajuan' => 2,
                    'pending_realisasi' => 0,
                    'approved_lembur_hours' => 6.5,
                    'active_tasks' => 3,
                ],
            ];
        });

        $effectiveApprover = $user->resolveEffectiveApprover('attendance');
        $activeSessionsCount = UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->count();
        $approvedDevicesCount = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $pendingDevicesCount = $user->can('portal.sessions.manage')
            ? UserDevice::query()->where('status', 'pending')->count()
            : UserDevice::query()->where('user_id', $user->id)->where('status', 'pending')->count();

        return view('portal.hub', [
            'user' => $user,
            'stats' => $stats,
            'metrics' => $hubMetrics,
            'effectiveApprover' => $effectiveApprover,
            'activeSessionsCount' => $activeSessionsCount,
            'approvedDevicesCount' => $approvedDevicesCount,
            'pendingDevicesCount' => $pendingDevicesCount,
        ]);
    }

    public function systemSettings(Request $request): View
    {
        abort_unless($request->user()?->can('portal.settings.manage'), 403);

        $allSettings = SystemSetting::query()
            ->with('updater')
            ->orderBy('group')
            ->orderBy('key')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        $currentSettings = $allSettings->unique('key')->values();
        $historyByKey = $allSettings->groupBy('key');

        return view('portal.settings', [
            'currentSettings' => $currentSettings,
            'historyByKey' => $historyByKey,
        ]);
    }

    public function updateSystemSetting(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('portal.settings.manage'), 403);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'value' => ['required', 'string', 'max:1000'],
            'effective_from' => ['required', 'date'],
            'change_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        SystemSettingService::set(
            key: $validated['key'],
            value: $validated['value'],
            reason: $validated['change_reason'],
            effectiveFrom: Carbon::parse($validated['effective_from'])
        );

        return back()->with('status', "Parameter `{$validated['key']}` berhasil diperbarui dan dicatat ke jejak audit.");
    }

    public function sessionsAndDevices(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $canManageAll = $user->can('portal.sessions.manage');

        $sessionsQuery = UserSession::query()->with(['user', 'device'])->latest('last_activity_at');
        $devicesQuery = UserDevice::query()->with(['user', 'approver'])->latest('updated_at');

        if (! $canManageAll) {
            $sessionsQuery->where('user_id', $user->id);
            $devicesQuery->where('user_id', $user->id);
        }

        $delegations = ApprovalDelegation::query()
            ->with(['delegator', 'delegate'])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('portal.sessions', [
            'sessions' => $sessionsQuery->limit(50)->get(),
            'devices' => $devicesQuery->limit(50)->get(),
            'delegations' => $delegations,
            'canManageAll' => $canManageAll,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'employee_code', 'position']),
        ]);
    }

    public function revokeSession(
        Request $request,
        UserSession $userSession,
        DeviceSessionService $deviceSessionService
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless(
            $actor->id === $userSession->user_id || $actor->can('portal.sessions.manage'),
            403
        );

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $deviceSessionService->revokeSession($userSession, $validated['reason'], $actor->id);

        return back()->with('status', 'Sesi perangkat berhasil dihentikan (Force Logout) dan dicatat pada Audit Log.');
    }

    public function approveDevice(Request $request, UserDevice $userDevice): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('portal.sessions.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,revoked'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $oldStatus = $userDevice->status;
        $userDevice->update([
            'status' => $validated['status'],
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        AuditLogger::record(
            action: "device.status_{$validated['status']}",
            auditable: $userDevice,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $validated['status']],
            reason: $validated['reason'],
            actorId: $actor->id
        );

        return back()->with('status', "Status perangkat `{$userDevice->device_name}` diubah menjadi `{$validated['status']}`.");
    }

    public function storeDelegation(Request $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $validated = $request->validate([
            'delegator_id' => ['required', 'exists:users,id'],
            'delegate_id' => ['required', 'exists:users,id', 'different:delegator_id'],
            'module' => ['required', 'in:all,attendance,ticketing,gomsn'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $actor->can('portal.sessions.manage') && (int) $validated['delegator_id'] !== $actor->id) {
            abort(403, 'Anda hanya dapat membuat delegasi untuk diri sendiri kecuali memiliki hak HRD/Admin.');
        }

        $delegation = ApprovalDelegation::query()->create(array_merge($validated, [
            'is_active' => true,
            'created_by' => $actor->id,
        ]));

        AuditLogger::record(
            action: 'approval_delegation.created',
            auditable: $delegation,
            newValues: $validated,
            reason: $validated['reason'],
            actorId: $actor->id
        );

        return back()->with('status', 'Delegasi persetujuan atasan berhasil disimpan.');
    }

    public function auditLogs(Request $request): View
    {
        abort_unless($request->user()?->can('portal.audit.view'), 403);

        $query = AuditLog::query()->with('user')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action').'%');
        }

        return view('portal.audit-logs', [
            'logs' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function profile(Request $request): View
    {
        $user = $request->user()->load(['supervisor']);

        return view('portal.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32', 'unique:users,phone,' . $user->id],
            'bank' => ['nullable', 'string', 'max:100'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'atas_nama' => ['nullable', 'string', 'max:100'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $userData = [
            'phone' => $validated['phone'],
            'bank' => $validated['bank'] ?? null,
            'no_rekening' => $validated['no_rekening'] ?? null,
            'atas_nama' => $validated['atas_nama'] ?? null,
        ];

        if (! empty($validated['new_password'])) {
            $userData['password'] = \Illuminate\Support\Facades\Hash::make($validated['new_password']);
            $userData['password_changed_at'] = now();
        }

        $user->update($userData);

        AuditLogger::log(
            action: 'profile.updated',
            targetType: 'User',
            targetId: $user->id,
            details: ['updated_fields' => array_keys($userData)],
            actorId: $user->id
        );

        return back()->with('status', 'Profil dan informasi akun Anda berhasil diperbarui.');
    }
}
