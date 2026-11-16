@extends('layouts.app', ['title' => 'Tiketing Gangguan Jaringan (NOC & Lapangan)'])

@section('content')
<div class="space-y-6">
    <!-- Header Banner: Ocean Teal Corporate Theme -->
    <div class="relative overflow-hidden rounded-2xl md:rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-teal-500/20"
         style="background: linear-gradient(135deg, #022836 0%, #053b47 30%, #0a525f 70%, #084c57 100%);">
        <!-- Ambient decorative glows -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-[#18C4C4]/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-teal-400/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-[#5eead4] shadow-inner shrink-0">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#18C4C4]/20 text-[#5eead4] border border-[#18C4C4]/30">
                            Work Report &amp; Ticketing
                        </span>
                        <span class="text-xs text-teal-200/70 font-mono">v3.2 Live SLA</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-heading font-extrabold text-white tracking-tight mt-1">
                        Tiketing Gangguan Jaringan
                    </h1>
                    <p class="text-xs sm:text-sm text-teal-100/80 mt-1 max-w-2xl leading-relaxed">
                        Sistem manajemen terintegrasi gangguan Backbone FO (<span class="font-mono text-[#5eead4] font-semibold">BDG-</span>) &amp; Broadband Maintenance (<span class="font-mono text-[#5eead4] font-semibold">BRD-</span>), pemantauan SLA MTTR, dan penugasan teknisi.
                    </p>
                </div>
            </div>

            <!-- Action Buttons: + Tiket Backbone & + Tiket Broadband -->
            <div class="flex flex-wrap items-center gap-3">
                @can('ticket.create')
                <a href="{{ route('tickets.create', ['category' => 'backbone']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-lg shadow-teal-950/40 border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer"
                   style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tiket Backbone</span>
                </a>

                <a href="{{ route('tickets.create', ['category' => 'broadband']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-lg shadow-emerald-950/40 border border-white/20 bg-emerald-600 hover:bg-emerald-700 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.393 9.929c5.857-5.858 15.355-5.858 21.213 0"/></svg>
                    <span>+ Tiket Broadband</span>
                </a>
                @endcan

                <a href="{{ route('gomsn.analytics.kpi') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-white/90 bg-white/10 hover:bg-white/15 border border-white/20 transition cursor-pointer">
                    <svg class="w-4 h-4 text-[#5eead4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Rekap KPI</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50/90 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-800 font-bold shrink-0">✓</div>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold ml-4">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-50/90 border border-rose-200 text-rose-800 rounded-2xl text-xs sm:text-sm space-y-1 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terjadi Kendala Validasi:</span>
            </div>
            <ul class="list-disc list-inside pl-4 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 4 Metric KPI Cards (Clean White Cards matching image 5) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Tiket -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#0D9488] shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Total Tiket</p>
                <p class="text-xl sm:text-2xl font-heading font-black text-slate-800 tracking-tight mt-0.5">
                    {{ number_format($stats['total'] ?? 0) }}
                </p>
            </div>
        </div>

        <!-- Dalam Penanganan (Open + Proses) -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Dalam Penanganan</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <p class="text-xl sm:text-2xl font-heading font-black text-amber-700 tracking-tight">
                        {{ number_format(($stats['open'] ?? 0) + ($stats['proses'] ?? 0)) }}
                    </p>
                    <span class="text-[11px] text-slate-400 font-medium">({{ $stats['open'] ?? 0 }} open)</span>
                </div>
            </div>
        </div>

        <!-- Pending Verifikasi Closing -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Verifikasi Closing</p>
                <p class="text-xl sm:text-2xl font-heading font-black text-purple-700 tracking-tight mt-0.5">
                    {{ number_format($stats['pending_verifikasi'] ?? 0) }}
                </p>
            </div>
        </div>

        <!-- Selesai & SLA Tepat -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex items-center gap-3.5">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Selesai (Close)</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <p class="text-xl sm:text-2xl font-heading font-black text-emerald-700 tracking-tight">
                        {{ number_format($stats['selesai'] ?? 0) }}
                    </p>
                    <span class="text-[11px] text-emerald-600 font-semibold">({{ $stats['sla_tepat'] ?? 0 }} SLA Tepat)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls (Identical to Image 5) -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-6 shadow-xs space-y-4">
        <form method="GET" action="{{ route('tickets.index') }}" class="space-y-3">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Select Kategori -->
                <div class="min-w-[150px] flex-1 sm:flex-initial">
                    <select name="kategori" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 py-2.5 px-3 font-medium text-slate-700 transition">
                        <option value="">Semua Kategori</option>
                        <option value="BACKBONE" {{ request('kategori') === 'BACKBONE' ? 'selected' : '' }}>Backbone (BDG-)</option>
                        <option value="BROADBAND" {{ request('kategori') === 'BROADBAND' ? 'selected' : '' }}>Broadband (BRD-)</option>
                    </select>
                </div>

                <!-- Select Status -->
                <div class="min-w-[150px] flex-1 sm:flex-initial">
                    <select name="status" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 py-2.5 px-3 font-medium text-slate-700 transition">
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
                <div class="min-w-[130px] flex-1 sm:flex-initial">
                    <select name="priority" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 py-2.5 px-3 font-medium text-slate-700 transition">
                        <option value="">Semua Prioritas</option>
                        <option value="P1" {{ request('priority') === 'P1' ? 'selected' : '' }}>P1 (Kritis)</option>
                        <option value="P2" {{ request('priority') === 'P2' ? 'selected' : '' }}>P2 (Mayor)</option>
                        <option value="P3" {{ request('priority') === 'P3' ? 'selected' : '' }}>P3 (Minor)</option>
                        <option value="P4" {{ request('priority') === 'P4' ? 'selected' : '' }}>P4 (Terencana)</option>
                    </select>
                </div>

                <!-- Select Penugasan (Ditugaskan ke Saya) -->
                <div class="min-w-[140px] flex-1 sm:flex-initial">
                    <select name="assigned" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 py-2.5 px-3 font-medium text-slate-700 transition">
                        <option value="">Semua Penugasan</option>
                        <option value="me" {{ request('assigned') === 'me' ? 'selected' : '' }}>Ditugaskan ke Saya</option>
                    </select>
                </div>

                <!-- Action Filter Buttons -->
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>

                <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white font-semibold text-xs sm:text-sm rounded-xl transition cursor-pointer">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Refresh</span>
                </a>

                <a href="{{ route('gomsn.analytics.kpi') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm rounded-xl transition cursor-pointer ml-auto">
                    <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Rekap KPI</span>
                </a>
            </div>

            <!-- Search Input Bar (matching image 5) -->
            <div class="flex items-center gap-2 pt-1">
                <div class="relative flex-1">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari ID Tiket / Pelanggan / CID / Segmen Backbone..."
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 pl-10 pr-4 py-2.5 text-slate-700 transition">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-xs cursor-pointer transition inline-flex items-center gap-2"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Ticket Cards Grid with Left Color Stripe (§11, AC-08) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @forelse($tickets as $ticket)
            @php
                $stripe = $ticket->stripeColor();
                $stripeBg = match($stripe) {
                    'red' => 'bg-red-500',
                    'yellow' => 'bg-amber-400',
                    'green' => 'bg-emerald-500',
                    'gray' => 'bg-slate-400',
                    default => 'bg-teal-600',
                };
                $lead = $ticket->assignees->firstWhere('pivot.role_in_team', 'lead');
            @endphp
            <div class="relative bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-xs hover:shadow-md transition overflow-hidden flex flex-col justify-between group">
                <!-- Stripe Accent on Left Border -->
                <div class="absolute top-0 bottom-0 left-0 w-2.5 {{ $stripeBg }}"></div>

                <div class="p-5 pl-6 sm:pl-7 space-y-3.5">
                    <!-- Top Bar: Ticket No & Badges -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wider font-mono
                                    {{ $ticket->kategori_tiket === 'BACKBONE' ? 'bg-cyan-50 text-cyan-800 border border-cyan-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                    {{ $ticket->kategori_tiket }}
                                </span>
                                <span class="text-[11px] text-slate-400">#{{ $ticket->id }}</span>
                            </div>
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-heading font-black text-base sm:text-lg text-slate-800 hover:text-[#0D9488] transition block mt-1 tracking-tight">
                                {{ $ticket->no_tiket }}
                            </a>
                        </div>

                        <!-- Priority & Status Pills -->
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                {{ $ticket->priority === 'P1' ? 'bg-red-100 text-red-700 border border-red-200' : ($ticket->priority === 'P2' ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($ticket->priority === 'P3' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200')) }}">
                                {{ $ticket->priority }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $ticket->status === 'CLOSE' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($ticket->status === 'CANCELLED' ? 'bg-slate-100 text-slate-700' : ($ticket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-100 text-purple-800 border border-purple-200' : ($ticket->status === 'PROSES' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-teal-100 text-teal-800 border border-teal-200'))) }}">
                                {{ $ticket->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Problem / Segment Info Body -->
                    <div class="bg-slate-50/70 rounded-xl p-3 border border-slate-100 space-y-1.5 text-xs text-slate-600">
                        @if($ticket->kategori_tiket === 'BACKBONE')
                            <div class="flex items-start justify-between gap-1">
                                <span class="text-slate-400 font-medium">Segmen:</span>
                                <span class="font-bold text-slate-800 text-right text-xs truncate max-w-[200px]">{{ $ticket->backbone_segment ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-slate-400 font-medium">Dampak:</span>
                                <span class="font-semibold text-xs {{ $ticket->status_link_impact === 'TOTAL_DOWN' ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ $ticket->status_link_impact ?? '-' }} ({{ $ticket->wilayah }})
                                </span>
                            </div>
                        @else
                            <div class="flex items-start justify-between gap-1">
                                <span class="text-slate-400 font-medium">Pelanggan:</span>
                                <span class="font-bold text-slate-800 text-right text-xs truncate max-w-[190px]">
                                    {{ $ticket->customer_name }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-1 text-[11px]">
                                <span class="text-slate-400 font-medium">CID / ODP:</span>
                                <span class="font-mono text-slate-700">
                                    {{ $ticket->customer_id ?? 'Non-CID' }} · {{ $ticket->customer_odp ?? '-' }}
                                    @if(!$ticket->is_ims_validated)
                                        <span class="text-amber-600 font-bold ml-1">(Manual)</span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-1 text-[11px] pt-1 border-t border-slate-200/60">
                            <span class="text-slate-400 font-medium">Buka:</span>
                            <span class="text-slate-700 font-medium font-mono">
                                {{ $ticket->tanggal_open ? $ticket->tanggal_open->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                            </span>
                        </div>
                    </div>

                    <!-- SLA & Team Lead Footer -->
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="text-slate-400 text-[11px]">Target SLA:</span>
                            <span class="font-bold text-slate-700 font-mono">{{ $ticket->sla_target_minutes }} Menit</span>
                        </div>

                        <!-- SLA Live Warning Alert -->
                        @if($ticket->activeStopClock)
                            <div class="p-1.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                <span>Stop Clock Aktif ({{ $ticket->activeStopClock->reason }})</span>
                            </div>
                        @elseif($ticket->isOverdue() && $ticket->status !== 'CLOSE')
                            <div class="p-1.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-bold flex items-center gap-1.5">
                                <span>⚠️ Overdue Target SLA</span>
                            </div>
                        @elseif($ticket->sla_status)
                            <div class="text-[11px] text-right">
                                <span class="font-bold {{ $ticket->sla_status === 'TEPAT' ? 'text-emerald-700' : 'text-rose-600' }}">
                                    Hasil: SLA {{ $ticket->sla_status }} ({{ $ticket->net_mttr_minutes }}m)
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center font-bold text-[10px] text-slate-700 uppercase">
                                    {{ $lead ? substr($lead->name, 0, 1) : '?' }}
                                </div>
                                <span class="text-slate-700 font-medium truncate max-w-[130px] text-xs">
                                    {{ $lead ? $lead->name : 'Belum di-dispatch' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400">
                                {{ $ticket->assignees->count() }} Teknisi
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Link -->
                <div class="bg-slate-50/80 px-5 py-3 pl-6 sm:pl-7 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] font-mono">
                        {{ $ticket->status_link_impact ?? $ticket->wilayah ?? 'OPERASIONAL' }}
                    </span>
                    <a href="{{ route('tickets.show', $ticket) }}"
                       class="text-[#0D9488] group-hover:text-[#096a62] font-bold inline-flex items-center gap-1.5 transition">
                        <span>Lihat Detail</span>
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
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
