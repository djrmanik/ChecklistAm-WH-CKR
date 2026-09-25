<?php
/**
 * ============================================================================
 *  app/views/partials/mesin_geprek/report.php
 *  Report bulanan khusus Mesin Geprek — struktur PERSIS mengikuti template
 *  excel lama "Checklist AM Std TDO" (bukan format "standard" 4 mesin lain,
 *  karena Geprek gak punya kolom Alat/Metode/Durasi/Pelaksanaan).
 * ============================================================================
 *
 * REFERENSI 14 ITEM — SUDAH DIHARDCODE DI BAWAH, GAK PERLU DIISI DARI DB
 * ---------------------------------------------------------------------------
 * Kategori + Standar itu teks TETAP (gak pernah berubah per hari), makanya
 * ditulis langsung di file ini. Yang berubah per hari cuma status OK/NOK-nya,
 * yang datang dari tabel `mesin_geprek` di database.
 *
 * PEMETAAN "no urut di template" -> "nama kolom di tabel mesin_geprek":
 *   1  tatakan_jumbo_bag           8  roda_tatakan_jumbobag
 *   2  punch                       9  tombol_panel_kabel
 *   3  body_mesin_geprek          10  mesin_compressor
 *   4  panel_hmi                  11  baut_body_mesin_geprek
 *   5  sensor                     12  putaran_tatakan_jumbobag
 *   6  as_punch                   13  selang_hidroplik_olimotor
 *   7  rantai_utama               14  baut_punch
 * (Ini didapat dari nyocokin urutan 14 item di excel dgn urutan 14 kolom
 *  yg dipakai di Mesin_geprekController.php->index(), cocok persis 1:1.)
 *
 * KONTRAK DATA ($data) — INI YANG PERLU DISIAPIN CONTROLLER
 * ---------------------------------------------------------------------------
 * $data = [
 *   'lokasi'         => '4M',
 *   'nama_mesin'     => 'GEPREK (PRESS)',
 *   'diperiksa_oleh' => '',                 // opsional, nama kalau ada
 *   'bulan_tahun'    => 'Agustus 2026',     // PARAMETER laporan, BUKAN kolom
 *                                           // di database — ini nentuin
 *                                           // bulan mana yg mau ditampilin,
 *                                           // dipakai buat nge-filter query
 *                                           // "WHERE date_created di bulan
 *                                           // itu" sebelum data ini disusun.
 *   'day_count'      => 31,
 *   'photo_image'    => 'assets/images/geprek_photo_panel.png', // hasil crop excel, statis
 *   'checks' => [
 *       // key = nama kolom db (lihat pemetaan di atas), value = [hari => nilai mentah dari DB]
 *       'tatakan_jumbo_bag' => [1 => 'OK', 2 => 'OK', 5 => 'NOK'],
 *       'punch'             => [1 => 'OK'],
 *       // ...lengkapi utk 14 kolom (yg kosong/gak ada hari itu = belum submit)
 *   ],
 *   'pelaksana' => [
 *       // key = tanggal, value = isi kolom user_created hari itu
 *       1 => 'edy.haryanto', 2 => 'candra',
 *   ],
 * ];
 *
 * CARA CONTROLLER MANGGIL FILE INI:
 * ---------------------------------------------------------------------------
 *   $this->view->report_title       = "Checklist AM Mesin Geprek";
 *   $this->view->report_layout      = "report_layout.php";
 *   $this->view->report_paper_size  = "A4";
 *   $this->view->report_orientation = "landscape";
 *   $this->render_view("mesin_geprek/report.php", $data);
 *
 * CATATAN / HAL YANG PERLU DICEK LAGI:
 * ---------------------------------------------------------------------------
 * [ASUMSI] "OK"/"NOK" ditampilkan APA ADANYA dari nilai mentah di database
 *   (bukan diubah jadi simbol ✓/✗), karena kita belum 100% yakin nilai lain
 *   apa aja yang mungkin muncul dari dropdown (Menu::$garpu). Kalau nilainya
 *   mengandung "OK" (bukan "NOK") -> ditulis hijau. Mengandung "NOK" -> merah.
 *   Selain itu -> ditulis apa adanya, warna hitam.
 * [BERES 10 Sep 2026] Baris "Pelaksana" sekarang pakai daftar inisial RESMI
 *   dari supervisor (bukan auto-derive huruf depan lagi) — lihat
 *   geprek_helpers.php -> geprek_user_initial(). Kolom tanggal tetap sempit
 *   (~16px) jadi INISIAL tetap dipakai, bukan nama lengkap.
 * [BERES 10 Sep 2026] Query ke database utk ngisi 'checks' & 'pelaksana' udah
 *   dikerjain di Mesin_geprekController.php -> render_checklist_report_inner().
 *   PENTING: 'pelaksana' yang dikirim controller HARUS tetap username MENTAH
 *   (contoh 'edy.haryanto'), BUKAN inisial yang udah jadi — konversi ke
 *   inisial terjadi DI SINI SAJA (lewat geprek_user_initial() di bawah), biar
 *   gak ke-proses dobel.
 */

require_once __DIR__ . '/geprek_helpers.php'; // geprek_row_val(), geprek_user_initial(), geprek_bulan_tahun_label()

if (!function_exists('geprek_e')) {
    function geprek_e($v) { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('geprek_check_cell')) {
    function geprek_check_cell($raw) {
        $raw = trim((string) ($raw ?? ''));
        if ($raw === 'N/A') {
            // Update 17 Sep 2026: hari sudah lewat tanpa record (checklist_na_days())
            return '<td align="center" style="border:1px solid #333; width:2.32%; padding:2px 1px; font-size:7px; font-weight:bold; color:#8a8a8a;">N/A</td>';
        }
        if ($raw === '') {
            // JANGAN pakai &nbsp; (atau karakter non-ASCII apa pun) di output
            // report ini. phpRAD (system/BaseView.php -> setInnerHTML()) nge-
            // htmlentities() dulu isi report terus di-appendXML(); &nbsp; itu
            // BUKAN entity XML yang sah -> parse gagal -> isi report hilang dan
            // yang kecetak cuma kop layout doang. Sel kosong cukup <td></td>.
            return '<td style="border:1px solid #333; width:2.32%; padding:2px 1px;"></td>';
        }
        $upper = strtoupper($raw);
        if (strpos($upper, 'NOK') !== false) {
            $color = '#c0392b';
        } elseif (strpos($upper, 'OK') !== false) {
            $color = '#0a7d1e';
        } else {
            $color = '#111';
        }
        return '<td align="center" style="border:1px solid #333; width:2.32%; padding:2px 1px; font-size:7px; font-weight:bold; color:' . $color . ';">' . geprek_e($raw) . '</td>';
    }
}

// ---------------------------------------------------------------------------
// 14 item reference — TETAP, sesuai persis template excel "Checklist AM Std TDO"
// ---------------------------------------------------------------------------
$GEPREK_SECTIONS = [
    [
        'title' => 'STANDAR PEMBERSIHAN (CLEANING)',
        'area_label' => 'Area pembersihan',
        'standard_label' => 'Pembersihan standar',
        'items' => [
            ['no' => 1, 'kategori' => 'Tatakan jumbo bag',        'standar' => 'Tidak ada sisa matarial atau kotoran', 'db_field' => 'tatakan_jumbo_bag'],
            ['no' => 2, 'kategori' => 'Pembersihan punch',        'standar' => 'Tidak ada sisa gemuk di piston',       'db_field' => 'punch'],
            ['no' => 3, 'kategori' => 'Pembersihan body mesin geprek', 'standar' => 'Bersih',                          'db_field' => 'body_mesin_geprek'],
            ['no' => 4, 'kategori' => 'Panel HMI',                'standar' => 'Bersih',                               'db_field' => 'panel_hmi'],
            ['no' => 5, 'kategori' => 'Sensor-sensor',            'standar' => 'Bersih',                               'db_field' => 'sensor'],
        ],
    ],
    [
        'title' => 'STANDAR PELUMASAN (LUBRICATING)',
        'area_label' => 'Area pelumasan',
        'standard_label' => 'Pelumasan standar',
        'items' => [
            ['no' => 6, 'kategori' => 'Punch (AS)',     'standar' => 'Terlumasi', 'db_field' => 'as_punch'],
            ['no' => 7, 'kategori' => 'Rantai utama',   'standar' => 'Terlumasi', 'db_field' => 'rantai_utama'],
        ],
    ],
    [
        'title' => 'STANDAR PENGECEKAN (INSPECTION)',
        'area_label' => 'Area pengecekan',
        'standard_label' => 'Pengecekan standar',
        'items' => [
            ['no' => 8,  'kategori' => 'Roda tatakan jumbo bag',       'standar' => 'berfungsi normal',              'db_field' => 'roda_tatakan_jumbobag'],
            ['no' => 9,  'kategori' => 'tombol panel & kabel',         'standar' => 'berfungsi normal & tidak putus', 'db_field' => 'tombol_panel_kabel'],
            ['no' => 10, 'kategori' => 'Mesin dan compresor',          'standar' => 'berfungsi normal',              'db_field' => 'mesin_compressor'],
            ['no' => 11, 'kategori' => 'Baut body mesin geprek',       'standar' => 'Tidak kendor',                  'db_field' => 'baut_body_mesin_geprek'],
            ['no' => 12, 'kategori' => 'Putaran tatakan jumbo bag',    'standar' => 'berfungsi normal',              'db_field' => 'putaran_tatakan_jumbobag'],
            ['no' => 13, 'kategori' => 'selang hydrolic & oli motor',  'standar' => 'Tidak bocor',                   'db_field' => 'selang_hidroplik_olimotor'],
            ['no' => 14, 'kategori' => 'Baut punch',                  'standar' => 'Tidak kendor',                  'db_field' => 'baut_punch'],
        ],
    ],
];

// ---------------------------------------------------------------------------
// Normalisasi $data
// ---------------------------------------------------------------------------
// PENTING (14 Sep 2026): kalau file ini dirender lewat alur aplikasi asli
// (Export -> PRINT/PDF/WORD), phpRAD TIDAK ngasih variable $data ke file ini.
// Data yang dikirim controller lewat render_view() nyangkutnya di
// $this->view_data. Kalau dipanggil lewat test_preview_geprek.php (include
// manual), yang ada malah $data. Dua-duanya di-handle di sini biar file ini
// tetap bisa dipakai bareng buat dua-duanya.
if (!isset($data) && isset($this) && isset($this->view_data)) {
    $data = $this->view_data;
}
$data       = (array) ($data ?? []);
$day_count  = (int) ($data['day_count'] ?? 31);
$checks     = $data['checks'] ?? [];
$pelaksana  = $data['pelaksana'] ?? [];
$na_days    = $data['na_days'] ?? [];
?>
<style>
    @media print {
        @page { size: landscape; margin: 8mm; }
    }
</style>
<?php if (isset($format) && $format === 'pdf'): ?>
<style>
    /* Update 17 Sep 2026 - khusus dompdf: margin @page di dompdf 0.8.3 diterapkan
       lewat elemen <html>, dan report_layout_geprek.php punya
       "html, body { margin:0 }" -> isi PDF nempel ke tepi kertas. PRINT tidak
       butuh ini (sudah pakai @page); kalau dipasang di PRINT margin jadi dobel. */
    html { margin: 6mm !important; }
</style>
<?php endif; ?>

<!--
    PENTING (14 Sep 2026): id="page-report-body" WAJIB ADA.
    phpRAD (system/BaseView.php -> parse_report_html()) nyari elemen dengan id
    ini buat diambil isinya. Kalau gak ketemu -> fungsinya return null -> yang
    ke-echo ke browser string kosong -> HALAMAN BLANK PUTIH. Ini akar masalah
    blank putih yang diburu dari sesi-sesi sebelumnya. Jangan dihapus/direname.
-->
<div id="page-report-body" style="font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #111;">

    <!-- Header: judul | Lokasi/Nama Mesin | Diperiksa Oleh -->
    <table cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse; border:1px solid #000;">
        <tr>
            <td colspan="2" align="center" style="border:1px solid #000; font-weight:bold; font-size:14px;">
                AUTONOMOUS MAINTENANCE STANDARD (PEMBERSIHAN, PELUMASAN, PENGECEKAN)
            </td>
            <td rowspan="2" valign="top" style="border:1px solid #000; width:220px; font-weight:bold;">
                Diperiksa Oleh:<br>
                <!-- NAMA UTUH (bukan inisial). checklist_display_name() ada di
                     _shared/checklist_helpers.php; bisa dimatikan lewat SAKLAR 7. -->
                <span style="font-weight:normal;"><?php echo geprek_e(checklist_display_name($data['diperiksa_oleh'] ?? '')); ?></span>
            </td>
        </tr>
        <tr>
            <td align="center" style="border:1px solid #000;"><strong>Lokasi:</strong><br><?php echo geprek_e($data['lokasi'] ?? '4M'); ?></td>
            <td align="center" style="border:1px solid #000;"><strong>Nama Mesin:</strong><br><?php echo geprek_e($data['nama_mesin'] ?? 'GEPREK (PRESS)'); ?></td>
        </tr>
    </table>

    <!-- Dua kolom: foto part (statis, hasil crop) | 3 tabel section -->
    <!-- Lebar kolom SENGAJA pakai PERSEN, bukan px. dompdf 0.8.3 (dipakai buat
         export PDF) gak ngehargain lebar px di dalam tabel bersarang - hasilnya
         kolom Kategori/Standar kegencet dan tabel kepotong. Pakai persen,
         browser sama dompdf sama-sama bener. -->
    <?php
    // Kolom foto cuma dipasang kalau 'photo_image' diisi.
    // CATATAN dompdf (14 Sep 2026): dompdf 0.8.3 SALAH naro gambar yang ada di
    // dalam sel tabel - gambarnya nempel di pojok kiri atas halaman, nimpa kop.
    // Udah dicoba macem-macem (width persen, width px, dibungkus div, valign
    // dilepas, table-layout dilepas) - hasilnya sama semua, itu bug rendering
    // dompdf-nya. Makanya khusus format PDF, controller ngirim 'photo_image'
    // kosong -> kolom foto dilewatin, tabelnya jadi full-width dan hasilnya rapi
    // 1 halaman. Buat PRINT (browser) & WORD, foto tetep ada.
    $geprek_show_photo = !empty($data['photo_image']);
    ?>
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <colgroup>
            <?php if ($geprek_show_photo): ?>
                <col style="width:18%;">
                <col style="width:82%;">
            <?php else: ?>
                <col style="width:100%;">
            <?php endif; ?>
        </colgroup>
        <tr>
            <?php if ($geprek_show_photo): ?>
                <!-- Update 17 Sep 2026: ukuran foto ABSOLUT (bukan 100%) + sel vertical-align:top
                     + tidak ada teks lain di sel ini = syarat dompdf 0.8.3 menaruh gambar dengan benar.
                     184x504 = rasio geprek_photo_panel.png (398x1090), muat di kolom 18%. -->
                <td valign="top" align="center" style="width:18%; border:1px solid #000; border-top:none; vertical-align:top; text-align:center; padding:0;"><img src="<?php echo geprek_e($data['photo_image']); ?>" width="184" height="504" style="width:184px; height:504px;"></td>
            <?php endif; ?>
            <td valign="top" style="padding:0;">
                <?php foreach ($GEPREK_SECTIONS as $section):
                    $items    = $section['items'];
                    $colCount = 3 + $day_count;
                ?>
                <table cellpadding="3" cellspacing="0" style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <colgroup>
                        <col style="width:3%;">
                        <col style="width:13%;">
                        <col style="width:12%;">
                        <?php for ($d = 1; $d <= $day_count; $d++): ?>
                            <col style="width:2.32%;">
                        <?php endfor; ?>
                    </colgroup>
                    <tr>
                        <td colspan="<?php echo $colCount; ?>" align="center" style="background:#000; color:#fff; font-weight:bold; font-size:12px; padding:4px; border:1px solid #000;">
                            <?php echo geprek_e($section['title']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" style="border:1px solid #000; font-weight:bold; background:#f2f2f2;"><?php echo geprek_e($section['area_label']); ?></td>
                        <td rowspan="2" align="center" style="border:1px solid #000; font-weight:bold; background:#f2f2f2; width:12%;"><?php echo geprek_e($section['standard_label']); ?></td>
                        <td colspan="<?php echo $day_count; ?>" align="center" style="border:1px solid #000; font-weight:bold; background:#f2f2f2;">Bulan dan Tahun : <?php echo geprek_e($data['bulan_tahun'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <td align="center" style="border:1px solid #000; width:3%; font-weight:bold; background:#f2f2f2;">No.</td>
                        <td align="center" style="border:1px solid #000; width:13%; font-weight:bold; background:#f2f2f2;">Kategori</td>
                        <?php for ($d = 1; $d <= $day_count; $d++): ?>
                            <td align="center" style="border:1px solid #000; width:2.32%; padding:2px 1px; font-size:8px; background:#f2f2f2;"><?php echo $d; ?></td>
                        <?php endfor; ?>
                    </tr>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td align="center" style="border:1px solid #000; width:3%;"><?php echo geprek_e($item['no']); ?></td>
                            <td style="border:1px solid #000; width:13%;"><?php echo geprek_e($item['kategori']); ?></td>
                            <td style="border:1px solid #000; width:12%;"><?php echo geprek_e($item['standar']); ?></td>
                            <?php
                            $itemChecks = $checks[$item['db_field']] ?? [];
                            for ($d = 1; $d <= $day_count; $d++) {
                                $val = $itemChecks[$d] ?? null;
                                if (($val === null || $val === '') && !empty($na_days[$d])) {
                                    $val = 'N/A'; // hari lewat tanpa record; baris Pelaksana dibiarkan kosong
                                }
                                echo geprek_check_cell($val);
                            }
                            ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="border:1px solid #000; font-weight:bold; background:#fafafa;">Pelaksana</td>
                        <?php for ($d = 1; $d <= $day_count; $d++): ?>
                            <td align="center" style="border:1px solid #000; width:2.32%; padding:2px 1px; font-size:7px; background:#fafafa;"><?php echo geprek_e(geprek_user_initial($pelaksana[$d] ?? '')); ?></td>
                        <?php endfor; ?>
                    </tr>
                </table>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>

</div>