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
 *    mesin/edit/<jenis|unit|bawaan|folder|isi|kunci|reset>/<id>  (POST) -> edit()
 *    mesin/delete/<jenis|folder|unit|bawaan>/<id>   (POST) -> delete()   pindah ke Arsip (bisa dipulihkan)
 *    mesin/delete/permanen/x  (POST, sel + konfirmasi)    -> delete()   [GENC-13OKT26] HAPUS PERMANEN dari Arsip
 *                                                          (cadangan .zip dibuat dulu; gagal = batal)
 *    mesin?pilih=cadangan                                  -> daftar cadangan hapus permanen
 *    mesin?cadangan=<id>[&file=<path>]                    -> unduh cadangan (hak akses mesin/delete)
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
require_once __DIR__ . '/../views/partials/_shared/genc_cadangan.php';   // [GENC-13OKT26] cadangan sebelum hapus permanen

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
		if(!$rd['ok'] || !$rd['profil_ok'] || !$rd['folder_ok']){ $setup_err = genc_reg_ensure_tables(); }   // [GENC-08OKT26-MESIN] + genc_folder   // [GENC-06OKT26-ISI] + genc_profil   // tabel registry dibuat otomatis (sama dengan SQL)
		$req = $this->request;
		// [GENC-13OKT26] parameter URL selalu teks (?pilih[]=... dari URL yang diutak-atik -> diabaikan, bukan Warning)
		$q = function($k) use ($req){ return (isset($req->$k) && is_scalar($req->$k)) ? (string) $req->$k : ''; };
		if($q('cadangan') !== ''){ return $this->mesin_cadangan_unduh($q('cadangan'), $q('file')); }
		$sel = $q('pilih');
		$new = strtolower($q('baru'));
		$tree = $this->mesin_tree();
		$data = array(
			'tree'      => $tree,
			'sel'       => $this->mesin_resolve($tree, $sel),
			'sel_raw'   => $sel,
			'new'       => in_array($new, array('jenis', 'unit'), true) ? $new : '',
			'new_folder'=> $q('folder'),
			'tipe'      => $q('tipe'),
			'old'       => array(),
			'errors'    => array(),
			'setup_err' => $setup_err,
			'panel'     => $sel === 'cadangan' ? 'cadangan' : '',                       // [GENC-13OKT26] daftar cadangan hapus permanen
			'cad_siap'  => $q('siap'),
		);
		return $this->mesin_render($data);
	}

	/** POST: jenis=1 (jenis mesin baru) atau unit=1 (unit baru). */
	function add($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ return $this->redirect('mesin'); }
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect('mesin'); }
		$this->mesin_ver_watch($this->mesin_ver_keys('add', '', '', $formdata), !empty($formdata['jenis']));   // [GENC-08OKT26-P3] riwayat versi
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
		$k = $this->mesin_ver_keys('edit', (string) $kind, $id, $formdata);
		// [GENC-08OKT26-P3] riwayat versi: perubahan apa pun pada mesin ini dicatat.
		// [GENC-13OKT26] KECUALI pulihkan dari Arsip, pindah versi & kunci / buka kunci: bukan perubahan isi -> tidak menambah versi
		// (kunci = pengaman, bukan isi: Kembalikan tidak pernah mengunci / membuka kunci).
		if($k !== '' && !in_array((string) $kind, array('pulihkan', 'versi', 'kunci'), true)){ $this->mesin_ver_watch($k); }
		if($kind === 'jenis'){ return $this->mesin_edit_jenis((string) $id, $formdata); }
		if($kind === 'unit'){ return $this->mesin_edit_unit((int) $id, $formdata); }
		if($kind === 'bawaan'){ return $this->mesin_edit_bawaan((string) $id, $formdata); }
		if($kind === 'folder'){ return $this->mesin_edit_folder((string) $id, $formdata); }   // [GENC-08OKT26-P3] pengaturan mesin (semua mesin)
		if($kind === 'pulihkan'){ return $this->mesin_unarchive($formdata); }                 // [GENC-08OKT26-P3] keluar dari Arsip
		if($kind === 'versi'){ return $this->mesin_ver_restore((int) $id); }                  // [GENC-08OKT26-P3] kembalikan ke versi
		if($kind === 'isi'){ return $this->mesin_edit_isi((string) $id, $formdata); }       // [GENC-06OKT26-ISI]
		if($kind === 'kunci'){ return $this->mesin_edit_kunci((string) $id, $formdata); }
		if($kind === 'reset'){ return $this->mesin_reset_isi((string) $id); }
		return $this->redirect('mesin');
	}

	/**
	 * POST: delete/<jenis|folder|unit|bawaan>/<id> (+ slug untuk unit awal). [GENC-08OKT26-P3] "Hapus" = pindah ke
	 * Arsip (bisa dipulihkan) - tabel, data, hak akses & foto tetap.
	 * [GENC-13OKT26] Masuk / keluar Arsip TIDAK menambah versi di Riwayat perubahan (bukan perubahan isi).
	 * POST delete/permanen/x (sel + konfirmasi nama): HAPUS PERMANEN dari Arsip, cadangan .zip dibuat dulu.
	 */
	function delete($kind = null, $id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ return $this->redirect('mesin'); }
		if(!in_array($kind, array('jenis', 'folder', 'unit', 'bawaan', 'permanen'), true)){ return $this->redirect('mesin'); }
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect('mesin'); }
		if($kind === 'permanen'){ return $this->mesin_purge($formdata); }
		return $this->mesin_archive((string) $kind, $kind === 'unit' ? (int) $id : (string) $id, $formdata);
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
				'menu' => $f['menu'], 'def' => $f, 'units' => array(), 'count' => 0, 'aktif' => $f['aktif'], 'created_at' => null, 'arsip' => $f['arsip'],
				'hapus' => !empty($f['hapus']));   // [GENC-13OKT26] dihapus permanen: tidak tampil di mana pun (nama boleh dipakai mesin baru)
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
						'sub' => $t['sub'], 'type' => '', 'profile' => $f['profile'], 'page' => $p, 'slug' => '', 'aktif' => !($note && (int) $note['aktif'] === 0),   // [GENC-08OKT26-MESIN]
						'count' => (int) genc_reg_table_count($p), 'norms' => array(checklist_norm_unit($label), checklist_norm_unit($t['label'])), 'kode' => $t['sub'], 'created_at' => null,
						'arsip' => $note && !empty($note['arsip']), 'note_slug' => '',   // [GENC-08OKT26-P3]
						'hapus' => $note && (int) $note['arsip'] === GENC_REG_HAPUS);   // [GENC-13OKT26]
				}
				foreach(genc_reg_units_of_folder($k) as $id => $r){
					$node['units'][] = array('sel' => 'u:' . $id, 'kind' => 'unit', 'id' => $id, 'label' => $r['label'], 'tab' => $r['label'],
						'sub' => $r['kode'], 'type' => '', 'profile' => $f['profile'], 'page' => $r['page'], 'slug' => '', 'aktif' => (int) $r['aktif'] === 1,
						'count' => (int) genc_reg_table_count($r['page']), 'norms' => array(checklist_norm_unit($r['label'])), 'kode' => $r['kode'], 'created_at' => $r['created_at'],
						'arsip' => !empty($r['arsip']), 'note_slug' => '', 'hapus' => false);
				}
			}
			foreach($node['units'] as $u){ if(empty($u['hapus'])){ $node['count'] += $u['count']; } }   // [GENC-13OKT26] unit dihapus permanen tidak dihitung
			$out[$k] = $node;
		}
		foreach(genc_reg_data()['mesin'] as $p => $m){
			$node = array('key' => $p, 'title' => $m['nama'], 'icon' => $m['ikon'], 'mode' => 'units', 'builtin' => false, 'jenis' => true,
				'menu' => $p, 'mesin' => $m, 'units' => array(), 'count' => 0, 'aktif' => (int) $m['aktif'] === 1, 'items' => genc_reg_item_count($m), 'created_at' => $m['created_at'], 'arsip' => !empty($m['arsip']), 'hapus' => false);
			$counts = genc_reg_unit_counts($p, 'unit');
			$t = array('profile' => $p, 'page' => $p, 'label' => '');
			foreach(genc_reg_units_of_folder($p) as $id => $r){
				$u = array('label' => $r['label'], 'alias' => $r['alias_arr'], 'reg_id' => $id);
				if((int) $r['aktif'] === 0){ $u['inactive'] = true; }
				if(!empty($r['arsip'])){ $u['archived'] = true; }
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
		$slug = !empty($u['slug']) ? (string) $u['slug'] : genc_reg_unit_slug($label);   // [GENC-08OKT26-MESIN] unit awal yang diganti nama: slug tetap
		$dyn = !empty($u['reg_id']);
		$all = genc_reg_data()['unit'];
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
			'default_label' => isset($u['label_awal']) ? (string) $u['label_awal'] : $label,   // [GENC-08OKT26-MESIN] nama awal (kode)
			'created_at'    => ($dyn && isset($all[(int) $u['reg_id']])) ? $all[(int) $u['reg_id']]['created_at'] : null,
			'arsip'         => !empty($u['archived']),   // [GENC-08OKT26-P3]
			'note_slug'     => $dyn ? '' : $slug,
			'hapus'         => !empty($u['deleted']),    // [GENC-13OKT26] unit awal dihapus permanen (nisan)
		);
	}

	/** Label yang tampil di tab = nama unit utuh. [GENC-08OKT26-P3] dulu disingkat (PM 1, TTL 6, Stacker). */
	protected function mesin_tab_label($folder, $label){
		return $label;
	}

	/** Pilihan di URL -> array('type' => 'folder'|'unit', 'folder' => node, 'unit' => node|null) atau null. */
	protected function mesin_resolve($tree, $sel){
		if($sel === ''){ return null; }
		if(strpos($sel, 'f:') === 0){
			$k = substr($sel, 2);
			return (isset($tree[$k]) && empty($tree[$k]['hapus'])) ? array('type' => 'folder', 'folder' => $tree[$k], 'unit' => null) : null;   // [GENC-13OKT26]
		}
		foreach($tree as $f){
			if(!empty($f['hapus'])){ continue; }
			foreach($f['units'] as $u){ if($u['sel'] === $sel && empty($u['hapus'])){ return array('type' => 'unit', 'folder' => $f, 'unit' => $u); } }
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
			// [GENC-13OKT26] unit awal yang dihapus permanen tetap menahan namanya: datanya masih di tabel asli
			// (nama sama = checklist lama ikut unit baru tanpa sengaja)
			if(in_array($n, $u['norms'], true)){ return $u['label'] . (!empty($u['hapus']) ? ' (sudah dihapus permanen, datanya masih tersimpan)' : ''); }
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
			'perm'   => $this->mesin_perm_post($fd),   // [GENC-08OKT26-AKSES] hak akses awal per jabatan (role)
			'pelaksanaan' => genc_reg_clean(isset($fd['pelaksanaan']) ? $fd['pelaksanaan'] : '', 150),
		);
		$errors = array();
		if(function_exists('mb_strlen') ? mb_strlen($old['nama'], 'UTF-8') < 2 : strlen($old['nama']) < 2){ $errors['nama'] = 'Isi nama mesin (minimal 2 huruf).'; }
		elseif(!preg_match('/[A-Za-z0-9]/', $old['nama'])){ $errors['nama'] = 'Nama harus berisi huruf / angka.'; }
		else{
			foreach($tree as $f){ if(empty($f['hapus']) && strcasecmp(trim($f['title']), $old['nama']) === 0){ $errors['nama'] = 'Nama ini sudah dipakai mesin "' . genc_e($f['title']) . '".'; } }
		}
		if(!in_array($old['ikon'], genc_reg_icons(), true)){ $old['ikon'] = 'fa-cube'; }
		if($old['unit'] === ''){ $errors['unit_label'] = 'Isi nama unit pertama (contoh: ' . genc_e($old['nama'] !== '' ? $old['nama'] : 'Hand Pallet') . ' 1).'; }
		elseif($e = $this->mesin_unit_label_error($old['unit'])){ $errors['unit_label'] = $e; }
		$sources = $this->mesin_copy_sources();
		if($old['salin'] !== '' && !isset($sources[$old['salin']])){ $old['salin'] = ''; }
		$has_list = false; foreach($old['perm'] as $acts){ if(in_array('list', $acts, true)){ $has_list = true; } }
		if(!$has_list){ $errors['akses'] = 'Centang minimal 1 jabatan yang boleh melihat mesin ini.'; }
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
					// [GENC-07OKT26-FOTO] semua foto item ikut disalin
					$fotos = array();
					foreach(genc_reg_fotos_of($it) as $fp){
						if(strpos($fp, 'uploads/mesin/') === 0){
							$dst = 'uploads/mesin/' . $page . '/' . $it['db'] . '-' . substr(md5(uniqid('', true)), 0, 8) . '.jpg';
							$fp = ((is_dir(ROOT . 'uploads/mesin/' . $page) || @mkdir(ROOT . 'uploads/mesin/' . $page, 0775, true)) && @copy(ROOT . $fp, ROOT . $dst)) ? $dst : '';
						}
						if($fp !== ''){ $fotos[] = $fp; }
					}
					$it['fotos'] = $fotos;
					$it['foto'] = $fotos ? $fotos[0] : '';
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
		list($acl_n, $acl_txt) = $this->mesin_perm_save($page, $old['perm']);
		genc_reg_data(true);
		genc_app_log('mesin.add', 'genc_mesin', $mid, 'Tambah mesin ' . $old['nama'] . ' (' . $seq . ' item' . ($old['salin'] !== '' ? ' disalin dari ' . $sources[$old['salin']] : '') . ', unit ' . $old['unit'] . ')',
			array('page' => $page, 'nama' => $old['nama'], 'area' => $old['area'], 'item' => $seq, 'unit' => $old['unit'], 'hak_akses' => $acl_txt, 'hak_akses_baris' => $acl_n));
		$msg = 'Mesin <b>' . genc_e($old['nama']) . '</b> dibuat.';
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
		if($r = $this->mesin_lock_guard($node, 'mesin?pilih=' . rawurlencode('f:' . $fk), 'ditambah unit')){ return $r; }   // [GENC-08OKT26-P3]
		if(!empty($node['arsip'])){ $this->mesin_toast('Mesin ini ada di Arsip &mdash; pulihkan dulu.', 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $fk)); }
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
		if(!empty($u['arsip'])){ $this->mesin_toast('Unit ini ada di Arsip &mdash; pulihkan dulu.', 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode('u:' . $id)); }
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
		if(!$old['aktif'] && $u['aktif'] && !$this->mesin_other_active($f, $u['sel'])){ $errors['aktif'] = $this->mesin_last_active_msg($f); }
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
		if(array_diff(array_keys($diff), array('status')) && ($r = $this->mesin_lock_guard($f, 'mesin?pilih=' . rawurlencode('u:' . $id), 'diubah (selain saklar Aktif)'))){ return $r; }   // [GENC-08OKT26-P3]
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

	/**
	 * [GENC-08OKT26-MESIN] Unit awal (dari kode) - SAMA dengan unit lain: nama unit + aktif / nonaktif.
	 * Disimpan sebagai baris catatan genc_unit (builtin=1): label '' = nama awal, aktif 0 = nonaktif.
	 * Nama dikosongkan + aktif = catatan dihapus (kembali persis seperti awal).
	 *  - 1 tabel banyak unit (Pallet Mover, Forklift, AGV): slug / URL tetap, nama awal & nama-nama
	 *    sebelumnya jadi alias -> data lama tetap ikut unit ini.
	 *  - 1 tabel per unit (Dumping, Geprek, Conveyor): data per tabel, nama = nama tab.
	 */
	protected function mesin_edit_bawaan($page, $fd){
		$tree = $this->mesin_tree();
		$slug = isset($fd['slug']) ? (string) $fd['slug'] : '';
		$sel = 'b:' . $page . ($slug !== '' ? ':' . $slug : '');
		$hit = $this->mesin_resolve($tree, $sel);
		if(!$hit || $hit['type'] !== 'unit' || $hit['unit']['kind'] !== 'bawaan'){ $this->mesin_toast('Unit tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $hit['folder']; $u = $hit['unit'];
		if(!empty($u['arsip'])){ $this->mesin_toast('Unit ini ada di Arsip &mdash; pulihkan dulu.', 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode($sel)); }
		$note = genc_reg_builtin_note($page, $slug);
		$def = $u['default_label'];
		$old = array(
			'label' => genc_reg_clean(isset($fd['label']) ? $fd['label'] : '', 60),
			'aktif' => !empty($fd['aktif']) ? 1 : 0,
		);
		$errors = array();
		if($old['label'] !== ''){
			if($e = $this->mesin_unit_label_error($old['label'])){ $errors['label'] = $e; }
			elseif($t = $this->mesin_label_taken($f, $old['label'], $u['sel'])){ $errors['label'] = 'Nama ini sama dengan unit "' . genc_e($t) . '" yang sudah ada.'; }
		}
		if(!$old['aktif'] && $u['aktif'] && !$this->mesin_other_active($f, $u['sel'])){ $errors['aktif'] = $this->mesin_last_active_msg($f); }
		if($errors){ return $this->mesin_render(array('tree' => $tree, 'sel' => $hit, 'sel_raw' => $sel, 'new' => '', 'new_folder' => '', 'old' => $old, 'errors' => $errors, 'setup_err' => '')); }

		$new_label = ($old['label'] === '' || checklist_norm_unit($old['label']) === checklist_norm_unit($def)) ? '' : $old['label'];
		$cur_label = ($note && trim($note['label']) !== '' && checklist_norm_unit($note['label']) !== checklist_norm_unit($def)) ? $note['label'] : '';
		$cur_aktif = $note ? (int) $note['aktif'] : 1;
		$diff = array();
		if($new_label !== $cur_label){ $diff['nama'] = ($cur_label !== '' ? $cur_label : $def) . ' -> ' . ($new_label !== '' ? $new_label : $def); }
		if($old['aktif'] !== $cur_aktif){ $diff['status'] = $old['aktif'] ? 'aktif' : 'nonaktif'; }
		if(empty($diff)){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect('mesin?pilih=' . rawurlencode($sel)); }
		if(array_diff(array_keys($diff), array('status')) && ($r = $this->mesin_lock_guard($f, 'mesin?pilih=' . rawurlencode($sel), 'diubah (selain saklar Aktif)'))){ return $r; }   // [GENC-08OKT26-P3]
		// nama-nama sebelumnya tetap dikenali (data yang sempat diisi dengan nama itu tetap ikut unit ini)
		$alias = $note ? $note['alias_arr'] : array();
		if($f['mode'] === 'units' && $cur_label !== '' && $new_label !== $cur_label && !in_array($cur_label, $alias, true)){ $alias[] = $cur_label; }
		$pdo = genc_reg_db();
		$id = $note ? (int) $note['id'] : 0;
		if($new_label === '' && $old['aktif'] === 1 && empty($alias)){
			if($note){ $pdo->prepare("DELETE FROM genc_unit WHERE id = ?")->execute(array($id)); }   // kembali persis seperti awal
		}
		elseif($note){
			$pdo->prepare("UPDATE genc_unit SET label = ?, alias = ?, aktif = ?, updated_at = ?, updated_by = ? WHERE id = ?")
				->execute(array($new_label, json_encode(array_values($alias)), $old['aktif'], datetime_now(), USER_NAME, $id));
		}
		else{
			$pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, urut, created_at, created_by, updated_at, updated_by) VALUES (?,?,?,?,?,'',?,1,?,0,?,?,?,?)")
				->execute(array($f['key'], $u['profile'], $page, $slug, $new_label, json_encode(array_values($alias)), $old['aktif'], datetime_now(), USER_NAME, datetime_now(), USER_NAME));
			$id = (int) $pdo->lastInsertId();
		}
		genc_reg_data(true);
		$note_txt = array(); foreach($diff as $k => $v){ $note_txt[] = $k . ': ' . $v; }
		genc_app_log('mesin.unit.edit', 'genc_unit', $id, 'Ubah unit ' . $u['label'] . ' (' . $f['title'] . ') - ' . implode('; ', $note_txt), $diff + array('halaman' => $page, 'unit' => $slug));
		$shown = $new_label !== '' ? $new_label : $def;
		$msg = 'Perubahan unit <b>' . genc_e($shown) . '</b> tersimpan.';
		if(isset($diff['status'])){ $msg .= $old['aktif'] ? ' Unit aktif lagi.' : ' Unit disembunyikan dari tab, Home &amp; form isi; datanya tetap.'; }
		if(isset($diff['nama']) && $new_label === ''){ $msg .= ' Nama kembali ke nama awal.'; }
		$this->mesin_toast($msg, 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode($sel));
	}

	/** Pesan "unit aktif terakhir" (tidak bisa dinonaktifkan; nonaktifkan mesinnya). */
	protected function mesin_last_active_msg($f){
		return 'Ini satu-satunya unit aktif di ' . genc_e($f['title']) . ' &mdash; tidak bisa dinonaktifkan. Untuk menyembunyikan seluruh mesin, matikan <i>Aktif</i> di pengaturan mesin ' . genc_e($f['title']) . '.';
	}

	/**
	 * [GENC-08OKT26-P3] Pengaturan mesin - SAMA untuk semua mesin (mesin awal & mesin baru): nama, area, pelaksanaan,
	 * ikon, aktif. Mesin awal: disimpan di genc_folder (kosong = nilai awal; semua awal = baris dihapus).
	 * Mesin baru: genc_mesin. Terkunci: hanya saklar Aktif yang bisa diubah.
	 */
	protected function mesin_edit_folder($key, $fd){
		$tree = $this->mesin_tree();
		$back = 'mesin?pilih=' . rawurlencode('f:' . $key);
		if(!isset($tree[$key])){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect($back); }
		$tree = $this->mesin_tree();
		$f = $tree[$key];
		if(!empty($f['arsip'])){ $this->mesin_toast('Mesin ini ada di Arsip &mdash; pulihkan dulu.', 'danger'); return $this->redirect($back); }
		$cur = $this->mesin_folder_ident($f);
		$old = array(
			'nama'        => genc_reg_clean(isset($fd['nama']) ? $fd['nama'] : '', 60),
			'area'        => genc_reg_clean(isset($fd['area']) ? $fd['area'] : '', 100),
			'pelaksanaan' => genc_reg_clean(isset($fd['pelaksanaan']) ? $fd['pelaksanaan'] : '', 150),
			'ikon'        => isset($fd['ikon']) && in_array((string) $fd['ikon'], genc_reg_icons(), true) ? (string) $fd['ikon'] : $f['icon'],
			'aktif'       => !empty($fd['aktif']) ? 1 : 0,
		);
		$errors = array();
		$nama_eff = $old['nama'] !== '' ? $old['nama'] : ($f['builtin'] ? $f['def']['title_awal'] : '');
		if($f['jenis'] && (function_exists('mb_strlen') ? mb_strlen($old['nama'], 'UTF-8') < 2 : strlen($old['nama']) < 2)){ $errors['nama'] = 'Isi nama mesin (minimal 2 huruf).'; }
		elseif($old['nama'] !== '' && !preg_match('/[A-Za-z0-9]/', $old['nama'])){ $errors['nama'] = 'Nama harus berisi huruf / angka.'; }
		else{ foreach($tree as $k => $x){ if($k !== $key && empty($x['hapus']) && strcasecmp(trim($x['title']), $nama_eff) === 0){ $errors['nama'] = 'Nama ini sudah dipakai mesin "' . genc_e($x['title']) . '".'; } } }
		if($f['jenis'] && $old['aktif'] && $f['items'] === 0){ $errors['aktif'] = 'Tambahkan minimal 1 item checklist dulu, baru mesin bisa diaktifkan.'; }
		// nilai baru (mesin awal: sama dengan awal = disimpan kosong)
		$new = array('nama' => $nama_eff, 'area' => $old['area'], 'pelaksanaan' => $old['pelaksanaan'], 'ikon' => $old['ikon'], 'aktif' => $old['aktif']);
		if($f['builtin']){
			if($new['area'] === '') { $new['area'] = $cur['area_awal']; }
			if($new['pelaksanaan'] === '') { $new['pelaksanaan'] = $cur['pelaksanaan_awal']; }
		}
		$diff = array();
		$names = array('nama' => 'nama', 'area' => 'area', 'pelaksanaan' => 'pelaksanaan', 'ikon' => 'ikon');
		foreach($names as $k => $lbl){ if((string) $new[$k] !== (string) $cur[$k]){ $diff[$lbl] = ($cur[$k] === '' ? '-' : $cur[$k]) . ' -> ' . ($new[$k] === '' ? '-' : $new[$k]); } }
		if($new['aktif'] !== ($f['aktif'] ? 1 : 0)){ $diff['status'] = $new['aktif'] ? 'aktif' : 'nonaktif'; }
		if(!$errors && $this->mesin_folder_lock($f) === 'all' && array_diff(array_keys($diff), array('status'))){
			$errors['nama'] = 'Mesin ini sedang <b>dikunci</b> &mdash; hanya saklar Aktif yang bisa diubah. Buka kunci dulu.';
		}
		if($errors){ return $this->mesin_render(array('tree' => $tree, 'sel' => array('type' => 'folder', 'folder' => $f, 'unit' => null), 'sel_raw' => 'f:' . $key, 'new' => '', 'new_folder' => '', 'old' => $old + array('_form' => 'folder'), 'errors' => $errors, 'setup_err' => '')); }
		if(empty($diff)){ $this->mesin_toast('Tidak ada yang berubah.', 'info'); return $this->redirect($back); }
		$pdo = genc_reg_db();
		if($f['jenis']){
			$pdo->prepare("UPDATE genc_mesin SET nama = ?, area = ?, ikon = ?, pelaksanaan = ?, aktif = ?, updated_at = ?, updated_by = ? WHERE page = ?")
				->execute(array($new['nama'], $new['area'], $new['ikon'], $new['pelaksanaan'], $new['aktif'], datetime_now(), USER_NAME, $key));
		}
		else{
			$def = $f['def'];
			$v = array(
				'nama'        => $new['nama'] === $def['title_awal'] ? null : $new['nama'],
				'area'        => $new['area'] === $cur['area_awal'] ? null : $new['area'],
				'pelaksanaan' => $new['pelaksanaan'] === $cur['pelaksanaan_awal'] ? null : $new['pelaksanaan'],
				'ikon'        => $new['ikon'] === $def['icon_awal'] ? null : $new['ikon'],
			);
			if($new['aktif'] === 1 && $v['nama'] === null && $v['area'] === null && $v['pelaksanaan'] === null && $v['ikon'] === null){
				$pdo->prepare("DELETE FROM genc_folder WHERE folder = ?")->execute(array($key));   // kembali persis seperti awal
			}
			else{
				$pdo->prepare("INSERT INTO genc_folder (folder, ikon, aktif, nama, area, pelaksanaan, arsip, updated_at, updated_by) VALUES (?,?,?,?,?,?,0,?,?)
					ON DUPLICATE KEY UPDATE ikon = VALUES(ikon), aktif = VALUES(aktif), nama = VALUES(nama), area = VALUES(area), pelaksanaan = VALUES(pelaksanaan), updated_at = VALUES(updated_at), updated_by = VALUES(updated_by)")
					->execute(array($key, $v['ikon'], $new['aktif'], $v['nama'], $v['area'], $v['pelaksanaan'], datetime_now(), USER_NAME));
			}
		}
		genc_reg_data(true);
		$note = array(); foreach($diff as $k => $x){ $note[] = $k . ': ' . $x; }
		genc_app_log('mesin.edit', $f['jenis'] ? 'genc_mesin' : 'genc_folder', $f['jenis'] ? $f['mesin']['id'] : 0, 'Ubah mesin ' . $f['title'] . ' - ' . implode('; ', $note), $diff + array('mesin' => $key));
		$msg = 'Perubahan <b>' . genc_e($new['nama']) . '</b> tersimpan.';
		if(isset($diff['nama'])){ $msg .= ' Nama baru dipakai di menu, Home, Approval / NOK History' . ($f['builtin'] ? ', judul halaman &amp; judul report' : ' &amp; report') . '.'; }
		if(isset($diff['status'])){ $msg .= $new['aktif'] ? ' Mesin tampil lagi di menu &amp; Home.' : ' Mesin disembunyikan dari menu, Home &amp; form isi; datanya tetap (Approval / NOK History tetap menampilkan yang belum beres).'; }
		$this->mesin_toast($msg, 'success');
		return $this->redirect($back);
	}

	/** [GENC-08OKT26-P3] Nilai pengaturan mesin sekarang + nilai awal (mesin awal: dari profil report). */
	protected function mesin_folder_ident($f){
		if($f['jenis']){
			$m = $f['mesin'];
			return array('nama' => $m['nama'], 'area' => (string) $m['area'], 'pelaksanaan' => (string) $m['pelaksanaan'], 'ikon' => $m['ikon'],
				'area_awal' => '', 'pelaksanaan_awal' => '', 'nama_awal' => '');
		}
		$def = $f['def'];
		$p = $def['mode'] === 'units' ? $def['types'][0]['profile'] : $def['profile'];
		$base = genc_reg_base_conf($p);
		$area_awal = $base && isset($base['area']) ? (string) $base['area'] : '';
		$pel_awal = $base && isset($base['pelaksanaan']) ? (string) $base['pelaksanaan'] : '';
		return array('nama' => $f['title'], 'area' => $def['area'] !== null ? $def['area'] : $area_awal, 'pelaksanaan' => $def['pelaksanaan'] !== null ? $def['pelaksanaan'] : $pel_awal,
			'ikon' => $f['icon'], 'area_awal' => $area_awal, 'pelaksanaan_awal' => $pel_awal, 'nama_awal' => $def['title_awal']);
	}

	/** [GENC-08OKT26-P3] Profil isi checklist milik sebuah folder. */
	protected function mesin_folder_profiles($f){
		if($f['jenis']){ return array($f['key']); }
		return $f['mode'] === 'units' ? array_values(array_unique(array_map(function($t){ return $t['profile']; }, $f['def']['types']))) : array($f['def']['profile']);
	}

	/** [GENC-08OKT26-P3] Status kunci mesin: 'all' (dikunci) | 'some' (sebagian, data lama) | ''. */
	protected function mesin_folder_lock($f){
		$ps = $this->mesin_folder_profiles($f);
		$n = 0; foreach($ps as $p){ if(genc_reg_locked($p)){ $n++; } }
		return $n === 0 ? '' : ($n === count($ps) ? 'all' : 'some');
	}

	/**
	 * [GENC-08OKT26-P3] Pesan + redirect kalau mesin dikunci. @return bool true = DIBLOKIR (pemanggil harus berhenti).
	 * Catatan: $this->redirect() phpRAD mengembalikan null (tidak exit), jadi jangan pakai nilai baliknya sebagai penanda.
	 */
	protected function mesin_lock_guard($f, $back, $what = 'diubah'){
		if($this->mesin_folder_lock($f) !== 'all'){ return false; }
		$this->mesin_toast('<b>' . genc_e($f['title']) . '</b> sedang <b>dikunci</b> &mdash; tidak bisa ' . $what . '. Buka kunci dulu di halaman mesinnya.', 'danger');
		$this->redirect($back);
		return true;
	}

	// =====================================================================
	//  ARSIP ("hapus" yang aman) + PULIHKAN          [GENC-08OKT26-P3]
	//  Tidak ada hapus permanen dari aplikasi: tabel, data, hak akses & foto tetap.
	//  Arsip = nonaktif + keluar dari sidebar, Home, Approval/NOK, pohon utama & matriks.
	//  Pulihkan = kembali ke pohon sebagai NONAKTIF (aktifkan lagi kalau perlu).
	// =====================================================================

	/** POST delete/<jenis|folder|unit|bawaan>/<id> (+ slug untuk bawaan). */
	protected function mesin_archive($kind, $id, $fd){
		$tree = $this->mesin_tree();
		$pdo = genc_reg_db();
		if($kind === 'jenis' || $kind === 'folder'){
			$back = 'mesin?pilih=' . rawurlencode('f:' . $id);
			if(!isset($tree[$id]) || ($kind === 'jenis') !== $tree[$id]['jenis']){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
			$f = $tree[$id];
			if(!empty($f['arsip'])){ $this->mesin_toast('Sudah ada di Arsip.', 'info'); return $this->redirect($back); }
			if($r = $this->mesin_lock_guard($f, $back, 'dihapus')){ return $r; }
			if($f['jenis']){ $pdo->prepare("UPDATE genc_mesin SET aktif = 0, arsip = 1, updated_at = ?, updated_by = ? WHERE page = ?")->execute(array(datetime_now(), USER_NAME, $id)); }
			else{
				$pdo->prepare("INSERT INTO genc_folder (folder, aktif, arsip, updated_at, updated_by) VALUES (?,0,1,?,?) ON DUPLICATE KEY UPDATE aktif = 0, arsip = 1, updated_at = VALUES(updated_at), updated_by = VALUES(updated_by)")
					->execute(array($id, datetime_now(), USER_NAME));
			}
			genc_reg_data(true);
			genc_app_log('mesin.arsip', $f['jenis'] ? 'genc_mesin' : 'genc_folder', $f['jenis'] ? $f['mesin']['id'] : 0, 'Hapus (arsipkan) mesin ' . $f['title'] . ' - ' . number_format($f['count'], 0, ',', '.') . ' checklist tetap tersimpan', array('mesin' => $id, 'checklist' => $f['count']));
			$this->mesin_toast('<b>' . genc_e($f['title']) . '</b> dipindah ke <b>Arsip</b> &mdash; hilang dari menu, Home &amp; Approval. Data, tabel &amp; hak aksesnya tetap; pulihkan kapan saja dari bagian Arsip.', 'success');
			return $this->redirect($back);
		}
		$sel = $kind === 'unit' ? 'u:' . (int) $id : 'b:' . $id . (isset($fd['slug']) && $fd['slug'] !== '' ? ':' . $fd['slug'] : '');
		$back = 'mesin?pilih=' . rawurlencode($sel);
		$hit = $this->mesin_resolve($tree, $sel);
		if(!$hit || $hit['type'] !== 'unit'){ $this->mesin_toast('Unit tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $hit['folder']; $u = $hit['unit'];
		if(!empty($u['arsip'])){ $this->mesin_toast('Sudah ada di Arsip.', 'info'); return $this->redirect($back); }
		if($r = $this->mesin_lock_guard($f, $back, 'dihapus')){ return $r; }
		if(!$this->mesin_other_active($f, $u['sel'])){
			$this->mesin_toast('<b>' . genc_e($u['label']) . '</b> satu-satunya unit aktif di ' . genc_e($f['title']) . ' &mdash; tidak bisa dihapus. Untuk menyembunyikan seluruh mesin, hapus / nonaktifkan mesinnya.', 'danger');
			return $this->redirect($back);
		}
		if($u['kind'] === 'unit'){ $pdo->prepare("UPDATE genc_unit SET aktif = 0, arsip = 1, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array(datetime_now(), USER_NAME, (int) $u['id'])); }
		else{
			$slug = isset($fd['slug']) ? (string) $fd['slug'] : '';
			$note = genc_reg_builtin_note($u['page'], $slug);
			if($note){ $pdo->prepare("UPDATE genc_unit SET aktif = 0, arsip = 1, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array(datetime_now(), USER_NAME, $note['id'])); }
			else{ $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, arsip, urut, created_at, created_by, updated_at, updated_by) VALUES (?,?,?,?,'','','[]',1,0,1,0,?,?,?,?)")
				->execute(array($f['key'], $u['profile'], $u['page'], $slug, datetime_now(), USER_NAME, datetime_now(), USER_NAME)); }
		}
		genc_reg_data(true);
		genc_app_log('mesin.unit.arsip', 'genc_unit', $u['id'] ? $u['id'] : 0, 'Hapus (arsipkan) unit ' . $u['label'] . ' dari ' . $f['title'] . ' - ' . $u['count'] . ' checklist tetap tersimpan', array('mesin' => $f['key'], 'halaman' => $u['page'], 'unit' => $u['label']));
		$this->mesin_toast('Unit <b>' . genc_e($u['label']) . '</b> dipindah ke <b>Arsip</b> &mdash; datanya tetap; pulihkan kapan saja dari bagian Arsip.', 'success');
		return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $f['key']));
	}

	/** POST edit/pulihkan/x  (form: sel = f:<key> | u:<id> | b:<page>[:<slug>]) -> keluar dari Arsip, sebagai NONAKTIF. */
	protected function mesin_unarchive($fd){
		$sel = isset($fd['sel']) ? (string) $fd['sel'] : '';
		$tree = $this->mesin_tree();
		$hit = $this->mesin_resolve($tree, $sel);
		if(!$hit){ $this->mesin_toast('Tidak ditemukan di Arsip.', 'danger'); return $this->redirect('mesin'); }
		$pdo = genc_reg_db();
		$back = 'mesin?pilih=' . rawurlencode($sel);
		if($hit['type'] === 'folder'){
			$f = $hit['folder'];
			if(empty($f['arsip'])){ $this->mesin_toast('Mesin ini tidak ada di Arsip.', 'info'); return $this->redirect($back); }
			if($f['jenis']){ $pdo->prepare("UPDATE genc_mesin SET arsip = 0, updated_at = ?, updated_by = ? WHERE page = ?")->execute(array(datetime_now(), USER_NAME, $f['key'])); }
			else{ $pdo->prepare("UPDATE genc_folder SET arsip = 0, updated_at = ?, updated_by = ? WHERE folder = ?")->execute(array(datetime_now(), USER_NAME, $f['key'])); }
			$what = 'mesin ' . $f['title'];
			// [GENC-13OKT26] mesin dari file cadangan bisa bernama sama dengan mesin yang dibuat sesudahnya -> ingatkan
			foreach($tree as $k => $x){ if($k !== $f['key'] && empty($x['hapus']) && empty($x['arsip']) && strcasecmp(trim($x['title']), trim($f['title'])) === 0){ $dup = $x['title']; } }
		}
		else{
			$u = $hit['unit']; $f = $hit['folder'];
			if(empty($u['arsip'])){ $this->mesin_toast('Unit ini tidak ada di Arsip.', 'info'); return $this->redirect($back); }
			if($u['kind'] === 'unit'){ $pdo->prepare("UPDATE genc_unit SET arsip = 0, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array(datetime_now(), USER_NAME, (int) $u['id'])); }
			else{
				$note = genc_reg_builtin_note($u['page'], $u['note_slug']);
				if($note){ $pdo->prepare("UPDATE genc_unit SET arsip = 0, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array(datetime_now(), USER_NAME, $note['id'])); }
			}
			$what = 'unit ' . $u['label'] . ' (' . $f['title'] . ')';
		}
		genc_reg_data(true);
		genc_app_log('mesin.pulihkan', 'genc_mesin', 0, 'Pulihkan dari arsip: ' . $what, array('pilih' => $sel));
		$this->mesin_toast('<b>' . genc_e(ucfirst($what)) . '</b> dipulihkan dari Arsip sebagai <b>nonaktif</b> &mdash; nyalakan saklar Aktif kalau sudah dipakai lagi.'
			. (!empty($dup) ? ' Catatan: namanya sama dengan mesin <b>' . genc_e($dup) . '</b> yang sudah ada &mdash; ganti salah satu di Pengaturan mesin.' : ''), 'success');
		return $this->redirect($back);
	}

	// =====================================================================
	//  RIWAYAT VERSI (kembalikan ke versi sebelumnya)          [GENC-08OKT26-P3]
	//  Setiap aksi Mesin & Unit yang mengubah data sebuah mesin otomatis menyimpan
	//  potret lengkapnya (pengaturan mesin, isi checklist + foto + gambar report,
	//  kunci, unit) di genc_versi. Dicek dengan membandingkan potret sebelum &
	//  sesudah request (fungsi penutup PHP) - tidak ada aksi yang terlewat.
	//  Pulihkan = potret ditulis balik (lalu jadi versi baru). Foto tidak pernah
	//  dihapus dari disk, jadi versi lama selalu lengkap.
	// =====================================================================

	const VERSI_MAX = 30;

	/** Folder yang tersentuh sebuah request edit/delete/add. */
	protected function mesin_ver_keys($route, $kind, $id, $fd){
		$d = genc_reg_data();
		$key = '';
		if($route === 'add'){ $key = !empty($fd['unit']) && isset($fd['folder']) ? (string) $fd['folder'] : ''; }
		elseif($kind === 'jenis' || $kind === 'folder'){ $key = (string) $id; }
		elseif($kind === 'unit'){ $key = isset($d['unit'][(int) $id]) ? $d['unit'][(int) $id]['folder'] : ''; }
		elseif($kind === 'bawaan'){ $f = genc_reg_folder_of_page((string) $id); $key = $f ? $f['key'] : ''; }
		elseif(in_array($kind, array('isi', 'kunci', 'reset'), true)){ $key = isset($d['mesin'][(string) $id]) ? (string) $id : genc_reg_folder_of_profile((string) $id); }
		elseif($kind === 'pulihkan'){
			$sel = isset($fd['sel']) ? (string) $fd['sel'] : '';
			if(strpos($sel, 'f:') === 0){ $key = substr($sel, 2); }
			elseif(strpos($sel, 'u:') === 0){ $uid = (int) substr($sel, 2); $key = isset($d['unit'][$uid]) ? $d['unit'][$uid]['folder'] : ''; }
			elseif(strpos($sel, 'b:') === 0){ $parts = explode(':', $sel); $f = genc_reg_folder_of_page(isset($parts[1]) ? $parts[1] : ''); $key = $f ? $f['key'] : ''; }
		}
		elseif($kind === 'versi'){ $v = $this->mesin_ver_row((int) $id); $key = $v ? $v['folder'] : ''; }
		return $key;
	}

	/** Mulai mengawasi perubahan 1 folder ('' + $new_jenis = folder yang baru dibuat di request ini). */
	protected function mesin_ver_watch($key, $new_jenis = false){
		if(genc_reg_ensure_tables() !== ''){ return; }
		$before_keys = array_keys(genc_reg_data(true)['mesin']);
		$s0 = $key !== '' ? $this->mesin_ver_snapshot($key) : null;
		// closure di dalam method kelas terikat ke $this -> boleh memanggil method protected (tanpa method public baru)
		register_shutdown_function(function() use ($key, $s0, $new_jenis, $before_keys){
			try{
				genc_reg_data(true);
				$keys = array();
				if($key !== ''){ $keys[$key] = $s0; }
				if($new_jenis){ foreach(array_diff(array_keys(genc_reg_data()['mesin']), $before_keys) as $k){ $keys[$k] = null; } }
				foreach($keys as $k => $before){
					$after = $this->mesin_ver_snapshot($k);
					if($after === null){ continue; }
					if($before !== null && $this->mesin_ver_norm($before) === $this->mesin_ver_norm($after)){ continue; }
					if($before !== null && !$this->mesin_ver_count($k)){ $this->mesin_ver_insert($k, $before, 'Kondisi sebelum perubahan pertama'); }   // titik kembali paling awal
					$msg = isset($GLOBALS['genc_last_log_msg']) && $GLOBALS['genc_last_log_msg'] !== '' ? $GLOBALS['genc_last_log_msg'] : 'Perubahan lewat Mesin & Unit';
					// [GENC-13OKT26] "apa yang berubah" dihitung dari kondisi tepat sebelum request ini (bukan versi bernomor sebelumnya:
					// sesudah Kembalikan ke v2 lalu diubah, v4 = v2 + perubahan itu) + versi asalnya
					$after['_meta'] = array('dari' => $this->mesin_ver_current_no($k), 'diff' => $before !== null ? $this->mesin_ver_diff($before, $after, $k) : array());
					$this->mesin_ver_insert($k, $after, $before === null ? 'Dibuat: ' . $msg : $msg);
				}
			} catch(\Throwable $e){ }
		});
	}

	/** Potret lengkap 1 folder (null kalau folder tidak ada). Dibaca langsung dari DB. */
	protected function mesin_ver_snapshot($key){
		$pdo = genc_reg_db();
		if(!$pdo){ return null; }
		$bf = genc_reg_builtin_folders_def();
		$snap = array('folder' => null, 'mesin' => null, 'profil' => array(), 'unit' => array());
		try{
			if(isset($bf[$key])){
				$st = $pdo->prepare("SELECT * FROM genc_folder WHERE folder = ?"); $st->execute(array($key));
				$r = $st->fetch(); $snap['folder'] = $r ? $r : null;
				$f = $bf[$key];
				$profiles = $f['mode'] === 'units' ? array_values(array_unique(array_map(function($t){ return $t['profile']; }, $f['types']))) : array($f['profile']);
			}
			else{
				$st = $pdo->prepare("SELECT * FROM genc_mesin WHERE page = ?"); $st->execute(array($key));
				$r = $st->fetch();
				if(!$r){ return null; }
				unset($r['id']);
				$snap['mesin'] = $r;
				$profiles = array($key);
			}
			foreach($profiles as $p){
				$st = $pdo->prepare("SELECT * FROM genc_profil WHERE profil = ?"); $st->execute(array($p));
				$r = $st->fetch(); $snap['profil'][$p] = $r ? $r : null;
			}
			$st = $pdo->prepare("SELECT * FROM genc_unit WHERE folder = ? ORDER BY id"); $st->execute(array($key));
			foreach($st->fetchAll() as $r){ $snap['unit'][(int) $r['id']] = $r; }
		} catch(\Throwable $e){ return null; }
		return $snap;
	}
	/** Bentuk pembanding: tanpa waktu / pengubah (perubahan nyata saja). [GENC-13OKT26] + tanpa status Arsip & kunci (bukan perubahan isi). */
	protected function mesin_ver_norm($snap){
		$strip = function($r){ if(!is_array($r)){ return $r; } foreach(array('updated_at', 'updated_by', 'kunci_at', 'kunci_by', 'arsip', 'kunci') as $k){ unset($r[$k]); } return $r; };
		$n = array('folder' => $strip($snap['folder']), 'mesin' => $strip($snap['mesin']), 'profil' => array(), 'unit' => array());
		foreach($snap['profil'] as $p => $r){ $n['profil'][$p] = $strip($r); }
		foreach($snap['unit'] as $i => $r){ $n['unit'][$i] = $strip($r); }
		return json_encode($n);
	}

	protected function mesin_ver_count($key){
		try{ $st = genc_reg_db()->prepare("SELECT COUNT(*) FROM genc_versi WHERE folder = ?"); $st->execute(array($key)); return (int) $st->fetchColumn(); }
		catch(\Throwable $e){ return 0; }
	}

	/** [GENC-13OKT26] Kolom penunjuk `sekarang` sudah ada? (install yang belum menjalankan SQL / belum membuka menu: belum) */
	protected function mesin_ver_has_ptr(){
		$c = $this->mesin_ver_cols('genc_versi');
		return isset($c['sekarang']);
	}

	/**
	 * Simpan versi baru (= perubahan nyata) dan jadikan "sekarang". [GENC-13OKT26] Penunjuk pindah ke versi baru;
	 * versi lain tetap. Disimpan VERSI_MAX terakhir (versi sekarang tidak pernah ikut dibuang).
	 */
	protected function mesin_ver_insert($key, $snap, $msg){
		$pdo = genc_reg_db();
		try{
			$st = $pdo->prepare("SELECT COALESCE(MAX(versi), 0) FROM genc_versi WHERE folder = ?"); $st->execute(array($key));
			$n = (int) $st->fetchColumn() + 1;
			$msg = function_exists('mb_substr') ? mb_substr(trim(strip_tags((string) $msg)), 0, 250, 'UTF-8') : substr(trim(strip_tags((string) $msg)), 0, 250);
			if($this->mesin_ver_has_ptr()){
				$pdo->prepare("UPDATE genc_versi SET sekarang = 0 WHERE folder = ?")->execute(array($key));
				$pdo->prepare("INSERT INTO genc_versi (folder, versi, ringkasan, data, created_at, created_by, sekarang) VALUES (?,?,?,?,?,?,1)")
					->execute(array($key, $n, $msg, json_encode($snap), datetime_now(), USER_NAME));
			}
			else{
				$pdo->prepare("INSERT INTO genc_versi (folder, versi, ringkasan, data, created_at, created_by) VALUES (?,?,?,?,?,?)")
					->execute(array($key, $n, $msg, json_encode($snap), datetime_now(), USER_NAME));
			}
			// simpan VERSI_MAX terakhir
			$st = $pdo->prepare("SELECT id FROM genc_versi WHERE folder = ? ORDER BY versi DESC LIMIT " . (int) self::VERSI_MAX . ", 1000"); $st->execute(array($key));
			foreach($st->fetchAll() as $r){ $pdo->prepare("DELETE FROM genc_versi WHERE id = ?")->execute(array($r['id'])); }
			return $n;
		} catch(\Throwable $e){ return 0; }
	}

	protected function mesin_ver_row($id){
		try{ $st = genc_reg_db()->prepare("SELECT * FROM genc_versi WHERE id = ?"); $st->execute(array((int) $id)); $r = $st->fetch(); return $r ? $r : null; }
		catch(\Throwable $e){ return null; }
	}

	/** [GENC-13OKT26] id versi "sekarang" 1 folder: baris bertanda `sekarang`, kalau tidak ada (data lama) = versi terbaru. 0 = belum ada versi. */
	protected function mesin_ver_current($key){
		try{
			$pdo = genc_reg_db();
			if($this->mesin_ver_has_ptr()){
				$st = $pdo->prepare("SELECT id FROM genc_versi WHERE folder = ? AND sekarang = 1 ORDER BY versi DESC LIMIT 1"); $st->execute(array($key));
				$id = (int) $st->fetchColumn();
				if($id){ return $id; }
			}
			$st = $pdo->prepare("SELECT id FROM genc_versi WHERE folder = ? ORDER BY versi DESC LIMIT 1"); $st->execute(array($key));
			return (int) $st->fetchColumn();
		} catch(\Throwable $e){ return 0; }
	}

	/** [GENC-13OKT26] Nomor versi "sekarang" (0 = belum ada versi). */
	protected function mesin_ver_current_no($key){
		$id = $this->mesin_ver_current($key);
		$r = $id ? $this->mesin_ver_row($id) : null;
		return $r ? (int) $r['versi'] : 0;
	}

	/**
	 * Daftar versi 1 folder, terbaru dulu. [GENC-13OKT26] + is_cur (penunjuk "sekarang") + diff (apa yang berubah
	 * dibanding versi sebelumnya, untuk layar penuh). Kolom data tidak ikut dikembalikan.
	 */
	protected function mesin_ver_list($key){
		try{ $st = genc_reg_db()->prepare("SELECT id, versi, ringkasan, data, created_at, created_by FROM genc_versi WHERE folder = ? ORDER BY versi DESC"); $st->execute(array($key)); $rows = $st->fetchAll(); }
		catch(\Throwable $e){ return array(); }
		$cur = $this->mesin_ver_current($key);
		$snaps = array();
		foreach($rows as $i => $r){ $s = json_decode((string) $r['data'], true); $snaps[$i] = is_array($s) ? $s : null; }
		foreach($rows as $i => $r){
			$rows[$i]['is_cur'] = (int) $r['id'] === $cur;
			$prev = isset($snaps[$i + 1]) ? $snaps[$i + 1] : null;
			$meta = ($snaps[$i] !== null && isset($snaps[$i]['_meta']) && is_array($snaps[$i]['_meta'])) ? $snaps[$i]['_meta'] : null;
			if($meta !== null && isset($meta['diff']) && is_array($meta['diff'])){ $rows[$i]['diff'] = array_values(array_map('strval', $meta['diff'])); }   // [GENC-13OKT26] dicatat saat disimpan
			else{ $rows[$i]['diff'] = ($snaps[$i] !== null && $prev !== null) ? $this->mesin_ver_diff($prev, $snaps[$i], $key) : array(); }   // versi lama (Paket 3): dibanding versi sebelumnya
			$rows[$i]['dari'] = ($meta !== null && !empty($meta['dari'])) ? (int) $meta['dari'] : 0;
			$rows[$i]['first'] = !isset($rows[$i + 1]);
			unset($rows[$i]['data']);
		}
		return $rows;
	}

	/** Kolom tabel (untuk menulis balik potret dari versi yang skemanya lebih lama / lebih baru). */
	protected function mesin_ver_cols($t){
		static $cache = array();
		if(!isset($cache[$t]) || !$cache[$t]){
			$cache[$t] = array();
			try{ foreach(genc_reg_db()->query("SHOW COLUMNS FROM `$t`")->fetchAll() as $c){ $cache[$t][$c['Field']] = true; } } catch(\Throwable $e){ }
		}
		return $cache[$t];
	}

	/** REPLACE 1 baris (hanya kolom yang ada di tabel). */
	protected function mesin_ver_put($t, $row){
		$cols = $this->mesin_ver_cols($t);
		$row = array_intersect_key($row, $cols);
		if(!$row){ return; }
		$k = array_keys($row);
		genc_reg_db()->prepare("REPLACE INTO `$t` (`" . implode('`, `', $k) . "`) VALUES (" . implode(',', array_fill(0, count($k), '?')) . ")")->execute(array_values($row));
	}

	/**
	 * POST edit/versi/<id>: kembalikan mesin ke versi itu.        [GENC-13OKT26] model PENUNJUK
	 * Potret versi ditulis balik lalu penunjuk "sekarang" pindah ke versi itu - TIDAK ada versi baru
	 * (v3 -> v2 tetap v1, v2, v3; bisa bolak-balik). Versi baru hanya tercipta kalau ada perubahan nyata.
	 * Yang tidak ikut dikembalikan: status Arsip, kunci, & unit yang sedang di Arsip / sudah dihapus permanen.
	 */
	protected function mesin_ver_restore($id){
		$v = $this->mesin_ver_row($id);
		if(!$v){ $this->mesin_toast('Versi tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$key = $v['folder'];
		$back = 'mesin?pilih=' . rawurlencode('f:' . $key) . (isset($this->request->dari) && $this->request->dari === 'penuh' ? '#riwayat-penuh' : '#riwayat');
		$tree = $this->mesin_tree();
		if(!isset($tree[$key]) || !empty($tree[$key]['hapus'])){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $tree[$key];
		if(!empty($f['arsip'])){ $this->mesin_toast('<b>' . genc_e($f['title']) . '</b> ada di Arsip &mdash; pulihkan dulu, baru pindah versi.', 'danger'); return $this->redirect($back); }
		if($r = $this->mesin_lock_guard($f, $back, 'dikembalikan ke versi lain')){ return $r; }
		$cur = $this->mesin_ver_current($key);
		if((int) $v['id'] === $cur){ $this->mesin_toast('<b>Versi ' . (int) $v['versi'] . '</b> sudah versi sekarang.', 'info'); return $this->redirect($back); }
		$cur_row = $this->mesin_ver_row($cur);
		$snap = json_decode((string) $v['data'], true);
		if(!is_array($snap)){ $this->mesin_toast('Data versi rusak.', 'danger'); return $this->redirect($back); }
		$pdo = genc_reg_db();
		$now = $this->mesin_ver_snapshot($key);
		try{
			$pdo->beginTransaction();
			// pengaturan mesin (status Arsip tidak ikut versi)
			if($f['builtin']){
				if(empty($snap['folder'])){ $pdo->prepare("DELETE FROM genc_folder WHERE folder = ?")->execute(array($key)); }
				else{ $row = $snap['folder']; $row['arsip'] = 0; $this->mesin_ver_put('genc_folder', $row); }
			}
			elseif(!empty($snap['mesin'])){
				$m = $snap['mesin'];
				$m['item_seq'] = max((int) $m['item_seq'], (int) $now['mesin']['item_seq']);   // kolom item_NN tidak pernah dipakai ulang
				$m['page'] = $key;
				$set = array(); $val = array();
				foreach(array_intersect_key($m, $this->mesin_ver_cols('genc_mesin')) as $c => $x){ if($c === 'page' || $c === 'id' || $c === 'arsip'){ continue; } $set[] = "`$c` = ?"; $val[] = $x; }
				$val[] = $key;
				$pdo->prepare("UPDATE genc_mesin SET " . implode(', ', $set) . " WHERE page = ?")->execute($val);
			}
			// isi checklist & gambar. [GENC-13OKT26] kunci TIDAK ikut versi: status kunci sekarang dipertahankan
			foreach($now['profil'] as $p => $cur_p){
				$old = isset($snap['profil'][$p]) ? $snap['profil'][$p] : null;
				$has_lock = $cur_p && $cur_p['kunci'] !== null && $cur_p['kunci'] !== '';
				if($old === null){
					if($has_lock){ $pdo->prepare("UPDATE genc_profil SET items = NULL, gambar = NULL WHERE profil = ?")->execute(array($p)); }
					else{ $pdo->prepare("DELETE FROM genc_profil WHERE profil = ?")->execute(array($p)); }
					continue;
				}
				$old['item_seq'] = max((int) $old['item_seq'], $cur_p ? (int) $cur_p['item_seq'] : 0);
				$old['profil'] = $p;
				foreach(array('kunci', 'kunci_at', 'kunci_by') as $kc){ $old[$kc] = $cur_p && array_key_exists($kc, $cur_p) ? $cur_p[$kc] : null; }
				$this->mesin_ver_put('genc_profil', $old);
			}
			// unit: unit yang ada di versi itu dikembalikan; catatan unit awal yang belum ada di versi itu dihapus;
			// unit baru yang dibuat SESUDAH versi itu tidak dihapus (tabel & datanya punya sendiri).
			// [GENC-13OKT26] unit yang sekarang di Arsip / dihapus permanen tidak disentuh (status itu bukan bagian versi).
			$snap_units = isset($snap['unit']) ? $snap['unit'] : array();
			$held = array();   // page|slug unit awal yang sedang di Arsip / nisan
			foreach($now['unit'] as $uid => $cu){
				if(!empty($cu['arsip'])){ if((int) $cu['builtin'] === 1){ $held[$cu['page'] . '|' . $cu['slug']] = true; } continue; }
				if(isset($snap_units[$uid])){ continue; }
				if((int) $cu['builtin'] === 1){ $pdo->prepare("DELETE FROM genc_unit WHERE id = ?")->execute(array($uid)); }
			}
			foreach($snap_units as $uid => $old){
				if(isset($now['unit'][$uid]) && !empty($now['unit'][$uid]['arsip'])){ continue; }
				if(!isset($now['unit'][$uid]) && (int) $old['builtin'] === 0){ continue; }
				if(!isset($now['unit'][$uid]) && isset($held[$old['page'] . '|' . $old['slug']])){ continue; }
				$old['arsip'] = 0;
				$this->mesin_ver_put('genc_unit', $old);
			}
			if($this->mesin_ver_has_ptr()){
				$pdo->prepare("UPDATE genc_versi SET sekarang = 0 WHERE folder = ?")->execute(array($key));
				$pdo->prepare("UPDATE genc_versi SET sekarang = 1 WHERE id = ?")->execute(array((int) $v['id']));
			}
			$pdo->commit();
		} catch(\Throwable $e){
			try{ $pdo->rollBack(); } catch(\Throwable $x){ }
			$this->mesin_toast('Gagal memulihkan: ' . genc_e($e->getMessage()), 'danger'); return $this->redirect($back);
		}
		genc_reg_data(true);
		foreach($this->mesin_folder_profiles($f) as $p){ $this->mesin_kolase_clear($p); }
		$from = $cur_row ? ' (dari versi ' . (int) $cur_row['versi'] . ')' : '';
		genc_app_log('mesin.versi', 'genc_versi', $v['id'], 'Kembalikan ' . $f['title'] . ' ke versi ' . $v['versi'] . $from . ' - ' . $v['ringkasan'], array('mesin' => $key, 'versi' => $v['versi'], 'dari_versi' => $cur_row ? $cur_row['versi'] : ''));
		$this->mesin_toast('<b>' . genc_e($f['title']) . '</b> sekarang di <b>versi ' . (int) $v['versi'] . '</b>' . ($cur_row ? ' (dari versi ' . (int) $cur_row['versi'] . ')' : '') . '. Tidak ada versi baru &mdash; versi lain tetap di riwayat dan bisa dipilih lagi kapan saja.', 'success');
		return $this->redirect($back);
	}

	/**
	 * [GENC-13OKT26] Isi checklist sebuah profil di potret versi: [db => array(part, standar, metode, alat, durasi, aktif, fotos, sec)] urut.
	 * Mesin baru: genc_mesin.items. Mesin awal: salinan menu (genc_profil.items) atau isi dari kode.
	 */
	protected function mesin_ver_items($snap, $p){
		$out = array();
		if(!empty($snap['mesin']) && $p === $snap['mesin']['page']){ $lists = genc_reg_items_decode($snap['mesin']['items']); }
		else{
			$lists = genc_reg_isi_base_rows($p);
			$row = isset($snap['profil'][$p]) ? $snap['profil'][$p] : null;
			$raw = ($row && $row['items'] !== null && $row['items'] !== '') ? json_decode((string) $row['items'], true) : null;
			if(is_array($raw)){
				$base = $lists; $lists = array(); $listed = array();
				foreach(genc_reg_sections() as $sk => $s){
					$lists[$sk] = array();
					foreach((isset($raw[$sk]) && is_array($raw[$sk])) ? $raw[$sk] : array() as $it){
						if(!is_array($it) || empty($it['db'])){ continue; }
						$it['aktif'] = !isset($it['aktif']) || !empty($it['aktif']);
						$lists[$sk][] = $it; $listed[$it['db']] = true;
					}
				}
				foreach($base as $sk => $list){ foreach($list as $it){ if(!isset($listed[$it['db']])){ $lists[$sk][] = $it; } } }
			}
		}
		foreach($lists as $sk => $list){
			foreach($list as $it){
				if(empty($it['db'])){ continue; }
				$out[(string) $it['db']] = array('part' => isset($it['part']) ? (string) $it['part'] : '', 'standar' => isset($it['standar']) ? (string) $it['standar'] : '',
					'metode' => isset($it['metode']) ? (string) $it['metode'] : '', 'alat' => isset($it['alat']) ? (string) $it['alat'] : '', 'durasi' => isset($it['durasi']) ? (string) $it['durasi'] : '',
					'aktif' => !isset($it['aktif']) || !empty($it['aktif']), 'fotos' => count(genc_reg_fotos_of($it)), 'sec' => $sk);
			}
		}
		return $out;
	}

	/**
	 * [GENC-13OKT26] Apa yang berubah dari potret $a ke $b (teks pendek untuk Riwayat layar penuh).
	 * Pengaturan mesin, kunci, item (baru / diubah / dihapus / dipulihkan / urutan / foto), gambar report, unit.
	 */
	protected function mesin_ver_diff($a, $b, $key){
		$out = array();
		$names = function($list){ $n = count($list); $s = implode(', ', array_slice($list, 0, 3)); return $n > 3 ? $s . ' +' . ($n - 3) . ' lagi' : $s; };
		$val = function($r, $k){ return is_array($r) && isset($r[$k]) && $r[$k] !== null ? trim((string) $r[$k]) : ''; };
		// pengaturan mesin
		$fa = !empty($a['mesin']) ? $a['mesin'] : $a['folder']; $fb = !empty($b['mesin']) ? $b['mesin'] : $b['folder'];
		$lbl = array('nama' => 'Nama', 'area' => 'Area', 'pelaksanaan' => 'Pelaksanaan');
		$kosong = !empty($b['mesin']) ? '(kosong)' : '(awal)';   // mesin awal: kosong = nilai awal dari kode
		foreach($lbl as $k => $t){
			$x = $val($fa, $k); $y = $val($fb, $k);
			if($x !== $y){ $out[] = $t . ': ' . ($x === '' ? $kosong : $x) . ' &rarr; ' . ($y === '' ? $kosong : $y); }
		}
		if($val($fa, 'ikon') !== $val($fb, 'ikon')){ $out[] = 'Ikon sidebar diganti'; }
		$aa = is_array($fa) ? (int) $fa['aktif'] : 1; $ab = is_array($fb) ? (int) $fb['aktif'] : 1;
		if($aa !== $ab){ $out[] = $ab ? 'Mesin diaktifkan' : 'Mesin dinonaktifkan'; }
		// kunci
		$lk = function($s){ $n = 0; foreach($s['profil'] as $r){ if(is_array($r) && (int) $r['kunci'] === 1){ $n++; } } return $n; };
		if($lk($a) !== $lk($b)){ $out[] = $lk($b) > $lk($a) ? 'Mesin dikunci' : 'Kunci dibuka'; }
		// isi checklist per profil
		$profiles = array_unique(array_merge(array_keys((array) $a['profil']), array_keys((array) $b['profil']), !empty($b['mesin']) ? array($b['mesin']['page']) : array()));
		$multi = count($profiles) > 1;
		foreach($profiles as $p){
			$ia = $this->mesin_ver_items($a, $p); $ib = $this->mesin_ver_items($b, $p);
			$pre = $multi ? $this->mesin_profile_label($p) . ': ' : '';
			$new = array(); $del = array(); $back = array(); $chg = array(); $fplus = 0; $fmin = 0;
			foreach($ib as $db => $it){
				if(!isset($ia[$db])){ if($it['aktif']){ $new[] = $it['part'] !== '' ? $it['part'] : $db; } if($it['fotos']){ $fplus += $it['fotos']; } continue; }
				$o = $ia[$db];
				if($o['aktif'] && !$it['aktif']){ $del[] = $o['part']; continue; }
				if(!$o['aktif'] && $it['aktif']){ $back[] = $it['part']; }
				$fields = array();
				foreach(array('part' => 'nama', 'standar' => 'standar', 'metode' => 'metode', 'alat' => 'alat', 'durasi' => 'durasi') as $k => $t){ if($o[$k] !== $it[$k]){ $fields[] = $t; } }
				if($o['sec'] !== $it['sec']){ $fields[] = 'bagian'; }
				if($fields && $it['aktif']){ $chg[] = $it['part'] . ' (' . implode(', ', $fields) . ')'; }
				if($it['fotos'] > $o['fotos']){ $fplus += $it['fotos'] - $o['fotos']; } elseif($it['fotos'] < $o['fotos']){ $fmin += $o['fotos'] - $it['fotos']; }
			}
			if($new){ $out[] = $pre . 'Item baru: ' . $names($new); }
			if($chg){ $out[] = $pre . 'Item diubah: ' . $names($chg); }
			if($del){ $out[] = $pre . 'Item dihapus: ' . $names($del); }
			if($back){ $out[] = $pre . 'Item dipulihkan: ' . $names($back); }
			if($fplus || $fmin){ $out[] = $pre . 'Foto item: ' . ($fplus ? '+' . $fplus : '') . ($fplus && $fmin ? ' / ' : '') . ($fmin ? '&minus;' . $fmin : ''); }
			$oa = array_keys(array_filter($ia, function($x){ return $x['aktif']; })); $ob = array_keys(array_filter($ib, function($x){ return $x['aktif']; }));
			$common_a = array_values(array_intersect($oa, $ob)); $common_b = array_values(array_intersect($ob, $oa));
			if($common_a !== $common_b){ $out[] = $pre . 'Urutan item diubah'; }
			$ga = isset($a['profil'][$p]) && is_array($a['profil'][$p]) ? (string) $a['profil'][$p]['gambar'] : '';
			$gb = isset($b['profil'][$p]) && is_array($b['profil'][$p]) ? (string) $b['profil'][$p]['gambar'] : '';
			if($ga !== $gb){ $out[] = $pre . 'Gambar bagian mesin (report) diubah'; }
		}
		// unit
		$ua = (array) $a['unit']; $ub = (array) $b['unit'];
		foreach($ub as $id => $u){
			$nm = trim((string) $u['label']) !== '' ? $u['label'] : ($u['slug'] !== '' ? $u['slug'] : $u['page']);
			if(!isset($ua[$id])){
				if((int) $u['builtin'] === 0){ $out[] = 'Unit baru: ' . $nm; }
				else{ if(trim((string) $u['label']) !== ''){ $out[] = 'Nama unit diganti: ' . $nm; } if((int) $u['aktif'] === 0){ $out[] = 'Unit dinonaktifkan: ' . $nm; } }
				continue;
			}
			$o = $ua[$id];
			if((string) $o['label'] !== (string) $u['label']){ $out[] = 'Nama unit: ' . (trim((string) $o['label']) !== '' ? $o['label'] : '(awal)') . ' &rarr; ' . (trim((string) $u['label']) !== '' ? $u['label'] : '(awal)'); }
			if((int) $o['aktif'] !== (int) $u['aktif']){ $out[] = ((int) $u['aktif'] ? 'Unit diaktifkan: ' : 'Unit dinonaktifkan: ') . $nm; }
			if((string) $o['kode'] !== (string) $u['kode']){ $out[] = 'Kode unit ' . $nm . ' diubah'; }
		}
		foreach($ua as $id => $u){
			if(isset($ub[$id])){ continue; }
			if((int) $u['builtin'] === 1){ $out[] = 'Unit ' . (trim((string) $u['label']) !== '' ? $u['label'] : ($u['slug'] !== '' ? $u['slug'] : $u['page'])) . ' kembali ke nama & status awal'; }
		}
		return array_slice(array_values(array_unique($out)), 0, 12);
	}

	// =====================================================================
	//  HAPUS PERMANEN dari Arsip + CADANGAN          [GENC-13OKT26]
	//  Hanya mesin / unit yang sudah di Arsip. Konfirmasi = ketik nama persis.
	//  Urutan: rencana -> cadangan .zip (report PDF, Excel, foto, pemulihan.sql)
	//  -> kalau cadangan GAGAL: batal, tidak ada yang dihapus -> hapus -> log.
	//  Buatan menu: benar-benar hilang (tabel am_* / baris unit, registry, foto,
	//  hak akses, riwayat). Mesin & unit AWAL (dari kode): hilang permanen dari
	//  aplikasi (nisan arsip = 2); tabel & checklist asli TETAP di database.
	// =====================================================================

	/** Nama yang harus diketik untuk konfirmasi. */
	protected function mesin_purge_name($hit){
		return $hit['type'] === 'folder' ? (string) $hit['folder']['title'] : (string) $hit['unit']['label'];
	}

	/** Profil report (tabel, kolom unit) untuk 1 unit / halaman. */
	protected function mesin_purge_conf($profile, $page){
		$ov = ($page !== '' && $page !== $profile && strpos($page, GENC_REG_PREFIX) !== 0) ? array('table' => $page) : array();
		try{ return checklist_load_machine($profile, $ov); } catch(\Throwable $e){ return null; }
	}

	/**
	 * Rencana hapus permanen + bahan cadangan (lihat genc_cad_build()). Juga dipakai panel untuk pratinjau.
	 * @return array
	 */
	protected function mesin_purge_plan($hit){
		$pdo = genc_reg_db();
		$f = $hit['folder'];
		$plan = array('kind' => $hit['type'] === 'folder' ? 'mesin' : 'unit', 'folder' => $f['key'], 'builtin' => false, 'title' => $this->mesin_purge_name($hit),
			'what' => '', 'note' => '', 'sources' => array(), 'registry' => array(), 'perms' => array(), 'sql_after' => array(), 'photo_dirs' => array(),
			// aksi
			'drop' => array(), 'unit_ids' => array(), 'mesin_page' => '', 'profil' => array(), 'versi' => '', 'perm_pages' => array(),
			'tomb_folder' => '', 'tomb_unit' => null,
			// angka pratinjau
			'n_hapus' => 0, 'n_tetap' => 0, 'n_unit' => 0, 'n_foto' => 0, 'n_perm' => 0, 'n_versi' => 0);
		$src = function($page, $table, $uf, $norms, $label, $mode, $pdf_units, $excel = true){
			return array('page' => $page, 'table' => $table, 'unit_field' => $uf, 'norms' => $norms, 'label' => $label, 'mode' => $mode, 'pdf_units' => $pdf_units, 'excel' => $excel);
		};
		$pu = function($u){ return array('slug' => $u['slug'], 'label' => $u['label'], 'norms' => $u['norms']); };
		$rows_of = function($t, $where, $args) use ($pdo){ try{ $st = $pdo->prepare("SELECT * FROM `$t` WHERE $where"); $st->execute($args); return $st->fetchAll(PDO::FETCH_ASSOC); } catch(\Throwable $e){ return array(); } };
		$all_units = genc_reg_data()['unit'];
		$live = array_values(array_filter($f['units'], function($u){ return empty($u['hapus']); }));

		if($hit['type'] === 'folder'){
			$plan['versi'] = $f['key'];
			$plan['n_versi'] = $this->mesin_ver_count($f['key']);
			$plan['registry']['genc_versi'] = array('rows' => $rows_of('genc_versi', 'folder = ?', array($f['key'])));
			$plan['n_unit'] = count($live);
			if($f['jenis']){
				// ---------------- mesin buatan menu: semuanya hilang
				$p = $f['key'];
				$plan['sources'][] = $src($p, $p, 'unit', null, $f['title'], 'drop', array_map($pu, $live));
				$plan['drop'][] = $p;
				$plan['mesin_page'] = $p;
				$plan['unit_ids'] = array_map(function($u){ return (int) $u['id']; }, array_filter($f['units'], function($u){ return $u['kind'] === 'unit'; }));
				$plan['profil'] = array($p);
				$plan['perm_pages'] = array($p);
				$plan['photo_dirs'] = array('uploads/mesin/' . $p);
				$plan['registry']['genc_mesin'] = array('rows' => $rows_of('genc_mesin', 'page = ?', array($p)), 'set' => array('arsip' => 1, 'aktif' => 0));
				$plan['registry']['genc_unit'] = array('rows' => $rows_of('genc_unit', 'folder = ?', array($p)));
				$plan['registry']['genc_profil'] = array('rows' => $rows_of('genc_profil', 'profil = ?', array($p)));
				$plan['n_hapus'] = (int) genc_reg_table_count($p);
				$plan['what'] = 'Mesin "' . $f['title'] . '" (dibuat lewat menu, tabel ' . $p . ', ' . count($live) . ' unit)';
			}
			else{
				// ---------------- mesin awal (dari kode): nisan; tabel & data asli tetap; tambahan lewat menu ikut hilang
				$plan['builtin'] = true;
				$def = $f['def'];
				$plan['tomb_folder'] = $f['key'];
				$plan['profil'] = $this->mesin_folder_profiles($f);
				foreach($plan['profil'] as $p){ $plan['photo_dirs'][] = 'uploads/mesin/' . $p; }
				$in = implode(',', array_fill(0, count($plan['profil']), '?'));
				$plan['registry']['genc_profil'] = array('rows' => $rows_of('genc_profil', "profil IN ($in)", $plan['profil']));
				$plan['registry']['genc_unit'] = array('rows' => $rows_of('genc_unit', 'folder = ? AND builtin = 0', array($f['key'])));
				$plan['unit_ids'] = array_map(function($r){ return (int) $r['id']; }, $plan['registry']['genc_unit']['rows']);
				$plan['perm_pages'] = genc_reg_folder_pages($f['key']);
				$plan['sql_after'][] = "INSERT INTO `genc_folder` (`folder`, `aktif`, `arsip`) VALUES (" . $pdo->quote($f['key']) . ", 0, 1) ON DUPLICATE KEY UPDATE `arsip` = 1, `aktif` = 0;";
				if($f['mode'] === 'units'){
					$tables = array();
					foreach($def['types'] as $t){
						$conf = $this->mesin_purge_conf($t['profile'], $t['page']);
						if(!$conf){ continue; }
						if(!isset($tables[$conf['table']])){ $tables[$conf['table']] = array('page' => $t['page'], 'uf' => $conf['unit_field'], 'units' => array()); }
						foreach($live as $u){ if($u['profile'] === $t['profile']){ $tables[$conf['table']]['units'][] = $u; } }
					}
					foreach($tables as $tb => $x){
						$plan['sources'][] = $src($x['page'], $tb, $x['uf'], null, $this->mesin_page_title($f, $x['page']), 'keep', array_map($pu, $x['units']));
						foreach($x['units'] as $u){
							if($u['kind'] !== 'unit'){ continue; }
							// unit tambahan lewat menu di tabel bersama: baris checklist-nya ikut dihapus
							$plan['sources'][] = $src($x['page'], $tb, $x['uf'], $u['norms'], $u['label'], 'rows', array(), false);
							$plan['n_hapus'] += $u['count'];
						}
					}
				}
				else{
					foreach($live as $u){
						$dyn = $u['kind'] === 'unit';
						$plan['sources'][] = $src($u['page'], $u['page'], '', null, $u['label'], $dyn ? 'drop' : 'keep', array(array('slug' => '', 'label' => $u['label'], 'norms' => null)));
						if($dyn){ $plan['drop'][] = $u['page']; $plan['n_hapus'] += $u['count']; }
					}
				}
				foreach($f['units'] as $u){ if(empty($u['hapus'])){ $plan['n_tetap'] += $u['kind'] === 'unit' ? 0 : $u['count']; } }
				$plan['what'] = 'Mesin "' . $f['title'] . '" (mesin awal' . ($f['title'] !== $def['title_awal'] ? ', nama awal ' . $def['title_awal'] : '') . ', ' . count($live) . ' unit)';
				$plan['note'] = 'Mesin awal: halamannya ada di kode aplikasi & tabel aslinya TIDAK dihapus (' . number_format($plan['n_tetap'], 0, ',', '.') . ' checklist tetap di database). '
					. 'pemulihan.sql mengembalikannya ke Arsip beserta hak akses, isi checklist yang diubah lewat menu, unit tambahan & riwayatnya.';
			}
		}
		else{
			$u = $hit['unit'];
			$plan['n_unit'] = 1;
			if($u['kind'] === 'unit'){
				$row = isset($all_units[(int) $u['id']]) ? $all_units[(int) $u['id']] : null;
				$plan['unit_ids'] = array((int) $u['id']);
				$plan['registry']['genc_unit'] = array('rows' => $row ? array(array_diff_key($row, array('alias_arr' => 1))) : array(), 'set' => array('arsip' => 1, 'aktif' => 0));
				if($f['mode'] === 'tables'){
					$plan['sources'][] = $src($u['page'], $u['page'], '', null, $u['label'], 'drop', array(array('slug' => '', 'label' => $u['label'], 'norms' => null)));
					$plan['drop'][] = $u['page'];
					$plan['perm_pages'] = array($u['page']);
				}
				else{
					$conf = $f['jenis'] ? null : $this->mesin_purge_conf($u['profile'], $u['page']);
					$tb = $f['jenis'] ? $f['key'] : ($conf ? $conf['table'] : $u['page']);
					$uf = $f['jenis'] ? 'unit' : ($conf ? $conf['unit_field'] : 'unit');
					$plan['sources'][] = $src($u['page'], $tb, $uf, $u['norms'], $u['label'], 'rows', array($pu($u)));
				}
				$plan['n_hapus'] = $u['count'];
				$plan['what'] = 'Unit "' . $u['label'] . '" dari mesin ' . $f['title'] . ' (dibuat lewat menu)';
			}
			else{
				$plan['builtin'] = true;
				$slug = $u['note_slug'];
				$plan['tomb_unit'] = array('page' => $u['page'], 'slug' => $slug, 'profile' => $u['profile']);
				if($f['mode'] === 'tables'){
					$plan['sources'][] = $src($u['page'], $u['page'], '', null, $u['label'], 'keep', array(array('slug' => '', 'label' => $u['label'], 'norms' => null)));
				}
				else{
					$conf = $this->mesin_purge_conf($u['profile'], $u['page']);
					$plan['sources'][] = $src($u['page'], $conf ? $conf['table'] : $u['page'], $conf ? $conf['unit_field'] : 'unit', $u['norms'], $u['label'], 'keep', array($pu($u)));
				}
				$plan['n_tetap'] = $u['count'];
				$plan['sql_after'][] = "UPDATE `genc_unit` SET `arsip` = 1 WHERE `builtin` = 1 AND `page` = " . $pdo->quote($u['page']) . " AND `slug` = " . $pdo->quote($slug) . ";";
				$plan['what'] = 'Unit "' . $u['label'] . '" dari mesin ' . $f['title'] . ' (unit awal' . ($u['label'] !== $u['default_label'] ? ', nama awal ' . $u['default_label'] : '') . ')';
				$plan['note'] = 'Unit awal: ' . number_format($u['count'], 0, ',', '.') . ' checklist-nya TIDAK dihapus dari tabel asli; unit hanya hilang permanen dari aplikasi. pemulihan.sql mengembalikannya ke Arsip.';
			}
		}
		// hak akses yang ikut hilang (route bersama Approval / NOK History di palletmover tetap)
		foreach($plan['perm_pages'] as $pg){
			$keep = genc_reg_shared_actions($pg);
			foreach($rows_of('role_permissions', 'page_name = ?', array($pg)) as $r){ if(!in_array($r['action_name'], $keep, true)){ $plan['perms'][] = $r; } }
		}
		$plan['n_perm'] = count($plan['perms']);
		foreach($plan['photo_dirs'] as $pd){ $plan['n_foto'] += genc_cad_count_photos(ROOT . $pd); }
		return $plan;
	}

	/** Judul halaman sebuah mesin awal (AGV Table Top Lift / Counterbalance, Forklift, ...). */
	protected function mesin_page_title($f, $page){
		if($f['mode'] === 'units' && count($f['def']['types']) > 1){
			$pages = array(); foreach($f['def']['types'] as $t){ $pages[$t['page']][] = $t['label']; }
			if(count($pages) > 1 && isset($pages[$page])){ return $f['title'] . ' ' . implode(' & ', $pages[$page]); }
		}
		return $f['title'];
	}

	/** POST delete/permanen/x: sel = f:<key> | u:<id> | b:<page>[:<slug>], konfirmasi = nama persis. */
	protected function mesin_purge($fd){
		$sel = isset($fd['sel']) && is_scalar($fd['sel']) ? (string) $fd['sel'] : '';
		$tree = $this->mesin_tree();
		$hit = $this->mesin_resolve($tree, $sel);
		if(!$hit){ $this->mesin_toast('Tidak ditemukan (mungkin sudah dihapus).', 'danger'); return $this->redirect('mesin'); }
		$back = 'mesin?pilih=' . rawurlencode($sel);
		$f = $hit['folder'];
		$in_arch = $hit['type'] === 'folder' ? !empty($f['arsip']) : (!empty($hit['unit']['arsip']) || !empty($f['arsip']));
		if(!$in_arch){ $this->mesin_toast('Hanya mesin / unit yang ada di <b>Arsip</b> yang bisa dihapus permanen. Hapus (pindahkan ke Arsip) dulu.', 'danger'); return $this->redirect($back); }
		if($hit['type'] === 'unit' && !empty($f['arsip'])){ $this->mesin_toast('Mesin <b>' . genc_e($f['title']) . '</b> sedang di Arsip &mdash; hapus permanen mesinnya (semua unit ikut), atau pulihkan mesinnya dulu.', 'danger'); return $this->redirect($back); }
		$name = $this->mesin_purge_name($hit);
		$typed = genc_reg_clean(isset($fd['konfirmasi']) && is_scalar($fd['konfirmasi']) ? $fd['konfirmasi'] : '', 120);
		$norm = function($s){ $s = preg_replace('/\s+/u', ' ', trim((string) $s)); return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s); };
		if($typed === '' || $norm($typed) !== $norm($name)){
			$this->mesin_toast('Nama yang diketik tidak sama dengan <b>' . genc_e($name) . '</b> &mdash; tidak ada yang dihapus.', 'danger');
			return $this->redirect($back . '#hapus-permanen');
		}
		if($r = $this->mesin_lock_guard($f, $back, 'dihapus permanen')){ return $r; }
		$plan = $this->mesin_purge_plan($hit);
		// ---------------------------------------------------------------- 1. cadangan (wajib berhasil)
		$bk = genc_cad_build($plan);
		if(empty($bk['ok'])){
			genc_app_log('mesin.hapus.gagal', 'genc_mesin', 0, 'Hapus permanen ' . $plan['what'] . ' DIBATALKAN - cadangan gagal: ' . $bk['msg'], array('pilih' => $sel));
			$this->mesin_toast('Hapus permanen <b>dibatalkan</b> &mdash; file cadangan gagal dibuat: ' . genc_e($bk['msg']) . ' Tidak ada yang dihapus.', 'danger');
			return $this->redirect($back);
		}
		// ---------------------------------------------------------------- 2. hapus
		$pdo = genc_reg_db();
		try{
			$pdo->beginTransaction();
			foreach($plan['sources'] as $i => $s){
				if($s['mode'] !== 'rows' || empty($bk['ids'][$i])){ continue; }
				foreach(array_chunk($bk['ids'][$i], 200) as $ids){
					$pdo->prepare("DELETE FROM `" . str_replace('`', '', $s['table']) . "` WHERE `id` IN (" . implode(',', array_fill(0, count($ids), '?')) . ")")->execute(array_values($ids));
				}
			}
			foreach($plan['unit_ids'] as $uid){ $pdo->prepare("DELETE FROM genc_unit WHERE id = ? AND builtin = 0")->execute(array((int) $uid)); }
			if($plan['mesin_page'] !== ''){
				$pdo->prepare("DELETE FROM genc_mesin WHERE page = ?")->execute(array($plan['mesin_page']));
				$pdo->prepare("DELETE FROM genc_unit WHERE folder = ?")->execute(array($plan['mesin_page']));
			}
			foreach($plan['profil'] as $p){ $pdo->prepare("DELETE FROM genc_profil WHERE profil = ?")->execute(array($p)); }
			if($plan['versi'] !== ''){ $pdo->prepare("DELETE FROM genc_versi WHERE folder = ?")->execute(array($plan['versi'])); }
			foreach($plan['perm_pages'] as $pg){
				$keep = genc_reg_shared_actions($pg);
				$pdo->prepare("DELETE FROM role_permissions WHERE page_name = ?" . ($keep ? " AND action_name NOT IN (" . implode(',', array_fill(0, count($keep), '?')) . ")" : ''))->execute(array_merge(array($pg), $keep));
			}
			if($plan['tomb_folder'] !== ''){
				$pdo->prepare("INSERT INTO genc_folder (folder, aktif, arsip, updated_at, updated_by) VALUES (?,0,?,?,?) ON DUPLICATE KEY UPDATE aktif = 0, arsip = VALUES(arsip), updated_at = VALUES(updated_at), updated_by = VALUES(updated_by)")
					->execute(array($plan['tomb_folder'], GENC_REG_HAPUS, datetime_now(), USER_NAME));
			}
			if($plan['tomb_unit']){
				$t = $plan['tomb_unit'];
				$note = genc_reg_builtin_note($t['page'], $t['slug']);
				if($note){ $pdo->prepare("UPDATE genc_unit SET aktif = 0, arsip = ?, updated_at = ?, updated_by = ? WHERE id = ?")->execute(array(GENC_REG_HAPUS, datetime_now(), USER_NAME, (int) $note['id'])); }
				else{ $pdo->prepare("INSERT INTO genc_unit (folder, profile, page, slug, label, kode, alias, builtin, aktif, arsip, urut, created_at, created_by, updated_at, updated_by) VALUES (?,?,?,?,'','','[]',1,0,?,0,?,?,?,?)")
					->execute(array($f['key'], $t['profile'], $t['page'], $t['slug'], GENC_REG_HAPUS, datetime_now(), USER_NAME, datetime_now(), USER_NAME)); }
			}
			$pdo->commit();
		} catch(\Throwable $e){
			try{ $pdo->rollBack(); } catch(\Throwable $x){ }
			genc_app_log('mesin.hapus.gagal', 'genc_mesin', 0, 'Hapus permanen ' . $plan['what'] . ' gagal: ' . $e->getMessage() . ' (cadangan ' . $bk['id'] . ' sudah dibuat)', array('pilih' => $sel, 'cadangan' => $bk['id']));
			$this->mesin_toast('Gagal menghapus: ' . genc_e($e->getMessage()) . '. Tidak ada yang berubah; file cadangan tetap tersimpan.', 'danger');
			return $this->redirect($back);
		}
		// DDL di luar transaksi (DROP = commit otomatis di MySQL)
		$drop_err = array();
		foreach($plan['drop'] as $t){
			if(strpos($t, GENC_REG_PREFIX) !== 0){ continue; }   // hanya tabel buatan menu
			try{ $pdo->exec("DROP TABLE IF EXISTS `" . str_replace('`', '', $t) . "`"); } catch(\Throwable $e){ $drop_err[] = $t; }
		}
		foreach($plan['photo_dirs'] as $pd){ if(strpos($pd, 'uploads/mesin/') === 0){ genc_cad_rrmdir(ROOT . $pd); } }
		foreach($plan['profil'] as $p){ $this->mesin_kolase_clear($p); }
		genc_reg_data(true);
		$pre = $plan['kind'] === 'mesin' ? 'mesin ' : 'unit ';
		$what_short = (stripos($plan['title'], trim($pre)) === 0 ? '' : $pre) . $plan['title'];   // "Mesin Dumping", bukan "mesin Mesin Dumping"
		genc_app_log($plan['kind'] === 'mesin' ? 'mesin.hapus' : 'mesin.unit.hapus', $plan['kind'] === 'mesin' ? 'genc_mesin' : 'genc_unit', 0,
			'Hapus permanen ' . $what_short . ($hit['type'] === 'unit' ? ' dari ' . $f['title'] : '') . ' - ' . number_format($plan['n_hapus'], 0, ',', '.') . ' checklist dihapus'
			. ($plan['n_tetap'] ? ', ' . number_format($plan['n_tetap'], 0, ',', '.') . ' checklist tetap di tabel asli' : '') . ' (cadangan ' . $bk['id'] . ')',
			array('pilih' => $sel, 'cadangan' => $bk['id'], 'tabel_dihapus' => implode(', ', $plan['drop']), 'hak_akses' => $plan['n_perm'], 'foto' => $plan['n_foto'], 'gagal_drop' => implode(', ', $drop_err)));
		$this->mesin_toast('<b>' . genc_e(ucfirst($what_short)) . '</b> dihapus permanen. File cadangan sudah dibuat &mdash; '
			. ($bk['meta']['zip'] !== '' ? '<a href="' . genc_e(genc_url('mesin', array('cadangan' => $bk['id']))) . '"><b>unduh cadangan</b></a> dan simpan di tempat aman.' : 'unduh file-nya di daftar di bawah dan simpan di tempat aman.')
			. ($drop_err ? ' Catatan: tabel ' . genc_e(implode(', ', $drop_err)) . ' belum bisa dihapus (minta admin database).' : ''), 'success');
		return $this->redirect('mesin?pilih=cadangan&siap=' . rawurlencode($bk['id']));
	}

	/** GET mesin?cadangan=<id>[&file=<path di folder cadangan tanpa zip>]: unduh (hak akses mesin/delete). */
	protected function mesin_cadangan_unduh($id, $file){
		if(!ACL::is_allowed('mesin/delete')){ $this->mesin_toast('Role Anda tidak punya akses ke cadangan hapus permanen.', 'danger'); return $this->redirect('mesin'); }
		$m = genc_cad_get($id);
		if(!$m){ $this->mesin_toast('Cadangan tidak ditemukan.', 'danger'); return $this->redirect('mesin?pilih=cadangan'); }
		$rel = $m['dir'];   // [GENC-13OKT26-FIX] folder tempat cadangan itu disimpan (utama atau cadangan)
		if($file === '' && $m['zip_ok']){
			$path = ROOT . $rel . '/' . basename($m['zip']); $name = 'Cadangan-hapus-' . $id . '.zip'; $type = 'application/zip';
		}
		else{
			$file = str_replace('\\', '/', $file);
			$list = isset($m['files']) ? (array) $m['files'] : array();
			if(!$m['dir_ok'] || $file === '' || strpos($file, '..') !== false || !in_array($file, $list, true)){ $this->mesin_toast('File cadangan tidak ditemukan.', 'danger'); return $this->redirect('mesin?pilih=cadangan'); }
			$path = ROOT . $rel . '/' . $id . '/' . $file; $name = basename($file); $type = 'application/octet-stream';
		}
		if(!is_file($path)){ $this->mesin_toast('File cadangan tidak ditemukan di server.', 'danger'); return $this->redirect('mesin?pilih=cadangan'); }
		genc_app_log('mesin.cadangan', 'genc_mesin', 0, 'Unduh cadangan hapus permanen ' . $m['title'] . ($file !== '' ? ' (' . $file . ')' : ''), array('cadangan' => $id));
		while(ob_get_level() > 0){ @ob_end_clean(); }
		header('Content-Type: ' . $type);
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header('Content-Length: ' . filesize($path));
		header('X-Content-Type-Options: nosniff');
		readfile($path);
		exit;
	}

	protected function mesin_other_active($folder_node, $except_sel){
		foreach($folder_node['units'] as $u){ if($u['sel'] !== $except_sel && $u['aktif']){ return true; } }
		return false;
	}

	/** Jenis mesin baru: identitas + daftar item (+ foto). */
	protected function mesin_edit_jenis($page, $fd){
		$tree = $this->mesin_tree();
		$d = genc_reg_data();
		if(!isset($d['mesin'][$page]) || !isset($tree[$page])){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$m = $d['mesin'][$page];
		$node = $tree[$page];
		if(empty($fd['nama']) && empty($fd['items']) && empty($fd['items_sent']) && !empty($_SERVER['CONTENT_LENGTH'])){
			$this->mesin_toast('Data tidak terkirim &mdash; kemungkinan ukuran foto terlalu besar. Coba unggah foto lebih sedikit sekaligus.', 'danger');
			return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page));
		}
		// [GENC-08OKT26-P3] identitas sekarang diatur di kartu "Pengaturan mesin" (edit/folder); form ini cukup item.
		// Identitas yang masih dikirim bersama item (form / skrip lama) tetap diterima.
		$ident = isset($fd['nama']);
		$old = $ident ? array(
			'nama'   => genc_reg_clean(isset($fd['nama']) ? $fd['nama'] : '', 60),
			'area'   => genc_reg_clean(isset($fd['area']) ? $fd['area'] : '', 100),
			'ikon'   => isset($fd['ikon']) && in_array((string) $fd['ikon'], genc_reg_icons(), true) ? (string) $fd['ikon'] : $m['ikon'],
			'pelaksanaan' => genc_reg_clean(isset($fd['pelaksanaan']) ? $fd['pelaksanaan'] : '', 150),
			'aktif'  => !empty($fd['aktif']) ? 1 : 0,
		) : array('nama' => $m['nama'], 'area' => (string) $m['area'], 'ikon' => $m['ikon'], 'pelaksanaan' => (string) $m['pelaksanaan'], 'aktif' => (int) $m['aktif']);
		if(!empty($m['arsip'])){ $this->mesin_toast('Mesin ini ada di Arsip &mdash; pulihkan dulu.', 'danger'); return $this->redirect('mesin?pilih=' . rawurlencode('f:' . $page)); }
		$errors = array();
		if(function_exists('mb_strlen') ? mb_strlen($old['nama'], 'UTF-8') < 2 : strlen($old['nama']) < 2){ $errors['nama'] = 'Isi nama mesin (minimal 2 huruf).'; }
		else{ foreach($tree as $k => $f){ if($k !== $page && strcasecmp(trim($f['title']), $old['nama']) === 0){ $errors['nama'] = 'Nama ini sudah dipakai mesin "' . genc_e($f['title']) . '".'; } } }

		// ---- item: urutan = urutan di form; item lama dikenali dari kolom db-nya
		$current = array();
		foreach($m['items_arr'] as $sk => $list){ foreach($list as $it){ $current[$it['db']] = $it; } }
		$seq = (int) $m['item_seq'];
		$locked = genc_reg_locked($page);   // [GENC-06OKT26-ISI] terkunci: item & foto tidak diubah
		if($locked){ $this->mesin_lock_guard($node, 'mesin?pilih=' . rawurlencode('f:' . $page), 'diubah'); return null; }   // [GENC-08OKT26-P3] kunci = seluruh mesin
		$posted = (!$locked && isset($fd['items']) && is_array($fd['items'])) ? $fd['items'] : array();
		$items = array(); $rows_old = array(); $new_cols = array(); $seen = array(); $item_err = array(); $active = 0;
		$maxlen = array('part' => 80, 'standar' => 150, 'metode' => 100, 'alat' => 80, 'durasi' => 30);
		foreach(genc_reg_sections() as $sk => $s){
			$items[$sk] = array(); $rows_old[$sk] = array();
			$list = (isset($posted[$sk]) && is_array($posted[$sk])) ? $posted[$sk] : array();
			foreach($list as $ri => $r){
				if(!is_array($r)){ continue; }
				$it = array('db' => '', 'aktif' => true, 'foto' => '', 'fotos' => array());
				foreach($maxlen as $k => $mx){ $it[$k] = genc_reg_clean(isset($r[$k]) ? $r[$k] : '', $mx); }
				$db = isset($r['db']) ? (string) $r['db'] : '';
				$key = isset($r['key']) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $r['key'])) : '';
				$hapus = !empty($r['hapus']);
				$empty = ($it['part'] . $it['standar'] . $it['metode'] . $it['alat'] . $it['durasi']) === '';
				$cur_f = array();
				if($db !== '' && isset($current[$db]) && !isset($seen[$db])){
					$seen[$db] = true;
					$it['db'] = $db;
					$cur_f = $current[$db]['fotos'];
					if($hapus){ $it = array_merge($current[$db], array('aktif' => false)); $items[$sk][] = $it; $rows_old[$sk][] = $it + array('key' => $key); continue; }
					if($it['part'] === ''){ $item_err[] = 'Nama part tidak boleh kosong (' . $s['short'] . ' baris ' . (count($items[$sk]) + 1) . ').'; }
				}
				else{
					if($hapus || $empty){ continue; }   // baris baru kosong / dibatalkan
					if($it['part'] === ''){ $item_err[] = 'Isi nama part untuk item baru di ' . $s['short'] . '.'; }
					$it['_new'] = true;
				}
				// [GENC-07OKT26-FOTO] banyak foto per item: urutan baru + yang dihapus
				list($it['fotos'], $it['_foto_rm']) = $this->mesin_item_fotos_post($r, $cur_f);
				$it['foto'] = $it['fotos'] ? $it['fotos'][0] : '';
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
		$item_err = array_merge($item_err, $this->mesin_file_errors($uploads), $this->mesin_fotos_count_errors($items, $uploads));
		$gb = $locked ? array() : $this->mesin_gambar_inputs();
		foreach($gb as $key => $tmp){ if(is_string($tmp) && strpos($tmp, '!') === 0){ $item_err[] = substr($tmp, 1); } }
		$xu = $locked ? array() : $this->mesin_file_inputs('gambar_extra_file', 'foto tambahan');   // [GENC-07OKT26-FOTO] foto tambahan kolase
		$item_err = array_merge($item_err, $this->mesin_file_errors($xu), $this->mesin_extra_errors($fd, $xu, $items, $page));
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
		// simpan foto (ukuran diperkecil), hapus foto yang dibuang
		list($items, $keymap, $foto_up, $foto_del) = $this->mesin_fotos_save($items, $uploads, $page);
		$stats = array('foto' => $foto_up);
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
		// [GENC-08OKT26-P3] urutan dibandingkan antar item AKTIF saja: item yang dihapus (tersimpan di tengah, tampil di daftar "dihapus") bukan perubahan urutan
		foreach($before as $sk => $list){ $a = array(); foreach($list as $x){ if($x['aktif']){ $a[] = $x['db']; } } $b = array(); foreach($items[$sk] as $x){ if($x['aktif']){ $b[] = $x['db']; } } if(array_values(array_intersect($a, $b)) !== array_values(array_intersect($b, $a))){ $order_changed = true; } }
		if($added){ $diff['item baru'] = $added; }
		if($removed){ $diff['item dihapus'] = $removed; }
		if($restored){ $diff['item dipulihkan'] = $restored; }
		if($changed){ $diff['item diubah'] = $changed; }
		if($order_changed){ $diff['urutan'] = 'diubah'; }
		if($stats['foto']){ $diff['foto'] = $stats['foto'] . ' diunggah'; }
		if($foto_del){ $diff['foto dihapus'] = $foto_del; }
		// [GENC-06OKT26-ISI] gambar bagian mesin di report
		$g_diff = array();
		if(!$locked && genc_reg_data()['profil_ok']){
			list($gambar, $g_diff) = $this->mesin_gambar_apply($page, $gb, $fd, array(), $items, $keymap, $xu, $page);
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
		$this->mesin_kolase_clear($page);   // [GENC-07OKT26-FOTO]
		$note = array(); foreach($diff as $k => $v){ $note[] = $k . ': ' . $v; }
		genc_app_log('mesin.edit', 'genc_mesin', $m['id'], 'Ubah isi mesin ' . $m['nama'] . ' - ' . implode('; ', $note), $diff);
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
		if(empty($fd['items']) && empty($fd['items_sent']) && !empty($_SERVER['CONTENT_LENGTH'])){
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
				$it = array('db' => '', 'aktif' => true, 'baru' => true, 'foto' => '', 'fotos' => array());
				foreach($maxlen as $k => $mx){ $it[$k] = genc_reg_clean(isset($r[$k]) ? $r[$k] : '', $mx); }
				$db = isset($r['db']) ? (string) $r['db'] : '';
				$key = isset($r['key']) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $r['key'])) : '';
				$hapus = !empty($r['hapus']);
				$empty = ($it['part'] . $it['standar'] . $it['metode'] . $it['alat'] . $it['durasi']) === '';
				$cur_f = array();
				if($db !== '' && isset($current[$db]) && !isset($seen[$db])){
					$seen[$db] = true;
					$it['db'] = $db; $it['baru'] = $current[$db]['baru']; $cur_f = $current[$db]['fotos'];
					if($hapus){ $x = array_merge($current[$db], array('aktif' => false)); $items[$sk][] = $x; $rows_old[$sk][] = $x + array('key' => $key); continue; }
					if($it['part'] === ''){ $item_err[] = 'Nama part tidak boleh kosong (' . $s['short'] . ' baris ' . (count($items[$sk]) + 1) . ').'; }
				}
				else{
					if($hapus || $empty){ continue; }
					if($it['part'] === ''){ $item_err[] = 'Isi nama part untuk item baru di ' . $s['short'] . '.'; }
					$it['_new'] = true;
				}
				list($it['fotos'], $it['_foto_rm']) = $this->mesin_item_fotos_post($r, $cur_f);   // [GENC-07OKT26-FOTO]
				$it['foto'] = $it['fotos'] ? $it['fotos'][0] : '';
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
		$item_err = array_merge($item_err, $this->mesin_file_errors($uploads), $this->mesin_fotos_count_errors($items, $uploads));
		$gb = $this->mesin_gambar_inputs();
		foreach($gb as $k => $tmp){ if(is_string($tmp) && strpos($tmp, '!') === 0){ $item_err[] = substr($tmp, 1); } }
		$xu = $this->mesin_file_inputs('gambar_extra_file', 'foto tambahan');   // [GENC-07OKT26-FOTO] foto tambahan kolase
		$item_err = array_merge($item_err, $this->mesin_file_errors($xu), $this->mesin_extra_errors($fd, $xu, $items, $profile));
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
		list($items, $keymap, $foto_up, $foto_del) = $this->mesin_fotos_save($items, $uploads, $profile);
		// gambar report
		$base = genc_reg_base_conf($profile);
		list($gambar, $g_diff) = $this->mesin_gambar_apply($profile, $gb, $fd, $base ? (array) $base['images'] : array(), $items, $keymap, $xu, $profile);
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
		$this->mesin_kolase_clear($profile);   // [GENC-07OKT26-FOTO]
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
				foreach(array('part', 'standar', 'metode', 'alat', 'durasi') as $k){ if((string) $x[$k] !== (string) $b[$i][$k]){ return false; } }
				if(genc_reg_fotos_of($x) !== genc_reg_fotos_of($b[$i])){ return false; }   // [GENC-07OKT26-FOTO]
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

	/** [GENC-07OKT26-FOTO] Batas foto per item (foto lama yang dipertahankan + unggahan baru). */
	protected function mesin_fotos_count_errors($items, $uploads){
		$err = array();
		foreach($items as $sk => $list){
			foreach($list as $it){
				if(empty($it['aktif'])){ continue; }
				$k = isset($it['_key']) ? $it['_key'] : '';
				$n = count((array) $it['fotos']) + count($this->mesin_file_ok($uploads, $k));
				if($n > GENC_REG_MAX_FOTO){ $err[] = 'Maksimal ' . GENC_REG_MAX_FOTO . ' foto per item (' . genc_e($it['part'] !== '' ? $it['part'] : 'item baru') . ': ' . $n . ' foto).'; }
			}
		}
		return $err;
	}

	/** Slot gambar tiap bagian + kunci item aktif di bagian itu: [slot => [key => true]]. */
	protected function mesin_slot_keys($items){
		$out = array(); $i = 0;
		foreach(genc_reg_sections() as $sk => $s){
			$slot = genc_reg_section_slot(array('key' => $sk), $i++);
			$out[$slot] = array();
			foreach((array) (isset($items[$sk]) ? $items[$sk] : array()) as $it){
				if(empty($it['aktif'])){ continue; }
				$k = isset($it['_key']) && $it['_key'] !== '' ? $it['_key'] : (isset($it['db']) ? $it['db'] : '');
				if($k !== ''){ $out[$slot][$k] = true; }
			}
		}
		return $out;
	}

	/** Foto tambahan kolase: item yang ditunjuk harus ada & aktif di bagian itu; batas jumlah. */
	protected function mesin_extra_errors($fd, $xu, $items, $profile){
		$err = array();
		$want = (isset($fd['gambar_extra_db']) && is_array($fd['gambar_extra_db'])) ? $fd['gambar_extra_db'] : array();
		$slots = $this->mesin_slot_keys($items);
		$labels = genc_reg_photo_slots();
		$add = array();
		foreach($xu as $xk => $files){
			$ok = $this->mesin_file_ok($xu, $xk);
			if(!$ok){ continue; }
			$slot = preg_replace('/_.*$/', '', $xk);
			$item = isset($want[$xk]) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $want[$xk])) : '';
			if(!isset($slots[$slot])){ continue; }
			if($item === '' || !isset($slots[$slot][$item])){ $err[] = 'Pilih item untuk foto tambahan ' . strtolower($labels[$slot]) . '.'; continue; }
			$add[$slot] = (isset($add[$slot]) ? $add[$slot] : 0) + count($ok);
		}
		foreach($add as $slot => $n){
			$now = 0;
			$raw = genc_reg_gambar_raw($profile);
			if(isset($raw[$slot]['extra']) && is_array($raw[$slot]['extra'])){ $now = count($raw[$slot]['extra']); }
			if($now + $n > GENC_REG_MAX_EXTRA){ $err[] = 'Foto tambahan ' . strtolower($labels[$slot]) . ' maksimal ' . GENC_REG_MAX_EXTRA . '.'; }
		}
		return $err;
	}

	/**
	 * Simpan foto baru tiap item (diperkecil) & hapus foto yang dibuang. Kolom 'foto' = foto pertama.
	 * @return array(items, keymap [key => db], jumlah diunggah, jumlah dihapus)
	 */
	protected function mesin_fotos_save($items, $uploads, $dir){
		$up = 0; $del = 0; $keymap = array();
		foreach($items as $sk => $list){
			foreach($list as $i => $it){
				$k = isset($it['_key']) ? $it['_key'] : '';
				if($k !== ''){ $keymap[$k] = $it['db']; }
				if(isset($it['db']) && $it['db'] !== ''){ $keymap[$it['db']] = $it['db']; }
				if(!array_key_exists('_foto_rm', $it)){ continue; }   // item disembunyikan / tidak terkirim: foto tetap
				$fotos = array_values((array) $it['fotos']);
				foreach($this->mesin_file_ok($uploads, $k) as $tmp){
					if(count($fotos) >= GENC_REG_MAX_FOTO){ break; }
					$path = $this->mesin_save_photo($tmp, $dir, $it['db']);
					if($path !== ''){ $fotos[] = $path; $up++; }
				}
				foreach((array) $it['_foto_rm'] as $p){ if($this->mesin_unlink_photo($p, $dir)){ $del++; } elseif(strpos($p, 'uploads/') !== 0){ $del++; } }
				$items[$sk][$i]['fotos'] = $fotos;
				$items[$sk][$i]['foto'] = $fotos ? $fotos[0] : '';
				unset($items[$sk][$i]['_foto_rm']);
			}
		}
		foreach($items as $sk => $list){ foreach($list as $i => $it){ unset($items[$sk][$i]['_new'], $items[$sk][$i]['_key'], $items[$sk][$i]['_foto_rm']); } }
		return array($items, $keymap, $up, $del);
	}

	/**
	 * Gambar bagian mesin di report (per bagian) [GENC-06OKT26-ISI + GENC-07OKT26-FOTO]:
	 *   panel   unggah gambar sendiri / kembali ke bawaan / sembunyikan bawaan (gambar[<slot>], gambar_hapus[<slot>])
	 *   mode    gambar_mode[<slot>] = auto (kolase otomatis dari foto item) | panel
	 *   hide    gambar_hide_sent[<slot>]=1 + gambar_hide[<slot>][] = foto yang tidak ikut kolase
	 *   extra   gambar_extra_db[<slot>_<n>] = item, gambar_extra_file[<slot>_<n>][] = foto; gambar_extra_del[<slot>][] = hapus
	 * @return array(array JSON gambar baru per slot, array diff)
	 */
	protected function mesin_gambar_apply($profile, $uploads, $fd, $base_images, $items = array(), $keymap = array(), $xu = array(), $dir = ''){
		if($dir === ''){ $dir = $profile; }
		$raw = genc_reg_gambar_raw($profile);
		$cfg_now = genc_reg_gambar_cfg($profile, $base_images);
		$cur = genc_reg_ov_gambar($profile);
		$out = array();
		foreach(genc_reg_photo_slots() as $k => $lbl){ $out[$k] = (isset($raw[$k]) && is_array($raw[$k])) ? $raw[$k] : array(); }
		$diff = array(); $n_up = 0; $n_del = 0;
		$hapus = (isset($fd['gambar_hapus']) && is_array($fd['gambar_hapus'])) ? $fd['gambar_hapus'] : array();
		$modes = (isset($fd['gambar_mode']) && is_array($fd['gambar_mode'])) ? $fd['gambar_mode'] : array();
		// semua foto item (untuk merapikan daftar "disembunyikan")
		$known = array();
		foreach($items as $list){ foreach($list as $it){ foreach(genc_reg_fotos_of($it) as $p){ $known[$p] = true; } } }
		$want = (isset($fd['gambar_extra_db']) && is_array($fd['gambar_extra_db'])) ? $fd['gambar_extra_db'] : array();
		foreach(genc_reg_photo_slots() as $k => $lbl){
			$o = $out[$k];
			// ---- panel
			if(isset($uploads[$k]) && is_string($uploads[$k]) && strpos($uploads[$k], '!') !== 0){
				$path = $this->mesin_save_photo($uploads[$k], $dir, 'gambar-' . $k);
				if($path !== ''){
					$sz = @getimagesize(ROOT . $path);
					$w = 152; $h = $sz ? (int) round(152 * $sz[1] / max(1, $sz[0])) : 135;
					if($h > 260){ $w = (int) round($w * 260 / $h); $h = 260; }
					if($h < 40){ $h = 40; }
					if(isset($cur[$k]) && $cur[$k]['path'] !== ''){ $this->mesin_unlink_photo($cur[$k]['path'], $dir); }
					$o['path'] = $path; $o['w'] = $w; $o['h'] = $h;
					if(!isset($modes[$k])){ $modes[$k] = 'panel'; }   // unggah gambar sendiri = pakai gambar itu
					$n_up++;
				}
			}
			elseif(!empty($hapus[$k])){
				$orig = isset($base_images[$k]) ? (string) $base_images[$k] : '';
				if(isset($cur[$k])){
					if($cur[$k]['path'] !== ''){ $this->mesin_unlink_photo($cur[$k]['path'], $dir); }
					unset($o['path'], $o['w'], $o['h']);   // kembali ke gambar bawaan (atau kosong kalau memang tidak ada)
					if($cur[$k]['path'] !== '' && $orig !== '' && is_file(ROOT . $orig)){ $diff['gambar ' . strtolower($lbl)] = 'kembali ke awal'; } else { $n_del++; }
				}
				elseif($orig !== '' && is_file(ROOT . $orig)){
					$o['path'] = ''; $o['w'] = 152; $o['h'] = 135;   // sembunyikan gambar bawaan
					$n_del++;
				}
			}
			// ---- mode
			$pm = isset($modes[$k]) && in_array($modes[$k], array('auto', 'panel'), true) ? $modes[$k] : '';
			if($pm !== '' && $pm !== $cfg_now[$k]['mode']){
				$o['mode'] = $pm;
				$diff['gambar ' . strtolower($lbl)] = $pm === 'auto' ? 'kolase otomatis dari foto item' : 'gambar sendiri';
			}
			// ---- foto tambahan: hapus
			$extra = $cfg_now[$k]['extra'];
			$del = (isset($fd['gambar_extra_del'][$k]) && is_array($fd['gambar_extra_del'][$k])) ? array_map('strval', $fd['gambar_extra_del'][$k]) : array();
			$n_xdel = 0;
			foreach($extra as $xi => $x){
				if(in_array($x['path'], $del, true)){ $this->mesin_unlink_photo($x['path'], $dir); unset($extra[$xi]); $n_xdel++; }
			}
			$extra = array_values($extra);
			// ---- foto tambahan: unggah baru (item lama = kolom db, item baru = key -> db setelah disimpan)
			$n_xup = 0;
			foreach($xu as $xk => $files){
				if(preg_replace('/_.*$/', '', $xk) !== $k){ continue; }
				$item = isset($want[$xk]) ? preg_replace('/[^a-z0-9_]/', '', strtolower((string) $want[$xk])) : '';
				$db = isset($keymap[$item]) ? $keymap[$item] : '';
				if($db === ''){ continue; }
				foreach($this->mesin_file_ok($xu, $xk) as $tmp){
					if(count($extra) >= GENC_REG_MAX_EXTRA){ break; }
					$path = $this->mesin_save_photo($tmp, $dir, 'extra-' . $k);
					if($path !== ''){ $extra[] = array('path' => $path, 'db' => $db); $n_xup++; }
				}
			}
			if($n_xup || $n_xdel){
				if($extra){ $o['extra'] = $extra; } else { unset($o['extra']); }
				if($n_xup){ $diff['foto tambahan ' . strtolower($lbl)] = $n_xup . ' diunggah'; }
				if($n_xdel){ $diff['foto tambahan ' . strtolower($lbl) . ' dihapus'] = $n_xdel; }
			}
			foreach($extra as $x){ $known[$x['path']] = true; }
			// ---- foto yang disembunyikan dari kolase
			$h_now = $cfg_now[$k]['hide'];
			$h_new = $h_now;
			if(!empty($fd['gambar_hide_sent'][$k])){
				$h_new = array();
				foreach((isset($fd['gambar_hide'][$k]) && is_array($fd['gambar_hide'][$k])) ? $fd['gambar_hide'][$k] : array() as $p){
					$p = str_replace('\\', '/', (string) $p);
					if(isset($known[$p]) && !in_array($p, $h_new, true)){ $h_new[] = $p; }
				}
			}
			$h_new = array_values(array_filter($h_new, function($p) use ($known){ return isset($known[$p]); }));   // foto yang sudah dihapus tidak dicatat lagi
			$a = $h_now; $b = $h_new; sort($a); sort($b);
			if($a !== $b){
				if($h_new){ $o['hide'] = $h_new; } else { unset($o['hide']); }
				$shown = count(array_diff($h_now, $h_new)); $hid = count(array_diff($h_new, $h_now));
				if($hid){ $diff['foto disembunyikan dari report ' . strtolower($lbl)] = $hid; }
				if($shown){ $diff['foto ditampilkan lagi di report ' . strtolower($lbl)] = $shown; }
			}
			if(empty($o)){ unset($out[$k]); } else { $out[$k] = $o; }
		}
		if($n_up){ $diff['gambar report'] = $n_up . ' diunggah'; }
		if($n_del){ $diff['gambar report dihapus'] = $n_del; }
		return array($out, $diff);
	}

	/** Kolase report dibuat ulang dari foto terbaru (cache lama dibuang). */
	protected function mesin_kolase_clear($profile){
		require_once ROOT . 'app/views/partials/_shared/checklist_report_plan.php';
		$dir = ROOT . checklist_rpt_kolase_dir($profile);
		if(!is_dir($dir)){ return; }
		foreach((array) glob($dir . '/*.jpg') as $f){ @unlink($f); }
		@rmdir($dir);
	}

	/** POST kunci=1|0: kunci / buka kunci isi checklist (bawaan atau jenis baru). */
	/**
	 * Kunci / buka kunci. [GENC-08OKT26-P3] Berlaku untuk SELURUH mesin (semua jenis isinya, pengaturan, unit):
	 * semua profil folder itu dikunci / dibuka bersama. Saat dikunci hanya saklar Aktif yang masih bisa diubah.
	 */
	protected function mesin_edit_kunci($profile, $fd){
		$d = genc_reg_data();
		$key = isset($d['mesin'][$profile]) ? $profile : genc_reg_folder_of_profile($profile);
		if($key === ''){ $key = $profile; }
		$tree = $this->mesin_tree();
		if(!isset($tree[$key])){ $this->mesin_toast('Mesin tidak ditemukan.', 'danger'); return $this->redirect('mesin'); }
		$f = $tree[$key];
		$back = 'mesin?pilih=' . rawurlencode('f:' . $key) . '#isi';
		if($e = genc_reg_ensure_tables()){ $this->mesin_toast('Tabel registry belum bisa dibuat: ' . genc_e($e), 'danger'); return $this->redirect($back); }
		$want = !empty($fd['kunci']) ? 1 : 0;
		if($f['jenis'] && $want && $f['items'] === 0){ $this->mesin_toast('Tambahkan item dulu sebelum mengunci.', 'danger'); return $this->redirect($back); }
		$state = $this->mesin_folder_lock($f);
		if(($want && $state === 'all') || (!$want && $state === '')){ $this->mesin_toast($want ? 'Sudah terkunci.' : 'Sudah tidak terkunci.', 'info'); return $this->redirect($back); }
		$pdo = genc_reg_db();
		$st = $pdo->prepare("INSERT INTO genc_profil (profil, kunci, kunci_at, kunci_by) VALUES (?,?,?,?)
			ON DUPLICATE KEY UPDATE kunci = VALUES(kunci), kunci_at = VALUES(kunci_at), kunci_by = VALUES(kunci_by)");
		foreach($this->mesin_folder_profiles($f) as $p){ $st->execute(array($p, $want, datetime_now(), USER_NAME)); }
		genc_reg_data(true);
		genc_app_log('mesin.kunci', 'genc_profil', 0, ($want ? 'Kunci' : 'Buka kunci') . ' mesin ' . $f['title'], array('mesin' => $key, 'kunci' => $want ? 'dikunci' : 'dibuka'));
		$this->mesin_toast($want ? '<i class="fa fa-lock"></i> <b>' . genc_e($f['title']) . '</b> dikunci &mdash; pengaturan, unit &amp; isi checklist tidak bisa diubah sampai kuncinya dibuka (saklar Aktif tetap bisa).'
			: '<i class="fa fa-unlock"></i> Kunci <b>' . genc_e($f['title']) . '</b> dibuka &mdash; bisa diubah lagi. Kunci lagi setelah selesai.', 'success');
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
		if(!$row || (($row['items'] === null || $row['items'] === '') && ($row['gambar'] === null || $row['gambar'] === ''))){ $this->mesin_toast('Isi checklist sudah sama dengan isi awal.', 'info'); return $this->redirect($back); }
		genc_reg_db()->prepare("UPDATE genc_profil SET items = NULL, gambar = NULL, updated_at = ?, updated_by = ? WHERE profil = ?")->execute(array(datetime_now(), USER_NAME, $profile));
		genc_reg_data(true);
		$this->mesin_kolase_clear($profile);   // [GENC-07OKT26-FOTO]
		genc_app_log('mesin.isi', 'genc_profil', 0, 'Kembalikan isi checklist ' . $label . ' ke isi awal', array('profil' => $profile));
		$this->mesin_toast('Isi checklist <b>' . genc_e($label) . '</b> kembali ke isi awal. Item yang tadinya ditambah disembunyikan; datanya tetap tersimpan.', 'success');
		return $this->redirect($back);
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
		$bf = genc_reg_builtin_folders();
		foreach($out as $p => $n){
			$fk = genc_reg_folder_of_profile($p);
			if($fk !== '' && !empty($bf[$fk]['hapus'])){ unset($out[$p]); continue; }   // [GENC-13OKT26] mesin awal yang dihapus permanen
			if($fk !== ''){ $n = genc_reg_rename_text($fk, $n); }   // [GENC-08OKT26-P3] nama baru
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
					'fotos' => genc_reg_fotos_existing($it),   // [GENC-07OKT26-FOTO]
				);
			}
		}
		return $out;
	}

	// =====================================================================
	//  HAK AKSES AWAL JENIS MESIN BARU - per jabatan (role)     [GENC-08OKT26-AKSES]
	// =====================================================================

	/** Kolom yang bisa dicentang (sama dengan matriks Role Permissions). */
	protected function mesin_perm_actions(){
		return array('list' => 'Lihat', 'view' => 'Detail', 'add' => 'Isi', 'edit' => 'Ubah', 'delete' => 'Hapus', 'approve' => 'Approve');
	}

	/** Semua jabatan: [role_id => nama]. */
	protected function mesin_roles(){
		$pdo = genc_reg_db();
		$out = array();
		if(!$pdo){ return $out; }
		try { foreach($pdo->query("SELECT role_id, role_name FROM roles ORDER BY role_id")->fetchAll() as $r){ $out[(int) $r['role_id']] = (string) $r['role_name']; } }
		catch(\Throwable $e){ }
		return $out;
	}

	/** [GENC-08OKT26-P3] Centang awal KOSONG (dulu = Mesin Geprek). Salinan cepat: mesin_perm_sources(). */
	protected function mesin_perm_default(){
		return array();
	}

	/** [GENC-08OKT26-P3] Tombol "Salin dari mesin...": [page => array('name', 'perm' => [role_id => [aksi]])]. */
	protected function mesin_perm_sources(){
		$pdo = genc_reg_db();
		$out = array();
		if(!$pdo){ return $out; }
		$acts = array_keys($this->mesin_perm_actions());
		foreach($this->mesin_acl_sources() as $page => $name){
			$perm = array();
			try{
				$st = $pdo->prepare("SELECT role_id, action_name FROM role_permissions WHERE page_name = ?");
				$st->execute(array($page));
				foreach($st->fetchAll() as $r){ if(in_array($r['action_name'], $acts, true)){ $perm[(int) $r['role_id']][] = $r['action_name']; } }
			} catch(\Throwable $e){ }
			$out[$page] = array('name' => $name, 'perm' => $perm);
		}
		return $out;
	}

	/** Centang dari form: perm[<role_id>][] = aksi. Aksi apa pun tanpa Lihat -> Lihat ikut dicentang (tanpa Lihat menu tidak muncul). */
	protected function mesin_perm_post($fd){
		$roles = $this->mesin_roles();
		$acts = array_keys($this->mesin_perm_actions());
		$out = array();
		$raw = (isset($fd['perm']) && is_array($fd['perm'])) ? $fd['perm'] : array();
		foreach($raw as $rid => $list){
			$rid = (int) $rid;
			if(!isset($roles[$rid]) || !is_array($list)){ continue; }
			$a = array_values(array_intersect($acts, array_map('strval', $list)));
			if($a && !in_array('list', $a, true)){ array_unshift($a, 'list'); }
			if($a){ $out[$rid] = $a; }
		}
		return $out;
	}

	/** Simpan hak akses awal. @return array(jumlah baris, ringkasan untuk log) */
	protected function mesin_perm_save($page, $perm){
		$pdo = genc_reg_db();
		if(!$pdo){ return array(0, ''); }
		$roles = $this->mesin_roles(); $names = $this->mesin_perm_actions();
		$n = 0; $txt = array();
		$st = $pdo->prepare("INSERT INTO role_permissions (role_id, page_name, action_name) SELECT ?, ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM role_permissions WHERE role_id = ? AND page_name = ? AND action_name = ?)");
		foreach($perm as $rid => $acts){
			foreach($acts as $a){ $st->execute(array($rid, $page, $a, $rid, $page, $a)); $n += $st->rowCount(); }
			$txt[] = $roles[$rid] . ': ' . implode(', ', array_map(function($a) use ($names){ return $names[$a]; }, $acts));
		}
		return array($n, implode('; ', $txt));
	}

	/** Halaman acuan hak akses awal: [page => nama]. */
	protected function mesin_acl_sources(){
		$out = array();
		$names = array('palletmover' => array('palletmover', 'Pallet Mover & Stacker'), 'forklift' => array('forklift', 'Forklift'), 'agv_table_top_lift' => array('agv', 'AGV Table Top Lift'),
			'counterbalance' => array('agv', 'AGV Counterbalance'), 'kir3p01dp001' => array('dumping', 'Mesin Dumping KIR3'), 'mesin_geprek' => array('geprek', 'Mesin Geprek'), 'conveyor' => array('conveyor', 'Conveyor'));
		$bf = genc_reg_builtin_folders();
		foreach($names as $p => $x){ if(empty($bf[$x[0]]['arsip'])){ $out[$p] = genc_reg_rename_text($x[0], $x[1]); } }   // [GENC-08OKT26-P3] nama baru, tanpa arsip
		foreach(genc_reg_data()['mesin'] as $p => $m){ if(empty($m['arsip'])){ $out[$p] = $m['nama']; } }
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
	 * Foto yang diunggah per item: [key => [tmp path | '!pesan error', ...]].
	 * Input: <input type="file" name="foto[<key>][]" multiple> (bisa beberapa input per item) - [GENC-07OKT26-FOTO].
	 * Bentuk lama foto[<key>] (1 file) tetap diterima.
	 */
	protected function mesin_photo_inputs(){
		return $this->mesin_file_inputs('foto', 'foto');
	}

	/** Baca $_FILES[$field][name][key] (string atau array) -> [key => [tmp | '!pesan']]. */
	protected function mesin_file_inputs($field, $what, $keys_ok = null){
		$out = array();
		if(empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])){ return $out; }
		foreach($_FILES[$field]['name'] as $rawkey => $names){
			$key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $rawkey));
			if($key === '' || ($keys_ok !== null && !isset($keys_ok[$key]))){ continue; }
			$f = array('name' => $names, 'error' => $_FILES[$field]['error'][$rawkey], 'tmp_name' => $_FILES[$field]['tmp_name'][$rawkey], 'size' => $_FILES[$field]['size'][$rawkey]);
			if(!is_array($f['name'])){ foreach($f as $k => $v){ $f[$k] = array($v); } }
			foreach($f['name'] as $i => $name){
				if(is_array($name)){ continue; }
				$err = (int) $f['error'][$i];
				if($err === UPLOAD_ERR_NO_FILE){ continue; }
				if($err !== UPLOAD_ERR_OK){ $out[$key][] = '!' . ucfirst($what) . ' "' . genc_e((string) $name) . '" gagal diunggah' . (in_array($err, array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), true) ? ' (terlalu besar)' : '') . '.'; continue; }
				$tmp = $f['tmp_name'][$i];
				$info = @getimagesize($tmp);
				if(!$info || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP), true)){ $out[$key][] = '!File "' . genc_e((string) $name) . '" bukan foto (JPG / PNG).'; continue; }
				if((int) $f['size'][$i] > 12 * 1048576){ $out[$key][] = '!' . ucfirst($what) . ' "' . genc_e((string) $name) . '" lebih dari 12 MB.'; continue; }
				$out[$key][] = $tmp;
			}
		}
		return $out;
	}

	/** Pesan error dari hasil mesin_file_inputs(). */
	protected function mesin_file_errors($files){
		$err = array();
		foreach($files as $list){ foreach((array) $list as $t){ if(is_string($t) && strpos($t, '!') === 0){ $err[] = substr($t, 1); } } }
		return $err;
	}

	/** File unggahan yang sah (tanpa pesan error) untuk 1 key. */
	protected function mesin_file_ok($files, $key){
		$out = array();
		if($key === '' || !isset($files[$key])){ return $out; }
		foreach((array) $files[$key] as $t){ if(is_string($t) && $t !== '' && strpos($t, '!') !== 0){ $out[] = $t; } }
		return $out;
	}

	/**
	 * [GENC-07OKT26-FOTO] Foto item yang dikirim form: urutan baru foto lama yang dipertahankan.
	 * items[..][fotos_sent]=1 + items[..][fotos][]=path (urut). Tanpa fotos_sent (form lama) -> foto tetap.
	 * @return array(array dipertahankan, array dihapus)
	 */
	protected function mesin_item_fotos_post($r, $cur){
		$cur = array_values((array) $cur);
		if(!empty($r['fotos_sent'])){
			$kept = array();
			foreach((isset($r['fotos']) && is_array($r['fotos'])) ? $r['fotos'] : array() as $p){
				$p = str_replace('\\', '/', (string) $p);
				if(in_array($p, $cur, true) && !in_array($p, $kept, true)){ $kept[] = $p; }
			}
		}
		else{ $kept = $cur; }
		if(!empty($r['foto_hapus'])){ $kept = array(); }   // bentuk lama: hapus foto
		return array($kept, array_values(array_diff($cur, $kept)));
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
	/**
	 * Foto dilepas dari item / gambar. [GENC-08OKT26-P3] Berkasnya TIDAK dihapus dari disk lagi: riwayat versi
	 * bisa memulihkan isi lama beserta fotonya. @return bool true = foto unggahan menu ini (dihitung "dihapus")
	 */
	protected function mesin_unlink_photo($path, $page){
		$path = str_replace('\\', '/', (string) $path);
		if(strpos($path, 'uploads/mesin/' . $page . '/') !== 0 || strpos($path, '..') !== false){ return false; }
		return true;
	}

	// =====================================================================
	//  TAMPILAN
	// =====================================================================

	protected function mesin_render($data){
		$data['can'] = array('add' => ACL::is_allowed('mesin/add'), 'edit' => ACL::is_allowed('mesin/edit'), 'delete' => ACL::is_allowed('mesin/delete'),
			'perm' => ACL::is_allowed('role_permissions/list'));
		$data['copy_sources'] = $this->mesin_copy_sources();
		$data['acl_sources']  = $this->mesin_acl_sources();
		$data['roles']        = $this->mesin_roles();              // [GENC-08OKT26-AKSES]
		$data['perm_actions'] = $this->mesin_perm_actions();
		$data['perm_default'] = $this->mesin_perm_default();
		$data['perm_sources'] = ($data['new'] === 'jenis') ? $this->mesin_perm_sources() : array();   // [GENC-08OKT26-P3]
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
			// [GENC-06OKT26-ISI + GENC-07OKT26-FOTO] gambar bagian mesin per bagian: panel / kolase otomatis
			$gsecs = array(); $slot_names = genc_reg_photo_slots();
			$base_imgs = ($base && isset($base['images'])) ? (array) $base['images'] : array();
			$gcfg = genc_reg_gambar_cfg($p, $base_imgs);
			$ovg = genc_reg_ov_gambar($p);
			if($c){
				$si = 0;
				foreach(genc_reg_sections() as $sk => $sdef){
					$s = null; foreach($c['sections'] as $x){ if(isset($x['key']) && $x['key'] === $sk){ $s = $x; } }
					$k = genc_reg_section_slot($s ? $s : array('key' => $sk), $si++);
					if(!isset($slot_names[$k])){ continue; }
					$cur = isset($c['images'][$k]) ? (string) $c['images'][$k] : '';
					$orig = isset($base_imgs[$k]) ? (string) $base_imgs[$k] : '';
					$base_sec = null; if($base){ foreach((array) $base['sections'] as $x){ if(isset($x['key']) && $x['key'] === $sk){ $base_sec = $x; } } }
					$items = ($s && !empty($s['items'])) ? $s['items'] : array();
					$gsecs[$sk] = array(
						'slot'   => $k,
						'label'  => $sdef['short'],
						'cur'    => ($cur !== '' && is_file(ROOT . $cur)) ? $cur : '',
						'orig'   => ($orig !== '' && is_file(ROOT . $orig)) ? $orig : '',
						'is_ov'  => isset($ovg[$k]),
						'mode'   => $gcfg[$k]['mode'],
						'hide'   => $gcfg[$k]['hide'],
						'extra'  => $gcfg[$k]['extra'],
						'items'  => $items,
						// bagian yang di form resmi memang kosong tapi punya gambar (Pelumasan Conveyor): gambar tetap tampil
						'always' => $base_sec && empty($base_sec['items']) && $orig !== '' && is_file(ROOT . $orig) && isset($base_sec['photo']) && $base_sec['photo'] === $k,
					);
				}
			}
			$slots = $gsecs;
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
				'gsecs'    => $gsecs,
				'tables'   => $f['jenis'] ? array($p) : genc_reg_profile_tables($p),
				'ready'    => genc_reg_data()['profil_ok'],
			);
		}
		// [GENC-08OKT26-P3] pengaturan mesin, kunci menyeluruh, riwayat versi
		$data['ident'] = null; $data['flock'] = ''; $data['versions'] = array();
		if(!empty($data['sel'])){
			$f = $data['sel']['folder'];
			$data['ident'] = $this->mesin_folder_ident($f);
			$data['flock'] = $this->mesin_folder_lock($f);
			if($data['sel']['type'] === 'folder'){ $data['versions'] = $this->mesin_ver_list($f['key']); }
		}
		// [GENC-13OKT26] hapus permanen: pratinjau untuk yang ada di Arsip
		$data['purge'] = null;
		if(!empty($data['sel']) && $data['can']['delete']){
			$s = $data['sel'];
			$arch = $s['type'] === 'folder' ? !empty($s['folder']['arsip']) : (!empty($s['unit']['arsip']) && empty($s['folder']['arsip']));
			if($arch){ $data['purge'] = $this->mesin_purge_plan($s); $data['purge']['name'] = $this->mesin_purge_name($s); }
		}
		// [GENC-13OKT26] daftar cadangan hapus permanen (hanya role yang boleh menghapus)
		if(!isset($data['panel'])){ $data['panel'] = ''; }
		if(!isset($data['cad_siap'])){ $data['cad_siap'] = ''; }
		$data['cad_list'] = $data['can']['delete'] ? genc_cad_list() : array();
		if($data['panel'] === 'cadangan' && !$data['can']['delete']){ $data['panel'] = ''; }
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
