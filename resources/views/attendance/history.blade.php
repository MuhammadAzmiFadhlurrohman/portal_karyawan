<x-layouts.app title="Riwayat Absen">
    @php
        $monthDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $monthName = $monthDate->translatedFormat('F');
    @endphp

    <div class="space-y-6 w-full pb-12"
         x-data="{
            showModal: false,
            modalData: {
                date: '',
                date_formatted: '',
                shift_name: '',
                clock_in: '',
                clock_out: ''
            },
            openCorrectionModal(data) {
                this.modalData = data;
                this.showModal = true;
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
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#historyWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="historyWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                        <span>Riwayat Absen</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-400/20 text-teal-300 border border-teal-400/30">
                            {{ $monthName }} {{ $year }}
                        </span>
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Rekapitulasi log kehadiran kerja, jam masuk &amp; pulang, durasi shift, dan pengajuan koreksi absensi.
                    </p>
                </div>
            </div>

            <!-- Right: Profile Info Card -->
            <div class="relative z-10 flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-3.5 py-2 text-white shrink-0 shadow-xs self-start sm:self-auto">
                <div class="relative shrink-0">
                    @if ($targetUser->foto)
                        <img src="/storage/{{ $targetUser->foto }}" 
                             alt="{{ $targetUser->name }}" 
                             class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-cyan-400/40 shadow-sm" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($targetUser->name) }}&background=155E63&color=fff&size=128';">
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#155E63] to-[#0c4349] border-2 border-cyan-400/40 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm">
                            {{ strtoupper(substr($targetUser->name, 0, 2)) }}
                        </div>
                    @endif
                </div>

                <div class="text-left leading-tight min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs sm:text-sm text-white tracking-tight truncate max-w-[140px] sm:max-w-[180px]">
                            {{ strtoupper($targetUser->name) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $targetUser->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-teal-200/85">
                        <span class="font-mono text-cyan-200">NIK: {{ $targetUser->employee_code }}</span>
                        @if ($targetUser->division)
                            <span class="truncate">· Divisi: {{ $targetUser->division }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Toolbar: Navigation & Filter Form -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 sm:px-5 sm:py-3.5 shadow-2xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5">
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('attendance.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Presensi Hari Ini</span>
                </a>

                @if (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage'))
                <a href="{{ route('attendance.reports') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-cyan-300 bg-cyan-50/70 hover:bg-cyan-100/70 text-cyan-900 text-xs font-semibold transition cursor-pointer">
                    <svg class="w-4 h-4 text-cyan-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Absensi Harian</span>
                </a>
                @endif

                <a href="{{ route('attendance.leaves') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition cursor-pointer">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Manajemen Cuti</span>
                </a>

                <a href="{{ route('attendance.deductions') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition cursor-pointer">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Keuangan Absen</span>
                </a>
            </div>

            <!-- Filter Period Form -->
            <form method="GET" action="{{ route('attendance.history') }}" class="flex flex-wrap items-center gap-2.5">
                @if ($canViewOthers && $allEmployees->count() > 1)
                    <div class="min-w-[180px]">
                        <select name="user_id" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:bg-white outline-none transition">
                            @foreach ($allEmployees as $emp)
                                <option value="{{ $emp->id }}" @selected($emp->id === $targetUser->id)>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <select name="month" class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:bg-white outline-none transition">
                    @for ($m = 1; $m <= 12; $m++)
                        @php $d = \Carbon\Carbon::create(null, $m, 1); @endphp
                        <option value="{{ $m }}" @selected($m === $month)>{{ $d->translatedFormat('F') }}</option>
                    @endfor
                </select>

                <select name="year" class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:bg-white outline-none transition font-mono">
                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold transition shadow-xs active:scale-[0.98] cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Tampilkan</span>
                </button>
            </form>
        </div>

        <!-- Flash Status Notification -->
        @if (session('status'))
            <div class="rounded-2xl bg-teal-50 border border-teal-200/80 text-teal-800 p-4 flex items-center gap-3 text-xs sm:text-sm shadow-2xs">
                <div class="w-8 h-8 rounded-xl bg-teal-100/80 flex items-center justify-center text-[#155E63] shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="font-medium">{{ session('status') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 p-4 text-xs sm:text-sm space-y-1 shadow-2xs">
                <div class="font-bold flex items-center gap-2 text-rose-900">
                    <svg class="w-4.5 h-4.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Pengajuan Koreksi Tidak Dapat Diproses</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6 text-xs text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 4 Summary Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- Total Hadir -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL HADIR</span>
                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#155E63] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                    {{ $stats['total_hadir'] }} <span class="text-xs font-semibold text-slate-500">Hari</span>
                </div>
                <span class="text-[11px] text-teal-700 font-medium block mt-1">Kehadiran tercatat</span>
            </div>

            <!-- Tepat Waktu -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TEPAT WAKTU</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-700 tracking-tight">
                    {{ $stats['total_tepat_waktu'] }} <span class="text-xs font-semibold text-slate-500">Hari</span>
                </div>
                <span class="text-[11px] text-emerald-600 font-medium block mt-1">Tanpa keterlambatan</span>
            </div>

            <!-- Terlambat -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TERLAMBAT</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-amber-700 tracking-tight">
                    {{ $stats['total_terlambat'] }} <span class="text-xs font-semibold text-slate-500">Hari</span>
                </div>
                <span class="text-[11px] text-amber-600 font-medium block mt-1">
                    {{ $stats['total_menit_terlambat'] > 0 ? $stats['total_menit_terlambat'] . ' menit akumulasi' : 'Nihil keterlambatan' }}
                </span>
            </div>

            <!-- Total Jam Kerja -->
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">JAM KERJA</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-indigo-700 tracking-tight">
                    {{ $stats['total_jam_kerja'] }} <span class="text-xs font-semibold text-slate-500">Jam</span>
                </div>
                <span class="text-[11px] text-indigo-600 font-medium block mt-1">Total durasi kerja</span>
            </div>
        </div>

        <!-- Filter Status Tabs / Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            @php
                $statusList = [
                    'all' => 'Semua',
                    'present' => 'Tepat Waktu',
                    'late' => 'Terlambat',
                    'leave' => 'Cuti & Izin',
                    'absent' => 'Alpa',
                ];
            @endphp

            @foreach ($statusList as $key => $label)
                <a href="{{ route('attendance.history', array_merge(request()->query(), ['status' => $key])) }}"
                   class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all shrink-0 {{ $statusFilter === $key ? 'bg-[#155E63] text-white shadow-2xs' : 'bg-white border border-slate-200/80 text-slate-600 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Attendance Records Table -->
        <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            @if ($attendances->isEmpty())
                <div class="p-12 sm:p-16 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-4 shadow-2xs">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Tidak ada catatan absensi</h3>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm">
                        Belum ada data kehadiran pada periode {{ $monthName }} {{ $year }} dengan filter yang dipilih.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-5">TANGGAL &amp; HARI</th>
                                <th class="py-3.5 px-5">SHIFT KERJA</th>
                                <th class="py-3.5 px-5">JAM MASUK</th>
                                <th class="py-3.5 px-5">JAM PULANG</th>
                                <th class="py-3.5 px-5">DURASI</th>
                                <th class="py-3.5 px-5 text-right pr-6">STATUS &amp; AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach ($attendances as $att)
                                @php
                                    $parsedDate = \Carbon\Carbon::parse($att->date);
                                    $isLate = ($att->late_minutes ?? 0) > 0 || $att->status === 'late';
                                    $dateKey = $parsedDate->toDateString();
                                    $corr = $corrections[$dateKey] ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <div class="font-bold text-slate-800 text-xs">
                                            {{ $parsedDate->translatedFormat('l, d F Y') }}
                                        </div>
                                        <span class="text-[11px] text-slate-400 font-mono">
                                            {{ $att->officeLocation?->name ?? 'Kantor Pusat' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        @if ($att->shift)
                                            <span class="font-semibold text-slate-700 block">{{ $att->shift->name }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $att->shift->start_time }} - {{ $att->shift->end_time }}</span>
                                        @else
                                            <span class="text-slate-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        @if ($att->clock_in_at)
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold font-mono text-slate-800">
                                                    {{ \Carbon\Carbon::parse($att->clock_in_at)->format('H:i:s') }}
                                                </span>
                                                @if ($isLate)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                        +{{ $att->late_minutes }}m
                                                    </span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                        Tepat
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        @if ($att->clock_out_at)
                                            <span class="font-bold font-mono text-slate-800">
                                                {{ \Carbon\Carbon::parse($att->clock_out_at)->format('H:i:s') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Belum absen</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        @if ($att->work_duration_minutes)
                                            @php
                                                $hours = floor($att->work_duration_minutes / 60);
                                                $mins = $att->work_duration_minutes % 60;
                                            @endphp
                                            <span class="font-semibold text-slate-700 font-mono">
                                                {{ $hours }}j {{ $mins }}m
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-right pr-6 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            @if ($att->status === 'present' || $att->status === 'ontime')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Hadir Tepat
                                                </span>
                                            @elseif ($att->status === 'late' || $isLate)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Terlambat
                                                </span>
                                            @elseif (in_array($att->status, ['leave', 'cuti', 'izin']))
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Cuti / Izin
                                                </span>
                                            @elseif ($att->status === 'absent')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Alpa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                    {{ strtoupper($att->status) }}
                                                </span>
                                            @endif

                                            <!-- Status Koreksi atau Tombol Koreksi -->
                                            @if ($corr && in_array($corr->status, ['pending_supervisor', 'pending_hrd']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Koreksi menunggu approval atasan">
                                                    ⏳ Menunggu Atasan
                                                </span>
                                            @elseif ($corr && $corr->status === 'approved')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200" title="Koreksi telah disetujui">
                                                    ✓ Terkoreksi
                                                </span>
                                            @else
                                                <button type="button"
                                                        @click="openCorrectionModal({
                                                            date: '{{ $parsedDate->toDateString() }}',
                                                            date_formatted: '{{ $parsedDate->translatedFormat('l, d F Y') }}',
                                                            shift_name: '{{ $att->shift?->name ?? 'Shift Kerja' }} ({{ $att->shift?->start_time ?? '08:15' }} - {{ $att->shift?->end_time ?? '17:00' }})',
                                                            clock_in: '{{ $att->clock_in_at ? \Carbon\Carbon::parse($att->clock_in_at)->format('H:i') : ($att->shift ? substr($att->shift->start_time, 0, 5) : '08:15') }}',
                                                            clock_out: '{{ $att->clock_out_at ? \Carbon\Carbon::parse($att->clock_out_at)->format('H:i') : ($att->shift ? substr($att->shift->end_time, 0, 5) : '17:00') }}'
                                                        })"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-teal-200 bg-teal-50/70 hover:bg-teal-100 text-[#155E63] text-xs font-semibold transition-all shadow-2xs active:scale-[0.98]">
                                                    <svg class="w-3.5 h-3.5 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>{{ ($corr && $corr->status === 'rejected') ? 'Koreksi Ulang' : 'Koreksi' }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- MODAL AJUKAN KOREKSI ABSENSI KE ATASAN -->
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.stop class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
                
                <!-- Modal Header -->
                <div class="bg-[#155E63] text-white px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="!text-white font-bold text-base leading-tight">Pengajuan Koreksi Absen</h3>
                            <p class="text-xs text-teal-100/90 mt-0.5">Diteruskan ke Atasan untuk ditinjau dan disetujui</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form method="POST" action="{{ route('attendance.corrections.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="date" :value="modalData.date">

                    <!-- Info Box Tanggal yang dikoreksi -->
                    <div class="rounded-2xl bg-teal-50/70 border border-teal-200/80 p-3.5 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 block">Jadwal yang Dikoreksi</span>
                            <div class="font-bold text-slate-800 text-sm mt-0.5" x-text="modalData.date_formatted"></div>
                        </div>
                        <div class="px-2.5 py-1 rounded-lg bg-teal-100 text-teal-800 font-mono text-xs font-semibold" x-text="modalData.shift_name"></div>
                    </div>

                    <!-- Jam Masuk & Jam Pulang yang Diajukan -->
                    <div class="grid grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Jam Masuk Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="requested_clock_in_time" x-model="modalData.clock_in" required
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-mono text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Jam Pulang Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="requested_clock_out_time" x-model="modalData.clock_out" required
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-mono text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                        </div>
                    </div>

                    <!-- Status Usulan & Tipe Kehadiran -->
                    <div class="grid grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Status Usulan <span class="text-rose-500">*</span>
                            </label>
                            <select name="requested_status" required
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition cursor-pointer">
                                <option value="present">Hadir Tepat Waktu</option>
                                <option value="late">Hadir Terlambat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Tipe Kehadiran <span class="text-rose-500">*</span>
                            </label>
                            <select name="requested_attendance_type" required
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition cursor-pointer">
                                <option value="office">Kantor (Office)</option>
                                <option value="field">Lapangan (Field)</option>
                                <option value="remote">Remote (WFH)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Lampiran Bukti -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Lampiran Bukti (Opsional)
                        </label>
                        <div class="relative flex items-center">
                            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" 
                                   class="block w-full text-xs text-slate-500 rounded-xl border border-slate-200 bg-slate-50/50 p-2
                                          file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold
                                          file:bg-teal-50 file:text-[#155E63] hover:file:bg-teal-100 cursor-pointer focus:outline-none focus:border-[#155E63]">
                        </div>
                        <span class="text-[10px] text-slate-400 block mt-1">Format: JPG, PNG, atau PDF (maksimal 4MB)</span>
                    </div>

                    <!-- Alasan Koreksi -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Koreksi <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="reason" rows="3" required 
                                  placeholder="Jelaskan alasan koreksi (misal: kendala GPS biometrik, penugasan teknis darurat di lapangan, dll)..."
                                  class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs sm:text-sm text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition"></textarea>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs sm:text-sm font-semibold transition shadow-xs active:scale-[0.98] cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Kirim ke Atasan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
