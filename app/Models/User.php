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
     * Determine if user has supervisor/manager/atasan authority.
     */
    public function isAtasan(): bool
    {
        if ($this->hasRole(['super_admin', 'admin', 'ceo', 'manager_teknisi', 'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum'])) {
            return true;
        }

        if ($this->can('gomsn.pengajuan.approve_atasan') || $this->can('attendance.correction.approve') || $this->can('gomsn.lembur.approve')) {
            return true;
        }

        return self::query()->where('supervisor_id', $this->id)->exists();
    }

    /**
     * Get display badge for chat / ticketing: Prioritaskan Divisi/Jabatan sesuai profil header.
     */
    public function getChatBadgeAttribute(): string
    {
        if (! empty($this->division)) {
            return $this->division;
        }

        if (! empty($this->position)) {
            return $this->position;
        }

        if ($this->isAtasan()) {
            return 'Atasan';
        }

        return 'Staf';
    }

    /**
     * Get color styling for each division and role badge.
     *
     * @return array{bg: string, text: string, border: string, class: string}
     */
    public static function getDivisionBadgeStyle(?string $divisionOrRole): array
    {
        $normalized = strtolower(trim((string) $divisionOrRole));

        if (empty($normalized)) {
            return [
                'bg' => '#F1F5F9',
                'text' => '#475569',
                'border' => '#E2E8F0',
                'class' => 'bg-slate-100 text-slate-700 border-slate-200',
            ];
        }

        // Teknik / Lapangan -> Warm Orange
        if (str_contains($normalized, 'teknik') || str_contains($normalized, 'teknisi') || str_contains($normalized, 'field')) {
            return [
                'bg' => '#FFEDD5',
                'text' => '#C2410C',
                'border' => '#FED7AA',
                'class' => 'bg-orange-100 text-orange-800 border-orange-200',
            ];
        }

        // IT & Sistem -> Modern Indigo
        if (str_contains($normalized, 'it') || str_contains($normalized, 'sistem') || str_contains($normalized, 'system') || str_contains($normalized, 'dev') || str_contains($normalized, 'programmer')) {
            return [
                'bg' => '#EEF2FF',
                'text' => '#4338CA',
                'border' => '#C7D2FE',
                'class' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            ];
        }

        // NOC -> Sky Blue
        if (str_contains($normalized, 'noc')) {
            return [
                'bg' => '#E0F2FE',
                'text' => '#0369A1',
                'border' => '#BAE6FD',
                'class' => 'bg-sky-100 text-sky-800 border-sky-200',
            ];
        }

        // Finance & Akuntansi -> Emerald Green
        if (str_contains($normalized, 'finance') || str_contains($normalized, 'keuangan') || str_contains($normalized, 'akuntansi') || str_contains($normalized, 'kasir')) {
            return [
                'bg' => '#ECFDF5',
                'text' => '#047857',
                'border' => '#A7F3D0',
                'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ];
        }

        // HRD & Human Capital -> Coral Rose
        if (str_contains($normalized, 'hrd') || str_contains($normalized, 'human') || str_contains($normalized, 'personalia') || str_contains($normalized, 'hc')) {
            return [
                'bg' => '#FFE4E6',
                'text' => '#BE123C',
                'border' => '#FECDD3',
                'class' => 'bg-rose-100 text-rose-800 border-rose-200',
            ];
        }

        // Operasional -> Brand Teal
        if (str_contains($normalized, 'operasional') || str_contains($normalized, 'ops')) {
            return [
                'bg' => '#CCFBF1',
                'text' => '#0F766E',
                'border' => '#99F6E4',
                'class' => 'bg-teal-100 text-teal-800 border-teal-200',
            ];
        }

        // Direksi & Executive -> Royal Purple
        if (str_contains($normalized, 'direksi') || str_contains($normalized, 'executive') || str_contains($normalized, 'direktur') || str_contains($normalized, 'general manager') || str_contains($normalized, 'gm')) {
            return [
                'bg' => '#F3E8FF',
                'text' => '#6B21A8',
                'border' => '#E9D5FF',
                'class' => 'bg-purple-100 text-purple-800 border-purple-200',
            ];
        }

        // Dispatch -> Golden Amber
        if (str_contains($normalized, 'dispatch')) {
            return [
                'bg' => '#FEF3C7',
                'text' => '#B45309',
                'border' => '#FDE68A',
                'class' => 'bg-amber-100 text-amber-800 border-amber-200',
            ];
        }

        // Default / Umum / Petugas -> Slate Gray
        return [
            'bg' => '#F1F5F9',
            'text' => '#475569',
            'border' => '#E2E8F0',
            'class' => 'bg-slate-100 text-slate-700 border-slate-200',
        ];
    }

    /**
     * Get division badge styling for this user instance.
     *
     * @return array{bg: string, text: string, border: string, class: string}
     */
    public function getDivisionBadgeStyleAttribute(): array
    {
        return self::getDivisionBadgeStyle($this->chat_badge);
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
     * @return HasMany<WebAuthnCredential, $this>
     */
    public function webauthnCredentials(): HasMany
    {
        return $this->hasMany(WebAuthnCredential::class);
    }

    /**
     * Get avatar URL if the photo physically exists on disk.
     * Served via authenticated Laravel route so it works reliably in Docker/VPS even without storage:link.
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
            if (
                \Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)
                || file_exists(public_path('storage/' . $cleanPath))
                || file_exists(public_path($cleanPath))
                || file_exists(base_path('../gomsn/uploads/foto_profil/' . basename($cleanPath)))
            ) {
                return route('portal.user.avatar', [
                    'user' => $this->id,
                    'v' => substr(md5($this->foto . ($this->updated_at?->timestamp ?? '')), 0, 8),
                ]);
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

    public function fcmTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserFcmToken::class);
    }

    public function notificationPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserNotificationPreference::class);
    }
}
