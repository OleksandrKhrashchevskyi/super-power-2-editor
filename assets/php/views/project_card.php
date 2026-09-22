<?php

$psize = project_size();
$pfull = PROJECT_MAX_BYTES > 0 ? min(100, (int)round($psize * 100 / PROJECT_MAX_BYTES)) : 0;
?>
<div class="form-text mb-2">
  <?= h(t('Everything you upload lands in a folder of its own. Keep this link to come back to the project from another browser or device.')) ?>
</div>

<label class="form-label small mb-1"><?= h(t('Project link')) ?></label>
<div class="input-group input-group-sm mb-2">
  <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
  <input class="form-control mono" id="projUrl" value="<?= h(project_url()) ?>" readonly
         onfocus="this.select()">
  <button class="btn btn-outline-secondary" type="button" data-copy="#projUrl"
          data-bs-tooltip="1" title="<?= h(t('Copy')) ?>"><i class="bi bi-clipboard"></i></button>
</div>

<div class="d-flex align-items-center gap-2 mb-1 small">
  <span class="text-body-secondary"><?= h(t('Code')) ?></span>
  <code><?= h(project_code()) ?></code>
  <span class="ms-auto text-body-secondary"><?= h(t('%s of %s', bytes_h($psize), bytes_h(PROJECT_MAX_BYTES))) ?></span>
</div>
<div class="progress mb-2" style="height:.4rem" role="progressbar" aria-valuenow="<?= $pfull ?>" aria-valuemin="0" aria-valuemax="100">
  <div class="progress-bar<?= $pfull > 85 ? ' bg-danger' : '' ?>" style="width:<?= $pfull ?>%"></div>
</div>

<div class="d-flex align-items-center gap-2">
  <span class="small text-body-secondary flex-grow-1">
    <i class="bi bi-clock-history"></i>
    <?= h(t('A project is deleted after %d days without visits.', PROJECT_TTL_DAYS)) ?>
  </span>
  <a class="btn btn-sm btn-outline-secondary" href="?a=newproject"
     onclick="return confirm(<?= h(json_encode(t('Start a new project? The current files stay available by their link.'), JSON_UNESCAPED_UNICODE)) ?>)">
    <i class="bi bi-folder-plus"></i> <?= h(t('New project')) ?></a>
</div>
