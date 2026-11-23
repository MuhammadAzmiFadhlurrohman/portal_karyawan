<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-6 pb-12">

        <!-- Top Breadcrumb & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('gomsn.pengajuan.index') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-teal-700 text-xs font-bold border border-slate-200/90 shadow-2xs transition group w-fit">
                <svg class="w-4 h-4 text-slate-400 group-hover:text-teal-600 group-hover:-translate-x-0.5 transition" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Daftar Pengajuan</span>
            </a>

            <div class="flex items-center gap-2">
                @if ($pengajuan->user_id === auth()->id() && in_array($pengajuan->status, ['draft', 'menunggu_atasan']) && ! $pengajuan->approvals()->where('action', 'approved')->exists())
                <a href="{{ route('gomsn.pengajuan.edit', $pengajuan->id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Edit Pengajuan Ini</span>
                </a>
                @endif
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TOP PAGE HEADER BANNER (Sculpted Metallic Ocean Teal Theme)    -->
        <!-- ============================================================== -->
        <div class="relative overflow-hidden rounded-3xl border border-cyan-400/30 shadow-[0_4px_24px_rgba(2,43,58,0.35)] p-5 sm:p-7 text-white select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#detailPengajuanWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="detailPengajuanWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Left Section: Icon + Nomor + Badges + Meta Subtitle -->
                <div class="flex items-start gap-4 min-w-0">
                    <!-- Glowing Icon Badge -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                        @if ($pengajuan->jenis === 'STOK')
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @elseif ($pengajuan->jenis === 'BELI')
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        @elseif ($pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG')
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <!-- Top Tag -->
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="text-[10px] sm:text-xs uppercase font-extrabold tracking-wider text-teal-200/90 font-mono">
                                DETAIL PENGAJUAN
                            </span>
                            <span class="text-white/40">&bull;</span>
                            <span class="text-[11px] text-teal-100/80 font-mono">
                                {{ $pengajuan->created_at->format('d M Y') }} &bull; {{ $pengajuan->created_at->format('H:i') }} WIB
                            </span>
                        </div>

                        <!-- Main Nomor Pengajuan Heading -->
                        <h1 class="text-2xl sm:text-3xl font-heading font-black text-white tracking-tight flex items-center gap-2 leading-tight">
                            <span>{{ $pengajuan->nomor_pengajuan }}</span>
                        </h1>

                        <!-- Badges Row -->
                        <div class="flex flex-wrap items-center gap-2 mt-2.5">
                            <!-- Status Badge -->
                            @php
                                $statusStyle = match($pengajuan->status) {
                                    'draft' => 'bg-white/10 text-slate-200 border-white/20',
                                    'menunggu_atasan' => 'bg-amber-400/20 text-amber-200 border-amber-400/40',
                                    'menunggu_finance' => 'bg-sky-400/20 text-sky-200 border-sky-400/40',
                                    'menunggu_executive' => 'bg-purple-400/20 text-purple-200 border-purple-400/40',
                                    'dicairkan' => 'bg-cyan-400/20 text-cyan-200 border-cyan-400/40',
                                    'menunggu_bukti' => 'bg-orange-400/20 text-orange-200 border-orange-400/40',
                                    'selesai' => 'bg-emerald-400/25 text-emerald-200 border-emerald-400/40',
                                    'ditolak' => 'bg-rose-400/25 text-rose-200 border-rose-400/40',
                                    default => 'bg-white/10 text-slate-200 border-white/20'
                                };
                                $statusDot = match($pengajuan->status) {
                                    'menunggu_atasan', 'menunggu_finance', 'menunggu_executive', 'menunggu_bukti' => 'bg-amber-400 animate-pulse',
                                    'selesai', 'dicairkan' => 'bg-emerald-400',
                                    'ditolak' => 'bg-rose-400',
                                    default => 'bg-slate-400'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border {{ $statusStyle }} backdrop-blur-xs shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                <span>{{ strtoupper(str_replace('_', ' ', $pengajuan->status)) }}</span>
                            </span>

                            <!-- Jenis Permohonan Badge -->
                            @if ($pengajuan->jenis === 'STOK')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-2xs">
                                    <span>📦</span>
                                    <span>Stok Gudang</span>
                                </span>
                            @elseif ($pengajuan->jenis === 'BELI')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white shadow-2xs">
                                    <span>🛒</span>
                                    <span>Pengadaan (Beli)</span>
                                </span>
                            @elseif ($pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-cyan-600 text-white shadow-2xs">
                                    <span>📄</span>
                                    <span>Permintaan Barang</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-2xs">
                                    <span>💵</span>
                                    <span>Kasbon Dana</span>
                                </span>
                            @endif

                            @if ($pengajuan->metode_pembayaran)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-white/10 text-teal-100 border border-white/15">
                                    Metode: {{ $pengajuan->metode_pembayaran }}
                                </span>
                            @endif
                        </div>

                        <!-- Subtitle Meta Information -->
                        <div class="text-xs text-teal-100/85 mt-3 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span>
                                Diajukan oleh <strong class="text-white font-bold">{{ $pengajuan->user?->name ?? 'Karyawan MSN' }}</strong> 
                                <span class="text-teal-200/80">({{ $pengajuan->user?->employee_code ?: 'KR'.$pengajuan->user_id }} &bull; {{ $pengajuan->user?->division ?: '-' }})</span>
                            </span>
                            @if ($pengajuan->atasan)
                                <span class="text-teal-200/50 hidden sm:inline">&bull;</span>
                                <span>
                                    Ditujukan ke: <strong class="text-cyan-200 font-bold">{{ $pengajuan->atasan->name }}</strong>
                                    <span class="text-teal-200/80">({{ $pengajuan->atasan->employee_code ?: 'KR'.$pengajuan->atasan_id }})</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Section: Total Nominal Metric Tile -->
                <div class="lg:text-right shrink-0">
                    <div class="p-4 sm:p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-inner flex flex-col justify-center min-w-[220px]">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-teal-200/90 block">
                            {{ ($pengajuan->jenis === 'STOK' || $pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG') ? 'Jenis Permintaan' : 'Total Estimasi Nominal' }}
                        </span>
                        <div class="mt-1">
                            @if ($pengajuan->jenis === 'STOK')
                                <span class="text-xl sm:text-2xl font-heading font-black text-amber-300">Stok Gudang</span>
                                <span class="block text-[11px] text-teal-100/70 mt-0.5">Dikelola Bagian Finance</span>
                            @elseif ($pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG')
                                <span class="text-xl sm:text-2xl font-heading font-black text-cyan-200">Permintaan Barang</span>
                                <span class="block text-[11px] text-teal-100/70 mt-0.5">Unit Pengadaan Fisik</span>
                            @else
                                <span class="text-2xl sm:text-3xl font-mono font-black text-white tracking-tight drop-shadow-xs">
                                    Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}
                                </span>
                                <span class="block text-[11px] text-teal-100/70 mt-0.5 font-medium">
                                    {{ in_array($pengajuan->status, ['dicairkan', 'selesai']) ? 'Telah Dicairkan oleh Finance' : 'Estimasi Pengeluaran' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Success Notification -->
        @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                ✓
            </div>
            <div class="flex-1">
                {{ session('success') }}
            </div>
        </div>
        @endif

        <!-- Error / Overdue Alerts -->
        @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm shrink-0">
                ✕
            </div>
            <div class="flex-1">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if ($pengajuan->status === 'dicairkan' && $pengajuan->realisasi_deadline && \Carbon\Carbon::now()->greaterThan($pengajuan->realisasi_deadline))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs sm:text-sm flex items-center gap-3 shadow-2xs">
            <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong>Melewati Batas Waktu Realisasi (7 Hari):</strong> Batas waktu unggah bukti nota belanja adalah <strong>{{ $pengajuan->realisasi_deadline->format('d M Y') }}</strong>. Harap segera unggah nota pengeluaran realisasi.
            </div>
        </div>
        @endif

        <!-- ============================================================== -->
        <!-- MAIN CONTENT TWO-COLUMN GRID                                   -->
        <!-- ============================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT 2 COLUMNS: Detail Permohonan, Tabel Item, & Realisasi -->
            <div class="lg:col-span-2 space-y-6">

                <!-- CARD 1: Ringkasan Permohonan -->
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#155E63] shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-sm sm:text-base text-slate-800">
                                    Ringkasan Permohonan
                                </h3>
                                <p class="text-[11px] text-slate-400">Data pemohon, alur peruntukan, dan catatan operasional</p>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Bento Grid Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Box 1: Pemohon -->
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100/90 space-y-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Pemohon / Karyawan</span>
                            <div class="flex items-center gap-3">
                                @if ($pengajuan->user?->avatar_url)
                                    <img src="{{ $pengajuan->user->avatar_url }}" alt="" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#155E63] to-slate-800 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs" style="{{ $pengajuan->user?->avatar_url ? 'display:none;' : '' }}">
                                    {{ $pengajuan->user?->initials ?? strtoupper(substr($pengajuan->user?->name ?? 'K', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 block truncate text-xs">{{ $pengajuan->user?->name ?? 'Karyawan MSN' }}</span>
                                    <span class="text-[11px] text-slate-400 block font-mono mt-0.5">{{ $pengajuan->user?->employee_code ?: 'KR'.$pengajuan->user_id }} &bull; {{ $pengajuan->user?->division ?: '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Box 2: Atasan Langsung -->
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100/90 space-y-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Atasan yang Dituju</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-[#155E63] font-bold text-sm flex items-center justify-center shrink-0">
                                    👤
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 block truncate text-xs">{{ $pengajuan->atasan?->name ?? 'Atasan Langsung' }}</span>
                                    <span class="text-[11px] text-slate-400 block font-mono mt-0.5">{{ $pengajuan->atasan?->employee_code ?: 'Atasan Divisi' }} &bull; {{ $pengajuan->atasan?->position ?: 'Supervisor' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Box 3: Jenis Permohonan -->
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100/90 space-y-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Kategori Pengajuan</span>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5 pt-0.5">
                                @if ($pengajuan->jenis === 'STOK')
                                    <span>📦 Pengambilan Stok Gudang</span>
                                @elseif ($pengajuan->jenis === 'BELI')
                                    <span>🛒 Pengadaan / Pembelian Barang</span>
                                @elseif ($pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG')
                                    <span>📄 Permintaan Barang Operasional</span>
                                @else
                                    <span>💵 Kasbon Dana Operasional</span>
                                @endif
                            </div>
                        </div>

                        <!-- Box 4: Pengelola / PIC Pencairan -->
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100/90 space-y-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Pengelola / PIC</span>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5 pt-0.5">
                                <span>🏦</span>
                                <span>{{ $pengajuan->jenis === 'STOK' ? 'Finance (Pengatur Stok Gudang)' : ($pengajuan->metode_pembayaran ? 'Finance (' . $pengajuan->metode_pembayaran . ')' : 'Bagian Finance') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Khusus Rekening Transfer (Jika ada) -->
                    @if ($pengajuan->jenis !== 'STOK' && $pengajuan->metode_pembayaran === 'TRANSFER' && $pengajuan->account_number)
                    <div class="p-4 rounded-2xl bg-sky-50/70 border border-sky-200/80 text-sky-950 text-xs space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-sky-900">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h.01M18 12h.01"/>
                            </svg>
                            <span>Informasi Rekening Tujuan Pencairan</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 font-mono">
                            <div><span class="text-sky-700 font-sans block text-[10px] uppercase font-bold">Bank:</span> {{ $pengajuan->bank_name ?: 'BCA' }}</div>
                            <div><span class="text-sky-700 font-sans block text-[10px] uppercase font-bold">No. Rekening:</span> {{ $pengajuan->account_number }}</div>
                            <div><span class="text-sky-700 font-sans block text-[10px] uppercase font-bold">Atas Nama:</span> {{ $pengajuan->account_holder }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Info Khusus Stok Gudang -->
                    @if ($pengajuan->jenis === 'STOK')
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-emerald-950 text-xs space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-emerald-900">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Mekanisme Pengambilan Barang Stok Gudang</span>
                        </div>
                        <p class="text-emerald-800 leading-relaxed text-[11px]">
                            Permohonan ini menggunakan inventaris fisik gudang internal perusahaan. Tidak ada transfer uang tunai; pengeluaran fisik barang disetujui &amp; dicatat langsung oleh bagian Finance di sistem inventory.
                        </p>
                    </div>
                    @endif

                    <!-- Keperluan / Keterangan -->
                    <div class="space-y-1.5 pt-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">
                            Keperluan / Keterangan Permohonan:
                        </span>
                        <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/70 text-slate-800 text-xs sm:text-[13px] leading-relaxed whitespace-pre-line font-medium shadow-2xs">
                            {{ $pengajuan->keperluan ?: '-' }}
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Rincian Item Permohonan -->
                @php
                    $displayItems = $pengajuan->details->isNotEmpty() 
                        ? $pengajuan->details 
                        : collect($pengajuan->getModalDetails())->map(function($d) {
                            return (object)[
                                'item_name' => $d['name'],
                                'material' => null,
                                'qty' => $d['qty'],
                                'satuan' => $d['satuan'],
                                'estimated_unit_price' => 0,
                                'subtotal' => 0,
                            ];
                        });
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#155E63] shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-sm sm:text-base text-slate-800">
                                    Rincian Item Permohonan
                                </h3>
                                <p class="text-[11px] text-slate-400">Daftar barang, material, atau kebutuhan operasional</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-teal-50 text-teal-800 border border-teal-200 shadow-2xs">
                            {{ $displayItems->count() }} Item
                        </span>
                    </div>

                    <!-- DESKTOP VIEW: Clean Structured Table (hidden on small screens) -->
                    <div class="hidden sm:block overflow-hidden rounded-2xl border border-slate-200/90 shadow-2xs">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-3 px-4">Item / Uraian</th>
                                    <th class="py-3 px-4 text-center">Qty Diminta</th>
                                    @if ($pengajuan->jenis === 'STOK')
                                        <th class="py-3 px-4 text-right">Stok Fisik Tersedia</th>
                                    @else
                                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                                        <th class="py-3 px-4 text-right">Subtotal</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($displayItems as $item)
                                <tr class="hover:bg-teal-50/20 transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                                        <span>{{ $item->item_name }}</span>
                                        @if ($item->material)
                                            <span class="text-[10px] text-slate-400 block font-mono mt-0.5">Kode: {{ $item->material->kode }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700 font-mono">
                                            {{ $item->qty }} {{ $item->satuan }}
                                        </span>
                                    </td>
                                    @if ($pengajuan->jenis === 'STOK')
                                    <td class="py-3.5 px-4 text-right font-medium">
                                        @if ($item->material)
                                            @php
                                                $isSufficient = $item->material->stok_tersedia >= $item->qty;
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isSufficient ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                <span>{{ $isSufficient ? '✓' : '⚠' }}</span>
                                                <span>{{ $item->material->stok_tersedia }} {{ $item->material->satuan }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">Material manual</span>
                                        @endif
                                    </td>
                                    @else
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                        Rp {{ number_format((float) $item->estimated_unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 italic">Tidak ada item rincian.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if ($pengajuan->jenis !== 'STOK')
                            <tfoot>
                                <tr class="bg-emerald-50/80 border-t-2 border-emerald-300 font-extrabold text-xs text-slate-900">
                                    <td colspan="3" class="py-3 px-4 text-right uppercase tracking-wider text-slate-700">
                                        TOTAL ESTIMASI:
                                    </td>
                                    <td class="py-3 px-4 text-right text-emerald-800 text-sm font-mono font-black">
                                        Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <!-- MOBILE VIEW: Stacked Responsive Item Cards (shown on small screens) -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($displayItems as $item)
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 block text-xs leading-tight">{{ $item->item_name }}</span>
                                    @if ($item->material)
                                        <span class="text-[10px] text-slate-400 font-mono block mt-0.5">Kode: {{ $item->material->kode }}</span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-white text-slate-700 border border-slate-200 shrink-0 font-mono">
                                    {{ $item->qty }} {{ $item->satuan }}
                                </span>
                            </div>

                            @if ($pengajuan->jenis === 'STOK')
                                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 text-[11px]">Stok Tersedia:</span>
                                    @if ($item->material)
                                        <span class="font-bold text-emerald-700">{{ $item->material->stok_tersedia }} {{ $item->material->satuan }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Manual</span>
                                    @endif
                                </div>
                            @else
                                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs font-mono">
                                    <span class="text-slate-500 font-sans text-[11px]">Rp {{ number_format((float) $item->estimated_unit_price, 0, ',', '.') }} &times; {{ $item->qty }}</span>
                                    <span class="font-bold text-slate-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                        @endforeach

                        @if ($pengajuan->jenis !== 'STOK')
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Total Estimasi</span>
                            <span class="text-base font-mono font-black text-emerald-800">
                                Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- CARD 3: Laporan Realisasi & Bukti Nota (Jika Ada) -->
                @if ($pengajuan->realization_amount !== null || $pengajuan->realization_proof_path)
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-sm sm:text-base text-slate-800">
                                    Laporan Realisasi &amp; Bukti Nota
                                </h3>
                                <p class="text-[11px] text-slate-400">Pertanggungjawaban pemakaian dana oleh pemohon</p>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">
                            Diserahkan: {{ $pengajuan->realized_at?->format('d/m/Y H:i') }} WIB
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Total Realisasi Pengeluaran:</span>
                            <span class="text-base sm:text-lg font-mono font-black text-emerald-700 block">
                                Rp {{ number_format((float) $pengajuan->realization_amount, 0, ',', '.') }}
                            </span>
                        </div>

                        @php
                            $diff = (float)$pengajuan->total_nominal - (float)$pengajuan->realization_amount;
                        @endphp
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Selisih Pengembalian / Kurang:</span>
                            <span class="text-base sm:text-lg font-mono font-black {{ $diff >= 0 ? 'text-teal-700' : 'text-rose-700' }} block">
                                Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                <span class="text-xs font-sans font-semibold">({{ $diff >= 0 ? 'Lebih / Sisa Kasbon' : 'Kurang Dana' }})</span>
                            </span>
                        </div>

                        @if ($pengajuan->realization_notes)
                        <div class="col-span-1 sm:col-span-2 p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Catatan Realisasi:</span>
                            <p class="text-slate-700 text-xs leading-relaxed">{{ $pengajuan->realization_notes }}</p>
                        </div>
                        @endif

                        @if ($pengajuan->realization_proof_path)
                        <div class="col-span-1 sm:col-span-2 pt-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2">Lampiran Bukti Nota / Foto Kwitansi:</span>
                            @if (!str_ends_with(strtolower($pengajuan->realization_proof_path), '.pdf'))
                            <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 p-2 max-w-sm">
                                <a href="{{ $pengajuan->bukti_realisasi_url }}" target="_blank" class="block group relative overflow-hidden rounded-xl">
                                    <img src="{{ $pengajuan->bukti_realisasi_url }}" alt="Bukti Nota" class="max-h-64 w-auto object-contain mx-auto group-hover:scale-105 transition duration-200">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-xs font-bold gap-1.5 backdrop-blur-xs">
                                        <span>Buka Ukuran Penuh ↗</span>
                                    </div>
                                </a>
                            </div>
                            @else
                            <a href="{{ $pengajuan->bukti_realisasi_url }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span>Unduh / Buka Dokumen Bukti (PDF)</span>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- RIGHT 1 COLUMN: Tindakan Alur Kerja & Riwayat Approval -->
            <div class="space-y-6">

                <!-- CARD 4: Tindakan Alur Kerja (Workflow Action Card) -->
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-[#0e6774] font-bold text-xs">
                                ⚡
                            </div>
                            <h3 class="font-heading font-extrabold text-sm sm:text-base text-slate-800">
                                Tindakan Alur Kerja
                            </h3>
                        </div>
                    </div>

                    <!-- 0. Draft Submission Action -->
                    @if ($pengajuan->status === 'draft' && auth()->id() === $pengajuan->user_id)
                    <div class="space-y-3">
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Formulir pengajuan Anda masih tersimpan sebagai draft. Klik tombol di bawah untuk meneruskan verifikasi ke atasan langsung.
                        </p>
                        <form method="POST" action="{{ route('gomsn.pengajuan.submit', $pengajuan->id) }}">
                            @csrf
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important;"
                                    class="w-full text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs hover:opacity-90 transition cursor-pointer">
                                <span>Ajukan ke Atasan Langsung &rarr;</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    <!-- 1. Atasan Approval Form -->
                    @if ($pengajuan->status === 'menunggu_atasan')
                        @php
                            $isTargetAtasan = auth()->user()->hasRole(['super_admin', 'admin']) 
                                || ! $pengajuan->atasan_id 
                                || $pengajuan->atasan_id === auth()->id();
                        @endphp
                        @if (auth()->id() !== $pengajuan->user_id && (auth()->user()->can('gomsn.pengajuan.approve_atasan') || auth()->user()->hasRole(['super_admin', 'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'hrd', 'ceo'])) && $isTargetAtasan)
                        <div class="space-y-4">
                            <div class="p-3.5 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl text-emerald-900 text-xs space-y-1">
                                <strong class="block font-bold">Verifikasi Urgensi Kebutuhan (Atasan):</strong>
                                <p class="text-emerald-800 text-[11px] leading-relaxed">
                                    Setujui permohonan operasional tim sebelum diteruskan ke bagian Finance untuk pencairan kas atau serah terima barang.
                                </p>
                            </div>

                            <!-- Form Setujui -->
                            <form method="POST" action="{{ route('gomsn.pengajuan.approve-atasan', $pengajuan->id) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Persetujuan (Opsional):</label>
                                    <input type="text" name="notes" placeholder="Contoh: Kebutuhan operasional disetujui..." class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 bg-white" />
                                </div>
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>✓ Setujui &amp; Teruskan ke Finance</span>
                                </button>
                            </form>

                            <!-- Form Tolak -->
                            <form method="POST" action="{{ route('gomsn.pengajuan.reject', $pengajuan->id) }}" class="space-y-2 pt-2 border-t border-slate-100">
                                @csrf
                                <input type="hidden" name="stage" value="atasan">
                                <div>
                                    <label class="block text-[11px] font-bold text-rose-800 mb-1">Alasan Penolakan: <span class="text-rose-500">*</span></label>
                                    <input type="text" name="reason" required placeholder="Tulis alasan jika menolak..." class="w-full text-xs rounded-xl border border-rose-300 py-2 px-3 focus:ring-2 focus:ring-rose-200 focus:border-rose-500 bg-white" />
                                </div>
                                <button type="submit" class="w-full py-2.5 px-4 text-xs font-bold text-rose-700 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <span>✕</span>
                                    <span>Tolak Pengajuan Ini</span>
                                </button>
                            </form>
                        </div>
                        @elseif ($pengajuan->atasan_id && $pengajuan->atasan_id !== auth()->id())
                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs space-y-1">
                            <strong class="font-bold block">Persetujuan Terbatas:</strong>
                            <p class="text-amber-800 leading-relaxed text-[11px]">
                                Pengajuan ini ditujukan secara spesifik kepada atasan: <strong>{{ $pengajuan->atasan?->name ?? 'Atasan lain' }}</strong>.
                            </p>
                        </div>
                        @else
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-600 text-xs flex items-center gap-2">
                            <span>⏳</span>
                            <span>Menunggu persetujuan atasan langsung. Pemohon tidak dapat menyetujui pengajuannya sendiri.</span>
                        </div>
                        @endif
                    @endif

                    <!-- 2. Executive Approval (> Rp 5jt) -->
                    @if ($pengajuan->status === 'menunggu_executive')
                        @if (auth()->id() !== $pengajuan->user_id && auth()->user()->hasRole(['super_admin', 'ceo']))
                        <div class="space-y-3">
                            <div class="p-3.5 rounded-2xl bg-purple-50 border border-purple-200 text-purple-950 text-xs space-y-1">
                                <strong class="block font-bold text-purple-900">👑 Otorisasi Direktur/CEO (> Rp 5.000.000)</strong>
                                <p class="text-purple-800 text-[11px] leading-relaxed">
                                    Pengajuan bernilai di atas Rp 5 Juta memerlukan otorisasi eksekutif sebelum pencairan dana oleh bagian Finance.
                                </p>
                            </div>
                            <form method="POST" action="{{ route('gomsn.pengajuan.approve-executive', $pengajuan->id) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Persetujuan Eksekutif:</label>
                                    <input type="text" name="notes" placeholder="Catatan approval Direktur..." class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 bg-white" />
                                </div>
                                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs transition cursor-pointer">
                                    <span>✓ Otorisasi &amp; Teruskan ke Finance</span>
                                </button>
                            </form>
                        </div>
                        @else
                        <div class="p-3.5 bg-purple-50/70 border border-purple-200 rounded-2xl text-purple-900 text-xs flex items-center gap-2">
                            <span>👑</span>
                            <span>Menunggu otorisasi Direktur/CEO karena nominal melebihi batas regulasi Rp 5.000.000.</span>
                        </div>
                        @endif
                    @endif

                    <!-- 3. Finance Disbursement / Stock Release -->
                    @if ($pengajuan->status === 'menunggu_finance')
                        @if (auth()->id() !== $pengajuan->user_id && auth()->user()->hasRole(['super_admin', 'finance']))
                        <div class="space-y-4">
                            @if ($pengajuan->jenis === 'STOK')
                                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-950 text-xs space-y-1">
                                    <strong class="block font-bold text-emerald-900">📦 Otorisasi Stok Gudang (Finance)</strong>
                                    <p class="text-emerald-800 text-[11px] leading-relaxed">
                                        Periksa ketersediaan fisik barang di gudang. Mengonfirmasi tombol di bawah akan otomatis <strong>memotong kuantitas stok di sistem</strong> dan menandai pengajuan <strong>SELESAI</strong>.
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('gomsn.pengajuan.disburse', $pengajuan->id) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Penyerahan Barang (Opsional):</label>
                                        <input type="text" name="notes" placeholder="Contoh: Barang telah diserahkan lengkap di gudang..." class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 bg-white" />
                                    </div>
                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>✓ Terima &amp; Serahkan Stok Gudang</span>
                                    </button>
                                </form>
                            @else
                                <div class="p-3.5 bg-sky-50 border border-sky-200 rounded-2xl text-sky-950 text-xs space-y-1">
                                    <strong class="block font-bold text-sky-900">🏦 Otorisasi Pencairan Kas (Finance)</strong>
                                    <p class="text-sky-800 text-[11px] leading-relaxed">
                                        Verifikasi saldo dan cairkan dana {{ $pengajuan->jenis === 'BELI' ? 'pembelian barang' : 'kasbon operasional' }} ke pemohon.
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('gomsn.pengajuan.disburse', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-3" x-data="{ method: 'Transfer' }">
                                    @csrf
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Metode Pencairan: <span class="text-rose-500">*</span></label>
                                        <select name="metode_pencairan" x-model="method" class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 bg-white font-medium text-slate-800 focus:ring-teal-700 focus:border-teal-700">
                                            <option value="Transfer">Transfer Bank</option>
                                            <option value="Cash">Cash (Tunai)</option>
                                        </select>
                                    </div>
                                    <template x-if="method === 'Transfer'">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Unggah Bukti Transfer <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                            <input type="file" name="bukti_transfer" accept="image/*,application/pdf" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-300 rounded-xl bg-white p-1" />
                                        </div>
                                    </template>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Pencairan (Opsional):</label>
                                        <input type="text" name="notes" placeholder="No. referensi transfer / catatan kas..." class="w-full text-xs rounded-xl border border-slate-300 py-2.5 px-3 bg-white" />
                                    </div>
                                    <button type="submit" 
                                            style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important;"
                                            class="w-full hover:opacity-90 text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="method === 'Cash' ? '✓ Konfirmasi Pencairan Cash' : '✓ Konfirmasi Pencairan Transfer'"></span>
                                    </button>
                                </form>
                            @endif

                            <!-- Tolak Finance Form -->
                            <form method="POST" action="{{ route('gomsn.pengajuan.reject', $pengajuan->id) }}" class="space-y-2 pt-2 border-t border-slate-100">
                                @csrf
                                <input type="hidden" name="stage" value="finance">
                                <div>
                                    <label class="block text-[11px] font-bold text-rose-800 mb-1">Alasan Penolakan Finance: <span class="text-rose-500">*</span></label>
                                    <input type="text" name="reason" required placeholder="Tulis alasan penolakan..." class="w-full text-xs rounded-xl border border-rose-300 py-2 px-3 bg-white" />
                                </div>
                                <button type="submit" class="w-full py-2.5 px-4 text-xs font-bold text-rose-700 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <span>✕</span>
                                    <span>Tolak Pengajuan</span>
                                </button>
                            </form>
                        </div>
                        @else
                        <div class="p-3.5 bg-sky-50/70 border border-sky-200 rounded-2xl text-sky-900 text-xs flex items-center gap-2">
                            <span>🏦</span>
                            <span>{{ $pengajuan->jenis === 'STOK' ? 'Menunggu verifikasi dan penyerahan stok oleh bagian Finance.' : 'Menunggu verifikasi dan pencairan kas oleh bagian Finance.' }}</span>
                        </div>
                        @endif
                    @endif

                    <!-- 4. Realization Submission (By Requester) -->
                    @if (in_array($pengajuan->status, ['dicairkan', 'selesai']) && auth()->id() === $pengajuan->user_id && ! $pengajuan->bukti_realisasi_path)
                    <div class="space-y-3">
                        <div class="p-3.5 bg-cyan-50 border border-cyan-200 rounded-2xl text-cyan-950 text-xs space-y-1">
                            <strong class="block font-bold text-cyan-900">📸 Unggah Bukti Nota Belanja</strong>
                            <p class="text-cyan-800 text-[11px] leading-relaxed">
                                Dana telah dicairkan oleh Finance. Anda dapat mengunggah bukti nota atau foto kwitansi belanja untuk pertanggungjawaban operasional.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('gomsn.pengajuan.realize', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="nominal_realisasi" value="{{ (float) $pengajuan->total_nominal }}">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan Realisasi (Opsional):</label>
                                <textarea name="realization_notes" rows="2" placeholder="Keterangan pengeluaran..." class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white"></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Foto / PDF Nota Kwitansi: <span class="text-rose-500">*</span></label>
                                <input type="file" name="realization_proof" required accept="image/*,application/pdf" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 border border-slate-300 rounded-xl bg-white p-1" />
                            </div>
                            <button type="submit" 
                                    style="background: linear-gradient(135deg, #0e7490 0%, #155E63 100%) !important;"
                                    class="w-full text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs hover:opacity-90 transition cursor-pointer">
                                <span>Simpan Bukti Nota Belanja</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    @if ($pengajuan->status === 'selesai')
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">✓</span>
                        <span>Pengajuan telah selesai diproses dan seluruh pertanggungjawaban terverifikasi.</span>
                    </div>
                    @endif

                    @if ($pengajuan->status === 'ditolak')
                    <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs space-y-1">
                        <strong class="font-bold flex items-center gap-1.5 text-rose-900">
                            <span>✕</span>
                            <span>Pengajuan Ditolak</span>
                        </strong>
                        <p class="text-rose-700 text-[11px] leading-relaxed">
                            Alasan: "{{ $pengajuan->approvals->where('action', 'rejected')->last()?->notes ?? $pengajuan->approvals->where('status', 'rejected')->last()?->notes ?? 'Tidak disetujui.' }}"
                        </p>
                    </div>
                    @endif
                </div>

                <!-- CARD 5: Riwayat Alur Persetujuan (Interactive Stepper Timeline) -->
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#155E63] font-bold text-xs">
                                🕒
                            </div>
                            <h3 class="font-heading font-extrabold text-sm sm:text-base text-slate-800">
                                Riwayat Persetujuan
                            </h3>
                        </div>
                    </div>

                    <!-- Vertical Timeline -->
                    <div class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        <!-- Step 1: Inisiasi Pengajuan -->
                        <div class="relative group">
                            <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-teal-500 border-2 border-white flex items-center justify-center text-[10px] text-white font-bold shadow-xs">
                                1
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Pengajuan Dibuat</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $pengajuan->created_at->format('d M Y - H:i') }} WIB</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Oleh: {{ $pengajuan->user?->name }}</span>
                            </div>
                        </div>

                        <!-- Step 2+: Log Approval -->
                        @forelse ($pengajuan->approvals as $idx => $appr)
                            @php
                                $isAppr = ($appr->action === 'approved' || $appr->status === 'approved');
                                $stageName = match($appr->stage) {
                                    'atasan' => 'Persetujuan Atasan Langsung',
                                    'finance' => 'Pencairan / Penyerahan Finance',
                                    'executive' => 'Otorisasi Direktur / Eksekutif',
                                    default => ucfirst($appr->stage)
                                };
                            @endphp
                            <div class="relative group">
                                <div class="absolute -left-6 top-0.5 w-5 h-5 rounded-full {{ $isAppr ? 'bg-emerald-500' : 'bg-rose-500' }} border-2 border-white flex items-center justify-center text-[10px] text-white font-bold shadow-xs">
                                    {{ $isAppr ? '✓' : '✕' }}
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/60 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800 text-xs">{{ $stageName }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $appr->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="font-semibold text-slate-700">{{ $appr->approver?->name ?? 'Approver' }}</span>
                                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full {{ $isAppr ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $isAppr ? 'Disetujui' : 'Ditolak' }}
                                        </span>
                                    </div>
                                    @if ($appr->notes)
                                        <p class="text-[11px] text-slate-600 italic bg-white p-2 rounded-xl border border-slate-100 mt-1">
                                            "{{ $appr->notes }}"
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-[11px] text-slate-400 italic pt-1">
                                Belum ada riwayat persetujuan tercatat.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
