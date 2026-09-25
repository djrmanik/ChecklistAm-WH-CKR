<?php
/**
 * ============================================================================
 *  app/views/partials/mesin_geprek/geprek_helpers.php
 *
 *  >>> FILE INI SEKARANG CUMA PINTU LAMA (SHIM). ISINYA SUDAH PINDAH. <<<
 *
 *  Sampai 21 Sep 2026 file ini menampung dua hal sekaligus:
 *    1) helper khusus Mesin Geprek, DAN
 *    2) saklar + fungsi bersama SEMUA mesin (karena dumping_helpers.php
 *       me-require file ini demi daftar inisial).
 *
 *  Nomor 2 bikin janggal: saklar "Diperiksa Oleh" milik semua mesin, tapi
 *  tempatnya di folder mesin_geprek - susah ditelusuri begitu mesin ke-5 dan
 *  seterusnya ikut pakai. Per 22 Sep 2026 isinya dipindah ke:
 *
 *      app/views/partials/_shared/checklist_config.php   <- SEMUA SAKLAR
 *      app/views/partials/_shared/checklist_helpers.php  <- fungsi bersama
 *
 *  File ini sengaja DIPERTAHANKAN supaya file lama yang sudah jalan
 *  (report.php geprek, Mesin_geprekController.php, test_preview_geprek.php,
 *  dumping_helpers.php, dumping_report.php) tidak perlu diubah satu baris pun.
 *  Semua fungsi & konstanta yang dulu ada di sini tetap tersedia setelah
 *  baris require di bawah - termasuk nama lama geprek_row_val(),
 *  geprek_user_initial(), geprek_bulan_tahun_label().
 *
 *  KODE BARU JANGAN me-require file ini. Pakai langsung:
 *      require_once __DIR__ . '/../_shared/checklist_helpers.php';
 * ============================================================================
 */

require_once __DIR__ . '/../_shared/checklist_helpers.php';
