<?php
/**
 * [GENC-25SEP26-AUDIT] Halaman error gaya Generasi C. Cadangan: _backup_genc_25sept26/errors_error_server_sebelum-audit.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$exception = $this->view_data;
?>
<?php echo genc_assets_css(); ?>
<div class="genc"><div class="genc-container genc-container--narrow">
    <div class="genc-card" style="margin-top:24px"><div class="genc-empty">
        <div class="genc-empty__icon" style="background:var(--gc-nok-soft);color:var(--gc-nok)"><i class="fa fa-bolt"></i></div>
        <div class="genc-small genc-muted" style="font-weight:700;letter-spacing:.06em">500 &middot; ERROR SERVER</div>
        <div class="genc-empty__title" style="font-size:20px;margin:4px 0 6px">Terjadi kesalahan di server</div>
        <p class="genc-muted" style="max-width:460px;margin:0 auto 18px">Data Anda mungkin belum tersimpan. Coba ulangi; kalau terjadi lagi, kirim screenshot halaman ini ke admin.</p>
        <?php if (DEVELOPMENT_MODE && is_object($exception)): ?>
        <details style="text-align:left;margin:0 0 18px"><summary class="genc-small" style="cursor:pointer;color:var(--gc-primary)">Detail teknis (hanya tampil di DEVELOPMENT_MODE)</summary>
            <div class="genc-mono genc-small" style="white-space:pre-wrap;word-break:break-word;background:var(--gc-surface-2);border:1px solid var(--gc-border);border-radius:8px;padding:10px;margin-top:8px;max-height:320px;overflow:auto"><?php
                echo genc_e($exception->getMessage()) . "\n" . genc_e($exception->getFile()) . ':' . (int) $exception->getLine() . "\n\n" . genc_e($exception->getTraceAsString()); ?></div>
        </details>
        <?php endif; ?>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
            <a class="genc-btn" href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Kembali</a>
            <a class="genc-btn genc-btn--primary" href="<?php print_link(HOME_PAGE); ?>"><i class="fa fa-home"></i> Ke Home</a>
        </div>
    </div></div>
</div></div>
