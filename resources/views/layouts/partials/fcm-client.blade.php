<!-- Firebase Cloud Messaging Client Integration -->
<div id="gomsn-notif-prompt-banner" style="display: none;" class="fixed top-4 left-1/2 -translate-x-1/2 z-[99998] w-[92%] max-w-lg bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-teal-500/40 rounded-2xl shadow-2xl p-4 transition-all duration-300">
    <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Aktifkan Notifikasi Portal</h4>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-snug">Izinkan notifikasi agar langsung menerima pembaruan tiket, lembur, &amp; persetujuan pengajuan.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="window.requestNotificationPermission()" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#155E63] to-[#0d9488] hover:from-[#114b4f] hover:to-[#0f766e] text-white text-xs font-bold shadow-md hover:shadow transition cursor-pointer">
                Izinkan
            </button>
            <button type="button" onclick="window.dismissNotificationPrompt()" class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 flex items-center justify-center transition cursor-pointer" title="Nanti">
                ✕
            </button>
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
    async function sendTokenToBackend(token) {
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

            if (response.ok) {
                localStorage.setItem('gomsn_fcm_token', token);
                console.log('[FCM] Device token successfully registered to backend!');
                return true;
            } else {
                console.warn('[FCM] Backend rejected token registration:', response.status);
                return false;
            }
        } catch (netErr) {
            console.error('[FCM] Failed to send token to backend:', netErr);
            return false;
        }
    }

    // Function to retrieve FCM Token and store to backend
    window.initGomsnFcmToken = async function(interactive = false) {
        if (!('Notification' in window)) {
            if (interactive && window.Swal) Swal.fire('Error', 'Browser Anda tidak mendukung notifikasi.', 'error');
            return;
        }

        let permission = Notification.permission;
        if (permission !== 'granted') {
            if (interactive) {
                permission = await Notification.requestPermission();
            } else {
                return;
            }
        }

        if (permission !== 'granted') {
            if (interactive && window.Swal) {
                Swal.fire('Izin Ditolak', 'Izin notifikasi belum diaktifkan di browser. Silakan izinkan di pengaturan situs browser Anda.', 'warning');
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
            }

            const token = await messaging.getToken(tokenOptions);
            if (token) {
                console.log('[FCM] FCM Token obtained:', token.substring(0, 16) + '...');
                await sendTokenToBackend(token);
                const banner = document.getElementById('gomsn-notif-prompt-banner');
                if (banner) banner.remove();

                if (interactive && window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Notifikasi Aktif',
                        text: 'Perangkat Anda siap menerima notifikasi portal.',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                }
            }
        } catch (err) {
            console.error('[FCM] Error generating FCM token:', err);
            if (interactive && window.Swal) {
                Swal.fire('Error FCM', err.message || 'Gagal membuat token notifikasi.', 'error');
            }
        }
    };

    // User-triggered permission request
    window.requestNotificationPermission = async function() {
        if (!('Notification' in window)) return;
        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                const banner = document.getElementById('gomsn-notif-prompt-banner');
                if (banner) banner.remove();
                await window.initGomsnFcmToken(true);
            } else if (permission === 'denied') {
                const banner = document.getElementById('gomsn-notif-prompt-banner');
                if (banner) banner.remove();
            }
        } catch (e) {
            console.warn('[FCM] requestPermission error:', e);
        }
    };

    window.dismissNotificationPrompt = function() {
        const banner = document.getElementById('gomsn-notif-prompt-banner');
        if (banner) banner.remove();
        sessionStorage.setItem('gomsn_notif_prompt_dismissed', '1');
    };

    // Auto-prompt logic if permission is not yet granted
    function checkAndPromptPermission() {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            window.initGomsnFcmToken();
        } else if (Notification.permission === 'default') {
            // Not yet decided: automatically prompt browser if supported, and show prompt banner
            if (!sessionStorage.getItem('gomsn_notif_prompt_dismissed')) {
                const banner = document.getElementById('gomsn-notif-prompt-banner');
                if (banner) {
                    setTimeout(() => {
                        banner.style.display = 'block';
                    }, 1200);
                }
            }

            // Also auto-trigger native browser prompt on first click anywhere on the page
            const autoPromptOnce = async function() {
                document.removeEventListener('click', autoPromptOnce);
                if (Notification.permission === 'default') {
                    try {
                        const perm = await Notification.requestPermission();
                        if (perm === 'granted') {
                            const banner = document.getElementById('gomsn-notif-prompt-banner');
                            if (banner) banner.remove();
                            window.initGomsnFcmToken(true);
                        }
                    } catch (err) {}
                }
            };
            document.addEventListener('click', autoPromptOnce, { once: true });
        }
    }

    if (document.readyState === 'complete') {
        checkAndPromptPermission();
    } else {
        window.addEventListener('load', checkAndPromptPermission);
    }
})();
</script>
