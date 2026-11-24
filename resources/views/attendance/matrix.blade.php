<x-layouts.app title="Matriks Jadwal Kerja &amp; Piket (Excel View)">
    @php
        $initialCells = [];
        foreach ($users as $u) {
            $userScheds = $schedules->get($u->id) ?? collect();
            foreach ($daysData as $d) {
                $dt = $d['date'];
                $s = $userScheds->get($dt);
                $initialCells[$u->id . '_' . $dt] = $s?->shift?->code ?? '';
            }
        }
    @endphp

    <div x-data="{
        cells: {{ json_encode($initialCells) }},
        dirtyCells: {},
        activeStamp: null,
        isSaving: false,
        saveStatus: '',
        searchQuery: '',
        divisionFilter: '{{ $division ?? 'all' }}',
        popover: { open: false, userId: null, userName: '', date: '', dayName: '', x: 0, y: 0 },

        get dirtyCount() {
            return Object.keys(this.dirtyCells).length;
        },

        onCellClick(userId, userName, dateStr, dayName, event) {
            if (this.activeStamp !== null) {
                this.updateCell(userId, dateStr, this.activeStamp);
                return;
            }

            const rect = event.currentTarget.getBoundingClientRect();
            this.popover = {
                open: true,
                userId: userId,
                userName: userName,
                date: dateStr,
                dayName: dayName,
                x: Math.min(window.innerWidth - 220, Math.max(10, rect.left - 50)),
                y: rect.bottom + window.scrollY + 6
            };
        },

        selectFromPopover(shiftCode) {
            if (this.popover.userId && this.popover.date) {
                this.updateCell(this.popover.userId, this.popover.date, shiftCode);
            }
            this.popover.open = false;
        },

        updateCell(userId, dateStr, shiftCode) {
            const key = userId + '_' + dateStr;
            this.cells[key] = shiftCode;
            this.dirtyCells[key] = { user_id: userId, date: dateStr, shift_code: shiftCode };
            this.saveSingleCell(userId, dateStr, shiftCode);
        },

        saveSingleCell(userId, dateStr, shiftCode) {
            this.isSaving = true;
            this.saveStatus = 'Menyimpan...';
            fetch('{{ route('attendance.schedules.matrix.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    date: dateStr,
                    shift_code: shiftCode
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                delete this.dirtyCells[userId + '_' + dateStr];
                this.saveStatus = 'Tersimpan otomatis ✔';
                setTimeout(() => { if (this.saveStatus === 'Tersimpan otomatis ✔') this.saveStatus = ''; }, 2500);
            })
            .catch(() => {
                this.isSaving = false;
                this.saveStatus = 'Gagal menyimpan!';
            });
        },

        saveAllDirty() {
            const payload = Object.values(this.dirtyCells);
            if (payload.length === 0) return;
            this.isSaving = true;
            this.saveStatus = 'Menyimpan semua perubahan...';
            fetch('{{ route('attendance.schedules.matrix.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cells: payload })
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                this.dirtyCells = {};
                this.saveStatus = 'Semua perubahan berhasil disimpan ✔';
                setTimeout(() => { this.saveStatus = ''; }, 3000);
            })
            .catch(() => {
                this.isSaving = false;
                this.saveStatus = 'Gagal menyimpan perubahan!';
            });
        },

        stampWeekendVisible(shiftCode) {
            if (!confirm(`Terapkan shift '${shiftCode}' untuk semua hari Sabtu & Minggu karyawan yang tampil?`)) return;
            @foreach ($daysData as $d)
                @if ($d['isWeekend'])
                    document.querySelectorAll('[data-date=\"{{ $d['date'] }}\"]').forEach(el => {
                        const uid = el.getAttribute('data-user-id');
                        if (uid && el.closest('tr').style.display !== 'none') {
                            this.cells[uid + '_{{ $d['date'] }}'] = shiftCode;
                            this.dirtyCells[uid + '_{{ $d['date'] }}'] = {
                                user_id: parseInt(uid),
                                date: '{{ $d['date'] }}',
                                shift_code: shiftCode
                            };
                        }
                    });
                @endif
            @endforeach
            this.saveAllDirty();
        }
    }" class="space-y-4 pb-16">

        <!-- Top Header & Navigation -->
        <div class="rounded-3xl bg-gradient-to-r from-[#155E63] via-[#0f4d51] to-[#0c3c3f] p-5 sm:p-7 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <a href="{{ route('attendance.schedules', ['view' => 'month', 'year' => $year, 'month' => $month, 'division' => $division]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition border border-white/15">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Kembali ke Roster Shift</span>
                        </a>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400 text-teal-950">
                            Excel Spreadsheet View
                        </span>
                    </div>
                    <h1 class="font-heading font-black text-xl sm:text-2xl tracking-tight text-white flex items-center gap-2">
                        <span>📊 Matriks Jadwal Piket &amp; Shift</span>
                        <span class="text-teal-200 text-base font-normal">({{ $monthName }} {{ $year }})</span>
                    </h1>
                    <p class="text-xs text-teal-100/90 mt-1">
                        Input jadwal shift dan piket karyawan secara langsung per tanggal seperti di Excel. Perubahan disimpan otomatis.
                    </p>
                </div>

                <!-- Right Side Status & Save All Button -->
                <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-center">
                    <span x-show="saveStatus" x-text="saveStatus"
                          class="px-3 py-1.5 rounded-xl bg-white/20 backdrop-blur-md text-white text-xs font-bold border border-white/20 animate-pulse">
                    </span>

                    <button type="button" @click="saveAllDirty()"
                            x-show="dirtyCount > 0"
                            class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-lg shadow-emerald-950/30 flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Perubahan (<span x-text="dirtyCount"></span>)</span>
                    </button>

                    <a href="{{ route('attendance.schedules.export', ['year' => $year, 'month' => $month]) }}"
                       class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Download CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Quick Toolbar Card -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-xs space-y-4">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <!-- Periode & Division Form -->
                <form method="GET" action="{{ route('attendance.schedules.matrix') }}" class="flex flex-wrap items-center gap-2">
                    <select name="month" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white">
                        @foreach ([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ] as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white font-mono">
                        @for ($y = now('Asia/Jakarta')->year - 1; $y <= now('Asia/Jakarta')->year + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <select name="division" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-white">
                        <option value="all">Semua Divisi</option>
                        @foreach ($allDivisions as $d)
                            <option value="{{ $d }}" {{ $division === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#155E63] hover:bg-[#0c4349] text-white text-xs font-bold transition">
                        Terapkan
                    </button>
                </form>

                <!-- Search Input -->
                <div class="relative w-full sm:w-72">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama karyawan / kode..."
                           class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            <!-- Quick Brush Tool (Pilih Shift untuk klik-langsung di tabel) -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-bold text-slate-700 mr-1 flex items-center gap-1">
                        <span>🖌️ Kuas Cepat:</span>
                    </span>

                    <button type="button" @click="activeStamp = (activeStamp === null ? null : null)"
                            :class="activeStamp === null ? 'bg-slate-800 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer">
                        Mode Normal
                    </button>

                    @foreach ($shifts as $s)
                        @php
                            $code = strtoupper($s->code);
                            $badgeColor = match($code) {
                                'PK' => 'bg-teal-600 text-white border-teal-700',
                                'PA' => 'bg-indigo-600 text-white border-indigo-700',
                                'SB' => 'bg-blue-600 text-white border-blue-700',
                                'S'  => 'bg-amber-600 text-white border-amber-700',
                                'BJ' => 'bg-emerald-600 text-white border-emerald-700',
                                'P'  => 'bg-slate-700 text-white border-slate-800',
                                'L'  => 'bg-rose-600 text-white border-rose-700',
                                default => 'bg-slate-600 text-white'
                            };
                        @endphp
                        <button type="button" @click="activeStamp = (activeStamp === '{{ $code }}' ? null : '{{ $code }}')"
                                :class="activeStamp === '{{ $code }}' ? '{{ $badgeColor }} ring-2 ring-teal-400 font-bold scale-105 shadow-xs' : 'bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer flex items-center gap-1">
                            <span class="font-mono font-bold">{{ $code }}</span>
                            <span class="text-[10px] opacity-80 hidden sm:inline">({{ $s->name }})</span>
                        </button>
                    @endforeach

                    <button type="button" @click="activeStamp = (activeStamp === 'CLEAR' ? null : 'CLEAR')"
                            :class="activeStamp === 'CLEAR' ? 'bg-rose-600 text-white font-bold ring-2 ring-rose-400' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'"
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer">
                        🧹 Hapus
                    </button>
                </div>

                <!-- Fast Weekend Actions -->
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-slate-500 font-medium">Isi Weekend:</span>
                    <button type="button" @click="stampWeekendVisible('PK')"
                            class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-[#155E63] border border-teal-200 text-xs font-bold transition">
                        Semua PK
                    </button>
                    <button type="button" @click="stampWeekendVisible('PA')"
                            class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition">
                        Semua PA
                    </button>
                </div>
            </div>

            <!-- Active Stamp Guide Notification -->
            <div x-show="activeStamp !== null" x-cloak
                 class="p-2.5 rounded-xl bg-teal-50 border border-teal-200 text-xs text-[#155E63] font-medium flex items-center justify-between">
                <span>
                    💡 <b>Kuas Aktif: <span x-text="activeStamp === 'CLEAR' ? 'HAPUS / KOSONGKAN' : activeStamp"></span></b>. Klik sel tanggal pada tabel di bawah untuk langsung mengisinya.
                </span>
                <button type="button" @click="activeStamp = null" class="font-bold underline hover:text-teal-900">
                    Matikan Kuas
                </button>
            </div>
        </div>

        <!-- Spreadsheet Matrix Table (Excel Style) -->
        <div class="rounded-3xl bg-white border border-slate-300 shadow-md overflow-hidden">
            <div class="overflow-x-auto max-h-[72vh] relative">
                <table class="w-full text-xs border-collapse">
                    <!-- Excel Table Header 1: Tanggal Angka (1..31) -->
                    <thead class="sticky top-0 z-30 shadow-xs">
                        <tr class="bg-[#1e4e5f] text-white font-bold text-center border-b border-slate-400 text-xs">
                            <th class="py-2.5 px-2 w-10 min-w-[40px] sticky left-0 z-40 bg-[#1e4e5f] border-r border-slate-400 text-center">
                                No
                            </th>
                            <th class="py-2.5 px-3 w-48 min-w-[190px] text-left sticky left-10 z-40 bg-[#1e4e5f] border-r border-slate-400">
                                Nama Karyawan
                            </th>
                            <th class="py-2.5 px-2 w-24 min-w-[90px] text-center sticky left-[230px] z-40 bg-[#1e4e5f] border-r border-slate-400">
                                Kode
                            </th>
                            <th class="py-2.5 px-2 w-24 min-w-[95px] text-center sticky left-[320px] z-40 bg-[#1e4e5f] border-r-2 border-slate-500 shadow-r">
                                Divisi
                            </th>

                            <!-- Dates columns -->
                            @foreach ($daysData as $item)
                                @php
                                    $isWk = $item['isWeekend'];
                                    $isSun = $item['isSunday'];
                                    $colBg = $isSun ? 'bg-[#153e4c] text-rose-300' : ($isWk ? 'bg-[#174656] text-amber-300' : 'bg-[#1e4e5f] text-white');
                                @endphp
                                <th class="py-2 px-1 w-11 min-w-[44px] max-w-[44px] {{ $colBg }} border-r border-slate-400 text-center font-bold"
                                    title="{{ $item['dayName'] }}, {{ $item['date'] }}{{ $item['holiday'] ? ' - Libur: '.$item['holiday'] : '' }}">
                                    <span>{{ $item['day'] }}</span>
                                </th>
                            @endforeach
                        </tr>

                        <!-- Excel Table Header 2: Nama Hari (Kam, Jum, Sab, Min...) -->
                        <tr class="bg-[#245d71] text-white/95 text-[11px] font-semibold text-center border-b border-slate-300">
                            <th colspan="4" class="py-1 px-3 text-left sticky left-0 z-40 bg-[#245d71] border-r-2 border-slate-500 tracking-wider font-bold">
                                Hari
                            </th>

                            @foreach ($daysData as $item)
                                @php
                                    $isWk = $item['isWeekend'];
                                    $isSun = $item['isSunday'];
                                    $txtColor = $isSun ? 'text-rose-300 font-bold bg-[#1b4b5c]' : ($isWk ? 'text-amber-300 font-bold bg-[#1d5264]' : 'text-white/90 bg-[#245d71]');
                                @endphp
                                <th class="py-1 px-1 w-11 min-w-[44px] max-w-[44px] {{ $txtColor }} border-r border-slate-400 text-center text-[10px]">
                                    <span>{{ $item['dayName'] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <!-- Excel Table Body: Rows of Employees -->
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($users as $idx => $u)
                            @php
                                $uCode = $u->employee_code ?: ('KR'.$u->id);
                                $searchString = strtolower($u->name . ' ' . $uCode . ' ' . $u->division);
                            @endphp
                            <tr x-show="!searchQuery || '{{ addslashes($searchString) }}'.includes(searchQuery.toLowerCase())"
                                class="hover:bg-teal-50/30 transition-colors group">
                                
                                <!-- No -->
                                <td class="py-2 px-2 text-center font-mono text-slate-500 sticky left-0 z-20 bg-white group-hover:bg-teal-50/50 border-r border-slate-200 text-xs">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Nama Karyawan -->
                                <td class="py-2 px-3 sticky left-10 z-20 bg-white group-hover:bg-teal-50/50 border-r border-slate-200 font-bold text-slate-900 text-xs truncate max-w-[190px]"
                                    title="{{ $u->name }}">
                                    {{ $u->name }}
                                </td>

                                <!-- Kode Karyawan -->
                                <td class="py-2 px-2 text-center font-mono text-xs font-semibold text-rose-600 sticky left-[230px] z-20 bg-white group-hover:bg-teal-50/50 border-r border-slate-200">
                                    {{ $uCode }}
                                </td>

                                <!-- Divisi -->
                                <td class="py-2 px-2 text-center text-xs text-slate-600 sticky left-[320px] z-20 bg-white group-hover:bg-teal-50/50 border-r-2 border-slate-400 shadow-r">
                                    {{ $u->division ?: 'Umum' }}
                                </td>

                                <!-- Daily Matrix Cells -->
                                @foreach ($daysData as $item)
                                    @php
                                        $dStr = $item['date'];
                                        $cellKey = $u->id . '_' . $dStr;
                                        $isWk = $item['isWeekend'];
                                        $isSun = $item['isSunday'];
                                        $bgCell = $isSun ? 'bg-rose-50/40' : ($isWk ? 'bg-amber-50/30' : 'bg-white');
                                    @endphp
                                    <td class="p-0 border-r border-b border-slate-200 {{ $bgCell }} text-center relative h-10 w-11 min-w-[44px] max-w-[44px]">
                                        <button type="button"
                                                data-user-id="{{ $u->id }}"
                                                data-date="{{ $dStr }}"
                                                @click="onCellClick({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $dStr }}', '{{ $item['dayName'] }}', $event)"
                                                :class="{
                                                    'bg-teal-100 text-[#155E63] font-black border border-teal-300': cells['{{ $cellKey }}'] === 'PK',
                                                    'bg-indigo-100 text-indigo-800 font-black border border-indigo-300': cells['{{ $cellKey }}'] === 'PA',
                                                    'bg-blue-100 text-blue-800 font-bold border border-blue-200': cells['{{ $cellKey }}'] === 'SB',
                                                    'bg-amber-100 text-amber-800 font-bold border border-amber-200': cells['{{ $cellKey }}'] === 'S',
                                                    'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200': cells['{{ $cellKey }}'] === 'BJ',
                                                    'bg-slate-200 text-slate-800 font-bold': cells['{{ $cellKey }}'] === 'P',
                                                    'bg-rose-100/90 text-rose-700 font-bold border border-rose-200': cells['{{ $cellKey }}'] === 'L',
                                                    'hover:bg-teal-100/60': !cells['{{ $cellKey }}']
                                                }"
                                                class="w-full h-full flex items-center justify-center font-mono text-xs cursor-pointer transition select-none">
                                            <span x-text="cells['{{ $cellKey }}'] || ''"></span>
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($daysData) + 4 }}" class="py-12 text-center text-slate-400 italic">
                                    Tidak ada data karyawan pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Floating Popover Shift Picker (When click cell in normal mode) -->
        <div x-show="popover.open" x-cloak
             @click.away="popover.open = false"
             :style="`position: absolute; left: ${popover.x}px; top: ${popover.y}px; z-index: 60;`"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl p-3 w-56 space-y-2 text-xs">
            <div class="border-b border-slate-100 pb-1.5 flex items-center justify-between">
                <div>
                    <span class="font-bold text-slate-900 block truncate" x-text="popover.userName"></span>
                    <span class="text-[10px] text-slate-500 font-mono" x-text="popover.dayName + ', ' + popover.date"></span>
                </div>
                <button type="button" @click="popover.open = false" class="text-slate-400 hover:text-slate-700 font-bold text-base leading-none">×</button>
            </div>

            <div class="grid grid-cols-2 gap-1.5 pt-1">
                @foreach ($shifts as $s)
                    @php
                        $code = strtoupper($s->code);
                    @endphp
                    <button type="button" @click="selectFromPopover('{{ $code }}')"
                            class="px-2 py-1.5 rounded-xl border border-slate-200 hover:border-teal-500 hover:bg-teal-50 text-slate-800 text-left transition font-mono flex items-center justify-between">
                        <span class="font-bold">{{ $code }}</span>
                        <span class="text-[10px] text-slate-500 truncate ml-1">{{ $s->name }}</span>
                    </button>
                @endforeach

                <button type="button" @click="selectFromPopover('')"
                        class="col-span-2 px-2 py-1 rounded-xl text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 text-center font-semibold text-[11px] transition">
                    Kosongkan Sel (Hapus)
                </button>
            </div>
        </div>

        <!-- Bottom Legend Section (Exactly like user's screenshot) -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-900 text-xs sm:text-sm uppercase tracking-wider">KODE SHIFT:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs text-slate-700 font-medium">
                @foreach ($shifts as $s)
                    @php
                        $code = strtoupper($s->code);
                        $startTime = $s->start_time ? substr((string)$s->start_time, 0, 5) : '08:15';
                        $endTime = $s->end_time ? substr((string)$s->end_time, 0, 5) : '17:00';
                        $hoursLabel = $s->is_day_off ? 'Hari Libur' : "{$startTime} - {$endTime}";
                    @endphp
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs shrink-0
                            {{ in_array($code, ['PK', 'PA']) ? 'bg-teal-100 text-[#155E63] border border-teal-200' : 'bg-slate-200 text-slate-800' }}">
                            {{ $code }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-900 block">{{ $s->name }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $hoursLabel }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts.app>
