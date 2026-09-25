<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/report_export_filter.php
 *
 *  FILTER UNTUK TOMBOL EXPORT: periode (bulan & tahun) + unit (nomor AGV /
 *  forklift / pallet mover, kalau mesinnya punya banyak unit).
 *
 *  Dulu bernama report_period_picker.php (22 Sep 2026 pagi, waktu isinya baru
 *  periode saja). Namanya diganti begitu filter unit ikut masuk ke sini,
 *  supaya nama file tetap jujur soal isinya. File lama dipertahankan sebagai
 *  shim yang meneruskan ke sini, jadi list.php yang belum sempat diganti
 *  tetap jalan.
 *
 *  Cara pakai di list.php - TEPAT SEBELUM <div class="dropup export-btn-holder">:
 *
 *      Mesin TANPA daftar unit (geprek, dumping):
 *          <?php include ROOT . 'app/views/partials/_shared/report_export_filter.php'; ?>
 *
 *      Mesin DENGAN daftar unit (AGV, nanti forklift & pallet mover) - set
 *      nama profilnya dulu supaya dropdown unitnya tahu mau menampilkan apa:
 *          <?php $report_filter_profile = 'agv_table_top_lift'; ?>
 *          <?php include ROOT . 'app/views/partials/_shared/report_export_filter.php'; ?>
 *
 *  ---------------------------------------------------------------------------
 *  CARA KERJANYA (kenapa ini aman & nyaris tanpa kode tambahan)
 *  ---------------------------------------------------------------------------
 *  Tombol Export sudah memakai $this->set_current_page_link(array('format'=>...)).
 *  Lihat system/BaseView.php baris 475: fungsi itu MENGGABUNGKAN seluruh query
 *  string halaman yang sedang dibuka dengan parameter baru. Jadi begitu URL
 *  halaman list mengandung ?bulan=8&tahun=2026&unit=agv-cb-1, SEMUA link Export
 *  otomatis ikut membawanya - tanpa mengubah satu baris pun di blok Export.
 *
 *  Di sisi report, ketiganya sudah dibaca checklist_render_report()
 *  (checklist_report_engine.php). Tanpa parameter apa pun, perilakunya:
 *  bulan berjalan + unit pertama di daftar profil.
 *
 *  Form ini cuma GET biasa ke halaman yang sama. TIDAK menyentuh query list,
 *  TIDAK menyentuh controller, TIDAK menyentuh report layout.
 *
 *  ---------------------------------------------------------------------------
 *  CATATAN UX
 *  ---------------------------------------------------------------------------
 *  Filter ini SENGAJA cuma mempengaruhi hasil Export, bukan daftar record di
 *  halaman ini - supaya tidak ada risiko mengubah perilaku halaman list yang
 *  dipakai 20 orang tiap pagi. Karena itu label & kalimat di bawah dropdown
 *  ditulis tegas, biar user tidak bingung kenapa daftarnya tidak ikut berubah.
 *
 *  Daftar tahun: dari CHECKLIST_PERIODE_TAHUN_AWAL (SAKLAR 5) sampai tahun
 *  berjalan. Daftar unit: dari 'units' di file profil mesin.
 * ============================================================================
 */

require_once ROOT . 'app/views/partials/_shared/checklist_report_engine.php';

$rpp_now_bulan = (int) date('n');
$rpp_now_tahun = (int) date('Y');

$rpp_req = isset($this->route->request) ? (array) $this->route->request : array();

$rpp_bulan = isset($rpp_req['bulan']) ? (int) $rpp_req['bulan'] : $rpp_now_bulan;
$rpp_tahun = isset($rpp_req['tahun']) ? (int) $rpp_req['tahun'] : $rpp_now_tahun;
if ($rpp_bulan < 1 || $rpp_bulan > 12)       { $rpp_bulan = $rpp_now_bulan; }
if ($rpp_tahun < 2000 || $rpp_tahun > 2100)  { $rpp_tahun = $rpp_now_tahun; }

// --- daftar unit (kosong = mesin ini tidak punya pemilih unit) --------------
$rpp_units = array();
if (!empty($report_filter_profile)) {
    try {
        $rpp_units = checklist_machine_units(checklist_load_machine($report_filter_profile));
    } catch (\Throwable $e) {
        $rpp_units = array(); // profil bermasalah -> filter unit disembunyikan, periode tetap jalan
    }
}
$rpp_unit_awal = !empty($rpp_units) ? key($rpp_units) : ''; // unit pertama = default engine
$rpp_unit_req = isset($rpp_req['unit']) ? strtolower(trim((string) $rpp_req['unit'])) : '';
$rpp_unit_sel = '';
if (!empty($rpp_units)) {
    if ($rpp_unit_req === 'semua') {
        $rpp_unit_sel = 'semua';
    } elseif (isset($rpp_units[$rpp_unit_req])) {
        $rpp_unit_sel = $rpp_unit_req;
    } else {
        $rpp_unit_sel = $rpp_unit_awal; // default = unit pertama, sama dengan engine
    }
}

// Parameter lain di URL (search, orderby, dst) dibawa lewat hidden input,
// supaya form GET ini tidak menghapusnya.
$rpp_keep = $rpp_req;
unset($rpp_keep['bulan'], $rpp_keep['tahun'], $rpp_keep['unit'], $rpp_keep['format'], $rpp_keep['request_uri']);

// URL halaman ini TANPA query string (parameter kedua true = jangan warisi query).
$rpp_action = $this->set_current_page_link(array(), true);

$rpp_nama_bulan  = checklist_nama_bulan();
$rpp_tahun_awal  = (int) CHECKLIST_PERIODE_TAHUN_AWAL;
$rpp_tahun_akhir = max($rpp_now_tahun, $rpp_tahun);
if ($rpp_tahun_awal > $rpp_tahun) { $rpp_tahun_awal = $rpp_tahun; }

$rpp_is_default = ($rpp_bulan === $rpp_now_bulan && $rpp_tahun === $rpp_now_tahun
                   && ($rpp_unit_sel === '' || $rpp_unit_sel === $rpp_unit_awal));
$rpp_periode    = $rpp_nama_bulan[$rpp_bulan] . ' ' . $rpp_tahun;
?>
<!-- Wadahnya SATU div supaya tetap jadi satu item flex di dalam
     <div class="p-3 d-flex justify-content-between"> milik list.php.
     Hasilnya: filter nempel di kiri, tombol Export di kanan. -->
<div class="report-export-filter mr-3">
    <form method="get" action="<?php print_link($rpp_action); ?>" class="form-inline m-0">
        <?php foreach ($rpp_keep as $rpp_k => $rpp_v) { if (is_scalar($rpp_v)) { ?>
        <input type="hidden" name="<?php echo htmlspecialchars((string) $rpp_k, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars((string) $rpp_v, ENT_QUOTES, 'UTF-8'); ?>">
        <?php } } ?>

        <label class="mb-0 mr-2 text-muted" style="font-size:12px; white-space:nowrap;"
               title="Menentukan data yang diambil saat Export. Daftar record di halaman ini tidak ikut terfilter.">
            <i class="fa fa-filter mr-1"></i>Filter report
        </label>

        <?php if (!empty($rpp_units)): ?>
        <select name="unit" class="form-control form-control-sm mr-1" style="width:auto; font-size:12px;"
                onchange="this.form.submit();" aria-label="Unit yang di-export">
            <?php foreach ($rpp_units as $rpp_slug => $rpp_u): ?>
            <option value="<?php echo htmlspecialchars($rpp_slug, ENT_QUOTES, 'UTF-8'); ?>"<?php echo ($rpp_slug === $rpp_unit_sel ? ' selected' : ''); ?>><?php echo htmlspecialchars($rpp_u['label'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
            <option value="semua"<?php echo ($rpp_unit_sel === 'semua' ? ' selected' : ''); ?>>Semua unit (<?php echo count($rpp_units); ?> halaman)</option>
        </select>
        <?php endif; ?>

        <select name="bulan" class="form-control form-control-sm mr-1" style="width:auto; font-size:12px;"
                onchange="this.form.submit();" aria-label="Bulan periode report">
            <?php foreach ($rpp_nama_bulan as $rpp_m => $rpp_mn): ?>
            <option value="<?php echo $rpp_m; ?>"<?php echo ($rpp_m === $rpp_bulan ? ' selected' : ''); ?>><?php echo $rpp_mn; ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tahun" class="form-control form-control-sm mr-1" style="width:auto; font-size:12px;"
                onchange="this.form.submit();" aria-label="Tahun periode report">
            <?php for ($rpp_y = $rpp_tahun_akhir; $rpp_y >= $rpp_tahun_awal; $rpp_y--): ?>
            <option value="<?php echo $rpp_y; ?>"<?php echo ($rpp_y === $rpp_tahun ? ' selected' : ''); ?>><?php echo $rpp_y; ?></option>
            <?php endfor; ?>
        </select>

        <!-- Tombol cadangan kalau JavaScript mati / browser lama -->
        <noscript><button type="submit" class="btn btn-sm btn-outline-secondary mr-1">Set</button></noscript>

        <?php if (!$rpp_is_default): ?>
        <a href="<?php print_link($rpp_action); ?>" class="text-muted ml-1" style="font-size:11.5px; white-space:nowrap;"
           title="Kembali ke pilihan awal (bulan berjalan<?php echo (!empty($rpp_units) ? ', unit pertama' : ''); ?>)"><i class="fa fa-undo mr-1"></i>reset</a>
        <?php endif; ?>
    </form>

    <div class="text-muted mt-1" style="font-size:11px; line-height:1.3;">
        <?php if ($rpp_unit_sel === 'semua'): ?>
            Export menarik <strong>semua <?php echo count($rpp_units); ?> unit</strong> periode
            <strong><?php echo htmlspecialchars($rpp_periode, ENT_QUOTES, 'UTF-8'); ?></strong> &mdash;
            satu unit per halaman, jadi <?php echo count($rpp_units); ?> halaman.
        <?php elseif ($rpp_unit_sel !== ''): ?>
            Export menarik data <strong><?php echo htmlspecialchars($rpp_units[$rpp_unit_sel]['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
            periode <strong><?php echo htmlspecialchars($rpp_periode, ENT_QUOTES, 'UTF-8'); ?></strong>
            untuk PRINT / PDF / WORD.
        <?php else: ?>
            Export menarik data <strong><?php echo htmlspecialchars($rpp_periode, ENT_QUOTES, 'UTF-8'); ?></strong>
            untuk PRINT / PDF / WORD.
        <?php endif; ?>
        Daftar di halaman ini tidak ikut terfilter.
    </div>
</div>
