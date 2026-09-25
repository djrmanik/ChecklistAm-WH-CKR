<?php
/**
 * GENERASI C - form isi & ubah checklist (VIEW BERSAMA semua mesin) [GENC-24SEP26]
 * Asal: app/views/partials/conveyor/add.php (23 Sep 2026).
 * Dipakai GencChecklistBase::add() DAN ::edit() ('mode' => 'add' | 'edit').
 * Item, standar, foto datang dari _shared/machines/<profil>.php.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d        = $this->view_data;
$g        = $d['genc'];          // [GENC-24SEP26] nama mesin, area, route - dari controller
$pg       = $g['page'];
$mode     = $d['mode'];
$items    = $d['items'];
$sections = $d['sections'];
$errors   = $d['errors'];
$record   = $d['record'];
$old      = $d['old'];
$csrf     = Csrf::$token;
// [GENC-24SEP26-AGV] multi-unit (K7). Mesin 1 unit: semua variabel ini kosong -> tampilan lama.
$multi    = !empty($g['multi']);
$unit     = $multi ? $g['unit'] : '';
$uq       = ($multi && $mode === 'add') ? '?unit=' . rawurlencode($unit) : '';

if ($mode === 'pick'):   /* [GENC-24SEP26-AGV] form isi dibuka tanpa unit -> pilih unit dulu */ ?>
    <?php echo genc_assets_css(); ?>
    <div class="genc"><div class="genc-container genc-container--narrow">
        <nav class="genc-crumbs" aria-label="Breadcrumb">
            <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
            <a href="<?php print_link($pg); ?>"><?php echo genc_e($g['heading']); ?></a><span>/</span>
            <span>Isi checklist</span>
        </nav>
        <div class="genc-header">
            <div>
                <h1 class="genc-title">Pilih unit <?php echo genc_e($g['machine']); ?></h1>
                <p class="genc-subtitle">Checklist diisi per unit. Pilih unit yang sedang Anda cek.</p>
            </div>
            <div class="genc-actions">
                <a class="genc-btn genc-btn--ghost" href="<?php print_link($pg); ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
        <?php echo genc_tabs($g, 'add'); ?>
        <div class="genc-unitpick">
            <?php foreach ($g['units'] as $slug => $label): $t0 = isset($d['unit_today'][$slug]) ? $d['unit_today'][$slug] : null; ?>
                <a class="genc-card genc-unitpick__item" href="<?php echo genc_e(genc_url($pg . '/add', array('unit' => $slug))); ?>">
                    <span class="genc-unitpick__name"><?php echo genc_e($label); ?></span><?php if (!empty($g['unit_machines'][$slug])): /* [GENC-24SEP26-FORKLIFT] jenis unit */ ?><span class="genc-small genc-muted genc-unitpick__type"><?php echo genc_e($g['unit_machines'][$slug]); ?></span><?php endif; ?>

                    <?php if ($t0): ?>
                        <span class="genc-badge genc-badge--ok"><i class="fa fa-check"></i> Hari ini sudah diisi<?php echo genc_time($t0['date_created']) ? ' ' . genc_e(genc_time($t0['date_created'])) : ''; ?></span>
                    <?php else: ?>
                        <span class="genc-badge"><i class="fa fa-clock-o"></i> Belum diisi hari ini</span>
                    <?php endif; ?>
                    <i class="fa fa-angle-right genc-unitpick__go" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div></div>
    <?php echo genc_assets_js(); ?>
<?php return; endif;

if ($mode === 'edit' && !$record): ?>
    <?php echo genc_assets_css(); ?>
    <div class="genc"><div class="genc-container genc-container--narrow">
        <div class="genc-card"><div class="genc-empty">
            <div class="genc-empty__icon"><i class="fa fa-search"></i></div>
            <div class="genc-empty__title">Checklist tidak ditemukan</div>
            <p class="genc-muted">Mungkin sudah dihapus. <a href="<?php print_link($pg); ?>">Kembali ke daftar</a></p>
        </div></div>
    </div></div>
<?php return; endif;

// Nilai yang ditampilkan: isian terakhir (kalau validasi gagal) > data record > kosong
$val = function ($key) use ($old, $record) {
    if (array_key_exists($key, $old)) { return (string) $old[$key]; }
    if ($record && isset($record[$key])) { return (string) $record[$key]; }
    return '';
};

$action   = ($mode === 'edit') ? $pg . '/edit/' . (int) $record['id'] : $pg . '/add';
$back     = ($mode === 'edit') ? $pg . '/view/' . (int) $record['id'] : $pg . $uq;   // [GENC-24SEP26-AGV] daftar unit yang sama
$when     = ($mode === 'edit') ? $record['date_created'] : date('Y-m-d H:i:s');
$pelaksana= ($mode === 'edit') ? $record['user_created'] : USER_NAME;
$approved = $record && !empty($record['_approved']);   // [GENC-24SEP26-DUMPING] dinilai controller
$legacy   = ($record && !empty($record['_legacy'])) ? $record['_legacy'] : array();
$num      = 0;
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">

    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <a href="<?php print_link($pg); ?>"><?php echo genc_e($g['heading']); ?></a><span>/</span>
        <?php if (!empty($g['tabs'])): ?><span><?php echo genc_e($g['title']); ?></span><span>/</span><?php endif; ?>
        <span><?php echo $mode === 'edit' ? 'Ubah checklist' : 'Isi checklist'; ?></span>
    </nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo $mode === 'edit' ? 'Ubah checklist ' . genc_e($g['name_lower']) : 'Checklist AM ' . genc_e($g['name_lower']); ?></h1>
            <p class="genc-subtitle">Cek tiap bagian, pilih hasilnya. Kalau ada yang tidak baik, tulis di keterangan.</p>
        </div>
        <div class="genc-actions">
            <a class="genc-btn genc-btn--ghost" href="<?php print_link($back); ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <?php echo genc_tabs($g, $mode === 'add' ? 'add' : 'record'); /* [GENC-24SEP26-DUMPING] */ ?>

    <form method="post" action="<?php print_link($action . '?csrf_token=' . $csrf . ($uq !== '' ? '&unit=' . rawurlencode($unit) : '')); ?>" data-genc-checklist novalidate class="genc-stack">
        <input type="hidden" name="csrf_token" value="<?php echo genc_e($csrf); ?>">

        <?php if (!empty($errors)): ?>
            <div class="genc-notice genc-notice--danger" role="alert">
                <i class="fa fa-exclamation-circle"></i>
                <div>
                    <strong>Belum bisa disimpan.</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?><li><?php echo $e; /* pesan dibuat controller, sudah aman */ ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'add' && !empty($d['today'])): $t0 = $d['today'][0]; ?>
            <div class="genc-notice genc-notice--warn">
                <i class="fa fa-info-circle"></i>
                <div>
                    <strong>Checklist <?php echo $multi ? genc_e($g['unit_label']) . ' ' : ''; ?>hari ini sudah diisi</strong> oleh <?php echo genc_e(genc_person_name($t0['user_created'])); ?><?php echo genc_time($t0['date_created']) !== '' ? ' pukul ' . genc_e(genc_time($t0['date_created'])) : ''; /* [GENC-24SEP26-PALLETMOVER] kolom DATE: tanpa "pukul" */ ?>.
                    Kalau diisi lagi, report bulanan memakai isian yang <strong>terakhir</strong>.
                    <a href="<?php print_link($pg . '/view/' . (int) $t0['id']); ?>">Lihat isian tadi</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'edit' && !empty($legacy)): ?>
            <div class="genc-notice genc-notice--info">
                <i class="fa fa-history"></i>
                <div><strong>Checklist ini diisi lewat form lama</strong> (hasil diketik bebas). <?php echo count($legacy); ?> item perlu dipilih ulang: Baik / Tidak baik / Perawatan &mdash; isian lamanya ditampilkan di bawah nama item sebagai petunjuk.</div>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'edit' && $approved): ?>
            <div class="genc-notice genc-notice--warn">
                <i class="fa fa-lock"></i>
                <div><strong>Checklist ini sudah di-approve.</strong> Kalau hasil atau keterangannya diubah, approval dikosongkan dan perlu di-approve ulang.</div>
            </div>
        <?php endif; ?>

        <!-- Info pengisian: otomatis, tidak perlu diketik -->
        <div class="genc-card genc-card__body">
            <div class="genc-meta<?php echo $multi ? ' genc-meta--4' : ''; ?>">
                <?php if ($multi): /* [GENC-24SEP26-AGV] unit dari tab aktif (add) / dari record (edit) - tidak bisa diganti di sini */ ?>
                <div class="genc-meta__unit"><div class="genc-meta__k">Unit</div><div class="genc-meta__v"><i class="fa fa-cube"></i> <?php echo genc_e($g['unit_label']); ?></div></div>
                <?php endif; ?>
                <div><div class="genc-meta__k">Tanggal</div><div class="genc-meta__v"><?php echo genc_e(genc_date($when, true)); ?></div></div>
                <div><div class="genc-meta__k">Pelaksana</div><div class="genc-meta__v"><?php echo genc_e(genc_person_name($pelaksana)); ?></div></div>
                <div><div class="genc-meta__k">Mesin &middot; Area</div><div class="genc-meta__v"><?php echo genc_e($multi ? $g['machine'] : $g['title']); ?> &middot; <?php echo genc_e($g['area']); ?></div></div>
            </div>
        </div>

        <?php if (!empty($g['extra_fields'])) { include __DIR__ . '/_extra_fields.php'; /* [GENC-24SEP26-FORKLIFT] jam kerja forklift */ } foreach ($sections as $si => $s): ?>
            <section class="genc-card" aria-labelledby="sec-<?php echo genc_e($s['key']); ?>">
                <div class="genc-card__head">
                    <div class="genc-section-title">
                        <h2 class="genc-card__title" id="sec-<?php echo genc_e($s['key']); ?>"><?php echo genc_e(isset($s['short']) ? $s['short'] : $s['title']); ?></h2>
                        <span class="genc-section-title__num"><?php echo count($s['items']); ?> item</span>
                    </div>
                    <span class="genc-small genc-muted"><?php echo genc_e($s['title']); ?></span>
                </div>
                <?php foreach ($s['items'] as $it):
                    $key = $it['db'];
                    $cur = strtoupper(trim($val($key)));
                    $err = isset($errors[$key]);
                ?>
                    <div class="genc-item<?php echo $err ? ' has-error' : ''; ?>" data-genc-item data-genc-name="<?php echo genc_e($it['part']); ?>" id="item-<?php echo genc_e($key); ?>">
                        <div class="genc-item__no"><?php echo (int) $it['no']; ?></div>
                        <?php if (!empty($it['foto'])): ?>
                            <button type="button" class="genc-thumb" data-genc-lightbox="<?php echo genc_e(set_url($it['foto'])); ?>" data-genc-caption="<?php echo genc_e($it['no'] . '. ' . $it['part']); ?>" aria-label="Perbesar foto <?php echo genc_e($it['part']); ?>">
                                <img src="<?php echo genc_e(set_url($it['foto'])); ?>" alt="" width="88" height="66"><?php /* [23SEP26] TANPA loading="lazy": page-scripts.js phpRAD (baris ~756) mengganti semua <img> yang belum termuat saat window load dengan no-image-available.png -> foto lazy jadi placeholder permanen */ ?>
                            </button>
                        <?php else: echo genc_thumb_empty($it, $g); /* [GENC-24SEP26-AGV] K19: kotak "Belum ada foto", klik = penjelasan */ endif; ?>
                        <div class="genc-item__body">
                            <div class="genc-item__name"><?php echo genc_e($it['part']); ?></div>
                            <div class="genc-item__std">Standar: <?php echo genc_e($it['standar']); ?></div>
                            <?php if (isset($legacy[$key]) && (!array_key_exists($key, $old) || $old[$key] === '')): /* [GENC-24SEP26-DUMPING] K6 */ ?>
                                <div class="genc-item__legacy"><i class="fa fa-history"></i> Isian lama: &ldquo;<?php echo genc_e(trim($legacy[$key]) !== '' ? $legacy[$key] : '(kosong)'); ?>&rdquo; &mdash; pilih ulang hasilnya</div>
                            <?php endif; ?>
                            <?php if ($it['alat'] !== '' || $it['metode'] !== ''): ?>
                                <details class="genc-item__how">
                                    <summary>Cara cek</summary>
                                    <dl>
                                        <dt>Metode</dt><dd><?php echo genc_e($it['metode']); ?></dd>
                                        <dt>Alat</dt><dd><?php echo genc_e($it['alat']); ?></dd>
                                        <?php if ($it['durasi'] !== ''): ?><dt>Durasi</dt><dd>&plusmn;<?php echo genc_e($it['durasi']); ?></dd><?php endif; ?>
                                    </dl>
                                </details>
                            <?php endif; ?>
                            <?php if ($err): ?><div class="genc-item__err"><?php echo $errors[$key]; ?></div><?php endif; ?>
                        </div>
                        <div class="genc-item__answer">
                            <div class="genc-seg" role="radiogroup" aria-label="Hasil <?php echo genc_e($it['part']); ?>">
                                <?php foreach (array('OK' => array('Baik', 'fa-check'), 'NOK' => array('Tidak baik', 'fa-times'), 'PR' => array('Perawatan', 'fa-wrench')) as $v => $lb):
                                    $rid = 'r-' . $key . '-' . strtolower($v); ?>
                                    <input type="radio" id="<?php echo $rid; ?>" name="<?php echo genc_e($key); ?>" value="<?php echo $v; ?>"<?php echo $cur === $v ? ' checked' : ''; ?>>
                                    <label for="<?php echo $rid; ?>"><i class="fa <?php echo $lb[1]; ?>"></i> <?php echo $lb[0]; ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>

        <div class="genc-card genc-card__body genc-field<?php echo isset($errors['keterangan']) ? ' has-error' : ''; ?>" data-genc-keterangan id="item-keterangan">
            <label class="genc-field__label" for="ctrl-keterangan">Keterangan <span class="genc-req-if">wajib diisi karena ada item tidak baik / perawatan</span></label>
            <textarea class="genc-textarea" id="ctrl-keterangan" name="keterangan" maxlength="<?php echo (int) (isset($g['keterangan_max']) ? $g['keterangan_max'] : 1000); ?>" placeholder="<?php echo $g['keterangan_contoh']; /* HTML dari controller */ ?>"><?php echo genc_e($val('keterangan')); ?></textarea>
            <?php if (isset($errors['keterangan'])): ?><div class="genc-item__err"><?php echo $errors['keterangan']; ?></div><?php endif; ?>
            <div class="genc-field__hint">Tulis apa yang ditemukan dan tindak lanjutnya. Boleh dikosongkan kalau semua baik.</div>
        </div>

        <div class="genc-savebar">
            <div class="genc-savebar__progress">
                <div class="genc-savebar__text"><span><b data-genc-count>0 dari <?php echo count($items); ?></b> item diisi</span><span data-genc-state></span></div>
                <div class="genc-meter"><span data-genc-meter style="width:0%"></span></div>
            </div>
            <button type="submit" class="genc-btn genc-btn--primary genc-btn--lg"><i class="fa fa-check"></i> <?php echo $mode === 'edit' ? 'Simpan perubahan' : 'Simpan checklist'; ?></button>
        </div>
    </form>
</div>
</div>
<?php echo genc_assets_js(); ?>
