<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotIntent extends Model
{
    protected $table = 'chatbot_intents';

    protected $fillable = [
        'intent_key',
        'keywords',
        'response_text',
        'category',
        'is_active',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];
}

