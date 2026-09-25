<?php
/**
 * GENERASI C - detail satu checklist (VIEW BERSAMA semua mesin)      [GENC-25SEP26-AUDIT]
 *
 * File ini HILANG dari paket kode (zip 24 Sep) -> semua tombol "Lihat" / klik unit di Home
 * yang sudah diisi berakhir "view.php File Not Found". Dibangun ulang dari conveyor/view.php
 * (23 Sep) + kemampuan cetakan yang ditambah sesudahnya: tab (dumping/AGV/forklift/pallet
 * mover), unit per record, profil per unit, isian lama (K6), jam kerja (K24), kolom DATE.
 * Data dari GencChecklistBase::view(): record, items, sections, can_*, genc.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d        = $this->view_data;
$g        = $d['genc'];
$pg       = $g['page'];
$r        = $d['record'];
$items    = $d['items'];
$sections = $d['sections'];
$csrf     = Csrf::$token;
$multi    = !empty($g['multi']);
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">

    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <a href="<?php print_link($pg); ?>"><?php echo genc_e($g['heading']); ?></a><span>/</span>
        <?php if ($r && !empty($g['tabs'])): ?><a href="<?php echo genc_e(genc_url($pg, $multi ? array('unit' => $g['unit']) : array())); ?>"><?php echo genc_e($g['title']); ?></a><span>/</span><?php endif; ?>
        <span>Detail</span>
    </nav>

<?php if (!$r): ?>
    <div class="genc-card"><div class="genc-empty">
        <div class="genc-empty__icon"><i class="fa fa-search"></i></div>
        <div class="genc-empty__title">Checklist tidak ditemukan</div>
        <p class="genc-muted">Mungkin sudah dihapus. <a href="<?php print_link($pg); ?>">Kembali ke daftar</a></p>
    </div></div>
<?php else:
    $id       = (int) $r['id'];
    $show     = genc_display_row($r);                       // nilai item hasil penilaian (_vals)
    $bad      = isset($r['_bad']) ? $r['_bad'] : array();
    $bad_parts= isset($r['_bad_parts']) ? $r['_bad_parts'] : array();
    $unknown  = isset($r['_unknown']) ? (int) $r['_unknown'] : 0;
    $legacy   = isset($r['_legacy']) ? $r['_legacy'] : array();
    $approved = !empty($r['_approved']);
    $ts       = strtotime($r['date_created']);
    $bq       = array('bulan' => (int) date('n', $ts), 'tahun' => (int) date('Y', $ts));
    if ($multi) { $bq = array('unit' => $g['unit']) + $bq; }
    $back     = $pg . '?' . http_build_query($bq);
    $jam      = genc_time($r['date_created']);
?>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo genc_e(genc_date($r['date_created'], true)); ?></h1>
            <p class="genc-subtitle">Checklist AM <?php echo genc_e($g['name_lower']); ?><?php echo $jam !== '' ? ' &middot; pukul ' . genc_e($jam) : ''; ?><?php echo $g['area'] !== '' ? ' &middot; ' . genc_e($g['area']) : ''; ?></p>
        </div>
        <div class="genc-actions">
            <a class="genc-btn genc-btn--ghost" href="<?php print_link($back); ?>"><i class="fa fa-arrow-left"></i> Daftar</a>
            <?php if ($d['can_delete']): ?>
                <a class="genc-btn genc-btn--danger" href="<?php print_link($pg . '/delete/' . $id . '?csrf_token=' . $csrf . '&redirect=' . rawurlencode($back)); ?>"
                   data-genc-confirm="Hapus checklist <?php echo genc_e(genc_date($r['date_created'])); ?>?" data-genc-confirm-msg="Data yang dihapus tidak bisa dikembalikan dan hilang dari report bulanan." data-genc-confirm-ok="Hapus" data-genc-danger>
                    <i class="fa fa-trash-o"></i> Hapus</a>
            <?php endif; ?>
            <?php if ($d['can_edit']): ?>
                <a class="genc-btn" href="<?php print_link($pg . '/edit/' . $id); ?>"><i class="fa fa-pencil"></i> Ubah</a>
            <?php endif; ?>
            <?php if ($d['can_approve'] && !$approved): ?>
                <form method="post" action="<?php print_link($pg . '/approve/' . $id . '?csrf_token=' . $csrf . '&redirect=' . rawurlencode($pg . '/view/' . $id)); ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?php echo genc_e($csrf); ?>">
                    <button type="submit" class="genc-btn genc-btn--success" data-genc-confirm="Approve checklist <?php echo genc_e(genc_date($r['date_created'])); ?>?"
                            data-genc-confirm-msg="<?php echo genc_e(empty($bad) ? ($unknown ? 'Tidak ada temuan (' . $unknown . ' isian lama tidak terbaca).' : 'Semua item baik.') : count($bad) . ' item perlu tindakan: ' . implode(', ', $bad) . '.'); ?> Paraf Anda akan tercetak di report." data-genc-confirm-ok="Approve">
                        <i class="fa fa-check"></i> Approve</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php echo genc_tabs($g, 'record'); ?>

    <div class="genc-stack">
        <?php if ($multi && empty($g['unit_known'])): ?>
            <div class="genc-notice genc-notice--warn"><i class="fa fa-question-circle"></i>
                <div><strong>Nomor unit di checklist lama ini tidak dikenali</strong> (&ldquo;<?php echo genc_e($r['_unit_raw'] !== '' ? $r['_unit_raw'] : '(kosong)'); ?>&rdquo;). Data tetap tersimpan apa adanya, tapi tidak masuk report unit mana pun.</div></div>
        <?php endif; ?>
        <?php if ($unknown > 0): ?>
            <div class="genc-notice genc-notice--info"><i class="fa fa-history"></i>
                <div><strong><?php echo $unknown; ?> isian lama tidak terbaca</strong> (diketik bebas di form lama). Ditampilkan abu-abu apa adanya dan tidak dihitung sebagai temuan.</div></div>
        <?php endif; ?>

        <div class="genc-card genc-card__body">
            <div class="genc-dl">
                <div><div class="genc-meta__k">Pelaksana</div><div style="margin-top:4px"><?php echo genc_person($r['user_created']); ?></div></div>
                <div><div class="genc-meta__k">Hasil</div><div style="margin-top:6px">
                    <?php echo empty($bad) ? '<span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Semua baik</span>'
                                          : '<span class="genc-badge genc-badge--nok"><i class="fa fa-exclamation-triangle"></i> ' . count($bad) . ' perlu tindakan</span>'; ?>
                </div></div>
                <div><div class="genc-meta__k">Approval</div><div style="margin-top:6px"><?php echo genc_approval_badge($r); ?>
                    <?php if ($approved && !empty($r['date_approve'])): ?><div class="genc-small genc-muted"><?php echo genc_e(trim(genc_date($r['date_approve']) . ' ' . genc_time($r['date_approve']))); ?></div><?php endif; ?>
                </div></div>
                <div><div class="genc-meta__k">Terakhir diubah</div><div style="margin-top:6px" class="genc-small">
                    <?php if (!empty($r['date_update'])): ?>
                        <?php echo genc_e(trim(genc_date($r['date_update']) . ' ' . genc_time($r['date_update']))); ?>
                        <?php if (trim((string) $r['user_update']) !== ''): ?><br>oleh <?php echo genc_e(genc_person_name($r['user_update'])); ?><?php endif; ?>
                    <?php else: ?><span class="genc-muted">Belum pernah</span><?php endif; ?>
                </div></div>
            </div>
            <?php if ($multi || !empty($g['extra_fields'])): ?>
            <div class="genc-dl" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--gc-border)">
                <?php if ($multi): ?><div><div class="genc-meta__k">Unit</div><div style="margin-top:4px" class="genc-strong"><i class="fa fa-cube genc-muted"></i> <?php echo genc_e($g['unit_label']); ?></div></div>
                <div><div class="genc-meta__k">Jenis</div><div style="margin-top:4px"><?php echo genc_e($g['machine']); ?></div></div><?php endif; ?>
                <?php if (!empty($g['extra_fields'])): foreach ($g['extra_fields'] as $f): $ev = isset($r[$f['db']]) ? trim((string) $r[$f['db']]) : ''; ?>
                    <div><div class="genc-meta__k"><?php echo genc_e($f['label']); ?></div><div style="margin-top:4px" class="genc-strong"><?php echo $ev !== '' ? genc_e($ev . ($f['suffix'] !== '' ? ' ' . $f['suffix'] : '')) : '<span class="genc-muted">&ndash;</span>'; ?></div></div>
                <?php endforeach; endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (trim((string) $r['keterangan']) !== ''): ?>
            <div class="genc-card genc-card__body">
                <div class="genc-meta__k" style="margin-bottom:4px">Keterangan</div>
                <div style="white-space:pre-wrap"><?php echo genc_e($r['keterangan']); ?></div>
            </div>
        <?php endif; ?>

        <?php foreach ($sections as $s): ?>
            <section class="genc-card">
                <div class="genc-card__head">
                    <h2 class="genc-card__title"><?php echo genc_e(isset($s['short']) ? $s['short'] : $s['title']); ?></h2>
                    <span class="genc-small genc-muted"><?php echo genc_e($s['title']); ?></span>
                </div>
                <?php foreach ($s['items'] as $it):
                    $v = isset($show[$it['db']]) ? $show[$it['db']] : '';
                    $m = genc_value_meta($v);
                    $no_style = $m['key'] === 'ok' ? 'background:var(--gc-ok-soft);color:var(--gc-ok)'
                              : ($m['key'] === 'pr' ? 'background:var(--gc-pr-soft);color:var(--gc-pr)'
                              : ($m['key'] === 'nok' ? 'background:var(--gc-nok-soft);color:var(--gc-nok)' : '')); ?>
                    <div class="genc-result-row">
                        <div class="genc-item__no" style="<?php echo $no_style; ?>"><?php echo (int) $it['no']; ?></div>
                        <div>
                            <div class="genc-strong"><?php echo genc_e($it['part']); ?></div>
                            <div class="genc-small genc-muted">Standar: <?php echo genc_e($it['standar']); ?></div>
                            <?php if (isset($legacy[$it['db']]) && $m['key'] !== ''): ?><div class="genc-item__legacy"><i class="fa fa-history"></i> Isian lama: &ldquo;<?php echo genc_e($legacy[$it['db']]); ?>&rdquo;</div><?php endif; ?>
                        </div>
                        <div><?php echo $m['key'] === '' && trim((string) $v) === '' ? '<span class="genc-badge">Kosong</span>' : genc_value_badge($v); ?></div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
</div>
<?php echo genc_assets_js(); ?>
