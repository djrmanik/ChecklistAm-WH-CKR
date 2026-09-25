<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/checklist_config.php
 *
 *  >>> SATU-SATUNYA TEMPAT SAKLAR REPORT LAYOUT SEMUA MESIN <<<
 *
 *  Kalau mau mengubah PERILAKU report (bukan isi/konten item), ubahnya DI SINI
 *  saja. Tidak ada saklar lain yang tersebar di file mesin mana pun.
 *
 *  Kenapa file ini ada (riwayat, 22 Sep 2026):
 *  Saklar "Diperiksa Oleh" awalnya ditulis nempel di
 *  app/views/partials/mesin_geprek/geprek_helpers.php, karena waktu report
 *  Mesin Dumping dibangun (17 Sep 2026) file geprek itu sudah terlanjur
 *  jadi satu-satunya tempat bersama (dumping_helpers.php me-require dia demi
 *  daftar inisial). Jadi saklar-nya ikut menumpang di sana - bukan karena
 *  saklar itu milik Mesin Geprek.
 *  Begitu mesin ke-5 dan seterusnya ikut pakai aturan yang sama, "semua mesin
 *  nge-root ke folder mesin_geprek" jadi menyesatkan. File ini memindahkan
 *  saklar-nya ke tempat netral. geprek_helpers.php tetap ada sebagai pintu
 *  lama (shim) supaya tidak ada satu pun file lama yang perlu diubah.
 *
 *  Rantai include-nya sekarang:
 *      checklist_config.php   (saklar)          <- paling atas, tidak require apa pun
 *          ^
 *      checklist_helpers.php  (fungsi bersama)
 *          ^                              ^
 *      checklist_report_engine.php     geprek_helpers.php (shim, buat file lama)
 *          ^
 *      machines/*.php  (profil per mesin: isi item, gambar, nama kolom)
 * ============================================================================
 */

// ---------------------------------------------------------------------------
// SAKLAR 1 - Cara memilih nama di kotak "Diperiksa Oleh"
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_DIPERIKSA_OLEH_MODE')) {
    /**
     *   'terakhir'  = approver dari record TERAKHIR di bulan itu yang sudah
     *                 di-approve (record yang belum di-approve dilewati)
     *   'terbanyak' = approver yang paling sering meng-approve di bulan itu
     *                 (kalau seri -> yang paling baru meng-approve)
     *
     * Masih menunggu konfirmasi SPV - cukup ganti nilai di baris di bawah.
     */
    define('CHECKLIST_DIPERIKSA_OLEH_MODE', 'terakhir');
}

// ---------------------------------------------------------------------------
// SAKLAR 2 - Approval otomatis dari trigger DB diabaikan atau tidak
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_DIPERIKSA_OLEH_ABAIKAN_SYSTEM')) {
    /**
     *   false (DEFAULT) = TIDAK diabaikan. Approver hasil trigger diperlakukan
     *                     sama seperti approver lain, jadi isi kotak "Diperiksa
     *                     Oleh" selalu konsisten dengan baris "Paraf Spv /
     *                     Fasilitator" di tanggal yang bersangkutan.
     *   true            = hasil trigger dilewati, cuma approver manusia yang
     *                     dipilih. Efek sampingnya: kotak bisa menampilkan nama
     *                     dari tanggal lain (bukan tanggal terakhir), dan kalau
     *                     sebulan penuh auto-approve semua, kotaknya kosong.
     *
     * Dibalikin ke false pada 18 Sep 2026 (sebelumnya sempat true) karena bikin
     * ambigu: paraf harian tanggal terakhir hasil trigger, tapi kotak "Diperiksa
     * Oleh" malah ngambil nama dari record beberapa hari sebelumnya.
     */
    define('CHECKLIST_DIPERIKSA_OLEH_ABAIKAN_SYSTEM', false);
}

// ---------------------------------------------------------------------------
// SAKLAR 3 - Username mana yang dianggap "hasil trigger auto-approve"
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_AUTO_APPROVE_USERS')) {
    /**
     * Dipisah koma, huruf besar/kecil tidak masalah.
     *
     * Dipakai 2 tempat:
     *   1) SAKLAR 2 di atas - menentukan approver mana yang dilewati
     *   2) Paraf Spv harian - approver yang masuk daftar ini ditulis 'SYS'
     *      supaya beda jelas dengan approval manual
     *
     * CATATAN (audit 21 Sep 2026): trigger `approval_mesin_geprek` yang ASLI
     * ternyata menulis user_approve = 'PAN', BUKAN 'System'. Jadi dengan nilai
     * default di bawah, data geprek asli memperlakukan PAN sebagai approver
     * manusia biasa (paraf hariannya tetap tampil 'PAN', bukan 'SYS') - ini
     * perilaku yang sekarang berjalan dan sengaja dipertahankan.
     * Kalau nanti diputuskan PAN hasil trigger harus dibedakan, ubah jadi:
     *      define('CHECKLIST_AUTO_APPROVE_USERS', 'system,pan');
     */
    define('CHECKLIST_AUTO_APPROVE_USERS', 'system');
}

// ---------------------------------------------------------------------------
// SAKLAR 4 - Tanda "N/A" untuk hari lewat yang tidak ada recordnya
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_NA_AKTIF')) {
    /**
     *   true (DEFAULT) = tanggal yang SUDAH LEWAT tapi tidak ada record sama
     *                    sekali ditandai "N/A" abu-abu (kelalaian pengisian
     *                    dibahas di meeting mingguan warehouse).
     *   false          = tanggal itu dibiarkan kosong seperti hari yang belum
     *                    tiba.
     * Tanggal hari ini & ke depan TIDAK PERNAH ditandai N/A, apa pun nilainya.
     */
    define('CHECKLIST_NA_AKTIF', true);
}

// ---------------------------------------------------------------------------
// SAKLAR 5 - Tahun paling awal yang muncul di dropdown filter periode
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_PERIODE_TAHUN_AWAL')) {
    /**
     * Dipakai oleh filter periode di sebelah tombol Export
     * (app/views/partials/_shared/report_export_filter.php).
     * Dropdown tahun diisi dari nilai ini sampai tahun berjalan.
     *
     * Kenapa 2020 (diubah 22 Sep 2026, sebelumnya 2025):
     * konstruksi pabrik Site Cikarang berjalan sejak awal 2020 dan fisiknya
     * rampung Desember 2020, jadi tidak ada data checklist yang mungkin lebih
     * tua dari itu. Record paling lama yang kelihatan di halaman list sendiri
     * ada di 2023. Angka 2025 sebelumnya kesempitan - report tahun 2023 & 2024
     * jadi tidak bisa ditarik sama sekali.
     *
     * Mau mundur lagi? Turunkan angkanya. Tahun yang sedang dipilih selalu
     * ikut muncul walau di luar rentang ini, jadi link report lama tidak
     * pernah rusak.
     */
    define('CHECKLIST_PERIODE_TAHUN_AWAL', 2020);
}

// ---------------------------------------------------------------------------
// SAKLAR 6 - Nomor unit ikut ditampilkan di sel "Mesin/Line"
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_TAMPILKAN_UNIT_DI_KOP')) {
    /**
     * Untuk mesin yang punya kolom nomor unit (AGV: `no_agv`, nanti Forklift:
     * `no_forklift`, Pallet Mover: `no_palletmover`).
     *
     *   true (DEFAULT) = sel Mesin/Line jadi "AGV Table Top Lift (AGV-01)".
     *                    Kalau di bulan itu ada lebih dari satu nomor unit,
     *                    semuanya ditulis dipisah koma - ini sekaligus jadi
     *                    tanda kalau ternyata satu halaman dipakai banyak unit.
     *   false          = cuma nama mesin, nomor unit tidak ditampilkan.
     */
    define('CHECKLIST_TAMPILKAN_UNIT_DI_KOP', true);
}

// ---------------------------------------------------------------------------
// SAKLAR 7 - Kotak "Diperiksa Oleh" pakai NAMA UTUH, bukan inisial
// ---------------------------------------------------------------------------
if (!defined('CHECKLIST_DIPERIKSA_OLEH_NAMA_UTUH')) {
    /**
     * PEMBAGIAN TUGAS YANG DIPEGANG (dikonfirmasi 22 Sep 2026):
     *   - INISIAL 3 huruf  -> HANYA untuk baris paraf HARIAN, yaitu
     *                         "Paraf Pelaksana" dan "Paraf Spv / Fasilitator".
     *   - NAMA UTUH        -> untuk kotak "Diperiksa Oleh" di kop kanan atas.
     *
     *   true (DEFAULT) = kotak "Diperiksa Oleh" selalu ditulis sebagai nama
     *                    orang, apa pun bentuk nilai di kolom user_approve:
     *                       'pramono'          -> "Pramono Nugroho"
     *                       'pramono.nugroho'  -> "Pramono Nugroho"
     *                       'PAN'              -> "Pramono Nugroho"
     *                       'System'           -> "System"
     *                    Perlu karena trigger auto-approve DB menulis kode
     *                    'PAN' ke user_approve; tanpa ini kotaknya ikut
     *                    tertulis "PAN" (inisial), bukan nama.
     *   false          = tulis apa adanya dari DB (perilaku sebelum 22 Sep).
     *
     * Daftar nama utuhnya ada di checklist_display_name()
     * (checklist_helpers.php) - itu yang diedit kalau ejaan namanya mau
     * diubah, misal cuma mau "Pramono" tanpa surname.
     *
     * Saklar ini TIDAK mempengaruhi baris paraf harian. Paraf harian tetap
     * inisial, dan tetap menulis 'SYS' untuk approver hasil trigger
     * (lihat SAKLAR 3).
     */
    define('CHECKLIST_DIPERIKSA_OLEH_NAMA_UTUH', true);
}
