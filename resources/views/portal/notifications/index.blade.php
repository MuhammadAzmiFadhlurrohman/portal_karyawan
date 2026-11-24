<x-layouts.app title="Pusat Broadcast Notifikasi — PT MSN">
    @php
        $employeeJson = $employees->map(function($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'code' => $emp->employee_code,
                'division' => $emp->division ?: 'Non-Divisi',
                'office' => $emp->office,
                'position' => $emp->position ?: 'Staff',
                'avatar_url' => $emp->avatar_url,
                'initials' => $emp->initials,
            ];
        })->values();
    @endphp

    <div x-data="{
        targetType: 'all',
        selectedDivision: 'Teknik Bandung',
        searchQuery: '',
        selectedUserIds: [],
        form: {
            title: '',
            body: '',
            type: 'info',
            link: ''
        },
        allEmployees: {{ Js::from($employeeJson) }},
        
        get filteredEmployees() {
            if (!this.searchQuery.trim()) {
                return this.allEmployees;
            }
            const q = this.searchQuery.toLowerCase();
            return this.allEmployees.filter(emp => 
                (emp.name && emp.name.toLowerCase().includes(q)) ||
                (emp.code && emp.code.toLowerCase().includes(q)) ||
                (emp.division && emp.division.toLowerCase().includes(q)) ||
                (emp.position && emp.position.toLowerCase().includes(q))
            );
        },

        selectAll() {
            const currentFiltered = this.filteredEmployees.map(e => e.id);
            const merged = Array.from(new Set([...this.selectedUserIds, ...currentFiltered]));
            this.selectedUserIds = merged;
        },

        deselectAll() {
            const currentFilteredSet = new Set(this.filteredEmployees.map(e => e.id));
            this.selectedUserIds = this.selectedUserIds.filter(id => !currentFilteredSet.has(id));
        },

        isAllSelected() {
            if (this.filteredEmployees.length === 0) return false;
            return this.filteredEmployees.every(e => this.selectedUserIds.includes(e.id));
        },

        toggleUser(id) {
            const idx = this.selectedUserIds.indexOf(id);
            if (idx > -1) {
                this.selectedUserIds.splice(idx, 1);
            } else {
                this.selectedUserIds.push(id);
            }
        },

        get targetSummaryText() {
            if (this.targetType === 'all') {
                return 'Semua Karyawan (' + this.allEmployees.length + ' orang)';
            }
            if (this.targetType === 'division') {
                return 'Divisi: ' + this.selectedDivision;
            }
            return this.selectedUserIds.length + ' Karyawan Terpilih';
        },

        get badgeLabel() {
            switch(this.form.type) {
                case 'announcement': return 'Pengumuman Resmi';
                case 'warning': return 'Penting & Perhatian';
                case 'alert': return 'Darurat / Urgent';
                default: return 'Informasi';
            }
        },

        get badgeColorClass() {
            switch(this.form.type) {
                case 'announcement': return 'bg-emerald-500 text-white';
                case 'warning': return 'bg-amber-500 text-white';
                case 'alert': return 'bg-rose-500 text-white';
                default: return 'bg-teal-500 text-white';
            }
        },

        submitForm(e) {
            if (this.targetType === 'selected' && this.selectedUserIds.length === 0) {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Target Belum Dipilih',
                        text: 'Silakan centang minimal satu karyawan sebagai penerima notifikasi.',
                        confirmButtonColor: '#155E63'
                    });
                } else {
                    alert('Silakan centang minimal satu karyawan sebagai penerima notifikasi.');
                }
                return false;
            }
        }
    }" class="space-y-6 w-full pb-16">

        <!-- Top Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-5 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Ambient Cyan Wave Glow on Right -->
            <svg class="absolute right-0 top-0 bottom-0 h-full w-2/3 pointer-events-none opacity-40" viewBox="0 0 350 70" fill="none" preserveAspectRatio="none">
                <path d="M 0 70 C 90 20 180 60 350 10 L 350 70 Z" fill="url(#notifWaveGrad)"/>
                <path d="M 50 70 C 130 15 220 50 350 20" stroke="rgba(94, 234, 212, 0.4)" stroke-width="1.5" fill="none"/>
                <defs>
                    <linearGradient id="notifWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.1"/>
                        <stop offset="70%" stop-color="#2dd4bf" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35"/>
                    </linearGradient>
                </defs>
            </svg>

            <!-- Left: Back Button + Icon + Title & Subtitle -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <a href="{{ route('portal.hub') }}"
                   class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white/90 hover:text-white transition shrink-0 active:scale-95"
                   title="Kembali ke Portal Hub">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                <div class="w-11 h-11 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                            <span>Pusat Broadcast Notifikasi</span>
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-cyan-400/20 text-cyan-200 border border-cyan-400/30">
                            FCM Push &amp; Web
                        </span>
                    </div>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug">
                        Kirim pengumuman resmi dan notifikasi langsung ke smartphone atau browser karyawan PT MSN.
                    </p>
                </div>
            </div>

            <!-- Right: Quick Stat Chips -->
            <div class="relative z-10 flex items-center gap-2 shrink-0">
                <div class="px-3 py-1.5 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs flex items-center gap-2 text-white text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium text-teal-100">Aktif:</span>
                    <span class="font-bold text-white">{{ $totalActiveEmployees }} Karyawan</span>
                </div>
            </div>
        </div>

        <!-- Alert Notification Messages -->
        @if (session('status'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="font-semibold">{{ session('status') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        <!-- 4 Quick KPI Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Total Karyawan Aktif -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-medium text-slate-500 block truncate">Total Karyawan Aktif</span>
                    <span class="text-xl font-bold text-slate-800 leading-tight">{{ $totalActiveEmployees }}</span>
                </div>
            </div>

            <!-- 2. Perangkat FCM Terdaftar -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-medium text-slate-500 block truncate">Perangkat Terhubung</span>
                    <span class="text-xl font-bold text-slate-800 leading-tight">{{ $registeredDevicesCount }}</span>
                </div>
            </div>

            <!-- 3. Siaran Hari Ini -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-medium text-slate-500 block truncate">Broadcast Hari Ini</span>
                    <span class="text-xl font-bold text-slate-800 leading-tight">{{ $broadcastsTodayCount }}</span>
                </div>
            </div>

            <!-- 4. Total Siaran Terkirim -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-medium text-slate-500 block truncate">Total Siaran Sistem</span>
                    <span class="text-xl font-bold text-slate-800 leading-tight">{{ $totalBroadcastsCount }}</span>
                </div>
            </div>
        </div>

        <!-- MAIN 2-COLUMN SECTION: Form (Left) & Smartphone Preview (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN: Form Broadcast (7 of 12 cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 shadow-sm p-5 sm:p-7 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="font-bold text-base text-slate-800 flex items-center gap-2">
                            <span>Form Kirim Notifikasi Custom</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan penerima, pilih jenis notifikasi, dan tuliskan pesan.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200/80 text-teal-700 text-[11px] font-bold">
                        Pemberitahuan Instan
                    </span>
                </div>

                <form method="POST" action="{{ route('portal.notifications.send') }}" @submit="submitForm($event)" class="space-y-5">
                    @csrf

                    <!-- 1. TARGET RECIPIENTS PICKER -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            1. Target Penerima Notifikasi <span class="text-rose-500">*</span>
                        </label>

                        <!-- Radio Segmented Tabs -->
                        <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200 text-xs font-semibold">
                            <!-- Option 1: Semua Karyawan -->
                            <button type="button"
                                    @click="targetType = 'all'"
                                    :class="targetType === 'all' ? 'bg-[#155E63] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2.5 px-3 rounded-xl transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span class="truncate">Semua Karyawan</span>
                            </button>

                            <!-- Option 2: Per Divisi -->
                            <button type="button"
                                    @click="targetType = 'division'"
                                    :class="targetType === 'division' ? 'bg-[#155E63] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2.5 px-3 rounded-xl transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="truncate">Per Divisi</span>
                            </button>

                            <!-- Option 3: Pilih Karyawan Spesifik -->
                            <button type="button"
                                    @click="targetType = 'selected'"
                                    :class="targetType === 'selected' ? 'bg-[#155E63] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2.5 px-3 rounded-xl transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="truncate">Pilih Karyawan</span>
                            </button>
                        </div>

                        <input type="hidden" name="target_type" :value="targetType">

                        <!-- Panel 1: Info Semua Karyawan -->
                        <div x-show="targetType === 'all'" x-transition class="mt-3 p-3.5 rounded-2xl bg-teal-50/70 border border-teal-200/80 text-xs text-teal-800 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold">Broadcast Global ke Semua Karyawan</p>
                                <p class="text-[11px] text-teal-700 mt-0.5">Notifikasi akan dikirimkan serentak ke seluruh <span class="font-bold">{{ $totalActiveEmployees }} karyawan aktif</span> di seluruh divisi PT MSN.</p>
                            </div>
                        </div>

                        <!-- Panel 2: Dropdown Pilih Divisi -->
                        <div x-show="targetType === 'division'" x-transition class="mt-3 space-y-2">
                            <label class="block text-xs font-semibold text-slate-700">Pilih Grup / Divisi Penerima:</label>
                            <select name="division" x-model="selectedDivision" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer">
                                @foreach ($divisionGroups as $label => $val)
                                    @if ($val !== 'all')
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500">Hanya karyawan aktif di divisi yang dipilih yang akan menerima pemberitahuan.</p>
                        </div>

                        <!-- Panel 3: Multi-select Checkbox Karyawan Spesifik -->
                        <div x-show="targetType === 'selected'" x-transition class="mt-3 space-y-3">
                            <!-- Search & Action Toolbar -->
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="relative flex-1 min-w-[200px]">
                                    <input type="text"
                                           x-model="searchQuery"
                                           placeholder="Cari nama, kode, atau divisi..."
                                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="selectAll()" class="px-2.5 py-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-[11px] font-bold transition">
                                        Pilih Semua
                                    </button>
                                    <button type="button" @click="deselectAll()" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-semibold transition">
                                        Batal Semua
                                    </button>
                                </div>
                            </div>

                            <!-- Selected Count Indicator -->
                            <div class="flex items-center justify-between text-xs font-semibold px-1">
                                <span class="text-slate-600">Daftar Karyawan:</span>
                                <span class="text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-200" x-text="selectedUserIds.length + ' dipilih'"></span>
                            </div>

                            <!-- Scrollable Employee List -->
                            <div class="max-h-64 overflow-y-auto space-y-1.5 p-2 rounded-2xl border border-slate-200 bg-slate-50/60 divide-y divide-slate-100">
                                <template x-for="emp in filteredEmployees" :key="emp.id">
                                    <label :class="selectedUserIds.includes(emp.id) ? 'bg-teal-50/90 border-teal-300 shadow-2xs' : 'bg-white border-transparent hover:bg-white/80'"
                                           class="p-2.5 rounded-xl border flex items-center justify-between gap-3 cursor-pointer transition select-none">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <input type="checkbox"
                                                   name="user_ids[]"
                                                   :value="emp.id"
                                                   :checked="selectedUserIds.includes(emp.id)"
                                                   @change="toggleUser(emp.id)"
                                                   class="w-4 h-4 rounded text-[#155E63] focus:ring-teal-500 border-slate-300 cursor-pointer">
                                            
                                            <!-- Avatar / Initials -->
                                            <div class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 shrink-0 overflow-hidden">
                                                <template x-if="emp.avatar_url">
                                                    <img :src="emp.avatar_url" class="w-full h-full object-cover" :alt="emp.name">
                                                </template>
                                                <template x-if="!emp.avatar_url">
                                                    <span x-text="emp.initials"></span>
                                                </template>
                                            </div>

                                            <div class="min-w-0">
                                                <span class="font-bold text-xs text-slate-800 block truncate" x-text="emp.name"></span>
                                                <span class="text-[10px] text-slate-500 block truncate" x-text="emp.code + ' • ' + emp.division + (emp.office === 'soreang' ? ' (Soreang)' : '')"></span>
                                            </div>
                                        </div>

                                        <span class="text-[10px] font-semibold text-slate-400 shrink-0" x-text="emp.position"></span>
                                    </label>
                                </template>

                                <div x-show="filteredEmployees.length === 0" class="p-6 text-center text-xs text-slate-400">
                                    Tidak ada karyawan yang cocok dengan pencarian.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. NOTIFICATION TYPE / CATEGORY -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            2. Kategori / Tipe Notifikasi <span class="text-rose-500">*</span>
                        </label>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <!-- Info -->
                            <label :class="form.type === 'info' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300'"
                                   class="p-2.5 rounded-xl border text-center cursor-pointer transition flex flex-col items-center gap-1">
                                <input type="radio" name="type" value="info" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                                <span class="text-xs font-bold text-slate-800">Info</span>
                                <span class="text-[10px] text-slate-400">Umum</span>
                            </label>

                            <!-- Pengumuman -->
                            <label :class="form.type === 'announcement' ? 'border-emerald-500 bg-emerald-50/60 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300'"
                                   class="p-2.5 rounded-xl border text-center cursor-pointer transition flex flex-col items-center gap-1">
                                <input type="radio" name="type" value="announcement" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold text-slate-800">Pengumuman</span>
                                <span class="text-[10px] text-slate-400">Resmi</span>
                            </label>

                            <!-- Peringatan / Penting -->
                            <label :class="form.type === 'warning' ? 'border-amber-500 bg-amber-50/60 ring-2 ring-amber-500/20' : 'border-slate-200 hover:border-slate-300'"
                                   class="p-2.5 rounded-xl border text-center cursor-pointer transition flex flex-col items-center gap-1">
                                <input type="radio" name="type" value="warning" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="text-xs font-bold text-slate-800">Penting</span>
                                <span class="text-[10px] text-slate-400">Jadwal/Peringatan</span>
                            </label>

                            <!-- Alert / Urgent -->
                            <label :class="form.type === 'alert' ? 'border-rose-500 bg-rose-50/60 ring-2 ring-rose-500/20' : 'border-slate-200 hover:border-slate-300'"
                                   class="p-2.5 rounded-xl border text-center cursor-pointer transition flex flex-col items-center gap-1">
                                <input type="radio" name="type" value="alert" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span class="text-xs font-bold text-slate-800">Mendesak</span>
                                <span class="text-[10px] text-slate-400">Alert Segera</span>
                            </label>
                        </div>
                    </div>

                    <!-- 3. TITLE & MESSAGE CONTENT -->
                    <div class="space-y-4">
                        <!-- Judul -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    3. Judul Notifikasi <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-400" x-text="form.title.length + ' / 120'"></span>
                            </div>
                            <input type="text"
                                   name="title"
                                   maxlength="120"
                                   x-model="form.title"
                                   required
                                   placeholder="Contoh: Pengumuman Hari Libur Nasional &amp; Jadwal Piket"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>

                        <!-- Pesan -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    4. Isi Pesan Notifikasi <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-400" x-text="form.body.length + ' / 1000'"></span>
                            </div>
                            <textarea name="body"
                                      rows="4"
                                      maxlength="1000"
                                      x-model="form.body"
                                      required
                                      placeholder="Tuliskan isi pesan notifikasi selengkapnya di sini..."
                                      class="w-full px-4 py-3 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 leading-relaxed"></textarea>
                        </div>

                        <!-- Link Tindakan Opsional -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                5. Tautan / Link Tindakan (Opsional)
                            </label>
                            <div class="relative">
                                <input type="text"
                                       name="link"
                                       x-model="form.link"
                                       placeholder="Misal: /attendance atau /gomsn/pengajuan"
                                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 font-mono">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Halaman yang akan otomatis terbuka saat notifikasi diklik oleh karyawan di HP.</p>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-teal-500 to-[#155E63] hover:from-teal-400 hover:to-[#0e4448] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all active:scale-[0.99] flex items-center justify-center gap-2.5 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Kirim &amp; Siarkan Notifikasi Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- RIGHT COLUMN: Interactive Smartphone Notification Mockup (5 of 12 cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <div class="bg-gradient-to-b from-slate-900 to-slate-800 rounded-3xl p-5 text-white shadow-xl border border-slate-700/80">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-teal-300 font-mono">Live Push Preview</span>
                        </div>
                        <span class="text-[11px] text-slate-400">Simulasi Tampilan HP</span>
                    </div>

                    <!-- Smartphone Mockup Frame -->
                    <div class="relative mx-auto max-w-[340px] bg-slate-950 rounded-[2.5rem] p-3.5 border-4 border-slate-800 shadow-2xl">
                        
                        <!-- Top Notch & Speaker -->
                        <div class="flex items-center justify-between px-6 pt-1 pb-3 text-[10px] text-slate-400">
                            <span class="font-bold text-white">09:41</span>
                            <div class="w-16 h-3.5 bg-black rounded-full"></div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 19.4c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l1.9-1.9C9.22 19.64 10.57 20 12 20c4.97 0 9-4.03 9-9s-4.03-9-9-9z"/></svg>
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17 4h-3V2h-4v2H7v18h10V4z"/></svg>
                            </div>
                        </div>

                        <!-- Lock Screen Wall Background Area -->
                        <div class="rounded-[2rem] bg-gradient-to-br from-teal-900 via-slate-900 to-slate-950 p-3 min-h-[360px] flex flex-col justify-start">
                            
                            <!-- Lock Screen Clock -->
                            <div class="text-center my-4">
                                <span class="text-4xl font-extralight text-white tracking-tight">09:41</span>
                                <p class="text-[10px] text-teal-200/80 font-medium mt-0.5">Selasa, 24 November</p>
                            </div>

                            <!-- Push Notification Banner Box (Floating Glass Popup) -->
                            <div class="p-3.5 rounded-2xl bg-white/95 backdrop-blur-md shadow-2xl border border-white/40 text-slate-900 transition-all duration-200">
                                <!-- Notification Header -->
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <img src="{{ asset('assets/logo/icon-192.png') }}" class="w-5 h-5 rounded-md shadow-xs shrink-0" alt="Logo">
                                        <span class="font-bold text-[11px] text-slate-800 uppercase tracking-tight">PORTAL PT MSN</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <span :class="badgeColorClass" class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-md shadow-2xs" x-text="badgeLabel"></span>
                                        <span class="text-[10px] text-slate-400">Baru saja</span>
                                    </div>
                                </div>

                                <!-- Notification Content -->
                                <div class="space-y-1">
                                    <h4 class="font-bold text-xs text-slate-900 leading-snug line-clamp-2"
                                        x-text="form.title ? form.title : 'Judul Notifikasi Akan Muncul Disini'"></h4>
                                    <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-3"
                                       x-text="form.body ? form.body : 'Tuliskan pesan notifikasi pada form di sebelah kiri untuk melihat simulasi tampilan langsung di layar ponsel penerima...'"></p>
                                </div>

                                <!-- Deep Link Action Chip -->
                                <div x-show="form.link" class="mt-2 pt-2 border-t border-slate-200/60 flex items-center gap-1.5 text-[10px] text-teal-700 font-semibold">
                                    <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span class="truncate" x-text="'Buka: ' + form.link"></span>
                                </div>
                            </div>

                            <!-- Target Recipient Chip -->
                            <div class="mt-auto pt-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-white text-[10px] font-medium border border-white/15">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                                    <span x-text="'Target: ' + targetSummaryText"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Home Indicator Bar -->
                        <div class="w-24 h-1 bg-white/30 rounded-full mx-auto mt-3 mb-1"></div>
                    </div>
                </div>

                <!-- Info Help Card -->
                <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200/80 text-xs text-teal-900 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-teal-800">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mekanisme Pengiriman Push FCM</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-teal-800/90">
                        Notifikasi dikirimkan melalui <strong>Google Firebase Cloud Messaging (FCM HTTP v1)</strong> dengan header prioritas tinggi. Pesan langsung muncul di notifikasi lock-screen ponsel karyawan meskipun aplikasi sedang tertutup.
                    </p>
                </div>
            </div>
        </div>

        <!-- SECTION 2: RIWAYAT SIARAN NOTIFIKASI TERKIRIM (History Table) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-5 sm:p-7 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                        <span>Riwayat Pengiriman Notifikasi</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar siaran dan pesan yang telah dikirimkan ke pengguna.</p>
                </div>
                <span class="text-xs font-semibold text-slate-500">
                    Total {{ $logs->total() }} riwayat siaran
                </span>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 select-none">
                        <tr>
                            <th class="px-4 py-3.5">Waktu Kirim</th>
                            <th class="px-4 py-3.5">Pengirim</th>
                            <th class="px-4 py-3.5">Target Penerima</th>
                            <th class="px-4 py-3.5">Tipe</th>
                            <th class="px-4 py-3.5">Judul &amp; Pesan</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-center">Aksi</th>
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
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Waktu Kirim -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-500">
                                    <div class="font-semibold text-slate-800">{{ $log->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $log->created_at?->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</div>
                                </td>

                                <!-- Pengirim -->
                                <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 font-bold text-[10px] flex items-center justify-center">
                                            {{ strtoupper(substr($senderName, 0, 1)) }}
                                        </div>
                                        <span>{{ $senderName }}</span>
                                    </div>
                                </td>

                                <!-- Target Penerima -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        {{ $log->recipient ?: ($targetCount ? $targetCount . ' Karyawan' : 'Semua Karyawan') }}
                                    </span>
                                </td>

                                <!-- Tipe Notifikasi -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if ($type === 'announcement')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Pengumuman</span>
                                    @elseif ($type === 'warning')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Penting</span>
                                    @elseif ($type === 'alert')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Mendesak</span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-300">Info</span>
                                    @endif
                                </td>

                                <!-- Judul & Pesan -->
                                <td class="px-4 py-3.5 max-w-xs">
                                    <div class="font-bold text-slate-800 leading-snug line-clamp-1">{{ $title }}</div>
                                    <div class="text-[11px] text-slate-500 leading-tight line-clamp-2 mt-0.5">{{ $body }}</div>
                                    @if (!empty($payload['link']))
                                        <div class="text-[10px] text-teal-600 font-mono mt-0.5 truncate">
                                            Link: {{ $payload['link'] }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Terkirim
                                    </span>
                                </td>

                                <!-- Aksi Hapus -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    <form method="POST" action="{{ route('portal.notifications.destroy', $log) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat siaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Riwayat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-xs">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                    <span>Belum ada riwayat siaran notifikasi. Silakan tulis dan kirim notifikasi pertama Anda di atas.</span>
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
    </div>
</x-layouts.app>
