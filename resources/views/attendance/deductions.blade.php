<x-layouts.app title="Keuangan Absen">
    <div class="space-y-6 w-full pb-12">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex items-center justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0 flex-1">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Keuangan Absen
                        </h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-300 border border-teal-400/30">
                            Potongan &amp; Payroll
                        </span>
                        @if ($monthlyReport['is_locked'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Periode Terkunci
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                                Periode Terbuka
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Laporan potongan keterlambatan, alpa &amp; penyesuaian gaji absensi karyawan
                    </p>
                </div>
            </div>
        </div>

        <!-- Action & Filter Toolbar -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3 sm:px-5 sm:py-3.5 shadow-2xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-3.5">
            <!-- Left: Back Button & Info Chips -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-2.5 w-full lg:w-auto">
                <a href="{{ route('attendance.history', ['month' => $month, 'year' => $year]) }}" 
                   class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs hover:shadow-xs transition-all cursor-pointer w-full sm:w-auto">
                    <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="truncate">Riwayat Absen</span>
                </a>

                @if (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage'))
                <a href="{{ route('attendance.reports') }}" 
                   class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-cyan-300 bg-cyan-50/70 hover:bg-cyan-100/70 text-cyan-900 text-xs font-semibold transition cursor-pointer w-full sm:w-auto">
                    <svg class="w-4 h-4 text-cyan-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Absensi</span>
                </a>
                @endif

                <div class="inline-flex items-center justify-between sm:justify-start gap-2 px-3 py-1.5 rounded-xl bg-teal-50/80 border border-teal-200/70 text-xs w-full sm:w-auto">
                    <div class="flex items-center gap-1.5">
                        <span class="text-teal-700 font-medium">Periode:</span>
                        <span class="font-mono font-bold text-slate-800">{{ sprintf('%04d-%02d', $year, $month) }}</span>
                    </div>
                    <span class="text-teal-300">|</span>
                    <div class="flex items-center gap-1">
                        <span class="text-teal-700 font-medium">Tarif Alpa:</span>
                        <span class="font-mono font-bold text-rose-600">Rp {{ number_format((float) ($selectedUser->potongan_alpa ?? 25000), 0, ',', '.') }}</span>
                        <span class="text-slate-500 text-[10px]">/hari</span>
                    </div>
                </div>
            </div>

            <!-- Right: Filter Form -->
            <form method="GET" action="{{ route('attendance.deductions') }}" class="flex flex-col sm:flex-row sm:items-center gap-2 w-full lg:w-auto">
                @if ($allEmployees->count() > 1)
                    <div class="relative w-full sm:w-auto">
                        <select name="user_id" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-white px-3 py-2 pr-8 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition cursor-pointer">
                            @foreach ($allEmployees as $emp)
                                <option value="{{ $emp->id }}" @selected($emp->id === $selectedUser->id)>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="grid grid-cols-3 sm:flex sm:items-center gap-1.5 w-full sm:w-auto">
                    <select name="month" class="rounded-xl border border-slate-200 bg-white px-2.5 sm:px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition cursor-pointer">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $month)>Bln {{ sprintf('%02d', $m) }}</option>
                        @endfor
                    </select>

                    <input type="number" name="year" value="{{ $year }}" min="2025" max="2035" 
                           class="w-full sm:w-20 rounded-xl border border-slate-200 bg-white px-2.5 sm:px-3 py-2 text-xs font-mono font-semibold text-slate-700 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">

                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        @if (session('status'))
            <div class="rounded-2xl p-4 bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-2xl p-4 bg-rose-50 border border-rose-200/80 text-rose-800 text-xs shadow-2xs">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Permintaan Tidak Dapat Diproses</span>
                </div>
                <ul class="list-disc list-inside text-rose-700 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Summary Metric Cards (DECIMAL 15,2) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3.5">
            
            <!-- Card 1: Potongan Terlambat -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-3 sm:py-3.5 sm:px-4 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500 truncate">Potongan Terlambat</span>
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5 sm:mt-2">
                    <div class="text-sm sm:text-lg lg:text-xl font-bold text-slate-900 font-mono tracking-tight truncate">
                        Rp {{ number_format((float) $monthlyReport['total_late_deduction'], 0, ',', '.') }}
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-500 mt-0.5 sm:mt-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                        <span class="truncate">Total: <strong class="text-slate-700">{{ $monthlyReport['total_late_minutes'] }}</strong> mnt terlambat</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Potongan Alpa -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-3 sm:py-3.5 sm:px-4 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500 truncate">Potongan Alpa</span>
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5 sm:mt-2">
                    <div class="text-sm sm:text-lg lg:text-xl font-bold text-rose-600 font-mono tracking-tight truncate">
                        Rp {{ number_format((float) $monthlyReport['total_alpa_deduction'], 0, ',', '.') }}
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-500 mt-0.5 sm:mt-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span class="truncate">Total: <strong class="text-rose-700">{{ $monthlyReport['total_alpa_days'] }}</strong> hari alpa</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Adjustment Lintas Periode -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-3 sm:py-3.5 sm:px-4 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500 truncate">Adjustment Periode</span>
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5 sm:mt-2">
                    <div class="text-sm sm:text-lg lg:text-xl font-bold text-amber-700 font-mono tracking-tight truncate">
                        Rp {{ number_format((float) $monthlyReport['total_adjustment_amount'], 0, ',', '.') }}
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-500 mt-0.5 sm:mt-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                        <span class="truncate">Koreksi pasca-kunci</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Total Potongan Bersih (Hero Metallic Box) -->
            <div class="rounded-2xl p-3 sm:py-3.5 sm:px-4 shadow-sm text-white flex flex-col justify-between relative overflow-hidden"
                 style="background: linear-gradient(135deg, #022836 0%, #06404d 50%, #0e6774 100%);">
                <!-- Gloss Rims -->
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-[#5eead4]/60 to-transparent pointer-events-none"></div>

                <div class="flex items-center justify-between gap-1.5 relative z-10">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-teal-200/90 truncate">Total Potongan</span>
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg sm:rounded-xl bg-white/10 border border-white/20 text-cyan-300 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5 sm:mt-2 relative z-10">
                    <div class="text-sm sm:text-lg lg:text-xl font-extrabold text-white font-mono tracking-tight truncate">
                        Rp {{ number_format((float) $monthlyReport['grand_total_deduction'], 0, ',', '.') }}
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-teal-200/80 mt-0.5 sm:mt-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-300 animate-pulse shrink-0"></span>
                        <span class="truncate">Presisi <code class="font-mono text-cyan-200">DECIMAL(15,2)</code></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Daily Breakdown Table (Full Width) -->
        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
            @php
                $deductionItems = collect($monthlyReport['daily_items'] ?? [])->filter(function ($item) {
                    return ((float) ($item['total_deduction'] ?? 0)) > 0;
                })->values();
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                                Rincian Harian &amp; Tautan Absensi
                            </h2>
                            @if ($deductionItems->isNotEmpty())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $deductionItems->count() }} Hari Berpotongan
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Hanya menampilkan hari yang memiliki potongan keterlambatan atau alpa</p>
                    </div>
                </div>

                <a href="{{ route('attendance.history', ['month' => $month, 'year' => $year]) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#155E63] hover:text-[#0f4448] transition cursor-pointer shrink-0">
                    <span>Lihat Semua di Riwayat</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if ($deductionItems->isEmpty())
                <div class="rounded-xl border border-dashed border-emerald-200 p-8 text-center bg-emerald-50/40">
                    <div class="w-10 h-10 rounded-2xl bg-white border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto mb-2 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-slate-800">Tidak Ada Hari Berpotongan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Luar biasa! Tidak ada potongan keterlambatan maupun alpa pada periode bulan ini.</p>
                </div>
            @else
                <!-- Mobile Card List View (sm:hidden) -->
                <div class="sm:hidden space-y-2.5">
                    @foreach ($deductionItems as $item)
                        <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-xs text-slate-800">
                                        {{ $item['date'] }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded font-mono font-semibold text-[10px] bg-slate-100 text-slate-700">
                                        {{ $item['shift_code'] }}
                                    </span>
                                </div>

                                @if ($item['status'] === 'present')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir Tepat</span>
                                @elseif ($item['status'] === 'late')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Terlambat</span>
                                @elseif ($item['status'] === 'absent')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Alpa</span>
                                @elseif ($item['status'] === 'leave')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Cuti</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ strtoupper($item['status']) }}</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100">
                                <div class="text-[11px] text-slate-500 font-mono">
                                    @if ($item['clock_in'])
                                        {{ $item['clock_in'] }} - {{ $item['clock_out'] ?? '--:--' }}
                                    @else
                                        <span class="text-slate-400">Tidak ada jam</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($item['late_minutes'] > 0)
                                        <span class="font-mono font-semibold text-[11px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">
                                            {{ $item['late_minutes'] }} mnt
                                        </span>
                                    @endif
                                    <span class="font-mono font-bold text-xs {{ ((float) $item['total_deduction']) > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                        Rp {{ number_format((float) $item['total_deduction'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            @if (!empty($item['notes']))
                                <p class="text-[10px] text-slate-400 italic bg-slate-50/70 p-1.5 rounded">
                                    {{ $item['notes'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden sm:block) -->
                <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-200/80">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-3">Tanggal</th>
                                <th class="py-3 px-3">Shift</th>
                                <th class="py-3 px-3">Status &amp; Jam</th>
                                <th class="py-3 px-3 text-right">Terlambat</th>
                                <th class="py-3 px-3 text-right">Potongan (Rp)</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($deductionItems as $item)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-3 font-mono font-semibold text-slate-800">
                                        {{ $item['date'] }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded font-mono font-semibold text-xs bg-slate-100 text-slate-700">
                                            {{ $item['shift_code'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-1.5">
                                            @if ($item['status'] === 'present')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir Tepat</span>
                                            @elseif ($item['status'] === 'late')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Terlambat</span>
                                            @elseif ($item['status'] === 'absent')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Alpa</span>
                                            @elseif ($item['status'] === 'leave')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Cuti</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ strtoupper($item['status']) }}</span>
                                            @endif
                                        </div>
                                        @if ($item['clock_in'])
                                            <span class="font-mono text-[11px] text-slate-500 block mt-0.5">
                                                {{ $item['clock_in'] }} - {{ $item['clock_out'] ?? '--:--' }}
                                            </span>
                                        @endif
                                        @if (!empty($item['notes']))
                                            <span class="text-[10px] text-slate-400 block mt-0.5 italic">{{ $item['notes'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-semibold {{ $item['late_minutes'] > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                        {{ $item['late_minutes'] > 0 ? $item['late_minutes'] . ' mnt' : '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold {{ ((float) $item['total_deduction']) > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                        {{ number_format((float) $item['total_deduction'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <a href="{{ route('attendance.history', ['month' => $month, 'year' => $year]) }}"
                                           class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#155E63] hover:text-[#114b4f] transition cursor-pointer">
                                            <span>Detail</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($correctionQueue->isNotEmpty())
            <!-- Antrean Persetujuan Koreksi Absensi (Full Width) -->
            <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                                Antrean Persetujuan Koreksi Absensi ({{ $correctionQueue->count() }})
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Memerlukan persetujuan atasan atau HRD</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        Butuh Tindakan
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach ($correctionQueue as $corr)
                        <div class="rounded-xl border border-slate-200/80 p-4 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-heading font-bold text-sm text-slate-800">
                                        {{ $corr->user?->name }} · {{ $corr->date->format('Y-m-d') }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $corr->status }}
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-slate-600 mt-1">
                                    Usulan: {{ strtoupper($corr->requested_status) }} ({{ $corr->requested_attendance_type }}) · Masuk {{ $corr->requested_clock_in_time }} - Pulang {{ $corr->requested_clock_out_time }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">Alasan: {{ $corr->reason }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($corr->status === 'pending_supervisor')
                                    <form method="POST" action="{{ route('attendance.corrections.approve-supervisor', $corr) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                                            Approve Atasan
                                        </button>
                                    </form>
                                @endif
                                @if ($user->can('attendance.employee.manage') || $corr->status === 'pending_hrd')
                                    <form method="POST" action="{{ route('attendance.corrections.approve-hrd', $corr) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                                            Approve Final HRD
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (auth()->user()?->can('attendance.employee.manage') || auth()->user()?->can('attendance.deduction.lock_period'))
            <!-- Administrative HRD Configurations (2-Column Grid Below Table) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 w-full">
                
                @can('attendance.employee.manage')
                <!-- HRD Employee Deduction Rate Config -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                </div>
                                <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                                    Tarif Potongan Alpa
                                </h2>
                            </div>
                            @if ($selectedUser->potongan_alpa !== null)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Tarif Khusus
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    Standar Sistem
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            Atur nominal potongan gaji harian untuk <strong>{{ $selectedUser->name }}</strong> saat alpa melebihi kuota 3 hari/bulan atau saat jatah cuti habis.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('attendance.deductions.update-employee-rate') }}" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nominal Potongan Alpa (Rp / Hari)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                <input type="number" name="potongan_alpa" 
                                       value="{{ old('potongan_alpa', (int) ($selectedUser->potongan_alpa ?? 25000)) }}" 
                                       min="0" step="1000" required 
                                       class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-mono font-semibold text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                            </div>
                        </div>
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Tarif Potongan</span>
                        </button>
                    </form>
                </div>
                @endcan

                @can('attendance.deduction.lock_period')
                <!-- HRD Period Lock -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                                    Penguncian Periode Bulanan
                                </h2>
                                <span class="text-[11px] text-slate-400">Payroll Freeze (AC-09)</span>
                            </div>
                        </div>
                        
                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                            Setelah periode ditutup, laporan dibekukan (<code class="font-mono text-slate-600">period_closings</code>). Perubahan tarif di masa depan tidak mengubah bulan yang ditutup; koreksi baru masuk sebagai <em>adjustment</em> bulan berikutnya.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('attendance.deductions.lock-period') }}" class="space-y-3.5">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tahun</label>
                                <input type="number" name="year" value="{{ $year }}" required 
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-mono font-semibold text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Bulan</label>
                                <input type="number" name="month" value="{{ $month }}" min="1" max="12" required 
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-mono font-semibold text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Catatan Tutup Buku</label>
                            <input type="text" name="notes" placeholder="Cut-off payroll bulanan HRD" 
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                        </div>
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Kunci &amp; Bekukan Periode {{ sprintf('%04d-%02d', $year, $month) }}</span>
                        </button>
                    </form>
                </div>
                @endcan

            </div>
        @endif

    </div>
</x-layouts.app>
