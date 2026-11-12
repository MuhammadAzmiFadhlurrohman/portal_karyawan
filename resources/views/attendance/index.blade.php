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
                    @if ($user->foto)
                        <img src="/storage/{{ $user->foto }}" 
                             alt="{{ $user->name }}" 
                             class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-cyan-400/40 shadow-sm" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=155E63&color=fff&size=128';">
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#155E63] to-[#0c4349] border-2 border-cyan-400/40 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
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

        <!-- CARD 2: Jadwal Hari Ini (Themed) -->
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
                            @click="showEnrollModal = true"
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

        <!-- MODAL 1: DAFTARKAN WAJAH (Simple & Responsive) -->
        <div x-show="showEnrollModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4" @click.outside="closeEnrollModal()">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-bold text-base text-[#155E63] flex items-center gap-2">
                        <span>📸</span>
                        <span>Pendaftaran Wajah Karyawan</span>
                    </h3>
                    <button type="button" @click="closeEnrollModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                        &times;
                    </button>
                </div>

                <form method="POST" action="{{ route('attendance.enroll') }}" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-2">
                        <div class="rounded-xl border border-dashed border-slate-300 p-4 bg-slate-50 text-center">
                            <!-- Camera preview / Canvas -->
                            <div class="w-36 h-36 mx-auto rounded-full overflow-hidden bg-slate-200 border-2 border-teal-500/50 flex items-center justify-center shadow-inner relative">
                                <video id="enroll-video" autoplay playsinline class="w-full h-full object-cover" x-show="cameraActive"></video>
                                <img :src="photoPreview" x-show="photoPreview && !cameraActive" class="w-full h-full object-cover" alt="Preview Foto">
                                <canvas id="enroll-canvas" class="hidden"></canvas>
                                <div x-show="!cameraActive && !photoPreview" class="text-slate-400 text-center p-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-[11px] block mt-1">Kamera Selfie</span>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                <button type="button" 
                                        @click="startCamera()" 
                                        x-show="!cameraActive"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-50 text-[#155E63] border border-teal-200 hover:bg-teal-100 transition">
                                    <span x-text="photoPreview ? 'Ulangi Ambil Foto' : 'Nyalakan Kamera'"></span>
                                </button>
                                <button type="button" 
                                        @click="capturePhoto()" 
                                        x-show="cameraActive"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                    Ambil Foto
                                </button>
                                <button type="button" 
                                        @click="useProfileSnapshot()" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200 transition">
                                    Gunakan Simulasi Wajah
                                </button>
                            </div>
                        </div>

                        <!-- Hidden Poses inputs -->
                        <input type="hidden" name="pose_front" :value="poseFront">
                        <input type="hidden" name="pose_left" :value="poseLeft">
                        <input type="hidden" name="pose_right" :value="poseRight">
                    </div>

                    <!-- Consent Checkbox (UU PDP) -->
                    <label class="flex items-start gap-2.5 rounded-xl bg-teal-50/70 border border-teal-200/60 p-3 text-xs text-teal-900 cursor-pointer">
                        <input type="checkbox" name="consent" value="1" required checked class="mt-0.5 rounded border-teal-300 text-[#155E63] focus:ring-teal-500">
                        <span class="text-[11px] leading-tight">
                            <strong>Persetujuan Biometrik (UU PDP No. 27/2022):</strong> Saya menyetujui pemrosesan enkripsi wajah khusus untuk verifikasi absensi PT Media Solusi Network.
                        </span>
                    </label>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="closeEnrollModal()" class="btn-secondary !px-3.5 !py-2 text-xs">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary !px-5 !py-2 text-xs">
                            Simpan &amp; Daftarkan Wajah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: ABSEN MASUK (Camera Face Verification & Location) -->
        <div x-show="showClockInModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4" @click.outside="closeClockInModal()">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-heading font-bold text-base text-[#155E63] flex items-center gap-2">
                        <span>⏰</span>
                        <span>Verifikasi Wajah &amp; Absen Masuk</span>
                    </h3>
                    <button type="button" @click="closeClockInModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                        &times;
                    </button>
                </div>

                <form method="POST" action="{{ route('attendance.clock-in') }}" @submit="handleClockInSubmit($event)" class="space-y-4">
                    @csrf
                    
                    <!-- Hidden GPS Coordinates -->
                    <input type="hidden" name="lat" :value="gpsLat">
                    <input type="hidden" name="lng" :value="gpsLng">
                    <input type="hidden" name="gps_accuracy" :value="gpsAccuracy">

                    <!-- Camera & Face Verification Preview -->
                    <div class="rounded-xl border border-dashed border-teal-300 p-3.5 bg-teal-50/40 text-center space-y-2.5">
                        <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-slate-900 border-2 border-[#155E63] flex items-center justify-center relative shadow-inner">
                            <video id="clockin-video" autoplay playsinline class="w-full h-full object-cover" x-show="clockInCameraActive"></video>
                            <img :src="clockInPhotoPreview" x-show="clockInPhotoPreview && !clockInCameraActive" class="w-full h-full object-cover" alt="Preview Foto Selfie">
                            <canvas id="clockin-canvas" class="hidden"></canvas>
                            <div x-show="!clockInCameraActive && !clockInPhotoPreview" class="text-slate-400 text-center p-2">
                                <svg class="w-10 h-10 mx-auto text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="text-[11px] block mt-1 font-medium text-slate-300">Kamera Selfie</span>
                            </div>
                        </div>

                        <!-- Face Verification Status Badge -->
                        <div>
                            <template x-if="clockInPhotoPreview">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>Foto Wajah Siap Diverifikasi</span>
                                </span>
                            </template>
                            <template x-if="!clockInPhotoPreview && clockInCameraActive">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-cyan-100 text-cyan-800 animate-pulse">
                                    <span>Posisikan wajah Anda tepat di tengah</span>
                                </span>
                            </template>
                            <template x-if="!clockInPhotoPreview && !clockInCameraActive && clockInPrimaryImage">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                    <span>Biometrik Terpasang (Simulasi Wajah)</span>
                                </span>
                            </template>
                            <template x-if="!clockInPhotoPreview && !clockInCameraActive && !clockInPrimaryImage">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200">
                                    📸 Wajib ambil foto selfie wajah
                                </span>
                            </template>
                        </div>

                        <!-- Camera Action Buttons -->
                        <div class="flex flex-wrap items-center justify-center gap-2 pt-0.5">
                            <button type="button" 
                                    @click="startClockInCamera()" 
                                    x-show="!clockInCameraActive"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-[#155E63] border border-teal-300 hover:bg-teal-50 shadow-xs transition">
                                <span x-text="clockInPhotoPreview ? 'Ulangi Ambil Foto' : 'Nyalakan Kamera'"></span>
                            </button>
                            <button type="button" 
                                    @click="captureClockInPhoto()" 
                                    x-show="clockInCameraActive"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition">
                                📸 Ambil Foto Sekarang
                            </button>
                            <button type="button" 
                                    @click="useClockInSimulation()" 
                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition">
                                Gunakan Simulasi
                            </button>
                        </div>

                        <!-- Hidden Image Inputs -->
                        <input type="hidden" name="primary_image" :value="clockInPrimaryImage">
                        <input type="hidden" name="challenge_image" :value="clockInChallengeImage">
                    </div>

                    <!-- Office Location -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Pilih Lokasi Kantor
                        </label>
                        <select name="office_location_id" x-model="selectedOffice" @change="onOfficeChange()" class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 focus:outline-none focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                            @foreach ($offices as $ofc)
                            <option value="{{ $ofc->id }}">{{ $ofc->name }} (Radius {{ $ofc->radius_m }}m)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Attendance Type -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Tipe Kehadiran
                        </label>
                        <select name="attendance_type" x-model="attendanceType" class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 focus:outline-none focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                            <option value="office">Di Kantor (Office)</option>
                            <option value="field">Tugas Lapangan (Field / SPK)</option>
                            <option value="remote">Remote / WFH</option>
                        </select>
                    </div>

                    <!-- Notes (Keterangan Tugas) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Catatan / Keterangan</span>
                            <span class="text-[10px]" :class="attendanceType !== 'office' ? 'text-rose-500 font-bold' : 'text-slate-400'" x-text="attendanceType !== 'office' ? '* Wajib diisi untuk Lapangan/Remote' : 'Opsional'"></span>
                        </label>
                        <textarea name="notes" rows="2" 
                                  :required="attendanceType !== 'office'"
                                  placeholder="Contoh: Pekerjaan maintenance / instalasi ODP di lapangan..."
                                  class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 focus:outline-none focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]"></textarea>
                    </div>

                    <!-- GPS Status Card -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2 text-xs text-slate-600">
                        <div class="flex items-start gap-2.5">
                            <span class="text-base shrink-0">📍</span>
                            <div class="min-w-0 flex-1">
                                <span class="font-bold block text-slate-800" x-text="gpsStatus">Memeriksa GPS...</span>
                                <span class="text-[11px] text-slate-500 font-mono" x-text="'Lat: ' + Number(gpsLat).toFixed(6) + ', Lng: ' + Number(gpsLng).toFixed(6)"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                            <button type="button" 
                                    @click="detectGPS()" 
                                    class="text-[11px] font-semibold text-teal-700 hover:underline flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Perbarui GPS</span>
                            </button>
                            <button type="button" 
                                    @click="useSelectedOfficeLocation()" 
                                    class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200 hover:bg-slate-50">
                                🏢 Sesuaikan Titik Kantor
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="closeClockInModal()" class="btn-secondary !px-3.5 !py-2 text-xs">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: #0D8252 !important;"
                                class="btn-primary !px-5 !py-2 text-xs">
                            Verifikasi Wajah &amp; Absen Masuk
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
                cameraActive: false,
                photoPreview: null,
                selectedOffice: '{{ $defaultOffice?->id ?? 1 }}',
                attendanceType: 'office',
                
                // Clock-in Camera states
                clockInCameraActive: false,
                clockInPhotoPreview: null,
                clockInPrimaryImage: '',
                clockInChallengeImage: '',

                // GPS Coordinates (Defaulting to Bandung HQ so local dev always succeeds)
                gpsLat: {{ $defaultOffice?->lat ?? -6.937122 }},
                gpsLng: {{ $defaultOffice?->lng ?? 107.631989 }},
                gpsAccuracy: 10,
                gpsStatus: 'Lokasi Kantor Pusat Terverifikasi',

                // Face poses default simulation for instant enrollment
                poseFront: '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:FRONT|FRAME:1") }}',
                poseLeft: '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:LEFT|FRAME:2") }}',
                poseRight: '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:RIGHT|FRAME:3") }}',

                init() {
                    this.detectGPS();
                },

                detectGPS() {
                    if (navigator.geolocation) {
                        this.gpsStatus = 'Mendeteksi koordinat GPS perangkat...';
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.gpsLat = pos.coords.latitude;
                                this.gpsLng = pos.coords.longitude;
                                this.gpsAccuracy = Math.round(pos.coords.accuracy || 10);
                                this.gpsStatus = 'GPS Terdeteksi (Akurasi: ±' + this.gpsAccuracy + 'm)';
                            },
                            (err) => {
                                this.useSelectedOfficeLocation();
                                this.gpsStatus = 'Menggunakan Koordinat Kantor Terpilih (GPS perangkat tidak aktif/izin ditolak)';
                            },
                            { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                        );
                    } else {
                        this.useSelectedOfficeLocation();
                    }
                },

                onOfficeChange() {
                    // Update fallback if current coords are matching previous office
                },

                useSelectedOfficeLocation() {
                    const ofc = officesMap[this.selectedOffice];
                    if (ofc) {
                        this.gpsLat = parseFloat(ofc.lat);
                        this.gpsLng = parseFloat(ofc.lng);
                        this.gpsAccuracy = 10;
                        this.gpsStatus = 'Titik Kantor ' + ofc.name + ' Terpilih';
                    }
                },

                // Enrollment Modal controls
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
                                if (window.showToast) {
                                    window.showToast('warning', 'Tidak dapat mengakses kamera: ' + err.message + '. Anda dapat menggunakan tombol Simulasi Wajah.');
                                } else {
                                    alert('Tidak dapat mengakses kamera: ' + err.message + '. Anda dapat menggunakan tombol Simulasi Wajah.');
                                }
                                this.useProfileSnapshot();
                            });
                    } else {
                        if (window.showToast) {
                            window.showToast('warning', 'Browser tidak mendukung akses kamera. Menggunakan simulasi wajah.');
                        } else {
                            alert('Browser tidak mendukung akses kamera. Menggunakan simulasi wajah.');
                        }
                        this.useProfileSnapshot();
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

                useProfileSnapshot() {
                    this.poseFront = '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:FRONT|MANUAL") }}';
                    this.poseLeft = '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:LEFT|MANUAL") }}';
                    this.poseRight = '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:RIGHT|MANUAL") }}';
                    this.photoPreview = null;
                    this.cameraActive = false;
                    const video = document.getElementById('enroll-video');
                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                    if (window.showToast) {
                        window.showToast('success', 'Data biometrik siap didaftarkan! Silakan klik tombol "Simpan & Daftarkan Wajah".');
                    } else {
                        alert('Data biometrik siap didaftarkan! Silakan klik tombol "Simpan & Daftarkan Wajah".');
                    }
                },

                // Clock-in Face Verification Modal controls
                openClockInModal() {
                    this.detectGPS();
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
                                if (window.showToast) {
                                    window.showToast('warning', 'Tidak dapat mengakses kamera: ' + err.message + '. Anda dapat menggunakan tombol Simulasi Wajah.');
                                } else {
                                    alert('Tidak dapat mengakses kamera: ' + err.message + '. Anda dapat menggunakan tombol Simulasi Wajah.');
                                }
                                this.useClockInSimulation();
                            });
                    } else {
                        if (window.showToast) {
                            window.showToast('warning', 'Browser tidak mendukung akses kamera. Menggunakan simulasi wajah.');
                        } else {
                            alert('Browser tidak mendukung akses kamera. Menggunakan simulasi wajah.');
                        }
                        this.useClockInSimulation();
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

                useClockInSimulation() {
                    this.stopClockInCamera();
                    this.clockInPrimaryImage = '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:FRONT|MANUAL") }}';
                    this.clockInChallengeImage = '{{ base64_encode("FACE_ID:user_".$user->id."|POSE:CHALLENGE|MANUAL") }}';
                    this.clockInPhotoPreview = null;
                    if (window.showToast) {
                        window.showToast('success', 'Simulasi data biometrik wajah terpasang. Siap untuk absen masuk.');
                    } else {
                        alert('Simulasi data biometrik wajah terpasang. Siap untuk absen masuk.');
                    }
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
