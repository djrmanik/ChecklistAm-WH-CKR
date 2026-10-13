<?php
/**
 * MASTER SELECT (Generasi C)                             [GENC-25SEP26-AUDIT]
 * [GENC-08OKT26-P3] Bagian atas: "Pilihan hasil cek" untuk SEMUA mesin (label tombol Baik / Tidak baik /
 * Perawatan; kode OK / NOK / PR di database & report tetap). Bagian bawah (dilipat): arsip pilihan form lama.
 * Cadangan tampilan lama: _backup_genc_25sept26/master_select_list_sebelum-audit.php
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data; $f = $d['form']; $can = $d['can'];
$legacy_form = $f && isset($f['mode']) && in_array($f['mode'], array('add', 'edit'), true);
$tone = function ($v) { $v = strtoupper(trim((string) $v)); return $v === 'OK' ? 'genc-tag--ok' : ($v === 'NOK' ? 'genc-tag--nok' : ($v === 'PR' ? 'genc-tag--pr' : '')); };
$pil = $d['pilihan']; $awal = $d['pilihan_awal']; $pold = $d['pil_old']; $perr = $d['pil_err'];
$meta = array('OK' => array('ok', 'fa-check', 'Kondisi sesuai standar'), 'NOK' => array('nok', 'fa-times', 'Ada kerusakan / tidak sesuai standar &mdash; dihitung temuan, keterangan wajib'), 'PR' => array('pr', 'fa-wrench', 'Perlu perawatan &mdash; dihitung temuan, keterangan wajib'));
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Master Select</span></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Master Select</h1>
            <p class="genc-subtitle">Pilihan hasil cek yang dipakai form isi checklist semua mesin.</p>
        </div>
    </div>

    <?php if ($perr): ?><div class="genc-notice genc-notice--danger" style="margin-bottom:14px"><i class="fa fa-exclamation-circle"></i><div><b>Belum tersimpan.</b> Periksa label yang ditandai merah.</div></div><?php endif; ?>

    <form method="post" class="genc-card ms-pil" action="<?php print_link('master_select/edit/pilihan?csrf_token=' . Csrf::$token); ?>" autocomplete="off" data-ms-pil>
        <div class="genc-card__head"><h2 class="genc-card__title">Pilihan hasil cek <span class="genc-small genc-muted">&middot; semua mesin</span></h2></div>
        <div class="genc-card__body">
            <p class="gm-lead" style="margin-top:0">Label tombol bisa diganti; <b>kode yang disimpan ke database &amp; dicetak di report tetap OK / NOK / PR</b>, jadi data lama, hitungan temuan, dan report PDF tidak berubah. Kosongkan = label awal.</p>
            <div class="ms-pil__rows">
            <?php foreach ($awal as $k => $a): $m = $meta[$k]; $v = $pold !== null ? $pold[$k] : ($pil[$k] !== $a ? $pil[$k] : ''); $er = isset($perr[$k]); ?>
                <div class="ms-pil__row<?php echo $er ? ' has-error' : ''; ?>">
                    <div class="ms-pil__code"><span class="genc-tag <?php echo $tone($k); ?> genc-mono" title="Kode di database &amp; report"><?php echo $k; ?></span></div>
                    <div class="ms-pil__field genc-field<?php echo $er ? ' has-error' : ''; ?>">
                        <label class="genc-field__label" for="ms-pil-<?php echo strtolower($k); ?>">Label tombol</label>
                        <input id="ms-pil-<?php echo strtolower($k); ?>" class="genc-input" name="label[<?php echo $k; ?>]" maxlength="20" value="<?php echo genc_e($v); ?>" placeholder="<?php echo genc_e($a); ?>" data-ms-in="<?php echo $k; ?>"<?php echo $can['edit'] ? '' : ' readonly'; ?>>
                        <span class="genc-field__hint"><?php echo $m[2]; ?>. Awal: <b><?php echo genc_e($a); ?></b>.</span>
                        <?php if ($er): ?><span class="genc-field__err"><?php echo genc_e($perr[$k]); ?></span><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="ms-pil__prev">
                <span class="genc-small genc-muted">Pratinjau tombol di form isi:</span>
                <div class="genc-seg ms-pil__seg" aria-hidden="true">
                    <?php foreach ($awal as $k => $a): $m = $meta[$k]; ?><label class="ms-pil__btn ms-pil__btn--<?php echo $m[0]; ?>"><i class="fa <?php echo $m[1]; ?>"></i> <span data-ms-prev="<?php echo $k; ?>" data-ms-awal="<?php echo genc_e($a); ?>"><?php echo genc_e($pold !== null && $pold[$k] !== '' ? $pold[$k] : $pil[$k]); ?></span></label><?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php if ($can['edit']): ?>
        <div class="genc-card__foot genc-formbar">
            <button class="genc-btn genc-btn--primary" type="submit"><i class="fa fa-check"></i> Simpan label</button>
        </div>
        <?php endif; ?>
    </form>

    <details class="genc-card ms-legacy"<?php echo $legacy_form ? ' open' : ''; ?>>
        <summary class="genc-card__head ms-legacy__head"><span class="genc-card__title"><i class="fa fa-archive" aria-hidden="true"></i> Arsip pilihan form lama <span class="genc-chip__count"><?php echo count($d['rows']); ?></span></span><i class="fa fa-chevron-down ms-legacy__chev" aria-hidden="true"></i></summary>
        <div class="genc-card__body" style="padding-bottom:0">
            <p class="genc-small genc-muted" style="margin:0 0 12px">Daftar dropdown form phpRAD lama (Forklift sebelum Generasi C). Tidak dipakai form mana pun lagi &mdash; disimpan sebagai arsip; mengubahnya tidak mengubah checklist.</p>
            <?php if ($can['add'] && !$legacy_form): ?><a class="genc-btn genc-btn--sm" href="<?php print_link('master_select/add'); ?>"><i class="fa fa-plus"></i> Tambah pilihan lama</a><?php endif; ?>
        </div>

        <?php if ($legacy_form): $is_add = $f['mode'] === 'add'; $e = $f['err']; ?>
        <form method="post" class="ms-legacy__form" action="<?php print_link(($is_add ? 'master_select/add' : 'master_select/edit/' . (int) $f['id']) . '?csrf_token=' . Csrf::$token); ?>">
            <div class="genc-card__body"><b><?php echo $is_add ? 'Tambah pilihan' : 'Ubah pilihan #' . (int) $f['id']; ?></b></div>
            <?php if (isset($e['_db'])): ?><div class="genc-card__body"><div class="genc-notice genc-notice--danger"><i class="fa fa-exclamation-circle"></i><div><?php echo genc_e($e['_db']); ?></div></div></div><?php endif; ?>
            <div class="genc-card__body genc-form" style="padding-top:0">
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

        <?php if (empty($d['rows'])): ?>
            <div class="genc-empty"><div class="genc-empty__icon"><i class="fa fa-list-ul"></i></div><div class="genc-empty__title">Belum ada pilihan lama</div></div>
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
                           data-genc-confirm="Hapus pilihan <?php echo genc_e($r['value']); ?>?" data-genc-confirm-msg="Hanya menghapus pilihan di arsip ini. Checklist yang sudah tersimpan tidak berubah." data-genc-confirm-ok="Hapus" data-genc-danger><i class="fa fa-trash-o"></i></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <div class="genc-card__foot genc-small genc-muted"><?php echo count($d['rows']); ?> pilihan</div>
        <?php endif; ?>
    </details>
</div>
</div>
<?php echo genc_assets_js(); ?>
<script>
(function () {   /* [GENC-08OKT26-P3] pratinjau label tombol */
    document.querySelectorAll('[data-ms-in]').forEach(function (inp) {
        var prev = document.querySelector('[data-ms-prev="' + inp.getAttribute('data-ms-in') + '"]');
        if (!prev) { return; }
        inp.addEventListener('input', function () { prev.textContent = inp.value.trim() !== '' ? inp.value.trim() : prev.getAttribute('data-ms-awal'); });
    });
})();
</script>
