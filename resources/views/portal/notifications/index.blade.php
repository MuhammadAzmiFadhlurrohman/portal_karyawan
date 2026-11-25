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

        $oldUserIds = array_values(array_map('intval', (array) old('user_ids', [])));
    @endphp

    <div x-data="{
        targetType: {{ Js::from(old('target_type', 'all')) }},
        selectedDivision: {{ Js::from(old('division', 'Teknik Bandung')) }},
        searchQuery: '',
        selectedUserIds: {{ Js::from($oldUserIds) }},
        mobileTab: 'form', // 'form' or 'preview' on small screens
        isSubmitting: false,
        form: {
            title: {{ Js::from(old('title', '')) }},
            body: {{ Js::from(old('body', '')) }},
            type: {{ Js::from(old('type', 'info')) }},
            link: {{ Js::from(old('link', '')) }}
        },
        allEmployees: {{ Js::from($employeeJson) }},
        
        init() {
            // Restore from localStorage draft if no flashed old input is present
            const hasOldInput = {{ Js::from(old('title') !== null || old('body') !== null || old('target_type') !== null) }};
            if (!hasOldInput) {
                try {
                    const saved = localStorage.getItem('gomsn_broadcast_draft');
                    if (saved) {
                        const d = JSON.parse(saved);
                        if (d.targetType) this.targetType = d.targetType;
                        if (d.selectedDivision) this.selectedDivision = d.selectedDivision;
                        if (Array.isArray(d.selectedUserIds)) this.selectedUserIds = d.selectedUserIds;
                        if (d.form) {
                            if (d.form.title) this.form.title = d.form.title;
                            if (d.form.body) this.form.body = d.form.body;
                            if (d.form.type) this.form.type = d.form.type;
                            if (d.form.link) this.form.link = d.form.link;
                        }
                    }
                } catch (e) {}
            } else {
                this.saveDraft();
            }

            this.$watch('targetType', () => this.saveDraft());
            this.$watch('selectedDivision', () => this.saveDraft());
            this.$watch('selectedUserIds', () => this.saveDraft());
            this.$watch('form', () => this.saveDraft(), { deep: true });
        },

        saveDraft() {
            try {
                localStorage.setItem('gomsn_broadcast_draft', JSON.stringify({
                    targetType: this.targetType,
                    selectedDivision: this.selectedDivision,
                    selectedUserIds: this.selectedUserIds,
                    form: this.form
                }));
            } catch (e) {}
        },

        resetForm() {
            this.form.title = '';
            this.form.body = '';
            this.form.link = '';
            this.form.type = 'info';
            this.targetType = 'all';
            this.selectedDivision = 'Teknik Bandung';
            this.selectedUserIds = [];
            this.searchQuery = '';
            try {
                localStorage.removeItem('gomsn_broadcast_draft');
            } catch (e) {}
        },
        
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
            this.selectedUserIds = Array.from(new Set([...this.selectedUserIds, ...currentFiltered]));
        },

        deselectAll() {
            const currentFilteredSet = new Set(this.filteredEmployees.map(e => e.id));
            this.selectedUserIds = this.selectedUserIds.filter(id => !currentFilteredSet.has(id));
        },

        toggleUser(id) {
            const idx = this.selectedUserIds.indexOf(id);
            if (idx > -1) {
                this.selectedUserIds.splice(idx, 1);
            } else {
                this.selectedUserIds.push(id);
            }
        },

        setLinkPreset(presetUrl) {
            this.form.link = presetUrl;
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
                case 'announcement': return 'PENGUMUMAN';
                case 'warning': return 'PERINGATAN';
                case 'alert': return 'MENDESAK';
                default: return 'INFORMASI';
            }
        },

        get badgeColorClass() {
            switch(this.form.type) {
                case 'announcement': return 'bg-emerald-500 text-white';
                case 'warning': return 'bg-amber-500 text-white';
                case 'alert': return 'bg-rose-500 text-white';
                default: return 'bg-[#155E63] text-white';
            }
        },

        get bannerBorderColor() {
            switch(this.form.type) {
                case 'announcement': return 'border-emerald-400/80';
                case 'warning': return 'border-amber-400/80';
                case 'alert': return 'border-rose-400/80';
                default: return 'border-teal-400/80';
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

            if (!this.form.title.trim() || !this.form.body.trim()) {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Belum Lengkap',
                        text: 'Judul dan isi pesan notifikasi wajib diisi.',
                        confirmButtonColor: '#155E63'
                    });
                } else {
                    alert('Judul dan isi pesan notifikasi wajib diisi.');
                }
                return false;
            }

            this.isSubmitting = true;
        }
    }" class="space-y-5 w-full pb-16">

        <!-- Top Header Banner (Sculpted Metallic Ocean Teal - Compact & Refined) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-md py-3.5 px-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Back Button + Icon + Title & Subtitle -->
            <div class="relative z-10 flex items-center gap-3 min-w-0">
                <a href="{{ route('portal.hub') }}"
                   class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white/90 hover:text-white transition shrink-0 active:scale-95 shadow-2xs"
                   title="Kembali ke Portal Hub">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                <div class="w-9 h-9 rounded-xl bg-teal-400/20 border border-teal-300/30 flex items-center justify-center text-cyan-200 shrink-0 shadow-inner">
                    <svg class="w-5 h-5 text-cyan-300 drop-shadow-[0_0_6px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-heading text-sm sm:text-base font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                            <span>Pusat Broadcast Notifikasi</span>
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-200 border border-teal-400/30">
                            FCM Push &amp; Web
                        </span>
                    </div>
                    <p class="text-[11px] sm:text-xs text-teal-100/80 leading-tight truncate mt-0.5">
                        Kirim pesan dan push notifikasi custom ke smartphone atau browser karyawan PT MSN.
                    </p>
                </div>
            </div>

            <!-- Right: Action Buttons & Status Badge -->
            <div class="relative z-10 flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                <a href="{{ route('portal.notifications.history') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold flex items-center gap-2 transition active:scale-95 shadow-2xs">
                    <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Riwayat Siaran</span>
                    <span class="px-1.5 py-0.5 rounded-full bg-teal-400/30 text-teal-200 text-[10px] font-bold">{{ $totalBroadcastsCount }}</span>
                </a>

                <div class="px-3 py-1.5 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs flex items-center gap-2 text-white text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-teal-100 hidden sm:inline">Karyawan:</span>
                    <span class="font-bold text-white">{{ $totalActiveEmployees }}</span>
                </div>
            </div>
        </div>

        <!-- Sleek Compact Metrics Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3">
            <!-- 1. Total Karyawan Aktif -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-teal-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100/80 text-teal-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Karyawan Aktif</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $totalActiveEmployees }}</span>
                </div>
            </div>

            <!-- 2. Perangkat Terhubung (FCM) -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-sky-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-100/80 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Perangkat FCM</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $registeredDevicesCount }}</span>
                </div>
            </div>

            <!-- 3. Broadcast Hari Ini -->
            <div class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 transition-colors flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100/80 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate">Siaran Hari Ini</span>
                    <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $broadcastsTodayCount }}</span>
                </div>
            </div>

            <!-- 4. Total Siaran Sistem (Clickable Link to History) -->
            <a href="{{ route('portal.notifications.history') }}"
               class="p-3 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-purple-300 hover:bg-purple-50/20 transition-all flex items-center justify-between group cursor-pointer"
               title="Buka Halaman Riwayat Siaran Notifikasi">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-100/80 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block truncate group-hover:text-purple-700">Total Riwayat ↗</span>
                        <span class="text-base font-extrabold text-slate-800 leading-tight">{{ $totalBroadcastsCount }}</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-purple-600 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- Mobile View Switcher (Visible only on < lg screens) -->
        <div class="lg:hidden flex rounded-xl bg-slate-200/80 p-1 text-xs font-bold">
            <button type="button"
                    @click="mobileTab = 'form'"
                    :class="mobileTab === 'form' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-2 rounded-lg text-center transition flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Form Broadcast</span>
            </button>
            <button type="button"
                    @click="mobileTab = 'preview'"
                    :class="mobileTab === 'preview' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-2 rounded-lg text-center transition flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Live Preview HP</span>
            </button>
        </div>

        <!-- MAIN 2-COLUMN SECTION: Form (Left) & Smartphone Preview (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
            
            <!-- LEFT COLUMN: Form Broadcast (7 of 12 cols on desktop) -->
            <div :class="mobileTab === 'form' ? 'block' : 'hidden lg:block'" class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 shadow-sm p-4 sm:p-6 space-y-5">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                    <div>
                        <h2 class="font-bold text-sm sm:text-base text-slate-800 flex items-center gap-2">
                            <span>Form Kirim Notifikasi Custom</span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Tentukan penerima, kategori notifikasi, dan tuliskan pesan.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="resetForm()"
                                title="Kosongkan form dan buat pesan baru"
                                class="px-2.5 py-1 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 text-[10px] font-semibold transition cursor-pointer flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Kosongkan</span>
                        </button>
                        <span class="px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200/80 text-teal-700 text-[10px] font-bold">
                            Pemberitahuan Instan
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('portal.notifications.send') }}" @submit="submitForm($event)" class="space-y-4">
                    @csrf

                    <!-- 1. TARGET RECIPIENTS PICKER -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                1. Target Penerima Notifikasi <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-teal-700 font-semibold" x-text="targetSummaryText"></span>
                        </div>

                        <!-- Segmented Pill Selector -->
                        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold">
                            <!-- Option 1: Semua Karyawan -->
                            <button type="button"
                                    @click="targetType = 'all'"
                                    :class="targetType === 'all' ? 'bg-[#155E63] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2 px-2 sm:px-3 rounded-lg transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span class="truncate text-[11px] sm:text-xs">Semua Karyawan</span>
                            </button>

                            <!-- Option 2: Per Divisi -->
                            <button type="button"
                                    @click="targetType = 'division'"
                                    :class="targetType === 'division' ? 'bg-[#155E63] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2 px-2 sm:px-3 rounded-lg transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="truncate text-[11px] sm:text-xs">Per Divisi</span>
                            </button>

                            <!-- Option 3: Pilih Karyawan Spesifik -->
                            <button type="button"
                                    @click="targetType = 'selected'"
                                    :class="targetType === 'selected' ? 'bg-[#155E63] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2 px-2 sm:px-3 rounded-lg transition text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="truncate text-[11px] sm:text-xs">Pilih Karyawan</span>
                            </button>
                        </div>

                        <input type="hidden" name="target_type" :value="targetType">

                        <!-- Panel 1: Info Semua Karyawan (Compact & Clean) -->
                        <div x-show="targetType === 'all'" x-transition class="mt-2.5 p-3 rounded-xl bg-teal-50/70 border border-teal-200/80 text-xs text-teal-900 flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-teal-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-teal-900 text-xs">Siaran Serentak ke Seluruh Karyawan</p>
                                <p class="text-[11px] text-teal-700 leading-tight mt-0.5">Notifikasi dikirimkan ke seluruh <span class="font-bold">{{ $totalActiveEmployees }} karyawan aktif</span> di seluruh unit kerja.</p>
                            </div>
                        </div>

                        <!-- Panel 2: Dropdown Pilih Divisi -->
                        <div x-show="targetType === 'division'" x-transition class="mt-2.5 space-y-1.5">
                            <select name="division" x-model="selectedDivision" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer">
                                @foreach ($divisionGroups as $label => $val)
                                    @if ($val !== 'all')
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400">Notifikasi hanya terkirim ke anggota divisi yang dipilih.</p>
                        </div>

                        <!-- Panel 3: Multi-select Checkbox Karyawan Spesifik -->
                        <div x-show="targetType === 'selected'" x-transition class="mt-2.5 space-y-2">
                            <!-- Search & Action Toolbar -->
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="relative flex-1 min-w-[180px]">
                                    <input type="text"
                                           x-model="searchQuery"
                                           placeholder="Cari nama atau divisi..."
                                           class="w-full pl-8 pr-2.5 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="selectAll()" class="px-2 py-1 rounded-md bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 text-[10px] font-bold transition cursor-pointer">
                                        Pilih Semua
                                    </button>
                                    <button type="button" @click="deselectAll()" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-semibold transition cursor-pointer">
                                        Batal
                                    </button>
                                </div>
                            </div>

                            <!-- Scrollable Employee List -->
                            <div class="max-h-52 overflow-y-auto space-y-1 p-1.5 rounded-xl border border-slate-200 bg-slate-50/60 divide-y divide-slate-100">
                                <template x-for="emp in filteredEmployees" :key="emp.id">
                                    <label :class="selectedUserIds.includes(emp.id) ? 'bg-teal-50/90 border-teal-300' : 'bg-white border-transparent hover:bg-white/90'"
                                           class="p-2 rounded-lg border flex items-center justify-between gap-2.5 cursor-pointer transition select-none">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <input type="checkbox"
                                                   name="user_ids[]"
                                                   :value="emp.id"
                                                   :checked="selectedUserIds.includes(emp.id)"
                                                   @change="toggleUser(emp.id)"
                                                   class="w-3.5 h-3.5 rounded text-[#155E63] focus:ring-teal-500 border-slate-300 cursor-pointer">
                                            
                                            <!-- Inisial / Avatar -->
                                            <div class="w-6 h-6 rounded-md bg-slate-200 flex items-center justify-center font-bold text-[10px] text-slate-700 shrink-0 overflow-hidden">
                                                <template x-if="emp.avatar_url">
                                                    <img :src="emp.avatar_url" class="w-full h-full object-cover" :alt="emp.name">
                                                </template>
                                                <template x-if="!emp.avatar_url">
                                                    <span x-text="emp.initials"></span>
                                                </template>
                                            </div>

                                            <div class="min-w-0">
                                                <span class="font-bold text-xs text-slate-800 block truncate" x-text="emp.name"></span>
                                                <span class="text-[9px] text-slate-400 block truncate" x-text="emp.code + ' • ' + emp.division + (emp.office === 'soreang' ? ' (Soreang)' : '')"></span>
                                            </div>
                                        </div>

                                        <span class="text-[9px] font-medium text-slate-400 shrink-0" x-text="emp.position"></span>
                                    </label>
                                </template>

                                <div x-show="filteredEmployees.length === 0" class="p-4 text-center text-xs text-slate-400">
                                    Tidak ada karyawan yang cocok.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. NOTIFICATION TYPE / CATEGORY -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            2. Kategori / Tipe Notifikasi <span class="text-rose-500">*</span>
                        </label>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <!-- Info -->
                            <label :class="form.type === 'info' ? 'border-[#155E63] bg-teal-50/70 ring-2 ring-teal-500/30' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                   class="py-2 px-2.5 rounded-xl border text-center cursor-pointer transition flex items-center gap-2 select-none">
                                <input type="radio" name="type" value="info" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-600 shrink-0"></span>
                                <div class="text-left min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block leading-tight">Info</span>
                                    <span class="text-[9px] text-slate-400 block leading-tight">Umum</span>
                                </div>
                            </label>

                            <!-- Pengumuman -->
                            <label :class="form.type === 'announcement' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500/30' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                   class="py-2 px-2.5 rounded-xl border text-center cursor-pointer transition flex items-center gap-2 select-none">
                                <input type="radio" name="type" value="announcement" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <div class="text-left min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block leading-tight">Pengumuman</span>
                                    <span class="text-[9px] text-slate-400 block leading-tight">Resmi</span>
                                </div>
                            </label>

                            <!-- Peringatan / Penting -->
                            <label :class="form.type === 'warning' ? 'border-amber-600 bg-amber-50/70 ring-2 ring-amber-500/30' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                   class="py-2 px-2.5 rounded-xl border text-center cursor-pointer transition flex items-center gap-2 select-none">
                                <input type="radio" name="type" value="warning" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                                <div class="text-left min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block leading-tight">Penting</span>
                                    <span class="text-[9px] text-slate-400 block leading-tight">Jadwal/Peringatan</span>
                                </div>
                            </label>

                            <!-- Alert / Urgent -->
                            <label :class="form.type === 'alert' ? 'border-rose-600 bg-rose-50/70 ring-2 ring-rose-500/30' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                   class="py-2 px-2.5 rounded-xl border text-center cursor-pointer transition flex items-center gap-2 select-none">
                                <input type="radio" name="type" value="alert" x-model="form.type" class="sr-only">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                                <div class="text-left min-w-0">
                                    <span class="text-xs font-bold text-slate-800 block leading-tight">Mendesak</span>
                                    <span class="text-[9px] text-slate-400 block leading-tight">Operasional</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 3. TITLE & MESSAGE CONTENT -->
                    <div class="space-y-3.5">
                        <!-- Judul -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                    3. Judul Notifikasi <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-slate-400" x-text="form.title.length + ' / 120'"></span>
                            </div>
                            <input type="text"
                                   name="title"
                                   maxlength="120"
                                   x-model="form.title"
                                   required
                                   placeholder="Contoh: Pengumuman Hari Libur &amp; Jadwal Piket Shift"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>

                        <!-- Pesan -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                    4. Isi Pesan Notifikasi <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-slate-400" x-text="form.body.length + ' / 1000'"></span>
                            </div>
                            <textarea name="body"
                                      rows="3"
                                      maxlength="1000"
                                      x-model="form.body"
                                      required
                                      placeholder="Tuliskan isi pesan pemberitahuan yang jelas dan ringkas..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 leading-relaxed"></textarea>
                        </div>

                        <!-- Link Tindakan Opsional + Quick Presets -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                5. Tautan / Link Tindakan (Opsional)
                            </label>
                            <div class="relative">
                                <input type="text"
                                       name="link"
                                       x-model="form.link"
                                       placeholder="Misal: /attendance atau https://..."
                                       class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 font-mono">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </div>
                            
                            <!-- Quick Link Presets -->
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                <span class="text-[10px] text-slate-400">Pilih Cepat:</span>
                                <button type="button" @click="setLinkPreset('/attendance')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 text-[10px] font-mono transition cursor-pointer">
                                    /attendance
                                </button>
                                <button type="button" @click="setLinkPreset('/gomsn/pengajuan')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 text-[10px] font-mono transition cursor-pointer">
                                    /gomsn/pengajuan
                                </button>
                                <button type="button" @click="setLinkPreset('/gomsn/overtime')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 text-[10px] font-mono transition cursor-pointer">
                                    /gomsn/overtime
                                </button>
                                <button type="button" @click="setLinkPreset('/tickets')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 text-[10px] font-mono transition cursor-pointer">
                                    /tickets
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="pt-2">
                        <button type="submit"
                                :disabled="isSubmitting"
                                class="w-full py-3 px-5 rounded-xl bg-gradient-to-r from-teal-500 via-[#155E63] to-[#0c4a4f] hover:from-teal-400 hover:to-[#08383c] text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-teal-500/25 transition-all active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                            <template x-if="!isSubmitting">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span>Kirim &amp; Siarkan Notifikasi Sekarang</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Sedang Mengirim Notifikasi...</span>
                                </div>
                            </template>
                        </button>
                    </div>
                </form>
            </div>

            <!-- RIGHT COLUMN: Authentic High-Fidelity Smartphone Mockup (5 of 12 cols on desktop) -->
            <div :class="mobileTab === 'preview' ? 'block' : 'hidden lg:block'" class="lg:col-span-5 lg:sticky lg:top-6 space-y-3">
                
                <!-- Mockup Wrapper Box (Explicit High-Contrast Dark Card) -->
                <div class="rounded-3xl p-4 sm:p-5 shadow-xl border select-none"
                     style="background: #090f1d; border-color: #1e293b; color: #f8fafc;">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shadow-sm shadow-emerald-400/50"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider font-mono" style="color: #2dd4bf;">Live Push Preview</span>
                        </div>
                        <span class="text-[11px] font-medium" style="color: #94a3b8;">Lock-Screen Simulasi</span>
                    </div>

                    <!-- REALISTIC SMARTPHONE CHASSIS (Fixed proportion, deep titanium border) -->
                    <div class="relative mx-auto w-full max-w-[290px] sm:max-w-[315px] rounded-[2.8rem] p-3 border-[5px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.85)] ring-1"
                         style="background: #020617; border-color: #334155; ring-color: rgba(255,255,255,0.15);">
                        
                        <!-- Top Dynamic Island & Status Bar -->
                        <div class="relative z-20 flex items-center justify-between px-3.5 pt-1.5 pb-2 text-[10px]"
                             style="color: #cbd5e1;">
                            <span class="font-bold text-[11px] tracking-tight font-sans" style="color: #ffffff;">09:41</span>
                            
                            <!-- Dynamic Island Pill -->
                            <div class="w-20 h-4.5 rounded-full flex items-center justify-end px-2 gap-1.5 shadow-inner"
                                 style="background: #000000; border: 1px solid #1e293b;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #1e293b;"></span>
                            </div>

                            <div class="flex items-center gap-1.5 text-[10px]" style="color: #ffffff;">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 19.4c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l1.9-1.9C9.22 19.64 10.57 20 12 20c4.97 0 9-4.03 9-9s-4.03-9-9-9z"/></svg>
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17 4h-3V2h-4v2H7v18h10V4z"/></svg>
                            </div>
                        </div>

                        <!-- Screen Area (Vibrant Deep Aurora Teal Lock Screen Wallpaper) -->
                        <div class="rounded-[2.2rem] p-4 min-h-[440px] flex flex-col justify-between relative overflow-hidden select-none"
                             style="background: radial-gradient(circle at 50% 12%, #0e3f46 0%, #072229 45%, #02090e 100%) !important; border: 1px solid rgba(255, 255, 255, 0.1);">
                            
                            <!-- Aurora Lighting Orbs -->
                            <div class="absolute -top-12 -right-12 w-44 h-44 rounded-full blur-2xl pointer-events-none" style="background: rgba(20, 184, 166, 0.25);"></div>
                            <div class="absolute bottom-10 -left-12 w-44 h-44 rounded-full blur-2xl pointer-events-none" style="background: rgba(6, 182, 212, 0.25);"></div>

                            <!-- Lock Screen Clock Header (High Contrast & Sharp) -->
                            <div class="relative z-10 text-center pt-2">
                                <div class="w-4 h-4 mx-auto mb-1 flex items-center justify-center" style="color: #94a3b8;">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                                </div>
                                <span class="text-4xl font-extralight tracking-tight block font-sans" style="color: #ffffff; text-shadow: 0 3px 12px rgba(0, 0, 0, 0.8);">09:41</span>
                                <p class="text-[11px] font-semibold mt-1 tracking-wide" style="color: #5eead4; text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8);">Selasa, 24 November</p>
                            </div>

                            <!-- Push Notification Glass Banner (Super Crisp White Card) -->
                            <div class="relative z-10 my-auto p-3.5 rounded-2xl transition-all duration-200"
                                 style="background: #ffffff !important; box-shadow: 0 16px 36px -8px rgba(0, 0, 0, 0.45), 0 4px 12px rgba(0, 0, 0, 0.15); border: 1px solid rgba(226, 232, 240, 0.9);"
                                 :class="bannerBorderColor">
                                
                                <!-- App Header in Banner -->
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <img src="{{ asset('assets/logo/icon-192.png') }}" class="w-4.5 h-4.5 rounded-md shadow-2xs shrink-0" alt="Logo">
                                        <span class="font-bold text-[10px] tracking-tight font-sans" style="color: #0f172a;">PORTAL PT MSN</span>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <span :class="badgeColorClass" class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 rounded shadow-2xs" x-text="badgeLabel"></span>
                                        <span class="text-[9px]" style="color: #64748b;">baru saja</span>
                                    </div>
                                </div>

                                <!-- Notification Content -->
                                <div class="space-y-1">
                                    <h4 class="font-bold text-xs leading-snug line-clamp-2"
                                        style="color: #0f172a;"
                                        x-text="form.title ? form.title : 'Judul Notifikasi Akan Muncul Disini'"></h4>
                                    <p class="text-[11px] leading-relaxed line-clamp-3"
                                       style="color: #334155;"
                                       x-text="form.body ? form.body : 'Tuliskan pesan notifikasi pada form di sebelah kiri untuk melihat simulasi tampilan langsung di layar ponsel...'"></p>
                                </div>

                                <!-- Deep Link Action Chip -->
                                <div x-show="form.link" class="mt-2 pt-2 border-t flex items-center gap-1 text-[9px] font-semibold font-mono truncate"
                                     style="border-color: #f1f5f9; color: #0f766e;">
                                    <svg class="w-3 h-3 shrink-0" style="color: #0d9488;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span class="truncate" x-text="'Buka: ' + form.link"></span>
                                </div>
                            </div>

                            <!-- Target Summary Chip at Bottom (Solid Dark Frosted Capsule) -->
                            <div class="relative z-10 pt-2 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-medium"
                                      style="background: rgba(15, 23, 42, 0.75); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.25); box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background: #2dd4bf;"></span>
                                    <span class="truncate max-w-[210px]" x-text="'Target: ' + targetSummaryText"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Home Indicator Bar -->
                        <div class="w-24 h-1 rounded-full mx-auto mt-2.5 mb-0.5" style="background: rgba(255, 255, 255, 0.45);"></div>
                    </div>
                </div>

                <!-- Info Help Box -->
                <div class="p-3.5 rounded-2xl text-[11px] flex items-start gap-2.5"
                     style="background: #f0fdfa; border: 1px solid #99f6e4; color: #115e59;">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" style="color: #0d9488;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="leading-relaxed" style="color: #115e59;">
                        Notifikasi dikirimkan langsung melalui <strong>Google FCM v1</strong> dengan prioritas tinggi. Pesan seketika muncul di lock-screen ponsel karyawan meskipun aplikasi sedang tertutup.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
