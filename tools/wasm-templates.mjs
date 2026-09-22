/* Sources emitted into docs/app/ by tools/build-wasm.mjs. Kept here so the
   build script stays readable; `__CHUNKS__` and `__PHP_LOADER__` are filled in. */

export const BOOT = String.raw`
import { PHP, PHPRequestHandler, loadPHPRuntime, setPhpIniEntries } from '@php-wasm/universal';
import * as phpLoaderModule from __PHP_LOADER__;

const CHUNKS = __CHUNKS__;
const base = new URL('./', location.href);
const runBase = new URL('run/', base).href;

const el = (id) => document.getElementById(id);
const ui = {
  status: (t) => { const e = el('status'); if (e) e.textContent = t; },
  detail: (t) => { const e = el('detail'); if (e) e.textContent = t; },
  bar: (p) => { const e = el('bar'); if (e) e.style.width = Math.round(p * 100) + '%'; },
  done: () => { const e = el('loader'); if (e) e.hidden = true; },
  fail: (msg) => {
    ui.status('Could not start');
    ui.detail(msg);
    const e = el('bar'); if (e) e.style.background = '#ff5c7a';
  },
};

async function fetchRuntime() {
  const parts = [];
  let got = 0;
  for (let i = 0; i < CHUNKS; i++) {
    const r = await fetch(new URL('runtime/php.wasm.' + i, base));
    if (!r.ok) throw new Error('PHP runtime part ' + i + ': HTTP ' + r.status);
    const buf = new Uint8Array(await r.arrayBuffer());
    parts.push(buf);
    got += buf.length;
    ui.bar(0.05 + 0.6 * ((i + 1) / CHUNKS));
    ui.detail('PHP interpreter — ' + (got / 1048576).toFixed(1) + ' MB');
  }
  const all = new Uint8Array(got);
  let at = 0;
  for (const p of parts) { all.set(p, at); at += p.length; }
  return all;
}

async function unpackEditor(php) {
  ui.detail('unpacking the editor');
  const pack = await (await fetch(new URL('editor.json', base))).json();
  await php.mkdir('/app');
  const paths = Object.keys(pack);
  let n = 0;
  for (const rel of paths) {
    const slash = rel.lastIndexOf('/');
    if (slash > 0) await php.mkdir('/app/' + rel.slice(0, slash));
    const bin = atob(pack[rel]);
    const buf = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
    php.writeFile('/app/' + rel, buf);
    if (++n % 16 === 0) ui.bar(0.65 + 0.32 * (n / paths.length));
  }
  await php.mkdir('/app/work');
  return paths.length;
}

let handler = null;
let busy = false;

async function serve(req) {
  const r = await handler.request({
    url: req.url,
    method: req.method,
    headers: req.headers,
    body: req.body ? new Uint8Array(req.body) : undefined,
  });
  return { status: r.httpStatusCode, headers: r.headers, bytes: r.bytes };
}

async function main() {
  if (!('serviceWorker' in navigator)) {
    ui.fail('This browser has no service workers, so PHP cannot serve pages here. ' +
            'Private windows in some browsers switch them off.');
    return;
  }

  ui.detail('starting the service worker');
  ui.bar(0.03);
  const reg = await navigator.serviceWorker.register(new URL('sw.js', base), { scope: base.pathname });
  await navigator.serviceWorker.ready;
  if (reg.active && !navigator.serviceWorker.controller) {
    reg.active.postMessage({ type: 'claim' });
    await new Promise((res) => {
      if (navigator.serviceWorker.controller) return res();
      navigator.serviceWorker.addEventListener('controllerchange', res, { once: true });
    });
  }

  navigator.serviceWorker.addEventListener('message', async (event) => {
    const msg = event.data;
    if (!msg || msg.type !== 'php-request') return;
    const port = event.ports[0];
    busy = true;
    try {
      port.postMessage(await serve(msg.request));
    } catch (e) {
      port.postMessage({
        status: 500,
        headers: { 'content-type': ['text/plain; charset=utf-8'] },
        bytes: new TextEncoder().encode('PHP error: ' + (e && e.message ? e.message : String(e))),
      });
    } finally { busy = false; }
  });

  const wasmBinary = await fetchRuntime();
  ui.status('Starting PHP');
  ui.bar(0.66);

  const php = new PHP(await loadPHPRuntime(phpLoaderModule, { wasmBinary }));
  await setPhpIniEntries(php, {
    memory_limit: '1G',
    upload_max_filesize: '256M',
    post_max_size: '256M',
    max_execution_time: '0',
  });

  const files = await unpackEditor(php);
  handler = new PHPRequestHandler({ php, documentRoot: '/app', absoluteUrl: runBase });

  const v = await php.run({ code: '<?php echo PHP_VERSION;' });
  window.__phpReady = { files, php: v.text };

  ui.bar(1);
  ui.done();
  el('app').src = runBase + 'index.php';

  /* Everything lives in this tab: nothing is uploaded anywhere, and nothing survives a reload. */
  let touched = false;
  window.addEventListener('message', () => { touched = true; });
  const mark = setInterval(() => {
    const f = el('app');
    try { if (f.contentDocument && /\.gdb|\.gst/i.test(f.contentDocument.body.innerText)) touched = true; }
    catch (e) { /* ignore */ }
  }, 4000);
  window.addEventListener('beforeunload', (e) => {
    if (!touched && !busy) return;
    e.preventDefault();
    e.returnValue = '';
  });
  window.addEventListener('unload', () => clearInterval(mark));
}

main().catch((e) => {
  console.error(e);
  ui.fail(e && e.message ? e.message : String(e));
});
`;

export const SW = String.raw`/* Routes every request under <scope>run/ to the PHP interpreter in the page. */
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
`;

export const SHELL = String.raw`<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="dark light">
<title>SP2 DB Editor - in your browser</title>
<meta name="description" content="The SuperPower 2 database editor running entirely in your browser: PHP compiled to WebAssembly, no server, no upload.">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Crect width='16' height='16' rx='3' fill='%234f8cff'/%3E%3Cg fill='%23fff'%3E%3Crect x='3' y='4' width='10' height='2' rx='.6'/%3E%3Crect x='3' y='7' width='10' height='2' rx='.6'/%3E%3Crect x='3' y='10' width='6' height='2' rx='.6'/%3E%3C/g%3E%3C/svg%3E">
<style>
  :root{--bg:#12151b;--fg:#d8dee9;--bd:#262c38;--p1:#171b23;--mu:#8a93a6;--ac:#4f8cff}
  *,*::before,*::after{box-sizing:border-box}
  html,body{height:100%}
  body{margin:0;background:var(--bg);color:var(--fg);overflow:hidden;
    font:15px/1.6 system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",sans-serif;
    display:flex;flex-direction:column}
  #loader{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:16px;padding:24px;text-align:center}
  #loader h1{font-size:1.4rem;margin:0;font-weight:650;letter-spacing:-.02em}
  #detail{color:var(--mu);font-size:.9rem;min-height:1.4em;margin:0}
  .track{width:min(420px,82vw);height:6px;background:var(--p1);border:1px solid var(--bd);
    border-radius:99px;overflow:hidden}
  #bar{height:100%;width:0;background:var(--ac);transition:width .25s ease}
  .hint{color:var(--mu);font-size:.82rem;max-width:470px;margin:0}
  .hint a{color:var(--ac)}
  iframe{flex:1;width:100%;border:0;background:var(--bg)}
  [hidden]{display:none!important}
  noscript{padding:40px;text-align:center;color:var(--mu)}
</style>
</head>
<body>
  <div id="loader">
    <h1 id="status">SP2 DB Editor</h1>
    <div class="track"><div id="bar"></div></div>
    <p id="detail">preparing</p>
    <p class="hint">PHP itself is being downloaded and started inside this tab &mdash; about 7&nbsp;MB
      the first time, then it comes from the browser cache. Your database stays on this device:
      nothing is uploaded anywhere.</p>
    <p class="hint">Work is kept in the tab only, so download your files before closing it.
      <a href="../index.html">About this editor</a></p>
  </div>
  <iframe id="app" title="SP2 DB Editor" allow="clipboard-write"></iframe>
  <noscript>This page needs JavaScript: the PHP interpreter runs inside your browser.</noscript>
  <script type="module" src="boot.js"></script>
</body>
</html>
`;
