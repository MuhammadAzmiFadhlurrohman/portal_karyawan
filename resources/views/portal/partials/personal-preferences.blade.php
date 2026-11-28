@php
    $userSettingsConfig = [
        'userId' => auth()->id(),
    ];
@endphp
<script>
    window.__gomsnUserSettingsConfig = @json($userSettingsConfig);

    function userSettingsPage() {
        const config = window.__gomsnUserSettingsConfig || {};
        return {
            isDark: document.documentElement.classList.contains('dark'),
            activeAccent: document.documentElement.getAttribute('data-accent') || 'teal',
            accentDropdownOpen: false,
            accentThemes: [
                { name: 'teal', label: 'Ocean Teal', desc: 'Default GoMSN', hex: '#0d9488', soft: '#f0fdfa', gradient: 'linear-gradient(135deg, #0a525f 0%, #0d9488 100%)' },
                { name: 'blue', label: 'Royal Blue', desc: 'Biru Profesional', hex: '#2563eb', soft: '#eff6ff', gradient: 'linear-gradient(135deg, #102a63 0%, #2563eb 100%)' },
                { name: 'indigo', label: 'Indigo', desc: 'Navy Elegan', hex: '#4f46e5', soft: '#eef2ff', gradient: 'linear-gradient(135deg, #1f1b5c 0%, #4f46e5 100%)' },
                { name: 'emerald', label: 'Emerald', desc: 'Hijau Segar', hex: '#059669', soft: '#ecfdf5', gradient: 'linear-gradient(135deg, #064230 0%, #059669 100%)' },
                { name: 'purple', label: 'Royal Purple', desc: 'Ungu Modern', hex: '#7c3aed', soft: '#faf5ff', gradient: 'linear-gradient(135deg, #311761 0%, #7c3aed 100%)' },
                { name: 'rose', label: 'Crimson Rose', desc: 'Merah Berani', hex: '#e11d48', soft: '#fff1f2', gradient: 'linear-gradient(135deg, #570e22 0%, #e11d48 100%)' },
                { name: 'amber', label: 'Amber Gold', desc: 'Emas Hangat', hex: '#d97706', soft: '#fffbeb', gradient: 'linear-gradient(135deg, #542807 0%, #d97706 100%)' },
                { name: 'pink', label: 'Sakura Pink', desc: 'Merah Muda Cerah', hex: '#ec4899', soft: '#fdf2f8', gradient: 'linear-gradient(135deg, #831843 0%, #ec4899 100%)' },
            ],
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
                this.accentDropdownOpen = false;
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
                this.accentDropdownOpen = false;
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

<div class="space-y-5 w-full" x-data="userSettingsPage()">
    <!-- 1. Warna Tema Aplikasi (Dropdown di Mobile, Grid di Desktop) -->
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
            <span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold text-white shrink-0"
                  :style="'background: ' + getActiveAccentObj().hex"
                  x-text="getActiveAccentObj().label"></span>
        </div>

        <!-- Mobile Dropdown Selector -->
        <div class="p-4 sm:hidden relative" data-accent-swatch="true" @click.outside="accentDropdownOpen = false">
            <button type="button"
                    @click="accentDropdownOpen = !accentDropdownOpen"
                    :aria-expanded="accentDropdownOpen"
                    class="w-full p-3.5 rounded-2xl border-2 text-left transition-all flex items-center justify-between gap-3 cursor-pointer"
                    :style="isDark
                        ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex}22;`
                        : `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().soft};`">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center text-white shadow-sm"
                         :style="`background: ${getActiveAccentObj().gradient};`">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold truncate"
                             :style="isDark ? 'color: #f8fafc;' : 'color: #1e293b;'"
                             x-text="getActiveAccentObj().label"></div>
                        <div class="text-[11px] truncate mt-0.5"
                             :style="isDark ? 'color: #94a3b8;' : 'color: #64748b;'"
                             x-text="getActiveAccentObj().desc"></div>
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-transform duration-200"
                     :class="accentDropdownOpen ? 'rotate-180' : ''"
                     :style="isDark ? 'background: rgba(255,255,255,0.08); color: #e2e8f0;' : 'background: rgba(15,23,42,0.06); color: #475569;'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            <!-- Dropdown Options List -->
            <div x-show="accentDropdownOpen"
                 x-cloak
                 style="display: none;"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="mt-2.5 rounded-2xl border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-900 shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                <template x-for="item in accentThemes" :key="'mob-' + item.name">
                    <button type="button"
                            @click="setAccent(item.name)"
                            class="w-full px-3.5 py-2.5 text-left transition flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60"
                            :class="activeAccent === item.name ? 'bg-slate-50/80 dark:bg-slate-800/80' : ''">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center text-white shadow-2xs"
                                 :style="`background: ${item.gradient};`">
                                <svg x-show="activeAccent === item.name" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold truncate text-slate-800 dark:text-slate-100"
                                     x-text="item.label"></div>
                                <div class="text-[10.5px] truncate text-slate-500 dark:text-slate-400"
                                     x-text="item.desc"></div>
                            </div>
                        </div>
                        <span x-show="activeAccent === item.name"
                              class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white shrink-0"
                              :style="`background: ${item.hex};`">Aktif</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Desktop Grid Selector -->
        <div class="hidden sm:grid sm:grid-cols-4 gap-3 p-5" data-accent-swatch="true">
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

    <!-- 2. Mode Tampilan (Terang / Gelap) -->
    <div class="rounded-2xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-800 leading-tight">Mode Layar</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Pilih mode terang atau gelap</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap shrink-0"
                  :class="isDark ? 'bg-slate-800 text-cyan-300 border border-slate-700' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                  x-text="isDark ? 'Mode Gelap' : 'Mode Terang'"></span>
        </div>

        <div class="p-4 sm:p-5 grid grid-cols-2 gap-3" data-accent-swatch="true">
            <!-- Pilihan Mode Terang -->
            <button type="button"
                    @click="setTheme('light', $event)"
                    class="p-4 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-3 cursor-pointer"
                    :style="!isDark
                        ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().soft}; box-shadow: 0 4px 12px ${getActiveAccentObj().hex}20;`
                        : 'border-color: rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.5);'">
                <div class="flex items-center justify-between w-full">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 text-white"
                         :style="!isDark ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex};` : 'border-color: #64748b;'">
                        <svg x-show="!isDark" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="text-xs sm:text-sm font-bold leading-snug" :style="isDark ? 'color: #f8fafc;' : 'color: #1e293b;'">Mode Terang</div>
                    <div class="text-[10.5px] sm:text-[11px] mt-0.5 leading-snug" :style="isDark ? 'color: #94a3b8;' : 'color: #64748b;'">Cerah untuk siang hari</div>
                </div>
            </button>

            <!-- Pilihan Mode Gelap -->
            <button type="button"
                    @click="setTheme('dark', $event)"
                    class="p-4 rounded-2xl border-2 text-left transition-all flex flex-col justify-between gap-3 cursor-pointer"
                    :style="isDark
                        ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex}26; box-shadow: 0 4px 14px ${getActiveAccentObj().hex}2e;`
                        : 'border-color: #e2e8f0; background: #ffffff;'">
                <div class="flex items-center justify-between w-full">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-cyan-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 text-white"
                         :style="isDark ? `border-color: ${getActiveAccentObj().hex}; background: ${getActiveAccentObj().hex};` : 'border-color: #cbd5e1;'">
                        <svg x-show="isDark" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="text-xs sm:text-sm font-bold leading-snug" :style="isDark ? 'color: #f8fafc;' : 'color: #1e293b;'">Mode Gelap</div>
                    <div class="text-[10.5px] sm:text-[11px] mt-0.5 leading-snug" :style="isDark ? 'color: #94a3b8;' : 'color: #64748b;'">Nyaman minim cahaya</div>
                </div>
            </button>
        </div>
    </div>

    <!-- 3. Keamanan Akun: Login Biometrik & Ubah Password -->
    @include('portal.partials.security-settings')

    <!-- 4. Kustomisasi Menu Mobile & Pintasan Profil -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
    </div>
</div>
