// PT MSN Portal - Unified PWA & Web Push Service Worker (v6 - Clean White Splash & No Extra Intro)
const CACHE_NAME = 'msn-portal-v6-shell';
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
    // Never cache sw.js or firebase-messaging-sw.js
    if (event.request.url.includes('sw.js')) return;
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});

// ==========================================
// Single Web Push Notification Handler
// ==========================================
let lastNotifSignature = '';
let lastNotifTimestamp = 0;

self.addEventListener('push', (event) => {
    let raw = {};
    if (event.data) {
        try {
            raw = event.data.json();
        } catch (e) {
            raw = { data: { title: 'Portal MSN', body: event.data.text() } };
        }
    }

    const d = raw.data || {};
    const n = raw.notification || {};

    const title = d.title || n.title || 'Portal MSN';
    const body = d.body || n.body || 'Anda menerima pembaruan baru.';
    const deepLink = d.deep_link || d.click_action || n.click_action || '/';
    const tag = d.tag || n.tag || ((d.type || 'gomsn') + '-' + (d.reference_id || 'notif'));

    // Deduplicate identical messages arriving within 8 seconds
    const now = Date.now();
    const signature = `${tag}|${title}|${body}`;
    if (lastNotifSignature === signature && (now - lastNotifTimestamp) < 8000) {
        return;
    }
    lastNotifSignature = signature;
    lastNotifTimestamp = now;

    const options = {
        body: body,
        icon: d.icon || n.icon || '/assets/logo/icon-192.png',
        badge: d.badge || n.badge || '/assets/logo/favicon-32x32.png',
        vibrate: [100, 50, 100],
        tag: tag,
        renotify: false,
        data: {
            deep_link: deepLink,
            ...d
        }
    };

    event.waitUntil(
        self.registration.getNotifications({ tag: tag }).then((existing) => {
            existing.forEach((notification) => notification.close());
            return self.registration.showNotification(title, options);
        })
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
