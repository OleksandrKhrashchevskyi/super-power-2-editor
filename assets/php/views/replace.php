<?php

$tables = Db::tables();
$table  = get_s('t', $curTable ?: 'REGION');
if (!isset($tables[$table])) $table = (string)array_key_first($tables);
if ($table === '') { echo '<div class="alert alert-warning">' . h(t('There are no tables in this database.')) . '</div>'; return; }
$cols   = Db::columns($table);
if (!$cols)         { echo '<div class="alert alert-warning">' . h(t('There are no tables in this database.')) . '</div>'; return; }
$col    = get_s('c');
$colMap = Db::colMap($table);
if (!isset($colMap[$col])) {


    $col = $cols[0]['name'];
    foreach ($cols as $cc) {
        if (!$cc['indexed'] && !is_blob_col($cc)) { $col = $cc['name']; break; }
    }
}
$opSel  = get_s('op', 'eq');
$val    = get_s('val');
$newval = get_s('newval');
$tonull = get_s('tonull') === '1';
$prev   = $_SESSION['rep_preview'] ?? null; unset($_SESSION['rep_preview']);
$c      = $colMap[$col];
$locked = $c['indexed'] || is_blob_col($c);
$OPS = ['eq' => t('equals'), 'ne' => t('does not equal'), 'gt' => t('greater than'),
        'lt' => t('less than'), 'has' => t('contains'), 'empty' => t('is empty'),
        'null' => t('is null'), 'notnull' => t('is not null'), 'any' => t('any value')];
?>
<div class="page-head">
  <h1 class="page-title"><i class="bi bi-search"></i> <?= h(t('Find and replace')) ?></h1>
  <p class="page-sub mb-0"><?= h(t('Changes one column of one table at a time. Preview shows how many rows are affected before anything is written.')) ?></p>
</div>

<form method="post" action="?a=doreplace" class="row g-3">
  <div class="col-xl-5">
    <div class="card shadow-sm">
      <div class="card-header"><i class="bi bi-funnel"></i> <?= h(t('What to change')) ?></div>
      <div class="card-body">
        <label class="form-label small text-body-secondary"><?= h(t('Table')) ?></label>
        <select class="form-select form-select-sm mb-3" name="table" id="repTable"
                onchange="location.href='?a=replace&t='+encodeURIComponent(this.value)">
          <?php foreach ($tables as $name => $_): ?>
            <option value="<?= h($name) ?>" <?= $name === $table ? 'selected' : '' ?>><?= h($name) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label small text-body-secondary"><?= h(t('Column')) ?></label>
        <select class="form-select form-select-sm mb-3" name="col" id="repCol"
                onchange="location.href='?a=replace&t=<?= urlencode($table) ?>&c='+encodeURIComponent(this.value)">
          <?php foreach ($cols as $cc): ?>
            <option value="<?= h($cc['name']) ?>" <?= $cc['name'] === $col ? 'selected' : '' ?>>
              <?= h($cc['name']) ?> — <?= h(type_name($cc)) ?><?= $cc['indexed'] ? ' · IDX' : '' ?></option>
          <?php endforeach; ?>
        </select>

        <?php if ($locked): ?>
          <div class="alert alert-warning py-2 px-3 small mb-0">
            <i class="bi bi-lock-fill"></i>
            <?= h($c['indexed'] ? t('the field belongs to a database index — editing it would break the index') : t('BLOB is not editable')) ?>
          </div>
        <?php else: ?>
          <label class="form-label small text-body-secondary"><?= h(t('Condition')) ?></label>
          <div class="input-group input-group-sm mb-3">
            <select class="form-select" name="op" id="repOp" style="max-width:11rem">
              <?php foreach ($OPS as $k => $lbl): ?>
                <option value="<?= h($k) ?>" <?= $k === $opSel ? 'selected' : '' ?>><?= h($lbl) ?></option>
              <?php endforeach; ?>
            </select>
            <input class="form-control mono" name="val" id="repVal" value="<?= h($val) ?>"
                   placeholder="<?= h(t('value')) ?>">
          </div>

          <label class="form-label small text-body-secondary"><?= h(t('New value')) ?></label>
          <div class="input-group input-group-sm">
            <input class="form-control mono" name="newval" id="repNew" value="<?= h($newval) ?>"
                   <?= $tonull ? 'disabled' : '' ?>>
            <div class="input-group-text null-box">
              <input class="form-check-input mt-0 me-1" type="checkbox" name="tonull" id="repNull" value="1" <?= $tonull ? 'checked' : '' ?>>
              <label class="mb-0 small" for="repNull">null</label>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3 pt-3 border-top">
            <button class="btn btn-outline-primary btn-sm" name="mode" value="preview">
              <i class="bi bi-eye"></i> <?= h(t('Preview')) ?></button>
            <button class="btn btn-primary btn-sm ms-auto" name="mode" value="apply"
                    data-confirm="<?= h(t('Apply to all matching rows?')) ?>">
              <i class="bi bi-check-lg"></i> <?= h(t('Replace')) ?></button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-7">
    <?php if ($prev && $prev['table'] === $table && $prev['col'] === $col): ?>
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="bi bi-eye"></i> <?= h(t('Preview')) ?>
          <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis ms-auto">
            <?= h(t('matched: %d', (int)$prev['hits'])) ?></span>
          <span class="badge rounded-pill text-bg-secondary"><?= h(t('will change: %d', (int)$prev['change'])) ?></span>
        </div>
        <div class="grid-wrap" style="max-height:min(50vh,26rem)">
          <table class="table table-sm table-hover grid align-middle mb-0">
            <thead><tr><th>ID</th><th><?= h(t('was')) ?></th><th><?= h(t('becomes')) ?></th></tr></thead>
            <tbody>
              <?php foreach ($prev['sample'] as [$rid, $was, $id]): ?>
                <tr><td class="mono"><?= $id === null ? '<span class="nullv">—</span>' : (int)$id ?></td>
                  <td class="mono"><?= $was === null ? '<span class="nullv">null</span>' : h((string)$was) ?></td>
                  <td class="mono"><?= $prev['new'] === null ? '<span class="nullv">null</span>' : h((string)$prev['new']) ?></td></tr>
              <?php endforeach; ?>
              <?php if (!$prev['sample']): ?>
                <tr><td colspan="3" class="empty-row"><i class="bi bi-check2-circle"></i> <?= h(t('Nothing to change.')) ?></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($prev['change'] > count($prev['sample'])): ?>
          <div class="card-footer small text-body-secondary">
            <?= h(t('First %d of %d rows shown.', count($prev['sample']), (int)$prev['change'])) ?></div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="card shadow-sm h-100"><div class="card-body empty-row">
        <i class="bi bi-eye"></i> <?= h(t('Press Preview to see what will change.')) ?>
      </div></div>
    <?php endif; ?>
  </div>
</form>
