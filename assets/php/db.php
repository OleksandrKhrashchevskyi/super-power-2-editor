<?php

declare(strict_types=1);

require_once __DIR__ . '/gdb.php';

final class Db
{
    private static ?Gdb $g = null;
    private static ?Gdb $w = null;      
    public static float $time = 0.0;

    public static function path(): ?string { return $_SESSION['db_path'] ?? null; }

    public static function open(bool $write = false): Gdb
    {
        if (self::$g !== null && !$write) return self::$g;


        if ($write) self::$g = null;
        $p = self::path();
        if (!$p || !is_file($p)) throw new GdbError(t('Database is not open.'));
        $t = microtime(true);
        $g = new Gdb($p, $write);
        self::$time += microtime(true) - $t;
        if (!$write) self::$g = $g;
        return $g;
    }

    public static function tables(): array { return self::open()->tables(); }

    public static function columns(string $t): array
    {
        static $c = [];
        return $c[$t] ??= self::open()->columns($t);
    }

    public static function colMap(string $t): array
    {
        $m = [];
        foreach (self::columns($t) as $c) $m[$c['name']] = $c;
        return $m;
    }


    public static function allRows(string $t): array
    {
        static $cache = [];
        if (isset($cache[$t])) return $cache[$t];
        $t0 = microtime(true);
        $rows = [];
        foreach (self::open()->rows($t) as $r) $rows[] = $r;
        self::$time += microtime(true) - $t0;
        return $cache[$t] = $rows;
    }

    public static function rowCount(string $t): int
    {
        static $c = [];
        if (isset($c[$t])) return $c[$t];
        $t0 = microtime(true);
        $n = self::open()->countRows($t);
        self::$time += microtime(true) - $t0;
        return $c[$t] = $n;
    }


    public static function fetch(string $t, array $filters, string $order, string $dir, int $page, int $per): array
    {

        if (!$filters && $order === '') {
            $skip = max(0, ($page - 1) * $per);
            $rows = []; $i = 0;
            $t0 = microtime(true);
            foreach (self::open()->rows($t) as $r) {
                if ($i++ < $skip) continue;
                $rows[] = $r;
                if (count($rows) >= $per) break;
            }
            self::$time += microtime(true) - $t0;
            return [$rows, self::rowCount($t)];
        }

        $rows = self::allRows($t);
        $meta = self::colMap($t);

        foreach ($filters as $col => $val) {
            if (!is_scalar($val) || $val === '' || !isset($meta[$col])) continue;
            $c = $meta[$col];
            $isNum = in_array($c['type'], [7, 8, 9, 10, 11, 16, 27], true);
            if ($isNum && preg_match('/^\s*(=|<>|<=|>=|<|>)?\s*(-?[0-9]+(?:[.,][0-9]+)?)\s*$/u', (string)$val, $m)) {
                $op = $m[1] ?: '='; $num = (float)str_replace(',', '.', $m[2]);
                $rows = array_values(array_filter($rows, function ($r) use ($col, $op, $num) {
                    $v = $r[$col];
                    if ($v === null) return false;
                    $v = (float)$v;
                    switch ($op) {
                        case '=':  return abs($v - $num) < 1e-9;
                        case '<>': return abs($v - $num) >= 1e-9;
                        case '<':  return $v < $num;   case '>':  return $v > $num;
                        case '<=': return $v <= $num;  case '>=': return $v >= $num;
                    }
                    return true;
                }));
            } else {
                $needle = mb_strtolower((string)$val, 'UTF-8');
                $rows = array_values(array_filter($rows, function ($r) use ($col, $needle) {
                    $v = $r[$col];
                    return $v !== null && mb_stripos((string)$v, $needle, 0, 'UTF-8') !== false;
                }));
            }
        }

        if ($order !== '' && isset($meta[$order])) {
            $k = $order; $sign = $dir === 'desc' ? -1 : 1;
            usort($rows, function ($a, $b) use ($k, $sign) {
                $x = $a[$k]; $y = $b[$k];
                if ($x === null && $y === null) return 0;
                if ($x === null) return -$sign;
                if ($y === null) return $sign;
                if (is_numeric($x) && is_numeric($y)) return $sign * ((float)$x <=> (float)$y);
                return $sign * strnatcasecmp((string)$x, (string)$y);
            });
        }

        $total = count($rows);
        return [array_slice($rows, max(0, ($page - 1) * $per), $per), $total];
    }

    public static function row(string $t, string $rowid): ?array { return self::open()->row($t, $rowid); }


    public static function bulkUpdate(string $t, array $ops): int
    {
        if (!$ops) return 0;
        $g = self::$w ?? self::open(true);
        $n = $g->updateBulk($t, $ops);
        if (self::$w === null) self::$g = null;   
        return $n;
    }


    public static function beginWrite(): void { self::$w = self::open(true); }
    public static function endWrite(): void   { self::$w = null; self::$g = null; }


    public static function countryRefs(): array
    {
        static $map = null;
        if ($map !== null) return $map;
        $map = [];
        foreach (self::tables() as $t => $_) {
            if ($t === 'COUNTRY' || $t === 'RELATIONS') continue;   
            foreach (self::columns($t) as $c) {
                if ($c['indexed'] || $c['type'] !== 8) continue;
                if (preg_match('/^(COUNTRY_ID|OWNER_ID|ASSIGNED_COUNTRY|COUNTRY_FRAME|COUNTRY_DESIGNER|MILITARY_OWNER_ID)$/', $c['name'])) {
                    $map[$t][] = $c['name'];
                }
            }
        }
        return $map;
    }


    public static function update(string $t, string $rowid, array $values): bool
    {
        $g = self::$w ?? self::open(true);    
        $changed = $g->update($t, $rowid, $values);   
        if (self::$w === null) self::$g = null;       
        return $changed;
    }


    public static function bulkPlan(array $plan, ?string &$err = null): int
    {
        $err = null; $n = 0;
        self::beginWrite();
        try {
            foreach ($plan as $tbl => $ops) {
                if (!$ops) continue;
                try { $n += self::bulkUpdate((string)$tbl, $ops); }
                catch (GdbBulkError $e) { $n += $e->done; $err = $e->getMessage(); break; }
            }
        } finally { self::endWrite(); }
        return $n;
    }
}
