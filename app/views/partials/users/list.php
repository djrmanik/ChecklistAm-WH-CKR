<?php
/**
 * USERS - daftar (Generasi C)                            [GENC-24SEP26-SHELL]
 * Data: UsersController::index(). Cadangan tampilan lama: tidak dihapus (users/add.php, edit.php, view.php tidak dipakai lagi).
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data;
$roles = $d['roles'];
$can = $d['can'];
$csrf = Csrf::$token;
$tone = function ($name) {
    $n = strtolower((string) $name);
    if (strpos($n, 'developer') !== false || strpos($n, 'admin') !== false) { return 'genc-tag--pending'; }
    if (strpos($n, 'manager') !== false || strpos($n, 'supervisor') !== false || strpos($n, 'spv') !== false) { return 'genc-tag--primary'; }
    return '';
};
$ini = function ($nama, $user) {
    $w = preg_split('/[\s._]+/', trim((string) ($nama !== '' ? $nama : $user)));
    return strtoupper(substr($w[0], 0, 1) . (isset($w[1]) ? substr($w[1], 0, 1) : substr($w[0], 1, 1)));
};
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Users</span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Users</h1>
            <p class="genc-subtitle">Akun yang bisa login. <b>Role</b> menentukan menu &amp; mesin yang bisa dibuka<?php if ($can['perm']): ?> &mdash; atur di <a href="<?php print_link('role_permissions'); ?>">Role Permissions</a><?php endif; ?>.</p>
        </div>
        <?php if ($can['add']): ?>
        <div class="genc-actions"><a class="genc-btn genc-btn--primary" href="<?php print_link('users/add'); ?>"><i class="fa fa-user-plus"></i> Tambah user</a></div>
        <?php endif; ?>
    </div>

    <div class="genc-card">
        <form class="genc-toolbar" method="get" action="<?php print_link('users'); ?>">
            <div class="genc-chips" role="tablist" aria-label="Filter role" style="flex-wrap:wrap">
                <a class="genc-chip<?php echo $d['role'] === '' ? ' is-active' : ''; ?>" href="<?php echo genc_e(genc_url('users', array('q' => $d['q']))); ?>">Semua<span class="genc-chip__count"><?php echo (int) $d['total']; ?></span></a>
                <?php foreach ($roles as $rid => $r): if ((int) $r['n'] === 0 && $d['role'] !== (string) $rid) { continue; } ?>
                    <a class="genc-chip<?php echo $d['role'] === (string) $rid ? ' is-active' : ''; ?>" href="<?php echo genc_e(genc_url('users', array('role' => $rid, 'q' => $d['q']))); ?>"><?php echo genc_e($r['role_name']); ?><span class="genc-chip__count"><?php echo (int) $r['n']; ?></span></a>
                <?php endforeach; ?>
            </div>
            <span class="genc-grow"></span>
            <?php if ($d['role'] !== ''): ?><input type="hidden" name="role" value="<?php echo genc_e($d['role']); ?>"><?php endif; ?>
            <label class="genc-search"><i class="fa fa-search"></i><span class="sr-only">Cari</span>
                <input class="genc-input" type="search" name="q" value="<?php echo genc_e($d['q']); ?>" placeholder="Cari nama / username / email"></label>
        </form>

        <?php if (empty($d['rows'])): ?>
            <div class="genc-empty"><div class="genc-empty__icon"><i class="fa fa-users"></i></div>
                <div class="genc-empty__title">Tidak ada user yang cocok</div>
                <p class="genc-muted"><a href="<?php print_link('users'); ?>">Tampilkan semua user</a></p></div>
        <?php else: ?>
        <div class="genc-table-wrap">
        <table class="genc-atable">
            <thead><tr><th>Nama</th><th>Paraf</th><th>Email</th><th>Role</th><th>Status</th><th class="genc-col-actions"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
            <?php foreach ($d['rows'] as $u):
                $blocked = UsersController::users_is_blocked($u['account_status']);
                $self = (int) $u['id_user'] === (int) USER_ID; ?>
                <tr class="<?php echo $blocked ? 'is-muted' : ''; ?>">
                    <td data-label="Nama">
                        <div class="genc-person"><span class="genc-avatar" aria-hidden="true"><?php echo genc_e(checklist_user_initial($u['username'])); /* [GENC-06OKT26-INISIAL] avatar = paraf */ ?></span>
                            <div style="min-width:0"><div class="genc-person__name"><?php echo genc_e(ucwords((string) $u['nama'])); ?><?php echo $self ? ' <span class="genc-tag" style="margin-left:4px">Anda</span>' : ''; ?></div>
                                <span class="genc-cellsub genc-mono"><?php echo genc_e($u['username']); ?></span></div></div>
                    </td>
                    <td data-label="Paraf"><?php /* [GENC-06OKT26-INISIAL] paraf yang tercetak di report */ $pi = checklist_user_initial($u['username']); $pset = !empty($u['inisial']); ?><span class="genc-tag genc-mono<?php echo $pset ? ' genc-tag--primary' : ''; ?>" title="<?php echo $pset ? 'Diatur di menu Users' : ($d['has_inisial'] ? 'Otomatis (belum diatur)' : 'Otomatis'); ?>"><?php echo genc_e($pi); ?></span><?php echo $pset ? '' : '<span class="genc-cellsub">otomatis</span>'; ?></td>
                    <td data-label="Email"><span class="genc-muted"><?php echo genc_e($u['email']); ?></span></td>
                    <td data-label="Role"><?php if ($u['role_name'] !== null): ?>
                        <a class="genc-tag <?php echo $tone($u['role_name']); ?>" href="<?php echo genc_e(genc_url('users', array('role' => $u['user_role_id']))); ?>" style="text-decoration:none"><?php echo genc_e($u['role_name']); ?></a>
                        <?php else: ?><span class="genc-tag genc-tag--nok" title="role_id <?php echo genc_e($u['user_role_id']); ?> tidak ada di tabel roles">Role tidak ada</span><?php endif; ?></td>
                    <td data-label="Status"><?php if ($blocked): ?>
                        <span class="genc-badge genc-badge--nok"><i class="fa fa-ban"></i> Nonaktif &middot; tidak bisa login</span>
                        <?php else: ?><span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> <?php echo genc_e($u['account_status']); ?></span><?php endif; ?></td>
                    <td class="genc-col-actions">
                        <?php if ($can['edit']): ?><a class="genc-btn genc-btn--sm" href="<?php print_link('users/edit/' . (int) $u['id_user']); ?>"><i class="fa fa-pencil"></i> Ubah</a><?php endif; ?>
                        <?php if ($can['delete'] && !$self): ?>
                        <a class="genc-btn genc-btn--sm genc-btn--icon genc-btn--danger" title="Hapus user" aria-label="Hapus user <?php echo genc_e($u['username']); ?>"
                           href="<?php print_link('users/delete/' . (int) $u['id_user'] . '?csrf_token=' . $csrf); ?>"
                           data-genc-confirm="Hapus user <?php echo genc_e($u['username']); ?>?"
                           data-genc-confirm-msg="Akun tidak bisa login lagi. Checklist lama yang diisinya tetap ada. Kalau hanya ingin memblokir sementara, ubah statusnya jadi Nonaktif."
                           data-genc-confirm-ok="Hapus" data-genc-danger><i class="fa fa-trash-o"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <div class="genc-card__foot genc-small genc-muted"><?php echo count($d['rows']); ?> dari <?php echo (int) $d['total']; ?> user</div>
        <?php endif; ?>
    </div>
    <p class="genc-small genc-muted" style="margin-top:12px"><i class="fa fa-info-circle"></i> Username tercatat sebagai pelaksana/approver di checklist. Mengganti username membuat checklist lama tidak lagi terhubung ke akun ini.</p>
</div>
</div>
<?php echo genc_assets_js(); ?>
