<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/dumping_report.php
 *
 *  >>> FILE INI SEKARANG CUMA PINTU LAMA (SHIM). <<<
 *
 *  Tata letak report (kop, header, sub header, kolom foto, 3 section, baris
 *  Paraf Pelaksana & Paraf Spv) dipindah ke
 *      app/views/partials/_shared/checklist_report_body.php
 *  supaya dipakai bareng SEMUA mesin - layout mesin dumping memang dijadikan
 *  standar untuk seluruh checklist.
 *
 *  Isi item 3 mesin dumping pindah ke
 *      app/views/partials/_shared/machines/dumping.php
 *
 *  File ini dipertahankan supaya pemanggil lama (yang menyiapkan $data versi
 *  lama: logo_image / photo_atas / photo_bawah, tanpa 'sections') tetap jalan.
 * ============================================================================
 */

require_once __DIR__ . '/checklist_report_engine.php';

if (!isset($data) && isset($this) && isset($this->view_data)) {
    $data = $this->view_data;
}
$data = (array) (isset($data) ? $data : array());

// Terjemahkan kontrak $data versi lama ke kontrak baru.
if (!isset($data['sections'])) {
    $conf = checklist_load_machine('dumping');
    $data['sections']    = $conf['sections'];
    $data['pelaksanaan'] = isset($data['pelaksanaan']) ? $data['pelaksanaan'] : $conf['pelaksanaan'];
    $data['img_size']    = isset($data['img_size'])    ? $data['img_size']    : $conf['img_size'];
    $data['images']      = array(
        'logo'  => isset($data['logo_image'])  ? $data['logo_image']  : '',
        'atas'  => isset($data['photo_atas'])  ? $data['photo_atas']  : '',
        'bawah' => isset($data['photo_bawah']) ? $data['photo_bawah'] : '',
    );
}

// Nama variabel format versi lama -> versi baru.
if (!isset($checklist_format) && isset($dumping_format)) {
    $checklist_format = $dumping_format;
}

if (!function_exists('dumping_e')) {
    function dumping_e($v) { return checklist_e($v); }
}

include __DIR__ . '/checklist_report_body.php';
