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
     * Hitung nominal uang lembur murni (sebelum pembulatan) berdasarkan jenis:
     * 1. Lembur biasa = Rp 5.000 / jam
     * 2. Piket Hari Libur = Rp 3.900 / jam
     * 3. Piket Hari Kerja = Rp 15.000 FLAT / Tetap (tidak per jam)
     */
    public function getRawNominalAttribute(): float
    {
        $jenis = strtolower(trim((string) $this->jenis_lembur));
        $durasiMenit = (int) ($this->durasi_menit ?? 0);
        $jam = $durasiMenit / 60.0;

        // 3. Piket Hari Kerja (Flat Rp 15.000)
        if (str_contains($jenis, 'piket') && str_contains($jenis, 'kerja')) {
            return 15000.0;
        }

        // 2. Piket Hari Libur (Rp 3.900 / jam)
        if (str_contains($jenis, 'piket') && (str_contains($jenis, 'libur') || str_contains($jenis, 'minggu') || str_contains($jenis, 'merah'))) {
            return $jam * 3900.0;
        }

        // 1. Lembur Biasa (Rp 5.000 / jam) - default
        return $jam * 5000.0;
    }

    /**
     * Pembulatan untuk Dashboard HRD / Tampilan Web:
     * Dibulatkan ke kelipatan Rp 100 terdekat: round($uang / 100) * 100
     */
    public function getNominalAttribute(): int
    {
        return (int) (round($this->raw_nominal / 100) * 100);
    }

    /**
     * Pembulatan untuk Export Laporan (Excel / CSV / PDF):
     * Dibulatkan ke bawah ke kelipatan Rp 1.000: floor($uang / 1000) * 1000
     */
    public function getNominalExportAttribute(): int
    {
        return (int) (floor($this->raw_nominal / 1000) * 1000);
    }
}

