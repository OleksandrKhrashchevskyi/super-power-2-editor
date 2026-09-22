<div class="page-head">
  <h1 class="page-title"><i class="bi bi-hdd-stack"></i> <?= h(t('SuperPower 2 database')) ?></h1>
  <p class="page-sub mb-0"><?= h(t('Firebird ODS 10.1 · parsed by PHP itself')) ?> — <code>.gdb</code>
    · <a href="?a=guide"><i class="bi bi-book"></i> <?= h(t('New here? Read the guide.')) ?></a></p>
</div>

<div class="row g-3">
  <div class="col-xl-7">
    <?php $uc = ['field' => 'gdb', 'action' => '?a=upload&ajax=1',
                 'title' => t('Upload DATABASE.GDB'), 'icon' => 'hdd-stack', 'accept' => '.gdb, .fdb, .zip',
                 'hint' => t('Database file, or a .zip with the database and language files.'),
                 'files' => find_game_files('/\.(gdb|fdb)$/i')];
          require __DIR__ . '/upload_card.php'; ?>
  </div>

  <div class="col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-header"><i class="bi bi-gear"></i> <?= h(t('Environment')) ?></div>
      <div class="card-body">
        <ul class="list-group list-group-flush env-list mb-3">
          <li class="list-group-item d-flex align-items-center gap-2 px-0">
            <i class="bi bi-filetype-php"></i><span>PHP</span>
            <span class="ms-auto d-flex gap-1 align-items-center">
              <code><?= h(PHP_VERSION) ?></code>
              <span class="badge text-bg-secondary"><?= PHP_INT_SIZE * 8 ?>-bit</span>
              <span class="badge text-bg-success"><?= h(t('any will do')) ?></span></span></li>
          <li class="list-group-item d-flex align-items-center gap-2 px-0">
            <i class="bi bi-puzzle"></i><span><?= h(t('Extensions')) ?></span>
            <span class="ms-auto"><span class="badge text-bg-success"><?= h(t('not needed')) ?></span></span></li>
          <li class="list-group-item d-flex align-items-center gap-2 px-0">
            <i class="bi bi-file-binary"></i><span><?= h(t('Format')) ?></span>
            <span class="ms-auto"><span class="badge bg-primary-subtle border border-primary-subtle text-primary-emphasis">Firebird ODS 10.x</span></span></li>
        </ul>

        <div class="accordion accordion-flush" id="envAcc">
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed px-0" type="button"
              data-bs-toggle="collapse" data-bs-target="#envHow"><?= h(t('How it works')) ?></button></h2>
            <div id="envHow" class="accordion-collapse collapse" data-bs-parent="#envAcc">
              <div class="accordion-body px-0 small text-body-secondary">
                <?= h(t('The .gdb file is parsed by PHP itself: pages, records, RLE compression and Firebird system tables. No Firebird, no extensions, no external programs — it runs on any hosting. Edits are written back into the same file.')) ?>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed px-0" type="button"
              data-bs-toggle="collapse" data-bs-target="#envProj"><?= h(t('Your project')) ?></button></h2>
            <div id="envProj" class="accordion-collapse collapse" data-bs-parent="#envAcc">
              <div class="accordion-body px-0">
                <div class="small text-body-secondary mb-2">
                  <?= h(t('Everything you upload lands in a folder of its own. Keep this link to come back to the project from another browser or device.')) ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <code class="flex-grow-1"><?= h(project_code()) ?></code>
                  <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#projModal">
                    <i class="bi bi-link-45deg"></i> <?= h(t('Link and settings')) ?></button>
                </div>
              </div>
            </div>
          </div>
          <?php if (LOCAL_MODE): ?>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed px-0" type="button"
              data-bs-toggle="collapse" data-bs-target="#envDir"><?= h(t('Game folder')) ?></button></h2>
            <div id="envDir" class="accordion-collapse collapse" data-bs-parent="#envAcc">
              <div class="accordion-body px-0">
                <form method="post" action="?a=setgame">
                  <div class="form-text mb-2"><?= h(t('optional, only if the editor runs on the same machine as the game')) ?></div>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-folder"></i></span>
                    <input class="form-control mono" name="dir" value="<?= h(game_dir()) ?>"
                           placeholder="D:\SteamLibrary\steamapps\common\SuperPower 2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i></button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
