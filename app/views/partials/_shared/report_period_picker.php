<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/report_period_picker.php
 *
 *  >>> FILE INI CUMA PINTU LAMA (SHIM). ISINYA PINDAH. <<<
 *
 *  Waktu dibuat (22 Sep 2026 pagi) isinya baru filter periode (bulan & tahun).
 *  Sorenya filter unit (nomor AGV) ikut masuk, jadi namanya tidak lagi jujur
 *  dan file-nya diganti nama jadi:
 *
 *      app/views/partials/_shared/report_export_filter.php
 *
 *  File ini dipertahankan supaya list.php yang masih meng-include nama lama
 *  tetap jalan (tidak jadi fatal error "failed to open stream").
 *
 *  KODE BARU jangan include file ini - include report_export_filter.php.
 *
 *  Catatan: filter unit butuh $report_filter_profile di-set dulu sebelum
 *  include. Lewat file lama ini variabel itu tetap kebawa (sama-sama satu
 *  scope), jadi keduanya setara.
 * ============================================================================
 */

include __DIR__ . '/report_export_filter.php';
