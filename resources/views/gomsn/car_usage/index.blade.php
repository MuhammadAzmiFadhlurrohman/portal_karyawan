<x-app-layout>
    @php
        $monthsList = [
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
    @endphp

    <div x-data="{
        showModal: false,
        activeItem: null,
        previewUrl: null,
        previewTitle: '',
        openModalWith(item) {
            this.activeItem = item;
            this.previewUrl = null;
            this.previewTitle = '';
            this.showModal = true;
        }
    }" class="w-full space-y-5 pb-12">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: var(--gomsn-header-bg) !important;">
            
            <!-- Gloss Highlights -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex items-center gap-3.5 min-w-0 flex-1">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3.1C2.1 11.4 2 11.7 2 12.1V16c0 .6.4 1 1 1h2"/>
                        <circle cx="7" cy="17" r="2"/>
                        <path d="M9 17h6"/>
                        <circle cx="17" cy="17" r="2"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Pengajuan &amp; Pemantauan Mobil
                        </h1>
                    </div>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug">
                        Memantau riwayat karyawan yang menggunakan mobil operasional via pengajuan dana bensin yang telah disetujui Atasan dan dicairkan Finance.
                    </p>
                </div>
            </div>

            <!-- Export Button -->
            <div class="relative z-10 flex items-center gap-2 shrink-0">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" 
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/25 text-white text-xs font-semibold backdrop-blur-xs transition shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- 2 KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <!-- Total Penggunaan Mobil -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 sm:p-4 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3.1C2.1 11.4 2 11.7 2 12.1V16c0 .6.4 1 1 1h2"/>
                        <circle cx="7" cy="17" r="2"/>
                        <path d="M9 17h6"/>
                        <circle cx="17" cy="17" r="2"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-medium text-slate-500 block truncate">Total Penggunaan</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight font-mono">{{ number_format($totalUsages) }}</span>
                    <span class="text-[10px] text-teal-600 block mt-0.5">Kali Pemakaian</span>
                </div>
            </div>

            <!-- Karyawan / Driver Terlibat -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 sm:p-4 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-medium text-slate-500 block truncate">Karyawan / Driver</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-800 leading-tight font-mono">{{ $uniqueDriversCount }}</span>
                    <span class="text-[10px] text-blue-600 block mt-0.5">Pengaju Mobil</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3 sm:p-4 shadow-2xs">
            <form method="GET" action="{{ route('gomsn.car-usage.index') }}" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <input type="text" 
                           name="q" 
                           value="{{ $searchTerm }}" 
                           placeholder="Cari karyawan, NIK, nama mobil (Sigra, Luxio...), tujuan..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/60 focus:bg-white focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] transition outline-none text-slate-800 placeholder:text-slate-400 font-medium">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Divisi -->
                    <select name="division" class="px-2.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/60 font-semibold text-slate-700 outline-none focus:bg-white focus:border-[#155E63] transition">
                        <option value="all">Semua Divisi</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $divisionFilter === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>

                    <!-- Periode Mode -->
                    <select name="period_mode" class="px-2.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/60 font-semibold text-slate-700 outline-none focus:bg-white focus:border-[#155E63] transition" onchange="this.form.submit()">
                        <option value="month" {{ $periodMode === 'month' ? 'selected' : '' }}>Per Bulan</option>
                        <option value="all" {{ $periodMode === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                    </select>

                    @if ($periodMode === 'month')
                        <!-- Bulan -->
                        <select name="month" class="px-2.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/60 font-semibold text-slate-700 outline-none focus:bg-white focus:border-[#155E63] transition">
                            @foreach ($monthsList as $num => $mName)
                                <option value="{{ $num }}" {{ (int) $selectedMonth === (int) $num ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>

                        <!-- Tahun -->
                        <select name="year" class="px-2.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/60 font-semibold text-slate-700 outline-none focus:bg-white focus:border-[#155E63] transition">
                            @foreach (range(date('Y'), date('Y') - 3) as $y)
                                <option value="{{ $y }}" {{ (int) $selectedYear === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    @endif

                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#155E63] hover:bg-[#0f464a] text-white text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Filter</span>
                    </button>

                    @if ($searchTerm || $divisionFilter !== 'all' || $periodMode !== 'month')
                        <a href="{{ route('gomsn.car-usage.index') }}" class="px-2.5 py-2 rounded-xl text-slate-500 hover:text-slate-800 text-xs font-semibold hover:bg-slate-100 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card List -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    <span class="text-xs font-bold text-slate-800">Daftar Penggunaan Mobil Operasional</span>
                    <span class="text-[10px] text-slate-400 font-mono">({{ $carUsages->total() }} data)</span>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Diurutkan dari pengajuan terbaru</span>
            </div>

            @if ($carUsages->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                            <tr>
                                <th class="py-3 px-3.5 sm:px-4">Tanggal &amp; No.</th>
                                <th class="py-3 px-3.5 sm:px-4">Karyawan / Pemohon</th>
                                <th class="py-3 px-3.5 sm:px-4">Mobil &amp; Perihal Pengajuan</th>
                                <th class="py-3 px-3.5 sm:px-4">Personel / Tim</th>
                                <th class="py-3 px-3.5 sm:px-4 text-right">Dana Bensin</th>
                                <th class="py-3 px-3.5 sm:px-4">Verifikasi Atasan &amp; Finance</th>
                                <th class="py-3 px-3.5 sm:px-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($carUsages as $item)
                                @php
                                    $personelList = $item->getPersonelList();
                                    $atasanAppr = $item->approvals->first(fn ($a) => $a->stage === 'atasan' && $a->action === 'approved');
                                    $financeAppr = $item->approvals->first(fn ($a) => $a->stage === 'finance' && $a->action === 'approved');
                                    
                                    // Serialize for modal view
                                    $itemJson = [
                                        'id' => $item->id,
                                        'nomor' => $item->nomor_pengajuan,
                                        'judul' => $item->judul,
                                        'keperluan' => $item->keperluan,
                                        'nominal' => 'Rp ' . number_format((float) $item->nominal_total, 0, ',', '.'),
                                        'status' => strtoupper(str_replace('_', ' ', $item->status)),
                                        'created_at' => $item->created_at?->format('d M Y, H:i') ?? '-',
                                        'transfer_at' => $item->transfer_at?->format('d M Y, H:i') ?? '-',
                                        'userName' => $item->user?->name ?? 'Karyawan',
                                        'userCode' => $item->user?->employee_code ?? '-',
                                        'division' => $item->user?->division ?? '-',
                                        'atasanName' => $atasanAppr?->approver?->name ?? ($item->atasan?->name ?? 'Atasan Terkait'),
                                        'atasanDate' => $atasanAppr?->created_at?->format('d M Y, H:i') ?? '-',
                                        'financeName' => $financeAppr?->approver?->name ?? ($item->transferUser?->name ?? 'Finance'),
                                        'financeDate' => $item->transfer_at?->format('d M Y, H:i') ?? ($financeAppr?->created_at?->format('d M Y, H:i') ?? '-'),
                                        'personel' => $personelList,
                                        'buktiTransferUrl' => $item->bukti_transfer_url,
                                        'buktiRealisasiUrl' => $item->bukti_realisasi_url,
                                        'details' => $item->getModalDetails(),
                                    ];
                                @endphp
                                <tr class="hover:bg-teal-50/20 transition-colors">
                                    <!-- Tanggal & No Pengajuan -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top">
                                        <span class="font-mono font-bold text-slate-800 block text-[11px]">{{ $item->nomor_pengajuan }}</span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">
                                            {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 mt-1 border border-slate-200">
                                            {{ $item->created_at ? $item->created_at->format('H:i') : '' }} WIB
                                        </span>
                                    </td>

                                    <!-- Karyawan / Pemohon -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-teal-100 text-[#155E63] font-bold flex items-center justify-center shrink-0 text-xs">
                                                {{ $item->user?->initials ?? 'K' }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-bold text-slate-800 block truncate">{{ $item->user?->name ?? 'Karyawan' }}</span>
                                                <span class="text-[10px] text-slate-400 block font-mono mt-0.5">{{ $item->user?->employee_code ?? '-' }}</span>
                                                <span class="inline-block mt-0.5 text-[9px] font-semibold text-teal-700 bg-teal-50 border border-teal-200/80 px-1.5 py-0.2 rounded">
                                                    {{ $item->user?->division ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Mobil & Perihal Pengajuan -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top max-w-xs">
                                        <span class="font-bold text-slate-800 block line-clamp-2 leading-snug">{{ $item->judul }}</span>
                                        @if ($item->keperluan && $item->keperluan !== $item->judul)
                                            <span class="text-[11px] text-slate-500 block line-clamp-1 mt-0.5">
                                                Rute/Tujuan: {{ $item->keperluan }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Personel / Tim -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top">
                                        @if (! empty($personelList))
                                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                                @foreach ($personelList as $p)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                        {{ $p }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-[11px] italic">Pengemudi sendiri</span>
                                        @endif
                                    </td>

                                    <!-- Dana Bensin -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top text-right font-mono font-bold text-emerald-700 whitespace-nowrap">
                                        Rp {{ number_format((float) $item->nominal_total, 0, ',', '.') }}
                                    </td>

                                    <!-- Persetujuan Atasan & Finance -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top text-[10px] space-y-1">
                                        <!-- Atasan -->
                                        <div class="flex items-center gap-1.5 text-emerald-700">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="truncate">Atasan: <strong>{{ $atasanAppr?->approver?->name ?? ($item->atasan?->name ?? 'Disetujui') }}</strong></span>
                                        </div>

                                        <!-- Finance -->
                                        <div class="flex items-center gap-1.5 text-teal-700">
                                            <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="truncate">Finance: <strong>{{ $financeAppr?->approver?->name ?? ($item->transferUser?->name ?? 'Dicairkan') }}</strong></span>
                                        </div>
                                    </td>

                                    <!-- Tombol Detail -->
                                    <td class="py-3 px-3.5 sm:px-4 align-top text-center whitespace-nowrap">
                                        <button type="button" 
                                                @click="openModalWith(@js($itemJson))"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-teal-200 bg-teal-50 hover:bg-teal-100 text-[#155E63] text-xs font-bold transition cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($carUsages->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $carUsages->links() }}
                    </div>
                @endif
            @else
                <div class="py-12 px-4 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3.1C2.1 11.4 2 11.7 2 12.1V16c0 .6.4 1 1 1h2"/>
                            <circle cx="7" cy="17" r="2"/>
                            <path d="M9 17h6"/>
                            <circle cx="17" cy="17" r="2"/>
                        </svg>
                    </div>
                    <h3 class="text-xs font-bold text-slate-700">Tidak ada data penggunaan mobil</h3>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                        Belum ada pengajuan dana bensin/mobil yang memenuhi kriteria telah disetujui Atasan dan Finance untuk filter yang dipilih.
                    </p>
                </div>
            @endif
        </div>

        <!-- Read-Only Detail Modal (Alpine.js) -->
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="showModal = false"></div>

            <!-- Modal Dialog Content -->
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-10 my-8"
                 @click.stop>
                
                <!-- Modal Header -->
                <div class="p-4 sm:px-6 sm:py-4 bg-gradient-to-r from-[#022836] to-[#0a525f] text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-cyan-300">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4zM3 13h1m16 0h1M5 13l1.5-5A2 2 0 018.4 6.5h7.2a2 2 0 011.9 1.5L19 13m-16 0a2 2 0 00-2 2v2a2 2 0 002 2h1.1a3 3 0 015.8 0h4.2a3 3 0 015.8 0H21a2 2 0 002-2v-2a2 2 0 00-2-2m-18 0h18"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-white" x-text="activeItem ? activeItem.nomor : 'Detail Pengajuan Mobil'"></h3>
                            <span class="text-[10px] text-teal-200 font-medium">Pemantauan Penggunaan Kendaraan Operasional</span>
                        </div>
                    </div>
                    <button type="button" 
                            @click="showModal = false"
                            class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto" x-show="activeItem">
                    <!-- Profile & Status Header -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-teal-100 text-[#155E63] font-bold flex items-center justify-center text-sm"
                                 x-text="activeItem ? activeItem.userName.charAt(0) : 'K'">
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs sm:text-sm" x-text="activeItem?.userName"></h4>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                                    <span x-text="'NIK: ' + (activeItem?.userCode || '-')"></span>
                                    <span>•</span>
                                    <span class="font-semibold text-teal-700" x-text="'Divisi ' + (activeItem?.division || '-')"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block">Total Dana Bensin</span>
                            <span class="font-mono font-bold text-base text-emerald-700 block" x-text="activeItem?.nominal"></span>
                        </div>
                    </div>

                    <!-- Perihal & Rute Keperluan -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Perihal / Judul Pengajuan</label>
                        <p class="text-xs font-semibold text-slate-800 p-2.5 rounded-xl bg-slate-50 border border-slate-200" x-text="activeItem?.judul"></p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Rute / Keperluan Operasional Mobil</label>
                        <p class="text-xs text-slate-700 p-2.5 rounded-xl bg-slate-50 border border-slate-200" x-text="activeItem?.keperluan || '-'"></p>
                    </div>

                    <!-- Personel / Tim -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Personel / Rekan Kerja di Mobil</label>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <template x-if="activeItem?.personel && activeItem.personel.length > 0">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="p in activeItem.personel" :key="p">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200" x-text="p"></span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!activeItem?.personel || activeItem.personel.length === 0">
                                <span class="text-xs text-slate-400 italic">Pengemudi sendiri (tanpa personel tambahan terdaftar)</span>
                            </template>
                        </div>
                    </div>

                    <!-- Riwayat Approval Atasan & Finance -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <!-- Atasan -->
                        <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50">
                            <div class="flex items-center gap-1.5 text-emerald-800 font-bold text-xs mb-1">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Persetujuan Atasan</span>
                            </div>
                            <p class="text-xs text-slate-800 font-medium" x-text="activeItem?.atasanName"></p>
                            <span class="text-[10px] text-slate-400 mt-0.5 block" x-text="activeItem?.atasanDate"></span>
                        </div>

                        <!-- Finance -->
                        <div class="p-3 rounded-xl border border-teal-200 bg-teal-50/50">
                            <div class="flex items-center gap-1.5 text-teal-800 font-bold text-xs mb-1">
                                <svg class="w-4 h-4 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Pencairan Dana (Finance)</span>
                            </div>
                            <p class="text-xs text-slate-800 font-medium" x-text="activeItem?.financeName"></p>
                            <span class="text-[10px] text-slate-400 mt-0.5 block" x-text="activeItem?.financeDate"></span>
                        </div>
                    </div>

                    <!-- Bukti Transfer / Realisasi Jika Ada -->
                    <div class="pt-2 flex flex-wrap gap-2">
                        <template x-if="activeItem?.buktiTransferUrl">
                            <a :href="activeItem.buktiTransferUrl" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-teal-300 bg-teal-50 hover:bg-teal-100 text-teal-800 text-xs font-bold transition">
                                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>Bukti Transfer Finance</span>
                            </a>
                        </template>

                        <template x-if="activeItem?.buktiRealisasiUrl">
                            <a :href="activeItem.buktiRealisasiUrl" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Struk / Nota BBM Realisasi</span>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 sm:px-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Mode: Read-only HRD Monitoring</span>
                    <button type="button" 
                            @click="showModal = false"
                            class="px-4 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
