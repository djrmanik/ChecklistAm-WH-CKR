<?php
/**
 * ============================================================================
 *  GencChecklistBase                        [GENC-24SEP26]  dibuat 24 Sep 2026
 *
 *  "Cetakan" controller halaman checklist GENERASI C. Isinya logika yang
 *  sebelumnya ditulis langsung di ConveyorController (23 Sep 2026) - dipindah
 *  ke sini APA ADANYA, cuma nama tabel / route / judul jadi parameter.
 *
 *  Controller mesin cukup:
 *
 *      require_once __DIR__ . '/_base/GencChecklistBase.php';
 *      class Mesin_geprekController extends GencChecklistBase{
 *          function __construct(){ parent::__construct(); $this->tablename = "mesin_geprek"; }
 *          protected function genc_conf(){ return array( ...lihat genc_default_conf()... ); }
 *      }
 *
 *  Tampilan: 3 view BERSAMA di app/views/partials/_shared/genc/
 *  (list.php, add.php, view.php). Nama mesin / area / route dikirim lewat
 *  $data['genc'] -> view yang sama dipakai semua mesin.
 *
 *  KEAMANAN ROUTE (phpRAD Router):
 *   - File ini di SUBFOLDER app/controllers/_base/ -> tidak di-autoload Router
 *     dan namanya tidak berakhiran "Controller" -> tidak bisa jadi route.
 *   - Method halaman (index/view/add/edit/delete/approve) = public (route).
 *     Semua helper = PROTECTED -> tidak lolos is_callable() Router, jadi
 *     tidak bisa dipanggil lewat URL (mis. conveyor/genc_find/1).
 *
 *  Kolom opsional Gen C (date_approve, date_update, user_update): kalau kolom
 *  belum ada di tabel (SQL belum dijalankan), halaman tetap jalan - kolom itu
 *  cuma tidak dibaca / ditulis. Lihat genc_has_column().
 * ============================================================================
 */
require_once __DIR__ . '/../../views/partials/_shared/checklist_report_engine.php';

abstract class GencChecklistBase extends SecureController{

	/** Nilai yang boleh disimpan di kolom item. Urutan = urutan tombol di form. */
	const ITEM_VALUES = 'OK,NOK,PR';

	/**
	 * true  = record yang SUDAH di-approve lalu datanya diubah -> approval
	 *         dikosongkan lagi (SPV perlu approve ulang data yang baru).
	 * false = approval tetap.
	 */
	const RESET_APPROVAL_ON_EDIT = true;

	/** Jumlah baris per halaman di daftar. */
	const PER_PAGE = 20;

	/** [GENC-24SEP26-AGV] ?unit= untuk record yang nomor unitnya tidak cocok dengan unit mana pun. */
	const UNIT_OTHER = 'lainnya';

	/** Cache per objek (BUKAN static di method: sejak PHP 8.1 static di method warisan dipakai bersama antar kelas turunan). */
	private $genc_cache = array();

	/**
	 * Konfigurasi mesin - WAJIB diisi kelas turunan. Kunci yang dikenal:
	 *   page              segmen URL & page_name ACL          'mesin_geprek'
	 *   profile           nama file machines/<profile>.php     'mesin_geprek'
	 *   overrides         timpaan profil (dumping)            array()
	 *   title             nama mesin di judul                  'Mesin Geprek'
	 *   name_lower        nama mesin di dalam kalimat          'mesin geprek'
	 *   area              area di subjudul & kartu meta        '4M'
	 *   file_slug         nama file CSV                        'Mesin-Geprek'
	 *   user_field        kolom pelaksana                      'user_created' (dumping: 'user_create')
	 *   update_user_field kolom pengubah                       'user_update'
	 *   keterangan_contoh placeholder kotak keterangan (HTML)
	 *
	 *   [GENC-24SEP26-DUMPING] kunci tambahan (semua opsional; kosong = perilaku lama):
	 *   group_title       judul besar halaman ber-tab             'Mesin Dumping'
	 *   tabs              tab di atas halaman (K12 / K7):
	 *                     array(array('label' => 'KIR3', 'sub' => 'KIR3P01DP001', 'page' => 'kir3p01dp001'), ...)
	 *                     tab yang role-nya tidak punya <page>/list disembunyikan
	 *   legacy_values     peta isian teks bebas lama (Gen B) -> OK/NOK/PR (K6).
	 *                     Kunci = teks yang sudah dinormalisasi (genc_norm_text).
	 *                     Hanya untuk TAMPILAN & hitungan; data di DB tidak diubah.
	 *   approval_pending_values  isi kolom approval lama yang artinya "belum" ('-', 'BELUM', ...)
	 *   edit_log          array('date' => 'date_perubahan', 'note' => 'perubahan') - kolom
	 *                     lama yang diisi saat record diubah (kalau kolomnya ada)
	 *
	 *   [GENC-24SEP26-AGV] MULTI-UNIT (K7) - aktif OTOMATIS kalau profil punya 'unit_field' + 'units'
	 *   (AGV, nanti Pallet Mover & Forklift). Mesin tanpa daftar unit tidak berubah sama sekali.
	 *     - unit aktif = ?unit=<slug> (slug dari checklist_machine_units, mis. 'agv-ttl-7');
	 *       tidak ada / tidak dikenal -> unit pertama; '?unit=lainnya' -> record yang nomor
	 *       unitnya tidak cocok dengan unit mana pun (salah ketik lama)
	 *     - daftar, ringkasan, "hari ini", export -> per unit aktif
	 *     - form isi WAJIB punya unit (tanpa unit -> halaman pilih unit); disimpan LABEL kanonik
	 *     - tabs boleh berisi 'unit' => '<slug>' dan 'group' => 'Table Top Lift' (judul kelompok tab)
	 *   machine_title     nama jenis mesin di kartu info ('AGV Table Top Lift'); default = title
	 *   foto_kosong       teks dialog saat kotak foto kosong diklik (item tanpa 'foto')
	 *
	 *   [GENC-24SEP26-FORKLIFT] kunci tambahan (semua opsional; kosong = perilaku lama):
	 *   unit_profiles     beberapa profil dalam SATU tabel, isi checklist beda per unit
	 *                     (forklift: array('forklift_electric', 'forklift_diesel')). Unit semua
	 *                     profil digabung jadi tab; isi form, penilaian, report & nama mesin
	 *                     mengikuti profil UNIT AKTIF (tab / record). 'profile' = profil cadangan.
	 *   unit_title_machine true -> judul unit = "<machine_code profil> <label unit>"
	 *                     (label unit forklift = nomor seri, kurang jelas kalau berdiri sendiri)
	 *   extra_fields      isian angka/teks di luar item Baik/Tidak baik/Perawatan (forklift: jam_kerja):
	 *                     array(array('db' => 'jam_kerja', 'label' => 'Jam kerja', 'type' => 'number',
	 *                       'required' => true, 'suffix' => 'jam', 'hint' => '...', 'placeholder' => '...',
	 *                       'last' => true, 'list' => true))
	 *                     last = tampilkan isian terakhir unit ini di form; list = tampil di daftar
	 *                     Kolom yang tidak ada di tabel dilewati (K9).
	 * @return array
	 */
	abstract protected function genc_conf();

	/** Konfigurasi lengkap (default + milik mesin). */
	protected function genc_c(){
		if(!isset($this->genc_cache['conf'])){
			$c = array_merge(array(
				'page'              => $this->tablename,
				'profile'           => $this->tablename,
				'overrides'         => array(),
				'title'             => ucfirst($this->tablename),
				'name_lower'        => strtolower($this->tablename),
				'area'              => '',
				'file_slug'         => ucfirst($this->tablename),
				'user_field'        => 'user_created',
				'update_user_field' => 'user_update',
				'keterangan_contoh' => 'Contoh: bagian kotor, sudah dibersihkan &mdash; atau: ada bunyi kasar, sudah lapor ke Engineering (Pak ...).',
				'group_title'       => '',
				'tabs'              => array(),
				'legacy_values'     => array(),
				'approval_pending_values' => array(),
				'edit_log'          => array(),
				'machine_title'     => '',
				'foto_kosong'       => '',
				'unit_profiles'     => array(),     // [GENC-24SEP26-FORKLIFT]
				'unit_title_machine'=> false,
				'extra_fields'      => array(),
			), $this->genc_conf());
			$this->genc_cache['conf'] = $c;
		}
		return $this->genc_cache['conf'];
	}

	/**
	 * Bagian konfigurasi yang dibutuhkan view (nama, area, route).
	 * [GENC-24SEP26-AGV] $unit = slug unit yang ditampilkan (null = dari URL); $raw_unit = isi
	 * kolom unit record yang tidak dikenal (untuk judul). Mesin tanpa unit: hasil sama dengan dulu.
	 */
	protected function genc_view_conf($unit = null, $raw_unit = ''){
		$c = $this->genc_c();
		$v = $this->genc_view_conf_base();
		if($c['foto_kosong'] !== ''){ $v['foto_kosong'] = $c['foto_kosong']; }
		if(!$this->genc_multi()){ return $v; }
		if($unit === null){ $unit = $this->genc_current_unit(true); }
		$units = $this->genc_units();
		$label = isset($units[$unit]) ? $units[$unit]['label'] : (trim((string) $raw_unit) !== '' ? trim((string) $raw_unit) : 'Unit tidak dikenal');
		$v['multi']      = true;
		$v['unit']       = $unit;
		$v['unit_known'] = isset($units[$unit]);
		$v['unit_label'] = $label;
		$v['units']      = array();
		foreach($units as $slug => $u){ $v['units'][$slug] = $u['label']; }
		$v['machine']    = ($c['machine_title'] !== '' ? $c['machine_title'] : $c['title']);
		$v['title']      = $label;
		$v['name_lower'] = $label;
		$v['tabs']       = $this->genc_tabs($unit);
		if($this->genc_multi_profile()){
			// [GENC-24SEP26-FORKLIFT] nama mesin & jumlah unit report mengikuti profil unit aktif
			$p = $this->genc_profile();
			$v['machine'] = isset($units[$unit]) ? $units[$unit]['machine'] : ($c['machine_title'] !== '' ? $c['machine_title'] : $c['title']);
			if($c['unit_title_machine'] && isset($units[$unit])){ $v['title'] = $v['name_lower'] = $v['machine'] . ' ' . $label; }
			$v['units_report']  = count(checklist_machine_units($p));
			$v['machine_all']   = ($c['machine_title'] !== '' ? $c['machine_title'] : $c['title']);   // semua jenis ("Forklift")
			$v['unit_machines'] = array();
			foreach($units as $slug => $u){ $v['unit_machines'][$slug] = $u['machine']; }
		}
		return $v;
	}

	/** Konfigurasi view tanpa unit (perilaku sebelum 24 Sep sore, tidak diubah). */
	protected function genc_view_conf_base(){
		$c = $this->genc_c();
		return array(
			'page'              => $c['page'],
			'title'             => $c['title'],
			'name_lower'        => $c['name_lower'],
			'area'              => $c['area'],
			'keterangan_contoh' => $c['keterangan_contoh'],
			// [GENC-24SEP26-DUMPING] halaman ber-tab. Tanpa 'tabs' -> heading = title, tabs kosong (tampilan lama)
			'heading'           => ($c['group_title'] !== '' ? $c['group_title'] : $c['title']),
			'tabs'              => $this->genc_tabs(),
			'legacy'            => !empty($c['legacy_values']),
			'keterangan_max'    => $this->genc_col_maxlen('keterangan', 1000),
		) + ($this->genc_extra_fields() ? array('extra_fields' => $this->genc_extra_fields()) : array());   // [GENC-24SEP26-FORKLIFT]
	}

	/**
	 * [GENC-24SEP26-DUMPING] Tab yang boleh dilihat role ini + tanda tab aktif.
	 * Tab halaman ini selalu tampil (walau role cuma punya add, mis.).
	 */
	protected function genc_tabs($unit = null){
		$c = $this->genc_c();
		$out = array();
		foreach($c['tabs'] as $t){
			$active = ($t['page'] === $c['page']);
			// [GENC-24SEP26-AGV] tab per unit: aktif kalau halaman DAN unitnya sama
			if(isset($t['unit']) && $t['unit'] !== ''){ $active = $active && ($unit !== null && $t['unit'] === $unit); }
			if(!$active && !ACL::is_allowed($t['page'] . '/list')){ continue; }
			$t['active']  = $active;
			$t['can_add'] = ACL::is_allowed($t['page'] . '/add');
			if(!isset($t['sub'])){ $t['sub'] = ''; }
			$out[] = $t;
		}
		return $out;
	}

	// =====================================================================
	//  HALAMAN (route)
	// =====================================================================

	/**
	 * Daftar record + ringkasan bulan terpilih. Export PRINT/PDF/WORD
	 * dilempar ke report layout; CSV/EXCEL ditangani di sini (data mentah).
	 */
	function index($fieldname = null, $fieldvalue = null){
		$request = $this->request;
		$c = $this->genc_c();

		$checklist_fmt = checklist_export_format($request);
		if($checklist_fmt !== ''){
			return $this->genc_render_report($request, $checklist_fmt);   // [GENC-24SEP26-FORKLIFT] hook (isi sama dengan dulu)
		}

		$items   = $this->genc_items();
		$period  = $this->genc_period($request);
		$status  = isset($request->status) ? strtolower(trim($request->status)) : 'semua';
		if(!in_array($status, array('semua', 'tindakan', 'menunggu'), true)){ $status = 'semua'; }
		$q       = isset($request->q) ? trim((string) $request->q) : '';
		$page    = isset($request->hal) ? max(1, (int) $request->hal) : 1;

		$month_rows = $this->genc_month_rows($period['bulan'], $period['tahun']);

		// [GENC-24SEP26-AGV] multi-unit: daftar & ringkasan hanya unit aktif (K7)
		$unit = $this->genc_current_unit(true);
		$unit_other = array();
		if($this->genc_multi()){
			foreach($month_rows as $row){ if($row['_unit'] === self::UNIT_OTHER){ $unit_other[] = $row['_unit_raw']; } }
			$month_rows = $this->genc_filter_unit($month_rows, $unit);
		}

		$raw_fmt = isset($request->format) ? strtolower(trim($request->format)) : '';
		if($raw_fmt === 'csv' || $raw_fmt === 'excel'){
			return $this->genc_export_raw($month_rows, $items, $period, $raw_fmt);
		}

		// Filter status & pencarian dikerjakan di PHP: data 1 bulan cuma
		// puluhan baris, dan ringkasan di atas tetap butuh data sebulan penuh.
		$filtered = array();
		foreach($month_rows as $row){
			if($status === 'tindakan' && $this->genc_is_ok($row, $items)){ continue; }
			if($status === 'menunggu' && $this->genc_is_approved($row)){ continue; }
			if($q !== ''){
				$hay = strtolower($row['user_created'] . ' ' . $row['keterangan'] . ' ' . $row['user_approve'] . ' ' . $row['date_created']);
				if(strpos($hay, strtolower($q)) === false){ continue; }
			}
			$filtered[] = $row;
		}
		$total      = count($filtered);
		$page_count = max(1, (int) ceil($total / self::PER_PAGE));
		if($page > $page_count){ $page = $page_count; }
		$records    = array_slice($filtered, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

		$p = $c['page'];
		$data = array(
			'items'        => $items,
			'period'       => $period,
			'status'       => $status,
			'q'            => $q,
			'page'         => $page,
			'page_count'   => $page_count,
			'total'        => $total,
			'records'      => $records,
			'summary'      => $this->genc_summary($month_rows, $items, $period),
			'today'        => $this->genc_today_rows($unit),
			// [GENC-24SEP26-AGV] tab "lainnya" tidak punya unit -> tidak bisa isi dari sini
			'can_add'      => ACL::is_allowed("$p/add") && $unit !== self::UNIT_OTHER,
			'can_edit'     => ACL::is_allowed("$p/edit"),
			'can_view'     => ACL::is_allowed("$p/view"),
			'can_delete'   => ACL::is_allowed("$p/delete"),
			'can_approve'  => ACL::is_allowed("$p/approve"),
			'genc'         => $this->genc_view_conf($unit === '' ? null : $unit),
		);
		if($this->genc_multi()){ $data['unit_other'] = array_count_values($unit_other); }
		$this->view->page_title = $this->genc_multi() ? $data['genc']['title'] : $c['title'];
		return $this->render_view("_shared/genc/list.php", $data);
	}

	/** Detail satu record. */
	function view($rec_id = null){
		$c = $this->genc_c();
		$p = $c['page'];
		$record = $this->genc_find($rec_id);
		$data = array(
			'items'       => $this->genc_items(),
			'sections'    => $this->genc_sections(),
			'record'      => $record,
			'can_edit'    => ACL::is_allowed("$p/edit"),
			'can_delete'  => ACL::is_allowed("$p/delete"),
			'can_approve' => ACL::is_allowed("$p/approve"),
			'genc'        => $this->genc_record_view_conf($record),
		);
		$this->view->page_title = "Detail Checklist " . $data['genc']['title'];
		return $this->render_view("_shared/genc/view.php", $data);
	}

	/** [GENC-24SEP26-AGV] Konfigurasi view untuk halaman satu record: unit diambil dari record-nya. */
	protected function genc_record_view_conf($record){
		if(!$this->genc_multi()){ return $this->genc_view_conf(); }
		if(!$record){ return $this->genc_view_conf(); }
		return $this->genc_view_conf($record['_unit'], $record['_unit_raw']);
	}

	/** Form isi checklist baru. */
	function add($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$c      = $this->genc_c();
		$items  = $this->genc_items();
		$errors = array();
		$old    = array();
		// [GENC-24SEP26-AGV] multi-unit: unit WAJIB dari URL (?unit=<slug>, dibawa tab / tombol isi).
		// Tanpa unit yang sah -> halaman pilih unit, form tidak ditampilkan (K7).
		$unit = $this->genc_requested_unit();
		if($this->genc_multi() && $unit === ''){
			return $this->genc_render_unit_picker();
		}
		if($formdata){
			$old = $this->genc_read_post($formdata, $items);
			$errors = $this->genc_validate($old, $items);
			if(empty($errors)){
				$db = $this->GetModel();
				$modeldata = $this->genc_modeldata($old, $items);
				$modeldata['date_created'] = $this->genc_now_for('date_created');   // [GENC-24SEP26-AGV] kolom DATE -> tanggal saja (K7a)
				$modeldata[$c['user_field']] = USER_NAME;
				if($unit !== ''){
					$units = $this->genc_units();
					$modeldata[$this->genc_unit_field()] = $units[$unit]['label'];    // label kanonik (K7)
				}
				$modeldata = $this->genc_fill_required($modeldata);   // [GENC-24SEP26-FIX]
				$rec_id = $db->insert($this->tablename, $modeldata);
				if($rec_id){
					$this->genc_log('add', $rec_id, $modeldata);   // [GENC-24SEP26-SHELL] app_logs
					$this->set_flash_msg($this->genc_toast('Checklist tersimpan. Terima kasih!', 'success'), 'custom');
					return $this->redirect($c['page'] . ($unit !== '' ? '?unit=' . $unit : ''));
				}
				$errors['_db'] = 'Gagal menyimpan ke database: ' . $db->getLastError();
			}
		}
		$data = array(
			'mode'     => 'add',
			'items'    => $items,
			'sections' => $this->genc_sections(),
			'old'      => $old,
			'errors'   => $errors,
			'record'   => null,
			'today'    => $this->genc_today_rows($unit),
			'genc'     => $this->genc_view_conf($unit === '' ? null : $unit),
		);
		if($this->genc_extra_fields()){ $data['extra_last'] = $this->genc_extra_last($unit); }   // [GENC-24SEP26-FORKLIFT]
		$this->view->page_title = "Isi Checklist " . $data['genc']['title'];
		return $this->render_view("_shared/genc/add.php", $data);
	}

	/**
	 * [GENC-24SEP26-AGV] Halaman "pilih unit" - form isi dibuka tanpa unit (mis. link lama,
	 * bookmark). Tiap unit menampilkan status hari ini supaya operator tidak salah pilih.
	 */
	protected function genc_render_unit_picker(){
		$status = array();
		foreach($this->genc_today_rows(null) as $r){
			if(!isset($status[$r['_unit']])){ $status[$r['_unit']] = $r; }
		}
		$data = array(
			'mode'        => 'pick',
			'items'       => $this->genc_items(),
			'sections'    => array(),
			'old'         => array(),
			'errors'      => array(),
			'record'      => null,
			'today'       => array(),
			'unit_today'  => $status,
			'genc'        => $this->genc_view_conf(''),
		);
		$this->view->page_title = "Pilih Unit " . $data['genc']['machine'];
		return $this->render_view("_shared/genc/add.php", $data);
	}

	/** Ubah record. */
	function edit($rec_id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$c      = $this->genc_c();
		$record = $this->genc_find($rec_id);   // [GENC-24SEP26-FORKLIFT] dulu setelah genc_items(): record dulu, karena profil bisa per unit
		$items  = $this->genc_items();
		$errors = array();
		$old    = array();
		if($record && $formdata){
			$old = $this->genc_read_post($formdata, $items);
			$errors = $this->genc_validate($old, $items);
			if(empty($errors)){
				$db = $this->GetModel();
				$modeldata = $this->genc_modeldata($old, $items);
				$meta_update = array();
				if($this->genc_has_column('date_update')){ $modeldata['date_update'] = $this->genc_now_for('date_update'); $meta_update[] = 'date_update'; }
				if($this->genc_has_column($c['update_user_field'])){ $modeldata[$c['update_user_field']] = USER_NAME; $meta_update[] = $c['update_user_field']; }

				$changed = false;
				foreach($modeldata as $k => $v){
					if(in_array($k, $meta_update, true)){ continue; }
					if((string) $record[$k] !== (string) $v){ $changed = true; break; }
				}
				// [GENC-24SEP26-DUMPING] kolom catatan perubahan lama (dumping: date_perubahan, perubahan)
				if($changed){ $modeldata = $this->genc_edit_log($modeldata, $record, $items); }
				$approval_reset = false;
				if($changed && self::RESET_APPROVAL_ON_EDIT && $this->genc_is_approved($record)){
					// [GENC-24SEP26-FIX] NULL kalau kolom boleh NULL, '' kalau NOT NULL (tabel Gen A lama)
					$modeldata['approval']     = $this->genc_empty_value('approval');
					$modeldata['user_approve'] = $this->genc_empty_value('user_approve');
					if($this->genc_has_column('date_approve')){ $modeldata['date_approve'] = null; }
					$approval_reset = true;
				}

				$db->where($this->tablename . ".id", $record['id']);
				$bool = $db->update($this->tablename, $modeldata);
				if($bool){
					if($changed){ $this->genc_log('edit', $record['id'], array_merge($record, $modeldata)); }   // [GENC-24SEP26-SHELL]
					$msg = 'Perubahan tersimpan.';
					if($approval_reset){ $msg .= ' Approval dikosongkan karena data berubah &mdash; perlu di-approve ulang.'; }
					$this->set_flash_msg($this->genc_toast($msg, 'success'), 'custom');
					return $this->redirect($c['page'] . "/view/" . $record['id']);
				}
				$errors['_db'] = 'Gagal menyimpan ke database: ' . $db->getLastError();
			}
		}
		$data = array(
			'mode'     => 'edit',
			'items'    => $items,
			'sections' => $this->genc_sections(),
			'old'      => $old,
			'errors'   => $errors,
			'record'   => $record,
			'today'    => array(),
			'genc'     => $this->genc_record_view_conf($record),
		);
		$this->view->page_title = "Ubah Checklist " . $data['genc']['title'];
		return $this->render_view("_shared/genc/add.php", $data);
	}

	/** Hapus record (GET + csrf_token, sama dengan pola phpRAD). */
	function delete($rec_id = null){
		Csrf::cross_check();
		$c  = $this->genc_c();
		$db = $this->GetModel();
		$ids = array_filter(array_map('intval', explode(",", (string) $rec_id)));
		if(!empty($ids)){
			$gone = array();   // [GENC-24SEP26-SHELL] isi record dicatat sebelum dihapus
			foreach($ids as $i){ $r = $this->genc_find($i); if($r){ $gone[] = $r; } }
			$db->where($this->tablename . ".id", $ids, "in");
			if($db->delete($this->tablename)){
				foreach($gone as $r){ $this->genc_log('delete', $r['id'], $r); }
				$this->set_flash_msg($this->genc_toast('Checklist dihapus.', 'success'), 'custom');
			}
			else{
				$this->set_flash_msg($this->genc_toast('Gagal menghapus: ' . htmlspecialchars((string) $db->getLastError()), 'danger'), 'custom');
			}
		}
		return $this->redirect($c['page']);
	}

	/** Approve satu record (POST). Cuma record yang belum di-approve. */
	function approve($rec_id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$c = $this->genc_c();
		$record = $this->genc_find($rec_id);
		if(!$record){
			$this->set_flash_msg($this->genc_toast('Record tidak ditemukan.', 'danger'), 'custom');
			return $this->redirect($c['page']);
		}
		if($formdata === null){
			// dibuka lewat GET (mis. link diketik manual) -> ke halaman detail
			return $this->redirect($c['page'] . "/view/" . $record['id']);
		}
		$back = $c['page'] . ($this->genc_multi() ? '?unit=' . $record['_unit'] : '');   // [GENC-24SEP26-AGV]
		if($this->genc_is_approved($record)){
			$this->set_flash_msg($this->genc_toast('Record ini sudah di-approve sebelumnya.', 'info'), 'custom');
			return $this->redirect($back);
		}
		$db = $this->GetModel();
		$db->where($this->tablename . ".id", $record['id']);
		$upd = array(
			'approval'     => 'Approved',
			'user_approve' => USER_NAME,       // username, bukan id - dibaca report (Paraf Spv)
		);
		if($this->genc_has_column('date_approve')){ $upd['date_approve'] = $this->genc_now_for('date_approve'); }
		$bool = $db->update($this->tablename, $upd);
		if($bool){
			$this->genc_log('approve', $record['id'], $record);   // [GENC-24SEP26-SHELL]
			$this->set_flash_msg($this->genc_toast('Checklist ' . $this->genc_date_label($record['date_created']) . ' di-approve.', 'success'), 'custom');
		}
		else{
			$this->set_flash_msg($this->genc_toast('Gagal approve: ' . htmlspecialchars((string) $db->getLastError()), 'danger'), 'custom');
		}
		return $this->redirect($back);
	}

	// =====================================================================
	//  DATA
	// =====================================================================

	/** Profil mesin (sumber tunggal isi checklist). [GENC-24SEP26-FORKLIFT] per unit kalau 'unit_profiles'. */
	protected function genc_profile(){
		$c = $this->genc_c();
		$name = $this->genc_profile_name();
		if(!isset($this->genc_cache['profiles'][$name])){
			$this->genc_cache['profiles'][$name] = checklist_load_machine($name, $c['overrides']);
		}
		return $this->genc_cache['profiles'][$name];
	}

	// ---------------------------------------------------------------------
	//  [GENC-24SEP26-FORKLIFT] BEBERAPA PROFIL DALAM SATU TABEL ('unit_profiles')
	//  Unit aktif menentukan profil: dari record (view/edit/approve), selain itu
	//  dari ?unit= (daftar, isi, export). Mesin tanpa 'unit_profiles' tidak berubah.
	// ---------------------------------------------------------------------

	protected function genc_multi_profile(){ $c = $this->genc_c(); return !empty($c['unit_profiles']); }

	/** Nama profil yang berlaku sekarang. */
	protected function genc_profile_name(){
		$c = $this->genc_c();
		if(!$this->genc_multi_profile()){ return $c['profile']; }
		$u = array_key_exists('ctx_unit', $this->genc_cache) ? $this->genc_cache['ctx_unit'] : $this->genc_current_unit(true);
		$units = $this->genc_units();
		return isset($units[$u]) ? $units[$u]['profile'] : $c['profile'];
	}

	/** Kunci unit aktif (dipakai genc_find: record menentukan profilnya sendiri). */
	protected function genc_set_unit_ctx($slug){ $this->genc_cache['ctx_unit'] = $slug; }

	/** Kolom item SEMUA profil (SELECT) - supaya record jenis apa pun terbaca lengkap. */
	protected function genc_all_item_columns(){
		$c = $this->genc_c();
		$cols = array();
		foreach($c['unit_profiles'] as $name){
			$p = checklist_load_machine($name, $c['overrides']);
			foreach($p['sections'] as $s){ foreach($s['items'] as $it){ $cols[$it['db']] = true; } }
		}
		return array_keys($cols);
	}

	/**
	 * [GENC-24SEP26-FORKLIFT] Report PDF/Print/Word. Mesin boleh menimpa (forklift: unit dikunci
	 * persis seperti tab lama). Bawaan = perilaku sebelum hook ini ada.
	 */
	protected function genc_render_report($request, $fmt){
		$c = $this->genc_c();
		return checklist_render_report($this->GetModel(), $this->genc_profile_name(), $request, $fmt, $c['overrides']);
	}

	// ---------------------------------------------------------------------
	//  [GENC-24SEP26-FORKLIFT] ISIAN TAMBAHAN ('extra_fields', mis. jam kerja forklift)
	// ---------------------------------------------------------------------

	/** Isian tambahan yang kolomnya ADA di tabel. */
	protected function genc_extra_fields(){
		if(!isset($this->genc_cache['extra'])){
			$c = $this->genc_c();
			$out = array();
			foreach($c['extra_fields'] as $f){
				if(empty($f['db']) || !$this->genc_has_column($f['db'])){ continue; }
				$out[] = array_merge(array('label' => $f['db'], 'type' => 'text', 'required' => false, 'suffix' => '',
					'hint' => '', 'placeholder' => '', 'last' => false, 'list' => false), $f);
			}
			$this->genc_cache['extra'] = $out;
		}
		return $this->genc_cache['extra'];
	}

	/** Isian terakhir tiap isian tambahan untuk unit ini: [db => array('value', 'date')]. */
	protected function genc_extra_last($unit){
		$want = array();
		foreach($this->genc_extra_fields() as $f){ if($f['last']){ $want[] = $f['db']; } }
		if(empty($want)){ return array(); }
		$t  = $this->tablename;
		$db = $this->GetModel();
		$fields = array_merge(array('id', 'date_created'), $this->genc_multi() ? array($this->genc_unit_field()) : array(), $want);
		$db->orderBy("$t.date_created", "DESC");
		$db->orderBy("$t.id", "DESC");
		$rows = $db->get($t, array(0, 200), $fields);
		$out = array();
		foreach((is_array($rows) ? $rows : array()) as $r){
			if($this->genc_multi() && $this->genc_row_unit($r) !== $unit){ continue; }
			foreach($want as $k){
				if(!isset($out[$k]) && trim((string) $r[$k]) !== ''){ $out[$k] = array('value' => trim((string) $r[$k]), 'date' => $r['date_created']); }
			}
			if(count($out) === count($want)){ break; }
		}
		return $out;
	}

	/** Nilai isian tambahan dari POST (angka: koma -> titik, spasi dibuang). */
	protected function genc_extra_read($formdata, $f){
		$v = isset($formdata[$f['db']]) ? trim((string) $formdata[$f['db']]) : '';
		if($f['type'] === 'number'){ $v = str_replace(array(' ', ','), array('', '.'), $v); }
		return $this->genc_fit_text($f['db'], strip_tags($v));
	}

	/** Nilai yang ditulis ke DB: kolom angka bulat -> dibulatkan (MySQL strict menolak "12.5" di INT). */
	protected function genc_extra_db_value($f, $v){
		if($f['type'] !== 'number' || $v === ''){ return $v; }
		$cols = $this->genc_columns();
		$k = strtolower($f['db']);
		if(isset($cols[$k]) && preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint)/', $cols[$k]['type'])){ return (string) (int) round((float) $v); }
		return $v;
	}

	/** Section yang punya item saja (section kosong tidak tampil di form, tetap di report). */
	protected function genc_sections(){
		$out = array();
		foreach($this->genc_profile()['sections'] as $s){
			if(!empty($s['items'])){ $out[] = $s; }
		}
		return $out;
	}

	/** Semua item, datar, urut nomor. */
	protected function genc_items(){
		$out = array();
		foreach($this->genc_sections() as $s){
			foreach($s['items'] as $it){
				$it['section'] = isset($s['short']) ? $s['short'] : $s['title'];
				$out[] = $it;
			}
		}
		return $out;
	}

	/**
	 * Apakah kolom ada di tabel. Dipakai untuk kolom opsional Gen C
	 * (date_approve, date_update, user_update) supaya halaman tidak error
	 * kalau SQL ALTER belum dijalankan. 1 query per request, di-cache.
	 */
	protected function genc_has_column($col){
		$cols = $this->genc_columns();
		return isset($cols[strtolower($col)]);
	}

	/**
	 * [GENC-24SEP26-FIX] Struktur kolom tabel (SHOW COLUMNS), di-cache per request.
	 * @return array [nama_kecil => array('field','type','null'(bool),'default','extra')]
	 */
	protected function genc_columns(){
		if(!isset($this->genc_cache['columns'])){
			$cols = array();
			$db = $this->GetModel();
			// [GENC-24SEP26-DUMPING] FULL -> ikut Collation (tabel lama bisa latin1, lihat genc_fit_text)
			$rows = $db->rawQuery("SHOW FULL COLUMNS FROM `" . str_replace('`', '', $this->tablename) . "`");
			if(is_array($rows)){
				foreach($rows as $r){
					$cols[strtolower($r['Field'])] = array(
						'field'   => $r['Field'],
						'type'    => strtolower((string) $r['Type']),
						'null'    => strtoupper((string) $r['Null']) === 'YES',
						'default' => $r['Default'],
						'extra'   => strtolower((string) $r['Extra']),
						'collation' => strtolower(isset($r['Collation']) ? (string) $r['Collation'] : ''),
					);
				}
			}
			$this->genc_cache['columns'] = $cols;
		}
		return $this->genc_cache['columns'];
	}

	/**
	 * [GENC-24SEP26-FIX] Nilai "kosong" yang diterima kolom ini: NULL kalau kolom
	 * boleh NULL, selain itu nilai kosong sesuai tipe ('' / 0). Dipakai saat
	 * approval dikosongkan (edit) - tabel lama Gen A punya kolom approval
	 * NOT NULL, dan halaman approval lama memakai '' sebagai "belum di-approve".
	 */
	protected function genc_empty_value($col){
		$cols = $this->genc_columns();
		$k = strtolower($col);
		if(!isset($cols[$k]) || $cols[$k]['null']){ return null; }
		return $this->genc_blank_for_type($cols[$k]['type']);
	}

	/**
	 * [GENC-24SEP26-DUMPING] Panjang maksimum kolom teks (VARCHAR(n) -> n, TEXT -> $default).
	 * Tabel Gen B punya `keterangan` VARCHAR(255): tanpa batas ini, keterangan
	 * panjang ditolak MySQL mode strict ("Data too long").
	 */
	protected function genc_col_maxlen($col, $default = 1000){
		$cols = $this->genc_columns();
		$k = strtolower($col);
		if(isset($cols[$k]) && preg_match('/^(var)?char\((\d+)\)/', $cols[$k]['type'], $m)){ return min($default, (int) $m[2]); }
		return $default;
	}

	/**
	 * [GENC-24SEP26-DUMPING] Teks aman untuk kolom: dipotong sesuai panjang kolom, dan
	 * kalau kolomnya latin1 (tabel lama), huruf di luar latin1 (emoji, tanda kutip HP
	 * tertentu) diganti padanannya -> MySQL strict tidak menolak ("Incorrect string value").
	 */
	protected function genc_fit_text($col, $text){
		$text = (string) $text;
		$cols = $this->genc_columns();
		$k = strtolower($col);
		if(isset($cols[$k]) && strpos($cols[$k]['collation'], 'latin1') === 0 && function_exists('iconv')){
			$t = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
			if($t !== false){ $text = iconv('Windows-1252', 'UTF-8', $t); }
		}
		// [GENC-24SEP26-FORKLIFT] koneksi DB "utf8" (config.php DB_CHARSET) = maks 3 byte per huruf:
		// emoji HP (4 byte, mis. 😀) ditolak MySQL strict ("Incorrect string value") walau kolomnya
		// utf8mb4 -> diganti '?' (sama dengan perlakuan tabel latin1). ✔️/❌ (3 byte) tidak terkena.
		if(!defined('DB_CHARSET') || strtolower((string) DB_CHARSET) !== 'utf8mb4'){
			$text = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '?', $text);
		}
		$max = $this->genc_col_maxlen($col, 1000);
		return function_exists('mb_substr') ? mb_substr($text, 0, $max, 'UTF-8') : substr($text, 0, $max);
	}

	protected function genc_blank_for_type($type){
		if(preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint|decimal|numeric|float|double|real|bit|year)/', $type)){ return 0; }
		if(strpos($type, 'datetime') === 0 || strpos($type, 'timestamp') === 0){ return datetime_now(); }
		if(strpos($type, 'date') === 0){ return date('Y-m-d'); }
		if(strpos($type, 'time') === 0){ return date('H:i:s'); }
		return '';
	}

	/**
	 * [GENC-24SEP26-FIX] Lengkapi data INSERT untuk kolom yang NOT NULL tanpa
	 * nilai bawaan (mis. `approval` di mesin_geprek kantor). Tanpa ini MySQL
	 * mode strict menolak insert: "Field 'approval' doesn't have a default value".
	 * Kolom yang sudah diisi tidak disentuh. Trigger BEFORE INSERT tetap bisa
	 * menimpa nilai ini (mis. approval_mesin_geprek -> 'Approved').
	 */
	protected function genc_fill_required($modeldata){
		$given = array();
		foreach($modeldata as $k => $v){ $given[strtolower($k)] = true; }
		foreach($this->genc_columns() as $k => $c){
			if(isset($given[$k]) || $c['null'] || $c['default'] !== null || strpos($c['extra'], 'auto_increment') !== false || strpos($c['extra'], 'generated') !== false){ continue; }
			$modeldata[$c['field']] = $this->genc_blank_for_type($c['type']);
		}
		return $modeldata;
	}

	// ---------------------------------------------------------------------
	//  [GENC-24SEP26-AGV] MULTI-UNIT (K7). Aktif otomatis kalau profil punya
	//  'unit_field' + 'units'. Pencocokan unit lewat alias profil
	//  (checklist_norm_unit: abaikan spasi & huruf besar) - SAMA dengan report.
	// ---------------------------------------------------------------------

	/** @return array [slug => array('label', 'norms')] - kosong untuk mesin 1 unit. */
	protected function genc_units(){
		if(!isset($this->genc_cache['units'])){
			if($this->genc_multi_profile()){
				// [GENC-24SEP26-FORKLIFT] gabungan unit semua profil, tiap unit tahu profil & nama mesinnya
				$c = $this->genc_c();
				$all = array();
				foreach($c['unit_profiles'] as $name){
					$p = checklist_load_machine($name, $c['overrides']);
					foreach(checklist_machine_units($p) as $slug => $u){
						$u['profile'] = $name;
						$u['machine'] = (string) $p['machine_code'];
						$all[$slug] = $u;
					}
				}
				$this->genc_cache['units'] = $all;
			}
			else{
				$p = $this->genc_profile();
				$this->genc_cache['units'] = (!empty($p['unit_field']) && !empty($p['units'])) ? checklist_machine_units($p) : array();
			}
		}
		return $this->genc_cache['units'];
	}

	protected function genc_multi(){ return !empty($this->genc_units()); }

	protected function genc_unit_field(){ $p = $this->genc_profile(); return (string) $p['unit_field']; }

	/** Slug unit dari ?unit= kalau SAH, selain itu ''. (Form isi: tanpa unit sah -> pilih unit.) */
	protected function genc_requested_unit(){
		$units = $this->genc_units();
		if(empty($units)){ return ''; }
		$u = isset($this->request->unit) ? strtolower(trim((string) $this->request->unit)) : '';
		return isset($units[$u]) ? $u : '';
	}

	/** Unit aktif halaman daftar: ?unit= sah, 'lainnya' (kalau $allow_other), atau unit pertama. '' = mesin 1 unit. */
	protected function genc_current_unit($allow_other = false){
		$units = $this->genc_units();
		if(empty($units)){ return ''; }
		$u = isset($this->request->unit) ? strtolower(trim((string) $this->request->unit)) : '';
		if(isset($units[$u])){ return $u; }
		if($allow_other && $u === self::UNIT_OTHER){ return $u; }
		reset($units);
		return key($units);
	}

	/** Slug unit sebuah record ('lainnya' kalau tidak dikenal). */
	protected function genc_row_unit($row){
		$f = $this->genc_unit_field();
		$n = checklist_norm_unit(isset($row[$f]) ? $row[$f] : '');
		if($n !== ''){
			foreach($this->genc_units() as $slug => $u){
				if(in_array($n, $u['norms'], true)){ return $slug; }
			}
		}
		return self::UNIT_OTHER;
	}

	/** Saring baris per unit. $unit '' / null = tidak disaring. */
	protected function genc_filter_unit($rows, $unit){
		if(!$this->genc_multi() || $unit === '' || $unit === null){ return $rows; }
		$out = array();
		foreach($rows as $r){ if($r['_unit'] === $unit){ $out[] = $r; } }
		return $out;
	}

	/**
	 * [GENC-24SEP26-AGV] Waktu "sekarang" sesuai tipe kolom: DATE -> 'Y-m-d' (K7a, forklift/pallet
	 * mover/AGV lama bisa DATE), selain itu datetime_now() seperti sebelumnya.
	 */
	protected function genc_now_for($col){
		$cols = $this->genc_columns();
		$k = strtolower($col);
		if(isset($cols[$k]) && $cols[$k]['type'] === 'date'){ return date('Y-m-d'); }
		return datetime_now();
	}

	/** Bulan & tahun dari URL, default bulan berjalan. */
	protected function genc_period($request){
		$now_b = (int) date('n');
		$now_y = (int) date('Y');
		$b = !empty($request->bulan) ? (int) $request->bulan : $now_b;
		$y = !empty($request->tahun) ? (int) $request->tahun : $now_y;
		if($b < 1 || $b > 12){ $b = $now_b; }
		if($y < 2000 || $y > 2100){ $y = $now_y; }
		$nama = checklist_nama_bulan();
		$prev = ($b === 1)  ? array(12, $y - 1) : array($b - 1, $y);
		$next = ($b === 12) ? array(1,  $y + 1) : array($b + 1, $y);
		return array(
			'bulan'      => $b,
			'tahun'      => $y,
			'label'      => $nama[$b] . ' ' . $y,
			'day_count'  => (int) date('t', mktime(0, 0, 0, $b, 1, $y)),
			'is_current' => ($b === $now_b && $y === $now_y),
			'is_future'  => ($y > $now_y || ($y === $now_y && $b > $now_b)),
			'prev'       => $prev,
			'next'       => $next,
		);
	}

	/** Kolom SELECT: meta + kolom item dari profil + kolom Gen C yang ADA di tabel. */
	protected function genc_fields(){
		$c = $this->genc_c();
		$fields = array('id', 'date_created', $c['user_field']);
		if($this->genc_multi()){ $fields[] = $this->genc_unit_field(); }   // [GENC-24SEP26-AGV]
		if($this->genc_multi_profile()){ $fields = array_merge($fields, $this->genc_all_item_columns()); }   // [GENC-24SEP26-FORKLIFT]
		else{ foreach($this->genc_items() as $it){ $fields[] = $it['db']; } }
		foreach($this->genc_extra_fields() as $f){ $fields[] = $f['db']; }
		$fields = array_merge($fields, array('keterangan', 'approval', 'user_approve'));
		// [GENC-24SEP26-DUMPING] `kondisi` ikut opsional: tabel Gen B belum punya sampai SQL ALTER dijalankan (K9)
		foreach(array('kondisi', 'date_approve', 'date_update', $c['update_user_field']) as $opt){
			if($this->genc_has_column($opt)){ $fields[] = $opt; }
		}
		return $fields;
	}

	/**
	 * Samakan bentuk baris untuk view: kolom pelaksana selalu tersedia sebagai
	 * 'user_created', kolom pengubah sebagai 'user_update', kolom opsional
	 * yang belum ada diisi null.
	 */
	protected function genc_normalize_row($row){
		if(!is_array($row)){ return $row; }
		$c = $this->genc_c();
		if($c['user_field'] !== 'user_created'){ $row['user_created'] = isset($row[$c['user_field']]) ? $row[$c['user_field']] : null; }
		if($c['update_user_field'] !== 'user_update'){ $row['user_update'] = isset($row[$c['update_user_field']]) ? $row[$c['update_user_field']] : null; }
		foreach(array('date_approve', 'date_update', 'user_update', 'kondisi', 'keterangan', 'approval', 'user_approve') as $k){
			if(!array_key_exists($k, $row)){ $row[$k] = null; }
		}
		if($this->genc_multi()){   // [GENC-24SEP26-AGV]
			$f = $this->genc_unit_field();
			$units = $this->genc_units();
			$row['_unit']     = $this->genc_row_unit($row);
			$row['_unit_raw'] = isset($row[$f]) ? trim((string) $row[$f]) : '';
			$row['_unit_label'] = isset($units[$row['_unit']]) ? $units[$row['_unit']]['label'] : ($row['_unit_raw'] !== '' ? $row['_unit_raw'] : '(kosong)');
		}
		return $this->genc_eval_row($row);
	}

	// ---------------------------------------------------------------------
	//  [GENC-24SEP26-DUMPING] PENILAIAN BARIS (satu tempat untuk daftar,
	//  ringkasan, detail, dialog approve). Nilai item di $row TETAP MENTAH
	//  (dipakai form ubah & export data mentah); hasil penilaian di kunci _*:
	//    _vals     [kolom => 'OK'|'NOK'|'PR'|teks lama apa adanya]  (untuk strip & badge)
	//    _legacy   [kolom => teks mentah] - isian yang bukan OK/NOK/PR (data Gen B lama)
	//    _bad      daftar part yang perlu tindakan ('Nama (perawatan)'); _bad_parts tanpa keterangan
	//    _unknown  jumlah isian lama yang maknanya tidak dikenali (tidak dihitung temuan)
	//    _ok       true kalau tidak ada temuan
	//    _approved status approval
	//  Mesin tanpa 'legacy_values' (conveyor, geprek): aturannya SAMA PERSIS
	//  dengan sebelumnya (semua yang bukan 'OK' = temuan).
	// ---------------------------------------------------------------------

	/** Teks isian dinormalisasi: trim, spasi ganda dirapatkan, titik di ujung dibuang, huruf besar. */
	protected function genc_norm_text($v){
		$v = preg_replace('/\s+/u', ' ', trim((string) $v));
		$v = rtrim($v, " .");
		return function_exists('mb_strtoupper') ? mb_strtoupper($v, 'UTF-8') : strtoupper($v);
	}

	/** Nilai item -> 'OK'/'NOK'/'PR', atau null kalau tidak dikenali. */
	protected function genc_map_value($raw){
		$n = $this->genc_norm_text($raw);
		if(in_array($n, $this->genc_allowed_values(), true)){ return $n; }
		$c = $this->genc_c();
		if(!empty($c['legacy_values']) && isset($c['legacy_values'][$n])){ return $c['legacy_values'][$n]; }
		return null;
	}

	protected function genc_eval_row($row){
		$c = $this->genc_c();
		$legacy_mode = !empty($c['legacy_values']);
		$vals = array(); $legacy = array(); $bad = array(); $bad_parts = array(); $unknown = 0; $has_items = false;
		foreach($this->genc_items() as $it){
			if(!array_key_exists($it['db'], $row)){ continue; }
			$has_items = true;
			$raw = (string) $row[$it['db']];
			$std = strtoupper(trim($raw));
			if(!$legacy_mode){
				// perilaku lama conveyor/geprek, tidak diubah
				$vals[$it['db']] = $raw;
				if($std !== 'OK'){ $bad[] = $it['part'] . ($std === 'PR' ? ' (perawatan)' : ''); $bad_parts[] = $it['part']; }
				continue;
			}
			$m = $this->genc_map_value($raw);
			if(!in_array($std, $this->genc_allowed_values(), true)){ $legacy[$it['db']] = $raw; }
			if($m === null){
				$vals[$it['db']] = trim($raw);
				$unknown++;
				continue;
			}
			$vals[$it['db']] = $m;
			if($m !== 'OK'){ $bad[] = $it['part'] . ($m === 'PR' ? ' (perawatan)' : ''); $bad_parts[] = $it['part']; }
		}
		if($has_items){
			$row['_vals']    = $vals;
			$row['_legacy']  = $legacy;
			$row['_bad']     = $bad;
			$row['_bad_parts'] = $bad_parts;
			$row['_unknown'] = $unknown;
			$row['_ok']      = empty($bad);
		}
		$row['_approved'] = $this->genc_is_approved($row);
		return $row;
	}

	/** Semua record 1 bulan, terbaru dulu. */
	protected function genc_month_rows($bulan, $tahun){
		$t  = $this->tablename;
		$db = $this->GetModel();
		$fields = $this->genc_fields();
		$db->where("MONTH($t.date_created) = ?", array($bulan));
		$db->where("YEAR($t.date_created) = ?", array($tahun));
		$db->orderBy("$t.date_created", "DESC");
		$db->orderBy("$t.id", "DESC");
		// array(OFFSET, LIMIT) - offset WAJIB 0 (lihat handover 22 Sep §6.2)
		$rows = $db->get($t, array(0, 1000), $fields);
		if($db->getLastError()){ $this->set_page_error(); }
		return is_array($rows) ? array_map(array($this, 'genc_normalize_row'), $rows) : array();
	}

	/** Record hari ini (untuk tombol "Isi checklist hari ini" & peringatan isi dobel). [GENC-24SEP26-AGV] per unit */
	protected function genc_today_rows($unit = ''){
		$c  = $this->genc_c();
		$t  = $this->tablename;
		$db = $this->GetModel();
		$db->where("DATE($t.date_created) = ?", array(date('Y-m-d')));
		$db->orderBy("$t.date_created", "DESC");
		// [GENC-24SEP26-DUMPING] kolom lengkap (bukan cuma kondisi): isian hari ini dari form lama
		// Gen B tidak punya `kondisi` -> status "Semua baik" dinilai dari item (_ok)
		$rows = $db->get($t, array(0, 50), $this->genc_fields());
		$rows = is_array($rows) ? array_map(array($this, 'genc_normalize_row'), $rows) : array();
		return $this->genc_filter_unit($rows, $unit);
	}

	protected function genc_find($rec_id){
		$id = (int) $rec_id;
		if($id <= 0){ return null; }
		$db = $this->GetModel();
		$fields = $this->genc_fields();
		$db->where($this->tablename . ".id", $id);
		$row = $db->getOne($this->tablename, $fields);
		if($row && $this->genc_multi_profile()){
			// [GENC-24SEP26-FORKLIFT] record menentukan profilnya (electric / diesel) untuk sisa request ini
			$slug = $this->genc_row_unit($row);
			if($slug !== self::UNIT_OTHER){ $this->genc_set_unit_ctx($slug); }
		}
		return $row ? $this->genc_normalize_row($row) : null;
	}

	protected function genc_is_approved($row){
		if(array_key_exists('_approved', $row)){ return $row['_approved']; }
		$ok = isset($row['approval']) && trim((string) $row['approval']) !== '' && stripos($row['approval'], 'not') === false;
		// [GENC-24SEP26-DUMPING] kolom approval Gen B dulu DIKETIK operator ('-', 'belum', ...)
		$c = $this->genc_c();
		if($ok && !empty($c['approval_pending_values'])){
			$n = $this->genc_norm_text($row['approval']);
			// isi tanpa huruf sama sekali ('.', '11', '-') = bukan status approval -> belum (data kantor 24 Sep)
			if(in_array($n, $c['approval_pending_values'], true) || !preg_match('/\pL/u', $n)){ $ok = false; }
		}
		return $ok;
	}

	protected function genc_is_ok($row, $items){
		if(isset($row['_ok'])){ return $row['_ok']; }   // [GENC-24SEP26-DUMPING] sudah dinilai genc_eval_row()
		foreach($items as $it){
			if(strtoupper(trim((string) $row[$it['db']])) !== 'OK'){ return false; }
		}
		return true;
	}

	/** Ringkasan bulan: kepatuhan isi, temuan, menunggu approval, kalender. */
	protected function genc_summary($rows, $items, $period){
		$days = array();                 // [tgl] => 'ok' | 'issue'
		$issues = 0; $pending = 0; $item_issue = array();
		foreach($rows as $r){
			$d = (int) date('j', strtotime($r['date_created']));
			$ok = $this->genc_is_ok($r, $items);
			if(!$ok){
				$issues++;
				foreach($r['_bad_parts'] as $part){   // [GENC-24SEP26-DUMPING] dari genc_eval_row()
					$item_issue[$part] = (isset($item_issue[$part]) ? $item_issue[$part] : 0) + 1;
				}
			}
			if(!$this->genc_is_approved($r)){ $pending++; }
			if(!isset($days[$d]) || !$ok){ $days[$d] = $ok ? 'ok' : 'issue'; }
		}
		arsort($item_issue);

		if($period['is_future'])      { $elapsed = 0; }
		elseif($period['is_current']) { $elapsed = (int) date('j'); }
		else                          { $elapsed = $period['day_count']; }

		$filled = 0;
		foreach($days as $d => $v){ if($d <= max($elapsed, 0)){ $filled++; } }

		return array(
			'days'       => $days,
			'elapsed'    => $elapsed,
			'filled'     => $filled,
			'percent'    => $elapsed > 0 ? (int) round($filled * 100 / $elapsed) : 0,
			'issues'     => $issues,
			'pending'    => $pending,
			'item_issue' => array_slice($item_issue, 0, 3, true),
			'records'    => count($rows),
		);
	}

	// =====================================================================
	//  FORM
	// =====================================================================

	protected function genc_allowed_values(){
		return explode(',', self::ITEM_VALUES);
	}

	/** Ambil nilai dari POST - hanya kolom yang dikenal, nilai item di-whitelist. */
	protected function genc_read_post($formdata, $items){
		$allowed = $this->genc_allowed_values();
		$out = array();
		foreach($items as $it){
			$v = isset($formdata[$it['db']]) ? strtoupper(trim((string) $formdata[$it['db']])) : '';
			$out[$it['db']] = in_array($v, $allowed, true) ? $v : '';
		}
		foreach($this->genc_extra_fields() as $f){ $out[$f['db']] = $this->genc_extra_read($formdata, $f); }   // [GENC-24SEP26-FORKLIFT]
		$ket = isset($formdata['keterangan']) ? (string) $formdata['keterangan'] : '';
		$ket = trim(strip_tags(html_entity_decode($ket, ENT_QUOTES, 'UTF-8')));
		$out['keterangan'] = $this->genc_fit_text('keterangan', $ket);   // [GENC-24SEP26-DUMPING] maks 1000 / panjang kolom
		return $out;
	}

	/** @return array [kolom => pesan]. Kosong = valid. */
	protected function genc_validate($old, $items){
		$errors = array();
		foreach($this->genc_extra_fields() as $f){   // [GENC-24SEP26-FORKLIFT] di atas item, sama dengan urutan di form
			$v = isset($old[$f['db']]) ? $old[$f['db']] : '';
			if($v === ''){
				if($f['required']){ $errors[$f['db']] = 'Isi ' . htmlspecialchars(strtolower($f['label'])) . '.'; }
			}
			elseif($f['type'] === 'number' && !preg_match('/^\d{1,9}(\.\d{1,2})?$/', $v)){
				$errors[$f['db']] = htmlspecialchars($f['label']) . ' diisi angka saja (contoh: 1250 atau 1250.5).';
			}
		}
		$has_issue = false;
		foreach($items as $it){
			if($old[$it['db']] === ''){
				$errors[$it['db']] = 'Pilih hasil pengecekan no. ' . $it['no'] . ' &ldquo;' . htmlspecialchars($it['part']) . '&rdquo;.';
			}
			elseif($old[$it['db']] !== 'OK'){
				$has_issue = true;
			}
		}
		if($has_issue && $old['keterangan'] === ''){
			$errors['keterangan'] = 'Ada item Tidak Baik / Perawatan &mdash; tulis keterangannya (apa yang ditemukan, sudah dilaporkan ke siapa).';
		}
		return $errors;
	}

	protected function genc_modeldata($old, $items){
		$m = array();
		$all_ok = true;
		foreach($items as $it){
			$m[$it['db']] = $old[$it['db']];
			if($old[$it['db']] !== 'OK'){ $all_ok = false; }
		}
		// Nilai sama dengan Menu::$kondisi -> trigger auto-approve & halaman NOK tetap cocok.
		// [GENC-24SEP26-DUMPING] ditulis hanya kalau kolomnya ada (Gen B: setelah SQL ALTER)
		if($this->genc_has_column('kondisi')){ $m['kondisi'] = $all_ok ? '✔️' : '❌'; }
		foreach($this->genc_extra_fields() as $f){ $m[$f['db']] = $this->genc_extra_db_value($f, $old[$f['db']]); }   // [GENC-24SEP26-FORKLIFT]
		$m['keterangan'] = $old['keterangan'];
		return $m;
	}

	/**
	 * [GENC-24SEP26-DUMPING] Isi kolom catatan perubahan lama (conf 'edit_log'),
	 * mis. dumping: date_perubahan = sekarang, perubahan = "3. Body Mesin Dumping: Baik -> Tidak baik; ...".
	 * Kolom yang tidak ada di tabel dilewati. Teks dipotong sesuai panjang kolom.
	 */
	protected function genc_edit_log($modeldata, $record, $items){
		$c = $this->genc_c();
		$log = $c['edit_log'];
		if(!empty($log['date']) && $this->genc_has_column($log['date'])){ $modeldata[$log['date']] = $this->genc_now_for($log['date']); }
		if(!empty($log['note']) && $this->genc_has_column($log['note'])){
			$label = array('OK' => 'Baik', 'NOK' => 'Tidak baik', 'PR' => 'Perawatan');
			$parts = array();
			foreach($items as $it){
				$from = (string) $record[$it['db']]; $to = (string) $modeldata[$it['db']];
				if($from === $to){ continue; }
				$f = strtoupper(trim($from));
				$parts[] = $it['no'] . '. ' . $it['part'] . ': ' . (isset($label[$f]) ? $label[$f] : ($from === '' ? '-' : $from)) . ' -> ' . (isset($label[$to]) ? $label[$to] : $to);
			}
			foreach($this->genc_extra_fields() as $f){   // [GENC-24SEP26-FORKLIFT]
				if((string) $record[$f['db']] !== (string) $modeldata[$f['db']]){ $parts[] = $f['label'] . ': ' . ((string) $record[$f['db']] === '' ? '-' : $record[$f['db']]) . ' -> ' . $modeldata[$f['db']]; }
			}
			if((string) $record['keterangan'] !== (string) $modeldata['keterangan']){ $parts[] = 'keterangan diubah'; }
			$modeldata[$log['note']] = $this->genc_fit_text($log['note'], 'Diubah ' . USER_NAME . ': ' . implode('; ', $parts));
		}
		return $modeldata;
	}

	// =====================================================================
	//  EXPORT DATA MENTAH (CSV / EXCEL)
	// =====================================================================

	/**
	 * CSV & "Excel" = data mentah bulan terpilih. Excel dikirim sebagai CSV
	 * ber-BOM UTF-8 dengan pemisah titik koma, yang dibuka Excel (setting
	 * regional Indonesia) langsung per kolom, tanpa library tambahan.
	 */
	protected function genc_export_raw($rows, $items, $period, $fmt){
		$c = $this->genc_c();
		$filename = sprintf('%04d-%02d-Checklist-AM-%s-data', $period['tahun'], $period['bulan'], $c['file_slug']);
		$multi = $this->genc_multi();   // [GENC-24SEP26-AGV] kolom Unit + nama file per unit
		if($multi){
			$u = $this->genc_current_unit(true);
			$units = $this->genc_units();
			$filename = sprintf('%04d-%02d-Checklist-AM-%s-%s-data', $period['tahun'], $period['bulan'], $c['file_slug'],
				preg_replace('/[^A-Za-z0-9]+/', '-', isset($units[$u]) ? $units[$u]['label'] : 'Unit-Lainnya'));
		}
		$sep = ($fmt === 'excel') ? ';' : ',';
		if(!headers_sent()){
			header('Content-Type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
		}
		$out = fopen('php://output', 'w');
		fwrite($out, "\xEF\xBB\xBF");
		$head = $multi ? array('ID', 'Tanggal', 'Unit', 'Pelaksana') : array('ID', 'Tanggal', 'Pelaksana');
		$extra = $this->genc_extra_fields();   // [GENC-24SEP26-FORKLIFT]
		foreach($extra as $f){ $head[] = $f['label']; }
		foreach($items as $it){ $head[] = $it['no'] . '. ' . $it['part']; }
		$head = array_merge($head, array('Kondisi', 'Keterangan', 'Approval', 'Di-approve oleh', 'Waktu approve'));
		fputcsv($out, $head, $sep);
		foreach(array_reverse($rows) as $r){
			$line = $multi ? array($r['id'], $r['date_created'], $r['_unit_raw'], $r['user_created']) : array($r['id'], $r['date_created'], $r['user_created']);
			foreach($extra as $f){ $line[] = $r[$f['db']]; }
			foreach($items as $it){ $line[] = $r[$it['db']]; }
			$line = array_merge($line, array(
				$this->genc_is_ok($r, $items) ? 'Baik' : 'Perlu tindakan',
				$r['keterangan'], $r['approval'], $r['user_approve'], $r['date_approve'],
			));
			fputcsv($out, $line, $sep);
		}
		fclose($out);
		exit;
	}

	// ---------------------------------------------------------------------
	//  [GENC-24SEP26-AGV] DATA LAMA GENERASI B (dumping, AGV) - K6 & K15.
	//  Dipindah APA ADANYA dari GencDumpingBase (24 Sep siang) supaya satu
	//  daftar dipakai semua mesin Gen B. Ubah kata di sini -> berlaku semua.
	// ---------------------------------------------------------------------

	/**
	 * K6 - peta isian teks bebas lama -> OK / NOK / PR.
	 * Kunci = teks yang sudah dinormalisasi (trim, spasi dirapatkan, titik di
	 * ujung dibuang, HURUF BESAR). Yang tidak ada di sini tampil apa adanya
	 * (kotak abu-abu) dan TIDAK dihitung "Perlu tindakan".
	 * Dipakai untuk tampilan & ringkasan saja. Report & data DB tidak tersentuh.
	 */
	protected function genc_legacy_values_gen_b(){
		$ok  = array('OK', 'O.K', 'OKE', 'OKAY', 'BAIK', 'KONDISI BAIK', 'BAGUS', 'BERSIH', 'SUDAH BERSIH', 'AMAN', 'NORMAL', 'KONDISI NORMAL',
			'SESUAI', 'V', '√', '✓', '✔', '✔️', 'GOOD', 'KENCANG', 'TIDAK KENDOR', 'TERPASANG', 'BERFUNGSI', 'TIDAK SOBEK', 'TIDAK BOCOR',
			'SUDAH DIBERSIHKAN', 'SUDAH DI BERSIHKAN', 'SUDAH DILAP', 'SUDAH DI LAP', 'TERBUKA', 'TERBUKA SEMPURNA', 'ON');
		$nok = array('NOK', 'NG', 'NOT OK', 'TIDAK OK', 'TIDAK BAIK', 'KONDISI TIDAK BAIK', 'RUSAK', 'KOTOR', 'BOCOR', 'SOBEK', 'KENDOR', 'LEPAS',
			'TERLEPAS', 'PATAH', 'X', '✗', '✘', '❌', 'TIDAK BERFUNGSI', 'MATI', 'ERROR', 'BAD');
		$pr  = array('PR', 'PERAWATAN', 'PERBAIKAN', 'PERLU PERAWATAN', 'PERLU PERBAIKAN', 'PERAWATAN/PERBAIKAN');
		$map = array();
		foreach($ok as $v){ $map[$v] = 'OK'; }
		foreach($nok as $v){ $map[$v] = 'NOK'; }
		foreach($pr as $v){ $map[$v] = 'PR'; }
		return $map;
	}

	/** K15 - isi kolom approval lama (dulu diketik operator) yang artinya "belum di-approve". */
	protected function genc_approval_pending_gen_b(){
		return array('-', '--', '0', 'NO', 'N/A', 'NA', 'BELUM', 'BELUM APPROVE', 'BELUM DI APPROVE', 'BELUM DIAPPROVE',
			'PENDING', 'MENUNGGU', 'WAITING', 'TIDAK', 'OPEN', 'PROSES');
	}

	// =====================================================================
	//  UTIL
	// =====================================================================

	/**
	 * [GENC-24SEP26-SHELL] Catat aksi checklist ke app_logs (menu App Logs).
	 * Gagal mencatat tidak menggagalkan aksi. Pesan berbahasa manusia: mesin, unit, tanggal, item bermasalah.
	 */
	protected function genc_log($what, $rec_id, $row){
		require_once __DIR__ . '/../../views/partials/_shared/genc_log.php';
		require_once __DIR__ . '/../../views/partials/_shared/genc_helpers.php';
		$c = $this->genc_c();
		$verb = array('add' => 'Isi', 'edit' => 'Ubah', 'delete' => 'Hapus', 'approve' => 'Approve');
		$name = isset($c['group_title']) && $c['group_title'] !== '' ? $c['group_title'] : $c['title'];
		if($this->genc_multi()){
			$uf = $this->genc_unit_field();
			if(isset($row[$uf]) && trim((string) $row[$uf]) !== '' && stripos($name, (string) $row[$uf]) === false){ $name .= ' ' . trim((string) $row[$uf]); }
		}
		$bad = array();
		foreach($this->genc_items() as $it){
			if(isset($row[$it['db']]) && in_array(strtoupper(trim((string) $row[$it['db']])), array('NOK', 'PR'), true)){ $bad[] = $it['part']; }
		}
		$tgl = !empty($row['date_created']) ? genc_date($row['date_created']) : genc_date(time());
		$msg = $verb[$what] . ' checklist ' . $name . ' (' . $tgl . ')';
		if($what !== 'delete' && $what !== 'approve'){ $msg .= empty($bad) ? ' - semua baik' : ' - perlu tindakan: ' . implode(', ', $bad); }
		$data = array('mesin' => $name, 'tanggal' => (isset($row['date_created']) ? $row['date_created'] : ''), 'oleh' => USER_NAME);
		if(!empty($bad)){ $data['perlu_tindakan'] = implode(', ', $bad); }
		if(!empty($row['keterangan'])){ $data['keterangan'] = $row['keterangan']; }
		genc_app_log('checklist.' . $what, $this->tablename, $rec_id, $msg, $data);
	}

	protected function genc_toast($msg, $type = 'success'){
		$icon = array('success' => 'fa-check-circle', 'danger' => 'fa-exclamation-circle', 'info' => 'fa-info-circle');
		$i = isset($icon[$type]) ? $icon[$type] : 'fa-info-circle';
		return '<div class="genc-toast genc-toast--' . $type . '" role="status"><i class="fa ' . $i . '"></i><span>' . $msg . '</span>'
			. '<button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>';
	}

	protected function genc_date_label($datetime){
		$ts = strtotime((string) $datetime);
		if(!$ts){ return (string) $datetime; }
		$nama = checklist_nama_bulan();
		return date('j', $ts) . ' ' . $nama[(int) date('n', $ts)] . ' ' . date('Y', $ts);
	}
}
