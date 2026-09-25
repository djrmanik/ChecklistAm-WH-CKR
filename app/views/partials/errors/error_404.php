<?php
/**
 * [GENC-25SEP26-AUDIT] Halaman error gaya Generasi C. Cadangan: _backup_genc_25sept26/errors_error_404_sebelum-audit.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
?>
<?php echo genc_assets_css(); ?>
<div class="genc"><div class="genc-container genc-container--narrow">
    <div class="genc-card" style="margin-top:24px"><div class="genc-empty">
        <div class="genc-empty__icon" style=""><i class="fa fa-map-signs"></i></div>
        <div class="genc-small genc-muted" style="font-weight:700;letter-spacing:.06em">404 &middot; TIDAK DITEMUKAN</div>
        <div class="genc-empty__title" style="font-size:20px;margin:4px 0 6px">Halaman tidak ditemukan</div>
        <p class="genc-muted" style="max-width:460px;margin:0 auto 18px">Alamatnya mungkin salah ketik, atau halaman lama yang sudah tidak dipakai.</p>
        <?php if (DEVELOPMENT_MODE && is_string($this->view_data) && $this->view_data !== ''): ?><p class="genc-small genc-muted genc-mono" style="margin:-8px 0 16px"><?php echo genc_e(strip_tags($this->view_data)); ?></p><?php endif; ?>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
            <a class="genc-btn" href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Kembali</a>
            <a class="genc-btn genc-btn--primary" href="<?php print_link(HOME_PAGE); ?>"><i class="fa fa-home"></i> Ke Home</a>
        </div>
    </div></div>
</div></div>
