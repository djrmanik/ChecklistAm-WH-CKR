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
    $gd = 0; $gf = 0; $ge = 0; $gm = 0; $gt = 0;
    foreach ($g['rows'] as $r) {
        $tot['units']++;
        if ($r['state'] !== 'none') { $tot['done']++; $gd++; }
        if ($r['state'] === 'issue') { $tot['issue']++; }
        $tot['menunggu'] += $r['menunggu']; $tot['tindakan'] += $r['tindakan'];
        $gf += $r['filled']; $ge += $r['elapsed']; $gm += $r['menunggu']; $gt += $r['tindakan'];
    }
    $tot['filled'] += $gf; $tot['elapsed'] += $ge;
    $groups[$gi]['done'] = $gd;
    $groups[$gi]['pct']  = $ge > 0 ? (int) round($gf * 100 / $ge) : 0;
    $groups[$gi]['menunggu'] = $gm;
    $groups[$gi]['tindakan'] = $gt;
}
$belum   = $tot['units'] - $tot['done'];
$pct_day = $tot['units'] > 0 ? (int) round($tot['done'] * 100 / $tot['units']) : 0;
$pct_mon = $tot['elapsed'] > 0 ? (int) round($tot['filled'] * 100 / $tot['elapsed']) : 0;
$pct_cls = function ($p) { return $p >= 90 ? 'ok' : ($p >= 60 ? 'pr' : 'nok'); };
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
        <div class="genc-card genc-kpi">
            <div class="genc-kpi__label"><i class="fa fa-exclamation-circle"></i> Temuan hari ini</div>
            <div class="genc-kpi__value<?php echo $tot['issue'] ? ' genc-dash__v--nok' : ''; ?>"><?php echo $tot['issue']; ?> <small>unit</small></div>
            <div class="genc-kpi__hint"><?php echo $tot['issue'] ? 'Ada item Tidak baik / Perawatan' : 'Tidak ada item bermasalah'; ?></div>
        </div>
        <?php $wait_tag = $d['can_approval'] ? 'a' : 'div'; ?>
        <<?php echo $wait_tag; ?> class="genc-card genc-kpi<?php echo $d['can_approval'] ? ' genc-kpi--link' : ''; ?>"<?php echo $d['can_approval'] ? ' href="' . genc_e(set_url('palletmover/approval')) . '"' : ''; ?>>
            <div class="genc-kpi__label"><i class="fa fa-clock-o"></i> Menunggu approval</div>
            <div class="genc-kpi__value genc-dash__v--pending"><?php echo $tot['menunggu']; ?> <small>checklist</small></div>
            <div class="genc-kpi__hint"><?php echo genc_e($period['label']); ?><?php echo $d['can_approval'] ? ' &middot; klik untuk approve' : ''; ?></div>
        </<?php echo $wait_tag; ?>>
        <div class="genc-card genc-kpi">
            <div class="genc-kpi__label"><i class="fa fa-line-chart"></i> Kepatuhan isi bulan ini</div>
            <div class="genc-kpi__value genc-dash__v--<?php echo $pct_cls($pct_mon); ?>"><?php echo $pct_mon; ?>%</div>
            <div class="genc-kpi__hint"><?php echo $tot['filled']; ?> dari <?php echo $tot['elapsed']; ?> hari-unit terisi &middot; <?php echo (int) $tot['tindakan']; ?> perlu tindakan</div>
        </div>
    </div>

    <div class="genc-dash__head">
        <h2 class="genc-dash__h">Status hari ini per mesin</h2>
        <div class="genc-legend">
            <span><i class="genc-dash__dot genc-dash__dot--ok"></i> Sudah diisi, semua baik</span>
            <span><i class="genc-dash__dot genc-dash__dot--issue"></i> Ada temuan</span>
            <span><i class="genc-dash__dot genc-dash__dot--none"></i> Belum diisi</span>
        </div>
    </div>

    <div class="genc-hub genc-dash">
    <?php foreach ($groups as $g):
        $n = count($g['rows']); ?>
        <section class="genc-card genc-hub__card">
            <div class="genc-card__head">
                <h3 class="genc-card__title"><i class="fa <?php echo $g['icon']; ?> genc-dash__ico"></i> <?php echo genc_e($g['title']); ?></h3>
                <span class="genc-dash__count<?php echo $g['done'] === $n ? ' is-done' : ''; ?>"><?php echo $g['done']; ?>/<?php echo $n; ?> diisi</span>
            </div>
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
            <div class="genc-dash__foot">
                <span title="Hari terisi bulan ini">Isi bulan ini <b class="genc-dash__v--<?php echo $pct_cls($g['pct']); ?>"><?php echo $g['pct']; ?>%</b></span>
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
