<div class="page-head">
  <h1 class="page-title"><i class="bi bi-translate"></i> <?= h(t('Language files')) ?></h1>
  <p class="page-sub mb-0">GolemLabs String Table — <code>.gst</code></p>
</div>

<div class="row g-3">
  <div class="col-xl-7">
    <?php $uc = ['field' => 'gst', 'action' => '?a=upload_gst&ajax=1',
                 'title' => t('Upload StringTable.*.gst'), 'icon' => 'translate', 'accept' => '.gst, .zip',
                 'hint' => t('A .gst file, or a .zip with several of them.'),
                 'files' => find_game_files('/\.gst$/i')];
          require __DIR__ . '/upload_card.php'; ?>
  </div>
  <div class="col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-header"><i class="bi bi-info-circle"></i> <?= h(t('What it is for')) ?></div>
      <div class="card-body">
        <div class="d-flex gap-3 mb-3">
          <i class="bi bi-1-circle-fill fs-5 text-primary"></i>
          <p class="mb-0 small"><?= t('The database keeps no names — only numeric %s. The texts live in %s.', '<code>*_STID</code>', '<code>StringTable.&lt;lang&gt;.gst</code>') ?></p>
        </div>
        <div class="d-flex gap-3 mb-3">
          <i class="bi bi-2-circle-fill fs-5 text-primary"></i>
          <p class="mb-0 small"><?= t('Open a language file here and the grid will show the real text next to %s instead of a number.', '<code>NAME_STID</code>') ?></p>
        </div>
        <div class="d-flex gap-3">
          <i class="bi bi-3-circle-fill fs-5 text-primary"></i>
          <p class="mb-0 small"><?= t('The file can be edited and saved: it is rebuilt as a whole, the previous version goes to %s.', '<code>backups\\</code>') ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
