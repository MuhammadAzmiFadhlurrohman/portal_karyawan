@extends('layouts.app', ['title' => 'Tiketing Gangguan Jaringan (NOC & Lapangan)'])

@section('content')
<div class="space-y-6">
    <!-- Header Banner: Compact Ocean Teal Corporate Theme -->
    <div class="gomsn-themed-banner relative overflow-hidden rounded-2xl px-5 py-3.5 sm:px-6 sm:py-4 text-white shadow-md border border-teal-500/20"
         style="background: var(--gomsn-header-bg) !important;">
        <!-- Ambient decorative glows (subtle) -->
        <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-[#18C4C4]/15 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-[#5eead4] shadow-inner shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-heading font-extrabold text-white tracking-tight">
                            Tiketing Gangguan Jaringan
                        </h1>
                        <span class="hidden md:inline-flex px-2 py-0.2 rounded-full text-[9px] font-bold tracking-wider uppercase bg-[#18C4C4]/20 text-[#5eead4] border border-[#18C4C4]/30">
                            v3.2 Live SLA
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-teal-100/75 mt-0.5 max-w-xl truncate">
                        Manajemen gangguan Backbone FO (<span class="font-mono text-[#5eead4] font-semibold">BDG-</span>) &amp; Broadband (<span class="font-mono text-[#5eead4] font-semibold">BRD-</span>) &bull; SLA MTTR &amp; Penugasan
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Buat Tiket, Rekap KPI -->
            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                @can('ticket.create')
                <a href="{{ route('tickets.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs text-white shadow-sm shadow-teal-950/30 border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer"
                   style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Tiket</span>
                </a>
                @endcan

                <a href="{{ route('gomsn.analytics.kpi') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-semibold text-xs text-white/90 bg-white/10 hover:bg-white/15 border border-white/20 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#5eead4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Rekap KPI</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-3.5 bg-emerald-50/90 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-800 font-bold text-xs shrink-0">✓</div>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold ml-4">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="p-3.5 bg-rose-50/90 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terjadi Kendala Validasi:</span>
            </div>
            <ul class="list-disc list-inside pl-4 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KPI Metric Stats Cards: Responsive Grid (Mobile: 2 Baris x 3 Kolom, Desktop: 1 Baris x 6 Kolom) -->
    <style>
        .kpi-ticket-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }
        @media (min-width: 768px) {
            .kpi-ticket-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: 0.625rem;
            }
        }
    </style>
    <div class="kpi-ticket-grid w-full">
        <!-- 1. TOTAL -->
        <div class="relative bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden group p-2 sm:p-3 flex flex-col justify-between min-h-[76px] sm:min-h-[88px]">
            <div class="absolute top-0 left-0 right-0 h-[2.5px] sm:h-[3px]" style="background-color: #2563eb;"></div>
            <!-- Card Icon -->
            <div class="absolute -bottom-1 -right-1 pointer-events-none group-hover:scale-105 transition-transform" style="color: #2563eb;">
                <svg class="w-8 h-8 sm:w-11 sm:h-11 md:w-13 md:h-13 opacity-75 sm:opacity-85" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <!-- Top Row -->
            <div class="flex items-center relative z-10">
                <span class="text-[9px] sm:text-[10px] md:text-[11px] font-extrabold tracking-wider text-slate-500 uppercase">TOTAL</span>
            </div>
            <!-- Value & Label -->
            <div class="mt-1 sm:mt-1.5 relative z-10">
                <div class="flex items-baseline">
                    <span class="text-base sm:text-xl md:text-2xl font-black text-slate-900 tracking-tight leading-none">
                        {{ number_format($stats['total'] ?? 0) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] md:text-xs font-semibold text-slate-400 ml-0.5 sm:ml-1">tiket</span>
                </div>
                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium text-slate-400 mt-0.5 truncate leading-tight">Keseluruhan</p>
            </div>
        </div>

        <!-- 2. OPEN -->
        <div class="relative bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden group p-2 sm:p-3 flex flex-col justify-between min-h-[76px] sm:min-h-[88px]">
            <div class="absolute top-0 left-0 right-0 h-[2.5px] sm:h-[3px]" style="background-color: #ef4444;"></div>
            <!-- Card Icon -->
            <div class="absolute -bottom-1 -right-1 pointer-events-none group-hover:scale-105 transition-transform" style="color: #ef4444;">
                <svg class="w-8 h-8 sm:w-11 sm:h-11 md:w-13 md:h-13 opacity-75 sm:opacity-85" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
                </svg>
            </div>
            <!-- Top Row -->
            <div class="flex items-center relative z-10">
                <span class="text-[9px] sm:text-[10px] md:text-[11px] font-extrabold tracking-wider uppercase" style="color: #dc2626;">OPEN</span>
            </div>
            <!-- Value & Label -->
            <div class="mt-1 sm:mt-1.5 relative z-10">
                <div class="flex items-baseline">
                    <span class="text-base sm:text-xl md:text-2xl font-black tracking-tight leading-none" style="color: #dc2626;">
                        {{ number_format($stats['open'] ?? 0) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] md:text-xs font-semibold text-rose-400 ml-0.5 sm:ml-1">tiket</span>
                </div>
                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium text-rose-600/75 mt-0.5 truncate leading-tight">Belum ditangani</p>
            </div>
        </div>

        <!-- 3. PROSES -->
        <div class="relative bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden group p-2 sm:p-3 flex flex-col justify-between min-h-[76px] sm:min-h-[88px]">
            <div class="absolute top-0 left-0 right-0 h-[2.5px] sm:h-[3px]" style="background-color: #f59e0b;"></div>
            <!-- Card Icon -->
            <div class="absolute -bottom-1 -right-1 pointer-events-none group-hover:scale-105 transition-transform" style="color: #f59e0b;">
                <svg class="w-8 h-8 sm:w-11 sm:h-11 md:w-13 md:h-13 opacity-75 sm:opacity-85" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <!-- Top Row -->
            <div class="flex items-center relative z-10">
                <span class="text-[9px] sm:text-[10px] md:text-[11px] font-extrabold tracking-wider uppercase" style="color: #d97706;">PROSES</span>
            </div>
            <!-- Value & Label -->
            <div class="mt-1 sm:mt-1.5 relative z-10">
                <div class="flex items-baseline">
                    <span class="text-base sm:text-xl md:text-2xl font-black tracking-tight leading-none" style="color: #d97706;">
                        {{ number_format($stats['proses'] ?? 0) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] md:text-xs font-semibold text-amber-500/80 ml-0.5 sm:ml-1">tiket</span>
                </div>
                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium text-amber-700/75 mt-0.5 truncate leading-tight">Sedang perbaikan</p>
            </div>
        </div>

        <!-- 4. CLOSE -->
        <div class="relative bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden group p-2 sm:p-3 flex flex-col justify-between min-h-[76px] sm:min-h-[88px]">
            <div class="absolute top-0 left-0 right-0 h-[2.5px] sm:h-[3px]" style="background-color: #10b981;"></div>
            <!-- Card Icon -->
            <div class="absolute -bottom-1 -right-1 pointer-events-none group-hover:scale-105 transition-transform" style="color: #10b981;">
                <svg class="w-8 h-8 sm:w-11 sm:h-11 md:w-13 md:h-13 opacity-75 sm:opacity-85" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <!-- Top Row -->
            <div class="flex items-center relative z-10">
                <span class="text-[9px] sm:text-[10px] md:text-[11px] font-extrabold tracking-wider uppercase" style="color: #059669;">CLOSE</span>
            </div>
            <!-- Value & Label -->
            <div class="mt-1 sm:mt-1.5 relative z-10">
                <div class="flex items-baseline">
                    <span class="text-base sm:text-xl md:text-2xl font-black tracking-tight leading-none" style="color: #059669;">
                        {{ number_format($stats['close'] ?? ($stats['selesai'] ?? 0)) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] md:text-xs font-semibold text-emerald-500/80 ml-0.5 sm:ml-1">tiket</span>
                </div>
                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium text-emerald-700/75 mt-0.5 truncate leading-tight">Selesai &amp; normal</p>
            </div>
        </div>

        <!-- 5. > SLA -->
        <div class="relative bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden group p-2 sm:p-3 flex flex-col justify-between min-h-[76px] sm:min-h-[88px]">
            <div class="absolute top-0 left-0 right-0 h-[2.5px] sm:h-[3px]" style="background-color: #ef4444;"></div>
            <!-- Card Icon -->
            <div class="absolute -bottom-1 -right-1 pointer-events-none group-hover:scale-105 transition-transform" style="color: #ef4444;">
                <svg class="w-8 h-8 sm:w-11 sm:h-11 md:w-13 md:h-13 opacity-75 sm:opacity-85" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <circle cx="12" cy="13" r="8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4l2.5 2.5M10 2h4M12 2v3"/>
                </svg>
            </div>
            <!-- Top Row -->
            <div class="flex items-center relative z-10">
                <span class="text-[9px] sm:text-[10px] md:text-[11px] font-extrabold tracking-wider uppercase" style="color: #dc2626;">&gt; SLA</span>
            </div>
            <!-- Value & Label -->
            <div class="mt-1 sm:mt-1.5 relative z-10">
                <div class="flex items-baseline">
                    <span class="text-base sm:text-xl md:text-2xl font-black tracking-tight leading-none" style="color: #dc2626;">
                        {{ number_format($stats['sla_lebih'] ?? 0) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] md:text-xs font-semibold text-rose-400 ml-0.5 sm:ml-1">tiket</span>
                </div>
                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium text-rose-600/75 mt-0.5 truncate leading-tight">&gt; Target SLA</p>
            </div>
        </div>

        <!-- 6. <= SLA -->
        <div class="relative bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden group p-2 sm:p-3 flex flex-col justify-between min-h-[76px] sm:min-h-[88px]">
            <div class="absolute top-0 left-0 right-0 h-[2.5px] sm:h-[3px]" style="background-color: #0d9488;"></div>
            <!-- Card Icon -->
            <div class="absolute -bottom-1 -right-1 pointer-events-none group-hover:scale-105 transition-transform" style="color: #0d9488;">
                <svg class="w-8 h-8 sm:w-11 sm:h-11 md:w-13 md:h-13 opacity-75 sm:opacity-85" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <!-- Top Row -->
            <div class="flex items-center relative z-10">
                <span class="text-[9px] sm:text-[10px] md:text-[11px] font-extrabold tracking-wider uppercase" style="color: #0f766e;">&le; SLA</span>
            </div>
            <!-- Value & Label -->
            <div class="mt-1 sm:mt-1.5 relative z-10">
                <div class="flex items-baseline">
                    <span class="text-base sm:text-xl md:text-2xl font-black tracking-tight leading-none" style="color: #0f766e;">
                        {{ number_format($stats['sla_tepat'] ?? 0) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] md:text-xs font-semibold text-teal-500/80 ml-0.5 sm:ml-1">tiket</span>
                </div>
                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-medium text-teal-700/75 mt-0.5 truncate leading-tight">&le; Target SLA</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar (Collapsible Accordion / Dropdown) -->
    @php
        $hasActiveFilter = request()->hasAny(['kategori', 'status', 'priority', 'assigned']);
    @endphp
    <div x-data="{ openFilter: {{ $hasActiveFilter ? 'true' : 'false' }} }" class="bg-white rounded-2xl border border-slate-200/90 p-3.5 sm:p-4 shadow-xs space-y-3">
        <!-- Search Input Bar & Filter Toggle Button -->
        <form method="GET" action="{{ route('tickets.index') }}" class="space-y-3">
            <div class="flex items-center gap-2 sm:gap-3 w-full">
                <!-- Search Input Bar (Full Width) -->
                <div class="relative flex-1 min-w-0">
                    <input type="text" name="q" value="{{ request('q') }}" 
                           placeholder="Cari No Tiket / Pelanggan / CID / Segmen Backbone..."
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 pl-9 pr-8 py-2.5 text-slate-700 transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    @if(request('q'))
                        <a href="{{ route('tickets.index', request()->except('q')) }}" 
                           class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-rose-500 transition cursor-pointer" 
                           title="Hapus pencarian">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
                
                <!-- Tombol Filter (Sedikit lebih panjang & proporsional) -->
                <button type="button" @click="openFilter = !openFilter"
                        class="px-4 sm:px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm border border-slate-200 hover:bg-slate-50 text-slate-700 transition inline-flex items-center justify-center gap-2 shrink-0 cursor-pointer min-w-[90px] sm:min-w-[110px]"
                        :class="openFilter ? 'bg-teal-50 text-teal-700 border-teal-200 shadow-2xs' : 'bg-white'">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                    @if($hasActiveFilter)
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    @endif
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openFilter ? 'rotate-180 text-teal-600' : 'text-slate-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Tombol Cari (Sedikit lebih panjang & proporsional) -->
                <button type="submit" 
                        class="px-5 sm:px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-xs cursor-pointer transition inline-flex items-center justify-center gap-2 shrink-0 min-w-[95px] sm:min-w-[115px]"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari</span>
                </button>
            </div>

            <!-- Collapsible Dropdown Content: Filter Selects + Full Width Action Buttons -->
            <div x-show="openFilter" x-collapse x-cloak class="space-y-3 pt-2 border-t border-slate-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                    <!-- Select Kategori -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Kategori</label>
                        <select name="kategori" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white focus:bg-white focus:border-teal-500 py-2 px-3 font-semibold text-slate-700 transition">
                            <option value="">Semua Kategori</option>
                            <option value="BACKBONE" {{ request('kategori') === 'BACKBONE' ? 'selected' : '' }}>Backbone (BDG-)</option>
                            <option value="BROADBAND" {{ request('kategori') === 'BROADBAND' ? 'selected' : '' }}>Broadband (BRD-)</option>
                        </select>
                    </div>

                    <!-- Select Status -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Status</label>
                        <select name="status" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white focus:bg-white focus:border-teal-500 py-2 px-3 font-semibold text-slate-700 transition">
                            <option value="">Semua Status</option>
                            <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
                            <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES</option>
                            <option value="PENDING_VERIFIKASI" {{ request('status') === 'PENDING_VERIFIKASI' ? 'selected' : '' }}>PENDING VERIFIKASI</option>
                            <option value="CLOSE" {{ request('status') === 'CLOSE' ? 'selected' : '' }}>CLOSE</option>
                            <option value="REOPENED" {{ request('status') === 'REOPENED' ? 'selected' : '' }}>REOPENED</option>
                            <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                        </select>
                    </div>

                    <!-- Select Prioritas -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Prioritas</label>
                        <select name="priority" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white focus:bg-white focus:border-teal-500 py-2 px-3 font-semibold text-slate-700 transition">
                            <option value="">Semua Prioritas</option>
                            <option value="P1" {{ request('priority') === 'P1' ? 'selected' : '' }}>P1 (Kritis)</option>
                            <option value="P2" {{ request('priority') === 'P2' ? 'selected' : '' }}>P2 (Mayor)</option>
                            <option value="P3" {{ request('priority') === 'P3' ? 'selected' : '' }}>P3 (Minor)</option>
                            <option value="P4" {{ request('priority') === 'P4' ? 'selected' : '' }}>P4 (Terencana)</option>
                        </select>
                    </div>

                    <!-- Select Penugasan (Ditugaskan ke Saya) -->
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Penugasan</label>
                        <select name="assigned" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white focus:bg-white focus:border-teal-500 py-2 px-3 font-semibold text-slate-700 transition">
                            <option value="">Semua Penugasan</option>
                            <option value="me" {{ request('assigned') === 'me' ? 'selected' : '' }}>Ditugaskan ke Saya</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Actions: Full Width Buttons Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter</span>
                    </button>

                    @if(request()->hasAny(['kategori', 'status', 'priority', 'assigned', 'q']))
                        <a href="{{ route('tickets.index') }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer" title="Hapus Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset Filter</span>
                        </a>
                    @else
                        <a href="{{ route('tickets.index') }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer" title="Refresh">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Refresh</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Ticket Cards Grid with Status-Colored Border & Compact Layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
        @forelse($tickets as $ticket)
            @php
                // Status Theme Colors: Left Stripe, Border Accent & Live Pulsing Dot
                $statusStripeBg = match($ticket->status) {
                    'CLOSE' => 'bg-emerald-500',
                    'PROSES' => 'bg-amber-400',
                    'PENDING_VERIFIKASI' => 'bg-purple-500',
                    'REOPENED' => 'bg-rose-500',
                    'CANCELLED' => 'bg-slate-400',
                    default => 'bg-teal-500', // OPEN
                };

                $statusBorderClass = match($ticket->status) {
                    'CLOSE' => 'border-emerald-200/90 hover:border-emerald-400/90 hover:shadow-emerald-500/5',
                    'PROSES' => 'border-amber-200/90 hover:border-amber-400/90 hover:shadow-amber-500/5',
                    'PENDING_VERIFIKASI' => 'border-purple-200/90 hover:border-purple-400/90 hover:shadow-purple-500/5',
                    'REOPENED' => 'border-rose-200/90 hover:border-rose-400/90 hover:shadow-rose-500/5',
                    'CANCELLED' => 'border-slate-200/90 hover:border-slate-400/90',
                    default => 'border-teal-200/90 hover:border-teal-400/90 hover:shadow-teal-500/5', // OPEN
                };

                $statusBadgeClass = match($ticket->status) {
                    'CLOSE' => 'bg-emerald-50 text-emerald-800 border-emerald-200/90',
                    'PROSES' => 'bg-amber-50 text-amber-800 border-amber-200/90',
                    'PENDING_VERIFIKASI' => 'bg-purple-50 text-purple-800 border-purple-200/90',
                    'REOPENED' => 'bg-rose-50 text-rose-800 border-rose-200/90',
                    'CANCELLED' => 'bg-slate-100 text-slate-700 border-slate-200',
                    default => 'bg-teal-50 text-teal-800 border-teal-200/90', // OPEN
                };

                $statusDotBg = match($ticket->status) {
                    'CLOSE' => 'bg-emerald-500',
                    'PROSES' => 'bg-amber-500',
                    'PENDING_VERIFIKASI' => 'bg-purple-500',
                    'REOPENED' => 'bg-rose-500',
                    'CANCELLED' => 'bg-slate-400',
                    default => 'bg-teal-500', // OPEN
                };

                $lead = $ticket->assignees->firstWhere('pivot.role_in_team', 'lead');
                $slaHours = round($ticket->sla_target_minutes / 60, 1);
            @endphp
            <div class="relative bg-white rounded-2xl border {{ $statusBorderClass }} shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                <!-- Status Accent Stripe on Left Border (Slim 4px, clipped smoothly by rounded-2xl) -->
                <div class="absolute top-0 bottom-0 left-0 w-1 {{ $statusStripeBg }}"></div>

                <div class="p-3.5 pl-4 sm:p-4 sm:pl-4.5 space-y-2.5">
                    <!-- Row 1: Category, Priority & Live Status Badges -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            @if($ticket->kategori_tiket === 'BACKBONE')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9.5px] font-bold font-mono tracking-wider uppercase bg-cyan-50/90 text-cyan-800 border border-cyan-200/80">
                                    <svg class="w-2.5 h-2.5 text-cyan-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>BACKBONE</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9.5px] font-bold font-mono tracking-wider uppercase bg-emerald-50/90 text-emerald-800 border border-emerald-200/80">
                                    <svg class="w-2.5 h-2.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                    <span>BROADBAND</span>
                                </span>
                            @endif

                            <span class="px-1.5 py-0.5 rounded-md text-[9.5px] font-bold
                                {{ $ticket->priority === 'P1' ? 'bg-rose-50 text-rose-700 border border-rose-200/80' : ($ticket->priority === 'P2' ? 'bg-amber-50 text-amber-800 border border-amber-200/80' : ($ticket->priority === 'P3' ? 'bg-sky-50 text-sky-800 border border-sky-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200')) }}">
                                {{ $ticket->priority }}
                            </span>
                        </div>

                        <!-- Live Status Pill with Pulsing Indicator Dot -->
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider border shrink-0 {{ $statusBadgeClass }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDotBg }} {{ $ticket->status === 'PROSES' || $ticket->status === 'OPEN' ? 'animate-pulse' : '' }}"></span>
                            <span>{{ str_replace('_', ' ', $ticket->status) }}</span>
                        </span>
                    </div>

                    <!-- Row 2: Ticket Number & Problem Context -->
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-heading font-black text-sm sm:text-[15px] text-slate-900 group-hover:text-[#0D9488] transition tracking-tight">
                                {{ $ticket->no_tiket }}
                            </a>
                            <span class="text-[10px] font-mono font-medium text-slate-400 bg-slate-100/80 px-1.5 py-0.2 rounded shrink-0">#{{ $ticket->id }}</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-600 truncate mt-0.5">
                            @if($ticket->kategori_tiket === 'BACKBONE')
                                <span class="text-slate-400 font-normal">Segmen:</span> <span class="text-slate-800 font-medium">{{ $ticket->backbone_segment ?? '-' }}</span>
                            @else
                                <span class="text-slate-400 font-normal">Pelanggan:</span> <span class="text-slate-800 font-medium">{{ $ticket->customer_name ?? '-' }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Row 3: Refined Data Strip (Dampak & Area + Waktu Buka) -->
                    <div class="bg-slate-50/70 rounded-xl px-3 py-2 border border-slate-100/90 flex items-center justify-between gap-2 text-[11px]">
                        @if($ticket->kategori_tiket === 'BACKBONE')
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">Dampak &amp; Area</span>
                                <div class="flex items-center gap-1.5 mt-0.5 truncate">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $ticket->status_link_impact === 'TOTAL_DOWN' ? 'bg-rose-500 animate-pulse' : 'bg-amber-500' }}"></span>
                                    <span class="font-bold text-[11px] truncate {{ $ticket->status_link_impact === 'TOTAL_DOWN' ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ str_replace('_', ' ', $ticket->status_link_impact ?? '-') }}
                                    </span>
                                    <span class="text-slate-400 text-[10px]">({{ $ticket->wilayah ?? '-' }})</span>
                                </div>
                            </div>
                        @else
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">CID / ODP</span>
                                <div class="font-mono font-semibold text-slate-700 text-[11px] truncate mt-0.5">
                                    {{ $ticket->customer_id ?? 'Non-CID' }} <span class="text-slate-300">&bull;</span> {{ $ticket->customer_odp ?? '-' }}
                                </div>
                            </div>
                        @endif

                        <div class="text-right shrink-0">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block leading-tight">Waktu Buka</span>
                            <span class="font-mono text-slate-600 text-[10.5px] block mt-0.5">
                                {{ $ticket->tanggal_open ? $ticket->tanggal_open->timezone('Asia/Jakarta')->format('d M, H:i') : '-' }} WIB
                            </span>
                        </div>
                    </div>

                    <!-- Row 4: Compact SLA Status & Target -->
                    <div class="flex items-center justify-between gap-2 text-[11px] pt-0.5">
                        <span class="text-slate-500 font-medium">
                            Target SLA: <strong class="text-slate-800 font-mono font-bold">{{ $ticket->sla_target_minutes }}m</strong> <span class="text-slate-400 text-[10px]">({{ $slaHours }}j)</span>
                        </span>

                        @if($ticket->activeStopClock)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 border border-amber-200/80 text-amber-800 text-[10px] font-bold truncate max-w-[150px]" title="Jeda: {{ $ticket->activeStopClock->reason }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping shrink-0"></span>
                                <span class="truncate">Jeda: {{ $ticket->activeStopClock->reason }}</span>
                            </span>
                        @elseif($ticket->isOverdue() && $ticket->status !== 'CLOSE')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-50 border border-rose-200/80 text-rose-700 text-[10px] font-bold shrink-0">
                                <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Overdue SLA</span>
                            </span>
                        @elseif($ticket->sla_status)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $ticket->sla_status === 'TEPAT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' }}">
                                <span>{{ $ticket->sla_status === 'TEPAT' ? '✓' : '✕' }} SLA {{ $ticket->sla_status }} ({{ $ticket->net_mttr_minutes }}m)</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200/70 text-[10px] font-semibold shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500 shrink-0"></span>
                                <span>On-Track</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Card Footer: PIC & Detail Action -->
                <div class="bg-slate-50/70 px-3.5 py-2.5 pl-4 sm:px-4 sm:py-3 sm:pl-4.5 border-t border-slate-100 flex items-center justify-between gap-3 text-[11px]">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="text-slate-700 font-semibold truncate max-w-[140px] text-[11px]">
                            {{ $lead ? $lead->name : 'Belum ditugaskan' }}
                        </span>
                        @if($ticket->assignees->count() > 1)
                            <span class="px-1.5 py-0.2 rounded-full bg-slate-200/70 text-slate-600 text-[9px] font-bold shrink-0" title="{{ $ticket->assignees->count() }} Anggota Tim">
                                +{{ $ticket->assignees->count() - 1 }}
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('tickets.show', $ticket) }}"
                       class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl font-extrabold text-xs sm:text-[13px] text-white shadow-sm shadow-teal-950/20 hover:shadow-md hover:shadow-teal-600/30 hover:brightness-105 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer shrink-0"
                       style="background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%);">
                        <span>Detail</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center text-slate-500">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-[#0D9488] mx-auto flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h4 class="font-heading font-bold text-base text-slate-800">Tidak ada tiket gangguan ditemukan</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Kriteria pencarian atau filter yang dipilih tidak memiliki data tiket yang cocok.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <a href="{{ route('tickets.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                        Reset Filter
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL BUKA TIKET BACKBONE (BDG-) -->
<!-- ========================================================================= -->
<div id="modal-backbone" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header Banner -->
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 30%, #0a525f 70%, #084c57 100%);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-[#5eead4]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-base sm:text-lg text-white">Buka Tiket Backbone FO</h3>
                    <p class="text-xs text-teal-200/80">Format nomor otomatis: <span class="font-mono text-[#5eead4] font-bold">BDG-YYYYMMDD-XXX</span></p>
                </div>
            </div>
            <button type="button" onclick="closeBackboneModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.backbone.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Segmen Backbone *</label>
                <input type="text" name="backbone_segment" required placeholder="Contoh: BDG-SRE-01 / Jalur Transmisi Selatan"
                       list="segment-list"
                       class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                <datalist id="segment-list">
                    @foreach($slaMatrix->where('kategori_tiket', 'BACKBONE') as $s)
                        <option value="{{ $s->backbone_segment }}">{{ $s->backbone_segment }} (SLA: {{ $s->sla_minutes }}m)</option>
                    @endforeach
                </datalist>
                <p class="text-[11px] text-slate-400 mt-1">SLA otomatis ditentukan berdasarkan segmen dan tingkat prioritas.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Dampak Link *</label>
                    <select name="status_link_impact" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        <option value="TOTAL_DOWN">TOTAL DOWN (Layanan Padam Penuh)</option>
                        <option value="DEGRADED">DEGRADED (Kualitas Turun / Redaman Tinggi)</option>
                        <option value="NO_IMPACT">NO IMPACT (Proteksi / Kabel Cadangan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prioritas Penanganan *</label>
                    <select name="priority" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        <option value="P1">P1 - Kritis (SLA Standar 180m)</option>
                        <option value="P2" selected>P2 - Mayor (SLA Standar 360m)</option>
                        <option value="P3">P3 - Minor (SLA Standar 480m)</option>
                        <option value="P4">P4 - Terencana (SLA Standar 720m)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Wilayah / Area *</label>
                    <input type="text" name="wilayah" required placeholder="Contoh: Soreang / Bandung Selatan"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipe Penanganan Teknis *</label>
                    <input type="text" name="tipe_penanganan" required placeholder="Contoh: Splicing / Cut FO / Bending"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Indikasi Masalah / Problem Awal</label>
                <textarea name="notes" rows="2" placeholder="Catatan indikasi alarm NMS, hasil OTDR awal, atau keterangan tim..."
                          class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2 text-slate-800 transition"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeBackboneModal()"
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md shadow-teal-950/20 cursor-pointer transition"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    Simpan &amp; Terbitkan Tiket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL BUKA TIKET BROADBAND (BRD-) DENGAN LIVE IMS SEARCH -->
<!-- ========================================================================= -->
<div id="modal-broadband" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header Banner -->
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 30%, #0a525f 70%, #084c57 100%);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.393 9.929c5.857-5.858 15.355-5.858 21.213 0"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-base sm:text-lg text-white">Buka Tiket Broadband Maintenance</h3>
                    <p class="text-xs text-teal-200/80">Format nomor otomatis: <span class="font-mono text-emerald-300 font-bold">BRD-YYYYMMDD-XXX</span></p>
                </div>
            </div>
            <button type="button" onclick="closeBroadbandModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.broadband.store') }}" class="p-6 space-y-4">
            @csrf
            <!-- Live IMS Search Card -->
            <div class="bg-teal-50/60 rounded-2xl p-3.5 border border-teal-200/60 space-y-2">
                <label class="block text-xs font-bold text-teal-900 uppercase tracking-wider">Cari Data Pelanggan di IMS (Opsional)</label>
                <div class="flex gap-2">
                    <input type="text" id="input_cid" name="customer_id" placeholder="Masukkan CID (contoh: CUST-10023 / nama)"
                           class="w-full text-xs sm:text-sm rounded-xl border border-teal-300 bg-white focus:border-teal-600 focus:ring-teal-600 px-3.5 py-2 text-slate-800 transition">
                    <button type="button" onclick="lookupImsCustomer()"
                            class="px-4 py-2 bg-[#0D9488] hover:bg-[#0b7a70] text-white text-xs font-bold rounded-xl shrink-0 transition cursor-pointer">
                        Cari IMS
                    </button>
                </div>
                <div id="ims_lookup_status" class="text-[11px] text-teal-800 font-medium hidden"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Pelanggan *</label>
                    <input type="text" id="input_cust_name" name="customer_name" required placeholder="Nama lengkap pelanggan"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / Telp *</label>
                    <input type="text" id="input_cust_phone" name="customer_phone" required placeholder="Contoh: 08123456789"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">ODP</label>
                    <input type="text" id="input_cust_odp" name="customer_odp" placeholder="ODP-SRE-01"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3 py-2 text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">OLT / POP</label>
                    <input type="text" id="input_cust_olt" name="customer_olt" placeholder="OLT-SRE-HUA"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3 py-2 text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prioritas *</label>
                    <select name="priority" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3 py-2 text-slate-800 transition">
                        <option value="P1">P1 (Kritis - 180m)</option>
                        <option value="P2" selected>P2 (Standar - 240m)</option>
                        <option value="P3">P3 (Minor - 360m)</option>
                        <option value="P4">P4 (Rendah - 480m)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Pelanggan</label>
                <textarea id="input_cust_address" name="customer_address" rows="2" placeholder="Alamat lengkap lokasi gangguan..."
                          class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2 text-slate-800 transition"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Uraian Kendala / Keluhan Masalah</label>
                <textarea name="notes" rows="2" placeholder="LOS merah / Modem restart terus / Redaman tinggi..."
                          class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2 text-slate-800 transition"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeBroadbandModal()"
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md shadow-emerald-950/20 bg-emerald-600 hover:bg-emerald-700 cursor-pointer transition">
                    Simpan &amp; Terbitkan Tiket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBackboneModal() {
    document.getElementById('modal-backbone').classList.remove('hidden');
}
function closeBackboneModal() {
    document.getElementById('modal-backbone').classList.add('hidden');
}
function openBroadbandModal() {
    document.getElementById('modal-broadband').classList.remove('hidden');
}
function closeBroadbandModal() {
    document.getElementById('modal-broadband').classList.add('hidden');
}

// Live lookup ke IMS (§7.1, AC-11)
function lookupImsCustomer() {
    const cidInput = document.getElementById('input_cid');
    const statusDiv = document.getElementById('ims_lookup_status');
    const term = cidInput.value.trim();

    if (!term) {
        statusDiv.classList.remove('hidden');
        statusDiv.className = 'text-[11px] text-amber-600 font-medium';
        statusDiv.innerText = 'Silakan masukkan nomor CID atau nama pelanggan.';
        return;
    }

    statusDiv.classList.remove('hidden');
    statusDiv.className = 'text-[11px] text-teal-700 font-medium';
    statusDiv.innerText = 'Mencari di database IMS...';

    fetch(`{{ route('tickets.ims.search') }}?q=${encodeURIComponent(term)}`)
        .then(res => res.json())
        .then(data => {
            if (data.available && data.customers && data.customers.length > 0) {
                const c = data.customers[0];
                document.getElementById('input_cust_name').value = c.nama || '';
                document.getElementById('input_cust_phone').value = c.telepon || '';
                document.getElementById('input_cust_odp').value = c.odp || '';
                document.getElementById('input_cust_olt').value = c.pop || '';
                document.getElementById('input_cust_address').value = c.alamat || '';

                statusDiv.className = 'text-[11px] text-emerald-700 font-bold';
                statusDiv.innerText = `✓ Ditemukan di IMS: ${c.nama} (${c.id_pelanggan}) - Paket: ${c.bandwidth || '-'}`;
            } else {
                statusDiv.className = 'text-[11px] text-amber-700 font-bold';
                statusDiv.innerText = `⚠️ Data tidak ditemukan di IMS atau server IMS offline. Anda tetap dapat melanjutkan secara manual (AC-11).`;
            }
        })
        .catch(err => {
            statusDiv.className = 'text-[11px] text-amber-700 font-bold';
            statusDiv.innerText = `⚠️ Tidak dapat menghubungi server IMS. Mode pengisian manual aktif (AC-11).`;
        });
}
</script>
@endsection
