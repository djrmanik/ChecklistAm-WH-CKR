<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/checklist_report_plan.php      [GENC-07OKT26-FOTO]
 *
 *  RENCANA HALAMAN REPORT + KOLASE FOTO OTOMATIS (dipakai checklist_report_body.php)
 *
 *  1. KOLASE. Bagian yang gambarnya bermode 'auto' memakai foto item bagian itu
 *     (urut item, diberi nomor = nomor item) + foto tambahan yang ditunjuk ke item.
 *     Foto digabung jadi SATU gambar JPG (GD) per bagian per halaman, karena
 *     dompdf 0.8.3 hanya rapi dengan 1 <img> ukuran absolut per sel. File disimpan
 *     di uploads/mesin/<profil>/kolase/ (cache; nama = sidik foto, dibuat ulang
 *     otomatis kalau foto / urutan / nomor berubah).
 *  2. TINGGI. Tinggi tiap bagian diperkirakan dengan metrik huruf Helvetica milik
 *     dompdf (teks dibungkus per kata seperti dompdf). Kolase disusun supaya pas
 *     dengan tinggi tabel bagian itu (1 atau 2 kolom foto, rasio 4:3 s/d 16:9).
 *     Kalau foto tetap lebih tinggi, baris item ikut ditinggikan (rata), bukan
 *     foto / huruf yang dikecilkan.
 *  3. HALAMAN. Kalau isi melebihi 1 halaman A4: pindah halaman di antara bagian;
 *     bagian yang sendirian pun tidak muat dipecah per item -> judul "- LANJUTAN"
 *     di halaman berikutnya, kop & kolom tanggal diulang, kolase ikut item di
 *     halaman itu, baris Paraf di potongan terakhir. Ukuran huruf, foto & tabel
 *     TIDAK dikecilkan.
 *
 *  Report yang muat 1 halaman & tanpa kolase menghasilkan markup lama PERSIS
 *  (dites identik dengan kode 6 Okt untuk semua mesin, semua role).
 * ============================================================================
 */

require_once __DIR__ . '/checklist_helpers.php';

if (!function_exists('checklist_rpt_plan')) {

    /** Tinggi halaman yang bisa diisi (pt): A4 landscape 595.28 - margin 2 x 6mm (17.01). Kop dihitung sendiri. */
    define('CHECKLIST_RPT_PAGE_PT', 561.26);
    /** Cadangan (pt): dompdf butuh +-8pt ruang sisa (baris kosong pembungkus) + cadangan 4pt; dikalibrasi 7 Okt (650 halaman acak: 0 terdorong). */
    if (!defined('CHECKLIST_RPT_SAFETY_PT')) { define('CHECKLIST_RPT_SAFETY_PT', 12.0); }
    /** Lebar kolom foto kiri (px) & jarak antar foto kolase (px). */
    define('CHECKLIST_RPT_IMG_W', 152);
    define('CHECKLIST_RPT_GAP', 4);
    /** Versi gambar kolase (naikkan kalau cara menggambar berubah -> cache dibuat ulang). */
    define('CHECKLIST_RPT_KOLASE_VER', 'k1');

    // ------------------------------------------------------------------
    //  METRIK HURUF (Helvetica / Helvetica-Bold milik dompdf = Arial di PDF)
    // ------------------------------------------------------------------

    function checklist_rpt_afm($bold) {
        static $cache = array();
        $k = $bold ? 'b' : 'r';
        if (isset($cache[$k])) { return $cache[$k]; }
        $cache[$k] = array();
        $f = ROOT . 'vendor/dompdf/dompdf/lib/fonts/' . ($bold ? 'Helvetica-Bold' : 'Helvetica') . '.afm.php';
        if (is_file($f)) {
            $a = include $f;
            if (is_array($a) && isset($a['C']) && is_array($a['C'])) { $cache[$k] = $a['C']; }
        }
        return $cache[$k];
    }

    /** Lebar teks (pt). */
    function checklist_rpt_text_w($text, $size_pt, $bold) {
        $c = checklist_rpt_afm($bold);
        $w = 0;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $o = ord($text[$i]);
            if ($o >= 0x80) {                       // UTF-8 multi-byte: hitung 1 huruf lebar rata-rata
                if (($o & 0xC0) === 0x80) { continue; }
                $w += 600; continue;
            }
            $w += isset($c[$o]) ? (float) $c[$o] : 556;
        }
        return $w * $size_pt / 1000;
    }

    /** Jumlah baris teks di sel selebar $avail_pt (dibungkus per kata / tanda hubung seperti dompdf). 0 = kosong. */
    function checklist_rpt_lines($text, $avail_pt, $size_pt, $bold) {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
        if ($text === '') { return 0; }
        $parts = preg_split('/([\s-]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $lines = 1; $cur = 0.0;
        for ($i = 0; $i < count($parts); $i += 2) {
            $word = $parts[$i] . (isset($parts[$i + 1]) ? $parts[$i + 1] : '');
            $wfull = checklist_rpt_text_w($word, $size_pt, $bold);   // dompdf mengukur kata + spasi sesudahnya
            if ($cur > 0 && $cur + $wfull > $avail_pt) { $lines++; $cur = $wfull; }
            else { $cur += $wfull; }
        }
        return $lines;
    }

    // ------------------------------------------------------------------
    //  GEOMETRI TABEL (sama dengan checklist_report_body.php)
    // ------------------------------------------------------------------

    function checklist_rpt_geom($data) {
        $W = array(1.9, 9.6, 6.8, 8.0, 8.2, 4.6, 6.4);
        $L = (isset($data['layout']) && is_array($data['layout'])) ? $data['layout'] : array();
        if (!empty($L['widths']) && is_array($L['widths']) && count($L['widths']) === 7) { $W = array_map('floatval', array_values($L['widths'])); }
        $font = !empty($L['item_font_px']) ? (float) $L['item_font_px'] : 0;
        $right = 807.87 * 0.85;                     // lebar kolom section (pt)
        $cols = array();
        foreach ($W as $p) { $cols[] = $right * $p / 100; }
        return array(
            'cols'   => $cols,                      // pt: no, part, alat, metode, standar, durasi, pelaksanaan
            'size'   => ($font > 0 ? $font : 8) * 0.75,
            'lh'     => ($font > 0 ? 1.15 : 1.2),
            'padv'   => ($font > 0 ? 1.5 : 3.0),     // padding atas + bawah (pt)
            'padh'   => ($font > 0 ? 3.0 : 4.5),     // padding kiri + kanan (pt)
            'day_w'  => $right * (100 - array_sum($W)) / 100 / max(1, (int) (isset($data['day_count']) ? $data['day_count'] : 31)),
        );
    }

    /** Tinggi 1 baris item (pt). */
    function checklist_rpt_item_h($it, $g, $pelaksanaan_default) {
        $size = $g['size']; $lh = $size * $g['lh'] * 1.02;     // 1.02 = jarak baris nyata dompdf (dikalibrasi)
        $c = $g['cols'];
        $dur = isset($it['durasi']) ? trim((string) $it['durasi']) : '';
        // [teks, lebar isi, tebal, padding atas+bawah]. Sel No. & Durasi selalu padding 2px (3pt), sel lain ikut layout.
        $cells = array(
            array((string) (isset($it['no']) ? $it['no'] : ''), $c[0] - 1.5, false, 3.0),
            array((string) (isset($it['part']) ? $it['part'] : ''), $c[1] - $g['padh'], false, $g['padv']),
            array((string) (isset($it['alat']) ? $it['alat'] : ''), $c[2] - $g['padh'], false, $g['padv']),
            array((string) (isset($it['metode']) ? $it['metode'] : ''), $c[3] - $g['padh'], false, $g['padv']),
            array((string) (isset($it['standar']) ? $it['standar'] : ''), $c[4] - $g['padh'], false, $g['padv']),
            array($dur === '' ? '' : '+' . $dur, $c[5] - 1.5, true, 3.0),
            array((string) (isset($it['pelaksanaan']) ? $it['pelaksanaan'] : $pelaksanaan_default), $c[6] - $g['padh'], true, $g['padv']),
        );
        $h = 8.4;
        foreach ($cells as $x) {   // dikalibrasi: jumlah baris = dompdf (8 profil, 300+ baris acak)
            $n = max(1, checklist_rpt_lines($x[0], max(4, $x[1] - 0.75), $size, $x[2]));
            $h = max($h, $n * $lh + $x[3]);
        }
        return $h + 0.75;
    }

    /** Tinggi baris judul + baris kepala kolom (pt). */
    function checklist_rpt_head_h($section, $g) {
        $title = 10.5 * 0.75 * 1.2 + 3.0 + 0.75;
        $c = $g['cols']; $n = 1;
        $heads = array(array('Nama Part', $c[1]), array('Alat', $c[2]), array('Metode', $c[3]),
            array((string) (isset($section['standard_label']) ? $section['standard_label'] : ''), $c[4]), array('Durasi', $c[5] + 3), array('Pelaksanaan', $c[6]));
        foreach ($heads as $h) { $n = max($n, checklist_rpt_lines($h[0], max(4, $h[1] - 4.5 - 0.75), 6.0, true)); }
        return $title + max($n * 7.2 + 3.0, 8.85) + 0.75;
    }

    /** Tinggi baris Paraf Pelaksana + Paraf Spv (pt). */
    function checklist_rpt_paraf_h() { return 2 * (7.2 + 3.0 + 0.75); }

    /** Tinggi baris kosong (td height:14px + padding). */
    function checklist_rpt_blank_h($g) { return 10.5 + $g['padv'] + 0.75; }

    // ------------------------------------------------------------------
    //  KOLASE
    // ------------------------------------------------------------------

    /** Foto kolase sebuah bagian: [array('path' rel, 'no')] urut item. Hanya file yang ada. */
    function checklist_rpt_tiles($items, $cfg) {
        $hide = array(); foreach ((array) (isset($cfg['hide']) ? $cfg['hide'] : array()) as $p) { $hide[$p] = true; }
        $extra = array();
        foreach ((array) (isset($cfg['extra']) ? $cfg['extra'] : array()) as $x) { $extra[(string) $x['db']][] = (string) $x['path']; }
        $out = array();
        foreach ((array) $items as $it) {
            $no = isset($it['no']) ? (int) $it['no'] : 0;
            $list = isset($it['fotos']) && is_array($it['fotos']) ? $it['fotos'] : (!empty($it['foto']) ? array($it['foto']) : array());
            $db = isset($it['db']) ? (string) $it['db'] : '';
            if ($db !== '' && isset($extra[$db])) { $list = array_merge($list, $extra[$db]); }
            foreach ($list as $p) {
                $p = str_replace('\\', '/', (string) $p);
                if ($p === '' || isset($hide[$p]) || strpos($p, '..') !== false || !is_file(ROOT . $p)) { continue; }
                $out[] = array('path' => $p, 'no' => $no);
            }
        }
        return $out;
    }

    /**
     * Susunan kolase untuk $n foto setinggi maksimal $target_px.
     * Coba 1 kolom lalu 2 kolom, rasio 4:3 -> 3:2 -> 16:9. Tidak muat -> 2 kolom 3:2 (bagian ikut tinggi).
     * Tidak pernah lebih dari 2 kolom: foto tidak dikecilkan di bawah +-74 px.
     */
    function checklist_rpt_grid($n, $target_px) {
        $W = CHECKLIST_RPT_IMG_W; $gap = CHECKLIST_RPT_GAP;
        $mk = function ($c, $ar) use ($n, $W, $gap) {
            $tw = (int) floor(($W - ($c - 1) * $gap) / $c);
            $th = (int) round($tw * $ar);
            $rows = (int) ceil($n / $c);
            return array('c' => $c, 'tw' => $tw, 'th' => $th, 'gap' => $gap, 'rows' => $rows,
                'w' => $c * $tw + ($c - 1) * $gap, 'h' => $rows * $th + ($rows - 1) * $gap);
        };
        foreach (array(1, 2) as $c) {
            if ($c > 1 && $n < 2) { break; }
            foreach (array(0.75, 0.667, 0.5625) as $ar) {
                $g = $mk($c, $ar);
                if ($g['h'] <= $target_px) { return $g; }
            }
        }
        return $n < 2 ? $mk(1, 0.5625) : $mk(2, 0.667);
    }

    /** Folder kolase sebuah profil (relatif ROOT). */
    function checklist_rpt_kolase_dir($key) {
        return 'uploads/mesin/' . preg_replace('/[^a-z0-9_]/', '', strtolower((string) $key)) . '/kolase';
    }

    /** Muat gambar apa pun (JPG/PNG/GIF/WEBP) jadi resource GD. */
    function checklist_rpt_gd_load($file) {
        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') { return null; }
        $im = @imagecreatefromstring($raw);
        return $im ? $im : null;
    }

    /**
     * Gambar kolase bernomor. @return string path relatif ('' = gagal -> body memakai susunan cadangan).
     */
    function checklist_rpt_kolase($key, $slot, $tiles, $grid) {
        if (!function_exists('imagecreatetruecolor') || empty($tiles)) { return ''; }
        $sig = array();
        foreach ($tiles as $t) { $f = ROOT . $t['path']; $sig[] = $t['path'] . '|' . $t['no'] . '|' . @filemtime($f) . '|' . @filesize($f); }
        $hash = substr(md5(implode(';', $sig) . '|' . $grid['c'] . 'x' . $grid['tw'] . 'x' . $grid['th'] . '|' . CHECKLIST_RPT_KOLASE_VER), 0, 16);
        $dir = checklist_rpt_kolase_dir($key);
        $rel = $dir . '/' . preg_replace('/[^a-z]/', '', (string) $slot) . '-' . $hash . '.jpg';
        if (is_file(ROOT . $rel)) { return $rel; }
        if (!is_dir(ROOT . $dir) && !@mkdir(ROOT . $dir, 0775, true)) { return ''; }

        $S = 2;                                         // digambar 2x lebih rapat supaya tajam di PDF & print
        $W = $grid['w'] * $S; $H = $grid['h'] * $S;
        $im = @imagecreatetruecolor($W, $H);
        if (!$im) { return ''; }
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 17, 17, 17);
        $line  = imagecolorallocate($im, 150, 150, 150);
        imagefill($im, 0, 0, $white);
        $font = ROOT . 'vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf';
        $ttf = function_exists('imagettftext') && is_file($font);
        $n = count($tiles); $c = $grid['c']; $tw = $grid['tw']; $th = $grid['th']; $gap = $grid['gap'];
        foreach (array_values($tiles) as $i => $t) {
            $r = (int) floor($i / $c); $col = $i % $c;
            $in_row = min($c, $n - $r * $c);
            $off = ($c - $in_row) * ($tw + $gap) / 2;      // baris terakhir yang tidak penuh: di tengah
            $x = (int) round(($col * ($tw + $gap) + $off) * $S);
            $y = (int) round($r * ($th + $gap) * $S);
            $src = checklist_rpt_gd_load(ROOT . $t['path']);
            if ($src) {
                $sw = imagesx($src); $sh = imagesy($src);
                $ratio = $tw / max(1, $th);
                if ($sw / max(1, $sh) > $ratio) { $cw = (int) round($sh * $ratio); $ch = $sh; $cx = (int) round(($sw - $cw) / 2); $cy = 0; }
                else { $cw = $sw; $ch = (int) round($sw / $ratio); $cx = 0; $cy = (int) round(($sh - $ch) * 0.4); }
                imagecopyresampled($im, $src, $x, $y, $cx, $cy, $tw * $S, $th * $S, max(1, $cw), max(1, $ch));
                imagedestroy($src);
            }
            imagesetthickness($im, 1);
            imagerectangle($im, $x, $y, $x + $tw * $S - 1, $y + $th * $S - 1, $line);
            // nomor item: bulatan putih bergaris hitam di pojok kiri atas (seperti gambar panel resmi)
            $txt = (string) (int) $t['no'];
            $fs = 7.5 * $S;                                  // px
            if ($ttf) { $bb = imagettfbbox($fs * 0.75, 0, $font, $txt); $tw_txt = abs($bb[2] - $bb[0]); }
            else { $tw_txt = strlen($txt) * imagefontwidth(3); }
            $bw = max(15 * $S, $tw_txt + 8 * $S); $bh = 15 * $S;
            $bx = $x + 3 * $S + (int) ($bw / 2); $by = $y + 3 * $S + (int) ($bh / 2);
            imagefilledellipse($im, $bx, $by, (int) $bw, (int) $bh, $black);
            imagefilledellipse($im, $bx, $by, (int) $bw - 3 * $S, (int) $bh - 3 * $S, $white);
            if ($ttf) {
                $b = imagettfbbox($fs * 0.75, 0, $font, $txt);
                $tx = (int) round($bx - ($b[2] - $b[0]) / 2 - $b[0]);
                $ty = (int) round($by + (abs($b[7] - $b[1])) / 2 - 1);
                imagettftext($im, $fs * 0.75, 0, $tx, $ty, $black, $font, $txt);
            } else {
                imagestring($im, 3, (int) ($bx - $tw_txt / 2), (int) ($by - imagefontheight(3) / 2), $txt, $black);
            }
        }
        $tmp = ROOT . $rel . '.' . substr(md5(uniqid('', true)), 0, 6) . '.tmp';
        $ok = @imagejpeg($im, $tmp, 88);
        imagedestroy($im);
        if (!$ok) { @unlink($tmp); return ''; }
        if (!@rename($tmp, ROOT . $rel)) { @unlink($tmp); return is_file(ROOT . $rel) ? $rel : ''; }
        return $rel;
    }

    // ------------------------------------------------------------------
    //  RENCANA HALAMAN
    // ------------------------------------------------------------------

    /**
     * @param array $data  $data report (checklist_collect_report_data) - images sudah berupa src sesuai format
     * @return array daftar halaman: [ array('chunks' => [chunk...]) ]
     *   chunk: section (asli), title, items, blank_rows, paraf (bool), paraf_ok (bagian punya item),
     *          photo_label, photo (src|''), photo_w, photo_h, tiles_fb (cadangan tanpa GD), row_h (px, 0 = biasa)
     */
    function checklist_rpt_plan($data) {
        $sections = isset($data['sections']) ? (array) $data['sections'] : array();
        $images   = isset($data['images']) ? (array) $data['images'] : array();
        $img_size = isset($data['img_size']) ? (array) $data['img_size'] : array();
        $cfg_all  = isset($data['gambar_cfg']) ? (array) $data['gambar_cfg'] : array();
        $format   = isset($data['format']) ? (string) $data['format'] : 'print';
        $key      = isset($data['profile_key']) ? (string) $data['profile_key'] : '';
        $plk_def  = isset($data['pelaksanaan']) ? $data['pelaksanaan'] : '';
        $g = checklist_rpt_geom($data);

        $sz = function ($k, $w, $h) use ($img_size) {
            if (isset($img_size[$k]) && is_array($img_size[$k]) && count($img_size[$k]) >= 2) { return array('w' => (int) $img_size[$k][0], 'h' => (int) $img_size[$k][1]); }
            return array('w' => $w, 'h' => $h);
        };
        $IMG_ATAS = $sz('atas', 152, 135); $IMG_BAWAH = $sz('bawah', 152, 169);
        $label_h = 8.5 * 0.75 * 1.2 + 3.75;

        // ---- 1. tiap bagian: tinggi tabel, gambar (panel / kolase)
        $blocks = array(); $any_auto = false;
        foreach ($sections as $si => $s) {
            $items = isset($s['items']) ? array_values((array) $s['items']) : array();
            $photo_key = isset($s['photo']) ? (string) $s['photo'] : '';
            $mode = 'panel';
            if ($photo_key !== '') {
                $mode = isset($cfg_all[$photo_key]['mode']) ? $cfg_all[$photo_key]['mode'] : (!empty($images[$photo_key]) ? 'panel' : 'auto');
            }
            $tiles = array();
            if ($photo_key !== '' && $mode === 'auto' && $items) {
                $tiles = checklist_rpt_tiles($items, isset($cfg_all[$photo_key]) ? $cfg_all[$photo_key] : array());
                if ($tiles) { $any_auto = true; }
            }
            $rows_h = array();
            foreach ($items as $it) { $rows_h[] = checklist_rpt_item_h($it, $g, $plk_def); }
            $blocks[] = array(
                'si' => $si, 's' => $s, 'items' => $items, 'rows_h' => $rows_h, 'photo_key' => $photo_key, 'mode' => $mode, 'tiles' => $tiles,
                'head_h' => checklist_rpt_head_h($s, $g), 'blank' => isset($s['blank_rows']) ? (int) $s['blank_rows'] : 0,
                'label' => isset($s['photo_label']) ? (string) $s['photo_label'] : '',
            );
        }
        $paraf_h = checklist_rpt_paraf_h();
        $blank_h = checklist_rpt_blank_h($g);

        // tinggi tabel potongan item [$a, $b) (pt)
        $table_h = function ($b, $from, $to, $last) use ($paraf_h, $blank_h) {
            $h = $b['head_h'];
            for ($i = $from; $i < $to; $i++) { $h += $b['rows_h'][$i]; }
            if ($last) { $h += $b['blank'] * $blank_h + $paraf_h; }
            return $h;
        };
        // tiles milik item [$from, $to)
        $tiles_of = function ($b, $from, $to) {
            if (!$b['tiles']) { return array(); }
            $nos = array();
            for ($i = $from; $i < $to; $i++) { $nos[(int) $b['items'][$i]['no']] = true; }
            $out = array();
            foreach ($b['tiles'] as $t) { if (isset($nos[$t['no']])) { $out[] = $t; } }
            return $out;
        };
        // gambar kiri sebuah potongan: array(px_w, px_h, grid|null, kind) ; tinggi kolom kiri (pt) dihitung pemanggil
        $left_of = function ($b, $from, $to, $tab_pt, $first) use ($tiles_of, $images, $IMG_ATAS, $IMG_BAWAH, $sz, $label_h) {
            if ($b['photo_key'] === '') { return null; }
            if ($b['mode'] === 'auto') {
                $tiles = $tiles_of($b, $from, $to);
                if (!$tiles) { return null; }
                $target = ($tab_pt - $label_h - 3.0) / 0.75;
                $grid = checklist_rpt_grid(count($tiles), $target);
                return array('kind' => 'kolase', 'tiles' => $tiles, 'grid' => $grid, 'w' => $grid['w'], 'h' => $grid['h']);
            }
            if (!$first || empty($images[$b['photo_key']])) { return null; }       // panel: hanya di potongan pertama
            $size = ($b['photo_key'] === 'atas') ? $IMG_ATAS : $sz($b['photo_key'], $IMG_BAWAH['w'], $IMG_BAWAH['h']);
            return array('kind' => 'panel', 'src' => $images[$b['photo_key']], 'w' => $size['w'], 'h' => $size['h']);
        };
        $left_pt = function ($pic, $with_label) use ($label_h) {
            return ($with_label ? $label_h : 0) + ($pic ? $pic['h'] * 0.75 + 3.0 : 0) + 0.75;
        };

        // ---- 2. potong per halaman (kop diulang di tiap halaman; baris Area / Mesin bisa 2 baris kalau namanya panjang)
        $kop_lines = max(1,
            checklist_rpt_lines('Area: ' . (isset($data['area']) ? $data['area'] : ''), (807.87 * 0.17 - 5.25) * 0.9, 6.75, true),
            checklist_rpt_lines(isset($data['nama_mesin']) ? $data['nama_mesin'] : '', (807.87 * 0.27 - 5.25) * 0.9, 6.75, true));   // 0.9: kop lebih baik diperkirakan lebih tinggi (dompdf membungkus sel kop lebih cepat)
        $kop_h = 66.5 + ($kop_lines - 1) * 8.25;      // dikalibrasi dengan dompdf (1 baris 66.5pt, tiap baris tambahan 8.25pt)
        $cap = CHECKLIST_RPT_PAGE_PT - $kop_h - CHECKLIST_RPT_SAFETY_PT;
        $pages = array(array()); $room = $cap;
        $chunk_h = function ($b, $from, $to, $last, $first) use ($table_h, $left_of, $left_pt) {
            $tab = $table_h($b, $from, $to, $last);
            $pic = $left_of($b, $from, $to, $tab, $first);
            return array(max($tab, $left_pt($pic, true)), $tab, $pic);
        };
        foreach ($blocks as $b) {
            $n = count($b['items']);
            $from = 0; $first = true; $guard = 0;
            while ($guard++ < 500) {
                $fresh = ($room >= $cap);
                list($h, $tab, $pic) = $chunk_h($b, $from, $n, true, $first);
                if ($h <= $room || ($fresh && $n - $from <= 1)) {       // sisa bagian muat (atau tidak bisa dipecah lagi)
                    $pages[count($pages) - 1][] = array('b' => $b, 'from' => $from, 'to' => $n, 'last' => true, 'first' => $first, 'tab' => $tab, 'pic' => $pic);
                    $room -= $h;
                    break;
                }
                if (!$fresh && ($from === 0 && $h <= $cap)) { $pages[] = array(); $room = $cap; continue; }   // utuh muat di halaman baru: pindah
                // pecah: item sebanyak mungkin di sisa halaman ini (bukan potongan terakhir -> tanpa baris paraf)
                $take = 0; $best = null;
                for ($t = $n - $from - 1; $t >= 1; $t--) {
                    $r = $chunk_h($b, $from, $from + $t, false, $first);
                    if ($r[0] <= $room) { $take = $t; $best = $r; break; }
                }
                if (!$fresh && $take < 2) { $pages[] = array(); $room = $cap; continue; }   // cuma 1 item di ujung halaman: pindah saja
                if ($take === 0) { $take = 1; $best = $chunk_h($b, $from, $from + 1, false, $first); }   // halaman kosong pun tidak muat 1 item (praktis tidak terjadi)
                $pages[count($pages) - 1][] = array('b' => $b, 'from' => $from, 'to' => $from + $take, 'last' => false, 'first' => $first, 'tab' => $best[1], 'pic' => $best[2]);
                $from += $take; $first = false;
                $pages[] = array(); $room = $cap;
            }
        }
        if (empty($pages[count($pages) - 1])) { array_pop($pages); }

        // ---- 3. bentuk potongan untuk body
        $out = array();
        foreach ($pages as $pi => $list) {
            $chunks = array();
            foreach ($list as $c) {
                $b = $c['b']; $s = $b['s']; $pic = $c['pic'];
                $items = array_slice($b['items'], $c['from'], $c['to'] - $c['from']);
                $chunk = array(
                    'section'     => $s,
                    'title'       => (string) (isset($s['title']) ? $s['title'] : '') . ($c['first'] ? '' : ' - LANJUTAN'),
                    'items'       => $items,
                    'blank_rows'  => $c['last'] ? $b['blank'] : 0,
                    'paraf'       => $c['last'],
                    'paraf_ok'    => !empty($b['items']),
                    'photo_label' => $c['first'] ? $b['label'] : '',
                    'photo'       => '', 'photo_w' => 0, 'photo_h' => 0, 'tiles_fb' => null, 'row_h' => array(),
                    'kolase'      => false,
                    'est_h'       => max($c['tab'], $left_pt($pic, true)),   // perkiraan tinggi (pt), untuk tes
                    'est_tab'     => $c['tab'], 'est_left' => $left_pt($pic, true),
                );
                if ($pic && $pic['kind'] === 'panel') {
                    $chunk['photo'] = $pic['src']; $chunk['photo_w'] = $pic['w']; $chunk['photo_h'] = $pic['h'];
                } elseif ($pic && $pic['kind'] === 'kolase') {
                    $chunk['kolase'] = true;
                    $rel = checklist_rpt_kolase($key !== '' ? $key : 'report', $b['photo_key'], $pic['tiles'], $pic['grid']);
                    if ($rel !== '') {
                        $chunk['photo'] = checklist_image_src($rel, $format); $chunk['photo_w'] = $pic['grid']['w']; $chunk['photo_h'] = $pic['grid']['h'];
                    } else {
                        $fb = array();
                        foreach ($pic['tiles'] as $t) { $fb[] = array('src' => checklist_image_src($t['path'], $format), 'no' => $t['no']); }
                        $chunk['tiles_fb'] = array('tiles' => $fb, 'grid' => $pic['grid']);
                    }
                    // foto lebih tinggi dari tabel -> baris item ditinggikan rata (px, tinggi isi sel)
                    $need = $left_pt($pic, true);
                    if ($need > $c['tab'] + 1 && $items) {
                        $extra = ($need - $c['tab']) / count($items);
                        foreach (array_keys($items) as $ii) {
                            $rh = $b['rows_h'][$c['from'] + $ii] + $extra;
                            $chunk['row_h'][$ii] = (int) floor(($rh - 3.0 - 0.75) / 0.75);   // sel No.: padding 2px atas+bawah (3pt) + garis
                        }
                    }
                }
                $chunks[] = $chunk;
            }
            // label "Gambar Bagian Mesin": di bagian pertama halaman yang punya gambar (bagian berlabel tanpa gambar -> label pindah)
            $has_pic = function ($ch) { return $ch['photo'] !== '' || !empty($ch['tiles_fb']); };
            $lbl = ''; $lbl_i = -1; $pic_i = -1;
            foreach ($chunks as $ci => $ch) {
                if ($ch['photo_label'] !== '' && $lbl_i < 0) { $lbl = $ch['photo_label']; $lbl_i = $ci; }
                if ($pic_i < 0 && $has_pic($ch)) { $pic_i = $ci; }
            }
            if ($lbl === '' && $pi > 0 && $pic_i >= 0) {
                foreach ($blocks as $b) { if ($b['label'] !== '') { $lbl = $b['label']; break; } }
            }
            if ($lbl !== '' && $pic_i >= 0 && ($lbl_i < 0 || !$has_pic($chunks[$lbl_i]))) {
                if ($lbl_i >= 0) { $chunks[$lbl_i]['photo_label'] = ''; }
                $chunks[$pic_i]['photo_label'] = $lbl;
            }
            $out[] = array('chunks' => $chunks, 'kop_h' => $kop_h);
        }
        if (!$out) { $out[] = array('chunks' => array()); }
        return $out;
    }
}
