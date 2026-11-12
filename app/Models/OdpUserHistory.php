<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdpUserHistory extends Model
{
    use HasFactory;

    protected $table = 'odp_user_histories';

    protected $fillable = [
        'odp_id',
        'pon_id',
        'user_id',
        'nomor_internet',
        'nama_pon',
        'nama_odp',
        'nama_user',
        'aksi',
        'keterangan',
        'waktu_input',
    ];

    protected $casts = [
        'waktu_input' => 'datetime',
    ];

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }

    public function pon(): BelongsTo
    {
        return $this->belongsTo(Pon::class, 'pon_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

