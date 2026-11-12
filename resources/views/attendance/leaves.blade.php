<x-layouts.app title="Manajemen Cuti & Izin">
    <div class="space-y-6 w-full pb-12" x-data="{ 
        showModal: false, 
        leaveType: 'annual', 
        openModal(type) { 
            this.leaveType = type || 'annual'; 
            this.showModal = true; 
        } 
    }">

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                        <span>Manajemen Cuti &amp; Izin</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-300 border border-teal-400/30">
                            Kuota: 12 Hari/Tahun
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            Maks 3 Hari/Bulan
                        </span>
                        @if ($balance->is_locked_override)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Saldo Terkunci HRD
                            </span>
                        @endif
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Kelola pengajuan cuti tahunan, cuti sakit, izin khusus, dan riwayat konversi alpa
                    </p>
                </div>
            </div>

            <!-- Right: Profile Info Card (Geser ke Kanan, tanpa awan putih) -->
            <div class="relative z-10 flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-3.5 py-2 text-white shrink-0 shadow-xs self-start sm:self-auto">
                <div class="relative shrink-0">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" 
                             alt="" 
                             class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-cyan-400/40 shadow-sm" 
                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <div style="display: none;" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#18C4C4] to-[#155E63] border-2 border-cyan-400/40 text-white items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                            {{ $user->initials }}
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#18C4C4] to-[#155E63] border-2 border-cyan-400/40 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>

                <div class="text-left leading-tight min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs sm:text-sm text-white tracking-tight truncate max-w-[140px] sm:max-w-[180px]">
                            {{ strtoupper($user->name) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-teal-200/85">
                        <span class="font-mono text-cyan-200">NIK: {{ $user->employee_code }}</span>
                        @if ($user->division)
                            <span class="truncate">· Divisi: {{ $user->division }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Toolbar: Navigation & Trigger Modal Buttons -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3 sm:px-5 sm:py-3.5 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
            <!-- Left: Back Button & Year Filter Form -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage'))
                <a href="{{ route('attendance.reports') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-cyan-300 bg-cyan-50/70 hover:bg-cyan-100/70 text-cyan-900 text-xs font-semibold transition cursor-pointer">
                    <svg class="w-4 h-4 text-cyan-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Absensi</span>
                </a>
                @endif

                <a href="{{ route('attendance.history') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Riwayat Absen</span>
                </a>

                <a href="{{ route('attendance.deductions') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition cursor-pointer">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Keuangan Absen</span>
                </a>

                <form method="GET" action="{{ route('attendance.leaves') }}" class="flex items-center gap-1.5">
                    <input type="number" name="year" value="{{ $year }}" min="2025" max="2035" 
                           class="w-20 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-mono font-semibold text-slate-700 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                    <button type="submit" 
                            class="px-2.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition cursor-pointer">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Right: Modal Trigger Button -->
            <div class="flex items-center">
                <button type="button" 
                        @click="openModal('sick')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 text-xs font-bold shadow-2xs transition active:scale-[0.98] cursor-pointer">
                    <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Ajukan Izin &amp; Sakit</span>
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-2xl p-4 bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-2xl p-4 bg-rose-50 border border-rose-200/80 text-rose-800 text-xs shadow-2xs">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Pengajuan Cuti / Izin Tidak Valid</span>
                </div>
                <ul class="list-disc list-inside text-rose-700 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Summary Metric Cards (4 Cards for Leave Balance) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Kuota Cuti Tahunan -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kuota Tahunan</span>
                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
                        {{ $balance->annual_quota }} <span class="text-sm font-semibold text-slate-500 font-sans">Hari</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                        <span>Jatah dasar 1 tahun penuh</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Sisa Cuti Aktif -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sisa Cuti Aktif</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-emerald-600 font-mono tracking-tight">
                        {{ $balance->remaining_days }} <span class="text-sm font-semibold text-slate-500 font-sans">Hari</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Batas pakai maks 3 hari / bulan</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Terpakai Cuti -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Terpakai Cuti</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-blue-700 font-mono tracking-tight">
                        {{ $balance->used_days }} <span class="text-sm font-semibold text-slate-500 font-sans">Hari</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        <span>Total cuti tahunan disetujui</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Terpotong Alpa -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Terpotong Alpa</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-rose-600 font-mono tracking-tight">
                        {{ $balance->alpa_deducted_days }} <span class="text-sm font-semibold text-slate-500 font-sans">Hari</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Alpa s/d 3 kali memotong cuti</span>
                    </div>
                </div>
            </div>

        </div>

        @if ($approvalQueue->isNotEmpty())
            <!-- Antrean Persetujuan Cuti (Atasan / HRD) -->
            <div class="rounded-2xl border border-amber-200/80 bg-amber-50/40 p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                                Antrean Persetujuan Cuti &amp; Izin ({{ $approvalQueue->count() }})
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Memerlukan persetujuan Atasan atau HRD</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-200/80 text-amber-900 border border-amber-300">
                        Atasan &rarr; HRD
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach ($approvalQueue as $item)
                        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-heading font-bold text-sm text-slate-900">
                                        {{ $item->user?->name }}
                                    </span>
                                    @if ($item->leave_type === 'annual')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Cuti Tahunan</span>
                                    @elseif ($item->leave_type === 'sick')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Cuti Sakit</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Cuti Khusus</span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 font-mono">
                                        {{ $item->working_days }} Hari Kerja
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-slate-600 mt-1">
                                    {{ $item->start_date->format('Y-m-d') }} s.d. {{ $item->end_date->format('Y-m-d') }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">{{ $item->reason }}</p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @if ($item->status === 'pending_supervisor')
                                    <form method="POST" action="{{ route('attendance.leaves.approve-supervisor', $item) }}">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                                            Approve Atasan
                                        </button>
                                    </form>
                                @endif
                                @if ($user->can('attendance.employee.manage') || $item->status === 'pending_hrd')
                                    <form method="POST" action="{{ route('attendance.leaves.approve-hrd', $item) }}">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                                            Approve Final HRD
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Riwayat Cuti & Konversi Alpa (Full Width) -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                            Riwayat Cuti, Izin &amp; Konversi Alpa Saya
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar cuti tahunan, sakit, izin khusus, dan alpa yang memotong kuota cuti</p>
                    </div>
                </div>

                <span class="text-xs text-slate-400">Tahun Aktif: <strong class="text-slate-700 font-mono">{{ $year }}</strong></span>
            </div>

            @if ($myLeaves->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-200 p-10 text-center bg-slate-50/50">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-2.5 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800">Belum Ada Riwayat Cuti / Alpa</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Pengajuan cuti tahunan, cuti sakit, izin khusus, maupun alpa yang memotong jatah cuti akan tercatat di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-3.5">Jenis Pengajuan</th>
                                <th class="py-3 px-3.5">Rentang Tanggal</th>
                                <th class="py-3 px-3.5 text-center">Durasi</th>
                                <th class="py-3 px-3.5">Alasan / Catatan</th>
                                <th class="py-3 px-3.5 text-center">Status</th>
                                <th class="py-3 px-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($myLeaves as $lv)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-3.5">
                                        @if ($lv->is_alpa_conversion)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                <span>Alpa Memotong Cuti</span>
                                            </span>
                                        @elseif ($lv->leave_type === 'annual')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                <span>Cuti Tahunan</span>
                                            </span>
                                        @elseif ($lv->leave_type === 'sick')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>Cuti Sakit</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                <span>Izin / Cuti Khusus</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 font-mono text-slate-800">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $lv->start_date->format('d/m/Y') }}</span>
                                            @if ($lv->start_date->ne($lv->end_date))
                                                <span class="text-slate-400">&rarr;</span>
                                                <span>{{ $lv->end_date->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-3.5 text-center font-mono font-bold text-slate-800">
                                        {{ $lv->working_days }} hari
                                    </td>
                                    <td class="py-3 px-3.5 text-slate-600 max-w-xs truncate">
                                        {{ $lv->reason }}
                                        @if ($lv->attachment_path)
                                            <span class="block text-[10px] text-teal-600 font-semibold mt-0.5">Ada Lampiran Bukti</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 text-center">
                                        @if ($lv->status === 'approved')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Disetujui</span>
                                        @elseif ($lv->status === 'cancelled')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Dibatalkan</span>
                                        @elseif ($lv->status === 'pending_supervisor')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu Atasan</span>
                                        @elseif ($lv->status === 'pending_hrd')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Menunggu HRD</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ strtoupper($lv->status) }}</span>
                                        @endif

                                        @if ($lv->is_alpa_conversion && $lv->dispute_deadline_at)
                                            <span class="block text-[9px] text-rose-500 font-mono mt-0.5">Sanggah s.d. {{ $lv->dispute_deadline_at->format('d/m H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 text-right">
                                        @if (in_array($lv->status, ['pending_supervisor', 'pending_hrd', 'approved'], true) && ! $lv->is_alpa_conversion && now('Asia/Jakarta')->startOfDay()->lt($lv->start_date))
                                            <form method="POST" action="{{ route('attendance.leaves.cancel', $lv) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan cuti ini dan mengembalikan kuota?');">
                                                @csrf
                                                <input type="hidden" name="reason" value="Dibatalkan oleh karyawan sebelum tanggal mulai">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold border border-rose-200 transition cursor-pointer">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @can('attendance.employee.manage')
            <!-- Kunci Override Saldo Cuti Manual (Khusus HRD) -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </div>
                    <div>
                        <h2 class="font-heading text-sm sm:text-base font-bold text-slate-800 leading-tight">
                            Override Saldo Cuti Karyawan (Khusus HRD)
                        </h2>
                        <span class="text-[11px] text-slate-400">Penyesuaian manual saldo cuti tahunan dengan proteksi audit log</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('attendance.leaves.override-balance') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3.5 mt-4 items-end">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Karyawan
                        </label>
                        <select name="user_id" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition cursor-pointer">
                            @foreach ($allBalances as $b)
                                <option value="{{ $b->user_id }}">
                                    {{ $b->user?->name }} (Sisa: {{ $b->remaining_days }} hari) {{ $b->is_locked_override ? '[TERKUNCI]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Sisa Saldo Baru (Hari)
                        </label>
                        <input type="number" name="remaining_days" value="12" min="0" max="36" required 
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono font-semibold text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Override (Audit)
                        </label>
                        <input type="text" name="reason" required placeholder="Kompensasi lembur / penyesuaian HRD" 
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" name="is_locked_override" value="1" checked class="rounded text-[#155E63] focus:ring-[#155E63]">
                            <span>Kunci Saldo</span>
                        </label>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                            Simpan Saldo
                        </button>
                    </div>
                </form>
            </div>
        @endcan

        <!-- ============================================================== -->
        <!-- MODAL AJUKAN CUTI / IZIN / SAKIT                              -->
        <!-- ============================================================== -->
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.stop class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
                
                <!-- Modal Header -->
                <div class="bg-[#155E63] text-white px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="!text-white font-bold text-base leading-tight" 
                                x-text="leaveType === 'sick' ? 'Pengajuan Izin & Sakit' : 'Pengajuan Cuti Karyawan'"></h3>
                            <p class="text-xs text-teal-100/90 mt-0.5">Diteruskan berjenjang ke Atasan dan HRD</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form method="POST" action="{{ route('attendance.leaves.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf

                    <!-- Segmented / Radio Pilihan Jenis Cuti -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jenis Pengajuan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2 p-1 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold">
                            <button type="button" 
                                    @click="leaveType = 'annual'" 
                                    :class="leaveType === 'annual' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/80 font-bold' : 'text-slate-500 hover:text-slate-800'"
                                    class="py-2 px-2.5 rounded-lg text-center transition cursor-pointer">
                                Cuti Tahunan
                            </button>
                            <button type="button" 
                                    @click="leaveType = 'sick'" 
                                    :class="leaveType === 'sick' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/80 font-bold' : 'text-slate-500 hover:text-slate-800'"
                                    class="py-2 px-2.5 rounded-lg text-center transition cursor-pointer">
                                Cuti Sakit
                            </button>
                            <button type="button" 
                                    @click="leaveType = 'special'" 
                                    :class="leaveType === 'special' ? 'bg-white text-slate-900 shadow-xs border border-slate-200/80 font-bold' : 'text-slate-500 hover:text-slate-800'"
                                    class="py-2 px-2.5 rounded-lg text-center transition cursor-pointer">
                                Izin Khusus
                            </button>
                        </div>
                        <input type="hidden" name="leave_type" :value="leaveType">
                        
                        <!-- Info hint per type -->
                        <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-1.5">
                            <template x-if="leaveType === 'annual'">
                                <span class="text-teal-700 font-medium">Memotong kuota cuti tahunan (Sisa: {{ $balance->remaining_days }} hari, batas maks 3 hari/bulan).</span>
                            </template>
                            <template x-if="leaveType === 'sick'">
                                <span class="text-amber-700 font-medium">Cuti sakit &gt; 1 hari wajib melampirkan surat dokter.</span>
                            </template>
                            <template x-if="leaveType === 'special'">
                                <span class="text-blue-700 font-medium">Izin keperluan mendesak / penting / keluarga.</span>
                            </template>
                        </div>
                    </div>

                    <!-- Tanggal Mulai & Tanggal Selesai -->
                    <div class="grid grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Tanggal Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="start_date" 
                                   value="{{ old('start_date', now('Asia/Jakarta')->addDays(1)->toDateString()) }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-mono text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Tanggal Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="end_date" 
                                   value="{{ old('end_date', now('Asia/Jakarta')->addDays(1)->toDateString()) }}" 
                                   required 
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-mono text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                        </div>
                    </div>

                    <!-- Lampiran Bukti / Surat Dokter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Lampiran Surat Dokter / Bukti Pendukung
                            <span x-show="leaveType === 'sick'" class="text-amber-600 font-normal">(Wajib jika &gt; 1 hari)</span>
                        </label>
                        <div class="relative flex items-center">
                            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" 
                                   class="block w-full text-xs text-slate-500 rounded-xl border border-slate-200 bg-slate-50/50 p-2
                                          file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold
                                          file:bg-teal-50 file:text-[#155E63] hover:file:bg-teal-100 cursor-pointer focus:outline-none focus:border-[#155E63]">
                        </div>
                        <span class="text-[10px] text-slate-400 block mt-1">Format: JPG, PNG, atau PDF (maksimal 4MB)</span>
                    </div>

                    <!-- Alasan Cuti -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Pengajuan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="reason" rows="3" required 
                                  placeholder="Jelaskan keperluan cuti, sakit, atau izin dan rencana pelimpahan tugas..."
                                  class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs sm:text-sm text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition"></textarea>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" @click="showModal = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs sm:text-sm font-semibold transition shadow-xs active:scale-[0.98] cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Kirim Pengajuan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
