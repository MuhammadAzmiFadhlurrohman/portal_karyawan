@extends('layouts.app', ['title' => 'Rekap Absensi Karyawan'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Top Banner Card (Matches Gambar 1) -->
    <div class="rounded-2xl p-5 md:p-6 text-white shadow-xl relative overflow-hidden" 
         style="background: linear-gradient(135deg, #094747 0%, #0d5f5f 50%, #0f766e 100%);">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                        Rekap Absensi Karyawan
                    </h1>
                    <p class="text-xs text-teal-100/90 mt-0.5">Monitoring absensi, alpa, izin &amp; potongan seluruh staf</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-900/60 border border-teal-400/30 text-[11px] font-mono text-teal-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7h16m-8 4v6m-3-3h6"/></svg>
                    attendance_db (baru)
                </span>
                <a href="{{ route('attendance.schedules') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-xs font-semibold text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Lihat Jadwal Piket
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Card: Bulan & Tahun -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('attendance.employee-recap') }}" class="space-y-4">
            <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter Periode</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-5">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                    <select name="month" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-hidden transition">
                        @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                    <select name="year" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-hidden transition">
                        @for ($y = now('Asia/Jakarta')->format('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white shadow-md transition flex items-center justify-center gap-1.5"
                            style="background: linear-gradient(135deg, #0d5f5f 0%, #0f766e 100%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </div>

            <!-- Search input bar -->
            <div class="pt-2 border-t border-slate-100 relative">
                <div class="absolute inset-y-0 left-0 pt-2 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="q" 
                       value="{{ $search }}" 
                       placeholder="Cari nama karyawan..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50/70 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-hidden transition">
            </div>
        </form>
    </div>

    <!-- Active Filter Status Notification Strip -->
    @php
        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1, 'Asia/Jakarta')->translatedFormat('F Y');
    @endphp
    <div class="bg-teal-50/70 border border-teal-200/80 rounded-xl px-4 py-3 flex items-center justify-between text-xs text-teal-900">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Menampilkan rekap absensi: <strong class="font-bold text-teal-950">{{ $monthName }}</strong> ({{ $scheduledDaysCount }} hari kerja)</span>
        </div>
        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 text-[10px] font-mono font-medium">
            attendance_db (baru)
        </span>
    </div>

    <!-- 4 Summary Metrics Cards (Matches Gambar 1) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Karyawan -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-500 block">Total Karyawan</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalEmployees }}</span>
            </div>
        </div>

        <!-- Card 2: Rata-rata Kehadiran -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-500 block">Rata-rata Kehadiran</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ $avgKehadiran }}</span>
            </div>
        </div>

        <!-- Card 3: Cuti Disetujui -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-500 block">Cuti Disetujui</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalCutiApprovedAll }}</span>
            </div>
        </div>

        <!-- Card 4: Total Potongan -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-4 border-l-4 border-l-rose-500">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-500 block">Total Potongan</span>
                <span class="text-lg font-black text-slate-800 tracking-tight">Rp {{ number_format($totalPotonganAll, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Employee Cards Grid (Matches Gambar 1) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($cards as $c)
            @php
                $u = $c['user'];
            @endphp
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <!-- Employee Name & Division -->
                    <div class="mb-4">
                        <h2 class="text-sm font-black text-slate-900 tracking-tight uppercase leading-snug">
                            {{ $u->name }}
                        </h2>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>{{ $u->division ?: 'Finance / Non-Divisi' }}</span>
                        </div>
                    </div>

                    <!-- Row 1: HADIR, TERLAMBAT, ALPA -->
                    <div class="grid grid-cols-3 gap-2 text-center py-2 border-y border-slate-100 mb-3">
                        <div>
                            <span class="text-base font-black text-emerald-600 block">{{ $c['hadir'] }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HADIR</span>
                        </div>
                        <div>
                            <span class="text-base font-black text-amber-500 block">{{ $c['terlambat'] }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TERLAMBAT</span>
                        </div>
                        <div>
                            <span class="text-base font-black text-rose-600 block">{{ $c['alpa'] }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ALPA</span>
                        </div>
                    </div>

                    <!-- Row 2: SISA CUTI, CUTI TERPAKAI, POTONGAN -->
                    <div class="grid grid-cols-3 gap-2 text-center py-1 mb-3">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">{{ $c['sisa_cuti'] }}</span>
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">SISA CUTI</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">{{ $c['cuti_terpakai'] }}</span>
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">CUTI TERPAKAI</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-rose-600 block truncate" title="Rp {{ number_format($c['total_potongan'], 0, ',', '.') }}">
                                Rp {{ number_format($c['total_potongan'], 0, ',', '.') }}
                            </span>
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">POTONGAN</span>
                        </div>
                    </div>

                    <!-- Warning tag: alpa dipotong -->
                    @if ($c['alpa'] > 0)
                        <div class="bg-rose-50 border border-rose-200/80 rounded-lg px-2.5 py-1.5 text-center text-[10px] font-bold text-rose-700 mb-4 flex items-center justify-center gap-1">
                            <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $c['alpa'] }} alpa dipotong (Rp {{ number_format($c['alpa_potongan'], 0, ',', '.') }})</span>
                        </div>
                    @else
                        <div class="bg-emerald-50 border border-emerald-200/80 rounded-lg px-2.5 py-1.5 text-center text-[10px] font-semibold text-emerald-700 mb-4 flex items-center justify-center gap-1">
                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Tidak ada alpa periode ini</span>
                        </div>
                    @endif
                </div>

                <!-- Action Button: Lihat Detail -->
                <a href="{{ route('attendance.employee-recap.show', ['user' => $u->id, 'year' => $year, 'month' => $month]) }}" 
                   class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white text-center shadow-xs transition flex items-center justify-center gap-1.5"
                   style="background: linear-gradient(135deg, #0d5f5f 0%, #0f766e 100%);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Lihat Detail</span>
                </a>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Tidak ada data karyawan ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang berbeda.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
