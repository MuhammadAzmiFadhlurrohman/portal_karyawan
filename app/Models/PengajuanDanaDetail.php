<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanDanaDetail extends Model
{
    protected $table = 'pengajuan_dana_detail';

    protected $fillable = [
        'pengajuan_id',
        'master_material_id',
        'deskripsi',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'master_material_id' => 'integer',
        'jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MasterMaterial::class, 'master_material_id');
    }

    public function getItemNameAttribute(): string
    {
        return $this->deskripsi ?? ($this->material?->nama ?? 'Item Pengajuan');
    }

    public function getQtyAttribute(): int
    {
        return (int) ($this->jumlah ?? 1);
    }

    public function getSatuanAttribute(): string
    {
        return $this->material?->satuan ?? 'pcs';
    }

    public function getEstimatedUnitPriceAttribute(): float
    {
        return (float) ($this->harga_satuan ?? 0.0);
    }
}

