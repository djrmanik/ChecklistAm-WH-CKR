<?php
/**
 * [GENC-25SEP26-AUDIT] Halaman error gaya Generasi C. Cadangan: _backup_genc_25sept26/errors_error_general_sebelum-audit.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
?>
<?php echo genc_assets_css(); ?>
<div class="genc"><div class="genc-container genc-container--narrow">
    <div class="genc-card" style="margin-top:24px"><div class="genc-empty">
        <div class="genc-empty__icon" style="background:var(--gc-nok-soft);color:var(--gc-nok)"><i class="fa fa-exclamation-circle"></i></div>
        <div class="genc-small genc-muted" style="font-weight:700;letter-spacing:.06em">TIDAK BISA DIPROSES</div>
        <div class="genc-empty__title" style="font-size:20px;margin:4px 0 6px">Permintaan tidak bisa diproses</div>
        <p class="genc-muted" style="max-width:460px;margin:0 auto 18px"><?php echo genc_e(is_string($this->view_data) ? $this->view_data : ''); ?></p>
        
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
            <a class="genc-btn" href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Kembali</a>
            <a class="genc-btn genc-btn--primary" href="<?php print_link(HOME_PAGE); ?>"><i class="fa fa-home"></i> Ke Home</a>
        </div>
    </div></div>
</div></div>
