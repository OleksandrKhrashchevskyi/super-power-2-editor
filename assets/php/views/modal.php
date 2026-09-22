<?php $cols = Db::columns($curTable); ?>
<div class="modal fade" id="rowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <form class="modal-content" method="post" action="?a=save">
      <div class="modal-header">
        <div class="min-w-0">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> <?= h(t('Edit row')) ?></h5>
          <div class="small text-body-secondary mono"><?= h($curTable) ?> · <span id="rowIdLabel"></span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>

      <div class="modal-sub px-3 py-2 border-bottom">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input class="form-control" id="fldSearch" placeholder="<?= h(t('Field')) ?>…" autocomplete="off">
          <button class="btn btn-outline-secondary" type="button" id="fldClear"
                  aria-label="<?= h(t('Reset')) ?>"><i class="bi bi-x-lg"></i></button>
        </div>
      </div>

      <div class="modal-body">
        <input type="hidden" name="table" value="<?= h($curTable) ?>">
        <input type="hidden" name="rowid" id="rowId">
        <input type="hidden" name="qs" value="<?= h($qs) ?>">

        <div class="field-list" id="fldList">
        <?php foreach ($cols as $c): $n = $c['name']; $ro = $c['indexed'] || is_blob_col($c); ?>
          <div class="field-row row g-2 align-items-center" data-field="<?= h(strtolower($n)) ?>">
            <div class="col-12 col-sm-5">
              <label class="field-label mb-0" for="v_<?= h($n) ?>">
                <span class="mono fw-semibold"><?= h($n) ?></span>
                <span class="field-type"><?= h(type_name($c)) ?></span>
                <?php if ($c['indexed']): ?><span class="badge tag-key">IDX</span><?php endif; ?>
                <?php if (is_stid_col($c)): ?><span class="badge tag-txt">TXT</span><?php endif; ?>
              </label>
            </div>
            <div class="col-12 col-sm-7">
              <?php if ($ro): ?>
                <input class="form-control form-control-sm" id="v_<?= h($n) ?>" disabled
                       data-bs-tooltip="1"
                       title="<?= h(is_blob_col($c) ? t('BLOB is not editable') : t('the field belongs to a database index — editing it would break the index')) ?>">
              <?php else: ?>
                <div class="input-group input-group-sm">
                  <input class="form-control<?= is_numeric_col($c) ? ' mono text-end' : '' ?>" name="v[<?= h($n) ?>]" id="v_<?= h($n) ?>"
                         <?= is_numeric_col($c) ? 'inputmode="decimal"' : '' ?>
                         <?= $c['type'] === 14 || $c['type'] === 37 ? 'maxlength="' . (int)($c['clen'] ?: $c['len']) . '"' : '' ?>>
                  <div class="input-group-text null-box">
                    <input class="form-check-input mt-0 me-1" type="checkbox" name="n[<?= h($n) ?>]" id="n_<?= h($n) ?>"
                           value="1" data-null-for="<?= h($n) ?>">
                    <label class="mb-0 small" for="n_<?= h($n) ?>">null</label>
                  </div>
                </div>
              <?php endif; ?>
              <?php if (is_stid_col($c)): ?><div class="stid" id="lbl_<?= h($n) ?>"></div><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
        <div class="text-body-secondary small text-center py-3 d-none" id="fldEmpty"><?= h(t('Nothing found')) ?></div>
      </div>

      <div class="modal-footer">
        <span class="small text-body-secondary me-auto d-none d-sm-inline">
          <span class="badge tag-key">IDX</span> <?= h(t('fields marked IDX cannot be edited')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal"><?= h(t('Cancel')) ?></button>
        <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> <?= h(t('Save')) ?></button>
      </div>
    </form>
  </div>
</div>

<script id="gridData" type="application/json"><?= json_encode([
  'table' => $curTable,
  'cols'  => array_map(fn($c) => ['n' => $c['name'], 'ro' => ($c['indexed'] || is_blob_col($c)), 'stid' => is_stid_col($c)], $cols),
], JSON_UNESCAPED_UNICODE) ?></script>
