<?php
/**
 * Conveyor - form isi & ubah checklist (GENERASI C)            23 Sep 2026
 * Dipakai ConveyorController::add() DAN ::edit() ('mode' => 'add' | 'edit').
 * Item, standar, foto datang dari _shared/machines/conveyor.php.
 */
require_once ROOT . 'app/views/partials/_shared/genc_helpers.php';

$d        = $this->view_data;
$mode     = $d['mode'];
$items    = $d['items'];
$sections = $d['sections'];
$errors   = $d['errors'];
$record   = $d['record'];
$old      = $d['old'];
$csrf     = Csrf::$token;

if ($mode === 'edit' && !$record): ?>
    <?php echo genc_assets_css(); ?>
    <div class="genc"><div class="genc-container genc-container--narrow">
        <div class="genc-card"><div class="genc-empty">
            <div class="genc-empty__icon"><i class="fa fa-search"></i></div>
            <div class="genc-empty__title">Checklist tidak ditemukan</div>
            <p class="genc-muted">Mungkin sudah dihapus. <a href="<?php print_link('conveyor'); ?>">Kembali ke daftar</a></p>
        </div></div>
    </div></div>
<?php return; endif;

// Nilai yang ditampilkan: isian terakhir (kalau validasi gagal) > data record > kosong
$val = function ($key) use ($old, $record) {
    if (array_key_exists($key, $old)) { return (string) $old[$key]; }
    if ($record && isset($record[$key])) { return (string) $record[$key]; }
    return '';
};

$action   = ($mode === 'edit') ? 'conveyor/edit/' . (int) $record['id'] : 'conveyor/add';
$when     = ($mode === 'edit') ? $record['date_created'] : date('Y-m-d H:i:s');
$pelaksana= ($mode === 'edit') ? $record['user_created'] : USER_NAME;
$approved = $record && trim((string) $record['approval']) !== '' && stripos($record['approval'], 'not') === false;
$num      = 0;
?>
<?php echo genc_assets_css(); ?>
<div class="genc">
<div class="genc-container genc-container--narrow">

    <nav class="genc-crumbs" aria-label="Breadcrumb">
        <a href="<?php print_link('home'); ?>">Home</a><span>/</span>
        <a href="<?php print_link('conveyor'); ?>">Conveyor</a><span>/</span>
        <span><?php echo $mode === 'edit' ? 'Ubah checklist' : 'Isi checklist'; ?></span>
    </nav>
    <div class="genc-header">
        <div>
            <h1 class="genc-title"><?php echo $mode === 'edit' ? 'Ubah checklist conveyor' : 'Checklist AM conveyor'; ?></h1>
            <p class="genc-subtitle">Cek tiap bagian, pilih hasilnya. Kalau ada yang tidak baik, tulis di keterangan.</p>
        </div>
        <div class="genc-actions">
            <a class="genc-btn genc-btn--ghost" href="<?php print_link($mode === 'edit' ? 'conveyor/view/' . (int) $record['id'] : 'conveyor'); ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <form method="post" action="<?php print_link($action . '?csrf_token=' . $csrf); ?>" data-genc-checklist novalidate class="genc-stack">
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
                    <strong>Checklist hari ini sudah diisi</strong> oleh <?php echo genc_e(genc_person_name($t0['user_created'])); ?> pukul <?php echo genc_e(genc_time($t0['date_created'])); ?>.
                    Kalau diisi lagi, report bulanan memakai isian yang <strong>terakhir</strong>.
                    <a href="<?php print_link('conveyor/view/' . (int) $t0['id']); ?>">Lihat isian tadi</a>
                </div>
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
            <div class="genc-meta">
                <div><div class="genc-meta__k">Tanggal</div><div class="genc-meta__v"><?php echo genc_e(genc_date($when, true)); ?></div></div>
                <div><div class="genc-meta__k">Pelaksana</div><div class="genc-meta__v"><?php echo genc_e(genc_person_name($pelaksana)); ?></div></div>
                <div><div class="genc-meta__k">Mesin &middot; Area</div><div class="genc-meta__v">Conveyor &middot; Dumping Lt 4M</div></div>
            </div>
        </div>

        <?php foreach ($sections as $si => $s): ?>
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
                    $cur = strtoupper($val($key));
                    $err = isset($errors[$key]);
                ?>
                    <div class="genc-item<?php echo $err ? ' has-error' : ''; ?>" data-genc-item data-genc-name="<?php echo genc_e($it['part']); ?>" id="item-<?php echo genc_e($key); ?>">
                        <div class="genc-item__no"><?php echo (int) $it['no']; ?></div>
                        <?php if (!empty($it['foto'])): ?>
                            <button type="button" class="genc-thumb" data-genc-lightbox="<?php echo genc_e(set_url($it['foto'])); ?>" data-genc-caption="<?php echo genc_e($it['no'] . '. ' . $it['part']); ?>" aria-label="Perbesar foto <?php echo genc_e($it['part']); ?>">
                                <img src="<?php echo genc_e(set_url($it['foto'])); ?>" alt="" width="88" height="66"><?php /* [23SEP26] TANPA loading="lazy": page-scripts.js phpRAD (baris ~756) mengganti semua <img> yang belum termuat saat window load dengan no-image-available.png -> foto lazy jadi placeholder permanen */ ?>
                            </button>
                        <?php else: ?><span class="genc-thumb" aria-hidden="true"></span><?php endif; ?>
                        <div class="genc-item__body">
                            <div class="genc-item__name"><?php echo genc_e($it['part']); ?></div>
                            <div class="genc-item__std">Standar: <?php echo genc_e($it['standar']); ?></div>
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
            <textarea class="genc-textarea" id="ctrl-keterangan" name="keterangan" maxlength="1000" placeholder="Contoh: sensor tertutup debu, sudah dibersihkan &mdash; atau: motor bunyi kasar, sudah lapor ke Engineering (Pak ...)."><?php echo genc_e($val('keterangan')); ?></textarea>
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
