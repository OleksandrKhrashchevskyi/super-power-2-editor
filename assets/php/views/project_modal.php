<?php  ?>
<div class="modal fade" id="projModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-folder2-open"></i> <?= h(t('Your project')) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <?php require __DIR__ . '/project_card.php'; ?>
      </div>
    </div>
  </div>
</div>
