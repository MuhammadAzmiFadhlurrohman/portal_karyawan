<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property array<int, float> $encrypted_embedding
 * @property Carbon $consent_at
 * @property Carbon $enrolled_at
 */
class FaceTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'encrypted_embedding',
        'primary_photo_hash',
        'reference_photo_path',
        'model_version',
        'poses_count',
        'quality_score',
        'consent_at',
        'consent_ip',
        'enrolled_at',
    ];

    protected $hidden = [
        'encrypted_embedding',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'encrypted_embedding' => 'encrypted:array',
            'poses_count' => 'integer',
            'quality_score' => 'float',
            'consent_at' => 'datetime',
            'enrolled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
