<?php

$tbls = ($tab === 'db' && $dbOpen) ? Db::tables() : [];
?>
<aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="sidebar" aria-label="<?= h(t('Menu')) ?>">

  <div class="offcanvas-header border-bottom d-lg-none">
    <h5 class="offcanvas-title d-flex align-items-center gap-2">
      <span class="brand-logo"><i class="bi bi-database-fill-gear"></i></span> <?= APP_NAME ?>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
  </div>

  <div class="offcanvas-body flex-column p-0">

    <div class="sb-top p-3 pb-2">
      <ul class="nav nav-pills nav-fill app-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link <?= $tab === 'db' ? 'active' : '' ?>" href="index.php">
          <i class="bi bi-hdd-stack"></i> <span><?= h(t('Database')) ?></span></a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'lang' ? 'active' : '' ?>" href="?a=lang">
          <i class="bi bi-translate"></i> <span><?= h(t('Languages')) ?></span>
          <?php if ($gstOpen): ?><span class="badge rounded-pill text-bg-secondary ms-1"><?= count(gst_files()) ?></span><?php endif; ?></a></li>
      </ul>
    </div>

    <div class="sb-scroll flex-grow-1" data-scroll>
      <div class="px-3 pb-3">

      <?php if ($tab === 'db' && $dbOpen): ?>
        <div class="card file-card mb-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-start gap-2 mb-2">
              <i class="bi bi-hdd-fill file-ico"></i>
              <div class="min-w-0 flex-grow-1">
                <div class="fw-semibold text-truncate" title="<?= h(basename((string)Db::path())) ?>"><?= h(basename((string)Db::path())) ?></div>
                <div class="small text-body-secondary"><?= bytes_h((int)filesize((string)Db::path())) ?> · <?= count($tbls) ?> <?= h(t('tables')) ?></div>
              </div>
            </div>
            <?php if ($gstOpen): ?>
              <span class="badge rounded-pill bg-success-subtle border border-success-subtle text-success-emphasis mb-2">
                <i class="bi bi-translate"></i> <?= h(t('dictionary attached')) ?></span>
            <?php endif; ?>
            <div class="d-grid gap-2">
              <a class="btn btn-dl btn-sm" href="?a=download_all"><i class="bi bi-file-earmark-zip"></i> <?= h(t('Download all (.zip)')) ?></a>
              <div class="sb-tools">
                <a class="btn btn-sm btn-outline-secondary" href="?a=download" data-bs-tooltip="1" title="<?= h(t('Download')) ?> .gdb"><i class="bi bi-download"></i></a>
                <a class="btn btn-sm btn-outline-secondary<?= $action === 'map' ? ' active' : '' ?>" href="?a=map"
                   data-bs-tooltip="1" title="<?= h(t('World map')) ?>"><i class="bi bi-globe-americas"></i></a>
                <a class="btn btn-sm btn-outline-secondary<?= $action === 'merge' ? ' active' : '' ?>" href="?a=merge" data-bs-tooltip="1" title="<?= h(t('Merge countries')) ?>"><i class="bi bi-diagram-2"></i></a>
                <a class="btn btn-sm btn-outline-secondary<?= $action === 'replace' ? ' active' : '' ?>" href="?a=replace"
                   data-bs-tooltip="1" title="<?= h(t('Find and replace')) ?>"><i class="bi bi-arrow-left-right"></i></a>
                <a class="btn btn-sm btn-outline-secondary<?= $action === 'find' ? ' active' : '' ?>" href="?a=find"
                   data-bs-tooltip="1" title="<?= h(t('Global search')) ?>"><i class="bi bi-binoculars"></i></a>
                <a class="btn btn-sm btn-outline-secondary<?= $action === 'check' ? ' active' : '' ?>" href="?a=check"
                   data-bs-tooltip="1" title="<?= h(t('Database check')) ?>"><i class="bi bi-clipboard-check"></i></a>
                <a class="btn btn-sm btn-outline-secondary<?= $action === 'log' ? ' active' : '' ?>" href="?a=log"
                   data-bs-tooltip="1" title="<?= h(t('Change log')) ?>"><i class="bi bi-clock-history"></i><?php
                   $nlog = count(log_ops()); if ($nlog): ?><span class="log-dot"></span><?php endif; ?></a>
                <a class="btn btn-sm btn-outline-secondary" href="?a=schemajson" data-bs-tooltip="1" title="<?= h(t('Schema JSON')) ?>"><i class="bi bi-filetype-json"></i></a>
                <?php if (!empty($_SESSION['db_origin'])): ?>
                  <a class="btn btn-sm btn-outline-warning" href="?a=writeback&t=<?= urlencode($curTable) ?>"
                     data-confirm="<?= h(t('Overwrite the game file?')) ?>" data-bs-tooltip="1"
                     title="<?= h(t('Write to game')) ?>"><i class="bi bi-box-arrow-in-down"></i></a>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-danger" href="?a=close" data-confirm="<?= h(t('Close the file and remove it from the project? A copy stays in the backups folder.')) ?>" data-bs-tooltip="1" title="<?= h(t('Close database')) ?>"><i class="bi bi-x-lg"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="sb-label"><i class="bi bi-table"></i> <?= h(t('Tables')) ?>
          <span class="badge rounded-pill text-bg-secondary ms-auto" id="tblCount"><?= count($tbls) ?></span></div>
        <div class="input-group input-group-sm mb-2">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="tblSearch" class="form-control" placeholder="<?= h(t('Table search…')) ?>" autocomplete="off">
        </div>
        <div id="tblList" class="list-group list-group-flush tbl-list">
          <?php foreach ($tbls as $name => $tt): ?>
            <a class="list-group-item list-group-item-action <?= $name === $curTable ? 'active' : '' ?>"
               href="?a=table&t=<?= urlencode($name) ?>">
              <i class="bi bi-table"></i><span class="text-truncate"><?= h($name) ?></span></a>
          <?php endforeach; ?>
        </div>
        <div class="text-body-secondary small mt-2 d-none" id="tblEmpty"><?= h(t('Nothing found')) ?></div>

      <?php elseif ($tab === 'lang' && $gstOpen): ?>
        <?php $gf = gst_files(); if (count($gf) > 1): ?>
          <div class="sb-label"><i class="bi bi-files"></i> <?= h(t('Files')) ?></div>
          <div class="list-group list-group-flush mb-3 gst-list">
            <?php foreach ($gf as $i => $p): ?>
              <a class="list-group-item list-group-item-action d-flex align-items-center gap-2 <?= $i === gst_active() ? 'active' : '' ?>"
                 href="?a=gst_use&i=<?= $i ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span class="text-truncate flex-grow-1"><?= h(gst_label($p)) ?></span>
                <span class="badge rounded-pill text-bg-secondary"><?= bytes_h((int)@filesize($p)) ?></span></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="card file-card mb-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-start gap-2 mb-2">
              <i class="bi bi-translate file-ico"></i>
              <div class="min-w-0 flex-grow-1">
                <div class="fw-semibold text-truncate" title="<?= h(basename((string)gst_path())) ?>"><?= h(basename((string)gst_path())) ?></div>
                <div class="small text-body-secondary"><?= number_format($gstCount, 0, '.', ' ') ?> <?= h(t('rows')) ?> · <?= bytes_h((int)filesize((string)gst_path())) ?></div>
              </div>
            </div>
            <div class="d-grid gap-2">
              <a class="btn btn-dl btn-sm" href="?a=download_all"><i class="bi bi-file-earmark-zip"></i> <?= h(t('Download all (.zip)')) ?></a>
              <div class="sb-tools">
                <a class="btn btn-sm btn-outline-secondary" href="?a=download_gst" data-bs-tooltip="1" title="<?= h(t('Download')) ?> .gst"><i class="bi bi-download"></i></a>
                <?php if (!empty($_SESSION['gst_origin'])): ?>
                  <a class="btn btn-sm btn-outline-warning" href="?a=writeback_gst" data-confirm="<?= h(t('Overwrite the game file?')) ?>"
                     data-bs-tooltip="1" title="<?= h(t('Write to game')) ?>"><i class="bi bi-box-arrow-in-down"></i></a>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-danger" href="?a=close_gst" data-confirm="<?= h(t('Close the file and remove it from the project? A copy stays in the backups folder.')) ?>" data-bs-tooltip="1" title="<?= h(t('Close file')) ?>"><i class="bi bi-x-lg"></i></a>
              </div>
            </div>
          </div>
        </div>

        <?php if ($dbOpen): ?>
          <div class="alert alert-secondary py-2 px-3 small mb-0">
            <i class="bi bi-hdd"></i> <?= h(basename((string)Db::path())) ?> — <a href="index.php"><?= h(t('open the database tab')) ?></a>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="sb-placeholder text-center text-body-secondary py-4">
          <i class="bi bi-inbox"></i>
          <p class="small mb-0"><?= h(t('Upload a file — it will appear here.')) ?></p>
        </div>
      <?php endif; ?>

      </div>
    </div>

    <div class="sb-foot border-top px-3 py-2 small text-body-secondary">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-filetype-php"></i> PHP <?= h(PHP_VERSION) ?>
        <span class="badge rounded-pill text-bg-secondary"><?= PHP_INT_SIZE * 8 ?>-bit</span>
        <?php if (Db::$time): ?>
          <span class="ms-auto" data-bs-tooltip="1" title="<?= h(t('parsing: %d ms', (int)round(Db::$time * 1000))) ?>">
            <i class="bi bi-stopwatch"></i> <?= (int)round(Db::$time * 1000) ?> ms</span>
        <?php endif; ?>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-link btn-sm p-0 text-decoration-none text-body-secondary" type="button"
                data-bs-toggle="modal" data-bs-target="#projModal"
                data-bs-tooltip="1" title="<?= h(t('Your project')) ?>">
          <i class="bi bi-folder2-open"></i> <code><?= h(project_code()) ?></code></button>
        <span class="made-ua ms-auto" title="<?= h(t('Made in Ukraine')) ?>">
          <svg viewBox="0 0 24 16" width="18" height="12" aria-hidden="true">
            <rect width="24" height="8" fill="#0057B7"/><rect y="8" width="24" height="8" fill="#FFD700"/>
          </svg>
          <span class="made-ua-t"><?= h(t('Made in Ukraine')) ?></span>
        </span>
      </div>
    </div>
  </div>
</aside>
