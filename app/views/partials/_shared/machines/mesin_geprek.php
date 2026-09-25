<?php
/**
 * ============================================================================
 *  PROFIL MESIN - MESIN GEPREK (PRESS)          dibuat 23 Sep 2026
 *
 *  Menggantikan body khusus app/views/partials/mesin_geprek/report.php supaya
 *  report geprek memakai tata letak standar yang sama dengan 3 mesin dumping
 *  & 2 AGV (kop, "Diperiksa Oleh", Bulan/Tahun, kolom Alat/Metode/Durasi/
 *  Pelaksanaan, Paraf Pelaksana & Paraf Spv harian).
 *
 *  SUMBER ISI - tidak ada yang ditebak:
 *   - 'part' & 'standar'  -> disalin APA ADANYA dari report geprek lama
 *                            (mesin_geprek/report.php, template excel
 *                            "Checklist AM Std TDO"). Hal yang diperiksa
 *                            TIDAK diubah.                                [FAKTA]
 *   - 'alat' & 'durasi'   -> dari tabel Metode/Alat/Standard/Durasi di
 *                            halaman input mesin_geprek/add.php.          [FAKTA]
 *                            Durasi 2' ditulis "2 menit" (tanda +- dari body).
 *   - 'metode'            -> RINGKASAN dari teks Metode halaman input
 *                            (23 Sep 2026, atas permintaan). Teks aslinya
 *                            paragraf s/d +-150 huruf; diringkas tanpa
 *                            mengubah maksud supaya report tetap 1 halaman.
 *   - no.7 Rantai utama   -> Alat / Metode / Durasi = [TEBAKAN]. Di halaman
 *                            input isinya salinan item Alarm Mundur forklift
 *                            (salah salin), jadi tidak ada sumber untuk
 *                            diringkas. Diisi mengikuti standar "Terlumasi"
 *                            + praktik umum pelumasan rantai. Perlu
 *                            dikonfirmasi SPV.
 *   - no.13 Alat "Visuali" di halaman input ditulis "Visual" (salah ketik).
 *   - Foto = geprek_photo_panel.png yang dipotong jadi 3 di garis pemisah
 *     aslinya (cleaning 1-5 / lubricating 6-7 / inspection 8-14).
 *
 *  [GENC-24SEP26] + 'short' per section & 'foto' per item (dipakai halaman
 *     Generasi C saja, report TIDAK membaca kunci ini). Foto per item =
 *     potongan geprek_photo_panel.png (nomor foto di panel = nomor item),
 *     diperbesar ke lebar 440 px -> resolusi aslinya kecil (+-120 px),
 *     ganti file assets/images/mesin_geprek/item_NN.jpg kalau ada foto asli.
 *
 *  KOLOM DB: 14 kolom sama persis dengan yang dipakai report lama
 *  (Mesin_geprekController, $fields di index()). Pengisi = `user_created`,
 *  approver = `user_approve` (diisi approvalbtn() dengan USER_NAME, dan oleh
 *  trigger `approval_mesin_geprek` dengan 'PAN').
 * ============================================================================
 */

return array(
    'key'           => 'mesin_geprek',
    'table'         => 'mesin_geprek',
    'machine_code'  => 'GEPREK (PRESS)',        // sama dengan report lama
    'area'          => '4M',                    // report lama: "Lokasi: 4M"
    'title'         => 'Checklist AM Mesin Geprek',
    'file_slug'     => 'Mesin-Geprek',          // nama file sama dengan report lama
    'user_field'    => 'user_created',
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => '',                      // cuma 1 mesin
    'pelaksanaan'   => 'Setiap hari diawal shift 1', // [FAKTA] halaman input

    'images' => array(
        'logo'   => 'assets/images/dumping_logo.png',
        'atas'   => 'assets/images/geprek_photo_atas.png',
        'tengah' => 'assets/images/geprek_photo_tengah.png',
        'bawah'  => 'assets/images/geprek_photo_bawah.png',
    ),
    'img_size' => array(
        'logo'   => array(138, 40),   // dumping_logo.png          520x150
        'atas'   => array(152, 85),   // geprek_photo_atas.png     398x222
        'tengah' => array(120, 113),  // geprek_photo_tengah.png   398x375 (dikecilkan: section cuma 2 item)
        'bawah'  => array(152, 173),  // geprek_photo_bawah.png    398x453
    ),

    // Metode (ringkasan) butuh kolom lebih lebar. Total kolom teks TETAP 45.5%
    // (sama dengan default), jadi lebar kolom tanggal & kotak paraf harian
    // PERSIS sama dengan mesin dumping/AGV. Huruf sel item 7px (default 8px).
    // Urutan: No, Nama Part, Alat, Metode, Standard, Durasi, Pelaksanaan.
    'layout' => array(
        'widths'       => array(1.9, 8.6, 7.0, 10.0, 7.6, 4.4, 6.0),
        'item_font_px' => 7,
    ),

    'sections' => array(
        array(
            'key'            => 'cleaning',
            'title'          => 'STANDAR PEMBERSIHAN (CLEANING)',
            'short'          => 'Pembersihan',                 // [GENC-24SEP26] judul kartu form Gen C (report tetap pakai 'title')
            'standard_label' => 'Pembersihan standar',
            'photo'          => 'atas',
            'photo_label'    => 'Gambar Bagian Mesin',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 1, 'part' => 'Tatakan jumbo bag',             'alat' => 'Lap tanpa serat',                        'metode' => 'Dilap basah hingga bersih', 'standar' => 'Tidak ada sisa matarial atau kotoran', 'durasi' => '2 menit', 'db' => 'tatakan_jumbo_bag', 'foto' => 'assets/images/mesin_geprek/item_01.jpg'),
                array('no' => 2, 'part' => 'Pembersihan punch',             'alat' => 'Quiltec (lap bebas serat) + alkohol 70%', 'metode' => 'Dilap, beri 4WD bila perlu; cek tekanan punch normal', 'standar' => 'Tidak ada sisa gemuk di piston',       'durasi' => '2 menit', 'db' => 'punch', 'foto' => 'assets/images/mesin_geprek/item_02.jpg'),
                array('no' => 3, 'part' => 'Pembersihan body mesin geprek', 'alat' => 'Quiltec (lap bebas serat) + alkohol 70%', 'metode' => 'Seluruh body dilap hingga bersih', 'standar' => 'Bersih',                               'durasi' => '3 menit', 'db' => 'body_mesin_geprek', 'foto' => 'assets/images/mesin_geprek/item_03.jpg'),
                array('no' => 4, 'part' => 'Panel HMI',                     'alat' => 'Quiltec (lap bebas serat) + alkohol 70%', 'metode' => 'Dilap hingga bersih', 'standar' => 'Bersih',                               'durasi' => '2 menit', 'db' => 'panel_hmi', 'foto' => 'assets/images/mesin_geprek/item_04.jpg'),
                array('no' => 5, 'part' => 'Sensor-sensor',                 'alat' => 'Quiltec (lap bebas serat) + alkohol 70%', 'metode' => 'Sensor dilap', 'standar' => 'Bersih',                               'durasi' => '2 menit', 'db' => 'sensor', 'foto' => 'assets/images/mesin_geprek/item_05.jpg'),
            ),
        ),
        array(
            'key'            => 'lubricating',
            'title'          => 'STANDAR PELUMASAN (LUBRICATING)',
            'short'          => 'Pelumasan',                 // [GENC-24SEP26] judul kartu form Gen C (report tetap pakai 'title')
            'standard_label' => 'Pelumasan standar',
            'photo'          => 'tengah',
            'photo_label'    => '',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 6, 'part' => 'Punch (AS)',   'alat' => 'Visual & Auditori', 'metode' => 'Cek bushing tidak kering/goyang, stoper sensor tidak berubah & tidak bunyi', 'standar' => 'Terlumasi', 'durasi' => '2 menit', 'db' => 'as_punch', 'foto' => 'assets/images/mesin_geprek/item_06.jpg'),
                array('no' => 7, 'part' => 'Rantai utama', 'alat' => 'Visual, grease/oli rantai', 'metode' => 'Cek rantai, lumasi bila kering', 'standar' => 'Terlumasi', 'durasi' => '2 menit',        'db' => 'rantai_utama', 'foto' => 'assets/images/mesin_geprek/item_07.jpg'),
            ),
        ),
        array(
            'key'            => 'inspection',
            'title'          => 'STANDAR PENGECEKAN (INSPECTION)',
            'short'          => 'Pengecekan',                 // [GENC-24SEP26] judul kartu form Gen C (report tetap pakai 'title')
            'standard_label' => 'Pengecekan standar',
            'photo'          => 'bawah',
            'photo_label'    => '',
            'blank_rows'     => 0,
            'items' => array(
                array('no' => 8,  'part' => 'Roda tatakan jumbo bag',      'alat' => 'Visual & Auditori', 'metode' => 'Jalankan mesin, cek roda tidak macet/karat/bunyi kasar', 'standar' => 'berfungsi normal',               'durasi' => '2 menit', 'db' => 'roda_tatakan_jumbobag', 'foto' => 'assets/images/mesin_geprek/item_08.jpg'),
                array('no' => 9,  'part' => 'tombol panel & kabel',        'alat' => 'Visual',            'metode' => 'Cek kabel tidak putus; nyalakan panel, tes tombol', 'standar' => 'berfungsi normal & tidak putus', 'durasi' => '1 menit', 'db' => 'tombol_panel_kabel', 'foto' => 'assets/images/mesin_geprek/item_09.jpg'),
                array('no' => 10, 'part' => 'Mesin dan compresor',         'alat' => 'Visual & Auditori', 'metode' => 'Nyalakan mesin, cek jarum tekanan hidrolik bergerak normal', 'standar' => 'berfungsi normal',               'durasi' => '2 menit', 'db' => 'mesin_compressor', 'foto' => 'assets/images/mesin_geprek/item_10.jpg'),
                array('no' => 11, 'part' => 'Baut body mesin geprek',      'alat' => 'Visual & Auditori', 'metode' => 'Goyangkan baut: tidak kendor/patah, mur terpasang', 'standar' => 'Tidak kendor',                   'durasi' => '2 menit', 'db' => 'baut_body_mesin_geprek', 'foto' => 'assets/images/mesin_geprek/item_11.jpg'),
                array('no' => 12, 'part' => 'Putaran tatakan jumbo bag',   'alat' => 'Visual & Auditori', 'metode' => 'Nyalakan mesin, cek putar 180 derajat, tidak macet/bunyi abnormal', 'standar' => 'berfungsi normal',               'durasi' => '2 menit', 'db' => 'putaran_tatakan_jumbobag', 'foto' => 'assets/images/mesin_geprek/item_12.jpg'),
                array('no' => 13, 'part' => 'selang hydrolic & oli motor', 'alat' => 'Visual',            'metode' => 'Lihat seluruh selang, pastikan tidak bocor', 'standar' => 'Tidak bocor',                    'durasi' => '1 menit', 'db' => 'selang_hidroplik_olimotor', 'foto' => 'assets/images/mesin_geprek/item_13.jpg'),
                array('no' => 14, 'part' => 'Baut punch',                  'alat' => 'Taktil & Visual',   'metode' => 'Goyangkan baut: tidak patah/goyang, mur terpasang', 'standar' => 'Tidak kendor',                   'durasi' => '2 menit', 'db' => 'baut_punch', 'foto' => 'assets/images/mesin_geprek/item_14.jpg'),
            ),
        ),
    ),
);
