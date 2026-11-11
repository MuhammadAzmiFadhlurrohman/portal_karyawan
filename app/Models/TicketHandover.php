<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHandover extends Model
{
    protected $table = 'ticket_handovers';

    protected $fillable = [
        'tiket_id',
        'shift_from',
        'shift_to',
        'sender_id',
        'recipient_id',
        'notes',
        'auto_summary',
        'acknowledged_at',
        'escalated_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
