<?php

namespace App\Modules\Ticketing\Services;

use App\Models\TicketSequence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TicketSequenceService
{
    /**
     * Generate atomic, sequential ticket number (§7.1, AC-04):
     * BDG-YYYYMMDD-XXX (Backbone) or BRD-YYYYMMDD-XXX (Broadband)
     * Format 3 digits zero-padded, auto-expanded if > 999.
     */
    public function generate(string $prefix, ?string $date = null): string
    {
        $prefix = strtoupper(trim($prefix));
        if (! in_array($prefix, ['BDG', 'BRD'], true)) {
            $prefix = 'BRD';
        }

        $dateStr = $date ? Carbon::parse($date, 'Asia/Jakarta')->format('Y-m-d') : Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $dateCompact = Carbon::parse($dateStr)->format('Ymd');

        return DB::transaction(function () use ($prefix, $dateStr, $dateCompact): string {
            /** @var TicketSequence $seq */
            $seq = TicketSequence::query()
                ->where('prefix', $prefix)
                ->whereDate('date', $dateStr)
                ->lockForUpdate()
                ->first();

            if (! $seq) {
                $seq = TicketSequence::query()->create([
                    'prefix' => $prefix,
                    'date' => $dateStr,
                    'last_number' => 1,
                ]);
                $num = 1;
            } else {
                $seq->increment('last_number');
                $num = (int) $seq->last_number;
            }

            $numPadded = str_pad((string) $num, 3, '0', STR_PAD_LEFT);

            return "{$prefix}-{$dateCompact}-{$numPadded}";
        });
    }
}
