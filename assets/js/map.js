
(function () {
  'use strict';

  const meta = document.getElementById('mapMeta');
  if (!meta) return;
  const CFG = JSON.parse(meta.textContent);

  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const LANG = document.documentElement.getAttribute('lang') || 'en';

  const I18N = (() => {
    try { return JSON.parse(document.getElementById('i18n').textContent); } catch (e) { return {}; }
  })();
  const tr = (k, n) => {
    const s = I18N[k] || k;
    return n === undefined ? s : s.replace('%d', n);
  };


  const W = 2560, H = 1280;
  const LAT_TOP = 82, LAT_BOT = -58;
  const LON_L = -180, LON_R = 180;
  const sx = W / (LON_R - LON_L), sy = H / (LAT_TOP - LAT_BOT);
  const pxLon = lon => (lon - LON_L) * sx;
  const pxLat = lat => (LAT_TOP - lat) * sy;
  const lonPx = x => x / sx + LON_L;
  const latPx = y => LAT_TOP - y / sy;


  const stage  = $('#mapStage');
  const view   = $('#mapCanvas');
  const vctx   = view.getContext('2d');
  const raster = document.createElement('canvas'); raster.width = W; raster.height = H;
  const rctx   = raster.getContext('2d', { willReadFrequently: true });

  let landMask = null;
  let realCou  = null;
  let couPolys = [];
  let ownReg   = null;
  let bbox     = null;
  let img      = null;
  let D = null;
  let landPolys = [];
  let nReg = 0;

  let RCOL = null;
  let RKEY = null;
  let RCOU = null;


  const S = {
    layer: 'country', item: 0, itemKind: null, highlight: 0, pick: null, hover: -1,
    sel: new Set(),
    cities: true, caps: true, regions: true, coast: true, borders: true,
    troops: false, missiles: false,
    dimInactive: false,
    zoom: 1, ox: 0, oy: 0
  };


  const LAYERS = {
    country: { kind: 'cat', dict: 'country' },
    mil:     { kind: 'cat', dict: 'country' },
    lang:    { kind: 'cat', dict: 'lang' },
    rel:     { kind: 'cat', dict: 'rel' },
    gvt:     { kind: 'cat', dict: 'gvt' },
    cont:    { kind: 'cat', dict: 'num' },
    geo:     { kind: 'cat', dict: 'num' },
    sharelang: { kind: 'num', pct: true, needs: 'lang' },
    sharerel:  { kind: 'num', pct: true, needs: 'rel' },
    relation:  { kind: 'num', div: true, needs: 'country', lazy: true },
    treaty:    { kind: 'cat', dict: 'side', needs: 'treaty' },
    force:     { kind: 'num' },
    forcetype: { kind: 'num', needs: 'dtype' },
    pop:     { kind: 'num' },
    infra:   { kind: 'num' },
    tele:    { kind: 'num' }
  };
  const isNum = () => LAYERS[S.layer].kind === 'num';


  let CATCOL = new Map();
  let CATCNT = [];
  let NOMAN = [70, 74, 84], EMPTY = [92, 96, 106];
  const dark = () => document.documentElement.getAttribute('data-bs-theme') !== 'light';

  function hsl(h, s, l) {
    const f = n => {
      const k = (n + h * 12) % 12;
      const a = s * Math.min(l, 1 - l);
      return Math.round(255 * (l - a * Math.max(-1, Math.min(k - 3, 9 - k, 1))));
    };
    return [f(0), f(8), f(4)];
  }

  function colorByRank(i) {
    const d = dark();
    const target = d ? 0.40 : 0.70;
    const h = (i * 137.508) % 360;
    const band = i % 3;
    const sat = d ? [0.46, 0.34, 0.26][band] : [0.42, 0.32, 0.24][band];
    const lum = d ? [0.46, 0.40, 0.52][band] : [0.64, 0.70, 0.60][band];
    let [r, g, b] = hsl(h / 360, sat, lum);
    const luma = (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
    const k = (target * (band === 2 ? 1.12 : band === 1 ? 0.92 : 1)) / Math.max(0.05, luma);
    return [Math.min(255, r * k), Math.min(255, g * k), Math.min(255, b * k)];
  }
  function buildCatColors() {
    const cnt = new Map();
    for (let ri = 0; ri < nReg; ri++) { const v = catVal(ri); if (v) cnt.set(v, (cnt.get(v) || 0) + 1); }
    CATCNT = [...cnt.entries()].sort((a, b) => b[1] - a[1] || a[0] - b[0]);
    CATCOL = new Map();
    CATCNT.forEach(([v], i) => CATCOL.set(v, colorByRank(i)));
  }
  const catColor = v => CATCOL.get(v) || EMPTY;


  let COUCOL = new Map();
  function buildCouColors() {
    const cnt = new Map();
    for (let ri = 0; ri < nReg; ri++) { const c = D.region.country[ri]; if (c) cnt.set(c, (cnt.get(c) || 0) + 1); }
    const list = [...cnt.entries()].sort((a, b) => b[1] - a[1] || a[0] - b[0]);
    COUCOL = new Map();
    list.forEach(([v], i) => COUCOL.set(v, colorByRank(i)));
  }
  const catColorFor = (kind, v) => (kind === 'country' ? (COUCOL.get(v) || EMPTY) : catColor(v));


  const RAMP = [[68,1,84],[65,68,135],[42,120,142],[34,168,132],[122,209,81],[253,231,37]];

  const RAMP_DIV = [[158,30,45],[204,96,72],[224,166,130],[150,150,155],[130,190,175],[58,140,140],[20,90,110]];
  function rampColor(t, ramp) {
    const R = ramp || RAMP;
    t = Math.max(0, Math.min(1, t));
    const x = t * (R.length - 1), i = Math.min(R.length - 2, Math.floor(x)), f = x - i;
    const a = R[i], b = R[i + 1];
    return [a[0] + (b[0]-a[0])*f, a[1] + (b[1]-a[1])*f, a[2] + (b[2]-a[2])*f];
  }
  const cssVar = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
  function hexRgb(h) {
    const m = /^#?([0-9a-f]{6})$/i.exec((h || '').trim()); if (!m) return null;
    const n = parseInt(m[1], 16); return [(n>>16)&255, (n>>8)&255, n&255];
  }


  const prog = $('#mapProgress'), ptext = $('#mapLoadingText'), loading = $('#mapLoading');
  const setProg = (p, txt) => {
    if (prog) prog.style.width = Math.round(p * 100) + '%';
    if (txt && ptext) ptext.textContent = txt;
  };
  const nextFrame = () => new Promise(r => requestAnimationFrame(r));

  async function boot() {
    setProg(.05, tr('Loading data…'));
    const [land, cous, data] = await Promise.all([
      fetch(CFG.land).then(r => r.json()),
      fetch(CFG.cous).then(r => r.json()).catch(() => null),
      fetch(CFG.url, { headers: { 'X-Requested-With': 'fetch' } }).then(r => r.json())
    ]);
    if (!data.ok) throw new Error(data.error || 'mapdata');
    D = data;
    nReg = D.region.id.length;
    prepare();
    decodeLand(land);
    if (cous) decodeCous(cous);

    setProg(.2, tr('Drawing land…'));  await nextFrame();
    buildLandMask();
    if (couPolys.length) buildRealCou();

    setProg(.3, tr('Filling territory…'));  await nextFrame();
    await buildOwnership();

    setProg(.95, tr('Colouring…')); await nextFrame();
    img = rctx.createImageData(W, H);
    initUI();
    applyLayer();

    loading.hidden = true;
    fitAll();
    draw();


    let skin = '';
    document.addEventListener('sp2:ui', e => {
      const d = e.detail || {};
      const now = (d.kind || '') + '|' + (d.theme || '') + '|' + (d.accent || '');
      if (now === skin) return;
      skin = now;
      applyLayer(); draw();
    });
    skin = (document.documentElement.getAttribute('data-bs-theme') || '') + '|' +
           (document.documentElement.getAttribute('data-theme') || '') + '|' +
           (document.documentElement.style.getPropertyValue('--ac') || '');
  }


  function buildMix(mix) {
    const n = mix.reg.length;
    const start = new Int32Array(nReg + 1);
    for (let k = 0; k < n; k++) start[mix.reg[k] + 1]++;
    for (let i = 0; i < nReg; i++) start[i + 1] += start[i];
    const pos = Int32Array.from(start.subarray(0, nReg));
    const ids = new Int32Array(n), pops = new Float64Array(n);
    for (let k = 0; k < n; k++) {
      const p = pos[mix.reg[k]]++;
      ids[p] = mix.id[k]; pops[p] = mix.pop[k];
    }
    const tot = new Float64Array(nReg), top = new Int32Array(nReg);
    for (let r = 0; r < nReg; r++) {
      let best = 0, bp = -1, t = 0;
      for (let k = start[r]; k < start[r + 1]; k++) { t += pops[k]; if (pops[k] > bp) { bp = pops[k]; best = ids[k]; } }
      tot[r] = t; top[r] = best;
    }
    return { start, ids, pops, tot, top };
  }
  let MIX = { lang: null, rel: null };
  function shareOf(mx, ri, id) {
    if (!mx.tot[ri]) return NODATA;
    for (let k = mx.start[ri]; k < mx.start[ri + 1]; k++) if (mx.ids[k] === id) return mx.pops[k] / mx.tot[ri];
    return 0;
  }
  function mixList(mx, ri, limit) {
    const out = [];
    for (let k = mx.start[ri]; k < mx.start[ri + 1]; k++) out.push([mx.ids[k], mx.pops[k]]);
    out.sort((a, b) => b[1] - a[1]);
    return limit ? out.slice(0, limit) : out;
  }

  function prepare() {
    MIX.lang = buildMix(D.langMix);
    MIX.rel  = buildMix(D.relMix);
    D.country.byId = {}; D.country.gvtById = {}; D.country.actById = {};
    D.country.id.forEach((id, i) => {
      D.country.byId[id] = i;
      D.country.gvtById[id] = D.country.gvt[i];
      D.country.actById[id] = D.country.act[i];
    });
    D.lang.byId = {}; D.lang.id.forEach((id, i) => D.lang.byId[id] = i);
    D.rel.byId  = {}; D.rel.id.forEach((id, i) => D.rel.byId[id] = i);
    D.gvt.byId  = {}; D.gvt.id.forEach((id, i) => D.gvt.byId[id] = i);
  }


  function decodeLand(L) {
    const k = L.s;
    landPolys = L.p.map(poly => poly.map(flat => {
      const out = new Float32Array(flat.length);
      let x = 0, y = 0;
      for (let i = 0; i < flat.length; i += 2) {
        x += flat[i]; y += flat[i + 1];
        out[i] = x / k; out[i + 1] = y / k;
      }
      return out;
    }));
  }
  function tracePoly(ctx, poly, px, py) {
    for (const ring of poly) {
      ctx.moveTo(px(ring[0]), py(ring[1]));
      for (let i = 2; i < ring.length; i += 2) ctx.lineTo(px(ring[i]), py(ring[i + 1]));
      ctx.closePath();
    }
  }
  function tracePolys(ctx, px, py) { for (const poly of landPolys) tracePoly(ctx, poly, px, py); }


  const ISO_ALIAS = {
    ROU: 'ROM', SRB: 'SCG', MNE: 'SCG', SSD: 'SDN',
    GRL: 'DNK', PRI: 'USA', NCL: 'FRA', ATF: 'FRA', FLK: 'GBR'
  };
  function decodeCous(L) {
    const k = L.s;
    couPolys = L.c.map(([code, rings]) => {
      const rs = rings.map(flat => {
        const out = new Float32Array(flat.length);
        let x = 0, y = 0;
        for (let i = 0; i < flat.length; i += 2) {
          x += flat[i]; y += flat[i + 1];
          out[i] = x / k; out[i + 1] = y / k;
        }
        return out;
      });
      let a = 0;
      for (const r of rs) {
        let s2 = 0;
        for (let i = 0; i + 3 < r.length; i += 2) s2 += r[i] * r[i + 3] - r[i + 2] * r[i + 1];
        a += Math.abs(s2) / 2;
      }
      return { code, rings: rs, area: a };
    }).sort((p, q) => q.area - p.area);
  }

  function buildRealCou() {
    const byCode = {};
    D.country.id.forEach((id, i) => { const c = D.country.code[i]; if (c) byCode[c] = id; });
    const c = document.createElement('canvas'); c.width = W; c.height = H;
    const x = c.getContext('2d', { willReadFrequently: true });
    x.fillStyle = '#000'; x.fillRect(0, 0, W, H);
    const idx = [];
    couPolys.forEach(p => {
      const code = byCode[p.code] ? p.code : (byCode[ISO_ALIAS[p.code]] ? ISO_ALIAS[p.code] : null);
      if (!code) return;
      idx.push(byCode[code]);
      const n = idx.length;
      x.fillStyle = 'rgb(' + (n & 255) + ',' + ((n >> 8) & 255) + ',0)';
      for (const ring of p.rings) {
        x.beginPath();
        x.moveTo(pxLon(ring[0]), pxLat(ring[1]));
        for (let i = 2; i < ring.length; i += 2) x.lineTo(pxLon(ring[i]), pxLat(ring[i + 1]));
        x.closePath(); x.fill();
      }
    });
    const d = x.getImageData(0, 0, W, H).data;
    realCou = new Int16Array(W * H);
    for (let i = 0, p = 0; i < realCou.length; i++, p += 4) {
      const n = d[p] | (d[p + 1] << 8);
      realCou[i] = (n > 0 && n <= idx.length) ? idx[n - 1] : 0;
    }
  }

  function buildLandMask() {
    const c = document.createElement('canvas'); c.width = W; c.height = H;
    const x = c.getContext('2d', { willReadFrequently: true });
    x.fillStyle = '#000'; x.fillRect(0, 0, W, H);
    x.fillStyle = '#fff';


    for (const poly of landPolys) { x.beginPath(); tracePoly(x, poly, pxLon, pxLat); x.fill('evenodd'); }
    const d = x.getImageData(0, 0, W, H).data;
    landMask = new Uint8Array(W * H);
    for (let i = 0, p = 0; i < landMask.length; i++, p += 4) landMask[i] = d[p] > 127 ? 1 : 0;
  }


  async function buildOwnership() {
    const n = D.city.lon.length;
    const GW = 360, GH = 180;
    const head = new Int32Array(GW * GH).fill(-1);
    const next = new Int32Array(n).fill(-1);
    const cx = new Float32Array(n), cy = new Float32Array(n);
    for (let i = 0; i < n; i++) {
      cx[i] = pxLon(D.city.lon[i]); cy[i] = pxLat(D.city.lat[i]);
      let gx = Math.floor(D.city.lon[i] + 180), gy = Math.floor(90 - D.city.lat[i]);
      gx = Math.max(0, Math.min(GW - 1, gx)); gy = Math.max(0, Math.min(GH - 1, gy));
      const k = gy * GW + gx;
      next[i] = head[k]; head[k] = i;
    }
    const cellW = W / GW, cellH = sy;
    const MAXD = 14 * sx;
    const MAXD2 = MAXD * MAXD;
    const cellMin = Math.min(cellW, cellH);
    const RMAX = Math.ceil(MAXD / cellMin) + 1;

    ownReg = new Int16Array(W * H).fill(-1);

    const CHUNK = 48;
    for (let y0 = 0; y0 < H; y0 += CHUNK) {
      const y1 = Math.min(H, y0 + CHUNK);
      for (let y = y0; y < y1; y++) {
        const lat = latPx(y + .5);
        const gy0 = Math.max(0, Math.min(GH - 1, Math.floor(90 - lat)));
        const row = y * W;
        for (let x = 0; x < W; x++) {
          if (!landMask[row + x]) continue;
          const px = x + .5, py = y + .5;
          const gx0 = Math.max(0, Math.min(GW - 1, Math.floor(lonPx(px) + 180)));
          let best = -1, bd = Infinity;
          for (let r = 0; r <= RMAX; r++) {
            if (best >= 0 && (r - 1) * cellMin > Math.sqrt(bd)) break;
            for (let gy = gy0 - r; gy <= gy0 + r; gy++) {
              if (gy < 0 || gy >= GH) continue;
              const edge = (gy === gy0 - r || gy === gy0 + r);
              for (let gx = gx0 - r; gx <= gx0 + r; gx++) {
                if (!edge && gx !== gx0 - r && gx !== gx0 + r) continue;
                let g = gx; if (g < 0) g += GW; else if (g >= GW) g -= GW;
                for (let i = head[gy * GW + g]; i !== -1; i = next[i]) {
                  let dx = cx[i] - px;
                  if (dx > W / 2) dx -= W; else if (dx < -W / 2) dx += W;
                  const dy = cy[i] - py, d = dx * dx + dy * dy;
                  if (d < bd) { bd = d; best = i; }
                }
              }
            }
          }
          if (best < 0 || bd > MAXD2) { ownReg[row + x] = -2; continue; }
          ownReg[row + x] = D.city.reg[best];
        }
      }
      setProg(.3 + .5 * (y1 / H));
      await nextFrame();
    }
    if (realCou) await fixByCountry(cx, cy);
    computeBBox();
  }


  async function fixByCountry(cx, cy) {
    const byCou = new Map();
    for (let i = 0; i < D.city.lon.length; i++) {
      const c = D.region.country[D.city.reg[i]];
      if (!c) continue;
      let a = byCou.get(c); if (!a) byCou.set(c, a = []);
      a.push(i);
    }
    const MAXD2b = (34 * sx) * (34 * sx);
    const CHUNK = 64;
    for (let y0 = 0; y0 < H; y0 += CHUNK) {
      const y1 = Math.min(H, y0 + CHUNK);
      for (let y = y0; y < y1; y++) {
        const row = y * W;
        for (let x = 0; x < W; x++) {
          const i = row + x;
          if (!landMask[i]) continue;
          const c = realCou[i];
          if (!c) continue;
          const cur = ownReg[i];
          if (cur >= 0 && D.region.country[cur] === c) continue;
          const list = byCou.get(c);
          if (!list) continue;
          const px = x + .5, py = y + .5;
          let best = -1, bd = Infinity;
          for (let k = 0; k < list.length; k++) {
            const j = list[k];
            let dx = cx[j] - px;
            if (dx > W / 2) dx -= W; else if (dx < -W / 2) dx += W;
            const dy = cy[j] - py, d = dx * dx + dy * dy;
            if (d < bd) { bd = d; best = j; }
          }
          if (best >= 0 && bd <= MAXD2b) ownReg[i] = D.city.reg[best];
        }
      }
      setProg(.8 + .12 * (y1 / H));
      await nextFrame();
    }
  }

  function computeBBox() {
    bbox = new Int32Array(nReg * 4);
    for (let i = 0; i < nReg; i++) { bbox[i*4] = W; bbox[i*4+1] = H; bbox[i*4+2] = -1; bbox[i*4+3] = -1; }
    for (let y = 0; y < H; y++) {
      const row = y * W;
      for (let x = 0; x < W; x++) {
        const ri = ownReg[row + x];
        if (ri < 0) continue;
        const b = ri * 4;
        if (x < bbox[b]) bbox[b] = x;
        if (y < bbox[b+1]) bbox[b+1] = y;
        if (x > bbox[b+2]) bbox[b+2] = x;
        if (y > bbox[b+3]) bbox[b+3] = y;
      }
    }
  }


  let RELROW = null;
  let relGen = 0;
  let TSIDE = null;

  function catVal(ri) {
    switch (S.layer) {
      case 'treaty': return TSIDE ? (TSIDE.get(D.region.country[ri]) || 0) : 0;
      case 'country': return D.region.country[ri];
      case 'mil':     return D.region.mil[ri];
      case 'lang':    return MIX.lang.top[ri];
      case 'rel':     return MIX.rel.top[ri];
      case 'gvt':     return D.country.gvtById[D.region.country[ri]] || 0;
      case 'cont':    return D.region.cont[ri];
      case 'geo':     return D.region.geo[ri];
    }
    return 0;
  }

  const NODATA = NaN, SELF = Infinity;
  const noData = v => !(v === SELF) && !(v >= 0) && !(v < 0);

  function numVal(ri) {
    switch (S.layer) {
      case 'relation': {
        if (!RELROW) return NODATA;
        const c = D.region.country[ri];
        if (c === S.item) return SELF;
        const v = RELROW[c];
        return (v === undefined) ? NODATA : v;
      }
      case 'force':     return D.force ? (D.force[D.region.country[ri]] || 0) : NODATA;
      case 'forcetype': {
        if (!S.item || !D.forceType) return NODATA;
        const t = D.forceType[S.item];
        return t ? (t[D.region.country[ri]] || 0) : NODATA;
      }
      case 'pop':   return D.region.pop[ri];
      case 'infra': return D.region.infra[ri];
      case 'tele':  return D.region.tele[ri];
      case 'sharelang': return S.item ? shareOf(MIX.lang, ri, S.item) : NODATA;
      case 'sharerel':  return S.item ? shareOf(MIX.rel,  ri, S.item) : NODATA;
    }
    return NODATA;
  }
  let numMin = 0, numMax = 1, numMed = 0, numSorted = null;
  function scanNum() {
    const L = LAYERS[S.layer];
    numSorted = null;
    if (L.pct) { numMin = 0; numMax = 1; numMed = .5; return; }
    if (L.div) {



      const a = [];
      for (let ri = 0; ri < nReg; ri++) {
        const v = numVal(ri);
        if (!isFinite(v)) continue;
        a.push(Math.abs(v));
      }
      a.sort((x, y) => x - y);
      const m = Math.max(3, a.length ? a[Math.floor(a.length * 0.9)] : 3);
      numMin = -m; numMax = m; numMed = 0; return;
    }
    const vals = [];
    for (let ri = 0; ri < nReg; ri++) {
      const v = numVal(ri);
      if (isFinite(v)) vals.push(v);
    }
    if (!vals.length) { numMin = 0; numMax = 1; numMed = .5; return; }
    vals.sort((a, b) => a - b);
    numSorted = Float64Array.from(vals);
    numMin = numSorted[0];
    numMax = numSorted[numSorted.length - 1];
    numMed = numSorted[numSorted.length >> 1];
  }

  function normNum(v) {
    const L = LAYERS[S.layer];
    if (L.pct) return Math.max(0, Math.min(1, v));
    if (L.div) return Math.max(0, Math.min(1, (v - numMin) / (numMax - numMin)));
    if (!numSorted || numSorted.length < 2) return 0.5;
    let lo = 0, hi = numSorted.length - 1;
    while (lo < hi) { const m = (lo + hi) >> 1; if (numSorted[m] < v) lo = m + 1; else hi = m; }
    return lo / (numSorted.length - 1);
  }


  function regionColor(ri) {
    const L = LAYERS[S.layer];
    let r, g, b, val;
    if (L.kind === 'num') {
      val = numVal(ri);


      if (val === SELF) { const a = ACCENT; r = a[0]; g = a[1]; b = a[2]; }
      else if (noData(val) || (L.pct && val === 0)) { r = EMPTY[0]; g = EMPTY[1]; b = EMPTY[2]; }
      else {
        const c = rampColor(L.pct ? 0.12 + 0.88 * normNum(val) : normNum(val), L.div ? RAMP_DIV : null);
        r = c[0]; g = c[1]; b = c[2];
      }
    } else {
      val = catVal(ri);
      if (!val) { r = EMPTY[0]; g = EMPTY[1]; b = EMPTY[2]; }
      else {
        const c = catColor(val);

        const j = 1 + (((ri * 2654435761) >>> 24) % 9 - 4) / 100;
        r = c[0] * j; g = c[1] * j; b = c[2] * j;
      }
    }
    const co = D.region.country[ri];
    if (S.dimInactive && !D.country.actById[co]) { const m = (r+g+b)/3; r = (r+m*2)/3; g = (g+m*2)/3; b = (b+m*2)/3; }
    const dimByCountry = S.highlight && co !== S.highlight;
    const dimByPick    = S.pick !== null && L.kind === 'cat' && val !== S.pick;
    if (dimByCountry || dimByPick) {
      const m = 0.2126*r + 0.7152*g + 0.0722*b;
      r = m*.62 + r*.10; g = m*.62 + g*.10; b = m*.64 + b*.10;
    }
    if (S.sel.has(ri))  { const a = ACCENT; r = r*.35 + a[0]*.65; g = g*.35 + a[1]*.65; b = b*.35 + a[2]*.65; }
    if (ri === S.hover) { r = r*.55 + 255*.45; g = g*.55 + 255*.45; b = b*.55 + 255*.45; }
    const o = ri * 3;
    RCOL[o] = r > 255 ? 255 : r; RCOL[o+1] = g > 255 ? 255 : g; RCOL[o+2] = b > 255 ? 255 : b;
  }
  let ACCENT = [79, 140, 255];

  function applyLayer() {
    const d = dark();
    NOMAN = d ? [58, 62, 72] : [206, 209, 216];
    EMPTY = d ? [74, 78, 90] : [222, 224, 230];
    ACCENT = hexRgb(cssVar('--ac')) || [79, 140, 255];
    if (!RCOL || RCOL.length !== nReg * 3) {
      RCOL = new Uint8Array(nReg * 3); RKEY = new Int32Array(nReg); RCOU = new Int32Array(nReg);
      for (let ri = 0; ri < nReg; ri++) RCOU[ri] = D.region.country[ri];
    }
    const L = LAYERS[S.layer];


    if (L.kind === 'num') { scanNum(); RKEY.fill(0); }
    else { buildCatColors(); for (let ri = 0; ri < nReg; ri++) RKEY[ri] = catVal(ri); }
    buildCouColors();
    for (let ri = 0; ri < nReg; ri++) regionColor(ri);
    paint(0, 0, W, H);
    rctx.putImageData(img, 0, 0);
    legend();
    tipFor = -1;
  }


  function paint(x0, y0, x1, y1) {
    const d = img.data;
    const dk = dark();
    const catLine = dk ? 0.72 : 1.16;
    const couLine = dk ? 0.48 : 1.42;
    const regLine = dk ? 0.88 : 1.07;
    const showReg = S.regions, showCou = S.borders;

    for (let y = y0; y < y1; y++) {
      const row = y * W;
      for (let x = x0; x < x1; x++) {
        const i = row + x, p = i * 4;
        const ri = ownReg[i];
        if (ri === -1) { d[p] = 0; d[p+1] = 0; d[p+2] = 0; d[p+3] = 0; continue; }
        if (ri === -2) { d[p] = NOMAN[0]; d[p+1] = NOMAN[1]; d[p+2] = NOMAN[2]; d[p+3] = 255; continue; }
        const o = ri * 3;
        let r = RCOL[o], g = RCOL[o+1], b = RCOL[o+2];
        const key = RKEY[ri], cou = RCOU[ri];

        for (let s = 0; s < 2; s++) {
          const j = s === 0 ? (x + 1 < W ? i + 1 : -1) : (y + 1 < H ? i + W : -1);
          if (j < 0) continue;
          const rj = ownReg[j];
          if (rj === ri || rj < 0) continue;
          let k = 1;
          if (showCou && RCOU[rj] !== cou) k = couLine;
          else if (RKEY[rj] !== key)      k = catLine;
          else if (showReg)               k = regLine;
          if (k !== 1) { r *= k; g *= k; b *= k; }
        }
        d[p] = r > 255 ? 255 : r; d[p+1] = g > 255 ? 255 : g; d[p+2] = b > 255 ? 255 : b; d[p+3] = 255;
      }
    }
  }
  function repaintAll() { for (let ri = 0; ri < nReg; ri++) regionColor(ri); paint(0, 0, W, H); rctx.putImageData(img, 0, 0); }
  function repaintRegion(ri) {
    if (ri == null || ri < 0) return;
    const b = ri * 4;
    if (bbox[b+2] < 0) return;
    regionColor(ri);
    const x0 = Math.max(0, bbox[b]-1), y0 = Math.max(0, bbox[b+1]-1);
    const x1 = Math.min(W, bbox[b+2]+2), y1 = Math.min(H, bbox[b+3]+2);
    paint(x0, y0, x1, y1);
    rctx.putImageData(img, 0, 0, x0, y0, x1-x0, y1-y0);
  }


  function fitAll() {
    const r = stage.getBoundingClientRect();
    const zw = r.width / W, zh = r.height / H;
    let z = Math.min(zw, zh);
    if (H * z < r.height * 0.6) {


      z = Math.min(zh * 0.94, zw * 3.2);
      S.zoom = z;
      S.ox = r.width / 2 - pxLon(14) * z;
      S.oy = r.height / 2 - pxLat(26) * z;
      return;
    }
    S.zoom = z;
    S.ox = (r.width - W * z) / 2;
    S.oy = (r.height - H * z) / 2;
  }
  function resize() {
    const r = stage.getBoundingClientRect();
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    view.width = Math.round(r.width * dpr); view.height = Math.round(r.height * dpr);
    view.style.width = r.width + 'px'; view.style.height = r.height + 'px';
    vctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }
  let pending = false;

  let CAPSET = null;
  function capSet() {
    if (!CAPSET) CAPSET = new Set(D.country.cap || []);
    return S.caps ? CAPSET : EMPTYSET;
  }
  const EMPTYSET = new Set();

  function draw() { if (!pending) { pending = true; requestAnimationFrame(() => { pending = false; render(); }); } }

  function render() {
    const r = stage.getBoundingClientRect();
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    vctx.setTransform(1, 0, 0, 1, 0, 0);
    vctx.clearRect(0, 0, view.width, view.height);
    vctx.setTransform(dpr, 0, 0, dpr, 0, 0);



    vctx.imageSmoothingEnabled = S.zoom < 1.2 || S.zoom > 3;
    vctx.drawImage(raster, S.ox, S.oy, W * S.zoom, H * S.zoom);

    const px = lon => S.ox + pxLon(lon) * S.zoom;
    const py = lat => S.oy + pxLat(lat) * S.zoom;

    if (S.coast) {
      vctx.beginPath(); tracePolys(vctx, px, py);
      vctx.strokeStyle = cssVar('--bs-border-color') || '#3a4152';
      vctx.lineWidth = Math.max(.4, Math.min(1.4, S.zoom * .8));
      vctx.stroke();
    }

    if (S.cities && S.zoom > .35) {
      const cap = capSet();
      const mu = cssVar('--mu') || '#8a93a6';
      for (let i = 0; i < D.city.lon.length; i++) {
        const x = px(D.city.lon[i]), y = py(D.city.lat[i]);
        if (x < -4 || y < -4 || x > r.width + 4 || y > r.height + 4) continue;
        const isCap = cap.has(D.city.id[i]);
        const pop = D.city.pop[i];
        if (!isCap && S.zoom < 1.2 && pop < 800000) continue;
        const rad = isCap ? Math.max(2.2, 1.6 * S.zoom)
                          : Math.max(.7, Math.min(3, Math.log10(Math.max(pop, 10)) - 3.2) * S.zoom * .7);
        if (rad < .5) continue;
        vctx.beginPath(); vctx.arc(x, y, rad, 0, 6.2832);
        if (isCap) { vctx.fillStyle = '#fff'; vctx.fill(); vctx.lineWidth = 1; vctx.strokeStyle = '#000'; vctx.stroke(); }
        else { vctx.fillStyle = mu; vctx.globalAlpha = .75; vctx.fill(); vctx.globalAlpha = 1; }
      }
    }


    if (S.troops && D.group) {
      const g = D.group;
      let maxA = 1;
      for (const a of g.amt) if (a > maxA) maxA = a;
      for (let i = 0; i < g.lon.length; i++) {
        const x = px(g.lon[i]), y = py(g.lat[i]);
        if (x < -8 || y < -8 || x > r.width + 8 || y > r.height + 8) continue;
        if (S.highlight && g.cou[i] !== S.highlight) continue;
        const k = Math.sqrt(Math.max(1, g.amt[i]) / maxA);
        const sz = Math.max(2.4, Math.min(13, 3 + k * 11) * Math.min(1.6, Math.max(.55, S.zoom)));
        const c = catColorFor('country', g.cou[i]);
        vctx.beginPath();
        vctx.moveTo(x, y - sz); vctx.lineTo(x + sz, y); vctx.lineTo(x, y + sz); vctx.lineTo(x - sz, y);
        vctx.closePath();
        vctx.fillStyle = 'rgba(' + c.map(Math.round).join(',') + ',.88)';
        vctx.fill();
        vctx.lineWidth = Math.min(1.6, .6 + S.zoom * .3);
        vctx.strokeStyle = 'rgba(0,0,0,.7)'; vctx.stroke();
      }
    }
    if (S.missiles && D.missile) {
      const m = D.missile;
      for (let i = 0; i < m.lon.length; i++) {
        const x = px(m.lon[i]), y = py(m.lat[i]);
        if (x < -6 || y < -6 || x > r.width + 6 || y > r.height + 6) continue;
        if (S.highlight && m.cou[i] !== S.highlight) continue;
        const sz = Math.max(1.8, Math.min(7, 2 + Math.log10(Math.max(1, m.qty[i])) * 2.2) * Math.min(1.5, Math.max(.6, S.zoom)));
        vctx.beginPath();
        vctx.moveTo(x, y - sz); vctx.lineTo(x + sz * .72, y + sz * .7); vctx.lineTo(x - sz * .72, y + sz * .7);
        vctx.closePath();
        vctx.fillStyle = '#ff5c4d'; vctx.globalAlpha = .9; vctx.fill(); vctx.globalAlpha = 1;
        vctx.lineWidth = .8; vctx.strokeStyle = 'rgba(0,0,0,.75)'; vctx.stroke();
      }
    }

    if (selRect) {
      vctx.setLineDash([5, 4]);
      vctx.strokeStyle = cssVar('--ac') || '#4f8cff'; vctx.lineWidth = 1.5;
      vctx.strokeRect(selRect.x, selRect.y, selRect.w, selRect.h);
      vctx.setLineDash([]);
      vctx.fillStyle = 'rgba(' + ACCENT.join(',') + ',.12)';
      vctx.fillRect(selRect.x, selRect.y, selRect.w, selRect.h);
    }
  }


  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c]));
  const regName = ri => (D.region.name && D.region.name[ri]) || (tr('region') + ' ' + D.region.id[ri]);
  function dictName(kind, id) {
    if (!id) return tr(kind === 'side' ? 'not a member' : 'no data');
    if (kind === 'country') {
      const i = D.country.byId[id];
      if (i === undefined) return '#' + id;
      return (D.country.name && D.country.name[i]) || D.country.code[i] || ('#' + id);
    }
    if (kind === 'num') return '#' + id;
    if (kind === 'side') return id ? tr('side %d', id) : tr('not a member');
    const src = D[kind]; if (!src) return '#' + id;
    const i = src.byId[id];
    if (i === undefined) return '#' + id;
    return (src.name && src.name[i]) || ('#' + id);
  }
  const couName = id => dictName('country', id);
  const catName = v => dictName(LAYERS[S.layer].dict, v);
  const fmtInt = n => { try { return Math.round(n).toLocaleString(LANG); } catch (e) { return String(Math.round(n)); } };
  function fmtNum(v) {
    const L = LAYERS[S.layer];
    if (L.pct) return (v * 100).toFixed(1) + '%';
    if (L.div) return (v > 0 ? '+' : '') + Math.round(v);
    if (S.layer === 'pop' || S.layer === 'force' || S.layer === 'forcetype') return fmtInt(v);
    return (Math.round(v * 1000) / 1000).toString();
  }

  function regionAt(clientX, clientY) {
    const r = stage.getBoundingClientRect();
    const x = Math.floor((clientX - r.left - S.ox) / S.zoom);
    const y = Math.floor((clientY - r.top - S.oy) / S.zoom);
    if (x < 0 || y < 0 || x >= W || y >= H) return -1;
    return ownReg[y * W + x];
  }


  let drag = null, selRect = null, moved = 0;
  let tsCountry = null, tsItem = null, COUOPTS = [];

  function initUI() {
    resize();
    new ResizeObserver(() => { resize(); draw(); }).observe(stage);

    const cityCnt = {}, regCnt = {};
    for (const ri of D.city.reg) { const c = D.region.country[ri]; cityCnt[c] = (cityCnt[c] || 0) + 1; }
    for (let i = 0; i < nReg; i++) { const c = D.region.country[i]; regCnt[c] = (regCnt[c] || 0) + 1; }
    D.stats = { cityCnt, regCnt };

    COUOPTS = D.country.id.map((id, i) => ({
      value: String(id),
      text: (D.country.name && D.country.name[i]) || D.country.code[i],
      code: D.country.code[i], regs: regCnt[id] || 0
    })).sort((a, b) => a.text.localeCompare(b.text));
    const couOpts = COUOPTS;

    const render = {
      option: (d, escf) => '<div class="option d-flex align-items-center gap-2">' +
        '<span class="flex-grow-1">' + escf(d.text) + '</span>' +
        (d.regs ? '<span class="ts-code">' + d.regs + '</span>' : '') +
        (d.code ? '<span class="ts-code">' + escf(d.code) + '</span>' : '') + '</div>',
      item: (d, escf) => '<div>' + escf(d.text) + '</div>'
    };
    tsCountry = new TomSelect($('#mapCountry'), {
      plugins: ['clear_button'], options: couOpts, valueField: 'value', labelField: 'text',
      searchField: ['text', 'code'], maxOptions: 500, allowEmptyOption: true, render,
      onChange: v => { S.highlight = +v || 0; repaintAll(); legend(); draw(); }
    });
    tsItem = new TomSelect($('#mapItem'), {
      options: [], valueField: 'value', labelField: 'text', searchField: ['text'],
      maxOptions: 500, render,
      onChange: async v => {
        S.item = +v || 0;
        S.itemKind = LAYERS[S.layer].needs || null;
        const L = LAYERS[S.layer], was = S.layer;
        if (L.lazy && S.item) {
          RELROW = null;


          const my = ++relGen;
          try {
            const r = await fetch('index.php?a=relations&c=' + S.item, { headers: { 'X-Requested-With': 'fetch' } });
            const d = await r.json();
            if (my !== relGen || S.layer !== was) return;
            if (d.ok) RELROW = d.v;
          } catch (e) { if (my !== relGen || S.layer !== was) return; }
        }
        if (S.layer === 'treaty') {
          TSIDE = new Map();
          if (S.item) for (let i = 0; i < D.tmember.t.length; i++) if (D.tmember.t[i] === S.item) TSIDE.set(D.tmember.c[i], D.tmember.s[i] || 1);
        }
        applyLayer(); draw();
      }
    });
    new TomSelect($('#mapSelTarget'), {
      options: couOpts, valueField: 'value', labelField: 'text',
      searchField: ['text', 'code'], maxOptions: 500, render
    });

    $('#mapLayer').addEventListener('change', () => setLayer($('#mapLayer').value));

    const ly = (id, key, relayer) => {
      const el = $('#' + id); if (!el) return;
      el.addEventListener('change', () => { S[key] = el.checked; if (relayer) { paint(0,0,W,H); rctx.putImageData(img,0,0); } draw(); });
    };
    ly('lyCities', 'cities'); ly('lyCaps', 'caps');
    ly('lyRegions', 'regions', true); ly('lyCoast', 'coast');
    ly('lyBorders', 'borders', true);
    ly('lyTroops', 'troops'); ly('lyMissiles', 'missiles');
    const di = $('#lyInactive');
    if (di) di.addEventListener('change', () => { S.dimInactive = di.checked; repaintAll(); draw(); });

    $('#mapZoomIn').addEventListener('click', () => zoomAt(1.5));
    $('#mapZoomOut').addEventListener('click', () => zoomAt(1 / 1.5));
    $('#mapReset').addEventListener('click', () => { fitAll(); draw(); });
    $('#mapSelClear').addEventListener('click', () => { const old = [...S.sel]; S.sel.clear(); old.forEach(repaintRegion); selBar(); draw(); });
    $('#mapSelBar').addEventListener('submit', () => { $('#mapSelIds').value = [...S.sel].map(ri => D.region.id[ri]).join(','); });


    const lg = $('#mapLegend'), lgBtn = $('#mapLegendToggle');
    const setLg = open => { lg.classList.toggle('collapsed', !open); lgBtn.setAttribute('aria-expanded', open ? 'true' : 'false'); };
    setLg(window.matchMedia('(min-width: 768px)').matches);
    lgBtn.addEventListener('click', () => setLg(lg.classList.contains('collapsed')));

    stage.addEventListener('pointerdown', onDown);
    window.addEventListener('pointermove', onMove);
    window.addEventListener('pointerup', onUp);
    stage.addEventListener('wheel', onWheel, { passive: false });
    stage.addEventListener('pointerleave', tipHide);
    window.addEventListener('keydown', e => {
      if (e.key !== 'Escape' || !S.sel.size) return;
      if (document.querySelector('.modal.show')) return;
      $('#mapSelClear').click();
    });


    const want = (CFG.layer || '').toString();
    if (want && LAYERS[want]) { $('#mapLayer').value = want; S.layer = want; }
    setLayer(S.layer, true);
  }

  function setLayer(id, silent) {
    if (!LAYERS[id]) return;
    S.layer = id; S.pick = null;
    ++relGen;
    const L = LAYERS[id];
    const wrap = $('#mapItemWrap');
    if (L.needs) {

      if (S.itemKind !== L.needs) { S.item = 0; S.itemKind = L.needs; RELROW = null; TSIDE = null; }
      tsItem.clear(true);
      tsItem.clearOptions();
      tsItem.addOptions(itemOptions(L.needs));
      if (S.item) tsItem.setValue(String(S.item), true);
      wrap.hidden = false;
    } else { wrap.hidden = true; S.item = 0; S.itemKind = null; RELROW = null; TSIDE = null; }
    if (!silent) { applyLayer(); draw(); }
  }

  function itemOptions(kind) {
    if (kind === 'country') return COUOPTS;
    if (kind === 'treaty') {
      const cnt = new Map();
      for (const t of D.tmember.t) cnt.set(t, (cnt.get(t) || 0) + 1);
      return D.treaty.id.map((v, i) => ({
        value: String(v), text: D.treaty.name[i], regs: cnt.get(v) || 0
      })).filter(o => o.regs).sort((a, b) => b.regs - a.regs);
    }
    if (kind === 'dtype') {
      return D.dtypeId.map((v, i) => ({
        value: String(v), text: '#' + v + (D.dtype[i] ? ' · ' + D.dtype[i] : '')
      }));
    }
    const src = D[kind];
    return src.id.map((v, i) => ({ value: String(v), text: (src.name && src.name[i]) || ('#' + v) }))
              .filter(o => o.text).sort((a, b) => a.text.localeCompare(b.text));
  }

  function zoomAt(k, cx, cy) {
    const r = stage.getBoundingClientRect();
    if (cx === undefined) { cx = r.width / 2; cy = r.height / 2; }
    const nz = Math.max(.15, Math.min(24, S.zoom * k));
    S.ox = cx - (cx - S.ox) * (nz / S.zoom);
    S.oy = cy - (cy - S.oy) * (nz / S.zoom);
    S.zoom = nz; draw();
  }
  function onWheel(e) {
    if (overUI(e)) return;
    e.preventDefault();
    const r = stage.getBoundingClientRect();
    zoomAt(e.deltaY < 0 ? 1.18 : 1 / 1.18, e.clientX - r.left, e.clientY - r.top);
  }

  const overUI = e => !!(e.target && e.target.closest && e.target.closest('.map-legend'));

  function onDown(e) {
    if (e.button !== 0 || overUI(e)) return;
    const r = stage.getBoundingClientRect();
    moved = 0;
    if (e.shiftKey) selRect = { x: e.clientX - r.left, y: e.clientY - r.top, w: 0, h: 0, sx: e.clientX - r.left, sy: e.clientY - r.top };
    else drag = { x: e.clientX, y: e.clientY, ox: S.ox, oy: S.oy };
    stage.setPointerCapture(e.pointerId);
  }
  function onMove(e) {
    const r = stage.getBoundingClientRect();
    if (selRect) {
      const x = e.clientX - r.left, y = e.clientY - r.top;
      selRect.x = Math.min(x, selRect.sx); selRect.y = Math.min(y, selRect.sy);
      selRect.w = Math.abs(x - selRect.sx); selRect.h = Math.abs(y - selRect.sy);
      moved = selRect.w + selRect.h; draw(); return;
    }
    if (drag) {
      S.ox = drag.ox + (e.clientX - drag.x); S.oy = drag.oy + (e.clientY - drag.y);
      moved = Math.abs(e.clientX - drag.x) + Math.abs(e.clientY - drag.y);
      draw(); return;
    }


    if (!stage.contains(e.target) || overUI(e)) { tipHide(); return; }
    const ri = regionAt(e.clientX, e.clientY);
    if (ri !== S.hover) {
      const old = S.hover; S.hover = ri;
      repaintRegion(old); repaintRegion(ri); draw();
    }
    if (ri < 0) tipHide();
    else if (ri === tipFor && !tip.hidden) tipMove(e.clientX, e.clientY);
    else tipShow(ri, e.clientX, e.clientY);
  }
  function onUp(e) {
    if (selRect) {
      if (selRect.w > 3 && selRect.h > 3) pickRect(selRect);
      selRect = null; draw();
    } else if (drag) {
      drag = null;
      if (moved < 4) clickAt(e);
    }
  }
  function pickRect(sr) {
    const x0 = Math.max(0, Math.floor((sr.x - S.ox) / S.zoom));
    const y0 = Math.max(0, Math.floor((sr.y - S.oy) / S.zoom));
    const x1 = Math.min(W, Math.ceil((sr.x + sr.w - S.ox) / S.zoom));
    const y1 = Math.min(H, Math.ceil((sr.y + sr.h - S.oy) / S.zoom));

    const found = new Set();
    for (let y = y0; y < y1; y++) {
      const row = y * W;
      for (let x = x0; x < x1; x++) { const ri = ownReg[row + x]; if (ri >= 0) found.add(ri); }
    }
    found.forEach(ri => S.sel.add(ri));

    if (found.size > 200) repaintAll(); else found.forEach(repaintRegion);
    selBar();
  }
  function clickAt(e) {
    const ri = regionAt(e.clientX, e.clientY);
    if (ri < 0) return;
    if (e.ctrlKey || e.metaKey || S.sel.size) {
      S.sel.has(ri) ? S.sel.delete(ri) : S.sel.add(ri);
      repaintRegion(ri); selBar(); draw(); return;
    }
    openRegion(ri);
  }
  function selBar() {
    $('#mapSelBar').hidden = S.sel.size === 0;
    $('#mapSelCount').textContent = S.sel.size;
  }


  const tip = $('#mapTip');
  let tipFor = -1, tipW = 0, tipH = 0;
  function tipShow(ri, cx, cy) {
    const co = D.region.country[ri], mi = D.region.mil[ri];
    let html = '<div class="tip-name">' + esc(regName(ri)) + '</div>' +
      '<div class="tip-row"><i class="bi bi-flag"></i> ' + esc(couName(co)) + '</div>';
    if (mi !== co) html += '<div class="tip-row tip-warn"><i class="bi bi-shield-fill"></i> ' + esc(couName(mi)) + ' · ' + tr('military control') + '</div>';

    const L = LAYERS[S.layer];
    if (L.kind === 'num') {
      const v = numVal(ri);
      html += '<div class="tip-row tip-hi"><i class="bi bi-thermometer-half"></i> ' +
              (v === SELF ? tr('selected country') : noData(v) ? tr('no data') : esc(fmtNum(v))) + '</div>';
    } else if (S.layer !== 'country' && S.layer !== 'mil') {
      html += '<div class="tip-row tip-hi"><i class="bi bi-palette"></i> ' + esc(catName(catVal(ri))) + '</div>';
    }

    const mkMix = (mx, icon, label) => {
      const list = mixList(mx, ri, 3);
      if (!list.length) return '';
      const tot = mx.tot[ri] || 1;
      let h = '<div class="tip-mix"><span class="tip-mix-h"><i class="bi bi-' + icon + '"></i> ' + label + '</span>';
      for (const [id, p] of list) {
        const kind = mx === MIX.lang ? 'lang' : 'rel';
        h += '<div class="tip-mix-r"><span>' + esc(dictName(kind, id)) + '</span><b>' +
             (p / tot * 100).toFixed(0) + '%</b></div>';
      }
      return h + '</div>';
    };
    html += mkMix(MIX.lang, 'translate', tr('Languages'));
    html += mkMix(MIX.rel, 'bank', tr('Religions'));
    if (S.troops || S.missiles || S.layer === 'force' || S.layer === 'forcetype') {
      const f = D.force ? (D.force[co] || 0) : 0;
      if (f) html += '<div class="tip-row"><i class="bi bi-shield-shaded"></i> ' + fmtInt(f) + ' ' + tr('units') + '</div>';
    }
    html += '<div class="tip-row tip-dim">ID ' + D.region.id[ri] + ' · ' + tr('population') + ' ' + fmtInt(D.region.pop[ri]) + '</div>';

    tip.innerHTML = html;
    tip.hidden = false;
    tipFor = ri;


    tip.style.left = '0px'; tip.style.top = '0px';
    tipW = tip.offsetWidth; tipH = tip.offsetHeight;
    tipMove(cx, cy);
  }
  function tipMove(cx, cy) {
    const r = stage.getBoundingClientRect();
    let x = cx - r.left + 14, y = cy - r.top + 14;
    if (x + tipW > r.width - 6) x = Math.max(4, cx - r.left - tipW - 12);
    if (y + tipH > r.height - 6) y = Math.max(4, cy - r.top - tipH - 12);
    tip.style.left = x + 'px'; tip.style.top = y + 'px';
  }
  function tipHide() {
    tip.hidden = true; tipFor = -1;
    if (S.hover >= 0) { const o = S.hover; S.hover = -1; repaintRegion(o); draw(); }
  }


  function legend() {
    const el = $('#mapLegend'), L = LAYERS[S.layer];
    const rows = el.querySelector('.ml-rows');
    const title = el.querySelector('.ml-title-t');
    el.hidden = false;

    if (L.kind === 'num') {
      title.textContent = $('#mapLayer').selectedOptions[0].textContent +
        (S.item ? ' · ' + itemLabel(L.needs, S.item) : '');
      if (L.div) {
        rows.innerHTML = S.item
          ? '<div class="ml-ramp ml-ramp-div"></div><div class="ml-scale"><span>' + tr('hostile') +
            '</span><span>0</span><span>' + tr('allied') + '</span></div>' +
            '<div class="ml-row" style="cursor:default"><span class="ml-dot" style="background:rgb(' +
            ACCENT.map(Math.round).join(',') + ')"></span><span class="ml-nm">' + tr('selected country') + '</span></div>'
          : '<div class="ml-empty">' + tr('Pick…') + '</div>';
        title.textContent = $('#mapLayer').selectedOptions[0].textContent +
          (S.item ? ' · ' + couName(S.item) : '');
        if (S.highlight) addCouLine(rows);
        return;
      }
      let bar = '<div class="ml-ramp"></div><div class="ml-scale"><span>' +
        (L.pct ? '0%' : esc(fmtNum(numMin))) + '</span>' +
        (L.pct ? '<span>50%</span>' : '<span>' + esc(fmtNum(numMed)) + '</span>') +
        '<span>' + (L.pct ? '100%' : esc(fmtNum(numMax))) + '</span></div>';
      if (L.needs && !S.item) bar = '<div class="ml-empty">' + tr('Pick…') + '</div>';
      rows.innerHTML = bar;
    } else {
      const list = CATCNT;
      title.textContent = $('#mapLayer').selectedOptions[0].textContent +
        (L.needs && S.item ? ' · ' + itemLabel(L.needs, S.item) : ' · ' + list.length);
      const show = list.slice(0, 14);
      let h = '';
      for (const [v, n] of show) {
        const c = catColor(v);
        h += '<button type="button" class="ml-row' + (S.pick === v ? ' on' : '') + '" data-cat="' + v + '">' +
             '<span class="ml-dot" style="background:rgb(' + c.map(Math.round).join(',') + ')"></span>' +
             '<span class="ml-nm">' + esc(catName(v)) + '</span><b>' + n + '</b></button>';
      }
      if (list.length > show.length) h += '<div class="ml-more">' + tr('and %d more', list.length - show.length) + '</div>';
      rows.innerHTML = h;
      $$('.ml-row', rows).forEach(btn => btn.addEventListener('click', () => {
        const v = +btn.dataset.cat;
        S.pick = (S.pick === v) ? null : v;
        repaintAll(); legend(); draw();
      }));
    }
    if (S.highlight) addCouLine(rows);
  }
  function addCouLine(rows) {
    const i = D.country.byId[S.highlight];
    rows.insertAdjacentHTML('afterbegin',
      '<div class="ml-cou"><i class="bi bi-flag-fill"></i><span>' + esc(couName(S.highlight)) +
      ' · ' + (D.stats.regCnt[S.highlight] || 0) + ' ' + tr('regions') +
      (D.country.act[i] ? '' : ' · <span class="tip-warn">' + tr('inactive') + '</span>') + '</span></div>');
  }
  function itemLabel(kind, id) {
    if (kind === 'country') return couName(id);
    if (kind === 'treaty') { const i = D.treaty.id.indexOf(id); return i < 0 ? '#' + id : D.treaty.name[i]; }
    if (kind === 'dtype')  { const i = D.dtypeId.indexOf(id); return '#' + id + (i >= 0 && D.dtype[i] ? ' · ' + D.dtype[i] : ''); }
    return dictName(kind, id);
  }


  async function openRegion(ri) {
    const rowid = D.region.rowid[ri];
    const modalEl = $('#rowModal');
    if (!modalEl || !rowid) return;
    try {
      const url = 'index.php?a=row&ajax=1&t=REGION&rowid=' + encodeURIComponent(rowid);
      const r = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
      const d = await r.json();
      if (!d.ok) { alert(d.error || 'error'); return; }
      window.SP2fillRow && window.SP2fillRow(d);
      const fs = $('#fldSearch');
      if (fs && fs.value) { fs.value = ''; fs.dispatchEvent(new Event('input')); }
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } catch (err) { alert('' + err); }
  }

  boot().catch(err => {
    loading.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-octagon"></i> ' +
                        esc(err.message || err) + '</div>';
  });
})();
