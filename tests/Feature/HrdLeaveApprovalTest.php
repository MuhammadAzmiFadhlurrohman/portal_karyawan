<?php

namespace Tests\Feature;

use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrdLeaveApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_new_leave_request_directly_has_pending_hrd_status(): void
    {
        /** @var User $employee */
        $employee = User::factory()->create(['name' => 'Budi Samsudin', 'division' => 'Teknik']);
        $employee->assignRole('teknisi');

        $nextMonday = now('Asia/Jakarta')->next('Monday');

        $response = $this->actingAs($employee)->post(route('attendance.leaves.store'), [
            'leave_type' => 'sick',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'reason' => 'Sakit demam butuh istirahat',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Pengajuan cuti/izin berhasil dikirim dan menunggu persetujuan HRD.');

        $leave = Leave::query()->where('user_id', $employee->id)->firstOrFail();
        $this->assertSame('pending_hrd', $leave->status);
    }

    public function test_hrd_can_view_leave_approvals_page(): void
    {
        /** @var User $hrd */
        $hrd = User::factory()->create(['name' => 'Amelia HRD', 'division' => 'HRD']);
        $hrd->assignRole('hrd');

        /** @var User $employee */
        $employee = User::factory()->create(['name' => 'Ryan Septiadi', 'division' => 'Teknik']);
        $employee->assignRole('teknisi');

        $nextMonday = now('Asia/Jakarta')->next('Monday');

        Leave::query()->create([
            'user_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'working_days' => 1,
            'reason' => 'Keperluan keluarga mendesak',
            'status' => 'pending_hrd',
            'is_alpa_conversion' => false,
        ]);

        $response = $this->actingAs($hrd)->get(route('attendance.leaves.approvals'));
        $response->assertOk();
        $response->assertSee('Pengajuan Absen (Cuti &amp; Izin)', false);
        $response->assertSee('Ryan Septiadi');
        $response->assertSee('Keperluan keluarga mendesak');
    }

    public function test_non_hrd_cannot_access_leave_approvals_page(): void
    {
        /** @var User $employee */
        $employee = User::factory()->create(['name' => 'Teknisi Biasa', 'division' => 'Teknik']);
        $employee->assignRole('teknisi');

        $response = $this->actingAs($employee)->get(route('attendance.leaves.approvals'));
        $response->assertForbidden();
    }

    public function test_hrd_can_approve_leave(): void
    {
        /** @var User $hrd */
        $hrd = User::factory()->create(['name' => 'Amelia HRD', 'division' => 'HRD']);
        $hrd->assignRole('hrd');

        /** @var User $employee */
        $employee = User::factory()->create(['name' => 'Dede Rahman', 'division' => 'Teknik']);
        $employee->assignRole('teknisi');

        $nextMonday = now('Asia/Jakarta')->next('Monday');
        $year = (int) $nextMonday->format('Y');

        LeaveBalance::query()->create([
            'user_id' => $employee->id,
            'year' => $year,
            'total_quota' => 12,
            'used_days' => 0,
            'remaining_days' => 12,
        ]);

        $leave = Leave::query()->create([
            'user_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'working_days' => 1,
            'reason' => 'Cuti tahunan 1 hari',
            'status' => 'pending_hrd',
            'is_alpa_conversion' => false,
        ]);

        $response = $this->actingAs($hrd)->post(route('attendance.leaves.approve-hrd', $leave));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Pengajuan cuti/izin berhasil disetujui dan saldo cuti telah diperbarui.');

        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame($hrd->id, $leave->hrd_approved_by);

        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $employee->id,
            'year' => $year,
            'used_days' => 1,
            'remaining_days' => 11,
        ]);
    }

    public function test_hrd_can_reject_leave_with_reason(): void
    {
        /** @var User $hrd */
        $hrd = User::factory()->create(['name' => 'Amelia HRD', 'division' => 'HRD']);
        $hrd->assignRole('hrd');

        /** @var User $employee */
        $employee = User::factory()->create(['name' => 'Rafi Putra', 'division' => 'Teknik']);
        $employee->assignRole('teknisi');

        $nextMonday = now('Asia/Jakarta')->next('Monday');

        $leave = Leave::query()->create([
            'user_id' => $employee->id,
            'leave_type' => 'sick',
            'start_date' => $nextMonday->toDateString(),
            'end_date' => $nextMonday->toDateString(),
            'working_days' => 1,
            'reason' => 'Sakit tidak enak badan',
            'status' => 'pending_hrd',
            'is_alpa_conversion' => false,
        ]);

        $response = $this->actingAs($hrd)->post(route('attendance.leaves.reject', $leave), [
            'rejected_reason' => 'Surat dokter tidak dilampirkan atau tidak valid',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Pengajuan cuti/izin berhasil ditolak.');

        $leave->refresh();
        $this->assertSame('rejected', $leave->status);
        $this->assertSame($hrd->id, $leave->rejected_by);
        $this->assertSame('Surat dokter tidak dilampirkan atau tidak valid', $leave->rejected_reason);
    }
}
