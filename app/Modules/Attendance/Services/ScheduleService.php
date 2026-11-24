<?php

namespace App\Modules\Attendance\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\NotificationLog;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    /**
     * Generate a non-destructive preview of monthly schedules (§5.2):
     * - Marks Saturday/Sunday and national holidays as 'L' (Libur)
     * - Preserves existing 'is_piket' schedules (`PK`, `PA`)
     * - Assigns default work shift (`P`) to regular workdays
     * - Counts how many existing schedules would be overwritten
     *
     * @return array{
     *   year: int,
     *   month: int,
     *   days_in_month: int,
     *   users_count: int,
     *   total_slots: int,
     *   preserved_piket_count: int,
     *   overwrite_count: int,
     *   holidays_count: int,
     *   entries: array<int, array<string, mixed>>
     * }
     */
    public function previewMonthlyGeneration(
        int $year,
        int $month,
        ?string $division = null,
        ?int $officeLocationId = null,
        string $defaultShiftCode = 'P',
        ?int $userId = null
    ): array {
        $startDate = Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $usersQuery = User::query()->where('is_active', true)->excludeAtasan()->orderBy('name');
        if ($userId) {
            $usersQuery->where('id', $userId);
        } elseif ($division) {
            $usersQuery->where('division', $division);
        }
        $users = $usersQuery->get();

        $defaultShift = Shift::query()->where('code', $defaultShiftCode)->firstOrFail();
        $offShift = Shift::query()->where('code', 'L')->firstOrFail();
        $office = $officeLocationId
            ? OfficeLocation::query()->findOrFail($officeLocationId)
            : OfficeLocation::query()->where('is_active', true)->firstOrFail();

        $holidays = NationalHoliday::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('name', 'date')
            ->all();

        $holidayDates = [];
        foreach ($holidays as $dt => $name) {
            $holidayDates[Carbon::parse((string) $dt)->toDateString()] = $name;
        }

        $existingSchedules = MonthlySchedule::query()
            ->with('shift')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn (MonthlySchedule $s): string => $s->user_id.'|'.$s->date->toDateString());

        $entries = [];
        $preservedPiketCount = 0;
        $overwriteCount = 0;

        foreach ($users as $user) {
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta');
                $dateStr = $currentDate->toDateString();
                $lookupKey = $user->id.'|'.$dateStr;

                /** @var MonthlySchedule|null $existing */
                $existing = $existingSchedules->get($lookupKey);

                if ($existing && ($existing->is_locked || ($existing->shift && $existing->shift->is_piket))) {
                    $preservedPiketCount++;
                    $entries[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'date' => $dateStr,
                        'shift_id' => $existing->shift_id,
                        'shift_code' => $existing->shift ? $existing->shift->code : 'PK',
                        'office_location_id' => $existing->office_location_id ?? $office->id,
                        'action' => 'preserve_piket',
                    ];

                    continue;
                }

                $isWeekendOrHoliday = $currentDate->isWeekend() || isset($holidayDates[$dateStr]);
                $targetShift = $isWeekendOrHoliday ? $offShift : $defaultShift;

                if ($existing && $existing->shift_id !== $targetShift->id) {
                    $overwriteCount++;
                }

                $entries[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'date' => $dateStr,
                    'shift_id' => $targetShift->id,
                    'shift_code' => $targetShift->code,
                    'office_location_id' => $office->id,
                    'action' => $existing ? 'overwrite' : 'create',
                ];
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'users_count' => $users->count(),
            'total_slots' => count($entries),
            'preserved_piket_count' => $preservedPiketCount,
            'overwrite_count' => $overwriteCount,
            'holidays_count' => count($holidayDates),
            'entries' => $entries,
        ];
    }

    /**
     * Commit confirmed monthly schedule generation inside a transaction with audit logging (§5.2).
     *
     * @return array<string, int>
     */
    public function commitMonthlyGeneration(
        int $year,
        int $month,
        int $actorId,
        ?string $division = null,
        ?int $officeLocationId = null,
        string $defaultShiftCode = 'P',
        ?int $userId = null
    ): array {
        $preview = $this->previewMonthlyGeneration($year, $month, $division, $officeLocationId, $defaultShiftCode, $userId);

        DB::transaction(function () use ($preview, $actorId): void {
            foreach ($preview['entries'] as $entry) {
                if ($entry['action'] === 'preserve_piket') {
                    continue;
                }

                $existingRow = MonthlySchedule::query()
                    ->where('user_id', $entry['user_id'])
                    ->whereDate('date', $entry['date'])
                    ->first();

                if ($existingRow) {
                    $existingRow->update([
                        'shift_id' => $entry['shift_id'],
                        'office_location_id' => $entry['office_location_id'],
                        'updated_by' => $actorId,
                    ]);
                } else {
                    MonthlySchedule::query()->create([
                        'user_id' => $entry['user_id'],
                        'date' => $entry['date'],
                        'shift_id' => $entry['shift_id'],
                        'office_location_id' => $entry['office_location_id'],
                        'updated_by' => $actorId,
                        'created_by' => $actorId,
                    ]);
                }
            }
        });

        AuditLogger::record(
            action: 'schedule.monthly_generated',
            newValues: [
                'year' => $year,
                'month' => $month,
                'division' => $division ?? 'ALL',
                'user_id' => $userId,
                'total_slots' => $preview['total_slots'],
                'preserved_piket_count' => $preview['preserved_piket_count'],
                'overwrite_count' => $preview['overwrite_count'],
            ],
            reason: "Auto-generate jadwal shift bulanan {$year}-{$month} (§5.2)",
            actorId: $actorId
        );

        return [
            'total_slots' => $preview['total_slots'],
            'preserved_piket_count' => $preview['preserved_piket_count'],
            'overwrite_count' => $preview['overwrite_count'],
        ];
    }

    /**
     * Get list of monthly schedules with employee count, days count, and status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMonthlyScheduleSummaries(): array
    {
        $records = MonthlySchedule::query()
            ->select('date', 'user_id')
            ->get();

        $grouped = [];
        foreach ($records as $row) {
            $dt = $row->date;
            $y = (int) $dt->year;
            $m = (int) $dt->month;
            $key = sprintf('%04d-%02d', $y, $m);
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'year' => $y,
                    'month' => $m,
                    'users' => [],
                    'dates' => [],
                ];
            }
            $grouped[$key]['users'][$row->user_id] = true;
            $grouped[$key]['dates'][$dt->toDateString()] = true;
        }

        // Include current month and recent 3 months if not in DB
        $current = Carbon::now('Asia/Jakarta');
        for ($i = 0; $i < 4; $i++) {
            $dt = $current->copy()->subMonths($i);
            $key = $dt->format('Y-m');
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'year' => (int) $dt->year,
                    'month' => (int) $dt->month,
                    'users' => [],
                    'dates' => [],
                ];
            }
        }

        krsort($grouped);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $monthCodes = [
            1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
            5 => 'MEI', 6 => 'JUN', 7 => 'JUL', 8 => 'AGU',
            9 => 'SEP', 10 => 'OKT', 11 => 'NOV', 12 => 'DES',
        ];

        $nowYear = $current->year;
        $nowMonth = $current->month;

        $results = [];
        foreach ($grouped as $item) {
            $y = $item['year'];
            $m = $item['month'];
            $daysInMonth = Carbon::createFromDate($y, $m, 1, 'Asia/Jakarta')->daysInMonth;
            $usersCount = count($item['users']);
            $daysCount = count($item['dates']);
            if ($daysCount === 0) {
                $daysCount = $daysInMonth;
            }

            $isOngoing = ($y > $nowYear) || ($y === $nowYear && $m >= $nowMonth);

            $results[] = [
                'year' => $y,
                'month' => $m,
                'month_name' => $monthNames[$m] ?? "Bulan {$m}",
                'month_code' => $monthCodes[$m] ?? "M{$m}",
                'users_count' => $usersCount,
                'days_count' => $daysCount,
                'days_in_month' => $daysInMonth,
                'status' => $isOngoing ? 'Berjalan' : 'Selesai',
                'is_ongoing' => $isOngoing,
                'has_schedules' => count($item['users']) > 0,
            ];
        }

        return $results;
    }

    /**
     * Delete all monthly schedules for a given year & month.
     */
    public function deleteMonthSchedules(int $year, int $month, int $actorId): int
    {
        $count = MonthlySchedule::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->delete();

        AuditLogger::record(
            action: 'schedule.month_deleted',
            newValues: ['year' => $year, 'month' => $month, 'deleted_count' => $count],
            reason: "Admin menghapus jadwal shift bulanan {$year}-{$month}",
            actorId: $actorId
        );

        return $count;
    }

    /**
     * Add national holiday and automatically update regular schedules to Libur ('L').
     */
    public function addHoliday(string $date, string $name, int $actorId): NationalHoliday
    {
        $holiday = NationalHoliday::query()->updateOrCreate(
            ['date' => Carbon::parse($date)->toDateString()],
            ['name' => $name, 'description' => 'Libur Nasional', 'is_collective_leave' => false]
        );

        $offShift = Shift::query()->where('code', 'L')->first();
        if ($offShift) {
            MonthlySchedule::query()
                ->whereDate('date', $holiday->date)
                ->where('is_locked', false)
                ->whereDoesntHave('shift', fn ($q) => $q->where('is_piket', true))
                ->update([
                    'shift_id' => $offShift->id,
                    'updated_by' => $actorId,
                ]);
        }

        AuditLogger::record(
            action: 'holiday.created',
            auditable: $holiday,
            newValues: ['date' => $holiday->date->toDateString(), 'name' => $name],
            reason: "Penambahan hari libur nasional {$name} ({$date})",
            actorId: $actorId
        );

        return $holiday;
    }

    /**
     * Delete national holiday.
     */
    public function deleteHoliday(NationalHoliday $holiday, int $actorId): void
    {
        $dateStr = $holiday->date->toDateString();
        $name = $holiday->name;
        $holiday->delete();

        AuditLogger::record(
            action: 'holiday.deleted',
            newValues: ['date' => $dateStr, 'name' => $name],
            reason: "Penghapusan hari libur nasional {$name}",
            actorId: $actorId
        );
    }

    /**
     * Import piket schedule from CSV file.
     *
     * @return array{total: int, imported: int, errors: array<string>}
     */
    public function importPiketCsv(string $filePath, int $actorId): array
    {
        if (! file_exists($filePath)) {
            return ['total' => 0, 'imported' => 0, 'errors' => ['File tidak ditemukan']];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return ['total' => 0, 'imported' => 0, 'errors' => ['File kosong']];
        }

        $header = str_getcsv(array_shift($lines));
        $headerMap = [];
        foreach ($header as $idx => $col) {
            $headerMap[strtolower(trim($col, "\xEF\xBB\xBF \t\n\r\0\x0B"))] = $idx;
        }

        $dateIdx = $headerMap['tanggal'] ?? $headerMap['date'] ?? 0;
        $codeIdx = $headerMap['kode_karyawan'] ?? $headerMap['kode'] ?? $headerMap['employee_code'] ?? 1;
        $shiftIdx = $headerMap['shift_code'] ?? $headerMap['shift'] ?? $headerMap['kode_shift'] ?? 3;

        $shifts = Shift::all()->keyBy(fn ($s) => strtoupper($s->code));
        $users = User::all()->keyBy(fn ($u) => strtoupper(trim((string) $u->employee_code)));

        $defaultOffice = OfficeLocation::query()->where('is_active', true)->first();

        $imported = 0;
        $total = 0;
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            $row = str_getcsv($line);
            if (count($row) < 3) {
                continue;
            }

            $total++;
            $dateRaw = trim($row[$dateIdx] ?? '');
            $userCodeRaw = strtoupper(trim($row[$codeIdx] ?? ''));
            $shiftCodeRaw = strtoupper(trim($row[$shiftIdx] ?? 'PK'));

            if (empty($dateRaw) || empty($userCodeRaw)) {
                $errors[] = "Baris #".($lineNum + 2).": Tanggal atau kode karyawan kosong.";
                continue;
            }

            $user = $users->get($userCodeRaw);
            if (! $user) {
                $errors[] = "Baris #".($lineNum + 2).": Karyawan dengan kode '{$userCodeRaw}' tidak ditemukan.";
                continue;
            }

            $shift = $shifts->get($shiftCodeRaw);
            if (! $shift) {
                $shift = $shifts->get('PK') ?? $shifts->first();
            }

            try {
                $date = Carbon::parse($dateRaw)->toDateString();
            } catch (\Exception $e) {
                $errors[] = "Baris #".($lineNum + 2).": Format tanggal tidak valid '{$dateRaw}'.";
                continue;
            }

            MonthlySchedule::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'shift_id' => $shift->id,
                    'office_location_id' => $defaultOffice?->id,
                    'is_locked' => true,
                    'updated_by' => $actorId,
                ]
            );

            $imported++;
        }

        AuditLogger::record(
            action: 'schedule.piket_imported',
            newValues: ['imported' => $imported, 'total' => $total],
            reason: "Import {$imported} jadwal piket dari file CSV",
            actorId: $actorId
        );

        return ['total' => $total, 'imported' => $imported, 'errors' => $errors];
    }

    /**
     * Detect shift rule conflicts such as < 11 hours rest gap between consecutive shifts (§5.2).
     *
     * @return array<int, array{user_id: int, user_name: string, prev_date: string, next_date: string, prev_shift: string, next_shift: string, rest_hours: float}>
     */
    public function detectRestConflicts(int $year, int $month, float $minRestHours = 11.0): array
    {
        $schedules = MonthlySchedule::query()
            ->with(['user', 'shift'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('user_id')
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $conflicts = [];

        foreach ($schedules as $userSchedules) {
            $prev = null;
            foreach ($userSchedules as $current) {
                if ($prev && $prev->shift && $current->shift && ! $prev->shift->is_day_off && ! $current->shift->is_day_off) {
                    if ($prev->date->copy()->addDay()->isSameDay($current->date) && $prev->shift->end_time && $current->shift->start_time) {
                        $prevEnd = Carbon::parse($prev->date->toDateString().' '.$prev->shift->end_time, 'Asia/Jakarta');
                        $nextStart = Carbon::parse($current->date->toDateString().' '.$current->shift->start_time, 'Asia/Jakarta');
                        $restHours = round($prevEnd->diffInMinutes($nextStart, false) / 60.0, 1);

                        if ($restHours < $minRestHours) {
                            $conflicts[] = [
                                'user_id' => $current->user_id,
                                'user_name' => $current->user ? $current->user->name : 'Karyawan',
                                'prev_date' => $prev->date->toDateString(),
                                'next_date' => $current->date->toDateString(),
                                'prev_shift' => $prev->shift->code.' (selesai '.$prev->shift->end_time.')',
                                'next_shift' => $current->shift->code.' (mulai '.$current->shift->start_time.')',
                                'rest_hours' => $restHours,
                            ];
                        }
                    }
                }
                $prev = $current;
            }
        }

        return $conflicts;
    }

    /**
     * Send shift reminders (§5.9):
     * 1) 1 hour before shift start (50 - 65 minutes before) to prompt attendance clock-in.
     * 2) 20 minutes before shift end (15 - 25 minutes before) to prompt attendance clock-out.
     *
     * @return array{
     *   clock_in_reminders: int,
     *   clock_out_reminders: int,
     *   total_notified: int,
     *   details: array<int, array<string, mixed>>
     * }
     */
    public function sendShiftReminders(?Carbon $officeNow = null, bool $force = false, ?int $userId = null, bool $test = false): array
    {
        $now = $officeNow ? $officeNow->copy()->setTimezone('Asia/Jakarta') : Carbon::now('Asia/Jakarta');
        $todayStr = $now->toDateString();

        $query = MonthlySchedule::query()
            ->with(['user', 'shift'])
            ->whereDate('date', $todayStr);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $schedules = $query->get();

        $fcmService = app(FcmService::class);
        $clockInCount = 0;
        $clockOutCount = 0;
        $details = [];

        foreach ($schedules as $schedule) {
            $shift = $schedule->shift;
            $user = $schedule->user;

            // Skip invalid, inactive users or day off (§5.9)
            if (! $shift || ! $user || $shift->is_day_off || ! $user->is_active) {
                continue;
            }

            $effectiveStartTime = $schedule->effective_start_time ?? ($shift->start_time ? substr((string) $shift->start_time, 0, 5) : null);
            $effectiveEndTime = $schedule->effective_end_time ?? ($shift->end_time ? substr((string) $shift->end_time, 0, 5) : null);

            if (empty($effectiveStartTime) || empty($effectiveEndTime)) {
                continue;
            }

            // Skip employees on approved leave (§5.9)
            $onApprovedLeave = Leave::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('is_alpa_conversion', false)
                ->whereDate('start_date', '<=', $todayStr)
                ->whereDate('end_date', '>=', $todayStr)
                ->exists();

            if ($onApprovedLeave && ! $test) {
                continue;
            }

            $shiftStart = Carbon::parse($todayStr . ' ' . $effectiveStartTime, 'Asia/Jakarta');
            $shiftEnd = Carbon::parse($todayStr . ' ' . $effectiveEndTime, 'Asia/Jakarta');

            $minsToStart = $now->diffInMinutes($shiftStart, false);
            $minsToEnd = $now->diffInMinutes($shiftEnd, false);
            $shiftName = $schedule->isCustomHours() ? 'Custom Jam Kerja' : $shift->name;

            // 1. CLOCK-IN REMINDER: 1 jam sebelum masuk shift (window 50 - 65 menit, atau jika force <= 75 & >= 0, atau bypass jika test)
            $isClockInWindow = $test
                ? true
                : ($force ? ($minsToStart >= 0 && $minsToStart <= 75) : ($minsToStart >= 50 && $minsToStart <= 65));

            if ($isClockInWindow) {
                // Skip if employee already clocked in today (kecuali mode test)
                $alreadyClockedIn = ! $test && Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', $todayStr)
                    ->whereNotNull('clock_in_at')
                    ->exists();

                if (! $alreadyClockedIn) {
                    $dedupKey = "shift_reminder_in_{$user->id}_{$todayStr}_{$schedule->id}";
                    $alreadyNotified = ! $force && ! $test && (
                        Cache::has($dedupKey) ||
                        NotificationLog::query()
                            ->where('user_id', $user->id)
                            ->where('event_type', 'shift_reminder_clock_in')
                            ->whereDate('created_at', $todayStr)
                            ->exists()
                    );

                    if (! $alreadyNotified) {
                        AuditLogger::record(
                            action: 'notification.shift_reminder.clock_in',
                            auditable: $schedule,
                            newValues: ['shift' => $shift->code, 'mins_before_start' => $minsToStart],
                            reason: 'Pengingat otomatis 1 jam sebelum masuk shift (§5.9)',
                            actorId: $user->id
                        );

                        $title = "⏰ Pengingat Absen Masuk ({$shiftName})";
                        $body = "Halo {$user->name}, 1 jam lagi jadwal kerja Anda ({$shiftName}) dimulai pukul {$effectiveStartTime} WIB. Jangan lupa lakukan presensi masuk!";
                        $deepLink = url('/attendance');

                        $fcmService->sendToUsers(
                            $user->id,
                            'absen',
                            $title,
                            $body,
                            $deepLink,
                            [
                                'tag' => "shift-reminder-in-{$user->id}-{$todayStr}",
                                'type' => 'absen',
                                'shift_code' => (string) $shift->code,
                                'start_time' => (string) $effectiveStartTime,
                                'schedule_id' => (string) $schedule->id,
                            ]
                        );

                        NotificationLog::query()->create([
                            'user_id' => $user->id,
                            'channel' => 'fcm',
                            'event_type' => 'shift_reminder_clock_in',
                            'recipient' => $user->name . ' (' . ($user->email ?? $user->username) . ')',
                            'payload' => [
                                'title' => $title,
                                'body' => $body,
                                'shift_code' => $shift->code,
                                'shift_name' => $shiftName,
                                'start_time' => $effectiveStartTime,
                                'mins_before_start' => $minsToStart,
                                'deep_link' => $deepLink,
                            ],
                            'status' => 'sent',
                            'attempts' => 1,
                            'sent_at' => Carbon::now('Asia/Jakarta'),
                        ]);

                        Cache::put($dedupKey, true, now()->addHours(12));
                        $clockInCount++;
                        $details[] = [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'type' => 'clock_in',
                            'shift' => $shiftName,
                            'time' => $effectiveStartTime,
                            'mins_diff' => $minsToStart,
                        ];
                    }
                }
            }

            // 2. CLOCK-OUT REMINDER: 20 menit sebelum pulang shift (window 15 - 25 menit)
            $isClockOutWindow = $force ? ($minsToEnd >= 0 && $minsToEnd <= 35) : ($minsToEnd >= 15 && $minsToEnd <= 25);
            if ($isClockOutWindow) {
                // Skip if employee already clocked out today
                $alreadyClockedOut = Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereDate('date', $todayStr)
                    ->whereNotNull('clock_out_at')
                    ->exists();

                if (! $alreadyClockedOut) {
                    $dedupKeyOut = "shift_reminder_out_{$user->id}_{$todayStr}_{$schedule->id}";
                    $alreadyNotifiedOut = ! $force && (
                        Cache::has($dedupKeyOut) ||
                        NotificationLog::query()
                            ->where('user_id', $user->id)
                            ->where('event_type', 'shift_reminder_clock_out')
                            ->whereDate('created_at', $todayStr)
                            ->exists()
                    );

                    if (! $alreadyNotifiedOut) {
                        AuditLogger::record(
                            action: 'notification.shift_reminder.clock_out',
                            auditable: $schedule,
                            newValues: ['shift' => $shift->code, 'mins_before_end' => $minsToEnd],
                            reason: 'Pengingat otomatis 20 menit sebelum pulang shift (§5.9)',
                            actorId: $user->id
                        );

                        $title = "⏰ Pengingat Absen Pulang ({$shiftName})";
                        $body = "Halo {$user->name}, jam kerja Anda ({$shiftName}) berakhir pukul {$effectiveEndTime} WIB. Jangan lupa lakukan presensi pulang!";
                        $deepLink = url('/attendance');

                        $fcmService->sendToUsers(
                            $user->id,
                            'absen',
                            $title,
                            $body,
                            $deepLink,
                            [
                                'tag' => "shift-reminder-out-{$user->id}-{$todayStr}",
                                'type' => 'absen',
                                'shift_code' => (string) $shift->code,
                                'end_time' => (string) $effectiveEndTime,
                                'schedule_id' => (string) $schedule->id,
                            ]
                        );

                        NotificationLog::query()->create([
                            'user_id' => $user->id,
                            'channel' => 'fcm',
                            'event_type' => 'shift_reminder_clock_out',
                            'recipient' => $user->name . ' (' . ($user->email ?? $user->username) . ')',
                            'payload' => [
                                'title' => $title,
                                'body' => $body,
                                'shift_code' => $shift->code,
                                'shift_name' => $shiftName,
                                'end_time' => $effectiveEndTime,
                                'mins_before_end' => $minsToEnd,
                                'deep_link' => $deepLink,
                            ],
                            'status' => 'sent',
                            'attempts' => 1,
                            'sent_at' => Carbon::now('Asia/Jakarta'),
                        ]);

                        Cache::put($dedupKeyOut, true, now()->addHours(12));
                        $clockOutCount++;
                        $details[] = [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'type' => 'clock_out',
                            'shift' => $shiftName,
                            'time' => $effectiveEndTime,
                            'mins_diff' => $minsToEnd,
                        ];
                    }
                }
            }
        }

        return [
            'total_schedules' => $schedules->count(),
            'clock_in_reminders' => $clockInCount,
            'clock_out_reminders' => $clockOutCount,
            'total_notified' => $clockInCount + $clockOutCount,
            'details' => $details,
        ];
    }
}
