<x-app-layout>
    <div class="space-y-6" x-data="{ 
        detailModal: false,
        approveModal: false, 
        rejectModal: false, 
        selectedActionUrl: '', 
        selectedNomor: '',
        activeItem: null,
        showRejectReason: false,
        rejectReason: '',
        openDetail(item) {
            this.activeItem = item;
            this.showRejectReason = false;
            this.rejectReason = '';
            this.detailModal = true;
        },
        openApprove(url, nomor) {
            this.selectedActionUrl = url;
            this.selectedNomor = nomor;
            this.approveModal = true;
        },
        openReject(url, nomor) {
            this.selectedActionUrl = url;
            this.selectedNomor = nomor;
            this.rejectModal = true;
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
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#approvalsWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="approvalsWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Check Badge Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Persetujuan Pengajuan Karyawan
                    </h1>
                    <p class="text-[11px] sm:text-[13px] text-teal-100/85 mt-0.5 leading-relaxed">
                        Verifikasi dan setujui permohonan kasbon, pengadaan barang, atau stok tim divisi sebelum diteruskan ke Finance.
                    </p>
                </div>
            </div>
        </div>

        <!-- 4 Metric KPI Cards (2x2 Grid on Mobile, 4 Columns on Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Card 1: Total Pengajuan -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Total Pengajuan</span>
                    <span class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight block">{{ $totalCount }}</span>
                </div>
            </div>

            <!-- Card 2: Disetujui -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Disetujui</span>
                    <span class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight block">{{ $approvedCount }}</span>
                </div>
            </div>

            <!-- Card 3: Ditolak -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Ditolak</span>
                    <span class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight block">{{ $rejectedCount }}</span>
                </div>
            </div>

            <!-- Card 4: Total Dana -->
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                        <circle cx="12" cy="12" r="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h.01M18 12h.01"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Total Dana</span>
                    <span class="text-sm sm:text-xl font-extrabold text-slate-800 leading-tight block truncate">Rp {{ number_format((float) $totalDana, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card (Responsive Grid on Mobile & Desktop) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs space-y-3">
            <form method="GET" action="{{ route('gomsn.pengajuan.approvals') }}" class="space-y-3">
                <!-- Row 1: Dropdown Semua Jenis, Semua Divisi, Bulan, Tahun, Tombol Filter, Refresh & Export Excel -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:flex lg:flex-wrap items-center gap-2.5">
                    <!-- Dropdown Jenis -->
                    <div class="col-span-1 lg:w-40">
                        <select name="jenis" class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 bg-white font-medium text-slate-700">
                            <option value="">Semua Jenis</option>
                            <option value="DANA" {{ request('jenis') === 'DANA' ? 'selected' : '' }}>Kasbon Dana</option>
                            <option value="BELI" {{ request('jenis') === 'BELI' ? 'selected' : '' }}>Pengadaan (Beli)</option>
                            <option value="STOK" {{ request('jenis') === 'STOK' ? 'selected' : '' }}>Stok Gudang</option>
                            <option value="PERMINTAAN" {{ request('jenis') === 'PERMINTAAN' ? 'selected' : '' }}>Permintaan</option>
                        </select>
                    </div>

                    <!-- Dropdown Divisi -->
                    <div class="col-span-1 lg:w-40">
                        <select name="divisi" class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 bg-white font-medium text-slate-700">
                            <option value="">Semua Divisi</option>
                            @foreach (($divisions ?? collect()) as $div)
                                <option value="{{ $div }}" {{ request('divisi') === $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dropdown Bulan -->
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $curSelectedMonth = isset($selectedMonth) ? $selectedMonth : (request()->has('month') ? request('month') : (request()->has('bulan') ? request('bulan') : (int) date('n')));
                        $curSelectedYear = isset($selectedYear) ? $selectedYear : (request()->has('year') ? request('year') : (request()->has('tahun') ? request('tahun') : (int) date('Y')));
                    @endphp
                    <div class="col-span-1 lg:w-36">
                        <select name="month" class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 bg-white font-medium text-slate-700">
                            <option value="all" {{ (string) $curSelectedMonth === 'all' || (string) $curSelectedMonth === '' ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach ($months as $num => $mName)
                            <option value="{{ $num }}" {{ (string) $curSelectedMonth === (string) $num ? 'selected' : '' }}>
                                {{ $mName }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dropdown Tahun -->
                    <div class="col-span-1 lg:w-32">
                        <select name="year" class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 bg-white font-medium text-slate-700">
                            <option value="all" {{ (string) $curSelectedYear === 'all' || (string) $curSelectedYear === '' ? 'selected' : '' }}>Semua Tahun</option>
                            @for ($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ (string) $curSelectedYear === (string) $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Action Buttons (Filter, Refresh & Export Excel) -->
                    <div class="col-span-2 sm:col-span-4 lg:w-auto flex flex-wrap items-center gap-2">
                        <button type="submit" 
                                style="background-color: #2563EB !important; color: #ffffff !important;"
                                class="flex-1 lg:flex-initial justify-center hover:opacity-90 text-white font-bold text-xs px-4 py-2.5 rounded-xl inline-flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Filter</span>
                        </button>

                        <a href="{{ route('gomsn.pengajuan.approvals') }}" 
                           style="background-color: #475569 !important; color: #ffffff !important;"
                           class="flex-1 lg:flex-initial justify-center hover:opacity-90 text-white font-bold text-xs px-4 py-2.5 rounded-xl inline-flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Refresh</span>
                        </a>

                        <button type="submit"
                                name="export"
                                value="excel"
                                title="Export Excel"
                                class="flex-initial justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs p-2.5 sm:px-4 sm:py-2.5 rounded-xl inline-flex items-center gap-1.5 shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 sm:w-3.5 sm:h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="hidden sm:inline">Export Excel</span>
                        </button>
                    </div>
                </div>

                <!-- Row 2: Search Input Cari ID / Username / Jenis + Tombol Cari Teal -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari ID / Username / Jenis..."
                               class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3.5 focus:ring-teal-700 focus:border-teal-700 placeholder-slate-400 bg-white">
                    </div>
                    <button type="submit" 
                            style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important; color: #ffffff !important;"
                            class="hover:opacity-90 text-white font-bold text-xs px-5 py-2.5 rounded-xl inline-flex items-center gap-1.5 shadow-2xs transition shrink-0 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Pengajuan (Responsive Card Grid) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <!-- Header Banner -->
            <div class="px-5 py-4 text-white flex items-center justify-between font-bold text-sm tracking-wide"
                 style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                <div class="flex items-center gap-2.5 text-white">
                    <span class="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center font-mono text-sm font-bold">#</span>
                    <span>Daftar Pengajuan</span>
                </div>
                <span class="px-3 py-1 rounded-full bg-white/15 text-xs text-white font-semibold">
                    Total: {{ $pengajuanList->total() }} data
                </span>
            </div>

            @if ($pengajuanList->isEmpty())
                <div class="p-6 bg-slate-50/50">
                    <div class="py-12 px-4 text-center bg-white rounded-2xl border border-slate-200/70">
                        <div class="max-w-xs mx-auto space-y-2">
                            <div class="w-12 h-12 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-slate-400 text-xl font-bold">
                                ✓
                            </div>
                            <p class="font-bold text-slate-700 text-sm">Tidak ada data pengajuan karyawan</p>
                            <p class="text-xs text-slate-400 leading-relaxed">Semua permohonan dari karyawan telah diproses atau filter pencarian tidak menemukan hasil.</p>
                        </div>
                    </div>
                </div>
            @else
                <!-- DESKTOP VIEW: Table List (hidden md:table / visible on desktop) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs min-w-[850px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4 font-bold">No. Pengajuan</th>
                                <th class="py-3.5 px-4 font-bold">Tanggal</th>
                                <th class="py-3.5 px-4 font-bold">Pengaju</th>
                                <th class="py-3.5 px-4 font-bold text-center">Jenis</th>
                                <th class="py-3.5 px-4 font-bold text-right">Total Pengajuan</th>
                                <th class="py-3.5 px-4 font-bold text-center">Status</th>
                                <th class="py-3.5 px-4 font-bold text-center w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($pengajuanList as $item)
                                @php
                                    $modalData = [
                                        'id' => $item->id,
                                        'nomor' => $item->nomor_pengajuan,
                                        'created_at' => $item->created_at->format('d M Y - H:i') . ' WIB',
                                        'userName' => $item->user?->name ?? 'Karyawan MSN',
                                        'userEmployeeCode' => $item->user?->employee_code ?? '',
                                        'userDivision' => $item->user?->division ?? '-',
                                        'atasanName' => $item->atasan?->name ?? 'Atasan Langsung',
                                        'jenis' => strtolower($item->jenis),
                                        'keperluan' => $item->keperluan ?? '-',
                                        'totalFormatted' => ($item->jenis === 'STOK' ? 'Stok Gudang' : 'Rp ' . number_format((float) $item->total_nominal, 0, ',', '.')),
                                        'totalNominal' => (float) $item->total_nominal,
                                        'status' => $item->status,
                                        'approveUrl' => route('gomsn.pengajuan.approve-atasan', $item->id),
                                        'rejectUrl' => route('gomsn.pengajuan.reject', $item->id),
                                        'nominalRealisasi' => (float) ($item->nominal_realisasi ?? $item->total_nominal),
                                        'nominalRealisasiFormatted' => $item->nominal_realisasi !== null ? ('Rp ' . number_format((float) $item->nominal_realisasi, 0, ',', '.')) : null,
                                        'selisihRealisasi' => $item->selisih_realisasi !== null ? (float) $item->selisih_realisasi : null,
                                        'selisihRealisasiFormatted' => $item->selisih_realisasi !== null ? ('Rp ' . number_format((float) abs($item->selisih_realisasi), 0, ',', '.')) : null,
                                        'buktiRealisasiPath' => $item->bukti_realisasi_path,
                                        'realisasiAt' => $item->realisasi_at?->format('d M Y - H:i') . ' WIB',
                                        'realisasiNotes' => $item->realisasi_notes,
                                        'details' => $item->details->map(function ($d) use ($item) {
                                            return [
                                                'name' => $d->item_name ?: ($d->material?->nama ?? '-'),
                                                'qty' => $d->qty,
                                                'satuan' => $d->satuan ?: ($d->material?->satuan ?? ''),
                                                'unitPrice' => ($item->jenis === 'STOK' ? 'Rp0' : 'Rp ' . number_format((float) $d->estimated_unit_price, 0, ',', '.')),
                                                'subtotal' => ($item->jenis === 'STOK' ? 'Rp0' : 'Rp ' . number_format((float) $d->subtotal, 0, ',', '.')),
                                            ];
                                        })->values()->all(),
                                    ];
                                    $isApprovedStatus = in_array($item->status, ['menunggu_finance', 'menunggu_executive', 'dicairkan', 'menunggu_bukti', 'selesai']);
                                @endphp
                                <tr class="hover:bg-teal-50/30 transition-colors">
                                    <!-- No. Pengajuan -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-extrabold text-[#155E63] bg-teal-50 border border-teal-200/70 px-1.5 py-0.5 rounded-md">#{{ $item->id }}</span>
                                            <button type="button" @click="openDetail({{ json_encode($modalData) }})" class="font-mono font-bold text-slate-800 hover:text-teal-700 hover:underline cursor-pointer text-left">
                                                {{ $item->nomor_pengajuan }}
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="py-3.5 px-4 whitespace-nowrap text-slate-700">
                                        <div class="font-medium text-slate-800">{{ $item->created_at->format('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $item->created_at->format('H:i') }} WIB</div>
                                    </td>

                                    <!-- Pengaju -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            @if ($item->user?->avatar_url)
                                                <img src="{{ $item->user->avatar_url }}" alt="" class="w-8 h-8 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                            @endif
                                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#155E63] to-slate-800 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs" style="{{ $item->user?->avatar_url ? 'display:none;' : '' }}">
                                                {{ $item->user?->initials ?? strtoupper(substr($item->user?->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-bold text-slate-800 block truncate leading-tight">{{ $item->user?->name ?? 'Karyawan MSN' }}</span>
                                                <span class="text-[10px] text-slate-400 block leading-tight mt-0.5">{{ $item->user?->employee_code ?: 'KR'.$item->user_id }} &bull; {{ $item->user?->division ?: '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Jenis -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if ($item->jenis === 'STOK')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">Stok</span>
                                        @elseif ($item->jenis === 'DANA')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Dana</span>
                                        @elseif ($item->jenis === 'BELI')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Beli</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $item->jenis }}</span>
                                        @endif
                                    </td>

                                    <!-- Total Pengajuan -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        @if ($item->jenis === 'STOK')
                                            <span class="text-amber-700 font-semibold font-sans text-xs">Stok Gudang</span>
                                        @else
                                            <span class="font-mono font-bold text-slate-900 text-xs">Rp {{ number_format((float) $item->total_nominal, 0, ',', '.') }}</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if ($isApprovedStatus)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span>✓</span>
                                                <span>Disetujui</span>
                                            </span>
                                        @elseif ($item->status === 'ditolak')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span>✕</span>
                                                <span>Ditolak</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Menunggu</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    @click="openDetail({{ json_encode($modalData) }})"
                                                    style="background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%) !important; color: #ffffff !important;"
                                                    class="hover:opacity-90 text-white font-bold text-xs px-2.5 py-1.5 rounded-xl shadow-2xs transition inline-flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span>Detail</span>
                                            </button>

                                            @if ($item->status === 'menunggu_atasan')
                                                <button type="button" 
                                                        @click="openApprove('{{ route('gomsn.pengajuan.approve-atasan', $item->id) }}', '{{ $item->nomor_pengajuan }}')"
                                                        style="background-color: #059669 !important; color: #ffffff !important;"
                                                        class="hover:opacity-90 text-white font-bold text-xs px-2.5 py-1.5 rounded-xl shadow-2xs transition inline-flex items-center gap-1 cursor-pointer">
                                                    <span>✓</span>
                                                    <span>Setujui</span>
                                                </button>
                                                <button type="button" 
                                                        @click="openReject('{{ route('gomsn.pengajuan.reject', $item->id) }}', '{{ $item->nomor_pengajuan }}')"
                                                        class="hover:bg-rose-100 text-rose-700 font-bold text-xs px-2.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50 transition inline-flex items-center gap-1 cursor-pointer">
                                                    <span>✕</span>
                                                    <span>Tolak</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- MOBILE VIEW: Cards Container (md:hidden) -->
                <div class="md:hidden p-4 sm:p-5 bg-slate-50/50 space-y-3.5">
                    <div class="space-y-3">
                        @foreach ($pengajuanList as $item)
                            @php
                                $modalData = [
                                    'id' => $item->id,
                                    'nomor' => $item->nomor_pengajuan,
                                    'created_at' => $item->created_at->format('d M Y - H:i') . ' WIB',
                                    'userName' => $item->user?->name ?? 'Karyawan MSN',
                                    'userEmployeeCode' => $item->user?->employee_code ?? '',
                                    'userDivision' => $item->user?->division ?? '-',
                                    'atasanName' => $item->atasan?->name ?? 'Atasan Langsung',
                                    'jenis' => strtolower($item->jenis),
                                    'keperluan' => $item->keperluan ?? '-',
                                    'totalFormatted' => ($item->jenis === 'STOK' ? 'Stok Gudang' : 'Rp' . number_format((float) $item->total_nominal, 0, ',', '.')),
                                    'totalNominal' => (float) $item->total_nominal,
                                    'status' => $item->status,
                                    'approveUrl' => route('gomsn.pengajuan.approve-atasan', $item->id),
                                    'rejectUrl' => route('gomsn.pengajuan.reject', $item->id),
                                    'nominalRealisasi' => (float) ($item->nominal_realisasi ?? $item->total_nominal),
                                    'nominalRealisasiFormatted' => $item->nominal_realisasi !== null ? ('Rp ' . number_format((float) $item->nominal_realisasi, 0, ',', '.')) : null,
                                    'selisihRealisasi' => $item->selisih_realisasi !== null ? (float) $item->selisih_realisasi : null,
                                    'selisihRealisasiFormatted' => $item->selisih_realisasi !== null ? ('Rp ' . number_format((float) abs($item->selisih_realisasi), 0, ',', '.')) : null,
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
                                $isApprovedStatus = in_array($item->status, ['menunggu_finance', 'menunggu_executive', 'dicairkan', 'menunggu_bukti', 'selesai']);
                            @endphp

                            <!-- Single Pengajuan Card -->
                            <div class="bg-white rounded-2xl border border-slate-200/90 hover:border-teal-400/80 shadow-2xs hover:shadow-md transition-all duration-200 p-4 flex flex-col justify-between gap-3.5">
                                <div class="space-y-3">
                                    <!-- Top Row: ID + Date & Badges -->
                                    <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-slate-100">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono font-extrabold text-xs text-[#155E63] bg-teal-50 border border-teal-200/70 px-2 py-0.5 rounded-lg">#{{ $item->id }}</span>
                                                <span class="font-mono text-[11px] font-semibold text-slate-500 truncate">{{ $item->nomor_pengajuan }}</span>
                                            </div>
                                            <span class="block text-[11px] text-slate-400 mt-1">
                                                {{ $item->created_at->format('d M Y • H:i') }} WIB
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <!-- Jenis Badge -->
                                            @if ($item->jenis === 'STOK')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">Stok</span>
                                            @elseif ($item->jenis === 'DANA')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Dana</span>
                                            @elseif ($item->jenis === 'BELI')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Beli</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $item->jenis }}</span>
                                            @endif

                                            <!-- Status Badge -->
                                            @if ($isApprovedStatus)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span>✓</span>
                                                    <span>Disetujui</span>
                                                </span>
                                            @elseif ($item->status === 'ditolak')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span>✕</span>
                                                    <span>Ditolak</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    <span>Menunggu</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Applicant Row -->
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            @if ($item->user?->avatar_url)
                                                <img src="{{ $item->user->avatar_url }}" alt="" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                            @endif
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#155E63] to-slate-800 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs" style="{{ $item->user?->avatar_url ? 'display:none;' : '' }}">
                                                {{ $item->user?->initials ?? substr($item->user?->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-900 text-xs truncate">{{ $item->user?->name ?? 'Karyawan MSN' }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono truncate">
                                                    {{ $item->user?->employee_code ?: '-' }}
                                                    @if ($item->user?->division)
                                                        &bull; {{ $item->user->division }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Nilai</span>
                                            <span class="text-xs sm:text-sm font-extrabold text-slate-900 font-mono">
                                                {{ $item->jenis === 'STOK' ? 'Stok Gudang' : 'Rp ' . number_format((float) $item->total_nominal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Keperluan Box -->
                                    @if ($item->keperluan && $item->keperluan !== '-')
                                        <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2 text-xs text-slate-600 line-clamp-2">
                                            {{ $item->keperluan }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Card Action Footer -->
                                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <button type="button" 
                                            @click="openDetail({{ json_encode($modalData) }})"
                                            style="background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%) !important; color: #ffffff !important;"
                                            class="hover:opacity-90 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-2xs transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </button>

                                    @if ($item->status === 'menunggu_atasan')
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" 
                                                    @click="openApprove('{{ route('gomsn.pengajuan.approve-atasan', $item->id) }}', '{{ $item->nomor_pengajuan }}')"
                                                    style="background-color: #059669 !important; color: #ffffff !important;"
                                                    class="hover:opacity-90 text-white font-bold text-xs px-3 py-2 rounded-xl shadow-2xs transition inline-flex items-center gap-1 cursor-pointer">
                                                <span>✓</span>
                                                <span>Setujui</span>
                                            </button>
                                            <button type="button" 
                                                    @click="openReject('{{ route('gomsn.pengajuan.reject', $item->id) }}', '{{ $item->nomor_pengajuan }}')"
                                                    class="hover:bg-rose-100 text-rose-700 font-bold text-xs px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 transition inline-flex items-center gap-1 cursor-pointer">
                                                <span>✕</span>
                                                <span>Tolak</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-medium px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-100">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>Sudah diproses</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($pengajuanList->hasPages())
            <div class="p-4 border-t border-slate-200 bg-white">
                {{ $pengajuanList->withQueryString()->links() }}
            </div>
            @endif
        </div>

        <!-- Modal Setujui Cepat -->
        <div x-show="approveModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200" 
                 @click.outside="approveModal = false">
                <div class="flex items-center gap-3 text-emerald-600">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center font-bold text-lg shrink-0">
                        ✓
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-800">Setujui Pengajuan Karyawan</h3>
                        <p class="text-xs text-slate-500 font-mono" x-text="selectedNomor"></p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Dengan menyetujui pengajuan ini, permohonan akan diverifikasi secara resmi dan diteruskan langsung ke <strong>Bagian Finance</strong> untuk proses pencairan dana atau pengeluaran barang.
                </p>

                <form :action="selectedActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Contoh: Kebutuhan telah dikoordinasikan dan disetujui untuk maintenance." class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="approveModal = false" class="btn-secondary !px-4 !py-2 text-xs">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary !px-5 !py-2 text-xs bg-emerald-600 hover:bg-emerald-700">
                            Ya, Setujui &amp; Teruskan ke Finance
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Tolak Cepat -->
        <div x-show="rejectModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200" 
                 @click.outside="rejectModal = false">
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center font-bold text-lg shrink-0">
                        ✕
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-800">Tolak Pengajuan Karyawan</h3>
                        <p class="text-xs text-slate-500 font-mono" x-text="selectedNomor"></p>
                    </div>
                </div>

                <form :action="selectedActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="stage" value="atasan">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea name="reason" rows="3" required placeholder="Jelaskan alasan penolakan agar karyawan dapat memahami atau merevisi pengajuan..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-2 focus:ring-rose-200 focus:border-rose-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="rejectModal = false" class="btn-secondary !px-4 !py-2 text-xs">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary !px-5 !py-2 text-xs bg-rose-600 hover:bg-rose-700">
                            Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL DETAIL PENGAJUAN LENGKAP UNTUK ATASAN   -->
        <!-- ============================================== -->
        <div x-show="detailModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="relative bg-white rounded-2xl max-w-4xl lg:max-w-5xl w-full shadow-2xl overflow-hidden border border-slate-200 transform transition-all my-8"
                 @click.away="detailModal = false">

                <!-- Header Modal Sesuai Tema (#155E63 -> #1F2937) -->
                <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important; color: #ffffff !important;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0 shadow-inner">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base tracking-wide text-white leading-tight">Detail Pengajuan Karyawan</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-white/80 font-mono" x-text="activeItem?.nomor"></span>
                                <span class="text-white/40 text-xs">•</span>
                                <span class="text-xs text-white/80" x-text="activeItem?.created_at"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="detailModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold text-lg leading-none transition cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 space-y-5 max-h-[80vh] overflow-y-auto" x-show="activeItem">
                    <!-- Top Two Cards: Informasi Pengaju & Informasi Keuangan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Card 1: Informasi Pengaju -->
                        <div class="border border-slate-200/90 rounded-2xl p-4 bg-slate-50/70 space-y-3">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Informasi Pengaju</span>
                            <div class="space-y-2.5 text-xs">
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
                                        <strong class="text-slate-800 text-xs" x-text="activeItem?.userDivision"></strong>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Jenis Pengajuan</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wide"
                                              :class="activeItem?.jenis === 'stok' ? 'bg-cyan-100 text-cyan-800' : (activeItem?.jenis === 'beli' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800')"
                                              x-text="activeItem?.jenis"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Informasi Keuangan -->
                        <div class="border border-slate-200/90 rounded-2xl p-4 bg-slate-50/70 space-y-3">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Informasi Keuangan</span>
                            <div class="space-y-2.5 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 font-bold">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Total Estimasi</span>
                                        <strong class="text-slate-900 font-extrabold text-sm" x-text="activeItem?.totalFormatted"></strong>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-[11px] block leading-none">Waktu Diajukan</span>
                                        <span class="text-slate-800 font-medium text-xs" x-text="activeItem?.created_at"></span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z" clip-rule="evenodd"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-slate-500 text-[11px] block leading-none">Keterangan / Keperluan</span>
                                        <span class="text-slate-800 font-medium text-xs block truncate" x-text="activeItem?.keperluan"></span>
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

                    <!-- TINDAKAN PERSETUJUAN ATASAN DI DALAM MODAL -->
                    <template x-if="activeItem?.status === 'menunggu_atasan'">
                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <!-- FORM SETUJUI -->
                            <form :action="activeItem?.approveUrl" method="POST" class="space-y-3 p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Persetujuan Atasan (Opsional):</label>
                                    <input type="text" name="notes" placeholder="Contoh: Kebutuhan telah dikoordinasikan dan disetujui..." class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white">
                                </div>
                                <button type="submit" class="w-full bg-[#10B981] hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>✓ Setujui &amp; Teruskan ke Finance</span>
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
                                    <input type="hidden" name="stage" value="atasan">
                                    <label class="block text-xs font-bold text-rose-800">Alasan Penolakan Atasan: <span class="text-rose-500">*</span></label>
                                    <textarea name="reason" x-model="rejectReason" required rows="2" 
                                              placeholder="Tuliskan alasan penolakan permohonan ini..."
                                              class="w-full text-xs rounded-lg border-rose-300 p-2.5 focus:ring-rose-500 focus:border-rose-500 bg-white"></textarea>
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

                    <!-- STATUS DISPLAY UNTUK YANG SUDAH TIDAK MENUNGGU ATASAN -->
                    <template x-if="activeItem?.status !== 'menunggu_atasan'">
                        <div class="p-4 rounded-2xl border flex items-center gap-3"
                             :class="{
                                 'bg-blue-50/80 border-blue-200 text-blue-900': activeItem?.status === 'menunggu_finance',
                                 'bg-purple-50/80 border-purple-200 text-purple-900': activeItem?.status === 'menunggu_executive',
                                 'bg-emerald-50/80 border-emerald-200 text-emerald-900': activeItem?.status === 'dicairkan' || activeItem?.status === 'selesai' || activeItem?.status === 'menunggu_bukti',
                                 'bg-rose-50/80 border-rose-200 text-rose-900': activeItem?.status === 'ditolak',
                                 'bg-slate-50 border-slate-200 text-slate-700': !['menunggu_finance','menunggu_executive','dicairkan','selesai','menunggu_bukti','ditolak'].includes(activeItem?.status)
                             }">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
                                 :class="{
                                     'bg-blue-100 text-blue-700': activeItem?.status === 'menunggu_finance',
                                     'bg-purple-100 text-purple-700': activeItem?.status === 'menunggu_executive',
                                     'bg-emerald-100 text-emerald-700': activeItem?.status === 'dicairkan' || activeItem?.status === 'selesai' || activeItem?.status === 'menunggu_bukti',
                                     'bg-rose-100 text-rose-700': activeItem?.status === 'ditolak',
                                     'bg-slate-200 text-slate-700': !['menunggu_finance','menunggu_executive','dicairkan','selesai','menunggu_bukti','ditolak'].includes(activeItem?.status)
                                 }">
                                <template x-if="activeItem?.status === 'menunggu_finance'"><span>🏦</span></template>
                                <template x-if="activeItem?.status === 'menunggu_executive'"><span>👑</span></template>
                                <template x-if="activeItem?.status === 'dicairkan' || activeItem?.status === 'selesai' || activeItem?.status === 'menunggu_bukti'"><span>✓</span></template>
                                <template x-if="activeItem?.status === 'ditolak'"><span>✕</span></template>
                                <template x-if="!['menunggu_finance','menunggu_executive','dicairkan','selesai','menunggu_bukti','ditolak'].includes(activeItem?.status)"><span>ℹ</span></template>
                            </div>
                            <div class="text-xs">
                                <span class="font-bold block"
                                      x-text="activeItem?.status === 'menunggu_finance' ? 'Status: Telah Disetujui Atasan · Menunggu Finance' : (activeItem?.status === 'menunggu_executive' ? 'Status: Menunggu Otorisasi Direksi' : (activeItem?.status === 'ditolak' ? 'Status: Ditolak' : 'Status: Telah Diproses / Selesai'))"></span>
                                <span class="text-slate-600"
                                      x-text="activeItem?.status === 'menunggu_finance' ? 'Permohonan telah Anda setujui dan saat ini diteruskan ke Bagian Finance untuk pencairan dana / pengeluaran stok.' : (activeItem?.status === 'ditolak' ? 'Pengajuan ini telah ditolak.' : 'Pengajuan telah selesai diproses oleh finance.')"></span>
                            </div>
                        </div>
                    </template>

                    <!-- JIKA SUDAH SELESAI / SUDAH ADA BUKTI FOTO REALISASI DARI KARYAWAN -->
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
                                        <a :href="'/storage/' + activeItem?.buktiRealisasiPath" target="_blank" class="text-xs text-teal-700 hover:text-teal-900 hover:underline font-bold inline-flex items-center gap-1">
                                            <span>Buka Ukuran Penuh</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>

                                    <!-- Foto Visual Inline -->
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

                                    <!-- Tautan Dokumen PDF -->
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

