<?php

namespace Tests\Feature;

use App\Models\MonthlySchedule;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\ShiftSwap;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalScheduleAndShiftSwapTest extends TestCase
{
    use RefreshDatabase;

    protected User $karyawan;
    protected User $rekan;
    protected User $admin;
    protected Shift $shiftPagi;
    protected Shift $shiftLibur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@msn.co.id')->firstOrFail();
        $this->karyawan = User::where('email', 'agus.teknisi@msn.co.id')->firstOrFail();
        $this->rekan = User::where('email', 'dedi.teknis@msn.co.id')->firstOrFail();

        $this->shiftPagi = Shift::where('code', 'P')->firstOrFail();
        $this->shiftLibur = Shift::where('code', 'L')->firstOrFail();
    }

    public function test_karyawan_sees_personal_schedule_view(): void
    {
        $response = $this->actingAs($this->karyawan)->get(route('attendance.schedules', [
            'year' => 2026,
            'month' => 11,
        ]));

        $response->assertOk();
        $response->assertSee('Jadwal Kerja');
        $response->assertSee(strtoupper($this->karyawan->name));
        $response->assertSee('Tampilkan');
        $response->assertSee('Permintaan');
        $response->assertSee('TANGGAL &amp; SHIFT', false);
        $response->assertSee('JAM KERJA');
        $response->assertSee('AKSI');
        $response->assertSee('Pilih Aksi');
        $response->assertSee('Tukar Jadwal');
        $response->assertSee('Backup Jadwal');
        $response->assertDontSee('Kalender & Custom Jadwal');
    }

    public function test_admin_can_view_personal_schedule_via_my_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.schedules', [
            'view' => 'my',
            'year' => 2026,
            'month' => 11,
        ]));

        $response->assertOk();
        $response->assertSee('Jadwal Kerja');
        $response->assertSee(strtoupper($this->admin->name));
        $response->assertSee('Jadwal Saya');
        $response->assertSee('Kelola Periode Bulan');
    }

    public function test_hrd_only_sees_own_schedule_and_cannot_manage_schedule(): void
    {
        $hrd = User::where('email', 'hrd@msn.co.id')->firstOrFail();

        // 1. HRD opening schedules index defaults to personal view
        $response = $this->actingAs($hrd)->get(route('attendance.schedules'));
        $response->assertOk();
        $response->assertSee('Jadwal Kerja Saya');
        $response->assertSee(strtoupper($hrd->name));
        $response->assertDontSee('Kalender & Custom Jadwal');
        $response->assertDontSee('Kelola Periode Bulan');

        // 2. HRD attempting to access admin views is forced to personal view
        $responseUserView = $this->actingAs($hrd)->get(route('attendance.schedules', ['view' => 'user', 'user_id' => $this->karyawan->id]));
        $responseUserView->assertOk();
        $responseUserView->assertSee('Jadwal Kerja Saya');
        $responseUserView->assertDontSee('Custom Jadwal Kerja');

        $responseGenerateView = $this->actingAs($hrd)->get(route('attendance.schedules', ['view' => 'generate']));
        $responseGenerateView->assertOk();
        $responseGenerateView->assertSee('Jadwal Kerja Saya');
        $responseGenerateView->assertDontSee('Generate Jadwal Bulanan');

        $responseMonthView = $this->actingAs($hrd)->get(route('attendance.schedules', ['view' => 'month', 'year' => 2026, 'month' => 11]));
        $responseMonthView->assertOk();
        $responseMonthView->assertSee('Jadwal Kerja Saya');

        // 3. HRD mutation actions are blocked with 403 Forbidden
        $this->actingAs($hrd)->post(route('attendance.schedules.generate'), [
            'year' => 2026,
            'month' => 11,
            'scope' => 'all',
            'default_shift_code' => 'P',
        ])->assertForbidden();

        $this->actingAs($hrd)->postJson(route('attendance.schedules.cell'), [
            'user_id' => $this->karyawan->id,
            'date' => '2026-11-05',
            'shift_id' => $this->shiftPagi->id,
        ])->assertForbidden();

        $this->actingAs($hrd)->post(route('attendance.schedules.delete-month'), [
            'year' => 2026,
            'month' => 11,
        ])->assertForbidden();

        $this->actingAs($hrd)->get(route('attendance.schedules.template', [
            'year' => 2026,
            'month' => 11,
        ]))->assertForbidden();
    }

    public function test_peer_shift_ajax_endpoint_returns_json(): void
    {
        $date = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();

        MonthlySchedule::updateOrCreate([
            'user_id' => $this->rekan->id,
            'date' => $date,
        ], [
            'shift_id' => $this->shiftPagi->id,
            'is_locked' => false,
        ]);

        $response = $this->actingAs($this->karyawan)->get(route('attendance.schedules.peer-shift', [
            'user_id' => $this->rekan->id,
            'date' => $date,
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['shift', 'available_count']);
        $this->assertStringContainsString('Pagi', $response->json('shift'));
    }

    public function test_user_can_submit_tukar_shift_request(): void
    {
        $d1 = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();
        $d2 = Carbon::now('Asia/Jakarta')->addDays(3)->toDateString();

        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d1], ['shift_id' => $this->shiftPagi->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d1], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->karyawan->id, 'date' => $d2], ['shift_id' => $this->shiftLibur->id]);
        MonthlySchedule::updateOrCreate(['user_id' => $this->rekan->id, 'date' => $d2], ['shift_id' => $this->shiftPagi->id]);

        $response = $this->actingAs($this->karyawan)->post(route('attendance.swaps.store'), [
            'target_user_id' => $this->rekan->id,
            'requester_date' => $d1,
            'target_date' => $d2,
            'is_backup' => 0,
            'reason' => 'Keperluan keluarga mendesak dan butuh penyesuaian jadwal.',
        ]);

        $response->assertSessionHas('status');
        $this->assertTrue(
            ShiftSwap::query()
                ->where('requester_id', $this->karyawan->id)
                ->where('target_user_id', $this->rekan->id)
                ->where('is_backup', false)
                ->exists()
        );
    }
}
