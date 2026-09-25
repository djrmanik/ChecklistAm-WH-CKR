<?php
/**
 * ============================================================================
 *  PROFIL MESIN - CONVEYOR                              dibuat 23 Sep 2026
 *
 *  SATU SUMBER KEBENARAN untuk isi checklist conveyor. Dipakai oleh:
 *   - report layout (checklist_report_engine.php)          -> sections/items
 *   - ConveyorController (validasi, kolom DB, kondisi)      -> items['db']
 *   - halaman Generasi C (form isi, daftar, detail)         -> items + 'foto'
 *  Mau tambah / ubah item? Cukup di sini + kolom DB-nya. Form, daftar, detail
 *  dan report ikut berubah sendiri.
 *
 *  SUMBER ISI:
 *   - Nama Part, Standard, urutan & pembagian section -> template report
 *     conveyor dari SPV (PNG, 23 Sep 2026).                             [FAKTA]
 *     Template menulis "Pembersihan standar" di header ketiga section (salin
 *     tempel); di sini tiap section pakai label masing-masing, sama dengan
 *     mesin lain.
 *   - Area "Dumping Lt 4M" -> kop template.                             [FAKTA]
 *   - Mesin/Line: template menulis "KIR7P01DP001" (kode mesin dumping KIR7).
 *     Kemungkinan besar sisa salin dari template dumping, jadi di sini ditulis
 *     "Conveyor".                                           [PERLU KONFIRMASI]
 *   - Alat / Metode / Durasi / Pelaksanaan -> KOSONG di template.
 *     Diisi [TEBAKAN] dengan kosakata form dumping (satu area, jenis part
 *     mirip). Perlu divalidasi SPV.
 *   - Foto: dipotong dari kolom "Gambar Bagian Mesin" template. Foto section
 *     mengikuti posisi di template (foto no.4 & 7 memang ada di baris
 *     Lubricating di template). Foto per item ('foto') dipakai form isi.
 *
 *  KOLOM DB: lihat database/conveyor_23sept26.sql. `kaki_conveyor` (no.3,
 *  cleaning) BEDA dengan `kaki_conveyor_inspection` (no.10) - pola sama
 *  dengan iris_valve / iris_valve_inspection di dumping.
 * ============================================================================
 */

return array(
    'key'           => 'conveyor',
    'table'         => 'conveyor',
    'machine_code'  => 'Conveyor',                 // template: KIR7P01DP001 [PERLU KONFIRMASI]
    'area'          => 'Dumping Lt 4M',            // [FAKTA] template
    'title'         => 'Checklist AM Conveyor',
    'file_slug'     => 'Conveyor',
    'user_field'    => 'user_created',
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => '',                         // 1 conveyor
    'pelaksanaan'   => 'Setiap pagi diawal shift I', // [TEBAKAN] ikut form dumping

    'images' => array(
        'logo'   => 'assets/images/dumping_logo.png',
        'atas'   => 'assets/images/conveyor_photo_atas.png',
        'tengah' => 'assets/images/conveyor_photo_tengah.png',
        'bawah'  => 'assets/images/conveyor_photo_bawah.png',
    ),
    'img_size' => array(
        'logo'   => array(138, 40),
        'atas'   => array(152, 140),   // 560x515
        'tengah' => array(152, 65),    // 560x240
        'bawah'  => array(152, 170),   // 560x628
    ),

    'sections' => array(
        array(
            'key'            => 'cleaning',
            'title'          => 'STANDAR PEMBERSIHAN (CLEANING)',
            'short'          => 'Pembersihan',
            'standard_label' => 'Pembersihan standar',
            'photo'          => 'atas',
            'photo_label'    => 'Gambar Bagian Mesin',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 1, 'part' => 'Alas conveyor', 'alat' => 'Quiltec', 'metode' => 'Di lap', 'standar' => 'Tidak ada sisa material / kemasan', 'durasi' => '2 menit', 'db' => 'alas_conveyor', 'foto' => 'assets/images/conveyor/item_01.jpg'),
                array('no' => 2, 'part' => 'Body conveyor', 'alat' => 'Quiltec', 'metode' => 'Di lap', 'standar' => 'Tidak ada sisa material / kemasan', 'durasi' => '2 menit', 'db' => 'body_conveyor', 'foto' => 'assets/images/conveyor/item_02.jpg'),
                array('no' => 3, 'part' => 'Kaki conveyor', 'alat' => 'Quiltec', 'metode' => 'Di lap', 'standar' => 'Bersih',                            'durasi' => '2 menit', 'db' => 'kaki_conveyor', 'foto' => 'assets/images/conveyor/item_03.jpg'),
            ),
        ),
        array(
            'key'            => 'lubricating',
            'title'          => 'STANDAR PELUMASAN (LUBRICATING)',
            'short'          => 'Pelumasan',
            'standard_label' => 'Pelumasan standar',
            'photo'          => 'tengah',
            'photo_label'    => '',
            'blank_rows'     => 2,             // template: 2 baris kosong
            'items'          => array(),
        ),
        array(
            'key'            => 'inspection',
            'title'          => 'STANDAR PENGECEKAN (INSPECTION)',
            'short'          => 'Pengecekan',
            'standard_label' => 'Pengecekan standar',
            'photo'          => 'bawah',
            'photo_label'    => '',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 4,  'part' => 'Tombol switch',          'alat' => 'Visual Control',    'metode' => 'Visual',                     'standar' => 'Posisi switch "On"',    'durasi' => '1 menit', 'db' => 'tombol_switch',            'foto' => 'assets/images/conveyor/item_04.jpg'),
                array('no' => 5,  'part' => 'Sensor',                 'alat' => 'Visual Control',    'metode' => 'Visual',                     'standar' => 'Sensor berfungsi',      'durasi' => '1 menit', 'db' => 'sensor',                   'foto' => 'assets/images/conveyor/item_05.jpg'),
                array('no' => 6,  'part' => 'Tombol emergency stop',  'alat' => 'Tangan',            'metode' => 'Ditekan lalu di-reset',      'standar' => 'Berfungsi dengan baik', 'durasi' => '1 menit', 'db' => 'tombol_emergency_stop',    'foto' => 'assets/images/conveyor/item_06.jpg'),
                array('no' => 7,  'part' => 'Panel',                  'alat' => 'Visual Control',    'metode' => 'Visual',                     'standar' => 'Menyala',               'durasi' => '1 menit', 'db' => 'panel',                    'foto' => 'assets/images/conveyor/item_07.jpg'),
                array('no' => 8,  'part' => 'Motor mesin conveyor',   'alat' => 'Visual & Auditori', 'metode' => 'Jalankan, dengarkan motor',  'standar' => 'Menyala',               'durasi' => '1 menit', 'db' => 'motor_mesin_conveyor',     'foto' => 'assets/images/conveyor/item_08.jpg'),
                array('no' => 9,  'part' => 'Tombol on-off manual',   'alat' => 'Tangan',            'metode' => 'Ditekan on / off',           'standar' => 'Berfungsi dengan baik', 'durasi' => '1 menit', 'db' => 'tombol_on_off_manual',     'foto' => 'assets/images/conveyor/item_09.jpg'),
                array('no' => 10, 'part' => 'Kaki conveyor',          'alat' => 'Visual Control',    'metode' => 'Visual',                     'standar' => 'Tidak bengkok / miring','durasi' => '1 menit', 'db' => 'kaki_conveyor_inspection', 'foto' => 'assets/images/conveyor/item_10.jpg'),
            ),
        ),
    ),
);
