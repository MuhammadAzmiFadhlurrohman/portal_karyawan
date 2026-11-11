<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $requester_date
 * @property Carbon $target_date
 * @property Carbon $expires_at
 * @property Carbon|null $target_approved_at
 * @property Carbon|null $approved_at
 * @property bool $is_backup
 */
class ShiftSwap extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'requester_id',
        'target_user_id',
        'requester_date',
        'target_date',
        'is_backup',
        'req_d1_shift_id',
        'tar_d1_shift_id',
        'req_d2_shift_id',
        'tar_d2_shift_id',
        'status',
        'reason',
        'expires_at',
        'target_approved_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requester_date' => 'date',
            'target_date' => 'date',
            'is_backup' => 'boolean',
            'expires_at' => 'datetime',
            'target_approved_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function reqD1Shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'req_d1_shift_id');
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function tarD1Shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'tar_d1_shift_id');
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function reqD2Shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'req_d2_shift_id');
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function tarD2Shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'tar_d2_shift_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
