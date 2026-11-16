@extends('layouts.app', ['title' => 'Tiket ' . $tiket->no_tiket . ' - Detail & Penanganan'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-16">
    @php
        $stripe = $tiket->stripeColor();
        $stripeBg = match($stripe) {
            'red' => 'bg-red-500',
            'yellow' => 'bg-amber-400',
            'green' => 'bg-emerald-500',
            'gray' => 'bg-slate-400',
            default => 'bg-teal-500',
        };
        $lead = $tiket->assignees->firstWhere('pivot.role_in_team', 'lead');
        $crew = $tiket->assignees->where('pivot.role_in_team', 'member');

        // Prerequisites calculation for Closing Readiness Checklist (matching Image 2)
        $hasResume = !empty(trim((string)$tiket->problem_temuan)) && !empty(trim((string)$tiket->action_taken));
        $hasPhoto = $tiket->chronologies->whereNotNull('photo_path')->isNotEmpty() || $tiket->chronologies->where('category', 'SELESAI')->isNotEmpty();
        $hasRepairOrJc = $tiket->repairPoints->isNotEmpty() || $tiket->jointClosures->isNotEmpty();
        $hasJcOrManeuver = $tiket->jointClosures->isNotEmpty() || $tiket->coreManeuvers->isNotEmpty();

        $fulfilledCount = ($hasResume ? 1 : 0) + ($hasPhoto ? 1 : 0) + ($hasRepairOrJc ? 1 : 0) + ($hasJcOrManeuver ? 1 : 0);
        $percentage = $fulfilledCount * 25;
        $remainingCount = 4 - $fulfilledCount;
    @endphp

    <!-- ========================================================================= -->
    <!-- 1. TOP HEADER BANNER (Theme Ocean Teal Gradient - Compact Sleek) -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-2xl md:rounded-3xl px-5 py-4 sm:px-6 sm:py-5 text-white shadow-lg border border-teal-500/20"
         style="background: linear-gradient(135deg, #022836 0%, #053b47 30%, #0a525f 70%, #084c57 100%);">
        
        <!-- Ambient decorative glows -->
        <div class="absolute -right-16 -top-16 w-60 h-60 rounded-full bg-[#18C4C4]/15 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-20 w-64 h-64 rounded-full bg-[#0D9488]/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="space-y-3 max-w-3xl">
                <!-- Badges Row: Ticket No, Category, Status -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <!-- Ticket Number Pill -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-mono font-bold bg-white/10 backdrop-blur-md border border-white/20 text-[#5eead4] shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#5eead4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>{{ $tiket->no_tiket }}</span>
                    </span>

                    <!-- Category Pill -->
                    <span class="px-3 py-1 rounded-full font-bold font-mono uppercase tracking-wider text-[11px]
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? 'bg-[#18C4C4] text-slate-900' : 'bg-emerald-400 text-slate-900' }}">
                        {{ $tiket->kategori_tiket }}
                    </span>

                    <!-- Status Pill -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold uppercase tracking-wider text-[11px]
                        {{ $tiket->status === 'CLOSE' ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-400/50' : ($tiket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-500/30 text-purple-200 border border-purple-400/50' : ($tiket->status === 'PROSES' ? 'bg-amber-500/30 text-amber-200 border border-amber-400/50' : 'bg-cyan-500/30 text-cyan-200 border border-cyan-400/50')) }}">
                        <span class="w-2 h-2 rounded-full {{ $tiket->status === 'CLOSE' ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                        {{ $tiket->status }}
                    </span>

                    @if($tiket->priority)
                        <span class="px-2.5 py-1 rounded-full font-bold text-[11px] bg-white/10 border border-white/15 text-white">
                            {{ $tiket->priority }}
                        </span>
                    @endif
                </div>

                <!-- Assigned Crew Pills Row (matching Image 1) -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                    @if($lead)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold bg-[#18C4C4]/20 border border-[#18C4C4]/40 text-[#5eead4]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>PIC: {{ $lead->name }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold bg-amber-500/20 border border-amber-500/40 text-amber-300">
                            <span>PIC: Belum Ditugaskan</span>
                        </span>
                    @endif

                    @foreach($crew as $c)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-medium bg-white/10 border border-white/15 text-white/90">
                            <svg class="w-3 h-3 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ $c->name }}</span>
                        </span>
                    @endforeach

                    @if($tiket->assignees->isEmpty())
                        @if(auth()->id() !== $tiket->created_by && !auth()->user()->hasRole(['noc', 'atasan_noc']) && auth()->user()->division !== 'NOC')
                            @can('dispatch', $tiket)
                                <button type="button" onclick="openDispatchModal()"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full font-bold text-xs bg-amber-400 text-slate-900 hover:bg-amber-300 transition cursor-pointer">
                                    + Tugaskan Tim (Atasan)
                                </button>
                            @endcan
                        @endif
                    @endif
                </div>

                <!-- Big Headline (Status Impact / Title) -->
                <h1 class="text-xl sm:text-2xl font-heading font-extrabold text-white tracking-tight pt-0.5">
                    {{ $tiket->status_link_impact ?? ($tiket->kategori_tiket === 'BACKBONE' ? $tiket->backbone_segment : $tiket->customer_name) }}
                </h1>
            </div>

            <!-- Header Actions: ← Kembali & :: Menu Aksi Dropdown -->
            <div class="flex items-center gap-2.5 shrink-0 self-start lg:self-center">
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white/90 bg-white/10 hover:bg-white/20 border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>

                <!-- Menu Aksi Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-lg border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer"
                            style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span>Menu Aksi</span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-2xl border border-slate-200/90 py-2 z-50 text-slate-800 divide-y divide-slate-100">
                        
                        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Aksi Tiket
                        </div>

                        <div class="py-1">
                            @if(auth()->id() !== $tiket->created_by && !auth()->user()->hasRole(['noc', 'atasan_noc']) && auth()->user()->division !== 'NOC')
                                @can('dispatch', $tiket)
                                    <button type="button" @click="open = false; openDispatchModal()"
                                            class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-teal-50 hover:text-teal-900 transition flex items-center gap-2.5">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>Penugasan Tim (Atasan)</span>
                                    </button>
                                @endcan
                            @endif

                            @if($tiket->status !== 'CLOSE' && $tiket->status !== 'CANCELLED')
                                <button type="button" @click="open = false; openStopClockModal()"
                                        class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-teal-50 hover:text-teal-900 transition flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Jeda SLA (Stop Clock)</span>
                                </button>

                                <button type="button" @click="open = false; switchTab('tab-resume')"
                                        class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-teal-50 hover:text-teal-900 transition flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Input Resume &amp; Closing</span>
                                </button>
                            @endif

                            @if($tiket->status === 'PENDING_VERIFIKASI')
                                @can('ticket.verify_close')
                                    <button type="button" @click="open = false; openVerifyCloseModal()"
                                            class="w-full text-left px-3.5 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition flex items-center gap-2.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Verifikasi Closing (NOC)</span>
                                    </button>
                                @endcan
                            @endif
                        </div>

                        <div class="py-1">
                            <a href="{{ route('gomsn.tickets.berita-acara', $tiket) }}" target="_blank"
                               class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-teal-50 hover:text-teal-900 transition flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak Berita Acara (PDF)</span>
                            </a>

                            @if($tiket->status === 'CLOSE')
                                <button type="button" @click="open = false; openReopenModal()"
                                        class="w-full text-left px-3.5 py-2 text-xs font-bold text-amber-700 hover:bg-amber-50 transition flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Reopen Tiket</span>
                                </button>
                            @elseif($tiket->status !== 'CANCELLED')
                                <button type="button" @click="open = false; openCancelModal()"
                                        class="w-full text-left px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50 transition flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>Batalkan Tiket</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-800 font-bold shrink-0">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs sm:text-sm space-y-1.5 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Perhatian:</span>
            </div>
            <ul class="list-disc list-inside pl-4 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. FOUR KEY INFO METRIC CARDS (2 Baris di Mobile dengan Dropdown Toggle) -->
    <!-- ========================================================================= -->
    <div x-data="{ showParamCards: true }" class="space-y-2.5">
        <!-- Mobile Dropdown / Accordion Bar (hanya di layar mobile < sm) -->
        <div class="sm:hidden">
            <button type="button" 
                    @click="showParamCards = !showParamCards"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:bg-slate-50 transition active:scale-[0.99]">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-teal-50 text-[#0D9488] flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <span class="text-xs font-heading font-extrabold text-slate-800">Parameter Tiket (4 Info)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-[#0D9488]">2 Baris</span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-500">
                    <span class="text-[11px] font-semibold" x-text="showParamCards ? 'Sembunyikan' : 'Lihat Card'"></span>
                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': showParamCards }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
        </div>

        <!-- 4 CARDS: 2 Baris di Mobile (grid-cols-2), 1 Baris di Desktop (lg:grid-cols-4) -->
        <div x-show="showParamCards" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            
            <!-- Card 1: SEGMENT BACKBONE / PELANGGAN -->
            <div class="bg-white rounded-2xl p-3 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#0D9488] shrink-0">
                    @if($tiket->kategori_tiket === 'BACKBONE')
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @else
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? 'Segment Backbone' : 'Pelanggan Broadband' }}
                    </span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 truncate mt-0.5" title="{{ $tiket->kategori_tiket === 'BACKBONE' ? ($tiket->backbone_segment ?? '-') : ($tiket->customer_name ?? '-') }}">
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? ($tiket->backbone_segment ?? '-') : ($tiket->customer_name ?? '-') }}
                    </p>
                    <span class="text-[10px] sm:text-[11px] text-slate-400 truncate block">
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? ($tiket->wilayah ?? 'Area Bandung') : ('CID: ' . ($tiket->customer_id ?? 'Non-CID')) }}
                    </span>
                </div>
            </div>

            <!-- Card 2: WAKTU OPEN -->
            <div class="bg-white rounded-2xl p-3 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-700 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Waktu Open</span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 mt-0.5 truncate">
                        {{ $tiket->tanggal_open ? $tiket->tanggal_open->format('d M, H:i') : '-' }} <span class="text-[10px] sm:text-xs text-slate-400 font-normal">WIB</span>
                    </p>
                    <span class="text-[10px] sm:text-[11px] text-slate-400 truncate block">
                        {{ $tiket->tanggal_open ? $tiket->tanggal_open->diffForHumans() : '-' }}
                    </span>
                </div>
            </div>

            <!-- Card 3: WAKTU CLOSE -->
            <div class="bg-white rounded-2xl p-3 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Waktu Close</span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 mt-0.5 truncate">
                        @if($tiket->tanggal_close)
                            {{ $tiket->tanggal_close->format('d M, H:i') }} <span class="text-[10px] sm:text-xs text-slate-400 font-normal">WIB</span>
                        @else
                            <span class="text-slate-400 font-semibold italic text-[11px] sm:text-sm">Belum tutup</span>
                        @endif
                    </p>
                    <span class="text-[10px] sm:text-[11px] text-slate-400 truncate block">
                        {{ $tiket->tanggal_close ? $tiket->tanggal_close->diffForHumans() : 'Tiket aktif' }}
                    </span>
                </div>
            </div>

            <!-- Card 4: TARGET SLA & MTTR -->
            <div class="bg-white rounded-2xl p-3 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Target SLA &amp; MTTR</span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 mt-0.5 truncate">
                        Target: {{ round($tiket->sla_target_minutes / 60, 1) }}j
                    </p>
                    <span class="text-[10px] sm:text-[11px] font-semibold truncate block
                        {{ $tiket->status === 'CLOSE' ? ($tiket->sla_status === 'TEPAT' ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-400' }}">
                        MTTR: {{ $tiket->net_mttr_minutes ? round($tiket->net_mttr_minutes / 60, 1) . 'j' : ($tiket->status === 'CLOSE' ? round($tiket->currentNetMinutes() / 60, 1) . 'j' : 'Berjalan...') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. DESKRIPSI GANGGUAN & CATATAN AWAL (matching Image 1) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border-l-4 border-l-[#0D9488] border border-slate-200/80 p-5 shadow-xs space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider">
            <svg class="w-4 h-4 text-[#0D9488]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            <span>Deskripsi Gangguan &amp; Catatan Awal</span>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-6">
            {{ $tiket->problem_temuan ?? 'Tidak ada deskripsi awal gangguan yang dicatat.' }}
        </p>
        <div class="flex items-center gap-2 text-[11px] text-slate-400 pl-6 pt-1 border-t border-slate-100">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Dibuat: <strong class="text-slate-600">{{ $tiket->creator->name ?? 'Helpdesk / NOC' }}</strong></span>
            <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 text-[10px] font-bold">
                {{ $tiket->creator->roles->first()?->name ?? 'NOC' }}
            </span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. GARIS ALUR SLA & SIKLUS HIDUP TIKET (5 Stage Stepper matching Image 1) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-6 shadow-xs space-y-5 sm:space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#0D9488] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-xs sm:text-sm text-slate-800">
                        Garis Alur SLA &amp; Siklus Hidup Tiket
                    </h3>
                    <p class="hidden sm:block text-[11px] text-slate-400">Open &rarr; Respon &rarr; Stop Clock &rarr; Closing Lapangan &rarr; Closing NOC</p>
                </div>
            </div>

            <span class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold font-mono bg-teal-50 text-[#0D9488] border border-teal-200 self-start sm:self-auto">
                Target SLA: {{ round($tiket->sla_target_minutes / 60, 1) }} jam ({{ $tiket->status === 'CLOSE' ? 'Selesai' : 'Aktif' }})
            </span>
        </div>

        @php
            // Hitung persentase progress garis alur (0% - 100%)
            $stepperProgress = 0;
            if ($tiket->status === 'CLOSE') {
                $stepperProgress = 100;
            } elseif ($tiket->closing_awal_at || $tiket->status === 'PENDING_VERIFIKASI') {
                $stepperProgress = 75;
            } elseif ($tiket->total_stop_clock_minutes > 0 || $tiket->activeStopClock) {
                $stepperProgress = 50;
            } elseif ($tiket->first_response_at || $tiket->status === 'PROSES') {
                $stepperProgress = 25;
            } else {
                $stepperProgress = 0;
            }
        @endphp

        <!-- 5-Node Stepper Progress Line: Strict 1-Row with Connecting Bar & Symmetrical Baseline Alignment -->
        <div class="overflow-x-auto pb-2 -mb-2 scrollbar-none">
            <div class="relative min-w-[440px] sm:min-w-0" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));">
                <!-- Track Line (Abu-abu di belakang) -->
                <div class="absolute top-4 sm:top-[18px] h-[2px] bg-slate-200" style="left: 10%; right: 10%; z-index: 0;"></div>
                <!-- Active Filled Progress Line (Hijau gradien) -->
                <div class="absolute top-4 sm:top-[18px] h-[2px] transition-all duration-500 ease-out" 
                     style="left: 10%; width: calc(80% * ({{ $stepperProgress }} / 100)); background: linear-gradient(90deg, #10B981 0%, #0D9488 100%); z-index: 0;"></div>

                <!-- Stage 1: Open Tiket -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-1 space-y-1 sm:space-y-1.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center text-xs sm:text-sm shadow-sm ring-4 ring-white">
                        ✓
                    </div>
                    <div class="min-h-[28px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 leading-tight">Open Tiket</span>
                    </div>
                    <div class="h-4 sm:h-5 flex items-center justify-center">
                        <span class="text-[10px] sm:text-[11px] text-slate-500 font-mono font-medium leading-none">{{ $tiket->tanggal_open ? $tiket->tanggal_open->format('d/m H:i') : '-' }}</span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-50 text-emerald-700 whitespace-nowrap">Stage 1</span>
                    </div>
                </div>

                <!-- Stage 2: Respon Pertama -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-1 space-y-1 sm:space-y-1.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-xs sm:text-sm shadow-sm ring-4 ring-white
                        {{ $tiket->first_response_at ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->first_response_at ? '✓' : '2' }}
                    </div>
                    <div class="min-h-[28px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 leading-tight">Respon Pertama</span>
                    </div>
                    <div class="h-4 sm:h-5 flex items-center justify-center">
                        <span class="text-[10px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->first_response_at ? $tiket->first_response_at->format('d/m H:i') : 'Menunggu' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->first_response_at ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->first_response_at ? 'Terespon' : 'Pending' }}
                        </span>
                    </div>
                </div>

                <!-- Stage 3: Jeda SLA (Stop Clock) -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-1 space-y-1 sm:space-y-1.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-xs sm:text-sm shadow-sm ring-4 ring-white
                        {{ $tiket->total_stop_clock_minutes > 0 || $tiket->activeStopClock ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->activeStopClock ? '⏸' : ($tiket->total_stop_clock_minutes > 0 ? '✓' : '3') }}
                    </div>
                    <div class="min-h-[28px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 leading-tight">Jeda SLA</span>
                    </div>
                    <div class="h-4 sm:h-5 flex items-center justify-center">
                        <span class="text-[10px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->total_stop_clock_minutes > 0 ? $tiket->total_stop_clock_minutes . 'm jeda' : ($tiket->activeStopClock ? 'Berjalan' : 'Menunggu') }}
                        </span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->activeStopClock ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->activeStopClock ? 'Aktif' : 'Normal' }}
                        </span>
                    </div>
                </div>

                <!-- Stage 4: Closing Awal Lapangan -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-1 space-y-1 sm:space-y-1.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-xs sm:text-sm shadow-sm ring-4 ring-white
                        {{ $tiket->closing_awal_at ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->closing_awal_at ? '✓' : '4' }}
                    </div>
                    <div class="min-h-[28px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 leading-tight">Closing Lapangan</span>
                    </div>
                    <div class="h-4 sm:h-5 flex items-center justify-center">
                        <span class="text-[10px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->closing_awal_at ? $tiket->closing_awal_at->format('d/m H:i') : 'Menunggu' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->closing_awal_at ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->closing_awal_at ? 'Diajukan' : 'Proses' }}
                        </span>
                    </div>
                </div>

                <!-- Stage 5: Closing Akhir (NOC) -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-1 space-y-1 sm:space-y-1.5">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-xs sm:text-sm shadow-sm ring-4 ring-white
                        {{ $tiket->status === 'CLOSE' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->status === 'CLOSE' ? '✓' : '5' }}
                    </div>
                    <div class="min-h-[28px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 leading-tight">Closing NOC</span>
                    </div>
                    <div class="h-4 sm:h-5 flex items-center justify-center">
                        <span class="text-[10px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->verified_at ? $tiket->verified_at->format('d/m H:i') : 'Menunggu' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->status === 'CLOSE' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->status === 'CLOSE' ? 'Terverifikasi' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. OVERDUE COMPLIANCE ALERT (30-Min SOP Interval - matching Image 2) -->
    <!-- ========================================================================= -->
    @if($intervalStatus === 'OVERDUE' && $tiket->status !== 'CLOSE' && $tiket->status !== 'CANCELLED')
        <div class="rounded-2xl p-4 sm:p-5 bg-rose-50 border-2 border-rose-300 shadow-sm space-y-3">
            <div class="flex items-center gap-2 text-rose-800">
                <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
                <h3 class="font-heading font-black text-xs sm:text-sm uppercase tracking-wider text-rose-900">
                    OVERDUE &gt; 30 MENIT Wajib Kirim Update!
                </h3>
            </div>
            <p class="text-xs text-rose-700">
                Terakhir update: <strong class="text-rose-900">{{ $tiket->chronologies->last()?->created_at?->diffForHumans() ?? 'Belum ada update' }}</strong> &bull; SOP interval koordinasi teknisi maksimal 30 menit.
            </p>
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <div class="flex items-center gap-4 text-xs font-mono">
                    <span class="px-3 py-1 rounded-xl bg-white border border-rose-200 text-rose-800 font-bold">
                        TERAKHIR: {{ $tiket->chronologies->last()?->created_at?->format('H:i') ?? '-' }} WIB
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-white border border-rose-200 text-slate-700">
                        STATUS: OVERDUE INTERVAL
                    </span>
                </div>
                <button type="button" onclick="switchTab('tab-chronology'); document.getElementById('quick-chat-input').focus();"
                        class="px-4 py-2 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Kirim Update Sekarang</span>
                </button>
            </div>
        </div>
    @elseif($intervalStatus === 'STOP_CLOCK')
        <div class="rounded-2xl p-4 bg-cyan-50 border border-cyan-300 text-cyan-900 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-cyan-500 animate-pulse"></span>
                <span>Jeda SLA Aktif (Stop Clock) &mdash; Timer kepatuhan dan SLA dihentikan sementara.</span>
            </div>
            <button type="button" onclick="switchTab('tab-stopclock')" class="text-xs font-bold text-cyan-800 underline">Lihat Detail &rarr;</button>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 6. KESIAPAN CLOSING TIKET BACKBONE (Checklist 4 Syarat - matching Image 2) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#0D9488] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-xs sm:text-sm text-slate-800">
                        Kesiapan Closing Tiket {{ ucfirst(strtolower($tiket->kategori_tiket)) }} ({{ $fulfilledCount }}/4 Terpenuhi ({{ $percentage }}%))
                    </h3>
                    <p class="hidden sm:block text-[11px] text-slate-400">4 syarat mandatori yang wajib dilengkapi teknisi sebelum closing awal dapat diajukan</p>
                </div>
            </div>

            <div>
                @if($fulfilledCount === 4)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span>✓ Siap Closing Awal</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span>⚠️ Belum Lengkap ({{ $remainingCount }} item lagi)</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- 4 Interactive Checklist Cards (2 Baris di Mobile: 2 Kolom x 2 Baris) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3.5">
            <!-- 1. Resume Kerja -->
            <button type="button" onclick="switchTab('tab-resume')"
                    class="p-2.5 sm:p-4 rounded-2xl border transition text-left cursor-pointer flex items-center justify-between group
                    {{ $hasResume ? 'border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50' : 'border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300' }}">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center font-bold text-xs shrink-0
                        {{ $hasResume ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $hasResume ? '✓' : '1' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 truncate">1. Resume Kerja</h4>
                        <p class="text-[10px] sm:text-[11px] mt-0.5 truncate {{ $hasResume ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                            {{ $hasResume ? 'Sudah Lengkap' : 'Belum diisi lengkap' }}
                        </p>
                    </div>
                </div>
                <span class="hidden sm:block text-slate-400 group-hover:text-teal-600 transition text-sm ml-1">&rsaquo;</span>
            </button>

            <!-- 2. Foto / OTDR -->
            <button type="button" onclick="switchTab('tab-chronology')"
                    class="p-2.5 sm:p-4 rounded-2xl border transition text-left cursor-pointer flex items-center justify-between group
                    {{ $hasPhoto ? 'border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50' : 'border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300' }}">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center font-bold text-xs shrink-0
                        {{ $hasPhoto ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $hasPhoto ? '✓' : '2' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 truncate">2. Foto / OTDR</h4>
                        <p class="text-[10px] sm:text-[11px] mt-0.5 truncate {{ $hasPhoto ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                            {{ $hasPhoto ? 'Terunggah' : 'Wajib minimal 1 foto' }}
                        </p>
                    </div>
                </div>
                <span class="hidden sm:block text-slate-400 group-hover:text-teal-600 transition text-sm ml-1">&rsaquo;</span>
            </button>

            <!-- 3. Koordinat / JC -->
            <button type="button" onclick="switchTab('tab-materials')"
                    class="p-2.5 sm:p-4 rounded-2xl border transition text-left cursor-pointer flex items-center justify-between group
                    {{ $hasRepairOrJc ? 'border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50' : 'border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300' }}">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center font-bold text-xs shrink-0
                        {{ $hasRepairOrJc ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $hasRepairOrJc ? '✓' : '3' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 truncate">3. Koordinat / JC</h4>
                        <p class="text-[10px] sm:text-[11px] mt-0.5 truncate {{ $hasRepairOrJc ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                            {{ $hasRepairOrJc ? ($tiket->repairPoints->count() + $tiket->jointClosures->count()) . ' Titik/JC' : 'Wajib minimal 1 titik' }}
                        </p>
                    </div>
                </div>
                <span class="hidden sm:block text-slate-400 group-hover:text-teal-600 transition text-sm ml-1">&rsaquo;</span>
            </button>

            <!-- 4. Penanganan Core & JC -->
            <button type="button" onclick="switchTab('tab-penanganan')"
                    class="p-2.5 sm:p-4 rounded-2xl border transition text-left cursor-pointer flex items-center justify-between group
                    {{ $hasJcOrManeuver ? 'border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50' : 'border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300' }}">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center font-bold text-xs shrink-0
                        {{ $hasJcOrManeuver ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $hasJcOrManeuver ? '✓' : '4' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-heading font-extrabold text-[11px] sm:text-xs text-slate-800 truncate">4. Penanganan</h4>
                        <p class="text-[10px] sm:text-[11px] mt-0.5 truncate {{ $hasJcOrManeuver ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                            {{ $hasJcOrManeuver ? ($tiket->jointClosures->count() + $tiket->coreManeuvers->count()) . ' Data' : 'Wajib JC / Manuver' }}
                        </p>
                    </div>
                </div>
                <span class="hidden sm:block text-slate-400 group-hover:text-teal-600 transition text-sm ml-1">&rsaquo;</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 7. THE 6 STRUCTURED TABS (matching Image 2, 4, 5) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-1.5 shadow-xs overflow-x-auto">
        <nav class="flex space-x-1 min-w-max text-xs sm:text-sm font-semibold" aria-label="Tabs">
            <!-- Tab 1: Kronologis -->
            <button type="button" onclick="switchTab('tab-chronology')" id="btn-tab-chronology"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-white font-bold"
                    style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kronologis</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">{{ $tiket->chronologies->count() }}</span>
            </button>

            <!-- Tab 2: Resume -->
            <button type="button" onclick="switchTab('tab-resume')" id="btn-tab-resume"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Resume</span>
                @if($hasResume)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </button>

            <!-- Tab 3: Penanganan Core & JC -->
            <button type="button" onclick="switchTab('tab-penanganan')" id="btn-tab-penanganan"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Penanganan Core &amp; JC</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->jointClosures->count() + $tiket->coreManeuvers->count() }}</span>
            </button>

            <!-- Tab 4: Material & Titik -->
            <button type="button" onclick="switchTab('tab-materials')" id="btn-tab-materials"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Material &amp; Titik</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->materials->count() + $tiket->repairPoints->count() }}</span>
            </button>

            <!-- Tab 5: Dokumentasi -->
            <button type="button" onclick="switchTab('tab-documentation')" id="btn-tab-documentation"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Dokumentasi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->chronologies->whereNotNull('photo_path')->count() }}</span>
            </button>

            <!-- Tab 6: Stop Clock & Shift -->
            <button type="button" onclick="switchTab('tab-stopclock')" id="btn-tab-stopclock"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Stop Clock &amp; Shift</span>
                @if($tiket->activeStopClock)
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                @endif
            </button>
        </nav>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: KRONOLOGIS (WhatsApp-Style Live Chat matching Image 2 & 3) -->
    <!-- ========================================================================= -->
    <div id="tab-chronology" class="tab-pane space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            
            <!-- Dark Ocean Teal Chat Header Banner -->
            <div class="px-6 py-4 text-white flex items-center justify-between"
                 style="background: linear-gradient(135deg, #022836 0%, #053b47 40%, #0a525f 100%);">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#18C4C4]/20 border border-[#18C4C4]/30 flex items-center justify-center text-[#5eead4]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-heading font-extrabold text-sm text-white">Koordinasi Update Lapangan</h3>
                            <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Sync (5s)
                            </span>
                        </div>
                        <p class="text-[11px] text-teal-200/80">Catatan teknis tiket <span class="font-mono text-[#5eead4]">{{ $tiket->no_tiket }}</span></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="getLiveGpsCoordinates()" class="text-xs text-teal-200 hover:text-white flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span id="gps_label">Sertakan GPS</span>
                    </button>
                </div>
            </div>

            <!-- WhatsApp Chat Body with Bubble Thread -->
            <div id="chat-stream-box" class="p-4 sm:p-6 space-y-4 max-h-[520px] overflow-y-auto bg-slate-50/70"
                 style="background-image: radial-gradient(#0D9488 0.5px, transparent 0.5px), radial-gradient(#0D9488 0.5px, #f8fafc 0.5px); background-size: 20px 20px; background-position: 0 0, 10px 10px; background-opacity: 0.05;">
                
                <!-- Date divider -->
                <div class="flex justify-center">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/90 text-slate-500 shadow-xs border border-slate-200">
                        {{ $tiket->tanggal_open ? $tiket->tanggal_open->isoFormat('dddd, D MMMM Y') : 'Hari Ini' }}
                    </span>
                </div>

                <!-- Initial Ticket Creation System Note -->
                <div class="flex justify-start">
                    <div class="max-w-xl bg-white rounded-2xl rounded-tl-xs p-4 shadow-xs border border-slate-200 text-xs space-y-1.5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                <span>{{ $tiket->creator->name ?? 'Helpdesk' }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-teal-100 text-teal-800 font-bold">NOC</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $tiket->tanggal_open ? $tiket->tanggal_open->format('H:i') : '' }} WIB</span>
                        </div>
                        <p class="text-slate-700 leading-relaxed font-semibold">
                            🎫 <strong>OPEN TIKET BARU</strong>: Tiket dibuat dengan status OPEN. Kendala: {{ $tiket->problem_temuan ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Dispatch Note if available -->
                @if($lead)
                    <div class="flex justify-start">
                        <div class="max-w-xl bg-amber-50/90 rounded-2xl rounded-tl-xs p-4 shadow-xs border border-amber-200 text-xs space-y-1.5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-amber-200 text-amber-900 font-bold flex items-center justify-center text-[10px]">MT</span>
                                    <span>Manager Teknis</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-amber-200 text-amber-900 font-bold">DISPATCH</span>
                                </span>
                                <span class="text-[10px] text-amber-700 font-mono">{{ $lead->pivot->assigned_at ? \Carbon\Carbon::parse($lead->pivot->assigned_at)->format('H:i') : '' }} WIB</span>
                            </div>
                            <p class="text-amber-950 leading-relaxed">
                                👷 <strong>DISPATCH / PENUGASAN TIM</strong>: Ditugaskan <strong>{{ $lead->name }}</strong> (Lead) bersama tim: 
                                {{ $crew->pluck('name')->join(', ') ?: 'mandiri' }} untuk menangani perbaikan tiket ini.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Chronology Entries List -->
                @forelse($tiket->chronologies as $chron)
                    @php
                        $isSelf = auth()->check() && $chron->user_id === auth()->id();
                    @endphp
                    <div class="flex {{ $isSelf ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xl rounded-2xl p-4 shadow-xs border text-xs space-y-2
                            {{ $isSelf ? 'bg-teal-50/90 border-teal-200 rounded-tr-xs' : 'bg-white border-slate-200 rounded-tl-xs' }}">
                            
                            <!-- Sender & Timestamp -->
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>{{ $chron->user->name ?? 'User' }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase
                                        {{ $chron->category === 'SELESAI' ? 'bg-emerald-100 text-emerald-800' : ($chron->category === 'OTDR' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $chron->category }}
                                    </span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $chron->created_at->format('H:i') }} WIB</span>
                            </div>

                            <!-- Message Content -->
                            <p class="text-slate-800 leading-relaxed whitespace-pre-line">{{ $chron->message }}</p>

                            <!-- Photo Attachment Preview if available -->
                            @if($chron->photo_path)
                                <div class="pt-1">
                                    <a href="{{ asset('storage/' . $chron->photo_path) }}" target="_blank" class="block group relative rounded-xl overflow-hidden border border-slate-200 max-w-xs">
                                        <img src="{{ asset('storage/' . $chron->photo_path) }}" alt="Bukti Foto" class="w-full h-40 object-cover group-hover:scale-105 transition">
                                        <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs">
                                            Lihat Foto Penuh &rarr;
                                        </div>
                                    </a>
                                </div>
                            @endif

                            <!-- Geotag coordinates if available -->
                            @if($chron->latitude && $chron->longitude)
                                <div class="text-[10px] text-teal-700 flex items-center gap-1 pt-1 font-mono">
                                    <svg class="w-3 h-3 text-teal-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    <span>GPS: {{ number_format($chron->latitude, 5) }}, {{ number_format($chron->longitude, 5) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">
                        Belum ada update kronologis koordinasi lapangan. Gunakan form di bawah untuk mengirim catatan.
                    </div>
                @endforelse
            </div>

            <!-- Quick Action Chips (Horizontally Scrollable - matching Image 3) -->
            <div class="px-4 py-2 bg-slate-100/90 border-t border-slate-200/80 overflow-x-auto flex items-center gap-2 text-xs no-scrollbar">
                <span class="text-[10px] uppercase font-bold text-slate-400 shrink-0">Template Cepat:</span>
                
                <button type="button" onclick="setQuickChip('📍 Menuju ke lokasi gangguan', 'TRACING')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    📍 Menuju lokasi
                </button>
                <button type="button" onclick="setQuickChip('🔍 Investigasi titik & pengukuran OTDR', 'OTDR')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    🔍 Investigasi &amp; OTDR
                </button>
                <button type="button" onclick="setQuickChip('✂️ Ditemukan kabel putus / bending core pada span', 'INVESTIGASI')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    ✂️ Kabel putus / bending
                </button>
                <button type="button" onclick="setQuickChip('⚡ Sedang proses splicing core di Joint Closure', 'JOINTING')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    ⚡ Proses splicing core
                </button>
                <button type="button" onclick="setQuickChip('📊 Pengukuran redaman optik selesai', 'OTDR')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    📊 Ukur redaman optik
                </button>
                <button type="button" onclick="setQuickChip('✅ Redaman normal & Link UP kembali', 'LINK_UP')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    ✅ Redaman normal &amp; Link UP
                </button>
                <button type="button" onclick="setQuickChip('⚠️ Kendala di lapangan, memerlukan manuver core', 'LAIN')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    ⚠️ Kendala / butuh manuver
                </button>
                <button type="button" onclick="setQuickChip('📷 Dokumentasi hasil perbaikan lapangan terlampir', 'SELESAI')"
                        class="px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 font-medium shrink-0 transition cursor-pointer shadow-2xs">
                    📷 Dokumentasi perbaikan
                </button>
            </div>

            <!-- Chat Input Bar (matching Image 3) -->
            <form method="POST" action="{{ route('tickets.chronologies.store', $tiket) }}" enctype="multipart/form-data"
                  class="p-4 bg-white border-t border-slate-200 flex items-center gap-2.5">
                @csrf
                <input type="hidden" name="category" id="chron_category" value="LAIN">
                <input type="hidden" name="latitude" id="chron_lat">
                <input type="hidden" name="longitude" id="chron_lng">
                <input type="hidden" name="gps_accuracy" id="chron_acc">

                <!-- Attachment Clip Button -->
                <button type="button" onclick="document.getElementById('chron_photo_input').click()"
                        title="Lampirkan Foto / Berkas"
                        class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-teal-50 text-slate-600 hover:text-[#0D9488] flex items-center justify-center transition cursor-pointer shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </button>
                <input type="file" name="photo" id="chron_photo_input" accept="image/*" class="hidden"
                       onchange="updateAttachmentLabel(this)">

                <!-- Text Input -->
                <div class="relative flex-1">
                    <input type="text"
                           name="message"
                           id="quick-chat-input"
                           required
                           placeholder="Ketik update koordinasi..."
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-4 py-2.5 text-slate-800 transition pr-28">
                    
                    <span id="attachment-name" class="absolute right-3 top-2.5 text-[11px] font-semibold text-teal-700 truncate max-w-[100px] pointer-events-none"></span>
                </div>

                <!-- Submit Send Button -->
                <button type="submit"
                        class="w-11 h-11 rounded-xl text-white flex items-center justify-center shadow-md shadow-teal-950/20 hover:scale-105 active:scale-95 transition cursor-pointer shrink-0"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-5 h-5 text-white transform rotate-45 -mr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: RESUME AKHIR PEKERJAAN (matching Image 4) -->
    <!-- ========================================================================= -->
    <div id="tab-resume" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#0D9488]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-slate-800">Resume Akhir Pekerjaan</h3>
                        <p class="text-xs text-slate-400">Rangkuman temuan masalah, tindakan perbaikan, dan tim pelaksana</p>
                    </div>
                </div>

                <button type="button" onclick="toggleResumeForm()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white shadow-md shadow-teal-950/20 cursor-pointer transition"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>{{ $hasResume ? 'Edit Resume Pekerjaan' : 'Input Resume Baru' }}</span>
                </button>
            </div>

            <!-- Resume Content / Display -->
            @if($hasResume)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Temuan Masalah di Lapangan</span>
                        <p class="text-xs sm:text-sm text-slate-800 font-semibold leading-relaxed">{{ $tiket->problem_temuan }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Tindakan Perbaikan Teknis</span>
                        <p class="text-xs sm:text-sm text-slate-800 font-semibold leading-relaxed">{{ $tiket->action_taken }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Pengukuran Redaman Optik</span>
                        <p class="text-xs sm:text-sm font-mono font-bold text-slate-800">
                            Sebelum: <span class="text-rose-600">{{ $tiket->redaman_sebelum ?? '-' }} dBm</span> &bull; 
                            Sesudah: <span class="text-emerald-600">{{ $tiket->redaman_sesudah ?? '-' }} dBm</span>
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Status Pemulihan Link</span>
                        <p class="text-xs sm:text-sm font-bold {{ $tiket->has_link_up ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $tiket->has_link_up ? '✓ LINK UP RESTORED' : 'Belum Restore' }}
                        </p>
                    </div>
                </div>
            @else
                <!-- Empty State matching Image 4 -->
                <div id="resume-empty-state" class="p-12 text-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-white shadow-xs border border-slate-200 flex items-center justify-center mx-auto text-teal-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h4 class="font-heading font-extrabold text-slate-800 text-sm">Resume Pekerjaan Belum Diinput</h4>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Rangkuman penanganan teknis, akar penyebab gangguan, dan tindakan pemulihan di lapangan belum dicatat untuk tiket ini.
                    </p>
                    <button type="button" onclick="toggleResumeForm()"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md shadow-teal-950/20 cursor-pointer transition inline-flex items-center gap-2"
                            style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                        <span>Input Resume Sekarang</span>
                    </button>
                </div>
            @endif

            <!-- Form Resume & Closing Awal (Toggled or inline) -->
            <div id="resume-form-container" class="{{ $hasResume ? 'hidden' : '' }} pt-4 border-t border-slate-100">
                <form method="POST" action="{{ route('tickets.closing-awal', $tiket) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Akar Masalah / Temuan Lapangan *
                        </label>
                        <textarea name="problem_temuan" rows="2" required placeholder="Jelaskan titik putus, bending, core bermasalah..."
                                  class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 p-3 text-slate-800">{{ old('problem_temuan', $tiket->problem_temuan) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tindakan Penanganan / Pemulihan *
                        </label>
                        <textarea name="action_taken" rows="2" required placeholder="Tindakan yang dilakukan (splicing, re-routing, bypass core)..."
                                  class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 p-3 text-slate-800">{{ old('action_taken', $tiket->action_taken) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Redaman Sebelum (dBm)</label>
                            <input type="number" step="0.01" name="redaman_sebelum" value="{{ old('redaman_sebelum', $tiket->redaman_sebelum) }}"
                                   placeholder="Contoh: -32.50"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 p-2.5 text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Redaman Sesudah (dBm) *</label>
                            <input type="number" step="0.01" name="redaman_sesudah" required value="{{ old('redaman_sesudah', $tiket->redaman_sesudah) }}"
                                   placeholder="Contoh: -18.20"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 p-2.5 text-slate-800">
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-teal-50/70 border border-teal-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-xs text-slate-800 block">Konfirmasi Link UP / Pulih</span>
                            <span class="text-[11px] text-slate-500">Tandai jika sinyal optik telah terhubung normal kembali.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_link_up" value="1" {{ $tiket->has_link_up ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0D9488]"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md shadow-teal-950/20 cursor-pointer transition"
                                style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                            Simpan Resume &amp; Ajukan Closing Awal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: PENANGANAN CORE & JOINT CLOSURE (matching Image 5) -->
    <!-- ========================================================================= -->
    <div id="tab-penanganan" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6"
             x-data="{ mode: 'maneuver' }">
            
            <!-- Switcher Header (matching Image 5) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                        <span>Pilih Metode Penanganan Fisik / Core</span>
                    </h3>
                    <p class="text-xs text-slate-400">Tentukan metode perbaikan kabel. Anda dapat memilih salah satu atau mengaktifkan keduanya sekaligus.</p>
                </div>

                <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 text-xs font-semibold">
                    <button type="button" @click="mode = 'jointing'" :class="mode === 'jointing' ? 'bg-white shadow-xs text-teal-800 font-bold' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg transition">Jointing</button>
                    <button type="button" @click="mode = 'maneuver'" :class="mode === 'maneuver' ? 'bg-white shadow-xs text-teal-800 font-bold' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg transition">Manuver Core</button>
                    <button type="button" @click="mode = 'both'" :class="mode === 'both' ? 'bg-white shadow-xs text-teal-800 font-bold' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg transition">Pilih Keduanya</button>
                </div>
            </div>

            <!-- 2 Clickable Cards (matching Image 5) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- 1. Jointing Lurus -->
                <div @click="mode = (mode === 'jointing' ? 'both' : 'jointing')"
                     :class="(mode === 'jointing' || mode === 'both') ? 'border-[#0D9488] bg-teal-50/40 ring-2 ring-teal-500/20' : 'border-slate-200 bg-white'"
                     class="p-5 rounded-2xl border-2 transition cursor-pointer flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-white border border-slate-200 text-slate-700">
                            {{ $tiket->jointClosures->count() }} Data JC
                        </span>
                    </div>
                    <div>
                        <h4 class="font-heading font-extrabold text-sm text-slate-800">1. Jointing Lurus</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Penyambungan kabel lurus eksisting &amp; jumper, mapping tube-core per tray, serta penambahan closure baru.
                        </p>
                    </div>
                </div>

                <!-- 2. Manuver Core -->
                <div @click="mode = (mode === 'maneuver' ? 'both' : 'maneuver')"
                     :class="(mode === 'maneuver' || mode === 'both') ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-500/20' : 'border-slate-200 bg-white'"
                     class="p-5 rounded-2xl border-2 transition cursor-pointer flex flex-col justify-between space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-white border border-purple-200 text-purple-800">
                            {{ $tiket->coreManeuvers->count() }} Record
                        </span>
                    </div>
                    <div>
                        <h4 class="font-heading font-extrabold text-sm text-slate-800">2. Manuver Core</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Pengalihan alokasi core serat optik (swapping core / bypass jalur putus) sebelum dan sesudah perbaikan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Joint Closure Section -->
            <div x-show="mode === 'jointing' || mode === 'both'" class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-heading font-extrabold text-sm text-slate-800">Data Joint Closure (JC)</h4>
                        <p class="text-xs text-slate-400">Pencatatan closure pada span FO dan pemetaan tray</p>
                    </div>
                    <button type="button" onclick="openJcModal()" class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-teal-600 hover:bg-teal-700 text-white transition cursor-pointer">
                        + Tambah Joint Closure
                    </button>
                </div>

                @if($tiket->jointClosures->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($tiket->jointClosures as $jc)
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800 text-sm">{{ $jc->closure_code }}</span>
                                    <span class="px-2 py-0.5 rounded bg-teal-100 text-teal-800 font-bold font-mono">{{ $jc->closure_type }}</span>
                                </div>
                                <p class="text-slate-600">Kapasitas: {{ $jc->capacity_cores }} Core &bull; Lokasi: {{ $jc->location_description ?? '-' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center rounded-2xl border border-dashed border-slate-200 text-xs text-slate-400">
                        Belum ada Joint Closure yang dicatat pada tiket ini.
                    </div>
                @endif
            </div>

            <!-- Manuver Core Section (matching Image 5) -->
            <div x-show="mode === 'maneuver' || mode === 'both'" class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-heading font-extrabold text-sm text-slate-800">Catatan Manuver Core Fiber Optik</h4>
                        <p class="text-xs text-slate-400">Mapping alokasi core sebelum dan sesudah perbaikan</p>
                    </div>
                    <button type="button" onclick="openManeuverModal()"
                            class="px-4 py-2 rounded-xl font-bold text-xs text-white bg-purple-600 hover:bg-purple-700 transition cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Manuver Core</span>
                    </button>
                </div>

                @if($tiket->coreManeuvers->isNotEmpty())
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">Tube Asal</th>
                                    <th class="p-3">Core Asal</th>
                                    <th class="p-3 text-center">&rarr;</th>
                                    <th class="p-3">Tube Tujuan</th>
                                    <th class="p-3">Core Tujuan</th>
                                    <th class="p-3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($tiket->coreManeuvers as $cm)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 font-semibold">{{ $cm->tube_source }}</td>
                                        <td class="p-3 font-mono font-bold text-purple-700">{{ $cm->core_source }}</td>
                                        <td class="p-3 text-center text-slate-400 font-bold">&rarr;</td>
                                        <td class="p-3 font-semibold">{{ $cm->tube_target }}</td>
                                        <td class="p-3 font-mono font-bold text-emerald-700">{{ $cm->core_target }}</td>
                                        <td class="p-3 text-slate-500">{{ $cm->reason ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Empty State matching Image 5 -->
                    <div class="p-12 text-center rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-white shadow-xs border border-purple-200 flex items-center justify-center mx-auto text-purple-600">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h4 class="font-heading font-extrabold text-slate-800 text-sm">Belum Ada Data Manuver Core</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Catat alokasi perpindahan core kabel optik (contoh: <code class="font-mono text-purple-700 font-bold">Tube 2 Core 1 &rarr; Tube 2 Core 1</code>) pada titik perbaikan.
                        </p>
                        <button type="button" onclick="openManeuverModal()"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-purple-600 hover:bg-purple-700 transition cursor-pointer inline-flex items-center gap-2">
                            <span>+ Tambah Manuver Sekarang</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: MATERIAL & TITIK PERBAIKAN -->
    <!-- ========================================================================= -->
    <div id="tab-materials" class="tab-pane hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Material Usage Form & Log -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Material Digunakan (Log Fisik)</span>
                    </h3>
                    <span class="text-xs font-bold font-mono text-slate-400">{{ $tiket->materials->count() }} Item</span>
                </div>

                <form method="POST" action="{{ route('tickets.materials.use', $tiket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Pilih Material *</label>
                        <select name="master_material_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="">-- Pilih Master Material --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->nama }} (Tersedia: {{ $m->stok_tersedia }} {{ $m->satuan }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Jumlah Digunakan *</label>
                            <input type="number" step="0.1" name="quantity_used" required min="0.1" placeholder="1" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Catatan Penggunaan</label>
                            <input type="text" name="notes" placeholder="Lokasi span / closure" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-xs transition">
                        + Catat Pemakaian Material
                    </button>
                </form>

                <!-- List Material -->
                @if($tiket->materials->isNotEmpty())
                    <div class="space-y-2 pt-2">
                        @foreach($tiket->materials as $tm)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-slate-800">{{ $tm->material->nama ?? 'Material' }}</span>
                                    <p class="text-[11px] text-slate-400">{{ $tm->notes ?? '-' }}</p>
                                </div>
                                <span class="font-mono font-bold text-teal-800">{{ $tm->quantity_used }} {{ $tm->material->satuan ?? 'pcs' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Titik Perbaikan (GPS Points) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>Titik Perbaikan (Koordinat GPS)</span>
                    </h3>
                    <span class="text-xs font-bold font-mono text-slate-400">{{ $tiket->repairPoints->count() }} Titik</span>
                </div>

                <form method="POST" action="{{ route('tickets.repair-points.store', $tiket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Deskripsi Titik Tiang / Span *</label>
                        <input type="text" name="location_name" required placeholder="Contoh: Tiang PT MSN No. 12 Depan RSUD" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Latitude *</label>
                            <input type="number" step="any" name="latitude" required placeholder="-6.917464" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Longitude *</label>
                            <input type="number" step="any" name="longitude" required placeholder="107.619122" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-xs transition">
                        + Tambah Titik Perbaikan
                    </button>
                </form>

                @if($tiket->repairPoints->isNotEmpty())
                    <div class="space-y-2 pt-2">
                        @foreach($tiket->repairPoints as $rp)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-slate-800">{{ $rp->location_name }}</span>
                                    <p class="text-[11px] font-mono text-slate-400">{{ $rp->latitude }}, {{ $rp->longitude }}</p>
                                </div>
                                <a href="https://maps.google.com/?q={{ $rp->latitude }},{{ $rp->longitude }}" target="_blank" class="text-teal-600 font-bold hover:underline">Maps &rarr;</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: DOKUMENTASI (Galeri Foto & Berkas) -->
    <!-- ========================================================================= -->
    <div id="tab-documentation" class="tab-pane hidden space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Galeri Dokumentasi Foto &amp; Bukti Gangguan</span>
                </h3>
            </div>

            @php
                $photos = $tiket->chronologies->whereNotNull('photo_path');
            @endphp

            @if($photos->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($photos as $p)
                        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 group relative">
                            <img src="{{ asset('storage/' . $p->photo_path) }}" alt="Foto" class="w-full h-44 object-cover group-hover:scale-105 transition duration-300">
                            <div class="p-2.5 bg-white border-t border-slate-100">
                                <span class="font-bold text-[11px] text-slate-800 block truncate">{{ $p->user->name ?? 'User' }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $p->created_at->format('d M, H:i') }}</span>
                            </div>
                            <a href="{{ asset('storage/' . $p->photo_path) }}" target="_blank" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs">
                                Buka Foto &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 space-y-2">
                    <p class="text-sm font-bold text-slate-700">Belum Ada Foto Terunggah</p>
                    <p class="text-xs text-slate-400">Gunakan tab Kronologis untuk mengunggah foto OTDR, kabel putus, atau hasil perbaikan.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 6: STOP CLOCK & SHIFT HANDOVER -->
    <!-- ========================================================================= -->
    <div id="tab-stopclock" class="tab-pane hidden space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Stop Clock Section -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Stop Clock SLA (Jeda Waktu)</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-amber-600">{{ $tiket->total_stop_clock_minutes }}m Terpakai</span>
                </div>

                @if($tiket->activeStopClock)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 space-y-3">
                        <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                            <span>Stop Clock Sedang Berjalan!</span>
                        </div>
                        <p class="text-xs text-amber-800">Alasan: <strong>{{ $tiket->activeStopClock->reason }}</strong> &bull; Dimulai: {{ $tiket->activeStopClock->started_at->format('H:i') }} WIB</p>
                        
                        <form method="POST" action="{{ route('tickets.stop-clock.end', $tiket->activeStopClock) }}">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                Selesaikan Stop Clock &amp; Lanjutkan SLA
                            </button>
                        </form>
                    </div>
                @else
                    <button type="button" onclick="openStopClockModal()" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        + Ajukan Stop Clock Baru
                    </button>
                @endif

                <!-- History Stop Clock -->
                @if($tiket->stopClocks->isNotEmpty())
                    <div class="space-y-2 pt-2 divide-y divide-slate-100 text-xs">
                        @foreach($tiket->stopClocks as $sc)
                            <div class="pt-2 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-slate-800">{{ $sc->reason }}</span>
                                    <p class="text-[11px] text-slate-400">{{ $sc->started_at->format('d M H:i') }} s.d. {{ $sc->ended_at ? $sc->ended_at->format('H:i') : 'Aktif' }}</p>
                                </div>
                                <span class="font-mono font-bold text-slate-700">{{ $sc->duration_minutes ?? '-' }} min</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Shift Handover Section -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Handover Pergantian Shift</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ $tiket->handovers->count() }} Handover</span>
                </div>

                <form method="POST" action="{{ route('tickets.handovers.store', $tiket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 text-[10px] uppercase">Shift Asal *</label>
                            <input type="text" name="shift_from" required placeholder="Contoh: Shift 1 Pagi" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 text-[10px] uppercase">Shift Tujuan *</label>
                            <input type="text" name="shift_to" required placeholder="Contoh: Shift 2 Siang" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 text-[10px] uppercase">Teknisi Penerima Shift *</label>
                        <select name="recipient_user_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                            <option value="">-- Pilih Teknisi Penerima --</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->position }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 text-[10px] uppercase">Catatan Handover *</label>
                        <textarea name="notes" rows="2" required placeholder="Catatan progress terakhir, kendala, core tersisa..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition">
                        Serahkan Tiket ke Shift Berikutnya
                    </button>
                </form>

                @if($tiket->handovers->isNotEmpty())
                    <div class="space-y-2 pt-2 divide-y divide-slate-100 text-xs">
                        @foreach($tiket->handovers as $h)
                            <div class="pt-2 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">{{ $h->shift_from }} &rarr; {{ $h->shift_to }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $h->created_at->format('d M H:i') }}</span>
                                </div>
                                <p class="text-slate-600 text-[11px]">{{ $h->notes }}</p>
                                <p class="text-[10px] text-indigo-700">Penerima: <strong>{{ $h->recipient->name ?? '-' }}</strong></p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS SECTION -->
<!-- ========================================================================= -->

<!-- Modal Dispatch Penugasan Tim (Khusus Atasan Teknik / Atasan Umum - Disembunyikan dari NOC Pembuat Tiket) -->
@if(auth()->id() !== $tiket->created_by && !auth()->user()->hasRole(['noc', 'atasan_noc']) && auth()->user()->division !== 'NOC')
@can('dispatch', $tiket)
<div id="modal-dispatch" 
     class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/65 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4"
     x-data="dispatchTeamModal()">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200 my-auto">
        <!-- Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 35%, #0a525f 100%);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-400/20 text-[#18C4C4] border border-teal-400/30 flex items-center justify-center shrink-0 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-sm sm:text-base text-white tracking-tight">Penugasan Tim Lapangan</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-teal-500/20 border border-teal-400/30 text-[10px] font-bold text-teal-200">
                            Khusus Divisi Teknik
                        </span>
                        <span class="text-[11px] text-slate-300 hidden sm:inline">&bull; Atasan Teknik / Atasan Umum</span>
                    </div>
                </div>
            </div>
            <button type="button" 
                    onclick="closeDispatchModal()" 
                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition active:scale-95 text-lg font-bold">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('tickets.dispatch', $tiket) }}" class="p-5 sm:p-6 space-y-4 sm:space-y-5 text-xs">
            @csrf
            
            <!-- Hidden real inputs for form submission -->
            <input type="hidden" name="lead_id" :value="leadId">
            <input type="hidden" name="lead_user_id" :value="leadId">
            <template x-for="mid in memberIds" :key="'m_'+mid">
                <input type="hidden" name="member_ids[]" :value="mid">
            </template>
            <template x-for="mid in memberIds" :key="'mu_'+mid">
                <input type="hidden" name="member_user_ids[]" :value="mid">
            </template>

            <!-- 1. LEADER TEKNISI (PIC UTAMA) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block font-heading font-extrabold text-slate-700 uppercase tracking-wider text-[11px]">
                        Leader Teknisi (PIC Utama) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-slate-400" x-text="selectedLead ? 'PIC Terpilih' : 'Wajib dipilih'"></span>
                </div>

                <!-- If Lead is already selected: show card -->
                <div x-show="selectedLead && !showLeadPicker" 
                     class="bg-gradient-to-r from-teal-50/70 to-emerald-50/50 rounded-2xl p-3 sm:p-3.5 border-2 border-teal-500/80 shadow-2xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0D9488] to-[#042f2e] text-white font-extrabold flex items-center justify-center text-xs shrink-0 shadow-sm">
                            <span x-text="selectedLead ? selectedLead.initials : 'PIC'"></span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-heading font-extrabold text-xs sm:text-sm text-slate-900 truncate" x-text="selectedLead ? selectedLead.name : ''"></span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#0D9488] text-white">★ PIC Utama</span>
                            </div>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5" x-text="selectedLead ? (selectedLead.position || 'Divisi Teknik') : ''"></p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="showLeadPicker = true"
                            class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-[11px] font-bold transition shrink-0 shadow-2xs">
                        Ganti Leader
                    </button>
                </div>

                <!-- If no lead selected or editing lead: show search & dropdown -->
                <div x-show="!selectedLead || showLeadPicker" class="space-y-2">
                    <div class="relative">
                        <input type="text" 
                               x-model="searchLead" 
                               placeholder="🔍 Cari nama karyawan teknik untuk PIC..."
                               class="w-full text-xs rounded-xl border border-slate-300 pl-9 pr-8 py-2.5 focus:border-[#0D9488] focus:ring-1 focus:ring-[#0D9488] transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <button type="button" 
                                x-show="searchLead" 
                                @click="searchLead = ''" 
                                class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-sm">&times;</button>
                    </div>

                    <div class="max-h-48 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xs p-1 divide-y divide-slate-100">
                        <template x-for="tech in filteredLeads" :key="tech.id">
                            <button type="button" 
                                    @click="selectLead(tech.id)"
                                    class="w-full flex items-center justify-between p-2 rounded-xl text-left transition hover:bg-teal-50/70 group"
                                    :class="{ 'bg-teal-50/90 font-bold': leadId == tech.id }">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-teal-100 text-[#0D9488] flex items-center justify-center text-[10px] font-bold shrink-0">
                                        <span x-text="tech.initials"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 truncate group-hover:text-teal-900" x-text="tech.name"></p>
                                        <p class="text-[10px] text-slate-400 truncate" x-text="tech.position || 'Teknisi Lapangan'"></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-[#0D9488] shrink-0 opacity-0 group-hover:opacity-100 transition">
                                    Pilih &rarr;
                                </span>
                            </button>
                        </template>
                        <div x-show="filteredLeads.length === 0" class="py-4 text-center text-slate-400 text-xs">
                            Tidak ditemukan karyawan teknik yang sesuai.
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. ANGGOTA TIM PENDUKUNG (CREW) -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block font-heading font-extrabold text-slate-700 uppercase tracking-wider text-[11px]">
                            Anggota Tim Pendukung (Crew Lapangan)
                        </label>
                        <p class="text-[10px] text-slate-400">Pilih satu atau beberapa teknisi pendukung.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-[#0D9488]" x-text="memberIds.length + ' Crew Terpilih'"></span>
                </div>

                <!-- Selected Crew Chips -->
                <div x-show="memberIds.length > 0" class="flex flex-wrap gap-1.5 p-2 bg-slate-50 rounded-2xl border border-slate-200/70 min-h-[42px] items-center">
                    <template x-for="mid in memberIds" :key="'chip_'+mid">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white text-slate-800 border border-slate-200 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                            <span x-text="getTechName(mid)"></span>
                            <button type="button" 
                                    @click="removeMember(mid)" 
                                    class="text-slate-400 hover:text-rose-600 font-bold ml-0.5 text-xs">&times;</button>
                        </span>
                    </template>
                </div>

                <!-- Search Member -->
                <div class="relative">
                    <input type="text" 
                           x-model="searchMember" 
                           placeholder="🔍 Filter nama teknisi pendukung..."
                           class="w-full text-xs rounded-xl border border-slate-300 pl-9 pr-8 py-2 focus:border-[#0D9488] focus:ring-1 focus:ring-[#0D9488] transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button type="button" 
                            x-show="searchMember" 
                            @click="searchMember = ''" 
                            class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-sm">&times;</button>
                </div>

                <!-- Member Checklist Box -->
                <div class="max-h-44 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-1 divide-y divide-slate-100 shadow-2xs">
                    <template x-for="tech in filteredMembers" :key="'mem_'+tech.id">
                        <div @click="toggleMember(tech.id)"
                             class="flex items-center justify-between p-2 rounded-xl cursor-pointer transition select-none"
                             :class="isMember(tech.id) ? 'bg-teal-50/80 hover:bg-teal-50' : 'hover:bg-slate-50'">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-4 h-4 rounded-md border flex items-center justify-center transition shrink-0"
                                     :class="isMember(tech.id) ? 'bg-[#0D9488] border-[#0D9488] text-white' : 'border-slate-300 bg-white'">
                                    <svg x-show="isMember(tech.id)" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 truncate" x-text="tech.name"></p>
                                    <p class="text-[10px] text-slate-400 truncate" x-text="tech.position || 'Teknisi Lapangan'"></p>
                                </div>
                            </div>
                            <span x-show="isMember(tech.id)" class="text-[10px] font-bold text-[#0D9488] px-2 py-0.5 rounded-full bg-teal-100/60 shrink-0">
                                ✓ Crew
                            </span>
                        </div>
                    </template>
                    <div x-show="filteredMembers.length === 0" class="py-4 text-center text-slate-400 text-xs">
                        Tidak ada teknisi lain yang tersedia.
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <div class="text-[11px] text-slate-500">
                    <span x-show="leadId" class="font-medium">
                        Total Tim: <strong class="text-slate-800" x-text="1 + memberIds.length"></strong> orang
                    </span>
                    <span x-show="!leadId" class="text-rose-500 font-medium">
                        * Pilih Leader PIC dahulu
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            onclick="closeDispatchModal()" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="!leadId"
                            class="px-5 py-2.5 rounded-xl text-white font-bold transition flex items-center gap-1.5 shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Penugasan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function dispatchTeamModal() {
    return {
        technicians: {!! json_encode($technicians->map(function($t) {
            $words = explode(' ', trim($t->name));
            $initials = strtoupper(substr($words[0] ?? 'T', 0, 1) . substr($words[1] ?? '', 0, 1));
            return [
                'id' => $t->id,
                'name' => $t->name,
                'position' => $t->position ?? 'Teknisi Lapangan',
                'initials' => $initials ?: 'T',
            ];
        })->values()) !!},
        leadId: {{ $tiket->technician_id ?? 'null' }},
        memberIds: {!! json_encode($tiket->assignees->where('is_lead', false)->pluck('user_id')->values()) !!},
        searchLead: '',
        searchMember: '',
        showLeadPicker: false,

        get selectedLead() {
            if (!this.leadId) return null;
            return this.technicians.find(t => t.id === Number(this.leadId)) || null;
        },

        get filteredLeads() {
            if (!this.searchLead) return this.technicians;
            const q = this.searchLead.toLowerCase();
            return this.technicians.filter(t => t.name.toLowerCase().includes(q) || (t.position && t.position.toLowerCase().includes(q)));
        },

        get filteredMembers() {
            // Exclude chosen lead
            const list = this.technicians.filter(t => t.id !== Number(this.leadId));
            if (!this.searchMember) return list;
            const q = this.searchMember.toLowerCase();
            return list.filter(t => t.name.toLowerCase().includes(q) || (t.position && t.position.toLowerCase().includes(q)));
        },

        selectLead(id) {
            this.leadId = id;
            this.showLeadPicker = false;
            this.searchLead = '';
            // If lead was chosen as crew, remove from crew
            this.memberIds = this.memberIds.filter(m => m !== id);
        },

        toggleMember(id) {
            if (this.memberIds.includes(id)) {
                this.memberIds = this.memberIds.filter(m => m !== id);
            } else {
                this.memberIds.push(id);
            }
        },

        removeMember(id) {
            this.memberIds = this.memberIds.filter(m => m !== id);
        },

        isMember(id) {
            return this.memberIds.includes(id);
        },

        getTechName(id) {
            const found = this.technicians.find(t => t.id === Number(id));
            return found ? found.name : ('ID: ' + id);
        }
    };
}
</script>
@endcan
@endif

<!-- Modal Stop Clock -->
<div id="modal-stopclock" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 40%, #0a525f 100%);">
            <h3 class="font-heading font-extrabold text-base text-white">Ajukan Jeda SLA (Stop Clock)</h3>
            <button type="button" onclick="closeStopClockModal()" class="w-8 h-8 rounded-full bg-white/10 text-white flex items-center justify-center text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.stop-clock.store', $tiket) }}" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Alasan Stop Clock *</label>
                <select name="reason" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    <option value="PERIZINAN">Terkendala Perizinan Kawasan / Site</option>
                    <option value="AKSES_SITE">Akses Site Terkunci / Menunggu Kunci</option>
                    <option value="PLN_PADAM">PLN Padam / Menunggu Genset</option>
                    <option value="CUACA_BURUK">Cuaca Buruk / Hujan Deras Petir</option>
                    <option value="FORCE_MAJEURE">Force Majeure (Bencana Alam / Insiden)</option>
                    <option value="LAINNYA">Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Keterangan Tambahan</label>
                <textarea name="notes" rows="2" placeholder="Detail kondisi di lapangan..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeStopClockModal()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold bg-amber-500 hover:bg-amber-600">
                    Mulai Stop Clock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Joint Closure -->
<div id="modal-jc" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 40%, #0a525f 100%);">
            <h3 class="font-heading font-extrabold text-base text-white">Tambah Joint Closure (JC)</h3>
            <button type="button" onclick="closeJcModal()" class="w-8 h-8 rounded-full bg-white/10 text-white flex items-center justify-center text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.joint-closures.store', $tiket) }}" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Kode / Nama Closure *</label>
                <input type="text" name="closure_code" required placeholder="Contoh: JC-SRE-01 / DOME-24C" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Tipe Closure *</label>
                    <select name="closure_type" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        <option value="DOME">Dome (Kubah)</option>
                        <option value="INLINE">Inline (Horizontal)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Kapasitas Core *</label>
                    <input type="number" name="capacity_cores" required value="24" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Deskripsi Lokasi Span</label>
                <input type="text" name="location_description" placeholder="Contoh: Tiang PT MSN No. 12 Depan Alfamart" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeJcModal()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    Simpan JC
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Manuver Core -->
<div id="modal-maneuver" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 40%, #0a525f 100%);">
            <h3 class="font-heading font-extrabold text-base text-white">Catat Manuver Core Fiber Optik</h3>
            <button type="button" onclick="closeManeuverModal()" class="w-8 h-8 rounded-full bg-white/10 text-white flex items-center justify-center text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.maneuvers.store', $tiket) }}" class="p-6 space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Tube Asal *</label>
                    <input type="text" name="tube_source" required placeholder="Tube 1 (Biru)" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Core Asal *</label>
                    <input type="text" name="core_source" required placeholder="Core 3 (Hijau)" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Tube Tujuan *</label>
                    <input type="text" name="tube_target" required placeholder="Tube 2 (Orange)" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Core Tujuan *</label>
                    <input type="text" name="core_target" required placeholder="Core 1 (Biru)" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Alasan Manuver *</label>
                <input type="text" name="reason" required placeholder="Core asal putus di span 14-15, bypass ke spare" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeManeuverModal()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold bg-purple-600 hover:bg-purple-700">
                    Simpan Manuver
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Verifikasi Closing NOC -->
<div id="modal-verify-close" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 40%, #0a525f 100%);">
            <h3 class="font-heading font-extrabold text-base text-white">Verifikasi Closing Tiket (NOC)</h3>
            <button type="button" onclick="closeVerifyCloseModal()" class="w-8 h-8 rounded-full bg-white/10 text-white flex items-center justify-center text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.verify-close', $tiket) }}" class="p-6 space-y-4 text-xs">
            @csrf
            <p class="text-slate-600 leading-relaxed">
                Anda menyatakan bahwa seluruh pemeriksaan fisik, redaman optik, dan link traffic telah terverifikasi normal oleh NOC. Tiket akan ditutup resmi.
            </p>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeVerifyCloseModal()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold bg-emerald-600 hover:bg-emerald-700">
                    Setujui &amp; Close Tiket
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.background = 'transparent';
        btn.classList.remove('text-white', 'font-bold');
        btn.classList.add('text-slate-600');
    });

    const activePane = document.getElementById(tabId);
    if (activePane) {
        activePane.classList.remove('hidden');
    }
    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.style.background = 'linear-gradient(90deg, #18C4C4 0%, #0D9488 100%)';
        activeBtn.classList.remove('text-slate-600');
        activeBtn.classList.add('text-white', 'font-bold');
    }
}

function setQuickChip(text, category) {
    const input = document.getElementById('quick-chat-input');
    const catInput = document.getElementById('chron_category');
    if (input) {
        input.value = text;
        input.focus();
    }
    if (catInput) {
        catInput.value = category || 'LAIN';
    }
}

function updateAttachmentLabel(fileInput) {
    const label = document.getElementById('attachment-name');
    if (fileInput.files && fileInput.files.length > 0) {
        label.innerText = '📎 ' + fileInput.files[0].name;
    } else {
        label.innerText = '';
    }
}

function toggleResumeForm() {
    const formCont = document.getElementById('resume-form-container');
    const emptyState = document.getElementById('resume-empty-state');
    if (formCont) {
        formCont.classList.toggle('hidden');
    }
    if (emptyState) {
        emptyState.classList.add('hidden');
    }
}

// Modal triggers
function openDispatchModal() { document.getElementById('modal-dispatch').classList.remove('hidden'); }
function closeDispatchModal() { document.getElementById('modal-dispatch').classList.add('hidden'); }
function openStopClockModal() { document.getElementById('modal-stopclock').classList.remove('hidden'); }
function closeStopClockModal() { document.getElementById('modal-stopclock').classList.add('hidden'); }
function openJcModal() { document.getElementById('modal-jc').classList.remove('hidden'); }
function closeJcModal() { document.getElementById('modal-jc').classList.add('hidden'); }
function openManeuverModal() { document.getElementById('modal-maneuver').classList.remove('hidden'); }
function closeManeuverModal() { document.getElementById('modal-maneuver').classList.add('hidden'); }
function openVerifyCloseModal() { document.getElementById('modal-verify-close').classList.remove('hidden'); }
function closeVerifyCloseModal() { document.getElementById('modal-verify-close').classList.add('hidden'); }

// Live HTML5 Geolocation helper
function getLiveGpsCoordinates() {
    const label = document.getElementById('gps_label');
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung Geolocation.');
        return;
    }
    label.innerText = 'GPS...';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('chron_lat').value = pos.coords.latitude;
            document.getElementById('chron_lng').value = pos.coords.longitude;
            document.getElementById('chron_acc').value = pos.coords.accuracy;
            label.innerText = `✓ Lokasi Disimpan (${pos.coords.latitude.toFixed(3)}, ${pos.coords.longitude.toFixed(3)})`;
        },
        (err) => {
            alert('Gagal mengambil lokasi GPS: ' + err.message);
            label.innerText = 'Sertakan GPS';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// Auto scroll chat to bottom on load
document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chat-stream-box');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});
</script>
@endpush
@endsection
