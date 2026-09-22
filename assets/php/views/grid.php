<?php
$table = $curTable;
$cols  = Db::columns($table);
$links = guessed_links($table);
$perWant = max(10, min(500, (int)get('per', PAGE_SIZE_DEFAULT)));
$colCount = count($cols);
$perMax  = max(10, (int)floor(MAX_CELLS_PER_PAGE / max(1, $colCount)));   
$per     = min($perWant, $perMax);
$capped  = $per < $perWant;
$page  = max(1, (int)get('p', 1));
$order = get_s('o');
$dir   = get('d') === 'desc' ? 'desc' : 'asc';
$filters = array_filter((array)get('f', []), fn($v) => $v !== '');
[$rows, $total] = Db::fetch($table, $filters, $order, $dir, $page, $per);
$pages = max(1, (int)ceil($total / $per));
$qs = http_build_query(array_filter(['p' => $page, 'per' => $per, 'o' => $order, 'd' => $dir, 'f' => $filters],
                                    fn($v) => $v !== '' && $v !== null && $v !== []));
$mk = fn(int $p) => '?' . http_build_query(array_filter(
        ['a' => 'table', 't' => $table, 'p' => $p, 'per' => $per, 'o' => $order, 'd' => $dir, 'f' => $filters],
        fn($v) => $v !== '' && $v !== []));
$from = $total ? ($page - 1) * $per + 1 : 0;
$to   = min($total, $page * $per);
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title mono"><i class="bi bi-table"></i> <?= h($table) ?></h1>
    <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis"
          data-bs-tooltip="1" title="<?= h(t('rows')) ?>">
      <i class="bi bi-list-ol"></i> <?= number_format($total, 0, '.', ' ') ?></span>
    <span class="badge rounded-pill text-bg-secondary" data-bs-tooltip="1" title="<?= h(t('Columns')) ?>">
      <i class="bi bi-layout-three-columns"></i> <?= $colCount ?></span>
    <?php if ($filters): ?>
      <span class="badge rounded-pill bg-warning-subtle border border-warning-subtle text-warning-emphasis">
        <i class="bi bi-funnel-fill"></i> <?= count($filters) ?></span>
    <?php endif; ?>
    <div class="ms-auto d-flex gap-2">
      <?php if (!$gstOpen): ?>
        <a class="btn btn-sm btn-outline-info" href="?a=lang"><i class="bi bi-translate"></i>
          <span class="d-none d-md-inline"><?= h(t('attach dictionary')) ?></span></a>
      <?php endif; ?>
      <button type="button" class="btn btn-sm <?= $filters ? 'btn-warning' : 'btn-outline-secondary' ?>" id="filterToggle"
              data-bs-tooltip="1" title="<?= h(t('Filter')) ?>">
        <i class="bi bi-funnel<?= $filters ? '-fill' : '' ?>"></i>
        <span class="d-none d-md-inline"><?= h(t('Filter')) ?></span>
        <?php if ($filters): ?><span class="badge rounded-pill text-bg-secondary"><?= count($filters) ?></span><?php endif; ?>
      </button>
      <?php

        $mapFor = ['COUNTRY' => 'country', 'REGION' => 'country', 'CITIES' => 'country',
                   'LANGUAGE' => 'lang', 'LANGUAGES' => 'lang', 'LANGUAGES_STATUS' => 'lang',
                   'RELIGION' => 'rel', 'RELIGIONS' => 'rel', 'RELIGIONS_STATUS' => 'rel',
                   'GVT_TYPE' => 'gvt', 'PARTIES' => 'gvt'];
        if (isset($mapFor[$table])):
      ?>
        <a class="btn btn-sm btn-outline-primary" href="?a=map&amp;c=<?= h($mapFor[$table]) ?>">
          <i class="bi bi-globe-americas"></i>
          <span class="d-none d-md-inline"><?= h(t('World map')) ?></span></a>
      <?php endif; ?>
      <a class="btn btn-sm btn-outline-secondary" href="?a=replace&amp;t=<?= urlencode($table) ?>"
         data-bs-tooltip="1" title="<?= h(t('Find and replace')) ?>"><i class="bi bi-search"></i></a>
      <a class="btn btn-sm btn-outline-secondary" href="?a=struct&t=<?= urlencode($table) ?>">
        <i class="bi bi-diagram-3"></i> <span class="d-none d-md-inline"><?= h(t('Structure')) ?></span></a>
    </div>
  </div>
</div>

<form method="get" id="gridForm">
  <input type="hidden" name="a" value="table"><input type="hidden" name="t" value="<?= h($table) ?>">
  <input type="hidden" name="o" value="<?= h($order) ?>"><input type="hidden" name="d" value="<?= h($dir) ?>">

  <div class="card shadow-sm overflow-hidden">
    <div class="grid-wrap">
      <table class="table table-sm table-hover grid align-middle mb-0">
        <thead>
        <tr class="th-row">
          <th class="col-actions"></th>
          <?php foreach ($cols as $c): $n = $c['name']; $nd = ($order === $n && $dir === 'asc') ? 'desc' : 'asc'; ?>
            <th<?= is_numeric_col($c) ? ' class="num"' : '' ?>>
              <a class="th-name<?= $order === $n ? ' sorted' : '' ?>"
                 href="?a=table&t=<?= urlencode($table) ?>&o=<?= urlencode($n) ?>&d=<?= $nd ?>&per=<?= $per ?><?= $filters ? '&' . http_build_query(['f' => $filters]) : '' ?>"
                 data-bs-tooltip="1" title="<?= h($n) ?> · <?= h(type_name($c)) ?>">
                <span class="th-label"><?= h($n) ?></span>
                <i class="bi bi-<?= $order === $n ? ($dir === 'asc' ? 'sort-down-alt' : 'sort-up-alt') : 'arrow-down-up' ?> th-sort"></i>
              </a>
              <span class="th-tags">
                <?php if ($c['indexed']): ?><span class="badge tag-key" data-bs-tooltip="1"
                  title="<?= h(t('field belongs to a database index — editing disabled')) ?>">IDX</span><?php endif; ?>
                <?php if (isset($links[$n])): ?><a class="badge tag-fk" href="?a=table&t=<?= urlencode($links[$n]) ?>"
                  data-bs-tooltip="1" title="<?= h(t('looks like a link to %s', $links[$n])) ?>"><i class="bi bi-arrow-right-short"></i></a><?php endif; ?>
                <?php if (is_stid_col($c)): ?><span class="badge tag-txt" data-bs-tooltip="1"
                  title="<?= h(t('text in .gst')) ?>">TXT</span><?php endif; ?>
              </span>
            </th>
          <?php endforeach; ?>
        </tr>
        <tr class="filter-row<?= $filters ? '' : ' d-none' ?>" id="filterRow">
          <th class="col-actions"><i class="bi bi-funnel"></i></th>
          <?php foreach ($cols as $c): $n = $c['name']; ?>
            <th<?= is_numeric_col($c) ? ' class="num"' : '' ?>>
              <input class="form-control form-control-sm th-filter" name="f[<?= h($n) ?>]" size="1"
                     value="<?= h($filters[$n] ?? '') ?>" placeholder="<?= h(t('filter')) ?>"
                     aria-label="<?= h($n) ?>" <?= is_blob_col($c) ? 'disabled' : '' ?>></th>
          <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
          $labels = [];
          foreach ($cols as $c) if ($gstOpen && is_stid_col($c)) $labels[$c['name']] = gst_lookup($r[$c['name']] ?? null);
        ?>
          <tr>
            <td class="col-actions">
              <button type="button" class="btn btn-sm btn-icon-xs" data-edit-row data-rowid="<?= h($r['__rowid']) ?>"
                      data-bs-tooltip="1" title="<?= h(t('Edit row')) ?>"><i class="bi bi-pencil-square"></i></button>
            </td>
<?php foreach ($cols as $c): $v = $r[$c['name']] ?? null; $lab = $labels[$c['name']] ?? null;
              echo is_numeric_col($c) ? '<td class=num>' : '<td>';
              if (is_blob_col($c))      echo '<span class=nullv>BLOB</span>';
              elseif ($v === null)      echo '<span class=nullv>null</span>';
              else {
                  echo h(is_float($v) ? fmt_num($v, $c['type']) : (string)$v);
                  if ($lab) echo '<div class=stid>', h(mb_strimwidth($lab, 0, 70, '…', 'UTF-8')), '</div>';
              }
              echo '</td>';
            endforeach; ?>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="<?= $colCount + 1 ?>" class="empty-row">
            <i class="bi bi-inbox"></i> <?= h(t('No rows')) ?></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card-footer gridbar">
      <div class="btn-group btn-group-sm">
        <button class="btn btn-primary"><i class="bi bi-funnel"></i> <?= h(t('Filter')) ?></button>
        <a class="btn btn-outline-secondary" href="?a=table&t=<?= urlencode($table) ?>"
           data-bs-tooltip="1" title="<?= h(t('Reset')) ?>"><i class="bi bi-x-circle"></i></a>
      </div>
      <select name="per" class="form-select form-select-sm w-auto" onchange="this.form.submit()"
              aria-label="<?= h(t('per page')) ?>">
        <?php foreach ([25, 50, 100, 200, 500] as $n): ?>
          <option value="<?= $n ?>" <?= $n === $perWant ? 'selected' : '' ?>><?= $n ?> / <?= h(t('per page')) ?></option>
        <?php endforeach; ?>
      </select>
      <span class="text-body-secondary small d-none d-md-inline">
        <?= number_format($from, 0, '.', ' ') ?>–<?= number_format($to, 0, '.', ' ') ?>
        <?= h(t('of')) ?> <?= number_format($total, 0, '.', ' ') ?></span>
      <ul class="pagination pagination-sm mb-0 ms-auto">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk(1)) ?>"><i class="bi bi-chevron-double-left"></i></a></li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk(max(1, $page - 1))) ?>"><i class="bi bi-chevron-left"></i></a></li>
        <li class="page-item disabled"><span class="page-link mono"><?= $page ?> / <?= $pages ?></span></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk(min($pages, $page + 1))) ?>"><i class="bi bi-chevron-right"></i></a></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= h($mk($pages)) ?>"><i class="bi bi-chevron-double-right"></i></a></li>
      </ul>
    </div>
  </div>

  <?php if ($capped): ?>
    <div class="alert alert-info d-flex gap-2 align-items-center py-2 mt-3 mb-0 small">
      <i class="bi bi-info-circle-fill"></i>
      <?= h(t('This table has %d columns, so the page is limited to %d rows — otherwise the page grows to megabytes.', $colCount, $per)) ?></div>
  <?php endif; ?>
</form>
