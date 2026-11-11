<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $dispute_deadline_at
 * @property Carbon|null $supervisor_approved_at
 * @property Carbon|null $hrd_approved_at
 * @property Carbon|null $cancelled_at
 * @property bool $is_alpa_conversion
 */
class Leave extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'working_days',
        'reason',
        'attachment_path',
        'status',
        'is_alpa_conversion',
        'dispute_deadline_at',
        'supervisor_approved_by',
        'supervisor_approved_at',
        'hrd_approved_by',
        'hrd_approved_at',
        'rejected_by',
        'rejected_reason',
        'cancelled_at',
        'cancel_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'working_days' => 'integer',
            'is_alpa_conversion' => 'boolean',
            'dispute_deadline_at' => 'datetime',
            'supervisor_approved_at' => 'datetime',
            'hrd_approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
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
    public function supervisorApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_approved_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function hrdApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hrd_approved_by');
    }
}
