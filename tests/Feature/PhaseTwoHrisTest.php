<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\DeductionAdjustment;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\Shift;
use App\Models\ShiftSwap;
use App\Models\SystemSetting;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseTwoHrisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_four_way_cross_date_shift_swap_and_stale_schedule_guard(): void
    {
        $manager = User::query()->where('username', 'mgr_teknis')->firstOrFail();
        $teknisi1 = User::query()->where('username', 'teknisi1')->firstOrFail();

        // Create a second technician in the same division ('Operasional Jaringan')
        $teknisi2 = User::query()->create([
            'employee_code' => 'MSN-008',
            'username' => 'teknisi2',
            'name' => 'Budi Santoso',
            'email' => 'budi.teknisi@msn.co.id',
            'phone' => '08112000008',
            'division' => 'Operasional Jaringan',
            'position' => 'Teknisi FO',
            'supervisor_id' => $manager->id,
            'join_date' => '2023-01-10',
            'is_active' => true,
            'password' => bcrypt('MsnPortal#2026'),
        ]);
        $teknisi2->assignRole('teknisi');

        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $shiftSiang = Shift::query()->where('code', 'S')->firstOrFail();
        $shiftSubuh = Shift::query()->where('code', 'SB')->firstOrFail();

        $d1 = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();
        $d2 = Carbon::now('Asia/Jakarta')->addDays(4)->toDateString();

        // Seed initial 4 schedule slots:
        // D1: teknisi1 = P, teknisi2 = S
        // D2: teknisi1 = S, teknisi2 = P
        $this->upsertSchedule($teknisi1->id, $d1, $shiftPagi->id);
        $this->upsertSchedule($teknisi2->id, $d1, $shiftSiang->id);
        $this->upsertSchedule($teknisi1->id, $d2, $shiftSiang->id);
        $this->upsertSchedule($teknisi2->id, $d2, $shiftPagi->id);

        // 1. Notice: after swapping D1 and D2, teknisi2 would have P on D1 and S on D2 (rest 20h OK),
        // while teknisi1 would have S (ends 21:00) on D1 and P (starts 08:15) on D2 -> rest 11.25h (>= 11h OK!).
        // Let's first test < 11h rest conflict rejection if D2 had Shift Subuh (06:00, rest 9h < 11h):
        $this->upsertSchedule($teknisi2->id, $d2, $shiftSubuh->id);

        $conflictRes = $this->actingAs($teknisi1)->post(route('attendance.swaps.store'), [
            'target_user_id' => $teknisi2->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => false,
            'reason' => 'Keperluan keluarga lintas tanggal',
        ]);
        $conflictRes->assertSessionHasErrors('swap');

        // Restore D2 teknisi2 to Shift Pagi (08:15 -> 11.25h rest gap >= 11h)
        $this->upsertSchedule($teknisi2->id, $d2, $shiftPagi->id);

        $createRes = $this->actingAs($teknisi1)->post(route('attendance.swaps.store'), [
            'target_user_id' => $teknisi2->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => false,
            'reason' => 'Tukar jadwal silang 4 arah yang valid',
        ]);
        $createRes->assertSessionHasNoErrors();

        // Duplicate active request on the same slot must be blocked (§5.3)
        $dupRes = $this->actingAs($teknisi1)->post(route('attendance.swaps.store'), [
            'target_user_id' => $teknisi2->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => false,
            'reason' => 'Duplikat request pada slot yang sama',
        ]);
        $dupRes->assertSessionHasErrors('swap');

        $swapId = (int) ShiftSwap::query()->latest('id')->value('id');

        // Target approves
        $this->actingAs($teknisi2)
            ->post(route('attendance.swaps.approve-target', $swapId))
            ->assertSessionHasNoErrors();

        // Supervisor approves -> executes atomic 4-way swap with lockForUpdate
        $this->actingAs($manager)
            ->post(route('attendance.swaps.approve-supervisor', $swapId))
            ->assertSessionHasNoErrors();

        // Verify all 4 slots swapped!
        $this->assertSame($shiftSiang->id, MonthlySchedule::query()->where('user_id', $teknisi1->id)->whereDate('date', $d1)->value('shift_id'));
        $this->assertSame($shiftPagi->id, MonthlySchedule::query()->where('user_id', $teknisi2->id)->whereDate('date', $d1)->value('shift_id'));
        $this->assertSame($shiftPagi->id, MonthlySchedule::query()->where('user_id', $teknisi1->id)->whereDate('date', $d2)->value('shift_id'));
        $this->assertSame($shiftSiang->id, MonthlySchedule::query()->where('user_id', $teknisi2->id)->whereDate('date', $d2)->value('shift_id'));
    }

    public function test_leave_quota_monthly_cap_cancellation_override_lock_and_daily_alpa_sync(): void
    {
        Storage::fake('private');

        $hrd = User::query()->where('username', 'hrd')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_teknis')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        // Find next Monday so 4 consecutive days are working days
        $nextMonday = Carbon::now('Asia/Jakarta')->next(Carbon::MONDAY);

        // 1. Requesting 4 working days of annual leave in a single month must be rejected (max 3 working days/month §5.4)
        $resOverCap = $this->actingAs($teknisi)->post(route('attendance.leaves.store'), [
            'leave_type' => 'annual',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->copy()->addDays(3)->toDateString(),
            'reason' => 'Cuti 4 hari kerja sekaligus',
        ]);
        $resOverCap->assertSessionHasErrors('leave');

        // 2. Sick leave > 1 working day without medical attachment must be rejected (§5.4)
        $resSickNoDoc = $this->actingAs($teknisi)->post(route('attendance.leaves.store'), [
            'leave_type' => 'sick',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->copy()->addDay()->toDateString(),
            'reason' => 'Demam tinggi 2 hari tanpa lampiran',
        ]);
        $resSickNoDoc->assertSessionHasErrors('leave');

        // 3. Valid 2-day annual leave request -> approved -> then cancelled before start_date restores balance (§5.4)
        $this->actingAs($teknisi)->post(route('attendance.leaves.store'), [
            'leave_type' => 'annual',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->copy()->addDay()->toDateString(),
            'reason' => 'Cuti keluarga 2 hari kerja',
        ])->assertSessionHasNoErrors();

        /** @var Leave $leave */
        $leave = Leave::query()->where('user_id', $teknisi->id)->latest('id')->firstOrFail();
        $this->assertSame(2, $leave->working_days);

        $this->actingAs($manager)->post(route('attendance.leaves.approve-supervisor', $leave))->assertSessionHasNoErrors();
        $this->actingAs($hrd)->post(route('attendance.leaves.approve-hrd', $leave))->assertSessionHasNoErrors();

        $year = (int) $nextMonday->format('Y');
        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $teknisi->id,
            'year' => $year,
            'used_days' => 2,
            'remaining_days' => 10,
        ]);

        // Cancel before start_date -> restores remaining_days back to 12!
        $this->actingAs($teknisi)->post(route('attendance.leaves.cancel', $leave), [
            'reason' => 'Acara keluarga ditunda',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $teknisi->id,
            'year' => $year,
            'used_days' => 0,
            'remaining_days' => 12,
        ]);

        // 4. Test daily idempotent `leave:sync` converting Alpa to leave with 3-workday dispute window (§5.4)
        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $alpaDate = Carbon::now('Asia/Jakarta')->subDays(2)->toDateString();
        $this->upsertSchedule($teknisi->id, $alpaDate, $shiftPagi->id);

        // Run leave:sync twice to verify idempotency
        $this->artisan("leave:sync --date={$alpaDate}")->assertExitCode(0);
        $this->artisan("leave:sync --date={$alpaDate}")->assertExitCode(0);

        $this->assertSame(
            1,
            Leave::query()
                ->where('user_id', $teknisi->id)
                ->where('is_alpa_conversion', true)
                ->whereDate('start_date', $alpaDate)
                ->count()
        );

        // 5. Test HRD override lock preventing `leave:sync` from deducting locked balance
        $this->actingAs($hrd)->post(route('attendance.leaves.override-balance'), [
            'user_id' => $teknisi->id,
            'year' => (int) Carbon::parse($alpaDate)->format('Y'),
            'remaining_days' => 15,
            'is_locked_override' => true,
            'reason' => 'Penyesuaian khusus HRD & kunci saldo',
        ])->assertSessionHasNoErrors();

        $secondAlpaDate = Carbon::now('Asia/Jakarta')->subDays(3)->toDateString();
        $this->upsertSchedule($teknisi->id, $secondAlpaDate, $shiftPagi->id);
        $this->artisan("leave:sync --date={$secondAlpaDate}")->assertExitCode(0);

        // Balance must remain 15 because is_locked_override = true
        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $teknisi->id,
            'year' => (int) Carbon::parse($alpaDate)->format('Y'),
            'remaining_days' => 15,
            'is_locked_override' => true,
        ]);
    }

    public function test_ac09_period_locking_freezes_deductions_and_post_closing_correction_creates_adjustment(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $hrd = User::query()->where('username', 'hrd')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_teknis')->firstOrFail();
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $targetDate = Carbon::now('Asia/Jakarta')->subDay()->startOfDay();
        $dateStr = $targetDate->toDateString();
        $year = (int) $targetDate->format('Y');
        $month = (int) $targetDate->format('n');

        $this->upsertSchedule($teknisi->id, $dateStr, $shiftPagi->id);

        // Create a Late attendance (40 minutes late -> 40 * Rp 250 = Rp 10,000.00)
        Attendance::query()->updateOrCreate(
            ['user_id' => $teknisi->id, 'date' => $dateStr],
            [
                'shift_id' => $shiftPagi->id,
                'attendance_type' => 'office',
                'status' => 'late',
                'source' => 'face',
                'clock_in_at' => Carbon::parse($dateStr.' 08:55:00', 'Asia/Jakarta')->utc(),
                'clock_out_at' => Carbon::parse($dateStr.' 17:05:00', 'Asia/Jakarta')->utc(),
                'late_minutes' => 40,
            ]
        );

        /** @var DeductionService $deductionService */
        $deductionService = app(DeductionService::class);
        $reportBeforeLock = $deductionService->calculateUserMonthlyReport($teknisi, $year, $month);
        $this->assertSame('10000.00', $reportBeforeLock['total_late_deduction']);

        // HRD locks the period (AC-09)
        $this->actingAs($hrd)->post(route('attendance.deductions.lock-period'), [
            'year' => $year,
            'month' => $month,
            'notes' => 'Tutup buku payroll bulanan',
        ])->assertSessionHasNoErrors();

        // Now Super Admin changes late fee from Rp 250 to Rp 500 effective immediately (AC-09)
        SystemSetting::query()->create([
            'key' => 'attendance.late_fee_per_minute',
            'group' => 'attendance',
            'label' => 'Tarif Denda Keterlambatan per Menit (Rp)',
            'value' => '500',
            'type' => 'decimal',
            'effective_from' => $targetDate->copy()->startOfMonth(),
            'updated_by' => $admin->id,
            'change_reason' => 'Kenaikan tarif uji AC-09',
        ]);

        // Locked period MUST NOT change! Still Rp 10,000.00 (AC-09)
        $reportAfterRateChange = $deductionService->calculateUserMonthlyReport($teknisi, $year, $month);
        $this->assertTrue($reportAfterRateChange['is_locked']);
        $this->assertSame('10000.00', $reportAfterRateChange['total_late_deduction']);

        // Submit an Attendance Correction for that date -> after HRD approves, since period is locked,
        // it must create a DeductionAdjustment in the next month and log before/after in audit_logs (§5.5, §5.6, AC-09)
        $this->actingAs($teknisi)->post(route('attendance.corrections.store'), [
            'date' => $dateStr,
            'requested_status' => 'present',
            'requested_attendance_type' => 'field',
            'requested_clock_in_time' => '08:05',
            'requested_clock_out_time' => '17:05',
            'reason' => 'Gangguan sinyal saat tugas lapangan bersama tim FO',
        ])->assertSessionHasNoErrors();

        $correction = AttendanceCorrection::query()->where('user_id', $teknisi->id)->latest('id')->firstOrFail();

        $this->actingAs($manager)->post(route('attendance.corrections.approve-supervisor', $correction))->assertSessionHasNoErrors();
        $this->actingAs($hrd)->post(route('attendance.corrections.approve-hrd', $correction))->assertSessionHasNoErrors();

        $correction->refresh();
        $this->assertTrue($correction->is_post_closing_adjustment);

        // Verify Attendance was updated with source = 'correction'
        $updatedAtt = Attendance::query()->where('user_id', $teknisi->id)->whereDate('date', $dateStr)->firstOrFail();
        $this->assertSame('correction', $updatedAtt->source);
        $this->assertSame('present', $updatedAtt->status);
        $this->assertSame(0, $updatedAtt->late_minutes);

        // Verify post-closing DeductionAdjustment was created for the next month
        $nextMonth = $targetDate->copy()->addMonth();
        $this->assertTrue(
            DeductionAdjustment::query()
                ->where('user_id', $teknisi->id)
                ->where('applied_year', (int) $nextMonth->format('Y'))
                ->where('applied_month', (int) $nextMonth->format('n'))
                ->exists()
        );

        // Verify audit_logs recorded old and new values
        $this->assertTrue(
            AuditLog::query()
                ->where('action', 'attendance.correction_hrd_approved')
                ->whereNotNull('old_values')
                ->whereNotNull('new_values')
                ->exists()
        );
    }

    public function test_leaderboard_ranking_opt_out_privacy_and_utf8_bom_csv_export(): void
    {
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();
        $helpdesk = User::query()->where('username', 'helpdesk')->firstOrFail();

        // Toggle opt-out for teknisi
        $this->actingAs($teknisi)
            ->post(route('attendance.leaderboard.opt-out'))
            ->assertSessionHasNoErrors();

        $this->assertTrue((bool) $teknisi->fresh()?->leaderboard_opt_out);

        // View leaderboard as helpdesk -> teknisi's name must be masked
        $pageRes = $this->actingAs($helpdesk)->get(route('attendance.leaderboard'));
        $pageRes->assertOk();
        $pageRes->assertSee('Karyawan Anonim (Opt-Out Privasi)');

        // Export CSV -> must start with UTF-8 BOM (\xEF\xBB\xBF)
        $csvRes = $this->actingAs($helpdesk)->get(route('attendance.leaderboard.export'));
        $csvRes->assertOk();
        $content = $csvRes->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
    }

    private function upsertSchedule(int $userId, string $dateStr, int $shiftId): void
    {
        $existing = MonthlySchedule::query()
            ->where('user_id', $userId)
            ->whereDate('date', $dateStr)
            ->first();

        if ($existing) {
            $existing->update(['shift_id' => $shiftId]);
        } else {
            MonthlySchedule::query()->create([
                'user_id' => $userId,
                'date' => $dateStr,
                'shift_id' => $shiftId,
            ]);
        }
    }
}
