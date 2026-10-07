<?php
/**
 * ============================================================================
 *  GENERASI C - pembaca data SEMUA MESIN untuk Home/Dashboard   [GENC-24SEP26-SHELL]
 *
 *  Dipakai HomeController. Bukan route: ada di subfolder _base/ dan namanya tidak
 *  berakhiran "Controller" (sama dengan GencChecklistBase).
 *
 *  Kenapa turunan GencChecklistBase: helper cetakan (genc_month_rows, genc_is_ok,
 *  genc_units, ...) itu PROTECTED. PHP mengizinkan kelas turunan memanggil helper
 *  protected milik objek lain yang dideklarasikan di kelas induk yang sama - pola
 *  yang sama dengan ringkasan Approval/NOK History (PalletmoverController, K28).
 *  Jadi angka di dashboard dihitung dengan aturan cetakan mesin masing-masing
 *  (profil per unit, peta nilai lama, approval lama) = sama dengan KPI di halaman mesin.
 *
 *  Menambah mesin baru: 1 baris di genc_hub_machines() (urut = menu). Samakan juga
 *  PalletmoverController::palletmover_hub_machines().
 * ============================================================================
 */
require_once __DIR__ . '/GencChecklistBase.php';

class GencHubReader extends GencChecklistBase{

	function __construct(){ parent::__construct(); $this->tablename = 'palletmover'; }

	/** Tidak dipakai (kelas ini tidak menampilkan halaman mesin); wajib ada karena abstract. */
	protected function genc_conf(){ return array('page' => 'home', 'title' => 'Home'); }

	/**
	 * [GENC-06OKT26-MESIN] Daftar mesin dari registry (_shared/genc_registry.php): 6 mesin bawaan (urut & ikon
	 * sama dengan dulu) + unit / jenis mesin yang ditambah lewat menu Mesin & Unit.
	 */
	protected function genc_hub_machines(){
		return genc_reg_hub_machines();
	}

	/**
	 * Semua mesin yang boleh dibuka role ini (<page>/list), 1 baris per unit:
	 * status HARI INI + angka BULAN INI (menunggu approval, perlu tindakan, % hari terisi).
	 */
	public function collect(){
		$period = $this->genc_period((object) array());      // bulan berjalan
		$groups = array();
		foreach($this->genc_hub_machines() as $m){
			$rows = array();
			foreach($m['pages'] as $page){
				if(!ACL::is_allowed($page . '/list')){ continue; }
				if(genc_reg_is_inactive($page, '')){ continue; }   // [GENC-06OKT26-MESIN] halaman nonaktif tidak tampil di Home
				$ctl = genc_reg_controller($page);                 // [GENC-06OKT26-MESIN] juga route am_* (tanpa file controller)
				if(!($ctl instanceof GencChecklistBase)){ continue; }
				foreach($this->genc_hub_unit_rows($ctl, $page, $period) as $r){ $rows[] = $r; }
			}
			if(empty($rows)){ continue; }
			$groups[] = array('title' => $m['title'], 'icon' => $m['icon'], 'rows' => $rows);
		}
		return array('period' => $period, 'groups' => $groups);
	}

	protected function genc_hub_unit_rows($ctl, $page, $period){
		$conf = $ctl->genc_c();
		$tab = array();
		foreach($conf['tabs'] as $t){
			if($t['page'] !== $page){ continue; }
			$tab[isset($t['unit']) ? (string) $t['unit'] : ''] = $t;
		}
		$can_add = ACL::is_allowed($page . '/add');
		$one = function($slug, $label, $group, $sub) use ($ctl, $page, $period, $can_add){
			if($slug !== ''){ $ctl->genc_set_unit_ctx($slug); }
			$items = $ctl->genc_items();
			$month = $ctl->genc_month_rows($period['bulan'], $period['tahun']);
			if($slug !== ''){ $month = $ctl->genc_filter_unit($month, $slug); }
			$sum = $ctl->genc_summary($month, $items, $period);
			$today = $ctl->genc_today_rows($slug);
			$t = !empty($today) ? $today[0] : null;
			$state = 'none';
			if($t){ $state = $ctl->genc_is_ok($t, $items) ? 'ok' : 'issue'; }
			$bad = array();
			if($t && $state === 'issue' && !empty($t['_bad_parts'])){ $bad = array_slice($t['_bad_parts'], 0, 2); }
			return array(
				'label' => $label, 'group' => $group, 'sub' => $sub,
				'page' => $page, 'unit' => $slug,
				'state' => $state,
				'today_id'   => $t ? $t['id'] : null,
				'today_by'   => $t ? $t['user_created'] : '',
				'today_time' => $t ? $t['date_created'] : '',
				'today_bad'  => $bad,
				'today_count'=> count($today),
				'menunggu'   => $sum['pending'],
				'tindakan'   => $sum['issues'],
				'filled'     => $sum['filled'],
				'elapsed'    => $sum['elapsed'],
				'percent'    => $sum['percent'],
				'can_add'    => $can_add,
			);
		};
		$out = array();
		$units = $ctl->genc_units();
		if(empty($units)){
			$t = isset($tab['']) ? $tab[''] : array();
			$out[] = $one('', isset($t['label']) ? $t['label'] : $conf['title'], '', isset($t['sub']) ? (string) $t['sub'] : '');
			return $out;
		}
		foreach($units as $slug => $u){
			if(!empty($u['inactive'])){ continue; }   // [GENC-06OKT26-MESIN] unit nonaktif tidak tampil di Home
			$t = isset($tab[$slug]) ? $tab[$slug] : array();
			$out[] = $one($slug, isset($t['label']) ? $t['label'] : $u['label'], isset($t['group']) ? (string) $t['group'] : '', $u['label']);
		}
		return $out;
	}
}
