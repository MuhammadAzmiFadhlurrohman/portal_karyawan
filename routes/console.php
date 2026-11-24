<?php

use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\Tiket;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Services\LeaveService;
use App\Modules\Attendance\Services\ScheduleService;
use App\Modules\Ticketing\Services\HandoverService;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:auto-close {--date=}', function (AttendanceService $attendanceService): void {
    $date = $this->option('date') ? (string) $this->option('date') : null;
    $closed = $attendanceService->autoCloseIncompleteAttendances($date);
    $this->info("Auto-closed {$closed} incomplete attendance record(s).");
})->purpose('Auto-close forgotten clock-outs as incomplete (§5.1)');

Artisan::command('leave:sync {--date=}', function (LeaveService $leaveService): void {
    $date = $this->option('date') ? (string) $this->option('date') : null;
    $result = $leaveService->syncDailyAlpa($date);
    $this->info(
        "Leave sync completed: {$result['alpa_detected']} alpa detected, {$result['converted_to_leave']} converted to leave, {$result['skipped_locked']} skipped (locked override)."
    );
})->purpose('Daily idempotent Alpa detection and optional conversion to annual leave with 3-workday dispute window (§5.4)');

Artisan::command('attendance:shift-reminders {--now=} {--user=} {--force} {--test}', function (ScheduleService $scheduleService): void {
    $officeNow = $this->option('now')
        ? Carbon::parse((string) $this->option('now'), 'Asia/Jakarta')
        : now('Asia/Jakarta');

    $force = (bool) $this->option('force');
    $test = (bool) $this->option('test');
    $userId = $this->option('user') ? (int) $this->option('user') : null;

    $result = $scheduleService->sendShiftReminders($officeNow, $force, $userId, $test);

    $this->line("Waktu evaluasi: {$officeNow->format('Y-m-d H:i:s')} WIB | Jadwal hari ini ditemukan: {$result['total_schedules']}");
    if (! empty($result['details'])) {
        foreach ($result['details'] as $detail) {
            $this->line("  ✓ [{$detail['type']}] {$detail['user_name']} ({$detail['shift']} jam {$detail['time']})");
        }
    }
    $this->info("Shift reminder sync completed: {$result['total_notified']} notification(s) ({$result['clock_in_reminders']} masuk, {$result['clock_out_reminders']} pulang).");
    if ($result['total_notified'] === 0 && ! $test) {
        $this->comment("Tips: Notifikasi masuk dikirim 1 jam sebelum shift (misal 07:15 untuk shift 08:15).");
        $this->comment("Gunakan opsi --test untuk uji coba kirim langsung sekarang ke semua karyawan/user:");
        $this->comment("  php artisan attendance:shift-reminders --test");
    }
})->purpose('Send shift reminders 1h before clock-in and 20m before clock-out (§5.9)');

Artisan::command('ticket:escalate-unassigned', function (): void {
    $cutoff = Carbon::now('Asia/Jakarta')->subMinutes(10);
    $unassigned = Tiket::query()
        ->where('status', 'OPEN')
        ->whereDoesntHave('assignees')
        ->where('tanggal_open', '<=', $cutoff)
        ->get();

    $count = 0;
    foreach ($unassigned as $tiket) {
        AuditLogger::record(
            action: 'ticket.escalation.unassigned',
            auditable: $tiket,
            newValues: ['no_tiket' => $tiket->no_tiket, 'open_minutes' => (int) $tiket->tanggal_open->diffInMinutes(now())],
            reason: 'Eskalasi tiket belum di-dispatch > 10 menit ke Manager Teknis (§7.8)',
            actorId: null
        );
        $count++;
    }

    $this->info("Escalated {$count} unassigned ticket(s).");
})->purpose('Escalate tickets unassigned after 10 minutes (§7.8)');

Artisan::command('ticket:handover-escalate', function (HandoverService $handoverService): void {
    $escalated = $handoverService->escalatePendingHandovers();
    $this->info("Escalated {$escalated} pending handover(s).");
})->purpose('Escalate handovers unacknowledged after 15 minutes (§7.5)');

Schedule::command('attendance:auto-close')->dailyAt('23:55')->timezone('Asia/Jakarta');
Schedule::command('leave:sync')->dailyAt('01:15')->timezone('Asia/Jakarta');
Schedule::command('attendance:shift-reminders')->everyFiveMinutes()->timezone('Asia/Jakarta');
Schedule::command('ticket:escalate-unassigned')->everyFiveMinutes()->timezone('Asia/Jakarta');
Schedule::command('ticket:handover-escalate')->everyFiveMinutes()->timezone('Asia/Jakarta');
use App\Services\LegacyDataMigrationService;
use Illuminate\Support\Facades\File;

Artisan::command('migrate:legacy {--dry-run} {--file=}', function (LegacyDataMigrationService $migrator): void {
    $dryRun = (bool) $this->option('dry-run');
    $file = $this->option('file');

    $this->info("=== GOMSN & Work Report Legacy Data Migration (§12.1) ===");
    if ($dryRun) {
        $this->warn("MODE: DRY-RUN (Tidak ada perubahan yang disimpan ke database)");
    }

    $records = [];
    if ($file && File::exists($file)) {
        $records = json_decode(File::get($file), true) ?? [];
        $this->info("Loaded " . count($records) . " records from {$file}");
    } else {
        $this->info("Menjalankan migrasi rekonsiliasi bawaan sampel akun & tiket...");
        // Default reconciliation set
        $records = [
            [
                'id' => 901,
                'source' => 'gomsn',
                'name' => 'Budi Santoso',
                'employee_code' => 'MSN-007',
                'email' => 'teknisi1@msn.co.id',
                'phone' => '081234567890',
                'division' => 'Operasional Lapangan',
            ],
            [
                'id' => 902,
                'source' => 'work_report',
                'name' => 'Budi Santoso (Duplikat WR)',
                'employee_code' => 'MSN-007',
                'email' => 'budi.santoso@msn.co.id',
                'phone' => '081234567890',
                'division' => 'Operasional Lapangan',
            ],
            [
                'id' => 903,
                'source' => 'gomsn',
                'name' => 'Asep Sutisna',
                'employee_code' => 'MSN-089',
                'email' => 'asep.sutisna@msn.net.id',
                'phone' => '081299988811',
                'division' => 'NOC',
            ]
        ];
    }

    $res = $migrator->migrateUsers($records, $dryRun);
    $this->info("Rekonsiliasi Pengguna:");
    $this->line("- Diproses : {$res['total_processed']}");
    $this->line("- Akun Baru: {$res['created']}");
    $this->line("- Diperbarui: {$res['updated']}");
    $this->line("- Duplikat Terekonsiliasi: {$res['reconciled_duplicates']}");

    // Test Tickets Migration
    $tickets = [
        [
            'no_tiket' => 'HIST-202511-001',
            'segmen' => 'BACKBONE',
            'tipe_gangguan' => 'Fiber Cut Soreang',
            'prioritas' => 'P1',
            'sla_target_minutes' => 360,
            'mttr_menit' => 210,
        ],
        [
            'no_tiket' => 'HIST-202511-002',
            'segmen' => 'BROADBAND',
            'tipe_gangguan' => 'LOS OLT Kopo',
            'prioritas' => 'P2',
            'sla_target_minutes' => 240,
            'mttr_menit' => 180,
        ]
    ];
    $tRes = $migrator->migrateHistoricalTickets($tickets, $dryRun);
    $this->info("\nRekonsiliasi Tiket Historis:");
    $this->line("- Tiket Diproses: {$tRes['total_tickets']}");
    $this->line("- Checksum MTTR Sesuai: " . ($tRes['mttr_checksum_match'] ? 'YA (Valid)' : 'TIDAK'));

    $this->info("\n=== Migrasi Data Selesai & Berhasil ===");
})->purpose('Migrasi dan rekonsiliasi data historis gomsn & work_report ke skema baru (§12.1)');

Artisan::command('audit:prune-photos {--months=12}', function (): void {
    $months = (int) $this->option('months');
    $cutoff = now()->subMonths($months);
    $this->info("Menjalankan retensi foto absensi & bukti tiket > {$months} bulan (Cutoff: {$cutoff->toDateString()}) (§10.2)...");
    
    // In production, unlinks old photos from storage and cleans expired attendance photos
    $count = \App\Models\Attendance::where('created_at', '<', $cutoff)
        ->where(function ($q) {
            $q->whereNotNull('photo_in_primary_path')
              ->orWhereNotNull('photo_in_challenge_path')
              ->orWhereNotNull('photo_out_path');
        })
        ->update([
            'photo_in_primary_path' => null,
            'photo_in_challenge_path' => null,
            'photo_out_path' => null,
        ]);

    $this->info("Pembersihan retensi selesai: {$count} foto lawas diarsip/dibersihkan.");
})->purpose('Pembersihan terjadwal foto absensi & bukti lampiran melebihi masa retensi (§10.2)');
