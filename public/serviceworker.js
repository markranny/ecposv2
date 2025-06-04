// public/service-worker.js
import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { StaleWhileRevalidate } from 'workbox-strategies';

precacheAndRoute(self.__WB_MANIFEST);

registerRoute(
  ({url}) => url.origin === 'https://fonts.googleapis.com',
  new StaleWhileRevalidate({
    cacheName: 'google-fonts-stylesheets',
  })
);

registerRoute(
  ({url}) => url.origin === 'https://fonts.gstatic.com',
  new StaleWhileRevalidate({
    cacheName: 'google-fonts-webfonts',
  })
);

registerRoute(
  ({url}) => url.origin === 'https://lottie.host',
  new StaleWhileRevalidate({
    cacheName: 'lottie-animations',
  })
);

registerRoute(
  ({request}) => request.destination === 'image' ||
                 request.destination === 'script' ||
                 request.destination === 'style',
  new StaleWhileRevalidate({
    cacheName: 'static-resources',
  })
);