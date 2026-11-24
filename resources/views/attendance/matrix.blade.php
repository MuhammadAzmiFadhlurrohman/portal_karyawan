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
        $weekendDates = collect($daysData)->where('isWeekend', true)->pluck('date')->values();
    @endphp

    <div x-data="scheduleMatrix()" class="space-y-4 pb-16">

        <!-- Top Header & Navigation -->
        <div style="background: linear-gradient(135deg, #0b2d32 0%, #155E63 50%, #0e3f43 100%);"
             class="rounded-3xl p-5 sm:p-7 text-white shadow-xl border border-teal-800/40">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2.5 flex-wrap">
                        <a href="{{ route('attendance.schedules', ['view' => 'month', 'year' => $year, 'month' => $month, 'division' => $division]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition border border-white/20 backdrop-blur-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Kembali ke Roster Shift</span>
                        </a>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400 text-teal-950 shadow-xs">
                            Spreadsheet Matrix View
                        </span>
                    </div>
                    <h1 class="font-heading font-black text-xl sm:text-2xl tracking-tight text-white flex items-center gap-2">
                        <span>📊 Matriks Jadwal Shift &amp; Piket</span>
                        <span class="text-teal-200 text-base font-normal">({{ $monthName }} {{ $year }})</span>
                    </h1>
                    <p class="text-xs text-teal-100/90 mt-1 max-w-2xl">
                        Atur dan edit jadwal shift harian serta penugasan piket secara instan per tanggal seperti di Excel. Setiap perubahan tersimpan otomatis ke database.
                    </p>
                </div>

                <!-- Right Side Status & Save All Button -->
                <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-center">
                    <span x-show="saveStatus" x-text="saveStatus"
                          class="px-3.5 py-1.5 rounded-xl bg-white/20 backdrop-blur-md text-white text-xs font-bold border border-white/25 shadow-xs transition-all">
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
                       class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold flex items-center gap-1.5 transition backdrop-blur-xs">
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Download CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Quick Toolbar Card -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-xs space-y-4">
            
            <!-- ROW 1: Filters & Search -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <!-- Periode & Division Form -->
                <form method="GET" action="{{ route('attendance.schedules.matrix') }}" class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1.5 bg-slate-50 p-1 rounded-2xl border border-slate-200">
                        <select name="month" class="px-3 py-1.5 rounded-xl border-0 text-xs font-bold text-slate-800 bg-white shadow-2xs focus:ring-2 focus:ring-teal-500">
                            @foreach ([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $mNum => $mLabel)
                                <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>

                        <select name="year" class="px-2.5 py-1.5 rounded-xl border-0 text-xs font-bold text-slate-800 bg-white shadow-2xs font-mono focus:ring-2 focus:ring-teal-500">
                            @for ($y = now('Asia/Jakarta')->year - 1; $y <= now('Asia/Jakarta')->year + 2; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>

                        <select name="division" class="px-3 py-1.5 rounded-xl border-0 text-xs font-bold text-slate-800 bg-white shadow-2xs focus:ring-2 focus:ring-teal-500">
                            <option value="all">Semua Divisi</option>
                            @foreach ($allDivisions as $d)
                                <option value="{{ $d }}" {{ $division === $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#0c4349] text-white text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Terapkan</span>
                    </button>
                </form>

                <!-- Search Input -->
                <div class="relative w-full sm:w-80">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama karyawan / NIK..."
                           class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white shadow-2xs">
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                            class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">×</button>
                </div>
            </div>

            <!-- ROW 2: Kuas Cepat (Stamp Tool) & Weekend Actions Toolbar Ribbon -->
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col 2xl:flex-row 2xl:items-center justify-between gap-2.5 text-xs">
                
                <!-- Kuas Cepat Segmented Tools -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <!-- Title Pill -->
                    <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-teal-50 text-[#0d9488] font-bold text-xs shrink-0 border border-teal-100">
                        <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span>Kuas Cepat:</span>
                    </div>

                    <!-- Normal Pointer Button -->
                    <button type="button" @click="activeStamp = null"
                            :class="activeStamp === null 
                                ? 'bg-slate-800 text-white font-bold ring-2 ring-slate-800/30 shadow-xs' 
                                : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90 font-semibold shadow-2xs'"
                            class="px-2.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                        </svg>
                        <span>Pointer Normal</span>
                    </button>

                    <div class="h-5 w-px bg-slate-200 mx-0.5 hidden sm:block shrink-0"></div>

                    <!-- Shift Stamp Buttons -->
                    @foreach ($shifts as $s)
                        @php
                            $code = strtoupper($s->code);
                            $btnStyle = match($code) {
                                'PK' => ['bg' => 'background-color: #0d9488; color: #ffffff;', 'badge' => 'Piket Pagi', 'short' => 'Piket Pagi', 'pill' => 'bg-teal-50 text-teal-800 border-teal-200'],
                                'PA' => ['bg' => 'background-color: #4f46e5; color: #ffffff;', 'badge' => 'Piket Siang', 'short' => 'Piket Siang', 'pill' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                                'SB' => ['bg' => 'background-color: #0284c7; color: #ffffff;', 'badge' => 'Subuh', 'short' => 'Subuh', 'pill' => 'bg-sky-50 text-sky-800 border-sky-200'],
                                'S'  => ['bg' => 'background-color: #d97706; color: #ffffff;', 'badge' => 'Siang', 'short' => 'Siang', 'pill' => 'bg-amber-50 text-amber-800 border-amber-200'],
                                'BJ' => ['bg' => 'background-color: #059669; color: #ffffff;', 'badge' => 'Visit Soreang', 'short' => 'Soreang', 'pill' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                'P'  => ['bg' => 'background-color: #475569; color: #ffffff;', 'badge' => 'Pagi Reguler', 'short' => 'Pagi', 'pill' => 'bg-slate-100 text-slate-700 border-slate-300'],
                                'L'  => ['bg' => 'background-color: #e11d48; color: #ffffff;', 'badge' => 'Libur', 'short' => 'Libur', 'pill' => 'bg-rose-50 text-rose-800 border-rose-200'],
                                default => ['bg' => 'background-color: #7c3aed; color: #ffffff;', 'badge' => $s->name, 'short' => 'Custom', 'pill' => 'bg-purple-50 text-purple-800 border-purple-200']
                            };
                        @endphp
                        <button type="button" @click="activeStamp = (activeStamp === '{{ $code }}' ? null : '{{ $code }}')"
                                :style="activeStamp === '{{ $code }}' ? '{{ $btnStyle['bg'] }}' : ''"
                                :class="activeStamp === '{{ $code }}' 
                                    ? 'ring-2 ring-teal-400 font-bold shadow-xs scale-105' 
                                    : 'bg-white text-slate-700 border border-slate-200/90 hover:border-slate-300 hover:bg-slate-50 shadow-2xs font-semibold'"
                                title="{{ $s->name }} ({{ $code }})"
                                class="px-2 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                            <span class="font-mono font-black px-1.5 py-0.5 rounded-md text-[10px] border"
                                  :class="activeStamp === '{{ $code }}' ? 'bg-white/20 text-white border-white/30' : '{{ $btnStyle['pill'] }}'">
                                {{ $code }}
                            </span>
                            <span class="text-xs font-semibold opacity-90">{{ $btnStyle['short'] }}</span>
                        </button>
                    @endforeach

                    <div class="h-5 w-px bg-slate-200 mx-0.5 hidden sm:block shrink-0"></div>

                    <!-- Clear / Hapus Button -->
                    <button type="button" @click="activeStamp = (activeStamp === 'CLEAR' ? null : 'CLEAR')"
                            :class="activeStamp === 'CLEAR' ? 'bg-rose-600 text-white font-bold ring-2 ring-rose-400 shadow-xs' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Hapus</span>
                    </button>
                </div>

                <!-- Fast Weekend Actions in Distinct Pill -->
                <div class="flex items-center gap-1.5 bg-slate-50/90 border border-slate-200/80 p-1 rounded-2xl shadow-2xs self-start 2xl:self-center shrink-0">
                    <span class="text-[11px] text-slate-600 font-bold px-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                        <span>Isi Weekend:</span>
                    </span>
                    <button type="button" @click="stampWeekendVisible('PK')"
                            class="px-2.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#0d9488] border border-teal-200 text-xs font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                        <span>Semua PK</span>
                    </button>
                    <button type="button" @click="stampWeekendVisible('PA')"
                            class="px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                        <span>Semua PA</span>
                    </button>
                </div>
            </div>

            <!-- Active Stamp Guide Notification Banner -->
            <div x-show="activeStamp !== null" x-cloak
                 class="p-3 rounded-2xl bg-teal-500/10 border border-teal-300 text-xs text-[#155E63] font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-[#155E63] text-white flex items-center justify-center font-bold text-xs">🖌️</span>
                    <span>
                        <b>Kuas Aktif: <span class="font-mono font-black px-1.5 py-0.5 bg-white rounded border border-teal-200" x-text="activeStamp === 'CLEAR' ? 'HAPUS' : activeStamp"></span></b>.
                        Klik sel tanggal pada tabel di bawah untuk langsung mengganti shift karyawan.
                    </span>
                </div>
                <button type="button" @click="activeStamp = null"
                        class="px-2.5 py-1 rounded-lg bg-white border border-teal-200 hover:bg-teal-50 text-teal-900 font-bold text-xs transition cursor-pointer">
                    ✕ Matikan Kuas
                </button>
            </div>
        </div>

        <!-- Spreadsheet Matrix Table (Excel Style) -->
        <div class="rounded-3xl bg-white border border-slate-300 shadow-md overflow-hidden">
            <div class="overflow-x-auto max-h-[72vh] relative">
                <table class="w-full text-xs border-collapse">
                    
                    <!-- Excel Table Header 1: Tanggal Angka (1..31) -->
                    <thead class="sticky top-0 z-30 shadow-xs">
                        <tr style="background-color: #134e4a; color: #ffffff;" class="font-bold text-center border-b border-teal-900 text-xs">
                            <th style="position: sticky; left: 0; z-index: 50; width: 44px; min-width: 44px; background-color: #134e4a; color: #ffffff;"
                                class="py-2.5 px-2 text-center border-r border-teal-800">
                                No
                            </th>
                            <th style="position: sticky; left: 44px; z-index: 50; width: 180px; min-width: 180px; background-color: #134e4a; color: #ffffff;"
                                class="py-2.5 px-3 text-left border-r border-teal-800">
                                Nama Karyawan
                            </th>
                            <th style="position: sticky; left: 224px; z-index: 50; width: 80px; min-width: 80px; background-color: #134e4a; color: #ffffff;"
                                class="py-2.5 px-2 text-center border-r border-teal-800">
                                Kode
                            </th>
                            <th style="position: sticky; left: 304px; z-index: 50; width: 90px; min-width: 90px; background-color: #134e4a; color: #ffffff; box-shadow: 4px 0 8px -2px rgba(0,0,0,0.15);"
                                class="py-2.5 px-2 text-center border-r-2 border-teal-900">
                                Divisi
                            </th>

                            <!-- Dates columns (1..30/31) -->
                            @foreach ($daysData as $item)
                                @php
                                    $isWk = $item['isWeekend'];
                                    $isSun = $item['isSunday'];
                                    $headerThStyle = $isSun 
                                        ? 'background-color: #be123c; color: #ffffff;' 
                                        : ($isWk ? 'background-color: #b45309; color: #ffffff;' : 'background-color: #134e4a; color: #ffffff;');
                                @endphp
                                <th style="{{ $headerThStyle }}"
                                    class="py-2 px-1 w-11 min-w-[44px] max-w-[44px] border-r border-teal-800 text-center font-black text-xs"
                                    title="{{ $item['dayName'] }}, {{ $item['date'] }}{{ $item['holiday'] ? ' - Libur: '.$item['holiday'] : '' }}">
                                    <span>{{ $item['day'] }}</span>
                                </th>
                            @endforeach
                        </tr>

                        <!-- Excel Table Header 2: Nama Hari (Kam, Jum, Sab, Min...) -->
                        <tr style="background-color: #0f3d3a; color: #cbd5e1;" class="text-[11px] font-semibold text-center border-b border-slate-300">
                            <th colspan="4"
                                style="position: sticky; left: 0; z-index: 50; background-color: #0f3d3a; color: #ffffff; box-shadow: 4px 0 8px -2px rgba(0,0,0,0.15);"
                                class="py-1 px-3 text-left border-r-2 border-teal-900 tracking-wider font-bold text-[10px] uppercase">
                                Hari
                            </th>

                            @foreach ($daysData as $item)
                                @php
                                    $isWk = $item['isWeekend'];
                                    $isSun = $item['isSunday'];
                                    $dayThStyle = $isSun 
                                        ? 'background-color: #9f1239; color: #ffe4e6;' 
                                        : ($isWk ? 'background-color: #92400e; color: #fef3c7;' : 'background-color: #0f3d3a; color: #99f6e4;');
                                @endphp
                                <th style="{{ $dayThStyle }}"
                                    class="py-1 px-1 w-11 min-w-[44px] max-w-[44px] border-r border-teal-800 text-center text-[10px] font-bold uppercase">
                                    <span>{{ substr($item['dayName'], 0, 3) }}</span>
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
                                $initials = strtoupper(substr($u->name, 0, 2));
                            @endphp
                            <tr x-show="!searchQuery || '{{ addslashes($searchString) }}'.includes(searchQuery.toLowerCase())"
                                class="hover:bg-teal-50/40 transition-colors group">
                                
                                <!-- No (Freeze column) -->
                                <td style="position: sticky; left: 0; z-index: 20; width: 44px; min-width: 44px; background-color: #ffffff;"
                                    class="py-2 px-2 text-center font-mono text-slate-400 group-hover:bg-teal-50/60 border-r border-slate-200 text-xs font-semibold">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Nama Karyawan (Freeze column) -->
                                <td style="position: sticky; left: 44px; z-index: 20; width: 180px; min-width: 180px; background-color: #ffffff;"
                                    class="py-2 px-3 group-hover:bg-teal-50/60 border-r border-slate-200 text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-6 h-6 rounded-full bg-teal-50 border border-teal-200 text-[#155E63] font-bold text-[10px] flex items-center justify-center shrink-0">
                                            <span>{{ $initials }}</span>
                                        </div>
                                        <span class="font-bold text-slate-900 truncate" title="{{ $u->name }}">{{ $u->name }}</span>
                                    </div>
                                </td>

                                <!-- Kode Karyawan (Freeze column) -->
                                <td style="position: sticky; left: 224px; z-index: 20; width: 80px; min-width: 80px; background-color: #ffffff;"
                                    class="py-2 px-2 text-center group-hover:bg-teal-50/60 border-r border-slate-200">
                                    <span class="font-mono text-[11px] font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/80">
                                        {{ $uCode }}
                                    </span>
                                </td>

                                <!-- Divisi (Freeze column) -->
                                <td style="position: sticky; left: 304px; z-index: 20; width: 90px; min-width: 90px; background-color: #ffffff; box-shadow: 4px 0 8px -2px rgba(0,0,0,0.08);"
                                    class="py-2 px-2 text-center group-hover:bg-teal-50/60 border-r-2 border-slate-300">
                                    <span class="text-[11px] font-medium text-slate-600 truncate block max-w-[85px]">
                                        {{ $u->division ?: 'Umum' }}
                                    </span>
                                </td>

                                <!-- Daily Matrix Cells -->
                                @foreach ($daysData as $item)
                                    @php
                                        $dStr = $item['date'];
                                        $cellKey = $u->id . '_' . $dStr;
                                        $isWk = $item['isWeekend'];
                                        $isSun = $item['isSunday'];
                                        $cellBgStyle = $isSun ? 'background-color: #fff1f2;' : ($isWk ? 'background-color: #fffbeb;' : 'background-color: #ffffff;');
                                    @endphp
                                    <td style="{{ $cellBgStyle }}"
                                        class="p-0 border-r border-b border-slate-200 text-center relative h-10 w-11 min-w-[44px] max-w-[44px]">
                                        <button type="button"
                                                data-user-id="{{ $u->id }}"
                                                data-date="{{ $dStr }}"
                                                @click="onCellClick({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $dStr }}', '{{ $item['dayName'] }}', $event)"
                                                :class="{
                                                    'bg-teal-500 text-white font-black shadow-xs ring-1 ring-teal-600': cells['{{ $cellKey }}'] === 'PK',
                                                    'bg-indigo-600 text-white font-black shadow-xs ring-1 ring-indigo-700': cells['{{ $cellKey }}'] === 'PA',
                                                    'bg-sky-100 text-sky-900 font-black border border-sky-300': cells['{{ $cellKey }}'] === 'SB',
                                                    'bg-amber-100 text-amber-900 font-black border border-amber-300': cells['{{ $cellKey }}'] === 'S',
                                                    'bg-emerald-100 text-emerald-900 font-black border border-emerald-300': cells['{{ $cellKey }}'] === 'BJ',
                                                    'bg-slate-100 text-slate-800 font-bold border border-slate-200': cells['{{ $cellKey }}'] === 'P',
                                                    'bg-rose-100 text-rose-800 font-black border border-rose-200': cells['{{ $cellKey }}'] === 'L',
                                                    'hover:bg-teal-100/70': !cells['{{ $cellKey }}']
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
             :style="'position: absolute; left: ' + popover.x + 'px; top: ' + popover.y + 'px; z-index: 60;'"
             class="bg-white rounded-3xl border border-slate-200/90 shadow-2xl p-4 w-80 space-y-3 text-xs animate-in fade-in zoom-in-95 duration-150">
            <!-- Popover Header -->
            <div class="border-b border-slate-100 pb-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-200/80 text-[#0d9488] font-bold text-xs flex items-center justify-center shrink-0 uppercase shadow-2xs">
                        <span x-text="popover.userName ? popover.userName.substring(0, 2) : 'KR'"></span>
                    </div>
                    <div class="min-w-0">
                        <span class="font-bold text-slate-900 block truncate text-xs" x-text="popover.userName"></span>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="font-medium font-mono text-[10.5px]" x-text="popover.dayName + ', ' + popover.date"></span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="popover.open = false" 
                        class="w-7 h-7 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Shift Options Grid -->
            <div class="grid grid-cols-2 gap-2">
                @foreach ($shifts as $s)
                    @php
                        $code = strtoupper($s->code);
                        $shiftMeta = match($code) {
                            'PK' => ['name' => 'Piket Pagi', 'hours' => '08:15–17:00', 'bg' => '#0d9488'],
                            'PA' => ['name' => 'Piket Siang', 'hours' => '13:00–21:00', 'bg' => '#4f46e5'],
                            'SB' => ['name' => 'Subuh', 'hours' => '06:00–15:00', 'bg' => '#0284c7'],
                            'S'  => ['name' => 'Siang', 'hours' => '13:00–21:00', 'bg' => '#d97706'],
                            'BJ' => ['name' => 'Soreang', 'hours' => '08:00–17:00', 'bg' => '#059669'],
                            'P'  => ['name' => 'Pagi', 'hours' => '08:15–17:00', 'bg' => '#475569'],
                            'L'  => ['name' => 'Libur', 'hours' => 'Off / Libur', 'bg' => '#e11d48'],
                            default => ['name' => 'Custom', 'hours' => 'Custom Jam', 'bg' => '#7c3aed']
                        };
                    @endphp
                    <button type="button" @click="selectFromPopover('{{ $code }}')"
                            :class="popover.currentCode === '{{ $code }}' 
                                ? 'ring-2 ring-teal-500 bg-teal-50/60 border-teal-300 shadow-xs' 
                                : 'bg-white hover:bg-slate-50 border-slate-200/90 hover:border-slate-300 shadow-2xs'"
                            class="p-2 rounded-xl border text-left transition flex items-center gap-2 cursor-pointer group">
                        <span class="w-7 h-7 rounded-lg font-mono font-black text-xs flex items-center justify-center shrink-0 text-white shadow-2xs"
                              style="background-color: {{ $shiftMeta['bg'] }};">
                            {{ $code }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 block truncate text-xs group-hover:text-teal-900 leading-tight">
                                    {{ $shiftMeta['name'] }}
                                </span>
                                <svg x-show="popover.currentCode === '{{ $code }}'" class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-[10px] text-slate-400 block font-mono leading-tight mt-0.5">
                                {{ $shiftMeta['hours'] }}
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>

            <!-- Clear Cell Action Button -->
            <button type="button" @click="selectFromPopover('')"
                    class="w-full py-2 rounded-xl text-rose-600 hover:text-rose-700 bg-rose-50/80 hover:bg-rose-100/90 border border-rose-200 text-center font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Kosongkan Sel (Hapus Shift)</span>
            </button>
        </div>

        <!-- Bottom Legend Section (Enterprise SaaS Style) -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-3.5">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    <h3 class="font-bold text-slate-900 text-xs sm:text-sm uppercase tracking-wider">LEGENDA KODE SHIFT &amp; JAM KERJA</h3>
                </div>
                <span class="text-[11px] text-slate-400">Gunakan kuas atau klik sel langsung untuk mengubah jadwal</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs text-slate-700 font-medium">
                @foreach ($shifts as $s)
                    @php
                        $code = strtoupper($s->code);
                        $startTime = $s->start_time ? substr((string)$s->start_time, 0, 5) : '08:15';
                        $endTime = $s->end_time ? substr((string)$s->end_time, 0, 5) : '17:00';
                        $hoursLabel = $s->is_day_off ? 'Hari Libur' : "{$startTime} - {$endTime}";
                        $legendBadgeStyle = match($code) {
                            'PK' => 'background-color: #0d9488; color: #ffffff;',
                            'PA' => 'background-color: #4f46e5; color: #ffffff;',
                            'SB' => 'background-color: #0284c7; color: #ffffff;',
                            'S'  => 'background-color: #d97706; color: #ffffff;',
                            'BJ' => 'background-color: #059669; color: #ffffff;',
                            'P'  => 'background-color: #475569; color: #ffffff;',
                            'L'  => 'background-color: #e11d48; color: #ffffff;',
                            default => 'background-color: #64748b; color: #ffffff;'
                        };
                    @endphp
                    <div class="flex items-center gap-2.5 p-2.5 rounded-2xl bg-slate-50 border border-slate-200/70 hover:bg-slate-100/70 transition">
                        <span style="{{ $legendBadgeStyle }}"
                              class="w-8 h-8 rounded-xl flex items-center justify-center font-mono font-black text-xs shrink-0 shadow-2xs">
                            {{ $code }}
                        </span>
                        <div class="min-w-0">
                            <span class="font-bold text-slate-900 block truncate text-xs">{{ $s->name }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $hoursLabel }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Script Component Function -->
    <script>
        function scheduleMatrix() {
            return {
                cells: {!! json_encode($initialCells) !!},
                weekendDates: {!! json_encode($weekendDates) !!},
                dirtyCells: {},
                activeStamp: null,
                isSaving: false,
                saveStatus: '',
                searchQuery: '',
                popover: { open: false, userId: null, userName: '', date: '', dayName: '', currentCode: '', x: 0, y: 0 },

                get dirtyCount() {
                    return Object.keys(this.dirtyCells).length;
                },

                onCellClick(userId, userName, dateStr, dayName, event) {
                    if (this.activeStamp !== null) {
                        this.updateCell(userId, dateStr, this.activeStamp);
                        return;
                    }

                    const rect = event.currentTarget.getBoundingClientRect();
                    const currentShift = this.cells[userId + '_' + dateStr] || '';
                    this.popover = {
                        open: true,
                        userId: userId,
                        userName: userName,
                        date: dateStr,
                        dayName: dayName,
                        currentCode: currentShift,
                        x: Math.min(window.innerWidth - 340, Math.max(10, rect.left - 40)),
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
                    if (!confirm("Terapkan shift '" + shiftCode + "' untuk semua hari Sabtu & Minggu karyawan yang tampil?")) return;
                    this.weekendDates.forEach(dStr => {
                        document.querySelectorAll('[data-date="' + dStr + '"]').forEach(el => {
                            const uid = el.getAttribute('data-user-id');
                            const row = el.closest('tr');
                            if (uid && row && row.style.display !== 'none') {
                                this.cells[uid + '_' + dStr] = shiftCode;
                                this.dirtyCells[uid + '_' + dStr] = {
                                    user_id: parseInt(uid),
                                    date: dStr,
                                    shift_code: shiftCode
                                };
                            }
                        });
                    });
                    this.saveAllDirty();
                }
            };
        }
    </script>
</x-layouts.app>
