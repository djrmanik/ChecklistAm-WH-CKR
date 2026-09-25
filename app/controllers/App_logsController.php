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
class App_logsController extends SecureController{
	const PER_PAGE = 50;

	function __construct(){
		parent::__construct();
		$this->tablename = "app_logs";
	}

	static function logs_kinds(){
		return array(
			''          => array('Semua', null),
			'login'     => array('Login', "(RequestMsg <> '' AND Action LIKE 'userlog%')"),
			'checklist' => array('Checklist', "(RequestMsg <> '' AND Action LIKE 'checklist.%')"),
			'admin'     => array('User & hak akses', "(RequestMsg <> '' AND (Action LIKE 'users.%' OR Action LIKE 'roles.%' OR Action LIKE 'permissions.%' OR Action LIKE 'master_select.%'))"),
			'lama'      => array('Arsip phpRAD', "(RequestMsg IS NULL OR RequestMsg = '')"),   // entri lama phpRAD tidak punya keterangan
		);
	}

	static function logs_mask($s){
		$s = (string) $s;
		$s = preg_replace('/("pass[a-z_]*"\s*:\s*)"(?:[^"\\\\]|\\\\.)*"/i', '$1"••••"', $s);
		$s = preg_replace('/\$2[aby]\$\d\d\$[.\/A-Za-z0-9]{20,}/', '••••', $s);
		return $s;
	}

	function index($fieldname = null, $fieldvalue = null){
		$db = $this->GetModel();
		$kinds = self::logs_kinds();
		$kind = isset($this->request->jenis) && isset($kinds[$this->request->jenis]) ? (string) $this->request->jenis : '';
		$q = trim((string) (isset($this->request->q) ? $this->request->q : ''));
		$page = max(1, (int) (isset($this->request->hal) ? $this->request->hal : 1));
		$w = array(); $p = array();
		if($kinds[$kind][1]){ $w[] = $kinds[$kind][1]; }
		if($q !== ''){ $w[] = "(RequestMsg LIKE ? OR Action LIKE ? OR TableName LIKE ? OR RequestUrl LIKE ? OR RecordID LIKE ?)"; array_push($p, "%$q%", "%$q%", "%$q%", "%$q%", "%$q%"); }
		$where = $w ? " WHERE " . implode(" AND ", $w) : "";
		$total = $db->rawQueryValue("SELECT COUNT(*) FROM app_logs" . $where, $p ? $p : null);
		if(is_array($total)){ $total = reset($total); }
		$total = (int) $total;
		$pages = max(1, (int) ceil($total / self::PER_PAGE));
		$page = min($page, $pages);
		$rows = $db->rawQuery("SELECT * FROM app_logs" . $where . " ORDER BY log_id DESC LIMIT " . (($page - 1) * self::PER_PAGE) . ", " . self::PER_PAGE, $p ? $p : null);
		$counts = array();
		foreach($kinds as $k => $v){
			$c = $db->rawQueryValue("SELECT COUNT(*) FROM app_logs" . ($v[1] ? " WHERE " . $v[1] : ""));
			$counts[$k] = (int) (is_array($c) ? reset($c) : $c);
		}
		$users = array();
		foreach((array) $db->rawQuery("SELECT id_user, username, nama FROM users") as $u){ $users[(string) $u['id_user']] = $u; }
		$last_old = $db->rawQueryValue("SELECT MAX(Timestamp) FROM app_logs WHERE " . $kinds['lama'][1]);
		if(is_array($last_old)){ $last_old = reset($last_old); }
		$data = array('rows' => is_array($rows) ? $rows : array(), 'kinds' => $kinds, 'kind' => $kind, 'q' => $q,
			'page' => $page, 'pages' => $pages, 'total' => $total, 'counts' => $counts, 'users' => $users, 'last_old' => $last_old);
		$this->view->page_title = "App Logs";
		return $this->render_view("app_logs/list.php", $data);
	}

	function view($rec_id = null, $value = null){ return $this->redirect("app_logs?q=" . urlencode((string) $rec_id)); }
	function add($formdata = null){ return $this->redirect("app_logs"); }
	function edit($rec_id = null, $formdata = null){ return $this->redirect("app_logs"); }
	function editfield($rec_id = null, $formdata = null){ render_error("Log tidak bisa diubah."); return null; }
	function delete($rec_id = null){ return $this->redirect("app_logs"); }
}
