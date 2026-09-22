<!doctype html>
<html lang="<?= h(lang()) ?>" data-theme="midnight" data-bs-theme="dark" data-font="inter">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="dark light">
<title><?= APP_NAME ?><?= $curTable ? ' — ' . h($curTable) : '' ?></title>
<script>

(function () {
  try {
    var s = JSON.parse(localStorage.getItem('sp2ui') || '{}'), d = document.documentElement;
    if (s.theme) d.setAttribute('data-theme', s.theme);
    if (s.kind)  d.setAttribute('data-bs-theme', s.kind);
    if (s.font)  d.setAttribute('data-font', s.font);
    if (s.size)  d.style.setProperty('--ui-fs', s.size + 'px');
    if (s.dense) d.setAttribute('data-dense', '1');
    if (s.accent) {
      d.style.setProperty('--ac', s.accent);

      var m = /^#?([0-9a-f]{6})$/i.exec(s.accent);
      if (m) {
        var n = parseInt(m[1], 16), r = (n >> 16) & 255, g = (n >> 8) & 255, b2 = n & 255;
        d.style.setProperty('--ac-rgb', r + ',' + g + ',' + b2);
        var ch = function (c) { c /= 255; return c <= .03928 ? c / 12.92 : Math.pow((c + .055) / 1.055, 2.4); };
        var L = .2126 * ch(r) + .7152 * ch(g) + .0722 * ch(b2);
        d.style.setProperty('--ac-fg', (1.05 / (L + .05)) >= ((L + .05) / 0.0555) ? '#fff' : '#12100a');
      }
    }
    if (s.text)   d.style.setProperty('--txt-override', s.text);
  } catch (e) {}
})();
</script>
<link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/icons/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/vendor/tom-select/tom-select.bootstrap5.min.css" rel="stylesheet">
<link href="assets/vendor/simplebar/simplebar.min.css" rel="stylesheet">
<link href="assets/vendor/nprogress/nprogress.css" rel="stylesheet">
<link href="assets/css/themes.css?v=8" rel="stylesheet">
<link href="assets/css/style.css?v=8" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
