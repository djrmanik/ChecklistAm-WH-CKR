<?php
/**
 * ============================================================================
 *  PROFIL MESIN - PALLET MOVER (4 unit)                dibuat 23 Sep 2026
 *
 *  Dipakai tombol Export di tab Pallet Mover 1..4 halaman Palletmover.
 *  1 tab = 1 report = 1 unit (PalletmoverController::palletmover_report_export).
 *  Tab "Pallet Stacker" pakai profil palletstacker.php (isi item sama).
 *
 *  SUMBER ISI:
 *   - Nama Part, Alat, Metode, Standard, Durasi -> tabel Metode/Alat/Standard/
 *     Durasi di halaman input palletmover/add.php.                      [FAKTA]
 *     Standard yang panjang DIRINGKAS tanpa mengubah maksud (pola yang sama
 *     dengan forklift), supaya report tetap 1 halaman & kotak paraf harian
 *     selebar mesin lain.
 *   - Semua item di section INSPECTION - halaman input cuma punya subjudul
 *     "STANDAR PEMERIKSAAN (INSPECTION)".                              [FAKTA]
 *     CLEANING & LUBRICATING dicetak kosong supaya bentuk form seragam.
 *   - 'area' & foto: belum ada -> kosong. (Foto di halaman input juga kosong.)
 *
 *  [GENC-24SEP26-PALLETMOVER] + 'short' per section (judul kartu halaman Gen C).
 *  Item, unit, isi report TIDAK berubah. Belum ada 'foto' per item -> halaman
 *  menampilkan kotak "Belum ada foto" (K19); isi 'foto' => 'assets/images/palletmover/item_NN.jpg'
 *  begitu foto dari SPV ada.
 *
 *  UNIT: nilai kolom no_palletmover = Menu::$no_palletmover, persis sama
 *  dengan filter tab di palletmover/list.php.                          [FAKTA]
 * ============================================================================
 */

return array(
    'key'           => 'palletmover',
    'table'         => 'palletmover',
    'machine_code'  => 'Pallet Mover',
    'area'          => '',                        // belum ada dari SPV
    'title'         => 'Checklist AM Pallet Mover',
    'file_slug'     => 'Pallet-Mover',
    'user_field'    => 'user_created',
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => 'no_palletmover',
    'pelaksanaan'   => 'Setiap hari diawal shift 1', // [FAKTA] halaman input

    'images' => array(
        'logo'  => 'assets/images/dumping_logo.png',
        'atas'  => '',
        'bawah' => '',
    ),
    'img_size' => array(
        'logo'  => array(138, 40),
    ),

    // Tanpa 'layout' -> lebar kolom & ukuran huruf DEFAULT (sama persis dengan
    // mesin dumping). 8 item muat 1 halaman.

    'units' => array(
        array('label' => 'Pallet Mover 1', 'alias' => array('Pallet Mover 1')),
        array('label' => 'Pallet Mover 2', 'alias' => array('Pallet Mover 2')),
        array('label' => 'Pallet Mover 3', 'alias' => array('Pallet Mover 3')),
        array('label' => 'Pallet Mover 4', 'alias' => array('Pallet Mover 4')),
    ),

    'sections' => array(
        array(
            'key' => 'cleaning', 'title' => 'STANDAR PEMBERSIHAN (CLEANING)', 'short' => 'Pembersihan',   // [GENC-24SEP26-PALLETMOVER] 'short' = judul kartu Gen C, report tidak membacanya
            'standard_label' => 'Pembersihan standar',
            'photo' => 'atas', 'photo_label' => 'Gambar Bagian Mesin',
            'blank_rows' => 2, 'items' => array(),
        ),
        array(
            'key' => 'lubricating', 'title' => 'STANDAR PELUMASAN (LUBRICATING)', 'short' => 'Pelumasan',
            'standard_label' => 'Pelumasan standar',
            'photo' => '', 'photo_label' => '',
            'blank_rows' => 2, 'items' => array(),
        ),
        array(
            'key' => 'inspection', 'title' => 'STANDAR PENGECEKAN (INSPECTION)', 'short' => 'Pengecekan',
            'standard_label' => 'Pengecekan standar',
            'photo' => 'bawah', 'photo_label' => '',
            'blank_rows' => 0,
            'items' => array(
                array('no' => 1, 'part' => 'Garpu',               'alat' => 'Visual Control',          'metode' => 'Dicek',                'standar' => 'Lancar, tidak retak, gompal, patah, bengkok',        'durasi' => '1 menit', 'db' => 'garpu'),
                array('no' => 2, 'part' => 'Handle',              'alat' => 'Handle, Visual Control',  'metode' => 'Dicek',                'standar' => 'Berfungsi baik, tidak ada kendala, tidak longgar', 'durasi' => '1 menit', 'db' => 'handle'),
                array('no' => 3, 'part' => 'Roda',                'alat' => 'Visual Control',          'metode' => 'Dicek',                'standar' => 'Tidak retak/longgar, berjalan baik',               'durasi' => '1 menit', 'db' => 'roda'),
                array('no' => 4, 'part' => 'Hydraulic',           'alat' => 'Visual Control',          'metode' => 'Dicek',                'standar' => 'Berfungsi baik, tidak bocor, tidak ada hambatan',  'durasi' => '1 menit', 'db' => 'hydraulic'),
                array('no' => 5, 'part' => 'Body',                'alat' => 'Visual Control',          'metode' => 'Dicek',                'standar' => 'Bersih, tidak retak/gompal',                       'durasi' => '1 menit', 'db' => 'body'),
                array('no' => 6, 'part' => 'Tuas',                'alat' => 'Tuas, Visual Control',    'metode' => 'Dicek, gerakkan tuas', 'standar' => 'Berfungsi sesuai arah, tidak retak/longgar',       'durasi' => '1 menit', 'db' => 'tuas'),
                array('no' => 7, 'part' => 'Air Aki',             'alat' => 'Indikator air aki',       'metode' => 'Dicek, Visual Control', 'standar' => 'Indikator level full, air aki tidak bocor',       'durasi' => '1 menit', 'db' => 'air_aki'),
                array('no' => 8, 'part' => 'Charger Palletmover', 'alat' => 'Indikator charger',       'metode' => 'Dicek, Visual Control', 'standar' => 'Indikator menyala saat terhubung, mengisi saat power ditekan, tombol stop berfungsi', 'durasi' => '1 menit', 'db' => 'charger_palletmover'),
            ),
        ),
    ),
);
