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
        $canFilterDivisi = $canFilterDivisi ?? ($isSuperAdmin || $isHrd || $isFinance);
        $canExport = $canExport ?? ($isSuperAdmin || $isHrd || $isFinance || $isAtasan);
        $totalNominalLembur = $totalNominalLembur ?? 0;
        $totalCountLembur = $totalCountLembur ?? 0;
        $viewMode = $viewMode ?? 'karyawan';
        $isFilterUnprocessed = $isFilterUnprocessed ?? false;
        $pendingPerMonth = $pendingPerMonth ?? collect();
        $unprocessedPreviousCount = $unprocessedPreviousCount ?? 0;
        $unprocessedTotalCount = $unprocessedTotalCount ?? 0;
        $previousMonthsWithPending = $previousMonthsWithPending ?? [];

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
        },
        showToast(msg, type = 'success') {
            if (!msg) return;
            if (window.Toast) {
                window.Toast.fire({
                    icon: type,
                    title: msg
                });
            } else if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: type,
                    title: msg
                });
            }
        }
    }"
    @overtime-toast.window="showToast($event.detail.message, $event.detail.type || 'success')">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="gomsn-themed-banner relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: var(--gomsn-header-bg) !important;">
            
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
                        @elseif ($isHrd && $viewMode === 'saya')
                            <span>Lembur Saya</span>
                        @elseif ($isHrd)
                            <span>Rekapitulasi Lembur Karyawan</span>
                        @elseif ($isAtasan)
                            <span>Persetujuan Lembur Karyawan</span>
                        @else
                            <span>Pengajuan &amp; Jadwal Lembur Karyawan</span>
                        @endif
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        @if ($isFinance)
                            Berikut adalah ringkasan seluruh aktivitas keuangan &amp; pembayaran lembur karyawan real-time.
                        @elseif ($isHrd && $viewMode === 'saya')
                            Kelola riwayat pengajuan lembur pribadi, pantau persetujuan atasan, dan transparansi uang lembur.
                        @elseif ($isHrd)
                            Monitoring rekapitulasi pengajuan lembur seluruh divisi staf yang telah disetujui atasan.
                        @else
                            Monitoring data jam lembur, verifikasi pekerjaan lapangan, dan transparansi perhitungan uang lembur.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Right: Action Button (Hanya ada di Lembur Saya atau Karyawan Mandiri) --}}
            @if ((! $isAtasan && ! $isFinance && ! $isSuperAdmin && ! $isHrd) || ($viewMode === 'saya'))
            <div class="relative z-10 flex items-center gap-2 shrink-0">
                <a href="{{ route('gomsn.overtime.create') }}" 
                   class="w-full sm:w-auto py-2.5 px-4 rounded-xl text-white font-bold text-xs inline-flex items-center justify-center gap-1.5 shadow-md shadow-teal-950/20 hover:opacity-95 active:scale-95 transition cursor-pointer whitespace-nowrap border border-white/20"
                   style="background: var(--gomsn-active-bg) !important;">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Ajukan Lembur</span>
                </a>
            </div>
            @endif
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

                @php
                    $isViewingSelf = (! $isAtasan && ! $isFinance && ! $isHrd && ! $isSuperAdmin) || ($isHrd && $viewMode === 'saya');
                    $showEmployeeSearch = empty($selectedUserId) && ! $isViewingSelf;

                    if ($showEmployeeSearch) {
                        $gridColsClass = $canFilterDivisi ? 'sm:grid-cols-3' : 'sm:grid-cols-2 max-w-2xl';
                    } else {
                        $gridColsClass = ($canFilterDivisi && ! $isViewingSelf) ? 'sm:grid-cols-2 max-w-xl' : 'sm:grid-cols-1 max-w-xs';
                    }
                @endphp
                <div class="grid grid-cols-1 {{ $gridColsClass }} gap-4 flex-1">
                    <!-- PERIODE -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5 flex items-center justify-between">
                            <span>Periode</span>
                            @if ($unprocessedPreviousCount > 0 && ! $isHrd)
                                <span class="text-[10px] text-amber-600 font-bold lowercase tracking-normal flex items-center gap-1 animate-pulse" title="Ada pengajuan tertunda di bulan sebelumnya">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ $unprocessedPreviousCount }} blm diproses
                                </span>
                            @endif
                        </label>
                        <select name="bulan" onchange="this.form.submit()" class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 font-medium text-slate-800 shadow-xs cursor-pointer">
                            @if (! $isHrd && $unprocessedTotalCount > 0)
                            <option value="unprocessed" {{ $isFilterUnprocessed ? 'selected' : '' }}>
                                🚨 Semua Belum Diproses ({{ $unprocessedTotalCount }} Pengajuan)
                            </option>
                            @endif
                            <optgroup label="Bulan di Tahun {{ $selectedTahun }}">
                                @foreach ($bulanNames as $mNum => $mName)
                                    @php
                                        $key = $selectedTahun . '-' . $mNum;
                                        $pCount = $pendingPerMonth[$key] ?? ($pendingPerMonth instanceof \Illuminate\Support\Collection ? $pendingPerMonth->get($key, 0) : 0);
                                    @endphp
                                    <option value="{{ $mNum }}" {{ (! $isFilterUnprocessed && $selectedBulan == $mNum) ? 'selected' : '' }}>
                                        {{ $mName }} {{ $selectedTahun }} @if ($pCount > 0 && ! $isHrd) (⚠️ {{ $pCount }} belum diproses) @endif
                                    </option>
                                @endforeach
                            </optgroup>
                            @if (! empty($previousMonthsWithPending) && ! $isHrd)
                                @php
                                    $outsideYearMonths = array_filter($previousMonthsWithPending, fn($pm) => $pm['year'] != $selectedTahun);
                                @endphp
                                @if (! empty($outsideYearMonths))
                                    <optgroup label="Bulan Belum Diproses (Tahun Lain)">
                                        @foreach ($outsideYearMonths as $pm)
                                            <option value="{{ $pm['month'] }}" data-tahun="{{ $pm['year'] }}" {{ (! $isFilterUnprocessed && $selectedBulan == $pm['month'] && $selectedTahun == $pm['year']) ? 'selected' : '' }}>
                                                ⚠️ {{ $pm['name'] }} ({{ $pm['count'] }} belum diproses)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endif
                        </select>
                        <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                    </div>

                    @if ($showEmployeeSearch)
                    <!-- CARI KARYAWAN (Hanya pada Mode Daftar Seluruh Karyawan) -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 tracking-wider uppercase mb-1.5">
                            Cari Karyawan
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="cari_karyawan" 
                                   value="{{ $searchKaryawan }}" 
                                   placeholder="Nama atau kode karyawan..." 
                                   class="w-full text-xs rounded-xl border-slate-300 py-2.5 pl-9 pr-3.5 bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 font-medium text-slate-800 shadow-xs placeholder-slate-400" />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if ($canFilterDivisi && ! $isViewingSelf)
                    <!-- DIVISI (Khusus SuperAdmin, HRD, dan Finance saat mode daftar karyawan) -->
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
                    @endif
                </div>

                <!-- Action Buttons: Filter & Excel Export -->
                <div class="flex items-center gap-2 w-full sm:w-auto sm:self-end pt-1 sm:pt-0">
                    <!-- Tombol Filter Gradient -->
                    <button type="submit" 
                            class="flex-1 sm:flex-initial py-2.5 px-4 sm:px-5 rounded-xl text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-xs hover:opacity-95 transition cursor-pointer"
                            style="background: var(--gomsn-active-bg) !important;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Filter</span>
                    </button>

                    @if ($canExport)
                    <!-- Tombol Export Excel Hijau (Khusus Atasan / HRD / Finance / Admin) -->
                    <a href="{{ route('gomsn.overtime.index', array_merge(request()->query(), ['export' => 'excel', 'bulan' => $selectedBulan, 'tahun' => $selectedTahun])) }}" 
                       title="Export Data ke Excel/CSV"
                       class="p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center justify-center shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </form>


            <!-- Notifikasi & Shortcut jika ada Lembur Bulan Sebelumnya yang Belum Diproses (Khusus Atasan / Admin / Finance yang perlu memproses) -->
            @if ($unprocessedPreviousCount > 0 && ! $isFilterUnprocessed && ! $isHrd)
            <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-amber-50/90 border border-amber-200/90 rounded-xl p-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                        ⚠️
                    </span>
                    <div class="min-w-0">
                        <span class="font-bold text-amber-950 block">
                            Ada {{ $unprocessedPreviousCount }} pengajuan lembur dari bulan sebelumnya yang belum diproses!
                        </span>
                        <span class="text-[11px] text-amber-800">
                            Periode tertunda: 
                            @foreach ($previousMonthsWithPending as $pm)
                                <a href="{{ route('gomsn.overtime.index', array_filter(['bulan' => $pm['month'], 'tahun' => $pm['year'], 'divisi' => $canFilterDivisi ? $selectedDivisi : null, 'view_mode' => $viewMode])) }}" class="underline hover:text-amber-950 font-bold">
                                    {{ $pm['name'] }} ({{ $pm['count'] }})
                                </a>@if(!$loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                </div>
                <a href="{{ route('gomsn.overtime.index', array_filter(['bulan' => 'unprocessed', 'divisi' => $canFilterDivisi ? $selectedDivisi : null, 'view_mode' => $viewMode])) }}" 
                   class="px-3.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-2xs transition flex items-center gap-1.5 shrink-0 whitespace-nowrap self-start sm:self-auto cursor-pointer">
                    <span>Lihat Semua yang Belum Diproses ({{ $unprocessedTotalCount }})</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
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
                    <span>Daftar Karyawan Lembur ({{ $isFilterUnprocessed ? 'Semua Belum Diproses' : ($bulanNames[$selectedBulan] ?? '') . ' ' . $selectedTahun }})</span>
                </h2>
                <span class="text-xs text-slate-500 font-medium">
                    {{ count($employeeStats) }} Karyawan Terdata
                </span>
            </div>

            <!-- Card Alert Summary di Atas Grid Jika Ada Lembur yang Perlu Disetujui (Atasan / Finance / Admin) -->
            @if ($menungguCount > 0 && ! $isHrd)
            <div class="py-2.5 px-3.5 sm:px-4 rounded-2xl border border-amber-300/80 bg-gradient-to-r from-amber-50 via-orange-50/40 to-amber-50 flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                    <span class="text-xs font-bold text-amber-950 truncate">
                        Terdapat {{ $menungguCount }} pengajuan lembur yang perlu disetujui
                    </span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shrink-0">
                    Perlu Tindakan
                </span>
            </div>
            @endif

            <!-- CSS Rigid Guard untuk Kartu Karyawan (3 Kolom Sempurna & Rapi) -->
            <style>
                .emp-overtime-card {
                    display: flex !important;
                    flex-direction: row !important;
                    align-items: stretch !important;
                    border-radius: 22px !important;
                    background: #ffffff !important;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.05) !important;
                    border: 1px solid #f1f5f9 !important;
                    padding: 12px !important;
                    position: relative !important;
                    overflow: hidden !important;
                    gap: 12px !important;
                }
                html.dark .emp-overtime-card {
                    background: #131c2e !important;
                    border-color: rgba(51, 65, 85, 0.85) !important;
                    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4) !important;
                }
                .emp-overtime-photo-panel {
                    width: 108px !important;
                    min-width: 108px !important;
                    max-width: 108px !important;
                    height: 165px !important;
                    min-height: 165px !important;
                    max-height: 165px !important;
                    border-radius: 18px 18px 45px 18px !important;
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
                    height: 165px !important;
                    max-height: 165px !important;
                    object-fit: contain !important;
                    object-position: center bottom !important;
                    display: block !important;
                    pointer-events: none !important;
                    filter: drop-shadow(0 8px 14px rgba(0,0,0,0.32)) !important;
                }
                @media (max-width: 640px) {
                    .emp-overtime-card {
                        flex-direction: row !important;
                        padding: 10px !important;
                        gap: 10px !important;
                        border-radius: 20px !important;
                    }
                    .emp-overtime-photo-panel {
                        width: 100px !important;
                        min-width: 100px !important;
                        max-width: 100px !important;
                        height: 155px !important;
                        min-height: 155px !important;
                        max-height: 155px !important;
                        border-radius: 16px 16px 38px 16px !important;
                    }
                    .emp-overtime-photo-img {
                        height: 155px !important;
                        max-height: 155px !important;
                    }
                }
                /* iOS Style Toggle Switch (Compact & Pas Proporsinya) */
                .ios-switch-track {
                    position: relative !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    width: 30px !important;
                    min-width: 30px !important;
                    max-width: 30px !important;
                    height: 18px !important;
                    min-height: 18px !important;
                    max-height: 18px !important;
                    border-radius: 9999px !important;
                    padding: 2px !important;
                    box-sizing: border-box !important;
                    flex-shrink: 0 !important;
                    cursor: pointer !important;
                    background-color: #cbd5e1 !important;
                    transition: background-color 0.25s ease-in-out !important;
                }
                .ios-switch-track.is-paid {
                    background-color: #22c55e !important;
                }
                .ios-switch-knob {
                    display: block !important;
                    width: 14px !important;
                    min-width: 14px !important;
                    max-width: 14px !important;
                    height: 14px !important;
                    min-height: 14px !important;
                    max-height: 14px !important;
                    border-radius: 9999px !important;
                    background-color: #ffffff !important;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25) !important;
                    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    pointer-events: none !important;
                    transform: translateX(0px) !important;
                }
                .ios-switch-track.is-paid .ios-switch-knob {
                    transform: translateX(12px) !important;
                }
            </style>

            <!-- Grid Kartu Karyawan (3 Kolom Sempurna) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @forelse ($employeeStats as $stat)
                @php
                    $cardUser = $stat['user'];
                    $cardAvatar = $cardUser->avatar_url ?? null;
                    $cardInitials = $cardUser->initials ?? strtoupper(substr($cardUser->name ?? 'K', 0, 2));
                @endphp
                <div class="emp-overtime-card {{ $stat['pending_count'] > 0 ? 'border-amber-300 ring-2 ring-amber-400/25 shadow-[0_8px_24px_rgba(245,158,11,0.12)]' : '' }} hover:shadow-[0_14px_32px_rgba(0,0,0,0.08)] hover:-translate-y-0.5 transition-all duration-300 group">

                    <!-- Background Soft Ambient Wave Kanan -->
                    <svg class="absolute right-0 bottom-0 h-full w-2/5 pointer-events-none opacity-20" viewBox="0 0 200 200" fill="none" preserveAspectRatio="none">
                        <circle cx="170" cy="170" r="130" fill="#2dd4bf" filter="blur(50px)"/>
                        <path d="M 40 200 C 110 130, 150 170, 200 110" stroke="#0d9488" stroke-width="1.5" stroke-opacity="0.25"/>
                    </svg>

                    <!-- KIRI: Panel Foto Profil Modern dengan Kurva Organik -->
                    <div class="emp-overtime-photo-panel select-none">
                        
                        <!-- Glow & Curved Lines Abstract Dekoratif -->
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute -top-6 -left-6 w-24 h-24 rounded-full bg-teal-300/20 blur-xl"></div>
                            <svg class="w-full h-full opacity-35" viewBox="0 0 200 240" fill="none" preserveAspectRatio="none">
                                <circle cx="40" cy="190" r="110" stroke="rgba(255,255,255,0.25)" stroke-width="2"/>
                                <circle cx="40" cy="190" r="140" stroke="rgba(94,234,212,0.3)" stroke-width="1.5"/>
                                <circle cx="40" cy="190" r="80" fill="rgba(20,184,166,0.35)"/>
                            </svg>
                        </div>

                        <!-- Pill Badges di Kiri Atas Panel Foto -->
                        <div class="absolute top-2 left-2 z-10 flex flex-col gap-1 items-start">
                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold text-white shadow-xs"
                                 style="background: rgba(3, 62, 57, 0.85); backdrop-filter: blur(8px); border: 1px solid rgba(94, 234, 212, 0.45);">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_#34d399]"></span>
                                <span>{{ $stat['total_count'] }} Pengajuan</span>
                            </div>
                        </div>

                        <!-- Foto Profil Karyawan / Fallback Icon Profile User -->
                        @if ($cardAvatar)
                            <img src="{{ $cardAvatar }}" alt="{{ $cardUser->name }}"
                                 class="emp-overtime-photo-img transition-transform duration-500 group-hover:scale-105"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="flex-col items-center justify-center text-white" style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">
                                <div class="flex items-center justify-center text-white shadow-md" style="width:52px; height:52px; border-radius:50%; background:rgba(255,255,255,0.18); border:2px solid rgba(255,255,255,0.45); margin-bottom:14px;">
                                    <svg class="w-7 h-7 text-white drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-white w-full h-full" style="align-items:center; justify-content:center;">
                                <div class="flex items-center justify-center text-white shadow-md" style="width:52px; height:52px; border-radius:50%; background:rgba(255,255,255,0.18); border:2px solid rgba(255,255,255,0.45); margin-bottom:14px;">
                                    <svg class="w-7 h-7 text-white drop-shadow-xs" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- KANAN: Data Lengkap Karyawan (Sangat Rapi, Lega & Tidak Ada Teks Terpotong) -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between h-full gap-2 relative z-10 py-0.5">
                        
                        <!-- Header: Periode, Nama Karyawan & Badge Divisi -->
                        <div class="min-w-0">
                            <!-- Periode Kalender di Atas Nama -->
                            <div class="flex items-center justify-between gap-1 text-[10px] font-semibold text-teal-800">
                                <span class="inline-flex items-center gap-1 text-teal-700 shrink-0">
                                    <svg class="w-3 h-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="16" y1="2" x2="16" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="8" y1="2" x2="8" y2="6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="3" y1="10" x2="21" y2="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ $isFilterUnprocessed ? 'Semua Periode' : ($bulanNames[$selectedBulan] ?? 'November') . ' ' . $selectedTahun }}
                                </span>
                                @if ($isHrd)
                                    <!-- Tombol Toggle Status Pembayaran Lembur Karyawan: Belum / Dibayar (Khusus Role HRD, Instan Tanpa Refresh) -->
                                    <div x-data="{
                                        isPaid: {{ $stat['is_paid'] ? 'true' : 'false' }},
                                        loading: false,
                                        async toggleStatus() {
                                            if (this.loading) return;
                                            
                                            // 1. Optimistic UI Update: Langsung ubah state seketika (0 ms) tanpa menunggu network
                                            const previousState = this.isPaid;
                                            this.isPaid = !this.isPaid;
                                            const nextStatus = this.isPaid ? 'paid' : 'unpaid';
                                            this.loading = true;

                                            try {
                                                const res = await fetch('{{ route('gomsn.overtime.toggle-payment') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json',
                                                        'X-Requested-With': 'XMLHttpRequest'
                                                    },
                                                    body: JSON.stringify({
                                                        user_id: {{ $cardUser->id }},
                                                        bulan: '{{ $selectedBulan }}',
                                                        tahun: '{{ $selectedTahun }}',
                                                        target_status: nextStatus
                                                    })
                                                });
                                                const data = await res.json();
                                                if (data && data.success) {
                                                    this.isPaid = data.is_paid;
                                                    window.dispatchEvent(new CustomEvent('overtime-toast', {
                                                        detail: { message: data.message, type: 'success' }
                                                    }));
                                                } else {
                                                    // Rollback jika response server gagal
                                                    this.isPaid = previousState;
                                                    window.dispatchEvent(new CustomEvent('overtime-toast', {
                                                        detail: { message: data?.message || 'Gagal mengubah status', type: 'error' }
                                                    }));
                                                }
                                            } catch (err) {
                                                console.error(err);
                                                // Rollback jika terjadi network error
                                                this.isPaid = previousState;
                                                window.dispatchEvent(new CustomEvent('overtime-toast', {
                                                    detail: { message: 'Terjadi kesalahan jaringan.', type: 'error' }
                                                }));
                                            } finally {
                                                this.loading = false;
                                            }
                                        }
                                    }" class="inline-flex items-center m-0">
                                        <button type="button" 
                                                @click="toggleStatus()"
                                                :title="isPaid ? 'Status: Dibayar. Klik untuk ubah ke Belum.' : 'Status: Belum. Klik untuk ubah ke Dibayar.'"
                                                class="group inline-flex items-center gap-1.5 py-0.5 px-0.5 rounded-full cursor-pointer select-none transition hover:opacity-90">
                                            
                                            <!-- iOS Style Toggle Switch (Compact & Pas Proporsinya) -->
                                            <span class="ios-switch-track"
                                                  :class="{ 'is-paid': isPaid }">
                                                <span class="ios-switch-knob"></span>
                                            </span>

                                            <!-- Label Status: Belum vs Dibayar -->
                                            <span class="text-[11px] font-bold leading-none tracking-tight select-none"
                                                  :class="isPaid ? 'text-emerald-600 font-extrabold' : 'text-slate-500'"
                                                  x-text="isPaid ? 'Dibayar' : 'Belum'">
                                                {{ $stat['is_paid'] ? 'Dibayar' : 'Belum' }}
                                            </span>
                                        </button>
                                    </div>
                                @elseif ($stat['pending_count'] > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black text-amber-900 bg-amber-100 border border-amber-300 shadow-2xs shrink-0 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $stat['pending_count'] }} Menunggu
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Selesai
                                    </span>
                                @endif
                            </div>

                            <!-- Nama Karyawan (Lebar 100% Penuh, Tidak Terpotong) -->
                            <h3 class="font-black text-slate-900 text-sm uppercase tracking-tight truncate leading-snug mt-0.5" title="{{ $cardUser->name }}">
                                {{ $cardUser->name }}
                            </h3>

                            <!-- Kode Karyawan & Divisi (Sejajar Rapi) -->
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-bold font-mono text-teal-800 bg-[#e0f4f2] border border-teal-200">
                                    {{ $cardUser->employee_code ?: 'KR' . $cardUser->id }}
                                </span>
                                <span class="text-slate-300 text-[10px]">•</span>
                                <span class="text-[10.5px] font-semibold text-slate-500 truncate">
                                    {{ $cardUser->division ?: 'Teknik' }}
                                </span>
                            </div>
                        </div>

                        <!-- Baris 2: Strip Info Periode/Lembur (Hari) & Lokasi (Tidak Pernah Terpotong) -->
                        <div class="grid grid-cols-2 gap-1.5 py-1.5 px-2 rounded-xl bg-slate-50/90 border border-slate-100">
                            <!-- Kolom Total Lembur -->
                            <div class="min-w-0">
                                <div class="text-[9px] font-medium text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Lembur</span>
                                </div>
                                <div class="text-[11.5px] font-black text-slate-800 truncate mt-0.5">{{ $stat['total_days'] }} hari</div>
                            </div>

                            <!-- Kolom Lokasi -->
                            <div class="min-w-0 pl-1.5 border-l border-slate-200">
                                <div class="text-[9px] font-medium text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Lokasi</span>
                                </div>
                                <div class="text-[11.5px] font-black text-slate-800 truncate mt-0.5">{{ $cardUser->branch ?? $cardUser->location ?? 'Bandung' }}</div>
                            </div>
                        </div>

                        <!-- Baris 3: Total Uang & Tombol Detail (Lega, Rapi, Bebas Tabrakan) -->
                        <div class="rounded-xl p-2 px-2.5 flex items-center justify-between gap-1.5"
                             style="background-color: #eaf6f4; border: 1px solid #d1ede8;">
                            <!-- Total Uang -->
                            <div class="min-w-0 flex-1">
                                <div class="text-[8px] font-bold text-teal-800 uppercase tracking-wider leading-none">TOTAL UANG</div>
                                <div class="text-[12.5px] font-black text-teal-950 truncate leading-tight mt-0.5">
                                    Rp {{ number_format($stat['total_nominal'], 0, ',', '.') }}
                                </div>
                            </div>

                            <!-- Tombol Detail / Setujui -->
                            @if ($stat['pending_count'] > 0 && ! $isHrd)
                            <a href="{{ route('gomsn.overtime.index', ['bulan' => $isFilterUnprocessed ? 'unprocessed' : $selectedBulan, 'tahun' => $selectedTahun, 'user_id' => $cardUser->id, 'view_mode' => $viewMode]) }}"
                               class="px-2.5 py-1.5 rounded-full text-white text-[10.5px] font-extrabold shadow-xs hover:opacity-95 transition-all transform active:scale-95 shrink-0 whitespace-nowrap flex items-center gap-1"
                               style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;">
                                <span>Setujui ({{ $stat['pending_count'] }})</span>
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @else
                            <a href="{{ route('gomsn.overtime.index', ['bulan' => $isFilterUnprocessed ? 'unprocessed' : $selectedBulan, 'tahun' => $selectedTahun, 'user_id' => $cardUser->id, 'view_mode' => $viewMode]) }}"
                               class="px-2.5 py-1.5 rounded-full text-white text-[10.5px] font-bold shadow-2xs hover:opacity-95 transition-all transform active:scale-95 shrink-0 whitespace-nowrap flex items-center gap-1"
                               style="background: #04615c;">
                                <span>Detail</span>
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @endif
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
                    <a href="{{ route('gomsn.overtime.index', array_filter(['bulan' => $isFilterUnprocessed ? 'unprocessed' : $selectedBulan, 'tahun' => $selectedTahun, 'divisi' => $canFilterDivisi ? $selectedDivisi : null, 'view_mode' => $viewMode])) }}" 
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

            <!-- 2 Kartu Summary Atas (Mobile: 2-Kolom Ringkas & Proporsional) -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <!-- Card 1: TOTAL LEMBUR (Mint / Emerald) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-3.5 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-4 border-t-2 border-emerald-500">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 tracking-wider uppercase block truncate">
                            TOTAL LEMBUR
                        </span>
                        <span class="text-base sm:text-2xl font-black text-slate-900 block mt-0.5 truncate">
                            Rp {{ number_format($totalNominalLembur, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Card 2: RINCIAN PENGAJUAN (Soft Blue) -->
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-3.5 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-4 border-t-2 border-blue-500">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 tracking-wider uppercase block truncate">
                            RINCIAN PENGAJUAN
                        </span>
                        <div class="mt-1">
                            <span class="px-2.5 py-0.5 sm:px-2.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/70 inline-block">
                                Lembur: {{ $totalCountLembur }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1. MOBILE VIEW: Overtime Request Cards (sm:hidden) -->
            <div class="sm:hidden space-y-3.5">
                @forelse ($lemburList as $idx => $item)
                    @php
                        $hours = floor($item->durasi_menit / 60);
                        $mins = $item->durasi_menit % 60;
                        $durasiStr = "{$hours} jam " . sprintf('%02d mnt', $mins);
                        $uangLembur = (int) $item->nominal;
                    @endphp
                    <div id="lembur-card-{{ $item->id }}" data-lembur-row="{{ $item->id }}" class="bg-white rounded-2xl border border-slate-200 border-l-[6px]! border-l-[#0f766e]! shadow-xs hover:shadow-md p-4 sm:p-5 space-y-3.5 transition-all overflow-hidden scroll-mt-28" style="border-left: 6px solid #0f766e !important;">
                        <!-- Top Header: Tanggal Lembur -->
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-200 text-[#0f766e] flex flex-col items-center justify-center font-bold shrink-0 shadow-2xs">
                                <span class="leading-none text-base font-black">{{ \Carbon\Carbon::parse($item->tanggal)->format('d') }}</span>
                                <span class="text-[10px] uppercase font-black tracking-wider text-teal-700 mt-0.5">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('M') }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-slate-900 block truncate leading-tight">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, j F Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- Time & Duration Pill -->
                        <div class="flex items-center justify-between bg-slate-50/90 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs shadow-2xs">
                            <div class="flex items-center gap-2 text-slate-700">
                                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-bold text-slate-800 text-[13px]">
                                    {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                                </span>
                            </div>
                            <span class="font-extrabold text-slate-800 bg-white border border-slate-200 shadow-2xs px-2.5 py-1 rounded-lg text-xs inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $durasiStr }}
                            </span>
                        </div>

                        <!-- Alasan Section with Note Border -->
                        <div class="border border-slate-200 bg-slate-50/70 rounded-xl p-3 space-y-1.5 shadow-2xs">
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                <span>Alasan Lembur</span>
                            </span>
                            <div class="text-slate-800 text-[13px] leading-relaxed break-words font-medium">
                                {{ $item->alasan }}
                            </div>
                        </div>

                        <!-- Status & Action Footer -->
                        <div class="pt-3 border-t border-slate-200 flex items-center justify-between gap-2 flex-wrap">
                            <!-- Status Badge with Stronger Border -->
                            <div>
                                @if ($item->status === 'paid')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border-2 border-emerald-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>Selesai Dibayar</span>
                                    </span>
                                @elseif ($item->status === 'approved_atasan')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 border-2 border-sky-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>Disetujui Atasan</span>
                                    </span>
                                @elseif ($item->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border-2 border-rose-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Ditolak</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border-2 border-amber-300 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Approval</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2 ml-auto">
                                <!-- Finance Pay Button -->
                                @if ($isFinance && $item->status === 'approved_atasan')
                                    <button type="button" 
                                            @click="openPayModal({{ $item->id }}, '{{ $item->nomor_lembur }}', '{{ $item->user->name }}', {{ $uangLembur }})"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                        Bayar Lembur
                                    </button>
                                @endif

                                <!-- Atasan Approval Button (HRD tidak melakukan approve) -->
                                @if (($isSuperAdmin || $isAtasan) && ! $isHrd && $item->status === 'pending' && $item->user_id !== auth()->id())
                                    <form method="POST" action="{{ route('gomsn.overtime.approve', $item->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition cursor-pointer" 
                                                onclick="return confirm('Setujui pengajuan lembur ini?')">
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('gomsn.overtime.reject', $item->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition cursor-pointer" 
                                                onclick="return confirm('Tolak pengajuan lembur ini?')">
                                            Tolak
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-slate-700">Belum Ada Catatan Lembur</h4>
                        <p class="text-[11px] text-slate-400 max-w-xs mx-auto mt-1 leading-relaxed">
                            Tidak ada data rincian lembur untuk periode yang dipilih.
                        </p>
                        @if ((! $isAtasan && ! $isFinance && ! $isSuperAdmin && ! $isHrd) || ($viewMode === 'saya'))
                            <a href="{{ route('gomsn.overtime.create') }}" class="inline-flex items-center gap-1.5 mt-3.5 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Ajukan Lembur Sekarang</span>
                            </a>
                        @endif
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
                            <tr id="lembur-{{ $item->id }}" data-lembur-row="{{ $item->id }}" class="hover:bg-slate-50/70 transition-colors scroll-mt-28">
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

                                        <!-- Atasan Approval Button (HRD tidak melakukan approve) -->
                                        @if (($isSuperAdmin || $isAtasan) && ! $isHrd && $item->status === 'pending' && $item->user_id !== auth()->id())
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
                                    <div class="max-w-sm mx-auto space-y-2.5">
                                        <p>Tidak ada data rincian lembur untuk periode yang dipilih.</p>
                                        @if ((! $isAtasan && ! $isFinance && ! $isSuperAdmin && ! $isHrd) || ($viewMode === 'saya'))
                                        <div>
                                            <a href="{{ route('gomsn.overtime.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-xs hover:opacity-95 transition" style="background: var(--gomsn-active-bg) !important;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <span>Ajukan Lembur Sekarang</span>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
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

    @push('styles')
    <style>
        @keyframes approvedRowGlow {
            0% {
                background-color: rgba(16, 185, 129, 0.28) !important;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.55), 0 8px 24px rgba(16, 185, 129, 0.2) !important;
            }
            50% {
                background-color: rgba(16, 185, 129, 0.15) !important;
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.35) !important;
            }
            100% {
                background-color: transparent !important;
                box-shadow: none !important;
            }
        }
        .highlight-approved-row {
            animation: approvedRowGlow 3.5s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
            position: relative;
            z-index: 20;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const targetId = '{{ session('target_lembur_id') }}' || (window.location.hash ? window.location.hash.replace('#', '') : null);
            if (targetId) {
                const cleanNum = targetId.toString().replace(/^(lembur-card-|lembur-)/, '');
                const el = document.getElementById('lembur-' + cleanNum) 
                        || document.getElementById('lembur-card-' + cleanNum)
                        || document.getElementById(targetId);
                if (el) {
                    setTimeout(function () {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('highlight-approved-row');
                        setTimeout(function () {
                            el.classList.remove('highlight-approved-row');
                        }, 4000);
                    }, 150);
                }
            }

            // Simpan scroll saat submit aksi approval atau pay
            document.querySelectorAll('form').forEach(function (form) {
                if (form.action && (form.action.includes('approve') || form.action.includes('pay') || form.action.includes('reject'))) {
                    form.addEventListener('submit', function () {
                        sessionStorage.setItem('last_approval_scroll_y', window.scrollY);
                    });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
