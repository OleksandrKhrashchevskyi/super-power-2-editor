<?php $lim = (string)ini_get('upload_max_filesize'); $limB = ini_bytes($lim); ?>
<div class="card shadow-sm h-100">
  <div class="card-header d-flex align-items-center gap-2">
    <i class="bi bi-<?= h($uc['icon']) ?>"></i> <?= h($uc['title']) ?>
    <span class="badge rounded-pill text-bg-secondary ms-auto"><?= h($uc['accept']) ?></span>
  </div>
  <div class="card-body">
    <form class="uploader" data-action="<?= h($uc['action']) ?>" data-field="<?= h($uc['field']) ?>">
      <label class="dropzone" tabindex="0">
        <input type="file" name="<?= h($uc['field']) ?>" accept="<?= h($uc['accept']) ?>" class="visually-hidden">
        <i class="bi bi-cloud-arrow-up dz-ico"></i>
        <span class="dz-main"><?= h(t('Drop the file here or click to choose')) ?></span>
        <span class="dz-sub"><?= h($uc['hint']) ?></span>
        <span class="dz-file d-none"></span>
      </label>

      <div class="progress mt-3 d-none" role="progressbar" style="height:1.4rem">
        <div class="progress-bar progress-bar-striped progress-bar-animated">0%</div>
      </div>
      <div class="up-status small text-body-secondary mt-1"></div>

      <div class="d-flex align-items-center gap-2 mt-3">
        <button class="btn btn-primary"><i class="bi bi-box-arrow-in-up"></i> <?= h(t('Upload')) ?></button>
        <span class="small text-body-secondary ms-auto"><?= h(t('Upload limit: %s.', $lim)) ?></span>
      </div>

      <?php if ($limB < 64 * 1048576): ?>
        <div class="alert alert-warning py-2 px-3 small mt-3 mb-0">
          <i class="bi bi-exclamation-triangle-fill"></i> <?= h($lim) ?> &lt; 64M —
          <code>upload_max_filesize=256M</code>, <code>post_max_size=256M</code>
        </div>
      <?php endif; ?>
    </form>

    <?php if ($uc['files']): ?>
      <hr>
      <div class="small text-body-secondary mb-2"><?= h(t('…or open straight from the game — we work on a copy:')) ?></div>
      <div class="list-group list-group-flush local-list">
        <?php foreach ($uc['files'] as $f): ?>
          <a class="list-group-item list-group-item-action d-flex align-items-center gap-2"
             href="?a=openlocal&path=<?= urlencode($f) ?>">
            <i class="bi bi-folder2-open"></i>
            <span class="text-truncate flex-grow-1 mono small"><?= h(str_replace(game_dir() . DIRECTORY_SEPARATOR, '', $f)) ?></span>
            <span class="badge rounded-pill text-bg-secondary"><?= bytes_h((int)filesize($f)) ?></span></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
