<?php
/**
 * ============================================================================
 *  GencBackupReader                    [GENC-13OKT26]  dibuat 13 Okt 2026
 *
 *  Membuat REPORT PDF resmi (sama dengan tombol Export PDF di halaman mesin) untuk
 *  file cadangan sebelum mesin / unit DIHAPUS PERMANEN dari menu Mesin & Unit.
 *
 *  Bukan route: ada di _base/ dan namanya tidak berakhiran "Controller" (sama dengan
 *  GencHubReader). Turunan GencChecklistBase supaya boleh memanggil helper PROTECTED
 *  milik controller mesin lain (genc_c, genc_units) - pola K28 / GencHubReader.
 *  Isi report dibuat dengan fungsi report yang sama (checklist_collect_report_data +
 *  checklist_build_report_html + checklist_pdf_bytes) -> tampilan identik.
 * ============================================================================
 */
require_once __DIR__ . '/GencChecklistBase.php';

class GencBackupReader extends GencChecklistBase{

	function __construct(){ parent::__construct(); $this->tablename = 'palletmover'; }

	/** Tidak dipakai (kelas ini tidak menampilkan halaman); wajib ada karena abstract. */
	protected function genc_conf(){ return array('page' => 'mesin', 'title' => 'Mesin & Unit'); }

	/**
	 * Report PDF 1 halaman mesin (+ 1 unit) untuk 1 bulan.
	 * @param string $page route mesin (bawaan atau am_*)
	 * @param string $slug slug unit ('' = halaman 1 unit)
	 * @return array|null array('bytes' => PDF, 'title' => judul report) atau null kalau halaman / unit tidak dikenal
	 */
	public function pdf($page, $slug, $bulan, $tahun){
		$ctl = genc_reg_controller($page);
		if(!($ctl instanceof GencChecklistBase)){ return null; }
		$c = $ctl->genc_c();
		$units = $ctl->genc_units();
		$db = $ctl->GetModel();
		if(empty($units)){
			$conf = checklist_load_machine($c['profile'], $c['overrides']);
			$data = checklist_collect_report_data($db, $conf, (int) $bulan, (int) $tahun, 'pdf');
			$title = $conf['title'];
		}
		else{
			if(!isset($units[$slug])){ return null; }
			$u = $units[$slug];
			$conf = checklist_load_machine(isset($u['profile']) ? $u['profile'] : $c['profile'], $c['overrides']);
			$data = checklist_collect_report_data($db, $conf, (int) $bulan, (int) $tahun, 'pdf', $u);
			$title = $conf['title'] . ' - ' . $u['label'];
		}
		$html = checklist_build_report_html($data, $title, 'pdf');
		return array('bytes' => checklist_pdf_bytes($html), 'title' => $title);
	}
}
