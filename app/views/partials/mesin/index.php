<?php
/**
 * MESIN & UNIT - explorer ala VS Code (Generasi C)      [GENC-06OKT26-MESIN]
 * Kiri: folder (= mesin di sidebar) & file (= unit / tab). Kanan: editor item yang dipilih.
 * Data dari MesinController::mesin_tree(). Gaya: checklist-genc.css bagian "MESIN & UNIT",
 * perilaku: assets/js/genc-mesin.js (data-gm-*).
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d     = $this->view_data;
$tree  = $d['tree'];
$sel   = $d['sel'];
$new   = $d['new'];
$old   = $d['old'];
$e     = $d['errors'];
$can   = $d['can'];
$csrf  = Csrf::$token;
$secs  = $d['sections'];
$sel_key   = $sel ? ($sel['type'] === 'folder' ? 'f:' . $sel['folder']['key'] : $sel['unit']['sel']) : '';
$open_key  = $sel ? $sel['folder']['key'] : ($new === 'unit' ? $d['new_folder'] : '');
$err = function ($k) use ($e) { return isset($e[$k]) ? '<span class="genc-field__err">' . $e[$k] . '</span>' : ''; };
$fcls = function ($k, $extra = '') use ($e) { return 'genc-field' . ($extra !== '' ? ' ' . $extra : '') . (isset($e[$k]) ? ' has-error' : ''); };
$oval = function ($k, $def = '') use ($old) { return array_key_exists($k, $old) ? (string) $old[$k] : (string) $def; };
$url  = function ($q) { return genc_url('mesin', $q); };
$units_total = 0; $units_off = 0; $mesin_off = 0; $mesin_total = 0; $arsip_n = 0;
foreach ($tree as $f) {
    if (!empty($f['hapus'])) { continue; }                 // [GENC-13OKT26] dihapus permanen: tidak ada di mana pun
    if (!empty($f['arsip'])) { $arsip_n++; continue; }   // [GENC-08OKT26-P3] arsip tidak dihitung
    $mesin_total++;
    if (empty($f['aktif'])) { $mesin_off++; }
    foreach ($f['units'] as $u) { if (!empty($u['hapus'])) { continue; } if (!empty($u['arsip'])) { $arsip_n++; continue; } $units_total++; if (!$u['aktif']) { $units_off++; } }
}
$cad_list = isset($d['cad_list']) ? $d['cad_list'] : array();   // [GENC-13OKT26] cadangan hapus permanen
$panel = isset($d['panel']) ? $d['panel'] : '';
$live_units = function ($f) { return array_values(array_filter($f['units'], function ($u) { return empty($u['hapus']); })); };
/** [GENC-06OKT26-ISI] status kunci folder: 'all' | 'some' | '' */
$folder_lock = function ($f) {
    if ($f['jenis']) { return genc_reg_locked($f['key']) ? 'all' : ''; }
    $ps = $f['mode'] === 'units' ? array_map(function ($t) { return $t['profile']; }, $f['def']['types']) : array($f['def']['profile']);
    $n = 0; foreach ($ps as $p) { if (genc_reg_locked($p)) { $n++; } }
    return $n === 0 ? '' : ($n === count($ps) ? 'all' : 'some');
};
/** [GENC-08OKT26-P3] Masih ada unit aktif lain di folder ini? */
$this_other_active = function ($f, $except) { foreach ($f['units'] as $x) { if ($x['sel'] !== $except && $x['aktif']) { return true; } } return false; };
$mode_txt = function ($f) {   // [GENC-08OKT26-MESIN] semua mesin diperlakukan sama (tanpa "bawaan" / "jenis baru")
    return $f['mode'] === 'units' ? '1 tabel, unit = tab' : 'Tiap unit punya tabel &amp; tab sendiri';
};
/** [GENC-08OKT26-MESIN] Status folder: 'draf' (belum ada item) | 'nonaktif' | 'baru' (<= GENC_REG_NEW_DAYS hari) | '' */
$folder_badge = function ($f) {
    if ($f['jenis'] && $f['items'] === 0) { return 'draf'; }
    if (empty($f['aktif'])) { return 'nonaktif'; }
    return genc_reg_is_new($f['created_at']) ? 'baru' : '';
};
/** [GENC-08OKT26-MESIN] Pemilih ikon (80 ikon Font Awesome 4.5 + cari). $awal = ikon awal mesin (ditandai). */
$icon_picker = function ($name, $cur, $awal = '') {
    $words = genc_reg_icon_words();
    ob_start(); ?>
<div class="gm-iconbox" data-gm-iconbox>
    <div class="gm-iconbox__bar">
        <span class="gm-iconbox__cur" aria-hidden="true"><i class="fa <?php echo genc_e($cur); ?>" data-gm-iconcur></i></span>
        <div class="gm-iconbox__find"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari ikon, mis. truk, baterai, gir" aria-label="Cari ikon" data-gm-iconfind autocomplete="off"></div>
        <span class="genc-small genc-muted" data-gm-iconn><?php echo count($words); ?> ikon</span>
    </div>
    <div class="gm-icons" role="radiogroup" aria-label="Ikon">
    <?php foreach ($words as $ic => $w): ?>
        <label class="gm-iconpick<?php echo $ic === $awal ? ' is-awal' : ''; ?>" title="<?php echo genc_e(substr($ic, 3) . ($ic === $awal ? ' (ikon awal)' : '')); ?>" data-gm-iconwords="<?php echo genc_e(strtolower(substr($ic, 3) . ' ' . $w)); ?>"><input type="radio" name="<?php echo genc_e($name); ?>" value="<?php echo genc_e($ic); ?>"<?php echo $cur === $ic ? ' checked' : ''; ?>><span><i class="fa <?php echo genc_e($ic); ?>"></i></span></label>
    <?php endforeach; ?>
    </div>
    <p class="gm-iconbox__none genc-small genc-muted" data-gm-iconnone hidden>Tidak ada ikon yang cocok.</p>
</div>
<?php return ob_get_clean();
};
/** Baris item di editor jenis mesin. */
$item_row = function ($sk, $idx, $it, $no) {
    $db   = isset($it['db']) ? (string) $it['db'] : '';
    $key  = $db !== '' ? $db : (isset($it['key']) && $it['key'] !== '' ? $it['key'] : $idx);
    $del  = isset($it['aktif']) && !$it['aktif'];
    $fotos = array_values(array_filter(genc_reg_fotos_of($it), function ($p) { return is_file(ROOT . $p); }));   // [GENC-07OKT26-FOTO]
    $n    = 'items[' . $sk . '][' . $idx . ']';
    ob_start(); ?>
<li class="gm-item<?php echo $del ? ' is-deleted' : ''; ?><?php echo $db === '' ? ' is-new' : ''; ?>" data-gm-item>
    <input type="hidden" name="<?php echo $n; ?>[db]" value="<?php echo genc_e($db); ?>">
    <input type="hidden" name="<?php echo $n; ?>[key]" value="<?php echo genc_e($key); ?>">
    <span class="gm-item__no" data-gm-no><?php echo (int) $no; ?></span>
    <div class="gm-item__photo" data-gm-fotos data-gm-key="<?php echo genc_e($key); ?>">
        <input type="hidden" name="<?php echo $n; ?>[fotos_sent]" value="1">
        <div class="gm-fotos<?php echo $fotos ? ' has-img' : ''; ?><?php echo count($fotos) > 1 ? ' has-many' : ''; ?>" data-gm-fotolist>
            <?php foreach ($fotos as $fi => $fp): ?>
            <div class="gm-foto<?php echo $fi === 0 ? ' is-main' : ''; ?>" data-gm-foto title="<?php echo $fi === 0 ? 'Foto utama' : 'Klik: jadikan foto utama'; ?>">
                <img src="<?php echo genc_e(set_url($fp)); ?>" alt="">
                <input type="hidden" name="<?php echo $n; ?>[fotos][]" value="<?php echo genc_e($fp); ?>" data-gm-fotopath>
                <button type="button" class="gm-foto__x" data-gm-foto-x title="Hapus foto ini" aria-label="Hapus foto">&times;</button>
            </div>
            <?php endforeach; ?>
            <label class="gm-foto gm-foto--add" title="Tambah foto (boleh beberapa sekaligus, maks. <?php echo GENC_REG_MAX_FOTO; ?>)"<?php echo count($fotos) >= GENC_REG_MAX_FOTO ? ' hidden' : ''; ?>>
                <i class="fa fa-camera" aria-hidden="true"></i><small>Foto</small>
                <input type="file" name="foto[<?php echo genc_e($key); ?>][]" multiple accept="image/jpeg,image/png,image/webp,image/gif" data-gm-photo aria-label="Tambah foto part">
            </label>
        </div>
        <span class="gm-fotos__n" data-gm-fotocount><?php echo count($fotos) ? count($fotos) . '/' . GENC_REG_MAX_FOTO . ' foto' : ''; ?></span>
    </div>
    <div class="gm-item__fields">
        <input class="genc-input gm-in-part" name="<?php echo $n; ?>[part]" value="<?php echo genc_e(isset($it['part']) ? $it['part'] : ''); ?>" maxlength="80" placeholder="Nama part *" aria-label="Nama part">
        <input class="genc-input" name="<?php echo $n; ?>[standar]" value="<?php echo genc_e(isset($it['standar']) ? $it['standar'] : ''); ?>" maxlength="150" placeholder="Standar (kondisi yang benar)" aria-label="Standar">
        <div class="gm-item__more">
            <input class="genc-input" name="<?php echo $n; ?>[metode]" value="<?php echo genc_e(isset($it['metode']) ? $it['metode'] : ''); ?>" maxlength="100" placeholder="Metode" aria-label="Metode">
            <input class="genc-input" name="<?php echo $n; ?>[alat]" value="<?php echo genc_e(isset($it['alat']) ? $it['alat'] : ''); ?>" maxlength="80" placeholder="Alat" aria-label="Alat">
            <input class="genc-input" name="<?php echo $n; ?>[durasi]" value="<?php echo genc_e(isset($it['durasi']) ? $it['durasi'] : ''); ?>" maxlength="30" placeholder="Durasi" aria-label="Durasi">
        </div>
        <?php if ($del): ?><span class="gm-item__deltxt"><i class="fa fa-info-circle"></i> Dihapus &mdash; data lama tetap tersimpan. Hilangkan centang <i class="fa fa-trash-o"></i> untuk memulihkan.</span><?php endif; ?>
    </div>
    <div class="gm-item__ctl">
        <button type="button" class="gm-ibtn" data-gm-up title="Naikkan" aria-label="Naikkan"><i class="fa fa-arrow-up"></i></button>
        <button type="button" class="gm-ibtn" data-gm-down title="Turunkan" aria-label="Turunkan"><i class="fa fa-arrow-down"></i></button>
        <label class="gm-ibtn gm-ibtn--del" title="Hapus item"><input type="checkbox" name="<?php echo $n; ?>[hapus]" value="1" data-gm-del<?php echo $del ? ' checked' : ''; ?>><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">Hapus item</span></label>
    </div>
</li>
<?php return ob_get_clean();
};
/** [GENC-06OKT26-ISI + GENC-08OKT26-P3] Bilah kunci SELURUH mesin (pengaturan, unit, isi checklist). $state 'all' | 'some' | '' */
$lock_bar = function ($isi, $f, $state) use ($can, $csrf) {
    $lk = $state === 'all';
    ob_start(); ?>
<div class="genc-card gm-lock<?php echo $lk ? ' is-locked' : ''; ?>" id="kunci">
    <div class="gm-lock__ico" aria-hidden="true"><i class="fa <?php echo $lk ? 'fa-lock' : 'fa-unlock-alt'; ?>"></i></div>
    <div class="gm-lock__txt">
        <b><?php echo $lk ? 'Mesin ini dikunci' : ($state === 'some' ? 'Sebagian isi checklist dikunci' : 'Mesin ini bisa diubah'); ?></b>
        <span><?php if ($lk): ?>Pengaturan, unit, item, foto &amp; gambar report <?php echo genc_e($f['title']); ?> tidak bisa diubah sampai kuncinya dibuka (saklar Aktif tetap bisa)<?php echo $isi['kunci_by'] !== '' ? ' &middot; dikunci ' . genc_e(genc_person_name($isi['kunci_by'])) . ', ' . genc_e(genc_date($isi['kunci_at'])) : ''; ?>.<?php else: ?>Kunci kalau semuanya sudah final (sudah dicek SPV) supaya tidak berubah tanpa sengaja.<?php echo (!$lk && $state === '' && $isi['kunci_by'] !== '') ? ' Kunci dibuka ' . genc_e(genc_person_name($isi['kunci_by'])) . ', ' . genc_e(genc_date($isi['kunci_at'])) . '.' : ''; ?><?php endif; ?></span>
    </div>
    <?php if ($can['edit'] && $isi['ready'] && empty($f['arsip'])): ?>
    <form method="post" action="<?php print_link('mesin/edit/kunci/' . rawurlencode($isi['profile']) . '?csrf_token=' . $csrf); ?>" class="gm-lock__act">
        <input type="hidden" name="kunci" value="<?php echo $lk ? '0' : '1'; ?>">
        <?php if ($lk): ?>
        <button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Buka kunci <?php echo genc_e($f['title']); ?>?" data-genc-confirm-msg="Pengaturan, unit &amp; isi checklist bisa diubah lagi oleh role yang punya akses Mesin &amp; Unit. Kunci lagi setelah selesai." data-genc-confirm-ok="Buka kunci"><i class="fa fa-unlock-alt"></i> Buka kunci</button>
        <?php else: ?>
        <button type="submit" class="genc-btn genc-btn--sm gm-lock__btn"><i class="fa fa-lock"></i> <?php echo $state === 'some' ? 'Kunci semua' : 'Kunci mesin'; ?></button>
        <?php endif; ?>
    </form>
    <?php endif; ?>
</div>
<?php return ob_get_clean();
};
/**
 * [GENC-06OKT26-ISI + GENC-07OKT26-FOTO] Kartu gambar bagian mesin (kolom kiri report), per bagian.
 * Otomatis = foto item bagian itu disusun & diberi nomor item (kolase); Gambar sendiri = 1 gambar utuh.
 * Bagian tanpa item tidak ditampilkan (gambarnya juga hilang dari report). $edit = bisa diubah.
 */
$gambar_card = function ($isi, $edit) {
    if (empty($isi['gsecs'])) { return ''; }
    $max_x = GENC_REG_MAX_EXTRA;
    ob_start(); ?>
<div class="genc-card gm-gcard" data-gm-gcard<?php echo $edit ? '' : ' data-gm-ro'; ?>>
    <div class="genc-card__head"><h3 class="genc-card__title">Gambar bagian mesin</h3><span class="genc-small genc-muted">kolom kiri report PDF / print</span></div>
    <div class="genc-card__body">
        <?php foreach ($isi['gsecs'] as $sk => $g):
            $k = $g['slot']; $hide = array_flip($g['hide']);
            $no_of = array(); $tiles = array();
            foreach ($g['items'] as $it) {
                if (!empty($it['db'])) { $no_of[$it['db']] = (int) $it['no']; }
                foreach (isset($it['fotos']) ? $it['fotos'] : (!empty($it['foto']) ? array($it['foto']) : array()) as $fp) { if (is_file(ROOT . $fp)) { $tiles[] = array('path' => $fp, 'no' => (int) $it['no'], 'x' => false); } }
            }
            $extras = array();
            foreach ($g['extra'] as $x) {
                if (!is_file(ROOT . $x['path'])) { continue; }
                $extras[] = array('path' => $x['path'], 'src' => set_url($x['path']), 'db' => $x['db']);
                if (isset($no_of[$x['db']])) { $tiles[] = array('path' => $x['path'], 'no' => $no_of[$x['db']], 'x' => true); }
            }
            usort($tiles, function ($a, $b) { return $a['no'] - $b['no']; });
            $show = !empty($g['items']) || $g['always'];
            $panel_only = empty($g['items']) && $g['always'];
            $mode = $panel_only ? 'panel' : $g['mode'];
            $st = $g['cur'] === '' ? 'Belum ada gambar' : ($g['is_ov'] ? 'Diunggah lewat menu' : 'Gambar awal'); ?>
        <section class="gm-gsec" data-gm-gsec="<?php echo genc_e($sk); ?>" data-gm-slot="<?php echo genc_e($k); ?>" data-gm-always="<?php echo $g['always'] ? '1' : '0'; ?>"
            data-gm-hide="<?php echo genc_e(json_encode(array_values($g['hide']))); ?>" data-gm-extra="<?php echo genc_e(json_encode($extras)); ?>"<?php echo $show ? '' : ' hidden'; ?>>
            <div class="gm-gsec__head">
                <b><?php echo genc_e($g['label']); ?></b>
                <?php if ($edit && !$panel_only): ?>
                <div class="gm-seg" role="radiogroup" aria-label="Sumber gambar <?php echo genc_e($g['label']); ?>" data-gm-seg>
                    <label><input type="radio" name="gambar_mode[<?php echo genc_e($k); ?>]" value="auto" data-gm-mode<?php echo $mode === 'auto' ? ' checked' : ''; ?>><span><i class="fa fa-th-large" aria-hidden="true"></i> Otomatis dari foto item</span></label>
                    <label><input type="radio" name="gambar_mode[<?php echo genc_e($k); ?>]" value="panel" data-gm-mode<?php echo $mode === 'panel' ? ' checked' : ''; ?>><span><i class="fa fa-picture-o" aria-hidden="true"></i> Gambar sendiri</span></label>
                </div>
                <?php else: ?>
                <span class="genc-tag"><?php echo $mode === 'auto' ? '<i class="fa fa-th-large"></i> Otomatis dari foto item' : '<i class="fa fa-picture-o"></i> Gambar sendiri'; ?></span>
                <?php endif; ?>
            </div>
            <div class="gm-gsec__auto" data-gm-auto<?php echo $mode === 'auto' ? '' : ' hidden'; ?>>
                <?php if ($edit): ?><input type="hidden" name="gambar_hide_sent[<?php echo genc_e($k); ?>]" value="1"><?php endif; ?>
                <div class="gm-kolase" data-gm-kolase>
                <?php foreach ($tiles as $t): $off = isset($hide[$t['path']]); ?>
                    <figure class="gm-kt<?php echo $off ? ' is-off' : ''; ?><?php echo $t['x'] ? ' is-extra' : ''; ?>">
                        <img src="<?php echo genc_e(set_url($t['path'])); ?>" alt="">
                        <span class="gm-kt__no"><?php echo (int) $t['no']; ?></span>
                        <?php if ($edit): ?>
                        <label class="gm-kt__btn gm-kt__eye" title="Tampil / sembunyikan di report"><input type="checkbox" name="gambar_hide[<?php echo genc_e($k); ?>][]" value="<?php echo genc_e($t['path']); ?>"<?php echo $off ? ' checked' : ''; ?>><i class="fa fa-eye" aria-hidden="true"></i><span class="sr-only">Sembunyikan dari report</span></label>
                        <?php if ($t['x']): ?><label class="gm-kt__btn gm-kt__del" title="Hapus foto tambahan"><input type="checkbox" name="gambar_extra_del[<?php echo genc_e($k); ?>][]" value="<?php echo genc_e($t['path']); ?>"><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sr-only">Hapus foto tambahan</span></label><?php endif; ?>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
                </div>
                <p class="gm-kolase__empty" data-gm-kempty<?php echo $tiles ? ' hidden' : ''; ?>><i class="fa fa-camera" aria-hidden="true"></i> Belum ada foto item di bagian ini.</p>
                <?php if ($edit): ?>
                <div class="gm-xadd" data-gm-xadd data-gm-xmax="<?php echo $max_x; ?>">
                    <div class="gm-xrows" data-gm-xrows></div>
                    <button type="button" class="genc-btn genc-btn--sm genc-btn--ghost" data-gm-xadd-btn><i class="fa fa-plus"></i> Foto tambahan</button>
                    <template data-gm-xtpl><div class="gm-xrow" data-gm-xrow>
                        <select class="genc-select" name="gambar_extra_db[<?php echo genc_e($k); ?>___N__]" aria-label="Untuk item nomor" data-gm-xsel><?php foreach ($g['items'] as $it): ?><option value="<?php echo genc_e($it['db']); ?>"><?php echo (int) $it['no'] . '. ' . genc_e($it['part']); ?></option><?php endforeach; ?></select>
                        <label class="genc-btn genc-btn--sm gm-xfile"><i class="fa fa-camera" aria-hidden="true"></i> <span data-gm-xname>Pilih foto</span><input type="file" name="gambar_extra_file[<?php echo genc_e($k); ?>___N__][]" multiple accept="image/jpeg,image/png,image/webp,image/gif" data-gm-xfile></label>
                        <button type="button" class="gm-ibtn" data-gm-xdel title="Batal" aria-label="Batal"><i class="fa fa-times"></i></button>
                    </div></template>
                </div>
                <?php endif; ?>
            </div>
            <div class="gm-gsec__panel" data-gm-panel<?php echo $mode === 'panel' ? '' : ' hidden'; ?>>
                <div class="gm-gslot">
                    <?php if ($edit): ?>
                    <label class="gm-photo gm-photo--lg<?php echo $g['cur'] !== '' ? ' has-img' : ''; ?>" title="Pilih gambar (JPG / PNG)">
                        <?php if ($g['cur'] !== ''): ?><img src="<?php echo genc_e(set_url($g['cur'])); ?>" alt=""><?php endif; ?>
                        <span class="gm-photo__ph"><i class="fa fa-picture-o" aria-hidden="true"></i><small><?php echo $g['cur'] !== '' ? 'Ganti' : 'Unggah'; ?></small></span>
                        <input type="file" name="gambar[<?php echo genc_e($k); ?>]" accept="image/jpeg,image/png,image/webp,image/gif" data-gm-photo aria-label="Gambar <?php echo genc_e($g['label']); ?>">
                    </label>
                    <?php else: ?>
                    <span class="gm-photo gm-photo--lg gm-photo--ro<?php echo $g['cur'] !== '' ? ' has-img' : ''; ?>"><?php if ($g['cur'] !== ''): ?><img src="<?php echo genc_e(set_url($g['cur'])); ?>" alt=""><?php else: ?><span class="gm-photo__ph"><i class="fa fa-picture-o" aria-hidden="true"></i></span><?php endif; ?></span>
                    <?php endif; ?>
                    <div class="gm-gslot__txt">
                        <small><?php echo $st; ?></small>
                        <?php if ($edit):
                            $h = $g['is_ov'] ? ($g['orig'] !== '' ? 'kembalikan gambar awal' : 'hapus gambar') : ($g['cur'] !== '' ? 'sembunyikan gambar' : '');
                            if ($h !== ''): ?><label class="gm-mini"><input type="checkbox" name="gambar_hapus[<?php echo genc_e($k); ?>]" value="1" data-gm-photo-del> <?php echo $h; ?></label><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endforeach; ?>
        <p class="gm-gcard__none genc-small genc-muted" data-gm-gnone hidden>Belum ada bagian yang punya item.</p>
    </div>
</div>
<?php return ob_get_clean();
};
/**
 * [GENC-13OKT26] Kartu HAPUS PERMANEN (hanya untuk yang ada di Arsip). Dilipat (<details>) supaya tidak terpencet;
 * konfirmasi = ketik nama persis (dicek lagi di server). Cadangan .zip dibuat dulu - gagal = batal.
 */
$purge_card = function ($pl, $selv) use ($csrf) {
    if (!$pl) { return ''; }
    $n = function ($x) { return number_format((int) $x, 0, ',', '.'); };
    $is_m = $pl['kind'] === 'mesin';
    $gone = array(); $keep = array();
    if ($pl['builtin']) {
        $gone[] = ($is_m ? 'Mesin' : 'Unit') . ' hilang <b>untuk selamanya</b> dari ' . ($is_m ? 'menu, Home, Approval / NOK History, Mesin &amp; Unit &amp; Role Permissions' : 'tab, Home, Approval / NOK History &amp; Mesin &amp; Unit');
        if ($is_m) {
            if (!empty($pl['registry']['genc_profil']['rows'])) { $gone[] = 'Isi checklist yang diubah lewat menu, gambar report &amp; kunci'; }
            if ($pl['n_foto']) { $gone[] = $n($pl['n_foto']) . ' foto item &amp; gambar report'; }
            if ($pl['n_perm']) { $gone[] = 'Hak akses (' . $n($pl['n_perm']) . ' baris)'; }
            if ($pl['n_versi']) { $gone[] = 'Riwayat perubahan (' . $n($pl['n_versi']) . ' versi)'; }
            if ($pl['unit_ids']) { $gone[] = count($pl['unit_ids']) . ' unit tambahan lewat menu' . ($pl['n_hapus'] ? ' + ' . $n($pl['n_hapus']) . ' checklist-nya' : ''); }
        }
        $keep[] = '<b>' . $n($pl['n_tetap']) . ' checklist</b> di tabel asli tetap tersimpan di database (tidak bisa dibuka lagi dari aplikasi)';
        $keep[] = $is_m ? 'Halaman mesin ini memang ditulis di kode aplikasi &mdash; file kodenya tidak disentuh' : 'Nama unit ini tidak bisa dipakai unit baru di mesin ini (supaya data lamanya tidak ikut)';
    } else {
        $gone[] = '<b>' . $n($pl['n_hapus']) . ' checklist</b>' . ($is_m ? ' semua unit' : ' unit ini');
        if ($pl['drop']) { $gone[] = 'Tabel data <span class="genc-mono">' . genc_e(implode(', ', $pl['drop'])) . '</span>'; }
        if ($is_m) {
            $gone[] = $n($pl['n_unit']) . ' unit, isi checklist &amp; pengaturan mesin';
            if ($pl['n_foto']) { $gone[] = $n($pl['n_foto']) . ' foto item &amp; gambar report'; }
            if ($pl['n_versi']) { $gone[] = 'Riwayat perubahan (' . $n($pl['n_versi']) . ' versi)'; }
        }
        if ($pl['n_perm']) { $gone[] = 'Hak akses (' . $n($pl['n_perm']) . ' baris)'; }
    }
    $keep[] = 'Catatan di App Logs (siapa menghapus, kapan)';
    ob_start(); ?>
<details class="genc-card gm-purge" id="hapus-permanen" data-gm-purge>
    <summary class="gm-purge__sum">
        <span class="gm-purge__ico" aria-hidden="true"><i class="fa fa-trash"></i></span>
        <span class="gm-purge__ttl"><b>Hapus permanen</b><small>Seperti mengosongkan tong sampah: tidak bisa dipulihkan dari menu. File cadangan dibuat otomatis lebih dulu.</small></span>
        <i class="fa fa-chevron-down gm-purge__chev" aria-hidden="true"></i>
    </summary>
    <form method="post" action="<?php print_link('mesin/delete/permanen/x?csrf_token=' . $csrf); ?>" class="gm-purge__body" autocomplete="off" novalidate data-gm-purge-form>
        <input type="hidden" name="sel" value="<?php echo genc_e($selv); ?>">
        <div class="gm-purge__cols">
            <div class="gm-purge__col gm-purge__col--gone"><h4><i class="fa fa-times-circle" aria-hidden="true"></i> Yang dihapus</h4><ul><?php foreach ($gone as $x) { echo '<li>' . $x . '</li>'; } ?></ul></div>
            <div class="gm-purge__col gm-purge__col--keep"><h4><i class="fa fa-check-circle" aria-hidden="true"></i> Yang tetap</h4><ul><?php foreach ($keep as $x) { echo '<li>' . $x . '</li>'; } ?></ul></div>
        </div>
        <div class="gm-purge__bk"><i class="fa fa-file-archive-o" aria-hidden="true"></i><div><b>File cadangan (.zip) dibuat dulu</b> &mdash; report PDF tiap unit per bulan, Excel data mentah, foto &amp; file pemulihan (.sql). Langsung terunduh &amp; tersimpan di <i>Arsip &rsaquo; Riwayat hapus permanen</i>. Kalau cadangan gagal dibuat, penghapusan <b>dibatalkan</b>.</div></div>
        <div class="genc-field gm-purge__confirm">
            <label class="genc-field__label" for="gm-purge-name">Ketik <b class="gm-purge__name"><?php echo genc_e($pl['name']); ?></b> untuk konfirmasi</label>
            <input id="gm-purge-name" class="genc-input" name="konfirmasi" maxlength="120" placeholder="<?php echo genc_e($pl['name']); ?>" autocomplete="off" autocapitalize="off" spellcheck="false" data-gm-purge-name="<?php echo genc_e($pl['name']); ?>">
        </div>
        <div class="gm-purge__bar">
            <span class="gm-purge__wait" data-gm-purge-wait hidden><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Membuat file cadangan (report PDF, Excel, foto)&hellip; jangan tutup halaman ini.</span>
            <button type="submit" class="genc-btn genc-btn--danger" data-gm-purge-btn><i class="fa fa-trash"></i> Hapus permanen</button>
        </div>
    </form>
</details>
<?php return ob_get_clean();
};
/** [GENC-13OKT26] Satu baris versi di Riwayat perubahan. $full = layar penuh (dengan "apa yang berubah"). */
$ver_li = function ($v, $f, $can_restore, $why_no, $full, $cur_no) use ($csrf) {
    $cur = !empty($v['is_cur']);
    ob_start(); ?>
<li class="gm-ver<?php echo $cur ? ' is-current' : ''; ?><?php echo !$cur && (int) $v['versi'] > $cur_no ? ' is-newer' : ''; ?>" data-gm-ver data-gm-vtext="<?php echo genc_e(strtolower('v' . $v['versi'] . ' ' . $v['ringkasan'] . ' ' . genc_person_name($v['created_by']) . ' ' . html_entity_decode(implode(' ', array_map('strip_tags', $v['diff'])), ENT_QUOTES, 'UTF-8'))); ?>">
    <span class="gm-ver__no">v<?php echo (int) $v['versi']; ?></span>
    <span class="gm-ver__txt"><b><?php echo genc_e($v['ringkasan']); ?></b><small><?php echo genc_e(genc_person_name($v['created_by'])); ?> &middot; <?php echo genc_e(genc_date($v['created_at'])); ?> <?php echo genc_e(genc_time($v['created_at'])); ?><?php echo $full && !empty($v['dari']) && (int) $v['dari'] !== (int) $v['versi'] - 1 ? ' &middot; <span class="gm-ver__from" title="Dibuat sesudah Kembalikan ke versi ' . (int) $v['dari'] . '">dari v' . (int) $v['dari'] . '</span>' : ''; ?></small>
    <?php if ($full): ?>
        <?php if ($v['diff']): ?><ul class="gm-ver__diff"><?php foreach ($v['diff'] as $x) { echo '<li>' . str_replace(array('&amp;rarr;', '&amp;minus;'), array('&rarr;', '&minus;'), genc_e($x)) . '</li>'; } ?></ul>
        <?php elseif (!empty($v['first'])): ?><span class="gm-ver__diff gm-ver__diff--none">Versi paling awal yang tercatat.</span>
        <?php else: ?><span class="gm-ver__diff gm-ver__diff--none">Tidak ada beda isi dengan versi sebelumnya.</span><?php endif; ?>
    <?php endif; ?>
    </span>
    <?php if ($cur): ?><span class="genc-tag genc-tag--ok">sekarang</span>
    <?php elseif ($can_restore): ?>
    <form method="post" action="<?php print_link('mesin/edit/versi/' . (int) $v['id'] . '?csrf_token=' . $csrf . ($full ? '&dari=penuh' : '')); ?>">
        <button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Kembalikan <?php echo genc_e($f['title']); ?> ke versi <?php echo (int) $v['versi']; ?>?" data-genc-confirm-msg="Pengaturan, unit, isi checklist, foto &amp; gambar report kembali seperti versi <?php echo (int) $v['versi']; ?>. Tidak ada versi baru: tanda &quot;sekarang&quot; pindah ke versi <?php echo (int) $v['versi']; ?>, versi lain tetap bisa dipilih lagi. Data checklist tidak berubah." data-genc-confirm-ok="Ya, kembalikan"><i class="fa fa-undo"></i> Kembalikan</button>
    </form>
    <?php elseif ($why_no !== ''): ?><span class="gm-ver__why" title="<?php echo genc_e($why_no); ?>"><i class="fa fa-lock" aria-hidden="true"></i></span>
    <?php endif; ?>
</li>
<?php return ob_get_clean();
};
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Admin &amp; sistem</span><span>/</span><a href="<?php print_link('mesin'); ?>">Mesin &amp; Unit</a></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Mesin &amp; Unit</h1>
            <p class="genc-subtitle">Tambah unit atau mesin baru tanpa coding &mdash; langsung jadi tab, menu, form isi, dan report.</p>
        </div>
        <?php if ($can['add']): ?>
        <div class="genc-actions">
            <a class="genc-btn" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $open_key))); ?>"><span class="gm-ico2"><i class="fa fa-file-o"></i><i class="fa fa-plus"></i></span> Unit baru</a>
            <a class="genc-btn genc-btn--primary" href="<?php echo genc_e($url(array('baru' => 'jenis'))); ?>"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span> Mesin baru</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($d['setup_err'] !== ''): ?>
        <div class="genc-notice genc-notice--danger" style="margin-bottom:16px"><i class="fa fa-exclamation-circle"></i><div><b>Tabel registry belum bisa dibuat otomatis.</b> Jalankan <span class="genc-mono">database/genc_06okt26.sql</span> di phpMyAdmin, lalu buka halaman ini lagi.<br><span class="genc-small"><?php echo genc_e($d['setup_err']); ?></span></div></div>
    <?php endif; ?>

    <div class="gm" data-gm>
        <!-- ================= EXPLORER ================= -->
        <aside class="gm-tree genc-card" aria-label="Mesin &amp; unit">
            <div class="gm-tree__head">
                <span class="gm-tree__title" title="Autonomous Maintenance">Semua mesin</span>
                <div class="gm-tree__tools">
                    <?php if ($can['add']): ?>
                    <a class="gm-tool" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $open_key))); ?>" title="Unit baru<?php echo $open_key !== '' && isset($tree[$open_key]) ? ' di ' . genc_e($tree[$open_key]['title']) : ''; ?>" aria-label="Unit baru"><span class="gm-ico2"><i class="fa fa-file-o"></i><i class="fa fa-plus"></i></span></a>
                    <a class="gm-tool" href="<?php echo genc_e($url(array('baru' => 'jenis'))); ?>" title="Mesin baru (folder baru)" aria-label="Mesin baru"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span></a>
                    <?php endif; ?>
                    <button type="button" class="gm-tool" data-gm-collapse title="Tutup semua folder" aria-label="Tutup semua folder"><i class="fa fa-minus-square-o"></i></button>
                </div>
            </div>
            <div class="gm-tree__find"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari mesin / unit" aria-label="Cari mesin atau unit" data-gm-filter autocomplete="off"></div>
            <div class="gm-tree__body" role="tree">
            <?php $arch = array();   /* [GENC-08OKT26-P3] mesin / unit di arsip -> bagian Arsip di bawah */
            foreach ($tree as $fk => $f):
                if (!empty($f['hapus'])) { continue; }   // [GENC-13OKT26]
                if (!empty($f['arsip'])) { $arch[] = array('sel' => 'f:' . $fk, 'icon' => $f['icon'], 'name' => $f['title'], 'sub' => count($live_units($f)) . ' unit', 'folder' => true); continue; }
                foreach ($f['units'] as $u) { if (!empty($u['arsip']) && empty($u['hapus'])) { $arch[] = array('sel' => $u['sel'], 'icon' => 'fa-file-text-o', 'name' => $u['label'], 'sub' => $f['title'], 'folder' => false); } }
                $f_units_vis = array_values(array_filter($f['units'], function ($u) { return empty($u['arsip']); }));
                $f_sel = ($sel_key === 'f:' . $fk);
                $f_badge = $folder_badge($f);
                $f_off = $f_badge === 'draf' || $f_badge === 'nonaktif';
                $f_lock = $folder_lock($f); ?>
                <details class="gm-folder" data-gm-folder open>
                    <summary class="gm-row gm-row--folder<?php echo $f_sel ? ' is-selected' : ''; ?><?php echo $f_off ? ' is-off' : ''; ?>" role="treeitem" aria-selected="<?php echo $f_sel ? 'true' : 'false'; ?>" data-gm-name="<?php echo genc_e(strtolower($f['title'])); ?>">
                        <span class="gm-chev" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                        <a class="gm-row__main" href="<?php echo genc_e($url(array('pilih' => 'f:' . $fk))); ?>"><i class="fa <?php echo genc_e($f['icon']); ?> gm-ico" aria-hidden="true"></i><span class="gm-name"><?php echo genc_e($f['title']); ?></span></a>
                        <?php if ($f_badge !== ''): ?><span class="gm-badge gm-badge--<?php echo $f_badge === 'baru' ? 'new' : 'off'; ?>" title="<?php echo $f_badge === 'baru' ? 'Dibuat ' . genc_e(genc_date($f['created_at'])) . ' (label hilang sendiri setelah ' . GENC_REG_NEW_DAYS . ' hari)' : ($f_badge === 'draf' ? 'Belum ada item checklist' : 'Disembunyikan dari menu &amp; Home'); ?>"><?php echo $f_badge; ?></span><?php endif; ?>
                        <?php if ($f_lock !== ''): ?><i class="fa fa-lock gm-flag gm-flag--lock<?php echo $f_lock === 'some' ? ' is-some' : ''; ?>" title="<?php echo $f_lock === 'some' ? 'Sebagian dikunci' : 'Dikunci'; ?>" aria-label="Dikunci"></i><?php endif; ?>
                        <span class="gm-count" title="<?php echo count($f_units_vis); ?> unit"><?php echo count($f_units_vis); ?></span>
                        <?php if ($can['add']): ?><a class="gm-row__act" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $fk))); ?>" title="Unit baru di <?php echo genc_e($f['title']); ?>" aria-label="Unit baru di <?php echo genc_e($f['title']); ?>"><i class="fa fa-plus"></i></a><?php endif; ?>
                    </summary>
                    <ul class="gm-files" role="group">
                    <?php foreach ($f_units_vis as $u): $u_sel = ($sel_key === $u['sel']); ?>
                        <li><a class="gm-row gm-row--file<?php echo $u_sel ? ' is-selected' : ''; ?><?php echo !$u['aktif'] ? ' is-off' : ''; ?>" href="<?php echo genc_e($url(array('pilih' => $u['sel']))); ?>" role="treeitem" aria-selected="<?php echo $u_sel ? 'true' : 'false'; ?>" data-gm-name="<?php echo genc_e(strtolower($u['label'] . ' ' . $u['tab'] . ' ' . $u['type'] . ' ' . $u['kode'])); ?>">
                            <i class="fa fa-file-text-o gm-ico" aria-hidden="true"></i>
                            <span class="gm-name"><?php echo genc_e($u['tab']); ?></span>
                            <?php if ($u['type'] !== '' && $f['builtin'] && count($f['def']['types']) > 1): ?><small class="gm-sub"><?php echo genc_e($u['type']); ?></small><?php elseif ($u['sub'] !== '' && $f['mode'] === 'tables'): ?><small class="gm-sub"><?php echo genc_e($u['sub']); ?></small><?php endif; ?>
                            <?php if (!$u['aktif']): ?><i class="fa fa-eye-slash gm-flag" title="Nonaktif" aria-label="Nonaktif"></i><?php elseif (genc_reg_is_new($u['created_at'])): ?><span class="gm-dot" title="Baru &middot; ditambah <?php echo genc_e(genc_date($u['created_at'])); ?>" aria-label="Baru"></span><?php endif; ?>
                            <?php if ($f_lock === 'some' && $u['profile'] !== '' && genc_reg_locked($u['profile'])): ?><i class="fa fa-lock gm-flag gm-flag--lock" title="Dikunci" aria-label="Dikunci"></i><?php endif; ?>
                        </a></li>
                    <?php endforeach; ?>
                    </ul>
                </details>
            <?php endforeach; ?>
                <div class="gm-tree__none" data-gm-none hidden>Tidak ada yang cocok.</div>
                <?php if ($arch || $cad_list): /* [GENC-08OKT26-P3] + [GENC-13OKT26] cadangan hapus permanen */ $arch_open = $panel === 'cadangan'; foreach ($arch as $a) { if ($a['sel'] === $sel_key) { $arch_open = true; } } ?>
                <details class="gm-arch" data-gm-arch<?php echo $arch_open ? ' open' : ''; ?>>
                    <summary class="gm-row gm-row--folder gm-arch__head" title="Seperti tong sampah: pulihkan, atau hapus permanen"><span class="gm-chev" aria-hidden="true"><i class="fa fa-chevron-right"></i></span><i class="fa fa-archive gm-ico" aria-hidden="true"></i><span class="gm-name">Arsip</span><span class="gm-count"><?php echo count($arch); ?></span></summary>
                    <ul class="gm-files">
                    <?php foreach ($arch as $a): ?>
                        <li><a class="gm-row gm-row--file is-off<?php echo $a['sel'] === $sel_key ? ' is-selected' : ''; ?>" href="<?php echo genc_e($url(array('pilih' => $a['sel']))); ?>" data-gm-name="<?php echo genc_e(strtolower($a['name'] . ' ' . $a['sub'])); ?>"><i class="fa <?php echo genc_e($a['icon']); ?> gm-ico" aria-hidden="true"></i><span class="gm-name"><?php echo genc_e($a['name']); ?></span><small class="gm-sub"><?php echo genc_e($a['sub']); ?></small></a></li>
                    <?php endforeach; ?>
                    <?php if (!$arch): ?><li class="gm-arch__empty">Arsip kosong.</li><?php endif; ?>
                    <?php if ($cad_list): ?>
                        <li><a class="gm-row gm-row--file gm-row--cad<?php echo $panel === 'cadangan' ? ' is-selected' : ''; ?>" href="<?php echo genc_e($url(array('pilih' => 'cadangan'))); ?>" data-gm-name="riwayat hapus permanen cadangan backup"><i class="fa fa-file-archive-o gm-ico" aria-hidden="true"></i><span class="gm-name">Riwayat hapus permanen</span><span class="gm-count"><?php echo count($cad_list); ?></span></a></li>
                    <?php endif; ?>
                    </ul>
                </details>
                <?php endif; ?>
            </div>
            <div class="gm-tree__foot">
                <span><i class="fa fa-lock"></i> dikunci</span><span><span class="gm-dot"></span> baru</span><span><i class="fa fa-eye-slash"></i> nonaktif</span>
            </div>
        </aside>

        <!-- ================= PANEL ================= -->
        <section class="gm-panel" id="gm-panel" aria-live="polite">
        <?php if (!empty($e)): ?>
            <div class="genc-notice genc-notice--danger" style="margin-bottom:14px"><i class="fa fa-exclamation-circle"></i><div><b>Belum tersimpan.</b> Periksa isian yang ditandai merah.<?php echo isset($e['items']) ? '<br>' . $e['items'] : ''; ?></div></div>
        <?php endif; ?>

        <?php /* ====================================================== JENIS MESIN BARU */ if ($new === 'jenis' && $can['add']): ?>
            <form method="post" action="<?php print_link('mesin/add?csrf_token=' . $csrf); ?>" class="genc-card" autocomplete="off" novalidate data-gm-form>
                <input type="hidden" name="jenis" value="1">
                <div class="genc-card__head"><h2 class="genc-card__title"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span> Mesin baru</h2></div>
                <div class="genc-card__body">
                    <p class="gm-lead">Untuk mesin / alat yang <b>belum ada</b> di menu. Hasilnya: menu baru di sidebar, form isi, daftar, approval, dan report PDF &mdash; sama seperti mesin lain.</p>
                    <div class="genc-form">
                        <div class="<?php echo $fcls('nama'); ?>">
                            <label class="genc-field__label" for="m-nama">Nama mesin <span class="genc-req">*</span></label>
                            <input id="m-nama" class="genc-input" name="nama" maxlength="60" value="<?php echo genc_e($oval('nama')); ?>" placeholder="Contoh: Hand Pallet" required data-gm-autounit>
                            <span class="genc-field__hint">Tampil di sidebar &amp; judul halaman.</span><?php echo $err('nama'); ?>
                        </div>
                        <div class="<?php echo $fcls('unit_label'); ?>">
                            <label class="genc-field__label" for="m-unit">Unit pertama <span class="genc-req">*</span></label>
                            <input id="m-unit" class="genc-input" name="unit_label" maxlength="60" value="<?php echo genc_e($oval('unit')); ?>" placeholder="Contoh: Hand Pallet 1" required data-gm-unitname>
                            <span class="genc-field__hint">Unit lain bisa ditambah kapan saja (ikon <i class="fa fa-file-o"></i>+).</span><?php echo $err('unit_label'); ?>
                        </div>
                        <div class="genc-field">
                            <label class="genc-field__label" for="m-area">Area</label>
                            <input id="m-area" class="genc-input" name="area" maxlength="100" value="<?php echo genc_e($oval('area')); ?>" placeholder="Contoh: Warehouse Lt 1">
                        </div>
                        <div class="genc-field">
                            <label class="genc-field__label" for="m-pel">Pelaksanaan (di report)</label>
                            <input id="m-pel" class="genc-input" name="pelaksanaan" maxlength="150" value="<?php echo genc_e($oval('pelaksanaan', 'Setiap hari diawal shift 1')); ?>">
                        </div>
                        <div class="genc-field is-wide">
                            <span class="genc-field__label">Ikon di sidebar</span>
                            <?php echo $icon_picker('ikon', $oval('ikon', 'fa-cube')); ?>
                        </div>
                        <div class="genc-field">
                            <label class="genc-field__label" for="m-salin">Salin isi checklist dari</label>
                            <select id="m-salin" class="genc-select" name="salin">
                                <option value="">&mdash; Mulai kosong &mdash;</option>
                                <?php foreach ($d['copy_sources'] as $k => $v): ?><option value="<?php echo genc_e($k); ?>"<?php echo $oval('salin') === $k ? ' selected' : ''; ?>><?php echo genc_e($v); ?></option><?php endforeach; ?>
                            </select>
                            <span class="genc-field__hint">Item (part, standar, metode, alat, durasi, foto) jadi awalan &mdash; bisa diubah setelah dibuat.</span>
                        </div>
                        <?php /* [GENC-08OKT26-AKSES] hak akses awal per jabatan - sama dengan matriks Role Permissions */
                            $pv = isset($old['perm']) ? $old['perm'] : $d['perm_default']; ?>
                        <div class="<?php echo $fcls('akses', 'is-wide'); ?>">
                            <div class="gm-perm__bar"><span class="genc-field__label">Hak Akses Awal <span class="genc-req">*</span></span>
                                <?php if ($d['perm_sources']): /* [GENC-08OKT26-P3] salin cepat dari mesin lain */ ?>
                                <select class="genc-select gm-perm__copy" aria-label="Salin hak akses dari mesin" data-gm-permcopy>
                                    <option value="">Salin dari mesin&hellip;</option>
                                    <?php foreach ($d['perm_sources'] as $sp => $sv): ?><option value="<?php echo genc_e(json_encode($sv['perm'])); ?>"><?php echo genc_e($sv['name']); ?></option><?php endforeach; ?>
                                    <option value="{}">&mdash; Kosongkan semua &mdash;</option>
                                </select>
                                <?php endif; ?></div>
                            <div class="genc-table-wrap gm-perm"><table class="genc-atable gm-perm__t">
                                <thead><tr><th>Jabatan</th><?php foreach ($d['perm_actions'] as $ak => $al): ?><th class="gm-perm__c"><?php echo genc_e($al); ?></th><?php endforeach; ?></tr></thead>
                                <tbody>
                                <?php foreach ($d['roles'] as $rid => $rn): $have = isset($pv[$rid]) ? $pv[$rid] : array(); ?>
                                    <tr data-gm-permrow>
                                        <th scope="row"><?php echo genc_e($rn); ?></th>
                                        <?php foreach ($d['perm_actions'] as $ak => $al): ?>
                                        <td class="gm-perm__c" data-label="<?php echo genc_e($al); ?>"><label class="gm-perm__cb" title="<?php echo genc_e($rn . ' - ' . $al); ?>"><input type="checkbox" name="perm[<?php echo (int) $rid; ?>][]" value="<?php echo genc_e($ak); ?>"<?php echo in_array($ak, $have, true) ? ' checked' : ''; ?> data-gm-perm="<?php echo genc_e($ak); ?>"><span class="sr-only"><?php echo genc_e($rn . ' ' . $al); ?></span></label></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table></div>
                            <span class="genc-field__hint">Centang jabatan yang boleh melihat / mengisi / approve mesin ini (atau salin dari mesin lain lewat pilihan di kanan atas). Centang apa pun otomatis ikut <b>Lihat</b> (supaya menunya muncul). Bisa diubah lagi kapan saja di Role Permissions.</span><?php echo $err('akses'); ?>
                        </div>
                    </div>
                </div>
                <div class="genc-card__foot genc-formbar">
                    <a class="genc-btn" href="<?php print_link('mesin'); ?>">Batal</a>
                    <button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Buat mesin</button>
                </div>
            </form>

        <?php /* ====================================================== UNIT BARU */ elseif ($new === 'unit' && $can['add']):
            $nf = isset($tree[$d['new_folder']]) ? $tree[$d['new_folder']] : null; ?>
            <?php if (!$nf): ?>
                <div class="genc-card">
                    <div class="genc-card__head"><h2 class="genc-card__title"><span class="gm-ico2"><i class="fa fa-file-o"></i><i class="fa fa-plus"></i></span> Unit baru &mdash; pilih mesinnya</h2></div>
                    <div class="genc-card__body">
                        <p class="gm-lead">Unit baru masuk ke folder mesin yang sama dan tampil sebagai <b>tab</b> di halaman mesin itu.</p>
                        <?php echo $err('folder'); ?>
                        <div class="gm-pick">
                        <?php foreach ($tree as $fk => $f): ?>
                            <a class="gm-pick__item" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $fk))); ?>">
                                <i class="fa <?php echo genc_e($f['icon']); ?>" aria-hidden="true"></i>
                                <span><b><?php echo genc_e($f['title']); ?></b><small><?php echo count($f['units']); ?> unit &middot; <?php echo $f['mode'] === 'units' ? 'tab di tabel yang sama' : 'tab + tabel sendiri'; ?></small></span>
                                <i class="fa fa-angle-right gm-pick__go" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else: $nd = $nf['builtin'] ? $nf['def'] : array(); ?>
                <form method="post" action="<?php print_link('mesin/add?csrf_token=' . $csrf); ?>" class="genc-card" autocomplete="off" novalidate data-gm-form>
                    <input type="hidden" name="unit" value="1"><input type="hidden" name="folder" value="<?php echo genc_e($nf['key']); ?>">
                    <div class="genc-card__head"><h2 class="genc-card__title"><span class="gm-ico2"><i class="fa fa-file-o"></i><i class="fa fa-plus"></i></span> Unit baru di <?php echo genc_e($nf['title']); ?></h2>
                        <a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php echo genc_e($url(array('baru' => 'unit'))); ?>">Ganti mesin</a></div>
                    <div class="genc-card__body">
                        <?php if ($nf['mode'] === 'units'): ?>
                            <p class="gm-lead">Unit baru memakai <b>isi checklist yang sama</b> dengan unit sejenis. Setelah disimpan langsung muncul sebagai tab di <b><?php echo genc_e($nf['title']); ?></b>, di Home, Approval / NOK History, dan report PDF.</p>
                        <?php else: ?>
                            <p class="gm-lead">Unit baru mendapat <b>tab &amp; tabel data sendiri</b> (sama seperti KIR3 / KIR6 / KIR7) dengan isi checklist &amp; report yang sama dengan <?php echo genc_e($nf['title']); ?>. Hak akses disalin dari halaman <?php echo genc_e($nf['title']); ?> yang sudah ada.</p>
                        <?php endif; ?>
                        <div class="genc-form">
                            <?php if ($nf['builtin'] && $nf['mode'] === 'units'): $pv = $oval('profile', $nd['types'][0]['profile']); ?>
                            <div class="<?php echo $fcls('profile', 'is-wide'); ?>">
                                <span class="genc-field__label">Jenis unit <span class="genc-req">*</span></span>
                                <div class="gm-types">
                                <?php foreach ($nd['types'] as $t): $cnt = 0; foreach ($nf['units'] as $u) { if ($u['profile'] === $t['profile']) { $cnt++; } } ?>
                                    <label class="gm-type"><input type="radio" name="profile" value="<?php echo genc_e($t['profile']); ?>"<?php echo $pv === $t['profile'] ? ' checked' : ''; ?>><span><b><?php echo genc_e($t['label']); ?></b><small><?php echo $cnt; ?> unit sekarang</small></span></label>
                                <?php endforeach; ?>
                                </div><?php echo $err('profile'); ?>
                            </div>
                            <?php endif; ?>
                            <div class="<?php echo $fcls('label'); ?>">
                                <label class="genc-field__label" for="u-label">Nama unit <span class="genc-req">*</span></label>
                                <input id="u-label" class="genc-input" name="label" maxlength="60" value="<?php echo genc_e($oval('label')); ?>" placeholder="<?php echo genc_e(isset($nd['unit_hint']) ? preg_replace('/^[^:]*:\s*/', '', $nd['unit_hint']) : $nf['title'] . ' ' . (count($nf['units']) + 1)); ?>" required>
                                <span class="genc-field__hint"><?php echo genc_e(isset($nd['unit_hint']) ? $nd['unit_hint'] : 'Nama yang tampil di tab & report.'); ?><?php echo !empty($nd['tab_note']) ? ' ' . genc_e($nd['tab_note']) : ''; ?></span><?php echo $err('label'); ?>
                            </div>
                            <?php if ($nf['mode'] === 'tables'): ?>
                            <div class="genc-field">
                                <label class="genc-field__label" for="u-kode">Kode mesin</label>
                                <input id="u-kode" class="genc-input genc-mono" name="kode" maxlength="60" value="<?php echo genc_e($oval('kode')); ?>" placeholder="<?php echo $nf['key'] === 'dumping' ? 'KIR8P01DP001' : ''; ?>">
                                <span class="genc-field__hint"><?php echo genc_e($nd['kode_hint']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="genc-card__foot genc-formbar">
                        <a class="genc-btn" href="<?php echo genc_e($url(array('pilih' => 'f:' . $nf['key']))); ?>">Batal</a>
                        <button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Tambah unit</button>
                    </div>
                </form>
            <?php endif; ?>

        <?php /* ====================================================== FOLDER DIPILIH
               [GENC-08OKT26-P3] SATU susunan untuk semua mesin (mesin awal = mesin baru):
               kepala -> Pengaturan mesin -> Unit -> Kunci + Isi checklist (+ gambar report) -> Riwayat perubahan -> Hapus */
            elseif ($sel && $sel['type'] === 'folder'):
            $f = $sel['folder']; $m = $f['jenis'] ? $f['mesin'] : null; $is_j = $f['jenis'];
            $isi = $d['isi']; $ident = $d['ident']; $flock = $d['flock']; $locked = $flock === 'all';
            $arsip = !empty($f['arsip']);
            $f_units = array_values(array_filter($f['units'], function ($u) { return empty($u['arsip']); }));
            $f_arch_units = count($live_units($f)) - count($f_units);   /* [GENC-13OKT26] tanpa unit yang dihapus permanen */
            $pl = isset($d['purge']) ? $d['purge'] : null; ?>
            <div class="genc-card gm-head">
                <div class="gm-head__ico"><i class="fa <?php echo genc_e($f['icon']); ?>"></i></div>
                <div class="gm-head__txt">
                    <h2 class="gm-head__title"><?php echo genc_e($f['title']); ?> <?php $fb = $arsip ? 'arsip' : $folder_badge($f); echo $fb === 'baru' ? '<span class="genc-tag genc-tag--primary">baru</span>' : ($fb === 'arsip' ? '<span class="genc-tag"><i class="fa fa-archive"></i> di arsip</span>' : ($fb !== '' ? '<span class="genc-tag"><i class="fa fa-eye-slash"></i> ' . $fb . '</span>' : '')); ?><?php echo $locked ? ' <span class="genc-tag"><i class="fa fa-lock"></i> dikunci</span>' : ''; ?></h2>
                    <div class="gm-head__meta"><?php echo $mode_txt($f); ?> &middot; <?php echo count($f_units); ?> unit &middot; <?php echo number_format($f['count'], 0, ',', '.'); ?> checklist tersimpan</div>
                </div>
                <div class="gm-head__act">
                    <?php $menu_ok = ACL::is_allowed($f['menu'] . '/list'); if ($menu_ok && ($arsip || (!empty($f['aktif']) && (!$is_j || $f['items'] > 0)))): ?><a class="genc-btn genc-btn--sm" href="<?php print_link($f['menu']); ?>"><i class="fa fa-external-link"></i> <?php echo $arsip ? 'Buka data' : 'Buka halaman'; ?></a><?php endif; ?>
                    <?php if ($can['add'] && !$arsip && !$locked): ?><a class="genc-btn genc-btn--sm genc-btn--primary" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $f['key']))); ?>"><i class="fa fa-plus"></i> Unit baru</a><?php endif; ?>
                </div>
            </div>

            <?php if ($arsip): ?>
            <div class="genc-card gm-archived">
                <div class="genc-card__body gm-danger__row">
                    <div><b><i class="fa fa-archive"></i> Mesin ini ada di Arsip</b><p class="genc-small genc-muted">Tidak tampil di menu, Home, Approval / NOK History &amp; matriks hak akses. <?php echo number_format($f['count'], 0, ',', '.'); ?> checklist, tabel &amp; hak aksesnya tetap tersimpan &mdash; data lama tetap bisa dibuka &amp; di-export lewat tombol <i>Buka data</i>. <b>Pulihkan</b> untuk memakainya lagi<?php echo $pl ? ', atau <b>hapus permanen</b> kalau sudah tidak diperlukan' : ''; ?>.</p></div>
                    <?php if ($can['edit']): ?>
                    <form method="post" action="<?php print_link('mesin/edit/pulihkan/x?csrf_token=' . $csrf); ?>"><input type="hidden" name="sel" value="f:<?php echo genc_e($f['key']); ?>">
                        <button type="submit" class="genc-btn genc-btn--sm genc-btn--primary"><i class="fa fa-undo"></i> Pulihkan</button></form>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>

            <?php /* ---------------- 1. Pengaturan mesin */
                $fa = $oval('aktif', $f['aktif'] ? '1' : '0') === '1';
                $fe = $can['edit'];
                $ro = $locked ? ' readonly' : ''; ?>
            <div class="genc-card" id="mesin">
                <div class="genc-card__head"><h3 class="genc-card__title">Pengaturan mesin</h3><?php if ($fe): ?><label class="gm-switch"><input type="checkbox" name="aktif" value="1" form="gm-folder-form"<?php echo $fa ? ' checked' : ''; ?>><span aria-hidden="true"></span> Aktif di menu</label><?php else: ?><span class="genc-tag<?php echo $f['aktif'] ? ' genc-tag--ok' : ''; ?>"><?php echo $f['aktif'] ? 'Aktif' : 'Nonaktif'; ?></span><?php endif; ?></div>
                <?php if ($fe): ?>
                <form method="post" id="gm-folder-form" action="<?php print_link('mesin/edit/folder/' . rawurlencode($f['key']) . '?csrf_token=' . $csrf); ?>" class="genc-card__body genc-form" autocomplete="off" novalidate data-gm-form>
                    <?php if ($locked): ?><div class="genc-notice genc-notice--info is-wide"><i class="fa fa-lock"></i><div>Mesin dikunci &mdash; hanya saklar <b>Aktif di menu</b> yang bisa diubah. Buka kunci di bawah untuk mengubah yang lain.</div></div><?php endif; ?>
                    <div class="<?php echo $fcls('nama'); ?>">
                        <label class="genc-field__label" for="f-nama">Nama<?php echo $is_j ? ' <span class="genc-req">*</span>' : ''; ?></label>
                        <input id="f-nama" class="genc-input" name="nama" maxlength="60" value="<?php echo genc_e($oval('nama', $ident['nama'])); ?>"<?php echo $is_j ? ' required' : ' placeholder="' . genc_e($ident['nama_awal']) . '"'; ?><?php echo $ro; ?>>
                        <span class="genc-field__hint">Tampil di menu, judul halaman, Home, Approval &amp; judul report.<?php echo !$is_j ? ' Kosongkan = nama awal (<b>' . genc_e($ident['nama_awal']) . '</b>).' : ''; ?></span><?php echo $err('nama'); ?>
                    </div>
                    <div class="genc-field">
                        <label class="genc-field__label" for="f-area">Area (kop report)</label>
                        <input id="f-area" class="genc-input" name="area" maxlength="100" value="<?php echo genc_e($oval('area', $ident['area'])); ?>"<?php echo !$is_j ? ' placeholder="' . genc_e($ident['area_awal'] !== '' ? $ident['area_awal'] : 'Contoh: Warehouse Lt 1') . '"' : ' placeholder="Contoh: Warehouse Lt 1"'; ?><?php echo $ro; ?>>
                        <?php if (!$is_j): ?><span class="genc-field__hint">Kosongkan = nilai awal<?php echo $ident['area_awal'] !== '' ? ' (' . genc_e($ident['area_awal']) . ')' : ' (kosong)'; ?>.</span><?php endif; ?>
                    </div>
                    <div class="genc-field">
                        <label class="genc-field__label" for="f-pel">Pelaksanaan (kop report)</label>
                        <input id="f-pel" class="genc-input" name="pelaksanaan" maxlength="150" value="<?php echo genc_e($oval('pelaksanaan', $ident['pelaksanaan'])); ?>"<?php echo !$is_j && $ident['pelaksanaan_awal'] !== '' ? ' placeholder="' . genc_e($ident['pelaksanaan_awal']) . '"' : ''; ?><?php echo $ro; ?>>
                        <?php if (!$is_j): ?><span class="genc-field__hint">Kosongkan = nilai awal<?php echo $ident['pelaksanaan_awal'] !== '' ? ' (' . genc_e($ident['pelaksanaan_awal']) . ')' : ''; ?>.</span><?php endif; ?>
                    </div>
                    <div class="genc-field">
                        <span class="genc-field__label">Route &amp; tabel</span>
                        <span class="genc-input gm-readonly genc-mono"><?php echo genc_e($f['menu']); ?></span>
                    </div>
                    <?php if (!$locked): ?>
                    <div class="genc-field is-wide">
                        <span class="genc-field__label">Ikon di sidebar</span>
                        <?php echo $icon_picker('ikon', $oval('ikon', $f['icon']), $is_j ? '' : $f['def']['icon_awal']); ?>
                        <?php if (!$is_j): ?><span class="genc-field__hint">Ikon bertanda titik = ikon awal (pilih lagi untuk mengembalikan).</span><?php endif; ?>
                    </div>
                    <?php else: ?><input type="hidden" name="ikon" value="<?php echo genc_e($f['icon']); ?>"><?php endif; ?>
                    <div class="<?php echo $fcls('aktif', 'is-wide'); ?>"><span class="genc-field__hint"><b>Nonaktif</b> = menu, Home &amp; form isi <?php echo genc_e($f['title']); ?> disembunyikan; data lama tetap tersimpan dan bisa diaktifkan lagi kapan saja. Approval / NOK History tetap menampilkan checklist yang belum beres.<?php echo $is_j && $f['items'] === 0 ? ' Mesin tanpa item belum bisa diaktifkan (tambah item dulu).' : ''; ?></span><?php echo $err('aktif'); ?></div>
                    <div class="genc-formbar is-wide"><button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Simpan</button></div>
                </form>
                <?php else: ?>
                <div class="genc-card__body"><p class="genc-muted">Role Anda hanya bisa melihat.</p></div>
                <?php endif; ?>
            </div>

            <?php /* ---------------- 2. Unit */ ?>
            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Unit <span class="genc-chip__count"><?php echo count($f_units); ?></span></h3><span class="genc-small genc-muted">Klik untuk mengatur<?php echo $f_arch_units ? ' &middot; ' . $f_arch_units . ' unit di Arsip' : ''; ?></span></div>
                <div class="genc-table-wrap"><table class="genc-atable">
                    <thead><tr><th>Unit</th><th><?php echo $f['mode'] === 'tables' ? 'Kode' : 'Jenis'; ?></th><th>Status</th><th>Data</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($f_units as $u): ?>
                        <tr<?php echo !$u['aktif'] ? ' class="is-muted"' : ''; ?>>
                            <td data-label="Unit"><b><?php echo genc_e($u['label']); ?></b></td>
                            <td data-label="<?php echo $f['mode'] === 'tables' ? 'Kode' : 'Jenis'; ?>"><?php echo genc_e($f['mode'] === 'tables' ? ($u['kode'] !== '' ? $u['kode'] : '–') : ($u['type'] !== '' ? $u['type'] : $f['title'])); ?></td>
                            <td data-label="Status"><?php echo $u['aktif'] ? '<span class="genc-tag genc-tag--ok">Aktif</span>' : '<span class="genc-tag">Nonaktif</span>'; ?><?php echo genc_reg_is_new($u['created_at']) ? ' <span class="genc-tag genc-tag--primary">baru</span>' : ''; ?></td>
                            <td data-label="Data"><?php echo number_format($u['count'], 0, ',', '.'); ?> checklist</td>
                            <td class="genc-col-actions"><a class="genc-btn genc-btn--sm" href="<?php echo genc_e($url(array('pilih' => $u['sel']))); ?>">Atur <i class="fa fa-angle-right"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$f_units): ?><tr><td colspan="5" class="genc-muted">Belum ada unit aktif.</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>

            <?php /* ---------------- 3. Kunci + isi checklist (+ gambar report) */
                $edit_ok = $can['edit'] && !$locked && $isi['ready'];
                $rows = isset($old['items_rows']) ? $old['items_rows'] : ($is_j ? $m['items_arr'] : $isi['rows']);
                $who = array(); foreach ($f_units as $u) { if ($u['profile'] === $isi['profile']) { $who[] = $u['label']; } }
                $isi_tag = $is_j ? '' : ($isi['changed'] ? '<span class="genc-tag genc-tag--primary" title="Diubah lewat menu ini">diubah lewat menu' . ($isi['upd_by'] !== '' ? ' &middot; ' . genc_e(genc_person_name($isi['upd_by'])) : '') . '</span>' : '<span class="genc-tag">isi awal</span>'); ?>
            <?php echo $lock_bar($isi, $f, $flock); ?>
            <span id="isi"></span>
            <?php if (count($isi['types']) > 1): ?>
            <nav class="gm-ttabs" aria-label="Jenis unit">
                <?php foreach ($isi['types'] as $tp => $tl): ?><a class="gm-ttab<?php echo $tp === $isi['profile'] ? ' is-active' : ''; ?>" href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key'], 'tipe' => $tp))); ?>#isi"<?php echo $tp === $isi['profile'] ? ' aria-current="page"' : ''; ?>><?php echo genc_e($tl); ?><?php if ($flock === 'some' && genc_reg_locked($tp)): ?> <i class="fa fa-lock" aria-label="dikunci"></i><?php endif; ?></a><?php endforeach; ?>
            </nav>
            <?php endif; ?>
            <?php if (!$edit_ok): ?>
            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Isi checklist <?php echo genc_e($isi['label']); ?> <span class="genc-chip__count"><?php $n_it = 0; foreach ($d['preview'] as $ps) { $n_it += count($ps['items']); } echo $n_it; ?></span></h3><?php echo $locked ? '<span class="genc-tag"><i class="fa fa-lock"></i> dikunci</span>' : $isi_tag; ?></div>
                <div class="genc-card__body">
                    <p class="gm-lead"><?php echo $locked ? 'Dikunci &mdash; buka kunci di atas untuk mengubah item, foto, atau gambar report.' : ($isi['ready'] ? 'Role Anda hanya bisa melihat.' : 'Jalankan SQL database dulu untuk mengubah isi checklist.'); ?><?php echo $who ? ' Dipakai: ' . genc_e(implode(', ', $who)) . '.' : ''; ?></p>
                    <?php echo genc_preview_items($d['preview']); ?>
                </div>
            </div>
            <?php echo $gambar_card($isi, false); ?>
            <?php else: $no = 0; ?>
            <form method="post" action="<?php print_link(($is_j ? 'mesin/edit/jenis/' . rawurlencode($f['key']) : 'mesin/edit/isi/' . rawurlencode($isi['profile'])) . '?csrf_token=' . $csrf); ?>" class="gm-editor" enctype="multipart/form-data" autocomplete="off" novalidate data-gm-form data-gm-editor>
                <input type="hidden" name="items_sent" value="1">
                <div class="genc-card<?php echo isset($e['items']) ? ' has-error' : ''; ?>">
                    <div class="genc-card__head"><h3 class="genc-card__title">Item checklist <?php echo genc_e($isi['label']); ?> <span class="genc-chip__count" data-gm-total>0</span></h3><?php echo $is_j ? '<span class="genc-small genc-muted">Maks. ' . GENC_REG_MAX_ITEMS . ' item</span>' : $isi_tag; ?></div>
                    <div class="genc-card__body">
                        <?php if ($is_j && $f['items'] === 0 && !isset($old['items_rows'])): ?>
                            <div class="genc-notice genc-notice--info" style="margin-bottom:14px"><i class="fa fa-info-circle"></i><div><b>Belum ada item.</b> Tambahkan minimal 1 item lalu simpan, kemudian nyalakan <i>Aktif di menu</i> &mdash; setelah itu <?php echo genc_e($f['title']); ?> muncul di sidebar &amp; bisa diisi.</div></div>
                        <?php endif; ?>
                        <p class="gm-lead">Urutan di sini = urutan di form isi &amp; report. Perubahan berlaku untuk checklist berikutnya<?php echo $who ? ' di <b>' . genc_e(implode(', ', $who)) . '</b>' : ''; ?>; checklist lama tetap tersimpan. Item yang dihapus hanya disembunyikan (datanya tetap).<?php echo $isi['profile'] === 'palletstacker' ? ' Isi Pallet Stacker diatur terpisah dari Pallet Mover.' : ''; ?></p>
                        <?php foreach ($secs as $sk => $s):
                            $act = array(); $gone = array(); $i = 0;
                            foreach ((array) (isset($rows[$sk]) ? $rows[$sk] : array()) as $it) { if (isset($it['aktif']) && !$it['aktif']) { $gone[] = $it; } else { $act[] = $it; } } ?>
                            <div class="gm-sec" data-gm-sec="<?php echo genc_e($sk); ?>">
                                <div class="gm-sec__head"><b><?php echo genc_e($s['short']); ?></b><small><?php echo genc_e(preg_replace('/^.*\(|\)$/', '', $s['title'])); ?></small></div>
                                <ol class="gm-list" data-gm-list>
                                <?php foreach ($act as $it) { $no++; echo $item_row($sk, 'e' . ($i++), $it, $no); } ?>
                                </ol>
                                <button type="button" class="genc-btn genc-btn--sm genc-btn--ghost gm-add" data-gm-add="<?php echo genc_e($sk); ?>"><i class="fa fa-plus"></i> Tambah item <?php echo genc_e(strtolower($s['short'])); ?></button>
                                <?php if ($gone): ?>
                                <details class="gm-gone"><summary><i class="fa fa-trash-o"></i> <?php echo count($gone); ?> item dihapus (data lama tetap tersimpan)</summary>
                                    <ol class="gm-list gm-list--gone"><?php foreach ($gone as $it) { echo $item_row($sk, 'e' . ($i++), $it, 0); } ?></ol>
                                </details>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <template data-gm-tpl><?php echo $item_row('__SEC__', '__IDX__', array('db' => '', 'key' => '__IDX__'), 0); ?></template>
                    </div>
                </div>
                <?php echo $gambar_card($isi, true); ?>
                <div class="genc-savebar gm-savebar">
                    <div class="genc-savebar__text" data-gm-dirty-txt>Perubahan langsung dipakai form isi, daftar &amp; report.</div>
                    <a class="genc-btn" href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key'], 'tipe' => count($isi['types']) > 1 ? $isi['profile'] : null))); ?>#isi">Batal</a>
                    <button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Simpan</button>
                </div>
            </form>
            <?php endif; ?>
            <?php endif; /* arsip */ ?>

            <?php /* ---------------- 4. Riwayat perubahan   [GENC-13OKT26] ringkas: versi sekarang + lipat + layar penuh
                   Kembalikan = pindah penunjuk "sekarang" (tanpa versi baru). Layar penuh tanpa JS: :target (#riwayat-penuh). */
                $vers = $d['versions']; $vn = count($vers); $vcur = null;
                foreach ($vers as $v) { if (!empty($v['is_cur'])) { $vcur = $v; } }
                if (!$vcur && $vers) { $vcur = $vers[0]; $vers[0]['is_cur'] = true; }
                $cur_no = $vcur ? (int) $vcur['versi'] : 0;
                $can_rs = $can['edit'] && !$locked && !$arsip;
                $why_no = !$can['edit'] ? '' : ($arsip ? 'Pulihkan mesin dari Arsip dulu untuk mengembalikan versi' : ($locked ? 'Mesin dikunci - buka kunci dulu untuk mengembalikan versi' : ''));
                $others = array(); foreach ($vers as $v) { if (empty($v['is_cur'])) { $others[] = $v; } }
                $peek = array_slice($others, 0, 5); ?>
            <div class="genc-card gm-hist" id="riwayat" data-gm-hist>
                <div class="genc-card__head">
                    <h3 class="genc-card__title"><i class="fa fa-history" aria-hidden="true"></i> Riwayat perubahan <span class="genc-chip__count"><?php echo $vn; ?></span></h3>
                    <?php if ($vers): ?><a class="genc-btn genc-btn--sm genc-btn--ghost gm-hist__full" href="#riwayat-penuh" data-gm-vfull-open title="Buka semua versi di layar penuh"><i class="fa fa-expand" aria-hidden="true"></i><span> Layar penuh</span></a><?php endif; ?>
                </div>
                <?php if (!$vers): ?>
                <div class="genc-card__body"><p class="gm-lead">Belum ada perubahan tercatat. Setiap kali pengaturan, unit, isi checklist, foto atau gambar report mesin ini disimpan, kondisinya dicatat di sini &mdash; dan bisa dikembalikan kapan saja.</p></div>
                <?php else: ?>
                <ol class="gm-vers gm-hist__now"><?php echo $ver_li($vcur, $f, false, '', false, $cur_no); ?></ol>
                <?php if ($others): ?>
                <details class="gm-hist__more" data-gm-hist-more>
                    <summary><i class="fa fa-chevron-right gm-hist__chev" aria-hidden="true"></i> <span data-gm-hist-lbl>Versi lain</span> <span class="genc-chip__count"><?php echo count($others); ?></span><span class="gm-hist__hint">Kembalikan = pindah ke versi itu, tanpa membuat versi baru</span></summary>
                    <ol class="gm-vers"><?php foreach ($peek as $v) { echo $ver_li($v, $f, $can_rs, $why_no, false, $cur_no); } ?></ol>
                    <?php if (count($others) > count($peek)): ?><a class="gm-hist__all" href="#riwayat-penuh" data-gm-vfull-open><i class="fa fa-expand" aria-hidden="true"></i> Lihat semua <?php echo $vn; ?> versi di layar penuh</a><?php endif; ?>
                </details>
                <?php endif; ?>
                <?php if ($why_no !== ''): ?><p class="gm-hist__note"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo genc_e($why_no); ?>.</p><?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ($vers): ?>
            <div class="gm-vfull" id="riwayat-penuh" role="dialog" aria-modal="true" aria-labelledby="gm-vfull-title" data-gm-vfull>
                <a class="gm-vfull__shade" href="#riwayat" tabindex="-1" aria-hidden="true" data-gm-vfull-close></a>
                <div class="gm-vfull__box">
                    <div class="gm-vfull__head">
                        <div class="gm-vfull__ttl">
                            <h2 id="gm-vfull-title"><i class="fa fa-history" aria-hidden="true"></i> Riwayat perubahan <span><?php echo genc_e($f['title']); ?></span></h2>
                            <p><?php echo $vn; ?> versi (maks. 30 terakhir) &middot; sekarang <b>v<?php echo $cur_no; ?></b> &middot; <b>Kembalikan</b> memindahkan tanda &ldquo;sekarang&rdquo; tanpa membuat versi baru &middot; data checklist tidak ikut berubah.</p>
                        </div>
                        <a class="genc-btn genc-btn--sm gm-vfull__x" href="#riwayat" data-gm-vfull-close title="Kecilkan (Esc)"><i class="fa fa-compress" aria-hidden="true"></i><span> Kecilkan</span></a>
                    </div>
                    <div class="gm-vfull__tools">
                        <div class="gm-vfull__find"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari versi, isi perubahan, atau nama" aria-label="Cari di riwayat" data-gm-vfind autocomplete="off"></div>
                        <span class="genc-small genc-muted" data-gm-vcount><?php echo $vn; ?> versi</span>
                    </div>
                    <?php if ($why_no !== ''): ?><p class="gm-hist__note"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo genc_e($why_no); ?>.</p><?php endif; ?>
                    <ol class="gm-vers gm-vtl" data-gm-vlist><?php foreach ($vers as $v) { echo $ver_li($v, $f, $can_rs, $why_no, true, $cur_no); } ?></ol>
                    <p class="gm-vfull__none" data-gm-vnone hidden>Tidak ada versi yang cocok.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($arsip) { echo $purge_card($pl, 'f:' . $f['key']); }   /* [GENC-13OKT26] paling bawah, seperti tombol Hapus */ ?>
            <?php /* ---------------- 5. Kembalikan isi awal / Hapus (arsip) */ ?>
            <?php if (!$arsip && $can['edit'] && !$locked && !$is_j && $isi['changed']): ?>
            <div class="genc-card gm-danger">
                <div class="genc-card__body">
                    <form method="post" action="<?php print_link('mesin/edit/reset/' . rawurlencode($isi['profile']) . '?csrf_token=' . $csrf); ?>" class="gm-danger__row">
                        <div><b>Kembalikan isi <?php echo genc_e($isi['label']); ?> ke isi awal</b><p class="genc-small genc-muted">Item, foto &amp; gambar report kembali seperti awal. Item yang ditambah lewat menu disembunyikan; data checklist-nya tetap tersimpan. Bisa dibatalkan lewat Riwayat perubahan.</p></div>
                        <input type="hidden" name="ya" value="1">
                        <button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Kembalikan isi <?php echo genc_e($isi['label']); ?> ke isi awal?" data-genc-confirm-msg="Perubahan isi checklist lewat menu ini dibatalkan (tetap tersimpan di Riwayat perubahan). Data checklist lama tidak berubah." data-genc-confirm-ok="Ya, kembalikan" data-genc-danger><i class="fa fa-undo"></i> Kembalikan</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!$arsip && $can['delete']): ?>
            <div class="genc-card gm-danger">
                <div class="genc-card__body">
                    <?php if ($locked): ?>
                        <b>Hapus mesin</b><p class="genc-small genc-muted">Mesin dikunci &mdash; buka kunci dulu.</p>
                    <?php else: ?>
                    <form method="post" action="<?php print_link('mesin/delete/' . ($is_j ? 'jenis' : 'folder') . '/' . rawurlencode($f['key']) . '?csrf_token=' . $csrf); ?>" class="gm-danger__row">
                        <div><b>Hapus mesin</b><p class="genc-small genc-muted">Dipindah ke <b>Arsip</b>: hilang dari menu, Home, Approval / NOK History &amp; matriks hak akses. <?php echo number_format($f['count'], 0, ',', '.'); ?> checklist, tabel, foto &amp; hak aksesnya <b>tetap tersimpan</b> &mdash; pulihkan kapan saja dari bagian Arsip.</p></div>
                        <input type="hidden" name="ya" value="1">
                        <button type="submit" class="genc-btn genc-btn--danger genc-btn--sm" data-genc-confirm="Hapus <?php echo genc_e($f['title']); ?>?" data-genc-confirm-msg="Mesin dipindah ke Arsip (bukan dihapus permanen). Data &amp; tabelnya tetap; bisa dipulihkan kapan saja." data-genc-confirm-ok="Ya, pindahkan ke Arsip" data-genc-danger><i class="fa fa-trash-o"></i> Hapus</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php /* ====================================================== UNIT DIPILIH */ elseif ($sel && $sel['type'] === 'unit'):
            $f = $sel['folder']; $u = $sel['unit']; $reg_units = genc_reg_data(); $reg_units = $reg_units['unit']; $row = ($u['kind'] === 'unit' && isset($reg_units[$u['id']])) ? $reg_units[$u['id']] : null;
            $link = $u['page'] . ($f['mode'] === 'units' ? '?unit=' . rawurlencode($u['slug']) : '');
            $edit_url = $u['kind'] === 'unit' ? 'mesin/edit/unit/' . (int) $u['id'] : 'mesin/edit/bawaan/' . rawurlencode($u['page']); ?>
            <div class="genc-card gm-head">
                <div class="gm-head__ico gm-head__ico--file"><i class="fa fa-file-text-o"></i></div>
                <div class="gm-head__txt">
                    <div class="gm-path"><a href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key']))); ?>"><i class="fa <?php echo genc_e($f['icon']); ?>"></i> <?php echo genc_e($f['title']); ?></a> <i class="fa fa-angle-right"></i></div>
                    <h2 class="gm-head__title"><?php echo genc_e($u['label']); ?> <?php echo genc_reg_is_new($u['created_at']) ? '<span class="genc-tag genc-tag--primary">baru</span>' : ''; ?> <?php echo !empty($u['arsip']) ? '<span class="genc-tag"><i class="fa fa-archive"></i> di arsip</span>' : (!$u['aktif'] ? '<span class="genc-tag"><i class="fa fa-eye-slash"></i> nonaktif</span>' : ''); ?><?php echo $d['flock'] === 'all' ? ' <span class="genc-tag"><i class="fa fa-lock"></i> dikunci</span>' : ''; ?><?php echo empty($f['aktif']) ? ' <span class="genc-tag" title="Mesin ' . genc_e($f['title']) . ' sedang nonaktif"><i class="fa fa-eye-slash"></i> mesin nonaktif</span>' : ''; ?></h2>
                    <div class="gm-head__meta"><?php echo number_format($u['count'], 0, ',', '.'); ?> checklist tersimpan<?php if ($row && $row['created_by']): ?> &middot; ditambah <?php echo genc_e(genc_person_name($row['created_by'])); ?>, <?php echo genc_e(genc_date($row['created_at'])); ?><?php endif; ?></div>
                </div>
                <div class="gm-head__act">
                    <?php if ($u['aktif'] && !empty($f['aktif']) && ACL::is_allowed($u['page'] . '/list') && (!$f['jenis'] || $f['items'] > 0)): ?><a class="genc-btn genc-btn--sm" href="<?php print_link($link); ?>"><i class="fa fa-external-link"></i> Buka tab</a><?php endif; ?>
                </div>
            </div>

            <?php $u_lock = $d['flock'] === 'all'; $u_ro = $u_lock ? ' readonly' : ''; ?>
            <?php if (!empty($u['arsip'])): /* [GENC-08OKT26-P3] unit di arsip */ ?>
            <div class="genc-card gm-archived">
                <div class="genc-card__body gm-danger__row">
                    <div><b><i class="fa fa-archive"></i> Unit ini ada di Arsip</b><p class="genc-small genc-muted">Tidak tampil di tab, Home, Approval / NOK History. <?php echo number_format($u['count'], 0, ',', '.'); ?> checklist-nya tetap tersimpan.<?php echo !empty($f['arsip']) ? ' Mesin ' . genc_e($f['title']) . ' juga ada di Arsip &mdash; pulihkan / hapus permanen lewat mesinnya.' : ''; ?></p></div>
                    <?php if ($can['edit'] && empty($f['arsip'])): ?>
                    <form method="post" action="<?php print_link('mesin/edit/pulihkan/x?csrf_token=' . $csrf); ?>"><input type="hidden" name="sel" value="<?php echo genc_e($u['sel']); ?>">
                        <button type="submit" class="genc-btn genc-btn--sm genc-btn--primary"><i class="fa fa-undo"></i> Pulihkan</button></form>
                    <?php endif; ?>
                </div>
            </div>
            <?php echo $purge_card(isset($d['purge']) ? $d['purge'] : null, $u['sel']); ?>
            <?php else: ?>
            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Pengaturan unit</h3></div>
                <?php if (!$can['edit']): ?>
                    <div class="genc-card__body"><p class="genc-muted">Role Anda hanya bisa melihat.</p></div>
                <?php else:
                    $is_dyn = $u['kind'] === 'unit';
                    $aktif_v = $oval('aktif', $u['aktif'] ? '1' : '0') === '1';
                    $lbl_v = $oval('label', $u['label']); ?>
                    <form method="post" action="<?php print_link($edit_url . '?csrf_token=' . $csrf); ?>" class="genc-card__body genc-form" autocomplete="off" novalidate data-gm-form>
                        <input type="hidden" name="slug" value="<?php echo genc_e($f['mode'] === 'units' ? $u['slug'] : ''); ?>">
                        <?php if ($u_lock): ?><div class="genc-notice genc-notice--info is-wide"><i class="fa fa-lock"></i><div>Mesin dikunci &mdash; hanya saklar <b>Aktif</b> yang bisa diubah.</div></div><?php endif; ?>
                        <div class="<?php echo $fcls('label'); ?>">
                            <label class="genc-field__label" for="x-label">Nama unit<?php echo $is_dyn ? ' <span class="genc-req">*</span>' : ''; ?></label>
                            <input id="x-label" class="genc-input" name="label" maxlength="60" value="<?php echo genc_e($lbl_v); ?>"<?php echo $is_dyn ? ' required' : ' placeholder="' . genc_e($u['default_label']) . '"'; ?><?php echo $u_ro; ?>>
                            <span class="genc-field__hint"><?php
                                $hint = $f['mode'] === 'units' ? 'Tampil di tab, Home &amp; report.' : 'Tampil di tab (kalau ' . genc_e($f['title']) . ' punya lebih dari 1 unit), Home &amp; Approval.';
                                if (!$is_dyn) { $hint .= ' Kosongkan = nama awal (<b>' . genc_e($u['default_label']) . '</b>).'; }
                                if ($f['mode'] === 'units' && $u['count'] > 0) { $hint .= ' Sudah ada data: nama lama tetap dikenali, data lama ikut unit ini.'; }
                                echo $hint; ?></span><?php echo $err('label'); ?>
                        </div>
                        <?php if ($f['mode'] === 'tables'): ?>
                            <?php if ($is_dyn): ?>
                            <div class="genc-field">
                                <label class="genc-field__label" for="x-kode">Kode mesin</label>
                                <input id="x-kode" class="genc-input genc-mono" name="kode" maxlength="60" value="<?php echo genc_e($oval('kode', $u['kode'])); ?>"<?php echo $u_ro; ?>>
                                <span class="genc-field__hint">Ditulis di kop report. Kosong = nama unit.</span>
                            </div>
                            <?php else: ?>
                            <div class="genc-field">
                                <span class="genc-field__label">Halaman &amp; tabel</span>
                                <span class="genc-input gm-readonly genc-mono"><?php echo genc_e($u['page']); ?></span>
                                <span class="genc-field__hint"><?php echo $u['kode'] !== '' ? 'Kode di report: ' . genc_e($u['kode']) . '. ' : ''; ?>Isi checklist &amp; report tidak berubah.</span>
                            </div>
                            <?php endif; ?>
                        <?php elseif ($is_dyn && $f['builtin'] && count($f['def']['types']) > 1): $pv = $oval('profile', $u['profile']); ?>
                            <div class="<?php echo $fcls('profile'); ?>">
                                <label class="genc-field__label" for="x-prof">Jenis unit</label>
                                <select id="x-prof" class="genc-select" name="profile"<?php echo $u['count'] > 0 ? ' disabled' : ''; ?>>
                                    <?php foreach ($f['def']['types'] as $t): ?><option value="<?php echo genc_e($t['profile']); ?>"<?php echo $pv === $t['profile'] ? ' selected' : ''; ?>><?php echo genc_e($t['label']); ?></option><?php endforeach; ?>
                                </select>
                                <?php if ($u['count'] > 0): ?><input type="hidden" name="profile" value="<?php echo genc_e($u['profile']); ?>"><span class="genc-field__hint">Terkunci: sudah ada data (isi checklist beda per jenis).</span><?php endif; ?><?php echo $err('profile'); ?>
                            </div>
                        <?php elseif (!$is_dyn): ?>
                            <div class="genc-field">
                                <span class="genc-field__label">Jenis</span>
                                <span class="genc-input gm-readonly"><?php echo genc_e($u['type'] !== '' ? $u['type'] : $f['title']); ?></span>
                                <span class="genc-field__hint">Tetap (isi checklist beda per jenis).</span>
                            </div>
                        <?php endif; ?>
                        <div class="<?php echo $fcls('aktif', 'is-wide'); ?>">
                            <label class="gm-switch gm-switch--row"><input type="checkbox" name="aktif" value="1"<?php echo $aktif_v ? ' checked' : ''; ?>><span aria-hidden="true"></span> <b>Aktif</b></label>
                            <span class="genc-field__hint">Nonaktif = tab, Home &amp; form isi disembunyikan (mis. unit dijual / rusak permanen). Data lama tetap bisa dibuka &amp; di-export, dan bisa diaktifkan lagi kapan saja. Approval / NOK History tetap menampilkan yang belum beres.</span><?php echo $err('aktif'); ?>
                        </div>
                        <div class="genc-formbar is-wide"><button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Simpan</button></div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Isi checklist unit ini<?php echo genc_reg_locked($u['profile']) ? ' <i class="fa fa-lock gm-titlelock" title="Dikunci" aria-label="Dikunci"></i>' : ''; ?></h3><a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key'], 'tipe' => (!$f['jenis'] && $f['mode'] === 'units' && count($f['def']['types']) > 1) ? $u['profile'] : null))); ?>#isi"><?php echo $can['edit'] && !genc_reg_locked($u['profile']) ? 'Ubah isi' : 'Lihat di folder'; ?> <i class="fa fa-angle-right"></i></a></div>
                <div class="genc-card__body"><p class="gm-lead">Sama untuk semua unit <?php echo genc_e($d['isi'] ? $d['isi']['label'] : $f['title']); ?> &mdash; diatur di folder <?php echo genc_e($f['title']); ?>.</p><?php echo genc_preview_items($d['preview']); ?></div>
            </div>

            <?php if ($can['delete']): /* [GENC-08OKT26-P3] hapus = pindah ke Arsip (semua unit) */ ?>
            <div class="genc-card gm-danger">
                <div class="genc-card__body">
                    <?php if ($u_lock): ?>
                        <b>Hapus unit</b><p class="genc-small genc-muted">Mesin dikunci &mdash; buka kunci dulu.</p>
                    <?php elseif ($u['aktif'] && !$this_other_active($f, $u['sel'])): ?>
                        <b>Hapus unit</b><p class="genc-small genc-muted">Ini satu-satunya unit aktif di <?php echo genc_e($f['title']); ?>. Untuk menyembunyikan seluruh mesin, hapus / nonaktifkan mesinnya.</p>
                    <?php else: ?>
                        <form method="post" action="<?php print_link('mesin/delete/' . ($u['kind'] === 'unit' ? 'unit/' . (int) $u['id'] : 'bawaan/' . rawurlencode($u['page'])) . '?csrf_token=' . $csrf); ?>" class="gm-danger__row">
                            <input type="hidden" name="slug" value="<?php echo genc_e($u['note_slug']); ?>">
                            <div><b>Hapus unit</b><p class="genc-small genc-muted">Dipindah ke <b>Arsip</b>: hilang dari tab, Home &amp; Approval. <?php echo number_format($u['count'], 0, ',', '.'); ?> checklist-nya tetap tersimpan &mdash; pulihkan kapan saja dari bagian Arsip.</p></div>
                            <input type="hidden" name="ya" value="1">
                            <button type="submit" class="genc-btn genc-btn--danger genc-btn--sm" data-genc-confirm="Hapus unit <?php echo genc_e($u['label']); ?>?" data-genc-confirm-msg="Unit dipindah ke Arsip (bukan dihapus permanen). Datanya tetap; bisa dipulihkan kapan saja." data-genc-confirm-ok="Ya, pindahkan ke Arsip" data-genc-danger><i class="fa fa-trash-o"></i> Hapus</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; /* arsip */ ?>

        <?php /* ====================================================== CADANGAN HAPUS PERMANEN  [GENC-13OKT26] */ elseif ($panel === 'cadangan'):
            $siap = isset($d['cad_siap']) ? (string) $d['cad_siap'] : ''; ?>
            <div class="genc-card gm-head">
                <div class="gm-head__ico"><i class="fa fa-file-archive-o"></i></div>
                <div class="gm-head__txt">
                    <h2 class="gm-head__title">Riwayat hapus permanen</h2>
                    <div class="gm-head__meta"><?php echo count($cad_list); ?> mesin / unit dihapus permanen &middot; file cadangan dibuat otomatis setiap kali mesin / unit dihapus permanen dari Arsip</div>
                </div>
            </div>
            <?php if ($siap !== '' && isset($cad_list[$siap])): $c = $cad_list[$siap]; ?>
            <?php if ($c['zip_ok']): ?>
            <div class="genc-card gm-cad-ready" data-gm-cad-ready="<?php echo genc_e(genc_url('mesin', array('cadangan' => $siap))); ?>">
                <div class="genc-card__body gm-danger__row">
                    <div><b><i class="fa fa-check-circle"></i> Cadangan siap diunduh</b><p class="genc-small genc-muted"><?php echo genc_e($c['what']); ?> sudah dihapus permanen. Unduh file ini &amp; simpan di tempat aman (mis. folder bersama / flashdisk IT) &mdash; hanya file ini yang bisa mengembalikannya.</p></div>
                    <a class="genc-btn genc-btn--primary" href="<?php echo genc_e(genc_url('mesin', array('cadangan' => $siap))); ?>"><i class="fa fa-download"></i> Unduh cadangan</a>
                </div>
            </div>
            <?php else: /* server tanpa ekstensi zip: cadangan = folder, unduh per file */ ?>
            <div class="genc-card gm-cad-ready">
                <div class="genc-card__body">
                    <b><i class="fa fa-check-circle"></i> Cadangan sudah dibuat</b><p class="genc-small genc-muted"><?php echo genc_e($c['what']); ?> sudah dihapus permanen. Server ini belum punya ekstensi PHP <i>zip</i>, jadi cadangan disimpan sebagai folder &mdash; unduh file-nya satu per satu di daftar bawah (minimal <span class="genc-mono">pemulihan.sql</span>) &amp; simpan di tempat aman.</p>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php /* [GENC-13OKT26c] ringkas seperti Riwayat perubahan: terbaru + "Lainnya" (lipat) + layar penuh berfilter */
            $bln = array('', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
            $cad_n = count($cad_list); $cad_k = array('mesin' => 0, 'unit' => 0); $cad_m = array();
            foreach ($cad_list as $c) {
                $cad_k[$c['kind'] === 'unit' ? 'unit' : 'mesin']++;
                $ts = strtotime((string) $c['at']); if ($ts) { $ym = date('Y-m', $ts); $cad_m[$ym] = $bln[(int) date('n', $ts)] . ' ' . date('Y', $ts); }
            }
            krsort($cad_m);
            $cad_li = function ($cid, $c, $full) use ($siap) {
                $kind = $c['kind'] === 'unit' ? 'unit' : 'mesin'; $ts = strtotime((string) $c['at']);
                $who = genc_person_name($c['by']); $when = genc_date($c['at']) . ' ' . genc_time($c['at']);
                $txt = strtolower(preg_replace('/\s+/', ' ', $c['title'] . ' ' . $kind . ' ' . $c['what'] . ' ' . $who . ' ' . $when));
                ob_start(); ?>
                    <li class="gm-cad<?php echo $cid === $siap ? ' is-new' : ''; ?>" data-gm-ver data-gm-vtext="<?php echo genc_e($txt); ?>" data-gm-vkind="<?php echo $kind; ?>" data-gm-vmonth="<?php echo $ts ? date('Y-m', $ts) : ''; ?>">
                        <span class="gm-cad__ico" aria-hidden="true"><i class="fa <?php echo $kind === 'unit' ? 'fa-file-text-o' : 'fa-folder-o'; ?>"></i></span>
                        <span class="gm-cad__txt">
                            <b><?php echo genc_e($c['title']); ?> <span class="genc-tag"><?php echo $kind; ?></span><?php if ($cid === $siap): ?> <span class="genc-tag gm-cad__baru">baru saja</span><?php endif; ?></b>
                            <small><?php echo genc_e($c['what']); ?></small>
                            <small>Dihapus <?php echo genc_e($who); ?> &middot; <?php echo genc_e($when); ?> &middot; <?php echo number_format((int) $c['checklist'], 0, ',', '.'); ?> checklist &middot; <?php echo (int) $c['pdf']; ?> PDF &middot; <?php echo (int) $c['foto']; ?> foto &middot; <?php echo genc_e(genc_cad_size($c['size'])); ?></small>
                        </span>
                        <?php if ($c['zip_ok']): ?>
                        <a class="genc-btn genc-btn--sm" href="<?php echo genc_e(genc_url('mesin', array('cadangan' => $cid))); ?>"><i class="fa fa-download"></i> Unduh .zip</a>
                        <?php elseif ($c['dir_ok'] && !empty($c['files'])): ?>
                        <details class="gm-cad__files"<?php echo $cid === $siap && !$full ? ' open' : ''; ?>><summary class="genc-btn genc-btn--sm"><i class="fa fa-download"></i> <?php echo count($c['files']); ?> file</summary>
                            <ul><?php foreach ($c['files'] as $fl): ?><li><a href="<?php echo genc_e(genc_url('mesin', array('cadangan' => $cid, 'file' => $fl))); ?>"><?php echo genc_e($fl); ?></a></li><?php endforeach; ?></ul>
                        </details>
                        <?php else: ?><span class="genc-tag">file tidak ada di server</span><?php endif; ?>
                    </li>
                <?php return ob_get_clean();
            };
            $cad_ids = array_keys($cad_list);
            $top = ($siap !== '' && isset($cad_list[$siap])) ? $siap : ($cad_ids ? $cad_ids[0] : '');
            $rest = array(); foreach ($cad_list as $cid => $c) { if ($cid !== $top) { $rest[$cid] = $c; } }
            $peek = array_slice($rest, 0, 5, true); ?>
            <div class="genc-card gm-hist" id="cadangan" data-gm-hist>
                <div class="genc-card__head">
                    <h3 class="genc-card__title"><i class="fa fa-trash-o" aria-hidden="true"></i> Semua cadangan <span class="genc-chip__count"><?php echo $cad_n; ?></span></h3>
                    <?php if ($cad_list): ?><a class="genc-btn genc-btn--sm genc-btn--ghost gm-hist__full" href="#cadangan-penuh" data-gm-vfull-open title="Buka semua cadangan di layar penuh (cari &amp; filter)"><i class="fa fa-expand" aria-hidden="true"></i><span> Layar penuh</span></a><?php endif; ?>
                </div>
                <?php if (!$cad_list): ?>
                <div class="genc-card__body"><p class="gm-lead">Belum ada yang dihapus permanen.</p></div>
                <?php else: ?>
                <ul class="gm-cads"><?php echo $cad_li($top, $cad_list[$top], false); ?></ul>
                <?php if ($rest): ?>
                <details class="gm-hist__more" data-gm-hist-more>
                    <summary><i class="fa fa-chevron-right gm-hist__chev" aria-hidden="true"></i> <span>Cadangan lain</span> <span class="genc-chip__count"><?php echo count($rest); ?></span><span class="gm-hist__hint">terbaru di atas</span></summary>
                    <ul class="gm-cads"><?php foreach ($peek as $cid => $c) { echo $cad_li($cid, $c, false); } ?></ul>
                    <?php if (count($rest) > count($peek)): ?><a class="gm-hist__all" href="#cadangan-penuh" data-gm-vfull-open><i class="fa fa-expand" aria-hidden="true"></i> Lihat semua <?php echo $cad_n; ?> cadangan di layar penuh</a><?php endif; ?>
                </details>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ($cad_list): ?>
            <div class="gm-vfull" id="cadangan-penuh" role="dialog" aria-modal="true" aria-labelledby="gm-cfull-title" data-gm-vfull data-gm-vhome="cadangan" data-gm-vunit="cadangan">
                <a class="gm-vfull__shade" href="#cadangan" tabindex="-1" aria-hidden="true" data-gm-vfull-close></a>
                <div class="gm-vfull__box">
                    <div class="gm-vfull__head">
                        <div class="gm-vfull__ttl">
                            <h2 id="gm-cfull-title"><i class="fa fa-trash-o" aria-hidden="true"></i> Riwayat hapus permanen</h2>
                            <p><?php echo $cad_n; ?> cadangan &middot; <?php echo $cad_k['mesin']; ?> mesin, <?php echo $cad_k['unit']; ?> unit &middot; terbaru di atas &middot; cadangan bisa diunduh ulang kapan saja.</p>
                        </div>
                        <a class="genc-btn genc-btn--sm gm-vfull__x" href="#cadangan" data-gm-vfull-close title="Kecilkan (Esc)"><i class="fa fa-compress" aria-hidden="true"></i><span> Kecilkan</span></a>
                    </div>
                    <div class="gm-vfull__tools gm-vfull__tools--wrap">
                        <div class="gm-vfull__find"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari nama mesin / unit atau siapa yang menghapus" aria-label="Cari cadangan" data-gm-vfind autocomplete="off"></div>
                        <div class="genc-chips" role="group" aria-label="Jenis">
                            <button type="button" class="genc-chip is-active" data-gm-vkind-btn="" aria-pressed="true">Semua <span class="genc-chip__count"><?php echo $cad_n; ?></span></button>
                            <button type="button" class="genc-chip" data-gm-vkind-btn="mesin" aria-pressed="false"<?php echo $cad_k['mesin'] ? '' : ' disabled'; ?>>Mesin <span class="genc-chip__count"><?php echo $cad_k['mesin']; ?></span></button>
                            <button type="button" class="genc-chip" data-gm-vkind-btn="unit" aria-pressed="false"<?php echo $cad_k['unit'] ? '' : ' disabled'; ?>>Unit <span class="genc-chip__count"><?php echo $cad_k['unit']; ?></span></button>
                        </div>
                        <?php if (count($cad_m) > 1): ?>
                        <select class="genc-input gm-vfull__month" aria-label="Bulan dihapus" data-gm-vmonth-sel>
                            <option value="">Semua bulan</option>
                            <?php foreach ($cad_m as $ym => $lbl): ?><option value="<?php echo $ym; ?>"><?php echo genc_e($lbl); ?></option><?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                        <span class="genc-small genc-muted" data-gm-vcount><?php echo $cad_n; ?> cadangan</span>
                    </div>
                    <ul class="gm-cads gm-vtl" data-gm-vlist><?php foreach ($cad_list as $cid => $c) { echo $cad_li($cid, $c, true); } ?></ul>
                    <p class="gm-vfull__none" data-gm-vnone hidden>Tidak ada cadangan yang cocok.</p>
                </div>
            </div>
            <?php endif; ?>
            <details class="genc-card gm-cad__help">
                <summary class="genc-card__head"><h3 class="genc-card__title"><i class="fa fa-life-ring" aria-hidden="true"></i> Cara memulihkan (developer)</h3><i class="fa fa-chevron-right gm-hist__chev" aria-hidden="true"></i></summary>
                <div class="genc-card__body">
                    <ol class="gm-cad__how">
                        <li>Ekstrak file .zip. Baca <span class="genc-mono">BACA-SAYA.txt</span>.</li>
                        <li>phpMyAdmin &rarr; database <span class="genc-mono"><?php echo genc_e(DB_NAME); ?></span> &rarr; tab <i>Import</i> &rarr; pilih <span class="genc-mono">pemulihan.sql</span> &rarr; <i>Go</i>.</li>
                        <li>Salin isi folder <span class="genc-mono">foto/</span> ke folder aplikasi <span class="genc-mono">uploads/mesin/</span> (kalau ada).</li>
                        <li>Buka Mesin &amp; Unit &rarr; <i>Arsip</i> &rarr; pilih mesin / unitnya &rarr; <b>Pulihkan</b>.</li>
                    </ol>
                    <p class="genc-small genc-muted">Report PDF &amp; Excel di dalam zip bisa langsung dibuka tanpa memulihkan apa pun. File cadangan disimpan di server (<span class="genc-mono">uploads/cadangan_hapus/</span>, atau <span class="genc-mono">uploads/mesin/_cadangan_hapus/</span> kalau folder pertama tidak boleh ditulis server; keduanya tidak bisa dibuka lewat alamat web).</p>
                </div>
            </details>

        <?php /* ====================================================== AWAL */ else: ?>
            <div class="genc-card gm-hero">
                <div class="gm-hero__ico" aria-hidden="true"><i class="fa fa-sitemap"></i></div>
                <h2 class="gm-hero__title">Kelola mesin &amp; unit tanpa coding</h2>
                <p class="gm-hero__lead">Folder di kiri = menu mesin di sidebar, file = unit (tab). Pilih salah satu untuk mengatur, atau mulai dari:</p>
                <?php if ($can['add']): ?>
                <div class="gm-choices">
                    <a class="gm-choice" href="<?php echo genc_e($url(array('baru' => 'unit'))); ?>">
                        <span class="gm-choice__ico"><span class="gm-ico2"><i class="fa fa-file-o"></i><i class="fa fa-plus"></i></span></span>
                        <b>Unit baru</b><span>Mesin yang sama, unitnya bertambah. Contoh: Pallet Mover 5, forklift baru, AGV TTL 11, Dumping KIR8, Geprek 2.</span>
                    </a>
                    <a class="gm-choice" href="<?php echo genc_e($url(array('baru' => 'jenis'))); ?>">
                        <span class="gm-choice__ico"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span></span>
                        <b>Mesin baru</b><span>Mesin / alat yang belum ada di menu, dengan item checklist sendiri. Contoh: Hand Pallet, Scissor Lift.</span>
                    </a>
                </div>
                <?php endif; ?>
                <div class="gm-stats">
                    <?php /* [GENC-08OKT26-P3] urutan sama dengan kartu di atasnya: unit kiri, mesin kanan */ ?>
                    <div><b><?php echo $units_total; ?></b><span>unit<?php echo $units_off ? ' (' . $units_off . ' nonaktif)' : ''; ?></span></div>
                    <div><b><?php echo $mesin_total; ?></b><span>mesin<?php echo $mesin_off ? ' (' . $mesin_off . ' nonaktif)' : ''; ?></span></div>
                </div>
                <ul class="gm-rules">
                    <li><i class="fa fa-shield"></i> Data checklist lama tidak pernah diubah. Mesin / unit yang <b>dihapus</b> masuk <b>Arsip</b> (seperti tong sampah: data &amp; tabel tetap) &mdash; bisa dipulihkan, atau <b>dihapus permanen</b> dengan file cadangan otomatis.</li>
                    <li><i class="fa fa-key"></i> Mesin baru: hak akses dicentang per jabatan saat dibuat. Unit baru ikut hak akses mesinnya. Atur lagi kapan saja di Role Permissions.</li>
                    <li><i class="fa fa-lock"></i> Pengaturan, unit &amp; isi checklist tiap mesin bisa diubah, lalu <b>dikunci</b> kalau sudah final.</li>
                    <li><i class="fa fa-history"></i> Setiap perubahan tersimpan di <b>Riwayat perubahan</b> mesinnya (bisa bolak-balik antar versi tanpa menambah versi baru) dan di App Logs (kategori <i>Mesin &amp; unit</i>).</li>
                </ul>
            </div>
        <?php endif; ?>
        </section>
    </div>
</div>
</div>
<?php echo genc_assets_js(); ?>
<script src="<?php echo genc_e(set_url('assets/js/genc-mesin.js')); ?>?v=<?php echo GENC_ASSET_VERSION; ?>"></script>
