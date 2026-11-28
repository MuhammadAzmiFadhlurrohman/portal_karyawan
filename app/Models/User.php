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
        'office',
        'office_location_id',
        'position',
        'gender',
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
     * Scope to exclude supervisors, managers, executives, admins, and system placeholder accounts
     * from shift and piket roster scheduling.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExcludeAtasan($query)
    {
        $atasanRoles = [
            'super_admin',
            'admin',
            'ceo',
            'manager_teknisi',
            'atasan',
            'atasan_noc',
            'atasan_teknik',
            'atasan_umum',
        ];
        $excludedDivisions = ['Direksi', 'Executive'];

        return $query->whereNotIn('division', $excludedDivisions)
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', $atasanRoles))
            ->where(function ($q) {
                $q->whereNull('position')
                    ->orWhere(function ($sq) {
                        $sq->where('position', 'not like', '%Manager%')
                            ->where('position', 'not like', '%Supervisor%')
                            ->where('position', 'not like', '%Head%')
                            ->where('position', 'not like', '%Direktur%')
                            ->where('position', 'not like', '%CEO%')
                            ->where('position', 'not like', '%Administrator%');
                    });
            })
            ->whereNotIn('id', function ($sq) {
                $sq->select('supervisor_id')
                    ->from('users')
                    ->whereNotNull('supervisor_id');
            })
            ->whereRaw('LOWER(TRIM(name)) != LOWER(TRIM(division))');
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

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'office_location_id');
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

    /**
     * Get customized attendance illustration avatar URL (Pria vs Perempuan).
     */
    public function getAttendanceAvatarUrlAttribute(): string
    {
        return $this->getAttendanceAvatarUrlForTheme('teal');
    }

    /**
     * Get theme-aware attendance illustration URL (teal, blue, indigo, emerald, purple, rose, amber, pink).
     */
    public function getAttendanceAvatarUrlForTheme(?string $theme = 'teal'): string
    {
        $validThemes = ['teal', 'blue', 'indigo', 'emerald', 'purple', 'rose', 'amber', 'pink'];
        $accent = in_array($theme, $validThemes, true) ? $theme : 'teal';
        $prefix = $this->isPerempuan() ? 'absenperempuan' : 'absenpria';

        $file = "assets/logo/{$prefix}-{$accent}.png";
        $fullPath = public_path($file);
        if (! is_file($fullPath)) {
            $file = "assets/logo/{$prefix}.png";
            $fullPath = public_path($file);
        }
        if (! is_file($fullPath)) {
            $file = "assets/logo/{$prefix}.jpg";
            $fullPath = public_path($file);
        }
        $version = is_file($fullPath) ? filemtime($fullPath) : '4';

        return asset($file) . '?v=' . $version;
    }

    /**
     * Get customized success/checkmark character illustration URL (Pria vs Perempuan).
     */
    public function getSuccessAvatarUrlAttribute(): string
    {
        return $this->getSuccessAvatarUrlForTheme('teal');
    }

    /**
     * Get theme-aware success/checkmark character illustration URL.
     */
    public function getSuccessAvatarUrlForTheme(?string $theme = 'teal'): string
    {
        $validThemes = ['teal', 'blue', 'indigo', 'emerald', 'purple', 'rose', 'amber', 'pink'];
        $accent = in_array($theme, $validThemes, true) ? $theme : 'teal';
        $prefix = $this->isPerempuan() ? 'ceklisperempuan' : 'ceklispria';

        $file = "assets/logo/{$prefix}-{$accent}.png";
        $fullPath = public_path($file);
        if (! is_file($fullPath)) {
            $file = "assets/logo/{$prefix}.png";
            $fullPath = public_path($file);
        }
        $version = is_file($fullPath) ? filemtime($fullPath) : '1';

        return asset($file) . '?v=' . $version;
    }

    /**
     * Determine if user is female.
     */
    public function isPerempuan(): bool
    {
        $gender = strtolower(trim((string) ($this->gender ?? '')));
        if (in_array($gender, ['perempuan', 'female', 'wanita', 'p'], true)) {
            return true;
        }
        if (in_array($gender, ['pria', 'male', 'laki-laki', 'l'], true)) {
            return false;
        }

        $lowerName = strtolower($this->name ?? '');
        $femaleKeywords = ['siti', 'nurhaliza', 'melani', 'putri', 'amelia', 'ida', 'dewi', 'kartika', 'karmeliandita', 'wati', 'lestari', 'ani', 'sarah', 'dina', 'fitri'];
        foreach ($femaleKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lowerName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Daftar 5 Pesan Cepat default untuk chat koordinasi tiket gangguan.
     *
     * @return array<int, array{text: string, category: string}>
     */
    public static function defaultQuickReplies(): array
    {
        return [
            ['text' => 'Menuju ke lokasi gangguan', 'category' => 'TRACING'],
            ['text' => 'Investigasi titik & pengukuran OTDR', 'category' => 'OTDR'],
            ['text' => 'Kabel putus / bending core pada span', 'category' => 'INVESTIGASI'],
            ['text' => 'Proses splicing core di Joint Closure', 'category' => 'JOINTING'],
            ['text' => 'Redaman normal & Link UP kembali', 'category' => 'LINK_UP'],
        ];
    }

    /**
     * Pastikan kolom quick_replies tersedia pada tabel user_notification_preferences.
     */
    public static function ensureQuickRepliesColumn(): bool
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('user_notification_preferences')) {
                return false;
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('user_notification_preferences', 'quick_replies')) {
                \Illuminate\Support\Facades\Schema::table('user_notification_preferences', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->json('quick_replies')->nullable();
                });
            }
            return true;
        } catch (\Throwable) {
            return \Illuminate\Support\Facades\Schema::hasColumn('user_notification_preferences', 'quick_replies');
        }
    }

    /**
     * Ambil daftar Pesan Cepat milik pengguna (kustomisasi sendiri atau default 5 pesan).
     *
     * @return array<int, array{text: string, category: string}>
     */
    public function getQuickReplies(): array
    {
        try {
            if (self::ensureQuickRepliesColumn()) {
                $pref = UserNotificationPreference::query()->where('user_id', $this->id)->first();
                if ($pref && is_array($pref->quick_replies) && count($pref->quick_replies) > 0) {
                    return array_values(array_map(function ($item) {
                        if (is_string($item)) {
                            return ['text' => trim($item), 'category' => 'LAIN'];
                        }
                        return [
                            'text' => trim((string) ($item['text'] ?? '')),
                            'category' => strtoupper(trim((string) ($item['category'] ?? 'LAIN'))) ?: 'LAIN',
                        ];
                    }, array_filter($pref->quick_replies, function ($item) {
                        $text = is_string($item) ? trim($item) : trim((string) ($item['text'] ?? ''));
                        return $text !== '';
                    })));
                }
            }
        } catch (\Throwable) {}

        $cached = \Illuminate\Support\Facades\Cache::get('user_quick_replies_' . $this->id);
        if (is_array($cached) && count($cached) > 0) {
            return $cached;
        }

        return self::defaultQuickReplies();
    }

    /**
     * Simpan daftar Pesan Cepat kustom milik pengguna (atau null untuk reset ke default).
     *
     * @param  array<int, mixed>|null  $items
     * @return array<int, array{text: string, category: string}>
     */
    public function saveQuickReplies(?array $items): array
    {
        $normalized = null;
        if (is_array($items)) {
            $defaults = self::defaultQuickReplies();
            $clean = [];
            foreach (array_slice($items, 0, 8) as $idx => $item) {
                $text = is_string($item) ? trim($item) : trim((string) ($item['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $text = mb_substr($text, 0, 120);
                $defaultCat = $defaults[$idx]['category'] ?? 'LAIN';
                $cat = is_array($item) && ! empty($item['category'])
                    ? strtoupper(trim((string) $item['category']))
                    : $defaultCat;
                $clean[] = ['text' => $text, 'category' => $cat];
            }
            if (count($clean) > 0) {
                $normalized = $clean;
            }
        }

        try {
            if (self::ensureQuickRepliesColumn()) {
                UserNotificationPreference::query()->updateOrCreate(
                    ['user_id' => $this->id],
                    ['quick_replies' => $normalized]
                );
            }
        } catch (\Throwable) {}

        if ($normalized === null) {
            \Illuminate\Support\Facades\Cache::forget('user_quick_replies_' . $this->id);
        } else {
            \Illuminate\Support\Facades\Cache::forever('user_quick_replies_' . $this->id, $normalized);
        }

        return $this->getQuickReplies();
    }
}
