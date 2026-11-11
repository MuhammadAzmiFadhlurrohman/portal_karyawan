<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Attendance\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'hrd']);
        Role::firstOrCreate(['name' => 'karyawan']);
    }

    public function test_non_admin_cannot_access_attendance_report(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('karyawan');

        $response = $this->actingAs($employee)->get(route('attendance.reports'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_attendance_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $employee = User::factory()->create(['name' => 'Budi Santoso', 'employee_code' => 'KR1001']);

        $response = $this->actingAs($admin)->get(route('attendance.reports', ['date' => '2026-11-02']));
        $response->assertStatus(200);
        $response->assertSee('Laporan Absensi');
        $response->assertSee('Budi Santoso');
        $response->assertSee('KR1001');
    }

    public function test_admin_can_quick_update_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $employee = User::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('attendance.reports.quick-status'), [
            'user_id' => $employee->id,
            'date' => '2026-11-02',
            'status' => 'present',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'status' => 'present',
        ]);
    }

    public function test_admin_can_update_attendance_record_with_times(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $employee = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('attendance.reports.update'), [
            'user_id' => $employee->id,
            'date' => '2026-11-02',
            'status' => 'present',
            'clock_in' => '08:15',
            'clock_out' => '17:00',
            'notes' => 'Koreksi kehadiran oleh HRD',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'status' => 'present',
            'notes' => 'Koreksi kehadiran oleh HRD',
        ]);
    }

    public function test_admin_can_export_csv(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $employee = User::factory()->create(['name' => 'Siti Nurhaliza', 'employee_code' => 'KR2002']);

        $response = $this->actingAs($admin)->get(route('attendance.reports.export', ['date' => '2026-11-02']));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}

