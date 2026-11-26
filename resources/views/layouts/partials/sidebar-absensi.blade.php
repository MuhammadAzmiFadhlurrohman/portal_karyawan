<div x-data="{ openAbsensi: {{ $isAbsensiActive ? 'true' : "sessionStorage.getItem('gomsn_sb_absensi') === '1'" }} }">
    <button type="button" 
            @click="openAbsensi = !openAbsensi; sessionStorage.setItem('gomsn_sb_absensi', openAbsensi ? '1' : '0')"
            class="w-full pt-2.5 pb-1.5 px-3 flex items-center justify-between text-white/90 hover:text-white transition group cursor-pointer select-none rounded-xl hover:bg-white/10">
        <div class="flex items-center gap-2 flex-1">
            <span class="text-[9px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                ABSENSI &amp; CUTI
            </span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>
        <div class="flex items-center gap-1.5 ml-2">
            @if ($isAbsensiActive)
                <span class="w-1.5 h-1.5 rounded-full bg-[#18C4C4] shadow-xs"></span>
            @endif
            <svg class="w-4 h-4 text-white/70 group-hover:text-white transition-transform duration-200 ease-out" 
                 :class="openAbsensi ? 'rotate-180' : ''" 
                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </button>

    <div id="sb-menu-absensi" x-show="openAbsensi" style="{{ $isAbsensiActive ? '' : 'display: none;' }}" class="space-y-1 mt-1">
        <a href="{{ route('attendance.index') }}"
           @if (request()->routeIs('attendance.index')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.index') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            <span>Absen</span>
            @if (request()->routeIs('attendance.index'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        @if ($canViewReports)
        <!-- Riwayat & Laporan Absensi (Dropdown Sub-Menu untuk Admin/HRD) -->
        <div x-data="{ openRiwayat: {{ $isRiwayatActive ? 'true' : "sessionStorage.getItem('gomsn_sb_riwayat') === '1'" }} }" class="space-y-1">
            <button type="button" 
                     @click="openRiwayat = !openRiwayat; sessionStorage.setItem('gomsn_sb_riwayat', openRiwayat ? '1' : '0')"
                     @if ($isRiwayatActive) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                     class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 cursor-pointer {{ $isRiwayatActive ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="truncate">Riwayat &amp; Laporan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    @if ($isRiwayatActive)
                    <span class="w-1.5 h-1.5 rounded-full bg-white shadow-xs"></span>
                    @endif
                    <svg class="w-4 h-4 shrink-0 text-white/80 transition-transform duration-200 ease-out" 
                         :class="openRiwayat ? 'rotate-180' : ''" 
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            <div id="sb-menu-riwayat" x-show="openRiwayat" style="{{ $isRiwayatActive ? '' : 'display: none;' }}" class="pl-4 pr-1 space-y-1 pt-0.5">
                <a href="{{ route('attendance.history') }}" 
                   @if (request()->routeIs('attendance.history')) data-sidebar-active="true" @endif
                   class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-[10px] font-medium transition-all duration-150 {{ request()->routeIs('attendance.history') ? 'bg-white/20 text-white font-bold shadow-2xs' : 'text-teal-100 hover:text-white hover:bg-white/10' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('attendance.history') ? 'bg-white' : 'bg-teal-300' }}"></span>
                    <span>Riwayat Absen Saya</span>
                </a>

                <a href="{{ route('attendance.reports') }}" 
                   @if (request()->routeIs('attendance.reports*')) data-sidebar-active="true" @endif
                   class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-[10px] font-medium transition-all duration-150 {{ request()->routeIs('attendance.reports*') ? 'bg-white/20 text-white font-bold shadow-2xs' : 'text-teal-100 hover:text-white hover:bg-white/10' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('attendance.reports*') ? 'bg-white' : 'bg-teal-300' }}"></span>
                    <span>Laporan Absensi Karyawan</span>
                </a>
            </div>
        </div>
        @else
        <a href="{{ route('attendance.history') }}"
           @if (request()->routeIs('attendance.history')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.history') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $isHrdRole ? 'Riwayat Absen Saya' : 'Riwayat Absen' }}</span>
            @if (request()->routeIs('attendance.history'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endif

        <a href="{{ route('attendance.schedules') }}"
           @if (request()->routeIs('attendance.schedules', 'attendance.swaps*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.schedules', 'attendance.swaps*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ $isHrdRole ? 'Jadwal Kerja Saya' : 'Jadwal Kerja' }}</span>
            @if (request()->routeIs('attendance.schedules', 'attendance.swaps*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <a href="{{ route('attendance.leaves') }}"
           @if (request()->routeIs('attendance.leaves')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.leaves') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>{{ $isHrdRole ? 'Cuti & Izin Saya' : 'Cuti & Izin' }}</span>
            @if (request()->routeIs('attendance.leaves'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <a href="{{ route('attendance.deductions') }}"
           @if (request()->routeIs('attendance.deductions')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.deductions') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $isHrdRole ? 'Potongan Absen Saya' : 'Keuangan Absen' }}</span>
            @if (request()->routeIs('attendance.deductions'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        @if (! $isHrdRole)
        <a href="{{ route('attendance.leaderboard') }}"
           @if (request()->routeIs('attendance.leaderboard')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.leaderboard') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Leaderboard</span>
            @if (request()->routeIs('attendance.leaderboard'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endif
    </div>
</div>
