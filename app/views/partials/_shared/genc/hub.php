<?php
/**
 * GENERASI C - menu "Approval": ringkasan SEMUA MESIN            [GENC-24SEP26-PALLETMOVER] K28
 * [GENC-16OKT26-PANTAU] Data dari PantauController::approval() -> GencPantauReader::approval()
 * (route pantau/approval; dulu palletmover/approval). NOK History punya halaman sendiri (temuan.php).
 * Tiap kartu mesin = PANEL ala Looker: judul tetap, daftar unit bisa digulir, footer "1–N / total".
 * Tiap baris = 1 unit; klik = daftar Gen C mesin itu, sudah tersaring "Menunggu approval" & bulan yang sama.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d       = $this->view_data;
$period  = $d['period'];
$groups  = $d['groups'];
$total   = (int) $d['total'];
$self    = 'pantau/approval';
$n_mesin = 0;
foreach ($groups as $gr) { if ($gr['sum'] > 0) { $n_mesin++; } }

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
        <span>Approval</span>
    </nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Menunggu approval &middot; semua mesin</h1>
            <p class="genc-subtitle">Checklist AM yang belum diparaf SPV / fasilitator &middot; <?php echo genc_e($period['label']); ?></p>
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
            <div class="genc-chips" role="tablist" aria-label="Menu pantau">
                <a class="genc-chip is-active" role="tab" aria-selected="true" href="<?php echo genc_e(genc_url($self, $base)); ?>">Menunggu approval<span class="genc-chip__count"><?php echo $total; ?></span></a>
                <?php if (!empty($d['can_nok'])): ?>
                <a class="genc-chip" role="tab" aria-selected="false" href="<?php echo genc_e(genc_url('pantau/temuan')); ?>" title="NOK History: temuan yang belum beres (semua bulan)">Temuan belum beres<span class="genc-chip__count"><?php echo (int) $d['nok_open']; ?></span></a>
                <?php endif; ?>
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
    <?php else: ?>
        <?php if ($total === 0): ?>
            <div class="genc-notice genc-notice--ok" style="margin-bottom:16px">
                <i class="fa fa-check-circle"></i>
                <div><strong>Semua checklist sudah di-approve</strong> di <?php echo genc_e($period['label']); ?>. Tidak ada yang menunggu paraf.</div>
            </div>
        <?php else: ?>
            <p class="genc-hub__lead"><strong><?php echo $total; ?> checklist</strong> menunggu approval di <?php echo $n_mesin; ?> mesin. Klik unit untuk membuka daftarnya.</p>
        <?php endif; ?>

        <div class="genc-hub genc-panels">
            <?php foreach ($groups as $gr): $gn = (int) $gr['sum'];
                $nu = count($gr['rows']); $nu_hit = 0; foreach ($gr['rows'] as $r) { if ((int) $r['menunggu'] > 0) { $nu_hit++; } } ?>
                <section class="genc-card genc-panel<?php echo $gn > 0 ? ' has-items' : ''; ?>" data-genc-panel>
                    <div class="genc-panel__head">
                        <span class="genc-panel__title"><?php if (!empty($gr['icon'])): ?><i class="fa <?php echo genc_e($gr['icon']); ?> genc-dash__ico" aria-hidden="true"></i><?php endif; ?><span class="genc-panel__name" title="<?php echo genc_e($gr['title']); ?>"><?php echo genc_e($gr['title']); ?></span></span>
                        <?php if ($gn > 0): ?>
                            <span class="genc-badge genc-badge--pending"><?php echo $gn; ?> menunggu</span>
                        <?php else: ?>
                            <span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Beres</span>
                        <?php endif; ?>
                    </div>
                    <div class="genc-panel__body" tabindex="0" aria-label="Daftar unit <?php echo genc_e($gr['title']); ?>">
                        <?php $cur = null; foreach ($gr['rows'] as $r): $n = (int) $r['menunggu'];
                            $q = array_merge($base, array('status' => 'menunggu'));
                            if ($r['unit'] !== '') { $q['unit'] = $r['unit']; }
                            if ($r['group'] !== '' && $r['group'] !== $cur): $cur = $r['group']; ?>
                                <div class="genc-hub__group"><?php echo genc_e($cur); ?></div>
                            <?php endif; ?>
                            <a class="genc-hub__row<?php echo $n > 0 ? ' has-items' : ''; ?>" data-genc-prow href="<?php echo genc_e(genc_url($r['page'], $q)); ?>">
                                <span class="genc-hub__name"><?php echo genc_e($r['label']); ?>
                                    <?php if ($r['sub'] !== '' && $r['sub'] !== $r['label']): ?><small><?php echo genc_e($r['sub']); ?></small><?php endif; ?></span>
                                <?php if ($n > 0): ?>
                                    <span class="genc-badge genc-badge--pending"><?php echo $n; ?></span>
                                <?php else: ?>
                                    <span class="genc-hub__zero">&ndash;</span>
                                <?php endif; ?>
                                <i class="fa fa-angle-right genc-hub__go" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="genc-panel__foot">
                        <span class="genc-panel__range" data-genc-prange><?php echo $nu; ?> unit</span>
                        <span><?php echo $nu_hit ? $nu_hit . ' unit menunggu' : 'tidak ada yang menunggu'; ?></span>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <p class="genc-small genc-muted" style="margin-top:16px"><i class="fa fa-info-circle"></i>
            Angka dihitung dengan aturan yang sama dengan ringkasan di halaman tiap mesin, untuk <?php echo genc_e($period['label']); ?>.
            Approve dilakukan di halaman mesinnya (tombol Approve di daftar atau detail). Mesin yang tidak bisa dibuka akun ini tidak ditampilkan.
        </p>
    <?php endif; ?>

</div>
</div>
<?php echo genc_assets_js(); ?>
