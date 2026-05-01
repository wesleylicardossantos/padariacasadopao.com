self.addEventListener('install', event => {
  event.waitUntil(caches.open('pdv-mobile-v1').then(cache => cache.addAll([
    '/mobile-pdv/index.html',
    '/mobile-pdv/manifest.json'
  ])));
});

self.addEventListener('fetch', event => {
  event.respondWith(caches.match(event.request).then(response => response || fetch(event.request)));
});
