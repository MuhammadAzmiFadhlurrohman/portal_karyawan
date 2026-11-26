<x-layouts.app title="Pengaturan">
    @php
        $userSettingsConfig = [
            'userId' => auth()->id(),
            'initialPrefs' => $notificationPreferences,
            'savePrefsUrl' => route('api.fcm.preferences.update', [], false),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <script>
        window.__gomsnUserSettingsConfig = @json($userSettingsConfig);

        function userSettingsPage() {
            const config = window.__gomsnUserSettingsConfig || {};
            return {
                isDark: document.documentElement.classList.contains('dark'),
                activeAccent: document.documentElement.getAttribute('data-accent') || 'teal',
                accentThemes: [
                    { name: 'teal', label: 'Ocean Teal', desc: 'Default GoMSN', hex: '#0d9488', soft: '#f0fdfa', gradient: 'linear-gradient(135deg, #0a525f 0%, #0d9488 100%)' },
                    { name: 'blue', label: 'Royal Blue', desc: 'Biru Profesional', hex: '#2563eb', soft: '#eff6ff', gradient: 'linear-gradient(135deg, #102a63 0%, #2563eb 100%)' },
                    { name: 'indigo', label: 'Indigo', desc: 'Navy Elegan', hex: '#4f46e5', soft: '#eef2ff', gradient: 'linear-gradient(135deg, #1f1b5c 0%, #4f46e5 100%)' },
                    { name: 'emerald', label: 'Emerald', desc: 'Hijau Segar', hex: '#059669', soft: '#ecfdf5', gradient: 'linear-gradient(135deg, #064230 0%, #059669 100%)' },
                    { name: 'purple', label: 'Royal Purple', desc: 'Ungu Modern', hex: '#7c3aed', soft: '#faf5ff', gradient: 'linear-gradient(135deg, #311761 0%, #7c3aed 100%)' },
                    { name: 'rose', label: 'Crimson Rose', desc: 'Merah Berani', hex: '#e11d48', soft: '#fff1f2', gradient: 'linear-gradient(135deg, #570e22 0%, #e11d48 100%)' },
                    { name: 'amber', label: 'Amber Gold', desc: 'Emas Hangat', hex: '#d97706', soft: '#fffbeb', gradient: 'linear-gradient(135deg, #542807 0%, #d97706 100%)' },
                    { name: 'slate', label: 'Midnight Slate', desc: 'Gelap Minimalis', hex: '#334155', soft: '#f1f5f9', gradient: 'linear-gradient(135deg, #0f172a 0%, #475569 100%)' },
                ],
                prefs: {
                    notify_pengajuan: !!(config.initialPrefs && config.initialPrefs.notify_pengajuan),
                    notify_lembur: !!(config.initialPrefs && config.initialPrefs.notify_lembur),
                    notify_tiket: !!(config.initialPrefs && config.initialPrefs.notify_tiket),
                    notify_absen: !!(config.initialPrefs && config.initialPrefs.notify_absen),
                },
                savingPrefs: false,
                savedNotice: false,
                saveError: '',
                hasCustomMobileMenu: false,
                mobileStorageKey: 'gomsn_mobile_menu_user_' + (config.userId || 0),

                init() {
                    this.checkMobileMenuStorage();
                    window.addEventListener('gomsn-theme-changed', (e) => {
                        this.isDark = !!(e.detail && e.detail.dark);
                    });
                    window.addEventListener('gomsn-accent-changed', (e) => {
                        if (e.detail && e.detail.accent) {
                            this.activeAccent = e.detail.accent;
                        }
                    });
                },

                getActiveAccentObj() {
                    return this.accentThemes.find(t => t.name === this.activeAccent) || this.accentThemes[0];
                },

                setAccent(name) {
                    this.activeAccent = name;
                    if (typeof window.setGomsnAccent === 'function') {
                        window.setGomsnAccent(name);
                    }
                    if (window.Toast) {
                        const obj = this.getActiveAccentObj();
                        window.Toast.fire({ icon: 'success', title: 'Warna tema diubah ke ' + obj.label });
                    }
                },

                checkMobileMenuStorage() {
                    try {
                        this.hasCustomMobileMenu = !!localStorage.getItem(this.mobileStorageKey);
                    } catch (e) {
                        this.hasCustomMobileMenu = false;
                    }
                },

                setTheme(mode, event) {
                    const targetDark = mode === 'dark';
                    if (this.isDark === targetDark) return;
                    if (typeof window.toggleGomsnTheme === 'function') {
                        window.toggleGomsnTheme(event);
                    } else {
                        document.documentElement.classList.toggle('dark', targetDark);
                        try { localStorage.setItem('gomsn_theme', mode); } catch (e) {}
                        this.isDark = targetDark;
                    }
                },

                async togglePref(key) {
                    if (this.savingPrefs) return;
                    const prev = !!this.prefs[key];
                    this.prefs[key] = !prev;
                    this.savingPrefs = true;
                    this.savedNotice = false;
                    this.saveError = '';

                    try {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrf = (csrfMeta && csrfMeta.getAttribute('content')) || config.csrfToken || '';
                        const res = await fetch(config.savePrefsUrl || '/api/fcm/preferences', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({
                                notify_pengajuan: !!this.prefs.notify_pengajuan,
                                notify_lembur: !!this.prefs.notify_lembur,
                                notify_tiket: !!this.prefs.notify_tiket,
                                notify_absen: !!this.prefs.notify_absen,
                            })
                        });
                        if (!res.ok) {
                            throw new Error('HTTP ' + res.status);
                        }
                        const data = await res.json();
                        if (data && data.preferences) {
                            this.prefs.notify_pengajuan = !!data.preferences.notify_pengajuan;
                            this.prefs.notify_lembur = !!data.preferences.notify_lembur;
                            this.prefs.notify_tiket = !!data.preferences.notify_tiket;
                            this.prefs.notify_absen = !!data.preferences.notify_absen;
                        }
                        this.savedNotice = true;
                        setTimeout(() => { this.savedNotice = false; }, 2200);
                    } catch (err) {
                        this.prefs[key] = prev;
                        this.saveError = 'Gagal menyimpan (' + (err.message || 'error') + ')';
                        if (window.Toast) {
                            window.Toast.fire({ icon: 'error', title: 'Gagal menyimpan pengaturan notifikasi.' });
                        }
                    } finally {
                        this.savingPrefs = false;
                    }
                },

                resetMobileMenu() {
                    if (!this.hasCustomMobileMenu) return;
                    if (confirm('Kembalikan urutan, judul grup, nama, dan warna menu mobile Anda ke default?')) {
                        try {
                            localStorage.removeItem(this.mobileStorageKey);
                            this.hasCustomMobileMenu = false;
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'success', title: 'Susunan menu mobile berhasil dikembalikan ke default.' });
                            }
                        } catch (e) {}
                    }
                }
            };
        }
    </script>

    <div class="space-y-5 w-full pb-20"
         x-data="userSettingsPage()">

        <!-- 1. Header Banner (Full Width & Mengikuti Warna Tema Aplikasi) -->
        <div class="gomsn-themed-banner relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.28)] p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 30%, #0a525f 70%, #0e6774 100%);">
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/40 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-white shrink-0 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Pengaturan Preferensi
                    </h1>
                    <p class="text-xs text-white/80 mt-0.5 leading-snug">
                        Atur mode tampilan, warna tema aplikasi, notifikasi pribadi, dan tata letak menu sesuai selera Anda
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Warna Tema Aplikasi (Tidak Hanya Hijau!) -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-white shadow-2xs"
                         :style="'background: ' + getActiveAccentObj().hex">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Warna Tema Aplikasi</h2>
                        <p class="text-[11px] text-slate-500">Ubah warna utama sidebar, header, dan aksen tombol aplikasi</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold text-white shrink-0"
                      :style="'background: ' + getActiveAccentObj().hex"
                      x-text="getActiveAccentObj().label"></span>
            </div>

            <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-3" data-accent-swatch="true">
                <template x-for="item in accentThemes" :key="item.name">
                    <button type="button"
                            @click="setAccent(item.name)"
                            class="p-3.5 rounded-2xl border-2 text-left transition-all flex items-center gap-3 cursor-pointer"
                            :style="activeAccent === item.name
                                ? (isDark
                                    ? `border-color: ${item.hex}; background: ${item.hex}26; box-shadow: 0 4px 14px ${item.hex}30;`
                                    : `border-color: ${item.hex}; background: ${item.soft}; box-shadow: 0 4px 12px ${item.hex}22;`)
                                : (isDark
                                    ? 'border-color: rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.5);'
                                    : 'border-color: #e2e8f0; background: #ffffff;')">
                        <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center text-white shadow-sm"
                             :style="`background: ${item.gradient};`">
                            <svg x-show="activeAccent === item.name" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold truncate"
                                 :style="isDark ? 'color: #f8fafc;' : 'color: #1e293b;'"
                                 x-text="item.label"></div>
                            <div class="text-[10px] truncate mt-0.5"
                                 :style="isDark ? 'color: #94a3b8;' : 'color: #64748b;'"
                                 x-text="item.desc"></div>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <!-- 3. Mode Tampilan (Terang / Gelap) -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Mode Layar</h2>
                        <p class="text-[11px] text-slate-500">Pilih mode terang atau gelap untuk kenyamanan mata</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                      :class="isDark ? 'bg-slate-800 text-cyan-300' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                      x-text="isDark ? 'Mode Gelap' : 'Mode Terang'"></span>
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3" data-accent-swatch="true">
                <!-- Pilihan Mode Terang -->
                <button type="button"
                        @click="setTheme('light', $event)"
                        class="p-4 rounded-2xl border-2 text-left transition-all flex items-center justify-between gap-3 cursor-pointer"
                        :style="!isDark
                            ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().soft};`
                            : 'border-color: rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.5);'">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold" :style="isDark ? 'color: #f8fafc;' : 'color: #1e293b;'">Mode Terang (Light)</div>
                            <div class="text-[11px] mt-0.5" :style="isDark ? 'color: #94a3b8;' : 'color: #64748b;'">Cerahkan layar untuk penggunaan siang hari</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 text-white"
                         :style="!isDark ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex};` : 'border-color: #64748b;'">
                        <svg x-show="!isDark" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </button>

                <!-- Pilihan Mode Gelap -->
                <button type="button"
                        @click="setTheme('dark', $event)"
                        class="p-4 rounded-2xl border-2 text-left transition-all flex items-center justify-between gap-3 cursor-pointer"
                        :style="isDark
                            ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex}26;`
                            : 'border-color: #e2e8f0; background: #ffffff;'">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-cyan-300 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold" :style="isDark ? 'color: #f8fafc;' : 'color: #1e293b;'">Mode Gelap (Dark)</div>
                            <div class="text-[11px] mt-0.5" :style="isDark ? 'color: #94a3b8;' : 'color: #64748b;'">Nyaman di mata saat kondisi minim cahaya</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 text-white"
                         :style="isDark ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex};` : 'border-color: #cbd5e1;'">
                        <svg x-show="isDark" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </button>
            </div>
        </div>

        <!-- 4. Preferensi Notifikasi Push -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-200/70 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Notifikasi Aplikasi</h2>
                        <p class="text-[11px] text-slate-500">Pilih jenis pemberitahuan push yang ingin Anda terima</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span x-show="saveError" x-cloak class="text-[11px] font-semibold text-rose-600" x-text="saveError"></span>
                    <span x-show="savingPrefs" x-cloak class="text-[11px] font-semibold text-teal-600">Menyimpan...</span>
                    <span x-show="savedNotice && !savingPrefs" x-cloak class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">Tersimpan</span>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                <!-- Pengajuan -->
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800">Pengajuan &amp; Kasbon</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Status persetujuan atasan, pencairan dana, dan pengajuan stok</div>
                    </div>
                    <button type="button"
                            @click="togglePref('notify_pengajuan')"
                            :aria-pressed="prefs.notify_pengajuan"
                            class="relative inline-flex shrink-0 cursor-pointer rounded-full transition-colors duration-200 items-center"
                            :style="`width: 44px; height: 24px; padding: 2px; border: none; background: ${prefs.notify_pengajuan ? getActiveAccentObj().hex : '#cbd5e1'};`">
                        <span class="pointer-events-none block rounded-full bg-white shadow-sm transition-transform duration-200"
                              :style="`width: 20px; height: 20px; transform: translateX(${prefs.notify_pengajuan ? '20px' : '0px'});`"></span>
                    </button>
                </div>

                <!-- Lembur -->
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800">Lembur &amp; SPK</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Pembaruan status persetujuan dan pembayaran lembur</div>
                    </div>
                    <button type="button"
                            @click="togglePref('notify_lembur')"
                            :aria-pressed="prefs.notify_lembur"
                            class="relative inline-flex shrink-0 cursor-pointer rounded-full transition-colors duration-200 items-center"
                            :style="`width: 44px; height: 24px; padding: 2px; border: none; background: ${prefs.notify_lembur ? getActiveAccentObj().hex : '#cbd5e1'};`">
                        <span class="pointer-events-none block rounded-full bg-white shadow-sm transition-transform duration-200"
                              :style="`width: 20px; height: 20px; transform: translateX(${prefs.notify_lembur ? '20px' : '0px'});`"></span>
                    </button>
                </div>

                <!-- Tiket -->
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800">Tiket Gangguan</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Penugasan tiket baru, pesan balasan, dan pembaruan SLA</div>
                    </div>
                    <button type="button"
                            @click="togglePref('notify_tiket')"
                            :aria-pressed="prefs.notify_tiket"
                            class="relative inline-flex shrink-0 cursor-pointer rounded-full transition-colors duration-200 items-center"
                            :style="`width: 44px; height: 24px; padding: 2px; border: none; background: ${prefs.notify_tiket ? getActiveAccentObj().hex : '#cbd5e1'};`">
                        <span class="pointer-events-none block rounded-full bg-white shadow-sm transition-transform duration-200"
                              :style="`width: 20px; height: 20px; transform: translateX(${prefs.notify_tiket ? '20px' : '0px'});`"></span>
                    </button>
                </div>

                <!-- Absensi -->
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800">Absensi &amp; Pengingat</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Pengingat jam masuk/pulang, status cuti, izin, dan koreksi absen</div>
                    </div>
                    <button type="button"
                            @click="togglePref('notify_absen')"
                            :aria-pressed="prefs.notify_absen"
                            class="relative inline-flex shrink-0 cursor-pointer rounded-full transition-colors duration-200 items-center"
                            :style="`width: 44px; height: 24px; padding: 2px; border: none; background: ${prefs.notify_absen ? getActiveAccentObj().hex : '#cbd5e1'};`">
                        <span class="pointer-events-none block rounded-full bg-white shadow-sm transition-transform duration-200"
                              :style="`width: 20px; height: 20px; transform: translateX(${prefs.notify_absen ? '20px' : '0px'});`"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. Kustomisasi Menu Mobile & Pintasan Akun -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Reset Menu Mobile -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200/70 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 truncate">Menu Mobile</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5" x-text="hasCustomMobileMenu ? 'Kustomisasi aktif' : 'Susunan default'"></div>
                    </div>
                </div>
                <button type="button"
                        @click="resetMobileMenu()"
                        :disabled="!hasCustomMobileMenu"
                        class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition shrink-0 cursor-pointer"
                        :class="hasCustomMobileMenu ? 'bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100' : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'">
                    Reset
                </button>
            </div>

            <!-- Profil & Rekening -->
            <a href="{{ route('portal.profile') }}"
               class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-teal-400 hover:shadow-sm transition flex items-center justify-between gap-3 group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 group-hover:text-teal-700 truncate">Profil, Kontak &amp; Rekening</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5">Ubah foto, nomor HP &amp; rekening</div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            <!-- Keamanan & Password -->
            <a href="{{ route('portal.profile') }}#keamanan"
               class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-teal-400 hover:shadow-sm transition flex items-center justify-between gap-3 group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 group-hover:text-teal-700 truncate">Keamanan &amp; Password</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5">Ganti password &amp; login biometrik</div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

    </div>
</x-layouts.app>
