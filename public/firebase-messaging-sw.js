// Firebase Cloud Messaging Service Worker for PT MSN Portal
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyA668mrHa1p0uumFLmK-b5Hmjw9AC2zHzk",
    authDomain: "portal-gomsn.firebaseapp.com",
    projectId: "portal-gomsn",
    storageBucket: "portal-gomsn.firebasestorage.app",
    messagingSenderId: "504216298607",
    appId: "1:504216298607:web:33f6314ca55837fae5999a",
    measurementId: "G-2FS9BQQ087"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Background message received:', payload);
    const title = payload.notification?.title || payload.data?.title || 'Portal MSN';
    const body = payload.notification?.body || payload.data?.body || 'Anda menerima notifikasi baru.';
    const deepLink = payload.data?.deep_link || payload.data?.click_action || '/';

    const notificationOptions = {
        body: body,
        icon: '/assets/logo/icon-192.png',
        badge: '/assets/logo/favicon-32x32.png',
        vibrate: [100, 50, 100],
        data: {
            deep_link: deepLink,
            ...(payload.data || {})
        },
        requireInteraction: true
    };

    return self.registration.showNotification(title, notificationOptions);
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const deepLink = event.notification.data?.deep_link || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    client.focus();
                    return client.navigate(deepLink);
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(deepLink);
            }
        })
    );
});
