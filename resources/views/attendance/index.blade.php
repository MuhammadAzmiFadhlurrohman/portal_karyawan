<x-layouts.app title="Presensi & Absensi Karyawan">
    <div class="w-full space-y-4 sm:space-y-5" 
         x-data="attendancePage()"
         x-init="init()">

        <!-- Status Alerts / Notifications -->
        @if (session('status'))
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-start gap-3 shadow-xs">
            <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                ✓
            </div>
            <div>
                <div class="text-xs font-bold text-emerald-800">Berhasil</div>
                <div class="text-xs text-emerald-700 mt-0.5">{{ session('status') }}</div>
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl flex items-start gap-3 shadow-xs">
            <div class="w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                ✕
            </div>
            <div>
                <div class="text-xs font-bold text-rose-800">Gagal Memproses Absensi</div>
                <ul class="text-xs text-rose-700 mt-0.5 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @php
            $shift = $todaySchedule?->shift ?? \App\Models\Shift::where('code', 'P')->first() ?? \App\Models\Shift::first();
            $shiftName = $shift?->name ?? 'Shift Pagi';
            $shiftStart = $shift?->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '08:15';
            $shiftEnd = $shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '17:00';
            $isWorkday = ! in_array(now('Asia/Jakarta')->dayOfWeek, [0, 6]);
            $defaultOffice = $offices->first();
        @endphp

        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Attendance Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                        <span>Presensi &amp; Absensi Karyawan</span>
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Pencatatan kehadiran kerja biometrik wajah &amp; verifikasi GPS kantor secara real-time.
                    </p>
                </div>
            </div>

            <!-- Right: Profile Info Card (Geser ke Kanan) -->
            <div class="relative z-10 flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-3.5 py-2 text-white shrink-0 shadow-xs self-start sm:self-auto">
                <div class="relative shrink-0">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" 
                             alt="" 
                             class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-cyan-400/40 shadow-sm" 
                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <div style="display: none;" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#18C4C4] to-[#155E63] border-2 border-cyan-400/40 text-white items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                            {{ $user->initials }}
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#18C4C4] to-[#155E63] border-2 border-cyan-400/40 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>

                <div class="text-left leading-tight min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs sm:text-sm text-white tracking-tight truncate max-w-[140px] sm:max-w-[200px]">
                            {{ strtoupper($user->name) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-teal-200/85">
                        <span class="font-mono text-cyan-200">NIK: {{ $user->employee_code }}</span>
                        @if ($user->division)
                            <span class="truncate">· Divisi: {{ $user->division }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Jadwal Hari Ini -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center gap-3.5 sm:gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-[#155E63] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-teal-700/70 tracking-wider uppercase block">JADWAL HARI INI</span>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800 leading-tight mt-0.5">{{ $shiftName }}</h3>
                    <span class="text-xs text-slate-500 font-mono block mt-0.5">{{ $shiftStart }} - {{ $shiftEnd }}</span>
                </div>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $isWorkday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $isWorkday ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    <span>{{ $isWorkday ? 'Hari Kerja' : 'Hari Libur' }}</span>
                </span>
            </div>
        </div>

        <!-- FLOATING COMPACT LOCATION PILL (Di Atas Kiri Navigasi Bar Mobile sesuai Referensi) -->
        <div class="fixed bottom-[68px] left-3.5 sm:left-6 md:bottom-6 md:left-[280px] z-30 select-none"
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

        <!-- CARD 3: Status Absen Hari Ini (Themed) -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-6">
            <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-[#155E63] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Status Absen Hari Ini</h3>
            </div>

            <!-- Center Visual & Status Text -->
            <div class="py-4 flex flex-col items-center justify-center text-center space-y-3">
                @if (! $todayAttendance)
                    <div class="w-20 h-20 rounded-full bg-slate-100/90 flex items-center justify-center text-slate-400">
                        <svg class="w-9 h-9 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium">
                        Belum melakukan absen masuk hari ini
                    </p>
                @elseif (! $todayAttendance->clock_out_at)
                    <div class="w-20 h-20 rounded-full bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-sm font-bold text-emerald-800">Sudah Absen Masuk</p>
                        <p class="text-xs text-slate-500">
                            Pukul <strong class="text-slate-700 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->clock_in_at)->format('H:i') }}</strong> · Siap untuk Absen Pulang
                        </p>
                    </div>
                @else
                    <div class="w-20 h-20 rounded-full bg-teal-50 border-2 border-teal-200 flex items-center justify-center text-[#155E63]">
                        <svg class="w-10 h-10 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-sm font-bold text-[#155E63]">Absensi Selesai Hari Ini</p>
                        <p class="text-xs text-slate-500">
                            Masuk: <strong class="font-mono text-slate-700">{{ \Carbon\Carbon::parse($todayAttendance->clock_in_at)->format('H:i') }}</strong> · 
                            Pulang: <strong class="font-mono text-slate-700">{{ \Carbon\Carbon::parse($todayAttendance->clock_out_at)->format('H:i') }}</strong>
                        </p>
                    </div>
                @endif
            </div>

            <!-- Main Dynamic Button -->
            <div>
                @if (! $faceTemplate)
                    <!-- JIKA BELUM DAFTAR WAJAH -> TOMBOL DAFTAR -->
                    <button type="button"
                            @click="openEnrollModal()"
                            style="background: #155E63 !important;"
                            class="w-full py-3.5 px-6 rounded-xl hover:bg-[#0c4349] text-white font-semibold text-sm shadow-md shadow-teal-900/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Daftarkan Wajah</span>
                    </button>
                    <p class="text-center text-[11px] text-slate-400 mt-2">
                        Anda wajib mendaftarkan wajah sekali sebelum dapat melakukan absensi.
                    </p>
                @else
                    <!-- SUDAH DAFTAR WAJAH -> TOMBOL ABSEN MASUK / PULANG -->
                    @if (! $todayAttendance)
                        <button type="button"
                                @click="openClockInModal()"
                                style="background: #0D8252 !important;"
                                class="w-full py-3.5 px-6 rounded-xl hover:opacity-95 text-white font-semibold text-sm shadow-md shadow-emerald-900/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Absen Masuk</span>
                        </button>
                    @elseif (! $todayAttendance->clock_out_at)
                        <form method="POST" action="{{ route('attendance.clock-out') }}" onsubmit="return confirm('Apakah Anda yakin ingin melakukan absen pulang?')">
                            @csrf
                            <input type="hidden" name="lat" :value="gpsLat">
                            <input type="hidden" name="lng" :value="gpsLng">
                            <input type="hidden" name="gps_accuracy" :value="gpsAccuracy">
                            <button type="submit"
                                    style="background: #D97706 !important;"
                                    class="w-full py-3.5 px-6 rounded-xl hover:opacity-95 text-white font-semibold text-sm shadow-md shadow-amber-900/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <span>Absen Pulang</span>
                            </button>
                        </form>
                    @else
                        <button type="button"
                                disabled
                                class="w-full py-3.5 px-6 rounded-xl bg-slate-200 text-slate-500 font-semibold text-sm flex items-center justify-center gap-2 cursor-not-allowed">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Absensi Selesai Hari Ini</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>

        <!-- RIWAYAT ABSENSI TERAKHIR (Collapsible) -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ openHistory: false }">
            <button type="button" 
                    @click="openHistory = !openHistory" 
                    class="w-full p-4 flex items-center justify-between text-left hover:bg-slate-50 transition cursor-pointer">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs font-semibold text-slate-700">Riwayat Kehadiran Terakhir</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="openHistory" x-cloak class="border-t border-slate-100 p-4">
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse ($recentAttendances as $att)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</span>
                            <span class="text-[11px] text-slate-500 block">
                                Masuk: {{ $att->clock_in_at ? \Carbon\Carbon::parse($att->clock_in_at)->format('H:i') : '-' }} · 
                                Pulang: {{ $att->clock_out_at ? \Carbon\Carbon::parse($att->clock_out_at)->format('H:i') : '-' }}
                            </span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ strtoupper($att->status) }}
                        </span>
                    </div>
                    @empty
                    <p class="py-3 text-center text-slate-400 text-xs">Belum ada catatan absensi sebelumnya.</p>
                    @endforelse
                </div>
                <div class="pt-3.5 mt-2 text-center border-t border-slate-100">
                    <a href="{{ route('attendance.history') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#155E63] hover:underline">
                        <span>Lihat Semua Riwayat Absen</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
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
                            <div class="w-full h-full rounded-full overflow-hidden bg-slate-900 border-[3.5px] border-[#155E63] ring-4 ring-teal-500/20 flex items-center justify-center shadow-lg relative">
                                <video id="enroll-video" autoplay playsinline class="w-full h-full object-cover scale-x-[-1]" x-show="cameraActive"></video>
                                <img :src="photoPreview" x-show="photoPreview && !cameraActive" class="w-full h-full object-cover" alt="Preview Foto">
                                <canvas id="enroll-canvas" class="hidden"></canvas>
                                <div x-show="!cameraActive && !photoPreview" class="text-slate-400 text-center p-2">
                                    <svg class="w-12 h-12 mx-auto text-teal-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-xs block mt-1.5 font-medium text-slate-300">Kamera Selfie</span>
                                </div>
                            </div>
                            <span x-show="cameraActive" class="absolute top-2.5 right-2.5 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        </div>

                        <div>
                            <template x-if="photoPreview">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-200 shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>Foto Wajah Siap Didaftarkan</span>
                                </span>
                            </template>
                            <template x-if="!photoPreview && cameraActive">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-white/90 text-[#155E63] border border-teal-200/80 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                                    <span>Posisikan wajah Anda tepat di tengah</span>
                                </span>
                            </template>
                            <template x-if="!photoPreview && !cameraActive">
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
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-700/20 active:scale-[0.98] transition cursor-pointer">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                <span>Ambil Foto Sekarang</span>
                            </button>
                        </div>

                        <!-- Hidden Poses inputs -->
                        <input type="hidden" name="pose_front" :value="poseFront">
                        <input type="hidden" name="pose_left" :value="poseLeft">
                        <input type="hidden" name="pose_right" :value="poseRight">
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

        <!-- MODAL 2: ABSEN MASUK (Camera Face Verification - Lokasi Otomatis dari Halaman) -->
        <div x-show="showClockInModal" 
             x-cloak 
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-[0_24px_60px_rgba(15,23,42,0.25)] max-w-md w-full overflow-hidden my-auto" @click.outside="closeClockInModal()">
                
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
                            <div class="w-full h-full rounded-full overflow-hidden bg-slate-900 border-[3.5px] border-[#155E63] ring-4 ring-teal-500/20 flex items-center justify-center relative shadow-md">
                                <video id="clockin-video" autoplay playsinline class="w-full h-full object-cover scale-x-[-1]" x-show="clockInCameraActive"></video>
                                <img :src="clockInPhotoPreview" x-show="clockInPhotoPreview && !clockInCameraActive" class="w-full h-full object-cover" alt="Preview Foto Selfie">
                                <canvas id="clockin-canvas" class="hidden"></canvas>
                                <div x-show="!clockInCameraActive && !clockInPhotoPreview" class="text-slate-400 text-center p-2">
                                    <svg class="w-12 h-12 mx-auto text-teal-400/75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-xs block mt-1.5 font-medium text-slate-300">Kamera Selfie</span>
                                </div>
                            </div>
                            <!-- Active Camera Live Indicator Dot -->
                            <span x-show="clockInCameraActive" class="absolute top-2.5 right-2.5 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        </div>

                        <!-- Face Verification Status Badge -->
                        <div>
                            <template x-if="clockInPhotoPreview">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-100/90 text-emerald-800 border border-emerald-200 shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>Foto Wajah Siap Diverifikasi</span>
                                </span>
                            </template>
                            <template x-if="!clockInPhotoPreview && clockInCameraActive">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold bg-white/90 text-[#155E63] border border-teal-200/80 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                                    <span>Posisikan wajah Anda tepat di tengah</span>
                                </span>
                            </template>
                            <template x-if="!clockInPhotoPreview && !clockInCameraActive">
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
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-700/20 active:scale-[0.98] transition cursor-pointer">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                <span>Ambil Foto Sekarang</span>
                            </button>
                        </div>

                        <!-- Hidden Image Inputs -->
                        <input type="hidden" name="primary_image" :value="clockInPrimaryImage">
                        <input type="hidden" name="challenge_image" :value="clockInChallengeImage">
                    </div>

                    <!-- Attendance Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Tipe Kehadiran
                        </label>
                        <div class="relative">
                            <select name="attendance_type" x-model="attendanceType" class="w-full text-xs font-medium text-slate-800 rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-white focus:bg-white py-2.5 pl-3 pr-8 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition">
                                <option value="office">Di Kantor (Office)</option>
                                <option value="field">Tugas Lapangan (Field / SPK)</option>
                                <option value="remote">Remote / WFH</option>
                            </select>
                        </div>
                    </div>

                    <!-- Notes (Keterangan Tugas) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Catatan / Keterangan</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full" :class="attendanceType !== 'office' ? 'bg-rose-50 text-rose-600 font-bold border border-rose-200' : 'bg-slate-100 text-slate-500 font-medium'" x-text="attendanceType !== 'office' ? 'Wajib Diisi' : 'Opsional'"></span>
                        </label>
                        <textarea name="notes" rows="2" 
                                  :required="attendanceType !== 'office'"
                                  placeholder="Contoh: Pekerjaan maintenance / instalasi ODP di lapangan..."
                                  class="w-full text-xs text-slate-800 placeholder-slate-400 rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-white focus:bg-white py-2.5 px-3.5 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition resize-none"></textarea>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" @click="closeClockInModal()" class="px-4 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-[0_6px_18px_rgba(21,94,99,0.28)] hover:brightness-105 active:scale-[0.99] transition cursor-pointer">
                            <svg class="w-4 h-4 text-teal-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Verifikasi Wajah &amp; Absen Masuk</span>
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
                showClockInModal: false,
                showLocationDetail: false,
                cameraActive: false,
                photoPreview: null,
                selectedOffice: '{{ $defaultOffice?->id ?? 1 }}',
                attendanceType: 'office',
                
                // Clock-in Camera states
                clockInCameraActive: false,
                clockInPhotoPreview: null,
                clockInPrimaryImage: '',
                clockInChallengeImage: '',

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
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                },

                closeEnrollModal() {
                    const video = document.getElementById('enroll-video');
                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                    this.cameraActive = false;
                    this.showEnrollModal = false;
                },

                startCamera() {
                    const video = document.getElementById('enroll-video');
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
                            .then((stream) => {
                                video.srcObject = stream;
                                this.cameraActive = true;
                                this.photoPreview = null;
                            })
                            .catch((err) => {
                                this.cameraActive = false;
                                if (window.showToast) {
                                    window.showToast('error', 'Tidak dapat mengakses kamera: pastikan izin kamera diaktifkan di browser Anda.');
                                } else {
                                    alert('Tidak dapat mengakses kamera: pastikan izin kamera diaktifkan di browser Anda.');
                                }
                            });
                    } else {
                        if (window.showToast) {
                            window.showToast('error', 'Browser tidak mendukung akses kamera.');
                        } else {
                            alert('Browser tidak mendukung akses kamera.');
                        }
                    }
                },

                capturePhoto() {
                    const video = document.getElementById('enroll-video');
                    const canvas = document.getElementById('enroll-canvas');
                    if (video && canvas) {
                        canvas.width = video.videoWidth || 320;
                        canvas.height = video.videoHeight || 240;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                        
                        this.photoPreview = dataUrl;
                        this.poseFront = dataUrl;
                        this.poseLeft = dataUrl;
                        this.poseRight = dataUrl;

                        if (video.srcObject) {
                            video.srcObject.getTracks().forEach(track => track.stop());
                            video.srcObject = null;
                        }
                        this.cameraActive = false;
                    }
                },

                handleEnrollSubmit(e) {
                    if (!this.poseFront) {
                        e.preventDefault();
                        if (window.showToast) {
                            window.showToast('warning', 'Silakan ambil foto selfie wajah terlebih dahulu sebelum menyimpan.');
                        } else {
                            alert('Silakan ambil foto selfie wajah terlebih dahulu sebelum menyimpan.');
                        }
                        if (!this.cameraActive) {
                            this.startCamera();
                        }
                        return false;
                    }
                },

                // Clock-in Face Verification Modal controls
                openClockInModal() {
                    this.showClockInModal = true;
                    this.$nextTick(() => {
                        this.startClockInCamera();
                    });
                },

                closeClockInModal() {
                    this.stopClockInCamera();
                    this.showClockInModal = false;
                },

                startClockInCamera() {
                    const video = document.getElementById('clockin-video');
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
                            .then((stream) => {
                                video.srcObject = stream;
                                this.clockInCameraActive = true;
                                this.clockInPhotoPreview = null;
                            })
                            .catch((err) => {
                                this.clockInCameraActive = false;
                                if (window.showToast) {
                                    window.showToast('error', 'Tidak dapat mengakses kamera: pastikan izin kamera diaktifkan di browser Anda.');
                                } else {
                                    alert('Tidak dapat mengakses kamera: pastikan izin kamera diaktifkan di browser Anda.');
                                }
                            });
                    } else {
                        if (window.showToast) {
                            window.showToast('error', 'Browser tidak mendukung akses kamera.');
                        } else {
                            alert('Browser tidak mendukung akses kamera.');
                        }
                    }
                },

                captureClockInPhoto() {
                    const video = document.getElementById('clockin-video');
                    const canvas = document.getElementById('clockin-canvas');
                    if (video && canvas) {
                        canvas.width = video.videoWidth || 320;
                        canvas.height = video.videoHeight || 240;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                        this.clockInPhotoPreview = dataUrl;
                        this.clockInPrimaryImage = dataUrl;
                        this.clockInChallengeImage = '{{ base64_encode("FACE_ID:user_".$user->id."|CHALLENGE_AUTO") }}';

                        this.stopClockInCamera();
                    }
                },

                stopClockInCamera() {
                    const video = document.getElementById('clockin-video');
                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                    this.clockInCameraActive = false;
                },

                handleClockInSubmit(e) {
                    if (!this.clockInPrimaryImage) {
                        e.preventDefault();
                        if (window.showToast) {
                            window.showToast('warning', 'Wajib mengambil foto selfie wajah terlebih dahulu untuk verifikasi identitas absensi masuk.');
                        } else {
                            alert('Wajib mengambil foto selfie wajah terlebih dahulu untuk verifikasi identitas absensi masuk.');
                        }
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
</x-layouts.app>
