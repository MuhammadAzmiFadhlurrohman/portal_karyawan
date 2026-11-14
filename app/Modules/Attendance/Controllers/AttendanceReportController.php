<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\MonthlySchedule;
use App\Models\User;
use App\Modules\Attendance\Services\LeaveService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    /**
     * Display daily attendance report for admin/hrd.
     */
    public function index(Request $request, LeaveService $leaveService): View
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['super_admin', 'admin'])) {
            abort(403, 'Akses laporan absensi hanya untuk Administrator.');
        }

        // Parse filter date (default: today)
        $dateInput = $request->input('date');
        try {
            $carbonDate = $dateInput ? Carbon::parse($dateInput) : now('Asia/Jakarta');
        } catch (Exception $e) {
            $carbonDate = now('Asia/Jakarta');
        }
        $targetDateStr = $carbonDate->toDateString();
        $year = (int) $carbonDate->format('Y');
        $month = (int) $carbonDate->format('n');

        // Query active employees
        $searchTerm = trim($request->input('q', ''));
        $employees = User::query()
            ->where('is_active', true)
            ->when($searchTerm !== '', function ($q) use ($searchTerm) {
                $q->where(function ($sub) use ($searchTerm) {
                    $sub->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('employee_code', 'like', "%{$searchTerm}%")
                        ->orWhere('division', 'like', "%{$searchTerm}%");
                });
            })
            ->orderBy('name')
            ->get();

        // Get attendances for target date
        $attendances = Attendance::query()
            ->whereDate('date', $targetDateStr)
            ->with(['shift', 'officeLocation'])
            ->get()
            ->keyBy('user_id');

        // Get schedules for target date
        $schedules = MonthlySchedule::query()
            ->whereDate('date', $targetDateStr)
            ->with(['shift', 'officeLocation'])
            ->get()
            ->keyBy('user_id');

        // Build composite row for each employee
        $rows = $employees->map(function (User $emp) use ($attendances, $schedules, $leaveService, $year, $month, $carbonDate) {
            /** @var Attendance|null $att */
            $att = $attendances->get($emp->id);
            /** @var MonthlySchedule|null $sched */
            $sched = $schedules->get($emp->id);

            // Leave balance
            $balance = $leaveService->getOrCreateBalance($emp, $year);
            $remainingLeave = $balance->remaining_days;

            // Monthly alpa count for employee
            $monthlyAlpas = Attendance::query()
                ->where('user_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->where('status', 'absent')
                ->whereDate('date', '<=', $carbonDate->toDateString())
                ->count();

            // Status determination
            $status = $att ? $att->status : 'absent';
            $isAlpa = ($status === 'absent');

            // Format clock in / out
            $clockInStr = $att?->clock_in_at ? $att->clock_in_at->format('H:i:s') : null;
            $clockOutStr = $att?->clock_out_at ? $att->clock_out_at->format('H:i:s') : null;

            // Duration
            $workDuration = null;
            if ($att?->work_duration_minutes) {
                $h = floor($att->work_duration_minutes / 60);
                $m = $att->work_duration_minutes % 60;
                $workDuration = $h > 0 ? "{$h} jam {$m} m" : "{$m} menit";
            } elseif ($att?->clock_in_at && $att?->clock_out_at) {
                $diff = $att->clock_out_at->diffInMinutes($att->clock_in_at);
                $h = floor($diff / 60);
                $m = $diff % 60;
                $workDuration = $h > 0 ? "{$h} jam {$m} m" : "{$m} menit";
            }

            // Distance & Location
            $distanceStr = null;
            if ($att && $att->distance_m_in !== null) {
                if ($att->distance_m_in >= 1000) {
                    $distanceStr = number_format($att->distance_m_in / 1000, 1, '.', '') . ' km';
                } else {
                    $distanceStr = round($att->distance_m_in) . ' m';
                }
            }

            $mapUrl = null;
            if ($att && $att->lat_in && $att->lng_in) {
                $mapUrl = "https://www.google.com/maps?q={$att->lat_in},{$att->lng_in}";
            }

            // Photo path & URL
            $photoPath = $att?->photo_in_primary_path ?: $att?->photo_out_path;
            $photoUrl = ($att && $photoPath) ? route('attendance.photo', $att->id) : null;

            // Alpa deduction label (e.g. Cuti (1/3 Alpa))
            $alpaCutiLabel = null;
            if ($isAlpa) {
                $alpaIndex = max(1, $monthlyAlpas);
                if ($alpaIndex <= 3 && $remainingLeave > 0) {
                    $alpaCutiLabel = "Cuti ({$alpaIndex}/3 Alpa)";
                } else {
                    $alpaCutiLabel = "Potong Gaji";
                }
            }

            return [
                'user' => $emp,
                'attendance' => $att,
                'schedule' => $sched,
                'status' => $status,
                'clock_in' => $clockInStr,
                'clock_out' => $clockOutStr,
                'duration' => $workDuration,
                'photo_path' => $photoPath,
                'photo_url' => $photoUrl,
                'distance' => $distanceStr,
                'map_url' => $mapUrl,
                'remaining_leave' => $remainingLeave,
                'alpa_cuti_label' => $alpaCutiLabel,
                'notes' => $att?->notes ?? ($isAlpa ? 'Tidak melakukan absensi' : '-'),
            ];
        });

        return view('attendance.reports.index', [
            'targetDate' => $targetDateStr,
            'carbonDate' => $carbonDate,
            'dateFormatted' => $carbonDate->translatedFormat('l, d F Y'),
            'rows' => $rows,
            'totalRecords' => $rows->count(),
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * Update or create attendance record from admin report modal.
     */
    public function updateRecord(Request $request, LeaveService $leaveService): RedirectResponse|JsonResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['super_admin', 'admin'])) {
            abort(403, 'Akses laporan absensi hanya untuk Administrator.');
        }

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:present,late,leave,sick,special,absent'],
            'clock_in' => ['nullable', 'string'],
            'clock_out' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = (int) $request->input('user_id');
        $dateStr = Carbon::parse($request->input('date'))->toDateString();
        $status = $request->input('status');
        $notes = $request->input('notes');

        $attendance = Attendance::firstOrNew([
            'user_id' => $userId,
            'date' => $dateStr,
        ]);

        $attendance->status = $status;
        $attendance->notes = $notes;
        $attendance->source = 'admin_override';
        $attendance->reviewed_by = $actor->id;
        $attendance->reviewed_at = now();
        $attendance->review_notes = 'Disesuaikan oleh Admin ' . $actor->name;

        // Parse Clock In
        if ($request->filled('clock_in')) {
            $rawIn = trim($request->input('clock_in'));
            $attendance->clock_in_at = Carbon::parse("{$dateStr} {$rawIn}");
        } elseif ($status === 'absent') {
            $attendance->clock_in_at = null;
        }

        // Parse Clock Out
        if ($request->filled('clock_out')) {
            $rawOut = trim($request->input('clock_out'));
            $attendance->clock_out_at = Carbon::parse("{$dateStr} {$rawOut}");
        } elseif ($status === 'absent') {
            $attendance->clock_out_at = null;
        }

        // Calculate work duration if both clock in and clock out exist
        if ($attendance->clock_in_at && $attendance->clock_out_at) {
            $attendance->work_duration_minutes = max(0, $attendance->clock_out_at->diffInMinutes($attendance->clock_in_at));
        } elseif ($status === 'absent') {
            $attendance->work_duration_minutes = 0;
        }

        $attendance->save();

        // Sync alpa if relevant
        $leaveService->syncDailyAlpa($dateStr);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data absensi karyawan berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Data absensi karyawan berhasil diperbarui.');
    }

    /**
     * Quick status update via dropdown.
     */
    public function quickStatus(Request $request, LeaveService $leaveService): JsonResponse|RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['super_admin', 'admin'])) {
            abort(403, 'Akses laporan absensi hanya untuk Administrator.');
        }

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:present,late,leave,sick,special,absent'],
        ]);

        $userId = (int) $request->input('user_id');
        $dateStr = Carbon::parse($request->input('date'))->toDateString();
        $status = $request->input('status');

        $attendance = Attendance::firstOrNew([
            'user_id' => $userId,
            'date' => $dateStr,
        ]);

        $attendance->status = $status;
        $attendance->source = 'admin_quick_status';
        $attendance->reviewed_by = $actor->id;
        $attendance->reviewed_at = now();

        if ($status === 'present' && ! $attendance->clock_in_at) {
            $attendance->clock_in_at = Carbon::parse("{$dateStr} 08:00:00");
        } elseif ($status === 'absent') {
            $attendance->clock_in_at = null;
            $attendance->clock_out_at = null;
            $attendance->work_duration_minutes = 0;
        }

        $attendance->save();
        $leaveService->syncDailyAlpa($dateStr);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Status berhasil diubah menjadi {$status}.",
            ]);
        }

        return redirect()->back()->with('success', "Status absensi berhasil diubah menjadi {$status}.");
    }

    /**
     * Export daily attendance report to CSV.
     */
    public function exportCsv(Request $request, LeaveService $leaveService): StreamedResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole(['super_admin', 'admin'])) {
            abort(403, 'Akses laporan absensi hanya untuk Administrator.');
        }

        $dateInput = $request->input('date', now('Asia/Jakarta')->toDateString());
        $carbonDate = Carbon::parse($dateInput);
        $dateStr = $carbonDate->toDateString();
        $year = (int) $carbonDate->format('Y');

        $employees = User::query()->where('is_active', true)->orderBy('name')->get();
        $attendances = Attendance::query()->whereDate('date', $dateStr)->get()->keyBy('user_id');

        $fileName = "laporan_absensi_{$dateStr}.csv";

        return response()->streamDownload(function () use ($employees, $attendances, $leaveService, $year, $dateStr) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'NO',
                'NIK',
                'NAMA KARYAWAN',
                'DIVISI',
                'TANGGAL',
                'JAM MASUK',
                'JAM PULANG',
                'DURASI KERJA',
                'STATUS',
                'JARAK (M)',
                'SISA CUTI (HARI)',
                'KETERANGAN',
            ]);

            foreach ($employees as $idx => $emp) {
                /** @var Attendance|null $att */
                $att = $attendances->get($emp->id);
                $status = $att ? $att->status : 'absent';
                $balance = $leaveService->getOrCreateBalance($emp, $year);

                fputcsv($handle, [
                    $idx + 1,
                    $emp->employee_code,
                    $emp->name,
                    $emp->division ?? '-',
                    $dateStr,
                    $att?->clock_in_at ? $att->clock_in_at->format('H:i:s') : '-',
                    $att?->clock_out_at ? $att->clock_out_at->format('H:i:s') : '-',
                    $att?->work_duration_minutes ? round($att->work_duration_minutes / 60, 1) . ' jam' : '-',
                    strtoupper($status),
                    $att?->distance_m_in ?? '-',
                    $balance->remaining_days,
                    $att?->notes ?? ($status === 'absent' ? 'Tidak melakukan absensi' : '-'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Serve attendance selfie photo securely.
     */
    public function photo(Request $request, Attendance $attendance)
    {
        $actor = $request->user();
        if (! $actor) {
            abort(401);
        }

        $canView = $actor->id === $attendance->user_id
            || $actor->hasRole(['super_admin', 'admin', 'hrd', 'atasan', 'ceo'])
            || $actor->can('attendance.reports');

        abort_unless($canView, 403, 'Akses ditolak.');

        $path = $attendance->photo_in_primary_path ?: $attendance->photo_out_path ?: $attendance->photo_in_challenge_path;

        $placeholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"><rect width="100%" height="100%" fill="#f1f5f9"/><circle cx="150" cy="115" r="45" fill="#cbd5e1"/><path d="M70 230c0-44.2 35.8-80 80-80s80 35.8 80 80v20H70v-20z" fill="#cbd5e1"/><text x="50%" y="275" font-family="sans-serif" font-size="12" font-weight="bold" fill="#64748b" text-anchor="middle">Foto Tidak Ditemukan</text></svg>';

        if (! $path) {
            return response($placeholderSvg, 200, ['Content-Type' => 'image/svg+xml']);
        }

        $content = null;
        if (Storage::disk('private')->exists($path)) {
            $content = Storage::disk('private')->get($path);
        } elseif (Storage::disk('public')->exists($path)) {
            $content = Storage::disk('public')->get($path);
        } elseif (Storage::exists($path)) {
            $content = Storage::get($path);
        }

        if (! $content || $content === 'MANUAL') {
            return response($placeholderSvg, 200, ['Content-Type' => 'image/svg+xml']);
        }

        // If content is Data URL (data:image/jpeg;base64,...)
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/s', $content, $matches)) {
            $mime = 'image/' . $matches[1];
            $binary = base64_decode($matches[2]);
            return response($binary, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=86400',
            ]);
        }

        // If raw base64 string
        $decoded = base64_decode($content, true);
        if ($decoded !== false && (str_starts_with($decoded, "\xFF\xD8\xFF") || str_starts_with($decoded, "\x89PNG"))) {
            $mime = str_starts_with($decoded, "\xFF\xD8\xFF") ? 'image/jpeg' : 'image/png';
            return response($decoded, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=86400',
            ]);
        }

        // If already binary image bytes
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content) ?: 'image/jpeg';
        return response($content, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}

