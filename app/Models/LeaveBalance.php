<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'annual_quota',
        'carried_over',
        'used_days',
        'alpa_deducted_days',
        'remaining_days',
        'is_locked_override',
        'override_by',
        'override_at',
        'override_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'annual_quota' => 'integer',
            'carried_over' => 'integer',
            'used_days' => 'integer',
            'alpa_deducted_days' => 'integer',
            'remaining_days' => 'integer',
            'is_locked_override' => 'boolean',
            'override_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function overrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }
}
