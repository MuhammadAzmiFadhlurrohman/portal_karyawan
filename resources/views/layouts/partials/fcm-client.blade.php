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
    const isSecureOrigin = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

    // Auto-update service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(function(reg) {
            if (reg) reg.update().catch(function() {});
        }).catch(function() {});
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

    // Foreground message listener
    async function setupForegroundListener() {
        await ensureFirebaseLoaded();
        initFirebaseApp();

        try {
            if (typeof firebase !== 'undefined' && firebase.messaging) {
                const messaging = firebase.messaging();
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
            }
        } catch (e) {
            console.warn('[FCM] onMessage setup error:', e);
        }
    }
    setupForegroundListener();

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

        // Wait for Firebase SDK to load
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

    // Trigger Dashboard popup dialog
    window.promptDashboardDialog = function() {
        if (!isSecureOrigin) return;
        if (!('Notification' in window)) return;
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
    };

    // Auto-prompt upon loading
    if (document.readyState === 'complete') {
        window.promptDashboardDialog();
    } else {
        window.addEventListener('load', window.promptDashboardDialog);
    }
})();
</script>
