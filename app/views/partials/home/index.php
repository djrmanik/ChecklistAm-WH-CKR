<?php
/**
 * HOME / DASHBOARD - Generasi C                          [GENC-24SEP26-SHELL]
 * Menjawab 3 pertanyaan di layar pertama: unit mana yang BELUM diisi hari ini,
 * mana yang ADA TEMUAN, dan berapa yang MENUNGGU APPROVAL bulan ini.
 * Data dari HomeController -> GencHubReader::collect(). Mesin yang role ini tidak boleh buka tidak tampil.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d      = $this->view_data;
$groups = $d['groups'];
$period = $d['period'];

$nama = isset($_SESSION[APP_ID . 'user_data']['nama']) ? trim((string) $_SESSION[APP_ID . 'user_data']['nama']) : '';
$first = $nama !== '' ? ucfirst(strtolower(strtok($nama, ' '))) : ucfirst((string) USER_NAME);
$h = (int) date('G');
$salam = $h < 11 ? 'Selamat pagi' : ($h < 15 ? 'Selamat siang' : ($h < 18 ? 'Selamat sore' : 'Selamat malam'));

$tot = array('units' => 0, 'done' => 0, 'issue' => 0, 'menunggu' => 0, 'tindakan' => 0, 'filled' => 0, 'elapsed' => 0);
foreach ($groups as $gi => $g) {
    $gd = 0; $gf = 0; $ge = 0; $gm = 0; $gt = 0; $gi_issue = 0;
    foreach ($g['rows'] as $r) {
        $tot['units']++;
        if ($r['state'] !== 'none') { $tot['done']++; $gd++; }
        if ($r['state'] === 'issue') { $tot['issue']++; $gi_issue++; }
        $tot['menunggu'] += $r['menunggu']; $tot['tindakan'] += $r['tindakan'];
        $gf += $r['filled']; $ge += $r['elapsed']; $gm += $r['menunggu']; $gt += $r['tindakan'];
    }
    $tot['filled'] += $gf; $tot['elapsed'] += $ge;
    $groups[$gi]['done'] = $gd;
    $groups[$gi]['pct']  = $ge > 0 ? (int) round($gf * 100 / $ge) : 0;
    $groups[$gi]['menunggu'] = $gm;
    $groups[$gi]['tindakan'] = $gt;
    $groups[$gi]['issue'] = $gi_issue;
    $groups[$gi]['filled'] = $gf;
    $groups[$gi]['elapsed'] = $ge;
}
$belum   = $tot['units'] - $tot['done'];
$pct_day = $tot['units'] > 0 ? (int) round($tot['done'] * 100 / $tot['units']) : 0;
$pct_mon = $tot['elapsed'] > 0 ? (int) round($tot['filled'] * 100 / $tot['elapsed']) : 0;
$pct_cls = function ($p) { return $p >= 90 ? 'ok' : ($p >= 60 ? 'pr' : 'nok'); };
// [GENC-08OKT26-P3] "Kelengkapan isi": checklist harian terisi / seharusnya (unit x hari yang sudah lewat bulan ini)
$mon_days = ($tot['units'] > 0 && $tot['elapsed'] % $tot['units'] === 0) ? (int) ($tot['elapsed'] / $tot['units']) : 0;
$mon_rumus = $mon_days > 0 ? ' (' . $tot['units'] . ' unit &times; ' . $mon_days . ' hari)' : '';
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">

    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo genc_e($salam . ', ' . $first); ?></h1>
            <p class="genc-subtitle"><?php echo genc_e(genc_date(time(), true)); ?> &middot; <?php echo genc_e((string) ACL::$user_role); ?></p>
        </div>
        <?php if ($d['can_approval'] || $d['can_nok']): ?>
        <div class="genc-actions">
            <?php if ($d['can_nok']): ?><a class="genc-btn" href="<?php print_link('palletmover/uncompleted'); ?>"><i class="fa fa-exclamation-triangle"></i> NOK History</a><?php endif; ?>
            <?php if ($d['can_approval']): ?><a class="genc-btn genc-btn--primary" href="<?php print_link('palletmover/approval'); ?>"><i class="fa fa-check-square-o"></i> Buka approval</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

<?php if (empty($groups)): ?>
    <div class="genc-card"><div class="genc-empty">
        <div class="genc-empty__icon"><i class="fa fa-lock"></i></div>
        <div class="genc-empty__title">Belum ada mesin yang bisa dibuka akun ini</div>
        <p class="genc-muted">Minta admin memberi hak akses mesin untuk role <b><?php echo genc_e((string) ACL::$user_role); ?></b> (menu Role Permissions).</p>
    </div></div>
<?php else: ?>

    <div class="genc-kpis" style="margin-bottom:20px">
        <div class="genc-card genc-kpi">
            <div class="genc-kpi__label"><i class="fa fa-calendar-check-o"></i> Checklist hari ini</div>
            <div class="genc-kpi__value"><?php echo $tot['done']; ?> <small>dari <?php echo $tot['units']; ?> unit</small></div>
            <div class="genc-meter genc-dash__meter" aria-hidden="true"><span style="width:<?php echo $pct_day; ?>%"></span></div>
            <div class="genc-kpi__hint"><?php echo $belum > 0 ? '<b>' . $belum . ' unit</b> belum diisi' : 'Semua unit sudah diisi &#127881;'; ?></div>
        </div>
        <?php $nok_tag = $d['can_nok'] ? 'a' : 'div';   /* [GENC-08OKT26-P3] klik -> NOK History */ ?>
        <<?php echo $nok_tag; ?> class="genc-card genc-kpi<?php echo $d['can_nok'] ? ' genc-kpi--link' : ''; ?>"<?php echo $d['can_nok'] ? ' href="' . genc_e(set_url('palletmover/uncompleted')) . '"' : ''; ?>>
            <div class="genc-kpi__label"><i class="fa fa-exclamation-circle"></i> Temuan hari ini</div>
            <div class="genc-kpi__value<?php echo $tot['issue'] ? ' genc-dash__v--nok' : ''; ?>"><?php echo $tot['issue']; ?> <small>unit</small></div>
            <div class="genc-kpi__hint"><?php echo $tot['issue'] ? 'Ada item ' . genc_e(genc_pilihan_label('NOK') . ' / ' . genc_pilihan_label('PR')) : 'Tidak ada item bermasalah'; ?><?php echo $d['can_nok'] ? ' &middot; klik untuk NOK History' : ''; ?></div>
        </<?php echo $nok_tag; ?>>
        <?php $wait_tag = $d['can_approval'] ? 'a' : 'div'; ?>
        <<?php echo $wait_tag; ?> class="genc-card genc-kpi<?php echo $d['can_approval'] ? ' genc-kpi--link' : ''; ?>"<?php echo $d['can_approval'] ? ' href="' . genc_e(set_url('palletmover/approval')) . '"' : ''; ?>>
            <div class="genc-kpi__label"><i class="fa fa-clock-o"></i> Menunggu approval</div>
            <div class="genc-kpi__value genc-dash__v--pending"><?php echo $tot['menunggu']; ?> <small>checklist</small></div>
            <div class="genc-kpi__hint"><?php echo genc_e($period['label']); ?><?php echo $d['can_approval'] ? ' &middot; klik untuk approve' : ''; ?></div>
        </<?php echo $wait_tag; ?>>
        <div class="genc-card genc-kpi">
            <div class="genc-kpi__label"><i class="fa fa-line-chart"></i> Kelengkapan isi bulan ini
                <span class="genc-kpi__info" tabindex="0" role="img" aria-label="Penjelasan" title="Setiap unit aktif seharusnya diisi 1 checklist per hari. Persentase = checklist harian yang sudah diisi dibagi yang seharusnya, dari tanggal 1 sampai hari ini (<?php echo genc_e($period['label']); ?>)."><i class="fa fa-info-circle" aria-hidden="true"></i></span></div>
            <div class="genc-kpi__value genc-dash__v--<?php echo $pct_cls($pct_mon); ?>"><?php echo $pct_mon; ?>%</div>
            <div class="genc-kpi__hint"><?php echo $tot['filled']; ?> dari <?php echo $tot['elapsed']; ?> checklist harian sudah diisi<?php echo $mon_rumus; ?> &middot; <?php echo (int) $tot['tindakan']; ?> perlu tindakan</div>
        </div>
    </div>

    <div class="genc-dash__head">
        <h2 class="genc-dash__h">Status hari ini per mesin</h2>
        <div class="genc-legend">
            <span><i class="genc-dash__dot genc-dash__dot--ok"></i> Sudah diisi, semua baik</span>
            <span><i class="genc-dash__dot genc-dash__dot--issue"></i> Ada temuan</span>
            <span><i class="genc-dash__dot genc-dash__dot--none"></i> Belum diisi</span>
        </div>
        <button type="button" class="genc-btn genc-btn--sm genc-btn--ghost genc-fold__all" data-genc-fold-all="home" hidden><i class="fa fa-expand"></i> <span>Buka semua</span></button>
    </div>

    <?php /* [GENC-08OKT26-HUB] kartu mesin sama besar: daftar unit tertutup (ringkasan titik status), klik judul untuk buka / tutup */ ?>
    <div class="genc-hub genc-dash" data-genc-folds="home">
    <?php foreach ($groups as $g):
        $n = count($g['rows']); $fid = 'home-' . substr(md5($g['title']), 0, 8); ?>
        <section class="genc-card genc-hub__card genc-fold" data-genc-fold="<?php echo $fid; ?>">
            <button type="button" class="genc-card__head genc-fold__head" aria-expanded="true" aria-controls="<?php echo $fid; ?>" data-genc-fold-btn>
                <span class="genc-card__title"><i class="fa <?php echo genc_e($g['icon']); ?> genc-dash__ico"></i> <span class="genc-fold__name" title="<?php echo genc_e($g['title']); ?>"><?php echo genc_e($g['title']); ?></span></span>
                <span class="genc-dash__count<?php echo $g['done'] === $n ? ' is-done' : ''; ?>"><?php echo $g['done']; ?>/<?php echo $n; ?> diisi</span>
                <i class="fa fa-chevron-down genc-fold__chev" aria-hidden="true"></i>
            </button>
            <div class="genc-fold__sum" aria-hidden="true">
                <span class="genc-fold__dots"><?php foreach ($g['rows'] as $r): ?><i class="genc-dash__dot genc-dash__dot--<?php echo $r['state']; ?>" title="<?php echo genc_e($r['label']); ?>"></i><?php endforeach; ?></span>
                <span class="genc-fold__txt"><?php echo $n; ?> unit<?php echo $g['issue'] ? ' &middot; <b class="genc-dash__v--nok">' . $g['issue'] . ' ada temuan</b>' : ''; ?><?php echo ($n - $g['done']) > 0 ? ' &middot; ' . ($n - $g['done']) . ' belum diisi' : ''; ?></span>
            </div>
            <div class="genc-fold__body" id="<?php echo $fid; ?>">
            <?php $last_group = null; foreach ($g['rows'] as $r):
                if ($r['group'] !== '' && $r['group'] !== $last_group): $last_group = $r['group']; ?>
                <div class="genc-hub__group"><?php echo genc_e($r['group']); ?></div>
            <?php endif;
                $q = $r['unit'] !== '' ? array('unit' => $r['unit']) : array();
                $href = genc_url($r['page'], $q);
                if ($r['state'] === 'ok')      { $cls = 'ok';    $txt = 'Semua baik'; }
                elseif ($r['state'] === 'issue') { $cls = 'issue'; $txt = empty($r['today_bad']) ? 'Ada temuan' : implode(', ', $r['today_bad']); }
                else                            { $cls = 'none';  $txt = 'Belum diisi'; }
                $who = $r['state'] !== 'none' ? genc_person_name($r['today_by']) : '';
                $jam = $r['state'] !== 'none' ? genc_time($r['today_time']) : ''; ?>
                <div class="genc-dash__row genc-dash__row--<?php echo $cls; ?>">
                    <a class="genc-dash__main" href="<?php echo genc_e($r['today_id'] ? genc_url($r['page'] . '/view/' . $r['today_id']) : $href); ?>">
                        <i class="genc-dash__dot genc-dash__dot--<?php echo $cls; ?>" aria-hidden="true"></i>
                        <span class="genc-dash__unit">
                            <b><?php echo genc_e($r['label']); ?></b>
                            <small><?php echo genc_e($txt); ?><?php echo $who !== '' ? ' &middot; ' . genc_e($who) . ($jam !== '' ? ' ' . genc_e($jam) : '') : ''; ?></small>
                        </span>
                    </a>
                    <?php if ($r['state'] === 'none' && $r['can_add']): ?>
                        <a class="genc-btn genc-btn--sm genc-btn--primary" href="<?php echo genc_e(genc_url($r['page'] . '/add', $q)); ?>" title="Isi checklist <?php echo genc_e($r['label']); ?> hari ini"><i class="fa fa-pencil"></i> Isi</a>
                    <?php else: ?>
                        <a class="genc-dash__go" href="<?php echo genc_e($href); ?>" aria-label="Buka daftar <?php echo genc_e($r['label']); ?>"><i class="fa fa-angle-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="genc-dash__foot">
                <span title="<?php echo (int) $g['filled']; ?> dari <?php echo (int) $g['elapsed']; ?> checklist harian bulan ini sudah diisi">Kelengkapan <b class="genc-dash__v--<?php echo $pct_cls($g['pct']); ?>"><?php echo $g['pct']; ?>%</b></span>
                <?php if ($g['menunggu']): ?><span class="genc-badge genc-badge--pending"><?php echo $g['menunggu']; ?> menunggu</span><?php endif; ?>
                <?php if ($g['tindakan']): ?><span class="genc-badge genc-badge--nok"><?php echo $g['tindakan']; ?> temuan</span><?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
    </div>

    <p class="genc-small genc-muted" style="margin-top:14px"><i class="fa fa-info-circle"></i>
        "Hari ini" = checklist dengan tanggal <?php echo genc_e(genc_date(time())); ?>. Angka bulan ini (<?php echo genc_e($period['label']); ?>) dihitung dengan aturan yang sama dengan ringkasan di halaman tiap mesin.
        Mesin yang tidak bisa dibuka akun ini tidak ditampilkan.</p>
<?php endif; ?>
</div>
</div>
<?php echo genc_assets_js(); ?>
