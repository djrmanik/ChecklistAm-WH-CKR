-- ============================================================================
--  SESI 8 OKT 2026 - Mesin aktif/nonaktif + ganti nama jabatan   [GENC-08OKT26]
--  Jalankan SEKALI di phpMyAdmin (database web_wh), SETELAH genc_6okt26.sql.
--  Aman dijalankan 2x. Tidak mengubah / menghapus data checklist lama.
--
--  1. Tabel kecil genc_folder: status aktif/nonaktif + ikon untuk 6 mesin awal
--     (Pallet Mover & Stacker, Forklift, AGV, Mesin Dumping, Mesin Geprek, Conveyor).
--     Tabel kosong = semua mesin aktif dengan ikon awal (tampilan sama dengan sebelumnya).
--     (Kalau lupa dijalankan, tabel ini dibuat otomatis saat menu Mesin & Unit dibuka.)
--  2. Nama jabatan "Manager & Supervisor" -> "Manager / Supervisor".
--     Hanya nama yang berubah; role_id & hak aksesnya tetap.
--
--  BATALKAN (kembali seperti sebelum sesi ini):
--     UPDATE `roles` SET `role_name` = 'Manager & Supervisor' WHERE `role_name` = 'Manager / Supervisor';
--     DROP TABLE IF EXISTS `genc_folder`;   -- semua mesin kembali aktif dengan ikon awal
-- ============================================================================

CREATE TABLE IF NOT EXISTS `genc_folder` (
    `folder` VARCHAR(64) NOT NULL,
    `ikon` VARCHAR(40) NULL,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
    PRIMARY KEY (`folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE `roles` SET `role_name` = 'Manager / Supervisor' WHERE `role_name` = 'Manager & Supervisor';
