<div x-data="{ openLainnya: {{ $isLainnyaActive ? 'true' : "sessionStorage.getItem('gomsn_sb_lainnya') === '1'" }} }">
    <button type="button"
            @click="openLainnya = !openLainnya; sessionStorage.setItem('gomsn_sb_lainnya', openLainnya ? '1' : '0')"
            class="w-full pt-2.5 pb-1.5 px-3 flex items-center justify-between text-white/90 hover:text-white transition group cursor-pointer select-none rounded-xl hover:bg-white/10">
        <div class="flex items-center gap-2 flex-1">
            <span class="text-[9px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                LAINNYA
            </span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>
        <div class="flex items-center gap-1.5 ml-2">
            @if ($isLainnyaActive)
                <span class="w-1.5 h-1.5 rounded-full bg-[#18C4C4] shadow-xs"></span>
            @endif
            <svg class="w-3.5 h-3.5 text-white/70 group-hover:text-white transition-transform duration-200 ease-out"
                 :class="openLainnya ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </button>

    <div id="sb-menu-lainnya" x-show="openLainnya" style="{{ $isLainnyaActive ? '' : 'display: none;' }}" class="space-y-1 mt-1">
        <a href="{{ route('gomsn.analytics.kpi') }}"
           @if (request()->routeIs('gomsn.analytics.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.analytics.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Laporan KPI</span>
            @if (request()->routeIs('gomsn.analytics.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        @hasrole(['admin', 'super_admin'])
        <a href="{{ route('portal.sessions') }}"
           @if (request()->routeIs('portal.sessions')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('portal.sessions') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Perangkat Saya</span>
            @if (request()->routeIs('portal.sessions'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endhasrole

        @if (auth()->user() && (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage')))
        <a href="{{ route('portal.employees.index') }}"
           @if (request()->routeIs('portal.employees.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('portal.employees.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Data Karyawan</span>
            @if (request()->routeIs('portal.employees.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endif

        @if (auth()->user() && (auth()->user()->hasRole(['super_admin', 'admin']) || (auth()->user()->can('attendance.employee.manage') && ! auth()->user()->hasRole('hrd'))))
        <a href="{{ route('portal.notifications.index') }}"
           @if (request()->routeIs('portal.notifications.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('portal.notifications.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span>Kirim Notifikasi</span>
            @if (request()->routeIs('portal.notifications.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endif

        @can('portal.settings.manage')
        <a href="{{ route('portal.settings') }}"
           @if (request()->routeIs('portal.settings')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('portal.settings') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Pengaturan</span>
            @if (request()->routeIs('portal.settings'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endcan

        @can('portal.audit.view')
        <a href="{{ route('portal.audit') }}"
           @if (request()->routeIs('portal.audit')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('portal.audit') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Audit Log</span>
            @if (request()->routeIs('portal.audit'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endcan
    </div>
</div>
