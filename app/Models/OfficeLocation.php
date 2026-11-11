<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float $lat
 * @property float $lng
 * @property int $radius_m
 * @property bool $is_active
 */
class OfficeLocation extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'lat',
        'lng',
        'radius_m',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'radius_m' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
