<?php
/**
 * ============================================================================
 *  ForkliftController                 [GENC-24SEP26-FORKLIFT] Generasi C, 24 Sep 2026
 *
 *  Forklift: 5 unit ELECTRIC + 1 unit DIESEL dalam SATU tabel `forklift`,
 *  dengan isi checklist yang BEDA per jenis:
 *      Electric (16 item) -> app/views/partials/_shared/machines/forklift_electric.php
 *      Diesel   (19 item) -> app/views/partials/_shared/machines/forklift_diesel.php
 *  Tampil sebagai 6 tab unit (K7), dikelompokkan:
 *      ELECTRIC: 185E00370 · 131AD0216 · R2B-06835 · 131AC8606 · 131AE3297
 *      DIESEL:   608FD18
 *  Tab aktif (atau record yang dibuka) menentukan isi form, penilaian, dan
 *  report - lewat kunci 'unit_profiles' di cetakan. UI/UX sama dengan Conveyor,
 *  Mesin Geprek, Mesin Dumping, AGV.
 *
 *  Semua logika di app/controllers/_base/GencChecklistBase.php (cetakan).
 *  Tampilan: app/views/partials/_shared/genc/{list,add,view}.php
 *
 *  Controller Generasi A lama disimpan utuh di
 *  _backup_genc_24sept26/ForkliftController.php. Cara balik: HANDOVER_TERKINI.md.
 *
 *  Route:
 *    forklift?unit=185e00370           -> index()   daftar + ringkasan + export unit itu
 *                                         (tanpa ?unit= -> unit pertama, 185E00370)
 *    forklift?unit=608fd18             -> tab Diesel
 *    forklift/view/{id}                -> view()    (isi electric / diesel sesuai unit record)
 *    forklift/add?unit=<slug>          -> add()     (tanpa unit -> pilih unit dulu)
 *    forklift/edit/{id}                -> edit()
 *    forklift/delete/{id}              -> delete()  (GET + csrf_token)
 *    forklift/approve/{id}             -> approve() (POST) <- ACL baru, database/genc_forklift_24sept26.sql
 *  Route lama (K3), diarahkan:
 *    forklift_1..forklift_5, forklift_diesel  -> forklift?unit=<slug>  (bulan/tahun/format ikut)
 *    add_forklift_diesel                      -> forklift/add?unit=608fd18
 *    approval                                 -> forklift?status=menunggu (unit pertama yang ada antreannya)
 *    nok_history                              -> forklift?status=tindakan (unit pertama yang ada temuannya)
 *    approvalbtn/{id}                         -> forklift/view/{id}
 *    editfield                                -> ditolak (ubah lewat form)
 *  Export PDF/Print/Word: report lama, TIDAK berubah - unit dikunci persis
 *  seperti forklift_report_export() lama (lihat genc_render_report di bawah).
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencChecklistBase.php';

class ForkliftController extends GencChecklistBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "forklift";
	}

	/** Profil per jenis, urut tampil. 'group' = judul kelompok tab. */
	protected function forklift_profiles(){
		return array(
			array('profile' => 'forklift_electric', 'group' => 'Electric'),
			array('profile' => 'forklift_diesel',   'group' => 'Diesel'),
		);
	}

	/** 6 tab unit dari daftar unit di profil report (satu sumber dengan report). */
	protected function forklift_tabs(){
		$tabs = array();
		foreach($this->forklift_profiles() as $pf){
			foreach(checklist_machine_units(checklist_load_machine($pf['profile'])) as $slug => $u){
				$tabs[] = array('label' => $u['label'], 'sub' => '', 'page' => 'forklift', 'unit' => $slug, 'group' => $pf['group']);
			}
		}
		return $tabs;
	}

	protected function genc_conf(){
		$profiles = array();
		foreach($this->forklift_profiles() as $pf){ $profiles[] = $pf['profile']; }
		return array(
			'page'              => 'forklift',
			'profile'           => 'forklift_electric',   // cadangan kalau unit tidak dikenal
			'unit_profiles'     => $profiles,             // isi checklist per unit (electric / diesel)
			'unit_title_machine'=> true,                  // "Forklift Electric 185E00370", bukan cuma nomor seri
			'group_title'       => 'Forklift',
			'title'             => 'Forklift',
			'machine_title'     => 'Forklift',
			'name_lower'        => 'forklift',
			'area'              => 'Warehouse CKR',        // [TEBAKAN] profil report belum punya Area (pertanyaan SPV)
			'file_slug'         => 'Forklift',
			'user_field'        => 'user_created',
			'update_user_field' => 'user_update',          // kolom lama Gen A (diisi form lama saat ubah)
			'edit_log'          => array('date' => 'date_perubahan', 'note' => 'perubahan'),   // kalau kolomnya ada
			'tabs'              => $this->forklift_tabs(),
			// Isian lama di luar OK/NOK/PR (Steering/Spion sebelum 23 Sep, pilihan master_select form diesel)
			// dibaca lewat peta nilai bersama (K6) - data tidak diubah, yang tidak dikenal = abu-abu.
			'legacy_values'     => $this->genc_legacy_values_gen_b(),
			'extra_fields'      => array(
				array(
					'db'          => 'jam_kerja',
					'label'       => 'Jam kerja',
					'type'        => 'number',
					'required'    => true,              // [FAKTA] form lama: wajib, angka
					'suffix'      => 'jam',
					'placeholder' => 'Contoh: 1250',
					'hint'        => 'Angka di hour meter forklift saat pengecekan.',
					'last'        => true,
					'list'        => true,
				),
			),
			'keterangan_contoh' => 'Contoh: klakson bunyinya lemah, sudah lapor ke Engineering (Pak ...) &mdash; atau: air accu kurang, sudah ditambah.',
			// K19 - dialog saat kotak foto kosong diklik
			'foto_kosong'       => 'Foto part forklift belum tersedia — bukan gambar yang rusak.',
		);
	}

	/**
	 * Report PDF/Print/Word per tab = PERSIS logika forklift_report_export() lama:
	 * profil jenis unit itu + daftar unit dikunci ke unit itu saja.
	 * "Semua unit" (?unit=semua, cuma ditawarkan di tab electric) -> cetakan bawaan:
	 * profil electric, 1 unit per halaman.
	 */
	protected function genc_render_report($request, $fmt){
		$req = isset($request->unit) ? strtolower(trim((string) $request->unit)) : '';
		if($req === 'semua'){ return parent::genc_render_report($request, $fmt); }
		$units = $this->genc_units();
		$slug  = $this->genc_current_unit(false);
		$unit  = $units[$slug];
		$want  = checklist_norm_unit($unit['label']);
		$lock  = array();
		foreach(checklist_load_machine($unit['profile'])['units'] as $u){
			if(checklist_norm_unit($u['label']) === $want){ $lock = array($u); break; }
		}
		if(empty($lock)){ $lock = array(array('label' => $unit['label'], 'alias' => array($unit['label']))); }
		return checklist_render_report($this->GetModel(), $unit['profile'], $request, $fmt, array('units' => $lock));
	}

	// =====================================================================
	//  ROUTE LAMA (Generasi A) - diarahkan ke halaman Gen C (K3)
	// =====================================================================

	/** Tab lama -> tab Gen C unit yang sama. Periode & format export ikut dibawa. */
	protected function forklift_old_tab($slug){
		$q = array('unit' => $slug);
		foreach(array('bulan', 'tahun', 'format') as $k){
			if(isset($this->request->$k) && $this->request->$k !== ''){ $q[$k] = (string) $this->request->$k; }
		}
		return $this->redirect('forklift?' . http_build_query($q));
	}
	function forklift_1($fieldname = null, $fieldvalue = null){ return $this->forklift_old_tab('185e00370'); }
	function forklift_2($fieldname = null, $fieldvalue = null){ return $this->forklift_old_tab('131ad0216'); }
	function forklift_3($fieldname = null, $fieldvalue = null){ return $this->forklift_old_tab('r2b-06835'); }
	function forklift_4($fieldname = null, $fieldvalue = null){ return $this->forklift_old_tab('131ac8606'); }
	function forklift_5($fieldname = null, $fieldvalue = null){ return $this->forklift_old_tab('131ae3297'); }
	function forklift_diesel($fieldname = null, $fieldvalue = null){ return $this->forklift_old_tab('608fd18'); }

	/** Form diesel lama -> form Gen C tab Diesel. */
	function add_forklift_diesel($formdata = null){
		return $this->redirect('forklift/add?unit=608fd18');
	}

	/**
	 * Halaman "Approval" lama (juga tombol lintas mesin di halaman approval
	 * pallet mover) -> daftar Gen C "Menunggu approval", langsung di unit
	 * pertama yang punya antrean bulan ini (supaya SPV tidak mendarat di tab kosong).
	 */
	function approval($fieldname = null, $fieldvalue = null){
		return $this->redirect('forklift?' . http_build_query(array('status' => 'menunggu', 'unit' => $this->forklift_first_unit('menunggu'))));
	}

	/** Halaman "NOK History" lama -> daftar Gen C "Perlu tindakan" (unit pertama yang ada temuannya). */
	function nok_history($fieldname = null, $fieldvalue = null){
		return $this->redirect('forklift?' . http_build_query(array('status' => 'tindakan', 'unit' => $this->forklift_first_unit('tindakan'))));
	}

	/** Form approve lama per record -> halaman detail (tombol Approve ada di sana). */
	function approvalbtn($rec_id = null, $formdata = null){
		$id = (int) $rec_id;
		return $this->redirect($id > 0 ? "forklift/view/" . $id : "forklift?status=menunggu");
	}

	/** Edit sebaris (inline) daftar lama - tidak dipakai lagi: ubah lewat form supaya kondisi dihitung ulang. */
	function editfield($rec_id = null, $formdata = null){
		render_error("Ubah data lewat halaman Ubah checklist.");
		return null;
	}

	/**
	 * Unit pertama (urut tab) yang bulan ini punya record menunggu approval /
	 * perlu tindakan. Tidak ada -> unit pertama. Penilaian per unit memakai
	 * profil unit itu (electric / diesel).
	 */
	protected function forklift_first_unit($status){
		$period = $this->genc_period($this->request);
		$units  = $this->genc_units();
		foreach($units as $slug => $u){
			$this->genc_set_unit_ctx($slug);
			$items = $this->genc_items();
			foreach($this->genc_filter_unit($this->genc_month_rows($period['bulan'], $period['tahun']), $slug) as $r){
				if($status === 'menunggu' && !$this->genc_is_approved($r)){ return $slug; }
				if($status === 'tindakan' && !$this->genc_is_ok($r, $items)){ return $slug; }
			}
		}
		reset($units);
		return key($units);
	}
}
