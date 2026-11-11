<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property array<string, mixed>|null $snapshot_data
 * @property Carbon $closed_at
 * @property bool $is_locked
 */
class PeriodClosing extends Model
{
    protected $fillable = [
        'year',
        'month',
        'is_locked',
        'total_late_deduction',
        'total_alpa_deduction',
        'total_adjustment_amount',
        'snapshot_data',
        'closed_by',
        'closed_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'is_locked' => 'boolean',
            'total_late_deduction' => 'decimal:2',
            'total_alpa_deduction' => 'decimal:2',
            'total_adjustment_amount' => 'decimal:2',
            'snapshot_data' => 'array',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
