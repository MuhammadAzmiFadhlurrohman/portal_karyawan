<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Olt extends Model
{
    use HasFactory;

    protected $table = 'olts';

    protected $fillable = [
        'nama_olt',
        'kode',
        'lokasi',
        'ip_address',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pons(): HasMany
    {
        return $this->hasMany(Pon::class, 'olt_id');
    }

    public function odps(): HasManyThrough
    {
        return $this->hasManyThrough(Odp::class, Pon::class, 'olt_id', 'pon_id');
    }
}
