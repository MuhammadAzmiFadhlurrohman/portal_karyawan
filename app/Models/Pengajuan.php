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

    public function getJenisAttribute($value): string
    {
        $val = strtoupper($value ?? 'DANA');
        if ($val === 'BARANG') {
            return 'STOK';
        }
        return in_array($val, ['DANA', 'BELI', 'STOK', 'PERMINTAAN'], true) ? $val : 'DANA';
    }

    public function setJenisAttribute($value): void
    {
        $val = strtoupper($value ?? 'DANA');
        if ($val === 'BARANG') {
            $val = 'STOK';
        }
        $this->attributes['jenis'] = in_array($val, ['DANA', 'BELI', 'STOK', 'PERMINTAAN'], true) ? $val : 'DANA';
    }

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

    public function getBuktiTransferUrlAttribute(): ?string
    {
        if (empty($this->bukti_transfer_path)) {
            return null;
        }

        try {
            return route('gomsn.pengajuan.file', ['pengajuan' => $this->id, 'type' => 'transfer']);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->bukti_transfer_path);
        }
    }

    public function getBuktiRealisasiUrlAttribute(): ?string
    {
        if (empty($this->bukti_realisasi_path)) {
            return null;
        }

        try {
            return route('gomsn.pengajuan.file', ['pengajuan' => $this->id, 'type' => 'realization']);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->bukti_realisasi_path);
        }
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

    /**
     * Return structured items array for detail modal with auto-fallback for legacy unpopulated rows.
     */
    public function getModalDetails(): array
    {
        if ($this->relationLoaded('details') && $this->details->isNotEmpty()) {
            return $this->details->map(function ($d) {
                $isStok = in_array(strtoupper($this->jenis), ['STOK', 'PERMINTAAN'], true);
                return [
                    'name' => $d->item_name ?: ($d->material?->nama ?? '-'),
                    'qty' => (int) ($d->qty ?: 1),
                    'satuan' => $d->satuan ?: ($d->material?->satuan ?? self::guessSatuan($d->item_name)),
                    'unitPrice' => $isStok ? 'Rp 0' : 'Rp ' . number_format((float) $d->estimated_unit_price, 0, ',', '.'),
                    'subtotal' => $isStok ? 'Rp 0' : 'Rp ' . number_format((float) $d->subtotal, 0, ',', '.'),
                ];
            })->values()->all();
        }

        // Fallback: parse from judul if details relationship is empty
        return self::parseLegacyItems(
            $this->judul ?? '',
            $this->keperluan ?? '',
            $this->jenis ?? 'STOK',
            (float) ($this->nominal_total ?? 0.0)
        );
    }

    public static function guessSatuan(?string $name): string
    {
        if (! $name) return 'pcs';
        if (preg_match('/kabel|cable|dropcore|fiber/i', $name)) return 'meter';
        if (preg_match('/\b(box|dus|pak|pack)\b/i', $name)) return 'box';
        if (preg_match('/\b(roll|rol)\b/i', $name)) return 'roll';
        if (preg_match('/\b(liter|ltr)\b/i', $name)) return 'liter';
        return 'pcs';
    }

    public static function parseLegacyItems(string $judul, string $keperluan = '', string $jenis = 'STOK', float $nominal = 0.0): array
    {
        $text = trim($judul);
        if (empty($text) || $text === '-') {
            $text = trim($keperluan);
        }
        if (empty($text)) {
            return [];
        }

        $rawParts = array_map('trim', explode(',', $text));
        $buffer = '';
        $reconstructed = [];

        foreach ($rawParts as $part) {
            if ($buffer === '') {
                $buffer = $part;
            } else {
                $buffer .= ', ' . $part;
            }
            if (preg_match('/\([0-9]+\)$/', trim($buffer))) {
                $reconstructed[] = trim($buffer);
                $buffer = '';
            }
        }
        if ($buffer !== '') {
            $reconstructed[] = trim($buffer);
        }

        $items = [];
        $isStok = in_array(strtoupper($jenis), ['STOK', 'PERMINTAAN', 'BARANG'], true);

        foreach ($reconstructed as $entry) {
            if (empty($entry)) continue;

            if (preg_match('/^(.*?)\s*\(([0-9]+)\)$/', $entry, $matches)) {
                $name = trim($matches[1]);
                $qty = (int) $matches[2];
            } else {
                $name = $entry;
                $qty = 1;
            }

            $satuan = self::guessSatuan($name);
            if ($satuan === 'meter' && $qty <= 5) {
                $satuan = 'pcs';
            }

            $unitPrice = $isStok ? 0.0 : ($nominal > 0 && count($reconstructed) === 1 ? $nominal : 0.0);
            $subtotal = $isStok ? 0.0 : ($nominal > 0 && count($reconstructed) === 1 ? $nominal : 0.0);

            $items[] = [
                'name' => $name,
                'qty' => $qty > 0 ? $qty : 1,
                'satuan' => $satuan,
                'unitPrice' => $isStok ? 'Rp 0' : 'Rp ' . number_format($unitPrice, 0, ',', '.'),
                'subtotal' => $isStok ? 'Rp 0' : 'Rp ' . number_format($subtotal, 0, ',', '.'),
            ];
        }

        return $items;
    }
}

