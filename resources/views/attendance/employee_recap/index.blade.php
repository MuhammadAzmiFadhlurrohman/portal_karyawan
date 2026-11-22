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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Card 1: Total Karyawan -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-4 justify-between sm:justify-start">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 block truncate">Total Karyawan</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight block truncate">{{ $totalEmployees }}</span>
            </div>
        </div>

        <!-- Card 2: Rata-rata Kehadiran -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-4 justify-between sm:justify-start">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 block truncate">Rata-rata Kehadiran</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight block truncate">{{ $avgKehadiran }}</span>
            </div>
        </div>

        <!-- Card 3: Cuti Disetujui -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-4 justify-between sm:justify-start">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 block truncate">Cuti Disetujui</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight block truncate">{{ $totalCutiApprovedAll }}</span>
            </div>
        </div>

        <!-- Card 4: Total Potongan -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-4 justify-between sm:justify-start border-l-4 border-l-rose-500">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 block truncate">Total Potongan</span>
                <span class="text-base sm:text-lg font-black text-slate-800 tracking-tight block truncate" title="Rp {{ number_format($totalPotonganAll, 0, ',', '.') }}">
                    Rp {{ number_format($totalPotonganAll, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <style>
        .emp-attendance-card {
            border-radius: 22px !important;
            background: #ffffff !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05) !important;
            border: 1px solid #f1f5f9 !important;
            padding: 12px !important;
            position: relative !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        html.dark .emp-attendance-card {
            background: #131c2e !important;
            border-color: rgba(51, 65, 85, 0.85) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4) !important;
        }
        .emp-attendance-photo-panel {
            width: 108px !important;
            min-width: 108px !important;
            max-width: 108px !important;
            border-radius: 18px 18px 45px 18px !important;
            flex-shrink: 0 !important;
            position: relative !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(145deg, #029990 0%, #038078 45%, #015c57 100%) !important;
        }
        .emp-attendance-photo-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center 12% !important;
            display: block !important;
            pointer-events: none !important;
            filter: drop-shadow(0 8px 14px rgba(0,0,0,0.32)) !important;
        }
        @media (max-width: 640px) {
            .emp-attendance-card {
                padding: 10px !important;
                gap: 10px !important;
                border-radius: 20px !important;
            }
            .emp-attendance-photo-panel {
                width: 96px !important;
                min-width: 96px !important;
                max-width: 96px !important;
                border-radius: 16px 16px 36px 16px !important;
            }
        }
    </style>

    <!-- Employee Cards Grid (Sesuai Desain Lembur Karyawan dengan Foto) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @forelse ($cards as $c)
            @php
                $u = $c['user'];
                $cardAvatar = $u->avatar_url ?? null;
                $cardInitials = $u->initials ?? strtoupper(substr($u->name ?? 'K', 0, 2));
            @endphp
            <div class="emp-attendance-card {{ $c['alpa'] > 0 ? 'border-rose-300 ring-2 ring-rose-400/20' : '' }} hover:shadow-[0_14px_32px_rgba(0,0,0,0.08)] hover:-translate-y-0.5 transition-all duration-300 group">

                <!-- Background Soft Ambient Wave Kanan -->
                <svg class="absolute right-0 bottom-0 h-full w-2/5 pointer-events-none opacity-20" viewBox="0 0 200 200" fill="none" preserveAspectRatio="none">
                    <circle cx="170" cy="170" r="130" fill="#2dd4bf" filter="blur(50px)"/>
                    <path d="M 40 200 C 110 130, 150 170, 200 110" stroke="#0d9488" stroke-width="1.5" stroke-opacity="0.25"/>
                </svg>

                <!-- KIRI: Panel Foto Profil Modern dengan Kurva Organik -->
                <div class="emp-attendance-photo-panel select-none">
                    
                    <!-- Glow & Curved Lines Abstract Dekoratif -->
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute -top-6 -left-6 w-24 h-24 rounded-full bg-teal-300/20 blur-xl"></div>
                        <svg class="w-full h-full opacity-35" viewBox="0 0 200 240" fill="none" preserveAspectRatio="none">
                            <circle cx="40" cy="190" r="110" stroke="rgba(255,255,255,0.25)" stroke-width="2"/>
                            <circle cx="40" cy="190" r="140" stroke="rgba(94,234,212,0.3)" stroke-width="1.5"/>
                            <circle cx="40" cy="190" r="80" fill="rgba(20,184,166,0.35)"/>
                        </svg>
                    </div>

                    <!-- Pill Badges di Kiri Atas Panel Foto -->
                    <div class="absolute top-2 left-2 z-10 flex flex-col gap-1 items-start">
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold text-white shadow-xs"
                             style="background: rgba(3, 62, 57, 0.85); backdrop-filter: blur(8px); border: 1px solid rgba(94, 234, 212, 0.45);">
                            <span class="w-1.5 h-1.5 rounded-full {{ $c['alpa'] > 0 ? 'bg-rose-400 shadow-[0_0_6px_#f43f5e]' : 'bg-emerald-400 shadow-[0_0_6px_#34d399]' }}"></span>
                            <span>{{ $c['hadir'] }} Hadir</span>
                        </div>
                    </div>

                    <!-- Foto Profil Karyawan / Fallback Icon Profile User -->
                    @if ($cardAvatar)
                        <img src="{{ $cardAvatar }}" alt="{{ $u->name }}"
                             class="emp-attendance-photo-img transition-transform duration-500 group-hover:scale-105"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="flex-col items-center justify-center text-white" style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">
                            <div class="flex items-center justify-center text-white shadow-md" style="width:48px; height:48px; border-radius:50%; background:rgba(255,255,255,0.18); border:2px solid rgba(255,255,255,0.45); margin-bottom:14px;">
                                <svg class="w-6 h-6 text-white drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold tracking-wider opacity-90">{{ $cardInitials }}</span>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center text-white w-full h-full" style="align-items:center; justify-content:center;">
                            <div class="flex items-center justify-center text-white shadow-md" style="width:48px; height:48px; border-radius:50%; background:rgba(255,255,255,0.18); border:2px solid rgba(255,255,255,0.45); margin-bottom:14px;">
                                <svg class="w-6 h-6 text-white drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold tracking-wider opacity-90">{{ $cardInitials }}</span>
                        </div>
                    @endif
                </div>

                <!-- KANAN: Data Lengkap Karyawan -->
                <div class="flex-1 min-w-0 flex flex-col justify-between h-full gap-2 relative z-10 py-0.5">
                    <div>
                        <!-- Periode & NIK Badge -->
                        <div class="flex items-center justify-between gap-1 text-[10px] font-semibold text-teal-800 mb-1">
                            <span class="inline-flex items-center gap-1 text-teal-700 shrink-0">
                                <svg class="w-3 h-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>{{ $monthName }}</span>
                            </span>
                            @if (!empty($u->nik))
                                <span class="px-1.5 py-0.2 rounded-md bg-teal-50 text-teal-800 border border-teal-200/60 font-mono text-[9px] font-bold">
                                    {{ $u->nik }}
                                </span>
                            @endif
                        </div>

                        <!-- Nama Karyawan & Divisi -->
                        <div class="mb-2">
                            <h2 class="text-xs sm:text-sm font-black text-slate-900 tracking-tight uppercase leading-snug truncate" title="{{ $u->name }}">
                                {{ $u->name }}
                            </h2>
                            <div class="flex items-center gap-1 text-[11px] text-slate-500 mt-0.5 truncate">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span class="truncate">{{ $u->division ?: 'Finance / Non-Divisi' }}</span>
                            </div>
                        </div>

                        <!-- Row 1: HADIR, TERLAMBAT, ALPA -->
                        <div class="grid grid-cols-3 gap-1 text-center py-1.5 border-y border-slate-100 mb-2">
                            <div>
                                <span class="text-sm sm:text-base font-black text-emerald-600 block leading-tight">{{ $c['hadir'] }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">HADIR</span>
                            </div>
                            <div>
                                <span class="text-sm sm:text-base font-black text-amber-500 block leading-tight">{{ $c['terlambat'] }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">TERLAMBAT</span>
                            </div>
                            <div>
                                <span class="text-sm sm:text-base font-black text-rose-600 block leading-tight">{{ $c['alpa'] }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">ALPA</span>
                            </div>
                        </div>

                        <!-- Row 2: SISA CUTI, CUTI TERPAKAI, POTONGAN -->
                        <div class="grid grid-cols-3 gap-1 text-center py-0.5 mb-2">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block leading-tight">{{ $c['sisa_cuti'] }}</span>
                                <span class="text-[8.5px] font-semibold text-slate-400 uppercase tracking-wider block truncate">SISA CUTI</span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-700 block leading-tight">{{ $c['cuti_terpakai'] }}</span>
                                <span class="text-[8.5px] font-semibold text-slate-400 uppercase tracking-wider block truncate">TERPAKAI</span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-rose-600 block leading-tight truncate" title="Rp {{ number_format($c['total_potongan'], 0, ',', '.') }}">
                                    Rp {{ number_format($c['total_potongan'], 0, ',', '.') }}
                                </span>
                                <span class="text-[8.5px] font-semibold text-slate-400 uppercase tracking-wider block truncate">POTONGAN</span>
                            </div>
                        </div>

                        <!-- Warning / Status Badge -->
                        @if ($c['alpa'] > 0)
                            <div class="bg-rose-50 border border-rose-200/80 rounded-lg px-2 py-1 text-center text-[10px] font-bold text-rose-700 mb-2 flex items-center justify-center gap-1">
                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span class="truncate">{{ $c['alpa'] }} alpa dipotong (Rp {{ number_format($c['alpa_potongan'], 0, ',', '.') }})</span>
                            </div>
                        @else
                            <div class="bg-emerald-50 border border-emerald-200/80 rounded-lg px-2 py-1 text-center text-[10px] font-semibold text-emerald-700 mb-2 flex items-center justify-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span class="truncate">Tidak ada alpa periode ini</span>
                            </div>
                        @endif
                    </div>

                    <!-- Action Button: Lihat Detail -->
                    <a href="{{ route('attendance.employee-recap.show', ['user' => $u->id, 'year' => $year, 'month' => $month]) }}" 
                       class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white text-center shadow-xs transition flex items-center justify-center gap-1.5 hover:opacity-95 hover:shadow-md"
                       style="background: linear-gradient(135deg, #0d5f5f 0%, #0f766e 100%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Lihat Detail</span>
                    </a>
                </div>
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

