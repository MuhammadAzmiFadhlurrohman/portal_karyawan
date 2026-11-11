<?php

namespace App\Modules\Ticketing\Services;

use App\Models\IntervalBreach;
use App\Models\TicketChronology;
use App\Models\Tiket;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChronologyService
{
    public const VALID_CATEGORIES = [
        'IZIN',
        'OTDR',
        'TRACING',
        'MATERIAL',
        'JOINTING',
        'LINK_UP',
        'SELESAI',
        'LAIN',
        'SYSTEM',
    ];

    /**
     * Add an append-only chronology entry with 30-min compliance tracking (§7.3).
     */
    public function addChronology(
        Tiket $tiket,
        User $user,
        string $category,
        string $message,
        ?string $photoPath = null,
        ?string $videoPath = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?float $gpsAccuracy = null,
        ?Carbon $createdAt = null
    ): TicketChronology {
        $category = strtoupper(trim($category));
        if (! in_array($category, self::VALID_CATEGORIES, true)) {
            throw new InvalidArgumentException("Kategori kronologis '{$category}' tidak valid.");
        }

        $now = $createdAt ?? Carbon::now();

        return DB::transaction(function () use (
            $tiket,
            $user,
            $category,
            $message,
            $photoPath,
            $videoPath,
            $latitude,
            $longitude,
            $gpsAccuracy,
            $now
        ): TicketChronology {
            // Find last chronology before this one
            /** @var TicketChronology|null $lastChronology */
            $lastChronology = $tiket->chronologies()->latest('id')->first();

            // Resolve cached reverse geocoded address if lat/lng given
            $address = null;
            if ($latitude !== null && $longitude !== null) {
                $address = $this->reverseGeocode($latitude, $longitude);
            }

            $chronology = TicketChronology::query()->create([
                'tiket_id' => $tiket->id,
                'user_id' => $user->id,
                'category' => $category,
                'message' => $message,
                'photo_path' => $photoPath,
                'video_path' => $videoPath,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'gps_accuracy' => $gpsAccuracy,
                'location_address' => $address,
                'created_at' => $now,
            ]);

            // If ticket was still OPEN, mark first response and set to PROSES (§7.2)
            if ($tiket->status === 'OPEN' && $category !== 'SYSTEM') {
                $tiket->status = 'PROSES';
                $tiket->first_response_at = $now;
                $tiket->save();

                $tiket->statusHistories()->create([
                    'from_status' => 'OPEN',
                    'to_status' => 'PROSES',
                    'changed_by' => $user->id,
                    'reason' => 'Respon pertama teknisi melalui input kronologis',
                    'created_at' => $now,
                ]);
            }

            // Check 30-minute interval compliance (§7.3)
            // Only check when in PROSES and not during active Stop Clock
            $activeStopClock = $tiket->activeStopClock()->exists();
            if ($tiket->status === 'PROSES' && ! $activeStopClock) {
                $referenceTime = $lastChronology ? $lastChronology->created_at : $tiket->first_response_at;
                if ($referenceTime) {
                    $elapsedMinutes = (int) abs($referenceTime->diffInMinutes($now));
                    if ($elapsedMinutes > 30) {
                        IntervalBreach::query()->create([
                            'tiket_id' => $tiket->id,
                            'breached_at' => $now,
                            'elapsed_minutes' => $elapsedMinutes,
                            'previous_chronology_id' => $lastChronology?->id,
                            'current_chronology_id' => $chronology->id,
                            'created_at' => $now,
                        ]);
                    }
                }
            }

            AuditLogger::record(
                action: 'ticket.chronology.add',
                auditable: $chronology,
                newValues: ['category' => $category, 'message' => mb_substr($message, 0, 100)],
                reason: "Entri kronologis ({$category}) ditambahkan",
                actorId: $user->id
            );

            return $chronology;
        });
    }

    /**
     * Get current 30-minute interval compliance status (§7.3).
     * Returns: 'NORMAL' (<= 20m), 'WARNING' (21-30m), 'OVERDUE' (> 30m), 'STOP_CLOCK'.
     */
    public function getIntervalStatus(Tiket $tiket, ?Carbon $now = null): string
    {
        if ($tiket->activeStopClock()->exists()) {
            return 'STOP_CLOCK';
        }

        if (! in_array($tiket->status, ['PROSES', 'OPEN'], true)) {
            return 'NORMAL';
        }

        $currentTime = $now ?? Carbon::now('Asia/Jakarta');
        $lastChronology = $tiket->chronologies()->latest('created_at')->first();
        $referenceTime = $lastChronology?->created_at ?? $tiket->first_response_at ?? $tiket->tanggal_open;

        if (! $referenceTime) {
            return 'NORMAL';
        }

        $elapsed = (int) $referenceTime->diffInMinutes($currentTime);

        if ($elapsed <= 20) {
            return 'NORMAL';
        }

        if ($elapsed <= 30) {
            return 'WARNING';
        }

        return 'OVERDUE';
    }

    /**
     * Reverse geocoding with 3-decimal rounding cache to prevent external API hammering (§7.3, §10.2).
     */
    public function reverseGeocode(float $latitude, float $longitude): string
    {
        $latRound = round($latitude, 3);
        $lngRound = round($longitude, 3);
        $cacheKey = "geo_addr_{$latRound}_{$lngRound}";

        return Cache::remember($cacheKey, 86400, function () use ($latRound, $lngRound): string {
            // Default mock/local format; when connecting to Nominatim in production, uses whitelist SSRF
            return "Lokasi ({$latRound}, {$lngRound})";
        });
    }
}
