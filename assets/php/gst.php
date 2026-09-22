<?php

declare(strict_types=1);




function gst_files(): array { return array_values(array_filter((array)($_SESSION['gst_files'] ?? []), 'is_file')); }
function gst_active(): int  { $n = count(gst_files()); $i = (int)($_SESSION['gst_active'] ?? 0); return $n ? max(0, min($i, $n - 1)) : 0; }
function gst_path(): ?string { $f = gst_files(); return $f[gst_active()] ?? null; }
function gst_label(string $path): string {
    $b = basename($path);
    if (preg_match('/stringtable[._-]*([a-z]+)\.gst$/i', $b, $m)) return ucfirst(strtolower($m[1]));
    return preg_replace('/\.gst$/i', '', $b);
}
function gst_add(string $path): void {
    $f = (array)($_SESSION['gst_files'] ?? []);
    foreach ($f as $k => $p) if (basename((string)$p) === basename($path)) { $f[$k] = $path; $_SESSION['gst_files'] = array_values($f); return; }
    $f[] = $path;
    $_SESSION['gst_files'] = array_values($f);
    $_SESSION['gst_active'] = count($f) - 1;
}
function gst_use(int $i): void { $_SESSION['gst_active'] = $i; }
function gst_drop(int $i): void {


    $f = gst_files();
    unset($f[$i]);
    $_SESSION['gst_files'] = array_values($f);
    $_SESSION['gst_active'] = 0;
}

function gst_buf(bool $reload = false): string {
    static $buf = null;
    if ($buf === null || $reload) {
        $p = gst_path();
        if (!$p || !is_file($p)) throw new RuntimeException(t('No language file is open.'));
        $buf = (string)file_get_contents($p);
    }
    return $buf;
}


function gst_index(bool $reload = false): array {
    static $idx = null;
    if ($idx !== null && !$reload) return $idx;
    $buf = gst_buf($reload);
    if (strlen($buf) < 4) throw new RuntimeException(t('The file is empty.'));
    $cnt = unpack('V', substr($buf, 0, 4))[1];
    if ($cnt <= 0 || $cnt > 2000000 || 4 + $cnt * 12 > strlen($buf)) {
        throw new RuntimeException(t('This does not look like a .gst file (count = %d).', $cnt));
    }
    $idx = [];
    for ($i = 0; $i < $cnt; $i++) {
        $e = unpack('Vid/Voff/Vlen', substr($buf, 4 + $i * 12, 12));
        $idx[] = [$e['id'], $e['off'], $e['len']];
    }
    return $idx;
}

function gst_text(int $off, int $len): string {
    if ($len <= 0) return '';
    $bin = substr(gst_buf(), $off, $len);
    $s = @iconv('UTF-16LE', 'UTF-8//IGNORE', $bin);
    return $s === false ? '' : $s;
}


function gst_index_safe(): array {
    try { return gst_path() ? gst_index() : []; }
    catch (Throwable $e) { return []; }
}


function gst_map(): array {
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    if (!gst_path()) return $map;
    try { foreach (gst_index() as [$id, $off, $len]) $map[$id] = [$off, $len]; }
    catch (Throwable $e) { $map = []; }
    return $map;
}

function gst_lookup($id): ?string {
    if ($id === null || $id === '' || !is_numeric($id)) return null;
    $m = gst_map(); $id = (int)$id;
    if (!isset($m[$id])) return null;
    return trim(str_replace(["\r", "\n"], ' ', gst_text($m[$id][0], $m[$id][1])));
}


function gst_write(array $changes): int {
    $p   = (string)gst_path();
    $buf = gst_buf();
    $n = 0; $parts = [];
    foreach (gst_index() as [$id, $off, $len]) {
        if (array_key_exists($id, $changes)) {
            $bin = @iconv('UTF-8', 'UTF-16LE//IGNORE', (string)$changes[$id]);
            $parts[] = [$id, $bin === false ? '' : $bin];
            $n++;
        } else {
            $parts[] = [$id, $len > 0 ? substr($buf, $off, $len) : ''];
        }
    }
    $count = count($parts);
    $cur   = 4 + $count * 12;
    $index = pack('V', $count);
    $data  = '';
    foreach ($parts as [$id, $bin]) {
        $index .= pack('VVV', $id, $cur, strlen($bin));
        $cur   += strlen($bin);
        $data  .= $bin;
    }
    make_backup($p, 'gst');

    $tmp = $p . '.tmp' . getmypid();
    if (@file_put_contents($tmp, $index . $data) === false || !@rename($tmp, $p)) {
        @unlink($tmp);
        throw new RuntimeException(t('Could not write the language file.'));
    }
    gst_buf(true); gst_index(true);
    return $n;
}



function files_by_ext(string $dir, array $exts): array {
    $out = [];
    foreach ((array)@scandir($dir) as $n) {
        if ($n === '.' || $n === '..') continue;
        $p = $dir . DIRECTORY_SEPARATOR . $n;
        if (!is_file($p)) continue;
        if (in_array(strtolower((string)pathinfo($n, PATHINFO_EXTENSION)), $exts, true)) $out[] = $p;
    }
    sort($out);
    return $out;
}


function project_adopt(): void {
    global $WORK;
    if (!is_dir($WORK)) return;

    if (!isset($_SESSION['db_path']) || !is_file((string)$_SESSION['db_path'])) {
        $db = files_by_ext($WORK, ['gdb', 'fdb']);
        if ($db) { sort($db); $_SESSION['db_path'] = $db[0]; }
        else unset($_SESSION['db_path']);
    }
    if (!gst_files()) {
        $g = files_by_ext($WORK, ['gst']);
        foreach ($g as $f) gst_add($f);
        if ($g) $_SESSION['gst_active'] = 0;
    }
}
