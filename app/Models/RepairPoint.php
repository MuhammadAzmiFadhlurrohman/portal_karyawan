<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairPoint extends Model
{
    protected $table = 'repair_points';

    protected $fillable = [
        'tiket_id',
        'nama_titik',
        'latitude',
        'longitude',
        'jenis_kerusakan',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function jointClosures(): HasMany
    {
        return $this->hasMany(JointClosure::class, 'repair_point_id');
    }
}
