<x-layouts.app title="Pusat Notifikasi">
    @php
        $inboxConfig = [
            'userId' => auth()->id(),
            'latestTs' => $notifications->max('timestamp') ?? 0,
            'allItems' => $notifications->map(fn ($n) => [
                'id' => $n['id'],
                'ts' => $n['timestamp'],
                'group' => $n['time_group'],
            ])->values()->all(),
        ];

        $timeGroupLabels = [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            'this_week' => 'Minggu Ini',
            'older' => 'Lebih Lama',
        ];
        $groupedNotifications = $notifications->groupBy('time_group');
    @endphp
    <script>
        window.__gomsnInboxConfig = @json($inboxConfig);

        function gomsnInboxPage() {
            const cfg = window.__gomsnInboxConfig || {};
            const uid = cfg.userId || 0;
            const readTsKey = 'gomsn_notif_read_ts_' + uid;
            const readIdsKey = 'gomsn_notif_read_ids_' + uid;
            const dismissedKey = 'gomsn_notif_dismissed_ids_' + uid;

            return {
                lastReadTs: 0,
                readIds: [],
                dismissedIds: [],
                onlyUnread: false,
                allItems: cfg.allItems || [],

                init() {
                    try {
                        this.lastReadTs = parseInt(localStorage.getItem(readTsKey) || '0', 10) || 0;
                        this.readIds = JSON.parse(localStorage.getItem(readIdsKey) || '[]') || [];
                        this.dismissedIds = JSON.parse(localStorage.getItem(dismissedKey) || '[]') || [];
                    } catch (e) {}
                },

                persist() {
                    try {
                        localStorage.setItem(readTsKey, String(this.lastReadTs));
                        localStorage.setItem(readIdsKey, JSON.stringify(this.readIds.slice(-300)));
                        localStorage.setItem(dismissedKey, JSON.stringify(this.dismissedIds.slice(-300)));
                        window.dispatchEvent(new CustomEvent('gomsn-notif-updated'));
                    } catch (e) {}
                },

                isDismissed(id) {
                    return this.dismissedIds.includes(id);
                },

                isUnread(id, ts) {
                    if (this.isDismissed(id)) return false;
                    if (this.readIds.includes(id)) return false;
                    return ts > this.lastReadTs;
                },

                shouldShowItem(id, ts) {
                    if (this.isDismissed(id)) return false;
                    if (this.onlyUnread && !this.isUnread(id, ts)) return false;
                    return true;
                },

                hasVisibleInGroup(groupKey) {
                    return this.allItems.some(it => it.group === groupKey && this.shouldShowItem(it.id, it.ts));
                },

                get unreadCount() {
                    return this.allItems.filter(it => this.isUnread(it.id, it.ts)).length;
                },

                get visibleCount() {
                    return this.allItems.filter(it => this.shouldShowItem(it.id, it.ts)).length;
                },

                markOneRead(id) {
                    if (!this.readIds.includes(id)) {
                        this.readIds.push(id);
                        this.persist();
                    }
                },

                dismissOne(id) {
                    if (!this.dismissedIds.includes(id)) {
                        this.dismissedIds.push(id);
                        this.persist();
                    }
                },

                restoreDismissed() {
                    this.dismissedIds = [];
                    this.persist();
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: 'Notifikasi terarsip ditampilkan kembali.' });
                    }
                },

                markAllRead() {
                    const nowTs = Math.max(Math.floor(Date.now() / 1000), cfg.latestTs || 0);
                    this.lastReadTs = nowTs;
                    this.readIds = [];
                    this.persist();
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: 'Semua notifikasi ditandai sudah dibaca.' });
                    }
                },

                openItem(id, link) {
                    this.markOneRead(id);
                    if (link) {
                        window.location.href = link;
                    }
                }
            };
        }
    </script>

    <div class="space-y-5 w-full pb-20" x-data="gomsnInboxPage()">
        <!-- 1. Header Banner (Full Width & Mengikuti Warna Tema Aplikasi) -->
        <div class="gomsn-themed-banner relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.28)] p-4 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 30%, #0a525f 70%, #0e6774 100%);">
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/40 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-white shrink-0 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Pusat Notifikasi
                        </h1>
                        <span x-show="unreadCount > 0" x-cloak
                              class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400 text-slate-900 shadow-xs"
                              x-text="unreadCount + ' Belum Dibaca'"></span>
                    </div>
                    <p class="text-xs text-white/80 mt-0.5 leading-snug">
                        Tindak lanjut cepat, pengumuman perusahaan, status pengajuan, lembur, tiket, dan absensi Anda
                    </p>
                </div>
            </div>

            <div class="relative z-10 flex flex-wrap items-center gap-2 shrink-0">
                <button type="button"
                        @click="markAllRead()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white/15 hover:bg-white/25 text-white border border-white/25 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </div>
        </div>

        <!-- 2. PERLU TINDAKAN ANDA (Action Center — Prioritas Utama) -->
        @if (! empty($actionItems))
            <section class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">
                            Perlu Tindakan Anda
                        </h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            {{ collect($actionItems)->sum('count') }} Menunggu
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($actionItems as $act)
                        @php
                            $actColors = match ($act['color']) {
                                'amber'   => ['icon' => 'bg-amber-50 text-amber-600 border-amber-200', 'badge' => 'bg-amber-500 text-white', 'border' => 'hover:border-amber-400'],
                                'emerald' => ['icon' => 'bg-emerald-50 text-emerald-600 border-emerald-200', 'badge' => 'bg-emerald-600 text-white', 'border' => 'hover:border-emerald-400'],
                                'blue'    => ['icon' => 'bg-blue-50 text-blue-600 border-blue-200', 'badge' => 'bg-blue-600 text-white', 'border' => 'hover:border-blue-400'],
                                'rose'    => ['icon' => 'bg-rose-50 text-rose-600 border-rose-200', 'badge' => 'bg-rose-600 text-white', 'border' => 'hover:border-rose-400'],
                                'purple'  => ['icon' => 'bg-purple-50 text-purple-600 border-purple-200', 'badge' => 'bg-purple-600 text-white', 'border' => 'hover:border-purple-400'],
                                default   => ['icon' => 'bg-teal-50 text-teal-600 border-teal-200', 'badge' => 'bg-teal-600 text-white', 'border' => 'hover:border-teal-400'],
                            };
                        @endphp
                        <a href="{{ $act['url'] }}"
                           class="group rounded-2xl bg-white border border-slate-200/90 {{ $actColors['border'] }} p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 {{ $actColors['icon'] }}">
                                        @if ($act['icon'] === 'wallet')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @elseif ($act['icon'] === 'clock')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif ($act['icon'] === 'ticket')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">{{ $act['title'] }}</h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $act['desc'] }}</p>
                                    </div>
                                </div>
                                <span class="min-w-[24px] h-6 px-1.5 rounded-full text-[11px] font-black flex items-center justify-center shrink-0 shadow-2xs {{ $actColors['badge'] }}">
                                    {{ $act['count'] > 99 ? '99+' : $act['count'] }}
                                </span>
                            </div>
                            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-teal-600 group-hover:text-teal-700">
                                <span>{{ $act['cta'] }}</span>
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- 3. Filter Cepat (Search + Belum Dibaca + Kategori) -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row gap-2.5">
                <form method="GET" action="{{ route('portal.notifications.inbox') }}" class="flex flex-1 gap-2">
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                    <div class="relative flex-1">
                        <input type="text"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Cari judul, pengirim, atau isi notifikasi..."
                               class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition cursor-pointer shrink-0">
                        Cari
                    </button>
                </form>

                <!-- Toggle Cepat: Semua vs Belum Dibaca -->
                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 border border-slate-200/80 shrink-0 self-start sm:self-auto">
                    <button type="button"
                            @click="onlyUnread = false"
                            :class="!onlyUnread ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-700'"
                            class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer">
                        Semua
                    </button>
                    <button type="button"
                            @click="onlyUnread = true"
                            :class="onlyUnread ? 'bg-white text-teal-700 shadow-2xs' : 'text-slate-500 hover:text-slate-700'"
                            class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer flex items-center gap-1.5">
                        <span>Belum Dibaca</span>
                        <span x-show="unreadCount > 0" x-cloak
                              class="px-1.5 py-0.2 rounded-full bg-teal-600 text-white text-[9.5px] font-mono"
                              x-text="unreadCount"></span>
                    </button>
                </div>
            </div>

            @php
                $tabs = [
                    'all' => 'Semua',
                    'pengumuman' => 'Pengumuman',
                    'pengajuan' => 'Pengajuan',
                    'lembur' => 'Lembur',
                    'tiket' => 'Tiket',
                    'absensi' => 'Absensi',
                ];
            @endphp
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs select-none">
                    @foreach ($tabs as $tabKey => $tabLabel)
                        <a href="{{ route('portal.notifications.inbox', array_filter(['category' => $tabKey, 'q' => $search])) }}"
                           class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0 {{ $activeCategory === $tabKey ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70' }}">
                            <span>{{ $tabLabel }}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ $activeCategory === $tabKey ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                                {{ $counts[$tabKey] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <button type="button"
                        x-show="dismissedIds.length > 0"
                        x-cloak
                        @click="restoreDismissed()"
                        class="text-[11px] font-bold text-teal-600 hover:text-teal-700 shrink-0 cursor-pointer">
                    Pulihkan Arsip (<span x-text="dismissedIds.length"></span>)
                </button>
            </div>
        </div>

        <!-- 4. Daftar Notifikasi Dikelompokkan Berdasarkan Waktu (Hari Ini, Kemarin, Minggu Ini, Lebih Lama) -->
        <div class="space-y-4">
            @foreach ($timeGroupLabels as $groupKey => $groupLabel)
                @if ($groupedNotifications->has($groupKey))
                    <div x-show="hasVisibleInGroup('{{ $groupKey }}')" class="space-y-2.5">
                        <!-- Date Group Divider -->
                        <div class="flex items-center gap-3 px-1">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">{{ $groupLabel }}</span>
                            <div class="h-px flex-1 bg-slate-200/80"></div>
                        </div>

                        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden divide-y divide-slate-100">
                            @foreach ($groupedNotifications->get($groupKey) as $notif)
                                @php
                                    $catMeta = match ($notif['category']) {
                                        'pengumuman' => ['label' => 'Pengumuman', 'bg' => 'bg-teal-50 text-teal-700 border-teal-200', 'icon' => 'megaphone'],
                                        'pengajuan'  => ['label' => 'Pengajuan',  'bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'wallet'],
                                        'lembur'     => ['label' => 'Lembur',     'bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'clock'],
                                        'tiket'      => ['label' => 'Tiket',      'bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'icon' => 'ticket'],
                                        'absensi'    => ['label' => 'Absensi',    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'calendar'],
                                        default      => ['label' => 'Info',       'bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => 'bell'],
                                    };
                                @endphp
                                <div x-show="shouldShowItem({{ $notif['id'] }}, {{ $notif['timestamp'] }})"
                                     data-link="{{ $notif['link'] ?? '' }}"
                                     @click="openItem({{ $notif['id'] }}, $el.dataset.link)"
                                     class="p-4 sm:p-5 hover:bg-slate-50/80 transition flex items-start gap-3.5 {{ !empty($notif['link']) ? 'cursor-pointer' : '' }}"
                                     :class="isUnread({{ $notif['id'] }}, {{ $notif['timestamp'] }}) ? 'bg-teal-50/25' : ''">

                                    <!-- Category Specific Icon -->
                                    <div class="w-10 h-10 rounded-2xl border flex items-center justify-center shrink-0 mt-0.5 {{ $catMeta['bg'] }}">
                                        @if ($catMeta['icon'] === 'megaphone')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                        @elseif ($catMeta['icon'] === 'wallet')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @elseif ($catMeta['icon'] === 'clock')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif ($catMeta['icon'] === 'ticket')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        @elseif ($catMeta['icon'] === 'calendar')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span x-show="isUnread({{ $notif['id'] }}, {{ $notif['timestamp'] }})" x-cloak class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></span>
                                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">{{ $notif['title'] }}</h3>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $catMeta['bg'] }}">
                                                    {{ $catMeta['label'] }}
                                                </span>
                                                <button type="button"
                                                        x-show="isUnread({{ $notif['id'] }}, {{ $notif['timestamp'] }})"
                                                        x-cloak
                                                        @click.stop="markOneRead({{ $notif['id'] }})"
                                                        title="Tandai sudah dibaca"
                                                        class="w-6 h-6 rounded-lg text-slate-400 hover:text-teal-600 hover:bg-teal-50 flex items-center justify-center transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                                <button type="button"
                                                        @click.stop="dismissOne({{ $notif['id'] }})"
                                                        title="Arsipkan / Sembunyikan"
                                                        class="w-6 h-6 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        @if ($notif['body'])
                                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed whitespace-pre-line">{{ $notif['body'] }}</p>
                                        @endif

                                        <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-slate-500">{{ $notif['sender_name'] }}</span>
                                                <span>&bull;</span>
                                                <span>{{ $notif['sent_human'] }}</span>
                                                @if ($notif['sent_formatted'])
                                                    <span class="hidden sm:inline text-slate-400">({{ $notif['sent_formatted'] }})</span>
                                                @endif
                                            </div>

                                            @if (! empty($notif['link']))
                                                <span class="inline-flex items-center gap-1 text-teal-600 font-bold">
                                                    <span>Buka Detail</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Empty State (Jika semua kosong / sudah dibaca semua saat filter Belum Dibaca) -->
            <div x-show="visibleCount === 0"
                 @if ($notifications->isNotEmpty()) x-cloak @endif
                 class="rounded-2xl bg-white border border-slate-200/90 shadow-xs py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700" x-text="onlyUnread ? 'Semua Notifikasi Sudah Dibaca' : 'Belum Ada Notifikasi'"></h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto" x-text="onlyUnread ? 'Tidak ada notifikasi baru yang belum dibaca saat ini.' : 'Semua pengumuman dan pembaruan status pengajuan, lembur, tiket, maupun absensi Anda akan muncul di sini.'"></p>
            </div>
        </div>
    </div>
</x-layouts.app>
