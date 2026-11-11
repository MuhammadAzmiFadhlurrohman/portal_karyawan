<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntervalBreach extends Model
{
    public $timestamps = false;

    protected $table = 'interval_breaches';

    protected $fillable = [
        'tiket_id',
        'breached_at',
        'elapsed_minutes',
        'previous_chronology_id',
        'current_chronology_id',
        'created_at',
    ];

    protected $casts = [
        'breached_at' => 'datetime',
        'created_at' => 'datetime',
        'elapsed_minutes' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function previousChronology(): BelongsTo
    {
        return $this->belongsTo(TicketChronology::class, 'previous_chronology_id');
    }

    public function currentChronology(): BelongsTo
    {
        return $this->belongsTo(TicketChronology::class, 'current_chronology_id');
    }
}
