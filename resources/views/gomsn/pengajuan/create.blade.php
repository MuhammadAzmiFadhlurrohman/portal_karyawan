<x-app-layout>
    <div class="w-full space-y-5 pb-10">
        
        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4.5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#createPengajuanWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="createPengajuanWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Buat Formulir Pengajuan
                    </h1>
                    <p class="text-[11px] sm:text-xs text-teal-100/85 mt-0.5 leading-snug">
                        Ajukan kasbon dana operasional, pengadaan barang, atau stok inventaris kerja
                    </p>
                </div>
            </div>

            <!-- Right: Back Button -->
            <div class="relative z-10 shrink-0 self-start sm:self-auto">
                <a href="{{ route('gomsn.pengajuan.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold backdrop-blur-xs transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>

        @if ($hasOverdue)
        <div role="alert" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <strong class="font-bold block text-rose-900">Pengajuan Baru Diblokir Sementara</strong>
                <span class="text-rose-700 mt-0.5 block leading-relaxed">Anda masih memiliki nota pertanggungjawaban/realisasi pengajuan sebelumnya yang belum diselesaikan lebih dari 7 hari kalender. Harap selesaikan realisasi tertunggak terlebih dahulu.</span>
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs shadow-2xs space-y-1">
            <div class="font-bold flex items-center gap-2 text-rose-900">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Mohon periksa kembali input formulir Anda:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-5 text-rose-700">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Alpine Component Definition Script -->
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
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num || 0);
                    }
                };
            };

            if (window.Alpine) {
                window.Alpine.data('pengajuanAppForm', window.pengajuanAppForm);
            } else {
                document.addEventListener('alpine:init', () => {
                    window.Alpine.data('pengajuanAppForm', window.pengajuanAppForm);
                });
            }
        </script>

        <!-- Main Form Container -->
        <div x-data="pengajuanAppForm()" x-cloak class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <form method="POST" action="{{ route('gomsn.pengajuan.store') }}" class="p-4 sm:p-7 space-y-6">
                @csrf

                <!-- ========================================== -->
                <!-- SECTION 1: PILIH JENIS PENGAJUAN           -->
                <!-- ========================================== -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-50 border border-teal-100 text-[#0D9488] flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </span>
                            <span>1. Pilih Jenis Pengajuan <span class="text-rose-500">*</span></span>
                        </label>
                        <span class="text-[11px] font-semibold text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-full border border-teal-200/60" x-text="jenis"></span>
                    </div>

                    <input type="hidden" name="jenis" :value="jenis">

                    <!-- 4 Sleek Selection Cards (Always 1 Single Row, 4 Columns across all screen sizes) -->
                    <div class="grid grid-cols-4 gap-2 sm:gap-3.5">
                        <!-- Card 1: Stok -->
                        <button type="button" @click="selectJenis('STOK')"
                                :class="jenis === 'STOK'
                                    ? 'border-2 border-[#0D9488] bg-teal-50/60 ring-2 ring-[#0D9488]/15 shadow-xs'
                                    : 'border border-slate-200/90 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'"
                                class="relative py-3 px-1.5 sm:py-4 sm:px-3 rounded-xl sm:rounded-2xl flex flex-col items-center justify-center text-center transition-all duration-150 cursor-pointer group active:scale-[0.98] select-none min-h-[78px] sm:min-h-[88px]">
                            <div :class="jenis === 'STOK' ? 'text-[#0D9488] scale-110' : 'text-slate-400 group-hover:text-teal-700'"
                                 class="transition-all duration-150 mb-1.5 sm:mb-2 flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'STOK' ? 'text-teal-950 font-extrabold' : 'text-slate-700 font-bold'"
                                  class="text-[11px] sm:text-xs md:text-sm block tracking-tight leading-tight whitespace-nowrap truncate w-full px-0.5">
                                Stok
                            </span>
                            <span class="sr-only">Stok Gudang</span>
                            <div x-show="jenis === 'STOK'" class="absolute bottom-0 inset-x-3 sm:inset-x-5 h-1 bg-[#0D9488] rounded-t-full"></div>
                        </button>

                        <!-- Card 2: Beli -->
                        <button type="button" @click="selectJenis('BELI')"
                                :class="jenis === 'BELI'
                                    ? 'border-2 border-[#0D9488] bg-teal-50/60 ring-2 ring-[#0D9488]/15 shadow-xs'
                                    : 'border border-slate-200/90 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'"
                                class="relative py-3 px-1.5 sm:py-4 sm:px-3 rounded-xl sm:rounded-2xl flex flex-col items-center justify-center text-center transition-all duration-150 cursor-pointer group active:scale-[0.98] select-none min-h-[78px] sm:min-h-[88px]">
                            <div :class="jenis === 'BELI' ? 'text-[#0D9488] scale-110' : 'text-slate-400 group-hover:text-teal-700'"
                                 class="transition-all duration-150 mb-1.5 sm:mb-2 flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'BELI' ? 'text-teal-950 font-extrabold' : 'text-slate-700 font-bold'"
                                  class="text-[11px] sm:text-xs md:text-sm block tracking-tight leading-tight whitespace-nowrap truncate w-full px-0.5">
                                Beli
                            </span>
                            <div x-show="jenis === 'BELI'" class="absolute bottom-0 inset-x-3 sm:inset-x-5 h-1 bg-[#0D9488] rounded-t-full"></div>
                        </button>

                        <!-- Card 3: Permintaan -->
                        <button type="button" @click="selectJenis('PERMINTAAN')"
                                :class="jenis === 'PERMINTAAN'
                                    ? 'border-2 border-[#0D9488] bg-teal-50/60 ring-2 ring-[#0D9488]/15 shadow-xs'
                                    : 'border border-slate-200/90 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'"
                                class="relative py-3 px-1.5 sm:py-4 sm:px-3 rounded-xl sm:rounded-2xl flex flex-col items-center justify-center text-center transition-all duration-150 cursor-pointer group active:scale-[0.98] select-none min-h-[78px] sm:min-h-[88px]">
                            <div :class="jenis === 'PERMINTAAN' ? 'text-[#0D9488] scale-110' : 'text-slate-400 group-hover:text-teal-700'"
                                 class="transition-all duration-150 mb-1.5 sm:mb-2 flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'PERMINTAAN' ? 'text-teal-950 font-extrabold' : 'text-slate-800 font-bold'"
                                  class="text-[11px] sm:text-xs md:text-sm block tracking-tight leading-tight whitespace-nowrap truncate w-full px-0.5">
                                Permintaan
                            </span>
                            <div x-show="jenis === 'PERMINTAAN'" class="absolute bottom-0 inset-x-3 sm:inset-x-5 h-1 bg-[#0D9488] rounded-t-full"></div>
                        </button>

                        <!-- Card 4: Dana -->
                        <button type="button" @click="selectJenis('DANA')"
                                :class="jenis === 'DANA'
                                    ? 'border-2 border-[#0D9488] bg-teal-50/60 ring-2 ring-[#0D9488]/15 shadow-xs'
                                    : 'border border-slate-200/90 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'"
                                class="relative py-3 px-1.5 sm:py-4 sm:px-3 rounded-xl sm:rounded-2xl flex flex-col items-center justify-center text-center transition-all duration-150 cursor-pointer group active:scale-[0.98] select-none min-h-[78px] sm:min-h-[88px]">
                            <div :class="jenis === 'DANA' ? 'text-[#0D9488] scale-110' : 'text-slate-400 group-hover:text-teal-700'"
                                 class="transition-all duration-150 mb-1.5 sm:mb-2 flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span :class="jenis === 'DANA' ? 'text-teal-950 font-extrabold' : 'text-slate-700 font-bold'"
                                  class="text-[11px] sm:text-xs md:text-sm block tracking-tight leading-tight whitespace-nowrap truncate w-full px-0.5">
                                Dana
                            </span>
                            <span class="sr-only">Dana Kasbon</span>
                            <div x-show="jenis === 'DANA'" class="absolute bottom-0 inset-x-3 sm:inset-x-5 h-1 bg-[#0D9488] rounded-t-full"></div>
                        </button>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECTION 2: DAFTAR ITEM PENGAJUAN           -->
                <!-- ========================================== -->
                <div class="space-y-3.5 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-50 border border-teal-100 text-[#0D9488] flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </span>
                            <span>2. Rincian Item Pengajuan</span>
                        </label>
                        <span class="text-[11px] font-semibold text-slate-500" x-text="`${items.length} Item`"></span>
                    </div>

                    <div class="space-y-3.5">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="rounded-2xl border border-slate-200/90 bg-slate-50/40 overflow-hidden shadow-2xs transition hover:border-teal-300/80">
                                <!-- Item Card Header -->
                                <div class="px-4 py-2.5 bg-white border-b border-slate-200/70 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white font-bold text-[11px] flex items-center justify-center shadow-2xs"
                                              x-text="idx + 1"></span>
                                        <span class="font-bold text-slate-800 text-xs sm:text-sm" x-text="`Item Pengajuan #${idx + 1}`"></span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <!-- Live Subtotal Badge for BELI -->
                                        <template x-if="jenis === 'BELI'">
                                            <span class="text-[11px] font-bold text-teal-800 bg-teal-50 border border-teal-200/70 px-2.5 py-0.5 rounded-full font-mono"
                                                  x-text="formatRupiah((Number(item.jumlah) || 0) * (Number(item.harga_satuan) || 0))"></span>
                                        </template>

                                        <button type="button" @click="removeItem(idx)" x-show="items.length > 1"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/70 transition cursor-pointer text-[11px] font-bold"
                                                title="Hapus item ini">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Item Card Body -->
                                <div class="p-4 sm:p-5 bg-white/80">
                                    <!-- VARIANT 1: BELI -->
                                    <template x-if="jenis === 'BELI'">
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-start">
                                            <!-- Nama Barang -->
                                            <div class="sm:col-span-6 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Nama Barang / Material <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="text" :name="`items[${idx}][nama_barang]`" x-model="item.nama_barang" required placeholder="Contoh: Router Mikrotik / Kabel Patchcord"
                                                       class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-medium text-slate-800 placeholder-slate-400 transition" />
                                            </div>
                                            <!-- Qty -->
                                            <div class="sm:col-span-2 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Jumlah (Qty) <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="number" :name="`items[${idx}][jumlah]`" x-model.number="item.jumlah" min="1" required placeholder="1"
                                                       class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-bold text-center text-slate-800 transition" />
                                            </div>
                                            <!-- Harga Satuan -->
                                            <div class="sm:col-span-4 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Harga Satuan <span class="text-rose-500">*</span>
                                                </label>
                                                <div class="relative flex items-center">
                                                    <span class="absolute left-3.5 text-xs font-bold text-slate-400 select-none pointer-events-none">Rp</span>
                                                    <input type="number" :name="`items[${idx}][harga_satuan]`" x-model.number="item.harga_satuan" min="0" required placeholder="0"
                                                           class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 pl-9 pr-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-bold text-right text-slate-800 font-mono transition" />
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- VARIANT 2: STOK -->
                                    <template x-if="jenis === 'STOK'">
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-start">
                                            <!-- Pilih Barang dari Stok -->
                                            <div class="sm:col-span-8 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Pilih Barang dari Stok Gudang <span class="text-rose-500">*</span>
                                                </label>
                                                <select :name="`items[${idx}][master_material_id]`" x-model="item.master_material_id" @change="onMaterialChange(item, $event)" required
                                                        class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-medium text-slate-800 transition">
                                                    <option value="">-- Pilih Barang Gudang --</option>
                                                    @foreach ($materials as $mat)
                                                    <option value="{{ $mat->id }}" data-satuan="{{ $mat->satuan }}" data-stok="{{ $mat->stok_tersedia }}">
                                                        {{ $mat->nama }} (Tersedia: {{ $mat->stok_tersedia }} {{ $mat->satuan }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- Jumlah -->
                                            <div class="sm:col-span-4 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Jumlah Ambil <span class="text-rose-500">*</span>
                                                </label>
                                                <div class="flex items-center gap-2">
                                                    <input type="number" :name="`items[${idx}][jumlah]`" x-model.number="item.jumlah" min="1" required placeholder="1"
                                                           class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-bold text-center text-slate-800 transition" />
                                                    <span x-show="item.satuan" class="px-2.5 py-2 rounded-xl bg-slate-100 text-xs text-slate-600 font-semibold whitespace-nowrap" x-text="item.satuan"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- VARIANT 3: PERMINTAAN -->
                                    <template x-if="jenis === 'PERMINTAAN'">
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-start">
                                            <!-- Nama Barang -->
                                            <div class="sm:col-span-8 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Nama Barang yang Diminta <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="text" :name="`items[${idx}][nama_barang]`" x-model="item.nama_barang" required placeholder="Ketik nama alat / barang yang dibutuhkan..."
                                                       class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-medium text-slate-800 placeholder-slate-400 transition" />
                                                <p class="text-[11px] text-slate-400 flex items-center gap-1.5 pt-0.5">
                                                    <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>Pastikan barang belum tersedia di daftar Stok Gudang sebelum mengajukan permintaan.</span>
                                                </p>
                                            </div>
                                            <!-- Jumlah -->
                                            <div class="sm:col-span-4 space-y-1.5">
                                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                    Jumlah (Qty) <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="number" :name="`items[${idx}][jumlah]`" x-model.number="item.jumlah" min="1" required placeholder="1"
                                                       class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-bold text-center text-slate-800 transition" />
                                            </div>
                                        </div>
                                    </template>

                                    <!-- VARIANT 4: DANA -->
                                    <template x-if="jenis === 'DANA'">
                                        <div class="space-y-4">
                                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-start">
                                                <!-- Keperluan Dana -->
                                                <div class="sm:col-span-7 space-y-1.5">
                                                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                        Kategori Keperluan Dana <span class="text-rose-500">*</span>
                                                    </label>
                                                    <select :name="`items[${idx}][keperluan_dana]`" x-model="item.keperluan_dana" required
                                                            class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-medium text-slate-800 transition">
                                                        <option value="Bensin Motor">Bensin Motor</option>
                                                        <option value="Bensin Mobil">Bensin Mobil</option>
                                                        <option value="Tol &amp; Parkir">Tol &amp; Parkir</option>
                                                        <option value="Konsumsi Lapangan">Konsumsi Lapangan</option>
                                                        <option value="Penginapan / Hotel Dinas">Penginapan / Hotel Dinas</option>
                                                        <option value="Transportasi Umum / Tiket">Transportasi Umum / Tiket</option>
                                                        <option value="Pembelian Material Cito">Pembelian Material Cito</option>
                                                        <option value="Lainnya">Lainnya</option>
                                                    </select>
                                                    <div x-show="item.keperluan_dana === 'Lainnya'" class="pt-1.5">
                                                        <input type="text" :name="`items[${idx}][keperluan_lainnya]`" x-model="item.keperluan_lainnya" placeholder="Tuliskan detail keperluan dana lainnya..."
                                                               class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488]" />
                                                    </div>
                                                </div>
                                                <!-- Jumlah Dana (Rp) -->
                                                <div class="sm:col-span-5 space-y-1.5">
                                                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                        Nominal Dana <span class="text-rose-500">*</span>
                                                    </label>
                                                    <div class="relative flex items-center">
                                                        <span class="absolute left-3.5 text-xs font-bold text-slate-400 select-none pointer-events-none">Rp</span>
                                                        <input type="number" :name="`items[${idx}][jumlah_dana]`" x-model.number="item.jumlah_dana" min="0" required placeholder="0"
                                                               class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 pl-9 pr-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-bold text-right text-slate-800 font-mono transition" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pilih Tim / Personel (Khusus Bensin Motor / Mobil) -->
                                            <div x-show="item.keperluan_dana === 'Bensin Motor' || item.keperluan_dana === 'Bensin Mobil'"
                                                 x-transition
                                                 class="space-y-2 pt-3 border-t border-slate-100">
                                                <div class="flex items-center justify-between">
                                                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                        Pilih Tim / Personel Lapangan <span class="text-rose-500">*</span>
                                                    </label>
                                                    <span class="text-[11px] font-semibold text-teal-700" x-text="`${item.personel.length} dipilih`"></span>
                                                </div>
                                                <div class="border border-slate-200/90 rounded-xl p-3 bg-slate-50/50 max-h-44 overflow-y-auto">
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                                                        @foreach ($employees as $emp)
                                                        <label class="flex items-center gap-2 text-xs text-slate-700 hover:bg-white p-1.5 rounded-lg border border-transparent hover:border-slate-200/80 cursor-pointer transition">
                                                            <input type="checkbox" :name="`items[${idx}][personel][]`" value="{{ $emp->name }}" x-model="item.personel" class="rounded text-[#0D9488] focus:ring-[#0D9488] border-slate-300">
                                                            <span class="truncate font-medium text-[11px] uppercase">{{ $emp->name }}</span>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Add Item Button & Total Estimasi Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                        <button type="button" @click="addItem()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-dashed border-teal-500/70 bg-teal-50/50 hover:bg-teal-50 text-teal-800 text-xs font-bold transition cursor-pointer">
                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Baris Item</span>
                        </button>

                        <!-- Total Dana Banner (Visible for BELI & DANA) -->
                        <div x-show="jenis === 'BELI' || jenis === 'DANA'"
                             class="rounded-2xl bg-gradient-to-r from-[#022836] to-[#0e6774] text-white px-4 py-3 flex items-center justify-between sm:justify-end gap-4 shadow-xs">
                            <span class="text-xs font-medium text-teal-100/90">Total Estimasi Pengajuan:</span>
                            <span x-text="formatRupiah(calculateTotal())" class="text-sm sm:text-base font-extrabold text-cyan-300 font-mono tracking-tight"></span>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECTION 3: KETERANGAN & ATASAN PENYETUJU   -->
                <!-- ========================================== -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-50 border border-teal-100 text-[#0D9488] flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span>3. Keterangan &amp; Tujuan Persetujuan</span>
                    </label>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- Keterangan -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                Keterangan / Keperluan Operasional <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="keterangan" rows="3" required placeholder="Jelaskan secara singkat tujuan pengajuan ini (contoh: Kebutuhan instalasi pelanggan baru / Perbaikan jalur FO)..."
                                      class="w-full text-xs rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white py-3 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] text-slate-800 placeholder-slate-400 transition"></textarea>
                        </div>

                        <!-- Pilih Atasan / Info Pengajuan Mandiri -->
                        @if (! empty($isAtasan))
                        <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-200/80 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#0D9488] text-white flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-teal-950 block">Pengajuan Mandiri Atasan</span>
                                <p class="text-[11px] text-teal-800 mt-0.5 leading-relaxed">
                                    Sebagai Atasan, permohonan ini otomatis langsung diteruskan ke <strong>Bagian Finance</strong> tanpa melalui persetujuan atasan lain.
                                </p>
                            </div>
                        </div>
                        @else
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                Pilih Atasan Penyetuju <span class="text-rose-500">*</span>
                            </label>
                            <select name="atasan_id" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white py-2.5 px-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#0D9488] font-medium text-slate-800 transition">
                                <option value="">-- Pilih Atasan Langsung --</option>
                                @foreach ($atasans as $atasan)
                                <option value="{{ $atasan->id }}">
                                    {{ $atasan->name }} ({{ $atasan->position ?: ($atasan->division ? 'Atasan ' . $atasan->division : 'Atasan') }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECTION 4: SUBMIT FOOTER                   -->
                <!-- ========================================== -->
                <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                    <a href="{{ route('gomsn.pengajuan.index') }}"
                       class="py-3 px-5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-bold text-center transition">
                        Batal
                    </a>
                    <button type="submit" :disabled="hasOverdue"
                            style="background: linear-gradient(135deg, #022836 0%, #0e6774 55%, #0D9488 100%) !important; color: #ffffff !important;"
                            class="py-3 px-6 rounded-xl hover:opacity-95 active:scale-[0.99] text-white font-bold text-xs sm:text-sm inline-flex items-center justify-center gap-2 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Kirim Pengajuan Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
