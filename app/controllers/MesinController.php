<?php
/**
 * ============================================================================
 *  MESIN & UNIT - tambah mesin / unit tanpa coding     [GENC-06OKT26-MESIN]
 *
 *  Tampilan ala Explorer VS Code: kiri pohon folder (= mesin di sidebar) & file
 *  (= unit / tab), kanan editor item yang dipilih. Data: _shared/genc_registry.php.
 *
 *  Route (hak akses role_permissions page_name 'mesin'):
 *    mesin                         -> index()  explorer (?pilih=f:<folder> | u:<id> | b:<page>:<slug> | ?baru=jenis | ?baru=unit&folder=<key>)
 *    mesin/add            (POST)   -> add()    jenis=1 -> jenis mesin baru  |  unit=1 -> unit baru
 *    mesin/edit/<jenis|unit|bawaan>/<id>  (POST) -> edit()
 *    mesin/delete/<jenis|unit>/<id>       (POST) -> delete()   hanya kalau BELUM ADA data checklist
 *  Semua perubahan dicatat di App Logs (aksi mesin.*).
 *
 *  Aturan data (K46-K53, KEPUTUSAN_GenerasiC.md):
 *   - Data checklist lama TIDAK pernah diubah / dihapus. Hapus hanya untuk mesin/unit yang belum punya data.
 *   - Unit yang sudah punya data: cukup "nonaktif" (tab, Home, isi baru disembunyikan; data tetap).
 *   - Ganti nama unit yang sudah punya data: nama lama otomatis jadi alias (data lama tetap ikut unit itu).
 *   - Item jenis baru yang dihapus: kolomnya tetap (data lama aman), item hanya disembunyikan.
 * ============================================================================
 */
require_once __DIR__ . '/../views/partials/_shared/genc_registry.php';
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';
require_once __DIR__ . '/../views/partials/_shared/genc_helpers.php';

class MesinController extends SecureController{

	function __construct(){
		parent::__construct();
		$this->tablename = "genc_mesin";
	}

	// =====================================================================
	//  HALAMAN
	// =====================================================================

	function index($fieldname = null, $fieldvalue = null){
		$setup_err = '';
		$rd = genc_reg_data();
		if(!$rd['ok'] || !$rd['profil_ok']){ $setup_err = genc_reg_ensure_tables(); }   // [GENC-06OKT26-ISI] + genc_profil   // tabel registry dibuat otomatis (sama dengan SQL)
		$req = $this->request;
		$sel = isset($req->pilih) ? (string) $req->pilih : '';
		$new = isset($req->baru) ? strtolower((string) $req->baru) : '';
		$tree = $this->mesin_tree();
		$data = array(
			'tree'      => $tree,
			'sel'       => $this->mesin_resolve($tree, $sel),
			'sel_raw'   => $sel,
			'new'       => in_array($new, array('jenis', 'unit'), true) ? $new : '',
			'new_folder'=> isset($req->folder) ? (string) $req->folder : '',
			'tipe'      => isset($req->tipe) ? (string) $req->tipe : '',
			'old'       => array(),
			'errors'    => array(),
			'setup_err' => $setup_err,
		);
		return $this->mesin_render($data);
	}

	/** POST: jenis=1 (jenis mesin baru) atau unit=1 (unit baru). */
	function add($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ return $this->redirect('mesin'); }
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect('mesin'); }
		if(!empty($formdata['jenis'])){ return $this->mesin_add_jenis($formdata); }
		if(!empty($formdata['unit'])){ return $this->mesin_add_unit($formdata); }
		return $this->redirect('mesin');
	}

	/** POST: edit/jenis/<page> | edit/unit/<id> | edit/bawaan/<page> (+ slug di form). */
	function edit($kind = null, $id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){
			$sel = $kind === 'unit' ? 'u:' . (int) $id : ($kind === 'jenis' ? 'f:' . (string) $id : '');
			return $this->redirect('mesin' . ($sel !== '' ? '?pilih=' . rawurlencode($sel) : ''));
		}
		if($kind === 'jenis'){ return $this->mesin_edit_jenis((string) $id, $formdata); }
		if($kind === 'unit'){ return $this->mesin_edit_unit((int) $id, $formdata); }
		if($kind === 'bawaan'){ return $this->mesin_edit_bawaan((string) $id, $formdata); }
		if($kind === 'isi'){ return $this->mesin_edit_isi((string) $id, $formdata); }       // [GENC-06OKT26-ISI]
		if($kind === 'kunci'){ return $this->mesin_edit_kunci((string) $id, $formdata); }
		if($kind === 'reset'){ return $this->mesin_reset_isi((string) $id); }
		return $this->redirect('mesin');
	}

	/** POST: delete/jenis/<page> | delete/unit/<id>. Ditolak kalau sudah ada data checklist. */
	function delete($kind = null, $id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ return $this->redirect('mesin'); }
		if($kind === 'jenis'){ return $this->mesin_delete_jenis((string) $id); }
		if($kind === 'unit'){ return $this->mesin_delete_unit((int) $id); }
		return $this->redirect('mesin');
	}

	// =====================================================================
	//  POHON (folder & unit) - dipakai tampilan dan validasi
	// =====================================================================

	/**
	 * @return array [folder_key => array(key, title, icon, mode, builtin, jenis(bool), types, units[], count, ...)]
	 * unit: sel, kind ('bawaan'|'unit'), id, label, tab, sub, type, profile, page, slug, aktif, count, norms
	 */
	protected function mesin_tree(){
		$out = array();
		foreach(genc_reg_builtin_folders() as $k => $f){
			$node = array('key' => $k, 'title' => $f['title'], 'icon' => $f['icon'], 'mode' => $f['mode'], 'builtin' => true, 'jenis' => false,
				'menu' => $f['menu'], 'def' => $f, 'units' => array(), 'count' => 0);
			if($f['mode'] === 'units'){
				$counts = array();
				foreach($f['types'] as $t){
					$conf = checklist_load_machine($t['profile']);
					if(!isset($counts[$conf['table']])){ $counts[$conf['table']] = genc_reg_unit_counts($conf['table'], $conf['unit_field']); }
					foreach((array) $conf['units'] as $u){
						$node['units'][] = $this->mesin_unit_node($u, $t, $counts[$conf['table']], $k);
					}
				}
			}
			else{
				foreach($f['pages'] as $p => $t){
					$note = genc_reg_builtin_note($p, '');
					$label = ($note && trim($note['label']) !== '') ? $note['label'] : $t['label'];
					$node['units'][] = array('sel' => 'b:' . $p, 'kind' => 'bawaan', 'id' => null, 'label' => $label, 'tab' => $label, 'default_label' => $t['label'],
						'sub' => $t['sub'], 'type' => '', 'profile' => $f['profile'], 'page' => $p, 'slug' => '', 'aktif' => true,
						'count' => (int) genc_reg_table_count($p), 'norms' => array(checklist_norm_unit($label), checklist_norm_unit($t['label'])), 'kode' => $t['sub']);
				}
				foreach(genc_reg_units_of_folder($k) as $id => $r){
					$node['units'][] = array('sel' => 'u:' . $id, 'kind' => 'unit', 'id' => $id, 'label' => $r['label'], 'tab' => $r['label'],
						'sub' => $r['kode'], 'type' => '', 'profile' => $f['profile'], 'page' => $r['page'], 'slug' => '', 'aktif' => (int) $r['aktif'] === 1,
						'count' => (int) genc_reg_table_count($r['page']), 'norms' => array(checklist_norm_unit($r['label'])), 'kode' => $r['kode']);
				}
			}
			foreach($node['units'] as $u){ $node['count'] += $u['count']; }
			$out[$k] = $node;
		}
		foreach(genc_reg_data()['mesin'] as $p => $m){
			$node = array('key' => $p, 'title' => $m['nama'], 'icon' => $m['ikon'], 'mode' => 'units', 'builtin' => false, 'jenis' => true,
				'menu' => $p, 'mesin' => $m, 'units' => array(), 'count' => 0, 'aktif' => (int) $m['aktif'] === 1, 'items' => genc_reg_item_count($m));
			$counts = genc_reg_unit_counts($p, 'unit');
			$t = array('profile' => $p, 'page' => $p, 'label' => '');
			foreach(genc_reg_units_of_folder($p) as $id => $r){
				$u = array('label' => $r['label'], 'alias' => $r['alias_arr'], 'reg_id' => $id);
				if((int) $r['aktif'] === 0){ $u['inactive'] = true; }
				$node['units'][] = $this->mesin_unit_node($u, $t, $counts, $p);
			}
			foreach($node['units'] as $u){ $node['count'] += $u['count']; }
			$out[$p] = $node;
		}
		return $out;
	}

	/** Satu unit di folder 1-tabel-banyak-unit. */
	protected function mesin_unit_node($u, $t, $counts, $folder){
		$label = trim((string) $u['label']);
		$norms = array();
		foreach(array_merge(array($label), isset($u['alias']) ? (array) $u['alias'] : array()) as $a){ $n = checklist_norm_unit($a); if($n !== ''){ $norms[$n] = true; } }
		$n = 0;
		foreach(array_keys($norms) as $k){ if(isset($counts[$k])){ $n += $counts[$k]; } }
		$slug = genc_reg_unit_slug($label);
		$dyn = !empty($u['reg_id']);
		return array(
			'sel'     => $dyn ? 'u:' . (int) $u['reg_id'] : 'b:' . $t['page'] . ':' . $slug,
			'kind'    => $dyn ? 'unit' : 'bawaan',
			'id'      => $dyn ? (int) $u['reg_id'] : null,
			'label'   => $label,
			'tab'     => $this->mesin_tab_label($folder, $label),
			'sub'     => $t['label'],
			'type'    => $t['label'],
			'profile' => $t['profile'],
			'page'    => $t['page'],
			'slug'    => $slug,
			'aktif'   => empty($u['inactive']),
			'count'   => $n,
			'norms'   => array_keys($norms),
			'alias'   => isset($u['alias']) ? (array) $u['alias'] : array(),
			'kode'    => '',
		);
	}

	/** Label yang tampil di tab (pola controller mesin: PM 1, TTL 6, Stacker). */
	protected function mesin_tab_label($folder, $label){
		if($folder === 'palletmover'){
			$label = preg_replace('/^Pallet\s+Mover\s+/i', 'PM ', $label);
			return preg_replace('/^Pallet\s+Stacker$/i', 'Stacker', $label);
		}
		if($folder === 'agv'){ return trim(preg_replace('/^AGV\s+/i', '', $label)); }
		return $label;
	}

	/** Pilihan di URL -> array('type' => 'folder'|'unit', 'folder' => node, 'unit' => node|null) atau null. */
	protected function mesin_resolve($tree, $sel){
		if($sel === ''){ return null; }
		if(strpos($sel, 'f:') === 0){
			$k = substr($sel, 2);
			return isset($tree[$k]) ? array('type' => 'folder', 'folder' => $tree[$k], 'unit' => null) : null;
		}
		foreach($tree as $f){
			foreach($f['units'] as $u){ if($u['sel'] === $sel){ return array('type' => 'unit', 'folder' => $f, 'unit' => $u); } }
		}
		return null;
	}

	protected function mesin_find_unit($tree, $id){
		foreach($tree as $f){ foreach($f['units'] as $u){ if($u['kind'] === 'unit' && (int) $u['id'] === (int) $id){ return array('folder' => $f, 'unit' => $u); } } }
		return null;
	}

	/** Label unit bentrok dengan unit lain di folder yang sama? (label, alias, slug sama -> bentrok) */
	protected function mesin_label_taken($folder_node, $label, $except_sel = ''){
		$n = checklist_norm_unit($label);
		$slug = genc_reg_unit_slug($label);
		foreach($folder_node['units'] as $u){
			if($u['sel'] === $except_sel){ continue; }
			if(in_array($n, $u['norms'], true)){ return $u['label']; }
			if($folder_node['mode'] === 'units' && $u['slug'] === $slug){ return $u['label']; }
		}
		return '';
	}

	// =====================================================================
	//  TAMBAH
	// =====================================================================

	protected function mesin_add_jenis($fd){
		$tree = $this->mesin_tree();
		$old = array(
			'nama'   => genc_reg_clean(isset($fd['nama']) ? $fd['nama'] : '', 60),
			'area'   => genc_reg_clean(isset($fd['area']) ? $fd['area'] : '', 100),
			'ikon'   => isset($fd['ikon']) ? (string) $fd['ikon'] : 'fa-cube',
			'unit'   => genc_reg_clean(isset($fd['unit_label']) ? $fd['unit_label'] : '', 60),
			'salin'  => isset($fd['salin']) ? (string) $fd['salin'] : '',
			'akses'  => isset($fd['akses']) ? (string) $fd['akses'] : '',
			'pelaksanaan' => genc_reg_clean(isset($fd['pelaksanaan']) ? $fd['pelaksanaan'] : '', 150),
		);
		$errors = array();
		if(function_exists('mb_strlen') ? mb_strlen($old['nama'], 'UTF-8') < 2 : strlen($old['nama']) < 2){ $errors['nama'] = 'Isi nama jenis mesin (minimal 2 huruf).'; }
		elseif(!preg_match('/[A-Za-z0-9]/', $old['nama'])){ $errors['nama'] = 'Nama harus berisi huruf / angka.'; }
		else{
			foreach($tree as $f){ if(strcasecmp(trim($f['title']), $old['nama']) === 0){ $errors['nama'] = 'Nama ini sudah dipakai mesin "' . genc_e($f['title']) . '".'; } }
		}
		if(!in_array($old['ikon'], genc_reg_icons(), true)){ $old['ikon'] = 'fa-cube'; }
		if($old['unit'] === ''){ $errors['unit_label'] = 'Isi nama unit pertama (contoh: ' . genc_e($old['nama'] !== '' ? $old['nama'] : 'Hand Pallet') . ' 1).'; }
		elseif($e = $this->mesin_unit_label_error($old['unit'])){ $errors['unit_label'] = $e; }
		$sources = $this->mesin_copy_sources();
		if($old['salin'] !== '' && !isset($sources[$old['salin']])){ $old['salin'] = ''; }
		$acl = $this->mesin_acl_sources();
		if(!isset($acl[$old['akses']])){ $errors['akses'] = 'Pilih hak akses awal.'; }
		if($errors){ return $this->mesin_render(array('tree' => $tree, 'sel' => null, 'sel_raw' => '', 'new' => 'jenis', 'new_folder' => '', 'old' => $old, 'errors' => $errors, 'setup_err' => '')); }

		$page = genc_reg_free_page($old['nama']);
		if($page === ''){ $this->mesin_toast('Tidak menemukan nama tabel yang bebas.', 'danger'); return $this->redirect('mesin?baru=jenis'); }
		// item awal (salinan dari mesin lain) -> kolom item_01, item_02, ...
		$items = array(); $seq = 0;
		foreach(genc_reg_sections() as $sk => $s){ $items[$sk] = array(); }
		if($old['salin'] !== ''){
			foreach($this->mesin_copy_items($old['salin']) as $sk => $list){
				foreach($list as $it){
					$seq++;
					$it['db'] = sprintf('item_%02d', $seq);
					$it['aktif'] = true;
					// [GENC-06OKT26-ISI] foto unggahan mesin lain disalin (bukan dipakai bersama: bisa diganti / dihapus di sana)
					if(strpos($it['foto'], 'uploads/mesin/') === 0){
						$dst = 'uploads/mesin/' . $page . '/' . $it['db'] . '-' . substr(md5(uniqid('', true)), 0, 8) . '.jpg';
						$it['foto'] = ((is_dir(ROOT . 'uploads/mesin/' . $page) || @mkdir(ROOT . 'uploads/mesin/' . $page, 0775, true)) && @copy(ROOT . $it['foto'], ROOT . $dst)) ? $dst : '';
					}
					$items[$sk][] = $it;
				}
			}
		}
		$cols = array();
		foreach($items as $list){ foreach($list as $it){ $cols[] = $it['db']; } }
		$pdo = genc_reg_db();
		$sql = genc_reg_create_table_sql($page, $cols);
		try { $pdo->exec($sql); }
		catch(\Throwable $e){
			$this->mesin_toast('Tabel data belum bisa dibuat (' . genc_e($e->getMessage()) . '). Minta admin database menjalankan perintah CREATE TABLE untuk ' . genc_e($page) . ', lalu coba lagi.', 'danger');
			return $this->redirect('mesin?baru=jenis');
		}
		try{
			$st = $pdo->prepare("INSERT INTO genc_mesin (page, nama, area, ikon, pelaksanaan, items, item_seq, aktif, urut, created_at, created_by, updated_at, updated_by) VALUES (?,?,?,?,?,?,?,1,?,?,?,?,?)");
			$st->execute(array($page, $old['nama'], $old['area'], $old['ikon'], $old['pelaksanaan'], json_encode($items), $seq, 100 + count(genc_reg_data()['mesin']), datetime_now(), USER_NAME, datetime_now(), USER_NAME));
			$mid = (int) $pdo->lastInsertId();
			$st = $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, urut, created_at, created_by) VALUES (?,?,?,?,?,'','[]',0,1,1,?,?)");
			$st->execute(array($page, $page, $page, genc_reg_unit_slug($old['unit']), $old['unit'], datetime_now(), USER_NAME));
		}
		catch(\Throwable $e){
			try { $pdo->exec("DROP TABLE IF EXISTS `$page`"); $pdo->prepare("DELETE FROM genc_mesin WHERE page = ?")->execute(array($page)); } catch(\Throwable $x){}
			$this->mesin_toast('Gagal menyimpan: ' . genc_e($e->getMessage()), 'danger');
			return $this->redirect('mesin?baru=jenis');
		}
		$acl_n = genc_reg_copy_acl($old['akses'], $page);
		genc_reg_data(true);
		genc_app_log('mesin.add', 'genc_mesin', $mid, 'Tambah jenis mesin ' . $old['nama'] . ' (' . $seq . ' item' . ($old['salin'] !== '' ? ' disalin dari ' . $sources[$old['salin']] : '') . ', unit ' . $old['unit'] . ')',
			array('page' => $page, 'nama' => $old['nama'], 'area' => $old['area'], 'item' => $seq, 'unit' => $old['unit'], 'hak_akses_dari' => $acl[$old['akses']], 'hak_akses_baris' => $acl_n));
		$msg = 'Jenis mesin <b>' . genc_e($old['nama']) . '</b> dibuat.';
		$msg .= $seq > 0 ? ' Cek / ubah ' . $seq . ' item checklist-nya, lalu simpan.' : ' Langkah berikutnya: tambahkan item checklist, lalu simpan &mdash; mesin baru muncul di menu setelah punya minimal 1 item.';
		if(!$this->mesin_role_can($page, 'list')){ $msg .= ' Catatan: role Anda belum punya akses Lihat ke mesin ini (atur di Role Permissions).'; }
		$this->mesin_toast($msg, 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page));
	}

	protected function mesin_add_unit($fd){
		$tree = $this->mesin_tree();
		$fk = isset($fd['folder']) ? (string) $fd['folder'] : '';
		$old = array(
			'folder' => $fk,
			'label'  => genc_reg_clean(isset($fd['label']) ? $fd['label'] : '', 60),
			'kode'   => genc_reg_clean(isset($fd['kode']) ? $fd['kode'] : '', 60),
			'profile'=> isset($fd['profile']) ? (string) $fd['profile'] : '',
		);
		$errors = array();
		if(!isset($tree[$fk])){ $errors['folder'] = 'Pilih mesin (folder) tempat unit ini.'; }
		if($old['label'] === ''){ $errors['label'] = 'Isi nama unit.'; }
		elseif($e = $this->mesin_unit_label_error($old['label'])){ $errors['label'] = $e; }
		elseif(isset($tree[$fk]) && ($t = $this->mesin_label_taken($tree[$fk], $old['label']))){ $errors['label'] = 'Nama ini sama dengan unit "' . genc_e($t) . '" yang sudah ada.'; }
		$type = null;
		if(isset($tree[$fk]) && $tree[$fk]['builtin'] && $tree[$fk]['mode'] === 'units'){
			foreach($tree[$fk]['def']['types'] as $t){ if($t['profile'] === $old['profile']){ $type = $t; } }
			if(!$type){ $errors['profile'] = 'Pilih jenis unit.'; }
		}
		if($errors){ return $this->mesin_render(array('tree' => $tree, 'sel' => null, 'sel_raw' => '', 'new' => 'unit', 'new_folder' => $fk, 'old' => $old, 'errors' => $errors, 'setup_err' => '')); }

		$node = $tree[$fk];
		$pdo = genc_reg_db();
		$urut = 1 + count(genc_reg_units_of_folder($fk));
		if($node['mode'] === 'units'){
			// 1 tabel banyak unit: cukup didaftarkan (tab + report + Home ikut otomatis)
			$profile = $node['jenis'] ? $fk : $type['profile'];
			$page    = $node['jenis'] ? $fk : $type['page'];
			$st = $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, urut, created_at, created_by) VALUES (?,?,?,?,?,'','[]',0,1,?,?,?)");
			$st->execute(array($fk, $profile, $page, genc_reg_unit_slug($old['label']), $old['label'], $urut, datetime_now(), USER_NAME));
			$id = (int) $pdo->lastInsertId();
			genc_app_log('mesin.unit.add', 'genc_unit', $id, 'Tambah unit ' . $old['label'] . ' di ' . $node['title'] . ($type ? ' (' . $type['label'] . ')' : ''),
				array('mesin' => $node['title'], 'unit' => $old['label'], 'jenis' => $type ? $type['label'] : '', 'halaman' => $page));
			// checklist lama yang kebetulan sudah tercatat dengan nama ini (mis. unit tak dikenal "Pallet Mover 5") ikut unit baru
			$conf = checklist_load_machine($profile);
			$cnt = genc_reg_unit_counts($conf['table'], $conf['unit_field']);
			$n_old = isset($cnt[checklist_norm_unit($old['label'])]) ? (int) $cnt[checklist_norm_unit($old['label'])] : 0;
			$this->mesin_toast('Unit <b>' . genc_e($old['label']) . '</b> ditambahkan ke ' . genc_e($node['title']) . ' &mdash; sudah muncul sebagai tab, di Home, dan di report.'
				. ($n_old > 0 ? ' <b>' . $n_old . ' checklist lama</b> yang sudah tercatat dengan nama ini sekarang ikut unit ini.' : ''), 'success');
			genc_reg_data(true);
			return $this->redirect('mesin?pilih=' . rawurlencode('u:' . $id));
		}
		// 1 tabel per unit (Dumping, Geprek, Conveyor): tabel baru = salinan struktur tabel bawaan
		$f = $node['def'];
		$ls = genc_reg_page_slug($old['label']);
		$page = genc_reg_free_page(strpos($ls, $fk) === 0 ? $ls : $fk . '_' . $ls);
		if($page === ''){ $this->mesin_toast('Tidak menemukan nama tabel yang bebas.', 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $fk)); }
		try { $pdo->exec("CREATE TABLE `$page` LIKE `" . $f['source'] . "`"); }
		catch(\Throwable $e){
			$this->mesin_toast('Tabel data unit belum bisa dibuat (' . genc_e($e->getMessage()) . ').', 'danger');
			return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $fk));
		}
		try{
			$st = $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, urut, created_at, created_by) VALUES (?,?,?,'',?,?,'[]',0,1,?,?,?)");
			$st->execute(array($fk, $f['profile'], $page, $old['label'], $old['kode'], $urut, datetime_now(), USER_NAME));
			$id = (int) $pdo->lastInsertId();
		}
		catch(\Throwable $e){
			try { $pdo->exec("DROP TABLE IF EXISTS `$page`"); } catch(\Throwable $x){}
			$this->mesin_toast('Gagal menyimpan: ' . genc_e($e->getMessage()), 'danger');
			return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $fk));
		}
		$acl_n = genc_reg_copy_acl($f['source'], $page);
		genc_reg_data(true);
		genc_app_log('mesin.unit.add', 'genc_unit', $id, 'Tambah unit ' . $old['label'] . ' di ' . $node['title'] . ' (halaman & tabel baru ' . $page . ')',
			array('mesin' => $node['title'], 'unit' => $old['label'], 'kode' => $old['kode'], 'halaman' => $page, 'hak_akses_dari' => $f['source'], 'hak_akses_baris' => $acl_n));
		$this->mesin_toast('Unit <b>' . genc_e($old['label']) . '</b> ditambahkan ke ' . genc_e($node['title']) . ' &mdash; tab baru dengan data sendiri. Hak akses disalin dari ' . genc_e($this->mesin_page_name($f['source'])) . '.', 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode('u:' . $id));
	}

	/** Nama unit yang tidak boleh (bentrok dengan kata kunci URL). */
	protected function mesin_unit_label_error($label){
		if(!preg_match('/[A-Za-z0-9]/', $label)){ return 'Nama unit harus berisi huruf / angka.'; }
		$slug = genc_reg_unit_slug($label);
		if(in_array($slug, array('semua', 'lainnya'), true)){ return 'Nama "' . genc_e($label) . '" dipakai sistem, pilih nama lain.'; }
		return '';
	}

	// =====================================================================
	//  UBAH
	// =====================================================================

	protected function mesin_edit_unit($id, $fd){
		$tree = $this->mesin_tree();
		$hit = $this->mesin_find_unit($tree, $id);
		$all = genc_reg_data()['unit'];
		if(!$hit || !isset($all[$id])){ $this->mesin_toast('Unit tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $hit['folder']; $u = $hit['unit']; $row = $all[$id];
		$old = array(
			'label'   => genc_reg_clean(isset($fd['label']) ? $fd['label'] : '', 60),
			'kode'    => genc_reg_clean(isset($fd['kode']) ? $fd['kode'] : '', 60),
			'profile' => isset($fd['profile']) ? (string) $fd['profile'] : $row['profile'],
			'aktif'   => !empty($fd['aktif']) ? 1 : 0,
		);
		$errors = array();
		if($old['label'] === ''){ $errors['label'] = 'Isi nama unit.'; }
		elseif($e = $this->mesin_unit_label_error($old['label'])){ $errors['label'] = $e; }
		elseif($t = $this->mesin_label_taken($f, $old['label'], $u['sel'])){ $errors['label'] = 'Nama ini sama dengan unit "' . genc_e($t) . '" yang sudah ada.'; }
		$type = null;
		if($f['builtin'] && $f['mode'] === 'units'){
			foreach($f['def']['types'] as $t){ if($t['profile'] === $old['profile']){ $type = $t; } }
			if(!$type){ $errors['profile'] = 'Pilih jenis unit.'; }
			elseif($old['profile'] !== $row['profile'] && $u['count'] > 0){ $errors['profile'] = 'Jenis tidak bisa diganti karena unit ini sudah punya ' . $u['count'] . ' checklist (isi checklist-nya beda per jenis).'; }
		}
		if(!$old['aktif'] && $u['aktif'] && !$this->mesin_other_active($f, $u['sel'])){ $errors['aktif'] = 'Ini satu-satunya unit aktif di ' . genc_e($f['title']) . ' &mdash; tidak bisa dinonaktifkan.'; }
		if($errors){ return $this->mesin_render(array('tree' => $tree, 'sel' => array('type' => 'unit', 'folder' => $f, 'unit' => $u), 'sel_raw' => $u['sel'], 'new' => '', 'new_folder' => '', 'old' => $old, 'errors' => $errors, 'setup_err' => '')); }

		$alias = $row['alias_arr'];
		$diff = array();
		if($old['label'] !== $row['label']){
			$diff['nama'] = $row['label'] . ' -> ' . $old['label'];
			// unit 1-tabel-banyak-unit yang sudah punya data: nama lama jadi alias supaya data lama tetap ikut unit ini
			if($f['mode'] === 'units' && $u['count'] > 0 && !in_array($row['label'], $alias, true)){ $alias[] = $row['label']; }
		}
		if($f['mode'] === 'tables' && $old['kode'] !== $row['kode']){ $diff['kode'] = ($row['kode'] === '' ? '-' : $row['kode']) . ' -> ' . ($old['kode'] === '' ? '-' : $old['kode']); }
		if($type && $old['profile'] !== $row['profile']){ $diff['jenis'] = $type['label']; }
		if((int) $old['aktif'] !== (int) $row['aktif']){ $diff['status'] = $old['aktif'] ? 'aktif' : 'nonaktif'; }
		if(empty($diff)){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect('mesin?pilih=' . rawurlencode('u:' . $id)); }
		$pdo = genc_reg_db();
		$st = $pdo->prepare("UPDATE genc_unit SET label = ?, slug = ?, kode = ?, profile = ?, page = ?, alias = ?, aktif = ?, updated_at = ?, updated_by = ? WHERE id = ?");
		$page = ($type ? $type['page'] : $row['page']);
		$st->execute(array($old['label'], $f['mode'] === 'units' ? genc_reg_unit_slug($old['label']) : '', $f['mode'] === 'tables' ? $old['kode'] : $row['kode'],
			$type ? $type['profile'] : $row['profile'], $page, json_encode(array_values($alias)), $old['aktif'], datetime_now(), USER_NAME, $id));
		genc_reg_data(true);
		$note = array(); foreach($diff as $k => $v){ $note[] = $k . ': ' . $v; }
		genc_app_log('mesin.unit.edit', 'genc_unit', $id, 'Ubah unit ' . $row['label'] . ' (' . $f['title'] . ') - ' . implode('; ', $note), $diff);
		$this->mesin_toast('Perubahan unit <b>' . genc_e($old['label']) . '</b> tersimpan.' . (isset($diff['status']) && !$old['aktif'] ? ' Unit disembunyikan dari tab, Home &amp; form isi; datanya tetap.' : ''), 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode('u:' . $id));
	}

	/** Unit bawaan: nonaktif / aktif (1-tabel-banyak-unit) atau nama tab (Dumping/Geprek/Conveyor). */
	protected function mesin_edit_bawaan($page, $fd){
		$tree = $this->mesin_tree();
		$slug = isset($fd['slug']) ? (string) $fd['slug'] : '';
		$sel = 'b:' . $page . ($slug !== '' ? ':' . $slug : '');
		$hit = $this->mesin_resolve($tree, $sel);
		if(!$hit || $hit['type'] !== 'unit' || $hit['unit']['kind'] !== 'bawaan'){ $this->mesin_toast('Unit tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $hit['folder']; $u = $hit['unit'];
		$note = genc_reg_builtin_note($page, $slug);
		$pdo = genc_reg_db();
		if($f['mode'] === 'units'){
			$aktif = !empty($fd['aktif']) ? 1 : 0;
			if($aktif === ($u['aktif'] ? 1 : 0)){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect('mesin?pilih=' . rawurlencode($sel)); }
			if(!$aktif && !$this->mesin_other_active($f, $u['sel'])){
				return $this->mesin_render(array('tree' => $tree, 'sel' => $hit, 'sel_raw' => $sel, 'new' => '', 'new_folder' => '', 'old' => array('aktif' => 0), 'errors' => array('aktif' => 'Ini satu-satunya unit aktif di ' . genc_e($f['title']) . ' &mdash; tidak bisa dinonaktifkan.'), 'setup_err' => ''));
			}
			if($note){ $pdo->prepare("UPDATE genc_unit SET aktif = ?, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array($aktif, datetime_now(), USER_NAME, $note['id'])); }
			else{ $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, urut, created_at, created_by) VALUES (?,?,?,?,?,'','[]',1,?,0,?,?)")->execute(array($f['key'], $u['profile'], $page, $slug, $u['label'], $aktif, datetime_now(), USER_NAME)); }
			genc_reg_data(true);
			genc_app_log('mesin.unit.edit', 'genc_unit', $note ? $note['id'] : $pdo->lastInsertId(), ($aktif ? 'Aktifkan' : 'Nonaktifkan') . ' unit bawaan ' . $u['label'] . ' (' . $f['title'] . ')', array('status' => $aktif ? 'aktif' : 'nonaktif'));
			$this->mesin_toast('Unit <b>' . genc_e($u['label']) . '</b> ' . ($aktif ? 'diaktifkan lagi.' : 'dinonaktifkan &mdash; tab, Home &amp; form isi disembunyikan, datanya tetap.'), 'success');
			return $this->redirect('mesin?pilih=' . rawurlencode($sel));
		}
		// Dumping / Geprek / Conveyor bawaan: hanya nama tab (tampil kalau ada unit lain di folder ini)
		$label = genc_reg_clean(isset($fd['label']) ? $fd['label'] : '', 60);
		$def = $u['default_label'];
		if($label !== '' && ($t = $this->mesin_label_taken($f, $label, $u['sel']))){
			return $this->mesin_render(array('tree' => $tree, 'sel' => $hit, 'sel_raw' => $sel, 'new' => '', 'new_folder' => '', 'old' => array('label' => $label), 'errors' => array('label' => 'Nama ini sama dengan unit "' . genc_e($t) . '".'), 'setup_err' => ''));
		}
		$new = ($label === '' || $label === $def) ? '' : $label;
		$cur = ($note && trim($note['label']) !== '') ? $note['label'] : '';
		if($new === $cur){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect('mesin?pilih=' . rawurlencode($sel)); }
		if($note){
			if($new === ''){ $pdo->prepare("DELETE FROM genc_unit WHERE id = ?")->execute(array($note['id'])); }
			else{ $pdo->prepare("UPDATE genc_unit SET label = ?, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array($new, datetime_now(), USER_NAME, $note['id'])); }
		}
		elseif($new !== ''){ $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, urut, created_at, created_by) VALUES (?,?,?,'',?,'','[]',1,1,0,?,?)")->execute(array($f['key'], $u['profile'], $page, $new, datetime_now(), USER_NAME)); }
		genc_reg_data(true);
		genc_app_log('mesin.unit.edit', 'genc_unit', $note ? $note['id'] : 0, 'Ubah nama tab ' . $f['title'] . ': ' . ($cur !== '' ? $cur : $def) . ' -> ' . ($new !== '' ? $new : $def), array('halaman' => $page));
		$this->mesin_toast('Nama tab tersimpan.', 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode($sel));
	}

	protected function mesin_other_active($folder_node, $except_sel){
		foreach($folder_node['units'] as $u){ if($u['sel'] !== $except_sel && $u['aktif']){ return true; } }
		return false;
	}

	/** Jenis mesin baru: identitas + daftar item (+ foto). */
	protected function mesin_edit_jenis($page, $fd){
		$tree = $this->mesin_tree();
		$d = genc_reg_data();
		if(!isset($d['mesin'][$page]) || !isset($tree[$page])){ $this->mesin_toast('Jenis mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$m = $d['mesin'][$page];
		$node = $tree[$page];
		if(empty($fd['nama']) && empty($fd['items']) && !empty($_SERVER['CONTENT_LENGTH'])){
			$this->mesin_toast('Data tidak terkirim &mdash; kemungkinan ukuran foto terlalu besar. Coba unggah foto lebih sedikit sekaligus.', 'danger');
			return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page));
		}
		$old = array(
			'nama'   => genc_reg_clean(isset($fd['nama']) ? $fd['nama'] : '', 60),
			'area'   => genc_reg_clean(isset($fd['area']) ? $fd['area'] : '', 100),
			'ikon'   => isset($fd['ikon']) && in_array((string) $fd['ikon'], genc_reg_icons(), true) ? (string) $fd['ikon'] : $m['ikon'],
			'pelaksanaan' => genc_reg_clean(isset($fd['pelaksanaan']) ? $fd['pelaksanaan'] : '', 150),
			'aktif'  => !empty($fd['aktif']) ? 1 : 0,
		);
		$errors = array();
		if(function_exists('mb_strlen') ? mb_strlen($old['nama'], 'UTF-8') < 2 : strlen($old['nama']) < 2){ $errors['nama'] = 'Isi nama jenis mesin (minimal 2 huruf).'; }
		else{ foreach($tree as $k => $f){ if($k !== $page && strcasecmp(trim($f['title']), $old['nama']) === 0){ $errors['nama'] = 'Nama ini sudah dipakai mesin "' . genc_e($f['title']) . '".'; } } }

		// ---- item: urutan = urutan di form; item lama dikenali dari kolom db-nya
		$current = array();
		foreach($m['items_arr'] as $sk => $list){ foreach($list as $it){ $current[$it['db']] = $it; } }
		$seq = (int) $m['item_seq'];
		$locked = genc_reg_locked($page);   // [GENC-06OKT26-ISI] terkunci: item & foto tidak diubah, identitas tetap bisa
		$posted = (!$locked && isset($fd['items']) && is_array($fd['items'])) ? $fd['items'] : array();
		$items = array(); $rows_old = array(); $new_cols = array(); $seen = array(); $item_err = array(); $active = 0;
		$maxlen = array('part' => 80, 'standar' => 150, 'metode' => 100, 'alat' => 80, 'durasi' => 30);
		foreach(genc_reg_sections() as $sk => $s){
			$items[$sk] = array(); $rows_old[$sk] = array();
			$list = (isset($posted[$sk]) && is_array($posted[$sk])) ? $posted[$sk] : array();
			foreach($list as $ri => $r){
				if(!is_array($r)){ continue; }
				$it = array('db' => '', 'aktif' => true, 'foto' => '');
				foreach($maxlen as $k => $mx){ $it[$k] = genc_reg_clean(isset($r[$k]) ? $r[$k] : '', $mx); }
				$db = isset($r['db']) ? (string) $r['db'] : '';
				$key = isset($r['key']) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $r['key'])) : '';
				$hapus = !empty($r['hapus']);
				$empty = ($it['part'] . $it['standar'] . $it['metode'] . $it['alat'] . $it['durasi']) === '';
				if($db !== '' && isset($current[$db]) && !isset($seen[$db])){
					$seen[$db] = true;
					$it['db'] = $db;
					$it['foto'] = $current[$db]['foto'];
					if($hapus){ $it = array_merge($current[$db], array('aktif' => false)); $items[$sk][] = $it; $rows_old[$sk][] = $it + array('key' => $key); continue; }
					if($it['part'] === ''){ $item_err[] = 'Nama part tidak boleh kosong (' . $s['short'] . ' baris ' . (count($items[$sk]) + 1) . ').'; }
				}
				else{
					if($hapus || $empty){ continue; }   // baris baru kosong / dibatalkan
					if($it['part'] === ''){ $item_err[] = 'Isi nama part untuk item baru di ' . $s['short'] . '.'; }
					$it['_new'] = true;
				}
				if(!empty($r['foto_hapus'])){ $it['foto'] = ''; $it['_foto_del'] = true; }
				$it['_key'] = $key;
				$items[$sk][] = $it;
				$rows_old[$sk][] = $it + array('key' => $key);
				$active++;
			}
		}
		// item lama yang tidak terkirim sama sekali (mis. form lama) -> tetap disimpan apa adanya
		foreach($current as $db => $it){
			if(isset($seen[$db])){ continue; }
			foreach($m['items_arr'] as $sk => $list){ foreach($list as $x){ if($x['db'] === $db){ $items[$sk][] = $x; if($x['aktif']){ $active++; } } } }
		}
		if($active > GENC_REG_MAX_ITEMS){ $item_err[] = 'Maksimal ' . GENC_REG_MAX_ITEMS . ' item per jenis mesin (sekarang ' . $active . ').'; }
		if($active === 0 && $old['aktif']){ $item_err[] = 'Tambahkan minimal 1 item supaya mesin bisa diisi (atau simpan dengan status nonaktif).'; }
		// foto: dicek dulu, disimpan setelah semua valid
		$uploads = $locked ? array() : $this->mesin_photo_inputs();
		foreach($uploads as $key => $tmp){ if(is_string($tmp) && strpos($tmp, '!') === 0){ $item_err[] = substr($tmp, 1); } }
		$gb = $locked ? array() : $this->mesin_gambar_inputs();
		foreach($gb as $key => $tmp){ if(is_string($tmp) && strpos($tmp, '!') === 0){ $item_err[] = substr($tmp, 1); } }
		if($item_err){ $errors['items'] = implode(' ', array_unique($item_err)); }
		if($errors){
			$old['items_rows'] = $rows_old;
			return $this->mesin_render(array('tree' => $tree, 'sel' => array('type' => 'folder', 'folder' => $node, 'unit' => null), 'sel_raw' => 'f:' . $page, 'new' => '', 'new_folder' => '', 'old' => $old, 'errors' => $errors, 'setup_err' => ''));
		}
		// kolom baru untuk item baru
		foreach($items as $sk => $list){
			foreach($list as $i => $it){
				if(empty($it['_new'])){ continue; }
				$seq++;
				$items[$sk][$i]['db'] = sprintf('item_%02d', $seq);
				$new_cols[] = $items[$sk][$i]['db'];
			}
		}
		$pdo = genc_reg_db();
		foreach($new_cols as $col){
			try { $pdo->exec("ALTER TABLE `$page` ADD COLUMN `$col` VARCHAR(20) NOT NULL DEFAULT ''"); }
			catch(\Throwable $e){
				if(stripos($e->getMessage(), 'Duplicate column') === false){
					$this->mesin_toast('Kolom item baru belum bisa dibuat (' . genc_e($e->getMessage()) . '). Tidak ada yang disimpan.', 'danger');
					return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page));
				}
			}
		}
		// simpan foto (ukuran diperkecil), hapus foto lama yang diganti
		$stats = array('foto' => 0);
		foreach($items as $sk => $list){
			foreach($list as $i => $it){
				$k = isset($it['_key']) ? $it['_key'] : '';
				$prev = isset($current[$it['db']]) ? $current[$it['db']]['foto'] : '';
				if($k !== '' && isset($uploads[$k]) && is_string($uploads[$k]) && strpos($uploads[$k], '!') !== 0){
					$path = $this->mesin_save_photo($uploads[$k], $page, $it['db']);
					if($path !== ''){ $items[$sk][$i]['foto'] = $path; $stats['foto']++; if($prev !== ''){ $this->mesin_unlink_photo($prev, $page); } }
				}
				elseif(!empty($it['_foto_del']) && $prev !== ''){ $this->mesin_unlink_photo($prev, $page); }
				unset($items[$sk][$i]['_new'], $items[$sk][$i]['_key'], $items[$sk][$i]['_foto_del']);
			}
		}
		// ringkasan perubahan untuk log
		$diff = array();
		foreach(array('nama', 'area', 'pelaksanaan', 'ikon') as $k){ if((string) $m[$k] !== (string) $old[$k]){ $diff[$k] = $m[$k] . ' -> ' . $old[$k]; } }
		if((int) $m['aktif'] !== $old['aktif']){ $diff['status'] = $old['aktif'] ? 'aktif' : 'nonaktif'; }
		$before = $m['items_arr']; $added = count($new_cols); $removed = 0; $changed = 0; $order_changed = false;
		$flat_before = array(); foreach($before as $list){ foreach($list as $x){ if($x['aktif']){ $flat_before[] = $x['db']; } } }
		$flat_after = array(); foreach($items as $list){ foreach($list as $x){ if($x['aktif']){ $flat_after[] = $x['db']; } } }
		foreach($current as $db => $x){
			if(!$x['aktif']){ continue; }
			$now = null; foreach($items as $list){ foreach($list as $y){ if($y['db'] === $db){ $now = $y; } } }
			if(!$now || !$now['aktif']){ $removed++; continue; }
			foreach(array('part', 'standar', 'metode', 'alat', 'durasi') as $k){ if($now[$k] !== $x[$k]){ $changed++; break; } }
		}
		$restored = 0; foreach($current as $db => $x){ if(!$x['aktif'] && in_array($db, $flat_after, true)){ $restored++; } }
		if(array_values(array_intersect($flat_before, $flat_after)) !== array_values(array_intersect($flat_after, $flat_before))){ $order_changed = true; }
		foreach($before as $sk => $list){ $a = array(); foreach($list as $x){ $a[] = $x['db']; } $b = array(); foreach($items[$sk] as $x){ $b[] = $x['db']; } if(array_values(array_intersect($a, $b)) !== array_values(array_intersect($b, $a))){ $order_changed = true; } }
		if($added){ $diff['item baru'] = $added; }
		if($removed){ $diff['item dihapus'] = $removed; }
		if($restored){ $diff['item dipulihkan'] = $restored; }
		if($changed){ $diff['item diubah'] = $changed; }
		if($order_changed){ $diff['urutan'] = 'diubah'; }
		if($stats['foto']){ $diff['foto'] = $stats['foto'] . ' diunggah'; }
		$foto_del = 0; foreach($rows_old as $list){ foreach($list as $x){ if(!empty($x['_foto_del'])){ $foto_del++; } } }
		if($foto_del){ $diff['foto dihapus'] = $foto_del; }
		// [GENC-06OKT26-ISI] gambar bagian mesin di report
		$g_diff = array();
		if(!$locked && genc_reg_data()['profil_ok']){
			list($gambar, $g_diff) = $this->mesin_gambar_apply($page, $gb, $fd, array());
			if($g_diff){
				$pdo->prepare("INSERT INTO genc_profil (profil, gambar, updated_at, updated_by) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE gambar = VALUES(gambar), updated_at = VALUES(updated_at), updated_by = VALUES(updated_by)")
					->execute(array($page, empty($gambar) ? null : json_encode($gambar), datetime_now(), USER_NAME));
				$diff = array_merge($diff, $g_diff);
			}
		}
		if(empty($diff)){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page)); }
		$st = $pdo->prepare("UPDATE genc_mesin SET nama = ?, area = ?, ikon = ?, pelaksanaan = ?, aktif = ?, items = ?, item_seq = ?, updated_at = ?, updated_by = ? WHERE page = ?");
		$st->execute(array($old['nama'], $old['area'], $old['ikon'], $old['pelaksanaan'], $old['aktif'], json_encode($items), $seq, datetime_now(), USER_NAME, $page));
		genc_reg_data(true);
		$note = array(); foreach($diff as $k => $v){ $note[] = $k . ': ' . $v; }
		genc_app_log('mesin.edit', 'genc_mesin', $m['id'], 'Ubah jenis mesin ' . $m['nama'] . ' - ' . implode('; ', $note), $diff);
		$n_active = 0; foreach($items as $list){ foreach($list as $x){ if($x['aktif']){ $n_active++; } } }
		$this->mesin_toast('Perubahan <b>' . genc_e($old['nama']) . '</b> tersimpan (' . $n_active . ' item aktif).' . ($added || $removed ? ' Berlaku untuk checklist berikutnya; checklist lama tetap tersimpan.' : ''), 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page));
	}

	// =====================================================================
	//  ISI CHECKLIST MESIN BAWAAN + KUNCI              [GENC-06OKT26-ISI]
	// =====================================================================

	/** URL kembali ke editor isi sebuah profil bawaan. */
	protected function mesin_isi_back($profile){
		$bp = genc_reg_builtin_profiles();
		if(!isset($bp[$profile])){ return 'mesin?pilih=' . rawurlencode('f:' . $profile); }
		$fk = $bp[$profile]['folder'];
		$f = genc_reg_builtin_folders();
		$multi = $f[$fk]['mode'] === 'units' && count($f[$fk]['types']) > 1;
		return 'mesin?pilih=' . rawurlencode('f:' . $fk) . ($multi ? '&tipe=' . rawurlencode($profile) : '') . '#isi';
	}

	/** Simpan isi checklist mesin bawaan (item, foto item, gambar report). */
	protected function mesin_edit_isi($profile, $fd){
		$bp = genc_reg_builtin_profiles();
		if(!isset($bp[$profile])){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$back = $this->mesin_isi_back($profile);
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect($back); }
		$label = $this->mesin_profile_label($profile);
		if(genc_reg_locked($profile)){ $this->mesin_toast('Isi checklist <b>' . genc_e($label) . '</b> sedang <b>dikunci</b>. Buka kunci dulu kalau memang perlu diubah.', 'danger'); return $this->redirect($back); }
		if(empty($fd['items']) && !empty($_SERVER['CONTENT_LENGTH'])){
			$this->mesin_toast('Data tidak terkirim &mdash; kemungkinan ukuran foto terlalu besar. Coba unggah foto lebih sedikit sekaligus.', 'danger');
			return $this->redirect($back);
		}
		$current_rows = genc_reg_isi_rows($profile);
		$current = array();
		foreach($current_rows as $sk => $list){ foreach($list as $it){ $current[$it['db']] = $it; } }
		$posted = (isset($fd['items']) && is_array($fd['items'])) ? $fd['items'] : array();
		$items = array(); $rows_old = array(); $seen = array(); $item_err = array(); $active = 0;
		$maxlen = array('part' => 80, 'standar' => 150, 'metode' => 100, 'alat' => 80, 'durasi' => 30);
		foreach(genc_reg_sections() as $sk => $s){
			$items[$sk] = array(); $rows_old[$sk] = array();
			$list = (isset($posted[$sk]) && is_array($posted[$sk])) ? $posted[$sk] : array();
			foreach($list as $r){
				if(!is_array($r)){ continue; }
				$it = array('db' => '', 'aktif' => true, 'baru' => true, 'foto' => '');
				foreach($maxlen as $k => $mx){ $it[$k] = genc_reg_clean(isset($r[$k]) ? $r[$k] : '', $mx); }
				$db = isset($r['db']) ? (string) $r['db'] : '';
				$key = isset($r['key']) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $r['key'])) : '';
				$hapus = !empty($r['hapus']);
				$empty = ($it['part'] . $it['standar'] . $it['metode'] . $it['alat'] . $it['durasi']) === '';
				if($db !== '' && isset($current[$db]) && !isset($seen[$db])){
					$seen[$db] = true;
					$it['db'] = $db; $it['baru'] = $current[$db]['baru']; $it['foto'] = $current[$db]['foto'];
					if($hapus){ $x = array_merge($current[$db], array('aktif' => false)); $items[$sk][] = $x; $rows_old[$sk][] = $x + array('key' => $key); continue; }
					if($it['part'] === ''){ $item_err[] = 'Nama part tidak boleh kosong (' . $s['short'] . ' baris ' . (count($items[$sk]) + 1) . ').'; }
				}
				else{
					if($hapus || $empty){ continue; }
					if($it['part'] === ''){ $item_err[] = 'Isi nama part untuk item baru di ' . $s['short'] . '.'; }
					$it['_new'] = true;
				}
				if(!empty($r['foto_hapus'])){ $it['foto'] = ''; $it['_foto_del'] = true; }
				$it['_key'] = $key;
				$items[$sk][] = $it;
				$rows_old[$sk][] = $it + array('key' => $key);
				$active++;
			}
		}
		foreach($current as $db => $x){   // item yang tidak terkirim (form lama / dipotong) -> tetap apa adanya
			if(isset($seen[$db])){ continue; }
			foreach($current_rows as $sk => $list){ foreach($list as $y){ if($y['db'] === $db){ $items[$sk][] = $y; if($y['aktif']){ $active++; } } } }
		}
		if($active > GENC_REG_MAX_ITEMS){ $item_err[] = 'Maksimal ' . GENC_REG_MAX_ITEMS . ' item per mesin (sekarang ' . $active . ').'; }
		if($active === 0){ $item_err[] = 'Minimal 1 item harus tetap ada.'; }
		$uploads = $this->mesin_photo_inputs();
		foreach($uploads as $k => $tmp){ if(is_string($tmp) && strpos($tmp, '!') === 0){ $item_err[] = substr($tmp, 1); } }
		$gb = $this->mesin_gambar_inputs();
		foreach($gb as $k => $tmp){ if(is_string($tmp) && strpos($tmp, '!') === 0){ $item_err[] = substr($tmp, 1); } }
		if($item_err){
			$tree = $this->mesin_tree();
			$node = $tree[$bp[$profile]['folder']];
			return $this->mesin_render(array('tree' => $tree, 'sel' => array('type' => 'folder', 'folder' => $node, 'unit' => null), 'sel_raw' => 'f:' . $node['key'], 'tipe' => $profile,
				'new' => '', 'new_folder' => '', 'old' => array('items_rows' => $rows_old), 'errors' => array('items' => implode(' ', array_unique($item_err))), 'setup_err' => ''));
		}
		// kolom baru (am_item_NN) di semua tabel yang memakai profil ini
		$tables = genc_reg_profile_tables($profile);
		$row = genc_reg_ov_row($profile);
		$seq = $row ? (int) $row['item_seq'] : 0;
		$pdo = genc_reg_db();
		$used = array(); $new_cols = array();
		foreach($items as $sk => $list){
			foreach($list as $i => $it){
				if(empty($it['_new'])){ continue; }
				$col = genc_reg_free_item_col($tables, $seq, $used);
				$used[$col] = true;
				$items[$sk][$i]['db'] = $col;
				$new_cols[] = $col;
			}
		}
		foreach($new_cols as $col){
			foreach($tables as $t){
				try { $pdo->exec("ALTER TABLE `" . str_replace('`', '', $t) . "` ADD COLUMN `$col` VARCHAR(100) NULL DEFAULT NULL"); }
				catch(\Throwable $e){
					if(stripos($e->getMessage(), 'Duplicate column') === false){
						$this->mesin_toast('Kolom item baru belum bisa dibuat di tabel ' . genc_e($t) . ' (' . genc_e($e->getMessage()) . '). Tidak ada yang disimpan.', 'danger');
						return $this->redirect($back);
					}
				}
			}
		}
		// foto item
		$dir = $profile;
		$foto_up = 0; $foto_del = 0;
		foreach($items as $sk => $list){
			foreach($list as $i => $it){
				$k = isset($it['_key']) ? $it['_key'] : '';
				$prev = isset($current[$it['db']]) ? $current[$it['db']]['foto'] : '';
				if($k !== '' && isset($uploads[$k]) && is_string($uploads[$k]) && strpos($uploads[$k], '!') !== 0){
					$path = $this->mesin_save_photo($uploads[$k], $dir, $it['db']);
					if($path !== ''){ $items[$sk][$i]['foto'] = $path; $foto_up++; if($prev !== ''){ $this->mesin_unlink_photo($prev, $dir); } }
				}
				elseif(!empty($it['_foto_del']) && $prev !== ''){ $foto_del++; $this->mesin_unlink_photo($prev, $dir); }
				unset($items[$sk][$i]['_new'], $items[$sk][$i]['_key'], $items[$sk][$i]['_foto_del']);
			}
		}
		// gambar report
		$base = genc_reg_base_conf($profile);
		list($gambar, $g_diff) = $this->mesin_gambar_apply($profile, $gb, $fd, $base ? (array) $base['images'] : array());
		// perubahan
		$diff = $this->mesin_items_diff($current_rows, $items, count($new_cols));
		if($foto_up){ $diff['foto'] = $foto_up . ' diunggah'; }
		if($foto_del){ $diff['foto dihapus'] = $foto_del; }
		$diff = array_merge($diff, $g_diff);
		if(empty($diff)){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect($back); }
		// sama persis dengan isi di kode -> salinan tidak disimpan (profil kembali 100% dari file)
		$items_json = $this->mesin_items_same($items, genc_reg_isi_base_rows($profile)) ? null : json_encode($items);
		$gambar_json = empty($gambar) ? null : json_encode($gambar);
		$st = $pdo->prepare("INSERT INTO genc_profil (profil, items, gambar, item_seq, updated_at, updated_by) VALUES (?,?,?,?,?,?)
			ON DUPLICATE KEY UPDATE items = VALUES(items), gambar = VALUES(gambar), item_seq = VALUES(item_seq), updated_at = VALUES(updated_at), updated_by = VALUES(updated_by)");
		$st->execute(array($profile, $items_json, $gambar_json, $seq, datetime_now(), USER_NAME));
		genc_reg_data(true);
		$note = array(); foreach($diff as $k => $v){ $note[] = $k . ': ' . $v; }
		genc_app_log('mesin.isi', 'genc_profil', 0, 'Ubah isi checklist ' . $label . ' - ' . implode('; ', $note), array('profil' => $profile, 'tabel' => implode(', ', $tables)) + $diff);
		$n_active = 0; foreach($items as $list){ foreach($list as $x){ if($x['aktif']){ $n_active++; } } }
		$this->mesin_toast('Isi checklist <b>' . genc_e($label) . '</b> tersimpan (' . $n_active . ' item aktif). Form isi, daftar &amp; report langsung memakai isi baru; checklist lama tetap tersimpan.', 'success');
		return $this->redirect($back);
	}

	/** Nama mesin untuk pesan & log: "Pallet Stacker", "Forklift Diesel", "AGV Counterbalance", "Mesin Dumping", nama jenis baru. */
	protected function mesin_profile_label($profile){
		$d = genc_reg_data();
		if(isset($d['mesin'][$profile])){ return $d['mesin'][$profile]['nama']; }
		$bp = genc_reg_builtin_profiles();
		if(!isset($bp[$profile])){ return $profile; }
		$f = genc_reg_builtin_folders();
		$fk = $bp[$profile]['folder'];
		if($f[$fk]['mode'] !== 'units' || $fk === 'palletmover'){ return $bp[$profile]['label']; }
		return $f[$fk]['title'] . ' ' . $bp[$profile]['label'];
	}

	/** Ringkasan perubahan item (untuk log & "tidak ada yang berubah"). */
	protected function mesin_items_diff($before, $after, $added){
		$cur = array(); foreach($before as $list){ foreach($list as $x){ $cur[$x['db']] = $x; } }
		$now = array(); foreach($after as $list){ foreach($list as $x){ $now[$x['db']] = $x; } }
		$diff = array(); $removed = 0; $restored = 0; $changed = 0;
		foreach($cur as $db => $x){
			if(!isset($now[$db])){ continue; }
			$y = $now[$db];
			if($x['aktif'] && !$y['aktif']){ $removed++; continue; }
			if(!$x['aktif'] && $y['aktif']){ $restored++; }
			foreach(array('part', 'standar', 'metode', 'alat', 'durasi') as $k){ if($y['aktif'] && (string) $x[$k] !== (string) $y[$k]){ $changed++; break; } }
		}
		$ord = function($rows){ $o = array(); foreach($rows as $sk => $list){ foreach($list as $x){ if($x['aktif']){ $o[] = $sk . ':' . $x['db']; } } } return $o; };
		$a = $ord($before); $b = $ord($after);
		if(array_values(array_intersect($a, $b)) !== array_values(array_intersect($b, $a))){ $diff['urutan'] = 'diubah'; }
		if($added){ $diff['item baru'] = $added; }
		if($removed){ $diff['item dihapus'] = $removed; }
		if($restored){ $diff['item dipulihkan'] = $restored; }
		if($changed){ $diff['item diubah'] = $changed; }
		return $diff;
	}

	/** Isi editor sama persis dengan isi di kode? (urutan, teks, foto, semua aktif, tanpa item baru) */
	protected function mesin_items_same($items, $base){
		foreach(genc_reg_sections() as $sk => $s){
			$a = isset($items[$sk]) ? array_values($items[$sk]) : array();
			$b = isset($base[$sk]) ? array_values($base[$sk]) : array();
			if(count($a) !== count($b)){ return false; }
			foreach($a as $i => $x){
				if(!$x['aktif'] || !empty($x['baru']) || $x['db'] !== $b[$i]['db']){ return false; }
				foreach(array('part', 'standar', 'metode', 'alat', 'durasi', 'foto') as $k){ if((string) $x[$k] !== (string) $b[$i][$k]){ return false; } }
			}
		}
		return true;
	}

	/** Gambar report yang diunggah: [atas|tengah|bawah => tmp | '!pesan']. Input: gambar[<slot>]. */
	protected function mesin_gambar_inputs(){
		$out = array();
		if(empty($_FILES['gambar']) || !is_array($_FILES['gambar']['name'])){ return $out; }
		$slots = genc_reg_photo_slots();
		foreach($_FILES['gambar']['name'] as $k => $name){
			if(!isset($slots[$k])){ continue; }
			$err = (int) $_FILES['gambar']['error'][$k];
			if($err === UPLOAD_ERR_NO_FILE){ continue; }
			if($err !== UPLOAD_ERR_OK){ $out[$k] = '!Gambar "' . genc_e((string) $name) . '" gagal diunggah' . (in_array($err, array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), true) ? ' (terlalu besar)' : '') . '.'; continue; }
			$tmp = $_FILES['gambar']['tmp_name'][$k];
			$info = @getimagesize($tmp);
			if(!$info || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP), true)){ $out[$k] = '!File "' . genc_e((string) $name) . '" bukan gambar (JPG / PNG).'; continue; }
			if((int) $_FILES['gambar']['size'][$k] > 12 * 1048576){ $out[$k] = '!Gambar "' . genc_e((string) $name) . '" lebih dari 12 MB.'; continue; }
			$out[$k] = $tmp;
		}
		return $out;
	}

	/**
	 * Gambar bagian mesin di report: unggah baru / hapus / kembali ke bawaan.
	 * @return array(array gambar_baru [slot => path,w,h], array diff)
	 */
	protected function mesin_gambar_apply($profile, $uploads, $fd, $base_images){
		$cur = genc_reg_ov_gambar($profile);
		$out = $cur; $diff = array(); $n_up = 0; $n_del = 0;
		$hapus = (isset($fd['gambar_hapus']) && is_array($fd['gambar_hapus'])) ? $fd['gambar_hapus'] : array();
		foreach(genc_reg_photo_slots() as $k => $lbl){
			if(isset($uploads[$k]) && is_string($uploads[$k]) && strpos($uploads[$k], '!') !== 0){
				$path = $this->mesin_save_photo($uploads[$k], $profile, 'gambar-' . $k);
				if($path === ''){ continue; }
				$sz = @getimagesize(ROOT . $path);
				$w = 152; $h = $sz ? (int) round(152 * $sz[1] / max(1, $sz[0])) : 135;
				if($h > 260){ $w = (int) round($w * 260 / $h); $h = 260; }
				if($h < 40){ $h = 40; }
				if(isset($cur[$k]) && $cur[$k]['path'] !== ''){ $this->mesin_unlink_photo($cur[$k]['path'], $profile); }
				$out[$k] = array('path' => $path, 'w' => $w, 'h' => $h);
				$n_up++;
				continue;
			}
			if(empty($hapus[$k])){ continue; }
			$orig = isset($base_images[$k]) ? (string) $base_images[$k] : '';
			if(isset($cur[$k])){
				if($cur[$k]['path'] !== ''){ $this->mesin_unlink_photo($cur[$k]['path'], $profile); }
				unset($out[$k]);   // kembali ke gambar bawaan (atau kosong kalau memang tidak ada)
				if($cur[$k]['path'] !== '' && $orig !== '' && is_file(ROOT . $orig)){ $diff['gambar ' . strtolower($lbl)] = 'kembali ke bawaan'; } else { $n_del++; }
			}
			elseif($orig !== '' && is_file(ROOT . $orig)){
				$out[$k] = array('path' => '', 'w' => 152, 'h' => 135);   // sembunyikan gambar bawaan
				$n_del++;
			}
		}
		if($n_up){ $diff['gambar report'] = $n_up . ' diunggah'; }
		if($n_del){ $diff['gambar report dihapus'] = $n_del; }
		return array($out, $diff);
	}

	/** POST kunci=1|0: kunci / buka kunci isi checklist (bawaan atau jenis baru). */
	protected function mesin_edit_kunci($profile, $fd){
		$bp = genc_reg_builtin_profiles();
		$d = genc_reg_data();
		$is_jenis = isset($d['mesin'][$profile]);
		if(!isset($bp[$profile]) && !$is_jenis){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$back = $is_jenis ? 'mesin?pilih=' . rawurlencode('f:' . $profile) . '#isi' : $this->mesin_isi_back($profile);
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect($back); }
		$want = !empty($fd['kunci']) ? 1 : 0;
		$label = $this->mesin_profile_label($profile);
		if($is_jenis && $want && genc_reg_item_count($d['mesin'][$profile]) === 0){ $this->mesin_toast('Tambahkan item dulu sebelum mengunci.', 'danger'); return $this->redirect($back); }
		if((genc_reg_locked($profile) ? 1 : 0) === $want){ $this->mesin_toast($want ? 'Sudah terkunci.' : 'Sudah tidak terkunci.', 'info'); return $this->redirect($back); }
		$pdo = genc_reg_db();
		$st = $pdo->prepare("INSERT INTO genc_profil (profil, kunci, kunci_at, kunci_by) VALUES (?,?,?,?)
			ON DUPLICATE KEY UPDATE kunci = VALUES(kunci), kunci_at = VALUES(kunci_at), kunci_by = VALUES(kunci_by)");
		$st->execute(array($profile, $want, datetime_now(), USER_NAME));
		genc_reg_data(true);
		genc_app_log('mesin.kunci', 'genc_profil', 0, ($want ? 'Kunci' : 'Buka kunci') . ' isi checklist ' . $label, array('profil' => $profile, 'kunci' => $want ? 'dikunci' : 'dibuka'));
		$this->mesin_toast($want ? '<i class="fa fa-lock"></i> Isi checklist <b>' . genc_e($label) . '</b> dikunci &mdash; tidak bisa diubah sampai kuncinya dibuka.'
			: '<i class="fa fa-unlock"></i> Kunci <b>' . genc_e($label) . '</b> dibuka &mdash; isi checklist bisa diubah. Kunci lagi setelah selesai.', 'success');
		return $this->redirect($back);
	}

	/** POST: kembalikan isi checklist mesin bawaan ke isi di kode (kolom & data tetap). */
	protected function mesin_reset_isi($profile){
		$bp = genc_reg_builtin_profiles();
		if(!isset($bp[$profile])){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$back = $this->mesin_isi_back($profile);
		$label = $this->mesin_profile_label($profile);
		if(genc_reg_locked($profile)){ $this->mesin_toast('Isi checklist <b>' . genc_e($label) . '</b> sedang dikunci.', 'danger'); return $this->redirect($back); }
		$row = genc_reg_ov_row($profile);
		if(!$row || (($row['items'] === null || $row['items'] === '') && ($row['gambar'] === null || $row['gambar'] === ''))){ $this->mesin_toast('Isi checklist sudah sama dengan bawaan.', 'info'); return $this->redirect($back); }
		genc_reg_db()->prepare("UPDATE genc_profil SET items = NULL, gambar = NULL, updated_at = ?, updated_by = ? WHERE profil = ?")->execute(array(datetime_now(), USER_NAME, $profile));
		genc_reg_data(true);
		genc_app_log('mesin.isi', 'genc_profil', 0, 'Kembalikan isi checklist ' . $label . ' ke bawaan (kode)', array('profil' => $profile));
		$this->mesin_toast('Isi checklist <b>' . genc_e($label) . '</b> kembali ke bawaan. Item yang tadinya ditambah disembunyikan; datanya tetap tersimpan.', 'success');
		return $this->redirect($back);
	}

	// =====================================================================
	//  HAPUS (hanya yang belum punya data)
	// =====================================================================

	protected function mesin_delete_jenis($page){
		$d = genc_reg_data();
		if(!isset($d['mesin'][$page])){ $this->mesin_toast('Jenis mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$m = $d['mesin'][$page];
		$n = genc_reg_table_count($page);
		if($n !== null && $n > 0){ $this->mesin_toast('<b>' . genc_e($m['nama']) . '</b> sudah punya ' . $n . ' checklist &mdash; tidak bisa dihapus. Nonaktifkan saja (data tetap tersimpan).', 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page)); }
		$pdo = genc_reg_db();
		try{
			$pdo->exec("DROP TABLE IF EXISTS `$page`");
			$pdo->prepare("DELETE FROM genc_unit WHERE folder = ?")->execute(array($page));
			$pdo->prepare("DELETE FROM genc_mesin WHERE page = ?")->execute(array($page));
			try { $pdo->prepare("DELETE FROM genc_profil WHERE profil = ?")->execute(array($page)); } catch(\Throwable $x){ }   // [GENC-06OKT26-ISI]
		}
		catch(\Throwable $e){ $this->mesin_toast('Gagal menghapus: ' . genc_e($e->getMessage()), 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page)); }
		$acl = genc_reg_drop_acl($page);
		foreach($m['items_arr'] as $list){ foreach($list as $it){ if($it['foto'] !== ''){ $this->mesin_unlink_photo($it['foto'], $page); } } }
		foreach(genc_reg_ov_gambar($page) as $g){ if($g['path'] !== ''){ $this->mesin_unlink_photo($g['path'], $page); } }
		@rmdir(ROOT . 'uploads/mesin/' . $page);
		genc_reg_data(true);
		genc_app_log('mesin.delete', 'genc_mesin', $m['id'], 'Hapus jenis mesin ' . $m['nama'] . ' (belum ada data)', array('page' => $page, 'hak_akses_dihapus' => $acl));
		$this->mesin_toast('Jenis mesin <b>' . genc_e($m['nama']) . '</b> dihapus.', 'success');
		return $this->redirect('mesin');
	}

	protected function mesin_delete_unit($id){
		$tree = $this->mesin_tree();
		$hit = $this->mesin_find_unit($tree, $id);
		$all = genc_reg_data()['unit'];
		if(!$hit || !isset($all[$id])){ $this->mesin_toast('Unit tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $hit['folder']; $u = $hit['unit']; $row = $all[$id];
		$back = 'mesin?pilih=' . rawurlencode('u:' . $id);
		if($u['count'] > 0){ $this->mesin_toast('Unit <b>' . genc_e($u['label']) . '</b> sudah punya ' . $u['count'] . ' checklist &mdash; tidak bisa dihapus. Nonaktifkan saja (data tetap tersimpan).', 'danger'); return $this->redirect($back); }
		if($f['jenis'] && count($f['units']) <= 1){ $this->mesin_toast('Jenis mesin minimal punya 1 unit. Tambah unit lain dulu, atau hapus jenis mesinnya.', 'danger'); return $this->redirect($back); }
		if($u['aktif'] && !$this->mesin_other_active($f, $u['sel'])){ $this->mesin_toast('Ini satu-satunya unit aktif di ' . genc_e($f['title']) . '.', 'danger'); return $this->redirect($back); }
		$pdo = genc_reg_db();
		try{
			if($f['mode'] === 'tables'){
				$n = genc_reg_table_count($row['page']);
				if($n !== null && $n > 0){ $this->mesin_toast('Unit ini sudah punya data &mdash; tidak bisa dihapus.', 'danger'); return $this->redirect($back); }
				$pdo->exec("DROP TABLE IF EXISTS `" . str_replace('`', '', $row['page']) . "`");
				genc_reg_drop_acl($row['page']);
			}
			$pdo->prepare("DELETE FROM genc_unit WHERE id = ?")->execute(array($id));
		}
		catch(\Throwable $e){ $this->mesin_toast('Gagal menghapus: ' . genc_e($e->getMessage()), 'danger'); return $this->redirect($back); }
		genc_reg_data(true);
		genc_app_log('mesin.unit.delete', 'genc_unit', $id, 'Hapus unit ' . $u['label'] . ' dari ' . $f['title'] . ' (belum ada data)', array('halaman' => $row['page']));
		$this->mesin_toast('Unit <b>' . genc_e($u['label']) . '</b> dihapus.', 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $f['key']));
	}

	// =====================================================================
	//  SALIN ITEM, HAK AKSES, FOTO
	// =====================================================================

	/** Sumber "salin isi checklist dari": [profil/page => nama]. */
	protected function mesin_copy_sources(){
		$out = array(
			'palletmover' => 'Pallet Mover (8 item)', 'forklift_electric' => 'Forklift Electric', 'forklift_diesel' => 'Forklift Diesel',
			'agv_table_top_lift' => 'AGV Table Top Lift', 'counterbalance' => 'AGV Counterbalance', 'dumping' => 'Mesin Dumping',
			'mesin_geprek' => 'Mesin Geprek', 'conveyor' => 'Conveyor',
		);
		foreach($out as $p => $n){
			try { $c = checklist_load_machine($p); $k = 0; foreach($c['sections'] as $s){ $k += count($s['items']); } $out[$p] = preg_replace('/ \(\d+ item\)$/', '', $n) . ' (' . $k . ' item)'; } catch(\Throwable $e){ unset($out[$p]); }
		}
		foreach(genc_reg_data()['mesin'] as $p => $m){ $out[$p] = $m['nama'] . ' (' . genc_reg_item_count($m) . ' item)'; }
		return $out;
	}

	/** Item dari profil bawaan / jenis baru, dikelompokkan per bagian. */
	protected function mesin_copy_items($src){
		$out = array();
		foreach(genc_reg_sections() as $sk => $s){ $out[$sk] = array(); }
		$keys = array_keys($out);
		try { $c = checklist_load_machine($src); } catch(\Throwable $e){ return $out; }
		foreach(array_values($c['sections']) as $i => $s){
			$sk = (isset($s['key']) && isset($out[$s['key']])) ? $s['key'] : (isset($keys[$i]) ? $keys[$i] : 'inspection');
			foreach((array) $s['items'] as $it){
				$out[$sk][] = array(
					'part' => genc_reg_clean(isset($it['part']) ? $it['part'] : '', 80), 'standar' => genc_reg_clean(isset($it['standar']) ? $it['standar'] : '', 150),
					'metode' => genc_reg_clean(isset($it['metode']) ? $it['metode'] : '', 100), 'alat' => genc_reg_clean(isset($it['alat']) ? $it['alat'] : '', 80),
					'durasi' => genc_reg_clean(isset($it['durasi']) ? $it['durasi'] : '', 30),
					'foto' => (!empty($it['foto']) && is_file(ROOT . $it['foto'])) ? (string) $it['foto'] : '',
				);
			}
		}
		return $out;
	}

	/** Halaman acuan hak akses awal: [page => nama]. */
	protected function mesin_acl_sources(){
		$out = array('mesin_geprek' => 'Mesin Geprek', 'conveyor' => 'Conveyor', 'kir3p01dp001' => 'Mesin Dumping KIR3', 'palletmover' => 'Pallet Mover & Stacker',
			'forklift' => 'Forklift', 'agv_table_top_lift' => 'AGV Table Top Lift', 'counterbalance' => 'AGV Counterbalance');
		foreach(genc_reg_data()['mesin'] as $p => $m){ $out[$p] = $m['nama']; }
		return $out;
	}

	protected function mesin_page_name($page){
		$a = $this->mesin_acl_sources();
		return isset($a[$page]) ? $a[$page] : $page;
	}

	/** Role user yang login punya <page>/<action>? (dibaca langsung dari DB: ACL request ini dimuat sebelum hak disalin) */
	protected function mesin_role_can($page, $action){
		$pdo = genc_reg_db();
		if(!$pdo){ return false; }
		$st = $pdo->prepare("SELECT COUNT(*) FROM role_permissions WHERE role_id = ? AND page_name = ? AND action_name = ?");
		$st->execute(array(USER_ROLE, $page, $action));
		return (int) $st->fetchColumn() > 0;
	}

	/**
	 * Foto yang diunggah: [key => tmp path] atau [key => '!pesan error'].
	 * Input: <input type="file" name="foto[<key>]">.
	 */
	protected function mesin_photo_inputs(){
		$out = array();
		if(empty($_FILES['foto']) || !is_array($_FILES['foto']['name'])){ return $out; }
		foreach($_FILES['foto']['name'] as $key => $name){
			$key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $key));
			$err = (int) $_FILES['foto']['error'][$key];
			if($err === UPLOAD_ERR_NO_FILE){ continue; }
			if($err !== UPLOAD_ERR_OK){ $out[$key] = '!Foto "' . genc_e((string) $name) . '" gagal diunggah' . (in_array($err, array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), true) ? ' (terlalu besar)' : '') . '.'; continue; }
			$tmp = $_FILES['foto']['tmp_name'][$key];
			$info = @getimagesize($tmp);
			if(!$info || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP), true)){ $out[$key] = '!File "' . genc_e((string) $name) . '" bukan foto (JPG / PNG).'; continue; }
			if((int) $_FILES['foto']['size'][$key] > 12 * 1048576){ $out[$key] = '!Foto "' . genc_e((string) $name) . '" lebih dari 12 MB.'; continue; }
			$out[$key] = $tmp;
		}
		return $out;
	}

	/** Simpan foto item (lebar maks 900 px, JPG) ke uploads/mesin/<page>/. @return string path relatif atau '' */
	protected function mesin_save_photo($tmp, $page, $db){
		$dir = 'uploads/mesin/' . $page;
		if(!is_dir(ROOT . $dir) && !@mkdir(ROOT . $dir, 0775, true)){ return ''; }
		$file = $dir . '/' . $db . '-' . substr(md5(uniqid('', true)), 0, 8) . '.jpg';
		$ok = false;
		if(function_exists('imagecreatefromstring')){
			$src = @imagecreatefromstring((string) @file_get_contents($tmp));
			if($src){
				// EXIF orientasi (foto HP)
				if(function_exists('exif_read_data') && function_exists('imagerotate')){
					$ex = @exif_read_data($tmp);
					$o = isset($ex['Orientation']) ? (int) $ex['Orientation'] : 1;
					if($o === 3){ $src = imagerotate($src, 180, 0); } elseif($o === 6){ $src = imagerotate($src, -90, 0); } elseif($o === 8){ $src = imagerotate($src, 90, 0); }
				}
				$w = imagesx($src); $h = imagesy($src);
				$nw = min(900, $w); $nh = max(1, (int) round($h * $nw / max(1, $w)));
				$dst = imagecreatetruecolor($nw, $nh);
				imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
				imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
				$ok = @imagejpeg($dst, ROOT . $file, 82);
				imagedestroy($dst); imagedestroy($src);
			}
		}
		if(!$ok){ $ok = @move_uploaded_file($tmp, ROOT . $file); }
		return $ok ? $file : '';
	}

	/** Hapus foto unggahan lama (hanya di uploads/mesin/<page>/, foto bawaan assets/ tidak disentuh). */
	protected function mesin_unlink_photo($path, $page){
		$path = str_replace('\\', '/', (string) $path);
		if(strpos($path, 'uploads/mesin/' . $page . '/') !== 0 || strpos($path, '..') !== false){ return; }
		if(is_file(ROOT . $path)){ @unlink(ROOT . $path); }
	}

	// =====================================================================
	//  TAMPILAN
	// =====================================================================

	protected function mesin_render($data){
		$data['can'] = array('add' => ACL::is_allowed('mesin/add'), 'edit' => ACL::is_allowed('mesin/edit'), 'delete' => ACL::is_allowed('mesin/delete'),
			'perm' => ACL::is_allowed('role_permissions/list'));
		$data['copy_sources'] = $this->mesin_copy_sources();
		$data['acl_sources']  = $this->mesin_acl_sources();
		$data['icons']        = genc_reg_icons();
		$data['sections']     = genc_reg_sections();
		// isi checklist untuk panel kanan (folder bawaan: hanya dibaca)
		$data['preview'] = array();
		$data['isi'] = null;
		if(!isset($data['tipe'])){ $data['tipe'] = ''; }
		if(!empty($data['sel'])){
			$f = $data['sel']['folder'];
			$p = $f['jenis'] ? $f['key'] : ($f['mode'] === 'units' ? $f['def']['types'][0]['profile'] : $f['def']['profile']);
			$types = array();
			if(!$f['jenis'] && $f['mode'] === 'units'){
				foreach($f['def']['types'] as $t){ $types[$t['profile']] = $t['label']; }
				if($data['tipe'] !== '' && isset($types[$data['tipe']])){ $p = $data['tipe']; }
			}
			if(!empty($data['sel']['unit']) && $data['sel']['unit']['profile'] !== ''){ $p = $data['sel']['unit']['profile']; }
			try { $c = checklist_load_machine($p); $data['preview'] = $c['sections']; } catch(\Throwable $e){ $c = null; }
			// [GENC-06OKT26-ISI] editor isi checklist + kunci
			$ov = genc_reg_ov_row($p);
			$base = $f['jenis'] ? null : genc_reg_base_conf($p);
			$slots = array(); $slot_names = genc_reg_photo_slots();
			if($c){
				foreach($c['sections'] as $s){
					$k = isset($s['photo']) ? (string) $s['photo'] : '';
					if($k === '' || !isset($slot_names[$k])){ continue; }
					$cur = isset($c['images'][$k]) ? (string) $c['images'][$k] : '';
					$orig = ($base && isset($base['images'][$k])) ? (string) $base['images'][$k] : '';
					$ovg = genc_reg_ov_gambar($p);
					$slots[$k] = array('label' => isset($s['short']) ? $s['short'] : $s['title'], 'cur' => ($cur !== '' && is_file(ROOT . $cur)) ? $cur : '',
						'orig' => ($orig !== '' && is_file(ROOT . $orig)) ? $orig : '', 'is_ov' => isset($ovg[$k]));
				}
			}
			$data['isi'] = array(
				'profile'  => $p,
				'label'    => $this->mesin_profile_label($p),
				'types'    => $types,
				'locked'   => genc_reg_locked($p),
				'kunci_by' => $ov && $ov['kunci_by'] ? $ov['kunci_by'] : '',
				'kunci_at' => $ov && $ov['kunci_at'] ? $ov['kunci_at'] : '',
				'upd_by'   => $ov && $ov['updated_by'] && ($ov['items'] !== null || $ov['gambar'] !== null) ? $ov['updated_by'] : '',
				'upd_at'   => $ov && $ov['updated_at'] ? $ov['updated_at'] : '',
				'changed'  => $ov && ((string) $ov['items'] !== '' || (string) $ov['gambar'] !== ''),
				'rows'     => $f['jenis'] ? null : genc_reg_isi_rows($p),
				'slots'    => $slots,
				'tables'   => $f['jenis'] ? array($p) : genc_reg_profile_tables($p),
				'ready'    => genc_reg_data()['profil_ok'],
			);
		}
		$this->view->page_title = "Mesin & Unit";
		return $this->render_view("mesin/index.php", $data);
	}

	protected function mesin_toast($msg, $type = 'success'){
		$icon = array('success' => 'fa-check-circle', 'danger' => 'fa-exclamation-circle', 'info' => 'fa-info-circle');
		$i = isset($icon[$type]) ? $icon[$type] : 'fa-info-circle';
		$this->set_flash_msg('<div class="genc-toast genc-toast--' . $type . '" role="status"><i class="fa ' . $i . '"></i><span>' . $msg . '</span>'
			. '<button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>', 'custom');
	}
}
