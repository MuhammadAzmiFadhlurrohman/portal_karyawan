<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $date
 * @property bool $is_collective_leave
 */
class NationalHoliday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'is_collective_leave',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_collective_leave' => 'boolean',
        ];
    }
}
