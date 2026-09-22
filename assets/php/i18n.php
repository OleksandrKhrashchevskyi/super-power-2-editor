<?php

declare(strict_types=1);


const LANGS = [
    'en' => 'English',
    'de' => 'Deutsch',
    'es' => 'Español',
    'fr' => 'Français',
    'it' => 'Italiano',
    'nl' => 'Nederlands',
    'pt' => 'Português',
    'uk' => 'Українська',
    'ru' => 'Русский',
];

function lang(): string
{
    static $l = null;
    if ($l !== null) return $l;
    $v = (string)($_GET['lang'] ?? '');
    if (isset(LANGS[$v])) {
        $_SESSION['ui_lang'] = $v;
        @setcookie('sp2_lang', $v, time() + 31536000, '/');
        return $l = $v;
    }
    $v = (string)($_SESSION['ui_lang'] ?? $_COOKIE['sp2_lang'] ?? '');
    if (isset(LANGS[$v])) return $l = $v;
    return $l = browser_lang();
}


function browser_lang(): string
{
    $h = (string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    if ($h === '') return 'en';
    $best = 'en'; $bestQ = -1.0;
    foreach (explode(',', $h) as $part) {
        $bits = explode(';q=', trim($part));
        $code = strtolower(substr(trim($bits[0]), 0, 2));
        $q    = isset($bits[1]) ? (float)$bits[1] : 1.0;
        if (isset(LANGS[$code]) && $q > $bestQ) { $best = $code; $bestQ = $q; }
    }
    return $best;
}

function t(string $s, ...$args): string
{
    static $dict = null;
    if ($dict === null) $dict = lang_dict(lang());
    $out = $dict[$s] ?? $s;
    if (!$args) return $out;


    try { return vsprintf($out, $args); }
    catch (Throwable $e) {
        try { return vsprintf($s, $args); } catch (Throwable $e2) { return $s; }
    }
}


function lang_dict(string $code): array
{
    static $cache = [];
    if (isset($cache[$code])) return $cache[$code];
    if ($code === 'en' || !isset(LANGS[$code])) return $cache[$code] = [];
    $file = __DIR__ . '/../lang/' . $code . '.json';
    if (!is_file($file)) return $cache[$code] = [];
    $raw = json_decode((string)@file_get_contents($file), true);
    return $cache[$code] = is_array($raw) ? $raw : [];
}
