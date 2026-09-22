/* Routes every request under <scope>run/ to the PHP interpreter in the page. */
const SCOPE = new URL('./', self.location.href).pathname;
const RUN = SCOPE + 'run/';

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(self.clients.claim()));
self.addEventListener('message', (e) => { if (e.data && e.data.type === 'claim') self.clients.claim(); });

async function ask(request) {
  const all = await self.clients.matchAll({ includeUncontrolled: true, type: 'window' });
  const host = all.find((c) => !c.url.startsWith(self.location.origin + RUN)) || all[0];
  if (!host) throw new Error('the editor tab is gone - reload the page');

  const headers = {};
  for (const [k, v] of request.headers.entries()) headers[k] = v;
  const body = request.method === 'GET' || request.method === 'HEAD' ? null : await request.arrayBuffer();

  return await new Promise((resolve, reject) => {
    const ch = new MessageChannel();
    const timer = setTimeout(() => reject(new Error('PHP did not answer in time')), 180000);
    ch.port1.onmessage = (ev) => { clearTimeout(timer); resolve(ev.data); };
    host.postMessage(
      { type: 'php-request', request: { url: request.url, method: request.method, headers, body } },
      [ch.port2, ...(body ? [body] : [])]
    );
  });
}

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin || !url.pathname.startsWith(RUN)) return;

  event.respondWith((async () => {
    try {
      const r = await ask(event.request);
      const h = new Headers();
      for (const [k, list] of Object.entries(r.headers || {})) {
        for (const v of [].concat(list)) h.append(k, v);
      }
      return new Response(r.bytes, { status: r.status, headers: h });
    } catch (e) {
      return new Response('The editor is not running: ' + (e && e.message ? e.message : e) +
        '\n\nReload the page to start it again.',
        { status: 503, headers: { 'content-type': 'text/plain; charset=utf-8' } });
    }
  })());
});
