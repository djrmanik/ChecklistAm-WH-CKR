-- ============================================================================
--  Checklist AM Warehouse CKR - 16 Okt 2026 - menu Pantau (OPSIONAL)   [GENC-16OKT26-PANTAU]
--
--  Approval & NOK History pindah dari PalletmoverController (palletmover/approval &
--  palletmover/uncompleted) ke PantauController (pantau/approval & pantau/temuan).
--
--  SQL INI TIDAK WAJIB. Aplikasi sudah membaca hak lama sebagai hak baru (libs/ACL.php,
--  ACL::$alias), jadi tanpa SQL ini semua role tetap bisa membuka kedua menu seperti biasa.
--  Gunanya hanya merapikan isi tabel role_permissions supaya namanya sama dengan route baru
--  (berguna untuk dokumentasi / URS).
--
--  Aman dijalankan berkali-kali. Baris lama TIDAK dihapus -> kalau kode dikembalikan ke
--  versi 15 Okt, hak akses tetap jalan. Matriks Role Permissions membersihkan baris lama
--  sendiri saat hak itu dicabut.
-- ============================================================================

INSERT INTO role_permissions (role_id, page_name, action_name)
SELECT DISTINCT a.role_id, 'pantau', 'approval' FROM role_permissions a
WHERE a.page_name = 'palletmover' AND a.action_name = 'approval'
  AND NOT EXISTS (SELECT 1 FROM role_permissions b WHERE b.role_id = a.role_id AND b.page_name = 'pantau' AND b.action_name = 'approval');

INSERT INTO role_permissions (role_id, page_name, action_name)
SELECT DISTINCT a.role_id, 'pantau', 'temuan' FROM role_permissions a
WHERE a.page_name = 'palletmover' AND a.action_name = 'uncompleted'
  AND NOT EXISTS (SELECT 1 FROM role_permissions b WHERE b.role_id = a.role_id AND b.page_name = 'pantau' AND b.action_name = 'temuan');

-- Cek hasil: setiap role yang punya hak lama sekarang juga punya hak baru
-- SELECT r.role_name, p.page_name, p.action_name FROM role_permissions p JOIN roles r ON r.role_id = p.role_id
--  WHERE (p.page_name = 'pantau') OR (p.page_name = 'palletmover' AND p.action_name IN ('approval', 'uncompleted')) ORDER BY r.role_name, p.page_name;
