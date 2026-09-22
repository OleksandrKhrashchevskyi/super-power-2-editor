<?php

declare(strict_types=1);

const FB_TYPES = [
    7 => 'SMALLINT', 8 => 'INTEGER', 9 => 'QUAD', 10 => 'FLOAT', 11 => 'D_FLOAT',
    12 => 'DATE', 13 => 'TIME', 14 => 'CHAR', 16 => 'BIGINT', 27 => 'DOUBLE',
    35 => 'TIMESTAMP', 37 => 'VARCHAR', 40 => 'CSTRING', 45 => 'BLOB_ID', 261 => 'BLOB',
];

function type_name(array $c): string {
    $t = FB_TYPES[$c['type']] ?? ('TYPE' . $c['type']);
    if (in_array($t, ['SMALLINT','INTEGER','BIGINT'], true) && $c['scale'] < 0) return 'NUMERIC(18,' . abs($c['scale']) . ')';
    if ($t === 'CHAR' || $t === 'VARCHAR') return $t . '(' . ($c['clen'] ?: $c['len']) . ')';
    if ($t === 'BLOB') return 'BLOB sub_type ' . $c['sub'];
    return $t;
}
function is_numeric_col(array $c): bool { return in_array($c['type'], [7, 8, 9, 10, 11, 16, 27], true); }
function is_blob_col(array $c): bool { return $c['type'] === 261; }
function is_stid_col(array $c): bool { return (bool)preg_match('/(^|_)STID$/', $c['name']); }


function fmt_num($v, int $type = 27) {
    if ($v === null) return null;
    if (is_int($v)) return (string)$v;
    if (!is_float($v)) return (string)$v;
    if ($v == (int)$v && abs($v) < 1e15) return (string)(int)$v;
    if ($type === 10) {
        $ref = pack('g', $v);
        for ($p = 1; $p <= 9; $p++) { $s = sprintf('%.' . $p . 'G', $v); if (pack('g', (float)$s) === $ref) return $s; }
        return sprintf('%.9G', $v);
    }
    for ($p = 6; $p <= 17; $p++) { $s = sprintf('%.' . $p . 'G', $v); if ((float)$s === $v) return $s; }
    return $s;
}


function guessed_links(string $table): array {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];
    $tables = Db::tables(); $out = [];
    foreach (Db::columns($table) as $c) {
        if (!preg_match('/^(.+)_ID$/', $c['name'], $m)) continue;
        if (isset($tables[$m[1]]) && $m[1] !== $table) $out[$c['name']] = $m[1];
    }
    return $cache[$table] = $out;
}
