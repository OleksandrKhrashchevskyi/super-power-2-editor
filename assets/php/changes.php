<?php

declare(strict_types=1);

const LOG_MAX_OPS   = 200;        
const LOG_MAX_CELLS = 40000;      

function log_ops(): array { return $_SESSION['chlog'] ?? []; }


function log_add(string $kind, string $title, string $table, array $undo, int $rows): string
{
    $cells = 0;
    foreach ($undo as $v) $cells += count($v);
    return log_push(['kind' => $kind, 'title' => $title, 'table' => $table,
                     'rows' => $rows, 'cells' => $cells,
                     'undo' => $cells <= LOG_MAX_CELLS ? $undo : null]);
}


function log_push(array $op): string
{
    $op['id']   = bin2hex(random_bytes(6));
    $op['time'] = time();
    $op['file'] = ($op['kind'] ?? '') === 'gst' ? (string)gst_path() : (string)(Db::path() ?? '');
    $op += ['multi' => null, 'undo' => null];

    $log = $_SESSION['chlog'] ?? [];
    array_unshift($log, $op);
    if (count($log) > LOG_MAX_OPS) $log = array_slice($log, 0, LOG_MAX_OPS);
    $_SESSION['chlog'] = $log;
    return $op['id'];
}


function log_add_gst(array $before, string $file): string
{
    return log_add('gst', t('Language file: %s', $file), '.gst',
                   array_map(fn($v) => ['text' => $v], $before), count($before));
}

function log_find(string $id): ?array
{
    foreach ($_SESSION['chlog'] ?? [] as $op) if ($op['id'] === $id) return $op;
    return null;
}

function log_drop(string $id): void
{
    $_SESSION['chlog'] = array_values(array_filter($_SESSION['chlog'] ?? [], fn($o) => $o['id'] !== $id));
}

function log_clear(): void { unset($_SESSION['chlog']); }


function log_before(string $table, array $ops): array
{
    $before = [];
    $want = $ops;
    foreach (Db::allRows($table) as $r) {
        $rid = $r['__rowid'];
        if (!isset($want[$rid])) continue;
        $row = [];
        foreach (array_keys($want[$rid]) as $col) $row[$col] = $r[$col] ?? null;
        $before[$rid] = $row;
    }
    return $before;
}


function log_add_multi(string $kind, string $title, array $byTable, int $rows): string
{
    $cells = 0;
    foreach ($byTable as $rowsMap) foreach ($rowsMap as $v) $cells += count($v);
    return log_push(['kind' => $kind, 'title' => $title,
                     'table' => implode(', ', array_keys($byTable)),
                     'rows' => $rows, 'cells' => $cells,
                     'multi' => $cells <= LOG_MAX_CELLS ? $byTable : null]);
}
