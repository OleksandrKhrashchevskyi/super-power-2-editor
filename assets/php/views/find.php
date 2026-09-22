<?php

$q = trim(get_s('q'));
$res = null; $secs = 0;
if ($q !== '') { $t0 = microtime(true); $res = db_search($q); $secs = microtime(true) - $t0; }
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-binoculars"></i> <?= h(t('Global search')) ?></h1>
    <?php if ($res): ?>
      <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis">
        <?= h(t('found: %d', (int)$res['total'])) ?></span>
      <span class="badge rounded-pill text-bg-secondary"><i class="bi bi-stopwatch"></i> <?= number_format($secs, 2) ?> s</span>
    <?php endif; ?>
    <form class="ms-auto d-flex gap-2 flex-wrap align-items-center lang-search" method="get">
      <input type="hidden" name="a" value="find">
      <div class="input-group input-group-sm search-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input class="form-control" name="q" value="<?= h($q) ?>" autofocus
               placeholder="<?= h(t('text or number')) ?>">
        <?php if ($q !== ''): ?><a class="btn btn-outline-secondary" href="?a=find"><i class="bi bi-x-lg"></i></a><?php endif; ?>
        <button class="btn btn-primary"><?= h(t('Find')) ?></button>
      </div>
    </form>
  </div>
  <p class="page-sub mb-0"><?= h(t('Searches every table and, if a dictionary is open, the game texts. Text is matched case-insensitively, numbers by exact value.')) ?></p>
</div>

<?php if ($q === ''): ?>
  <div class="card shadow-sm"><div class="card-body empty-row">
    <i class="bi bi-binoculars"></i> <?= h(t('Type something to search for.')) ?>
  </div></div>
<?php elseif (!$res['total']): ?>
  <div class="card shadow-sm"><div class="card-body empty-row">
    <i class="bi bi-inbox"></i> <?= h(t('Nothing found')) ?>
  </div></div>
<?php else: ?>
  <div class="row g-3">
    <?php if ($res['gst']): ?>
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-translate"></i> <?= h(t('Game texts')) ?>
            <span class="badge rounded-pill text-bg-secondary ms-auto"><?= count($res['gst']) ?></span>
          </div>
          <div class="grid-wrap" style="max-height:min(34vh,18rem)">
            <table class="table table-sm table-hover grid align-middle mb-0">
              <tbody>
                <?php foreach ($res['gst'] as [$id, $txt]): ?>
                  <tr><td class="col-id num mono text-body-secondary"><?= (int)$id ?></td>
                    <td><?= h($txt) ?></td>
                    <td class="col-actions"><a class="btn btn-sm btn-icon-xs" href="?a=lang&q=<?= urlencode((string)$id) ?>"
                      data-bs-tooltip="1" title="<?= h(t('Open in the dictionary')) ?>"><i class="bi bi-box-arrow-up-right"></i></a></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php foreach ($res['tables'] as $name => $hits): ?>
      <div class="col-xl-6">
        <div class="card shadow-sm h-100">
          <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-table"></i> <span class="mono"><?= h($name) ?></span>
            <span class="badge rounded-pill text-bg-secondary ms-auto"><?= count($hits) ?></span>
            <a class="btn btn-sm btn-outline-secondary" href="?a=table&t=<?= urlencode($name) ?>"
               data-bs-tooltip="1" title="<?= h(t('Open in the table')) ?>"><i class="bi bi-box-arrow-up-right"></i></a>
          </div>
          <div class="grid-wrap" style="max-height:min(30vh,16rem)">
            <table class="table table-sm table-hover grid align-middle mb-0">
              <thead><tr><th>ID</th><th><?= h(t('Found in')) ?></th></tr></thead>
              <tbody>
                <?php foreach ($hits as $hit): ?>
                  <tr>
                    <td class="mono"><?= $hit['id'] === null ? '<span class="nullv">—</span>' : (int)$hit['id'] ?></td>
                    <td class="tags-cell">
                      <?php foreach (array_slice($hit['cols'], 0, 6) as $cn): ?>
                        <a class="badge tag-fk" href="?a=table&t=<?= urlencode($name) ?>&f%5B<?= urlencode($cn) ?>%5D=<?= urlencode($q) ?>"><?= h($cn) ?></a>
                      <?php endforeach; ?>
                      <?php if (count($hit['cols']) > 6): ?><span class="text-body-secondary small">+<?= count($hit['cols']) - 6 ?></span><?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
