<?php
/**
 * ============================================================================
 *  PROFIL MESIN - FORKLIFT ELECTRIC (5 unit)          dibuat 23 Sep 2026
 *
 *  Dipakai tombol Export di 5 tab unit halaman Forklift:
 *  185E00370, 131AD0216, R2B-06835, 131AC8606, 131AE3297.
 *  Tiap tab mengunci unitnya sendiri (ForkliftController::forklift_report_export),
 *  jadi 1 tab = 1 report = 1 unit. Tidak ada dropdown unit di tab forklift.
 *
 *  SUMBER ISI:
 *   - Nama Part, Alat, Metode, Standard, Durasi -> tabel Metode/Alat/Standard/
 *     Durasi di halaman input forklift/add.php.                         [FAKTA]
 *     23 Sep 2026 (revisi): Alat/Metode/Standard DIRINGKAS tanpa mengubah
 *     maksud, supaya kotak paraf harian bisa selebar mesin lain.
 *   - Steering & Spion -> tidak punya tabel Metode/Alat/Standard di form.
 *     Diisi [TEBAKAN] mengikuti gaya item lain + praktik cek harian forklift
 *     umumnya. Perlu dikonfirmasi SPV.
 *   - Semua item ada di section INSPECTION, persis seperti halaman input
 *     (form-nya cuma punya "STANDAR PEMERIKSAAN (INSPECTION)"). Section
 *     CLEANING & LUBRICATING dicetak kosong supaya bentuk form seragam dengan
 *     mesin lain.                                                    [FAKTA]
 *   - Judul section pakai "PENGECEKAN" (standar semua mesin), bukan
 *     "PEMERIKSAAN" seperti di halaman input.
 *   - 'area' & foto: belum ada -> kosong.                            [BELUM ADA]
 *
 *  [GENC-24SEP26-FORKLIFT] + 'short' per section (judul kartu halaman Gen C).
 *  Foto per item belum ada -> kotak "Belum ada foto" (klik = penjelasan).
 *  Isi item & unit TIDAK diubah: dipakai juga oleh halaman Gen C forklift
 *  (form isi, daftar, detail) - satu sumber dengan report.
 *
 *  UNIT: label = nomor seri yang dipakai tab & tersimpan di kolom no_forklift
 *  (screenshot halaman list 23 Sep 2026). Menu::$forklift_no_forklift punya
 *  daftar lain ("Forklift 1".."Forklift 5") - SENGAJA tidak dijadikan alias
 *  karena belum dipastikan "Forklift 1" = 185E00370. Cek dulu:
 *      SELECT DISTINCT no_forklift FROM forklift;
 *  Kalau ternyata ada, tinggal tambahkan ke 'alias' unit yang benar.
 * ============================================================================
 */

return array(
    'key'           => 'forklift_electric',
    'table'         => 'forklift',
    'machine_code'  => 'Forklift Electric',
    'area'          => '',                        // belum ada dari SPV
    'title'         => 'Checklist AM Forklift Electric',
    'file_slug'     => 'Forklift-Electric',
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
        array('label' => '185E00370', 'alias' => array('185E00370')),
        array('label' => '131AD0216', 'alias' => array('131AD0216')),
        array('label' => 'R2B-06835', 'alias' => array('R2B-06835')),
        array('label' => '131AC8606', 'alias' => array('131AC8606')),
        array('label' => '131AE3297', 'alias' => array('131AE3297')),
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
                array('no' => 6, 'part' => 'Tuas Maju Mundur Elektrik', 'alat' => 'Tuas garpu maju/mundur', 'metode' => 'Gerakkan tuas garpu maju/mundur', 'standar' => 'Tuas normal, garpu maju/mundur lancar', 'durasi' => '1 menit', 'db' => 'tuas_maju_mundur_elektrik'),
                array('no' => 7, 'part' => 'Garpu Rantai', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Garpu & rantai lancar, tidak retak/gompal/patah/putus', 'durasi' => '1 menit', 'db' => 'garpu_rantai'),
                array('no' => 8, 'part' => 'Pedal', 'alat' => 'Pedal gas & rem', 'metode' => 'Tekan pedal gas & rem', 'standar' => 'Gas: forklift jalan lancar. Rem: forklift berhenti saat ditekan', 'durasi' => '1 menit', 'db' => 'pedal'),
                array('no' => 9, 'part' => 'Roda', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Tidak retak, berjalan baik', 'durasi' => '1 menit', 'db' => 'roda'),
                array('no' => 10, 'part' => 'Lampu Sign', 'alat' => 'Tombol lampu sein, Visual Control', 'metode' => 'Tekan tombol lampu sein', 'standar' => 'Lampu menyala terang', 'durasi' => '1 menit', 'db' => 'lampu_sign'),
                array('no' => 11, 'part' => 'Air Accu', 'alat' => 'Indikator air accu, Visual Control', 'metode' => 'Dicek', 'standar' => 'Air accu terisi sesuai kebutuhan', 'durasi' => '1 menit', 'db' => 'air_accu'),
                array('no' => 12, 'part' => 'Oli Hidrolik Dan Rem', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Oli terisi, kondisi baik, tidak bocor', 'durasi' => '1 menit', 'db' => 'oli_hidrolik_dan_rem'),
                array('no' => 13, 'part' => 'Apar', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Tidak expired, tersegel', 'durasi' => '1 menit', 'db' => 'apar'),
                array('no' => 14, 'part' => 'Charger Forklift', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Listrik normal, mengisi baterai, layar & indikator menyala', 'durasi' => '1 menit', 'db' => 'charger_forklift'),
                array('no' => 15, 'part' => 'Steering', 'alat' => 'Setir, Visual & Taktil', 'metode' => 'Putar setir penuh kiri-kanan', 'standar' => 'Ringan, tidak oblak/macet, belok sesuai arah', 'durasi' => '1 menit', 'db' => 'steering'),  // [TEBAKAN] form tidak punya tabel Metode/Alat/Standard
                array('no' => 16, 'part' => 'Spion', 'alat' => 'Visual Control', 'metode' => 'Dicek', 'standar' => 'Utuh, bersih, kencang, pandangan belakang jelas', 'durasi' => '1 menit', 'db' => 'spion'),  // [TEBAKAN] form tidak punya tabel Metode/Alat/Standard
            ),
        ),
    ),
);
