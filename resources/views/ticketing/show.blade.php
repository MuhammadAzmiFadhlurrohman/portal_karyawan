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
        $isBroadband = ($tiket->kategori_tiket === 'BROADBAND');
        $hasBasicResume = !empty(trim((string)$tiket->problem_temuan)) && !empty(trim((string)$tiket->action_taken));
        $hasOptical = !$isBroadband || ($tiket->redaman_sesudah !== null && (float)$tiket->redaman_sesudah >= -40.0 && (float)$tiket->redaman_sesudah <= 0.0);
        $hasResume = $hasBasicResume && $hasOptical;

        $hasPhoto = $tiket->chronologies->whereNotNull('photo_path')->isNotEmpty() || $tiket->chronologies->where('category', 'SELESAI')->isNotEmpty();
        $hasRepairOrJc = $tiket->repairPoints->isNotEmpty() || $tiket->jointClosures->isNotEmpty();
        $hasJcOrManeuver = $tiket->jointClosures->isNotEmpty() || $tiket->coreManeuvers->isNotEmpty();

        $fulfilledCount = ($hasResume ? 1 : 0) + ($hasPhoto ? 1 : 0) + ($hasRepairOrJc ? 1 : 0) + ($hasJcOrManeuver ? 1 : 0);
        $percentage = $fulfilledCount * 25;
        $remainingCount = 4 - $fulfilledCount;

        // WA Maintenance Message Template
        $tglTiket = $tiket->tanggal_open ?? $tiket->created_at;
        $waDate = $tglTiket ? \Carbon\Carbon::parse($tglTiket)->translatedFormat('d M Y') : date('d M Y');

        if ($tiket->kategori_tiket === 'BACKBONE') {
            $waStatusLabel = 'Status Segment Backbone :';
            $waStatusValue = $tiket->backbone_segment ?: ($tiket->status_link_impact ?: '-');
        } else {
            $waStatusLabel = 'Status Customer Retail :';
            $hasCid = !empty($tiket->customer_id) && !in_array(strtoupper(trim((string)$tiket->customer_id)), ['NON-CID', 'NON CID', '-']);
            $cid = $hasCid ? ' ' . trim((string)$tiket->customer_id) : '';
            $addr = !empty($tiket->customer_address) ? '/' . trim((string)$tiket->customer_address) : '';
            $waStatusValue = trim(($tiket->customer_name ?? 'Pelanggan') . $cid . $addr);
            if (empty($waStatusValue)) {
                $waStatusValue = '-';
            }
        }

        $waProblem = $tiket->problem_temuan ?: ($tiket->deskripsi_gangguan ?: '-');
        $waProblemClean = preg_replace('/^indikasi\s*[:\-]?\s*/i', '', trim((string)$waProblem));
        $waIndikasi = 'Indikasi : ' . ($waProblemClean ?: '-');

        $waFormatText = "-Open Tiket Maintenance-\n"
            . "No Tiket: " . $tiket->no_tiket . "\n\n"
            . $waStatusLabel . "\n"
            . $waStatusValue . "\n\n"
            . $waIndikasi . "\n\n"
            . $waDate . "\n"
            . "Link Tiket: ";
    @endphp

    <!-- ========================================================================= -->
    <!-- 1. TOP HEADER BANNER (Futuristic Metallic Ocean Teal matching Dashboard) -->
    <!-- ========================================================================= -->
    <div class="gomsn-themed-banner relative rounded-2xl md:rounded-3xl p-4 sm:py-5 sm:px-6 text-white border border-white/15 shadow-[0_8px_30px_rgba(2,40,54,0.45)] select-none z-20"
         style="background: var(--gomsn-header-bg) !important;">
        
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

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-3.5 sm:gap-4">
            <!-- Left Info Area -->
            <div class="space-y-2.5 sm:space-y-3 min-w-0 flex-1">
                <!-- Row 1: Badges & Status (Kategori, No Tiket, Status, Prioritas) -->
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <!-- Category Badge -->
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-mono font-extrabold tracking-wider uppercase shadow-xs shrink-0
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? 'bg-[#18C4C4] text-slate-950' : 'bg-emerald-400 text-slate-950' }}">
                        {{ $tiket->kategori_tiket }}
                    </span>

                    <!-- Ticket Number Capsule -->
                    <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-0.5 rounded-full font-mono font-bold text-[11px] sm:text-xs bg-slate-950/50 backdrop-blur-md border border-[#2dd4bf]/40 text-[#5eead4] shadow-xs shrink-0">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-[#2dd4bf]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>{{ $tiket->no_tiket }}</span>
                    </span>

                    <!-- Status Indicator Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider shrink-0
                        {{ $tiket->status === 'CLOSE' ? 'bg-emerald-500/25 text-emerald-300 border border-emerald-400/50' : ($tiket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-500/25 text-purple-200 border border-purple-400/50' : ($tiket->status === 'PROSES' ? 'bg-amber-500/25 text-amber-200 border border-amber-400/50' : 'bg-cyan-500/25 text-cyan-200 border border-cyan-400/50')) }}">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $tiket->status === 'CLOSE' ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                        <span>{{ $tiket->status }}</span>
                    </span>

                    <!-- Priority Badge -->
                    @if($tiket->priority)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-[10px] sm:text-[11px] bg-white/10 border border-white/20 text-white/90 shrink-0">
                            {{ $tiket->priority }}
                        </span>
                    @endif
                </div>

                <!-- Row 2: Headline / Judul Gangguan -->
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-heading font-extrabold text-white tracking-tight leading-snug break-words">
                        {{ $tiket->status_link_impact ?? ($tiket->kategori_tiket === 'BACKBONE' ? $tiket->backbone_segment : $tiket->customer_name) }}
                    </h1>
                </div>

                <!-- Row 3: Tim Penugasan (PIC & Anggota Tim) -->
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 pt-0.5 text-xs">
                    <span class="text-[10px] sm:text-[11px] font-semibold text-teal-200/70 uppercase tracking-wider shrink-0 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-teal-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Tim Lapangan:</span>
                    </span>

                    @if($lead)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:py-1 rounded-xl font-bold text-[11px] sm:text-xs bg-[#18C4C4]/20 border border-[#18C4C4]/40 text-[#5eead4]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#5eead4] shrink-0"></span>
                            <span class="truncate max-w-[180px] sm:max-w-none">PIC: {{ $lead->name }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:py-1 rounded-xl font-semibold text-[11px] sm:text-xs bg-amber-500/20 border border-amber-500/40 text-amber-300">
                            <span>PIC: Belum Ditugaskan</span>
                        </span>
                    @endif

                    @foreach($crew as $c)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 sm:py-1 rounded-xl font-medium text-[11px] sm:text-xs bg-white/10 border border-white/15 text-white/90">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300/70 shrink-0"></span>
                            <span class="truncate max-w-[140px] sm:max-w-none">{{ $c->name }}</span>
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
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0 pt-2 md:pt-0 border-t border-white/10 md:border-t-0 md:self-center">
                <a href="{{ route('tickets.index') }}"
                   class="flex-1 md:flex-initial h-9 sm:h-10 inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 rounded-xl font-semibold text-xs text-teal-100 bg-white/10 hover:bg-white/20 border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer shadow-xs whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>

                <!-- Menu Aksi Dropdown -->
                <div class="relative flex-1 md:flex-initial" x-data="{ open: false }">
                    <button type="button"
                            @click="open = !open"
                            class="w-full md:w-auto h-9 sm:h-10 inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3.5 sm:px-4 rounded-xl font-bold text-xs text-slate-950 shadow-md hover:shadow-cyan-500/25 border border-cyan-300/40 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer whitespace-nowrap"
                            style="background: var(--gomsn-active-bg) !important;">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-950 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span>Menu Aksi</span>
                        <svg class="w-3.5 h-3.5 transition-transform text-slate-950 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open"
                         x-cloak
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
                            <button type="button" @click="open = false; copyWaTicketFormat()"
                                    class="w-full text-left px-3.5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 hover:text-emerald-900 transition flex items-center gap-2.5 group">
                                <svg class="w-4 h-4 text-emerald-600 group-hover:scale-110 transition-transform shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.04 14.69 2 12.04 2ZM12.04 20.17C10.55 20.17 9.09 19.77 7.82 19.01L7.52 18.83L4.4 19.65L5.23 16.61L5.03 16.29C4.2 14.97 3.76 13.46 3.76 11.91C3.76 7.35 7.48 3.63 12.04 3.63C14.25 3.63 16.32 4.49 17.88 6.05C19.44 7.61 20.32 9.69 20.32 11.91C20.32 16.47 16.6 20.17 12.04 20.17ZM16.58 14.39C16.33 14.26 15.11 13.66 14.88 13.58C14.65 13.49 14.49 13.45 14.32 13.7C14.16 13.95 13.68 14.52 13.54 14.69C13.39 14.85 13.25 14.87 13 14.75C12.75 14.62 11.95 14.36 11 13.52C10.26 12.86 9.76 12.05 9.61 11.8C9.47 11.55 9.6 11.42 9.72 11.3C9.83 11.19 9.97 11.01 10.1 10.86C10.22 10.72 10.26 10.61 10.35 10.45C10.43 10.28 10.39 10.14 10.33 10.02C10.26 9.89 9.77 8.69 9.57 8.19C9.37 7.7 9.17 7.77 9.02 7.76C8.88 7.75 8.71 7.75 8.55 7.75C8.38 7.75 8.12 7.81 7.89 8.06C7.66 8.31 7.02 8.91 7.02 10.13C7.02 11.35 7.91 12.52 8.03 12.69C8.16 12.85 9.78 15.35 12.26 16.42C12.85 16.67 13.31 16.83 13.67 16.94C14.26 17.13 14.8 17.1 15.23 17.04C15.7 16.97 16.68 16.45 16.89 15.87C17.09 15.29 17.09 14.79 17.03 14.69C16.97 14.58 16.83 14.52 16.58 14.39Z"/>
                                </svg>
                                <span>Salin ke WA</span>
                            </button>
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

                                <button type="button" @click="open = false; openResumeModal()"
                                        class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-teal-50 hover:text-teal-900 transition flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Input Resume &amp; Closing</span>
                                </button>
                            @endif

                            @if($tiket->status === 'PENDING_VERIFIKASI')
                                @can('verifyClose', $tiket)
                                    <button type="button" @click="open = false; openVerifyCloseModal()"
                                            class="w-full text-left px-3.5 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition flex items-center gap-2.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Verifikasi Closing (NOC)</span>
                                    </button>
                                @endcan
                            @endif
                        </div>

                        <div class="py-1">
                            <div class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                EXPORT DOKUMEN
                            </div>
                            <a href="{{ route('tickets.export.pdf', ['tiket' => $tiket, 'type' => 'internal']) }}" target="_blank"
                               class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-xl bg-rose-100/70 border border-rose-200/50 flex items-center justify-center shrink-0 text-rose-600 shadow-2xs group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5h-2v1.5h2v1.5h-2V16H4V9h3.5c.8 0 1.5.7 1.5 1.5v1c0 .8-.7 1.5-1.5 1.5zm5 4.5h-3.5V9H14c.8 0 1.5.7 1.5 1.5v4c0 .8-.7 1.5-1.5 1.5zm4.5-4.5H17v1.5h2V14h-2v2h-1.5V9H19c.8 0 1.5.7 1.5 1.5v1c0 .8-.7 1.5-1.5 1.5z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 leading-tight">PDF Internal</div>
                                    <div class="text-[10px] text-slate-400 font-normal truncate">Data teknis, mapping core &amp; kronologis</div>
                                </div>
                            </a>
                            <a href="{{ route('tickets.export.pdf', ['tiket' => $tiket, 'type' => 'customer']) }}" target="_blank"
                               class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-xl bg-sky-100/70 border border-sky-200/50 flex items-center justify-center shrink-0 text-sky-600 shadow-2xs group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm6 12H6v-1c0-2 4-3.1 6-3.1s6 1.1 6 3.1v1z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 leading-tight">PDF Pelanggan</div>
                                    <div class="text-[10px] text-slate-400 font-normal truncate">Berita Acara resmi, akar masalah &amp; foto</div>
                                </div>
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
    <!-- 2. FOUR KEY INFO METRIC CARDS (Tertutup Default di Mobile dengan Dropdown Toggle) -->
    <!-- ========================================================================= -->
    <div x-data="{ showParamCards: window.innerWidth >= 640 }" class="space-y-2.5">
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
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-[#0D9488]">Detail</span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-500">
                    <span class="text-[11px] font-semibold" x-text="showParamCards ? 'Sembunyikan' : 'Lihat Card'"></span>
                    <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': showParamCards }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
        </div>

        <!-- 4 CARDS: 2 Baris di Mobile (grid-cols-2), 1 Baris di Desktop (lg:grid-cols-4) -->
        <div x-show="showParamCards" 
             x-cloak
             class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 sm:!grid"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2">
            
            <!-- Card 1: SEGMENT BACKBONE / PELANGGAN -->
            <div class="relative overflow-hidden bg-white/95 rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.04)] hover:shadow-md hover:border-teal-300/60 transition-all duration-200 group flex items-start gap-3 sm:gap-3.5">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-teal-500/10 to-teal-500/20 text-teal-600 border border-teal-500/20 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-200">
                    @if($tiket->kategori_tiket === 'BACKBONE')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? 'Segment Backbone' : 'Pelanggan Broadband' }}
                    </span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 truncate mt-0.5 leading-snug" title="{{ $tiket->kategori_tiket === 'BACKBONE' ? ($tiket->backbone_segment ?? '-') : ($tiket->customer_name ?? '-') }}">
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? ($tiket->backbone_segment ?? '-') : ($tiket->customer_name ?? '-') }}
                    </p>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-500 mt-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500 shrink-0"></span>
                        <span class="truncate">{{ $tiket->kategori_tiket === 'BACKBONE' ? ($tiket->wilayah ?? 'Area Bandung') : ('CID: ' . ($tiket->customer_id ?? 'Non-CID')) }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: WAKTU OPEN -->
            <div class="relative overflow-hidden bg-white/95 rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.04)] hover:shadow-md hover:border-sky-300/60 transition-all duration-200 group flex items-start gap-3 sm:gap-3.5">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-sky-500/10 to-sky-500/20 text-sky-600 border border-sky-500/20 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Waktu Open</span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 truncate mt-0.5 leading-snug">
                        {{ $tiket->tanggal_open ? $tiket->tanggal_open->format('d M, H:i') : '-' }} <span class="text-[10px] sm:text-xs text-slate-400 font-normal">WIB</span>
                    </p>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] text-slate-500 mt-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                        <span class="truncate">{{ $tiket->tanggal_open ? $tiket->tanggal_open->diffForHumans() : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: WAKTU CLOSE -->
            <div class="relative overflow-hidden bg-white/95 rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.04)] hover:shadow-md hover:border-slate-300/80 transition-all duration-200 group flex items-start gap-3 sm:gap-3.5">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $tiket->tanggal_close ? 'bg-gradient-to-br from-emerald-500/10 to-emerald-500/20 text-emerald-600 border border-emerald-500/20' : 'bg-gradient-to-br from-slate-100 to-slate-200/60 text-slate-500 border border-slate-200/80' }} flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-200">
                    @if($tiket->tanggal_close)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Waktu Close</span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 truncate mt-0.5 leading-snug">
                        @if($tiket->tanggal_close)
                            {{ $tiket->tanggal_close->format('d M, H:i') }} <span class="text-[10px] sm:text-xs text-slate-400 font-normal">WIB</span>
                        @else
                            <span class="text-slate-400 font-medium italic text-xs sm:text-sm">Belum tutup</span>
                        @endif
                    </p>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] mt-1 truncate">
                        @if($tiket->tanggal_close)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-emerald-600 font-medium truncate">{{ $tiket->tanggal_close->diffForHumans() }}</span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                            <span class="text-amber-600 font-semibold truncate">Tiket aktif</span>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $isOverdue = $tiket->isOverdue();
                $isClosed = in_array($tiket->status, ['CLOSE', 'CANCELLED'], true);
            @endphp
            <!-- Card 4: TARGET SLA & MTTR -->
            <div class="relative overflow-hidden bg-white/95 rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.04)] hover:shadow-md hover:border-slate-300/80 transition-all duration-200 group flex items-start gap-3 sm:gap-3.5">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $isOverdue ? 'bg-gradient-to-br from-rose-500/10 to-rose-500/20 text-rose-600 border border-rose-500/20' : ($isClosed ? 'bg-gradient-to-br from-emerald-500/10 to-emerald-500/20 text-emerald-600 border border-emerald-500/20' : 'bg-gradient-to-br from-amber-500/10 to-amber-500/20 text-amber-600 border border-amber-500/20') }} flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Target SLA &amp; MTTR</span>
                    <p class="text-xs sm:text-sm font-heading font-extrabold text-slate-800 truncate mt-0.5 leading-snug">
                        Target: <span class="font-bold">{{ round($tiket->sla_target_minutes / 60, 1) }}j</span>
                    </p>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-semibold mt-1 truncate">
                        @if($isOverdue)
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse shrink-0"></span>
                            <span class="text-rose-600 truncate">Overdue ({{ round($tiket->currentNetMinutes() / 60, 1) }}j)</span>
                        @elseif($isClosed)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-emerald-600 truncate">MTTR: {{ $tiket->net_mttr_minutes ? round($tiket->net_mttr_minutes / 60, 1) . 'j' : round($tiket->currentNetMinutes() / 60, 1) . 'j' }}</span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="text-slate-500 truncate">MTTR: <span class="text-amber-600 font-bold">Berjalan...</span></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. DESKRIPSI GANGGUAN & CATATAN AWAL -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.04)] overflow-hidden">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 px-4 sm:px-5 py-3 bg-gradient-to-r from-slate-50/90 via-teal-50/20 to-slate-50/90 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-teal-500/10 text-[#0D9488] flex items-center justify-center shrink-0 border border-teal-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                </div>
                <h3 class="text-xs font-heading font-extrabold text-slate-800 uppercase tracking-wider">
                    Deskripsi Gangguan &amp; Catatan Awal
                </h3>
            </div>

            <!-- Creator info pill -->
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/95 border border-slate-200/80 shadow-2xs text-[11px] text-slate-500">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Pelapor:</span>
                <span class="font-semibold text-slate-700">{{ $tiket->creator->name ?? 'Helpdesk / NOC' }}</span>
                @if($roleName = ($tiket->creator->roles->first()?->name ?? null))
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-[#0D9488] border border-teal-100 uppercase tracking-wider">
                        {{ $roleName }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Body / Content -->
        <div class="p-4 sm:p-5">
            <div class="rounded-xl bg-slate-50/70 border border-slate-100/90 p-3.5 sm:p-4 text-xs sm:text-sm text-slate-700 leading-relaxed font-normal whitespace-pre-line">
                {{ $tiket->problem_temuan ?? 'Tidak ada deskripsi awal gangguan yang dicatat.' }}
            </div>
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

        <!-- 5-Node Stepper Progress Line: Strict 1-Row without scroll, responsive on all mobile screens -->
        <div class="w-full">
            <div class="relative w-full" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));">
                <!-- Track Line (Abu-abu di belakang) -->
                <div class="absolute top-3.5 sm:top-[18px] h-[2px] bg-slate-200" style="left: 10%; right: 10%; z-index: 0;"></div>
                <!-- Active Filled Progress Line (Hijau gradien) -->
                <div class="absolute top-3.5 sm:top-[18px] h-[2px] transition-all duration-500 ease-out" 
                     style="left: 10%; width: calc(80% * ({{ $stepperProgress }} / 100)); background: var(--gomsn-active-bg) !important; z-index: 0;"></div>

                <!-- Stage 1: Open Tiket -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-0.5 sm:px-1 space-y-0.5 sm:space-y-1.5">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center text-[11px] sm:text-sm shadow-sm ring-2 sm:ring-4 ring-white">
                        ✓
                    </div>
                    <div class="min-h-[24px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[9px] sm:text-xs text-slate-800 leading-tight">Open Tiket</span>
                    </div>
                    <div class="h-3.5 sm:h-5 flex items-center justify-center">
                        <span class="text-[8px] sm:text-[11px] text-slate-500 font-mono font-medium leading-none">{{ $tiket->tanggal_open ? $tiket->tanggal_open->format('d/m H:i') : '-' }}</span>
                    </div>
                    <div>
                        <span class="px-1.5 sm:px-2 py-0.2 sm:py-0.5 rounded-full text-[8px] sm:text-[10px] font-bold bg-emerald-50 text-emerald-700 whitespace-nowrap">Stage 1</span>
                    </div>
                </div>

                <!-- Stage 2: Respon Pertama -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-0.5 sm:px-1 space-y-0.5 sm:space-y-1.5">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-[11px] sm:text-sm shadow-sm ring-2 sm:ring-4 ring-white
                        {{ $tiket->first_response_at ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->first_response_at ? '✓' : '2' }}
                    </div>
                    <div class="min-h-[24px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[9px] sm:text-xs text-slate-800 leading-tight">Respon Pertama</span>
                    </div>
                    <div class="h-3.5 sm:h-5 flex items-center justify-center">
                        <span class="text-[8px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->first_response_at ? $tiket->first_response_at->format('d/m H:i') : 'Menunggu' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-1.5 sm:px-2 py-0.2 sm:py-0.5 rounded-full text-[8px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->first_response_at ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->first_response_at ? 'Terespon' : 'Pending' }}
                        </span>
                    </div>
                </div>

                <!-- Stage 3: Jeda SLA (Stop Clock) -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-0.5 sm:px-1 space-y-0.5 sm:space-y-1.5">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-[11px] sm:text-sm shadow-sm ring-2 sm:ring-4 ring-white
                        {{ $tiket->total_stop_clock_minutes > 0 || $tiket->activeStopClock ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->activeStopClock ? '⏸' : ($tiket->total_stop_clock_minutes > 0 ? '✓' : '3') }}
                    </div>
                    <div class="min-h-[24px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[9px] sm:text-xs text-slate-800 leading-tight">Jeda SLA</span>
                    </div>
                    <div class="h-3.5 sm:h-5 flex items-center justify-center">
                        <span class="text-[8px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->total_stop_clock_minutes > 0 ? $tiket->total_stop_clock_minutes . 'm' : ($tiket->activeStopClock ? 'Aktif' : 'Menunggu') }}
                        </span>
                    </div>
                    <div>
                        <span class="px-1.5 sm:px-2 py-0.2 sm:py-0.5 rounded-full text-[8px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->activeStopClock ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->activeStopClock ? 'Aktif' : 'Normal' }}
                        </span>
                    </div>
                </div>

                <!-- Stage 4: Closing Awal Lapangan -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-0.5 sm:px-1 space-y-0.5 sm:space-y-1.5">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-[11px] sm:text-sm shadow-sm ring-2 sm:ring-4 ring-white
                        {{ $tiket->closing_awal_at ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->closing_awal_at ? '✓' : '4' }}
                    </div>
                    <div class="min-h-[24px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[9px] sm:text-xs text-slate-800 leading-tight">Closing Lapangan</span>
                    </div>
                    <div class="h-3.5 sm:h-5 flex items-center justify-center">
                        <span class="text-[8px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->closing_awal_at ? $tiket->closing_awal_at->format('d/m H:i') : 'Menunggu' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-1.5 sm:px-2 py-0.2 sm:py-0.5 rounded-full text-[8px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->closing_awal_at ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->closing_awal_at ? 'Diajukan' : 'Proses' }}
                        </span>
                    </div>
                </div>

                <!-- Stage 5: Closing Akhir (NOC) -->
                <div class="flex flex-col items-center justify-between text-center relative z-10 px-0.5 sm:px-1 space-y-0.5 sm:space-y-1.5">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full font-bold flex items-center justify-center text-[11px] sm:text-sm shadow-sm ring-2 sm:ring-4 ring-white
                        {{ $tiket->status === 'CLOSE' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                        {{ $tiket->status === 'CLOSE' ? '✓' : '5' }}
                    </div>
                    <div class="min-h-[24px] sm:min-h-[32px] flex items-center justify-center">
                        <span class="font-heading font-extrabold text-[9px] sm:text-xs text-slate-800 leading-tight">Closing NOC</span>
                    </div>
                    <div class="h-3.5 sm:h-5 flex items-center justify-center">
                        <span class="text-[8px] sm:text-[11px] text-slate-400 font-mono leading-none">
                            {{ $tiket->verified_at ? $tiket->verified_at->format('d/m H:i') : 'Menunggu' }}
                        </span>
                    </div>
                    <div>
                        <span class="px-1.5 sm:px-2 py-0.2 sm:py-0.5 rounded-full text-[8px] sm:text-[10px] font-bold whitespace-nowrap {{ $tiket->status === 'CLOSE' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tiket->status === 'CLOSE' ? 'Selesai' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. OVERDUE COMPLIANCE ALERT (30-Min SOP Interval - matching Image 2) -->
    <!-- ========================================================================= -->
    @php
        $lastChron = $tiket->relationLoaded('chronologies') && $tiket->chronologies->isNotEmpty()
            ? $tiket->chronologies->last()
            : $tiket->chronologies()->reorder()->latest('id')->first();
        $refTime = $lastChron?->created_at ?? $tiket->first_response_at ?? $tiket->tanggal_open;
        $isIntervalOverdue = false;
        if ($refTime && in_array($tiket->status, ['PROSES', 'OPEN'], true) && !$tiket->activeStopClock()->exists()) {
            $diffMins = (int) abs($refTime->diffInMinutes(now()));
            $isIntervalOverdue = ($diffMins > 30);
        }
    @endphp
    @if($isIntervalOverdue && $tiket->status !== 'CLOSE' && $tiket->status !== 'CANCELLED')
        <div id="overdue-interval-alert" class="rounded-2xl p-4 sm:p-5 bg-rose-50 border-2 border-rose-300 shadow-sm space-y-3 transition-all duration-300">
            <div class="flex items-center gap-2 text-rose-800">
                <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
                <h3 class="font-heading font-black text-xs sm:text-sm uppercase tracking-wider text-rose-900">
                    OVERDUE &gt; 30 MENIT Wajib Kirim Update!
                </h3>
            </div>
            <p class="text-xs text-rose-700">
                Terakhir update: <strong class="text-rose-900">{{ $lastChron?->created_at?->diffForHumans() ?? 'Belum ada update' }}</strong> &bull; SOP interval koordinasi teknisi maksimal 30 menit.
            </p>
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <div class="flex items-center gap-4 text-xs font-mono">
                    <span class="px-3 py-1 rounded-xl bg-white border border-rose-200 text-rose-800 font-bold">
                        TERAKHIR: {{ $lastChron?->created_at ? $lastChron->created_at->timezone('Asia/Jakarta')->format('H:i') : '-' }} WIB
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-white border border-rose-200 text-slate-700">
                        STATUS: OVERDUE INTERVAL
                    </span>
                </div>
                @can('chat', $tiket)
                <button type="button" onclick="switchTab('tab-chronology'); if(document.getElementById('quick-chat-input')) document.getElementById('quick-chat-input').focus();"
                        class="px-4 py-2 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Kirim Update Sekarang</span>
                </button>
                @endcan
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
                            @if($hasResume)
                                Sudah Lengkap
                            @elseif(!$hasBasicResume)
                                Belum diisi lengkap
                            @elseif(!$hasOptical)
                                Redaman sesudah wajib diisi
                            @else
                                Belum diisi lengkap
                            @endif
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
                    style="background: var(--gomsn-active-bg) !important;">
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
        /* Fullscreen Zoom Mode Animation & Styling */
        @keyframes chatFsEnter {
            0% {
                opacity: 0;
                transform: scale(0.94) translateY(14px);
                filter: blur(2px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
                filter: blur(0);
            }
        }

        @keyframes chatFsExit {
            0% {
                opacity: 1;
                transform: scale(1) translateY(0);
                filter: blur(0);
            }
            100% {
                opacity: 0;
                transform: scale(0.97) translateY(4px);
                filter: blur(1px);
            }
        }

        @keyframes chatFsSettle {
            0% {
                opacity: 0.95;
                transform: scale(0.99);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        #chat-fs-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 22, 25, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 9998;
            opacity: 0;
            transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }

        #chat-fs-backdrop.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        .ticket-chat-card.is-chat-fullscreen {
            position: fixed !important;
            inset: 0 !important;
            z-index: 9999 !important;
            height: 100dvh !important;
            width: 100vw !important;
            max-width: none !important;
            border-radius: 0 !important;
            border: none !important;
            box-shadow: 0 25px 50px -12px rgba(2, 22, 25, 0.5) !important;
            margin: 0 !important;
            background-color: #EFEAE2 !important;
            background-image: url('{{ asset('assets/images/whatsapp-chat-bg.jpg') }}?v=2') !important;
            background-repeat: repeat !important;
            background-size: 420px auto !important;
            background-position: top left !important;
            transform-origin: center center;
        }

        html.dark .ticket-chat-card.is-chat-fullscreen,
        .dark .ticket-chat-card.is-chat-fullscreen {
            background-color: #0b141a !important;
            background-image: url('{{ asset('assets/images/whatsapp-chat-bg-dark.png') }}?v=2') !important;
        }

        #chat-stream-box {
            background-color: #EFEAE2;
            background-image: url('{{ asset('assets/images/whatsapp-chat-bg.jpg') }}?v=2');
            background-repeat: repeat;
            background-size: 420px auto;
            background-position: top left;
        }

        html.dark #chat-stream-box,
        .dark #chat-stream-box {
            background-color: #0b141a !important;
            background-image: url('{{ asset('assets/images/whatsapp-chat-bg-dark.png') }}?v=2') !important;
            background-repeat: repeat !important;
            background-size: 420px auto !important;
            background-position: top left !important;
        }

        html.dark .chat-date-divider span,
        .dark .chat-date-divider span {
            background-color: rgba(18, 30, 39, 0.95) !important;
            color: #cbd5e1 !important;
            border-color: rgba(51, 65, 85, 0.75) !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.35) !important;
        }
        html.dark .chat-date-divider span svg,
        .dark .chat-date-divider span svg {
            color: #94a3b8 !important;
        }

        html.dark #btn-scroll-to-bottom,
        .dark #btn-scroll-to-bottom {
            background: rgba(17, 27, 33, 0.95) !important;
            border-color: rgba(45, 212, 191, 0.4) !important;
            color: #5eead4 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4) !important;
        }
        html.dark #btn-scroll-to-bottom svg,
        .dark #btn-scroll-to-bottom svg {
            color: #5eead4 !important;
        }

        .ticket-chat-card.is-chat-fullscreen.chat-fs-entering {
            animation: chatFsEnter 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            will-change: transform, opacity, filter;
        }

        .ticket-chat-card.is-chat-fullscreen.chat-fs-exiting {
            animation: chatFsExit 0.22s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
            will-change: transform, opacity, filter;
            pointer-events: none !important;
        }

        .ticket-chat-card.chat-fs-settling {
            animation: chatFsSettle 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            transform-origin: center center;
            will-change: transform, opacity;
        }

        .ticket-chat-card.is-chat-fullscreen #chat-stream-box {
            background: transparent !important;
        }

        .ticket-chat-card.is-chat-fullscreen .chat-header-banner {
            border-radius: 0 !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        /* Floating WhatsApp-Style Input Bar in Fullscreen (All Viewports including Landscape Mobile & Desktop) */
        .ticket-chat-card.is-chat-fullscreen .chat-quick-templates-bar {
            background: transparent !important;
            border-top: none !important;
            border-bottom: none !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .ticket-chat-card.is-chat-fullscreen .chat-quick-templates-bar button {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
            border-color: rgba(226, 232, 240, 0.95) !important;
            background-color: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(4px);
            padding: 0.25rem 0.75rem !important;
            font-size: 11px !important;
        }
        .ticket-chat-card.is-chat-fullscreen #quick-chat-form {
            background: transparent !important;
            border-top: none !important;
            padding: 0.2rem 0.75rem 0.85rem 0.75rem !important;
            padding-bottom: calc(max(0.85rem, env(safe-area-inset-bottom)) + 0.35rem) !important;
        }
        .ticket-chat-card.is-chat-fullscreen #quick-chat-form .chat-form-inner {
            padding: 0 !important;
        }
        .ticket-chat-card.is-chat-fullscreen #chat-input-container {
            border-radius: 9999px !important;
            background: #FFFFFF !important;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.09) !important;
            border: 1px solid rgba(226, 232, 240, 0.95) !important;
            padding-left: 0.35rem !important;
            padding-right: 0.85rem !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            min-height: 40px !important;
        }
        .ticket-chat-card.is-chat-fullscreen #chat-input-container:focus-within {
            border-color: #0D9488 !important;
            box-shadow: 0 0 0 2.5px rgba(24, 196, 196, 0.22), 0 3px 12px rgba(0, 0, 0, 0.09) !important;
        }
        .ticket-chat-card.is-chat-fullscreen #quick-chat-input {
            font-size: 0.8125rem !important;
            line-height: 1.25rem !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-attachment-toggle {
            border-radius: 9999px !important;
            width: 32px !important;
            height: 32px !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: #64748B !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.15s ease !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-attachment-toggle:hover {
            color: #0D9488 !important;
            background: rgba(240, 253, 250, 0.95) !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-voice-to-text {
            border-radius: 9999px !important;
            width: 40px !important;
            height: 40px !important;
            background: #FFFFFF !important;
            border: 1px solid rgba(226, 232, 240, 0.95) !important;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.09) !important;
            color: #64748B !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            transition: all 0.15s ease !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-voice-to-text:hover {
            color: #0D9488 !important;
            background: rgba(240, 253, 250, 0.95) !important;
            border-color: rgba(20, 184, 166, 0.4) !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-voice-to-text.is-recording {
            background: #FFF1F2 !important;
            border-color: #FDA4AF !important;
            color: #E11D48 !important;
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.22), 0 3px 12px rgba(244, 63, 94, 0.28) !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-voice-to-text svg {
            width: 1.1rem !important;
            height: 1.1rem !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-quick-chat-submit {
            border-radius: 9999px !important;
            width: 40px !important;
            height: 40px !important;
            box-shadow: 0 3px 12px rgba(13, 148, 136, 0.42) !important;
            flex-shrink: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .ticket-chat-card.is-chat-fullscreen #btn-quick-chat-submit svg {
            width: 1.1rem !important;
            height: 1.1rem !important;
            margin-right: 0 !important;
        }
        .ticket-chat-card.is-chat-fullscreen #chat-reply-preview,
        .ticket-chat-card.is-chat-fullscreen #photo-attached-preview,
        .ticket-chat-card.is-chat-fullscreen #gps-attached-preview {
            margin: 0 0 0.4rem 0 !important;
            border-radius: 0.9rem !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
        }

        /* Mobile Fullscreen: Mengapung ke atas & ukuran besar nyaman seperti WhatsApp Mobile */
        @media (max-width: 767.98px) {
            .ticket-chat-card.is-chat-fullscreen .chat-quick-templates-bar {
                padding-top: 0.35rem !important;
                padding-bottom: 0.35rem !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .ticket-chat-card.is-chat-fullscreen #quick-chat-form {
                padding-top: 0.25rem !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                padding-bottom: calc(max(1.1rem, env(safe-area-inset-bottom)) + 0.4rem) !important;
            }
            .ticket-chat-card.is-chat-fullscreen #chat-input-container {
                min-height: 48px !important;
                padding-left: 0.45rem !important;
                padding-right: 1rem !important;
                padding-top: 0.35rem !important;
                padding-bottom: 0.35rem !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12) !important;
            }
            .ticket-chat-card.is-chat-fullscreen #quick-chat-input {
                font-size: 0.9375rem !important;
                line-height: 1.4rem !important;
            }
            .ticket-chat-card.is-chat-fullscreen #btn-attachment-toggle {
                width: 40px !important;
                height: 40px !important;
            }
            .ticket-chat-card.is-chat-fullscreen #btn-attachment-toggle svg {
                width: 1.35rem !important;
                height: 1.35rem !important;
            }
            .ticket-chat-card.is-chat-fullscreen #btn-voice-to-text {
                width: 48px !important;
                height: 48px !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12) !important;
            }
            .ticket-chat-card.is-chat-fullscreen #btn-voice-to-text svg {
                width: 1.35rem !important;
                height: 1.35rem !important;
            }
            .ticket-chat-card.is-chat-fullscreen #btn-quick-chat-submit {
                width: 48px !important;
                height: 48px !important;
                box-shadow: 0 4px 16px rgba(13, 148, 136, 0.45) !important;
            }
            .ticket-chat-card.is-chat-fullscreen #btn-quick-chat-submit svg {
                width: 1.35rem !important;
                height: 1.35rem !important;
            }
        }

        /* Desktop Fullscreen: Center bottom bar (templates + input) to 80% width cleanly */
        @media (min-width: 768px) {
            .ticket-chat-card.is-chat-fullscreen .chat-quick-templates-bar {
                width: 80% !important;
                max-width: 1080px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            .ticket-chat-card.is-chat-fullscreen .chat-quick-templates-bar > div {
                flex-wrap: wrap !important;
                justify-content: center !important;
                overflow-x: visible !important;
                gap: 0.375rem !important;
            }
            .ticket-chat-card.is-chat-fullscreen .chat-quick-templates-bar > div > span {
                display: none !important;
            }
            .ticket-chat-card.is-chat-fullscreen #quick-chat-form {
                width: 80% !important;
                max-width: 1080px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
    </style>
    <div id="tab-chronology" class="tab-pane space-y-4">
        <!-- Backdrop for Chat Full Size Transition -->
        <div id="chat-fs-backdrop"
             onclick="toggleChatFullscreen()"
             class="hidden"
             title="Tutup Tampilan Penuh (Esc)"></div>

        <!-- Placeholder to prevent layout shift & page height collapse during Full Size mode -->
        <div id="chat-fs-placeholder" class="hidden rounded-3xl" aria-hidden="true"></div>

        <div id="ticket-chat-container" class="ticket-chat-card bg-white rounded-3xl border border-slate-200/80 shadow-xs flex flex-col relative"
             style="overflow: visible !important;">
            
            <!-- Dark Ocean Teal Chat Header Banner -->
            <div class="chat-header-banner px-3.5 sm:px-5 py-2.5 sm:py-3 text-white flex items-center justify-between gap-2 shrink-0 rounded-t-3xl overflow-hidden"
                 style="background: var(--gomsn-header-bg) !important;">
                <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#18C4C4]/20 border border-[#18C4C4]/30 flex items-center justify-center text-[#5eead4] shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-heading font-extrabold text-xs text-white truncate">Koordinasi Update Lapangan</h3>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1 shrink-0 leading-none">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Live</span>
                            </span>
                        </div>
                        <p class="text-[10px] text-teal-200/80 truncate leading-tight mt-0.5">Tiket <span class="font-mono text-[#5eead4] font-semibold">{{ $tiket->no_tiket }}</span></p>
                    </div>
                </div>

                <!-- Header Actions: Fullscreen Zoom (Full Size) -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="toggleChatFullscreen()"
                            id="btn-chat-fullscreen"
                            title="Tampilan Penuh (Full Size) / Tekan Esc untuk keluar"
                            class="text-[11px] text-white flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-500/30 hover:bg-teal-500/50 border border-teal-400/40 shadow-xs transition-all duration-200 active:scale-95 cursor-pointer">
                        <!-- Icon Expand / Maximize -->
                        <svg id="icon-fs-expand" class="w-3 h-3 text-cyan-300 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <!-- Icon Minimize (hidden by default) -->
                        <svg id="icon-fs-minimize" class="w-3 h-3 text-cyan-300 hidden transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4v4m0 0H5m4 0L4 3m11 1v4m0 0h4m-4 0l5-5M9 20v-4m0 0H5m4 0l-5 5m11-1v-4m0 0h4m-4 0l5 5"/>
                        </svg>
                        <span id="fs_label" class="text-[10.5px] font-bold text-cyan-200 transition-colors duration-200">Full Size</span>
                    </button>
                </div>
            </div>

            <!-- WhatsApp Chat Body with Bubble Thread -->
            <div class="flex-1 min-h-0 relative w-full flex flex-col">
                <div id="chat-stream-box"
                     onscroll="handleChatScrollPosition()"
                     class="p-3 sm:p-4 flex-1 min-h-0 overflow-y-auto relative w-full">
                
                <!-- Full Width Conversation Stream Container -->
                <div id="chat-stream-container" class="w-full space-y-1.5 sm:space-y-2">
                    @php
                        $formatDateDivider = function ($dateTime) {
                            if (!$dateTime) return 'Hari Ini';
                            $c = $dateTime instanceof \Carbon\CarbonInterface ? $dateTime : \Carbon\Carbon::parse($dateTime);
                            if ($c->isToday()) {
                                return 'Hari Ini';
                            }
                            if ($c->isYesterday()) {
                                return 'Kemarin';
                            }
                            return $c->isoFormat('dddd, D MMMM Y');
                        };

                        $currentDateGroup = null;
                        $initialDate = $tiket->tanggal_open ?? $tiket->created_at;
                        $initialDateKey = $initialDate ? $initialDate->format('Y-m-d') : null;
                    @endphp

                    @if($initialDateKey)
                        @php $currentDateGroup = $initialDateKey; @endphp
                        <!-- Date divider (WhatsApp Date Pill) -->
                        <div class="flex justify-center my-2.5 py-0.5 pointer-events-none chat-date-divider" data-date="{{ $initialDateKey }}">
                            <span class="px-3 py-1 rounded-xl text-[10.5px] font-semibold bg-white/95 text-slate-600 shadow-[0_1px_3px_rgba(0,0,0,0.08)] border border-slate-200/70 backdrop-blur-xs flex items-center gap-1.5 pointer-events-auto"
                                  title="{{ $initialDate->isoFormat('dddd, D MMMM Y') }}">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ $formatDateDivider($initialDate) }}</span>
                            </span>
                        </div>
                    @endif

                    <!-- Initial Ticket Creation System Note -->
                    <div class="flex justify-start items-start gap-1.5 sm:gap-2 my-1">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden shrink-0 border border-teal-200/90 shadow-2xs flex items-center justify-center bg-teal-600 text-white select-none mt-0.5"
                             title="{{ $tiket->creator->name ?? 'Helpdesk' }} (NOC)">
                            @if($tiket->creator?->avatar_url)
                                <img src="{{ $tiket->creator->avatar_url }}" alt="{{ $tiket->creator->name }}" class="w-full h-full object-cover" onerror="this.remove()">
                            @else
                                <span class="text-[10px] sm:text-[11px] font-bold text-white">{{ $tiket->creator?->initials ?? 'NOC' }}</span>
                            @endif
                        </div>
                        <div style="max-width: min(80%, 420px);"
                             class="bg-white dark:bg-slate-800/95 rounded-xl rounded-tl-xs px-3 py-2 shadow-xs border border-slate-200/90 dark:border-slate-700/80 text-[11px] space-y-1">
                            <div class="flex items-center justify-between gap-2.5">
                                <span class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                    <span class="text-[11px]">{{ $tiket->creator->name ?? 'Helpdesk' }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 font-bold">NOC</span>
                                </span>
                                <span class="text-[9.5px] text-slate-400 dark:text-slate-400 font-mono">{{ $tiket->tanggal_open ? $tiket->tanggal_open->format('H:i') : '' }} WIB</span>
                            </div>
                            <p class="text-slate-700 dark:text-slate-200 leading-snug font-medium text-[11px]">
                                🎫 <strong>OPEN TIKET BARU</strong>: Tiket dibuat dengan status OPEN. Kendala: {{ $tiket->problem_temuan ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Dispatch Note if available -->
                    @if($lead)
                        @php
                            $dispatchDate = $lead->pivot->assigned_at ? \Carbon\Carbon::parse($lead->pivot->assigned_at) : null;
                            $dispatchDateKey = $dispatchDate ? $dispatchDate->format('Y-m-d') : null;
                        @endphp
                        @if($dispatchDateKey && $dispatchDateKey !== $currentDateGroup)
                            @php $currentDateGroup = $dispatchDateKey; @endphp
                            <div class="flex justify-center my-2.5 py-0.5 pointer-events-none chat-date-divider" data-date="{{ $dispatchDateKey }}">
                                <span class="px-3 py-1 rounded-xl text-[10.5px] font-semibold bg-white/95 text-slate-600 shadow-[0_1px_3px_rgba(0,0,0,0.08)] border border-slate-200/70 backdrop-blur-xs flex items-center gap-1.5 pointer-events-auto"
                                      title="{{ $dispatchDate->isoFormat('dddd, D MMMM Y') }}">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $formatDateDivider($dispatchDate) }}</span>
                                </span>
                            </div>
                        @endif
                        <div class="flex justify-start items-start gap-1.5 sm:gap-2 my-1">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden shrink-0 border border-amber-300 shadow-2xs flex items-center justify-center bg-amber-500 text-white select-none mt-0.5"
                                 title="Manager Teknis / Atasan">
                                <span class="text-[10px] sm:text-[11px] font-bold text-white">MT</span>
                            </div>
                            <div style="max-width: min(80%, 420px);"
                                 class="bg-amber-50/90 dark:bg-amber-950/40 rounded-xl rounded-tl-xs px-3 py-2 shadow-xs border border-amber-200/90 dark:border-amber-700/60 text-[11px] space-y-1">
                                <div class="flex items-center justify-between gap-2.5">
                                    <span class="font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100 font-bold flex items-center justify-center text-[8px]">MT</span>
                                        <span class="text-[11px]">Manager Teknis</span>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100 font-bold">DISPATCH</span>
                                    </span>
                                    <span class="text-[9.5px] text-amber-700 dark:text-amber-300 font-mono">{{ $lead->pivot->assigned_at ? \Carbon\Carbon::parse($lead->pivot->assigned_at)->format('H:i') : '' }} WIB</span>
                                </div>
                                <p class="text-amber-950 dark:text-amber-100 leading-snug text-[11px]">
                                    👷 <strong>DISPATCH / PENUGASAN TIM</strong>: Ditugaskan <strong>{{ $lead->name }}</strong> (Lead) bersama tim: 
                                    {{ $crew->pluck('name')->join(', ') ?: 'mandiri' }} untuk menangani perbaikan tiket ini.
                                </p>
                            </div>
                        </div>
                    @endif

                <!-- Chronology Entries List -->
                @forelse($tiket->chronologies as $chron)
                    @php
                        $chronDate = $chron->created_at;
                        $chronDateKey = $chronDate ? $chronDate->format('Y-m-d') : null;
                    @endphp

                    @if($chronDateKey && $chronDateKey !== $currentDateGroup)
                        @php $currentDateGroup = $chronDateKey; @endphp
                        <!-- WhatsApp Date Divider for Chronology Group -->
                        <div class="flex justify-center my-2.5 py-0.5 pointer-events-none chat-date-divider" data-date="{{ $chronDateKey }}">
                            <span class="px-3 py-1 rounded-xl text-[10.5px] font-semibold bg-white/95 text-slate-600 shadow-[0_1px_3px_rgba(0,0,0,0.08)] border border-slate-200/70 backdrop-blur-xs flex items-center gap-1.5 pointer-events-auto"
                                  title="{{ $chronDate->isoFormat('dddd, D MMMM Y') }}">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ $formatDateDivider($chronDate) }}</span>
                            </span>
                        </div>
                    @endif

                    @php
                        $isSelf = auth()->check() && $chron->user_id === auth()->id();
                        $roleLabel = $chron->user?->chat_badge ?? ($chron->user?->division ?: ($chron->category ?? 'Petugas'));
                        
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
                            $quotedSender = ($chron->replyTo->user_id === auth()->id()) ? 'Anda' : ($chron->replyTo->user->name ?? 'User');
                            $parentRawMsg = trim((string) ($chron->replyTo->message ?? ''));
                            if ($parentRawMsg !== '' && preg_match('/^\[Balas @[^\]]+\]:\s*(.*)$/s', $parentRawMsg, $pm)) {
                                $parentRawMsg = trim($pm[1]);
                            }
                            $quotedSnippet = $parentRawMsg !== '' ? $parentRawMsg : ($chron->replyTo->latitude ? '📍 Titik Lokasi Lapangan' : ($chron->replyTo->photo_path ? '📷 Lampiran Foto' : ($chron->replyTo->video_path ? '🎥 Lampiran Video' : 'Pesan')));
                            if ($displayMessage && preg_match('/^\[Balas @[^\]]+\]:\s*(.*)$/s', $displayMessage, $cm)) {
                                $cleanMessage = trim($cm[1]);
                            }
                        } elseif ($displayMessage && preg_match('/^\[Balas @([^\]\|]+)(?:\s*\|\s*([^\]]+))?\]:\s*(.*)$/s', $displayMessage, $matches)) {
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
                    <div class="flex {{ $isSelf ? 'justify-end' : 'justify-start' }} items-start gap-1.5 sm:gap-2 my-1 group/bubble relative" id="chron-row-{{ $chron->id }}">
                        @if(!$isSelf)
                            <!-- Circular Avatar Others (Left) -->
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden shrink-0 border border-slate-200/90 shadow-2xs flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 select-none mt-0.5"
                                 title="{{ $chron->user->name ?? 'User' }}">
                                @if($chron->user?->avatar_url)
                                    <img src="{{ $chron->user->avatar_url }}" alt="{{ $chron->user->name }}" class="w-full h-full object-cover" onerror="this.remove()">
                                @else
                                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-700">{{ $chron->user?->initials ?? 'U' }}</span>
                                @endif
                            </div>
                        @endif

                        <div id="chron-bubble-{{ $chron->id }}"
                             data-id="{{ $chron->id }}"
                             data-sender="{{ $isSelf ? 'Anda' : ($chron->user->name ?? 'User') }}"
                             data-real-sender="{{ $chron->user->name ?? 'User' }}"
                             data-role="{{ str_replace('_', ' ', $roleLabel) }}"
                             data-message="{{ $cleanMessage }}"
                             data-time="{{ $chron->created_at->format('d M Y, H:i') }} WIB"
                             data-read="{{ $isRead ? ($chron->read_at ? $chron->read_at->format('d M Y, H:i') . ' WIB' : 'Sudah dibaca') : 'Belum dibaca' }}"
                             data-lat="{{ $chron->latitude ?? '' }}"
                             data-lng="{{ $chron->longitude ?? '' }}"
                             data-photo="{{ $chron->photo_url ?? ($chron->photo_path ? asset('storage/' . $chron->photo_path) : '') }}"
                             data-category="{{ $chron->category ?? 'LAIN' }}"
                             style="{{ $isSelf ? 'background-color: #EAF4FE; border: 1px solid #CFE4FC;' : 'background-color: #FFFFFF; border: 1px solid #E2E8F0;' }} box-shadow: 0 1px 2px rgba(0,0,0,0.05); max-width: min(80%, 380px); min-width: 140px;"
                             class="rounded-xl px-2.5 py-1.5 sm:px-3 sm:py-2 text-xs space-y-1 {{ $isSelf ? 'rounded-tr-xs' : 'rounded-tl-xs' }} relative">
                            
                            <!-- Sender & Role Badge + 3-Dots Dropdown Trigger (Matching User Attachment Image 1 & 2) -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="font-bold text-[11px] {{ $isSelf ? 'text-teal-950' : 'text-slate-800' }} truncate">
                                        {{ $isSelf ? 'Anda' : ($chron->user->name ?? 'User') }}
                                    </span>
                                    @php
                                        $divBadge = \App\Models\User::getDivisionBadgeStyle($roleLabel);
                                    @endphp
                                    <span style="background-color: {{ $divBadge['bg'] }}; color: {{ $divBadge['text'] }}; border: 1px solid {{ $divBadge['border'] }};"
                                          class="px-1.5 py-0.5 rounded-full text-[8.5px] font-bold tracking-wide shrink-0 leading-none shadow-2xs">
                                        {{ str_replace('_', ' ', $roleLabel) }}
                                    </span>
                                </div>

                                <!-- 3-Dots Vertical Action Button (matching Image 1) -->
                                <div class="relative shrink-0">
                                    <button type="button"
                                            onclick="toggleMessageMenu({{ $chron->id }}, event)"
                                            class="w-6 h-6 rounded-md text-slate-500 hover:text-slate-800 hover:bg-black/5 flex items-center justify-center transition cursor-pointer active:scale-95"
                                            title="Pilihan pesan">
                                        <svg class="w-4 h-4 text-slate-600 hover:text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>

                                    <!-- WhatsApp Dropdown Menu -->
                                    <div id="msg-dropdown-{{ $chron->id }}"
                                         style="bottom: calc(100% + 6px); top: auto; right: 0;"
                                         class="msg-action-dropdown hidden absolute right-0 w-52 sm:w-56 rounded-2xl bg-white/95 backdrop-blur-md shadow-[0_12px_32px_rgba(15,23,42,0.16)] border border-slate-200/80 ring-1 ring-slate-900/5 p-1.5 z-50 select-none divide-y divide-slate-100">
                                        
                                        <div class="space-y-0.5 pb-1">
                                            <!-- 1. Salin -->
                                            <button type="button"
                                                    onclick="copyMessageText({{ $chron->id }})"
                                                    class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                                <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                                </span>
                                                <span>Salin</span>
                                            </button>

                                            <!-- 2. Info Pesan -->
                                            <button type="button"
                                                    onclick="openMessageInfo({{ $chron->id }})"
                                                    class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                                <span class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                                                </span>
                                                <span>Info Pesan</span>
                                            </button>

                                            <!-- 3. Simpan ke Dokumentasi -->
                                            <button type="button"
                                                    onclick="saveToDocumentation({{ $chron->id }})"
                                                    class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                                </span>
                                                <span class="truncate">Simpan Dokumentasi</span>
                                            </button>

                                            @if(!in_array($tiket->status, ['CLOSE', 'CANCELLED'], true))
                                                <!-- 4. Balas -->
                                                <button type="button"
                                                        onclick="replyToMessage({{ $chron->id }})"
                                                        class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                                    <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/></svg>
                                                    </span>
                                                    <span>Balas</span>
                                                </button>

                                                @if($isSelf || auth()->user()?->hasRole('admin'))
                                                    <!-- 5. Edit -->
                                                    <button type="button"
                                                            onclick="editMessage({{ $chron->id }})"
                                                            class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-amber-50/70 hover:text-amber-900 transition-all duration-150 cursor-pointer text-left">
                                                        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        </span>
                                                        <span>Edit Pesan</span>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>

                                        @if(!in_array($tiket->status, ['CLOSE', 'CANCELLED'], true) && ($isSelf || auth()->user()?->hasRole('admin')))
                                            <div class="pt-1">
                                                <!-- 6. Hapus -->
                                                <button type="button"
                                                        onclick="deleteMessage({{ $chron->id }})"
                                                        class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-all duration-150 cursor-pointer text-left">
                                                    <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </span>
                                                    <span>Hapus Pesan</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp Quoted Reply Box -->
                            @if($quotedSender)
                                <div style="background-color: {{ $isSelf ? 'rgba(13, 148, 136, 0.09)' : '#F1F5F9' }}; border-left: 3.5px solid #0D9488; border-radius: 7px;"
                                     class="px-2.5 py-1.5 mb-1 select-none overflow-hidden text-left {{ !empty($chron->reply_to_id) ? 'cursor-pointer hover:brightness-95' : '' }} transition group/quote"
                                     @if(!empty($chron->reply_to_id)) onclick="scrollToChronology({{ $chron->reply_to_id }})" title="Klik untuk menuju pesan asli" @endif>
                                    <div class="font-bold text-[10.5px] leading-tight truncate" style="color: #0F766E;">
                                        {{ $quotedSender }}
                                    </div>
                                    @if($quotedSnippet)
                                        <p class="text-slate-600 text-[10.5px] line-clamp-2 mt-0.5 leading-snug break-words">
                                            {{ html_entity_decode($quotedSnippet, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <!-- Message Content -->
                            @if($cleanMessage)
                                <p id="chron-text-{{ $chron->id }}" class="text-slate-800 leading-snug whitespace-pre-line text-[11.5px] font-normal">{{ $cleanMessage }}</p>
                            @endif

                            <!-- Photo / PDF Attachment Preview if available (Lightbox Modal or PDF Link) -->
                            @if($chron->photo_path)
                                @php
                                    $photoSrc = $chron->photo_url ?? asset('storage/' . $chron->photo_path);
                                    $isPdf = str_ends_with(strtolower($chron->photo_path), '.pdf');
                                @endphp
                                @if($isPdf)
                                    <div class="pt-0.5">
                                        <a href="{{ $photoSrc }}" target="_blank" rel="noopener noreferrer"
                                           style="width: 210px; max-width: 100%; border-radius: 10px; border: 1px solid #E2E8F0;"
                                           class="flex items-center gap-2.5 p-2 bg-rose-50/70 hover:bg-rose-100/80 border-rose-200 text-rose-800 transition rounded-xl group shadow-2xs">
                                            <div class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="truncate flex-1">
                                                <span class="block text-xs font-bold text-slate-800 truncate">{{ basename($chron->photo_path) }}</span>
                                                <span class="block text-[10px] text-rose-600 font-semibold group-hover:underline">Buka PDF &nearr;</span>
                                            </div>
                                        </a>
                                    </div>
                                @else
                                    <div class="pt-0.5">
                                        <button type="button"
                                                onclick="openImageLightbox('{{ $photoSrc }}', 'Foto Dokumentasi - {{ $isSelf ? 'Anda' : ($chron->user->name ?? 'User') }}')"
                                                style="width: 190px; max-width: 100%; border-radius: 10px; overflow: hidden; border: 1px solid #E2E8F0; display: block;"
                                                class="block group relative shadow-2xs bg-slate-100 cursor-pointer text-left">
                                            <img src="{{ $photoSrc }}"
                                                 alt="Bukti Foto"
                                                 loading="lazy"
                                                 style="width: 100%; height: 118px; object-fit: cover; display: block;"
                                                 class="group-hover:scale-105 transition duration-200"
                                                 onerror="if(this.src!=='{{ asset('storage/' . $chron->photo_path) }}'){this.src='{{ asset('storage/' . $chron->photo_path) }}';}">
                                            <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-[11px] gap-1 backdrop-blur-[1px]">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                <span>Perbesar &rarr;</span>
                                            </div>
                                        </button>
                                    </div>
                                @endif
                            @endif

                            <!-- Video Attachment Preview if available -->
                            @if($chron->video_path)
                                <div class="pt-0.5">
                                    <video controls
                                           style="width: 210px; max-width: 100%; height: 128px; border-radius: 10px; border: 1px solid #E2E8F0; display: block;"
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
                                   style="background-color: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);"
                                   class="block p-1.5 rounded-lg hover:border-rose-300 hover:shadow-xs transition-all group mt-0.5">
                                    <div class="flex items-center gap-2">
                                        <div style="background-color: #FEE2E2; border: 1px solid #FECDD3;"
                                             class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-[11px] text-slate-800 group-hover:text-rose-600 transition-colors leading-tight">Lokasi Titik Lapangan</div>
                                            <div class="text-[9.5px] font-mono text-slate-500 mt-0.5 truncate tracking-tight">{{ number_format($chron->latitude, 6) }}, {{ number_format($chron->longitude, 6) }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endif

                            <!-- Timestamp & WhatsApp status indicators -->
                            <div class="flex justify-end items-center gap-1 text-[9.5px] text-slate-400 font-mono pt-0.5 select-none leading-none">
                                <span>{{ $chron->created_at->format('H:i') }} WIB</span>
                                @if($isSelf)
                                    @if($isRead)
                                        <!-- Double Blue Checkmark (Dibaca) -->
                                        <span title="Dibaca" class="status-indicator inline-flex items-center text-[#53bdeb] ml-0.5" aria-label="Dibaca" data-status="read">
                                            <svg class="w-3.5 h-3 text-[#53bdeb]" viewBox="0 0 16 11" fill="none">
                                                <path d="M11 1.5L4.5 8L1.5 5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14.5 1.5L8 8L6.5 6.5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    @else
                                        <!-- Double Grey Checkmark (Terkirim) -->
                                        <span title="Terkirim" class="status-indicator inline-flex items-center text-slate-400 ml-0.5" aria-label="Terkirim" data-status="sent">
                                            <svg class="w-3.5 h-3 text-slate-400" viewBox="0 0 16 11" fill="none">
                                                <path d="M11 1.5L4.5 8L1.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14.5 1.5L8 8L6.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if($isSelf)
                            <!-- Circular Avatar Self / Anda (Right) -->
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden shrink-0 border border-teal-200/80 shadow-2xs flex items-center justify-center bg-gradient-to-br from-teal-500 to-teal-700 text-white select-none mt-0.5"
                                 title="Anda ({{ auth()->user()->name ?? 'User' }})">
                                @if(auth()->user()?->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="Anda" class="w-full h-full object-cover" onerror="this.remove()">
                                @else
                                    <span class="text-[10px] sm:text-[11px] font-bold text-white">{{ auth()->user()?->initials ?? 'A' }}</span>
                                @endif
                            </div>
                        @endif
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

                <!-- Floating "Scroll to Latest Chat" Icon Button (WhatsApp-style circular button at bottom-right) -->
                <button type="button"
                        id="btn-scroll-to-bottom"
                        onclick="scrollToLatestChat()"
                        title="Scroll ke pesan paling baru"
                        style="display: none; position: absolute; bottom: 12px; right: 16px; z-index: 40; width: 32px; height: 32px; background: rgba(255, 255, 255, 0.96); border: 1px solid rgba(13, 148, 136, 0.35); box-shadow: 0 3px 10px rgba(2, 40, 54, 0.16); backdrop-filter: blur(6px);"
                        class="items-center justify-center rounded-full text-[#0D9488] hover:bg-teal-50 hover:text-teal-700 hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer select-none">
                    <span id="new-msg-badge"
                          style="position: absolute; top: -2px; right: -2px;"
                          class="hidden w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white animate-bounce"></span>
                    <svg class="w-4 h-4 text-[#0D9488]" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            @can('chat', $tiket)
                @if(in_array($tiket->status, ['CLOSE', 'CANCELLED'], true))
                <!-- Banner Tiket Selesai / Ditutup untuk Tim Berkepentingan -->
                <div class="px-3.5 py-2 bg-emerald-50/80 border-t border-emerald-200 text-center text-xs text-emerald-800 font-semibold flex items-center justify-center gap-2 select-none shrink-0 shadow-2xs">
                    <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span>Tiket berstatus <strong class="text-emerald-950 font-bold">{{ $tiket->status }}</strong> &bull; Chat koordinasi aktif khusus Admin, Semua Atasan, CEO, NOC &amp; Tim Teknik yang Ditugaskan.</span>
                </div>
                @endif
            <!-- Quick Action Chips (Horizontally Scrollable - matching Image 3) -->
            <div class="chat-quick-templates-bar px-3 sm:px-4 py-1.5 bg-slate-100/90 border-t border-slate-200/80 overflow-x-auto text-xs scrollbar-none shrink-0"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                <div class="w-full flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                    <span class="text-[9.5px] uppercase font-bold text-slate-400 shrink-0 tracking-wide">Template Cepat:</span>
                    
                    <button type="button" onclick="setQuickChip('📍 Menuju ke lokasi gangguan', 'TRACING')"
                            class="px-2.5 py-0.5 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        📍 Menuju lokasi
                    </button>
                    <button type="button" onclick="setQuickChip('🔍 Investigasi titik & pengukuran OTDR', 'OTDR')"
                            class="px-2.5 py-0.5 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        🔍 Investigasi &amp; OTDR
                    </button>
                    <button type="button" onclick="setQuickChip('✂️ Ditemukan kabel putus / bending core pada span', 'INVESTIGASI')"
                            class="px-2.5 py-0.5 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ✂️ Kabel putus / bending
                    </button>
                    <button type="button" onclick="setQuickChip('⚡ Sedang proses splicing core di Joint Closure', 'JOINTING')"
                            class="px-2.5 py-0.5 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ⚡ Proses splicing core
                    </button>
                    <button type="button" onclick="setQuickChip('📊 Pengukuran redaman optik selesai', 'OTDR')"
                            class="px-2.5 py-0.5 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        📊 Ukur redaman optik
                    </button>
                    <button type="button" onclick="setQuickChip('✅ Redaman normal & Link UP kembali', 'LINK_UP')"
                            class="px-2.5 py-0.5 rounded-full bg-white hover:bg-teal-50 hover:text-teal-900 border border-slate-200 text-slate-700 text-[11px] font-medium shrink-0 transition cursor-pointer shadow-2xs">
                        ✅ Redaman normal &amp; Link UP
                    </button>
                </div>
            </div>

            <!-- Chat Input Bar (matching Image 3 & Attachment Menu) -->
            <form id="quick-chat-form" method="POST" action="{{ route('tickets.chronologies.store', $tiket) }}" enctype="multipart/form-data"
                  onsubmit="event.preventDefault(); handleChatFormSubmit(event); return false;"
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
                <div id="chat-reply-preview" class="hidden border-b border-slate-200/80 bg-slate-50/95 px-3 py-2 transition-all">
                    <div class="w-full flex items-center justify-between gap-2 rounded-lg bg-white px-2.5 py-1.5 shadow-2xs" style="border-left: 4px solid #0D9488;">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-[10.5px] sm:text-[11px] leading-tight truncate" style="color: #0F766E;">
                                <span id="reply-to-sender">User</span>
                            </div>
                            <p id="reply-to-text" class="text-slate-600 text-[10.5px] sm:text-[11px] truncate mt-0.5 font-normal leading-snug">Isi pesan...</p>
                        </div>
                        <button type="button" onclick="cancelReply()" class="w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition cursor-pointer text-base font-bold shrink-0" title="Batal balas">&times;</button>
                    </div>
                </div>

                <!-- Attached Photo / Watermark / Document Tray (di atas input bar agar tidak menghalangi input chat) -->
                <div id="photo-attached-preview" class="hidden border-b border-slate-200/80 bg-slate-50/95 px-3 py-2 transition-all">
                    <div class="w-full flex items-center justify-between gap-2.5 rounded-xl bg-white border border-teal-200/80 px-2.5 py-1.5 shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-50 to-sky-100 text-teal-700 border border-teal-200/60 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[9.5px] font-bold uppercase tracking-wider text-teal-600 leading-none">Lampiran Siap Dikirim</div>
                                <span id="photo-preview-text" class="block text-[11.5px] font-semibold text-slate-700 truncate mt-0.5 leading-snug">Foto Terlampir</span>
                            </div>
                        </div>
                        <button type="button" onclick="clearAttachedPhoto()" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer text-sm font-bold shrink-0" title="Hapus Lampiran">&times;</button>
                    </div>
                </div>

                <!-- Attached GPS Location Tray -->
                <div id="gps-attached-preview" class="hidden border-b border-slate-200/80 bg-slate-50/95 px-3 py-2 transition-all">
                    <div class="w-full flex items-center justify-between gap-2.5 rounded-xl bg-white border border-rose-200/80 px-2.5 py-1.5 shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 border border-rose-200/60 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[9.5px] font-bold uppercase tracking-wider text-rose-500 leading-none">Lokasi GPS Terlampir</div>
                                <span id="gps-preview-text" class="block text-[11.5px] font-semibold font-mono text-slate-700 truncate mt-0.5 leading-snug">GPS: -6.934309, 107.594269</span>
                            </div>
                        </div>
                        <button type="button" onclick="clearAttachedGps()" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer text-sm font-bold shrink-0" title="Hapus Lokasi">&times;</button>
                    </div>
                </div>

                <div class="chat-form-inner p-2 sm:px-3.5 sm:py-2.5">
                    <div class="w-full flex items-center gap-2">

                <!-- Hidden Native File Inputs -->
                <!-- 1. General Media (Photo / Video / PDF) -->
                <input type="file" name="photo" id="input_upload_media" accept=".pdf,application/pdf,image/*,video/*" class="hidden" onchange="handleFileChosen(this, 'Dokumen')">
                <!-- 2. Video Capture (HP Video Camera) -->
                <input type="file" name="video" id="input_record_video" accept="video/*" capture="environment" class="hidden" onchange="handleFileChosen(this, 'Video')">
                <!-- 3. Camera GPS (Watermark) trigger -->
                <input type="file" id="input_camera_gps_raw" accept="image/*" capture="environment" class="hidden" onchange="processGpsWatermark(this)">
                <!-- 4. Camera Polos (Direct Camera Capture) -->
                <input type="file" id="input_camera_plain" accept="image/*" capture="environment" class="hidden" onchange="handleFileChosen(this, 'Kamera')">

                <!-- Text & Location Input Box Container with Integrated Attachment Clip (Nyambung Bagian Putihnya) -->
                <div id="chat-input-container"
                     class="flex-1 rounded-full border border-slate-300/90 bg-white pl-2 pr-4 py-1.5 sm:pl-1.5 sm:py-1 transition-all flex flex-col justify-center min-h-[48px] sm:min-h-[40px] shadow-[0_3px_12px_rgba(0,0,0,0.09)] min-w-0">
                    <div class="relative flex items-center min-w-0 w-full gap-1">
                        <!-- Attachment Clip Button inside the white pill -->
                        <div class="relative shrink-0">
                            <button type="button"
                                    id="btn-attachment-toggle"
                                    onclick="toggleAttachmentMenu(event)"
                                    title="Lampirkan Dokumen / Media / Kamera / Lokasi"
                                    class="w-10 h-10 sm:w-8 sm:h-8 rounded-full bg-transparent hover:bg-slate-100 text-slate-500 hover:text-[#0D9488] flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0">
                                <svg class="w-5 h-5 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </button>

                            <!-- WhatsApp-Style Dropup Attachment Menu (Robust Native JS & Clean Elevating Z-Index) -->
                            <div id="dropdown-attachment-menu"
                                 style="bottom: 100% !important; margin-bottom: 10px !important; top: auto !important;"
                                 class="hidden absolute left-0 w-72 sm:w-80 rounded-2xl bg-white shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-slate-200/90 py-2 px-1.5 z-[100] select-none divide-y divide-slate-100">
                                
                                <div class="space-y-0.5 pb-1">
                                    <!-- Option 1: Upload Media (Foto / Video) -->
                                    <button type="button"
                                            onclick="closeAttachmentMenu(); document.getElementById('input_upload_media').click();"
                                            class="w-full flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-xs text-slate-800 leading-tight">Upload File (Foto / Video / PDF)</h4>
                                            <p class="text-[10px] text-slate-500 truncate mt-0.5">Pilih foto, video, atau dokumen PDF dari perangkat</p>
                                        </div>
                                    </button>

                                    <!-- Option 2: Rekam Video Lapangan -->
                                    <button type="button"
                                            onclick="closeAttachmentMenu(); document.getElementById('input_record_video').click();"
                                            class="w-full flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-xs text-slate-800 leading-tight">Rekam Video Lapangan</h4>
                                            <p class="text-[10px] text-slate-500 truncate mt-0.5">Rekam langsung kamera HP (Video Ringan &amp; Cepat)</p>
                                        </div>
                                    </button>

                                    <!-- Option 3: Kamera GPS (Watermark) -->
                                    <button type="button"
                                            onclick="closeAttachmentMenu(); captureCameraWithGps();"
                                            class="w-full flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-xs text-slate-800 leading-tight flex items-center gap-1.5">
                                                <span>Kamera GPS (Watermark)</span>
                                                <span class="px-1.5 py-0.2 rounded-full text-[8.5px] font-bold bg-emerald-100 text-emerald-700">MSN GPS</span>
                                            </h4>
                                            <p class="text-[10px] text-slate-500 truncate mt-0.5">Dengan Logo MSN, Timestamp &amp; Alamat</p>
                                        </div>
                                    </button>

                                    <!-- Option 4: Kamera Polos (Tanpa Watermark) -->
                                    <button type="button"
                                            onclick="closeAttachmentMenu(); document.getElementById('input_camera_plain').click();"
                                            class="w-full flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 transition text-left cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-xs text-slate-800 leading-tight">Kamera Polos (Tanpa Watermark)</h4>
                                            <p class="text-[10px] text-slate-500 truncate mt-0.5">Potret langsung foto kamera original</p>
                                        </div>
                                    </button>
                                </div>

                                <!-- Option 5: Share Lokasi Saya -->
                                <div class="pt-1">
                                    <button type="button"
                                            onclick="closeAttachmentMenu(); shareMyCurrentLocation();"
                                            class="w-full flex items-center gap-3 p-1.5 rounded-xl hover:bg-rose-50 transition text-left cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 border border-rose-200 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-bold text-xs text-slate-800 leading-tight">Share Lokasi Saya</h4>
                                            <p class="text-[10px] text-slate-500 truncate mt-0.5">Sematkan koordinat GPS lapangan</p>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <input type="text"
                               name="message"
                               id="quick-chat-input"
                               placeholder="Ketik update koordinasi..."
                               autocomplete="off"
                               onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();handleChatFormSubmit(event);return false;}"
                               style="outline: none !important; box-shadow: none !important; border: none !important;"
                               class="flex-1 text-[15px] sm:text-xs bg-transparent border-0 p-0 text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none focus-visible:outline-none focus-visible:ring-0 min-w-0 pr-1">
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

                    @keyframes messagePopIn {
                        0% {
                            opacity: 0;
                            transform: translateY(12px) scale(0.96);
                        }
                        60% {
                            transform: translateY(-2px) scale(1.01);
                        }
                        100% {
                            opacity: 1;
                            transform: translateY(0) scale(1);
                        }
                    }

                    .animate-message-in {
                        animation: messagePopIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                        transform-origin: bottom right;
                    }

                    .animate-message-in-left {
                        animation: messagePopIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                        transform-origin: bottom left;
                    }

                    @keyframes newChatBorderGlowSelf {
                        0% {
                            outline: 3px solid #2563EB;
                            outline-offset: 1px;
                            box-shadow: 0 0 16px rgba(37, 99, 235, 0.45);
                        }
                        60% {
                            outline: 2.5px solid #3B82F6;
                            outline-offset: 1px;
                            box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
                        }
                        100% {
                            outline: 0px solid transparent;
                            outline-offset: 0px;
                            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
                        }
                    }

                    @keyframes newChatBorderGlowIncoming {
                        0% {
                            outline: 3px solid #0D9488;
                            outline-offset: 1px;
                            box-shadow: 0 0 16px rgba(13, 148, 136, 0.45);
                        }
                        60% {
                            outline: 2.5px solid #14B8A6;
                            outline-offset: 1px;
                            box-shadow: 0 0 10px rgba(20, 184, 166, 0.3);
                        }
                        100% {
                            outline: 0px solid transparent;
                            outline-offset: 0px;
                            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
                        }
                    }

                    .new-chat-highlight-self {
                        animation: newChatBorderGlowSelf 3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards !important;
                    }

                    .new-chat-highlight-incoming {
                        animation: newChatBorderGlowIncoming 3s cubic-bezier(0.2, 0.8, 0.2, 1) forwards !important;
                    }
                </style>

                <!-- Voice-to-Text Microphone Button -->
                <button type="button"
                        id="btn-voice-to-text"
                        onclick="toggleVoiceRecognition()"
                        title="Bicara untuk Mengetik (Voice-to-Text)"
                        class="w-12 h-12 sm:w-10 sm:h-10 rounded-full bg-white border border-slate-200/95 shadow-[0_3px_12px_rgba(0,0,0,0.09)] hover:bg-teal-50 text-slate-600 hover:text-[#0D9488] flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 relative">
                    <!-- Mic Normal Icon -->
                    <svg id="mic-icon-normal" class="w-5 h-5 sm:w-4.5 sm:h-4.5 text-slate-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <!-- Mic Active (Recording Pulse) -->
                    <span id="mic-pulse-indicator" class="hidden absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                    <span id="mic-dot-indicator" class="hidden absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                </button>

                <!-- Submit Send Button -->
                <button type="button"
                        id="btn-quick-chat-submit"
                        onclick="handleChatFormSubmit(event)"
                        class="w-12 h-12 sm:w-10 sm:h-10 rounded-full text-white flex items-center justify-center shadow-[0_3px_12px_rgba(13,148,136,0.42)] hover:scale-105 active:scale-95 transition cursor-pointer shrink-0"
                        style="background: var(--gomsn-active-bg) !important;">
                    <svg class="w-5 h-5 sm:w-4.5 sm:h-4.5 text-white transform rotate-45 -mr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
                    </div>
                </div>
            </form>
            @else
            <!-- Read-Only Banner for Non-Assigned / Non-Authorized Users -->
            <div class="p-3.5 bg-slate-50/90 border-t border-slate-200 text-center text-xs text-slate-500 font-medium flex items-center justify-center gap-2 select-none shrink-0">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Mode Hanya Baca: Anda tidak memiliki hak akses mengirim koordinasi pada tiket ini (Khusus Admin, Semua Atasan, CEO, NOC, dan Teknisi yang ditugaskan).</span>
            </div>
            @endcan
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

                <button type="button" onclick="openResumeModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white shadow-md shadow-teal-950/20 cursor-pointer transition"
                        style="background: var(--gomsn-active-bg) !important;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>{{ $hasResume ? 'Edit Resume Pekerjaan' : 'Input Resume Baru' }}</span>
                </button>
            </div>

            <!-- Resume Content / Display -->
            @if($hasResume)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Team OM / Daftar Nama Teknis Lapangan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 md:col-span-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Team OM / Daftar Nama Teknis Lapangan</span>
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                                $displayMembers = (!empty($tiket->team_members) && is_array($tiket->team_members) && count($tiket->team_members) > 0)
                                    ? $tiket->team_members
                                    : ($tiket->assignees->count() > 0 ? $tiket->assignees->pluck('name')->values()->all() : ['Ricky']);
                            @endphp
                            @foreach($displayMembers as $m)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-800 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>{{ $m }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Problem / Temuan Masalah -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Problem / Temuan Masalah di Lapangan</span>
                        <p class="text-xs sm:text-sm text-slate-800 font-semibold leading-relaxed">{{ $tiket->problem_temuan }}</p>
                    </div>

                    <!-- Action / Tindakan Perbaikan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Action / Tindakan Perbaikan yang Dilakukan</span>
                        <p class="text-xs sm:text-sm text-slate-800 font-semibold leading-relaxed">{{ $tiket->action_taken }}</p>
                    </div>

                    <!-- Tipe Penanganan Fisik / Core -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Tipe Penanganan Fisik / Core</span>
                        <div class="pt-0.5">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#0D9488] text-white shadow-2xs inline-block">
                                Jointing Lurus &amp; Manuver Core
                            </span>
                        </div>
                    </div>

                    <!-- Status Pemulihan Link -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Status Pemulihan Link</span>
                        <p class="text-xs sm:text-sm font-bold {{ $tiket->has_link_up ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $tiket->has_link_up ? '✓ LINK UP RESTORED' : 'Belum Restore' }}
                        </p>
                    </div>

                    @if($tiket->catatan_tambahan)
                    <!-- Catatan Tambahan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1 md:col-span-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Catatan Tambahan</span>
                        <p class="text-xs sm:text-sm text-slate-800 leading-relaxed">{{ $tiket->catatan_tambahan }}</p>
                    </div>
                    @endif

                    @if($tiket->redaman_sebelum !== null || $tiket->redaman_sesudah !== null)
                    <!-- Pengukuran Redaman Optik -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1 md:col-span-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Pengukuran Redaman Optik</span>
                        <p class="text-xs sm:text-sm font-mono font-bold text-slate-800">
                            Sebelum: <span class="text-rose-600">{{ $tiket->redaman_sebelum !== null ? $tiket->redaman_sebelum . ' dBm' : '-' }}</span> &bull; 
                            Sesudah: <span class="text-emerald-600">{{ $tiket->redaman_sesudah !== null ? $tiket->redaman_sesudah . ' dBm' : '-' }}</span>
                        </p>
                    </div>
                    @elseif($isBroadband)
                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-1 md:col-span-2">
                        <span class="text-[10px] uppercase font-bold text-amber-600">Pengukuran Redaman Optik</span>
                        <p class="text-xs sm:text-sm text-amber-850 font-semibold">
                            ⚠️ Nilai redaman sesudah belum dicatat. Silakan klik <button type="button" onclick="openResumeModal()" class="text-teal-700 underline font-bold cursor-pointer">Edit Resume Pekerjaan</button> untuk mengisi redaman sesudah agar dapat melakukan Closing Awal.
                        </p>
                    </div>
                    @endif
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
                    <button type="button" onclick="openResumeModal()"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-md shadow-teal-950/20 cursor-pointer transition inline-flex items-center gap-2"
                            style="background: var(--gomsn-active-bg) !important;">
                        <span>Input Resume Sekarang</span>
                    </button>
                </div>
            @endif
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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($tiket->jointClosures as $jc)
                            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs text-xs space-y-3">
                                <!-- Top Bar: Name, Badges, & Delete -->
                                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-slate-800 text-sm sm:text-base tracking-tight truncate">{{ $jc->closure_code }}</span>
                                            <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 font-bold text-[10px] font-mono border border-teal-200">
                                                {{ $jc->tipe_closure ?: ($jc->closure_type ?: 'DOME') }}
                                            </span>
                                            @if($jc->repairPoint?->jenis_kerusakan)
                                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10px] border border-slate-200">
                                                    {{ $jc->repairPoint->jenis_kerusakan }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('tickets.joint-closures.destroy', $jc) }}" onsubmit="return confirm('Hapus Joint Closure {{ $jc->closure_code }} beserta seluruh data core-nya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition cursor-pointer shrink-0" title="Hapus Joint Closure">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Spec & Location Information -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Kapasitas Closure:</span>
                                        <span class="font-bold text-slate-800 font-mono">{{ $jc->capacity_cores }} Core</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Titik Lokasi:</span>
                                        <span class="font-semibold text-slate-800 truncate block">{{ $jc->location_description ?: ($jc->repairPoint?->nama_titik ?: 'Lokasi Tiang/Span') }}</span>
                                    </div>
                                    @if($jc->repairPoint?->latitude && $jc->repairPoint?->longitude)
                                        <div class="sm:col-span-2 flex items-center justify-between pt-1 border-t border-slate-200/60 text-[10.5px]">
                                            <div class="flex items-center gap-1.5 font-mono text-slate-600 truncate">
                                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                                <span>{{ $jc->repairPoint->latitude }}, {{ $jc->repairPoint->longitude }}</span>
                                            </div>
                                            <a href="https://maps.google.com/?q={{ $jc->repairPoint->latitude }},{{ $jc->repairPoint->longitude }}" target="_blank" class="text-teal-600 font-bold hover:underline shrink-0 flex items-center gap-0.5">
                                                <span>Buka Maps</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Connected Cores Matrix Section -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-[11px] uppercase tracking-wider text-teal-800 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>Sambungan Core Splicing ({{ $jc->cores->count() }} Sambungan)</span>
                                        </span>
                                    </div>

                                    @if($jc->cores->isNotEmpty())
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-56 overflow-y-auto pr-1 custom-scrollbar">
                                            @foreach($jc->cores as $core)
                                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px]">
                                                    <div class="flex items-center gap-1.5 font-mono min-w-0 pr-1">
                                                        <span class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></span>
                                                        <span class="font-bold text-slate-800 truncate">{{ $core->tube_asal }}:C{{ $core->core_asal }}</span>
                                                        <span class="text-teal-600 font-bold shrink-0">&rarr;</span>
                                                        <span class="font-bold text-slate-800 truncate">{{ $core->tube_tujuan }}:C{{ $core->core_tujuan }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0">
                                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold {{ $core->status_core === 'TERHUBUNG' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($core->status_core === 'CADANGAN / SPARE' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                                            {{ $core->status_core }}
                                                        </span>
                                                        <span class="font-mono text-slate-500 text-[10px] font-bold">{{ $core->loss_db }} dB</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 rounded-xl border border-dashed border-slate-200 text-center text-slate-400 text-[11px]">
                                            Belum ada detail sambungan core yang tercatat pada closure ini.
                                        </div>
                                    @endif
                                </div>
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

                <form method="POST" action="{{ route('tickets.repair-points.store', $tiket) }}" 
                      x-data="{ 
                          lat: '', 
                          lng: '', 
                          isGpsLoading: false, 
                          getGps() {
                              this.isGpsLoading = true;
                              const applyCoords = (la, ln) => {
                                  this.lat = Number(la).toFixed(6);
                                  this.lng = Number(ln).toFixed(6);
                                  this.isGpsLoading = false;
                              };
                              if (!navigator.geolocation) {
                                  fetch('https://ipwho.is/').then(r=>r.json()).then(d=>{ if(d.latitude) applyCoords(d.latitude, d.longitude); else this.isGpsLoading=false; }).catch(()=>{ this.isGpsLoading = false; });
                                  return;
                              }
                              navigator.geolocation.getCurrentPosition(
                                  (pos) => applyCoords(pos.coords.latitude, pos.coords.longitude),
                                  () => {
                                      navigator.geolocation.getCurrentPosition(
                                          (fb) => applyCoords(fb.coords.latitude, fb.coords.longitude),
                                          () => {
                                              fetch('https://ipwho.is/').then(r=>r.json()).then(d=>{ if(d.latitude) applyCoords(d.latitude, d.longitude); else this.isGpsLoading=false; }).catch(()=>{ this.isGpsLoading=false; });
                                          },
                                          { enableHighAccuracy: false, timeout: 8000 }
                                      );
                                  },
                                  { enableHighAccuracy: true, timeout: 5000 }
                              );
                          }
                      }"
                      class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider text-[10px]">Deskripsi Titik Tiang / Span *</label>
                        <input type="text" name="nama_titik" required placeholder="Contoh: Tiang PT MSN No. 12 Depan RSUD" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-bold text-slate-700 uppercase tracking-wider text-[10px]">Latitude *</label>
                                <button type="button" @click="getGps()" :disabled="isGpsLoading" class="text-[10px] font-bold text-teal-600 hover:text-teal-700 inline-flex items-center gap-1 cursor-pointer">
                                    <span x-text="isGpsLoading ? 'Mendeteksi...' : 'Ambil GPS'"></span>
                                </button>
                            </div>
                            <input type="number" step="any" name="latitude" x-model="lat" required placeholder="-6.917464" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-bold text-slate-700 uppercase tracking-wider text-[10px]">Longitude *</label>
                            </div>
                            <input type="number" step="any" name="longitude" x-model="lng" required placeholder="107.619122" class="w-full text-xs rounded-xl border border-slate-300 p-2.5">
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
                                <div class="min-w-0 pr-2">
                                    <span class="font-bold text-slate-800 block truncate">{{ $rp->nama_titik ?: ($rp->location_name ?: 'Titik Perbaikan') }}</span>
                                    @if($rp->jenis_kerusakan)
                                        <span class="text-[10px] text-teal-700 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded font-semibold inline-block my-0.5">{{ $rp->jenis_kerusakan }}</span>
                                    @endif
                                    <p class="text-[11px] font-mono text-slate-400">{{ $rp->latitude }}, {{ $rp->longitude }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="https://maps.google.com/?q={{ $rp->latitude }},{{ $rp->longitude }}" target="_blank" class="text-teal-600 font-bold hover:underline">Maps &rarr;</a>
                                    <form method="POST" action="{{ route('tickets.repair-points.destroy', $rp) }}" onsubmit="return confirm('Hapus titik perbaikan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-6 h-6 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition cursor-pointer" title="Hapus titik">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
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
                        @php
                            $isPdf = str_ends_with(strtolower($p->photo_path), '.pdf');
                            $fileUrl = $p->photo_url ?? asset('storage/' . $p->photo_path);
                        @endphp
                        @if($isPdf)
                            <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-4 flex flex-col justify-between hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="font-bold text-xs text-slate-800 block truncate" title="{{ basename($p->photo_path) }}">{{ basename($p->photo_path) }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $p->created_at->format('d M, H:i') }} &bull; {{ $p->user->name ?? 'User' }}</span>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-rose-100 flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Dokumen PDF</span>
                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition">
                                        <span>Buka</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 group relative">
                                <img src="{{ $fileUrl }}"
                                     alt="Foto"
                                     loading="lazy"
                                     class="w-full h-44 object-cover group-hover:scale-105 transition duration-300"
                                     onerror="if(this.src!=='{{ asset('storage/' . $p->photo_path) }}'){this.src='{{ asset('storage/' . $p->photo_path) }}';}">
                                <div class="p-2.5 bg-white border-t border-slate-100">
                                    <span class="font-bold text-[11px] text-slate-800 block truncate">{{ $p->user->name ?? 'User' }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $p->created_at->format('d M, H:i') }}</span>
                                </div>
                                <button type="button"
                                        onclick="openImageLightbox('{{ $fileUrl }}', 'Foto Dokumentasi - {{ $p->user->name ?? 'User' }}')"
                                        class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-xs gap-1.5 cursor-pointer backdrop-blur-[1px]">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    <span>Perbesar &rarr;</span>
                                </button>
                            </div>
                        @endif
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
    <div class="bg-white rounded-3xl max-w-4xl lg:max-w-5xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200 my-auto">
        <!-- Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 text-white flex items-center justify-between"
             style="background: var(--gomsn-header-bg) !important;">
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

        <form method="POST" action="{{ route('tickets.dispatch', $tiket) }}" class="p-5 sm:p-6 space-y-5 text-xs">
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

            <!-- 2 Kolom Horizontal (Kiri: Leader PIC, Kanan: Crew Pendukung) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 items-start">
                
                <!-- KOLOM 1: LEADER TEKNISI (PIC UTAMA) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                            <div>
                                <label class="block font-heading font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">
                                    Leader Teknisi (PIC Utama) <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[10px] text-slate-400">Penanggung jawab utama lapangan</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                              :class="selectedLead ? 'bg-teal-50 text-[#0D9488] border border-teal-200' : 'bg-rose-50 text-rose-600 border border-rose-200'"
                              x-text="selectedLead ? 'PIC Terpilih' : 'Wajib dipilih'"></span>
                    </div>

                    <!-- Selected PIC Card Preview -->
                    <div x-show="selectedLead" 
                         class="bg-gradient-to-r from-teal-50/80 via-emerald-50/60 to-white rounded-2xl p-3 border-2 border-teal-500/80 shadow-2xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0D9488] to-[#042f2e] text-white font-extrabold flex items-center justify-center text-xs shrink-0 shadow-xs">
                                <span x-text="selectedLead ? selectedLead.initials : 'PIC'"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-heading font-extrabold text-xs text-slate-900 truncate" x-text="selectedLead ? selectedLead.name : ''"></span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#0D9488] text-white">★ PIC Utama</span>
                                </div>
                                <p class="text-[10px] text-slate-500 truncate mt-0.5" x-text="selectedLead ? (selectedLead.position || 'Divisi Teknik') : ''"></p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="leadId = null"
                                class="px-2.5 py-1 rounded-xl bg-white border border-slate-200 text-rose-600 hover:bg-rose-50 text-[10px] font-bold transition shrink-0 shadow-2xs">
                            Lepas PIC
                        </button>
                    </div>

                    <div x-show="!selectedLead" class="p-2.5 bg-slate-50/90 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-[11px] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Klik salah satu nama di bawah untuk menetapkan PIC.</span>
                    </div>

                    <!-- Search Input Leader -->
                    <div class="relative">
                        <input type="text" 
                               x-model="searchLead" 
                               placeholder="🔍 Cari nama teknisi untuk PIC..."
                               class="w-full text-xs rounded-xl border border-slate-300 pl-9 pr-8 py-2 focus:border-[#0D9488] focus:ring-1 focus:ring-[#0D9488] transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <button type="button" 
                                x-show="searchLead" 
                                @click="searchLead = ''" 
                                class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-sm">&times;</button>
                    </div>

                    <!-- Lead Candidates List -->
                    <div class="max-h-56 sm:max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xs p-1 divide-y divide-slate-100">
                        <template x-for="tech in filteredLeads" :key="'lead_'+tech.id">
                            <button type="button" 
                                    @click="selectLead(tech.id)"
                                    class="w-full flex items-center justify-between p-2 rounded-xl text-left transition group"
                                    :class="leadId == tech.id ? 'bg-teal-50/90 font-bold border border-teal-200' : 'hover:bg-teal-50/60'">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold shrink-0 transition"
                                         :class="leadId == tech.id ? 'bg-[#0D9488] text-white' : 'bg-teal-100 text-[#0D9488]'">
                                        <span x-text="tech.initials"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 truncate group-hover:text-teal-900" x-text="tech.name"></p>
                                        <p class="text-[10px] text-slate-400 truncate" x-text="tech.position || 'Teknisi Lapangan'"></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 transition"
                                      :class="leadId == tech.id ? 'bg-[#0D9488] text-white' : 'text-[#0D9488] bg-teal-50 opacity-0 group-hover:opacity-100'">
                                    <span x-text="leadId == tech.id ? '✓ Terpilih' : 'Pilih PIC'"></span>
                                </span>
                            </button>
                        </template>
                        <div x-show="filteredLeads.length === 0" class="py-4 text-center text-slate-400 text-xs">
                            Tidak ditemukan karyawan teknik yang sesuai.
                        </div>
                    </div>
                </div>

                <!-- KOLOM 2: ANGGOTA TIM PENDUKUNG (CREW LAPANGAN) -->
                <div class="space-y-3 md:border-l md:border-slate-100 md:pl-6">
                    <div class="flex items-center justify-between pb-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <div>
                                <label class="block font-heading font-extrabold text-slate-800 uppercase tracking-wider text-[11px]">
                                    Anggota Pendukung (Crew)
                                </label>
                                <p class="text-[10px] text-slate-400">Pilih satu atau beberapa crew lapangan</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-100 shrink-0"
                              x-text="memberIds.length + ' Crew Terpilih'"></span>
                    </div>

                    <!-- Selected Crew Chips -->
                    <div class="p-2 bg-slate-50/90 rounded-2xl border border-slate-200/80 min-h-[46px] max-h-24 overflow-y-auto flex flex-wrap gap-1.5 items-center">
                        <template x-for="mid in memberIds" :key="'chip_'+mid">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white text-slate-800 border border-slate-200 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                <span x-text="getTechName(mid)"></span>
                                <button type="button" 
                                        @click="removeMember(mid)" 
                                        class="text-slate-400 hover:text-rose-600 font-bold ml-0.5 text-xs transition">&times;</button>
                            </span>
                        </template>
                        <div x-show="memberIds.length === 0" class="text-[11px] text-slate-400 italic px-1">
                            Belum ada crew pendukung yang dipilih.
                        </div>
                    </div>

                    <!-- Search Member Input -->
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
                    <div class="max-h-56 sm:max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-1 divide-y divide-slate-100 shadow-xs">
                        <template x-for="tech in filteredMembers" :key="'mem_'+tech.id">
                            <div @click="toggleMember(tech.id)"
                                 class="flex items-center justify-between p-2 rounded-xl cursor-pointer transition select-none group"
                                 :class="isMember(tech.id) ? 'bg-teal-50/80 hover:bg-teal-50 font-semibold' : 'hover:bg-slate-50'">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-4 h-4 rounded-md border flex items-center justify-center transition shrink-0"
                                         :class="isMember(tech.id) ? 'bg-[#0D9488] border-[#0D9488] text-white' : 'border-slate-300 bg-white group-hover:border-[#0D9488]'">
                                        <svg x-show="isMember(tech.id)" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-800 truncate" :class="{ 'text-teal-950 font-bold': isMember(tech.id) }" x-text="tech.name"></p>
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
            </div>

            <!-- Footer Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <div class="text-[11px] text-slate-500 self-start sm:self-auto">
                    <span x-show="leadId" class="font-medium">
                        Total Formasi: <strong class="text-slate-800" x-text="1 + memberIds.length"></strong> orang (1 Leader + <span x-text="memberIds.length"></span> Crew)
                    </span>
                    <span x-show="!leadId" class="text-rose-500 font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Wajib memilih 1 Leader PIC Utama
                    </span>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" 
                            onclick="closeDispatchModal()" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="!leadId"
                            class="px-5 py-2.5 rounded-xl text-white font-bold transition flex items-center gap-1.5 shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            style="background: var(--gomsn-active-bg) !important;">
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
        memberIds: {!! json_encode($tiket->assignees->where('is_lead', false)->pluck('user_id')->filter()->values()) !!},
        searchLead: '',
        searchMember: '',

        init() {
            this.memberIds = (this.memberIds || [])
                .filter(id => id !== null && id !== undefined && id !== '' && id !== 'null')
                .map(id => Number(id));
            if (this.leadId === 'null' || !this.leadId) {
                this.leadId = null;
            } else {
                this.leadId = Number(this.leadId);
            }
        },

        get selectedLead() {
            if (!this.leadId) return null;
            return this.technicians.find(t => Number(t.id) === Number(this.leadId)) || null;
        },

        get filteredLeads() {
            if (!this.searchLead) return this.technicians;
            const q = this.searchLead.toLowerCase();
            return this.technicians.filter(t => t.name.toLowerCase().includes(q) || (t.position && t.position.toLowerCase().includes(q)));
        },

        get filteredMembers() {
            // Exclude chosen lead
            const currentLead = this.leadId ? Number(this.leadId) : null;
            const list = this.technicians.filter(t => !currentLead || Number(t.id) !== currentLead);
            if (!this.searchMember) return list;
            const q = this.searchMember.toLowerCase();
            return list.filter(t => t.name.toLowerCase().includes(q) || (t.position && t.position.toLowerCase().includes(q)));
        },

        selectLead(id) {
            id = Number(id);
            this.leadId = id;
            this.searchLead = '';
            // If lead was chosen as crew, remove from crew
            this.memberIds = this.memberIds.filter(m => Number(m) !== id);
        },

        toggleMember(id) {
            id = Number(id);
            if (!id) return;
            if (this.memberIds.map(Number).includes(id)) {
                this.memberIds = this.memberIds.filter(m => Number(m) !== id);
            } else {
                this.memberIds.push(id);
            }
        },

        removeMember(id) {
            id = Number(id);
            this.memberIds = this.memberIds.filter(m => Number(m) !== id);
        },

        isMember(id) {
            return this.memberIds.map(Number).includes(Number(id));
        },

        getTechName(id) {
            if (!id || id === 'null') return '';
            const found = this.technicians.find(t => Number(t.id) === Number(id));
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
             style="background: var(--gomsn-header-bg) !important;">
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

<script>
function jointClosureModal() {
    return {
        namaClosure: '',
        jenisClosure: 'DOME',
        penempatanFisik: 'POLE',
        lat: '',
        lng: '',
        isGpsLoading: false,
        gpsStatus: '',
        gpsError: '',
        gpsAddress: '',
        gpsAccuracy: null,
        isNewCut: false,
        kapasitasAsal: 24,
        jumlahTubeAsal: 2,
        kapasitasJumper: 24,
        jumlahTubeJumper: 2,
        activeTubeAsal: 1,
        activeTubeJumper: 1,
        colors: [
            { num: 1, name: 'Biru', hex: '#3b82f6' },
            { num: 2, name: 'Oranye', hex: '#f97316' },
            { num: 3, name: 'Hijau', hex: '#22c55e' },
            { num: 4, name: 'Cokelat', hex: '#a16207' },
            { num: 5, name: 'Abu-abu', hex: '#94a3b8' },
            { num: 6, name: 'Putih', hex: '#f8fafc', isWhite: true },
            { num: 7, name: 'Merah', hex: '#ef4444' },
            { num: 8, name: 'Hitam', hex: '#0f172a', isBlack: true },
            { num: 9, name: 'Kuning', hex: '#eab308' },
            { num: 10, name: 'Ungu', hex: '#a855f7' },
            { num: 11, name: 'Pink', hex: '#ec4899' },
            { num: 12, name: 'Toska', hex: '#06b6d4' }
        ],
        matrix: [
            {
                tube_asal: 'Tube 1',
                core_asal: 1,
                core_asal_label: 'Core 1 (Biru)',
                tube_jumper: 'Tube 1',
                core_jumper: 1,
                core_jumper_label: 'Core 1 (Biru)',
                status: 'TERHUBUNG',
                loss: 0.02
            }
        ],

        init() {
            window.addEventListener('jc-modal-opened', () => {
                if (!this.lat || !this.lng) {
                    this.getGps(false, false);
                }
            });
        },

        getGps(openMapAfter = false, showAlertOnError = true) {
            this.isGpsLoading = true;
            this.gpsStatus = 'Mendeteksi koordinat GPS...';
            this.gpsError = '';

            const applyCoords = (pos, source) => {
                const latNum = pos.coords.latitude;
                const lngNum = pos.coords.longitude;
                const acc = Math.round(pos.coords.accuracy || 10);
                this.lat = latNum.toFixed(6);
                this.lng = lngNum.toFixed(6);
                this.gpsAccuracy = acc;
                this.isGpsLoading = false;
                this.gpsStatus = `✓ Terdeteksi via ${source} (Akurasi ~${acc}m)`;
                this.fetchAddress(latNum, lngNum);
                if (openMapAfter) {
                    window.open(`https://www.google.com/maps?q=${this.lat},${this.lng}`, '_blank');
                }
            };

            if (!navigator.geolocation) {
                // Fallback langsung ke Jaringan IP jika browser tidak mendukung geolocation / konteks HTTP
                this.fetchFromIpFallback(openMapAfter, showAlertOnError);
                return;
            }

            // 1. Coba High Accuracy (GPS Satelit)
            navigator.geolocation.getCurrentPosition(
                (pos) => applyCoords(pos, 'GPS'),
                (err) => {
                    console.warn('GPS satelit tidak terkunci/timeout, mencoba via jaringan/Wi-Fi...', err);
                    // 2. Fallback ke Network / Wi-Fi
                    navigator.geolocation.getCurrentPosition(
                        (fallbackPos) => applyCoords(fallbackPos, 'Jaringan/Wi-Fi'),
                        (fallbackErr) => {
                            console.warn('Network location gagal, fallback ke IP Geolocation...', fallbackErr);
                            // 3. Fallback ke IP Geolocation (pasti dapat lokasi)
                            this.fetchFromIpFallback(openMapAfter, showAlertOnError);
                        },
                        { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
                    );
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 }
            );
        },

        async fetchFromIpFallback(openMapAfter = false, showAlertOnError = true) {
            this.gpsStatus = 'Mendeteksi via Jaringan IP...';
            try {
                const res = await fetch('https://ipwho.is/');
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.success && data.latitude && data.longitude) {
                        this.lat = Number(data.latitude).toFixed(6);
                        this.lng = Number(data.longitude).toFixed(6);
                        this.gpsAccuracy = 1000;
                        this.isGpsLoading = false;
                        this.gpsStatus = '✓ Terdeteksi via Jaringan IP (' + (data.city || 'Regional') + ')';
                        this.gpsAddress = [data.city, data.region, data.country].filter(Boolean).join(', ');
                        if (openMapAfter) {
                            window.open(`https://www.google.com/maps?q=${this.lat},${this.lng}`, '_blank');
                        }
                        return;
                    }
                }
            } catch (e) {
                console.warn('IP fallback ipwho.is failed:', e);
            }

            try {
                const res2 = await fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?localityLanguage=id');
                if (res2.ok) {
                    const d = await res2.json();
                    if (d && d.latitude && d.longitude) {
                        this.lat = Number(d.latitude).toFixed(6);
                        this.lng = Number(d.longitude).toFixed(6);
                        this.gpsAccuracy = 1500;
                        this.isGpsLoading = false;
                        this.gpsStatus = '✓ Terdeteksi via Jaringan IP (' + (d.city || d.locality || 'Regional') + ')';
                        this.gpsAddress = [d.locality, d.city, d.principalSubdivision].filter(Boolean).join(', ');
                        if (openMapAfter) {
                            window.open(`https://www.google.com/maps?q=${this.lat},${this.lng}`, '_blank');
                        }
                        return;
                    }
                }
            } catch (e2) {
                console.warn('IP fallback BigDataCloud failed:', e2);
            }

            this.isGpsLoading = false;
            this.gpsError = 'Gagal mendeteksi lokasi otomatis. Silakan periksa izin lokasi browser atau masukkan koordinat secara manual.';
            this.gpsStatus = '';
            if (showAlertOnError) alert(this.gpsError);
        },

        async fetchAddress(lat, lng) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.display_name) {
                        this.gpsAddress = data.display_name;
                        return;
                    }
                }
            } catch (e) {}

            try {
                const res2 = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=id`);
                if (res2.ok) {
                    const d = await res2.json();
                    const parts = [d.locality, d.city, d.principalSubdivision].filter(Boolean);
                    if (parts.length) {
                        this.gpsAddress = parts.join(', ');
                    }
                }
            } catch (e) {}
        },

        openMap() {
            if (this.lat && this.lng) {
                window.open(`https://www.google.com/maps?q=${this.lat},${this.lng}`, '_blank');
            } else {
                this.getGps(true, true);
            }
        },

        getColor(num) {
            return this.colors.find(c => c.num === Number(num)) || { num: num, name: 'Core ' + num, hex: '#94a3b8' };
        },

        isConnected(tubeNum, coreNum, isJumper = false) {
            const tubeStr = 'Tube ' + tubeNum;
            if (!isJumper) {
                return this.matrix.some(m => m.tube_asal === tubeStr && Number(m.core_asal) === Number(coreNum));
            } else {
                return this.matrix.some(m => (m.tube_jumper || m.tube_tujuan) === tubeStr && Number(m.core_jumper || m.core_tujuan) === Number(coreNum));
            }
        },

        togglePort(coreNum) {
            const tubeAsalStr = 'Tube ' + this.activeTubeAsal;
            const existingIdx = this.matrix.findIndex(m => m.tube_asal === tubeAsalStr && Number(m.core_asal) === Number(coreNum));
            if (existingIdx >= 0) {
                this.matrix.splice(existingIdx, 1);
            } else {
                const color = this.getColor(coreNum);
                this.matrix.push({
                    tube_asal: tubeAsalStr,
                    core_asal: Number(coreNum),
                    core_asal_label: `Core ${coreNum} (${color.name})`,
                    tube_jumper: 'Tube ' + this.activeTubeJumper,
                    core_jumper: Number(coreNum),
                    core_jumper_label: `Core ${coreNum} (${color.name})`,
                    status: 'TERHUBUNG',
                    loss: 0.02
                });
            }
        },

        connectAll1to1() {
            const tubeAsalStr = 'Tube ' + this.activeTubeAsal;
            const tubeJumperStr = 'Tube ' + this.activeTubeJumper;
            for (let i = 1; i <= 12; i++) {
                const existingIdx = this.matrix.findIndex(m => m.tube_asal === tubeAsalStr && Number(m.core_asal) === i);
                const color = this.getColor(i);
                if (existingIdx >= 0) {
                    this.matrix[existingIdx].tube_jumper = tubeJumperStr;
                    this.matrix[existingIdx].core_jumper = i;
                    this.matrix[existingIdx].core_jumper_label = `Core ${i} (${color.name})`;
                } else {
                    this.matrix.push({
                        tube_asal: tubeAsalStr,
                        core_asal: i,
                        core_asal_label: `Core ${i} (${color.name})`,
                        tube_jumper: tubeJumperStr,
                        core_jumper: i,
                        core_jumper_label: `Core ${i} (${color.name})`,
                        status: 'TERHUBUNG',
                        loss: 0.02
                    });
                }
            }
        },

        swapTubes() {
            const temp = this.activeTubeAsal;
            this.activeTubeAsal = this.activeTubeJumper;
            this.activeTubeJumper = temp;
        },

        resetConnections() {
            if (confirm('Reset seluruh daftar sambungan core?')) {
                this.matrix = [];
            }
        },

        removeRow(index) {
            this.matrix.splice(index, 1);
        },

        addManualRow() {
            const nextCore = (this.matrix.length % 12) + 1;
            const color = this.getColor(nextCore);
            this.matrix.push({
                tube_asal: 'Tube ' + this.activeTubeAsal,
                core_asal: nextCore,
                core_asal_label: `Core ${nextCore} (${color.name})`,
                tube_jumper: 'Tube ' + this.activeTubeJumper,
                core_jumper: nextCore,
                core_jumper_label: `Core ${nextCore} (${color.name})`,
                status: 'TERHUBUNG',
                loss: 0.02
            });
        }
    };
}

function coreManeuverModal() {
    return {
        titikLokasi: '',
        tipeLokasi: 'CLOSURE LAPANGAN (JC)',
        statusAlokasi: 'SESUDAH',
        sifatManuver: 'SEMENTARA',
        titikNormalisasi: '',
        statusCoreAset: 'TERPAKAI (TRAFFIC AKTIF)',
        keterangan: '',
        kapasitasAsal: 24,
        jumlahTubeAsal: 2,
        kapasitasTujuan: 24,
        jumlahTubeTujuan: 2,
        activeTubeAsal: 1,
        activeTubeTujuan: 1,
        selectedSource: null,
        colors: [
            { num: 1, name: 'Biru', hex: '#3b82f6' },
            { num: 2, name: 'Oranye', hex: '#f97316' },
            { num: 3, name: 'Hijau', hex: '#22c55e' },
            { num: 4, name: 'Cokelat', hex: '#a16207' },
            { num: 5, name: 'Abu-abu', hex: '#94a3b8' },
            { num: 6, name: 'Putih', hex: '#f8fafc', isWhite: true },
            { num: 7, name: 'Merah', hex: '#ef4444' },
            { num: 8, name: 'Hitam', hex: '#0f172a', isBlack: true },
            { num: 9, name: 'Kuning', hex: '#eab308' },
            { num: 10, name: 'Ungu', hex: '#a855f7' },
            { num: 11, name: 'Pink', hex: '#ec4899' },
            { num: 12, name: 'Toska', hex: '#06b6d4' }
        ],
        connections: [
            {
                tube_asal: 'Tube 1',
                core_asal: 1,
                core_asal_name: 'Biru',
                tube_tujuan: 'Tube 1',
                core_tujuan: 1,
                core_tujuan_name: 'Biru'
            }
        ],

        getColor(num) {
            return this.colors.find(c => c.num === Number(num)) || { num: num, name: 'Core ' + num, hex: '#94a3b8' };
        },

        isSourceConnected(tubeNum, coreNum) {
            const tubeStr = 'Tube ' + tubeNum;
            return this.connections.some(c => c.tube_asal === tubeStr && Number(c.core_asal) === Number(coreNum));
        },

        isTargetConnected(tubeNum, coreNum) {
            const tubeStr = 'Tube ' + tubeNum;
            return this.connections.some(c => c.tube_tujuan === tubeStr && Number(c.core_tujuan) === Number(coreNum));
        },

        selectSourcePort(coreNum) {
            if (this.selectedSource && this.selectedSource.tube === this.activeTubeAsal && this.selectedSource.core === coreNum) {
                this.selectedSource = null;
            } else {
                this.selectedSource = { tube: this.activeTubeAsal, core: coreNum };
            }
        },

        selectTargetPort(coreNum) {
            if (!this.selectedSource) {
                this.selectedSource = { tube: this.activeTubeAsal, core: coreNum };
                return;
            }
            const sTubeStr = 'Tube ' + this.selectedSource.tube;
            const tTubeStr = 'Tube ' + this.activeTubeTujuan;
            const sCore = this.selectedSource.core;
            const tCore = coreNum;
            const sColor = this.getColor(sCore);
            const tColor = this.getColor(tCore);

            const existingIdx = this.connections.findIndex(c => c.tube_asal === sTubeStr && Number(c.core_asal) === Number(sCore));
            if (existingIdx >= 0) {
                this.connections[existingIdx] = {
                    tube_asal: sTubeStr,
                    core_asal: sCore,
                    core_asal_name: sColor.name,
                    tube_tujuan: tTubeStr,
                    core_tujuan: tCore,
                    core_tujuan_name: tColor.name
                };
            } else {
                this.connections.push({
                    tube_asal: sTubeStr,
                    core_asal: sCore,
                    core_asal_name: sColor.name,
                    tube_tujuan: tTubeStr,
                    core_tujuan: tCore,
                    core_tujuan_name: tColor.name
                });
            }
            this.selectedSource = null;
        },

        connectAll1to1() {
            const sTubeStr = 'Tube ' + this.activeTubeAsal;
            const tTubeStr = 'Tube ' + this.activeTubeTujuan;
            for (let i = 1; i <= 12; i++) {
                const sColor = this.getColor(i);
                const tColor = this.getColor(i);
                const existingIdx = this.connections.findIndex(c => c.tube_asal === sTubeStr && Number(c.core_asal) === i);
                if (existingIdx >= 0) {
                    this.connections[existingIdx].tube_tujuan = tTubeStr;
                    this.connections[existingIdx].core_tujuan = i;
                    this.connections[existingIdx].core_tujuan_name = tColor.name;
                } else {
                    this.connections.push({
                        tube_asal: sTubeStr,
                        core_asal: i,
                        core_asal_name: sColor.name,
                        tube_tujuan: tTubeStr,
                        core_tujuan: i,
                        core_tujuan_name: tColor.name
                    });
                }
            }
        },

        swapTubes() {
            const temp = this.activeTubeAsal;
            this.activeTubeAsal = this.activeTubeTujuan;
            this.activeTubeTujuan = temp;
        },

        resetConnections() {
            if (confirm('Reset seluruh daftar manuver core?')) {
                this.connections = [];
                this.selectedSource = null;
            }
        },

        removeConnection(index) {
            this.connections.splice(index, 1);
        },

        get primaryCoreAsal() {
            if (this.connections.length > 0) return this.connections[0].core_asal;
            return 1;
        },

        get primaryCoreTujuan() {
            if (this.connections.length > 0) return this.connections[0].core_tujuan;
            return 1;
        },

        get summaryCoreDialihkan() {
            if (this.connections.length === 0) return 'Belum ada core yang disambungkan';
            return this.connections.map(c => `T${String(c.tube_asal).replace('Tube ', '')}C${c.core_asal} ➔ T${String(c.tube_tujuan).replace('Tube ', '')}C${c.core_tujuan}`).join(', ');
        }
    };
}
</script>

<!-- Modal Tambah Joint Closure (JC) & Sambungan Kabel -->
<div id="modal-jc" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
     x-data="jointClosureModal()">
    <div class="bg-slate-50 dark:bg-slate-900 rounded-3xl max-w-5xl w-full shadow-2xl border border-teal-800/40 overflow-hidden my-auto animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="px-6 py-4.5 text-white flex items-center justify-between border-b border-teal-500/20"
             style="background: var(--gomsn-header-bg) !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-base text-white tracking-wide flex items-center gap-2">
                        Tambah Data Joint Closure (JC) & Sambungan Kabel
                    </h3>
                    <p class="text-[11px] text-teal-200/80 font-normal">Pencatatan penempatan closure fisik, kapasitas kabel eksisting, jumper & matrix splicing fiber</p>
                </div>
            </div>
            <button type="button" onclick="closeJcModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg transition">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.joint-closures.store', $tiket) }}" class="p-5 sm:p-6 max-h-[82vh] overflow-y-auto space-y-5 custom-scrollbar">
            @csrf
            <input type="hidden" name="kapasitas_core" :value="kapasitasAsal">

            <!-- 1. IDENTITAS & LOKASI FISIK CLOSURE -->
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-3.5">
                <div class="flex items-center gap-2 text-rose-500 font-extrabold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>1. IDENTITAS & LOKASI FISIK CLOSURE</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                    <div class="md:col-span-6">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Nama / Kode Closure <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_closure" x-model="namaClosure" required placeholder="Contoh: JC-01, JC Tiang Span 14"
                               class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Jenis Closure <span class="text-rose-500">*</span></label>
                        <select name="tipe_closure" x-model="jenisClosure" required
                                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                            <option value="DOME">DOME (Kubah)</option>
                            <option value="INLINE">INLINE (Horizontal)</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Penempatan Fisik <span class="text-rose-500">*</span></label>
                        <select name="penempatan_fisik" x-model="penempatanFisik" required
                                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                            <option value="POLE">POLE (Tiang Udara)</option>
                            <option value="MANHOLE">MANHOLE (Bawah Tanah)</option>
                            <option value="WALL">WALL (Dinding Gedung)</option>
                            <option value="RACK">RACK (ODF Rack)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <!-- Koordinat GPS -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300 text-[11px]">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                </svg>
                                <span>Koordinat GPS Lokasi Closure</span>
                            </label>
                            <button type="button" @click="getGps(false, true)" :disabled="isGpsLoading" class="text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:text-teal-700 inline-flex items-center gap-1 cursor-pointer disabled:opacity-50">
                                <svg class="w-3.5 h-3.5" :class="isGpsLoading ? 'animate-spin text-amber-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="isGpsLoading ? 'Mendeteksi...' : 'Ambil GPS Saya'"></span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-2.5 rounded-l-xl border border-r-0 border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-500">Lat</span>
                                <input type="text" name="latitude" x-model="lat" placeholder="-6.xxxxxx"
                                       class="w-full text-xs rounded-r-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div class="flex-1 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-2.5 rounded-l-xl border border-r-0 border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-500">Lng</span>
                                <input type="text" name="longitude" x-model="lng" placeholder="107.xxxxxx"
                                       class="w-full text-xs rounded-r-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                            </div>
                            <button type="button" @click="openMap()" title="Buka di Maps"
                                    class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition cursor-pointer">
                                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 012.5 15.488V4.724a2 2 0 012.947-1.789L9 4m0 16l6-3m-6 3V4m6 16l5.447-2.724A2 2 0 0021.5 15.488V4.724a2 2 0 00-2.947-1.789L15 4m0 16V4m0 0L9 4"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Status & Geocoding Feedback -->
                        <div x-show="isGpsLoading" class="flex items-center gap-1.5 text-[10.5px] text-amber-600 dark:text-amber-400 pt-0.5">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="gpsStatus || 'Sedang mencari koordinat lokasi...'"></span>
                        </div>

                        <div x-show="gpsStatus && !isGpsLoading" class="p-2 rounded-lg bg-teal-50/70 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-[10.5px] space-y-0.5">
                            <div class="flex items-center justify-between text-teal-700 dark:text-teal-300 font-semibold">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="gpsStatus"></span>
                                </span>
                            </div>
                            <div x-show="gpsAddress" class="text-slate-600 dark:text-slate-400 text-[10px] flex items-start gap-1 pt-0.5">
                                <span class="shrink-0 text-slate-400">📍</span>
                                <span x-text="gpsAddress" class="line-clamp-2"></span>
                            </div>
                        </div>

                        <div x-show="gpsError && !isGpsLoading" class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-[10.5px] text-rose-700 dark:text-rose-300 flex items-center justify-between">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span x-text="gpsError"></span>
                            </span>
                            <button type="button" @click="getGps(false, true)" class="font-bold underline text-[10px] ml-2 shrink-0">Coba Lagi</button>
                        </div>
                    </div>

                    <!-- Tandai Penambahan Aset Baru (New Cut) -->
                    <div class="flex items-center p-3 sm:p-3.5 rounded-xl border border-amber-200/60 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20">
                        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
                            <input type="checkbox" name="is_new_cut" x-model="isNewCut" value="1"
                                   class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 shrink-0">
                            <div class="min-w-0 flex-1">
                                <span class="block font-bold text-xs text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>Tandai Penambahan Aset Baru (New Cut)</span>
                                </span>
                                <span class="block text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5">Centang jika ada pemasangan closure fisik baru di span ini.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 2. SPESIFIKASI KABEL & KAPASITAS -->
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-3.5">
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-extrabold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>2. SPESIFIKASI KABEL & KAPASITAS</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kabel Asal / Eksisting -->
                    <div class="p-3.5 rounded-xl border border-blue-200 dark:border-blue-900/40 bg-blue-50/40 dark:bg-blue-950/20 space-y-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-blue-600 text-white font-extrabold text-[10px] uppercase tracking-wider">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                            KABEL ASAL / EKSISTING
                        </span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Kapasitas Kabel <span class="text-rose-500">*</span></label>
                                <select name="kapasitas_kabel_asal" x-model.number="kapasitasAsal" @change="jumlahTubeAsal = Math.max(1, Math.ceil(kapasitasAsal / 12))"
                                        class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 p-2 text-slate-800 dark:text-slate-100">
                                    <option value="12">12 Core</option>
                                    <option value="24">24 Core</option>
                                    <option value="48">48 Core</option>
                                    <option value="96">96 Core</option>
                                    <option value="144">144 Core</option>
                                    <option value="288">288 Core</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Jumlah Tube <span class="text-rose-500">*</span></label>
                                <input type="number" min="1" max="24" name="jumlah_tube_asal" x-model.number="jumlahTubeAsal"
                                       class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 p-2 text-slate-800 dark:text-slate-100">
                            </div>
                        </div>
                    </div>

                    <!-- Kabel Jumper / Distribusi -->
                    <div class="p-3.5 rounded-xl border border-cyan-200 dark:border-cyan-900/40 bg-cyan-50/40 dark:bg-cyan-950/20 space-y-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-teal-600 text-white font-extrabold text-[10px] uppercase tracking-wider">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                            KABEL JUMPER / DISTRIBUSI
                        </span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Kapasitas Kabel <span class="text-rose-500">*</span></label>
                                <select name="kapasitas_kabel_jumper" x-model.number="kapasitasJumper" @change="jumlahTubeJumper = Math.max(1, Math.ceil(kapasitasJumper / 12))"
                                        class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 p-2 text-slate-800 dark:text-slate-100">
                                    <option value="12">12 Core</option>
                                    <option value="24">24 Core</option>
                                    <option value="48">48 Core</option>
                                    <option value="96">96 Core</option>
                                    <option value="144">144 Core</option>
                                    <option value="288">288 Core</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Jumlah Tube <span class="text-rose-500">*</span></label>
                                <input type="number" min="1" max="24" name="jumlah_tube_jumper" x-model.number="jumlahTubeJumper"
                                       class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 p-2 text-slate-800 dark:text-slate-100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. VISUAL FIBER PATCHER (JOINTING LURUS) -->
            <div class="rounded-2xl p-4 sm:p-5 shadow-2xl border border-teal-500/30 text-slate-100 space-y-4"
                 style="background: linear-gradient(180deg, #021a24 0%, #03212c 100%);">
                <!-- Patcher Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-2 border-b border-slate-800">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-300 text-xs font-extrabold tracking-wide">
                        <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Visual Fiber Patcher (Jointing Lurus)
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="connectAll1to1()" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                            <span>+ Sambung (1:1)</span>
                        </button>
                        <button type="button" @click="swapTubes()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <span>Swap T1 ↔ T2</span>
                        </button>
                        <button type="button" @click="resetConnections()" class="px-3 py-1.5 rounded-lg bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 border border-rose-800/50 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Notice banner -->
                <div class="px-3 py-2 rounded-xl bg-teal-950/40 border border-teal-500/20 text-teal-300 text-[11px] flex items-center justify-center gap-2 text-center">
                    <span>⚡ Klik port core (C1-C12) untuk langsung menyambung 1-ke-1 secara otomatis</span>
                </div>

                <!-- Canvas Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                    <!-- Left: KABEL ASAL (INPUT) -->
                    <div class="lg:col-span-5 bg-slate-950/60 rounded-2xl p-3.5 border border-slate-800/80 space-y-3">
                        <div class="flex items-center justify-between pb-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span class="font-extrabold text-xs text-slate-200 uppercase tracking-wide">Kabel Asal (Input)</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[10.5px] font-mono text-slate-300" x-text="kapasitasAsal + ' Core (' + jumlahTubeAsal + 'T)'"></span>
                        </div>

                        <!-- Tube Tabs -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                            <template x-for="t in jumlahTubeAsal" :key="t">
                                <button type="button" @click="activeTubeAsal = t"
                                        :class="activeTubeAsal === t ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-500/30' : 'bg-slate-900 text-slate-400 hover:text-slate-200 border border-slate-800'"
                                        class="px-3 py-1 rounded-lg text-[11px] font-mono transition cursor-pointer"
                                        x-text="'Tube ' + t"></button>
                            </template>
                        </div>

                        <!-- Ports Grid (2 Columns: C1/C2, C3/C4, ...) -->
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="c in colors" :key="c.num">
                                <button type="button" @click="togglePort(c.num)"
                                        :class="isConnected(activeTubeAsal, c.num) ? 'border-teal-400/80 bg-teal-950/60 shadow-[0_0_10px_rgba(20,184,166,0.3)] text-white' : 'border-slate-800/90 bg-slate-900/90 text-slate-300 hover:border-slate-700 hover:bg-slate-800'"
                                        class="flex items-center justify-between px-2.5 py-2 rounded-xl border text-[11px] font-semibold transition group cursor-pointer">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: c.hex, border: c.isWhite ? '1px solid #94a3b8' : (c.isBlack ? '1px solid #475569' : 'none') }"></span>
                                        <span class="font-mono truncate" x-text="'C' + c.num + ' ' + c.name"></span>
                                    </div>
                                    <template x-if="isConnected(activeTubeAsal, c.num)">
                                        <span class="w-4 h-4 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Middle Indicator -->
                    <div class="lg:col-span-2 flex flex-col items-center justify-center py-2 space-y-2">
                        <div class="hidden lg:flex items-center gap-1 text-slate-600 font-mono text-xs">
                            <span>○</span>
                            <span class="tracking-widest">········</span>
                            <span>○</span>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-teal-500/20 border border-teal-500/40 text-teal-300 text-[11px] font-bold font-mono tracking-wide shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span x-text="matrix.length + ' Sambungan Aktif'"></span>
                        </span>
                    </div>

                    <!-- Right: KABEL JUMPER (OUTPUT) -->
                    <div class="lg:col-span-5 bg-slate-950/60 rounded-2xl p-3.5 border border-slate-800/80 space-y-3">
                        <div class="flex items-center justify-between pb-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                <span class="font-extrabold text-xs text-slate-200 uppercase tracking-wide">Kabel Jumper (Output)</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[10.5px] font-mono text-slate-300" x-text="kapasitasJumper + ' Core (' + jumlahTubeJumper + 'T)'"></span>
                        </div>

                        <!-- Tube Tabs -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                            <template x-for="t in jumlahTubeJumper" :key="t">
                                <button type="button" @click="activeTubeJumper = t"
                                        :class="activeTubeJumper === t ? 'bg-teal-600 text-white font-bold shadow-md shadow-teal-500/30' : 'bg-slate-900 text-slate-400 hover:text-slate-200 border border-slate-800'"
                                        class="px-3 py-1 rounded-lg text-[11px] font-mono transition cursor-pointer"
                                        x-text="'Tube ' + t"></button>
                            </template>
                        </div>

                        <!-- Ports Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="c in colors" :key="c.num">
                                <div :class="isConnected(activeTubeJumper, c.num, true) ? 'border-teal-400/80 bg-teal-950/60 shadow-[0_0_10px_rgba(20,184,166,0.3)] text-white' : 'border-slate-800/90 bg-slate-900/90 text-slate-300'"
                                     class="flex items-center justify-between px-2.5 py-2 rounded-xl border text-[11px] font-semibold transition">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: c.hex, border: c.isWhite ? '1px solid #94a3b8' : (c.isBlack ? '1px solid #475569' : 'none') }"></span>
                                        <span class="font-mono truncate" x-text="'C' + c.num + ' ' + c.name"></span>
                                    </div>
                                    <template x-if="isConnected(activeTubeJumper, c.num, true)">
                                        <span class="w-4 h-4 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Active Connection Chips -->
                <div class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center flex-wrap gap-2 text-xs">
                        <span class="text-teal-400 font-bold flex items-center gap-1.5 mr-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Sambungan Aktif:
                        </span>
                        <template x-for="(row, idx) in matrix" :key="idx">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900/90 border border-teal-500/40 text-teal-200 text-[11px] font-mono shadow-sm">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: getColor(row.core_asal).hex }"></span>
                                <span x-text="'T' + String(row.tube_asal).replace('Tube ', '') + 'C' + row.core_asal + ' (' + getColor(row.core_asal).name + ') ➜ T' + String(row.tube_jumper).replace('Tube ', '') + 'C' + row.core_jumper + ' (' + getColor(row.core_jumper).name + ')'"></span>
                                <button type="button" @click="removeRow(idx)" class="w-3.5 h-3.5 rounded-full hover:bg-rose-500/20 hover:text-rose-400 text-slate-400 flex items-center justify-center font-bold ml-1 transition cursor-pointer">&times;</button>
                            </span>
                        </template>
                        <template x-if="matrix.length === 0">
                            <span class="text-slate-500 text-xs italic">Belum ada core yang disambung. Klik port di atas untuk menyambung.</span>
                        </template>
                    </div>
                    <div class="text-teal-400 font-bold text-xs font-mono" x-text="matrix.length + ' Core Terhubung'"></div>
                </div>
            </div>

            <!-- 4. DAFTAR CORE YANG DISAMBUNG / MATRIX -->
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-2 text-slate-800 dark:text-slate-100 font-bold text-xs">
                        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Daftar Core yang Disambung / Matrix:</span>
                    </div>
                    <button type="button" @click="addManualRow()" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/70 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                        <span>+ Tambah Baris Manual</span>
                    </button>
                </div>

                <!-- Hidden input to send matrix array as JSON -->
                <input type="hidden" name="cores" :value="JSON.stringify(matrix)">

                <div class="space-y-2">
                    <template x-for="(row, idx) in matrix" :key="idx">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 tracking-wider mb-0.5">Tube Asal</label>
                                <input type="text" x-model="row.tube_asal" class="w-full text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-1.5 outline-hidden transition">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 tracking-wider mb-0.5">Core Asal</label>
                                <input type="text" x-model="row.core_asal_label" class="w-full text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-1.5 outline-hidden transition">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 tracking-wider mb-0.5">Tube Jumper</label>
                                <input type="text" x-model="row.tube_jumper" class="w-full text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-1.5 outline-hidden transition">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 tracking-wider mb-0.5">Core Jumper</label>
                                <input type="text" x-model="row.core_jumper_label" class="w-full text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-1.5 outline-hidden transition">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 tracking-wider mb-0.5">Status</label>
                                <select x-model="row.status" class="w-full text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-1.5 outline-hidden transition">
                                    <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white" value="TERHUBUNG">TERHUBUNG</option>
                                    <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white" value="PUTUS / LOSS">PUTUS / LOSS</option>
                                    <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white" value="CADANGAN / SPARE">CADANGAN / SPARE</option>
                                    <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white" value="RUSAK">RUSAK</option>
                                </select>
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 tracking-wider mb-0.5">Loss (dB)</label>
                                <input type="number" step="0.01" x-model.number="row.loss" class="w-full text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-1.5 outline-hidden transition">
                            </div>
                            <div class="sm:col-span-1 flex justify-center pt-3 sm:pt-0">
                                <button type="button" @click="removeRow(idx)" title="Hapus sambungan"
                                        class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="matrix.length === 0">
                        <div class="p-6 text-center text-slate-400 text-xs border border-dashed border-slate-300 dark:border-slate-700 rounded-xl">
                            Belum ada baris matrix. Klik salah satu port pada Visual Fiber Patcher di atas atau gunakan tombol "+ Tambah Baris Manual".
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeJcModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition cursor-pointer">
                    ✕ Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-extrabold text-xs flex items-center gap-2 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition cursor-pointer"
                        style="background: var(--gomsn-active-bg) !important;">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Simpan Data Joint Closure</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Record Manuver Core -->
<div id="modal-maneuver" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
     x-data="coreManeuverModal()">
    <div class="bg-slate-50 dark:bg-slate-900 rounded-3xl max-w-5xl w-full shadow-2xl border border-teal-800/40 overflow-hidden my-auto animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="px-6 py-4.5 text-white flex items-center justify-between border-b border-teal-500/20"
             style="background: var(--gomsn-header-bg) !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-base text-white tracking-wide">
                        Tambah Record Manuver Core
                    </h3>
                    <p class="text-[11px] text-teal-200/80 font-normal">Splicing, Bypassing & Pengalihan Core Fiber Optik</p>
                </div>
            </div>
            <button type="button" onclick="closeManeuverModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg transition">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.maneuvers.store', $tiket) }}" class="p-5 sm:p-6 max-h-[82vh] overflow-y-auto space-y-5 custom-scrollbar">
            @csrf

            <!-- 1. LOKASI & STATUS MANUVER -->
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-3.5">
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-extrabold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>1. LOKASI & STATUS MANUVER</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                    <div class="md:col-span-7">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Titik / Lokasi Manuver <span class="text-rose-500">*</span></label>
                        <input type="text" name="segmen" x-model="titikLokasi" required placeholder="CONTOH: JC-01, OTB-POP-BDG, FAT-04"
                               class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Tipe Lokasi Aset <span class="text-rose-500">*</span></label>
                        <select name="tipe_lokasi" x-model="tipeLokasi" required
                                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                            <option value="CLOSURE LAPANGAN (JC)">CLOSURE LAPANGAN (JC)</option>
                            <option value="OTB/ODF">OTB / ODF</option>
                            <option value="FAT/ODP">FAT / ODP</option>
                            <option value="ODC">ODC</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 pt-1">
                    <!-- Status Alokasi -->
                    <div class="md:col-span-6 space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-[11px]">Status Alokasi <span class="text-rose-500">*</span></label>
                        <input type="hidden" name="status_alokasi" :value="statusAlokasi">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="statusAlokasi = 'SEBELUM'"
                                    :class="statusAlokasi === 'SEBELUM' ? 'bg-slate-700 text-white font-bold border-slate-600 shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'"
                                    class="px-3 py-2.5 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>SEBELUM</span>
                            </button>
                            <button type="button" @click="statusAlokasi = 'SESUDAH'"
                                    :class="statusAlokasi === 'SESUDAH' ? 'bg-teal-700 text-white font-bold border-teal-600 shadow-sm' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'"
                                    class="px-3 py-2.5 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>SESUDAH</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sifat Manuver -->
                    <div class="md:col-span-6 space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-[11px]">Sifat Manuver <span class="text-rose-500">*</span></label>
                        <input type="hidden" name="sifat_manuver" :value="sifatManuver">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="sifatManuver = 'SEMENTARA'"
                                    :class="sifatManuver === 'SEMENTARA' ? 'bg-amber-400 hover:bg-amber-400 text-slate-950 font-extrabold border-amber-500 shadow-md shadow-amber-500/20' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'"
                                    class="px-3 py-2.5 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>SEMENTARA</span>
                            </button>
                            <button type="button" @click="sifatManuver = 'PERMANEN'"
                                    :class="sifatManuver === 'PERMANEN' ? 'bg-teal-600 hover:bg-teal-500 text-white font-extrabold border-teal-500 shadow-md shadow-teal-500/20' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'"
                                    class="px-3 py-2.5 rounded-xl border text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z"/>
                                </svg>
                                <span>PERMANEN</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. VISUAL FIBER PATCHER (MANUVER) -->
            <div class="rounded-2xl p-4 sm:p-5 shadow-2xl border border-teal-500/30 text-slate-100 space-y-4"
                 style="background: linear-gradient(180deg, #021a24 0%, #03212c 100%);">
                <!-- Patcher Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-2 border-b border-slate-800">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-300 text-xs font-extrabold tracking-wide">
                        <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Visual Fiber Patcher
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="connectAll1to1()" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                            <span>+ Sambung (1:1)</span>
                        </button>
                        <button type="button" @click="swapTubes()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <span>Swap T1 ↔ T2</span>
                        </button>
                        <button type="button" @click="resetConnections()" class="px-3 py-1.5 rounded-lg bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 border border-rose-800/50 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Notice banner -->
                <div class="px-3 py-2 rounded-xl bg-teal-950/40 border border-teal-500/20 text-teal-300 text-[11px] flex items-center justify-center gap-2 text-center">
                    <span>⚡ Tap Port Asal (Kiri) ➔ Tap Port Tujuan (Kanan)</span>
                </div>

                <!-- Canvas Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                    <!-- Left: ASAL (INPUT) -->
                    <div class="lg:col-span-5 bg-slate-950/60 rounded-2xl p-3.5 border border-slate-800/80 space-y-3">
                        <div class="flex items-center justify-between pb-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                                </svg>
                                <span class="font-extrabold text-xs text-slate-200 uppercase tracking-wide">ASAL (INPUT)</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[10.5px] font-mono text-slate-300" x-text="kapasitasAsal + ' Core (' + jumlahTubeAsal + 'T)'"></span>
                        </div>

                        <!-- Tube Tabs -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                            <template x-for="t in jumlahTubeAsal" :key="t">
                                <button type="button" @click="activeTubeAsal = t"
                                        :class="activeTubeAsal === t ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-500/30' : 'bg-slate-900 text-slate-400 hover:text-slate-200 border border-slate-800'"
                                        class="px-3 py-1 rounded-lg text-[11px] font-mono transition cursor-pointer"
                                        x-text="'Tube ' + t"></button>
                            </template>
                        </div>

                        <!-- Ports Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="c in colors" :key="c.num">
                                <button type="button" @click="selectSourcePort(c.num)"
                                        :class="{
                                            'ring-2 ring-amber-400 bg-amber-950/60 text-amber-100 border-amber-400 animate-pulse': selectedSource && selectedSource.tube === activeTubeAsal && selectedSource.core === c.num,
                                            'border-teal-400/80 bg-teal-950/60 text-white shadow-[0_0_10px_rgba(20,184,166,0.3)]': isSourceConnected(activeTubeAsal, c.num) && !(selectedSource && selectedSource.tube === activeTubeAsal && selectedSource.core === c.num),
                                            'border-slate-800/90 bg-slate-900/90 text-slate-300 hover:border-slate-700 hover:bg-slate-800': !isSourceConnected(activeTubeAsal, c.num) && !(selectedSource && selectedSource.tube === activeTubeAsal && selectedSource.core === c.num)
                                        }"
                                        class="flex items-center justify-between px-2.5 py-2 rounded-xl border text-[11px] font-semibold transition group cursor-pointer">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: c.hex, border: c.isWhite ? '1px solid #94a3b8' : (c.isBlack ? '1px solid #475569' : 'none') }"></span>
                                        <span class="font-mono truncate" x-text="'C' + c.num + ' ' + c.name"></span>
                                    </div>
                                    <template x-if="isSourceConnected(activeTubeAsal, c.num)">
                                        <span class="w-4 h-4 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Middle Indicator -->
                    <div class="lg:col-span-2 flex flex-col items-center justify-center py-2 space-y-2">
                        <div class="hidden lg:flex items-center gap-1 text-slate-600 font-mono text-xs">
                            <span>○</span>
                            <span class="tracking-widest">········</span>
                            <span>○</span>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-teal-500/20 border border-teal-500/40 text-teal-300 text-[11px] font-bold font-mono tracking-wide shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span x-text="connections.length + ' Sambungan Aktif'"></span>
                        </span>
                    </div>

                    <!-- Right: TUJUAN (OUTPUT) -->
                    <div class="lg:col-span-5 bg-slate-950/60 rounded-2xl p-3.5 border border-slate-800/80 space-y-3">
                        <div class="flex items-center justify-between pb-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                </svg>
                                <span class="font-extrabold text-xs text-slate-200 uppercase tracking-wide">TUJUAN (OUTPUT)</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[10.5px] font-mono text-slate-300" x-text="kapasitasTujuan + ' Core (' + jumlahTubeTujuan + 'T)'"></span>
                        </div>

                        <!-- Tube Tabs -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                            <template x-for="t in jumlahTubeTujuan" :key="t">
                                <button type="button" @click="activeTubeTujuan = t"
                                        :class="activeTubeTujuan === t ? 'bg-teal-600 text-white font-bold shadow-md shadow-teal-500/30' : 'bg-slate-900 text-slate-400 hover:text-slate-200 border border-slate-800'"
                                        class="px-3 py-1 rounded-lg text-[11px] font-mono transition cursor-pointer"
                                        x-text="'Tube ' + t"></button>
                            </template>
                        </div>

                        <!-- Ports Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="c in colors" :key="c.num">
                                <button type="button" @click="selectTargetPort(c.num)"
                                        :class="isTargetConnected(activeTubeTujuan, c.num) ? 'border-teal-400/80 bg-teal-950/60 shadow-[0_0_10px_rgba(20,184,166,0.3)] text-white' : 'border-slate-800/90 bg-slate-900/90 text-slate-300 hover:border-teal-500/60 hover:bg-slate-800'"
                                        class="flex items-center justify-between px-2.5 py-2 rounded-xl border text-[11px] font-semibold transition group cursor-pointer">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: c.hex, border: c.isWhite ? '1px solid #94a3b8' : (c.isBlack ? '1px solid #475569' : 'none') }"></span>
                                        <span class="font-mono truncate" x-text="'C' + c.num + ' ' + c.name"></span>
                                    </div>
                                    <template x-if="isTargetConnected(activeTubeTujuan, c.num)">
                                        <span class="w-4 h-4 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center text-[10px] font-bold">✓</span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Active Connection Chips -->
                <div class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center flex-wrap gap-2 text-xs">
                        <span class="text-teal-400 font-bold flex items-center gap-1.5 mr-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Sambungan Aktif:
                        </span>
                        <template x-for="(conn, idx) in connections" :key="idx">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900/90 border border-teal-500/40 text-teal-200 text-[11px] font-mono shadow-sm">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: getColor(conn.core_asal).hex }"></span>
                                <span x-text="'T' + String(conn.tube_asal).replace('Tube ', '') + 'C' + conn.core_asal + ' (' + conn.core_asal_name + ') ➜ T' + String(conn.tube_tujuan).replace('Tube ', '') + 'C' + conn.core_tujuan + ' (' + conn.core_tujuan_name + ')'"></span>
                                <button type="button" @click="removeConnection(idx)" class="w-3.5 h-3.5 rounded-full hover:bg-rose-500/20 hover:text-rose-400 text-slate-400 flex items-center justify-center font-bold ml-1 transition cursor-pointer">&times;</button>
                            </span>
                        </template>
                        <template x-if="connections.length === 0">
                            <span class="text-slate-500 text-xs italic">Belum ada manuver yang dicatat. Tap port Asal lalu tap port Tujuan.</span>
                        </template>
                    </div>
                    <div class="text-teal-400 font-bold text-xs font-mono" x-text="connections.length + ' Core Terhubung'"></div>
                </div>
            </div>

            <!-- 3. DETAIL TAMBAHAN & ALASAN -->
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm space-y-3.5">
                <div class="flex items-center gap-2 text-slate-800 dark:text-slate-100 font-bold text-xs">
                    <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Detail Tambahan & Alasan Manuver</span>
                </div>

                <!-- Hidden inputs for form submit -->
                <input type="hidden" name="core_asal" :value="primaryCoreAsal">
                <input type="hidden" name="core_tujuan" :value="primaryCoreTujuan">
                <input type="hidden" name="connections" :value="JSON.stringify(connections)">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Core yang Dialihkan</label>
                        <div class="p-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-900/60 text-xs text-slate-800 dark:text-slate-200 font-mono"
                             x-text="summaryCoreDialihkan"></div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Titik Normalisasi / Estimasi Waktu</label>
                        <input type="text" name="titik_normalisasi" x-model="titikNormalisasi" placeholder="Misal: Normalisasi saat span pengganti terpasang"
                               class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Status Core Aset</label>
                        <select name="status_core_aset" x-model="statusCoreAset"
                                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                            <option value="TERPAKAI (TRAFFIC AKTIF)">TERPAKAI (TRAFFIC AKTIF)</option>
                            <option value="CADANGAN / SPARE">CADANGAN / SPARE</option>
                            <option value="LOSS TINGGI">LOSS TINGGI</option>
                            <option value="RUSAK / PUTUS">RUSAK / PUTUS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Alasan / Keterangan Manuver <span class="text-rose-500">*</span></label>
                        <input type="text" name="alasan" x-model="keterangan" required placeholder="Contoh: Core putus di Span 14-15, dialihkan ke Core cadangan"
                               class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/60 p-2.5 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeManeuverModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition cursor-pointer">
                    ✕ Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-extrabold text-xs flex items-center gap-2 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition cursor-pointer"
                        style="background: var(--gomsn-active-bg) !important;">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Manuver Core</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Verifikasi Closing NOC -->
<div id="modal-verify-close" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: var(--gomsn-header-bg) !important;">
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
<!-- MODAL INPUT RESUME PEKERJAAN (Matching Reference UI) -->
<!-- ========================================================================= -->
@php
    $modalInitialMembers = old('team_members');
    if (!is_array($modalInitialMembers) || empty($modalInitialMembers)) {
        $modalInitialMembers = (!empty($tiket->team_members) && is_array($tiket->team_members) && count($tiket->team_members) > 0)
            ? array_values($tiket->team_members)
            : ($tiket->assignees->count() > 0 ? array_values($tiket->assignees->pluck('name')->all()) : ['Ricky']);
    }
    if (empty($modalInitialMembers)) {
        $modalInitialMembers = ['Ricky'];
    }
@endphp
<div id="modal-resume" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
     x-data="resumeFormModal(@js($modalInitialMembers))">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden my-auto transform transition-all animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-5 py-4 sm:px-6 text-white flex items-center justify-between"
             style="background: #0f1d35;">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm font-bold shrink-0">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <h3 class="font-bold text-sm sm:text-base text-white tracking-tight">Input Resume Pekerjaan</h3>
            </div>
            <button type="button" onclick="closeResumeModal()"
                    class="w-8 h-8 rounded-full text-slate-300 hover:text-white hover:bg-white/10 flex items-center justify-center transition cursor-pointer text-lg">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('tickets.closing-awal', $tiket) }}">
            @csrf
            <div class="p-5 sm:p-6 space-y-4 max-h-[calc(85vh-120px)] overflow-y-auto">
                
                <!-- 1. Team OM / Daftar Nama Teknis Lapangan * -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-2">
                        Team OM / Daftar Nama Teknis Lapangan <span class="text-rose-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <template x-for="(member, idx) in teamMembers" :key="idx">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                    <input type="text"
                                           name="team_members[]"
                                           x-model="teamMembers[idx]"
                                           required
                                           placeholder="Nama teknisi..."
                                           class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-xs sm:text-sm text-slate-800 transition outline-none">
                                </div>
                                <button type="button"
                                        @click="removeMember(idx)"
                                        title="Hapus Anggota"
                                        class="w-9 h-9 rounded-xl border border-rose-300 hover:border-rose-400 text-rose-500 hover:bg-rose-50 flex items-center justify-center shrink-0 cursor-pointer transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2">
                        <button type="button"
                                @click="addMember()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Tambah Anggota Team</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Problem / Temuan Masalah di Lapangan * -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Problem / Temuan Masalah di Lapangan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="problem_temuan"
                              rows="3"
                              required
                              placeholder="Contoh: Kabel Tertarik Truk di Crossingan Jalan span KM 12+400"
                              class="w-full p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-xs sm:text-sm text-slate-800 outline-none transition leading-relaxed">{{ old('problem_temuan', $tiket->problem_temuan) }}</textarea>
                </div>

                <!-- 3. Action / Tindakan Perbaikan yang Dilakukan * -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Action / Tindakan Perbaikan yang Dilakukan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="action_taken"
                              rows="3"
                              required
                              placeholder="Contoh: Jumper Kabel 150 meter, Pemasangan New JC 2 Titik (JC1 dan JC2)..."
                              class="w-full p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-xs sm:text-sm text-slate-800 outline-none transition leading-relaxed">{{ old('action_taken', $tiket->action_taken) }}</textarea>
                </div>

                <!-- 4. Tipe Penanganan Fisik / Core -->
                <div class="p-3.5 sm:px-4 sm:py-3 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span class="text-xs font-bold text-slate-700 shrink-0">Tipe Penanganan Fisik / Core:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#0D9488] text-white shadow-2xs">
                            Jointing Lurus &amp; Manuver Core
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400 sm:text-right">Dikelola di menu <b class="text-slate-600 font-semibold">Penanganan Core &amp; JC</b></span>
                </div>

                <!-- 5. Catatan Tambahan (Opsional) -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea name="catatan_tambahan"
                              rows="2"
                              placeholder="Catatan tambahan hasil pengujian redaman / informasi khusus..."
                              class="w-full p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-xs sm:text-sm text-slate-800 outline-none transition leading-relaxed">{{ old('catatan_tambahan', $tiket->catatan_tambahan) }}</textarea>
                </div>

                <!-- 6. Pengukuran Redaman Optik (dBm) -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Pengukuran Redaman Optik (dBm)</span>
                        </label>
                        <span class="text-[10.5px] text-slate-500 font-medium">Rentang standar: <b>-40.00</b> s.d. <b>0.00 dBm</b></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <!-- Redaman Sebelum -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Redaman Sebelum (dBm) <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <input type="number"
                                       step="0.01"
                                       min="-60"
                                       max="10"
                                       name="redaman_sebelum"
                                       value="{{ old('redaman_sebelum', $tiket->redaman_sebelum) }}"
                                       placeholder="Contoh: -28.50"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-xs sm:text-sm text-slate-800 outline-none transition font-mono">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-slate-400 font-mono pointer-events-none">dBm</span>
                            </div>
                        </div>

                        <!-- Redaman Sesudah -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Redaman Sesudah (dBm) @if($isBroadband)<span class="text-rose-500 font-bold">* (Wajib Broadband)</span>@else<span class="text-slate-400 font-normal">(Opsional)</span>@endif
                            </label>
                            <div class="relative">
                                <input type="number"
                                       step="0.01"
                                       min="-40"
                                       max="0"
                                       @if($isBroadband) required @endif
                                       name="redaman_sesudah"
                                       value="{{ old('redaman_sesudah', $tiket->redaman_sesudah) }}"
                                       placeholder="Contoh: -18.50"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-xs sm:text-sm text-slate-800 outline-none transition font-mono">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-slate-400 font-mono pointer-events-none">dBm</span>
                            </div>
                        </div>
                    </div>

                    @if($tiket->kategori_tiket === 'BACKBONE')
                        <div class="pt-2 border-t border-slate-200/80">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox"
                                       name="has_link_up"
                                       value="1"
                                       {{ old('has_link_up', $tiket->has_link_up) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-teal-600 border-slate-300 focus:ring-teal-500">
                                <span class="text-xs font-semibold text-slate-700">Konfirmasi Pemulihan Link (LINK UP Restored)</span>
                            </label>
                        </div>
                    @else
                        <input type="hidden" name="has_link_up" value="{{ $tiket->has_link_up ? 1 : 1 }}">
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3.5 sm:px-6 bg-slate-50/90 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button"
                        onclick="closeResumeModal()"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs sm:text-sm flex items-center gap-2 shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Simpan Resume</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- IMAGE LIGHTBOX MODAL (Professional Dark Glass Viewer with Zoom Controls) -->
<!-- ========================================================================= -->
<div id="image-lightbox-modal"
     style="position: fixed !important; inset: 0 !important; z-index: 999999 !important; background-color: rgba(2, 16, 24, 0.92) !important; backdrop-filter: blur(14px) !important; -webkit-backdrop-filter: blur(14px) !important;"
     class="hidden flex flex-col justify-between p-3 sm:p-5 select-none transition-opacity duration-200"
     onclick="closeImageLightbox(event)">
    
    <!-- Top Floating Glass Header Bar -->
    <div style="background: linear-gradient(135deg, rgba(2, 40, 54, 0.88) 0%, rgba(10, 82, 95, 0.88) 100%); border: 1px solid rgba(94, 234, 212, 0.25); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);"
         class="flex items-center justify-between text-white z-20 shrink-0 max-w-5xl mx-auto w-full px-3.5 sm:px-5 py-2.5 rounded-2xl backdrop-blur-md"
         onclick="event.stopPropagation()">
        <div class="flex items-center gap-2.5 min-w-0 pr-2">
            <span class="w-8 h-8 rounded-xl bg-[#18C4C4]/20 text-[#5eead4] border border-[#18C4C4]/35 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
            <div class="min-w-0">
                <span id="lightbox-title" class="font-bold text-xs sm:text-sm text-white block truncate leading-tight">Dokumentasi Foto</span>
                <span class="text-[10px] text-teal-200/80 block truncate">Pratinjau Dokumentasi Tiket</span>
            </div>
        </div>
        
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
            <!-- Zoom Controls Pill -->
            <div class="hidden sm:inline-flex items-center bg-slate-950/50 border border-white/15 rounded-xl p-0.5 mr-1">
                <button type="button" onclick="adjustLightboxZoom(-0.25)" title="Perkecil (Zoom Out)"
                        class="w-7 h-7 rounded-lg hover:bg-white/15 text-slate-200 hover:text-white flex items-center justify-center transition cursor-pointer font-bold text-sm">
                    &minus;
                </button>
                <button type="button" onclick="resetLightboxZoom()" title="Reset Ukuran (100%)"
                        id="lightbox-zoom-label"
                        class="px-2 text-[11px] font-mono font-bold text-cyan-200 hover:text-white cursor-pointer">
                    100%
                </button>
                <button type="button" onclick="adjustLightboxZoom(0.25)" title="Perbesar (Zoom In)"
                        class="w-7 h-7 rounded-lg hover:bg-white/15 text-slate-200 hover:text-white flex items-center justify-center transition cursor-pointer font-bold text-sm">
                    &plus;
                </button>
            </div>

            <!-- Download Button -->
            <button type="button"
                    id="lightbox-download-btn"
                    onclick="downloadLightboxImage(event)"
                    style="background: var(--gomsn-active-bg) !important;"
                    class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 rounded-xl hover:opacity-95 text-white text-xs font-bold shadow-lg shadow-teal-950/40 transition active:scale-95 cursor-pointer">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download</span>
            </button>

            <!-- Close Button -->
            <button type="button"
                    onclick="closeImageLightbox()"
                    class="w-8 h-8 rounded-xl bg-white/10 hover:bg-rose-500 text-white border border-white/15 flex items-center justify-center transition active:scale-95 cursor-pointer"
                    title="Tutup (Esc)">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Center: Scaled Image Viewport -->
    <div class="flex-1 flex items-center justify-center p-2 sm:p-4 min-h-0 relative overflow-hidden select-none" onclick="closeImageLightbox(event)">
        <img id="lightbox-image"
             src=""
             alt="Pratinjau Foto"
             ondblclick="toggleDoubleClickZoom(event)"
             style="max-height: 78vh; max-width: 92vw; border: 1.5px solid rgba(255, 255, 255, 0.16); box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.85); transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);"
             class="object-contain rounded-2xl bg-slate-900/60 cursor-zoom-in"
             onclick="event.stopPropagation()">
    </div>

    <!-- Bottom Instruction Pill (Disembunyikan di Mobile, Khusus Desktop) -->
    <div class="hidden sm:flex justify-center pb-1 z-20 shrink-0 pointer-events-none">
        <div style="background-color: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.12);"
             class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[11px] font-medium text-slate-300 shadow-lg backdrop-blur-md pointer-events-auto">
            <span>Klik dua kali foto untuk Zoom &bull; Klik area gelap atau tekan</span>
            <kbd class="px-1.5 py-0.5 rounded bg-white/15 text-cyan-200 font-mono text-[10px] font-bold border border-white/10">ESC</kbd>
            <span>untuk menutup</span>
        </div>
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
// Copy WA Ticket Format to Clipboard
function copyWaTicketFormat() {
    const rawTemplate = @json($waFormatText);
    const currentUrl = window.location.href.split('?')[0].split('#')[0];
    const textToCopy = rawTemplate + currentUrl;

    const notifySuccess = () => {
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Format WA Disalin!',
                text: 'Format laporan open tiket berhasil disalin ke clipboard.',
                timer: 2500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert('Format laporan WA berhasil disalin ke clipboard!');
        }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(notifySuccess).catch(() => {
            fallbackCopyWa(textToCopy, notifySuccess);
        });
    } else {
        fallbackCopyWa(textToCopy, notifySuccess);
    }
}

function fallbackCopyWa(text, callback) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    ta.style.top = '-9999px';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
        document.execCommand('copy');
        if (typeof callback === 'function') callback();
    } catch (e) {
        alert('Gagal menyalin format WA.');
    }
    document.body.removeChild(ta);
}

// Image Lightbox Modal with Direct Download & Interactive Zoom
let currentLightboxZoom = 1;

function openImageLightbox(url, title = 'Dokumentasi Foto') {
    const modal = document.getElementById('image-lightbox-modal');
    const img = document.getElementById('lightbox-image');
    const titleEl = document.getElementById('lightbox-title');

    if (!modal || !img) return;

    // Teleport modal to document.body so it covers 100% of screen including sidebar & sticky headers
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    resetLightboxZoom();
    img.src = url;
    if (titleEl) {
        titleEl.innerText = title;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function adjustLightboxZoom(delta) {
    currentLightboxZoom = Math.min(3, Math.max(0.5, Math.round((currentLightboxZoom + delta) * 100) / 100));
    applyLightboxZoom();
}

function resetLightboxZoom() {
    currentLightboxZoom = 1;
    applyLightboxZoom();
}

function toggleDoubleClickZoom(e) {
    if (e) e.stopPropagation();
    currentLightboxZoom = currentLightboxZoom > 1 ? 1 : 1.75;
    applyLightboxZoom();
}

function applyLightboxZoom() {
    const img = document.getElementById('lightbox-image');
    const label = document.getElementById('lightbox-zoom-label');
    if (img) {
        img.style.transform = `scale(${currentLightboxZoom})`;
        img.style.cursor = currentLightboxZoom > 1 ? 'zoom-out' : 'zoom-in';
    }
    if (label) {
        label.innerText = Math.round(currentLightboxZoom * 100) + '%';
    }
}

function closeImageLightbox(e) {
    if (e && e.target && e.target.id === 'lightbox-image') return;
    const modal = document.getElementById('image-lightbox-modal');
    if (modal) {
        modal.classList.add('hidden');
        const img = document.getElementById('lightbox-image');
        if (img) {
            img.src = '';
            img.style.transform = 'scale(1)';
        }
    }
    // Restore body scroll unless chat is in fullscreen mode
    const chatCard = document.querySelector('.ticket-chat-card');
    if (!chatCard || !chatCard.classList.contains('is-chat-fullscreen')) {
        document.body.style.overflow = '';
    }
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
            return;
        }
        const chatCard = document.querySelector('.ticket-chat-card');
        if (chatCard && chatCard.classList.contains('is-chat-fullscreen')) {
            toggleChatFullscreen();
            return;
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

// WhatsApp Dynamic Date Divider ensure helper
function ensureChatDateDivider(targetDateStr) {
    const stream = document.getElementById('chat-stream-container');
    if (!stream) return;

    const now = new Date();
    const todayKey = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    let dateKey = todayKey;
    if (targetDateStr && typeof targetDateStr === 'string' && targetDateStr.length >= 10 && targetDateStr.includes('-')) {
        dateKey = targetDateStr.substring(0, 10);
    }

    const allDividers = stream.querySelectorAll('.chat-date-divider');
    const lastDivider = allDividers.length > 0 ? allDividers[allDividers.length - 1] : null;

    if (!lastDivider || lastDivider.dataset.date !== dateKey) {
        let label = 'Hari Ini';
        if (dateKey !== todayKey) {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const yestKey = `${yesterday.getFullYear()}-${String(yesterday.getMonth() + 1).padStart(2, '0')}-${String(yesterday.getDate()).padStart(2, '0')}`;
            if (dateKey === yestKey) {
                label = 'Kemarin';
            } else {
                try {
                    const parts = dateKey.split('-');
                    const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
                    label = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                } catch (e) {
                    label = dateKey;
                }
            }
        }

        const div = document.createElement('div');
        div.className = 'flex justify-center my-2.5 py-0.5 pointer-events-none chat-date-divider';
        div.dataset.date = dateKey;
        div.innerHTML = `
            <span class="px-3 py-1 rounded-xl text-[10.5px] font-semibold bg-white/95 text-slate-600 shadow-[0_1px_3px_rgba(0,0,0,0.08)] border border-slate-200/70 backdrop-blur-xs flex items-center gap-1.5 pointer-events-auto">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>${label}</span>
            </span>
        `;
        stream.appendChild(div);
    }
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
        // Explicitly force opening upwards above the 3-dots
        targetDropdown.style.bottom = 'calc(100% + 6px)';
        targetDropdown.style.top = 'auto';
        targetDropdown.style.right = '0px';
        targetDropdown.style.left = 'auto';
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

    const displaySender = bubble.dataset.sender || 'User';
    const realSender = bubble.dataset.realSender || displaySender;
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
    if (replySenderInput) replySenderInput.value = realSender;
    if (replySnippetInput) replySnippetInput.value = msg;

    if (replySender) replySender.innerText = displaySender;
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

function getDivisionBadgeStyle(divisionOrRole) {
    const raw = String(divisionOrRole || '').toLowerCase().trim();
    if (raw.includes('teknik') || raw.includes('teknisi') || raw.includes('field')) {
        return { bg: '#FFEDD5', text: '#C2410C', border: '#FED7AA' };
    }
    if (raw.includes('it') || raw.includes('sistem') || raw.includes('system') || raw.includes('dev') || raw.includes('programmer')) {
        return { bg: '#EEF2FF', text: '#4338CA', border: '#C7D2FE' };
    }
    if (raw.includes('noc')) {
        return { bg: '#E0F2FE', text: '#0369A1', border: '#BAE6FD' };
    }
    if (raw.includes('finance') || raw.includes('keuangan') || raw.includes('akuntansi') || raw.includes('kasir')) {
        return { bg: '#ECFDF5', text: '#047857', border: '#A7F3D0' };
    }
    if (raw.includes('hrd') || raw.includes('human') || raw.includes('personalia') || raw.includes('hc')) {
        return { bg: '#FFE4E6', text: '#BE123C', border: '#FECDD3' };
    }
    if (raw.includes('operasional') || raw.includes('ops')) {
        return { bg: '#CCFBF1', text: '#0F766E', border: '#99F6E4' };
    }
    if (raw.includes('direksi') || raw.includes('executive') || raw.includes('direktur') || raw.includes('general manager') || raw.includes('gm')) {
        return { bg: '#F3E8FF', text: '#6B21A8', border: '#E9D5FF' };
    }
    if (raw.includes('dispatch')) {
        return { bg: '#FEF3C7', text: '#B45309', border: '#FDE68A' };
    }
    return { bg: '#F1F5F9', text: '#475569', border: '#E2E8F0' };
}

async function handleChatFormSubmit(e) {
    if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
    }

    if (typeof stopVoiceRecognition === 'function') {
        stopVoiceRecognition();
    }

    const form = document.getElementById('quick-chat-form');
    if (!form) return false;
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
        return false;
    }

    const authUserBadge = '{{ auth()->user()?->chat_badge ?? 'Staf' }}';
    const authAvatarUrl = '{{ auth()->user()?->avatar_url ?? '' }}';
    const authInitials = '{{ auth()->user()?->initials ?? 'A' }}';

    const replyId = document.getElementById('chron_reply_to_id')?.value;
    const replySender = document.getElementById('chron_reply_sender')?.value;
    const replySnippet = document.getElementById('chron_reply_snippet')?.value;

    const submitBtn = document.getElementById('btn-quick-chat-submit');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    }

    // Capture form values into FormData BEFORE resetting inputs!
    const formData = new FormData(form);

    // Kosongkan input field seketika saat pesan dikirim agar tidak ada sisa teks (termasuk dari VN/suara)
    if (input) {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    voiceInitialText = '';
    clearAttachedGps();
    clearAttachedPhoto();
    cancelReply();

    // Append optimistic message bubble with pending CLOCK icon (🕒)
    const stream = document.getElementById('chat-stream-container') || document.getElementById('chat-stream-box');
    const scrollBox = document.getElementById('chat-stream-box');
    const tempRowId = 'temp-row-' + Date.now();
    let localMediaPreviewUrl = null;

    if (stream) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeStr = `${hours}:${minutes} WIB`;

        const bubble = document.createElement('div');
        bubble.className = 'flex justify-end items-start gap-1.5 sm:gap-2 my-1 group/bubble relative animate-message-in';
        bubble.id = tempRowId;
        
        let mediaNotice = '';
        const attachedPhotoFile = (mediaInput && mediaInput.files && mediaInput.files[0]) || null;
        const attachedVideoFile = (videoInput && videoInput.files && videoInput.files[0]) || null;

        if (attachedPhotoFile && attachedPhotoFile.type && attachedPhotoFile.type.startsWith('image/')) {
            try {
                localMediaPreviewUrl = URL.createObjectURL(attachedPhotoFile);
            } catch (e) {}

            mediaNotice = `
                <div class="chat-media-preview-container pt-0.5">
                    <div style="width: 190px; max-width: 100%; border-radius: 10px; overflow: hidden; border: 1px solid #E2E8F0; display: block;"
                         class="block group relative shadow-2xs bg-slate-100 text-left">
                        <img src="${localMediaPreviewUrl}"
                             alt="Bukti Foto"
                             style="width: 100%; height: 118px; object-fit: cover; display: block;">
                        <div class="upload-badge absolute bottom-1.5 right-1.5 bg-black/65 backdrop-blur-xs text-white text-[9.5px] font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
                            <svg class="w-3 h-3 animate-spin text-teal-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Mengunggah...</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (hasMedia) {
            mediaNotice = `<div class="chat-media-preview-container p-1.5 rounded-lg bg-sky-100/70 text-sky-800 text-[10.5px] font-semibold flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-sky-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                <span>Mengunggah berkas media...</span>
            </div>`;
        }

        let locNotice = '';
        if (lat && lng) {
            locNotice = `<div class="p-1.5 rounded-lg bg-rose-50 border border-rose-100 text-rose-700 text-[10.5px] font-semibold flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                <span>Lokasi: ${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}</span>
            </div>`;
        }

        let replyBox = '';
        if (replySender) {
            replyBox = `
                <div style="background-color: rgba(13, 148, 136, 0.09); border-left: 3.5px solid #0D9488; border-radius: 7px;"
                     class="px-2.5 py-1.5 mb-1 select-none overflow-hidden text-left ${replyId ? 'cursor-pointer hover:brightness-95' : ''} transition group/quote"
                     ${replyId ? `onclick="scrollToChronology(${replyId})" title="Klik untuk menuju pesan asli"` : ''}>
                    <div class="font-bold text-[10.5px] leading-tight truncate" style="color: #0F766E;">
                        ${escapeChatHtml(replySender)}
                    </div>
                    ${replySnippet ? `<p class="text-slate-600 text-[10.5px] line-clamp-2 mt-0.5 leading-snug break-words">${escapeChatHtml(replySnippet)}</p>` : ''}
                </div>
            `;
        }

        const selfAvatarHtml = `
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden shrink-0 border border-teal-200/80 shadow-2xs flex items-center justify-center bg-gradient-to-br from-teal-500 to-teal-700 text-white select-none mt-0.5"
                 title="Anda">
                ${authAvatarUrl ? `<img src="${authAvatarUrl}" alt="Anda" class="w-full h-full object-cover" onerror="this.remove()">` : `<span class="text-[10px] sm:text-[11px] font-bold text-white">${authInitials}</span>`}
            </div>
        `;

        const selfBadgeStyle = getDivisionBadgeStyle(authUserBadge);

        bubble.innerHTML = `
            <div style="background-color: #EAF4FE; border: 1px solid #CFE4FC; box-shadow: 0 1px 2px rgba(0,0,0,0.05); max-width: min(80%, 380px); min-width: 140px;"
                 class="rounded-xl rounded-tr-xs px-2.5 py-1.5 sm:px-3 sm:py-2 text-xs space-y-1 relative new-chat-highlight-self">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="font-bold text-[11px] text-teal-950 truncate">Anda</span>
                        <span style="background-color: ${selfBadgeStyle.bg}; color: ${selfBadgeStyle.text}; border: 1px solid ${selfBadgeStyle.border};" class="px-1.5 py-0.5 rounded-full text-[8.5px] font-bold tracking-wide shrink-0 leading-none shadow-2xs">
                            ${escapeChatHtml(authUserBadge)}
                        </span>
                    </div>
                    <div class="relative shrink-0 msg-menu-container">
                        <button type="button"
                                class="w-6 h-6 rounded-md text-slate-500 hover:text-slate-800 hover:bg-black/5 flex items-center justify-center transition cursor-pointer"
                                title="Pilihan pesan">
                            <svg class="w-4 h-4 text-slate-600 hover:text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                ${replyBox}
                ${msg ? `<p class="text-slate-800 leading-snug whitespace-pre-line text-[11.5px] font-normal">${escapeChatHtml(msg)}</p>` : ''}
                ${mediaNotice}
                ${locNotice}
                <div class="flex justify-end items-center gap-1 text-[9.5px] text-slate-400 font-mono pt-0.5 select-none leading-none">
                    <span>${timeStr}</span>
                    <span class="status-indicator inline-flex items-center text-slate-400 ml-0.5" title="Mengirim...">
                        <svg class="w-3 h-3 text-slate-400 shrink-0 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/>
                        </svg>
                    </span>
                </div>
            </div>
            ${selfAvatarHtml}
        `;
        ensureChatDateDivider();
        stream.appendChild(bubble);
        if (scrollBox) {
            scrollBox.scrollTop = scrollBox.scrollHeight;
        }
    }

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            const errMsg = errData.message || (res.status === 403 ? 'Anda tidak memiliki hak akses untuk mengirim pesan pada tiket ini (AC-05: Khusus teknisi tim penanganan / PIC).' : (res.status === 419 ? 'Sesi login telah kedaluwarsa, silakan muat ulang halaman.' : `Gagal mengirim pesan (HTTP ${res.status})`));
            throw new Error(errMsg);
        }

        const data = await res.json();
        const chron = data.chronology;

        if (chron && chron.id) {
            highestChronologyId = Math.max(highestChronologyId, chron.id);
        }

        dismissOverdueAlert();

        // Transition pending clock icon -> double grey checkmark (Terkirim) like WhatsApp
        const tempBubbleRow = document.getElementById(tempRowId);
        if (tempBubbleRow && chron) {
            tempBubbleRow.id = 'chron-row-' + chron.id;
            const bubbleBox = tempBubbleRow.firstElementChild;
            if (bubbleBox) {
                bubbleBox.id = 'chron-bubble-' + chron.id;
                bubbleBox.dataset.id = chron.id;
                bubbleBox.dataset.sender = 'Anda';
                bubbleBox.dataset.realSender = @json(auth()->user()?->name ?? 'Anda');
                bubbleBox.dataset.role = data.sender_badge || authUserBadge;
                bubbleBox.dataset.message = msg || (chron.message || '');
                bubbleBox.dataset.time = (chron.created_at || '') + ' WIB';
                bubbleBox.dataset.read = 'Belum dibaca';
                bubbleBox.dataset.lat = chron.latitude || '';
                bubbleBox.dataset.lng = chron.longitude || '';
                const finalPhotoUrl = data.photo_url || (chron.photo_path ? `/storage/${chron.photo_path}` : (localMediaPreviewUrl || ''));
                bubbleBox.dataset.photo = finalPhotoUrl;
                bubbleBox.dataset.category = chron.category || 'LAIN';

                // Langsung update container media: hilangkan spinner loading dan jadikan foto permanen interaktif
                const mediaContainer = bubbleBox.querySelector('.chat-media-preview-container');
                if (mediaContainer) {
                    if (finalPhotoUrl) {
                        const isPdfFile = finalPhotoUrl.toLowerCase().endsWith('.pdf') || (chron.photo_path && chron.photo_path.toLowerCase().endsWith('.pdf'));
                        if (isPdfFile) {
                            mediaContainer.innerHTML = `
                                <a href="${finalPhotoUrl}" target="_blank" rel="noopener noreferrer"
                                   style="width: 210px; max-width: 100%; border-radius: 10px; border: 1px solid #E2E8F0;"
                                   class="flex items-center gap-2.5 p-2 bg-rose-50/70 hover:bg-rose-100/80 border-rose-200 text-rose-800 transition rounded-xl group shadow-2xs">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="truncate flex-1">
                                        <span class="block text-xs font-bold text-slate-800 truncate">${chron.photo_path ? chron.photo_path.split('/').pop() : 'Dokumen.pdf'}</span>
                                        <span class="block text-[10px] text-rose-600 font-semibold group-hover:underline">Buka PDF &nearr;</span>
                                    </div>
                                </a>
                            `;
                        } else {
                            mediaContainer.innerHTML = `
                                <button type="button"
                                        onclick="openImageLightbox('${finalPhotoUrl}', 'Foto Dokumentasi - Anda')"
                                        style="width: 190px; max-width: 100%; border-radius: 10px; overflow: hidden; border: 1px solid #E2E8F0; display: block;"
                                        class="block group relative shadow-2xs bg-slate-100 cursor-pointer text-left">
                                    <img src="${finalPhotoUrl}"
                                         alt="Bukti Foto"
                                         loading="lazy"
                                         style="width: 100%; height: 118px; object-fit: cover; display: block;"
                                         class="group-hover:scale-105 transition duration-200">
                                    <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-[11px] gap-1 backdrop-blur-[1px]">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        <span>Perbesar &rarr;</span>
                                    </div>
                                </button>
                            `;
                        }
                    } else if (data.video_url) {
                        mediaContainer.innerHTML = `
                            <div class="pt-0.5" style="width: 220px; max-width: 100%;">
                                <video controls class="rounded-xl border border-slate-200 w-full" style="max-height: 160px;">
                                    <source src="${data.video_url}" type="video/mp4">
                                </video>
                            </div>
                        `;
                    } else {
                        mediaContainer.remove();
                    }
                }

                const statusSpan = bubbleBox.querySelector('.status-indicator');
                if (statusSpan) {
                    statusSpan.title = 'Terkirim';
                    statusSpan.className = 'status-indicator inline-flex items-center text-slate-400 ml-0.5';
                    statusSpan.innerHTML = `
                        <svg class="w-3.5 h-3 text-slate-400" viewBox="0 0 16 11" fill="none">
                            <path d="M11 1.5L4.5 8L1.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5 1.5L8 8L6.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    `;
                }

                // Populate 3-Dots dropdown menu with real message ID
                const menuContainer = bubbleBox.querySelector('.msg-menu-container');
                if (menuContainer) {
                    menuContainer.innerHTML = `
                        <button type="button"
                                onclick="toggleMessageMenu(${chron.id}, event)"
                                class="w-6 h-6 rounded-md text-slate-500 hover:text-slate-800 hover:bg-black/5 flex items-center justify-center transition cursor-pointer active:scale-95"
                                title="Pilihan pesan">
                            <svg class="w-4 h-4 text-slate-600 hover:text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </button>
                        <div id="msg-dropdown-${chron.id}"
                             style="bottom: calc(100% + 6px); top: auto; right: 0;"
                             class="msg-action-dropdown hidden absolute right-0 w-52 sm:w-56 rounded-2xl bg-white/95 backdrop-blur-md shadow-[0_12px_32px_rgba(15,23,42,0.16)] border border-slate-200/80 ring-1 ring-slate-900/5 p-1.5 z-50 select-none divide-y divide-slate-100">
                            <div class="space-y-0.5 pb-1">
                                <button type="button" onclick="copyMessageText(${chron.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                    <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    </span>
                                    <span>Salin</span>
                                </button>
                                <button type="button" onclick="openMessageInfo(${chron.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                    <span class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                                    </span>
                                    <span>Info Pesan</span>
                                </button>
                                <button type="button" onclick="saveToDocumentation(${chron.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                    </span>
                                    <span class="truncate">Simpan Dokumentasi</span>
                                </button>
                                <button type="button" onclick="replyToMessage(${chron.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                    <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/></svg>
                                    </span>
                                    <span>Balas</span>
                                </button>
                                <button type="button" onclick="editMessage(${chron.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-amber-50/70 hover:text-amber-900 transition-all duration-150 cursor-pointer text-left">
                                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </span>
                                    <span>Edit Pesan</span>
                                </button>
                            </div>
                            <div class="pt-1">
                                <button type="button" onclick="deleteMessage(${chron.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-all duration-150 cursor-pointer text-left">
                                    <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </span>
                                    <span>Hapus Pesan</span>
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        }
        if (scrollBox) {
            scrollBox.scrollTop = scrollBox.scrollHeight;
        }
    } catch (err) {
        console.error('Chat submit error:', err);
        const tempBubbleRow = document.getElementById(tempRowId);
        if (tempBubbleRow) {
            const statusSpan = tempBubbleRow.querySelector('.status-indicator');
            if (statusSpan) {
                statusSpan.title = 'Gagal terkirim: ' + err.message;
                statusSpan.className = 'status-indicator inline-flex items-center text-rose-500 ml-0.5 font-bold cursor-pointer';
                statusSpan.onclick = () => alert('Pesan tidak dapat terkirim:\n' + err.message);
                statusSpan.innerHTML = `⚠️`;
            }
        }
        // Tampilkan notifikasi error agar user langsung tahu penyebabnya
        alert(err.message || 'Gagal mengirim pesan');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
        if (input && window.innerWidth >= 768) {
            input.focus();
        }
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

// Fast client-side image compression for mobile uploads (< 400KB payload)
async function compressImageIfNeeded(file, maxDimension = 1600, quality = 0.82) {
    if (!file || !file.type || !file.type.startsWith('image/') || file.type === 'image/gif') {
        return file;
    }
    // Jika ukuran file sudah di bawah 450 KB, tidak perlu kompres lagi
    if (file.size <= 450 * 1024) {
        return file;
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;
                if (width > maxDimension || height > maxDimension) {
                    if (width > height) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (blob && blob.size < file.size) {
                            const cleanName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                            const compressedFile = new File([blob], cleanName, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            console.log(`[Photo Optimizer] Compressed: ${(file.size/1024).toFixed(0)}KB -> ${(compressedFile.size/1024).toFixed(0)}KB`);
                            resolve(compressedFile);
                        } else {
                            resolve(file);
                        }
                    },
                    'image/jpeg',
                    quality
                );
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

// Handlers for WhatsApp-style attachment menu options
async function handleFileChosen(input, typeName) {
    const label = document.getElementById('attachment-name');
    const photoChip = document.getElementById('photo-attached-preview');
    const photoText = document.getElementById('photo-preview-text');
    const mediaInput = document.getElementById('input_upload_media');

    if (input.files && input.files.length > 0) {
        let file = input.files[0];
        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

        if (file.type && file.type.startsWith('image/')) {
            if (label) label.innerText = '⚙️ Mengoptimalkan foto...';
            if (photoText) photoText.innerText = '⚙️ Mengompres foto...';
            if (photoChip) photoChip.classList.remove('hidden');
            file = await compressImageIfNeeded(file);
        }

        // Transfer compressed file into the actual form input
        const dt = new DataTransfer();
        dt.items.add(file);
        if (mediaInput) {
            mediaInput.files = dt.files;
        }

        const sizeKb = Math.round(file.size / 1024);
        if (label) label.innerText = (isPdf ? '📄 Dokumen PDF' : '📎 ' + typeName) + ' (' + sizeKb + ' KB)';
        if (photoText) photoText.innerText = (isPdf ? '📄 PDF: ' : '📷 ' + typeName + ': ') + file.name + ' (' + sizeKb + ' KB)';
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
    const radius = size * 0.14;

    const n1x = cx - size * 0.12;
    const n1y = cy - size * 0.22;
    const n2x = cx - size * 0.32;
    const n2y = cy + size * 0.32;
    const n3x = cx + size * 0.34;
    const n3y = cy - size * 0.02;

    ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
    ctx.shadowBlur = 6;
    ctx.shadowOffsetX = 0;
    ctx.shadowOffsetY = 2;

    // Connecting cyan lines
    ctx.strokeStyle = '#0EA5E9';
    ctx.lineWidth = Math.max(3, size * 0.08);
    ctx.beginPath();
    ctx.moveTo(n2x, n2y);
    ctx.lineTo(n1x, n1y);
    ctx.lineTo(n3x, n3y);
    ctx.stroke();

    // Draw nodes
    [ [n1x, n1y, '#0284C7'], [n2x, n2y, '#0369A1'], [n3x, n3y, '#38BDF8'] ].forEach(([nx, ny, col]) => {
        ctx.beginPath();
        ctx.arc(nx, ny, radius, 0, Math.PI * 2);
        ctx.fillStyle = col;
        ctx.fill();
        ctx.strokeStyle = '#FFFFFF';
        ctx.lineWidth = Math.max(2, size * 0.04);
        ctx.stroke();
    });

    ctx.restore();
}

function processGpsWatermark(fileInput) {
    if (!fileInput.files || fileInput.files.length === 0) return;
    const file = fileInput.files[0];
    const reader = new FileReader();

    const label = document.getElementById('attachment-name');
    const photoChip = document.getElementById('photo-attached-preview');
    const photoText = document.getElementById('photo-preview-text');
    if (label) label.innerText = '📸 Memproses Watermark GPS...';
    if (photoText) photoText.innerText = '📸 Menyiapkan Watermark GPS...';
    if (photoChip) photoChip.classList.remove('hidden');

    reader.onload = function(e) {
        const img = new Image();
        img.onload = async function() {
            // Skala foto jika lebih besar dari 1600px agar upload di HP sangat cepat (< 350 KB)
            let targetWidth = img.width;
            let targetHeight = img.height;
            const maxDim = 1600;
            if (targetWidth > maxDim || targetHeight > maxDim) {
                if (targetWidth > targetHeight) {
                    targetHeight = Math.round((targetHeight * maxDim) / targetWidth);
                    targetWidth = maxDim;
                } else {
                    targetWidth = Math.round((targetWidth * maxDim) / targetHeight);
                    targetHeight = maxDim;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            const ctx = canvas.getContext('2d');

            // 1. Draw original photo scaled
            ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

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
            const fontSize = Math.max(18, Math.round(targetWidth * 0.032));
            const lineHeight = Math.round(fontSize * 1.4);
            const marginX = Math.round(targetWidth * 0.045);
            const marginY = Math.round(targetHeight * 0.045);

            ctx.font = '700 ' + fontSize + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'alphabetic';

            const lines = [dateStr];
            if (coordStr) lines.push(coordStr);
            addressLines.forEach(l => lines.push(l));

            const totalHeight = (lines.length - 1) * lineHeight;
            const startY = targetHeight - marginY - totalHeight;

            lines.forEach((line, idx) => {
                const lineY = startY + (idx * lineHeight);
                ctx.save();
                ctx.shadowColor = 'rgba(0, 0, 0, 0.85)';
                ctx.shadowBlur = Math.round(fontSize * 0.35);
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = Math.round(fontSize * 0.1);

                ctx.strokeStyle = 'rgba(0, 0, 0, 0.9)';
                ctx.lineWidth = Math.max(3, Math.round(fontSize * 0.18));
                ctx.lineJoin = 'round';
                ctx.strokeText(line, targetWidth - marginX, lineY);

                // Crisp pure white text
                ctx.fillStyle = '#FFFFFF';
                ctx.fillText(line, targetWidth - marginX, lineY);
                ctx.restore();
            });

@php
    $msnLogoBase64 = '';
    $logoFile = public_path('assets/logo/logo-msn2.png');
    if (file_exists($logoFile)) {
        $msnLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile));
    }
@endphp
            // 4. Draw Official MSN Logo on top-right (Base64 embedded for instant, zero-CORS rendering with natural aspect ratio)
            const logoWidth = Math.max(90, Math.round(targetWidth * 0.13));
            const logoImg = new Image();
            logoImg.onload = function() {
                const aspect = (logoImg.naturalHeight || 399) / (logoImg.naturalWidth || 489);
                const logoHeight = Math.round(logoWidth * aspect);
                const logoX = targetWidth - marginX - logoWidth;
                const logoY = marginY;

                ctx.save();
                ctx.shadowColor = 'rgba(0, 0, 0, 0.45)';
                ctx.shadowBlur = Math.round(logoWidth * 0.06);
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = Math.round(logoWidth * 0.02);
                ctx.drawImage(logoImg, logoX, logoY, logoWidth, logoHeight);
                ctx.restore();
                finalizeWatermarkedCanvas();
            };
            logoImg.onerror = function() {
                // Fallback to stylized network icon if image load fails
                drawWatermarkNetworkIcon(ctx, targetWidth - marginX, marginY, logoWidth);
                finalizeWatermarkedCanvas();
            };
            const logoBase64 = @json($msnLogoBase64);
            logoImg.src = logoBase64 || '{{ asset('assets/logo/logo-msn2.png') }}';

            function finalizeWatermarkedCanvas() {
                // 5. Convert canvas to Blob & assign to input (0.84 quality for lightweight super-fast upload)
                canvas.toBlob(function(blob) {
                    const cleanName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                    const watermarkedFile = new File([blob], 'GPS_' + cleanName, { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(watermarkedFile);
                    const mediaInput = document.getElementById('input_upload_media');
                    if (mediaInput) {
                        mediaInput.files = dataTransfer.files;
                    }
                    const sizeKb = Math.round(blob.size / 1024);
                    const label = document.getElementById('attachment-name');
                    if (label) label.innerText = '📸 Watermark GPS Siap (' + sizeKb + ' KB)';

                    const photoChip = document.getElementById('photo-attached-preview');
                    const photoText = document.getElementById('photo-preview-text');
                    if (photoText) photoText.innerText = '📸 Watermark GPS: ' + cleanName + ' (' + sizeKb + ' KB)';
                    if (photoChip) photoChip.classList.remove('hidden');
                }, 'image/jpeg', 0.84);
            }
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
    if (previewText) previewText.innerText = 'Mengambil koordinat GPS...';
    if (preview) preview.classList.remove('hidden');
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

// =========================================================================
// VOICE-TO-TEXT SPEECH RECOGNITION (Input Suara Jadi Teks)
// =========================================================================
let speechRecognizer = null;
let isRecordingVoice = false;
let voiceInitialText = '';

function scrollChatInputToEnd(inputEl) {
    if (!inputEl) inputEl = document.getElementById('quick-chat-input');
    if (!inputEl) return;
    try {
        const len = inputEl.value.length;
        inputEl.setSelectionRange(len, len);
    } catch (e) {}
    inputEl.scrollLeft = inputEl.scrollWidth;
    setTimeout(() => {
        if (inputEl) {
            inputEl.scrollLeft = inputEl.scrollWidth;
            try {
                const len = inputEl.value.length;
                inputEl.setSelectionRange(len, len);
            } catch (e) {}
        }
    }, 15);
}

function toggleVoiceRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Browser Tidak Mendukung',
                text: 'Browser Anda belum mendukung fitur Voice-to-Text (Speech Recognition). Silakan gunakan Google Chrome atau browser berbasis Chromium.',
                confirmButtonColor: '#155E63'
            });
        } else {
            alert('Browser Anda belum mendukung fitur Voice-to-Text. Silakan gunakan Google Chrome.');
        }
        return;
    }

    if (isRecordingVoice) {
        stopVoiceRecognition();
    } else {
        startVoiceRecognition();
    }
}

function startVoiceRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) return;

    if (speechRecognizer) {
        try { speechRecognizer.abort(); } catch (e) {}
        speechRecognizer = null;
    }

    try {
        speechRecognizer = new SpeechRecognition();
        speechRecognizer.lang = 'id-ID'; // Bahasa Indonesia
        speechRecognizer.interimResults = true; // Real-time responsif instan saat berbicara
        speechRecognizer.continuous = true; // Terus mendengarkan tanpa putus saat jeda napas
        speechRecognizer.maxAlternatives = 1;

        const micBtn = document.getElementById('btn-voice-to-text');
        const micNormal = document.getElementById('mic-icon-normal');
        const micPulse = document.getElementById('mic-pulse-indicator');
        const micDot = document.getElementById('mic-dot-indicator');
        const chatInput = document.getElementById('quick-chat-input');

        voiceInitialText = (chatInput ? chatInput.value : '');

        // Umpan balik visual instan tanpa delay
        isRecordingVoice = true;
        if (micBtn) {
            micBtn.classList.remove('text-slate-600', 'bg-white');
            micBtn.classList.add('bg-rose-50', 'text-rose-600', 'border-rose-300', 'is-recording');
        }
        if (micNormal) {
            micNormal.classList.remove('text-slate-600');
            micNormal.classList.add('text-rose-600', 'animate-pulse');
        }
        if (micPulse) micPulse.classList.remove('hidden');
        if (micDot) micDot.classList.remove('hidden');

        if (chatInput) {
            chatInput.placeholder = '🎙️ Mendengarkan... Bicara sekarang (otomatis tertulis)';
            chatInput.focus();
        }

        speechRecognizer.onstart = function() {
            isRecordingVoice = true;
        };

        speechRecognizer.onresult = function(event) {
            if (!isRecordingVoice) return;
            let finalAccumulated = '';
            let interimAccumulated = '';

            for (let i = 0; i < event.results.length; ++i) {
                const res = event.results[i];
                if (res.isFinal) {
                    finalAccumulated += res[0].transcript + ' ';
                } else {
                    interimAccumulated += res[0].transcript;
                }
            }

            if (!isRecordingVoice) return;

            const currentSpoken = (finalAccumulated + interimAccumulated).trim();
            if (chatInput && isRecordingVoice) {
                const prefix = voiceInitialText.trim() ? voiceInitialText.trim() + ' ' : '';
                chatInput.value = prefix + currentSpoken;
                chatInput.dispatchEvent(new Event('input', { bubbles: true }));

                // Geser scroll input ke posisi paling kanan agar kata terbaru yang diucapkan selalu terlihat
                scrollChatInputToEnd(chatInput);
            }
        };

        speechRecognizer.onerror = function(event) {
            console.warn('[Voice-to-Text] Error:', event.error);
            if (event.error === 'no-speech') {
                return; // User belum bicara, jangan dihentikan
            }
            stopVoiceRecognition();
            if (event.error === 'not-allowed' || event.error === 'permission-denied') {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akses Mikrofon Belum Diizinkan',
                        html: `<div class="text-left text-xs leading-relaxed space-y-2.5">
                            <p>Izin mikrofon belum aktif atau diblokir oleh browser / Windows.</p>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 space-y-1.5 mt-2">
                                <p class="font-bold text-slate-900">Langkah Membuka Izin:</p>
                                <ol class="list-decimal pl-4 space-y-1 text-[11px]">
                                    <li>Klik ikon pengaturan/gembok di sebelah kiri address bar (URL) &rarr; ubah <b>Microphone</b> menjadi <b>Allow / Izinkan</b>.</li>
                                    <li>Buka <b>Windows Settings (Win+I)</b> &rarr; <b>Privacy &amp; Security</b> &rarr; <b>Microphone</b> &rarr; Pastikan <b>Microphone access</b> dan <b>Let desktop apps access your microphone</b> aktif (ON).</li>
                                </ol>
                            </div>
                        </div>`,
                        confirmButtonColor: '#155E63'
                    });
                } else {
                    alert('Akses mikrofon diblokir. Pastikan mikrofon diizinkan di browser dan Windows Privacy Settings.');
                }
            } else if (event.error === 'network') {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Suara Terputus',
                        text: 'Layanan Speech Recognition memerlukan koneksi internet aktif ke server Google.',
                        confirmButtonColor: '#155E63'
                    });
                }
            }
        };

        speechRecognizer.onend = function() {
            stopVoiceRecognition();
        };

        speechRecognizer.start();
    } catch (err) {
        console.error('[Voice-to-Text] Failed to start:', err);
        stopVoiceRecognition();
    }
}

function stopVoiceRecognition() {
    isRecordingVoice = false;
    voiceInitialText = '';
    if (speechRecognizer) {
        try {
            speechRecognizer.onresult = null;
            speechRecognizer.onerror = null;
            speechRecognizer.onend = null;
            speechRecognizer.abort();
        } catch (e) {}
        speechRecognizer = null;
    }

    const micBtn = document.getElementById('btn-voice-to-text');
    const micNormal = document.getElementById('mic-icon-normal');
    const micPulse = document.getElementById('mic-pulse-indicator');
    const micDot = document.getElementById('mic-dot-indicator');
    const chatInput = document.getElementById('quick-chat-input');

    if (micBtn) {
        micBtn.classList.remove('bg-rose-50', 'text-rose-600', 'border-rose-300', 'is-recording');
        micBtn.classList.add('text-slate-600', 'bg-white');
    }
    if (micNormal) {
        micNormal.classList.remove('text-rose-600', 'animate-pulse');
        micNormal.classList.add('text-slate-600');
    }
    if (micPulse) micPulse.classList.add('hidden');
    if (micDot) micDot.classList.add('hidden');

    if (chatInput && chatInput.placeholder.includes('Mendengarkan')) {
        chatInput.placeholder = 'Ketik update koordinasi...';
    }
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

function openResumeModal() {
    const m = document.getElementById('modal-resume');
    if (m) m.classList.remove('hidden');
}
function closeResumeModal() {
    const m = document.getElementById('modal-resume');
    if (m) m.classList.add('hidden');
}
function toggleResumeForm() {
    openResumeModal();
}
function resumeFormModal(initialMembers) {
    return {
        teamMembers: Array.isArray(initialMembers) && initialMembers.length > 0 ? [...initialMembers] : ['Ricky'],
        addMember() {
            this.teamMembers.push('');
            this.$nextTick(() => {
                const inputs = this.$el.querySelectorAll('input[name="team_members[]"]');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            });
        },
        removeMember(idx) {
            if (this.teamMembers.length > 1) {
                this.teamMembers.splice(idx, 1);
            } else {
                this.teamMembers[0] = '';
            }
        }
    };
}

// Modal triggers
function openDispatchModal() { document.getElementById('modal-dispatch').classList.remove('hidden'); }
function closeDispatchModal() { document.getElementById('modal-dispatch').classList.add('hidden'); }
function openStopClockModal() { document.getElementById('modal-stopclock').classList.remove('hidden'); }
function closeStopClockModal() { document.getElementById('modal-stopclock').classList.add('hidden'); }
function openJcModal() { 
    document.getElementById('modal-jc').classList.remove('hidden'); 
    window.dispatchEvent(new CustomEvent('jc-modal-opened'));
}
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
    if (label) label.innerText = 'Mencari...';

    const applyLiveCoords = (lat, lng, acc, src) => {
        const accVal = Math.min(9999, Math.max(0, Math.round(acc * 100) / 100));
        const latInput = document.getElementById('chron_lat');
        const lngInput = document.getElementById('chron_lng');
        const accInput = document.getElementById('chron_acc');
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (accInput) accInput.value = accVal;
        if (label) label.innerText = `✓ ${Number(lat).toFixed(3)}, ${Number(lng).toFixed(3)}`;
        if (previewText) previewText.innerText = `GPS: ${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)} (${src})`;
        if (preview) preview.classList.remove('hidden');
        const btn = document.getElementById('gps_action_btn');
        if (btn) {
            btn.classList.add('bg-emerald-500/30', 'text-emerald-200', 'border-emerald-400/40');
        }
    };

    const fallbackIp = () => {
        fetch('https://ipwho.is/').then(r=>r.json()).then(d => {
            if (d && d.success && d.latitude) {
                applyLiveCoords(d.latitude, d.longitude, 1000, 'IP');
            } else {
                alert('Gagal mendeteksi lokasi GPS atau Jaringan.');
                if (label) label.innerText = 'GPS';
            }
        }).catch(() => {
            alert('Gagal mendeteksi lokasi.');
            if (label) label.innerText = 'GPS';
        });
    };

    if (!navigator.geolocation) {
        fallbackIp();
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => applyLiveCoords(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy || 10, 'GPS Satelit'),
        () => {
            navigator.geolocation.getCurrentPosition(
                (fallbackPos) => applyLiveCoords(fallbackPos.coords.latitude, fallbackPos.coords.longitude, fallbackPos.coords.accuracy || 50, 'Jaringan/Wi-Fi'),
                () => fallbackIp(),
                { enableHighAccuracy: false, timeout: 8000 }
            );
        },
        { enableHighAccuracy: true, timeout: 5000 }
    );
}

// Toggle Chat Fullscreen Mode (Zoom Full Size) with Smooth In-Place Transition
let isChatFullscreenTransitioning = false;
let savedChatScrollY = null;

function toggleChatFullscreen() {
    if (isChatFullscreenTransitioning) return;

    const chatCard = document.getElementById('ticket-chat-container') || document.querySelector('.ticket-chat-card');
    if (!chatCard) return;

    const expandIcon = document.getElementById('icon-fs-expand');
    const minimizeIcon = document.getElementById('icon-fs-minimize');
    const fsLabel = document.getElementById('fs_label');
    const backdrop = document.getElementById('chat-fs-backdrop');
    const placeholder = document.getElementById('chat-fs-placeholder');

    const willBeFullscreen = !chatCard.classList.contains('is-chat-fullscreen');

    if (willBeFullscreen) {
        isChatFullscreenTransitioning = true;

        // 1. Simpan posisi scroll vertikal chat sebelum fullscreen & pasang placeholder
        const offset = window.innerWidth < 768 ? 65 : 80;
        const currentRect = chatCard.getBoundingClientRect();
        const currentScroll = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
        savedChatScrollY = Math.max(0, currentRect.top + currentScroll - offset);

        if (placeholder) {
            placeholder.style.height = (chatCard.offsetHeight || 500) + 'px';
            placeholder.style.width = '100%';
            placeholder.classList.remove('hidden');
        }

        // 2. Tampilkan backdrop dengan smooth fade-in
        if (backdrop) {
            backdrop.classList.remove('hidden');
            requestAnimationFrame(() => {
                backdrop.classList.add('is-visible');
            });
        }

        // 3. Masuk ke mode fullscreen
        chatCard.classList.remove('chat-fs-exiting', 'chat-fs-settling');
        chatCard.classList.add('is-chat-fullscreen', 'chat-fs-entering');
        chatCard.style.height = '100dvh';
        document.body.style.overflow = 'hidden';

        // 4. Update button UI
        if (expandIcon) expandIcon.classList.add('hidden');
        if (minimizeIcon) {
            minimizeIcon.classList.remove('hidden');
            minimizeIcon.classList.add('rotate-90');
            requestAnimationFrame(() => minimizeIcon.classList.remove('rotate-90'));
        }
        if (fsLabel) fsLabel.innerText = 'Kecilkan';

        // 5. Bersihkan class animasi masuk
        setTimeout(() => {
            chatCard.classList.remove('chat-fs-entering');
            isChatFullscreenTransitioning = false;
        }, 280);
    } else {
        isChatFullscreenTransitioning = true;

        // 1. Kunci posisi scroll halaman agar sudah langsung berada di posisi chat (tidak ke atas)
        if (typeof savedChatScrollY === 'number' && savedChatScrollY >= 0) {
            window.scrollTo({ top: savedChatScrollY, behavior: 'instant' });
        }

        // 2. Animasi fade-out backdrop
        if (backdrop) {
            backdrop.classList.remove('is-visible');
        }

        // 3. Animasi keluar halus
        chatCard.classList.remove('chat-fs-entering');
        chatCard.classList.add('chat-fs-exiting');

        // 4. Update button UI
        if (expandIcon) {
            expandIcon.classList.remove('hidden');
            expandIcon.classList.add('scale-75');
            requestAnimationFrame(() => expandIcon.classList.remove('scale-75'));
        }
        if (minimizeIcon) minimizeIcon.classList.add('hidden');
        if (fsLabel) fsLabel.innerText = 'Full Size';

        // 5. Selesai animasi keluar: langsung berada di posisi chat tanpa scroll dari atas ke bawah
        setTimeout(() => {
            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            chatCard.classList.remove('is-chat-fullscreen', 'chat-fs-exiting');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.style.overflow = '';
            adjustChatHeight();

            // Settle halus pada posisi container
            chatCard.classList.add('chat-fs-settling');
            setTimeout(() => {
                chatCard.classList.remove('chat-fs-settling');
            }, 200);

            // Kunci viewport secara instan di posisi chatCard (tidak meloncat ke atas baru ke bawah)
            if (typeof savedChatScrollY === 'number' && savedChatScrollY >= 0) {
                window.scrollTo({ top: savedChatScrollY, behavior: 'instant' });
            } else {
                chatCard.scrollIntoView({ behavior: 'instant', block: 'start' });
            }

            isChatFullscreenTransitioning = false;
        }, 220);
    }

    const chatBox = document.getElementById('chat-stream-box');
    if (chatBox) {
        requestAnimationFrame(() => {
            chatBox.scrollTop = chatBox.scrollHeight;
        });
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
        // On mobile, use window.innerHeight to prevent jarring height shrink/expand when soft keyboard opens/closes
        const windowHeight = isDesktop 
            ? (window.visualViewport ? window.visualViewport.height : window.innerHeight)
            : window.innerHeight;

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
        chatBox.addEventListener('scroll', handleChatScrollPosition, { passive: true });
    }
    initLiveChatSSE();
});

function isChatNearBottom(scrollBox, threshold = 60) {
    if (!scrollBox) return true;
    const distance = scrollBox.scrollHeight - scrollBox.scrollTop - scrollBox.clientHeight;
    return distance <= threshold;
}

function handleChatScrollPosition() {
    const scrollBox = document.getElementById('chat-stream-box');
    const btn = document.getElementById('btn-scroll-to-bottom');
    const newBadge = document.getElementById('new-msg-badge');
    if (!scrollBox || !btn) return;

    if (isChatNearBottom(scrollBox, 60)) {
        btn.style.display = 'none';
        if (newBadge) newBadge.classList.add('hidden');
    } else {
        btn.style.display = 'inline-flex';
    }
}

function scrollToLatestChat() {
    const scrollBox = document.getElementById('chat-stream-box');
    const btn = document.getElementById('btn-scroll-to-bottom');
    const newBadge = document.getElementById('new-msg-badge');
    if (scrollBox) {
        scrollBox.scrollTo({
            top: scrollBox.scrollHeight,
            behavior: 'smooth'
        });
    }
    if (btn) {
        btn.style.display = 'none';
    }
    if (newBadge) {
        newBadge.classList.add('hidden');
    }
}

// Real-Time Chat Synchronization via Server-Sent Events (SSE) with Polling Fallback
let chatEventSource = null;
var highestChronologyId = {{ $tiket->chronologies->max('id') ?? 0 }};
let sseReconnectTimer = null;
let pollTimer = null;

function initLiveChatSSE() {
    startSSEStream();
    // Safety polling fallback every 4 seconds in case SSE is blocked by proxy/server configuration
    if (!pollTimer) {
        pollTimer = setInterval(pollLatestMessages, 4000);
    }
}

function startSSEStream() {
    if (!window.EventSource) return;

    if (chatEventSource) {
        try { chatEventSource.close(); } catch (e) {}
    }

    const streamUrl = '{{ route('tickets.chronologies.stream', $tiket) }}?last_id=' + highestChronologyId;
    chatEventSource = new EventSource(streamUrl);

    chatEventSource.addEventListener('new_message', (e) => {
        try {
            const data = JSON.parse(e.data);
            if (!data || !data.id) return;
            handleIncomingLivePayload(data);
        } catch (err) {
            console.error('SSE Message parsing error:', err);
        }
    });

    chatEventSource.onerror = () => {
        try { chatEventSource.close(); } catch (e) {}
        if (!sseReconnectTimer) {
            sseReconnectTimer = setTimeout(() => {
                sseReconnectTimer = null;
                startSSEStream();
            }, 3000);
        }
    };
}

async function pollLatestMessages() {
    try {
        const url = '{{ route('tickets.chronologies.poll', $tiket) }}?last_id=' + highestChronologyId;
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (res.ok) {
            const data = await res.json();
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    handleIncomingLivePayload(msg);
                });
            }

            // Real-time update read receipts (double blue checkmarks) for self messages read by others
            if (data.max_read_self_id && data.max_read_self_id > 0) {
                updateReadReceiptsUpTo(data.max_read_self_id);
            }
        }
    } catch (e) {
        // Silent fail on background poll
    }
}

function updateReadReceiptsUpTo(maxReadId) {
    const bubbles = document.querySelectorAll('[id^="chron-bubble-"]');
    bubbles.forEach(bubble => {
        const msgId = parseInt(bubble.dataset.id, 10);
        const sender = bubble.dataset.sender;
        if (sender === 'Anda' && msgId <= maxReadId) {
            bubble.dataset.read = 'Sudah dibaca';
            const statusSpan = bubble.querySelector('.status-indicator');
            if (statusSpan && statusSpan.dataset.status !== 'read') {
                statusSpan.dataset.status = 'read';
                statusSpan.title = 'Dibaca';
                statusSpan.className = 'status-indicator inline-flex items-center text-[#53bdeb] ml-0.5';
                statusSpan.innerHTML = `
                    <svg class="w-3.5 h-3 text-[#53bdeb]" viewBox="0 0 16 11" fill="none">
                        <path d="M11 1.5L4.5 8L1.5 5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.5 1.5L8 8L6.5 6.5" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `;
            }
        }
    });
}

function dismissOverdueAlert() {
    const alertBox = document.getElementById('overdue-interval-alert');
    if (alertBox) {
        alertBox.style.opacity = '0';
        alertBox.style.transform = 'translateY(-8px)';
        setTimeout(() => {
            alertBox.remove();
        }, 300);
    }
}

function handleIncomingLivePayload(data) {
    if (!data || !data.id) return;
    highestChronologyId = Math.max(highestChronologyId, data.id);
    dismissOverdueAlert();

    // If message already rendered (e.g. self optimistic message), ignore
    if (document.getElementById('chron-row-' + data.id)) {
        return;
    }

    renderIncomingLiveMessage(data);
}

function renderIncomingLiveMessage(data) {
    const stream = document.getElementById('chat-stream-container') || document.getElementById('chat-stream-box');
    const scrollBox = document.getElementById('chat-stream-box');
    if (!stream) return;

    // Remove empty chat placeholder if present
    const emptyNotice = stream.querySelector('.py-6');
    if (emptyNotice && emptyNotice.innerText.includes('Belum ada update koordinasi')) {
        emptyNotice.remove();
    }

    const row = document.createElement('div');
    const animClass = data.is_self ? 'animate-message-in' : 'animate-message-in-left';
    row.className = `flex ${data.is_self ? 'justify-end' : 'justify-start'} items-start gap-1.5 sm:gap-2 my-1 group/bubble relative ${animClass}`;
    row.id = 'chron-row-' + data.id;

    const avatarHtml = data.sender_avatar
        ? `<img src="${data.sender_avatar}" alt="${escapeChatHtml(data.sender_name)}" class="w-full h-full object-cover" onerror="this.remove()">`
        : `<span class="text-[10px] sm:text-[11px] font-bold ${data.is_self ? 'text-white' : 'text-slate-700'}">${escapeChatHtml(data.sender_initials || 'U')}</span>`;

    const avatarBox = `
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden shrink-0 border ${data.is_self ? 'border-teal-200/80 bg-gradient-to-br from-teal-500 to-teal-700 text-white' : 'border-slate-200/90 bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700'} shadow-2xs flex items-center justify-center select-none mt-0.5"
             title="${escapeChatHtml(data.sender_name)}">
            ${avatarHtml}
        </div>
    `;

    let resolvedReplySender = data.reply_sender || null;
    let resolvedReplySnippet = data.reply_snippet || null;
    let resolvedCleanMessage = data.message || '';

    // Fallback parsing jika pesan masuk masih berformat [Balas @Sender | Snippet]: Pesan
    if (!resolvedReplySender && resolvedCleanMessage) {
        const replyMatch = resolvedCleanMessage.match(/^\[Balas @([^\]\|]+)(?:\s*\|\s*([^\]]+))?\]:\s*([\s\S]*)$/);
        if (replyMatch) {
            resolvedReplySender = replyMatch[1].trim();
            resolvedReplySnippet = replyMatch[2] ? replyMatch[2].trim() : null;
            resolvedCleanMessage = replyMatch[3].trim();
        }
    } else if (resolvedCleanMessage) {
        const nestedMatch = resolvedCleanMessage.match(/^\[Balas @[^\]]+\]:\s*([\s\S]*)$/);
        if (nestedMatch) {
            resolvedCleanMessage = nestedMatch[1].trim();
        }
    }

    let replyBox = '';
    if (resolvedReplySender) {
        const quoteBg = data.is_self ? 'rgba(13, 148, 136, 0.09)' : '#F1F5F9';
        replyBox = `
            <div style="background-color: ${quoteBg}; border-left: 3.5px solid #0D9488; border-radius: 7px;"
                 class="px-2.5 py-1.5 mb-1 select-none overflow-hidden text-left ${data.reply_to_id ? 'cursor-pointer hover:brightness-95' : ''} transition group/quote"
                 ${data.reply_to_id ? `onclick="scrollToChronology(${data.reply_to_id})" title="Klik untuk menuju pesan asli"` : ''}>
                <div class="font-bold text-[10.5px] leading-tight truncate" style="color: #0F766E;">
                    ${escapeChatHtml(resolvedReplySender)}
                </div>
                ${resolvedReplySnippet ? `<p class="text-slate-600 text-[10.5px] line-clamp-2 mt-0.5 leading-snug break-words">${escapeChatHtml(resolvedReplySnippet)}</p>` : ''}
            </div>
        `;
    }

    let photoBox = '';
    if (data.photo_url) {
        photoBox = `
            <div class="pt-0.5">
                <button type="button"
                        onclick="openImageLightbox('${data.photo_url}', 'Foto Dokumentasi - ${escapeChatHtml(data.sender_name)}')"
                        style="width: 190px; max-width: 100%; border-radius: 10px; overflow: hidden; border: 1px solid #E2E8F0; display: block;"
                        class="block group relative shadow-2xs bg-slate-100 cursor-pointer text-left">
                    <img src="${data.photo_url}"
                         alt="Bukti Foto"
                         loading="lazy"
                         style="width: 100%; height: 118px; object-fit: cover; display: block;"
                         class="group-hover:scale-105 transition duration-200">
                    <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-[11px] gap-1 backdrop-blur-[1px]">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                        <span>Perbesar &rarr;</span>
                    </div>
                </button>
            </div>
        `;
    }

    let videoBox = '';
    if (data.video_url) {
        videoBox = `
            <div class="pt-0.5">
                <video controls
                       style="width: 210px; max-width: 100%; height: 128px; border-radius: 10px; border: 1px solid #E2E8F0; display: block;"
                       class="shadow-2xs">
                    <source src="${data.video_url}" type="video/mp4">
                    Browser Anda tidak mendukung pemutaran video.
                </video>
            </div>
        `;
    }

    let locBox = '';
    if (data.latitude && data.longitude) {
        locBox = `
            <a href="https://maps.google.com/?q=${data.latitude},${data.longitude}" target="_blank"
               title="Buka titik koordinat di Google Maps"
               style="background-color: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);"
               class="block p-1.5 rounded-lg hover:border-rose-300 hover:shadow-xs transition-all group mt-0.5">
                <div class="flex items-center gap-2">
                    <div style="background-color: #FEE2E2; border: 1px solid #FECDD3;"
                         class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-[11px] text-slate-800 group-hover:text-rose-600 transition-colors leading-tight">Lokasi Titik Lapangan</div>
                        <div class="text-[9.5px] font-mono text-slate-500 mt-0.5 truncate tracking-tight">${Number(data.latitude).toFixed(6)}, ${Number(data.longitude).toFixed(6)}</div>
                    </div>
                </div>
            </a>
        `;
    }

    const highlightClass = data.is_self ? 'new-chat-highlight-self' : 'new-chat-highlight-incoming';

    const bubbleBox = `
        <div id="chron-bubble-${data.id}"
             data-id="${data.id}"
             data-sender="${escapeChatHtml(data.sender_name)}"
             data-real-sender="${escapeChatHtml(data.real_sender_name || data.sender_name)}"
             data-role="${escapeChatHtml(data.sender_badge)}"
             data-message="${escapeChatHtml(resolvedCleanMessage)}"
             data-time="${escapeChatHtml(data.date_str || '')}"
             data-read="Belum dibaca"
             data-lat="${data.latitude || ''}"
             data-lng="${data.longitude || ''}"
             data-photo="${data.photo_url || ''}"
             data-category="${escapeChatHtml(data.category || 'LAIN')}"
             style="${data.is_self ? 'background-color: #EAF4FE; border: 1px solid #CFE4FC;' : 'background-color: #FFFFFF; border: 1px solid #E2E8F0;'} box-shadow: 0 1px 2px rgba(0,0,0,0.05); max-width: min(80%, 380px); min-width: 140px;"
             class="rounded-xl px-2.5 py-1.5 sm:px-3 sm:py-2 text-xs space-y-1 ${data.is_self ? 'rounded-tr-xs' : 'rounded-tl-xs'} relative ${highlightClass}">
            
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="font-bold text-[11px] ${data.is_self ? 'text-teal-950' : 'text-slate-800'} truncate">
                        ${escapeChatHtml(data.sender_name)}
                    </span>
                    <span style="${data.is_self ? 'background-color: #D6E8FF; color: #0369A1;' : 'background-color: #F1F5F9; color: #475569;'}"
                          class="px-1.5 py-0.5 rounded-full text-[8.5px] font-semibold tracking-wide shrink-0 leading-none">
                        ${escapeChatHtml(data.sender_badge)}
                    </span>
                </div>

                <div class="relative shrink-0">
                    <button type="button"
                            onclick="toggleMessageMenu(${data.id}, event)"
                            class="w-6 h-6 rounded-md text-slate-500 hover:text-slate-800 hover:bg-black/5 flex items-center justify-center transition cursor-pointer active:scale-95"
                            title="Pilihan pesan">
                        <svg class="w-4 h-4 text-slate-600 hover:text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                    </button>
                    <div id="msg-dropdown-${data.id}"
                         style="bottom: calc(100% + 6px); top: auto; right: 0;"
                         class="msg-action-dropdown hidden absolute right-0 w-52 sm:w-56 rounded-2xl bg-white/95 backdrop-blur-md shadow-[0_12px_32px_rgba(15,23,42,0.16)] border border-slate-200/80 ring-1 ring-slate-900/5 p-1.5 z-50 select-none divide-y divide-slate-100">
                        <div class="space-y-0.5">
                            <button type="button" onclick="copyMessageText(${data.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </span>
                                <span>Salin</span>
                            </button>
                            <button type="button" onclick="openMessageInfo(${data.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                <span class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/></svg>
                                </span>
                                <span>Info Pesan</span>
                            </button>
                            <button type="button" onclick="saveToDocumentation(${data.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                </span>
                                <span class="truncate">Simpan Dokumentasi</span>
                            </button>
                            <button type="button" onclick="replyToMessage(${data.id})" class="group/item w-full flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-[12.5px] font-semibold text-slate-700 hover:bg-teal-50/70 hover:text-teal-900 transition-all duration-150 cursor-pointer text-left">
                                <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover/item:scale-105 transition-transform">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/></svg>
                                </span>
                                <span>Balas</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            ${replyBox}
            ${resolvedCleanMessage ? `<p id="chron-text-${data.id}" class="text-slate-800 leading-snug whitespace-pre-line text-[11.5px] font-normal">${escapeChatHtml(resolvedCleanMessage)}</p>` : ''}
            ${photoBox}
            ${videoBox}
            ${locBox}

            <div class="flex justify-end items-center gap-1 text-[9.5px] text-slate-400 font-mono pt-0.5 select-none leading-none">
                <span>${escapeChatHtml(data.time_str || '')}</span>
                ${data.is_self ? `
                    <span title="${data.is_read ? 'Dibaca' : 'Terkirim'}"
                          class="status-indicator inline-flex items-center ${data.is_read ? 'text-[#53bdeb]' : 'text-slate-400'} ml-0.5"
                          data-status="${data.is_read ? 'read' : 'sent'}">
                        <svg class="w-3.5 h-3 ${data.is_read ? 'text-[#53bdeb]' : 'text-slate-400'}" viewBox="0 0 16 11" fill="none">
                            <path d="M11 1.5L4.5 8L1.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5 1.5L8 8L6.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                ` : ''}
            </div>
        </div>
    `;

    row.innerHTML = data.is_self ? (bubbleBox + avatarBox) : (avatarBox + bubbleBox);

    const wasNearBottom = isChatNearBottom(scrollBox, 80);
    ensureChatDateDivider(data.created_at || null);
    stream.appendChild(row);

    // Auto scroll down smoothly when user is already near bottom or sent the message themselves
    if (scrollBox) {
        if (wasNearBottom || data.is_self) {
            scrollBox.scrollTop = scrollBox.scrollHeight;
        } else {
            const btn = document.getElementById('btn-scroll-to-bottom');
            const newBadge = document.getElementById('new-msg-badge');
            if (btn) {
                btn.style.display = 'inline-flex';
            }
            if (newBadge) {
                newBadge.classList.remove('hidden');
            }
        }
    }
}
</script>
@endpush
@endsection
