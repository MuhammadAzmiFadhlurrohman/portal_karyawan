<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use App\Models\PeriodClosing;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use App\Modules\Attendance\Services\LeaveService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DeductionAndCorrectionController extends Controller
{
    public function index(Request $request, DeductionService $deductionService): View
    {
        /** @var User $user */
        $user = $request->user();
        $year = (int) $request->integer('year', (int) now('Asia/Jakarta')->format('Y'));
        $month = (int) $request->integer('month', (int) now('Asia/Jakarta')->format('n'));

        $selectedUser = $user;
        if ($request->filled('user_id') && ($user->can('attendance.employee.manage') || $user->can('attendance.deduction.lock_period'))) {
            $found = User::query()->find((int) $request->input('user_id'));
            if ($found) {
                $selectedUser = $found;
            }
        }

        $monthlyReport = $deductionService->calculateUserMonthlyReport($selectedUser, $year, $month);
        $sixMonthTrend = $deductionService->getSixMonthTrend($selectedUser, $year, $month);

        $myCorrections = AttendanceCorrection::query()
            ->with(['supervisorApprover', 'hrdApprover'])
            ->where('user_id', $selectedUser->id)
            ->latest()
            ->limit(20)
            ->get();

        $correctionQueue = collect();
        if ($user->can('attendance.correction.approve')) {
            $correctionQueue = AttendanceCorrection::query()
                ->with('user')
                ->whereIn('status', ['pending_supervisor', 'pending_hrd'])
                ->latest()
                ->get();
        }

        $periodClosings = PeriodClosing::query()
            ->with('closer')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        $allEmployees = ($user->can('attendance.employee.manage') || $user->can('attendance.deduction.lock_period'))
            ? User::query()->where('is_active', true)->orderBy('name')->get()
            : collect([$user]);

        return view('attendance.deductions', [
            'user' => $user,
            'selectedUser' => $selectedUser,
            'allEmployees' => $allEmployees,
            'year' => $year,
            'month' => $month,
            'monthlyReport' => $monthlyReport,
            'sixMonthTrend' => $sixMonthTrend,
            'myCorrections' => $myCorrections,
            'correctionQueue' => $correctionQueue,
            'periodClosings' => $periodClosings,
        ]);
    }

    public function submitCorrection(Request $request, DeductionService $deductionService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'requested_status' => ['required', 'in:present,late'],
            'requested_attendance_type' => ['required', 'in:office,field,remote'],
            'requested_clock_in_time' => ['required', 'date_format:H:i'],
            'requested_clock_out_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        try {
            $deductionService->submitCorrection($user, [
                'date' => (string) $validated['date'],
                'requested_status' => (string) $validated['requested_status'],
                'requested_attendance_type' => (string) $validated['requested_attendance_type'],
                'requested_clock_in_time' => (string) $validated['requested_clock_in_time'].':00',
                'requested_clock_out_time' => (string) $validated['requested_clock_out_time'].':00',
                'reason' => (string) $validated['reason'],
            ], $request->file('attachment'));

            return back()->with('status', 'Pengajuan koreksi absensi berhasil dikirim dan menunggu persetujuan Atasan (§5.6).');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['correction' => $e->getMessage()])->withInput();
        }
    }

    protected function authorizeApproval(User $user): void
    {
        $canApprove = $user->can('attendance.correction.approve') 
            || $user->can('gomsn.pengajuan.approve_atasan')
            || $user->can('attendance.leave.approve')
            || $user->can('attendance.employee.manage')
            || $user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin', 'hrd']);

        abort_unless($canApprove, 403, 'Akses khusus Atasan / HRD.');
    }

    public function approvals(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorizeApproval($user);

        $status = (string) $request->input('status', 'pending');

        $query = AttendanceCorrection::query()
            ->with(['user', 'attendance.shift', 'attendance.officeLocation', 'supervisorApprover', 'hrdApprover', 'rejectedBy']);

        if (! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
            if ($user->division) {
                $query->whereHas('user', fn ($q) => $q->where('division', $user->division));
            }
            $query->where('user_id', '!=', $user->id);
        }

        if ($status === 'pending') {
            $query->whereIn('status', ['pending_supervisor', 'pending_hrd']);
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status === 'rejected') {
            $query->where('status', 'rejected');
        }

        $corrections = $query->latest()->paginate(15)->withQueryString();

        $pendingCount = AttendanceCorrection::query()
            ->whereIn('status', ['pending_supervisor', 'pending_hrd'])
            ->when(! $user->hasRole(['super_admin', 'admin', 'hrd']), function ($q) use ($user) {
                $q->where('user_id', '!=', $user->id);
                if ($user->division) {
                    $q->whereHas('user', fn ($uq) => $uq->where('division', $user->division));
                }
            })
            ->count();

        return view('attendance.corrections.approvals', [
            'user' => $user,
            'status' => $status,
            'corrections' => $corrections,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function approveSupervisor(
        Request $request,
        AttendanceCorrection $attendanceCorrection,
        DeductionService $deductionService,
        LeaveService $leaveService
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $this->authorizeApproval($user);

        try {
            $deductionService->approveCorrectionBySupervisor($attendanceCorrection, $user, $leaveService);

            return back()->with('status', 'Koreksi absensi berhasil disetujui. Data absensi karyawan telah diperbarui sesuai pengajuan.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['correction' => $e->getMessage()]);
        }
    }

    public function reject(
        Request $request,
        AttendanceCorrection $attendanceCorrection,
        DeductionService $deductionService
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $this->authorizeApproval($user);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        try {
            $deductionService->rejectCorrection($attendanceCorrection, $user, (string) $validated['reason']);

            return back()->with('status', 'Pengajuan koreksi absensi telah ditolak.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['correction' => $e->getMessage()]);
        }
    }

    public function approveHrd(
        Request $request,
        AttendanceCorrection $attendanceCorrection,
        DeductionService $deductionService,
        LeaveService $leaveService
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->can('attendance.employee.manage') || $user->can('attendance.correction.approve'), 403);

        try {
            $updated = $deductionService->approveCorrectionByHrd($attendanceCorrection, $user, $leaveService);
            $msg = $updated->is_post_closing_adjustment
                ? 'Koreksi disetujui final oleh HRD. Karena periode bulan terkait sudah dikunci, selisih potongan otomatis dicatat sebagai adjustment bulan berikutnya (§5.5, AC-09).'
                : 'Koreksi disetujui final oleh HRD, data Attendance diperbarui (source=correction), dan potongan dihitung ulang (§5.6).';

            return back()->with('status', $msg);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['correction' => $e->getMessage()]);
        }
    }

    public function lockPeriod(Request $request, DeductionService $deductionService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('attendance.deduction.lock_period'), 403);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $closing = $deductionService->lockMonthlyPeriod(
            year: (int) $validated['year'],
            month: (int) $validated['month'],
            actor: $actor,
            notes: $validated['notes'] ?? null
        );

        return back()->with(
            'status',
            "Periode potongan {$closing->year}-".sprintf('%02d', $closing->month).' berhasil dikunci oleh HRD (AC-09). Perubahan tarif di masa depan tidak akan mengubah periode ini.'
        );
    }

    public function updateEmployeeRate(Request $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('attendance.employee.manage'), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'potongan_alpa' => ['required', 'numeric', 'min:0', 'max:50000000'],
        ]);

        /** @var User $targetUser */
        $targetUser = User::query()->findOrFail((int) $validated['user_id']);
        $oldRate = $targetUser->potongan_alpa;
        $targetUser->update([
            'potongan_alpa' => $validated['potongan_alpa'],
        ]);

        AuditLogger::record(
            action: 'user.potongan_alpa_updated',
            auditable: $targetUser,
            oldValues: ['potongan_alpa' => $oldRate],
            newValues: ['potongan_alpa' => $validated['potongan_alpa']],
            reason: 'Pembaruan tarif potongan gaji alpa per karyawan oleh HRD',
            actorId: $actor->id
        );

        return back()->with(
            'status',
            "Tarif potongan gaji alpa untuk {$targetUser->name} berhasil diperbarui menjadi Rp " . number_format((float) $validated['potongan_alpa'], 2, ',', '.') . " per hari."
        );
    }
}
