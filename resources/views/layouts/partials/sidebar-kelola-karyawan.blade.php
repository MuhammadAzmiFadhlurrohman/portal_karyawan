@if ($canViewEmployeeRecap)
@php
    $totalPendingKelola = ($navPendingLeaveHrdCount ?? 0) + ($navPendingLemburCount ?? 0);
@endphp
<div x-data="{ openKelolaKaryawan: {{ $isKelolaKaryawanActive ? 'true' : "sessionStorage.getItem('gomsn_sb_kelola_karyawan') === '1'" }} }">
    <button type="button" 
            @click="openKelolaKaryawan = !openKelolaKaryawan; sessionStorage.setItem('gomsn_sb_kelola_karyawan', openKelolaKaryawan ? '1' : '0')"
            class="w-full pt-2.5 pb-1.5 px-3 flex items-center justify-between text-white/90 hover:text-white transition group cursor-pointer select-none rounded-xl hover:bg-white/10">
        <div class="flex items-center gap-2 flex-1">
            <span class="text-[9px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                KELOLA KARYAWAN
            </span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>
        <div class="flex items-center gap-1.5 ml-2">
            @if ($totalPendingKelola > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs leading-none">
                    {{ $totalPendingKelola }}
                </span>
            @elseif ($isKelolaKaryawanActive)
                <span class="w-1.5 h-1.5 rounded-full bg-[#18C4C4] shadow-xs"></span>
            @endif
            <svg class="w-4 h-4 text-white/70 group-hover:text-white transition-transform duration-200 ease-out" 
                 :class="openKelolaKaryawan ? 'rotate-180' : ''" 
                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </button>

    <div id="sb-menu-kelola-karyawan" x-show="openKelolaKaryawan" style="{{ $isKelolaKaryawanActive ? '' : 'display: none;' }}" class="space-y-1 mt-1">
        <!-- 1. Data Karyawan -->
        <a href="{{ route('portal.employees.index') }}"
           @if (request()->routeIs('portal.employees.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('portal.employees.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Data Karyawan</span>
            @if (request()->routeIs('portal.employees.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <!-- 2. Absen Karyawan -->
        <a href="{{ route('attendance.employee-recap') }}"
           @if (request()->routeIs('attendance.employee-recap*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.employee-recap*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Absen Karyawan</span>
            @if (request()->routeIs('attendance.employee-recap*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        @if ($isHrdRole)
        <!-- 3. Pengajuan Absen (Cuti & Izin Karyawan) -->
        <a href="{{ route('attendance.leaves.approvals') }}"
           @if (request()->routeIs('attendance.leaves.approvals*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('attendance.leaves.approvals*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Pengajuan Absen</span>
            </div>
            @if (($navPendingLeaveHrdCount ?? 0) > 0)
            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                {{ $navPendingLeaveHrdCount }}
            </span>
            @elseif (request()->routeIs('attendance.leaves.approvals*'))
            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endif

        <!-- 4. Pengajuan Mobil -->
        <a href="{{ route('gomsn.car-usage.index') }}"
           @if (request()->routeIs('gomsn.car-usage.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.car-usage.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3.1C2.1 11.4 2 11.7 2 12.1V16c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
            <span>Pengajuan Mobil</span>
            @if (request()->routeIs('gomsn.car-usage.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        @if ($isHrdRole)
        <!-- 5. Lembur Karyawan -->
        <a href="{{ route('gomsn.overtime.index', ['view_mode' => 'karyawan']) }}"
           @if ($isOvertimeKaryawan) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ $isOvertimeKaryawan ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Lembur Karyawan</span>
            </div>
            @if ($navPendingLemburCount > 0)
            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                {{ $navPendingLemburCount }}
            </span>
            @elseif ($isOvertimeKaryawan)
            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <!-- 6. Leaderboard Disiplin Karyawan -->
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
@endif
