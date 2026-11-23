<!-- Firebase Cloud Messaging Client Integration -->
<!-- Floating Connect Notification Card -->
<div id="gomsn-notif-prompt-banner" style="display: none;" class="fixed top-4 left-1/2 -translate-x-1/2 z-[99998] w-[94%] max-w-md bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-2 border-teal-500/60 rounded-2xl shadow-2xl p-4 transition-all duration-300">
    <div class="flex items-start gap-3.5">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-500/20 to-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0 border border-teal-500/30">
            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">Sambungkan Notifikasi HP</h4>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-snug mt-1">
                Daftarkan HP ini ke database server agar menerima notifikasi tiket, lembur, &amp; persetujuan.
            </p>
            <div class="mt-3 flex items-center gap-2">
                <button type="button" onclick="window.initGomsnFcmToken(true)" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#155E63] to-[#0d9488] hover:from-[#114b4f] hover:to-[#0f766e] text-white text-xs font-bold shadow-md hover:shadow-lg transition cursor-pointer flex items-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Sambungkan Sekarang</span>
                </button>
                <button type="button" onclick="window.dismissNotificationPrompt()" class="px-3 py-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-xs font-medium transition cursor-pointer">
                    Nanti
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
<script>
(function() {
    'use strict';

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

    // Auto-update service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(function(reg) {
            if (reg) reg.update().catch(function() {});
        }).catch(function() {});
    }

    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
        return;
    }

    // Initialize Firebase
    if (!firebase.apps.length) {
        try {
            firebase.initializeApp(firebaseConfig);
        } catch (e) {
            console.error('[FCM] Initialization error:', e);
            return;
        }
    }

    if (!firebase.messaging.isSupported()) {
        console.warn('[FCM] Firebase messaging is not supported in this environment (requires HTTPS or localhost).');
        return;
    }

    const messaging = firebase.messaging();

    // Foreground message listener
    messaging.onMessage(function(payload) {
        console.log('[FCM] Foreground notification received:', payload);
        const title = payload.notification?.title || payload.data?.title || 'Notifikasi Baru';
        const body = payload.notification?.body || payload.data?.body || '';
        const deepLink = payload.data?.deep_link || payload.data?.click_action || '';

        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: title,
                text: body,
                showConfirmButton: !!deepLink,
                confirmButtonText: 'Buka',
                confirmButtonColor: '#155E63',
                showCancelButton: !!deepLink,
                cancelButtonText: 'Tutup',
                timer: deepLink ? 8000 : 5000,
                timerProgressBar: true,
                customClass: {
                    popup: 'cursor-pointer'
                }
            }).then(function(result) {
                if (result.isConfirmed && deepLink) {
                    window.location.href = deepLink;
                }
            });
        } else if (window.triggerToast) {
            window.triggerToast('info', title + (body ? ': ' + body : ''));
        }
    });

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
                    platform: 'web',
                    device_name: (navigator.userAgentData?.platform || navigator.platform || 'Browser') + ' (' + (navigator.userAgent.includes('Mobile') ? 'Mobile' : 'Desktop') + ')'
                })
            });

            const data = await response.json().catch(function() { return {}; });

            if (response.ok && data.success) {
                localStorage.setItem('gomsn_fcm_token_registered', '1');
                console.log('[FCM] Device token successfully registered to backend!');
                
                const banner = document.getElementById('gomsn-notif-prompt-banner');
                if (banner) banner.remove();

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
        if (!('Notification' in window)) {
            if (interactive && window.Swal) Swal.fire('Error', 'Browser Anda tidak mendukung notifikasi.', 'error');
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
            const swReg = await navigator.serviceWorker.ready;
            const tokenOptions = {
                serviceWorkerRegistration: swReg
            };

            if (vapidKey && vapidKey.trim() !== '') {
                tokenOptions.vapidKey = vapidKey.trim();
            } else {
                console.warn('[FCM] vapidKey is empty!');
                if (interactive && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'VAPID Key Belum Terbaca',
                        text: 'FCM_VAPID_KEY belum terbaca di server. Pastikan sudah ada di .env server lalu jalankan: php artisan config:clear',
                        confirmButtonColor: '#155E63'
                    });
                    return;
                }
            }

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

    window.requestNotificationPermission = function() {
        window.initGomsnFcmToken(true);
    };

    window.dismissNotificationPrompt = function() {
        const banner = document.getElementById('gomsn-notif-prompt-banner');
        if (banner) banner.remove();
        sessionStorage.setItem('gomsn_notif_prompt_dismissed', '1');
    };

    // Auto-prompt modal upon entering Dashboard / Portal
    function checkAndPromptPermission() {
        if (!('Notification' in window)) return;

        const isRegistered = localStorage.getItem('gomsn_fcm_token_registered') === '1';

        // If not registered in database yet:
        if (!isRegistered) {
            // If user already granted permission in phone, try silent register immediately
            if (Notification.permission === 'granted') {
                window.initGomsnFcmToken(false).then(() => {
                    // If still not registered after silent attempt, pop up the prominent connect dialog
                    if (localStorage.getItem('gomsn_fcm_token_registered') !== '1') {
                        promptDashboardDialog();
                    }
                });
            } else {
                // Not yet granted: pop up dashboard dialog immediately
                setTimeout(promptDashboardDialog, 1000);
            }
        }
    }

    function promptDashboardDialog() {
        if (sessionStorage.getItem('gomsn_notif_modal_dismissed')) return;

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
                } else {
                    sessionStorage.setItem('gomsn_notif_modal_dismissed', '1');
                    const banner = document.getElementById('gomsn-notif-prompt-banner');
                    if (banner) banner.style.display = 'block';
                }
            });
        } else {
            const banner = document.getElementById('gomsn-notif-prompt-banner');
            if (banner) banner.style.display = 'block';
        }
    }

    if (document.readyState === 'complete') {
        checkAndPromptPermission();
    } else {
        window.addEventListener('load', checkAndPromptPermission);
    }
})();
</script>
