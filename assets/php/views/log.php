<?php

$ops = log_ops();
$icons = ['row' => 'pencil-square', 'merge' => 'diagram-2', 'map' => 'globe-americas',
          'replace' => 'search', 'gst' => 'translate'];
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-clock-history"></i> <?= h(t('Change log')) ?></h1>
    <span class="badge rounded-pill text-bg-secondary"><?= count($ops) ?></span>
    <?php if ($ops): ?>
      <a class="btn btn-sm btn-outline-danger ms-auto" href="?a=logclear"
         data-confirm="<?= h(t('Clear the log? The changes themselves stay in the file.')) ?>">
        <i class="bi bi-eraser"></i> <?= h(t('Clear log')) ?></a>
    <?php endif; ?>
  </div>
  <p class="page-sub mb-0"><?= h(t('Everything you changed in this session. Undo puts the previous values back into the file.')) ?></p>
</div>

<?php if (!$ops): ?>
  <div class="card shadow-sm"><div class="card-body empty-row">
    <i class="bi bi-clock-history"></i> <?= h(t('Nothing changed yet.')) ?>
  </div></div>
<?php else: ?>
  <div class="card shadow-sm overflow-hidden">
    <div class="list-group list-group-flush">
      <?php foreach ($ops as $op): ?>
        <div class="list-group-item d-flex align-items-center gap-3 flex-wrap">
          <i class="bi bi-<?= h($icons[$op['kind']] ?? 'pencil') ?> qa-ico"></i>
          <div class="min-w-0 flex-grow-1">
            <div class="fw-semibold text-truncate"><?= h($op['title']) ?></div>
            <div class="small text-body-secondary">
              <span class="mono"><?= h($op['table']) ?></span> ·
              <?= h(t('rows: %d', (int)$op['rows'])) ?> ·
              <?= h(date('H:i:s', (int)$op['time'])) ?>
            </div>
          </div>
          <?php if ($op['kind'] === 'gst' ? !empty($op['undo']) : (!empty($op['undo']) || !empty($op['multi']))): ?>
            <a class="btn btn-sm btn-outline-warning" href="?a=undo&amp;id=<?= h($op['id']) ?>"
               data-confirm="<?= h(t('Undo this change?')) ?>">
              <i class="bi bi-arrow-counterclockwise"></i> <?= h(t('Undo')) ?></a>
          <?php else: ?>
            <span class="badge text-bg-secondary" data-bs-tooltip="1"
                  title="<?= h(t('This change is too large to undo.')) ?>"><i class="bi bi-lock"></i></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
