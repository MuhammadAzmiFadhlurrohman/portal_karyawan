<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date
 * @property Carbon|null $supervisor_approved_at
 * @property Carbon|null $hrd_approved_at
 * @property bool $is_post_closing_adjustment
 */
class AttendanceCorrection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'requested_status',
        'requested_attendance_type',
        'requested_clock_in_time',
        'requested_clock_out_time',
        'reason',
        'attachment_path',
        'status',
        'is_post_closing_adjustment',
        'supervisor_approved_by',
        'supervisor_approved_at',
        'hrd_approved_by',
        'hrd_approved_at',
        'rejected_by',
        'rejected_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_post_closing_adjustment' => 'boolean',
            'supervisor_approved_at' => 'datetime',
            'hrd_approved_at' => 'datetime',
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
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
