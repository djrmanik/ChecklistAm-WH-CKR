<?php
/**
 * USERS - tambah / ubah (Generasi C)                     [GENC-24SEP26-SHELL]
 * Password: wajib untuk user baru; saat ubah, kosongkan = password tetap.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data;
$o = $d['old']; $e = $d['errors']; $is_add = $d['mode'] === 'add';
$action = $is_add ? 'users/add' : 'users/edit/' . (int) $d['user']['id_user'];
$err = function ($k) use ($e) { return isset($e[$k]) ? '<span class="genc-field__err">' . genc_e($e[$k]) . '</span>' : ''; };
$cls = function ($k) use ($e) { return 'genc-field' . (isset($e[$k]) ? ' has-error' : ''); };
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><a href="<?php print_link('users'); ?>">Users</a><span>/</span><span><?php echo $is_add ? 'Tambah' : genc_e($d['user']['username']); ?></span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo $is_add ? 'Tambah user' : 'Ubah user'; ?></h1>
            <p class="genc-subtitle"><?php echo $is_add ? 'Akun baru langsung bisa login dengan username / email dan password di bawah.' : genc_e(ucwords((string) $d['user']['nama'])) . ' &middot; ' . genc_e($d['user']['username']); ?></p>
        </div>
    </div>

    <?php if (!empty($e)): ?>
        <div class="genc-notice genc-notice--danger" style="margin-bottom:16px"><i class="fa fa-exclamation-circle"></i><div><b>Belum tersimpan.</b> Periksa isian yang ditandai merah.<?php echo isset($e['_db']) ? '<br>' . genc_e($e['_db']) : ''; ?></div></div>
    <?php endif; ?>

    <form method="post" action="<?php print_link($action . '?csrf_token=' . Csrf::$token); ?>" class="genc-card" autocomplete="off" novalidate>
        <div class="genc-card__head"><h2 class="genc-card__title">Data akun</h2></div>
        <div class="genc-card__body genc-form">
            <div class="<?php echo $cls('nama'); ?> is-wide">
                <label class="genc-field__label" for="u-nama">Nama lengkap <span class="genc-req">*</span></label>
                <input id="u-nama" class="genc-input" name="nama" maxlength="255" value="<?php echo genc_e($o['nama']); ?>" required>
                <?php echo $err('nama'); ?>
            </div>
            <div class="<?php echo $cls('username'); ?>">
                <label class="genc-field__label" for="u-username">Username <span class="genc-req">*</span></label>
                <input id="u-username" class="genc-input genc-mono" name="username" maxlength="255" value="<?php echo genc_e($o['username']); ?>" required autocapitalize="none" spellcheck="false">
                <span class="genc-field__hint"><?php echo $is_add ? 'Tanpa spasi, contoh nama.belakang. Tercatat sebagai pelaksana di checklist.' : 'Hati-hati: checklist lama tercatat dengan username ini.'; ?></span>
                <?php echo $err('username'); ?>
            </div>
            <div class="<?php echo $cls('email'); ?>">
                <label class="genc-field__label" for="u-email">Email <span class="genc-req">*</span></label>
                <input id="u-email" class="genc-input" type="email" name="email" maxlength="255" value="<?php echo genc_e($o['email']); ?>" required>
                <?php echo $err('email'); ?>
            </div>
            <div class="<?php echo $cls('user_role_id'); ?>">
                <label class="genc-field__label" for="u-role">Role <span class="genc-req">*</span></label>
                <select id="u-role" class="genc-select" name="user_role_id" required<?php echo $d['is_self'] ? ' disabled' : ''; ?>>
                    <option value="">Pilih role...</option>
                    <?php foreach ($d['roles'] as $rid => $r): ?>
                        <option value="<?php echo genc_e($rid); ?>"<?php echo (string) $o['user_role_id'] === (string) $rid ? ' selected' : ''; ?>><?php echo genc_e($r['role_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($d['is_self']): ?><input type="hidden" name="user_role_id" value="<?php echo genc_e($o['user_role_id']); ?>"><span class="genc-field__hint">Role akun sendiri tidak bisa diganti (supaya tidak terkunci).</span><?php else: ?>
                <span class="genc-field__hint">Menentukan menu &amp; mesin yang bisa dibuka.</span><?php endif; ?>
                <?php echo $err('user_role_id'); ?>
            </div>
            <div class="<?php echo $cls('account_status'); ?>">
                <label class="genc-field__label" for="u-status">Status <span class="genc-req">*</span></label>
                <select id="u-status" class="genc-select" name="account_status" required>
                    <?php foreach ($d['statuses'] as $s): ?>
                        <option value="<?php echo genc_e($s); ?>"<?php echo (string) $o['account_status'] === (string) $s ? ' selected' : ''; ?>><?php echo genc_e($s); ?><?php echo UsersController::users_is_blocked($s) ? ' (tidak bisa login)' : ''; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="genc-field__hint"><b>Nonaktif</b> = blokir login tanpa menghapus akun. Status lain (Active, SPV, Manager) = boleh login.</span>
                <?php echo $err('account_status'); ?>
            </div>
        </div>
        <?php /* [GENC-06OKT26-INISIAL] paraf di report & avatar - diatur di sini, bukan di kode */
            $u_name = $is_add ? '' : (string) $d['user']['username'];
            $ini_auto = $u_name !== '' ? checklist_default_initial($u_name) : '';
            $ini_val  = isset($o['inisial']) ? (string) $o['inisial'] : '';
            $ini_show = $ini_val !== '' ? $ini_val : ($ini_auto !== '' ? $ini_auto : '?'); ?>
        <div class="genc-card__head" style="border-top:1px solid var(--gc-border)"><h2 class="genc-card__title">Paraf di checklist &amp; report</h2><span class="genc-small genc-muted">Kosongkan = otomatis</span></div>
        <div class="genc-card__body genc-form">
            <?php if (!empty($d['has_inisial'])): ?>
            <div class="<?php echo $cls('inisial'); ?>">
                <label class="genc-field__label" for="u-ini">Inisial (paraf)</label>
                <div class="gu-ini">
                    <span class="genc-avatar gu-ini__av" id="u-ini-prev" aria-hidden="true"><?php echo genc_e($ini_show); ?></span>
                    <input id="u-ini" class="genc-input genc-mono gu-ini__in" name="inisial" maxlength="3" value="<?php echo genc_e($ini_val); ?>" placeholder="<?php echo genc_e($ini_auto !== '' ? $ini_auto : 'Otomatis'); ?>" autocapitalize="characters" spellcheck="false"
                           oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');document.getElementById('u-ini-prev').textContent=this.value||this.placeholder.replace('Otomatis','?')">
                </div>
                <span class="genc-field__hint">2&ndash;3 huruf/angka, unik (kolom paraf report muat 3 huruf). Tercetak di baris <b>Paraf Pelaksana</b> &amp; <b>Paraf Spv</b> report semua mesin, dan di avatar daftar checklist.<?php echo $ini_auto !== '' ? ' Kosong = <b>' . genc_e($ini_auto) . '</b>.' : ''; ?></span>
                <?php echo $err('inisial'); ?>
            </div>
            <?php else: ?>
            <div class="genc-field">
                <span class="genc-field__label">Inisial (paraf)</span>
                <span class="genc-input gm-readonly genc-mono"><?php echo genc_e($ini_auto !== '' ? $ini_auto : 'otomatis dari username'); ?></span>
                <span class="genc-field__hint">Supaya bisa diatur di sini, jalankan <span class="genc-mono">database/genc_06okt26.sql</span> sekali di phpMyAdmin.</span>
            </div>
            <?php endif; ?>
            <div class="genc-field">
                <span class="genc-field__label">Nama di report &amp; daftar</span>
                <span class="genc-input gm-readonly"><?php echo genc_e($is_add ? 'Dari nama lengkap' : genc_person_name($u_name)); ?></span>
                <span class="genc-field__hint">Orang di daftar resmi SPV memakai nama resmi; user lain memakai <b>Nama lengkap</b> di atas.</span>
            </div>
        </div>
        <div class="genc-card__head" style="border-top:1px solid var(--gc-border)"><h2 class="genc-card__title">Password</h2><?php if (!$is_add): ?><span class="genc-small genc-muted">Kosongkan kalau tidak diganti</span><?php endif; ?></div>
        <div class="genc-card__body genc-form">
            <div class="<?php echo $cls('password'); ?>">
                <label class="genc-field__label" for="u-pass"><?php echo $is_add ? 'Password' : 'Password baru'; ?><?php echo $is_add ? ' <span class="genc-req">*</span>' : ''; ?></label>
                <input id="u-pass" class="genc-input" type="password" name="password" minlength="6" autocomplete="new-password" placeholder="<?php echo $is_add ? 'Minimal 6 karakter' : 'Biarkan kosong = tetap'; ?>">
                <?php echo $err('password'); ?>
            </div>
            <div class="<?php echo $cls('confirm_password'); ?>">
                <label class="genc-field__label" for="u-pass2">Ulangi password</label>
                <input id="u-pass2" class="genc-input" type="password" name="confirm_password" autocomplete="new-password">
                <?php echo $err('confirm_password'); ?>
            </div>
        </div>
        <div class="genc-card__foot genc-formbar">
            <a class="genc-btn" href="<?php print_link('users'); ?>">Batal</a>
            <button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> <?php echo $is_add ? 'Tambah user' : 'Simpan perubahan'; ?></button>
        </div>
    </form>
</div>
</div>
<?php echo genc_assets_js(); ?>
