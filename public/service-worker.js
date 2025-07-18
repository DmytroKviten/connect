const CACHE = 'demo-v1';
const ASSETS = [
  '/demo',                    // сама сторінка
  '/manifest.webmanifest',    // маніфест
  // якщо на demo є власний css / js файл – додай сюди
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(ASSETS)));
});

self.addEventListener('fetch', e => {
  // нічого не кешуємо, крім ASSETS
  e.respondWith(
    caches.match(e.request).then(r => r || fetch(e.request))
  );
});

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // ➊ ніколи не кешувати API-запити
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(fetch(event.request));
    return;
  }

  // ➋ решта – як було
  event.respondWith(
    caches.match(event.request).then(r => r || fetch(event.request))
  );
});