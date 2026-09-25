<?php
/**
 * ============================================================================
 *  PROFIL MESIN - AGV TABLE TOP LIFT   (tabel: agv_table_top_lift)
 *
 *  Dibuat 22 Sep 2026, direvisi hari yang sama (pembagian section + kolom
 *  statis diisi). Tata letak report mengikuti Mesin Dumping apa adanya.
 *
 *  =====================  MANA FAKTA, MANA TEBAKAN  ==========================
 *
 *  [FAKTA] - diambil langsung dari kode, bukan karangan:
 *    - urutan & label "Nama Part"      -> halaman input "Add New Agv Table Top Lift"
 *    - nama kolom DB tiap item ('db')  -> $fields di Agv_table_top_liftController.php
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
 *  CLEANING  = part yang tiap pagi DILAP/DIBERSIHKAN, standarnya "bersih".
 *              Body & Alas karena permukaan kerja. Obstruction Sensor & Laser
 *              Scanner ikut ke sini karena pada AGV lensa kotor = sensor salah
 *              baca; membersihkan lensa itu justru pekerjaan harian utamanya.
 *              Preseden langsung dari form dumping: "Sensor-sensor" dan
 *              "Panel HMI" di sana juga masuk CLEANING (Di lap / Bersih).
 *
 *  LUBRICATING = KOSONG. AGV pakai gearbox & bearing tertutup, pelumasan bukan
 *              kerjaan harian operator. Sama seperti mesin dumping, section-nya
 *              tetap dicetak (2 baris kosong) supaya bentuk formnya seragam.
 *
 *  INSPECTION = part yang DIUJI FUNGSINYA atau dilihat kondisi fisiknya, bukan
 *              dibersihkan: tombol-tombol, switch, bumper, roda.
 *
 *  Mau mindahin item antar section? Potong satu baris array-nya, tempel ke
 *  'items' section tujuan, rapikan nomor 'no'-nya. Tidak ada file lain yang
 *  perlu disentuh - nomor & baris tabel ikut otomatis.
 *
 *  CATATAN EJAAN: "Bumpper" ditulis persis seperti label di halaman input
 *  (aslinya memang begitu) supaya operator gampang mencocokkan baris report
 *  dengan baris di form. Kalau SPV mau diperbaiki jadi "Bumper", ganti di
 *  'part'-nya saja - kolom DB `bumpper` JANGAN ikut diubah.
 * ============================================================================
 */

return array(
    'key'           => 'agv_table_top_lift',
    'table'         => 'agv_table_top_lift',
    'machine_code'  => 'AGV Table Top Lift',
    'area'          => 'Area Bridge',          // [FAKTA] konfirmasi SPV 22 Sep 2026
    'title'         => 'Checklist AM AGV Table Top Lift',
    'file_slug'     => 'AGV-Table-Top-Lift',
    'user_field'    => 'user_created',        // AWAS: pakai "d" (beda dengan dumping)
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => 'no_agv',
    'pelaksanaan'   => 'Setiap pagi diawal shift I',

    // ---------------------------------------------------------------------
    //  DAFTAR UNIT FISIK  [FAKTA - konfirmasi SPV 22 Sep 2026]
    //  AGV Table Top Lift berada di area bridge, ada 5 unit: no. 6 sampai 10.
    //
    //  'label' = yang muncul di dropdown filter, di sel Mesin/Line, dan di
    //            nama file hasil Export.
    //  'alias' = SEMUA penulisan yang dipakai di kolom `no_agv` untuk unit
    //            fisik yang SAMA.
    //
    //  KENAPA BUTUH ALIAS: isi kolom no_agv di DB belum seragam. Per 22 Sep
    //  2026 halaman ini berisi campuran 'AGV 6' / 'AGV 7' DAN 'AGV TTL 7' /
    //  'AGV TTL 9' / 'AGV TTL 10' - dan menurut SPV itu memang mesin yang
    //  sama ("AGV 7 / AGV TTL 7"). Kalau filter cuma cocokin teks mentah,
    //  memilih "AGV TTL 7" akan melewatkan record yang tersimpan 'AGV 7':
    //  report jadi bolong TANPA pesan error apa pun.
    //
    //  Perbandingan alias mengabaikan spasi & huruf besar-kecil
    //  (checklist_norm_unit), jadi 'AGV TTL 7' sudah otomatis mencakup
    //  'agvttl7', 'AGV  TTL7', 'agv-ttl-7'. Tidak perlu ditulis satu-satu.
    //
    //  Kalau nanti data no_agv sudah dirapikan jadi satu gaya, alias yang
    //  tidak terpakai boleh dihapus - tidak ada kode lain yang bergantung.
    // ---------------------------------------------------------------------
    'units' => array(
        array('label' => 'AGV TTL 6',  'alias' => array('AGV 6',  'AGV TTL 6')),
        array('label' => 'AGV TTL 7',  'alias' => array('AGV 7',  'AGV TTL 7')),
        array('label' => 'AGV TTL 8',  'alias' => array('AGV 8',  'AGV TTL 8')),
        array('label' => 'AGV TTL 9',  'alias' => array('AGV 9',  'AGV TTL 9')),
        array('label' => 'AGV TTL 10', 'alias' => array('AGV 10', 'AGV TTL 10')),
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
                array('no' => 2, 'part' => 'Alas',               'alat' => 'Quiltec',              'metode' => 'Di lap', 'standar' => 'Bersih, tidak ada sisa material / kemasan',     'durasi' => '3 menit', 'db' => 'alas'),
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
            'standard_label' => 'Pengecekan standar', // ikut form dumping (aslinya memang begitu)
            'photo'          => 'bawah',
            'photo_label'    => '',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 5, 'part' => 'Bumpper',                 'alat' => 'Visual Control',            'metode' => 'Ditekan & dicek',      'standar' => 'Terpasang kencang, tidak retak, kembali ke posisi semula',   'durasi' => '1 menit', 'db' => 'bumpper'),
                array('no' => 6, 'part' => 'Switch Kunci',            'alat' => 'Switch kunci, Visual Control', 'metode' => 'Diputar On / Off',  'standar' => 'Berfungsi, tidak longgar, kunci tidak macet',                'durasi' => '1 menit', 'db' => 'switch_kunci'),
                array('no' => 7, 'part' => 'Emergency Button',        'alat' => 'Tombol Emergency',          'metode' => 'Ditekan',              'standar' => 'AGV berhenti seketika, tombol kembali normal saat diputar',  'durasi' => '1 menit', 'db' => 'emergency_button'),
                array('no' => 8, 'part' => 'Start Stop Reset Button', 'alat' => 'Tombol Start / Stop / Reset', 'metode' => 'Ditekan satu per satu', 'standar' => 'Ketiga tombol berfungsi sesuai fungsinya',                'durasi' => '1 menit', 'db' => 'start_stop_reset_button'),
                array('no' => 9, 'part' => 'Roda',                    'alat' => 'Visual Control',            'metode' => 'Dicek',                'standar' => 'Tidak retak, tidak aus, tidak ada material terlilit',        'durasi' => '2 menit', 'db' => 'roda'),
            ),
        ),
    ),
);
