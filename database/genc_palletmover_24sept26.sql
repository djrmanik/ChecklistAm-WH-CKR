-- ============================================================================
--  PALLET MOVER 1-4 + PALLET STACKER -> Generasi C   [GENC-24SEP26-PALLETMOVER] 24 Sep 2026
--  Jalankan di phpMyAdmin, DB `web_wh`, tab SQL. Aman dijalankan 2x.
--
--  Isi:
--   1. Tambah kolom (kalau belum ada):
--        date_approve  - kapan di-approve (jejak audit)
--        kondisi       - SUDAH ADA di palletmover (Gen A); baris ini cuma jaga-jaga,
--                        dilewati kalau kolomnya ada (IF NOT EXISTS)
--      Kolom baru NULL -> data lama tidak diubah sama sekali (K6).
--   2. Index date_created (daftar & ringkasan per bulan).
--   3. Hak akses aksi BARU `palletmover/approve`, DISALIN dari role yang sekarang
--      punya `palletmover/approvalbtn` (tombol approve lama). Role itu juga
--      dipastikan punya `palletmover/list` + `palletmover/view` (K11) supaya menu
--      Pallet Mover & Stacker dan halaman detail terbuka untuk approver.
--   4. Role yang dulu bisa membuka TAB lama (palletmover1..4 / palletstacker) tapi
--      tidak punya `palletmover/list` -> diberi `palletmover/list` (tab lama sekarang
--      diarahkan ke halaman daftar; isi yang terlihat sama dengan dulu).
--   Hak role lain TIDAK diubah. TIDAK ada trigger baru (K13): approve manual.
--
--  Catatan: "IF NOT EXISTS" di ALTER / CREATE INDEX didukung MariaDB (XAMPP
--  memakai MariaDB). Kalau muncul error sintaks di baris itu, berarti server
--  memakai MySQL -> jalankan versi manual di bagian paling bawah file ini.
--
--  Halaman tetap jalan walaupun bagian 1 belum dijalankan (aplikasi cek
--  kolomnya dulu), tapi tombol Approve baru muncul setelah bagian 3.
-- ============================================================================

-- 1. Kolom Gen C
ALTER TABLE `palletmover`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;

-- 2. Index query per bulan
CREATE INDEX IF NOT EXISTS `idx_palletmover_date_created` ON `palletmover` (`date_created`);

-- 3. Hak akses: approve + list + view untuk role approver pallet mover lama
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, pg.page_name, pg.action_name
FROM `role_permissions` rp
CROSS JOIN (
          SELECT 'palletmover' AS page_name, 'approve' AS action_name
UNION ALL SELECT 'palletmover', 'list'
UNION ALL SELECT 'palletmover', 'view'
) pg
WHERE LOWER(rp.page_name) = 'palletmover'
  AND LOWER(rp.action_name) = 'approvalbtn'
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = pg.page_name AND x.action_name = pg.action_name);

-- 4. Pemegang tab lama -> daftar
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, 'palletmover', 'list'
FROM `role_permissions` rp
WHERE LOWER(rp.page_name) = 'palletmover'
  AND LOWER(rp.action_name) IN ('palletmover1', 'palletmover2', 'palletmover3', 'palletmover4', 'palletstacker')
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = 'palletmover' AND x.action_name = 'list');

-- ---------------------------------------------------------------------------
--  CEK HASIL (jalankan terpisah kalau mau lihat):
--    SHOW COLUMNS FROM `palletmover` LIKE 'date_approve';
--    SELECT role_id, page_name, action_name FROM role_permissions
--     WHERE page_name = 'palletmover' AND action_name IN ('approve','list','view')
--     ORDER BY role_id, action_name;
-- ---------------------------------------------------------------------------

-- ---------------------------------------------------------------------------
--  VERSI MANUAL (HANYA kalau bagian 1/2 error karena MySQL). Cek dulu:
--    SHOW COLUMNS FROM `palletmover`;
--  lalu jalankan baris untuk kolom yang BELUM ada saja:
--    ALTER TABLE `palletmover` ADD COLUMN `date_approve` DATETIME NULL DEFAULT NULL;
--    ALTER TABLE `palletmover` ADD INDEX `idx_palletmover_date_created` (`date_created`);
-- ---------------------------------------------------------------------------
