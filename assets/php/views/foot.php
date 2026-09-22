<script id="i18n" type="application/json"><?= json_encode(js_i18n(), JSON_UNESCAPED_UNICODE) ?></script>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/simplebar/simplebar.min.js"></script>
<script src="assets/vendor/tom-select/tom-select.complete.min.js"></script>
<script src="assets/vendor/nprogress/nprogress.js"></script>
<script src="assets/js/app.js?v=8"></script>
<?php if (($action ?? '') === 'map' && $dbOpen): ?>
<script src="assets/js/map.js?v=8"></script>
<?php endif; ?>
</body>
</html>
