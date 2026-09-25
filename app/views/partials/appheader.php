<?php
/**
 * KERANGKA APP GENERASI C - topbar + sidebar            [GENC-24SEP26-SHELL]
 * Dulu: navbar phpRAD + sidebar ungu (dropdown akun menimpa sidebar, "Hi Pramono" dobel).
 * Isi menu tetap dari helpers/Menu.php (label & path) + hak akses ACL seperti dulu;
 * file ini hanya mengelompokkan, memberi ikon, dan menandai menu aktif - termasuk
 * halaman ber-tab (AGV Counterbalance, Dumping KIR6/KIR7) yang dulu tidak tertandai.
 * Cadangan versi lama: _backup_genc_24sept26/appheader_sebelum-shell.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$gs_logged = (user_login_status() == true);
?>
<header class="gs-top">
<?php if ($gs_logged): ?>
    <button type="button" class="gs-iconbtn" data-gs-side-toggle aria-label="Tampilkan / sembunyikan menu" aria-controls="gs-side"><i class="fa fa-bars"></i></button>
<?php endif; ?>
    <a class="gs-brand" href="<?php print_link(HOME_PAGE); ?>">
        <span class="gs-mark" aria-hidden="true"><i class="fa fa-check-square-o"></i></span>
        <span class="gs-brand__text"><span class="gs-brand__name">Checklist AM</span><span class="gs-brand__sub">Warehouse CKR &middot; Autonomous Maintenance</span></span>
    </a>
    <span class="gs-top__spacer"></span>
<?php if ($gs_logged):
    $gs_user  = isset($_SESSION[APP_ID . 'user_data']) ? $_SESSION[APP_ID . 'user_data'] : array();
    $gs_nama  = trim(isset($gs_user['nama']) ? (string) $gs_user['nama'] : '');
    $gs_nama  = $gs_nama !== '' ? ucwords($gs_nama) : ucwords((string) USER_NAME);
    $gs_words = preg_split('/[\s._]+/', trim($gs_nama));
    $gs_ini   = strtoupper(substr($gs_words[0], 0, 1) . (isset($gs_words[1]) ? substr($gs_words[1], 0, 1) : substr($gs_words[0], 1, 1)));
    $gs_role  = (string) ACL::$user_role;
?>
    <span class="gs-today"><i class="fa fa-calendar-o"></i> <?php echo genc_e(genc_date(time(), true)); ?></span>
    <div class="gs-user" data-gs-user>
        <button type="button" class="gs-user__btn" data-gs-user-toggle aria-haspopup="true" aria-expanded="false">
            <span class="gs-avatar" aria-hidden="true"><?php echo genc_e($gs_ini); ?></span>
            <span class="gs-user__txt"><span class="gs-user__name"><?php echo genc_e($gs_nama); ?></span><span class="gs-user__role"><?php echo genc_e($gs_role); ?></span></span>
            <i class="fa fa-angle-down"></i>
        </button>
        <div class="gs-user__menu" role="menu">
            <div class="gs-user__head">
                <span class="gs-avatar gs-avatar--lg" aria-hidden="true"><?php echo genc_e($gs_ini); ?></span>
                <div style="min-width:0"><b><?php echo genc_e($gs_nama); ?></b><small><?php echo genc_e(USER_NAME); ?> &middot; <?php echo genc_e($gs_role); ?></small></div>
            </div>
            <a class="gs-menu-link" role="menuitem" href="<?php print_link('account'); ?>"><i class="fa fa-user"></i> Akun saya</a>
            <a class="gs-menu-link gs-menu-link--danger" role="menuitem" href="<?php print_link('index/logout?csrf_token=' . Csrf::$token); ?>"><i class="fa fa-sign-out"></i> Keluar</a>
        </div>
    </div>
<?php endif; ?>
</header>
<?php if ($gs_logged):
    $gs_page   = strtolower((string) Router::$page_name);
    $gs_action = strtolower((string) Router::$page_action);
    // menu -> halaman lain yang juga menandai menu itu aktif (tab, sub-halaman)
    $gs_match = array(
        'home'                    => array('home'),
        'agv_table_top_lift'      => array('agv_table_top_lift', 'counterbalance'),
        'kir3p01dp001'            => array('kir3p01dp001', 'kir6p01dp001', 'kir7p01dp001'),
        'roles'                   => array('roles'),
    );
    $gs_icons = array(
        'home' => 'fa-home', 'palletmover' => 'fa-th-large', 'forklift' => 'fa-truck', 'agv_table_top_lift' => 'fa-rocket',
        'kir3p01dp001' => 'fa-download', 'mesin_geprek' => 'fa-compress', 'conveyor' => 'fa-exchange',
        'palletmover/approval' => 'fa-check-square-o', 'palletmover/uncompleted' => 'fa-exclamation-triangle',
        'users' => 'fa-users', 'roles' => 'fa-shield', 'role_permissions' => 'fa-key', 'app_logs' => 'fa-history',
        'master_select' => 'fa-list-ul',
    );
    $gs_section_of = array('home' => 'main', 'palletmover/approval' => 'pantau', 'palletmover/uncompleted' => 'pantau',
        'users' => 'admin', 'master_select' => 'admin');
    $gs_sec_label  = array('main' => '', 'am' => 'Autonomous Maintenance', 'pantau' => 'Pantau', 'admin' => 'Admin & sistem', 'lain' => 'Lainnya');
    $gs_sections   = array('main' => array(), 'am' => array(), 'pantau' => array(), 'admin' => array(), 'lain' => array());
    foreach (Menu::$navbarsideleft as $m) {
        if (!empty($m['submenu'])) {
            $key = (stripos($m['label'], 'autonomous') !== false) ? 'am' : ((stripos($m['label'], 'developer') !== false) ? 'admin' : 'lain');
            foreach ($m['submenu'] as $s) { $gs_sections[$key][] = $s; }
        } else {
            $p = trim($m['path'], '/');
            $gs_sections[isset($gs_section_of[$p]) ? $gs_section_of[$p] : 'lain'][] = $m;
        }
    }
    $gs_is_active = function ($path) use ($gs_page, $gs_action, $gs_match) {
        $path = trim($path, '/');
        $seg  = explode('/', $path);
        if (isset($seg[1]) && $seg[1] !== '') { return $gs_page === $seg[0] && $gs_action === $seg[1]; }
        if ($seg[0] === 'palletmover' && in_array($gs_action, array('approval', 'uncompleted'), true)) { return false; }
        $pages = isset($gs_match[$seg[0]]) ? $gs_match[$seg[0]] : array($seg[0]);
        return in_array($gs_page, $pages, true);
    };
?>
<nav class="gs-side" id="gs-side" aria-label="Menu utama">
    <div class="gs-side__scroll">
    <?php foreach ($gs_sections as $key => $items):
        $shown = array();
        foreach ($items as $it) {
            if (ACL::is_allowed($it['path'])) { $shown[] = $it; continue; }
            // [GENC-25SEP26-AUDIT] menu ber-tab (AGV, Dumping): tampil kalau role boleh membuka SALAH SATU tabnya,
            // tautan ke tab pertama yang boleh (dulu: role yang cuma punya Counterbalance / KIR6 tidak melihat menu)
            $p0 = trim($it['path'], '/');
            if (isset($gs_match[$p0]) && $p0 !== 'home') {
                foreach ($gs_match[$p0] as $alt) { if ($alt !== $p0 && ACL::is_allowed($alt)) { $it['href'] = $alt; $shown[] = $it; break; } }
            }
        }
        if (empty($shown)) { continue; } ?>
        <div class="gs-sec">
            <?php if ($gs_sec_label[$key] !== ''): ?><div class="gs-sec__label"><?php echo genc_e($gs_sec_label[$key]); ?></div><?php endif; ?>
            <ul class="gs-nav">
            <?php foreach ($shown as $it):
                $p = trim($it['path'], '/');
                $icon = isset($gs_icons[$p]) ? $gs_icons[$p] : 'fa-circle-o';
                $act = $gs_is_active($p); ?>
                <li><a class="gs-nav__link<?php echo $act ? ' is-active' : ''; ?>" href="<?php print_link(isset($it['href']) ? $it['href'] : $p); ?>"<?php echo $act ? ' aria-current="page"' : ''; ?>><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i><span><?php echo genc_e($it['label']); ?></span></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    </div>
    <div class="gs-side__foot">PT Bintang Toedjoe &middot; Warehouse Cikarang</div>
</nav>
<div class="gs-overlay" data-gs-overlay></div>
<?php endif; ?>
