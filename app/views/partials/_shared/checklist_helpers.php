<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/checklist_helpers.php
 *
 *  Fungsi bersama Report Layout SEMUA mesin. Isinya dipindah apa adanya dari
 *  app/views/partials/mesin_geprek/geprek_helpers.php (22 Sep 2026) supaya
 *  tidak ada lagi mesin yang harus "nge-root" ke folder mesin_geprek.
 *
 *  KOMPATIBILITAS: nama fungsi lama (geprek_row_val, geprek_user_initial,
 *  geprek_bulan_tahun_label) TETAP ADA di file ini sebagai alias. Jadi
 *  report.php geprek, Mesin_geprekController.php, test_preview_geprek.php dan
 *  dumping_report.php TIDAK perlu diubah sama sekali.
 *
 *  Nama baru yang dipakai kode mesin baru:
 *      checklist_row_val()            (dulu geprek_row_val)
 *      checklist_user_initial()       (dulu geprek_user_initial)
 *      checklist_bulan_tahun_label()  (dulu geprek_bulan_tahun_label)
 *      checklist_approver_initial()   (dulu dumping_approver_initial)
 *
 *  Saklar-nya TIDAK di file ini - semuanya di checklist_config.php.
 * ============================================================================
 */

require_once __DIR__ . '/checklist_config.php';

// ---------------------------------------------------------------------------
// Baca field dari 1 row hasil query
// ---------------------------------------------------------------------------
if (!function_exists('checklist_row_val')) {
    /**
     * Aman baik row-nya array asosiatif maupun object (stdClass).
     * Kalau field tidak ada, balikin $default.
     */
    function checklist_row_val($row, $field, $default = null) {
        if (is_array($row)) {
            return array_key_exists($field, $row) ? $row[$field] : $default;
        }
        if (is_object($row)) {
            return isset($row->$field) ? $row->$field : $default;
        }
        return $default;
    }
}

// ---------------------------------------------------------------------------
// [GENC-06OKT26-INISIAL] Inisial & nama dari menu USERS (tabel users, kolom `inisial`)
// ---------------------------------------------------------------------------
if (!function_exists('checklist_users_map')) {
    /**
     * Daftar user dari database, dibaca SEKALI per request:
     *   'ok'      => kolom users.inisial sudah ada (SQL genc_06okt26.sql sudah dijalankan)
     *   'by_user' => [username huruf kecil => array('nama' => ..., 'inisial' => 'ABC' | '')]
     *   'by_ini'  => [INISIAL => username]  (inisial yang diisi admin di menu Users)
     * Gagal / kolom belum ada -> daftar kosong = perilaku lama (daftar resmi di bawah).
     */
    function checklist_users_map($reset = false) {
        static $map = null;
        if ($reset) { $map = null; return null; }
        if ($map !== null) { return $map; }
        $map = array('ok' => false, 'by_user' => array(), 'by_ini' => array());
        if (!defined('DB_HOST') || !defined('DB_NAME')) { return $map; }
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . (defined('DB_PORT') && DB_PORT !== '' ? ';port=' . DB_PORT : '')
                 . ';charset=' . (defined('DB_CHARSET') && DB_CHARSET !== '' ? DB_CHARSET : 'utf8');
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $has = count($pdo->query("SHOW COLUMNS FROM `users` LIKE 'inisial'")->fetchAll()) > 0;
            $rows = $pdo->query("SELECT username, nama" . ($has ? ", inisial" : "") . " FROM `users`")->fetchAll(PDO::FETCH_ASSOC);
            $map['ok'] = $has;
            foreach ($rows as $r) {
                $k = strtolower(trim((string) $r['username']));
                if ($k === '') { continue; }
                $ini = $has ? strtoupper(trim((string) $r['inisial'])) : '';
                $map['by_user'][$k] = array('nama' => trim((string) $r['nama']), 'inisial' => $ini);
                if ($ini !== '' && !isset($map['by_ini'][$ini])) { $map['by_ini'][$ini] = $k; }
            }
        } catch (\Throwable $e) {
            $map = array('ok' => false, 'by_user' => array(), 'by_ini' => array());
        }
        return $map;
    }
}

if (!function_exists('checklist_official_initial')) {
    /** Inisial dari DAFTAR RESMI SPV (10 Sep 2026) saja - '' kalau username tidak ada di daftar. */
    function checklist_official_initial($username) {
        $map = checklist_official_initials();
        $key = strtolower(trim((string) $username));
        return isset($map[$key]) ? $map[$key] : '';
    }
}

// ---------------------------------------------------------------------------
// Username -> inisial 3 huruf
// ---------------------------------------------------------------------------
if (!function_exists('checklist_user_initial')) {
    /**
     * Konversi username (kolom user_created / user_create / user_approve) jadi
     * inisial sesuai daftar resmi dari supervisor (per request 10 Sep 2026).
     *
     * INPUT-nya harus username MENTAH (misal 'edy.haryanto'), BUKAN inisial
     * yang sudah jadi - jangan dipanggil dua kali untuk data yang sama.
     *
     * Kalau usernamenya belum ada di daftar, fallback ke inisial auto (huruf
     * depan tiap bagian nama, maksimal 3 huruf) supaya tetap muat di kolom
     * sempit dan kelihatan jelas ada user yang belum kepetakan.
     *
     * CATATAN (tolong dikonfirmasi ke supervisor / dicek manual):
     * - Baris "candra" ditulis kebalik format-nya di daftar asli ("CFW = candra"
     *   vs yang lain "USERNAME=KODE") - diasumsikan tetap berarti candra -> CFW.
     * - Key "pramono" & "edy.haryanto" (ejaan yang kelihatan di data ASLI)
     *   ditambahkan sebagai alias manual, karena daftar resmi cuma menulis
     *   "PRAMONO.NUGROHO" & "EDY.HARTONO" (beda ejaan surname: HARTONO vs
     *   HARYANTO). Perlu dipastikan lagi ini orang yang sama atau bukan.
     * - 'pan' ditambahkan 22 Sep 2026: trigger auto-approve DB menulis
     *   user_approve = 'PAN'. Tanpa baris ini, fallback auto menghasilkan 'P'
     *   (satu huruf) di baris Paraf Spv. Lihat BLUEPRINT bagian 8.1 no.1.
     */
    function checklist_user_initial($username) {
        $key = strtolower(trim((string) $username));
        if ($key === '') {
            return '';
        }
        // [GENC-06OKT26-INISIAL] 1) inisial yang diatur admin di menu Users  2) isian sudah berupa inisial user
        $um = checklist_users_map();
        if (isset($um['by_user'][$key]) && $um['by_user'][$key]['inisial'] !== '') {
            return $um['by_user'][$key]['inisial'];
        }
        if (isset($um['by_ini'][strtoupper($key)])) {
            return strtoupper($key);
        }
        // 3) daftar resmi SPV (bawaan, sama dengan sebelum 6 Okt)  4) otomatis dari username
        return checklist_default_initial($key);
    }
}

if (!function_exists('checklist_default_initial')) {
    /** [GENC-06OKT26-INISIAL] Inisial TANPA pengaturan Users: daftar resmi SPV, kalau tidak ada -> huruf depan tiap bagian username (maks 3). */
    function checklist_default_initial($username) {
        $key = strtolower(trim((string) $username));
        if ($key === '') { return ''; }
        $map = checklist_official_initials();
        if (isset($map[$key])) {
            return $map[$key];
        }
        $parts = preg_split('/[.\s_]+/', $key);
        $initial = '';
        foreach ($parts as $p) {
            if ($p !== '') { $initial .= strtoupper($p[0]); }
        }
        return $initial !== '' ? substr($initial, 0, 3) : strtoupper(substr($key, 0, 3));
    }
}

if (!function_exists('checklist_official_initials')) {
    /**
     * DAFTAR RESMI inisial dari supervisor (10 Sep 2026) - cadangan kalau kolom
     * users.inisial belum ada / kosong untuk user itu, dan untuk ejaan username
     * lama yang ada di data tapi tidak ada di tabel users (alias di bawah).
     * [GENC-06OKT26-INISIAL] Mulai 6 Okt inisial diatur di menu Users; SQL
     * genc_06okt26.sql menyalin daftar ini ke kolom users.inisial sekali.
     */
    function checklist_official_initials() {
        static $map = null;
        if ($map === null) {
            $map = array(
                'candra'               => 'CFW',
                'pramono.nugroho'      => 'PAN',
                'pramono'              => 'PAN', // alias: username asli di DB cuma "pramono"
                'pan'                  => 'PAN', // hasil trigger auto-approve DB
                'fitri.fidiastuti'     => 'FFI',
                'hendro.cahyono'       => 'HCO',
                'edy.hartono'          => 'EHO', // sesuai ejaan di daftar supervisor
                'edy.haryanto'         => 'EHO', // alias: ejaan yang kelihatan di data asli
                'yudi.setyawan'        => 'YSE',
                'bayu.suryanto'        => 'BSO',
                'nana.sujana'          => 'NSA',
                'cahyudi'              => 'CHY',
                'findi.atikaningsing'  => 'FND',
                'ali.usman'            => 'ALS',
                'tri.istianto'         => 'TST',
                'ibnu.mubharok'        => 'IBN',
                'anggi'                => 'ANG',
                'rendi'                => 'RND',
                // Alias 23 Sep 2026 - ejaan username yang KELIHATAN di halaman
                // list Forklift (screenshot 23 Sep 2026). Tanpa baris ini paraf
                // jatuh ke fallback huruf depan: 'CS', 'AD', 'IM' - bukan
                // inisial resmi. Perlu dipastikan orangnya sama (kemungkinan
                // besar ya: nama depan sama persis dengan daftar SPV).
                'cahyudi.supriyadi'    => 'CHY',
                'anggi.diantoro'       => 'ANG',
                'ibnu.mubarok'         => 'IBN', // daftar SPV: "mubharok" (pakai h)
            );
        }
        return $map;
    }
}

// ---------------------------------------------------------------------------
// Username / inisial -> NAMA UTUH  (khusus kotak "Diperiksa Oleh")
// ---------------------------------------------------------------------------
if (!function_exists('checklist_display_name')) {
    /**
     * Kebalikan dari checklist_user_initial(): dipakai di tempat yang butuh
     * NAMA ORANG, bukan paraf.
     *
     * Pembagiannya tegas dan jangan dibalik:
     *   - baris "Paraf Pelaksana" & "Paraf Spv / Fasilitator" (harian)
     *        -> checklist_user_initial() / checklist_approver_initial()  = 3 huruf
     *   - kotak "Diperiksa Oleh" di kop
     *        -> checklist_display_name()                                 = nama utuh
     *
     * Kenapa perlu: kolom user_approve isinya tidak seragam. Approval manual
     * menulis username ('pramono'), sedangkan trigger auto-approve DB menulis
     * KODE INISIAL ('PAN'). Tanpa fungsi ini, kotak "Diperiksa Oleh" bisa
     * tertulis "PAN" - inisial nyasar ke tempat yang seharusnya nama.
     *
     * Caranya: apa pun bentuk inputnya diubah dulu jadi inisial (username
     * maupun kode inisial sama-sama menghasilkan inisial yang sama), baru
     * inisial itu dipetakan ke nama utuh. Hasilnya konsisten:
     *     'pramono' / 'pramono.nugroho' / 'PAN'  ->  "Pramono Nugroho"
     *
     * MAU UBAH EJAAN NAMA? Edit $nama di bawah. Contoh kalau SPV maunya cuma
     * "Pramono" tanpa surname: ganti 'PAN' => 'Pramono'.
     *
     * Username yang belum terdaftar -> dirapikan otomatis dari usernamenya
     * ('budi.santoso' -> "Budi Santoso", 'System' -> "System"), bukan dibiarkan
     * huruf kecil semua.
     *
     * Dimatikan lewat SAKLAR 7 (CHECKLIST_DIPERIKSA_OLEH_NAMA_UTUH = false)
     * kalau suatu saat kotak itu mau ditulis apa adanya dari DB.
     */
    function checklist_display_name($username) {
        static $nama = null;
        if ($nama === null) {
            $nama = array(
                'CFW' => 'Candra',
                'PAN' => 'Pramono Nugroho',
                'FFI' => 'Fitri Fidiastuti',
                'HCO' => 'Hendro Cahyono',
                'EHO' => 'Edy Haryanto',
                'YSE' => 'Yudi Setyawan',
                'BSO' => 'Bayu Suryanto',
                'NSA' => 'Nana Sujana',
                'CHY' => 'Cahyudi',
                'FND' => 'Findi Atikaningsing',
                'ALS' => 'Ali Usman',
                'TST' => 'Tri Istianto',
                'IBN' => 'Ibnu Mubharok',
                'ANG' => 'Anggi',
                'RND' => 'Rendi',
            );
        }

        $raw = trim((string) $username);
        if ($raw === '') {
            return '';
        }
        if (!CHECKLIST_DIPERIKSA_OLEH_NAMA_UTUH) {
            return $raw; // SAKLAR 7 mati -> apa adanya dari DB
        }

        // [GENC-06OKT26-INISIAL] Nama daftar resmi SPV dicari lewat inisial RESMI orang itu (bukan inisial
        // yang diatur di Users) -> mengganti inisial di menu Users tidak mengubah nama di report.
        $um  = checklist_users_map();
        $key = strtolower($raw);
        $off = checklist_official_initial($key);
        if ($off === '' && isset($nama[strtoupper($raw)])) { $off = strtoupper($raw); }   // isian sudah kode resmi ('PAN')
        $dbu = isset($um['by_user'][$key]) ? $um['by_user'][$key] : null;
        if ($off === '' && !$dbu && isset($um['by_ini'][strtoupper($raw)])) {            // isian = inisial dari Users
            $k2  = $um['by_ini'][strtoupper($raw)];
            $dbu = $um['by_user'][$k2];
            $off = checklist_official_initial($k2);
            $raw = $k2;
        }
        if ($off !== '' && isset($nama[$off])) {
            return $nama[$off];
        }
        // Belum ada di daftar resmi: nama lengkap dari menu Users (dirapikan), kalau tidak ada -> dari username.
        if ($dbu && $dbu['nama'] !== '') {
            $raw = $dbu['nama'];
        }
        $parts = preg_split('/[.\s_]+/', $raw);
        $out = array();
        foreach ($parts as $p) {
            if ($p === '') { continue; }
            $out[] = ucfirst(strtolower($p));
        }
        return empty($out) ? $raw : implode(' ', $out);
    }
}

// ---------------------------------------------------------------------------
// Label bulan/tahun
// ---------------------------------------------------------------------------
if (!function_exists('checklist_bulan_tahun_label')) {
    function checklist_bulan_tahun_label($bulan, $tahun) {
        $nama = checklist_nama_bulan();
        $b = isset($nama[(int) $bulan]) ? $nama[(int) $bulan] : $bulan;
        return $b . ' ' . $tahun;
    }
}

if (!function_exists('checklist_nama_bulan')) {
    /** Dipakai label report DAN dropdown filter periode - satu sumber. */
    function checklist_nama_bulan() {
        return array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        );
    }
}

// ---------------------------------------------------------------------------
// Approver hasil trigger auto-approve DB
// ---------------------------------------------------------------------------
if (!function_exists('checklist_is_system_approver')) {
    /**
     * true kalau approver termasuk daftar SAKLAR 3 (CHECKLIST_AUTO_APPROVE_USERS)
     * di checklist_config.php.
     */
    function checklist_is_system_approver($username) {
        static $list = null;
        if ($list === null) {
            $list = array();
            foreach (explode(',', (string) CHECKLIST_AUTO_APPROVE_USERS) as $u) {
                $u = strtolower(trim($u));
                if ($u !== '') { $list[$u] = true; }
            }
        }
        $key = strtolower(trim((string) $username));
        return ($key !== '' && isset($list[$key]));
    }
}

if (!function_exists('checklist_approver_initial')) {
    /**
     * Inisial untuk baris "Paraf Spv / Fasilitator".
     * Approver yang masuk daftar CHECKLIST_AUTO_APPROVE_USERS ditulis 'SYS'
     * supaya beda jelas dengan approval manual. Selain itu pakai daftar inisial
     * yang sama dengan Paraf Pelaksana.
     */
    function checklist_approver_initial($username) {
        $key = strtolower(trim((string) $username));
        if ($key === '') {
            return '';
        }
        if (checklist_is_system_approver($key)) {
            return 'SYS';
        }
        return checklist_user_initial($username);
    }
}

// ---------------------------------------------------------------------------
// Isi kotak "Diperiksa Oleh"  (SAKLAR 1 & 2)
// ---------------------------------------------------------------------------
if (!function_exists('checklist_diperiksa_oleh')) {
    /**
     * Nama untuk kotak "Diperiksa Oleh", dipilih dari daftar row 1 bulan
     * (urut tanggal ASC).
     *
     * Record yang user_approve-nya KOSONG selalu dilewati - itu artinya "belum
     * di-approve", bukan nama approver. Jadi satu hari yang masih pending
     * (misal ada NOK yang belum disetujui) tidak bikin kotak ini ikut kosong,
     * padahal hari-hari lain sudah ada approver-nya.
     *
     * Hasil '' (kosong) kalau di bulan itu memang belum ada approval sama
     * sekali - atau, kalau SAKLAR 2 = true, kalau yang ada cuma approval
     * otomatis dari trigger.
     *
     * @param array|object $rows          hasil query 1 bulan, urut date_created ASC
     * @param string       $mode          'terakhir' | 'terbanyak'
     * @param bool|null    $abaikan_system null = ikut SAKLAR 2
     * @param string       $approve_field nama kolom approver (default user_approve)
     * @return string nama approver apa adanya dari DB ('' kalau tidak ada)
     */
    function checklist_diperiksa_oleh($rows, $mode = CHECKLIST_DIPERIKSA_OLEH_MODE, $abaikan_system = null, $approve_field = 'user_approve') {
        if ($abaikan_system === null) {
            $abaikan_system = CHECKLIST_DIPERIKSA_OLEH_ABAIKAN_SYSTEM;
        }

        $approvers = array(); // urut sesuai tanggal
        foreach ((array) $rows as $row) {
            $u = trim((string) checklist_row_val($row, $approve_field, ''));
            if ($u === '') {
                continue; // belum di-approve
            }
            if ($abaikan_system && checklist_is_system_approver($u)) {
                continue;
            }
            $approvers[] = $u;
        }
        if (empty($approvers)) {
            return '';
        }

        if ($mode === 'terbanyak') {
            $count = array(); $last_pos = array(); $label = array();
            foreach ($approvers as $i => $u) {
                $k = strtolower($u);
                $count[$k]    = isset($count[$k]) ? $count[$k] + 1 : 1;
                $last_pos[$k] = $i;
                $label[$k]    = $u;
            }
            $best = null;
            foreach ($count as $k => $c) {
                if ($best === null || $c > $count[$best] || ($c === $count[$best] && $last_pos[$k] > $last_pos[$best])) {
                    $best = $k;
                }
            }
            return $label[$best];
        }

        return end($approvers); // 'terakhir'
    }
}

// ---------------------------------------------------------------------------
// Tanggal yang ditandai N/A  (SAKLAR 4)
// ---------------------------------------------------------------------------
if (!function_exists('checklist_na_days')) {
    /**
     * Tanggal "N/A" = tanggal yang SUDAH LEWAT tapi tidak ada record sama
     * sekali (libur / tidak operasional / lupa isi).
     *  - Tanggal hari ini & ke depan -> TIDAK N/A (bisa jadi belum diisi)
     *  - Ada record tapi field-nya kosong -> TIDAK N/A (tetap kosong)
     * "Hari ini" dihitung pakai zona Asia/Jakarta, tidak tergantung setting
     * timezone PHP/XAMPP (config.php DEFAULT_TIMEZONE masih kosong).
     *
     * @param int   $bulan, $tahun, $day_count
     * @param array $filled_days  [tanggal => true] untuk tanggal yang punya record
     * @return array [tanggal => true]
     */
    function checklist_na_days($bulan, $tahun, $day_count, $filled_days) {
        if (!CHECKLIST_NA_AKTIF) {
            return array();
        }
        $tz    = new DateTimeZone('Asia/Jakarta');
        $today = new DateTime('now', $tz);
        $today_key = (int) $today->format('Ymd');
        $na = array();
        for ($d = 1; $d <= $day_count; $d++) {
            $key = (int) sprintf('%04d%02d%02d', $tahun, $bulan, $d);
            if ($key < $today_key && empty($filled_days[$d])) {
                $na[$d] = true;
            }
        }
        return $na;
    }
}

// ---------------------------------------------------------------------------
// Normalisasi nomor unit (no_agv / no_forklift / no_palletmover)
// ---------------------------------------------------------------------------
if (!function_exists('checklist_norm_unit')) {
    /**
     * Buang semua yang bukan huruf/angka lalu jadikan HURUF BESAR, supaya
     * penulisan nomor unit yang berantakan tetap kecocok.
     *
     *     'AGV TTL 7'  ->  'AGVTTL7'
     *     'agv ttl-7'  ->  'AGVTTL7'
     *     'AGV  TTL7'  ->  'AGVTTL7'
     *
     * Ini dipakai supaya satu unit fisik yang di DB ditulis dua gaya (fakta
     * 22 Sep 2026: kolom no_agv berisi 'AGV 7' DAN 'AGV TTL 7' untuk mesin
     * yang sama) tetap ketarik dua-duanya waktu difilter. Tanpa ini, filter
     * per unit akan mengembalikan report setengah isi TANPA pesan error.
     *
     * CATATAN: pencocokan unit sengaja dikerjakan di PHP, BUKAN di SQL.
     * Query-nya sudah dibatasi 1 bulan (paling banyak ratusan baris), dan
     * normalisasi di SQL butuh REPLACE bertingkat yang bikin index tidak
     * kepakai sekaligus beda-beda antar versi MySQL.
     */
    function checklist_norm_unit($value) {
        $s = preg_replace('/[^A-Za-z0-9]+/', '', (string) $value);
        return strtoupper((string) $s);
    }
}

// ---------------------------------------------------------------------------
// Sumber gambar sesuai format
// ---------------------------------------------------------------------------
if (!function_exists('checklist_image_src')) {
    /**
     *  PDF        -> PATH FILE LOKAL. Kalau dikasih URL http, dompdf 0.8.3
     *                menyalin gambar ke folder temp via tempnam(); di XAMPP
     *                macOS (Apache = user "daemon") itu gagal -> "Path cannot
     *                be empty" (Image/Cache.php:113). Path lokal dibaca
     *                langsung, tanpa nulis.
     *  PRINT/WORD -> URL absolut (browser & Word butuh URL).
     *
     * $relative_path kosong -> balikin '' (gambar dilewati, bukan error).
     */
    function checklist_image_src($relative_path, $format) {
        $relative_path = trim((string) $relative_path);
        if ($relative_path === '') {
            return '';
        }
        if ($format === 'pdf') {
            $path = ROOT . $relative_path;
            return is_readable($path) ? $path : '';
        }
        return set_url($relative_path);
    }
}

// ---------------------------------------------------------------------------
// Escape HTML + paksa ASCII
// ---------------------------------------------------------------------------
if (!function_exists('checklist_e')) {
    /**
     * phpRAD menjalankan htmlentities() lalu appendXML() ke isi report; entity
     * bernama (&nbsp; dll) bukan XML sah -> isi report hilang, yang tercetak
     * cuma kop. Jadi semua karakter non-ASCII diubah jadi entity ANGKA.
     */
    function checklist_e($v) {
        $s = htmlspecialchars((string) ($v === null ? '' : $v), ENT_QUOTES, 'UTF-8');
        if (function_exists('mb_encode_numericentity')) {
            $s = mb_encode_numericentity($s, array(0x80, 0x10FFFF, 0, 0x1FFFFF), 'UTF-8');
        }
        return $s;
    }
}

// ===========================================================================
//  ALIAS NAMA LAMA - jangan dihapus. Dipakai file yang sudah jadi:
//    app/views/partials/mesin_geprek/report.php
//    app/controllers/Mesin_geprekController.php
//    app/views/partials/_shared/dumping_report.php
//    test_preview_geprek.php
// ===========================================================================
if (!function_exists('geprek_row_val')) {
    function geprek_row_val($row, $field, $default = null) {
        return checklist_row_val($row, $field, $default);
    }
}
if (!function_exists('geprek_user_initial')) {
    function geprek_user_initial($username) {
        return checklist_user_initial($username);
    }
}
if (!function_exists('geprek_bulan_tahun_label')) {
    function geprek_bulan_tahun_label($bulan, $tahun) {
        return checklist_bulan_tahun_label($bulan, $tahun);
    }
}
