<?php
/**
 * RIWAYAT TAMPILAN (Super Editor UI)                       [GENC-13OKT26-TAMPILAN]
 * Pola sama dengan Riwayat perubahan Mesin & Unit (K84/K85) & Riwayat hapus permanen (K91):
 * versi sekarang + "Versi lain" dilipat + layar penuh (cari, filter halaman / user / bulan).
 * Kembalikan = pindah tanda "sekarang" (tidak membuat versi baru). JS layar penuh: assets/js/genc-mesin.js.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';
$d = $this->view_data;
$rows = $d['rows']; $cur_no = (int) $d['cur_no']; $csrf = $d['csrf']; $can = $d['can_edit'];
$bln = array('', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
$all_pages = array(); $all_users = array(); $all_months = array();
foreach ($rows as $r) {
    foreach ($r['pages'] as $p) { $all_pages[$p] = true; }
    foreach ($r['lines'] as $l) { if (isset($l['w'])) { $all_pages[$l['w']] = true; } }
    if ($r['by'] !== '') { $all_users[$r['by']] = genc_person_name($r['by']); }
    $ts = strtotime((string) $r['at']); if ($ts) { $all_months[date('Y-m', $ts)] = $bln[(int) date('n', $ts)] . ' ' . date('Y', $ts); }
}
ksort($all_pages); asort($all_users); krsort($all_months);
$back = set_url('tampilan');
$mode_url = function ($m) use ($csrf) { return set_url('tampilan/edit/mode/' . $m) . '?csrf_token=' . rawurlencode($csrf) . '&kembali=' . rawurlencode(set_url('home')); };
$ver_li = function ($r, $full) use ($csrf, $can, $cur_no, $back) {
    $cur = !empty($r['sekarang']);
    $pages = array();
    foreach ($r['lines'] as $l) { if (isset($l['w'])) { $pages[$l['w']] = true; } }
    foreach ($r['pages'] as $p) { $pages[$p] = true; }
    $who = $r['by'] !== '' ? genc_person_name($r['by']) : 'Sistem';
    $txt = strtolower('v' . $r['versi'] . ' ' . $r['ringkasan'] . ' ' . $who . ' ' . $r['catatan'] . ' ' . implode(' ', array_map(function ($l) { return (isset($l['w']) ? $l['w'] : '') . ' ' . (isset($l['old']) ? $l['old'] : '') . ' ' . (isset($l['new']) ? $l['new'] : '') . ' ' . (isset($l['what']) ? $l['what'] : ''); }, $r['lines'])));
    $ts = strtotime((string) $r['at']);
    ob_start(); ?>
<li class="gm-ver<?php echo $cur ? ' is-current' : ''; ?><?php echo !$cur && (int) $r['versi'] > $cur_no ? ' is-newer' : ''; ?>" data-gm-ver data-gm-vtext="<?php echo genc_e($txt); ?>" data-gm-vf-page="|<?php echo genc_e(implode('|', array_keys($pages))); ?>|" data-gm-vf-user="|<?php echo genc_e($r['by']); ?>|" data-gm-vmonth="<?php echo $ts ? date('Y-m', $ts) : ''; ?>">
    <span class="gm-ver__no">v<?php echo (int) $r['versi']; ?></span>
    <span class="gm-ver__txt"><b><?php echo genc_e($r['ringkasan']); ?></b><small><?php echo genc_e($who); ?><?php if ($ts): ?> &middot; <?php echo genc_e(genc_date($r['at'])); ?> <?php echo genc_e(genc_time($r['at'])); ?><?php endif; ?><?php echo $full && !empty($r['dari']) && (int) $r['dari'] !== (int) $r['versi'] - 1 ? ' &middot; <span class="gm-ver__from" title="Dibuat sesudah Kembalikan ke versi ' . (int) $r['dari'] . '">dari v' . (int) $r['dari'] . '</span>' : ''; ?></small>
    <?php if ($full): ?>
        <?php if ($r['lines'] || $r['data']): ?><ul class="gm-ver__diff tp-diff">
            <?php foreach ($r['lines'] as $l): ?>
                <?php if (isset($l['sec'])): $tm_hex = ''; if ($l['sec'] === 'tema' && !empty($l['new'])) { $tm_a = gx_tema_aksen(); $tm_hex = isset($tm_a[$l['new']]) ? $tm_a[$l['new']][1] : (gx_hex_ok($l['new']) ? $l['new'] : ''); } ?><li><span class="tp-diff__w"><?php echo genc_e($l['w']); ?></span> <?php if ($tm_hex !== ''): ?><span class="tp-chip" style="background:<?php echo genc_e($tm_hex); ?>"></span><?php endif; ?><?php echo genc_e($l['what']); ?></li>
                <?php else: ?><li><span class="tp-diff__w"><?php echo genc_e($l['w']); ?></span> <s><?php echo genc_e($l['old']); ?></s> &rarr; <b><?php echo genc_e($l['new']); ?></b><?php echo !empty($l['awal']) ? ' <span class="genc-muted">(teks awal)</span>' : ''; ?></li><?php endif; ?>
            <?php endforeach; ?>
            <?php foreach ($r['data'] as $l): ?><li class="tp-diff--data" title="Disimpan lewat menunya (tercatat di riwayatnya sendiri, tidak ikut Kembalikan versi tampilan)"><span class="tp-diff__w"><i class="fa fa-database" aria-hidden="true"></i> <?php echo genc_e($l['what']); ?></span> <s><?php echo genc_e($l['old']); ?></s> &rarr; <b><?php echo genc_e($l['new']); ?></b></li><?php endforeach; ?>
        </ul>
        <?php elseif (!empty($r['virtual']) || (int) $r['versi'] === 1): ?><span class="gm-ver__diff gm-ver__diff--none">Tampilan aplikasi sebelum ada perubahan lewat Mode edit tampilan.</span>
        <?php else: ?><span class="gm-ver__diff gm-ver__diff--none">Tidak ada rincian.</span><?php endif; ?>
        <?php if ($r['catatan'] !== ''): ?><span class="tp-note"><i class="fa fa-comment-o" aria-hidden="true"></i> <?php echo genc_e($r['catatan']); ?></span><?php endif; ?>
    <?php endif; ?>
    </span>
    <?php if ($cur): ?><span class="genc-tag genc-tag--ok">sekarang</span>
    <?php elseif ($can && empty($r['virtual'])): ?>
    <form method="post" action="<?php print_link('tampilan/edit/versi/' . (int) $r['versi'] . '?csrf_token=' . $csrf); ?>">
        <input type="hidden" name="kembali" value="<?php echo genc_e($back . ($full ? '#riwayat-penuh' : '#riwayat')); ?>">
        <button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Kembalikan tampilan ke v<?php echo (int) $r['versi']; ?>?" data-genc-confirm-msg="Teks &amp; susunan semua halaman kembali seperti v<?php echo (int) $r['versi']; ?> untuk semua user. Tidak ada versi baru: tanda &quot;sekarang&quot; pindah ke v<?php echo (int) $r['versi']; ?>, versi lain tetap bisa dipilih lagi. Nama mesin / unit &amp; label hasil cek yang diubah lewat jalan pintas tidak ikut (riwayatnya ada di menunya sendiri)." data-genc-confirm-ok="Ya, kembalikan"><i class="fa fa-undo"></i> Kembalikan</button>
    </form>
    <?php endif; ?>
</li>
<?php return ob_get_clean();
};
$cur_row = null; $others = array();
foreach ($rows as $r) { if (!empty($r['sekarang']) && $cur_row === null) { $cur_row = $r; } else { $others[] = $r; } }
if ($cur_row === null) { $cur_row = array_shift($others); }
$peek = array_slice($others, 0, 5);
$vn = count($rows);
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow tp-page">
    <nav class="genc-crumbs" aria-label="Breadcrumb"><a href="<?php print_link('home'); ?>">Home</a><span>/</span><span>Admin &amp; sistem</span><span>/</span><a href="<?php print_link('tampilan'); ?>">Riwayat tampilan</a></nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title">Riwayat tampilan</h1>
            <p class="genc-subtitle">Semua perubahan teks &amp; susunan lewat Mode edit tampilan. Versi yang berlaku ditandai <b>sekarang</b>.</p>
        </div>
        <?php if ($can): ?>
        <div class="genc-actions">
            <?php if ($d['mode'] === 'edit'): ?>
            <a class="genc-btn" href="<?php echo genc_e($mode_url('keluar')); ?>"><i class="fa fa-sign-out"></i> Keluar Mode edit</a>
            <?php else: ?>
            <a class="genc-btn genc-btn--primary" href="<?php echo genc_e($mode_url('edit')); ?>"><i class="fa fa-paint-brush"></i> Mode edit tampilan</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!$d['ok']): ?>
        <div class="genc-notice genc-notice--danger" style="margin-bottom:16px"><i class="fa fa-exclamation-circle"></i><div><b>Tabel tampilan belum bisa dibuat otomatis.</b> Jalankan <span class="genc-mono">database/genc_tampilan_14okt26.sql</span> di phpMyAdmin.</div></div>
    <?php endif; ?>

    <?php if ($d['draft_n']): ?>
    <div class="genc-notice genc-notice--warn tp-draft" style="margin-bottom:16px"><i class="fa fa-pencil"></i><div>
        <b>Draf lo: <?php echo (int) $d['draft_n']; ?> perubahan belum disimpan.</b> Belum terlihat oleh user lain.
        <div class="tp-draft__act">
            <?php if ($can): ?><a class="genc-btn genc-btn--sm genc-btn--primary" href="<?php echo genc_e($mode_url('edit')); ?>"><i class="fa fa-paint-brush"></i> Lanjutkan di Mode edit</a><?php endif; ?>
            <form method="post" action="<?php print_link('tampilan/edit/batal?csrf_token=' . $csrf); ?>"><input type="hidden" name="kembali" value="<?php echo genc_e($back); ?>"><button type="submit" class="genc-btn genc-btn--sm" data-genc-confirm="Buang draf?" data-genc-confirm-msg="<?php echo (int) $d['draft_n']; ?> perubahan di draf dibuang. Tampilan tetap seperti versi sekarang." data-genc-confirm-ok="Buang draf" data-genc-danger><i class="fa fa-times"></i> Buang draf</button></form>
        </div>
    </div></div>
    <?php endif; ?>

    <div class="genc-card gm-hist" id="riwayat" data-gm-hist>
        <div class="genc-card__head">
            <h3 class="genc-card__title"><i class="fa fa-history" aria-hidden="true"></i> Riwayat tampilan <span class="genc-chip__count"><?php echo $vn; ?></span></h3>
            <a class="genc-btn genc-btn--sm genc-btn--ghost gm-hist__full" href="#riwayat-penuh" data-gm-vfull-open title="Buka semua versi di layar penuh (cari &amp; filter)"><i class="fa fa-expand" aria-hidden="true"></i><span> Layar penuh</span></a>
        </div>
        <ul class="gm-vers gm-hist__now"><?php echo $ver_li($cur_row, false); ?></ul>
        <?php if ($others): ?>
        <details class="gm-hist__more" data-gm-hist-more>
            <summary><i class="fa fa-chevron-right gm-hist__chev" aria-hidden="true"></i> <span>Versi lain</span> <span class="genc-chip__count"><?php echo count($others); ?></span><span class="gm-hist__hint">terbaru di atas</span></summary>
            <ul class="gm-vers"><?php foreach ($peek as $r) { echo $ver_li($r, false); } ?></ul>
            <?php if (count($others) > count($peek)): ?><a class="gm-hist__all" href="#riwayat-penuh" data-gm-vfull-open><i class="fa fa-expand" aria-hidden="true"></i> Lihat semua <?php echo $vn; ?> versi di layar penuh</a><?php endif; ?>
        </details>
        <?php endif; ?>
        <p class="gm-hist__note"><b>v1</b> = tampilan awal aplikasi. Tiap <b>Simpan</b> di Mode edit tampilan yang benar-benar mengubah sesuatu = versi baru. <b>Kembalikan</b> hanya memindah tanda &quot;sekarang&quot; &mdash; tidak membuat versi baru, jadi v1 &harr; v2 &harr; v3 bisa bolak-balik.</p>
    </div>

    <div class="gm-vfull" id="riwayat-penuh" role="dialog" aria-modal="true" aria-labelledby="tp-vfull-title" data-gm-vfull data-gm-vhome="riwayat" data-gm-vunit="versi">
        <a class="gm-vfull__shade" href="#riwayat" tabindex="-1" aria-hidden="true" data-gm-vfull-close></a>
        <div class="gm-vfull__box">
            <div class="gm-vfull__head">
                <div class="gm-vfull__ttl">
                    <h2 id="tp-vfull-title"><i class="fa fa-history" aria-hidden="true"></i> Riwayat tampilan</h2>
                    <p><?php echo $vn; ?> versi &middot; sekarang v<?php echo $cur_no; ?> &middot; terbaru di atas &middot; tiap versi menampilkan apa yang berubah.</p>
                </div>
                <a class="genc-btn genc-btn--sm gm-vfull__x" href="#riwayat" data-gm-vfull-close title="Kecilkan (Esc)"><i class="fa fa-compress" aria-hidden="true"></i><span> Kecilkan</span></a>
            </div>
            <div class="gm-vfull__tools gm-vfull__tools--wrap">
                <div class="gm-vfull__find"><i class="fa fa-search" aria-hidden="true"></i><input type="search" class="genc-input" placeholder="Cari teks, halaman, atau nama" aria-label="Cari versi" data-gm-vfind autocomplete="off"></div>
                <?php if (count($all_pages) > 1): ?>
                <select class="genc-input gm-vfull__month" aria-label="Halaman" data-gm-vsel="page">
                    <option value="">Semua halaman</option>
                    <?php foreach (array_keys($all_pages) as $p): ?><option value="<?php echo genc_e($p); ?>"><?php echo genc_e($p); ?></option><?php endforeach; ?>
                </select>
                <?php endif; ?>
                <?php if (count($all_users) > 1): ?>
                <select class="genc-input gm-vfull__month" aria-label="User" data-gm-vsel="user">
                    <option value="">Semua user</option>
                    <?php foreach ($all_users as $u => $nm): ?><option value="<?php echo genc_e($u); ?>"><?php echo genc_e($nm); ?></option><?php endforeach; ?>
                </select>
                <?php endif; ?>
                <?php if (count($all_months) > 1): ?>
                <select class="genc-input gm-vfull__month" aria-label="Bulan" data-gm-vmonth-sel>
                    <option value="">Semua bulan</option>
                    <?php foreach ($all_months as $ym => $lbl): ?><option value="<?php echo $ym; ?>"><?php echo genc_e($lbl); ?></option><?php endforeach; ?>
                </select>
                <?php endif; ?>
                <span class="genc-small genc-muted" data-gm-vcount><?php echo $vn; ?> versi</span>
            </div>
            <ul class="gm-vers gm-vtl" data-gm-vlist><?php foreach ($rows as $r) { echo $ver_li($r, true); } ?></ul>
            <p class="gm-vfull__none" data-gm-vnone hidden>Tidak ada versi yang cocok.</p>
        </div>
    </div>

    <details class="genc-card gm-cad__help tp-help">
        <summary class="genc-card__head"><h3 class="genc-card__title"><i class="fa fa-question-circle" aria-hidden="true"></i> Cara pakai Mode edit tampilan</h3><i class="fa fa-chevron-right gm-hist__chev" aria-hidden="true"></i></summary>
        <div class="genc-card__body">
            <ol class="gm-cad__how">
                <li>Klik <b><i class="fa fa-paint-brush"></i> Mode edit tampilan</b> (pojok kanan atas, atau tombol di halaman ini).</li>
                <li>Buka halaman mana pun, lalu klik teks yang mau diubah (bergaris putus-putus). Ketik teks baru, pilih berlaku di mana, tekan <b>Enter</b>. Dikosongkan = kembali ke teks awal.</li>
                <li>Placeholder, tooltip &amp; isi pilihan: tombol <b>Teks lain</b> di bilah bawah. Nama mesin / unit &amp; label hasil cek ikut bisa diubah (disimpan lewat menunya).</li>
                <li><b>Pratinjau</b> = lihat halaman seperti user lain nanti melihatnya. <b>Simpan</b> = jadi versi baru untuk semua user. <b>Buang draf</b> = batalkan semua.</li>
            </ol>
            <p class="genc-small genc-muted">Yang tidak ikut: report PDF / Print / Word, dan teks yang berasal dari data (tanggal, angka, nama user, isi log, isi checklist).</p>
        </div>
    </details>
</div>
</div>
<?php echo genc_assets_js(); ?>
<script src="<?php echo genc_e(set_url('assets/js/genc-mesin.js')); ?>?v=<?php echo GENC_ASSET_VERSION; ?>"></script>
