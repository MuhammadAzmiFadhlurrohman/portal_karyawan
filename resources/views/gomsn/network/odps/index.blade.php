<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Breadcrumb Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-5 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>

            <!-- Left: Breadcrumb Navigation -->
            <div class="relative z-10 flex items-center gap-2.5 min-w-0 text-white font-bold text-sm sm:text-base">
                <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}" class="hover:text-cyan-300 transition-colors flex items-center gap-1.5 shrink-0 text-cyan-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>{{ $olt->nama_olt }}</span>
                </a>
                <span class="text-cyan-400/60">›</span>
                <span class="text-white flex items-center gap-1.5 truncate">
                    <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>{{ $pon->nama_pon }}</span>
                </span>
            </div>

            <div class="relative z-10 flex items-center gap-2 shrink-0">
                <span class="px-3 py-1 rounded-xl bg-white/10 text-cyan-200 text-xs font-semibold border border-white/10">
                    {{ $odps->total() }} Titik ODP
                </span>
                <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke PON</span>
                </a>
            </div>
        </div>

        <!-- Card Tambah ODP Baru (Hanya Muncul untuk Admin / Atasan Teknisi) -->
        @if ($canManage)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2 text-slate-800 font-bold text-sm">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span>Tambah ODP Baru pada {{ $pon->nama_pon }}</span>
                </div>

                <form action="{{ route('gomsn.network.odps.store', $pon) }}" method="POST" class="p-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                        <!-- Nama ODP -->
                        <div class="lg:col-span-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                Nama ODP
                            </label>
                            <input type="text" name="nama_odp" required placeholder="Contoh: ODP-001" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Maks User -->
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Maks User
                            </label>
                            <select name="port_max" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all bg-white">
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
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Latitude
                            </label>
                            <input type="text" name="latitude" placeholder="-6.92976" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Longitude -->
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Longitude
                            </label>
                            <input type="text" name="longitude" placeholder="107.5933" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Action Buttons -->
                        <div class="lg:col-span-3 flex items-center gap-2">
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #10b981 0%, #0d9488 100%) !important; color: #ffffff !important;"
                                    class="flex-1 py-2.5 px-3 rounded-xl text-white font-bold text-xs sm:text-sm shadow-sm hover:shadow transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah ODP</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Notice Read-Only Mode -->
            <div class="bg-sky-50/70 border border-sky-200/80 rounded-2xl p-4 flex items-center gap-3 text-sky-800 text-xs">
                <svg class="w-5 h-5 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Anda masuk dalam <strong>Mode Monitoring</strong>. Penambahan, edit, dan hapus ODP dikhususkan untuk <strong>Atasan Teknisi</strong> dan <strong>Admin</strong>.</span>
            </div>
        @endif

        <!-- Tabel Daftar ODP -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Gradient Table Header -->
            <div style="background: linear-gradient(90deg, #0f4c5c 0%, #007f8c 50%, #0d9488 100%) !important; color: #ffffff !important;" class="px-6 py-4 text-white">
                <div class="grid grid-cols-12 items-center text-xs font-bold tracking-wider uppercase">
                    <div class="col-span-6 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                        <span>NAMA ODP</span>
                    </div>
                    <div class="col-span-3 text-center flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>USER TERHUBUNG</span>
                    </div>
                    <div class="col-span-3 text-right flex items-center justify-end gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>AKSI</span>
                    </div>
                </div>
            </div>

            <!-- Table Rows -->
            <div class="divide-y divide-slate-100">
                @forelse ($odps as $odp)
                    @php
                        $userCount = $odp->customers_count ?? 0;
                        $max = $odp->port_max ?: 8;
                        $pct = min(100, round(($userCount / $max) * 100));
                        $barColor = $pct >= 90 ? 'background-color: #ef4444;' : ($pct >= 60 ? 'background-color: #f59e0b;' : 'background-color: #10b981;');
                        $textClass = $pct >= 90 ? 'text-rose-600' : ($pct >= 60 ? 'text-amber-600' : 'text-emerald-600');
                    @endphp
                    <div class="px-6 py-4 grid grid-cols-12 items-center hover:bg-slate-50/80 transition-colors">
                        <!-- Nama ODP & Lokasi GPS -->
                        <div class="col-span-6 flex flex-col">
                            <span class="font-bold text-slate-800 text-sm uppercase tracking-wide">
                                {{ $odp->nama_odp }}
                            </span>
                            @if ($odp->latitude && $odp->longitude && ($odp->latitude != 0 || $odp->longitude != 0))
                                <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank"
                                   class="text-[11px] text-teal-600 hover:text-teal-700 hover:underline flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $odp->latitude }}, {{ $odp->longitude }}</span>
                                </a>
                            @endif
                        </div>

                        <!-- Progress Bar User/Port -->
                        <div class="col-span-3 flex flex-col items-center justify-center">
                            <span class="text-xs font-extrabold {{ $textClass }} mb-1">
                                {{ $userCount }}/{{ $max }}
                            </span>
                            <div class="w-16 h-1.5 bg-slate-200/80 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width: {{ $pct }}%; {{ $barColor }}"></div>
                            </div>
                        </div>

                        <!-- Aksi Buttons -->
                        <div class="col-span-3 flex items-center justify-end gap-2">
                            <!-- Lihat (Semua user bisa lihat) -->
                            <a href="{{ route('gomsn.network.odps.show', ['olt' => $olt->id, 'pon' => $pon->id, 'odp' => $odp->id]) }}" 
                               style="background: #0f4c5c !important; color: #ffffff !important;"
                               class="px-3.5 py-1.5 rounded-xl text-white text-xs font-bold shadow-xs hover:opacity-90 transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat</span>
                            </a>

                            @if ($canManage)
                                <!-- Edit Trigger -->
                                <button type="button" 
                                        onclick="openEditOdpModal({{ $odp->id }}, '{{ addslashes($odp->nama_odp) }}', {{ $odp->port_max }}, '{{ $odp->latitude }}', '{{ $odp->longitude }}')"
                                        style="background: #f59e0b !important; color: #ffffff !important;"
                                        class="px-3.5 py-1.5 rounded-xl text-white text-xs font-bold shadow-xs hover:opacity-90 transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- Hapus -->
                                <form action="{{ route('gomsn.network.odps.destroy', $odp) }}" method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus ODP {{ addslashes($odp->nama_odp) }}? Semua user terkait juga akan terhapus!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            style="background: #ef4444 !important; color: #ffffff !important;"
                                            class="px-3.5 py-1.5 rounded-xl text-white text-xs font-bold shadow-xs hover:opacity-90 transition-all flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Belum ada ODP terdaftar di PON {{ $pon->nama_pon }}.
                    </div>
                @endforelse
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
