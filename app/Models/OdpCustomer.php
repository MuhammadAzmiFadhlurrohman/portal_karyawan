<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdpCustomer extends Model
{
    use HasFactory;

    protected $table = 'odp_customers';

    protected $fillable = [
        'odp_id',
        'nomor_internet',
        'nama_user',
        'layanan',
        'alamat',
        'nomor',
        'olt_name',
        'note',
        'keterangan',
        'status',
    ];

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class, 'odp_id');
    }
}
