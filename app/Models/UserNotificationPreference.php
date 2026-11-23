<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $table = 'user_notification_preferences';

    protected $fillable = [
        'user_id',
        'notify_pengajuan',
        'notify_lembur',
        'notify_tiket',
    ];

    protected $casts = [
        'notify_pengajuan' => 'boolean',
        'notify_lembur' => 'boolean',
        'notify_tiket' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
