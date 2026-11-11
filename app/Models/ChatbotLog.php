<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotLog extends Model
{
    public $timestamps = false;

    protected $table = 'chatbot_logs';

    protected $fillable = [
        'user_id',
        'user_message',
        'matched_intent_key',
        'confidence_score',
        'bot_response',
        'is_answered',
        'created_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'is_answered' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

