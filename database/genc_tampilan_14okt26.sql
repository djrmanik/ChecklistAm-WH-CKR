-- ============================================================================
--  SUPER EDITOR UI ("Mode edit tampilan") - Paket 4A   [GENC-13OKT26-TAMPILAN]
--  Jalankan SEKALI di phpMyAdmin (database web_wh), SETELAH genc_13okt26.sql.
--  Aman dijalankan 2x (IF NOT EXISTS / NOT EXISTS). Tidak mengubah / menghapus data checklist.
--  (Kalau lupa dijalankan: kedua tabel dibuat otomatis saat halaman Riwayat tampilan / Mode edit dibuka,
--   tapi HAK AKSES di bawah tetap harus dicentang manual di Role Permissions -> baris "Tampilan".)
--
--  1. genc_tampilan       : riwayat versi tampilan (1 baris = 1 versi). `sekarang` = versi yang berlaku
--                           untuk semua user. Kembalikan = penanda pindah (TIDAK membuat versi baru).
--                           Tabel kosong = v1 = tampilan aplikasi seperti sebelum paket ini.
--  2. genc_tampilan_draf  : draf Mode edit milik masing-masing user (belum terlihat user lain).
--  3. Hak akses tampilan/list (Riwayat tampilan) + tampilan/edit (Mode edit tampilan) untuk role yang
--     sudah boleh mengubah Mesin & Unit (Developer & Administrator). Bisa diubah di Role Permissions.
--
--  BATALKAN (kembali seperti sebelum paket ini; pakai bersama file aplikasi lama):
--     DELETE FROM `role_permissions` WHERE `page_name` = 'tampilan';
--     DROP TABLE IF EXISTS `genc_tampilan_draf`, `genc_tampilan`;
--  (Cukup kembali ke tampilan awal tanpa membuang riwayat? Buka Riwayat tampilan -> Kembalikan ke v1.)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `genc_tampilan` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `versi` INT(11) NOT NULL,
    `sekarang` TINYINT(1) NOT NULL DEFAULT 0,
    `data` MEDIUMTEXT NULL,
    `ringkasan` VARCHAR(255) NOT NULL DEFAULT '',
    `meta` MEDIUMTEXT NULL,
    `halaman` TEXT NULL,
    `created_at` DATETIME NULL,
    `created_by` VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`), UNIQUE KEY `versi` (`versi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `genc_tampilan_draf` (
    `user` VARCHAR(100) NOT NULL,
    `data` MEDIUMTEXT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.`role_id`, 'tampilan', a.`act`
FROM `role_permissions` rp
JOIN (SELECT 'list' AS `act` UNION SELECT 'edit') a
WHERE rp.`page_name` = 'mesin' AND rp.`action_name` = 'edit'
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x WHERE x.`role_id` = rp.`role_id` AND x.`page_name` = 'tampilan' AND x.`action_name` = a.`act`);
