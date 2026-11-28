<x-layouts.app title="Sesi Login & Perangkat">
    <div class="space-y-6 w-full pb-16"
         x-data="{
            activeTab: 'sessions',
            search: '',
            statusFilter: 'all',
            showForceModal: false,
            forceSessionId: null,
            forceUserName: '',
            forceDeviceName: '',
            forceReason: 'Diputus oleh Administrator',

            openForceModal(id, user, device) {
                this.forceSessionId = id;
                this.forceUserName = user;
                this.forceDeviceName = device;
                this.forceReason = 'Diputus oleh Administrator';
                this.showForceModal = true;
            }
         }">

        <!-- 1. Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Shield Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-teal-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-teal-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Sesi Login &amp; Perangkat
                        </h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-300 border border-teal-400/30">
                            Keamanan Akun &amp; Multi-Device
                        </span>
                    </div>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Pantau sesi login aktif, kontrol pemutusan paksa (force logout), hapus riwayat, dan binding perangkat
                    </p>
                </div>
            </div>

            <!-- Right: Action Button (Prune Revoked Sessions) -->
            <div class="relative z-10 flex flex-wrap items-center gap-2">
                @if (($revokedSessionsCount ?? 0) > 0)
                <form method="POST" action="{{ route('portal.sessions.prune') }}" onsubmit="return confirm('Hapus seluruh riwayat sesi yang telah dihentikan/logout? ({{ $revokedSessionsCount }} data sesi)');">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-500/20 hover:bg-rose-500/30 text-rose-200 border border-rose-400/30 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-rose-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Bersihkan Sesi Dihentikan ({{ $revokedSessionsCount }})</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- 2. Status & Error Notifications -->
        @if (session('status'))
            <div class="rounded-2xl bg-teal-50 border border-teal-200 p-4 flex items-center justify-between gap-3 text-xs text-teal-900 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 shadow-2xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-rose-900">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>Terjadi kesalahan:</span>
                </div>
                <ul class="list-disc pl-6 space-y-0.5 text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 3. Metric Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <!-- Active Sessions Card -->
            <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Sesi Aktif</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-800 font-mono">{{ $activeSessionsCount ?? $sessions->whereNull('revoked_at')->count() }}</span>
                        <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block truncate">Sesi login terhubung ke server</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            <!-- Revoked Sessions Card -->
            <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Sesi Dihentikan</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-800 font-mono">{{ $revokedSessionsCount ?? $sessions->whereNotNull('revoked_at')->count() }}</span>
                        <span class="text-xs text-slate-500 font-medium">Riwayat</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block truncate">Logout mandiri / Force logout</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>

            <!-- Registered Devices Card -->
            <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Perangkat Terdaftar</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-black text-slate-800 font-mono">{{ $totalDevicesCount ?? $devices->count() }}</span>
                        <span class="text-xs text-teal-600 font-semibold">Device Binding</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block truncate">Maks. 2 perangkat per karyawan</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- 4. Tab Navigation (Sesi Login vs Perangkat vs Delegasi) -->
        <div class="flex items-center p-1 rounded-2xl bg-slate-200/80 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs gap-1.5 select-none w-full sm:w-auto self-start">
            <button type="button"
                    @click="activeTab = 'sessions'"
                    :class="activeTab === 'sessions'
                        ? 'bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 shadow-xs font-bold border border-teal-500/20'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer">
                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <span>Sesi Login</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 border border-teal-200/60 dark:border-teal-700/60">
                    {{ $sessions->count() }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'devices'"
                    :class="activeTab === 'devices'
                        ? 'bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 shadow-xs font-bold border border-teal-500/20'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer">
                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Perangkat Terdaftar</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $devices->count() }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'delegations'"
                    :class="activeTab === 'delegations'
                        ? 'bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 shadow-xs font-bold border border-teal-500/20'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer">
                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Delegasi Persetujuan</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $delegations->count() }}
                </span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: DAFTAR SESI LOGIN                    -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'sessions'" class="space-y-4">
            <!-- Filter & Search Toolbar -->
            <div class="rounded-2xl bg-white border border-slate-200/90 p-3.5 sm:p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <input type="text"
                           x-model="search"
                           placeholder="Cari nama karyawan, kode, IP, atau perangkat..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs">&times;</button>
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs select-none">
                    <button type="button"
                            @click="statusFilter = 'all'"
                            :class="statusFilter === 'all' ? 'bg-[#022836] text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0">
                        Semua
                    </button>
                    <button type="button"
                            @click="statusFilter = 'active'"
                            :class="statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Aktif
                    </button>
                    <button type="button"
                            @click="statusFilter = 'revoked'"
                            :class="statusFilter === 'revoked' ? 'bg-slate-700 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0">
                        Dihentikan
                    </button>
                </div>
            </div>

            <!-- Desktop Table View (Hidden on mobile) -->
            <div class="hidden md:block rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4">Pengguna</th>
                                <th class="py-3 px-4">Perangkat &amp; Browser</th>
                                <th class="py-3 px-4">IP Address</th>
                                <th class="py-3 px-4">Aktivitas Terakhir</th>
                                <th class="py-3 px-4">Status Sesi</th>
                                <th class="py-3 px-4 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($sessions as $s)
                                @php
                                    $isRevoked = !is_null($s->revoked_at);
                                    $isCurrentSession = ($s->session_id === session()->getId());
                                    $searchString = strtolower(($s->user?->name ?? '') . ' ' . ($s->user?->employee_code ?? '') . ' ' . ($s->device_name ?? '') . ' ' . ($s->ip_address ?? ''));
                                @endphp
                                <tr x-show="(statusFilter === 'all' || (statusFilter === 'active' && {{ $isRevoked ? 'false' : 'true' }}) || (statusFilter === 'revoked' && {{ $isRevoked ? 'true' : 'false' }})) && ('{{ addslashes($searchString) }}'.includes(search.toLowerCase()))"
                                    class="hover:bg-teal-50/30 transition">
                                    
                                    <!-- Pengguna -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-teal-600/10 text-teal-700 border border-teal-200/60 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($s->user?->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <span>{{ $s->user?->name ?? 'User #'.$s->user_id }}</span>
                                                    @if ($isCurrentSession)
                                                        <span class="px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-teal-100 text-teal-700 border border-teal-200">Anda</span>
                                                    @endif
                                                </div>
                                                <span class="font-mono text-[11px] text-slate-500">{{ $s->user?->employee_code ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Perangkat & Browser -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            @if (stripos($s->device_name, 'Android') !== false)
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.414 13.8533 8.125 12 8.125c-1.8533 0-3.5902.289-5.1368.8247L4.8409 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396"/></svg>
                                            @elseif (stripos($s->device_name, 'iOS') !== false || stripos($s->device_name, 'iPhone') !== false || stripos($s->device_name, 'Mac') !== false)
                                                <svg class="w-4 h-4 text-slate-800 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.1c.66-.82 1.11-1.96.99-3.1-.96.04-2.11.64-2.79 1.44-.59.69-1.11 1.83-.97 2.94 1.07.08 2.14-.52 2.77-1.28z"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            @endif
                                            <span class="font-medium text-slate-800">{{ $s->device_name }}</span>
                                        </div>
                                    </td>

                                    <!-- IP Address -->
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[11px] bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $s->ip_address }}
                                        </span>
                                    </td>

                                    <!-- Aktivitas Terakhir -->
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                        {{ $s->last_activity_at?->format('d M Y H:i:s') ?? '-' }}
                                    </td>

                                    <!-- Status Sesi -->
                                    <td class="py-3 px-4">
                                        @if ($isRevoked)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Dihentikan ({{ \Illuminate\Support\Str::limit($s->revoke_reason ?? 'Logout', 25) }})</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tindakan -->
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-1.5">
                                            @if (! $isRevoked)
                                                <button type="button"
                                                        @click="openForceModal('{{ $s->id }}', '{{ addslashes($s->user?->name ?? 'User') }}', '{{ addslashes($s->device_name) }}')"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.2 rounded-lg text-[11px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition shadow-2xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                    <span>Force Logout</span>
                                                </button>
                                            @endif

                                            <form method="POST" action="{{ route('portal.sessions.destroy', $s) }}" onsubmit="return confirm('Hapus rekam data sesi login ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="Hapus riwayat sesi ini"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition border border-transparent hover:border-rose-200 cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Tidak ada data sesi login yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards View (Visible on mobile screens) -->
            <div class="md:hidden space-y-3">
                @forelse ($sessions as $s)
                    @php
                        $isRevoked = !is_null($s->revoked_at);
                        $isCurrentSession = ($s->session_id === session()->getId());
                        $searchString = strtolower(($s->user?->name ?? '') . ' ' . ($s->user?->employee_code ?? '') . ' ' . ($s->device_name ?? '') . ' ' . ($s->ip_address ?? ''));
                    @endphp
                    <div x-show="(statusFilter === 'all' || (statusFilter === 'active' && {{ $isRevoked ? 'false' : 'true' }}) || (statusFilter === 'revoked' && {{ $isRevoked ? 'true' : 'false' }})) && ('{{ addslashes($searchString) }}'.includes(search.toLowerCase()))"
                         class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs space-y-3">
                        
                        <!-- Header Card -->
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-teal-600/10 text-teal-700 border border-teal-200/60 font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($s->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-xs truncate flex items-center gap-1.5">
                                        <span>{{ $s->user?->name ?? 'User #'.$s->user_id }}</span>
                                        @if ($isCurrentSession)
                                            <span class="px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-teal-100 text-teal-700 border border-teal-200">Anda</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-[11px] text-slate-500">{{ $s->user?->employee_code ?? '-' }}</span>
                                </div>
                            </div>
                            
                            @if ($isRevoked)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                    Dihentikan
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Aktif
                                </span>
                            @endif
                        </div>

                        <!-- Info Rows -->
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Perangkat</span>
                                <span class="font-medium text-slate-800">{{ $s->device_name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">IP Address</span>
                                <span class="font-mono text-slate-700">{{ $s->ip_address }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Aktivitas Terakhir</span>
                                <span class="font-mono text-slate-700">{{ $s->last_activity_at?->format('d M Y H:i:s') ?? '-' }}</span>
                            </div>
                            @if ($isRevoked && $s->revoke_reason)
                                <div class="col-span-2 rounded-xl bg-slate-50 p-2 text-slate-600 text-[10.5px]">
                                    <span class="font-bold text-slate-700">Alasan Logout:</span> {{ $s->revoke_reason }}
                                </div>
                            @endif
                        </div>

                        <!-- Action Footer -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            @if (! $isRevoked)
                                <button type="button"
                                        @click="openForceModal('{{ $s->id }}', '{{ addslashes($s->user?->name ?? 'User') }}', '{{ addslashes($s->device_name) }}')"
                                        class="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition text-center">
                                    Force Logout
                                </button>
                            @endif

                            <form method="POST" action="{{ route('portal.sessions.destroy', $s) }}" onsubmit="return confirm('Hapus rekam data sesi login ini secara permanen?');" class="{{ $isRevoked ? 'w-full' : '' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full py-1.5 px-3 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 transition flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus Sesi</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-8 text-center text-slate-400">
                        Tidak ada data sesi login.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: PERANGKAT TERDAFTAR (DEVICE BINDING) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'devices'" x-cloak class="space-y-4">
            <!-- Desktop Table View -->
            <div class="hidden md:block rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4">Karyawan</th>
                                <th class="py-3 px-4">Nama Perangkat &amp; Platform</th>
                                <th class="py-3 px-4">Fingerprint</th>
                                <th class="py-3 px-4">Terakhir Dipakai</th>
                                <th class="py-3 px-4">Status Binding</th>
                                <th class="py-3 px-4 text-right">Kontrol Admin / HRD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($devices as $d)
                                <tr class="hover:bg-teal-50/30 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800">{{ $d->user?->name ?? 'User #'.$d->user_id }}</div>
                                        <span class="font-mono text-[11px] text-slate-500">{{ $d->user?->employee_code ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-800">{{ $d->device_name }}</div>
                                        <span class="text-[11px] text-slate-500 capitalize">{{ $d->platform }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($d->device_fingerprint, 24) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                        {{ $d->last_used_at?->format('d M Y H:i') ?? '-' }}
                                        <span class="block text-[10px] text-slate-400">{{ $d->last_ip }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($d->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                APPROVED
                                            </span>
                                        @elseif ($d->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                PENDING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                REVOKED
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            @if ($canManageAll)
                                                <form method="POST" action="{{ route('portal.devices.approve', $d) }}" class="inline-flex items-center gap-1.5">
                                                    @csrf
                                                    <select name="status" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-teal-500 outline-none">
                                                        <option value="approved" @selected($d->status === 'approved')>Setujui</option>
                                                        <option value="revoked" @selected($d->status === 'revoked')>Cabut Akses</option>
                                                    </select>
                                                    <input type="text" name="reason" required placeholder="Alasan audit..." value="Penyesuaian otorisasi perangkat" class="w-32 rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs text-slate-800 focus:border-teal-500 outline-none">
                                                    <button type="submit" class="px-2.5 py-1 rounded-xl bg-slate-800 text-white font-bold text-xs hover:bg-slate-900 transition">
                                                        Simpan
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('portal.devices.destroy', $d) }}" onsubmit="return confirm('Hapus perangkat terdaftar ini? Seluruh sesi terkait perangkat ini akan diputus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus perangkat" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition border border-transparent hover:border-rose-200 cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Tidak ada perangkat terdaftar dalam sistem.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div class="md:hidden space-y-3">
                @forelse ($devices as $d)
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                            <div>
                                <div class="font-bold text-slate-800 text-xs">{{ $d->user?->name ?? 'User #'.$d->user_id }}</div>
                                <span class="font-mono text-[10px] text-slate-500">{{ $d->user?->employee_code ?? '-' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $d->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $d->status }}
                            </span>
                        </div>
                        <div class="text-[11px] space-y-1">
                            <div><strong class="text-slate-500">Perangkat:</strong> {{ $d->device_name }} ({{ $d->platform }})</div>
                            <div><strong class="text-slate-500">Fingerprint:</strong> <span class="font-mono text-slate-600">{{ \Illuminate\Support\Str::limit($d->device_fingerprint, 20) }}</span></div>
                            <div><strong class="text-slate-500">Terakhir Dipakai:</strong> {{ $d->last_used_at?->format('d M Y H:i') ?? '-' }} ({{ $d->last_ip }})</div>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('portal.devices.destroy', $d) }}" onsubmit="return confirm('Hapus perangkat terdaftar ini?');" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-1.5 px-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus Perangkat</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-8 text-center text-slate-400">
                        Tidak ada perangkat terdaftar.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: DELEGASI PERSETUJUAN                 -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'delegations'" x-cloak class="space-y-4">
            <!-- Form Tambah Delegasi -->
            <div class="rounded-2xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800">Buat Delegasi Persetujuan Baru</h2>
                </div>

                <form method="POST" action="{{ route('portal.delegations.store') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Pemberi Delegasi</label>
                        <select name="delegator_id" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-teal-500 outline-none">
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected($u->id === auth()->id())>{{ $u->name }} ({{ $u->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Penerima Delegasi</label>
                        <select name="delegate_id" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-teal-500 outline-none">
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Modul</label>
                        <select name="module" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-teal-500 outline-none">
                            <option value="all">Semua Modul</option>
                            <option value="attendance">Absensi &amp; Cuti</option>
                            <option value="ticketing">Tiketing</option>
                            <option value="gomsn">GoMSN Operasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Mulai</label>
                        <input type="date" name="start_date" required value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Selesai</label>
                        <input type="date" name="end_date" required value="{{ now()->addDays(7)->toDateString() }}" class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Alasan</label>
                        <div class="flex gap-1.5">
                            <input type="text" name="reason" required placeholder="Cuti / dinas..." class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-teal-500 outline-none">
                            <button type="submit" class="px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shrink-0 transition">
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- List Delegasi -->
            <div class="space-y-2">
                @forelse ($delegations as $del)
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-slate-800">{{ $del->delegator?->name }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                <span class="font-bold text-teal-700">{{ $del->delegate?->name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ strtoupper($del->module) }}
                                </span>
                            </div>
                            <div class="text-slate-500 text-[11px]">
                                Berlaku: <strong class="text-slate-700">{{ $del->start_date?->format('d M Y') }}</strong> s.d. <strong class="text-slate-700">{{ $del->end_date?->format('d M Y') }}</strong>
                                · Alasan: <span class="italic text-slate-600">{{ $del->reason }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('portal.delegations.destroy', $del) }}" onsubmit="return confirm('Hapus delegasi persetujuan ini?');" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white border border-slate-200/90 p-8 text-center text-slate-400 text-xs">
                        Belum ada delegasi persetujuan yang aktif.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 5. Modal Konfirmasi Force Logout -->
        <div x-show="showForceModal"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
                 @click="showForceModal = false"></div>

            <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
                <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full p-6 border border-slate-200 space-y-4"
                     @click.outside="showForceModal = false">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Konfirmasi Force Logout</h3>
                            <p class="text-[11px] text-slate-500">Putuskan sesi login pengguna secara paksa</p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-3.5 text-xs text-slate-700 space-y-1 border border-slate-200/60">
                        <div>Pengguna: <strong class="text-slate-900" x-text="forceUserName"></strong></div>
                        <div>Perangkat: <span class="font-mono text-slate-800" x-text="forceDeviceName"></span></div>
                    </div>

                    <form :action="'{{ url('/portal/sessions') }}/' + forceSessionId + '/revoke'" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Alasan Force Logout <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="reason"
                                   required
                                   x-model="forceReason"
                                   placeholder="mis: Audit keamanan / pergantian perangkat..."
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button"
                                    @click="showForceModal = false"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white font-bold text-xs shadow-xs transition">
                                Putuskan Sesi Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
