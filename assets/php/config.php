<?php

declare(strict_types=1);

@ini_set('memory_limit', '512M');   

const APP_NAME = 'SP2 DB Editor';
const PAGE_SIZE_DEFAULT = 50;
const MAX_CELLS_PER_PAGE = 24000;   




const PROJECT_TTL_DAYS = 7;

const PROJECT_MAX_BYTES = 600 * 1024 * 1024;

const SWEEP_EVERY = 6 * 3600;


const LOCAL_MODE = false;

$ROOT     = dirname(__DIR__, 2);
$ASSETS   = $ROOT . '/assets';
$WORKROOT = $ROOT . '/work';


function project_code(): string {
    static $code = null;
    if ($code !== null) return $code;

    $ok = static fn($v) => is_string($v) && preg_match('/^[a-z0-9]{10}$/', $v) === 1;

    $from = (string)($_GET['p'] ?? '');
    if ($ok($from)) {
        $code = $from;
    } elseif ($ok($_COOKIE['sp2_project'] ?? '')) {
        $code = (string)$_COOKIE['sp2_project'];
    } else {
        $a = 'abcdefghijkmnpqrstuvwxyz23456789';   
        $code = '';
        for ($i = 0; $i < 10; $i++) $code .= $a[random_int(0, strlen($a) - 1)];
    }

    if (($_COOKIE['sp2_project'] ?? '') !== $code && !headers_sent()) {
        setcookie('sp2_project', $code, [
            'expires'  => time() + 180 * 86400,
            'path'     => '/',
            'samesite' => 'Lax',
            'httponly' => true,             
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        $_COOKIE['sp2_project'] = $code;
    }


    if (($_SESSION['project'] ?? '') !== $code) {
        unset($_SESSION['db_path'], $_SESSION['db_origin'],
              $_SESSION['gst_files'], $_SESSION['gst_active'], $_SESSION['gst_origin'],
              $_SESSION['chlog'], $_SESSION['merge_preview'], $_SESSION['merge_sel'], $_SESSION['rep_preview']);
        $_SESSION['project'] = $code;
    }
    return $code;
}

function project_dir(): string { global $WORKROOT; return $WORKROOT . '/' . project_code(); }


function project_url(): string {
    $s = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $path = (string)strtok((string)($_SERVER['REQUEST_URI'] ?? '/index.php'), '?');
    return $s . '://' . $host . $path . '?p=' . project_code();
}


function project_size(): int {
    $n = 0;
    foreach (dir_walk(project_dir()) as $f) if (is_file($f)) $n += (int)@filesize($f);
    return $n;
}

function dir_walk(string $dir): array {
    if (!is_dir($dir)) return [];
    $out = [];
    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD);   
        foreach ($it as $f) $out[] = $f->getPathname();
    } catch (Throwable $e) {  }
    return $out;
}

function rm_rf(string $dir): void {
    foreach (dir_walk($dir) as $p) { is_dir($p) ? @rmdir($p) : @unlink($p); }
    @rmdir($dir);
}


function projects_sweep(): void {
    global $WORKROOT;
    $stamp = $WORKROOT . '/.sweep';
    if (is_file($stamp) && time() - (int)@filemtime($stamp) < SWEEP_EVERY) return;
    @touch($stamp);
    $edge = time() - PROJECT_TTL_DAYS * 86400;
    foreach ((array)@scandir($WORKROOT) as $n) {
        if (!preg_match('/^[a-z0-9]{10}$/', (string)$n)) continue;
        $d = $WORKROOT . '/' . $n;
        if (!is_dir($d) || $n === project_code()) continue;
        $seen = is_file($d . '/.alive') ? (int)@filemtime($d . '/.alive') : (int)@filemtime($d);
        if ($seen < $edge) rm_rf($d);
    }
}


function guard_dir(string $dir): void {
    if (!is_file($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess',
            "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n" .
            "<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    }
    if (!is_file($dir . '/web.config')) {
        @file_put_contents($dir . '/web.config',
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer>\n" .
            "  <security><authorization><deny users=\"*\" /></authorization></security>\n" .
            "</system.webServer></configuration>\n");
    }
    if (!is_file($dir . '/index.html')) @file_put_contents($dir . '/index.html', '');
}



foreach ([$WORKROOT] as $d) if (!is_dir($d)) @mkdir($d, 0777, true);
guard_dir($WORKROOT);
projects_sweep();

$WORK    = project_dir();
$BACKUP  = $WORK . '/backups';
$CFGFILE = $WORK . '/config.json';
foreach ([$WORK, $BACKUP] as $d) if (!is_dir($d)) @mkdir($d, 0777, true);
@touch($WORK . '/.alive');

function cfg_load(): array {
    global $CFGFILE;
    $c = is_file($CFGFILE) ? json_decode((string)file_get_contents($CFGFILE), true) : [];
    return is_array($c) ? $c : [];
}
function cfg_save(array $c): void { global $CFGFILE; file_put_contents($CFGFILE, json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); }


function game_dir(): string {
    static $g = null;
    if ($g !== null) return $g;
    if (!LOCAL_MODE) return $g = '';
    $c = cfg_load();
    foreach ([$c['game_dir'] ?? '', getenv('SP2_GAME_DIR') ?: ''] as $cand) {
        if ($cand && is_dir($cand)) return $g = rtrim($cand, '\\/');
    }
    return $g = '';
}
