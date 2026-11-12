<?php

namespace Tests\Feature;

use App\Models\MonthlySchedule;
use App\Models\NationalHoliday;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected OfficeLocation $office;
    protected Shift $shiftPagi;
    protected Shift $shiftLibur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('username', 'admin')->firstOrFail();
        $this->office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $this->shiftPagi = Shift::where('code', 'P')->firstOrFail();
        $this->shiftLibur = Shift::where('code', 'L')->firstOrFail();
    }

    public function test_screen_1_renders_monthly_schedule_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.schedules'));

        $response->assertOk();
        $response->assertSee('Jadwal Kerja Karyawan');
        $response->assertSee('Generate');
        $response->assertSee('Cari bulan atau tahun...');
    }

    public function test_screen_2_renders_generate_schedule_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.schedules', ['view' => 'generate']));

        $response->assertOk();
        $response->assertSee('Generate Jadwal Bulanan');
        $response->assertSee('Semua Karyawan');
        $response->assertSee('Per Karyawan');
        $response->assertSee('Periode');
        $response->assertSee('Shift Default');
        $response->assertSee('Generate Semua');
    }

    public function test_admin_can_generate_monthly_schedule_and_redirects_to_screen_3(): void
    {
        $response = $this->actingAs($this->admin)->post(route('attendance.schedules.generate'), [
            'year' => 2026,
            'month' => 11,
            'scope' => 'all',
            'default_shift_code' => 'P',
            'office_location_id' => $this->office->id,
        ]);

        $response->assertRedirect(route('attendance.schedules', [
            'view' => 'month',
            'year' => 2026,
            'month' => 11,
        ]));

        $this->assertTrue(
            MonthlySchedule::query()->whereYear('date', 2026)->whereMonth('date', 11)->exists()
        );
    }

    public function test_screen_3_renders_monthly_detail_and_employee_table(): void
    {
        // First generate
        $this->actingAs($this->admin)->post(route('attendance.schedules.generate'), [
            'year' => 2026,
            'month' => 11,
            'scope' => 'all',
            'default_shift_code' => 'P',
            'office_location_id' => $this->office->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('attendance.schedules', [
            'view' => 'month',
            'year' => 2026,
            'month' => 11,
        ]));

        $response->assertOk();
        $response->assertSee('Roster Shift: November 2026');
        $response->assertSee('Hari Libur Nasional');
        $response->assertSee('Data Karyawan');
        $response->assertSee('Upload');
        $response->assertSee('Export');
        $response->assertSee('Print Semua');
        $response->assertSee('Print Divisi');
    }

    public function test_admin_can_add_national_holiday_and_updates_schedules(): void
    {
        // Generate workdays for 2026-11
        $user = User::where('username', 'teknisi1')->firstOrFail();
        MonthlySchedule::query()->updateOrCreate(
            ['user_id' => $user->id, 'date' => '2026-11-20'],
            ['shift_id' => $this->shiftPagi->id, 'office_location_id' => $this->office->id]
        );

        $response = $this->actingAs($this->admin)->post(route('attendance.schedules.holidays.store'), [
            'date' => '2026-11-20',
            'name' => 'Hari Libur Uji Coba',
            'year' => 2026,
            'month' => 11,
        ]);

        $response->assertSessionHas('status');

        $this->assertTrue(
            NationalHoliday::query()->whereDate('date', '2026-11-20')->exists()
        );

        $sched = MonthlySchedule::query()->where('user_id', $user->id)->whereDate('date', '2026-11-20')->firstOrFail();
        $this->assertSame($this->shiftLibur->id, $sched->shift_id);
    }

    public function test_screen_5_renders_employee_day_by_day_calendar(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('attendance.schedules', [
            'view' => 'user',
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 11,
        ]));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee('November 2026');
        $response->assertSee('Keterangan Shift:');
        $response->assertSee('Jam Kerja');
    }

    public function test_admin_can_update_schedule_cell_via_ajax(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();

        $response = $this->actingAs($this->admin)->postJson(route('attendance.schedules.cell'), [
            'user_id' => $user->id,
            'date' => '2026-11-05',
            'shift_id' => $this->shiftPagi->id,
            'reason' => 'Perubahan uji coba shift',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'shift' => [
                'code' => 'P',
            ],
        ]);

        $this->assertTrue(
            MonthlySchedule::query()
                ->where('user_id', $user->id)
                ->whereDate('date', '2026-11-05')
                ->where('shift_id', $this->shiftPagi->id)
                ->exists()
        );
    }

    public function test_admin_can_delete_month_schedules(): void
    {
        // Seed schedule
        MonthlySchedule::query()->create([
            'user_id' => $this->admin->id,
            'date' => '2026-11-01',
            'shift_id' => $this->shiftPagi->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('attendance.schedules.delete-month'), [
            'year' => 2026,
            'month' => 11,
        ]);

        $response->assertRedirect(route('attendance.schedules'));
        $this->assertDatabaseMissing('monthly_schedules', [
            'user_id' => $this->admin->id,
            'date' => '2026-11-01',
        ]);
    }

    public function test_admin_can_download_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.schedules.template', [
            'year' => 2026,
            'month' => 11,
            'division' => 'all',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
