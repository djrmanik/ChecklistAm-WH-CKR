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
$units_total = 0; $units_off = 0; $jenis_n = 0;
foreach ($tree as $f) { if ($f['jenis']) { $jenis_n++; } foreach ($f['units'] as $u) { $units_total++; if (!$u['aktif']) { $units_off++; } } }
/** [GENC-06OKT26-ISI] status kunci folder: 'all' | 'some' | '' */
$folder_lock = function ($f) {
    if ($f['jenis']) { return genc_reg_locked($f['key']) ? 'all' : ''; }
    $ps = $f['mode'] === 'units' ? array_map(function ($t) { return $t['profile']; }, $f['def']['types']) : array($f['def']['profile']);
    $n = 0; foreach ($ps as $p) { if (genc_reg_locked($p)) { $n++; } }
    return $n === 0 ? '' : ($n === count($ps) ? 'all' : 'some');
};
$mode_txt = function ($f) {
    if ($f['jenis']) { return 'Jenis baru &middot; 1 tabel, unit = tab'; }
    return $f['mode'] === 'units' ? 'Bawaan &middot; 1 tabel, unit = tab' : 'Bawaan &middot; tiap unit punya tabel &amp; tab sendiri';
};
/** Baris item di editor jenis mesin. */
$item_row = function ($sk, $idx, $it, $no) {
    $db   = isset($it['db']) ? (string) $it['db'] : '';
    $key  = $db !== '' ? $db : (isset($it['key']) && $it['key'] !== '' ? $it['key'] : $idx);
    $del  = isset($it['aktif']) && !$it['aktif'];
    $foto = isset($it['foto']) ? (string) $it['foto'] : '';
    $n    = 'items[' . $sk . '][' . $idx . ']';
    ob_start(); ?>
<li class="gm-item<?php echo $del ? ' is-deleted' : ''; ?><?php echo $db === '' ? ' is-new' : ''; ?>" data-gm-item>
    <input type="hidden" name="<?php echo $n; ?>[db]" value="<?php echo genc_e($db); ?>">
    <input type="hidden" name="<?php echo $n; ?>[key]" value="<?php echo genc_e($key); ?>">
    <span class="gm-item__no" data-gm-no><?php echo (int) $no; ?></span>
    <div class="gm-item__photo">
        <label class="gm-photo<?php echo $foto !== '' ? ' has-img' : ''; ?>" title="Pilih foto part (JPG / PNG)">
            <?php if ($foto !== ''): ?><img src="<?php echo genc_e(set_url($foto)); ?>" alt=""><?php endif; ?>
            <span class="gm-photo__ph"><i class="fa fa-camera" aria-hidden="true"></i><small>Foto</small></span>
            <input type="file" name="foto[<?php echo genc_e($key); ?>]" accept="image/jpeg,image/png,image/webp,image/gif" data-gm-photo aria-label="Foto part">
        </label>
        <?php if ($foto !== ''): ?><label class="gm-mini"><input type="checkbox" name="<?php echo $n; ?>[foto_hapus]" value="1" data-gm-photo-del> hapus foto</label><?php endif; ?>
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
/** [GENC-06OKT26-ISI] Bilah kunci isi checklist (lock / unlock). */
$lock_bar = function ($isi) use ($can, $csrf) {
    $lk = $isi['locked'];
    ob_start(); ?>
<div class="genc-card gm-lock<?php echo $lk ? ' is-locked' : ''; ?>" id="isi">
    <div class="gm-lock__ico" aria-hidden="true"><i class="fa <?php echo $lk ? 'fa-lock' : 'fa-unlock-alt'; ?>"></i></div>
    <div class="gm-lock__txt">
        <b><?php echo $lk ? 'Isi checklist dikunci' : 'Isi checklist bisa diubah'; ?></b>
        <span><?php if ($lk): ?>Item, foto &amp; gambar report <?php echo genc_e($isi['label']); ?> tidak bisa diubah sampai kuncinya dibuka<?php echo $isi['kunci_by'] !== '' ? ' &middot; dikunci ' . genc_e(genc_person_name($isi['kunci_by'])) . ', ' . genc_e(genc_date($isi['kunci_at'])) : ''; ?>.<?php else: ?>Kunci kalau isinya sudah final (sudah dicek SPV) supaya tidak berubah tanpa sengaja.<?php echo $isi['kunci_by'] !== '' ? ' Kunci dibuka ' . genc_e(genc_person_name($isi['kunci_by'])) . ', ' . genc_e(genc_date($isi['kunci_at'])) . '.' : ''; ?><?php endif; ?></span>
    </div>
    <?php if ($can['edit'] && $isi['ready']): ?>
    <form method="post" action="<?php print_link('mesin/edit/kunci/' . rawurlencode($isi['profile']) . '?csrf_token=' . $csrf); ?>" class="gm-lock__act">
        <input type="hidden" name="kunci" value="<?php echo $lk ? '0' : '1'; ?>">
        <?php if ($lk): ?>
        <button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Buka kunci <?php echo genc_e($isi['label']); ?>?" data-genc-confirm-msg="Isi checklist bisa diubah lagi oleh role yang punya akses Mesin &amp; Unit. Kunci lagi setelah selesai." data-genc-confirm-ok="Buka kunci"><i class="fa fa-unlock-alt"></i> Buka kunci</button>
        <?php else: ?>
        <button type="submit" class="genc-btn genc-btn--sm gm-lock__btn"><i class="fa fa-lock"></i> Kunci isi</button>
        <?php endif; ?>
    </form>
    <?php endif; ?>
</div>
<?php return ob_get_clean();
};
/** [GENC-06OKT26-ISI] Kartu gambar bagian mesin (kolom kiri report). $edit = bisa diunggah. */
$gambar_card = function ($isi, $edit) {
    if (empty($isi['slots'])) { return ''; }
    ob_start(); ?>
<div class="genc-card">
    <div class="genc-card__head"><h3 class="genc-card__title">Gambar bagian mesin</h3><span class="genc-small genc-muted">kolom kiri report PDF / print</span></div>
    <div class="genc-card__body">
        <div class="gm-gslots">
        <?php foreach ($isi['slots'] as $k => $s):
            $st = $s['cur'] === '' ? 'Belum ada gambar' : ($s['is_ov'] ? 'Diunggah lewat menu' : 'Gambar bawaan'); ?>
            <div class="gm-gslot">
                <?php if ($edit): ?>
                <label class="gm-photo gm-photo--lg<?php echo $s['cur'] !== '' ? ' has-img' : ''; ?>" title="Pilih gambar (JPG / PNG)">
                    <?php if ($s['cur'] !== ''): ?><img src="<?php echo genc_e(set_url($s['cur'])); ?>" alt=""><?php endif; ?>
                    <span class="gm-photo__ph"><i class="fa fa-picture-o" aria-hidden="true"></i><small><?php echo $s['cur'] !== '' ? 'Ganti' : 'Unggah'; ?></small></span>
                    <input type="file" name="gambar[<?php echo genc_e($k); ?>]" accept="image/jpeg,image/png,image/webp,image/gif" data-gm-photo aria-label="Gambar <?php echo genc_e($s['label']); ?>">
                </label>
                <?php else: ?>
                <span class="gm-photo gm-photo--lg gm-photo--ro<?php echo $s['cur'] !== '' ? ' has-img' : ''; ?>"><?php if ($s['cur'] !== ''): ?><img src="<?php echo genc_e(set_url($s['cur'])); ?>" alt=""><?php else: ?><span class="gm-photo__ph"><i class="fa fa-picture-o" aria-hidden="true"></i></span><?php endif; ?></span>
                <?php endif; ?>
                <div class="gm-gslot__txt">
                    <b><?php echo genc_e($s['label']); ?></b><small><?php echo $st; ?></small>
                    <?php if ($edit):
                        $h = $s['is_ov'] ? ($s['orig'] !== '' ? 'kembalikan gambar bawaan' : 'hapus gambar') : ($s['cur'] !== '' ? 'sembunyikan gambar' : '');
                        if ($h !== ''): ?><label class="gm-mini"><input type="checkbox" name="gambar_hapus[<?php echo genc_e($k); ?>]" value="1" data-gm-photo-del> <?php echo $h; ?></label><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php if ($edit): ?><p class="genc-small genc-muted" style="margin:10px 0 0">Gambar otomatis diperkecil &amp; disesuaikan dengan kolom report (lebar 152&nbsp;px, tinggi mengikuti gambar, maks. 260&nbsp;px).</p><?php endif; ?>
    </div>
</div>
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
            <p class="genc-subtitle">Tambah unit atau jenis mesin baru tanpa coding &mdash; langsung jadi tab, menu, form isi, dan report.</p>
        </div>
        <?php if ($can['add']): ?>
        <div class="genc-actions">
            <a class="genc-btn" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $open_key))); ?>"><span class="gm-ico2"><i class="fa fa-file-o"></i><i class="fa fa-plus"></i></span> Unit baru</a>
            <a class="genc-btn genc-btn--primary" href="<?php echo genc_e($url(array('baru' => 'jenis'))); ?>"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span> Jenis mesin baru</a>
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
                    <a class="gm-tool" href="<?php echo genc_e($url(array('baru' => 'jenis'))); ?>" title="Jenis mesin baru (folder baru)" aria-label="Jenis mesin baru"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span></a>
                    <?php endif; ?>
                    <button type="button" class="gm-tool" data-gm-collapse title="Tutup semua folder" aria-label="Tutup semua folder"><i class="fa fa-minus-square-o"></i></button>
                </div>
            </div>
            <div class="gm-tree__find"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari mesin / unit" aria-label="Cari mesin atau unit" data-gm-filter autocomplete="off"></div>
            <div class="gm-tree__body" role="tree">
            <?php foreach ($tree as $fk => $f):
                $f_sel = ($sel_key === 'f:' . $fk);
                $f_off = $f['jenis'] && (empty($f['aktif']) || $f['items'] === 0);
                $f_lock = $folder_lock($f); ?>
                <details class="gm-folder" data-gm-folder open>
                    <summary class="gm-row gm-row--folder<?php echo $f_sel ? ' is-selected' : ''; ?><?php echo $f_off ? ' is-off' : ''; ?>" role="treeitem" aria-selected="<?php echo $f_sel ? 'true' : 'false'; ?>" data-gm-name="<?php echo genc_e(strtolower($f['title'])); ?>">
                        <span class="gm-chev" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                        <a class="gm-row__main" href="<?php echo genc_e($url(array('pilih' => 'f:' . $fk))); ?>"><i class="fa <?php echo genc_e($f['icon']); ?> gm-ico" aria-hidden="true"></i><span class="gm-name"><?php echo genc_e($f['title']); ?></span></a>
                        <?php if ($f['jenis']): ?><span class="gm-badge gm-badge--new" title="Dibuat lewat menu ini"><?php echo $f_off ? ($f['items'] === 0 ? 'draf' : 'nonaktif') : 'baru'; ?></span><?php endif; ?>
                        <?php if ($f_lock !== ''): ?><i class="fa fa-lock gm-flag gm-flag--lock<?php echo $f_lock === 'some' ? ' is-some' : ''; ?>" title="<?php echo $f_lock === 'some' ? 'Sebagian isi checklist dikunci' : 'Isi checklist dikunci'; ?>" aria-label="Dikunci"></i><?php endif; ?>
                        <span class="gm-count" title="<?php echo count($f['units']); ?> unit"><?php echo count($f['units']); ?></span>
                        <?php if ($can['add']): ?><a class="gm-row__act" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $fk))); ?>" title="Unit baru di <?php echo genc_e($f['title']); ?>" aria-label="Unit baru di <?php echo genc_e($f['title']); ?>"><i class="fa fa-plus"></i></a><?php endif; ?>
                    </summary>
                    <ul class="gm-files" role="group">
                    <?php foreach ($f['units'] as $u): $u_sel = ($sel_key === $u['sel']); ?>
                        <li><a class="gm-row gm-row--file<?php echo $u_sel ? ' is-selected' : ''; ?><?php echo !$u['aktif'] ? ' is-off' : ''; ?>" href="<?php echo genc_e($url(array('pilih' => $u['sel']))); ?>" role="treeitem" aria-selected="<?php echo $u_sel ? 'true' : 'false'; ?>" data-gm-name="<?php echo genc_e(strtolower($u['label'] . ' ' . $u['tab'] . ' ' . $u['type'] . ' ' . $u['kode'])); ?>">
                            <i class="fa fa-file-text-o gm-ico" aria-hidden="true"></i>
                            <span class="gm-name"><?php echo genc_e($u['tab']); ?></span>
                            <?php if ($u['type'] !== '' && $f['builtin'] && count($f['def']['types']) > 1): ?><small class="gm-sub"><?php echo genc_e($u['type']); ?></small><?php elseif ($u['sub'] !== '' && $f['mode'] === 'tables'): ?><small class="gm-sub"><?php echo genc_e($u['sub']); ?></small><?php endif; ?>
                            <?php if (!$u['aktif']): ?><i class="fa fa-eye-slash gm-flag" title="Nonaktif" aria-label="Nonaktif"></i><?php elseif ($u['kind'] !== 'bawaan'): ?><span class="gm-dot" title="Ditambah lewat menu ini" aria-label="Unit baru"></span><?php endif; ?>
                            <?php if ($f_lock === 'some' && $u['profile'] !== '' && genc_reg_locked($u['profile'])): ?><i class="fa fa-lock gm-flag gm-flag--lock" title="Isi checklist dikunci" aria-label="Dikunci"></i><?php endif; ?>
                        </a></li>
                    <?php endforeach; ?>
                    </ul>
                </details>
            <?php endforeach; ?>
                <div class="gm-tree__none" data-gm-none hidden>Tidak ada yang cocok.</div>
            </div>
            <div class="gm-tree__foot">
                <span><i class="fa fa-lock"></i> isi dikunci</span><span><span class="gm-dot"></span> ditambah di sini</span><span><i class="fa fa-eye-slash"></i> nonaktif</span>
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
                <div class="genc-card__head"><h2 class="genc-card__title"><span class="gm-ico2"><i class="fa fa-folder-o"></i><i class="fa fa-plus"></i></span> Jenis mesin baru</h2></div>
                <div class="genc-card__body">
                    <p class="gm-lead">Untuk mesin / alat yang <b>belum ada</b> di menu. Hasilnya: menu baru di sidebar, form isi, daftar, approval, dan report PDF &mdash; sama seperti mesin lain.</p>
                    <div class="genc-form">
                        <div class="<?php echo $fcls('nama'); ?>">
                            <label class="genc-field__label" for="m-nama">Nama jenis mesin <span class="genc-req">*</span></label>
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
                            <div class="gm-icons" role="radiogroup" aria-label="Ikon">
                            <?php $ik = $oval('ikon', 'fa-cube'); foreach ($d['icons'] as $ic): ?>
                                <label class="gm-iconpick" title="<?php echo genc_e(substr($ic, 3)); ?>"><input type="radio" name="ikon" value="<?php echo genc_e($ic); ?>"<?php echo $ik === $ic ? ' checked' : ''; ?>><span><i class="fa <?php echo genc_e($ic); ?>"></i></span></label>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="genc-field">
                            <label class="genc-field__label" for="m-salin">Salin isi checklist dari</label>
                            <select id="m-salin" class="genc-select" name="salin">
                                <option value="">&mdash; Mulai kosong &mdash;</option>
                                <?php foreach ($d['copy_sources'] as $k => $v): ?><option value="<?php echo genc_e($k); ?>"<?php echo $oval('salin') === $k ? ' selected' : ''; ?>><?php echo genc_e($v); ?></option><?php endforeach; ?>
                            </select>
                            <span class="genc-field__hint">Item (part, standar, metode, alat, durasi, foto) jadi awalan &mdash; bisa diubah setelah dibuat.</span>
                        </div>
                        <div class="<?php echo $fcls('akses'); ?>">
                            <label class="genc-field__label" for="m-akses">Hak akses awal sama dengan <span class="genc-req">*</span></label>
                            <select id="m-akses" class="genc-select" name="akses">
                                <?php foreach ($d['acl_sources'] as $k => $v): ?><option value="<?php echo genc_e($k); ?>"<?php echo $oval('akses', 'mesin_geprek') === $k ? ' selected' : ''; ?>><?php echo genc_e($v); ?></option><?php endforeach; ?>
                            </select>
                            <span class="genc-field__hint">Role yang bisa lihat / isi / approve mesin itu otomatis bisa di mesin baru. Atur lagi di Role Permissions.</span><?php echo $err('akses'); ?>
                        </div>
                    </div>
                </div>
                <div class="genc-card__foot genc-formbar">
                    <a class="genc-btn" href="<?php print_link('mesin'); ?>">Batal</a>
                    <button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Buat jenis mesin</button>
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
                                <label class="genc-field__label" for="u-label"><?php echo $nf['mode'] === 'tables' ? 'Nama tab' : 'Nama unit'; ?> <span class="genc-req">*</span></label>
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

        <?php /* ====================================================== FOLDER DIPILIH */ elseif ($sel && $sel['type'] === 'folder'):
            $f = $sel['folder']; $m = $f['jenis'] ? $f['mesin'] : null; ?>
            <div class="genc-card gm-head">
                <div class="gm-head__ico"><i class="fa <?php echo genc_e($f['icon']); ?>"></i></div>
                <div class="gm-head__txt">
                    <h2 class="gm-head__title"><?php echo genc_e($f['title']); ?></h2>
                    <div class="gm-head__meta"><?php echo $mode_txt($f); ?> &middot; <?php echo count($f['units']); ?> unit &middot; <?php echo number_format($f['count'], 0, ',', '.'); ?> checklist tersimpan</div>
                </div>
                <div class="gm-head__act">
                    <?php $menu_ok = ACL::is_allowed($f['menu'] . '/list'); if ($menu_ok && (!$f['jenis'] || $f['items'] > 0)): ?><a class="genc-btn genc-btn--sm" href="<?php print_link($f['menu']); ?>"><i class="fa fa-external-link"></i> Buka halaman</a><?php endif; ?>
                    <?php if ($can['add']): ?><a class="genc-btn genc-btn--sm genc-btn--primary" href="<?php echo genc_e($url(array('baru' => 'unit', 'folder' => $f['key']))); ?>"><i class="fa fa-plus"></i> Unit baru</a><?php endif; ?>
                </div>
            </div>

            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Unit / tab</h3><span class="genc-small genc-muted">Klik untuk mengatur</span></div>
                <div class="genc-table-wrap"><table class="genc-atable">
                    <thead><tr><th>Tab</th><th><?php echo $f['mode'] === 'tables' ? 'Kode' : 'Jenis'; ?></th><th>Status</th><th>Data</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($f['units'] as $u): ?>
                        <tr<?php echo !$u['aktif'] ? ' class="is-muted"' : ''; ?>>
                            <td data-label="Tab"><b><?php echo genc_e($u['tab']); ?></b><?php echo $u['tab'] !== $u['label'] ? '<span class="genc-cellsub">' . genc_e($u['label']) . '</span>' : ''; ?></td>
                            <td data-label="<?php echo $f['mode'] === 'tables' ? 'Kode' : 'Jenis'; ?>"><?php echo genc_e($f['mode'] === 'tables' ? ($u['kode'] !== '' ? $u['kode'] : '–') : ($u['type'] !== '' ? $u['type'] : $f['title'])); ?></td>
                            <td data-label="Status"><?php echo $u['aktif'] ? '<span class="genc-tag genc-tag--ok">Aktif</span>' : '<span class="genc-tag">Nonaktif</span>'; ?> <?php echo $u['kind'] === 'bawaan' ? '<span class="genc-tag" title="Unit awal (dari kode)"><i class="fa fa-code"></i> bawaan</span>' : '<span class="genc-tag genc-tag--primary">baru</span>'; ?></td>
                            <td data-label="Data"><?php echo number_format($u['count'], 0, ',', '.'); ?> checklist</td>
                            <td class="genc-col-actions"><a class="genc-btn genc-btn--sm" href="<?php echo genc_e($url(array('pilih' => $u['sel']))); ?>">Atur <i class="fa fa-angle-right"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>

            <?php if (!$f['jenis']): /* ---------------- [GENC-06OKT26-ISI] isi checklist mesin bawaan: bisa diubah, bisa dikunci */
                $isi = $d['isi']; $edit_ok = $can['edit'] && !$isi['locked'] && $isi['ready'];
                $who = array(); foreach ($f['units'] as $u) { if ($u['profile'] === $isi['profile']) { $who[] = $u['tab']; } } ?>
                <?php if (count($isi['types']) > 1): ?>
                <nav class="gm-ttabs" aria-label="Jenis unit">
                    <?php foreach ($isi['types'] as $tp => $tl): ?><a class="gm-ttab<?php echo $tp === $isi['profile'] ? ' is-active' : ''; ?>" href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key'], 'tipe' => $tp))); ?>#isi"<?php echo $tp === $isi['profile'] ? ' aria-current="page"' : ''; ?>><?php echo genc_e($tl); ?><?php if (genc_reg_locked($tp)): ?> <i class="fa fa-lock" aria-label="dikunci"></i><?php endif; ?></a><?php endforeach; ?>
                </nav>
                <?php endif; ?>
                <?php echo $lock_bar($isi); ?>
                <?php if (!$edit_ok): ?>
                <div class="genc-card">
                    <div class="genc-card__head"><h3 class="genc-card__title">Isi checklist <?php echo genc_e($isi['label']); ?></h3><?php echo $isi['changed'] ? '<span class="genc-tag genc-tag--primary" title="Diubah lewat menu ini">diubah lewat menu</span>' : '<span class="genc-tag"><i class="fa fa-code"></i> isi bawaan</span>'; ?></div>
                    <div class="genc-card__body">
                        <p class="gm-lead"><?php echo $isi['locked'] ? 'Terkunci &mdash; buka kunci di atas untuk mengubah item, foto, atau gambar report.' : ($isi['ready'] ? 'Role Anda hanya bisa melihat.' : 'Jalankan database/genc_06okt26.sql dulu untuk mengubah isi checklist.'); ?><?php echo $who ? ' Dipakai: ' . genc_e(implode(', ', $who)) . '.' : ''; ?></p>
                        <?php echo genc_preview_items($d['preview']); ?>
                    </div>
                </div>
                <?php echo $gambar_card($isi, false); ?>
                <?php else: $rows = isset($old['items_rows']) ? $old['items_rows'] : $isi['rows']; $no = 0; ?>
                <form method="post" action="<?php print_link('mesin/edit/isi/' . rawurlencode($isi['profile']) . '?csrf_token=' . $csrf); ?>" class="gm-editor" enctype="multipart/form-data" autocomplete="off" novalidate data-gm-form data-gm-editor>
                    <div class="genc-card<?php echo isset($e['items']) ? ' has-error' : ''; ?>">
                        <div class="genc-card__head"><h3 class="genc-card__title">Item checklist <?php echo genc_e($isi['label']); ?> <span class="genc-chip__count" data-gm-total>0</span></h3><?php echo $isi['changed'] ? '<span class="genc-tag genc-tag--primary">diubah lewat menu' . ($isi['upd_by'] !== '' ? ' &middot; ' . genc_e(genc_person_name($isi['upd_by'])) : '') . '</span>' : '<span class="genc-tag"><i class="fa fa-code"></i> isi bawaan</span>'; ?></div>
                        <div class="genc-card__body">
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
                    <?php if ($isi['changed']): ?>
                    <div class="genc-card gm-danger">
                        <div class="genc-card__body">
                            <form method="post" action="<?php print_link('mesin/edit/reset/' . rawurlencode($isi['profile']) . '?csrf_token=' . $csrf); ?>" class="gm-danger__row">
                                <div><b>Kembalikan ke isi bawaan</b><p class="genc-small genc-muted">Item, foto &amp; gambar report kembali seperti di kode. Item yang ditambah lewat menu disembunyikan; data checklist-nya tetap tersimpan.</p></div>
                                <input type="hidden" name="ya" value="1">
                                <button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Kembalikan isi <?php echo genc_e($isi['label']); ?> ke bawaan?" data-genc-confirm-msg="Semua perubahan isi checklist lewat menu ini dibatalkan. Data checklist lama tidak berubah." data-genc-confirm-ok="Ya, kembalikan" data-genc-danger><i class="fa fa-undo"></i> Kembalikan</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: /* ---------------- editor jenis mesin baru */
                $rows = isset($old['items_rows']) ? $old['items_rows'] : $m['items_arr'];
                $no = 0; $isi = $d['isi']; $j_lock = $isi['locked']; ?>
                <?php if ($f['items'] > 0 || $j_lock): echo $lock_bar($isi); endif; ?>
                <form method="post" action="<?php print_link('mesin/edit/jenis/' . rawurlencode($f['key']) . '?csrf_token=' . $csrf); ?>" class="gm-editor" enctype="multipart/form-data" autocomplete="off" novalidate data-gm-form data-gm-editor>
                    <div class="genc-card">
                        <div class="genc-card__head"><h3 class="genc-card__title">Identitas</h3>
                            <label class="gm-switch"><input type="checkbox" name="aktif" value="1"<?php echo ($oval('aktif', $m['aktif']) === '1') ? ' checked' : ''; ?>><span aria-hidden="true"></span> Aktif di menu</label></div>
                        <div class="genc-card__body genc-form">
                            <div class="<?php echo $fcls('nama'); ?>">
                                <label class="genc-field__label" for="j-nama">Nama <span class="genc-req">*</span></label>
                                <input id="j-nama" class="genc-input" name="nama" maxlength="60" value="<?php echo genc_e($oval('nama', $m['nama'])); ?>" required><?php echo $err('nama'); ?>
                            </div>
                            <div class="genc-field">
                                <label class="genc-field__label" for="j-area">Area</label>
                                <input id="j-area" class="genc-input" name="area" maxlength="100" value="<?php echo genc_e($oval('area', $m['area'])); ?>">
                            </div>
                            <div class="genc-field">
                                <label class="genc-field__label" for="j-pel">Pelaksanaan (di report)</label>
                                <input id="j-pel" class="genc-input" name="pelaksanaan" maxlength="150" value="<?php echo genc_e($oval('pelaksanaan', $m['pelaksanaan'])); ?>">
                            </div>
                            <div class="genc-field">
                                <span class="genc-field__label">Route &amp; tabel</span>
                                <span class="genc-input gm-readonly genc-mono"><?php echo genc_e($f['key']); ?></span>
                            </div>
                            <div class="genc-field is-wide">
                                <span class="genc-field__label">Ikon di sidebar</span>
                                <div class="gm-icons" role="radiogroup" aria-label="Ikon">
                                <?php $ik = $oval('ikon', $m['ikon']); foreach ($d['icons'] as $ic): ?>
                                    <label class="gm-iconpick" title="<?php echo genc_e(substr($ic, 3)); ?>"><input type="radio" name="ikon" value="<?php echo genc_e($ic); ?>"<?php echo $ik === $ic ? ' checked' : ''; ?>><span><i class="fa <?php echo genc_e($ic); ?>"></i></span></label>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($j_lock): ?>
                    <div class="genc-card">
                        <div class="genc-card__head"><h3 class="genc-card__title">Item checklist <span class="genc-chip__count"><?php echo (int) $f['items']; ?></span></h3><span class="genc-tag"><i class="fa fa-lock"></i> dikunci</span></div>
                        <div class="genc-card__body"><p class="gm-lead">Terkunci &mdash; buka kunci di atas untuk mengubah item, foto, atau gambar report. Nama, area, ikon &amp; status tetap bisa diubah.</p><?php echo genc_preview_items($d['preview']); ?></div>
                    </div>
                    <?php echo $gambar_card($isi, false); ?>
                    <?php else: ?>
                    <div class="genc-card<?php echo isset($e['items']) ? ' has-error' : ''; ?>">
                        <div class="genc-card__head"><h3 class="genc-card__title">Item checklist <span class="genc-chip__count" data-gm-total>0</span></h3><span class="genc-small genc-muted">Maks. <?php echo GENC_REG_MAX_ITEMS; ?> item</span></div>
                        <div class="genc-card__body">
                            <?php if ($f['items'] === 0 && !isset($old['items_rows'])): ?>
                                <div class="genc-notice genc-notice--info" style="margin-bottom:14px"><i class="fa fa-info-circle"></i><div><b>Belum ada item.</b> Tambahkan minimal 1 item lalu simpan &mdash; setelah itu <?php echo genc_e($f['title']); ?> muncul di sidebar &amp; bisa diisi.</div></div>
                            <?php endif; ?>
                            <p class="gm-lead">Urutan di sini = urutan di form isi &amp; report. Mengubah / menghapus item berlaku untuk checklist berikutnya; checklist lama tetap tersimpan.</p>
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
                    <?php if ($isi['ready'] && $f['items'] > 0) { echo $gambar_card($isi, true); } ?>
                    <?php endif; ?>
                    <div class="genc-savebar gm-savebar">
                        <div class="genc-savebar__text" data-gm-dirty-txt>Perubahan disimpan untuk checklist berikutnya.</div>
                        <a class="genc-btn" href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key']))); ?>">Batal</a>
                        <button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Simpan</button>
                    </div>
                </form>

                <?php if ($can['delete']): ?>
                <div class="genc-card gm-danger">
                    <div class="genc-card__body">
                        <?php if ($f['count'] > 0): ?>
                            <b>Hapus jenis mesin</b><p class="genc-small genc-muted">Tidak bisa: sudah ada <?php echo number_format($f['count'], 0, ',', '.'); ?> checklist. Hilangkan centang <i>Aktif di menu</i> untuk menyembunyikannya (data tetap tersimpan).</p>
                        <?php else: ?>
                            <form method="post" action="<?php print_link('mesin/delete/jenis/' . rawurlencode($f['key']) . '?csrf_token=' . $csrf); ?>" class="gm-danger__row">
                                <div><b>Hapus jenis mesin</b><p class="genc-small genc-muted">Belum ada checklist. Menu, tabel, unit &amp; hak akses <?php echo genc_e($f['title']); ?> ikut dihapus.</p></div>
                                <input type="hidden" name="ya" value="1">
                                <button type="submit" class="genc-btn genc-btn--danger genc-btn--sm" data-genc-confirm="Hapus <?php echo genc_e($f['title']); ?>?" data-genc-confirm-msg="Menu, tabel data (kosong), <?php echo count($f['units']); ?> unit, dan hak aksesnya dihapus. Tidak bisa dibatalkan." data-genc-confirm-ok="Ya, hapus" data-genc-danger><i class="fa fa-trash-o"></i> Hapus</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php /* ====================================================== UNIT DIPILIH */ elseif ($sel && $sel['type'] === 'unit'):
            $f = $sel['folder']; $u = $sel['unit']; $reg_units = genc_reg_data(); $reg_units = $reg_units['unit']; $row = ($u['kind'] === 'unit' && isset($reg_units[$u['id']])) ? $reg_units[$u['id']] : null;
            $link = $u['page'] . ($f['mode'] === 'units' ? '?unit=' . rawurlencode($u['slug']) : '');
            $edit_url = $u['kind'] === 'unit' ? 'mesin/edit/unit/' . (int) $u['id'] : 'mesin/edit/bawaan/' . rawurlencode($u['page']); ?>
            <div class="genc-card gm-head">
                <div class="gm-head__ico gm-head__ico--file"><i class="fa fa-file-text-o"></i></div>
                <div class="gm-head__txt">
                    <div class="gm-path"><a href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key']))); ?>"><i class="fa <?php echo genc_e($f['icon']); ?>"></i> <?php echo genc_e($f['title']); ?></a> <i class="fa fa-angle-right"></i></div>
                    <h2 class="gm-head__title"><?php echo genc_e($u['tab']); ?> <?php echo $u['kind'] === 'bawaan' ? '<span class="genc-tag" title="Unit awal (dari kode)"><i class="fa fa-code"></i> bawaan</span>' : '<span class="genc-tag genc-tag--primary">baru</span>'; ?> <?php echo !$u['aktif'] ? '<span class="genc-tag"><i class="fa fa-eye-slash"></i> nonaktif</span>' : ''; ?></h2>
                    <div class="gm-head__meta"><?php echo number_format($u['count'], 0, ',', '.'); ?> checklist tersimpan<?php if ($row && $row['created_by']): ?> &middot; ditambah <?php echo genc_e(genc_person_name($row['created_by'])); ?>, <?php echo genc_e(genc_date($row['created_at'])); ?><?php endif; ?></div>
                </div>
                <div class="gm-head__act">
                    <?php if ($u['aktif'] && ACL::is_allowed($u['page'] . '/list') && (!$f['jenis'] || $f['items'] > 0)): ?><a class="genc-btn genc-btn--sm" href="<?php print_link($link); ?>"><i class="fa fa-external-link"></i> Buka tab</a><?php endif; ?>
                </div>
            </div>

            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Pengaturan unit</h3></div>
                <?php if (!$can['edit']): ?>
                    <div class="genc-card__body"><p class="genc-muted">Role Anda hanya bisa melihat.</p></div>
                <?php elseif ($u['kind'] === 'bawaan' && $f['mode'] === 'tables'): ?>
                    <form method="post" action="<?php print_link($edit_url . '?csrf_token=' . $csrf); ?>" class="genc-card__body genc-form" autocomplete="off" novalidate data-gm-form>
                        <input type="hidden" name="slug" value="">
                        <div class="<?php echo $fcls('label'); ?>">
                            <label class="genc-field__label" for="b-label">Nama tab</label>
                            <input id="b-label" class="genc-input" name="label" maxlength="60" value="<?php echo genc_e($oval('label', $u['tab'])); ?>" placeholder="<?php echo genc_e($u['default_label']); ?>">
                            <span class="genc-field__hint">Tampil kalau <?php echo genc_e($f['title']); ?> punya lebih dari 1 unit. Kosongkan = <?php echo genc_e($u['default_label']); ?>.</span><?php echo $err('label'); ?>
                        </div>
                        <div class="genc-field">
                            <span class="genc-field__label">Halaman &amp; tabel</span>
                            <span class="genc-input gm-readonly genc-mono"><?php echo genc_e($u['page']); ?></span>
                            <span class="genc-field__hint">Halaman bawaan selalu aktif. Isi checklist &amp; report tidak berubah.</span>
                        </div>
                        <div class="genc-formbar is-wide"><button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Simpan</button></div>
                    </form>
                <?php else:
                    $is_dyn = $u['kind'] === 'unit';
                    $aktif_v = $oval('aktif', $u['aktif'] ? '1' : '0') === '1'; ?>
                    <form method="post" action="<?php print_link($edit_url . '?csrf_token=' . $csrf); ?>" class="genc-card__body genc-form" autocomplete="off" novalidate data-gm-form>
                        <input type="hidden" name="slug" value="<?php echo genc_e($u['slug']); ?>">
                        <?php if ($is_dyn): ?>
                        <div class="<?php echo $fcls('label'); ?>">
                            <label class="genc-field__label" for="x-label"><?php echo $f['mode'] === 'tables' ? 'Nama tab' : 'Nama unit'; ?> <span class="genc-req">*</span></label>
                            <input id="x-label" class="genc-input" name="label" maxlength="60" value="<?php echo genc_e($oval('label', $u['label'])); ?>" required>
                            <span class="genc-field__hint"><?php echo ($f['mode'] === 'units' && $u['count'] > 0) ? 'Sudah ada data: nama lama otomatis tetap dikenali, data lama ikut unit ini.' : 'Tampil di tab, Home &amp; report.'; ?></span><?php echo $err('label'); ?>
                        </div>
                            <?php if ($f['mode'] === 'tables'): ?>
                            <div class="genc-field">
                                <label class="genc-field__label" for="x-kode">Kode mesin</label>
                                <input id="x-kode" class="genc-input genc-mono" name="kode" maxlength="60" value="<?php echo genc_e($oval('kode', $u['kode'])); ?>">
                                <span class="genc-field__hint">Ditulis di kop report. Kosong = nama tab.</span>
                            </div>
                            <?php elseif ($f['builtin'] && count($f['def']['types']) > 1): $pv = $oval('profile', $u['profile']); ?>
                            <div class="<?php echo $fcls('profile'); ?>">
                                <label class="genc-field__label" for="x-prof">Jenis unit</label>
                                <select id="x-prof" class="genc-select" name="profile"<?php echo $u['count'] > 0 ? ' disabled' : ''; ?>>
                                    <?php foreach ($f['def']['types'] as $t): ?><option value="<?php echo genc_e($t['profile']); ?>"<?php echo $pv === $t['profile'] ? ' selected' : ''; ?>><?php echo genc_e($t['label']); ?></option><?php endforeach; ?>
                                </select>
                                <?php if ($u['count'] > 0): ?><input type="hidden" name="profile" value="<?php echo genc_e($u['profile']); ?>"><span class="genc-field__hint">Terkunci: sudah ada data (isi checklist beda per jenis).</span><?php endif; ?><?php echo $err('profile'); ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                        <div class="genc-field">
                            <span class="genc-field__label">Nama unit</span>
                            <span class="genc-input gm-readonly"><?php echo genc_e($u['label']); ?></span>
                            <span class="genc-field__hint">Unit bawaan: nama tercatat di data &amp; report lama, tidak diubah di sini.</span>
                        </div>
                        <div class="genc-field">
                            <span class="genc-field__label">Jenis</span>
                            <span class="genc-input gm-readonly"><?php echo genc_e($u['type'] !== '' ? $u['type'] : $f['title']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="<?php echo $fcls('aktif', 'is-wide'); ?>">
                            <label class="gm-switch gm-switch--row"><input type="checkbox" name="aktif" value="1"<?php echo $aktif_v ? ' checked' : ''; ?>><span aria-hidden="true"></span> <b>Aktif</b></label>
                            <span class="genc-field__hint">Nonaktif = tab, Home, Approval &amp; form isi disembunyikan (mis. unit dijual / rusak permanen). Data lama tetap bisa dibuka &amp; di-export.</span><?php echo $err('aktif'); ?>
                        </div>
                        <div class="genc-formbar is-wide"><button type="submit" class="genc-btn genc-btn--primary"><i class="fa fa-check"></i> Simpan</button></div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="genc-card">
                <div class="genc-card__head"><h3 class="genc-card__title">Isi checklist unit ini<?php echo genc_reg_locked($u['profile']) ? ' <i class="fa fa-lock gm-titlelock" title="Dikunci" aria-label="Dikunci"></i>' : ''; ?></h3><a class="genc-btn genc-btn--sm genc-btn--ghost" href="<?php echo genc_e($url(array('pilih' => 'f:' . $f['key'], 'tipe' => (!$f['jenis'] && $f['mode'] === 'units' && count($f['def']['types']) > 1) ? $u['profile'] : null))); ?>#isi"><?php echo $can['edit'] && !genc_reg_locked($u['profile']) ? 'Ubah isi' : 'Lihat di folder'; ?> <i class="fa fa-angle-right"></i></a></div>
                <div class="genc-card__body"><p class="gm-lead">Sama untuk semua unit <?php echo genc_e($d['isi'] ? $d['isi']['label'] : $f['title']); ?> &mdash; diatur di folder <?php echo genc_e($f['title']); ?>.</p><?php echo genc_preview_items($d['preview']); ?></div>
            </div>

            <?php if ($u['kind'] === 'unit' && $can['delete']): ?>
            <div class="genc-card gm-danger">
                <div class="genc-card__body">
                    <?php if ($u['count'] > 0): ?>
                        <b>Hapus unit</b><p class="genc-small genc-muted">Tidak bisa: sudah ada <?php echo number_format($u['count'], 0, ',', '.'); ?> checklist. Nonaktifkan saja (data tetap tersimpan).</p>
                    <?php elseif ($f['jenis'] && count($f['units']) <= 1): ?>
                        <b>Hapus unit</b><p class="genc-small genc-muted">Ini satu-satunya unit <?php echo genc_e($f['title']); ?>. Tambah unit lain dulu, atau hapus jenis mesinnya.</p>
                    <?php else: ?>
                        <form method="post" action="<?php print_link('mesin/delete/unit/' . (int) $u['id'] . '?csrf_token=' . $csrf); ?>" class="gm-danger__row">
                            <div><b>Hapus unit</b><p class="genc-small genc-muted">Belum ada checklist<?php echo $f['mode'] === 'tables' ? ' &mdash; tab, tabel (kosong) &amp; hak aksesnya ikut dihapus' : ''; ?>.</p></div>
                            <input type="hidden" name="ya" value="1">
                            <button type="submit" class="genc-btn genc-btn--danger genc-btn--sm" data-genc-confirm="Hapus unit <?php echo genc_e($u['tab']); ?>?" data-genc-confirm-msg="Unit belum punya data. Tidak bisa dibatalkan." data-genc-confirm-ok="Ya, hapus" data-genc-danger><i class="fa fa-trash-o"></i> Hapus</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

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
                        <b>Jenis mesin baru</b><span>Mesin / alat yang belum ada di menu, dengan item checklist sendiri. Contoh: Hand Pallet, Scissor Lift.</span>
                    </a>
                </div>
                <?php endif; ?>
                <div class="gm-stats">
                    <div><b><?php echo count($tree); ?></b><span>mesin</span></div>
                    <div><b><?php echo $units_total; ?></b><span>unit<?php echo $units_off ? ' (' . $units_off . ' nonaktif)' : ''; ?></span></div>
                    <div><b><?php echo $jenis_n; ?></b><span>jenis baru</span></div>
                </div>
                <ul class="gm-rules">
                    <li><i class="fa fa-shield"></i> Data checklist lama tidak pernah diubah. Unit yang sudah punya data cukup <b>dinonaktifkan</b>, bukan dihapus.</li>
                    <li><i class="fa fa-key"></i> Halaman baru mendapat hak akses salinan dari mesin acuan &mdash; atur lagi di Role Permissions.</li>
                    <li><i class="fa fa-lock"></i> Isi checklist (item, foto, gambar report) bisa diubah per mesin, lalu <b>dikunci</b> kalau sudah final.</li>
                    <li><i class="fa fa-history"></i> Semua perubahan tercatat di App Logs (kategori <i>Mesin &amp; unit</i>).</li>
                </ul>
            </div>
        <?php endif; ?>
        </section>
    </div>
</div>
</div>
<?php echo genc_assets_js(); ?>
<script src="<?php echo genc_e(set_url('assets/js/genc-mesin.js')); ?>?v=<?php echo GENC_ASSET_VERSION; ?>"></script>
