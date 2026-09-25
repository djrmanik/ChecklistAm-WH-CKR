<?php
/**
 * ============================================================================
 *  PROFIL MESIN - AGV COUNTERBALANCE   (tabel: counterbalance)
 *
 *  Dibuat 22 Sep 2026, direvisi hari yang sama (pembagian section + kolom
 *  statis diisi). Tata letak report mengikuti Mesin Dumping apa adanya.
 *
 *  =====================  MANA FAKTA, MANA TEBAKAN  ==========================
 *
 *  [FAKTA] - diambil langsung dari kode, bukan karangan:
 *    - urutan & label "Nama Part"      -> halaman input "Add New Counterbalance"
 *    - nama kolom DB tiap item ('db')  -> $fields di CounterbalanceController.php
 *                                         INI yang bikin grid tanggal keisi.
 *                                         JANGAN diubah kalau tidak ganti kolom DB.
 *    - kolom pengisi `user_created` (PAKAI "d"), approver `user_approve`
 *    - logo TPM  -> assets/images/dumping_logo.png (file yang sama dipakai
 *                   report mesin dumping; logo perusahaan memang satu)
 *
 *  [TEBAKAN] - diisi pakai logika AM warehouse + kebiasaan form mesin dumping,
 *  BELUM divalidasi SPV. Semua ada di kolom 'alat', 'metode', 'standar',
 *  'durasi', dan pembagian section. Tinggal ketik ulang di baris yang salah.
 *
 *  [BELUM ADA] - yang memang masih nunggu:
 *    - 'area'                       -> sel Area di kop masih kosong
 *    - gambar bagian mesin          -> 'atas' & 'bawah' di bawah masih ''
 *
 *  ---------------------------------------------------------------------------
 *  DASAR PEMBAGIAN SECTION  [TEBAKAN]
 *  ---------------------------------------------------------------------------
 *  CLEANING  = part yang tiap pagi DILAP, standarnya "bersih". Body karena
 *              permukaan luar; Panel HMI mengikuti preseden form dumping
 *              (di sana "Panel HMI" juga CLEANING: Di lap / Bersih);
 *              Obstruction Sensor & Laser Scanner karena pada AGV lensa kotor
 *              = sensor salah baca, dan form dumping juga menaruh
 *              "Sensor-sensor" di CLEANING.
 *
 *  LUBRICATING = KOSONG. AGV pakai gearbox & bearing tertutup, pelumasan bukan
 *              kerjaan harian operator. Section-nya tetap dicetak (2 baris
 *              kosong) supaya bentuk formnya seragam dengan mesin dumping.
 *
 *  INSPECTION = part yang DIUJI FUNGSINYA atau dilihat kondisi fisiknya:
 *              garpu, socket kabel, switch, tombol-tombol, roda.
 *              Garpu sengaja TIDAK masuk cleaning - di form Pallet Mover &
 *              Forklift yang sudah jadi, "Garpu" juga item pengecekan visual
 *              (tidak retak / bengkok), bukan item lap-lapan.
 *
 *  Mau mindahin item antar section? Potong satu baris array-nya, tempel ke
 *  'items' section tujuan, rapikan nomor 'no'-nya.
 *
 *  BEDA DENGAN AGV TABLE TOP LIFT: mesin ini punya Garpu, Panel HMI dan
 *  Socket Kabel; sebaliknya tidak punya Alas dan Bumpper.
 * ============================================================================
 */

return array(
    'key'           => 'counterbalance',
    'table'         => 'counterbalance',
    'machine_code'  => 'AGV Counterbalance',
    'area'          => 'Warehouse 1 - Warehouse 2', // [FAKTA] konfirmasi SPV 22 Sep 2026
    'title'         => 'Checklist AM AGV Counterbalance',
    'file_slug'     => 'AGV-Counterbalance',
    'user_field'    => 'user_created',        // AWAS: pakai "d" (beda dengan dumping)
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => 'no_agv',
    'pelaksanaan'   => 'Setiap pagi diawal shift I',

    // ---------------------------------------------------------------------
    //  DAFTAR UNIT FISIK  [FAKTA - konfirmasi SPV 22 Sep 2026]
    //  AGV Counterbalance berada di Warehouse 1 - Warehouse 2, ada 3 unit.
    //
    //  Penjelasan lengkap soal alias ada di machines/agv_table_top_lift.php.
    //  Ringkasnya: kolom `no_agv` di DB menyimpan unit yang sama dengan dua
    //  gaya penulisan ('AGV 1' dan 'AGV CB 1'), jadi keduanya didaftarkan
    //  supaya filter tidak melewatkan record. Perbandingan mengabaikan spasi
    //  & huruf besar-kecil, jadi 'AGVCB1' sudah otomatis tercakup.
    //
    //  CATATAN DATA (22 Sep 2026): ada 1 record di tabel ini ber-no_agv
    //  'AGV TTL 1'. Itu bukan unit counterbalance (CB cuma 1-3) dan bukan
    //  unit table top lift juga (TTL mulai dari 6) - kemungkinan salah ketik
    //  waktu input. Record itu SENGAJA tidak didaftarkan sebagai alias:
    //  memaksakannya ke salah satu unit malah menyembunyikan salah input.
    //  Dia tetap kelihatan lewat opsi "Semua unit". Kalau sudah dibetulkan
    //  datanya, tidak ada yang perlu diubah di file ini.
    // ---------------------------------------------------------------------
    'units' => array(
        array('label' => 'AGV CB 1', 'alias' => array('AGV 1', 'AGV CB 1')),
        array('label' => 'AGV CB 2', 'alias' => array('AGV 2', 'AGV CB 2')),
        array('label' => 'AGV CB 3', 'alias' => array('AGV 3', 'AGV CB 3')),
    ),

    'images' => array(
        'logo'  => 'assets/images/dumping_logo.png', // [FAKTA] logo TPM, sama untuk semua mesin
        'atas'  => '',   // <- [BELUM ADA] foto bagian mesin untuk section CLEANING
        'bawah' => '',   // <- [BELUM ADA] foto bagian mesin untuk section INSPECTION
    ),
    'img_size' => array(
        'logo'  => array(138, 40),
        'atas'  => array(152, 135),
        'bawah' => array(152, 169),
    ),

    'sections' => array(
        array(
            'key'            => 'cleaning',
            'short'          => 'Pembersihan',   // [GENC-24SEP26-AGV] judul kartu Gen C (report tidak membaca)
            'title'          => 'STANDAR PEMBERSIHAN (CLEANING)',
            'standard_label' => 'Pembersihan standar',
            'photo'          => 'atas',
            'photo_label'    => 'Gambar Bagian Mesin',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 1, 'part' => 'Body',               'alat' => 'Quiltec',              'metode' => 'Di lap', 'standar' => 'Bersih, tidak ada debu & sisa material',        'durasi' => '5 menit', 'db' => 'body'),
                array('no' => 2, 'part' => 'Panel HMI',          'alat' => 'Quiltec',              'metode' => 'Di lap', 'standar' => 'Bersih, layar terbaca jelas',                  'durasi' => '2 menit', 'db' => 'panel_hmi'),
                array('no' => 3, 'part' => 'Obstruction Sensor', 'alat' => 'Quiltec, Alcohol 70%', 'metode' => 'Di lap', 'standar' => 'Lensa bersih, tidak ada debu yang menghalangi', 'durasi' => '2 menit', 'db' => 'obstruction_sensor'),
                array('no' => 4, 'part' => 'Laser Scanner',      'alat' => 'Quiltec, Alcohol 70%', 'metode' => 'Di lap', 'standar' => 'Lensa bersih, tidak ada debu yang menghalangi', 'durasi' => '2 menit', 'db' => 'laser_scanner'),
            ),
        ),
        array(
            'key'            => 'lubricating',
            'short'          => 'Pelumasan',   // [GENC-24SEP26-AGV] judul kartu Gen C (report tidak membaca)
            'title'          => 'STANDAR PELUMASAN (LUBRICATING)',
            'standard_label' => 'Pelumasan standar',
            'photo'          => '',
            'photo_label'    => '',
            'blank_rows'     => 2,   // tidak ada item pelumasan harian - lihat penjelasan di atas
            'items'          => array(),
        ),
        array(
            'key'            => 'inspection',
            'short'          => 'Pengecekan',   // [GENC-24SEP26-AGV] judul kartu Gen C (report tidak membaca)
            'title'          => 'STANDAR PENGECEKAN (INSPECTION)',
            'standard_label' => 'Pengecekan standar',
            'photo'          => 'bawah',
            'photo_label'    => '',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 5,  'part' => 'Garpu',                   'alat' => 'Visual Control',               'metode' => 'Dicek',                 'standar' => 'Tidak bengkok, tidak retak, naik turun lancar',             'durasi' => '2 menit', 'db' => 'garpu'),
                array('no' => 6,  'part' => 'Socket Kabel',            'alat' => 'Visual Control',               'metode' => 'Dicek',                 'standar' => 'Terpasang kencang, isolasi tidak terkelupas',               'durasi' => '1 menit', 'db' => 'socket_kabel'),
                array('no' => 7,  'part' => 'Switch Kunci',            'alat' => 'Switch kunci, Visual Control', 'metode' => 'Diputar On / Off',      'standar' => 'Berfungsi, tidak longgar, kunci tidak macet',               'durasi' => '1 menit', 'db' => 'switch_kunci'),
                array('no' => 8,  'part' => 'Emergency Button',        'alat' => 'Tombol Emergency',             'metode' => 'Ditekan',               'standar' => 'AGV berhenti seketika, tombol kembali normal saat diputar', 'durasi' => '1 menit', 'db' => 'emergency_button'),
                array('no' => 9,  'part' => 'Start Stop Reset Button', 'alat' => 'Tombol Start / Stop / Reset',  'metode' => 'Ditekan satu per satu', 'standar' => 'Ketiga tombol berfungsi sesuai fungsinya',                  'durasi' => '1 menit', 'db' => 'start_stop_reset_button'),
                array('no' => 10, 'part' => 'Roda',                    'alat' => 'Visual Control',               'metode' => 'Dicek',                 'standar' => 'Tidak retak, tidak aus, tidak ada material terlilit',       'durasi' => '2 menit', 'db' => 'roda'),
            ),
        ),
    ),
);
