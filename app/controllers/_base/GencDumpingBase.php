<?php
/**
 * ============================================================================
 *  GencDumpingBase                    [GENC-24SEP26-DUMPING]  dibuat 24 Sep 2026
 *
 *  Bagian yang SAMA untuk 3 mesin dumping (KIR3P01DP001, KIR6P01DP001,
 *  KIR7P01DP001). Controller tiap mesin cukup menyebut kodenya:
 *
 *      class Kir3p01dp001Controller extends GencDumpingBase{
 *          function __construct(){ parent::__construct(); $this->tablename = "kir3p01dp001"; }
 *          protected function dumping_code(){ return 'KIR3'; }
 *      }
 *
 *  Keputusan (KEPUTUSAN_GenerasiC.md):
 *   K12  1 menu "Mesin Dumping" + tab KIR3 / KIR6 / KIR7. Tabel & route tetap 3.
 *   K6   isian teks bebas lama dibaca lewat peta nilai di bawah - DB tidak diubah.
 *   K14  pengubah -> `user_perubah` (kolom lama), tanggal ubah -> `date_update`.
 *   K13  tanpa trigger auto-approve: semua approve manual.
 *
 *  File ini di subfolder _base/ dan namanya tidak berakhiran "Controller" ->
 *  tidak bisa jadi route. Semua helper protected.
 * ============================================================================
 */
require_once __DIR__ . '/GencChecklistBase.php';

abstract class GencDumpingBase extends GencChecklistBase{

	/** 'KIR3' / 'KIR6' / 'KIR7' */
	abstract protected function dumping_code();

	/** Tab di atas halaman, urut. 'page' = route & tabel. */
	protected function dumping_tabs(){
		return array(
			array('label' => 'KIR3', 'sub' => 'KIR3P01DP001', 'page' => 'kir3p01dp001'),
			array('label' => 'KIR6', 'sub' => 'KIR6P01DP001', 'page' => 'kir6p01dp001'),
			array('label' => 'KIR7', 'sub' => 'KIR7P01DP001', 'page' => 'kir7p01dp001'),
		);
	}

	/**
	 * K6 - peta isian teks bebas lama -> OK / NOK / PR.
	 * [GENC-24SEP26-AGV] isinya dipindah APA ADANYA ke GencChecklistBase::genc_legacy_values_gen_b()
	 * supaya dipakai juga AGV (Gen B). Ubah daftarnya di sana.
	 */
	protected function dumping_legacy_values(){
		return $this->genc_legacy_values_gen_b();
	}

	protected function genc_conf(){
		$code = $this->dumping_code();                     // KIR3
		$machine = $code . 'P01DP001';                      // KIR3P01DP001
		return array(
			'page'              => $this->tablename,
			'profile'           => 'dumping',
			// SAMA PERSIS dengan dumping_render_report() lama -> report tidak berubah
			'overrides'         => array(
				'table'        => $this->tablename,
				'machine_code' => $machine,
				'title'        => 'Checklist AM Mesin Dumping ' . $machine,
				'file_slug'    => $machine,
			),
			'group_title'       => 'Mesin Dumping',
			'title'             => 'Mesin Dumping ' . $code,
			'name_lower'        => 'mesin dumping ' . $code,
			'area'              => 'Dumping Lt 4M',
			'file_slug'         => 'Mesin-Dumping-' . $machine,
			'user_field'        => 'user_create',            // Gen B: tanpa "d"
			'update_user_field' => 'user_perubah',           // K14: kolom lama
			'edit_log'          => array('date' => 'date_perubahan', 'note' => 'perubahan'),
			'tabs'              => $this->dumping_tabs(),
			'legacy_values'     => $this->dumping_legacy_values(),
			// Form lama menyuruh operator MENGETIK kolom Approval -> isi yang artinya "belum"
			'approval_pending_values' => $this->genc_approval_pending_gen_b(),   // [GENC-24SEP26-AGV] daftar dipindah ke cetakan
			'keterangan_contoh' => 'Contoh: iris valve sobek sedikit, sudah lapor ke Engineering (Pak ...) &mdash; atau: sisa material di hopper, sudah dibersihkan.',
		);
	}

	// =====================================================================
	//  ROUTE LAMA (Generasi B) - K3
	// =====================================================================

	/** Edit sebaris (inline) daftar lama - tidak dipakai lagi: ubah lewat form supaya kondisi dihitung ulang. */
	function editfield($rec_id = null, $formdata = null){
		render_error("Ubah data lewat halaman Ubah checklist.");
		return null;
	}
}
