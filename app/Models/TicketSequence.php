<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSequence extends Model
{
    protected $table = 'ticket_sequences';

    protected $fillable = [
        'prefix',
        'date',
        'last_number',
    ];

    protected $casts = [
        'date' => 'date',
        'last_number' => 'integer',
    ];
}
