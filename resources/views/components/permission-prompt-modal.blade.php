<div x-data="permissionOnboarding()" 
     x-init="init()"
     x-cloak>
    <!-- Modal Backdrop & Container -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/70 dark:bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        
        <div class="bg-white dark:bg-[#111c26] rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-[0_24px_70px_rgba(15,23,42,0.35)] max-w-lg w-full overflow-hidden my-auto" 
             @click.outside="dismiss()">
            
            <!-- Sleek Gradient Header -->
            <div class="px-5 py-4 sm:px-6 sm:py-5 bg-gradient-to-r from-[#093c40] via-[#155E63] to-[#0d9488] text-white relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-white/15 border border-white/25 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6 text-teal-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-base sm:text-lg text-white leading-tight">
                                Izin Akses Perangkat
                            </h3>
                            <p class="text-xs text-teal-100/90 font-normal mt-0.5">
                                Aktifkan akses 1 kali untuk fungsionalitas penuh portal
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="dismiss()" 
                            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/90 hover:text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Content Body: 4 Permission Cards Grid -->
            <div class="p-5 sm:p-6 space-y-4">
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Portal PT Media Solusi Network memerlukan izin berikut agar fitur absensi, peta jaringan, dan asisten AI bekerja optimal:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- 1. Kamera -->
                    <div class="p-3 sm:p-3.5 rounded-2xl border transition-all"
                         :class="permissions.camera === 'granted' 
                            ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-300/80 dark:border-emerald-800/60' 
                            : (permissions.camera === 'denied' 
                                ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-300/70 dark:border-rose-800/60' 
                                : 'bg-slate-50/70 dark:bg-slate-900/50 border-slate-200/90 dark:border-slate-800')">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-8 h-8 rounded-xl bg-teal-100/70 dark:bg-teal-950/80 text-[#155E63] dark:text-teal-400 flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                                  :class="permissions.camera === 'granted' ? 'bg-emerald-600 text-white' : (permissions.camera === 'denied' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400')"
                                  x-text="permissions.camera === 'granted' ? '✓ Aktif' : (permissions.camera === 'denied' ? '✕ Ditolak' : 'Kamera')">
                            </span>
                        </div>
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white mt-2">Kamera</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug mt-0.5">Verifikasi biometrik selfie absensi &amp; lampiran tiket</p>
                    </div>

                    <!-- 2. Lokasi / GPS / Maps -->
                    <div class="p-3 sm:p-3.5 rounded-2xl border transition-all"
                         :class="permissions.location === 'granted' 
                            ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-300/80 dark:border-emerald-800/60' 
                            : (permissions.location === 'denied' 
                                ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-300/70 dark:border-rose-800/60' 
                                : 'bg-slate-50/70 dark:bg-slate-900/50 border-slate-200/90 dark:border-slate-800')">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-8 h-8 rounded-xl bg-blue-100/70 dark:bg-blue-950/80 text-blue-700 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                                  :class="permissions.location === 'granted' ? 'bg-emerald-600 text-white' : (permissions.location === 'denied' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400')"
                                  x-text="permissions.location === 'granted' ? '✓ Aktif' : (permissions.location === 'denied' ? '✕ Ditolak' : 'Lokasi')">
                            </span>
                        </div>
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white mt-2">Lokasi (GPS)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug mt-0.5">Validasi radius kantor &amp; rute penugasan dinas</p>
                    </div>

                    <!-- 3. Notifikasi -->
                    <div class="p-3 sm:p-3.5 rounded-2xl border transition-all"
                         :class="permissions.notification === 'granted' 
                            ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-300/80 dark:border-emerald-800/60' 
                            : (permissions.notification === 'denied' 
                                ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-300/70 dark:border-rose-800/60' 
                                : 'bg-slate-50/70 dark:bg-slate-900/50 border-slate-200/90 dark:border-slate-800')">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-8 h-8 rounded-xl bg-amber-100/70 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                                  :class="permissions.notification === 'granted' ? 'bg-emerald-600 text-white' : (permissions.notification === 'denied' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400')"
                                  x-text="permissions.notification === 'granted' ? '✓ Aktif' : (permissions.notification === 'denied' ? '✕ Ditolak' : 'Notifikasi')">
                            </span>
                        </div>
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white mt-2">Notifikasi</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug mt-0.5">Pemberitahuan persetujuan izin, lembur, &amp; pesan</p>
                    </div>

                    <!-- 4. Voice / Mikrofon -->
                    <div class="p-3 sm:p-3.5 rounded-2xl border transition-all"
                         :class="permissions.microphone === 'granted' 
                            ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-300/80 dark:border-emerald-800/60' 
                            : (permissions.microphone === 'denied' 
                                ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-300/70 dark:border-rose-800/60' 
                                : 'bg-slate-50/70 dark:bg-slate-900/50 border-slate-200/90 dark:border-slate-800')">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-8 h-8 rounded-xl bg-purple-100/70 dark:bg-purple-950/80 text-purple-700 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                                  :class="permissions.microphone === 'granted' ? 'bg-emerald-600 text-white' : (permissions.microphone === 'denied' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400')"
                                  x-text="permissions.microphone === 'granted' ? '✓ Aktif' : (permissions.microphone === 'denied' ? '✕ Ditolak' : 'Voice')">
                            </span>
                        </div>
                        <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white mt-2">Suara (Voice)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug mt-0.5">Perintah suara asisten chatbot &amp; pesan suara</p>
                    </div>
                </div>

                <!-- Action Button Trigger -->
                <div class="pt-3 flex flex-col sm:flex-row items-center gap-2.5">
                    <button type="button" 
                            @click="requestAllPermissions()"
                            :disabled="loading || allDone"
                            style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                            class="w-full sm:flex-1 py-3 px-5 rounded-2xl text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-900/20 hover:brightness-105 active:scale-[0.99] transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Meminta Izin Browser...</span>
                            </span>
                        </template>
                        <template x-if="!loading && !allDone">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-teal-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Izinkan Semua Akses Sekaligus</span>
                            </span>
                        </template>
                        <template x-if="allDone">
                            <span class="flex items-center gap-2 text-emerald-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Izin Akses Berhasil Diatur!</span>
                            </span>
                        </template>
                    </button>

                    <button type="button" 
                            @click="dismiss()"
                            class="w-full sm:w-auto py-3 px-5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs sm:text-sm transition cursor-pointer">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function permissionOnboarding() {
        const userId = '{{ auth()->id() ?? "guest" }}';
        const storageKey = 'gomsn_perm_prompt_' + userId;

        return {
            open: false,
            loading: false,
            allDone: false,
            permissions: {
                camera: 'prompt',
                microphone: 'prompt',
                location: 'prompt',
                notification: 'prompt',
            },

            async init() {
                // Check if already completed previously
                const status = localStorage.getItem(storageKey);
                
                // Inspect existing browser permissions silently
                await this.checkCurrentStatus();

                // If not marked done or dismissed, and at least one is not granted yet, show prompt
                const allAlreadyGranted = 
                    this.permissions.camera === 'granted' && 
                    this.permissions.microphone === 'granted' && 
                    this.permissions.location === 'granted' && 
                    this.permissions.notification === 'granted';

                if (status !== 'done' && status !== 'dismissed' && !allAlreadyGranted) {
                    setTimeout(() => {
                        this.open = true;
                    }, 800);
                }

                // Global listener if triggered from button/settings
                window.addEventListener('open-permission-modal', () => {
                    this.checkCurrentStatus();
                    this.open = true;
                });
            },

            async checkCurrentStatus() {
                // 1. Notification
                if ('Notification' in window) {
                    if (Notification.permission === 'granted') this.permissions.notification = 'granted';
                    else if (Notification.permission === 'denied') this.permissions.notification = 'denied';
                }

                // 2. Navigator Permissions API (for camera, mic, geolocation)
                if (navigator.permissions && navigator.permissions.query) {
                    try {
                        const geo = await navigator.permissions.query({ name: 'geolocation' });
                        if (geo.state === 'granted') this.permissions.location = 'granted';
                        else if (geo.state === 'denied') this.permissions.location = 'denied';
                    } catch (e) {}

                    try {
                        const cam = await navigator.permissions.query({ name: 'camera' });
                        if (cam.state === 'granted') this.permissions.camera = 'granted';
                        else if (cam.state === 'denied') this.permissions.camera = 'denied';
                    } catch (e) {}

                    try {
                        const mic = await navigator.permissions.query({ name: 'microphone' });
                        if (mic.state === 'granted') this.permissions.microphone = 'granted';
                        else if (mic.state === 'denied') this.permissions.microphone = 'denied';
                    } catch (e) {}
                }
            },

            async requestAllPermissions() {
                this.loading = true;

                // 1. Request Camera and Microphone together (prompts in 1 unified browser dialog!)
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                        this.permissions.camera = 'granted';
                        this.permissions.microphone = 'granted';
                        stream.getTracks().forEach(track => track.stop());
                    } catch (err) {
                        // Fallback: try camera only then audio only
                        try {
                            const vid = await navigator.mediaDevices.getUserMedia({ video: true });
                            this.permissions.camera = 'granted';
                            vid.getTracks().forEach(t => t.stop());
                        } catch (e1) {
                            this.permissions.camera = 'denied';
                        }
                        try {
                            const aud = await navigator.mediaDevices.getUserMedia({ audio: true });
                            this.permissions.microphone = 'granted';
                            aud.getTracks().forEach(t => t.stop());
                        } catch (e2) {
                            this.permissions.microphone = 'denied';
                        }
                    }
                } else {
                    this.permissions.camera = 'unsupported';
                    this.permissions.microphone = 'unsupported';
                }

                // 2. Request Notification Permission
                if ('Notification' in window) {
                    try {
                        const notifResult = await Notification.requestPermission();
                        this.permissions.notification = (notifResult === 'granted') ? 'granted' : 'denied';
                    } catch (e) {
                        this.permissions.notification = 'denied';
                    }
                } else {
                    this.permissions.notification = 'unsupported';
                }

                // 3. Request Geolocation (GPS / Maps)
                if ('geolocation' in navigator) {
                    await new Promise((resolve) => {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.permissions.location = 'granted';
                                resolve();
                            },
                            (err) => {
                                this.permissions.location = 'denied';
                                resolve();
                            },
                            { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                        );
                    });
                } else {
                    this.permissions.location = 'unsupported';
                }

                this.loading = false;
                this.allDone = true;
                localStorage.setItem(storageKey, 'done');

                // Auto-close after showing success state
                setTimeout(() => {
                    this.open = false;
                }, 1300);
            },

            dismiss() {
                localStorage.setItem(storageKey, 'dismissed');
                this.open = false;
            }
        };
    }
</script>
