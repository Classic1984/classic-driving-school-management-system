const OFFLINE_URL = '/offline.html';
const CACHE_NAME = 'cdsms-shell-v1';
const PRECACHE_URLS = [OFFLINE_URL, '/images/logo.png', '/favicon.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

// Only page navigations are intercepted, so a network failure can fall back
// to the offline page. CDSMS is a live, database-backed dashboard - caching
// the JS/CSS bundle or any API/page response here would risk serving a
// stale bundle or stale data after the next deploy, so nothing else is
// cached or intercepted.
self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});
