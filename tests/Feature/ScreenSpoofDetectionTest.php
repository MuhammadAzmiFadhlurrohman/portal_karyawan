<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\OfficeLocation;
use App\Models\User;
use App\Modules\Attendance\Services\AttendanceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScreenSpoofDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_clock_in_rejected_and_logged_when_client_anti_spoof_meta_flags_screen(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);
        $descriptor = $this->enrollWith128dDescriptor($user, $service);

        $response = $this->actingAs($user)->post(route('attendance.clock-in'), [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 6.0,
            'primary_image' => base64_encode('FACE_ID:user_'.$user->id.'|SELFIE'),
            'challenge_image' => base64_encode('FACE_ID:user_'.$user->id.'|CHALLENGE'),
            'face_descriptor' => json_encode($descriptor),
            'anti_spoof_meta' => json_encode([
                'screen_detected' => true,
                'screen_score' => 72,
                'metrics' => ['bezelScore' => 42, 'moireScore' => 30],
            ]),
        ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('attendances', ['user_id' => $user->id]);
        $this->assertTrue(
            AuditLog::query()->where('action', 'attendance.spoof_attempt_blocked')->exists(),
            'Client screen spoof detection must record attendance.spoof_attempt_blocked audit log.'
        );
    }

    public function test_clock_in_rejected_by_server_gd_when_image_simulates_second_phone_screen(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);
        $descriptor = $this->enrollWith128dDescriptor($user, $service);

        $screenSpoofBase64 = $this->buildSecondPhoneScreenJpegBase64();

        // Bypass JS client check (no anti_spoof_meta sent) to verify server-side GD analyzer blocks it
        $response = $this->actingAs($user)->post(route('attendance.clock-in'), [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 5.0,
            'primary_image' => $screenSpoofBase64,
            'challenge_image' => 'CHALLENGE_VERIFIED_TEST',
            'face_descriptor' => json_encode($descriptor),
        ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('attendances', ['user_id' => $user->id]);

        $log = AuditLog::query()->where('action', 'attendance.spoof_attempt_blocked')->latest('id')->first();
        $this->assertNotNull($log, 'Server GD spoof check must log attendance.spoof_attempt_blocked.');
        $this->assertSame('SERVER_SCREEN_SPOOF_DETECTED', $log->new_values['reason'] ?? null);
    }

    public function test_clock_in_accepted_by_server_gd_for_natural_warm_skin_portrait_jpeg(): void
    {
        $user = User::where('username', 'teknisi1')->firstOrFail();
        $office = OfficeLocation::where('code', 'BDG-HQ')->firstOrFail();
        $service = app(AttendanceService::class);
        $descriptor = $this->enrollWith128dDescriptor($user, $service);

        $naturalPrimary = $this->buildNaturalPortraitJpegBase64(0);
        $naturalChallenge = $this->buildNaturalPortraitJpegBase64(12);

        $response = $this->actingAs($user)->post(route('attendance.clock-in'), [
            'office_location_id' => $office->id,
            'attendance_type' => 'office',
            'lat' => $office->lat,
            'lng' => $office->lng,
            'gps_accuracy' => 5.0,
            'primary_image' => $naturalPrimary,
            'challenge_image' => $naturalChallenge,
            'face_descriptor' => json_encode($descriptor),
            'anti_spoof_meta' => json_encode([
                'screen_detected' => false,
                'screen_score' => 6,
                'liveness_passed' => true,
                'challenge_type' => 'blink',
            ]),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'office_location_id' => $office->id,
            'source' => 'face',
        ]);
        $this->assertFalse(
            AuditLog::query()->where('action', 'attendance.spoof_attempt_blocked')->exists(),
            'Natural portrait selfie must not be falsely flagged as a spoof attempt.'
        );
    }

    /**
     * @return array<int, float>
     */
    private function enrollWith128dDescriptor(User $user, AttendanceService $service): array
    {
        $descriptor = [];
        for ($i = 0; $i < 128; $i++) {
            $descriptor[] = round((($i % 11) - 5) * 0.04, 6);
        }

        $sample = base64_encode('FACE_ID:user_'.$user->id.'|ENROLL_SAMPLE');
        $service->enrollUserFace(
            user: $user,
            posesBase64: [$sample, $sample, $sample],
            consentGiven: true,
            ip: '127.0.0.1',
            descriptors: $descriptor
        );

        return $descriptor;
    }

    /**
     * Build a 320x240 JPEG simulating a second phone screen showing a face photo:
     * dark-blue screen bezel perimeter + glowing inner screen + skin area with RGB Moire + glass specular glare.
     */
    private function buildSecondPhoneScreenJpegBase64(): string
    {
        $w = 320;
        $h = 240;
        $im = imagecreatetruecolor($w, $h);

        // Dark bluish phone bezel & outer background
        $bezelBg = imagecolorallocate($im, 18, 22, 34);
        imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $bezelBg);

        // Bright backlit inner phone screen rectangle (strong sharp 4-side border edge)
        $screenBg = imagecolorallocate($im, 198, 212, 232);
        imagefilledrectangle($im, 34, 20, $w - 35, $h - 21, $screenBg);

        // Face skin region with alternating horizontal chromatic Moire subpixel tint
        for ($y = 42; $y < 196; $y++) {
            for ($x = 92; $x < 228; $x++) {
                $phase = ($x % 2 === 0) ? 14 : -14;
                $r = max(0, min(255, 206 + $phase));
                $g = max(0, min(255, 162 - $phase));
                $b = max(0, min(255, 178 + $phase));
                imagesetpixel($im, $x, $y, imagecolorallocate($im, $r, $g, $b));
            }
        }

        // Specular glass glare patch (~8% of viewport)
        $glare = imagecolorallocate($im, 250, 250, 252);
        imagefilledrectangle($im, 60, 44, 118, 86, $glare);

        ob_start();
        imagejpeg($im, null, 92);
        $raw = (string) ob_get_clean();
        imagedestroy($im);

        return 'data:image/jpeg;base64,'.base64_encode($raw);
    }

    /**
     * Build a 320x240 JPEG simulating a natural warm indoor live selfie without bezels, glare, or Moire.
     */
    private function buildNaturalPortraitJpegBase64(int $variantOffset = 0): string
    {
        $w = 320;
        $h = 240;
        $im = imagecreatetruecolor($w, $h);

        // Soft warm indoor background gradient
        for ($y = 0; $y < $h; $y++) {
            $shade = (int) (225 - ($y / $h) * 28);
            $bgRow = imagecolorallocate($im, $shade, max(0, $shade - 5), max(0, $shade - 14));
            imageline($im, 0, $y, $w - 1, $y, $bgRow);
        }

        // Smooth natural warm skin ellipse with gentle natural shading
        for ($y = 38; $y < 202; $y++) {
            for ($x = 84 + $variantOffset; $x < 226 + $variantOffset; $x++) {
                $dx = ($x - (155 + $variantOffset)) / 70.0;
                $dy = ($y - 120) / 82.0;
                if (($dx * $dx + $dy * $dy) <= 1.0) {
                    $light = 1.0 - 0.18 * ($dx * $dx + $dy * $dy);
                    $r = (int) round(204 * $light);
                    $g = (int) round(148 * $light);
                    $b = (int) round(116 * $light);
                    imagesetpixel($im, $x, $y, imagecolorallocate($im, $r, $g, $b));
                }
            }
        }

        // Subtle dark hair & shoulders for realistic variance without blue cast or bezels
        $hair = imagecolorallocate($im, 42, 34, 28);
        imagefilledellipse($im, 155 + $variantOffset, 46, 110, 32, $hair);

        ob_start();
        imagejpeg($im, null, 88);
        $raw = (string) ob_get_clean();
        imagedestroy($im);

        return 'data:image/jpeg;base64,'.base64_encode($raw);
    }
}
