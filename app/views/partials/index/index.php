<?php
/**
 * HALAMAN LOGIN - Generasi C                             [GENC-24SEP26-SHELL]
 * Dipakai index/index (buka app) dan index/login (setelah login gagal).
 * Form & field sama dengan phpRAD (username, password, rememberme, csrf) -> IndexController tidak berubah cara kerjanya.
 * "Reset Password?" dihapus: butuh email SMTP yang tidak disetel di config.php (link lama selalu gagal).
 * Cadangan: _backup_genc_24sept26/index_index_sebelum-shell.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$gs_err = $this->page_error;
if (is_array($gs_err)) { $gs_err = implode(' ', $gs_err); }
$gs_err = trim((string) $gs_err);
$gs_map = array(
    'Username or password not correct' => 'Username / email atau password salah.',
    'Invalid request'                  => 'Sesi login kedaluwarsa. Silakan coba lagi.',
);
if (isset($gs_map[$gs_err])) { $gs_err = $gs_map[$gs_err]; }
$gs_user = isset($_POST['username']) ? (string) $_POST['username'] : '';
$gs_pesan = isset($_GET['pesan']) ? (string) $_GET['pesan'] : '';
$gs_notice = $gs_pesan === 'keluar' ? 'Anda sudah keluar.' : ($gs_pesan === 'lupa' ? 'Reset password lewat email belum aktif. Hubungi admin / SPV warehouse untuk dibuatkan password baru.' : '');   // [GENC-25SEP26-AUDIT] + lupa
?>
<div class="gs-login">
    <section class="gs-login__art" aria-hidden="true">
        <div class="gs-login__brand">
            <span class="gs-mark"><i class="fa fa-check-square-o"></i></span>
            <div>Checklist AM<small>Warehouse CKR &middot; PT Bintang Toedjoe</small></div>
        </div>
        <div class="gs-login__hero">
            <h1>Cek mesin pagi ini,<br>beres sebelum shift jalan.</h1>
            <p>Checklist Autonomous Maintenance harian untuk semua alat &amp; mesin Warehouse Cikarang &mdash; dari isi, temuan, sampai approval SPV.</p>
            <ul class="gs-login__points">
                <li><i class="fa fa-hand-pointer-o"></i><div><b>Satu tap per item</b><span>Baik, Tidak baik, atau Perawatan &mdash; lengkap dengan cara cek & foto part.</span></div></li>
                <li><i class="fa fa-exclamation-triangle"></i><div><b>Temuan langsung kelihatan</b><span>Item bermasalah terkumpul di NOK History untuk ditindaklanjuti.</span></div></li>
                <li><i class="fa fa-check-square-o"></i><div><b>Approval dalam satu layar</b><span>SPV melihat antrean semua mesin sekaligus.</span></div></li>
            </ul>
            <div class="gs-login__strip"><i></i><i></i><i></i><i></i><i class="x"></i><i></i><i></i><i></i><i class="p"></i><i class="p"></i></div>
        </div>
        <div class="gs-login__legal">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></div>
    </section>

    <section class="gs-login__panel">
        <div class="gs-login__card">
            <div class="gs-login__mobile-brand"><span class="gs-mark" aria-hidden="true"><i class="fa fa-check-square-o"></i></span> Checklist AM &middot; Warehouse CKR</div>
            <h2>Masuk</h2>
            <p class="gs-lead">Gunakan akun yang diberikan admin warehouse.</p>

            <?php if ($gs_err !== ''): ?>
                <div class="gs-alert" role="alert"><i class="fa fa-exclamation-circle" style="margin-top:2px"></i><div><?php echo genc_e($gs_err); ?></div></div>
            <?php elseif ($gs_notice !== ''): ?>
                <div class="gs-alert gs-alert--info" role="status"><i class="fa fa-check-circle" style="margin-top:2px"></i><div><?php echo genc_e($gs_notice); ?></div></div>
            <?php endif; ?>

            <form name="loginForm" action="<?php print_link('index/login/?csrf_token=' . Csrf::$token); ?>" method="post" data-gs-auth novalidate>
                <div class="gs-field">
                    <label for="gs-username">Username atau email</label>
                    <div class="gs-input-wrap">
                        <i class="fa fa-user"></i>
                        <input id="gs-username" class="gs-input" name="username" type="text" required autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="mis. tri.istianto" value="<?php echo genc_e($gs_user); ?>"<?php echo $gs_user === '' ? ' autofocus' : ''; ?>>
                    </div>
                </div>
                <div class="gs-field">
                    <label for="gs-password">Password</label>
                    <div class="gs-input-wrap">
                        <i class="fa fa-lock"></i>
                        <input id="gs-password" class="gs-input" name="password" type="password" required autocomplete="current-password" placeholder="Password"<?php echo $gs_user !== '' ? ' autofocus' : ''; ?>>
                        <button type="button" class="gs-eye" data-gs-eye="gs-password" aria-label="Tampilkan password"><i class="fa fa-eye"></i></button>
                    </div>
                </div>
                <div class="gs-login__row">
                    <label class="gs-check"><input type="checkbox" name="rememberme" value="true"> Ingat saya di perangkat ini</label>
                </div>
                <button class="gs-submit" type="submit" data-gs-busy="Masuk...">Masuk <i class="fa fa-arrow-right"></i></button>
                <p class="gs-help" style="margin-top:12px"><i class="fa fa-info-circle"></i> Lupa password? Hubungi admin / SPV warehouse untuk dibuatkan password baru.</p>
            </form>

            <div class="gs-login__alt">Belum punya akun? <a href="<?php print_link('index/register'); ?>">Daftar sebagai operator</a></div>
        </div>
    </section>
</div>
