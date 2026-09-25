-- ============================================================================
--  AGV TABLE TOP LIFT + AGV COUNTERBALANCE -> Generasi C   [GENC-24SEP26-AGV] 24 Sep 2026
--  Jalankan di phpMyAdmin, DB `web_wh`, tab SQL. Aman dijalankan 2x.
--
--  Isi (sama untuk kedua tabel agv_table_top_lift & counterbalance):
--   1. Tambah 2 kolom (kalau belum ada) - K14: kolom pengubah pakai `user_perubah` yang sudah ada
--        kondisi       - dihitung aplikasi: semua Baik = '✔️', ada temuan = '❌'
--                        (utf8mb4 eksplisit: tabel lama bisa latin1, emoji tidak muat di latin1)
--        date_approve  - kapan di-approve (jejak audit)
--      Keduanya NULL -> data lama tidak diubah sama sekali (K6).
--   2. Index date_created (daftar & ringkasan per bulan).
--   3. Hak akses aksi BARU `<tabel>/approve`, DISALIN dari role yang sekarang
--      punya `forklift/approvalbtn` (K11: AGV belum pernah punya approve).
--      Role itu juga diberi `<tabel>/list` + `<tabel>/view` supaya menu "AGV"
--      & halaman detail terbuka untuk approver. Hak role lain TIDAK diubah.
--   TIDAK ada trigger (K13): semua checklist AGV di-approve manual.
--
--  Catatan: "IF NOT EXISTS" di ALTER / CREATE INDEX didukung MariaDB (XAMPP
--  memakai MariaDB). Kalau muncul error sintaks di baris itu, berarti server
--  memakai MySQL -> jalankan versi manual di bagian paling bawah file ini.
--
--  Halaman tetap jalan walaupun bagian 1 belum dijalankan (aplikasi cek
--  kolomnya dulu), tapi tombol Approve baru muncul setelah bagian 3.
-- ============================================================================

-- 1. Kolom Gen C
ALTER TABLE `agv_table_top_lift`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;
ALTER TABLE `counterbalance`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;

-- 2. Index query per bulan
CREATE INDEX IF NOT EXISTS `idx_agv_table_top_lift_date_created` ON `agv_table_top_lift` (`date_created`);
CREATE INDEX IF NOT EXISTS `idx_counterbalance_date_created`     ON `counterbalance` (`date_created`);

-- 3. Hak akses: approve + list + view untuk role approver forklift
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, pg.page_name, pg.action_name
FROM `role_permissions` rp
CROSS JOIN (
          SELECT 'agv_table_top_lift' AS page_name, 'approve' AS action_name
UNION ALL SELECT 'agv_table_top_lift', 'list'
UNION ALL SELECT 'agv_table_top_lift', 'view'
UNION ALL SELECT 'counterbalance', 'approve'
UNION ALL SELECT 'counterbalance', 'list'
UNION ALL SELECT 'counterbalance', 'view'
) pg
WHERE LOWER(rp.page_name) = 'forklift'
  AND LOWER(rp.action_name) = 'approvalbtn'
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = pg.page_name AND x.action_name = pg.action_name);

-- ---------------------------------------------------------------------------
--  CEK HASIL (jalankan terpisah kalau mau lihat):
--    SHOW COLUMNS FROM `agv_table_top_lift` LIKE 'kondisi';     -- harus ada (juga counterbalance)
--    SHOW COLUMNS FROM `agv_table_top_lift` LIKE 'date_approve';
--    SELECT role_id, page_name, action_name FROM role_permissions
--     WHERE page_name IN ('agv_table_top_lift','counterbalance') AND action_name IN ('approve','list','view')
--     ORDER BY page_name, role_id, action_name;
-- ---------------------------------------------------------------------------

-- ---------------------------------------------------------------------------
--  VERSI MANUAL (HANYA kalau bagian 1/2 error karena MySQL). Cek dulu:
--    SHOW COLUMNS FROM `agv_table_top_lift`;
--  lalu jalankan baris untuk kolom yang BELUM ada saja (ulangi untuk counterbalance):
--    ALTER TABLE `agv_table_top_lift` ADD COLUMN `kondisi` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
--    ALTER TABLE `agv_table_top_lift` ADD COLUMN `date_approve` DATETIME NULL DEFAULT NULL;
--    ALTER TABLE `agv_table_top_lift` ADD INDEX `idx_agv_table_top_lift_date_created` (`date_created`);
-- ---------------------------------------------------------------------------
