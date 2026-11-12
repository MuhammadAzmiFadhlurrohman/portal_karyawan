<x-layouts.app title="Riwayat Absen">
    @php
        $monthDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $monthName = $monthDate->translatedFormat('F');
    @endphp

    <div class="space-y-6 w-full pb-12"
         x-data="{
            openDay: null,
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
                    <p class="text-[11px] sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug">
                        Rekap log kehadiran, durasi kerja &amp; koreksi absensi
                    </p>
                </div>
            </div>
        </div>

        <!-- Filter Period Toolbar -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3.5 sm:px-5 sm:py-3.5 shadow-2xs">
            <form method="GET" action="{{ route('attendance.history') }}" class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2.5">
                @if ($canViewOthers && $allEmployees->count() > 1)
                    <div class="col-span-2 sm:w-auto sm:min-w-[220px] sm:flex-1 sm:max-w-xs">
                        <select name="user_id" class="w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:bg-white outline-none transition">
                            @foreach ($allEmployees as $emp)
                                <option value="{{ $emp->id }}" @selected($emp->id === $targetUser->id)>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <select name="month" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:bg-white outline-none transition">
                    @for ($m = 1; $m <= 12; $m++)
                        @php $d = \Carbon\Carbon::create(null, $m, 1); @endphp
                        <option value="{{ $m }}" @selected($m === $month)>{{ $d->translatedFormat('F') }}</option>
                    @endfor
                </select>

                <select name="year" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#155E63] focus:bg-white outline-none transition font-mono">
                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="col-span-2 sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold transition shadow-xs active:scale-[0.98] cursor-pointer">
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

        <!-- Attendance Records Card (Mobile Accordion List + Desktop Table) -->
        <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            
            <!-- MOBILE VIEW: Collapsible Daily Card List -->
            <div class="md:hidden divide-y divide-slate-100">
                @foreach ($dailyRecords as $row)
                    @php
                        $corr = $row['correction'];
                        $isLibur = $row['status'] === 'libur';
                        $isAbsent = $row['status'] === 'absent';
                        $isPresent = $row['status'] === 'present';
                        $isLate = $row['status'] === 'late';
                        $isLeave = $row['status'] === 'leave';
                    @endphp
                    <div class="transition-colors" :class="openDay === {{ $row['day'] }} ? 'bg-slate-50/40' : 'bg-white'">
                        <!-- Summary Row (Tap to Toggle Dropdown) -->
                        <button type="button"
                                @click="openDay = (openDay === {{ $row['day'] }} ? null : {{ $row['day'] }})"
                                class="w-full flex items-center text-left py-3.5 px-4 active:bg-slate-50 transition cursor-pointer">
                            
                            <!-- Left Date Column -->
                            <div class="w-14 pr-3 border-r border-slate-100 flex flex-col items-center justify-center shrink-0">
                                <span class="text-[17px] font-extrabold leading-none {{ $row['day_short'] === 'MIN' || $isLibur ? 'text-slate-700' : 'text-slate-800' }}">
                                    {{ $row['day_num'] }}
                                </span>
                                <span class="text-[10px] font-bold tracking-wider uppercase mt-1 {{ $row['day_short'] === 'MIN' ? 'text-rose-400' : 'text-slate-400' }}">
                                    {{ $row['day_short'] }}
                                </span>
                            </div>

                            <!-- Middle Summary Text -->
                            <div class="flex-1 px-3.5 min-w-0">
                                @if ($row['clock_in'])
                                    <div class="flex items-center gap-1.5 font-mono text-xs font-bold text-slate-800">
                                        <span>{{ $row['clock_in'] }}</span>
                                        <span class="text-slate-300">-</span>
                                        <span class="{{ $row['clock_out'] ? 'text-slate-800' : 'text-slate-400 italic font-sans font-normal' }}">
                                            {{ $row['clock_out'] ?? 'Belum pulang' }}
                                        </span>
                                    </div>
                                @elseif ($isLibur)
                                    <span class="text-xs italic text-slate-400">Libur</span>
                                @elseif ($isLeave)
                                    <span class="text-xs italic text-sky-600">{{ $row['notes'] ?? 'Cuti / Izin' }}</span>
                                @elseif ($row['is_future'])
                                    <span class="text-xs italic text-slate-400">Belum berlangsung</span>
                                @else
                                    <span class="text-xs italic text-slate-400">Tidak absen</span>
                                @endif
                            </div>

                            <!-- Right Status Badge + Chevron -->
                            <div class="flex items-center gap-2 shrink-0">
                                @if ($isLibur)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                                        </svg>
                                        <span>Libur</span>
                                    </span>
                                @elseif ($isPresent)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Hadir</span>
                                    </span>
                                @elseif ($isLate)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Terlambat</span>
                                    </span>
                                @elseif ($isLeave)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200/80">
                                        <span>Cuti/Izin</span>
                                    </span>
                                @elseif ($isAbsent)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M11 6a3 3 0 11-6 0 3 3 0 016 0zM14 17a6 6 0 00-12 0h12zM13 8a1 1 0 100 2h4a1 1 0 100-2h-4z"/>
                                        </svg>
                                        <span>Alpa</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">
                                        <span>Jadwal</span>
                                    </span>
                                @endif

                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                     :class="openDay === {{ $row['day'] }} ? 'rotate-180 text-slate-600' : ''"
                                     fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <!-- Collapsible Dropdown Details -->
                        <div x-show="openDay === {{ $row['day'] }}"
                             x-cloak
                             class="px-4 pb-4 pt-2 bg-slate-50/70 border-t border-slate-100 space-y-2.5">
                            
                            <!-- JAM MASUK -->
                            <div class="rounded-2xl bg-white border border-slate-200/80 px-4 py-3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">JAM MASUK</span>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-800 font-mono">
                                        {{ $row['clock_in'] ?? '-' }}
                                    </span>
                                    @if ($row['late_minutes'] > 0)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            +{{ $row['late_minutes'] }}m
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- JAM PULANG -->
                            <div class="rounded-2xl bg-white border border-slate-200/80 px-4 py-3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">JAM PULANG</span>
                                <div class="mt-0.5">
                                    <span class="text-sm font-bold text-slate-800 font-mono">
                                        {{ $row['clock_out'] ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <!-- DURASI -->
                            <div class="rounded-2xl bg-white border border-slate-200/80 px-4 py-3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">DURASI</span>
                                <div class="mt-0.5">
                                    <span class="text-sm font-bold text-slate-800 font-mono">
                                        {{ $row['duration_text'] ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <!-- CATATAN -->
                            <div class="rounded-2xl bg-white border border-slate-200/80 px-4 py-3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">CATATAN</span>
                                <div class="mt-1">
                                    @if ($isAbsent)
                                        <div class="flex items-center gap-1.5 text-rose-600 font-bold text-xs">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Alpa ke-{{ $row['alpa_index'] }}</span>
                                        </div>
                                        <span class="text-xs font-extrabold text-rose-600 block mt-0.5">
                                            Potongan: Rp {{ number_format($row['deduction_amount'], 0, ',', '.') }}
                                        </span>
                                    @elseif ($row['notes'])
                                        <span class="text-xs font-semibold text-slate-700">{{ $row['notes'] }}</span>
                                    @else
                                        <span class="text-sm font-bold text-slate-800">-</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Tombol Ajukan Perubahan -->
                            <div class="rounded-2xl bg-blue-50/50 border border-blue-100/90 p-2">
                                @if ($corr && in_array($corr->status, ['pending_supervisor', 'pending_hrd']))
                                    <div class="w-full py-2.5 px-4 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-700 font-bold text-xs flex items-center justify-center gap-2">
                                        <span>⏳ Koreksi Menunggu Persetujuan Atasan</span>
                                    </div>
                                @elseif ($corr && $corr->status === 'approved')
                                    <div class="w-full py-2.5 px-4 rounded-xl bg-teal-50 border border-teal-200/80 text-teal-700 font-bold text-xs flex items-center justify-center gap-2">
                                        <span>✓ Koreksi Absensi Telah Disetujui</span>
                                    </div>
                                @else
                                    <button type="button"
                                            @click="openCorrectionModal({
                                                date: '{{ $row['date'] }}',
                                                date_formatted: '{{ $row['date_formatted'] }}',
                                                shift_name: '{{ $row['shift_label'] }}',
                                                clock_in: '{{ $row['default_clock_in'] }}',
                                                clock_out: '{{ $row['default_clock_out'] }}'
                                            })"
                                            class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-blue-50/40 border border-blue-200/80 text-blue-600 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs active:scale-[0.99] transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                        <span>{{ ($corr && $corr->status === 'rejected') ? 'Ajukan Ulang Perubahan' : 'Ajukan Perubahan' }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- DESKTOP VIEW: Full Monthly Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-5">TANGGAL &amp; HARI</th>
                            <th class="py-3.5 px-5">SHIFT KERJA</th>
                            <th class="py-3.5 px-5">JAM MASUK</th>
                            <th class="py-3.5 px-5">JAM PULANG</th>
                            <th class="py-3.5 px-5">DURASI</th>
                            <th class="py-3.5 px-5">CATATAN</th>
                            <th class="py-3.5 px-5 text-right pr-6">STATUS &amp; AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach ($dailyRecords as $row)
                            @php
                                $corr = $row['correction'];
                                $isLibur = $row['status'] === 'libur';
                                $isAbsent = $row['status'] === 'absent';
                                $isPresent = $row['status'] === 'present';
                                $isLate = $row['status'] === 'late';
                                $isLeave = $row['status'] === 'leave';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition {{ $isLibur ? 'bg-slate-50/30' : '' }}">
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 text-xs">
                                        {{ $row['date_formatted'] }}
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-mono">
                                        {{ $row['office_name'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    @if ($isLibur)
                                        <span class="text-slate-400 italic">Libur</span>
                                    @elseif ($row['shift_name'])
                                        <span class="font-semibold text-slate-700 block">{{ $row['shift_name'] }}</span>
                                        @if ($row['shift_hours'])
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $row['shift_hours'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    @if ($row['clock_in_full'])
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold font-mono text-slate-800">
                                                {{ $row['clock_in_full'] }}
                                            </span>
                                            @if ($row['late_minutes'] > 0)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    +{{ $row['late_minutes'] }}m
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
                                    @if ($row['clock_out_full'])
                                        <span class="font-bold font-mono text-slate-800">
                                            {{ $row['clock_out_full'] }}
                                        </span>
                                    @elseif ($row['clock_in_full'])
                                        <span class="text-slate-400 italic">Belum absen</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    @if ($row['duration_text'])
                                        <span class="font-semibold text-slate-700 font-mono">
                                            {{ $row['duration_text'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5">
                                    @if ($isAbsent)
                                        <div class="text-rose-600 font-bold text-[11px]">
                                            ⚠️ Alpa ke-{{ $row['alpa_index'] }}
                                        </div>
                                        <span class="text-[11px] font-bold text-rose-600">
                                            Potongan: Rp {{ number_format($row['deduction_amount'], 0, ',', '.') }}
                                        </span>
                                    @elseif ($row['notes'])
                                        <span class="text-slate-600 text-xs">{{ $row['notes'] }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right pr-6 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2 justify-end">
                                        @if ($isLibur)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                Libur
                                            </span>
                                        @elseif ($isPresent)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Hadir
                                            </span>
                                        @elseif ($isLate)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Terlambat
                                            </span>
                                        @elseif ($isLeave)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                Cuti / Izin
                                            </span>
                                        @elseif ($isAbsent)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                Alpa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                Jadwal
                                            </span>
                                        @endif

                                        @if ($corr && in_array($corr->status, ['pending_supervisor', 'pending_hrd']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                ⏳ Menunggu Atasan
                                            </span>
                                        @elseif ($corr && $corr->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                ✓ Terkoreksi
                                            </span>
                                        @else
                                            <button type="button"
                                                    @click="openCorrectionModal({
                                                        date: '{{ $row['date'] }}',
                                                        date_formatted: '{{ $row['date_formatted'] }}',
                                                        shift_name: '{{ $row['shift_label'] }}',
                                                        clock_in: '{{ $row['default_clock_in'] }}',
                                                        clock_out: '{{ $row['default_clock_out'] }}'
                                                    })"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-600 text-xs font-semibold transition-all shadow-2xs active:scale-[0.98] cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span>{{ ($corr && $corr->status === 'rejected') ? 'Ajukan Ulang' : 'Ajukan Perubahan' }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
