<?php
/**
 * ROLES - kartu per role + form tambah/ubah di atas (Generasi C)   [GENC-24SEP26-SHELL]
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data; $roles = $d['roles']; $can = $d['can']; $f = $d['form'];
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Roles</span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Roles</h1>
            <p class="genc-subtitle">Kelompok hak akses. Tiap user punya satu role; isi hak akses role diatur di Role Permissions.</p>
        </div>
        <?php if ($can['add'] && !$f): ?><div class="genc-actions"><a class="genc-btn genc-btn--primary" href="<?php print_link('roles/add'); ?>"><i class="fa fa-plus"></i> Tambah role</a></div><?php endif; ?>
    </div>

    <?php if ($f): $is_add = $f['mode'] === 'add'; ?>
    <form method="post" class="genc-card" style="margin-bottom:16px" action="<?php print_link(($is_add ? 'roles/add' : 'roles/edit/' . (int) $f['id']) . '?csrf_token=' . Csrf::$token); ?>">
        <div class="genc-card__head"><h2 class="genc-card__title"><?php echo $is_add ? 'Tambah role' : 'Ganti nama role'; ?></h2></div>
        <div class="genc-card__body genc-form">
            <div class="genc-field<?php echo $f['err'] !== '' ? ' has-error' : ''; ?>">
                <label class="genc-field__label" for="r-name">Nama role <span class="genc-req">*</span></label>
                <input id="r-name" class="genc-input" name="role_name" maxlength="255" value="<?php echo genc_e($f['old']['role_name']); ?>" required autofocus placeholder="mis. SPV Shift 2">
                <?php if ($f['err'] !== ''): ?><span class="genc-field__err"><?php echo genc_e($f['err']); ?></span><?php endif; ?>
            </div>
            <?php if ($is_add): ?>
            <div class="genc-field">
                <label class="genc-field__label" for="r-copy">Salin hak akses dari</label>
                <select id="r-copy" class="genc-select" name="copy_from">
                    <option value="">Tidak (mulai kosong)</option>
                    <?php foreach ($roles as $rid => $r): ?><option value="<?php echo genc_e($rid); ?>"<?php echo $f['old']['copy_from'] === (string) $rid ? ' selected' : ''; ?>><?php echo genc_e($r['role_name']); ?> (<?php echo (int) $r['n_perm']; ?> hak akses)</option><?php endforeach; ?>
                </select>
                <span class="genc-field__hint">Setelah dibuat, Anda langsung dibawa ke matriks hak aksesnya.</span>
            </div>
            <?php endif; ?>
        </div>
        <div class="genc-card__foot genc-formbar">
            <a class="genc-btn" href="<?php print_link('roles'); ?>">Batal</a>
            <button class="genc-btn genc-btn--primary" type="submit"><i class="fa fa-check"></i> <?php echo $is_add ? 'Buat role' : 'Simpan'; ?></button>
        </div>
    </form>
    <?php endif; ?>

    <div class="genc-roles">
    <?php foreach ($roles as $rid => $r): $own = (string) $rid === (string) USER_ROLE; ?>
        <section class="genc-card">
            <div class="genc-card__body">
                <div class="genc-row" style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start">
                    <div>
                        <div class="genc-role__name"><?php echo genc_e($r['role_name']); ?></div>
                        <span class="genc-small genc-muted">ID <?php echo genc_e($rid); ?><?php echo $own ? ' &middot; role Anda' : ''; ?></span>
                    </div>
                    <div style="display:flex;gap:4px">
                        <?php if ($can['edit']): ?><a class="genc-btn genc-btn--sm genc-btn--icon genc-btn--ghost" title="Ganti nama" aria-label="Ganti nama role <?php echo genc_e($r['role_name']); ?>" href="<?php print_link('roles/edit/' . (int) $rid); ?>"><i class="fa fa-pencil"></i></a><?php endif; ?>
                        <?php if ($can['delete'] && !$own): ?>
                            <?php if ((int) $r['n_user'] > 0): ?>
                                <span class="genc-btn genc-btn--sm genc-btn--icon genc-btn--ghost is-disabled" title="Masih dipakai <?php echo (int) $r['n_user']; ?> user"><i class="fa fa-trash-o"></i></span>
                            <?php else: ?>
                                <a class="genc-btn genc-btn--sm genc-btn--icon genc-btn--danger" title="Hapus role" aria-label="Hapus role <?php echo genc_e($r['role_name']); ?>" href="<?php print_link('roles/delete/' . (int) $rid . '?csrf_token=' . Csrf::$token); ?>"
                                   data-genc-confirm="Hapus role <?php echo genc_e($r['role_name']); ?>?" data-genc-confirm-msg="<?php echo (int) $r['n_perm']; ?> hak akses role ini ikut dihapus. Tidak ada user yang memakai role ini." data-genc-confirm-ok="Hapus" data-genc-danger><i class="fa fa-trash-o"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="genc-role__stats">
                    <div><b><?php echo (int) $r['n_user']; ?></b><span>user</span></div>
                    <div><b><?php echo (int) $r['n_perm']; ?></b><span>hak akses</span></div>
                    <div><b><?php echo (int) $r['n_add']; ?></b><span>halaman bisa isi</span></div>
                    <div><b><?php echo (int) $r['n_approve']; ?></b><span>mesin bisa approve</span></div>
                </div>
            </div>
            <div class="genc-card__foot genc-role__links">
                <?php if ($can['perm']): ?><a class="genc-btn genc-btn--sm" href="<?php echo genc_e(genc_url('role_permissions', array('role' => $rid))); ?>"><i class="fa fa-key"></i> Hak akses</a><?php endif; ?>
                <?php if ($can['users']): ?><a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php echo genc_e(genc_url('users', array('role' => $rid))); ?>"><i class="fa fa-users"></i> Lihat user</a><?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
    </div>
</div>
</div>
<?php echo genc_assets_js(); ?>
