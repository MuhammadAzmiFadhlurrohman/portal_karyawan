<x-layouts.app title="Leaderboard Kehadiran">
    <div class="space-y-6 w-full pb-12">
        <!-- 1. Top Page Header Sesuai Tema Aplikasi Kita -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('attendance.index') }}" 
                   class="w-10 h-10 rounded-2xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight leading-tight">Leaderboard Kehadiran</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200/60">
                            {{ $monthName }} {{ $year }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Peringkat karyawan berdasarkan tingkat kehadiran terbaik</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
                <form method="POST" action="{{ route('attendance.leaderboard.opt-out') }}">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                        <span>{{ $user->leaderboard_opt_out ? 'Tampilkan Nama Saya' : 'Opt-Out Privasi' }}</span>
                    </button>
                </form>

                <a href="{{ route('attendance.leaderboard.export', ['period_type' => $periodType, 'year' => $year, 'period_value' => $periodValue]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- 2. Hero Card: Peringkat Anda -->
        <div class="rounded-3xl bg-gradient-to-r from-[#091322] via-[#0f1d38] to-[#0c3144] p-5 sm:p-6 text-white shadow-md relative overflow-hidden border border-slate-800">
            <!-- Glow background decor -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
                <!-- Left: Rank Info -->
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 flex items-center justify-center text-amber-400 shrink-0 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0H9.496m5.004 0a3.75 3.75 0 003.75-3.75V5.25a.75.75 0 00-.75-.75h-1.5a.75.75 0 00-.75.75v3.75c0 .621-.504 1.125-1.125 1.125h-.872m-5.004 0c-.621 0-1.125-.504-1.125-1.125V5.25a.75.75 0 00-.75-.75h-1.5a.75.75 0 00-.75.75v6a3.75 3.75 0 003.75 3.75z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-teal-300 font-mono">PERINGKAT ANDA</p>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                                #{{ $myRanking['rank'] ?? '-' }}
                            </h2>
                            <span class="text-xs text-slate-300 font-medium">
                                {{ number_format($myRanking['present_pct'] ?? 0, 0) }}% kehadiran bulan ini
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Stats & Motivation Pill -->
                <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                    <div class="flex items-center gap-4 text-xs font-medium text-slate-300 border-l border-white/15 pl-4 sm:pl-6">
                        <div>
                            <span class="block text-slate-400 text-[11px]">Hadir</span>
                            <span class="font-bold text-white text-sm">{{ $myRanking['attended_days'] ?? 0 }} hari</span>
                        </div>
                        <div class="w-px h-6 bg-white/15"></div>
                        <div>
                            <span class="block text-slate-400 text-[11px]">Target</span>
                            <span class="font-bold text-white text-sm">{{ $myRanking['effective_workdays'] ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white text-slate-800 text-xs font-bold shadow-sm select-none">
                        <span>🚀</span>
                        <span>Terus semangat!</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Filter Card Sesuai Gambar -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-5 shadow-xs">
            <form method="GET" action="{{ route('attendance.leaderboard') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:items-end">
                <input type="hidden" name="period_type" value="monthly">

                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">BULAN</label>
                    <div class="relative">
                        <select name="period_value" 
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 appearance-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition shadow-2xs pr-9">
                            @php
                                $indonesianMonths = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @foreach ($indonesianMonths as $num => $name)
                                <option value="{{ $num }}" @selected((int)$periodValue === $num)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">TAHUN</label>
                    <div class="relative">
                        <select name="year" 
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 appearance-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition shadow-2xs pr-9">
                            @foreach ([2025, 2026, 2027] as $y)
                                <option value="{{ $y }}" @selected((int)$year === $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-4 flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Tampilkan</span>
                    </button>

                    <a href="{{ route('attendance.leaderboard') }}" 
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- 4. Leaderboard Table Card Sesuai Gambar -->
        <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Table Card Header -->
            <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800">Top 10 karyawan terbaik</h3>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200/60">
                    Bulan {{ $monthName }} {{ $year }}
                </span>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-5 text-center w-20">RANK</th>
                            <th class="py-3 px-5">KARYAWAN</th>
                            <th class="py-3 px-5">DIVISI</th>
                            <th class="py-3 px-5 text-center">HADIR</th>
                            <th class="py-3 px-5">PERSENTASE</th>
                            <th class="py-3 px-5 text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rankings as $row)
                            @php
                                $lateMins = (int) $row['total_late_minutes'];
                                $hours = intdiv($lateMins, 60);
                                $mins = $lateMins % 60;
                                $lateFormatted = $hours > 0 ? "{$hours}j {$mins}m" : "{$mins}m";
                                $isMe = ($row['user_id'] === $user->id);
                            @endphp
                            <tr class="transition-colors {{ $isMe ? 'bg-teal-50/40 hover:bg-teal-50/70' : 'hover:bg-slate-50/70' }}">
                                <!-- RANK -->
                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center">
                                        @if ($row['rank'] === 1)
                                            <!-- Ribbon Medal Emas -->
                                            <div class="w-8 h-8 flex items-center justify-center" title="Juara 1">
                                                <svg class="w-7 h-7 drop-shadow-2xs" viewBox="0 0 36 36" fill="none">
                                                    <path d="M12 4L6 18h6l3-8-3-6z" fill="#DC2626"/>
                                                    <path d="M24 4l6 18h-6l-3-8 3-6z" fill="#B91C1C"/>
                                                    <circle cx="18" cy="22" r="11" fill="#F59E0B" stroke="#D97706" stroke-width="2"/>
                                                    <circle cx="18" cy="22" r="8.5" fill="#FBBF24"/>
                                                    <text x="18" y="26" text-anchor="middle" font-size="11" font-weight="900" fill="#78350F">1</text>
                                                </svg>
                                            </div>
                                        @elseif ($row['rank'] === 2)
                                            <!-- Ribbon Medal Perak -->
                                            <div class="w-8 h-8 flex items-center justify-center" title="Juara 2">
                                                <svg class="w-7 h-7 drop-shadow-2xs" viewBox="0 0 36 36" fill="none">
                                                    <path d="M12 4L6 18h6l3-8-3-6z" fill="#0284C7"/>
                                                    <path d="M24 4l6 18h-6l-3-8 3-6z" fill="#0369A1"/>
                                                    <circle cx="18" cy="22" r="11" fill="#94A3B8" stroke="#64748B" stroke-width="2"/>
                                                    <circle cx="18" cy="22" r="8.5" fill="#CBD5E1"/>
                                                    <text x="18" y="26" text-anchor="middle" font-size="11" font-weight="900" fill="#1E293B">2</text>
                                                </svg>
                                            </div>
                                        @elseif ($row['rank'] === 3)
                                            <!-- Ribbon Medal Perunggu -->
                                            <div class="w-8 h-8 flex items-center justify-center" title="Juara 3">
                                                <svg class="w-7 h-7 drop-shadow-2xs" viewBox="0 0 36 36" fill="none">
                                                    <path d="M12 4L6 18h6l3-8-3-6z" fill="#16A34A"/>
                                                    <path d="M24 4l6 18h-6l-3-8 3-6z" fill="#15803D"/>
                                                    <circle cx="18" cy="22" r="11" fill="#D97706" stroke="#B45309" stroke-width="2"/>
                                                    <circle cx="18" cy="22" r="8.5" fill="#F59E0B"/>
                                                    <text x="18" y="26" text-anchor="middle" font-size="11" font-weight="900" fill="#451A03">3</text>
                                                </svg>
                                            </div>
                                        @else
                                            <!-- Peringkat 4+ Circle -->
                                            <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-600 font-bold text-xs font-mono">
                                                {{ $row['rank'] }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- KARYAWAN -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        @if (!empty($row['foto']))
                                            <img src="{{ asset('storage/' . $row['foto']) }}" 
                                                 alt="{{ $row['display_name'] }}" 
                                                 class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-700 font-bold text-sm flex items-center justify-center shrink-0 border border-violet-200/60 uppercase">
                                                {{ mb_substr($row['display_name'], 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-tight">
                                                    {{ $row['display_name'] }}
                                                </span>
                                                @if ($isMe)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 font-mono">Anda</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] font-mono text-slate-400 block">
                                                {{ $row['employee_code'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- DIVISI -->
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200/60">
                                        {{ $row['division'] }}
                                    </span>
                                </td>

                                <!-- HADIR -->
                                <td class="py-4 px-5 text-center">
                                    <span class="text-xs font-bold text-slate-700 font-mono">
                                        {{ $row['attended_days'] }} <span class="text-slate-400 font-normal">/</span> {{ $row['effective_workdays'] }}
                                    </span>
                                </td>

                                <!-- PERSENTASE -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-24 sm:w-28 bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-teal-500 h-2 rounded-full transition-all duration-300" 
                                                 style="width: {{ min(100, max(0, $row['present_pct'])) }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-800 font-mono">
                                            {{ round($row['present_pct']) }}%
                                        </span>
                                    </div>
                                </td>

                                <!-- STATUS -->
                                <td class="py-4 px-5 text-center">
                                    @if ($lateMins === 0)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-semibold">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Tepat waktu</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200/80 text-amber-700 text-xs font-semibold">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $lateFormatted }}</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada data peringkat</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Data peringkat kehadiran belum tersedia untuk periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
