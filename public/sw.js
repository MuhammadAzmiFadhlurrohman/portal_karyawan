// PT MSN Portal - Unified PWA & Background Web Push Service Worker (v9)
const CACHE_NAME = 'msn-portal-v9-shell';
const OFFLINE_ASSETS = [
    '/',
    '/login',
    '/manifest.webmanifest?v=2026112308',
    '/assets/logo/icon-192.png',
    '/assets/logo/favicon-32x32.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(OFFLINE_ASSETS))
            .catch(() => {})
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
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
// Background & Lock-Screen Web Push Handler
// ==========================================
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
    const tag = d.tag || n.tag || ((d.type || 'gomsn') + '-' + (d.reference_id || Date.now()));
    const timestamp = d.timestamp ? Number(d.timestamp) : Date.now();

    const options = {
        body: body,
        icon: d.icon || n.icon || '/assets/logo/icon-192.png',
        badge: d.badge || n.badge || '/assets/logo/favicon-32x32.png',
        vibrate: [200, 100, 200],
        tag: tag,
        renotify: true,
        silent: false,
        timestamp: Number.isFinite(timestamp) ? timestamp : Date.now(),
        data: {
            deep_link: deepLink,
            ...d
        }
    };

    // Always fulfill userVisibleOnly: true contract so Chrome/Android/iOS never throttle background pushes
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const rawDeepLink = event.notification.data?.deep_link || '/';
    const targetUrl = new URL(rawDeepLink, self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                    return client.focus().then((focused) => {
                        if (focused && 'navigate' in focused) {
                            return focused.navigate(targetUrl).catch(() => focused);
                        }
                        return focused;
                    });
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
