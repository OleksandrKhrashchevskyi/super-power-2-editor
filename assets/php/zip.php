<?php

declare(strict_types=1);

final class Zip
{

    public static function listFiles(string $path): array
    {
        $out = [];
        foreach (self::entries($path) as $name => $e) $out[$name] = $e['size'];
        return $out;
    }


    public static function extract(string $path, string $name): ?string
    {
        $e = self::entries($path)[$name] ?? null;
        if ($e === null) return null;
        $fh = fopen($path, 'rb');
        fseek($fh, $e['offset']);
        $loc = (string)fread($fh, 30);
        if (substr($loc, 0, 4) !== "PK\x03\x04") { fclose($fh); return null; }
        $h = unpack('vver/vflag/vmethod/vmtime/vmdate/Vcrc/Vcsize/Vusize/vnlen/vxlen', substr($loc, 4));
        fseek($fh, $e['offset'] + 30 + $h['nlen'] + $h['xlen']);
        $csize = $h['csize'] ?: $e['csize'];
        if ($csize <= 0) { fclose($fh); return ''; }
        $data = (string)fread($fh, $csize);
        fclose($fh);
        if (($h['method'] ?: $e['method']) === 8) {
            $raw = @gzinflate($data);
            if ($raw === false) return null;
            return $raw;
        }
        return $data;
    }


    public static function create(string $dest, array $files): bool
    {
        $local = ''; $central = ''; $offset = 0; $count = 0;
        foreach ($files as $name => $src) {
            $data = is_array($src) ? (string)$src['data'] : (string)@file_get_contents($src);
            if ($data === '' && !is_array($src) && !is_file($src)) continue;
            $crc  = crc32($data);
            $usize = strlen($data);
            $comp = function_exists('gzdeflate') ? @gzdeflate($data, 6) : false;
            if ($comp === false || strlen($comp) >= $usize) { $comp = $data; $method = 0; }
            else { $method = 8; }
            $csize = strlen($comp);
            $name  = str_replace('\\', '/', $name);
            $dos   = self::dosTime(time());

            $lh = "PK\x03\x04" . pack('vvvvvVVVvv', 20, 0, $method, $dos[0], $dos[1],
                                      $crc, $csize, $usize, strlen($name), 0) . $name;
            $local  .= $lh . $comp;
            $central .= "PK\x01\x02" . pack('vvvvvvVVVvvvvvVV', 20, 20, 0, $method, $dos[0], $dos[1],
                                            $crc, $csize, $usize, strlen($name), 0, 0, 0, 0, 32, $offset) . $name;
            $offset += strlen($lh) + $csize;
            $count++;
        }
        $eocd = "PK\x05\x06" . pack('vvvvVVv', 0, 0, $count, $count, strlen($central), $offset, 0);
        return @file_put_contents($dest, $local . $central . $eocd) !== false;
    }



    private static function entries(string $path): array
    {
        static $cache = [];
        $key = $path . '|' . (string)@filemtime($path);
        if (isset($cache[$key])) return $cache[$key];

        $size = (int)@filesize($path);
        if ($size < 22) return $cache[$key] = [];
        $fh = fopen($path, 'rb');
        $tailLen = min($size, 66000);
        fseek($fh, $size - $tailLen);
        $tail = (string)fread($fh, $tailLen);
        $pos = strrpos($tail, "PK\x05\x06");
        if ($pos === false) { fclose($fh); return $cache[$key] = []; }
        $eocd = unpack('vdisk/vcddisk/vnum/vtotal/Vcdsize/Vcdoff', substr($tail, $pos + 4, 18));
        fseek($fh, $eocd['cdoff']);
        $cd = (string)fread($fh, $eocd['cdsize']);
        fclose($fh);

        $out = []; $p = 0;
        for ($i = 0; $i < $eocd['total']; $i++) {
            if (substr($cd, $p, 4) !== "PK\x01\x02") break;
            $h = unpack('vvmade/vvneed/vflag/vmethod/vmtime/vmdate/Vcrc/Vcsize/Vusize/vnlen/vxlen/vclen/vdisk/vattr/Vext/Voff',
                        substr($cd, $p + 4, 42));
            $name = substr($cd, $p + 46, $h['nlen']);
            $p += 46 + $h['nlen'] + $h['xlen'] + $h['clen'];
            if ($name === '' || substr($name, -1) === '/') continue;
            $out[$name] = ['size' => $h['usize'], 'csize' => $h['csize'],
                           'method' => $h['method'], 'offset' => $h['off']];
        }
        return $cache[$key] = $out;
    }

    private static function dosTime(int $ts): array
    {
        $d = getdate($ts);
        return [(($d['hours'] << 11) | ($d['minutes'] << 5) | ($d['seconds'] >> 1)),
                ((($d['year'] - 1980) << 9) | ($d['mon'] << 5) | $d['mday'])];
    }
}
