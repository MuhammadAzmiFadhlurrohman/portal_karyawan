<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use App\Modules\Attendance\Services\ScheduleService;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduleController extends Controller
{
    /**
     * Determine if a user can manage / generate schedules.
     * Hanya role Admin (admin & super_admin) yang berhak mengatur/mengelola jadwal kerja.
     */
    protected function canManageSchedule(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'admin']);
    }
    /**
     * Display schedule pages:
     * - Screen 1: Monthly schedule overview list (default)
     * - Screen 2: Generate monthly schedule form (?view=generate)
     * - Screen 3: Employee schedule list for a specific month (?view=month&year=...&month=...)
     * - Screen 5: Day-by-day calendar schedule for a single employee (?view=user&user_id=...)
     */
    public function index(Request $request, ScheduleService $scheduleService): View
    {
        $view = $request->input('view');
        $now = Carbon::now('Asia/Jakarta');
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);
        $division = $request->filled('division') ? (string) $request->input('division') : null;

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $shifts = Shift::query()->orderBy('id')->get();
        $offices = OfficeLocation::query()->where('is_active', true)->orderBy('name')->get();
        $allDivisions = User::query()->whereNotNull('division')->where('division', '!=', '')->pluck('division')->unique()->values();

        /** @var User $actor */
        $actor = $request->user();
        $canManageSchedule = $this->canManageSchedule($actor);

        // Jika bukan admin yang berhak mengelola, selalu tampilkan jadwal sendiri (personal)
        if (! $canManageSchedule) {
            $view = 'personal';
        }

        // Jika role Admin membuka Jadwal Kerja tanpa parameter view, arahkan langsung ke Kalender & Custom Jadwal
        if (! $view && $canManageSchedule) {
            $view = 'user';
        }

        // -------------------------------------------------------------------------
        // Screen Personal: Jadwal Kerja Karyawan & Tukar / Backup Shift
        // -------------------------------------------------------------------------
        if ($view === 'my' || $view === 'personal') {
            $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;
            $today = Carbon::now('Asia/Jakarta')->startOfDay();

            $mySchedules = MonthlySchedule::query()
                ->with(['shift', 'officeLocation'])
                ->where('user_id', $actor->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(fn (MonthlySchedule $s): int => $s->date->day);

            $holidays = NationalHoliday::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(fn (NationalHoliday $h): int => $h->date->day);

            $divisionPeers = User::query()
                ->where('is_active', true)
                ->where('id', '!=', $actor->id)
                ->when($actor->division, fn ($q) => $q->where('division', $actor->division))
                ->orderBy('name')
                ->get();

            $peerSchedulesData = MonthlySchedule::query()
                ->with('shift')
                ->whereIn('user_id', $divisionPeers->pluck('id'))
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->groupBy('user_id')
                ->map(function ($schedules) {
                    return $schedules->mapWithKeys(function ($s) {
                        $label = $s->shift
                            ? ($s->shift->code === 'L' ? 'Libur' : $s->shift->name . ' (' . substr($s->shift->start_time, 0, 5) . '–' . substr($s->shift->end_time, 0, 5) . ')')
                            : 'Libur';
                        return [$s->date->format('Y-m-d') => $label];
                    });
                });

            return view('attendance.schedules', [
                'viewMode' => 'personal',
                'actor' => $actor,
                'year' => $year,
                'month' => $month,
                'monthName' => $monthNames[$month] ?? "Bulan {$month}",
                'monthNames' => $monthNames,
                'daysInMonth' => $daysInMonth,
                'today' => $today,
                'mySchedules' => $mySchedules,
                'holidays' => $holidays,
                'divisionPeers' => $divisionPeers,
                'peerSchedules' => $peerSchedulesData,
                'shifts' => $shifts,
                'offices' => $offices,
                'canManageSchedule' => $canManageSchedule,
            ]);
        }

        // -------------------------------------------------------------------------
        // Screen 2: Generate Jadwal Bulanan
        // -------------------------------------------------------------------------
        if ($view === 'generate') {
            $activeUsers = User::query()->where('is_active', true)->orderBy('name')->get();

            return view('attendance.schedules', [
                'viewMode' => 'generate',
                'year' => $year,
                'month' => $month,
                'monthName' => $monthNames[$month] ?? "Bulan {$month}",
                'monthNames' => $monthNames,
                'activeUsers' => $activeUsers,
                'shifts' => $shifts,
                'offices' => $offices,
                'allDivisions' => $allDivisions,
                'canManageSchedule' => $this->canManageSchedule($request->user()),
            ]);
        }

        // -------------------------------------------------------------------------
        // Screen 5: Kalender Jadwal Detail Karyawan (Custom Jadwal)
        // -------------------------------------------------------------------------
        if ($view === 'user') {
            $activeUsers = User::query()->where('is_active', true)->orderBy('name')->get();
            $userId = (int) $request->input('user_id', 0);
            $targetUser = $userId > 0
                ? (User::query()->find($userId) ?? $actor)
                : ($activeUsers->firstWhere('id', $actor->id) ?? $activeUsers->first() ?? $actor);

            $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;

            $userSchedules = MonthlySchedule::query()
                ->with('shift')
                ->where('user_id', $targetUser->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(fn (MonthlySchedule $s): int => $s->date->day);

            $holidays = NationalHoliday::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(fn (NationalHoliday $h): int => $h->date->day);

            $conflicts = $scheduleService->detectRestConflicts($year, $month);
            $userConflicts = array_filter($conflicts, fn ($c) => $c['user_id'] === $targetUser->id);

            return view('attendance.schedules', [
                'viewMode' => 'user',
                'targetUser' => $targetUser,
                'activeUsers' => $activeUsers,
                'year' => $year,
                'month' => $month,
                'monthName' => $monthNames[$month] ?? "Bulan {$month}",
                'monthNames' => $monthNames,
                'daysInMonth' => $daysInMonth,
                'userSchedules' => $userSchedules,
                'holidays' => $holidays,
                'shifts' => $shifts,
                'offices' => $offices,
                'conflicts' => $userConflicts,
                'canManageSchedule' => $canManageSchedule,
            ]);
        }

        // -------------------------------------------------------------------------
        // Screen 3: Detail Bulan / Daftar Karyawan
        // -------------------------------------------------------------------------
        if ($view === 'month' || ($request->has('year') && $request->has('month') && ! $request->has('list'))) {
            $usersQuery = User::query()->where('is_active', true)->orderBy('name');
            if ($division) {
                $usersQuery->where('division', $division);
            }
            $users = $usersQuery->get();

            $schedules = MonthlySchedule::query()
                ->with(['shift', 'officeLocation'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereIn('user_id', $users->pluck('id'))
                ->get()
                ->groupBy('user_id');

            $holidays = NationalHoliday::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date')
                ->get();

            $registeredCount = 0;
            $unregisteredCount = 0;
            foreach ($users as $u) {
                if ($schedules->has($u->id) && $schedules->get($u->id)->isNotEmpty()) {
                    $registeredCount++;
                } else {
                    $unregisteredCount++;
                }
            }

            $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;
            $conflicts = $scheduleService->detectRestConflicts($year, $month);

            return view('attendance.schedules', [
                'viewMode' => 'month',
                'year' => $year,
                'month' => $month,
                'monthName' => $monthNames[$month] ?? "Bulan {$month}",
                'division' => $division,
                'daysInMonth' => $daysInMonth,
                'users' => $users,
                'totalUsersCount' => $users->count(),
                'registeredCount' => $registeredCount,
                'unregisteredCount' => $unregisteredCount,
                'schedulesByUser' => $schedules,
                'shifts' => $shifts,
                'offices' => $offices,
                'holidays' => $holidays,
                'conflicts' => $conflicts,
                'allDivisions' => $allDivisions,
                'canManageSchedule' => $this->canManageSchedule($request->user()),
            ]);
        }

        // -------------------------------------------------------------------------
        // Screen 1: Index Daftar Bulan Jadwal Kerja
        // -------------------------------------------------------------------------
        $summaries = $scheduleService->getMonthlyScheduleSummaries();

        return view('attendance.schedules', [
            'viewMode' => 'list',
            'summaries' => $summaries,
            'shifts' => $shifts,
            'offices' => $offices,
            'canManageSchedule' => $this->canManageSchedule($request->user()),
        ]);
    }

    /**
     * Screen 2 Submission: Generate Monthly Schedules
     */
    public function generate(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'scope' => ['required', 'in:all,user'],
            'user_id' => ['nullable', 'exists:users,id'],
            'default_shift_code' => ['required', 'exists:shifts,code'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $userId = ($validated['scope'] === 'user' && ! empty($validated['user_id']))
            ? (int) $validated['user_id']
            : null;

        $defaultOffice = ! empty($validated['office_location_id'])
            ? (int) $validated['office_location_id']
            : (OfficeLocation::where('is_active', true)->first()?->id ?? 1);

        $summary = $scheduleService->commitMonthlyGeneration(
            year: $year,
            month: $month,
            actorId: $actor->id,
            division: null,
            officeLocationId: $defaultOffice,
            defaultShiftCode: (string) $validated['default_shift_code'],
            userId: $userId
        );

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $mName = $monthNames[$month] ?? "Bulan {$month}";

        return redirect()->route('attendance.schedules', [
            'view' => 'month',
            'year' => $year,
            'month' => $month,
        ])->with(
            'status',
            "Jadwal bulan {$mName} {$year} berhasil di-generate ({$summary['total_slots']} slot dibuat/diperbarui, {$summary['preserved_piket_count']} jadwal piket dipertahankan)."
        );
    }

    /**
     * Delete entire monthly schedule.
     */
    public function deleteMonth(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $deleted = $scheduleService->deleteMonthSchedules($year, $month, $actor->id);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $mName = $monthNames[$month] ?? "Bulan {$month}";

        return redirect()->route('attendance.schedules')->with(
            'status',
            "Jadwal bulan {$mName} {$year} berhasil dihapus ({$deleted} slot jadwal dibersihkan)."
        );
    }

    /**
     * Add national holiday and set workday shift on that date to 'L'.
     */
    public function storeHoliday(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:120'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer'],
        ]);

        $holiday = $scheduleService->addHoliday($validated['date'], $validated['name'], $actor->id);

        return back()->with(
            'status',
            "Hari Libur Nasional '{$holiday->name}' ({$holiday->date->format('d/m/Y')}) berhasil ditambahkan dan jadwal kerja otomatis disesuaikan menjadi libur."
        );
    }

    /**
     * Delete national holiday.
     */
    public function deleteHoliday(NationalHoliday $holiday, Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $scheduleService->deleteHoliday($holiday, $actor->id);

        return back()->with('status', "Hari libur '{$holiday->name}' berhasil dihapus.");
    }

    /**
     * Download CSV template for piket schedule.
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        abort_unless($this->canManageSchedule($request->user()), 403);

        $year = (int) $request->input('year', now('Asia/Jakarta')->year);
        $month = (int) $request->input('month', now('Asia/Jakarta')->month);
        $division = $request->input('division');

        $usersQuery = User::query()->where('is_active', true)->orderBy('name');
        if ($division && $division !== 'Semua Divisi' && $division !== 'all') {
            $usersQuery->where('division', $division);
        }
        $users = $usersQuery->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->daysInMonth;
        $divName = $division ?: 'semua-divisi';
        $filename = "template-piket-{$divName}-{$year}-{$month}.csv";

        return response()->streamDownload(function () use ($year, $month, $daysInMonth, $users): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['tanggal', 'kode_karyawan', 'nama_karyawan', 'divisi', 'shift_code']);

            foreach ($users as $u) {
                // Seed 1-2 weekend/sample piket days
                for ($d = 1; $d <= min(3, $daysInMonth); $d++) {
                    $dt = Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta')->toDateString();
                    fputcsv($out, [
                        $dt,
                        $u->employee_code ?: "KR{$u->id}",
                        $u->name,
                        $u->division,
                        'PK',
                    ]);
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Upload piket schedules from CSV.
     */
    public function uploadPiket(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $result = $scheduleService->importPiketCsv($file->getPathname(), $actor->id);

        if ($result['imported'] === 0 && ! empty($result['errors'])) {
            return back()->withErrors(['file' => implode(' | ', array_slice($result['errors'], 0, 3))]);
        }

        return back()->with(
            'status',
            "Upload piket berhasil: {$result['imported']} dari {$result['total']} jadwal piket berhasil diproses dan dikunci."
        );
    }

    /**
     * Backward-compatible Preview Generate
     */
    public function previewGenerate(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        abort_unless($this->canManageSchedule($request->user()), 403);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'division' => ['nullable', 'string'],
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'default_shift_code' => ['required', 'exists:shifts,code'],
        ]);

        $preview = $scheduleService->previewMonthlyGeneration(
            year: (int) $validated['year'],
            month: (int) $validated['month'],
            division: $validated['division'] ?? null,
            officeLocationId: (int) $validated['office_location_id'],
            defaultShiftCode: (string) $validated['default_shift_code']
        );

        return back()->with('schedule_preview', [
            'year' => $preview['year'],
            'month' => $preview['month'],
            'division' => $validated['division'] ?? null,
            'office_location_id' => (int) $validated['office_location_id'],
            'default_shift_code' => (string) $validated['default_shift_code'],
            'users_count' => $preview['users_count'],
            'total_slots' => $preview['total_slots'],
            'preserved_piket_count' => $preview['preserved_piket_count'],
            'overwrite_count' => $preview['overwrite_count'],
            'holidays_count' => $preview['holidays_count'],
        ])->with('status', 'Pratinjau jadwal bulanan siap. Periksa ringkasan di bawah dan klik Konfirmasi untuk menyimpan (§5.2).');
    }

    /**
     * Backward-compatible Commit Generate
     */
    public function commitGenerate(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'division' => ['nullable', 'string'],
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'default_shift_code' => ['required', 'exists:shifts,code'],
            'confirmed' => ['required', 'accepted'],
        ]);

        $summary = $scheduleService->commitMonthlyGeneration(
            year: (int) $validated['year'],
            month: (int) $validated['month'],
            actorId: $actor->id,
            division: $validated['division'] ?? null,
            officeLocationId: (int) $validated['office_location_id'],
            defaultShiftCode: (string) $validated['default_shift_code']
        );

        return redirect()->route('attendance.schedules', [
            'view' => 'month',
            'year' => $validated['year'],
            'month' => $validated['month'],
            'division' => $validated['division'] ?? null,
        ])->with(
            'status',
            "Jadwal bulanan berhasil di-generate ({$summary['total_slots']} slot, {$summary['preserved_piket_count']} jadwal piket dipertahankan, {$summary['overwrite_count']} slot diperbarui)."
        );
    }

    /**
     * Update single schedule cell (supports AJAX instant update on Screen 5).
     */
    public function updateCell(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($this->canManageSchedule($actor), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'office_location_id' => ['nullable', 'exists:office_locations,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reason = $validated['reason'] ?: 'Pembaruan jadwal kerja melalui Kalender Shift Admin';

        $existing = MonthlySchedule::query()
            ->where('user_id', $validated['user_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        $oldShiftId = $existing?->shift_id;
        $shift = Shift::findOrFail($validated['shift_id']);

        if ($existing) {
            $existing->update([
                'shift_id' => $shift->id,
                'office_location_id' => $validated['office_location_id'] ?? $existing->office_location_id,
                'updated_by' => $actor->id,
            ]);
            $schedule = $existing;
        } else {
            $schedule = MonthlySchedule::query()->create([
                'user_id' => $validated['user_id'],
                'date' => Carbon::parse($validated['date'])->toDateString(),
                'shift_id' => $shift->id,
                'office_location_id' => $validated['office_location_id'] ?? OfficeLocation::where('is_active', true)->first()?->id,
                'updated_by' => $actor->id,
                'created_by' => $actor->id,
            ]);
        }

        AuditLogger::record(
            action: 'schedule.cell_updated',
            auditable: $schedule,
            oldValues: ['shift_id' => $oldShiftId],
            newValues: ['shift_id' => (int) $validated['shift_id'], 'date' => $validated['date']],
            reason: $reason,
            actorId: $actor->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perubahan jadwal shift berhasil disimpan.',
                'shift' => [
                    'id' => $shift->id,
                    'code' => $shift->code,
                    'name' => $shift->name,
                    'color' => $shift->color,
                    'start_time' => $shift->start_time ? substr((string) $shift->start_time, 0, 5) : null,
                    'end_time' => $shift->end_time ? substr((string) $shift->end_time, 0, 5) : null,
                    'is_day_off' => $shift->is_day_off,
                ],
            ]);
        }

        return back()->with('status', 'Perubahan jadwal shift berhasil disimpan.');
    }

    public function storeOffice(Request $request): RedirectResponse
    {
        abort_unless($this->canManageSchedule($request->user()), 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:120'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius_m' => ['required', 'integer', 'min:10', 'max:2000'],
        ]);

        $office = OfficeLocation::query()->updateOrCreate(
            ['code' => strtoupper($validated['code'])],
            [
                'name' => $validated['name'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'radius_m' => $validated['radius_m'],
                'is_active' => true,
            ]
        );

        AuditLogger::record(
            action: 'office_location.upserted',
            auditable: $office,
            newValues: $validated,
            reason: 'Pembaruan master lokasi kantor & radius geofence (§5.1)'
        );

        return back()->with('status', "Lokasi kantor `{$office->name}` (Radius {$office->radius_m}m) berhasil disimpan.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now('Asia/Jakarta')->year);
        $month = (int) $request->input('month', now('Asia/Jakarta')->month);

        $schedules = MonthlySchedule::query()
            ->with(['user', 'shift', 'officeLocation'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $filename = "jadwal-shift-msn-{$year}-{$month}.csv";

        return response()->streamDownload(function () use ($schedules): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, ['Tanggal', 'Kode Karyawan', 'Nama', 'Divisi', 'Kode Shift', 'Nama Shift', 'Kantor']);
            foreach ($schedules as $s) {
                fputcsv($out, [
                    $s->date->toDateString(),
                    $s->user?->employee_code,
                    $s->user?->name,
                    $s->user?->division,
                    $s->shift?->code,
                    $s->shift?->name,
                    $s->officeLocation?->name,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * AJAX endpoint to fetch peer shift on a specific target date
     */
    public function getPeerShift(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id');
        $dateStr = (string) $request->input('date');

        if (! $userId || ! $dateStr) {
            return response()->json(['shift' => '-', 'available_count' => 0]);
        }

        $targetUser = User::query()->find($userId);
        $schedule = MonthlySchedule::query()->with('shift')->where('user_id', $userId)->whereDate('date', $dateStr)->first();

        $shiftLabel = '-';
        if ($schedule && $schedule->shift) {
            $shiftLabel = $schedule->shift->code === 'L'
                ? 'Libur'
                : $schedule->shift->name . ' (' . substr($schedule->shift->start_time, 0, 5) . '–' . substr($schedule->shift->end_time, 0, 5) . ')';
        }

        $division = $targetUser?->division;
        $availableCount = MonthlySchedule::query()
            ->whereHas('user', fn ($q) => $q->where('is_active', true)->when($division, fn ($q2) => $q2->where('division', $division)))
            ->whereDate('date', $dateStr)
            ->count();

        return response()->json([
            'shift' => $shiftLabel,
            'available_count' => max(1, $availableCount),
        ]);
    }
}
