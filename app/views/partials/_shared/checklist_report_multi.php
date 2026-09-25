<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/checklist_report_multi.php
 *
 *  Report MULTI HALAMAN - satu halaman per unit fisik.
 *  Dipakai hanya kalau filter unit di-set ke "Semua unit (gabungan)".
 *
 *  Kenapa opsi "Semua unit" TIDAK ditumpuk jadi satu halaman:
 *  satu halaman report = satu grid tanggal 1..31. Kalau beberapa unit dimuat
 *  bersamaan, dua record di tanggal yang sama (AGV CB 1 & AGV CB 2 sama-sama
 *  diisi tanggal 5) akan saling menimpa di sel yang sama - report kelihatan
 *  benar tapi isinya salah, TANPA pesan error. Jebakan ini sudah dicatat di
 *  BLUEPRINT bagian 6.2. Jadi "Semua unit" artinya: unit 1 di halaman 1,
 *  unit 2 di halaman 2, dst - bukan digabung.
 *
 *  Menerima $data_list = array of $data (kontraknya sama persis dengan
 *  checklist_report_body.php), disiapkan checklist_render_report().
 *
 *  CATATAN dompdf 0.8.3: page-break diletakkan di <div> PEMBUNGKUS tiap
 *  halaman, bukan di elemen kosong terpisah - div kosong sebagai pemisah
 *  sering diabaikan dompdf. Halaman TERAKHIR sengaja tidak diberi
 *  page-break-after supaya tidak muncul halaman kosong di ujung PDF.
 * ============================================================================
 */

require_once __DIR__ . '/checklist_helpers.php';

$data_list = isset($data_list) ? array_values((array) $data_list) : array();
$total_hal = count($data_list);
?>
<?php if ($total_hal === 0): ?>
    <div id="page-report-body" style="font-family: Arial, Helvetica, sans-serif; font-size:11px; padding:10px;">
        Tidak ada unit terdaftar untuk mesin ini, jadi tidak ada yang bisa ditampilkan.
        Daftar unit diatur di file profil mesin (bagian 'units').
    </div>
<?php else: ?>
    <?php foreach ($data_list as $i => $data): ?>
        <div<?php echo ($i < $total_hal - 1) ? ' style="page-break-after:always;"' : ''; ?>>
            <?php
            // checklist_report_body.php memakai $data dari scope ini.
            include __DIR__ . '/checklist_report_body.php';
            ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
