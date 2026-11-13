<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lembur extends Model
{
    use SoftDeletes;

    protected $table = 'lembur';

    protected $fillable = [
        'nomor_lembur',
        'jenis_lembur',
        'user_id',
        'atasan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'alasan',
        'status',
        'approver_id',
        'approved_at',
        'rejection_reason',
        'is_reconciled',
        'reconciliation_flag',
        'actual_clockout_at',
        'actual_diff_minutes',
        'paid_at',
        'paid_by',
        'payment_reference',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
        'durasi_menit' => 'integer',
        'is_reconciled' => 'boolean',
        'actual_clockout_at' => 'datetime',
        'actual_diff_minutes' => 'integer',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Hitung nominal uang lembur berdasarkan jenis lembur:
     * 1. Lembur biasa = Rp 5.000 / jam
     * 2. Piket Hari Libur = Rp 3.900 / jam
     * 3. Piket Hari Kerja = Rp 15.000 FLAT / Tetap (tidak per jam)
     */
    public function getNominalAttribute(): int
    {
        $jenis = strtolower(trim((string) $this->jenis_lembur));
        $durasiMenit = (int) ($this->durasi_menit ?? 0);
        $jam = $durasiMenit / 60;

        // 3. Piket Hari Kerja (Flat Rp 15.000)
        if (str_contains($jenis, 'piket') && str_contains($jenis, 'kerja')) {
            return 15000;
        }

        // 2. Piket Hari Libur (Rp 3.900 / jam)
        if (str_contains($jenis, 'piket') && (str_contains($jenis, 'libur') || str_contains($jenis, 'minggu') || str_contains($jenis, 'merah'))) {
            return (int) round($jam * 3900);
        }

        // 1. Lembur Biasa (Rp 5.000 / jam) - default
        return (int) round($jam * 5000);
    }
}

