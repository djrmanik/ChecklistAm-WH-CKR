<?php
/**
 * ============================================================================
 *  GENERASI C - data menu PANTAU: "Approval" & "NOK History"   [GENC-16OKT26-PANTAU]
 *
 *  Dipakai PantauController (route pantau/approval & pantau/temuan). Dulu kode ini
 *  menumpang di PalletmoverController (route palletmover/approval & palletmover/uncompleted,
 *  warisan menu phpRAD) -> setiap perubahan Approval / NOK History ikut mengubah file
 *  Pallet Mover. Sekarang terpisah; route lama hanya mengarahkan ke sini.
 *
 *  Bukan route: ada di subfolder _base/ dan namanya tidak berakhiran "Controller"
 *  (pola sama dengan GencHubReader). Turunan GencChecklistBase supaya boleh memanggil
 *  helper protected cetakan milik controller mesin lain (genc_month_rows, genc_temuan_all, ...).
 *
 *  Approval  : jumlah checklist menunggu approval per unit, bulan terpilih (K28, tidak berubah).
 *  NOK History: temuan + status (K111-K113), tampil PER UNIT (K114-K116):
 *     tab buka    = temuan belum beres, semua bulan, per hari ini
 *     tab riwayat = temuan yang muncul di bulan terpilih (f = semua | beres | belum)
 * ============================================================================
 */
require_once __DIR__ . '/GencChecklistBase.php';

class GencPantauReader extends GencChecklistBase{

	function __construct(){ parent::__construct(); $this->tablename = 'palletmover'; }

	/** Tidak dipakai (kelas ini tidak menampilkan halaman mesin); wajib ada karena abstract. */
	protected function genc_conf(){ return array('page' => 'pantau', 'title' => 'Pantau'); }

	/** Controller mesin sebuah route (juga route am_* buatan menu Mesin & Unit). */
	protected function pantau_ctl($page){
		$ctl = genc_reg_controller($page);
		return ($ctl instanceof GencChecklistBase) ? $ctl : null;
	}

	/** Label pendek, kelompok & sub dari tab mesin (sama dengan ringkasan lama). */
	protected function pantau_tab_info($ctl, $page){
		$conf = $ctl->genc_c();
		$out = array('label' => array(), 'group' => array(), 'sub' => array(), 'title' => $conf['title']);
		foreach($conf['tabs'] as $t){
			if($t['page'] !== $page){ continue; }
			$k = isset($t['unit']) ? (string) $t['unit'] : '';
			$out['label'][$k] = $t['label'];
			$out['group'][$k] = isset($t['group']) ? (string) $t['group'] : '';
			$out['sub'][$k]   = isset($t['sub']) ? (string) $t['sub'] : '';
		}
		return $out;
	}

	// =====================================================================
	//  APPROVAL (ringkasan semua mesin, bulan terpilih)
	// =====================================================================

	public function approval($request){
		$period = $this->genc_period($request);
		$groups = array();
		$tot = 0;
		foreach(genc_reg_hub_machines() as $m){
			$rows = array();
			foreach($m['pages'] as $page){
				if(!ACL::is_allowed($page . '/list')){ continue; }
				$ctl = $this->pantau_ctl($page);
				if(!$ctl){ continue; }
				foreach($this->pantau_approval_rows($ctl, $page, $period) as $r){ $rows[] = $r; }
			}
			if(empty($rows)){ continue; }
			$sum = 0;
			foreach($rows as $r){ $sum += $r['menunggu']; }
			$tot += $sum;
			$groups[] = array('title' => $m['title'], 'icon' => isset($m['icon']) ? $m['icon'] : '', 'rows' => $rows, 'sum' => $sum);
		}
		return array('period' => $period, 'groups' => $groups, 'total' => $tot);
	}

	/** 1 baris per unit (mesin 1 unit: 1 baris). Unit nonaktif tanpa antrean & arsip tidak tampil. */
	protected function pantau_approval_rows($ctl, $page, $period){
		$ti = $this->pantau_tab_info($ctl, $page);
		$count = function($rows) use ($ctl){
			$n = 0;
			foreach($rows as $r){ if(!$ctl->genc_is_approved($r)){ $n++; } }
			return $n;
		};
		$out = array();
		if(genc_reg_is_archived($page, '')){ return $out; }
		$units = $ctl->genc_units();
		if(empty($units)){
			$n = $count($ctl->genc_month_rows($period['bulan'], $period['tahun']));
			if(genc_reg_is_inactive($page, '') && $n === 0){ return $out; }
			$out[] = array('label' => isset($ti['label']['']) ? $ti['label'][''] : $ti['title'], 'group' => '', 'sub' => isset($ti['sub']['']) ? $ti['sub'][''] : '',
				'menunggu' => $n, 'page' => $page, 'unit' => '');
			return $out;
		}
		$all = $ctl->genc_month_rows($period['bulan'], $period['tahun']);
		foreach($units as $slug => $u){
			$n = $count($ctl->genc_filter_unit($all, $slug));
			if(!empty($u['archived'])){ continue; }
			if((!empty($u['inactive']) || genc_reg_is_inactive($page, '')) && $n === 0){ continue; }
			$out[] = array('label' => isset($ti['label'][$slug]) ? $ti['label'][$slug] : $u['label'], 'group' => isset($ti['group'][$slug]) ? $ti['group'][$slug] : '',
				'sub' => $u['label'], 'menunggu' => $n, 'page' => $page, 'unit' => $slug);
		}
		$other = $count($ctl->genc_filter_unit($all, self::UNIT_OTHER));
		if($other > 0){
			$out[] = array('label' => 'Unit tidak dikenal', 'group' => '', 'sub' => 'nomor unit di data lama tidak cocok',
				'menunggu' => $other, 'page' => $page, 'unit' => self::UNIT_OTHER);
		}
		return $out;
	}

	// =====================================================================
	//  NOK HISTORY = TEMUAN PER UNIT
	// =====================================================================

	/**
	 * @param object $request  tab (buka|riwayat), f (semua|beres|belum), bulan, tahun, buka (kunci unit)
	 */
	public function temuan($request){
		$period = $this->genc_period($request);
		$tab = isset($request->tab) ? (string) $request->tab : 'buka';
		$f   = isset($request->f) ? (string) $request->f : 'semua';
		if($tab === 'beres'){ $tab = 'riwayat'; $f = 'beres'; }   // alamat lama (15 Okt): tab Sudah beres -> Riwayat, saring Sudah beres
		if(!in_array($tab, array('buka', 'riwayat'), true)){ $tab = 'buka'; }
		if(!in_array($f, array('semua', 'beres', 'belum'), true)){ $f = 'semua'; }
		$buka = isset($request->buka) ? preg_replace('/[^a-z0-9]/', '', strtolower((string) $request->buka)) : '';

		$today  = date('Y-m-d');
		$m_from = sprintf('%04d-%02d-01', $period['tahun'], $period['bulan']);
		$m_to   = date('Y-m-t', strtotime($m_from));
		$from   = $this->genc_temuan_from($period['bulan'], $period['tahun']);

		$groups = array();
		$tot = array('buka' => 0, 'unit_buka' => 0, 'tertua' => 0, 'riwayat' => 0, 'riwayat_beres' => 0, 'riwayat_belum' => 0, 'hari' => 0, 'mesin_buka' => 0, 'mesin_riwayat' => 0);
		foreach(genc_reg_hub_machines() as $m){
			$units = array(); $any = false;
			foreach($m['pages'] as $page){
				if(!ACL::is_allowed($page . '/list')){ continue; }
				if(genc_reg_is_archived($page, '')){ continue; }
				$ctl = $this->pantau_ctl($page);
				if(!$ctl){ continue; }
				$any = true;
				foreach($this->pantau_temuan_units($ctl, $page, $from) as $u){ $units[] = $u; }
			}
			if(!$any){ continue; }
			$sum = array('buka' => 0, 'unit_buka' => 0, 'riwayat' => 0, 'riwayat_beres' => 0, 'unit_riwayat' => 0, 'tertua' => 0);
			foreach($units as $ui => $u){
				$c = array('buka' => 0, 'riwayat' => 0, 'riwayat_beres' => 0, 'tertua' => 0, 'hari' => 0);
				foreach($u['items'] as $ii => $e){
					$open = ($e['end_id'] === null);
					$e['open'] = $open;
					$e['days'] = $this->genc_days_between($e['start_date'], $open ? $today : $e['end_date']);
					$e['in_riwayat'] = $e['start_date'] >= $m_from && $e['start_date'] <= $m_to;
					if($open){ $c['buka']++; if($e['days'] > $c['tertua']){ $c['tertua'] = $e['days']; } }
					if($e['in_riwayat']){ $c['riwayat']++; if(!$open){ $c['riwayat_beres']++; $c['hari'] += $e['days']; } }
					$u['items'][$ii] = $e;
				}
				$u['c'] = $c;
				$units[$ui] = $u;
				$sum['buka'] += $c['buka']; $sum['riwayat'] += $c['riwayat']; $sum['riwayat_beres'] += $c['riwayat_beres'];
				if($c['buka'] > 0){ $sum['unit_buka']++; }
				if($c['riwayat'] > 0){ $sum['unit_riwayat']++; }
				if($c['tertua'] > $sum['tertua']){ $sum['tertua'] = $c['tertua']; }
				$tot['hari'] += $c['hari'];
			}
			$tot['buka'] += $sum['buka']; $tot['unit_buka'] += $sum['unit_buka'];
			$tot['riwayat'] += $sum['riwayat']; $tot['riwayat_beres'] += $sum['riwayat_beres'];
			if($sum['tertua'] > $tot['tertua']){ $tot['tertua'] = $sum['tertua']; }
			if($sum['buka'] > 0){ $tot['mesin_buka']++; }
			if($sum['riwayat'] > 0){ $tot['mesin_riwayat']++; }
			$groups[] = array('title' => $m['title'], 'icon' => isset($m['icon']) ? $m['icon'] : '', 'units' => $units, 'sum' => $sum);
		}
		$tot['riwayat_belum'] = $tot['riwayat'] - $tot['riwayat_beres'];
		return array(
			'tab' => $tab, 'f' => $f, 'buka' => $buka,
			'period' => $period, 'groups' => $groups, 'total' => $tot,
			'today' => $today, 'from' => $from,
		);
	}

	/** Jumlah temuan belum beres semua mesin yang boleh dibuka (untuk chip di halaman Approval). */
	public function temuan_open_count(){
		$from = $this->genc_temuan_from();
		$n = 0;
		foreach(genc_reg_hub_machines() as $m){
			foreach($m['pages'] as $page){
				if(!ACL::is_allowed($page . '/list') || genc_reg_is_archived($page, '')){ continue; }
				$ctl = $this->pantau_ctl($page);
				if(!$ctl){ continue; }
				foreach($this->pantau_temuan_units($ctl, $page, $from) as $u){
					foreach($u['items'] as $e){ if($e['end_id'] === null){ $n++; } }
				}
			}
		}
		return $n;
	}

	/** Temuan satu route, dikelompokkan per unit (urutan unit = urutan tab / Mesin & Unit). */
	protected function pantau_temuan_units($ctl, $page, $from){
		$ti = $this->pantau_tab_info($ctl, $page);
		$units = $ctl->genc_units();
		$can_view = ACL::is_allowed($page . '/view');
		$order = array_flip(array_keys($units));
		$out = array();
		foreach($ctl->genc_temuan_all($from) as $u){
			$slug = $u['unit'];
			if($slug === ''){ $label = isset($ti['label']['']) ? $ti['label'][''] : $ti['title']; $group = ''; $sub = ''; }
			elseif($slug === self::UNIT_OTHER){ $label = $u['raw'] !== '' ? $u['raw'] : '(kosong)'; $group = 'Unit tidak dikenal'; $sub = 'nomor unit di data lama tidak cocok'; }
			else{
				$label = isset($ti['label'][$slug]) ? $ti['label'][$slug] : $units[$slug]['label'];
				$group = isset($ti['group'][$slug]) ? $ti['group'][$slug] : '';
				$sub   = $units[$slug]['label'];
			}
			$out[] = array(
				'key' => genc_tm_key($page, $slug, $u['raw']),
				'page' => $page, 'unit' => $slug, 'raw' => $u['raw'],
				'label' => $label, 'group' => $group, 'sub' => $sub, 'machine' => $ti['title'],
				'can_view' => $can_view,
				'uord' => isset($order[$slug]) ? $order[$slug] : count($order),
				'items' => $u['items'],
			);
		}
		usort($out, function($a, $b){
			if($a['uord'] !== $b['uord']){ return $a['uord'] < $b['uord'] ? -1 : 1; }
			return strcmp($a['raw'], $b['raw']);
		});
		return $out;
	}
}
