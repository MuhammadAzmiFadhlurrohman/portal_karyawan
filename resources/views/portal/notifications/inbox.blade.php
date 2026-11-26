<x-layouts.app title="Perlu Tindakan Anda">
    @php
        $totalActions = collect($actionItems)->sum('count');
    @endphp

    <div class="space-y-5 w-full pb-20">
        <!-- 1. Header Banner (Full Width & Mengikuti Warna Tema Aplikasi) -->
        <div class="gomsn-themed-banner relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.28)] p-4 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 select-none"
             style="background: var(--gomsn-header-bg) !important;">
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
                            Perlu Tindakan Anda
                        </h1>
                        @if ($totalActions > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-amber-400 text-slate-900 shadow-xs">
                                {{ $totalActions }} Menunggu Tindakan
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-white/80 mt-0.5 leading-snug">
                        Daftar persetujuan, tugas operasional, dan permintaan yang memerlukan tindakan segera dari Anda
                    </p>
                </div>
            </div>

            @if ($totalActions > 0)
                <div class="relative z-10 flex items-center gap-2 shrink-0">
                    <span class="px-3 py-1.5 rounded-xl bg-white/15 border border-white/20 text-white text-xs font-bold">
                        {{ count($actionItems) }} Kategori Tugas
                    </span>
                </div>
            @endif
        </div>

        <!-- 2. DAFTAR TINDAKAN OPERASIONAL -->
        @if (! empty($actionItems))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
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
                       class="group rounded-2xl bg-white border border-slate-200/90 {{ $actColors['border'] }} p-4 sm:p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between gap-4 cursor-pointer">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-2xl border flex items-center justify-center shrink-0 {{ $actColors['icon'] }} shadow-2xs">
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
                                    <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-teal-700 transition-colors">{{ $act['title'] }}</h3>
                                    <p class="text-xs text-slate-500 mt-1 leading-snug">{{ $act['desc'] }}</p>
                                </div>
                            </div>
                            <span class="min-w-[26px] h-6 px-2 rounded-full text-xs font-black flex items-center justify-center shrink-0 shadow-2xs {{ $actColors['badge'] }}">
                                {{ $act['count'] > 99 ? '99+' : $act['count'] }}
                            </span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-teal-600 group-hover:text-teal-700">
                            <span>{{ $act['cta'] }}</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Tampilan Kosong Jika Tidak Ada Tindakan Menunggu -->
            <div class="rounded-2xl bg-white border border-slate-200/90 p-8 sm:p-14 text-center shadow-xs space-y-3">
                <div class="w-16 h-16 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-base sm:text-lg font-bold text-slate-800">Semua Tindakan Selesai! 🎉</h3>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Tidak ada tugas, persetujuan pengajuan, verifikasi lembur, atau tiket gangguan yang memerlukan respon Anda saat ini.
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
