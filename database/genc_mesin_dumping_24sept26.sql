-- ============================================================================
--  MESIN DUMPING KIR3 / KIR6 / KIR7 -> Generasi C      [GENC-24SEP26-DUMPING] 24 Sep 2026
--  Jalankan di phpMyAdmin, DB `web_wh`, tab SQL. Aman dijalankan 2x.
--
--  Isi (sama untuk ketiga tabel kir3p01dp001, kir6p01dp001, kir7p01dp001):
--   1. Tambah 2 kolom (kalau belum ada) - K14: kolom pengubah pakai `user_perubah` yang sudah ada
--        kondisi       - dihitung aplikasi: semua Baik = '✔️', ada temuan = '❌'
--                        (utf8mb4 eksplisit: tabel lama bisa latin1, emoji tidak muat di latin1)
--        date_approve  - kapan di-approve (jejak audit)
--      Keduanya NULL -> data lama tidak diubah sama sekali (K6).
--   2. Index date_created (daftar & ringkasan per bulan).
--   3. Hak akses aksi BARU `<tabel>/approve`, DISALIN dari role yang sekarang
--      punya `forklift/approvalbtn` (K11: dumping belum pernah punya approve).
--      Role itu juga diberi `<tabel>/list` + `<tabel>/view` supaya menu
--      "Mesin Dumping" & halaman detail terbuka untuk approver (kasus 403
--      akun Pramono di geprek, 24 Sep). Hak role lain TIDAK diubah.
--   TIDAK ada trigger (K13): semua checklist dumping di-approve manual.
--
--  Catatan: "IF NOT EXISTS" di ALTER / CREATE INDEX didukung MariaDB (XAMPP
--  memakai MariaDB). Kalau muncul error sintaks di baris itu, berarti server
--  memakai MySQL -> jalankan versi manual di bagian paling bawah file ini.
--
--  Halaman tetap jalan walaupun bagian 1 belum dijalankan (aplikasi cek
--  kolomnya dulu), tapi tombol Approve baru muncul setelah bagian 3.
-- ============================================================================

-- 1. Kolom Gen C
ALTER TABLE `kir3p01dp001`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;
ALTER TABLE `kir6p01dp001`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;
ALTER TABLE `kir7p01dp001`
  ADD COLUMN IF NOT EXISTS `kondisi`      VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `date_approve` DATETIME NULL DEFAULT NULL;

-- 2. Index query per bulan
CREATE INDEX IF NOT EXISTS `idx_kir3p01dp001_date_created` ON `kir3p01dp001` (`date_created`);
CREATE INDEX IF NOT EXISTS `idx_kir6p01dp001_date_created` ON `kir6p01dp001` (`date_created`);
CREATE INDEX IF NOT EXISTS `idx_kir7p01dp001_date_created` ON `kir7p01dp001` (`date_created`);

-- 3. Hak akses: approve + list + view untuk role approver forklift
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, pg.page_name, pg.action_name
FROM `role_permissions` rp
CROSS JOIN (
          SELECT 'kir3p01dp001' AS page_name, 'approve' AS action_name
UNION ALL SELECT 'kir3p01dp001', 'list'
UNION ALL SELECT 'kir3p01dp001', 'view'
UNION ALL SELECT 'kir6p01dp001', 'approve'
UNION ALL SELECT 'kir6p01dp001', 'list'
UNION ALL SELECT 'kir6p01dp001', 'view'
UNION ALL SELECT 'kir7p01dp001', 'approve'
UNION ALL SELECT 'kir7p01dp001', 'list'
UNION ALL SELECT 'kir7p01dp001', 'view'
) pg
WHERE LOWER(rp.page_name) = 'forklift'
  AND LOWER(rp.action_name) = 'approvalbtn'
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = pg.page_name AND x.action_name = pg.action_name);

-- ---------------------------------------------------------------------------
--  CEK HASIL (jalankan terpisah kalau mau lihat):
--    SHOW COLUMNS FROM `kir3p01dp001` LIKE 'kondisi';        -- harus ada (juga kir6, kir7)
--    SHOW COLUMNS FROM `kir3p01dp001` LIKE 'date_approve';
--    SELECT role_id, page_name, action_name FROM role_permissions
--     WHERE page_name LIKE 'kir_p01dp001' AND action_name IN ('approve','list','view')
--     ORDER BY page_name, role_id, action_name;
-- ---------------------------------------------------------------------------

-- ---------------------------------------------------------------------------
--  VERSI MANUAL (HANYA kalau bagian 1/2 error karena MySQL). Cek dulu:
--    SHOW COLUMNS FROM `kir3p01dp001`;
--  lalu jalankan baris untuk kolom yang BELUM ada saja (ulangi untuk kir6 & kir7):
--    ALTER TABLE `kir3p01dp001` ADD COLUMN `kondisi` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
--    ALTER TABLE `kir3p01dp001` ADD COLUMN `date_approve` DATETIME NULL DEFAULT NULL;
--    ALTER TABLE `kir3p01dp001` ADD INDEX `idx_kir3p01dp001_date_created` (`date_created`);
-- ---------------------------------------------------------------------------
