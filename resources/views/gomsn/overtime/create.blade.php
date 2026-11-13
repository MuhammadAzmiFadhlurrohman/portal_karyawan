<x-app-layout>
    <div class="w-full space-y-6 pb-12">
        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4.5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#createOvertimeWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="createOvertimeWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Glowing Clock Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Form Pengajuan Lembur
                    </h1>
                    <p class="text-[11px] sm:text-xs text-teal-100/85 mt-0.5 leading-snug">
                        Pastikan Anda telah melakukan absensi hadir sebelum mengajukan jam kerja lembur
                    </p>
                </div>
            </div>

            <!-- Right: Back Button -->
            <div class="relative z-10 shrink-0 self-start sm:self-auto">
                <a href="{{ route('gomsn.overtime.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold backdrop-blur-xs transition shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Daftar Lembur</span>
                </a>
            </div>
        </div>

        @if (session('error'))
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl flex items-start gap-3 shadow-xs">
            <div class="w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                ✕
            </div>
            <div>
                <div class="text-xs font-bold text-rose-800">Gagal Mengajukan Lembur</div>
                <div class="text-xs text-rose-600 mt-0.5">{{ session('error') }}</div>
            </div>
        </div>
        @endif

        <!-- Overtime Form Card (Premium Design Matching Pengajuan) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden"
             x-data="overtimeForm()"
             x-init="init()">
            
            <!-- Card Header Accent -->
            <div style="background: linear-gradient(135deg, #072A2D 0%, #0E5458 100%);" class="px-5 py-3.5 flex items-center justify-between text-white">
                <div class="flex items-center gap-2 font-bold text-xs sm:text-sm">
                    <span class="text-[#18C4C4] font-extrabold">#</span>
                    <span>Informasi &amp; Jadwal Jam Lembur</span>
                </div>
                <span class="text-[11px] font-semibold text-teal-100/90 bg-white/10 px-2.5 py-1 rounded-full">
                    Form Mandiri
                </span>
            </div>

            <form method="POST" action="{{ route('gomsn.overtime.store') }}" class="p-5 sm:p-8 space-y-6">
                @csrf

                <!-- Tanggal Lembur -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 tracking-wide">
                        Tanggal Lembur <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative max-w-md">
                        <input type="date"
                               name="tanggal"
                               x-model="tanggal"
                               @change="checkAttendance()"
                               required
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition shadow-xs text-slate-800 font-semibold" />
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Pastikan Anda sudah melakukan absensi masuk pada tanggal yang dipilih.
                    </p>

                    <!-- Checking Indicator -->
                    <div x-show="checking" x-cloak class="mt-2.5 p-3 bg-teal-50/60 border border-teal-200/80 rounded-xl flex items-center gap-2.5 text-xs text-teal-800">
                        <svg class="animate-spin h-4 w-4 text-teal-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memeriksa data absensi tanggal terpilih...</span>
                    </div>

                    <!-- No Attendance Alert -->
                    <div x-show="!checking && attendanceChecked && !hasAttendance" 
                         x-cloak 
                         class="mt-3 p-3.5 bg-rose-50/80 border border-rose-200 rounded-xl flex items-start gap-3 shadow-2xs">
                        <div class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                            ✕
                        </div>
                        <div>
                            <div class="text-xs font-bold text-rose-800 flex items-center gap-1.5">
                                <span>Tidak ditemukan data absensi hadir pada tanggal tersebut</span>
                            </div>
                            <p class="text-[11px] text-rose-600 mt-0.5 leading-relaxed">
                                Pastikan Anda sudah melakukan absensi dengan status Hadir/Tepat Waktu sebelum mengajukan permohonan lembur.
                            </p>
                        </div>
                    </div>

                    <!-- Attendance Verified Box -->
                    <div x-show="!checking && attendanceChecked && hasAttendance" 
                         x-cloak 
                         class="mt-3 p-3.5 bg-emerald-50/80 border border-emerald-200 rounded-xl flex items-start gap-3 shadow-2xs">
                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                            ✓
                        </div>
                        <div>
                            <div class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                                <span x-text="attendanceData.message">Absensi terverifikasi</span>
                            </div>
                            <p class="text-[11px] text-emerald-600 mt-0.5" x-text="attendanceData.subtext"></p>
                        </div>
                    </div>
                </div>

                <!-- Jenis Lembur -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 tracking-wide">
                        Jenis Lembur <span class="text-rose-500">*</span>
                    </label>
                    <div class="max-w-md">
                        <select name="jenis_lembur"
                                x-model="jenisLembur"
                                @change="calculateEstimasi()"
                                required
                                class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition shadow-xs text-slate-800 font-semibold">
                            <option value="">-- Pilih Jenis Lembur --</option>
                            <option value="Lembur">Lembur (Rp 5.000 / jam)</option>
                            <option value="Piket Hari Libur">Piket Hari Libur (Rp 3.900 / jam)</option>
                            <option value="Piket Hari Kerja">Piket Hari Kerja (Rp 15.000 Flat / Tetap)</option>
                        </select>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Tarif: <strong>Lembur</strong> Rp 5.000/jam &bull; <strong>Piket Libur</strong> Rp 3.900/jam &bull; <strong>Piket Hari Kerja</strong> Rp 15.000 flat/tetap.
                    </p>
                </div>

                <!-- Jam Mulai & Jam Selesai -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 tracking-wide">
                            Jam Mulai Lembur <span class="text-rose-500">*</span>
                        </label>
                        <input type="time"
                               name="jam_mulai"
                               x-model="jamMulai"
                               @change="calculateEstimasi()"
                               required
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition shadow-xs text-slate-800 font-semibold" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 tracking-wide">
                            Jam Selesai Lembur <span class="text-rose-500">*</span>
                        </label>
                        <input type="time"
                               name="jam_selesai"
                               x-model="jamSelesai"
                               @change="calculateEstimasi()"
                               required
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition shadow-xs text-slate-800 font-semibold" />
                    </div>
                </div>

                <!-- Live Estimasi Uang Lembur Card -->
                <div x-show="jenisLembur && jamMulai && jamSelesai" x-cloak class="p-4 bg-teal-50/80 border border-teal-200 rounded-2xl flex items-center justify-between gap-3 max-w-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                            Rp
                        </div>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800 block">Estimasi Uang Lembur</span>
                            <span class="text-[11px] text-slate-600 block mt-0.5" x-text="estimasiKeterangan"></span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-base sm:text-lg font-black text-teal-900 block" x-text="'Rp ' + Number(estimasiNominal).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Alasan Lembur with Character Counter -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 tracking-wide">
                        Alasan &amp; Rincian Pekerjaan Lembur <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="alasan"
                              rows="4"
                              maxlength="100"
                              x-model="alasan"
                              required
                              placeholder="Jelaskan alasan dan rincian pekerjaan yang dilakukan selama jam lembur..."
                              class="w-full text-xs rounded-xl border-slate-300 p-3.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition shadow-xs text-slate-800 placeholder-slate-400"></textarea>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Maksimal 100 karakter</span>
                        <span class="font-bold text-slate-700" x-text="(alasan ? alasan.length : 0) + '/100 karakter'">0/100 karakter</span>
                    </div>
                </div>

                <!-- Tombol Simpan Lembur -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-500">
                        Pastikan semua informasi telah benar sebelum diajukan untuk proses approval atasan.
                    </div>
                    <div class="w-full sm:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <a href="{{ route('gomsn.overtime.index') }}"
                           class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs text-center transition">
                            Batal
                        </a>
                        <button type="submit"
                                :disabled="!canSubmit"
                                :class="canSubmit ? 'opacity-100 hover:opacity-90 shadow-md cursor-pointer' : 'opacity-50 cursor-not-allowed'"
                                style="background: linear-gradient(135deg, #18C4C4 0%, #0D9488 100%) !important;"
                                class="px-6 py-2.5 text-white font-bold text-xs rounded-xl inline-flex items-center justify-center gap-2 transition duration-200">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Simpan &amp; Ajukan Lembur</span>
                        </button>
                    </div>
                </div>

                <div x-show="!hasAttendance && attendanceChecked" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-center text-xs text-rose-700 font-semibold">
                    Tombol simpan dinonaktifkan karena absensi belum terdaftar pada tanggal yang dipilih.
                </div>
            </form>
        </div>
    </div>

    <script>
        function overtimeForm() {
            return {
                tanggal: '{{ \Carbon\Carbon::now()->toDateString() }}',
                jenisLembur: '{{ old('jenis_lembur', '') }}',
                jamMulai: '{{ old('jam_mulai', '') }}',
                jamSelesai: '{{ old('jam_selesai', '') }}',
                alasan: '{{ old('alasan', '') }}',
                checking: false,
                attendanceChecked: false,
                hasAttendance: false,
                attendanceData: {},
                estimasiNominal: 0,
                estimasiKeterangan: '',

                get canSubmit() {
                    return this.hasAttendance && 
                           this.jenisLembur && 
                           this.jamMulai && 
                           this.jamSelesai && 
                           this.alasan.trim().length > 0;
                },

                calculateEstimasi() {
                    if (!this.jenisLembur) {
                        this.estimasiNominal = 0;
                        this.estimasiKeterangan = '';
                        return;
                    }

                    const jenis = this.jenisLembur.toLowerCase();

                    // 3. Piket Hari Kerja (Flat Rp 15.000)
                    if (jenis.includes('piket') && jenis.includes('kerja')) {
                        this.estimasiNominal = 15000;
                        this.estimasiKeterangan = 'Tarif flat Rp 15.000 (tidak dihitung per jam)';
                        return;
                    }

                    // Perhitungan berbasis jam jika jam_mulai dan jam_selesai terisi
                    if (!this.jamMulai || !this.jamSelesai) {
                        this.estimasiNominal = 0;
                        this.estimasiKeterangan = 'Isi jam mulai & selesai untuk menghitung nominal';
                        return;
                    }

                    const [hM, mM] = this.jamMulai.split(':').map(Number);
                    const [hS, mS] = this.jamSelesai.split(':').map(Number);
                    let diffMinutes = (hS * 60 + mS) - (hM * 60 + mM);
                    if (diffMinutes < 0) {
                        diffMinutes += 24 * 60; // Lintas hari
                    }
                    const hours = diffMinutes / 60;
                    const hDisplay = Math.floor(diffMinutes / 60);
                    const mDisplay = diffMinutes % 60;

                    // 2. Piket Hari Libur (Rp 3.900 / jam)
                    if (jenis.includes('piket') && (jenis.includes('libur') || jenis.includes('minggu') || jenis.includes('merah'))) {
                        this.estimasiNominal = Math.round(hours * 3900);
                        this.estimasiKeterangan = `${hDisplay} jam ${mDisplay} mnt x Rp 3.900/jam`;
                        return;
                    }

                    // 1. Lembur Biasa (Rp 5.000 / jam)
                    this.estimasiNominal = Math.round(hours * 5000);
                    this.estimasiKeterangan = `${hDisplay} jam ${mDisplay} mnt x Rp 5.000/jam`;
                },

                init() {
                    if (this.tanggal) {
                        this.checkAttendance();
                    }
                    this.calculateEstimasi();
                },

                async checkAttendance() {
                    if (!this.tanggal) {
                        this.attendanceChecked = false;
                        this.hasAttendance = false;
                        return;
                    }

                    this.checking = true;
                    this.attendanceChecked = false;

                    try {
                        const res = await fetch(`{{ route('gomsn.overtime.check-attendance') }}?tanggal=${encodeURIComponent(this.tanggal)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();
                        this.attendanceData = data;
                        this.hasAttendance = !!data.has_attendance;
                        this.attendanceChecked = true;
                    } catch (err) {
                        console.error('Attendance check error:', err);
                        this.hasAttendance = false;
                        this.attendanceChecked = true;
                        this.attendanceData = {
                            message: 'Gagal menghubungi server',
                            subtext: 'Terjadi kendala saat memeriksa absensi.'
                        };
                    } finally {
                        this.checking = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>

