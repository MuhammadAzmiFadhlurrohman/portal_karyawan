<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date
 * @property bool $is_locked
 */
class MonthlySchedule extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'shift_id',
        'custom_start_time',
        'custom_end_time',
        'office_location_id',
        'is_locked',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * Determine if this schedule has custom hours configured.
     */
    public function isCustomHours(): bool
    {
        return ! empty($this->custom_start_time) && ! empty($this->custom_end_time);
    }

    /**
     * Get effective start time (H:i) taking custom hours into account.
     */
    public function getEffectiveStartTimeAttribute(): ?string
    {
        if (! empty($this->custom_start_time)) {
            return substr((string) $this->custom_start_time, 0, 5);
        }

        return $this->shift?->start_time ? substr((string) $this->shift->start_time, 0, 5) : null;
    }

    /**
     * Get effective end time (H:i) taking custom hours into account.
     */
    public function getEffectiveEndTimeAttribute(): ?string
    {
        if (! empty($this->custom_end_time)) {
            return substr((string) $this->custom_end_time, 0, 5);
        }

        return $this->shift?->end_time ? substr((string) $this->shift->end_time, 0, 5) : null;
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
