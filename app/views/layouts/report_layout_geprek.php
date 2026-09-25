<?php
/**
 * ============================================================================
 *  app/views/layouts/report_layout_geprek.php
 *  Pembungkus HTML buat report Checklist AM (Mesin Geprek dulu).
 * ============================================================================
 *
 * PENTING - BEDA SAMA VERSI SEBELUMNYA:
 * ---------------------------------------------------------------------------
 * File ini SEKARANG di-include LANGSUNG dari controller
 * (Mesin_geprekController::geprek_build_report_html()), BUKAN lewat mekanisme
 * $this->view->report_layout milik phpRAD.
 *
 * Alasannya: jalur bawaan phpRAD (system/BaseView.php -> parse_report_html())
 * rapuh dan GAGALNYA DIAM-DIAM - kalau ada satu hal kecil yang gak pas, dia
 * return null, yang ke-echo string kosong = HALAMAN BLANK PUTIH tanpa pesan
 * error sama sekali. Udah kebuang banyak waktu ngejar itu. Dengan di-include
 * langsung, semua yang ke-render 100% di bawah kendali kita dan kalau ada yang
 * salah, errornya KELIATAN.
 *
 * File ini gak butuh $this sama sekali. Variable yang dipakai dikirim
 * controller lewat scope include:
 *   $geprek_report_title  string  judul di <title>
 *   $geprek_paper_size    string  'A4'
 *   $geprek_orientation   string  'landscape'
 *   $geprek_force_print   bool    true = browser langsung buka dialog print
 *   $geprek_view_file     string  path absolut ke report.php
 *   $data                 array   data report (dipakai di dalam report.php)
 */

$geprek_report_title = isset($geprek_report_title) ? $geprek_report_title : 'Report';
$geprek_paper_size   = isset($geprek_paper_size) ? $geprek_paper_size : 'A4';
$geprek_orientation  = isset($geprek_orientation) ? $geprek_orientation : 'landscape';
$geprek_force_print  = isset($geprek_force_print) ? (bool) $geprek_force_print : false;
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title><?php echo htmlspecialchars($geprek_report_title, ENT_QUOTES, 'UTF-8'); ?></title>
	<style>
		@page {
			size: <?php echo $geprek_paper_size . ' ' . $geprek_orientation; ?>;
			margin: 6mm;
		}

		html,
		body {
			margin: 0;
			padding: 0;
			font-family: Arial, Helvetica, sans-serif;
			font-size: 10px;
			color: #111;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}

		table {
			border-collapse: collapse;
		}

		img {
			max-width: 100%;
		}

		#report-body {
			padding: 0;
			margin: 0;
		}
	</style>
</head>

<body>
	<div id="report-body">
		<?php
		if (!empty($geprek_view_file) && is_file($geprek_view_file)) {
			include $geprek_view_file;
		} else {
			// Sengaja ditampilin terang-terangan, JANGAN dibikin diam-diam:
			// halaman blank putih tanpa pesan itu yang bikin bug kemarin susah dikejar.
			echo '<p style="font-family:monospace;color:#c0392b;">'
				. 'FILE REPORT GAK KETEMU: '
				. htmlspecialchars((string) (isset($geprek_view_file) ? $geprek_view_file : '(kosong)'), ENT_QUOTES, 'UTF-8')
				. '</p>';
		}
		?>
	</div>

	<?php if ($geprek_force_print) { ?>
		<script>
			window.print();
		</script>
	<?php } ?>
</body>

</html>
