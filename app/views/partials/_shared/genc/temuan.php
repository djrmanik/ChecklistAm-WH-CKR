<?php
/**
 * GENERASI C - menu "NOK History": TEMUAN semua mesin + statusnya   [GENC-15OKT26-TEMUAN] K111-K113
 * [GENC-16OKT26-PANTAU] Data dari PantauController::temuan() -> GencPantauReader::temuan() (route pantau/temuan).
 *
 * 1 temuan = 1 item di 1 unit yang dinilai NOK/PR (satu atau beberapa checklist berturut-turut).
 * Status dihitung, tidak disimpan: "beres" begitu checklist berikutnya untuk unit itu menilai item
 * tersebut Baik. Checklist lama & report TIDAK berubah.
 *
 * [16 Okt] 2 tab (dulu 3: "Sudah beres" & "Riwayat" terasa sama):
 *   buka    = Belum beres, semua bulan, per hari ini
 *   riwayat = temuan yang muncul di bulan terpilih, disaring f = semua | beres | belum
 * Kartu mesin = PANEL ala Looker (judul tetap, isi digulir). Isi panel = PER UNIT (bukan per item):
 * klik unit -> pop-up berisi daftar item (bisa diperbesar layar penuh, Esc = tutup).
 * Tanpa JS: ?buka=<kunci unit> membuka pop-up unit itu dari server.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d      = $this->view_data;
$tab    = $d['tab'];
$fil    = $d['f'];
$buka   = $d['buka'];
$period = $d['period'];
$groups = $d['groups'];
$total  = $d['total'];
$self   = 'pantau/temuan';
$base   = array('bulan' => $period['bulan'], 'tahun' => $period['tahun']);
$is_open = ($tab === 'buka');

$nama_bulan = checklist_nama_bulan();
$now_y      = (int) date('Y');
$tahun_awal = min((int) CHECKLIST_PERIODE_TAHUN_AWAL, $period['tahun']);
$ok_lbl     = genc_pilihan_label('OK');
$nok_pr     = genc_pilihan_label('NOK') . ' / ' . genc_pilihan_label('PR');

$val_badge = function ($v) {
    $m = genc_value_meta($v);
    return '<span class="genc-badge genc-badge--' . $m['key'] . ' genc-tm__val">' . genc_e($m['label']) . '</span>';
};
/** Item yang tampil di tab / saringan ini. */
$pick = function ($items) use ($tab, $fil) {
    $out = array();
    foreach ($items as $e) {
        if ($tab === 'buka') { if ($e['open']) { $out[] = $e; } continue; }
        if (!$e['in_riwayat']) { continue; }
        if (($fil === 'beres' && $e['open']) || ($fil === 'belum' && !$e['open'])) { continue; }
        $out[] = $e;
    }
    usort($out, function ($a, $b) use ($tab) {
        /* belum beres = terlama dulu; riwayat = terbaru dulu */
        $x = $tab === 'buka' ? strcmp($a['start_date'], $b['start_date']) : strcmp($b['start_date'], $a['start_date']);
        return $x !== 0 ? $x : strcmp($a['part'], $b['part']);
    });
    return $out;
};

$avg     = $total['riwayat_beres'] > 0 ? round($total['hari'] / $total['riwayat_beres'], 1) : null;
$avg_txt = $avg === null ? '' : str_replace('.', ',', (string) $avg);
$kpi = array(
    'buka'    => array('icon' => 'fa-clock-o', 'label' => 'Belum beres', 'value' => $total['buka'],
                       'hint' => $total['buka'] ? 'di ' . $total['unit_buka'] . ' unit &middot; terlama ' . genc_e(genc_tm_lama($total['tertua'], true)) : 'Semua temuan sudah beres',
                       'q' => array('tab' => 'buka')),
    'riwayat' => array('icon' => 'fa-history', 'label' => 'Riwayat temuan<span class="genc-tm__kper"> &middot; ' . genc_e($period['label']) . '</span>', 'value' => $total['riwayat'],
                       'hint' => $total['riwayat'] ? (int) $total['riwayat_beres'] . ' sudah beres &middot; ' . (int) $total['riwayat_belum'] . ' belum' . ($avg !== null ? ' &middot; rata-rata ' . genc_e($avg_txt) . ' hari sampai ' . genc_e($ok_lbl) : '') : 'Tidak ada temuan baru di bulan ini',
                       'q' => array('tab' => 'riwayat')),
);
$sub = $is_open
    ? 'Item ' . genc_e($nok_pr) . ' yang di checklist terakhir unitnya masih belum ' . genc_e($ok_lbl) . ' &middot; semua bulan, per hari ini'
    : 'Semua temuan yang muncul di ' . genc_e($period['label']) . ', sudah beres atau belum';
$fil_lbl = array('semua' => 'Semua', 'beres' => 'Sudah beres', 'belum' => 'Belum beres');
$fil_n   = array('semua' => $total['riwayat'], 'beres' => $total['riwayat_beres'], 'belum' => $total['riwayat_belum']);
$n_show  = $is_open ? $total['buka'] : $fil_n[$fil];
$q_tab   = array_merge($base, $is_open ? array('tab' => 'buka') : array('tab' => 'riwayat', 'f' => $fil === 'semua' ? '' : $fil));
$modals  = array();
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">

    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <span>NOK History</span>
    </nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Temuan &middot; semua mesin</h1>
            <p class="genc-subtitle"><?php echo $sub; ?></p>
        </div>
        <?php if (!empty($d['can_approval'])): ?>
        <div class="genc-actions"><a class="genc-btn" href="<?php print_link('pantau/approval'); ?>"><i class="fa fa-check-square-o"></i> Approval</a></div>
        <?php endif; ?>
    </div>

    <div class="genc-card" style="margin-bottom:16px">
        <form class="genc-toolbar genc-toolbar--flat" method="get" action="<?php print_link($self); ?>">
            <input type="hidden" name="tab" value="<?php echo genc_e($tab); ?>">
            <?php if (!$is_open && $fil !== 'semua'): ?><input type="hidden" name="f" value="<?php echo genc_e($fil); ?>"><?php endif; ?>
            <div class="genc-period" aria-label="Periode">
                <a class="genc-btn genc-btn--icon genc-btn--ghost" title="Bulan sebelumnya" href="<?php echo genc_e(genc_url($self, array_merge($q_tab, array('bulan' => $period['prev'][0], 'tahun' => $period['prev'][1])))); ?>"><i class="fa fa-chevron-left"></i></a>
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
                <a class="genc-btn genc-btn--icon genc-btn--ghost<?php echo $period['is_current'] ? ' is-disabled' : ''; ?>" title="Bulan berikutnya" href="<?php echo genc_e(genc_url($self, array_merge($q_tab, array('bulan' => $period['next'][0], 'tahun' => $period['next'][1])))); ?>"><i class="fa fa-chevron-right"></i></a>
            </div>
            <span class="genc-tm__per genc-small genc-muted"><?php echo $is_open ? 'Belum beres tidak tergantung bulan' : 'Bulan berlaku untuk Riwayat temuan'; ?></span>
        </form>
    </div>

    <?php $this::display_page_errors(); ?>

    <div class="genc-kpis genc-tm__kpis" role="tablist" aria-label="Status temuan">
        <?php foreach ($kpi as $key => $k): $on = ($key === $tab); ?>
            <a class="genc-card genc-kpi genc-kpi--link genc-tm__kpi genc-tm__kpi--<?php echo $key; ?><?php echo $on ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $on ? 'true' : 'false'; ?>"
               href="<?php echo genc_e(genc_url($self, array_merge($base, $k['q']))); ?>">
                <span class="genc-kpi__label"><i class="fa <?php echo $k['icon']; ?>"></i> <?php echo $k['label']; ?></span>
                <span class="genc-kpi__value"><?php echo (int) $k['value']; ?> <small>temuan</small></span>
                <span class="genc-kpi__hint"><?php echo $k['hint']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($groups)): ?>
        <div class="genc-card"><div class="genc-empty">
            <div class="genc-empty__icon"><i class="fa fa-lock"></i></div>
            <div class="genc-empty__title">Tidak ada mesin yang bisa ditampilkan</div>
            <p class="genc-muted">Akun ini belum punya akses ke daftar checklist mesin mana pun. Hubungi admin aplikasi.</p>
        </div></div>
    <?php else: ?>
        <div class="genc-hub__bar">
            <?php if ($n_show === 0): ?>
                <div class="genc-notice <?php echo $is_open || $fil === 'semua' ? 'genc-notice--ok' : ''; ?> genc-tm__notice">
                    <i class="fa <?php echo $is_open || $fil === 'semua' ? 'fa-check-circle' : 'fa-info-circle'; ?>"></i>
                    <div><?php if ($is_open): ?><strong>Semua temuan sudah beres.</strong> Di checklist terakhir tiap unit, semua item tercatat <?php echo genc_e($ok_lbl); ?>.
                        <?php elseif ($fil === 'semua'): ?><strong>Tidak ada temuan</strong> di <?php echo genc_e($period['label']); ?>. Semua item di semua mesin tercatat <?php echo genc_e($ok_lbl); ?>.
                        <?php else: ?><strong>Tidak ada temuan yang <?php echo $fil === 'beres' ? 'sudah beres' : 'belum beres'; ?></strong> dari <?php echo genc_e($period['label']); ?>.<?php endif; ?></div>
                </div>
            <?php else: ?>
                <p class="genc-hub__lead"><?php if ($is_open): ?><strong><?php echo (int) $n_show; ?> temuan</strong> belum beres di <?php echo (int) $total['unit_buka']; ?> unit.<?php else: ?><strong><?php echo (int) $n_show; ?> temuan</strong> <?php echo $fil === 'beres' ? 'sudah beres' : ($fil === 'belum' ? 'masih belum beres' : 'muncul'); ?> dari <?php echo genc_e($period['label']); ?>.<?php endif; ?> Klik unit untuk melihat item yang bermasalah.</p>
            <?php endif; ?>
            <?php if (!$is_open): ?>
            <div class="genc-chips" role="tablist" aria-label="Saring riwayat">
                <?php foreach ($fil_lbl as $fk => $fl): ?>
                    <a class="genc-chip<?php echo $fil === $fk ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $fil === $fk ? 'true' : 'false'; ?>"
                       href="<?php echo genc_e(genc_url($self, array_merge($base, array('tab' => 'riwayat', 'f' => $fk === 'semua' ? '' : $fk)))); ?>"><?php echo genc_e($fl); ?><span class="genc-chip__count"><?php echo (int) $fil_n[$fk]; ?></span></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="genc-hub genc-panels genc-tm">
            <?php foreach ($groups as $gr):
                $rows = array(); $gn = 0;
                foreach ($gr['units'] as $u) {
                    $list = $pick($u['items']);
                    if (!$list) { continue; }
                    $u['list'] = $list; $u['n'] = count($list); $u['mtitle'] = $gr['title'];
                    $u['n_open'] = 0; $u['n_done'] = 0; $u['tertua'] = 0; $u['sejak'] = null;
                    foreach ($list as $e) {
                        if ($e['open']) { $u['n_open']++; if ($e['days'] > $u['tertua']) { $u['tertua'] = $e['days']; } if ($u['sejak'] === null || $e['start_date'] < $u['sejak']) { $u['sejak'] = $e['start_date']; } }
                        else { $u['n_done']++; }
                    }
                    $rows[] = $u; $gn += $u['n'];
                } ?>
                <section class="genc-card genc-panel<?php echo $gn > 0 ? ' has-items' : ''; ?>" data-genc-panel>
                    <div class="genc-panel__head">
                        <span class="genc-panel__title"><?php if (!empty($gr['icon'])): ?><i class="fa <?php echo genc_e($gr['icon']); ?> genc-dash__ico" aria-hidden="true"></i><?php endif; ?><span class="genc-panel__name" title="<?php echo genc_e($gr['title']); ?>"><?php echo genc_e($gr['title']); ?></span></span>
                        <?php if ($gn > 0 && $is_open): ?>
                            <span class="genc-badge genc-badge--open"><i class="fa fa-clock-o"></i> <?php echo $gn; ?> belum beres</span>
                        <?php elseif ($gn > 0 && $fil === 'beres'): ?>
                            <span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> <?php echo $gn; ?> beres</span>
                        <?php elseif ($gn > 0 && $fil === 'belum'): ?>
                            <span class="genc-badge genc-badge--open"><?php echo $gn; ?> belum beres</span>
                        <?php elseif ($gn > 0): ?>
                            <span class="genc-badge genc-badge--muted"><?php echo $gn; ?> temuan</span>
                        <?php elseif ($is_open): ?>
                            <span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Semua beres</span>
                        <?php else: ?>
                            <span class="genc-tm__none" aria-label="tidak ada">&ndash;</span>
                        <?php endif; ?>
                    </div>
                    <div class="genc-panel__body" tabindex="0" aria-label="Unit <?php echo genc_e($gr['title']); ?>">
                        <?php if (!$rows): ?>
                            <p class="genc-tm__empty"><?php echo $is_open ? 'Tidak ada temuan yang terbuka.' : 'Tidak ada temuan di bulan ini.'; ?></p>
                        <?php endif; ?>
                        <?php $cur = null; foreach ($rows as $u):
                            $modals[] = $u;
                            if ($u['group'] !== '' && $u['group'] !== $cur): $cur = $u['group']; ?>
                                <div class="genc-hub__group"><?php echo genc_e($cur); ?></div>
                            <?php endif; ?>
                            <a class="genc-hub__row genc-tm__urow has-items<?php echo $u['n_open'] > 0 ? ' is-open' : ' is-done'; ?>" data-genc-prow data-genc-modal-open="tmm-<?php echo genc_e($u['key']); ?>"
                               href="<?php echo genc_e(genc_url($self, array_merge($q_tab, array('buka' => $u['key'])))); ?>#tmm-<?php echo genc_e($u['key']); ?>">
                                <span class="genc-hub__name"><?php echo genc_e($u['label']); ?>
                                    <small><?php if ($is_open): ?>terlama <?php echo genc_e(genc_tm_lama($u['tertua'], true)); ?> &middot; sejak <?php echo genc_e(genc_tm_tgl($u['sejak'])); ?>
                                        <?php else: ?><?php echo $u['n_done']; ?> beres &middot; <?php echo $u['n_open']; ?> belum<?php endif; ?></small></span>
                                <span class="genc-badge <?php echo $u['n_open'] > 0 ? 'genc-badge--open' : 'genc-badge--ok'; ?>"><?php echo (int) $u['n']; ?></span>
                                <i class="fa fa-angle-right genc-hub__go" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="genc-panel__foot">
                        <span class="genc-panel__range" data-genc-prange><?php echo count($rows); ?> unit</span>
                        <span><?php echo $gn > 0 ? $gn . ' temuan' : '&nbsp;'; ?></span>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <p class="genc-small genc-muted genc-tm__foot"><i class="fa fa-info-circle"></i>
            Status temuan dihitung otomatis: temuan = item <?php echo genc_e(genc_pilihan_label('NOK')); ?> / <?php echo genc_e(genc_pilihan_label('PR')); ?> di checklist,
            dan dianggap <b>beres</b> begitu checklist berikutnya untuk unit yang sama menilai item itu <?php echo genc_e(genc_pilihan_label('OK')); ?> (hari itu juga atau sesudahnya).
            Checklist lama dan report tidak diubah &mdash; catatan <?php echo genc_e(genc_pilihan_label('NOK')); ?> tetap tercatat apa adanya.
            Data dibaca sejak <?php echo genc_e(genc_date($d['from'])); ?>. Mesin yang tidak bisa dibuka akun ini tidak ditampilkan.
        </p>

        <?php /* ===== pop-up per unit (daftar item) ===== */
        foreach ($modals as $u): $is_cur = ($buka !== '' && $buka === $u['key']); $mid = 'tmm-' . $u['key'];
            $unit_q = $u['unit'] !== '' ? array('unit' => $u['unit']) : array(); ?>
            <div class="genc-modal genc-tmm<?php echo $is_cur ? ' is-open' : ''; ?>" id="<?php echo genc_e($mid); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo genc_e($mid); ?>-t"<?php echo $is_cur ? '' : ' hidden'; ?> data-genc-modal>
                <div class="genc-modal__box">
                    <div class="genc-modal__head">
                        <div class="genc-modal__titles">
                            <div class="genc-modal__kicker"><?php echo genc_e($u['mtitle']); ?><?php echo ($u['group'] !== '' && strcasecmp($u['group'], $u['mtitle']) !== 0) ? ' &middot; ' . genc_e($u['group']) : ''; ?></div>
                            <h2 class="genc-modal__title" id="<?php echo genc_e($mid); ?>-t"><?php echo genc_e($u['label']); ?></h2>
                            <div class="genc-modal__sub"><?php if ($is_open): ?><b class="genc-tm__open-txt"><?php echo (int) $u['n']; ?> temuan belum beres</b> &middot; terlama <?php echo genc_e(genc_tm_lama($u['tertua'], true)); ?>
                                <?php else: ?><?php echo (int) $u['n']; ?> temuan dari <?php echo genc_e($period['label']); ?> &middot; <?php echo $u['n_done']; ?> beres &middot; <?php echo $u['n_open']; ?> belum<?php endif; ?></div>
                        </div>
                        <div class="genc-modal__tools">
                            <button type="button" class="genc-btn genc-btn--icon genc-btn--ghost" data-genc-modal-max title="Perbesar / perkecil" aria-label="Perbesar"><i class="fa fa-expand" aria-hidden="true"></i></button>
                            <a class="genc-btn genc-btn--icon genc-btn--ghost" data-genc-modal-close href="<?php echo genc_e(genc_url($self, $q_tab)); ?>" title="Tutup (Esc)" aria-label="Tutup"><i class="fa fa-times" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <div class="genc-modal__body genc-tm__body">
                        <?php foreach ($u['list'] as $e):
                            $open = $e['open'];
                            $href = $u['can_view'] ? genc_url($u['page'] . '/view/' . $e['start_id']) : '';
                            $tip  = ($e['section'] !== '' ? $e['section'] . ' · ' : '') . $e['part'] . ' — dicatat ' . genc_date($e['start_date']) . ($e['start_user'] !== '' ? ' oleh ' . genc_person_name($e['start_user']) : '')
                                  . ($e['checks'] > 1 ? ' · ' . $e['checks'] . ' checklist berturut-turut belum ' . $ok_lbl : '')
                                  . ($open ? '' : ' · ' . $ok_lbl . ' lagi ' . genc_date($e['end_date']) . ($e['end_user'] !== '' ? ' (dicek ' . genc_person_name($e['end_user']) . ')' : ''))
                                  . ($e['note'] !== '' ? ' · Keterangan: ' . $e['note'] : ''); ?>
                            <div class="genc-tm__row genc-tm__row--<?php echo $open ? 'open' : 'done'; ?>" title="<?php echo genc_e($tip); ?>">
                                <span class="genc-tm__main">
                                    <?php if ($href !== ''): ?><a class="genc-tm__item" href="<?php echo genc_e($href); ?>"><?php else: ?><span class="genc-tm__item"><?php endif; ?>
                                        <?php echo $val_badge($open ? $e['last_val'] : $e['val']); ?><span class="genc-tm__part"><?php echo genc_e($e['part']); ?></span>
                                    <?php echo $href !== '' ? '</a>' : '</span>'; ?>
                                    <small class="genc-tm__meta">
                                    <?php if ($open): ?>
                                        sejak <?php echo genc_e(genc_tm_tgl($e['start_date'])); ?><?php if ($e['last_date'] !== $e['start_date']): ?> &middot; terakhir dicek <?php echo genc_e(genc_tm_tgl($e['last_date'])); ?><?php endif; ?>
                                    <?php else: ?>
                                        <?php echo genc_e(genc_tm_tgl($e['start_date'])); ?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                                        <?php if ($u['can_view']): ?><a href="<?php echo genc_e(genc_url($u['page'] . '/view/' . $e['end_id'])); ?>"><?php echo genc_e($ok_lbl . ' ' . genc_tm_tgl($e['end_date'])); ?></a><?php else: ?><?php echo genc_e($ok_lbl . ' ' . genc_tm_tgl($e['end_date'])); ?><?php endif; ?><?php if ($e['end_user'] !== ''): ?> &middot; dicek <?php echo genc_e(genc_person_name($e['end_user'])); ?><?php endif; ?>
                                    <?php endif; ?>
                                    </small>
                                    <?php if ($e['note'] !== ''): ?><small class="genc-tm__note"><?php echo genc_e(function_exists('mb_strimwidth') ? mb_strimwidth($e['note'], 0, 120, '…', 'UTF-8') : substr($e['note'], 0, 120)); ?></small><?php endif; ?>
                                </span>
                                <?php if ($open): ?>
                                    <span class="genc-tm__age<?php echo ($e['days'] >= 7 || !$is_open) ? ' is-long' : ''; ?>"><?php echo genc_e((!$is_open ? 'belum beres · ' : '') . genc_tm_lama($e['days'], true)); ?></span>
                                <?php else: ?>
                                    <span class="genc-tm__age is-done"><i class="fa fa-check" aria-hidden="true"></i> <?php echo genc_e(genc_tm_lama($e['days'], false)); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="genc-modal__foot">
                        <span class="genc-small genc-muted">Klik item untuk membuka checklist tempat temuan itu dicatat.</span>
                        <?php if (ACL::is_allowed($u['page'] . '/list')): ?>
                            <a class="genc-btn genc-btn--sm" href="<?php echo genc_e(genc_url($u['page'], array_merge($unit_q, $is_open ? array('status' => 'tindakan') : $base))); ?>"><i class="fa fa-list"></i> Buka daftar unit</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</div>
<?php echo genc_assets_js(); ?>
