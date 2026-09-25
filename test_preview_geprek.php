<?php
/**
 * test_preview_geprek.php — FILE TESTING DOANG, taro sejajar folder "app"
 * (misal: C:\xampp\htdocs\checklistamwarehouseckr\test_preview_geprek.php)
 *
 * Sebelum buka di browser, pastikan:
 * 1. mesin_geprek_report.php (yang gue kasih) ditaro di:
 *      app/views/partials/mesin_geprek/report.php
 *    (bikin folder "mesin_geprek" kalau belum ada — nama controllernya)
 * 2. geprek_photo_panel.png (yang gue kasih) ditaro di:
 *      assets/images/geprek_photo_panel.png
 * 3. Buka browser ke: localhost/checklistamwarehouseckr/test_preview_geprek.php
 *
 * Data di bawah ini CONTOH doang (niru pola dari screenshot lo: 13 hari,
 * mayoritas OK, 1 NOK biar keliatan bedanya) — bukan data asli dari database.
 */
header('Content-Type: text/html; charset=UTF-8');
$dbFields = ['tatakan_jumbo_bag','punch','body_mesin_geprek','panel_hmi','sensor','as_punch','rantai_utama','roda_tatakan_jumbobag','tombol_panel_kabel','mesin_compressor','baut_body_mesin_geprek','putaran_tatakan_jumbobag','selang_hidroplik_olimotor','baut_punch'];
$days = [1,2,3,4,5,6,7,10,11,12,13,14,18]; // sesuai tanggal di screenshot
$pelaksanaByDay = [1=>'candra',2=>'edy.haryanto',3=>'edy.haryanto',4=>'edy.haryanto',5=>'edy.haryanto',6=>'edy.haryanto',7=>'edy.haryanto',10=>'edy.haryanto',11=>'edy.haryanto',12=>'edy.haryanto',13=>'edy.haryanto',14=>'edy.haryanto',18=>'edy.haryanto'];
$checks = [];
foreach ($dbFields as $f) {
    $checks[$f] = [];
    foreach ($days as $d) {
        $checks[$f][$d] = 'OK';
    }
}
// bikin 1 NOK contoh di tanggal 5, item 'sensor'
$checks['sensor'][5] = 'NOK';

$data = [
    'lokasi' => '4M',
    'nama_mesin' => 'GEPREK (PRESS)',
    'diperiksa_oleh' => 'pramono',
    'bulan_tahun' => 'Agustus 2026',
    'day_count' => 31,
    'photo_image' => 'assets/images/geprek_photo_panel.png',
    'checks' => $checks,
    'pelaksana' => $pelaksanaByDay,
];

// Ganti path ini kalau lokasi report.php kamu beda
include __DIR__ . '/app/views/partials/mesin_geprek/report.php';
