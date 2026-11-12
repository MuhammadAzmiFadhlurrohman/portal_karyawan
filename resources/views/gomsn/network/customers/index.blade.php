<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching GoMSN Dashboard & Roster Shift) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Back Button + Glowing Icon + Title / Path -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <a href="{{ route('gomsn.network.pons.show', ['olt' => $olt->id, 'pon' => $pon->id]) }}"
                   class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center shrink-0 shadow-xs transition"
                   title="Kembali ke Daftar ODP">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            ODP: {{ $odp->nama_odp }}
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
                        {{ $olt->nama_olt }} &rsaquo; {{ $pon->nama_pon }} &bull; Port Terpakai: {{ $customers->total() }} / {{ $odp->port_max }} Pelanggan
                    </p>
                </div>
            </div>

            <!-- Right Controls: Badges & Action Buttons -->
            <div class="relative z-10 flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-3 bg-black/25 backdrop-blur-md border border-white/15 px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-xs">
                    <span class="flex items-center gap-1.5" title="Kapasitas Port Terpakai">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">Port</span>
                        <span class="font-extrabold text-white">{{ $customers->total() }}/{{ $odp->port_max }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="flex items-center gap-1.5 text-emerald-300" title="Port Tersedia">
                        <span class="text-teal-200/80 text-[10px] uppercase font-bold tracking-wider">Tersedia</span>
                        <span class="font-extrabold text-emerald-300">{{ max(0, $odp->port_max - $customers->total()) }} Port</span>
                    </span>
                </div>

                <button type="button" onclick="openHistoryModal()"
                        style="background: #0284c7 !important; color: #ffffff !important;"
                        class="px-3.5 py-2 rounded-xl font-bold text-xs shadow-xs hover:opacity-90 transition-all flex items-center gap-1.5 border border-sky-400/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Port</span>
                </button>

                <a href="{{ route('gomsn.network.pons.show', ['olt' => $olt->id, 'pon' => $pon->id]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Card Tambah User Baru (Hanya Muncul untuk Admin / Atasan Teknisi) -->
        @if ($canManage)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5 text-slate-800 font-bold text-xs sm:text-sm">
                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200/60 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span>Tambah User Baru ke ODP {{ $odp->nama_odp }}</span>
                    </div>

                    <div class="text-[11px] font-semibold text-slate-500">
                        Kapasitas Port Terisi: <span class="font-extrabold text-teal-600 font-mono">{{ $customers->total() }} / {{ $odp->port_max }}</span>
                    </div>
                </div>

                <form action="{{ route('gomsn.network.customers.store', $odp) }}" method="POST" class="p-4 sm:p-5">
                    @csrf
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 sm:gap-4">
                        <!-- Nomor Internet -->
                        <div class="w-full sm:w-56 shrink-0">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <span class="font-mono text-slate-400 font-bold">#</span>
                                <span>Nomor Internet</span>
                            </label>
                            <input type="text" name="nomor_internet" required placeholder="Contoh: 123456" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs">
                        </div>

                        <!-- Nama User -->
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Nama Lengkap User</span>
                            </label>
                            <input type="text" name="nama_user" required placeholder="Nama lengkap pelanggan" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs">
                        </div>

                        <!-- Keterangan -->
                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Keterangan</span>
                            </label>
                            <input type="text" name="keterangan" placeholder="Opsional (cth: ONT ZTE, drop core)" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all shadow-2xs">
                        </div>

                        <!-- Tombol Tambah User -->
                        <div class="w-full sm:w-auto shrink-0">
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #10b981 0%, #0d9488 100%) !important; color: #ffffff !important;"
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-xs hover:shadow-md transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah User</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Notice Read-Only Mode -->
            <div class="bg-sky-50/70 border border-sky-200/80 rounded-2xl p-3 flex items-center gap-2.5 text-sky-800 text-xs">
                <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Anda masuk dalam <strong>Mode Monitoring</strong>. Penambahan, edit, dan hapus user pelanggan dikhususkan untuk <strong>Atasan Teknisi</strong> dan <strong>Admin</strong>.</span>
            </div>
        @endif

        <!-- Tabel Daftar Pelanggan (Rapi & Proporsional dengan Horizontal Scrolling yang Mulus) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr style="background: linear-gradient(90deg, #0f4c5c 0%, #007f8c 50%, #0d9488 100%) !important; color: #ffffff !important;" 
                            class="text-[11px] font-bold tracking-wider uppercase text-white select-none whitespace-nowrap">
                            <th class="py-3 px-3.5 w-12 text-center">#</th>
                            <th class="py-3 px-4 min-w-[200px]">PELANGGAN</th>
                            <th class="py-3 px-4 min-w-[140px]">LAYANAN</th>
                            <th class="py-3 px-4 min-w-[180px]">ALAMAT</th>
                            <th class="py-3 px-4 min-w-[110px]">NOMOR / BW</th>
                            <th class="py-3 px-3 text-center w-20">OLT</th>
                            <th class="py-3 px-4 min-w-[130px]">NOTE (PORT)</th>
                            <th class="py-3 px-4 min-w-[140px]">KETERANGAN</th>
                            <th class="py-3 px-3 text-center w-24">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($customers as $c)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Nomor Urut -->
                                <td class="py-3 px-3.5 text-center text-slate-400 font-mono font-semibold">
                                    {{ $customers->firstItem() ? $customers->firstItem() + $loop->index : $loop->iteration }}
                                </td>

                                <!-- Pelanggan: Nomor Internet, Nama, Status Badge -->
                                <td class="py-3 px-4">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-[10px] text-slate-400 font-mono font-medium">{{ $c->nomor_internet }}</span>
                                        <span class="font-bold text-slate-800 text-xs tracking-tight">{{ $c->nama_user }}</span>
                                        <div>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-600 border border-emerald-200/60">
                                                {{ $c->status ?? 'AKTIF' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Layanan -->
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ $c->layanan ?: 'BROADBAND FREE' }}
                                </td>

                                <!-- Alamat -->
                                <td class="py-3 px-4 text-slate-600">
                                    <div class="max-w-[220px] truncate" title="{{ $c->alamat }}">
                                        {{ $c->alamat ?: '-' }}
                                    </div>
                                </td>

                                <!-- Nomor / Bandwidth -->
                                <td class="py-3 px-4 text-slate-700 font-mono text-xs">
                                    {{ $c->nomor ?: '-' }}
                                </td>

                                <!-- OLT Badge -->
                                <td class="py-3 px-3 text-center">
                                    <span style="background: #2563eb !important; color: #ffffff !important;" 
                                          class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase shadow-2xs">
                                        {{ $c->olt_name ?: $olt->kode ?: 'MSN' }}
                                    </span>
                                </td>

                                <!-- Note ZTE/Port (styled red text like original) -->
                                <td class="py-3 px-4 text-rose-600 font-mono text-[11px] font-semibold break-words">
                                    {{ $c->note ?: '-' }}
                                </td>

                                <!-- Keterangan -->
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    <div class="max-w-[180px] truncate" title="{{ $c->keterangan }}">
                                        {{ $c->keterangan ?: '-' }}
                                    </div>
                                </td>

                                <!-- Aksi Buttons -->
                                <td class="py-3 px-3 text-center">
                                    @if ($canManage)
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Edit -->
                                            <button type="button" 
                                                    onclick="openEditCustomerModal({{ $c->id }}, '{{ addslashes($c->nomor_internet) }}', '{{ addslashes($c->nama_user) }}', '{{ addslashes($c->keterangan ?? '') }}', '{{ addslashes($c->layanan ?? '') }}', '{{ addslashes($c->alamat ?? '') }}', '{{ addslashes($c->nomor ?? '') }}', '{{ addslashes($c->note ?? '') }}')"
                                                    style="background: #f59e0b !important; color: #ffffff !important;"
                                                    class="p-1.5 rounded-lg shadow-2xs hover:opacity-90 transition-all text-white" title="Edit Pelanggan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>

                                            <!-- Hapus -->
                                            <form action="{{ route('gomsn.network.customers.destroy', $c) }}" method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ addslashes($c->nama_user) }} dari ODP ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        style="background: #ef4444 !important; color: #ffffff !important;"
                                                        class="p-1.5 rounded-lg shadow-2xs hover:opacity-90 transition-all text-white" title="Hapus Pelanggan">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>Belum ada pelanggan terhubung di ODP {{ $odp->nama_odp }}. Silakan tambahkan melalui form di atas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Riwayat Log (History) -->
    <div id="historyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh] animate-in fade-in zoom-in-95 duration-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Riwayat Aktivitas ODP: {{ $odp->nama_odp }}</h3>
                        <p class="text-[11px] text-slate-500">Log mutasi penambahan, perubahan, dan pencabutan port</p>
                    </div>
                </div>
                <button type="button" onclick="closeHistoryModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-3 flex-1">
                @forelse ($histories as $h)
                    @php
                        $badgeStyle = match(strtolower($h->aksi)) {
                            'tambah' => 'background-color: #ecfdf5; color: #047857; border-color: #a7f3d0;',
                            'edit' => 'background-color: #fffbeb; color: #b45309; border-color: #fde68a;',
                            'hapus' => 'background-color: #fff1f2; color: #be123c; border-color: #fecdd3;',
                            default => 'background-color: #f8fafc; color: #334155; border-color: #e2e8f0;',
                        };
                    @endphp
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 flex items-start justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase border" style="{{ $badgeStyle }}">
                                    {{ $h->aksi }}
                                </span>
                                <span class="font-bold text-slate-800">{{ $h->nama_user }}</span>
                                <span class="font-mono text-slate-500 text-[11px]">({{ $h->nomor_internet }})</span>
                            </div>
                            @if ($h->keterangan)
                                <p class="text-slate-600 text-[11px]">{{ $h->keterangan }}</p>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium shrink-0 text-right">
                            {{ $h->waktu_input ? $h->waktu_input->format('d M Y, H:i') : ($h->created_at ? $h->created_at->format('d M Y, H:i') : '-') }}
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        Belum ada riwayat aktivitas yang tercatat untuk ODP ini.
                    </div>
                @endforelse
            </div>

            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button type="button" onclick="closeHistoryModal()" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @if ($canManage)
        <!-- Modal Edit Pelanggan -->
        <div id="editCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm">Edit Data Pelanggan ODP</h3>
                    <button type="button" onclick="closeEditCustomerModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="editCustomerForm" method="POST" class="p-5 space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Internet</label>
                            <input type="text" id="edit_nomor_internet" name="nomor_internet" required 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama User</label>
                            <input type="text" id="edit_nama_user" name="nama_user" required 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Layanan</label>
                            <input type="text" id="edit_layanan" name="layanan" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor / Bandwidth</label>
                            <input type="text" id="edit_nomor" name="nomor" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat</label>
                        <input type="text" id="edit_alamat" name="alamat" 
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Note (ZTE/Port)</label>
                            <input type="text" id="edit_note" name="note" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan</label>
                            <input type="text" id="edit_keterangan" name="keterangan" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="closeEditCustomerModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" style="background: #0f4c5c !important; color: #ffffff !important;" class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openHistoryModal() {
                document.getElementById('historyModal').classList.remove('hidden');
            }

            function closeHistoryModal() {
                document.getElementById('historyModal').classList.add('hidden');
            }

            function openEditCustomerModal(id, noInternet, nama, ket, layanan, alamat, no, note) {
                const modal = document.getElementById('editCustomerModal');
                const form = document.getElementById('editCustomerForm');
                form.action = `/gomsn/network/customers/${id}`;
                document.getElementById('edit_nomor_internet').value = noInternet;
                document.getElementById('edit_nama_user').value = nama;
                document.getElementById('edit_keterangan').value = ket;
                document.getElementById('edit_layanan').value = layanan;
                document.getElementById('edit_alamat').value = alamat;
                document.getElementById('edit_nomor').value = no;
                document.getElementById('edit_note').value = note;
                modal.classList.remove('hidden');
            }

            function closeEditCustomerModal() {
                document.getElementById('editCustomerModal').classList.add('hidden');
            }
        </script>
    @endif
</x-app-layout>
