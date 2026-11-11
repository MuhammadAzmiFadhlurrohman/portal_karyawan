<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketChronology extends Model
{
    public $timestamps = false;

    protected $table = 'ticket_chronologies';

    protected $fillable = [
        'tiket_id',
        'user_id',
        'category',
        'message',
        'photo_path',
        'video_path',
        'latitude',
        'longitude',
        'gps_accuracy',
        'location_address',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'gps_accuracy' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
