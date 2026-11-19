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

    public function test_hrd_cannot_approve_employee_overtime_and_only_views_approved_overtime(): void
    {
        $hrd = User::factory()->create(['division' => 'HRD']);
        $hrd->assignRole('hrd');

        $karyawan = User::factory()->create(['division' => 'Teknik']);
        $karyawan->assignRole('karyawan');

        // Lembur status pending (belum disetujui atasan)
        $lemburPending = Lembur::create([
            'nomor_lembur' => 'LBR-PENDING-001',
            'user_id' => $karyawan->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_mulai' => Carbon::now()->setTime(17, 0),
            'jam_selesai' => Carbon::now()->setTime(19, 0),
            'durasi_menit' => 120,
            'alasan' => 'Tugas pending',
            'status' => 'pending',
        ]);

        // Lembur status approved_atasan
        $lemburApproved = Lembur::create([
            'nomor_lembur' => 'LBR-APPROVED-002',
            'user_id' => $karyawan->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_mulai' => Carbon::now()->setTime(17, 0),
            'jam_selesai' => Carbon::now()->setTime(20, 0),
            'durasi_menit' => 180,
            'alasan' => 'Tugas approved atasan',
            'status' => 'approved_atasan',
        ]);

        // HRD mengakses index: tidak boleh melihat lembur pending, hanya lembur approved
        $responseHrd = $this->actingAs($hrd)->get(route('gomsn.overtime.index'));
        $responseHrd->assertStatus(200);
        $responseHrd->assertDontSee('Terdapat 1 pengajuan lembur yang perlu disetujui');
        $responseHrd->assertDontSee('Setujui (1)');
        $responseHrd->assertDontSee('blm diproses');

        // HRD mencoba approve lembur pending: harus 403 Forbidden
        $responseApprove = $this->actingAs($hrd)->post(route('gomsn.overtime.approve', $lemburPending->id));
        $responseApprove->assertStatus(403);
    }

    public function test_hrd_can_toggle_payment_status_for_employee(): void
    {
        $hrd = User::factory()->create(['division' => 'HRD']);
        $hrd->assignRole('hrd');

        $karyawan = User::factory()->create(['division' => 'Teknik']);
        $karyawan->assignRole('karyawan');

        $currentDate = Carbon::now();
        $lembur = Lembur::create([
            'nomor_lembur' => 'LBR-TOGGLE-001',
            'user_id' => $karyawan->id,
            'tanggal' => $currentDate->toDateString(),
            'jam_mulai' => $currentDate->copy()->setTime(17, 0),
            'jam_selesai' => $currentDate->copy()->setTime(20, 0),
            'durasi_menit' => 180,
            'alasan' => 'Tugas lembur selesai',
            'status' => 'approved_atasan',
        ]);

        // Toggle to PAID (ON)
        $responseOn = $this->actingAs($hrd)->post(route('gomsn.overtime.toggle-payment'), [
            'user_id' => $karyawan->id,
            'bulan' => $currentDate->month,
            'tahun' => $currentDate->year,
            'target_status' => 'paid',
        ]);
        $responseOn->assertRedirect();
        $lembur->refresh();
        $this->assertEquals('paid', $lembur->status);
        $this->assertNotNull($lembur->paid_at);

        // Toggle back to UNPAID (OFF)
        $responseOff = $this->actingAs($hrd)->post(route('gomsn.overtime.toggle-payment'), [
            'user_id' => $karyawan->id,
            'bulan' => $currentDate->month,
            'tahun' => $currentDate->year,
            'target_status' => 'unpaid',
        ]);
        $responseOff->assertRedirect();
        $lembur->refresh();
        $this->assertEquals('approved_atasan', $lembur->status);
        $this->assertNull($lembur->paid_at);
    }
}

