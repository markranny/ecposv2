// resources/js/service-worker.js
import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { StaleWhileRevalidate } from 'workbox-strategies';

precacheAndRoute(self.__WB_MANIFEST);

// Cache the Google Fonts stylesheets with a stale-while-revalidate strategy.
registerRoute(
  ({url}) => url.origin === 'https://fonts.googleapis.com',
  new StaleWhileRevalidate({
    cacheName: 'google-fonts-stylesheets',
  })
);

// Cache the underlying font files with a cache-first strategy for 1 year.
registerRoute(
  ({url}) => url.origin === 'https://fonts.gstatic.com',
  new StaleWhileRevalidate({
    cacheName: 'google-fonts-webfonts',
  })
);

// Cache Lottie animations
registerRoute(
  ({url}) => url.origin === 'https://lottie.host',
  new StaleWhileRevalidate({
    cacheName: 'lottie-animations',
  })
);

// Default cache strategy for other resources
registerRoute(
  ({request}) => request.destination === 'image' ||
                 request.destination === 'script' ||
                 request.destination === 'style',
  new StaleWhileRevalidate({
    cacheName: 'static-resources',
  })
);