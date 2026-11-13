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

    <!-- 6 Metric Cards in 2 Rows (Matches Gambar 2) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Row 1: HADIR -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <span class="text-xl font-black text-slate-800 block leading-tight">{{ $hadirCount }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HADIR</span>
            </div>
        </div>

        <!-- Row 1: TERLAMBAT -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5 border-t-2 border-t-amber-400">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <span class="text-xl font-black text-slate-800 block leading-tight">{{ $terlambatCount }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TERLAMBAT</span>
            </div>
        </div>

        <!-- Row 1: ALPA -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5 border-t-2 border-t-rose-500">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <span class="text-xl font-black text-slate-800 block leading-tight">{{ $alpaCount }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ALPA</span>
            </div>
        </div>

        <!-- Row 2: SISA CUTI -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <span class="text-xl font-black text-slate-800 block leading-tight">{{ $sisaCuti }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">SISA CUTI</span>
                <span class="text-[10px] text-slate-400">Dari {{ $annualQuota }} hari</span>
            </div>
        </div>

        <!-- Row 2: CUTI TERPAKAI -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <span class="text-xl font-black text-slate-800 block leading-tight">{{ $cutiTerpakaiAnnual }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">CUTI TERPAKAI</span>
                <span class="text-[10px] text-slate-400">Bulan ini: {{ $monthLeavesCount }}</span>
            </div>
        </div>

        <!-- Row 2: TOTAL POTONGAN -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5 border-t-2 border-t-rose-500">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <span class="text-base font-black text-slate-800 block leading-tight">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL POTONGAN</span>
                <span class="text-[10px] text-slate-400">Telat: Rp {{ number_format($telatPotongan, 0, ',', '.') }} | Alpa: Rp {{ number_format($alpaPotongan, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Table (Rekapitulasi Harian - Matches Gambar 2) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ photoModalUrl: null, photoModalName: '' }">
        <div class="p-4 md:p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-sm font-black text-slate-800 tracking-tight">Rekapitulasi Harian</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3 px-4">TANGGAL</th>
                        <th class="py-3 px-4 text-center">FOTO</th>
                        <th class="py-3 px-4">JAM MASUK</th>
                        <th class="py-3 px-4">JAM PULANG</th>
                        <th class="py-3 px-4">SHIFT</th>
                        <th class="py-3 px-4">STATUS</th>
                        <th class="py-3 px-4">POTONGAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($dailyRows as $row)
                        @php
                            $isRowLibur = in_array(strtolower($row['status']), ['libur']);
                            $isRowAlpa = in_array(strtolower($row['status']), ['alpa']);
                            $isRowLate = in_array(strtolower($row['status']), ['late', 'terlambat']);
                            $isRowHadir = in_array(strtolower($row['status']), ['present', 'hadir']);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $row['day_name'] }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $row['date_formatted'] }}</span>
                            </td>

                            <!-- Foto -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($row['photo_path'])
                                    <div class="inline-block relative group cursor-pointer"
                                         @click="photoModalUrl = '/storage/{{ $row['photo_path'] }}'; photoModalName = '{{ addslashes($user->name) }} ({{ $row['date_formatted'] }})'">
                                        <img src="/storage/{{ $row['photo_path'] }}" 
                                             alt="Selfie" 
                                             class="w-10 h-10 object-cover rounded-xl border border-slate-200 shadow-2xs group-hover:scale-105 transition mx-auto">
                                        <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-[8px] font-bold">
                                            Lihat
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- Jam Masuk -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($row['clock_in'] !== '-')
                                    <span class="font-bold text-slate-800 font-mono block">{{ $row['clock_in'] }}</span>
                                    @if ($row['late_text'])
                                        <span class="text-[10px] text-amber-600 font-semibold block">{{ $row['late_text'] }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- Jam Pulang -->
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono">
                                @if ($row['clock_out'] !== '-')
                                    <span class="font-bold text-slate-800">{{ $row['clock_out'] }}</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- Shift -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($isRowLibur)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-sky-50 text-sky-700 text-[11px] font-semibold">
                                        Libur
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-sky-50 text-sky-700 text-[11px] font-semibold">
                                        {{ $row['shift_label'] }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($isRowHadir)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-bold">
                                        Hadir
                                    </span>
                                @elseif ($isRowLate)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 text-[11px] font-bold">
                                        Terlambat
                                    </span>
                                @elseif ($isRowAlpa)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-rose-50 text-rose-700 text-[11px] font-bold">
                                        Alpa
                                    </span>
                                @elseif ($isRowLibur)
                                    <span class="text-slate-400 font-medium">Libur</span>
                                @else
                                    <span class="text-slate-400">{{ ucfirst($row['status']) }}</span>
                                @endif
                            </td>

                            <!-- Potongan -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($row['potongan_badge'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200/80 text-[10px] font-bold text-rose-700">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $row['potongan_badge']['amount'] }}</span>
                                        <span class="text-rose-500 font-normal">({{ $row['potongan_badge']['note'] }})</span>
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Lightbox Modal Foto -->
        <div x-show="photoModalUrl" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs"
             @click.self="photoModalUrl = null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl max-w-sm w-full p-4 shadow-2xl relative">
                <button type="button" 
                        @click="photoModalUrl = null"
                        class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="mb-3">
                    <span class="text-xs font-bold text-slate-800" x-text="photoModalName"></span>
                </div>
                <img :src="photoModalUrl" alt="Foto Absensi" class="w-full h-80 object-cover rounded-xl border border-slate-200">
            </div>
        </div>
    </div>

</div>
@endsection
