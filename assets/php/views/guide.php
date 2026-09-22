<?php



defined('SHOT_SIZE') || define('SHOT_SIZE', [
    '01-upload.webp' => [1280, 729],
    '02-overview.webp' => [1280, 800],
    '03-table.webp' => [1280, 800],
    '04-edit-row.webp' => [1280, 800],
    '05-structure.webp' => [1280, 800],
    '06-map-country.webp' => [1280, 800],
    '07-map-language.webp' => [1280, 800],
    '08-map-religion.webp' => [1280, 800],
    '09-map-government.webp' => [1280, 800],
    '10-map-population.webp' => [1280, 800],
    '11-map-relations.webp' => [1280, 800],
    '12-map-treaty.webp' => [1280, 800],
    '13-map-layers-menu.webp' => [1280, 800],
    '14-map-force.webp' => [1280, 800],
    '15-merge.webp' => [1280, 800],
    '16-replace.webp' => [1280, 800],
    '17-check.webp' => [1280, 800],
    '18-find.webp' => [1280, 800],
    '19-log.webp' => [1280, 800],
    '20-languages.webp' => [1280, 800],
    '21-appearance.webp' => [1280, 800],
    '22-project.webp' => [1280, 800],
    '23-mobile.webp' => [414, 860],
]);


$GDIR = $ASSETS . '/guide';
$found = [];
foreach ((array)@scandir($GDIR) as $f) {
    if (preg_match('/^([a-z]{2})\.php$/', (string)$f, $m)) $found[$m[1]] = true;
}

$have = [];
foreach (array_keys(LANGS) as $code) if (isset($found[$code])) $have[] = $code;
foreach (array_keys($found) as $code) if (!in_array($code, $have, true)) $have[] = $code;
if (!$have) { $have = ['en']; }


$glAsked = get_s('gl');
$glPicked = in_array($glAsked, $have, true);
$fallback = in_array('en', $have, true) ? 'en' : $have[0];
$gl = $glPicked ? $glAsked : (in_array(lang(), $have, true) ? lang() : $fallback);

$glFallback = !$glPicked && !in_array(lang(), $have, true);


$loadGuide = function (string $code) use ($GDIR) {
    $f = $GDIR . '/' . $code . '.php';
    if (!is_file($f)) return null;
    try { $g = require $f; } catch (Throwable $e) { return null; }
    if (!is_array($g) || !is_array($g['sections'] ?? null) || !$g['sections']) return null;
    $g['name'] = (string)($g['name'] ?? strtoupper($code));
    $g['lead'] = (string)($g['lead'] ?? '');
    return $g;
};
$G = $loadGuide($gl);
if ($G === null && $gl !== $fallback) { $gl = $fallback; $G = $loadGuide($gl); $glFallback = true; }
if ($G === null) {
    echo '<div class="alert alert-danger"><i class="bi bi-x-octagon-fill"></i> ',
         h(t('The guide text is missing or unreadable (%s).', 'assets/guide/' . $gl . '.php')),
         '</div>';
    return;
}
$SECTIONS = $G['sections'];

$link = function (string $code): string {
    return '?' . http_build_query(array_merge($_GET, ['a' => 'guide', 'gl' => $code]));
};
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-book"></i> <?= h(t('Guide')) ?></h1>
    <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis">
      SuperPower 2 · DATABASE.GDB · StringTable.*.gst</span>

    <?php if (count($have) > 1):

          $nameOf = fn(string $c): string => LANGS[$c] ?? strtoupper($c); ?>
      <div class="dropdown ms-auto guide-lang">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                data-bs-toggle="dropdown" aria-expanded="false"
                aria-label="<?= h(t('Guide language')) ?>">
          <i class="bi bi-translate"></i> <?= h($nameOf($gl)) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header"><?= h(t('Guide language')) ?></h6></li>
          <?php foreach ($have as $code): ?>
            <li><a class="dropdown-item d-flex align-items-center gap-2<?= $code === $gl ? ' active' : '' ?>"
                   href="<?= h($link($code)) ?>" hreflang="<?= h($code) ?>"
                   <?= $code === $gl ? 'aria-current="true"' : '' ?>>
              <span class="badge text-bg-secondary lang-badge"><?= strtoupper($code) ?></span><?= h($nameOf($code)) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
  <p class="page-sub mb-0" lang="<?= h($gl) ?>"><?= h($G['lead']) ?>
    <?php if ($glFallback): ?>
      <span class="text-body-secondary">· <?= h(t('The guide is not translated into your language yet — showing %s.', $G['name'])) ?></span>
    <?php endif; ?>
  </p>
</div>

<div class="guide-wrap" lang="<?= h($gl) ?>">

  <nav class="guide-toc" id="guideToc" aria-label="<?= h(t('Contents')) ?>">
    <div class="guide-toc-inner">
      <div class="guide-toc-head"><i class="bi bi-list-ul"></i> <?= h(t('Contents')) ?></div>
      <ul class="nav nav-pills flex-column">
        <?php foreach ($SECTIONS as $s): ?>
          <li class="nav-item"><a class="nav-link" href="#<?= h($s['id']) ?>">
            <i class="bi bi-<?= h($s['icon']) ?>"></i> <span><?= h($s['h']) ?></span></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>

  <article class="guide-body">
    <?php foreach ($SECTIONS as $s): ?>
      <section id="<?= h($s['id']) ?>">
        <h2><i class="bi bi-<?= h($s['icon']) ?>"></i> <?= h($s['h']) ?></h2>
        <?php foreach ($s['body'] as $b):
          if (is_string($b)) { echo $b, "\n"; continue; }
          if ($b[0] === 'note'): ?>
            <div class="guide-note"><i class="bi bi-<?= h($b[1]) ?>"></i><div><?= $b[2] ?></div></div>
          <?php elseif ($b[0] === 'fig'):
            [, $file, $alt, $cap] = $b + [3 => ''];
            [$w, $hh] = SHOT_SIZE[$file] ?? [1280, 800]; ?>
            <figure class="guide-fig">
              <button type="button" class="guide-zoom" data-guide-img="assets/img/guide/<?= h($file) ?>"
                      data-guide-cap="<?= h($cap ?: $alt) ?>" aria-label="<?= h($alt) ?>">
                <img src="assets/img/guide/<?= h($file) ?>" alt="<?= h($alt) ?>"
                     width="<?= $w ?>" height="<?= $hh ?>" loading="lazy" decoding="async">
                <span class="guide-zoom-hint"><i class="bi bi-arrows-fullscreen"></i></span>
              </button>
              <?php if ($cap !== ''): ?><figcaption><?= h($cap) ?></figcaption><?php endif; ?>
            </figure>
          <?php endif;
        endforeach; ?>
      </section>
    <?php endforeach; ?>
  </article>
</div>

<div class="modal fade" id="guideLightbox" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="guideLightboxCap"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(t('Close')) ?>"></button>
      </div>
      <div class="modal-body d-flex align-items-center justify-content-center p-2">
        <img id="guideLightboxImg" src="" alt="" class="guide-lightbox-img">
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const el = document.getElementById('guideLightbox');
  if (!el) return;
  const img = document.getElementById('guideLightboxImg');
  const cap = document.getElementById('guideLightboxCap');
  document.querySelectorAll('[data-guide-img]').forEach(b => b.addEventListener('click', () => {
    img.src = b.dataset.guideImg; img.alt = cap.textContent = b.dataset.guideCap || '';
    bootstrap.Modal.getOrCreateInstance(el).show();
  }));
  el.addEventListener('hidden.bs.modal', () => { img.removeAttribute('src'); });


  const links = [...document.querySelectorAll('#guideToc .nav-link')];
  const secs  = links.map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);
  if (secs.length !== links.length || !secs.length) return;
  const box = document.querySelector('.guide-toc-inner');
  let cur = -1, tick = 0;

  function pick() {
    const line = innerHeight * 0.28;
    const atEnd = innerHeight + scrollY >= document.documentElement.scrollHeight - 4;
    let i = 0;
    if (atEnd) {
      i = secs.length - 1;
    } else {
      for (let k = 0; k < secs.length; k++) {
        if (secs[k].getBoundingClientRect().top <= line) i = k; else break;
      }
    }
    if (i === cur) return;
    cur = i;
    links.forEach((a, k) => a.classList.toggle('active', k === i));

    if (box && box.scrollHeight > box.clientHeight) {
      const r = links[i].getBoundingClientRect(), br = box.getBoundingClientRect();
      if (r.top < br.top + 4) box.scrollTop -= (br.top + 4 - r.top);
      else if (r.bottom > br.bottom - 4) box.scrollTop += (r.bottom - br.bottom + 4);
    }
  }
  const onScroll = () => { if (!tick) tick = requestAnimationFrame(() => { tick = 0; pick(); }); };
  addEventListener('scroll', onScroll, { passive: true });
  addEventListener('resize', onScroll);


  let hold = 0;
  links.forEach((a, k) => a.addEventListener('click', () => {
    cur = k;
    links.forEach((x, j) => x.classList.toggle('active', j === k));
    clearTimeout(hold);
    hold = setTimeout(() => { cur = -1; pick(); }, 700);
  }));
  addEventListener('hashchange', () => {
    const k = links.findIndex(a => a.getAttribute('href') === location.hash);
    if (k >= 0) { cur = k; links.forEach((x, j) => x.classList.toggle('active', j === k)); }
  });
  pick();
})();
</script>
