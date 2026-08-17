const CACHE = 'fenroy-v2';
const OFFLINE_URL = '/';

// Cache shell assets on install
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll([
            '/',
            '/images/fenroy-logo.png',
            '/images/favicon.png',
        ])).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Network-first for navigation, cache-first for assets
self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);

    // Only handle http/https — chrome-extension://, data:, blob: etc. are unsupported by Cache API
    if (!['http:', 'https:'].includes(url.protocol)) return;

    // Skip non-GET, admin, api, livewire
    if (e.request.method !== 'GET') return;
    if (['/store-portal', '/livewire', '/api'].some(p => url.pathname.startsWith(p))) return;

    if (e.request.mode === 'navigate') {
        e.respondWith(
            fetch(e.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Cache-first for static assets
    if (url.pathname.match(/\.(png|jpg|jpeg|webp|svg|ico|woff2?|css|js)$/)) {
        e.respondWith(
            caches.match(e.request).then(cached =>
                cached || fetch(e.request).then(res => {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                    return res;
                })
            )
        );
    }
});
