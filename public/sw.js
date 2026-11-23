// PT MSN Portal - Unified PWA & Firebase Cloud Messaging Service Worker
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

const CACHE_NAME = 'msn-portal-v2-shell';
const OFFLINE_ASSETS = [
    '/',
    '/login',
    '/manifest.webmanifest'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(OFFLINE_ASSETS)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});

// ==========================================
// Firebase Cloud Messaging (FCM) Setup
// ==========================================
const firebaseConfig = {
    apiKey: "AIzaSyA668mrHa1p0uumFLmK-b5Hmjw9AC2zHzk",
    authDomain: "portal-gomsn.firebaseapp.com",
    projectId: "portal-gomsn",
    storageBucket: "portal-gomsn.firebasestorage.app",
    messagingSenderId: "504216298607",
    appId: "1:504216298607:web:33f6314ca55837fae5999a",
    measurementId: "G-2FS9BQQ087"
};

try {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
        console.log('[sw.js] Firebase background message:', payload);
        const title = payload.notification?.title || payload.data?.title || 'Portal MSN';
        const body = payload.notification?.body || payload.data?.body || 'Anda menerima notifikasi baru.';
        const deepLink = payload.data?.deep_link || payload.data?.click_action || '/';

        const options = {
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

        return self.registration.showNotification(title, options);
    });
} catch (e) {
    console.warn('[sw.js] Firebase background initialization error:', e);
}

// Fallback native push event listener
self.addEventListener('push', (event) => {
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { notification: { title: 'Portal MSN', body: event.data.text() } };
        }
    }

    const title = data.notification?.title || data.data?.title || 'Portal MSN';
    const body = data.notification?.body || data.data?.body || 'Anda menerima notifikasi baru.';
    const deepLink = data.data?.deep_link || data.data?.click_action || '/';

    const options = {
        body: body,
        icon: '/assets/logo/icon-192.png',
        badge: '/assets/logo/favicon-32x32.png',
        vibrate: [100, 50, 100],
        data: {
            deep_link: deepLink,
            ...(data.data || {})
        },
        requireInteraction: true
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const deepLink = event.notification.data?.deep_link || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
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
