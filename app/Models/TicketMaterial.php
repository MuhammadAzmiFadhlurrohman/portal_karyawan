<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMaterial extends Model
{
    public $timestamps = false;

    protected $table = 'ticket_materials';

    protected $fillable = [
        'tiket_id',
        'master_material_id',
        'quantity',
        'used_by',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MasterMaterial::class, 'master_material_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
