<?php
/**
 * DAFTAR AKUN - Generasi C                               [GENC-24SEP26-SHELL]
 * Role TIDAK bisa dipilih lagi: IndexController::register() selalu memberi role Operator/Staff
 * (dulu siapa pun bisa daftar sebagai Administrator/Developer). Role lain diatur admin di menu Users.
 * Cadangan: _backup_genc_24sept26/index_register_sebelum-shell.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$gs_err = $this->page_error;
if (!is_array($gs_err)) { $gs_err = trim((string) $gs_err) === '' ? array() : array($gs_err); }
$v = function ($k) { return isset($_POST[$k]) ? genc_e($_POST[$k]) : ''; };
?>
<div class="gs-login">
    <section class="gs-login__art" aria-hidden="true">
        <div class="gs-login__brand">
            <span class="gs-mark"><i class="fa fa-check-square-o"></i></span>
            <div>Checklist AM<small>Warehouse CKR &middot; PT Bintang Toedjoe</small></div>
        </div>
        <div class="gs-login__hero">
            <h1>Akun operator<br>untuk isi checklist harian.</h1>
            <p>Akun baru otomatis berperan <b>Operator/Staff</b>: bisa mengisi dan melihat checklist mesin. Hak SPV, approval, atau admin diberikan oleh admin warehouse lewat menu Users.</p>
        </div>
        <div class="gs-login__legal">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></div>
    </section>
    <section class="gs-login__panel">
        <div class="gs-login__card" style="max-width:420px">
            <div class="gs-login__mobile-brand"><span class="gs-mark" aria-hidden="true"><i class="fa fa-check-square-o"></i></span> Checklist AM &middot; Warehouse CKR</div>
            <h2>Daftar akun</h2>
            <p class="gs-lead">Isi data diri. Username dipakai sebagai nama pelaksana di checklist.</p>
            <?php if (!empty($gs_err)): ?>
                <div class="gs-alert" role="alert"><i class="fa fa-exclamation-circle" style="margin-top:2px"></i><div><?php foreach ($gs_err as $e) { echo '<div>' . $e . '</div>'; } ?></div></div>
            <?php endif; ?>
            <form action="<?php print_link('index/register?csrf_token=' . Csrf::$token); ?>" method="post" data-gs-auth>
                <div class="gs-field"><label for="r-nama">Nama lengkap</label>
                    <input id="r-nama" class="gs-input gs-input--plain" name="nama" required maxlength="255" value="<?php echo $v('nama'); ?>" placeholder="mis. Tri Istianto" autocomplete="name"></div>
                <div class="gs-field"><label for="r-username">Username</label>
                    <input id="r-username" class="gs-input gs-input--plain" name="username" required maxlength="255" value="<?php echo $v('username'); ?>" placeholder="mis. tri.istianto" autocapitalize="none" spellcheck="false" autocomplete="username">
                    <div class="gs-help">Huruf kecil tanpa spasi, contoh <b>nama.belakang</b>.</div></div>
                <div class="gs-field"><label for="r-email">Email</label>
                    <input id="r-email" class="gs-input gs-input--plain" name="email" type="email" required maxlength="255" value="<?php echo $v('email'); ?>" placeholder="nama@warehousecikarang.com" autocomplete="email"></div>
                <div class="gs-field"><label for="r-pass">Password</label>
                    <div class="gs-input-wrap"><input id="r-pass" class="gs-input gs-input--plain" name="password" type="password" required minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter">
                    <button type="button" class="gs-eye" data-gs-eye="r-pass" aria-label="Tampilkan password"><i class="fa fa-eye"></i></button></div></div>
                <div class="gs-field"><label for="r-pass2">Ulangi password</label>
                    <input id="r-pass2" class="gs-input gs-input--plain" name="confirm_password" type="password" required minlength="6" autocomplete="new-password" placeholder="Ketik ulang password"></div>
                <button class="gs-submit" type="submit" data-gs-busy="Mendaftarkan...">Daftar &amp; masuk <i class="fa fa-arrow-right"></i></button>
            </form>
            <div class="gs-login__alt">Sudah punya akun? <a href="<?php print_link(''); ?>">Masuk</a></div>
        </div>
    </section>
</div>
