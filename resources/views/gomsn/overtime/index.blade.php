<x-app-layout>
    @php
        $isAtasan = $isAtasan ?? false;
        $isFinance = $isFinance ?? false;
        $isHrd = $isHrd ?? false;
        $isSuperAdmin = $isSuperAdmin ?? false;
        $pendingLemburCount = $pendingLemburCount ?? 0;
        $selectedBulan = $selectedBulan ?? \Carbon\Carbon::now()->month;
        $selectedTahun = $selectedTahun ?? \Carbon\Carbon::now()->year;
        $selectedUserId = $selectedUserId ?? null;
        $detailEmployee = $detailEmployee ?? null;
        $totalLemburCount = $totalLemburCount ?? 0;
        $menungguCount = $menungguCount ?? 0;
        $disetujuiCount = $disetujuiCount ?? 0;
        $ditolakCount = $ditolakCount ?? 0;
        $employeeStats = $employeeStats ?? [];
        $searchKaryawan = $searchKaryawan ?? '';
        $selectedDivisi = $selectedDivisi ?? 'all';
        $divisions = $divisions ?? ['Teknik', 'NOC', 'Umum', 'Finance', 'HRD'];
        $totalNominalLembur = $totalNominalLembur ?? 0;
        $totalCountLembur = $totalCountLembur ?? 0;
        $viewMode = $viewMode ?? 'karyawan';

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    @endphp

    <div class="space-y-6" x-data="{
        payModalOpen: false,
        payData: { id: '', nomor: '', name: '', amount: 0, ref: '' },
        openPayModal(id, nomor, name, amount) {
            this.payData = { id: id, nomor: nomor, name: name, amount: amount, ref: 'PAY-LBR-' + (new Date().toISOString().slice(0,10).replace(/-/g,'')) + '-' + Math.floor(1000 + Math.random() * 9000) };
            this.payModalOpen = true;
        },
        editModalOpen: false,
        editData: { id: '', actionUrl: '', nomor_lembur: '', tanggal: '', jenis_lembur: 'Lembur', jam_mulai: '', jam_selesai: '', alasan: '' },
        openEditModal(item) {
            this.editData = {
                id: item.id,
                actionUrl: '/gomsn/overtime/' + item.id,
                nomor_lembur: item.nomor_lembur,
                tanggal: item.tanggal_raw,
                jenis_lembur: item.jenis_lembur || 'Lembur',
                jam_mulai: item.jam_mulai_raw,
                jam_selesai: item.jam_selesai_raw,
                alasan: item.alasan
            };
            this.editModalOpen = true;
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
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#overtimeWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="overtimeWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Clock Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-1.5 leading-snug">
                        @if ($isFinance)
                            <span>Rekapitulasi &amp; Pembayaran Lembur</span>
                        @elseif ($isHrd)
                            @if ($viewMode === 'saya')
                                <span>Pengajuan Lembur Saya Sendiri</span>
                            @else
                                <span>Manajemen &amp; Approval Lembur Karyawan</span>
                            @endif
                        @elseif ($isAtasan)
                            <span>Persetujuan Lembur Karyawan</span>
                        @else
                            <span>Pengajuan &amp; Jadwal Lembur Karyawan</span>
                        @endif
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        @if ($isFinance)
                            Berikut adalah ringkasan seluruh aktivitas keuangan &amp; pembayaran lembur karyawan real-time.
                        @elseif ($isHrd)
                            @if ($viewMode === 'saya')
                                Monitoring dan riwayat pengajuan lembur pribadi Anda serta status approval atasan &amp; pencairan.
                            @else
                                Monitoring rekapitulasi pengajuan lembur seluruh divisi staf dan proses verifikasi akhir HRD.
                            @endif
                        @else
                            Monitoring data jam lembur, verifikasi pekerjaan lapangan, dan transparansi perhitungan uang lembur.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1. TOP FILTER BAR (PERSIS GAMBAR 1 DENGAN PERIODE, CARI, DIVISI, EXPORT) -->
        <!-- ============================================================== -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('gomsn.overtime.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-end justify-between gap-4">
                @if ($selectedUserId)
                    <input type="hidden" name="user_id" value="{{ $selectedUserId }}">
                @endif
                @if ($viewMode)
                    <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <!-- PERIODE -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5">
                            Periode
                        </label>
                        <select name="bulan" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 font-medium text-slate-800 shadow-xs cursor-pointer">
                            @foreach ($bulanNames as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ $selectedBulan == $mNum ? 'selected' : '' }}>
                                    {{ $mName }} {{ $selectedTahun }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                    </div>

                    <!-- CARI KARYAWAN -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5">
                            Cari Karyawan
                        </label>
                        <input type="text" 
                               name="cari_karyawan" 
                               value="{{ $searchKaryawan }}" 
                               placeholder="Username..." 
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 font-medium text-slate-800 shadow-xs placeholder-slate-400" />
                    </div>

                    <!-- DIVISI -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5">
                            Divisi
                        </label>
                        <select name="divisi" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 font-medium text-slate-800 shadow-xs cursor-pointer">
                            <option value="all" {{ $selectedDivisi === 'all' ? 'selected' : '' }}>Semua Divisi</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div }}" {{ strtolower($selectedDivisi) === strtolower($div) ? 'selected' : '' }}>
                                    {{ $div }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Action Buttons: Filter & Excel Export -->
                <div class="flex items-center gap-2.5 w-full sm:w-auto sm:self-end pt-1 sm:pt-0">
                    <!-- Tombol Filter Gradient -->
                    <button type="submit" 
                            class="flex-1 sm:flex-initial py-2.5 px-5 rounded-xl text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-xs hover:opacity-95 transition cursor-pointer"
                            style="background: linear-gradient(135deg, #093c40 0%, #0d5f66 100%) !important;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Filter</span>
                    </button>

                    <!-- Tombol Export Excel Hijau (Sesuai Gambar 1) -->
                    <a href="{{ route('gomsn.overtime.index', array_merge(request()->query(), ['export' => 'excel', 'bulan' => $selectedBulan, 'tahun' => $selectedTahun])) }}" 
                       title="Export Data ke Excel/CSV"
                       class="p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center justify-center shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>

                    <!-- Tombol Buat Pengajuan Lembur (HRD Bisa Ajukan untuk Dirinya Sendiri) -->
                    @if ($isHrd || ! $isAtasan)
                    <a href="{{ route('gomsn.overtime.create') }}" 
                       class="flex-1 sm:flex-initial py-2.5 px-4 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 font-semibold text-xs flex items-center justify-center gap-1.5 shadow-xs transition cursor-pointer whitespace-nowrap">
                        <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Ajukan Lembur</span>
                    </a>
                    @endif
                </div>
            </form>

            <!-- Mode Switcher Khusus HRD (Approval Karyawan vs Lembur Saya Sendiri) -->
            @if ($isHrd)
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <div class="inline-flex items-center p-1 bg-slate-100/80 rounded-xl text-xs font-semibold">
                    <a href="{{ route('gomsn.overtime.index', ['view_mode' => 'karyawan', 'bulan' => $selectedBulan, 'tahun' => $selectedTahun]) }}" 
                       class="px-3.5 py-1.5 rounded-lg transition {{ $viewMode !== 'saya' ? 'bg-white text-teal-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Approval Lembur Karyawan (Disetujui Atasan)
                    </a>
                    <a href="{{ route('gomsn.overtime.index', ['view_mode' => 'saya', 'bulan' => $selectedBulan, 'tahun' => $selectedTahun]) }}" 
                       class="px-3.5 py-1.5 rounded-lg transition {{ $viewMode === 'saya' ? 'bg-white text-teal-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Pengajuan Lembur Saya Sendiri
                    </a>
                </div>
                <div class="text-[11px] text-slate-500">
                    Role HRD memiliki wewenang approval lembur karyawan &amp; pengajuan mandiri.
                </div>
            </div>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- 2. GRID KARTU KARYAWAN (PERSIS GAMBAR 1 - SAAT BELUM LIHAT DETAIL) -->
        <!-- ============================================================== -->
        @if (empty($selectedUserId) && ($isAtasan || $isFinance || $isHrd || $isSuperAdmin) && $viewMode !== 'saya')
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-700 tracking-wider uppercase flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                    <span>Daftar Karyawan Lembur ({{ $bulanNames[$selectedBulan] ?? '' }} {{ $selectedTahun }})</span>
                </h2>
                <span class="text-xs text-slate-500 font-medium">
                    {{ count($employeeStats) }} Karyawan Terdata
                </span>
            </div>

            <!-- CSS Rigid Guard untuk Kartu Karyawan (Mencegah Foto Pecah/Melebar) -->
            <style>
                .emp-overtime-card {
                    display: flex !important;
                    flex-direction: row !important;
                    align-items: stretch !important;
                    border-radius: 28px !important;
                    background: #ffffff !important;
                    box-shadow: 0 12px 36px rgba(0,0,0,0.06) !important;
                    border: 1px solid #f1f5f9 !important;
                    padding: 16px !important;
                    position: relative !important;
                    overflow: hidden !important;
                    gap: 18px !important;
                }
                .emp-overtime-photo-panel {
                    width: 195px !important;
                    min-width: 195px !important;
                    max-width: 195px !important;
                    height: 230px !important;
                    min-height: 230px !important;
                    max-height: 230px !important;
                    border-radius: 24px 24px 80px 24px !important;
                    flex-shrink: 0 !important;
                    position: relative !important;
                    overflow: hidden !important;
                    display: flex !important;
                    align-items: flex-end !important;
                    justify-content: center !important;
                    background: linear-gradient(145deg, #029990 0%, #038078 45%, #015c57 100%) !important;
                }
                .emp-overtime-photo-img {
                    width: 100% !important;
                    height: 230px !important;
                    max-height: 230px !important;
                    object-fit: contain !important;
                    object-position: center bottom !important;
                    display: block !important;
                    pointer-events: none !important;
                    filter: drop-shadow(0 12px 18px rgba(0,0,0,0.35)) !important;
                }
                @media (max-width: 640px) {
                    .emp-overtime-card {
                        flex-direction: column !important;
                        padding: 14px !important;
                    }
                    .emp-overtime-photo-panel {
                        width: 100% !important;
                        min-width: 100% !important;
                        max-width: 100% !important;
                        height: 210px !important;
                        min-height: 210px !important;
                        max-height: 210px !important;
                        border-radius: 22px !important;
                    }
                    .emp-overtime-photo-img {
                        height: 210px !important;
                        max-height: 210px !important;
                    }
                }
            </style>

            <!-- Grid Kartu Karyawan (Sesuai Desain Modern Referensi) -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                @forelse ($employeeStats as $stat)
                @php
                    $cardUser = $stat['user'];
                    $cardAvatar = $cardUser->avatar_url ?? null;
                    $cardInitials = $cardUser->initials ?? strtoupper(substr($cardUser->name ?? 'K', 0, 2));
                @endphp
                <div class="emp-overtime-card hover:shadow-[0_16px_44px_rgba(0,0,0,0.1)] hover:-translate-y-0.5 transition-all duration-300 group">

                    <!-- Background Soft Ambient Wave Kanan -->
                    <svg class="absolute right-0 bottom-0 h-full w-2/5 pointer-events-none opacity-25" viewBox="0 0 200 200" fill="none" preserveAspectRatio="none">
                        <circle cx="170" cy="170" r="130" fill="#2dd4bf" filter="blur(50px)"/>
                        <path d="M 40 200 C 110 130, 150 170, 200 110" stroke="#0d9488" stroke-width="1.5" stroke-opacity="0.3"/>
                        <path d="M 80 200 C 140 150, 170 180, 200 140" stroke="#0d9488" stroke-width="1.5" stroke-opacity="0.2"/>
                    </svg>

                    <!-- KIRI: Panel Foto Profil Modern dengan Kurva Organik (Terkunci Sempurna) -->
                    <div class="emp-overtime-photo-panel select-none">
                        
                        <!-- Glow & Curved Lines Abstract Dekoratif -->
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute -top-10 -left-10 w-36 h-36 rounded-full bg-teal-300/20 blur-xl"></div>
                            <svg class="w-full h-full opacity-35" viewBox="0 0 200 240" fill="none" preserveAspectRatio="none">
                                <circle cx="40" cy="190" r="110" stroke="rgba(255,255,255,0.25)" stroke-width="2"/>
                                <circle cx="40" cy="190" r="140" stroke="rgba(94,234,212,0.3)" stroke-width="1.5"/>
                                <circle cx="40" cy="190" r="80" fill="rgba(20,184,166,0.35)"/>
                            </svg>
                        </div>

                        <!-- Pill Badge Jumlah Pengajuan di Kiri Atas -->
                        <div class="absolute top-3 left-3 z-10 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold text-white shadow-xs"
                             style="background: rgba(3, 62, 57, 0.78); backdrop-filter: blur(8px); border: 1px solid rgba(94, 234, 212, 0.45);">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399]"></span>
                            <span>{{ $stat['total_count'] }} Pengajuan</span>
                        </div>

                        <!-- Foto Profil Karyawan / Fallback Inisial -->
                        @if ($cardAvatar)
                            <img src="{{ $cardAvatar }}" alt="{{ $cardUser->name }}"
                                 class="emp-overtime-photo-img transition-transform duration-500 group-hover:scale-105"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="items-center justify-center text-white font-black" style="display:none; width:90px; height:90px; margin-bottom:30px; border-radius:50%; font-size:32px; background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.4); text-shadow:0 2px 8px rgba(0,0,0,0.3);">
                                {{ $cardInitials }}
                            </div>
                        @else
                            <div class="flex items-center justify-center text-white font-black" style="width:90px; height:90px; margin-bottom:30px; border-radius:50%; font-size:32px; background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.4); text-shadow:0 2px 8px rgba(0,0,0,0.3);">
                                {{ $cardInitials }}
                            </div>
                        @endif
                    </div>

                    <!-- KANAN: Data Lengkap Karyawan Sesuai Referensi -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between h-full gap-3 sm:gap-4 relative z-10 py-1">
                        
                        <!-- Baris 1: Nama Karyawan & Periode -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-black text-slate-900 text-lg sm:text-xl uppercase tracking-tight truncate leading-tight" title="{{ $cardUser->name }}">
                                    {{ $cardUser->name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold font-mono text-teal-800 bg-[#e0f4f2] border border-teal-200">
                                        {{ $cardUser->employee_code ?: 'KR' . $cardUser->id }}
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-xs sm:text-sm font-semibold text-slate-600 truncate">
                                        {{ $cardUser->division ?: 'Teknik' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Periode Bulan & Tahun di Pojok Kanan Atas -->
                            <div class="shrink-0 flex items-center gap-2">
                                <div class="w-10 h-10 rounded-2xl bg-[#e6f7f5] flex items-center justify-center text-teal-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="8" cy="14" r="0.75" fill="currentColor"/>
                                        <circle cx="12" cy="14" r="0.75" fill="currentColor"/>
                                        <circle cx="16" cy="14" r="0.75" fill="currentColor"/>
                                        <circle cx="8" cy="17" r="0.75" fill="currentColor"/>
                                        <circle cx="12" cy="17" r="0.75" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="text-right leading-tight">
                                    <div class="text-xs sm:text-sm font-semibold text-slate-700">{{ $bulanNames[$selectedBulan] ?? 'November' }}</div>
                                    <div class="text-xs sm:text-sm font-semibold text-slate-700">{{ $selectedTahun }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 2: Total Lembur (Hari) & Lokasi (2 Kolom dengan Divider) -->
                        <div class="flex items-center gap-4 py-1">
                            <!-- Kolom Periode / Total Lembur -->
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-2xl bg-[#e6f7f5] flex items-center justify-center text-teal-700 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] font-medium text-slate-400">Periode</div>
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900 truncate">{{ $stat['total_days'] }} hari</div>
                                </div>
                            </div>

                            <!-- Vertical Divider -->
                            <div class="h-8 w-px bg-slate-200 shrink-0"></div>

                            <!-- Kolom Lokasi -->
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-2xl bg-[#e6f7f5] flex items-center justify-center text-teal-700 shrink-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] font-medium text-slate-400">Lokasi</div>
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900 truncate">{{ $cardUser->branch ?? $cardUser->location ?? 'Bandung' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Total Uang & Tombol Lihat Detail Sesuai Mockup -->
                        <div class="rounded-2xl sm:rounded-3xl p-2.5 sm:p-3 px-4 sm:px-5 flex items-center justify-between gap-3"
                             style="background-color: #eaf6f4; border: 1px solid #d1ede8;">
                            <!-- Total Uang -->
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
                                     style="background: #0d6e67; color: #ffffff;">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.5 3.75a3 3 0 00-3 3v.75h21v-.75a3 3 0 00-3-3h-15z" />
                                        <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 003 3h15a3 3 0 003-3v-7.5zm-4.875 3a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[10px] sm:text-[11px] font-semibold text-teal-800 uppercase tracking-wider">Total Uang</div>
                                    <div class="text-base sm:text-xl font-black text-teal-950 whitespace-nowrap leading-tight">
                                        Rp {{ number_format($stat['total_nominal'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Lihat Detail Sesuai Gambar -->
                            <a href="{{ route('gomsn.overtime.index', ['bulan' => $selectedBulan, 'tahun' => $selectedTahun, 'user_id' => $cardUser->id, 'view_mode' => $viewMode]) }}"
                               class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2.5 sm:py-2.8 rounded-full text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all transform active:scale-95 shrink-0"
                               style="background: #04615c;">
                                <span>Lihat Detail</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full p-10 text-center bg-white rounded-2xl border border-slate-200/80 text-slate-400 text-xs">
                    Tidak ada catatan lembur karyawan untuk filter dan periode yang dipilih.
                </div>
                @endforelse
            </div>
        </div>
        @endif

        <!-- ============================================================== -->
        <!-- 3. HALAMAN DETAIL LEMBUR (PERSIS GAMBAR 2 - SAAT KLIK LIHAT DETAIL) -->
        <!-- ============================================================== -->
        @if ($selectedUserId || (! $isAtasan && ! $isFinance && ! $isHrd && ! $isSuperAdmin) || ($isHrd && $viewMode === 'saya'))
        <div class="space-y-5">
            <!-- Header Navigasi Kembali & Keterangan Karyawan -->
            @if ($selectedUserId)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-3.5">
                    <a href="{{ route('gomsn.overtime.index', ['bulan' => $selectedBulan, 'tahun' => $selectedTahun, 'view_mode' => $viewMode]) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer shadow-xs self-start">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Daftar</span>
                    </a>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">
                                {{ $detailEmployee ? $detailEmployee->name : 'Karyawan' }}
                            </h2>
                            <span class="text-[11px] font-mono font-bold text-teal-800 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-full">
                                {{ $detailEmployee->employee_code ?? 'MSN' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Divisi: <span class="font-medium text-slate-700">{{ $detailEmployee->division ?? 'Teknik' }}</span> &bull; Periode: <span class="font-medium text-slate-700">{{ $bulanNames[$selectedBulan] ?? 'November' }} {{ $selectedTahun }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- 2 Kartu Summary Atas (Persis Gambar 2) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <!-- Card 1: TOTAL LEMBUR (Mint / Emerald) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 border-t-2 border-emerald-500">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 tracking-wider uppercase block">
                            TOTAL LEMBUR
                        </span>
                        <span class="text-xl sm:text-2xl font-extrabold text-slate-900 block mt-0.5">
                            Rp {{ number_format($totalNominalLembur, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Card 2: RINCIAN PENGAJUAN (Soft Blue) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 border-t-2 border-blue-500">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 tracking-wider uppercase block">
                            RINCIAN PENGAJUAN
                        </span>
                        <div class="mt-1">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                Lembur: {{ $totalCountLembur }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1. MOBILE VIEW: Overtime Request Cards (sm:hidden) -->
            <div class="sm:hidden space-y-3">
                @forelse ($lemburList as $idx => $item)
                    @php
                        $hours = floor($item->durasi_menit / 60);
                        $mins = $item->durasi_menit % 60;
                        $durasiStr = "{$hours} jam " . sprintf('%02d mnt', $mins);
                        $uangLembur = (int) $item->nominal;
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3">
                        <!-- Top Header: Tanggal & Uang Lembur Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/80 text-[#155E63] flex flex-col items-center justify-center font-bold text-xs shrink-0">
                                    <span class="leading-none text-sm font-extrabold">{{ \Carbon\Carbon::parse($item->tanggal)->format('d') }}</span>
                                    <span class="text-[9px] uppercase tracking-wider text-teal-600 mt-0.5">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('M') }}</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block truncate">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, j F Y') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono block">
                                        {{ $item->nomor_lembur }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-xl inline-block">
                                    Rp {{ number_format($uangLembur, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Time & Duration Pill -->
                        <div class="flex items-center justify-between bg-slate-50/90 border border-slate-100 rounded-xl px-3 py-2 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                                </span>
                            </div>
                            <span class="font-bold text-slate-700 bg-white border border-slate-200/80 px-2 py-0.5 rounded-lg text-[11px]">
                                {{ $durasiStr }}
                            </span>
                        </div>

                        <!-- Alasan Section -->
                        <div class="text-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Alasan Lembur:</span>
                            <p class="text-slate-700 bg-slate-50/60 p-2.5 rounded-xl border border-slate-100/90 text-xs leading-relaxed break-words">
                                {{ $item->alasan }}
                            </p>
                        </div>

                        <!-- Status & Action Footer -->
                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap">
                            <!-- Status Badge -->
                            <div>
                                @if ($item->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>Selesai Dibayar</span>
                                    </span>
                                @elseif ($item->status === 'approved_atasan')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        Disetujui Atasan
                                    </span>
                                @elseif ($item->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Approval
                                    </span>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1.5 ml-auto">
                                <!-- Finance Pay Button -->
                                @if ($isFinance && $item->status === 'approved_atasan')
                                    <button type="button" 
                                            @click="openPayModal({{ $item->id }}, '{{ $item->nomor_lembur }}', '{{ $item->user->name }}', {{ $uangLembur }})"
                                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                        Bayar Lembur
                                    </button>
                                @endif

                                <!-- HRD / Atasan Approval Button -->
                                @if (($isHrd || $isSuperAdmin || $isAtasan) && $item->status === 'pending' && $item->user_id !== auth()->id())
                                    <form method="POST" action="{{ route('gomsn.overtime.approve', $item->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition cursor-pointer" 
                                                onclick="return confirm('Setujui pengajuan lembur ini?')">
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('gomsn.overtime.reject', $item->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition cursor-pointer" 
                                                onclick="return confirm('Tolak pengajuan lembur ini?')">
                                            Tolak
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-2xl border border-slate-200/80 text-slate-400 text-xs">
                        Tidak ada data rincian lembur untuk karyawan ini pada periode yang dipilih.
                    </div>
                @endforelse
            </div>

            <!-- 2. DESKTOP VIEW: Tabel Rincian Lembur (hidden sm:block) -->
            <div class="hidden sm:block bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="text-slate-400 font-bold uppercase tracking-wider text-[11px] bg-slate-50/70 border-b border-slate-200/80">
                                <th class="py-3.5 px-4 text-center w-12">NO</th>
                                <th class="py-3.5 px-4">TANGGAL</th>
                                <th class="py-3.5 px-4">JENIS</th>
                                <th class="py-3.5 px-4">JAM</th>
                                <th class="py-3.5 px-4">DURASI</th>
                                <th class="py-3.5 px-4">ALASAN</th>
                                <th class="py-3.5 px-4 text-right">UANG LEMBUR</th>
                                <th class="py-3.5 px-4 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                            @forelse ($lemburList as $idx => $item)
                            @php
                                $hours = floor($item->durasi_menit / 60);
                                $mins = $item->durasi_menit % 60;
                                $durasiStr = "{$hours} jam " . sprintf('%02d menit', $mins);
                                $uangLembur = (int) $item->nominal;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- NO -->
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- TANGGAL -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-medium text-slate-800">
                                    <span>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('j F Y') }}</span>
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $item->nomor_lembur }}</span>
                                </td>

                                <!-- JENIS -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                        {{ $item->jenis_lembur ?? 'Lembur' }}
                                    </span>
                                </td>

                                <!-- JAM -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                                </td>

                                <!-- DURASI -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                    {{ $durasiStr }}
                                </td>

                                <!-- ALASAN -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="truncate text-slate-700" title="{{ $item->alasan }}">
                                        {{ $item->alasan }}
                                    </p>
                                </td>

                                <!-- UANG LEMBUR (Persis Gambar 2) -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap font-bold text-blue-600 text-xs sm:text-sm">
                                    Rp {{ number_format($uangLembur, 0, ',', '.') }}
                                </td>

                                <!-- AKSI APPROVAL / BAYAR / EDIT -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Finance Pay Button -->
                                        @if ($isFinance && $item->status === 'approved_atasan')
                                            <button type="button" 
                                                    @click="openPayModal({{ $item->id }}, '{{ $item->nomor_lembur }}', '{{ $item->user->name }}', {{ $uangLembur }})"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] shadow-xs transition cursor-pointer">
                                                Bayar
                                            </button>
                                        @endif

                                        <!-- HRD / Atasan Approval Button -->
                                        @if (($isHrd || $isSuperAdmin || $isAtasan) && $item->status === 'pending' && $item->user_id !== auth()->id())
                                            <form method="POST" action="{{ route('gomsn.overtime.approve', $item->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-[11px] shadow-xs transition cursor-pointer" 
                                                        onclick="return confirm('Setujui pengajuan lembur ini?')">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('gomsn.overtime.reject', $item->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px] transition cursor-pointer" 
                                                        onclick="return confirm('Tolak pengajuan lembur ini?')">
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif

                                        @if ($item->status === 'paid')
                                            <span class="text-[11px] text-slate-400 font-medium">Selesai</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 text-xs">
                                    Tidak ada data rincian lembur untuk karyawan ini pada periode yang dipilih.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($lemburList->hasPages())
                <div class="pt-2">
                    {{ $lemburList->links() }}
                </div>
            @endif
        </div>
        @endif

        <!-- ============================================================== -->
        <!-- MODAL KONFIRMASI PEMBAYARAN FINANCE                           -->
        <!-- ============================================================== -->
        <div x-show="payModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200"
                 @click.outside="payModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Konfirmasi Pembayaran Lembur
                    </h3>
                    <button type="button" @click="payModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form :action="'/gomsn/overtime/' + payData.id + '/pay'" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Karyawan:</span>
                            <span class="font-bold text-slate-800" x-text="payData.name"></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Nomor Lembur:</span>
                            <span class="font-mono text-slate-700" x-text="payData.nomor"></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Total Uang Lembur:</span>
                            <span class="font-bold text-blue-600" x-text="'Rp ' + Number(payData.amount).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Referensi / Bukti Transfer *</label>
                        <input type="text" name="payment_reference" x-model="payData.ref" required class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 focus:ring-emerald-500 focus:border-emerald-500 font-mono" />
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="payModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                            Konfirmasi Bayar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
