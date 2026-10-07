<?php
/**
 * ============================================================================
 *  REGISTRY MESIN & UNIT  (tambah mesin tanpa coding)     [GENC-06OKT26-MESIN]
 *
 *  Sumber data menu "Mesin & Unit" (MesinController). Dua tabel kecil:
 *    genc_mesin  jenis mesin BARU (folder baru di sidebar): nama, area, ikon, item checklist (JSON)
 *    genc_unit   unit BARU (tab baru) + catatan untuk unit BAWAAN (nonaktif / nama tab)
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
        );
    }

    /** Buat tabel registry kalau belum ada. @return string '' = beres, selain itu pesan error */
    function genc_reg_ensure_tables() {
        $pdo = genc_reg_db();
        if (!$pdo) { return 'Koneksi database gagal.'; }
        try {
            foreach (genc_reg_schema_sql() as $sql) { $pdo->exec($sql); }
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
        $data = array('ok' => false, 'mesin' => array(), 'unit' => array(), 'profil' => array(), 'profil_ok' => false);
        $pdo = genc_reg_db();
        if (!$pdo) { return $data; }
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
                $r['items_arr'] = genc_reg_items_decode($r['items']);
                $data['mesin'][$r['page']] = $r;
            }
            foreach ($u as $r) {
                $r['alias_arr'] = genc_reg_json_list($r['alias']);
                $data['unit'][(int) $r['id']] = $r;
            }
        } catch (\Throwable $e) {
            $data = array('ok' => false, 'mesin' => array(), 'unit' => array(), 'profil' => $data['profil'], 'profil_ok' => $data['profil_ok']);
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
                $out[$k][] = array(
                    'db'      => (string) $it['db'],
                    'part'    => isset($it['part']) ? (string) $it['part'] : '',
                    'standar' => isset($it['standar']) ? (string) $it['standar'] : '',
                    'metode'  => isset($it['metode']) ? (string) $it['metode'] : '',
                    'alat'    => isset($it['alat']) ? (string) $it['alat'] : '',
                    'durasi'  => isset($it['durasi']) ? (string) $it['durasi'] : '',
                    'foto'    => isset($it['foto']) ? (string) $it['foto'] : '',
                    'aktif'   => !isset($it['aktif']) || !empty($it['aktif']),
                );
            }
        }
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
        return array(
            'palletmover' => array('title' => 'Pallet Mover & Stacker', 'icon' => 'fa-th-large', 'mode' => 'units', 'menu' => 'palletmover',
                'types' => array(
                    array('profile' => 'palletmover',   'page' => 'palletmover', 'label' => 'Pallet Mover'),
                    array('profile' => 'palletstacker', 'page' => 'palletmover', 'label' => 'Pallet Stacker'),
                ),
                'kunci_awal' => false, 'unit_hint' => 'Contoh: Pallet Mover 5', 'tab_note' => 'Label "Pallet Mover N" tampil sebagai "PM N" di tab.'),
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
                'kunci_awal' => false, 'unit_hint' => 'Contoh: AGV TTL 11 atau AGV CB 4', 'tab_note' => 'Awalan "AGV " tidak ditulis di tab.'),
            'dumping' => array('title' => 'Mesin Dumping', 'icon' => 'fa-download', 'mode' => 'tables', 'menu' => 'kir3p01dp001',
                'profile' => 'dumping', 'source' => 'kir3p01dp001', 'prefix' => 'Mesin Dumping',
                'pages' => array(
                    'kir3p01dp001' => array('label' => 'KIR3', 'sub' => 'KIR3P01DP001'),
                    'kir6p01dp001' => array('label' => 'KIR6', 'sub' => 'KIR6P01DP001'),
                    'kir7p01dp001' => array('label' => 'KIR7', 'sub' => 'KIR7P01DP001'),
                ),
                'kunci_awal' => false, 'unit_hint' => 'Nama tab, contoh: KIR8', 'kode_hint' => 'Kode mesin di report, contoh: KIR8P01DP001'),
            'geprek' => array('title' => 'Mesin Geprek', 'icon' => 'fa-compress', 'mode' => 'tables', 'menu' => 'mesin_geprek',
                'profile' => 'mesin_geprek', 'source' => 'mesin_geprek', 'prefix' => '',
                'pages' => array('mesin_geprek' => array('label' => 'Geprek 1', 'sub' => '')),
                'kunci_awal' => false, 'unit_hint' => 'Nama tab, contoh: Geprek 2', 'kode_hint' => 'Kode / nama mesin di report (boleh kosong)'),
            'conveyor' => array('title' => 'Conveyor', 'icon' => 'fa-exchange', 'mode' => 'tables', 'menu' => 'conveyor',
                'profile' => 'conveyor', 'source' => 'conveyor', 'prefix' => '',
                'pages' => array('conveyor' => array('label' => 'Conveyor 1', 'sub' => '')),
                'kunci_awal' => false, 'unit_hint' => 'Nama tab, contoh: Conveyor 2', 'kode_hint' => 'Kode / nama mesin di report (boleh kosong)'),
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
                if ((int) $r['builtin'] === 1 && $r['profile'] === $profile && $r['slug'] === $slug && (int) $r['aktif'] === 0) { $u['inactive'] = true; }
            }
            $out[] = $u;
        }
        foreach ($d['unit'] as $r) {
            if ((int) $r['builtin'] !== 0 || $r['profile'] !== $profile) { continue; }
            $bf = genc_reg_builtin_folders();
            if (!isset($bf[$r['folder']]) || $bf[$r['folder']]['mode'] !== 'units') { continue; }   // jenis baru: genc_reg_profile(); 1-tabel-per-unit: tab sendiri
            $u = array('label' => $r['label'], 'alias' => $r['alias_arr'], 'reg_id' => (int) $r['id']);
            if ((int) $r['aktif'] === 0) { $u['inactive'] = true; }
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
                if ($it['foto'] !== '' && is_file(ROOT . $it['foto'])) { $row['foto'] = $it['foto']; }
                $items[] = $row;
            }
            $slots = array_keys(genc_reg_photo_slots()); $slot = $slots[count($sections)];   // [GENC-06OKT26-ISI] gambar report opsional
            $sections[] = array('key' => $k, 'title' => $s['title'], 'short' => $s['short'], 'standard_label' => $s['standard_label'],
                'photo' => $slot, 'photo_label' => ($k === 'cleaning' ? 'Gambar Bagian Mesin' : ''), 'blank_rows' => empty($items) ? 2 : 0, 'items' => $items);
        }
        $units = array();
        foreach (genc_reg_units_of_folder($name) as $r) {
            $u = array('label' => $r['label'], 'alias' => $r['alias_arr'], 'reg_id' => (int) $r['id']);
            if ((int) $r['aktif'] === 0) { $u['inactive'] = true; }
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
                foreach (array('part', 'standar', 'metode', 'alat', 'durasi', 'foto') as $f) { $row[$f] = isset($it[$f]) ? (string) $it[$f] : ''; }
                $out[$k][] = $row;
            }
        }
        return $out;
    }

    /** Gambar report yang diganti: [atas|tengah|bawah => array('path','w','h')] ('' path = disembunyikan). */
    function genc_reg_ov_gambar($profile) {
        $r = genc_reg_ov_row($profile);
        $out = array();
        if (!$r || $r['gambar'] === null || $r['gambar'] === '') { return $out; }
        $raw = json_decode((string) $r['gambar'], true);
        if (!is_array($raw)) { return $out; }
        foreach (genc_reg_photo_slots() as $k => $lbl) {
            if (!isset($raw[$k]) || !is_array($raw[$k])) { continue; }
            $p = isset($raw[$k]['path']) ? str_replace('\\', '/', (string) $raw[$k]['path']) : '';
            if ($p !== '' && (strpos($p, '..') !== false || !is_file(ROOT . $p))) { continue; }   // file hilang -> kembali ke bawaan
            $out[$k] = array('path' => $p, 'w' => max(20, min(152, (int) (isset($raw[$k]['w']) ? $raw[$k]['w'] : 152))), 'h' => max(20, min(260, (int) (isset($raw[$k]['h']) ? $raw[$k]['h'] : 135))));
        }
        return $out;
    }

    /** Slot gambar bagian mesin di report (kolom kiri), per bagian checklist. */
    function genc_reg_photo_slots() {
        return array('atas' => 'Pembersihan', 'tengah' => 'Pelumasan', 'bawah' => 'Pengecekan');
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
                    $foto = isset($it['foto']) ? (string) $it['foto'] : '';
                    if ($foto !== '' && is_file(ROOT . $foto)) { $row['foto'] = $foto; } else { unset($row['foto']); }
                    if (!empty($it['baru'])) { $row['baru'] = true; }
                    $items[] = $row;
                }
                $was = count((array) $s['items']);
                $conf['sections'][$si]['items'] = $items;
                $bl = isset($s['blank_rows']) ? (int) $s['blank_rows'] : 0;
                $conf['sections'][$si]['blank_rows'] = empty($items) ? ($was === 0 ? $bl : 2) : ($was === 0 ? 0 : $bl);
            }
            $conf['hidden_cols'] = $hidden;
            $conf['isi_menu'] = true;
        }
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
                    'alat' => (string) $it['alat'], 'durasi' => (string) $it['durasi'], 'foto' => isset($it['foto']) ? (string) $it['foto'] : '', 'aktif' => true, 'baru' => false);
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
                    'alat' => (string) $it['alat'], 'durasi' => (string) $it['durasi'], 'foto' => isset($it['foto']) ? (string) $it['foto'] : '', 'aktif' => true, 'baru' => false);
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
        return $set;
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

    /** Ikon yang boleh dipilih untuk jenis baru (semua ada di Font Awesome 4.5 bawaan phpRAD). */
    function genc_reg_icons() {
        return array('fa-cube', 'fa-cubes', 'fa-cogs', 'fa-wrench', 'fa-bolt', 'fa-industry', 'fa-truck', 'fa-shopping-cart',
            'fa-cart-plus', 'fa-archive', 'fa-plug', 'fa-fire-extinguisher', 'fa-tachometer', 'fa-battery-full', 'fa-arrows-v',
            'fa-arrows-h', 'fa-recycle', 'fa-tint', 'fa-filter', 'fa-lightbulb-o', 'fa-car', 'fa-print', 'fa-balance-scale', 'fa-magnet');
    }

    /**
     * Daftar mesin untuk Home & ringkasan Approval/NOK History. Urut = menu.
     * Tanpa isi registry: SAMA PERSIS dengan daftar lama di GencHubReader / PalletmoverController.
     */
    function genc_reg_hub_machines() {
        $out = array();
        foreach (genc_reg_builtin_folders() as $k => $f) {
            $pages = array();
            if ($f['mode'] === 'units') { foreach ($f['types'] as $t) { $pages[$t['page']] = true; } }
            else {
                foreach ($f['pages'] as $p => $t) { $pages[$p] = true; }
                foreach (genc_reg_units_of_folder($k) as $u) { $pages[$u['page']] = true; }
            }
            $out[] = array('title' => $f['title'], 'icon' => $f['icon'], 'pages' => array_keys($pages));
        }
        foreach (genc_reg_active_jenis() as $p => $m) {
            $out[] = array('title' => $m['nama'], 'icon' => $m['ikon'], 'pages' => array($p));
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
            foreach (genc_reg_units_of_folder($k) as $u) { $out[$u['page']] = trim(($f['prefix'] !== '' ? $f['prefix'] . ' ' : '') . $u['label']); }
        }
        foreach (genc_reg_data()['mesin'] as $p => $m) { $out[$p] = $m['nama']; }
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
