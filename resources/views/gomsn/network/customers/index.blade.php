<x-app-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Breadcrumb Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-5 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>

            <!-- Left: Breadcrumb Navigation -->
            <div class="relative z-10 flex flex-wrap items-center gap-2 text-white font-bold text-sm sm:text-base">
                <a href="{{ route('gomsn.network.index', ['olt_id' => $olt->id]) }}" class="hover:text-cyan-300 transition-colors flex items-center gap-1.5 shrink-0 text-cyan-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>{{ $olt->nama_olt }}</span>
                </a>
                <span class="text-cyan-400/60">›</span>
                <a href="{{ route('gomsn.network.pons.show', ['olt' => $olt->id, 'pon' => $pon->id]) }}" class="hover:text-cyan-300 transition-colors flex items-center gap-1.5 shrink-0 text-cyan-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>{{ $pon->nama_pon }}</span>
                </a>
                <span class="text-cyan-400/60">›</span>
                <span class="text-white flex items-center gap-1.5 truncate">
                    <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    <span>ODP {{ $odp->nama_odp }}</span>
                </span>
            </div>

            <div class="relative z-10 flex items-center gap-2 shrink-0">
                <button type="button" onclick="openHistoryModal()"
                        style="background: #0284c7 !important; color: #ffffff !important;"
                        class="px-3.5 py-1.5 rounded-xl font-bold text-xs shadow-sm hover:opacity-90 transition-all flex items-center gap-1.5 border border-sky-400/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Port</span>
                </button>
                <a href="{{ route('gomsn.network.pons.show', ['olt' => $olt->id, 'pon' => $pon->id]) }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/20 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke ODP</span>
                </a>
            </div>
        </div>

        <!-- Card Tambah User Baru (Hanya Muncul untuk Admin / Atasan Teknisi) -->
        @if ($canManage)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between text-slate-800 font-bold text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span>Tambah User Baru ke ODP {{ $odp->nama_odp }}</span>
                    </div>

                    <div class="text-[11px] font-semibold text-slate-500">
                        Kapasitas Port: <span class="font-extrabold text-teal-600">{{ $odp->customers()->count() }} / {{ $odp->port_max }}</span>
                    </div>
                </div>

                <form action="{{ route('gomsn.network.customers.store', $odp) }}" method="POST" class="p-3.5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-end">
                        <!-- Nomor Internet -->
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1 flex items-center gap-1">
                                <span class="font-bold text-slate-400">#</span>
                                Nomor Internet
                            </label>
                            <input type="text" name="nomor_internet" required placeholder="Contoh: 123456" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Nama User -->
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Nama User
                            </label>
                            <input type="text" name="nama_user" required placeholder="Nama lengkap" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Keterangan -->
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Keterangan
                            </label>
                            <input type="text" name="keterangan" placeholder="Opsional" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                        </div>

                        <!-- Tombol Tambah -->
                        <div class="md:col-span-2">
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #10b981 0%, #0d9488 100%) !important; color: #ffffff !important;"
                                    class="w-full py-1.5 px-3 rounded-lg text-white font-bold text-xs shadow-2xs hover:shadow transition-all flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
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

        <!-- Tabel Daftar Pelanggan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Gradient Table Header -->
            <div style="background: linear-gradient(90deg, #0f4c5c 0%, #007f8c 50%, #0d9488 100%) !important; color: #ffffff !important;" class="px-5 py-2.5 text-white overflow-x-auto">
                <div class="grid grid-cols-12 items-center text-[11px] font-bold tracking-wider uppercase min-w-[760px]">
                    <div class="col-span-3">PELANGGAN</div>
                    <div class="col-span-2">LAYANAN</div>
                    <div class="col-span-2">ALAMAT</div>
                    <div class="col-span-1">NOMOR</div>
                    <div class="col-span-1">OLT</div>
                    <div class="col-span-1">NOTE</div>
                    <div class="col-span-1">KETERANGAN</div>
                    <div class="col-span-1 text-right">AKSI</div>
                </div>
            </div>

            <!-- Table Rows -->
            <div class="divide-y divide-slate-100 overflow-x-auto">
                @forelse ($customers as $c)
                    <div class="px-5 py-2 grid grid-cols-12 items-center hover:bg-slate-50/80 transition-colors min-w-[760px] text-xs">
                        <!-- Pelanggan: Nomor Internet, Nama, Status Badge -->
                        <div class="col-span-3 flex flex-col gap-0.5">
                            <span class="text-[10px] text-slate-400 font-mono font-medium">{{ $c->nomor_internet }}</span>
                            <span class="font-bold text-slate-800 text-xs tracking-tight">{{ $c->nama_user }}</span>
                            <div>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-600 border border-emerald-200/60">
                                    {{ $c->status ?? 'AKTIF' }}
                                </span>
                            </div>
                        </div>

                        <!-- Layanan -->
                        <div class="col-span-2 text-slate-700 font-medium text-xs">
                            {{ $c->layanan ?: 'BROADBAND FREE' }}
                        </div>

                        <!-- Alamat -->
                        <div class="col-span-2 text-slate-600 truncate pr-2 text-xs">
                            {{ $c->alamat ?: '-' }}
                        </div>

                        <!-- Nomor -->
                        <div class="col-span-1 text-slate-700 font-medium text-xs">
                            {{ $c->nomor ?: '-' }}
                        </div>

                        <!-- OLT Badge -->
                        <div class="col-span-1">
                            <span style="background: #2563eb !important; color: #ffffff !important;" class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase shadow-2xs">
                                {{ $c->olt_name ?: $olt->kode ?: 'MSN' }}
                            </span>
                        </div>

                        <!-- Note (styled red text like screenshot) -->
                        <div class="col-span-1 text-rose-600 font-mono text-[10px] font-semibold break-words">
                            {{ $c->note ?: '-' }}
                        </div>

                        <!-- Keterangan -->
                        <div class="col-span-1 text-slate-500 text-[10px] truncate">
                            {{ $c->keterangan ?: '-' }}
                        </div>

                        <!-- Aksi Buttons -->
                        <div class="col-span-1 flex items-center justify-end gap-1">
                            @if ($canManage)
                                <button type="button" 
                                        onclick="openEditCustomerModal({{ $c->id }}, '{{ addslashes($c->nomor_internet) }}', '{{ addslashes($c->nama_user) }}', '{{ addslashes($c->keterangan ?? '') }}', '{{ addslashes($c->layanan ?? '') }}', '{{ addslashes($c->alamat ?? '') }}', '{{ addslashes($c->nomor ?? '') }}', '{{ addslashes($c->note ?? '') }}')"
                                        style="background: #f59e0b !important; color: #ffffff !important;"
                                        class="p-1 rounded-md shadow-2xs hover:opacity-90 transition-all text-white" title="Edit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>

                                <form action="{{ route('gomsn.network.customers.destroy', $c) }}" method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ addslashes($c->nama_user) }} dari ODP ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            style="background: #ef4444 !important; color: #ffffff !important;"
                                            class="p-1 rounded-md shadow-2xs hover:opacity-90 transition-all text-white" title="Hapus">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Belum ada pelanggan terhubung di ODP {{ $odp->nama_odp }}.
                    </div>
                @endforelse
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
