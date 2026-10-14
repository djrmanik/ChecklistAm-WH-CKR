<?php
/**
 * ============================================================================
 *  SUPER EDITOR UI  ("Mode edit tampilan")                [GENC-13OKT26-TAMPILAN]
 *
 *  Teks & tata letak aplikasi bisa diubah dari dalam aplikasi oleh role yang punya hak
 *  `tampilan/edit` (Role Permissions), tanpa mengubah kode.
 *
 *  Cara kerja (ringkas, detail di RESEP Bagian 28):
 *   1. KONDISI v1 (belum ada perubahan tampilan) = view di-include seperti biasa -> HTML identik.
 *   2. Kalau ada perubahan teks (atau Mode edit / Pratinjau aktif), view TIDAK di-include tapi
 *      dibaca + "ditandai" (gx_src): teks statis di HTML view & string yang langsung di-echo diberi
 *      penanda tak terlihat (karakter Unicode area pribadi U+E000..U+E002). Data dari database selalu
 *      lewat echo variabel -> tidak pernah ditandai -> tidak bisa diubah dari sini.
 *   3. Sesudah halaman selesai dibentuk, gx_finish() membaca HTML akhir: penanda di teks / atribut
 *      teks (placeholder, title, ...) diganti teks baru (atau teks awal), penanda di tempat lain
 *      (class, href, script, ...) dibuang. Mode edit: teks dibungkus <gx-t> supaya bisa diklik.
 *   4. Riwayat: tabel genc_tampilan (1 baris = 1 versi, `sekarang` = penunjuk versi yang berlaku,
 *      sama dengan model Riwayat perubahan Mesin & Unit K84). Draf per user: genc_tampilan_draf.
 *
 *  Report PDF / Print / Word / CSV TIDAK pernah lewat sini (hanya layout halaman).
 * ============================================================================
 */
if (!function_exists('gx_state')) {

    define('GX_A', "\xEE\x80\x80");   // awal penanda
    define('GX_B', "\xEE\x80\x81");   // pemisah id | teks
    define('GX_C', "\xEE\x80\x82");   // akhir penanda
    define('GX_VER', '2026101402');   // versi format penanda (cache)
    /** Atribut yang isinya teks untuk dibaca orang (bisa diubah). Atribut lain: penanda dibuang. */
    define('GX_ATTRS', 'placeholder|title|aria-label|alt|data-label|data-genc-confirm|data-genc-confirm-msg|data-genc-confirm-ok|data-gs-busy|data-prompt-msg|data-loading-msg|data-available-msg|data-unavailable-msg|data-gm-confirm|data-gm-msg');

    /* ---------------------------------------------------------------- database */

    function gx_db() {
        if (!function_exists('genc_reg_db')) { require_once __DIR__ . '/genc_registry.php'; }
        return genc_reg_db();
    }

    function gx_schema_sql() {
        return array(
            "CREATE TABLE IF NOT EXISTS `genc_tampilan` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `versi` INT(11) NOT NULL,
                `sekarang` TINYINT(1) NOT NULL DEFAULT 0,
                `data` MEDIUMTEXT NULL,
                `ringkasan` VARCHAR(255) NOT NULL DEFAULT '',
                `meta` MEDIUMTEXT NULL,
                `halaman` TEXT NULL,
                `created_at` DATETIME NULL,
                `created_by` VARCHAR(100) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`), UNIQUE KEY `versi` (`versi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS `genc_tampilan_draf` (
                `user` VARCHAR(100) NOT NULL,
                `data` MEDIUMTEXT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        );
    }

    /** Buat tabel kalau belum ada (dipanggil hanya dari TampilanController). @return bool */
    function gx_ensure_tables() {
        $pdo = gx_db();
        if (!$pdo) { return false; }
        try { foreach (gx_schema_sql() as $q) { $pdo->exec($q); } return true; } catch (\Throwable $e) { return false; }
    }

    function gx_json($v) {
        $s = json_encode($v);   // \uXXXX -> aman untuk koneksi utf8 3 byte (K26)
        return $s === false ? '{}' : $s;
    }

    function gx_dec($s) {
        $a = json_decode((string) $s, true);
        return is_array($a) ? $a : array();
    }

    /** Bentuk konfigurasi tampilan yang selalu lengkap. */
    function gx_conf_norm($c) {
        $c = is_array($c) ? $c : array();
        foreach (array('teks', 'susunan', 'tema') as $k) { if (!isset($c[$k]) || !is_array($c[$k])) { $c[$k] = array(); } }
        return $c;
    }

    /** Versi yang berlaku: array('no' => int, 'conf' => array). Tabel belum ada / kosong = v1 kosong. */
    function gx_current($reset = false) {
        static $cur = null;
        if ($cur !== null && !$reset) { return $cur; }
        $cur = array('no' => 1, 'conf' => gx_conf_norm(array()), 'ok' => false);
        $pdo = gx_db();
        if (!$pdo) { return $cur; }
        try {
            $r = $pdo->query("SELECT versi, data FROM genc_tampilan WHERE sekarang = 1 ORDER BY versi DESC LIMIT 1")->fetch();
            $cur['ok'] = true;
            if ($r) { $cur['no'] = (int) $r['versi']; $cur['conf'] = gx_conf_norm(gx_dec($r['data'])); }
        } catch (\Throwable $e) { }
        return $cur;
    }

    function gx_user() { return defined('USER_NAME') ? (string) USER_NAME : ''; }

    function gx_draft_get($user = null, $reset = false) {
        static $cache = array();
        $user = $user === null ? gx_user() : $user;
        if ($user === '') { return null; }
        if (array_key_exists($user, $cache) && !$reset) { return $cache[$user]; }
        $cache[$user] = null;
        $pdo = gx_db();
        if (!$pdo) { return null; }
        try {
            $st = $pdo->prepare("SELECT data FROM genc_tampilan_draf WHERE user = ?");
            $st->execute(array($user));
            $r = $st->fetch();
            if ($r) { $d = gx_dec($r['data']); $cache[$user] = gx_draft_norm($d); }
        } catch (\Throwable $e) { }
        return $cache[$user];
    }

    function gx_draft_norm($d) {
        $d = is_array($d) ? $d : array();
        foreach (array('teks', 'data', 'susunan', 'tema') as $k) { if (!isset($d[$k]) || !is_array($d[$k])) { $d[$k] = array(); } }
        if (!isset($d['base'])) { $d['base'] = 0; }
        return $d;
    }

    function gx_draft_put($d, $user = null) {
        $user = $user === null ? gx_user() : $user;
        $pdo = gx_db();
        if (!$pdo || $user === '') { return false; }
        try {
            $st = $pdo->prepare("REPLACE INTO genc_tampilan_draf (user, data, updated_at) VALUES (?, ?, ?)");
            $st->execute(array($user, gx_json(gx_draft_norm($d)), date('Y-m-d H:i:s')));
            gx_draft_get($user, true);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    function gx_draft_del($user = null) {
        $user = $user === null ? gx_user() : $user;
        $pdo = gx_db();
        if (!$pdo || $user === '') { return; }
        try { $pdo->prepare("DELETE FROM genc_tampilan_draf WHERE user = ?")->execute(array($user)); } catch (\Throwable $e) { }
        gx_draft_get($user, true);
    }

    /** Jumlah perubahan di draf. */
    function gx_draft_count($d) {
        if (!$d) { return 0; }
        $n = count($d['teks']) + count($d['data']) + count($d['tema']);
        foreach ($d['susunan'] as $grp) { $n += count((array) $grp); }
        return $n;
    }

    /** Konfigurasi = versi sekarang + draf (null = hapus perubahan -> teks awal). */
    function gx_apply_draft($conf, $d) {
        $conf = gx_conf_norm($conf);
        if (!$d) { return $conf; }
        foreach ($d['teks'] as $k => $v) {
            if (!is_array($v) || !isset($v['t']) || $v['t'] === null) { unset($conf['teks'][$k]); }
            else { $conf['teks'][$k] = $v; }
        }
        foreach ($d['susunan'] as $sk => $grp) {   // [GENC-14OKT26-SUSUNAN] per cakupan (p|halaman / v|file) -> per grup
            foreach ((array) $grp as $ck => $v) {
                if ($v === null) { unset($conf['susunan'][$sk][$ck]); } else { $conf['susunan'][$sk][$ck] = $v; }
            }
            if (isset($conf['susunan'][$sk]) && !$conf['susunan'][$sk]) { unset($conf['susunan'][$sk]); }
        }
        foreach ($d['tema'] as $k => $v) {
            if ($v === null) { unset($conf['tema'][$k]); } else { $conf['tema'][$k] = $v; }
        }
        return $conf;
    }

    /* ---------------------------------------------------------------- status per request */

    /** Hak membuka Mode edit tampilan. */
    function gx_can_edit() {
        if (!function_exists('user_login_status') || user_login_status() != true || !class_exists('ACL')) { return false; }
        if (empty(ACL::$role_pages) && defined('USER_ROLE') && USER_ROLE) { new ACL; }
        return ACL::is_allowed('tampilan/edit');
    }

    /** '' | 'edit' | 'pratinjau' (disimpan di sesi, hanya untuk yang punya hak). */
    function gx_mode() {
        $m = function_exists('get_session') ? (string) get_session('gx_mode') : '';
        if ($m === '') { return ''; }
        if (!gx_can_edit()) { return ''; }
        return in_array($m, array('edit', 'pratinjau'), true) ? $m : '';
    }

    /**
     * Status tampilan request ini (sekali hitung).
     *  conf  = konfigurasi yang dipakai (versi sekarang, + draf kalau Mode edit / Pratinjau)
     *  inst  = view perlu ditandai (ada perubahan teks / mode edit)
     *  post  = HTML akhir perlu diproses (inst, susunan, tema)
     */
    function gx_state($reset = false) {
        static $st = null;
        if ($st !== null && !$reset) { return $st; }
        $cur = gx_current($reset);
        $mode = gx_mode();
        $d = $mode !== '' ? gx_draft_get() : null;
        $conf = $mode !== '' ? gx_apply_draft($cur['conf'], $d) : $cur['conf'];
        $st = array(
            'cur_no' => $cur['no'], 'ok' => $cur['ok'], 'mode' => $mode, 'draft' => $d, 'conf' => $conf,
            'inst' => $mode !== '' || !empty($conf['teks']) || !empty($conf['susunan']),   // susunan: perlu daftar file view yang dipakai halaman
            'post' => $mode !== '' || !empty($conf['teks']) || !empty($conf['susunan']) || !empty($conf['tema']),
        );
        return $st;
    }

    /** Sedang menandai view (antara gx_begin & gx_finish). */
    function gx_inst($set = null) {
        static $on = false;
        if ($set !== null) { $on = (bool) $set; }
        return $on;
    }

    /* ---------------------------------------------------------------- teks & kunci */

    function gx_norm($s) {
        $s = html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim((string) $s);
    }

    function gx_h($s) { return substr(md5(gx_norm($s)), 0, 12); }

    /** Teks layak ditandai: berisi huruf. */
    function gx_wordy($s) { return (bool) preg_match('/[A-Za-z\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}\x{00F8}-\x{024F}]/u', html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8')); }

    /** Halaman sekarang ("forklift/index", "home/index", "view:errors/error_404.php"). */
    function gx_page() {
        if (!empty($GLOBALS['gx_page_override'])) { return (string) $GLOBALS['gx_page_override']; }
        $p = class_exists('Router', false) ? strtolower((string) Router::$page_name) : '';
        $a = class_exists('Router', false) ? strtolower((string) Router::$page_action) : '';
        if ($p === '') { return 'home/index'; }
        if ($a === '' || $a === 'list') { $a = 'index'; }
        if ($p === 'index' && $a === 'login') { $a = 'index'; }   // gagal masuk = halaman Masuk yang sama
        return $p . '/' . $a;
    }

    /** Peta id file -> nama file relatif (app/views/...). */
    function gx_files($fid = null, $rel = null) {
        static $map = array();
        if ($rel !== null) { $map[$fid] = $rel; }
        return $map;
    }

    /** Path absolut (PAGES_DIR / LAYOUTS_DIR phpRAD relatif terhadap ROOT). */
    function gx_abs($p) {
        $p = str_replace('\\', '/', (string) $p);
        if ($p !== '' && $p[0] !== '/' && !preg_match('#^[A-Za-z]:/#', $p)) { $p = str_replace('\\', '/', ROOT) . $p; }
        return $p;
    }

    function gx_rel($abs) {
        $abs = gx_abs($abs);
        $root = str_replace('\\', '/', ROOT) . 'app/views/';
        return strpos($abs, $root) === 0 ? substr($abs, strlen($root)) : basename($abs);
    }

    /**
     * Teks baru untuk penanda: halaman ini > tempat ini (file view) > semua halaman.
     * @return array|null  array('t' => teks baru, 'k' => kunci) atau null
     */
    function gx_resolve($conf, $rel, $h) {
        $page = gx_page();
        foreach (array('p|' . $page . '|' . $h, 'v|' . $rel . '|' . $h, 'g|' . $h) as $k) {
            if (isset($conf['teks'][$k]['t']) && $conf['teks'][$k]['t'] !== null) { return array('t' => (string) $conf['teks'][$k]['t'], 'k' => $k); }
        }
        return null;
    }

    /** Teks JS (checklist-genc.js dll.) - hanya kunci "semua halaman" & "halaman ini", dicari lewat teks awal. */
    function gx_js_map($conf) {
        $out = array();
        $page = gx_page();
        foreach ($conf['teks'] as $k => $v) {
            if (!isset($v['d']) || !isset($v['t'])) { continue; }
            if (strpos($k, 'g|') === 0 || strpos($k, 'p|' . $page . '|') === 0) {
                $n = gx_norm($v['d']);
                if (!isset($out[$n]) || strpos($k, 'p|') === 0) { $out[$n] = (string) $v['t']; }
            }
        }
        return $out;
    }

    /* ---------------------------------------------------------------- penandaan view */

    /** Tandai satu potongan teks (sudah dipastikan konteks teks / nilai atribut). */
    function gx_mark_run($s, $fid) {
        if ($s === '' || !gx_wordy($s)) { return $s; }
        if (!preg_match('/^(\s*)(.*?)(\s*)$/s', $s, $m) || $m[2] === '') { return $s; }
        if (strlen($m[2]) > 600) { return $s; }
        return $m[1] . GX_A . $fid . GX_B . $m[2] . GX_C . $m[3];
    }

    /**
     * Penanda di potongan HTML (dari isi file view atau string PHP). State lexer dibawa antar potongan:
     * 0 teks, 1 di dalam tag, 2 nilai atribut (q = kutip), 3 komentar, 4 isi script/style/textarea.
     */
    function gx_mark_html($html, &$S, $fid) {
        $out = ''; $n = strlen($html); $i = 0; $run = '';
        while ($i < $n) {
            if ($S['s'] === 0) {
                $j = strpos($html, '<', $i);
                if ($j === false) { $run .= substr($html, $i); $i = $n; break; }
                $run .= substr($html, $i, $j - $i);
                $nx = substr($html, $j + 1, 1);
                if (substr($html, $j, 4) === '<!--') { $out .= gx_mark_run($run, $fid); $run = ''; $S['s'] = 3; $out .= '<!--'; $i = $j + 4; continue; }
                if ($nx !== '' && (ctype_alpha($nx) || $nx === '/')) {
                    $out .= gx_mark_run($run, $fid); $run = '';
                    if (preg_match('/^<\/?([a-zA-Z][a-zA-Z0-9-]*)/', substr($html, $j, 40), $tm)) { $S['tag'] = strtolower($tm[1]); $S['close'] = $nx === '/'; $out .= $tm[0]; $i = $j + strlen($tm[0]); }
                    else { $out .= '<'; $i = $j + 1; }
                    $S['s'] = 1; $S['attr'] = ''; continue;
                }
                $run .= '<'; $i = $j + 1; continue;
            }
            if ($S['s'] === 1) {
                $c = $html[$i];
                if ($c === '>') {
                    $out .= '>'; $i++;
                    $S['s'] = (!$S['close'] && in_array($S['tag'], array('script', 'style', 'textarea', 'title'), true)) ? 4 : 0;
                    continue;
                }
                if ($c === '"' || $c === "'") { $S['s'] = 2; $S['q'] = $c; $out .= $c; $i++; continue; }
                if (preg_match('/\G([^\s=>"\'\/]+)\s*=?/', $html, $am, 0, $i) && $am[0] !== '') {
                    $S['attr'] = strtolower($am[1]); $out .= $am[0]; $i += strlen($am[0]); continue;
                }
                $out .= $c; $i++; continue;
            }
            if ($S['s'] === 2) {
                $j = strpos($html, $S['q'], $i);
                $val = $j === false ? substr($html, $i) : substr($html, $i, $j - $i);
                $out .= preg_match('/^(' . GX_ATTRS . ')$/', $S['attr']) ? gx_mark_run($val, $fid) : $val;
                if ($j === false) { $i = $n; break; }
                $out .= $S['q']; $i = $j + 1; $S['s'] = 1; $S['attr'] = ''; continue;
            }
            if ($S['s'] === 3) {
                $j = strpos($html, '-->', $i);
                if ($j === false) { $out .= substr($html, $i); $i = $n; break; }
                $out .= substr($html, $i, $j + 3 - $i); $i = $j + 3; $S['s'] = 0; continue;
            }
            if ($S['s'] === 4) {
                $j = stripos($html, '</' . $S['tag'], $i);
                if ($j === false) { $out .= substr($html, $i); $i = $n; break; }
                $out .= substr($html, $i, $j - $i); $i = $j; $S['s'] = 0; continue;
            }
        }
        if ($run !== '') { $out .= gx_mark_run($run, $fid); }
        return $out;
    }

    /** String kalimat untuk manusia (bukan kode / kunci / SQL / HTML). */
    function gx_human($v) {
        if (!gx_wordy($v) || strlen($v) > 300 || preg_match('/[<>{}$%#=;\\\\|`]/', $v)) { return false; }
        if (preg_match('/^\s*(SELECT|INSERT|UPDATE|DELETE|SHOW|ALTER|CREATE|DROP|REPLACE)\b/i', $v)) { return false; }
        if (!preg_match('/\s/', $v)) {   // satu kata: harus huruf besar di depan + huruf kecil, tanpa _ . / : -
            return (bool) preg_match('/^[A-Z][a-z]/', $v) && !preg_match('/[_.\/:@-]/', $v);
        }
        return !preg_match('/^[a-z0-9_.-]+(\s|$)/', $v) || preg_match('/\s[a-z]+\s/', $v);
    }

    /** Nilai string PHP dari token T_CONSTANT_ENCAPSED_STRING. */
    function gx_str_val($tok) {
        if ($tok[0] === "'") { return str_replace(array("\\\\", "\\'"), array("\\", "'"), substr($tok, 1, -1)); }
        if ($tok[0] === '"' && strpos($tok, '$') === false) { return stripcslashes(substr($tok, 1, -1)); }
        return null;
    }

    /**
     * Kode PHP view yang sudah ditandai (untuk eval). Isi file tidak pernah diubah di disk.
     * Yang ditandai: teks HTML di luar <?php ?> + string yang LANGSUNG di-echo (operan echo / . / ?: ,
     * atau argumen pertama genc_e / htmlspecialchars). include / require di dalam view ikut ditandai.
     */
    function gx_src($file) {
        static $mem = array();
        $file = gx_abs($file);
        $rel = gx_rel($file);
        $fid = substr(md5($rel), 0, 8);
        gx_files($fid, $rel);
        $mt = @filemtime($file);
        $ck = 'gx|' . GX_VER . '|' . $file . '|' . $mt;
        if (isset($mem[$ck])) { return $mem[$ck]; }
        if (function_exists('apcu_fetch')) { $ok = false; $v = @apcu_fetch($ck, $ok); if ($ok && is_string($v)) { return $mem[$ck] = $v; } }
        $src = (string) @file_get_contents($file);
        $toks = token_get_all($src);
        $S = array('s' => 0, 'tag' => '', 'close' => false, 'attr' => '', 'q' => '"');
        $out = '';
        $n = count($toks);
        $sig = function ($k, $dir) use ($toks, $n) {   // token bermakna sebelum / sesudah
            for ($j = $k + $dir; $j >= 0 && $j < $n; $j += $dir) {
                $t = $toks[$j];
                if (is_array($t) && in_array($t[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) { continue; }
                return $t;
            }
            return null;
        };
        $in_echo = false; $stack = array();   // tumpukan kurung di dalam echo: 'g' kurung biasa, 'e' fungsi escape (arg 1), 'x' lainnya
        $gs = array();                         // tumpukan kurung seluruh kode: 'a' array, 'c' panggilan fungsi / index, 'g' kurung biasa
        $esc = array('genc_e', 'htmlspecialchars', 'nl2br', 'ucfirst');
        for ($k = 0; $k < $n; $k++) {
            $t = $toks[$k];
            if (is_array($t)) {
                $id = $t[0];
                if ($id === T_INLINE_HTML) { $out .= gx_mark_html($t[1], $S, $fid); continue; }
                if ($id === T_FILE) { $out .= var_export($file, true); continue; }
                if ($id === T_DIR) { $out .= var_export(dirname($file), true); continue; }
                if ($id === T_ECHO || $id === T_PRINT || $id === T_OPEN_TAG_WITH_ECHO) { $in_echo = true; $stack = array(); $out .= $t[1]; continue; }
                if ($id === T_CLOSE_TAG) { $in_echo = false; $stack = array(); $out .= $t[1]; continue; }
                if ($id === T_INCLUDE || $id === T_REQUIRE) {
                    // include X;  ->  eval(gx_src_inc(X));
                    $expr = ''; $depth = 0; $j = $k + 1; $end = '';
                    for (; $j < $n; $j++) {
                        $u = $toks[$j];
                        $us = is_array($u) ? ($u[0] === T_DIR ? var_export(dirname($file), true) : ($u[0] === T_FILE ? var_export($file, true) : $u[1])) : $u;
                        if (!is_array($u)) {
                            if ($u === '(' || $u === '[') { $depth++; }
                            elseif ($u === ')' || $u === ']') { $depth--; }
                            elseif ($u === ';' && $depth === 0) { $end = ';'; break; }
                        }
                        elseif ($u[0] === T_CLOSE_TAG && $depth === 0) { $end = 'close'; break; }
                        if ($u === ')' && $depth < 0) { $end = ')'; break; }
                        $expr .= $us;
                    }
                    $out .= 'eval(\\gx_src_inc(' . $expr . '))';
                    if ($end === ';') { $out .= ';'; $k = $j; }
                    elseif ($end === 'close') { $out .= ';'; $k = $j - 1; }
                    else { $k = $j - 1; }
                    continue;
                }
                if ($id === T_CONSTANT_ENCAPSED_STRING && $in_echo) {
                    $top = $stack ? end($stack) : 'g';
                    $prev = $sig($k, -1); $next = $sig($k, 1);
                    $pv = is_array($prev) ? $prev[0] : $prev; $nv = is_array($next) ? $next[0] : $next;
                    $okp = in_array($pv, array(T_ECHO, T_PRINT, T_OPEN_TAG_WITH_ECHO, '.', '?', ':', ',', '(', T_COALESCE), true);
                    $okn = in_array($nv, array('.', ';', ':', ',', ')', T_CLOSE_TAG), true);
                    if ($top === 'x' || ($top === 'e' && $pv === ',')) { $okp = false; }
                    $val = ($okp && $okn) ? gx_str_val($t[1]) : null;
                    if ($val !== null && gx_wordy($val)) {
                        $LS = array('s' => preg_match('/^\s+[a-zA-Z-]+="/', $val) ? 1 : 0, 'tag' => '', 'close' => false, 'attr' => '', 'q' => '"');
                        $mk = gx_mark_html($val, $LS, $fid);
                        $out .= $mk === $val ? $t[1] : var_export($mk, true);
                        continue;
                    }
                }
                if ($id === T_CONSTANT_ENCAPSED_STRING && !$in_echo) {
                    // string kalimat (huruf besar di awal / ada spasi) yang DISIAPKAN untuk tampil: nilai =, .=, =>, ?:, return,
                    // operan "." , atau isi array(...). Bukan: argumen fungsi, index $x['...'], pembanding, case, kode (OK/NOK, a_b, x.y).
                    $val = gx_str_val($t[1]);
                    if ($val !== null && gx_human($val)) {
                        $top = $gs ? end($gs) : 'g';
                        $prev = $sig($k, -1); $next = $sig($k, 1);
                        $pv = is_array($prev) ? $prev[0] : $prev; $nv = is_array($next) ? $next[0] : $next;
                        $okp = in_array($pv, array('=', T_CONCAT_EQUAL, T_DOUBLE_ARROW, '?', ':', '.', T_COALESCE, T_RETURN), true)
                            || (in_array($pv, array('(', ','), true) && $top === 'a');
                        $okn = in_array($nv, array(';', ':', '.', T_CLOSE_TAG), true)
                            || (in_array($nv, array(',', ')', ']'), true) && in_array($top, array('a', 'g'), true));
                        if ($pv === T_DOUBLE_ARROW && !in_array($top, array('a'), true)) { $okp = false; }
                        if ($okp && $okn) {
                            $LS = array('s' => 0, 'tag' => '', 'close' => false, 'attr' => '', 'q' => '"');
                            $mk = gx_mark_html($val, $LS, $fid);
                            $out .= $mk === $val ? $t[1] : var_export($mk, true);
                            continue;
                        }
                    }
                }
                $out .= $t[1];
                continue;
            }
            // token satu karakter
            if ($t === '(' || $t === '[') {
                $prev = $sig($k, -1);
                $pv = is_array($prev) ? $prev[0] : $prev;
                if ($t === '[') { $gs[] = in_array($pv, array(T_VARIABLE, ']', ')', T_STRING, '}'), true) ? 'c' : 'a'; }
                elseif ($pv === T_ARRAY) { $gs[] = 'a'; }
                elseif (in_array($pv, array(T_STRING, T_VARIABLE, ')', ']', T_ISSET, T_EMPTY, T_UNSET, T_LIST, T_FUNCTION, T_USE, T_IF, T_ELSEIF, T_WHILE, T_FOR, T_FOREACH, T_SWITCH, T_CATCH, T_STATIC, T_NEW), true)) { $gs[] = 'c'; }
                else { $gs[] = 'g'; }
            }
            elseif ($t === '{') { $gs[] = 'b'; }
            elseif ($t === ')' || $t === ']' || $t === '}') { array_pop($gs); }
            if ($in_echo) {
                if ($t === '(') {
                    $prev = $sig($k, -1);
                    if (is_array($prev) && $prev[0] === T_STRING) { $stack[] = in_array(strtolower($prev[1]), $esc, true) ? 'e' : 'x'; }
                    elseif (is_array($prev) && in_array($prev[0], array(T_ARRAY, T_ISSET, T_EMPTY, T_VARIABLE, T_ISSET), true)) { $stack[] = 'x'; }
                    elseif ($prev === ')' || $prev === ']') { $stack[] = 'x'; }
                    else { $stack[] = 'g'; }
                }
                elseif ($t === '[' || $t === '{') { $stack[] = 'x'; }
                elseif ($t === ')' || $t === ']' || $t === '}') { array_pop($stack); }
                elseif ($t === ';' && !$stack) { $in_echo = false; }
            }
            $out .= $t;
        }
        $code = '?>' . $out;
        if (function_exists('apcu_store')) { @apcu_store($ck, $code, 86400); }
        return $mem[$ck] = $code;
    }

    /** include di dalam view yang sedang ditandai. */
    function gx_src_inc($path) {
        $p = gx_abs($path);
        $root = str_replace('\\', '/', ROOT) . 'app/views/';
        if (strpos($p, $root) === 0 && is_file($p) && strpos($p, '..') === false) { return gx_src($p); }
        return 'return include ' . var_export((string) $path, true) . ';';
    }

    /**
     * Teks dari kode (bukan data) yang dicetak lewat variabel - mis. label menu dari helpers/Menu.php,
     * judul kelompok sidebar. Ditandai dengan "tempat" = file view pemanggil ($rel).
     */
    function gx_lit($s, $rel) {
        $s = (string) $s;
        if (!gx_inst() || $s === '' || !gx_wordy($s)) { return $s; }
        $fid = substr(md5($rel), 0, 8);
        gx_files($fid, $rel);
        return gx_mark_run($s, $fid);
    }

    /** Alamat Mode edit (masuk / pratinjau / keluar) yang kembali ke halaman ini. */
    function gx_mode_url($m) {
        $here = function_exists('get_current_url') ? (string) get_current_url() : '';
        return set_url('tampilan/edit/mode/' . $m) . '?csrf_token=' . rawurlencode(class_exists('Csrf', false) ? (string) Csrf::$token : '') . '&kembali=' . rawurlencode($here);
    }

    /* ---------------------------------------------------------------- data (jalan pintas) */

    /**
     * Teks yang sebenarnya DATA: nama/area/pelaksanaan mesin, nama unit, label hasil cek.
     * @return array teks (dirapikan) => daftar sumber [kind, id, field, url, label]
     */
    function gx_data_map() {
        static $map = null;
        if ($map !== null) { return $map; }
        $map = array();
        $add = function ($text, $src) use (&$map) {
            $n = gx_norm($text);
            if ($n === '' || !gx_wordy($n)) { return; }
            $map[$n][] = $src;
        };
        try {
            if (!function_exists('genc_reg_builtin_folders')) { require_once __DIR__ . '/genc_registry.php'; }
            if (!function_exists('checklist_load_machine')) { require_once __DIR__ . '/checklist_report_engine.php'; }
            $can_m = ACL::is_allowed('mesin/edit');
            $can_p = ACL::is_allowed('master_select/edit');
            if ($can_m) {
                foreach (genc_reg_builtin_folders() as $k => $f) {
                    if (!empty($f['arsip']) || !empty($f['hapus'])) { continue; }
                    $add($f['title'], array('mesin', $k, 'nama', 'f:' . $k, 'Nama mesin'));
                    if (!empty($f['area'])) { $add($f['area'], array('mesin', $k, 'area', 'f:' . $k, 'Area mesin')); }
                    if (!empty($f['pelaksanaan'])) { $add($f['pelaksanaan'], array('mesin', $k, 'pelaksanaan', 'f:' . $k, 'Pelaksanaan')); }
                    if ($f['mode'] === 'units') {
                        foreach ($f['types'] as $t) {
                            $conf = checklist_load_machine($t['profile']);
                            foreach ((array) $conf['units'] as $u) {
                                if (!empty($u['deleted']) || !empty($u['archived'])) { continue; }
                                if (!empty($u['reg_id'])) { $add($u['label'], array('unit', (int) $u['reg_id'], 'label', 'u:' . (int) $u['reg_id'], 'Nama unit')); }
                                else {
                                    $slug = !empty($u['slug']) ? (string) $u['slug'] : genc_reg_unit_slug($u['label']);
                                    $add($u['label'], array('unit', $t['page'] . ':' . $slug, 'label', 'b:' . $t['page'] . ':' . $slug, 'Nama unit'));
                                }
                            }
                        }
                    }
                    else {
                        foreach ($f['pages'] as $p => $t) {
                            $note = genc_reg_builtin_note($p, '');
                            if ($note && !empty($note['arsip'])) { continue; }
                            $label = ($note && trim($note['label']) !== '') ? $note['label'] : $t['label'];
                            $add($label, array('unit', $p, 'label', 'b:' . $p, 'Nama unit'));
                        }
                        foreach (genc_reg_units_of_folder($k) as $id => $r) {
                            if (!empty($r['arsip'])) { continue; }
                            $add($r['label'], array('unit', (int) $id, 'label', 'u:' . (int) $id, 'Nama unit'));
                        }
                    }
                }
                foreach (genc_reg_data()['mesin'] as $p => $m) {
                    if (!empty($m['arsip'])) { continue; }
                    $add($m['nama'], array('mesin', $p, 'nama', 'f:' . $p, 'Nama mesin'));
                    if (trim((string) $m['area']) !== '') { $add($m['area'], array('mesin', $p, 'area', 'f:' . $p, 'Area mesin')); }
                    if (isset($m['pelaksanaan']) && trim((string) $m['pelaksanaan']) !== '') { $add($m['pelaksanaan'], array('mesin', $p, 'pelaksanaan', 'f:' . $p, 'Pelaksanaan')); }
                    foreach (genc_reg_units_of_folder($p) as $id => $r) {
                        if (!empty($r['arsip'])) { continue; }
                        $add($r['label'], array('unit', (int) $id, 'label', 'u:' . (int) $id, 'Nama unit'));
                    }
                }
            }
            if ($can_p && function_exists('genc_pilihan_labels')) {
                foreach (genc_pilihan_labels() as $kode => $lbl) { $add($lbl, array('pilihan', $kode, 'label', $kode, 'Label hasil cek')); }
            }
        } catch (\Throwable $e) { }
        // satu teks = satu sumber (teks ganda tidak dijadikan jalan pintas)
        foreach ($map as $k => $v) {
            $u = array();
            foreach ($v as $s) { $u[$s[0] . '|' . $s[1] . '|' . $s[2]] = $s; }
            if (count($u) !== 1) { unset($map[$k]); } else { $map[$k] = array_values($u)[0]; }
        }
        return $map;
    }

    /* ---------------------------------------------------------------- HTML akhir */

    /** Mulai satu render halaman (layout utama). @return bool perlu gx_finish */
    function gx_begin($layout) {
        static $depth = 0;
        if (!in_array((string) $layout, array('main_layout.php', 'info_layout.php'), true)) { return false; }
        if (gx_inst() || $depth > 0) { return false; }   // render bersarang (mis. halaman error di tengah render)
        $st = gx_state();
        if (!$st['post']) { return false; }
        $depth++;
        gx_inst($st['inst']);
        ob_start();
        return true;
    }

    function gx_esc_text($s) { return htmlspecialchars((string) $s, ENT_NOQUOTES, 'UTF-8'); }
    function gx_esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

    /** Buang semua penanda (teks awal). */
    function gx_strip($s) {
        if (strpos($s, GX_A) === false && strpos($s, GX_C) === false) { return $s; }
        $s = preg_replace('/' . GX_A . '[0-9a-f]{1,8}' . GX_B . '/', '', $s);
        return str_replace(array(GX_A, GX_B, GX_C), '', $s);
    }

    /**
     * Proses HTML akhir halaman.
     */
    function gx_finish($html) {
        gx_inst(false);
        // penanda yang ikut ter-encode (json_encode -> \ue000, urlencode -> %EE%80%80) selalu dibuang = teks awal
        if (stripos($html, '\\ue00') !== false) { $html = preg_replace('/\\\\u[eE]000[0-9a-f]{1,8}\\\\u[eE]001|\\\\u[eE]00[0-2]/', '', $html); }
        if (stripos($html, '%EE%80%8') !== false) { $html = preg_replace('/%EE%80%80[0-9a-f]{1,8}%EE%80%81|%EE%80%8[0-2]/i', '', $html); }
        $st = gx_state();
        if (!$st['inst']) { return gx_inject($html, $st, array()); }   // hanya tema: tidak perlu membaca penanda
        $conf = $st['conf'];
        $edit = $st['mode'] === 'edit';
        $mode = $st['mode'];
        $files = gx_files();
        $items = array(); $ix = array();
        $page = gx_page();
        $data = ($mode !== '') ? gx_data_map() : array();
        $dnew = array();   // pratinjau perubahan data di draf: teks lama => baru
        if ($mode !== '' && $st['draft']) {
            foreach ($st['draft']['data'] as $dk => $dv) { if (isset($dv['old'], $dv['new'])) { $dnew[gx_norm($dv['old'])] = array((string) $dv['new'], $dk); } }
        }
        $item = function ($fid, $raw, $ctx) use (&$items, &$ix, $files, $conf, $page) {
            $rel = isset($files[$fid]) ? $files[$fid] : '?';
            $h = gx_h($raw);
            $key = $rel . '|' . $h . '|' . $ctx;
            if (isset($ix[$key])) { return $ix[$key]; }
            $r = gx_resolve($conf, $rel, $h);
            $items[] = array('h' => $h, 'v' => $rel, 'c' => $ctx, 'd' => gx_norm($raw), 't' => $r ? $r['t'] : null, 'k' => $r ? $r['k'] : '');
            return $ix[$key] = count($items) - 1;
        };
        $resolve = function ($fid, $raw) use ($files, $conf) {
            $rel = isset($files[$fid]) ? $files[$fid] : '?';
            $r = gx_resolve($conf, $rel, gx_h($raw));
            return $r;
        };
        $re_m = '/' . GX_A . '([0-9a-f]{1,8})' . GX_B . '([^' . "\xEE" . '<>]*?)' . GX_C . '/';

        // potongan: komentar, elemen mentah (script/style/textarea/title), tag
        preg_match_all('~<!--.*?-->|<(script|style|textarea|title)\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>.*?</\1\s*>|</?[a-zA-Z][^\s/>]*(?:[^>"\']|"[^"]*"|\'[^\']*\')*>~is', $html, $mm, PREG_OFFSET_CAPTURE);
        $out = array(); $pos = 0; $last_tag = -1; $last_name = '';
        $attr_add = array();   // index potongan tag => array('name:N')
        $proc_text = function ($txt) use (&$out, &$last_tag, &$last_name, &$attr_add, $edit, $mode, $re_m, $item, $resolve, $data, $dnew) {
            if ($txt === '') { return ''; }
            if (strpos($txt, GX_A) !== false) {
                $in_opt = in_array($last_name, array('option', 'optgroup'), true);
                $txt = preg_replace_callback($re_m, function ($m) use ($edit, $item, $resolve, $in_opt, &$attr_add, $last_tag) {
                    if ($in_opt) {
                        $r = $resolve($m[1], $m[2]);
                        if ($edit && $last_tag >= 0) { $attr_add[$last_tag][] = 'text:' . $item($m[1], $m[2], 'option'); }
                        return $r ? gx_esc_text($r['t']) : $m[2];
                    }
                    if ($edit) {
                        $n = $item($m[1], $m[2], 'text');
                        $r = $resolve($m[1], $m[2]);
                        return '<gx-t data-gx="' . $n . '">' . ($r ? gx_esc_text($r['t']) : $m[2]) . '</gx-t>';
                    }
                    $r = $resolve($m[1], $m[2]);
                    return $r ? gx_esc_text($r['t']) : $m[2];
                }, $txt);
                return gx_strip($txt);
            }
            if ($mode !== '' && ($data || $dnew) && trim($txt) !== '' && !in_array($last_name, array('option', 'optgroup'), true)) {
                $n = gx_norm($txt);
                if (isset($dnew[$n]) || isset($data[$n])) {
                    preg_match('/^(\s*)(.*?)(\s*)$/s', $txt, $p);
                    $show = isset($dnew[$n]) ? gx_esc_text($dnew[$n][0]) : $p[2];
                    if ($edit) {
                        $src = isset($dnew[$n]) ? $dnew[$n][1] : implode('|', array_slice($data[$n], 0, 3));
                        return $p[1] . '<gx-d data-gxd="' . gx_esc_attr($src) . '" data-gxo="' . gx_esc_attr($n) . '">' . $show . '</gx-d>' . $p[3];
                    }
                    return $p[1] . $show . $p[3];
                }
            }
            return $txt;
        };
        foreach ($mm[0] as $gi => $g) {
            list($chunk, $off) = $g;
            $out[] = $proc_text(substr($html, $pos, $off - $pos));
            $pos = $off + strlen($chunk);
            if ($chunk[1] === '!' || !empty($mm[1][$gi][0])) {   // komentar / elemen mentah
                $out[] = gx_strip($chunk);
                $last_name = ''; continue;
            }
            $nm = preg_match('/^<\/?([a-zA-Z][a-zA-Z0-9-]*)/', $chunk, $tn) ? strtolower($tn[1]) : '';
            $closing = $chunk[1] === '/';
            if (strpos($chunk, GX_A) !== false) {
                $idx = count($out);
                $chunk = preg_replace_callback('/([^\s=\/<>"\']+)(\s*=\s*)("([^"]*)"|\'([^\']*)\')/', function ($am) use ($edit, $item, $resolve, &$attr_add, $idx, $re_m) {
                    $val = isset($am[5]) && $am[5] !== '' ? $am[5] : $am[4];
                    if (strpos($val, GX_A) === false) { return $am[0]; }
                    $q = $am[3][0];
                    $name = strtolower($am[1]);
                    if (!preg_match('/^(' . GX_ATTRS . ')$/', $name)) { return $am[1] . $am[2] . $q . gx_strip($val) . $q; }
                    $val = preg_replace_callback($re_m, function ($m) use ($edit, $item, $resolve, &$attr_add, $idx, $name) {
                        $r = $resolve($m[1], $m[2]);
                        if ($edit) { $attr_add[$idx][] = $name . ':' . $item($m[1], $m[2], 'attr'); }
                        return $r ? gx_esc_attr($r['t']) : $m[2];
                    }, $val);
                    return $am[1] . $am[2] . $q . gx_strip($val) . $q;
                }, $chunk);
                $chunk = gx_strip($chunk);
            }
            $out[] = $chunk;
            if (!$closing) { $last_tag = count($out) - 1; $last_name = $nm; } else { $last_name = ''; }
        }
        $out[] = $proc_text(substr($html, $pos));
        if ($edit) {
            foreach ($attr_add as $i => $list) {
                $list = array_values(array_unique($list));
                $out[$i] = preg_replace('/\s*(\/?)>$/', ' data-gx-a="' . gx_esc_attr(implode(';', $list)) . '"$1>', $out[$i], 1);
            }
        }
        $html = implode('', $out);
        return gx_inject($html, $st, $items);
    }

    /** Sisipan di <head> / sebelum </body>: kamus teks JS, aset & data Mode edit. */
    function gx_inject($html, $st, $items) {
        $conf = $st['conf'];
        $head = '';
        $js = gx_js_map($conf);
        if ($js) { $head .= '<script>window.GX_T=' . json_encode($js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ';</script>'; }
        // [GENC-14OKT26-TEMA] variabel warna (semua user); di Mode edit selalu ada supaya editor bisa menggantinya langsung
        $tcss = gx_tema_css(isset($conf['tema']) ? $conf['tema'] : array());
        if ($tcss !== '' || $st['mode'] === 'edit') { $head .= '<style id="gx-tema">' . $tcss . '</style>'; }
        $eff = gx_tema_eff(isset($conf['tema']) ? $conf['tema'] : array());
        if ($tcss !== '' && $eff['dasar'] === 'gelap') { $html = preg_replace('/<html\b/i', '<html data-gx-tema="gelap"', $html, 1); }
        $tail = '';
        // [GENC-14OKT26-SUSUNAN] susunan yang berlaku di halaman ini: halaman (p|) + file view yang dipakai (v|)
        $S = gx_susunan_for_page($conf);
        $v = defined('GENC_ASSET_VERSION') ? GENC_ASSET_VERSION : '1';
        if ($S || $st['mode'] !== '') {
            $head .= '<script>window.GX_S=' . json_encode($S ? $S : new stdClass, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ';'
                . ($st['mode'] === 'edit' ? 'window.GX_EDIT=1;' : '')
                . ($S && $st['mode'] === '' ? 'document.documentElement.className+=" gx-sw";setTimeout(function(){document.documentElement.classList.remove("gx-sw")},1500);' : '') . '</script>';
            $tail .= '<script src="' . gx_esc_attr(set_url('assets/js/genc-susunan.js')) . '?v=' . $v . '"></script>';
        }
        if ($st['mode'] !== '') {
            $v = defined('GENC_ASSET_VERSION') ? GENC_ASSET_VERSION : '1';
            $head .= '<link rel="stylesheet" href="' . gx_esc_attr(set_url('assets/css/genc-tampilan.css')) . '?v=' . $v . '">';
            $d = $st['draft'];
            $payload = array(
                'mode' => $st['mode'], 'page' => gx_page(), 'cur' => $st['cur_no'], 'ok' => $st['ok'],
                'items' => $items, 'count' => gx_draft_count($d), 'base' => $d ? (int) $d['base'] : 0,
                'stale' => $d && (int) $d['base'] !== 0 && (int) $d['base'] !== (int) $st['cur_no'],
                'draft' => $d ? array('teks' => (object) $d['teks'], 'data' => (object) $d['data'], 'susunan' => (object) $d['susunan'], 'tema' => (object) $d['tema']) : array('teks' => new stdClass, 'data' => new stdClass, 'susunan' => new stdClass, 'tema' => new stdClass),
                'tema' => array('aksen' => gx_tema_aksen(), 'status' => gx_tema_status(), 'cur' => (object) $conf['tema'], 'eff' => gx_tema_eff($conf['tema'])),
                'narrow' => 768,
                'data' => $st['mode'] === 'edit' ? gx_data_list() : array(),
                'js' => $st['mode'] === 'edit' ? gx_js_items($conf) : array(),
                'where' => gx_where_map($items), 'pageName' => gx_where_page(gx_page()),
                'csrf' => class_exists('Csrf', false) ? Csrf::$token : '',
                'url' => array('base' => set_url(''), 'edit' => set_url('tampilan/edit'), 'riwayat' => set_url('tampilan'), 'mesin' => set_url('mesin'), 'pilihan' => set_url('master_select'), 'masuk' => set_url('tampilan/edit/lihat/masuk'), 'daftar' => set_url('tampilan/edit/lihat/daftar')),
            );
            $tail .= '<script>window.GX=' . json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ';</script>';
            $tail .= '<script src="' . gx_esc_attr(set_url('assets/js/genc-tampilan.js')) . '?v=' . $v . '"></script>';
        }
        if ($head !== '') {
            $p = stripos($html, '</head>');
            $html = $p === false ? $head . $html : substr($html, 0, $p) . $head . substr($html, $p);
        }
        if ($tail !== '') {
            $p = strripos($html, '</body>');
            $html = $p === false ? $html . $tail : substr($html, 0, $p) . $tail . substr($html, $p);
        }
        return $html;
    }

    /** Susunan untuk halaman ini: array('p' => grup halaman, 'v' => array(file => grup)) atau array() kalau tidak ada. */
    function gx_susunan_for_page($conf) {
        $su = isset($conf['susunan']) ? (array) $conf['susunan'] : array();
        if (!$su) { return array(); }
        $out = array();
        $pk = 'p|' . gx_page();
        if (!empty($su[$pk])) { $out['p'] = $su[$pk]; }
        foreach (gx_files() as $fid => $rel) {
            if (!empty($su['v|' . $rel])) { $out['v'][$rel] = $su['v|' . $rel]; }
        }
        return $out;
    }

    /** Nama tempat tiap file view yang muncul di halaman (untuk editor). */
    function gx_where_map($items) {
        $out = array();
        foreach ($items as $it) { if (!isset($out[$it['v']])) { $out[$it['v']] = gx_where_view($it['v']); } }
        return $out ? $out : new stdClass;
    }

    /**
     * Teks tombol / dialog yang dibuat JavaScript (checklist-genc.js, genc-shell.js, genc-mesin.js): string di dalam T('...').
     * Diubah lewat laci "Teks lain"; dipakai JS lewat window.GX_T (kunci "semua halaman" / "halaman ini").
     */
    function gx_js_strings() {
        $out = array();
        foreach (array('checklist-genc.js', 'genc-shell.js', 'genc-mesin.js') as $f) {
            $src = (string) @file_get_contents(ROOT . 'assets/js/' . $f);
            if (preg_match_all("/\\bT\\('((?:[^'\\\\]|\\\\.)*)'\\)/", $src, $m)) {
                foreach ($m[1] as $x) { $x = stripcslashes($x); if (gx_wordy($x)) { $out[gx_norm($x)] = true; } }
            }
        }
        return array_keys($out);
    }

    function gx_js_items($conf) {
        $out = array();
        foreach (gx_js_strings() as $d) {
            $h = gx_h($d);
            $r = gx_resolve($conf, '-', $h);
            $out[] = array('h' => $h, 'v' => '-', 'c' => 'js', 'd' => $d, 't' => $r ? $r['t'] : null, 'k' => $r ? $r['k'] : '');
        }
        return $out;
    }

    /** Sumber data yang bisa diubah lewat jalan pintas (untuk editor). */
    function gx_data_list() {
        $out = array();
        foreach (gx_data_map() as $text => $s) { $out[$s[0] . '|' . $s[1] . '|' . $s[2]] = array('kind' => $s[0], 'id' => (string) $s[1], 'field' => $s[2], 'sel' => (string) $s[3], 'what' => $s[4], 'text' => $text); }
        return $out;
    }

    /**
     * Pesan (toast) dari controller: kalimat yang persis sama dengan string di kode controller ditandai
     * (tempat = "Pesan / toast"), supaya bisa diubah. Pesan yang memuat data (nama, tanggal) tidak ikut.
     */
    function gx_flash_mark($html) {
        $html = (string) $html;
        if (!gx_inst() || strpos($html, 'genc-toast') === false) { return $html; }
        $cat = gx_ctrl_strings();
        $fid = substr(md5('toast'), 0, 8);
        gx_files($fid, 'toast');
        return preg_replace_callback('~(<div class="genc-toast[^"]*"[^>]*>\s*<i [^>]*></i>\s*<span>)([^<]*)(</span>)~', function ($m) use ($cat, $fid) {
            $n = gx_norm($m[2]);
            return isset($cat[$n]) ? $m[1] . gx_mark_run($m[2], $fid) . $m[3] : $m[0];
        }, $html);
    }

    /** Semua string kalimat di controller (untuk mengenali pesan toast yang statis). */
    function gx_ctrl_strings() {
        static $set = null;
        if ($set !== null) { return $set; }
        $set = array();
        $files = array_merge((array) glob(ROOT . 'app/controllers/*.php'), (array) glob(ROOT . 'app/controllers/_base/*.php'));
        foreach ($files as $f) {
            foreach (@token_get_all((string) @file_get_contents($f)) as $t) {
                if (!is_array($t) || $t[0] !== T_CONSTANT_ENCAPSED_STRING) { continue; }
                $v = gx_str_val($t[1]);
                if ($v === null || !gx_wordy($v) || strpos($v, ' ') === false) { continue; }
                $set[gx_norm(strip_tags($v))] = true;
            }
        }
        return $set;
    }

    /* ---------------------------------------------------------------- riwayat */

    /** Ringkasan perbedaan dua konfigurasi (untuk riwayat). @return array baris teks + halaman yang kena */
    function gx_diff($a, $b) {
        $a = gx_conf_norm($a); $b = gx_conf_norm($b);
        $lines = array(); $pages = array();
        $keys = array_unique(array_merge(array_keys($a['teks']), array_keys($b['teks'])));
        sort($keys);
        foreach ($keys as $k) {
            $x = isset($a['teks'][$k]) ? $a['teks'][$k] : null;
            $y = isset($b['teks'][$k]) ? $b['teks'][$k] : null;
            $tx = $x ? (string) $x['t'] : null; $ty = $y ? (string) $y['t'] : null;
            if ($tx === $ty) { continue; }
            $d = $y ? $y['d'] : $x['d'];
            $w = gx_where($k, $y ? $y : $x);
            $pages[$w] = true;
            $lines[] = array('w' => $w, 'k' => $k, 'old' => $tx === null ? $d : $tx, 'new' => $ty === null ? $d : $ty, 'awal' => $ty === null);
        }
        $sks = array_unique(array_merge(array_keys($a['susunan']), array_keys($b['susunan'])));
        sort($sks);
        foreach ($sks as $sk) {
            $ga = isset($a['susunan'][$sk]) ? (array) $a['susunan'][$sk] : array(); $gb = isset($b['susunan'][$sk]) ? (array) $b['susunan'][$sk] : array();
            $cks = array_unique(array_merge(array_keys($ga), array_keys($gb)));
            sort($cks);
            foreach ($cks as $ck) {
                $x = isset($ga[$ck]) ? $ga[$ck] : null; $y = isset($gb[$ck]) ? $gb[$ck] : null;
                if (gx_json($x) === gx_json($y)) { continue; }
                $w = gx_where($sk . '|x', array());
                $nm = $y && isset($y['n']) ? $y['n'] : ($x && isset($x['n']) ? $x['n'] : 'bagian');
                $pages[$w] = true;
                $lines[] = array('w' => $w, 'k' => 'susunan:' . $sk . ':' . $ck, 'sec' => 'susunan', 'what' => 'Susunan ' . $nm . ($y === null ? ' kembali ke awal' : ' diubah' . gx_susunan_desc($y)));
            }
        }
        foreach (array('tema' => 'Tema') as $sec => $lbl) {
            $ks = array_unique(array_merge(array_keys($a[$sec]), array_keys($b[$sec])));
            sort($ks);
            foreach ($ks as $k) {
                $x = isset($a[$sec][$k]) ? $a[$sec][$k] : null; $y = isset($b[$sec][$k]) ? $b[$sec][$k] : null;
                if (gx_json($x) === gx_json($y)) { continue; }
                $w = $sec === 'tema' ? 'Tema' : gx_where_page($k);
                $pages[$w] = true;
                $lines[] = array('w' => $w, 'k' => $sec . ':' . $k, 'sec' => $sec, 'what' => gx_tema_desc($k, $y), 'old' => $x, 'new' => $y);
            }
        }
        return array('lines' => $lines, 'pages' => array_keys($pages));
    }

    /* ================================================================ TEMA (4D)   [GENC-14OKT26-TEMA]
       conf['tema'] = { dasar: terang|gelap, aksen: nama preset | #rrggbb, ok|nok|pr|pending: #rrggbb }
       Berlaku untuk semua user (versi tampilan). Kosong = tampilan bawaan (tidak ada CSS tambahan sama sekali). */

    /** Pilihan warna utama: kunci => array(label, hex). Warna tegas dipilih supaya teks putih di atasnya tetap terbaca (kontras >= 4.5). */
    function gx_tema_aksen() {
        return array(
            'biru' => array('Biru', '#1d6fe8'), 'indigo' => array('Indigo', '#4f46e5'), 'ungu' => array('Ungu', '#7c3aed'),
            'pink' => array('Pink', '#db2777'), 'merah' => array('Merah', '#dc2626'), 'oranye' => array('Oranye', '#c2410c'),
            'kuning' => array('Kuning', '#a16207'), 'hijau' => array('Hijau', '#15803d'), 'teal' => array('Teal', '#0f766e'),
            'biru-p' => array('Biru pastel', '#93c5fd'), 'indigo-p' => array('Indigo pastel', '#a5b4fc'), 'ungu-p' => array('Ungu pastel', '#c4b5fd'),
            'pink-p' => array('Pink pastel', '#f9a8d4'), 'merah-p' => array('Merah pastel', '#fca5a5'), 'oranye-p' => array('Oranye pastel', '#fdba74'),
            'kuning-p' => array('Kuning pastel', '#fde047'), 'hijau-p' => array('Hijau pastel', '#86efac'), 'teal-p' => array('Teal pastel', '#5eead4'),
        );
    }
    /** Warna status bawaan (sama dengan checklist-genc.css). */
    function gx_tema_status() {
        return array('ok' => array('OK / baik', '#12863a'), 'nok' => array('NOK / temuan', '#d92d20'), 'pr' => array('Perlu perbaikan (PR)', '#b54708'), 'pending' => array('Menunggu approval', '#6941c6'));
    }
    function gx_hex_ok($h) { return is_string($h) && preg_match('/^#[0-9a-f]{6}$/', strtolower($h)); }
    /** Bersihkan 1 nilai tema; null = tidak sah. */
    function gx_tema_val($k, $v) {
        if ($v === null) { return null; }
        $v = strtolower(trim((string) $v));
        if ($k === 'dasar') { return in_array($v, array('terang', 'gelap'), true) ? $v : null; }
        if ($k === 'aksen') { $a = gx_tema_aksen(); return (isset($a[$v]) || gx_hex_ok($v)) ? $v : null; }
        $st = gx_tema_status();
        if (isset($st[$k])) { return gx_hex_ok($v) ? $v : null; }
        return null;
    }
    function gx_rgb($h) { $h = ltrim($h, '#'); return array(hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))); }
    function gx_hex($c) { return sprintf('#%02x%02x%02x', max(0, min(255, round($c[0]))), max(0, min(255, round($c[1]))), max(0, min(255, round($c[2])))); }
    /** Campur: $t bagian warna a + (1 - $t) bagian warna b. */
    function gx_mix($a, $b, $t) { $x = gx_rgb($a); $y = gx_rgb($b); return gx_hex(array($x[0] * $t + $y[0] * (1 - $t), $x[1] * $t + $y[1] * (1 - $t), $x[2] * $t + $y[2] * (1 - $t))); }
    function gx_lum($h) {
        $c = gx_rgb($h); $o = array();
        foreach ($c as $v) { $v /= 255; $o[] = $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4); }
        return 0.2126 * $o[0] + 0.7152 * $o[1] + 0.0722 * $o[2];
    }
    function gx_contrast($a, $b) { $x = gx_lum($a); $y = gx_lum($b); return (max($x, $y) + 0.05) / (min($x, $y) + 0.05); }
    /** Geser warna ke hitam (latar terang) / putih (latar gelap) sampai kontras dengan $bg >= $min. */
    function gx_readable($c, $bg, $min) {
        $to = gx_lum($bg) > 0.4 ? '#000000' : '#ffffff';
        for ($i = 0; $i <= 20 && gx_contrast($c, $bg) < $min; $i++) { $c = gx_mix($c, $to, 0.92); }
        return $c;
    }
    /** Warna teks di atas latar $c: putih kalau kontrasnya cukup (>= 4.5), selain itu yang paling kontras. */
    function gx_on($c) {
        $w = gx_contrast('#ffffff', $c); $d = gx_contrast('#101828', $c);
        return ($w >= 4.5 || $w >= $d) ? '#fff' : '#101828';
    }
    function gx_rgba($h, $a) { $c = gx_rgb($h); return 'rgba(' . $c[0] . ',' . $c[1] . ',' . $c[2] . ',' . $a . ')'; }

    /** Warna efektif tema (setelah bawaan diisi). */
    function gx_tema_eff($t) {
        $t = is_array($t) ? $t : array();
        $a = gx_tema_aksen();
        $ak = isset($t['aksen']) ? (string) $t['aksen'] : 'biru';
        $out = array('dasar' => (isset($t['dasar']) && $t['dasar'] === 'gelap') ? 'gelap' : 'terang', 'aksen' => isset($a[$ak]) ? $a[$ak][1] : (gx_hex_ok($ak) ? strtolower($ak) : '#1d6fe8'));
        foreach (gx_tema_status() as $k => $d) { $out[$k] = (isset($t[$k]) && gx_hex_ok($t[$k])) ? strtolower($t[$k]) : $d[1]; }
        return $out;
    }

    /** CSS variabel tema. '' kalau tema kosong (tampilan bawaan). */
    function gx_tema_css($t) {
        $t = is_array($t) ? array_filter($t, function ($v) { return $v !== null && $v !== ''; }) : array();
        if (!$t) { return ''; }
        $e = gx_tema_eff($t);
        $dark = $e['dasar'] === 'gelap';
        $v = array();
        $surf = '#ffffff';
        if ($dark) {
            $surf = '#161b22';
            $v += array(
                'bg' => '#0d1117', 'surface' => $surf, 'surface-2' => '#1c2129', 'border' => '#2d333b', 'border-strong' => '#444c56',
                'text' => '#e6edf3', 'text-2' => '#b6c2cf', 'muted' => '#8b949e', 'empty' => '#2d333b', 'row-hover' => '#1c2129',
                'shadow' => '0 1px 2px rgba(0,0,0,.4)', 'shadow-lg' => '0 12px 28px -6px rgba(0,0,0,.6), 0 4px 10px -4px rgba(0,0,0,.45)',
                'ink-bg' => '#e6edf3', 'ink-bg-hover' => '#c9d1d9', 'on-ink' => '#0d1117', 'overlay' => 'rgba(1,4,9,.66)', 'glass' => 'rgba(22,27,34,.96)',
            );
        }
        $soft = $dark ? 0.18 : 0.09; $line = $dark ? 0.42 : 0.28; $tint = $dark ? 0.06 : 0.025;
        // warna utama
        if ($dark || (isset($t['aksen']) && $e['aksen'] !== '#1d6fe8')) {
            $c = $e['aksen']; $sf = gx_mix($c, $surf, $soft);
            $v += array(
                'primary' => $c, 'primary-hover' => $dark ? gx_mix($c, '#ffffff', 0.85) : gx_mix($c, '#000000', 0.85), 'primary-soft' => $sf,
                'primary-text' => gx_readable($c, $surf, 4.5), 'primary-line' => gx_mix($c, $surf, $line), 'primary-ink' => gx_readable($c, $sf, 6),
                'primary-tint' => gx_mix($c, $surf, $tint), 'on-primary' => gx_on($c), 'focus' => '0 0 0 3px ' . gx_rgba($c, $dark ? '.45' : '.28'),
                'mark-1' => gx_mix($c, '#ffffff', 0.82), 'mark-3' => gx_mix($c, '#000000', 0.78),
            );
        }
        // warna status
        $solid = array('ok' => '#34a853', 'pr' => '#f79009');
        foreach (gx_tema_status() as $k => $d) {
            $custom = isset($t[$k]) && $e[$k] !== $d[1];
            if (!$dark && !$custom) { continue; }
            $c = $e[$k]; $sf = gx_mix($c, $surf, $soft);
            $v[$k] = $c; $v[$k . '-soft'] = $sf; $v[$k . '-text'] = gx_readable($c, $surf, 4.5); $v[$k . '-line'] = gx_mix($c, $surf, $line);
            if ($k !== 'pending') { $v[$k . '-ink'] = gx_readable($c, $sf, 6); $v[$k . '-tint'] = gx_mix($c, $surf, $tint); }
            if ($k === 'ok' || $k === 'nok') { $v['on-' . $k] = gx_on($c); }
            if (isset($solid[$k])) { $sc = $custom ? $c : $solid[$k]; $v[$k . '-solid'] = $sc; $v['on-' . $k . '-solid'] = $custom ? gx_on($sc) : '#fff'; }
        }
        if (!$v) { return ''; }
        $css = '';
        foreach ($v as $k => $x) { $css .= '--gc-' . $k . ':' . $x . ';'; }
        // "html " = lebih kuat dari token bawaan (checklist-genc.css bisa dimuat ulang di tengah halaman)
        return 'html .genc,html .genc-toast,html .gs-app{' . $css . '}' . ($dark ? 'html[data-gx-tema="gelap"]{color-scheme:dark;background:#0d1117}' : '');
    }

    /** Ringkasan tema untuk riwayat / Simpan. */
    function gx_tema_desc($k, $v) {
        if ($v === null) { $st = gx_tema_status(); return ($k === 'dasar' ? 'Dasar' : ($k === 'aksen' ? 'Warna utama' : 'Warna ' . (isset($st[$k]) ? $st[$k][0] : $k))) . ' kembali ke bawaan'; }
        if ($k === 'dasar') { return 'Dasar tampilan: ' . ($v === 'gelap' ? 'Gelap' : 'Terang'); }
        if ($k === 'aksen') { $a = gx_tema_aksen(); return 'Warna utama: ' . (isset($a[$v]) ? $a[$v][0] : $v); }
        $st = gx_tema_status();
        return 'Warna ' . (isset($st[$k]) ? $st[$k][0] : $k) . ': ' . $v;
    }

    /** Ringkasan 1 spek susunan: (urutan, 2 disembunyikan, lebar, posisi bebas). */
    function gx_susunan_desc($y) {
        $p = array();
        if (!empty($y['o'])) { $p[] = 'urutan'; }
        if (!empty($y['h'])) { $p[] = count($y['h']) . ' disembunyikan'; }
        if (!empty($y['w'])) { $p[] = 'lebar'; }
        if (!empty($y['f'])) { $p[] = 'posisi bebas'; }
        if (!$p && !empty($y['a'])) { $p[] = 'susunan awal di halaman ini'; }
        return $p ? ' (' . implode(', ', $p) . ')' : '';
    }

    /** Nama tempat sebuah kunci teks untuk manusia. */
    function gx_where($k, $v) {
        if (strpos($k, 'g|') === 0) { return 'Semua halaman'; }
        if (strpos($k, 'p|') === 0) { $p = explode('|', $k); return gx_where_page($p[1]); }
        if (strpos($k, 'v|') === 0) { $p = explode('|', $k); return gx_where_view($p[1]); }
        return '?';
    }

    function gx_page_names() {
        return array(
            'home' => 'Home', 'index' => 'Masuk / Daftar', 'mesin' => 'Mesin & Unit', 'app_logs' => 'App Logs', 'users' => 'Users',
            'roles' => 'Roles', 'role_permissions' => 'Role Permissions', 'master_select' => 'Master Select', 'account' => 'Akun saya',
            'tampilan' => 'Riwayat tampilan', 'info' => 'Halaman info',
        );
    }

    function gx_where_page($p) {
        $p = (string) $p;
        if (strpos($p, 'view:') === 0) { return gx_where_view(substr($p, 5)); }
        list($pg, $ac) = array_pad(explode('/', $p, 2), 2, 'index');
        $names = gx_page_names();
        if ($pg === 'palletmover' && $ac === 'approval') { return 'Approval'; }
        if ($pg === 'palletmover' && $ac === 'uncompleted') { return 'NOK History'; }
        if (isset($names[$pg])) { $n = $names[$pg]; }
        else {
            $n = $pg;
            if (function_exists('genc_reg_folder_of_page') || (include_once __DIR__ . '/genc_registry.php')) {
                try { $f = genc_reg_folder_of_page($pg); if ($f) { $all = genc_reg_builtin_folders(); if (isset($all[$f])) { $n = $all[$f]['title']; } } elseif (($m = genc_reg_page($pg)) && isset($m['nama'])) { $n = $m['nama']; } } catch (\Throwable $e) { }
            }
        }
        $act = array('index' => '', 'add' => ' (isi)', 'edit' => ' (ubah)', 'view' => ' (detail)');
        return $n . (isset($act[$ac]) ? $act[$ac] : ' (' . $ac . ')');
    }

    function gx_where_view($rel) {
        $map = array(
            'partials/_shared/genc/list.php' => 'Semua halaman mesin (daftar)', 'partials/_shared/genc/add.php' => 'Semua halaman mesin (isi)',
            'partials/_shared/genc/view.php' => 'Semua halaman mesin (detail)', 'partials/_shared/genc/hub.php' => 'Approval & NOK History',
            'partials/_shared/genc/_extra_fields.php' => 'Semua halaman mesin (isi)',
            'partials/appheader.php' => 'Topbar & sidebar', 'partials/appfooter.php' => 'Footer', 'layouts/main_layout.php' => 'Kerangka halaman',
            'partials/home/index.php' => 'Home', 'partials/mesin/index.php' => 'Mesin & Unit', 'partials/index/index.php' => 'Halaman masuk',
            'partials/index/login.php' => 'Halaman masuk', 'partials/index/register.php' => 'Halaman daftar', 'partials/app_logs/list.php' => 'App Logs',
            'partials/users/list.php' => 'Users', 'partials/users/form.php' => 'Users (form)', 'partials/roles/list.php' => 'Roles',
            'partials/role_permissions/list.php' => 'Role Permissions', 'partials/master_select/list.php' => 'Master Select',
            'partials/account/view.php' => 'Akun saya', 'partials/tampilan/index.php' => 'Riwayat tampilan',
            'toast' => 'Pesan (toast)', 'partials/_shared/genc_helpers.php' => 'Semua halaman mesin (label status)', 'partials/info/index.php' => 'Halaman info',
        );
        if (isset($map[$rel])) { return $map[$rel]; }
        if (strpos($rel, 'partials/errors/') === 0) { return 'Halaman error'; }
        return $rel;
    }

    /** Daftar versi (terbaru dulu). */
    function gx_versions() {
        $pdo = gx_db();
        if (!$pdo) { return array(); }
        try { return $pdo->query("SELECT id, versi, sekarang, ringkasan, meta, halaman, created_at, created_by FROM genc_tampilan ORDER BY versi DESC")->fetchAll(); }
        catch (\Throwable $e) { return array(); }
    }
}
