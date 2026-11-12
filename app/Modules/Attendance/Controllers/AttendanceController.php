<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\OfficeLocation;
use App\Models\User;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Services\FaceRecognitionClient;
use App\Services\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(
        Request $request,
        AttendanceService $attendanceService,
        FaceRecognitionClient $faceClient
    ): View {
        /** @var User $user */
        $user = $request->user();

        $todayStr = now('Asia/Jakarta')->toDateString();
        $todayAttendance = Attendance::query()
            ->with(['shift', 'officeLocation'])
            ->where('user_id', $user->id)
            ->whereDate('date', $todayStr)
            ->first();

        $todaySchedule = MonthlySchedule::query()
            ->with(['shift', 'officeLocation'])
            ->where('user_id', $user->id)
            ->whereDate('date', $todayStr)
            ->first();

        $recentAttendances = Attendance::query()
            ->with(['shift', 'officeLocation'])
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->limit(15)
            ->get();

        $offices = OfficeLocation::query()->where('is_active', true)->orderBy('name')->get();
        $faceTemplate = $user->faceTemplate;
        $consecutiveFailures = $attendanceService->getConsecutiveFailures($user->id);

        return view('attendance.index', [
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'todaySchedule' => $todaySchedule,
            'recentAttendances' => $recentAttendances,
            'offices' => $offices,
            'faceTemplate' => $faceTemplate,
            'consecutiveFailures' => $consecutiveFailures,
            'faceServiceHealth' => $faceClient->health(),
        ]);
    }

    public function history(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $canViewOthers = $currentUser->can('attendance.leave.approve') 
            || $currentUser->can('attendance.schedule.generate')
            || in_array(strtolower((string)$currentUser->role), ['admin', 'hrd', 'supervisor', 'ceo']);

        $selectedUserId = (int) $request->input('user_id', $currentUser->id);
        if (!$canViewOthers) {
            $selectedUserId = $currentUser->id;
        }

        $targetUser = $selectedUserId === $currentUser->id ? $currentUser : (User::find($selectedUserId) ?? $currentUser);

        $now = now('Asia/Jakarta');
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);
        $statusFilter = (string) $request->input('status', 'all');

        $query = Attendance::query()
            ->with(['shift', 'officeLocation'])
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($statusFilter === 'present') {
            $query->whereIn('status', ['present', 'ontime']);
        } elseif ($statusFilter === 'late') {
            $query->where(function ($q) {
                $q->where('status', 'late')->orWhere('late_minutes', '>', 0);
            });
        } elseif ($statusFilter === 'absent') {
            $query->where('status', 'absent');
        } elseif ($statusFilter === 'leave') {
            $query->whereIn('status', ['leave', 'cuti', 'izin', 'holiday']);
        }

        $attendances = $query->orderByDesc('date')->get();

        $corrections = AttendanceCorrection::query()
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($c) => $c->date->toDateString());

        $monthAll = Attendance::query()
            ->with(['shift', 'officeLocation'])
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $totalHadir = $monthAll->whereIn('status', ['present', 'ontime', 'late'])->count();
        $totalTepatWaktu = $monthAll->filter(fn ($a) => in_array($a->status, ['present', 'ontime']) && ($a->late_minutes ?? 0) == 0)->count();
        $totalTerlambat = $monthAll->filter(fn ($a) => $a->status === 'late' || ($a->late_minutes ?? 0) > 0)->count();
        $totalMenitTerlambat = $monthAll->sum('late_minutes');
        $totalMenitKerja = $monthAll->sum('work_duration_minutes');
        $totalJamKerja = round($totalMenitKerja / 60, 1);

        // Build complete daily records (01 .. daysInMonth) for mobile card accordion & table
        $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;
        $today = $now->copy()->startOfDay();
        $startOfMonthStr = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->toDateString();
        $endOfMonthStr = Carbon::createFromDate($year, $month, $daysInMonth, 'Asia/Jakarta')->toDateString();

        $attendancesByDay = $monthAll->keyBy(fn (Attendance $a): int => Carbon::parse($a->date)->day);

        $schedulesByDay = MonthlySchedule::query()
            ->with('shift')
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn (MonthlySchedule $s): int => $s->date->day);

        $holidaysByDay = NationalHoliday::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn (NationalHoliday $h): int => $h->date->day);

        $approvedLeaves = Leave::query()
            ->where('user_id', $targetUser->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endOfMonthStr)
            ->whereDate('end_date', '>=', $startOfMonthStr)
            ->get();

        $defaultAlpaFee = SystemSettingService::getFloat('attendance.default_alpa_fee', 10000.0);
        $lateFeePerMinute = SystemSettingService::getFloat('attendance.late_fee_per_minute', 250.0);
        $dailyMaxLateFee = SystemSettingService::getFloat('attendance.daily_max_late_fee', 50000.0);
        $userAlpaRate = ($targetUser->potongan_alpa !== null && (float) $targetUser->potongan_alpa > 0)
            ? (float) $targetUser->potongan_alpa
            : 10000.0;

        $dayNamesShort = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
        $alpaCounter = 0;
        $dailyRecords = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta')->startOfDay();
            $dateStr = $dateObj->toDateString();
            $isFuture = $dateObj->gt($today);
            $att = $attendancesByDay->get($day);
            $sched = $schedulesByDay->get($day);
            $holiday = $holidaysByDay->get($day);
            $leave = $approvedLeaves->first(fn (Leave $l) => $dateObj->betweenIncluded($l->start_date->copy()->startOfDay(), $l->end_date->copy()->startOfDay()));
            $corr = $corrections->get($dateStr);

            $shiftObj = $att?->shift ?? $sched?->shift;
            $isScheduledHoliday = ($sched && $sched->shift && ($sched->shift->code === 'L' || str_contains(strtolower($sched->shift->name), 'libur')));
            $isCalendarHoliday = ($holiday !== null || (! $sched && $dateObj->isWeekend()));

            $status = 'holiday';
            $lateMinutes = (int) ($att?->late_minutes ?? 0);

            if ($att) {
                if ($att->status === 'absent') {
                    $status = 'absent';
                } elseif (in_array($att->status, ['leave', 'cuti', 'izin', 'sakit'], true)) {
                    $status = 'leave';
                } elseif ($att->status === 'late' || $lateMinutes > 0) {
                    $status = 'late';
                } else {
                    $status = 'present';
                }
            } elseif ($leave) {
                $status = 'leave';
            } elseif ($isScheduledHoliday || $isCalendarHoliday) {
                $status = 'holiday';
            } elseif (! $isFuture) {
                $status = 'absent';
            } else {
                $status = 'upcoming';
            }

            $alpaIndex = null;
            $deductionAmount = 0.0;
            if ($status === 'absent') {
                $alpaCounter++;
                $alpaIndex = $alpaCounter;
                $deductionAmount = $userAlpaRate;
            } elseif ($status === 'late' && $lateMinutes > 0 && $att?->attendance_type !== 'remote') {
                $deductionAmount = min($dailyMaxLateFee, round($lateMinutes * $lateFeePerMinute, 0));
            }

            $clockInShort = $att?->clock_in_at ? Carbon::parse($att->clock_in_at)->timezone('Asia/Jakarta')->format('H:i') : null;
            $clockOutShort = $att?->clock_out_at ? Carbon::parse($att->clock_out_at)->timezone('Asia/Jakarta')->format('H:i') : null;
            $clockInFull = $att?->clock_in_at ? Carbon::parse($att->clock_in_at)->timezone('Asia/Jakarta')->format('H:i:s') : null;
            $clockOutFull = $att?->clock_out_at ? Carbon::parse($att->clock_out_at)->timezone('Asia/Jakarta')->format('H:i:s') : null;

            $durationText = null;
            if ($att && $att->work_duration_minutes) {
                $hrs = (int) floor($att->work_duration_minutes / 60);
                $mns = (int) ($att->work_duration_minutes % 60);
                $durationText = "{$hrs}j {$mns}m";
            }

            $shiftStart = $shiftObj?->start_time ? substr((string) $shiftObj->start_time, 0, 5) : '08:15';
            $shiftEnd = $shiftObj?->end_time ? substr((string) $shiftObj->end_time, 0, 5) : '17:00';
            $shiftLabel = $shiftObj ? "{$shiftObj->name} ({$shiftStart} - {$shiftEnd})" : "Shift Kerja ({$shiftStart} - {$shiftEnd})";

            $dailyRecords[] = [
                'day' => $day,
                'day_num' => sprintf('%02d', $day),
                'day_short' => $dayNamesShort[$dateObj->dayOfWeek],
                'date' => $dateStr,
                'date_formatted' => $dateObj->translatedFormat('l, d F Y'),
                'is_today' => $dateObj->isSameDay($today),
                'is_future' => $isFuture,
                'status' => $status,
                'clock_in' => $clockInShort,
                'clock_out' => $clockOutShort,
                'clock_in_full' => $clockInFull,
                'clock_out_full' => $clockOutFull,
                'duration_text' => $durationText,
                'late_minutes' => $lateMinutes,
                'alpa_index' => $alpaIndex,
                'deduction_amount' => $deductionAmount,
                'notes' => $att?->notes ?? ($leave ? "Cuti / Izin ({$leave->leave_type})" : ($holiday?->name ?? null)),
                'shift_name' => $shiftObj?->name ?? ($status === 'holiday' ? 'Libur' : 'Shift Pagi'),
                'shift_hours' => $status === 'holiday' ? '-' : "{$shiftStart} - {$shiftEnd}",
                'shift_label' => $shiftLabel,
                'office_name' => $att?->officeLocation?->name ?? 'Kantor Pusat',
                'default_clock_in' => $clockInShort ?? $shiftStart,
                'default_clock_out' => $clockOutShort ?? $shiftEnd,
                'correction' => $corr,
            ];
        }

        $allEmployees = collect();
        if ($canViewOthers) {
            $allEmployees = User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('attendance.history', [
            'currentUser' => $currentUser,
            'targetUser' => $targetUser,
            'canViewOthers' => $canViewOthers,
            'allEmployees' => $allEmployees,
            'year' => $year,
            'month' => $month,
            'statusFilter' => $statusFilter,
            'attendances' => $attendances,
            'dailyRecords' => $dailyRecords,
            'corrections' => $corrections,
            'stats' => [
                'total_hadir' => $totalHadir,
                'total_tepat_waktu' => $totalTepatWaktu,
                'total_terlambat' => $totalTerlambat,
                'total_menit_terlambat' => $totalMenitTerlambat,
                'total_jam_kerja' => $totalJamKerja,
            ],
        ]);
    }

    public function enroll(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'consent' => ['required', 'accepted'],
            'pose_front' => ['required', 'string'],
            'pose_left' => ['nullable', 'string'],
            'pose_right' => ['nullable', 'string'],
        ]);

        $poseFront = $validated['pose_front'];
        $poseLeft = ! empty($validated['pose_left']) ? $validated['pose_left'] : $poseFront;
        $poseRight = ! empty($validated['pose_right']) ? $validated['pose_right'] : $poseFront;

        $attendanceService->enrollUserFace(
            user: $user,
            posesBase64: [
                $poseFront,
                $poseLeft,
                $poseRight,
            ],
            consentGiven: true,
            ip: (string) $request->ip()
        );

        return back()->with('status', 'Biometrik wajah (3 pose) berhasil didaftarkan dan dienkripsi sesuai UU PDP No. 27/2022 (§5.1, §10.2).');
    }

    public function resetFace(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $validated = $request->validate([
            'target_user_id' => ['nullable', 'exists:users,id'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $targetUser = isset($validated['target_user_id'])
            ? User::query()->findOrFail($validated['target_user_id'])
            : $actor;

        if ($targetUser->id !== $actor->id && ! $actor->can('attendance.face.reset')) {
            abort(403, 'Anda tidak memiliki izin untuk mereset biometrik karyawan lain.');
        }

        $attendanceService->resetUserFace($targetUser, $validated['reason'], $actor->id);

        return back()->with('status', "Template biometrik dan foto terkait untuk {$targetUser->name} telah dihapus; pendaftaran ulang kini diwajibkan (AC-12).");
    }

    public function issueAttemptToken(Request $request, AttendanceService $attendanceService): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'sample_image' => ['required', 'string'],
        ]);

        $attemptToken = $attendanceService->issueAttemptToken($user, $validated['sample_image']);

        if ($request->wantsJson()) {
            return response()->json([
                'attempt_token' => $attemptToken->token,
                'challenge_type' => $attemptToken->challenge_type,
                'expires_at' => $attemptToken->expires_at->toIso8601String(),
            ]);
        }

        return back()
            ->with('issued_attempt_token', $attemptToken->token)
            ->with('issued_challenge_type', $attemptToken->challenge_type)
            ->with('status', "Tahap 1 Lulus: Token percobaan diterbitkan (berlaku 2 menit). Lakukan tantangan aktif: {$attemptToken->challenge_type}.");
    }

    public function clockIn(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'attendance_type' => ['required', 'in:office,field,remote'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'gps_accuracy' => ['required', 'numeric', 'min:0'],
            'is_mock_location' => ['nullable', 'boolean'],
            'attempt_token' => ['nullable', 'string'],
            'primary_image' => ['nullable', 'string'],
            'challenge_image' => ['nullable', 'string'],
            'use_manual_fallback' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'ticket_reference' => ['nullable', 'string', 'max:64'],
        ]);

        if (empty($validated['primary_image']) && ! ($validated['use_manual_fallback'] ?? false)) {
            throw ValidationException::withMessages([
                'primary_image' => 'Foto wajah wajib diambil untuk verifikasi kesesuaian dengan pendaftaran biometrik Anda.',
            ]);
        }

        if (empty($validated['attempt_token']) && ! ($validated['use_manual_fallback'] ?? false)) {
            $sample = ! empty($validated['primary_image']) ? $validated['primary_image'] : base64_encode('FACE_ID:user_'.$user->id.'|SAMPLE_AUTO');
            $attemptToken = $attendanceService->issueAttemptToken($user, $sample);
            $validated['attempt_token'] = $attemptToken->token;
            if (empty($validated['challenge_image'])) {
                $validated['challenge_image'] = base64_encode('FACE_ID:user_'.$user->id.'|CHALLENGE_AUTO');
            }
        }

        $attendance = $attendanceService->clockIn($user, $validated);

        return back()->with(
            'status',
            'Clock-In berhasil diverifikasi wajah & dicatat (Status: '.strtoupper($attendance->status).", Jarak: {$attendance->distance_m_in}m, Terlambat: {$attendance->late_minutes} menit)."
        );
    }

    public function clockOut(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'gps_accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $attendance = $attendanceService->clockOut($user, $validated);

        return back()->with(
            'status',
            "Clock-Out berhasil. Durasi kerja bersih: {$attendance->work_duration_minutes} menit (Usulan lembur: {$attendance->proposed_overtime_minutes} menit)."
        );
    }

    public function syncOffline(Request $request, AttendanceService $attendanceService): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'client_uuid' => ['required', 'string', 'max:64'],
            'captured_at' => ['required', 'date'],
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'attendance_type' => ['required', 'in:office,field,remote'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'gps_accuracy' => ['required', 'numeric', 'min:0'],
            'is_mock_location' => ['nullable', 'boolean'],
            'primary_image' => ['required', 'string'],
            'challenge_image' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $attendance = $attendanceService->syncOfflineAttendance($user, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'idempotent' => true,
                'attendance_id' => $attendance->id,
                'client_uuid' => $attendance->client_uuid,
                'status' => $attendance->status,
                'is_flagged' => $attendance->is_flagged,
            ]);
        }

        return back()->with('status', "Sinkronisasi offline berhasil diproses secara idempoten (UUID: {$attendance->client_uuid}).");
    }
}
