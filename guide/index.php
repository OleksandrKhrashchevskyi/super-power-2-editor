<?php

declare(strict_types=1);
$q = $_GET ?: [];
$q['a'] = 'guide';
header('Location: ../index.php?' . http_build_query($q), true, 302);
exit;
