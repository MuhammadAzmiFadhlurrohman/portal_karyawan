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

    // Initialize Firebase if not already initialized
    if (!firebase.apps.length) {
        try {
            firebase.initializeApp(firebaseConfig);
        } catch (e) {
            console.error('[FCM] Initialization error:', e);
            return;
        }
    }

    if (!firebase.messaging.isSupported()) {
        console.warn('[FCM] Firebase messaging is not supported in this browser.');
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

    // Function to retrieve FCM Token and store to backend
    window.initGomsnFcmToken = async function() {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'granted') return;

        try {
            // Register or wait for service worker
            let swReg = null;
            if ('serviceWorker' in navigator) {
                swReg = await navigator.serviceWorker.ready.catch(function() { return null; });
            }

            const tokenOptions = {};
            if (swReg) {
                tokenOptions.serviceWorkerRegistration = swReg;
            }
            if (vapidKey && vapidKey.trim() !== '') {
                tokenOptions.vapidKey = vapidKey.trim();
            }

            const token = await messaging.getToken(tokenOptions);
            if (token) {
                console.log('[FCM] Token active:', token.substring(0, 16) + '...');
                const prevToken = localStorage.getItem('gomsn_fcm_token');
                
                // Only post to backend if token changed or not yet stored
                if (prevToken !== token) {
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
                        console.log('[FCM] Token successfully registered to backend.');
                    }
                }
            }
        } catch (err) {
            console.warn('[FCM] Failed to obtain token:', err);
        }
    };

    // Auto-initialize if notification permission is already granted
    if ('Notification' in window && Notification.permission === 'granted') {
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
