<x-app-layout>
    @php
        $onTimeCount = $onTimeCount ?? 0;
        $avgMttrInt = (int) round((float) ($avgMttr ?? 0));
        $mttrHours = intdiv($avgMttrInt, 60);
        $mttrRemMins = $avgMttrInt % 60;
        $mttrReadable = $avgMttrInt > 0
            ? ($mttrHours > 0 ? "{$mttrHours} jam {$mttrRemMins} mnt" : "{$mttrRemMins} menit")
            : '0 menit';

        $totalTechnicians = count($kpiList);
        $metTargetCount = 0;
        $needsImprovementCount = 0;
        $belowStandardCount = 0;
        $insufficientCount = 0;
        $filterableRows = [];

        foreach ($kpiList as $idx => $row) {
            $u = $row['user'];
            $k = $row['data'];

            if (($k['status'] ?? '') === 'DATA_TIDAK_CUKUP' || $k['score'] === null) {
                $statusKey = 'insufficient';
                $insufficientCount++;
            } elseif ($k['score'] >= 85) {
                $statusKey = 'met';
                $metTargetCount++;
            } elseif ($k['score'] >= 70) {
                $statusKey = 'warn';
                $needsImprovementCount++;
            } else {
                $statusKey = 'low';
                $belowStandardCount++;
            }

            $filterableRows[] = [
                'idx' => $idx,
                'q' => mb_strtolower(trim(($u->name ?? '') . ' ' . ($u->employee_code ?? '') . ' ' . ($u->division ?? ''))),
                'status' => $statusKey,
            ];
        }

        $qualifiedCount = $totalTechnicians - $insufficientCount;

        $nowDate = \Carbon\Carbon::now();
        $presets = [
            'this_month' => [
                'label' => 'Bulan Ini',
                'start' => $nowDate->copy()->startOfMonth()->toDateString(),
                'end' => $nowDate->copy()->endOfMonth()->toDateString(),
            ],
            'last_month' => [
                'label' => 'Bulan Lalu',
                'start' => $nowDate->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                'end' => $nowDate->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            'last_30_days' => [
                'label' => '30 Hari Terakhir',
                'start' => $nowDate->copy()->subDays(29)->toDateString(),
                'end' => $nowDate->copy()->toDateString(),
            ],
        ];
        $activeStartStr = $startDate->toDateString();
        $activeEndStr = $endDate->toDateString();

        $slaBarColor = $slaComplianceRate >= 95
            ? 'bg-emerald-500'
            : ($slaComplianceRate >= 80 ? 'bg-amber-500' : 'bg-rose-500');
        $slaTextColor = $slaComplianceRate >= 95
            ? 'text-emerald-600'
            : ($slaComplianceRate >= 80 ? 'text-amber-600' : 'text-rose-600');
        $slaIconBg = $slaComplianceRate >= 95
            ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
            : ($slaComplianceRate >= 80 ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-rose-50 text-rose-600 border-rose-100');
    @endphp

    <div class="w-full space-y-5">
        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching GoMSN Theme) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">

            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none" aria-hidden="true">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#kpiWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="kpiWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Analytics Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Analitik MTTR &amp; KPI Teknisi Lapangan
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/85 mt-0.5 leading-snug">
                        Evaluasi performa penyelesaian gangguan lapangan secara real-time berdasarkan SLA, interval 30 menit, dan respon awal.
                    </p>
                </div>
            </div>

            <!-- Right: Active Period Glass Badge -->
            <div class="relative z-10 flex items-center gap-2 shrink-0 self-start lg:self-auto bg-white/10 border border-white/20 backdrop-blur-xs rounded-xl px-3.5 py-2 text-xs text-white">
                <svg class="w-4 h-4 text-cyan-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-teal-100/80 font-medium">Periode:</span>
                <span class="font-semibold text-white whitespace-nowrap">
                    {{ $startDate->translatedFormat('d M Y') }} &ndash; {{ $endDate->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Date Filter & Quick-Preset Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-5">
            <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
                <form method="GET" action="{{ route('gomsn.analytics.kpi') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-end gap-3 flex-1">
                    <div class="lg:w-48">
                        <label for="kpi-start-date" class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5">
                            Dari Tanggal
                        </label>
                        <input id="kpi-start-date"
                               type="date"
                               name="start_date"
                               value="{{ $activeStartStr }}"
                               class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 font-medium text-slate-800 shadow-2xs" />
                    </div>

                    <div class="lg:w-48">
                        <label for="kpi-end-date" class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5">
                            Sampai Tanggal
                        </label>
                        <input id="kpi-end-date"
                               type="date"
                               name="end_date"
                               value="{{ $activeEndStr }}"
                               class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 font-medium text-slate-800 shadow-2xs" />
                    </div>

                    <div class="sm:col-span-2 lg:w-auto">
                        <button type="submit"
                                class="w-full lg:w-auto py-2 px-5 rounded-xl text-white font-semibold text-xs inline-flex items-center justify-center gap-2 shadow-xs hover:opacity-95 active:scale-[0.98] transition cursor-pointer whitespace-nowrap"
                                style="background: linear-gradient(135deg, #093c40 0%, #0d5f66 100%) !important;">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </form>

                <!-- Quick Presets -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1 xl:pt-0">
                    <span class="text-[11px] font-semibold text-slate-400 mr-1">Pintasan:</span>
                    @foreach ($presets as $preset)
                        @php
                            $isPresetActive = ($activeStartStr === $preset['start'] && $activeEndStr === $preset['end']);
                        @endphp
                        <a href="{{ route('gomsn.analytics.kpi', ['start_date' => $preset['start'], 'end_date' => $preset['end']]) }}"
                           class="px-3 py-1.5 rounded-xl text-xs border transition whitespace-nowrap {{ $isPresetActive ? 'bg-teal-50 text-[#155E63] border-teal-300 font-bold shadow-2xs' : 'bg-slate-50 text-slate-600 border-slate-200 font-medium hover:bg-teal-50/50 hover:border-teal-200 hover:text-[#155E63]' }}">
                            {{ $preset['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 4 Summary Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Card 1: Tiket Closed -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:border-teal-200 transition-colors flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block truncate">Tiket Closed</span>
                        <div class="mt-1.5 flex items-baseline gap-1.5 flex-wrap">
                            <span class="text-2xl sm:text-3xl font-heading font-extrabold text-[#093c40] tabular-nums">
                                {{ number_format($totalClosed, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 font-medium">tiket selesai</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-teal-50 text-[#155E63] border border-teal-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Cakupan tim</span>
                    <span class="font-semibold text-slate-700">{{ $totalTechnicians }} teknisi terdata</span>
                </div>
            </div>

            <!-- Card 2: Rata-Rata MTTR Bersih -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:border-teal-200 transition-colors flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block truncate">Rata-Rata MTTR Bersih</span>
                        <div class="mt-1.5 flex items-baseline gap-1.5 flex-wrap">
                            <span class="text-2xl sm:text-3xl font-heading font-extrabold text-slate-800 tabular-nums">
                                {{ number_format($avgMttrInt, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 font-medium">menit</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-cyan-50 text-cyan-700 border border-cyan-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Konversi durasi</span>
                    <span class="font-semibold text-slate-700">Setara {{ $mttrReadable }}</span>
                </div>
            </div>

            <!-- Card 3: SLA Compliance Rate -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:border-teal-200 transition-colors flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block truncate">SLA Compliance Rate</span>
                        <div class="mt-1.5 flex items-baseline gap-1.5 flex-wrap">
                            <span class="text-2xl sm:text-3xl font-heading font-extrabold tabular-nums {{ $slaTextColor }}">
                                {{ $slaComplianceRate }}%
                            </span>
                            <span class="text-xs text-slate-500 font-medium">tepat waktu</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl border flex items-center justify-center shrink-0 {{ $slaIconBg }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                    </div>
                </div>
                <div class="space-y-1.5 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Tepat SLA</span>
                        <span class="font-semibold text-slate-700 tabular-nums">{{ $onTimeCount }} dari {{ $totalClosed }} tiket</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $slaBarColor }}" style="width: {{ min(100, max(0, (float) $slaComplianceRate)) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Standar Minimal KPI -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:border-teal-200 transition-colors flex flex-col justify-between gap-3">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block truncate">Standar Minimal KPI</span>
                        <div class="mt-1.5 flex items-baseline gap-1.5 flex-wrap">
                            <span class="text-2xl sm:text-3xl font-heading font-extrabold text-[#0D9488] tabular-nums">
                                85.0
                            </span>
                            <span class="text-xs text-slate-500 font-medium">skor kelulusan</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                        </svg>
                    </div>
                </div>
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Teknisi memenuhi</span>
                    @if ($qualifiedCount > 0)
                        <span class="font-semibold text-emerald-700 tabular-nums">{{ $metTargetCount }} dari {{ $qualifiedCount }} memenuhi target</span>
                    @else
                        <span class="font-medium text-slate-500">Belum ada teknisi &ge; 5 tiket</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Compact Formula & Rules Info Strip -->
        <div class="rounded-2xl bg-gradient-to-r from-teal-50/80 via-white to-cyan-50/60 border border-teal-200/70 px-4 py-3 flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 font-bold text-[#093c40]">
                    <svg class="w-4 h-4 text-[#0D9488] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Formula KPI (§7.7):</span>
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-100/80 text-[#155E63] border border-teal-200">
                    SLA 45%
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100/80 text-cyan-800 border border-cyan-200">
                    Interval 30m 35%
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200">
                    Respon 20%
                </span>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-600 leading-snug">
                Minimal <strong class="text-slate-800">5 tiket closed</strong> untuk kalkulasi skor penuh; di bawah 5 tiket berstatus <strong class="text-slate-700">Data Tidak Cukup</strong>.
            </p>
        </div>

        <!-- Leaderboard Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
             x-data="{
                 search: '',
                 statusFilter: 'all',
                 rows: @js($filterableRows),
                 matches(idx) {
                     const r = this.rows[idx];
                     if (!r) return false;
                     const okStatus = this.statusFilter === 'all' || r.status === this.statusFilter;
                     const q = this.search.trim().toLowerCase();
                     const okSearch = q === '' || r.q.includes(q);
                     return okStatus && okSearch;
                 },
                 get visibleCount() {
                     const q = this.search.trim().toLowerCase();
                     return this.rows.filter(r =>
                         (this.statusFilter === 'all' || r.status === this.statusFilter) &&
                         (q === '' || r.q.includes(q))
                     ).length;
                 }
             }">

            <!-- Header & Search / Filter Toolbar -->
            <div class="p-4 sm:p-5 border-b border-slate-100 space-y-3.5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-[#18C4C4] to-[#093c40] shrink-0"></span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-heading font-bold text-base text-[#093c40] tracking-tight">
                                    Peringkat KPI Teknisi Lapangan
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-[#155E63] border border-teal-200/80 tabular-nums"
                                      x-text="visibleCount + ' dari ' + rows.length + ' Teknisi'">
                                    {{ $totalTechnicians }} dari {{ $totalTechnicians }} Teknisi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Box -->
                    <div class="relative w-full md:w-72">
                        <label for="kpi-tech-search" class="sr-only">Cari nama atau kode teknisi</label>
                        <input id="kpi-tech-search"
                               type="text"
                               x-model="search"
                               placeholder="Cari nama atau kode teknisi..."
                               class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 pl-9 pr-8 py-2 text-slate-800 placeholder-slate-400 transition outline-none">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button type="button"
                                x-cloak
                                x-show="search !== ''"
                                @click="search = ''"
                                aria-label="Hapus pencarian"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                    <button type="button"
                            @click="statusFilter = 'all'"
                            :class="statusFilter === 'all' ? 'bg-[#093c40] text-white border-[#093c40] shadow-2xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                            class="px-3 py-1.5 rounded-xl border text-[11px] font-semibold inline-flex items-center gap-1.5 whitespace-nowrap transition cursor-pointer shrink-0">
                        <span>Semua</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-700'">{{ $totalTechnicians }}</span>
                    </button>

                    <button type="button"
                            @click="statusFilter = 'met'"
                            :class="statusFilter === 'met' ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs' : 'bg-emerald-50/70 text-emerald-800 border-emerald-200 hover:bg-emerald-100/70'"
                            class="px-3 py-1.5 rounded-xl border text-[11px] font-semibold inline-flex items-center gap-1.5 whitespace-nowrap transition cursor-pointer shrink-0">
                        <span>Memenuhi Target</span>
                        <span class="px-1.5 rounded-full text-[10px] font-bold"
                              :class="statusFilter === 'met' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">{{ $metTargetCount }}</span>
                    </button>

                    <button type="button"
                            @click="statusFilter = 'warn'"
                            :class="statusFilter === 'warn' ? 'bg-amber-600 text-white border-amber-600 shadow-2xs' : 'bg-amber-50/70 text-amber-800 border-amber-200 hover:bg-amber-100/70'"
                            class="px-3 py-1.5 rounded-xl border text-[11px] font-semibold inline-flex items-center gap-1.5 whitespace-nowrap transition cursor-pointer shrink-0">
                        <span>Perlu Peningkatan</span>
                        <span class="px-1.5 rounded-full text-[10px] font-bold"
                              :class="statusFilter === 'warn' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">{{ $needsImprovementCount }}</span>
                    </button>

                    <button type="button"
                            @click="statusFilter = 'low'"
                            :class="statusFilter === 'low' ? 'bg-rose-600 text-white border-rose-600 shadow-2xs' : 'bg-rose-50/70 text-rose-800 border-rose-200 hover:bg-rose-100/70'"
                            class="px-3 py-1.5 rounded-xl border text-[11px] font-semibold inline-flex items-center gap-1.5 whitespace-nowrap transition cursor-pointer shrink-0">
                        <span>Di Bawah Standar</span>
                        <span class="px-1.5 rounded-full text-[10px] font-bold"
                              :class="statusFilter === 'low' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800'">{{ $belowStandardCount }}</span>
                    </button>

                    <button type="button"
                            @click="statusFilter = 'insufficient'"
                            :class="statusFilter === 'insufficient' ? 'bg-slate-700 text-white border-slate-700 shadow-2xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                            class="px-3 py-1.5 rounded-xl border text-[11px] font-semibold inline-flex items-center gap-1.5 whitespace-nowrap transition cursor-pointer shrink-0">
                        <span>Data Tidak Cukup</span>
                        <span class="px-1.5 rounded-full text-[10px] font-bold"
                              :class="statusFilter === 'insufficient' ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-700'">{{ $insufficientCount }}</span>
                    </button>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[11px] whitespace-nowrap">
                            <th class="py-3.5 px-4 w-16 text-center">Rank</th>
                            <th class="py-3.5 px-4">Teknisi</th>
                            <th class="py-3.5 px-4 text-center">Tiket</th>
                            <th class="py-3.5 px-4 text-right">SLA (45%)</th>
                            <th class="py-3.5 px-4 text-right">Interval 30m (35%)</th>
                            <th class="py-3.5 px-4 text-right">Respon (20%)</th>
                            <th class="py-3.5 px-4 text-right">Skor KPI</th>
                            <th class="py-3.5 pl-4 pr-6 text-center">Status Kelayakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kpiList as $index => $row)
                            @php
                                $tech = $row['user'];
                                $kpi = $row['data'];
                                $isInsufficient = (($kpi['status'] ?? '') === 'DATA_TIDAK_CUKUP' || $kpi['score'] === null);
                                $isPodium = (! $isInsufficient && $index < 3);
                                $rankBadgeClass = match ($index) {
                                    0 => 'bg-amber-100 text-amber-800 border-amber-300',
                                    1 => 'bg-slate-200/90 text-slate-700 border-slate-300',
                                    2 => 'bg-orange-100 text-orange-800 border-orange-300',
                                    default => 'bg-slate-100 text-slate-500 border-slate-200',
                                };
                                $divStyle = \App\Models\User::getDivisionBadgeStyle($tech->division);
                            @endphp
                            <tr x-show="matches({{ $index }})" class="hover:bg-teal-50/35 transition-colors">
                                <!-- Rank -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2.1rem] px-2 py-1 rounded-lg border text-[11px] font-extrabold tabular-nums {{ $isPodium ? $rankBadgeClass : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                        #{{ $index + 1 }}
                                    </span>
                                </td>

                                <!-- Teknisi + Avatar -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if ($tech->avatar_url)
                                            <img src="{{ $tech->avatar_url }}" alt="{{ $tech->name }}" class="w-9 h-9 rounded-full object-cover border border-teal-200 shrink-0 shadow-2xs">
                                        @else
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-2xs"
                                                 style="background: linear-gradient(135deg, #155E63 0%, #0D9488 100%);">
                                                {{ $tech->initials }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-800 block leading-snug truncate">{{ $tech->name }}</span>
                                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                <span class="font-mono text-[10.5px] text-slate-500">{{ $tech->employee_code }}</span>
                                                @if (! empty($tech->division))
                                                    <span class="px-1.5 py-0.2 rounded-md border text-[9.5px] font-semibold {{ $divStyle['class'] }}">
                                                        {{ $tech->division }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tiket -->
                                <td class="py-3.5 px-4 text-center">
                                    @if ($kpi['total_tickets'] >= 5)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-teal-50 text-[#155E63] border border-teal-200 tabular-nums">
                                            {{ $kpi['total_tickets'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200/80 tabular-nums">
                                            {{ $kpi['total_tickets'] }}<span class="text-slate-400 font-normal">/5</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- SLA 45% -->
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    @if ($isInsufficient)
                                        <span class="font-mono text-slate-400">0%</span>
                                    @else
                                        <div class="inline-flex flex-col items-end gap-1">
                                            <span class="font-mono font-semibold text-slate-700">{{ $kpi['sla_score'] }}%</span>
                                            <div class="w-16 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-teal-500" style="width: {{ min(100, max(0, (float) $kpi['sla_score'])) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Interval 30m 35% -->
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    @if ($isInsufficient)
                                        <span class="font-mono text-slate-400">0%</span>
                                    @else
                                        <div class="inline-flex flex-col items-end gap-1">
                                            <span class="font-mono font-semibold text-slate-700">{{ $kpi['interval_score'] }}%</span>
                                            <div class="w-16 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-cyan-500" style="width: {{ min(100, max(0, (float) $kpi['interval_score'])) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Respon 20% -->
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    @if ($isInsufficient)
                                        <span class="font-mono text-slate-400">0%</span>
                                    @else
                                        <div class="inline-flex flex-col items-end gap-1">
                                            <span class="font-mono font-semibold text-slate-700">{{ $kpi['response_score'] }}%</span>
                                            <div class="w-16 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, max(0, (float) $kpi['response_score'])) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Skor KPI -->
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    @if (! $isInsufficient)
                                        @php
                                            $scoreChipClass = $kpi['score'] >= 85
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                : ($kpi['score'] >= 70 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg border font-heading font-extrabold text-sm {{ $scoreChipClass }}">
                                            {{ number_format($kpi['score'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-medium">&mdash;</span>
                                    @endif
                                </td>

                                <!-- Status Kelayakan -->
                                <td class="py-3.5 pl-4 pr-6 text-center">
                                    @if ($isInsufficient)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Data Tidak Cukup (&lt;5)</span>
                                        </span>
                                    @elseif ($kpi['score'] >= 85)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Memenuhi Target</span>
                                        </span>
                                    @elseif ($kpi['score'] >= 70)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Perlu Peningkatan</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-800 border border-rose-200 whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Di Bawah Standar</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2 text-slate-400">
                                        <svg class="w-9 h-9 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="text-xs font-medium text-slate-500">Tidak ada data teknisi yang tercatat pada periode ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($kpiList as $index => $row)
                    @php
                        $tech = $row['user'];
                        $kpi = $row['data'];
                        $isInsufficient = (($kpi['status'] ?? '') === 'DATA_TIDAK_CUKUP' || $kpi['score'] === null);
                        $isPodium = (! $isInsufficient && $index < 3);
                        $rankBadgeClass = match ($index) {
                            0 => 'bg-amber-100 text-amber-800 border-amber-300',
                            1 => 'bg-slate-200/90 text-slate-700 border-slate-300',
                            2 => 'bg-orange-100 text-orange-800 border-orange-300',
                            default => 'bg-slate-100 text-slate-500 border-slate-200',
                        };
                    @endphp
                    <div x-show="matches({{ $index }})" class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-1 rounded-lg border text-[11px] font-extrabold tabular-nums shrink-0 {{ $isPodium ? $rankBadgeClass : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                    #{{ $index + 1 }}
                                </span>
                                @if ($tech->avatar_url)
                                    <img src="{{ $tech->avatar_url }}" alt="{{ $tech->name }}" class="w-9 h-9 rounded-full object-cover border border-teal-200 shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0"
                                         style="background: linear-gradient(135deg, #155E63 0%, #0D9488 100%);">
                                        {{ $tech->initials }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="font-bold text-xs sm:text-sm text-slate-800 block leading-snug truncate">{{ $tech->name }}</span>
                                    <span class="font-mono text-[10.5px] text-slate-500 block">{{ $tech->employee_code }}</span>
                                </div>
                            </div>

                            @if ($isInsufficient)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap shrink-0">
                                    Data Tidak Cukup (&lt;5)
                                </span>
                            @elseif ($kpi['score'] >= 85)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap shrink-0">
                                    Memenuhi Target
                                </span>
                            @elseif ($kpi['score'] >= 70)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap shrink-0">
                                    Perlu Peningkatan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 whitespace-nowrap shrink-0">
                                    Di Bawah Standar
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-4 gap-2 bg-slate-50/90 border border-slate-100 rounded-xl p-2.5 text-center tabular-nums">
                            <div>
                                <span class="block text-[10px] font-semibold text-slate-400 uppercase">Tiket</span>
                                <span class="text-xs font-bold text-slate-800 mt-0.5 block">{{ $kpi['total_tickets'] }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-slate-400 uppercase">SLA</span>
                                <span class="text-xs font-mono font-semibold text-slate-700 mt-0.5 block">{{ $kpi['sla_score'] }}%</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-slate-400 uppercase">Interval</span>
                                <span class="text-xs font-mono font-semibold text-slate-700 mt-0.5 block">{{ $kpi['interval_score'] }}%</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-semibold text-slate-400 uppercase">Respon</span>
                                <span class="text-xs font-mono font-semibold text-slate-700 mt-0.5 block">{{ $kpi['response_score'] }}%</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-0.5">
                            <span class="text-[11px] font-semibold text-slate-500">Skor Total KPI</span>
                            @if (! $isInsufficient)
                                @php
                                    $mobileScoreText = $kpi['score'] >= 85
                                        ? 'text-emerald-700'
                                        : ($kpi['score'] >= 70 ? 'text-amber-700' : 'text-rose-700');
                                    $mobileScoreBar = $kpi['score'] >= 85
                                        ? 'bg-emerald-500'
                                        : ($kpi['score'] >= 70 ? 'bg-amber-500' : 'bg-rose-500');
                                @endphp
                                <div class="flex items-center gap-2.5 flex-1 max-w-[180px]">
                                    <div class="h-1.5 flex-1 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full {{ $mobileScoreBar }}" style="width: {{ min(100, max(0, (float) $kpi['score'])) }}%"></div>
                                    </div>
                                    <span class="font-heading font-extrabold text-sm tabular-nums {{ $mobileScoreText }}">
                                        {{ number_format($kpi['score'], 2) }}
                                    </span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Menunggu &ge; 5 tiket</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        Tidak ada data teknisi yang tercatat pada periode ini.
                    </div>
                @endforelse
            </div>

            <!-- Client Filter Empty State -->
            @if (! empty($kpiList))
                <div x-cloak x-show="visibleCount === 0" class="py-12 px-4 text-center space-y-2.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-700">Tidak ada teknisi yang cocok dengan filter atau pencarian Anda.</p>
                    <button type="button"
                            @click="search = ''; statusFilter = 'all'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-teal-50 text-[#155E63] border border-teal-200 hover:bg-teal-100 transition cursor-pointer">
                        Reset Filter
                    </button>
                </div>
            @endif

            <!-- Legend Footer -->
            <div class="bg-slate-50/70 border-t border-slate-100 px-4 sm:px-5 py-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>&ge; 85.0 Memenuhi Target</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>70.0 &ndash; 84.9 Perlu Peningkatan</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span>&lt; 70.0 Di Bawah Standar</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>&lt; 5 Tiket Data Tidak Cukup</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
