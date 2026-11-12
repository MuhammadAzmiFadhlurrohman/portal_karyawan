<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
        <!-- OLT Switcher & Breadcrumb Bar -->
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 text-slate-800 font-bold text-base tracking-tight">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span>{{ $selectedOlt->nama_olt }}</span>
            </div>

            <!-- OLT Tab Switcher -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                @foreach ($olts as $olt)
                    <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $olt->id === $selectedOlt->id ? 'bg-gradient-to-r from-[#0f4c5c] to-[#0d9488] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' }}">
                        {{ $olt->nama_olt }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Card Tambah PON Baru -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2 text-slate-800 font-bold text-sm">
                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span>Tambah PON Baru</span>
            </div>

            <form action="{{ route('gomsn.network.pons.store', $selectedOlt) }}" method="POST" class="p-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-7">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Nama PON
                        </label>
                        <input type="text" name="nama_pon" required placeholder="Contoh: PON-001" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Port Maks
                        </label>
                        <select name="port_max" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all bg-white">
                            <option value="2">2 Port</option>
                            <option value="4">4 Port</option>
                            <option value="8" selected>8 Port</option>
                            <option value="16">16 Port</option>
                            <option value="24">24 Port</option>
                            <option value="32">32 Port</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" 
                                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah PON</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar PON -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Gradient Table Header -->
            <div class="bg-gradient-to-r from-[#0f4c5c] via-[#007f8c] to-[#0d9488] px-6 py-4 text-white">
                <div class="grid grid-cols-12 items-center text-xs font-bold tracking-wider uppercase">
                    <div class="col-span-6 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>NAMA PON</span>
                    </div>
                    <div class="col-span-3 text-center flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>PORT</span>
                    </div>
                    <div class="col-span-3 text-right flex items-center justify-end gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>AKSI</span>
                    </div>
                </div>
            </div>

            <!-- Table Rows -->
            <div class="divide-y divide-slate-100">
                @forelse ($pons as $pon)
                    @php
                        $usage = $pon->odps_count ?? 0;
                        $max = $pon->port_max ?: 8;
                        $pct = min(100, round(($usage / $max) * 100));
                        $barColor = $pct >= 90 ? 'bg-rose-500' : ($pct >= 60 ? 'bg-amber-500' : 'bg-emerald-500');
                        $textColor = $pct >= 90 ? 'text-rose-600' : ($pct >= 60 ? 'text-amber-600' : 'text-emerald-600');
                    @endphp
                    <div class="px-6 py-4 grid grid-cols-12 items-center hover:bg-slate-50/70 transition-colors">
                        <!-- Nama PON -->
                        <div class="col-span-6 font-bold text-slate-800 text-sm uppercase tracking-wide">
                            {{ $pon->nama_pon }}
                        </div>

                        <!-- Progress Bar Port -->
                        <div class="col-span-3 flex flex-col items-center justify-center">
                            <span class="text-xs font-extrabold {{ $textColor }} mb-1">
                                {{ $usage }}/{{ $max }}
                            </span>
                            <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        <!-- Aksi Buttons -->
                        <div class="col-span-3 flex items-center justify-end gap-2">
                            <!-- Lihat -->
                            <a href="{{ route('gomsn.network.pons.show', ['olt' => $selectedOlt->id, 'pon' => $pon->id]) }}" 
                               class="px-3.5 py-1.5 rounded-xl bg-[#0f4c5c] hover:bg-[#007f8c] text-white text-xs font-bold shadow-xs hover:shadow transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat</span>
                            </a>

                            <!-- Edit Trigger -->
                            <button type="button" 
                                    onclick="openEditPonModal({{ $pon->id }}, '{{ addslashes($pon->nama_pon) }}', {{ $pon->port_max }})"
                                    class="px-3.5 py-1.5 rounded-xl bg-[#f59e0b] hover:bg-[#d97706] text-white text-xs font-bold shadow-xs hover:shadow transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit</span>
                            </button>

                            <!-- Hapus -->
                            <form action="{{ route('gomsn.network.pons.destroy', $pon) }}" method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus PON {{ addslashes($pon->nama_pon) }}? Semua ODP terkait juga akan terhapus!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-3.5 py-1.5 rounded-xl bg-[#ef4444] hover:bg-[#dc2626] text-white text-xs font-bold shadow-xs hover:shadow transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Belum ada PON terdaftar di {{ $selectedOlt->nama_olt }}. Silakan tambahkan melalui form di atas.
                    </div>
                @endforelse
            </div>

            @if ($pons->hasPages())
                <div class="px-6 py-3 border-t border-slate-100">
                    {{ $pons->links() }}
                </div>
            @endif
        </div>
    </div>

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
                    <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm">Simpan Perubahan</button>
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
</x-app-layout>
