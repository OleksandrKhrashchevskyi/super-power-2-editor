<?php

$countries = [];
foreach (Db::allRows('COUNTRY') as $r) {
    $name = $gstOpen ? gst_lookup($r['NAME_STID'] ?? null) : null;
    $countries[] = ['id' => (int)$r['ID'], 'code' => (string)$r['CODE'],
                    'name' => $name ?: (string)$r['CODE'], 'active' => ($r['ACTIVATED'] ?? '') === 'T',
                    'rowid' => $r['__rowid']];
}
usort($countries, fn($a, $b) => strcasecmp($a['name'], $b['name']));
$target  = (int)get('target', 0);
$sources = array_map('intval', (array)get('src', []));
$preview = $_SESSION['merge_preview'] ?? null; unset($_SESSION['merge_preview']);
$nActive = count(array_filter($countries, fn($c) => $c['active']));
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-diagram-2"></i> <?= h(t('Merge countries')) ?></h1>
    <span class="badge rounded-pill text-bg-secondary"><?= count($countries) ?> <?= h(t('Countries')) ?></span>
    <span class="badge rounded-pill bg-success-subtle border border-success-subtle text-success-emphasis">
      <i class="bi bi-check-circle"></i> <?= $nActive ?> <?= h(t('active')) ?></span>
    <?php if (!$gstOpen): ?>
      <a class="btn btn-sm btn-outline-info ms-auto" href="?a=lang"><i class="bi bi-translate"></i> <?= h(t('attach dictionary')) ?></a>
    <?php endif; ?>
  </div>
  <p class="page-sub mb-0"><?= h(t('Territory, cities, units, parties and treaties of the selected countries will be transferred to the target country.')) ?>
    <?= h(t('Relations matrix (RELATIONS) is left untouched.')) ?></p>
</div>

<form method="post" action="?a=domerge" class="row g-3">
  <div class="col-xl-5">
    <div class="card shadow-sm merge-target">
      <div class="card-header"><i class="bi bi-bullseye"></i> <?= h(t('Target country (everything will be transferred to it)')) ?></div>
      <div class="card-body">
        <select name="target" id="mTarget" class="form-select" required
                placeholder="<?= h(t('Pick a country…')) ?>" autocomplete="off">
          <option value=""></option>
          <?php foreach ($countries as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $target === $c['id'] ? 'selected' : '' ?>
                    data-code="<?= h($c['code']) ?>" data-cid="<?= $c['id'] ?>">
              <?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <div class="mt-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="deactivate" id="mDeact" value="1" checked>
            <label class="form-check-label" for="mDeact"><?= h(t('Deactivate merged countries')) ?></label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="military" id="mMil" value="1" checked>
            <label class="form-check-label" for="mMil"><?= h(t('Transfer military control of regions')) ?></label>
          </div>
        </div>

        <div class="alert alert-secondary py-2 px-3 small mt-3 mb-0 d-flex gap-2">
          <i class="bi bi-lightbulb"></i>
          <span><?= h(t('Run Preview first — it shows how many rows each table will lose or gain.')) ?></span>
        </div>

        <div class="d-flex gap-2 mt-3 pt-3 border-top">
          <button class="btn btn-outline-primary" name="mode" value="preview"><i class="bi bi-eye"></i> <?= h(t('Preview')) ?></button>
          <button class="btn btn-primary ms-auto" name="mode" value="apply"
                  data-confirm="<?= h(t('Merge countries')) ?>?"><i class="bi bi-diagram-2"></i> <?= h(t('Merge')) ?></button>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-list-check"></i> <?= h(t('Countries to merge in')) ?>
        <span class="badge rounded-pill text-bg-primary ms-1" id="cPicked">0</span>
        <div class="input-group input-group-sm ms-auto" style="max-width:240px">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="cSearch" class="form-control" placeholder="<?= h(t('Search')) ?>" autocomplete="off">
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="cClear"
                data-bs-tooltip="1" title="<?= h(t('Reset')) ?>"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="country-list list-group list-group-flush" id="cList" data-scroll>
        <?php foreach ($countries as $c): ?>
          <label class="list-group-item country-item d-flex align-items-center gap-2">
            <input class="form-check-input m-0 flex-shrink-0" type="checkbox" name="src[]" value="<?= $c['id'] ?>"
                   <?= in_array($c['id'], $sources, true) ? 'checked' : '' ?>>
            <span class="cname text-truncate flex-grow-1"><?= h($c['name']) ?></span>
            <span class="badge bg-secondary-subtle border text-body-secondary mono"><?= h($c['code']) ?></span>
            <span class="text-body-secondary small mono cid"><?= $c['id'] ?></span>
            <?php if (!$c['active']): ?><span class="badge tag-fk"><?= h(t('off')) ?></span><?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</form>

<?php if ($preview !== null): ?>
  <div class="card shadow-sm mt-3">
    <div class="card-header"><i class="bi bi-eye"></i> <?= h(t('Will be changed')) ?></div>
    <div class="grid-wrap" style="max-height:min(50vh,26rem)">
      <table class="table table-sm table-hover grid align-middle mb-0">
        <thead><tr><th><?= h(t('table')) ?></th><th><?= h(t('column')) ?></th><th class="num"><?= h(t('rows to change')) ?></th></tr></thead>
        <tbody>
        <?php $sum = 0; foreach ($preview as $row): $sum += $row[2]; ?>
          <tr><td class="mono"><a href="?a=table&t=<?= urlencode((string)$row[0]) ?>"><?= h($row[0]) ?></a></td>
              <td class="mono text-body-secondary"><?= h($row[1]) ?></td>
              <td class="num"><span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis"><?= (int)$row[2] ?></span></td></tr>
        <?php endforeach; ?>
        <?php if (!$preview): ?><tr><td colspan="3" class="empty-row"><i class="bi bi-check2-circle"></i> <?= h(t('Nothing to change.')) ?></td></tr><?php endif; ?>
        </tbody>
        <?php if ($preview): ?><tfoot><tr><th colspan="2" class="text-end"><?= h(t('Total')) ?></th>
          <th class="num"><?= number_format($sum, 0, '.', ' ') ?></th></tr></tfoot><?php endif; ?>
      </table>
    </div>
  </div>
<?php endif; ?>
