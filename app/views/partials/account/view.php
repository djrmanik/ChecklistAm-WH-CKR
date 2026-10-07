<?php
/**
 * AKUN SAYA (Generasi C)                                 [GENC-24SEP26-SHELL]
 * Cadangan tampilan lama: _backup_genc_24sept26/account_view_sebelum-shell.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data; $u = $d['user']; $e = $d['errors']; $o = $d['old'];
$val = function ($k) use ($u, $o) { return genc_e(isset($o[$k]) ? $o[$k] : $u[$k]); };
$err = function ($k) use ($e) { return isset($e[$k]) ? '<span class="genc-field__err">' . genc_e($e[$k]) . '</span>' : ''; };
$cls = function ($k) use ($e) { return 'genc-field' . (isset($e[$k]) ? ' has-error' : ''); };
$ini = checklist_user_initial($u['username']);   // [GENC-06OKT26-INISIAL] paraf = avatar (diatur admin di menu Users)
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Akun saya</span></nav>

    <div class="genc-card" style="margin-bottom:16px">
        <div class="genc-card__body" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
            <span class="genc-avatar" style="width:56px;height:56px;font-size:19px" aria-hidden="true"><?php echo genc_e($ini); ?></span>
            <div style="flex:1;min-width:200px">
                <h1 class="genc-title" style="font-size:22px"><?php echo genc_e(ucwords((string) $u['nama'])); ?></h1>
                <p class="genc-subtitle"><span class="genc-mono"><?php echo genc_e($u['username']); ?></span> &middot; <?php echo genc_e($u['email']); ?></p>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                <span class="genc-tag genc-tag--primary"><i class="fa fa-shield"></i> <?php echo genc_e($d['role'] !== '' ? $d['role'] : 'Tanpa role'); ?></span>
                <span class="genc-tag genc-tag--ok"><?php echo genc_e($u['account_status']); ?></span>
                <span class="genc-tag genc-mono" title="Paraf di checklist &amp; report. Diatur admin di menu Users."><i class="fa fa-pencil"></i> Paraf <?php echo genc_e($ini); ?></span>
            </div>
        </div>
        <div class="genc-card__foot genc-small genc-muted"><i class="fa fa-info-circle"></i> Role Anda punya <?php echo (int) $d['n_perm']; ?> hak akses. Username, role, dan status hanya bisa diubah admin lewat menu Users.</div>
    </div>

    <?php if (!empty($e)): ?>
        <div class="genc-notice genc-notice--danger" style="margin-bottom:16px"><i class="fa fa-exclamation-circle"></i><div><b>Belum tersimpan.</b> Periksa isian yang ditandai merah.</div></div>
    <?php endif; ?>

    <form method="post" class="genc-card" style="margin-bottom:16px" action="<?php print_link('account/edit?csrf_token=' . Csrf::$token); ?>">
        <div class="genc-card__head"><h2 class="genc-card__title">Data diri</h2></div>
        <div class="genc-card__body genc-form">
            <div class="<?php echo $cls('nama'); ?>"><label class="genc-field__label" for="a-nama">Nama lengkap</label>
                <input id="a-nama" class="genc-input" name="nama" maxlength="255" value="<?php echo $val('nama'); ?>" required><?php echo $err('nama'); ?></div>
            <div class="<?php echo $cls('email'); ?>"><label class="genc-field__label" for="a-email">Email</label>
                <input id="a-email" class="genc-input" type="email" name="email" maxlength="255" value="<?php echo $val('email'); ?>" required><?php echo $err('email'); ?></div>
        </div>
        <div class="genc-card__foot genc-formbar"><button class="genc-btn genc-btn--primary" type="submit"><i class="fa fa-check"></i> Simpan data diri</button></div>
    </form>

    <form method="post" class="genc-card" action="<?php print_link('account/change_password?csrf_token=' . Csrf::$token); ?>" autocomplete="off">
        <div class="genc-card__head"><h2 class="genc-card__title">Ganti password</h2></div>
        <div class="genc-card__body genc-form">
            <div class="<?php echo $cls('current_password'); ?> is-wide"><label class="genc-field__label" for="a-cur">Password lama</label>
                <input id="a-cur" class="genc-input" type="password" name="current_password" autocomplete="current-password" required style="max-width:380px"><?php echo $err('current_password'); ?></div>
            <div class="<?php echo $cls('new_password'); ?>"><label class="genc-field__label" for="a-new">Password baru</label>
                <input id="a-new" class="genc-input" type="password" name="new_password" minlength="6" autocomplete="new-password" required placeholder="Minimal 6 karakter"><?php echo $err('new_password'); ?></div>
            <div class="<?php echo $cls('confirm_password'); ?>"><label class="genc-field__label" for="a-new2">Ulangi password baru</label>
                <input id="a-new2" class="genc-input" type="password" name="confirm_password" autocomplete="new-password" required><?php echo $err('confirm_password'); ?></div>
        </div>
        <div class="genc-card__foot genc-formbar"><button class="genc-btn genc-btn--primary" type="submit"><i class="fa fa-lock"></i> Ganti password</button></div>
    </form>
</div>
</div>
<?php echo genc_assets_js(); ?>
