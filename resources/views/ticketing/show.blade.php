@extends('layouts.app', ['title' => 'Tiket ' . $tiket->no_tiket . ' - Detail & Penanganan', 'hideChatbot' => true])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
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
    <!-- 1. TOP HEADER BANNER (Futuristic Metallic Ocean Teal matching Dashboard) -->
    <!-- ========================================================================= -->
    <div class="relative rounded-2xl md:rounded-3xl p-4 sm:py-5 sm:px-6 text-white border border-teal-500/30 shadow-[0_8px_30px_rgba(2,40,54,0.45)] select-none z-20"
         style="background: radial-gradient(100% 140% at 15% 20%, #093d40 0%, #05282b 55%, #021619 100%);">
        
        <!-- SVG Geometric Tech Contours, Diagonal Glass Bars & Dot Grids (Clipped in an absolute overlay so it never clips dropdowns) -->
        <div class="absolute inset-0 rounded-2xl md:rounded-3xl overflow-hidden pointer-events-none z-0">
            <svg class="w-full h-full" viewBox="0 0 1100 240" fill="none" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="tktHdrDiag1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.30"/>
                        <stop offset="60%" stop-color="#14b8a6" stop-opacity="0.10"/>
                        <stop offset="100%" stop-color="#04272a" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="tktHdrDiag2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#5eead4" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#0d9488" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="tktHdrArc" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#5eead4" stop-opacity="0.40"/>
                        <stop offset="50%" stop-color="#14b8a6" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#042f2e" stop-opacity="0.04"/>
                    </linearGradient>
                    <pattern id="tktHdrDots" x="0" y="0" width="16" height="16" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1.2" fill="#5eead4" fill-opacity="0.25"/>
                    </pattern>
                </defs>
                <!-- Diagonal Glass Accent Bars -->
                <path d="M -60 -30 L 70 -30 L -15 180 L -145 180 Z" fill="url(#tktHdrDiag1)"/>
                <path d="M -15 -30 L 35 -30 L -65 190 L -115 190 Z" fill="url(#tktHdrDiag2)"/>
                <!-- Right Subtle Wave Flare -->
                <path d="M 750 0 C 830 80, 910 120, 1100 160 L 1100 0 Z" fill="url(#tktHdrDiag1)" opacity="0.35"/>
                <!-- Concentric Rims -->
                <circle cx="950" cy="120" r="190" stroke="url(#tktHdrArc)" stroke-width="1.2" opacity="0.45"/>
                <circle cx="950" cy="120" r="250" stroke="url(#tktHdrArc)" stroke-width="0.9" stroke-dasharray="6 5" opacity="0.3"/>
                <!-- Dot Matrix Grids -->
                <rect x="990" y="16" width="72" height="38" fill="url(#tktHdrDots)"/>
                <rect x="20" y="180" width="72" height="38" fill="url(#tktHdrDots)"/>
            </svg>

            <!-- Ambient Glow Orbs -->
            <div class="absolute -left-16 -top-16 w-52 h-52 rounded-full bg-teal-400/15 blur-3xl"></div>
            <div class="absolute right-1/4 -bottom-16 w-60 h-60 rounded-full bg-cyan-400/12 blur-3xl"></div>
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/30 to-transparent"></div>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left Info Area -->
            <div class="space-y-3 min-w-0 flex-1">
                <!-- Row 1: Badges & Status (Kategori, No Tiket, Status, Prioritas) -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Category Badge -->
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-mono font-extrabold tracking-wider uppercase shadow-xs
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? 'bg-[#18C4C4] text-slate-950' : 'bg-emerald-400 text-slate-950' }}">
                        {{ $tiket->kategori_tiket }}
                    </span>

                    <!-- Ticket Number Capsule -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full font-mono font-bold text-xs bg-slate-950/50 backdrop-blur-md border border-[#2dd4bf]/40 text-[#5eead4] shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#2dd4bf]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>{{ $tiket->no_tiket }}</span>
                    </span>

                    <!-- Status Indicator Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                        {{ $tiket->status === 'CLOSE' ? 'bg-emerald-500/25 text-emerald-300 border border-emerald-400/50' : ($tiket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-500/25 text-purple-200 border border-purple-400/50' : ($tiket->status === 'PROSES' ? 'bg-amber-500/25 text-amber-200 border border-amber-400/50' : 'bg-cyan-500/25 text-cyan-200 border border-cyan-400/50')) }}">
                        <span class="w-2 h-2 rounded-full {{ $tiket->status === 'CLOSE' ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                        <span>{{ $tiket->status }}</span>
                    </span>

                    <!-- Priority Badge -->
                    @if($tiket->priority)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-[11px] bg-white/10 border border-white/20 text-white/90">
                            {{ $tiket->priority }}
                        </span>
                    @endif
                </div>

                <!-- Row 2: Headline / Judul Gangguan -->
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-heading font-extrabold text-white tracking-tight leading-snug truncate">
                        {{ $tiket->status_link_impact ?? ($tiket->kategori_tiket === 'BACKBONE' ? $tiket->backbone_segment : $tiket->customer_name) }}
                    </h1>
                </div>

                <!-- Row 3: Tim Penugasan (PIC & Anggota Tim) -->
                <div class="flex flex-wrap items-center gap-2 pt-0.5 text-xs">
                    <span class="text-[11px] font-semibold text-teal-200/70 uppercase tracking-wider shrink-0 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Tim Lapangan:</span>
                    </span>

                    @if($lead)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-bold bg-[#18C4C4]/20 border border-[#18C4C4]/40 text-[#5eead4]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#5eead4]"></span>
                            <span>PIC: {{ $lead->name }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl font-semibold bg-amber-500/20 border border-amber-500/40 text-amber-300">
                            <span>PIC: Belum Ditugaskan</span>
                        </span>
                    @endif

                    @foreach($crew as $c)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-medium bg-white/10 border border-white/15 text-white/90">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300/70"></span>
                            <span>{{ $c->name }}</span>
                        </span>
                    @endforeach

                    @if($tiket->assignees->isEmpty())
                        @if(auth()->id() !== $tiket->created_by && !auth()->user()->hasRole(['noc', 'atasan_noc']) && auth()->user()->division !== 'NOC')
                            @can('dispatch', $tiket)
                                <button type="button" onclick="openDispatchModal()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-bold text-xs bg-amber-400 text-slate-950 hover:bg-amber-300 transition cursor-pointer shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    <span>Tugaskan Tim</span>
                                </button>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>

            <!-- Right Actions Area (Kembali & Menu Aksi) -->
            <div class="flex items-center gap-2.5 shrink-0 self-start md:self-center pt-2 md:pt-0">
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-teal-100 bg-white/10 hover:bg-white/20 border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>

                <!-- Menu Aksi Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-slate-950 shadow-md hover:shadow-cyan-500/25 border border-cyan-300/40 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer"
                            style="background: linear-gradient(135deg, #2dd4bf 0%, #14b8a6 50%, #0d9488 100%);">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span>Menu Aksi</span>
                        <svg class="w-3.5 h-3.5 transition-transform text-slate-950" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-56 rounded-2xl bg-white shadow-[0_20px_50px_rgba(0,0,0,0.35)] border border-slate-200/90 py-2 z-[60] text-slate-800 divide-y divide-slate-100">
                        
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
        <div class="overflow-x-auto pb-2 -mb-2 scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
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
    <div class="bg-white rounded-2xl border border-slate-200/80 p-1.5 shadow-xs overflow-x-auto scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
        <nav class="flex space-x-1 min-w-max text-xs sm:text-sm font-semibold" aria-label="Tabs">
            <!-- Tab 1: Kronologis -->
            <button type="button" onclick="switchTab('tab-chronology')" id="btn-tab-chronology"
                    class="tab-btn px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 sm:gap-2 text-white font-bold text-xs sm:text-sm shadow-xs"
                    style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kronologis</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">{{ $tiket->chronologies->count() }}</span>
            </button>

            <!-- Tab 2: Resume -->
            <button type="button" onclick="switchTab('tab-resume')" id="btn-tab-resume"
                    class="tab-btn px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 sm:gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs sm:text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Resume</span>
                @if($hasResume)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </button>

            <!-- Tab 3: Penanganan Core & JC -->
            <button type="button" onclick="switchTab('tab-penanganan')" id="btn-tab-penanganan"
                    class="tab-btn px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 sm:gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs sm:text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Penanganan Core &amp; JC</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->jointClosures->count() + $tiket->coreManeuvers->count() }}</span>
            </button>

            <!-- Tab 4: Material & Titik -->
            <button type="button" onclick="switchTab('tab-materials')" id="btn-tab-materials"
                    class="tab-btn px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 sm:gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs sm:text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Material &amp; Titik</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->materials->count() + $tiket->repairPoints->count() }}</span>
            </button>

            <!-- Tab 5: Dokumentasi -->
            <button type="button" onclick="switchTab('tab-documentation')" id="btn-tab-documentation"
                    class="tab-btn px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 sm:gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs sm:text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Dokumentasi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->chronologies->whereNotNull('photo_path')->count() }}</span>
            </button>

            <!-- Tab 6: Stop Clock & Shift -->
            <button type="button" onclick="switchTab('tab-stopclock')" id="btn-tab-stopclock"
                    class="tab-btn px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl transition cursor-pointer flex items-center gap-1.5 sm:gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs sm:text-sm font-semibold">
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
    <style>
        .ticket-chat-card {
            height: calc(100vh - 150px);
            min-height: 480px;
            scroll-margin-top: 80px;
        }
        @media (max-width: 767px) {
            .ticket-chat-card {
                height: calc(100dvh - 165px);
                min-height: 420px;
                scroll-margin-top: 65px;
            }
        }
        /* Fullscreen Zoom Mode */
        .ticket-chat-card.is-chat-fullscreen {
            position: fixed !important;
            inset: 0 !important;
            z-index: 9999 !important;
            height: 100dvh !important;
            width: 100vw !important;
            max-width: none !important;
            border-radius: 0 !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }
    </style>
    <div id="tab-chronology" class="tab-pane space-y-4">
        <div id="ticket-chat-container" class="ticket-chat-card bg-white rounded-3xl border border-slate-200/80 shadow-xs flex flex-col relative"
             style="overflow: visible !important;">
            
            <!-- Dark Ocean Teal Chat Header Banner -->
            <div class="px-3.5 sm:px-6 py-3 sm:py-4 text-white flex items-center justify-between gap-2.5 shrink-0 rounded-t-3xl overflow-hidden"
                 style="background: linear-gradient(135deg, #022836 0%, #053b47 40%, #0a525f 100%);">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-[#18C4C4]/20 border border-[#18C4C4]/30 flex items-center justify-center text-[#5eead4] shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <h3 class="font-heading font-extrabold text-xs sm:text-sm text-white truncate">Koordinasi Update Lapangan</h3>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Live</span>
                            </span>
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-teal-200/80 truncate">Tiket <span class="font-mono text-[#5eead4] font-semibold">{{ $tiket->no_tiket }}</span></p>
                    </div>
                </div>

                <!-- Header Actions: Fullscreen Zoom (Full Size) -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <button type="button" onclick="toggleChatFullscreen()"
                            id="btn-chat-fullscreen"
                            title="Tampilan Penuh (Full Size)"
                            class="text-xs text-white flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-teal-500/30 hover:bg-teal-500/50 border border-teal-400/40 shadow-xs transition-all active:scale-95 cursor-pointer">
                        <!-- Icon Expand / Maximize -->
                        <svg id="icon-fs-expand" class="w-3.5 h-3.5 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <!-- Icon Minimize (hidden by default) -->
                        <svg id="icon-fs-minimize" class="w-3.5 h-3.5 text-cyan-300 hidden" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4v4m0 0H5m4 0L4 3m11 1v4m0 0h4m-4 0l5-5M9 20v-4m0 0H5m4 0l-5 5m11-1v-4m0 0h4m-4 0l5 5"/>
                        </svg>
                        <span id="fs_label" class="text-[11px] font-bold text-cyan-200">Full Size</span>
                    </button>
                </div>
            </div>

            <!-- WhatsApp Chat Body with Bubble Thread -->
            <div id="chat-stream-box" class="p-3.5 sm:p-6 flex-1 min-h-0 overflow-y-auto relative w-full"
                 style="background-color: #EFEAE2; background-image: url('{{ asset('assets/images/whatsapp-chat-bg.jpg') }}?v=2'); background-repeat: repeat; background-size: 420px auto; background-position: top left;">
                
                <!-- Full Width Conversation Stream Container -->
                <div id="chat-stream-container" class="w-full space-y-2.5 sm:space-y-3">
                    <!-- Date divider (WhatsApp Floating Pill) -->
                    <div class="flex justify-center sticky top-0 z-10 py-1 pointer-events-none">
                        <span class="px-3 py-0.5 rounded-lg text-[10px] font-semibold bg-white/95 text-slate-600 shadow-[0_1px_2px_rgba(0,0,0,0.12)] border border-slate-200/60 backdrop-blur-xs flex items-center gap-1.5 pointer-events-auto">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $tiket->tanggal_open ? $tiket->tanggal_open->isoFormat('dddd, D MMMM Y') : 'Hari Ini' }}</span>
                        </span>
                    </div>

                    <!-- Initial Ticket Creation System Note -->
                    <div class="flex justify-start">
                        <div style="max-width: min(85%, 520px);"
                             class="bg-white rounded-2xl rounded-tl-xs p-2.5 sm:p-3 shadow-xs border border-slate-200 text-xs space-y-1">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                    <span class="text-xs">{{ $tiket->creator->name ?? 'Helpdesk' }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] bg-teal-100 text-teal-800 font-bold">NOC</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $tiket->tanggal_open ? $tiket->tanggal_open->format('H:i') : '' }} WIB</span>
                            </div>
                            <p class="text-slate-700 leading-snug font-medium text-[11px] sm:text-xs">
                                🎫 <strong>OPEN TIKET BARU</strong>: Tiket dibuat dengan status OPEN. Kendala: {{ $tiket->problem_temuan ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Dispatch Note if available -->
                    @if($lead)
                        <div class="flex justify-start">
                            <div style="max-width: min(85%, 520px);"
                                 class="bg-amber-50/90 rounded-2xl rounded-tl-xs p-2.5 sm:p-3 shadow-xs border border-amber-200 text-xs space-y-1">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-bold text-amber-900 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-amber-200 text-amber-900 font-bold flex items-center justify-center text-[9px]">MT</span>
                                        <span class="text-xs">Manager Teknis</span>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] bg-amber-200 text-amber-900 font-bold">DISPATCH</span>
                                    </span>
                                    <span class="text-[10px] text-amber-700 font-mono">{{ $lead->pivot->assigned_at ? \Carbon\Carbon::parse($lead->pivot->assigned_at)->format('H:i') : '' }} WIB</span>
                                </div>
                                <p class="text-amber-950 leading-snug text-[11px] sm:text-xs">
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
                        $roleLabel = $chron->user?->roles?->first()?->name ?? $chron->user?->division ?? $chron->category ?? 'Petugas';
                        
                        // Clean duplicate raw GPS text if location card is already present
                        $displayMessage = $chron->message;
                        if ($chron->latitude && $chron->longitude && str_starts_with(trim((string)$displayMessage), '📍 Lokasi Saya saat ini: https://maps.google.com/')) {
                            $displayMessage = null;
                        }

                        // WhatsApp Reply Quote Detection:
                        // 1. Via replyTo relation
                        // 2. Or via [Balas @Sender | Snippet]: Message format
                        // 3. Or via [Balas @Sender]: Message format
                        $quotedSender = null;
                        $quotedSnippet = null;
                        $cleanMessage = $displayMessage;

                        if (!empty($chron->reply_to_id) && $chron->relationLoaded('replyTo') && $chron->replyTo) {
                            $quotedSender = $chron->replyTo->user->name ?? 'User';
                            $quotedSnippet = $chron->replyTo->message ?: ($chron->replyTo->latitude ? '📍 Titik Lokasi Lapangan' : ($chron->replyTo->photo_path ? '📷 Lampiran Foto' : 'Pesan'));
                        } elseif ($displayMessage && preg_match('/^\[Balas @([^\]\|]+)(?: \| ([^\]]+))?\]:\s*(.*)$/s', $displayMessage, $matches)) {
                            $quotedSender = trim($matches[1]);
                            $quotedSnippet = !empty($matches[2]) ? trim($matches[2]) : null;
                            $cleanMessage = trim($matches[3]);
                        }

                        // WhatsApp read status:
                        // 1. Explicit read_at timestamp populated
                        // 2. Or there exists a subsequent chronology from someone else in this ticket
                        $isRead = !empty($chron->read_at);
                        if (!$isRead && $isSelf) {
                            $isRead = $tiket->chronologies->contains(function ($other) use ($chron) {
                                return $other->id > $chron->id && $other->user_id !== $chron->user_id;
                            });
                        }
                    @endphp
                    <div class="flex {{ $isSelf ? 'justify-end' : 'justify-start' }} my-1 group/bubble relative" id="chron-row-{{ $chron->id }}">
                        <div id="chron-bubble-{{ $chron->id }}"
                             data-id="{{ $chron->id }}"
                             data-sender="{{ $isSelf ? 'Anda' : ($chron->user->name ?? 'User') }}"
                             data-role="{{ ucfirst(str_replace('_', ' ', $roleLabel)) }}"
                             data-message="{{ $chron->message }}"
                             data-time="{{ $chron->created_at->format('d M Y, H:i') }} WIB"
                             data-read="{{ $isRead ? ($chron->read_at ? $chron->read_at->format('d M Y, H:i') . ' WIB' : 'Sudah dibaca') : 'Belum dibaca' }}"
                             data-lat="{{ $chron->latitude ?? '' }}"
                             data-lng="{{ $chron->longitude ?? '' }}"
                             data-photo="{{ $chron->photo_url ?? ($chron->photo_path ? asset('storage/' . $chron->photo_path) : '') }}"
                             data-category="{{ $chron->category ?? 'LAIN' }}"
                             style="{{ $isSelf ? 'background-color: #EAF4FE; border: 1px solid #CFE4FC;' : 'background-color: #FFFFFF; border: 1px solid #E2E8F0;' }} box-shadow: 0 1px 2px rgba(0,0,0,0.06); max-width: min(85%, 440px);"
                             class="rounded-2xl p-2.5 sm:p-3 text-xs space-y-1.5 {{ $isSelf ? 'rounded-tr-xs' : 'rounded-tl-xs' }} relative">
                            
                            <!-- Sender & Role Badge + 3-Dots Dropdown Trigger (Matching User Attachment Image 1 & 2) -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="font-bold text-xs {{ $isSelf ? 'text-teal-950' : 'text-slate-800' }} truncate">
                                        {{ $isSelf ? 'Anda' : ($chron->user->name ?? 'User') }}
                                    </span>
                                    <span style="{{ $isSelf ? 'background-color: #D6E8FF; color: #0369A1;' : 'background-color: #F1F5F9; color: #475569;' }}"
                                          class="px-2 py-0.5 rounded-full text-[9px] font-semibold tracking-wide shrink-0">
                                        {{ ucfirst(str_replace('_', ' ', $roleLabel)) }}
                                    </span>
                                </div>

                                <!-- 3-Dots Vertical Action Button (matching Image 1) -->
                                <div class="relative shrink-0">
                                    <button type="button"
                                            onclick="toggleMessageMenu({{ $chron->id }}, event)"
                                            class="w-5 h-5 -mr-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-200/50 flex items-center justify-center transition cursor-pointer"
                                            title="Pilihan pesan">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>

                                    <!-- WhatsApp Dropdown Menu (matching Image 2) -->
                                    <div id="msg-dropdown-{{ $chron->id }}"
                                         class="msg-action-dropdown hidden absolute {{ $isSelf ? 'right-0' : 'left-0 sm:right-auto' }} top-full mt-1 w-52 sm:w-56 rounded-2xl bg-white shadow-xl border border-slate-200/90 py-1.5 px-1 z-50 select-none divide-y divide-slate-100">
                                        
                                        <div class="space-y-0.5 pb-1">
                                            <!-- 1. Salin -->
                                            <button type="button"
                                                    onclick="copyMessageText({{ $chron->id }})"
                                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer text-left">
                                                <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                                <span>Salin</span>
                                            </button>

                                            <!-- 2. Info Pesan -->
                                            <button type="button"
                                                    onclick="openMessageInfo({{ $chron->id }})"
                                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer text-left">
                                                <svg class="w-4 h-4 text-cyan-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                                                <span>Info Pesan</span>
                                            </button>

                                            <!-- 3. Simpan ke Dokumentasi -->
                                            <button type="button"
                                                    onclick="saveToDocumentation({{ $chron->id }})"
                                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer text-left">
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                                <span>Simpan ke Dokumentasi</span>
                                            </button>

                                            <!-- 4. Balas -->
                                            <button type="button"
                                                    onclick="replyToMessage({{ $chron->id }})"
                                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer text-left">
                                                <svg class="w-4 h-4 text-cyan-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/></svg>
                                                <span>Balas</span>
                                            </button>

                                            @if($isSelf || auth()->user()?->hasRole('admin'))
                                                <!-- 5. Edit -->
                                                <button type="button"
                                                        onclick="editMessage({{ $chron->id }})"
                                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer text-left">
                                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit</span>
                                                </button>
                                            @endif
                                        </div>

                                        @if($isSelf || auth()->user()?->hasRole('admin'))
                                            <div class="pt-1">
                                                <!-- 6. Hapus -->
                                                <button type="button"
                                                        onclick="deleteMessage({{ $chron->id }})"
                                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer text-left">
                                                    <svg class="w-4 h-4 text-slate-600 hover:text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp Quoted Reply Box (Matching User Reference Image) -->
                            @if($quotedSender)
                                <div style="background-color: rgba(3, 105, 161, 0.08); border-left: 4px solid #2563EB; border-radius: 8px;"
                                     class="px-2.5 py-1.5 select-none overflow-hidden text-left cursor-pointer hover:bg-sky-100/60 transition group/quote"
                                     @if(!empty($chron->reply_to_id)) onclick="scrollToChronology({{ $chron->reply_to_id }})" title="Klik untuk menuju pesan asli" @endif>
                                    <div class="flex items-center gap-1.5 text-blue-600 font-bold text-[11px] leading-tight">
                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0 transform -rotate-12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/>
                                        </svg>
                                        <span class="truncate uppercase tracking-wide">{{ $quotedSender }}</span>
                                    </div>
                                    @if($quotedSnippet)
                                        <p class="text-slate-600 text-[11px] truncate mt-0.5 leading-snug">
                                            {{ html_entity_decode($quotedSnippet, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <!-- Message Content -->
                            @if($cleanMessage)
                                <p id="chron-text-{{ $chron->id }}" class="text-slate-800 leading-snug whitespace-pre-line text-xs font-normal">{{ $cleanMessage }}</p>
                            @endif

                            <!-- Photo Attachment Preview if available (Lightbox Modal) -->
                            @if($chron->photo_path)
                                @php
                                    $photoSrc = $chron->photo_url ?? asset('storage/' . $chron->photo_path);
                                @endphp
                                <div class="pt-0.5">
                                    <button type="button"
                                            onclick="openImageLightbox('{{ $photoSrc }}', 'Foto Dokumentasi - {{ $isSelf ? 'Anda' : ($chron->user->name ?? 'User') }}')"
                                            style="width: 220px; max-width: 100%; border-radius: 14px; overflow: hidden; border: 1px solid #E2E8F0; display: block;"
                                            class="block group relative shadow-2xs bg-slate-100 cursor-pointer text-left">
                                        <img src="{{ $photoSrc }}"
                                             alt="Bukti Foto"
                                             loading="lazy"
                                             style="width: 100%; height: 140px; object-fit: cover; display: block;"
                                             class="group-hover:scale-105 transition duration-200"
                                             onerror="if(this.src!=='{{ asset('storage/' . $chron->photo_path) }}'){this.src='{{ asset('storage/' . $chron->photo_path) }}';}">
                                        <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs gap-1.5 backdrop-blur-[1px]">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            <span>Perbesar &rarr;</span>
                                        </div>
                                    </button>
                                </div>
                            @endif

                            <!-- Video Attachment Preview if available -->
                            @if($chron->video_path)
                                <div class="pt-0.5">
                                    <video controls
                                           style="width: 240px; max-width: 100%; height: 150px; border-radius: 14px; border: 1px solid #E2E8F0; display: block;"
                                           class="shadow-2xs">
                                        <source src="{{ $chron->video_url ?? asset('storage/' . $chron->video_path) }}" type="video/mp4">
                                        Browser Anda tidak mendukung pemutaran video.
                                    </video>
                                </div>
                            @endif

                            <!-- Interactive Location Card (Compact & Clean) -->
                            @if($chron->latitude && $chron->longitude)
                                <a href="https://maps.google.com/?q={{ $chron->latitude }},{{ $chron->longitude }}" target="_blank"
                                   title="Buka titik koordinat di Google Maps"
                                   style="background-color: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);"
                                   class="block p-2 rounded-xl hover:border-rose-300 hover:shadow-xs transition-all group mt-1">
                                    <div class="flex items-center gap-2.5">
                                        <div style="background-color: #FEE2E2; border: 1px solid #FECDD3;"
                                             class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4 text-rose-600" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-xs text-slate-800 group-hover:text-rose-600 transition-colors leading-tight">Lokasi Titik Lapangan</div>
                                            <div class="text-[10px] font-mono text-slate-500 mt-0.5 truncate tracking-tight">{{ number_format($chron->latitude, 6) }}, {{ number_format($chron->longitude, 6) }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endif

                            <!-- Timestamp & WhatsApp status indicators -->
                            <div class="flex justify-end items-center gap-1 text-[10px] text-slate-400 font-mono pt-0.5 select-none">
                                <span>{{ $chron->created_at->format('H:i') }} WIB</span>
                                @if($isSelf)
                                    @if($isRead)
                                        <!-- Double Blue Checkmark (Dibaca) -->
                                        <span title="Dibaca" class="inline-flex items-center text-[#53bdeb] ml-0.5" aria-label="Dibaca">
                                            <svg class="w-3.5 h-3 text-[#53bdeb]" viewBox="0 0 16 11" fill="none">
                                                <path d="M11 1.5L4.5 8L1.5 5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14.5 1.5L8 8L6.5 6.5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    @else
                                        <!-- Double Grey Checkmark (Terkirim) -->
                                        <span title="Terkirim" class="inline-flex items-center text-slate-400 ml-0.5" aria-label="Terkirim">
                                            <svg class="w-3.5 h-3 text-slate-400" viewBox="0 0 16 11" fill="none">
                                                <path d="M11 1.5L4.5 8L1.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14.5 1.5L8 8L6.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex justify-center py-6">
                        <div class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white/95 border border-slate-200/90 shadow-xs text-slate-500 text-xs text-center max-w-sm">
                            <span class="text-base shrink-0">💬</span>
                            <span class="leading-relaxed">Belum ada update koordinasi. Gunakan template atau form di bawah untuk mengirim update.</span>
                        </div>
                    </div>
                @endforelse
                </div>
            </div>

            <!-- Quick Action Chips (Horizontally Scrollable - matching Image 3) -->
            <div class="px-3 sm:px-4 py-2 bg-slate-100/90 border-t border-slate-200/80 overflow-x-auto text-xs scrollbar-none shrink-0"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                <div class="w-full flex items-center gap-1.5 sm:gap-2 overflow-x-auto scrollbar-none">
                    <span class="text-[10px] uppercase font-bold text-slate-400 shrink-0">Template Cepat:</span>
                    
                    <button type="button" onclick="setQuickChip('📍 Menuju ke lokasi gangguan', 'TRACING')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        📍 Menuju lokasi
                    </button>
                    <button type="button" onclick="setQuickChip('🔍 Investigasi titik & pengukuran OTDR', 'OTDR')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        🔍 Investigasi &amp; OTDR
                    </button>
                    <button type="button" onclick="setQuickChip('✂️ Ditemukan kabel putus / bending core pada span', 'INVESTIGASI')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ✂️ Kabel putus / bending
                    </button>
                    <button type="button" onclick="setQuickChip('⚡ Sedang proses splicing core di Joint Closure', 'JOINTING')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ⚡ Proses splicing core
                    </button>
                    <button type="button" onclick="setQuickChip('📊 Pengukuran redaman optik selesai', 'OTDR')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        📊 Ukur redaman optik
                    </button>
                    <button type="button" onclick="setQuickChip('✅ Redaman normal & Link UP kembali', 'LINK_UP')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ✅ Redaman normal &amp; Link UP
                    </button>
                    <button type="button" onclick="setQuickChip('⚠️ Kendala di lapangan, memerlukan manuver core', 'LAIN')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ⚠️ Kendala / butuh manuver
                    </button>
                    <button type="button" onclick="setQuickChip('📷 Dokumentasi hasil perbaikan lapangan terlampir', 'SELESAI')"
                            class="px-2.5 sm:px-3 py-1 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] sm:text-xs font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        📷 Dokumentasi perbaikan
                    </button>
                </div>
            </div>

            <!-- Chat Input Bar (matching Image 3 & Attachment Menu) -->
            <form id="quick-chat-form" method="POST" action="{{ route('tickets.chronologies.store', $tiket) }}" enctype="multipart/form-data"
                  onsubmit="handleChatFormSubmit(event)"
                  class="bg-white border-t border-slate-200 shrink-0 relative">
                @csrf
                <input type="hidden" name="category" id="chron_category" value="LAIN">
                <input type="hidden" name="reply_to_id" id="chron_reply_to_id">
                <input type="hidden" name="reply_sender" id="chron_reply_sender">
                <input type="hidden" name="reply_snippet" id="chron_reply_snippet">
                <input type="hidden" name="latitude" id="chron_lat">
                <input type="hidden" name="longitude" id="chron_lng">
                <input type="hidden" name="gps_accuracy" id="chron_acc">

                <!-- WhatsApp Reply Banner Preview (shows when replying to a message) -->
                <div id="chat-reply-preview" class="hidden border-b border-slate-200/90 bg-sky-50/70 px-3.5 py-2 transition-all">
                    <div class="w-full flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0 border-l-4 border-blue-600 pl-2.5 py-0.5">
                            <div class="min-w-0">
                                <div class="font-bold text-blue-600 text-[10px] sm:text-[11px] flex items-center gap-1.5 uppercase tracking-wide">
                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0 transform -rotate-12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/></svg>
                                    <span id="reply-to-sender">Membalas User</span>
                                </div>
                                <p id="reply-to-text" class="text-slate-600 text-[10px] sm:text-[11px] truncate mt-0.5 font-normal">Isi pesan...</p>
                            </div>
                        </div>
                        <button type="button" onclick="cancelReply()" class="w-6 h-6 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition cursor-pointer text-sm font-bold" title="Batal balas">&times;</button>
                    </div>
                </div>

                <div class="p-2.5 sm:p-4">
                    <div class="w-full flex items-center gap-2 sm:gap-2.5">

                <!-- Hidden Native File Inputs -->
                <!-- 1. General Media (Photo / Video) -->
                <input type="file" name="photo" id="input_upload_media" accept="image/*,video/*" class="hidden" onchange="handleFileChosen(this, 'Media')">
                <!-- 2. Video Capture (HP Video Camera) -->
                <input type="file" name="video" id="input_record_video" accept="video/*" capture="environment" class="hidden" onchange="handleFileChosen(this, 'Video')">
                <!-- 3. Camera GPS (Watermark) trigger -->
                <input type="file" id="input_camera_gps_raw" accept="image/*" capture="environment" class="hidden" onchange="processGpsWatermark(this)">
                <!-- 4. Camera Polos (Direct Camera Capture) -->
                <input type="file" id="input_camera_plain" accept="image/*" capture="environment" class="hidden" onchange="handleFileChosen(this, 'Kamera')">

                <!-- Attachment Clip Button with Dropup Menu -->
                <div class="relative">
                    <button type="button"
                            id="btn-attachment-toggle"
                            onclick="toggleAttachmentMenu(event)"
                            title="Lampirkan Dokumen / Media / Kamera / Lokasi"
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-teal-50 text-slate-600 hover:text-[#0D9488] flex items-center justify-center transition cursor-pointer shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </button>

                    <!-- WhatsApp-Style Dropup Attachment Menu (Robust Native JS & Clean Elevating Z-Index) -->
                    <div id="dropdown-attachment-menu"
                         style="bottom: 100% !important; margin-bottom: 12px !important; top: auto !important;"
                         class="hidden absolute left-0 w-80 sm:w-88 rounded-3xl bg-white shadow-[0_20px_50px_rgba(0,0,0,0.30)] border border-slate-200/90 py-2.5 px-2 z-[100] select-none divide-y divide-slate-100">
                        
                        <div class="space-y-1 pb-1.5">
                            <!-- Option 1: Upload Media (Foto / Video) -->
                            <button type="button"
                                    onclick="closeAttachmentMenu(); document.getElementById('input_upload_media').click();"
                                    class="w-full flex items-center gap-3.5 p-2 rounded-2xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-800 leading-tight">Upload Media (Foto / Video)</h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">Pilih foto atau video dari galeri HP / perangkat</p>
                                </div>
                            </button>

                            <!-- Option 2: Rekam Video Lapangan -->
                            <button type="button"
                                    onclick="closeAttachmentMenu(); document.getElementById('input_record_video').click();"
                                    class="w-full flex items-center gap-3.5 p-2 rounded-2xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-800 leading-tight">Rekam Video Lapangan</h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">Rekam langsung kamera HP (Video Ringan &amp; Cepat)</p>
                                </div>
                            </button>

                            <!-- Option 3: Kamera GPS (Watermark) -->
                            <button type="button"
                                    onclick="closeAttachmentMenu(); captureCameraWithGps();"
                                    class="w-full flex items-center gap-3.5 p-2 rounded-2xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-800 leading-tight flex items-center gap-1.5">
                                        <span>Kamera GPS (Watermark)</span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700">MSN GPS</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">Dengan Logo MSN, Timestamp &amp; Alamat</p>
                                </div>
                            </button>

                            <!-- Option 4: Kamera Polos (Tanpa Watermark) -->
                            <button type="button"
                                    onclick="closeAttachmentMenu(); document.getElementById('input_camera_plain').click();"
                                    class="w-full flex items-center gap-3.5 p-2 rounded-2xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-800 leading-tight">Kamera Polos (Tanpa Watermark)</h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">Potret langsung foto kamera original</p>
                                </div>
                            </button>
                        </div>

                        <!-- Option 5: Share Lokasi Saya -->
                        <div class="pt-1.5">
                            <button type="button"
                                    onclick="closeAttachmentMenu(); shareMyCurrentLocation();"
                                    class="w-full flex items-center gap-3.5 p-2 rounded-2xl hover:bg-rose-50 transition text-left cursor-pointer group">
                                <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 border border-rose-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-800 leading-tight">Share Lokasi Saya</h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">Sematkan koordinat GPS lapangan</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Text & Location Input Box Container (Matching Image 1) -->
                <div id="chat-input-container"
                     class="flex-1 rounded-2xl border border-slate-300/90 bg-white px-3.5 py-2 sm:py-2.5 transition-all flex flex-col justify-center min-h-[46px] shadow-2xs">
                    <div class="relative flex items-center">
                        <input type="text"
                               name="message"
                               id="quick-chat-input"
                               placeholder="Ketik update koordinasi..."
                               autocomplete="off"
                               style="outline: none !important; box-shadow: none !important; border: none !important;"
                               class="w-full text-xs sm:text-sm bg-transparent border-0 p-0 text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none focus-visible:outline-none focus-visible:ring-0">
                        
                        <span id="attachment-name" class="text-[10px] sm:text-[11px] font-semibold text-teal-700 truncate max-w-[120px] ml-2 shrink-0"></span>
                    </div>

                    <!-- GPS Attached Chip (shows when latitude & longitude are attached - Matching Image 1) -->
                    <div id="gps-attached-preview" class="hidden pt-2 mt-2 border-t border-dashed border-slate-200 flex items-center justify-between">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 border border-rose-200 text-rose-600 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            <span id="gps-preview-text" class="font-bold">GPS: -6.934309, 107.594269</span>
                            <button type="button" onclick="clearAttachedGps()" class="ml-1.5 text-rose-400 hover:text-rose-700 font-bold transition text-base leading-none cursor-pointer" title="Hapus Lokasi">&times;</button>
                        </div>
                    </div>

                    <!-- Photo Attached Chip (shows when photo or media is selected) -->
                    <div id="photo-attached-preview" class="hidden pt-2 mt-2 border-t border-dashed border-slate-200 flex items-center justify-between">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 border border-sky-200 text-sky-700 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-sky-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span id="photo-preview-text" class="font-bold truncate max-w-[220px]">Foto Terlampir</span>
                            <button type="button" onclick="clearAttachedPhoto()" class="ml-1.5 text-sky-400 hover:text-sky-700 font-bold transition text-base leading-none cursor-pointer" title="Hapus Foto">&times;</button>
                        </div>
                    </div>
                </div>

                <style>
                    #chat-input-container:focus-within {
                        border-color: #0D9488 !important;
                        box-shadow: 0 0 0 3px rgba(24, 196, 196, 0.22) !important;
                    }
                    #quick-chat-input,
                    #quick-chat-input:focus,
                    #quick-chat-input:focus-visible {
                        outline: none !important;
                        box-shadow: none !important;
                        border: none !important;
                    }
                </style>

                <!-- Submit Send Button -->
                <button type="submit"
                        id="btn-quick-chat-submit"
                        class="w-10 h-10 rounded-2xl text-white flex items-center justify-center shadow-md shadow-teal-950/20 hover:scale-105 active:scale-95 transition cursor-pointer shrink-0"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white transform rotate-45 -mr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
                    </div>
                </div>
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
                            <img src="{{ $p->photo_url ?? asset('storage/' . $p->photo_path) }}"
                                 alt="Foto"
                                 loading="lazy"
                                 class="w-full h-44 object-cover group-hover:scale-105 transition duration-300"
                                 onerror="if(this.src!=='{{ asset('storage/' . $p->photo_path) }}'){this.src='{{ asset('storage/' . $p->photo_path) }}';}">
                            <div class="p-2.5 bg-white border-t border-slate-100">
                                <span class="font-bold text-[11px] text-slate-800 block truncate">{{ $p->user->name ?? 'User' }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $p->created_at->format('d M, H:i') }}</span>
                            </div>
                            <button type="button"
                                    onclick="openImageLightbox('{{ $p->photo_url ?? asset('storage/' . $p->photo_path) }}', 'Foto Dokumentasi - {{ $p->user->name ?? 'User' }}')"
                                    class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs gap-1.5 cursor-pointer backdrop-blur-[1px]">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                <span>Perbesar &rarr;</span>
                            </button>
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

<!-- ========================================================================= -->
<!-- IMAGE LIGHTBOX MODAL (Enlarge in Modal with Direct Download Button) -->
<!-- ========================================================================= -->
<div id="image-lightbox-modal"
     class="hidden fixed inset-0 z-[99999] bg-slate-950/90 backdrop-blur-md flex flex-col justify-between p-3 sm:p-5 select-none transition-all duration-200"
     onclick="closeImageLightbox(event)">
    
    <!-- Top Header Bar -->
    <div class="flex items-center justify-between text-white z-10 shrink-0 max-w-6xl mx-auto w-full pt-1" onclick="event.stopPropagation()">
        <div class="flex items-center gap-2.5 min-w-0 pr-2">
            <span class="w-8 h-8 rounded-xl bg-teal-500/20 text-[#18C4C4] border border-teal-500/30 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
            <span id="lightbox-title" class="font-bold text-xs sm:text-sm text-slate-100 truncate">Dokumentasi Foto</span>
        </div>
        
        <div class="flex items-center gap-2 shrink-0">
            <!-- Download Button -->
            <button type="button"
                    id="lightbox-download-btn"
                    onclick="downloadLightboxImage(event)"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#18C4C4] to-[#0D9488] hover:opacity-95 text-white text-xs font-bold shadow-lg shadow-teal-950/30 transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download</span>
            </button>

            <!-- Close Button -->
            <button type="button"
                    onclick="closeImageLightbox()"
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-rose-500/80 text-white flex items-center justify-center transition active:scale-95 cursor-pointer"
                    title="Tutup (Esc)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Center: Scaled Image Viewport -->
    <div class="flex-1 flex items-center justify-center p-2 min-h-0 relative select-none" onclick="closeImageLightbox(event)">
        <img id="lightbox-image"
             src=""
             alt="Pratinjau Foto"
             class="max-h-[82vh] max-w-[94vw] object-contain rounded-2xl shadow-2xl border border-white/10 cursor-zoom-out"
             onclick="event.stopPropagation()">
    </div>

    <!-- Bottom Instruction Pill -->
    <div class="text-center text-[11px] text-slate-400 select-none pb-1 z-10 shrink-0">
        Klik di luar gambar atau tekan <kbd class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono text-[10px]">ESC</kbd> untuk menutup
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL INFO PESAN (WhatsApp Style) -->
<!-- ========================================================================= -->
<div id="modal-msg-info" class="hidden fixed inset-0 z-[99999] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                </div>
                <h4 class="font-bold text-sm text-slate-800">Info Pesan</h4>
            </div>
            <button type="button" onclick="closeMessageInfo()" class="w-7 h-7 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-lg">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400">Pengirim</div>
                <div id="info-msg-sender" class="font-bold text-slate-800 text-sm">Nama</div>
                <div id="info-msg-role" class="text-[11px] text-teal-700 font-semibold">Role</div>
            </div>

            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <div class="text-[10px] uppercase font-bold text-slate-400">Isi Pesan</div>
                <div id="info-msg-content" class="text-slate-800 leading-relaxed whitespace-pre-line font-medium">-</div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[10px] uppercase font-bold text-slate-400 flex items-center gap-1">
                        <span>Terkirim</span>
                        <svg class="w-3.5 h-3 text-slate-400" viewBox="0 0 16 11" fill="none"><path d="M11 1.5L4.5 8L1.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.5 1.5L8 8L6.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div id="info-msg-sent-time" class="font-mono text-[11px] text-slate-700 font-bold">-</div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[10px] uppercase font-bold text-slate-400 flex items-center gap-1">
                        <span>Dibaca</span>
                        <svg class="w-3.5 h-3 text-[#53bdeb]" viewBox="0 0 16 11" fill="none"><path d="M11 1.5L4.5 8L1.5 5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.5 1.5L8 8L6.5 6.5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div id="info-msg-read-time" class="font-mono text-[11px] text-emerald-600 font-bold">-</div>
                </div>
            </div>

            <div id="info-msg-attachment-box" class="hidden p-3 rounded-2xl bg-teal-50/60 border border-teal-100 space-y-1">
                <div class="text-[10px] uppercase font-bold text-teal-800">Lampiran</div>
                <div id="info-msg-attachment-text" class="text-[11px] text-teal-900 font-medium">📍 Titik Lokasi &amp; Foto</div>
            </div>
        </div>

        <button type="button" onclick="closeMessageInfo()" class="w-full py-2.5 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
            Tutup
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL EDIT PESAN -->
<!-- ========================================================================= -->
<div id="modal-msg-edit" class="hidden fixed inset-0 z-[99999] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <h4 class="font-bold text-sm text-slate-800">Edit Pesan Koordinasi</h4>
            </div>
            <button type="button" onclick="closeMessageEdit()" class="w-7 h-7 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-lg">&times;</button>
        </div>

        <form id="form-edit-message" onsubmit="submitEditMessage(event)" class="space-y-4 text-xs">
            <input type="hidden" id="edit-msg-id" value="">
            <div class="space-y-1.5">
                <label class="font-semibold text-slate-700">Teks Pesan:</label>
                <textarea id="edit-msg-textarea" rows="4" required class="w-full rounded-2xl border border-slate-300 p-3 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeMessageEdit()" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" id="btn-save-edit-msg" class="px-5 py-2 rounded-xl text-white font-bold bg-teal-600 hover:bg-teal-700 cursor-pointer shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Toast Feedback -->
<div id="chat-toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[999999] px-4 py-2 rounded-full bg-slate-900/90 text-white text-xs font-semibold shadow-2xl backdrop-blur-md flex items-center gap-2 transition-all">
    <span id="chat-toast-icon">✓</span>
    <span id="chat-toast-text">Teks disalin</span>
</div>

@push('scripts')
<script>
// Image Lightbox Modal with Direct Download
function openImageLightbox(url, title = 'Dokumentasi Foto') {
    const modal = document.getElementById('image-lightbox-modal');
    const img = document.getElementById('lightbox-image');
    const titleEl = document.getElementById('lightbox-title');

    if (!modal || !img) return;

    img.src = url;
    if (titleEl) {
        titleEl.innerText = title;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox(e) {
    if (e && e.target && e.target.id === 'lightbox-image') return;
    const modal = document.getElementById('image-lightbox-modal');
    if (modal) {
        modal.classList.add('hidden');
        const img = document.getElementById('lightbox-image');
        if (img) img.src = '';
    }
    document.body.style.overflow = '';
}

async function downloadLightboxImage(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const img = document.getElementById('lightbox-image');
    if (!img || !img.src) return;

    try {
        const response = await fetch(img.src);
        const blob = await response.blob();
        const blobUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        let filename = (img.src.split('/').pop() || 'foto-tiket.jpg').split('?')[0];
        if (!filename.includes('.')) filename += '.jpg';
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
    } catch (err) {
        const a = document.createElement('a');
        a.href = img.src;
        a.target = '_blank';
        a.download = 'foto-tiket.jpg';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('image-lightbox-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeImageLightbox();
        }
        closeMessageInfo();
        closeMessageEdit();
        closeAllMessageMenus();
    }
});

// =========================================================================
// WHATSAPP MESSAGE 3-DOTS ACTION MENU & HELPERS (Matching Image 1 & 2)
// =========================================================================

function showChatToast(text, icon = '✓') {
    const toast = document.getElementById('chat-toast');
    const toastText = document.getElementById('chat-toast-text');
    const toastIcon = document.getElementById('chat-toast-icon');
    if (!toast || !toastText) return;

    toastText.innerText = text;
    if (toastIcon) toastIcon.innerText = icon;
    toast.classList.remove('hidden');

    clearTimeout(window.__chatToastTimeout);
    window.__chatToastTimeout = setTimeout(() => {
        toast.classList.add('hidden');
    }, 2500);
}

function toggleMessageMenu(id, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const targetDropdown = document.getElementById('msg-dropdown-' + id);
    const wasOpen = targetDropdown && !targetDropdown.classList.contains('hidden');

    // Close all open dropdowns
    closeAllMessageMenus();

    if (!wasOpen && targetDropdown) {
        targetDropdown.classList.remove('hidden');
    }
}

function closeAllMessageMenus() {
    document.querySelectorAll('.msg-action-dropdown').forEach(d => d.classList.add('hidden'));
}

// Close dropdown if clicked outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.msg-action-dropdown') && !e.target.closest('button[onclick*="toggleMessageMenu"]')) {
        closeAllMessageMenus();
    }
});

// 1. Salin (Copy Text)
function copyMessageText(id) {
    closeAllMessageMenus();
    const bubble = document.getElementById('chron-bubble-' + id);
    if (!bubble) return;

    let textToCopy = bubble.dataset.message || '';
    if (!textToCopy && bubble.dataset.lat && bubble.dataset.lng) {
        textToCopy = `https://maps.google.com/?q=${bubble.dataset.lat},${bubble.dataset.lng}`;
    } else if (!textToCopy && bubble.dataset.photo) {
        textToCopy = bubble.dataset.photo;
    }

    if (!textToCopy) {
        textToCopy = 'Update koordinasi lapangan';
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            showChatToast('Pesan berhasil disalin ke clipboard', '📋');
        }).catch(() => {
            fallbackCopyText(textToCopy);
        });
    } else {
        fallbackCopyText(textToCopy);
    }
}

function fallbackCopyText(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showChatToast('Pesan berhasil disalin ke clipboard', '📋');
    } catch (e) {
        alert('Gagal menyalin: ' + text);
    }
    document.body.removeChild(textarea);
}

// 2. Info Pesan
function openMessageInfo(id) {
    closeAllMessageMenus();
    const bubble = document.getElementById('chron-bubble-' + id);
    if (!bubble) return;

    const modal = document.getElementById('modal-msg-info');
    if (!modal) return;

    document.getElementById('info-msg-sender').innerText = bubble.dataset.sender || 'User';
    document.getElementById('info-msg-role').innerText = bubble.dataset.role || 'Petugas';
    document.getElementById('info-msg-content').innerText = bubble.dataset.message || '(Hanya lampiran media/lokasi)';
    document.getElementById('info-msg-sent-time').innerText = bubble.dataset.time || '-';
    document.getElementById('info-msg-read-time').innerText = bubble.dataset.read || '-';

    const attachBox = document.getElementById('info-msg-attachment-box');
    const attachText = document.getElementById('info-msg-attachment-text');
    let attachments = [];
    if (bubble.dataset.lat && bubble.dataset.lng) attachments.push(`📍 Titik Lokasi (${Number(bubble.dataset.lat).toFixed(6)}, ${Number(bubble.dataset.lng).toFixed(6)})`);
    if (bubble.dataset.photo) attachments.push('📷 Foto Lampiran');
    if (attachments.length > 0 && attachBox && attachText) {
        attachText.innerText = attachments.join(' • ');
        attachBox.classList.remove('hidden');
    } else if (attachBox) {
        attachBox.classList.add('hidden');
    }

    modal.classList.remove('hidden');
}

function closeMessageInfo() {
    const modal = document.getElementById('modal-msg-info');
    if (modal) modal.classList.add('hidden');
}

// 3. Simpan ke Dokumentasi
function saveToDocumentation(id) {
    closeAllMessageMenus();
    const bubble = document.getElementById('chron-bubble-' + id);
    if (!bubble) return;

    if (bubble.dataset.photo) {
        showChatToast('Tersimpan di arsip dokumentasi tiket', '📁');
        const a = document.createElement('a');
        a.href = bubble.dataset.photo;
        a.download = `dokumentasi-tiket-${id}.jpg`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    } else {
        showChatToast('Catatan disimpan ke arsip tiket', '📁');
    }
}

// 4. Balas (Reply to message)
function replyToMessage(id) {
    closeAllMessageMenus();
    const bubble = document.getElementById('chron-bubble-' + id);
    const input = document.getElementById('quick-chat-input');
    const preview = document.getElementById('chat-reply-preview');
    const replySender = document.getElementById('reply-to-sender');
    const replyText = document.getElementById('reply-to-text');
    const replyIdInput = document.getElementById('chron_reply_to_id');
    const replySenderInput = document.getElementById('chron_reply_sender');
    const replySnippetInput = document.getElementById('chron_reply_snippet');

    if (!bubble) return;

    const sender = bubble.dataset.sender || 'User';
    let msg = bubble.dataset.message || '';
    if (!msg) {
        msg = bubble.dataset.lat ? '📍 Titik Lokasi Lapangan' : (bubble.dataset.photo ? '📷 Lampiran Foto' : 'Pesan');
    }
    // Clean any prior reply tag from snippet
    if (msg.startsWith('[Balas @')) {
        const parts = msg.split(']: ');
        if (parts.length > 1) {
            msg = parts.slice(1).join(']: ');
        }
    }

    if (replyIdInput) replyIdInput.value = id;
    if (replySenderInput) replySenderInput.value = sender;
    if (replySnippetInput) replySnippetInput.value = msg;

    if (replySender) replySender.innerText = 'Membalas ' + sender;
    if (replyText) replyText.innerText = msg;
    if (preview) preview.classList.remove('hidden');

    if (input) {
        input.focus();
        // Clean input if it was prepended with old tag
        if (input.value.startsWith('[Balas @')) {
            input.value = '';
        }
    }
}

function cancelReply() {
    const preview = document.getElementById('chat-reply-preview');
    const replyIdInput = document.getElementById('chron_reply_to_id');
    const replySenderInput = document.getElementById('chron_reply_sender');
    const replySnippetInput = document.getElementById('chron_reply_snippet');
    if (preview) preview.classList.add('hidden');
    if (replyIdInput) replyIdInput.value = '';
    if (replySenderInput) replySenderInput.value = '';
    if (replySnippetInput) replySnippetInput.value = '';
}

function scrollToChronology(id) {
    const target = document.getElementById('chron-row-' + id) || document.getElementById('chron-bubble-' + id);
    if (!target) return;
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const bubble = document.getElementById('chron-bubble-' + id);
    if (bubble) {
        bubble.classList.add('ring-2', 'ring-blue-400');
        setTimeout(() => bubble.classList.remove('ring-2', 'ring-blue-400'), 1500);
    }
}

// 5. Edit Pesan
function editMessage(id) {
    closeAllMessageMenus();
    const bubble = document.getElementById('chron-bubble-' + id);
    if (!bubble) return;

    const modal = document.getElementById('modal-msg-edit');
    const idInput = document.getElementById('edit-msg-id');
    const textarea = document.getElementById('edit-msg-textarea');

    if (!modal || !idInput || !textarea) return;

    idInput.value = id;
    textarea.value = bubble.dataset.message || '';
    modal.classList.remove('hidden');
    setTimeout(() => textarea.focus(), 50);
}

function closeMessageEdit() {
    const modal = document.getElementById('modal-msg-edit');
    if (modal) modal.classList.add('hidden');
}

async function submitEditMessage(e) {
    e.preventDefault();
    const id = document.getElementById('edit-msg-id')?.value;
    const textarea = document.getElementById('edit-msg-textarea');
    const saveBtn = document.getElementById('btn-save-edit-msg');
    const csrf = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';

    if (!id || !textarea) return;
    const newMsg = textarea.value.trim();
    if (!newMsg) return;

    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerText = 'Menyimpan...';
    }

    try {
        const res = await fetch(`/tickets/chronologies/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: newMsg })
        });

        const data = await res.json();
        if (res.ok && data.success) {
            const textEl = document.getElementById('chron-text-' + id);
            const bubble = document.getElementById('chron-bubble-' + id);
            if (textEl) textEl.innerText = newMsg;
            if (bubble) bubble.dataset.message = newMsg;

            closeMessageEdit();
            showChatToast('Pesan berhasil diedit', '✏️');
        } else {
            alert(data.message || 'Gagal mengedit pesan');
        }
    } catch (err) {
        alert('Terjadi kesalahan saat mengedit pesan: ' + err.message);
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Simpan Perubahan';
        }
    }
}

// 6. Hapus Pesan
async function deleteMessage(id) {
    closeAllMessageMenus();
    if (!confirm('Apakah Anda yakin ingin menghapus pesan ini dari koordinasi tiket?')) return;

    const csrf = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
    const row = document.getElementById('chron-row-' + id);

    try {
        const res = await fetch(`/tickets/chronologies/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (row) {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'scale(0.9)';
                setTimeout(() => row.remove(), 300);
            }
            showChatToast('Pesan berhasil dihapus', '🗑️');
        } else {
            alert(data.message || 'Gagal menghapus pesan');
        }
    } catch (err) {
        alert('Terjadi kesalahan saat menghapus pesan: ' + err.message);
    }
}

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
    if (tabId === 'tab-chronology') {
        if (typeof adjustChatHeight === 'function') {
            adjustChatHeight();
        }
        const chatBox = document.getElementById('chat-stream-box');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }
}

function handleChatFormSubmit(e) {
    const input = document.getElementById('quick-chat-input');
    const msg = input ? input.value.trim() : '';
    const lat = document.getElementById('chron_lat')?.value;
    const lng = document.getElementById('chron_lng')?.value;
    const mediaInput = document.getElementById('input_upload_media');
    const videoInput = document.getElementById('input_record_video');
    const hasMedia = (mediaInput && mediaInput.files && mediaInput.files.length > 0) ||
                     (videoInput && videoInput.files && videoInput.files.length > 0);

    // If nothing entered or attached, do not submit
    if (!msg && !lat && !hasMedia) {
        e.preventDefault();
        return;
    }

    // Append optimistic message bubble with pending CLOCK icon (🕒)
    const stream = document.getElementById('chat-stream-container') || document.getElementById('chat-stream-box');
    const scrollBox = document.getElementById('chat-stream-box');
    if (stream) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeStr = `${hours}:${minutes} WIB`;

        const bubble = document.createElement('div');
        bubble.className = 'flex justify-end my-1.5 opacity-90 transition-opacity';
        
        let mediaNotice = '';
        if (hasMedia) {
            mediaNotice = `<div class="p-2 rounded-xl bg-sky-100/70 text-sky-800 text-[11px] font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-sky-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                <span>Mengunggah berkas media...</span>
            </div>`;
        }

        let locNotice = '';
        if (lat && lng) {
            locNotice = `<div class="p-2 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 text-[11px] font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                <span>Lokasi: ${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}</span>
            </div>`;
        }

        const replySender = document.getElementById('chron_reply_sender')?.value;
        const replySnippet = document.getElementById('chron_reply_snippet')?.value;

        let replyBox = '';
        if (replySender) {
            replyBox = `
                <div style="background-color: rgba(3, 105, 161, 0.08); border-left: 4px solid #2563EB; border-radius: 8px;"
                     class="px-2.5 py-1.5 mb-1.5 select-none overflow-hidden text-left">
                    <div class="flex items-center gap-1.5 text-blue-600 font-bold text-[11px] leading-tight">
                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0 transform -rotate-12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/>
                        </svg>
                        <span class="truncate uppercase tracking-wide">${escapeChatHtml(replySender)}</span>
                    </div>
                    ${replySnippet ? `<p class="text-slate-600 text-[11px] truncate mt-0.5 leading-snug">${escapeChatHtml(replySnippet)}</p>` : ''}
                </div>
            `;
        }

        bubble.innerHTML = `
            <div style="background-color: #EAF4FE; border: 1px solid #CFE4FC; box-shadow: 0 1px 2px rgba(0,0,0,0.06); max-width: min(85%, 440px);"
                 class="rounded-2xl rounded-tr-xs p-2.5 sm:p-3 text-xs space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-xs text-teal-950">Anda</span>
                    <span style="background-color: #D6E8FF; color: #0369A1;" class="px-2 py-0.5 rounded-full text-[9px] font-semibold tracking-wide">
                        Teknisi
                    </span>
                </div>
                ${replyBox}
                ${msg ? `<p class="text-slate-800 leading-snug whitespace-pre-line text-xs font-normal">${escapeChatHtml(msg)}</p>` : ''}
                ${mediaNotice}
                ${locNotice}
                <div class="flex justify-end items-center gap-1 text-[10px] text-slate-400 font-mono pt-0.5 select-none">
                    <span>${timeStr}</span>
                    <span title="Mengirim..." class="inline-flex items-center text-slate-400 ml-0.5">
                        <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/>
                        </svg>
                    </span>
                </div>
            </div>
        `;
        stream.appendChild(bubble);
        if (scrollBox) scrollBox.scrollTop = scrollBox.scrollHeight;
    }

    const submitBtn = document.getElementById('btn-quick-chat-submit');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    }
}

function escapeChatHtml(text) {
    const p = document.createElement('p');
    p.textContent = text;
    return p.innerHTML;
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
    if (!label) return;
    if (fileInput.files && fileInput.files.length > 0) {
        label.innerText = '📎 ' + fileInput.files[0].name;
    } else {
        label.innerText = '';
    }
}

// Handlers for WhatsApp-style attachment menu options
function handleFileChosen(input, typeName) {
    const label = document.getElementById('attachment-name');
    const photoChip = document.getElementById('photo-attached-preview');
    const photoText = document.getElementById('photo-preview-text');
    const mediaInput = document.getElementById('input_upload_media');

    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        // If camera plain was picked, transfer file into the form input
        if (input.id === 'input_camera_plain' && mediaInput) {
            const dt = new DataTransfer();
            dt.items.add(file);
            mediaInput.files = dt.files;
        }
        if (label) label.innerText = '📎 ' + typeName + ': ' + file.name;
        if (photoText) photoText.innerText = '📷 ' + typeName + ': ' + file.name;
        if (photoChip) photoChip.classList.remove('hidden');
    }
}

function clearAttachedPhoto() {
    const mediaInput = document.getElementById('input_upload_media');
    const videoInput = document.getElementById('input_record_video');
    const plainInput = document.getElementById('input_camera_plain');
    const gpsRawInput = document.getElementById('input_camera_gps_raw');
    const photoChip = document.getElementById('photo-attached-preview');
    const photoText = document.getElementById('photo-preview-text');
    const label = document.getElementById('attachment-name');

    if (mediaInput) mediaInput.value = '';
    if (videoInput) videoInput.value = '';
    if (plainInput) plainInput.value = '';
    if (gpsRawInput) gpsRawInput.value = '';
    if (photoChip) photoChip.classList.add('hidden');
    if (photoText) photoText.innerText = '';
    if (label && !label.innerText.includes('GPS')) label.innerText = '';
}

let currentGpsAddressLines = [];

// Reverse geocoding helper using Nominatim + BigDataCloud fallback
async function fetchGpsAddress(lat, lng) {
    if (!lat || !lng) return;
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        if (res.ok) {
            const data = await res.json();
            const a = data.address || {};
            const road = a.road || a.street || a.pedestrian || a.residential || '';
            const sub = a.suburb || a.village || a.neighbourhood || a.quarter || '';
            const city = a.city || a.town || a.municipality || a.county || '';
            const state = a.state || '';
            const lines = [];
            if (road) lines.push(road);
            if (sub) lines.push(sub);
            if (city) lines.push(city);
            if (state) lines.push(state);
            if (lines.length > 0) {
                currentGpsAddressLines = lines;
                return;
            }
        }
    } catch (e) {}

    try {
        const res2 = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=id`);
        if (res2.ok) {
            const d = await res2.json();
            const sub = d.locality || '';
            const city = d.city ? (d.city.includes('Kota') ? d.city : 'Kota ' + d.city) : '';
            const state = d.principalSubdivision || 'Jawa Barat';
            const lines = [];
            if (sub) lines.push(sub);
            if (city) lines.push(city);
            if (state) lines.push(state);
            if (lines.length > 0) {
                currentGpsAddressLines = lines;
                return;
            }
        }
    } catch (e) {}

    currentGpsAddressLines = ['Kota Bandung', 'Jawa Barat'];
}

function captureCameraWithGps() {
    // 1. Ambil GPS secara otomatis dan prefetch alamat
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const acc = Math.min(9999, Math.max(0, Math.round((pos.coords.accuracy || 10) * 100) / 100));

                const latInput = document.getElementById('chron_lat');
                const lngInput = document.getElementById('chron_lng');
                const accInput = document.getElementById('chron_acc');
                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
                if (accInput) accInput.value = acc;

                fetchGpsAddress(lat, lng);
            },
            () => {},
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }
    // 2. Buka kamera
    const rawInput = document.getElementById('input_camera_gps_raw');
    if (rawInput) rawInput.click();
}

// Draw top-right GPS constellation / network icon matching user reference photo
function drawWatermarkNetworkIcon(ctx, rightX, topY, size) {
    ctx.save();
    const cx = rightX - size / 2;
    const cy = topY + size / 2;
    const radius = size * 0.12;

    const n1x = cx - size * 0.15;
    const n1y = cy - size * 0.25;
    const n2x = cx - size * 0.35;
    const n2y = cy + size * 0.35;
    const n3x = cx + size * 0.35;
    const n3y = cy - size * 0.05;

    // Connecting cyan lines
    ctx.strokeStyle = 'rgba(24, 196, 196, 0.85)';
    ctx.lineWidth = Math.max(2, size * 0.06);
    ctx.beginPath();
    ctx.moveTo(n2x, n2y);
    ctx.lineTo(n1x, n1y);
    ctx.lineTo(n3x, n3y);
    ctx.stroke();

    // Draw nodes
    [ [n1x, n1y], [n2x, n2y], [n3x, n3y] ].forEach(([nx, ny]) => {
        ctx.beginPath();
        ctx.arc(nx, ny, radius, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(2, 40, 54, 0.55)';
        ctx.fill();
        ctx.strokeStyle = '#18C4C4';
        ctx.lineWidth = Math.max(2, size * 0.06);
        ctx.stroke();
    });

    // Wifi arc above top node
    ctx.beginPath();
    ctx.arc(n1x, n1y, radius * 2.1, Math.PI * 1.25, Math.PI * 1.75);
    ctx.strokeStyle = '#5EEAD4';
    ctx.lineWidth = Math.max(1.5, size * 0.05);
    ctx.stroke();

    ctx.restore();
}

function processGpsWatermark(fileInput) {
    if (!fileInput.files || fileInput.files.length === 0) return;
    const file = fileInput.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        const img = new Image();
        img.onload = async function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');

            // 1. Draw original photo
            ctx.drawImage(img, 0, 0);

            // 2. Coordinates & Date (Matching User Reference Image)
            const latVal = parseFloat(document.getElementById('chron_lat')?.value);
            const lngVal = parseFloat(document.getElementById('chron_lng')?.value);
            const hasCoords = !isNaN(latVal) && !isNaN(lngVal);

            const d = new Date();
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

            let coordStr = '';
            if (hasCoords) {
                const latDir = latVal < 0 ? 'S' : 'N';
                const lngDir = lngVal < 0 ? 'W' : 'E';
                coordStr = `${Math.abs(latVal).toFixed(5)}${latDir} ${Math.abs(lngVal).toFixed(5)}${lngDir}`;
            }

            // Ensure address is fetched if coords are available
            if (hasCoords && currentGpsAddressLines.length === 0) {
                await fetchGpsAddress(latVal, lngVal);
            }
            const addressLines = currentGpsAddressLines.length > 0 ? currentGpsAddressLines : ['Kota Bandung', 'Jawa Barat'];

            // 3. Watermark Text Configuration (Right-aligned, bottom-right corner)
            const fontSize = Math.max(16, Math.round(img.width * 0.024));
            const lineHeight = Math.round(fontSize * 1.35);
            const marginX = Math.round(img.width * 0.045);
            const marginY = Math.round(img.height * 0.045);

            ctx.font = '600 ' + fontSize + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'alphabetic';

            const lines = [dateStr];
            if (coordStr) lines.push(coordStr);
            addressLines.forEach(l => lines.push(l));

            const totalHeight = (lines.length - 1) * lineHeight;
            const startY = img.height - marginY - totalHeight;

            lines.forEach((line, idx) => {
                const lineY = startY + (idx * lineHeight);
                // Subtle dark outline/shadow for high contrast on light or dark backgrounds
                ctx.strokeStyle = 'rgba(0, 0, 0, 0.75)';
                ctx.lineWidth = Math.max(2, Math.round(fontSize * 0.14));
                ctx.strokeText(line, img.width - marginX, lineY);
                // Crisp pure white text
                ctx.fillStyle = '#FFFFFF';
                ctx.fillText(line, img.width - marginX, lineY);
            });

            // 4. Draw top-right network constellation icon
            drawWatermarkNetworkIcon(ctx, img.width - marginX, marginY, Math.max(34, Math.round(img.width * 0.055)));

            // 5. Convert canvas to Blob & assign to input
            canvas.toBlob(function(blob) {
                const watermarkedFile = new File([blob], 'GPS_' + file.name, { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(watermarkedFile);
                const mediaInput = document.getElementById('input_upload_media');
                if (mediaInput) {
                    mediaInput.files = dataTransfer.files;
                }
                const label = document.getElementById('attachment-name');
                if (label) label.innerText = '📸 Watermark GPS Siap';

                const photoChip = document.getElementById('photo-attached-preview');
                const photoText = document.getElementById('photo-preview-text');
                if (photoText) photoText.innerText = '📸 Watermark GPS: ' + file.name;
                if (photoChip) photoChip.classList.remove('hidden');
            }, 'image/jpeg', 0.92);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function shareMyCurrentLocation() {
    const input = document.getElementById('quick-chat-input');
    const label = document.getElementById('attachment-name');
    const preview = document.getElementById('gps-attached-preview');
    const previewText = document.getElementById('gps-preview-text');

    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung Geolocation.');
        return;
    }
    if (label) label.innerText = 'Mengambil GPS...';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            // Clamp accuracy so it never exceeds database decimal(6,2)
            const rawAcc = pos.coords.accuracy || 10;
            const acc = Math.min(9999, Math.max(0, Math.round(rawAcc * 100) / 100));

            const latInput = document.getElementById('chron_lat');
            const lngInput = document.getElementById('chron_lng');
            const accInput = document.getElementById('chron_acc');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;
            if (accInput) accInput.value = acc;

            if (previewText) {
                previewText.innerText = `GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }
            if (preview) {
                preview.classList.remove('hidden');
            }
            if (label) label.innerText = '';

            if (input) {
                input.focus();
            }
        },
        (err) => {
            alert('Gagal mengambil lokasi GPS: ' + err.message);
            if (label) label.innerText = '';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

function clearAttachedGps() {
    const lat = document.getElementById('chron_lat');
    const lng = document.getElementById('chron_lng');
    const acc = document.getElementById('chron_acc');
    const preview = document.getElementById('gps-attached-preview');
    const previewText = document.getElementById('gps-preview-text');
    const label = document.getElementById('attachment-name');

    if (lat) lat.value = '';
    if (lng) lng.value = '';
    if (acc) acc.value = '';
    if (preview) preview.classList.add('hidden');
    if (previewText) previewText.innerText = '';
    if (label && label.innerText.includes('GPS')) label.innerText = '';
}

// Attachment Dropup Menu Controls
function toggleAttachmentMenu(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const menu = document.getElementById('dropdown-attachment-menu');
    if (!menu) return;
    menu.classList.toggle('hidden');
}

function closeAttachmentMenu() {
    const menu = document.getElementById('dropdown-attachment-menu');
    if (menu) menu.classList.add('hidden');
}

// Close attachment menu if clicked outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('dropdown-attachment-menu');
    const btn = document.getElementById('btn-attachment-toggle');
    if (!menu || menu.classList.contains('hidden')) return;
    if (btn && btn.contains(e.target)) return;
    if (!menu.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

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
    const preview = document.getElementById('gps-attached-preview');
    const previewText = document.getElementById('gps-preview-text');
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung Geolocation.');
        return;
    }
    if (label) label.innerText = 'GPS...';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const rawAcc = pos.coords.accuracy || 10;
            const acc = Math.min(9999, Math.max(0, Math.round(rawAcc * 100) / 100));

            document.getElementById('chron_lat').value = pos.coords.latitude;
            document.getElementById('chron_lng').value = pos.coords.longitude;
            document.getElementById('chron_acc').value = acc;
            if (label) label.innerText = `✓ GPS (${pos.coords.latitude.toFixed(3)}, ${pos.coords.longitude.toFixed(3)})`;
            if (previewText) previewText.innerText = `GPS: ${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}`;
            if (preview) preview.classList.remove('hidden');
            const btn = document.getElementById('gps_action_btn');
            if (btn) {
                btn.classList.add('bg-emerald-500/30', 'text-emerald-200', 'border-emerald-400/40');
            }
        },
        (err) => {
            alert('Gagal mengambil lokasi GPS: ' + err.message);
            if (label) label.innerText = 'GPS';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// Toggle Chat Fullscreen Mode (Zoom Full Size)
function toggleChatFullscreen() {
    const chatCard = document.getElementById('ticket-chat-container') || document.querySelector('.ticket-chat-card');
    if (!chatCard) return;

    const isFullscreen = chatCard.classList.toggle('is-chat-fullscreen');
    const expandIcon = document.getElementById('icon-fs-expand');
    const minimizeIcon = document.getElementById('icon-fs-minimize');
    const fsLabel = document.getElementById('fs_label');

    if (isFullscreen) {
        if (expandIcon) expandIcon.classList.add('hidden');
        if (minimizeIcon) minimizeIcon.classList.remove('hidden');
        if (fsLabel) fsLabel.innerText = 'Kecilkan';
        chatCard.style.height = '100dvh';
        document.body.style.overflow = 'hidden';
    } else {
        if (expandIcon) expandIcon.classList.remove('hidden');
        if (minimizeIcon) minimizeIcon.classList.add('hidden');
        if (fsLabel) fsLabel.innerText = 'Full Size';
        document.body.style.overflow = '';
        adjustChatHeight();
    }

    const chatBox = document.getElementById('chat-stream-box');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

// Responsive dynamic height calculation for all devices
function adjustChatHeight() {
    requestAnimationFrame(() => {
        const chatCard = document.querySelector('.ticket-chat-card');
        if (!chatCard) return;

        // Skip recalculation if in fullscreen zoom mode
        if (chatCard.classList.contains('is-chat-fullscreen')) {
            chatCard.style.height = '100dvh';
            return;
        }

        const isDesktop = window.innerWidth >= 768;
        const windowHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;

        let topOffset = 0;
        let bottomOffset = 0;

        if (isDesktop) {
            // Desktop: sticky header (h-16 / ~53px at 82% zoom) + clean clearance
            const header = document.querySelector('header');
            const headerHeight = header ? header.getBoundingClientRect().height : 54;

            // Extra top clearance (+16px) so card top is never clipped under header shadow
            topOffset = headerHeight + 16;
            bottomOffset = 24; 
        } else {
            // Mobile: fixed top header + fixed bottom navbar + safe margin
            const mobHeader = document.querySelector('.md\\:hidden.fixed.top-0');
            const topHeaderHeight = mobHeader ? mobHeader.getBoundingClientRect().height : 48;

            const mobNav = document.querySelector('.md\\:hidden.fixed.bottom-3 nav') || 
                           document.querySelector('.md\\:hidden.fixed.bottom-3');
            const bottomNavHeight = mobNav ? mobNav.getBoundingClientRect().height + 14 : 72;

            topOffset = topHeaderHeight + 14; // Memberi ruang ekstra agar tidak tertutup header mobile
            bottomOffset = bottomNavHeight + 4;
        }

        const calculatedHeight = Math.max(380, Math.floor(windowHeight - topOffset - bottomOffset));
        chatCard.style.height = calculatedHeight + 'px';
    });
}

// Attach listeners for all devices, dynamic viewport changes, and orientation
window.addEventListener('resize', adjustChatHeight);
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', adjustChatHeight);
}
window.addEventListener('orientationchange', adjustChatHeight);

// Auto scroll chat to bottom and adjust height on load
document.addEventListener('DOMContentLoaded', () => {
    adjustChatHeight();
    const chatBox = document.getElementById('chat-stream-box');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});
</script>
@endpush
@endsection
