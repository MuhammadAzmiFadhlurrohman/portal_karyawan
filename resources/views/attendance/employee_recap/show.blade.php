@extends('layouts.app', ['title' => 'Detail Absensi - ' . $user->name])

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Top Banner: Title & Back Button (Matches Gambar 2) -->
    <div class="rounded-2xl p-5 md:p-6 text-white shadow-xl relative overflow-hidden" 
         style="background: linear-gradient(135deg, #094747 0%, #0d5f5f 50%, #0f766e 100%);">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                        Detail Absensi Karyawan
                    </h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-teal-100 font-medium">Detail absensi {{ strtoupper($user->name) }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-teal-900/60 border border-teal-400/30 text-[10px] font-mono text-teal-200">
                            attendance_db (baru)
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('attendance.employee-recap', ['year' => $year, 'month' => $month]) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-xs font-bold text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Employee Profile Info Strip (Matches Gambar 2) -->
    @php
        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->translatedFormat('F Y');
        $initials = collect(explode(' ', $user->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
    @endphp
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- User Info -->
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-700 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                {{ $initials ?: 'KR' }}
            </div>
            <div>
                <h2 class="text-sm md:text-base font-black text-slate-900 tracking-tight uppercase">
                    {{ $user->name }}
                </h2>
                <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $user->division ?: 'NOC' }}
                    </span>
                    @if ($user->employee_code)
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-mono text-slate-600">{{ $user->employee_code }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3 Info Badges: Kode, Periode, Sisa Cuti -->
        <div class="grid grid-cols-3 gap-2 md:w-auto">
            <div class="bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2 text-center min-w-[110px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">KODE</span>
                <span class="text-xs font-mono font-bold text-slate-800">{{ $user->employee_code ?: 'KR' . $user->id }}</span>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2 text-center min-w-[120px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">PERIODE</span>
                <span class="text-xs font-bold text-slate-800">{{ $monthName }}</span>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2 text-center min-w-[110px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">SISA CUTI</span>
                <span class="text-xs font-bold text-slate-800">{{ $sisaCuti }} hari</span>
            </div>
        </div>
    </div>

    <!-- 6 Metric Cards in 1 Row on Desktop (grid-cols-2 sm:grid-cols-3 lg:grid-cols-6) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 lg:gap-3">
        <!-- 1: HADIR -->
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HADIR</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl lg:text-2xl font-black text-slate-800 block leading-tight">{{ $hadirCount }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold block mt-0.5">Hari Kerja</span>
            </div>
        </div>

        <!-- 2: TERLAMBAT -->
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-amber-300 transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TERLAMBAT</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl lg:text-2xl font-black text-slate-800 block leading-tight">{{ $terlambatCount }}</span>
                <span class="text-[10px] text-amber-600 font-semibold block mt-0.5">Keterlambatan</span>
            </div>
        </div>

        <!-- 3: ALPA -->
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-rose-300 transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-rose-500 to-rose-600"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ALPA</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl lg:text-2xl font-black text-slate-800 block leading-tight">{{ $alpaCount }}</span>
                <span class="text-[10px] text-rose-500 font-medium block mt-0.5">Tanpa Keterangan</span>
            </div>
        </div>

        <!-- 4: SISA CUTI -->
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-sky-300 transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-sky-400 to-blue-500"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SISA CUTI</span>
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl lg:text-2xl font-black text-slate-800 block leading-tight">{{ $sisaCuti }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Dari {{ $annualQuota }} hari</span>
            </div>
        </div>

        <!-- 5: CUTI TERPAKAI -->
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-indigo-300 transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-400 to-purple-500"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">CUTI TERPAKAI</span>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <span class="text-xl lg:text-2xl font-black text-slate-800 block leading-tight">{{ $cutiTerpakaiAnnual }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Bulan ini: {{ $monthLeavesCount }}</span>
            </div>
        </div>

        <!-- 6: TOTAL POTONGAN -->
        <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-rose-300 transition-all relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-rose-500 to-red-600"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">POTONGAN</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <span class="text-sm sm:text-base lg:text-[13px] xl:text-[15px] font-black text-rose-600 block leading-tight truncate" title="Rp {{ number_format($totalPotongan, 0, ',', '.') }}">
                    Rp {{ number_format($totalPotongan, 0, ',', '.') }}
                </span>
                <span class="text-[9.5px] text-slate-400 block mt-0.5 truncate" title="Telat: Rp {{ number_format($telatPotongan, 0, ',', '.') }} | Alpa: Rp {{ number_format($alpaPotongan, 0, ',', '.') }}">
                    T: {{ number_format($telatPotongan, 0, ',', '.') }} | A: {{ number_format($alpaPotongan, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Table (Rekapitulasi Harian) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ photoModalUrl: null, photoModalName: '' }">
        <!-- Card Header with Filters / Meta Info -->
        <div class="px-5 py-4 border-b border-slate-100 bg-linear-to-r from-slate-50/70 via-white to-slate-50/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 shadow-2xs">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800 tracking-tight">Rekapitulasi Harian</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            {{ count($dailyRows) }} Hari
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Rincian absensi, jam masuk-pulang, shift, dan kalkulasi potongan harian</p>
                </div>
            </div>

            <!-- Status Legend Badges -->
            <div class="flex items-center flex-wrap gap-2 text-[10.5px]">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hadir
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-semibold border border-amber-200/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Terlambat
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-semibold border border-rose-200/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Alpa
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-semibold border border-slate-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Libur
                </span>
            </div>
        </div>

        <!-- Table Responsive -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-44">Tanggal & Hari</th>
                        <th class="py-3 px-3 text-center w-20">Foto</th>
                        <th class="py-3 px-4 w-36">Jam Masuk</th>
                        <th class="py-3 px-4 w-36">Jam Pulang</th>
                        <th class="py-3 px-4 w-32">Shift</th>
                        <th class="py-3 px-4 w-32">Status</th>
                        <th class="py-3 px-4">Potongan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($dailyRows as $row)
                        @php
                            $statusLower = strtolower($row['status'] ?? '');
                            $isRowLibur = in_array($statusLower, ['libur']);
                            $isRowAlpa = in_array($statusLower, ['alpa']);
                            $isRowLate = in_array($statusLower, ['late', 'terlambat']);
                            $isRowHadir = in_array($statusLower, ['present', 'hadir']);
                            $isWeekend = in_array(strtolower($row['day_name']), ['minggu', 'sabtu', 'sunday', 'saturday']);
                            $isSunday = in_array(strtolower($row['day_name']), ['minggu', 'sunday']);
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/80 {{ $isRowLibur ? 'bg-slate-50/30' : ($isRowAlpa ? 'bg-rose-50/20' : '') }}">
                            <!-- Tanggal & Hari -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-xl flex flex-col items-center justify-center border font-mono text-center shrink-0 {{ $isSunday ? 'bg-rose-50 border-rose-200/70 text-rose-700' : ($isRowLibur ? 'bg-slate-100 border-slate-200 text-slate-600' : 'bg-slate-50 border-slate-200/80 text-slate-800') }}">
                                        <span class="text-[9px] font-bold uppercase leading-none {{ $isSunday ? 'text-rose-500' : 'text-slate-400' }}">
                                            {{ substr($row['day_name'], 0, 3) }}
                                        </span>
                                        <span class="text-sm font-black leading-tight mt-0.5">
                                            {{ explode('/', $row['date_formatted'])[0] ?? '' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 block text-xs {{ $isSunday ? 'text-rose-600' : '' }}">
                                            {{ $row['day_name'] }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-mono block">
                                            {{ $row['date_formatted'] }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Foto Absensi -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if ($row['photo_path'])
                                    <div class="inline-block relative group cursor-pointer"
                                         @click="photoModalUrl = '/storage/{{ $row['photo_path'] }}'; photoModalName = '{{ addslashes($user->name) }} ({{ $row['date_formatted'] }})'">
                                        <img src="/storage/{{ $row['photo_path'] }}" 
                                             alt="Selfie" 
                                             class="w-10 h-10 object-cover rounded-xl border border-slate-200/90 shadow-2xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-teal-500 transition mx-auto">
                                        <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-[9px] font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100/70 text-slate-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </span>
                                @endif
                            </td>

                            <!-- Jam Masuk -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($row['clock_in'] && $row['clock_in'] !== '-')
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-mono font-bold text-xs border border-slate-200/60 shadow-2xs">
                                            {{ $row['clock_in'] }}
                                        </span>
                                        @if ($row['late_text'])
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                                <svg class="w-2.5 h-2.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $row['late_text'] }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-300 font-mono text-sm pl-2">-</span>
                                @endif
                            </td>

                            <!-- Jam Pulang -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($row['clock_out'] && $row['clock_out'] !== '-')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-mono font-bold text-xs border border-slate-200/60 shadow-2xs">
                                        {{ $row['clock_out'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300 font-mono text-sm pl-2">-</span>
                                @endif
                            </td>

                            <!-- Shift -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($isRowLibur)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-semibold border border-slate-200/70">
                                        Libur
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 text-[11px] font-semibold border border-sky-200/60">
                                        {{ $row['shift_label'] ?? '-' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($isRowHadir)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200/70 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Hadir
                                    </span>
                                @elseif ($isRowLate)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[11px] font-bold border border-amber-200/70 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Terlambat
                                    </span>
                                @elseif ($isRowAlpa)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 text-[11px] font-bold border border-rose-200/70 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Alpa
                                    </span>
                                @elseif ($isRowLibur)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[11px] font-medium border border-slate-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Libur
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-50 text-slate-600 text-[11px] font-semibold border border-slate-200/60">
                                        {{ ucfirst($row['status']) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Potongan -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($row['potongan_badge'] && !empty($row['potongan_badge']['amount']))
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50/90 border border-rose-200/80 text-[10.5px] font-bold text-rose-700 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-mono font-black">{{ $row['potongan_badge']['amount'] }}</span>
                                        @if(!empty($row['potongan_badge']['note']))
                                            <span class="text-rose-500 font-medium text-[9.5px]">({{ $row['potongan_badge']['note'] }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-300 font-medium text-xs pl-2">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <!-- Table Footer Summary -->
                <tfoot class="bg-slate-50/90 border-t-2 border-slate-200 font-semibold text-slate-700">
                    <tr>
                        <td class="py-3 px-4 text-xs font-bold uppercase text-slate-600" colspan="2">
                            Total Ringkasan Bulan Ini
                        </td>
                        <td class="py-3 px-4" colspan="3">
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-emerald-700 font-bold">{{ $hadirCount }} Hadir</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-amber-700 font-bold">{{ $terlambatCount }} Telat</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-rose-700 font-bold">{{ $alpaCount }} Alpa</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500 uppercase font-bold text-right">
                            Total Potongan:
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-rose-100 text-rose-800 font-mono font-black text-xs border border-rose-200">
                                Rp {{ number_format($totalPotongan, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Lightbox Modal Foto (Modern Glassmorphic) -->
        <div x-show="photoModalUrl" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/75 backdrop-blur-xs"
             @click.self="photoModalUrl = null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-3xl max-w-sm w-full p-4 shadow-2xl relative border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
                <button type="button" 
                        @click="photoModalUrl = null"
                        class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shadow-2xs"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="mb-3 pr-8">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-teal-600 block">Bukti Selfie Absensi</span>
                    <span class="text-xs font-bold text-slate-800 line-clamp-1" x-text="photoModalName"></span>
                </div>
                <div class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 shadow-inner">
                    <img :src="photoModalUrl" alt="Foto Absensi" class="w-full h-80 object-cover">
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Klik di luar untuk menutup</span>
                    <button type="button" @click="photoModalUrl = null" class="text-xs font-bold text-teal-600 hover:underline">Selesai</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

