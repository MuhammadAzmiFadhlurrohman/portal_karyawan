<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::create([
            'key' => 'denda_terlambat_per_menit',
            'group' => 'attendance',
            'label' => 'Tarif Denda',
            'value' => '250',
            'type' => 'integer',
        ]);
    }

    public function test_authenticated_user_can_view_attendance_history(): void
    {
        $user = User::create([
            'name' => 'Budi Pratama',
            'username' => 'budi_p',
            'email' => 'budi@gomsn.net',
            'employee_code' => 'KR20268',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.history'));

        $response->assertOk();
        $response->assertSee('Riwayat Absen');
        $response->assertSee('TOTAL HADIR');
    }

    public function test_authenticated_user_can_view_keuangan_absen(): void
    {
        $user = User::create([
            'name' => 'Budi Pratama',
            'username' => 'budi_p',
            'email' => 'budi@gomsn.net',
            'employee_code' => 'KR20268',
            'phone' => '08123456789',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.deductions'));

        $response->assertOk();
        $response->assertSee('Keuangan Absen');
        $response->assertDontSee('Ajukan Koreksi Status Absensi');
    }

    public function test_supervisor_can_approve_attendance_correction_and_attendance_updates_directly(): void
    {
        $employee = User::create([
            'name' => 'Karyawan Test',
            'username' => 'karyawan_test',
            'email' => 'karyawan@gomsn.net',
            'employee_code' => 'KR001',
            'phone' => '08123456781',
            'division' => 'Teknik',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $supervisor = User::create([
            'name' => 'Atasan Teknik',
            'username' => 'atasan_teknik',
            'email' => 'atasan@gomsn.net',
            'employee_code' => 'AT001',
            'phone' => '08123456782',
            'division' => 'Teknik',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        \Spatie\Permission\Models\Role::findOrCreate('atasan');
        $supervisor->assignRole('atasan');

        // Initial late attendance
        $attendance = \App\Models\Attendance::create([
            'user_id' => $employee->id,
            'date' => '2026-11-09',
            'status' => 'late',
            'attendance_type' => 'office',
            'clock_in_at' => '2026-11-09 08:35:00',
            'clock_out_at' => '2026-11-09 17:00:00',
            'late_minutes' => 20,
            'work_duration_minutes' => 505,
        ]);

        // Employee submits correction
        $response = $this->actingAs($employee)->post(route('attendance.corrections.store'), [
            'date' => '2026-11-09',
            'requested_status' => 'present',
            'requested_attendance_type' => 'office',
            'requested_clock_in_time' => '08:10',
            'requested_clock_out_time' => '17:05',
            'reason' => 'Perbaikan data karena kendala absensi biometrik',
        ]);

        $response->assertSessionHas('status');

        $correction = \App\Models\AttendanceCorrection::where('user_id', $employee->id)->firstOrFail();
        $this->assertEquals('pending_supervisor', $correction->status);

        // Supervisor views approvals page
        $approvalView = $this->actingAs($supervisor)->get(route('attendance.corrections.approvals'));
        $approvalView->assertOk();
        $approvalView->assertSee('Karyawan Test');
        $approvalView->assertSee('Perbaikan data karena kendala absensi biometrik');

        // Supervisor approves correction
        $approveResponse = $this->actingAs($supervisor)->post(route('attendance.corrections.approve-supervisor', $correction));
        $approveResponse->assertRedirect();

        // Verify Attendance record updated directly!
        $attendance->refresh();
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('2026-11-09 08:10:00', $attendance->clock_in_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-11-09 17:05:00', $attendance->clock_out_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'));
        $this->assertEquals(0, $attendance->late_minutes);

        $correction->refresh();
        $this->assertEquals('approved', $correction->status);
    }
}
