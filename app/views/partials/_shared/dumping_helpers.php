<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/dumping_helpers.php
 *
 *  >>> FILE INI SEKARANG CUMA PINTU LAMA (SHIM). LOGIKANYA SUDAH PINDAH. <<<
 *
 *  Sampai 21 Sep 2026 file ini memegang seluruh logic report 3 mesin dumping.
 *  Per 22 Sep 2026 logic-nya digeneralisasi jadi mesin bersama semua checklist:
 *
 *      checklist_report_engine.php            <- query, pivot, PDF/Word/Print
 *      checklist_report_body.php              <- tata letak tabel report
 *      machines/dumping.php                   <- isi item 3 mesin dumping
 *
 *  Controller Kir3/Kir6/Kir7 TIDAK perlu diubah: fungsi
 *  dumping_render_report() di bawah tetap ada dengan parameter yang sama
 *  persis, cuma sekarang meneruskan ke engine bersama.
 *
 *  Tampilan hasilnya identik dengan versi 21 Sep 2026 (sudah dibandingkan
 *  HTML-nya baris per baris waktu refactor).
 * ============================================================================
 */

require_once __DIR__ . '/checklist_report_engine.php';

if (!function_exists('dumping_render_report')) {
    /**
     * Entry point lama dari blok short-circuit di index() controller KIR.
     *
     * @param object $db
     * @param string $tablename     'kir3p01dp001' / 'kir6p01dp001' / 'kir7p01dp001'
     * @param string $machine_code  'KIR3P01DP001' / ...
     * @param object $request
     * @param string $format        'print' | 'pdf' | 'word' (sudah lowercase)
     */
    function dumping_render_report($db, $tablename, $machine_code, $request, $format) {
        return checklist_render_report($db, 'dumping', $request, $format, array(
            'table'        => $tablename,
            'machine_code' => $machine_code,
            'title'        => 'Checklist AM Mesin Dumping ' . $machine_code,
            'file_slug'    => $machine_code,
        ));
    }
}

// --- alias nama lama, jaga-jaga masih dipanggil dari file lain -------------
if (!function_exists('dumping_check_fields')) {
    function dumping_check_fields() {
        return checklist_machine_db_fields(checklist_load_machine('dumping'));
    }
}
if (!function_exists('dumping_approver_initial')) {
    function dumping_approver_initial($username) { return checklist_approver_initial($username); }
}
if (!function_exists('dumping_pdf_bytes')) {
    function dumping_pdf_bytes($html) { return checklist_pdf_bytes($html); }
}
if (!function_exists('dumping_build_report_html')) {
    function dumping_build_report_html($data, $title, $format) { return checklist_build_report_html($data, $title, $format); }
}
if (!function_exists('dumping_collect_report_data')) {
    function dumping_collect_report_data($db, $tablename, $machine_code, $bulan, $tahun, $format = 'print') {
        $conf = checklist_load_machine('dumping', array('table' => $tablename, 'machine_code' => $machine_code));
        return checklist_collect_report_data($db, $conf, $bulan, $tahun, $format);
    }
}
