<?php
/**
 * APP LOGS - log aktivitas (Generasi C)                  [GENC-24SEP26-SHELL]
 *
 * Dulu: tabel app_logs tidak diisi lagi sejak 24 Okt 2024, tampil mentah (query SQL, hash password),
 * dan bisa ditambah/diubah/dihapus seperti data biasa.
 * Sekarang: diisi lagi oleh genc_app_log() (login, checklist, user, role, hak akses); halaman ini
 * HANYA MEMBACA (log tidak bisa diubah/dihapus dari app); hash password selalu disamarkan.
 * Route: app_logs (daftar). add/edit/delete/view lama -> kembali ke daftar.
 * Cadangan: _backup_genc_24sept26/App_logsController_sebelum-shell.php
 * @category  Controller
 */
require_once __DIR__ . '/../views/partials/_shared/genc_helpers.php';   // [GENC-08OKT26-P3] genc_date/genc_person_name (export)
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';

class App_logsController extends SecureController{
	const PER_PAGE = 50;

	function __construct(){
		parent::__construct();
		$this->tablename = "app_logs";
	}

	/**
	 * Jenis log (chip). [GENC-08OKT26-P3] "Arsip phpRAD" (entri lama tanpa keterangan, sebelum Okt 2024) DISEMBUNYIKAN:
	 * tidak tampil di daftar, chip, maupun export - datanya tetap di tabel app_logs (tidak dihapus).
	 */
	static function logs_kinds(){
		return array(
			''          => array('Semua', null),
			'login'     => array('Login', "(Action LIKE 'userlog%')"),
			'checklist' => array('Checklist', "(Action LIKE 'checklist.%')"),
			'admin'     => array('User & hak akses', "(Action LIKE 'users.%' OR Action LIKE 'roles.%' OR Action LIKE 'permissions.%' OR Action LIKE 'master_select.%')"),
			'mesin'     => array('Mesin & unit', "(Action LIKE 'mesin.%')"),   // [GENC-06OKT26-MESIN]
			'tampilan'  => array('Tampilan', "(Action LIKE 'tampilan.%')"),   // [GENC-13OKT26-TAMPILAN]
			'lain'      => array('Lainnya', "(Action NOT LIKE 'userlog%' AND Action NOT LIKE 'checklist.%' AND Action NOT LIKE 'users.%' AND Action NOT LIKE 'roles.%' AND Action NOT LIKE 'permissions.%' AND Action NOT LIKE 'master_select.%' AND Action NOT LIKE 'mesin.%' AND Action NOT LIKE 'tampilan.%')"),   // [GENC-08OKT26-P3] mis. export log
		);
	}

	/** Syarat dasar semua tampilan: hanya catatan Generasi C (punya keterangan). */
	const LOGS_BASE = "(RequestMsg IS NOT NULL AND RequestMsg <> '')";

	/** Nama jenis aksi (dipakai daftar & export). [nama, warna tag] */
	static function logs_labels(){
		return array(
			'userlogin' => array('Login', 'ok'), 'userlogin.failed' => array('Login gagal', 'nok'), 'userlogin.blocked' => array('Login diblokir', 'nok'), 'userlogout' => array('Keluar', ''),
			'checklist.add' => array('Isi checklist', 'primary'), 'checklist.edit' => array('Ubah checklist', 'pr'), 'checklist.delete' => array('Hapus checklist', 'nok'), 'checklist.approve' => array('Approve', 'pending'),
			'users.add' => array('Tambah user', 'primary'), 'users.edit' => array('Ubah user', 'pr'), 'users.delete' => array('Hapus user', 'nok'), 'users.register' => array('Daftar akun', 'primary'),
			'roles.add' => array('Tambah role', 'primary'), 'roles.edit' => array('Ubah role', 'pr'), 'roles.delete' => array('Hapus role', 'nok'), 'permissions.save' => array('Ubah hak akses', 'pending'),
			'master_select.add' => array('Tambah pilihan', 'primary'), 'master_select.edit' => array('Ubah pilihan', 'pr'), 'master_select.delete' => array('Hapus pilihan', 'nok'),
			'master_select.pilihan' => array('Label hasil cek', 'pr'),
			// [GENC-08OKT26-P3] Mesin & Unit
			'mesin.add' => array('Tambah mesin', 'primary'), 'mesin.edit' => array('Ubah mesin', 'pr'), 'mesin.isi' => array('Ubah isi checklist', 'pr'),
			'mesin.kunci' => array('Kunci / buka', 'pending'), 'mesin.arsip' => array('Hapus mesin (arsip)', 'nok'), 'mesin.pulihkan' => array('Pulihkan arsip', 'ok'),
			'mesin.versi' => array('Kembalikan versi', 'pending'), 'mesin.delete' => array('Hapus mesin', 'nok'),
			'mesin.unit.add' => array('Tambah unit', 'primary'), 'mesin.unit.edit' => array('Ubah unit', 'pr'), 'mesin.unit.arsip' => array('Hapus unit (arsip)', 'nok'), 'mesin.unit.delete' => array('Hapus unit', 'nok'),
			// [GENC-13OKT26] hapus permanen dari Arsip + cadangan
			'mesin.hapus' => array('Hapus permanen mesin', 'nok'), 'mesin.unit.hapus' => array('Hapus permanen unit', 'nok'),
			'mesin.hapus.gagal' => array('Hapus permanen batal', 'pr'), 'mesin.cadangan' => array('Unduh cadangan', ''),
			// [GENC-13OKT26-TAMPILAN] Mode edit tampilan
			'tampilan.simpan' => array('Simpan tampilan', 'primary'), 'tampilan.versi' => array('Kembalikan tampilan', 'pending'),
			'tampilan.batal' => array('Buang draf tampilan', ''), 'tampilan.mode' => array('Mode edit tampilan', ''), 'tampilan.data' => array('Ubah data (jalan pintas)', 'pr'),
		);
	}

	/** Jenis (chip) sebuah aksi. */
	static function logs_kind_of($action){
		$a = strtolower((string) $action);
		if(strpos($a, 'userlog') === 0){ return 'login'; }
		if(strpos($a, 'checklist.') === 0){ return 'checklist'; }
		if(preg_match('/^(users|roles|permissions|master_select)\./', $a)){ return 'admin'; }
		if(strpos($a, 'mesin.') === 0){ return 'mesin'; }
		if(strpos($a, 'tampilan.') === 0){ return 'tampilan'; }   // [GENC-13OKT26-TAMPILAN]
		return 'lain';
	}

	static function logs_mask($s){
		$s = (string) $s;
		$s = preg_replace('/("pass[a-z_]*"\s*:\s*)"(?:[^"\\\\]|\\\\.)*"/i', '$1"••••"', $s);
		$s = preg_replace('/\$2[aby]\$\d\d\$[.\/A-Za-z0-9]{20,}/', '••••', $s);
		return $s;
	}

	/** [GENC-08OKT26-P3] Filter dari URL: jenis, q, dari, sampai (Y-m-d), user (UserID). */
	protected function logs_filter(){
		$r = $this->request;
		$kinds = self::logs_kinds();
		$f = array(
			'jenis'  => isset($r->jenis) && isset($kinds[(string) $r->jenis]) ? (string) $r->jenis : '',
			'q'      => trim((string) (isset($r->q) ? $r->q : '')),
			'dari'   => '', 'sampai' => '',
			'user'   => isset($r->user) ? preg_replace('/[^0-9]/', '', (string) $r->user) : '',
		);
		foreach(array('dari', 'sampai') as $k){
			$v = isset($r->$k) ? trim((string) $r->$k) : '';
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false){ $f[$k] = $v; }
		}
		if($f['dari'] !== '' && $f['sampai'] !== '' && $f['dari'] > $f['sampai']){ $t = $f['dari']; $f['dari'] = $f['sampai']; $f['sampai'] = $t; }
		return $f;
	}

	/** WHERE + parameter untuk filter ($with_kind = false: untuk hitungan chip). */
	protected function logs_where($f, $with_kind = true){
		$kinds = self::logs_kinds();
		$w = array(self::LOGS_BASE); $p = array();
		if($with_kind && $kinds[$f['jenis']][1]){ $w[] = $kinds[$f['jenis']][1]; }
		if($f['q'] !== ''){ $w[] = "(RequestMsg LIKE ? OR Action LIKE ? OR TableName LIKE ? OR RequestUrl LIKE ? OR RecordID LIKE ?)"; array_push($p, "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%"); }
		if($f['dari'] !== ''){ $w[] = "Timestamp >= ?"; $p[] = $f['dari'] . ' 00:00:00'; }
		if($f['sampai'] !== ''){ $w[] = "Timestamp <= ?"; $p[] = $f['sampai'] . ' 23:59:59'; }
		if($f['user'] !== ''){ $w[] = "UserID = ?"; $p[] = $f['user']; }
		return array(" WHERE " . implode(" AND ", $w), $p);
	}

	protected function logs_users(){
		$db = $this->GetModel();
		$users = array();
		foreach((array) $db->rawQuery("SELECT id_user, username, nama FROM users") as $u){ $users[(string) $u['id_user']] = $u; }
		return $users;
	}

	function index($fieldname = null, $fieldvalue = null){
		$db = $this->GetModel();
		$kinds = self::logs_kinds();
		$f = $this->logs_filter();
		$fmt = isset($this->request->format) ? strtolower((string) $this->request->format) : '';
		if(in_array($fmt, array('pdf', 'excel', 'csv'), true)){ return $this->logs_export($f, $fmt); }   // [GENC-08OKT26-P3]
		$page = max(1, (int) (isset($this->request->hal) ? $this->request->hal : 1));
		list($where, $p) = $this->logs_where($f);
		$total = $db->rawQueryValue("SELECT COUNT(*) FROM app_logs" . $where, $p ? $p : null);
		if(is_array($total)){ $total = reset($total); }
		$total = (int) $total;
		$pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = min($page, $pages);
		$rows = $db->rawQuery("SELECT * FROM app_logs" . $where . " ORDER BY log_id DESC LIMIT " . (($page - 1) * self::PER_PAGE) . ", " . self::PER_PAGE, $p ? $p : null);
		$counts = array();
		foreach($kinds as $k => $v){
			list($w2, $p2) = $this->logs_where(array_merge($f, array('jenis' => $k)));
			$c = $db->rawQueryValue("SELECT COUNT(*) FROM app_logs" . $w2, $p2 ? $p2 : null);
			$counts[$k] = (int) (is_array($c) ? reset($c) : $c);
		}
		$users = $this->logs_users();
		$active_users = array();
		foreach((array) $db->rawQuery("SELECT DISTINCT UserID FROM app_logs WHERE " . self::LOGS_BASE . " AND UserID <> ''") as $r){ $active_users[] = (string) $r['UserID']; }
		$data = array('rows' => is_array($rows) ? $rows : array(), 'kinds' => $kinds, 'kind' => $f['jenis'], 'q' => $f['q'], 'f' => $f,
			'page' => $page, 'pages' => $pages, 'total' => $total, 'counts' => $counts, 'users' => $users, 'active_users' => $active_users, 'labels' => self::logs_labels());
		$this->view->page_title = "App Logs";
		return $this->render_view("app_logs/list.php", $data);
	}

	// =====================================================================
	//  EXPORT PDF / EXCEL / CSV                      [GENC-08OKT26-P3]
	//  Mengikuti filter yang sedang dipakai (jenis, cari, tanggal, user).
	//  Ringkasan (per jenis, per user, per hari) + daftar kronologis per jenis.
	// =====================================================================

	const EXPORT_MAX = 10000;

	protected function logs_export($f, $fmt){
		$db = $this->GetModel();
		list($where, $p) = $this->logs_where($f);
		$rows = (array) $db->rawQuery("SELECT * FROM app_logs" . $where . " ORDER BY log_id ASC LIMIT " . (self::EXPORT_MAX + 1), $p ? $p : null);
		$cut = count($rows) > self::EXPORT_MAX;
		if($cut){ $rows = array_slice($rows, 0, self::EXPORT_MAX); }
		$users = $this->logs_users();
		$labels = self::logs_labels();
		$kinds = self::logs_kinds();
		$kind_names = array(); foreach($kinds as $k => $v){ if($k !== ''){ $kind_names[$k] = $v[0]; } }
		$out = array();
		foreach($rows as $r){
			$uid = (string) $r['UserID'];
			$u = isset($users[$uid]) ? $users[$uid] : null;
			$uname = $u ? (string) $u['username'] : ($uid !== '' ? 'user #' . $uid : '-');
			$act = (string) $r['Action'];
			$out[] = array(
				'waktu'  => (string) $r['Timestamp'],
				'user'   => $uname,
				'nama'   => $u ? genc_person_name($u['username']) : '',
				'jenis'  => self::logs_kind_of($act),
				'aksi'   => isset($labels[$act]) ? $labels[$act][0] : $act,
				'ket'    => trim((string) $r['RequestMsg']),
				'tabel'  => (string) $r['TableName'],
				'record' => (string) $r['RecordID'],
				'ip'     => (string) $r['ServerIP'],
			);
		}
		// ringkasan
		$sum = array('jenis' => array(), 'user' => array(), 'hari' => array());
		foreach($out as $r){
			$sum['jenis'][$r['jenis']] = (isset($sum['jenis'][$r['jenis']]) ? $sum['jenis'][$r['jenis']] : 0) + 1;
			$uk = $r['nama'] !== '' ? $r['nama'] . ' (' . $r['user'] . ')' : $r['user'];
			$sum['user'][$uk] = (isset($sum['user'][$uk]) ? $sum['user'][$uk] : 0) + 1;
			$dk = substr($r['waktu'], 0, 10);
			$sum['hari'][$dk] = (isset($sum['hari'][$dk]) ? $sum['hari'][$dk] : 0) + 1;
		}
		arsort($sum['user']); ksort($sum['hari']);
		// keterangan filter
		$fl = array();
		$fl[] = 'Jenis: ' . $kinds[$f['jenis']][0];
		$fl[] = 'Periode: ' . ($f['dari'] !== '' ? genc_date($f['dari']) : 'awal') . ' s/d ' . ($f['sampai'] !== '' ? genc_date($f['sampai']) : 'sekarang');
		if($f['user'] !== ''){ $fl[] = 'User: ' . (isset($users[$f['user']]) ? $users[$f['user']]['username'] : '#' . $f['user']); }
		if($f['q'] !== ''){ $fl[] = 'Cari: "' . $f['q'] . '"'; }
		$name = 'Log-Aktivitas-' . date('Y-m-d') . ($f['jenis'] !== '' ? '-' . $f['jenis'] : '');
		$ctx = array('rows' => $out, 'sum' => $sum, 'filter' => $fl, 'cut' => $cut, 'kind_names' => $kind_names, 'jenis' => $f['jenis']);
		genc_app_log('app_logs.export', 'app_logs', 0, 'Export log aktivitas (' . strtoupper($fmt) . ', ' . count($out) . ' catatan) - ' . implode('; ', $fl), array('format' => $fmt));
		if($fmt === 'csv'){ return $this->logs_csv($ctx, $name); }
		if($fmt === 'excel'){ return $this->logs_excel($ctx, $name); }
		return $this->logs_pdf($ctx, $name);
	}

	protected function logs_csv($ctx, $name){
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $name . '.csv"');
		$fh = fopen('php://output', 'w');
		fwrite($fh, "\xEF\xBB\xBF");   // BOM: Excel membaca UTF-8
		fputcsv($fh, array('Waktu', 'Username', 'Nama', 'Jenis', 'Aksi', 'Keterangan', 'Tabel', 'Record', 'IP'));
		foreach($ctx['rows'] as $r){
			fputcsv($fh, array($r['waktu'], $r['user'], $r['nama'], $ctx['kind_names'][$r['jenis']], $r['aksi'], self::logs_mask($r['ket']), $r['tabel'], $r['record'], $r['ip']));
		}
		fclose($fh);
		exit;
	}

	/** Excel: .xlsx (XLSXWriter phpRAD, butuh ekstensi zip) - cadangan tanpa zip: SpreadsheetML (.xls, dibuka Excel). */
	protected function logs_excel($ctx, $name){
		$sheets = array();
		$ring = array(array('Log aktivitas - Checklist AM Warehouse CKR'), array('Dicetak ' . date('Y-m-d H:i') . ' oleh ' . USER_NAME));
		foreach($ctx['filter'] as $x){ $ring[] = array($x); }
		if($ctx['cut']){ $ring[] = array('Dipotong: hanya ' . self::EXPORT_MAX . ' catatan pertama - persempit filter.'); }
		$ring[] = array(''); $ring[] = array('Jenis', 'Jumlah');
		foreach($ctx['kind_names'] as $k => $n){ if(isset($ctx['sum']['jenis'][$k])){ $ring[] = array($n, $ctx['sum']['jenis'][$k]); } }
		$ring[] = array('Total', count($ctx['rows']));
		$ring[] = array(''); $ring[] = array('User', 'Jumlah');
		foreach($ctx['sum']['user'] as $u => $n){ $ring[] = array($u, $n); }
		$ring[] = array(''); $ring[] = array('Tanggal', 'Jumlah');
		foreach($ctx['sum']['hari'] as $dk => $n){ $ring[] = array($dk, $n); }
		$sheets['Ringkasan'] = $ring;
		foreach($ctx['kind_names'] as $k => $n){
			$list = array(array('Waktu', 'Username', 'Nama', 'Aksi', 'Keterangan', 'Tabel', 'Record', 'IP'));
			foreach($ctx['rows'] as $r){ if($r['jenis'] === $k){ $list[] = array($r['waktu'], $r['user'], $r['nama'], $r['aksi'], self::logs_mask($r['ket']), $r['tabel'], $r['record'], $r['ip']); } }
			if(count($list) > 1){ $sheets[substr(preg_replace('/[^A-Za-z0-9 &]/', ' ', $n), 0, 30)] = $list; }
		}
		if(class_exists('ZipArchive') && is_file(ROOT . 'libs/XLSXWriter.php')){
			require_once ROOT . 'libs/XLSXWriter.php';
			$w = new XLSXWriter();
			$w->setAuthor(USER_NAME);
			foreach($sheets as $sn => $rows){
				$first = true;
				foreach($rows as $row){
					$row = array_map(function($v){ return is_int($v) ? $v : (string) $v; }, $row);
					if($first && $sn !== 'Ringkasan'){ $w->writeSheetHeader($sn, array_fill_keys($row, 'string'), array('widths' => array(19, 16, 22, 20, 80, 16, 10, 15), 'freeze_rows' => 1)); $first = false; continue; }
					$first = false;
					$w->writeSheetRow($sn, $row);
				}
			}
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="' . $name . '.xlsx"');
			echo $w->writeToString();
			exit;
		}
		header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $name . '.xls"');
		echo self::logs_spreadsheetml($sheets);
		exit;
	}

	/** Excel 2003 XML (beberapa sheet, tanpa ekstensi zip). */
	static function logs_spreadsheetml($sheets){
		$x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<?mso-application progid="Excel.Sheet"?>' . "\n"
			. '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
			. '<Styles><Style ss:ID="h"><Font ss:Bold="1"/></Style></Styles>';
		foreach($sheets as $sn => $rows){
			$x .= '<Worksheet ss:Name="' . htmlspecialchars($sn, ENT_QUOTES, 'UTF-8') . '"><Table>';
			foreach($rows as $i => $row){
				$x .= '<Row>';
				foreach($row as $v){
					$t = is_int($v) ? 'Number' : 'String';
					$x .= '<Cell' . ($i === 0 ? ' ss:StyleID="h"' : '') . '><Data ss:Type="' . $t . '">' . htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</Data></Cell>';
				}
				$x .= '</Row>';
			}
			$x .= '</Table></Worksheet>';
		}
		return $x . '</Workbook>';
	}

	protected function logs_pdf($ctx, $name){
		require_once ROOT . 'app/views/partials/_shared/checklist_report_engine.php';
		$e = function($s){ return function_exists('checklist_e') ? checklist_e($s) : htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
		// dompdf 0.8.3: tanpa elemen fixed / counter (PHP 8 -> TypeError), judul bagian = div, page-break di div pembungkus
		$h = '<html><head><meta charset="utf-8"><style>
			@page { margin: 12mm 10mm 12mm 10mm; }
			body { font-family: Helvetica, Arial, sans-serif; font-size: 8.5px; color: #1d2433; }
			table.hd { margin-bottom: 8px; } table.hd td { border: none; padding: 0 0 2px; }
			td.t1 { font-size: 15px; font-weight: bold; } td.sub { color: #5d6678; font-size: 8.5px; padding-bottom: 4px; }
			td.t2 { font-size: 11px; font-weight: bold; padding-bottom: 3px; border-bottom: 1.5px solid #1d2433; }
			table { width: 100%; border-collapse: collapse; } th, td { border: 0.6px solid #c9ced8; padding: 3px 4px; vertical-align: top; text-align: left; }
			th { background: #eef1f6; font-weight: bold; } tr { page-break-inside: avoid; }
			th.cap { background: #1d2433; color: #fff; font-size: 9px; }
			.num { text-align: right; width: 46px; } .w-time { width: 82px; } .w-user { width: 110px; } .w-act { width: 95px; }
			table.grid td.g { border: none; padding: 0 8px 0 0; vertical-align: top; } .muted { color: #6b7385; } td.warn { color: #b54708; font-weight: bold; }
		</style></head><body>';
		// teks lepas (div/p) di dompdf 0.8.3 + PHP 8 bisa bertumpuk dengan tabel -> judul pun dibuat sebagai tabel (aturan report K-dompdf)
		$h .= '<table class="hd"><tr><td class="t1">Log Aktivitas - Checklist AM Warehouse CKR</td></tr><tr><td class="sub">' . $e(implode('  |  ', $ctx['filter'])) . '  |  ' . count($ctx['rows']) . ' catatan  |  dicetak ' . $e(date('d/m/Y H:i')) . ' oleh ' . $e(USER_NAME) . '</td></tr>'
			. ($ctx['cut'] ? '<tr><td class="warn">Dipotong: hanya ' . self::EXPORT_MAX . ' catatan pertama yang dicetak. Persempit filter (tanggal / jenis / user).</td></tr>' : '') . '</table>';
		$tbl = function($title, $pairs, $head) use ($e){
			$t = '<table><tr><th class="cap" colspan="2">' . $e($title) . '</th></tr><tr><th>' . $e($head) . '</th><th class="num">Jumlah</th></tr>';
			if(!$pairs){ $t .= '<tr><td colspan="2" class="muted">-</td></tr>'; }
			foreach($pairs as $k => $n){ $t .= '<tr><td>' . $e($k) . '</td><td class="num">' . $e($n) . '</td></tr>'; }
			return $t . '</table>';
		};
		$pj = array(); foreach($ctx['kind_names'] as $k => $n){ if(isset($ctx['sum']['jenis'][$k])){ $pj[$n] = $ctx['sum']['jenis'][$k]; } } $pj['Total'] = count($ctx['rows']);
		$ph = array(); foreach($ctx['sum']['hari'] as $dk => $n){ $ph[genc_date($dk)] = $n; }
		if(count($ph) > 31){ $ph = array('(31 hari terakhir)' => '') + array_slice($ph, -31, null, true); }
		$h .= '<table class="grid"><tr><td class="g" style="width:30%">' . $tbl('Ringkasan per jenis', $pj, 'Jenis') . '</td><td class="g" style="width:38%">' . $tbl('Per user', array_slice($ctx['sum']['user'], 0, 25, true), 'User') . '</td><td class="g" style="width:32%">' . $tbl('Per hari', $ph, 'Tanggal') . '</td></tr></table>';
		foreach($ctx['kind_names'] as $k => $n){
			$list = array(); foreach($ctx['rows'] as $r){ if($r['jenis'] === $k){ $list[] = $r; } }
			if(!$list){ continue; }
			$h .= '<div style="page-break-before: always;"><table class="hd"><tr><td class="t2">' . $e($n) . ' (' . count($list) . ' catatan)</td></tr></table>';
			$h .= '<table><thead><tr><th class="w-time">Waktu</th><th class="w-user">Oleh</th><th class="w-act">Aksi</th><th>Keterangan</th></tr></thead><tbody>';
			foreach($list as $r){
				$h .= '<tr><td>' . $e(genc_date($r['waktu'])) . ' ' . $e(genc_time($r['waktu'])) . '</td><td>' . $e($r['nama'] !== '' ? $r['nama'] : $r['user']) . ($r['nama'] !== '' ? '<br><span class="muted">' . $e($r['user']) . '</span>' : '') . '</td><td>' . $e($r['aksi']) . '</td><td>' . $e(self::logs_mask($r['ket'])) . '</td></tr>';
			}
			$h .= '</tbody></table></div>';
		}
		$h .= '</body></html>';
		$bytes = checklist_pdf_bytes($h);
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="' . $name . '.pdf"');
		echo $bytes;
		exit;
	}

	function view($rec_id = null, $value = null){ return $this->redirect("app_logs?q=" . urlencode((string) $rec_id)); }
	function add($formdata = null){ return $this->redirect("app_logs"); }
	function edit($rec_id = null, $formdata = null){ return $this->redirect("app_logs"); }
	function editfield($rec_id = null, $formdata = null){ render_error("Log tidak bisa diubah."); return null; }
	function delete($rec_id = null){ return $this->redirect("app_logs"); }
}
