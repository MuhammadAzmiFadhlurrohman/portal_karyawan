<x-layouts.app title="Pengaturan">
    <div class="space-y-5 w-full pb-20 max-w-4xl mx-auto"
         x-data="userSettingsPage({
             userId: {{ auth()->id() }},
             initialPrefs: {{ \Illuminate\Support\Js::from($notificationPreferences) }},
             savePrefsUrl: '{{ route('api.fcm.preferences.update') }}',
             csrfToken: '{{ csrf_token() }}'
         })">

        <!-- 1. Header Banner -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.28)] p-4 sm:py-5 sm:px-6 flex items-center justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 30%, #0a525f 70%, #0e6774 100%);">
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-teal-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-teal-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                        Pengaturan Preferensi
                    </h1>
                    <p class="text-xs text-teal-100/80 mt-0.5 leading-snug">
                        Atur tema tampilan, notifikasi pribadi, dan tata letak menu sesuai kenyamanan Anda
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Tampilan & Tema Aplikasi -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Tema Tampilan</h2>
                        <p class="text-[11px] text-slate-500">Pilih mode terang atau gelap untuk aplikasi GoMSN</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                      :class="isDark ? 'bg-slate-800 text-cyan-300' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                      x-text="isDark ? 'Mode Gelap' : 'Mode Terang'"></span>
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Pilihan Mode Terang -->
                <button type="button"
                        @click="setTheme('light', $event)"
                        class="p-4 rounded-2xl border-2 text-left transition-all flex items-center justify-between gap-3 cursor-pointer"
                        :class="!isDark ? 'border-teal-500 bg-teal-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-500 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Mode Terang (Light)</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Cerahkan layar untuk penggunaan siang hari</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                         :class="!isDark ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                        <svg x-show="!isDark" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </button>

                <!-- Pilihan Mode Gelap -->
                <button type="button"
                        @click="setTheme('dark', $event)"
                        class="p-4 rounded-2xl border-2 text-left transition-all flex items-center justify-between gap-3 cursor-pointer"
                        :class="isDark ? 'border-teal-500 bg-teal-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-cyan-300 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Mode Gelap (Dark)</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Nyaman di mata saat kondisi minim cahaya</div>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                         :class="isDark ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                        <svg x-show="isDark" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </button>
            </div>
        </div>

        <!-- 3. Preferensi Notifikasi Push -->
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
                <span x-show="savingPrefs" x-cloak class="text-[11px] font-semibold text-teal-600">Menyimpan...</span>
                <span x-show="savedNotice && !savingPrefs" x-cloak class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">Tersimpan</span>
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
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                            :style="prefs.notify_pengajuan ? 'background: #0d9488;' : 'background: #cbd5e1;'">
                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-sm transform transition duration-200"
                              :style="prefs.notify_pengajuan ? 'transform: translateX(20px);' : 'transform: translateX(0px);'"></span>
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
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                            :style="prefs.notify_lembur ? 'background: #0d9488;' : 'background: #cbd5e1;'">
                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-sm transform transition duration-200"
                              :style="prefs.notify_lembur ? 'transform: translateX(20px);' : 'transform: translateX(0px);'"></span>
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
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                            :style="prefs.notify_tiket ? 'background: #0d9488;' : 'background: #cbd5e1;'">
                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-sm transform transition duration-200"
                              :style="prefs.notify_tiket ? 'transform: translateX(20px);' : 'transform: translateX(0px);'"></span>
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
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                            :style="prefs.notify_absen ? 'background: #0d9488;' : 'background: #cbd5e1;'">
                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-sm transform transition duration-200"
                              :style="prefs.notify_absen ? 'transform: translateX(20px);' : 'transform: translateX(0px);'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 4. Kustomisasi Menu Mobile -->
        <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200/70 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-800">Tata Letak Menu Mobile</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="hasCustomMobileMenu ? 'Anda memiliki kustomisasi urutan/nama/warna menu mobile yang aktif.' : 'Saat ini menggunakan susunan menu mobile default.'"></p>
                    </div>
                </div>
                <button type="button"
                        @click="resetMobileMenu()"
                        :disabled="!hasCustomMobileMenu"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer"
                        :class="hasCustomMobileMenu ? 'bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100' : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'">
                    Reset Menu Mobile
                </button>
            </div>
        </div>

        <!-- 5. Pintasan Akun & Keamanan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('portal.profile') }}"
               class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-teal-400 hover:shadow-sm transition flex items-center justify-between gap-3 group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 group-hover:text-teal-700">Profil, Kontak &amp; Rekening</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5">Ubah foto profil, nomor HP, dan rekening pencairan</div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('portal.profile') }}#keamanan"
               class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-teal-400 hover:shadow-sm transition flex items-center justify-between gap-3 group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 group-hover:text-teal-700">Keamanan &amp; Password</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5">Ganti password akun &amp; kelola login sidik jari</div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 group-hover:text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

    </div>

    <script>
        function userSettingsPage(config) {
            return {
                isDark: document.documentElement.classList.contains('dark'),
                prefs: Object.assign({
                    notify_pengajuan: true,
                    notify_lembur: true,
                    notify_tiket: true,
                    notify_absen: true,
                }, config.initialPrefs || {}),
                savingPrefs: false,
                savedNotice: false,
                hasCustomMobileMenu: false,
                mobileStorageKey: 'gomsn_mobile_menu_user_' + config.userId,

                init() {
                    this.checkMobileMenuStorage();
                    window.addEventListener('gomsn-theme-changed', (e) => {
                        this.isDark = !!(e.detail && e.detail.dark);
                    });
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
                    const prev = this.prefs[key];
                    this.prefs[key] = !prev;
                    this.savingPrefs = true;
                    this.savedNotice = false;

                    try {
                        const res = await fetch(config.savePrefsUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify(this.prefs)
                        });
                        if (!res.ok) throw new Error('Gagal menyimpan preferensi');
                        this.savedNotice = true;
                        setTimeout(() => { this.savedNotice = false; }, 2200);
                    } catch (err) {
                        this.prefs[key] = prev;
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
</x-layouts.app>
