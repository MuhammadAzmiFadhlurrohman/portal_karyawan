<x-app-layout>
    <div class="w-full space-y-6">
        <!-- Top Breadcrumbs & Heading -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('gomsn.overtime.index') }}" 
                   class="h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 flex items-center justify-center shadow-xs transition shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg sm:text-xl font-heading font-bold text-[#1E3A8A] tracking-tight">
                        Form Pengajuan Lembur
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-500">
                        Pastikan Anda telah melakukan absensi hadir sebelum mengajukan jam kerja lembur.
                    </p>
                </div>
            </div>
            <a href="{{ route('gomsn.overtime.index') }}" class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-blue-600 hover:text-blue-800 font-semibold shadow-2xs">
                <span>Daftar Lembur</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if (session('error'))
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl flex items-start gap-3 shadow-xs">
            <div class="w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                ✕
            </div>
            <div>
                <div class="text-xs font-bold text-rose-800">Gagal Mengajukan Lembur</div>
                <div class="text-xs text-rose-600 mt-0.5">{{ session('error') }}</div>
            </div>
        </div>
        @endif

        <!-- Overtime Form Card (Pixel-aligned with User Mockup) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-8"
             x-data="overtimeForm()"
             x-init="init()">
            <form method="POST" action="{{ route('gomsn.overtime.store') }}" class="space-y-6">
                @csrf

                <!-- Tanggal Lembur -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Lembur
                    </label>
                    <div class="relative">
                        <input type="date"
                               name="tanggal"
                               x-model="tanggal"
                               @change="checkAttendance()"
                               required
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs text-slate-800 font-medium" />
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Pastikan Anda sudah melakukan absensi pada tanggal ini
                    </p>

                    <!-- Checking Indicator -->
                    <div x-show="checking" x-cloak class="mt-2.5 p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2.5 text-xs text-slate-600">
                        <svg class="animate-spin h-4 w-4 text-blue-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memeriksa data absensi tanggal terpilih...</span>
                    </div>

                    <!-- No Attendance Alert (Screenshot Exact Match) -->
                    <div x-show="!checking && attendanceChecked && !hasAttendance" 
                         x-cloak 
                         class="mt-3 p-3.5 bg-[#FFF5F5] border-l-4 border-rose-500 rounded-r-xl flex items-start gap-3 shadow-xs">
                        <div class="w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                            ✕
                        </div>
                        <div>
                            <div class="text-xs font-bold text-rose-800 flex items-center gap-1.5">
                                <span>Tidak ditemukan absensi pada tanggal tersebut</span>
                            </div>
                            <p class="text-[11px] text-rose-600 mt-0.5">
                                Pastikan Anda sudah melakukan absensi dengan status Hadir/Tepat Waktu
                            </p>
                        </div>
                    </div>

                    <!-- Attendance Verified Box -->
                    <div x-show="!checking && attendanceChecked && hasAttendance" 
                         x-cloak 
                         class="mt-3 p-3.5 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-start gap-3 shadow-xs">
                        <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
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
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jenis Lembur
                    </label>
                    <select name="jenis_lembur"
                            x-model="jenisLembur"
                            required
                            class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs text-slate-800">
                        <option value="">-- Pilih Jenis Lembur --</option>
                        <option value="Lembur Hari Kerja">Lembur Hari Kerja</option>
                        <option value="Lembur Hari Libur / Tanggal Merah">Lembur Hari Libur / Tanggal Merah</option>
                        <option value="Lembur Darurat / Penanganan Gangguan">Lembur Darurat / Penanganan Gangguan</option>
                        <option value="Lembur Proyek / Tugas Lapangan">Lembur Proyek / Tugas Lapangan</option>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Silakan pilih jenis lembur yang sesuai.
                    </p>
                </div>

                <!-- Jam Mulai & Jam Selesai -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jam Mulai Lembur
                        </label>
                        <input type="time"
                               name="jam_mulai"
                               x-model="jamMulai"
                               required
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs text-slate-800 font-medium" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jam Selesai Lembur
                        </label>
                        <input type="time"
                               name="jam_selesai"
                               x-model="jamSelesai"
                               required
                               class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs text-slate-800 font-medium" />
                    </div>
                </div>

                <!-- Alasan Lembur with Character Counter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alasan Lembur
                    </label>
                    <textarea name="alasan"
                              rows="4"
                              maxlength="100"
                              x-model="alasan"
                              required
                              placeholder="Jelaskan alasan dan pekerjaan yang dilakukan selama lembur"
                              class="w-full text-xs rounded-xl border-slate-300 p-3.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs text-slate-800 placeholder-slate-400"></textarea>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                        <span>Maksimal 100 karakter</span>
                        <span class="font-medium text-slate-600" x-text="(alasan ? alasan.length : 0) + '/100 karakter'">0/100 karakter</span>
                    </div>
                </div>

                <!-- Tombol Simpan Lembur -->
                <div class="pt-2">
                    <button type="submit"
                            :disabled="!canSubmit"
                            :class="canSubmit ? 'bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 cursor-pointer' : 'bg-blue-400 opacity-60 cursor-not-allowed'"
                            class="w-full py-3 px-6 text-white font-semibold text-sm rounded-xl flex items-center justify-center gap-2 transition duration-200">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span>Simpan Lembur</span>
                    </button>
                    <p x-show="!hasAttendance && attendanceChecked" x-cloak class="text-center text-[11px] text-rose-500 font-medium mt-2">
                        Tombol dinonaktifkan karena absensi belum terdaftar di tanggal tersebut.
                    </p>
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

                get canSubmit() {
                    return this.hasAttendance && 
                           this.jenisLembur && 
                           this.jamMulai && 
                           this.jamSelesai && 
                           this.alasan.trim().length > 0;
                },

                init() {
                    if (this.tanggal) {
                        this.checkAttendance();
                    }
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

