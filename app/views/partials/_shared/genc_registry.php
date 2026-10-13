<?php
/**
 * ============================================================================
 *  REGISTRY MESIN & UNIT  (tambah mesin tanpa coding)     [GENC-06OKT26-MESIN]
 *
 *  Sumber data menu "Mesin & Unit" (MesinController). Dua tabel kecil:
 *    genc_mesin  jenis mesin BARU (folder baru di sidebar): nama, area, ikon, item checklist (JSON)
 *    genc_unit   unit BARU (tab baru) + catatan untuk unit awal (nonaktif / nama unit)
 *    genc_folder [GENC-08OKT26-MESIN] status mesin awal (aktif / ikon) - mesin awal = 6 mesin di kode
 *  Tabel dibuat oleh database/genc_06okt26.sql (atau otomatis saat menu
 *  Mesin & Unit pertama kali dibuka). Selama tabel belum ada / kosong, SEMUA
 *  fungsi di sini mengembalikan "tidak ada tambahan" -> app sama persis dengan
 *  sebelum fitur ini (dites: halaman mesin identik).
 *
 *  Tiga cara simpan (sesuai bentuk tabel lama, data lama TIDAK diubah):
 *   1. 'units'  - 1 tabel banyak unit (Pallet Mover, Forklift, AGV, jenis baru):
 *                 unit baru = baris genc_unit -> ikut daftar unit profil
 *                 (checklist_load_machine) -> tab, report, Home, Approval otomatis.
 *   2. 'tables' - 1 tabel per unit (Dumping KIR3/6/7, Geprek, Conveyor):
 *                 unit baru = tabel BARU hasil salinan struktur tabel bawaan
 *                 (CREATE TABLE ... LIKE), route baru am_<...> dilayani
 *                 GencDynamicMachine, tampil sebagai tab di halaman mesinnya.
 *   3. jenis baru - tabel am_<slug> dibuat dari daftar item (kolom item_NN),
 *                 profil report dibentuk dari genc_mesin.items.
 *
 *  Halaman baru (am_*) mendapat hak akses SALINAN dari halaman acuan
 *  (role_permissions), lalu bisa diatur di Role Permissions seperti biasa.
 * ============================================================================
 */
require_once __DIR__ . '/checklist_report_engine.php';

if (!function_exists('genc_reg_db')) {

    /** Awalan route & tabel buatan registry. */
    define('GENC_REG_PREFIX', 'am_');
    /** Batas item per jenis mesin baru. */
    define('GENC_REG_MAX_ITEMS', 60);
    /** [GENC-07OKT26-FOTO] Batas foto per item checklist & foto tambahan per gambar bagian mesin. */
    define('GENC_REG_MAX_FOTO', 6);
    define('GENC_REG_MAX_EXTRA', 12);
    /** [GENC-08OKT26-MESIN] Label "BARU" di menu Mesin & Unit tampil selama N hari sejak dibuat. */
    define('GENC_REG_NEW_DAYS', 3);
    /**
     * [GENC-13OKT26] Nilai kolom `arsip`: 0 = biasa, 1 = di Arsip (bisa dipulihkan), 2 = DIHAPUS PERMANEN.
     * 2 hanya dipakai untuk mesin & unit AWAL (dari kode): halamannya tetap ada di kode & tabel aslinya tetap,
     * jadi catatannya disimpan sebagai "nisan" supaya tidak pernah tampil lagi. Mesin & unit buatan menu yang
     * dihapus permanen benar-benar hilang (baris registry, tabel / data, foto, hak akses).
     * Semua pemeriksaan lama `!empty(arsip)` tetap benar (2 = ikut tersembunyi seperti arsip).
     */
    define('GENC_REG_HAPUS', 2);

    /** Koneksi PDO sendiri (dibaca sebelum controller ada, mis. di Router). null kalau DB tidak tersedia. */
    function genc_reg_db() {
        static $pdo = false;
        if ($pdo !== false) { return $pdo; }
        $pdo = null;
        if (!defined('DB_HOST') || !defined('DB_NAME')) { return null; }
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . (defined('DB_PORT') && DB_PORT !== '' ? ';port=' . DB_PORT : '')
                 . ';charset=' . (defined('DB_CHARSET') && DB_CHARSET !== '' ? DB_CHARSET : 'utf8');
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
        } catch (\Throwable $e) {
            $pdo = null;
        }
        return $pdo;
    }

    /** SQL pembuat 2 tabel registry (sama dengan database/genc_06okt26.sql). */
    function genc_reg_schema_sql() {
        return array(
            "CREATE TABLE IF NOT EXISTS `genc_mesin` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `page` VARCHAR(64) NOT NULL,
                `nama` VARCHAR(100) NOT NULL,
                `area` VARCHAR(100) NOT NULL DEFAULT '',
                `ikon` VARCHAR(40) NOT NULL DEFAULT 'fa-cube',
                `pelaksanaan` VARCHAR(150) NOT NULL DEFAULT '',
                `items` MEDIUMTEXT NULL,
                `item_seq` INT NOT NULL DEFAULT 0,
                `aktif` TINYINT(1) NOT NULL DEFAULT 1,
                `urut` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL, `created_by` VARCHAR(100) NULL,
                `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
                PRIMARY KEY (`id`), UNIQUE KEY `uk_genc_mesin_page` (`page`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS `genc_unit` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `folder` VARCHAR(64) NOT NULL,
                `profile` VARCHAR(64) NOT NULL DEFAULT '',
                `page` VARCHAR(64) NOT NULL,
                `slug` VARCHAR(100) NOT NULL DEFAULT '',
                `label` VARCHAR(100) NOT NULL DEFAULT '',
                `kode` VARCHAR(100) NOT NULL DEFAULT '',
                `alias` TEXT NULL,
                `builtin` TINYINT(1) NOT NULL DEFAULT 0,
                `aktif` TINYINT(1) NOT NULL DEFAULT 1,
                `urut` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL, `created_by` VARCHAR(100) NULL,
                `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
                PRIMARY KEY (`id`), KEY `ix_genc_unit_folder` (`folder`), KEY `ix_genc_unit_page` (`page`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // [GENC-06OKT26-ISI] isi checklist mesin bawaan yang diubah lewat menu + kunci (lock) per mesin
            "CREATE TABLE IF NOT EXISTS `genc_profil` (
                `profil` VARCHAR(64) NOT NULL,
                `items` MEDIUMTEXT NULL,
                `gambar` TEXT NULL,
                `item_seq` INT NOT NULL DEFAULT 0,
                `kunci` TINYINT(1) NULL,
                `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
                `kunci_at` DATETIME NULL, `kunci_by` VARCHAR(100) NULL,
                PRIMARY KEY (`profil`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // [GENC-08OKT26-MESIN] status mesin awal (Pallet Mover ... Conveyor): aktif / nonaktif + ikon sidebar
            "CREATE TABLE IF NOT EXISTS `genc_folder` (
                `folder` VARCHAR(64) NOT NULL,
                `ikon` VARCHAR(40) NULL,
                `aktif` TINYINT(1) NOT NULL DEFAULT 1,
                `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
                PRIMARY KEY (`folder`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // [GENC-08OKT26-P3] riwayat versi pengaturan tiap mesin (Mesin & Unit) - dipulihkan kapan saja
            "CREATE TABLE IF NOT EXISTS `genc_versi` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `folder` VARCHAR(64) NOT NULL,
                `versi` INT NOT NULL DEFAULT 1,
                `ringkasan` VARCHAR(255) NOT NULL DEFAULT '',
                `data` MEDIUMTEXT NULL,
                `created_at` DATETIME NULL, `created_by` VARCHAR(100) NULL,
                PRIMARY KEY (`id`), KEY `ix_genc_versi_folder` (`folder`, `versi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            // [GENC-08OKT26-P3] label tombol hasil cek (kode OK/NOK/PR di database & report TETAP)
            "CREATE TABLE IF NOT EXISTS `genc_pilihan` (
                `kode` VARCHAR(10) NOT NULL,
                `label` VARCHAR(40) NOT NULL DEFAULT '',
                `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
                PRIMARY KEY (`kode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        );
    }

    /** [GENC-08OKT26-P3] Kolom yang ditambah ke tabel registry yang sudah ada (install lama): tabel => [kolom => definisi]. */
    function genc_reg_schema_columns() {
        return array(
            'genc_folder' => array(
                'nama'        => "VARCHAR(100) NULL",
                'area'        => "VARCHAR(100) NULL",
                'pelaksanaan' => "VARCHAR(150) NULL",
                'arsip'       => "TINYINT(1) NOT NULL DEFAULT 0",
            ),
            'genc_mesin' => array('arsip' => "TINYINT(1) NOT NULL DEFAULT 0"),
            'genc_unit'  => array('arsip' => "TINYINT(1) NOT NULL DEFAULT 0"),
            // [GENC-13OKT26] penunjuk "versi sekarang" (Kembalikan = pindah penunjuk, bukan versi baru)
            'genc_versi' => array('sekarang' => "TINYINT(1) NOT NULL DEFAULT 0"),
        );
    }

    /** Buat tabel registry kalau belum ada. @return string '' = beres, selain itu pesan error */
    function genc_reg_ensure_tables() {
        $pdo = genc_reg_db();
        if (!$pdo) { return 'Koneksi database gagal.'; }
        try {
            foreach (genc_reg_schema_sql() as $sql) { $pdo->exec($sql); }
            foreach (genc_reg_schema_columns() as $t => $cols) {
                $have = array();
                foreach ($pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll() as $c) { $have[strtolower($c['Field'])] = true; }
                foreach ($cols as $c => $def) {
                    if (!isset($have[$c])) { $pdo->exec("ALTER TABLE `$t` ADD COLUMN `$c` $def"); }
                }
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
        genc_reg_data(true);
        return '';
    }

    /**
     * Isi registry, dibaca SEKALI per request (2 query kecil). Tabel belum ada -> 'ok' false, daftar kosong.
     * @param bool $reset baca ulang (setelah menulis)
     */
    function genc_reg_data($reset = false) {
        static $data = null;
        if ($reset) { $data = null; }
        if ($data !== null) { return $data; }
        $data = array('ok' => false, 'mesin' => array(), 'unit' => array(), 'profil' => array(), 'profil_ok' => false, 'folder' => array(), 'folder_ok' => false);
        $pdo = genc_reg_db();
        if (!$pdo) { return $data; }
        // [GENC-08OKT26-MESIN] tabel ke-4 juga dibaca terpisah (install lama tanpa genc_folder tetap jalan)
        try {
            foreach ($pdo->query("SELECT * FROM `genc_folder`")->fetchAll() as $r) { $data['folder'][$r['folder']] = $r; }
            $data['folder_ok'] = true;
        } catch (\Throwable $e) { $data['folder'] = array(); }
        // [GENC-06OKT26-ISI] tabel ke-3 dibaca terpisah: install yang belum punya genc_profil tetap jalan normal
        try {
            foreach ($pdo->query("SELECT * FROM `genc_profil`")->fetchAll() as $r) { $data['profil'][$r['profil']] = $r; }
            $data['profil_ok'] = true;
        } catch (\Throwable $e) { $data['profil'] = array(); }
        try {
            $m = $pdo->query("SELECT * FROM `genc_mesin` ORDER BY `urut`, `id`")->fetchAll();
            $u = $pdo->query("SELECT * FROM `genc_unit` ORDER BY `urut`, `id`")->fetchAll();
            $data['ok'] = true;
            foreach ($m as $r) {
                $r['arsip'] = isset($r['arsip']) ? (int) $r['arsip'] : 0;   // [GENC-08OKT26-P3]
                $r['items_arr'] = genc_reg_items_decode($r['items']);
                $data['mesin'][$r['page']] = $r;
            }
            foreach ($u as $r) {
                $r['arsip'] = isset($r['arsip']) ? (int) $r['arsip'] : 0;   // [GENC-08OKT26-P3]
                $r['alias_arr'] = genc_reg_json_list($r['alias']);
                $data['unit'][(int) $r['id']] = $r;
            }
        } catch (\Throwable $e) {
            $data = array('ok' => false, 'mesin' => array(), 'unit' => array(), 'profil' => $data['profil'], 'profil_ok' => $data['profil_ok'], 'folder' => $data['folder'], 'folder_ok' => $data['folder_ok']);
        }
        return $data;
    }

    function genc_reg_json_list($json) {
        $a = json_decode((string) $json, true);
        return is_array($a) ? array_values(array_filter(array_map('strval', $a), 'strlen')) : array();
    }

    /** Tiga bagian checklist (sama dengan semua profil bawaan). */
    function genc_reg_sections() {
        return array(
            'cleaning'    => array('title' => 'STANDAR PEMBERSIHAN (CLEANING)',  'short' => 'Pembersihan', 'standard_label' => 'Pembersihan standar'),
            'lubricating' => array('title' => 'STANDAR PELUMASAN (LUBRICATING)', 'short' => 'Pelumasan',   'standard_label' => 'Pelumasan standar'),
            'inspection'  => array('title' => 'STANDAR PENGECEKAN (INSPECTION)', 'short' => 'Pengecekan',  'standard_label' => 'Pengecekan standar'),
        );
    }

    /** JSON item -> array('cleaning' => [item...], ...). Item: db, part, standar, metode, alat, durasi, foto, aktif. */
    function genc_reg_items_decode($json) {
        $raw = json_decode((string) $json, true);
        $out = array();
        foreach (genc_reg_sections() as $k => $s) {
            $out[$k] = array();
            if (!is_array($raw) || empty($raw[$k]) || !is_array($raw[$k])) { continue; }
            foreach ($raw[$k] as $it) {
                if (!is_array($it) || empty($it['db']) || !preg_match('/^item_\d{2,3}$/', (string) $it['db'])) { continue; }
                $fotos = genc_reg_fotos_of($it);   // [GENC-07OKT26-FOTO] banyak foto per item ('foto' = foto pertama)
                $out[$k][] = array(
                    'db'      => (string) $it['db'],
                    'part'    => isset($it['part']) ? (string) $it['part'] : '',
                    'standar' => isset($it['standar']) ? (string) $it['standar'] : '',
                    'metode'  => isset($it['metode']) ? (string) $it['metode'] : '',
                    'alat'    => isset($it['alat']) ? (string) $it['alat'] : '',
                    'durasi'  => isset($it['durasi']) ? (string) $it['durasi'] : '',
                    'foto'    => $fotos ? $fotos[0] : '',
                    'fotos'   => $fotos,
                    'aktif'   => !isset($it['aktif']) || !empty($it['aktif']),
                );
            }
        }
        return $out;
    }

    /**
     * [GENC-07OKT26-FOTO] Daftar foto sebuah item (urut, foto pertama = foto utama).
     * Bentuk lama ('foto' saja) tetap dibaca. Path relatif terhadap ROOT, tanpa '..'.
     */
    function genc_reg_fotos_of($it) {
        $out = array();
        $src = (is_array($it) && isset($it['fotos']) && is_array($it['fotos'])) ? $it['fotos'] : array();
        if (!$src && is_array($it) && isset($it['foto']) && (string) $it['foto'] !== '') { $src = array($it['foto']); }
        foreach ($src as $p) {
            $p = str_replace('\\', '/', trim((string) $p));
            if ($p === '' || strpos($p, '..') !== false || in_array($p, $out, true)) { continue; }
            $out[] = $p;
        }
        return array_slice($out, 0, GENC_REG_MAX_FOTO);
    }

    /** Foto item yang filenya ada (untuk form isi & report). */
    function genc_reg_fotos_existing($it) {
        $out = array();
        foreach (genc_reg_fotos_of($it) as $p) { if (is_file(ROOT . $p)) { $out[] = $p; } }
        return $out;
    }

    /** Jumlah item aktif sebuah jenis baru. */
    function genc_reg_item_count($mesin) {
        $n = 0;
        foreach ($mesin['items_arr'] as $list) { foreach ($list as $it) { if ($it['aktif']) { $n++; } } }
        return $n;
    }

    // ------------------------------------------------------------------
    //  FOLDER BAWAAN (urut = menu sidebar). Tidak dibaca dari DB.
    // ------------------------------------------------------------------

    function genc_reg_builtin_folders() {
        // [GENC-08OKT26-MESIN] ikon & status aktif dari genc_folder ditimpakan; 'icon_awal' = ikon di kode (untuk "kembalikan")
        $out = genc_reg_builtin_folders_def();
        $st = genc_reg_data()['folder'];
        foreach ($out as $k => $f) {
            $out[$k]['icon_awal'] = $f['icon'];
            $out[$k]['title_awal'] = $f['title'];
            $out[$k]['aktif'] = true;
            $out[$k]['arsip'] = false;
            $out[$k]['hapus'] = false;        // [GENC-13OKT26] dihapus permanen (arsip = 2): hilang dari aplikasi, tabel asli tetap
            $out[$k]['area'] = null;          // [GENC-08OKT26-P3] null = ikut profil / controller (awal)
            $out[$k]['pelaksanaan'] = null;
            if (isset($st[$k])) {
                if ((string) $st[$k]['ikon'] !== '' && in_array($st[$k]['ikon'], genc_reg_icons(), true)) { $out[$k]['icon'] = $st[$k]['ikon']; }
                $out[$k]['aktif'] = (int) $st[$k]['aktif'] !== 0;
                $out[$k]['arsip'] = !empty($st[$k]['arsip']);
                $out[$k]['hapus'] = (int) $st[$k]['arsip'] === GENC_REG_HAPUS;
                if (isset($st[$k]['nama']) && trim((string) $st[$k]['nama']) !== '') { $out[$k]['title'] = trim($st[$k]['nama']); }
                if (isset($st[$k]['area']) && $st[$k]['area'] !== null) { $out[$k]['area'] = (string) $st[$k]['area']; }
                if (isset($st[$k]['pelaksanaan']) && $st[$k]['pelaksanaan'] !== null) { $out[$k]['pelaksanaan'] = (string) $st[$k]['pelaksanaan']; }
            }
        }
        return $out;
    }

    /** Definisi 6 mesin awal (urut = menu sidebar), tanpa status dari DB. */
    function genc_reg_builtin_folders_def() {
        return array(
            'palletmover' => array('title' => 'Pallet Mover & Stacker', 'icon' => 'fa-th-large', 'mode' => 'units', 'menu' => 'palletmover',
                'types' => array(
                    array('profile' => 'palletmover',   'page' => 'palletmover', 'label' => 'Pallet Mover'),
                    array('profile' => 'palletstacker', 'page' => 'palletmover', 'label' => 'Pallet Stacker'),
                ),
                'kunci_awal' => false, 'unit_hint' => 'Contoh: Pallet Mover 5', 'tab_note' => ''),
            'forklift' => array('title' => 'Forklift', 'icon' => 'fa-truck', 'mode' => 'units', 'menu' => 'forklift',
                'types' => array(
                    array('profile' => 'forklift_electric', 'page' => 'forklift', 'label' => 'Electric'),
                    array('profile' => 'forklift_diesel',   'page' => 'forklift', 'label' => 'Diesel'),
                ),
                'kunci_awal' => false, 'unit_hint' => 'Nomor seri unit, contoh: 185E00412', 'tab_note' => ''),
            'agv' => array('title' => 'AGV', 'icon' => 'fa-rocket', 'mode' => 'units', 'menu' => 'agv_table_top_lift',
                'types' => array(
                    array('profile' => 'agv_table_top_lift', 'page' => 'agv_table_top_lift', 'label' => 'Table Top Lift'),
                    array('profile' => 'counterbalance',     'page' => 'counterbalance',     'label' => 'Counterbalance'),
                ),
                'kunci_awal' => false, 'unit_hint' => 'Contoh: AGV TTL 11 atau AGV CB 4', 'tab_note' => ''),
            'dumping' => array('title' => 'Mesin Dumping', 'icon' => 'fa-download', 'mode' => 'tables', 'menu' => 'kir3p01dp001',
                'profile' => 'dumping', 'source' => 'kir3p01dp001', 'prefix' => 'Mesin Dumping',
                'pages' => array(
                    'kir3p01dp001' => array('label' => 'KIR3', 'sub' => 'KIR3P01DP001'),
                    'kir6p01dp001' => array('label' => 'KIR6', 'sub' => 'KIR6P01DP001'),
                    'kir7p01dp001' => array('label' => 'KIR7', 'sub' => 'KIR7P01DP001'),
                ),
                'kunci_awal' => false, 'unit_hint' => 'Contoh: KIR8', 'kode_hint' => 'Kode mesin di report, contoh: KIR8P01DP001'),
            'geprek' => array('title' => 'Mesin Geprek', 'icon' => 'fa-compress', 'mode' => 'tables', 'menu' => 'mesin_geprek',
                'profile' => 'mesin_geprek', 'source' => 'mesin_geprek', 'prefix' => '',
                'pages' => array('mesin_geprek' => array('label' => 'Geprek 1', 'sub' => '')),
                'kunci_awal' => false, 'unit_hint' => 'Contoh: Geprek 2', 'kode_hint' => 'Kode / nama mesin di report (boleh kosong)'),
            'conveyor' => array('title' => 'Conveyor', 'icon' => 'fa-exchange', 'mode' => 'tables', 'menu' => 'conveyor',
                'profile' => 'conveyor', 'source' => 'conveyor', 'prefix' => '',
                'pages' => array('conveyor' => array('label' => 'Conveyor 1', 'sub' => '')),
                'kunci_awal' => false, 'unit_hint' => 'Contoh: Conveyor 2', 'kode_hint' => 'Kode / nama mesin di report (boleh kosong)'),
        );
    }

    /** Baris genc_unit (bukan bawaan) milik folder, urut. */
    function genc_reg_units_of_folder($folder, $include_builtin = false) {
        $out = array();
        foreach (genc_reg_data()['unit'] as $id => $u) {
            if ($u['folder'] !== $folder) { continue; }
            if (!$include_builtin && (int) $u['builtin'] === 1) { continue; }
            $out[$id] = $u;
        }
        return $out;
    }

    /** Catatan bawaan (nonaktif / nama tab) untuk page + slug unit ('' = halaman 1 unit). */
    function genc_reg_builtin_note($page, $slug) {
        foreach (genc_reg_data()['unit'] as $u) {
            if ((int) $u['builtin'] === 1 && $u['page'] === $page && (string) $u['slug'] === (string) $slug) { return $u; }
        }
        return null;
    }

    /** Folder bawaan / jenis baru yang memuat route ini. @return array|null (dengan 'key') */
    function genc_reg_folder_of_page($page) {
        foreach (genc_reg_builtin_folders() as $k => $f) {
            if ($f['mode'] === 'units') { foreach ($f['types'] as $t) { if ($t['page'] === $page) { return $f + array('key' => $k); } } }
            else {
                if (isset($f['pages'][$page])) { return $f + array('key' => $k); }
                foreach (genc_reg_units_of_folder($k) as $u) { if ($u['page'] === $page) { return $f + array('key' => $k); } }
            }
        }
        $d = genc_reg_data();
        if (isset($d['mesin'][$page])) { return array('key' => $page, 'mode' => 'units', 'jenis' => true); }
        return null;
    }

    /**
     * Route buatan registry (am_*). @return array|null
     *   array('kind' => 'jenis', 'mesin' => row)                       jenis mesin baru
     *   array('kind' => 'clone', 'unit' => row, 'folder' => def+key)    unit baru folder 1-tabel-per-unit
     */
    function genc_reg_page($page) {
        $page = strtolower((string) $page);
        if (strpos($page, GENC_REG_PREFIX) !== 0) { return null; }
        $d = genc_reg_data();
        if (isset($d['mesin'][$page])) { return array('kind' => 'jenis', 'mesin' => $d['mesin'][$page]); }
        $folders = genc_reg_builtin_folders();
        foreach ($d['unit'] as $u) {
            if ((int) $u['builtin'] === 0 && $u['page'] === $page && isset($folders[$u['folder']]) && $folders[$u['folder']]['mode'] === 'tables') {
                return array('kind' => 'clone', 'unit' => $u, 'folder' => $folders[$u['folder']] + array('key' => $u['folder']));
            }
        }
        return null;
    }

    /** Controller untuk sebuah route mesin (bawaan atau am_*). null kalau tidak ada. */
    function genc_reg_controller($page) {
        $cls = ucfirst($page) . 'Controller';
        if (strpos($page, GENC_REG_PREFIX) !== 0 && class_exists($cls)) { return new $cls(); }
        if (genc_reg_page($page)) {
            require_once ROOT . 'app/controllers/_base/GencDynamicMachine.php';
            return GencDynamicMachine::make($page);
        }
        return null;
    }

    // ------------------------------------------------------------------
    //  UNIT: digabung ke daftar unit profil (dipanggil checklist_load_machine)
    // ------------------------------------------------------------------

    /** Slug unit (= checklist_machine_units). */
    function genc_reg_unit_slug($label) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $label), '-'));
    }

    /**
     * Daftar unit profil + unit baru dari registry + tanda nonaktif.
     * Dipanggil SEBELUM overrides diterapkan (forklift/pallet mover mengunci unit lewat overrides -> tetap menang).
     */
    function genc_reg_merge_units($profile, $units) {
        $d = genc_reg_data();
        if (!$d['ok'] || empty($d['unit'])) { return $units; }
        $out = array();
        foreach ((array) $units as $u) {
            $slug = genc_reg_unit_slug(isset($u['label']) ? $u['label'] : '');
            foreach ($d['unit'] as $r) {
                if ((int) $r['builtin'] !== 1 || $r['profile'] !== $profile || $r['slug'] !== $slug) { continue; }
                if ((int) $r['aktif'] === 0) { $u['inactive'] = true; }
                if (!empty($r['arsip'])) { $u['archived'] = true; }   // [GENC-08OKT26-P3]
                if ((int) $r['arsip'] === GENC_REG_HAPUS) { $u['deleted'] = true; }   // [GENC-13OKT26] dihapus permanen
                if ($r['alias_arr']) { $u['alias'] = array_values(array_unique(array_merge(isset($u['alias']) ? (array) $u['alias'] : array(), $r['alias_arr']))); }
                // [GENC-08OKT26-MESIN] unit awal diganti nama: slug (URL ?unit=) tetap, nama awal jadi alias (data lama tetap ikut)
                $nl = trim((string) $r['label']);
                if ($nl !== '' && checklist_norm_unit($nl) !== checklist_norm_unit($u['label'])) {
                    $u['alias'] = array_values(array_unique(array_merge(isset($u['alias']) ? (array) $u['alias'] : array(), array($u['label']))));
                    $u['label_awal'] = $u['label'];
                    $u['label'] = $nl;
                    $u['slug'] = $slug;
                }
            }
            $out[] = $u;
        }
        foreach ($d['unit'] as $r) {
            if ((int) $r['builtin'] !== 0 || $r['profile'] !== $profile) { continue; }
            $bf = genc_reg_builtin_folders();
            if (!isset($bf[$r['folder']]) || $bf[$r['folder']]['mode'] !== 'units') { continue; }   // jenis baru: genc_reg_profile(); 1-tabel-per-unit: tab sendiri
            $u = array('label' => $r['label'], 'alias' => $r['alias_arr'], 'reg_id' => (int) $r['id']);
            if ((int) $r['aktif'] === 0) { $u['inactive'] = true; }
            if (!empty($r['arsip'])) { $u['archived'] = true; }
            $out[] = $u;
        }
        return $out;
    }

    /** Profil report untuk jenis mesin baru (am_*). null kalau bukan. */
    function genc_reg_profile($name) {
        $name = (string) $name;
        if (strpos($name, GENC_REG_PREFIX) !== 0) { return null; }
        $d = genc_reg_data();
        if (!isset($d['mesin'][$name])) { return null; }
        $m = $d['mesin'][$name];
        $sections = array(); $no = 0;
        foreach (genc_reg_sections() as $k => $s) {
            $items = array();
            foreach ($m['items_arr'][$k] as $it) {
                if (!$it['aktif']) { continue; }
                $no++;
                $row = array('no' => $no, 'part' => $it['part'], 'alat' => $it['alat'], 'metode' => $it['metode'], 'standar' => $it['standar'], 'durasi' => $it['durasi'], 'db' => $it['db']);
                $fotos = genc_reg_fotos_existing($it);   // [GENC-07OKT26-FOTO]
                if ($fotos) { $row['foto'] = $fotos[0]; $row['fotos'] = $fotos; }
                $items[] = $row;
            }
            $slots = array_keys(genc_reg_photo_slots()); $slot = $slots[count($sections)];   // [GENC-06OKT26-ISI] gambar report opsional
            // [GENC-07OKT26-FOTO] bagian tanpa item = tanpa gambar (kerangka bagian tetap utuh di report)
            $sections[] = array('key' => $k, 'title' => $s['title'], 'short' => $s['short'], 'standard_label' => $s['standard_label'],
                'photo' => empty($items) ? '' : $slot, 'photo_label' => ($k === 'cleaning' ? 'Gambar Bagian Mesin' : ''), 'blank_rows' => empty($items) ? 2 : 0, 'items' => $items);
        }
        $units = array();
        foreach (genc_reg_units_of_folder($name) as $r) {
            $u = array('label' => $r['label'], 'alias' => $r['alias_arr'], 'reg_id' => (int) $r['id']);
            if ((int) $r['aktif'] === 0) { $u['inactive'] = true; }
            if (!empty($r['arsip'])) { $u['archived'] = true; }
            $units[] = $u;
        }
        return array(
            'key'           => $name,
            'table'         => $name,
            'machine_code'  => $m['nama'],
            'area'          => $m['area'],
            'title'         => 'Checklist AM ' . $m['nama'],
            'file_slug'     => preg_replace('/[^A-Za-z0-9]+/', '-', $m['nama']),
            'user_field'    => 'user_created',
            'approve_field' => 'user_approve',
            'date_field'    => 'date_created',
            'unit_field'    => 'unit',
            'pelaksanaan'   => $m['pelaksanaan'],
            'images'        => array('logo' => 'assets/images/dumping_logo.png'),
            'img_size'      => array('logo' => array(138, 40)),
            'units'         => $units,
            'sections'      => $sections,
        );
    }

    // ------------------------------------------------------------------
    //  ISI CHECKLIST MESIN BAWAAN BISA DIUBAH + KUNCI      [GENC-06OKT26-ISI]
    //
    //  File profil (_shared/machines/*.php) tetap jadi ISI AWAL. Kalau isi
    //  diubah lewat menu Mesin & Unit, salinan lengkapnya disimpan di
    //  genc_profil.items (JSON, bentuk sama dengan genc_mesin.items, kolom db
    //  boleh nama kolom lama) dan ditimpakan di checklist_load_machine().
    //  Tanpa baris genc_profil: profil 100% sama dengan file (dites identik).
    //   - item bawaan dikenali dari kolom db-nya (data lama tetap ikut)
    //   - item baru -> kolom baru am_item_NN (ALTER TABLE, boleh NULL) di SEMUA
    //     tabel yang memakai profil itu; isian kosong di checklist lama TIDAK
    //     dihitung temuan (flag 'baru')
    //   - item dihapus -> disembunyikan (aktif=0), kolom & data tetap
    //   - item yang ada di file tapi belum ada di salinan (developer menambah
    //     item di kode) tetap tampil, di ujung bagiannya
    //  Kunci (genc_profil.kunci): 1 = isi checklist tidak bisa diubah sampai
    //  dibuka lagi. NULL = ikut kunci awal folder ('kunci_awal', sekarang semua terbuka - K60 rev.).
    // ------------------------------------------------------------------

    /** Profil bawaan yang bisa diubah: [profil => folder, label, kunci_awal]. Urut = menu. Semua mesin diperlakukan sama (terbuka). */
    function genc_reg_builtin_profiles() {
        $out = array();
        foreach (genc_reg_builtin_folders() as $k => $f) {
            if ($f['mode'] === 'units') {
                foreach ($f['types'] as $t) { $out[$t['profile']] = array('folder' => $k, 'label' => $t['label'], 'kunci_awal' => !empty($f['kunci_awal'])); }
            } else {
                $out[$f['profile']] = array('folder' => $k, 'label' => $f['title'], 'kunci_awal' => !empty($f['kunci_awal']));
            }
        }
        return $out;
    }

    /** Baris genc_profil (atau null). */
    function genc_reg_ov_row($profile) {
        $d = genc_reg_data();
        return isset($d['profil'][$profile]) ? $d['profil'][$profile] : null;
    }

    /** Isi checklist mesin ini sedang dikunci? */
    function genc_reg_locked($profile) {
        $r = genc_reg_ov_row($profile);
        if ($r && $r['kunci'] !== null && $r['kunci'] !== '') { return (int) $r['kunci'] === 1; }
        $b = genc_reg_builtin_profiles();
        return isset($b[$profile]) ? $b[$profile]['kunci_awal'] : false;
    }

    /** Kolom db yang sah untuk item (kolom lama bawaan atau am_item_NN). */
    function genc_reg_db_col_ok($c) {
        return is_string($c) && preg_match('/^[a-z][a-z0-9_]{0,63}$/', $c) && !in_array($c, array('id', 'date_created', 'user_created', 'user_create', 'unit', 'keterangan', 'kondisi', 'approval', 'user_approve', 'date_approve', 'date_update', 'user_update'), true);
    }

    /** JSON genc_profil.items -> array('cleaning' => [item...], ...). null kalau tidak ada salinan. */
    function genc_reg_ov_items($profile) {
        $r = genc_reg_ov_row($profile);
        if (!$r || $r['items'] === null || $r['items'] === '') { return null; }
        $raw = json_decode((string) $r['items'], true);
        if (!is_array($raw)) { return null; }
        $out = array();
        foreach (genc_reg_sections() as $k => $s) {
            $out[$k] = array();
            if (empty($raw[$k]) || !is_array($raw[$k])) { continue; }
            foreach ($raw[$k] as $it) {
                if (!is_array($it) || !isset($it['db']) || !genc_reg_db_col_ok($it['db'])) { continue; }
                $row = array('db' => $it['db'], 'aktif' => !isset($it['aktif']) || !empty($it['aktif']), 'baru' => !empty($it['baru']));
                foreach (array('part', 'standar', 'metode', 'alat', 'durasi') as $f) { $row[$f] = isset($it[$f]) ? (string) $it[$f] : ''; }
                $row['fotos'] = genc_reg_fotos_of($it);   // [GENC-07OKT26-FOTO]
                $row['foto'] = $row['fotos'] ? $row['fotos'][0] : '';
                $out[$k][] = $row;
            }
        }
        return $out;
    }

    /** JSON genc_profil.gambar mentah: [slot => array] (kosong kalau tidak ada). */
    function genc_reg_gambar_raw($profile) {
        $r = genc_reg_ov_row($profile);
        if (!$r || $r['gambar'] === null || $r['gambar'] === '') { return array(); }
        $raw = json_decode((string) $r['gambar'], true);
        return is_array($raw) ? $raw : array();
    }

    /**
     * Gambar PANEL yang diganti lewat menu: [atas|tengah|bawah => array('path','w','h')] ('' path = gambar bawaan disembunyikan).
     * [GENC-07OKT26-FOTO] slot yang hanya berisi pengaturan kolase (mode/hide/extra, tanpa 'path') tidak dihitung di sini.
     */
    function genc_reg_ov_gambar($profile) {
        $out = array();
        $raw = genc_reg_gambar_raw($profile);
        foreach (genc_reg_photo_slots() as $k => $lbl) {
            if (!isset($raw[$k]) || !is_array($raw[$k]) || !array_key_exists('path', $raw[$k])) { continue; }
            $p = str_replace('\\', '/', (string) $raw[$k]['path']);
            if ($p !== '' && (strpos($p, '..') !== false || !is_file(ROOT . $p))) { continue; }   // file hilang -> kembali ke bawaan
            $out[$k] = array('path' => $p, 'w' => max(20, min(152, (int) (isset($raw[$k]['w']) ? $raw[$k]['w'] : 152))), 'h' => max(20, min(260, (int) (isset($raw[$k]['h']) ? $raw[$k]['h'] : 135))));
        }
        return $out;
    }

    /** Slot gambar bagian mesin di report (kolom kiri), per bagian checklist. */
    function genc_reg_photo_slots() {
        return array('atas' => 'Pembersihan', 'tengah' => 'Pelumasan', 'bawah' => 'Pengecekan');
    }

    /** [GENC-07OKT26-FOTO] Slot gambar sebuah bagian: 'photo' di profil, atau menurut kunci bagian (cleaning/lubricating/inspection). */
    function genc_reg_section_slot($s, $index = 0) {
        if (isset($s['photo']) && (string) $s['photo'] !== '') { return (string) $s['photo']; }
        $by = array('cleaning' => 'atas', 'lubricating' => 'tengah', 'inspection' => 'bawah');
        if (isset($s['key']) && isset($by[$s['key']])) { return $by[$s['key']]; }
        $slots = array_keys(genc_reg_photo_slots());
        return isset($slots[$index]) ? $slots[$index] : 'bawah';
    }

    /**
     * [GENC-07OKT26-FOTO] Pengaturan gambar bagian mesin per slot:
     *   mode   'panel' = 1 gambar utuh (bawaan / diunggah)   'auto' = kolase otomatis dari foto item (bernomor)
     *   hide   foto (path) yang tidak ikut kolase
     *   extra  foto tambahan untuk kolase: [array('path', 'db')] (db = item yang ditunjuk, nomornya ikut item)
     * Mode bawaan (belum pernah dipilih): 'panel' kalau slot punya gambar (bawaan / diunggah), selain itu 'auto'.
     * $images = images profil SEBELUM gambar menu ditimpakan (gambar bawaan dari kode).
     */
    function genc_reg_gambar_cfg($profile, $images = array()) {
        $raw = genc_reg_gambar_raw($profile);
        $ov = genc_reg_ov_gambar($profile);
        $out = array();
        foreach (genc_reg_photo_slots() as $k => $lbl) {
            $r = (isset($raw[$k]) && is_array($raw[$k])) ? $raw[$k] : array();
            $mode = (isset($r['mode']) && in_array($r['mode'], array('auto', 'panel'), true)) ? $r['mode'] : '';
            if ($mode === '') {
                $base = (isset($images[$k]) && (string) $images[$k] !== '' && is_file(ROOT . $images[$k]));
                $mode = (isset($ov[$k]) || $base) ? 'panel' : 'auto';
            }
            $hide = array();
            foreach ((isset($r['hide']) && is_array($r['hide'])) ? $r['hide'] : array() as $p) { $p = str_replace('\\', '/', (string) $p); if ($p !== '' && strpos($p, '..') === false) { $hide[$p] = true; } }
            $extra = array();
            foreach ((isset($r['extra']) && is_array($r['extra'])) ? $r['extra'] : array() as $x) {
                if (!is_array($x) || empty($x['path']) || !isset($x['db'])) { continue; }
                $p = str_replace('\\', '/', (string) $x['path']);
                if (strpos($p, '..') !== false || !genc_reg_db_col_ok((string) $x['db']) && !preg_match('/^item_\d{2,3}$/', (string) $x['db'])) { continue; }
                $extra[] = array('path' => $p, 'db' => (string) $x['db']);
                if (count($extra) >= GENC_REG_MAX_EXTRA) { break; }
            }
            $out[$k] = array('mode' => $mode, 'hide' => array_keys($hide), 'extra' => $extra, 'set' => isset($r['mode']));
        }
        return $out;
    }

    /** Profil dari FILE saja (tanpa salinan menu) - acuan "isi bawaan". */
    function genc_reg_base_conf($profile) {
        $file = __DIR__ . '/machines/' . basename($profile) . '.php';
        if (!is_file($file)) { return null; }
        $c = include $file;
        return is_array($c) ? $c : null;
    }

    /**
     * Timpakan salinan menu ke profil (dipanggil checklist_load_machine sebelum overrides).
     * Tanpa salinan & tanpa gambar: $conf dikembalikan apa adanya.
     */
    function genc_reg_apply_override($profile, $conf) {
        $d = genc_reg_data();
        if (empty($d['profil'])) { return $conf; }
        $profile = (string) $profile;
        $ov = strpos($profile, GENC_REG_PREFIX) === 0 ? null : genc_reg_ov_items($profile);
        if ($ov !== null && !empty($conf['sections'])) {
            $orig = array();   // db => item asli (simpan kunci tambahan di masa depan)
            foreach ($conf['sections'] as $s) { foreach ((array) $s['items'] as $it) { if (!empty($it['db'])) { $orig[$it['db']] = $it; } } }
            $listed = array(); foreach ($ov as $list) { foreach ($list as $it) { $listed[$it['db']] = true; } }
            $hidden = array(); $no = 0;
            foreach ($conf['sections'] as $si => $s) {
                $sk = isset($s['key']) ? $s['key'] : '';
                $items = array();
                $src = isset($ov[$sk]) ? $ov[$sk] : array();
                foreach ((array) $s['items'] as $it) { if (!empty($it['db']) && !isset($listed[$it['db']])) { $src[] = $it + array('aktif' => true, 'baru' => false); } }   // item baru di kode
                foreach ($src as $it) {
                    if (empty($it['aktif'])) { $hidden[] = $it['db']; continue; }
                    $row = isset($orig[$it['db']]) ? $orig[$it['db']] : array();
                    foreach (array('part', 'alat', 'metode', 'standar', 'durasi') as $f) { $row[$f] = isset($it[$f]) ? (string) $it[$f] : ''; }
                    $row['db'] = $it['db'];
                    $row['no'] = ++$no;
                    $fotos = genc_reg_fotos_existing($it);   // [GENC-07OKT26-FOTO] banyak foto per item
                    unset($row['foto'], $row['fotos']);
                    if ($fotos) { $row['foto'] = $fotos[0]; $row['fotos'] = $fotos; }
                    if (!empty($it['baru'])) { $row['baru'] = true; }
                    $items[] = $row;
                }
                $was = count((array) $s['items']);
                $conf['sections'][$si]['items'] = $items;
                $bl = isset($s['blank_rows']) ? (int) $s['blank_rows'] : 0;
                $conf['sections'][$si]['blank_rows'] = empty($items) ? ($was === 0 ? $bl : 2) : ($was === 0 ? 0 : $bl);
                // [GENC-07OKT26-FOTO] semua item bagian ini dihapus lewat menu -> gambarnya ikut hilang (kerangka bagian tetap).
                // Bagian yang dari awal kosong (form resmi, mis. Pelumasan Conveyor) tetap memakai gambar bawaannya.
                if (empty($items) && $was > 0) { $conf['sections'][$si]['photo'] = ''; }
                // bagian yang dulu kosong tanpa slot gambar (mis. Pelumasan Dumping) lalu diberi item -> dapat slot sendiri
                if (!empty($items) && (!isset($s['photo']) || (string) $s['photo'] === '')) { $conf['sections'][$si]['photo'] = genc_reg_section_slot(array('key' => $sk), $si); }
            }
            $conf['hidden_cols'] = $hidden;
            $conf['isi_menu'] = true;
        }
        // [GENC-07OKT26-FOTO] mode gambar per bagian (panel / kolase otomatis), dihitung dari gambar bawaan sebelum ditimpa
        $conf['gambar_cfg'] = genc_reg_gambar_cfg($profile, isset($conf['images']) ? (array) $conf['images'] : array());
        foreach (genc_reg_ov_gambar($profile) as $k => $g) {
            if (!isset($conf['images']) || !is_array($conf['images'])) { $conf['images'] = array(); }
            $conf['images'][$k] = $g['path'];
            if ($g['path'] !== '') { $conf['img_size'][$k] = array($g['w'], $g['h']); }
        }
        return $conf;
    }

    /**
     * Isi checklist SEKARANG untuk editor (termasuk item yang disembunyikan):
     * [bagian => [array(db, part, standar, metode, alat, durasi, foto, aktif, baru)]].
     */
    function genc_reg_isi_rows($profile) {
        $base = genc_reg_base_conf($profile);
        $out = array();
        foreach (genc_reg_sections() as $k => $s) { $out[$k] = array(); }
        if (!$base) { return $out; }
        $ov = genc_reg_ov_items($profile);
        $listed = array();
        if ($ov !== null) { foreach ($ov as $k => $list) { foreach ($list as $it) { $out[$k][] = $it; $listed[$it['db']] = true; } } }
        foreach ((array) $base['sections'] as $i => $s) {
            $k = isset($s['key']) && isset($out[$s['key']]) ? $s['key'] : 'inspection';
            foreach ((array) $s['items'] as $it) {
                if (empty($it['db']) || isset($listed[$it['db']])) { continue; }
                $out[$k][] = array('db' => $it['db'], 'part' => (string) $it['part'], 'standar' => (string) $it['standar'], 'metode' => (string) $it['metode'],
                    'alat' => (string) $it['alat'], 'durasi' => (string) $it['durasi'], 'foto' => isset($it['foto']) ? (string) $it['foto'] : '', 'fotos' => genc_reg_fotos_of($it), 'aktif' => true, 'baru' => false);
            }
        }
        return $out;
    }

    /** Isi dari file saja dalam bentuk editor (pembanding "sama dengan bawaan?"). */
    function genc_reg_isi_base_rows($profile) {
        $base = genc_reg_base_conf($profile);
        $out = array();
        foreach (genc_reg_sections() as $k => $s) { $out[$k] = array(); }
        if (!$base) { return $out; }
        foreach ((array) $base['sections'] as $s) {
            $k = isset($s['key']) && isset($out[$s['key']]) ? $s['key'] : 'inspection';
            foreach ((array) $s['items'] as $it) {
                if (empty($it['db'])) { continue; }
                $out[$k][] = array('db' => $it['db'], 'part' => (string) $it['part'], 'standar' => (string) $it['standar'], 'metode' => (string) $it['metode'],
                    'alat' => (string) $it['alat'], 'durasi' => (string) $it['durasi'], 'foto' => isset($it['foto']) ? (string) $it['foto'] : '', 'fotos' => genc_reg_fotos_of($it), 'aktif' => true, 'baru' => false);
            }
        }
        return $out;
    }

    /** Semua tabel data yang memakai profil ini (kolom item baru dibuat di semuanya). */
    function genc_reg_profile_tables($profile) {
        $out = array();
        if (strpos($profile, GENC_REG_PREFIX) === 0) { return array($profile); }
        foreach (genc_reg_builtin_folders() as $k => $f) {
            if ($f['mode'] === 'tables' && $f['profile'] === $profile) {
                foreach ($f['pages'] as $p => $t) { $out[$p] = true; }
                foreach (genc_reg_units_of_folder($k) as $u) { $out[$u['page']] = true; }
            }
        }
        if (!$out) {
            $c = genc_reg_base_conf($profile);
            if ($c && !empty($c['table'])) { $out[$c['table']] = true; }
        }
        return array_keys($out);
    }

    /** Nama kolom am_item_NN yang belum dipakai di tabel-tabel itu / salinan profil lain. */
    function genc_reg_free_item_col($tables, &$seq, $taken = array()) {
        $pdo = genc_reg_db();
        $cols = $taken;
        foreach ($tables as $t) {
            try { foreach ($pdo->query("SHOW COLUMNS FROM `" . str_replace('`', '', $t) . "`")->fetchAll() as $c) { $cols[strtolower($c['Field'])] = true; } } catch (\Throwable $e) { }
        }
        foreach (genc_reg_data()['profil'] as $r) {
            $a = json_decode((string) $r['items'], true);
            if (is_array($a)) { foreach ($a as $list) { if (is_array($list)) { foreach ($list as $it) { if (isset($it['db'])) { $cols[strtolower((string) $it['db'])] = true; } } } } }
        }
        do { $seq++; $c = sprintf('am_item_%02d', $seq); } while (isset($cols[$c]) && $seq < 999);
        return $c;
    }

    /** Unit nonaktif: set "page|slug" ('' slug = halaman 1-tabel-per-unit yang nonaktif). */
    function genc_reg_inactive_set() {
        $d = genc_reg_data();
        $bf = genc_reg_builtin_folders();
        $set = array();
        foreach ($d['unit'] as $r) {
            if ((int) $r['aktif'] !== 0) { continue; }
            if ((int) $r['builtin'] === 0 && isset($bf[$r['folder']]) && $bf[$r['folder']]['mode'] === 'tables') {
                $set[$r['page'] . '|'] = true;     // unit = halaman sendiri
            } else {
                $set[$r['page'] . '|' . $r['slug']] = true;
            }
        }
        foreach ($d['mesin'] as $p => $m) { if ((int) $m['aktif'] === 0 || genc_reg_item_count($m) === 0) { $set[$p . '|'] = true; } }
        // [GENC-08OKT26-MESIN] mesin awal yang dinonaktifkan: semua halamannya nonaktif
        foreach ($bf as $k => $f) {
            if ($f['aktif']) { continue; }
            foreach (genc_reg_folder_pages($k) as $p) { $set[$p . '|'] = true; }
        }
        return $set;
    }

    /** [GENC-08OKT26-MESIN] Semua halaman (route) sebuah mesin awal, urut tab: halaman di kode lalu unit baru. */
    function genc_reg_folder_pages($key) {
        $bf = genc_reg_builtin_folders_def();
        if (!isset($bf[$key])) { return array(); }
        $f = $bf[$key];
        $pages = array();
        if ($f['mode'] === 'units') { foreach ($f['types'] as $t) { $pages[$t['page']] = true; } }
        else {
            foreach ($f['pages'] as $p => $t) { $pages[$p] = true; }
            foreach (genc_reg_units_of_folder($key) as $u) { $pages[$u['page']] = true; }
        }
        return array_keys($pages);
    }

    /**
     * [GENC-08OKT26-P3] Diarsipkan ("hapus" yang aman): set "page|slug" seperti genc_reg_inactive_set().
     * Arsip = nonaktif + keluar dari semua daftar (sidebar, Home, Approval/NOK, pohon utama, matriks). Data & tabel tetap.
     */
    function genc_reg_archived_set() {
        $d = genc_reg_data();
        $bf = genc_reg_builtin_folders();
        $set = array();
        foreach ($d['unit'] as $r) {
            if (empty($r['arsip'])) { continue; }
            if ((int) $r['builtin'] === 0 && isset($bf[$r['folder']]) && $bf[$r['folder']]['mode'] === 'tables') { $set[$r['page'] . '|'] = true; }
            else { $set[$r['page'] . '|' . $r['slug']] = true; }
        }
        foreach ($d['mesin'] as $p => $m) { if (!empty($m['arsip'])) { $set[$p . '|'] = true; } }
        foreach ($bf as $k => $f) { if ($f['arsip']) { foreach (genc_reg_folder_pages($k) as $p) { $set[$p . '|'] = true; } } }
        return $set;
    }

    /**
     * [GENC-13OKT26] Route mesin AWAL yang sudah DIHAPUS PERMANEN: seluruh mesinnya, atau unit 1-tabel-per-unit
     * (KIR6, Geprek, Conveyor 1, ...) yang dihapus permanen. Dipakai Router (-> 404). Bukan halaman mesin awal: false.
     */
    function genc_reg_is_deleted_page($page) {
        $page = strtolower((string) $page);
        $fk = '';
        foreach (genc_reg_builtin_folders_def() as $k => $f) {
            $pages = $f['mode'] === 'units' ? array_map(function ($t) { return $t['page']; }, $f['types']) : array_keys($f['pages']);
            if (in_array($page, $pages, true)) { $fk = $k; break; }
        }
        if ($fk === '') { return false; }
        $bf = genc_reg_builtin_folders();
        if (!empty($bf[$fk]['hapus'])) { return true; }
        $n = genc_reg_builtin_note($page, '');
        return $n && (int) $n['arsip'] === GENC_REG_HAPUS;
    }

    /** [GENC-13OKT26] Hak akses yang TIDAK ikut dihapus saat mesin awal dihapus permanen: route bersama (Approval / NOK History di palletmover). */
    function genc_reg_shared_actions($page) {
        return $page === 'palletmover' ? array('approval', 'uncompleted') : array();
    }

    function genc_reg_is_archived($page, $slug = '') {
        $s = genc_reg_archived_set();
        return isset($s[$page . '|' . (string) $slug]) || ($slug !== '' && isset($s[$page . '|']));
    }

    /** [GENC-08OKT26-P3] Folder mesin awal pemilik sebuah profil report ('' kalau bukan). */
    function genc_reg_folder_of_profile($profile) {
        $bp = genc_reg_builtin_profiles();
        return isset($bp[$profile]) ? $bp[$profile]['folder'] : '';
    }

    /**
     * [GENC-08OKT26-P3] Ganti nama mesin awal di sebuah teks: nama awal (utuh, bukan potongan kata) -> nama baru.
     * $lower = teks huruf kecil (name_lower). Tidak diganti nama: teks dikembalikan apa adanya.
     */
    function genc_reg_rename_text($key, $text, $lower = false) {
        $bf = genc_reg_builtin_folders();
        if (!isset($bf[$key]) || $bf[$key]['title'] === $bf[$key]['title_awal'] || (string) $text === '') { return $text; }
        $from = $bf[$key]['title_awal']; $to = $bf[$key]['title'];
        if ($lower) { $to = function_exists('mb_strtolower') ? mb_strtolower($to, 'UTF-8') : strtolower($to); }
        $out = preg_replace('/(?<![A-Za-z0-9])' . preg_quote($from, '/') . '(?![A-Za-z0-9])/u' . ($lower ? 'i' : ''), str_replace(array('\\', '$'), array('\\\\', '\\$'), $to), (string) $text);
        return $out === null ? $text : $out;
    }

    /**
     * [GENC-08OKT26-P3] Dipanggil di akhir checklist_load_machine() (SESUDAH overrides controller): nama, area,
     * pelaksanaan mesin awal yang diubah lewat Mesin & Unit. Tanpa perubahan: $conf tidak disentuh (report identik).
     * Judul report yang tidak memuat nama awal (Pallet Mover / Pallet Stacker) -> "Checklist AM <nama> - <jenis>".
     */
    function genc_reg_final_conf($profile, $conf) {
        $fk = genc_reg_folder_of_profile($profile);
        if ($fk === '') { return $conf; }
        $bf = genc_reg_builtin_folders();
        $f = $bf[$fk];
        if ($f['title'] !== $f['title_awal']) {
            foreach (array('title', 'machine_code') as $k) {
                if (!isset($conf[$k])) { continue; }
                $new = genc_reg_rename_text($fk, $conf[$k]);
                if ($k === 'title' && $new === $conf[$k]) {
                    $bp = genc_reg_builtin_profiles();
                    $new = 'Checklist AM ' . $f['title'] . (isset($bp[$profile]) && $bp[$profile]['label'] !== $f['title_awal'] ? ' - ' . $bp[$profile]['label'] : '');
                }
                $conf[$k] = $new;
            }
        }
        if ($f['area'] !== null) { $conf['area'] = $f['area']; }
        if ($f['pelaksanaan'] !== null) { $conf['pelaksanaan'] = $f['pelaksanaan']; }
        return $conf;
    }

    /** [GENC-08OKT26-P3] Konfigurasi halaman mesin awal (GencChecklistBase::genc_c): nama & area yang diubah. */
    function genc_reg_page_conf($c) {
        if (empty($c['page'])) { return $c; }
        $f = genc_reg_folder_of_page($c['page']);
        if (!$f || !empty($f['jenis'])) { return $c; }
        foreach (array('group_title', 'title', 'machine_title') as $k) { if (isset($c[$k])) { $c[$k] = genc_reg_rename_text($f['key'], $c[$k]); } }
        if (isset($c['name_lower'])) { $c['name_lower'] = genc_reg_rename_text($f['key'], $c['name_lower'], true); }
        if ($f['area'] !== null) { $c['area'] = $f['area']; }
        return $c;
    }

    /** [GENC-08OKT26-MESIN] Masih "baru" (dibuat <= GENC_REG_NEW_DAYS hari lalu)? */
    function genc_reg_is_new($created_at) {
        $t = $created_at ? strtotime((string) $created_at) : false;
        return $t !== false && $t > 0 && (time() - $t) < GENC_REG_NEW_DAYS * 86400;
    }

    function genc_reg_is_inactive($page, $slug = '') {
        $s = genc_reg_inactive_set();
        return isset($s[$page . '|' . (string) $slug]) || ($slug !== '' && isset($s[$page . '|']));
    }

    // ------------------------------------------------------------------
    //  TAB untuk folder 1-tabel-per-unit (Dumping, Geprek, Conveyor) + jenis baru
    // ------------------------------------------------------------------

    /**
     * Dipanggil GencChecklistBase::genc_c(). Folder 1-tabel-per-unit yang PUNYA unit baru:
     * tab = halaman bawaan + unit baru. Tanpa unit baru: $c tidak disentuh (tampilan lama).
     */
    function genc_reg_apply_conf($c) {
        if (empty($c['page'])) { return $c; }
        $d = genc_reg_data();
        if (!$d['ok'] || empty($d['unit'])) { return $c; }
        $f = genc_reg_folder_of_page($c['page']);
        if (!$f || $f['mode'] !== 'tables') { return $c; }
        $clones = genc_reg_units_of_folder($f['key']);
        $notes = false;
        foreach ($f['pages'] as $p => $t) { if (genc_reg_builtin_note($p, '')) { $notes = true; } }
        if (empty($clones) && !$notes) { return $c; }
        $tabs = array();
        foreach ($f['pages'] as $p => $t) {
            $n = genc_reg_builtin_note($p, '');
            $tabs[] = array('label' => ($n && trim($n['label']) !== '') ? $n['label'] : $t['label'], 'sub' => $t['sub'], 'page' => $p);
        }
        foreach ($clones as $u) {
            if ((int) $u['aktif'] === 0 && $u['page'] !== $c['page']) { continue; }   // nonaktif: tab tidak dibuat (kecuali halaman itu sendiri)
            $tabs[] = array('label' => $u['label'], 'sub' => ($f['key'] === 'dumping' ? $u['kode'] : ''), 'page' => $u['page']);
        }
        if (count($tabs) < 2) { return $c; }
        $c['tabs'] = $tabs;
        if (empty($c['group_title'])) {
            $c['group_title'] = $f['title'];
            if (isset($f['pages'][$c['page']]) && $f['key'] !== 'dumping') {   // Geprek / Conveyor bawaan: judul unit = nama tab
                foreach ($tabs as $t) { if ($t['page'] === $c['page']) { $c['title'] = $t['label']; $c['name_lower'] = $t['label']; } }
            }
        }
        return $c;
    }

    // ------------------------------------------------------------------
    //  MENU, HOME, APPROVAL/NOK, MATRIKS
    // ------------------------------------------------------------------

    /** Jenis mesin baru yang tampil di sidebar / Home / ringkasan (aktif & punya item). */
    function genc_reg_active_jenis() {
        $out = array();
        foreach (genc_reg_data()['mesin'] as $p => $m) {
            if ((int) $m['aktif'] === 1 && genc_reg_item_count($m) > 0) { $out[$p] = $m; }
        }
        return $out;
    }

    /**
     * Ikon yang boleh dipilih untuk semua mesin (semua ada di Font Awesome 4.5 bawaan phpRAD - dicek ke
     * assets/css/font-awesome.min.css). [GENC-08OKT26-MESIN] 24 -> 80, termasuk ikon 6 mesin awal.
     * Nilai = kata kunci pencarian (Indonesia / Inggris).
     */
    function genc_reg_icon_words() {
        return array(
            // mesin & alat
            'fa-cube' => 'kubus kotak box', 'fa-cubes' => 'kubus banyak boxes', 'fa-cogs' => 'gir mesin gear', 'fa-cog' => 'gir mesin gear setting',
            'fa-wrench' => 'kunci pas perbaikan tool', 'fa-gavel' => 'palu hammer', 'fa-industry' => 'pabrik industri factory', 'fa-compress' => 'geprek tekan press',
            'fa-expand' => 'perluas expand', 'fa-download' => 'dumping turun unduh', 'fa-upload' => 'naik unggah upload', 'fa-exchange' => 'conveyor tukar pindah',
            'fa-random' => 'acak sortir', 'fa-refresh' => 'putar ulang refresh', 'fa-recycle' => 'daur ulang recycle', 'fa-filter' => 'saring filter',
            'fa-magnet' => 'magnet', 'fa-plug' => 'colokan listrik plug', 'fa-bolt' => 'listrik petir bolt', 'fa-power-off' => 'power daya',
            'fa-battery-full' => 'baterai penuh battery', 'fa-battery-half' => 'baterai setengah battery', 'fa-battery-empty' => 'baterai kosong battery charger',
            'fa-tachometer' => 'speedometer meter', 'fa-tint' => 'cairan oli air tetes', 'fa-fire' => 'api panas fire',
            'fa-fire-extinguisher' => 'apar pemadam', 'fa-lightbulb-o' => 'lampu ide bulb', 'fa-print' => 'printer cetak', 'fa-barcode' => 'barcode label',
            'fa-qrcode' => 'qr kode', 'fa-balance-scale' => 'timbangan scale', 'fa-flask' => 'lab kimia flask', 'fa-eyedropper' => 'pipet sampel',
            'fa-scissors' => 'gunting potong cutter', 'fa-paint-brush' => 'kuas cat', 'fa-desktop' => 'komputer monitor pc',
            'fa-laptop' => 'laptop', 'fa-mobile' => 'hp handphone scanner', 'fa-hdd-o' => 'harddisk server', 'fa-server' => 'server rak',
            'fa-sitemap' => 'struktur jaringan', 'fa-wifi' => 'wifi sinyal', 'fa-video-camera' => 'cctv kamera', 'fa-camera' => 'kamera foto',
            'fa-lock' => 'gembok kunci lock', 'fa-key' => 'kunci key', 'fa-shield' => 'perisai keamanan', 'fa-bell' => 'bel alarm',
            // angkut & gudang
            'fa-truck' => 'forklift truk', 'fa-car' => 'mobil kendaraan', 'fa-bus' => 'bus', 'fa-bicycle' => 'sepeda',
            'fa-rocket' => 'agv roket', 'fa-plane' => 'pesawat', 'fa-ship' => 'kapal', 'fa-subway' => 'kereta rel',
            'fa-shopping-cart' => 'troli keranjang cart', 'fa-cart-plus' => 'troli tambah', 'fa-cart-arrow-down' => 'troli muat', 'fa-archive' => 'arsip kotak',
            'fa-dropbox' => 'kotak paket box', 'fa-th-large' => 'pallet mover grid', 'fa-th' => 'pallet rak grid', 'fa-bars' => 'rak garis',
            'fa-building' => 'gedung gudang', 'fa-home' => 'rumah', 'fa-map-marker' => 'lokasi area', 'fa-road' => 'jalan',
            'fa-arrows' => 'arah gerak', 'fa-arrows-v' => 'naik turun lift', 'fa-arrows-h' => 'kiri kanan geser', 'fa-level-up' => 'angkat lifter',
            'fa-sort-amount-asc' => 'tumpuk stacker', 'fa-life-ring' => 'pelampung safety', 'fa-medkit' => 'p3k medis', 'fa-leaf' => 'daun lingkungan', 'fa-sun-o' => 'matahari panas', 'fa-flag' => 'bendera', 'fa-star' => 'bintang',
        );
    }

    function genc_reg_icons() {
        return array_keys(genc_reg_icon_words());
    }

    /**
     * Daftar mesin untuk Home & ringkasan Approval/NOK History. Urut = menu.
     * Tanpa isi registry: SAMA PERSIS dengan daftar lama di GencHubReader / PalletmoverController.
     */
    function genc_reg_hub_machines() {
        $out = array();
        foreach (genc_reg_builtin_folders() as $k => $f) {
            if ($f['arsip']) { continue; }   // [GENC-08OKT26-P3] diarsipkan: keluar dari semua daftar
            $out[] = array('title' => $f['title'], 'icon' => $f['icon'], 'pages' => genc_reg_folder_pages($k));
        }
        // [GENC-08OKT26-MESIN] jenis nonaktif ikut di sini (Home menyembunyikan halaman nonaktif; Approval / NOK
        // History tetap menampilkannya selama masih ada yang menunggu / perlu tindakan - sama dengan mesin lain)
        foreach (genc_reg_data()['mesin'] as $p => $m) {
            if (genc_reg_item_count($m) === 0 || !empty($m['arsip'])) { continue; }
            $out[] = array('title' => $m['nama'], 'icon' => $m['ikon'], 'pages' => array($p));
        }
        return $out;
    }

    /**
     * [GENC-08OKT26-MESIN] Sidebar untuk 6 mesin awal: [menu page => array('icon', 'aktif', 'pages' => [route urut tab],
     * 'off' => [route => true] halaman yang nonaktif / semua unitnya nonaktif)]. Menu mengarah ke halaman AKTIF pertama.
     */
    function genc_reg_sidebar() {
        $out = array();
        $set = genc_reg_inactive_set();
        $d = genc_reg_data();
        foreach (genc_reg_builtin_folders() as $k => $f) {
            $pages = genc_reg_folder_pages($k);
            $off = array();
            foreach ($pages as $p) { if (isset($set[$p . '|'])) { $off[$p] = true; } }
            if ($f['mode'] === 'units' && $f['aktif']) {
                $any = false;
                foreach ($d['unit'] as $r) { if ($r['folder'] === $k && (int) $r['aktif'] === 0) { $any = true; } }
                if ($any) {
                    $on = array();
                    foreach ($f['types'] as $t) {
                        foreach (checklist_machine_units(checklist_load_machine($t['profile'])) as $u) { if (empty($u['inactive'])) { $on[$t['page']] = true; } }
                    }
                    foreach ($pages as $p) { if (!isset($on[$p])) { $off[$p] = true; } }
                }
            }
            $out[$f['menu']] = array('icon' => $f['icon'], 'aktif' => $f['aktif'] && !$f['arsip'], 'pages' => $pages, 'off' => $off, 'title' => $f['title']);
        }
        return $out;
    }

    /** Halaman tambahan yang menandai menu bawaan aktif (sidebar): menu page => [page...]. */
    function genc_reg_menu_match() {
        $out = array();
        foreach (genc_reg_builtin_folders() as $k => $f) {
            if ($f['mode'] !== 'tables') { continue; }
            foreach (genc_reg_units_of_folder($k) as $u) { $out[$f['menu']][] = $u['page']; }
        }
        return $out;
    }

    /** Baris tambahan matriks hak akses (bagian Checklist AM): [page => nama]. */
    function genc_reg_perm_rows() {
        $out = array();
        foreach (genc_reg_builtin_folders() as $k => $f) {
            if ($f['mode'] !== 'tables') { continue; }
            foreach (genc_reg_units_of_folder($k) as $u) {
                if (!empty($u['arsip']) || $f['arsip']) { continue; }   // [GENC-08OKT26-P3] arsip: keluar dari matriks (hak akses tetap tersimpan)
                $out[$u['page']] = trim(($f['prefix'] !== '' ? genc_reg_rename_text($k, $f['prefix']) . ' ' : '') . $u['label']);
            }
        }
        foreach (genc_reg_data()['mesin'] as $p => $m) { if (empty($m['arsip'])) { $out[$p] = $m['nama']; } }
        return $out;
    }

    // ------------------------------------------------------------------
    //  ALAT BANTU TULIS (dipakai MesinController)
    // ------------------------------------------------------------------

    /** Teks 1 baris yang aman untuk DB (koneksi utf8 3-byte): tanpa tag, spasi dirapikan, emoji 4-byte -> ?, dipotong. */
    function genc_reg_clean($v, $max = 100) {
        $v = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $v, ENT_QUOTES, 'UTF-8'))));
        $v = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '?', $v);
        if ($v === null) { $v = ''; }
        return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
    }

    /** Slug untuk route/tabel: huruf kecil, angka, garis bawah. */
    function genc_reg_page_slug($text) {
        $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string) $text), '_'));
        return $s === '' ? 'mesin' : substr($s, 0, 40);
    }

    /** Nama route/tabel am_* yang belum dipakai (tabel, route bawaan, registry). */
    function genc_reg_free_page($base) {
        $pdo = genc_reg_db();
        $base = GENC_REG_PREFIX . genc_reg_page_slug($base);
        $base = substr($base, 0, 56);
        for ($i = 1; $i < 200; $i++) {
            $p = $i === 1 ? $base : $base . '_' . $i;
            $taken = genc_reg_page($p) !== null || class_exists(ucfirst($p) . 'Controller', false) || is_file(ROOT . 'app/controllers/' . ucfirst($p) . 'Controller.php');
            if (!$taken && $pdo) {
                $st = $pdo->prepare("SHOW TABLES LIKE ?");
                $st->execute(array($p));
                $taken = (bool) $st->fetch();
            }
            if (!$taken) { return $p; }
        }
        return '';
    }

    /** Salin hak akses halaman acuan ke halaman baru. @return int jumlah baris */
    function genc_reg_copy_acl($from, $to) {
        $pdo = genc_reg_db();
        if (!$pdo || $from === '' || $to === '') { return 0; }
        $st = $pdo->prepare("INSERT INTO role_permissions (role_id, page_name, action_name)
            SELECT DISTINCT a.role_id, ?, a.action_name FROM role_permissions a
            WHERE a.page_name = ? AND NOT EXISTS (SELECT 1 FROM role_permissions b WHERE b.role_id = a.role_id AND b.page_name = ? AND b.action_name = a.action_name)");
        $st->execute(array($to, $from, $to));
        return $st->rowCount();
    }

    function genc_reg_drop_acl($page) {
        $pdo = genc_reg_db();
        if (!$pdo || strpos($page, GENC_REG_PREFIX) !== 0) { return 0; }
        $st = $pdo->prepare("DELETE FROM role_permissions WHERE page_name = ?");
        $st->execute(array($page));
        return $st->rowCount();
    }

    /** DDL tabel jenis baru. */
    function genc_reg_create_table_sql($page, $item_cols) {
        $cols = array();
        foreach ($item_cols as $c) { $cols[] = "`$c` VARCHAR(20) NOT NULL DEFAULT ''"; }
        return "CREATE TABLE `$page` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `date_created` DATETIME NOT NULL,
            `user_created` VARCHAR(100) NOT NULL DEFAULT '',
            `unit` VARCHAR(100) NOT NULL DEFAULT ''," . (empty($cols) ? '' : "\n            " . implode(",\n            ", $cols) . ",") . "
            `keterangan` TEXT NULL,
            `kondisi` VARCHAR(10) NULL,
            `approval` VARCHAR(50) NULL,
            `user_approve` VARCHAR(100) NULL,
            `date_approve` DATETIME NULL,
            `date_update` DATETIME NULL,
            `user_update` VARCHAR(100) NULL,
            PRIMARY KEY (`id`), KEY `ix_{$page}_date` (`date_created`), KEY `ix_{$page}_unit` (`unit`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    /** Jumlah record di tabel (null kalau tabel tidak ada). */
    function genc_reg_table_count($table) {
        $pdo = genc_reg_db();
        if (!$pdo) { return null; }
        try { return (int) $pdo->query("SELECT COUNT(*) FROM `" . str_replace('`', '', $table) . "`")->fetchColumn(); }
        catch (\Throwable $e) { return null; }
    }

    /** Jumlah record per unit (folder 1-tabel-banyak-unit): [norm label => n]. */
    function genc_reg_unit_counts($table, $unit_field) {
        $pdo = genc_reg_db();
        $out = array();
        if (!$pdo) { return $out; }
        try {
            $rows = $pdo->query("SELECT `" . str_replace('`', '', $unit_field) . "` AS u, COUNT(*) AS n FROM `" . str_replace('`', '', $table) . "` GROUP BY `" . str_replace('`', '', $unit_field) . "`")->fetchAll();
            foreach ($rows as $r) {
                $k = checklist_norm_unit($r['u']);
                $out[$k] = (isset($out[$k]) ? $out[$k] : 0) + (int) $r['n'];
            }
        } catch (\Throwable $e) { }
        return $out;
    }
}
