<?php
/**
 * [GENC-24SEP26-FORKLIFT] Isian di luar item Baik/Tidak baik/Perawatan (conf 'extra_fields',
 * forklift: jam kerja). Di-include oleh _shared/genc/add.php - variabel $g, $d, $errors,
 * $mode, $val dari sana. Mesin tanpa 'extra_fields' tidak memuat file ini (HTML-nya tidak berubah).
 */
?>
<div class="genc-card genc-card__body genc-extra">
    <?php foreach ($g['extra_fields'] as $f):
        $fk   = $f['db'];
        $ferr = isset($errors[$fk]);
        $last = ($mode === 'add' && !empty($d['extra_last'][$fk])) ? $d['extra_last'][$fk] : null; ?>
        <div class="genc-field<?php echo $ferr ? ' has-error' : ''; ?>" data-genc-field id="item-<?php echo genc_e($fk); ?>"
             data-genc-field-msg="<?php echo genc_e('Isi ' . strtolower($f['label']) . ($f['type'] === 'number' ? ' dengan angka.' : '.')); ?>">
            <label class="genc-field__label" for="ctrl-<?php echo genc_e($fk); ?>"><?php echo genc_e($f['label']); ?><?php if ($f['required']): ?> <span class="genc-req">*</span><?php endif; ?></label>
            <div class="genc-input-suffix">
                <input class="genc-input" id="ctrl-<?php echo genc_e($fk); ?>" name="<?php echo genc_e($fk); ?>" value="<?php echo genc_e($val($fk)); ?>" autocomplete="off"
                    <?php if ($f['type'] === 'number'): ?>type="text" inputmode="decimal" pattern="[0-9]{1,9}([.,][0-9]{1,2})?"<?php else: ?>type="text"<?php endif; ?>
                    <?php echo $f['required'] ? 'required' : ''; ?> placeholder="<?php echo genc_e($f['placeholder']); ?>">
                <?php if ($f['suffix'] !== ''): ?><span class="genc-input-suffix__txt"><?php echo genc_e($f['suffix']); ?></span><?php endif; ?>
            </div>
            <?php if ($ferr): ?><div class="genc-item__err"><?php echo $errors[$fk]; /* pesan dibuat controller, sudah aman */ ?></div><?php endif; ?>
            <?php if ($f['hint'] !== '' || $last): ?>
                <div class="genc-field__hint"><?php echo genc_e($f['hint']); ?>
                    <?php if ($last): ?><span class="genc-field__last"><i class="fa fa-history"></i> Isian terakhir unit ini: <b><?php echo genc_e($last['value'] . ($f['suffix'] !== '' ? ' ' . $f['suffix'] : '')); ?></b> (<?php echo genc_e(genc_date($last['date'])); ?>)</span><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
