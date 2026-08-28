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

// The payload is always a small { title, body, url } JSON object built by
// WebPushService - nothing about it is optional, but a malformed or
// missing payload still shouldn't crash the event (some push services
// deliver an empty "wake up and check" push with no data at all).
self.addEventListener('push', (event) => {
    let data = { title: 'Classic Driving School', body: '' };

    try {
        if (event.data) {
            data = { ...data, ...event.data.json() };
        }
    } catch (e) {
        // Not JSON - fall back to the default above.
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/images/pwa/icon-192.png',
            badge: '/images/pwa/icon-192.png',
            data: { url: data.url || '/' },
        })
    );
});

// Focuses an already-open CDSMS tab rather than opening a duplicate one,
// falling back to opening a new tab only if none is open.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data && event.notification.data.url ? event.notification.data.url : '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});
