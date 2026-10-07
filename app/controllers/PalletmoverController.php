<?php
/**
 * ============================================================================
 *  PalletmoverController         [GENC-24SEP26-PALLETMOVER] Generasi C, 24 Sep 2026
 *
 *  Pallet Mover 1-4 + Pallet Stacker dalam SATU tabel `palletmover`
 *  (kolom unit `no_palletmover`). Isi checklist kelimanya sama (8 item), tapi
 *  report Stacker memakai profil sendiri (judul "Pallet Stacker"):
 *      Pallet Mover 1-4 -> app/views/partials/_shared/machines/palletmover.php
 *      Pallet Stacker   -> app/views/partials/_shared/machines/palletstacker.php
 *  Tampil sebagai 5 tab unit (K7), dikelompokkan:
 *      PALLET MOVER:   PM 1 · PM 2 · PM 3 · PM 4
 *      PALLET STACKER: Stacker
 *  Profil per unit lewat 'unit_profiles' di cetakan (K23, sama dengan forklift).
 *  UI/UX sama dengan Conveyor, Mesin Geprek, Mesin Dumping, AGV, Forklift.
 *
 *  Semua logika checklist di app/controllers/_base/GencChecklistBase.php (cetakan).
 *  Tampilan: app/views/partials/_shared/genc/{list,add,view}.php
 *
 *  Controller Generasi A lama disimpan utuh di
 *  _backup_genc_24sept26/PalletmoverController.php. Cara balik: HANDOVER_TERKINI.md.
 *
 *  Route:
 *    palletmover?unit=pallet-mover-1   -> index()   daftar + ringkasan + export unit itu
 *                                         (tanpa ?unit= -> unit pertama, Pallet Mover 1)
 *    palletmover?unit=pallet-stacker   -> tab Stacker
 *    palletmover/view/{id}             -> view()
 *    palletmover/add?unit=<slug>       -> add()     (tanpa unit -> pilih unit dulu)
 *    palletmover/edit/{id}             -> edit()
 *    palletmover/delete/{id}           -> delete()  (GET + csrf_token)
 *    palletmover/approve/{id}          -> approve() (POST) <- ACL baru, database/genc_palletmover_24sept26.sql
 *
 *  Menu tingkat atas "Approval" (palletmover/approval) & "NOK History"
 *  (palletmover/uncompleted) dulu halaman daftar pallet mover + tombol lintas
 *  mesin (Pallet Mover / Forklift / Geprek). Sekarang (K28): halaman RINGKASAN
 *  SEMUA MESIN - tiap mesin & unit dengan jumlah checklist yang menunggu approval
 *  / perlu tindakan, klik = daftar Gen C mesin itu yang sudah tersaring.
 *
 *  Route lama lain (K3), diarahkan:
 *    palletmover1..4, palletstacker   -> palletmover?unit=<slug>  (bulan/tahun/format ikut)
 *    approvalbtn/{id}                 -> palletmover/view/{id}
 *    editfield                        -> ditolak (ubah lewat form)
 *  Export PDF/Print/Word: report lama, TIDAK berubah - unit dikunci persis
 *  seperti palletmover_report_export() lama (lihat genc_render_report di bawah).
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencChecklistBase.php';

class PalletmoverController extends GencChecklistBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "palletmover";
	}

	/** Profil per jenis, urut tampil. 'group' = judul kelompok tab. */
	protected function palletmover_profiles(){
		return array(
			array('profile' => 'palletmover',   'group' => 'Pallet Mover'),
			array('profile' => 'palletstacker', 'group' => 'Pallet Stacker'),
		);
	}

	/**
	 * 5 tab unit dari daftar unit di profil report (satu sumber dengan report).
	 * Label dipendekkan ("Pallet Mover 1" -> "PM 1", "Pallet Stacker" -> "Stacker")
	 * karena judul halaman & judul kelompok sudah menyebut jenisnya.
	 */
	protected function palletmover_tabs(){
		$tabs = array();
		foreach($this->palletmover_profiles() as $pf){
			foreach(checklist_machine_units(checklist_load_machine($pf['profile'])) as $slug => $u){
				$label = preg_replace('/^Pallet\s+Mover\s+/i', 'PM ', $u['label']);
				$label = preg_replace('/^Pallet\s+Stacker$/i', 'Stacker', $label);
				$tabs[] = array('label' => $label, 'sub' => '', 'page' => 'palletmover', 'unit' => $slug, 'group' => $pf['group']);
			}
		}
		return $tabs;
	}

	protected function genc_conf(){
		$profiles = array();
		foreach($this->palletmover_profiles() as $pf){ $profiles[] = $pf['profile']; }
		return array(
			'page'              => 'palletmover',
			'profile'           => 'palletmover',          // cadangan kalau unit tidak dikenal / "semua unit"
			'unit_profiles'     => $profiles,              // Stacker -> profil & report palletstacker
			'group_title'       => 'Pallet Mover & Stacker',
			'title'             => 'Pallet Mover',
			'machine_title'     => 'Pallet Mover & Stacker',
			'name_lower'        => 'pallet mover',
			'area'              => 'Warehouse CKR',         // [TEBAKAN] profil report belum punya Area (pertanyaan SPV)
			'file_slug'         => 'Pallet-Mover',
			'user_field'        => 'user_created',
			'update_user_field' => 'user_update',           // kolom lama Gen A (diisi form lama saat ubah)
			'edit_log'          => array('date' => 'date_perubahan', 'note' => 'perubahan'),   // dipakai HANYA kalau kolomnya ada (K9)
			'tabs'              => $this->palletmover_tabs(),
			// Isian lama di luar OK/NOK/PR dibaca lewat peta nilai bersama (K6/K21) - data tidak diubah.
			'legacy_values'     => $this->genc_legacy_values_gen_b(),
			'keterangan_contoh' => 'Contoh: roda depan agak oblak, sudah lapor ke Engineering (Pak ...) &mdash; atau: air aki kurang, sudah ditambah.',
			// K19 - dialog saat kotak foto kosong diklik
			'foto_kosong'       => 'Foto part pallet mover & pallet stacker belum tersedia — bukan gambar yang rusak. Cek part-nya langsung di unit, sesuai standar yang tertulis di sebelah kotak ini.',
		);
	}

	/**
	 * Report PDF/Print/Word per tab = PERSIS logika palletmover_report_export() lama:
	 * profil jenis unit itu (palletmover / palletstacker) + daftar unit dikunci ke unit itu.
	 * "Semua unit" (?unit=semua, ditawarkan di tab Pallet Mover) -> cetakan bawaan:
	 * profil palletmover, 4 unit, 1 unit per halaman.
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
	protected function palletmover_old_tab($slug){
		$q = array('unit' => $slug);
		foreach(array('bulan', 'tahun', 'format') as $k){
			if(isset($this->request->$k) && $this->request->$k !== ''){ $q[$k] = (string) $this->request->$k; }
		}
		return $this->redirect('palletmover?' . http_build_query($q));
	}
	function palletmover1($fieldname = null, $fieldvalue = null){ return $this->palletmover_old_tab('pallet-mover-1'); }
	function palletmover2($fieldname = null, $fieldvalue = null){ return $this->palletmover_old_tab('pallet-mover-2'); }
	function palletmover3($fieldname = null, $fieldvalue = null){ return $this->palletmover_old_tab('pallet-mover-3'); }
	function palletmover4($fieldname = null, $fieldvalue = null){ return $this->palletmover_old_tab('pallet-mover-4'); }
	function palletstacker($fieldname = null, $fieldvalue = null){ return $this->palletmover_old_tab('pallet-stacker'); }

	/** Form approve lama per record -> halaman detail (tombol Approve ada di sana). */
	function approvalbtn($rec_id = null, $formdata = null){
		$id = (int) $rec_id;
		return $this->redirect($id > 0 ? "palletmover/view/" . $id : "palletmover/approval");
	}

	/** Edit sebaris (inline) daftar lama - tidak dipakai lagi: ubah lewat form supaya kondisi dihitung ulang. */
	function editfield($rec_id = null, $formdata = null){
		render_error("Ubah data lewat halaman Ubah checklist.");
		return null;
	}

	// =====================================================================
	//  MENU "APPROVAL" & "NOK HISTORY" - RINGKASAN SEMUA MESIN (K28)
	// =====================================================================

	/** Menu "Approval": semua mesin, jumlah checklist yang menunggu approval per unit. */
	function approval($fieldname = null, $fieldvalue = null){
		return $this->palletmover_hub('menunggu');
	}

	/** Menu "NOK History": semua mesin, jumlah checklist yang perlu tindakan per unit. */
	function uncompleted($fieldname = null, $fieldvalue = null){
		return $this->palletmover_hub('tindakan');
	}

	/**
	 * Mesin di halaman ringkasan, urut sama dengan menu Autonomous Maintenance.
	 * 'pages' = route (= nama controller); tiap route punya cetakan Gen C sendiri.
	 */
	protected function palletmover_hub_machines(){
		return genc_reg_hub_machines();   // [GENC-06OKT26-MESIN] 6 mesin bawaan (urut sama) + tambahan dari menu Mesin & Unit
	}

	/**
	 * Hitung per unit (bulan terpilih): menunggu approval & perlu tindakan.
	 * Penilaian memakai cetakan milik mesin itu sendiri (profil, peta nilai lama,
	 * status approval lama) -> angka di sini = angka KPI di halaman mesinnya.
	 * Mesin yang role ini tidak punya <page>/list tidak ditampilkan.
	 */
	protected function palletmover_hub($mode){
		$period = $this->genc_period($this->request);
		$groups = array();
		$tot = array('menunggu' => 0, 'tindakan' => 0);
		foreach($this->palletmover_hub_machines() as $m){
			$rows = array();
			foreach($m['pages'] as $page){
				if(!ACL::is_allowed($page . '/list')){ continue; }
				if($page === 'palletmover'){ $ctl = $this; }
				else{
					$ctl = genc_reg_controller($page);   // [GENC-06OKT26-MESIN] juga route am_* (tanpa file controller)
					if(!($ctl instanceof GencChecklistBase)){ continue; }
				}
				foreach($this->palletmover_hub_rows($ctl, $page, $period) as $r){ $rows[] = $r; }
			}
			if(empty($rows)){ continue; }
			$sum = array('menunggu' => 0, 'tindakan' => 0);
			foreach($rows as $r){ $sum['menunggu'] += $r['menunggu']; $sum['tindakan'] += $r['tindakan']; }
			$tot['menunggu'] += $sum['menunggu'];
			$tot['tindakan'] += $sum['tindakan'];
			$groups[] = array('title' => $m['title'], 'rows' => $rows, 'sum' => $sum);
		}
		$data = array(
			'mode'   => $mode,
			'period' => $period,
			'groups' => $groups,
			'total'  => $tot,
		);
		$this->view->page_title = ($mode === 'menunggu' ? 'Approval' : 'NOK History') . ' - semua mesin';
		return $this->render_view("_shared/genc/hub.php", $data);
	}

	/** Baris ringkasan satu route: 1 baris per unit (mesin 1 unit: 1 baris). Link dibentuk di view. */
	protected function palletmover_hub_rows($ctl, $page, $period){
		$conf = $ctl->genc_c();
		// label pendek dari tab (PM 1, TTL 6, KIR3, 608FD18...), kelompok dari tab
		$tab_label = array(); $tab_group = array(); $tab_sub = array();
		foreach($conf['tabs'] as $t){
			if($t['page'] !== $page){ continue; }
			$k = isset($t['unit']) ? (string) $t['unit'] : '';
			$tab_label[$k] = $t['label'];
			$tab_group[$k] = isset($t['group']) ? (string) $t['group'] : '';
			$tab_sub[$k]   = isset($t['sub']) ? (string) $t['sub'] : '';
		}
		$count = function($rows, $items) use ($ctl){
			$n = array('menunggu' => 0, 'tindakan' => 0);
			foreach($rows as $r){
				if(!$ctl->genc_is_approved($r)){ $n['menunggu']++; }
				if(!$ctl->genc_is_ok($r, $items)){ $n['tindakan']++; }
			}
			return $n;
		};
		$out = array();
		$units = $ctl->genc_units();
		if(empty($units)){
			$n = $count($ctl->genc_month_rows($period['bulan'], $period['tahun']), $ctl->genc_items());
			if(genc_reg_is_inactive($page, '') && ($n['menunggu'] + $n['tindakan']) === 0){ return $out; }   // [GENC-06OKT26-MESIN] nonaktif & beres: tidak tampil
			$out[] = array(
				'label' => isset($tab_label['']) ? $tab_label[''] : $conf['title'],
				'group' => '', 'sub' => (isset($tab_sub['']) ? $tab_sub[''] : ''),
				'menunggu' => $n['menunggu'], 'tindakan' => $n['tindakan'],
				'page' => $page, 'unit' => '',
			);
			return $out;
		}
		$other = null;
		foreach($units as $slug => $u){
			$ctl->genc_set_unit_ctx($slug);                     // profil unit ini (forklift electric/diesel, stacker)
			$all = $ctl->genc_month_rows($period['bulan'], $period['tahun']);
			$n = $count($ctl->genc_filter_unit($all, $slug), $ctl->genc_items());
			if($other === null){ $other = $count($ctl->genc_filter_unit($all, self::UNIT_OTHER), $ctl->genc_items()); }
			if((!empty($u['inactive']) || genc_reg_is_inactive($page, '')) && ($n['menunggu'] + $n['tindakan']) === 0){ continue; }   // [GENC-06OKT26-MESIN] nonaktif & beres: tidak tampil
			$out[] = array(
				'label' => isset($tab_label[$slug]) ? $tab_label[$slug] : $u['label'],
				'group' => isset($tab_group[$slug]) ? $tab_group[$slug] : '',
				'sub'   => $u['label'],
				'menunggu' => $n['menunggu'], 'tindakan' => $n['tindakan'],
				'page' => $page, 'unit' => $slug,
			);
		}
		// record lama dengan nomor unit yang tidak dikenal -> baris sendiri, hanya kalau ada isinya
		if($other !== null && ($other['menunggu'] + $other['tindakan']) > 0){
			$out[] = array(
				'label' => 'Unit tidak dikenal', 'group' => '', 'sub' => 'nomor unit di data lama tidak cocok',
				'menunggu' => $other['menunggu'], 'tindakan' => $other['tindakan'],
				'page' => $page, 'unit' => self::UNIT_OTHER,
			);
		}
		return $out;
	}
}
