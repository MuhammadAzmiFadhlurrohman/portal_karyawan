<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreManeuver extends Model
{
    protected $table = 'core_maneuvers';

    protected $fillable = [
        'tiket_id',
        'segmen',
        'core_asal',
        'core_tujuan',
        'keterangan',
    ];

    protected $casts = [
        'core_asal' => 'integer',
        'core_tujuan' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function getTubeSourceAttribute(): string
    {
        return 'Tube 1';
    }

    public function getCoreSourceAttribute(): int
    {
        return (int) $this->core_asal;
    }

    public function getTubeTargetAttribute(): string
    {
        return 'Tube 1';
    }

    public function getCoreTargetAttribute(): int
    {
        return (int) $this->core_tujuan;
    }

    public function getReasonAttribute(): ?string
    {
        return $this->keterangan;
    }
}
