<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterMaterial extends Model
{
    protected $table = 'master_materials';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'stok_tersedia',
        'min_stok',
        'is_active',
    ];

    protected $casts = [
        'stok_tersedia' => 'integer',
        'min_stok' => 'integer',
        'is_active' => 'boolean',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(MaterialMovement::class, 'master_material_id');
    }

    public function ticketUsages(): HasMany
    {
        return $this->hasMany(TicketMaterial::class, 'master_material_id');
    }
}
