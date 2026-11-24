<!-- Firebase Cloud Messaging Client Integration (v5) -->
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
<script>
(function() {
    'use strict';

    const SW_VERSION = '2026112410';
    const SW_URL = '/sw.js';

    // Firebase Web App Configuration
    const firebaseConfig = {
        apiKey: "AIzaSyA668mrHa1p0uumFLmK-b5Hmjw9AC2zHzk",
        authDomain: "portal-gomsn.firebaseapp.com",
        projectId: "portal-gomsn",
        storageBucket: "portal-gomsn.firebasestorage.app",
        messagingSenderId: "504216298607",
        appId: "1:504216298607:web:33f6314ca55837fae5999a",
        measurementId: "G-2FS9BQQ087"
    };

    const vapidKey = "{{ config('services.fcm.vapid_key') }}";
    const isSecureOrigin = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;

    // Interactive Apple iOS Safari Home Screen installation guide
    window.showIosPushGuideModal = function() {
        if (!window.Swal) return;
        Swal.fire({
            title: '<div class="flex items-center justify-center gap-2 text-base font-bold text-slate-800"><svg class="w-6 h-6 text-[#155E63]" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.63-.77 1.06-1.85.94-2.93-.93.04-2.07.62-2.73 1.39-.58.67-.99 1.77-.85 2.82 1.05.08 2.01-.51 2.64-1.28z"/></svg><span>Panduan Notifikasi di iPhone</span></div>',
            html: `
                <div class="text-left text-xs text-slate-700 space-y-3 pt-2">
                    <div class="p-3 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 leading-relaxed text-[11px]">
                        <strong>Aturan Resmi Apple iOS:</strong> Di iPhone Safari, notifikasi Web Push baru aktif jika portal telah ditambahkan ke <strong>Layar Utama (Home Screen)</strong>.
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="w-5 h-5 rounded-full bg-[#155E63] text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">1</span>
                            <div>
                                <p class="font-bold text-slate-800">Ketuk Tombol Bagikan (Share)</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Ketuk ikon kotak dengan panah ke atas (<strong class="text-slate-800">⎋</strong>) di bilah navigasi bawah Safari.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="w-5 h-5 rounded-full bg-[#155E63] text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">2</span>
                            <div>
                                <p class="font-bold text-slate-800">Pilih "Tambahkan ke Layar Utama"</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Gulir ke bawah menu lalu pilih opsi <strong class="text-slate-800">"Add to Home Screen"</strong> (ikon <strong class="text-slate-800">[+]</strong>).</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="w-5 h-5 rounded-full bg-[#155E63] text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">3</span>
                            <div>
                                <p class="font-bold text-slate-800">Buka Aplikasi dari Layar Utama</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Tutup Safari, lalu buka ikon <strong>GoMSN</strong> yang baru muncul di Layar Utama iPhone Anda.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">4</span>
                            <div>
                                <p class="font-bold text-emerald-900">Aktifkan Notifikasi</p>
                                <p class="text-[11px] text-emerald-700 mt-0.5">Ketuk tombol <strong>"Aktifkan Sekarang"</strong> di dalam aplikasi Layar Utama. Izin pop-up notifikasi iPhone akan langsung muncul dan tersambung!</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-[11px] leading-relaxed">
                        <strong class="text-slate-800">💡 Ingin tetap di Safari tanpa instal?</strong>
                        <p class="mt-0.5">Anda tetap dapat menggunakan seluruh fitur portal secara normal di Safari. Seluruh pemberitahuan dapat Anda pantau langsung melalui ikon <strong>Lonceng Notifikasi</strong> di bilah atas portal saat website dibuka.</p>
                    </div>

                    <p class="text-[10px] text-slate-400 text-center pt-0.5">
                        * Fitur Push Layar Kunci iPhone memerlukan iOS 16.4+ dan ditambahkan ke Layar Utama sesuai regulasi Apple.
                    </p>
                </div>
            `,
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonColor: '#155E63',
            confirmButtonText: 'Tambah ke Layar Utama',
            cancelButtonColor: '#64748b',
            cancelButtonText: 'Tetap Buka di Safari',
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-slate-200'
            }
        });
    };

    // Ensure clean Service Worker registration (unregister duplicate firebase-messaging-sw.js & upgrade sw.js)
    async function getCleanServiceWorkerRegistration() {
        if (!('serviceWorker' in navigator)) return null;

        try {
            const regs = await navigator.serviceWorker.getRegistrations();
            for (const r of regs) {
                const scriptUrl = (r.active && r.active.scriptURL) || (r.installing && r.installing.scriptURL) || (r.waiting && r.waiting.scriptURL) || '';
                if (scriptUrl.includes('firebase-messaging-sw.js')) {
                    await r.unregister().catch(function() {});
                }
            }
        } catch (e) {}

        try {
            const reg = await navigator.serviceWorker.register(SW_URL, {
                scope: '/',
                updateViaCache: 'none'
            });
            await reg.update().catch(function() {});
            const readyReg = await navigator.serviceWorker.ready;
            window.msnServiceWorkerReg = readyReg || reg;
            return readyReg || reg;
        } catch (e) {
            return await navigator.serviceWorker.ready.catch(function() { return null; });
        }
    }

    // Wait until Firebase SDK script from Google CDN is fully loaded
    async function ensureFirebaseLoaded() {
        if (typeof firebase !== 'undefined' && typeof firebase.messaging === 'function') {
            return true;
        }
        for (let i = 0; i < 30; i++) {
            await new Promise(resolve => setTimeout(resolve, 150));
            if (typeof firebase !== 'undefined' && typeof firebase.messaging === 'function') {
                return true;
            }
        }
        return typeof firebase !== 'undefined';
    }

    // Initialize Firebase
    function initFirebaseApp() {
        if (typeof firebase !== 'undefined' && !firebase.apps.length) {
            try {
                firebase.initializeApp(firebaseConfig);
            } catch (e) {
                console.error('[FCM] Initialization error:', e);
            }
        }
    }

    // Helper to register token to backend
    async function sendTokenToBackend(token, interactive = false) {
        try {
            const response = await fetch('{{ route("api.fcm.token.register") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    token: token,
                    platform: isIOS ? 'ios' : 'web',
                    device_name: (isIOS ? 'Apple iPhone' : (navigator.userAgentData?.platform || navigator.platform || 'Browser')) + ' (' + (isStandalone ? 'PWA Home Screen' : (navigator.userAgent.includes('Mobile') ? 'Mobile' : 'Desktop')) + ')'
                })
            });

            const data = await response.json().catch(function() { return {}; });

            if (response.ok && data.success) {
                localStorage.setItem('gomsn_fcm_token_registered', '1');
                localStorage.setItem('gomsn_sw_version', SW_VERSION);
                console.log('[FCM] Device token successfully registered to backend!');
                
                const connectCard = document.getElementById('hub-notif-connect-card');
                if (connectCard) connectCard.remove();

                if (interactive && window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Perangkat Terhubung!',
                        text: 'Notifikasi berhasil diaktifkan dan terdaftar di server database.',
                        confirmButtonColor: '#155E63',
                        timer: 3500
                    });
                }
                return true;
            } else {
                console.warn('[FCM] Backend rejected token registration:', response.status, data);
                if (interactive && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan ke Database',
                        text: data.message || ('Error HTTP ' + response.status),
                        confirmButtonColor: '#155E63'
                    });
                }
                return false;
            }
        } catch (netErr) {
            console.error('[FCM] Failed to send token to backend:', netErr);
            if (interactive && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: netErr.message || 'Gagal mengirim token ke server.',
                    confirmButtonColor: '#155E63'
                });
            }
            return false;
        }
    }

    // Function to retrieve FCM Token and store to backend
    window.initGomsnFcmToken = async function(interactive = false) {
        if (!isSecureOrigin) {
            if (interactive && window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Memerlukan HTTPS',
                    text: 'Browser HP (Chrome/Android) mewajibkan koneksi HTTPS untuk notifikasi push. Website ini saat ini diakses via HTTP.',
                    confirmButtonColor: '#155E63'
                });
            }
            console.warn('[FCM] Push requires HTTPS origin.');
            return;
        }

        if (!('Notification' in window)) {
            if (interactive && window.Swal) {
                if (isIOS) {
                    if (!isStandalone) {
                        window.showIosPushGuideModal();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pembaruan iOS Diperlukan',
                            html: '<div class="text-left text-xs text-slate-700 space-y-2"><p>Apple mewajibkan minimal <strong>iOS 16.4 atau lebih baru</strong> untuk mendukung Web Push Notification di iPhone.</p><p class="text-slate-500">Silakan periksa dan perbarui iPhone Anda melalui <strong>Pengaturan &gt; Umum &gt; Pembaruan Perangkat Lunak</strong>.</p></div>',
                            confirmButtonColor: '#155E63',
                            confirmButtonText: 'Tutup'
                        });
                    }
                } else {
                    Swal.fire('Error', 'Browser perangkat Anda belum mendukung Web Push Notification. Silakan gunakan Google Chrome.', 'error');
                }
            }
            console.warn('[FCM] Notification API is not available in current window.');
            return;
        }

        if (interactive && window.Swal) {
            Swal.fire({
                title: 'Menghubungkan Notifikasi...',
                text: 'Sedang mendaftarkan perangkat ke Google FCM & server...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        const loaded = await ensureFirebaseLoaded();
        if (!loaded) {
            if (interactive && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'SDK Google Belum Siap',
                    text: 'Gagal memuat pustaka Firebase dari Google CDN. Periksa koneksi internet Anda.',
                    confirmButtonColor: '#155E63'
                });
            }
            return;
        }

        initFirebaseApp();

        let permission = Notification.permission;
        if (permission !== 'granted') {
            permission = await Notification.requestPermission();
        }

        if (permission !== 'granted') {
            if (interactive && window.Swal) {
                Swal.fire('Izin Belum Aktif', 'Izin notifikasi belum diaktifkan di browser. Silakan aktifkan di info situs browser Anda.', 'warning');
            }
            return;
        }

        try {
            const swReg = await getCleanServiceWorkerRegistration();
            const tokenOptions = {};
            if (swReg) {
                tokenOptions.serviceWorkerRegistration = swReg;
            }

            if (vapidKey && vapidKey.trim() !== '') {
                tokenOptions.vapidKey = vapidKey.trim();
            } else {
                console.warn('[FCM] vapidKey is empty!');
                if (interactive && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'VAPID Key Belum Terbaca',
                        text: 'FCM_VAPID_KEY belum terbaca di server. Pastikan sudah ada di .env server lalu jalankan: docker exec -it gomsn2-app php artisan config:clear',
                        confirmButtonColor: '#155E63'
                    });
                    return;
                }
            }

            const messaging = firebase.messaging();
            const token = await messaging.getToken(tokenOptions);
            if (token) {
                console.log('[FCM] FCM Token obtained:', token.substring(0, 16) + '...');
                await sendTokenToBackend(token, interactive);
            } else {
                if (interactive && window.Swal) {
                    Swal.fire('Perhatian', 'Tidak berhasil mendapatkan token dari Firebase.', 'warning');
                }
            }
        } catch (err) {
            console.error('[FCM] Error generating FCM token:', err);
            if (interactive && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Firebase',
                    text: err.message || 'Gagal membuat token notifikasi.',
                    confirmButtonColor: '#155E63'
                });
            }
        }
    };

    // Hide connect card automatically if already registered with latest SW version
    function checkInitialState() {
        if (!isSecureOrigin || !('Notification' in window)) return;

        // Always upgrade Service Worker & re-sync token silently if permission is already granted
        if (Notification.permission === 'granted') {
            const connectCard = document.getElementById('hub-notif-connect-card');
            if (connectCard && localStorage.getItem('gomsn_fcm_token_registered') === '1') {
                connectCard.remove();
            }
            window.initGomsnFcmToken(false);
            return;
        }

        // If permission not yet granted, prompt dialog
        if (localStorage.getItem('gomsn_fcm_token_registered') === '1') return;

        function doPrompt() {
            if (window.Swal) {
                Swal.fire({
                    title: 'Aktifkan Notifikasi Portal?',
                    text: 'Dapatkan pemberitahuan seketika di HP saat pengajuan kasbon/reimbursement disetujui, lembur dikonfirmasi, & tiket ditugaskan.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#155E63',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: '🔔 Ya, Sambungkan Notifikasi',
                    cancelButtonText: 'Nanti Saja',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.initGomsnFcmToken(true);
                    }
                });
            } else {
                setTimeout(doPrompt, 300);
            }
        }

        setTimeout(doPrompt, 600);
    }

    if (document.readyState === 'complete') {
        checkInitialState();
    } else {
        window.addEventListener('load', checkInitialState);
    }
})();
</script>
