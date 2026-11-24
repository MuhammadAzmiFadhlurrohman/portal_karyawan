<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\NotificationLog;
use App\Models\User;
use App\Modules\Attendance\Services\DeductionService;
use App\Modules\Attendance\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveAndCorrectionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'hrd']);
        Role::firstOrCreate(['name' => 'atasan']);
        Role::firstOrCreate(['name' => 'karyawan']);
    }

    public function test_leave_request_and_approval_workflow_dispatches_notifications(): void
    {
        $employee = User::factory()->create(['name' => 'Budi Staff', 'division' => 'NOC']);
        $employee->assignRole('karyawan');

        $hrd = User::factory()->create(['name' => 'Siti HRD', 'division' => 'HRD']);
        $hrd->assignRole('hrd');

        $leaveService = app(LeaveService::class);

        // 1. Employee submits leave -> HRD notified
        $startDate = Carbon::now('Asia/Jakarta')->addDays(3);
        $endDate = Carbon::now('Asia/Jakarta')->addDays(4);

        $leave = $leaveService->requestLeave($employee, [
            'leave_type' => 'annual',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'reason' => 'Keperluan keluarga di luar kota',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $hrd->id,
            'event_type' => 'leave_requested',
        ]);

        // 2. HRD approves leave -> Employee notified
        $leaveService->approveByHrd($leave, $hrd);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $employee->id,
            'event_type' => 'leave_approved',
        ]);
    }

    public function test_leave_rejection_notifies_employee(): void
    {
        $employee = User::factory()->create(['name' => 'Budi Staff', 'division' => 'NOC']);
        $employee->assignRole('karyawan');

        $hrd = User::factory()->create(['name' => 'Siti HRD', 'division' => 'HRD']);
        $hrd->assignRole('hrd');

        $leaveService = app(LeaveService::class);

        $startDate = Carbon::now('Asia/Jakarta')->addDays(5);
        $endDate = Carbon::now('Asia/Jakarta')->addDays(6);

        $leave = $leaveService->requestLeave($employee, [
            'leave_type' => 'annual',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'reason' => 'Cuti mendadak',
        ]);

        $leaveService->rejectByHrd($leave, $hrd, 'Kuota tim operasional penuh');

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $employee->id,
            'event_type' => 'leave_rejected',
        ]);
    }

    public function test_correction_request_and_approval_workflow_dispatches_notifications(): void
    {
        $employee = User::factory()->create(['name' => 'Rahmat Teknisi', 'division' => 'Teknik']);
        $employee->assignRole('karyawan');

        $supervisor = User::factory()->create(['name' => 'Pak Joko Supervisor', 'division' => 'Teknik']);
        $supervisor->assignRole('atasan');

        $testDate = Carbon::now('Asia/Jakarta')->subDays(2)->toDateString();

        Attendance::create([
            'user_id' => $employee->id,
            'date' => $testDate,
            'status' => 'late',
            'attendance_type' => 'office',
            'clock_in_at' => "{$testDate} 08:30:00",
            'clock_out_at' => "{$testDate} 17:00:00",
            'late_minutes' => 15,
        ]);

        $deductionService = app(DeductionService::class);

        // 1. Employee submits correction -> Supervisor notified
        $correction = $deductionService->submitCorrection($employee, [
            'date' => $testDate,
            'requested_status' => 'present',
            'requested_attendance_type' => 'office',
            'requested_clock_in_time' => '08:05:00',
            'requested_clock_out_time' => '17:00:00',
            'reason' => 'Kendala fingerprint di lokasi',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $supervisor->id,
            'event_type' => 'correction_requested',
        ]);

        // 2. Supervisor approves correction -> Employee notified
        $deductionService->approveCorrectionBySupervisor($correction, $supervisor);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $employee->id,
            'event_type' => 'correction_approved',
        ]);
    }

    public function test_correction_rejection_notifies_employee(): void
    {
        $employee = User::factory()->create(['name' => 'Rahmat Teknisi', 'division' => 'Teknik']);
        $employee->assignRole('karyawan');

        $supervisor = User::factory()->create(['name' => 'Pak Joko Supervisor', 'division' => 'Teknik']);
        $supervisor->assignRole('atasan');

        $testDate = Carbon::now('Asia/Jakarta')->subDays(2)->toDateString();

        $deductionService = app(DeductionService::class);

        $correction = $deductionService->submitCorrection($employee, [
            'date' => $testDate,
            'requested_status' => 'present',
            'requested_attendance_type' => 'office',
            'requested_clock_in_time' => '08:00:00',
            'requested_clock_out_time' => '17:00:00',
            'reason' => 'Lupa absen masuk',
        ]);

        $deductionService->rejectCorrection($correction, $supervisor, 'Bukti log aktivitas tidak valid');

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $employee->id,
            'event_type' => 'correction_rejected',
        ]);
    }
}
