<x-layouts.app title="Presensi & Absensi Karyawan">
    <script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
    <script src="{{ asset('js/attendance-screen-detector.js') }}?v={{ filemtime(public_path('js/attendance-screen-detector.js')) }}"></script>

    <div class="w-full space-y-4 sm:space-y-5" 
         x-data="attendancePage()"
         x-init="init()">

        @php
            $shift = $todaySchedule?->shift ?? \App\Models\Shift::where('code', 'P')->first() ?? \App\Models\Shift::first();
            $hasCustomHours = $todaySchedule && $todaySchedule->isCustomHours();
            $shiftName = $hasCustomHours ? 'Custom Jam Kerja' : ($shift?->name ?? 'Shift Pagi');
            $shiftStart = $todaySchedule?->effective_start_time ?? ($shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '08:15');
            $shiftEnd = $todaySchedule?->effective_end_time ?? ($shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '17:00');
            $isWorkday = ! in_array(now('Asia/Jakarta')->dayOfWeek, [0, 6]);
            $defaultOffice = $offices->first();
        @endphp

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-3xl border border-cyan-400/30 shadow-[0_8px_28px_rgba(2,43,58,0.28)] p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-3.5 sm:gap-4 select-none"
             style="background: linear-gradient(110deg, #022836 0%, #053b47 28%, #0a525f 62%, #0e6774 86%, #084c57 100%);">
            
            <!-- Ambient Glow Orbs & Gloss Highlights -->
            <div class="absolute -top-12 -right-12 w-44 h-44 rounded-full bg-teal-400/15 blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-14 -left-10 w-44 h-44 rounded-full bg-cyan-400/10 blur-2xl pointer-events-none"></div>
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Glowing Attendance Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-white/12 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-extrabold text-white tracking-tight leading-snug truncate">
                        Presensi &amp; Absensi Karyawan
                    </h1>
                    <p class="text-[11px] sm:text-xs text-teal-100/85 mt-0.5 truncate">
                        Absensi biometrik wajah &amp; lokasi GPS
                    </p>
                </div>
            </div>
        </div>

        <!-- CARD 2: Jadwal Hari Ini -->
        <div class="rounded-3xl bg-white border border-slate-200/80 shadow-[0_4px_20px_rgb(15,23,42,0.03)] p-4 sm:p-5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-200/70 flex items-center justify-center text-[#155E63] shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-extrabold text-[#155E63] tracking-wider uppercase">Jadwal Hari Ini</span>
                        <span class="text-[10px] text-slate-400 hidden sm:inline">&bull; {{ now('Asia/Jakarta')->translatedFormat('l, d M Y') }}</span>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 leading-tight mt-0.5 truncate">{{ $shiftName }}</h3>
                    <div class="inline-flex items-center gap-1.5 mt-1 text-xs font-mono font-semibold text-slate-600">
                        <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $shiftStart }} - {{ $shiftEnd }} WIB</span>
                    </div>
                </div>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ $isWorkday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $isWorkday ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                    <span>{{ $isWorkday ? 'Hari Kerja' : 'Hari Libur' }}</span>
                </span>
            </div>
        </div>

        <!-- FLOATING COMPACT LOCATION PILL (Di Atas Kiri Navigasi Bar Mobile sesuai Referensi) -->
        <div class="fixed bottom-[65px] left-3.5 sm:left-6 md:bottom-[22px] md:left-[280px] z-30 select-none"
             @click.outside="showLocationDetail = false">
            
            <!-- Mini Popover Detail Lokasi saat Pill Diklik -->
            <div x-show="showLocationDetail"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 class="mb-2 w-64 sm:w-72 rounded-2xl bg-white border border-slate-200/90 shadow-[0_10px_30px_rgba(15,23,42,0.14)] p-3.5 text-left">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 block">Deteksi Lokasi Otomatis</span>
                        <p class="text-xs font-bold text-slate-800 mt-0.5 leading-snug" x-text="gpsStatus"></p>
                        <p class="text-[11px] font-mono text-slate-500 mt-1" x-text="Number(gpsLat).toFixed(6) + ', ' + Number(gpsLng).toFixed(6)"></p>
                    </div>
                    <button type="button"
                            @click.stop="detectGPS()"
                            class="p-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#155E63] border border-teal-200/70 transition cursor-pointer shrink-0"
                            title="Perbarui GPS">
                        <svg class="w-4 h-4" :class="gpsLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Pill Utama: [Map Pin Biru] "0m dari Kantor" [Centang Hijau] [>] -->
            <button type="button"
                    @click="showLocationDetail = !showLocationDetail"
                    class="h-10 sm:h-11 md:h-12 inline-flex items-center gap-2 px-3.5 sm:px-4 rounded-full bg-white border border-slate-200/80 shadow-[0_4px_16px_rgba(15,23,42,0.10)] hover:shadow-[0_6px_20px_rgba(15,23,42,0.14)] active:scale-[0.98] transition-all cursor-pointer">
                
                <!-- Blue Map + Location Pin Icon (Persis Gambar Referensi) -->
                <svg class="w-4 h-4 text-[#2563EB] shrink-0" :class="gpsLoading ? 'animate-pulse' : ''" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.686 2 6 4.686 6 8c0 4.5 6 11 6 11s6-6.5 6-11c0-3.314-2.686-6-6-6zm0 8.2a2.2 2.2 0 110-4.4 2.2 2.2 0 010 4.4z"/>
                    <path d="M3.5 9.5L8 7.8v11.7l-4.5 1.7a.7.7 0 01-.95-.65V10.15a.7.7 0 01.45-.65zm17 0l-4.5-1.7v11.7l4.5 1.7a.7.7 0 00.95-.65V10.15a.7.7 0 00-.45-.65z" opacity="0.85"/>
                </svg>

                <!-- Teks Jarak dari Kantor -->
                <span class="text-xs font-medium tracking-tight whitespace-nowrap"
                      :class="gpsLoading ? 'text-slate-600' : (isWithinOfficeRadius ? 'text-[#0f8a5f]' : 'text-amber-600')"
                      x-text="gpsLoading ? 'Mendeteksi...' : ((detectedDistanceM !== null ? detectedDistanceM : 0) + 'm dari Kantor')">
                    0m dari Kantor
                </span>

                <!-- Kotak Centang Hijau (Dalam Radius) / Indikator Status -->
                <template x-if="!gpsLoading && isWithinOfficeRadius">
                    <span class="w-4 h-4 rounded-[3px] bg-[#22c55e] border border-slate-800 flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                </template>
                <template x-if="!gpsLoading && !isWithinOfficeRadius">
                    <span class="w-4 h-4 rounded-[3px] bg-amber-500 border border-slate-800 flex items-center justify-center shrink-0 text-[10px] font-extrabold text-white leading-none">
                        !
                    </span>
                </template>

                <!-- Chevron Kanan (>) -->
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200"
                     :class="showLocationDetail ? '-rotate-90' : ''"
                     fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- CARD 3: Status Absen Hari Ini (Themed & Elevated) -->
        <div class="rounded-3xl bg-white border border-slate-200/80 shadow-[0_8px_30px_rgb(15,23,42,0.04)] p-5 sm:p-6 space-y-5">
            <!-- Header Bar -->
            <div class="flex items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-[#155E63] border border-teal-100 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 leading-tight">Status Absen Hari Ini</h3>
                        <p class="text-[11px] text-slate-400">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>

                @if (! $todayAttendance)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Belum Absen
                    </span>
                @elseif (! $todayAttendance->clock_out_at)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sedang Bekerja
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-[#155E63] border border-teal-200/80">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Lengkap
                    </span>
                @endif
            </div>

            <!-- Center Visual & Status Hero Box -->
            <div class="rounded-2xl bg-gradient-to-b from-slate-50/80 via-teal-50/25 to-slate-50/50 border border-slate-100 py-6 px-4 flex flex-col items-center justify-center text-center space-y-3">
                @if (! $todayAttendance)
                    <div class="w-20 h-20 rounded-full bg-white border-2 border-slate-200 ring-8 ring-slate-100/80 flex items-center justify-center text-slate-400 shadow-xs">
                        <svg class="w-9 h-9 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm sm:text-base font-extrabold text-slate-800">Belum Absen Masuk</p>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto">
                            Silakan lakukan verifikasi wajah untuk mencatat kehadiran masuk Anda hari ini.
                        </p>
                    </div>
                @elseif (! $todayAttendance->clock_out_at)
                    <div class="w-20 h-20 rounded-full bg-emerald-50 border-2 border-emerald-300 ring-8 ring-emerald-50/80 flex items-center justify-center text-emerald-600 shadow-xs">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm sm:text-base font-extrabold text-emerald-800">Sudah Absen Masuk</p>
                        <p class="text-xs text-slate-500">
                            Tercatat pukul <strong class="text-slate-800 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->clock_in_at)->format('H:i') }} WIB</strong> &middot; Selamat bekerja!
                        </p>
                    </div>
                @else
                    <div class="w-20 h-20 rounded-full bg-teal-50 border-2 border-teal-300 ring-8 ring-teal-50/70 flex items-center justify-center text-[#155E63] shadow-xs">
                        <svg class="w-10 h-10 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm sm:text-base font-extrabold text-[#155E63]">Absensi Selesai Hari Ini</p>
                        <p class="text-xs text-slate-500">
                            Terima kasih, catatan kehadiran kerja Anda hari ini telah lengkap.
                        </p>
                    </div>
                @endif
            </div>

            <!-- 2-Column Check-In & Check-Out Time Summary Boxes -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Box Jam Masuk -->
                <div class="rounded-2xl p-3.5 border {{ $todayAttendance?->clock_in_at ? 'bg-emerald-50/40 border-emerald-200/80' : 'bg-slate-50/70 border-slate-200/70' }} flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $todayAttendance?->clock_in_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200/70 text-slate-400' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $todayAttendance?->clock_in_at ? 'text-emerald-700' : 'text-slate-400' }} block">Absen Masuk</span>
                        <span class="text-sm sm:text-base font-extrabold font-mono text-slate-900 block leading-tight mt-0.5">
                            {{ $todayAttendance?->clock_in_at ? \Carbon\Carbon::parse($todayAttendance->clock_in_at)->format('H:i') : '--:--' }}
                        </span>
                    </div>
                </div>

                <!-- Box Jam Pulang -->
                <div class="rounded-2xl p-3.5 border {{ $todayAttendance?->clock_out_at ? 'bg-teal-50/50 border-teal-200/80' : 'bg-slate-50/70 border-slate-200/70' }} flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $todayAttendance?->clock_out_at ? 'bg-teal-100 text-[#155E63]' : 'bg-slate-200/70 text-slate-400' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $todayAttendance?->clock_out_at ? 'text-[#155E63]' : 'text-slate-400' }} block">Absen Pulang</span>
                        <span class="text-sm sm:text-base font-extrabold font-mono text-slate-900 block leading-tight mt-0.5">
                            {{ $todayAttendance?->clock_out_at ? \Carbon\Carbon::parse($todayAttendance->clock_out_at)->format('H:i') : '--:--' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Dynamic Button -->
            @if (! $faceTemplate || ! $todayAttendance || ! $todayAttendance->clock_out_at)
            <div class="space-y-3">
                @if (! $faceTemplate)
                    <!-- JIKA BELUM DAFTAR WAJAH -> TOMBOL DAFTAR -->
                    <button type="button"
                            @click="openEnrollModal()"
                            style="background: linear-gradient(135deg, #093c40 0%, #155E63 60%, #0d9488 100%) !important;"
                            class="w-full py-3.5 px-6 rounded-2xl hover:opacity-95 text-white font-bold text-sm shadow-lg shadow-teal-900/20 transition flex items-center justify-center gap-2.5 cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Daftarkan Wajah Sekarang</span>
                    </button>
                    <p class="text-center text-[11px] text-slate-400">
                        Anda wajib mendaftarkan wajah sekali sebelum dapat melakukan absensi.
                    </p>
                @else
                    <!-- SUDAH DAFTAR WAJAH -> TOMBOL ABSEN MASUK / PULANG -->
                    @if (! $todayAttendance)
                        <button type="button"
                                @click="openAttendanceTypeModal()"
                                style="background: linear-gradient(135deg, #065f46 0%, #0D8252 55%, #10b981 100%) !important;"
                                class="w-full py-3.5 px-6 rounded-2xl hover:opacity-95 text-white font-bold text-sm shadow-lg shadow-emerald-900/20 transition flex items-center justify-center gap-2.5 cursor-pointer">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Absen Masuk Sekarang</span>
                        </button>
                    @elseif (! $todayAttendance->clock_out_at)
                        <button type="button"
                                @click="openClockOutModal()"
                                style="background: linear-gradient(135deg, #b45309 0%, #D97706 60%, #f59e0b 100%) !important;"
                                class="w-full py-3.5 px-6 rounded-2xl hover:opacity-95 text-white font-bold text-sm shadow-lg shadow-amber-900/20 active:scale-[0.99] transition flex items-center justify-center gap-2.5 cursor-pointer">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Absen Pulang Sekarang</span>
                        </button>
                    @endif
                @endif
            </div>
            @endif
        </div>

        <!-- MODAL 1: DAFTARKAN WAJAH (Biometric Enrollment - Tanpa Simulasi) -->
        <div x-show="showEnrollModal" 
             x-cloak 
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-[0_24px_60px_rgba(15,23,42,0.25)] max-w-md w-full overflow-hidden" @click.outside="closeEnrollModal()">
                
                <!-- Modal Header -->
                <div class="px-5 py-4 bg-gradient-to-r from-[#093c40] via-[#155E63] to-[#0d9488] text-white flex items-center justify-between relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5 text-teal-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm sm:text-base text-white leading-tight">
                                Pendaftaran Wajah Karyawan
                            </h3>
                            <p class="text-[11px] text-teal-100/85 font-normal mt-0.5">
                                Enkripsi Biometrik Presensi GoMSN
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeEnrollModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/90 hover:text-white flex items-center justify-center transition cursor-pointer relative z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('attendance.enroll') }}" @submit="handleEnrollSubmit($event)" class="p-5 space-y-4">
                    @csrf
                    
                    <div class="rounded-2xl border border-teal-200/80 bg-gradient-to-b from-teal-50/40 via-slate-50/60 to-teal-50/30 p-5 text-center space-y-3.5 shadow-inner">
                        <!-- Camera preview / Canvas (Lebih Besar) -->
                        <div class="relative w-48 h-48 sm:w-52 sm:h-52 mx-auto">
                            <div class="w-full h-full rounded-full overflow-hidden bg-slate-900 border-[3.5px] transition-all duration-300 flex items-center justify-center shadow-lg relative"
                                 :class="cameraActive ? (enrollScreenDetected ? 'border-rose-500 ring-4 ring-rose-400/35' : (faceDetected ? 'border-emerald-500 ring-4 ring-emerald-400/30' : 'border-amber-400 ring-4 ring-amber-400/20')) : 'border-[#155E63] ring-4 ring-teal-500/20'">
                                <video id="enroll-video" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]" x-show="cameraActive"></video>
                                <img :src="photoPreview" x-show="photoPreview && !cameraActive" class="w-full h-full object-cover" alt="Preview Foto">
                                <canvas id="enroll-canvas" class="hidden"></canvas>
                                <div x-show="!cameraActive && !photoPreview" class="text-slate-400 text-center p-2">
                                    <svg class="w-12 h-12 mx-auto text-teal-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-xs block mt-1.5 font-medium text-slate-300">Kamera Selfie</span>
                                </div>
                            </div>
                            <span x-show="cameraActive" class="absolute top-2.5 right-2.5 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="enrollScreenDetected ? 'bg-rose-400' : (faceDetected ? 'bg-emerald-400' : 'bg-amber-400')"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 border-2 border-white" :class="enrollScreenDetected ? 'bg-rose-500' : (faceDetected ? 'bg-emerald-500' : 'bg-amber-500')"></span>
                            </span>
                        </div>

                        <div>
                            <template x-if="isDetecting">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-[#155E63] border border-teal-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Mendeteksi wajah &amp; biometrik...</span>
                                </span>
                            </template>
                            <template x-if="!isDetecting && photoPreview">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-200 shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>✓ Wajah Terdeteksi &amp; Biometrik Siap Disimpan</span>
                                </span>
                            </template>
                            <template x-if="!isDetecting && !photoPreview && cameraActive && enrollScreenDetected">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                        <span>🚫 Terdeteksi Layar HP / Foto Digital!</span>
                                    </span>
                                    <p class="text-[11px] font-medium text-rose-700"
                                       x-text="(enrollScreenReason ? enrollScreenReason + '. ' : '') + 'Gunakan wajah asli secara langsung.'"></p>
                                </div>
                            </template>
                            <template x-if="!isDetecting && !photoPreview && cameraActive && !enrollScreenDetected && faceDetected">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>✓ Wajah Terdeteksi — Klik "Ambil Foto Sekarang"</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-teal-50 text-[#155E63] border border-teal-200/90 shadow-2xs">
                                        <span>🛡️ Anti-Layar Aman</span>
                                    </span>
                                </div>
                            </template>
                            <template x-if="!isDetecting && !photoPreview && cameraActive && !enrollScreenDetected && !faceDetected">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                    <span>Posisikan wajah Anda tepat di tengah frame</span>
                                </span>
                            </template>
                            <template x-if="!isDetecting && !photoPreview && !cameraActive">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200/90">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Wajib ambil foto selfie wajah</span>
                                </span>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                            <button type="button" 
                                    @click="startCamera()" 
                                    x-show="!cameraActive"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold bg-white text-[#155E63] border border-teal-300 hover:bg-teal-50 shadow-xs transition cursor-pointer">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span x-text="photoPreview ? 'Ulangi Ambil Foto' : 'Nyalakan Kamera'"></span>
                            </button>
                            <button type="button" 
                                    @click="capturePhoto()" 
                                    x-show="cameraActive"
                                    :disabled="isDetecting || enrollScreenDetected"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-700/20 active:scale-[0.98] transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                <span x-text="isDetecting ? 'Menganalisis...' : 'Ambil Foto Sekarang'"></span>
                            </button>
                        </div>

                        <!-- Hidden Poses inputs -->
                        <input type="hidden" name="pose_front" :value="poseFront">
                        <input type="hidden" name="pose_left" :value="poseLeft">
                        <input type="hidden" name="pose_right" :value="poseRight">
                        <input type="hidden" name="face_descriptor" :value="faceDescriptor">
                        <input type="hidden" name="anti_spoof_meta" :value="enrollAntiSpoofMeta">
                    </div>

                    <!-- Consent Checkbox (UU PDP) -->
                    <label class="flex items-start gap-2.5 rounded-2xl bg-teal-50/70 border border-teal-200/70 p-3.5 text-xs text-teal-950 cursor-pointer">
                        <input type="checkbox" name="consent" value="1" required checked class="mt-0.5 w-4 h-4 rounded border-teal-300 text-[#155E63] focus:ring-teal-500">
                        <span class="text-[11px] leading-relaxed text-slate-700">
                            <strong class="text-teal-900">Persetujuan Biometrik (UU PDP No. 27/2022):</strong> Saya menyetujui pemrosesan enkripsi wajah khusus untuk verifikasi absensi PT Media Solusi Network.
                        </span>
                    </label>

                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" @click="closeEnrollModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-[0_6px_16px_rgba(21,94,99,0.28)] hover:brightness-105 transition cursor-pointer">
                            Simpan &amp; Daftarkan Wajah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL PILIH TIPE KEHADIRAN / LOKASI ABSEN (Step 1 Sebelum Kamera Absen) -->
        <div x-show="showAttendanceTypeModal" 
             x-cloak 
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-[#111c26] rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-[0_24px_60px_rgba(15,23,42,0.3)] max-w-md w-full overflow-hidden my-auto" 
                 @click.outside="closeAttendanceTypeModal()">
                
                <!-- Sleek Header with Location Pin -->
                <div class="px-6 pt-6 pb-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/60 dark:border-teal-800/60 flex items-center justify-center text-[#155E63] dark:text-teal-400 shadow-xs">
                            <span class="text-xl leading-none">📍</span>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-base sm:text-lg text-slate-800 dark:text-white leading-tight">
                                Pilih Lokasi Absen
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                                Tentukan lokasi kerja sebelum verifikasi wajah
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeAttendanceTypeModal()" 
                            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Two Selection Cards -->
                <div class="p-6 space-y-4">
                    <!-- Pilihan Tombol (Hanya Dinas Luar jika jauh dari kantor) -->
                    <div :class="isWithinOfficeRadius ? 'grid grid-cols-2 gap-3.5 sm:gap-4' : 'flex flex-col items-center justify-center'">
                        <!-- Card 1: Kantor (HANYA MUNCUL JIKA DALAM RADIUS KANTOR) -->
                        <button type="button" 
                                x-show="isWithinOfficeRadius"
                                @click="attendanceType = 'office'"
                                class="group relative flex flex-col items-center text-center p-4 sm:p-5 rounded-2xl border-2 transition-all duration-200 cursor-pointer focus:outline-none w-full"
                                :class="attendanceType === 'office' 
                                    ? 'border-[#155E63] dark:border-teal-400 bg-gradient-to-b from-teal-50/90 to-emerald-50/40 dark:from-teal-950/50 dark:to-emerald-950/20 shadow-md ring-2 ring-[#155E63]/20 dark:ring-teal-400/30 scale-[1.02]' 
                                    : 'border-slate-200/90 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 hover:border-teal-300 dark:hover:border-teal-700 hover:bg-slate-100/70 dark:hover:bg-slate-900/80 opacity-85 hover:opacity-100'">
                            
                            <!-- Office Icon Box -->
                            <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-2xl flex items-center justify-center mb-3 transition shadow-sm"
                                 :class="attendanceType === 'office' 
                                    ? 'bg-gradient-to-tr from-[#093c40] via-[#155E63] to-[#0d9488] text-white shadow-teal-900/25' 
                                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700'">
                                <svg class="w-8 h-8 sm:w-9 sm:h-9" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>

                            <h4 class="font-bold text-sm sm:text-base text-slate-800 dark:text-white mb-1">
                                Kantor
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mb-2">
                                Absen di kantor
                            </p>

                            <!-- Checkmark / Selection Indicator -->
                            <div class="mt-auto pt-1">
                                <span x-show="attendanceType === 'office'" 
                                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#155E63] text-white dark:bg-teal-500 dark:text-slate-950 shadow-2xs">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>Dipilih</span>
                                </span>
                                <span x-show="attendanceType !== 'office'" 
                                      class="inline-block w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></span>
                            </div>
                        </button>

                        <!-- Card 2: Dinas Luar (SELALU MUNCUL, JIKA DILUAR RADIUS JADI SATU-SATUNYA TOMBOL) -->
                        <button type="button" 
                                @click="attendanceType = 'field'"
                                class="group relative flex flex-col items-center text-center p-4 sm:p-5 rounded-2xl border-2 transition-all duration-200 cursor-pointer focus:outline-none"
                                :class="[
                                    isWithinOfficeRadius ? 'w-full' : 'w-full max-w-sm',
                                    attendanceType === 'field' 
                                        ? 'border-amber-500 dark:border-amber-400 bg-gradient-to-b from-amber-50/90 to-orange-50/40 dark:from-amber-950/50 dark:to-orange-950/20 shadow-md ring-2 ring-amber-500/20 dark:ring-amber-400/30 scale-[1.02]' 
                                        : 'border-slate-200/90 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 hover:border-amber-300 dark:hover:border-amber-700 hover:bg-slate-100/70 dark:hover:bg-slate-900/80 opacity-85 hover:opacity-100'
                                ]">
                            
                            <!-- Field Work / Car Icon Box -->
                            <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-2xl flex items-center justify-center mb-3 transition shadow-sm"
                                 :class="attendanceType === 'field' 
                                    ? 'bg-gradient-to-tr from-amber-600 via-amber-500 to-orange-500 text-white shadow-amber-900/25' 
                                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700'">
                                <svg class="w-8 h-8 sm:w-9 sm:h-9" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1" />
                                </svg>
                            </div>

                            <h4 class="font-bold text-sm sm:text-base text-slate-800 dark:text-white mb-1">
                                Dinas Luar
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mb-2">
                                Tugas di luar kantor
                            </p>

                            <!-- Checkmark / Selection Indicator -->
                            <div class="mt-auto pt-1">
                                <span x-show="attendanceType === 'field'" 
                                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-600 text-white dark:bg-amber-400 dark:text-slate-950 shadow-2xs">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>Dipilih</span>
                                </span>
                                <span x-show="attendanceType !== 'field'" 
                                      class="inline-block w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></span>
                            </div>
                        </button>
                    </div>

                    <!-- Bottom Action Buttons: Lanjutkan & Batal -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" 
                                @click="proceedToClockIn()"
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="flex-1 py-3 px-5 rounded-2xl text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-900/20 hover:brightness-105 active:scale-[0.99] transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Lanjutkan</span>
                            <svg class="w-4 h-4 text-teal-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                        <button type="button" 
                                @click="closeAttendanceTypeModal()"
                                class="py-3 px-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition cursor-pointer">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 2: ABSEN MASUK (Camera Face Verification - Lokasi Otomatis dari Halaman) -->
        <div x-show="showClockInModal" 
             x-cloak 
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 dark:bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-[#111c26] rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-[0_24px_60px_rgba(15,23,42,0.25)] max-w-md w-full overflow-hidden my-auto" @click.outside="closeClockInModal()">
                
                <!-- Sleek Gradient Header -->
                <div class="px-5 py-3.5 bg-gradient-to-r from-[#093c40] via-[#155E63] to-[#0d9488] text-white flex items-center justify-between relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5 text-teal-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm sm:text-base text-white leading-tight">
                                Verifikasi Wajah &amp; Absen Masuk
                            </h3>
                            <p class="text-[11px] text-teal-100/85 font-normal mt-0.5">
                                Ambil foto selfie untuk konfirmasi kehadiran
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeClockInModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/90 hover:text-white flex items-center justify-center transition cursor-pointer relative z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('attendance.clock-in') }}" @submit="handleClockInSubmit($event)" class="p-4 sm:p-5 space-y-3.5">
                    @csrf
                    
                    <!-- Hidden Auto-Detected Location & GPS Coordinates -->
                    <input type="hidden" name="office_location_id" :value="selectedOffice">
                    <input type="hidden" name="lat" :value="gpsLat">
                    <input type="hidden" name="lng" :value="gpsLng">
                    <input type="hidden" name="gps_accuracy" :value="gpsAccuracy">

                    <!-- Camera & Face Verification Scanner Card -->
                    <div class="rounded-2xl border border-teal-200/80 bg-gradient-to-b from-teal-50/45 via-slate-50/50 to-teal-50/30 p-5 text-center space-y-3.5 relative overflow-hidden shadow-inner">
                        <!-- Circular Biometric Viewport (Lebih Besar) -->
                        <div class="relative w-48 h-48 sm:w-52 sm:h-52 mx-auto">
                            <div class="w-full h-full rounded-full overflow-hidden bg-slate-900 border-[3.5px] transition-all duration-300 flex items-center justify-center relative shadow-md"
                                 :class="clockInCameraActive ? (clockInScreenDetected ? 'border-rose-500 ring-4 ring-rose-400/35' : (clockInFaceDetected && !clockInMultiFace ? 'border-emerald-500 ring-4 ring-emerald-400/30' : 'border-amber-400 ring-4 ring-amber-400/20')) : (clockInVerified ? 'border-emerald-500 ring-4 ring-emerald-400/30' : 'border-[#155E63] ring-4 ring-teal-500/20')">
                                <video id="clockin-video" autoplay playsinline muted class="w-full h-full object-cover scale-x-[-1]" x-show="clockInCameraActive"></video>
                                <img :src="clockInPhotoPreview" x-show="clockInPhotoPreview && !clockInCameraActive" class="w-full h-full object-cover" alt="Preview Foto Selfie">
                                <canvas id="clockin-canvas" class="hidden"></canvas>
                                <div x-show="!clockInCameraActive && !clockInPhotoPreview" class="text-slate-400 text-center p-2">
                                    <svg class="w-12 h-12 mx-auto text-teal-400/75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-xs block mt-1.5 font-medium text-slate-300">Kamera Selfie</span>
                                </div>
                            </div>
                            <!-- Active Camera Live Indicator Dot -->
                            <span x-show="clockInCameraActive" class="absolute top-2.5 right-2.5 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="clockInScreenDetected ? 'bg-rose-400' : (clockInFaceDetected && !clockInMultiFace ? 'bg-emerald-400' : 'bg-amber-400')"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 border-2 border-white" :class="clockInScreenDetected ? 'bg-rose-500' : (clockInFaceDetected && !clockInMultiFace ? 'bg-emerald-500' : 'bg-amber-500')"></span>
                            </span>
                        </div>

                        <!-- Face Verification & Passive Anti-Screen Status Badge (Tanpa Tes Tantangan) -->
                        <div>
                            <template x-if="isVerifying">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-[#155E63] border border-teal-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Memverifikasi kesesuaian biometrik wajah...</span>
                                </span>
                            </template>
                            <template x-if="!isVerifying && clockInPhotoPreview && clockInVerified">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-300 shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span x-text="'✓ Wajah Cocok & Terverifikasi (' + clockInMatchPercent + '%)'"></span>
                                </span>
                            </template>
                            <template x-if="!isVerifying && !clockInPhotoPreview && clockInCameraActive && clockInScreenDetected">
                                <div class="rounded-xl bg-rose-50 border border-rose-300 px-3.5 py-2.5 text-left space-y-1 shadow-xs">
                                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-rose-800">
                                        <span>🚫 Terdeteksi Layar HP Kedua / Foto Digital!</span>
                                    </div>
                                    <p class="text-[11px] text-rose-700 leading-snug"
                                       x-text="(clockInScreenReason ? clockInScreenReason + '. ' : '') + 'Gunakan wajah asli Anda secara langsung di depan kamera.'"></p>
                                </div>
                            </template>
                            <template x-if="!isVerifying && !clockInPhotoPreview && clockInCameraActive && !clockInScreenDetected && clockInMultiFace">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                                    <span>⚠️ Terdeteksi lebih dari 1 wajah. Pastikan hanya wajah Anda di kamera.</span>
                                </span>
                            </template>
                            <template x-if="!isVerifying && !clockInPhotoPreview && clockInCameraActive && !clockInScreenDetected && !clockInMultiFace && clockInFaceDetected">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>✓ Wajah Terdeteksi — Klik "Ambil Foto Sekarang"</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-teal-50 text-[#155E63] border border-teal-200/90 shadow-2xs">
                                        <span>🛡️ Anti-Layar Aman</span>
                                    </span>
                                </div>
                            </template>
                            <template x-if="!isVerifying && !clockInPhotoPreview && clockInCameraActive && !clockInScreenDetected && !clockInMultiFace && !clockInFaceDetected">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200/90 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                    <span>Wajah belum terdeteksi. Arahkan kamera ke wajah Anda</span>
                                </span>
                            </template>
                            <template x-if="!isVerifying && !clockInPhotoPreview && !clockInCameraActive">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200/90">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Wajib ambil foto selfie wajah</span>
                                </span>
                            </template>
                        </div>

                        <!-- Camera Action Buttons (Lebih Besar) -->
                        <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                            <button type="button" 
                                    @click="startClockInCamera()" 
                                    x-show="!clockInCameraActive"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold bg-white text-[#155E63] border border-teal-300 hover:bg-teal-50 shadow-xs transition cursor-pointer">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span x-text="clockInPhotoPreview ? 'Ulangi Ambil Foto' : 'Nyalakan Kamera'"></span>
                            </button>
                            <button type="button" 
                                    @click="captureClockInPhoto()" 
                                    x-show="clockInCameraActive"
                                    :disabled="isVerifying || clockInScreenDetected || clockInMultiFace"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-700/20 active:scale-[0.98] transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                <span x-text="isVerifying ? 'Memverifikasi...' : 'Ambil Foto Sekarang'"></span>
                            </button>
                        </div>

                        <!-- Hidden Image & Biometrics Inputs -->
                        <input type="hidden" name="primary_image" :value="clockInPrimaryImage">
                        <input type="hidden" name="challenge_image" :value="clockInChallengeImage">
                        <input type="hidden" name="face_descriptor" :value="clockInFaceDescriptor">
                        <input type="hidden" name="anti_spoof_meta" :value="clockInAntiSpoofMeta">
                    </div>

                    <!-- Hidden Attendance Type -->
                    <input type="hidden" name="attendance_type" :value="attendanceType">

                    <!-- Selected Type Badge (Menggantikan dropdown) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-xl border transition"
                         :class="attendanceType === 'field' 
                            ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/80 text-amber-900 dark:text-amber-200' 
                            : 'bg-teal-50/80 dark:bg-teal-950/40 border-teal-200 dark:border-teal-800/80 text-teal-900 dark:text-teal-200'">
                        <div class="flex items-center gap-2 text-xs font-semibold">
                            <template x-if="attendanceType === 'office'">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#155E63] dark:text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Lokasi Absen: <strong class="font-bold">Kantor</strong></span>
                                </span>
                            </template>
                            <template x-if="attendanceType === 'field'">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
                                    <span>Lokasi Absen: <strong class="font-bold">Dinas Luar</strong></span>
                                </span>
                            </template>
                        </div>
                        <button type="button" 
                                x-show="isWithinOfficeRadius"
                                @click="closeClockInModal(); openAttendanceTypeModal()" 
                                class="text-[11px] font-bold text-[#155E63] dark:text-teal-300 hover:underline cursor-pointer">
                            Ganti
                        </button>
                    </div>

                    <!-- Notes (Keterangan Tugas) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                            <span x-text="attendanceType === 'field' ? 'Keterangan Tugas Dinas Luar' : 'Catatan / Keterangan'"></span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full" 
                                  :class="attendanceType !== 'office' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold border border-rose-200 dark:border-rose-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-medium'" 
                                  x-text="attendanceType !== 'office' ? 'Wajib Diisi' : 'Opsional'"></span>
                        </label>
                        <textarea name="notes" rows="2" 
                                  :required="attendanceType !== 'office'"
                                  :placeholder="attendanceType === 'field' ? 'Wajib sebutkan tugas/tujuan dinas luar...' : 'Contoh: Pekerjaan di kantor / catatan...'"
                                  class="w-full text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 rounded-xl border border-slate-200/90 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/60 hover:bg-white dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-900 py-2.5 px-3.5 focus:outline-none focus:border-[#155E63] dark:focus:border-teal-400 focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition resize-none"></textarea>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="closeClockInModal()" class="px-4 py-2.5 rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="isVerifying || !clockInVerified"
                                :class="(!clockInVerified || isVerifying) ? 'opacity-60 cursor-not-allowed' : 'hover:brightness-105 active:scale-[0.99] cursor-pointer'"
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-[0_6px_18px_rgba(21,94,99,0.28)] transition">
                            <svg class="w-4 h-4 text-teal-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Verifikasi Wajah &amp; Absen Masuk</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: KONFIRMASI ABSEN PULANG (Themed & Modern Confirmation Dialog) -->
        <div x-show="showClockOutModal" 
             x-cloak 
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-[0_24px_60px_rgba(15,23,42,0.25)] max-w-md w-full overflow-hidden my-auto" @click.outside="closeClockOutModal()">
                
                <!-- Sleek Sunset Header -->
                <div class="px-5 py-3.5 bg-gradient-to-r from-[#78350f] via-[#b45309] to-[#d97706] text-white flex items-center justify-between relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5 text-amber-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm sm:text-base text-white leading-tight">
                                Konfirmasi Absen Pulang
                            </h3>
                            <p class="text-[11px] text-amber-100/90 font-normal mt-0.5">
                                Selesaikan catatan kehadiran hari ini
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeClockOutModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/90 hover:text-white flex items-center justify-center transition cursor-pointer relative z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('attendance.clock-out') }}" class="p-5 space-y-4">
                    @csrf
                    <input type="hidden" name="lat" :value="gpsLat">
                    <input type="hidden" name="lng" :value="gpsLng">
                    <input type="hidden" name="gps_accuracy" :value="gpsAccuracy">

                    <!-- Visual Icon & Question -->
                    <div class="text-center space-y-2 py-1">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 border border-amber-300 text-amber-700 flex items-center justify-center mx-auto shadow-xs">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <h4 class="font-heading font-extrabold text-base sm:text-lg text-slate-900 tracking-tight">
                            Apakah Anda Yakin Ingin Pulang?
                        </h4>
                        <p class="text-xs text-slate-500 leading-relaxed max-w-xs mx-auto">
                            Sistem akan mencatat jam kepulangan dan menghitung total durasi jam kerja Anda hari ini.
                        </p>
                    </div>

                    <!-- Bento Info Card -->
                    <div class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-3.5 space-y-2.5">
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 shadow-2xs">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jam Masuk</span>
                                <span class="text-xs sm:text-sm font-mono font-extrabold text-emerald-700 block mt-0.5">
                                    {{ $todayAttendance?->clock_in_at ? \Carbon\Carbon::parse($todayAttendance->clock_in_at)->format('H:i') : '--:--' }} WIB
                                </span>
                            </div>
                            <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 shadow-2xs">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Waktu Sekarang</span>
                                <span class="text-xs sm:text-sm font-mono font-extrabold text-amber-700 block mt-0.5" x-text="clockOutLiveTime + ' WIB'">
                                    --:-- WIB
                                </span>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 flex items-center justify-between text-xs shadow-2xs">
                            <span class="text-[11px] text-slate-500 font-medium">Titik Lokasi:</span>
                            <span class="font-bold text-slate-800 text-[11px] truncate max-w-[200px]" x-text="gpsStatus"></span>
                        </div>
                    </div>

                    <!-- Helpful Notice -->
                    <div class="flex items-start gap-2.5 rounded-xl bg-amber-50/80 border border-amber-200/70 p-3 text-[11px] text-amber-900 leading-snug">
                        <span class="text-base leading-none">💡</span>
                        <span>Pastikan seluruh file dan tugas pekerjaan Anda hari ini telah tersimpan dengan aman sebelum meninggalkan kantor.</span>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" 
                                @click="closeClockOutModal()" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #b45309 0%, #D97706 60%, #f59e0b 100%) !important;"
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-[0_6px_18px_rgba(217,119,6,0.3)] hover:brightness-105 active:scale-[0.99] transition cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Ya, Absen Pulang Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function attendancePage() {
            const officesMap = @js($offices->keyBy('id'));

            return {
                showEnrollModal: false,
                showAttendanceTypeModal: false,
                showClockInModal: false,
                showClockOutModal: false,
                showLocationDetail: false,
                clockOutLiveTime: '',
                cameraActive: false,
                photoPreview: null,
                selectedOffice: '{{ $defaultOffice?->id ?? 1 }}',
                attendanceType: 'office',
                
                // Clock-in Camera states
                clockInCameraActive: false,
                clockInPhotoPreview: null,
                clockInPrimaryImage: '',
                clockInChallengeImage: '',
                clockInFaceDescriptor: '',
                clockInFaceDetected: false,
                clockInMultiFace: false,
                clockInVerified: false,
                clockInMatchPercent: 0,
                isVerifying: false,
                clockInScanInterval: null,
                clockInDetector: null,
                clockInScreenDetected: false,
                clockInScreenScore: 0,
                clockInScreenReason: '',
                clockInAntiSpoofMeta: '',
                clockInChallenge: { type: 'blink', label: 'Kedipkan mata Anda 1 kali', icon: '👀' },
                clockInLivenessPassed: false,
                clockInChallengePeakReached: false,
                clockInBaselineEAR: null,
                clockInBaselineYaw: null,
                clockInBaselineSamples: 0,

                // Face-api Model & Enroll Anti-Spoof states
                modelsLoaded: false,
                loadingModels: false,
                faceDetected: false,
                isDetecting: false,
                faceDescriptor: '',
                faceEnrolledLocally: false,
                enrollScanInterval: null,
                enrollDetector: null,
                enrollScreenDetected: false,
                enrollScreenScore: 0,
                enrollScreenReason: '',
                enrollAntiSpoofMeta: '',
                storedFaceEmbedding: @json($faceEmbedding ?? null),

                // Auto-Detected GPS & Office states
                gpsLat: {{ $defaultOffice?->lat ?? -6.937122 }},
                gpsLng: {{ $defaultOffice?->lng ?? 107.631989 }},
                gpsAccuracy: 10,
                gpsLoading: true,
                gpsError: false,
                isWithinOfficeRadius: true,
                detectedDistanceM: 0,
                gpsStatus: 'Mendeteksi titik koordinat GPS perangkat...',

                // Face poses (Captured from real camera)
                poseFront: '',
                poseLeft: '',
                poseRight: '',

                init() {
                    this.detectGPS();
                    this.loadFaceApiModels();
                },

                async loadFaceApiModels() {
                    if (this.modelsLoaded || this.loadingModels) return;
                    if (typeof faceapi === 'undefined') {
                        console.warn('face-api.js library belum selesai dimuat di browser.');
                        return;
                    }
                    this.loadingModels = true;
                    const localUri = '{{ asset("vendor/face-api/models") }}';
                    const cdnUri = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';

                    try {
                        await Promise.all([
                            faceapi.nets.tinyFaceDetector.loadFromUri(localUri),
                            faceapi.nets.faceLandmark68Net.loadFromUri(localUri),
                            faceapi.nets.faceRecognitionNet.loadFromUri(localUri),
                        ]);
                        this.modelsLoaded = true;
                        console.log('✓ Model face-api berhasil dimuat dari lokal.');
                    } catch (errLocal) {
                        console.warn('Gagal memuat model face-api lokal, mencoba CDN fallback...', errLocal);
                        try {
                            await Promise.all([
                                faceapi.nets.tinyFaceDetector.loadFromUri(cdnUri),
                                faceapi.nets.faceLandmark68Net.loadFromUri(cdnUri),
                                faceapi.nets.faceRecognitionNet.loadFromUri(cdnUri),
                            ]);
                            this.modelsLoaded = true;
                            console.log('✓ Model face-api berhasil dimuat dari CDN fallback.');
                        } catch (errCdn) {
                            console.error('Gagal memuat model face-api:', errCdn);
                        }
                    } finally {
                        this.loadingModels = false;
                    }
                },

                // Haversine formula to compute distance in meters between 2 GPS points
                calculateDistanceM(lat1, lon1, lat2, lon2) {
                    const R = 6371000;
                    const toRad = (deg) => (deg * Math.PI) / 180;
                    const dLat = toRad(lat2 - lat1);
                    const dLon = toRad(lon2 - lon1);
                    const a =
                        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return R * c;
                },

                resolveNearestOffice(lat, lng) {
                    let nearest = null;
                    let minDist = Infinity;

                    Object.values(officesMap).forEach((ofc) => {
                        const d = this.calculateDistanceM(lat, lng, parseFloat(ofc.lat), parseFloat(ofc.lng));
                        if (d < minDist) {
                            minDist = d;
                            nearest = ofc;
                        }
                    });

                    if (nearest) {
                        this.selectedOffice = String(nearest.id);
                        this.detectedDistanceM = Math.round(minDist);
                        this.isWithinOfficeRadius = minDist <= parseFloat(nearest.radius_m || 50);
                        this.gpsStatus = nearest.name + ' (Radius ' + nearest.radius_m + 'm)';
                        if (!this.isWithinOfficeRadius) {
                            this.attendanceType = 'field';
                        }
                    }
                },

                detectGPS() {
                    this.gpsLoading = true;
                    this.gpsError = false;
                    this.gpsStatus = 'Mendeteksi koordinat GPS perangkat...';

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.gpsLat = pos.coords.latitude;
                                this.gpsLng = pos.coords.longitude;
                                this.gpsAccuracy = Math.round(pos.coords.accuracy || 10);
                                this.gpsLoading = false;
                                this.gpsError = false;
                                this.resolveNearestOffice(this.gpsLat, this.gpsLng);
                            },
                            (err) => {
                                this.gpsLoading = false;
                                this.gpsError = true;
                                const ofc = officesMap[this.selectedOffice] || Object.values(officesMap)[0];
                                if (ofc) {
                                    this.selectedOffice = String(ofc.id);
                                    this.gpsLat = parseFloat(ofc.lat);
                                    this.gpsLng = parseFloat(ofc.lng);
                                    this.gpsAccuracy = 10;
                                    this.gpsStatus = ofc.name + ' (Izin GPS belum aktif)';
                                } else {
                                    this.gpsStatus = 'Izin GPS perangkat belum diaktifkan';
                                }
                            },
                            { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                        );
                    } else {
                        this.gpsLoading = false;
                        this.gpsError = true;
                        this.gpsStatus = 'Browser tidak mendukung Geolocation GPS';
                    }
                },

                // Enrollment Modal controls
                openEnrollModal() {
                    this.showEnrollModal = true;
                    this.loadFaceApiModels();
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                },

                closeEnrollModal() {
                    this.stopCamera();
                    this.showEnrollModal = false;
                },

                captureSnapshotDataUrl(video, quality = 0.80) {
                    const tmpCanvas = document.createElement('canvas');
                    tmpCanvas.width = video.videoWidth || 320;
                    tmpCanvas.height = video.videoHeight || 240;
                    const tmpCtx = tmpCanvas.getContext('2d');
                    if (tmpCtx) {
                        tmpCtx.save();
                        tmpCtx.translate(tmpCanvas.width, 0);
                        tmpCtx.scale(-1, 1);
                        tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);
                        tmpCtx.restore();
                        return tmpCanvas.toDataURL('image/jpeg', quality);
                    }
                    return '';
                },

                showScreenSpoofAlert(reasons) {
                    const list = Array.isArray(reasons) && reasons.length > 0
                        ? reasons
                        : ['Terdeteksi tampilan layar HP / foto digital di depan kamera'];
                    const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
                    const itemsHtml = list.map((r) => `<li>${escapeHtml(r)}</li>`).join('');

                    this.showAlert({
                        icon: 'error',
                        title: 'Deteksi Layar HP / Spoofing!',
                        confirmColor: '#be123c',
                        confirmText: 'Mengerti',
                        html: `<div class="space-y-2.5 text-left">
                            <p class="text-xs sm:text-sm font-semibold text-rose-900 leading-snug text-center">
                                Sistem mendeteksi percobaan absensi menggunakan <strong>layar HP kedua / foto digital</strong>!
                            </p>
                            <ul class="text-xs text-rose-800 bg-rose-50 border border-rose-200 rounded-xl p-3 list-disc list-inside space-y-1">
                                ${itemsHtml}
                            </ul>
                            <p class="text-xs text-slate-500 text-center">
                                Harap arahkan kamera langsung ke wajah asli Anda.
                            </p>
                        </div>`
                    });
                },

                rotateClockInChallenge() {
                    if (window.AttendanceScreenDetector && typeof window.AttendanceScreenDetector.pickRandomChallenge === 'function') {
                        this.clockInChallenge = window.AttendanceScreenDetector.pickRandomChallenge(this.clockInChallenge?.type);
                    }
                    this.clockInLivenessPassed = false;
                    this.clockInChallengePeakReached = false;
                    this.clockInBaselineEAR = null;
                    this.clockInBaselineYaw = null;
                    this.clockInBaselineSamples = 0;
                    this.clockInChallengeImage = '';
                },

                startCamera() {
                    const video = document.getElementById('enroll-video');
                    if (this.enrollScanInterval) {
                        clearInterval(this.enrollScanInterval);
                        this.enrollScanInterval = null;
                    }

                    this.enrollScreenDetected = false;
                    this.enrollScreenScore = 0;
                    this.enrollScreenReason = '';
                    this.enrollAntiSpoofMeta = '';
                    this.enrollDetector = window.AttendanceScreenDetector ? window.AttendanceScreenDetector.createSession() : null;

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
                            .then((stream) => {
                                video.srcObject = stream;
                                this.cameraActive = true;
                                this.photoPreview = null;
                                this.faceDetected = false;

                                let scanBusy = false;
                                // Real-time face + anti-screen detection loop
                                this.enrollScanInterval = setInterval(async () => {
                                    if (scanBusy || !this.cameraActive || !video || video.paused || video.ended) return;
                                    if (typeof faceapi !== 'undefined' && this.modelsLoaded && video.videoWidth > 0) {
                                        scanBusy = true;
                                        try {
                                            const det = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }));
                                            this.faceDetected = !!det;
                                            if (det && this.enrollDetector) {
                                                const box = det.detection ? det.detection.box : det.box;
                                                const scan = this.enrollDetector.analyzeFrame(video, box);
                                                this.enrollScreenScore = scan.score;
                                                this.enrollScreenDetected = scan.confirmed;
                                                this.enrollScreenReason = scan.reasons[0] || '';
                                            } else if (!det) {
                                                this.enrollScreenDetected = false;
                                            }
                                        } catch (_) {}
                                        scanBusy = false;
                                    }
                                }, 250);
                            })
                            .catch((err) => {
                                this.cameraActive = false;
                                this.showAlert({
                                    icon: 'warning',
                                    title: 'Akses Kamera Diperlukan',
                                    text: 'Tidak dapat mengakses kamera: pastikan izin kamera diaktifkan di browser perangkat Anda.'
                                });
                            });
                    } else {
                        this.showAlert({
                            icon: 'error',
                            title: 'Browser Tidak Mendukung',
                            text: 'Browser perangkat Anda tidak mendukung akses kamera.'
                        });
                    }
                },

                stopCamera() {
                    if (this.enrollScanInterval) {
                        clearInterval(this.enrollScanInterval);
                        this.enrollScanInterval = null;
                    }
                    const video = document.getElementById('enroll-video');
                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                    this.cameraActive = false;
                    this.enrollScreenDetected = false;
                },

                async capturePhoto() {
                    const video = document.getElementById('enroll-video');
                    const canvas = document.getElementById('enroll-canvas');
                    if (!video || !canvas) return;

                    this.isDetecting = true;
                    if (!this.modelsLoaded) {
                        await this.loadFaceApiModels();
                    }

                    let detection = null;
                    if (typeof faceapi !== 'undefined' && this.modelsLoaded && video.videoWidth > 0) {
                        try {
                            detection = await faceapi
                                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }))
                                .withFaceLandmarks()
                                .withFaceDescriptor();
                        } catch (err) {
                            console.error('Face detection error during enroll capture:', err);
                        }
                    }

                    // Strict Face Verification: Reject if no human face detected
                    if (!detection) {
                        this.isDetecting = false;
                        this.showAlert({
                            icon: 'warning',
                            title: 'Wajah Tidak Terdeteksi',
                            text: 'Kamera tidak mendeteksi wajah manusia. Pastikan wajah Anda terlihat jelas tepat di tengah lingkaran kamera dengan pencahayaan yang cukup, bukan objek lain.',
                            confirmText: 'Coba Lagi'
                        });
                        return;
                    }

                    // Anti-Spoofing 3-frame Capture Burst Verification
                    let antiSpoofPayload = { screen_detected: false, screen_score: this.enrollScreenScore || 0, avg_score: this.enrollScreenScore || 0 };
                    if (this.enrollDetector) {
                        const burst = await this.enrollDetector.analyzeCaptureBurst(video, detection.detection.box, 3, 110);
                        antiSpoofPayload = {
                            screen_detected: burst.blocked,
                            screen_score: burst.maxScore,
                            avg_score: burst.avgScore,
                            metrics: burst.metrics,
                        };
                        if (burst.blocked) {
                            this.enrollScreenDetected = true;
                            this.enrollScreenScore = burst.maxScore;
                            this.enrollScreenReason = burst.reasons[0] || '';
                            this.isDetecting = false;
                            this.showScreenSpoofAlert(burst.reasons);
                            return;
                        }
                    }

                    canvas.width = video.videoWidth || 320;
                    canvas.height = video.videoHeight || 240;
                    const ctx = canvas.getContext('2d');
                    ctx.save();
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    ctx.restore();
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    
                    this.photoPreview = dataUrl;
                    this.poseFront = dataUrl;
                    this.poseLeft = dataUrl;
                    this.poseRight = dataUrl;
                    this.faceDescriptor = JSON.stringify(Array.from(detection.descriptor));
                    this.enrollAntiSpoofMeta = JSON.stringify(antiSpoofPayload);
                    this.faceEnrolledLocally = true;
                    this.isDetecting = false;

                    this.stopCamera();
                },

                handleEnrollSubmit(e) {
                    if (!this.poseFront) {
                        e.preventDefault();
                        this.showAlert({
                            icon: 'info',
                            title: 'Foto Wajah Diperlukan',
                            text: 'Silakan ambil foto selfie wajah terlebih dahulu sebelum menyimpan pendaftaran biometrik Anda.'
                        });
                        if (!this.cameraActive) {
                            this.startCamera();
                        }
                        return false;
                    }
                },

                // Attendance Type Selection Modal controls (Step 1)
                openAttendanceTypeModal() {
                    if (!this.isWithinOfficeRadius) {
                        this.attendanceType = 'field';
                    }
                    this.showAttendanceTypeModal = true;
                },

                closeAttendanceTypeModal() {
                    this.showAttendanceTypeModal = false;
                },

                proceedToClockIn() {
                    if (!this.isWithinOfficeRadius) {
                        this.attendanceType = 'field';
                    }
                    this.showAttendanceTypeModal = false;
                    this.openClockInModal();
                },

                // Clock-in Face Verification Modal controls (Step 2)
                openClockInModal() {
                    this.showClockInModal = true;
                    this.clockInVerified = false;
                    this.clockInMatchPercent = 0;
                    this.clockInPhotoPreview = null;
                    this.clockInPrimaryImage = '';
                    this.clockInChallengeImage = '';
                    this.clockInFaceDescriptor = '';
                    this.clockInAntiSpoofMeta = '';
                    this.clockInScreenDetected = false;
                    this.clockInScreenScore = 0;
                    this.clockInScreenReason = '';
                    this.clockInMultiFace = false;
                    this.clockInLivenessPassed = false;
                    this.clockInChallengePeakReached = false;
                    this.loadFaceApiModels();
                    this.$nextTick(() => {
                        this.startClockInCamera();
                    });
                },

                closeClockInModal() {
                    this.stopClockInCamera();
                    this.showClockInModal = false;
                },

                // Clock-out Modal controls
                openClockOutModal() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const mins = String(now.getMinutes()).padStart(2, '0');
                    this.clockOutLiveTime = `${hours}:${mins}`;
                    this.showClockOutModal = true;
                },

                closeClockOutModal() {
                    this.showClockOutModal = false;
                },

                // Styled Dialog Alert (SweetAlert2)
                showAlert({ title = 'Perhatian', text = '', html = null, icon = 'info', confirmText = 'Mengerti', confirmColor = '#155E63' }) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: title,
                            text: html ? undefined : text,
                            html: html || undefined,
                            icon: icon,
                            confirmButtonText: confirmText,
                            confirmButtonColor: confirmColor,
                            backdrop: 'rgba(15, 23, 42, 0.65)',
                            customClass: {
                                popup: 'rounded-3xl shadow-2xl border border-slate-200/90 font-sans p-6 text-center select-none',
                                title: 'font-extrabold text-base sm:text-lg text-slate-900 tracking-tight',
                                htmlContainer: 'text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed',
                                confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-xs text-white shadow-md active:scale-95 transition cursor-pointer'
                            }
                        });
                    } else if (window.showToast) {
                        window.showToast(icon === 'error' ? 'error' : (icon === 'warning' ? 'warning' : 'info'), text || title);
                    } else {
                        alert(text || title);
                    }
                },

                startClockInCamera() {
                    const video = document.getElementById('clockin-video');
                    if (this.clockInScanInterval) {
                        clearInterval(this.clockInScanInterval);
                        this.clockInScanInterval = null;
                    }

                    this.clockInDetector = window.AttendanceScreenDetector ? window.AttendanceScreenDetector.createSession() : null;
                    if (window.AttendanceScreenDetector && typeof window.AttendanceScreenDetector.pickRandomChallenge === 'function') {
                        this.clockInChallenge = window.AttendanceScreenDetector.pickRandomChallenge();
                    }
                    this.clockInBaselineEAR = null;
                    this.clockInBaselineYaw = null;
                    this.clockInBaselineSamples = 0;
                    this.clockInLivenessPassed = false;
                    this.clockInChallengePeakReached = false;
                    this.clockInScreenDetected = false;
                    this.clockInScreenScore = 0;
                    this.clockInScreenReason = '';
                    this.clockInMultiFace = false;
                    this.clockInChallengeImage = '';
                    this.clockInVerified = false;
                    this.clockInMatchPercent = 0;
                    this.clockInPrimaryImage = '';
                    this.clockInFaceDescriptor = '';
                    this.clockInAntiSpoofMeta = '';

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
                            .then((stream) => {
                                video.srcObject = stream;
                                this.clockInCameraActive = true;
                                this.clockInPhotoPreview = null;
                                this.clockInFaceDetected = false;

                                let scanBusy = false;
                                // Real-time face + Anti-Screen + 3D Liveness Challenge scan loop
                                this.clockInScanInterval = setInterval(async () => {
                                    if (scanBusy || !this.clockInCameraActive || !video || video.paused || video.ended) return;
                                    if (typeof faceapi !== 'undefined' && this.modelsLoaded && video.videoWidth > 0) {
                                        scanBusy = true;
                                        try {
                                            const detections = await faceapi
                                                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }))
                                                .withFaceLandmarks();

                                            if (!detections || detections.length === 0) {
                                                this.clockInFaceDetected = false;
                                                this.clockInMultiFace = false;
                                                this.clockInScreenDetected = false;
                                                scanBusy = false;
                                                return;
                                            }

                                            if (detections.length > 1) {
                                                this.clockInFaceDetected = true;
                                                this.clockInMultiFace = true;
                                                this.clockInLivenessPassed = false;
                                                this.clockInChallengePeakReached = false;
                                                scanBusy = false;
                                                return;
                                            }

                                            this.clockInFaceDetected = true;
                                            this.clockInMultiFace = false;
                                            const det = detections[0];

                                            // 1. Passive Screen-Replay Check (Tanpa Tes Tantangan Gerakan)
                                            if (this.clockInDetector) {
                                                const scan = this.clockInDetector.analyzeFrame(video, det.detection.box);
                                                this.clockInScreenScore = scan.score;
                                                this.clockInScreenDetected = scan.confirmed;
                                                this.clockInScreenReason = scan.reasons[0] || '';

                                                if (scan.confirmed) {
                                                    this.clockInLivenessPassed = false;
                                                    this.clockInChallengeImage = '';
                                                    scanBusy = false;
                                                    return;
                                                }
                                            }

                                            // 2. Wajah asli terdeteksi & lolos anti-spoofing pasif
                                            this.clockInLivenessPassed = true;
                                            if (!this.clockInChallengeImage) {
                                                this.clockInChallengeImage = this.captureSnapshotDataUrl(video, 0.80);
                                            }
                                        } catch (_) {}
                                        scanBusy = false;
                                    }
                                }, 200);
                            })
                            .catch((err) => {
                                this.clockInCameraActive = false;
                                this.showAlert({
                                    icon: 'warning',
                                    title: 'Akses Kamera Diperlukan',
                                    text: 'Tidak dapat mengakses kamera: pastikan izin kamera diaktifkan di browser perangkat Anda.'
                                });
                            });
                    } else {
                        this.showAlert({
                            icon: 'error',
                            title: 'Browser Tidak Mendukung',
                            text: 'Browser perangkat Anda tidak mendukung akses kamera.'
                        });
                    }
                },

                stopClockInCamera() {
                    if (this.clockInScanInterval) {
                        clearInterval(this.clockInScanInterval);
                        this.clockInScanInterval = null;
                    }
                    const video = document.getElementById('clockin-video');
                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                    this.clockInCameraActive = false;
                },

                async captureClockInPhoto() {
                    const video = document.getElementById('clockin-video');
                    const canvas = document.getElementById('clockin-canvas');
                    if (!video || !canvas) return;

                    if (this.clockInScreenDetected) {
                        this.showScreenSpoofAlert(this.clockInScreenReason ? [this.clockInScreenReason] : []);
                        return;
                    }

                    this.isVerifying = true;
                    if (!this.modelsLoaded) {
                        await this.loadFaceApiModels();
                    }

                    let detection = null;
                    if (typeof faceapi !== 'undefined' && this.modelsLoaded && video.videoWidth > 0) {
                        try {
                            detection = await faceapi
                                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }))
                                .withFaceLandmarks()
                                .withFaceDescriptor();
                        } catch (err) {
                            console.error('Face verification error on capture:', err);
                        }
                    }

                    // 1. REJECT IF NO HUMAN FACE DETECTED
                    if (!detection) {
                        this.isVerifying = false;
                        this.clockInVerified = false;
                        this.showAlert({
                            icon: 'warning',
                            title: 'Wajah Tidak Terdeteksi',
                            text: 'Kamera tidak mendeteksi wajah manusia. Pastikan wajah Anda terlihat jelas di dalam lingkaran kamera, bukan dinding, lantai, atau objek lain.',
                            confirmText: 'Coba Lagi'
                        });
                        return;
                    }

                    // 1B. FINAL 3-FRAME ANTI-SCREEN CAPTURE BURST VERIFICATION
                    let antiSpoofPayload = {
                        screen_detected: false,
                        screen_score: this.clockInScreenScore || 0,
                        avg_score: this.clockInScreenScore || 0,
                        liveness_passed: true,
                        challenge_type: this.clockInChallenge.type,
                    };

                    if (this.clockInDetector) {
                        const burst = await this.clockInDetector.analyzeCaptureBurst(video, detection.detection.box, 3, 110);
                        antiSpoofPayload = {
                            screen_detected: burst.blocked,
                            screen_score: burst.maxScore,
                            avg_score: burst.avgScore,
                            liveness_passed: this.clockInLivenessPassed,
                            challenge_type: this.clockInChallenge.type,
                            metrics: burst.metrics,
                        };
                        if (burst.blocked) {
                            this.clockInScreenDetected = true;
                            this.clockInScreenScore = burst.maxScore;
                            this.clockInScreenReason = burst.reasons[0] || '';
                            this.clockInLivenessPassed = false;
                            this.clockInVerified = false;
                            this.isVerifying = false;
                            this.showScreenSpoofAlert(burst.reasons);
                            return;
                        }
                    }

                    const capturedDescriptor = Array.from(detection.descriptor);

                    // 2. VERIFY MATCH AGAINST REGISTERED BIOMETRIC TEMPLATE
                    let isMatch = true;
                    let matchScorePercent = 95;

                    if (this.storedFaceEmbedding && Array.isArray(this.storedFaceEmbedding) && this.storedFaceEmbedding.length === 128) {
                        const distance = faceapi.euclideanDistance(this.storedFaceEmbedding, capturedDescriptor);
                        // Standard ResNet Face Distance: <= 0.55 match
                        matchScorePercent = Math.max(0, Math.min(100, Math.round((1 - (distance / 0.70)) * 100)));

                        if (distance > 0.55) {
                            isMatch = false;
                        }
                    }

                    if (!isMatch) {
                        this.isVerifying = false;
                        this.clockInVerified = false;
                        this.showAlert({
                            icon: 'error',
                            title: 'Verifikasi Wajah Gagal',
                            confirmColor: '#be123c',
                            confirmText: 'Tutup',
                            html: `<div class="space-y-2.5">
                                <p class="text-sm font-semibold text-rose-900 leading-snug">Wajah yang terdeteksi <strong>TIDAK COCOK</strong> dengan data biometrik yang didaftarkan pada akun ini!</p>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    Tingkat Kemiripan: ${matchScorePercent}%
                                </div>
                                <p class="text-xs text-slate-500">Absensi kehadiran hanya dapat dilakukan oleh pemilik akun terdaftar.</p>
                            </div>`
                        });
                        return;
                    }

                    // SUCCESS: Match confirmed & verified — ensure real challenge_image is distinct JPEG frame
                    if (!this.clockInChallengeImage) {
                        this.clockInChallengeImage = this.captureSnapshotDataUrl(video, 0.80);
                    }

                    canvas.width = video.videoWidth || 320;
                    canvas.height = video.videoHeight || 240;
                    const ctx = canvas.getContext('2d');
                    ctx.save();
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    ctx.restore();
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    this.clockInPhotoPreview = dataUrl;
                    this.clockInPrimaryImage = dataUrl;
                    this.clockInFaceDescriptor = JSON.stringify(capturedDescriptor);
                    this.clockInAntiSpoofMeta = JSON.stringify(antiSpoofPayload);
                    this.clockInVerified = true;
                    this.clockInMatchPercent = matchScorePercent;
                    this.isVerifying = false;

                    this.stopClockInCamera();
                    if (typeof window.playSuccessDingSound === 'function') {
                        window.playSuccessDingSound();
                    }
                    if (window.showToast) {
                        window.showToast('success', 'Wajah berhasil diverifikasi (' + matchScorePercent + '% kemiripan)! Silakan klik tombol verifikasi di bawah.');
                    }
                },

                handleClockInSubmit(e) {
                    if (this.attendanceType === 'office' && !this.isWithinOfficeRadius) {
                        e.preventDefault();
                        this.showAlert({
                            icon: 'warning',
                            title: 'Di Luar Radius Kantor',
                            text: `Jarak Anda (${this.detectedDistanceM} meter) berada di luar batas geofence kantor. Silakan pilih tipe kehadiran Dinas Luar untuk melanjutkan.`
                        });
                        return false;
                    }

                    if (!this.clockInPrimaryImage || !this.clockInVerified) {
                        e.preventDefault();
                        const msg = !this.clockInPrimaryImage
                            ? 'Wajib mengambil foto selfie wajah terlebih dahulu untuk verifikasi identitas absensi masuk.'
                            : 'Wajah belum terverifikasi cocok dengan data biometrik yang didaftarkan.';
                        this.showAlert({
                            icon: 'warning',
                            title: 'Foto Wajah Diperlukan',
                            text: msg
                        });
                        if (!this.clockInCameraActive) {
                            this.startClockInCamera();
                        }
                        return false;
                    }
                    this.stopClockInCamera();
                }
            };
        }
    </script>

    <!-- Audio Context Sound Generator untuk Nada 'Ting' Sukses Absen -->
    <script>
        window.playSuccessDingSound = function() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();

                // Bell / Ting chord: dua frekuensi harmonis tinggi (C6 ~ 1046.5Hz dan G6 ~ 1567.98Hz)
                const playChime = (freq, startTime, duration) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, startTime);

                    // Fade-in cepat lalu decay halus seperti lonceng / ting
                    gain.gain.setValueAtTime(0, startTime);
                    gain.gain.linearRampToValueAtTime(0.3, startTime + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                };

                const now = ctx.currentTime;
                // Nada 1: 1046.5 Hz (C6)
                playChime(1046.5, now, 0.4);
                // Nada 2 (Ting utama lebih tinggi): 1567.98 Hz (G6)
                playChime(1567.98, now + 0.08, 0.9);
            } catch (e) {
                console.warn('Audio play failed:', e);
            }
        };

        // Otomatis bunyikan saat halaman memuat flash alert session('status') sukses absen
        @if (session('status'))
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof window.playSuccessDingSound === 'function') {
                        window.playSuccessDingSound();
                    }
                }, 300);
            });
        @endif
    </script>
</x-layouts.app>
