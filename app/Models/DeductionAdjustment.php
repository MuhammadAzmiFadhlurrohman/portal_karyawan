<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeductionAdjustment extends Model
{
    protected $fillable = [
        'user_id',
        'source_year',
        'source_month',
        'applied_year',
        'applied_month',
        'attendance_correction_id',
        'amount',
        'description',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_year' => 'integer',
            'source_month' => 'integer',
            'applied_year' => 'integer',
            'applied_month' => 'integer',
            'amount' => 'decimal:2',
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
     * @return BelongsTo<AttendanceCorrection, $this>
     */
    public function correction(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrection::class, 'attendance_correction_id');
    }
}
