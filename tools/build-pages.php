<?php
declare(strict_types=1);

/*
 * Builds the static GitHub Pages site into docs/.
 *
 *   php tools/build-pages.php
 *
 * Output: docs/index.html            English landing page
 *         docs/guide/<code>.html     the illustrated guide, one file per language
 *         docs/guide/index.html      copy of the English guide
 *         docs/assets/               stylesheet, icon subset, screenshots
 *
 * Everything is generated from assets/guide/*.php and assets/img/guide/*.webp,
 * so re-run this script whenever the guide or the screenshots change.
 */

const LIVE  = 'http://editor.kraineuolek.com/super-power-2/';
const REPO  = 'https://github.com/OleksandrKhrashchevskyi/super-power-2-editor';
const TITLE = 'SP2 DB Editor';

$root = dirname(__DIR__);
$docs = $root . '/docs';

/* ---------------------------------------------------------------- helpers */

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function rm_rf(string $d): void {
    if (!is_dir($d)) return;
    foreach (scandir($d) as $n) {
        if ($n === '.' || $n === '..') continue;
        $p = "$d/$n";
        is_dir($p) ? rm_rf($p) : unlink($p);
    }
    rmdir($d);
}

function put(string $path, string $body): void {
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, $body);
}

/* ------------------------------------------------------------- input data */

$langs = [
    'en' => 'English',  'de' => 'Deutsch',    'es' => 'Español',
    'fr' => 'Français', 'it' => 'Italiano',   'nl' => 'Nederlands',
    'pt' => 'Português','uk' => 'Українська', 'ru' => 'Русский',
];

$guides = [];
foreach ($langs as $code => $_) {
    $file = "$root/assets/guide/$code.php";
    if (!is_file($file)) { fwrite(STDERR, "missing $file\n"); exit(1); }
    $guides[$code] = include $file;
}

/* Image dimensions, so the page does not jump while screenshots load. */
$dims = [];
foreach (glob("$root/assets/img/guide/*.webp") as $f) {
    $s = @getimagesize($f);
    if ($s) $dims[basename($f)] = [$s[0], $s[1]];
}

/* ------------------------------------------------- icon subset stylesheet */

$iconCss = (string)@file_get_contents("$root/assets/vendor/icons/bootstrap-icons.min.css");
$used = [];
foreach ($guides['en']['sections'] as $s) $used[$s['icon']] = true;
foreach (['book', 'github', 'box-arrow-up-right', 'translate', 'check2',
          'hdd-network', 'code-slash', 'shield-check'] as $extra) $used[$extra] = true;

$rules = '';
foreach (array_keys($used) as $name) {
    if (preg_match('/\.bi-' . preg_quote($name, '/') . '::before\s*\{\s*content\s*:\s*"([^"]+)"/', $iconCss, $m)) {
        $rules .= ".bi-$name::before{content:\"{$m[1]}\"}\n";
    } else {
        fwrite(STDERR, "warning: icon '$name' not found in bootstrap-icons.min.css\n");
    }
}

$iconsheet = <<<CSS
/* Subset of Bootstrap Icons 1.13.1 (MIT) - only the glyphs this site uses. */
@font-face{
  font-family:"bootstrap-icons";
  font-display:block;
  src:url("fonts/bootstrap-icons.woff2") format("woff2");
}
.bi::before,[class^="bi-"]::before,[class*=" bi-"]::before{
  display:inline-block;
  font-family:"bootstrap-icons"!important;
  font-style:normal;font-weight:normal!important;font-variant:normal;
  text-transform:none;line-height:1;vertical-align:-.125em;
  -webkit-font-smoothing:antialiased;
}

CSS;

/* ------------------------------------------------------------ stylesheet */

$css = <<<'CSS'
:root{
  --bg:#12151b; --fg:#d8dee9; --bd:#262c38; --p1:#171b23; --p2:#1c2130;
  --mu:#8a93a6; --ac:#4f8cff; --ac-soft:rgba(79,140,255,.14);
  --maxw:820px;
}
@media (prefers-color-scheme: light){
  :root:not([data-theme="dark"]){
    --bg:#f6f7f9; --fg:#22262e; --bd:#dfe3ea; --p1:#ffffff; --p2:#f0f2f6;
    --mu:#595f6b; --ac:#2563eb; --ac-soft:rgba(37,99,235,.10);
  }
}
:root[data-theme="dark"]{
  --bg:#12151b; --fg:#d8dee9; --bd:#262c38; --p1:#171b23; --p2:#1c2130;
  --mu:#8a93a6; --ac:#4f8cff; --ac-soft:rgba(79,140,255,.14);
}

*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{
  margin:0;background:var(--bg);color:var(--fg);
  font:16px/1.65 system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",sans-serif;
  overflow-x:hidden;
}
a{color:var(--ac);text-decoration:none}
a:hover{text-decoration:underline}
code{
  font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
  font-size:.875em;background:var(--p2);border:1px solid var(--bd);
  border-radius:4px;padding:.08em .34em;
}
pre{
  background:var(--p1);border:1px solid var(--bd);border-radius:10px;
  padding:14px 16px;overflow-x:auto;font-size:.875rem;line-height:1.55;
}
pre code{background:none;border:0;padding:0;font-size:inherit}
img{max-width:100%;height:auto;display:block}
hr{border:0;border-top:1px solid var(--bd);margin:40px 0}

.wrap{max-width:var(--maxw);margin:0 auto;padding:0 16px}

/* ---- top bar ---- */
.top{
  position:sticky;top:0;z-index:20;background:var(--bg);
  border-bottom:1px solid var(--bd);
}
.top-in{
  max-width:1180px;margin:0 auto;padding:10px 16px;
  display:flex;align-items:center;gap:16px;flex-wrap:wrap;
}
.brand{font-weight:650;color:var(--fg);letter-spacing:-.01em}
.brand:hover{text-decoration:none}
.top nav{margin-left:auto;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.top nav a{
  color:var(--mu);font-size:.9rem;padding:5px 10px;border-radius:7px;
}
.top nav a:hover{color:var(--fg);background:var(--p2);text-decoration:none}
.top nav a.cta{background:var(--ac);color:#fff;font-weight:550}
.top nav a.cta:hover{opacity:.9;background:var(--ac);color:#fff}

/* ---- hero ---- */
.hero{padding:64px 0 18px;text-align:center}
.hero + .section{padding-top:14px}
.hero h1{
  font-size:clamp(2rem,6vw,3rem);line-height:1.1;margin:0 0 14px;
  letter-spacing:-.025em;font-weight:700;
}
.hero .lead{font-size:clamp(1.05rem,2.6vw,1.2rem);color:var(--mu);margin:0 auto 10px;max-width:620px}
.hero .sub{color:var(--mu);font-size:.95rem;margin:0 auto 28px;max-width:620px}
.btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 18px;border-radius:9px;border:1px solid var(--bd);
  background:var(--p1);color:var(--fg);font-weight:550;font-size:.95rem;
}
.btn:hover{background:var(--p2);text-decoration:none;color:var(--fg)}
.btn-primary{background:var(--ac);border-color:var(--ac);color:#fff}
.btn-primary:hover{background:var(--ac);color:#fff;opacity:.9}

/* ---- shared blocks ---- */
.section{padding:44px 0}
.section h2{
  font-size:1.5rem;margin:0 0 6px;letter-spacing:-.02em;font-weight:650;
}
.section .intro{color:var(--mu);margin:0 0 26px}

.cards{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(230px,1fr))}
.card{
  background:var(--p1);border:1px solid var(--bd);border-radius:12px;padding:18px;
}
.card h3{margin:0 0 6px;font-size:1rem;font-weight:600;display:flex;align-items:center;gap:8px}
.card h3 .bi::before{color:var(--ac)}
.card p{margin:0;color:var(--mu);font-size:.925rem}

figure{margin:22px 0}
figure img{
  border:1px solid var(--bd);border-radius:12px;background:var(--p1);width:100%;
}
figcaption{color:var(--mu);font-size:.875rem;margin-top:9px}

.note{
  display:flex;gap:11px;background:var(--ac-soft);border:1px solid var(--bd);
  border-radius:10px;padding:13px 15px;margin:20px 0;font-size:.94rem;
}
.note .bi::before{color:var(--ac)}
.note p{margin:0}

.stats{
  display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  margin:24px 0 0;
}
.stat{background:var(--p1);border:1px solid var(--bd);border-radius:12px;padding:16px}
.stat b{display:block;font-size:1.45rem;font-weight:650;letter-spacing:-.02em}
.stat span{color:var(--mu);font-size:.875rem}

.badge{
  display:inline-block;background:var(--p2);border:1px solid var(--bd);
  border-radius:5px;padding:.1em .45em;font-size:.78em;color:var(--mu);
  vertical-align:.08em;
}
.text-bg-secondary{background:var(--p2)}
.text-body-secondary{color:var(--mu)}
.mb-0{margin-bottom:0}

/* ---- guide ---- */
.guide{display:flex;gap:40px;max-width:1180px;margin:0 auto;padding:0 16px}
.toc{
  width:236px;flex:0 0 236px;position:sticky;top:60px;align-self:flex-start;
  max-height:calc(100vh - 80px);overflow-y:auto;padding:28px 0 40px;
}
.toc ol{list-style:none;margin:0;padding:0;counter-reset:s}
.toc li{counter-increment:s}
.toc a{
  display:block;color:var(--mu);font-size:.88rem;padding:5px 10px 5px 30px;
  border-radius:7px;line-height:1.4;position:relative;
}
.toc a::before{
  content:counter(s) ".";color:var(--bd);
  position:absolute;left:10px;width:16px;text-align:right;
}
.toc a:hover{color:var(--fg);background:var(--p2);text-decoration:none}
.toc a.on{color:var(--fg);background:var(--ac-soft)}
.toc a.on::before{color:var(--ac)}
.doc{flex:1 1 auto;min-width:0;max-width:760px;padding:28px 0 60px}
.doc h1{font-size:2rem;margin:0 0 8px;letter-spacing:-.025em;font-weight:700}
.doc .lead{color:var(--mu);margin:0 0 6px;font-size:1.05rem}
.doc section{scroll-margin-top:66px;padding-top:34px}
.doc section h2{
  font-size:1.3rem;margin:0 0 14px;font-weight:650;letter-spacing:-.015em;
  display:flex;align-items:center;gap:10px;
}
.doc section h2 .bi::before{color:var(--ac);font-size:.9em}
.doc ul,.doc ol{padding-left:22px}
.doc li{margin:5px 0}

.langbar{display:flex;gap:6px;flex-wrap:wrap;margin:18px 0 8px}
.langbar a{
  font-size:.85rem;color:var(--mu);padding:4px 9px;border-radius:7px;
  border:1px solid var(--bd);
}
.langbar a:hover{color:var(--fg);background:var(--p2);text-decoration:none}
.langbar a.on{color:#fff;background:var(--ac);border-color:var(--ac)}

/* ---- footer ---- */
footer{border-top:1px solid var(--bd);padding:30px 0 44px;color:var(--mu);font-size:.875rem}
footer .wrap{max-width:1180px}
footer p{margin:0 0 8px}
.flag{display:inline-block;width:18px;height:12px;vertical-align:-1px;border-radius:2px;overflow:hidden}

@media (max-width:980px){
  .toc{display:none}
  .guide{display:block}
  .doc{max-width:none;margin:0 auto}
}
@media (prefers-reduced-motion:no-preference){
  html{scroll-behavior:smooth}
}
CSS;

/* ------------------------------------------------------------ page shell */

function page(string $title, string $desc, string $body, string $base, string $lang = 'en', string $extraHead = ''): string
{
    $t = h($title); $d = h($desc);
    return <<<HTML
<!doctype html>
<html lang="{$lang}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t}</title>
<meta name="description" content="{$d}">
<meta property="og:title" content="{$t}">
<meta property="og:description" content="{$d}">
<meta property="og:type" content="website">
<meta name="color-scheme" content="dark light">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Crect width='16' height='16' rx='3' fill='%234f8cff'/%3E%3Cg fill='%23fff'%3E%3Crect x='3' y='4' width='10' height='2' rx='.6'/%3E%3Crect x='3' y='7' width='10' height='2' rx='.6'/%3E%3Crect x='3' y='10' width='6' height='2' rx='.6'/%3E%3C/g%3E%3C/svg%3E">
<link rel="stylesheet" href="{$base}assets/site.css">
<link rel="stylesheet" href="{$base}assets/icons.css">
{$extraHead}</head>
<body>
{$body}
</body>
</html>

HTML;
}

function topbar(string $base, string $here = ''): string
{
    $live = LIVE; $repo = REPO; $t = TITLE;
    $g = $here === 'guide' ? ' class="on"' : '';
    return <<<HTML
<header class="top"><div class="top-in">
  <a class="brand" href="{$base}index.html">{$t}</a>
  <nav>
    <a href="{$base}guide/index.html"{$g}>Guide</a>
    <a href="{$repo}">Source</a>
    <a class="cta" href="{$live}">Open the editor</a>
  </nav>
</div></header>
HTML;
}

function footerHtml(): string
{
    $repo = REPO;
    return <<<HTML
<footer><div class="wrap">
  <p>MIT licence. Bundled third-party assets keep their own licences &mdash;
     see <a href="{$repo}/blob/main/THIRD-PARTY-NOTICES.md">THIRD-PARTY-NOTICES</a>.</p>
  <p>Unofficial fan-made tool. Not affiliated with, endorsed by or connected to GolemLabs,
     DreamCatcher Interactive or any current rights holder of SuperPower&nbsp;2.
     No game data is distributed here &mdash; you supply your own files.</p>
  <p><svg class="flag" viewBox="0 0 3 2" aria-hidden="true"><rect width="3" height="1" fill="#0057b7"/><rect y="1" width="3" height="1" fill="#ffd700"/></svg>
     Made in Ukraine.</p>
</div></footer>
HTML;
}

/* ----------------------------------------------------------- guide render */

function renderBody(array $body, array $dims): string
{
    $out = '';
    foreach ($body as $item) {
        if (is_string($item)) { $out .= $item . "\n"; continue; }
        if (!is_array($item) || !isset($item[0])) continue;

        if ($item[0] === 'fig') {
            $file = (string)($item[1] ?? '');
            $alt  = (string)($item[2] ?? '');
            $cap  = (string)($item[3] ?? '');
            $wh   = $dims[$file] ?? null;
            $size = $wh ? ' width="' . $wh[0] . '" height="' . $wh[1] . '"' : '';
            $out .= '<figure><img src="../assets/img/' . h($file) . '" alt="' . h($alt) . '"'
                  . $size . ' loading="lazy" decoding="async">'
                  . ($cap !== '' ? '<figcaption>' . $cap . '</figcaption>' : '')
                  . "</figure>\n";
        } elseif ($item[0] === 'note') {
            $icon = (string)($item[1] ?? 'info-circle');
            $text = (string)($item[2] ?? '');
            $out .= '<div class="note"><i class="bi bi-' . h($icon) . '" aria-hidden="true"></i>'
                  . '<p>' . $text . "</p></div>\n";
        }
    }
    return $out;
}

function langbar(array $langs, string $current): string
{
    $out = '<div class="langbar">';
    foreach ($langs as $code => $name) {
        $on = $code === $current ? ' class="on"' : '';
        $out .= '<a href="' . h($code) . '.html" hreflang="' . h($code) . '"' . $on . '>' . h($name) . '</a>';
    }
    return $out . '</div>';
}

/* --------------------------------------------------------------- building */

foreach (["$docs/assets", "$docs/guide"] as $d) rm_rf($d);   // docs/app is built by tools/build-wasm.mjs
@unlink("$docs/index.html");
@mkdir("$docs/assets/img", 0777, true);
@mkdir("$docs/assets/fonts", 0777, true);
@mkdir("$docs/guide", 0777, true);

put("$docs/.nojekyll", '');
put("$docs/assets/site.css", $css);
put("$docs/assets/icons.css", $iconsheet . $rules);

foreach (glob("$root/assets/img/guide/*.webp") as $f) copy($f, "$docs/assets/img/" . basename($f));
copy("$root/assets/vendor/icons/fonts/bootstrap-icons.woff2", "$docs/assets/fonts/bootstrap-icons.woff2");

/* ---- guide pages ---- */

foreach ($guides as $code => $g) {
    $toc = '<ol>';
    $secs = '';
    foreach ($g['sections'] as $s) {
        $id = h((string)$s['id']);
        $toc .= '<li><a href="#' . $id . '">' . h((string)$s['h']) . '</a></li>';
        $secs .= '<section id="' . $id . '"><h2><i class="bi bi-' . h((string)$s['icon']) . '" aria-hidden="true"></i>'
               . h((string)$s['h']) . "</h2>\n" . renderBody($s['body'], $dims) . "</section>\n";
    }
    $toc .= '</ol>';

    $title = TITLE . ' &mdash; guide';
    $body = topbar('../', 'guide') . "\n<div class=\"guide\">\n"
          . '<aside class="toc" aria-label="Contents">' . $toc . "</aside>\n"
          . '<main class="doc">'
          . '<h1>' . h(TITLE) . '</h1>'
          . '<p class="lead">' . h((string)$g['lead']) . '</p>'
          . langbar($langs, $code)
          . $secs
          . "</main>\n</div>\n" . footerHtml();

    $script = <<<'JS'
<script>
(function(){
  var links=[].slice.call(document.querySelectorAll('.toc a'));
  var secs=links.map(function(a){return document.querySelector(a.getAttribute('href'));});
  function mark(){
    var y=window.scrollY+120,i=0;
    for(var k=0;k<secs.length;k++){ if(secs[k]&&secs[k].offsetTop<=y) i=k; }
    links.forEach(function(a,k){ a.classList.toggle('on',k===i); });
  }
  window.addEventListener('scroll',mark,{passive:true});
  mark();
})();
</script>
JS;

    $html = page(
        strip_tags(TITLE . ' — guide (' . $langs[$code] . ')'),
        strip_tags((string)$g['lead']),
        $body . $script,
        '../',
        $code
    );
    put("$docs/guide/$code.html", $html);
}
copy("$docs/guide/en.html", "$docs/guide/index.html");

/* ---- landing page ---- */

$live = LIVE; $repo = REPO;
$d = fn(string $f) => isset($dims[$f]) ? ' width="' . $dims[$f][0] . '" height="' . $dims[$f][1] . '"' : '';

$landing = topbar('') . <<<HTML

<div class="hero"><div class="wrap">
  <h1>Edit the SuperPower&nbsp;2 database in your browser</h1>
  <p class="lead">SP2 DB Editor opens <code>DATABASE.GDB</code> and the
     <code>StringTable.*.gst</code> text files, lets you change them, and hands them back
     ready for the game.</p>
  <p class="sub">Nothing to install. Your files are copies &mdash; the game's originals are never touched.</p>
  <div class="btns">
    <a class="btn btn-primary" href="app/index.html">Run it in your browser</a>
    <a class="btn" href="{$live}">Open the hosted editor</a>
    <a class="btn" href="guide/index.html"><i class="bi bi-book" aria-hidden="true"></i>Read the guide</a>
    <a class="btn" href="{$repo}"><i class="bi bi-github" aria-hidden="true"></i>Source on GitHub</a>
  </div>
</div></div>

<div class="section"><div class="wrap">
  <figure>
    <img src="assets/img/02-overview.webp" alt="The database overview screen"{$d('02-overview.webp')} decoding="async">
    <figcaption>28 tables, about 62,800 rows. The badge on the left means a language file is attached,
      so numeric IDs are shown as real names.</figcaption>
  </figure>
</div></div>

<div class="section"><div class="wrap">
  <h2>It runs without a server</h2>
  <p class="intro">The same PHP that powers the hosted editor is compiled to WebAssembly and started
     inside your tab. A service worker hands every request to it, so the editor behaves exactly as it
     does on a server &mdash; except there is no server. Your database is never uploaded anywhere;
     it is opened on your own machine.</p>
  <div class="stats">
    <div class="stat"><b>~7 MB</b><span>downloaded once, then cached by the browser</span></div>
    <div class="stat"><b>1.4 s</b><span>to parse a 9.5 MB DATABASE.GDB in the browser</span></div>
    <div class="stat"><b>identical</b><span>bytes written, checked against native PHP</span></div>
  </div>
  <p class="intro" style="margin-top:22px">Work lives in the tab only, so download your files before
     closing it. For long sessions, or to share one instance with other people, use the
     <a href="{$live}">hosted editor</a>.</p>
  <div class="btns" style="justify-content:flex-start;margin-top:18px">
    <a class="btn btn-primary" href="app/index.html">Run it in your browser</a>
  </div>
</div></div>

<div class="section"><div class="wrap">
  <h2>What it does</h2>
  <p class="intro">Everything below works on the file you upload, in the browser, with no game installed.</p>
  <div class="cards">
    <div class="card"><h3><i class="bi bi-table" aria-hidden="true"></i>Browse and edit</h3>
      <p>All 28 tables with paging, filters and sorting. Edit any value that is not an index or a BLOB.</p></div>
    <div class="card"><h3><i class="bi bi-globe-americas" aria-hidden="true"></i>World map</h3>
      <p>Thirteen colourings &mdash; ownership, language, religion, government, relations, treaties,
         troops, population. Click a region to edit it, drag to select many and hand them to another country.</p></div>
    <div class="card"><h3><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Merge countries</h3>
      <p>Territory, troops, missiles, parties, designs, treaty membership and covert cells all move
         together, with a preview before anything is written.</p></div>
    <div class="card"><h3><i class="bi bi-translate" aria-hidden="true"></i>Language files</h3>
      <p>Edit <code>StringTable.*.gst</code> dictionaries. With one open, tables show real names
         instead of numeric STIDs.</p></div>
    <div class="card"><h3><i class="bi bi-clipboard-check" aria-hidden="true"></i>Check and search</h3>
      <p>Find genuinely broken references, missing capitals and duplicate IDs. Search all tables
         and the game texts from one field.</p></div>
    <div class="card"><h3><i class="bi bi-clock-history" aria-hidden="true"></i>Log and undo</h3>
      <p>Every write is recorded with its previous values and can be put back. A copy of the file
         is kept before each edit.</p></div>
  </div>
</div></div>

<div class="section"><div class="wrap">
  <h2>The map is built from the database</h2>
  <p class="intro">The game stores no region geometry &mdash; only city coordinates and who owns what.
     The map is rasterised from those, constrained by public-domain Natural Earth outlines.
     Change an owner and it recolours at once.</p>
  <figure>
    <img src="assets/img/07-map-language.webp" alt="World map coloured by dominant language"{$d('07-map-language.webp')} loading="lazy" decoding="async">
    <figcaption>Dominant language per region. This view also exposes the game's own mistakes &mdash;
      the dominant language of Brazil in the database is English.</figcaption>
  </figure>
</div></div>

<div class="section"><div class="wrap">
  <h2>Why it is written in PHP</h2>
  <p class="intro">The database is a Firebird 1.5 file from January 2005. That engine exists only for
     Windows, only 32-bit, so it cannot run on a Linux host at all. The editor therefore reads and
     writes the file format directly: 4&nbsp;KB pages, slot tables, RLE records, and the schema taken
     from the file's own system tables rather than hard-coded.</p>
  <p class="intro">Everything was cross-checked against a real Firebird 1.5 engine on the same database.</p>
  <div class="stats">
    <div class="stat"><b>669,369</b><span>values read across 28 tables &mdash; 0 discrepancies</span></div>
    <div class="stat"><b>1,382</b><span>values written back &mdash; the engine read exactly what PHP wrote</span></div>
    <div class="stat"><b>1 of 16,684</b><span>values changed by a single-field edit in COUNTRY</span></div>
  </div>
</div></div>

<div class="section"><div class="wrap">
  <h2>Run your own copy</h2>
  <p class="intro">Pure PHP 7.4+. No Firebird, no extensions, no Composer, no build step.
     Copy the folder onto any host and open it.</p>
  <pre><code>git clone {$repo}.git super-power-2
cd super-power-2
php -S 127.0.0.1:8000</code></pre>
  <p class="intro" style="margin-top:18px">Only <code>work/</code> needs to be writable; the editor creates
     everything else itself. Details, nginx notes and upload limits are in the
     <a href="{$repo}#installation">README</a>.</p>
</div></div>

<div class="section"><div class="wrap">
  <h2>Guide</h2>
  <p class="intro">Twenty-three illustrated sections covering every screen, in nine languages.</p>
  <div class="btns" style="justify-content:flex-start">
    <a class="btn" href="guide/index.html"><i class="bi bi-book" aria-hidden="true"></i>Read the guide</a>
  </div>
</div></div>

HTML
. footerHtml();

put("$docs/index.html", page(
    TITLE . ' — browser editor for the SuperPower 2 database',
    'Edit DATABASE.GDB and StringTable.*.gst from the browser: 28 tables, a world map with thirteen '
    . 'colourings, country merging, checks, search and undo. Pure PHP, no Firebird engine required.',
    $landing,
    ''
));

/* ------------------------------------------------------------------ done */

$n = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docs, FilesystemIterator::SKIP_DOTS));
$bytes = 0;
foreach ($it as $f) { $n++; $bytes += $f->getSize(); }
printf("docs/ built: %d files, %.1f MB\n", $n, $bytes / 1048576);
