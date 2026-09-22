<?php
$per    = max(10, min(500, (int)get('per', PAGE_SIZE_DEFAULT)));
$page   = max(1, (int)get('p', 1));
$needle = trim(get_s('q'));
$idx    = gst_index();
$hits   = [];
if ($needle === '') $hits = $idx;
elseif (ctype_digit($needle)) { foreach ($idx as $e) if ((string)$e[0] === $needle) $hits[] = $e; }
else {
    foreach ($idx as $e) {
        $t = gst_text($e[1], $e[2]);
        if ($t !== '' && mb_stripos($t, $needle, 0, 'UTF-8') !== false) $hits[] = $e;
        if (count($hits) >= 3000) break;
    }
}
$total = count($hits);
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);
$slice = array_slice($hits, ($page - 1) * $per, $per);
$qs    = http_build_query(array_filter(['p' => $page, 'per' => $per, 'q' => $needle], fn($v) => $v !== '' && $v !== null));
$origs = [];
foreach ($slice as $e) $origs[$e[0]] = gst_text($e[1], $e[2]);
$mk = fn(int $p) => '?' . http_build_query(array_filter(['a' => 'lang', 'p' => $p, 'per' => $per, 'q' => $needle], fn($v) => $v !== '' && $v !== null));
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-translate"></i> <?= h(basename((string)gst_path())) ?></h1>
    <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis">
      <i class="bi bi-list-ol"></i> <?= number_format($total, 0, '.', ' ') ?></span>
    <?php if ($needle !== ''): ?>
      <span class="badge rounded-pill bg-warning-subtle border border-warning-subtle text-warning-emphasis">
        <i class="bi bi-search"></i> <?= h(mb_strimwidth($needle, 0, 24, '…', 'UTF-8')) ?></span>
    <?php endif; ?>
    <form class="ms-auto d-flex gap-2 flex-wrap align-items-center lang-search" method="get">
      <input type="hidden" name="a" value="lang">
      <div class="input-group input-group-sm search-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input class="form-control" name="q" value="<?= h($needle) ?>" placeholder="<?= h(t('text or ID')) ?>">
        <?php if ($needle !== ''): ?>
          <a class="btn btn-outline-secondary" href="?a=lang" data-bs-tooltip="1" title="<?= h(t('Reset')) ?>"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
        <button class="btn btn-primary"><?= h(t('Find')) ?></button>
      </div>
      <select name="per" class="form-select form-select-sm w-auto" onchange="this.form.submit()"
              aria-label="<?= h(t('per page')) ?>">
        <?php foreach ([25, 50, 100, 200] as $n): ?>
          <option value="<?= $n ?>" <?= $n === $per ? 'selected' : '' ?>><?= $n ?> / <?= h(t('per page')) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
</div>

<form method="post" action="?a=save_gst">
  <input type="hidden" name="qs" value="<?= h($qs) ?>">
  <input type="hidden" name="orig" value="<?= h(json_encode($origs, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)) ?>">
  <div class="card shadow-sm overflow-hidden">
    <div class="grid-wrap">
      <table class="table table-sm grid lang-grid align-middle mb-0">
        <thead><tr><th class="col-id num">ID</th><th><?= h(t('Text')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($slice as [$id, $off, $len]): $tx = $origs[$id] ?? ''; ?>
          <tr>
            <td class="col-id num mono text-body-secondary"><?= (int)$id ?></td>
            <td><textarea class="form-control form-control-sm auto-grow" name="s[<?= (int)$id ?>]"
                          rows="<?= min(6, substr_count(rtrim($tx, "\r\n"), "\n") + 1) ?>"><?= h($tx) ?></textarea></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$slice): ?>
          <tr><td colspan="2" class="empty-row"><i class="bi bi-inbox"></i> <?= h(t('Nothing found')) ?></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card-footer gridbar">
      <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> <?= h(t('Save')) ?></button>
      <span class="text-body-secondary small d-none d-md-inline"><i class="bi bi-info-circle"></i> <?= h(t('only changed lines are saved')) ?></span>
      <ul class="pagination pagination-sm mb-0 ms-auto">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk(1)) ?>"><i class="bi bi-chevron-double-left"></i></a></li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk(max(1, $page - 1))) ?>"><i class="bi bi-chevron-left"></i></a></li>
        <li class="page-item disabled"><span class="page-link mono"><?= $page ?> / <?= $pages ?></span></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk(min($pages, $page + 1))) ?>"><i class="bi bi-chevron-right"></i></a></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk($pages)) ?>"><i class="bi bi-chevron-double-right"></i></a></li>
      </ul>
    </div>
  </div>
</form>
