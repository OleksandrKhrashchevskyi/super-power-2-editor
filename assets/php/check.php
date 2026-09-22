<?php

declare(strict_types=1);

function db_checks(): array
{
    $out = [];
    $tables = Db::tables();


    $idSets = [];
    $idsOf = function (string $t) use (&$idSets): array {
        if (isset($idSets[$t])) return $idSets[$t];
        $set = [];
        $cols = Db::colMap($t);
        $key = isset($cols[$t . '_ID']) ? $t . '_ID' : (isset($cols['ID']) ? 'ID' : null);
        if ($key === null) return $idSets[$t] = [];
        foreach (Db::allRows($t) as $r) $set[(int)$r[$key]] = true;
        return $idSets[$t] = $set;
    };

    foreach ($tables as $name => $_) {
        $links = guessed_links($name);
        if (!$links) continue;
        foreach ($links as $col => $target) {
            $ids = $idsOf($target);
            if (!$ids) continue;
            $bad = [];
            foreach (Db::allRows($name) as $r) {
                $v = (int)($r[$col] ?? 0);
                if ($v === 0 || isset($ids[$v])) continue;
                $bad[] = [$r['__rowid'], $v, $r['ID'] ?? null];
                if (count($bad) >= 200) break;
            }
            if ($bad) $out[] = ['level' => 'danger', 'table' => $name, 'col' => $col,
                                'title' => t('%s.%s points to a row that does not exist in %s', $name, $col, $target),
                                'rows' => $bad];
        }
    }


    if (gst_path()) {
        $idx = [];
        foreach (gst_index_safe() as $e) $idx[(int)$e[0]] = true;
        foreach ($tables as $name => $_) {
            foreach (Db::columns($name) as $c) {
                if (!is_stid_col($c)) continue;
                $bad = [];
                foreach (Db::allRows($name) as $r) {
                    $v = (int)($r[$c['name']] ?? 0);
                    if ($v === 0 || isset($idx[$v])) continue;
                    $bad[] = [$r['__rowid'], $v, $r['ID'] ?? null];
                    if (count($bad) >= 200) break;
                }
                if ($bad) $out[] = ['level' => 'warning', 'table' => $name, 'col' => $c['name'],
                                    'title' => t('%s.%s has no line in the dictionary', $name, $c['name']),
                                    'rows' => $bad];
            }
        }
    }


    $regCnt = [];
    foreach (Db::allRows('REGION') as $r) $regCnt[(int)$r['COUNTRY_ID']] = ($regCnt[(int)$r['COUNTRY_ID']] ?? 0) + 1;
    $bad = [];
    foreach (Db::allRows('COUNTRY') as $r) {
        if (($r['ACTIVATED'] ?? '') !== 'T') continue;
        if (!empty($regCnt[(int)$r['ID']])) continue;
        $bad[] = [$r['__rowid'], (int)$r['ID'], (int)$r['ID']];
    }
    if ($bad) $out[] = ['level' => 'warning', 'table' => 'COUNTRY', 'col' => 'ACTIVATED',
                        'title' => t('Country is active but owns no regions'), 'rows' => $bad];


    $cityRegion = [];
    foreach (Db::allRows('CITIES') as $c) $cityRegion[(int)$c['ID']] = (int)$c['REGION_ID'];
    $regCountry = [];
    foreach (Db::allRows('REGION') as $r) $regCountry[(int)$r['REGION_ID']] = (int)$r['COUNTRY_ID'];
    $bad = [];
    foreach (Db::allRows('COUNTRY') as $r) {
        $cap = (int)$r['CAPITAL_ID'];
        if ($cap === 0) continue;
        if (!isset($cityRegion[$cap])) { $bad[] = [$r['__rowid'], $cap, (int)$r['ID']]; continue; }
        $owner = $regCountry[$cityRegion[$cap]] ?? 0;
        if ($owner !== (int)$r['ID']) $bad[] = [$r['__rowid'], $cap, (int)$r['ID']];
    }
    if ($bad) $out[] = ['level' => 'danger', 'table' => 'COUNTRY', 'col' => 'CAPITAL_ID',
                        'title' => t('Capital city belongs to another country or does not exist'), 'rows' => $bad];


    foreach ($tables as $name => $_) {
        $cols = Db::colMap($name);
        if (!isset($cols['ID']) || !$cols['ID']['indexed']) continue;
        $seen = []; $bad = [];
        foreach (Db::allRows($name) as $r) {
            $v = (int)$r['ID'];
            if (isset($seen[$v])) { $bad[] = [$r['__rowid'], $v, $v]; if (count($bad) >= 200) break; }
            $seen[$v] = true;
        }
        if ($bad) $out[] = ['level' => 'danger', 'table' => $name, 'col' => 'ID',
                            'title' => t('Duplicate ID in a table with a unique index'), 'rows' => $bad];
    }


    $hasCity = [];
    foreach (Db::allRows('CITIES') as $c) $hasCity[(int)$c['REGION_ID']] = true;
    $bad = [];
    foreach (Db::allRows('REGION') as $r) {
        if (isset($hasCity[(int)$r['REGION_ID']])) continue;
        $bad[] = [$r['__rowid'], (int)$r['REGION_ID'], (int)$r['REGION_ID']];
        if (count($bad) >= 200) break;
    }
    if ($bad) $out[] = ['level' => 'info', 'table' => 'REGION', 'col' => 'REGION_ID',
                        'title' => t('Region has no cities — the map draws it approximately'), 'rows' => $bad];

    return $out;
}


function db_search(string $q, int $limitPerTable = 30): array
{
    $out = ['tables' => [], 'gst' => [], 'total' => 0];
    if ($q === '') return $out;
    $isNum = is_numeric(str_replace(',', '.', $q));
    $qNum  = $isNum ? (float)str_replace(',', '.', $q) : 0.0;

    foreach (Db::tables() as $name => $_) {
        $cols = Db::columns($name);
        $hits = [];
        foreach (Db::allRows($name) as $r) {
            $where = [];
            foreach ($cols as $c) {
                if (is_blob_col($c)) continue;
                $v = $r[$c['name']] ?? null;
                if ($v === null) continue;
                if (is_numeric_col($c)) {
                    if ($isNum && (float)$v == $qNum) $where[] = $c['name'];
                } elseif (mb_stripos((string)$v, $q, 0, 'UTF-8') !== false) {
                    $where[] = $c['name'];
                }
            }
            if (!$where) continue;
            $hits[] = ['rowid' => $r['__rowid'], 'id' => $r['ID'] ?? null, 'cols' => $where];
            if (count($hits) >= $limitPerTable) break;
        }
        if ($hits) { $out['tables'][$name] = $hits; $out['total'] += count($hits); }
    }

    if (gst_path() && !$isNum) {
        foreach (gst_index_safe() as $e) {
            $txt = gst_text($e[1], $e[2]);
            if ($txt === '' || mb_stripos($txt, $q, 0, 'UTF-8') === false) continue;
            $out['gst'][] = [(int)$e[0], mb_strimwidth($txt, 0, 120, '…', 'UTF-8')];
            if (count($out['gst']) >= 60) break;
        }
        $out['total'] += count($out['gst']);
    }
    return $out;
}
