<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\Shift;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use App\Modules\Attendance\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAndAlpaDeductionLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_annual_leave_quota_defaults_to_12_days_and_monthly_cap_is_3_days(): void
    {
        /** @var User $teknisi */
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();

        $leaveService = app(LeaveService::class);
        $balance = $leaveService->getOrCreateBalance($teknisi, 2026);

        // 1 tahun jatah cuti 12
        $this->assertSame(12, $balance->annual_quota);
        $this->assertSame(12, $balance->remaining_days);

        // Mencoba mengajukan cuti 4 hari kerja sekaligus dalam 1 bulan harus ditolak (maks 3 hari/bulan)
        $nextMonday = Carbon::parse('2026-11-02', 'Asia/Jakarta');
        $response = $this->actingAs($teknisi)->post(route('attendance.leaves.store'), [
            'leave_type' => 'annual',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->copy()->addDays(3)->toDateString(),
            'reason' => 'Pengajuan cuti 4 hari kerja',
        ]);

        $response->assertSessionHasErrors('leave');
    }

    public function test_first_three_alpa_in_month_cut_leave_quota_without_salary_deduction(): void
    {
        /** @var User $teknisi */
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();
        $teknisi->update(['potongan_alpa' => 50000.00]);

        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $year = 2026;
        $month = 11;

        // Buat 3 hari absen (alpa) pada bulan November 2026
        $dates = ['2026-11-02', '2026-11-03', '2026-11-04'];
        foreach ($dates as $d) {
            MonthlySchedule::query()->updateOrCreate(
                ['user_id' => $teknisi->id, 'date' => $d],
                ['shift_id' => $shiftPagi->id, 'office_location_id' => 1]
            );
            Attendance::query()->updateOrCreate(
                ['user_id' => $teknisi->id, 'date' => $d],
                [
                    'shift_id' => $shiftPagi->id,
                    'status' => 'absent',
                    'source' => 'manual',
                    'notes' => 'Alpa tercatat',
                ]
            );
        }

        /** @var DeductionService $deductionService */
        $deductionService = app(DeductionService::class);
        $report = $deductionService->calculateUserMonthlyReport($teknisi, $year, $month);

        // 3 alpa pertama dikonversi memotong cuti tahunan, potongan gaji Alpa = Rp 0
        $this->assertSame('0.00', $report['total_alpa_deduction']);
        $this->assertSame(0, $report['total_alpa_days']);

        /** @var LeaveService $leaveService */
        $leaveService = app(LeaveService::class);
        $balance = $leaveService->getOrCreateBalance($teknisi, $year);

        // Kuota cuti terpotong 3 hari (12 - 3 = 9 sisa hari)
        $this->assertSame(3, $balance->alpa_deducted_days);
        $this->assertSame(9, $balance->remaining_days);

        // 3 record Leave alpa_conversion dibuat
        $this->assertSame(
            3,
            Leave::query()
                ->where('user_id', $teknisi->id)
                ->where('is_alpa_conversion', true)
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->count()
        );
    }

    public function test_fourth_alpa_in_month_exceeds_monthly_cap_and_deducts_salary(): void
    {
        /** @var User $teknisi */
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();
        $teknisi->update(['potongan_alpa' => 75000.00]);

        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $year = 2026;
        $month = 11;

        // Buat 4 hari alpa dalam 1 bulan
        $dates = ['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05'];
        foreach ($dates as $d) {
            MonthlySchedule::query()->updateOrCreate(
                ['user_id' => $teknisi->id, 'date' => $d],
                ['shift_id' => $shiftPagi->id, 'office_location_id' => 1]
            );
            Attendance::query()->updateOrCreate(
                ['user_id' => $teknisi->id, 'date' => $d],
                [
                    'shift_id' => $shiftPagi->id,
                    'status' => 'absent',
                    'source' => 'manual',
                    'notes' => 'Alpa tercatat',
                ]
            );
        }

        /** @var DeductionService $deductionService */
        $deductionService = app(DeductionService::class);
        $report = $deductionService->calculateUserMonthlyReport($teknisi, $year, $month);

        // 3 alpa memotong cuti, alpa ke-4 ditetapkan sebagai potong gaji
        $this->assertSame(1, $report['total_alpa_days']);
        $this->assertSame('75000.00', $report['total_alpa_deduction']);

        /** @var LeaveService $leaveService */
        $leaveService = app(LeaveService::class);
        $balance = $leaveService->getOrCreateBalance($teknisi, $year);

        // Cuti hanya terpotong 3 hari (maksimal 3 hari per bulan)
        $this->assertSame(3, $balance->alpa_deducted_days);
        $this->assertSame(9, $balance->remaining_days);
    }

    public function test_alpa_when_annual_quota_exhausted_directly_deducts_salary(): void
    {
        /** @var User $teknisi */
        $teknisi = User::query()->where('username', 'teknisi1')->firstOrFail();
        $teknisi->update(['potongan_alpa' => 60000.00]);

        $year = 2026;
        $month = 11;

        // Habiskan kuota cuti tahunan (sisa 0)
        /** @var LeaveService $leaveService */
        $leaveService = app(LeaveService::class);
        $balance = $leaveService->getOrCreateBalance($teknisi, $year);
        $balance->update([
            'used_days' => 12,
            'remaining_days' => 0,
        ]);

        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $date = '2026-11-09';

        MonthlySchedule::query()->updateOrCreate(
            ['user_id' => $teknisi->id, 'date' => $date],
            ['shift_id' => $shiftPagi->id, 'office_location_id' => 1]
        );
        Attendance::query()->updateOrCreate(
            ['user_id' => $teknisi->id, 'date' => $date],
            [
                'shift_id' => $shiftPagi->id,
                'status' => 'absent',
                'source' => 'manual',
            ]
        );

        /** @var DeductionService $deductionService */
        $deductionService = app(DeductionService::class);
        $report = $deductionService->calculateUserMonthlyReport($teknisi, $year, $month);

        // Karena sisa kuota cuti 0, alpa ke-1 langsung memotong gaji
        $this->assertSame(1, $report['total_alpa_days']);
        $this->assertSame('60000.00', $report['total_alpa_deduction']);
    }

    public function test_each_employee_has_different_deduction_rate_and_hrd_can_update_it(): void
    {
        /** @var User $hrd */
        $hrd = User::query()->where('username', 'hrd')->firstOrFail();
        /** @var User $empA */
        $empA = User::query()->where('username', 'teknisi1')->firstOrFail();
        /** @var User $empB */
        $empB = User::query()->where('username', 'helpdesk')->firstOrFail();

        // HRD memperbarui tarif potongan alpa untuk empA menjadi 80.000
        $responseA = $this->actingAs($hrd)->post(route('attendance.deductions.update-employee-rate'), [
            'user_id' => $empA->id,
            'potongan_alpa' => 80000,
        ]);
        $responseA->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $empA->id, 'potongan_alpa' => '80000.00']);

        // HRD memperbarui tarif potongan alpa untuk empB menjadi 120.000
        $responseB = $this->actingAs($hrd)->post(route('attendance.deductions.update-employee-rate'), [
            'user_id' => $empB->id,
            'potongan_alpa' => 120000,
        ]);
        $responseB->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $empB->id, 'potongan_alpa' => '120000.00']);

        // Buat alpa ke-4 untuk masing-masing karyawan
        $shiftPagi = Shift::query()->where('code', 'P')->firstOrFail();
        $dates = ['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05'];

        foreach ([$empA, $empB] as $emp) {
            foreach ($dates as $d) {
                MonthlySchedule::query()->updateOrCreate(
                    ['user_id' => $emp->id, 'date' => $d],
                    ['shift_id' => $shiftPagi->id, 'office_location_id' => 1]
                );
                Attendance::query()->updateOrCreate(
                    ['user_id' => $emp->id, 'date' => $d],
                    [
                        'shift_id' => $shiftPagi->id,
                        'status' => 'absent',
                        'source' => 'manual',
                    ]
                );
            }
        }

        $deductionService = app(DeductionService::class);
        $reportA = $deductionService->calculateUserMonthlyReport($empA->fresh(), 2026, 11);
        $reportB = $deductionService->calculateUserMonthlyReport($empB->fresh(), 2026, 11);

        // Keduanya memiliki 1 alpa berbayar (alpa ke-4), namun dengan tarif berbeda
        $this->assertSame('80000.00', $reportA['total_alpa_deduction']);
        $this->assertSame('120000.00', $reportB['total_alpa_deduction']);
    }
}
