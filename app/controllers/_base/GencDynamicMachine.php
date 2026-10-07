<?php
/**
 * ============================================================================
 *  GencDynamicMachine                  [GENC-06OKT26-MESIN]  dibuat 6 Okt 2026
 *
 *  Controller untuk halaman mesin yang DIBUAT LEWAT MENU "Mesin & Unit"
 *  (route am_*). Tidak ada file controller per mesin: Router mencari nama
 *  route di registry (_shared/genc_registry.php) lalu memakai kelas ini.
 *
 *  Dua jenis halaman:
 *   - 'jenis' : jenis mesin baru (mis. am_hand_pallet). Tabel am_<slug> berisi
 *               kolom item_NN; isi checklist & unit dari tabel genc_mesin / genc_unit.
 *               1 tabel banyak unit (kolom `unit`) - sama dengan AGV/Forklift.
 *   - 'clone' : unit baru untuk mesin yang 1 tabel per unit (Dumping, Geprek,
 *               Conveyor). Tabel am_<...> = salinan struktur tabel bawaan; isi
 *               checklist, report, peta nilai lama = SAMA dengan halaman bawaan.
 *
 *  Semua perilaku halaman (daftar, isi, ubah, detail, approve, hapus, export,
 *  report) dari cetakan GencChecklistBase - tidak ada logika baru di sini.
 *  Hak akses: role_permissions page_name = route am_* (disalin dari halaman
 *  acuan waktu dibuat, bisa diatur di Role Permissions).
 *
 *  KEAMANAN ROUTE: nama kelas tidak berakhiran "Controller" & ada di _base/ ->
 *  tidak bisa dipanggil langsung lewat URL. make() static -> ditolak Router (K39).
 * ============================================================================
 */
require_once __DIR__ . '/GencChecklistBase.php';

class GencDynamicMachine extends GencChecklistBase{

	/** Route yang akan dilayani objek berikutnya (diisi make()). */
	private static $boot_page = null;

	/** @var array|null hasil genc_reg_page() */
	private $dyn = null;

	/** Buat controller untuk route am_* tertentu (dipakai Home & ringkasan Approval/NOK). */
	static function make($page){
		self::$boot_page = strtolower((string) $page);
		$o = new self();
		self::$boot_page = null;
		return $o;
	}

	function __construct(){
		parent::__construct();
		$page = self::$boot_page !== null ? self::$boot_page : strtolower((string) Router::$page_name);
		$this->dyn = genc_reg_page($page);
		$this->tablename = $page;
	}

	protected function genc_conf(){
		$page = $this->tablename;
		if(!$this->dyn){ return array('page' => $page, 'profile' => $page, 'title' => $page); }

		// ---------------------------------------------------------------- unit baru Dumping / Geprek / Conveyor
		if($this->dyn['kind'] === 'clone'){
			$u = $this->dyn['unit'];
			$f = $this->dyn['folder'];
			$src = genc_reg_controller($f['source']);
			$c = ($src instanceof GencChecklistBase) ? $src->genc_conf() : array('profile' => $f['profile']);
			$title = trim(($f['prefix'] !== '' ? $f['prefix'] . ' ' : '') . $u['label']);
			$code  = trim($u['kode']) !== '' ? trim($u['kode']) : $u['label'];
			$c['page']        = $page;
			$c['title']       = $title;
			$c['name_lower']  = $title;
			$c['group_title'] = $f['title'];
			$c['file_slug']   = preg_replace('/[^A-Za-z0-9]+/', '-', $title);
			$c['overrides']   = array_merge(isset($c['overrides']) ? (array) $c['overrides'] : array(), array(
				'table'        => $page,
				'machine_code' => $code,
				'title'        => 'Checklist AM ' . ($f['key'] === 'dumping' ? 'Mesin Dumping ' . $code : $title),
				'file_slug'    => preg_replace('/[^A-Za-z0-9]+/', '-', $code),
			));
			$c['tabs'] = array();          // diisi genc_reg_apply_conf() (bawaan + unit baru)
			return $c;
		}

		// ---------------------------------------------------------------- jenis mesin baru
		$m = $this->dyn['mesin'];
		$tabs = array();
		foreach(genc_reg_units_of_folder($page) as $r){
			$tabs[] = array('label' => $r['label'], 'sub' => '', 'page' => $page, 'unit' => genc_reg_unit_slug($r['label']));
		}
		$shown = 0;
		foreach(genc_reg_units_of_folder($page) as $r){ if((int) $r['aktif'] === 1){ $shown++; } }
		return array(
			'page'              => $page,
			'profile'           => $page,
			'title'             => $m['nama'],
			'group_title'       => $m['nama'],
			'machine_title'     => $m['nama'],
			'name_lower'        => $m['nama'],
			'area'              => $m['area'] !== '' ? $m['area'] : 'Warehouse Cikarang',
			'file_slug'         => preg_replace('/[^A-Za-z0-9]+/', '-', $m['nama']),
			'user_field'        => 'user_created',
			'update_user_field' => 'user_update',
			'tabs'              => $shown > 1 ? $tabs : array(),     // 1 unit aktif: tanpa baris tab
			// item yang ditambahkan SETELAH checklist lama tersimpan = kosong di record lama -> abu-abu, bukan temuan
			'legacy_values'     => array('BAIK' => 'OK'),
			'keterangan_contoh' => 'Contoh: bagian kotor, sudah dibersihkan &mdash; atau: ada bunyi kasar, sudah lapor ke Engineering (Pak ...).',
			'foto_kosong'       => 'Foto part ini belum diunggah admin (menu Mesin & Unit) — bukan gambar yang rusak.',
		);
	}
}
