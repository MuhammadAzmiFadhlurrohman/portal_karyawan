<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#155E63">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-msn2.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logo/logo-msn2.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo/logo-msn2.png') }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <title>{{ $title ?? 'Portal Karyawan Terpadu' }} — PT MSN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @view-transition {
            navigation: auto;
        }
        #desktop-sidebar {
            view-transition-name: desktop-sidebar;
        }
        ::view-transition-old(desktop-sidebar),
        ::view-transition-new(desktop-sidebar) {
            animation: none;
            mix-blend-mode: normal;
        }
        img.object-cover[src*="/avatar"] {
            object-position: center top !important;
        }
        #main-content, #main-content > * {
            overflow-anchor: none;
        }
        .overflow-x-hidden {
            overflow-x: clip !important;
            overflow-y: visible !important;
        }
    </style>
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-900 font-sans antialiased" x-data="{ bottomSheetOpen: false }">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-[#155E63] focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">
        Langsung ke konten utama
    </a>

    @guest
    <!-- Guest Layout (Login, OTP, Password Change) -->
    @if (request()->routeIs('login'))
        <main id="main-content" class="min-h-screen w-full">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    @else
    <div class="min-h-screen flex flex-col justify-between">
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 py-3 px-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ route('login') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/logo/logo-msn2.png') }}" 
                         alt="PT Media Solusi Network" 
                         class="h-10 w-auto object-contain">
                    <div>
                        <span class="font-heading font-bold text-base text-[#1F2937] tracking-tight block leading-none">
                            PT MEDIA SOLUSI NETWORK
                        </span>
                        <span class="text-xs text-slate-500 font-medium block mt-0.5">
                            Enterprise Portal · Keamanan UU PDP
                        </span>
                    </div>
                </a>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span>Server Prod Normal</span>
                </div>
            </div>

        </header>

        <main id="main-content" class="flex-1 py-8 px-4 sm:px-6">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>

        <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200/60">
            Hak Cipta &copy; {{ date('Y') }} PT Media Solusi Network · Seluruh Hak Dilindungi UU PDP No. 27/2022
        </footer>
    </div>
    @endif
    @endguest

    @auth
    <!-- Authenticated Enterprise Shell (Dual-Panel Layout) -->
    <div class="min-h-screen flex flex-row">
        <!-- Left Sidebar Navigation (Desktop Only: hidden on mobile, md:flex) -->
        <aside id="desktop-sidebar"
               style="background: linear-gradient(180deg, #022836 0%, #053b47 22%, #094d5a 50%, #0c5a68 75%, #031e26 100%) !important;"
               class="hidden md:flex md:sticky top-0 inset-y-0 left-0 z-40 w-64 text-white flex-col justify-between h-screen shadow-2xl shrink-0 border-r border-[#14b8a6]/25 relative overflow-hidden select-none">
            
            <!-- Ambient Sculpted Waves & Elegant Fluid Lines (matching Page Header Theme) -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
                <!-- Ambient Radial Glow Spots -->
                <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-teal-400/10 blur-3xl pointer-events-none"></div>
                <div class="absolute top-1/3 -left-20 w-56 h-56 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-24 -right-16 w-60 h-60 rounded-full bg-teal-300/10 blur-3xl pointer-events-none"></div>

                <!-- Fluid Wave Glow & Line Geometry -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-40" viewBox="0 0 256 900" fill="none" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="sidebarWaveFill1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.08"/>
                            <stop offset="50%" stop-color="#2dd4bf" stop-opacity="0.14"/>
                            <stop offset="100%" stop-color="#5eead4" stop-opacity="0.03"/>
                        </linearGradient>
                        <linearGradient id="sidebarWaveFill2" x1="100%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#0e7490" stop-opacity="0.10"/>
                            <stop offset="70%" stop-color="#14b8a6" stop-opacity="0.06"/>
                            <stop offset="100%" stop-color="#042f2e" stop-opacity="0.0"/>
                        </linearGradient>
                        <linearGradient id="sidebarStrokeGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#5eead4" stop-opacity="0.55"/>
                            <stop offset="50%" stop-color="#2dd4bf" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#0d9488" stop-opacity="0.12"/>
                        </linearGradient>
                        <linearGradient id="sidebarStrokeGrad2" x1="0%" y1="100%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.08"/>
                            <stop offset="40%" stop-color="#5eead4" stop-opacity="0.40"/>
                            <stop offset="80%" stop-color="#99f6e4" stop-opacity="0.50"/>
                            <stop offset="100%" stop-color="#5eead4" stop-opacity="0.15"/>
                        </linearGradient>
                        <linearGradient id="sidebarStrokeGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.45"/>
                            <stop offset="50%" stop-color="#5eead4" stop-opacity="0.25"/>
                            <stop offset="100%" stop-color="#14b8a6" stop-opacity="0.08"/>
                        </linearGradient>
                    </defs>

                    <!-- Wave Ribbon 1 (Flowing down right edge) -->
                    <path d="M 120 0 C 200 180, 80 340, 210 520 C 290 640, 160 780, 256 900 L 256 0 Z" fill="url(#sidebarWaveFill1)"/>
                    
                    <!-- Wave Ribbon 2 (Flowing deep accent) -->
                    <path d="M 180 0 C 90 220, 240 400, 140 620 C 60 760, 180 840, 256 900 L 256 0 Z" fill="url(#sidebarWaveFill2)"/>

                    <!-- Flowing Vector Lines (Garis-garis menarik yang elegan & dinamis) -->
                    <path d="M 120 0 C 200 180, 80 340, 210 520 C 290 640, 160 780, 256 900" stroke="url(#sidebarStrokeGrad1)" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M 145 0 C 225 175, 105 345, 235 515 C 315 635, 185 785, 256 870" stroke="url(#sidebarStrokeGrad2)" stroke-width="1.2" stroke-dasharray="4 4" stroke-linecap="round"/>
                    <path d="M 90 0 C 170 190, 50 350, 180 530 C 260 650, 130 790, 230 900" stroke="url(#sidebarStrokeGrad3)" stroke-width="1.2" stroke-linecap="round"/>
                    <path d="M 210 0 C 120 230, 250 420, 160 640 C 90 780, 200 860, 256 900" stroke="url(#sidebarStrokeGrad2)" stroke-width="1.5" stroke-linecap="round"/>
                    
                    <!-- Subtle Secondary Flow Lines at bottom half -->
                    <path d="M 40 450 C 120 540, 70 660, 190 780 C 240 830, 230 870, 256 900" stroke="url(#sidebarStrokeGrad1)" stroke-width="1" stroke-linecap="round" opacity="0.6"/>
                </svg>

                <!-- Top & Edge Specular Gloss Highlights (matching Page Header top rim) -->
                <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 w-[1px] bg-gradient-to-b from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>
            </div>

            <!-- Sidebar Top: Brand Logo (Full White & Centered) -->
            <div class="p-4 sm:p-5 pb-3.5 border-b border-cyan-400/20 relative z-10">
                <div class="flex items-center justify-center w-full">
                    <a href="{{ route('portal.hub') }}" class="flex items-center justify-center group py-0.5 mx-auto">
                        <img src="{{ asset('assets/logo/logo1.png') }}" 
                             alt="PT Media Solusi Network" 
                             style="filter: brightness(0) invert(1) !important;"
                             class="h-8 sm:h-8.5 w-auto max-w-[160px] object-contain group-hover:scale-105 transition-transform duration-200 mx-auto">
                    </a>
                </div>
            </div>


            <!-- Sidebar Scrollable Nav Menu -->
            <nav id="desktop-sidebar-nav"
                 @scroll.passive="sessionStorage.setItem('gomsn_sb_scroll', $el.scrollTop)"
                 @click="if ($event.target.closest('a')) sessionStorage.setItem('gomsn_sb_scroll', $el.scrollTop)"
                 class="flex-1 overflow-y-auto px-3 py-3.5 space-y-4 sidebar-scroll text-sm relative z-10">
                
                <!-- Portal Hub Utama -->
                <div>
                    <a href="{{ route('portal.hub') }}"
                       @if (request()->routeIs('portal.hub')) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.hub') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white font-medium hover:bg-white/15' }}">
                        <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                        @if (request()->routeIs('portal.hub'))
                        <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                        @endif
                    </a>
                </div>

                <!-- Bagian 1: Pengajuan & Lembur (Non-Dropdown / Selalu Terbuka) -->
                @php
                    $navUser = auth()->user();
                    $navIsHrdOnly = $navUser && $navUser->hasRole('hrd') && ! $navUser->hasRole(['super_admin', 'admin']);
                    $navCanApprovePengajuan = $navUser && ! $navIsHrdOnly && ($navUser->can('gomsn.pengajuan.approve_atasan') || $navUser->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']));
                    $navCanApproveKoreksi = $navUser && ! $navIsHrdOnly && ($navUser->can('attendance.correction.approve') || $navUser->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']));
                    $navIsAtasan = $navUser && ($navCanApprovePengajuan || $navUser->can('gomsn.lembur.approve'));
                    $navIsFinance = $navUser && ($navUser->hasRole(['finance', 'admin', 'super_admin']) || $navUser->can('gomsn.pengajuan.approve_finance'));
                    $navPendingAtasanCount = 0;
                    $navPendingLemburCount = 0;
                    $navPendingKoreksiCount = 0;
                    $navLowStockCount = 0;
                    if ($navCanApprovePengajuan) {
                        $navQ = \App\Models\Pengajuan::where('status', 'menunggu_atasan')->where('user_id', '!=', $navUser->id);
                        if (! $navUser->hasRole(['super_admin', 'admin'])) {
                            $navQ->where('atasan_id', $navUser->id);
                        }
                        $navPendingAtasanCount = $navQ->count();
                    }
                    if ($navIsAtasan) {
                        $navLQ = \App\Models\Lembur::where('status', 'pending')->where('user_id', '!=', $navUser->id);
                        if (! $navUser->hasRole(['super_admin', 'admin', 'hrd'])) {
                            $navLQ->where(function ($q) use ($navUser) {
                                $q->where('atasan_id', $navUser->id);
                                if (! empty($navUser->division)) {
                                    $q->orWhereHas('user', function ($qu) use ($navUser) {
                                        $qu->whereRaw('LOWER(division) = ?', [strtolower($navUser->division)]);
                                    });
                                }
                            });
                        }
                        $navPendingLemburCount = $navLQ->count();
                    }
                    if ($navCanApproveKoreksi) {
                        $navCQ = \App\Models\AttendanceCorrection::whereIn('status', ['pending_supervisor', 'pending_hrd'])->where('user_id', '!=', $navUser->id);
                        if (! $navUser->hasRole(['super_admin', 'admin'])) {
                            if (! empty($navUser->division)) {
                                $navCQ->whereHas('user', function ($qu) use ($navUser) {
                                    $qu->whereRaw('LOWER(division) = ?', [strtolower($navUser->division)]);
                                });
                            }
                        }
                        $navPendingKoreksiCount = $navCQ->count();
                    }
                    if ($navIsFinance) {
                        $navLowStockCount = \App\Models\MasterMaterial::whereColumn('stok_tersedia', '<=', 'min_stok')->count();
                    }
                    $isPengajuanActive = request()->routeIs('gomsn.pengajuan.*') || request()->routeIs('attendance.corrections.*') || request()->routeIs('gomsn.overtime.*') || request()->routeIs('gomsn.inventory.*');
                    $totalPengajuanBadges = $navPendingAtasanCount + $navPendingLemburCount + $navPendingKoreksiCount + $navLowStockCount;
                @endphp
                <div class="space-y-1">
                    <div class="pt-2.5 pb-1 px-3 flex items-center justify-between text-white/90 select-none">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                                PENGAJUAN &amp; LEMBUR
                            </span>
                            <div class="h-px flex-1 bg-white/15"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <!-- 1. Pengajuan Saya (Mandiri) -->
                        <a href="{{ route('gomsn.pengajuan.index') }}"
                           @if (request()->routeIs('gomsn.pengajuan.index') || request()->routeIs('gomsn.pengajuan.create') || (request()->routeIs('gomsn.pengajuan.show') && ! request()->routeIs('gomsn.pengajuan.approvals'))) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ (request()->routeIs('gomsn.pengajuan.index') || request()->routeIs('gomsn.pengajuan.create') || (request()->routeIs('gomsn.pengajuan.show') && ! request()->routeIs('gomsn.pengajuan.approvals'))) ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>{{ $navCanApprovePengajuan ? 'Pengajuan Saya' : 'Pengajuan' }}</span>
                            @if (request()->routeIs('gomsn.pengajuan.index') || request()->routeIs('gomsn.pengajuan.create'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <!-- 2. Persetujuan Karyawan (Khusus Atasan — Tidak Muncul di Role HRD) -->
                        @if ($navCanApprovePengajuan)
                        <a href="{{ route('gomsn.pengajuan.approvals') }}"
                           @if (request()->routeIs('gomsn.pengajuan.approvals')) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.pengajuan.approvals') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('gomsn.pengajuan.approvals') ? 'text-white' : 'text-amber-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Persetujuan Karyawan</span>
                            </div>
                            @if ($navPendingAtasanCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $navPendingAtasanCount }}
                            </span>
                            @endif
                        </a>
                        @endif

                        <!-- 3. Pengajuan Absen (Khusus Atasan & Admin — Tidak Muncul di Role HRD) -->
                        @if ($navCanApproveKoreksi)
                        <a href="{{ route('attendance.corrections.approvals') }}"
                           @if (request()->routeIs('attendance.corrections.approvals')) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.corrections.approvals') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('attendance.corrections.approvals') ? 'text-white' : 'text-emerald-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>Pengajuan Absen</span>
                            </div>
                            @if ($navPendingKoreksiCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $navPendingKoreksiCount }}
                            </span>
                            @endif
                        </a>
                        @endif

                        <a href="{{ route('gomsn.overtime.index') }}"
                           @if (request()->routeIs('gomsn.overtime.*')) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.overtime.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $navIsAtasan ? 'Persetujuan Lembur' : 'Lembur' }}</span>
                            </div>
                            @if ($navPendingLemburCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $navPendingLemburCount }}
                            </span>
                            @elseif (request()->routeIs('gomsn.overtime.*'))
                            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <!-- 4. Stok Barang Kantor (Khusus Finance & Admin) -->
                        @if ($navIsFinance)
                        <a href="{{ route('gomsn.inventory.index') }}"
                           @if (request()->routeIs('gomsn.inventory.*')) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.inventory.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Stok Barang</span>
                            </div>
                            @if ($navLowStockCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-400 text-slate-900 shadow-xs" title="{{ $navLowStockCount }} jenis barang butuh restock">
                                {{ $navLowStockCount }}
                            </span>
                            @elseif (request()->routeIs('gomsn.inventory.*'))
                            <span class="w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Bagian 2: Absensi & Cuti (Dropdown with Persistent State) -->
                @php
                    $sidebarUser = auth()->user();
                    $canViewReports = $sidebarUser && $sidebarUser->hasRole(['super_admin', 'admin']);
                    $isHrdRole = $sidebarUser && $sidebarUser->hasRole('hrd') && ! $sidebarUser->hasRole(['super_admin', 'admin']);
                    $isAbsensiActive = request()->routeIs('attendance.*') && ! request()->routeIs('attendance.corrections.approvals');
                    $isRiwayatActive = request()->routeIs('attendance.history', 'attendance.reports*');
                @endphp
                <div x-data="{ openAbsensi: {{ $isAbsensiActive ? 'true' : "sessionStorage.getItem('gomsn_sb_absensi') === '1'" }} }">
                    <button type="button" 
                            @click="openAbsensi = !openAbsensi; sessionStorage.setItem('gomsn_sb_absensi', openAbsensi ? '1' : '0')"
                            class="w-full pt-2.5 pb-1.5 px-3 flex items-center justify-between text-white/90 hover:text-white transition group cursor-pointer select-none rounded-xl hover:bg-white/10">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-200/90 font-mono">
                                ABSENSI &amp; CUTI
                            </span>
                            <div class="h-px flex-1 bg-white/15"></div>
                        </div>
                        <div class="flex items-center gap-1.5 ml-2">
                            @if ($isAbsensiActive)
                                <span class="w-1.5 h-1.5 rounded-full bg-[#18C4C4] shadow-xs"></span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-white/70 group-hover:text-white transition-transform duration-200 ease-out" 
                                 :class="openAbsensi ? 'rotate-180' : ''" 
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div id="sb-menu-absensi" x-show="openAbsensi" style="{{ $isAbsensiActive ? '' : 'display: none;' }}" class="space-y-1 mt-1">
                        <a href="{{ route('attendance.index') }}"
                           @if (request()->routeIs('attendance.index')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.index') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Absen</span>
                            @if (request()->routeIs('attendance.index'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        @if ($isHrdRole)
                        <!-- Data Karyawan (Khusus HRD di Menu Absensi) -->
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

                        @if ($canViewReports)
                        <!-- Riwayat & Laporan Absensi (Dropdown Sub-Menu untuk Admin/HRD) -->
                        <div x-data="{ openRiwayat: {{ $isRiwayatActive ? 'true' : "sessionStorage.getItem('gomsn_sb_riwayat') === '1'" }} }" class="space-y-1">
                            <button type="button" 
                                    @click="openRiwayat = !openRiwayat; sessionStorage.setItem('gomsn_sb_riwayat', openRiwayat ? '1' : '0')"
                                    @if ($isRiwayatActive) style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 cursor-pointer {{ $isRiwayatActive ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="truncate">Riwayat &amp; Laporan</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if ($isRiwayatActive)
                                    <span class="w-1.5 h-1.5 rounded-full bg-white shadow-xs"></span>
                                    @endif
                                    <svg class="w-3.5 h-3.5 shrink-0 text-white/80 transition-transform duration-200 ease-out" 
                                         :class="openRiwayat ? 'rotate-180' : ''" 
                                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div id="sb-menu-riwayat" x-show="openRiwayat" style="{{ $isRiwayatActive ? '' : 'display: none;' }}" class="pl-4 pr-1 space-y-1 pt-0.5">
                                <a href="{{ route('attendance.history') }}" 
                                   @if (request()->routeIs('attendance.history')) data-sidebar-active="true" @endif
                                   class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('attendance.history') ? 'bg-white/20 text-white font-bold shadow-2xs' : 'text-teal-100 hover:text-white hover:bg-white/10' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('attendance.history') ? 'bg-white' : 'bg-teal-300' }}"></span>
                                    <span>Riwayat Absen Saya</span>
                                </a>

                                <a href="{{ route('attendance.reports') }}" 
                                   @if (request()->routeIs('attendance.reports*')) data-sidebar-active="true" @endif
                                   class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs transition-all duration-150 {{ request()->routeIs('attendance.reports*') ? 'bg-white/20 text-white font-bold shadow-2xs' : 'text-teal-100 hover:text-white hover:bg-white/10' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('attendance.reports*') ? 'bg-white' : 'bg-teal-300' }}"></span>
                                    <span>Laporan Absensi Karyawan</span>
                                </a>
                            </div>
                        </div>
                        @else
                        <a href="{{ route('attendance.history') }}"
                           @if (request()->routeIs('attendance.history')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.history') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Riwayat Absen</span>
                            @if (request()->routeIs('attendance.history'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>
                        @endif

                        <a href="{{ route('attendance.schedules') }}"
                           @if (request()->routeIs('attendance.schedules', 'attendance.swaps*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.schedules', 'attendance.swaps*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Jadwal Kerja</span>
                            @if (request()->routeIs('attendance.schedules', 'attendance.swaps*'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <a href="{{ route('attendance.leaves') }}"
                           @if (request()->routeIs('attendance.leaves')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.leaves') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Cuti &amp; Izin</span>
                            @if (request()->routeIs('attendance.leaves'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <a href="{{ route('attendance.deductions') }}"
                           @if (request()->routeIs('attendance.deductions')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.deductions') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Keuangan Absen</span>
                            @if (request()->routeIs('attendance.deductions'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <a href="{{ route('attendance.leaderboard') }}"
                           @if (request()->routeIs('attendance.leaderboard')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('attendance.leaderboard') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Leaderboard</span>
                            @if (request()->routeIs('attendance.leaderboard'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>
                    </div>
                </div>


                <!-- Bagian 3: Pekerjaan & Tiketing (Dropdown with Persistent State) -->
                @php
                    $isTiketingActive = request()->routeIs('tickets.*') || request()->routeIs('gomsn.tasks.*') || request()->routeIs('gomsn.network.*') || request()->routeIs('gomsn.analytics.*') || request()->routeIs('portal.sessions') || request()->routeIs('portal.employees.*') || request()->routeIs('portal.settings') || request()->routeIs('portal.audit');
                @endphp
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
                            @if ($isTiketingActive)
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
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('tickets.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <span>Tiket Gangguan</span>
                            @if (request()->routeIs('tickets.*'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
                            @endif
                        </a>

                        <a href="{{ route('gomsn.tasks.index') }}"
                           @if (request()->routeIs('gomsn.tasks.*')) data-sidebar-active="true" style="background: linear-gradient(90deg, #18C4C4 0%, #0D9488 100%) !important;" @endif
                           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('gomsn.tasks.*') ? 'text-white font-bold shadow-md border border-white/25' : 'text-white/90 font-medium hover:bg-white/10 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Tugas Lapangan</span>
                            @if (request()->routeIs('gomsn.tasks.*'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-white shadow-xs"></span>
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
                            <span>Radius Absen</span>
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
            </nav>

            <!-- Instant Synchronous Sidebar State & Scroll Restoration (Zero Jump / Zero Refresh) -->
            <script>
                (function () {
                    try {
                        var nav = document.getElementById('desktop-sidebar-nav');
                        if (!nav) return;

                        @if ($isAbsensiActive)
                            sessionStorage.setItem('gomsn_sb_absensi', '1');
                        @endif
                        @if ($isRiwayatActive)
                            sessionStorage.setItem('gomsn_sb_riwayat', '1');
                        @endif
                        @if ($isTiketingActive)
                            sessionStorage.setItem('gomsn_sb_tiketing', '1');
                        @endif

                        var elAbsensi = document.getElementById('sb-menu-absensi');
                        if (elAbsensi && sessionStorage.getItem('gomsn_sb_absensi') === '1') {
                            elAbsensi.style.display = '';
                        }
                        var elRiwayat = document.getElementById('sb-menu-riwayat');
                        if (elRiwayat && sessionStorage.getItem('gomsn_sb_riwayat') === '1') {
                            elRiwayat.style.display = '';
                        }
                        var elTiketing = document.getElementById('sb-menu-tiketing');
                        if (elTiketing && sessionStorage.getItem('gomsn_sb_tiketing') === '1') {
                            elTiketing.style.display = '';
                        }

                        var savedScroll = sessionStorage.getItem('gomsn_sb_scroll');
                        if (savedScroll !== null) {
                            nav.scrollTop = parseInt(savedScroll, 10) || 0;
                        }
                    } catch (e) {}
                })();
            </script>

            <!-- Sidebar User Profile Footer (Blended Metallic Ocean Teal) -->
            <div class="p-3.5 border-t border-cyan-400/20 backdrop-blur-md relative z-10" style="background: linear-gradient(180deg, rgba(3, 30, 38, 0.90) 0%, rgba(2, 20, 26, 0.98) 100%) !important;">
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-[#5eead4]/40 to-transparent pointer-events-none"></div>
                <div class="flex items-center justify-between gap-2.5 relative z-10">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-[#18C4C4] to-[#0D9488] border border-white/20 flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-sm">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[13px] font-bold text-white block truncate leading-tight">
                                {{ auth()->user()->name }}
                            </span>
                            <span class="text-[11px] text-teal-100/90 block truncate font-mono mt-0.5">
                                {{ auth()->user()->employee_code }} · {{ auth()->user()->getRoleNames()->first() ?? 'Staff' }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Keluar dari Portal" class="p-2 rounded-xl text-white/90 hover:text-rose-300 hover:bg-white/10 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Right Main Panel -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
            <!-- Mobile Fixed Header (Sculpted Metallic Teal Bar - Always Pinned on Scroll) -->
            <div class="md:hidden fixed top-0 inset-x-0 z-50 pt-2 px-3 pb-1.5 bg-slate-50/95 backdrop-blur-md select-none">
                <div class="relative h-[48px] w-full rounded-[22px] flex items-center justify-between px-3.5 border border-[#1cd8d2]/35 shadow-[0_4px_18px_rgba(4,40,44,0.38)]"
                     style="background: linear-gradient(100deg, #07383c 0%, #0c4d53 28%, #105c64 52%, #0a4449 82%, #063135 100%);">
                    
                    <!-- Decorative Background & Highlights Layer (Clipped to Pill Corners) -->
                    <div class="absolute inset-0 rounded-[22px] overflow-hidden pointer-events-none">
                        <!-- Specular Highlights (Top & Bottom Gloss Rims) -->
                        <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/80 to-transparent"></div>
                        <div class="absolute inset-x-3 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/45 to-transparent"></div>
                        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.10] via-transparent to-black/[0.20]"></div>

                        <!-- Sculpted Aerodynamic Wave SVG on Right Pocket -->
                        <svg class="absolute right-0 top-0 h-full w-[170px]" viewBox="0 0 170 48" fill="none" preserveAspectRatio="none">
                            <path d="M 45 0 C 35 14 18 28 0 38 L 0 48 L 170 48 L 170 0 Z" fill="url(#mobilePocketGrad)" opacity="0.88"/>
                            <path d="M 45 0 C 35 14 18 28 0 38 L 0 48" stroke="url(#mobileHighlightStroke)" stroke-width="2" stroke-linecap="round"/>
                            <defs>
                                <linearGradient id="mobilePocketGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#042023"/>
                                    <stop offset="50%" stop-color="#072d31"/>
                                    <stop offset="100%" stop-color="#051f22"/>
                                </linearGradient>
                                <linearGradient id="mobileHighlightStroke" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#5eead4" stop-opacity="0.9"/>
                                    <stop offset="50%" stop-color="#2dd4bf" stop-opacity="1"/>
                                    <stop offset="100%" stop-color="#0d9488" stop-opacity="0.4"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <!-- Left: White GoMSN Logo -->
                    <a href="{{ route('portal.hub') }}" class="relative z-10 flex items-center group py-0.5" title="GoMSN Portal">
                        <img src="{{ asset('assets/logo/logo1.png') }}" 
                             alt="GoMSN" 
                             style="filter: brightness(0) invert(1) !important;"
                             class="h-6 sm:h-7 w-auto max-w-[130px] object-contain drop-shadow-[0_1px_2px_rgba(0,0,0,0.6)]">
                    </a>

                    <!-- Right: Actions inside sculpted bezel (Solid White Ringed Avatar + Chevron) -->
                    <div class="relative z-10 flex items-center gap-2">
                        <!-- Avatar with Solid White Ring + Name & Division + Chevron Down (Dropdown Menu) -->
                        <div class="relative" x-data="{ mobileUserMenuOpen: false }" @click.outside="mobileUserMenuOpen = false">
                            <button @click="mobileUserMenuOpen = !mobileUserMenuOpen"
                                    type="button"
                                    aria-haspopup="true"
                                    :aria-expanded="mobileUserMenuOpen"
                                    class="flex items-center gap-2 cursor-pointer focus:outline-none select-none pl-1">
                                
                                <!-- User Name & Division Beside Profile (User Request 1) -->
                                <div class="text-right leading-tight max-w-[105px] sm:max-w-[150px] min-w-0">
                                    <span class="block text-xs font-bold text-white truncate drop-shadow-xs">{{ auth()->user()->name }}</span>
                                    <span class="block text-[10px] text-teal-200/90 font-medium truncate">{{ auth()->user()->division ?: (auth()->user()->employee_code ?: 'MSN') }}</span>
                                </div>

                                <!-- Avatar circle with solid 2px white border ring -->
                                <div class="relative w-[35px] h-[35px] shrink-0">
                                    @if (auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" 
                                             alt="" 
                                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                             class="w-[35px] h-[35px] rounded-full border-2 border-white object-cover shadow-md">
                                        <div style="display: none;" class="w-[35px] h-[35px] rounded-full border-2 border-white bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white items-center justify-center font-bold text-xs shadow-md shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @else
                                        <div class="w-[35px] h-[35px] rounded-full border-2 border-white bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white flex items-center justify-center font-bold text-xs shadow-md shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Pure White Down Chevron -->
                                <svg class="w-3.5 h-3.5 text-white transition-transform duration-200 shrink-0"
                                     :class="mobileUserMenuOpen ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Mobile User Dropdown Menu -->
                            <div x-show="mobileUserMenuOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                 class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 z-50 divide-y divide-slate-100 select-auto">
                                
                                <!-- Header Ringkasan Akun -->
                                <div class="px-4 py-3 flex items-center gap-3">
                                    @if (auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" 
                                             alt="" 
                                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                             class="h-9 w-9 rounded-xl object-cover border border-slate-200 shadow-xs shrink-0">
                                        <div style="display: none;" class="h-9 w-9 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @else
                                        <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                            {{ auth()->user()->initials }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-[10px] text-teal-700 font-mono font-medium truncate">{{ auth()->user()->employee_code }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->division ?: 'MSN Member' }}</p>
                                    </div>
                                </div>

                                <!-- Menu Tautan -->
                                <div class="py-1">
                                    <a href="{{ route('portal.profile') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>Halaman Profile</span>
                                    </a>

                                    <a href="{{ route('gomsn.network.index') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <span>Jaringan OLT, PON &amp; ODP</span>
                                    </a>

                                    @if (auth()->user() && auth()->user()->hasRole(['super_admin', 'admin']))
                                    <a href="{{ route('portal.sessions') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Perangkat &amp; Sesi Login</span>
                                    </a>
                                    @endif

                                    @if (auth()->user() && (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage')))
                                    <a href="{{ route('portal.employees.index') }}" 
                                       class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>Data Karyawan</span>
                                    </a>
                                    @endif
                                </div>

                                <!-- Logout Form & Button -->
                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>Logout / Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spacer for Fixed Mobile Header so content is never covered -->
            <div class="md:hidden h-[58px] shrink-0" aria-hidden="true"></div>

            <!-- Desktop Header (Pristine Clean White with Elegant Visual Highlights) -->
            <header class="hidden md:flex sticky top-0 z-30 bg-white/90 text-slate-900 backdrop-blur-xl border-b border-slate-200/80 h-16 items-center justify-between px-6 sm:px-8 gap-4 select-none relative transition-colors shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <!-- Soft Micro-Pattern & Edge Accent in Background (Still Crisp Pure White) -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden z-0 opacity-40">
                    <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:18px_18px] [mask-image:radial-gradient(ellipse_60%_100%_at_50%_0%,#000_60%,transparent_100%)]"></div>
                    <div class="absolute inset-x-0 bottom-0 h-[1.5px] bg-gradient-to-r from-transparent via-teal-500/25 to-transparent"></div>
                </div>

                <!-- Left: Live Date & Enterprise Status Badge -->
                <div class="flex items-center gap-3 relative z-10 min-w-0">
                    <!-- Date Pill with Calendar Icon -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50/90 border border-slate-200/80 text-slate-700 text-xs font-semibold shadow-2xs">
                        <div class="w-5 h-5 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="tracking-tight text-slate-800">
                            {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </span>
                    </div>

                    <!-- Online Status Pill -->
                    <div class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50/90 border border-emerald-200/70 text-emerald-700 text-[11px] font-semibold">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Portal Aktif</span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3 shrink-0 relative z-10">
                    <!-- Shift Pill (Professional Badging) -->
                    <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50/90 hover:bg-slate-100/90 px-3 py-1.5 text-xs font-semibold text-slate-700 border border-slate-200/80 shadow-2xs transition">
                        <div class="w-4.5 h-4.5 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-slate-800 font-bold">Shift Pagi</span>
                        <span class="text-slate-400 font-normal">·</span>
                        <span class="text-teal-700 font-mono text-[11px]">WIB</span>
                    </div>

                    <!-- Divider -->
                    <div class="h-6 w-px bg-slate-200"></div>

                    <!-- Profile Dropdown (Card Pill) -->
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                type="button"
                                class="flex items-center gap-2.5 pl-1.5 pr-3 py-1 rounded-2xl bg-slate-50/90 hover:bg-white border border-slate-200/80 hover:border-teal-300 hover:shadow-xs transition-all duration-200 cursor-pointer group focus:outline-none">
                            <!-- Avatar with online indicator ring -->
                            <div class="relative shrink-0">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" 
                                         alt="" 
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                         class="w-8 h-8 rounded-xl object-cover border border-slate-200 shadow-2xs ring-2 ring-white">
                                    <div style="display: none;" class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold text-xs shadow-2xs ring-2 ring-white shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-xs shadow-2xs ring-2 ring-white">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                            </div>

                            <!-- User Name & Division -->
                            <div class="text-left hidden sm:block min-w-0">
                                <span class="text-xs font-bold text-slate-800 block truncate leading-tight group-hover:text-teal-700 transition-colors max-w-[140px]">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="text-[10px] text-slate-400 block truncate leading-tight font-medium mt-0.5 max-w-[140px]">
                                    {{ auth()->user()->division ?: (auth()->user()->getRoleNames()->first() ? ucfirst(auth()->user()->getRoleNames()->first()) : 'MSN') }}
                                </span>
                            </div>

                            <!-- Dropdown Chevron Icon -->
                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-teal-600 transition-transform duration-200 ml-0.5"
                                 :class="userMenuOpen ? 'rotate-180 text-teal-600' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu Box -->
                        <div x-show="userMenuOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2.5 w-64 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 z-50 divide-y divide-slate-100">
                            
                            <!-- Header Ringkasan Akun -->
                            <div class="px-4 py-3 flex items-center gap-3">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" 
                                         alt="" 
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                         class="h-10 w-10 rounded-xl object-cover border border-slate-200 shadow-xs shrink-0">
                                    <div style="display: none;" class="h-10 w-10 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-teal-700 font-mono font-medium truncate">{{ auth()->user()->employee_code }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->division ?: 'MSN Member' }}</p>
                                </div>
                            </div>

                            <!-- Menu Tautan -->
                            <div class="py-1">
                                <a href="{{ route('portal.profile') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Halaman Profile</span>
                                </a>

                                <a href="{{ route('gomsn.network.index') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition-colors">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span>Jaringan OLT, PON &amp; ODP</span>
                                </a>

                                @if (auth()->user() && auth()->user()->hasRole(['super_admin', 'admin']))
                                <a href="{{ route('portal.sessions') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Perangkat &amp; Sesi Login</span>
                                </a>
                                @endif
                            </div>

                            <!-- Logout Form & Button -->
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Logout / Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Toast / Status Banner -->
            @if (session('status'))
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                <div role="alert" class="rounded-xl bg-emerald-50 border border-emerald-300 px-4 py-3 flex items-center justify-between gap-3 text-emerald-900 shadow-xs">
                    <div class="flex items-center gap-2.5 text-sm font-medium">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            </div>

            @endif

            @if (isset($errors) && $errors->any())
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                <div role="alert" class="rounded-xl bg-red-50 border border-red-300 px-4 py-3 text-red-900 shadow-xs">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Periksa kembali input Anda:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @endif

            <!-- Main Page Slot (Compact Padding) -->
            <main id="main-content" class="flex-1 p-3.5 sm:p-5 lg:p-6 w-full pb-24 md:pb-6">
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MOBILE BOTTOM NAVIGATION BAR (CENTER ELEVATED HOME WITH CURVED DOME) -->
    <!-- ============================================================== -->
    @php
        $navTabActive = 'home';
        if (request()->routeIs('attendance.*')) {
            $navTabActive = 'presensi';
        } elseif (request()->routeIs('gomsn.pengajuan.*')) {
            $navTabActive = 'pengajuan';
        } elseif (request()->routeIs('gomsn.tasks.*') || request()->routeIs('tickets.*')) {
            $navTabActive = 'tugas';
        } elseif (request()->routeIs('portal.hub') || request()->routeIs('dashboard')) {
            $navTabActive = 'home';
        }
    @endphp

    <div class="md:hidden fixed bottom-2 inset-x-3 sm:inset-x-6 max-w-md mx-auto z-40 pointer-events-none pt-4"
         x-data="{
             currentTab: '{{ $navTabActive }}',
             get isMenu() { return bottomSheetOpen; },
             isActive(tab) {
                 if (this.isMenu) return tab === 'menu';
                 return this.currentTab === tab;
             }
         }">
        <nav aria-label="Navigasi Cepat Mobile" 
             class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-[20px] shadow-[0_8px_25px_rgba(15,91,98,0.10)] border border-teal-100/80 h-[52px] px-1 flex items-center justify-around relative">
            
            <!-- Soft Ambient Teal Wave Background on Right Side (matching reference image) -->
            <div class="absolute right-0 top-0 bottom-0 w-2/5 rounded-r-[20px] bg-gradient-to-l from-teal-500/10 via-teal-200/5 to-transparent pointer-events-none"></div>
            <svg class="absolute right-0 bottom-0 h-full w-36 pointer-events-none opacity-25" viewBox="0 0 144 52" fill="none">
                <path d="M 0 52 C 45 20 85 45 144 12 L 144 52 Z" fill="#14b8a6"/>
            </svg>

            <!-- 1. PRESENSI -->
            <a href="{{ route('attendance.index') }}" 
               @click="currentTab = 'presensi'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10">
                <svg class="w-[20px] h-[20px] transition-colors" 
                     :class="isActive('presensi') ? 'text-[#155E63]' : 'text-[#195a60] group-hover:text-[#114347]'"
                     fill="none" stroke="currentColor" :stroke-width="isActive('presensi') ? '2.1' : '1.85'" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-[10px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('presensi') ? 'font-bold text-[#155E63]' : 'font-medium text-slate-700 group-hover:text-slate-900'">
                    Presensi
                </span>
                <div class="h-1 flex items-center justify-center mt-0.5">
                    <span x-show="isActive('presensi')" class="w-4 h-[2px] bg-[#155E63] rounded-full" style="display: none;"></span>
                </div>
            </a>

            <!-- 2. PENGAJUAN -->
            <a href="{{ $navCanApprovePengajuan ? route('gomsn.pengajuan.approvals') : route('gomsn.pengajuan.index') }}" 
               @click="currentTab = 'pengajuan'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10">
                <div class="relative">
                    <svg class="w-[20px] h-[20px] transition-colors" 
                         :class="isActive('pengajuan') ? 'text-[#155E63]' : 'text-[#195a60] group-hover:text-[#114347]'"
                         fill="none" stroke="currentColor" :stroke-width="isActive('pengajuan') ? '2.1' : '1.85'" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    @if (!empty($navPendingAtasanCount) && $navPendingAtasanCount > 0)
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('pengajuan') ? 'font-bold text-[#155E63]' : 'font-medium text-slate-700 group-hover:text-slate-900'">
                    Pengajuan
                </span>
                <div class="h-1 flex items-center justify-center mt-0.5">
                    <span x-show="isActive('pengajuan')" class="w-4 h-[2px] bg-[#155E63] rounded-full" style="display: none;"></span>
                </div>
            </a>

            <!-- 3. HOME (CENTER HERO - ORGANIC ARCH MOUND + 3D GLOSSY TEAL ORB) -->
            <a href="{{ route('portal.hub') }}" 
               @click="currentTab = 'home'"
               class="flex-1 h-full flex flex-col items-center justify-end relative group select-none cursor-pointer pb-1 z-20">
                
                <!-- Seamless Arch Mound Pedestal (Curves smoothly out of the navbar) -->
                <svg class="absolute -top-[16px] left-1/2 -translate-x-1/2 w-[76px] h-[18px] pointer-events-none drop-shadow-[0_-2px_2px_rgba(0,0,0,0.02)]" viewBox="0 0 76 18">
                    <!-- Mound Fill connecting to navbar body -->
                    <path d="M 0,18 C 16,18 24,1 38,1 C 52,1 60,18 76,18 L 76,22 L 0,22 Z" fill="#ffffff"/>
                    <!-- Crisp Top Stroke connecting with navbar border -->
                    <path d="M 0,18 C 16,18 24,1 38,1 C 52,1 60,18 76,18" fill="none" stroke="rgba(153, 246, 228, 0.75)" stroke-width="1.3"/>
                </svg>

                <!-- Elevated 3D Glossy Sphere Button -->
                <div class="absolute -top-3.5 w-[44px] h-[44px] rounded-full flex items-center justify-center text-white border-[2.5px] border-white shadow-[0_5px_15px_rgba(9,64,68,0.32)] transition-transform duration-200 transform group-hover:scale-105 active:scale-95 overflow-hidden"
                     style="background: radial-gradient(circle at 50% 28%, #14999f 0%, #0d636a 55%, #073f44 100%) !important;">
                    
                    <!-- Specular Curved Gloss Arc (Bubble glass reflection on top) -->
                    <div class="absolute top-1 inset-x-2 h-[16px] rounded-full bg-gradient-to-b from-white/60 via-white/20 to-transparent pointer-events-none"></div>

                    <!-- Prominent House Icon (Peaked roof with rounded arch door) -->
                    <svg class="w-[23px] h-[23px] text-white relative z-10 drop-shadow-[0_1px_2px_rgba(0,0,0,0.25)]" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>

                <!-- Label & Indicator Line -->
                <span class="text-[10px] tracking-tight leading-none transition-colors"
                      :class="isActive('home') ? 'font-bold text-[#0d595f]' : 'font-medium text-slate-700 group-hover:text-[#0d595f]'">
                    Home
                </span>
                <div class="h-1 flex items-center justify-center mt-0.5">
                    <span x-show="isActive('home')" class="w-6 h-[2.5px] bg-gradient-to-r from-[#14b8a6] via-[#0d7379] to-[#14b8a6] rounded-full"></span>
                </div>
            </a>

            <!-- 4. TUGAS -->
            <a href="{{ route('gomsn.tasks.index') }}" 
               @click="currentTab = 'tugas'"
               class="flex-1 h-full flex flex-col items-center justify-center relative group select-none cursor-pointer z-10">
                <svg class="w-[20px] h-[20px] transition-colors" 
                     :class="isActive('tugas') ? 'text-[#155E63]' : 'text-[#195a60] group-hover:text-[#114347]'"
                     fill="none" stroke="currentColor" :stroke-width="isActive('tugas') ? '2.1' : '1.85'" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 6.5l1.5 1.5 3-3m-4.5 4.5l1.5 1.5 3-3"/>
                </svg>
                <span class="text-[10px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('tugas') ? 'font-bold text-[#155E63]' : 'font-medium text-slate-700 group-hover:text-slate-900'">
                    Tugas
                </span>
                <div class="h-1 flex items-center justify-center mt-0.5">
                    <span x-show="isActive('tugas')" class="w-4 h-[2px] bg-[#155E63] rounded-full" style="display: none;"></span>
                </div>
            </a>

            <!-- 5. MENU (TOGGLE BOTTOM SHEET) -->
            <button @click="bottomSheetOpen = !bottomSheetOpen" 
                    type="button" 
                    class="flex-1 h-full flex flex-col items-center justify-center relative group cursor-pointer select-none z-10">
                <svg class="w-[20px] h-[20px] transition-colors" 
                     :class="isActive('menu') ? 'text-[#155E63]' : 'text-[#195a60] group-hover:text-[#114347]'"
                     fill="none" stroke="currentColor" :stroke-width="isActive('menu') ? '2.2' : '2'" viewBox="0 0 24 24">
                    <circle cx="7" cy="7" r="2.2"/>
                    <circle cx="17" cy="7" r="2.2"/>
                    <circle cx="7" cy="17" r="2.2"/>
                    <circle cx="17" cy="17" r="2.2"/>
                </svg>
                <span class="text-[10px] tracking-tight mt-0.5 leading-none transition-colors"
                      :class="isActive('menu') ? 'font-bold text-[#155E63]' : 'font-medium text-slate-700 group-hover:text-slate-900'">
                    Menu
                </span>
                <div class="h-1 flex items-center justify-center mt-0.5">
                    <span x-show="isActive('menu')" class="w-4 h-[2px] bg-[#155E63] rounded-full" style="display: none;"></span>
                </div>
            </button>
        </nav>
    </div>

    <!-- ============================================================== -->
    <!-- MOBILE BOTTOM SHEET MODAL (SEMUA LAYANAN & MENU GOMSN)        -->
    <!-- ============================================================== -->
    <div x-show="bottomSheetOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-end justify-center md:hidden"
         x-data="{ 
             sheetSearch: '',
             isDragging: false,
             dragStartY: 0,
             sheetTranslateY: 0,
             startDrag(e) {
                 this.isDragging = true;
                 this.dragStartY = e.touches ? e.touches[0].clientY : e.clientY;
                 this.sheetTranslateY = 0;
             },
             onDrag(e) {
                 if (!this.isDragging) return;
                 const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                 const deltaY = clientY - this.dragStartY;
                 if (deltaY > 0) {
                     this.sheetTranslateY = deltaY;
                 } else {
                     this.sheetTranslateY = deltaY * 0.15;
                 }
             },
             endDrag() {
                 if (!this.isDragging) return;
                 const threshold = 55;
                 if (this.sheetTranslateY > threshold) {
                     this.isDragging = false;
                     this.sheetTranslateY = 0;
                     bottomSheetOpen = false;
                 } else {
                     this.isDragging = false;
                     this.sheetTranslateY = 0;
                 }
             }
         }"
         @touchmove="if (isDragging) onDrag($event)"
         @touchend="if (isDragging) endDrag()"
         @mousemove.window="if (isDragging) onDrag($event)"
         @mouseup.window="if (isDragging) endDrag()">
        
        <!-- Backdrop Blur -->
        <div x-show="bottomSheetOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="bottomSheetOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <!-- Sliding Sheet Panel (Supports Pull / Drag Down to Close) -->
        <div x-show="bottomSheetOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             :style="isDragging ? 'transform: translateY(' + Math.max(0, sheetTranslateY) + 'px) !important; transition: none !important;' : (sheetTranslateY > 0 ? 'transform: translateY(0px); transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);' : '')"
             class="relative w-full max-w-xl mx-auto max-h-[88vh] bg-white rounded-t-[30px] shadow-[0_-16px_50px_rgba(0,0,0,0.35)] flex flex-col z-20 overflow-hidden">
            
            <!-- Crisp Solid White Background with Ultra-Soft Micro-Dot Texture (Body Area) -->
            <div class="absolute inset-0 pointer-events-none z-0" 
                 style="background-color: #FFFFFF; background-image: radial-gradient(rgba(148, 163, 184, 0.14) 0.75px, transparent 0.75px); background-size: 20px 20px;"></div>

            <!-- Unified Theme-Green Top Header (Pull Handle + User Identity + Search Bar) -->
            <div class="relative z-10 select-none touch-none overflow-hidden shadow-md"
                 style="background: linear-gradient(135deg, #052629 0%, #0b464b 50%, #0f6368 100%);">
                
                <!-- Decorative Specular Rim & Subtle Wave Accent -->
                <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                <svg class="absolute right-0 top-0 h-full w-56 opacity-15 pointer-events-none" viewBox="0 0 220 140" fill="none" preserveAspectRatio="none">
                    <path d="M220 0C155 25 130 95 45 140H220V0Z" fill="url(#sheetHeaderWave)"/>
                    <defs>
                        <linearGradient id="sheetHeaderWave" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#5eead4"/>
                            <stop offset="100%" stop-color="#0d9488" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>

                <!-- 1. Pull Handle Bar Indicator -->
                <div class="pt-2.5 pb-1 cursor-grab active:cursor-grabbing flex flex-col items-center justify-center group relative z-10"
                     @touchstart.passive="startDrag($event)"
                     @touchmove="onDrag($event)"
                     @touchend="endDrag()"
                     @mousedown="startDrag($event)"
                     @click="bottomSheetOpen = false"
                     title="Tarik ke bawah dari garis ini untuk menutup menu">
                    <div class="w-11 h-1.5 bg-white/30 group-hover:bg-white/60 group-active:bg-[#5eead4] rounded-full transition-colors"></div>
                </div>

                <!-- 2. Sheet Header (Executive Profile & Close Button) -->
                <div class="px-5 pt-2 pb-3.5 flex items-center justify-between gap-3 cursor-grab active:cursor-grabbing relative z-10"
                     @touchstart.passive="startDrag($event)"
                     @touchmove="onDrag($event)"
                     @touchend="endDrag()"
                     @mousedown="startDrag($event)">
                    
                    <div class="flex items-center gap-3.5 min-w-0 pointer-events-none">
                        <!-- Avatar with Online Indicator -->
                        <div class="relative shrink-0">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" 
                                     alt="" 
                                     onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                     class="h-11 w-11 rounded-2xl object-cover shadow-md ring-2 ring-white/25 shrink-0">
                                <div style="display: none;" class="h-11 w-11 rounded-2xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold text-sm shadow-md ring-2 ring-white/25 shrink-0">
                                    {{ auth()->user()->initials }}
                                </div>
                            @else
                                <div class="h-11 w-11 rounded-2xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-white/25 shrink-0">
                                    {{ auth()->user()->initials }}
                                </div>
                            @endif
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#073337]"></span>
                        </div>

                        <!-- Title & User Info -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-[15px] text-white tracking-tight leading-snug truncate">Semua Layanan Portal</h3>
                                <span class="text-[9px] font-extrabold tracking-wider px-2 py-0.5 rounded-md bg-[#18C4C4]/20 text-[#5eead4] border border-[#5eead4]/35 shrink-0">
                                    GOMSN
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 min-w-0">
                                <span class="text-xs font-semibold text-teal-50/95 truncate">{{ auth()->user()->name }}</span>
                                <span class="text-teal-300/50 text-xs">•</span>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-teal-100 bg-white/12 px-2 py-0.5 rounded-md border border-white/15 shrink-0">
                                    {{ auth()->user()->division ?: (auth()->user()->getRoleNames()->first() ? ucfirst(auth()->user()->getRoleNames()->first()) : 'MSN') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button type="button" 
                            @click.stop="bottomSheetOpen = false" 
                            @mousedown.stop 
                            @touchstart.stop 
                            aria-label="Tutup Menu"
                            class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white/90 hover:text-white flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- 3. Integrated Search Bar Inside Theme Header -->
                <div class="px-5 pb-3.5 relative z-10">
                    <div class="relative flex items-center">
                        <input type="text" 
                               x-model="sheetSearch" 
                               placeholder="Cari layanan (absen, lembur, kasbon, tiket)..."
                               class="peer w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-white/20 bg-white/12 focus:bg-white focus:border-white focus:ring-2 focus:ring-[#5eead4]/40 transition text-white focus:text-slate-800 placeholder:text-teal-100/70 focus:placeholder:text-slate-400">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none z-10 text-teal-100 peer-focus:text-[#155E63] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="button" 
                                x-show="sheetSearch" 
                                @click="sheetSearch = ''" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center z-10 text-teal-200 hover:text-white peer-focus:text-slate-400 peer-focus:hover:text-slate-700 text-xs cursor-pointer">
                            &times;
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content of Menus -->
            <div class="p-5 overflow-y-auto space-y-5 flex-1 max-h-[calc(88vh-140px)] relative z-10">
                
                <!-- KATEGORI 1: PENGAJUAN & OPERASIONAL -->
                <div x-show="!sheetSearch || 'pengajuan kasbon dana beli persetujuan atasan lembur overtime stok barang inventaris'.includes(sheetSearch.toLowerCase())" class="space-y-2.5">
                    <div class="flex items-center justify-between pt-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-3.5 rounded-full bg-gradient-to-b from-amber-500 to-orange-500"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Pengajuan &amp; Operasional</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Layanan</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('gomsn.pengajuan.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Pengajuan Saya</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Kasbon, Beli, Stok</span>
                            </div>
                        </a>

                        @if ($navCanApprovePengajuan)
                        <a href="{{ route('gomsn.pengajuan.approvals') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-amber-400 hover:bg-amber-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center justify-between gap-2 group shadow-2xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-amber-800 leading-snug">Persetujuan</span>
                                    <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Tim Karyawan</span>
                                </div>
                            </div>
                            @if ($navPendingAtasanCount > 0)
                            <span class="w-5 h-5 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center justify-center shrink-0">
                                {{ $navPendingAtasanCount }}
                            </span>
                            @endif
                        </a>
                        @endif

                        @if ($navCanApproveKoreksi)
                        <a href="{{ route('attendance.corrections.approvals') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-emerald-400 hover:bg-emerald-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center justify-between gap-2 group shadow-2xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Pengajuan Absen</span>
                                    <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Koreksi Kehadiran</span>
                                </div>
                            </div>
                            @if ($navPendingKoreksiCount > 0)
                            <span class="w-5 h-5 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center justify-center shrink-0">
                                {{ $navPendingKoreksiCount }}
                            </span>
                            @endif
                        </a>
                        @endif

                        <a href="{{ route('gomsn.overtime.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-blue-400 hover:bg-blue-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center justify-between gap-2 group shadow-2xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-blue-800 leading-snug">Lembur</span>
                                    <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Pengajuan &amp; SPK</span>
                                </div>
                            </div>
                            @if ($navPendingLemburCount > 0)
                            <span class="w-5 h-5 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center justify-center shrink-0">
                                {{ $navPendingLemburCount }}
                            </span>
                            @endif
                        </a>

                        @if ($navIsFinance)
                        <a href="{{ route('gomsn.inventory.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-emerald-400 hover:bg-emerald-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center justify-between gap-2 group shadow-2xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-emerald-800 leading-snug">Stok Barang</span>
                                    <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Gudang &amp; Material</span>
                                </div>
                            </div>
                            @if ($navLowStockCount > 0)
                            <span class="w-5 h-5 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center justify-center shrink-0">
                                {{ $navLowStockCount }}
                            </span>
                            @endif
                        </a>
                        @endif
                    </div>
                </div>

                <!-- KATEGORI 2: ABSENSI & CUTI -->
                <div x-show="!sheetSearch || 'absen presensi jadwal kerja shift cuti izin tukar koreksi peringkat disiplin leaderboard laporan'.includes(sheetSearch.toLowerCase())" class="space-y-2.5">
                    <div class="flex items-center justify-between pt-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-3.5 rounded-full bg-gradient-to-b from-[#155E63] to-[#18C4C4]"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Absensi &amp; Cuti</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Layanan</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('attendance.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Absen</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Biometrik &amp; GPS</span>
                            </div>
                        </a>

                        <a href="{{ route('attendance.history') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-cyan-400 hover:bg-cyan-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-cyan-800 leading-snug">Riwayat Absen</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Log Kehadiran</span>
                            </div>
                        </a>

                        @if ($canViewReports)
                        <a href="{{ route('attendance.reports') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Laporan Absensi</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Rekap Harian &amp; Admin</span>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('attendance.schedules') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-indigo-400 hover:bg-indigo-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-indigo-800 leading-snug">Jadwal Kerja</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Kalender Shift</span>
                            </div>
                        </a>

                        <a href="{{ route('attendance.leaves') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-emerald-400 hover:bg-emerald-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-emerald-800 leading-snug">Cuti &amp; Izin</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Saldo &amp; Pengajuan</span>
                            </div>
                        </a>

                        <a href="{{ route('attendance.deductions') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-amber-400 hover:bg-amber-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-amber-800 leading-snug">Keuangan Absen</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Potongan &amp; Denda</span>
                            </div>
                        </a>

                        <a href="{{ route('attendance.leaderboard') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-purple-400 hover:bg-purple-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-purple-800 leading-snug">Leaderboard</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Peringkat Disiplin</span>
                            </div>
                        </a>

                        <a href="{{ route('attendance.swaps') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Tukar Shift</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Pengajuan Jadwal</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- KATEGORI 3: PEKERJAAN & LAPANGAN -->
                <div x-show="!sheetSearch || 'tiket noc gangguan tugas lapangan kpi laporan teknisi perangkat sesi olt pon odp jaringan fiber pelanggan'.includes(sheetSearch.toLowerCase())" class="space-y-2.5">
                    <div class="flex items-center justify-between pt-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-3.5 rounded-full bg-gradient-to-b from-rose-500 to-pink-500"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Pekerjaan &amp; Lapangan</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Operasional</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('gomsn.network.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">OLT, PON &amp; ODP</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Jaringan &amp; Pelanggan</span>
                            </div>
                        </a>

                        <a href="{{ route('tickets.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-rose-400 hover:bg-rose-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 group-hover:bg-rose-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-rose-800 leading-snug">Tiket Gangguan</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Laporan NOC / SPK</span>
                            </div>
                        </a>

                        <a href="{{ route('gomsn.tasks.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-sky-400 hover:bg-sky-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 group-hover:bg-sky-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-sky-800 leading-snug">Tugas Lapangan</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Work Order Tim</span>
                            </div>
                        </a>

                        <a href="{{ route('gomsn.analytics.kpi') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-indigo-400 hover:bg-indigo-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-indigo-800 leading-snug">Laporan KPI</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Analitik Performa</span>
                            </div>
                        </a>

                        @hasrole(['admin', 'super_admin'])
                        <a href="{{ route('portal.sessions') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-slate-400 hover:bg-slate-50 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 group-hover:bg-slate-700 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-slate-900 leading-snug">Perangkat Saya</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Binding Multi-Device</span>
                            </div>
                        </a>
                        @endhasrole
                    </div>
                </div>

                <!-- KATEGORI 4: AKUN & PENGATURAN -->
                <div x-show="!sheetSearch || 'profil akun pengaturan radius absen audit log keluar logout'.includes(sheetSearch.toLowerCase())" class="space-y-2.5 pt-0.5">
                    <div class="flex items-center justify-between pt-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-3.5 rounded-full bg-gradient-to-b from-slate-600 to-slate-400"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Akun &amp; Pengaturan</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Sistem</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('portal.profile') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Profil Saya</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Kontak &amp; Rekening</span>
                            </div>
                        </a>

                        @can('portal.settings.manage')
                        <a href="{{ route('portal.settings') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-slate-400 hover:bg-slate-50 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 group-hover:bg-slate-700 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-slate-900 leading-snug">Radius Absen</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Konfigurasi Lokasi</span>
                            </div>
                        </a>
                        @endcan

                        @can('portal.audit.view')
                        <a href="{{ route('portal.audit') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-slate-400 hover:bg-slate-50 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 group-hover:bg-slate-700 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-slate-900 leading-snug">Audit Log</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Riwayat Keamanan</span>
                            </div>
                        </a>
                        @endcan

                        @if (auth()->user() && (auth()->user()->hasRole(['super_admin', 'admin', 'hrd']) || auth()->user()->can('attendance.employee.manage')))
                        <a href="{{ route('portal.employees.index') }}" class="p-3 rounded-2xl border border-slate-200/90 bg-white hover:border-teal-400 hover:bg-teal-50/30 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 group-hover:bg-[#155E63] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-xs text-slate-800 block truncate group-hover:text-[#155E63] leading-snug">Data Karyawan</span>
                                <span class="text-[10px] text-slate-400 block truncate mt-0.5 leading-tight">Kelola SDM &amp; Akun</span>
                            </div>
                        </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="col-span-1">
                            @csrf
                            <button type="submit" class="w-full p-3 rounded-2xl border border-rose-200/90 bg-white hover:bg-rose-50/80 hover:border-rose-300 hover:shadow-sm active:scale-[0.98] transition-all flex items-center gap-2.5 group shadow-2xs text-left cursor-pointer">
                                <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 group-hover:bg-rose-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs text-rose-600 block truncate leading-snug">Logout / Keluar</span>
                                    <span class="text-[10px] text-rose-400 block truncate mt-0.5 leading-tight">Akhiri Sesi Portal</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Floating Chatbot Asisten PT MSN (§5.8) -->
    <x-chatbot-widget />
    @endauth

    <!-- SweetAlert2 Toast Global Notifier (Ujung Kanan Atas) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function triggerToast(icon, message) {
                if (!message) return;
                const fire = () => {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: icon,
                            title: message
                        });
                    } else if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 4500,
                            timerProgressBar: true,
                            padding: '0.75rem 1.25rem',
                            customClass: {
                                popup: 'rounded-2xl shadow-2xl border border-slate-100 text-xs sm:text-sm font-sans select-none',
                                title: 'font-semibold text-slate-800 text-xs sm:text-sm',
                            },
                            icon: icon,
                            title: message
                        });
                    }
                };

                if (window.Toast || window.Swal) {
                    fire();
                } else {
                    setTimeout(fire, 200);
                }
            }

            @if (session('success'))
                triggerToast('success', {!! json_encode(session('success')) !!});
            @elseif (session('status'))
                triggerToast('success', {!! json_encode(session('status')) !!});
            @elseif (session('message'))
                triggerToast('info', {!! json_encode(session('message')) !!});
            @elseif (session('warning'))
                triggerToast('warning', {!! json_encode(session('warning')) !!});
            @elseif (session('error'))
                triggerToast('error', {!! json_encode(session('error')) !!});
            @elseif (isset($errors) && $errors->any())
                triggerToast('error', {!! json_encode($errors->first()) !!});
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
