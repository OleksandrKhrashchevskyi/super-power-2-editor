<?php

$CRUMBS = [
  'replace' => t('Find and replace'), 'check' => t('Database check'),
  'find'    => t('Global search'),    'log'   => t('Change log'),
  'map'     => t('World map'),        'merge' => t('Merge countries'),
  'guide'   => t('Guide'),
];
if ($tab === 'lang') {
    $crumb = $gstOpen ? basename((string)gst_path()) : t('Languages');
} elseif (isset($CRUMBS[$action])) {
    $crumb = $CRUMBS[$action];
} elseif ($curTable !== '') {
    $crumb = $curTable . ($action === 'struct' ? ' · ' . t('Structure') : '');
} else {
    $crumb = t('Database');
}
?>
<nav class="navbar app-navbar sticky-top px-2 px-lg-3 py-0">
  <div class="d-flex align-items-center gap-2 w-100">

    <button class="btn btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebar" aria-controls="sidebar" aria-label="<?= h(t('Menu')) ?>">
      <i class="bi bi-list"></i>
    </button>

    <a class="navbar-brand d-none d-lg-flex align-items-center gap-2 m-0 py-2" href="index.php">
      <span class="brand-logo"><i class="bi bi-database-fill-gear"></i></span>
      <span class="brand-text d-none d-xl-inline"><?= APP_NAME ?></span>
    </a>

    <nav aria-label="breadcrumb" class="min-w-0 flex-grow-1">
      <ol class="breadcrumb m-0 flex-nowrap">
        <li class="breadcrumb-item d-none d-sm-block">
          <a href="<?= $tab === 'lang' ? '?a=lang' : 'index.php' ?>">
            <i class="bi bi-<?= $tab === 'lang' ? 'translate' : 'hdd-stack' ?>"></i>
            <span class="d-none d-md-inline ms-1"><?= h($tab === 'lang' ? t('Languages') : t('Database')) ?></span>
          </a>
        </li>
        <li class="breadcrumb-item active text-truncate" aria-current="page"><?= h($crumb) ?></li>
      </ol>
    </nav>

    <?php if ($dbOpen || $gstOpen): ?>
      <a class="btn btn-sm btn-dl d-none d-md-inline-flex align-items-center gap-1" href="?a=download_all"
         data-bs-toggle="tooltip" title="<?= h(t('Download all (.zip)')) ?>">
        <i class="bi bi-file-earmark-zip"></i><span class="d-none d-xl-inline"><?= h(t('Download all')) ?></span>
      </a>
    <?php endif; ?>

    <span class="nav-sep d-none d-md-block"></span>

    <div class="dropdown">
      <button class="btn btn-icon" type="button" data-bs-toggle="dropdown" data-bs-display="static"
              aria-expanded="false" aria-label="<?= h(t('Language')) ?>">
        <i class="bi bi-globe2"></i><span class="lang-abbr"><?= strtoupper(lang()) ?></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><h6 class="dropdown-header"><?= h(t('Interface language')) ?></h6></li>
        <?php foreach (LANGS as $code => $name): ?>
          <li><a class="dropdown-item d-flex align-items-center gap-2 <?= lang() === $code ? 'active' : '' ?>"
                 href="<?= h('?' . http_build_query(array_merge($_GET, ['lang' => $code]))) ?>">
            <span class="badge text-bg-secondary lang-badge"><?= strtoupper($code) ?></span><?= h($name) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <a class="btn btn-icon<?= $action === 'guide' ? ' active' : '' ?>" href="?a=guide"
       data-bs-tooltip="1" title="<?= h(t('Guide')) ?>" aria-label="<?= h(t('Guide')) ?>">
      <i class="bi bi-book"></i>
    </a>

    <button class="btn btn-icon" type="button" data-bs-toggle="modal" data-bs-target="#uiModal"
            data-bs-tooltip="1" title="<?= h(t('Appearance')) ?>" aria-label="<?= h(t('Appearance')) ?>">
      <i class="bi bi-palette"></i>
    </button>
  </div>
</nav>

<div class="app-shell flex-grow-1">
