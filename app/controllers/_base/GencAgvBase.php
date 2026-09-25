<?php
/**
 * ============================================================================
 *  GencAgvBase                             [GENC-24SEP26-AGV]  dibuat 24 Sep 2026
 *
 *  Bagian yang SAMA untuk 2 halaman AGV:
 *      agv_table_top_lift  (5 unit: AGV TTL 6 - 10)   -> Agv_table_top_liftController
 *      counterbalance      (3 unit: AGV CB 1 - 3)     -> CounterbalanceController
 *
 *  Tampil sebagai SATU menu "AGV" dengan 8 tab unit (K7 + K12):
 *      Table Top Lift:  TTL 6 · TTL 7 · TTL 8 · TTL 9 · TTL 10
 *      Counterbalance:  CB 1 · CB 2 · CB 3
 *  Tiap tab = route tabelnya sendiri + ?unit=<slug>. Tabel DB TIDAK digabung.
 *  Daftar unit & alias (AGV 7 = AGV TTL 7) dibaca dari profil report
 *  (machines/agv_table_top_lift.php, machines/counterbalance.php) - satu
 *  sumber dengan report, jadi tab & report tidak mungkin beda pendapat.
 *
 *  Keputusan (KEPUTUSAN_GenerasiC.md):
 *   K6   isian teks bebas lama dibaca lewat peta nilai bersama Gen B (sama dengan dumping)
 *   K13  tanpa trigger auto-approve: semua approve manual
 *   K14  pengubah -> `user_perubah` (kolom lama), tanggal ubah -> `date_update`
 *   K15  kolom approval lama yang dulu diketik operator
 *   K17  `perubahan` & `date_perubahan` diisi otomatis saat record diubah
 *   K19  foto part belum ada -> kotak "Belum ada foto" yang bisa diklik (penjelasan)
 *
 *  File ini di subfolder _base/ dan namanya tidak berakhiran "Controller" ->
 *  tidak bisa jadi route. Semua helper protected.
 * ============================================================================
 */
require_once __DIR__ . '/GencChecklistBase.php';

abstract class GencAgvBase extends GencChecklistBase{

	/** Isi khusus halaman: array('profile', 'title', 'short', 'area', 'contoh'). */
	abstract protected function agv_conf();

	/** Dua halaman AGV, urut tampil. 'short' = awalan label tab. */
	protected function agv_pages(){
		return array(
			array('page' => 'agv_table_top_lift', 'group' => 'Table Top Lift', 'short' => 'TTL'),
			array('page' => 'counterbalance',     'group' => 'Counterbalance', 'short' => 'CB'),
		);
	}

	/**
	 * Tab 8 unit dari daftar unit di profil report. Label tab dipendekkan
	 * ("AGV TTL 7" -> "TTL 7") karena judul halaman sudah "AGV".
	 */
	protected function agv_tabs(){
		$tabs = array();
		foreach($this->agv_pages() as $pg){
			$units = checklist_machine_units(checklist_load_machine($pg['page']));
			foreach($units as $slug => $u){
				$tabs[] = array(
					'label' => trim(preg_replace('/^AGV\s+/i', '', $u['label'])),
					'sub'   => '',
					'page'  => $pg['page'],
					'unit'  => $slug,
					'group' => $pg['group'],
				);
			}
		}
		return $tabs;
	}

	protected function genc_conf(){
		$a = $this->agv_conf();
		return array(
			'page'              => $this->tablename,
			'profile'           => $a['profile'],        // report: profil & unit SAMA dengan sebelum Gen C
			'overrides'         => array(),
			'group_title'       => 'AGV',
			'title'             => $a['title'],          // diganti label unit aktif di tampilan (multi-unit)
			'machine_title'     => $a['title'],
			'name_lower'        => $a['title'],
			'area'              => $a['area'],
			'file_slug'         => $a['file_slug'],
			'user_field'        => 'user_created',        // AGV pakai "d" (beda dengan dumping)
			'update_user_field' => 'user_perubah',        // K14: kolom lama
			'edit_log'          => array('date' => 'date_perubahan', 'note' => 'perubahan'),
			'tabs'              => $this->agv_tabs(),
			'legacy_values'     => $this->genc_legacy_values_gen_b(),
			'approval_pending_values' => $this->genc_approval_pending_gen_b(),
			'keterangan_contoh' => $a['contoh'],
			// K19 - dialog saat kotak foto kosong diklik
			'foto_kosong'       => 'Foto part AGV belum tersedia — bukan gambar yang rusak.',
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
