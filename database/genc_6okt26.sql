-- ============================================================================
--  SESI 6 OKT 2026 - Mesin & Unit + Inisial dari menu Users   [GENC-06OKT26]
--  Jalankan SEKALI di phpMyAdmin (database web_wh). Aman dijalankan 2x.
--  Tidak mengubah / menghapus data checklist lama.
--
--  1. Tiga tabel kecil: daftar mesin & unit tambahan + isi checklist bawaan yang diubah / dikunci.
--     (Kalau lupa dijalankan, tabel ini juga dibuat otomatis saat menu
--      Mesin & Unit pertama kali dibuka oleh akun yang punya akses.)
--  2. Hak akses menu Mesin & Unit (lihat/tambah/ubah/hapus) untuk role yang
--     sekarang boleh MENAMBAH USER (Developer & Administrator di kantor).
--     Role lain bisa diberi lewat Role Permissions (baris "Mesin & Unit").
-- ============================================================================

CREATE TABLE IF NOT EXISTS `genc_mesin` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `page` VARCHAR(64) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `area` VARCHAR(100) NOT NULL DEFAULT '',
    `ikon` VARCHAR(40) NOT NULL DEFAULT 'fa-cube',
    `pelaksanaan` VARCHAR(150) NOT NULL DEFAULT '',
    `items` MEDIUMTEXT NULL,
    `item_seq` INT NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `urut` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL, `created_by` VARCHAR(100) NULL,
    `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `uk_genc_mesin_page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `genc_unit` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `folder` VARCHAR(64) NOT NULL,
    `profile` VARCHAR(64) NOT NULL DEFAULT '',
    `page` VARCHAR(64) NOT NULL,
    `slug` VARCHAR(100) NOT NULL DEFAULT '',
    `label` VARCHAR(100) NOT NULL DEFAULT '',
    `kode` VARCHAR(100) NOT NULL DEFAULT '',
    `alias` TEXT NULL,
    `builtin` TINYINT(1) NOT NULL DEFAULT 0,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `urut` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL, `created_by` VARCHAR(100) NULL,
    `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
    PRIMARY KEY (`id`), KEY `ix_genc_unit_folder` (`folder`), KEY `ix_genc_unit_page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [GENC-06OKT26-ISI] Isi checklist mesin bawaan yang diubah lewat menu + kunci (lock) per mesin.
-- Kosong = semua mesin memakai isi dari kode, persis seperti sebelumnya.
CREATE TABLE IF NOT EXISTS `genc_profil` (
    `profil` VARCHAR(64) NOT NULL,
    `items` MEDIUMTEXT NULL,
    `gambar` TEXT NULL,
    `item_seq` INT NOT NULL DEFAULT 0,
    `kunci` TINYINT(1) NULL,
    `updated_at` DATETIME NULL, `updated_by` VARCHAR(100) NULL,
    `kunci_at` DATETIME NULL, `kunci_by` VARCHAR(100) NULL,
    PRIMARY KEY (`profil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hak akses: mesin/list, add, edit, delete untuk role yang punya users/add
INSERT INTO role_permissions (role_id, page_name, action_name)
SELECT DISTINCT r.role_id, 'mesin', a.action_name
FROM role_permissions r
JOIN (SELECT 'list' AS action_name UNION ALL SELECT 'add' UNION ALL SELECT 'edit' UNION ALL SELECT 'delete') a
WHERE r.page_name = 'users' AND r.action_name = 'add'
  AND NOT EXISTS (SELECT 1 FROM role_permissions x WHERE x.role_id = r.role_id AND x.page_name = 'mesin' AND x.action_name = a.action_name);

-- Cek hasil (opsional):
-- SELECT r.role_name, p.action_name FROM role_permissions p JOIN roles r ON r.role_id = p.role_id WHERE p.page_name = 'mesin' ORDER BY r.role_name, p.action_name;

-- ============================================================================
--  INISIAL (PARAF) DARI MENU USERS   [GENC-06OKT26-INISIAL]
--  Kolom baru users.inisial (kosong = otomatis). Daftar inisial resmi SPV yang
--  selama ini ada di kode disalin SEKALI ke kolom ini (hanya user yang kolomnya
--  masih kosong) -> report tetap sama, lalu admin bisa mengubahnya di menu Users.
-- ============================================================================
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `inisial` VARCHAR(5) NULL DEFAULT NULL;

UPDATE `users` SET `inisial` = CASE LOWER(TRIM(`username`))
    WHEN 'candra' THEN 'CFW'
    WHEN 'pramono.nugroho' THEN 'PAN'
    WHEN 'pramono' THEN 'PAN'
    WHEN 'fitri.fidiastuti' THEN 'FFI'
    WHEN 'hendro.cahyono' THEN 'HCO'
    WHEN 'edy.hartono' THEN 'EHO'
    WHEN 'edy.haryanto' THEN 'EHO'
    WHEN 'yudi.setyawan' THEN 'YSE'
    WHEN 'bayu.suryanto' THEN 'BSO'
    WHEN 'nana.sujana' THEN 'NSA'
    WHEN 'cahyudi' THEN 'CHY'
    WHEN 'findi.atikaningsing' THEN 'FND'
    WHEN 'ali.usman' THEN 'ALS'
    WHEN 'tri.istianto' THEN 'TST'
    WHEN 'ibnu.mubharok' THEN 'IBN'
    WHEN 'anggi' THEN 'ANG'
    WHEN 'rendi' THEN 'RND'
    WHEN 'cahyudi.supriyadi' THEN 'CHY'
    WHEN 'anggi.diantoro' THEN 'ANG'
    WHEN 'ibnu.mubarok' THEN 'IBN'
END
WHERE (`inisial` IS NULL OR `inisial` = '')
  AND LOWER(TRIM(`username`)) IN ('candra', 'pramono.nugroho', 'pramono', 'fitri.fidiastuti', 'hendro.cahyono', 'edy.hartono', 'edy.haryanto', 'yudi.setyawan', 'bayu.suryanto', 'nana.sujana', 'cahyudi', 'findi.atikaningsing', 'ali.usman', 'tri.istianto', 'ibnu.mubharok', 'anggi', 'rendi', 'cahyudi.supriyadi', 'anggi.diantoro', 'ibnu.mubarok');

-- Cek hasil (opsional):
-- SELECT username, nama, inisial FROM users ORDER BY nama;
