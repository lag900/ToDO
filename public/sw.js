const CACHE_NAME = 'batucore-v1';
const OFFLINE_URL = '/offline.html';

// Assets that should be cached with Stale-while-revalidate
const ASSET_EXTENSIONS = [
    '.js', '.css', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.ico'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([OFFLINE_URL]);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // 1. Skip caching for non-GET requests
    if (request.method !== 'GET') return;

    // 2. Skip caching for API, Auth, and Inertia Requests
    const isInertia = request.headers.get('X-Inertia');
    const isApi = url.pathname.startsWith('/api/');
    const isAuth = url.pathname.startsWith('/login') || 
                   url.pathname.startsWith('/logout') || 
                   url.pathname.startsWith('/auth/google');
    
    if (isInertia || isApi || isAuth || url.hostname !== self.location.hostname) {
        return;
    }

    // 3. Handle Static Assets (Stale-while-revalidate)
    const isStaticAsset = ASSET_EXTENSIONS.some(ext => url.pathname.endsWith(ext)) || url.pathname.includes('/build/assets/');

    if (isStaticAsset) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) => {
                return cache.match(request).then((cachedResponse) => {
                    const fetchPromise = fetch(request).then((networkResponse) => {
                        cache.put(request, networkResponse.clone());
                        return networkResponse;
                    });
                    return cachedResponse || fetchPromise;
                });
            })
        );
        return;
    }

    // 4. Handle Navigation Requests (Network First, fallback to offline)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
    }
});
