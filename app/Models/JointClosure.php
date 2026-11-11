<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JointClosure extends Model
{
    protected $table = 'joint_closures';

    protected $fillable = [
        'tiket_id',
        'repair_point_id',
        'nama_closure',
        'kapasitas_core',
        'tipe_closure',
    ];

    protected $casts = [
        'kapasitas_core' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function repairPoint(): BelongsTo
    {
        return $this->belongsTo(RepairPoint::class, 'repair_point_id');
    }

    public function cores(): HasMany
    {
        return $this->hasMany(ClosureCore::class, 'joint_closure_id');
    }
}
