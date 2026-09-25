-- ============================================================================
--  MESIN GEPREK -> Generasi C                          [GENC-24SEP26] 24 Sep 2026
--  Jalankan di phpMyAdmin, DB `web_wh`, tab SQL. Aman dijalankan 2x.
--
--  Isi:
--   1. Tambah 3 kolom opsional Gen C (kalau belum ada):
--        date_approve  - kapan di-approve (jejak audit)
--        date_update   - kapan terakhir diubah
--        user_update   - siapa yang mengubah
--      Semua NULL -> data lama & halaman lain tidak terpengaruh.
--   2. Index date_created (daftar & ringkasan per bulan).
--   3. Hak akses aksi BARU `mesin_geprek/approve`, DISALIN dari role yang
--      sekarang punya `mesin_geprek/approvalbtn`. Tidak ada role yang ditebak.
--   Trigger `approval_mesin_geprek` TIDAK disentuh.
--
--  Catatan: "IF NOT EXISTS" di ALTER / CREATE INDEX didukung MariaDB (XAMPP
--  memakai MariaDB). Kalau muncul error sintaks di baris itu, berarti server
--  memakai MySQL -> jalankan versi manual di bagian paling bawah file ini.
--
--  Halaman tetap jalan walaupun bagian 1 belum dijalankan (controller cek
--  kolomnya dulu), tapi tombol Approve baru muncul setelah bagian 3.
-- ============================================================================

-- 1. Kolom opsional Gen C
ALTER TABLE `mesin_geprek`
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME     NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_update`  DATETIME     NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `user_update`  VARCHAR(100) NULL DEFAULT NULL;

-- 2. Index query per bulan
CREATE INDEX IF NOT EXISTS `idx_mesin_geprek_date_created` ON `mesin_geprek` (`date_created`);

-- 3. Hak akses approve (disalin dari approvalbtn halaman ini sendiri)
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, 'mesin_geprek', 'approve'
FROM `role_permissions` rp
WHERE LOWER(rp.page_name) = 'mesin_geprek'
  AND LOWER(rp.action_name) = 'approvalbtn'
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = 'mesin_geprek' AND x.action_name = 'approve');

-- ---------------------------------------------------------------------------
--  CEK HASIL (jalankan terpisah kalau mau lihat):
--    SHOW COLUMNS FROM `mesin_geprek` LIKE 'date_%';      -- harus ada date_approve, date_update
--    SHOW COLUMNS FROM `mesin_geprek` LIKE 'user_update';
--    SELECT role_id, action_name FROM role_permissions
--     WHERE page_name='mesin_geprek' AND action_name IN ('approvalbtn','approve') ORDER BY role_id, action_name;
--      -> tiap role yang punya approvalbtn sekarang juga punya approve
-- ---------------------------------------------------------------------------

-- ---------------------------------------------------------------------------
--  VERSI MANUAL (HANYA kalau bagian 1/2 error karena MySQL). Cek dulu:
--    SHOW COLUMNS FROM `mesin_geprek`;
--  lalu jalankan baris untuk kolom yang BELUM ada saja:
--    ALTER TABLE `mesin_geprek` ADD COLUMN `date_approve` DATETIME NULL DEFAULT NULL;
--    ALTER TABLE `mesin_geprek` ADD COLUMN `date_update`  DATETIME NULL DEFAULT NULL;
--    ALTER TABLE `mesin_geprek` ADD COLUMN `user_update`  VARCHAR(100) NULL DEFAULT NULL;
--    ALTER TABLE `mesin_geprek` ADD INDEX `idx_mesin_geprek_date_created` (`date_created`);
-- ---------------------------------------------------------------------------
