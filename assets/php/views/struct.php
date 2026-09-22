<?php $table = $curTable; $cols = Db::columns($table); $links = guessed_links($table); ?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title mono"><i class="bi bi-diagram-3"></i> <?= h($table) ?></h1>
    <span class="badge rounded-pill text-bg-secondary"><?= h(t('Structure')) ?></span>
    <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis">
      <i class="bi bi-list-ol"></i> <?= number_format(Db::rowCount($table), 0, '.', ' ') ?></span>
    <span class="badge rounded-pill text-bg-secondary"><i class="bi bi-layout-three-columns"></i> <?= count($cols) ?></span>
    <a class="btn btn-sm btn-primary ms-auto" href="?a=table&t=<?= urlencode($table) ?>">
      <i class="bi bi-table"></i> <?= h(t('Back to data')) ?></a>
  </div>
</div>

<div class="card shadow-sm overflow-hidden">
  <div class="grid-wrap">
    <table class="table table-sm table-hover grid grid-fill align-middle mb-0">
      <thead><tr>
        <th class="num" style="width:3rem">#</th><th><?= h(t('Field')) ?></th><th><?= h(t('Type')) ?></th>
        <th>NULL</th><th class="num"><?= h(t('Offset')) ?></th><th><?= h(t('Domain')) ?></th><th><?= h(t('Notes')) ?></th>
      </tr></thead>
      <tbody>
      <?php foreach ($cols as $i => $c): ?>
        <tr>
          <td class="num text-body-secondary"><?= $i + 1 ?></td>
          <td class="mono fw-semibold"><?= h($c['name']) ?></td>
          <td><span class="badge bg-secondary-subtle border text-body-secondary mono"><?= h(type_name($c)) ?></span></td>
          <td><?= $c['nullable']
                ? '<i class="bi bi-check2 text-success"></i>'
                : '<i class="bi bi-dash-lg text-body-secondary"></i>' ?></td>
          <td class="num mono text-body-secondary"><?= (int)$c['off'] ?></td>
          <td class="text-body-secondary small"><?= h($c['domain']) ?></td>
          <td class="tags-cell">
            <?php if ($c['indexed']): ?><span class="badge tag-key"><i class="bi bi-key-fill"></i> <?= h(t('indexed')) ?></span><?php endif; ?>
            <?php if (isset($links[$c['name']])): ?><a class="badge tag-fk" href="?a=table&t=<?= urlencode($links[$c['name']]) ?>">
              <i class="bi bi-arrow-right-short"></i><?= h($links[$c['name']]) ?></a><?php endif; ?>
            <?php if (is_stid_col($c)): ?><span class="badge tag-txt"><i class="bi bi-translate"></i> <?= h(t('text in .gst')) ?></span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
