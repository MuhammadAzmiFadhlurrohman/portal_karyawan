<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\FaceTemplate;
use App\Models\MonthlySchedule;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Services\ScheduleService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PhaseOneAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_ac01_clock_in_outside_office_radius_is_rejected_with_distance_and_radius(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);

        $this->enrollDefaultFace($user, $service);
        $token = $service->issueAttemptToken($user, base64_encode('FACE_ID:user_'.$user->id.'|SAMPLE'));

        try {
            // Soreang coordinates (~15.4 km away from Bandung HQ) with type 'office'
            $service->clockIn($user, [
                'office_location_id' => $office->id,
                'attendance_type' => 'office',
                'lat' => -7.0422150,
                'lng' => 107.5409810,
                'gps_accuracy' => 10.0,
                'attempt_token' => $token->token,
                'primary_image' => base64_encode('FACE_ID:user_'.$user->id.'|PRIMARY'),
                'challenge_image' => base64_encode('FACE_ID:user_'.$user->id.'|CHALLENGE'),
            ]);
            $this->fail('Expected ValidationException for out-of-radius office clock-in (AC-01).');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('location', $errors);
            $this->assertStringContainsString('batas radius kantor', $errors['location'][0]);
            $this->assertStringContainsString('AC-01', $errors['location'][0]);
        }
    }

    public function test_ac02_spoof_photo_rejected_by_liveness_and_three_failures_unlock_manual_fallback(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);

        $this->enrollDefaultFace($user, $service);
        $token = $service->issueAttemptToken($user, base64_encode('FACE_ID:user_'.$user->id.'|SAMPLE'));

        // 1. Spoof screen attack is rejected and logged in audit_logs (AC-02)
        try {
            $service->clockIn($user, [
                'office_location_id' => $office->id,
                'attendance_type' => 'office',
                'lat' => $office->lat,
                'lng' => $office->lng,
                'gps_accuracy' => 5.0,
                'attempt_token' => $token->token,
                'primary_image' => base64_encode('SPOOF_SCREEN_ATTACK_PAYLOAD'),
                'challenge_image' => base64_encode('FACE_ID:user_'.$user->id.'|CHALLENGE'),
            ]);
            $this->fail('Expected spoof screen photo to be rejected (AC-02).');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('image', $e->errors());
        }

        $this->assertTrue(
            AuditLog::query()->where('action', 'attendance.spoof_attempt_blocked')->exists(),
            'Spoof attempt must be recorded in audit_logs (AC-02).'
        );

        // 2. Three consecutive 1:1 face mismatches unlock manual review fallback (§5.1)
        for ($i = 1; $i <= 3; $i++) {
            $t = $service->issueAttemptToken($user, base64_encode('FACE_ID:user_'.$user->id.'|SAMPLE_'.$i));
            try {
                $service->clockIn($user, [
                    'office_location_id' => $office->id,
                    'attendance_type' => 'office',
                    'lat' => $office->lat,
                    'lng' => $office->lng,
                    'gps_accuracy' => 5.0,
                    'attempt_token' => $t->token,
                    'primary_image' => base64_encode('FACE_ID:impostor_999|POSE_'.$i),
                    'challenge_image' => base64_encode('FACE_ID:impostor_999|CHALLENGE_'.$i),
                ]);
            } catch (ValidationException) {
                // Expected failure
            }
        }

        $this->assertSame(3, $service->getConsecutiveFailures($user->id));

        // Now manual fallback succeeds and sets status = pending_review
        $manualAttendance = $service->clockIn($user, [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 8.0,
            'use_manual_fallback' => true,
            'notes' => 'Kamera depan berembun setelah hujan di lapangan',
        ]);

        $this->assertSame('pending_review', $manualAttendance->status);
        $this->assertSame('manual', $manualAttendance->source);
    }

    public function test_ac03_offline_sync_sent_twice_is_idempotent(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);

        $this->enrollDefaultFace($user, $service);

        $payload = [
            'client_uuid' => 'offline-uuid-20261104-0001',
            'captured_at' => now()->subMinutes(20)->toIso8601String(),
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 9.0,
            'primary_image' => base64_encode('FACE_ID:user_'.$user->id.'|OFFLINE_1'),
            'challenge_image' => base64_encode('FACE_ID:user_'.$user->id.'|OFFLINE_2'),
        ];

        $first = $service->syncOfflineAttendance($user, $payload);
        $second = $service->syncOfflineAttendance($user, $payload);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Attendance::query()->where('user_id', $user->id)->count());
    }

    public function test_ac12_reset_face_deletes_template_and_photos_and_logs_audit(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);

        $template = $this->enrollDefaultFace($user, $service);
        $refPath = $template->reference_photo_path;
        $this->assertNotNull($refPath);
        Storage::disk('private')->assertExists($refPath);

        $token = $service->issueAttemptToken($user, base64_encode('FACE_ID:user_'.$user->id.'|SAMPLE'));
        $att = $service->clockIn($user, [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 6.0,
            'attempt_token' => $token->token,
            'primary_image' => base64_encode('FACE_ID:user_'.$user->id.'|IN_1'),
            'challenge_image' => base64_encode('FACE_ID:user_'.$user->id.'|IN_2'),
        ]);

        $photoInPath = (string) $att->photo_in_primary_path;
        Storage::disk('private')->assertExists($photoInPath);

        // Execute Reset Wajah (AC-12)
        $service->resetUserFace($user, 'Permintaan hapus data biometrik oleh karyawan (AC-12)', $user->id);

        $this->assertDatabaseMissing('face_templates', ['user_id' => $user->id]);
        Storage::disk('private')->assertMissing($refPath);
        Storage::disk('private')->assertMissing($photoInPath);
        $this->assertTrue(AuditLog::query()->where('action', 'biometric.face_reset')->exists());
    }

    public function test_monthly_schedule_preserves_piket_detects_11h_conflict_and_sends_reminders(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $shiftPiket = Shift::where('code', 'PK')->firstOrFail();
        $shiftSiang = Shift::where('code', 'S')->firstOrFail();  // ends 21:00
        $shiftSubuh = Shift::where('code', 'SB')->firstOrFail(); // starts 06:00 (9h rest < 11h!)

        // Place a PK piket schedule on 2026-11-10
        MonthlySchedule::query()->updateOrCreate(
            ['user_id' => $user->id, 'date' => '2026-11-10'],
            ['shift_id' => $shiftPiket->id, 'office_location_id' => $office->id]
        );

        $scheduleService = app(ScheduleService::class);
        $summary = $scheduleService->commitMonthlyGeneration(2026, 11, $user->id, 'Operasional Jaringan', $office->id, 'P');

        $this->assertGreaterThanOrEqual(1, $summary['preserved_piket_count']);
        $preserved = MonthlySchedule::query()->where('user_id', $user->id)->whereDate('date', '2026-11-10')->firstOrFail();
        $this->assertSame($shiftPiket->id, $preserved->shift_id);

        // Create a < 11h rest conflict: 2026-11-11 = Shift Siang (ends 21:00), 2026-11-12 = Shift Subuh (starts 06:00)
        MonthlySchedule::query()->where('user_id', $user->id)->whereDate('date', '2026-11-11')->update([
            'shift_id' => $shiftSiang->id,
            'office_location_id' => $office->id,
        ]);
        MonthlySchedule::query()->where('user_id', $user->id)->whereDate('date', '2026-11-12')->update([
            'shift_id' => $shiftSubuh->id,
            'office_location_id' => $office->id,
        ]);

        $conflicts = $scheduleService->detectRestConflicts(2026, 11);
        $this->assertNotEmpty($conflicts);
        $this->assertSame(9.0, $conflicts[0]['rest_hours']);

        // Test shift reminder 1 hour before Shift Subuh (05:00 WIB for 06:00 start)
        Artisan::call('attendance:shift-reminders', ['--now' => '2026-11-12 05:00:00']);
        $this->assertTrue(AuditLog::query()->where('action', 'notification.shift_reminder.clock_in')->exists());
    }

    public function test_clock_in_via_controller_requires_face_photo_and_verifies_face(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);

        $this->enrollDefaultFace($user, $service);

        // 1. Clock in without face photo is rejected
        $response = $this->actingAs($user)->post(route('attendance.clock-in'), [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 5.0,
            'primary_image' => '',
        ]);
        $response->assertSessionHasErrors(['primary_image']);

        // 2. Clock in with matching face photo succeeds
        $response = $this->actingAs($user)->post(route('attendance.clock-in'), [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 5.0,
            'primary_image' => base64_encode('FACE_ID:user_'.$user->id.'|SELFIE_CLOCK_IN'),
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'office_location_id' => $office->id,
            'source' => 'face',
        ]);
    }

    private function enrollDefaultFace(User $user, AttendanceService $service): FaceTemplate
    {
        return $service->enrollUserFace(
            user: $user,
            posesBase64: [
                base64_encode('FACE_ID:user_'.$user->id.'|POSE:1'),
                base64_encode('FACE_ID:user_'.$user->id.'|POSE:2'),
                base64_encode('FACE_ID:user_'.$user->id.'|POSE:3'),
            ],
            consentGiven: true,
            ip: '127.0.0.1'
        );
    }
}
