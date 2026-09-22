
(function () {
  'use strict';

  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const I18N = (() => {
    try { return JSON.parse(document.getElementById('i18n').textContent); } catch (e) { return {}; }
  })();
  const tr = (k, n) => {
    const s = I18N[k] || k;
    return n === undefined ? s : s.replace('%d', n);
  };


  const DEF = { theme: 'midnight', kind: 'dark', font: 'inter', size: 15, accent: '', text: '', dense: false };
  let UI = Object.assign({}, DEF);
  try { UI = Object.assign(UI, JSON.parse(localStorage.getItem('sp2ui') || '{}')); } catch (e) {}

  function applyUI(save) {
    const d = document.documentElement;
    d.setAttribute('data-theme', UI.theme);
    d.setAttribute('data-bs-theme', UI.kind);
    d.setAttribute('data-font', UI.font);
    d.style.setProperty('--ui-fs', UI.size + 'px');
    if (UI.dense) d.setAttribute('data-dense', '1'); else d.removeAttribute('data-dense');
    if (UI.accent) d.style.setProperty('--ac', UI.accent); else d.style.removeProperty('--ac');
    if (UI.accent) d.style.setProperty('--ac-rgb', hexRgb(UI.accent)); else d.style.removeProperty('--ac-rgb');

    if (UI.accent) d.style.setProperty('--ac-fg', onAccent(UI.accent)); else d.style.removeProperty('--ac-fg');
    if (UI.text) d.style.setProperty('--txt-override', UI.text); else d.style.removeProperty('--txt-override');

    $$('.theme-dot').forEach(b => b.classList.toggle('active', b.dataset.themeId === UI.theme));
    $$('#uiSize .btn').forEach(b => b.classList.toggle('active', +b.dataset.size === +UI.size));
    $$('[data-accent-pick]').forEach(b => b.classList.toggle('active', b.dataset.accentPick === UI.accent));
    const f = $('#uiFont'); if (f && f.value !== UI.font) { f.value = UI.font; if (f.tomselect) f.tomselect.setValue(UI.font, true); }
    const dn = $('#uiDense'); if (dn) dn.checked = !!UI.dense;
    const ac = $('#uiAccent'); if (ac && UI.accent) ac.value = UI.accent;
    const tc = $('#uiText');
    if (tc) {
      const cur = UI.text || getComputedStyle(d).getPropertyValue('--bs-body-color').trim();
      if (/^#[0-9a-f]{6}$/i.test(cur)) tc.value = cur;
    }
    if (save !== false) { try { localStorage.setItem('sp2ui', JSON.stringify(UI)); } catch (e) {} }
    measureHead();

    document.dispatchEvent(new CustomEvent('sp2:ui', {
      detail: { theme: UI.theme, kind: UI.kind, accent: UI.accent }
    }));
  }

  function onAccent(hex) {
    const m = /^#?([0-9a-f]{6})$/i.exec(hex || ''); if (!m) return '#fff';
    const n = parseInt(m[1], 16);
    const ch = c => { c /= 255; return c <= .03928 ? c / 12.92 : Math.pow((c + .055) / 1.055, 2.4); };
    const L = .2126 * ch((n >> 16) & 255) + .7152 * ch((n >> 8) & 255) + .0722 * ch(n & 255);
    return (1.05 / (L + .05)) >= ((L + .05) / 0.0555) ? '#fff' : '#12100a';
  }
  function hexRgb(hex) {
    const m = /^#?([0-9a-f]{6})$/i.exec(hex || ''); if (!m) return '';
    const n = parseInt(m[1], 16);
    return ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255);
  }

  $$('.theme-dot').forEach(b => b.addEventListener('click', () => {
    UI.theme = b.dataset.themeId; UI.kind = b.dataset.kind; UI.accent = ''; applyUI();
  }));
  $$('#uiSize .btn').forEach(b => b.addEventListener('click', () => { UI.size = +b.dataset.size; applyUI(); }));
  $$('[data-accent-pick]').forEach(b => b.addEventListener('click', () => { UI.accent = b.dataset.accentPick; applyUI(); }));
  const dnc = $('#uiDense'); if (dnc) dnc.addEventListener('change', () => { UI.dense = dnc.checked; applyUI(); });
  const aInp = $('#uiAccent'); if (aInp) aInp.addEventListener('input', () => { UI.accent = aInp.value; applyUI(); });
  const tInp = $('#uiText');   if (tInp) tInp.addEventListener('input', () => { UI.text = tInp.value; applyUI(); });
  const tAut = $('#uiTextAuto'); if (tAut) tAut.addEventListener('click', () => { UI.text = ''; applyUI(); });
  const rBtn = $('#uiReset');  if (rBtn) rBtn.addEventListener('click', () => { UI = Object.assign({}, DEF); applyUI(); });


  $$('[data-bs-tooltip], [data-bs-toggle="tooltip"]').forEach(el => {
    if (window.matchMedia('(hover: none)').matches) return;
    new bootstrap.Tooltip(el, { container: 'body', delay: { show: 400, hide: 0 }, trigger: 'hover' });
  });
  $$('.toast').forEach(el => bootstrap.Toast.getOrCreateInstance(el).show());


  $$('[data-copy]').forEach(b => {
    const i = b.querySelector('i');
    const was = i ? i.className : '';
    let timer = 0;
    b.addEventListener('click', async () => {
      const f = $(b.dataset.copy); if (!f) return;
      try { await navigator.clipboard.writeText(f.value); }
      catch (e) { if (f.select) { f.select(); try { document.execCommand('copy'); } catch (e2) {} } }
      if (!i) return;
      clearTimeout(timer);
      i.className = 'bi bi-check-lg';
      b.classList.add('btn-success'); b.classList.remove('btn-outline-secondary');
      timer = setTimeout(() => {
        i.className = was; b.classList.remove('btn-success'); b.classList.add('btn-outline-secondary');
      }, 1400);
    });
  });


  if (window.SimpleBar) {
    $$('[data-scroll]').forEach(el => {
      if (el.closest('.app-sidebar') && !window.matchMedia('(min-width: 992px)').matches) return;
      new SimpleBar(el, { autoHide: false });
    });
  }


  if (window.NProgress) {
    NProgress.configure({ showSpinner: false, trickleSpeed: 120, minimum: .12 });
    document.addEventListener('click', e => {
      const a = e.target.closest('a[href]');
      if (!a || a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const href = a.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
      if (a.dataset.confirm) return;
      if (/[?&]a=(download|download_gst|download_all|schemajson)\b/.test(href)) return;
      NProgress.start();
    });
    $$('form:not(.uploader)').forEach(f => f.addEventListener('submit', () => NProgress.start()));
    window.addEventListener('pageshow', () => NProgress.done());
  }


  if (window.TomSelect) {
    const tgt = $('#mTarget');
    if (tgt) new TomSelect(tgt, {
      placeholder: tr('Pick a country…'), maxOptions: 500, create: false,
      render: {
        option: (d, esc) => '<div class="option d-flex align-items-center gap-2"><span class="flex-grow-1">' +
          esc(d.text) + '</span><span class="ts-code">' + esc(d.$option ? d.$option.dataset.code || '' : '') +
          ' · ' + esc(d.$option ? d.$option.dataset.cid || '' : '') + '</span></div>',
        item: (d, esc) => '<div>' + esc(d.text) + '</div>'
      }
    });
    const uf = $('#uiFont');
    if (uf) new TomSelect(uf, {
      controlInput: null, maxOptions: 50,
      render: {
        option: (d, esc) => '<div class="option" data-font-id="' + esc(d.value) + '">' + esc(d.text) + '</div>',
        item:   (d, esc) => '<div data-font-id="' + esc(d.value) + '">' + esc(d.text) + '</div>'
      },
      onChange: v => { UI.font = v; applyUI(); }
    });
  }
  applyUI(false);


  const sb = $('#sidebar');
  if (sb) $$('a[href]', sb).forEach(a => a.addEventListener('click', () => {
    const inst = bootstrap.Offcanvas.getInstance(sb);
    if (inst) inst.hide();
  }));


  $$('[data-confirm]').forEach(el => el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) { e.preventDefault(); if (window.NProgress) NProgress.done(); }
  }));


  function liveFilter(inputSel, listSel, itemSel, emptySel, counterSel) {
    const inp = $(inputSel), list = $(listSel);
    if (!inp || !list) return;
    const items = $$(itemSel, list);
    const run = () => {
      const v = inp.value.trim().toUpperCase();
      let n = 0;
      items.forEach(a => {


        const hay = (a.dataset.field || a.textContent).trim().toUpperCase();
        const ok = hay.includes(v);
        a.classList.toggle('d-none', !ok); if (ok) n++;
      });
      const em = emptySel && $(emptySel); if (em) em.classList.toggle('d-none', n !== 0);
      const ct = counterSel && $(counterSel); if (ct) ct.textContent = n;
    };
    inp.addEventListener('input', run);
  }
  liveFilter('#tblSearch', '#tblList', '.list-group-item', '#tblEmpty', '#tblCount');
  liveFilter('#wlSearch',  '#wlList',  '.list-group-item');
  liveFilter('#fldSearch', '#fldList', '.field-row', '#fldEmpty');
  const fClr = $('#fldClear');
  if (fClr) fClr.addEventListener('click', () => { const i = $('#fldSearch'); i.value = ''; i.dispatchEvent(new Event('input')); i.focus(); });


  const fRow = $('#filterRow'), fTgl = $('#filterToggle');
  if (fTgl && fRow) fTgl.addEventListener('click', () => {
    fRow.classList.toggle('d-none');
    measureHead();
    const inp = fRow.querySelector('input:not([disabled])');
    if (!fRow.classList.contains('d-none') && inp) inp.focus();
  });


  function measureHead() {
    $$('.grid-wrap').forEach(w => {
      const r = w.querySelector('.th-row');
      if (r) w.style.setProperty('--th1-h', r.getBoundingClientRect().height + 'px');
    });
  }
  measureHead();
  let mhTimer = 0;
  window.addEventListener('resize', () => { clearTimeout(mhTimer); mhTimer = setTimeout(measureHead, 80); });
  if (window.ResizeObserver) {
    const ro = new ResizeObserver(measureHead);
    $$('.grid-wrap .th-row').forEach(r => ro.observe(r));
  }


  const cs = $('#cSearch'), cList = $('#cList');
  if (cs && cList) {
    const items = $$('.country-item', cList);
    const boxes = $$('input[type=checkbox]', cList);
    const picked = $('#cPicked');
    const count = () => { if (picked) picked.textContent = boxes.filter(b => b.checked).length; };
    cs.addEventListener('input', () => {
      const v = cs.value.trim().toLowerCase();
      items.forEach(el => el.classList.toggle('d-none', !el.textContent.toLowerCase().includes(v)));
    });
    boxes.forEach(b => b.addEventListener('change', count));
    const clr = $('#cClear');
    if (clr) clr.addEventListener('click', () => { boxes.forEach(b => b.checked = false); cs.value = ''; cs.dispatchEvent(new Event('input')); count(); });
    count();
  }



  $$('textarea.auto-grow').forEach(ta => {
    const size = () => { ta.style.height = 'auto'; ta.style.height = Math.min(260, ta.scrollHeight + 2) + 'px'; };
    const fit = (trim) => {
      const v = ta.value, t = v.replace(/[\r\n]+$/, '');
      const swap = trim && t !== v && document.activeElement !== ta;
      if (swap) ta.value = t;
      size();
      if (swap) ta.value = v;
    };
    ta.addEventListener('input', () => fit(false));
    ta.addEventListener('focus', () => fit(false));
    ta.addEventListener('blur',  () => fit(true));
    fit(true);
  });


  const mb = b => (b / 1048576).toFixed(1) + ' ' + tr('MB');

  $$('form.uploader').forEach(form => {
    const input = form.querySelector('input[type=file]');
    const zone  = form.querySelector('.dropzone');
    const fName = form.querySelector('.dz-file');

    const showName = () => {
      if (!fName) return;
      if (input.files.length) {

        const ic = document.createElement('i'); ic.className = 'bi bi-file-earmark-check';
        fName.replaceChildren(ic, document.createTextNode(
          ' ' + input.files[0].name + ' · ' + mb(input.files[0].size)));
        fName.classList.remove('d-none');
      } else fName.classList.add('d-none');
    };
    input.addEventListener('change', showName);

    if (zone) {
      zone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
      ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('drag'); }));
      ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('drag'); }));
      zone.addEventListener('drop', e => {
        if (!e.dataTransfer.files.length) return;
        input.files = e.dataTransfer.files; showName();
      });
    }

    form.addEventListener('submit', ev => {
      ev.preventDefault();
      if (!input.files.length) { input.click(); return; }

      const file   = input.files[0];
      const wrap   = form.querySelector('.progress');
      const bar    = form.querySelector('.progress-bar');
      const status = form.querySelector('.up-status');
      const btn    = form.querySelector('button[type=submit], button:not([type])');

      wrap.classList.remove('d-none');
      bar.classList.remove('bg-danger');
      bar.classList.add('progress-bar-animated');
      if (btn) btn.disabled = true;
      input.disabled = true;
      status.textContent = tr('Preparing…');

      const fd = new FormData();
      fd.append(form.dataset.field, file);

      const started = Date.now();
      const xhr = new XMLHttpRequest();
      xhr.open('POST', form.dataset.action, true);

      xhr.upload.onprogress = e => {
        if (!e.lengthComputable) return;
        const pct = Math.round(e.loaded / e.total * 100);
        const sec = (Date.now() - started) / 1000;
        const speed = sec > 0 ? e.loaded / sec : 0;
        bar.style.width = pct + '%';
        bar.textContent = pct + '%';
        wrap.setAttribute('aria-valuenow', pct);
        status.textContent = mb(e.loaded) + ' ' + tr('of') + ' ' + mb(e.total) + (speed ? ' · ' + mb(speed) + '/s' : '');
      };
      xhr.upload.onload = () => {
        bar.style.width = '100%'; bar.textContent = '100%';
        status.textContent = tr('Sent, opening…');
      };
      xhr.onload = () => {
        if (btn) btn.disabled = false; input.disabled = false;
        let r = null;
        try { r = JSON.parse(xhr.responseText); } catch (e) {}
        if (r && r.ok) {
          bar.classList.remove('progress-bar-animated');
          if (window.NProgress) NProgress.start();
          location.href = r.url;
        } else {
          bar.classList.add('bg-danger');
          bar.classList.remove('progress-bar-animated');
          status.textContent = (r && r.error) ? r.error : (tr('Server error') + ' (HTTP ' + xhr.status + ')');
        }
      };
      xhr.onerror = () => {
        if (btn) btn.disabled = false; input.disabled = false;
        bar.classList.add('bg-danger');
        status.textContent = tr('Connection failed');
      };
      xhr.send(fd);
    });
  });


  const dataEl = $('#gridData');
  if (!dataEl) return;
  const GRID = JSON.parse(dataEl.textContent);
  const modalEl = $('#rowModal');

  $$('[data-null-for]').forEach(cb => cb.addEventListener('change', () => {
    const inp = $('#v_' + cb.dataset.nullFor);
    if (inp) inp.disabled = cb.checked;
  }));

  function fillRow(d) {
    $('#rowId').value = d.id;
    const lbl = $('#rowIdLabel'); if (lbl) lbl.textContent = d.id;
    GRID.cols.forEach(c => {
      const inp = $('#v_' + c.n), nul = $('#n_' + c.n);
      const val = d.v[c.n];
      const isNull = (val === null || val === undefined);
      if (inp) inp.value = isNull ? '' : val;
      if (nul) { nul.checked = isNull; if (inp) inp.disabled = isNull; }
      if (c.stid) { const l = $('#lbl_' + c.n); if (l) l.textContent = (d.l && d.l[c.n]) ? d.l[c.n] : ''; }
    });
  }

  window.SP2fillRow = fillRow;

  $$('[data-edit-row]').forEach(btn => btn.addEventListener('click', async () => {
    const id = btn.dataset.rowid;
    btn.classList.add('busy');
    if (window.NProgress) NProgress.start();
    try {
      const url = 'index.php?a=row&ajax=1&t=' + encodeURIComponent(GRID.table) + '&rowid=' + encodeURIComponent(id);
      const r = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
      const d = await r.json();
      if (!d.ok) { alert(d.error || 'error'); return; }
      fillRow(d);
      const s = $('#fldSearch'); if (s && s.value) { s.value = ''; s.dispatchEvent(new Event('input')); }
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } catch (e) {
      alert('' + e);
    } finally {
      btn.classList.remove('busy');
      if (window.NProgress) NProgress.done();
    }
  }));
})();
