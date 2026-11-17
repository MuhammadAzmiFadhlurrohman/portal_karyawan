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
        'reply_to_id',
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
        'read_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'gps_accuracy' => 'decimal:2',
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get reliable direct streaming URL for photo attachment across Docker/VPS.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (empty($this->photo_path)) {
            return null;
        }

        try {
            return route('tickets.chronologies.photo', $this);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->photo_path);
        }
    }

    /**
     * Get reliable direct streaming URL for video attachment across Docker/VPS.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (empty($this->video_path)) {
            return null;
        }

        try {
            return route('tickets.chronologies.video', $this);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->video_path);
        }
    }
}
