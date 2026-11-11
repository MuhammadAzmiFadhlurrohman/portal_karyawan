<x-app-layout>
    <div class="w-full space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('gomsn.pengajuan.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#1E40AF] hover:underline font-semibold mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar Pengajuan
                </a>
                <h1 class="text-2xl sm:text-3xl font-heading font-bold text-[#1E3A8A] tracking-tight">
                    Buat Formulir Pengajuan
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                    SOP §8.1: Sistem terpadu pengajuan kasbon operasional, pengadaan barang, dan permohonan stok inventaris.
                </p>
            </div>
        </div>

        @if ($hasOverdue)
        <div role="alert" class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <strong class="font-bold">Pengajuan Baru Diblokir:</strong> Anda masih memiliki nota pertanggungjawaban/realisasi pengajuan sebelumnya yang belum diselesaikan lebih dari 7 hari kalender. Harap selesaikan realisasi tertunggak terlebih dahulu.
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-xs shadow-xs space-y-1">
            <div class="font-bold">Mohon periksa kembali input formulir Anda:</div>
            <ul class="list-disc list-inside space-y-0.5 text-red-800">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Alpine Component Definition Script (Runs reliably before DOM init) -->
        <script>
            window.pengajuanAppForm = function() {
                return {
                    jenis: 'BELI',
                    hasOverdue: {{ $hasOverdue ? 'true' : 'false' }},
                    items: [
                        {
                            nama_barang: '',
                            jumlah: 1,
                            harga_satuan: 0,
                            master_material_id: '',
                            satuan: '',
                            keperluan_dana: 'Bensin Motor',
                            keperluan_lainnya: '',
                            jumlah_dana: 0,
                            personel: []
                        }
                    ],
                    selectJenis(val) {
                        this.jenis = val;
                    },
                    onMaterialChange(item, evt) {
                        const sel = evt.target;
                        const opt = sel.options[sel.selectedIndex];
                        if (opt) {
                            item.satuan = opt.getAttribute('data-satuan') || '';
                        }
                    },
                    addItem() {
                        this.items.push({
                            nama_barang: '',
                            jumlah: 1,
                            harga_satuan: 0,
                            master_material_id: '',
                            satuan: '',
                            keperluan_dana: 'Bensin Motor',
                            keperluan_lainnya: '',
                            jumlah_dana: 0,
                            personel: []
                        });
                    },
                    removeItem(idx) {
                        if (this.items.length > 1) {
                            this.items.splice(idx, 1);
                        }
                    },
                    calculateTotal() {
                        let total = 0;
                        if (this.jenis === 'BELI') {
                            this.items.forEach(it => {
                                total += (Number(it.jumlah) || 0) * (Number(it.harga_satuan) || 0);
                            });
                        } else if (this.jenis === 'DANA') {
                            this.items.forEach(it => {
                                total += Number(it.jumlah_dana) || 0;
                            });
                        }
                        return total;
                    },
                    formatRupiah(num) {
                        return 'Rp' + new Intl.NumberFormat('id-ID').format(num || 0);
                    }
                };
            };

            // Register with Alpine if available
            if (window.Alpine) {
                window.Alpine.data('pengajuanAppForm', window.pengajuanAppForm);
            } else {
                document.addEventListener('alpine:init', () => {
                    window.Alpine.data('pengajuanAppForm', window.pengajuanAppForm);
                });
            }
        </script>

        <!-- Main Form Card (Theme Consistent with User Mockup) -->
        <div x-data="pengajuanAppForm()" x-cloak class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
            <form method="POST" action="{{ route('gomsn.pengajuan.store') }}" class="space-y-6">
                @csrf

                <!-- Section 1: Selector 4 Jenis Pengajuan -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#009688]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Jenis Pengajuan <span class="text-red-500">*</span></span>
                    </label>

                    <input type="hidden" name="jenis" :value="jenis">

                    <!-- 4 Big Selection Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        <!-- Stok -->
                        <button type="button" @click="selectJenis('STOK')"
                                :class="jenis === 'STOK' ? 'border-2 border-slate-800 bg-[#F4F8FB] shadow-xs relative after:absolute after:bottom-0 after:left-0 after:right-0 after:h-1 after:bg-[#009688] after:rounded-b-xl' : 'border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                class="py-6 px-4 rounded-2xl text-center flex flex-col items-center justify-center gap-3 transition-all cursor-pointer group">
                            <div :class="jenis === 'STOK' ? 'text-[#009688]' : 'text-slate-400 group-hover:text-slate-600'" class="transition-colors">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'STOK' ? 'text-slate-900 font-bold' : 'text-slate-700 font-medium'" class="text-xs sm:text-sm transition-colors">
                                Stok
                            </span>
                            <span class="sr-only">Stok Gudang</span>
                        </button>

                        <!-- Beli -->
                        <button type="button" @click="selectJenis('BELI')"
                                :class="jenis === 'BELI' ? 'border-2 border-slate-800 bg-[#F4F8FB] shadow-xs relative after:absolute after:bottom-0 after:left-0 after:right-0 after:h-1 after:bg-[#009688] after:rounded-b-xl' : 'border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                class="py-6 px-4 rounded-2xl text-center flex flex-col items-center justify-center gap-3 transition-all cursor-pointer group">
                            <div :class="jenis === 'BELI' ? 'text-[#009688]' : 'text-slate-400 group-hover:text-slate-600'" class="transition-colors">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'BELI' ? 'text-slate-900 font-bold' : 'text-slate-700 font-medium'" class="text-xs sm:text-sm transition-colors">
                                Beli
                            </span>
                        </button>

                        <!-- Permintaan -->
                        <button type="button" @click="selectJenis('PERMINTAAN')"
                                :class="jenis === 'PERMINTAAN' ? 'border-2 border-slate-800 bg-[#F4F8FB] shadow-xs relative after:absolute after:bottom-0 after:left-0 after:right-0 after:h-1 after:bg-[#009688] after:rounded-b-xl' : 'border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                class="py-6 px-4 rounded-2xl text-center flex flex-col items-center justify-center gap-3 transition-all cursor-pointer group">
                            <div :class="jenis === 'PERMINTAAN' ? 'text-[#009688]' : 'text-slate-400 group-hover:text-slate-600'" class="transition-colors">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'PERMINTAAN' ? 'text-slate-900 font-bold' : 'text-slate-700 font-medium'" class="text-xs sm:text-sm transition-colors">
                                Permintaan
                            </span>
                        </button>

                        <!-- Dana -->
                        <button type="button" @click="selectJenis('DANA')"
                                :class="jenis === 'DANA' ? 'border-2 border-slate-800 bg-[#F4F8FB] shadow-xs relative after:absolute after:bottom-0 after:left-0 after:right-0 after:h-1 after:bg-[#009688] after:rounded-b-xl' : 'border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                class="py-6 px-4 rounded-2xl text-center flex flex-col items-center justify-center gap-3 transition-all cursor-pointer group">
                            <div :class="jenis === 'DANA' ? 'text-[#009688]' : 'text-slate-400 group-hover:text-slate-600'" class="transition-colors">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'DANA' ? 'text-slate-900 font-bold' : 'text-slate-700 font-medium'" class="text-xs sm:text-sm transition-colors">
                                Dana
                            </span>
                            <span class="sr-only">Dana Kasbon</span>
                        </button>
                    </div>
                </div>

                <!-- Section 2: Dynamic Item Cards -->
                <div class="space-y-4">
                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-4 relative shadow-2xs transition hover:border-slate-300">
                            <!-- Card Header -->
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <div class="flex items-center gap-2 text-[#00897B] font-bold text-sm sm:text-base">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span x-text="`Item Pengajuan ${idx + 1}`"></span>
                                </div>
                                <button type="button" @click="removeItem(idx)" x-show="items.length > 1"
                                        class="w-6 h-6 rounded-full bg-rose-400 hover:bg-rose-500 text-white flex items-center justify-center transition cursor-pointer text-xs font-bold shadow-2xs"
                                        title="Hapus item ini">
                                    ✕
                                </button>
                            </div>

                            <!-- VARIANT 1: BELI -->
                            <template x-if="jenis === 'BELI'">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-start">
                                    <!-- Nama Barang -->
                                    <div class="sm:col-span-5 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            <span>Nama Barang <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="text" :name="`items[${idx}][nama_barang]`" x-model="item.nama_barang" required placeholder="COMPRO"
                                               class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-medium" />
                                    </div>
                                    <!-- Qty -->
                                    <div class="sm:col-span-3 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="text-slate-500 font-bold">#</span>
                                            <span>Jumlah (Qty) <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="number" :name="`items[${idx}][jumlah]`" x-model.number="item.jumlah" min="1" required placeholder="5"
                                               class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-semibold text-center" />
                                    </div>
                                    <!-- Harga Satuan -->
                                    <div class="sm:col-span-4 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="text-slate-500 font-bold">$</span>
                                            <span>Harga Satuan (Rp) <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="number" :name="`items[${idx}][harga_satuan]`" x-model.number="item.harga_satuan" min="0" required placeholder="48000"
                                               class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-semibold text-right" />
                                    </div>
                                </div>
                            </template>

                            <!-- VARIANT 2: STOK -->
                            <template x-if="jenis === 'STOK'">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-start">
                                    <!-- Pilih Barang dari Stok -->
                                    <div class="sm:col-span-8 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Pilih Barang Dari Stok <span class="text-red-500">*</span></span>
                                        </label>
                                        <select :name="`items[${idx}][master_material_id]`" x-model="item.master_material_id" @change="onMaterialChange(item, $event)" required
                                                class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-medium">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach ($materials as $mat)
                                            <option value="{{ $mat->id }}" data-satuan="{{ $mat->satuan }}" data-stok="{{ $mat->stok_tersedia }}">
                                                {{ $mat->nama }} (Tersedia: {{ $mat->stok_tersedia }} {{ $mat->satuan }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Jumlah -->
                                    <div class="sm:col-span-4 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="text-slate-500 font-bold">#</span>
                                            <span>Jumlah <span class="text-red-500">*</span></span>
                                        </label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" :name="`items[${idx}][jumlah]`" x-model.number="item.jumlah" min="1" required placeholder="Masukkan jumlah"
                                                   class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-semibold text-center" />
                                            <span class="text-xs text-slate-500 font-medium whitespace-nowrap" x-text="item.satuan || ''"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- VARIANT 3: PERMINTAAN -->
                            <template x-if="jenis === 'PERMINTAAN'">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-start">
                                    <!-- Nama Barang -->
                                    <div class="sm:col-span-8 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            <span>Nama Barang <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="text" :name="`items[${idx}][nama_barang]`" x-model="item.nama_barang" required placeholder="Nama barang yang diminta"
                                               class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-medium" />
                                        <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                            <span class="text-blue-600">ⓘ</span>
                                            <span>Periksa apakah barang yang Anda ajukan sudah ada di stok</span>
                                        </p>
                                    </div>
                                    <!-- Jumlah -->
                                    <div class="sm:col-span-4 space-y-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="text-slate-500 font-bold">#</span>
                                            <span>Jumlah <span class="text-red-500">*</span></span>
                                        </label>
                                        <input type="number" :name="`items[${idx}][jumlah]`" x-model.number="item.jumlah" min="1" required placeholder="Jumlah"
                                               class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-semibold text-center" />
                                    </div>
                                </div>
                            </template>

                            <!-- VARIANT 4: DANA -->
                            <template x-if="jenis === 'DANA'">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-start">
                                        <!-- Keperluan Dana -->
                                        <div class="sm:col-span-6 space-y-1.5">
                                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                                <span>Keperluan Dana <span class="text-red-500">*</span></span>
                                            </label>
                                            <select :name="`items[${idx}][keperluan_dana]`" x-model="item.keperluan_dana" required
                                                    class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-medium">
                                                <option value="Bensin Motor">Bensin Motor</option>
                                                <option value="Bensin Mobil">Bensin Mobil</option>
                                                <option value="Tol &amp; Parkir">Tol &amp; Parkir</option>
                                                <option value="Konsumsi Lapangan">Konsumsi Lapangan</option>
                                                <option value="Penginapan / Hotel Dinas">Penginapan / Hotel Dinas</option>
                                                <option value="Transportasi Umum / Tiket">Transportasi Umum / Tiket</option>
                                                <option value="Pembelian Material Cito">Pembelian Material Cito</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                            <div x-show="item.keperluan_dana === 'Lainnya'" class="mt-2">
                                                <input type="text" :name="`items[${idx}][keperluan_lainnya]`" x-model="item.keperluan_lainnya" placeholder="Tuliskan keperluan lainnya"
                                                       class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs" />
                                            </div>
                                        </div>
                                        <!-- Jumlah Dana (Rp) -->
                                        <div class="sm:col-span-6 space-y-1.5">
                                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                                <span class="text-slate-500 font-bold">$</span>
                                                <span>Jumlah Dana (Rp) <span class="text-red-500">*</span></span>
                                            </label>
                                            <input type="number" :name="`items[${idx}][jumlah_dana]`" x-model.number="item.jumlah_dana" min="0" required placeholder="Jumlah dana yang dibutuhkan"
                                                   class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-semibold text-right" />
                                        </div>
                                    </div>

                                    <!-- Pilih Tim / Personel (Hanya muncul jika mengajukan Bensin Motor atau Bensin Mobil) -->
                                    <div x-show="item.keperluan_dana === 'Bensin Motor' || item.keperluan_dana === 'Bensin Mobil'"
                                         x-transition
                                         class="space-y-1.5 pt-2 border-t border-slate-100">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Pilih Tim / Personel <span class="text-red-500">*</span></span>
                                        </label>
                                        <div class="border border-slate-200 rounded-xl p-3 bg-white max-h-40 overflow-y-auto">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                @foreach ($employees as $emp)
                                                <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-slate-50 p-1 rounded-md cursor-pointer transition-colors">
                                                    <input type="checkbox" :name="`items[${idx}][personel][]`" value="{{ $emp->name }}" x-model="item.personel" class="rounded text-[#009688] focus:ring-[#009688] border-slate-300">
                                                    <span class="truncate font-medium text-[11px] uppercase">{{ $emp->name }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                            <span class="text-blue-600">ⓘ</span>
                                            <span>Pilih satu atau lebih personel yang ikut dalam perjalanan</span>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Section 3: Total Dana Banner (Visible for BELI & DANA) -->
                <div x-show="jenis === 'BELI' || jenis === 'DANA'"
                     class="bg-[#EBF3FC] border border-blue-100 rounded-xl px-5 py-3.5 flex items-center gap-2.5 text-slate-800 text-sm font-bold shadow-2xs">
                    <span class="text-base">🧮</span>
                    <span>Total Dana:</span>
                    <span x-text="formatRupiah(calculateTotal())" class="text-slate-900 font-extrabold text-base"></span>
                </div>

                <!-- Section 4: Tombol Aksi Tambah Item & Kembali -->
                <div class="flex items-center justify-between pt-1">
                    <a href="{{ route('gomsn.pengajuan.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition border border-slate-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </a>
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#009688] hover:bg-[#00796B] text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah</span>
                    </button>
                </div>

                <!-- Section 5: Keterangan & Pilih Atasan -->
                <div class="space-y-4 pt-3 border-t border-slate-200">
                    <!-- Keterangan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Keterangan <span class="text-red-500">*</span></span>
                        </label>
                        <textarea name="keterangan" rows="3" required placeholder="Company Profile MSN"
                                  class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs"></textarea>
                    </div>

                    <!-- Pilih Atasan / Info Pengajuan Mandiri -->
                    @if (! empty($isAtasan))
                    <div class="p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 flex items-start gap-3.5 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-blue-900 block font-heading">Pengajuan Mandiri Atasan</span>
                            <p class="text-xs text-blue-700 mt-0.5 leading-relaxed">
                                Sebagai Atasan, permohonan ini otomatis langsung diteruskan ke <strong>Bagian Finance</strong> tanpa melalui persetujuan atasan lain.
                            </p>
                        </div>
                    </div>
                    @else
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Pilih Atasan <span class="text-red-500">*</span></span>
                        </label>
                        <select name="atasan_id" required class="w-full text-xs rounded-xl border border-slate-300 bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-100 focus:border-[#009688] shadow-2xs font-medium">
                            <option value="">-- Pilih Atasan --</option>
                            @foreach ($atasans as $atasan)
                            <option value="{{ $atasan->id }}">
                                {{ $atasan->name }} ({{ $atasan->position ?: ($atasan->division ? 'Atasan ' . $atasan->division : 'Atasan') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Section 6: Submit Button -->
                <div class="pt-2">
                    <button type="submit" :disabled="hasOverdue"
                            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-gradient-to-r from-[#003B46] to-[#00796B] hover:from-[#002D36] hover:to-[#00695C] text-white font-bold text-sm shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Ajukan Pengajuan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
