<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSla extends Model
{
    protected $table = 'master_sla';

    protected $fillable = [
        'kategori_tiket',
        'priority',
        'sla_minutes',
        'description',
        'is_active',
    ];

    protected $casts = [
        'sla_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}
