@extends('layouts.app')

@section('title', 'Open Tiket Gangguan Baru - GoMSN')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="openTicketForm({
    initialCategory: '{{ $initialCategory ?? 'backbone' }}',
    backbonePreview: '{{ $backbonePreview }}',
    broadbandPreview: '{{ $broadbandPreview }}',
    creatorName: '{{ auth()->user()->name }}',
    backboneStoreUrl: '{{ route('tickets.backbone.store') }}',
    broadbandStoreUrl: '{{ route('tickets.broadband.store') }}',
    imsSearchUrl: '{{ route('tickets.ims.search') }}'
})">

    <!-- Top Header Banner (Corporate Ocean Teal Gradient - Compact Sleek) -->
    <div class="gomsn-themed-banner rounded-2xl px-5 py-4 sm:px-6 sm:py-4.5 text-white shadow-lg relative overflow-hidden"
         style="background: var(--gomsn-header-bg) !important;">
        <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-[#18C4C4]/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-[#5eead4] shadow-inner shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-base sm:text-lg font-heading font-extrabold tracking-tight text-white">
                            Open Tiket Gangguan Baru
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/40">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                            STATUS: OPEN
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-teal-100/75 mt-0.5 max-w-xl font-normal">
                        Input tiket gangguan Backbone FO atau Maintenance Pelanggan Broadband FTTH
                    </p>
                </div>
            </div>

            <div>
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white/90 bg-white/10 hover:bg-white/20 border border-white/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Error / Validation Alert -->
    @if($errors->any())
        <div class="p-4 sm:p-5 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs sm:text-sm space-y-1.5 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Mohon lengkapi formulir sesuai ketentuan berikut:</span>
            </div>
            <ul class="list-disc list-inside pl-4 space-y-0.5 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Grid: Left Form (2 cols) & Right Sidebar Preview/Guide (1 col) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- ================================================================= -->
        <!-- LEFT: FORMULIR TIKET GANGGUAN -->
        <!-- ================================================================= -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">

            <!-- Card Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#0D9488]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-heading font-extrabold text-base sm:text-lg text-slate-800">
                            Formulir Tiket Gangguan
                        </h2>
                        <p class="text-xs text-slate-400">Pilih kategori tiket &amp; lengkapi rincian kendala</p>
                    </div>
                </div>

                <!-- Preview Number Badge -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-teal-50 border border-teal-200 text-[#0D9488]">
                    <span x-text="category === 'backbone' ? backbonePreview : broadbandPreview"></span>
                </div>
            </div>

            <!-- Form starts -->
            <form :action="category === 'backbone' ? backboneStoreUrl : broadbandStoreUrl"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf

                <!-- 1. Kategori Tiket Gangguan (Selector Tiles) -->
                <div>
                    <div class="flex items-center gap-2 mb-2.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kategori Tiket Gangguan
                        </label>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Backbone FO Selector Card -->
                        <button type="button"
                                @click="selectCategory('backbone')"
                                :class="category === 'backbone' ? 'border-[#0D9488] bg-teal-50/50 shadow-xs ring-2 ring-teal-500/20' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                class="p-4 rounded-2xl border-2 transition text-left cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div :class="category === 'backbone' ? 'bg-[#0D9488] text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-teal-50 group-hover:text-[#0D9488]'"
                                     class="w-11 h-11 rounded-xl flex items-center justify-center transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-heading font-extrabold text-sm text-slate-800">Backbone FO</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Transmisi kabel optik utama</p>
                                </div>
                            </div>
                            <!-- Radio indicator -->
                            <div :class="category === 'backbone' ? 'border-[#0D9488] bg-[#0D9488] text-white' : 'border-slate-300 bg-white text-transparent'"
                                 class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </button>

                        <!-- Maintenance Broadband Selector Card -->
                        <button type="button"
                                @click="selectCategory('broadband')"
                                :class="category === 'broadband' ? 'border-[#0D9488] bg-teal-50/50 shadow-xs ring-2 ring-teal-500/20' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                class="p-4 rounded-2xl border-2 transition text-left cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div :class="category === 'broadband' ? 'bg-[#0D9488] text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-teal-50 group-hover:text-[#0D9488]'"
                                     class="w-11 h-11 rounded-xl flex items-center justify-center transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.393 9.929c5.857-5.858 15.355-5.858 21.213 0"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-heading font-extrabold text-sm text-slate-800">Maintenance Broadband</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">User FTTH / Pelanggan Home</p>
                                </div>
                            </div>
                            <!-- Radio indicator -->
                            <div :class="category === 'broadband' ? 'border-[#0D9488] bg-[#0D9488] text-white' : 'border-slate-300 bg-white text-transparent'"
                                 class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- SECTION: FIELDS FOR BACKBONE FO -->
                <!-- ========================================================= -->
                <div x-show="category === 'backbone'" class="space-y-4">
                    <!-- Status Link Impact -->
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Status Link Impact
                            </label>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                        </div>
                        <input type="text"
                               name="status_link_impact"
                               x-model="statusLinkImpact"
                               :required="category === 'backbone'"
                               placeholder="Contoh: Backbone : SW BBLU - SW Reog Down"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-width="2" d="M12 16v-4m0-4h.01"/></svg>
                            <span>Tuliskan nama link switch / perangkat dan status gangguan dengan jelas.</span>
                        </p>
                    </div>

                    <!-- Segment / Backbone -->
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Segment / Backbone
                            </label>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                        </div>
                        <select name="backbone_segment"
                                x-model="segment"
                                @change="onSegmentChange($event)"
                                :required="category === 'backbone'"
                                class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                            <option value="">-- Pilih Segment Backbone --</option>
                            @foreach($segments as $seg)
                                <option value="{{ $seg }}">{{ $seg }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-teal-600 font-medium mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                            <span>Pilih jalur link untuk menghitung SLA otomatis.</span>
                        </p>
                    </div>

                    <!-- Wilayah & Tipe Penanganan (Optional / Pre-filled) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Wilayah / Area Operasional
                            </label>
                            <input type="text"
                                   name="wilayah"
                                   x-model="wilayah"
                                   placeholder="Contoh: Bandung Selatan / Soreang"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tipe Penanganan
                            </label>
                            <input type="text"
                                   name="tipe_penanganan"
                                   x-model="tipePenanganan"
                                   placeholder="Contoh: Splicing / Cut FO / Bending Core"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- SECTION: FIELDS FOR MAINTENANCE BROADBAND -->
                <!-- ========================================================= -->
                <div x-show="category === 'broadband'" class="space-y-4" style="display: none;">
                    <!-- IMS Live Search Field -->
                    <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2 relative">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-extrabold text-[#0D9488] uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#0D9488]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Pencarian Data Pelanggan IMS (Otomatis)</span>
                            </label>
                            <span class="text-[10px] text-teal-700 font-semibold bg-teal-100/80 px-2 py-0.5 rounded-full">Auto-fill No. Internet / WhatsApp / ODP</span>
                        </div>
                        <div class="relative">
                            <input type="text"
                                   x-model="imsSearchQuery"
                                   @input.debounce.350ms="searchIms()"
                                   placeholder="Ketik Nomor Internet (contoh: 1010222), Nama Pelanggan, atau No WhatsApp..."
                                   class="w-full text-xs sm:text-sm rounded-xl border border-teal-300 bg-white focus:border-[#0D9488] focus:ring-[#0D9488] px-3.5 py-2.5 text-slate-800 transition pr-10 shadow-xs">
                            <div class="absolute right-3 top-3 text-slate-400" x-show="isSearchingIms">
                                <svg class="animate-spin w-4 h-4 text-teal-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>

                        <!-- Dropdown Search Results -->
                        <div x-show="imsResults.length > 0"
                             @click.outside="imsResults = []"
                             class="absolute left-0 right-0 top-full mt-1 bg-white rounded-2xl border border-slate-200 shadow-xl z-30 max-h-56 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="item in imsResults" :key="item.nomor_internet || item.cid || item.id">
                                <button type="button"
                                        @click="selectCustomer(item)"
                                        class="w-full px-4 py-2.5 text-left hover:bg-teal-50/80 transition flex items-center justify-between group">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs sm:text-sm text-slate-800" x-text="item.name"></span>
                                            <span class="font-mono text-[11px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 group-hover:bg-teal-100 group-hover:text-teal-800" x-text="'No. Internet: ' + (item.nomor_internet || item.cid)"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-0.5" x-text="(item.phone ? 'WA: ' + item.phone : '') + (item.odp ? ' • ODP: ' + item.odp : '') + (item.olt ? ' • OLT: ' + item.olt : '') + (item.address ? ' • ' + item.address : '')"></p>
                                    </div>
                                    <span class="text-xs font-bold text-teal-600 opacity-0 group-hover:opacity-100 transition">Pilih &rarr;</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Nomor Internet & Nama Pelanggan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Nomor Internet
                                </label>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">Opsional</span>
                            </div>
                            <input type="text"
                                   name="customer_id"
                                   x-model="customerCid"
                                   placeholder="Contoh: 1010222 (Nomor Internet Pelanggan)"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Nama Pelanggan
                                </label>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                            </div>
                            <input type="text"
                                   name="customer_name"
                                   x-model="customerName"
                                   :required="category === 'broadband'"
                                   placeholder="Nama lengkap pelanggan"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        </div>
                    </div>

                    <!-- No WhatsApp & ODP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    No WhatsApp / Telepon
                                </label>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                            </div>
                            <input type="text"
                                   name="customer_phone"
                                   x-model="customerPhone"
                                   :required="category === 'broadband'"
                                   placeholder="08123456789"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    ODP / Lokasi FAT
                                </label>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-100 uppercase">Data FTTH</span>
                            </div>
                            <input type="text"
                                   name="customer_odp"
                                   x-model="customerOdp"
                                   list="available-odp-list"
                                   placeholder="Contoh: ODP-SRE-04/12"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                            <datalist id="available-odp-list">
                                @foreach($availableOdps ?? [] as $odpItem)
                                    <option value="{{ $odpItem }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <!-- POP / OLT & Alamat Pelanggan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    POP / OLT / Hub
                                </label>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-100 uppercase">Data FTTH</span>
                            </div>
                            <input type="text"
                                   name="customer_olt"
                                   x-model="customerOlt"
                                   list="available-olt-list"
                                   placeholder="Contoh: OLT-ZTE-SOREANG-01"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                            <datalist id="available-olt-list">
                                @foreach($availableOlts ?? [] as $oltItem)
                                    <option value="{{ $oltItem }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Pelanggan
                            </label>
                            <input type="text"
                                   name="customer_address"
                                   x-model="customerAddress"
                                   placeholder="Jalan, No Rumah, RT/RW, Kelurahan"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- COMMON FIELDS: SLA TARGET & TANGGAL JAM GANGGUAN -->
                <!-- ========================================================= -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                    <!-- Target SLA (Menit) + Jam Badge -->
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Target SLA (Menit)
                            </label>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                        </div>
                        <div class="relative flex items-center">
                            <input type="number"
                                   name="sla_target_minutes"
                                   x-model="slaMinutes"
                                   required
                                   min="15"
                                   max="10080"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 pr-20 text-slate-800 transition">
                            <div class="absolute right-2.5 px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200 text-[#0D9488] font-bold text-xs"
                                 x-text="(parseFloat(slaMinutes || 0) / 60).toFixed(1) + ' Jam'">
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal & Jam Gangguan (WIB) -->
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Tanggal &amp; Jam Gangguan (WIB)
                            </label>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 uppercase">Wajib</span>
                        </div>
                        <input type="datetime-local"
                               name="tanggal_open"
                               x-model="tanggalOpen"
                               required
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition">
                    </div>
                </div>

                <!-- Deskripsi Gangguan & Informasi Awal -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Deskripsi Gangguan &amp; Informasi Awal
                        </label>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">Opsional</span>
                    </div>
                    <textarea name="notes"
                              rows="3"
                              x-model="notes"
                              placeholder="Catatan tambahan kondisi gangguan lapangan atau informasi awal..."
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:border-teal-500 focus:ring-teal-500 px-3.5 py-2.5 text-slate-800 transition"></textarea>
                </div>

                <!-- Foto Bukti Gangguan / Masalah (Drag & Drop Zone) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Foto Bukti Gangguan / Masalah</span>
                            </label>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">Opsional</span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">Bisa pilih &gt; 1 foto &bull; Maks 12MB/foto</span>
                    </div>

                    <!-- Dropzone -->
                    <div @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         @click="$refs.photoInput.click()"
                         :class="isDragging ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-300 bg-slate-50/40 hover:bg-slate-50 hover:border-slate-400'"
                         class="border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center transition cursor-pointer group">
                        
                        <input type="file"
                               x-ref="photoInput"
                               name="photos[]"
                               multiple
                               accept="image/*,.pdf"
                               class="hidden"
                               @change="handleFileSelect($event)">

                        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 border border-rose-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-700 group-hover:text-teal-700 transition">
                            Klik atau Tarik Foto Bukti ke Sini
                        </h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                            Foto indikator LOS, layar NMS, kabel putus, redaman OPM, atau kondisi perangkat
                        </p>
                    </div>

                    <!-- Selected Files Preview List -->
                    <template x-if="selectedFiles.length > 0">
                        <div class="mt-3 space-y-2">
                            <template x-for="(file, idx) in selectedFiles" :key="idx">
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="font-semibold text-slate-700 truncate" x-text="file.name"></span>
                                        <span class="text-[11px] text-slate-400" x-text="'(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
                                    </div>
                                    <button type="button" @click="removeFile(idx)" class="text-rose-500 hover:text-rose-700 font-bold px-2 py-0.5 text-sm">&times;</button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('tickets.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 transition cursor-pointer">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Batal</span>
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white shadow-lg shadow-teal-950/20 hover:scale-[1.02] active:scale-[0.98] transition cursor-pointer"
                            style="background: var(--gomsn-active-bg) !important;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Simpan &amp; Open Tiket</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ================================================================= -->
        <!-- RIGHT: SIDEBAR WIDGETS (PREVIEW TIKET + PANDUAN OPEN TIKET) -->
        <!-- ================================================================= -->
        <div class="space-y-6">

            <!-- WIDGET 1: LIVE PREVIEW TIKET CARD (Dark Ocean Teal Theme) -->
            <div class="rounded-3xl p-6 text-white shadow-xl border border-[#18C4C4]/20 relative overflow-hidden"
                 style="background: var(--gomsn-header-bg) !important;">
                
                <!-- Glowing effect -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-[#18C4C4]/20 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-4">
                    <!-- Top Bar: Title & Status -->
                    <div class="flex items-center justify-between border-b border-white/10 pb-3.5">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#5eead4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-300">Preview Tiket</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/40">
                            STATUS: OPEN
                        </span>
                    </div>

                    <!-- Kategori Row -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-medium">Kategori:</span>
                        <span class="px-2.5 py-0.5 rounded-md font-extrabold text-[11px] tracking-wider uppercase bg-[#18C4C4] text-slate-900"
                              x-text="category === 'backbone' ? 'BACKBONE' : 'BROADBAND'">
                        </span>
                    </div>

                    <!-- Center Inset Box: Auto Ticket Number -->
                    <div class="rounded-2xl p-4 text-center border border-white/10 shadow-inner"
                         style="background: rgba(2, 40, 54, 0.7);">
                        <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">
                            Nomor Tiket Otomatis:
                        </p>
                        <p class="font-mono text-xl sm:text-2xl font-black text-[#5eead4] tracking-wider"
                           x-text="category === 'backbone' ? backbonePreview : broadbandPreview">
                        </p>
                    </div>

                    <!-- Detail Rows -->
                    <div class="space-y-2.5 pt-1 text-xs">
                        <div class="flex items-center justify-between border-b border-white/5 pb-2">
                            <span class="text-slate-400 font-medium">PIC Pembuat:</span>
                            <span class="font-bold text-white" x-text="creatorName"></span>
                        </div>
                        <div class="flex items-center justify-between border-b border-white/5 pb-2">
                            <span class="text-slate-400 font-medium">Segment / Target:</span>
                            <span class="font-semibold text-[#5eead4] truncate max-w-[160px] text-right"
                                  x-text="category === 'backbone' ? (segment || '-') : (customerName || '-')">
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-medium">Target SLA:</span>
                            <span class="px-2 py-0.5 rounded-md bg-teal-400/20 border border-teal-400/30 text-[#5eead4] font-bold text-[11px]"
                                  x-text="(parseFloat(slaMinutes || 0) / 60).toFixed(1) + ' Jam (' + slaMinutes + ' menit)'">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WIDGET 2: PANDUAN OPEN TIKET -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3.5">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#0D9488] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-heading font-extrabold text-sm text-slate-800">
                        Panduan Open Tiket
                    </h3>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Step 1 -->
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-teal-50 border border-teal-200 text-[#0D9488] font-black flex items-center justify-center shrink-0 text-xs">
                            1
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-slate-800">Pilihan Kategori Tiket</h4>
                            <p class="text-slate-500 leading-relaxed">
                                Gunakan <strong class="text-slate-700">Backbone</strong> untuk transmisi FO utama (<span class="font-mono text-rose-500 font-bold">BDG-</span>) dan <strong class="text-slate-700">Broadband</strong> untuk pelanggan FTTH (<span class="font-mono text-teal-600 font-bold">BRD-</span>).
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-teal-50 border border-teal-200 text-[#0D9488] font-black flex items-center justify-center shrink-0 text-xs">
                            2
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-slate-800">Integrasi IMS Customer</h4>
                            <p class="text-slate-500 leading-relaxed">
                                Ketik CID atau nama di kolom pencarian untuk mengisi otomatis data nomor HP, ODP, POP, dan GPS.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-teal-50 border border-teal-200 text-[#0D9488] font-black flex items-center justify-center shrink-0 text-xs">
                            3
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-slate-800">Dispatch Atasan Teknik &amp; Atasan Umum</h4>
                            <p class="text-slate-500 leading-relaxed">
                                Setelah tiket dibuat, Atasan Teknik atau Atasan Umum akan menugaskan tim teknisi lapangan sebelum tiket ditangani.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
function openTicketForm(config) {
    // Current WIB datetime helper in YYYY-MM-DDTHH:mm format
    const getWibLocalStr = () => {
        try {
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).formatToParts(new Date());
            const m = {};
            parts.forEach(p => m[p.type] = p.value);
            return `${m.year}-${m.month}-${m.day}T${m.hour}:${m.minute}`;
        } catch (e) {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            return `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        }
    };
    const localWibStr = getWibLocalStr();

    return {
        category: config.initialCategory || 'backbone',
        backbonePreview: config.backbonePreview,
        broadbandPreview: config.broadbandPreview,
        creatorName: config.creatorName,
        backboneStoreUrl: config.backboneStoreUrl,
        broadbandStoreUrl: config.broadbandStoreUrl,
        imsSearchUrl: config.imsSearchUrl,

        // Form state
        statusLinkImpact: '',
        segment: '',
        wilayah: 'Area Bandung & Sekitarnya',
        tipePenanganan: 'Corrective Maintenance FO',
        slaMinutes: config.initialCategory === 'backbone' ? 360 : 240,
        tanggalOpen: localWibStr,
        notes: '',

        // Broadband specific
        customerCid: '',
        customerName: '',
        customerPhone: '',
        customerOdp: '',
        customerOlt: '',
        customerAddress: '',

        // IMS search
        imsSearchQuery: '',
        imsResults: [],
        isSearchingIms: false,

        // File uploads
        isDragging: false,
        selectedFiles: [],

        selectCategory(cat) {
            this.category = cat;
            if (cat === 'backbone') {
                this.slaMinutes = 360;
            } else {
                this.slaMinutes = 240;
            }
        },

        onSegmentChange(e) {
            this.segment = e.target.value;
            if (this.segment) {
                // If segment has SLA suffix or defaults
                this.slaMinutes = 360;
            }
        },

        async searchIms() {
            const q = this.imsSearchQuery.trim();
            if (q.length < 2) {
                this.imsResults = [];
                return;
            }
            this.isSearchingIms = true;
            try {
                const res = await fetch(`${this.imsSearchUrl}?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (data && data.customers) {
                    this.imsResults = data.customers;
                } else {
                    this.imsResults = [];
                }
            } catch (err) {
                console.warn('IMS search error:', err);
                this.imsResults = [];
            } finally {
                this.isSearchingIms = false;
            }
        },

        selectCustomer(item) {
            this.customerCid = item.nomor_internet || item.cid || '';
            this.customerName = item.name || '';
            this.customerPhone = item.phone || '';
            this.customerOdp = item.odp || '';
            this.customerOlt = item.olt || '';
            this.customerAddress = item.address || '';
            this.imsResults = [];
            this.imsSearchQuery = item.name ? `${item.name} (${item.nomor_internet || item.cid || ''})` : (item.nomor_internet || item.cid || '');
        },

        handleFileSelect(e) {
            if (e.target.files) {
                this.addFiles(Array.from(e.target.files));
            }
        },

        handleDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer && e.dataTransfer.files) {
                this.addFiles(Array.from(e.dataTransfer.files));
                // Sync to file input
                this.$refs.photoInput.files = e.dataTransfer.files;
            }
        },

        addFiles(files) {
            for (const file of files) {
                this.selectedFiles.push({
                    name: file.name,
                    size: file.size
                });
            }
        },

        removeFile(idx) {
            this.selectedFiles.splice(idx, 1);
        }
    };
}
</script>
@endpush
@endsection
