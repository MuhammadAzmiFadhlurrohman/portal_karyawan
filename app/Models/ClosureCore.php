<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClosureCore extends Model
{
    protected $table = 'closure_cores';

    protected $fillable = [
        'joint_closure_id',
        'tube_asal',
        'core_asal',
        'tube_tujuan',
        'core_tujuan',
        'status_core',
        'loss_db',
        'notes',
    ];

    protected $casts = [
        'core_asal' => 'integer',
        'core_tujuan' => 'integer',
        'loss_db' => 'decimal:2',
    ];

    public function jointClosure(): BelongsTo
    {
        return $this->belongsTo(JointClosure::class, 'joint_closure_id');
    }
}
