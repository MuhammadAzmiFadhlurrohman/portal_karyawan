<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date
 * @property Carbon|null $clock_in_at
 * @property Carbon|null $clock_out_at
 * @property Carbon|null $captured_at
 * @property Carbon|null $reviewed_at
 * @property bool $is_mock_location
 * @property bool $is_flagged
 */
class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'date',
        'shift_id',
        'office_location_id',
        'attendance_type',
        'status',
        'source',
        'clock_in_at',
        'clock_out_at',
        'captured_at',
        'lat_in',
        'lng_in',
        'gps_accuracy_in',
        'distance_m_in',
        'lat_out',
        'lng_out',
        'gps_accuracy_out',
        'is_mock_location',
        'face_score',
        'liveness_score',
        'is_flagged',
        'flag_reason',
        'late_minutes',
        'work_duration_minutes',
        'proposed_overtime_minutes',
        'notes',
        'ticket_reference',
        'photo_in_primary_path',
        'photo_in_challenge_path',
        'photo_out_path',
        'device_id',
        'client_uuid',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'captured_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'lat_in' => 'float',
            'lng_in' => 'float',
            'gps_accuracy_in' => 'float',
            'distance_m_in' => 'float',
            'lat_out' => 'float',
            'lng_out' => 'float',
            'gps_accuracy_out' => 'float',
            'is_mock_location' => 'boolean',
            'face_score' => 'float',
            'liveness_score' => 'float',
            'is_flagged' => 'boolean',
            'late_minutes' => 'integer',
            'work_duration_minutes' => 'integer',
            'proposed_overtime_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return BelongsTo<OfficeLocation, $this>
     */
    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class);
    }
}
