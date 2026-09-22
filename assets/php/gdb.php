<?php


declare(strict_types=1);

class GdbError extends RuntimeException {}


class GdbBulkError extends GdbError
{
    public int $done;
    public function __construct(string $msg, int $done, ?Throwable $prev = null)
    {
        parent::__construct($msg, 0, $prev);
        $this->done = $done;
    }
}

final class Gdb
{

    private const SYS = [
    'RDB$FIELDS' => ['rel'=>2,'len'=>308,'fields'=>[[0,'RDB$FIELD_NAME',14,31,0,4], [1,'RDB$QUERY_NAME',14,31,0,35], [2,'RDB$VALIDATION_BLR',261,8,0,68], [3,'RDB$VALIDATION_SOURCE',261,8,0,76], [4,'RDB$COMPUTED_BLR',261,8,0,84], [5,'RDB$COMPUTED_SOURCE',261,8,0,92], [6,'RDB$DEFAULT_VALUE',261,8,0,100], [7,'RDB$DEFAULT_SOURCE',261,8,0,108], [8,'RDB$FIELD_LENGTH',7,2,0,116], [9,'RDB$FIELD_SCALE',7,2,0,118], [10,'RDB$FIELD_TYPE',7,2,0,120], [11,'RDB$FIELD_SUB_TYPE',7,2,0,122], [12,'RDB$MISSING_VALUE',261,8,0,124], [13,'RDB$MISSING_SOURCE',261,8,0,132], [14,'RDB$DESCRIPTION',261,8,0,140], [15,'RDB$SYSTEM_FLAG',7,2,0,148], [16,'RDB$QUERY_HEADER',261,8,0,152], [17,'RDB$SEGMENT_LENGTH',7,2,0,160], [18,'RDB$EDIT_STRING',37,125,0,162], [19,'RDB$EXTERNAL_LENGTH',7,2,0,290], [20,'RDB$EXTERNAL_SCALE',7,2,0,292], [21,'RDB$EXTERNAL_TYPE',7,2,0,294], [22,'RDB$DIMENSIONS',7,2,0,296], [23,'RDB$NULL_FLAG',7,2,0,298], [24,'RDB$CHARACTER_LENGTH',7,2,0,300], [25,'RDB$COLLATION_ID',7,2,0,302], [26,'RDB$CHARACTER_SET_ID',7,2,0,304], [27,'RDB$FIELD_PRECISION',7,2,0,306]]],
    'RDB$INDEX_SEGMENTS' => ['rel'=>3,'len'=>68,'fields'=>[[0,'RDB$INDEX_NAME',14,31,0,4], [1,'RDB$FIELD_NAME',14,31,0,35], [2,'RDB$FIELD_POSITION',7,2,0,66]]],
    'RDB$INDICES' => ['rel'=>4,'len'=>144,'fields'=>[[0,'RDB$INDEX_NAME',14,31,0,4], [1,'RDB$RELATION_NAME',14,31,0,35], [2,'RDB$INDEX_ID',7,2,0,66], [3,'RDB$UNIQUE_FLAG',7,2,0,68], [4,'RDB$DESCRIPTION',261,8,0,72], [5,'RDB$SEGMENT_COUNT',7,2,0,80], [6,'RDB$INDEX_INACTIVE',7,2,0,82], [7,'RDB$INDEX_TYPE',7,2,0,84], [8,'RDB$FOREIGN_KEY',14,31,0,86], [9,'RDB$SYSTEM_FLAG',7,2,0,118], [10,'RDB$EXPRESSION_BLR',261,8,0,120], [11,'RDB$EXPRESSION_SOURCE',261,8,0,128], [12,'RDB$STATISTICS',27,8,0,136]]],
    'RDB$RELATION_FIELDS' => ['rel'=>5,'len'=>402,'fields'=>[[0,'RDB$FIELD_NAME',14,31,0,4], [1,'RDB$RELATION_NAME',14,31,0,35], [2,'RDB$FIELD_SOURCE',14,31,0,66], [3,'RDB$QUERY_NAME',14,31,0,97], [4,'RDB$BASE_FIELD',14,31,0,128], [5,'RDB$EDIT_STRING',37,125,0,160], [6,'RDB$FIELD_POSITION',7,2,0,288], [7,'RDB$QUERY_HEADER',261,8,0,292], [8,'RDB$UPDATE_FLAG',7,2,0,300], [9,'RDB$FIELD_ID',7,2,0,302], [10,'RDB$VIEW_CONTEXT',7,2,0,304], [11,'RDB$DESCRIPTION',261,8,0,308], [12,'RDB$DEFAULT_VALUE',261,8,0,316], [13,'RDB$SYSTEM_FLAG',7,2,0,324], [14,'RDB$SECURITY_CLASS',14,31,0,326], [15,'RDB$COMPLEX_NAME',14,31,0,357], [16,'RDB$NULL_FLAG',7,2,0,388], [17,'RDB$DEFAULT_SOURCE',261,8,0,392], [18,'RDB$COLLATION_ID',7,2,0,400]]],
    'RDB$RELATIONS' => ['rel'=>6,'len'=>436,'fields'=>[[0,'RDB$VIEW_BLR',261,8,0,4], [1,'RDB$VIEW_SOURCE',261,8,0,12], [2,'RDB$DESCRIPTION',261,8,0,20], [3,'RDB$RELATION_ID',7,2,0,28], [4,'RDB$SYSTEM_FLAG',7,2,0,30], [5,'RDB$DBKEY_LENGTH',7,2,0,32], [6,'RDB$FORMAT',7,2,0,34], [7,'RDB$FIELD_ID',7,2,0,36], [8,'RDB$RELATION_NAME',14,31,0,38], [9,'RDB$SECURITY_CLASS',14,31,0,69], [10,'RDB$EXTERNAL_FILE',37,253,0,100], [11,'RDB$RUNTIME',261,8,0,356], [12,'RDB$EXTERNAL_DESCRIPTION',261,8,0,364], [13,'RDB$OWNER_NAME',14,31,0,372], [14,'RDB$DEFAULT_CLASS',14,31,0,403], [15,'RDB$FLAGS',7,2,0,434]]],    ];

    private const ALIGN = [7=>2, 8=>4, 9=>4, 10=>4, 11=>4, 12=>4, 13=>4, 14=>1,
                           16=>4, 27=>4, 35=>4, 37=>2, 40=>1, 45=>4, 261=>4];
    private const SIZE  = [7=>2, 8=>4, 9=>8, 10=>4, 11=>8, 12=>4, 13=>4,
                           16=>8, 27=>8, 35=>8, 45=>8, 261=>8];

    public string $path;
    private $fh;
    public int $pageSize = 4096;
    public int $pageCount = 0;
    public int $ods = 0, $odsMinor = 0;
    private array $pagesByRel = [];     
    private array $relations = [];      
    private array $cols = [];           
    private array $indexed = [];        
    private array $pageCache = [];
    private bool $locked = false;

    public function __construct(string $path, bool $write = false)
    {
        $this->path = $path;
        $this->fh = @fopen($path, $write ? 'r+b' : 'rb');
        if (!$this->fh) throw new GdbError(t('Cannot open the database file.'));

        $mode = ($write ? LOCK_EX : LOCK_SH) | LOCK_NB;
        for ($i = 0; $i < 100; $i++) {          
            if (@flock($this->fh, $mode)) { $this->locked = true; break; }
            usleep(50000);
        }
        if (!$this->locked && $write) {
            fclose($this->fh); $this->fh = null;
            throw new GdbError(t('The file is busy — someone else is saving right now. Try again in a moment.'));
        }
        $hdr = (string)fread($this->fh, 128);
        if (strlen($hdr) < 128 || ord($hdr[0]) !== 1) throw new GdbError(t('This is not a Firebird database (.gdb).'));
        $u = unpack('vps/vods', substr($hdr, 16, 4));
        $this->pageSize = $u['ps'];
        $this->ods      = $u['ods'];
        $this->odsMinor = unpack('v', substr($hdr, 62, 2))[1];
        if ($this->ods !== 10) {
            throw new GdbError(t('Only ODS 10.x (Firebird 1.x) is supported, this file has ODS %d.', $this->ods));
        }
        if ($this->pageSize < 1024 || $this->pageSize > 16384) throw new GdbError(t('Unexpected page size.'));
        $this->pageCount = (int)floor(filesize($path) / $this->pageSize);
        $this->scanPages();
        $this->loadMeta();
    }

    public function __destruct()
    {
        if ($this->fh) { if ($this->locked) @flock($this->fh, LOCK_UN); @fclose($this->fh); }
    }



    public function page(int $n): string
    {
        if (isset($this->pageCache[$n])) return $this->pageCache[$n];
        if ($n < 0 || $n >= $this->pageCount) return '';
        fseek($this->fh, $n * $this->pageSize);
        $p = (string)fread($this->fh, $this->pageSize);
        if (count($this->pageCache) > 64) $this->pageCache = [];
        return $this->pageCache[$n] = $p;
    }


    private function scanPages(): void
    {
        fseek($this->fh, 0);
        $ps = $this->pageSize;
        for ($n = 0; $n < $this->pageCount; $n++) {
            fseek($this->fh, $n * $ps);
            $head = (string)fread($this->fh, 24);
            if (strlen($head) < 24) break;
            if (ord($head[0]) !== 5) continue;              
            $rel = unpack('v', substr($head, 20, 2))[1];
            $this->pagesByRel[$rel][] = $n;
        }
    }




    public static function unrle(string $b, int $limit = 0): string
    {
        $out = ''; $i = 0; $n = strlen($b);
        while ($i < $n) {
            if ($limit && strlen($out) >= $limit) break;   
            $c = ord($b[$i]); $i++;
            if ($c === 0) continue;
            if ($c < 128) { $out .= substr($b, $i, $c); $i += $c; }
            else { if ($i >= $n) break; $out .= str_repeat($b[$i], 256 - $c); $i++; }
        }
        return $out;
    }




    public static function rleChunks(string $d, array $caps): ?array
    {
        $chunks = array_fill(0, count($caps), '');
        $ci = 0; $i = 0; $n = strlen($d);
        while ($i < $n) {
            if ($ci >= count($caps)) return null;
            $rem = $caps[$ci] - strlen($chunks[$ci]);
            if ($rem < 2) { $ci++; continue; }
            $run = 1;
            while ($i + $run < $n && $d[$i + $run] === $d[$i] && $run < 127) $run++;
            if ($run >= 3) {
                $chunks[$ci] .= chr(256 - $run) . $d[$i];
                $i += $run;
                continue;
            }

            $start = $i; $lit = 0; $max = min(127, $rem - 1);
            while ($i < $n && $lit < $max) {
                $r = 1;
                while ($i + $r < $n && $d[$i + $r] === $d[$i] && $r < 127) $r++;
                if ($r >= 3) break;
                if ($lit + $r > $max) $r = $max - $lit;
                $i += $r; $lit += $r;
            }
            if ($lit === 0) { $ci++; continue; }
            $chunks[$ci] .= chr($lit) . substr($d, $start, $lit);
        }
        return $chunks;
    }

    private function slot(int $page, int $line): ?array
    {
        $p = $this->page($page);
        if ($p === '' || ord($p[0]) !== 5) return null;
        $cnt = unpack('v', substr($p, 22, 2))[1];
        if ($line < 0 || $line >= $cnt) return null;
        $s = unpack('voff/vlen', substr($p, 24 + $line * 4, 4));
        if (!$s['off'] || !$s['len']) return null;
        return [$s['off'], $s['len'], substr($p, $s['off'], $s['len'])];
    }


    private function assemble(int $page, int $line, int $limit = 0): ?array
    {
        $chain = []; $comp = ''; $guard = 0;
        $cur = [$page, $line];
        while ($cur && $guard++ < 64) {
            $s = $this->slot($cur[0], $cur[1]);
            if ($s === null) break;
            [$off, $len, $rec] = $s;
            if (strlen($rec) < 13) break;
            $flags = unpack('v', substr($rec, 10, 2))[1];
            if ($flags & 8) {
                $f = unpack('Vp/vl', substr($rec, 16, 6));
                $head = 22; $next = [$f['p'], $f['l']];
            } else { $head = 13; $next = null; }
            $chain[] = ['page' => $cur[0], 'line' => $cur[1], 'off' => $off, 'len' => $len,
                        'head' => $head, 'cap' => $len - $head, 'flags' => $flags];
            $comp .= substr($rec, $head);
            $cur = $next;
        }
        if (!$chain) return null;
        return [self::unrle($comp, $limit), $chain];
    }



    public static function layout(array $fields): array
    {
        $maxId = 0;
        foreach ($fields as $f) $maxId = max($maxId, $f[0]);
        $off = (int)(((intdiv($maxId + 1 + 7, 8)) + 3) / 4) * 4;   
        $out = [];
        foreach ($fields as $f) {
            [$fid, $name, $t, $len, $scale] = $f;
            if ($t === 14)      { $sz = $len; $al = 1; }
            elseif ($t === 37)  { $sz = $len + 2; $al = 2; }
            else                { $sz = self::SIZE[$t] ?? $len; $al = self::ALIGN[$t] ?? 4; }
            $off = (intdiv($off + $al - 1, $al)) * $al;
            $out[] = ['id' => $fid, 'name' => $name, 'type' => $t, 'len' => $len,
                      'scale' => $scale, 'off' => $off, 'size' => $sz];
            $off += $sz;
        }
        return [$out, $off];
    }


    private static function cut(string $v, int $len): string
    {
        if (strlen($v) > $len) {
            if (function_exists('mb_strcut')) {
                $v = mb_strcut($v, 0, $len, 'UTF-8');
            } else {
                $v = substr($v, 0, $len);

                for ($i = 0; $i < 3 && $v !== '' && !self::utf8ok($v); $i++) $v = substr($v, 0, -1);
            }
        }
        return str_pad($v, $len, ' ');
    }

    private static function utf8ok(string $v): bool
    {
        return (bool)preg_match('//u', $v);
    }

    public static function value(string $d, array $f)
    {
        $fid = $f['id']; $off = $f['off']; $t = $f['type'];
        if (ord($d[$fid >> 3]) >> ($fid & 7) & 1) return null;
        switch ($t) {
            case 14:  return rtrim(substr($d, $off, $f['len']), " \0");
            case 37:  $n = unpack('v', substr($d, $off, 2))[1]; return substr($d, $off + 2, $n);
            case 7:   $v = unpack('v', substr($d, $off, 2))[1]; if ($v > 32767) $v -= 65536; break;
            case 8:   $v = unpack('V', substr($d, $off, 4))[1]; if ($v > 2147483647) $v -= 4294967296; break;
            case 16:  $v = unpack('P', substr($d, $off, 8))[1];
                      if ($v > 9223372036854775807) $v = (int)($v - 18446744073709551616); break;
            case 10:  return unpack('g', substr($d, $off, 4))[1];
            case 11: case 27: return unpack('e', substr($d, $off, 8))[1];
            case 35:  $a = unpack('Vd/Vt', substr($d, $off, 8));
                      $days = $a['d'] > 2147483647 ? $a['d'] - 4294967296 : $a['d'];
                      return gmdate('Y-m-d H:i:s', ($days - 40587) * 86400 + (int)($a['t'] / 10000));
            case 12:  $days = unpack('V', substr($d, $off, 4))[1];
                      return gmdate('Y-m-d', ($days - 40587) * 86400);
            case 261: return '[BLOB]';
            default:  return null;
        }
        return $f['scale'] ? $v / pow(10, -$f['scale']) : $v;
    }


    public static function poke(string $d, array $f, $v): string
    {
        $fid = $f['id']; $off = $f['off']; $t = $f['type'];
        $byte = $fid >> 3; $bit = 1 << ($fid & 7);
        $flag = ord($d[$byte]);
        if ($v === null) {
            $d[$byte] = chr($flag | $bit);
            return $d;
        }
        $d[$byte] = chr($flag & ~$bit);
        switch ($t) {
            case 14: $s = self::cut((string)$v, $f['len']); break;
            case 37: $s = rtrim(self::cut((string)$v, $f['len'])); $s = pack('v', strlen($s)) . str_pad($s, $f['len'], "\0"); break;
            case 7:  $s = pack('v', ((int)round((float)$v * ($f['scale'] ? pow(10, -$f['scale']) : 1))) & 0xFFFF); break;
            case 8:  $s = pack('V', ((int)round((float)$v * ($f['scale'] ? pow(10, -$f['scale']) : 1))) & 0xFFFFFFFF); break;
            case 16: $s = pack('P', (int)round((float)$v * ($f['scale'] ? pow(10, -$f['scale']) : 1))); break;
            case 10: $s = pack('g', (float)$v); break;
            case 11: case 27: $s = pack('e', (float)$v); break;
            case 35: $ts = strtotime((string)$v . ' UTC'); if ($ts === false) $ts = 0;
                     $s = pack('VV', intdiv($ts, 86400) + 40587, ($ts % 86400) * 10000); break;
            case 12: $ts = strtotime((string)$v . ' UTC'); if ($ts === false) $ts = 0;
                     $s = pack('V', intdiv($ts, 86400) + 40587); break;
            default: throw new GdbError(t('Field type %s cannot be written.', (string)$t));
        }
        return substr_replace($d, $s, $off, strlen($s));
    }



    private function sysRows(string $name): array
    {
        $s = self::SYS[$name];
        $fields = [];
        foreach ($s['fields'] as $f) {
            $fields[] = ['id' => $f[0], 'name' => $f[1], 'type' => $f[2], 'len' => $f[3],
                         'scale' => $f[4], 'off' => $f[5], 'size' => 0];
        }
        $out = [];
        foreach ($this->rawRows($s['rel'], $s['len']) as $r) {
            $row = [];
            foreach ($fields as $f) $row[$f['name']] = self::value($r['data'], $f);
            $out[] = $row;
        }
        return $out;
    }


    private function rawRows(int $rel, int $fmtLen): iterable
    {
        foreach ($this->pagesByRel[$rel] ?? [] as $p) {
            $pg = $this->page($p);
            if ($pg === '') continue;
            $cnt = unpack('v', substr($pg, 22, 2))[1];
            for ($i = 0; $i < $cnt; $i++) {
                $s = unpack('voff/vlen', substr($pg, 24 + $i * 4, 4));
                if (!$s['off'] || $s['len'] < 13) continue;
                $flags = unpack('v', substr($pg, $s['off'] + 10, 2))[1];
                if ($flags & 1 || $flags & 4) continue;      
                $a = $this->assemble($p, $i, $fmtLen);
                if ($a === null) continue;
                $data = $a[0];
                if (strlen($data) < $fmtLen) $data = str_pad($data, $fmtLen, "\0");
                yield ['page' => $p, 'line' => $i, 'data' => $data];
            }
        }
    }

    private function loadMeta(): void
    {
        foreach ($this->sysRows('RDB$RELATIONS') as $r) {
            $n = $r['RDB$RELATION_NAME'];
            if ($n === null || $n === '') continue;
            $this->relations[$n] = ['id' => (int)$r['RDB$RELATION_ID'],
                                    'system' => (bool)$r['RDB$SYSTEM_FLAG'],
                                    'view' => $r['RDB$VIEW_BLR'] !== null,
                                    'format' => (int)$r['RDB$FORMAT']];
        }
        $dom = [];
        foreach ($this->sysRows('RDB$FIELDS') as $r) $dom[$r['RDB$FIELD_NAME']] = $r;

        $byTable = [];
        foreach ($this->sysRows('RDB$RELATION_FIELDS') as $r) {
            $d = $dom[$r['RDB$FIELD_SOURCE']] ?? null;
            if ($d === null) continue;
            $byTable[$r['RDB$RELATION_NAME']][] = [
                'id' => (int)$r['RDB$FIELD_ID'], 'name' => $r['RDB$FIELD_NAME'],
                'type' => (int)$d['RDB$FIELD_TYPE'], 'len' => (int)$d['RDB$FIELD_LENGTH'],
                'scale' => (int)($d['RDB$FIELD_SCALE'] ?? 0), 'sub' => (int)($d['RDB$FIELD_SUB_TYPE'] ?? 0),
                'clen' => (int)($d['RDB$CHARACTER_LENGTH'] ?? 0),
                'pos' => (int)$r['RDB$FIELD_POSITION'],
                'nullable' => ((int)($r['RDB$NULL_FLAG'] ?? 0)) !== 1,
                'domain' => (string)$r['RDB$FIELD_SOURCE'],
            ];
        }
        foreach ($byTable as $t => $cl) {
            usort($cl, fn($a, $b) => $a['id'] <=> $b['id']);
            [$lay, $total] = self::layout(array_map(fn($c) => [$c['id'], $c['name'], $c['type'], $c['len'], $c['scale']], $cl));
            $meta = [];
            foreach ($cl as $k => $c) { $c['off'] = $lay[$k]['off']; $c['size'] = $lay[$k]['size']; $meta[$c['name']] = $c; }
            $order = $cl;
            usort($order, fn($a, $b) => $a['pos'] <=> $b['pos']);
            $this->cols[$t] = ['len' => $total, 'meta' => $meta,
                               'order' => array_map(fn($c) => $c['name'], $order)];
        }

        $segs = [];
        foreach ($this->sysRows('RDB$INDEX_SEGMENTS') as $r) $segs[$r['RDB$INDEX_NAME']][] = $r['RDB$FIELD_NAME'];
        foreach ($this->sysRows('RDB$INDICES') as $r) {
            foreach ($segs[$r['RDB$INDEX_NAME']] ?? [] as $f) $this->indexed[$r['RDB$RELATION_NAME']][$f] = true;
        }
    }



    public function tables(): array
    {
        $out = [];
        foreach ($this->relations as $n => $r) {
            if ($r['system'] || $r['view']) continue;
            if (!isset($this->cols[$n])) continue;
            $out[$n] = ['name' => $n, 'id' => $r['id'], 'is_view' => false];
        }
        ksort($out);
        return $out;
    }

    public function columns(string $t): array
    {
        if (!isset($this->cols[$t])) throw new GdbError(t('No such table: %s', $t));
        $c = $this->cols[$t];
        $out = [];
        foreach ($c['order'] as $name) {
            $m = $c['meta'][$name];
            $m['indexed'] = isset($this->indexed[$t][$name]);
            $out[] = $m;
        }
        return $out;
    }

    public function isIndexed(string $t, string $col): bool { return isset($this->indexed[$t][$col]); }


    public function rows(string $t): iterable
    {
        if (!isset($this->cols[$t], $this->relations[$t])) throw new GdbError(t('No such table: %s', $t));
        $rel = $this->relations[$t]['id'];
        $c = $this->cols[$t];
        foreach ($this->rawRows($rel, $c['len']) as $r) {
            $row = ['__rowid' => $r['page'] . ':' . $r['line']];
            foreach ($c['order'] as $name) $row[$name] = self::value($r['data'], $c['meta'][$name]);
            yield $row;
        }
    }


    public function countRows(string $t): int
    {
        if (!isset($this->relations[$t])) throw new GdbError(t('No such table: %s', $t));
        $rel = $this->relations[$t]['id'];
        $n = 0;
        foreach ($this->pagesByRel[$rel] ?? [] as $p) {
            $pg = $this->page($p);
            if ($pg === '') continue;
            $cnt = unpack('v', substr($pg, 22, 2))[1];
            for ($i = 0; $i < $cnt; $i++) {
                $s = unpack('voff/vlen', substr($pg, 24 + $i * 4, 4));
                if (!$s['off'] || $s['len'] < 13) continue;
                $flags = unpack('v', substr($pg, $s['off'] + 10, 2))[1];
                if ($flags & 1 || $flags & 4) continue;
                $n++;
            }
        }
        return $n;
    }

    public function row(string $t, string $rowid): ?array
    {
        if (!isset($this->cols[$t])) throw new GdbError(t('No such table: %s', $t));
        if (!preg_match('/^\d+:\d+$/', $rowid)) return null;
        [$p, $l] = array_map('intval', explode(':', $rowid));
        $c = $this->cols[$t];
        $a = $this->assemble($p, $l, $c['len']);
        if ($a === null) return null;
        $data = str_pad($a[0], $c['len'], "\0");
        $row = ['__rowid' => $rowid];
        foreach ($c['order'] as $name) $row[$name] = self::value($data, $c['meta'][$name]);
        return $row;
    }





    public function update(string $t, string $rowid, array $values): bool
    {
        if (!isset($this->cols[$t])) throw new GdbError(t('No such table: %s', $t));
        if (!preg_match('/^\d+:\d+$/', $rowid)) throw new GdbError(t('Bad row id.'));
        $c = $this->cols[$t];
        [$p, $l] = array_map('intval', explode(':', $rowid));
        $a = $this->assemble($p, $l, $c['len']);
        if ($a === null) throw new GdbError(t('Row not found — the file may have changed.'));
        [$data, $chain] = $a;
        $data = str_pad($data, $c['len'], "\0");

        $changed = false;
        foreach ($values as $col => $v) {
            $f = $c['meta'][$col] ?? null;
            if ($f === null || $f['type'] === 261) continue;
            $old = self::value($data, $f);
            if ($old === $v || (is_numeric($old) && is_numeric($v) && (float)$old === (float)$v)) continue;
            if ($this->isIndexed($t, $col)) {
                throw new GdbError(t('Column %s belongs to a database index — it cannot be changed, the index would stop matching the data.', $col));
            }
            $data = self::poke($data, $f, $v);
            $changed = true;
        }
        if (!$changed) return false;

        $caps = array_map(fn($s) => $s['cap'], $chain);
        $chunks = self::rleChunks($data, $caps);
        if ($chunks === null) {

            $last = count($chain) - 1;
            $need = max(16, (int)ceil(strlen($data) / 4));
            $grown = $this->grow($chain[$last], $need);
            if ($grown !== null) {
                $chain[$last] = $grown;
                $caps[$last] = $grown['cap'];
                $chunks = self::rleChunks($data, $caps);
            }
            if ($chunks === null) {
                throw new GdbError(t('The new values do not fit into the record — there is no free space left on the database page. Shorten the text fields.'));
            }
        }
        foreach ($chain as $k => $s) $this->writeSlot($s, $chunks[$k]);
        $this->pageCache = [];
        return true;
    }


    public function updateBulk(string $t, array $ops): int
    {
        $n = 0;


        foreach ($ops as $rowid => $vals) {
            try { if ($this->update($t, (string)$rowid, $vals)) $n++; }
            catch (Throwable $e) { throw new GdbBulkError($e->getMessage(), $n, $e); }
        }
        return $n;
    }


    private function grow(array $s, int $need): ?array
    {
        $pg = $this->page($s['page']);
        if ($pg === '') return null;
        $cnt = unpack('v', substr($pg, 22, 2))[1];
        $lowest = $this->pageSize;
        for ($i = 0; $i < $cnt; $i++) {
            $x = unpack('voff/vlen', substr($pg, 24 + $i * 4, 4));
            if ($x['off']) $lowest = min($lowest, $x['off']);
        }
        $top = 24 + $cnt * 4;
        $newLen = $s['len'] + $need;
        $newOff = intdiv($lowest - $newLen, 4) * 4;          
        if ($newOff < $top + 4) return null;                 
        $rec = substr($pg, $s['off'], $s['len']);
        $pg = substr_replace($pg, str_pad($rec, $newLen, "\0"), $newOff, $newLen);
        $pg = substr_replace($pg, pack('vv', $newOff, $newLen), 24 + $s['line'] * 4, 4);
        $this->putPage($s['page'], $pg);
        $s['off'] = $newOff; $s['len'] = $newLen; $s['cap'] = $newLen - $s['head'];
        return $s;
    }

    private function writeSlot(array $s, string $payload): void
    {
        $pg = $this->page($s['page']);
        $body = str_pad($payload, $s['cap'], "\0");          
        $pg = substr_replace($pg, $body, $s['off'] + $s['head'], $s['cap']);
        $this->putPage($s['page'], $pg);
    }

    private function putPage(int $n, string $data): void
    {
        if (strlen($data) !== $this->pageSize) throw new GdbError(t('Internal error: page size.'));
        fseek($this->fh, $n * $this->pageSize);
        if (fwrite($this->fh, $data) !== $this->pageSize) throw new GdbError(t('Could not write the page — is the file read-only?'));
        fflush($this->fh);
        $this->pageCache[$n] = $data;
    }
}
