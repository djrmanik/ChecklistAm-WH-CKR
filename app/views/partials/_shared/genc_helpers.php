<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/genc_helpers.php              23 Sep 2026
 *
 *  Fungsi tampilan GENERASI C (dipakai halaman conveyor, nanti mesin lain).
 *  Cuma format & escape - tidak ada query, tidak ada logika bisnis.
 *  Pasangannya: assets/css/checklist-genc.css & assets/js/checklist-genc.js
 * ============================================================================
 */

require_once __DIR__ . '/checklist_helpers.php'; // checklist_user_initial(), checklist_display_name(), checklist_nama_bulan()

/** Versi aset - ganti angka ini kalau CSS/JS diubah, supaya browser tidak pakai cache lama. */
if (!defined('GENC_ASSET_VERSION')) { define('GENC_ASSET_VERSION', '2026101601'); } // [GENC-16OKT26] status temuan di semua halaman, Pantau, panel scroll, Mode edit bebas penuh + ikon (dulu 2026101501) // [GENC-15OKT26-TEMUAN] NOK History = temuan + status beres (dulu 2026101403) // [GENC-14OKT26-TEKS2/KOTAK] teks dinamis, hapus teks, teks tambahan, susunan isi kartu (dulu 2026101402) // [GENC-14OKT26-SUSUNAN/TEMA] susunan, posisi bebas, tema (dulu 2026101401) // [GENC-13OKT26-TAMPILAN] Mode edit tampilan 4A (dulu 2026101302) // [GENC-13OKT26c] riwayat hapus permanen ringkas (dulu 2026101301) [GENC-13OKT26] riwayat ringkas + layar penuh, hapus permanen (dulu 2026100803) // [GENC-08OKT26-P3] paket 3 (dulu 2026100801) [GENC-08OKT26] kartu mesin lipat, mesin aktif/nonaktif, 80 ikon + cari (dulu 2026100701) [GENC-07OKT26-FOTO] banyak foto per item, kolase gambar bagian mesin (dulu 2026100603) [GENC-06OKT26] Mesin & Unit, tab banyak, inisial, tanggal bisa diklik, isi bawaan + kunci (dulu 2026100602)

if (!function_exists('genc_e')) {
    function genc_e($v) { return htmlspecialchars((string) ($v === null ? '' : $v), ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('genc_assets_css')) {
    function genc_assets_css() {
        return '<link rel="stylesheet" href="' . genc_e(set_url('assets/css/checklist-genc.css')) . '?v=' . GENC_ASSET_VERSION . '">';
    }
}
if (!function_exists('genc_assets_js')) {
    function genc_assets_js() {
        return '<script src="' . genc_e(set_url('assets/js/checklist-genc.js')) . '?v=' . GENC_ASSET_VERSION . '"></script>';
    }
}

if (!function_exists('genc_nama_hari')) {
    function genc_nama_hari($ts) {
        $h = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
        return $h[(int) date('w', $ts)];
    }
}

/** "Rabu, 23 Sep 2026" (pendek) atau "Rabu, 23 September 2026" (panjang). */
if (!function_exists('genc_date')) {
    function genc_date($datetime, $long = false) {
        $ts = is_numeric($datetime) ? (int) $datetime : strtotime((string) $datetime);
        if (!$ts) { return (string) $datetime; }
        $b = checklist_nama_bulan();
        $bulan = $b[(int) date('n', $ts)];
        if (!$long) { $bulan = substr($bulan, 0, 3); }
        return genc_nama_hari($ts) . ', ' . date('j', $ts) . ' ' . $bulan . ' ' . date('Y', $ts);
    }
}

/** "07:32" - kosong kalau kolomnya cuma tanggal (00:00:00). */
if (!function_exists('genc_time')) {
    function genc_time($datetime) {
        $ts = strtotime((string) $datetime);
        if (!$ts) { return ''; }
        $t = date('H:i', $ts);
        return ($t === '00:00' && strlen(trim((string) $datetime)) <= 10) ? '' : $t;
    }
}

/** Nama orang untuk ditampilkan (pakai daftar nama resmi yang sama dengan report). */
if (!function_exists('genc_person_name')) {
    function genc_person_name($username) {
        $n = checklist_display_name($username);
        return $n !== '' ? $n : (string) $username;
    }
}

/** Avatar inisial + nama. */
if (!function_exists('genc_person')) {
    function genc_person($username, $sub = '') {
        if (trim((string) $username) === '') { return '<span class="genc-muted">&ndash;</span>'; }
        $ini = checklist_user_initial($username);
        // [GENC-06OKT26-INISIAL] inisial dari menu Users boleh 4 huruf -> huruf diperkecil (3 huruf: HTML sama dengan dulu)
        $html  = '<div class="genc-person"><span class="genc-avatar' . (strlen($ini) > 3 ? ' genc-avatar--4' : '') . '" aria-hidden="true">' . genc_e(substr($ini, 0, 4)) . '</span>';
        $html .= '<div style="min-width:0"><div class="genc-person__name">' . genc_e(genc_person_name($username)) . '</div>';
        if ($sub !== '') { $html .= '<div class="genc-small genc-muted">' . $sub . '</div>'; }
        return $html . '</div></div>';
    }
}

/**
 * [GENC-08OKT26-P3] Label tombol hasil cek (menu Master Select -> "Pilihan hasil cek").
 * Kode yang disimpan ke database & dicetak di report TETAP OK / NOK / PR; yang bisa diganti hanya labelnya.
 * Tabel genc_pilihan belum ada / kosong -> label awal (Baik / Tidak baik / Perawatan).
 */
if (!function_exists('genc_pilihan_default')) {
    function genc_pilihan_default() {
        return array('OK' => 'Baik', 'NOK' => 'Tidak baik', 'PR' => 'Perawatan');
    }
}
if (!function_exists('genc_pilihan_labels')) {
    function genc_pilihan_labels($reset = false) {
        static $out = null;
        if ($reset) { $out = null; }
        if ($out !== null) { return $out; }
        $out = genc_pilihan_default();
        try {
            if (!defined('DB_HOST') || !defined('DB_NAME')) { return $out; }
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . (defined('DB_PORT') && DB_PORT !== '' ? ';port=' . DB_PORT : '') . ';charset=' . (defined('DB_CHARSET') && DB_CHARSET !== '' ? DB_CHARSET : 'utf8'),
                DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
            foreach ($pdo->query("SELECT kode, label FROM genc_pilihan")->fetchAll() as $r) {
                $k = strtoupper((string) $r['kode']);
                if (isset($out[$k]) && trim((string) $r['label']) !== '') { $out[$k] = trim((string) $r['label']); }
            }
        } catch (\Throwable $e) { }
        return $out;
    }
}
if (!function_exists('genc_pilihan_label')) {
    function genc_pilihan_label($kode) {
        $l = genc_pilihan_labels();
        $k = strtoupper(trim((string) $kode));
        return isset($l[$k]) ? $l[$k] : (string) $kode;
    }
}

/** Label & gaya untuk nilai item. */
if (!function_exists('genc_value_meta')) {
    function genc_value_meta($v) {
        $orig = trim((string) $v);
        $v = strtoupper($orig);
        if ($v === 'OK')  { return array('key' => 'ok',  'label' => genc_pilihan_label('OK'),  'icon' => 'fa-check'); }
        if ($v === 'NOK') { return array('key' => 'nok', 'label' => genc_pilihan_label('NOK'), 'icon' => 'fa-times'); }
        if ($v === 'PR')  { return array('key' => 'pr',  'label' => genc_pilihan_label('PR'),  'icon' => 'fa-wrench'); }
        // [GENC-24SEP26-DUMPING] isian teks bebas lama ditampilkan apa adanya (bukan huruf besar)
        return array('key' => '', 'label' => ($v === '' ? 'Belum diisi' : $orig), 'icon' => 'fa-question');
    }
}
if (!function_exists('genc_value_badge')) {
    function genc_value_badge($v) {
        $m = genc_value_meta($v);
        return '<span class="genc-badge' . ($m['key'] ? ' genc-badge--' . $m['key'] : '') . '"><i class="fa ' . $m['icon'] . '"></i> ' . genc_e($m['label']) . '</span>';
    }
}

/** Strip 1 kotak per item. */
if (!function_exists('genc_strip')) {
    function genc_strip($row, $items) {
        $html = '<span class="genc-strip" role="img" aria-label="Hasil per item">';
        foreach ($items as $it) {
            $m = genc_value_meta(isset($row[$it['db']]) ? $row[$it['db']] : '');
            $html .= '<span class="genc-strip__i' . ($m['key'] ? ' genc-strip__i--' . $m['key'] : '') . '" title="' . genc_e($it['no'] . '. ' . $it['part'] . ': ' . $m['label']) . '"></span>';
        }
        return $html . '</span>';
    }
}

/* ---------------------------------------------------------------- [GENC-16OKT26-TEMUAN2] status temuan */

/** Kunci unit untuk pop-up NOK History (?buka=). Sama di Home, halaman mesin & NOK History. */
if (!function_exists('genc_tm_key')) {
    function genc_tm_key($page, $unit, $raw = '') { return 'u' . substr(md5($page . '|' . $unit . '|' . $raw), 0, 10); }
}

/** Tanggal pendek temuan: "Rab 14 Okt" (+ tahun kalau bukan tahun ini). */
if (!function_exists('genc_tm_tgl')) {
    function genc_tm_tgl($ymd) {
        $ts = strtotime((string) $ymd);
        if (!$ts) { return (string) $ymd; }
        $bln = checklist_nama_bulan();
        $s = substr(genc_nama_hari($ts), 0, 3) . ' ' . date('j', $ts) . ' ' . substr($bln[(int) date('n', $ts)], 0, 3);
        return (int) date('Y', $ts) !== (int) date('Y') ? $s . ' ' . date('Y', $ts) : $s;
    }
}

/** Lama temuan: "hari ini" / "1 hari" (masih terbuka) atau "hari itu juga" / "2 hari" (sudah beres). */
if (!function_exists('genc_tm_lama')) {
    function genc_tm_lama($n, $open) {
        $gx = function ($s) { return function_exists('gx_lit') ? gx_lit($s, 'partials/_shared/genc_helpers.php') : $s; };
        if ((int) $n <= 0) { return $open ? $gx('hari ini') : $gx('hari itu juga'); }
        return (int) $n . ' ' . $gx('hari');
    }
}

/**
 * Label status temuan sebuah checklist (baris daftar & detail). Checklist-nya sendiri tetap tercatat NOK/PR;
 * label ini hanya bilang apakah temuan itu sudah dinilai Baik lagi di checklist berikutnya.
 * $st = '_tm' dari GencChecklistBase::genc_attach_temuan() / genc_temuan_rec_status().
 */
if (!function_exists('genc_tm_badge')) {
    function genc_tm_badge($st) {
        if (!$st || ($st['open'] + $st['done']) === 0) { return ''; }
        $gx = function ($s) { return function_exists('gx_lit') ? gx_lit($s, 'partials/_shared/genc_helpers.php') : $s; };
        if ($st['open'] === 0) {
            $tip = genc_pilihan_label('OK') . ' lagi di checklist ' . genc_date($st['end_date']) . ($st['end_user'] !== '' ? ', dicek ' . genc_person_name($st['end_user']) : '');
            return '<span class="genc-tmst genc-tmst--done" title="' . genc_e($tip) . '"><i class="fa fa-check-circle" aria-hidden="true"></i> ' . $gx('Beres') . ' ' . genc_e(genc_tm_tgl($st['end_date'])) . '</span>';
        }
        if ($st['done'] === 0) {
            return '<span class="genc-tmst genc-tmst--open"><i class="fa fa-clock-o" aria-hidden="true"></i> ' . $gx('Belum beres') . '</span>';
        }
        return '<span class="genc-tmst genc-tmst--part" title="' . genc_e($st['open'] . ' item masih belum ' . genc_pilihan_label('OK')) . '"><i class="fa fa-adjust" aria-hidden="true"></i> '
             . (int) $st['done'] . ' ' . $gx('dari') . ' ' . (int) ($st['open'] + $st['done']) . ' ' . $gx('beres') . '</span>';
    }
}

/** Status approval sebagai badge. */
if (!function_exists('genc_approval_badge')) {
    function genc_approval_badge($row) {
        $a = isset($row['approval']) ? trim((string) $row['approval']) : '';
        // [GENC-24SEP26-DUMPING] status dinilai controller (_approved) kalau ada
        $is_ok = isset($row['_approved']) ? $row['_approved'] : ($a !== '' && stripos($a, 'not') === false);
        if ($is_ok) {
            $who = isset($row['user_approve']) ? trim((string) $row['user_approve']) : '';
            if (!preg_match('/[A-Za-z]/', $who)) { $who = ''; }   // [GENC-24SEP26-AGV] isian lama '-' / '.' bukan nama -> tidak ditulis "oleh -"
            $gx = function ($s) { return function_exists('gx_lit') ? gx_lit($s, 'partials/_shared/genc_helpers.php') : $s; };   // [GENC-13OKT26-TAMPILAN]
            return '<span class="genc-badge genc-badge--ok"><i class="fa fa-check-circle"></i> ' . $gx('Approved') . '</span>'
                 . ($who !== '' ? '<div class="genc-small genc-muted" style="margin-top:3px">' . $gx('oleh') . ' ' . genc_e(genc_person_name($who)) . '</div>' : '');
        }
        if ($a !== '' && stripos($a, 'not') !== false) { return '<span class="genc-badge genc-badge--nok">' . genc_e($a) . '</span>'; }
        return '<span class="genc-badge genc-badge--pending"><i class="fa fa-clock-o"></i> ' . (function_exists('gx_lit') ? gx_lit('Menunggu', 'partials/_shared/genc_helpers.php') : 'Menunggu') . '</span>';
    }
}

/** URL halaman ini + parameter (parameter kosong dibuang). */
if (!function_exists('genc_url')) {
    function genc_url($path, $params = array()) {
        $params = array_filter($params, function ($v) { return $v !== null && $v !== ''; });
        return set_url($path) . (empty($params) ? '' : '?' . http_build_query($params));
    }
}

/** [GENC-24SEP26-DUMPING] Baris untuk tampilan: nilai item diganti hasil penilaian controller (_vals). */
if (!function_exists('genc_display_row')) {
    function genc_display_row($row) {
        return (is_array($row) && isset($row['_vals'])) ? array_merge($row, $row['_vals']) : $row;
    }
}

/**
 * [GENC-24SEP26-DUMPING] Tab di atas halaman (K12). $target: 'list' | 'add' | 'record'.
 * [GENC-24SEP26-AGV] tab boleh punya 'unit' (tab per unit, K7) dan 'group' (judul kelompok,
 * mis. "Table Top Lift" / "Counterbalance"). Tanpa 'unit'/'group' -> HTML sama persis dengan dumping.
 */
if (!function_exists('genc_tabs')) {
    function genc_tabs($g, $target = 'list', $params = array()) {
        if (empty($g['tabs'])) { return ''; }
        $grouped = false;
        foreach ($g['tabs'] as $t) { if (!empty($t['group'])) { $grouped = true; break; } }
        $html = '<nav class="genc-tabs' . ($grouped ? ' genc-tabs--grouped' : '') . '" aria-label="' . genc_e($g['heading']) . '"><div class="genc-tabs__list" role="tablist">';
        $cur = null;
        foreach ($g['tabs'] as $t) {
            if ($grouped && $t['group'] !== $cur) {
                if ($cur !== null) { $html .= '</div></div>'; }
                $cur = $t['group'];
                $html .= '<div class="genc-tabs__group" role="presentation"><span class="genc-tabs__group-label">' . genc_e($cur) . '</span><div class="genc-tabs__group-items">';
            }
            $up  = (isset($t['unit']) && $t['unit'] !== '') ? array('unit' => $t['unit']) : array();
            $url = ($target === 'add' && $t['can_add']) ? genc_url($t['page'] . '/add', $up) : genc_url($t['page'], array_merge($params, $up));
            $html .= '<a class="genc-tab' . ($t['active'] ? ' is-active' : '') . '" role="tab" aria-selected="' . ($t['active'] ? 'true' : 'false') . '"'
                   . ($t['active'] ? ' aria-current="page"' : '') . ' href="' . genc_e($url) . '">'
                   . '<span class="genc-tab__label">' . genc_e($t['label']) . '</span>'
                   . ($t['sub'] !== '' ? '<span class="genc-tab__sub">' . genc_e($t['sub']) . '</span>' : '')
                   . '</a>';
        }
        if ($grouped && $cur !== null) { $html .= '</div></div>'; }
        $html .= '</div>';
        // [GENC-06OKT26-MESIN] unit banyak (> GENC_TABS_MANY, mis. setelah unit ditambah lewat menu Mesin & Unit):
        // tab tetap bisa digeser + tombol "Semua unit (N)" berisi daftar & kotak cari. <= 8 tab: HTML sama dengan dulu.
        if (count($g['tabs']) > GENC_TABS_MANY) {
            $html = str_replace('<nav class="genc-tabs', '<nav class="genc-tabs genc-tabs--many', $html);
            $html .= '<div class="genc-menu genc-tabs__all" data-genc-menu>'
                   . '<button type="button" class="genc-btn genc-btn--sm" data-genc-menu-toggle aria-haspopup="true" aria-expanded="false"><i class="fa fa-th-list"></i> Semua unit <span class="genc-chip__count">' . count($g['tabs']) . '</span> <i class="fa fa-angle-down"></i></button>'
                   . '<div class="genc-menu__list genc-tabs__alllist" role="menu">'
                   . '<div class="genc-tabs__find" data-genc-menu-keep><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari unit..." aria-label="Cari unit" data-genc-tabfilter autocomplete="off"></div>';
            $cur = null;
            foreach ($g['tabs'] as $t) {
                $grp = isset($t['group']) ? (string) $t['group'] : '';
                if ($grp !== '' && $grp !== $cur) { $html .= '<div class="genc-menu__label" data-genc-tabgroup>' . genc_e($grp) . '</div>'; $cur = $grp; }
                $up  = (isset($t['unit']) && $t['unit'] !== '') ? array('unit' => $t['unit']) : array();
                $url = ($target === 'add' && $t['can_add']) ? genc_url($t['page'] . '/add', $up) : genc_url($t['page'], array_merge($params, $up));
                $html .= '<a class="genc-menu__item' . ($t['active'] ? ' is-active' : '') . '" role="menuitem" href="' . genc_e($url) . '" data-genc-tabname="' . genc_e(strtolower($t['label'] . ' ' . $t['sub'] . ' ' . $grp)) . '">'
                       . '<i class="fa ' . ($t['active'] ? 'fa-dot-circle-o' : 'fa-circle-o') . '" aria-hidden="true"></i><span>' . genc_e($t['label']) . ($t['sub'] !== '' ? '<small>' . genc_e($t['sub']) . '</small>' : '') . '</span></a>';
            }
            $html .= '<div class="genc-tabs__none" hidden>Tidak ada unit yang cocok.</div></div></div>';
        }
        return $html . '</nav>';
    }
}
/**
 * [GENC-06OKT26-MESIN] Daftar item checklist (hanya baca) per bagian - panel kanan menu Mesin & Unit.
 * $sections = profil (checklist_load_machine)['sections'].
 */
if (!function_exists('genc_preview_items')) {
    function genc_preview_items($sections) {
        $html = ''; $n = 0;
        foreach ((array) $sections as $s) {
            if (empty($s['items'])) { continue; }
            $html .= '<div class="gm-prev"><div class="gm-prev__sec">' . genc_e(isset($s['short']) ? $s['short'] : $s['title']) . '</div><ol class="gm-prev__list">';
            foreach ($s['items'] as $it) {
                $n++;
                $foto = !empty($it['foto']) ? '<img src="' . genc_e(set_url($it['foto'])) . '" alt="" width="44" height="34">' : '<span class="gm-prev__nofoto" aria-hidden="true"><i class="fa fa-camera"></i></span>';
                if (!empty($it['fotos']) && count($it['fotos']) > 1) { $foto = '<span class="gm-prev__ph">' . $foto . '<span class="gm-prev__n" title="' . count($it['fotos']) . ' foto">' . count($it['fotos']) . '</span></span>'; }   // [GENC-07OKT26-FOTO]
                $html .= '<li><span class="gm-prev__no">' . (int) $it['no'] . '</span>' . $foto . '<span class="gm-prev__txt"><b>' . genc_e($it['part']) . '</b>'
                       . (!empty($it['standar']) ? '<small>' . genc_e($it['standar']) . '</small>' : '') . '</span></li>';
            }
            $html .= '</ol></div>';
        }
        return $n === 0 ? '<p class="genc-muted">Belum ada item.</p>' : $html;
    }
}
/** [GENC-06OKT26-MESIN] Mulai berapa tab tombol "Semua unit" muncul (AGV sekarang 8 tab -> tidak berubah). */
if (!defined('GENC_TABS_MANY')) { define('GENC_TABS_MANY', 8); }

/**
 * [GENC-24SEP26-AGV] Kotak foto untuk item TANPA foto (K19): bukan kotak abu-abu bisu, tapi
 * tombol "Belum ada foto" yang kalau diklik menjelaskan kenapa kosong. Teks dari controller
 * (conf 'foto_kosong'), default umum kalau tidak diisi.
 */
if (!function_exists('genc_thumb_empty')) {
    function genc_thumb_empty($it, $g) {
        $msg = !empty($g['foto_kosong']) ? $g['foto_kosong']
             : 'Foto part ini belum tersedia — bukan gambar yang rusak.';
        return '<button type="button" class="genc-thumb genc-thumb--none" data-genc-info="Foto ' . genc_e($it['no'] . '. ' . $it['part']) . ' belum ada"'
             . ' data-genc-info-msg="' . genc_e($msg) . '" aria-label="Foto ' . genc_e($it['part']) . ' belum ada - klik untuk penjelasan">'
             . '<i class="fa fa-camera" aria-hidden="true"></i><span>Belum ada foto</span></button>';
    }
}
