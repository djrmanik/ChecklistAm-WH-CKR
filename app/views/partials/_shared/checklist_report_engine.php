<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/checklist_report_engine.php
 *
 *  MESIN PENGGERAK Report Layout (PRINT / PDF / WORD) untuk SEMUA mesin.
 *  Generalisasi dari dumping_helpers.php (22 Sep 2026).
 *
 *  Controller mesin cukup punya blok short-circuit 4 baris:
 *
 *      require_once __DIR__ . '/../views/partials/_shared/checklist_report_engine.php';
 *      ...
 *      $fmt = checklist_export_format($request);
 *      if ($fmt !== '') {
 *          return checklist_render_report($this->GetModel(), 'agv_table_top_lift', $request, $fmt);
 *      }
 *
 *  Sisanya (isi item, nama kolom DB, gambar, area, judul) ada di file profil
 *  app/views/partials/_shared/machines/<nama_profil>.php - SATU file per mesin,
 *  gampang dicari, dan tidak ada mesin yang perlu mengintip file mesin lain.
 *
 *  Saklar perilaku: checklist_config.php (bukan di sini, bukan di profil).
 * ============================================================================
 */

require_once __DIR__ . '/checklist_helpers.php';

if (!function_exists('checklist_export_format')) {
    /**
     * Format export yang ditangani report layout kita sendiri.
     * CSV & EXCEL sengaja dikembalikan '' supaya lanjut ke perilaku bawaan
     * phpRAD (dump data mentah) - sesuai keputusan awal di Mesin Geprek.
     *
     * Tombol Export mengirim format HURUF BESAR, jadi selalu di-lowercase dulu.
     *
     * @return string 'print' | 'pdf' | 'word' | '' (bukan urusan kita)
     */
    function checklist_export_format($request) {
        $fmt = strtolower(trim((string) (isset($request->format) ? $request->format : '')));
        return in_array($fmt, array('print', 'pdf', 'word'), true) ? $fmt : '';
    }
}

if (!function_exists('checklist_load_machine')) {
    /**
     * Baca file profil mesin. $overrides dipakai kalau satu profil dipakai
     * beberapa mesin kembar (contoh: 3 mesin dumping -> beda 'table' &
     * 'machine_code' saja).
     *
     * @param string $profile   nama file di folder machines/ tanpa .php
     * @param array  $overrides key profil yang mau ditimpa
     * @return array profil lengkap (sudah diberi nilai default)
     */
    function checklist_load_machine($profile, $overrides = array()) {
        $file = __DIR__ . '/machines/' . basename($profile) . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Profil mesin tidak ditemukan: $file");
        }
        $conf = include $file;
        if (!is_array($conf)) {
            throw new \RuntimeException("Profil mesin $profile harus mengembalikan array.");
        }
        $conf = array_merge($conf, (array) $overrides);

        $defaults = array(
            'key'            => $profile,
            'table'          => '',
            'machine_code'   => '',
            'area'           => '',
            'title'          => '',          // judul halaman/dokumen
            'file_slug'      => '',          // nama file unduhan (tanpa tanggal)
            'user_field'     => 'user_created',   // AWAS: dumping pakai 'user_create' (tanpa "d")
            'approve_field'  => 'user_approve',
            'date_field'     => 'date_created',
            'unit_field'     => '',          // '' = mesin ini tidak punya nomor unit
            'units'          => array(),     // daftar unit fisik; lihat checklist_machine_units()
            'pelaksanaan'    => '',
            'images'         => array(),     // path relatif; '' = tidak ada gambar
            'img_size'       => array(),
            'sections'       => array(),
            'layout'         => array(),     // opsional: lebar kolom teks & ukuran huruf item (23 Sep 2026)
        );
        $conf = array_merge($defaults, $conf);

        if ($conf['table'] === '') {
            throw new \RuntimeException("Profil mesin $profile belum punya 'table'.");
        }
        if ($conf['machine_code'] === '') { $conf['machine_code'] = strtoupper($conf['table']); }
        if ($conf['title'] === '')        { $conf['title']        = 'Checklist AM ' . $conf['machine_code']; }
        if ($conf['file_slug'] === '')    { $conf['file_slug']    = preg_replace('/[^A-Za-z0-9]+/', '-', $conf['machine_code']); }

        return $conf;
    }
}

if (!function_exists('checklist_machine_units')) {
    /**
     * Daftar unit fisik satu halaman, sudah dirapikan & diberi slug.
     *
     * Bentuk di file profil:
     *     'units' => array(
     *         array('label' => 'AGV TTL 7', 'alias' => array('AGV 7', 'AGV TTL 7')),
     *         ...
     *     )
     *
     *   label = yang dipakai di dropdown, di sel Mesin/Line, dan di nama file
     *   alias = SEMUA penulisan yang pernah/mungkin dipakai di kolom unit DB
     *           untuk unit fisik yang sama. Perbandingannya lewat
     *           checklist_norm_unit() (abaikan spasi & huruf besar-kecil),
     *           jadi 'AGV CB 1' & 'AGVCB1' cukup ditulis salah satu.
     *
     * Slug dibuat otomatis dari label ('AGV TTL 7' -> 'agv-ttl-7') dan itu
     * yang muncul di URL sebagai ?unit=agv-ttl-7 - lebih enak dibaca daripada
     * label ber-spasi yang harus di-encode.
     *
     * @return array [ slug => array('label'=>..., 'norms'=>array(...)) ]
     */
    function checklist_machine_units($conf) {
        $out = array();
        foreach ((array) $conf['units'] as $u) {
            $label = isset($u['label']) ? trim((string) $u['label']) : '';
            if ($label === '') { continue; }
            $slug  = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $label), '-'));
            $norms = array();
            $alias = isset($u['alias']) ? (array) $u['alias'] : array();
            $alias[] = $label; // label sendiri selalu ikut jadi alias
            foreach ($alias as $a) {
                $n = checklist_norm_unit($a);
                if ($n !== '') { $norms[$n] = true; }
            }
            $out[$slug] = array('label' => $label, 'norms' => array_keys($norms));
        }
        return $out;
    }
}

if (!function_exists('checklist_filter_rows_by_unit')) {
    /**
     * Saring baris hasil query supaya cuma menyisakan 1 unit fisik.
     * Pencocokan lewat checklist_norm_unit(), jadi 'AGV 7' & 'AGV TTL 7'
     * dianggap unit yang sama kalau dua-duanya terdaftar sebagai alias.
     *
     * Baris yang nilai unitnya TIDAK terdaftar di unit mana pun (misal salah
     * ketik waktu input) tidak akan muncul di filter unit mana pun - hanya
     * kelihatan lewat opsi "Semua unit".
     */
    function checklist_filter_rows_by_unit($rows, $unit_field, $norms) {
        if ($unit_field === '' || empty($norms)) {
            return $rows;
        }
        $want = array_flip($norms);
        $out  = array();
        foreach ((array) $rows as $row) {
            $n = checklist_norm_unit(checklist_row_val($row, $unit_field, ''));
            if ($n !== '' && isset($want[$n])) {
                $out[] = $row;
            }
        }
        return $out;
    }
}

if (!function_exists('checklist_machine_db_fields')) {
    /** Semua kolom DB yang dipakai item di seluruh section profil ini. */
    function checklist_machine_db_fields($conf) {
        $out = array();
        foreach ($conf['sections'] as $section) {
            if (empty($section['items'])) { continue; }
            foreach ($section['items'] as $item) {
                if (!empty($item['db'])) { $out[$item['db']] = true; }
            }
        }
        return array_keys($out);
    }
}

if (!function_exists('checklist_collect_report_data')) {
    /**
     * Query 1 bulan data, lalu pivot jadi grid $checks[kolom][tanggal].
     * Tidak mengeluarkan output apa pun.
     *
     * @param object $db     model PDODb dari $this->GetModel()
     * @param array  $conf   profil mesin (hasil checklist_load_machine)
     * @param int    $bulan, $tahun
     * @param string $format 'print' | 'pdf' | 'word'
     * @param array  $unit   satu entri dari checklist_machine_units():
     *                       array('label'=>'AGV TTL 7', 'norms'=>array('AGV7','AGVTTL7'))
     *                       array() = tidak difilter (semua unit digabung)
     * @return array $data untuk checklist_report_body.php
     */
    function checklist_collect_report_data($db, $conf, $bulan, $tahun, $format = 'print', $unit = array()) {
        $tablename = $conf['table'];
        $date_f    = $conf['date_field'];
        $user_f    = $conf['user_field'];
        $appr_f    = $conf['approve_field'];
        $unit_f    = $conf['unit_field'];

        $day_count = (int) date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
        $dbFields  = checklist_machine_db_fields($conf);

        $meta = array('id', $date_f, $user_f, $appr_f);
        if ($unit_f !== '') { $meta[] = $unit_f; }
        $fields = array_values(array_unique(array_merge($meta, $dbFields)));

        $db->where("MONTH($tablename.$date_f) = ?", array($bulan));
        $db->where("YEAR($tablename.$date_f) = ?", array($tahun));
        $db->orderBy("$tablename.$date_f", "ASC");

        // array(OFFSET, LIMIT) - WAJIB offset 0. array(1, 1000) membuang baris
        // paling awal bulan itu (bug yang pernah kejadian di geprek).
        $rows = $db->get($tablename, array(0, 1000), $fields);

        if ($db->getLastError()) {
            throw new \RuntimeException("DB error saat query laporan $tablename: " . $db->getLastError());
        }

        // Filter unit dikerjakan DI SINI, bukan di SQL - lihat alasannya di
        // checklist_norm_unit(). Ini yang bikin 'AGV 7' & 'AGV TTL 7' ketarik
        // bareng selama dua-duanya terdaftar sebagai alias unit yang sama.
        $unit_label = '';
        if (!empty($unit['norms'])) {
            $rows       = checklist_filter_rows_by_unit($rows, $unit_f, $unit['norms']);
            $unit_label = isset($unit['label']) ? $unit['label'] : '';
        }

        $checks = array();
        foreach ($dbFields as $f) {
            $checks[$f] = array_fill(1, $day_count, null);
        }
        $pelaksana   = array();
        $approver    = array();
        $filled_days = array(); // tanggal yang punya record (buat aturan N/A)
        $units       = array();

        foreach ((array) $rows as $row) {
            $tgl = checklist_row_val($row, $date_f);
            if (!$tgl) { continue; }
            $day = (int) date('j', strtotime($tgl));
            if ($day < 1 || $day > $day_count) { continue; }

            foreach ($dbFields as $f) {
                $val = checklist_row_val($row, $f);
                if ($val !== null && $val !== '') {
                    $checks[$f][$day] = $val; // apa adanya dari DB
                }
            }
            // Username disimpan MENTAH. Konversi ke inisial cuma sekali, di view.
            $pelaksana[$day]   = checklist_row_val($row, $user_f);
            $approver[$day]    = checklist_row_val($row, $appr_f);
            $filled_days[$day] = true;

            if ($unit_f !== '') {
                $u = trim((string) checklist_row_val($row, $unit_f, ''));
                if ($u !== '') { $units[$u] = true; }
            }
        }

        // Nama mesin di sel Mesin/Line. SAKLAR 6 di checklist_config.php.
        //  - difilter per unit -> pakai LABEL RESMI unit itu, bukan tulisan
        //    mentah dari DB (yang bisa campur 'AGV 7' & 'AGV TTL 7' dalam satu
        //    bulan untuk mesin yang sama - bikin kop kelihatan seperti 2 unit).
        //  - tanpa filter      -> tulis semua nomor unit yang ketemu apa adanya,
        //    sekaligus jadi tanda kalau halaman ini memang dipakai banyak unit.
        $nama_mesin = $conf['machine_code'];
        if (CHECKLIST_TAMPILKAN_UNIT_DI_KOP) {
            if ($unit_label !== '') {
                // (23 Sep 2026) label unit = nama mesin (mis. 'Pallet Stacker')
                // -> tidak ditulis dua kali "Pallet Stacker (Pallet Stacker)".
                if (checklist_norm_unit($unit_label) !== checklist_norm_unit($conf['machine_code'])) {
                    $nama_mesin .= ' (' . $unit_label . ')';
                }
            } elseif (!empty($units)) {
                $nama_mesin .= ' (' . implode(', ', array_keys($units)) . ')';
            }
        }

        // Gambar: PDF = path lokal, PRINT/WORD = URL. Lihat checklist_image_src().
        $images = array();
        foreach ((array) $conf['images'] as $k => $rel) {
            $images[$k] = checklist_image_src($rel, $format);
        }

        return array(
            'area'           => $conf['area'],
            'nama_mesin'     => $nama_mesin,
            // SAKLAR 1 & 2 di checklist_config.php
            'diperiksa_oleh' => checklist_diperiksa_oleh($rows, CHECKLIST_DIPERIKSA_OLEH_MODE, null, $appr_f),
            'bulan_tahun'    => checklist_bulan_tahun_label($bulan, $tahun),
            'day_count'      => $day_count,
            'sections'       => $conf['sections'],
            'pelaksanaan'    => $conf['pelaksanaan'],
            'images'         => $images,
            'img_size'       => $conf['img_size'],
            'layout'         => $conf['layout'],
            'checks'         => $checks,
            'pelaksana'      => $pelaksana,
            'approver'       => $approver,
            'na_days'        => checklist_na_days($bulan, $tahun, $day_count, $filled_days),
            'row_count'      => count((array) $rows),
        );
    }
}

if (!function_exists('checklist_build_report_html')) {
    /**
     * Gabungkan layout pembungkus + body report jadi satu string HTML.
     * PRINT, PDF, WORD semuanya dari HTML yang sama.
     *
     * @param array $data  SATU $data, ATAU daftar $data (satu per halaman).
     *                     Daftar dipakai opsi "Semua unit": 1 unit = 1 halaman,
     *                     dipisah page-break. Dibedakan dari key 'sections' -
     *                     satu $data pasti punya key itu, daftar tidak.
     */
    function checklist_build_report_html($data, $title, $format) {
        $layout_file = ROOT . 'app/views/layouts/report_layout_geprek.php';
        $single_file = ROOT . 'app/views/partials/_shared/checklist_report_body.php';
        $multi_file  = ROOT . 'app/views/partials/_shared/checklist_report_multi.php';

        $is_multi  = !isset($data['sections']);
        $view_file = $is_multi ? $multi_file : $single_file;

        $missing = array();
        if (!is_file($layout_file)) { $missing[] = $layout_file; }
        if (!is_file($view_file))   { $missing[] = $view_file; }
        if (!empty($missing)) {
            throw new \RuntimeException("SETUP BELUM LENGKAP - file belum ada: " . implode(' | ', $missing));
        }

        if ($is_multi) {
            // checklist_report_multi.php membaca $data_list, bukan $data.
            $data_list = array_values((array) $data);
            $data      = null;
        }

        // Variable ini dibaca oleh report_layout_geprek.php (scope include).
        $geprek_report_title = $title;
        $geprek_paper_size   = 'A4';
        $geprek_orientation  = 'landscape';
        $geprek_force_print  = ($format === 'print');
        $geprek_view_file    = $view_file;
        $checklist_format    = $format; // dibaca checklist_report_body.php
        $dumping_format      = $format; // kompatibilitas file lama

        ob_start();
        include $layout_file;
        return ob_get_clean();
    }
}

if (!function_exists('checklist_pdf_bytes')) {
    /**
     * Render HTML jadi PDF (dompdf). Warning/notice dompdf ditampung lalu
     * dibuang supaya header PDF tidak gagal terkirim ("headers already sent").
     */
    function checklist_pdf_bytes($html) {
        // [GENC-25SEP26-AUDIT] "PDF semua unit" Forklift (5 lembar) butuh > 128 MB di dompdf 0.8.3 -> Fatal "Allowed memory size".
        // Naikkan batas HANYA untuk request PDF ini (isi PDF tidak berubah).
        $lim = ini_get('memory_limit');
        $num = (int) $lim; $u = strtoupper(substr(trim((string) $lim), -1));
        $bytes_lim = $u === 'G' ? $num * 1073741824 : ($u === 'M' ? $num * 1048576 : ($u === 'K' ? $num * 1024 : $num));
        if ($num !== -1 && $bytes_lim < 536870912) { @ini_set('memory_limit', '512M'); }
        if (function_exists('set_time_limit')) { @set_time_limit(180); }
        $prev_display_errors = ini_get('display_errors');
        ini_set('display_errors', '0');
        ob_start();
        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $bytes = $dompdf->output();
        } catch (\Throwable $e) {
            ob_end_clean();
            ini_set('display_errors', $prev_display_errors);
            throw $e;
        }
        ob_end_clean();
        ini_set('display_errors', $prev_display_errors);
        return $bytes;
    }
}

if (!function_exists('checklist_render_report')) {
    /**
     * Entry point dari blok short-circuit di index() controller.
     * Error apa pun ditampilkan sebagai teks (bukan halaman blank putih).
     *
     * @param object $db
     * @param string $profile   nama file profil di machines/ (tanpa .php)
     * @param object $request
     * @param string $format    'print' | 'pdf' | 'word' (sudah lowercase)
     * @param array  $overrides timpa isi profil (dipakai mesin kembar)
     *
     * Parameter URL yang dibaca: ?bulan= ?tahun= ?unit=
     * ?unit= isinya SLUG unit ('agv-ttl-7'), atau 'semua' / kosong.
     * Aturan default unit (mesin yang punya daftar 'units'):
     *   - tidak ada ?unit=        -> UNIT PERTAMA di daftar profil
     *   - ?unit=semua             -> semua unit, 1 unit = 1 halaman
     *   - ?unit=<slug tidak dikenal> -> diperlakukan seperti unit pertama
     * Mesin tanpa daftar 'units' (geprek, dumping) mengabaikan ?unit= total.
     */
    function checklist_render_report($db, $profile, $request, $format, $overrides = array()) {
        $tablename = is_array($overrides) && !empty($overrides['table']) ? $overrides['table'] : $profile;
        try {
            $conf  = checklist_load_machine($profile, $overrides);
            $bulan = !empty($request->bulan) ? (int) $request->bulan : (int) date('n');
            $tahun = !empty($request->tahun) ? (int) $request->tahun : (int) date('Y');
            $unit_req = isset($request->unit) ? strtolower(trim((string) $request->unit)) : '';

            // Jaga-jaga kalau parameter di URL diutak-atik manual.
            if ($bulan < 1 || $bulan > 12) { $bulan = (int) date('n'); }
            if ($tahun < 2000 || $tahun > 2100) { $tahun = (int) date('Y'); }

            $tablename = $conf['table'];
            $units     = checklist_machine_units($conf);
            $filename  = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-Checklist-AM-' . $conf['file_slug'];

            if (empty($units)) {
                // Mesin tanpa daftar unit - persis seperti sebelumnya.
                $data = checklist_collect_report_data($db, $conf, $bulan, $tahun, $format);
                $html = checklist_build_report_html($data, $conf['title'], $format);
            } elseif ($unit_req === 'semua') {
                // 1 unit = 1 halaman. Lihat checklist_report_multi.php.
                $list = array();
                foreach ($units as $u) {
                    $list[] = checklist_collect_report_data($db, $conf, $bulan, $tahun, $format, $u);
                }
                $filename .= '-Semua-Unit';
                $html = checklist_build_report_html($list, $conf['title'] . ' - Semua Unit', $format);
            } else {
                // Satu unit. Slug tidak dikenal / tidak diisi -> unit pertama,
                // supaya report tidak pernah diam-diam mencampur banyak unit
                // dalam satu grid tanggal (jebakan BLUEPRINT 6.2).
                $slug = isset($units[$unit_req]) ? $unit_req : key($units);
                $u    = $units[$slug];
                $data = checklist_collect_report_data($db, $conf, $bulan, $tahun, $format, $u);
                $filename .= '-' . preg_replace('/[^A-Za-z0-9]+/', '-', $u['label']);
                $html = checklist_build_report_html($data, $conf['title'] . ' - ' . $u['label'], $format);
            }

            if ($format === 'word') {
                $htd = new Html2Doc();
                $htd->createDoc($html, $filename . '.doc', true);
                exit;
            }

            if ($format === 'pdf') {
                $pdf = checklist_pdf_bytes($html);
                if (!headers_sent()) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $filename . '.pdf"');
                    header('Content-Length: ' . strlen($pdf));
                }
                echo $pdf;
                exit;
            }

            // print
            if (!headers_sent()) {
                header('Content-Type: text/html; charset=UTF-8');
            }
            echo $html;
            exit;
        } catch (\Throwable $e) {
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
            }
            echo "REPORT ERROR ($tablename)\n";
            echo "Message : " . $e->getMessage() . "\n";
            echo "File    : " . $e->getFile() . "\n";
            echo "Line    : " . $e->getLine() . "\n";
            echo "Trace   :\n" . $e->getTraceAsString() . "\n";
            exit;
        }
    }
}
