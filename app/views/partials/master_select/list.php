<?php
/**
 * MASTER SELECT (Generasi C)                             [GENC-25SEP26-AUDIT]
 * Cadangan tampilan lama: _backup_genc_25sept26/master_select_list_sebelum-audit.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data; $f = $d['form']; $can = $d['can'];
$tone = function ($v) { $v = strtoupper(trim((string) $v)); return $v === 'OK' ? 'genc-tag--ok' : ($v === 'NOK' ? 'genc-tag--nok' : ($v === 'PR' ? 'genc-tag--pr' : '')); };
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Master Select</span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Master Select</h1>
            <p class="genc-subtitle">Daftar pilihan dropdown di form lama (Forklift sebelum Generasi C).</p>
        </div>
        <?php if ($can['add'] && !$f): ?><div class="genc-actions"><a class="genc-btn genc-btn--primary" href="<?php print_link('master_select/add'); ?>"><i class="fa fa-plus"></i> Tambah pilihan</a></div><?php endif; ?>
    </div>
    <div class="genc-notice genc-notice--info" style="margin-bottom:16px"><i class="fa fa-info-circle"></i>
        <div>Form checklist sekarang memakai tombol <b>Baik / Tidak baik / Perawatan</b> (OK / NOK / PR) untuk semua mesin, jadi daftar ini <b>tidak lagi dipakai form mana pun</b>. Disimpan sebagai arsip; mengubahnya tidak mengubah checklist.</div></div>

    <?php if ($f): $is_add = $f['mode'] === 'add'; $e = $f['err']; ?>
    <form method="post" class="genc-card" style="margin-bottom:16px" action="<?php print_link(($is_add ? 'master_select/add' : 'master_select/edit/' . (int) $f['id']) . '?csrf_token=' . Csrf::$token); ?>">
        <div class="genc-card__head"><h2 class="genc-card__title"><?php echo $is_add ? 'Tambah pilihan' : 'Ubah pilihan #' . (int) $f['id']; ?></h2></div>
        <?php if (isset($e['_db'])): ?><div class="genc-card__body"><div class="genc-notice genc-notice--danger"><i class="fa fa-exclamation-circle"></i><div><?php echo genc_e($e['_db']); ?></div></div></div><?php endif; ?>
        <div class="genc-card__body genc-form">
            <div class="genc-field<?php echo isset($e['value']) ? ' has-error' : ''; ?>">
                <label class="genc-field__label" for="ms-v">Nilai (disimpan) <span class="genc-req">*</span></label>
                <input id="ms-v" class="genc-input genc-mono" name="value" maxlength="255" value="<?php echo genc_e($f['old']['value']); ?>" required autofocus placeholder="mis. OK">
                <?php if (isset($e['value'])): ?><span class="genc-field__err"><?php echo genc_e($e['value']); ?></span><?php endif; ?>
            </div>
            <div class="genc-field<?php echo isset($e['label']) ? ' has-error' : ''; ?>">
                <label class="genc-field__label" for="ms-l">Label (tampil) <span class="genc-req">*</span></label>
                <input id="ms-l" class="genc-input" name="label" maxlength="255" value="<?php echo genc_e($f['old']['label']); ?>" required placeholder="mis. Kondisi Baik">
                <?php if (isset($e['label'])): ?><span class="genc-field__err"><?php echo genc_e($e['label']); ?></span><?php endif; ?>
            </div>
        </div>
        <div class="genc-card__foot genc-formbar">
            <a class="genc-btn" href="<?php print_link('master_select'); ?>">Batal</a>
            <button class="genc-btn genc-btn--primary" type="submit"><i class="fa fa-check"></i> <?php echo $is_add ? 'Tambah' : 'Simpan'; ?></button>
        </div>
    </form>
    <?php endif; ?>

    <div class="genc-card">
        <?php if (empty($d['rows'])): ?>
            <div class="genc-empty"><div class="genc-empty__icon"><i class="fa fa-list-ul"></i></div><div class="genc-empty__title">Belum ada pilihan</div></div>
        <?php else: ?>
        <div class="genc-table-wrap">
        <table class="genc-atable">
            <thead><tr><th style="width:70px">ID</th><th>Nilai</th><th>Label</th><th class="genc-col-actions"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
            <?php foreach ($d['rows'] as $r): ?>
                <tr>
                    <td data-label="ID" class="genc-muted genc-mono"><?php echo (int) $r['id']; ?></td>
                    <td data-label="Nilai"><span class="genc-tag <?php echo $tone($r['value']); ?> genc-mono"><?php echo genc_e($r['value']); ?></span></td>
                    <td data-label="Label"><?php echo genc_e($r['label']); ?></td>
                    <td class="genc-col-actions">
                        <?php if ($can['edit']): ?><a class="genc-btn genc-btn--sm" href="<?php print_link('master_select/edit/' . (int) $r['id']); ?>"><i class="fa fa-pencil"></i> Ubah</a><?php endif; ?>
                        <?php if ($can['delete']): ?><a class="genc-btn genc-btn--sm genc-btn--icon genc-btn--danger" title="Hapus" aria-label="Hapus pilihan <?php echo genc_e($r['value']); ?>" href="<?php print_link('master_select/delete/' . (int) $r['id'] . '?csrf_token=' . Csrf::$token); ?>"
                           data-genc-confirm="Hapus pilihan <?php echo genc_e($r['value']); ?>?" data-genc-confirm-msg="Hanya menghapus pilihan di daftar ini. Checklist yang sudah tersimpan tidak berubah." data-genc-confirm-ok="Hapus" data-genc-danger><i class="fa fa-trash-o"></i></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <div class="genc-card__foot genc-small genc-muted"><?php echo count($d['rows']); ?> pilihan</div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php echo genc_assets_js(); ?>
