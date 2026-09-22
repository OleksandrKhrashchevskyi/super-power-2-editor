<?php

declare(strict_types=1);

$action = get_s('a', 'home');

try {
    switch ($action) {

    case 'setgame': {
        if (!LOCAL_MODE) { flash('danger', t('Working with folders on the server is switched off.')); redirect(''); }
        $dir = rtrim(trim(post_s('dir')), '\\/');
        if ($dir !== '' && !is_dir($dir)) { flash('danger', t('No such folder.')); redirect(''); }
        $c = cfg_load(); $c['game_dir'] = $dir; cfg_save($c);
        flash('success', $dir === '' ? t('Game folder cleared.') : t('Game folder saved: %s', $dir));
        redirect('');
    }

    case 'upload':
    case 'upload_gst': {
        global $WORK;
        $field = $action === 'upload' ? 'gdb' : 'gst';
        $dst = save_upload($field, 'gdb|fdb|gst|zip', 'upload');
        $gotDb = false; $gotGst = 0;

        if (preg_match('/\.zip$/i', $dst)) {

            $inside = Zip::listFiles($dst);
            if (!$inside) { @unlink($dst); throw new RuntimeException(t('The archive is empty or unreadable.')); }
            foreach ($inside as $name => $sz) {
                if (!preg_match('/\.(gdb|fdb|gst)$/i', $name)) continue;

                if ((int)$sz > PROJECT_MAX_BYTES - project_size()) {
                    throw new RuntimeException(t('The project is full (limit %s). Close files you no longer need or start a new project.', bytes_h(PROJECT_MAX_BYTES)));
                }
                $data = Zip::extract($dst, $name);
                if ($data === null || $data === '') continue;
                $out = $WORK . DIRECTORY_SEPARATOR . preg_replace('/[^A-Za-z0-9_.\- ]/', '_', basename($name));
                file_put_contents($out, $data);
                if (preg_match('/\.gst$/i', $name)) { gst_add($out); $gotGst++; }
                else {
                    try { new Gdb($out); $_SESSION['db_path'] = $out; $_SESSION['db_origin'] = null; $gotDb = true; }
                    catch (Throwable $e) { @unlink($out); }
                }
            }
            @unlink($dst);
            if (!$gotDb && !$gotGst) throw new RuntimeException(t('No .gdb or .gst files found in the archive.'));

            foreach (gst_files() as $i => $p2) {
                if (stripos(basename($p2), 'english') !== false) { gst_use($i); break; }
                if ($i === 0) gst_use(0);
            }
            flash('success', t('From the archive: database — %s, language files — %d.', $gotDb ? t('yes') : t('no'), $gotGst));
            $url = $gotDb ? 'index.php' : 'index.php?a=lang';
            if (is_ajax()) jout(['ok' => true, 'url' => $url]);
            redirect($gotDb ? '' : '?a=lang');
        }

        if (preg_match('/\.gst$/i', $dst)) {
            gst_add($dst);
            flash('success', t('Language file loaded: %s.', basename($dst)));
            if (is_ajax()) jout(['ok' => true, 'url' => 'index.php?a=lang']);
            redirect('?a=lang');
        }

        try { new Gdb($dst); }
        catch (Throwable $e) { @unlink($dst); throw new RuntimeException(t('The file does not look like a Firebird database: %s', $e->getMessage())); }
        $_SESSION['db_path'] = $dst; $_SESSION['db_origin'] = null;
        flash('success', t('Database loaded: %s (%s).', basename($dst), bytes_h((int)filesize($dst))));
        if (is_ajax()) jout(['ok' => true, 'url' => 'index.php']);
        redirect('');
    }

    case 'gst_use': { gst_use((int)get('i', 0)); redirect('?a=lang'); }
    case 'gst_drop': { gst_drop((int)get('i', 0)); redirect('?a=lang'); }

    case 'download_all': {
        $files = [];
        if (Db::path()) $files[basename((string)Db::path())] = Db::path();
        foreach (gst_files() as $p) $files[basename($p)] = $p;
        if (!$files) { flash('warning', t('Nothing to download.')); redirect(''); }
        $zip = (string)tempnam(sys_get_temp_dir(), 'sp2');
        if (!Zip::create($zip, $files)) { @unlink($zip); flash('danger', t('Could not build the archive.')); redirect(''); }
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="superpower2-files.zip"');
        header('Content-Length: ' . filesize($zip));
        readfile($zip);
        @unlink($zip);
        exit;
    }

    case 'openlocal': {
        global $WORK;
        if (!LOCAL_MODE) { flash('danger', t('Working with folders on the server is switched off.')); redirect(''); }
        $src = get_s('path');
        if (!is_file($src)) { flash('danger', t('File not found.')); redirect(''); }
        $isGst = (bool)preg_match('/\.gst$/i', $src);
        $dst = $WORK . DIRECTORY_SEPARATOR . basename(dirname(dirname($src))) . '-' . basename($src);
        if (!@copy($src, $dst)) { flash('danger', t('Could not copy the file into work/.')); redirect(''); }
        make_backup($dst, 'open');
        if ($isGst) {
            gst_add($dst); $_SESSION['gst_origin'] = $src;
            flash('success', t('Opened a copy of the language file.'));
            redirect('?a=lang');
        }
        $_SESSION['db_path'] = $dst; $_SESSION['db_origin'] = $src;
        flash('success', t('Opened a copy of the database. The original stays untouched until you press "Write to game".'));
        redirect('');
    }

    case 'newproject': {

        setcookie('sp2_project', '', ['expires' => time() - 3600, 'path' => '/']);
        unset($_COOKIE['sp2_project'], $_SESSION['project'],
              $_SESSION['db_path'], $_SESSION['db_origin'],
              $_SESSION['gst_files'], $_SESSION['gst_active'], $_SESSION['gst_origin'],
              $_SESSION['chlog'], $_SESSION['merge_preview'], $_SESSION['merge_sel'], $_SESSION['rep_preview']);
        flash('success', t('New project started.'));
        redirect('');
    }


    case 'close': {
        $p = (string)(Db::path() ?? '');
        if ($p && is_file($p)) { make_backup($p, 'close'); @unlink($p); }
        unset($_SESSION['db_path'], $_SESSION['db_origin']);
        redirect('');
    }
    case 'close_gst': {


        $i = gst_active();
        $p = (string)(gst_path() ?? '');
        gst_drop($i);
        if ($p && is_file($p)) { make_backup($p, 'close'); @unlink($p); }
        unset($_SESSION['gst_origin']);
        redirect('?a=lang');
    }

    case 'download':
    case 'download_gst': {
        $p = $action === 'download' ? Db::path() : gst_path();
        if (!$p || !is_file($p)) { flash('danger', t('File is not open.')); redirect(''); }
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($p) . '"');
        header('Content-Length: ' . filesize($p));
        header('X-Content-Type-Options: nosniff');
        readfile($p);
        exit;
    }

    case 'writeback':
    case 'writeback_gst': {
        $gst = $action === 'writeback_gst';
        $p = $gst ? gst_path() : Db::path();
        $o = $_SESSION[$gst ? 'gst_origin' : 'db_origin'] ?? null;
        if (!$p || !$o) { flash('danger', t('The file was not opened from the game folder.')); redirect($gst ? '?a=lang' : ''); }
        make_backup($o, 'before-writeback');
        if (@copy($p, $o)) flash('success', t('Written to the game: %s (previous version is in backups/).', $o));
        else flash('danger', t('Could not write the file. Close the game and try again.'));
        redirect($gst ? '?a=lang' : ('?a=table&t=' . urlencode(get_s('t'))));
    }

    case 'row': {
        $table = get_s('t');
        $rowid = get_s('rowid');
        $r = Db::row($table, $rowid);
        if ($r === null) jout(['ok' => false, 'error' => t('Row not found — the file may have changed.')]);
        $v = []; $l = [];
        foreach (Db::columns($table) as $c) {
            $val = $r[$c['name']] ?? null;
            $v[$c['name']] = is_float($val) ? fmt_num($val, $c['type']) : $val;
            if (is_stid_col($c) && gst_path()) $l[$c['name']] = gst_lookup($val);
        }
        jout(['ok' => true, 'id' => $rowid, 'v' => $v, 'l' => $l]);
    }

    case 'save': {
        $table = post_s('table');
        $rowid = post_s('rowid');
        $vals  = (array)post('v', []);
        $nulls = (array)post('n', []);
        $meta  = Db::colMap($table);
        $set = [];
        foreach ($meta as $name => $c) {
            if (is_blob_col($c) || $c['indexed']) continue;
            if (!array_key_exists($name, $vals) && !isset($nulls[$name])) continue;
            if (isset($nulls[$name])) { $set[$name] = null; continue; }
            $raw = (string)$vals[$name];
            if (is_numeric_col($c)) {
                $set[$name] = $raw === '' ? null : str_replace(',', '.', $raw);
            } else {
                $set[$name] = $raw;
            }
        }
        if (!$set) { flash('warning', t('Nothing to save.')); redirect('?a=table&t=' . urlencode($table)); }
        $before = log_before($table, [$rowid => $set]);

        $same = true;
        foreach ($set as $col => $v) {
            $old = $before[$rowid][$col] ?? null;
            if ($old === $v || (is_numeric($old) && is_numeric($v) && (float)$old === (float)$v)) continue;
            $same = false; break;
        }
        if ($same) { flash('info', t('No changes.')); redirect('?a=table&t=' . urlencode($table) . '&' . post_s('qs')); }
        if (!make_backup((string)Db::path(), 'edit')) flash('warning', t('Could not save a backup copy — check free space.'));
        if (Db::update($table, $rowid, $set)) {
            log_add('row', t('Row %s in %s', $rowid, $table), $table, $before, 1);
            flash('success', t('Saved.'));
        } else {

            flash('info', t('No changes.'));
        }
        redirect('?a=table&t=' . urlencode($table) . '&' . post_s('qs'));
    }

    case 'domerge': {
        $target  = (int)post('target', 0);
        $sources = array_values(array_unique(array_filter(array_map('intval', (array)post('src', [])))));
        $sources = array_values(array_diff($sources, [$target]));
        $mode    = post_s('mode', 'preview');
        $withMil = (bool)post('military', false);
        if (!$target || !$sources) {
            flash('warning', t('Pick a target country and at least one country to merge.'));
            redirect('?a=merge');
        }
        $refs = Db::countryRefs();
        $plan = [];               
        $report = [];
        foreach ($refs as $tbl => $cols) {
            foreach ($cols as $col) {
                if ($col === 'MILITARY_OWNER_ID' && !$withMil) continue;
                $hits = 0;
                foreach (Db::allRows($tbl) as $r) {
                    if (in_array((int)$r[$col], $sources, true)) {
                        $plan[$tbl][$r['__rowid']][$col] = $target;
                        $hits++;
                    }
                }
                if ($hits) $report[] = [$tbl, $col, $hits];
            }
        }
        $deact = [];
        if (post('deactivate')) {
            foreach (Db::allRows('COUNTRY') as $r) {
                if (in_array((int)$r['ID'], $sources, true) && ($r['ACTIVATED'] ?? '') !== 'F') {
                    $deact[$r['__rowid']] = ['ACTIVATED' => 'F'];
                }
            }
            if ($deact) $report[] = ['COUNTRY', 'ACTIVATED', count($deact)];
        }
        if ($mode !== 'apply') {
            $_SESSION['merge_preview'] = $report;
            $_SESSION['merge_sel'] = ['target' => $target, 'src' => $sources];
            redirect('?a=merge&target=' . $target . '&' . http_build_query(['src' => $sources]));
        }
        if (!make_backup((string)Db::path(), 'merge')) flash('warning', t('Could not save a backup copy — check free space.'));
        $undo = [];
        foreach ($plan as $tbl => $ops) { $undo[$tbl] = log_before($tbl, $ops); }
        if ($deact) $undo['COUNTRY'] = array_replace($undo['COUNTRY'] ?? [], log_before('COUNTRY', $deact));
        $full = $plan;
        if ($deact) $full['COUNTRY'] = array_replace($full['COUNTRY'] ?? [], $deact);
        $rows = Db::bulkPlan($full, $err);
        $tablesTouched = count(array_filter($full));
        if ($rows) log_add_multi('merge', t('Merge into %s', $target), $undo, $rows);
        if ($err !== null) { flash('danger', $err); redirect('?a=merge'); }
        flash('success', t('Merged: %d rows in %d tables.', $rows, $tablesTouched));
        redirect('?a=merge');
    }


    case 'mapdata': {

        $regIdx = [];                       
        $rCountry = []; $rMil = []; $rName = []; $rRowid = []; $rId = [];
        $rCont = []; $rGeo = []; $rPop = []; $rInfra = []; $rTele = [];
        $i = 0;
        foreach (Db::allRows('REGION') as $r) {
            $rid = (int)$r['REGION_ID'];
            $regIdx[$rid] = $i;
            $rId[]      = $rid;
            $rCountry[] = (int)$r['COUNTRY_ID'];
            $rMil[]     = (int)$r['MILITARY_OWNER_ID'];
            $rName[]    = (int)$r['REGION_NAME'];
            $rRowid[]   = (string)$r['__rowid'];
            $rCont[]    = (int)$r['CONTINENT'];
            $rGeo[]     = (int)$r['GEO_GROUP'];
            $rPop[]     = (int)$r['POPULATION_15'] + (int)$r['POPULATION_15_65'] + (int)$r['POPULATION_65'];
            $rInfra[]   = round((float)$r['INFRASTRUCTURE'], 4);
            $rTele[]    = round((float)$r['TELECOM_LEVEL'], 4);
            $i++;
        }

        $cLon = []; $cLat = []; $cReg = []; $cPop = []; $cName = []; $cId = [];
        foreach (Db::allRows('CITIES') as $c) {
            $rid = (int)$c['REGION_ID'];
            if (!isset($regIdx[$rid])) continue;          
            $cLon[]  = round((float)$c['LONGITUDE'], 3);
            $cLat[]  = round((float)$c['LATITUDE'], 3);
            $cReg[]  = $regIdx[$rid];
            $cPop[]  = (int)$c['POP'];
            $cName[] = (int)$c['NAME_STID'];
            $cId[]   = (int)$c['ID'];
        }

        $coId = []; $coCode = []; $coName = []; $coAct = []; $coCap = []; $coGvt = [];
        foreach (Db::allRows('COUNTRY') as $c) {
            $coId[]   = (int)$c['ID'];
            $coCode[] = rtrim((string)$c['CODE']);
            $coName[] = (int)$c['NAME_STID'];
            $coAct[]  = ($c['ACTIVATED'] ?? '') === 'T' ? 1 : 0;
            $coCap[]  = (int)$c['CAPITAL_ID'];
            $coGvt[]  = (int)$c['GVT_TYPE'];
        }


        $mix = function (string $table, string $idCol) use ($regIdx): array {
            $reg = []; $id = []; $pop = [];
            foreach (Db::allRows($table) as $r) {
                $rid = (int)$r['REGION_ID'];
                if (!isset($regIdx[$rid])) continue;
                $p = (int)$r['POPULATION'];
                if ($p <= 0) continue;
                $reg[] = $regIdx[$rid]; $id[] = (int)$r[$idCol]; $pop[] = $p;
            }
            return ['reg' => $reg, 'id' => $id, 'pop' => $pop];
        };


        $dict = function (string $table, string $nameCol): array {
            $ids = []; $stid = [];
            foreach (Db::allRows($table) as $r) { $ids[] = (int)$r['ID']; $stid[] = (int)$r[$nameCol]; }
            $names = [];
            if (gst_path()) {
                foreach ($stid as $k => $sid) { $t = gst_lookup($sid); if ($t !== null && $t !== '') $names[$k] = $t; }
            }
            return ['id' => $ids, 'name' => $names];
        };

        $txt = function (array $stids): array {
            if (!gst_path()) return [];
            $out = [];
            foreach ($stids as $k => $id) { $s = gst_lookup($id); if ($s !== null && $s !== '') $out[$k] = $s; }
            return $out;
        };


        $grpUnits = [];                       
        $unitAmount = [];
        foreach (Db::allRows('UNITS') as $u) $unitAmount[(int)$u['UNIT_ID']] = (int)$u['AMOUNT'];
        foreach (Db::allRows('GROUP_UNIT') as $g) {
            $gid = (int)$g['GROUP_ID'];
            $grpUnits[$gid] = ($grpUnits[$gid] ?? 0) + ($unitAmount[(int)$g['UNIT_ID']] ?? 0);
        }
        $gLon = []; $gLat = []; $gCou = []; $gAmt = []; $gId = [];
        foreach (Db::allRows('UNIT_GROUPS') as $g) {
            $x = (float)$g['LONGITUDE']; $y = (float)$g['LATITUDE'];
            if ($x < -180 || $x > 180 || $y < -90 || $y > 90) continue;
            $gLon[] = round($x, 3); $gLat[] = round($y, 3);
            $gCou[] = (int)$g['COUNTRY_ID'];
            $gAmt[] = $grpUnits[(int)$g['GROUP_ID']] ?? 0;
            $gId[]  = (int)$g['GROUP_ID'];
        }
        $mLon = []; $mLat = []; $mCou = []; $mQty = [];
        foreach (Db::allRows('MISSILE') as $m) {
            $x = (float)$m['LONGITUDE']; $y = (float)$m['LATITUDE'];
            if ($x < -180 || $x > 180 || $y < -90 || $y > 90 || ($x == -1.0 && $y == -1.0)) continue;
            $mLon[] = round($x, 3); $mLat[] = round($y, 3);
            $mCou[] = (int)$m['COUNTRY_ID']; $mQty[] = (int)$m['QUANTITY'];
        }


        $designType = []; $typeSample = [];
        foreach (Db::allRows('DESIGN') as $d) {
            $ty = (int)$d['TYPE_ID'];
            $designType[(int)$d['DESIGN_ID']] = $ty;
            if (count($typeSample[$ty] ?? []) < 2 && gst_path()) {
                $nm = gst_lookup($d['NAME']);
                if ($nm) $typeSample[$ty][] = $nm;
            } elseif (!isset($typeSample[$ty])) $typeSample[$ty] = [];
        }
        $force = []; $forceType = [];
        foreach (Db::allRows('UNITS') as $u) {
            $c = (int)$u['COUNTRY_ID']; $a = (int)$u['AMOUNT'];
            $force[$c] = ($force[$c] ?? 0) + $a;
            $ty = $designType[(int)$u['DESIGN_ID']] ?? 0;
            if ($ty) $forceType[$ty][$c] = ($forceType[$ty][$c] ?? 0) + $a;
        }
        ksort($forceType);


        $trId = []; $trName = []; $trType = []; $trAct = [];
        foreach (Db::allRows('TREATY') as $t2) {
            $trId[]   = (int)$t2['TREATY_ID'];
            $trName[] = gst_path() ? (gst_lookup($t2['NAME']) ?: ('#' . (int)$t2['TREATY_ID'])) : ('#' . (int)$t2['TREATY_ID']);
            $trType[] = (int)$t2['TYPE_TREATY'];
            $trAct[]  = ($t2['ACTIVATED'] ?? '') === 'T' ? 1 : 0;
        }
        $tmT = []; $tmC = []; $tmS = [];
        foreach (Db::allRows('TREATY_MEMBER') as $m) {
            if (($m['ACTIVATED'] ?? '') !== 'T') continue;
            $tmT[] = (int)$m['TREATY_ID']; $tmC[] = (int)$m['COUNTRY_ID']; $tmS[] = (int)$m['SIDE'];
        }


        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode([
            'ok' => true,
            'group'   => ['lon' => $gLon, 'lat' => $gLat, 'cou' => $gCou, 'amt' => $gAmt, 'id' => $gId],
            'missile' => ['lon' => $mLon, 'lat' => $mLat, 'cou' => $mCou, 'qty' => $mQty],
            'force'   => $force,
            'forceType' => $forceType,
            'dtype'   => array_map(fn($t3) => implode(', ', array_slice($typeSample[$t3] ?? [], 0, 2)), array_keys($forceType)),
            'dtypeId' => array_keys($forceType),
            'treaty'  => ['id' => $trId, 'name' => $trName, 'type' => $trType, 'act' => $trAct],
            'tmember' => ['t' => $tmT, 'c' => $tmC, 's' => $tmS],
            'region'  => ['id' => $rId, 'country' => $rCountry, 'mil' => $rMil,
                          'rowid' => $rRowid, 'name' => $txt($rName),
                          'cont' => $rCont, 'geo' => $rGeo, 'pop' => $rPop,
                          'infra' => $rInfra, 'tele' => $rTele],
            'city'    => ['id' => $cId, 'lon' => $cLon, 'lat' => $cLat, 'reg' => $cReg,
                          'pop' => $cPop, 'name' => $txt($cName)],
            'country' => ['id' => $coId, 'code' => $coCode, 'act' => $coAct,
                          'cap' => $coCap, 'gvt' => $coGvt, 'name' => $txt($coName)],
            'lang'    => $dict('LANGUAGE', 'LANGUAGE_NAME'),
            'rel'     => $dict('RELIGION', 'RELIGION_NAME'),
            'gvt'     => $dict('GVT_TYPE', 'NAME_STID'),
            'langMix' => $mix('LANGUAGES', 'LANGUAGE_ID'),
            'relMix'  => $mix('RELIGIONS', 'RELIGION_ID'),
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }


    case 'relations': {
        $id = (int)get('c', 0);
        $out = [];
        foreach (Db::allRows('RELATIONS') as $r) {
            if ((int)$r['ID'] !== $id) continue;
            foreach ($r as $k => $v) {
                if (strpos($k, 'COUNTRY_') !== 0 || $v === null) continue;
                $out[(int)substr($k, 8)] = round((float)$v, 2);
            }
            break;
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => true, 'id' => $id, 'v' => $out]);
        exit;
    }


    case 'mapassign': {
        $target = (int)post('target', 0);
        $raw = post('reg', []);
        if (is_string($raw)) $raw = explode(',', $raw);        
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$raw))));
        $what   = post_s('what', 'country');      
        if (!$target || !$ids) {
            flash('warning', t('Pick a target country and at least one region.'));
            redirect('?a=map');
        }
        $known = [];
        foreach (Db::allRows('COUNTRY') as $c) $known[(int)$c['ID']] = true;
        if (!isset($known[$target])) throw new RuntimeException(t('Unknown country: %d', $target));

        $want = array_flip($ids);
        $ops = [];
        foreach (Db::allRows('REGION') as $r) {
            if (!isset($want[(int)$r['REGION_ID']])) continue;
            $set = [];
            if ($what !== 'mil'     && (int)$r['COUNTRY_ID']        !== $target) $set['COUNTRY_ID'] = $target;
            if ($what !== 'country' && (int)$r['MILITARY_OWNER_ID'] !== $target) $set['MILITARY_OWNER_ID'] = $target;
            if ($set) $ops[$r['__rowid']] = $set;
        }
        if (!$ops) { flash('info', t('No changes.')); redirect('?a=map'); }

        if (!make_backup((string)Db::path(), 'map')) flash('warning', t('Could not save a backup copy — check free space.'));
        $before = log_before('REGION', $ops);
        $n = Db::bulkPlan(['REGION' => $ops], $err);
        if ($n) log_add('map', t('Regions → %s', $target), 'REGION', $before, $n);
        if ($err !== null) { flash('danger', $err); redirect('?a=map'); }
        flash('success', t('Regions transferred: %d.', $n));
        redirect('?a=map');
    }


    case 'undo': {
        $op = log_find(get_s('id'));
        if (!$op) { flash('warning', t('This change is no longer in the log.')); redirect('?a=log'); }
        $now = $op['kind'] === 'gst' ? (string)gst_path() : (string)(Db::path() ?? '');
        if (($op['file'] ?? '') !== '' && $op['file'] !== $now) {
            flash('warning', t('That change was made in another file (%s). Open it to roll back.',
                               basename((string)$op['file'])));
            redirect('?a=log');
        }
        if ($op['kind'] === 'gst') {
            if (!$op['undo']) { flash('warning', t('This change is too large to undo.')); redirect('?a=log'); }
            $back = [];
            foreach ($op['undo'] as $id => $v) $back[(int)$id] = (string)$v['text'];
            $n = gst_write($back);
            log_drop($op['id']);
            flash('success', t('Rolled back: %d rows.', $n));
            redirect('?a=log');
        }
        $plan = $op['multi'] ?? ($op['undo'] ? [$op['table'] => $op['undo']] : null);
        if (!$plan) { flash('warning', t('This change is too large to undo.')); redirect('?a=log'); }
        if (!make_backup((string)Db::path(), 'undo')) flash('warning', t('Could not save a backup copy — check free space.'));
        $n = Db::bulkPlan($plan, $err);
        if ($err !== null) { flash('danger', $err); redirect('?a=log'); }   
        log_drop($op['id']);
        flash('success', t('Rolled back: %d rows.', $n));
        redirect('?a=log');
    }

    case 'logclear': { log_clear(); flash('info', t('Log cleared.')); redirect('?a=log'); }


    case 'doreplace': {
        $table = post_s('table');
        $col   = post_s('col');
        $op    = post_s('op', 'eq');
        $needle= post_s('val');
        $newRaw= post_s('newval');
        $toNull= (bool)post('tonull', false);
        $mode  = post_s('mode', 'preview');

        $meta = Db::colMap($table);
        if (!isset($meta[$col])) throw new RuntimeException(t('Unknown column: %s', $col));
        $c = $meta[$col];
        if ($c['indexed']) throw new RuntimeException(t('the field belongs to a database index — editing it would break the index'));
        if (is_blob_col($c)) throw new RuntimeException(t('BLOB is not editable'));

        $num = is_numeric_col($c);
        $cmp = $num ? (float)str_replace(',', '.', $needle) : $needle;
        $match = function ($v) use ($op, $cmp, $num, $needle) {
            switch ($op) {
                case 'any':   return true;
                case 'null':  return $v === null;
                case 'notnull': return $v !== null;
                case 'eq':    return $num ? ($v !== null && (float)$v == $cmp) : ((string)$v === $needle);
                case 'ne':    return $num ? ($v === null || (float)$v != $cmp) : ((string)$v !== $needle);
                case 'gt':    return $v !== null && (float)$v >  $cmp;
                case 'lt':    return $v !== null && (float)$v <  $cmp;
                case 'has':   return $needle !== '' && $v !== null && mb_stripos((string)$v, $needle, 0, 'UTF-8') !== false;
                case 'empty': return $v === null || (string)$v === '';
            }
            return false;
        };
        $newVal = $toNull ? null : ($num ? ($newRaw === '' ? null : str_replace(',', '.', $newRaw)) : $newRaw);

        $ops = []; $sample = []; $hits = 0;
        foreach (Db::allRows($table) as $r) {
            if (!$match($r[$col] ?? null)) continue;
            $hits++;
            $cur = $r[$col] ?? null;


            $same = ($toNull || $newVal === null) ? ($cur === null)
                  : ($num ? ($cur !== null && (float)$cur == (float)$newVal) : ((string)$cur === (string)$newVal));
            if ($same) continue;
            $ops[$r['__rowid']] = [$col => $newVal];
            if (count($sample) < 15) $sample[] = [$r['__rowid'], $cur, $r['ID'] ?? null];
        }

        if ($mode !== 'apply') {
            $_SESSION['rep_preview'] = ['table' => $table, 'col' => $col, 'hits' => $hits,
                                        'change' => count($ops), 'sample' => $sample,
                                        'new' => $toNull ? null : $newVal];
            redirect('?a=replace&' . http_build_query(['t' => $table, 'c' => $col, 'op' => $op,
                                                       'val' => $needle, 'newval' => $newRaw, 'tonull' => $toNull ? 1 : 0]));
        }
        if (!$ops) { flash('info', t('No changes.')); redirect('?a=replace&t=' . urlencode($table) . '&c=' . urlencode($col)); }
        if (!make_backup((string)Db::path(), 'replace')) flash('warning', t('Could not save a backup copy — check free space.'));
        $before = log_before($table, $ops);
        $n = Db::bulkPlan([$table => $ops], $err);
        if ($n) log_add('replace', t('%s.%s → %s', $table, $col, $toNull ? 'null' : $newRaw), $table, $before, $n);
        if ($err !== null) { flash('danger', $err); redirect('?a=replace&t=' . urlencode($table) . '&c=' . urlencode($col)); }
        flash('success', t('Rows changed: %d.', $n));
        redirect('?a=replace&t=' . urlencode($table) . '&c=' . urlencode($col));
    }

    case 'schemajson': {
        $out = ['db' => Db::path(), 'tables' => []];
        foreach (Db::tables() as $name => $t) {
            $cols = Db::columns($name);
            $info = ['columns' => [], 'rows' => Db::rowCount($name), 'sample' => []];
            foreach ($cols as $c) {
                $info['columns'][] = ['name' => $c['name'], 'type' => type_name($c),
                                      'nullable' => $c['nullable'], 'indexed' => $c['indexed'],
                                      'domain' => $c['domain']];
            }
            $info['sample'] = array_slice(Db::allRows($name), 0, 5);
            $out['tables'][$name] = $info;
        }
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="sp2_schema.json"');
        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    case 'save_gst': {
        $orig = json_decode(post_s('orig'), true) ?: [];
        $norm = fn($s) => str_replace(["\r\n", "\r"], "\n", (string)$s);
        $changes = [];
        foreach ((array)post('s', []) as $id => $txt) {
            if (!is_numeric($id)) continue;
            $id = (int)$id; $new = $norm($txt); $old = (string)($orig[$id] ?? '');
            if ($new === $norm($old)) continue;
            if ($old === '' || strpos($old, "\r\n") !== false) $new = str_replace("\n", "\r\n", $new);
            if ($old !== '' && preg_match('/(\r\n|\n)$/', $old, $m) && !preg_match('/(\r\n|\n)$/', $new)) $new .= $m[1];
            $changes[$id] = $new;
        }
        if (!$changes) { flash('info', t('No changes.')); redirect('?a=lang&' . post_s('qs')); }
        $old = [];
        foreach (array_keys($changes) as $id) $old[$id] = (string)($orig[$id] ?? '');
        $n = gst_write($changes);
        log_add_gst($old, basename((string)gst_path()));
        flash('success', t('Saved lines: %d (a copy is in backups/).', $n));
        redirect('?a=lang&' . post_s('qs'));
    }

    }
} catch (Throwable $e) {
    if (is_ajax()) jout(['ok' => false, 'error' => $e->getMessage()]);
    flash('danger', $e->getMessage());
    redirect('?a=' . ($action === 'save' ? 'table&t=' . urlencode(post_s('table')) : 'home'));
}
