<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengajuan extends Model
{
    use SoftDeletes;

    protected $table = 'pengajuan';

    protected $fillable = [
        'nomor_pengajuan',
        'jenis',
        'user_id',
        'atasan_id',
        'judul',
        'keperluan',
        'nominal_total',
        'status',
        'requires_executive_approval',
        'current_approver_stage',
        'bukti_transfer_path',
        'transfer_at',
        'transfer_by',
        'nominal_realisasi',
        'selisih_realisasi',
        'bukti_realisasi_path',
        'realisasi_at',
        'realisasi_notes',
        'realisasi_deadline',
    ];

    protected $casts = [
        'nominal_total' => 'decimal:2',
        'nominal_realisasi' => 'decimal:2',
        'selisih_realisasi' => 'decimal:2',
        'requires_executive_approval' => 'boolean',
        'transfer_at' => 'datetime',
        'realisasi_at' => 'datetime',
        'realisasi_deadline' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function transferUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transfer_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PengajuanDanaDetail::class, 'pengajuan_id');
    }

    public function getTotalNominalAttribute(): float
    {
        return (float) ($this->nominal_total ?? 0.0);
    }

    public function getRealizationAmountAttribute(): ?float
    {
        return $this->nominal_realisasi !== null ? (float) $this->nominal_realisasi : null;
    }

    public function getRealizationProofPathAttribute(): ?string
    {
        return $this->bukti_realisasi_path;
    }

    public function getRealizedAtAttribute(): ?\Carbon\Carbon
    {
        return $this->realisasi_at;
    }

    public function getRealizationNotesAttribute(): ?string
    {
        return $this->realisasi_notes;
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PengajuanApproval::class, 'pengajuan_id')->orderBy('created_at', 'asc');
    }
}

