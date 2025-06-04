/* var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/',
    '/offline.html',
    '/build/assets/app.css',
    '/build/assets/app.js',
    '/login',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});


 */

const staticCacheName = "pwa-v1";

const filesToCache = [
    '/',
    '/offline.html',
    /* '/build/assets/app.css',
    '/build/assets/app.js', */
    '/css/app.css',
    '/app/app.js',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
  event.waitUntil(
      caches.open(staticCacheName)
          .then(cache => {
              return Promise.all(
                  filesToCache.map(url => {
                      return cache.add(url).catch(error => {
                          console.error('Failed to cache:', url, error);
                          return Promise.resolve(); // Continue with other files
                      });
                  })
              );
          })
  );
});

// Clear cache on activate
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName.startsWith("pwa-") && cacheName !== staticCacheName) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request)
                    .catch(() => {
                        return caches.match('/offline.html');
                    });
            })
    );
});