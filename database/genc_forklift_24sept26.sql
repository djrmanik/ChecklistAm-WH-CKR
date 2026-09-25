-- ============================================================================
--  FORKLIFT (5 Electric + 1 Diesel) -> Generasi C   [GENC-24SEP26-FORKLIFT] 24 Sep 2026
--  Jalankan di phpMyAdmin, DB `web_wh`, tab SQL. Aman dijalankan 2x.
--
--  Isi:
--   1. Tambah kolom (kalau belum ada):
--        date_approve  - kapan di-approve (jejak audit)
--        kondisi       - SUDAH ADA di forklift (Gen A); baris ini cuma jaga-jaga,
--                        dilewati kalau kolomnya ada (IF NOT EXISTS)
--      Kolom baru NULL -> data lama tidak diubah sama sekali (K6).
--   2. Index date_created (daftar & ringkasan per bulan).
--   3. Hak akses aksi BARU `forklift/approve`, DISALIN dari role yang sekarang
--      punya `forklift/approvalbtn` (tombol approve lama). Role itu juga
--      dipastikan punya `forklift/list` + `forklift/view` (K11) supaya menu
--      Forklift & halaman detail terbuka untuk approver. Hak role lain TIDAK diubah.
--   TIDAK ada trigger baru (K13): semua checklist forklift di-approve manual.
--
--  Catatan: "IF NOT EXISTS" di ALTER / CREATE INDEX didukung MariaDB (XAMPP
--  memakai MariaDB). Kalau muncul error sintaks di baris itu, berarti server
--  memakai MySQL -> jalankan versi manual di bagian paling bawah file ini.
--
--  Halaman tetap jalan walaupun bagian 1 belum dijalankan (aplikasi cek
--  kolomnya dulu), tapi tombol Approve baru muncul setelah bagian 3.
-- ============================================================================

-- 1. Kolom Gen C
ALTER TABLE `forklift`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;

-- 2. Index query per bulan
CREATE INDEX IF NOT EXISTS `idx_forklift_date_created` ON `forklift` (`date_created`);

-- 3. Hak akses: approve + list + view untuk role approver forklift lama
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, pg.page_name, pg.action_name
FROM `role_permissions` rp
CROSS JOIN (
          SELECT 'forklift' AS page_name, 'approve' AS action_name
UNION ALL SELECT 'forklift', 'list'
UNION ALL SELECT 'forklift', 'view'
) pg
WHERE LOWER(rp.page_name) = 'forklift'
  AND LOWER(rp.action_name) = 'approvalbtn'
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = pg.page_name AND x.action_name = pg.action_name);

-- ---------------------------------------------------------------------------
--  CEK HASIL (jalankan terpisah kalau mau lihat):
--    SHOW COLUMNS FROM `forklift` LIKE 'date_approve';
--    SELECT role_id, page_name, action_name FROM role_permissions
--     WHERE page_name = 'forklift' AND action_name IN ('approve','list','view')
--     ORDER BY role_id, action_name;
-- ---------------------------------------------------------------------------

-- ---------------------------------------------------------------------------
--  VERSI MANUAL (HANYA kalau bagian 1/2 error karena MySQL). Cek dulu:
--    SHOW COLUMNS FROM `forklift`;
--  lalu jalankan baris untuk kolom yang BELUM ada saja:
--    ALTER TABLE `forklift` ADD COLUMN `date_approve` DATETIME NULL DEFAULT NULL;
--    ALTER TABLE `forklift` ADD INDEX `idx_forklift_date_created` (`date_created`);
-- ---------------------------------------------------------------------------
