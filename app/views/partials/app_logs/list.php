<?php
/**
 * APP LOGS - log aktivitas, hanya baca (Generasi C)      [GENC-24SEP26-SHELL]
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data;
$labels = $d['labels'];   // [GENC-08OKT26-P3] satu sumber dengan export (App_logsController::logs_labels)
$labels['app_logs.export'] = array('Export log', '');
$fl = $d['f'];
$base = array('jenis' => $d['kind'], 'q' => $d['q'], 'dari' => $fl['dari'], 'sampai' => $fl['sampai'], 'user' => $fl['user']);
$base = array_filter($base, function ($v) { return $v !== ''; });
$has_filter = $fl['dari'] !== '' || $fl['sampai'] !== '' || $fl['user'] !== '';
?>
<?php echo genc_assets_css(); ?>
<div class="genc genc-log">
<div class="genc-container">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>App Logs</span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Log aktivitas</h1>
            <p class="genc-subtitle">Siapa melakukan apa dan kapan: login, isi/ubah/hapus/approve checklist, perubahan user, hak akses, mesin &amp; unit. Hanya bisa dibaca.</p>
        </div>
        <div class="genc-actions">
            <div class="genc-menu" data-genc-menu>   <?php /* [GENC-08OKT26-P3] export mengikuti filter */ ?>
                <button type="button" class="genc-btn" data-genc-menu-toggle aria-haspopup="true" aria-expanded="false"><i class="fa fa-download"></i> Export <i class="fa fa-angle-down"></i></button>
                <div class="genc-menu__list genc-menu__list--right" role="menu">
                    <div class="genc-menu__label"><?php echo genc_e($d['kinds'][$d['kind']][0]); ?> &middot; <?php echo (int) $d['total']; ?> catatan<?php echo $has_filter || $d['q'] !== '' ? ' (sesuai filter)' : ''; ?></div>
                    <a class="genc-menu__item" role="menuitem" target="_blank" href="<?php echo genc_e(genc_url('app_logs', $base + array('format' => 'pdf'))); ?>"><i class="fa fa-file-pdf-o"></i><span>PDF<small>Ringkasan + daftar per jenis, siap cetak</small></span></a>
                    <a class="genc-menu__item" role="menuitem" href="<?php echo genc_e(genc_url('app_logs', $base + array('format' => 'excel'))); ?>"><i class="fa fa-file-excel-o"></i><span>Excel<small>Sheet ringkasan + 1 sheet per jenis</small></span></a>
                    <a class="genc-menu__item" role="menuitem" href="<?php echo genc_e(genc_url('app_logs', $base + array('format' => 'csv'))); ?>"><i class="fa fa-file-text-o"></i><span>CSV<small>1 tabel, untuk diolah</small></span></a>
                </div>
            </div>
        </div>
    </div>

    <div class="genc-card">
        <form class="genc-toolbar" method="get" action="<?php print_link('app_logs'); ?>">
            <div class="genc-chips" style="flex-wrap:wrap">
                <?php foreach ($d['kinds'] as $k => $v): ?>
                    <a class="genc-chip<?php echo $d['kind'] === $k ? ' is-active' : ''; ?>" href="<?php echo genc_e(genc_url('app_logs', array_merge($base, array('jenis' => $k)))); ?>"><?php echo genc_e($v[0]); ?><span class="genc-chip__count"><?php echo (int) $d['counts'][$k]; ?></span></a>
                <?php endforeach; ?>
            </div>
            <span class="genc-grow"></span>
            <?php if ($d['kind'] !== ''): ?><input type="hidden" name="jenis" value="<?php echo genc_e($d['kind']); ?>"><?php endif; ?>
            <label class="genc-search"><i class="fa fa-search"></i><span class="sr-only">Cari</span>
                <input class="genc-input" type="search" name="q" value="<?php echo genc_e($d['q']); ?>" placeholder="Cari mesin / user / kata"></label>
            <div class="log-filter">   <?php /* [GENC-08OKT26-P3] tanggal & user */ ?>
                <label><span>Dari</span><input class="genc-input" type="date" name="dari" value="<?php echo genc_e($fl['dari']); ?>"></label>
                <label><span>Sampai</span><input class="genc-input" type="date" name="sampai" value="<?php echo genc_e($fl['sampai']); ?>"></label>
                <label><span>User</span><select class="genc-select" name="user"><option value="">Semua user</option>
                    <?php $ul = array(); foreach ($d['active_users'] as $uid) { if (isset($d['users'][$uid])) { $ul[$uid] = genc_person_name($d['users'][$uid]['username']); } } asort($ul); foreach ($ul as $uid => $un): ?><option value="<?php echo genc_e($uid); ?>"<?php echo $fl['user'] === (string) $uid ? ' selected' : ''; ?>><?php echo genc_e($un); ?></option><?php endforeach; ?>
                </select></label>
                <button type="submit" class="genc-btn genc-btn--sm"><i class="fa fa-filter"></i> Terapkan</button>
                <?php if ($has_filter): ?><a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php echo genc_e(genc_url('app_logs', array_filter(array('jenis' => $d['kind'], 'q' => $d['q']), function ($v) { return $v !== ''; }))); ?>">Hapus filter</a><?php endif; ?>
            </div>
        </form>
        <?php if (empty($d['rows'])): ?>
            <div class="genc-empty"><div class="genc-empty__icon"><i class="fa fa-history"></i></div><div class="genc-empty__title">Belum ada catatan</div>
                <p class="genc-muted">Catatan muncul setelah ada yang login atau mengisi checklist.</p></div>
        <?php else: ?>
        <div class="genc-table-wrap">
        <table class="genc-atable">
            <thead><tr><th style="width:170px">Waktu</th><th style="width:190px">Oleh</th><th style="width:150px">Jenis</th><th>Keterangan</th></tr></thead>
            <tbody>
            <?php foreach ($d['rows'] as $r):
                $act = (string) $r['Action'];
                $lab = isset($labels[$act]) ? $labels[$act] : array($act, '');
                $uid = (string) $r['UserID'];
                $u = isset($d['users'][$uid]) ? $d['users'][$uid] : null;
                $msg = trim((string) $r['RequestMsg']);
                $old = !isset($labels[$act]);
                if ($msg === '') { $msg = trim($act . ' ' . $r['TableName'] . ($r['RecordID'] !== '' ? ' #' . $r['RecordID'] : '')); }
                $raw = genc_e(App_logsController::logs_mask($r['RequestData']));
                $sql = trim((string) $r['SqlQuery']); ?>
                <tr>
                    <td data-label="Waktu"><div class="genc-date"><span class="genc-date__main"><?php echo genc_e(genc_date($r['Timestamp'])); ?></span><span class="genc-date__sub"><?php echo genc_e(genc_time($r['Timestamp'])); ?> &middot; <?php echo genc_e($r['ServerIP']); ?></span></div></td>
                    <td data-label="Oleh"><?php echo $u ? genc_person($u['username'], genc_e($u['username'])) : ($uid !== '' ? '<span class="genc-muted">user #' . genc_e($uid) . '</span>' : '<span class="genc-muted">&ndash;</span>'); ?></td>
                    <td data-label="Jenis"><span class="genc-tag<?php echo $lab[1] !== '' ? ' genc-tag--' . $lab[1] : ''; ?>"><?php echo genc_e($lab[0]); ?></span></td>
                    <td data-label="Keterangan">
                        <div class="genc-log__msg"><?php echo genc_e($msg); ?></div>
                        <?php if (($raw !== '' && $raw !== '[]' && $raw !== '{}') || $sql !== ''): ?>
                        <details><summary><i class="fa fa-angle-right"></i> Detail teknis</summary>
                            <div class="genc-log__data genc-mono"><?php echo $raw; ?><?php echo $sql !== '' ? "\n\n" . genc_e(App_logsController::logs_mask($sql)) : ''; ?><?php echo $r['RequestUrl'] !== '' ? "\n\nURL: " . genc_e($r['RequestUrl']) : ''; ?></div>
                        </details>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <div class="genc-card__foot" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="genc-small genc-muted"><?php echo (($d['page'] - 1) * App_logsController::PER_PAGE) + 1; ?>&ndash;<?php echo min($d['total'], $d['page'] * App_logsController::PER_PAGE); ?> dari <?php echo (int) $d['total']; ?> catatan</span>
            <?php if ($d['pages'] > 1): ?>
            <div style="display:flex;gap:6px">
                <a class="genc-btn genc-btn--sm<?php echo $d['page'] <= 1 ? ' is-disabled' : ''; ?>" href="<?php echo genc_e(genc_url('app_logs', $base + array('hal' => $d['page'] - 1))); ?>"><i class="fa fa-chevron-left"></i> Lebih baru</a>
                <span class="genc-small genc-muted" style="align-self:center">Hal <?php echo (int) $d['page']; ?>/<?php echo (int) $d['pages']; ?></span>
                <a class="genc-btn genc-btn--sm<?php echo $d['page'] >= $d['pages'] ? ' is-disabled' : ''; ?>" href="<?php echo genc_e(genc_url('app_logs', $base + array('hal' => $d['page'] + 1))); ?>">Lebih lama <i class="fa fa-chevron-right"></i></a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php echo genc_assets_js(); ?>
