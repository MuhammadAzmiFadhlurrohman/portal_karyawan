<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Lembur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OvertimeApprovalHrdFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'hrd']);
        Role::firstOrCreate(['name' => 'finance']);
        Role::firstOrCreate(['name' => 'atasan']);
        Role::firstOrCreate(['name' => 'karyawan']);
    }

    public function test_finance_and_hrd_can_view_overtime_index_page(): void
    {
        $finance = User::factory()->create(['division' => 'Finance']);
        $finance->assignRole('finance');

        $hrd = User::factory()->create(['division' => 'HRD']);
        $hrd->assignRole('hrd');

        $karyawan = User::factory()->create(['division' => 'Teknik']);
        $karyawan->assignRole('karyawan');

        // Create overtime with approved_atasan status
        $lembur = Lembur::create([
            'nomor_lembur' => 'LBR-TEST-001',
            'user_id' => $karyawan->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_mulai' => Carbon::now()->setTime(17, 0),
            'jam_selesai' => Carbon::now()->setTime(20, 0),
            'durasi_menit' => 180,
            'alasan' => 'Pekerjaan mendesak',
            'status' => 'approved_atasan',
        ]);

        $responseFinance = $this->actingAs($finance)->get(route('gomsn.overtime.index'));
        $responseFinance->assertStatus(200);
        $responseFinance->assertSee($karyawan->name);

        $responseHrd = $this->actingAs($hrd)->get(route('gomsn.overtime.index'));
        $responseHrd->assertStatus(200);
        $responseHrd->assertSee($karyawan->name);
    }

    public function test_hrd_can_create_overtime_for_themselves(): void
    {
        $hrd = User::factory()->create(['division' => 'HRD']);
        $hrd->assignRole('hrd');

        // HRD opens create form
        $response = $this->actingAs($hrd)->get(route('gomsn.overtime.create'));
        $response->assertStatus(200);

        // Record attendance for HRD on today's date
        $today = Carbon::now()->toDateString();
        Attendance::create([
            'user_id' => $hrd->id,
            'date' => $today,
            'status' => 'present',
            'clock_in_at' => Carbon::now()->setTime(8, 0),
            'clock_out_at' => Carbon::now()->setTime(17, 0),
        ]);

        // Submit overtime
        $responseSubmit = $this->actingAs($hrd)->post(route('gomsn.overtime.store'), [
            'tanggal' => $today,
            'jam_mulai' => '17:30',
            'jam_selesai' => '20:30',
            'alasan' => 'Rekap data karyawan bulanan',
            'jenis_lembur' => 'Lembur Hari Kerja',
        ]);

        $responseSubmit->assertRedirect(route('gomsn.overtime.index'));
        $this->assertDatabaseHas('lembur', [
            'user_id' => $hrd->id,
            'alasan' => 'Rekap data karyawan bulanan',
        ]);
    }

    public function test_finance_can_confirm_payment_for_approved_overtime(): void
    {
        $finance = User::factory()->create(['division' => 'Finance']);
        $finance->assignRole('finance');

        $karyawan = User::factory()->create(['division' => 'Teknik']);
        $karyawan->assignRole('karyawan');

        $lembur = Lembur::create([
            'nomor_lembur' => 'LBR-PAY-001',
            'user_id' => $karyawan->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_mulai' => Carbon::now()->setTime(17, 0),
            'jam_selesai' => Carbon::now()->setTime(21, 0),
            'durasi_menit' => 240,
            'alasan' => 'Penanganan server',
            'status' => 'approved_atasan',
        ]);

        $response = $this->actingAs($finance)->post(route('gomsn.overtime.pay', $lembur->id), [
            'payment_reference' => 'TRF-BCA-987654',
        ]);

        $response->assertRedirect(route('gomsn.overtime.index'));
        $lembur->refresh();
        $this->assertEquals('paid', $lembur->status);
        $this->assertEquals('TRF-BCA-987654', $lembur->payment_reference);
        $this->assertEquals($finance->id, $lembur->paid_by);
    }
}

