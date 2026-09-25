<?php
/**
 * ============================================================================
 *  PROFIL MESIN - MESIN DUMPING (KIR3P01DP001 / KIR6P01DP001 / KIR7P01DP001)
 *
 *  Satu profil dipakai bertiga; controller masing-masing menimpa 'table',
 *  'machine_code', 'title' dan 'file_slug' lewat parameter $overrides.
 *
 *  Isi item disalin apa adanya dari dumping_report.php versi 21 Sep 2026
 *  (yang sudah disetujui & dites di kantor). Struktur tabel, kop, header dan
 *  sub header sekarang ada di checklist_report_body.php - dipakai bareng
 *  semua mesin.
 *
 *  CATATAN KOLOM DB:
 *   - Mesin dumping memakai `user_create` (TANPA huruf "d") untuk pengisi.
 *     Ini SATU-SATUNYA kelompok halaman yang begitu; geprek, AGV, forklift dan
 *     palletmover pakai `user_created`. Kalau salah, kolom Paraf Pelaksana
 *     kosong semua TANPA pesan error apa pun.
 *   - no.1 `iris_valve` (cleaning) BEDA dengan no.9 `iris_valve_inspection`.
 *
 *  Section LUBRICATING sengaja TETAP ditampilkan walau kosong (mesin dumping
 *  memang tidak punya item pelumasan) - mengikuti form kertas aslinya.
 *  Header section Inspection = "Pengecekan standar" (form aslinya memang typo).
 *
 *  [GENC-24SEP26-DUMPING] + 'short' per section & 'foto' per item (dipakai
 *  halaman Gen C: form isi, detail). Report TIDAK membaca dua kunci ini.
 *  Foto = potongan bernomor dari dumping_photo_atas.png (1-6) &
 *  dumping_photo_bawah.png (7-15) -> assets/images/dumping/item_NN.jpg.
 * ============================================================================
 */

return array(
    'key'           => 'dumping',
    'table'         => 'kir3p01dp001',        // ditimpa controller
    'machine_code'  => 'KIR3P01DP001',        // ditimpa controller
    'area'          => 'Dumping Lt 4M',
    'title'         => 'Checklist AM Mesin Dumping KIR3P01DP001', // ditimpa controller
    'file_slug'     => 'KIR3P01DP001',        // ditimpa controller
    'user_field'    => 'user_create',         // <- tanpa "d", khusus dumping
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => '',                    // tidak ada nomor unit
    'pelaksanaan'   => 'Setiap pagi diawal shift I',

    'images' => array(
        'logo'  => 'assets/images/dumping_logo.png',
        'atas'  => 'assets/images/dumping_photo_atas.png',
        'bawah' => 'assets/images/dumping_photo_bawah.png',
    ),
    'img_size' => array(
        'logo'  => array(138, 40),   // dumping_logo.png        520x150
        'atas'  => array(152, 135),  // dumping_photo_atas.png  560x497
        'bawah' => array(152, 169),  // dumping_photo_bawah.png 560x624
    ),

    'sections' => array(
        array(
            'key'            => 'cleaning',
            'short'          => 'Pembersihan',                 // [GENC-24SEP26-DUMPING] judul kartu form Gen C (report tetap pakai 'title')
            'title'          => 'STANDAR PEMBERSIHAN (CLEANING)',
            'standard_label' => 'Pembersihan standar',
            'photo'          => 'atas',
            'photo_label'    => 'Gambar Bagian Mesin',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 1, 'part' => 'Iris Valve',                              'alat' => 'Vacum',                'metode' => 'Divacum/disedot', 'standar' => 'Tidak ada sisa material / kemasan', 'durasi' => '5 menit',  'db' => 'iris_valve', 'foto' => 'assets/images/dumping/item_01.jpg'),
                array('no' => 2, 'part' => 'Hopper BBDS ( Big Bag Dumping Station )', 'alat' => 'Quiltec',              'metode' => 'Di lap',          'standar' => 'Tidak ada sisa material / kemasan', 'durasi' => '5 menit',  'db' => 'hopper_bbds', 'foto' => 'assets/images/dumping/item_02.jpg'),
                array('no' => 3, 'part' => 'Body Mesin Dumping',                      'alat' => 'Quiltec',              'metode' => 'Di lap',          'standar' => 'Bersih',                            'durasi' => '10 menit', 'db' => 'body_mesin_dumping', 'foto' => 'assets/images/dumping/item_03.jpg'),
                array('no' => 4, 'part' => 'Panel HMI',                               'alat' => 'Quiltec',              'metode' => 'Di lap',          'standar' => 'Bersih',                            'durasi' => '2 menit',  'db' => 'panel_hmi', 'foto' => 'assets/images/dumping/item_04.jpg'),
                array('no' => 5, 'part' => 'Sensor-sensor',                           'alat' => 'Quiltec',              'metode' => 'Di lap',          'standar' => 'Bersih',                            'durasi' => '2 menit',  'db' => 'sensor', 'foto' => 'assets/images/dumping/item_05.jpg'),
                array('no' => 6, 'part' => 'Jumbo Bag',                               'alat' => 'Quiltec, Alcohol 70%', 'metode' => 'Di lap',          'standar' => 'Bersih',                            'durasi' => '2 menit',  'db' => 'jumbo_bag', 'foto' => 'assets/images/dumping/item_06.jpg'),
            ),
        ),
        array(
            'key'            => 'lubricating',
            'short'          => 'Pelumasan',                 // [GENC-24SEP26-DUMPING] judul kartu form Gen C (report tetap pakai 'title')
            'title'          => 'STANDAR PELUMASAN (LUBRICATING)',
            'standard_label' => 'Pelumasan standar',
            'photo'          => '',
            'photo_label'    => '',
            'blank_rows'     => 2,   // tidak punya item pelumasan -> 2 baris kosong seperti form
            'items'          => array(),
        ),
        array(
            'key'            => 'inspection',
            'short'          => 'Pengecekan',                 // [GENC-24SEP26-DUMPING] judul kartu form Gen C (report tetap pakai 'title')
            'title'          => 'STANDAR PENGECEKAN (INSPECTION)',
            'standard_label' => 'Pengecekan standar',
            'photo'          => 'bawah',
            'photo_label'    => '',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 7,  'part' => 'Baut Massage & Piringannya',            'alat' => 'Kunci Inggris',              'metode' => 'Dikencangkan',            'standar' => 'Tidak kendor',                          'durasi' => '5 menit', 'db' => 'baut_massage_piringannya', 'foto' => 'assets/images/dumping/item_07.jpg'),
                array('no' => 8,  'part' => 'Baut Motor Vibrator',                   'alat' => 'Kunci Pas no.',              'metode' => 'Dikencangkan',            'standar' => 'Tidak kendor',                          'durasi' => '2 menit', 'db' => 'baut_motor_vibrator', 'foto' => 'assets/images/dumping/item_08.jpg'),
                array('no' => 9,  'part' => 'Iris Valve',                            'alat' => 'Visual Control',             'metode' => 'Visual',                  'standar' => 'Tidak Sobek',                           'durasi' => '2 menit', 'db' => 'iris_valve_inspection', 'foto' => 'assets/images/dumping/item_09.jpg'),
                array('no' => 10, 'part' => 'Kabel Grounding',                       'alat' => 'Kunci Pas no.10',            'metode' => 'Dikencangkan',            'standar' => 'Terpasang & tidak kendor',              'durasi' => '1 menit', 'db' => 'kabel_grounding', 'foto' => 'assets/images/dumping/item_10.jpg'),
                array('no' => 11, 'part' => 'Periksa jalur udara',                   'alat' => 'Indikator udara pada valve', 'metode' => 'Dicek',                   'standar' => 'Tidak ada yang terlepas',               'durasi' => '1 menit', 'db' => 'jalur_udara', 'foto' => 'assets/images/dumping/item_11.jpg'),
                array('no' => 12, 'part' => 'Periksa Tuas udara ( Kran )',           'alat' => 'Visual Control',             'metode' => 'Pastikan dlm kondisi On', 'standar' => 'Terbuka Sempurna',                      'durasi' => '1 menit', 'db' => 'tuas_kran', 'foto' => 'assets/images/dumping/item_12.jpg'),
                array('no' => 13, 'part' => 'Periksa Klem Fleksibel Dust Collector', 'alat' => 'Obeng minus',                'metode' => 'Dikencangkan',            'standar' => 'Tidak Kendor & Tidak Sobek ( Bolong )', 'durasi' => '1 menit', 'db' => 'klem_fleksibel_dust_collector', 'foto' => 'assets/images/dumping/item_13.jpg'),
                array('no' => 14, 'part' => 'Periksa Fleksibel & Klem',              'alat' => 'Visual Control',             'metode' => 'Visual',                  'standar' => 'Tidak kendor',                          'durasi' => '1 menit', 'db' => 'fleksibel_klem_pipa', 'foto' => 'assets/images/dumping/item_14.jpg'),
                array('no' => 15, 'part' => 'Periksa Seal Corong Dumping',           'alat' => 'Visual Control',             'metode' => 'Visual',                  'standar' => 'Markah sesuai',                         'durasi' => '1 menit', 'db' => 'seal_corong_dumping', 'foto' => 'assets/images/dumping/item_15.jpg'),
            ),
        ),
    ),
);
