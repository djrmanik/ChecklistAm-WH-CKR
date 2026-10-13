<?php
/**
 * ============================================================================
 *  CADANGAN SEBELUM HAPUS PERMANEN (Mesin & Unit)          [GENC-13OKT26]
 *
 *  Sebelum mesin / unit di Arsip dihapus permanen, MesinController memanggil
 *  genc_cad_build($plan). Hasilnya 1 file .zip di uploads/cadangan_hapus/:
 *
 *    BACA-SAYA.txt          isi cadangan + cara memulihkan
 *    pemulihan.sql          dijalankan developer di phpMyAdmin -> mesin / unit kembali ke Arsip,
 *                           lengkap dengan tabel, data checklist, pengaturan, isi, hak akses, riwayat
 *    data/<nama>.xlsx       semua checklist mentah (semua kolom), 1 sheet per tabel / unit
 *    report-pdf/<unit>/...  report PDF resmi tiap unit per bulan yang ada datanya
 *    foto/...               foto item & gambar report (salin ke uploads/mesin/ saat memulihkan)
 *
 *  + <id>.json di sebelahnya (ringkasan untuk daftar "Cadangan hapus permanen").
 *  Folder dilindungi .htaccess (tidak bisa dibuka lewat URL); unduh hanya lewat
 *  menu Mesin & Unit (hak akses Hapus) - lihat MesinController::index().
 *  Kalau cadangan gagal dibuat -> penghapusan DIBATALKAN (tidak ada yang dihapus).
 *  Tanpa ekstensi zip: cadangan tetap dibuat sebagai folder (file bisa diunduh satu per satu).
 * ============================================================================
 */
require_once __DIR__ . '/genc_registry.php';

if (!function_exists('genc_cad_dir')) {

    /**
     * [GENC-13OKT26-FIX] Folder cadangan yang mungkin dipakai, urut. Yang pertama = folder utama. Yang kedua = cadangan
     * kalau folder utama ADA tapi tidak boleh ditulis server (mis. dibuat dari ekstrak zip di Mac: milik user Mac, izin
     * 755, sedangkan Apache XAMPP jalan sebagai "daemon"). uploads/mesin selalu bisa ditulis (tempat foto item).
     */
    function genc_cad_dirs() {
        return array('uploads/cadangan_hapus', 'uploads/mesin/_cadangan_hapus');
    }

    /** Siapkan 1 folder cadangan: buat kalau belum ada, coba buka izinnya, pasang pelindung. @return bool bisa ditulis */
    function genc_cad_prepare($rel) {
        $abs = ROOT . $rel;
        if (!is_dir($abs)) { @mkdir($abs, 0777, true); }
        if (!is_dir($abs)) { return false; }
        // tes tulis sungguhan (is_writable tidak bisa dipercaya di Windows)
        $probe = $abs . '/.tulis-' . substr(md5(uniqid('', true)), 0, 6);
        if (@file_put_contents($probe, '1') === false) { @chmod($abs, 0777); if (@file_put_contents($probe, '1') === false) { return false; } }
        @unlink($probe);
        if (!is_file($abs . '/.htaccess')) {
            @file_put_contents($abs . '/.htaccess', "# [GENC-13OKT26] cadangan hapus permanen: tidak boleh dibuka lewat URL (unduh lewat menu Mesin & Unit)\n"
                . "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n");
        }
        if (!is_file($abs . '/index.html')) { @file_put_contents($abs . '/index.html', ''); }
        return true;
    }

    /** Folder cadangan yang bisa ditulis (relatif ROOT), dibuat + dilindungi. '' kalau tidak ada yang bisa. */
    function genc_cad_dir() {
        foreach (genc_cad_dirs() as $rel) { if (genc_cad_prepare($rel)) { return $rel; } }
        return '';
    }

    /** Potongan nama file yang aman (huruf, angka, - _). */
    function genc_cad_slug($s, $max = 60) {
        $s = trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $s), '-');
        return $s === '' ? 'mesin' : substr($s, 0, $max);
    }

    /** id cadangan sah? (YYYYmmdd-HHiiss_slug_xxxx) */
    function genc_cad_id_ok($id) {
        return is_string($id) && preg_match('/^\d{8}-\d{6}_[A-Za-z0-9-]{1,60}_[a-f0-9]{4}$/', $id);
    }

    /** Semua cadangan, terbaru dulu: [id => meta]. */
    function genc_cad_list() {
        $out = array();
        foreach (genc_cad_dirs() as $rel) {   // tidak membuat folder (hanya dibaca); semua folder cadangan dibaca
            if (!is_dir(ROOT . $rel)) { continue; }
            foreach ((array) glob(ROOT . $rel . '/*.json') as $f) {
                $id = basename($f, '.json');
                if (!genc_cad_id_ok($id) || isset($out[$id])) { continue; }
                $m = json_decode((string) @file_get_contents($f), true);
                if (!is_array($m)) { continue; }
                $m['id'] = $id;
                $m['dir'] = $rel;   // folder tempat cadangan ini disimpan
                $m['zip_ok'] = !empty($m['zip']) && is_file(ROOT . $rel . '/' . basename($m['zip']));
                $m['dir_ok'] = is_dir(ROOT . $rel . '/' . $id);
                $out[$id] = $m;
            }
        }
        krsort($out);
        return $out;
    }

    function genc_cad_get($id) {
        if (!genc_cad_id_ok($id)) { return null; }
        $all = genc_cad_list();
        return isset($all[$id]) ? $all[$id] : null;
    }

    /** Hapus folder (hanya di dalam uploads/). */
    function genc_cad_rrmdir($abs) {
        $abs = rtrim(str_replace('\\', '/', $abs), '/');
        if ($abs === '' || strpos($abs, str_replace('\\', '/', ROOT) . 'uploads/') !== 0 || strpos($abs, '..') !== false || !is_dir($abs)) { return; }
        foreach ((array) scandir($abs) as $x) {
            if ($x === '.' || $x === '..') { continue; }
            $p = $abs . '/' . $x;
            if (is_dir($p) && !is_link($p)) { genc_cad_rrmdir($p); } else { @unlink($p); }
        }
        @rmdir($abs);
    }

    /** Salin isi folder (rekursif), tanpa folder kolase (cache gambar report, dibuat ulang otomatis). @return int jumlah file */
    function genc_cad_copy_dir($src, $dst) {
        $n = 0;
        if (!is_dir($src)) { return 0; }
        if (!is_dir($dst)) { @mkdir($dst, 0775, true); }
        foreach ((array) scandir($src) as $x) {
            if ($x === '.' || $x === '..' || $x === 'kolase') { continue; }
            if (is_dir($src . '/' . $x)) { $n += genc_cad_copy_dir($src . '/' . $x, $dst . '/' . $x); }
            elseif (@copy($src . '/' . $x, $dst . '/' . $x)) { $n++; }
        }
        return $n;
    }

    /** Nilai SQL. */
    function genc_cad_q($pdo, $v) {
        if ($v === null) { return 'NULL'; }
        if (is_int($v) || is_float($v)) { return (string) $v; }
        return $pdo->quote((string) $v);
    }

    /** INSERT IGNORE untuk banyak baris ($set = kolom yang ditimpa, mis. arsip = 1). */
    function genc_cad_sql_insert($pdo, $table, $rows, $set = array()) {
        if (empty($rows)) { return ''; }
        $out = '';
        $cols = array_keys(array_merge($rows[0], $set));
        foreach (array_chunk($rows, 100) as $chunk) {
            $vals = array();
            foreach ($chunk as $r) {
                $r = array_merge($r, $set);
                $v = array();
                foreach ($cols as $c) { $v[] = genc_cad_q($pdo, array_key_exists($c, $r) ? $r[$c] : null); }
                $vals[] = '(' . implode(', ', $v) . ')';
            }
            $out .= 'INSERT IGNORE INTO `' . $table . '` (`' . implode('`, `', $cols) . "`) VALUES\n  " . implode(",\n  ", $vals) . ";\n";
        }
        return $out;
    }

    /** CREATE TABLE IF NOT EXISTS dari SHOW CREATE TABLE. */
    function genc_cad_sql_create($pdo, $table) {
        $r = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '', $table) . '`')->fetch(PDO::FETCH_NUM);
        $sql = isset($r[1]) ? (string) $r[1] : '';
        $sql = preg_replace('/^CREATE TABLE /', 'CREATE TABLE IF NOT EXISTS ', $sql);
        $sql = preg_replace('/ AUTO_INCREMENT=\d+/', '', $sql);
        return $sql . ";\n";
    }

    /** Excel (beberapa sheet): .xlsx kalau ada ekstensi zip, selain itu Excel 2003 XML (.xls). @return string nama file */
    function genc_cad_excel($abs_base, $sheets) {
        // [GENC-13OKT26b] file sementara XLSXWriter di folder cadangan (sudah pasti bisa ditulis), bukan folder temp sistem:
        // di XAMPP Mac folder temp sistem bisa tidak boleh ditulis Apache -> tempnam() = false -> "Path cannot be empty".
        // Kalau .xlsx tetap gagal -> jatuh ke Excel 2003 XML (.xls) di bawah, cadangan tidak batal karena Excel.
        if (class_exists('ZipArchive') && is_file(ROOT . 'libs/XLSXWriter.php')) {
          try {
            require_once ROOT . 'libs/XLSXWriter.php';
            $tmp = dirname($abs_base) . '/_tmp';
            if (!is_dir($tmp)) { @mkdir($tmp, 0777, true); }
            $w = new XLSXWriter();
            $w->setTempDir(is_dir($tmp) ? $tmp : dirname($abs_base));
            $w->setAuthor(defined('USER_NAME') ? USER_NAME : '');
            foreach ($sheets as $sn => $s) {
                $hdr = array();
                foreach ($s['head'] as $h) { $k = (string) $h; while (isset($hdr[$k])) { $k .= ' '; } $hdr[$k] = 'string'; }
                $w->writeSheetHeader($sn, $hdr, array('freeze_rows' => 1));
                foreach ($s['rows'] as $row) { $w->writeSheetRow($sn, array_map(function ($v) { return $v === null ? '' : (string) $v; }, array_values($row))); }
            }
            $w->writeToFile($abs_base . '.xlsx');
            unset($w); if (is_dir($tmp)) { @array_map('unlink', (array) glob($tmp . '/*')); @rmdir($tmp); }
            if (is_file($abs_base . '.xlsx') && filesize($abs_base . '.xlsx') > 0) { return basename($abs_base) . '.xlsx'; }
          } catch (\Throwable $e) { }
            unset($w); if (isset($tmp) && is_dir($tmp)) { @array_map('unlink', (array) glob($tmp . '/*')); @rmdir($tmp); }
            @unlink($abs_base . '.xlsx');
        }
        $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<?mso-application progid="Excel.Sheet"?>' . "\n"
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Styles><Style ss:ID="h"><Font ss:Bold="1"/></Style></Styles>';
        foreach ($sheets as $sn => $s) {
            $x .= '<Worksheet ss:Name="' . htmlspecialchars($sn, ENT_QUOTES, 'UTF-8') . '"><Table><Row>';
            foreach ($s['head'] as $h) { $x .= '<Cell ss:StyleID="h"><Data ss:Type="String">' . htmlspecialchars((string) $h, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</Data></Cell>'; }
            $x .= '</Row>';
            foreach ($s['rows'] as $row) {
                $x .= '<Row>';
                foreach ($row as $v) { $x .= '<Cell><Data ss:Type="String">' . htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</Data></Cell>'; }
                $x .= '</Row>';
            }
            $x .= '</Table></Worksheet>';
        }
        file_put_contents($abs_base . '.xls', $x . '</Workbook>');
        return basename($abs_base) . '.xls';
    }

    /** Nama sheet Excel yang sah & unik (maks 31 huruf). */
    function genc_cad_sheet_name($name, $taken) {
        $n = trim(preg_replace('/[\\\\\/\?\*\[\]:]+/', ' ', (string) $name));
        $n = substr($n === '' ? 'Data' : $n, 0, 28);
        $out = $n; $i = 2;
        while (isset($taken[strtolower($out)])) { $out = substr($n, 0, 25) . ' (' . $i++ . ')'; }
        return $out;
    }

    /** Jumlah foto di folder foto mesin (tanpa cache kolase). */
    function genc_cad_count_photos($abs) {
        $n = 0;
        foreach (genc_cad_files($abs) as $f) { if (strpos($f, 'kolase/') !== 0) { $n++; } }
        return $n;
    }

    /** Semua file di folder (relatif terhadap $base), urut. */
    function genc_cad_files($abs, $base = '') {
        $out = array();
        if (!is_dir($abs)) { return $out; }
        foreach ((array) scandir($abs) as $x) {
            if ($x === '.' || $x === '..') { continue; }
            $rel = $base === '' ? $x : $base . '/' . $x;
            if (is_dir($abs . '/' . $x)) { $out = array_merge($out, genc_cad_files($abs . '/' . $x, $rel)); }
            else { $out[] = $rel; }
        }
        return $out;
    }

    /**
     * Buat cadangan. $plan (dari MesinController::mesin_purge_plan()):
     *   kind 'mesin'|'unit', title, what (teks pendek), builtin (bool), checklist (int)
     *   sources  [array(page, table, unit_field, norms (null = semua baris), label, mode 'drop'|'rows'|'keep',
 *            pdf_units [array(slug, label, norms|null)] report PDF per unit, excel (bool) ikut sheet Excel)]
     *   registry [tabel => array(rows, set)]  baris registry yang ikut dihapus (ditulis balik di pemulihan.sql)
     *   perms    [baris role_permissions]
     *   sql_after [string SQL tambahan, mis. UPDATE genc_folder SET arsip = 1]
     *   photo_dirs [path relatif ROOT]
     * @return array('ok' => bool, 'msg' => string, 'id' => string, 'meta' => array, 'rows' => [i => ids])
     */
    function genc_cad_build($plan) {
        $pdo = genc_reg_db();
        if (!$pdo) { return array('ok' => false, 'msg' => 'Koneksi database gagal.'); }
        $rel = genc_cad_dir();
        if ($rel === '') { return array('ok' => false, 'msg' => 'Server tidak diizinkan menulis ke folder uploads/cadangan_hapus maupun uploads/mesin (izin folder). Mac: Terminal -> chmod -R 777 <folder app>/uploads. Windows: klik kanan folder uploads -> Properties -> Security -> beri izin Write.'); }
        if (function_exists('set_time_limit')) { @set_time_limit(0); }
        @ignore_user_abort(true);
        $lim = ini_get('memory_limit'); $num = (int) $lim;
        if ($num !== -1 && $num < 512 && in_array(strtoupper(substr(trim((string) $lim), -1)), array('M'), true)) { @ini_set('memory_limit', '512M'); }

        $id = date('Ymd-His') . '_' . genc_cad_slug($plan['title'], 40) . '_' . substr(md5(uniqid('', true)), 0, 4);
        $abs = ROOT . $rel . '/' . $id;
        if (!@mkdir($abs, 0777, true)) { return array('ok' => false, 'msg' => 'Folder cadangan ' . $rel . '/' . $id . ' tidak bisa dibuat (izin folder).'); }
        $fail = function ($msg) use ($abs) { genc_cad_rrmdir($abs); return array('ok' => false, 'msg' => $msg); };
        $who = defined('USER_NAME') ? USER_NAME : '';
        $stamp = date('Y-m-d H:i:s');
        try {
            // ---------------------------------------------------------- 1. data mentah per sumber
            $rows_by = array(); $ids_by = array(); $months_by = array(); $n_rows = 0;
            foreach ($plan['sources'] as $i => $s) {
                $all = $pdo->query('SELECT * FROM `' . str_replace('`', '', $s['table']) . '` ORDER BY `id`')->fetchAll(PDO::FETCH_ASSOC);
                $keep = array();
                foreach ($all as $r) {
                    if ($s['norms'] !== null) {
                        $u = isset($r[$s['unit_field']]) ? checklist_norm_unit($r[$s['unit_field']]) : '';
                        if (!in_array($u, $s['norms'], true)) { continue; }
                    }
                    $keep[] = $r;
                }
                $rows_by[$i] = $keep;
                $ids_by[$i] = array_map(function ($r) { return $r['id']; }, $keep);
                if (!isset($s['excel']) || $s['excel']) { $n_rows += count($keep); }
                // bulan yang ada datanya, per unit report
                $months_by[$i] = array();
                foreach ((array) $s['pdf_units'] as $j => $pu) {
                    $m = array();
                    foreach ($keep as $r) {
                        if ($pu['norms'] !== null) {
                            $un = isset($r[$s['unit_field']]) ? checklist_norm_unit($r[$s['unit_field']]) : '';
                            if (!in_array($un, $pu['norms'], true)) { continue; }
                        }
                        $d = isset($r['date_created']) ? substr((string) $r['date_created'], 0, 7) : '';
                        if (preg_match('/^(\d{4})-(\d{2})$/', $d)) { $m[$d] = true; }
                    }
                    ksort($m);
                    $months_by[$i][$j] = array_keys($m);
                }
            }

            // ---------------------------------------------------------- 2. Excel
            @mkdir($abs . '/data', 0775, true);
            $sheets = array(); $taken = array();
            $info = array(array('Cadangan hapus permanen - Checklist AM Warehouse CKR'), array($plan['what']), array('Dibuat ' . $stamp . ' oleh ' . $who), array(''),
                array('Sheet', 'Tabel', 'Unit', 'Jumlah checklist', 'Setelah dihapus'));
            $info_rows = array();
            foreach ($plan['sources'] as $i => $s) {
                if (isset($s['excel']) && !$s['excel']) { continue; }
                $sn = genc_cad_sheet_name($s['label'] !== '' ? $s['label'] : $s['table'], $taken);
                $taken[strtolower($sn)] = true;
                $head = $rows_by[$i] ? array_keys($rows_by[$i][0]) : array_map(function ($c) { return $c['Field']; }, $pdo->query('SHOW COLUMNS FROM `' . str_replace('`', '', $s['table']) . '`')->fetchAll(PDO::FETCH_ASSOC));
                $sheets[$sn] = array('head' => $head, 'rows' => $rows_by[$i]);
                $info_rows[] = array($sn, $s['table'], $s['label'], (string) count($rows_by[$i]),
                    $s['mode'] === 'drop' ? 'tabel dihapus' : ($s['mode'] === 'rows' ? 'baris unit ini dihapus' : 'tetap di tabel asli (tidak dibuka lagi dari aplikasi)'));
            }
            $sheets = array_merge(array('Info' => array('head' => array('Info'), 'rows' => array_merge(array_slice($info, 1), $info_rows))), $sheets);
            $xls = genc_cad_excel($abs . '/data/' . genc_cad_slug($plan['title']) . '-data-checklist', $sheets);

            // ---------------------------------------------------------- 3. report PDF tiap unit per bulan
            require_once ROOT . 'app/controllers/_base/GencBackupReader.php';
            $reader = new GencBackupReader();
            $n_pdf = 0;
            foreach ($plan['sources'] as $i => $s) {
                foreach ((array) $s['pdf_units'] as $j => $pu) {
                    $dir = $abs . '/report-pdf/' . genc_cad_slug($pu['label'] !== '' ? $pu['label'] : $s['page']);
                    foreach ($months_by[$i][$j] as $ym) {
                        list($y, $mo) = explode('-', $ym);
                        $res = $reader->pdf($s['page'], $pu['slug'], (int) $mo, (int) $y);
                        if ($res === null) { return $fail('Report PDF ' . $pu['label'] . ' ' . $ym . ' tidak bisa dibuat (unit tidak dikenali halaman ' . $s['page'] . ').'); }
                        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                        if (@file_put_contents($dir . '/' . $ym . '-' . genc_cad_slug($res['title'], 80) . '.pdf', $res['bytes']) === false) { return $fail('Report PDF ' . $ym . ' tidak bisa ditulis.'); }
                        $n_pdf++;
                    }
                }
            }

            // ---------------------------------------------------------- 4. foto
            $n_foto = 0;
            foreach ($plan['photo_dirs'] as $pd) {
                $pd = trim(str_replace('\\', '/', (string) $pd), '/');
                if (strpos($pd, 'uploads/mesin/') !== 0 || strpos($pd, '..') !== false) { continue; }
                $n_foto += genc_cad_copy_dir(ROOT . $pd, $abs . '/foto/' . substr($pd, strlen('uploads/mesin/')));
            }

            // ---------------------------------------------------------- 5. pemulihan.sql
            $sql = "-- ============================================================================\n"
                . "--  PEMULIHAN: " . preg_replace('/[\r\n]+/', ' ', $plan['what']) . "\n"
                . "--  Dihapus permanen " . $stamp . " oleh " . $who . " lewat menu Mesin & Unit (Checklist AM Warehouse CKR)\n"
                . "--  Cara memulihkan (developer): phpMyAdmin -> database " . (defined('DB_NAME') ? DB_NAME : 'web_wh') . " -> tab SQL / Import -> jalankan file ini.\n"
                . "--  Lalu salin isi folder foto/ di zip ini ke uploads/mesin/ (kalau ada).\n"
                . "--  Hasil: " . ($plan['kind'] === 'unit' ? 'unit' : 'mesin') . " kembali ke ARSIP di Mesin & Unit (tombol Pulihkan seperti biasa).\n"
                . "--  Aman dijalankan 2x (INSERT IGNORE / IF NOT EXISTS).\n"
                . "-- ============================================================================\n\nSET NAMES utf8mb4;\n\n";
            foreach ($plan['sources'] as $i => $s) {
                if ($s['mode'] === 'drop') {
                    $sql .= "-- tabel data " . $s['table'] . " (" . count($rows_by[$i]) . " checklist)\n" . genc_cad_sql_create($pdo, $s['table']) . genc_cad_sql_insert($pdo, $s['table'], $rows_by[$i]) . "\n";
                } elseif ($s['mode'] === 'rows') {
                    $sql .= "-- checklist unit " . $s['label'] . " di tabel " . $s['table'] . " (" . count($rows_by[$i]) . ")\n" . genc_cad_sql_insert($pdo, $s['table'], $rows_by[$i]) . "\n";
                } elseif (!isset($s['excel']) || $s['excel']) {
                    $sql .= "-- " . $s['table'] . ($s['label'] !== '' ? ' / ' . $s['label'] : '') . ": " . count($rows_by[$i]) . " checklist TIDAK dihapus (tabel asli tetap)\n\n";
                }
            }
            foreach ($plan['registry'] as $t => $x) {
                if (empty($x['rows'])) { continue; }
                $sql .= "-- registry " . $t . "\n" . genc_cad_sql_insert($pdo, $t, $x['rows'], isset($x['set']) ? $x['set'] : array()) . "\n";
            }
            if (!empty($plan['perms'])) {
                $sql .= "-- hak akses (role_permissions)\n";
                foreach ($plan['perms'] as $r) {
                    $sql .= 'INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`) SELECT ' . genc_cad_q($pdo, (int) $r['role_id']) . ', ' . genc_cad_q($pdo, $r['page_name']) . ', ' . genc_cad_q($pdo, $r['action_name'])
                        . ' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE `role_id` = ' . genc_cad_q($pdo, (int) $r['role_id']) . ' AND `page_name` = ' . genc_cad_q($pdo, $r['page_name']) . ' AND `action_name` = ' . genc_cad_q($pdo, $r['action_name']) . ");\n";
                }
                $sql .= "\n";
            }
            foreach ($plan['sql_after'] as $x) { $sql .= $x . "\n"; }
            if (@file_put_contents($abs . '/pemulihan.sql', $sql) === false) { return $fail('File pemulihan.sql tidak bisa ditulis.'); }

            // ---------------------------------------------------------- 6. BACA-SAYA.txt
            $txt = "CADANGAN HAPUS PERMANEN - Checklist AM Warehouse CKR\r\n"
                . str_repeat('=', 60) . "\r\n"
                . $plan['what'] . "\r\n"
                . "Dihapus: " . $stamp . " oleh " . $who . "\r\n\r\n"
                . "ISI\r\n"
                . "  data/" . $xls . "   semua checklist mentah (" . $n_rows . " baris), 1 sheet per tabel / unit\r\n"
                . "  report-pdf/          " . $n_pdf . " report PDF resmi (tiap unit per bulan yang ada datanya)\r\n"
                . "  foto/                " . $n_foto . " foto item & gambar report\r\n"
                . "  pemulihan.sql        mengembalikan " . ($plan['kind'] === 'unit' ? 'unit' : 'mesin') . " ini ke Arsip (tabel, data, pengaturan, isi, hak akses, riwayat)\r\n\r\n"
                . "CARA MEMULIHKAN (developer)\r\n"
                . "  1. phpMyAdmin -> database " . (defined('DB_NAME') ? DB_NAME : 'web_wh') . " -> tab Import -> pilih pemulihan.sql -> Go.\r\n"
                . "  2. Salin isi folder foto/ ke folder aplikasi uploads/mesin/ (kalau ada).\r\n"
                . "  3. Buka Admin & sistem -> Mesin & Unit -> bagian Arsip -> Pulihkan.\r\n";
            if (!empty($plan['note'])) { $txt .= "\r\nCATATAN\r\n  " . $plan['note'] . "\r\n"; }
            @file_put_contents($abs . '/BACA-SAYA.txt', $txt);

            $meta = array(
                'title' => $plan['title'], 'kind' => $plan['kind'], 'what' => $plan['what'], 'folder' => isset($plan['folder']) ? $plan['folder'] : '',
                'by' => $who, 'at' => $stamp, 'checklist' => $n_rows, 'pdf' => $n_pdf, 'foto' => $n_foto, 'excel' => 'data/' . $xls,
                'builtin' => !empty($plan['builtin']), 'zip' => '', 'size' => 0,
            );
            @file_put_contents($abs . '/info.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // ---------------------------------------------------------- 7. zip (+ cek bisa dibuka lagi)
            $files = genc_cad_files($abs);
            if (class_exists('ZipArchive')) {
                $zf = ROOT . $rel . '/' . $id . '.zip';
                $z = new ZipArchive();
                if ($z->open($zf, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { return $fail('File .zip cadangan tidak bisa dibuat.'); }
                foreach ($files as $f) { $z->addFile($abs . '/' . $f, $f); }
                if (!$z->close()) { @unlink($zf); return $fail('File .zip cadangan gagal ditulis.'); }
                $chk = new ZipArchive();
                if ($chk->open($zf) !== true || $chk->numFiles !== count($files) || $chk->locateName('pemulihan.sql') === false) { @unlink($zf); return $fail('File .zip cadangan rusak (gagal dicek ulang).'); }
                $chk->close();
                genc_cad_rrmdir($abs);
                $meta['zip'] = $id . '.zip';
                $meta['size'] = (int) @filesize($zf);
            } else {
                $meta['files'] = $files;
                $sz = 0; foreach ($files as $f) { $sz += (int) @filesize($abs . '/' . $f); }
                $meta['size'] = $sz;
            }
            if (@file_put_contents(ROOT . $rel . '/' . $id . '.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false) {
                if ($meta['zip'] !== '') { @unlink(ROOT . $rel . '/' . $meta['zip']); }
                return $fail('Ringkasan cadangan tidak bisa ditulis.');
            }
            return array('ok' => true, 'msg' => '', 'id' => $id, 'meta' => $meta, 'ids' => $ids_by);
        } catch (\Throwable $e) {
            return $fail($e->getMessage());
        }
    }

    /** Ukuran file yang enak dibaca. */
    function genc_cad_size($b) {
        $b = (int) $b;
        if ($b >= 1048576) { return number_format($b / 1048576, 1, ',', '.') . ' MB'; }
        if ($b >= 1024) { return number_format($b / 1024, 0, ',', '.') . ' KB'; }
        return $b . ' B';
    }
}
