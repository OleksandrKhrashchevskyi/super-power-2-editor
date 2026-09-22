<?php

$t0 = microtime(true);
$found = db_checks();
$secs  = microtime(true) - $t0;
$tone  = ['danger' => 'danger', 'warning' => 'warning', 'info' => 'secondary'];
$ico   = ['danger' => 'x-octagon-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
$total = array_sum(array_map(fn($f) => count($f['rows']), $found));
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-clipboard-check"></i> <?= h(t('Database check')) ?></h1>
    <?php if ($found): ?>
      <span class="badge rounded-pill bg-warning-subtle border border-warning-subtle text-warning-emphasis">
        <?= h(t('findings: %d', count($found))) ?></span>
      <span class="badge rounded-pill text-bg-secondary"><?= h(t('rows: %d', $total)) ?></span>
    <?php else: ?>
      <span class="badge rounded-pill bg-success-subtle border border-success-subtle text-success-emphasis">
        <i class="bi bi-check2"></i> <?= h(t('nothing found')) ?></span>
    <?php endif; ?>
    <span class="badge rounded-pill text-bg-secondary ms-auto"><i class="bi bi-stopwatch"></i> <?= number_format($secs, 2) ?> s</span>
  </div>
  <p class="page-sub mb-0"><?= h(t('Only real breakage: links to rows that do not exist, texts missing from the dictionary, duplicate keys. Nothing is guessed.')) ?>
    <?php if (!$gstOpen): ?><br><i class="bi bi-info-circle"></i> <?= h(t('Attach a dictionary to also check the texts.')) ?><?php endif; ?></p>
</div>

<?php if (!$found): ?>
  <div class="card shadow-sm"><div class="card-body empty-row">
    <i class="bi bi-check2-circle"></i> <?= h(t('No problems found.')) ?>
  </div></div>
<?php else: ?>
  <div class="accordion" id="chkAcc">
    <?php foreach ($found as $i => $f): ?>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button <?= $i ? 'collapsed' : '' ?>" type="button"
                  data-bs-toggle="collapse" data-bs-target="#chk<?= $i ?>">
            <i class="bi bi-<?= h($ico[$f['level']]) ?> text-<?= h($tone[$f['level']]) ?> me-2"></i>
            <span class="flex-grow-1"><?= h($f['title']) ?></span>
            <span class="badge rounded-pill text-bg-secondary ms-2"><?= count($f['rows']) ?></span>
          </button>
        </h2>
        <div id="chk<?= $i ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>" data-bs-parent="#chkAcc">
          <div class="accordion-body p-0">
            <div class="grid-wrap" style="max-height:min(42vh,22rem)">
              <table class="table table-sm table-hover grid align-middle mb-0">
                <thead><tr><th>ID</th><th><?= h($f['col']) ?></th><th class="col-actions"></th></tr></thead>
                <tbody>
                  <?php foreach ($f['rows'] as [$rid, $v, $id]): ?>
                    <tr>
                      <td class="mono"><?= $id === null ? '<span class="nullv">—</span>' : (int)$id ?></td>
                      <td class="mono"><?= h((string)$v) ?></td>
                      <td class="col-actions">
                        <a class="btn btn-sm btn-icon-xs" href="?a=table&t=<?= urlencode($f['table']) ?>&f%5B<?= urlencode($f['col']) ?>%5D=<?= urlencode((string)$v) ?>"
                           data-bs-tooltip="1" title="<?= h(t('Open in the table')) ?>"><i class="bi bi-box-arrow-up-right"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="card-footer small text-body-secondary d-flex gap-2 flex-wrap">
              <span class="mono"><?= h($f['table']) ?>.<?= h($f['col']) ?></span>
              <a class="ms-auto" href="?a=replace&t=<?= urlencode($f['table']) ?>&c=<?= urlencode($f['col']) ?>">
                <i class="bi bi-search"></i> <?= h(t('Fix with find and replace')) ?></a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
