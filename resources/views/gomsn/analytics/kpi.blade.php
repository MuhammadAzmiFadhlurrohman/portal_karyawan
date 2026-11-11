<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-heading font-bold text-[#1E3A8A] tracking-tight">
                    Analitik MTTR &amp; KPI Teknisi Lapangan
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Evaluasi performa penyelesaian gangguan: Formula KPI = 0.45 &times; SLA + 0.35 &times; Interval + 0.20 &times; Respon (§7.7).
                </p>
            </div>

            <!-- Date Range Filter -->
            <form method="GET" action="{{ route('gomsn.analytics.kpi') }}" class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-xs">
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="text-xs rounded-lg border-slate-300 py-1.5" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="text-xs rounded-lg border-slate-300 py-1.5" />
                <button type="submit" class="btn-primary !px-3 !py-1.5 text-xs">
                    Filter
                </button>
            </form>
        </div>

        <!-- 4 Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Tiket Closed</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-heading font-bold text-[#1E3A8A]">{{ $totalClosed }}</span>
                    <span class="text-xs text-slate-500">tiket selesai</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Rata-Rata MTTR Bersih</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-heading font-bold text-slate-800">{{ $avgMttr }}</span>
                    <span class="text-xs text-slate-500">menit ({{ round($avgMttr / 60, 1) }} jam)</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">SLA Compliance Rate</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-heading font-bold {{ $slaComplianceRate >= 95 ? 'text-emerald-600' : ($slaComplianceRate >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ $slaComplianceRate }}%
                    </span>
                    <span class="text-xs text-slate-500">tepat waktu</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Standar Minimal KPI</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-heading font-bold text-indigo-700">85.0</span>
                    <span class="text-xs text-slate-500">skor kelulusan</span>
                </div>
            </div>
        </div>

        <!-- Leaderboard Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-heading font-bold text-base text-[#1E3A8A]">
                    Peringkat KPI Teknisi Lapangan
                </h3>
                <span class="text-xs text-slate-400">
                    *Teknisi dengan penanganan &lt; 5 tiket dikategorikan 'DATA TIDAK CUKUP'
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">Rank</th>
                            <th class="py-3 px-4">Teknisi</th>
                            <th class="py-3 px-4 text-center">Tiket Ditangani</th>
                            <th class="py-3 px-4 text-right">Skor SLA (45%)</th>
                            <th class="py-3 px-4 text-right">Skor Interval 30m (35%)</th>
                            <th class="py-3 px-4 text-right">Skor Respon (20%)</th>
                            <th class="py-3 px-4 text-right">Skor Total KPI</th>
                            <th class="py-3 px-4 text-center">Status Kelayakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kpiList as $index => $row)
                        @php
                            $tech = $row['user'];
                            $kpi = $row['data'];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 text-center font-bold text-slate-500">
                                #{{ $index + 1 }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800">{{ $tech->name }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $tech->employee_code }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-semibold text-slate-700">
                                {{ $kpi['total_tickets'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                {{ $kpi['sla_score'] }}%
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                {{ $kpi['interval_score'] }}%
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                {{ $kpi['response_score'] }}%
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-sm">
                                @if ($kpi['score'] !== null)
                                    <span class="{{ $kpi['score'] >= 85 ? 'text-emerald-700' : ($kpi['score'] >= 70 ? 'text-amber-700' : 'text-red-700') }}">
                                        {{ number_format($kpi['score'], 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-normal italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($kpi['status'] === 'DATA_TIDAK_CUKUP')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        DATA TIDAK CUKUP (&lt;5)
                                    </span>
                                @elseif ($kpi['score'] >= 85)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ MEMENUHI TARGET
                                    </span>
                                @elseif ($kpi['score'] >= 70)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        ⚠️ PERLU PENINGKATAN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                        ✕ DI BAWAH STANDAR
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Tidak ada data teknisi yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

