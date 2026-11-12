<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odp extends Model
{
    use HasFactory;

    protected $table = 'odps';

    protected $fillable = [
        'pon_id',
        'nama_odp',
        'port_max',
        'port_terpakai',
        'latitude',
        'longitude',
        'keterangan',
    ];

    protected $casts = [
        'port_max' => 'integer',
        'port_terpakai' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function pon(): BelongsTo
    {
        return $this->belongsTo(Pon::class, 'pon_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(OdpCustomer::class, 'odp_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OdpUserHistory::class, 'odp_id')->latest('waktu_input');
    }

    /**
     * Recalculate port_terpakai based on customers count
     */
    public function recalculateUsage(): void
    {
        $this->port_terpakai = $this->customers()->where('status', 'aktif')->count();
        $this->save();

        if ($this->pon) {
            $this->pon->recalculateUsage();
        }
    }
}

