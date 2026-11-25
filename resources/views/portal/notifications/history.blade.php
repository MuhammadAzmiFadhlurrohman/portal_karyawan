<x-layouts.app title="Riwayat Siaran Notifikasi — PT MSN">
    <div x-data="{
        detailModalOpen: false,
        selectedLog: null,
        openDetail(logData) {
            this.selectedLog = logData;
            this.detailModalOpen = true;
        },
        closeDetail() {
            this.detailModalOpen = false;
            this.selectedLog = null;
        }
    }" class="space-y-5 w-full pb-16">

        <!-- Top Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-md py-3.5 px-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Back Button + Icon + Title & Subtitle -->
            <div class="relative z-10 flex items-center gap-3 min-w-0">
                <a href="{{ route('portal.notifications.index') }}"
                   class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white/90 hover:text-white transition shrink-0 active:scale-95 shadow-2xs"
                   title="Kembali ke Form Kirim Notifikasi">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                <div class="w-9 h-9 rounded-xl bg-teal-400/20 border border-teal-300/30 flex items-center justify-center text-cyan-200 shrink-0 shadow-inner">
                    <svg class="w-5 h-5 text-cyan-300 drop-shadow-[0_0_6px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-heading text-sm sm:text-base font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                            <span>Riwayat Siaran Notifikasi</span>
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-200 border border-teal-400/30">
                            Log Broadcast FCM &amp; Web
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-teal-100/80 leading-tight truncate mt-0.5">
                        Daftar lengkap pesan broadcast dan push notifikasi yang telah disiarkan ke pengguna.
                    </p>
                </div>
            </div>

            <!-- Right: Action Button to Compose -->
            <div class="relative z-10 flex items-center gap-2.5 shrink-0">
                <a href="{{ route('portal.notifications.index') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-teal-400 to-[#155E63] hover:from-teal-300 hover:to-[#0f4d51] text-white text-xs font-bold flex items-center gap-1.5 shadow-md active:scale-95 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Kirim Siaran Baru</span>
                </a>
            </div>
        </div>

        <!-- Sleek Compact Metrics Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">
            <!-- 1. Total Siaran Sistem -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-purple-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-100/80 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Total Riwayat</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $totalBroadcastsCount }}</span>
                </div>
            </div>

            <!-- 2. Broadcast Hari Ini -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100/80 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Siaran Hari Ini</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $broadcastsTodayCount }}</span>
                </div>
            </div>

            <!-- 3. Target Karyawan Aktif -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-teal-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100/80 text-teal-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Karyawan Aktif</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $totalActiveEmployees }}</span>
                </div>
            </div>

            <!-- 4. Hasil Filter / Ditampilkan -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-sky-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-100/80 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Data Ditampilkan</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $logs->total() }}</span>
                </div>
            </div>
        </div>

        <!-- MAIN CARD: Filter Toolbar & History Table -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-4 sm:p-6 space-y-4">
            
            <!-- Filter Toolbar -->
            <form method="GET" action="{{ route('portal.notifications.history') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <!-- Search Input -->
                    <div class="relative sm:col-span-1">
                        <input type="text"
                               name="search"
                               value="{{ $currentSearch }}"
                               placeholder="Cari judul, pesan, pengirim..."
                               class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Category Type Filter -->
                    <div>
                        <select name="type" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer">
                            <option value="">Semua Kategori</option>
                            <option value="info" {{ $currentType === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="announcement" {{ $currentType === 'announcement' ? 'selected' : '' }}>Pengumuman</option>
                            <option value="warning" {{ $currentType === 'warning' ? 'selected' : '' }}>Penting / Jadwal</option>
                            <option value="alert" {{ $currentType === 'alert' ? 'selected' : '' }}>Mendesak / Alert</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <input type="date"
                               name="date"
                               value="{{ $currentDate }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer">
                    </div>
                </div>

                <!-- Action Filter Buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#0e484c] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Filter</span>
                    </button>

                    @if ($currentSearch || $currentType || $currentDate)
                        <a href="{{ route('portal.notifications.history') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition cursor-pointer">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Table Container -->
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 select-none">
                        <tr>
                            <th class="px-3.5 py-3">Waktu Kirim</th>
                            <th class="px-3.5 py-3">Pengirim</th>
                            <th class="px-3.5 py-3">Target Penerima</th>
                            <th class="px-3.5 py-3">Kategori</th>
                            <th class="px-3.5 py-3">Judul &amp; Pesan</th>
                            <th class="px-3.5 py-3 text-center">Status</th>
                            <th class="px-3.5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($logs as $log)
                            @php
                                $payload = is_array($log->payload) ? $log->payload : json_decode($log->payload, true) ?? [];
                                $title = $payload['title'] ?? '-';
                                $body = $payload['body'] ?? ($payload['message'] ?? '-');
                                $type = $payload['type'] ?? 'info';
                                $senderName = $payload['sender_name'] ?? ($log->user?->name ?? 'Administrator');
                                $targetCount = $payload['target_count'] ?? null;
                                $link = $payload['link'] ?? null;
                                $recipientsPreview = $payload['recipient_preview'] ?? [];
                                $pushSentCount = $payload['push_sent_count'] ?? 0;

                                $logPayloadJson = json_encode([
                                    'id' => $log->id,
                                    'title' => $title,
                                    'body' => $body,
                                    'type' => $type,
                                    'sender_name' => $senderName,
                                    'recipient' => $log->recipient ?: ($targetCount ? $targetCount . ' Karyawan' : 'Semua Karyawan'),
                                    'target_count' => $targetCount,
                                    'push_sent_count' => $pushSentCount,
                                    'link' => $link,
                                    'date_human' => $log->created_at?->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i:s') . ' WIB',
                                    'diff_human' => $log->created_at?->diffForHumans(),
                                    'recipients_preview' => $recipientsPreview,
                                ]);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Waktu Kirim -->
                                <td class="px-3.5 py-3 whitespace-nowrap text-slate-500">
                                    <div class="font-semibold text-slate-800">{{ $log->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $log->created_at?->timezone('Asia/Jakarta')->format('H:i:s') }} WIB <span class="text-teal-600 font-medium">({{ $log->created_at?->diffForHumans() }})</span></div>
                                </td>

                                <!-- Pengirim -->
                                <td class="px-3.5 py-3 whitespace-nowrap font-semibold text-slate-800">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 font-bold text-[10px] flex items-center justify-center">
                                            {{ strtoupper(substr($senderName, 0, 1)) }}
                                        </div>
                                        <span class="text-xs">{{ $senderName }}</span>
                                    </div>
                                </td>

                                <!-- Target Penerima -->
                                <td class="px-3.5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        {{ $log->recipient ?: ($targetCount ? $targetCount . ' Karyawan' : 'Semua Karyawan') }}
                                    </span>
                                </td>

                                <!-- Kategori -->
                                <td class="px-3.5 py-3 whitespace-nowrap">
                                    @if ($type === 'announcement')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Pengumuman</span>
                                    @elseif ($type === 'warning')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Penting</span>
                                    @elseif ($type === 'alert')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Mendesak</span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-teal-100 text-teal-800 border border-teal-300">Info</span>
                                    @endif
                                </td>

                                <!-- Judul & Pesan -->
                                <td class="px-3.5 py-3 max-w-sm">
                                    <div class="font-bold text-slate-800 leading-snug line-clamp-1 text-xs">{{ $title }}</div>
                                    <div class="text-[11px] text-slate-500 leading-tight line-clamp-2 mt-0.5">{{ $body }}</div>
                                    @if (!empty($link))
                                        <div class="text-[9px] text-teal-600 font-mono mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            <span class="truncate">{{ $link }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-3.5 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Terkirim
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-3.5 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Tombol Detail -->
                                        <button type="button"
                                                @click="openDetail({{ $logPayloadJson }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-teal-700 hover:bg-teal-50 transition cursor-pointer"
                                                title="Lihat Detail Pesan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form method="POST" action="{{ route('portal.notifications.destroy', $log) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat siaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Riwayat">
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
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-xs">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2.5">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Tidak ada riwayat siaran ditemukan.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Coba ubah kata kunci filter atau tanggal pencarian Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($logs->hasPages())
                <div class="pt-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- DETAIL MODAL DIALOG (Alpine.js) -->
        <div x-show="detailModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div x-show="detailModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                 @click="closeDetail()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="detailModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                    
                    <!-- Header Modal -->
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between"
                         style="background: linear-gradient(90deg, #022836 0%, #084c57 100%);">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-teal-400/20 text-cyan-200 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-white" id="modal-title">Detail Siaran Notifikasi</h3>
                                <p class="text-[10px] text-teal-200" x-text="selectedLog?.date_human"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeDetail()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="p-5 space-y-4 text-xs text-slate-700 max-h-[70vh] overflow-y-auto">
                        <!-- Judul & Kategori -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Judul Notifikasi</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800 border border-emerald-300': selectedLog?.type === 'announcement',
                                          'bg-amber-100 text-amber-800 border border-amber-300': selectedLog?.type === 'warning',
                                          'bg-rose-100 text-rose-800 border border-rose-300': selectedLog?.type === 'alert',
                                          'bg-teal-100 text-teal-800 border border-teal-300': selectedLog?.type === 'info'
                                      }"
                                      x-text="selectedLog?.type"></span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900" x-text="selectedLog?.title"></h4>
                        </div>

                        <!-- Isi Pesan -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Isi Pesan Siaran</span>
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200 text-slate-800 text-xs leading-relaxed whitespace-pre-wrap select-text"
                                 x-text="selectedLog?.body"></div>
                        </div>

                        <!-- Tautan Tindakan (Jika ada) -->
                        <template x-if="selectedLog?.link">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tautan / Deep Link</span>
                                <div class="p-2.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 font-mono text-[11px] flex items-center justify-between gap-2">
                                    <span class="truncate" x-text="selectedLog?.link"></span>
                                    <a :href="selectedLog?.link" target="_blank" class="px-2 py-0.5 rounded bg-[#155E63] text-white text-[10px] font-bold shrink-0 hover:bg-[#0e484c]">
                                        Buka ↗
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- Info Metadata Pengirim & Target -->
                        <div class="grid grid-cols-2 gap-2.5 pt-1">
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-[10px] text-slate-400 block font-medium">Pengirim</span>
                                <span class="font-bold text-slate-800 text-xs" x-text="selectedLog?.sender_name"></span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                <span class="text-[10px] text-slate-400 block font-medium">Penerima</span>
                                <span class="font-bold text-slate-800 text-xs truncate block" x-text="selectedLog?.recipient"></span>
                            </div>
                        </div>

                        <!-- Preview Daftar Nama Penerima (Jika ada) -->
                        <template x-if="selectedLog?.recipients_preview && selectedLog.recipients_preview.length > 0">
                            <div class="space-y-1 pt-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Daftar Sebagian Karyawan Penerima</span>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="name in selectedLog.recipients_preview" :key="name">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[10px] text-slate-700" x-text="name"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Footer Modal -->
                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                        <button type="button" @click="closeDetail()" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
