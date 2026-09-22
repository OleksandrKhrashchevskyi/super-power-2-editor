<?php
declare(strict_types=1);


function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function post(string $k, $d = null) { return $_POST[$k] ?? $d; }
function get(string $k, $d = null)  { return $_GET[$k]  ?? $d; }


function get_s(string $k, string $d = ''): string  { $v = $_GET[$k]  ?? $d; return is_scalar($v) ? (string)$v : $d; }
function post_s(string $k, string $d = ''): string { $v = $_POST[$k] ?? $d; return is_scalar($v) ? (string)$v : $d; }
function redirect(string $qs): void { header('Location: index.php' . $qs); exit; }
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function is_ajax(): bool { return get_s('ajax') === '1'; }
function jout(array $a): void {
    header('Content-Type: application/json; charset=utf-8');


    $j = json_encode($a, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    echo $j === false ? '{"ok":false,"error":"encode"}' : $j;
    exit;
}

function ini_bytes(string $v): int {
    $v = trim($v);
    if ($v === '') return 0;
    $n = (int)$v;
    $u = strtolower(substr($v, -1));
    if ($u === 'g') return $n * 1073741824;
    if ($u === 'm') return $n * 1048576;
    if ($u === 'k') return $n * 1024;
    return $n;
}

function bytes_h(int $b): string {
    $u = ['B', 'KB', 'MB', 'GB']; $i = 0;
    while ($b >= 1024 && $i < 3) { $b /= 1024; $i++; }
    return sprintf('%.1f %s', $b, $u[$i]);
}


const BACKUP_KEEP = 12;


function make_backup(string $src, string $tag = ''): ?string {
    global $BACKUP;
    if (!is_file($src)) return null;
    $base = basename($src);
    $dst = $BACKUP . DIRECTORY_SEPARATOR . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(2)), 0, 3)
         . ($tag ? "-$tag" : '') . '-' . $base;
    if (!@copy($src, $dst)) return null;
    backup_rotate($base);
    return $dst;
}


function backup_rotate(string $base): void {
    global $BACKUP;

    $all = [];
    $tail = '-' . $base;
    foreach (@glob($BACKUP . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
        $n = basename($f);
        if (!preg_match('/^\d{8}-\d{6}-[0-9a-f]{3}(-[a-z-]+)?-/', $n)) continue;
        if (substr($n, -strlen($tail)) !== $tail) continue;

        if (strlen($n) - strlen($tail) < 19) continue;
        $all[] = $f;
    }
    sort($all);                                  
    while (count($all) > BACKUP_KEEP) { @unlink(array_shift($all)); }

    $cap = intdiv(PROJECT_MAX_BYTES, 2);
    $every = @glob($BACKUP . DIRECTORY_SEPARATOR . '*') ?: [];
    sort($every);
    $sum = 0;
    foreach ($every as $f) $sum += (int)@filesize($f);
    while ($sum > $cap && count($every) > 1) {
        $old = array_shift($every);
        $sum -= (int)@filesize($old);
        @unlink($old);
    }
}

function save_upload(string $field, string $exts, string $tag): string {
    global $WORK;
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {

        $len = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        $max = ini_bytes((string)ini_get('post_max_size'));
        if ($len > 0 && $max > 0 && $len > $max) {
            throw new RuntimeException(t(
                'The file (%s) is larger than the post_max_size limit (%s). Raise upload_max_filesize and post_max_size in php.ini, to 256M for example.',
                bytes_h($len), (string)ini_get('post_max_size')));
        }
        throw new RuntimeException(t('No file chosen.'));
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $m = [1 => t('the file is larger than upload_max_filesize'), 2 => t("the file is larger than the form's MAX_FILE_SIZE"),
              3 => t('the file arrived only partly'), 6 => t('there is no temporary folder'), 7 => t('writing to disk failed')];
        throw new RuntimeException(t('Upload failed: %s', $m[$_FILES[$field]['error']] ?? t('code %d', (int)$_FILES[$field]['error'])));
    }
    $name = preg_replace('/[^A-Za-z0-9_.\- ]/', '_', basename((string)$_FILES[$field]['name']));
    if (!preg_match('/\.(' . $exts . ')$/i', $name)) throw new RuntimeException(t('Expected a .%s file', str_replace('|', ' / .', $exts)));
    $free = PROJECT_MAX_BYTES - project_size();
    if ((int)$_FILES[$field]['size'] > $free) {
        throw new RuntimeException(t('The project is full (limit %s). Close files you no longer need or start a new project.', bytes_h(PROJECT_MAX_BYTES)));
    }
    $dst = $WORK . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dst)) throw new RuntimeException(t('Could not save the file into the project folder.'));
    make_backup($dst, $tag);
    return $dst;
}


function find_game_files(string $re): array {
    $g = game_dir(); $found = [];
    if (!$g || !is_dir($g)) return $found;
    foreach (['MODS', 'Extras'] as $sub) {
        $base = $g . DIRECTORY_SEPARATOR . $sub;
        if (!is_dir($base) || !is_readable($base)) continue;
        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD);
            $it->setMaxDepth(3);
            foreach ($it as $f) if ($f->isFile() && preg_match($re, $f->getFilename())) $found[] = $f->getPathname();
        } catch (Throwable $e) {  }
    }
    sort($found);
    return $found;
}
