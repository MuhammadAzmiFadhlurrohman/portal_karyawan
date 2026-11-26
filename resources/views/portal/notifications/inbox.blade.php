<x-layouts.app title="Notifikasi">
    @php
        $inboxConfig = [
            'userId' => auth()->id(),
            'latestTs' => $notifications->max('timestamp') ?? 0,
        ];
    @endphp
    <script>
        window.__gomsnInboxConfig = @json($inboxConfig);
        function gomsnInboxPage() {
            const cfg = window.__gomsnInboxConfig || {};
            const readKey = 'gomsn_notif_read_ts_' + (cfg.userId || 0);
            return {
                lastReadTs: 0,
                init() {
                    try {
                        this.lastReadTs = parseInt(localStorage.getItem(readKey) || '0', 10) || 0;
                    } catch (e) {}
                },
                isUnread(ts) {
                    return ts > this.lastReadTs;
                },
                markAllRead() {
                    const nowTs = Math.max(Math.floor(Date.now() / 1000), cfg.latestTs || 0);
                    this.lastReadTs = nowTs;
                    try {
                        localStorage.setItem(readKey, String(nowTs));
                        window.dispatchEvent(new CustomEvent('gomsn-notif-read', { detail: { ts: nowTs } }));
                    } catch (e) {}
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: 'Semua notifikasi ditandai sudah dibaca.' });
                    }
                }
            };
        }
    </script>

    <div class="space-y-5 w-full pb-20" x-data="gomsnInboxPage()">
        <!-- 1. Header Banner (Mengikuti Warna Tema Aplikasi) -->
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
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Pusat Notifikasi
                    </h1>
                    <p class="text-xs text-white/80 mt-0.5 leading-snug">
                        Pengumuman perusahaan, status pengajuan, lembur, tiket, dan pengingat absensi Anda
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
                    <span>Tandai Dibaca</span>
                </button>
                <a href="{{ route('portal.settings') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white text-slate-800 hover:bg-slate-100 transition shadow-sm">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Atur Notifikasi</span>
                </a>
            </div>
        </div>

        <!-- 2. Filter Kategori & Pencarian -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs space-y-3">
            <form method="GET" action="{{ route('portal.notifications.inbox') }}" class="flex flex-col sm:flex-row gap-2.5">
                <input type="hidden" name="category" value="{{ $activeCategory }}">
                <div class="relative flex-1">
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari judul atau isi notifikasi..."
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
        </div>

        <!-- 3. Daftar Notifikasi -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden divide-y divide-slate-100">
            @forelse ($notifications as $notif)
                @php
                    $catMeta = match ($notif['category']) {
                        'pengumuman' => ['label' => 'Pengumuman', 'bg' => 'bg-teal-50 text-teal-700 border-teal-200'],
                        'pengajuan'  => ['label' => 'Pengajuan',  'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
                        'lembur'     => ['label' => 'Lembur',     'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
                        'tiket'      => ['label' => 'Tiket',      'bg' => 'bg-rose-50 text-rose-700 border-rose-200'],
                        'absensi'    => ['label' => 'Absensi',    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        default      => ['label' => 'Info',       'bg' => 'bg-slate-100 text-slate-700 border-slate-200'],
                    };
                @endphp
                <div class="p-4 sm:p-5 hover:bg-slate-50/70 transition flex items-start gap-3.5"
                     :class="isUnread({{ $notif['timestamp'] }}) ? 'bg-teal-50/25' : ''">
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-2xl border flex items-center justify-center shrink-0 mt-0.5 {{ $catMeta['bg'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span x-show="isUnread({{ $notif['timestamp'] }})" x-cloak class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">{{ $notif['title'] }}</h3>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0 {{ $catMeta['bg'] }}">
                                {{ $catMeta['label'] }}
                            </span>
                        </div>

                        @if ($notif['body'])
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed whitespace-pre-line">{{ $notif['body'] }}</p>
                        @endif

                        <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-500">{{ $notif['sender_name'] }}</span>
                                <span>&bull;</span>
                                <span>{{ $notif['sent_at'] ? $notif['sent_at']->diffForHumans() : '-' }}</span>
                                @if ($notif['sent_at'])
                                    <span class="hidden sm:inline text-slate-400">({{ $notif['sent_at']->format('d M Y, H:i') }})</span>
                                @endif
                            </div>

                            @if (!empty($notif['link']))
                                <a href="{{ $notif['link'] }}"
                                   class="inline-flex items-center gap-1 text-teal-600 hover:text-teal-700 font-bold">
                                    <span>Buka Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-14 px-6 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Belum Ada Notifikasi</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Semua pengumuman dan pembaruan status pengajuan, lembur, tiket, maupun absensi Anda akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
