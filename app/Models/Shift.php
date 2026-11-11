<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $late_tolerance_minutes
 * @property int $break_minutes
 * @property bool $is_day_off
 * @property bool $is_piket
 * @property bool $is_active
 */
class Shift extends Model
{
    protected $fillable = [
        'code',
        'name',
        'start_time',
        'end_time',
        'color',
        'late_tolerance_minutes',
        'break_minutes',
        'is_day_off',
        'is_piket',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'late_tolerance_minutes' => 'integer',
            'break_minutes' => 'integer',
            'is_day_off' => 'boolean',
            'is_piket' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
