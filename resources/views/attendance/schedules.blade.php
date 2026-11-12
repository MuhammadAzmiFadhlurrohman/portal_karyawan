<x-layouts.app title="Jadwal Kerja Karyawan">
    <div x-data="{
        searchQuery: '',
        showUploadModal: false,
        showDeleteModal: false,
        deleteYear: null,
        deleteMonth: null,
        deleteMonthName: '',
        activeScope: 'all',
        toastMessage: '',
        showToast: false,
        triggerToast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3500);
        }
    }">

        <!-- Floating Notification Toast -->
        <div x-cloak x-show="showToast"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl text-xs font-medium border border-slate-700">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span x-text="toastMessage"></span>
        </div>

        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 mb-6 flex items-center gap-3 text-emerald-900 text-xs shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 mb-6 text-rose-900 text-xs shadow-xs">
                <div class="font-bold mb-1 flex items-center gap-2 text-rose-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Terjadi kesalahan:
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- VIEW MODE 0: JADWAL KERJA KARYAWAN & TUKAR / BACKUP SHIFT                 --}}
        {{-- (Untuk Karyawan, Atasan, HRD, Finance dengan Fitur Tukar & Backup Shift)  --}}
        {{-- ========================================================================= --}}
        @if ($viewMode === 'personal')
            <div x-data="{
                showActionModal: false,
                showTukarModal: false,
                showBackupModal: false,

                // Selected date & shift to be swapped/backed up
                selectedDate: '',
                selectedDateFormatted: '',
                selectedShiftLabel: '',

                // Target form inputs
                targetDate: '',
                selectedPeerId: '',
                targetShiftLabel: '-',
                swapReason: '',
                availableCount: 0,

                // Preloaded data for instant feedback
                peers: @js($divisionPeers->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'employee_code' => $p->employee_code, 'division' => $p->division])),
                peerSchedules: @js($peerSchedules),

                openPilihAksi(date, dateFormatted, shiftLabel) {
                    this.selectedDate = date;
                    this.selectedDateFormatted = dateFormatted;
                    this.selectedShiftLabel = shiftLabel;
                    this.targetDate = date;
                    this.selectedPeerId = '';
                    this.targetShiftLabel = '-';
                    this.swapReason = '';
                    this.updateAvailableCount();
                    this.showActionModal = true;
                },

                chooseTukar() {
                    this.showActionModal = false;
                    this.showTukarModal = true;
                    this.updateTargetShift();
                },

                chooseBackup() {
                    this.showActionModal = false;
                    this.targetDate = this.selectedDate;
                    this.showBackupModal = true;
                    this.updateTargetShift();
                },

                updateTargetShift() {
                    if (!this.selectedPeerId || !this.targetDate) {
                        this.targetShiftLabel = '-';
                        this.updateAvailableCount();
                        return;
                    }

                    // 1. Try local cache
                    const userMap = this.peerSchedules[this.selectedPeerId];
                    if (userMap && userMap[this.targetDate]) {
                        this.targetShiftLabel = userMap[this.targetDate];
                        this.updateAvailableCount();
                        return;
                    }

                    // 2. Fetch via AJAX
                    fetch(`{{ route('attendance.schedules.peer-shift') }}?user_id=${this.selectedPeerId}&date=${this.targetDate}`)
                        .then(res => res.json())
                        .then(data => {
                            this.targetShiftLabel = data.shift || '-';
                            if (data.available_count !== undefined) {
                                this.availableCount = data.available_count;
                            }
                        })
                        .catch(() => {
                            this.targetShiftLabel = '-';
                        });

                    this.updateAvailableCount();
                },

                updateAvailableCount() {
                    let count = 0;
                    const dt = this.targetDate || this.selectedDate;
                    for (const pid in this.peerSchedules) {
                        if (this.peerSchedules[pid] && this.peerSchedules[pid][dt]) {
                            count++;
                        }
                    }
                    this.availableCount = Math.max(count, this.peers.length > 0 ? 1 : 0);
                }
            }" class="space-y-6">

                @if ($canManageSchedule)
                    <!-- Admin Navigation Switch -->
                    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#155E63] text-white shadow-xs whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Jadwal Saya</span>
                        </span>
                        <a href="{{ route('attendance.schedules', ['view' => 'user']) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#155E63] hover:bg-teal-50 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>📅 Kalender & Custom Jadwal</span>
                        </a>
                        <a href="{{ route('attendance.schedules', ['view' => 'list']) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#155E63] hover:bg-teal-50 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Kelola Periode Bulan</span>
                        </a>
                    </div>
                @endif

                <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
                <div class="relative overflow-hidden rounded-3xl border border-cyan-400/30 shadow-[0_6px_24px_rgba(2,43,58,0.3)] p-4 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5 sm:gap-4 select-none"
                     style="background: linear-gradient(110deg, #022836 0%, #053b47 28%, #0a525f 62%, #0e6774 86%, #084c57 100%);">
                    
                    <!-- Gloss Highlights (Top & Bottom Rims) -->
                    <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                    <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

                    <!-- Left: Glowing Calendar Icon + User Info -->
                    <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-white/12 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="font-heading text-base sm:text-lg font-extrabold text-white tracking-tight leading-snug truncate">
                                Jadwal Kerja Saya
                            </h1>
                            <p class="text-[11px] sm:text-xs text-teal-100/85 mt-0.5 leading-snug truncate">
                                {{ strtoupper($actor->name) }} &bull; {{ $actor->employee_code ?? 'KR92590' }} &bull; {{ $actor->division ?: 'Operasional' }}
                            </p>
                        </div>
                    </div>

                    <!-- Right: Filter Form (Year, Month, Tampilkan) -->
                    <form method="GET" action="{{ route('attendance.schedules') }}" class="relative z-10 w-full sm:w-auto flex items-center gap-2">
                        <input type="hidden" name="view" value="my">
                        <div class="relative w-24 sm:w-auto shrink-0">
                            <select name="year" class="w-full appearance-none bg-black/25 backdrop-blur-md border border-white/15 rounded-xl px-3 py-2 pr-7 text-xs font-semibold text-white focus:outline-none focus:ring-1 focus:ring-cyan-400 transition cursor-pointer">
                                @for ($y = 2025; $y <= 2030; $y++)
                                    <option value="{{ $y }}" class="bg-slate-800 text-white" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <svg class="w-3 h-3 text-cyan-300 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <div class="relative flex-1 sm:flex-initial">
                            <select name="month" class="w-full appearance-none bg-black/25 backdrop-blur-md border border-white/15 rounded-xl px-3 py-2 pr-7 text-xs font-semibold text-white focus:outline-none focus:ring-1 focus:ring-cyan-400 transition cursor-pointer">
                                @foreach ($monthNames as $num => $name)
                                    <option value="{{ $num }}" class="bg-slate-800 text-white" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <svg class="w-3 h-3 text-cyan-300 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <button type="submit" class="btn-primary !py-2 !px-3.5 text-xs font-bold shadow-md shadow-teal-900/30 inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Tampilkan</span>
                        </button>
                    </form>
                </div>

                <!-- 2. Subheader Bar (Month Title + Tombol Permintaan) -->
                <div class="flex items-center justify-between pt-0.5 px-0.5">
                    <div class="flex items-center gap-2 text-slate-800 font-extrabold text-sm">
                        <div class="w-7 h-7 rounded-lg bg-teal-50 border border-teal-200/70 text-[#155E63] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span>{{ $monthName }} {{ $year }}</span>
                    </div>

                    <a href="{{ route('attendance.swaps') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-teal-200 bg-teal-50/80 hover:bg-teal-100 text-[#155E63] text-xs font-bold transition-all shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>Permintaan</span>
                    </a>
                </div>

                <!-- 3. Responsive Daily Schedules List -->
                <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-4 sm:px-5">TANGGAL &amp; SHIFT</th>
                                    <th class="hidden sm:table-cell py-3 px-5">JAM KERJA</th>
                                    <th class="py-3 px-4 sm:px-5 text-right sm:pr-7 w-16 sm:w-24">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $dateObj = \Carbon\Carbon::createFromDate($year, $month, $day, 'Asia/Jakarta');
                                        $isToday = $dateObj->isSameDay($today);
                                        $isFuture = $dateObj->gt($today);
                                        $dateString = $dateObj->toDateString();
                                        $dayNum = sprintf('%02d', $day);
                                        $dayNamesIndo = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
                                        $dayNamesFull = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        $dayNameStr = $dayNamesIndo[$dateObj->dayOfWeek];
                                        $dayNameLong = $dayNamesFull[$dateObj->dayOfWeek];

                                        $sched = $mySchedules->get($day);
                                        $holiday = $holidays->get($day);

                                        $isLibur = false;
                                        $shiftTitle = '';
                                        $shiftName = '';
                                        $workHours = '—';

                                        if ($sched && $sched->shift) {
                                            if ($sched->shift->code === 'L' || str_contains(strtolower($sched->shift->name), 'libur')) {
                                                $isLibur = true;
                                                $shiftTitle = 'Libur';
                                                $shiftName = 'Libur';
                                            } else {
                                                $startTime = substr($sched->shift->start_time, 0, 5);
                                                $endTime = substr($sched->shift->end_time, 0, 5);
                                                $shiftTitle = $sched->shift->name;
                                                $shiftName = $sched->shift->name . ' (' . $startTime . '–' . $endTime . ')';
                                                $workHours = $startTime . ' – ' . $endTime;
                                            }
                                        } elseif ($holiday || $dateObj->isWeekend()) {
                                            $isLibur = true;
                                            $shiftTitle = $holiday ? $holiday->name : 'Libur';
                                            $shiftName = $shiftTitle;
                                        } else {
                                            $shiftTitle = 'Shift Pagi';
                                            $shiftName = 'Pagi (08:15–17:00)';
                                            $workHours = '08:15 – 17:00';
                                        }
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors {{ $isToday ? 'bg-teal-50/30' : '' }}">
                                        <!-- Tanggal & Shift -->
                                        <td class="py-3 sm:py-3.5 px-3.5 sm:px-5">
                                            <div class="flex items-center gap-3 sm:gap-3.5">
                                                <!-- Date Card Badge -->
                                                <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl flex flex-col items-center justify-center shrink-0 transition-all {{ $isToday ? 'bg-[#155E63] text-white shadow-sm shadow-teal-900/30 ring-2 ring-teal-400/40' : ($isLibur ? 'bg-rose-50/60 border border-rose-200/70 text-rose-700' : 'bg-slate-50 border border-slate-200/90 text-slate-800') }}">
                                                    <span class="font-heading font-extrabold text-sm sm:text-base leading-tight">{{ $dayNum }}</span>
                                                    <span class="text-[10px] font-bold tracking-wider uppercase {{ $isToday ? 'text-teal-100' : ($isLibur ? 'text-rose-400' : 'text-slate-400') }}">{{ $dayNameStr }}</span>
                                                </div>

                                                <div class="min-w-0 flex-1 space-y-1">
                                                    <!-- Date Label Row -->
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-xs font-bold text-slate-800 truncate">
                                                            {{ $dayNameLong }}, {{ $dayNum }} {{ substr($monthName, 0, 3) }} {{ $year }}
                                                        </span>
                                                        @if ($isToday)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#155E63] text-white text-[10px] font-bold tracking-wide shrink-0">
                                                                Hari Ini
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Shift Badge + Mobile Inline Work Hours (No Double Wrapping!) -->
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        @if ($isLibur)
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 border border-rose-200/80 text-rose-600 whitespace-nowrap">
                                                                <span>⭐</span>
                                                                <span>{{ $shiftTitle }}</span>
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 border border-sky-200/80 text-sky-700 whitespace-nowrap">
                                                                <span>🌅</span>
                                                                <span>{{ $shiftTitle }}</span>
                                                            </span>
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100/90 border border-slate-200/70 text-[11px] font-mono font-semibold text-slate-600 whitespace-nowrap">
                                                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                                <span>{{ $workHours }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Jam Kerja (Desktop Column) -->
                                        <td class="hidden sm:table-cell py-3.5 px-5 text-xs font-semibold text-slate-600 font-mono whitespace-nowrap">
                                            {{ $workHours }}
                                        </td>

                                        <!-- Aksi Button -->
                                        <td class="py-3 sm:py-3.5 px-3.5 sm:px-5 text-right sm:pr-7">
                                            @if ($isFuture)
                                                <button type="button"
                                                        @click="openPilihAksi('{{ $dateString }}', '{{ $dayNum }} {{ $monthName }} {{ $year }}', '{{ $shiftName }}')"
                                                        class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#155E63] hover:bg-[#0c4349] text-white shadow-xs hover:shadow-md transition-all cursor-pointer transform hover:scale-105 active:scale-95"
                                                        title="Tukar atau Backup Shift">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                </button>
                                            @else
                                                <button type="button"
                                                        disabled
                                                        class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200/60"
                                                        title="Tanggal lampau tidak dapat ditukar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- MODAL 1: PILIH AKSI (IMAGE 3)                                             -->
                <!-- ========================================================================= -->
                <div x-show="showActionModal" 
                     x-cloak 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                    <div @click.outside="showActionModal = false"
                         x-show="showActionModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-3xl max-w-sm w-full shadow-2xl overflow-hidden border border-slate-100">
                        
                        <!-- Header Banner -->
                        <div class="bg-[#155E63] text-white px-5 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-[#17555B] flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-sm tracking-tight">Pilih Aksi</h3>
                            </div>
                            <button @click="showActionModal = false" class="w-7 h-7 rounded-lg bg-[#17555B] hover:bg-[#155E63] text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Body Options -->
                        <div class="p-5 space-y-3.5">
                            <p class="text-xs text-slate-700 font-medium">
                                Pilih aksi yang ingin dilakukan untuk jadwal ini:
                            </p>

                            <!-- Option 1: Tukar -->
                            <button @click="chooseTukar()" type="button" class="w-full text-left rounded-2xl border border-slate-200 p-4 flex items-start gap-3.5 hover:border-teal-400 hover:bg-teal-50/40 transition-all cursor-pointer group">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-[#155E63] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-[#155E63] transition-colors">Tukar</h4>
                                    <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                                        Tukar jadwal dengan karyawan divisi yang sama (tanggal bisa berbeda)
                                    </p>
                                </div>
                            </button>

                            <!-- Option 2: Backup -->
                            <button @click="chooseBackup()" type="button" class="w-full text-left rounded-2xl border border-slate-200 p-4 flex items-start gap-3.5 hover:border-emerald-400 hover:bg-emerald-50/40 transition-all cursor-pointer group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">Backup</h4>
                                    <p class="text-[11px] text-slate-500 leading-snug mt-0.5">
                                        Backup jadwal di tanggal yang sama
                                    </p>
                                </div>
                            </button>

                            <!-- Footer Button Batal -->
                            <div class="pt-2 flex justify-end">
                                <button @click="showActionModal = false" type="button" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- MODAL 2: TUKAR JADWAL (IMAGE 4)                                           -->
                <!-- ========================================================================= -->
                <div x-show="showTukarModal" 
                     x-cloak 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                    <div @click.outside="showTukarModal = false"
                         x-show="showTukarModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 max-h-[90vh] overflow-y-auto">
                        
                        <!-- Header Banner -->
                        <div class="bg-[#155E63] text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-[#17555B] flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-sm tracking-tight">Tukar Jadwal</h3>
                            </div>
                            <button @click="showTukarModal = false" class="w-7 h-7 rounded-lg bg-[#17555B] hover:bg-[#155E63] text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Form Content -->
                        <form method="POST" action="{{ route('attendance.swaps.store') }}" class="p-5 space-y-4">
                            @csrf
                            <input type="hidden" name="requester_date" :value="selectedDate">
                            <input type="hidden" name="target_date" :value="targetDate">
                            <input type="hidden" name="is_backup" value="0">

                            <!-- Info Box -->
                            <div class="rounded-xl bg-teal-50 border border-teal-200/80 p-3 text-xs text-teal-900 flex items-start gap-2.5 leading-relaxed">
                                <span class="text-base leading-none">ℹ️</span>
                                <span>Pilih karyawan dengan divisi yang sama untuk melakukan pertukaran jadwal. Anda bisa memilih tanggal target yang berbeda.</span>
                            </div>

                            <!-- Row 1: Tanggal Saya & Shift Saya -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">TANGGAL SAYA</label>
                                    <input type="text" readonly :value="selectedDate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">SHIFT SAYA</label>
                                    <input type="text" readonly :value="selectedShiftLabel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Row 2: Tanggal Target -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    TANGGAL TARGET <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       x-model="targetDate" 
                                       @change="updateTargetShift()" 
                                       min="{{ now()->toDateString() }}" 
                                       required 
                                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#155E63] focus:border-[#155E63] transition cursor-pointer">
                                <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                    <span>ℹ️</span>
                                    <span>Pilih tanggal shift target yang ingin ditukar (bisa berbeda)</span>
                                </p>
                            </div>

                            <!-- Warning Box -->
                            <div class="rounded-xl bg-amber-50 border border-amber-200/80 p-3 text-xs text-amber-900 flex items-center gap-2">
                                <span>⚠️ 📌</span>
                                <span>Anda akan tukar jadwal di tanggal <strong class="font-mono text-amber-950" x-text="targetDate"></strong> dengan karyawan target.</span>
                            </div>

                            <!-- Row 3: Pilih Karyawan & Shift Target -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">PILIH KARYAWAN</label>
                                    <select name="target_user_id" 
                                            x-model="selectedPeerId" 
                                            @change="updateTargetShift()" 
                                            required 
                                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#155E63] focus:border-[#155E63] transition cursor-pointer">
                                        <option value="">-- Pilih Karyawan --</option>
                                        <template x-for="p in peers" :key="p.id">
                                            <option :value="p.id" x-text="p.name + ' (' + (p.employee_code || p.division) + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">SHIFT TARGET</label>
                                    <input type="text" readonly :value="targetShiftLabel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Row 4: Catatan -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">CATATAN (OPSIONAL)</label>
                                <textarea name="reason" 
                                          x-model="swapReason" 
                                          rows="3" 
                                          placeholder="Tambahkan catatan jika diperlukan..." 
                                          class="w-full bg-white border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#155E63] focus:border-[#155E63] transition resize-none"></textarea>
                            </div>

                            <!-- Microcopy Availability -->
                            <p class="text-[11px] text-slate-400">
                                Tersedia <span class="font-bold text-slate-700" x-text="availableCount"></span> karyawan untuk tukar di tanggal <span class="font-bold text-slate-700" x-text="targetDate"></span>.
                            </p>

                            <!-- Footer Buttons -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                <button @click="showTukarModal = false" type="button" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" 
                                        :disabled="!selectedPeerId" 
                                        :class="!selectedPeerId ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#0c4349]'"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#155E63] text-white text-xs font-semibold shadow-sm shadow-teal-900/25 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span>Kirim</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- MODAL 3: BACKUP JADWAL (IMAGE 5)                                          -->
                <!-- ========================================================================= -->
                <div x-show="showBackupModal" 
                     x-cloak 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                    <div @click.outside="showBackupModal = false"
                         x-show="showBackupModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 max-h-[90vh] overflow-y-auto">
                        
                        <!-- Header Banner -->
                        <div class="bg-[#155E63] text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-[#17555B] flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-sm tracking-tight">Backup Jadwal</h3>
                            </div>
                            <button @click="showBackupModal = false" class="w-7 h-7 rounded-lg bg-[#17555B] hover:bg-[#155E63] text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Form Content -->
                        <form method="POST" action="{{ route('attendance.swaps.store') }}" class="p-5 space-y-4">
                            @csrf
                            <input type="hidden" name="requester_date" :value="selectedDate">
                            <input type="hidden" name="target_date" :value="selectedDate">
                            <input type="hidden" name="is_backup" value="1">

                            <!-- Info Box -->
                            <div class="rounded-xl bg-teal-50 border border-teal-200/80 p-3 text-xs text-teal-900 flex items-start gap-2.5 leading-relaxed">
                                <span class="text-base leading-none">ℹ️</span>
                                <span>Backup jadwal di tanggal yang sama dengan karyawan divisi yang sama.</span>
                            </div>

                            <!-- Row 1: Tanggal Saya & Shift Saya -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">TANGGAL SAYA</label>
                                    <input type="text" readonly :value="selectedDate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">SHIFT SAYA</label>
                                    <input type="text" readonly :value="selectedShiftLabel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Row 2: Pilih Karyawan & Shift Target -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">PILIH KARYAWAN</label>
                                    <select name="target_user_id" 
                                            x-model="selectedPeerId" 
                                            @change="updateTargetShift()" 
                                            required 
                                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#155E63] focus:border-[#155E63] transition cursor-pointer">
                                        <option value="">-- Pilih Karyawan --</option>
                                        <template x-for="p in peers" :key="p.id">
                                            <option :value="p.id" x-text="p.name + ' (' + (p.employee_code || p.division) + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">SHIFT TARGET</label>
                                    <input type="text" readonly :value="targetShiftLabel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Row 3: Catatan -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">CATATAN (OPSIONAL)</label>
                                <textarea name="reason" 
                                          x-model="swapReason" 
                                          rows="3" 
                                          placeholder="Tambahkan catatan jika diperlukan..." 
                                          class="w-full bg-white border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#155E63] focus:border-[#155E63] transition resize-none"></textarea>
                            </div>

                            <!-- Microcopy Availability -->
                            <p class="text-[11px] text-slate-400">
                                Tersedia <span class="font-bold text-slate-700" x-text="availableCount"></span> karyawan untuk backup di tanggal <span class="font-bold text-slate-700" x-text="selectedDate"></span>.
                            </p>

                            <!-- Footer Buttons -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                <button @click="showBackupModal = false" type="button" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" 
                                        :disabled="!selectedPeerId" 
                                        :class="!selectedPeerId ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#0c4349]'"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#155E63] text-white text-xs font-semibold shadow-sm shadow-teal-900/25 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span>Kirim</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- VIEW MODE 1: INDEX DAFTAR BULAN JADWAL KERJA (SCREEN 1)                   --}}
        {{-- ========================================================================= --}}
        @if ($viewMode === 'list')
            <div class="space-y-5">
                @if ($canManageSchedule)
                    <!-- Admin Navigation Switch -->
                    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
                        <a href="{{ route('attendance.schedules', ['view' => 'my']) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#155E63] hover:bg-teal-50 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Jadwal Saya</span>
                        </a>
                        <a href="{{ route('attendance.schedules', ['view' => 'user']) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#155E63] hover:bg-teal-50 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>📅 Kalender & Custom Jadwal</span>
                        </a>
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#155E63] text-white shadow-xs whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Kelola Periode Bulan</span>
                        </span>
                    </div>
                @endif

                <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
                <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
                     style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
                    
                    <!-- Gloss Highlights (Top & Bottom Rims) -->
                    <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                    <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

                    <!-- Left: Glowing Calendar Icon + Page Title -->
                    <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                                <span>Jadwal Kerja Karyawan</span>
                            </h1>
                            <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                                Kelola pembagian shift roster kerja, generate jadwal otomatis, dan pemantauan per bulan.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Action Button (+ Generate Jadwal) -->
                    @if ($canManageSchedule)
                    <div class="relative z-10 shrink-0">
                        <a href="{{ route('attendance.schedules', ['view' => 'generate']) }}"
                           class="btn-primary !py-2.5 !px-4 text-xs font-bold shadow-md shadow-teal-900/30 flex items-center gap-2 cursor-pointer transition">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>+ Generate Jadwal</span>
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Search Input Bar -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-2.5 shadow-xs flex items-center gap-3 focus-within:border-[#155E63] focus-within:ring-1 focus-within:ring-[#155E63] transition-all">
                    <svg class="w-4 h-4 text-slate-400 ml-2 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari bulan atau tahun..."
                           class="w-full bg-transparent text-xs text-slate-800 placeholder-slate-400 focus:outline-hidden">
                </div>

                <!-- Month Cards List -->
                <div class="space-y-3">
                    @forelse ($summaries as $item)
                        <div x-show="!searchQuery || '{{ strtolower($item['month_name'].' '.$item['year'].' '.$item['month_code']) }}'.includes(searchQuery.toLowerCase())"
                             class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-all hover:border-teal-300 hover:shadow-sm">
                            <div class="flex items-center gap-4">
                                <!-- Month Code Box Badge -->
                                <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-200/80 flex flex-col items-center justify-center shrink-0">
                                    <span class="font-heading font-extrabold text-sm text-[#155E63] uppercase tracking-wider">{{ $item['month_code'] }}</span>
                                    <span class="text-[10px] text-teal-800/70 font-mono font-medium">{{ $item['year'] }}</span>
                                </div>

                                <div>
                                    <h2 class="font-heading font-bold text-sm text-slate-900">{{ $item['month_name'] }}</h2>
                                    <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            {{ $item['users_count'] }} karyawan
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $item['days_in_month'] }} hari
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2.5 sm:pt-0 border-t sm:border-0 border-slate-100 w-full sm:w-auto justify-between sm:justify-end shrink-0">
                                <!-- Status Badge -->
                                @if ($item['is_ongoing'])
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Berjalan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Selesai
                                    </span>
                                @endif

                                <!-- Detail Button -->
                                <a href="{{ route('attendance.schedules', ['view' => 'month', 'year' => $item['year'], 'month' => $item['month']]) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#155E63] border border-teal-200/80 text-xs font-semibold transition-all shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Detail</span>
                                </a>

                                <!-- Hapus Button -->
                                @if ($canManageSchedule)
                                    <button type="button"
                                            @click="deleteYear = {{ $item['year'] }}; deleteMonth = {{ $item['month'] }}; deleteMonthName = '{{ $item['month_name'] }} {{ $item['year'] }}'; showDeleteModal = true"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-all shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-white border border-slate-200 p-8 text-center text-slate-500 text-xs">
                            Belum ada jadwal yang di-generate. Silakan klik tombol <strong>+ Generate</strong> di atas untuk membuat jadwal pertama.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Modal Konfirmasi Hapus Bulan -->
            <div x-cloak x-show="showDeleteModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
                <div @click.away="showDeleteModal = false"
                     class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-sm w-full p-6 text-center space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900">Hapus Jadwal Bulan?</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Anda akan menghapus seluruh data jadwal kerja untuk <span class="font-bold text-slate-700" x-text="deleteMonthName"></span>. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('attendance.schedules.delete-month') }}" class="flex items-center gap-3 pt-2">
                        @csrf
                        <input type="hidden" name="year" :value="deleteYear">
                        <input type="hidden" name="month" :value="deleteMonth">
                        <button type="button" @click="showDeleteModal = false"
                                class="w-1/2 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs">
                            Hapus Semua
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- VIEW MODE 2: GENERATE JADWAL BULANAN (SCREEN 2)                           --}}
        {{-- ========================================================================= --}}
        @if ($viewMode === 'generate')
            <div class="space-y-6 max-w-3xl mx-auto">
                <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
                <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex items-center justify-between gap-4 select-none"
                     style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
                    
                    <!-- Gloss Highlights (Top & Bottom Rims) -->
                    <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                    <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

                    <!-- Left: Back Button + Glowing Icon + Title -->
                    <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                        <a href="{{ route('attendance.schedules', ['view' => 'list']) }}"
                           class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center shrink-0 shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                                <span>Generate Jadwal Bulanan</span>
                            </h1>
                            <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                                Buat jadwal shift otomatis untuk seluruh karyawan atau per user dalam satu bulan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-7 shadow-xs">
                    <form method="POST" action="{{ route('attendance.schedules.generate') }}" class="space-y-6">
                        @csrf

                        <!-- Header Konfigurasi Jadwal -->
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                            <svg class="w-4 h-4 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span class="font-heading font-bold text-xs uppercase tracking-wider text-slate-700">Konfigurasi Jadwal</span>
                        </div>

                        <!-- Scope Switcher: [Semua Karyawan] / [Per Karyawan] -->
                        <div class="grid grid-cols-2 p-1 rounded-2xl bg-slate-100/80 border border-slate-200/60 max-w-md">
                            <button type="button"
                                    @click="activeScope = 'all'"
                                    :class="activeScope === 'all' ? 'bg-white text-[#155E63] font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Semua Karyawan</span>
                            </button>
                            <button type="button"
                                    @click="activeScope = 'user'"
                                    :class="activeScope === 'user' ? 'bg-white text-[#155E63] font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Per Karyawan</span>
                            </button>
                        </div>
                        <input type="hidden" name="scope" :value="activeScope">

                        <!-- Employee Select Dropdown (Visible only when scope is 'user') -->
                        <div x-show="activeScope === 'user'" x-transition class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Pilih Karyawan</label>
                            <select name="user_id" class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:outline-hidden focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($activeUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->employee_code ?: 'KR'.$u->id }}) - {{ $u->division }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Periode Section -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <svg class="w-4 h-4 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Periode</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                                    <select name="year" class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 font-semibold focus:outline-hidden focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                                        @for ($y = now('Asia/Jakarta')->year - 1; $y <= now('Asia/Jakarta')->year + 2; $y++)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                                    <select name="month" class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 font-semibold focus:outline-hidden focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                                        @foreach ($monthNames as $mNum => $mName)
                                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Shift Default Section -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <svg class="w-4 h-4 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Shift Default</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Shift Untuk Hari Kerja</label>
                                <select name="default_shift_code" class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 font-semibold focus:outline-hidden focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                                    @foreach ($shifts->where('is_day_off', false) as $s)
                                        <option value="{{ $s->code }}" {{ $s->code === 'P' ? 'selected' : '' }}>
                                            {{ $s->name }} ({{ substr((string)$s->start_time, 0, 5) }} – {{ substr((string)$s->end_time, 0, 5) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Info Blue Box -->
                        <div class="rounded-2xl bg-teal-50/80 border border-teal-200/80 p-4 sm:p-5 flex gap-3 text-xs text-teal-900 leading-relaxed">
                            <div class="w-6 h-6 rounded-full bg-[#155E63] text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                                i
                            </div>
                            <div class="space-y-1.5">
                                <span class="font-heading font-bold text-[#072d31] block text-xs">Informasi</span>
                                <ul class="space-y-1 text-[11px] text-teal-900/90">
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-teal-600 shrink-0">•</span>
                                        <span>Akan generate jadwal untuk <strong x-text="activeScope === 'all' ? 'SEMUA karyawan aktif' : 'karyawan terpilih'"></strong></span>
                                    </li>
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-teal-600 shrink-0">•</span>
                                        <span>Jadwal piket (<strong>Piket &amp; Piket Siang</strong>) akan tetap dipertahankan</span>
                                    </li>
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-teal-600 shrink-0">•</span>
                                        <span>Hari libur nasional dan akhir pekan otomatis menjadi <strong>Libur</strong></span>
                                    </li>
                                    <li class="flex items-start gap-1.5">
                                        <span class="text-teal-600 shrink-0">•</span>
                                        <span>Jika jadwal sudah ada, akan diupdate (kecuali piket)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <a href="{{ route('attendance.schedules') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                <span>Kembali</span>
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#0c4349] text-white text-xs font-semibold shadow-md shadow-teal-900/25 transition-all">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                <span x-text="activeScope === 'all' ? 'Generate Semua' : 'Generate Karyawan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- VIEW MODE 3: DAFTAR KARYAWAN PER BULAN (SCREEN 3)                         --}}
        {{-- ========================================================================= --}}
        @if ($viewMode === 'month')
            <div class="space-y-6">
                <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
                <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 select-none"
                     style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
                    
                    <!-- Gloss Highlights (Top & Bottom Rims) -->
                    <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                    <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

                    <!-- Left: Back Button + Glowing Icon + Title -->
                    <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                        <a href="{{ route('attendance.schedules', ['view' => 'list']) }}"
                           class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center shrink-0 shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                                <span>Roster Shift: {{ $monthName }} {{ $year }}</span>
                            </h1>
                            <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                                Daftar shift karyawan, status pengisian jadwal, dan cetak roster divisi.
                            </p>
                        </div>
                    </div>

                    <!-- Right Controls: Badges & Action Buttons -->
                    <div class="relative z-10 flex flex-wrap items-center gap-2.5">
                        <!-- Stats Badges -->
                        <div class="flex items-center gap-2 bg-black/25 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-xl text-xs font-semibold text-white">
                            <span title="Total Karyawan Aktif" class="flex items-center gap-1">
                                👥 <span>{{ $totalUsersCount }}</span>
                            </span>
                            <span class="text-white/30">|</span>
                            <span title="Karyawan Terdaftar Jadwal" class="flex items-center gap-1 text-emerald-300">
                                ✔ <span>{{ $registeredCount }}</span>
                            </span>
                            <span class="text-white/30">|</span>
                            <span title="Karyawan Belum Ada Jadwal" class="flex items-center gap-1 text-amber-300">
                                ⚠️ <span>{{ $unregisteredCount }}</span>
                            </span>
                        </div>

                        <!-- Upload Button -->
                        @if ($canManageSchedule)
                            <button type="button"
                                    @click="showUploadModal = true"
                                    class="btn-primary !py-2 !px-3.5 text-xs font-bold shadow-md shadow-teal-900/30 flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span>Upload</span>
                            </button>
                        @endif

                        <!-- Export Button -->
                        <a href="{{ route('attendance.schedules.export', ['year' => $year, 'month' => $month]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold shadow-xs transition">
                            <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Export</span>
                        </a>

                        <!-- Print Semua Button -->
                        <button type="button"
                                onclick="window.print()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Print</span>
                        </button>

                        <!-- Print Divisi Dropdown -->
                        <div x-data="{ openDivPrint: false }" class="relative">
                            <button type="button"
                                    @click="openDivPrint = !openDivPrint"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                                <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>Print Divisi</span>
                                <svg class="w-3 h-3 text-cyan-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openDivPrint" @click.away="openDivPrint = false"
                                 class="absolute right-0 mt-2 w-48 rounded-2xl bg-white border border-slate-200 shadow-xl py-2 z-30 text-xs">
                                <div class="px-3 py-1 font-bold text-[10px] uppercase tracking-wider text-slate-400">Pilih Divisi</div>
                                <a href="{{ route('attendance.schedules', ['view' => 'month', 'year' => $year, 'month' => $month]) }}"
                                   class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50 font-medium">Semua Divisi</a>
                                @foreach ($allDivisions as $div)
                                    <a href="{{ route('attendance.schedules', ['view' => 'month', 'year' => $year, 'month' => $month, 'division' => $div]) }}"
                                       class="block px-3 py-1.5 text-slate-700 hover:bg-slate-50 {{ $division === $div ? 'font-bold text-[#155E63] bg-teal-50/50' : '' }}">
                                        {{ $div }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hari Libur Nasional Card -->
                <div class="rounded-3xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h2 class="font-heading font-bold text-sm text-slate-900">Hari Libur Nasional</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                            {{ $holidays->count() }} libur
                        </span>
                    </div>

                    @if ($canManageSchedule)
                        <!-- Inline Form Tambah Libur -->
                        <form method="POST" action="{{ route('attendance.schedules.holidays.store') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <div class="sm:col-span-4">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal Libur</label>
                                <input type="date" name="date" required value="{{ sprintf('%04d-%02d-01', $year, $month) }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 font-mono focus:outline-hidden focus:border-violet-500">
                            </div>
                            <div class="sm:col-span-5">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama Libur</label>
                                <input type="text" name="name" required placeholder="Contoh: Idul Fitri"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-hidden focus:border-violet-500">
                            </div>
                            <div class="sm:col-span-3">
                                <button type="submit"
                                        class="w-full py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Tambah Libur</span>
                                </button>
                            </div>
                        </form>
                    @endif

                    <!-- Existing Holidays List -->
                    @if ($holidays->isNotEmpty())
                        <div class="flex flex-wrap gap-2 pt-1">
                            @foreach ($holidays as $h)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-violet-50/80 border border-violet-200 text-xs text-violet-900 font-medium">
                                    <span class="font-bold font-mono">{{ $h->date->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span>{{ $h->name }}</span>
                                    @if ($canManageSchedule)
                                        <form method="POST" action="{{ route('attendance.schedules.holidays.destroy', $h) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus hari libur"
                                                    class="text-violet-400 hover:text-rose-600 p-0.5 rounded transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Belum ada hari libur nasional bulan ini</p>
                    @endif

                    <!-- Info Alert -->
                    <div class="rounded-2xl bg-slate-50 border border-slate-200/80 px-4 py-2.5 flex items-center gap-2 text-xs text-slate-600">
                        <div class="w-4 h-4 rounded-full bg-violet-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0">i</div>
                        <span>Hari libur nasional akan otomatis mengubah jadwal karyawan menjadi libur nasional</span>
                    </div>
                </div>

                <!-- Data Karyawan Table Card -->
                <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-[#155E63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <h2 class="font-heading font-bold text-sm text-slate-900">Data Karyawan</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-[#155E63] border border-teal-200">
                                {{ $users->count() }}
                            </span>
                        </div>

                        <!-- Live Search Input -->
                        <div class="relative w-full sm:w-64">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari karyawan..."
                                   class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63]">
                        </div>
                    </div>

                    {{-- Mobile View: Employee Schedule Cards --}}
                    <div class="sm:hidden divide-y divide-slate-100">
                        @forelse ($users as $idx => $u)
                            @php
                                $hasSched = $schedulesByUser->has($u->id) && $schedulesByUser->get($u->id)->isNotEmpty();
                            @endphp
                            <div x-show="!searchQuery || '{{ addslashes(strtolower($u->name.' '.$u->employee_code.' '.$u->division)) }}'.includes(searchQuery.toLowerCase())"
                                 class="p-4 hover:bg-slate-50/70 transition-colors">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#155E63] to-[#0c4349] text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs uppercase">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-heading font-bold text-slate-900 block text-sm">{{ $u->name }}</span>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-bold text-rose-600 bg-rose-50 border border-rose-200">
                                                    {{ $u->employee_code ?: 'KR'.$u->id }}
                                                </span>
                                                <span class="text-xs text-slate-500 font-medium">• {{ $u->division ?: 'Umum' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($hasSched)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 shrink-0">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Terdaftar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 shrink-0">
                                            <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            Belum Ada
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-2 text-xs">
                                    <span class="text-slate-500 flex items-center gap-1.5 font-mono text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $u->phone ?: '08' . rand(1000000000, 9999999999) }}
                                    </span>
                                    <a href="{{ route('attendance.schedules', ['view' => 'user', 'user_id' => $u->id, 'year' => $year, 'month' => $month]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#155E63] border border-teal-200 text-xs font-bold transition-all shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Kelola Jadwal</span>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400 italic text-xs">
                                Tidak ada karyawan yang ditemukan.
                            </div>
                        @endforelse
                    </div>

                    {{-- Desktop View: Table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Karyawan</th>
                                    <th class="py-3 px-4">Kode</th>
                                    <th class="py-3 px-4">Telepon</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($users as $idx => $u)
                                    @php
                                        $hasSched = $schedulesByUser->has($u->id) && $schedulesByUser->get($u->id)->isNotEmpty();
                                    @endphp
                                    <tr x-show="!searchQuery || '{{ addslashes(strtolower($u->name.' '.$u->employee_code.' '.$u->division)) }}'.includes(searchQuery.toLowerCase())"
                                        class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4 text-center font-mono text-slate-400 font-medium">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#155E63] to-[#0c4349] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs uppercase">
                                                    {{ substr($u->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="font-heading font-bold text-slate-900 block text-xs">{{ $u->name }}</span>
                                                    <span class="text-[11px] text-slate-500 block">{{ $u->division ?: 'Umum' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-block px-2.5 py-0.5 rounded-md font-mono text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200">
                                                {{ $u->employee_code ?: 'KR'.$u->id }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-mono text-slate-600">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                {{ $u->phone ?: '08' . rand(1000000000, 9999999999) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($hasSched)
                                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Terdaftar
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600">
                                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    Belum Ada
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ route('attendance.schedules', ['view' => 'user', 'user_id' => $u->id, 'year' => $year, 'month' => $month]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#155E63] border border-teal-200 text-xs font-semibold transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Jadwal</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400 italic">
                                            Tidak ada karyawan yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Screen 4: Upload Jadwal Piket -->
            <div x-cloak x-show="showUploadModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.away="showUploadModal = false"
                     class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-7 space-y-5">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="font-heading font-bold text-base text-slate-900">Upload Jadwal Piket</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Upload jadwal piket karyawan format Excel atau CSV</p>
                        </div>
                        <button type="button" @click="showUploadModal = false"
                                class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Download Template Section -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-800">Download Template</label>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('attendance.schedules.template', ['year' => $year, 'month' => $month, 'division' => 'all']) }}"
                               class="px-3 py-1.5 rounded-xl border border-teal-200 bg-teal-50/70 hover:bg-teal-100 text-[#155E63] text-xs font-semibold transition-all">
                                Semua Divisi
                            </a>
                            @foreach (['Umum', 'Teknik', 'NOC', 'HRD', 'Finance'] as $dName)
                                <a href="{{ route('attendance.schedules.template', ['year' => $year, 'month' => $month, 'division' => $dName]) }}"
                                   class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium transition-all">
                                    {{ $dName }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form method="POST" action="{{ route('attendance.schedules.upload-piket') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800">File Jadwal (Excel / CSV)</label>
                            <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 hover:border-[#155E63] rounded-3xl p-6 text-center cursor-pointer transition-all bg-slate-50/50 hover:bg-teal-50/30">
                                <svg class="w-10 h-10 text-slate-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="font-bold text-xs text-slate-800 block">Klik untuk upload atau drag &amp; drop</span>
                                <span class="text-[11px] text-slate-400 mt-0.5 block">XLSX, XLS, CSV (Maksimal 10MB)</span>
                                <input type="file" name="file" required accept=".csv,.xlsx,.xls,text/csv" class="hidden"
                                       @change="$el.closest('label').querySelector('.file-name').textContent = $el.files[0]?.name || ''">
                                <span class="file-name text-xs font-mono text-[#155E63] font-bold mt-2"></span>
                            </label>
                        </div>

                        <!-- Catatan Section -->
                        <div class="space-y-1 text-[11px] text-slate-500 leading-relaxed bg-slate-50 rounded-2xl p-3.5 border border-slate-100">
                            <span class="font-bold text-slate-700 block">Catatan:</span>
                            <ol class="list-decimal list-inside space-y-0.5">
                                <li>Format file harus sesuai template di atas</li>
                                <li>Upload piket tidak akan menimpa jadwal hari kerja reguler</li>
                                <li>Jadwal piket akan otomatis dipertahankan saat generate ulang</li>
                            </ol>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="showUploadModal = false"
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#0c4349] text-white text-xs font-semibold shadow-xs">
                                Upload &amp; Proses
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- VIEW MODE 4: KALENDER JADWAL KARYAWAN DETAIL (SCREEN 5)                   --}}
        {{-- ========================================================================= --}}
        @if ($viewMode === 'user')
            <div class="space-y-6" x-data="{
                saveShift(day, dateStr, shiftId) {
                    fetch('{{ route('attendance.schedules.cell') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: {{ $targetUser->id }},
                            date: dateStr,
                            shift_id: shiftId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const dayFormatted = (day < 10 ? '0' + day : day);
                            triggerToast('Jadwal tgl ' + dayFormatted + ' diubah ke ' + data.shift.name + ' (' + data.shift.code + ')');
                            
                            // Sync desktop & mobile selects
                            const dSelect = document.getElementById('desktop-select-day-' + day);
                            if (dSelect && dSelect.value != shiftId) dSelect.value = shiftId;
                            const mSelect = document.getElementById('mobile-select-day-' + day);
                            if (mSelect && mSelect.value != shiftId) mSelect.value = shiftId;

                            // Update hours display
                            const hoursText = data.shift.is_day_off ? '-' : (data.shift.start_time.substring(0, 5) + ' – ' + data.shift.end_time.substring(0, 5));
                            const mobileHoursText = data.shift.is_day_off ? '— Libur' : (data.shift.start_time.substring(0, 5) + ' – ' + data.shift.end_time.substring(0, 5));
                            
                            const hoursEl = document.getElementById('hours-day-' + day);
                            if (hoursEl) hoursEl.textContent = hoursText;
                            const mHoursEl = document.getElementById('mobile-hours-day-' + day);
                            if (mHoursEl) mHoursEl.textContent = mobileHoursText;

                            // Update mobile badge
                            const mBadge = document.getElementById('mobile-badge-day-' + day);
                            if (mBadge) mBadge.textContent = data.shift.code;
                        } else {
                            alert(data.message || 'Gagal menyimpan perubahan shift');
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan saat menyimpan perubahan shift');
                    });
                }
            }">
                @if ($canManageSchedule)
                    <!-- Admin Navigation Switch -->
                    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
                        <a href="{{ route('attendance.schedules', ['view' => 'my']) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#155E63] hover:bg-teal-50 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Jadwal Saya</span>
                        </a>
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#155E63] text-white shadow-xs whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>📅 Kalender & Custom Jadwal</span>
                        </span>
                        <a href="{{ route('attendance.schedules', ['view' => 'list']) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#155E63] hover:bg-teal-50 transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Kelola Periode Bulan</span>
                        </a>
                    </div>
                @endif

                <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
                <div class="relative overflow-hidden rounded-3xl border border-cyan-400/30 shadow-[0_6px_24px_rgba(2,43,58,0.3)] p-4 sm:py-5 sm:px-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 select-none"
                     style="background: linear-gradient(110deg, #022836 0%, #053b47 28%, #0a525f 62%, #0e6774 86%, #084c57 100%);">
                    
                    <!-- Gloss Highlights (Top & Bottom Rims) -->
                    <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
                    <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

                    <!-- Left: Glowing Icon + User Info -->
                    <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-white/12 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="font-heading text-base sm:text-lg font-extrabold text-white tracking-tight leading-snug flex items-center gap-2">
                                <span>Custom Jadwal Kerja</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-200 border border-teal-300/30">Admin Mode</span>
                            </h1>
                            <p class="text-[11px] sm:text-xs text-teal-100/85 mt-0.5 leading-snug truncate">
                                {{ strtoupper($targetUser->name) }} &bull; {{ $targetUser->employee_code ?: 'KR'.$targetUser->id }} &bull; Divisi: {{ $targetUser->division ?: 'Operasional' }}
                            </p>
                        </div>
                    </div>

                    <!-- Right: Quick Selector Form (Employee, Month, Year) -->
                    <form method="GET" action="{{ route('attendance.schedules') }}" class="relative z-10 w-full md:w-auto flex flex-wrap items-center gap-2">
                        <input type="hidden" name="view" value="user">
                        
                        <!-- Employee Selector -->
                        @if (isset($activeUsers) && $activeUsers->isNotEmpty())
                            <div class="relative flex-1 sm:flex-initial min-w-[160px]">
                                <select name="user_id" onchange="this.form.submit()" class="w-full appearance-none bg-black/25 backdrop-blur-md border border-white/15 rounded-xl px-3 py-2 pr-7 text-xs font-semibold text-white focus:outline-none focus:ring-1 focus:ring-cyan-400 transition cursor-pointer">
                                    @foreach ($activeUsers as $u)
                                        <option value="{{ $u->id }}" class="bg-slate-800 text-white" {{ $targetUser->id == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->employee_code ?: 'KR'.$u->id }})
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="w-3 h-3 text-cyan-300 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        @endif

                        <!-- Month Selector -->
                        <div class="relative w-32 shrink-0">
                            <select name="month" onchange="this.form.submit()" class="w-full appearance-none bg-black/25 backdrop-blur-md border border-white/15 rounded-xl px-3 py-2 pr-7 text-xs font-semibold text-white focus:outline-none focus:ring-1 focus:ring-cyan-400 transition cursor-pointer">
                                @foreach ($monthNames as $num => $name)
                                    <option value="{{ $num }}" class="bg-slate-800 text-white" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <svg class="w-3 h-3 text-cyan-300 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <!-- Year Selector -->
                        <div class="relative w-24 shrink-0">
                            <select name="year" onchange="this.form.submit()" class="w-full appearance-none bg-black/25 backdrop-blur-md border border-white/15 rounded-xl px-3 py-2 pr-7 text-xs font-semibold text-white focus:outline-none focus:ring-1 focus:ring-cyan-400 transition cursor-pointer">
                                @for ($y = 2025; $y <= 2030; $y++)
                                    <option value="{{ $y }}" class="bg-slate-800 text-white" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <svg class="w-3 h-3 text-cyan-300 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <button type="submit" class="btn-primary !py-2 !px-3.5 text-xs font-bold shadow-md shadow-teal-900/30 inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Tampilkan</span>
                        </button>
                    </form>
                </div>

                <!-- Shift Legend Pills Bar -->
                <div class="rounded-2xl bg-white border border-slate-200/80 p-3.5 shadow-xs flex flex-wrap items-center gap-2.5">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Shift Tersedia:</span>
                    @foreach ($shifts as $s)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $s->color ?: '#3B82F6' }}"></span>
                            <span class="font-bold font-mono text-slate-800">{{ $s->code }}</span>
                            <span class="text-slate-500 font-medium">({{ $s->name }})</span>
                        </div>
                    @endforeach
                </div>

                @php
                    $dayNamesIndoShort = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
                    $dayNamesIndoFull = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $offShiftModel = $shifts->firstWhere('code', 'L') ?? $shifts->firstWhere('is_day_off', true);
                    $pagiShiftModel = $shifts->firstWhere('code', 'P');
                @endphp

                <!-- 1. MOBILE VIEW: Daily Touch-Friendly Cards (sm:hidden) -->
                <div class="sm:hidden space-y-3">
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $currentDate = \Carbon\Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta');
                            $isWeekend = $currentDate->isWeekend();
                            $holiday = $holidays->get($d);
                            $isDayOffByDefault = $isWeekend || $holiday !== null;

                            $schedule = $userSchedules->get($d);
                            $assignedShift = $schedule?->shift;

                            if (!$assignedShift) {
                                $assignedShift = $isDayOffByDefault ? $offShiftModel : $pagiShiftModel;
                            }

                            $isRowRed = $isDayOffByDefault || ($assignedShift && $assignedShift->is_day_off);
                            
                            $keteranganText = 'Hari Kerja Reguler';
                            if ($holiday) {
                                $keteranganText = 'Libur Nasional: ' . $holiday->name;
                            } elseif ($isWeekend) {
                                $keteranganText = 'Libur Akhir Pekan';
                            } elseif ($assignedShift && $assignedShift->is_piket) {
                                $keteranganText = 'Piket Operasional';
                            } elseif ($assignedShift && $assignedShift->is_day_off) {
                                $keteranganText = 'Libur';
                            }
                        @endphp
                        <div class="rounded-2xl border p-3.5 transition-all shadow-2xs {{ $isRowRed ? 'bg-rose-50/40 border-rose-200/80' : 'bg-white border-slate-200/80' }}"
                             id="mobile-card-day-{{ $d }}">
                            <!-- Top row: Date badge + Day Name + Keterangan Pill -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-10 h-10 rounded-xl flex flex-col items-center justify-center shrink-0 {{ $isRowRed ? 'bg-rose-100/90 text-rose-700 border border-rose-200' : 'bg-teal-50 text-[#155E63] border border-teal-200/70' }}">
                                        <span class="font-extrabold text-sm leading-none">{{ sprintf('%02d', $d) }}</span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5 {{ $isRowRed ? 'text-rose-500' : 'text-teal-600' }}">{{ $dayNamesIndoShort[$currentDate->dayOfWeek] }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold {{ $isRowRed ? 'text-rose-700' : 'text-slate-800' }}">
                                                {{ $dayNamesIndoFull[$currentDate->dayOfWeek] }}, {{ sprintf('%02d', $d) }} {{ substr($monthName, 0, 3) }}
                                            </span>
                                            @if ($isRowRed)
                                                <span class="text-amber-500 text-xs">⭐</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] font-medium {{ $isRowRed ? 'text-rose-600/90' : 'text-slate-500' }} block truncate" id="mobile-desc-day-{{ $d }}">
                                            {{ $keteranganText }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Shift Short Badge -->
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase shrink-0 {{ $isRowRed ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-sky-100 text-sky-700 border border-sky-200' }}" id="mobile-badge-day-{{ $d }}">
                                    {{ $assignedShift?->code ?? '-' }}
                                </span>
                            </div>

                            <!-- Middle: Shift Select Dropdown (Touch-friendly) -->
                            <div class="pt-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Shift Kerja:</label>
                                <div class="relative">
                                    <select id="mobile-select-day-{{ $d }}"
                                            @change="saveShift({{ $d }}, '{{ $currentDate->toDateString() }}', $event.target.value)"
                                            class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 py-2 pr-8 text-xs font-semibold text-slate-800 shadow-2xs focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] cursor-pointer">
                                        @foreach ($shifts as $s)
                                            <option value="{{ $s->id }}" {{ ($assignedShift && $assignedShift->id === $s->id) ? 'selected' : '' }}>
                                                {{ $s->name }} ({{ $s->code }}) {{ $s->is_day_off ? '— Libur' : '• ' . substr((string)$s->start_time, 0, 5) . '–' . substr((string)$s->end_time, 0, 5) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>

                            <!-- Bottom row: Working hours indicator -->
                            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100/80">
                                <span class="text-slate-400 text-[11px] font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Jam Kerja:</span>
                                </span>
                                <span class="font-mono font-bold text-slate-700 text-xs" id="mobile-hours-day-{{ $d }}">
                                    @if ($assignedShift && ! $assignedShift->is_day_off && $assignedShift->start_time && $assignedShift->end_time)
                                        {{ substr((string)$assignedShift->start_time, 0, 5) }} – {{ substr((string)$assignedShift->end_time, 0, 5) }}
                                    @else
                                        — Libur
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- 2. DESKTOP VIEW: Clean 5-Column Table (hidden sm:block) -->
                <div class="hidden sm:block rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="py-3 px-5 w-36">Tanggal</th>
                                    <th class="py-3 px-4 w-28">Hari</th>
                                    <th class="py-3 px-4 min-w-[200px]">Shift</th>
                                    <th class="py-3 px-4 w-36">Jam Kerja</th>
                                    <th class="py-3 px-5">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $currentDate = \Carbon\Carbon::createFromDate($year, $month, $d, 'Asia/Jakarta');
                                        $isWeekend = $currentDate->isWeekend();
                                        $holiday = $holidays->get($d);
                                        $isDayOffByDefault = $isWeekend || $holiday !== null;

                                        $schedule = $userSchedules->get($d);
                                        $assignedShift = $schedule?->shift;

                                        if (!$assignedShift) {
                                            $assignedShift = $isDayOffByDefault ? $offShiftModel : $pagiShiftModel;
                                        }

                                        $isRowRed = $isDayOffByDefault || ($assignedShift && $assignedShift->is_day_off);
                                    @endphp
                                    <tr class="transition-colors {{ $isRowRed ? 'bg-rose-50/40 hover:bg-rose-50/70' : 'hover:bg-slate-50/70' }}"
                                        id="desktop-row-day-{{ $d }}">
                                        <!-- Tanggal -->
                                        <td class="py-3 px-5 font-mono font-medium text-slate-700 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ sprintf('%02d', $d) }} {{ substr($monthName, 0, 3) }} {{ $year }}</span>
                                                @if ($isRowRed)
                                                    <span class="text-amber-500 text-xs" title="Hari Libur">⭐</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Hari -->
                                        <td class="py-3 px-4 font-semibold {{ $isWeekend ? 'text-rose-600' : 'text-slate-800' }}">
                                            {{ $dayNamesIndoFull[$currentDate->dayOfWeek] }}
                                        </td>

                                        <!-- Shift Dropdown (AJAX Auto Save) -->
                                        <td class="py-2.5 px-4">
                                            <div class="relative max-w-xs">
                                                <select id="desktop-select-day-{{ $d }}"
                                                        @change="saveShift({{ $d }}, '{{ $currentDate->toDateString() }}', $event.target.value)"
                                                        class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 py-1.5 pr-8 text-xs text-slate-800 font-semibold focus:outline-hidden focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] shadow-2xs cursor-pointer">
                                                    @foreach ($shifts as $s)
                                                        <option value="{{ $s->id }}" {{ ($assignedShift && $assignedShift->id === $s->id) ? 'selected' : '' }}>
                                                            {{ $s->name }} ({{ $s->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <svg class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            </div>
                                        </td>

                                        <!-- Jam Kerja -->
                                        <td class="py-3 px-4 font-mono text-slate-600" id="hours-day-{{ $d }}">
                                            @if ($assignedShift && ! $assignedShift->is_day_off && $assignedShift->start_time && $assignedShift->end_time)
                                                {{ substr((string)$assignedShift->start_time, 0, 5) }} – {{ substr((string)$assignedShift->end_time, 0, 5) }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- Keterangan -->
                                        <td class="py-3 px-5 text-xs">
                                            @if ($holiday)
                                                <span class="inline-flex items-center gap-1 font-semibold text-rose-700">
                                                    <span>Libur Nasional:</span>
                                                    <span>{{ $holiday->name }}</span>
                                                </span>
                                            @elseif ($isWeekend)
                                                <span class="text-rose-600 font-medium">Libur Akhir Pekan</span>
                                            @elseif ($assignedShift && $assignedShift->is_piket)
                                                <span class="text-amber-700 font-semibold">Piket Operasional</span>
                                            @elseif ($assignedShift && $assignedShift->is_day_off)
                                                <span class="text-rose-600 font-medium">Libur</span>
                                            @else
                                                <span class="text-slate-500">Hari Kerja Reguler</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layouts.app>
