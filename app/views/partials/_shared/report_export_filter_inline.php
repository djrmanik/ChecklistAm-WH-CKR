<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/report_export_filter_inline.php     23 Sep 2026
 *
 *  FILTER PERIODE (bulan & tahun) UNTUK TOMBOL EXPORT - versi TANPA RELOAD.
 *
 *  Kenapa ada versi kedua selain report_export_filter.php:
 *  report_export_filter.php bekerja dengan me-reload halaman memakai
 *  ?bulan=&tahun= di URL. Itu jalan untuk halaman list biasa (geprek, dumping,
 *  AGV), tapi TIDAK untuk tab-tab Forklift: tiap tab dirender lewat
 *  $this->render_page("forklift/forklift_1/...") di dalam forklift/list.php,
 *  jadi URL tab itu tetap (tidak ikut query string halaman induk), dan form
 *  GET akan membuka tab-nya sendirian tanpa bingkai tab.
 *
 *  Cara kerja versi ini:
 *  phpRAD (assets/js/page-scripts.js baris 431) MENULIS ULANG href tombol
 *  Export tepat saat diklik, dari atribut data-page-url milik
 *  <section class="ajax-page"> + format. Jadi yang diubah di sini adalah
 *  data-page-url section tab itu (ditambah bulan & tahun) - tepat sebelum
 *  handler phpRAD jalan (event listener fase capture). Hasilnya link Export
 *  = URL tab + bulan + tahun + format, dan report engine membacanya seperti
 *  biasa. Tidak ada reload, tab tidak pindah, daftar record tidak berubah.
 *
 *  Cara pakai - TEPAT SEBELUM <div class="dropup export-btn-holder mx-1">:
 *      <?php include ROOT . 'app/views/partials/_shared/report_export_filter_inline.php'; ?>
 *
 *  Unit TIDAK dipilih di sini: tiap tab forklift sudah satu unit.
 * ============================================================================
 */

require_once ROOT . 'app/views/partials/_shared/checklist_report_engine.php';

$rpfi_now_bulan = (int) date('n');
$rpfi_now_tahun = (int) date('Y');
$rpfi_nama      = checklist_nama_bulan();
$rpfi_awal      = min((int) CHECKLIST_PERIODE_TAHUN_AWAL, $rpfi_now_tahun);
$rpfi_id        = 'rptf-' . substr(md5(uniqid('', true)), 0, 10);
?>
<div class="report-export-filter mr-3" id="<?php echo $rpfi_id; ?>">
    <div class="form-inline m-0">
        <label class="mb-0 mr-2 text-muted" style="font-size:12px; white-space:nowrap;"
               title="Menentukan data yang diambil saat Export. Daftar record di halaman ini tidak ikut terfilter.">
            <i class="fa fa-filter mr-1"></i>Filter report
        </label>
        <select data-rptf="bulan" class="form-control form-control-sm mr-1" style="width:auto; font-size:12px;" aria-label="Bulan periode report">
            <?php foreach ($rpfi_nama as $rpfi_m => $rpfi_mn): ?>
            <option value="<?php echo $rpfi_m; ?>"<?php echo ($rpfi_m === $rpfi_now_bulan ? ' selected' : ''); ?>><?php echo $rpfi_mn; ?></option>
            <?php endforeach; ?>
        </select>
        <select data-rptf="tahun" class="form-control form-control-sm mr-1" style="width:auto; font-size:12px;" aria-label="Tahun periode report">
            <?php for ($rpfi_y = $rpfi_now_tahun; $rpfi_y >= $rpfi_awal; $rpfi_y--): ?>
            <option value="<?php echo $rpfi_y; ?>"<?php echo ($rpfi_y === $rpfi_now_tahun ? ' selected' : ''); ?>><?php echo $rpfi_y; ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="text-muted mt-1" style="font-size:11px; line-height:1.3;">
        Export PRINT / PDF / WORD menarik data unit tab ini untuk periode yang dipilih.
        Daftar di halaman ini tidak ikut terfilter.
    </div>
</div>
<script>
(function () {
    var box = document.getElementById('<?php echo $rpfi_id; ?>');
    if (!box) { return; }

    // Buang bulan/tahun lama dari query string, lalu tambahkan yang baru.
    // Sengaja manipulasi string (bukan new URL) supaya URL relatif pun aman.
    function withPeriod(url, bulan, tahun) {
        url = String(url || '');
        var hash = '';
        var h = url.indexOf('#');
        if (h >= 0) { hash = url.substring(h); url = url.substring(0, h); }
        var q = url.indexOf('?');
        var base = q >= 0 ? url.substring(0, q) : url;
        var parts = q >= 0 ? url.substring(q + 1).split('&') : [];
        var keep = [];
        for (var i = 0; i < parts.length; i++) {
            var k = parts[i].split('=')[0];
            if (parts[i] !== '' && k !== 'bulan' && k !== 'tahun') { keep.push(parts[i]); }
        }
        keep.push('bulan=' + encodeURIComponent(bulan));
        keep.push('tahun=' + encodeURIComponent(tahun));
        return base + '?' + keep.join('&') + hash;
    }

    function apply() {
        var bulan = box.querySelector('[data-rptf="bulan"]').value;
        var tahun = box.querySelector('[data-rptf="tahun"]').value;

        // 1) data-page-url section tab ini -> dipakai phpRAD saat tombol Export diklik
        var sec = box.closest ? box.closest('.ajax-page') : null;
        if (sec) {
            var cur = null;
            if (window.jQuery) { cur = window.jQuery(sec).data('page-url'); }
            if (!cur) { cur = sec.getAttribute('data-page-url'); }
            var next = withPeriod(cur, bulan, tahun);
            sec.setAttribute('data-page-url', next);
            if (window.jQuery) { window.jQuery(sec).data('page-url', next); }
        }

        // 2) href tombol Export juga diubah (cadangan kalau handler phpRAD tidak jalan)
        var holder = box.parentNode;
        var links = holder ? holder.querySelectorAll('a.export-link-btn') : [];
        for (var i = 0; i < links.length; i++) {
            links[i].setAttribute('href', withPeriod(links[i].getAttribute('href'), bulan, tahun));
        }
    }

    box.addEventListener('change', apply);

    // Fase CAPTURE: jalan SEBELUM handler phpRAD (bubbling, di document) yang
    // menulis ulang href dari data-page-url. Diterapkan ulang tiap klik supaya
    // tetap benar walau data-page-url sempat diganti (paging / search ajax).
    document.addEventListener('click', function (e) {
        var t = e.target;
        var a = (t && t.closest) ? t.closest('a.export-link-btn') : null;
        if (a && box.parentNode && box.parentNode.contains(a)) { apply(); }
    }, true);
})();
</script>
