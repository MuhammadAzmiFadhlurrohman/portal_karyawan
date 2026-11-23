<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\NotificationLog;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use App\Modules\Attendance\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'karyawan']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'hrd']);
    }

    public function test_shift_reminder_notifies_one_hour_before_shift_start_and_deduplicates(): void
    {
        $office = OfficeLocation::create([
            'code' => 'TEST-HQ',
            'name' => 'Test Headquarters',
            'lat' => -6.914744,
            'lng' => 107.609810,
            'radius_m' => 100,
            'is_active' => true,
        ]);

        $shiftPagi = Shift::create([
            'code' => 'P',
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_day_off' => false,
            'is_active' => true,
        ]);

        $shiftSiang = Shift::create([
            'code' => 'S',
            'name' => 'Shift Siang',
            'start_time' => '13:00:00',
            'end_time' => '21:00:00',
            'is_day_off' => false,
            'is_active' => true,
        ]);

        $shiftLibur = Shift::create([
            'code' => 'L',
            'name' => 'Libur',
            'start_time' => null,
            'end_time' => null,
            'is_day_off' => true,
            'is_active' => true,
        ]);

        // User 1: Shift starts at 08:00 (1 hour before reference time 07:00) -> SHOULD be notified
        $user1 = User::factory()->create(['name' => 'Budi Santoso', 'is_active' => true]);
        MonthlySchedule::create([
            'user_id' => $user1->id,
            'date' => '2026-11-24',
            'shift_id' => $shiftPagi->id,
            'office_location_id' => $office->id,
        ]);

        // User 2: Shift starts at 13:00 (6 hours away from 07:00) -> SHOULD NOT be notified
        $user2 = User::factory()->create(['name' => 'Siti Aminah', 'is_active' => true]);
        MonthlySchedule::create([
            'user_id' => $user2->id,
            'date' => '2026-11-24',
            'shift_id' => $shiftSiang->id,
            'office_location_id' => $office->id,
        ]);

        // User 3: Shift starts at 08:00 but has already clocked in -> SHOULD NOT be notified
        $user3 = User::factory()->create(['name' => 'Ahmad Dani', 'is_active' => true]);
        MonthlySchedule::create([
            'user_id' => $user3->id,
            'date' => '2026-11-24',
            'shift_id' => $shiftPagi->id,
            'office_location_id' => $office->id,
        ]);
        Attendance::create([
            'user_id' => $user3->id,
            'date' => '2026-11-24',
            'shift_id' => $shiftPagi->id,
            'clock_in_at' => Carbon::parse('2026-11-24 06:45:00'),
            'status' => 'present',
            'attendance_type' => 'office',
        ]);

        // User 4: Shift starts at 08:00 but on approved leave -> SHOULD NOT be notified
        $user4 = User::factory()->create(['name' => 'Dewi Sartika', 'is_active' => true]);
        MonthlySchedule::create([
            'user_id' => $user4->id,
            'date' => '2026-11-24',
            'shift_id' => $shiftPagi->id,
            'office_location_id' => $office->id,
        ]);
        Leave::create([
            'user_id' => $user4->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-24',
            'end_date' => '2026-11-24',
            'status' => 'approved',
            'is_alpa_conversion' => false,
            'reason' => 'Cuti tahunan',
        ]);

        // User 5: Scheduled on Day-Off ('L') -> SHOULD NOT be notified
        $user5 = User::factory()->create(['name' => 'Rian Hidayat', 'is_active' => true]);
        MonthlySchedule::create([
            'user_id' => $user5->id,
            'date' => '2026-11-24',
            'shift_id' => $shiftLibur->id,
            'office_location_id' => $office->id,
        ]);

        // Reference time: exactly 07:00:00 (60 minutes before 08:00:00)
        Artisan::call('attendance:shift-reminders', ['--now' => '2026-11-24 07:00:00']);

        // Assert User 1 received the notification log
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $user1->id,
            'event_type' => 'shift_reminder_clock_in',
            'status' => 'sent',
        ]);

        // Assert others did NOT receive it
        $this->assertDatabaseMissing('notification_logs', ['user_id' => $user2->id]);
        $this->assertDatabaseMissing('notification_logs', ['user_id' => $user3->id]);
        $this->assertDatabaseMissing('notification_logs', ['user_id' => $user4->id]);
        $this->assertDatabaseMissing('notification_logs', ['user_id' => $user5->id]);

        $this->assertSame(1, NotificationLog::count());

        // Test Deduplication: Running again at 07:05 (55 minutes before 08:00) should NOT duplicate
        Artisan::call('attendance:shift-reminders', ['--now' => '2026-11-24 07:05:00']);
        $this->assertSame(1, NotificationLog::count());
    }

    public function test_sync_reminders_route_accessible_by_admin(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('attendance.schedules.sync-reminders'));
        $response->assertSessionHas('status');
        $response->assertRedirect();
    }
}
