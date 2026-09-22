<?php


declare(strict_types=1);
error_reporting(E_ALL);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
@set_time_limit(0);
session_start();


if (!ob_start('ob_gzhandler')) ob_start();

require __DIR__ . '/assets/php/config.php';
require __DIR__ . '/assets/php/i18n.php';
require __DIR__ . '/assets/php/jsi18n.php';
require __DIR__ . '/assets/php/helpers.php';
require __DIR__ . '/assets/php/gdb.php';
require __DIR__ . '/assets/php/db.php';
require __DIR__ . '/assets/php/schema.php';
require __DIR__ . '/assets/php/zip.php';
require __DIR__ . '/assets/php/changes.php';
require __DIR__ . '/assets/php/check.php';
require __DIR__ . '/assets/php/gst.php';

project_adopt();

require __DIR__ . '/assets/php/router.php';



$dbOpen = false; $dbErr = null;
if (Db::path()) {
    try { Db::tables(); $dbOpen = true; }
    catch (Throwable $e) { $dbErr = $e->getMessage(); }
}
$gstOpen = false; $gstErr = null; $gstCount = 0;
if (gst_path()) {
    try { $gstCount = count(gst_index()); $gstOpen = true; }
    catch (Throwable $e) { $gstErr = $e->getMessage(); }
}

$flashes  = $_SESSION['flash'] ?? []; unset($_SESSION['flash']);

$curTable = get_s('t');
if ($curTable !== '' && $dbOpen) {
    try { if (!isset(Db::tables()[$curTable])) { $curTable = ''; $flashes[] = ['warning', t('No such table.')]; } }
    catch (Throwable $e) { $curTable = ''; }
} elseif ($curTable !== '') { $curTable = ''; }
$tab      = $action === 'lang' ? 'lang' : 'db';
$curTable = $action === 'guide' ? '' : $curTable;
$curTable = $action === 'merge' ? '' : $curTable;
$qs       = '';

require __DIR__ . '/assets/php/views/head.php';
require __DIR__ . '/assets/php/views/topbar.php';
require __DIR__ . '/assets/php/views/sidebar.php';
?>
<main class="app-main flex-grow-1 min-w-0">
  <div class="app-page">
  <?php require __DIR__ . '/assets/php/views/flash.php'; ?>

  <?php

  try {
    if ($action === 'guide'):                require __DIR__ . '/assets/php/views/guide.php';
    elseif ($tab === 'lang' && !$gstOpen):   require __DIR__ . '/assets/php/views/home_lang.php';
    elseif ($tab === 'lang'):                require __DIR__ . '/assets/php/views/lang.php';
    elseif (!$dbOpen):                       require __DIR__ . '/assets/php/views/home_db.php';
    elseif ($action === 'merge'):            require __DIR__ . '/assets/php/views/merge.php';
    elseif ($action === 'map'):              require __DIR__ . '/assets/php/views/map.php';
    elseif ($action === 'replace'):          require __DIR__ . '/assets/php/views/replace.php';
    elseif ($action === 'check'):            require __DIR__ . '/assets/php/views/check.php';
    elseif ($action === 'find'):             require __DIR__ . '/assets/php/views/find.php';
    elseif ($action === 'log'):              require __DIR__ . '/assets/php/views/log.php';
    elseif ($action === 'struct' && $curTable !== ''): require __DIR__ . '/assets/php/views/struct.php';
    elseif ($curTable !== ''):               require __DIR__ . '/assets/php/views/grid.php';
    else:                                    require __DIR__ . '/assets/php/views/welcome.php';
    endif;
  } catch (Throwable $e) { ?>
    <div class="alert alert-danger d-flex gap-2 align-items-start">
      <i class="bi bi-x-octagon-fill mt-1"></i>
      <div><strong><?= h(t('Could not build this page.')) ?></strong><br>
        <span class="small"><?= h($e->getMessage()) ?></span></div>
    </div>
  <?php } ?>
  </div>
</main>
</div>
<?php
try {
    if ($tab === 'db' && $dbOpen && $action === 'map' && isset(Db::tables()['REGION'])) {
        $curTable = 'REGION';
        require __DIR__ . '/assets/php/views/modal.php';
    } elseif ($tab === 'db' && $dbOpen && $curTable !== '' && $action !== 'struct') {
        require __DIR__ . '/assets/php/views/modal.php';
    }
} catch (Throwable $e) {  }
require __DIR__ . '/assets/php/views/project_modal.php';
require __DIR__ . '/assets/php/views/settings.php';
require __DIR__ . '/assets/php/views/foot.php';
ob_end_flush();
