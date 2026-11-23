// PT MSN Portal - Firebase Messaging Service Worker Alias (v5)
// Intentionally delegates to unified sw.js without duplicate listeners
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', () => self.clients.claim());
