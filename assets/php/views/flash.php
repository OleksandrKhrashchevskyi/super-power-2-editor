<?php

$ico = ['success' => 'check-circle-fill', 'danger' => 'x-octagon-fill',
        'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
?>
<?php if ($flashes): ?>
<div class="toast-container position-fixed end-0 p-3 app-toasts">
  <?php foreach ($flashes as [$type, $msg]): $t = $type ?: 'info'; ?>
    <div class="toast app-toast t-<?= h($t) ?> border-0 fade show" role="alert" aria-live="assertive" aria-atomic="true"
         data-bs-autohide="<?= $t === 'danger' ? 'false' : 'true' ?>" data-bs-delay="6000">
      <div class="toast-header text-bg-<?= h($t) ?>">
        <i class="bi bi-<?= h($ico[$t] ?? 'info-circle-fill') ?> me-2"></i>
        <strong class="me-auto"><?= h(t(ucfirst($t === 'danger' ? 'Error' : ($t === 'success' ? 'Done' : ($t === 'warning' ? 'Warning' : 'Notice'))))) ?></strong>
        <?php  ?>
        <button type="button" class="btn-close<?= $t === 'warning' ? '' : ' btn-close-white' ?>"
                data-bs-dismiss="toast" aria-label="<?= h(t('Close')) ?>"></button>
      </div>
      <div class="toast-body"><?= h($msg) ?></div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($dbErr && $tab === 'db'): ?>
  <div class="alert alert-danger d-flex gap-3 align-items-start">
    <i class="bi bi-exclamation-octagon-fill fs-4"></i>
    <div class="min-w-0 flex-grow-1">
      <h6 class="alert-heading mb-1"><?= h(t('Could not open the database')) ?></h6>
      <pre class="mb-0 small text-break"><?= h($dbErr) ?></pre>
    </div>
    <a class="btn btn-sm btn-outline-danger flex-shrink-0" href="?a=close" data-confirm="<?= h(t('Close the file and remove it from the project? A copy stays in the backups folder.')) ?>"><i class="bi bi-x-lg"></i></a>
  </div>
<?php endif; ?>
<?php if ($gstErr && $tab === 'lang'): ?>
  <div class="alert alert-danger d-flex gap-3 align-items-start">
    <i class="bi bi-exclamation-octagon-fill fs-4"></i>
    <div class="min-w-0 flex-grow-1">
      <h6 class="alert-heading mb-1"><?= h(t('The language file cannot be read.')) ?></h6>
      <pre class="mb-0 small text-break"><?= h($gstErr) ?></pre>
    </div>
    <a class="btn btn-sm btn-outline-danger flex-shrink-0" href="?a=close_gst" data-confirm="<?= h(t('Close the file and remove it from the project? A copy stays in the backups folder.')) ?>"><i class="bi bi-x-lg"></i></a>
  </div>
<?php endif; ?>
