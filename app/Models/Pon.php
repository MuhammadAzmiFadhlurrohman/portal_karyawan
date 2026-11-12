<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pon extends Model
{
    use HasFactory;

    protected $table = 'pons';

    protected $fillable = [
        'olt_id',
        'nama_pon',
        'port_max',
        'port_terisi',
        'total_port',
        'keterangan',
    ];

    protected $casts = [
        'port_max' => 'integer',
        'port_terisi' => 'integer',
        'total_port' => 'integer',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    public function odps(): HasMany
    {
        return $this->hasMany(Odp::class, 'pon_id');
    }

    /**
     * Recalculate port_terisi based on sum of ODP count or custom logic
     */
    public function recalculateUsage(): void
    {
        $this->port_terisi = $this->odps()->count();
        $this->save();
    }
}

