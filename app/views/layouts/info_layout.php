<?php
/**
 * [GENC-25SEP26-AUDIT] Layout info & halaman error (403, 404, role tidak dikenal, error server).
 * Dulu: navbar phpRAD terpisah (logo "P", About/Help/Contact) -> beda sendiri dari app.
 * Sekarang memakai kerangka yang SAMA dengan main_layout.php (topbar, sidebar, footer Gen C),
 * jadi user yang nyasar ke halaman error tetap bisa pindah halaman lewat menu.
 * Cadangan: _backup_genc_25sept26/layouts_info_layout_sebelum-audit.php
 */
// halaman 404 dari Router tidak lewat SecureController -> daftar hak akses belum dimuat
if (defined('USER_ROLE') && USER_ROLE && empty(ACL::$role_pages)) { new ACL; }
include __DIR__ . '/main_layout.php';
