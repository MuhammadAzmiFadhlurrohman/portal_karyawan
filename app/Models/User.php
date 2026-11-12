<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property Carbon|null $join_date
 * @property bool $leaderboard_opt_out
 * @property bool $is_active
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'username',
        'name',
        'email',
        'phone',
        'nik',
        'division',
        'position',
        'foto',
        'bank',
        'no_rekening',
        'atas_nama',
        'supervisor_id',
        'join_date',
        'potongan_alpa',
        'must_change_password',
        'password_changed_at',
        'leaderboard_opt_out',
        'is_active',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'join_date' => 'date',
            'potongan_alpa' => 'decimal:2',
            'must_change_password' => 'boolean',
            'leaderboard_opt_out' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Lookup user by any of the 4 unique identifiers (§6.1):
     * phone, username, email, or employee_code.
     */
    public static function findByIdentifier(string $identifier): ?self
    {
        $trimmed = trim($identifier);

        return self::query()
            ->where('username', $trimmed)
            ->orWhere('email', $trimmed)
            ->orWhere('phone', $trimmed)
            ->orWhere('employee_code', $trimmed)
            ->first();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    /**
     * @return HasMany<UserDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * @return HasMany<UserSession, $this>
     */
    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * @return HasOne<FaceTemplate, $this>
     */
    public function faceTemplate(): HasOne
    {
        return $this->hasOne(FaceTemplate::class);
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * @return HasMany<MonthlySchedule, $this>
     */
    public function monthlySchedules(): HasMany
    {
        return $this->hasMany(MonthlySchedule::class);
    }

    /**
     * Get avatar URL if the photo physically exists on disk.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        $cleanPath = ltrim($this->foto, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
            if (file_exists(public_path('storage/' . $cleanPath))) {
                return asset('storage/' . $cleanPath);
            }
        } catch (\Throwable $e) {
            // fallback gracefully
        }

        return null;
    }

    /**
     * Get user initials (1-2 uppercase letters) for avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? '');
        if ($name === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name);
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        }

        return strtoupper(mb_substr($name, 0, 1));
    }

    /**
     * Resolve effective approver taking into account active delegations (§4.3).
     */
    public function resolveEffectiveApprover(string $module = 'all', ?CarbonInterface $date = null): ?self
    {
        if (! $this->supervisor_id) {
            return null;
        }

        $targetDate = ($date ?? now())->toDateString();

        $delegation = ApprovalDelegation::query()
            ->where('delegator_id', $this->supervisor_id)
            ->where('is_active', true)
            ->whereIn('module', ['all', $module])
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->latest('id')
            ->first();

        if ($delegation && $delegation->delegate && $delegation->delegate->id !== $this->id) {
            return $delegation->delegate;
        }

        return $this->supervisor;
    }
}
