<x-app-layout>
    @php
        $hasOverdue = $hasOverdue ?? false;
        $pengajuanList = $pengajuanList ?? collect();
        $isAtasan = $isAtasan ?? false;
        $isFinance = $isFinance ?? false;
        $isSuperAdmin = $isSuperAdmin ?? false;
        $tab = $tab ?? 'my';
        $pendingApprovalsCount = $pendingApprovalsCount ?? 0;
        $financeMetrics = $financeMetrics ?? [
            'total' => 0,
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
            'total_dana' => 0,
        ];
        $divisions = $divisions ?? collect();
        $isFinanceView = ($isFinance && ! $isSuperAdmin);
    @endphp

    <div x-data="{
        showModal: false,
        activeItem: null,
        showRejectReason: false,
        rejectReason: '',
        disburseMethod: '',
        timeString: '',
        previewRealizationUrl: null,
        previewRealizationName: '',
        openModalWith(item) {
            this.activeItem = item;
            this.showRejectReason = false;
            this.rejectReason = '';
            this.disburseMethod = '';
            this.previewRealizationUrl = null;
            this.previewRealizationName = '';
            this.showModal = true;
        },
        updateTime() {
            const d = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const day = days[d.getDay()];
            const date = d.getDate();
            const month = months[d.getMonth()];
            const year = d.getFullYear();
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            const s = String(d.getSeconds()).padStart(2, '0');
            this.timeString = `${day}, ${date} ${month} ${year} pukul ${h}.${m}.${s} WIB`;
        },
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
        }
    }" class="w-full space-y-5">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#pengajuanWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="pengajuanWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Document Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-1.5 leading-snug">
                        @if ($isFinanceView)
                            <span>Pengajuan &amp; Pencairan Dana</span>
                        @elseif ($isAtasan)
                            <span>Pengajuan Saya &amp; Persetujuan</span>
                        @else
                            <span>Pengajuan Dana &amp; Barang Operasional</span>
                        @endif
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        @if ($isFinanceView)
                            Berikut adalah ringkasan seluruh pengajuan dan aktivitas pencairan keuangan secara real-time.
                        @elseif ($isAtasan)
                            Kelola permohonan mandiri divisi dan pantau status persetujuan pengajuan karyawan secara real-time.
                        @else
                            Kelola permohonan dana operasional tim lapangan, pengadaan barang kerja, dan klaim reimbursement.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Flash Alerts -->
        @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1 shadow-2xs">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terdapat kesalahan proses data:</span>
            </div>
            <ul class="list-disc list-inside text-rose-700 pl-2">
                @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Overdue Realization Blocking Banner (§8.1) -->
        @if ($hasOverdue)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 flex items-start gap-3 shadow-sm">
            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h3 class="font-bold text-sm">Pembuatan Pengajuan Baru Diblokir Sementara</h3>
                <p class="text-xs text-amber-800 mt-0.5">
                    Anda memiliki pengajuan yang telah dicairkan namun belum mengunggah bukti nota/struk realisasi melewati batas waktu 7 hari kalender. Harap selesaikan pengunggahan bukti sebelum membuat permohonan baru.
                </p>
            </div>
        </div>
        @endif

        @if ($isFinanceView)
            <!-- ========================================== -->
            <!-- TAMPILAN KHUSUS ROLE FINANCE SESUAI MOCKUP -->
            <!-- ========================================== -->

            <!-- 1. 4 KPI Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Card 1: Total Pengajuan -->
                <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-500 block mb-0.5 truncate">Total Pengajuan</span>
                        <span class="text-xl sm:text-2xl font-heading font-extrabold text-slate-900">{{ $financeMetrics['total'] }}</span>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                        </svg>
                    </div>
                </div>

                <!-- Card 2: Disetujui -->
                <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-500 block mb-0.5 truncate">Disetujui</span>
                        <span class="text-xl sm:text-2xl font-heading font-extrabold text-slate-900">{{ $financeMetrics['approved'] }}</span>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                <!-- Card 3: Ditolak -->
                <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-500 block mb-0.5 truncate">Ditolak</span>
                        <span class="text-xl sm:text-2xl font-heading font-extrabold text-slate-900">{{ $financeMetrics['rejected'] }}</span>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                <!-- Card 4: Total Dana -->
                <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-2.5">
                    <div class="min-w-0">
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-500 block mb-0.5 truncate">Total Dana</span>
                        <span class="text-sm sm:text-xl font-heading font-extrabold text-slate-900 block truncate">
                            Rp {{ number_format($financeMetrics['total_dana'], 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- 2. Filter Bar Toolbar & Search Combined -->
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <form method="GET" action="{{ route('gomsn.pengajuan.index') }}" class="space-y-2.5">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:flex lg:flex-wrap items-center gap-2">
                        <!-- Dropdown Semua Jenis -->
                        <select name="jenis" class="w-full lg:w-36 text-xs rounded-xl border-slate-200 bg-slate-50/70 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 font-semibold text-slate-700">
                            <option value="">Semua Jenis</option>
                            <option value="STOK" {{ request('jenis') === 'STOK' ? 'selected' : '' }}>Stok</option>
                            <option value="DANA" {{ request('jenis') === 'DANA' ? 'selected' : '' }}>Dana</option>
                            <option value="BELI" {{ request('jenis') === 'BELI' ? 'selected' : '' }}>Beli</option>
                            <option value="PERMINTAAN" {{ request('jenis') === 'PERMINTAAN' ? 'selected' : '' }}>Permintaan</option>
                        </select>

                        <!-- Dropdown Semua Divisi -->
                        <select name="divisi" class="w-full lg:w-36 text-xs rounded-xl border-slate-200 bg-slate-50/70 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 font-semibold text-slate-700">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div }}" {{ request('divisi') === $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>

                        <!-- Dropdown Bulan -->
                        @php
                            $months = [
                                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                                '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                                '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                        @endphp
                        <select name="bulan" class="w-full lg:w-36 text-xs rounded-xl border-slate-200 bg-slate-50/70 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 font-semibold text-slate-700">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $mNum => $mLabel)
                                <option value="{{ $mNum }}" {{ (string) request('bulan') === (string) $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>

                        <!-- Dropdown Tahun -->
                        <select name="tahun" class="w-full lg:w-28 text-xs rounded-xl border-slate-200 bg-slate-50/70 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 font-semibold text-slate-700">
                            <option value="">Semua Tahun</option>
                            @for ($y = date('Y') + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ (int) request('tahun') === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>

                        <!-- Tombol Filter / Refresh / Export -->
                        <div class="col-span-2 sm:col-span-4 lg:col-auto flex flex-wrap items-center gap-2">
                            <button type="submit" class="flex-1 sm:flex-initial bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl inline-flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Tampilkan</span>
                            </button>

                            <a href="{{ route('gomsn.pengajuan.index') }}" class="flex-1 sm:flex-initial bg-slate-600 hover:bg-slate-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl inline-flex items-center justify-center gap-1.5 shadow-2xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Refresh</span>
                            </a>

                            <button type="submit" name="export" value="excel" class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl inline-flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Export Excel</span>
                            </button>
                        </div>

                        <div class="hidden xl:flex items-center gap-1.5 text-xs text-slate-500 font-medium ml-auto shrink-0">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                            </svg>
                            <span x-text="timeString">Senin, {{ date('d F Y') }} WIB</span>
                        </div>
                    </div>
                </form>

                <!-- Search Form -->
                <form method="GET" action="{{ route('gomsn.pengajuan.index') }}" class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    @if(request('jenis')) <input type="hidden" name="jenis" value="{{ request('jenis') }}"> @endif
                    @if(request('divisi')) <input type="hidden" name="divisi" value="{{ request('divisi') }}"> @endif
                    @if(request('bulan')) <input type="hidden" name="bulan" value="{{ request('bulan') }}"> @endif
                    @if(request('tahun')) <input type="hidden" name="tahun" value="{{ request('tahun') }}"> @endif

                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari ID / Nomor Pengajuan / Nama Karyawan / Keperluan..."
                               class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/70 py-2 px-3.5 focus:ring-teal-500 focus:border-teal-500 placeholder-slate-400">
                    </div>
                    <button type="submit" style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important;" class="hover:opacity-90 text-white text-xs font-bold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-2xs transition shrink-0 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Cari</span>
                    </button>
                </form>
            </div>

            <!-- 3. Card Grid Daftar Pengajuan (Finance) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div style="background: linear-gradient(135deg, #072A2D 0%, #0E5458 100%);" class="px-4 sm:px-5 py-3.5 flex items-center justify-between text-white">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="text-[#18C4C4] font-extrabold">#</span>
                        <span>Daftar Pengajuan Karyawan</span>
                    </div>
                    <span class="text-xs font-semibold text-teal-100/90 bg-white/10 px-2.5 py-1 rounded-full">
                        Total: {{ $pengajuanList->total() }} data
                    </span>
                </div>

                <div class="p-3.5 sm:p-5 bg-slate-50/50">
                    @if ($pengajuanList->isEmpty())
                        <div class="bg-white rounded-2xl border border-dashed border-slate-200 py-12 px-4 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Tidak Ada Data Pengajuan</p>
                            <p class="text-xs text-slate-400 mt-0.5">Belum ada data pengajuan karyawan yang sesuai dengan filter.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
                            @foreach ($pengajuanList as $item)
                                @php
                                    $isApproved = in_array($item->status, ['dicairkan', 'menunggu_bukti', 'selesai']);
                                    $isRejected = ($item->status === 'ditolak');
                                    
                                    $modalData = [
                                        'id' => $item->id,
                                        'nomor' => $item->nomor_pengajuan,
                                        'created_at' => $item->created_at->format('d M Y - H:i') . ' WIB',
                                        'userName' => $item->user?->name ?? 'Karyawan MSN',
                                        'userEmployeeCode' => $item->user?->employee_code ?? '',
                                        'userDivision' => $item->user?->division ?? '-',
                                        'atasanName' => $item->atasan?->name ?? ($item->user?->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum']) ? 'Pengajuan Mandiri' : 'Atasan Langsung'),
                                        'jenis' => strtolower($item->jenis),
                                        'keperluan' => $item->keperluan ?? '-',
                                        'totalFormatted' => ($item->jenis === 'STOK' ? 'Stok Gudang' : 'Rp' . number_format((float) $item->total_nominal, 0, ',', '.')),
                                        'totalNominal' => (float) $item->total_nominal,
                                        'status' => $item->status,
                                        'buktiTransferPath' => $item->bukti_transfer_path,
                                        'rejectUrl' => route('gomsn.pengajuan.reject', $item->id),
                                        'disburseUrl' => route('gomsn.pengajuan.disburse', $item->id),
                                        'realizeUrl' => route('gomsn.pengajuan.realize', $item->id),
                                        'userId' => $item->user_id,
                                        'isOwner' => ($item->user_id === auth()->id()),
                                        'nominalRealisasi' => (float) ($item->nominal_realisasi ?? $item->total_nominal),
                                        'nominalRealisasiFormatted' => $item->nominal_realisasi !== null ? ('Rp' . number_format((float) $item->nominal_realisasi, 0, ',', '.')) : null,
                                        'selisihRealisasi' => $item->selisih_realisasi !== null ? (float) $item->selisih_realisasi : null,
                                        'selisihRealisasiFormatted' => $item->selisih_realisasi !== null ? ('Rp' . number_format((float) abs($item->selisih_realisasi), 0, ',', '.')) : null,
                                        'buktiRealisasiPath' => $item->bukti_realisasi_path,
                                        'realisasiAt' => $item->realisasi_at?->format('d M Y - H:i') . ' WIB',
                                        'realisasiNotes' => $item->realisasi_notes,
                                        'details' => $item->details->map(function ($d) use ($item) {
                                            return [
                                                'name' => $d->item_name ?: ($d->material?->nama ?? '-'),
                                                'qty' => $d->qty,
                                                'satuan' => $d->satuan ?: ($d->material?->satuan ?? ''),
                                                'unitPrice' => ($item->jenis === 'STOK' ? 'Rp0' : 'Rp' . number_format((float) $d->estimated_unit_price, 0, ',', '.')),
                                                'subtotal' => ($item->jenis === 'STOK' ? 'Rp0' : 'Rp ' . number_format((float) $d->subtotal, 0, ',', '.')),
                                            ];
                                        })->values()->all(),
                                    ];
                                @endphp

                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-teal-300/80 transition-all duration-200 flex flex-col justify-between p-4 gap-3">
                                    <!-- Top Row: ID & Badges -->
                                    <div>
                                        <div class="flex items-start justify-between gap-2 mb-3">
                                            <div class="min-w-0">
                                                <button type="button" @click="openModalWith({{ json_encode($modalData) }})" class="inline-flex items-center gap-1.5 text-left group cursor-pointer">
                                                    <span class="text-xs font-extrabold text-teal-700 group-hover:text-teal-900 bg-teal-50 border border-teal-200/70 px-2 py-0.5 rounded-lg">#{{ $item->id }}</span>
                                                    <span class="text-[11px] font-mono font-semibold text-slate-500 truncate">{{ $item->nomor_pengajuan }}</span>
                                                </button>
                                                <div class="flex items-center gap-1 text-[11px] text-slate-400 mt-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                                                    </svg>
                                                    <span>{{ $item->created_at->format('d M Y, H:i') }} WIB</span>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap items-center justify-end gap-1.5 shrink-0">
                                                @if ($item->jenis === 'STOK')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">Stok</span>
                                                @elseif ($item->jenis === 'DANA')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white">Dana</span>
                                                @elseif ($item->jenis === 'BELI')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white">Beli</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-600 text-white">Permintaan</span>
                                                @endif

                                                @if ($isApproved)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Disetujui
                                                    </span>
                                                @elseif ($isRejected)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Ditolak
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                        Menunggu
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Applicant & Nominal Box -->
                                        <div class="bg-slate-50/90 rounded-xl p-3 border border-slate-100 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-9 h-9 rounded-xl bg-teal-600/10 text-teal-700 font-bold text-xs flex items-center justify-center shrink-0 border border-teal-500/20">
                                                    {{ strtoupper(substr($item->user?->name ?? 'K', 0, 2)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <button type="button" @click="openModalWith({{ json_encode($modalData) }})" class="text-xs font-bold text-slate-800 hover:text-teal-700 truncate block text-left cursor-pointer">
                                                        {{ $item->user?->name ?? 'Karyawan MSN' }}
                                                    </button>
                                                    <span class="text-[11px] text-slate-500 block truncate">
                                                        {{ $item->user?->employee_code ?? '-' }} &bull; {{ $item->user?->division ?? 'Umum' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="text-right shrink-0">
                                                <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Nilai</span>
                                                @if ($item->jenis === 'STOK')
                                                    <span class="text-xs font-extrabold text-amber-600">Stok Gudang</span>
                                                @else
                                                    <span class="text-xs sm:text-sm font-extrabold text-slate-900">
                                                        Rp {{ number_format((float) $item->total_nominal, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($item->keperluan)
                                            <p class="text-xs text-slate-600 line-clamp-2 mt-2.5 leading-relaxed" title="{{ $item->keperluan }}">
                                                <span class="font-semibold text-slate-500">Keperluan:</span> {{ $item->keperluan }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Bottom Actions -->
                                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                                        <button type="button"
                                                @click="openModalWith({{ json_encode($modalData) }})"
                                                style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important; color: #ffffff !important;"
                                                class="w-full sm:w-auto hover:opacity-90 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Lihat Detail</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($pengajuanList->hasPages())
                <div class="p-4 border-t border-slate-200 bg-white">
                    {{ $pengajuanList->links() }}
                </div>
                @endif
            </div>

        @else
            <!-- ============================================== -->
            <!-- TAMPILAN STANDAR (KARYAWAN / ATASAN / ADMIN)   -->
            <!-- ============================================== -->

            <!-- Action Toolbar (Tombol Buat Pengajuan & Approval) -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                <div class="text-xs text-slate-500 font-medium">
                    @if ($isAtasan)
                        Daftar riwayat permohonan operasional mandiri divisi Anda.
                    @else
                        Daftar riwayat permohonan operasional &amp; reimburse Anda.
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($isAtasan)
                    <a href="{{ route('gomsn.pengajuan.approvals') }}" class="btn-secondary min-h-[38px] inline-flex items-center gap-2 text-xs">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Persetujuan Karyawan</span>
                        @if ($pendingApprovalsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white animate-pulse">
                            {{ $pendingApprovalsCount }}
                        </span>
                        @endif
                    </a>
                    @endif

                    @if (! $hasOverdue)
                    <a href="{{ route('gomsn.pengajuan.create') }}" class="btn-primary min-h-[38px] inline-flex items-center gap-2 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Pengajuan Baru
                    </a>
                    @endif
                </div>
            </div>

            <!-- Navigation Tabs (Untuk Atasan / Admin) -->
            @if ($isAtasan || $isSuperAdmin)
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2 text-xs font-semibold overflow-x-auto">
                @if ($isAtasan)
                <a href="{{ route('gomsn.pengajuan.index', ['tab' => 'my']) }}" 
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $tab === 'my' ? 'bg-[#18C4C4] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pengajuan Saya (Mandiri)</span>
                </a>

                <a href="{{ route('gomsn.pengajuan.approvals') }}" 
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0 text-slate-600 hover:bg-slate-100">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Persetujuan Karyawan</span>
                    @if ($pendingApprovalsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                        {{ $pendingApprovalsCount }}
                    </span>
                    @endif
                </a>
                @endif

                @if ($isSuperAdmin)
                <a href="{{ route('gomsn.pengajuan.index', ['tab' => 'all']) }}" 
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $tab === 'all' ? 'bg-[#18C4C4] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Semua Pengajuan</span>
                </a>

                <a href="{{ route('gomsn.pengajuan.index', ['tab' => 'my']) }}" 
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $tab === 'my' ? 'bg-[#18C4C4] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pengajuan Saya Sendiri</span>
                </a>
                @endif
            </div>
            @endif

            <!-- Filter Bar Standar (Hanya Bulan dan Tahun untuk Karyawan Biasa / Non-Admin & Non-Finance) -->
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <form method="GET" action="{{ route('gomsn.pengajuan.index') }}" class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2.5 w-full sm:w-auto">
                    @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @endif

                    @if ($isSuperAdmin || $isFinance)
                        <select name="jenis" onchange="this.form.submit()" class="w-full sm:w-40 text-xs rounded-xl border-slate-200 bg-slate-50/70 focus:ring-teal-500 focus:border-teal-500 py-2 px-3 font-semibold text-slate-700">
                            <option value="">Semua Jenis</option>
                            <option value="DANA" {{ request('jenis') === 'DANA' ? 'selected' : '' }}>Kasbon Dana</option>
                            <option value="BELI" {{ request('jenis') === 'BELI' ? 'selected' : '' }}>Pengadaan (Beli)</option>
                            <option value="STOK" {{ request('jenis') === 'STOK' ? 'selected' : '' }}>Stok Gudang</option>
                            <option value="PERMINTAAN" {{ request('jenis') === 'PERMINTAAN' ? 'selected' : '' }}>Permintaan</option>
                        </select>

                        <select name="divisi" onchange="this.form.submit()" class="w-full sm:w-40 text-xs rounded-xl border-slate-200 bg-slate-50/70 focus:ring-teal-500 focus:border-teal-500 py-2 px-3 font-semibold text-slate-700">
                            <option value="">Semua Divisi</option>
                            @foreach (($divisions ?? collect()) as $div)
                                <option value="{{ $div }}" {{ request('divisi') === $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>

                        <select name="status" onchange="this.form.submit()" class="w-full sm:w-44 text-xs rounded-xl border-slate-200 bg-slate-50/70 focus:ring-teal-500 focus:border-teal-500 py-2 px-3 font-semibold text-slate-700">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="menunggu_atasan" {{ request('status') === 'menunggu_atasan' ? 'selected' : '' }}>Menunggu Atasan</option>
                            <option value="menunggu_finance" {{ request('status') === 'menunggu_finance' ? 'selected' : '' }}>Menunggu Finance</option>
                            <option value="menunggu_executive" {{ request('status') === 'menunggu_executive' ? 'selected' : '' }}>Menunggu Executive</option>
                            <option value="dicairkan" {{ request('status') === 'dicairkan' ? 'selected' : '' }}>Dicairkan</option>
                            <option value="menunggu_bukti" {{ request('status') === 'menunggu_bukti' ? 'selected' : '' }}>Verifikasi Nota</option>
                            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    @endif

                    <!-- Filter Bulan -->
                    @php
                        $monthsList = [
                            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                    @endphp
                    <select name="bulan" onchange="this.form.submit()" class="w-full sm:w-36 text-xs rounded-xl border-slate-200 bg-slate-50/70 focus:ring-teal-500 focus:border-teal-500 py-2 px-3 font-semibold text-slate-700">
                        <option value="">Semua Bulan</option>
                        @foreach ($monthsList as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ (string) request('bulan') === (string) $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>

                    <!-- Filter Tahun -->
                    <select name="tahun" onchange="this.form.submit()" class="w-full sm:w-28 text-xs rounded-xl border-slate-200 bg-slate-50/70 focus:ring-teal-500 focus:border-teal-500 py-2 px-3 font-semibold text-slate-700">
                        <option value="">Semua Tahun</option>
                        @for ($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ (int) request('tahun', date('Y')) === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <button type="submit" name="export" value="excel" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl inline-flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export Excel</span>
                    </button>
                </form>

                <span class="text-xs font-semibold text-slate-500">
                    Menampilkan <strong class="text-slate-800">{{ $pengajuanList->total() }}</strong> data pengajuan
                </span>
            </div>

            <!-- Card Grid List Standar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div style="background: linear-gradient(135deg, #072A2D 0%, #0E5458 100%);" class="px-4 sm:px-5 py-3.5 flex items-center justify-between text-white">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="text-[#18C4C4] font-extrabold">#</span>
                        <span>Daftar Pengajuan</span>
                    </div>
                    <span class="text-xs font-semibold text-teal-100/90 bg-white/10 px-2.5 py-1 rounded-full">
                        Total: {{ $pengajuanList->total() }} data
                    </span>
                </div>

                <div class="p-3.5 sm:p-5 bg-slate-50/50">
                    @if ($pengajuanList->isEmpty())
                        <div class="bg-white rounded-2xl border border-dashed border-slate-200 py-12 px-4 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Belum Ada Riwayat Pengajuan</p>
                            <p class="text-xs text-slate-400 mt-0.5">Silakan klik tombol "Buat Pengajuan Baru" untuk mengajukan kasbon atau barang.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
                            @foreach ($pengajuanList as $item)
                                @php
                                    $modalData = [
                                        'id' => $item->id,
                                        'nomor' => $item->nomor_pengajuan,
                                        'created_at' => $item->created_at->format('d M Y - H:i') . ' WIB',
                                        'userName' => $item->user?->name ?? 'Karyawan MSN',
                                        'userEmployeeCode' => $item->user?->employee_code ?? '',
                                        'userDivision' => $item->user?->division ?? '-',
                                        'atasanName' => $item->atasan?->name ?? ($item->user?->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum']) ? 'Pengajuan Mandiri' : 'Atasan Langsung'),
                                        'jenis' => strtolower($item->jenis),
                                        'keperluan' => $item->keperluan ?? '-',
                                        'totalFormatted' => ($item->jenis === 'STOK' ? 'Stok Gudang' : 'Rp' . number_format((float) $item->total_nominal, 0, ',', '.')),
                                        'totalNominal' => (float) $item->total_nominal,
                                        'status' => $item->status,
                                        'buktiTransferPath' => $item->bukti_transfer_path,
                                        'rejectUrl' => route('gomsn.pengajuan.reject', $item->id),
                                        'disburseUrl' => route('gomsn.pengajuan.disburse', $item->id),
                                        'realizeUrl' => route('gomsn.pengajuan.realize', $item->id),
                                        'userId' => $item->user_id,
                                        'isOwner' => ($item->user_id === auth()->id()),
                                        'nominalRealisasi' => (float) ($item->nominal_realisasi ?? $item->total_nominal),
                                        'nominalRealisasiFormatted' => $item->nominal_realisasi !== null ? ('Rp' . number_format((float) $item->nominal_realisasi, 0, ',', '.')) : null,
                                        'selisihRealisasi' => $item->selisih_realisasi !== null ? (float) $item->selisih_realisasi : null,
                                        'selisihRealisasiFormatted' => $item->selisih_realisasi !== null ? ('Rp' . number_format((float) abs($item->selisih_realisasi), 0, ',', '.')) : null,
                                        'buktiRealisasiPath' => $item->bukti_realisasi_path,
                                        'realisasiAt' => $item->realisasi_at?->format('d M Y - H:i') . ' WIB',
                                        'realisasiNotes' => $item->realisasi_notes,
                                        'details' => $item->details->map(function ($d) use ($item) {
                                            return [
                                                'name' => $d->item_name ?: ($d->material?->nama ?? '-'),
                                                'qty' => $d->qty,
                                                'satuan' => $d->satuan ?: ($d->material?->satuan ?? ''),
                                                'unitPrice' => ($item->jenis === 'STOK' ? 'Rp0' : 'Rp' . number_format((float) $d->estimated_unit_price, 0, ',', '.')),
                                                'subtotal' => ($item->jenis === 'STOK' ? 'Rp0' : 'Rp ' . number_format((float) $d->subtotal, 0, ',', '.')),
                                            ];
                                        })->values()->all(),
                                    ];

                                    $badge = match($item->status) {
                                        'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'menunggu_atasan' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'menunggu_finance' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'menunggu_executive' => 'bg-purple-50 text-purple-800 border-purple-200',
                                        'dicairkan' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
                                        'menunggu_bukti' => 'bg-orange-50 text-orange-800 border-orange-200',
                                        'selesai' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'ditolak' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                @endphp

                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-teal-300/80 transition-all duration-200 flex flex-col justify-between p-4 gap-3">
                                    <div>
                                        <!-- Top Row: Nomor & Badges -->
                                        <div class="flex items-start justify-between gap-2 mb-3">
                                            <div class="min-w-0">
                                                <button type="button" @click="openModalWith({{ json_encode($modalData) }})" class="inline-flex items-center gap-1.5 text-left group cursor-pointer">
                                                    <span class="text-xs font-extrabold text-teal-700 group-hover:text-teal-900 bg-teal-50 border border-teal-200/70 px-2 py-0.5 rounded-lg">#{{ $item->id }}</span>
                                                    <span class="text-[11px] font-mono font-semibold text-slate-500 truncate">{{ $item->nomor_pengajuan }}</span>
                                                </button>
                                                <div class="flex items-center gap-1 text-[11px] text-slate-400 mt-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                                                    </svg>
                                                    <span>{{ $item->created_at->format('d M Y, H:i') }} WIB</span>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap items-center justify-end gap-1.5 shrink-0">
                                                @if ($item->jenis === 'STOK')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">Stok</span>
                                                @elseif ($item->jenis === 'DANA')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white">Dana</span>
                                                @elseif ($item->jenis === 'BELI')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white">Beli</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-600 text-white">Permintaan</span>
                                                @endif

                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge }}">
                                                    {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Applicant & Nominal Box -->
                                        <div class="bg-slate-50/90 rounded-xl p-3 border border-slate-100 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-9 h-9 rounded-xl bg-teal-600/10 text-teal-700 font-bold text-xs flex items-center justify-center shrink-0 border border-teal-500/20">
                                                    {{ strtoupper(substr($item->user?->name ?? 'K', 0, 2)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <button type="button" @click="openModalWith({{ json_encode($modalData) }})" class="text-xs font-bold text-slate-800 hover:text-teal-700 truncate block text-left cursor-pointer">
                                                        {{ $item->user?->name ?? 'Karyawan MSN' }}
                                                    </button>
                                                    <span class="text-[11px] text-slate-500 block truncate">
                                                        {{ $item->user?->employee_code ?? '-' }} &bull; {{ $item->user?->division ?? 'Umum' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="text-right shrink-0">
                                                <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Nilai</span>
                                                @if ($item->jenis === 'STOK')
                                                    <span class="text-xs font-extrabold text-amber-600">Stok Gudang</span>
                                                @elseif ($item->jenis === 'PERMINTAAN' || $item->jenis === 'BARANG')
                                                    <span class="text-xs font-extrabold text-cyan-700">Permintaan Barang</span>
                                                @else
                                                    <span class="text-xs sm:text-sm font-extrabold text-slate-900">
                                                        Rp {{ number_format((float) $item->total_nominal, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($item->keperluan)
                                            <p class="text-xs text-slate-600 line-clamp-2 mt-2.5 leading-relaxed" title="{{ $item->keperluan }}">
                                                <span class="font-semibold text-slate-500">Keperluan:</span> {{ $item->keperluan }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Bottom Actions -->
                                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                                        @if ($item->user_id === auth()->id() && in_array($item->status, ['draft', 'menunggu_atasan']) && ! $item->approvals()->where('action', 'approved')->exists())
                                            <a href="{{ route('gomsn.pengajuan.edit', $item->id) }}" class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition" title="Edit Pengajuan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                <span>Edit</span>
                                            </a>
                                        @endif

                                        <button type="button" 
                                                @click="openModalWith({{ json_encode($modalData) }})"
                                                style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important;"
                                                class="flex-1 sm:flex-initial hover:opacity-90 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Detail</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($pengajuanList->hasPages())
                <div class="p-4 border-t border-slate-200 bg-white">
                    {{ $pengajuanList->links() }}
                </div>
                @endif
            </div>
        @endif

        <!-- ============================================== -->
        <!-- MODAL POPUP: DETAIL PENGAJUAN (SESUAI MOCKUP)  -->
        <!-- ============================================== -->
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="relative bg-white rounded-2xl max-w-4xl lg:max-w-5xl w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all my-8"
                 @click.away="showModal = false">

                <!-- Header Modal Sesuai Tema (#155E63 -> #1F2937) -->
                <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0 shadow-inner">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base tracking-wide text-white leading-tight">Detail Pengajuan</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-white/80 font-mono" x-text="activeItem?.nomor"></span>
                                <span class="text-white/40 text-xs">•</span>
                                <span class="text-xs text-white/80" x-text="activeItem?.created_at"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold text-lg leading-none transition cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 sm:p-7 space-y-6 max-h-[82vh] overflow-y-auto" x-show="activeItem">
                    <!-- Top Two Cards: Informasi Pengaju & Informasi Keuangan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Card 1: Informasi Pengaju -->
                        <div class="border border-slate-200/90 rounded-2xl p-5 bg-slate-50/70 space-y-3.5 shadow-xs">
                            <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Informasi Pengaju</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide"
                                      :class="activeItem?.jenis === 'stok' ? 'bg-cyan-100 text-cyan-800 border border-cyan-200' : (activeItem?.jenis === 'beli' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200')"
                                      x-text="activeItem?.jenis"></span>
                            </div>
                            <div class="space-y-3 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-200 text-teal-700 flex items-center justify-center shrink-0 font-bold">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Nama Karyawan</span>
                                        <strong class="text-slate-900 font-bold text-xs" x-text="activeItem?.userName"></strong>
                                        <span class="text-[10px] text-slate-400 font-mono ml-1" x-text="activeItem?.userEmployeeCode ? '(' + activeItem.userEmployeeCode + ')' : ''"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Divisi / Bagian</span>
                                        <strong class="text-slate-800 text-xs" x-text="activeItem?.userDivision || '-'"></strong>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Atasan Langsung</span>
                                        <strong class="text-slate-900 text-xs" x-text="activeItem?.atasanName || '-'"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Informasi Keuangan & Keperluan -->
                        <div class="border border-slate-200/90 rounded-2xl p-5 bg-slate-50/70 space-y-3.5 shadow-xs">
                            <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Informasi Keuangan</span>
                                <span class="text-[11px] font-medium text-slate-500" x-text="activeItem?.created_at"></span>
                            </div>
                            <div class="space-y-3 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 font-bold">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Total Estimasi</span>
                                        <strong class="text-slate-900 font-extrabold text-sm" x-text="activeItem?.totalFormatted"></strong>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-slate-500 text-[11px] block leading-none">Keterangan / Keperluan</span>
                                        <p class="text-slate-800 font-medium text-xs mt-0.5 leading-relaxed" x-text="activeItem?.keperluan || '-'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Barang Table Sesuai Mockup -->
                    <div class="border border-slate-200/90 rounded-2xl overflow-hidden shadow-2xs">
                        <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center gap-2 font-bold text-xs text-slate-700">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Rincian Item Pengajuan</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-white font-bold text-slate-700">
                                        <th class="py-3 px-4 w-10 text-center">#</th>
                                        <th class="py-3 px-4">Nama Barang / Keperluan</th>
                                        <th class="py-3 px-4 text-center">Jumlah</th>
                                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                                        <th class="py-3 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="(detail, idx) in (activeItem?.details || [])" :key="idx">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="py-3 px-4 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                                            <td class="py-3 px-4 font-semibold text-slate-800" x-text="detail.name"></td>
                                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 font-mono text-xs">
                                                    <span x-text="detail.qty"></span>
                                                    <span class="text-slate-500 font-normal ml-1" x-text="detail.satuan"></span>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-right text-slate-600 font-mono" x-text="detail.unitPrice"></td>
                                            <td class="py-3 px-4 text-right font-bold text-slate-900 font-mono" x-text="detail.subtotal"></td>
                                        </tr>
                                    </template>
                                    <template x-if="!activeItem?.details || activeItem.details.length === 0">
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-slate-400">
                                                Tidak ada rincian item barang/dana.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-emerald-50/80 border-t-2 border-emerald-300 text-slate-900 font-extrabold text-xs">
                                        <td colspan="4" class="py-3 px-4 text-center uppercase tracking-wider text-slate-800">TOTAL</td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-900 text-sm font-mono" x-text="activeItem?.totalFormatted"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    @if ($isFinance)
                    <!-- JIKA JENIS DANA / BELI: DROPDOWN METODE PENCAIRAN -->
                    <template x-if="activeItem?.status === 'menunggu_finance' && activeItem?.jenis !== 'stok'">
                        <div class="space-y-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1.5">
                                    Metode Pencairan: <span class="text-rose-500">*</span>
                                </label>
                                <select x-model="disburseMethod" class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 bg-white font-medium text-slate-700">
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="Transfer Bank BCA">Transfer Bank BCA</option>
                                    <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
                                    <option value="Kas Kecil / Tunai">Kas Kecil / Tunai (Petty Cash)</option>
                                </select>
                            </div>

                            <!-- NOTIFIKASI KETIKA METODE PENCAIRAN BELUM DIPILIH -->
                            <template x-if="!disburseMethod">
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs flex items-center gap-3">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Silakan pilih <strong>Metode Pencairan</strong> di atas terlebih dahulu untuk menampilkan formulir Terima atau Tolak.</span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- AKSI FINANCE UNTUK JENIS STOK (LANGSUNG MUNCUL TERIMA ATAU TOLAK TANPA METODE PENCAIRAN) -->
                    <template x-if="activeItem?.status === 'menunggu_finance' && activeItem?.jenis === 'stok'">
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <!-- FORM TERIMA / SERAHKAN STOK -->
                            <form :action="activeItem?.disburseUrl" method="POST" class="space-y-3 p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl">
                                @csrf
                                <input type="hidden" name="metode_pencairan" value="Penyerahan Stok Gudang">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penyerahan Barang (Opsional):</label>
                                    <input type="text" name="notes" placeholder="Catatan penyerahan barang di gudang..." class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white">
                                </div>
                                <button type="submit" class="w-full bg-[#10B981] hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>✓ Terima &amp; Serahkan Stok Gudang</span>
                                </button>
                            </form>

                            <!-- TOMBOL TOLAK & FORM PENOLAKAN -->
                            <div>
                                <button type="button" 
                                        x-show="!showRejectReason" 
                                        @click="showRejectReason = true"
                                        class="w-full bg-[#EF4444] hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm transition cursor-pointer">
                                    <span>✕</span>
                                    <span>Tolak Pengajuan</span>
                                </button>

                                <form x-show="showRejectReason" :action="activeItem?.rejectUrl" method="POST" class="p-4 bg-rose-50 border border-rose-200 rounded-2xl space-y-2.5">
                                    @csrf
                                    <input type="hidden" name="stage" value="finance">
                                    <input type="hidden" name="metode_pencairan" value="Penyerahan Stok Gudang">
                                    <label class="block text-xs font-bold text-rose-800">Alasan Penolakan Finance: <span class="text-rose-500">*</span></label>
                                    <textarea name="reason" x-model="rejectReason" required rows="2" 
                                              placeholder="Tuliskan alasan penolakan permohonan ini..."
                                              class="w-full text-xs rounded-xl border-rose-300 p-2.5 focus:ring-rose-500 focus:border-rose-500 bg-white"></textarea>
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-2xs cursor-pointer">
                                            Konfirmasi Tolak Pengajuan
                                        </button>
                                        <button type="button" @click="showRejectReason = false" class="text-xs text-slate-600 hover:underline cursor-pointer">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>

                    <!-- AKSI FINANCE UNTUK JENIS DANA / BELI (MUNCUL JIKA SUDAH PILIH METODE PENCAIRAN) -->
                    <template x-if="activeItem?.status === 'menunggu_finance' && activeItem?.jenis !== 'stok' && disburseMethod">
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <!-- FORM TERIMA / CAIRKAN DANA -->
                            <form :action="activeItem?.disburseUrl" method="POST" enctype="multipart/form-data" class="space-y-3 p-4 bg-teal-50/70 border border-teal-200 rounded-2xl">
                                @csrf
                                <input type="hidden" name="metode_pencairan" :value="disburseMethod">
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">
                                        Unggah Bukti Transfer / Pencairan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="file" name="bukti_transfer" required accept="image/*,application/pdf"
                                           class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-600 file:text-white hover:file:bg-teal-700 border border-slate-300 rounded-xl bg-white p-1">
                                    <p class="text-[10px] text-slate-500 mt-1">Format dokumen: JPG, PNG, atau PDF (Maks. 4MB)</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pencairan (Opsional):</label>
                                    <input type="text" name="notes" placeholder="No. referensi transaksi / kas kecil..." class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white">
                                </div>
                                <button type="submit" 
                                        style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important;"
                                        class="w-full hover:opacity-90 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>✓ Terima &amp; Cairkan Dana</span>
                                </button>
                            </form>

                            <!-- TOMBOL TOLAK & FORM PENOLAKAN -->
                            <div>
                                <button type="button" 
                                        x-show="!showRejectReason" 
                                        @click="showRejectReason = true"
                                        class="w-full bg-[#EF4444] hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm transition cursor-pointer">
                                    <span>✕</span>
                                    <span>Tolak Pengajuan</span>
                                </button>

                                <form x-show="showRejectReason" :action="activeItem?.rejectUrl" method="POST" class="p-4 bg-rose-50 border border-rose-200 rounded-2xl space-y-2.5">
                                    @csrf
                                    <input type="hidden" name="stage" value="finance">
                                    <input type="hidden" name="metode_pencairan" :value="disburseMethod">
                                    <label class="block text-xs font-bold text-rose-800">Alasan Penolakan Finance: <span class="text-rose-500">*</span></label>
                                    <textarea name="reason" x-model="rejectReason" required rows="2" 
                                              placeholder="Tuliskan alasan penolakan permohonan ini..."
                                              class="w-full text-xs rounded-xl border-rose-300 p-2.5 focus:ring-rose-500 focus:border-rose-500 bg-white"></textarea>
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-2xs cursor-pointer">
                                            Konfirmasi Tolak Pengajuan
                                        </button>
                                        <button type="button" @click="showRejectReason = false" class="text-xs text-slate-600 hover:underline cursor-pointer">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                    @else
                    <!-- STATUS UNTUK PEMOHON (KARYAWAN / ATASAN MANDIRI) -->
                    <template x-if="activeItem?.status === 'menunggu_atasan'">
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs flex items-center gap-3">
                            <span class="text-lg">⏳</span>
                            <div>
                                <span class="font-bold block">Status: Menunggu Persetujuan Atasan</span>
                                <span class="text-amber-800">Permohonan ini sedang dalam antrean verifikasi oleh Atasan Langsung Anda.</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeItem?.status === 'menunggu_finance'">
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl text-blue-900 text-xs flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-base shrink-0">
                                🏦
                            </div>
                            <div>
                                <span class="font-bold block">Status: Menunggu Finance</span>
                                <span class="text-blue-800">Permohonan telah disetujui atasan dan sedang menunggu verifikasi serta penyerahan/pencairan oleh Bagian Finance.</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeItem?.status === 'menunggu_executive'">
                        <div class="p-4 bg-purple-50 border border-purple-200 rounded-2xl text-purple-900 text-xs flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-base shrink-0">
                                👑
                            </div>
                            <div>
                                <span class="font-bold block">Status: Menunggu Otorisasi Direksi</span>
                                <span class="text-purple-800">Permohonan melebihi batas nominal operasional standar sehingga memerlukan otorisasi Direksi sebelum dicairkan.</span>
                            </div>
                        </div>
                    </template>
                    @endif

                    <!-- STATUS DISPLAY UNTUK YANG SUDAH DISETUJUI / DICAIRKAN -->
                    <template x-if="activeItem?.status === 'dicairkan' || activeItem?.status === 'selesai' || activeItem?.status === 'menunggu_bukti'">
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs space-y-2">
                            <div class="flex items-center gap-2.5 font-bold text-emerald-800">
                                <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                </div>
                                <span class="text-sm">Pengajuan ini telah Disetujui &amp; Diproses oleh Finance</span>
                            </div>
                            <template x-if="activeItem?.buktiTransferPath">
                                <div class="pt-1 pl-9">
                                    <a :href="'/storage/' + activeItem?.buktiTransferPath" target="_blank" class="inline-flex items-center gap-1.5 text-teal-700 hover:text-teal-900 hover:underline font-semibold bg-white border border-emerald-200 px-3 py-1.5 rounded-lg shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Bukti Transfer / Penyerahan</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- ============================================== -->
                    <!-- UPLOAD BUKTI REALISASI / BELI & DANA (ROLE KARYAWAN) -->
                    <!-- ============================================== -->
                    <template x-if="(activeItem?.jenis === 'dana' || activeItem?.jenis === 'beli') && (activeItem?.status === 'dicairkan' || activeItem?.status === 'menunggu_bukti') && !activeItem?.buktiRealisasiPath">
                        <div class="mt-4 border border-teal-300/80 bg-gradient-to-br from-teal-50/80 via-emerald-50/50 to-slate-50/70 rounded-2xl p-5 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-teal-200/80 pb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#155E63] text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-800 leading-tight">Upload Bukti Foto Realisasi / Struk Pembelian</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Pengajuan dana &amp; beli telah disetujui Finance. Harap unggah foto bukti nota/struk dan total riil belanja.</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                                    Wajib Dilaporkan
                                </span>
                            </div>

                            <template x-if="activeItem?.isOwner || {{ auth()->user()->hasRole(['super_admin', 'admin']) ? 'true' : 'false' }}">
                                <form :action="activeItem?.realizeUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                Total Pengeluaran Riil (Rp) <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                                <input type="number" 
                                                       name="nominal_realisasi" 
                                                       :value="activeItem?.totalNominal" 
                                                       required 
                                                       min="0" 
                                                       step="any"
                                                       placeholder="0"
                                                       class="w-full text-xs font-bold pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-800 shadow-2xs">
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1">Estimasi awal diajukan: <strong class="text-slate-700" x-text="activeItem?.totalFormatted"></strong></p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                Foto Bukti Struk / Nota / Kwitansi <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="file" 
                                                   name="bukti_realisasi" 
                                                   required 
                                                   accept="image/*,application/pdf"
                                                   @change="
                                                       const file = $event.target.files[0];
                                                       if (file) {
                                                           previewRealizationName = file.name;
                                                           if (file.type.startsWith('image/')) {
                                                               previewRealizationUrl = URL.createObjectURL(file);
                                                           } else {
                                                               previewRealizationUrl = null;
                                                           }
                                                       } else {
                                                           previewRealizationName = '';
                                                           previewRealizationUrl = null;
                                                       }
                                                   "
                                                   class="w-full text-xs file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#155E63] file:text-white hover:file:bg-[#114b4f] border border-slate-300 rounded-xl bg-white p-1 text-slate-600 shadow-2xs cursor-pointer">
                                            <p class="text-[10px] text-slate-500 mt-1">Format gambar JPG, PNG, atau file PDF (Maksimal 4MB)</p>
                                        </div>
                                    </div>

                                    <!-- Image Preview Thumbnail (Jika user memilih foto) -->
                                    <div x-show="previewRealizationUrl" class="p-3 bg-white border border-teal-200 rounded-xl flex items-center gap-3">
                                        <img :src="previewRealizationUrl" alt="Preview Bukti" class="w-16 h-16 object-cover rounded-lg border border-slate-200 shrink-0">
                                        <div class="text-xs">
                                            <span class="font-bold text-slate-800 block">Preview Foto Terpilih:</span>
                                            <span class="text-slate-500 font-mono text-[11px]" x-text="previewRealizationName"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                            Catatan / Rincian Pengeluaran (Opsional)
                                        </label>
                                        <input type="text" 
                                               name="realisasi_notes" 
                                               placeholder="Contoh: Pembelian operasional di toko ABC, nota terlampir..." 
                                               class="w-full text-xs py-2.5 px-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-800 shadow-2xs">
                                    </div>

                                    <div class="pt-1">
                                        <button type="submit" 
                                                style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important;"
                                                class="w-full text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-md hover:opacity-95 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span>Unggah Foto Bukti &amp; Selesaikan Pengajuan</span>
                                        </button>
                                    </div>
                                </form>
                            </template>

                            <template x-if="!activeItem?.isOwner && !{{ auth()->user()->hasRole(['super_admin', 'admin']) ? 'true' : 'false' }}">
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-center gap-2">
                                    <span>⏳</span>
                                    <span>Menunggu pemohon (<strong x-text="activeItem?.userName"></strong>) mengunggah foto bukti nota/struk realisasi pembelian.</span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- JIKA SUDAH SELESAI / SUDAH ADA BUKTI FOTO REALISASI -->
                    <template x-if="(activeItem?.jenis === 'dana' || activeItem?.jenis === 'beli') && (activeItem?.buktiRealisasiPath || activeItem?.status === 'selesai')">
                        <div class="mt-4 border border-emerald-200 bg-emerald-50/70 rounded-2xl p-5 shadow-xs space-y-3.5">
                            <div class="flex items-center justify-between border-b border-emerald-200/80 pb-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                                        ✓
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-emerald-900 leading-tight">Bukti Foto Realisasi Pembelian Telah Diunggah</h4>
                                        <p class="text-[11px] text-emerald-700 mt-0.5" x-text="activeItem?.realisasiAt ? 'Diunggah pada: ' + activeItem.realisasiAt : 'Pengajuan telah selesai direalisasikan'"></p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                    Realisasi Selesai
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                                <div class="bg-white/90 border border-emerald-100 rounded-xl p-3 shadow-2xs">
                                    <span class="text-[11px] text-slate-500 block">Total Realisasi Pengeluaran Riil:</span>
                                    <span class="text-sm font-bold text-emerald-800" x-text="activeItem?.nominalRealisasiFormatted || activeItem?.totalFormatted"></span>
                                </div>
                                <div class="bg-white/90 border border-emerald-100 rounded-xl p-3 shadow-2xs" x-show="activeItem?.selisihRealisasi !== null && activeItem?.selisihRealisasi !== undefined">
                                    <span class="text-[11px] text-slate-500 block">Status Selisih Anggaran:</span>
                                    <span class="text-xs font-bold"
                                          :class="activeItem?.selisihRealisasi > 0 ? 'text-teal-700' : (activeItem?.selisihRealisasi < 0 ? 'text-rose-700' : 'text-slate-700')"
                                          x-text="activeItem?.selisihRealisasi > 0 ? ('Sisa Dikembalikan: ' + activeItem?.selisihRealisasiFormatted) : (activeItem?.selisihRealisasi < 0 ? ('Kekurangan: ' + activeItem?.selisihRealisasiFormatted) : 'Sesuai Estimasi Awal (Pas)')">
                                    </span>
                                </div>
                            </div>

                            <template x-if="activeItem?.realisasiNotes">
                                <div class="bg-white/90 border border-emerald-100 rounded-xl p-3 text-xs shadow-2xs">
                                    <span class="text-[11px] text-slate-500 block font-semibold mb-0.5">Catatan Pembelian:</span>
                                    <p class="text-slate-700" x-text="activeItem?.realisasiNotes"></p>
                                </div>
                            </template>

                            <template x-if="activeItem?.buktiRealisasiPath">
                                <div class="mt-3 bg-white p-3.5 rounded-xl border border-emerald-200/90 shadow-2xs space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Foto Bukti Realisasi / Kwitansi Pembelian</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a :href="'/storage/' + activeItem?.buktiRealisasiPath" target="_blank" class="text-xs text-teal-700 hover:text-teal-900 hover:underline font-bold inline-flex items-center gap-1">
                                                <span>Buka Ukuran Penuh</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Foto Visual Inline (Jika Gambar) -->
                                    <template x-if="!activeItem?.buktiRealisasiPath.toLowerCase().endsWith('.pdf')">
                                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center max-h-80">
                                            <img :src="'/storage/' + activeItem?.buktiRealisasiPath" 
                                                 alt="Bukti Foto Realisasi Pembelian" 
                                                 class="max-h-80 w-auto object-contain rounded-lg shadow-inner group-hover:scale-[1.02] transition duration-200">
                                            <a :href="'/storage/' + activeItem?.buktiRealisasiPath" target="_blank" class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-xs font-bold gap-1.5 backdrop-blur-2xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Klik untuk Memperbesar Gambar</span>
                                            </a>
                                        </div>
                                    </template>

                                    <!-- Tautan Dokumen (Jika PDF) -->
                                    <template x-if="activeItem?.buktiRealisasiPath.toLowerCase().endsWith('.pdf')">
                                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                                            <div class="flex items-center gap-2.5">
                                                <span class="text-2xl">📄</span>
                                                <div>
                                                    <span class="text-xs font-bold text-slate-800 block">Dokumen Lampiran Bukti PDF</span>
                                                    <span class="text-[11px] text-slate-500 font-mono" x-text="activeItem?.buktiRealisasiPath.split('/').pop()"></span>
                                                </div>
                                            </div>
                                            <a :href="'/storage/' + activeItem?.buktiRealisasiPath" target="_blank" class="px-3.5 py-1.5 bg-[#155E63] text-white rounded-lg text-xs font-bold hover:bg-[#114b4f] transition shadow-2xs">
                                                Buka Dokumen PDF
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- STATUS DISPLAY UNTUK YANG DITOLAK -->
                    <template x-if="activeItem?.status === 'ditolak'">
                        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-900 text-xs flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-base shrink-0">
                                ✕
                            </div>
                            <div>
                                <span class="font-bold block text-rose-900">Status: Ditolak</span>
                                <span class="text-rose-700">Pengajuan ini telah ditolak.</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
