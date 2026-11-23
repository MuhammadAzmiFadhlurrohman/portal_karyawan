<!-- Firebase Cloud Messaging Client Integration -->
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

    if (!('Notification' in window)) {
        console.warn('[FCM] Browser does not support Notification API.');
        return;
    }

    if (!('serviceWorker' in navigator)) {
        console.warn('[FCM] Browser does not support Service Worker.');
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
                console.log('[FCM] Notification permission is currently:', permission);
                return;
            }
        }

        if (permission !== 'granted') {
            if (interactive && window.Swal) {
                Swal.fire('Izin Ditolak', 'Izin notifikasi belum diaktifkan di browser. Silakan izinkan di pengaturan situs.', 'warning');
            }
            return;
        }

        try {
            // Wait for unified service worker ready
            const swReg = await navigator.serviceWorker.ready;

            const tokenOptions = {
                serviceWorkerRegistration: swReg
            };

            if (vapidKey && vapidKey.trim() !== '') {
                tokenOptions.vapidKey = vapidKey.trim();
            } else {
                console.warn('[FCM] FCM_VAPID_KEY is not defined in .env! Make sure FCM_VAPID_KEY is saved.');
            }

            const token = await messaging.getToken(tokenOptions);
            if (token) {
                console.log('[FCM] FCM Token obtained:', token.substring(0, 16) + '...');
                const success = await sendTokenToBackend(token);
                if (interactive && window.Swal) {
                    Swal.fire('Berhasil', 'Notifikasi berhasil diaktifkan di perangkat ini!', 'success');
                }
            } else {
                console.warn('[FCM] No token returned by Firebase.');
                if (interactive && window.Swal) {
                    Swal.fire('Perhatian', 'Tidak berhasil mendapatkan token dari Firebase.', 'warning');
                }
            }
        } catch (err) {
            console.error('[FCM] Error generating FCM token:', err);
            if (interactive && window.Swal) {
                Swal.fire('Error FCM', err.message || 'Gagal membuat token notifikasi.', 'error');
            }
        }
    };

    // Auto-run if permission is already granted
    if (Notification.permission === 'granted') {
        if (document.readyState === 'complete') {
            window.initGomsnFcmToken();
        } else {
            window.addEventListener('load', function() {
                window.initGomsnFcmToken();
            });
        }
    }
})();
</script>
