<?php

$nReg = Db::rowCount('REGION');
$nCit = Db::rowCount('CITIES');
$nCou = Db::rowCount('COUNTRY');
?>
<div class="page-head">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h1 class="page-title"><i class="bi bi-globe-americas"></i> <?= h(t('World map')) ?></h1>
    <span class="badge rounded-pill bg-primary-subtle border border-primary-subtle text-primary-emphasis">
      <i class="bi bi-pin-map"></i> <?= number_format($nReg, 0, '.', ' ') ?> <?= h(t('regions')) ?></span>
    <span class="badge rounded-pill text-bg-secondary"><i class="bi bi-buildings"></i> <?= number_format($nCit, 0, '.', ' ') ?></span>
    <?php if (!$gstOpen): ?>
      <a class="btn btn-sm btn-outline-info ms-auto" href="?a=lang"><i class="bi bi-translate"></i> <?= h(t('attach dictionary')) ?></a>
    <?php endif; ?>
  </div>
</div>

<div class="card shadow-sm map-card">
  <div class="card-header map-toolbar">
    <div class="map-pick map-pick-lay">
      <label class="visually-hidden" for="mapLayer"><?= h(t('Colour by')) ?></label>
      <select class="form-select form-select-sm" id="mapLayer">
        <optgroup label="<?= h(t('Ownership')) ?>">
          <option value="country"><?= h(t('Country')) ?></option>
          <option value="mil"><?= h(t('Military control')) ?></option>
        </optgroup>
        <optgroup label="<?= h(t('Population')) ?>">
          <option value="lang"><?= h(t('Dominant language')) ?></option>
          <option value="rel"><?= h(t('Dominant religion')) ?></option>
          <option value="sharelang"><?= h(t('Share of one language')) ?></option>
          <option value="sharerel"><?= h(t('Share of one religion')) ?></option>
        </optgroup>
        <optgroup label="<?= h(t('State')) ?>">
          <option value="gvt"><?= h(t('Government type')) ?></option>
          <option value="relation"><?= h(t('Relations of one country')) ?></option>
          <option value="treaty"><?= h(t('Treaty members')) ?></option>
        </optgroup>
        <optgroup label="<?= h(t('Military')) ?>">
          <option value="force"><?= h(t('Total units')) ?></option>
          <option value="forcetype"><?= h(t('Units of one type')) ?></option>
        </optgroup>
        <optgroup label="<?= h(t('Geography')) ?>">
          <option value="cont"><?= h(t('Continent')) ?></option>
          <option value="geo"><?= h(t('Geo group')) ?></option>
        </optgroup>
        <optgroup label="<?= h(t('Numbers')) ?>">
          <option value="pop"><?= h(t('Region population')) ?></option>
          <option value="infra"><?= h(t('Infrastructure')) ?></option>
          <option value="tele"><?= h(t('Telecom')) ?></option>
        </optgroup>
      </select>
    </div>

    <div class="map-pick" id="mapItemWrap" hidden>
      <select id="mapItem" placeholder="<?= h(t('Pick…')) ?>" autocomplete="off"></select>
    </div>

    <div class="map-pick">
      <select id="mapCountry" placeholder="<?= h(t('Highlight a country…')) ?>" autocomplete="off"></select>
    </div>

    <div class="btn-group btn-group-sm ms-auto" role="group">
      <button type="button" class="btn btn-outline-secondary" id="mapZoomOut" data-bs-tooltip="1" title="−"><i class="bi bi-dash-lg"></i></button>
      <button type="button" class="btn btn-outline-secondary" id="mapZoomIn" data-bs-tooltip="1" title="+"><i class="bi bi-plus-lg"></i></button>
      <button type="button" class="btn btn-outline-secondary" id="mapReset" data-bs-tooltip="1" title="<?= h(t('Reset')) ?>"><i class="bi bi-arrows-fullscreen"></i></button>
    </div>

    <div class="dropdown">
      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
              data-bs-auto-close="outside" aria-label="<?= h(t('Layers')) ?>">
        <i class="bi bi-layers"></i> <span class="d-none d-md-inline"><?= h(t('Layers')) ?></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end p-2 map-layers">
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyBorders" checked>
          <label class="form-check-label" for="lyBorders"><?= h(t('Country borders')) ?></label></div></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyRegions" checked>
          <label class="form-check-label" for="lyRegions"><?= h(t('Region borders')) ?></label></div></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyCoast" checked>
          <label class="form-check-label" for="lyCoast"><?= h(t('Coastline')) ?></label></div></li>
        <li><hr class="dropdown-divider"></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyCities" checked>
          <label class="form-check-label" for="lyCities"><?= h(t('Cities')) ?></label></div></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyCaps" checked>
          <label class="form-check-label" for="lyCaps"><?= h(t('Capitals')) ?></label></div></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyTroops">
          <label class="form-check-label" for="lyTroops"><?= h(t('Troop groups')) ?></label></div></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyMissiles">
          <label class="form-check-label" for="lyMissiles"><?= h(t('Missiles')) ?></label></div></li>
        <li><hr class="dropdown-divider"></li>
        <li><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="lyInactive">
          <label class="form-check-label" for="lyInactive"><?= h(t('Dim inactive countries')) ?></label></div></li>
      </ul>
    </div>
  </div>

  <div class="map-stage" id="mapStage">
    <canvas id="mapCanvas"></canvas>

    <div class="map-loading" id="mapLoading">
      <div class="spinner-border text-primary mb-2" role="status"></div>
      <div class="small" id="mapLoadingText"><?= h(t('Building the map…')) ?></div>
      <div class="progress mt-2" style="width:14rem;height:.4rem">
        <div class="progress-bar" id="mapProgress" style="width:0%"></div>
      </div>
    </div>

    <div class="map-tip" id="mapTip" hidden></div>

    <div class="map-legend" id="mapLegend" hidden>
      <button type="button" class="ml-title" id="mapLegendToggle" aria-expanded="true">
        <span class="ml-title-t"></span><i class="bi bi-chevron-down ml-caret"></i>
      </button>
      <div class="ml-rows"></div>
    </div>

    <div class="map-hint" id="mapHint">
      <i class="bi bi-mouse"></i> <?= h(t('drag — pan · wheel — zoom · click — edit region · Shift+drag — select')) ?>
    </div>
  </div>

  <form class="card-footer map-sel" id="mapSelBar" method="post" action="?a=mapassign" hidden>
    <input type="hidden" name="reg" id="mapSelIds">
    <span class="badge rounded-pill text-bg-primary" id="mapSelCount">0</span>
    <span class="small text-body-secondary d-none d-lg-inline"><?= h(t('regions selected')) ?></span>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="mapSelClear" aria-label="<?= h(t('Clear')) ?>">
      <i class="bi bi-x-lg"></i> <span class="d-none d-sm-inline"><?= h(t('Clear')) ?></span></button>

    <div class="map-pick ms-auto">
      <select name="target" id="mapSelTarget" required placeholder="<?= h(t('Transfer to…')) ?>" autocomplete="off"></select>
    </div>
    <select name="what" class="form-select form-select-sm w-auto">
      <option value="country"><?= h(t('Ownership')) ?></option>
      <option value="mil"><?= h(t('Military control')) ?></option>
      <option value="both"><?= h(t('Both')) ?></option>
    </select>
    <button class="btn btn-sm btn-primary" data-confirm="<?= h(t('Transfer the selected regions?')) ?>">
      <i class="bi bi-arrow-left-right"></i> <?= h(t('Transfer')) ?></button>
  </form>
</div>

<div class="small text-body-secondary mt-2">
  <i class="bi bi-info-circle"></i>
  <?= h(t('Region geometry is not stored in the database: territory is drawn by the nearest of %d cities, so the borders are approximate. Coastlines — Natural Earth, public domain.', $nCit)) ?>
</div>

<script id="mapMeta" type="application/json"><?= json_encode([
  'url'   => 'index.php?a=mapdata',
  'land'  => 'assets/vendor/geo/land.json',
  'cous'  => 'assets/vendor/geo/countries.json',
  'gst'   => $gstOpen,
  'layer' => get_s('c', 'country'),
], JSON_UNESCAPED_UNICODE) ?></script>
