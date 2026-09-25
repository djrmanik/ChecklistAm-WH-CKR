<?php
/**
 * ============================================================================
 *  app/views/partials/_shared/checklist_report_body.php
 *
 *  BODY REPORT LAYOUT BULANAN - dipakai SEMUA mesin.
 *
 *  Ini generalisasi dari app/views/partials/_shared/dumping_report.php
 *  (22 Sep 2026). Tata letaknya PERSIS sama - kop, header, sub header, kotak
 *  "Diperiksa Oleh", Bulan/Tahun, kolom foto, 3 section, baris Paraf Pelaksana
 *  & Paraf Spv - karena layout Mesin Dumping dipakai sebagai standar untuk
 *  semua mesin ke depan. Yang beda antar mesin CUMA ISINYA, dan isinya datang
 *  dari file profil di app/views/partials/_shared/machines/<mesin>.php.
 *
 *  KONTRAK $data (disiapkan checklist_collect_report_data() di
 *  checklist_report_engine.php):
 *    area, nama_mesin, diperiksa_oleh, bulan_tahun, day_count
 *    sections      daftar section + item (dari profil mesin)
 *    pelaksanaan   teks kolom "Pelaksanaan" default (dipakai kalau item tidak
 *                  punya 'pelaksanaan' sendiri)
 *    images        array('logo'=>src, 'atas'=>src, 'bawah'=>src)  '' = dilewati
 *    img_size      array('logo'=>array(w,h), 'atas'=>..., 'bawah'=>...)
 *    checks        [kolom_db][tanggal] => nilai mentah DB
 *    pelaksana     [tanggal] => username MENTAH
 *    approver      [tanggal] => username MENTAH
 *    na_days       [tanggal] => true (hari lewat tanpa record -> ditulis N/A)
 *
 *  ATURAN YANG DIPEGANG (jangan diubah tanpa tes ulang PDF):
 *   - isi output ASCII-only: tidak ada &nbsp; atau karakter non-ASCII langsung.
 *     Tanda plus-minus ditulis sebagai entity angka &#177;
 *   - lebar kolom pakai PERSEN (dompdf 0.8.3 tidak menghargai px di tabel bersarang)
 *   - sel tanggal padding:2px 1px
 *   - GAMBAR (logo & foto) pakai atribut width/height ABSOLUT, sel-nya
 *     vertical-align:top/middle, dan TIDAK boleh ada teks lain di sel yang sama.
 *     Hasil eksperimen 16 Sep 2026 dengan dompdf 0.8.3: gambar width:100% di
 *     sel tabel tergeser keluar sel (atau hilang), dan teks yang satu sel
 *     dengan gambar ketimpa.
 *   - id="page-report-body" WAJIB ada (phpRAD mencarinya; tidak ketemu ->
 *     halaman blank putih tanpa pesan error).
 * ============================================================================
 */

require_once __DIR__ . '/checklist_helpers.php';

if (!function_exists('checklist_day_cell')) {
    /**
     * Satu sel tanggal. Aturan warna:
     * mengandung "NOK" -> merah, mengandung "OK" -> hijau, selain itu hitam.
     * (Nilai "PR" = Perawatan/Perbaikan otomatis masuk kategori hitam.)
     */
    function checklist_day_cell($raw, $width_pct, $bg = '') {
        $raw   = trim((string) ($raw === null ? '' : $raw));
        $style = 'border:1px solid #000; width:' . $width_pct . '%; padding:2px 1px; text-align:center; overflow:hidden;';
        if ($bg !== '') {
            $style .= ' background:' . $bg . ';';
        }
        if ($raw === '') {
            return '<td style="' . $style . '"></td>';
        }
        if ($raw === 'N/A') {
            return '<td align="center" style="' . $style . ' font-size:6px; font-weight:bold; color:#8a8a8a;">N/A</td>';
        }
        $upper = strtoupper($raw);
        if (strpos($upper, 'NOK') !== false) {
            $color = '#c0392b';
        } elseif (strpos($upper, 'OK') !== false) {
            $color = '#0a7d1e';
        } else {
            $color = '#111';
        }
        return '<td align="center" style="' . $style . ' font-size:6px; font-weight:bold; color:' . $color . ';">' . checklist_e($raw) . '</td>';
    }
}

if (!function_exists('checklist_initial_cell')) {
    function checklist_initial_cell($text, $width_pct) {
        return '<td align="center" style="border:1px solid #000; width:' . $width_pct . '%; padding:2px 1px; text-align:center; overflow:hidden; font-size:6px; font-weight:bold;">' . checklist_e($text) . '</td>';
    }
}

// ---------------------------------------------------------------------------
// Normalisasi $data (dukung $data dari include manual maupun $this->view_data)
// ---------------------------------------------------------------------------
if (!isset($data) && isset($this) && isset($this->view_data)) {
    $data = $this->view_data;
}
$data      = (array) (isset($data) ? $data : array());
$day_count = (int) (isset($data['day_count']) ? $data['day_count'] : 31);
$checks    = isset($data['checks'])    ? $data['checks']    : array();
$pelaksana = isset($data['pelaksana']) ? $data['pelaksana'] : array();
$approver  = isset($data['approver'])  ? $data['approver']  : array();
$na_days   = isset($data['na_days'])   ? $data['na_days']   : array();
$SECTIONS  = isset($data['sections'])  ? $data['sections']  : array();
$PELAKSANAAN_DEFAULT = isset($data['pelaksanaan']) ? $data['pelaksanaan'] : '';
$IMAGES    = isset($data['images'])    ? $data['images']    : array();
$IMG_SIZE  = isset($data['img_size'])  ? $data['img_size']  : array();

$PM = '&#177;'; // plus-minus, entity angka (ASCII-only)

// Lebar kolom (PERSEN dari tabel section). Total kolom teks 45.5%.
// Metode sengaja 8% karena "Divacum/disedot" tidak punya titik putus kata.
// Sisanya dibagi rata ke jumlah hari bulan itu (28-31) supaya tabel selalu penuh.
$W_NO = 1.9; $W_PART = 9.6; $W_ALAT = 6.8; $W_METODE = 8.0; $W_STD = 8.2; $W_DUR = 4.6; $W_PLK = 6.4;

// 23 Sep 2026 - profil mesin BOLEH (opsional) menimpa lebar kolom teks &
// ukuran huruf sel item lewat 'layout' => array('widths'=>..., 'item_font_px'=>...).
// Dipakai forklift: teks Metode/Standard dari halaman input panjang-panjang,
// dengan lebar default report jadi 3 halaman. Mesin yang tidak mengisi
// 'layout' (geprek, dumping, AGV) TIDAK berubah sama sekali.
$LAYOUT = (isset($data['layout']) && is_array($data['layout'])) ? $data['layout'] : array();
if (!empty($LAYOUT['widths']) && is_array($LAYOUT['widths']) && count($LAYOUT['widths']) === 7) {
    list($W_NO, $W_PART, $W_ALAT, $W_METODE, $W_STD, $W_DUR, $W_PLK) = array_map('floatval', array_values($LAYOUT['widths']));
}
$ITEM_FONT = !empty($LAYOUT['item_font_px']) ? (float) $LAYOUT['item_font_px'] : 0; // 0 = ikut default (8px)

$W_TEXT  = $W_NO + $W_PART + $W_ALAT + $W_METODE + $W_STD + $W_DUR + $W_PLK;
$W_DAY   = round((100 - $W_TEXT) / max($day_count, 1), 3);
$colSpan = 7 + $day_count;

// Ukuran gambar ABSOLUT (px). Lebar area cetak A4 landscape margin 6mm = +-1077px.
// Kolom foto 15% = +-161px, kolom logo 17% = +-183px.
$sz = function ($key, $w, $h) use ($IMG_SIZE) {
    if (isset($IMG_SIZE[$key]) && is_array($IMG_SIZE[$key]) && count($IMG_SIZE[$key]) >= 2) {
        return array('w' => (int) $IMG_SIZE[$key][0], 'h' => (int) $IMG_SIZE[$key][1]);
    }
    return array('w' => $w, 'h' => $h);
};
$IMG_LOGO  = $sz('logo',  138, 40);
$IMG_ATAS  = $sz('atas',  152, 135);
$IMG_BAWAH = $sz('bawah', 152, 169);

$B    = 'border:1px solid #000;';
$CELL = $B . ' padding:2px 3px; vertical-align:middle;';
if ($ITEM_FONT > 0) {
    // Cuma dipakai sel item & baris kosong (bukan header, bukan kop).
    $CELL = $B . ' padding:1px 2px; vertical-align:middle; font-size:' . $ITEM_FONT . 'px; line-height:1.15;';
}
$HEAD = $B . ' padding:2px 3px; font-weight:bold; vertical-align:middle;';

// File ini bisa di-include BERKALI-KALI dalam satu dokumen (report multi
// halaman lewat checklist_report_multi.php). Blok <style> dan
// id="page-report-body" cuma boleh muncul SEKALI: id kembar itu HTML tidak
// sah, dan phpRAD mencari id itu (kalau tidak ketemu -> halaman blank putih,
// bug lama yang sudah pernah kejadian).
$GLOBALS['checklist_body_ke'] = isset($GLOBALS['checklist_body_ke']) ? $GLOBALS['checklist_body_ke'] + 1 : 1;
$IS_HALAMAN_PERTAMA = ($GLOBALS['checklist_body_ke'] === 1);
?>
<?php if ($IS_HALAMAN_PERTAMA): ?>
<style>
    /* Margin halaman. SENGAJA tanpa "size:" - dompdf 0.8.3 membuang seluruh
       aturan @page kalau ada "size: A4 landscape" (ukuran kertas sudah diatur
       lewat setPaper() di engine / dialog print browser). */
    @page { margin: 6mm; }
</style>
<?php if (isset($checklist_format) && $checklist_format === 'pdf'): ?>
<style>
    /* Khusus dompdf: margin @page di dompdf 0.8.3 diterapkan lewat elemen <html>,
       dan report_layout_geprek.php punya "html, body { margin:0 }" yang menimpanya
       -> isi PDF nempel ke tepi kertas. Browser (PRINT) tidak butuh ini karena
       sudah pakai @page; kalau dipasang juga di PRINT, margin jadi dobel. */
    html { margin: 6mm !important; }
</style>
<?php endif; ?>
<?php endif; /* $IS_HALAMAN_PERTAMA */ ?>
<div<?php echo $IS_HALAMAN_PERTAMA ? ' id="page-report-body"' : ''; ?> style="font-family: Arial, Helvetica, sans-serif; font-size:8px; color:#111; line-height:1.2;">

    <!-- ================= KOP ================= -->
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <colgroup>
            <col style="width:17%;">
            <col style="width:9%;">
            <col style="width:27%;">
            <col style="width:32%;">
            <col style="width:6%;">
            <col style="width:9%;">
        </colgroup>
        <tr>
            <!-- Logo sendirian di selnya, ukuran absolut (lihat catatan dompdf di atas) -->
            <td rowspan="2" align="center" style="<?php echo $B; ?> width:17%; padding:2px; vertical-align:middle; text-align:center;"><?php if (!empty($IMAGES['logo'])): ?><img src="<?php echo checklist_e($IMAGES['logo']); ?>" width="<?php echo $IMG_LOGO['w']; ?>" height="<?php echo $IMG_LOGO['h']; ?>"><?php endif; ?></td>
            <td rowspan="2" colspan="2" align="center" style="<?php echo $B; ?> width:36%; padding:3px; vertical-align:middle; text-align:center; font-size:9px; line-height:1.35;">
                PT. BINTANG TOEDJOE<br>
                MANUFACTURING INDONESIA<br>
                <strong>Total Productive Maintenance</strong><br>
                <strong>Site Cikarang</strong>
            </td>
            <td rowspan="2" align="center" style="<?php echo $B; ?> width:32%; padding:3px; vertical-align:middle; text-align:center; font-weight:bold; font-size:12px; line-height:1.35;">
                AUTONOMOUS MAINTENANCE STANDARD<br>
                Check Sheet Kerja
            </td>
            <td colspan="2" align="center" style="<?php echo $HEAD; ?> width:15%; text-align:center; font-size:9px; height:14px;">Diperiksa Oleh</td>
        </tr>
        <tr>
            <!-- NAMA UTUH, bukan inisial. Inisial cuma dipakai di baris paraf
                 harian di bawah. Lihat checklist_display_name() & SAKLAR 7. -->
            <td colspan="2" align="center" style="<?php echo $CELL; ?> width:15%; text-align:center; font-size:9px; height:36px;"><?php echo checklist_e(checklist_display_name(isset($data['diperiksa_oleh']) ? $data['diperiksa_oleh'] : '')); ?></td>
        </tr>
        <tr>
            <td style="<?php echo $HEAD; ?> width:17%; font-size:9px;">Area: <?php echo checklist_e(isset($data['area']) ? $data['area'] : ''); ?></td>
            <td style="<?php echo $HEAD; ?> width:9%; font-size:9px;">Mesin/Line</td>
            <td align="center" style="<?php echo $HEAD; ?> width:27%; text-align:center; font-size:9px;"><?php echo checklist_e(isset($data['nama_mesin']) ? $data['nama_mesin'] : ''); ?></td>
            <td align="center" style="<?php echo $HEAD; ?> width:32%; text-align:center; font-size:9px; font-style:italic;">Saya Pakai Saya Rawat</td>
            <td align="center" style="<?php echo $HEAD; ?> width:6%; text-align:center; font-size:8px;">Bulan/Tahun</td>
            <td align="center" style="<?php echo $CELL; ?> width:9%; text-align:center; font-size:9px;"><?php echo checklist_e(isset($data['bulan_tahun']) ? $data['bulan_tahun'] : ''); ?></td>
        </tr>
    </table>

    <!-- ================= BODY: kolom foto | section ================= -->
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <colgroup>
            <col style="width:15%;">
            <col style="width:85%;">
        </colgroup>
        <?php foreach ($SECTIONS as $section):
            $photo_key   = isset($section['photo']) ? $section['photo'] : '';
            $photo       = ($photo_key !== '' && !empty($IMAGES[$photo_key])) ? $IMAGES[$photo_key] : '';
            // Ukuran foto dicari per key dulu (23 Sep 2026: geprek punya 3 foto
            // 'atas' / 'tengah' / 'bawah', satu per section). Key yang tidak
            // terdaftar di img_size jatuh ke perilaku lama: 'atas' -> ukuran
            // atas, selain itu -> ukuran bawah. Mesin lama tidak berubah.
            $size        = ($photo_key === 'atas') ? $IMG_ATAS : $sz($photo_key, $IMG_BAWAH['w'], $IMG_BAWAH['h']);
            $photo_label = isset($section['photo_label']) ? $section['photo_label'] : '';
            $blank_rows  = isset($section['blank_rows']) ? (int) $section['blank_rows'] : 0;
            $items       = isset($section['items']) ? $section['items'] : array();
        ?>
        <tr>
            <td style="<?php echo $B; ?> border-top:none; width:15%; padding:0; vertical-align:top;">
                <!-- Label & foto dipisah ke baris tabel sendiri-sendiri (syarat dompdf) -->
                <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <?php if ($photo_label !== ''): ?>
                    <tr><td align="center" style="text-align:center; font-weight:bold; font-size:8.5px; padding:3px 2px 2px 2px;"><?php echo checklist_e($photo_label); ?></td></tr>
                    <?php endif; ?>
                    <?php if ($photo !== ''): ?>
                    <tr><td align="center" style="width:100%; text-align:center; vertical-align:top; padding:2px 0;"><img src="<?php echo checklist_e($photo); ?>" width="<?php echo $size['w']; ?>" height="<?php echo $size['h']; ?>" style="width:<?php echo $size['w']; ?>px; height:<?php echo $size['h']; ?>px;"></td></tr>
                    <?php endif; ?>
                </table>
            </td>
            <td style="width:85%; padding:0; vertical-align:top;">
                <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <colgroup>
                        <col style="width:<?php echo $W_NO; ?>%;">
                        <col style="width:<?php echo $W_PART; ?>%;">
                        <col style="width:<?php echo $W_ALAT; ?>%;">
                        <col style="width:<?php echo $W_METODE; ?>%;">
                        <col style="width:<?php echo $W_STD; ?>%;">
                        <col style="width:<?php echo $W_DUR; ?>%;">
                        <col style="width:<?php echo $W_PLK; ?>%;">
                        <?php for ($d = 1; $d <= $day_count; $d++): ?>
                            <col style="width:<?php echo $W_DAY; ?>%;">
                        <?php endfor; ?>
                    </colgroup>
                    <tr>
                        <td colspan="<?php echo $colSpan; ?>" align="center" style="background:#000; color:#fff; font-weight:bold; font-size:10.5px; padding:2px; border:1px solid #000; text-align:center;">
                            <?php echo checklist_e(isset($section['title']) ? $section['title'] : ''); ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="<?php echo $HEAD; ?> width:<?php echo $W_NO; ?>%; text-align:center; padding:2px 1px;">No.</td>
                        <td style="<?php echo $HEAD; ?> width:<?php echo $W_PART; ?>%;">Nama Part</td>
                        <td style="<?php echo $HEAD; ?> width:<?php echo $W_ALAT; ?>%;">Alat</td>
                        <td style="<?php echo $HEAD; ?> width:<?php echo $W_METODE; ?>%;">Metode</td>
                        <td style="<?php echo $HEAD; ?> width:<?php echo $W_STD; ?>%;"><?php echo checklist_e(isset($section['standard_label']) ? $section['standard_label'] : ''); ?></td>
                        <td align="center" style="<?php echo $HEAD; ?> width:<?php echo $W_DUR; ?>%; text-align:center;">Durasi</td>
                        <td style="<?php echo $HEAD; ?> width:<?php echo $W_PLK; ?>%;">Pelaksanaan</td>
                        <?php for ($d = 1; $d <= $day_count; $d++): ?>
                            <td align="center" style="<?php echo $B; ?> width:<?php echo $W_DAY; ?>%; padding:2px 1px; text-align:center; font-weight:bold; font-size:6.5px;"><?php echo $d; ?></td>
                        <?php endfor; ?>
                    </tr>

                    <?php foreach ($items as $item):
                        // Kolom statis yang belum diisi (menunggu template UI dari SPV)
                        // sengaja dibiarkan string kosong -> sel-nya kosong, siap ditulis
                        // tangan / diisi belakangan, bukan diisi tebakan.
                        $i_alat   = isset($item['alat'])    ? $item['alat']    : '';
                        $i_metode = isset($item['metode'])  ? $item['metode']  : '';
                        $i_std    = isset($item['standar']) ? $item['standar'] : '';
                        $i_durasi = isset($item['durasi'])  ? trim((string) $item['durasi']) : '';
                        $i_plk    = isset($item['pelaksanaan']) ? $item['pelaksanaan'] : $PELAKSANAAN_DEFAULT;
                    ?>
                    <tr>
                        <td align="center" style="<?php echo $CELL; ?> width:<?php echo $W_NO; ?>%; text-align:center; padding:2px 1px;"><?php echo checklist_e($item['no']); ?></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_PART; ?>%;"><?php echo checklist_e($item['part']); ?></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_ALAT; ?>%;"><?php echo checklist_e($i_alat); ?></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_METODE; ?>%;"><?php echo checklist_e($i_metode); ?></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_STD; ?>%;"><?php echo checklist_e($i_std); ?></td>
                        <td align="center" style="<?php echo $CELL; ?> width:<?php echo $W_DUR; ?>%; text-align:center; font-weight:bold; padding:2px 1px;"><?php echo ($i_durasi === '' ? '' : $PM . checklist_e($i_durasi)); ?></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_PLK; ?>%; font-weight:bold;"><?php echo checklist_e($i_plk); ?></td>
                        <?php
                        $itemChecks = isset($checks[$item['db']]) ? $checks[$item['db']] : array();
                        for ($d = 1; $d <= $day_count; $d++) {
                            $val = isset($itemChecks[$d]) ? $itemChecks[$d] : null;
                            if (($val === null || $val === '') && !empty($na_days[$d])) {
                                $val = 'N/A'; // hari lewat tanpa record; paraf dibiarkan kosong
                            }
                            echo checklist_day_cell($val, $W_DAY);
                        }
                        ?>
                    </tr>
                    <?php endforeach; ?>

                    <?php for ($r = 0; $r < $blank_rows; $r++): ?>
                    <tr>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_NO; ?>%; height:14px;"></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_PART; ?>%;"></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_ALAT; ?>%;"></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_METODE; ?>%;"></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_STD; ?>%;"></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_DUR; ?>%;"></td>
                        <td style="<?php echo $CELL; ?> width:<?php echo $W_PLK; ?>%;"></td>
                        <?php for ($d = 1; $d <= $day_count; $d++) { echo checklist_day_cell(null, $W_DAY); } ?>
                    </tr>
                    <?php endfor; ?>

                    <tr>
                        <td colspan="7" align="center" style="<?php echo $HEAD; ?> text-align:center;">Paraf Pelaksana</td>
                        <?php for ($d = 1; $d <= $day_count; $d++) {
                            // Section tanpa item (mis. Lubricating) -> paraf dibiarkan kosong
                            $txt = empty($items) ? '' : checklist_user_initial(isset($pelaksana[$d]) ? $pelaksana[$d] : '');
                            echo checklist_initial_cell($txt, $W_DAY);
                        } ?>
                    </tr>
                    <tr>
                        <td colspan="7" align="center" style="<?php echo $HEAD; ?> text-align:center;">Paraf Spv / Fasilitator</td>
                        <?php for ($d = 1; $d <= $day_count; $d++) {
                            $txt = empty($items) ? '' : checklist_approver_initial(isset($approver[$d]) ? $approver[$d] : '');
                            echo checklist_initial_cell($txt, $W_DAY);
                        } ?>
                    </tr>
                </table>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>
