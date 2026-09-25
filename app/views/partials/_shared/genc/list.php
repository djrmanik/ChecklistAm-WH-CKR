<?php
/**
 * GENERASI C - daftar checklist (VIEW BERSAMA semua mesin)      [GENC-24SEP26]
 * Asal: app/views/partials/conveyor/list.php (23 Sep 2026), kata "conveyor"
 * diganti $g['page'] / $g['title'] / $g['area'] dari controller.
 * Data disiapkan GencChecklistBase::index(). Tampilan: assets/css/checklist-genc.css
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d        = $this->view_data;
$g        = $d['genc'];          // [GENC-24SEP26] nama mesin, area, route - dari controller
$pg       = $g['page'];
$items    = $d['items'];
$period   = $d['period'];
$summary  = $d['summary'];
$records  = $d['records'];
$today    = $d['today'];
$status   = $d['status'];
$q        = $d['q'];
$csrf     = Csrf::$token;

// [GENC-24SEP26-AGV] multi-unit (K7): unit aktif ikut di semua link. Mesin 1 unit: tidak berubah.
$multi    = !empty($g['multi']);
$unit     = $multi ? $g['unit'] : '';
$is_other = $multi && !$g['unit_known'];
$uq       = $multi ? '?unit=' . rawurlencode($unit) : '';

// Parameter periode dibawa ke semua link (filter, paging, export)
$base = array('bulan' => $period['bulan'], 'tahun' => $period['tahun']);
if ($multi) { $base['unit'] = $unit; }
$keep = array_merge($base, array('status' => ($status !== 'semua' ? $status : ''), 'q' => $q));
$navx = $multi ? array('unit' => $unit) : array();
$self = genc_url($pg, $keep);

$nama_bulan  = checklist_nama_bulan();
$now_y       = (int) date('Y');
$tahun_awal  = min((int) CHECKLIST_PERIODE_TAHUN_AWAL, $period['tahun']);
$today_day   = (int) date('j');
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">

    <!-- ============ HEADER ============ -->
    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <span>Autonomous Maintenance</span><span>/</span>
        <span><?php echo genc_e($g['heading']); ?></span>
        <?php if ($multi): ?><span>/</span><span><?php echo genc_e($g['title']); ?></span><?php endif; ?>
    </nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo genc_e($g['heading']); ?></h1>
            <p class="genc-subtitle">Checklist Autonomous Maintenance harian &middot; <?php if ($multi): ?><?php echo genc_e($g['title']); ?> &middot; <?php endif; ?><?php echo genc_e($g['area']); ?></p>
        </div>
        <div class="genc-actions">
            <div class="genc-menu" data-genc-menu>
                <button type="button" class="genc-btn" data-genc-menu-toggle aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-download"></i> Export <i class="fa fa-angle-down"></i>
                </button>
                <div class="genc-menu__list" role="menu">
                    <div class="genc-menu__label">Report <?php echo $multi ? genc_e($g['title']) . ' &middot; ' : ''; ?><?php echo genc_e($period['label']); ?></div>
                    <?php if (!$is_other): ?>
                    <a class="genc-menu__item" role="menuitem" target="_blank" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('format' => 'pdf')))); ?>">
                        <i class="fa fa-file-pdf-o"></i><span>PDF<small>Check sheet resmi, siap cetak</small></span></a>
                    <a class="genc-menu__item" role="menuitem" target="_blank" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('format' => 'print')))); ?>">
                        <i class="fa fa-print"></i><span>Print<small>Buka dialog cetak browser</small></span></a>
                    <a class="genc-menu__item" role="menuitem" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('format' => 'word')))); ?>">
                        <i class="fa fa-file-word-o"></i><span>Word<small>Check sheet yang bisa diedit</small></span></a>
                    <?php endif; ?>
                    <?php $n_rep = $multi ? (isset($g['units_report']) ? (int) $g['units_report'] : count($g['units'])) : 0;   /* [GENC-24SEP26-FORKLIFT] unit sejenis (profil aktif) */
                    if ($multi && $n_rep > 1): /* [GENC-24SEP26-AGV] semua unit = 1 unit per halaman */ ?>
                    <a class="genc-menu__item" role="menuitem" target="_blank" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('unit' => 'semua', 'format' => 'pdf')))); ?>">
                        <i class="fa fa-files-o"></i><span>PDF semua unit<small><?php echo $n_rep; ?> unit <?php echo genc_e($g['machine']); ?>, 1 unit per halaman</small></span></a>
                    <?php endif; ?>
                    <div class="genc-menu__sep"></div>
                    <div class="genc-menu__label">Data mentah</div>
                    <a class="genc-menu__item" role="menuitem" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('format' => 'excel')))); ?>">
                        <i class="fa fa-file-excel-o"></i><span>Excel (CSV ;)<small>1 baris = 1 checklist</small></span></a>
                    <a class="genc-menu__item" role="menuitem" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('format' => 'csv')))); ?>">
                        <i class="fa fa-file-text-o"></i><span>CSV (,)</span></a>
                </div>
            </div>
            <?php if ($d['can_add']): ?>
                <?php if (!empty($today)): $t0 = $today[0]; ?>
                    <a class="genc-btn" href="<?php print_link($pg . '/add' . $uq); ?>" title="Checklist hari ini sudah ada. Klik kalau memang perlu isi lagi.">
                        <i class="fa fa-plus"></i> Isi lagi
                    </a>
                <?php else: ?>
                    <a class="genc-btn genc-btn--primary" href="<?php print_link($pg . '/add' . $uq); ?>">
                        <i class="fa fa-pencil-square-o"></i> Isi checklist hari ini
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php echo genc_tabs($g, 'list', $base); /* [GENC-24SEP26-DUMPING] kosong kalau mesin tanpa tab */ ?>

    <?php if ($is_other): /* [GENC-24SEP26-AGV] */ ?>
        <div class="genc-notice genc-notice--warn" style="margin-bottom:16px">
            <i class="fa fa-question-circle"></i>
            <div><strong>Checklist dengan nomor unit yang tidak dikenal.</strong> Nomor unitnya (lihat di bawah tanggal) tidak cocok dengan <?php echo genc_e(isset($g['machine_all']) ? $g['machine_all'] : $g['machine']); ?> mana pun,
                jadi tidak masuk tab unit maupun report per unit &mdash; hanya ikut di <em>PDF semua unit</em>. Kemungkinan salah ketik di form lama.
                <a href="<?php echo genc_e(genc_url($pg, array('bulan' => $period['bulan'], 'tahun' => $period['tahun']))); ?>">Kembali ke unit pertama</a></div>
        </div>
    <?php elseif ($multi && !empty($d['unit_other'])): $uo = array(); foreach ($d['unit_other'] as $raw => $n) { $uo[] = '&ldquo;' . genc_e($raw !== '' ? $raw : '(kosong)') . '&rdquo;'; } ?>
        <div class="genc-notice genc-notice--info" style="margin-bottom:16px">
            <i class="fa fa-info-circle"></i>
            <div><?php echo (int) array_sum($d['unit_other']); ?> checklist <?php echo genc_e(isset($g['machine_all']) ? $g['machine_all'] : $g['machine']); ?> di <?php echo genc_e($period['label']); ?> tercatat dengan nomor unit yang tidak dikenal (<?php echo implode(', ', $uo); ?>), jadi tidak tampil di tab mana pun.
                <a href="<?php echo genc_e(genc_url($pg, array_merge($base, array('unit' => 'lainnya')))); ?>">Lihat</a></div>
        </div>
    <?php endif; ?>

    <?php $this::display_page_errors(); ?>

    <!-- ============ RINGKASAN BULAN ============ -->
    <div class="genc-kpis">
        <!-- Kepatuhan pengisian + kalender -->
        <div class="genc-card genc-kpi">
            <div class="genc-kpi__label"><i class="fa fa-calendar-check-o"></i> Pengisian <?php echo genc_e($period['label']); ?></div>
            <div class="genc-row" style="justify-content:space-between; align-items:flex-end">
                <div class="genc-kpi__value"><?php echo (int) $summary['filled']; ?> <small>dari <?php echo (int) $summary['elapsed']; ?> hari</small></div>
                <div class="genc-strong" style="color:<?php echo $summary['percent'] >= 90 ? 'var(--gc-ok)' : ($summary['percent'] >= 60 ? 'var(--gc-pr)' : 'var(--gc-nok)'); ?>">
                    <?php echo $summary['elapsed'] > 0 ? (int) $summary['percent'] . '%' : '&ndash;'; ?>
                </div>
            </div>
            <div class="genc-cal" aria-label="Kalender pengisian">
                <?php for ($i = 1; $i <= $period['day_count']; $i++):
                    $st = isset($summary['days'][$i]) ? $summary['days'][$i] : '';
                    if ($st === 'ok')            { $cls = 'ok';     $tip = 'Terisi, semua baik'; }
                    elseif ($st === 'issue')     { $cls = 'issue';  $tip = 'Terisi, ada temuan'; }
                    elseif ($i <= $summary['elapsed']) { $cls = 'missed'; $tip = 'Belum / tidak diisi'; }
                    else                         { $cls = 'future'; $tip = ''; }
                    $is_today = $period['is_current'] && $i === $today_day;
                ?>
                    <span class="genc-cal__d genc-cal__d--<?php echo $cls; ?><?php echo $is_today ? ' genc-cal__d--today' : ''; ?>" title="<?php echo $i . ' ' . genc_e($nama_bulan[$period['bulan']]) . ($tip ? ': ' . $tip : ''); ?>"><?php echo $i; ?></span>
                <?php endfor; ?>
            </div>
            <div class="genc-legend">
                <span><i style="background:#34a853"></i>Baik</span>
                <span><i style="background:var(--gc-nok)"></i>Ada temuan</span>
                <span><i style="background:#fff;box-shadow:inset 0 0 0 1px var(--gc-border-strong)"></i>Tidak diisi</span>
            </div>
        </div>

        <!-- Hari ini -->
        <div class="genc-card genc-kpi">
            <div class="genc-kpi__label"><i class="fa fa-sun-o"></i> Hari ini</div>
            <?php if (!empty($today)): $t0 = $today[0];
                // [GENC-24SEP26-DUMPING] isian dari form lama (tanpa `kondisi`) dinilai dari item
                $ok0 = trim((string) $t0['kondisi']) !== '' ? (strpos((string) $t0['kondisi'], '✔') !== false) : !empty($t0['_ok']); ?>
                <div class="genc-kpi__value" style="color:var(--gc-ok)"><i class="fa fa-check-circle"></i> Sudah</div>
                <div class="genc-kpi__hint">
                    <?php echo genc_e(genc_person_name($t0['user_created'])); ?><?php echo genc_time($t0['date_created']) !== '' ? ' &middot; ' . genc_e(genc_time($t0['date_created'])) : ''; /* [GENC-24SEP26-PALLETMOVER] kolom DATE: tanpa jam */ ?>
                    <?php if (count($today) > 1): ?> &middot; <?php echo count($today); ?> kali diisi<?php endif; ?>
                </div>
                <div><?php echo $ok0 ? '<span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Semua baik</span>' : '<span class="genc-badge genc-badge--nok"><i class="fa fa-exclamation-triangle"></i> Ada temuan</span>'; ?></div>
            <?php else: ?>
                <div class="genc-kpi__value" style="color:var(--gc-muted)">Belum</div>
                <div class="genc-kpi__hint"><?php echo genc_e(genc_date(time(), true)); ?></div>
                <?php if ($d['can_add']): ?><div><a href="<?php print_link($pg . '/add' . $uq); ?>" class="genc-strong">Isi sekarang <i class="fa fa-arrow-right"></i></a></div><?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Temuan -->
        <a class="genc-card genc-kpi genc-kpi--link<?php echo $status === 'tindakan' ? ' is-active' : ''; ?>" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('status' => $status === 'tindakan' ? '' : 'tindakan')))); ?>">
            <div class="genc-kpi__label"><i class="fa fa-exclamation-triangle"></i> Perlu tindakan</div>
            <div class="genc-kpi__value" style="color:<?php echo $summary['issues'] ? 'var(--gc-nok)' : 'var(--gc-text)'; ?>"><?php echo (int) $summary['issues']; ?> <small>checklist</small></div>
            <div class="genc-kpi__hint">
                <?php if (!empty($summary['item_issue'])):
                    $parts = array();
                    foreach ($summary['item_issue'] as $name => $n) { $parts[] = genc_e($name) . ' (' . (int) $n . ')'; }
                    echo 'Terbanyak: ' . implode(', ', $parts);
                else: ?>Tidak ada item NOK / perawatan<?php endif; ?>
            </div>
        </a>

        <!-- Menunggu approval -->
        <a class="genc-card genc-kpi genc-kpi--link<?php echo $status === 'menunggu' ? ' is-active' : ''; ?>" href="<?php echo genc_e(genc_url($pg, array_merge($base, array('status' => $status === 'menunggu' ? '' : 'menunggu')))); ?>">
            <div class="genc-kpi__label"><i class="fa fa-clock-o"></i> Menunggu approval</div>
            <div class="genc-kpi__value" style="color:<?php echo $summary['pending'] ? 'var(--gc-pending)' : 'var(--gc-text)'; ?>"><?php echo (int) $summary['pending']; ?> <small>checklist</small></div>
            <div class="genc-kpi__hint"><?php echo $d['can_approve'] ? 'Klik untuk lihat &amp; approve' : 'Di-approve SPV / fasilitator'; ?></div>
        </a>
    </div>

    <!-- ============ DAFTAR ============ -->
    <div class="genc-card">
        <form class="genc-toolbar" method="get" action="<?php print_link($pg); ?>" role="search">
            <div class="genc-period" aria-label="Periode">
                <a class="genc-btn genc-btn--icon genc-btn--ghost" title="Bulan sebelumnya" href="<?php echo genc_e(genc_url($pg, array_merge(array('bulan' => $period['prev'][0], 'tahun' => $period['prev'][1], 'status' => $keep['status'], 'q' => $q), $navx))); ?>"><i class="fa fa-chevron-left"></i></a>
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
                <a class="genc-btn genc-btn--icon genc-btn--ghost<?php echo $period['is_current'] ? ' is-disabled' : ''; ?>" title="Bulan berikutnya" href="<?php echo genc_e(genc_url($pg, array_merge(array('bulan' => $period['next'][0], 'tahun' => $period['next'][1], 'status' => $keep['status'], 'q' => $q), $navx))); ?>"><i class="fa fa-chevron-right"></i></a>
            </div>

            <div class="genc-chips" role="tablist" aria-label="Status">
                <?php
                $chips = array('semua' => array('Semua', $summary['records']), 'tindakan' => array('Perlu tindakan', $summary['issues']), 'menunggu' => array('Menunggu approval', $summary['pending']));
                foreach ($chips as $key => $c): ?>
                    <a class="genc-chip<?php echo $status === $key ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $status === $key ? 'true' : 'false'; ?>"
                       href="<?php echo genc_e(genc_url($pg, array_merge($base, array('status' => $key === 'semua' ? '' : $key, 'q' => $q)))); ?>">
                        <?php echo genc_e($c[0]); ?><span class="genc-chip__count"><?php echo (int) $c[1]; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($status !== 'semua'): ?><input type="hidden" name="status" value="<?php echo genc_e($status); ?>"><?php endif; ?>
            <?php if ($multi): ?><input type="hidden" name="unit" value="<?php echo genc_e($unit); ?>"><?php endif; ?>

            <div class="genc-grow"></div>
            <label class="genc-search">
                <span class="sr-only">Cari</span>
                <i class="fa fa-search"></i>
                <input class="genc-input" type="search" name="q" value="<?php echo genc_e($q); ?>" placeholder="Cari pelaksana / keterangan">
            </label>
        </form>

        <?php if (empty($records)): ?>
            <div class="genc-empty">
                <div class="genc-empty__icon"><i class="fa <?php echo ($q !== '' || $status !== 'semua') ? 'fa-filter' : 'fa-clipboard'; ?>"></i></div>
                <?php if ($q !== '' || $status !== 'semua'): ?>
                    <div class="genc-empty__title">Tidak ada yang cocok dengan filter</div>
                    <p class="genc-muted">Periode <?php echo genc_e($period['label']); ?>. <a href="<?php echo genc_e(genc_url($pg, $base)); ?>">Hapus filter</a></p>
                <?php else: ?>
                    <div class="genc-empty__title">Belum ada checklist <?php echo $multi ? genc_e($g['title']) . ' ' : ''; ?>di <?php echo genc_e($period['label']); ?></div>
                    <p class="genc-muted">Checklist yang diisi akan muncul di sini, lengkap dengan status approval-nya.</p>
                    <?php if ($d['can_add'] && $period['is_current']): ?>
                        <p style="margin-top:14px"><a class="genc-btn genc-btn--primary" href="<?php print_link($pg . '/add' . $uq); ?>"><i class="fa fa-pencil-square-o"></i> Isi checklist hari ini</a></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="genc-table-wrap">
                <table class="genc-table">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Pelaksana</th>
                            <th scope="col">Hasil <?php echo count($items); ?> item</th>
                            <th scope="col">Keterangan</th>
                            <th scope="col">Approval</th>
                            <th scope="col" class="genc-col-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($records as $r):
                        $id = (int) $r['id'];
                        $bad = $r['_bad'];              // [GENC-24SEP26-DUMPING] dinilai controller (genc_eval_row)
                        $approved = $r['_approved'];
                    ?>
                        <tr>
                            <td data-label="Tanggal">
                                <div class="genc-date">
                                    <span class="genc-date__main"><?php echo genc_e(genc_date($r['date_created'])); ?></span>
                                    <span class="genc-date__sub"><?php echo genc_e(genc_time($r['date_created'])); ?><?php if ($is_other): ?> &middot; Unit: <?php echo genc_e($r['_unit_label']); ?><?php endif; ?><?php
                                        if (!empty($g['extra_fields'])): foreach ($g['extra_fields'] as $f): if (!$f['list'] || trim((string) $r[$f['db']]) === '') { continue; } /* [GENC-24SEP26-FORKLIFT] */ ?><span class="genc-date__extra" title="<?php echo genc_e($f['label']); ?>"><i class="fa fa-tachometer" aria-hidden="true"></i><?php echo genc_e($r[$f['db']] . ($f['suffix'] !== '' ? ' ' . $f['suffix'] : '')); ?></span><?php endforeach; endif; ?></span>
                                </div>
                            </td>
                            <td data-label="Pelaksana"><?php echo genc_person($r['user_created']); ?></td>
                            <td data-label="Hasil">
                                <div class="genc-result">
                                    <?php echo genc_strip(genc_display_row($r), $items); ?>
                                    <span class="genc-result__text">
                                        <?php if (empty($bad)): ?><?php echo empty($r['_unknown']) ? 'Semua baik' : 'Tidak ada temuan'; ?><?php else: ?><b><?php echo count($bad); ?> perlu tindakan:</b> <?php echo genc_e(implode(', ', $bad)); ?><?php endif; ?>
                                        <?php if (!empty($r['_unknown'])): ?><span class="genc-legacy-note" title="Diisi lewat form lama (teks bebas). Kotak abu-abu = isian yang tidak bisa dibaca sebagai Baik / Tidak baik."><?php echo (int) $r['_unknown']; ?> isian lama tidak terbaca</span><?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Keterangan"><div class="genc-note" title="<?php echo genc_e($r['keterangan']); ?>"><?php echo trim((string) $r['keterangan']) !== '' ? genc_e($r['keterangan']) : '<span class="genc-muted">&ndash;</span>'; ?></div></td>
                            <td data-label="Approval"><?php echo genc_approval_badge($r); ?></td>
                            <td class="genc-col-actions">
                                <?php if ($d['can_approve'] && !$approved): ?>
                                    <form method="post" action="<?php print_link($pg . '/approve/' . $id . '?csrf_token=' . $csrf . '&redirect=' . rawurlencode($pg . '?' . http_build_query(array_filter($keep)))); ?>" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo genc_e($csrf); ?>">
                                        <button type="submit" class="genc-btn genc-btn--sm genc-btn--success"
                                                data-genc-confirm="Approve checklist <?php echo genc_e(genc_date($r['date_created'])); ?>?"
                                                data-genc-confirm-msg="<?php echo genc_e(empty($bad) ? (empty($r['_unknown']) ? 'Semua item baik.' : 'Tidak ada temuan (' . (int) $r['_unknown'] . ' isian lama tidak terbaca).') : count($bad) . ' item perlu tindakan: ' . implode(', ', $bad) . '.'); ?> Paraf Anda akan tercetak di report."
                                                data-genc-confirm-ok="Approve"><i class="fa fa-check"></i> Approve</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($d['can_view']): ?>
                                    <a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php print_link($pg . '/view/' . $id); ?>" title="Lihat detail"><i class="fa fa-eye"></i><span class="sr-only">Lihat</span></a>
                                <?php endif; ?>
                                <?php if ($d['can_edit']): ?>
                                    <a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php print_link($pg . '/edit/' . $id); ?>" title="Ubah"><i class="fa fa-pencil"></i><span class="sr-only">Ubah</span></a>
                                <?php endif; ?>
                                <?php if ($d['can_delete']): ?>
                                    <a class="genc-btn genc-btn--sm genc-btn--ghost" style="color:var(--gc-nok)" title="Hapus"
                                       href="<?php print_link($pg . '/delete/' . $id . '?csrf_token=' . $csrf . '&redirect=' . rawurlencode($pg . '?' . http_build_query(array_filter($keep)))); ?>"
                                       data-genc-confirm="Hapus checklist <?php echo genc_e(genc_date($r['date_created'])); ?>?"
                                       data-genc-confirm-msg="Data yang dihapus tidak bisa dikembalikan dan hilang dari report bulanan."
                                       data-genc-confirm-ok="Hapus" data-genc-danger><i class="fa fa-trash-o"></i><span class="sr-only">Hapus</span></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="genc-pager">
                <span class="genc-muted genc-small">
                    <?php $from = ($d['page'] - 1) * 20 + 1; $to = $from + count($records) - 1; ?>
                    <?php echo $from; ?>&ndash;<?php echo $to; ?> dari <?php echo (int) $d['total']; ?> checklist &middot; <?php echo genc_e($period['label']); ?>
                </span>
                <?php if ($d['page_count'] > 1): ?>
                    <span class="genc-pager__pages">
                        <?php for ($p = 1; $p <= $d['page_count']; $p++): ?>
                            <?php if ($p === $d['page']): ?><span class="is-current"><?php echo $p; ?></span>
                            <?php else: ?><a href="<?php echo genc_e(genc_url($pg, array_merge($keep, array('hal' => $p)))); ?>"><?php echo $p; ?></a><?php endif; ?>
                        <?php endfor; ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <p class="genc-small genc-muted" style="margin-top:12px">
        <i class="fa fa-info-circle"></i> Periode di atas berlaku untuk daftar, ringkasan, dan Export &mdash; yang terlihat di sini sama dengan yang tercetak di report.
    </p>
</div>
</div>
<?php echo genc_assets_js(); ?>
