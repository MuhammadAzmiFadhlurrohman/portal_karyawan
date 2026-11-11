<x-app-layout>
    <div class="w-full space-y-6" x-data="{
        editModal: false,
        deleteModal: false,
        historyModal: false,
        
        editItem: {
            id: null,
            kode: '',
            nama: '',
            satuan: '',
            stok_tersedia: 0,
            updateUrl: ''
        },
        deleteItem: {
            id: null,
            nama: '',
            deleteUrl: ''
        },
        
        openEdit(item) {
            this.editItem = {
                id: item.id,
                kode: item.kode,
                nama: item.nama,
                satuan: item.satuan,
                stok_tersedia: item.stok_tersedia,
                updateUrl: `/gomsn/stok/${item.id}`
            };
            this.editModal = true;
        },
        openDelete(item) {
            this.deleteItem = {
                id: item.id,
                nama: item.nama,
                deleteUrl: `/gomsn/stok/${item.id}`
            };
            this.deleteModal = true;
        }
    }">
        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#inventoryWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="inventoryWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Cube/Package Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-1.5 leading-snug">
                        <span>Manajemen Stok Barang Kantor</span>
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Monitoring persediaan material, pencatatan barang operasional baru, dan riwayat mutasi stok secara real-time.
                    </p>
                </div>
            </div>
        </div>

        <!-- Top Flash Alerts -->
        @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs sm:text-sm font-medium shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1 shadow-2xs">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terdapat kesalahan pengisian data:</span>
            </div>
            <ul class="list-disc list-inside text-rose-700 pl-2">
                @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <span class="sr-only">Stok Barang Kantor</span>

        <!-- ============================================== -->
        <!-- CARD 1: TAMBAH BARANG BARU (SESUAI MOCKUP)     -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <!-- Header Banner Teal -> Dark Slate Sesuai Tema -->
            <div class="px-5 py-3.5 flex items-center justify-between text-white"
                 style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                <div class="flex items-center gap-2.5 font-bold text-sm tracking-wide text-white">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8"/>
                    </svg>
                    <span>Tambah Barang Baru</span>
                </div>
                <button type="button" 
                        @click="historyModal = true"
                        class="text-white text-xs px-3.5 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition font-medium"
                        style="background-color: rgba(255, 255, 255, 0.18) !important; color: #ffffff !important;">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                    </svg>
                    <span>Riwayat Stok</span>
                </button>
            </div>

            <!-- Form Body Tambah Barang -->
            <div class="p-4 sm:p-5">
                <form method="POST" action="{{ route('gomsn.inventory.store') }}" class="flex flex-wrap lg:flex-nowrap items-end gap-3.5">
                    @csrf
                    <!-- Input Nama Barang -->
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Barang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" required 
                               placeholder="Contoh: Router, Kabel UTP, Switch" 
                               value="{{ old('nama') }}"
                               style="border: 1px solid #cbd5e1 !important;"
                               class="w-full text-xs rounded-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Input Jumlah Stok -->
                    <div class="w-full sm:w-36">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jumlah Stok <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="stok_tersedia" min="0" required 
                               value="{{ old('stok_tersedia', 0) }}" 
                               style="border: 1px solid #cbd5e1 !important;"
                               class="w-full text-xs rounded-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 font-bold text-slate-800 bg-white">
                    </div>

                    <!-- Select Satuan -->
                    <div class="w-full sm:w-56">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Satuan <span class="text-rose-500">*</span>
                        </label>
                        <select name="satuan" required 
                                style="border: 1px solid #cbd5e1 !important;"
                                class="w-full text-xs rounded-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 bg-white text-slate-700 font-medium">
                            <option value="">-- Pilih Satuan --</option>
                            <option value="pcs" {{ old('satuan') === 'pcs' ? 'selected' : '' }}>Pcs (Piece)</option>
                            <option value="meter" {{ old('satuan') === 'meter' ? 'selected' : '' }}>Meter (m)</option>
                            <option value="box" {{ old('satuan') === 'box' ? 'selected' : '' }}>Box</option>
                            <option value="roll" {{ old('satuan') === 'roll' ? 'selected' : '' }}>Roll</option>
                            <option value="unit" {{ old('satuan') === 'unit' ? 'selected' : '' }}>Unit</option>
                            <option value="set" {{ old('satuan') === 'set' ? 'selected' : '' }}>Set</option>
                            <option value="pack" {{ old('satuan') === 'pack' ? 'selected' : '' }}>Pack</option>
                            <option value="batang" {{ old('satuan') === 'batang' ? 'selected' : '' }}>Batang</option>
                            <option value="rim" {{ old('satuan') === 'rim' ? 'selected' : '' }}>Rim</option>
                        </select>
                    </div>

                    <!-- Tombol Tambah Hijau Sesuai Mockup -->
                    <div class="shrink-0">
                        <button type="submit" 
                                style="background-color: #059669 !important; color: #ffffff !important;"
                                class="hover:opacity-90 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-sm inline-flex items-center gap-1.5 transition h-[39px] cursor-pointer">
                            <span>+</span>
                            <span>Tambah</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- CARD 2: DAFTAR BARANG (SESUAI MOCKUP)          -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <!-- Header Banner Teal -> Dark Slate Sesuai Tema -->
            <div class="px-5 py-3.5 text-white flex items-center justify-between font-bold text-sm tracking-wide"
                 style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                <div class="flex items-center gap-2.5 text-white">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>Daftar Barang</span>
                </div>
            </div>

            <!-- Search Bar Inline -->
            <div class="p-4 sm:p-5 pb-3">
                <form method="GET" action="{{ route('gomsn.inventory.index') }}" class="flex items-center max-w-full">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nama barang..."
                               style="border: 1px solid #cbd5e1 !important; border-right: none !important;"
                               class="w-full text-xs rounded-l-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 placeholder-slate-400 bg-white">
                    </div>
                    <button type="submit" 
                            style="background: linear-gradient(135deg, #18C4C4 0%, #155E63 100%) !important; color: #ffffff !important;"
                            class="hover:opacity-90 text-white font-bold text-xs px-5 py-2.5 rounded-r-xl inline-flex items-center gap-1.5 transition h-[39px] shrink-0 shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari</span>
                    </button>
                    @if(request('search'))
                    <a href="{{ route('gomsn.inventory.index') }}" class="ml-2 text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Tabel Daftar Barang -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs min-w-[650px]">
                    <thead>
                        <tr class="border-y border-slate-200 bg-slate-50/70 text-slate-800 font-bold text-xs">
                            <th class="py-3 px-4 w-12 font-bold text-center">#</th>
                            <th class="py-3 px-4 font-bold">Nama Barang</th>
                            <th class="py-3 px-4 font-bold text-center">Jumlah Stok</th>
                            <th class="py-3 px-4 font-bold text-center">Satuan</th>
                            <th class="py-3 px-4 font-bold text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($materials as $material)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-600">
                                {{ $loop->iteration + ($materials->currentPage() - 1) * $materials->perPage() }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 text-xs">{{ $material->nama }}</span>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $material->kode }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($material->stok_tersedia <= 0)
                                    <span class="font-bold text-sm" style="color: #dc2626 !important;">
                                        {{ number_format($material->stok_tersedia, 0, ',', ',') }}
                                    </span>
                                @elseif ($material->stok_tersedia <= ($material->min_stok ?? 2))
                                    <span class="font-bold text-sm" style="color: #d97706 !important;">
                                        {{ number_format($material->stok_tersedia, 0, ',', ',') }}
                                    </span>
                                @else
                                    <span class="font-bold text-sm" style="color: #059669 !important;">
                                        {{ number_format($material->stok_tersedia, 0, ',', ',') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-semibold"
                                      style="background-color: #ecfeff !important; border: 1px solid #a5f3fc !important; color: #0e7490 !important;">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="color: #0891b2 !important;">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                    </svg>
                                    <span>{{ $material->satuan }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <!-- Tombol Edit Orange Sesuai Mockup -->
                                    <button type="button" 
                                            @click="openEdit({{ json_encode($material) }})"
                                            style="background-color: #d97706 !important; color: #ffffff !important;"
                                            class="hover:opacity-90 text-white font-semibold text-xs px-2.5 py-1.5 rounded-lg inline-flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Tombol Hapus Merah Sesuai Mockup -->
                                    <button type="button" 
                                            @click="openDelete({{ json_encode($material) }})"
                                            style="background-color: #dc2626 !important; color: #ffffff !important;"
                                            class="hover:opacity-90 text-white font-semibold text-xs px-2.5 py-1.5 rounded-lg inline-flex items-center gap-1 transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Tidak ada data barang inventaris yang sesuai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($materials->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $materials->links() }}
            </div>
            @endif
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT BARANG (SESUAI MOCKUP GAMBAR 2)     -->
        <!-- ============================================== -->
        <div x-show="editModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all"
                 @click.away="editModal = false">

                <!-- Header Modal Edit Sesuai Mockup Gradasi Teal -->
                <div class="px-5 py-3.5 text-white flex items-center justify-between"
                     style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                    <div class="flex items-center gap-2 font-bold text-base tracking-wide text-white">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit Barang</span>
                    </div>
                    <button type="button" @click="editModal = false" class="text-white hover:opacity-80 text-xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <!-- Form Edit Modal -->
                <form :action="editItem.updateUrl" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-5 space-y-4">
                        <!-- Nama Barang -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Barang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" x-model="editItem.nama" required 
                                   style="border: 1px solid #cbd5e1 !important;"
                                   class="w-full text-xs rounded-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 bg-white">
                        </div>

                        <!-- Jumlah Stok -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jumlah Stok <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="stok_tersedia" x-model="editItem.stok_tersedia" min="0" required 
                                   style="border: 1px solid #cbd5e1 !important;"
                                   class="w-full text-xs rounded-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 font-bold text-slate-800 bg-white">
                        </div>

                        <!-- Satuan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Satuan <span class="text-rose-500">*</span>
                            </label>
                            <select name="satuan" x-model="editItem.satuan" required 
                                    style="border: 1px solid #cbd5e1 !important;"
                                    class="w-full text-xs rounded-xl py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 bg-white text-slate-700 font-medium">
                                <option value="pcs">Pcs (Piece)</option>
                                <option value="meter">Meter (m)</option>
                                <option value="box">Box</option>
                                <option value="roll">Roll</option>
                                <option value="unit">Unit</option>
                                <option value="set">Set</option>
                                <option value="pack">Pack</option>
                                <option value="batang">Batang</option>
                                <option value="rim">Rim</option>
                            </select>
                        </div>
                    </div>

                    <!-- Footer Buttons Sesuai Mockup -->
                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5">
                        <button type="button" @click="editModal = false" 
                                style="background-color: #475569 !important; color: #ffffff !important;"
                                class="hover:opacity-90 text-white font-medium text-xs px-4 py-2.5 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important; color: #ffffff !important;"
                                class="hover:opacity-90 text-white font-bold text-xs px-5 py-2.5 rounded-xl inline-flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL KONFIRMASI HAPUS BARANG                  -->
        <!-- ============================================== -->
        <div x-show="deleteModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="relative bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all"
                 @click.away="deleteModal = false">
                <div class="p-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">Hapus Barang Inventaris?</h3>
                    <p class="text-xs text-slate-600">
                        Apakah Anda yakin ingin menghapus barang <strong class="text-slate-900" x-text="deleteItem.nama"></strong> dari stok kantor? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5">
                    <button type="button" @click="deleteModal = false" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <form :action="deleteItem.deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                style="background-color: #dc2626 !important; color: #ffffff !important;"
                                class="hover:opacity-90 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-2xs transition cursor-pointer">
                            Ya, Hapus Barang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL RIWAYAT MUTASI STOK KANTOR               -->
        <!-- ============================================== -->
        <div x-show="historyModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="relative bg-white rounded-2xl max-w-3xl w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all"
                 @click.away="historyModal = false">
                
                <div class="px-5 py-3.5 text-white flex items-center justify-between font-bold text-sm"
                     style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                    <div class="flex items-center gap-2 text-white">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                        <span>Riwayat Mutasi Stok Inventaris</span>
                    </div>
                    <button type="button" @click="historyModal = false" class="text-white hover:opacity-80 text-xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <div class="p-5 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold">
                            <tr>
                                <th class="py-2.5 px-3">Waktu</th>
                                <th class="py-2.5 px-3">Barang</th>
                                <th class="py-2.5 px-3 text-center">Tipe</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-center">Sebelum &rarr; Sesudah</th>
                                <th class="py-2.5 px-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentMovements ?? [] as $mvt)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">
                                    {{ $mvt->created_at ? $mvt->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    {{ $mvt->material?->nama ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if ($mvt->type === 'IN')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">MASUK</span>
                                    @elseif ($mvt->type === 'OUT')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">KELUAR</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">ADJUST</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-slate-900">
                                    {{ $mvt->quantity }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-600">
                                    {{ $mvt->stock_before }} &rarr; {{ $mvt->stock_after }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-600 max-w-xs truncate" title="{{ $mvt->notes }}">
                                    {{ $mvt->notes ?: '-' }}
                                    @if ($mvt->user)
                                        <span class="block text-[10px] text-slate-400">Oleh: {{ $mvt->user->name }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada catatan mutasi stok inventaris.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 text-right">
                    <button type="button" @click="historyModal = false" class="bg-slate-600 hover:bg-slate-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
