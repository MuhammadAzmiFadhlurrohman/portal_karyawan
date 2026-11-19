<div x-data="{ openTiketing: {{ $isTiketingActive ? 'true' : "sessionStorage.getItem('gomsn_sb_tiketing') === '1'" }} }">
    <button type="button" 
            @click="openTiketing = !openTiketing; sessionStorage.setItem('gomsn_sb_tiketing', openTiketing ? '1' : '0')"
            class="w-full pt-2.5 pb-1.5 px-3 flex items-center justify-between text-white/90 hover:text-white transition group cursor-pointer select-none rounded-xl hover:bg-white/10">
        <div class="flex items-center gap-2 flex-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                PEKERJAAN &amp; TIKETING
            </span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>
        <div class="flex items-center gap-1.5 ml-2">
            @if ($navAssignedTicketCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900 shadow-xs leading-none">
                    {{ $navAssignedTicketCount }}
                </span>
            @elseif ($isTiketingActive)
                <span class="w-1.5 h-1.5 rounded-full bg-[#18C4C4] shadow-xs"></span>
            @endif
            <svg class="w-3.5 h-3.5 text-white/70 group-hover:text-white transition-transform duration-200 ease-out" 
                 :class="openTiketing ? 'rotate-180' : ''" 
                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </button>

    <div id="sb-menu-tiketing" x-show="openTiketing" style="{{ $isTiketingActive ? '' : 'display: none;' }}" class="space-y-1 mt-1">
        <a href="{{ route('tickets.index') }}"
           @if (request()->routeIs('tickets.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('tickets.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Tiket Gangguan</span>
            </div>
            @if ($navAssignedTicketCount > 0)
                <div class="flex items-center gap-1.5 ml-auto">
                    <span class="w-2.5 h-2.5 rounded-full {{ request()->routeIs('tickets.*') ? 'bg-white' : 'bg-amber-400' }} shadow-xs animate-pulse"></span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('tickets.*') ? 'bg-white text-teal-800' : 'bg-amber-400 text-slate-900' }} shadow-xs leading-none">
                        {{ $navAssignedTicketCount }}
                    </span>
                </div>
            @elseif (request()->routeIs('tickets.*'))
                <span class="ml-auto w-2.5 h-2.5 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <a href="{{ route('gomsn.network.index') }}"
           @if (request()->routeIs('gomsn.network.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.network.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>OLT, PON & ODP</span>
            @if (request()->routeIs('gomsn.network.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <a href="{{ route('gomsn.analytics.kpi') }}"
           @if (request()->routeIs('gomsn.analytics.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.analytics.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Laporan KPI</span>
            @if (request()->routeIs('gomsn.analytics.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        @hasrole(['admin', 'super_admin'])
        <a href="{{ route('portal.sessions') }}"
           @if (request()->routeIs('portal.sessions')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('portal.sessions') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
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
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('portal.employees.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Data Karyawan</span>
            @if (request()->routeIs('portal.employees.*'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endif

        @can('portal.settings.manage')
        <a href="{{ route('portal.settings') }}"
           @if (request()->routeIs('portal.settings')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('portal.settings') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
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
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('portal.audit') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Audit Log</span>
            @if (request()->routeIs('portal.audit'))
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>
        @endcan
    </div>
</div>
