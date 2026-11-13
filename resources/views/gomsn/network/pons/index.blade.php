<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching GoMSN Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Icon + Title + Role Access Badge -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Infrastruktur Jaringan FTTH
                        </h1>
                        @if ($canManage)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                Akses Penuh: Admin & Atasan
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-cyan-500/20 text-cyan-300 border border-cyan-400/30">
                                Mode Monitoring (Read-Only)
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-[13px] mt-0.5 leading-snug truncate" style="color: rgba(204, 251, 241, 0.95) !important;">
                        Manajemen &amp; Pemetaan Terpadu OLT, Distribusi PON, Titik ODP, dan Port Pelanggan PT MSN
                    </p>
                </div>
            </div>

            <!-- Right: Unified Stats KPI Pill (Matching Roster Shift style) -->
            <div class="relative z-10 flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-3 bg-black/25 backdrop-blur-md border border-white/15 px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-xs">
                    <span class="flex items-center gap-1.5" title="Total OLT">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">OLT</span>
                        <span class="font-extrabold text-white">{{ $totalOlts }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="flex items-center gap-1.5" title="Total PON">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">PON</span>
                        <span class="font-extrabold text-white">{{ $totalPons }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="flex items-center gap-1.5" title="Total ODP">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">ODP</span>
                        <span class="font-extrabold text-white">{{ $totalOdps }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="flex items-center gap-1.5 text-emerald-300" title="Total Pelanggan Terdaftar">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">Pelanggan</span>
                        <span class="font-extrabold text-emerald-300">{{ number_format($totalCustomers) }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- OLT Switcher & Breadcrumb Bar -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
            <div class="flex items-center gap-2.5 text-slate-800 font-bold text-sm sm:text-base tracking-tight">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span>{{ $selectedOlt->nama_olt }}</span>
                <span class="text-xs text-slate-400 font-medium">({{ $pons->total() }} PON terdaftar)</span>
            </div>

            <!-- OLT Tab Switcher Segmented Control -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 p-1 bg-slate-100/80 rounded-2xl border border-slate-200/60 max-w-full">
                @foreach ($olts as $olt)
                    @php
                        $isSelected = ($olt->id === $selectedOlt->id);
                    @endphp
                    <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}" 
                       @if ($isSelected)
                           style="background: linear-gradient(135deg, #094747 0%, #0d5f5f 50%, #0f766e 100%) !important; color: #ffffff !important;"
                       @endif
                       class="group px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2 {{ $isSelected ? 'shadow-md border border-teal-400/40 text-white' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                        <span class="w-1.5 h-1.5 rounded-full transition-colors {{ $isSelected ? 'bg-emerald-300 shadow-[0_0_8px_#6ee7b7]' : 'bg-slate-300 group-hover:bg-slate-400' }}"></span>
                        <span>{{ $olt->nama_olt }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Card Tambah PON Baru (Hanya Muncul untuk Admin / Atasan Teknisi) -->
        @if ($canManage)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5 text-slate-800 font-bold text-xs sm:text-sm">
                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200/60 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span>Tambah PON Baru pada {{ $selectedOlt->nama_olt }}</span>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400 hidden sm:inline">Kelola alokasi splitter &amp; distribusi port</span>
                </div>

                <form action="{{ route('gomsn.network.pons.store', $selectedOlt) }}" method="POST" class="p-4 sm:p-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4 items-end">
                        <!-- Nama PON (6 Kolom) -->
                        <div class="md:col-span-6">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Nama PON</span>
                            </label>
                            <input type="text" name="nama_pon" required placeholder="Contoh: PON-001 atau PON 5 / MS SUFIA" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs bg-white">
                        </div>

                        <!-- Kapasitas Port (3 Kolom) -->
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Kapasitas Port</span>
                            </label>
                            <select name="port_max" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all bg-white shadow-2xs">
                                <option value="2">2 Port</option>
                                <option value="4">4 Port</option>
                                <option value="8" selected>8 Port</option>
                                <option value="16">16 Port</option>
                                <option value="24">24 Port</option>
                                <option value="32">32 Port</option>
                            </select>
                        </div>

                        <!-- Tombol Tambah (3 Kolom) -->
                        <div class="md:col-span-3">
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%) !important; color: #ffffff !important;"
                                    class="w-full px-4 py-2.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-xs hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah PON</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Notice Read-Only Mode -->
            <div class="bg-sky-50/70 border border-sky-200/80 rounded-2xl p-3 flex items-center gap-2.5 text-sky-800 text-xs">
                <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Anda masuk dalam <strong>Mode Monitoring</strong>. Hak akses menambah, mengedit, dan menghapus PON/ODP dikhususkan untuk <strong>Atasan Teknisi</strong> dan <strong>Admin</strong>.</span>
            </div>
        @endif

        <!-- Tabel Daftar PON (Ringkas & Pas di Mobile, Lengkap di Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr style="background: linear-gradient(90deg, #0f4c5c 0%, #007f8c 50%, #0d9488 100%) !important; color: #ffffff !important;" 
                            class="text-[11px] font-bold tracking-wider uppercase text-white select-none">
                            <th class="hidden md:table-cell py-3 px-4 w-14 text-center">#</th>
                            <th class="py-3 pl-4 pr-2 sm:px-4">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>NAMA PON</span>
                                </div>
                            </th>
                            <th class="py-3 px-2 sm:px-4 w-16 sm:w-64 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5 hidden sm:inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span class="sm:hidden">PORT</span>
                                    <span class="hidden sm:inline">KAPASITAS &amp; UTILISASI PORT</span>
                                </div>
                            </th>
                            <th class="hidden md:table-cell py-3 px-4 w-36 text-center">STATUS</th>
                            <th class="py-3 pl-2 pr-4 sm:px-4 w-34 sm:w-52 text-right sm:text-center">
                                <div class="flex items-center justify-end sm:justify-center gap-1">
                                    <svg class="w-3.5 h-3.5 hidden sm:inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>AKSI</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pons as $pon)
                            @php
                                $usage = $pon->odps_count ?? 0;
                                $max = $pon->port_max ?: 8;
                                $pct = min(100, round(($usage / $max) * 100));
                                $isFull = $usage >= $max || $pct >= 90;
                                $barBg = $isFull ? 'background-color: #dc2626;' : ($pct >= 75 ? 'background-color: #f59e0b;' : 'background-color: #15803d;');
                                $textClass = $isFull ? 'text-rose-600' : 'text-emerald-700';
                                
                                if ($usage == 0) {
                                    $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                                    $badgeText = 'Kosong';
                                } elseif ($isFull) {
                                    $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                    $badgeText = 'Penuh';
                                } elseif ($pct >= 60) {
                                    $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $badgeText = 'Hampir Penuh';
                                } else {
                                    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $badgeText = 'Tersedia';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Nomor Urut (Desktop) -->
                                <td class="hidden md:table-cell py-3.5 px-4 text-center text-slate-400 font-mono font-semibold">
                                    {{ $pons->firstItem() ? $pons->firstItem() + $loop->index : $loop->iteration }}
                                </td>

                                <!-- Nama PON & Metadata -->
                                <td class="py-3.5 pl-4 pr-2 sm:px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="hidden sm:flex w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 items-center justify-center text-teal-700 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-900 text-[13px] sm:text-xs tracking-tight uppercase leading-snug">
                                                {{ $pon->nama_pon }}
                                            </div>
                                            <div class="hidden sm:block text-[11px] text-slate-400 mt-0.5">
                                                {{ $selectedOlt->nama_olt }} &bull; <span class="font-medium text-slate-500">{{ $usage }} ODP terhubung</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Progress Bar Kapasitas Port (Compact di Mobile seperti referensi, Lengkap di Desktop) -->
                                <td class="py-3.5 px-2 sm:px-4 text-center">
                                    <!-- Mobile Compact Ratio + Mini Bar -->
                                    <div class="sm:hidden flex flex-col items-center justify-center">
                                        <span class="text-[13px] font-semibold {{ $textClass }} leading-none mb-1.5">{{ $usage }}/{{ $max }}</span>
                                        <div class="w-9 h-1.5 bg-slate-200/80 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all" style="width: {{ max($usage > 0 ? 22 : 0, $pct) }}%; {{ $barBg }}"></div>
                                        </div>
                                    </div>

                                    <!-- Desktop Full Bar -->
                                    <div class="hidden sm:block w-full max-w-[200px] mx-auto">
                                        <div class="flex items-center justify-between text-[11px] font-semibold mb-1">
                                            <span class="font-mono {{ $textClass }}">{{ $usage }} / {{ $max }} Port</span>
                                            <span class="font-mono text-slate-500 text-[10px]">{{ $pct }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200/60">
                                            <div class="h-full rounded-full transition-all" style="width: {{ $pct }}%; {{ $barBg }}"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge (Desktop) -->
                                <td class="hidden md:table-cell py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </td>

                                <!-- Aksi Buttons (Icon-only Square Buttons di Mobile sesuai gambar, dengan Teks di Desktop) -->
                                <td class="py-3.5 pl-2 pr-4 sm:px-4 text-right sm:text-center">
                                    <div class="flex items-center justify-end sm:justify-center gap-1.5 sm:gap-2">
                                        <!-- Lihat (Semua user bisa lihat) -->
                                        <a href="{{ route('gomsn.network.pons.show', ['olt' => $selectedOlt->id, 'pon' => $pon->id]) }}" 
                                           style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important; color: #ffffff !important;"
                                           title="Lihat Detail PON"
                                           class="w-9 h-9 lg:w-auto lg:h-auto lg:px-3 lg:py-1.5 rounded-xl text-white text-[11px] font-semibold shadow-[0_4px_10px_rgba(13,148,136,0.28)] hover:brightness-105 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 shrink-0">
                                            <svg class="w-4 h-4 lg:w-3.5 lg:h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="hidden lg:inline">Lihat</span>
                                        </a>

                                        @if ($canManage)
                                            <!-- Edit Trigger -->
                                            <button type="button" 
                                                    onclick="openEditPonModal({{ $pon->id }}, '{{ addslashes($pon->nama_pon) }}', {{ $pon->port_max }})"
                                                    style="background: #f59e0b !important; color: #1e293b !important;"
                                                    title="Edit PON"
                                                    class="w-9 h-9 lg:w-auto lg:h-auto lg:px-3 lg:py-1.5 rounded-xl text-[11px] font-bold shadow-[0_4px_10px_rgba(245,158,11,0.22)] hover:brightness-105 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                                                <svg class="w-4 h-4 lg:w-3.5 lg:h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span class="hidden lg:inline">Edit</span>
                                            </button>

                                            <!-- Hapus -->
                                            <form action="{{ route('gomsn.network.pons.destroy', $pon) }}" method="POST" class="inline-flex"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus PON {{ addslashes($pon->nama_pon) }}? Semua ODP terkait juga akan terhapus!');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        style="background: #ef4444 !important; color: #ffffff !important;"
                                                        title="Hapus PON"
                                                        class="w-9 h-9 lg:w-auto lg:h-auto lg:px-3 lg:py-1.5 rounded-xl text-white text-[11px] font-semibold shadow-[0_4px_10px_rgba(239,68,68,0.25)] hover:brightness-105 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                                                    <svg class="w-4 h-4 lg:w-3.5 lg:h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="hidden lg:inline">Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <span>Belum ada PON terdaftar di {{ $selectedOlt->nama_olt }}. Silakan tambahkan melalui form di atas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pons->hasPages())
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $pons->links() }}
                </div>
            @endif
        </div>
    </div>

    @if ($canManage)
        <!-- Modal Edit PON -->
        <div id="editPonModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm">Edit Data PON</h3>
                    <button type="button" onclick="closeEditPonModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="editPonForm" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama PON</label>
                        <input type="text" id="edit_nama_pon" name="nama_pon" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Port Maks</label>
                        <select id="edit_port_max" name="port_max" required 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                            <option value="2">2 Port</option>
                            <option value="4">4 Port</option>
                            <option value="8">8 Port</option>
                            <option value="16">16 Port</option>
                            <option value="24">24 Port</option>
                            <option value="32">32 Port</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="closeEditPonModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" style="background: #0f4c5c !important; color: #ffffff !important;" class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openEditPonModal(id, nama, max) {
                const modal = document.getElementById('editPonModal');
                const form = document.getElementById('editPonForm');
                form.action = `/gomsn/network/pons/${id}`;
                document.getElementById('edit_nama_pon').value = nama;
                document.getElementById('edit_port_max').value = max;
                modal.classList.remove('hidden');
            }

            function closeEditPonModal() {
                document.getElementById('editPonModal').classList.add('hidden');
            }
        </script>
    @endif
</x-app-layout>
