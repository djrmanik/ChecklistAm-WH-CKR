<?php
/**
 * ============================================================================
 *  PROFIL MESIN - FORKLIFT DIESEL (1 unit)             dibuat 23 Sep 2026
 *
 *  Dipakai tombol Export di tab "Forklift Diesel" halaman Forklift.
 *
 *  SUMBER ISI:
 *   - Nama Part, Alat, Metode, Standard, Durasi -> tabel Metode/Alat/Standard/
 *     Durasi di halaman input forklift/add_forklift_diesel.php.          [FAKTA]
 *     23 Sep 2026 (revisi): Alat/Metode/Standard DIRINGKAS tanpa mengubah
 *     maksud, supaya kotak paraf harian bisa selebar mesin lain.
 *     Item Label Uji Emisi yang di form isinya
 *     GESER satu baris (Durasi kosong, Pelaksanaan = 1') -> dibaca Durasi 1 menit.
 *     "uji emosi" di Standard Label Uji Emisi = salah ketik di form, ditulis
 *     "uji emisi".
 *   - Pelaksanaan: 6 item diesel (Solar s/d Sabuk Pengaman) di form KOSONG
 *     -> ikut default profil "Setiap hari diawal shift 1", sama dengan item
 *     diesel lainnya.                                                [TEBAKAN kecil]
 *   - Steering & Spion -> ada di form tapi tanpa tabel Metode/Alat/Standard
 *     -> diisi [TEBAKAN] (gaya item lain + praktik cek harian forklift
 *     umumnya). Perlu dikonfirmasi SPV.
 *   - Semua item di section INSPECTION, persis halaman input.
 *   - 'area' & foto: belum ada -> kosong.
 *
 *  UNIT: satu unit fisik, dua ejaan di kolom no_forklift:
 *    'Forklift Diesel'          -> Menu::$no_forklift (dipakai tab & form electric)
 *    'Forklift Diesel 608FD18'  -> Menu::$no_forklift2 (dipakai form diesel)
 *  Dua-duanya didaftarkan sebagai alias supaya report tidak bolong diam-diam.
 * ============================================================================
 */

return array(
    'key'           => 'forklift_diesel',
    'table'         => 'forklift',
    'machine_code'  => 'Forklift Diesel',
    'area'          => '',                        // belum ada dari SPV
    'title'         => 'Checklist AM Forklift Diesel',
    'file_slug'     => 'Forklift-Diesel',
    'user_field'    => 'user_created',
    'approve_field' => 'user_approve',
    'date_field'    => 'date_created',
    'unit_field'    => 'no_forklift',
    'pelaksanaan'   => 'Setiap hari diawal shift 1', // [FAKTA] halaman input

    'images' => array(
        'logo'  => 'assets/images/dumping_logo.png',
        'atas'  => '',
        'bawah' => '',
    ),
    'img_size' => array(
        'logo'  => array(138, 40),
    ),

    // Total kolom teks = 45.5% (SAMA dengan default) -> lebar kolom tanggal &
    // kotak paraf harian persis sama dengan mesin lain. Pembagian di dalamnya
    // digeser ke Metode/Standard, huruf sel item 7px.
    // Urutan: No, Nama Part, Alat, Metode, Standard, Durasi, Pelaksanaan (% lebar).
    'layout' => array(
        'widths'       => array(1.9, 7.0, 7.6, 8.6, 9.8, 4.2, 6.4),
        'item_font_px' => 7,
    ),

    'units' => array(
        array('label' => '608FD18', 'alias' => array('Forklift Diesel', 'Forklift Diesel 608FD18')),
    ),

    'sections' => array(
        array(
            'key' => 'cleaning', 'title' => 'STANDAR PEMBERSIHAN (CLEANING)',
            'short' => 'Pembersihan',   // [GENC-24SEP26-FORKLIFT] judul kartu Gen C (report tidak membaca)
            'standard_label' => 'Pembersihan standar',
            'photo' => 'atas', 'photo_label' => 'Gambar Bagian Mesin',
            'blank_rows' => 1, 'items' => array(),
        ),
        array(
            'key' => 'lubricating', 'title' => 'STANDAR PELUMASAN (LUBRICATING)',
            'short' => 'Pelumasan',   // [GENC-24SEP26-FORKLIFT] judul kartu Gen C (report tidak membaca)
            'standard_label' => 'Pelumasan standar',
            'photo' => '', 'photo_label' => '',
            'blank_rows' => 1, 'items' => array(),
        ),
        array(
            'key' => 'inspection', 'title' => 'STANDAR PENGECEKAN (INSPECTION)',
            'short' => 'Pengecekan',   // [GENC-24SEP26-FORKLIFT] judul kartu Gen C (report tidak membaca)
            'standard_label' => 'Pengecekan standar',
            'photo' => 'bawah', 'photo_label' => '',
            'blank_rows' => 0,
            'items' => array(
                array('no' => 1, 'part' => 'Alarm Mundur', 'alat' => 'Tuas mundur, Audio Control', 'metode' => 'Gerakkan tuas mundur, dengarkan alarm', 'standar' => 'Bersuara jelas', 'durasi' => '1 menit', 'db' => 'alarm_mundur'),
                array('no' => 2, 'part' => 'Klakson', 'alat' => 'Tombol klakson, Audio Control', 'metode' => 'Tekan tombol klakson', 'standar' => 'Bersuara jelas', 'durasi' => '1 menit', 'db' => 'klakson'),
                array('no' => 3, 'part' => 'Lampu Forklift', 'alat' => 'Tombol lampu, Visual Control', 'metode' => 'Tekan tombol lampu', 'standar' => 'Lampu menyala terang', 'durasi' => '1 menit', 'db' => 'lampu_forklift'),
                array('no' => 4, 'part' => 'Tuas Naik Turun', 'alat' => 'Tuas garpu, Visual Control', 'metode' => 'Gerakkan tuas garpu naik/turun', 'standar' => 'Tuas normal, garpu naik/turun lancar', 'durasi' => '1 menit', 'db' => 'tuas_naik_turun'),
                array('no' => 5, 'part' => 'Tuas Serong Atas Bawah', 'alat' => 'Tuas garpu, Visual Control', 'metode' => 'Gerakkan tuas serong atas/bawah, pastikan sesuai arah', 'standar' => 'Tuas normal, garpu serong lancar', 'durasi' => '1 menit', 'db' => 'tuas_serong_atas_bawah'),
                array('no' => 6, 'part' => 'Garpu Rantai', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Garpu & rantai lancar, tidak retak/gompal/patah/putus', 'durasi' => '1 menit', 'db' => 'garpu_rantai'),
                array('no' => 7, 'part' => 'Pedal', 'alat' => 'Pedal gas & rem', 'metode' => 'Tekan pedal gas & rem', 'standar' => 'Gas: forklift jalan lancar. Rem: forklift berhenti saat ditekan', 'durasi' => '1 menit', 'db' => 'pedal'),
                array('no' => 8, 'part' => 'Roda', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Tidak retak, berjalan baik', 'durasi' => '1 menit', 'db' => 'roda'),
                array('no' => 9, 'part' => 'Lampu Sign', 'alat' => 'Tombol lampu sein, Visual Control', 'metode' => 'Tekan tombol lampu sein', 'standar' => 'Lampu menyala terang', 'durasi' => '1 menit', 'db' => 'lampu_sign'),
                array('no' => 10, 'part' => 'Oli Hidrolik Dan Rem', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Oli terisi, kondisi baik, tidak bocor', 'durasi' => '1 menit', 'db' => 'oli_hidrolik_dan_rem'),
                array('no' => 11, 'part' => 'Apar', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Tidak expired, tersegel', 'durasi' => '1 menit', 'db' => 'apar'),
                array('no' => 12, 'part' => 'Solar', 'alat' => 'Indikator solar, Visual Control', 'metode' => 'Dicek', 'standar' => 'Solar terisi sesuai kebutuhan', 'durasi' => '1 menit', 'db' => 'solar'),
                array('no' => 13, 'part' => 'Air Radiator', 'alat' => 'Indikator air radiator, Visual Control', 'metode' => 'Dicek', 'standar' => 'Air radiator baik, terisi sesuai kebutuhan', 'durasi' => '1 menit', 'db' => 'air_radiator'),
                array('no' => 14, 'part' => 'Filter Gas Buang', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Bersih, tidak tersumbat, tidak retak/berlubang/korosi', 'durasi' => '1 menit', 'db' => 'filter_gas_buang'),
                array('no' => 15, 'part' => 'Label Uji Emisi', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Label baik, tidak expired', 'durasi' => '1 menit', 'db' => 'label_uji_emisi'),
                array('no' => 16, 'part' => 'Tuas Tangan Rem', 'alat' => 'Tuas rem tangan', 'metode' => 'Gerakkan tuas rem tangan', 'standar' => 'Lancar, tidak ada hambatan, mengerem dengan baik', 'durasi' => '1 menit', 'db' => 'tuas_tangan_rem'),
                array('no' => 17, 'part' => 'Sabuk Pengaman', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Tidak putus, berfungsi baik, lancar', 'durasi' => '1 menit', 'db' => 'sabuk_pengaman'),
                array('no' => 18, 'part' => 'Steering', 'alat' => 'Setir, Visual & Taktil', 'metode' => 'Putar setir penuh kiri-kanan', 'standar' => 'Ringan, tidak oblak/macet, belok sesuai arah', 'durasi' => '1 menit', 'db' => 'steering'),  // [TEBAKAN] form tidak punya tabel Metode/Alat/Standard
                array('no' => 19, 'part' => 'Kaca Spion', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Utuh, bersih, kencang, pandangan belakang jelas', 'durasi' => '1 menit', 'db' => 'spion'),  // [TEBAKAN] form tidak punya tabel Metode/Alat/Standard
            ),
        ),
    ),
);
