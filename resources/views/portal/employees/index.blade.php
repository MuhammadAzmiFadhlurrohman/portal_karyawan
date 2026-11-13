<x-layouts.app title="Data Karyawan — Manajemen SDM">
    <div x-data="{
        isCreateOpen: false,
        isEditOpen: false,
        isDeleteOpen: false,
        isResetOpen: false,
        isResetFaceOpen: false,
        selectedEmployee: null,
        editForm: {
            id: '',
            name: '',
            employee_code: '',
            username: '',
            email: '',
            phone: '',
            nik: '',
            division: '',
            position: '',
            supervisor_id: '',
            role: '',
            join_date: '',
            bank: '',
            no_rekening: '',
            atas_nama: '',
            potongan_alpa: '',
            is_active: true
        },
        deleteTarget: { id: '', name: '', code: '' },
        resetTarget: { id: '', name: '', code: '' },
        resetFaceTarget: { id: '', name: '', code: '', hasFace: false },
        openEdit(emp) {
            this.selectedEmployee = emp;
            this.editForm = {
                id: emp.id,
                name: emp.name || '',
                employee_code: emp.employee_code || '',
                username: emp.username || '',
                email: emp.email || '',
                phone: emp.phone || '',
                nik: emp.nik || '',
                division: emp.division || '',
                position: emp.position || '',
                supervisor_id: emp.supervisor_id || '',
                role: (emp.roles && emp.roles.length > 0) ? emp.roles[0].name : 'karyawan',
                join_date: emp.join_date ? emp.join_date.substring(0, 10) : '',
                bank: emp.bank || '',
                no_rekening: emp.no_rekening || '',
                atas_nama: emp.atas_nama || '',
                potongan_alpa: emp.potongan_alpa || '',
                is_active: Boolean(emp.is_active)
            };
            this.isEditOpen = true;
        },
        openDelete(emp) {
            this.deleteTarget = { id: emp.id, name: emp.name, code: emp.employee_code };
            this.isDeleteOpen = true;
        },
        openReset(emp) {
            this.resetTarget = { id: emp.id, name: emp.name, code: emp.employee_code };
            this.isResetOpen = true;
        },
        openResetFace(emp) {
            this.resetFaceTarget = {
                id: emp.id,
                name: emp.name,
                code: emp.employee_code,
                hasFace: Boolean(emp.face_template)
            };
            this.isResetFaceOpen = true;
        }
    }" class="space-y-6 w-full pb-16">

        <!-- Top Welcome/Header Card (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Fluid Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#empWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="empWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Icon + Title & Subtitle -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                            <span>Manajemen Data Karyawan</span>
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                            {{ $totalEmployees }} Total Personel
                        </span>
                    </div>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-1 leading-snug">
                        Kelola data kepegawaian, jabatan, divisi, atasan langsung, role RBAC, dan rekening payroll PT MSN.
                    </p>
                </div>
            </div>

            <!-- Right: Action Button (+ Tambah Karyawan - Hanya Admin) -->
            @if ($canWrite ?? true)
            <div class="relative z-10 flex items-center gap-2 shrink-0">
                <button @click="isCreateOpen = true"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-400 to-[#18C4C4] hover:from-teal-300 hover:to-[#22d3d3] text-slate-900 font-bold text-xs shadow-lg hover:shadow-cyan-500/30 transition-all active:scale-95 cursor-pointer border border-white/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Karyawan</span>
                </button>
            </div>
            @endif
        </div>

        <!-- Feedback Alert Messages -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-base font-bold">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-base font-bold">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-xs">
                <p class="font-bold flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Terdapat beberapa kesalahan pengisian form:
                </p>
                <ul class="list-disc list-inside space-y-0.5 ml-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI Metric Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Karyawan</span>
                    <span class="text-base">👥</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="font-heading text-2xl font-bold text-slate-800">{{ $totalEmployees }}</span>
                    <span class="text-xs text-slate-400">Terdaftar</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Database aktif PT MSN
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Karyawan Aktif</span>
                    <span class="text-base">🟢</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="font-heading text-2xl font-bold text-emerald-700">{{ $activeEmployees }}</span>
                    <span class="text-xs text-emerald-700 font-medium">Bisa Login</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Akses sistem aktif
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Karyawan Nonaktif</span>
                    <span class="text-base">⏸️</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="font-heading text-2xl font-bold text-rose-700">{{ $inactiveEmployees }}</span>
                    <span class="text-xs text-rose-700 font-medium">Terkunci</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Non-aktif / resigned
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi Teknik</span>
                    <span class="text-base">🛠️</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="font-heading text-2xl font-bold text-teal-700">{{ $divisionCounts['Teknik'] ?? 0 }}</span>
                    <span class="text-xs text-slate-400">Personel</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    NOC: {{ $divisionCounts['NOC'] ?? 0 }} · Umum: {{ $divisionCounts['Umum'] ?? 0 }}
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('portal.employees.index') }}" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5 sm:gap-3 items-center">
                <!-- Search Input -->
                <div class="col-span-2 sm:col-span-3 relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $currentFilters['search'] }}" 
                           placeholder="Cari nama, NIK, kode..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-slate-50/50">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Division Select -->
                <div class="col-span-1 sm:col-span-2">
                    <select name="division" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-slate-50/50">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $currentFilters['division'] === $div ? 'selected' : '' }}>
                                {{ $div }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Month Select (For Attendance Stats) -->
                <div class="col-span-1 sm:col-span-2">
                    <select name="month" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-slate-50/50 font-semibold text-slate-700">
                        @for ($m = 1; $m <= 12; $m++)
                            @php $d = \Carbon\Carbon::create(null, $m, 1); @endphp
                            <option value="{{ $m }}" @selected($m === $month)>{{ $d->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Year Select (For Attendance Stats) -->
                <div class="col-span-1 sm:col-span-2">
                    <select name="year" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-slate-50/50 font-mono font-semibold text-slate-700">
                        @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Status Select -->
                <div class="col-span-1 sm:col-span-2">
                    <select name="status" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-slate-50/50">
                        <option value="active" {{ $currentFilters['status'] === 'active' ? 'selected' : '' }}>Status: Aktif</option>
                        <option value="inactive" {{ $currentFilters['status'] === 'inactive' ? 'selected' : '' }}>Status: Nonaktif</option>
                        <option value="all" {{ $currentFilters['status'] === 'all' ? 'selected' : '' }}>Semua Status</option>
                    </select>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="col-span-2 sm:col-span-1 flex items-center gap-1.5">
                    <button type="submit" 
                            title="Terapkan Filter"
                            class="flex-1 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs flex items-center justify-center shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>
                    @if ($currentFilters['search'] || $currentFilters['division'] || $currentFilters['role'] || $currentFilters['status'] !== 'active' || $month !== now('Asia/Jakarta')->month || $year !== now('Asia/Jakarta')->year)
                        <a href="{{ route('portal.employees.index') }}" 
                           title="Reset Filter"
                           class="p-2 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-100 text-xs transition">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Mobile Card View (Matches Reference Design) -->
        <div class="md:hidden space-y-3">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                    <span class="text-[11px] font-bold text-slate-800 uppercase tracking-tight">
                        DAFTAR KARYAWAN ({{ strtoupper(\Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y')) }})
                    </span>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">{{ $employees->total() }} Karyawan Terdata</span>
            </div>

            @forelse ($employees as $emp)
                @php
                    $mStats = $attendanceStats[$emp->id] ?? [
                        'total_hadir' => 0,
                        'tepat_waktu' => 0,
                        'terlambat' => 0,
                        'alpa' => 0,
                        'cuti' => 0,
                        'jam_kerja' => 0,
                    ];
                @endphp
                <div class="bg-white rounded-2xl border-2 border-teal-500/50 p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-xs shrink-0">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span class="font-bold text-xs sm:text-sm text-slate-900 tracking-tight truncate">{{ strtoupper($emp->name) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if ($emp->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Periode</span>
                            </span>
                            <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Total Kehadiran</span>
                            </span>
                            <span class="font-bold text-slate-900">
                                {{ $mStats['total_hadir'] }} hari
                                <span class="text-[10px] font-normal text-slate-500">(Tepat: {{ $mStats['tepat_waktu'] }}, Telat: {{ $mStats['terlambat'] }})</span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Divisi</span>
                            </span>
                            <span class="font-semibold text-slate-800">{{ $emp->division ?: '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Lokasi</span>
                            </span>
                            <span class="font-semibold text-slate-800">Bandung</span>
                        </div>
                    </div>

                    <!-- Full-Width "Lihat Detail" Button matching User Reference -->
                    <a href="{{ route('attendance.history', ['user_id' => $emp->id, 'month' => $month, 'year' => $year]) }}"
                       class="w-full py-2.5 px-4 rounded-xl border border-slate-300 bg-white hover:bg-teal-50 hover:border-teal-400 hover:text-teal-800 text-slate-800 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Lihat Detail</span>
                    </a>

                    <!-- Compact Admin Action Tools (Hanya Admin) -->
                    @if ($canWrite ?? true)
                    <div class="flex items-center justify-end gap-1 pt-1 border-t border-slate-100">
                        <button @click="openEdit({{ $emp->toJson() }})"
                                type="button" 
                                title="Ubah Data Karyawan"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-teal-50 hover:text-teal-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button @click="openResetFace({{ $emp->toJson() }})"
                                type="button" 
                                title="Reset Wajah"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m-4 12h2a2 2 0 002-2v-2M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0"/></svg>
                        </button>
                        <button @click="openReset({{ $emp->toJson() }})"
                                type="button" 
                                title="Reset Password"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-amber-50 hover:text-amber-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </button>
                        @if ($emp->id !== auth()->id())
                            <button @click="openDelete({{ $emp->toJson() }})"
                                    type="button" 
                                    title="Hapus / Nonaktifkan"
                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-700 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center text-slate-400">
                    <span class="text-3xl block mb-2">🔍</span>
                    <p class="font-bold text-slate-700 text-sm">Tidak ada karyawan yang sesuai filter</p>
                </div>
            @endforelse
        </div>

        <!-- Employee Table Card (Desktop View) -->
        <div class="hidden md:block bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div>
                <table class="w-full text-left text-xs table-fixed">
                    <thead>
                        <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
                            <th class="py-3 px-3.5 w-[22%]">Karyawan</th>
                            <th class="py-3 px-2.5 w-[19%]">Kontak &amp; Akun</th>
                            <th class="py-3 px-2.5 w-[14%]">Penempatan</th>
                            <th class="py-3 px-2 w-[11%]">Role</th>
                            <th class="py-3 px-2.5 w-[17%]">Statistik ({{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('M Y') }})</th>
                            <th class="py-3 px-2 w-[10%]">Status</th>
                            <th class="py-3 pr-3.5 text-right w-[7%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employees as $emp)
                            <tr class="hover:bg-teal-50/20 transition-colors">
                                <!-- Profile & Name -->
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center gap-2.5">
                                        @if ($emp->avatar_url)
                                            <img src="{{ $emp->avatar_url }}" 
                                                 alt="{{ $emp->name }}" 
                                                 style="object-position: center top;"
                                                 onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                                 class="w-8 h-8 rounded-xl object-cover object-top border border-slate-200 shrink-0 shadow-2xs">
                                            <div style="display: none;" class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                {{ $emp->initials }}
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#18C4C4] to-[#0D9488] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                {{ $emp->initials }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-slate-900 block text-xs tracking-tight truncate" title="{{ $emp->name }}">{{ $emp->name }}</span>
                                            <div class="flex items-center gap-1 mt-0.5 text-[10px] text-slate-500 font-mono">
                                                <span class="text-teal-700 font-bold bg-teal-50 px-1 rounded">{{ $emp->employee_code }}</span>
                                                @if ($emp->nik)
                                                    <span class="text-slate-300">·</span>
                                                    <span class="truncate" title="NIK: {{ $emp->nik }}">NIK: {{ $emp->nik }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact & Username -->
                                <td class="py-2.5 px-2.5">
                                    <div class="text-slate-700 space-y-0.5 leading-tight">
                                        <div class="text-[11px] font-mono font-semibold text-slate-800">{{ $emp->phone ?: '-' }}</div>
                                        <div class="text-[10.5px] text-slate-500 truncate" title="{{ $emp->email }}">{{ $emp->email }}</div>
                                        <div class="text-[10px] text-teal-700 font-mono font-medium truncate">@<span>{{ $emp->username }}</span></div>
                                    </div>
                                </td>

                                <!-- Division, Position & Supervisor -->
                                <td class="py-2.5 px-2.5">
                                    <div class="space-y-0.5 leading-tight">
                                        <div class="flex items-center gap-1 flex-wrap">
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200 truncate max-w-full">
                                                {{ $emp->division ?: 'Non-Divisi' }}
                                            </span>
                                            <span class="text-slate-800 font-semibold text-[11px] truncate">{{ $emp->position ?: '-' }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 truncate">
                                            <span>Atasan:</span>
                                            <span class="text-slate-600 font-medium">{{ $emp->supervisor ? $emp->supervisor->name : '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role -->
                                <td class="py-2.5 px-2">
                                    @php
                                        $primaryRole = $emp->getRoleNames()->first() ?? 'karyawan';
                                        $roleColor = match ($primaryRole) {
                                            'super_admin', 'admin' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'ceo' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'hrd' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'finance' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default => 'bg-teal-50 text-teal-700 border-teal-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9.5px] font-bold border tracking-wide shadow-2xs truncate {{ $roleColor }}">
                                        {{ strtoupper(str_replace('_', ' ', $primaryRole)) }}
                                    </span>
                                </td>

                                <!-- Statistik Absen -->
                                @php
                                    $empStats = $attendanceStats[$emp->id] ?? [
                                        'total_hadir' => 0,
                                        'tepat_waktu' => 0,
                                        'terlambat' => 0,
                                        'alpa' => 0,
                                        'cuti' => 0,
                                        'jam_kerja' => 0,
                                    ];
                                @endphp
                                <td class="py-2.5 px-2.5">
                                    <div class="flex flex-col gap-1 leading-tight">
                                        <div class="flex items-center gap-1 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-teal-50 text-teal-800 border border-teal-200" title="Total Hadir">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                {{ $empStats['total_hadir'] }} hr
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200" title="Tepat Waktu">
                                                Tepat: {{ $empStats['tepat_waktu'] }}
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-semibold {{ $empStats['terlambat'] > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-50 text-slate-500 border border-slate-200' }}" title="Terlambat">
                                                Telat: {{ $empStats['terlambat'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono">
                                            <span class="font-medium text-slate-600">⏱️ {{ $empStats['jam_kerja'] }}h</span>
                                            @if ($empStats['alpa'] > 0)
                                                <span class="text-rose-600 font-bold bg-rose-50 px-1 py-0.2 rounded border border-rose-200">Alpa: {{ $empStats['alpa'] }}</span>
                                            @endif
                                            @if ($empStats['cuti'] > 0)
                                                <span class="text-blue-600 font-medium">· Cuti: {{ $empStats['cuti'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Aktif & Biometrik Wajah -->
                                <td class="py-2.5 px-2 align-middle">
                                    <div class="flex flex-col items-start gap-1 leading-tight">
                                        @if ($emp->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Nonaktif
                                            </span>
                                        @endif

                                        @if ($emp->faceTemplate)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-cyan-50 text-cyan-800 border border-cyan-200/80 truncate max-w-full" title="Wajah sudah terdaftar untuk absensi">
                                                <svg class="w-2.5 h-2.5 text-cyan-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span>Wajah OK</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-medium bg-slate-100 text-slate-500 border border-slate-200/80 truncate max-w-full" title="Belum melakukan registrasi wajah">
                                                <span>No Face</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-2.5 pr-3.5 text-right">
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <!-- Lihat Detail Absen (Requirement) -->
                                        <a href="{{ route('attendance.history', ['user_id' => $emp->id, 'month' => $month, 'year' => $year]) }}"
                                           title="Lihat Detail Riwayat Absen {{ $emp->name }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-600 text-teal-700 hover:text-white border border-teal-200/90 hover:border-teal-600 text-[10.5px] font-bold transition cursor-pointer shadow-2xs whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat</span>
                                        </a>

                                        @if ($canWrite ?? true)
                                        <!-- Edit Button -->
                                        <button @click="openEdit({{ $emp->toJson() }})"
                                                type="button" 
                                                title="Ubah Data Karyawan"
                                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-300 transition cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>

                                        <!-- Reset Pendaftaran Wajah Absen Button -->
                                        <button @click="openResetFace({{ $emp->toJson() }})"
                                                type="button" 
                                                title="Reset Pendaftaran Wajah Absen (Registrasi Ulang)"
                                                class="p-1.5 rounded-lg border transition cursor-pointer shadow-2xs {{ $emp->faceTemplate ? 'border-cyan-200 bg-cyan-50/70 text-cyan-700 hover:bg-cyan-100 hover:border-cyan-300' : 'border-slate-200 text-slate-500 hover:bg-cyan-50 hover:text-cyan-700 hover:border-cyan-300' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m-4 12h2a2 2 0 002-2v-2M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0"/>
                                            </svg>
                                        </button>

                                        <!-- Reset Password Button -->
                                        <button @click="openReset({{ $emp->toJson() }})"
                                                type="button" 
                                                title="Reset Password Akun"
                                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 transition cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </button>

                                        <!-- Delete / Deactivate Button -->
                                        @if ($emp->id !== auth()->id())
                                            <button @click="openDelete({{ $emp->toJson() }})"
                                                    type="button" 
                                                    title="Hapus / Nonaktifkan"
                                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 transition cursor-pointer shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="max-w-sm mx-auto">
                                        <span class="text-3xl block mb-2">🔍</span>
                                        <p class="font-bold text-slate-700 text-sm">Tidak ada karyawan yang sesuai filter</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci pencarian atau reset filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if ($employees->hasPages())
                <div class="px-4 py-3 border-t border-slate-200/80 bg-slate-50/50">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 1: TAMBAH KARYAWAN (LEBIH BESAR, RAPI & TERSTRUKTUR)     -->
        <!-- ============================================================== -->
        <div x-show="isCreateOpen" 
             x-cloak 
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/65 backdrop-blur-xs overflow-y-auto">
            <div @click.outside="isCreateOpen = false" 
                 class="bg-white rounded-3xl shadow-[0_24px_60px_rgba(15,23,42,0.28)] border border-slate-200/90 max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden my-auto">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0"
                     style="background: linear-gradient(90deg, #022836 0%, #053b47 45%, #0a525f 100%);">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-base sm:text-lg text-white leading-tight">Tambah Data Karyawan Baru</h3>
                            <p class="text-xs text-teal-200/85 mt-0.5">Masukkan informasi personal, penempatan kerja, dan akun login</p>
                        </div>
                    </div>
                    <button @click="isCreateOpen = false" type="button" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/85 hover:text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (Form) -->
                <form method="POST" action="{{ route('portal.employees.store') }}" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf

                    <div class="overflow-y-auto p-5 sm:p-6 space-y-4 flex-1 text-xs sm:text-[13px]">
                        <!-- Section 1: Profil Utama -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Informasi Pribadi &amp; Kontak</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Nama Lengkap <span class="text-rose-500">*</span></label>
                                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">NIK (KTP)</label>
                                    <input type="text" name="nik" placeholder="16 digit NIK" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Email Perusahaan <span class="text-rose-500">*</span></label>
                                    <input type="email" name="email" required placeholder="budi@msn.co.id" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                                    <input type="text" name="phone" required placeholder="081234567890" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Akun & Hak Akses -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Kredensial &amp; Role Sistem</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Kode Karyawan</label>
                                    <input type="text" name="employee_code" placeholder="Auto (KR000xx)" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Username</label>
                                    <input type="text" name="username" placeholder="Auto jika kosong" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Role Sistem <span class="text-rose-500">*</span></label>
                                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        @foreach ($roles as $r)
                                            <option value="{{ $r->name }}" {{ $r->name === 'karyawan' ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Password Awal Akun</label>
                                    <input type="text" name="password" placeholder="Default: msn12345" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Organisasi & Penempatan -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">3</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Penempatan Kerja &amp; Struktur Organisasi</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Divisi</label>
                                    <select name="division" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        <option value="">Pilih Divisi...</option>
                                        @foreach ($divisions as $div)
                                            <option value="{{ $div }}">{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Jabatan / Posisi</label>
                                    <input type="text" name="position" placeholder="Teknisi Lapangan, NOC..." class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Atasan Langsung</label>
                                    <select name="supervisor_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        <option value="">Tidak Ada / Direksi</option>
                                        @foreach ($supervisors as $spv)
                                            <option value="{{ $spv->id }}">{{ $spv->name }} ({{ $spv->division }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Tanggal Bergabung</label>
                                    <input type="date" name="join_date" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>

                            <!-- Row 2: Upload Foto Profil & Status Akun Aktif -->
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 pt-1">
                                <div class="sm:col-span-7">
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Foto Profil Karyawan (Maks. 5MB)</label>
                                    <div class="px-3.5 py-2 rounded-xl bg-white border border-dashed border-teal-300 flex items-center">
                                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-[#155E63] hover:file:bg-teal-100 cursor-pointer">
                                    </div>
                                </div>
                                <div class="sm:col-span-5 flex flex-col justify-end">
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Status Akun</label>
                                    <label class="px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 hover:border-teal-400 flex items-center gap-2.5 cursor-pointer shadow-2xs transition h-[42px]">
                                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-[#155E63] focus:ring-teal-500">
                                        <span class="text-slate-800 font-bold text-xs">Akun Aktif Langsung</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Data Rekening Payroll -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">4</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Informasi Payroll (Rekening Bank)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Nama Bank</label>
                                    <select name="bank" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        <option value="">Pilih Bank...</option>
                                        @foreach ($banks as $b)
                                            <option value="{{ $b }}">{{ $b }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Nomor Rekening</label>
                                    <input type="text" name="no_rekening" placeholder="Contoh: 1234567890" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Atas Nama Rekening</label>
                                    <input type="text" name="atas_nama" placeholder="Sesuai buku tabungan" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50/90 border-t border-slate-200/80 flex items-center justify-end gap-3 shrink-0">
                        <button @click="isCreateOpen = false" type="button" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-xs sm:text-sm hover:bg-slate-100 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="px-6 py-2.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-md hover:brightness-105 transition cursor-pointer">
                            Simpan Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 2: EDIT DATA KARYAWAN (LEBIH BESAR, RAPI & TERSTRUKTUR)   -->
        <!-- ============================================================== -->
        <div x-show="isEditOpen" 
             x-cloak 
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/65 backdrop-blur-xs overflow-y-auto">
            <div @click.outside="isEditOpen = false" 
                 class="bg-white rounded-3xl shadow-[0_24px_60px_rgba(15,23,42,0.28)] border border-slate-200/90 max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden my-auto">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0"
                     style="background: linear-gradient(90deg, #022836 0%, #053b47 45%, #0a525f 100%);">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-base sm:text-lg text-white leading-tight">Ubah Data Karyawan</h3>
                            <p class="text-xs text-teal-200/90 font-mono mt-0.5" x-text="editForm.name + ' (' + editForm.employee_code + ')'"></p>
                        </div>
                    </div>
                    <button @click="isEditOpen = false" type="button" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/85 hover:text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (Form) -->
                <form :action="'{{ url('/portal/employees') }}/' + editForm.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    @method('PUT')

                    <div class="overflow-y-auto p-5 sm:p-6 space-y-4 flex-1 text-xs sm:text-[13px]">
                        <!-- Section 1: Profil Utama -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Informasi Pribadi &amp; Kontak</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Nama Lengkap <span class="text-rose-500">*</span></label>
                                    <input type="text" name="name" x-model="editForm.name" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">NIK (KTP)</label>
                                    <input type="text" name="nik" x-model="editForm.nik" placeholder="16 digit NIK" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Email Perusahaan <span class="text-rose-500">*</span></label>
                                    <input type="email" name="email" x-model="editForm.email" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                                    <input type="text" name="phone" x-model="editForm.phone" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Akun & Hak Akses -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Kredensial &amp; Role Sistem</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Kode Karyawan <span class="text-rose-500">*</span></label>
                                    <input type="text" name="employee_code" x-model="editForm.employee_code" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Username <span class="text-rose-500">*</span></label>
                                    <input type="text" name="username" x-model="editForm.username" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Role Sistem <span class="text-rose-500">*</span></label>
                                    <select name="role" x-model="editForm.role" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        @foreach ($roles as $r)
                                            <option value="{{ $r->name }}">{{ ucfirst(str_replace('_', ' ', $r->name)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Ganti Password (Opsional)</label>
                                    <input type="text" name="password" placeholder="Kosongkan jika tetap" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Organisasi & Penempatan -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">3</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Penempatan Kerja &amp; Struktur Organisasi</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Divisi</label>
                                    <select name="division" x-model="editForm.division" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        <option value="">Pilih Divisi...</option>
                                        @foreach ($divisions as $div)
                                            <option value="{{ $div }}">{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Jabatan / Posisi</label>
                                    <input type="text" name="position" x-model="editForm.position" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Atasan Langsung</label>
                                    <select name="supervisor_id" x-model="editForm.supervisor_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        <option value="">Tidak Ada / Direksi</option>
                                        @foreach ($supervisors as $spv)
                                            <option value="{{ $spv->id }}">{{ $spv->name }} ({{ $spv->division }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Tanggal Bergabung</label>
                                    <input type="date" name="join_date" x-model="editForm.join_date" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>

                            <!-- Row 2: Ganti Foto Profil & Status Akun Aktif -->
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 pt-1">
                                <div class="sm:col-span-7">
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Ganti Foto Profil (Maks. 5MB)</label>
                                    <div class="px-3.5 py-2 rounded-xl bg-white border border-dashed border-teal-300 flex items-center">
                                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-[#155E63] hover:file:bg-teal-100 cursor-pointer">
                                    </div>
                                </div>
                                <div class="sm:col-span-5 flex flex-col justify-end">
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Status Akun</label>
                                    <label class="px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 hover:border-teal-400 flex items-center gap-2.5 cursor-pointer shadow-2xs transition h-[42px]">
                                        <input type="checkbox" name="is_active" value="1" :checked="editForm.is_active" class="w-4 h-4 rounded text-[#155E63] focus:ring-teal-500">
                                        <span class="text-slate-800 font-bold text-xs">Status Akun Aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Data Rekening Payroll -->
                        <div class="rounded-2xl bg-slate-50/70 border border-slate-200/80 p-4 sm:p-5 space-y-3.5">
                            <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                                <span class="w-5 h-5 rounded-md bg-[#155E63] text-white text-[11px] font-bold flex items-center justify-center shrink-0">4</span>
                                <span class="font-bold text-slate-800 text-xs uppercase tracking-wider">Informasi Payroll (Rekening Bank)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Nama Bank</label>
                                    <select name="bank" x-model="editForm.bank" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                        <option value="">Pilih Bank...</option>
                                        @foreach ($banks as $b)
                                            <option value="{{ $b }}">{{ $b }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Nomor Rekening</label>
                                    <input type="text" name="no_rekening" x-model="editForm.no_rekening" placeholder="Contoh: 1234567890" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 font-mono focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                                <div>
                                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Atas Nama Rekening</label>
                                    <input type="text" name="atas_nama" x-model="editForm.atas_nama" placeholder="Sesuai buku tabungan" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300/90 text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-teal-500/20 shadow-2xs transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50/90 border-t border-slate-200/80 flex items-center justify-end gap-3 shrink-0">
                        <button @click="isEditOpen = false" type="button" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-xs sm:text-sm hover:bg-slate-100 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="px-6 py-2.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-md hover:brightness-105 transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 3: RESET PASSWORD CONFIRMATION                          -->
        <!-- ============================================================== -->
        <div x-show="isResetOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="isResetOpen = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                    🔑
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">Reset Password Karyawan?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Password akun untuk <strong x-text="resetTarget.name"></strong> (<span class="font-mono text-teal-700" x-text="resetTarget.code"></span>) akan direset ke password acak sementara. Karyawan akan diwajibkan mengganti password baru saat login berikutnya.
                </p>

                <form :action="'{{ url('/portal/employees') }}/' + resetTarget.id + '/reset-password'" method="POST" class="mt-5 flex items-center justify-center gap-2.5">
                    @csrf
                    <button @click="isResetOpen = false" type="button" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                        Ya, Reset Password
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 4: HAPUS / NONAKTIFKAN KARYAWAN                         -->
        <!-- ============================================================== -->
        <div x-show="isDeleteOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="isDeleteOpen = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                    ⚠️
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">Hapus / Nonaktifkan Karyawan?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Anda yakin ingin menghapus data <strong x-text="deleteTarget.name"></strong> (<span class="font-mono text-teal-700" x-text="deleteTarget.code"></span>)? Akun ini akan dinonaktifkan dari sistem dan dipindahkan ke arsip (soft delete).
                </p>

                <form :action="'{{ url('/portal/employees') }}/' + deleteTarget.id" method="POST" class="mt-5 flex items-center justify-center gap-2.5">
                    @csrf
                    @method('DELETE')
                    <button @click="isDeleteOpen = false" type="button" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">
                        Ya, Hapus Karyawan
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 5: RESET PENDAFTARAN WAJAH ABSEN                        -->
        <!-- ============================================================== -->
        <div x-show="isResetFaceOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="isResetFaceOpen = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-200 text-[#155E63] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m-4 12h2a2 2 0 002-2v-2M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0"/>
                    </svg>
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900">Reset Pendaftaran Wajah Absen?</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Data biometrik wajah absen untuk <strong class="text-slate-800" x-text="resetFaceTarget.name"></strong> (<span class="font-mono text-teal-700" x-text="resetFaceTarget.code"></span>) akan dihapus. Karyawan harus melakukan <strong>Daftarkan Wajah</strong> ulang saat membuka halaman Absen.
                </p>

                <form :action="'{{ url('/portal/employees') }}/' + resetFaceTarget.id + '/reset-face'" method="POST" class="mt-4 space-y-3 text-left">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Alasan Reset (Opsional)</label>
                        <input type="text" 
                               name="reason" 
                               placeholder="Contoh: Ganti perangkat / pembaruan foto wajah..." 
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="pt-2 flex items-center justify-center gap-2.5">
                        <button @click="isResetFaceOpen = false" type="button" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                                class="px-5 py-2 rounded-xl text-white font-bold text-xs shadow-sm hover:brightness-105 transition cursor-pointer">
                            Ya, Reset Wajah
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>

