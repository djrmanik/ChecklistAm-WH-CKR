<?php
/**
 * Conveyor - detail satu checklist (GENERASI C)                23 Sep 2026
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d        = $this->view_data;
$r        = $d['record'];
$items    = $d['items'];
$sections = $d['sections'];
$csrf     = Csrf::$token;
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">

    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <a href="<?php print_link('conveyor'); ?>">Conveyor</a><span>/</span>
        <span>Detail</span>
    </nav>

<?php if (!$r): ?>
    <div class="genc-card"><div class="genc-empty">
        <div class="genc-empty__icon"><i class="fa fa-search"></i></div>
        <div class="genc-empty__title">Checklist tidak ditemukan</div>
        <p class="genc-muted">Mungkin sudah dihapus. <a href="<?php print_link('conveyor'); ?>">Kembali ke daftar</a></p>
    </div></div>
<?php else:
    $id = (int) $r['id'];
    $bad = array();
    foreach ($items as $it) { if (strtoupper(trim((string) $r[$it['db']])) !== 'OK') { $bad[] = $it['part']; } }
    $approved = trim((string) $r['approval']) !== '' && stripos($r['approval'], 'not') === false;
    $back = 'conveyor?' . http_build_query(array('bulan' => (int) date('n', strtotime($r['date_created'])), 'tahun' => (int) date('Y', strtotime($r['date_created']))));
?>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo genc_e(genc_date($r['date_created'], true)); ?></h1>
            <p class="genc-subtitle">Checklist AM conveyor<?php echo genc_time($r['date_created']) ? ' &middot; pukul ' . genc_e(genc_time($r['date_created'])) : ''; ?></p>
        </div>
        <div class="genc-actions">
            <a class="genc-btn genc-btn--ghost" href="<?php print_link($back); ?>"><i class="fa fa-arrow-left"></i> Daftar</a>
            <?php if ($d['can_delete']): ?>
                <a class="genc-btn genc-btn--danger" href="<?php print_link('conveyor/delete/' . $id . '?csrf_token=' . $csrf . '&redirect=' . rawurlencode($back)); ?>"
                   data-genc-confirm="Hapus checklist ini?" data-genc-confirm-msg="Data yang dihapus tidak bisa dikembalikan dan hilang dari report bulanan." data-genc-confirm-ok="Hapus" data-genc-danger>
                    <i class="fa fa-trash-o"></i> Hapus</a>
            <?php endif; ?>
            <?php if ($d['can_edit']): ?>
                <a class="genc-btn" href="<?php print_link('conveyor/edit/' . $id); ?>"><i class="fa fa-pencil"></i> Ubah</a>
            <?php endif; ?>
            <?php if ($d['can_approve'] && !$approved): ?>
                <form method="post" action="<?php print_link('conveyor/approve/' . $id . '?csrf_token=' . $csrf . '&redirect=' . rawurlencode('conveyor/view/' . $id)); ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?php echo genc_e($csrf); ?>">
                    <button type="submit" class="genc-btn genc-btn--success" data-genc-confirm="Approve checklist ini?"
                            data-genc-confirm-msg="<?php echo genc_e(empty($bad) ? 'Semua item baik.' : count($bad) . ' item perlu tindakan: ' . implode(', ', $bad) . '.'); ?> Paraf Anda akan tercetak di report." data-genc-confirm-ok="Approve">
                        <i class="fa fa-check"></i> Approve</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="genc-stack">
        <div class="genc-card genc-card__body">
            <div class="genc-dl">
                <div><div class="genc-meta__k">Pelaksana</div><div style="margin-top:4px"><?php echo genc_person($r['user_created']); ?></div></div>
                <div><div class="genc-meta__k">Hasil</div><div style="margin-top:6px">
                    <?php echo empty($bad) ? '<span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Semua baik</span>'
                                          : '<span class="genc-badge genc-badge--nok"><i class="fa fa-exclamation-triangle"></i> ' . count($bad) . ' perlu tindakan</span>'; ?>
                </div></div>
                <div><div class="genc-meta__k">Approval</div><div style="margin-top:6px"><?php echo genc_approval_badge($r); ?>
                    <?php if ($approved && !empty($r['date_approve'])): ?><div class="genc-small genc-muted"><?php echo genc_e(genc_date($r['date_approve']) . ' ' . genc_time($r['date_approve'])); ?></div><?php endif; ?>
                </div></div>
                <div><div class="genc-meta__k">Terakhir diubah</div><div style="margin-top:6px" class="genc-small">
                    <?php echo !empty($r['date_update']) ? genc_e(genc_date($r['date_update']) . ' ' . genc_time($r['date_update'])) . '<br>oleh ' . genc_e(genc_person_name($r['user_update'])) : '<span class="genc-muted">Belum pernah</span>'; ?>
                </div></div>
            </div>
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
                <?php foreach ($s['items'] as $it): $m = genc_value_meta($r[$it['db']]); ?>
                    <div class="genc-result-row">
                        <div class="genc-item__no" style="<?php echo $m['key'] === 'ok' ? 'background:var(--gc-ok-soft);color:var(--gc-ok)' : ($m['key'] ? 'background:var(--gc-nok-soft);color:var(--gc-nok)' : ''); ?>"><?php echo (int) $it['no']; ?></div>
                        <div>
                            <div class="genc-strong"><?php echo genc_e($it['part']); ?></div>
                            <div class="genc-small genc-muted">Standar: <?php echo genc_e($it['standar']); ?></div>
                        </div>
                        <div><?php echo genc_value_badge($r[$it['db']]); ?></div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
</div>
<?php echo genc_assets_js(); ?>
