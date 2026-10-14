<?php
/**
 * ============================================================================
 *  TAMPILAN - "Mode edit tampilan" (Super Editor UI) + Riwayat tampilan   [GENC-13OKT26-TAMPILAN]
 *
 *  tampilan                     Riwayat tampilan (hak tampilan/list): versi sekarang + versi lain + layar penuh
 *  tampilan/edit/mode/<m>       GET + csrf_token: masuk Mode edit (edit) / Pratinjau (pratinjau) / keluar (keluar)
 *  tampilan/edit/draf           POST (fetch): ubah draf milik user (teks / data / susunan / tema) -> JSON
 *  tampilan/edit/simpan         POST: draf -> versi baru (kalau ada perubahan nyata) -> berlaku untuk semua user
 *  tampilan/edit/batal          POST: buang draf
 *  tampilan/edit/versi/<no>     POST: Kembalikan ke versi <no> = pindah penunjuk "sekarang" (TIDAK membuat versi baru)
 *
 *  Semua aksi ubah memakai hak tampilan/edit (Role Permissions). Inti: _shared/genc_tampilan.php.
 *  Jalan pintas data (nama mesin/unit, area, pelaksanaan, label hasil cek) TIDAK disimpan di sini: editor
 *  mengirimnya lewat form asli Mesin & Unit / Master Select saat Simpan (satu sumber, riwayatnya sendiri).
 * ============================================================================
 */
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';
require_once __DIR__ . '/../views/partials/_shared/genc_helpers.php';
require_once __DIR__ . '/../views/partials/_shared/genc_registry.php';
require_once __DIR__ . '/../views/partials/_shared/genc_tampilan.php';

class TampilanController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = 'genc_tampilan';
	}

	/** Riwayat tampilan. */
	function index(){
		$ok = gx_ensure_tables();
		$cur = gx_current(true);
		$vers = gx_versions();
		$rows = array();
		foreach($vers as $v){
			$m = gx_dec($v['meta']);
			$rows[] = array('versi' => (int) $v['versi'], 'sekarang' => (int) $v['sekarang'] === 1, 'ringkasan' => (string) $v['ringkasan'],
				'by' => (string) $v['created_by'], 'at' => (string) $v['created_at'], 'lines' => isset($m['lines']) ? (array) $m['lines'] : array(),
				'data' => isset($m['data']) ? (array) $m['data'] : array(), 'dari' => isset($m['dari']) ? (int) $m['dari'] : 0,
				'catatan' => isset($m['catatan']) ? (string) $m['catatan'] : '', 'pages' => array_filter(explode('|', (string) $v['halaman'])));
		}
		if(!$rows){
			$rows[] = array('versi' => 1, 'sekarang' => true, 'ringkasan' => 'Tampilan awal', 'by' => '', 'at' => '', 'lines' => array(), 'data' => array(), 'dari' => 0, 'catatan' => '', 'pages' => array(), 'virtual' => true);
		}
		$d = gx_draft_get(null, true);
		$this->view->page_title = 'Riwayat tampilan';
		$this->render_view('tampilan/index.php', array(
			'ok' => $ok, 'rows' => $rows, 'cur_no' => $cur['no'], 'draft' => $d, 'draft_n' => gx_draft_count($d),
			'can_edit' => ACL::is_allowed('tampilan/edit'), 'mode' => gx_mode(), 'csrf' => Csrf::$token,
		), 'main_layout.php');
	}

	/* ---------------------------------------------------------------- bantuan */

	protected function tp_json($a){
		while(ob_get_level() > 0){ @ob_end_clean(); }
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store');
		echo json_encode($a, JSON_UNESCAPED_UNICODE);
		exit;
	}

	protected function tp_toast($msg, $type = 'success'){
		$i = array('success' => 'fa-check-circle', 'danger' => 'fa-exclamation-circle', 'info' => 'fa-info-circle');
		$this->set_flash_msg('<div class="genc-toast genc-toast--' . $type . '" role="status"><i class="fa ' . (isset($i[$type]) ? $i[$type] : $i['info']) . '"></i><span>' . $msg . '</span>'
			. '<button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>', 'custom');
	}

	/** Alamat kembali yang aman (hanya di dalam aplikasi ini). */
	protected function tp_back($u, $def = 'tampilan'){
		$u = trim((string) $u);
		$base = set_url('');
		if($u !== '' && strpos($u, $base) === 0 && !preg_match('/[\r\n]/', $u)){ return $u; }
		if($u !== '' && preg_match('#^[a-z0-9_][a-z0-9_/\-]*(\?[^\r\n<>"]*)?$#i', $u)){ return set_url($u); }
		return set_url($def);
	}

	protected function tp_go($url){
		header('Location: ' . $url);
		exit;
	}

	/** Teks dari user: tanpa karakter kontrol & penanda, maks 500 huruf. */
	protected function tp_clean($s, $max = 500){
		$s = gx_strip((string) $s);
		$s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $s);
		$s = preg_replace('/[\x{E000}-\x{F8FF}]/u', '', (string) $s);
		$s = preg_replace('/\s+/u', ' ', (string) $s);
		$s = trim((string) $s);
		if(function_exists('mb_substr')){ $s = mb_substr($s, 0, $max, 'UTF-8'); } else { $s = substr($s, 0, $max); }
		return $s;
	}

	protected function tp_key_ok($k){
		return is_string($k) && strlen($k) < 300 && preg_match('#^(g\|[0-9a-f]{12}|v\|[A-Za-z0-9_./\-]{1,200}\|[0-9a-f]{12}|p\|[a-z0-9_:./\-]{1,200}\|[0-9a-f]{12})$#', $k);
	}

	/* ---------------------------------------------------------------- aksi */

	function edit($kind = '', $arg = '', $formdata = null){
		// Router menambahkan $_POST sebagai argumen TERAKHIR (jumlah segmen URL tidak tetap)
		$args = func_get_args(); $last = $args ? end($args) : null;
		$formdata = (is_post_request() && is_array($last)) ? $last : null;
		if(is_array($kind)){ $kind = ''; }
		if(is_array($arg)){ $arg = ''; }
		$kind = strtolower((string) $kind);
		if($kind === 'mode'){ return $this->tp_mode($arg); }
		if($kind === 'lihat'){ return $this->tp_lihat($arg); }
		if($formdata === null){ return $this->tp_go(set_url('tampilan')); }
		if(!gx_ensure_tables()){
			if($kind === 'draf'){ $this->tp_json(array('ok' => false, 'msg' => 'Tabel tampilan belum bisa dibuat. Jalankan database/genc_tampilan_14okt26.sql di phpMyAdmin.')); }
			$this->tp_toast('Tabel tampilan belum bisa dibuat. Jalankan <span class="genc-mono">database/genc_tampilan_14okt26.sql</span> di phpMyAdmin.', 'danger');
			return $this->tp_go(set_url('tampilan'));
		}
		if($kind === 'draf'){ return $this->tp_draf($formdata); }
		if($kind === 'simpan'){ return $this->tp_simpan($formdata); }
		if($kind === 'batal'){ return $this->tp_batal($formdata); }
		if($kind === 'versi'){ return $this->tp_versi((int) $arg, $formdata); }
		return $this->tp_go(set_url('tampilan'));
	}

	/**
	 * Halaman Masuk / Daftar hanya terlihat sebelum login -> di Mode edit / Pratinjau ditampilkan di sini
	 * (tanpa sidebar, sama persis dengan aslinya) supaya teksnya bisa diubah. "Halaman ini" = halaman aslinya.
	 */
	protected function tp_lihat($w){
		if(gx_mode() === ''){ return $this->tp_go(set_url('tampilan')); }
		$w = $w === 'daftar' ? 'daftar' : 'masuk';
		$GLOBALS['gx_auth_preview'] = true;
		$GLOBALS['gx_page_override'] = $w === 'daftar' ? 'index/register' : 'index/index';
		$this->view->page_title = ($w === 'daftar' ? 'Daftar' : 'Masuk') . ' - ' . SITE_NAME;
		return $this->render_view($w === 'daftar' ? 'index/register.php' : 'index/index.php', null, 'main_layout.php');
	}

	/** Masuk / keluar Mode edit atau Pratinjau (GET dari tombol topbar, dicek csrf_token). */
	protected function tp_mode($m){
		$tok = isset($_GET['csrf_token']) ? (string) $_GET['csrf_token'] : '';
		$back = $this->tp_back(isset($_GET['kembali']) ? $_GET['kembali'] : '', 'home');
		if($tok === '' || $tok !== (string) get_session('csrf_token')){ return $this->tp_go($back); }
		$m = strtolower((string) $m);
		if($m === 'edit' || $m === 'pratinjau'){
			gx_ensure_tables();
			$prev = (string) get_session('gx_mode');
			set_session('gx_mode', $m);
			if($m === 'edit' && $prev === ''){
				$d = gx_draft_get(null, true);
				genc_app_log('tampilan.mode', 'genc_tampilan', '', 'Masuk Mode edit tampilan' . ($d && gx_draft_count($d) ? ' (melanjutkan draf: ' . gx_draft_count($d) . ' perubahan)' : ''));
			}
		}
		else{
			clear_session('gx_mode');
		}
		return $this->tp_go($back);
	}

	/**
	 * Ubah draf (fetch). POST ops = JSON array operasi:
	 *  {op:'teks', key, t (null = teks awal), d (teks awal), w (tempat), clear:[kunci lain yang dibuang]}
	 *  {op:'data', kind, id, field, sel, old, new (null = batal)}
	 *  {op:'reset', keys:[...]}  (Kembalikan halaman ini: buang perubahan teks kunci-kunci itu)
	 *  {op:'semua'}              (buang seluruh draf)
	 */
	protected function tp_draf($fd){
		$ops = json_decode(isset($fd['ops']) ? (string) $fd['ops'] : '[]', true);
		if(!is_array($ops)){ $this->tp_json(array('ok' => false, 'msg' => 'Data tidak terbaca.')); }
		$cur = gx_current(true);
		$d = gx_draft_get(null, true);
		if(!$d){ $d = gx_draft_norm(array()); }
		if(!$d['base']){ $d['base'] = $cur['no']; }
		$conf = $cur['conf'];
		$n = 0; $err = array();
		foreach(array_slice($ops, 0, 200) as $o){
			if(!is_array($o) || !isset($o['op'])){ continue; }
			if($o['op'] === 'teks'){
				$k = isset($o['key']) ? $o['key'] : '';
				if(!$this->tp_key_ok($k)){ $err[] = 'kunci'; continue; }
				$def = $this->tp_clean(isset($o['d']) ? $o['d'] : '', 600);
				$w = $this->tp_clean(isset($o['w']) ? $o['w'] : '', 120);
				$t = (isset($o['t']) && $o['t'] !== null) ? $this->tp_clean($o['t']) : null;
				if($t === '' || ($t !== null && gx_norm($t) === gx_norm($def))){ $t = null; }   // kosong / sama dengan awal = teks awal
				$this->tp_set_teks($d, $conf, $k, $t, $def, $w);
				foreach((isset($o['clear']) && is_array($o['clear']) ? array_slice($o['clear'], 0, 3) : array()) as $ck){
					if($this->tp_key_ok($ck) && $ck !== $k){ $this->tp_set_teks($d, $conf, $ck, null, $def, $w); }
				}
				$n++;
			}
			elseif($o['op'] === 'data'){
				$kind = isset($o['kind']) ? (string) $o['kind'] : '';
				$field = isset($o['field']) ? (string) $o['field'] : '';
				if(!in_array($kind . '.' . $field, array('mesin.nama', 'mesin.area', 'mesin.pelaksanaan', 'unit.label', 'pilihan.label'), true)){ $err[] = 'data'; continue; }
				if($kind === 'pilihan' ? !ACL::is_allowed('master_select/edit') : !ACL::is_allowed('mesin/edit')){ $err[] = 'hak'; continue; }
				$id = $this->tp_clean(isset($o['id']) ? $o['id'] : '', 120);
				$sel = $this->tp_clean(isset($o['sel']) ? $o['sel'] : '', 160);
				$dk = $kind . '|' . $id . '|' . $field;
				$old = $this->tp_clean(isset($o['old']) ? $o['old'] : '', 150);
				$new = (isset($o['new']) && $o['new'] !== null) ? $this->tp_clean($o['new'], $kind === 'pilihan' ? 20 : ($field === 'pelaksanaan' ? 150 : ($field === 'area' ? 100 : 60))) : null;
				if($new === null || $new === '' || $new === $old){ unset($d['data'][$dk]); }
				else{ $d['data'][$dk] = array('kind' => $kind, 'id' => $id, 'field' => $field, 'sel' => $sel, 'old' => $old, 'new' => $new); }
				$n++;
			}
			elseif($o['op'] === 'susunan'){   // [GENC-14OKT26-SUSUNAN] {op, sk: 'p|halaman' / 'v|file', c: kunci grup, spec: {...} / null}
				$sk = isset($o['sk']) ? (string) $o['sk'] : ''; $ck = isset($o['c']) ? (string) $o['c'] : '';
				if(!$this->tp_sk_ok($sk) || $ck === '' || strlen($ck) > 400){ $err[] = 'susunan'; continue; }
				$spec = isset($o['spec']) && is_array($o['spec']) ? $this->tp_spec_clean($o['spec']) : null;
				$this->tp_set_susunan($d, $conf, $sk, $ck, $spec);
				foreach((isset($o['clear']) && is_array($o['clear']) ? array_slice($o['clear'], 0, 3) : array()) as $csk){
					if($this->tp_sk_ok($csk) && $csk !== $sk){ $this->tp_set_susunan($d, $conf, $csk, $ck, null); }
				}
				$n++;
			}
			elseif($o['op'] === 'tema'){   // [GENC-14OKT26-TEMA] {op, k: dasar|aksen|ok|nok|pr|pending, v: nilai / null = bawaan}
				$k = isset($o['k']) ? (string) $o['k'] : '';
				if(!in_array($k, array('dasar', 'aksen', 'ok', 'nok', 'pr', 'pending'), true)){ $err[] = 'tema'; continue; }
				$v = isset($o['v']) && $o['v'] !== null ? gx_tema_val($k, $o['v']) : null;
				if(isset($o['v']) && $o['v'] !== null && $v === null){ $err[] = 'tema'; continue; }
				$st = gx_tema_status();
				if($v === ($k === 'dasar' ? 'terang' : ($k === 'aksen' ? 'biru' : (isset($st[$k]) ? $st[$k][1] : '')))){ $v = null; }   // sama dengan bawaan
				$has = isset($conf['tema'][$k]);
				if($v === null){ if($has){ $d['tema'][$k] = null; } else { unset($d['tema'][$k]); } }
				elseif($has && $conf['tema'][$k] === $v){ unset($d['tema'][$k]); }
				else{ $d['tema'][$k] = $v; }
				$n++;
			}
			elseif($o['op'] === 'reset'){
				foreach((isset($o['keys']) && is_array($o['keys']) ? array_slice($o['keys'], 0, 2000) : array()) as $k){
					if(!$this->tp_key_ok($k)){ continue; }
					$def = isset($conf['teks'][$k]['d']) ? $conf['teks'][$k]['d'] : (isset($d['teks'][$k]['d']) ? $d['teks'][$k]['d'] : '');
					$w = isset($conf['teks'][$k]['w']) ? $conf['teks'][$k]['w'] : (isset($d['teks'][$k]['w']) ? $d['teks'][$k]['w'] : '');
					$this->tp_set_teks($d, $conf, $k, null, $def, $w);
				}
				foreach((isset($o['data']) && is_array($o['data']) ? $o['data'] : array()) as $dk){ unset($d['data'][(string) $dk]); }
				foreach((isset($o['susunan']) && is_array($o['susunan']) ? array_slice($o['susunan'], 0, 500) : array()) as $pair){
					if(is_array($pair) && count($pair) === 2 && $this->tp_sk_ok((string) $pair[0])){ $this->tp_set_susunan($d, $conf, (string) $pair[0], (string) $pair[1], null); }
				}
				$n++;
			}
			elseif($o['op'] === 'semua'){
				$d = gx_draft_norm(array('base' => $cur['no'])); $n++;
			}
		}
		if(gx_draft_count($d) === 0){ gx_draft_del(); }
		elseif(!gx_draft_put($d)){ $this->tp_json(array('ok' => false, 'msg' => 'Draf gagal disimpan.')); }
		$eff = gx_apply_draft($conf, $d);
		$this->tp_json(array('ok' => true, 'n' => $n, 'err' => $err, 'count' => gx_draft_count($d), 'draft' => array('teks' => (object) $d['teks'], 'data' => (object) $d['data'], 'susunan' => (object) $d['susunan']), 'eff' => (object) $eff['teks'], 'effS' => (object) $eff['susunan'], 'draftTema' => (object) $d['tema'], 'tema' => (object) $eff['tema'], 'temaCss' => gx_tema_css($eff['tema']), 'temaEff' => gx_tema_eff($eff['tema'])));
	}

	protected function tp_sk_ok($sk){
		return is_string($sk) && strlen($sk) < 260 && preg_match('#^(p\|[a-z0-9_:./\-]{1,200}|v\|[A-Za-z0-9_./\-]{1,200})$#', $sk);
	}

	/** Spek susunan dari editor: hanya bentuk yang dikenal. */
	protected function tp_spec_clean($sp){
		$k = function ($x){ $x = (string) $x; return (strlen($x) <= 500 && !preg_match('/[\x00-\x1F]/', $x)) ? $x : null; };
		$out = array();
		foreach(array('o', 'h') as $f){
			if(isset($sp[$f]) && is_array($sp[$f])){ $l = array(); foreach(array_slice($sp[$f], 0, 200) as $x){ $x = $k($x); if($x !== null){ $l[] = $x; } } if($l){ $out[$f] = array_values(array_unique($l)); } }
		}
		if(isset($sp['w']) && is_array($sp['w'])){
			$w = array(); foreach(array_slice($sp['w'], 0, 200, true) as $bk => $span){ $bk = $k($bk); $span = (int) $span; if($bk !== null && in_array($span, array(3, 4, 6, 8, 9, 12), true)){ $w[$bk] = $span; } }
			if($w){ $out['w'] = $w; }
		}
		if(isset($sp['f']) && is_array($sp['f'])){
			$f = array(); foreach(array_slice($sp['f'], 0, 200, true) as $bk => $pos){
				$bk = $k($bk);
				if($bk === null || !is_array($pos) || count($pos) !== 3){ continue; }
				$r = max(1, min(200, (int) $pos[0])); $c = max(1, min(12, (int) $pos[1])); $sp2 = max(1, min(12, (int) $pos[2]));
				if($c + $sp2 - 1 > 12){ $sp2 = 13 - $c; }
				$f[$bk] = array($r, $c, $sp2);
			}
			if($f){ $out['f'] = $f; }
		}
		foreach(array('n' => 80, 'v' => 200) as $f => $max){ if(isset($sp[$f])){ $out[$f] = $this->tp_clean($sp[$f], $max); } }
		if(!empty($sp['a']) && !isset($out['o']) && !isset($out['h']) && !isset($out['w']) && !isset($out['f'])){ $out['a'] = 1; }   // halaman ini = susunan awal (menimpa "tempat ini")
		return (isset($out['o']) || isset($out['h']) || isset($out['w']) || isset($out['f']) || isset($out['a'])) ? $out : null;
	}

	/** Isi 1 grup susunan di draf. $spec null = kembali ke susunan awal. */
	protected function tp_set_susunan(&$d, $conf, $sk, $ck, $spec){
		$has = isset($conf['susunan'][$sk][$ck]);
		if($spec === null){
			if($has){ $d['susunan'][$sk][$ck] = null; } else { unset($d['susunan'][$sk][$ck]); }
		}
		elseif($has && gx_json($conf['susunan'][$sk][$ck]) === gx_json($spec)){ unset($d['susunan'][$sk][$ck]); }
		else{ $d['susunan'][$sk][$ck] = $spec; }
		if(isset($d['susunan'][$sk]) && !$d['susunan'][$sk]){ unset($d['susunan'][$sk]); }
	}

	/** Isi 1 kunci teks di draf. $t null = kembali ke teks awal. */
	protected function tp_set_teks(&$d, $conf, $k, $t, $def, $w){
		$has = isset($conf['teks'][$k]);
		$curt = $has ? (string) $conf['teks'][$k]['t'] : null;
		if($t === null){
			if($has){ $d['teks'][$k] = array('t' => null, 'd' => $def !== '' ? $def : $conf['teks'][$k]['d'], 'w' => $w !== '' ? $w : (isset($conf['teks'][$k]['w']) ? $conf['teks'][$k]['w'] : '')); }
			else{ unset($d['teks'][$k]); }
			return;
		}
		if($has && $curt === $t){ unset($d['teks'][$k]); return; }
		$d['teks'][$k] = array('t' => $t, 'd' => $def, 'w' => $w);
	}

	/** Draf -> versi baru. POST: catatan (opsional), data_hasil (JSON hasil jalan pintas data dari editor). */
	protected function tp_simpan($fd){
		$ajax = !empty($fd['ajax']);
		$back = $this->tp_back(isset($fd['kembali']) ? $fd['kembali'] : '', 'tampilan');
		$d = gx_draft_get(null, true);
		$cur = gx_current(true);
		$data_res = json_decode(isset($fd['data_hasil']) ? (string) $fd['data_hasil'] : '[]', true);
		$data_lines = array(); $data_fail = 0;
		foreach((is_array($data_res) ? array_slice($data_res, 0, 100) : array()) as $r){
			if(!is_array($r)){ continue; }
			$ok = !empty($r['ok']);
			$line = array('what' => $this->tp_clean(isset($r['what']) ? $r['what'] : '', 60), 'old' => $this->tp_clean(isset($r['old']) ? $r['old'] : '', 150),
				'new' => $this->tp_clean(isset($r['new']) ? $r['new'] : '', 150), 'ok' => $ok, 'msg' => $this->tp_clean(isset($r['msg']) ? $r['msg'] : '', 300));
			if($ok){ $data_lines[] = $line; } else { $data_fail++; }
			if($d && isset($r['key']) && $ok){ unset($d['data'][(string) $r['key']]); }
		}
		$catatan = $this->tp_clean(isset($fd['catatan']) ? $fd['catatan'] : '', 200);
		$new = $d ? gx_apply_draft($cur['conf'], $d) : $cur['conf'];
		$diff = gx_diff($cur['conf'], $new);
		$no = 0;
		if($diff['lines']){
			$pdo = gx_db();
			try{
				$pdo->beginTransaction();
				$has = (int) $pdo->query("SELECT COUNT(*) FROM genc_tampilan")->fetchColumn();
				if(!$has){
					$st = $pdo->prepare("INSERT INTO genc_tampilan (versi, sekarang, data, ringkasan, meta, halaman, created_at, created_by) VALUES (1, 0, ?, ?, ?, '', ?, '')");
					$st->execute(array(gx_json(gx_conf_norm(array())), 'Tampilan awal', gx_json(array('lines' => array(), 'awal' => 1)), date('Y-m-d H:i:s')));
				}
				$no = (int) $pdo->query("SELECT COALESCE(MAX(versi), 1) FROM genc_tampilan")->fetchColumn() + 1;
				$pdo->exec("UPDATE genc_tampilan SET sekarang = 0");
				$st = $pdo->prepare("INSERT INTO genc_tampilan (versi, sekarang, data, ringkasan, meta, halaman, created_at, created_by) VALUES (?, 1, ?, ?, ?, ?, ?, ?)");
				$st->execute(array($no, gx_json(gx_conf_norm($new)), $this->tp_summary($diff, $data_lines),
					gx_json(array('lines' => $diff['lines'], 'data' => $data_lines, 'dari' => $cur['no'], 'catatan' => $catatan)),
					'|' . implode('|', $diff['pages']) . '|', date('Y-m-d H:i:s'), gx_user()));
				$pdo->commit();
			}
			catch(\Throwable $e){
				if($pdo && $pdo->inTransaction()){ $pdo->rollBack(); }
				$msg = 'Gagal menyimpan tampilan: ' . $e->getMessage();
				if($ajax){ $this->tp_json(array('ok' => false, 'msg' => $msg)); }
				$this->tp_toast(genc_e($msg), 'danger'); return $this->tp_go($back);
			}
			$txt = array();
			foreach(array_slice($diff['lines'], 0, 20) as $l){ $txt[] = isset($l['sec']) ? $l['w'] . ': ' . $l['what'] : $l['w'] . ': "' . $l['old'] . '" -> "' . $l['new'] . '"'; }
			genc_app_log('tampilan.simpan', 'genc_tampilan', $no, 'Simpan tampilan v' . $no . ' (dari v' . $cur['no'] . '): ' . implode('; ', $txt) . (count($diff['lines']) > 20 ? '; ...' : '') . ($catatan !== '' ? ' | Catatan: ' . $catatan : ''),
				array('versi' => $no, 'dari' => $cur['no'], 'jumlah' => count($diff['lines'])));
		}
		elseif($data_lines){
			$txt = array();
			foreach($data_lines as $l){ $txt[] = $l['what'] . ': "' . $l['old'] . '" -> "' . $l['new'] . '"'; }
			genc_app_log('tampilan.data', 'genc_tampilan', '', 'Mode edit tampilan: ubah data lewat jalan pintas - ' . implode('; ', $txt));
		}
		// draf: perubahan teks sudah masuk versi; perubahan data yang gagal tetap di draf
		$left = $d ? array('base' => $no ? $no : $cur['no'], 'teks' => array(), 'data' => $d['data'], 'susunan' => array(), 'tema' => array()) : null;
		if($left && gx_draft_count($left)){ gx_draft_put($left); } else { gx_draft_del(); }
		if($no){ $msg = 'Tampilan disimpan sebagai <b>v' . $no . '</b> dan berlaku untuk semua user.'; $type = 'success'; }
		elseif($data_lines){ $msg = 'Perubahan data disimpan lewat ' . (count($data_lines) > 1 ? 'menunya masing-masing' : 'menunya') . ' (tercatat di riwayatnya). Tidak ada perubahan tampilan lain.'; $type = 'success'; }
		else{ $msg = 'Tidak ada perubahan untuk disimpan.'; $type = 'info'; }
		if($data_fail){ $msg .= ' ' . $data_fail . ' perubahan data gagal &amp; tetap di draf.'; $type = $no || $data_lines ? 'info' : 'danger'; }
		if($ajax){ $this->tp_toast($msg, $type); $this->tp_json(array('ok' => true, 'versi' => $no, 'back' => $back)); }
		$this->tp_toast($msg, $type);
		return $this->tp_go($back);
	}

	protected function tp_summary($diff, $data_lines){
		$nt = 0; $ns = 0; $nm = 0;
		foreach($diff['lines'] as $l){ if(isset($l['sec'])){ if($l['sec'] === 'tema'){ $nm++; } else { $ns++; } } else { $nt++; } }
		$p = array();
		if($nt){ $p[] = $nt . ' teks'; }
		if($ns){ $p[] = 'susunan ' . $ns . ' bagian'; }
		if($nm){ $p[] = 'tema'; }
		if($data_lines){ $p[] = count($data_lines) . ' data'; }
		$s = ucfirst(implode(', ', $p)) . ' diubah';
		$pg = array_slice($diff['pages'], 0, 3);
		if($pg){ $s .= ' · ' . implode(', ', $pg) . (count($diff['pages']) > 3 ? ' +' . (count($diff['pages']) - 3) : ''); }
		return function_exists('mb_substr') ? mb_substr($s, 0, 250, 'UTF-8') : substr($s, 0, 250);
	}

	protected function tp_batal($fd){
		$d = gx_draft_get(null, true);
		$n = gx_draft_count($d);
		gx_draft_del();
		if($n){ genc_app_log('tampilan.batal', 'genc_tampilan', '', 'Batal draf tampilan (' . $n . ' perubahan dibuang)'); }
		$this->tp_toast($n ? 'Draf dibuang (' . $n . ' perubahan). Tampilan kembali seperti versi sekarang.' : 'Tidak ada draf.', 'info');
		return $this->tp_go($this->tp_back(isset($fd['kembali']) ? $fd['kembali'] : '', 'tampilan'));
	}

	/** Kembalikan ke versi lain = pindah penunjuk. */
	protected function tp_versi($no, $fd){
		$back = $this->tp_back(isset($fd['kembali']) ? $fd['kembali'] : '', 'tampilan');
		$pdo = gx_db();
		$cur = gx_current(true);
		try{
			$has = (int) $pdo->query("SELECT COUNT(*) FROM genc_tampilan")->fetchColumn();
			$st = $pdo->prepare("SELECT versi FROM genc_tampilan WHERE versi = ?");
			$st->execute(array($no));
			if(!$st->fetch() || !$has){ $this->tp_toast('Versi tidak ditemukan.', 'danger'); return $this->tp_go($back); }
			if($no === (int) $cur['no']){ $this->tp_toast('v' . $no . ' sudah versi sekarang.', 'info'); return $this->tp_go($back); }
			$pdo->beginTransaction();
			$pdo->exec("UPDATE genc_tampilan SET sekarang = 0");
			$pdo->prepare("UPDATE genc_tampilan SET sekarang = 1 WHERE versi = ?")->execute(array($no));
			$pdo->commit();
		}
		catch(\Throwable $e){
			if($pdo && $pdo->inTransaction()){ $pdo->rollBack(); }
			$this->tp_toast('Gagal: ' . genc_e($e->getMessage()), 'danger'); return $this->tp_go($back);
		}
		genc_app_log('tampilan.versi', 'genc_tampilan', $no, 'Kembalikan tampilan ke v' . $no . ' (sebelumnya v' . $cur['no'] . ')', array('versi' => $no, 'dari' => $cur['no']));
		$d = gx_draft_get(null, true);
		$this->tp_toast('Tampilan sekarang <b>v' . $no . '</b> untuk semua user. Tidak ada versi baru &mdash; versi lain tetap bisa dipilih lagi.' . ($d && gx_draft_count($d) ? ' Draf lo (' . gx_draft_count($d) . ' perubahan) tetap ada.' : ''), 'success');
		return $this->tp_go($back);
	}
}
