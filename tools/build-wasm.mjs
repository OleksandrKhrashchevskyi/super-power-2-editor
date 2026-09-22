#!/usr/bin/env node
/*
 * Builds the in-browser edition of the editor into docs/app/.
 *
 *   npm install @php-wasm/universal @php-wasm/web @php-wasm/web-8-3 esbuild
 *   node tools/build-wasm.mjs
 *
 * The result is a static folder that GitHub Pages can serve. It contains the
 * PHP interpreter compiled to WebAssembly, split into chunks small enough for
 * the GitHub web uploader, and the whole editor packed into one file. A service
 * worker routes every request under docs/app/run/ to that PHP, so the editor
 * runs exactly as it does on a server - only the server is the browser tab.
 *
 * Nothing here touches the editor's PHP. It is copied in unchanged.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { execFileSync } from 'node:child_process';
import { BOOT, SW, SHELL } from './wasm-templates.mjs';

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const out = path.join(root, 'docs', 'app');
const CHUNK = 7 * 1000 * 1000;

const rm = (p) => fs.rmSync(p, { recursive: true, force: true });
const mk = (p) => fs.mkdirSync(p, { recursive: true });
const write = (p, b) => { mk(path.dirname(p)); fs.writeFileSync(p, b); };

/* ------------------------------------------------- collect editor sources */

const SKIP = new Set(['work', 'docs', 'tools', 'node_modules', '.git', '.github', 'backups']);
function collect(dir, prefix = '') {
  const files = [];
  for (const name of fs.readdirSync(dir).sort()) {
    if (prefix === '' && SKIP.has(name)) continue;
    if (name.startsWith('.')) continue;
    const abs = path.join(dir, name);
    const rel = prefix ? `${prefix}/${name}` : name;
    if (fs.statSync(abs).isDirectory()) files.push(...collect(abs, rel));
    else if (!/\.(md|gdb|gst|zip)$/i.test(name) || /README/i.test(name)) files.push(rel);
  }
  return files;
}

const sources = collect(root);
const pack = {};
let raw = 0;
for (const rel of sources) {
  const buf = fs.readFileSync(path.join(root, rel));
  pack[rel] = buf.toString('base64');
  raw += buf.length;
}

rm(out);
mk(out);
write(path.join(out, 'editor.json'), JSON.stringify(pack));
console.log(`editor    : ${sources.length} files, ${(raw / 1048576).toFixed(1)} MB ` +
            `-> editor.json ${(fs.statSync(path.join(out, 'editor.json')).size / 1048576).toFixed(1)} MB`);

/* ----------------------------------------------------- split the PHP wasm */

const wasmPath = path.join(root, 'node_modules', '@php-wasm', 'web-8-3', 'asyncify', '8_3_33', 'php_8_3.wasm');
if (!fs.existsSync(wasmPath)) {
  console.error(`\nPHP runtime not found at\n  ${wasmPath}\nRun: npm install @php-wasm/web-8-3\n`);
  process.exit(1);
}
const wasm = fs.readFileSync(wasmPath);
const chunks = Math.ceil(wasm.length / CHUNK);
for (let i = 0; i < chunks; i++) {
  write(path.join(out, 'runtime', `php.wasm.${i}`), wasm.subarray(i * CHUNK, (i + 1) * CHUNK));
}
console.log(`runtime   : ${(wasm.length / 1048576).toFixed(1)} MB in ${chunks} chunks`);

/* --------------------------------------------------------- bundle the glue */

const entry = path.join(out, '.boot-entry.mjs');
write(entry, BOOT.replace('__CHUNKS__', String(chunks))
                 .replace('__PHP_LOADER__', JSON.stringify(
                   path.join(root, 'node_modules', '@php-wasm', 'web-8-3', 'asyncify', 'php_8_3.js'))));
execFileSync('npx', ['esbuild', entry, '--bundle', '--format=esm', '--minify',
  `--outfile=${path.join(out, 'boot.js')}`, '--external:worker_threads',
  '--loader:.wasm=file', '--loader:.so=file', '--loader:.dat=file',
  '--asset-names=unused-[hash]', '--log-level=error'], { cwd: root, stdio: 'inherit' });
fs.rmSync(entry);
for (const f of fs.readdirSync(out)) if (f.startsWith('unused-')) fs.rmSync(path.join(out, f));
console.log(`glue      : boot.js ${(fs.statSync(path.join(out, 'boot.js')).size / 1024).toFixed(0)} KB`);

write(path.join(out, 'sw.js'), SW);
write(path.join(out, 'index.html'), SHELL);
write(path.join(out, '.nojekyll'), '');

let total = 0, count = 0;
(function walk(d) { for (const n of fs.readdirSync(d)) { const p = path.join(d, n);
  const s = fs.statSync(p); s.isDirectory() ? walk(p) : (total += s.size, count++); } })(out);
console.log(`docs/app  : ${count} files, ${(total / 1048576).toFixed(1)} MB`);
