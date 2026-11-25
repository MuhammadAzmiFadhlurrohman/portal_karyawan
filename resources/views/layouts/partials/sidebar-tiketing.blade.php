@php
    $isOdpActive = (request()->routeIs('gomsn.network.*') && !request()->routeIs('gomsn.network.coverage')) && !request()->routeIs('coverage');
    $isCoverageActive = request()->routeIs('gomsn.network.coverage') || request()->routeIs('coverage');
@endphp
<div x-data="{ openTiketing: {{ $isTiketingActive ? 'true' : "sessionStorage.getItem('gomsn_sb_tiketing') === '1'" }} }">
    <button type="button" 
            @click="openTiketing = !openTiketing; sessionStorage.setItem('gomsn_sb_tiketing', openTiketing ? '1' : '0')"
            class="w-full pt-2.5 pb-1.5 px-3 flex items-center justify-between text-white/90 hover:text-white transition group cursor-pointer select-none rounded-xl hover:bg-white/10">
        <div class="flex items-center gap-2 flex-1">
            <span class="text-[9px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                PEKERJAAN &amp; TIKETING
            </span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>
        <div class="flex items-center gap-1.5 ml-2">
            @if ($navAssignedTicketCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-400 text-slate-900 shadow-xs leading-none">
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
        <!-- 1. Tiket Gangguan -->
        <a href="{{ route('tickets.index') }}"
           @if (request()->routeIs('tickets.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ request()->routeIs('tickets.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Tiket Gangguan</span>
            </div>
            @if ($navAssignedTicketCount > 0)
                <div class="flex items-center gap-1.5 ml-auto">
                    <span class="w-2.5 h-2.5 rounded-full {{ request()->routeIs('tickets.*') ? 'bg-white' : 'bg-amber-400' }} shadow-xs animate-pulse"></span>
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black {{ request()->routeIs('tickets.*') ? 'bg-white text-teal-800' : 'bg-amber-400 text-slate-900' }} shadow-xs leading-none">
                        {{ $navAssignedTicketCount }}
                    </span>
                </div>
            @elseif (request()->routeIs('tickets.*'))
                <span class="ml-auto w-2.5 h-2.5 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <!-- 2. OLT, PON & ODP -->
        <a href="{{ route('gomsn.network.index') }}"
           @if ($isOdpActive) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ $isOdpActive ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>OLT, PON & ODP</span>
            @if ($isOdpActive)
            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
            @endif
        </a>

        <!-- 3. Cek Coverage ODP (Menu Baru Di Bawah OLT, PON & ODP) -->
        <a href="{{ route('gomsn.network.coverage') }}"
           @if ($isCoverageActive) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-[11px] font-semibold transition-all duration-200 {{ $isCoverageActive ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Cek Coverage ODP</span>
            <span class="ml-auto flex items-center gap-1.5">
                <span class="px-1.5 py-0.2 rounded text-[8.5px] font-mono font-bold tracking-wider {{ $isCoverageActive ? 'bg-white/25 text-white' : 'bg-teal-400/20 text-teal-200' }}">FTTH</span>
                @if ($isCoverageActive)
                <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
                @endif
            </span>
        </a>
    </div>
</div>