<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching GoMSN Dashboard & Roster Shift) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Back Button + Glowing Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}"
                   class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center shrink-0 shadow-xs transition"
                   title="Kembali ke Daftar PON">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Distribusi ODP: {{ $pon->nama_pon }}
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
                        {{ $olt->nama_olt }} &bull; Titik distribusi Optical Distribution Point &amp; kapasitas port pelanggan
                    </p>
                </div>
            </div>

            <!-- Right Controls: Badges & Back Button -->
            <div class="relative z-10 flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-3 bg-black/25 backdrop-blur-md border border-white/15 px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-xs">
                    <span class="flex items-center gap-1.5" title="OLT Induk">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">OLT</span>
                        <span class="font-extrabold text-white">{{ $olt->nama_olt }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="flex items-center gap-1.5" title="Total ODP di PON ini">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">ODP</span>
                        <span class="font-extrabold text-white">{{ $odps->total() }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="flex items-center gap-1.5 text-emerald-300" title="Kapasitas Maksimal PON">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">Kapasitas Maks</span>
                        <span class="font-extrabold text-emerald-300">{{ $pon->port_max }} Port</span>
                    </span>
                </div>

                <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke PON</span>
                </a>
            </div>
        </div>

        <!-- Card Tambah ODP Baru (Hanya Muncul untuk Admin / Atasan Teknisi) -->
        @if ($canManage)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5 text-slate-800 font-bold text-xs sm:text-sm">
                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200/60 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span>Tambah ODP Baru pada {{ $pon->nama_pon }}</span>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400 hidden sm:inline">Tentukan kapasitas port &amp; koordinat Google Maps</span>
                </div>

                <form action="{{ route('gomsn.network.odps.store', $pon) }}" method="POST" class="p-4 sm:p-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 items-end">
                        <!-- Nama ODP -->
                        <div class="lg:col-span-4">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                <span>Nama ODP</span>
                            </label>
                            <input type="text" name="nama_odp" required placeholder="Contoh: ODP-001 atau JEMBATAN TOL" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs">
                        </div>

                        <!-- Maks User -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Maks User</span>
                            </label>
                            <select name="port_max" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all bg-white shadow-2xs">
                                <option value="4">4 User</option>
                                <option value="8" selected>8 User</option>
                                <option value="16">16 User</option>
                                <option value="20">20 User</option>
                                <option value="24">24 User</option>
                                <option value="32">32 User</option>
                            </select>
                        </div>

                        <!-- Latitude -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Latitude</span>
                            </label>
                            <input type="text" name="latitude" placeholder="-6.92976" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs">
                        </div>

                        <!-- Longitude -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Longitude</span>
                            </label>
                            <input type="text" name="longitude" placeholder="107.5933" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs">
                        </div>

                        <!-- Tombol Tambah ODP -->
                        <div class="lg:col-span-2">
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #10b981 0%, #0d9488 100%) !important; color: #ffffff !important;"
                                    class="w-full py-2.5 px-4 rounded-xl text-white font-bold text-xs sm:text-sm shadow-xs hover:shadow-md transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah ODP</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Notice Read-Only Mode -->
            <div class="bg-sky-50/70 border border-sky-200/80 rounded-2xl p-3 flex items-center gap-2.5 text-sky-800 text-xs">
                <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Anda masuk dalam <strong>Mode Monitoring</strong>. Hak akses menambah, mengedit, dan menghapus ODP dikhususkan untuk <strong>Atasan Teknisi</strong> dan <strong>Admin</strong>.</span>
            </div>
        @endif

        <!-- Tabel Daftar ODP (Desain Rapi & Proporsional 5 Kolom) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr style="background: linear-gradient(90deg, #0f4c5c 0%, #007f8c 50%, #0d9488 100%) !important; color: #ffffff !important;" 
                            class="text-[11px] font-bold tracking-wider uppercase text-white select-none">
                            <th class="py-3 px-4 w-14 text-center">#</th>
                            <th class="py-3 px-4 min-w-[260px]">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                    <span>NAMA ODP &amp; LOKASI</span>
                                </div>
                            </th>
                            <th class="py-3 px-4 w-72 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>USER TERHUBUNG &amp; KAPASITAS</span>
                                </div>
                            </th>
                            <th class="py-3 px-4 w-36 text-center">STATUS</th>
                            <th class="py-3 px-4 w-52 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>AKSI</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($odps as $odp)
                            @php
                                $userCount = $odp->customers_count ?? 0;
                                $max = $odp->port_max ?: 8;
                                $pct = min(100, round(($userCount / $max) * 100));
                                $barBg = $pct >= 90 ? 'background-color: #ef4444;' : ($pct >= 60 ? 'background-color: #f59e0b;' : 'background-color: #10b981;');
                                $textClass = $pct >= 90 ? 'text-rose-600' : ($pct >= 60 ? 'text-amber-600' : 'text-emerald-600');

                                if ($userCount == 0) {
                                    $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                                    $badgeText = 'Kosong';
                                } elseif ($pct >= 90) {
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
                                <!-- Nomor Urut -->
                                <td class="py-3 px-4 text-center text-slate-400 font-mono font-semibold">
                                    {{ $odps->firstItem() ? $odps->firstItem() + $loop->index : $loop->iteration }}
                                </td>

                                <!-- Nama ODP & Lokasi GPS -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-xs tracking-tight uppercase">
                                                {{ $odp->nama_odp }}
                                            </div>
                                            @if ($odp->latitude && $odp->longitude && ($odp->latitude != 0 || $odp->longitude != 0))
                                                <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank"
                                                   class="text-[11px] text-teal-600 hover:text-teal-700 hover:underline flex items-center gap-1 mt-0.5">
                                                    <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span>{{ $odp->latitude }}, {{ $odp->longitude }}</span>
                                                </a>
                                            @else
                                                <div class="text-[11px] text-slate-400 mt-0.5">
                                                    {{ $pon->nama_pon }} &bull; Koordinat belum diset
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Progress Bar Kapasitas Port ODP -->
                                <td class="py-3 px-4">
                                    <div class="w-full max-w-[220px] mx-auto">
                                        <div class="flex items-center justify-between text-[11px] font-semibold mb-1">
                                            <span class="font-mono {{ $textClass }}">{{ $userCount }} / {{ $max }} User</span>
                                            <span class="font-mono text-slate-500 text-[10px]">{{ $pct }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200/60">
                                            <div class="h-full rounded-full transition-all" style="width: {{ $pct }}%; {{ $barBg }}"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </td>

                                <!-- Aksi Buttons -->
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Lihat (Semua user bisa lihat) -->
                                        <a href="{{ route('gomsn.network.odps.show', ['olt' => $olt->id, 'pon' => $pon->id, 'odp' => $odp->id]) }}" 
                                           style="background: #0f4c5c !important; color: #ffffff !important;"
                                           class="px-2.5 py-1 rounded-lg text-white text-[11px] font-semibold shadow-2xs hover:opacity-90 transition-all flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat</span>
                                        </a>

                                        @if ($canManage)
                                            <!-- Edit Trigger -->
                                            <button type="button" 
                                                    onclick="openEditOdpModal({{ $odp->id }}, '{{ addslashes($odp->nama_odp) }}', {{ $odp->port_max }}, '{{ $odp->latitude }}', '{{ $odp->longitude }}')"
                                                    style="background: #f59e0b !important; color: #ffffff !important;"
                                                    class="px-2.5 py-1 rounded-lg text-white text-[11px] font-semibold shadow-2xs hover:opacity-90 transition-all flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Hapus -->
                                            <form action="{{ route('gomsn.network.odps.destroy', $odp) }}" method="POST" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus ODP {{ addslashes($odp->nama_odp) }}? Semua user terkait juga akan terhapus!');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        style="background: #ef4444 !important; color: #ffffff !important;"
                                                        class="px-2.5 py-1 rounded-lg text-white text-[11px] font-semibold shadow-2xs hover:opacity-90 transition-all flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Hapus</span>
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
                                        <span>Belum ada ODP terdaftar di PON {{ $pon->nama_pon }}. Silakan tambahkan melalui form di atas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($odps->hasPages())
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $odps->links() }}
                </div>
            @endif
        </div>
    </div>

    @if ($canManage)
        <!-- Modal Edit ODP -->
        <div id="editOdpModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm">Edit Data ODP</h3>
                    <button type="button" onclick="closeEditOdpModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="editOdpForm" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama ODP</label>
                        <input type="text" id="edit_nama_odp" name="nama_odp" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Maks User</label>
                        <select id="edit_port_max" name="port_max" required 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                            <option value="4">4 User</option>
                            <option value="8">8 User</option>
                            <option value="16">16 User</option>
                            <option value="20">20 User</option>
                            <option value="24">24 User</option>
                            <option value="32">32 User</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Latitude</label>
                            <input type="text" id="edit_latitude" name="latitude" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Longitude</label>
                            <input type="text" id="edit_longitude" name="longitude" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="closeEditOdpModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" style="background: #0f4c5c !important; color: #ffffff !important;" class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openEditOdpModal(id, nama, max, lat, lng) {
                const modal = document.getElementById('editOdpModal');
                const form = document.getElementById('editOdpForm');
                form.action = `/gomsn/network/odps/${id}`;
                document.getElementById('edit_nama_odp').value = nama;
                document.getElementById('edit_port_max').value = max;
                document.getElementById('edit_latitude').value = lat;
                document.getElementById('edit_longitude').value = lng;
                modal.classList.remove('hidden');
            }

            function closeEditOdpModal() {
                document.getElementById('editOdpModal').classList.add('hidden');
            }
        </script>
    @endif
</x-app-layout>
