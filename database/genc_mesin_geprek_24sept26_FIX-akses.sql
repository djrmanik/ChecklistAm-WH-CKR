-- ============================================================================
--  MESIN GEPREK - FIX AKSES DAFTAR               [GENC-24SEP26-FIX2] 24 Sep 2026
--  Jalankan di phpMyAdmin, DB `web_wh`, tab SQL. Aman dijalankan 2x.
--
--  Masalah: role yang bisa approve geprek (punya approval / approvalbtn)
--  tapi TIDAK punya `mesin_geprek/list` (mis. role "Manager & Supervisor"):
--   - menu "Mesin Geprek" tidak muncul di sidebar (phpRAD hanya menampilkan
--     menu yang role-nya punya akses list - ini sudah begitu sebelum Gen C)
--   - sejak Gen C, halaman Approval lama diarahkan ke daftar Gen C
--     -> role itu kena 403 "You are not authorized".
--
--  Perbaikan: beri `mesin_geprek/list` HANYA ke role yang sudah punya hak
--  approve geprek (approval / approvalbtn / approve). Role lain tidak disentuh.
-- ============================================================================

-- 0. (opsional) lihat dulu role mana yang akan ditambah:
--    SELECT DISTINCT r.role_id, ro.role_name
--    FROM role_permissions r LEFT JOIN roles ro ON ro.role_id = r.role_id
--    WHERE r.page_name='mesin_geprek' AND r.action_name IN ('approval','approvalbtn','approve')
--      AND NOT EXISTS (SELECT 1 FROM role_permissions x WHERE x.role_id=r.role_id AND x.page_name='mesin_geprek' AND x.action_name='list');

INSERT INTO `role_permissions` (`role_id`, `page_name`, `action_name`)
SELECT DISTINCT rp.role_id, 'mesin_geprek', 'list'
FROM `role_permissions` rp
WHERE LOWER(rp.page_name) = 'mesin_geprek'
  AND LOWER(rp.action_name) IN ('approval', 'approvalbtn', 'approve')
  AND NOT EXISTS (SELECT 1 FROM `role_permissions` x
                  WHERE x.role_id = rp.role_id AND x.page_name = 'mesin_geprek' AND x.action_name = 'list');

-- CEK: semua role yang bisa approve sekarang juga punya list
--    SELECT role_id, GROUP_CONCAT(action_name ORDER BY action_name) FROM role_permissions
--     WHERE page_name='mesin_geprek' GROUP BY role_id;
--
-- BATALKAN (kalau memang role itu tidak boleh melihat daftar geprek):
--    DELETE FROM role_permissions WHERE page_name='mesin_geprek' AND action_name='list' AND role_id IN (<id role>);
