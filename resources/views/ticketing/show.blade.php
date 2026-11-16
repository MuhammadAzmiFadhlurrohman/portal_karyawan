@extends('layouts.app', ['title' => 'Tiket ' . $tiket->no_tiket . ' - Detail & Penanganan'])

@section('content')
<div class="space-y-6">
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
    @endphp

    <!-- Top Banner: Theme Dark Ocean Teal with Left Accent Stripe -->
    <div class="relative overflow-hidden rounded-2xl md:rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-teal-500/20"
         style="background: linear-gradient(135deg, #022836 0%, #053b47 30%, #0a525f 70%, #084c57 100%);">
        <!-- Accent stripe on far left -->
        <div class="absolute top-0 bottom-0 left-0 w-3 {{ $stripeBg }}"></div>
        
        <!-- Ambient decorative glows -->
        <div class="absolute -right-20 -top-20 w-72 h-72 rounded-full bg-[#18C4C4]/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 pl-2 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs font-semibold text-teal-200/80 mb-2">
                    <a href="{{ route('tickets.index') }}" class="hover:text-white transition">Pekerjaan &amp; Tiketing</a>
                    <span>&rsaquo;</span>
                    <span class="text-teal-300 font-mono">{{ $tiket->kategori_tiket }}</span>
                    <span>&rsaquo;</span>
                    <span class="text-white font-mono">{{ $tiket->no_tiket }}</span>
                </nav>

                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-heading font-extrabold text-white tracking-tight">
                        {{ $tiket->no_tiket }}
                    </h1>
                    
                    <!-- Kategori Badge -->
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono uppercase tracking-wider
                        {{ $tiket->kategori_tiket === 'BACKBONE' ? 'bg-cyan-500/20 text-[#5eead4] border border-cyan-400/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' }}">
                        {{ $tiket->kategori_tiket }}
                    </span>

                    <!-- Priority Badge -->
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                        {{ $tiket->priority === 'P1' ? 'bg-red-500/20 text-red-300 border border-red-500/40' : ($tiket->priority === 'P2' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-blue-500/20 text-blue-300 border border-blue-500/40') }}">
                        Prioritas {{ $tiket->priority }}
                    </span>

                    <!-- Status Badge -->
                    <span class="px-3 py-1 rounded-lg text-xs font-extrabold uppercase tracking-wider
                        {{ $tiket->status === 'CLOSE' ? 'bg-emerald-500/30 text-emerald-200 border border-emerald-400/50' : ($tiket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-500/30 text-purple-200 border border-purple-400/50' : ($tiket->status === 'PROSES' ? 'bg-amber-500/30 text-amber-200 border border-amber-400/50' : 'bg-teal-500/30 text-teal-200 border border-teal-400/50')) }}">
                        {{ $tiket->status }}
                    </span>

                    @if($tiket->sla_status)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono
                            {{ $tiket->sla_status === 'TEPAT' ? 'bg-emerald-400/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                            SLA: {{ $tiket->sla_status }} (MTTR: {{ $tiket->net_mttr_minutes }}m)
                        </span>
                    @endif
                </div>

                <p class="text-xs sm:text-sm text-teal-100/80 mt-2">
                    @if($tiket->kategori_tiket === 'BACKBONE')
                        Segmen: <strong class="text-white">{{ $tiket->backbone_segment }}</strong> &bull; Dampak: <strong class="text-white">{{ $tiket->status_link_impact }}</strong> ({{ $tiket->wilayah }})
                    @else
                        Pelanggan: <strong class="text-white">{{ $tiket->customer_name }}</strong> (CID: <span class="font-mono text-teal-200">{{ $tiket->customer_id ?? 'Non-CID' }}</span>) &bull; ODP: <strong class="text-white">{{ $tiket->customer_odp ?? '-' }}</strong>
                    @endif
                </p>
            </div>

            <!-- Header Quick Metric Stats & Berita Acara Action -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/15 flex items-center gap-4 text-xs">
                    <div>
                        <span class="text-teal-200/70 block text-[10px] uppercase font-bold tracking-wider">Kepatuhan 30m</span>
                        <span class="font-bold inline-flex items-center gap-1.5 mt-0.5
                            {{ $intervalStatus === 'NORMAL' ? 'text-emerald-300' : ($intervalStatus === 'WARNING' ? 'text-amber-300' : ($intervalStatus === 'STOP_CLOCK' ? 'text-cyan-300' : 'text-rose-400')) }}">
                            @if($intervalStatus === 'NORMAL')
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> NORMAL (&le;20m)
                            @elseif($intervalStatus === 'WARNING')
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> WARNING (21-30m)
                            @elseif($intervalStatus === 'STOP_CLOCK')
                                <span class="w-2 h-2 rounded-full bg-cyan-400"></span> STOP CLOCK
                            @else
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span> OVERDUE (&gt;30m)
                            @endif
                        </span>
                    </div>

                    <div class="h-8 w-px bg-white/20"></div>

                    <div>
                        <span class="text-teal-200/70 block text-[10px] uppercase font-bold tracking-wider">Target SLA</span>
                        <span class="font-bold text-white font-mono text-sm mt-0.5 block">{{ $tiket->sla_target_minutes }}m</span>
                    </div>

                    <div class="h-8 w-px bg-white/20"></div>

                    <div>
                        <span class="text-teal-200/70 block text-[10px] uppercase font-bold tracking-wider">Jeda Stop Clock</span>
                        <span class="font-bold text-amber-300 font-mono text-sm mt-0.5 block">{{ $tiket->total_stop_clock_minutes }}m</span>
                    </div>
                </div>

                <!-- Berita Acara PDF Link Button -->
                <a href="{{ route('gomsn.tickets.berita-acara', $tiket) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl font-bold text-xs sm:text-sm text-white shadow-lg bg-teal-600/90 hover:bg-teal-600 border border-teal-300/30 transition cursor-pointer">
                    <svg class="w-4 h-4 text-[#5eead4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Berita Acara</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-800 font-bold shrink-0">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs sm:text-sm space-y-1 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside pl-4 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabbed Navigation: Pill Styled with Ocean Teal Highlights -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-1.5 shadow-xs overflow-x-auto">
        <nav class="flex space-x-1 min-w-max text-xs sm:text-sm font-semibold" aria-label="Tabs">
            <button type="button" onclick="switchTab('tab-summary')" id="btn-tab-summary"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-white font-bold"
                    style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>1. Ringkasan &amp; Tim</span>
            </button>

            <button type="button" onclick="switchTab('tab-chronology')" id="btn-tab-chronology"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>2. Kronologis</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">{{ $tiket->chronologies->count() }}</span>
            </button>

            <button type="button" onclick="switchTab('tab-stopclock')" id="btn-tab-stopclock"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>3. Stop Clock</span>
                @if($tiket->activeStopClock)
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                @else
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ $tiket->stopClocks->count() }}</span>
                @endif
            </button>

            <button type="button" onclick="switchTab('tab-assets')" id="btn-tab-assets"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>4. Joint Closure &amp; Manuver</span>
            </button>

            <button type="button" onclick="switchTab('tab-materials')" id="btn-tab-materials"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>5. Material &amp; Titik Perbaikan</span>
            </button>

            <button type="button" onclick="switchTab('tab-handover')" id="btn-tab-handover"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>6. Handover Shift</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ $tiket->handovers->count() }}</span>
            </button>

            <button type="button" onclick="switchTab('tab-closing')" id="btn-tab-closing"
                    class="tab-btn px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>7. Alur Closing &amp; Verifikasi</span>
                @if($tiket->status === 'PENDING_VERIFIKASI')
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></span>
                @endif
            </button>
        </nav>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: RINGKASAN & DISPATCH TIM -->
    <!-- ========================================================================= -->
    <div id="tab-summary" class="tab-pane space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informasi Detail Tiket -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0D9488]"></span>
                        <span>Informasi Teknis Gangguan</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-slate-400">ID: #{{ $tiket->id }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                    @if($tiket->kategori_tiket === 'BACKBONE')
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">Segmen Jaringan</span>
                            <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ $tiket->backbone_segment }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">Dampak Link Jaringan</span>
                            <span class="font-bold text-sm mt-0.5 block {{ $tiket->status_link_impact === 'TOTAL_DOWN' ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $tiket->status_link_impact }}
                            </span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">Wilayah / Area</span>
                            <span class="font-bold text-slate-800 mt-0.5 block">{{ $tiket->wilayah }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">Tipe Penanganan</span>
                            <span class="font-bold text-slate-800 mt-0.5 block">{{ $tiket->tipe_penanganan }}</span>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">Nama Pelanggan</span>
                            <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ $tiket->customer_name }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">Customer ID (CID)</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono font-bold text-slate-800">{{ $tiket->customer_id ?? 'Non-CID' }}</span>
                                @if($tiket->is_ims_validated)
                                    <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">IMS ✓</span>
                                @else
                                    <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-bold text-[9px]">Manual (AC-11)</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">No. WhatsApp / Telepon</span>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tiket->customer_phone) }}" target="_blank"
                               class="font-mono font-bold text-teal-600 hover:underline mt-0.5 block">
                                {{ $tiket->customer_phone }} ↗
                            </a>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 font-semibold block text-[10px] uppercase">ODP &amp; OLT</span>
                            <span class="font-bold text-slate-800 mt-0.5 block font-mono">{{ $tiket->customer_odp ?? '-' }} · {{ $tiket->customer_olt ?? '-' }}</span>
                        </div>
                    @endif

                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 font-semibold block text-[10px] uppercase">Waktu Pembukaan Tiket</span>
                        <span class="font-mono text-slate-700 mt-0.5 block">
                            {{ $tiket->tanggal_open ? $tiket->tanggal_open->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 font-semibold block text-[10px] uppercase">Respon Pertama (First Response)</span>
                        <span class="font-mono text-slate-700 mt-0.5 block">
                            {{ $tiket->first_response_at ? $tiket->first_response_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : 'Belum tercatat' }}
                        </span>
                    </div>
                </div>

                @if($tiket->customer_address)
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                        <span class="text-slate-400 font-semibold block text-[10px] uppercase">Alamat Pelanggan</span>
                        <p class="text-slate-700 mt-0.5">{{ $tiket->customer_address }}</p>
                    </div>
                @endif
            </div>

            <!-- Tim Teknisi Lapangan & Dispatch -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#18C4C4]"></span>
                        <span>Penugasan Tim Teknisi (Dispatch)</span>
                    </h3>
                    @can('ticket.dispatch')
                        <button type="button" onclick="openDispatchModal()"
                                class="text-xs text-[#0D9488] hover:text-[#096a62] font-bold hover:underline cursor-pointer">
                            + Tugaskan / Ubah Tim
                        </button>
                    @endcan
                </div>

                @if($lead)
                    <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-200/70 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-[#0D9488] text-white flex items-center justify-center font-bold text-base shadow-sm">
                                {{ substr($lead->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800 bg-teal-200/60 px-2 py-0.5 rounded-md">
                                    LEAD PIC TEKNISI
                                </span>
                                <h4 class="font-bold text-slate-900 text-sm mt-0.5">{{ $lead->name }}</h4>
                                <p class="text-xs text-slate-500 font-mono">{{ $lead->employee_code ?? '-' }} · {{ $lead->phone ?? '-' }}</p>
                            </div>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone ?? '') }}" target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-white border border-teal-200 text-[#0D9488] font-bold text-xs hover:bg-teal-50 transition">
                            Hubungi PIC
                        </a>
                    </div>
                @else
                    <div class="p-6 text-center rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 text-amber-800">
                        <p class="text-xs font-semibold">⚠️ Tiket ini belum memiliki Lead Teknisi yang di-dispatch.</p>
                        @can('ticket.dispatch')
                            <button type="button" onclick="openDispatchModal()" class="mt-2 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                                Dispatch Tim Sekarang
                            </button>
                        @endcan
                    </div>
                @endif

                <!-- Anggota Tim Lapangan Lainnya -->
                <div class="space-y-2 pt-1">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase tracking-wider">Anggota Tim Teknisi Lapangan</span>
                    <div class="space-y-2">
                        @forelse($tiket->assignees->where('pivot.role_in_team', '!=', 'lead') as $member)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 block">{{ $member->name }}</span>
                                        <span class="text-slate-400 text-[11px] font-mono">{{ $member->employee_code }} · {{ $member->phone }}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 uppercase">
                                    Anggota
                                </span>
                            </div>
                        @empty
                            <p class="text-slate-400 italic text-xs">Belum ada anggota tim tambahan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Resume Masalah & Hasil Penanganan Terkini -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h3 class="font-heading font-extrabold text-base text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                <span>Resume Masalah &amp; Hasil Penanganan Lapangan</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block uppercase tracking-wider text-[10px]">Problem Temuan:</label>
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 text-slate-800 min-h-[70px] leading-relaxed">
                        {{ $tiket->problem_temuan ?? 'Belum ada catatan temuan dari lapangan.' }}
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block uppercase tracking-wider text-[10px]">Action Taken (Tindakan):</label>
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 text-slate-800 min-h-[70px] leading-relaxed">
                        {{ $tiket->action_taken ?? 'Belum ada catatan tindakan penanganan dari teknisi.' }}
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6 text-xs pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-semibold">Redaman Sebelum:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $tiket->redaman_sebelum !== null ? $tiket->redaman_sebelum . ' dBm' : '-' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-semibold">Redaman Sesudah:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $tiket->redaman_sesudah !== null ? $tiket->redaman_sesudah . ' dBm' : '-' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-semibold">Status Link:</span>
                    {!! $tiket->has_link_up ? '<span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">✓ Link UP Terpulihkan</span>' : '<span class="text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">✗ Belum Link Up</span>' !!}
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: KRONOLOGIS INTERAKTIF -->
    <!-- ========================================================================= -->
    <div id="tab-chronology" class="tab-pane hidden space-y-6">
        <!-- Form Tambah Kronologis (Append-Only §7.3, AC-05) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0D9488]"></span>
                    <span>Tambah Catatan Kronologis (Append-Only §7.3)</span>
                </h3>
                <span class="text-[11px] text-teal-700 font-semibold bg-teal-50 px-2 py-0.5 rounded-full">
                    Aturan Pelaporan 30 Menit Aktif
                </span>
            </div>

            <form method="POST" action="{{ route('tickets.chronologies.store', $tiket) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori Aktivitas *</label>
                        <select name="category" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                            <option value="IZIN">IZIN (Izin Kawasan / Akses Masuk)</option>
                            <option value="OTDR">OTDR (Pengukuran Jarak / Titik Putus)</option>
                            <option value="TRACING">TRACING (Penelusuran Rute Kabel)</option>
                            <option value="MATERIAL">MATERIAL (Persiapan Material &amp; Alat)</option>
                            <option value="JOINTING">JOINTING (Penyambungan Core / Splicing)</option>
                            <option value="LINK_UP">LINK_UP (Pemulihan Sinyal Layanan)</option>
                            <option value="SELESAI">SELESAI (Pekerjaan Lapangan Tuntas)</option>
                            <option value="LAIN">LAIN (Koordinasi / Catatan Lainnya)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lampiran Foto Lapangan</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full text-xs text-slate-600 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-[#0D9488] hover:file:bg-teal-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50/50 p-1">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lampiran Video Lapangan</label>
                        <input type="file" name="video" accept="video/*"
                               class="w-full text-xs text-slate-600 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-[#0D9488] hover:file:bg-teal-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50/50 p-1">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Uraian Kronologis Lapangan *</label>
                    <textarea name="message" rows="3" required placeholder="Jelaskan detail aktivitas teknis, kendala yang dihadapi, atau progres penanganan..."
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition"></textarea>
                </div>

                <!-- Hidden GPS inputs populated via Geolocation button -->
                <input type="hidden" id="chron_lat" name="latitude">
                <input type="hidden" id="chron_lng" name="longitude">
                <input type="hidden" id="chron_acc" name="gps_accuracy">

                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <button type="button" onclick="getLiveGpsCoordinates()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer">
                        <svg class="w-4 h-4 text-[#0D9488]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span id="gps_label">Sertakan Geotag GPS Terkini</span>
                    </button>

                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md shadow-teal-950/20 cursor-pointer transition"
                            style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                        Kirim Entri Kronologis
                    </button>
                </div>
            </form>
        </div>

        <!-- Kronologis Timeline Feed -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h3 class="font-heading font-extrabold text-base text-slate-800 border-b border-slate-100 pb-3 flex items-center justify-between">
                <span>Riwayat Kronologis &amp; Live Feed</span>
                <span class="text-xs font-normal text-slate-400">Total: {{ $tiket->chronologies->count() }} entri</span>
            </h3>

            <div class="space-y-4 relative before:absolute before:inset-0 before:left-4 before:w-0.5 before:bg-slate-100">
                @forelse($tiket->chronologies as $chron)
                    @php
                        $catBadge = match($chron->category) {
                            'LINK_UP' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'SELESAI' => 'bg-teal-100 text-teal-800 border-teal-200',
                            'OTDR' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                            'JOINTING' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'MATERIAL' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'IZIN' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <div class="relative flex items-start gap-3.5 pl-1 text-xs">
                        <!-- Avatar node -->
                        <div class="w-8 h-8 rounded-full bg-teal-50 border border-teal-200 text-[#0D9488] flex items-center justify-center font-bold text-xs shrink-0 shadow-xs z-10">
                            {{ substr($chron->user->name ?? 'S', 0, 1) }}
                        </div>

                        <!-- Card Body -->
                        <div class="flex-1 bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 text-sm">{{ $chron->user->name ?? 'Sistem GoMSN' }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $catBadge }}">
                                        {{ $chron->category }}
                                    </span>
                                </div>
                                <span class="font-mono text-slate-400 text-[11px]">
                                    {{ $chron->created_at ? $chron->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                                </span>
                            </div>

                            <p class="text-slate-700 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                                {{ $chron->message }}
                            </p>

                            <!-- Media Attachments & GPS Geotag -->
                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                @if($chron->photo_path)
                                    <a href="/storage/{{ $chron->photo_path }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-teal-700 font-semibold text-xs hover:bg-teal-50 transition shadow-xs">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Lihat Foto Dokumentasi</span>
                                    </a>
                                @endif

                                @if($chron->video_path)
                                    <a href="/storage/{{ $chron->video_path }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-indigo-700 font-semibold text-xs hover:bg-indigo-50 transition shadow-xs">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>Putar Video Lapangan</span>
                                    </a>
                                @endif

                                @if($chron->latitude && $chron->longitude)
                                    <a href="https://maps.google.com/?q={{ $chron->latitude }},{{ $chron->longitude }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-100 transition shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        <span class="font-mono text-[11px]">{{ round($chron->latitude, 5) }}, {{ round($chron->longitude, 5) }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs italic">
                        Belum ada catatan kronologis pekerjaan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: STOP CLOCK / JEDA SLA -->
    <!-- ========================================================================= -->
    <div id="tab-stopclock" class="tab-pane hidden space-y-6">
        @if($tiket->activeStopClock)
            <!-- Active Stop Clock Banner -->
            <div class="p-6 bg-amber-500/10 border-2 border-amber-500/40 rounded-3xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0 animate-pulse">
                        ⏸
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 bg-amber-200 px-2 py-0.5 rounded-md">
                            STOP CLOCK SEDANG BERJALAN
                        </span>
                        <h4 class="font-heading font-extrabold text-base sm:text-lg text-amber-950 mt-1">
                            Alasan: {{ $tiket->activeStopClock->reason }}
                        </h4>
                        <p class="text-xs text-amber-900/80 mt-0.5">
                            {{ $tiket->activeStopClock->reason_detail }} &bull; Dimulai sejak {{ $tiket->activeStopClock->started_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('tickets.stop-clock.end', $tiket->activeStopClock) }}">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm shadow-md transition cursor-pointer">
                        Hentikan Stop Clock
                    </button>
                </form>
            </div>
        @else
            <!-- Start Stop Clock Form (§7.4) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>Mulai Jeda SLA / Stop Clock (§7.4)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Gunakan saat pekerjaan tertunda akibat faktor eksternal di luar kendali tim teknis. Stop Clock otomatis memotong durasi MTTR.
                    </p>
                </div>

                <form method="POST" action="{{ route('tickets.stop-clock.store', $tiket) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Stop Clock Standar *</label>
                            <select name="reason" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                                <option value="IZIN_AKSES">IZIN_AKSES (Menunggu Izin Kawasan/Gedung - Wajib Bukti)</option>
                                <option value="PIHAK_KETIGA_PLN">PIHAK_KETIGA_PLN (Menunggu PLN / Pihak Ketiga)</option>
                                <option value="CUACA_EKSTRIM">CUACA_EKSTRIM (Hujan Badai / Bencana Alam)</option>
                                <option value="MENUNGGU_MATERIAL">MENUNGGU_MATERIAL (Pengadaan Material Khusus)</option>
                                <option value="PERMINTAAN_PELANGGAN">PERMINTAAN_PELANGGAN (Jadwal Ulang Pelanggan)</option>
                                <option value="FORCE_MAJEURE">FORCE_MAJEURE (Keadaan Kahar)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Bukti Pendukung (Foto / Surat Izin)</label>
                            <input type="file" name="evidence" accept="image/*,application/pdf"
                                   class="w-full text-xs text-slate-600 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50/50 p-1">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Uraian Detail Kendala Lapangan *</label>
                        <textarea name="reason_detail" rows="2" required placeholder="Jelaskan alasan detail kendala dan perkiraan estimasi penanganan..."
                                  class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2 text-slate-800 transition"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-amber-600 hover:bg-amber-700 shadow-md transition cursor-pointer">
                            Aktifkan Stop Clock
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Riwayat Stop Clock Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <h3 class="font-heading font-extrabold text-base text-slate-800 border-b border-slate-100 pb-3 flex items-center justify-between">
                <span>Histori Jeda Stop Clock</span>
                <span class="text-xs text-slate-500 font-mono">Total Akumulasi: <strong class="text-amber-700">{{ $tiket->total_stop_clock_minutes }} Menit</strong></span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3">Alasan &amp; Bukti</th>
                            <th class="p-3">Waktu Mulai</th>
                            <th class="p-3">Waktu Berhenti</th>
                            <th class="p-3">Durasi</th>
                            <th class="p-3">Status Approval</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tiket->stopClocks as $sc)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3">
                                    <span class="font-bold text-slate-900 block">{{ $sc->reason }}</span>
                                    <span class="text-slate-500 text-[11px] block mt-0.5">{{ $sc->reason_detail }}</span>
                                    @if($sc->evidence_path)
                                        <a href="/storage/{{ $sc->evidence_path }}" target="_blank" class="text-teal-600 hover:underline text-[10px] font-semibold mt-1 inline-block">
                                            📎 Lihat Dokumen Bukti
                                        </a>
                                    @endif
                                </td>
                                <td class="p-3 font-mono text-slate-700">
                                    {{ $sc->started_at ? $sc->started_at->timezone('Asia/Jakarta')->format('d M, H:i') : '-' }} WIB
                                </td>
                                <td class="p-3 font-mono text-slate-700">
                                    {{ $sc->ended_at ? $sc->ended_at->timezone('Asia/Jakarta')->format('d M, H:i') . ' WIB' : 'Sedang Berjalan' }}
                                </td>
                                <td class="p-3 font-mono font-bold text-amber-700">
                                    {{ $sc->duration_minutes ?? '-' }} Menit
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $sc->approval_status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($sc->approval_status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $sc->approval_status }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    @can('ticket.stop_clock.approve')
                                        @if($sc->approval_status === 'pending')
                                            <div class="flex items-center justify-end gap-1.5">
                                                <form method="POST" action="{{ route('tickets.stop-clock.approve', $sc) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold transition">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('tickets.stop-clock.reject', $sc) }}">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="Ditolak oleh NOC">
                                                    <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-bold transition">Tolak</button>
                                                </form>
                                            </div>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400 italic">Tidak ada catatan jeda Stop Clock pada tiket ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: JOINT CLOSURE & MANUVER CORE -->
    <!-- ========================================================================= -->
    <div id="tab-assets" class="tab-pane hidden space-y-6">
        <!-- Section Joint Closure -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0D9488]"></span>
                    <span>Joint Closures (Kabel Sambungan FO §7.6)</span>
                </h3>
            </div>

            <!-- Form Tambah Joint Closure -->
            <form method="POST" action="{{ route('tickets.joint-closures.store', $tiket) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-200">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nama Joint Closure *</label>
                    <input type="text" name="nama_closure" required placeholder="Contoh: JC-SRE-KM12 / Dome Closure" class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kapasitas Core *</label>
                    <input type="number" name="kapasitas_core" value="24" min="1" required class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl text-white font-bold text-xs shadow-xs" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                        + Tambah Closure
                    </button>
                </div>
            </form>

            <!-- List Joint Closures & Core Matrix -->
            <div class="space-y-4 pt-2">
                @forelse($tiket->jointClosures as $jc)
                    <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ $jc->nama_closure }}</h4>
                                <span class="text-xs text-slate-500">Kapasitas: {{ $jc->kapasitas_core }} Core &bull; Tipe: {{ $jc->tipe_closure ?? 'Dome' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 text-teal-800">
                                {{ $jc->cores->count() }} Sambungan Core
                            </span>
                        </div>

                        <!-- Form Tambah Sambungan Core -->
                        <form method="POST" action="{{ route('tickets.closure-cores.store', $jc) }}" class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200/80 text-xs">
                            @csrf
                            <input type="text" name="tube_asal" placeholder="Tube Asal" required class="w-20 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs">
                            <input type="number" name="core_asal" placeholder="Core Asal" required class="w-20 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs">
                            <span class="text-slate-400 font-bold">&rarr;</span>
                            <input type="text" name="tube_tujuan" placeholder="Tube Tjn" required class="w-20 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs">
                            <input type="number" name="core_tujuan" placeholder="Core Tjn" required class="w-20 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs">
                            <input type="number" step="0.01" name="loss_db" placeholder="Loss (dB)" class="w-20 rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs">
                            <button type="submit" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold text-xs shadow-xs">
                                Sambung Core
                            </button>
                        </form>

                        <!-- Tabel Matriks Core -->
                        @if($jc->cores->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs text-left bg-white rounded-xl border border-slate-200">
                                    <thead class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase">
                                        <tr>
                                            <th class="p-2">Asal</th>
                                            <th class="p-2">Tujuan</th>
                                            <th class="p-2">Loss (dB)</th>
                                            <th class="p-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($jc->cores as $core)
                                            <tr>
                                                <td class="p-2 font-mono">Tube {{ $core->tube_asal }} / Core {{ $core->core_asal }}</td>
                                                <td class="p-2 font-mono">Tube {{ $core->tube_tujuan }} / Core {{ $core->core_tujuan }}</td>
                                                <td class="p-2 font-mono font-bold {{ $core->loss_db > 0.3 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $core->loss_db ?? '0.00' }} dB</td>
                                                <td class="p-2">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $core->status ?? 'TERHUBUNG' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-400 italic text-xs p-4 text-center">Belum ada Joint Closure yang dicatat.</p>
                @endforelse
            </div>
        </div>

        <!-- Section Manuver Core -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#18C4C4]"></span>
                    <span>Manuver Core FO (§7.6)</span>
                </h3>
            </div>

            <!-- Form Tambah Manuver Core -->
            <form method="POST" action="{{ route('tickets.maneuvers.store', $tiket) }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-200">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Segmen / Titik Manuver *</label>
                    <input type="text" name="segmen" required placeholder="POP Soreang / OTB Rack A" class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Core Asal *</label>
                    <input type="number" name="core_asal" required placeholder="No Core" class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Core Tujuan *</label>
                    <input type="number" name="core_tujuan" required placeholder="No Core" class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-xs">
                        + Manuver Core
                    </button>
                </div>
            </form>

            <!-- Tabel Manuver Core -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="p-3">Segmen / Titik</th>
                            <th class="p-3">Core Asal</th>
                            <th class="p-3">Core Tujuan</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tiket->coreManeuvers as $man)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-semibold text-slate-800">{{ $man->segmen }}</td>
                                <td class="p-3 font-mono font-bold text-teal-700">Core #{{ $man->core_asal }}</td>
                                <td class="p-3 font-mono font-bold text-teal-700">Core #{{ $man->core_tujuan }}</td>
                                <td class="p-3 text-slate-600">{{ $man->keterangan ?? '-' }}</td>
                                <td class="p-3 font-mono text-slate-400">{{ $man->created_at ? $man->created_at->timezone('Asia/Jakarta')->format('d M H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-slate-400 italic">Tidak ada catatan manuver core.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: MATERIAL & TITIK PERBAIKAN -->
    <!-- ========================================================================= -->
    <div id="tab-materials" class="tab-pane hidden space-y-6">
        <!-- Pemakaian Material / Logistik -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                    <span>Pemakaian Material / Logistik Lapangan (§7.6, AC-05)</span>
                </h3>
            </div>

            <!-- Form Pemakaian Material -->
            <form method="POST" action="{{ route('tickets.materials.use', $tiket) }}" class="flex flex-wrap items-end gap-3 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-200">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <label class="block font-bold text-slate-700 mb-1">Pilih Master Material *</label>
                    <select name="material_id" required class="w-full text-xs rounded-xl border-slate-300 bg-white px-3.5 py-2.5">
                        @foreach($materials as $mat)
                            <option value="{{ $mat->id }}">{{ $mat->kode }} - {{ $mat->nama }} (Tersedia: {{ $mat->stok_tersedia }} {{ $mat->satuan }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-28">
                    <label class="block font-bold text-slate-700 mb-1">Jumlah *</label>
                    <input type="number" name="quantity" min="1" value="1" required class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2.5">
                </div>
                <button type="submit" class="px-5 py-2.5 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer"
                        style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    + Pakai Material
                </button>
            </form>

            <!-- Tabel Pemakaian Material -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="p-3">Kode</th>
                            <th class="p-3">Nama Material</th>
                            <th class="p-3">Jumlah Pakai</th>
                            <th class="p-3">Petugas Pencatat</th>
                            <th class="p-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tiket->materials as $tMat)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-mono font-bold text-slate-800">{{ $tMat->material->kode }}</td>
                                <td class="p-3 font-medium text-slate-800">{{ $tMat->material->nama }}</td>
                                <td class="p-3 font-bold text-teal-800">{{ $tMat->quantity }} {{ $tMat->material->satuan }}</td>
                                <td class="p-3 text-slate-600">{{ $tMat->user->name }}</td>
                                <td class="p-3 font-mono text-slate-400">{{ $tMat->created_at ? $tMat->created_at->timezone('Asia/Jakarta')->format('d M H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-center text-slate-400 italic">Belum ada material yang digunakan untuk tiket ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Titik Perbaikan (Repair Points) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                    <span>Titik Perbaikan (Repair Points §7.6)</span>
                </h3>
            </div>

            <!-- Form Tambah Titik Perbaikan -->
            <form method="POST" action="{{ route('tickets.repair-points.store', $tiket) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-4 rounded-2xl border border-slate-200">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nama Titik / Tiang / Handhole *</label>
                    <input type="text" name="nama_titik" required placeholder="Contoh: Tiang FO KM-14 / Manhole SRE-02" class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Kerusakan</label>
                    <input type="text" name="jenis_kerusakan" placeholder="Kabel putus / Bending / Macro-bend" class="w-full text-xs rounded-xl border-slate-300 bg-white px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-cyan-700 hover:bg-cyan-800 text-white rounded-xl font-bold text-xs shadow-xs">
                        + Tambah Titik
                    </button>
                </div>
            </form>

            <!-- Daftar Titik Perbaikan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($tiket->repairPoints as $rp)
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-1">
                        <h5 class="font-bold text-slate-800">{{ $rp->nama_titik }}</h5>
                        <p class="text-slate-500 text-[11px]">Kerusakan: <span class="font-semibold text-rose-600">{{ $rp->jenis_kerusakan ?? '-' }}</span></p>
                        @if($rp->latitude && $rp->longitude)
                            <a href="https://maps.google.com/?q={{ $rp->latitude }},{{ $rp->longitude }}" target="_blank"
                               class="text-teal-600 hover:underline font-mono text-[10px] inline-block font-bold mt-1">
                                📍 {{ $rp->latitude }}, {{ $rp->longitude }} ↗
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="col-span-full text-slate-400 italic text-xs p-4 text-center">Belum ada titik perbaikan dicatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 6: HANDOVER SHIFT -->
    <!-- ========================================================================= -->
    <div id="tab-handover" class="tab-pane hidden space-y-6">
        <!-- Form Buat Handover Shift -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <span>Serah Terima Tugas (Handover Shift Kerja §7.5)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Mencatat alih tugas ke teknisi shift berikutnya agar eskalasi dan informasi penanganan tidak terputus.
                </p>
            </div>

            <form method="POST" action="{{ route('tickets.handovers.store', $tiket) }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Shift Asal *</label>
                        <input type="text" name="shift_from" required placeholder="Pagi (08:15 - 17:00)"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 px-3.5 py-2.5">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Shift Tujuan *</label>
                        <input type="text" name="shift_to" required placeholder="Siang (13:00 - 21:00)"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 px-3.5 py-2.5">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Teknisi Penerima *</label>
                        <select name="recipient_id" required class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 px-3.5 py-2.5">
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Catatan Kendala &amp; Pekerjaan Pending Wajib *</label>
                    <textarea name="notes" rows="3" required placeholder="Tuliskan kendala teknis yang sedang berlangsung dan instruksi pekerjaan untuk shift berikutnya..."
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 px-3.5 py-2.5"></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition cursor-pointer">
                        Kirim Handover Shift
                    </button>
                </div>
            </form>
        </div>

        <!-- Histori Handover Shift -->
        <div class="space-y-3">
            @forelse($tiket->handovers as $ho)
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs text-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-slate-800 text-sm">{{ $ho->shift_from }} &rarr; {{ $ho->shift_to }}</span>
                        <span class="font-mono text-slate-400">{{ $ho->created_at ? $ho->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB</span>
                    </div>
                    <div class="text-slate-600 flex items-center gap-3 text-xs">
                        <span>Penyerah: <strong class="text-slate-900">{{ $ho->sender->name }}</strong></span>
                        <span>&bull;</span>
                        <span>Penerima: <strong class="text-slate-900">{{ $ho->recipient->name }}</strong></span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 text-slate-800 leading-relaxed">
                        <strong class="text-[10px] uppercase text-slate-400 block mb-1">Catatan Handover:</strong>
                        {{ $ho->notes }}
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        @if($ho->acknowledged_at)
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                ✓ Dikonfirmasi Penerima pada {{ $ho->acknowledged_at->timezone('Asia/Jakarta')->format('d M H:i') }} WIB
                            </span>
                        @else
                            <span class="text-amber-700 font-bold bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                                ⏳ Menunggu konfirmasi penerima shift
                            </span>
                            @if(auth()->id() === $ho->recipient_id)
                                <form method="POST" action="{{ route('tickets.handovers.acknowledge', $ho) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition">
                                        Konfirmasi Terima Handover
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-200 text-center text-slate-400 text-xs italic">
                    Belum ada riwayat handover shift pada tiket ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 7: ALUR CLOSING & VERIFIKASI AKHIR -->
    <!-- ========================================================================= -->
    <div id="tab-closing" class="tab-pane hidden space-y-6">
        <!-- Prerequisites Checklist Card (AC-06, §7.6) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-slate-800 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $prerequisites['passed'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    <span>Status Prasyarat Closing Lapangan (AC-06)</span>
                </h3>
                <span class="px-3 py-1 rounded-full text-xs font-bold
                    {{ $prerequisites['passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $prerequisites['passed'] ? '✓ Siap Diajukan Closing' : '⚠️ Prasyarat Belum Lengkap' }}
                </span>
            </div>

            @if(!$prerequisites['passed'])
                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 text-xs space-y-1.5">
                    <strong>Pekerjaan lapangan belum memenuhi prasyarat closing awal:</strong>
                    <ul class="list-disc list-inside space-y-1 pl-2">
                        @foreach($prerequisites['missing'] as $miss)
                            <li>{{ $miss }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="text-xs text-emerald-700 font-semibold">
                    ✓ Seluruh syarat teknis (resume, dokumentasi, redaman / titik perbaikan) telah terpenuhi.
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tahap 1: Closing Awal Teknisi (AC-06, §7.2, §7.6) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-md">
                        TAHAP 1
                    </span>
                    <h3 class="font-heading font-extrabold text-base text-slate-800 mt-1">Pengajuan Closing Awal (Teknisi)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Diajukan oleh teknisi lapangan setelah perbaikan selesai untuk diverifikasi oleh tim NOC.
                    </p>
                </div>

                <form method="POST" action="{{ route('tickets.closing-awal', $tiket) }}" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[10px] mb-1">Problem Temuan *</label>
                        <input type="text" name="problem_temuan" value="{{ $tiket->problem_temuan }}" required placeholder="Uraian masalah yang ditemukan..."
                               class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 px-3.5 py-2.5">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[10px] mb-1">Action Taken (Tindakan) *</label>
                        <input type="text" name="action_taken" value="{{ $tiket->action_taken }}" required placeholder="Tindakan yang telah dilakukan..."
                               class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 px-3.5 py-2.5">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[10px] mb-1">Redaman Sesudah (dBm)</label>
                            <input type="number" step="0.01" name="redaman_sesudah" value="{{ $tiket->redaman_sesudah }}" placeholder="-19.5"
                                   class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 px-3.5 py-2.5 font-mono">
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_link_up" value="1" {{ $tiket->has_link_up ? 'checked' : '' }} class="rounded text-[#0D9488] focus:ring-teal-500">
                                <span class="font-bold text-slate-800 text-xs">Konfirmasi Link UP</span>
                            </label>
                        </div>
                    </div>

                    <div class="text-right pt-2">
                        <button type="submit"
                                class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition cursor-pointer">
                            Ajukan Closing Awal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tahap 2: Verifikasi & Closing Akhir NOC (AC-08, §7.2, §7.7) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-md">
                        TAHAP 2
                    </span>
                    <h3 class="font-heading font-extrabold text-base text-slate-800 mt-1">Verifikasi &amp; Closing Akhir (NOC)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Memvalidasi hasil kerja teknisi, menutup tiket secara permanen, dan menghitung MTTR bersih serta status SLA.
                    </p>
                </div>

                @if($tiket->status === 'PENDING_VERIFIKASI')
                    <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200 text-xs text-purple-900 space-y-2">
                        <p class="font-bold">Tiket ini telah diajukan Closing Awal oleh teknisi lapangan.</p>
                        <p>Silakan verifikasi trafik monitoring dan redaman. Pilih salah satu tindakan di bawah:</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                        <form method="POST" action="{{ route('tickets.verify-close', $tiket) }}" class="w-full">
                            @csrf
                            <button type="submit"
                                    class="w-full px-5 py-3 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md bg-emerald-600 hover:bg-emerald-700 transition cursor-pointer">
                                ✓ Verifikasi &amp; Tutup Tiket (CLOSE)
                            </button>
                        </form>

                        <button type="button" onclick="document.getElementById('modal-reject-closing').classList.remove('hidden')"
                                class="w-full px-5 py-3 rounded-xl font-bold text-xs sm:text-sm text-white shadow-md bg-rose-600 hover:bg-rose-700 transition cursor-pointer">
                            ✗ Tolak Closing (Reject)
                        </button>
                    </div>
                @else
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center text-slate-400 text-xs italic">
                        Tombol verifikasi aktif saat status tiket bernilai <strong class="text-purple-700">PENDING_VERIFIKASI</strong>.
                    </div>
                @endif

                <!-- Kontrol Reopen & Pembatalan Tiket -->
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <h4 class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">Kontrol Khusus Tiket</h4>
                    @if($tiket->status === 'CLOSE')
                        <form method="POST" action="{{ route('tickets.reopen', $tiket) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Alasan pembukaan kembali tiket..." required
                                   class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2">
                            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition">
                                Reopen Tiket (Batas Maksimal 7 Hari)
                            </button>
                        </form>
                    @elseif(in_array($tiket->status, ['OPEN', 'PROSES']))
                        <form method="POST" action="{{ route('tickets.cancel', $tiket) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Alasan pembatalan tiket..." required
                                   class="w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2">
                            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                                Batalkan Tiket (CANCELLED)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL DISPATCH TIM TEKNISI -->
<!-- ========================================================================= -->
<div id="modal-dispatch" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-5 text-white flex items-center justify-between"
             style="background: linear-gradient(135deg, #022836 0%, #053b47 30%, #0a525f 70%, #084c57 100%);">
            <h3 class="font-heading font-extrabold text-base sm:text-lg text-white">Tugaskan Tim Teknisi (Dispatch)</h3>
            <button type="button" onclick="closeDispatchModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.dispatch', $tiket) }}" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Lead PIC Teknisi *</label>
                <select name="lead_id" required class="w-full text-xs sm:text-sm rounded-xl border-slate-300 bg-slate-50/50 px-3.5 py-2.5">
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" {{ $lead && $lead->id === $t->id ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Anggota Tim Lapangan (Pilih Beberapa)</label>
                <select name="member_ids[]" multiple class="w-full text-xs rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 min-h-[110px]">
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" {{ $tiket->assignees->pluck('id')->contains($t->id) && (!$lead || $lead->id !== $t->id) ? 'selected' : '' }}>
                            {{ $t->name }} ({{ $t->employee_code }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Tekan CTRL untuk memilih lebih dari satu teknisi.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t">
                <button type="button" onclick="closeDispatchModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold shadow-md" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%);">
                    Simpan Penugasan Tim
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL TOLAK CLOSING AWAL (REJECT) -->
<!-- ========================================================================= -->
<div id="modal-reject-closing" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 bg-rose-600 text-white flex items-center justify-between">
            <h3 class="font-heading font-bold text-base">Tolak Closing Awal</h3>
            <button type="button" onclick="document.getElementById('modal-reject-closing').classList.add('hidden')" class="text-white text-lg font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('tickets.reject-closing', $tiket) }}" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1.5 uppercase tracking-wider text-[10px]">Alasan Penolakan Closing *</label>
                <textarea name="reason" rows="3" required placeholder="Jelaskan alasan penolakan (misal redaman belum standar, foto perbaikan buram)..."
                          class="w-full text-xs rounded-xl border border-slate-300 p-3"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="document.getElementById('modal-reject-closing').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

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

function openDispatchModal() {
    document.getElementById('modal-dispatch').classList.remove('hidden');
}
function closeDispatchModal() {
    document.getElementById('modal-dispatch').classList.add('hidden');
}

// Live HTML5 Geolocation helper for Chronology
function getLiveGpsCoordinates() {
    const label = document.getElementById('gps_label');
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung Geolocation.');
        return;
    }
    label.innerText = 'Mengambil GPS...';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('chron_lat').value = pos.coords.latitude;
            document.getElementById('chron_lng').value = pos.coords.longitude;
            document.getElementById('chron_acc').value = pos.coords.accuracy;
            label.innerText = `✓ Lokasi Disimpan (${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)})`;
        },
        (err) => {
            alert('Gagal mengambil lokasi GPS: ' + err.message);
            label.innerText = 'Sertakan Geotag GPS Terkini';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}
</script>
@endsection
