<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tiket extends Model
{
    use SoftDeletes;

    protected $table = 'tiket';

    protected $fillable = [
        'no_tiket',
        'kategori_tiket',
        'priority',
        'status',
        'sla_status',
        'sla_target_minutes',
        'total_stop_clock_minutes',
        'net_mttr_minutes',
        'backbone_segment',
        'status_link_impact',
        'wilayah',
        'tipe_penanganan',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_odp',
        'customer_olt',
        'customer_snapshot',
        'is_ims_validated',
        'problem_temuan',
        'action_taken',
        'redaman_sebelum',
        'redaman_sesudah',
        'has_link_up',
        'closing_awal_by',
        'closing_awal_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'tanggal_open',
        'first_response_at',
        'pending_verification_at',
        'tanggal_close',
        'reopened_count',
        'reopened_at',
        'reopened_reason',
        'cancelled_at',
        'cancelled_reason',
        'parent_ticket_id',
        'created_by',
    ];

    protected $casts = [
        'customer_snapshot' => 'array',
        'is_ims_validated' => 'boolean',
        'has_link_up' => 'boolean',
        'sla_target_minutes' => 'integer',
        'total_stop_clock_minutes' => 'integer',
        'net_mttr_minutes' => 'integer',
        'reopened_count' => 'integer',
        'redaman_sebelum' => 'decimal:2',
        'redaman_sesudah' => 'decimal:2',
        'tanggal_open' => 'datetime',
        'first_response_at' => 'datetime',
        'pending_verification_at' => 'datetime',
        'tanggal_close' => 'datetime',
        'reopened_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'closing_awal_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closingAwalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closing_awal_by');
    }

    public function verifierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function parentTicket(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_ticket_id');
    }

    public function childTickets(): HasMany
    {
        return $this->hasMany(self::class, 'parent_ticket_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_assignees', 'tiket_id', 'user_id')
            ->withPivot(['id', 'role_in_team', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function ticketAssignees(): HasMany
    {
        return $this->hasMany(TicketAssignee::class, 'tiket_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class, 'tiket_id')->orderBy('created_at', 'asc');
    }

    public function chronologies(): HasMany
    {
        return $this->hasMany(TicketChronology::class, 'tiket_id')->orderBy('created_at', 'asc');
    }

    public function breaches(): HasMany
    {
        return $this->hasMany(IntervalBreach::class, 'tiket_id');
    }

    public function stopClocks(): HasMany
    {
        return $this->hasMany(StopClock::class, 'tiket_id')->orderBy('started_at', 'asc');
    }

    public function activeStopClock(): HasOne
    {
        return $this->hasOne(StopClock::class, 'tiket_id')->whereNull('ended_at');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(TicketHandover::class, 'tiket_id')->orderBy('created_at', 'asc');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TicketMaterial::class, 'tiket_id');
    }

    public function repairPoints(): HasMany
    {
        return $this->hasMany(RepairPoint::class, 'tiket_id');
    }

    public function jointClosures(): HasMany
    {
        return $this->hasMany(JointClosure::class, 'tiket_id');
    }

    public function coreManeuvers(): HasMany
    {
        return $this->hasMany(CoreManeuver::class, 'tiket_id');
    }

    /**
     * Compute current net elapsed minutes up to a reference time (or now).
     */
    public function currentNetMinutes(?Carbon $referenceTime = null): int
    {
        $ref = $referenceTime ?? now();
        $start = $this->tanggal_open ?? $this->created_at;
        if (! $start) {
            return 0;
        }

        $gross = (int) $start->diffInMinutes($ref);
        $stopClock = $this->total_stop_clock_minutes;

        // If currently in active stop clock, subtract active ongoing portion too
        $activeStop = $this->activeStopClock;
        if ($activeStop && $activeStop->started_at) {
            $ongoingStop = (int) $activeStop->started_at->diffInMinutes($ref);
            $stopClock += max(0, $ongoingStop);
        }

        return max(0, $gross - $stopClock);
    }

    /**
     * Check if currently overdue beyond SLA target (§7.7).
     */
    public function isOverdue(?Carbon $referenceTime = null): bool
    {
        if ($this->sla_status === 'LEBIH') {
            return true;
        }

        if (in_array($this->status, ['CLOSE', 'CANCELLED'], true)) {
            return $this->sla_status === 'LEBIH';
        }

        return $this->currentNetMinutes($referenceTime) > $this->sla_target_minutes;
    }

    /**
     * Mobile card stripe indicator color (§11, AC-08).
     * Merah: LEBIH / OVERDUE
     * Biru: OPEN
     * Kuning: PROSES / PENDING_VERIFIKASI
     * Hijau: CLOSE
     * Abu-abu: CANCELLED
     */
    public function stripeColor(): string
    {
        if ($this->status === 'CANCELLED') {
            return 'gray';
        }

        if ($this->isOverdue()) {
            return 'red';
        }

        return match ($this->status) {
            'OPEN' => 'blue',
            'PROSES', 'PENDING_VERIFIKASI' => 'yellow',
            'CLOSE' => 'green',
            default => 'blue',
        };
    }
}
