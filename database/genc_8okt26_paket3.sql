-- ============================================================================
--  SESI 8 OKT 2026 MALAM - Paket 3 (catatan SPV poin 1-10)   [GENC-08OKT26-P3]
--  Jalankan SEKALI di phpMyAdmin (database web_wh), SETELAH genc_6okt26.sql & genc_8okt26.sql (nama lama: genc_08okt26.sql).
--  Aman dijalankan 2x (IF NOT EXISTS). Tidak mengubah / menghapus data checklist lama.
--  (Kalau lupa dijalankan, semua tabel & kolom ini dibuat otomatis saat menu Mesin & Unit dibuka.)
--
--  1. genc_versi   : riwayat versi pengaturan & isi tiap mesin (tombol "Kembalikan").
--  2. genc_pilihan : label tombol hasil cek (Baik / Tidak baik / Perawatan) dari Master Select.
--                    Kode di database & report TETAP OK / NOK / PR. Tabel kosong = label awal.
--  3. Kolom baru   : genc_folder.nama/area/pelaksanaan (ganti nama & area mesin awal),
--                    genc_folder.arsip, genc_mesin.arsip, genc_unit.arsip (Hapus = masuk Arsip,
--                    bisa dipulihkan; data & tabel checklist tidak pernah dihapus).
--
--  BATALKAN (kembali seperti sebelum Paket 3; pakai bersama file aplikasi lama):
--     DROP TABLE IF EXISTS `genc_versi`;
--     DROP TABLE IF EXISTS `genc_pilihan`;
--     -- kolom tambahan boleh dibiarkan (diabaikan kode lama). Kalau mau dibuang juga:
--     -- ALTER TABLE `genc_folder` DROP COLUMN `nama`, DROP COLUMN `area`, DROP COLUMN `pelaksanaan`, DROP COLUMN `arsip`;
--     -- ALTER TABLE `genc_mesin` DROP COLUMN `arsip`;
--     -- ALTER TABLE `genc_unit`  DROP COLUMN `arsip`;
--     -- Catatan: mesin/unit yang sedang di Arsip akan tampil lagi sebagai NONAKTIF di kode lama.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `genc_versi` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `folder` VARCHAR(64) NOT NULL,
    `versi` INT NOT NULL DEFAULT 1,
    `ringkasan` VARCHAR(255) NOT NULL DEFAULT '',
    `data` MEDIUMTEXT NULL,
    `created_at` DATETIME NULL, `created_by` VARCHAR(100) NULL,
    PRIMARY KEY (`id`), KEY `ix_genc_versi_folder` (`folder`, `versi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `genc_pilihan` (
    `kode` VARCHAR(10) NOT NULL,
    `label` VARCHAR(40) NOT NULL DEFAULT '',
    `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
    PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `genc_folder` (
    `folder` VARCHAR(64) NOT NULL,
    `ikon` VARCHAR(40) NULL,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
    PRIMARY KEY (`folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `genc_folder` ADD COLUMN IF NOT EXISTS `nama` VARCHAR(100) NULL;
ALTER TABLE `genc_folder` ADD COLUMN IF NOT EXISTS `area` VARCHAR(100) NULL;
ALTER TABLE `genc_folder` ADD COLUMN IF NOT EXISTS `pelaksanaan` VARCHAR(150) NULL;
ALTER TABLE `genc_folder` ADD COLUMN IF NOT EXISTS `arsip` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `genc_mesin`  ADD COLUMN IF NOT EXISTS `arsip` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `genc_unit`   ADD COLUMN IF NOT EXISTS `arsip` TINYINT(1) NOT NULL DEFAULT 0;
