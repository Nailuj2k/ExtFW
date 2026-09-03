// PWA Service Worker — habilita "Añadir a pantalla de inicio"
// Sin caché offline: la app funciona solo con conexión (comportamiento normal)

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(clients.claim()));

// Sin fetch handler: todas las peticiones van a la red normalmente
