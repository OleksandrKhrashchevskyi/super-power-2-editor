<?php

$themes = [
  ['midnight',   '#12151b', '#4f8cff', 'dark',  'Midnight'],
  ['graphite',   '#151515', '#9aa0a6', 'dark',  'Graphite'],
  ['nord',       '#2e3440', '#88c0d0', 'dark',  'Nord'],
  ['dracula',    '#282a36', '#bd93f9', 'dark',  'Dracula'],
  ['solar-dark', '#002b36', '#b58900', 'dark',  'Solarized Dark'],
  ['forest',     '#0f1912', '#57c785', 'dark',  'Forest'],
  ['amber',      '#141007', '#ffb300', 'dark',  'Amber'],
  ['crimson',    '#160f12', '#ff5c7a', 'dark',  'Crimson'],
  ['paper',      '#f6f7f9', '#2563eb', 'light', 'Paper'],
  ['solar-light','#fdf6e3', '#268bd2', 'light', 'Solarized Light'],
];
$fonts = ['inter' => 'Inter', 'system' => 'System UI', 'segoe' => 'Segoe UI', 'helvetica' => 'Helvetica',
          'verdana' => 'Verdana', 'tahoma' => 'Tahoma', 'trebuchet' => 'Trebuchet MS', 'georgia' => 'Georgia',
          'palatino' => 'Palatino', 'jetbrains' => 'JetBrains Mono'];
$accents = ['#4f8cff', '#7b5cff', '#57c785', '#ffb300', '#ff5c7a', '#20c5c5', '#e07b39', '#9aa0a6'];
?>
<div class="modal fade" id="uiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-palette"></i> <?= h(t('Appearance')) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>

      <div class="modal-body pb-0">
        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#uiTabTheme" type="button">
            <i class="bi bi-droplet-half"></i> <?= h(t('Theme')) ?></button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#uiTabType" type="button">
            <i class="bi bi-fonts"></i> <?= h(t('Font')) ?></button></li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade show active" id="uiTabTheme">
            <div class="theme-grid mb-3">
              <?php foreach ($themes as [$id, $bg, $ac, $kind, $name]): ?>
                <button type="button" class="theme-dot" data-theme-id="<?= h($id) ?>" data-kind="<?= h($kind) ?>"
                       >
                  <span class="td-swatch" style="background:<?= h($bg) ?>">
                    <i style="background:<?= h($ac) ?>"></i>
                    <b style="background:<?= h($kind === 'dark' ? 'rgba(255,255,255,.22)' : 'rgba(0,0,0,.18)') ?>"></b>
                  </span>
                  <em><?= h($name) ?></em>
                  <i class="bi bi-check-circle-fill td-check"></i>
                </button>
              <?php endforeach; ?>
            </div>

            <label class="form-label small text-body-secondary" for="uiAccent"><?= h(t('Accent colour')) ?></label>
            <div class="swatch-row mb-3">
              <?php foreach ($accents as $a): ?>
                <button type="button" class="swatch" data-accent-pick="<?= h($a) ?>" style="background:<?= h($a) ?>"
                        data-bs-tooltip="1" title="<?= h($a) ?>"></button>
              <?php endforeach; ?>
              <input type="color" id="uiAccent" class="form-control form-control-color form-control-sm" title="<?= h(t('Custom')) ?>">
            </div>

            <label class="form-label small text-body-secondary" for="uiText"><?= h(t('Text colour')) ?></label>
            <div class="d-flex align-items-center gap-2 mb-3">
              <input type="color" id="uiText" class="form-control form-control-color form-control-sm"
                     title="<?= h(t('Text colour')) ?>" aria-label="<?= h(t('Text colour')) ?>">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="uiTextAuto">
                <i class="bi bi-magic"></i> <?= h(t('From theme')) ?></button>
              <span class="small text-body-secondary d-none d-sm-inline"><?= h(t('Overrides the theme text colour.')) ?></span>
            </div>
          </div>

          <div class="tab-pane fade" id="uiTabType">
            <label class="form-label small text-body-secondary" for="uiFont"><?= h(t('Font')) ?></label>
            <select class="form-select" id="uiFont">
              <?php foreach ($fonts as $id => $name): ?>
                <option value="<?= h($id) ?>" data-font-id="<?= h($id) ?>"><?= h($name) ?></option>
              <?php endforeach; ?>
            </select>

            <label class="form-label small text-body-secondary mt-3" id="uiSizeLbl"><?= h(t('Size')) ?></label>
            <div class="btn-group btn-group-sm d-flex flex-wrap" id="uiSize" role="group" aria-labelledby="uiSizeLbl">
              <?php foreach ([12, 14, 15, 16, 18, 20, 22] as $px): ?>
                <button type="button" class="btn btn-outline-secondary" data-size="<?= $px ?>"><?= $px ?></button>
              <?php endforeach; ?>
            </div>

            <div class="form-check form-switch mt-3">
              <input class="form-check-input" type="checkbox" role="switch" id="uiDense">
              <label class="form-check-label" for="uiDense"><?= h(t('Compact rows')) ?></label>
            </div>
          </div>
        </div>

        <div class="ui-preview mt-3">
          <div class="ui-preview-title"><i class="bi bi-eye"></i> <?= h(t('Preview')) ?></div>
          <div class="card overflow-hidden">
            <table class="table table-sm table-hover grid align-middle mb-0">
              <thead><tr class="th-row">
                <th><a class="th-name"><span class="th-label">ID</span></a><span class="th-tags"><span class="badge tag-key">IDX</span></span></th>
                <th><a class="th-name"><span class="th-label">NAME_STID</span></a><span class="th-tags"><span class="badge tag-txt">TXT</span></span></th>
                <th><a class="th-name"><span class="th-label">CODE</span></a></th>
              </tr></thead>
              <tbody>
                <tr><td class="num">1</td><td class="num">2136<div class="stid">Afghanistan</div></td><td>AFG</td></tr>
                <tr><td class="num">2</td><td class="num">2137<div class="stid">Albania</div></td><td>ALB</td></tr>
                <tr><td class="num">3</td><td class="num"><span class="nullv">null</span></td><td>DZA</td></tr>
              </tbody>
            </table>
          </div>
          <div class="d-flex gap-2 mt-2" aria-hidden="true">
            <button type="button" class="btn btn-sm btn-primary" tabindex="-1"><i class="bi bi-check-lg"></i> <?= h(t('Save')) ?></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" tabindex="-1"><?= h(t('Cancel')) ?></button>
            <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis ms-auto align-self-center">badge</span>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary me-auto" id="uiReset">
          <i class="bi bi-arrow-counterclockwise"></i> <?= h(t('Reset')) ?></button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal"><?= h(t('Done')) ?></button>
      </div>
    </div>
  </div>
</div>
