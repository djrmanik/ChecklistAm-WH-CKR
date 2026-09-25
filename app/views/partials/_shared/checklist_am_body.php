<?php
/**
 * ============================================================================
 *  checklist_am_body.php  (v2 — table-based layout)
 *  Body renderer untuk report "Autonomous Maintenance Standard / Check Sheet
 *  Kerja" (Checklist AM Warehouse) — KCH / PT. Bintang Toedjoe.
 * ============================================================================
 *
 * KENAPA VERSI INI BEDA DARI SEBELUMNYA?
 * ---------------------------------------------------------------------------
 * Versi pertama pakai CSS flexbox buat nyusun kolom foto + tabel checklist.
 * Flexbox cuma jalan di browser & mesin PDF (wkhtmltopdf dkk). Word dan Excel
 * (baik dibuka langsung maupun lewat fitur export "WORD"/"EXCEL" bawaan
 * phpRAD) render HTML pakai engine yang jauh lebih terbatas — gak ngerti
 * flexbox, position:absolute, atau object-fit. Supaya tampilan Print, PDF,
 * WORD, dan EXCEL keluar SAMA PERSIS (sesuai requirement), seluruh layout di
 * bawah ini ditulis ulang pakai <table> HTML biasa + atribut width/height
 * langsung di tag <img>, karena itu satu-satunya pendekatan yang didukung
 * konsisten di keempat format tsb. Format CSV boleh beda karena CSV memang
 * cuma data mentah, bukan hasil render HTML.
 *
 * CARA PAKAI
 * ---------------------------------------------------------------------------
 * 1) Siapkan array $data sesuai KONTRAK DATA di bawah.
 * 2) Panggil dari controller kamu:
 *      $this->view->report_title       = "Checklist AM Mesin Geprek";
 *      $this->view->report_layout      = "report_layout.php";
 *      $this->view->report_paper_size  = "A4";
 *      $this->view->report_orientation = "landscape";
 *      $this->render_view("_shared/checklist_am_body.php", $data);
 *
 * KONTRAK DATA ($data) — SAMA seperti versi sebelumnya, tidak berubah:
 * ---------------------------------------------------------------------------
 * $data = [
 *   'masthead' => [
 *     'company_name' => 'PT. BINTANG TOEDJOE',
 *     'company_sub'  => 'MANUFACTURING INDONESIA',
 *     'program'      => 'Total Productive Maintenance',
 *     'site'         => 'Site Cikarang',
 *     'logo'         => 'assets/images/logo-bintangtoedjoe.png', // optional
 *     'report_name'  => 'AUTONOMOUS MAINTENANCE STANDARD',
 *     'report_sub'   => 'Check Sheet Kerja',
 *     'tagline'      => 'Saya Pakai Saya Rawat',
 *   ],
 *   'area'                 => 'Dumping Lt 4M',
 *   'mesin_line'           => 'KIR3P01DP001',
 *   'bulan_tahun'          => 'Agustus 2026',
 *   'diperiksa_oleh'       => '',
 *   'spv_fasilitator'      => '',
 *   'day_count'            => 31,
 *   'machine_images' => [
 *       ['no' => 1, 'src' => 'uploads/kir3p01/part1.jpg', 'label' => 'Iris Valve'],
 *   ],
 *   'sections' => [
 *       [
 *           'key' => 'cleaning', 'title' => 'STANDAR PEMBERSIHAN (CLEANING)', 'standard_label' => 'Pembersihan standar',
 *           'items' => [
 *               [
 *                   'no' => 1, 'nama_part' => 'Iris Valve', 'alat' => 'Vacum', 'metode' => 'Divacum/disedot',
 *                   'standar' => 'Tidak ada sisa material / kemasan', 'durasi' => '±5 menit',
 *                   'pelaksanaan' => 'Setiap pagi diawal shift',
 *                   'checks' => [1 => 'ok', 2 => null, 3 => 'nok'],
 *               ],
 *           ],
 *       ],
 *       [ 'key' => 'lubricating', 'title' => 'STANDAR PELUMASAN (LUBRICATING)', 'standard_label' => 'Pelumasan standar', 'items' => [] ],
 *       [ 'key' => 'inspection',  'title' => 'STANDAR PENGECEKAN (INSPECTION)',  'standard_label' => 'Pengecekan standar', 'items' => [] ],
 *   ],
 *   'doc_code'          => 'CR-PR-PR-1203.00 (25 Okt 2021)',
 *   'total_waktu_menit' => null,
 * ];
 *
 * CATATAN / ASUMSI (lihat riwayat obrolan sebelumnya untuk detail lengkap):
 * - Kolom Alat/Metode/Durasi/Pelaksanaan dikosongkan kalau gak ada isinya
 *   (kasus Geprek yang formatnya lebih lama).
 * - Label kolom "standar" gue benerin per section (bukan ikut typo asli
 *   yang selalu nulis "Pembersihan standar" di ketiga section).
 * - Total Waktu dihitung otomatis dari SUM 'durasi' — confirm ke supervisor
 *   kalau ternyata cara hitungnya beda.
 */

if (!function_exists('cam_e')) {
    function cam_e($value) {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cam_duration_to_minutes')) {
    function cam_duration_to_minutes($text) {
        if (!$text) return 0.0;
        if (preg_match('/(\d+(?:[.,]\d+)?)/', (string) $text, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
        return 0.0;
    }
}

if (!function_exists('cam_total_minutes')) {
    function cam_total_minutes(array $sections) {
        $total = 0.0;
        foreach ($sections as $section) {
            foreach (($section['items'] ?? []) as $item) {
                $total += cam_duration_to_minutes($item['durasi'] ?? null);
            }
        }
        return $total;
    }
}

if (!function_exists('cam_check_cell')) {
    function cam_check_cell($status) {
        switch ($status) {
            case 'ok':
                return '<td align="center" style="border:1px solid #333; width:16px; color:#0a7d1e; font-weight:bold; font-size:8px;" title="OK">&#10003;</td>';
            case 'nok':
                return '<td align="center" style="border:1px solid #333; width:16px; color:#c0392b; font-weight:bold; font-size:8px;" title="NOK">&#10007;</td>';
            default:
                return '<td align="center" style="border:1px solid #333; width:16px; font-size:8px;">&nbsp;</td>';
        }
    }
}

if (!function_exists('cam_day_header_cells')) {
    function cam_day_header_cells($day_count) {
        $html = '';
        for ($d = 1; $d <= $day_count; $d++) {
            $html .= '<th align="center" style="border:1px solid #333; background:#eee; width:16px; font-size:8px;">' . (int) $d . '</th>';
        }
        return $html;
    }
}

if (!function_exists('cam_photo_grid')) {
    /**
     * Grid foto bagian mesin, table-based (3 kolom per baris).
     * Nomor badge ditulis sebagai baris kecil DI ATAS foto (bukan overlay
     * position:absolute), supaya tetap kebaca walau dibuka di Word/Excel.
     */
    function cam_photo_grid(array $images) {
        if (empty($images)) {
            echo '<p style="font-size:10px; color:#888; font-style:italic;">Belum ada foto bagian mesin.</p>';
            return;
        }
        $perRow = 3;
        $rows = array_chunk($images, $perRow);
        echo '<table cellpadding="2" cellspacing="0" style="width:100%; border-collapse:collapse;">';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $img) {
                $no    = cam_e($img['no'] ?? '');
                $src   = cam_e($img['src'] ?? '');
                $label = cam_e($img['label'] ?? '');
                echo '<td align="center" valign="top" style="width:' . floor(100 / $perRow) . '%; padding:3px;">';
                echo '<table cellpadding="0" cellspacing="0" style="width:100%; border:1px solid #999;">';
                echo '<tr><td align="center" style="background:#eee; font-size:8px; font-weight:bold; border-bottom:1px solid #999;">' . $no . '</td></tr>';
                echo '<tr><td align="center" style="background:#f2f2f2;">';
                if ($src !== '') {
                    echo '<img src="' . $src . '" width="70" height="55" alt="' . $label . '" style="width:70px; height:55px;">';
                } else {
                    echo '<div style="width:70px; height:55px;">&nbsp;</div>';
                }
                echo '</td></tr>';
                echo '</table>';
                if ($label !== '') {
                    echo '<div style="font-size:8px; margin-top:2px;">' . $label . '</div>';
                }
                echo '</td>';
            }
            // isi sel kosong kalau baris terakhir gak penuh, biar border tabel rapi
            $missing = $perRow - count($row);
            for ($i = 0; $i < $missing; $i++) {
                echo '<td style="width:' . floor(100 / $perRow) . '%;">&nbsp;</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
}

if (!function_exists('cam_section_table')) {
    function cam_section_table(array $section, $day_count) {
        $title          = cam_e($section['title'] ?? '');
        $standard_label = cam_e($section['standard_label'] ?? 'Standar');
        $items          = $section['items'] ?? [];
        $colCount       = 7 + $day_count;
        ?>
        <table cellpadding="3" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:10px; table-layout:fixed;">
            <tr>
                <td colspan="<?php echo $colCount; ?>" align="center" style="background:#000; color:#fff; font-weight:bold; font-size:12px; padding:4px;">
                    <?php echo $title; ?>
                </td>
            </tr>
            <tr>
                <th style="border:1px solid #333; background:#eee; width:26px; font-size:9.5px;">No.</th>
                <th style="border:1px solid #333; background:#eee; width:130px; font-size:9.5px;">Nama Part</th>
                <th style="border:1px solid #333; background:#eee; width:70px; font-size:9.5px;">Alat</th>
                <th style="border:1px solid #333; background:#eee; width:70px; font-size:9.5px;">Metode</th>
                <th style="border:1px solid #333; background:#eee; width:120px; font-size:9.5px;"><?php echo $standard_label; ?></th>
                <th style="border:1px solid #333; background:#eee; width:45px; font-size:9.5px;">Durasi</th>
                <th style="border:1px solid #333; background:#eee; width:90px; font-size:9.5px;">Pelaksanaan</th>
                <?php echo cam_day_header_cells($day_count); ?>
            </tr>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="<?php echo $colCount; ?>" align="center" style="border:1px solid #333; color:#888; font-style:italic; font-size:9.5px;">
                        Belum ada item untuk section ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td align="center" style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['no'] ?? ''); ?></td>
                        <td style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['nama_part'] ?? ''); ?></td>
                        <td style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['alat'] ?? ''); ?></td>
                        <td style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['metode'] ?? ''); ?></td>
                        <td style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['standar'] ?? ''); ?></td>
                        <td align="center" style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['durasi'] ?? ''); ?></td>
                        <td style="border:1px solid #333; font-size:9.5px;"><?php echo cam_e($item['pelaksanaan'] ?? ''); ?></td>
                        <?php
                        $checks = $item['checks'] ?? [];
                        for ($d = 1; $d <= $day_count; $d++) {
                            echo cam_check_cell($checks[$d] ?? null);
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="7" style="border:1px solid #333; font-weight:bold; background:#fafafa; font-size:9.5px;">Paraf Pelaksana</td>
                <?php for ($d = 1; $d <= $day_count; $d++) { echo '<td style="border:1px solid #333; background:#fafafa;">&nbsp;</td>'; } ?>
            </tr>
            <tr>
                <td colspan="7" style="border:1px solid #333; font-weight:bold; background:#fafafa; font-size:9.5px;">Paraf Spv / Fasilitator</td>
                <?php for ($d = 1; $d <= $day_count; $d++) { echo '<td style="border:1px solid #333; background:#fafafa;">&nbsp;</td>'; } ?>
            </tr>
        </table>
        <?php
    }
}

// ---------------------------------------------------------------------------
// Normalisasi data
// ---------------------------------------------------------------------------
$data           = $data ?? [];
$masthead       = $data['masthead'] ?? [];
$day_count      = (int) ($data['day_count'] ?? 31);
$sections       = $data['sections'] ?? [];
$machine_images = $data['machine_images'] ?? [];
$total_minutes  = $data['total_waktu_menit'] ?? cam_total_minutes($sections);
?>
<style>
    /* @media print masih aman dipakai — browser & wkhtmltopdf saja yang baca ini,
       Word/Excel akan mengabaikannya begitu saja (tidak merusak apa pun). */
    @media print {
        @page { size: landscape; margin: 8mm; }
    }
</style>

<div style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #111;">

    <!-- Masthead: logo | info perusahaan | judul report | kotak tanda tangan -->
    <table cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; border:2px solid #000; margin-bottom:8px;">
        <tr>
            <td align="center" valign="middle" style="width:90px; border-right:1px solid #000;">
                <?php if (!empty($masthead['logo'])): ?>
                    <img src="<?php echo cam_e($masthead['logo']); ?>" width="70" height="60" alt="logo" style="width:70px; height:60px;">
                <?php endif; ?>
            </td>
            <td valign="middle" style="border-right:1px solid #000; font-size:11px; line-height:1.4;">
                <strong style="font-size:12px;"><?php echo cam_e($masthead['company_name'] ?? 'PT. BINTANG TOEDJOE'); ?></strong><br>
                <?php echo cam_e($masthead['company_sub'] ?? 'MANUFACTURING INDONESIA'); ?><br>
                <strong><?php echo cam_e($masthead['program'] ?? 'Total Productive Maintenance'); ?></strong><br>
                <?php echo cam_e($masthead['site'] ?? 'Site Cikarang'); ?>
            </td>
            <td align="center" valign="middle" style="border-right:1px solid #000;">
                <h1 style="font-size:15px; margin:0 0 2px;"><?php echo cam_e($masthead['report_name'] ?? 'AUTONOMOUS MAINTENANCE STANDARD'); ?></h1>
                <h2 style="font-size:12px; margin:0 0 6px; font-weight:normal;"><?php echo cam_e($masthead['report_sub'] ?? 'Check Sheet Kerja'); ?></h2>
                <div style="font-style:italic; font-size:10px;"><?php echo cam_e($masthead['tagline'] ?? 'Saya Pakai Saya Rawat'); ?></div>
            </td>
            <td valign="top" style="width:160px; font-size:10px; padding:0;">
                <table cellpadding="4" cellspacing="0" style="width:100%; border-collapse:collapse;">
                    <tr><td style="border-bottom:1px solid #000;"><strong>Diperiksa Oleh</strong><br><?php echo cam_e($data['diperiksa_oleh'] ?? ''); ?></td></tr>
                    <tr><td><strong>SPV/Fasilitator</strong><br><?php echo cam_e($data['spv_fasilitator'] ?? ''); ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Area / Mesin-Line / Bulan-Tahun -->
    <table cellpadding="4" cellspacing="0" style="width:100%; border-collapse:collapse; border:2px solid #000; border-top:none; margin-bottom:8px; font-size:11px;">
        <tr>
            <td style="border-right:1px solid #000;"><strong>Area:</strong> <?php echo cam_e($data['area'] ?? ''); ?></td>
            <td style="border-right:1px solid #000;"><strong>Mesin/Line:</strong> <?php echo cam_e($data['mesin_line'] ?? ''); ?></td>
            <td><strong>Bulan/Tahun:</strong> <?php echo cam_e($data['bulan_tahun'] ?? ''); ?></td>
        </tr>
    </table>

    <!-- Dua kolom utama: foto part (kiri) | tabel checklist (kanan) -->
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr>
            <td valign="top" style="width:230px; padding-right:10px;">
                <div style="font-weight:bold; text-align:center; margin-bottom:4px;">Gambar Bagian Mesin</div>
                <?php cam_photo_grid($machine_images); ?>
            </td>
            <td valign="top">
                <?php foreach ($sections as $section): ?>
                    <?php cam_section_table($section, $day_count); ?>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>

    <!-- Footer ringkasan: no dokumen (kiri) | total waktu (kanan) -->
    <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:8px; font-size:10px;">
        <tr>
            <td align="left"><?php if (!empty($data['doc_code'])): ?>No. Dok: <?php echo cam_e($data['doc_code']); ?><?php endif; ?></td>
            <td align="right">Total Waktu: <strong><?php echo cam_e(rtrim(rtrim(number_format($total_minutes, 1), '0'), '.')); ?> Menit</strong></td>
        </tr>
    </table>

</div>
