<?php

$tbls = Db::tables();
$stats = [];
$totalRows = 0; $totalCols = 0;
foreach ($tbls as $name => $tt) {
    $n = Db::rowCount($name);
    $c = count(Db::columns($name));
    $totalRows += $n; $totalCols += $c;
    $stats[] = ['name' => $name, 'rows' => $n, 'cols' => $c];
}
usort($stats, fn($a, $b) => $b['rows'] <=> $a['rows']);
$maxRows = max(1, $stats ? $stats[0]['rows'] : 1);
$cards = [
  ['table',            t('Tables'),  number_format(count($tbls), 0, '.', ' '),    'primary'],
  ['list-ol',          t('Rows'),    number_format($totalRows, 0, '.', ' '),      'success'],
  ['layout-three-columns', t('Columns'), number_format($totalCols, 0, '.', ' '),  'info'],
  ['hdd',              t('File'),    bytes_h((int)filesize((string)Db::path())),  'warning'],
];
?>
<div class="page-head">
  <h1 class="page-title"><i class="bi bi-speedometer2"></i> <?= h(t('Database is open')) ?></h1>
  <p class="page-sub mb-0"><?= h(basename((string)Db::path())) ?> · Firebird ODS 10.1</p>
</div>

<div class="row g-3 mb-3">
  <?php foreach ($cards as [$ico, $label, $val, $tone]): ?>
    <div class="col-6 col-xl-3">
      <div class="card stat-card h-100 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="stat-ico bg-<?= h($tone) ?>-subtle text-<?= h($tone) ?>-emphasis"><i class="bi bi-<?= h($ico) ?>"></i></span>
          <div class="min-w-0">
            <div class="stat-val"><?= h($val) ?></div>
            <div class="stat-label"><?= h($label) ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <i class="bi bi-bar-chart-line"></i>
        <span class="me-auto"><?= h(t('Tables by size')) ?></span>
        <input id="wlSearch" class="form-control form-control-sm wl-search"
               placeholder="<?= h(t('Table search…')) ?>" autocomplete="off">
      </div>
      <div class="list-group list-group-flush wl-list" id="wlList">
        <?php foreach ($stats as $s): ?>
          <a class="list-group-item list-group-item-action d-flex align-items-center gap-3"
             href="?a=table&t=<?= urlencode($s['name']) ?>">
            <span class="mono flex-grow-1 text-truncate"><?= h($s['name']) ?></span>
            <span class="wl-bar"><span style="width:<?= max(2, (int)round($s['rows'] / $maxRows * 100)) ?>%"></span></span>
            <span class="badge rounded-pill text-bg-secondary wl-num"><?= number_format($s['rows'], 0, '.', ' ') ?></span>
            <span class="text-body-secondary small d-none d-md-inline wl-cols"><?= $s['cols'] ?> <?= h(t('col.')) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <?php  ?>
    <div class="card shadow-sm">
      <div class="card-header"><i class="bi bi-lightning-charge"></i> <?= h(t('Quick actions')) ?></div>
      <div class="list-group list-group-flush qa-list">
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=map">
          <i class="bi bi-globe-americas qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('World map')) ?></span>
            <span class="small text-body-secondary"><?= h(t('See which regions belong to whom')) ?></span></span></a>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=map&amp;c=lang">
          <i class="bi bi-translate qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Map by language')) ?></span>
            <span class="small text-body-secondary"><?= h(t('Dominant language')) ?> · <?= h(t('Share of one language')) ?></span></span></a>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=map&amp;c=rel">
          <i class="bi bi-bank qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Map by religion')) ?></span>
            <span class="small text-body-secondary"><?= h(t('Dominant religion')) ?> · <?= h(t('Share of one religion')) ?></span></span></a>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=merge">
          <i class="bi bi-diagram-2 qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Merge countries')) ?></span>
            <span class="small text-body-secondary"><?= h(t('Transfer everything to one country')) ?></span></span></a>
        <?php if (!$gstOpen): ?>
          <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=lang">
            <i class="bi bi-translate qa-ico"></i>
            <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('attach dictionary')) ?></span>
              <span class="small text-body-secondary"><?= h(t('Names instead of numeric IDs')) ?></span></span></a>
        <?php else: ?>
          <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=lang">
            <i class="bi bi-translate qa-ico"></i>
            <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Languages')) ?></span>
              <span class="small text-body-secondary"><?= h(basename((string)gst_path())) ?></span></span></a>
        <?php endif; ?>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=check">
          <i class="bi bi-clipboard-check qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Database check')) ?></span>
            <span class="small text-body-secondary"><?= h(t('Broken links, missing texts, duplicate keys')) ?></span></span></a>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=replace">
          <i class="bi bi-search qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Find and replace')) ?></span>
            <span class="small text-body-secondary"><?= h(t('One column at a time, with preview')) ?></span></span></a>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=schemajson">
          <i class="bi bi-filetype-json qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Export schema as JSON')) ?></span>
            <span class="small text-body-secondary"><?= h(t('Tables, fields, types')) ?></span></span></a>
        <a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="?a=download_all">
          <i class="bi bi-file-earmark-zip qa-ico"></i>
          <span class="min-w-0"><span class="d-block fw-semibold"><?= h(t('Download all (.zip)')) ?></span>
            <span class="small text-body-secondary"><?= h(t('Database and language files')) ?></span></span></a>
      </div>
    </div>
  </div>
</div>
