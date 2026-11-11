<?php

namespace App\Modules\Attendance\Services;

use App\Models\OfficeLocation;
use App\Services\SystemSettingService;
use Illuminate\Validation\ValidationException;

class GeofenceService
{
    /**
     * Calculate Haversine distance in meters between two WGS84 coordinates.
     */
    public function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusMeters = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusMeters * $c, 2);
    }

    /**
     * Validate geofence & anti-manipulation rules (§5.1, AC-01).
     *
     * Rule: Passes if `distance <= radius_m + min(gps_accuracy, 15m)`.
     * Flags attendance if `gps_accuracy > 30m` or `is_mock_location == true`.
     *
     * @return array{distance_m: float, allowed_radius_m: float, is_flagged: bool, flag_reason: string|null}
     */
    public function evaluateLocation(
        OfficeLocation $office,
        float $lat,
        float $lng,
        float $gpsAccuracyMeters,
        bool $isMockLocation,
        string $attendanceType = 'office'
    ): array {
        $distanceMeters = $this->calculateDistanceMeters($office->lat, $office->lng, $lat, $lng);
        $accuracyTolerance = min(max(0.0, $gpsAccuracyMeters), 15.0);
        $effectiveLimit = $office->radius_m + $accuracyTolerance;

        if ($attendanceType === 'office' && $distanceMeters > $effectiveLimit) {
            throw ValidationException::withMessages([
                'location' => sprintf(
                    'Clock-in ditolak: Jarak Anda %.1f meter dari %s (batas radius kantor %d meter + toleransi akurasi %.1f meter). (AC-01)',
                    $distanceMeters,
                    $office->name,
                    $office->radius_m,
                    $accuracyTolerance
                ),
            ]);
        }

        $maxGpsAccuracy = (float) SystemSettingService::get('geofence.max_gps_accuracy_m', 30.0);
        $flagReasons = [];

        if ($isMockLocation) {
            $flagReasons[] = 'Mock location / Fake GPS terdeteksi pada perangkat (§5.1)';
        }

        if ($gpsAccuracyMeters > $maxGpsAccuracy) {
            $flagReasons[] = sprintf(
                'Akurasi GPS terlalu rendah (%.1fm > batas %.1fm)',
                $gpsAccuracyMeters,
                $maxGpsAccuracy
            );
        }

        return [
            'distance_m' => $distanceMeters,
            'allowed_radius_m' => $effectiveLimit,
            'is_flagged' => count($flagReasons) > 0,
            'flag_reason' => count($flagReasons) > 0 ? implode('; ', $flagReasons) : null,
        ];
    }
}
