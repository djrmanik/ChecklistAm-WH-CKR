<?php
/**
 * GENERASI C - ringkasan SEMUA MESIN untuk menu "Approval" & "NOK History"
 *                                                  [GENC-24SEP26-PALLETMOVER] K28
 * Data disiapkan PalletmoverController::palletmover_hub() (route palletmover/approval
 * & palletmover/uncompleted - menu tingkat atas yang dulu halaman pallet mover +
 * tombol lintas mesin). Tiap baris = 1 unit; klik = daftar Gen C mesin itu,
 * sudah tersaring status & bulan yang sama.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d       = $this->view_data;
$mode    = $d['mode'];             // 'menunggu' | 'tindakan'
$period  = $d['period'];
$groups  = $d['groups'];
$total   = $d['total'];
$routes  = array('menunggu' => 'palletmover/approval', 'tindakan' => 'palletmover/uncompleted');
$self    = $routes[$mode];
$is_wait = ($mode === 'menunggu');
$badge   = $is_wait ? 'genc-badge--pending' : 'genc-badge--nok';
$n_mesin = 0;
foreach ($groups as $gr) { if ($gr['sum'][$mode] > 0) { $n_mesin++; } }

$nama_bulan = checklist_nama_bulan();
$now_y      = (int) date('Y');
$tahun_awal = min((int) CHECKLIST_PERIODE_TAHUN_AWAL, $period['tahun']);
$base       = array('bulan' => $period['bulan'], 'tahun' => $period['tahun']);
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">

    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <span><?php echo $is_wait ? 'Approval' : 'NOK History'; ?></span>
    </nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo $is_wait ? 'Menunggu approval' : 'Perlu tindakan'; ?> &middot; semua mesin</h1>
            <p class="genc-subtitle"><?php echo $is_wait
                ? 'Checklist AM yang belum diparaf SPV / fasilitator'
                : 'Checklist AM dengan item ' . genc_e(genc_pilihan_label('NOK')) . ' atau ' . genc_e(genc_pilihan_label('PR')); ?> &middot; <?php echo genc_e($period['label']); ?></p>
        </div>
    </div>

    <div class="genc-card" style="margin-bottom:16px">
        <form class="genc-toolbar genc-toolbar--flat" method="get" action="<?php print_link($self); ?>">
            <div class="genc-period" aria-label="Periode">
                <a class="genc-btn genc-btn--icon genc-btn--ghost" title="Bulan sebelumnya" href="<?php echo genc_e(genc_url($self, array('bulan' => $period['prev'][0], 'tahun' => $period['prev'][1]))); ?>"><i class="fa fa-chevron-left"></i></a>
                <select name="bulan" class="genc-select" aria-label="Bulan" onchange="this.form.submit()">
                    <?php foreach ($nama_bulan as $m => $mn): ?>
                        <option value="<?php echo $m; ?>"<?php echo $m === $period['bulan'] ? ' selected' : ''; ?>><?php echo genc_e($mn); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="genc-select" aria-label="Tahun" onchange="this.form.submit()">
                    <?php for ($y = max($now_y, $period['tahun']); $y >= $tahun_awal; $y--): ?>
                        <option value="<?php echo $y; ?>"<?php echo $y === $period['tahun'] ? ' selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <a class="genc-btn genc-btn--icon genc-btn--ghost<?php echo $period['is_current'] ? ' is-disabled' : ''; ?>" title="Bulan berikutnya" href="<?php echo genc_e(genc_url($self, array('bulan' => $period['next'][0], 'tahun' => $period['next'][1]))); ?>"><i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="genc-chips" role="tablist" aria-label="Jenis ringkasan">
                <?php foreach (array('menunggu' => 'Menunggu approval', 'tindakan' => 'Perlu tindakan') as $key => $lbl):
                    if ($mode !== $key && !ACL::is_allowed($routes[$key])): /* [GENC-25SEP26-AUDIT] dulu link ke 403 (operator: NOK History saja) */ ?>
                    <span class="genc-chip" style="opacity:.5;cursor:not-allowed" title="Role Anda tidak punya akses menu ini"><?php echo genc_e($lbl); ?><span class="genc-chip__count"><?php echo (int) $total[$key]; ?></span></span>
                <?php continue; endif; ?>
                    <a class="genc-chip<?php echo $mode === $key ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $mode === $key ? 'true' : 'false'; ?>"
                       href="<?php echo genc_e(genc_url($routes[$key], $base)); ?>"><?php echo genc_e($lbl); ?><span class="genc-chip__count"><?php echo (int) $total[$key]; ?></span></a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <?php $this::display_page_errors(); ?>

    <?php if (empty($groups)): ?>
        <div class="genc-card"><div class="genc-empty">
            <div class="genc-empty__icon"><i class="fa fa-lock"></i></div>
            <div class="genc-empty__title">Tidak ada mesin yang bisa ditampilkan</div>
            <p class="genc-muted">Akun ini belum punya akses ke daftar checklist mesin mana pun. Hubungi admin aplikasi.</p>
        </div></div>
    <?php else:
        $fold_btn = '<button type="button" class="genc-btn genc-btn--sm genc-btn--ghost genc-fold__all" data-genc-fold-all="' . $mode . '" hidden><i class="fa fa-expand"></i> <span>Buka semua</span></button>'; ?>
        <?php if ($total[$mode] === 0): ?>
            <div class="genc-notice genc-notice--ok" style="margin-bottom:16px">
                <i class="fa fa-check-circle"></i>
                <div><strong><?php echo $is_wait ? 'Semua checklist sudah di-approve' : 'Tidak ada temuan'; ?></strong> di <?php echo genc_e($period['label']); ?>.
                    <?php echo $is_wait ? 'Tidak ada yang menunggu paraf.' : 'Semua item di semua mesin tercatat Baik.'; ?></div>
            </div>
        <?php else: ?>
            <div class="genc-hub__bar"><p class="genc-hub__lead"><strong><?php echo (int) $total[$mode]; ?> checklist</strong> <?php echo $is_wait ? 'menunggu approval' : 'perlu tindakan'; ?> di <?php echo $n_mesin; ?> mesin. Klik nama mesin untuk melihat unitnya.</p><?php echo $fold_btn; ?></div>
        <?php endif; ?>
        <?php if ($total[$mode] === 0): ?><div class="genc-hub__bar"><span></span><?php echo $fold_btn; ?></div><?php endif; ?>

        <?php /* [GENC-08OKT26-HUB] kartu mesin sama besar: daftar unit tertutup + ringkasan, klik judul untuk buka / tutup */ ?>
        <div class="genc-hub" data-genc-folds="<?php echo $mode; ?>">
            <?php foreach ($groups as $gr): $gn = (int) $gr['sum'][$mode];
                $fid = $mode . '-' . substr(md5($gr['title']), 0, 8);
                $nu = count($gr['rows']); $nu_hit = 0; foreach ($gr['rows'] as $r) { if ((int) $r[$mode] > 0) { $nu_hit++; } } ?>
                <section class="genc-card genc-hub__card genc-fold<?php echo $gn > 0 ? ' has-items' : ''; ?>" data-genc-fold="<?php echo $fid; ?>">
                    <button type="button" class="genc-card__head genc-fold__head" aria-expanded="true" aria-controls="<?php echo $fid; ?>" data-genc-fold-btn>
                        <span class="genc-card__title"><?php if (!empty($gr['icon'])): ?><i class="fa <?php echo genc_e($gr['icon']); ?> genc-dash__ico" aria-hidden="true"></i> <?php endif; ?><span class="genc-fold__name" title="<?php echo genc_e($gr['title']); ?>"><?php echo genc_e($gr['title']); ?></span></span>
                        <?php if ($gn > 0): ?>
                            <span class="genc-badge <?php echo $badge; ?>"><?php echo $gn; ?> <?php echo $is_wait ? 'menunggu' : 'temuan'; ?></span>
                        <?php else: ?>
                            <span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Beres</span>
                        <?php endif; ?>
                        <i class="fa fa-chevron-down genc-fold__chev" aria-hidden="true"></i>
                    </button>
                    <div class="genc-fold__sum" aria-hidden="true">
                        <span class="genc-fold__txt"><?php echo $nu; ?> unit<?php echo $nu_hit ? ' &middot; <b>' . $nu_hit . ' unit ' . ($is_wait ? 'menunggu approval' : 'perlu tindakan') . '</b>' : ' &middot; tidak ada yang ' . ($is_wait ? 'menunggu' : 'perlu tindakan'); ?></span>
                    </div>
                    <div class="genc-hub__rows genc-fold__body" id="<?php echo $fid; ?>">
                        <?php $cur = null; foreach ($gr['rows'] as $r): $n = (int) $r[$mode];
                            $q = array_merge($base, array('status' => $mode));
                            if ($r['unit'] !== '') { $q['unit'] = $r['unit']; }
                            if ($r['group'] !== '' && $r['group'] !== $cur): $cur = $r['group']; ?>
                                <div class="genc-hub__group"><?php echo genc_e($cur); ?></div>
                            <?php endif; ?>
                            <a class="genc-hub__row<?php echo $n > 0 ? ' has-items' : ''; ?>" href="<?php echo genc_e(genc_url($r['page'], $q)); ?>">
                                <span class="genc-hub__name"><?php echo genc_e($r['label']); ?>
                                    <?php if ($r['sub'] !== '' && $r['sub'] !== $r['label']): ?><small><?php echo genc_e($r['sub']); ?></small><?php endif; ?></span>
                                <?php if ($n > 0): ?>
                                    <span class="genc-badge <?php echo $badge; ?>"><?php echo $n; ?></span>
                                <?php else: ?>
                                    <span class="genc-hub__zero">&ndash;</span>
                                <?php endif; ?>
                                <i class="fa fa-angle-right genc-hub__go" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <p class="genc-small genc-muted" style="margin-top:16px">
            Angka dihitung dengan aturan yang sama dengan ringkasan di halaman tiap mesin, untuk <?php echo genc_e($period['label']); ?>.
            <?php echo $is_wait ? 'Approve dilakukan di halaman mesinnya (tombol Approve di daftar atau detail).' : '&ldquo;Perlu tindakan&rdquo; = ada item ' . genc_e(genc_pilihan_label('NOK')) . ' atau ' . genc_e(genc_pilihan_label('PR')) . ', sudah di-approve atau belum.'; ?>
            Mesin yang tidak bisa dibuka akun ini tidak ditampilkan.
        </p>
    <?php endif; ?>

</div>
</div>
<?php echo genc_assets_js(); ?>
