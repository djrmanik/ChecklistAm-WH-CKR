-- ============================================================================
--  CONVEYOR - Checklist AM (Generasi C)                       23 Sep 2026
--
--  Jalankan SEKALI di phpMyAdmin, database web_wh (tab SQL -> paste -> Go).
--  Aman dijalankan ulang: CREATE TABLE IF NOT EXISTS, dan INSERT hak akses
--  hanya menambah baris yang belum ada.
--
--  Isi file:
--    1. Tabel `conveyor`
--    2. Hak akses halaman conveyor (role_permissions) - DISALIN dari hak akses
--       halaman forklift, jadi role yang boleh buka forklift otomatis boleh
--       buka conveyor dengan hak yang sama. Tidak ada role yang ditebak.
--    3. (OPSIONAL, masih dikomentari) trigger auto-approve seperti geprek
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. TABEL
-- Kolom item: nilai 'OK' / 'NOK' / 'PR' (sama dengan Generasi A).
-- kondisi: dihitung OTOMATIS oleh controller -> semua OK = '✔️', selain itu '❌'
-- (nilai sama dengan Menu::$kondisi, jadi trigger & halaman NOK tetap cocok).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conveyor` (
  `id`                       INT(11)      NOT NULL AUTO_INCREMENT,
  `date_created`             DATETIME     NOT NULL,
  `user_created`             VARCHAR(100) NOT NULL,

  -- STANDAR PEMBERSIHAN (CLEANING)
  `alas_conveyor`            VARCHAR(10)  NOT NULL,
  `body_conveyor`            VARCHAR(10)  NOT NULL,
  `kaki_conveyor`            VARCHAR(10)  NOT NULL,
  -- STANDAR PENGECEKAN (INSPECTION)
  `tombol_switch`            VARCHAR(10)  NOT NULL,
  `sensor`                   VARCHAR(10)  NOT NULL,
  `tombol_emergency_stop`    VARCHAR(10)  NOT NULL,
  `panel`                    VARCHAR(10)  NOT NULL,
  `motor_mesin_conveyor`     VARCHAR(10)  NOT NULL,
  `tombol_on_off_manual`     VARCHAR(10)  NOT NULL,
  `kaki_conveyor_inspection` VARCHAR(10)  NOT NULL,

  `kondisi`                  VARCHAR(10)  NOT NULL,
  `keterangan`               TEXT         NULL,
  `approval`                 VARCHAR(20)  NULL DEFAULT NULL,
  `user_approve`             VARCHAR(100) NULL DEFAULT NULL,
  `date_approve`             DATETIME     NULL DEFAULT NULL,
  `date_update`              DATETIME     NULL DEFAULT NULL,
  `user_update`              VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_conveyor_date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- 2. HAK AKSES
-- conveyor/list, view, add, edit, delete  <- sama dengan forklift/<aksi yang sama>
-- conveyor/approve                         <- sama dengan forklift/approvalbtn
-- Cek hasilnya: SELECT * FROM role_permissions WHERE page_name = 'conveyor';
-- Mau ubah per role? Lewat menu Developer Menu -> Role Permissions.
-- ----------------------------------------------------------------------------
INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, 'conveyor', LOWER(rp.action_name)
FROM `role_permissions` rp
WHERE LOWER(rp.page_name) = 'forklift'
  AND LOWER(rp.action_name) IN ('list', 'view', 'add', 'edit', 'delete')
  AND NOT EXISTS (
      SELECT 1 FROM `role_permissions` x
      WHERE x.role_id = rp.role_id AND x.page_name = 'conveyor' AND x.action_name = LOWER(rp.action_name)
  );

INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, 'conveyor', 'approve'
FROM `role_permissions` rp
WHERE LOWER(rp.page_name) = 'forklift'
  AND LOWER(rp.action_name) = 'approvalbtn'
  AND NOT EXISTS (
      SELECT 1 FROM `role_permissions` x
      WHERE x.role_id = rp.role_id AND x.page_name = 'conveyor' AND x.action_name = 'approve'
  );

-- ----------------------------------------------------------------------------
-- 3. (OPSIONAL) TRIGGER AUTO-APPROVE - sama persis dengan approval_mesin_geprek.
-- Belum diputuskan menulis 'PAN' atau 'System' (handover 22 Sep §10.1 no.5),
-- jadi SENGAJA masih dikomentari. Kalau mau dipakai: hapus tanda "-- " di
-- 7 baris di bawah, lalu jalankan (DELIMITER dibutuhkan di tab SQL phpMyAdmin).
-- ----------------------------------------------------------------------------
-- DELIMITER $$
-- CREATE TRIGGER `approval_conveyor` BEFORE INSERT ON `conveyor` FOR EACH ROW
-- BEGIN
--     IF NEW.kondisi = '✔️' THEN
--         SET NEW.approval = 'Approved'; SET NEW.user_approve = 'PAN'; SET NEW.date_approve = NOW();
--     END IF;
-- END$$
-- DELIMITER ;
