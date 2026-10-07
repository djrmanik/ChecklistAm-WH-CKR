<?php
/**
 * USERS - Generasi C                                     [GENC-24SEP26-SHELL]
 *
 * Yang dibereskan dibanding versi phpRAD (cadangan: _backup_genc_24sept26/UsersController_sebelum-shell.php):
 *  - Ubah user TANPA wajib ketik password baru (kosong = password tetap). Dulu form ubah
 *    menampilkan hash password & mewajibkan password baru setiap kali.
 *  - Status "Nonaktif" benar-benar memblokir login (IndexController). Nilai lama
 *    (Active / SPV / Manager) tetap sah & tidak diubah.
 *  - Admin tidak bisa menonaktifkan / menghapus / mengganti role akunnya sendiri (anti terkunci).
 *  - Pencarian tidak lagi mencari di kolom hash password.
 *  - Semua perubahan dicatat di App Logs.
 * Route & hak akses sama dengan dulu: users/list, users/add, users/edit, users/delete (users/view -> ubah/daftar).
 * @category  Controller
 */
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';
require_once __DIR__ . '/../views/partials/_shared/checklist_helpers.php';   // [GENC-06OKT26-INISIAL] daftar inisial resmi

class UsersController extends SecureController{
	/** Nilai status yang memblokir login (huruf kecil). Sama dengan daftar di IndexController. */
	const BLOCKED = 'nonaktif,non aktif,inactive,disabled,blocked,diblokir';

	function __construct(){
		parent::__construct();
		$this->tablename = "users";
	}

	protected function users_roles(){
		$db = $this->GetModel();
		$rows = $db->rawQuery("SELECT r.role_id, r.role_name, COUNT(u.id_user) AS n FROM roles r LEFT JOIN users u ON u.user_role_id = r.role_id GROUP BY r.role_id, r.role_name ORDER BY r.role_name");
		$out = array();
		foreach((array) $rows as $r){ $out[(string) $r['role_id']] = $r; }
		return $out;
	}

	protected function users_statuses(){
		$db = $this->GetModel();
		$vals = array('Active' => 1);
		foreach((array) $db->rawQuery("SELECT DISTINCT account_status FROM users") as $r){
			$v = trim((string) $r['account_status']);
			if($v !== ''){ $vals[$v] = 1; }
		}
		$vals['Nonaktif'] = 1;
		return array_keys($vals);
	}

	static function users_is_blocked($status){
		return in_array(strtolower(trim((string) $status)), explode(',', self::BLOCKED), true);
	}

	/** Daftar user + filter role + cari. */
	function index($fieldname = null, $fieldvalue = null){
		$db = $this->GetModel();
		$q = trim((string) (isset($this->request->q) ? $this->request->q : (isset($this->request->search) ? $this->request->search : '')));
		$role = isset($this->request->role) ? (string) $this->request->role : '';
		$sql = "SELECT u.id_user, u.nama, u.email, u.username, u.account_status, u.user_role_id" . ($this->users_has_inisial() ? ", u.inisial" : "") . ", r.role_name FROM users u LEFT JOIN roles r ON r.role_id = u.user_role_id";   // [GENC-06OKT26-INISIAL]
		$w = array(); $p = array();
		if($q !== ''){
			$w[] = "(u.nama LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.account_status LIKE ?" . ($this->users_has_inisial() ? " OR u.inisial LIKE ?" : "") . ")";
			array_push($p, "%$q%", "%$q%", "%$q%", "%$q%");
			if($this->users_has_inisial()){ $p[] = "%$q%"; }
		}
		if($role !== ''){ $w[] = "u.user_role_id = ?"; $p[] = $role; }
		if($w){ $sql .= " WHERE " . implode(" AND ", $w); }
		$sql .= " ORDER BY u.nama ASC LIMIT 1000";
		$rows = $db->rawQuery($sql, $p ? $p : null);
		$total = $db->rawQueryValue("SELECT COUNT(*) FROM users");
		if(is_array($total)){ $total = reset($total); }
		$data = array(
			'rows'  => is_array($rows) ? $rows : array(),
			'roles' => $this->users_roles(),
			'q'     => $q,
			'role'  => $role,
			'total' => (int) $total,
			'has_inisial' => $this->users_has_inisial(),   // [GENC-06OKT26-INISIAL]
			'can'   => array('add' => ACL::is_allowed('users/add'), 'edit' => ACL::is_allowed('users/edit'), 'delete' => ACL::is_allowed('users/delete'),
			                 'perm' => ACL::is_allowed('role_permissions/list')),
		);
		$this->view->page_title = "Users";
		return $this->render_view("users/list.php", $data);
	}

	/** Detail lama -> form ubah (kalau boleh) atau daftar. */
	function view($rec_id = null, $value = null){
		if(ACL::is_allowed('users/edit')){ return $this->redirect("users/edit/" . (int) $rec_id); }
		return $this->redirect("users");
	}

	function add($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$old = array('nama' => '', 'username' => '', 'email' => '', 'user_role_id' => '', 'account_status' => 'Active', 'inisial' => '');
		$errors = array();
		if($formdata){
			$old = $this->users_read($formdata);
			$errors = $this->users_validate($old, null, $formdata);
			if(empty($errors)){
				$db = $this->GetModel();
				$row = $old;
				if(array_key_exists('inisial', $row) && $row['inisial'] === ''){ $row['inisial'] = null; }   // kosong = otomatis
				$row['password'] = password_hash((string) $formdata['password'], PASSWORD_DEFAULT);
				$id = $db->insert("users", $row);
				if($id){
					genc_app_log('users.add', 'users', $id, 'Tambah user ' . $row['username'] . ' (' . $this->users_role_name($row['user_role_id']) . ')', array('username' => $row['username'], 'nama' => $row['nama'], 'role' => $this->users_role_name($row['user_role_id']), 'status' => $row['account_status'], 'inisial' => isset($row['inisial']) && $row['inisial'] !== null ? $row['inisial'] : 'otomatis'));
					$this->users_toast('User ' . $row['username'] . ' ditambahkan.');
					return $this->redirect("users");
				}
				$errors['_db'] = 'Gagal menyimpan: ' . $db->getLastError();
			}
		}
		return $this->users_form('add', $old, $errors, null);
	}

	function edit($rec_id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$db = $this->GetModel();
		$db->where("id_user", (int) $rec_id);
		$user = $db->getOne("users", array_merge(array("id_user", "nama", "email", "username", "account_status", "user_role_id"), $this->users_has_inisial() ? array("inisial") : array()));
		if($user && !array_key_exists('inisial', $user)){ $user['inisial'] = ''; }
		if($user){ $user['inisial'] = strtoupper(trim((string) $user['inisial'])); }
		if(!$user){ $this->users_toast('User tidak ditemukan.', 'danger'); return $this->redirect("users"); }
		$old = $user; $errors = array();
		if($formdata){
			$old = $this->users_read($formdata);
			$errors = $this->users_validate($old, $user, $formdata);
			if(empty($errors)){
				$upd = $old;
				if(array_key_exists('inisial', $upd) && $upd['inisial'] === ''){ $upd['inisial'] = null; }
				$pw = (string) (isset($formdata['password']) ? $formdata['password'] : '');
				if($pw !== ''){ $upd['password'] = password_hash($pw, PASSWORD_DEFAULT); }
				$diff = array();
				foreach(array('nama', 'email', 'username', 'account_status', 'user_role_id', 'inisial') as $k){
					if(!array_key_exists($k, $upd)){ continue; }
					if((string) $user[$k] !== (string) $upd[$k]){
						$a = $k === 'user_role_id' ? $this->users_role_name($user[$k]) : $user[$k];
						$b = $k === 'user_role_id' ? $this->users_role_name($upd[$k]) : $upd[$k];
						$diff[$k] = $a . ' -> ' . $b;
					}
				}
				if($pw !== ''){ $diff['password'] = 'diganti'; }
				if(empty($diff)){ $this->users_toast('Tidak ada yang berubah.', 'info'); return $this->redirect("users"); }
				$db->where("id_user", (int) $user['id_user']);
				if($db->update("users", $upd)){
					$note = array(); foreach($diff as $k => $v){ $note[] = $k . ': ' . $v; }
					genc_app_log('users.edit', 'users', $user['id_user'], 'Ubah user ' . $user['username'] . ' - ' . implode('; ', $note), $diff);
					$this->users_toast('Perubahan user ' . $upd['username'] . ' tersimpan.');
					return $this->redirect("users");
				}
				$errors['_db'] = 'Gagal menyimpan: ' . $db->getLastError();
			}
		}
		return $this->users_form('edit', $old, $errors, $user);
	}

	function delete($rec_id = null){
		Csrf::cross_check();
		$id = (int) $rec_id;
		if($id === (int) USER_ID){ $this->users_toast('Akun yang sedang dipakai tidak bisa dihapus.', 'danger'); return $this->redirect("users"); }
		$db = $this->GetModel();
		$db->where("id_user", $id);
		$u = $db->getOne("users", array("id_user", "username", "nama"));
		if($u){
			$db->where("id_user", $id);
			if($db->delete("users")){
				genc_app_log('users.delete', 'users', $id, 'Hapus user ' . $u['username'], array('username' => $u['username'], 'nama' => $u['nama']));
				$this->users_toast('User ' . $u['username'] . ' dihapus. Checklist lama yang diisinya tetap ada.');
			}
			else{ $this->users_toast('Gagal menghapus: ' . htmlspecialchars((string) $db->getLastError()), 'danger'); }
		}
		return $this->redirect("users");
	}

	/** Edit sebaris lama (x-editable) dimatikan: ubah lewat form supaya aturan di atas berlaku. */
	function editfield($rec_id = null, $formdata = null){
		render_error("Ubah user lewat halaman Ubah user.");
		return null;
	}

	// ------------------------------------------------------------------ helper
	protected function users_read($f){
		$g = function($k) use ($f){ return trim((string) (isset($f[$k]) ? $f[$k] : '')); };
		$o = array('nama' => $g('nama'), 'username' => $g('username'), 'email' => $g('email'),
		             'user_role_id' => $g('user_role_id'), 'account_status' => $g('account_status'));
		// [GENC-06OKT26-INISIAL] inisial paraf (kosong = otomatis). Hanya kalau kolomnya sudah ada (SQL genc_06okt26.sql)
		if($this->users_has_inisial()){ $o['inisial'] = strtoupper(preg_replace('/\s+/', '', $g('inisial'))); }
		return $o;
	}

	/** [GENC-06OKT26-INISIAL] Kolom users.inisial sudah ada? (1x SHOW COLUMNS per request) */
	private $users_has_ini = null;
	protected function users_has_inisial(){
		if($this->users_has_ini === null){
			$db = $this->GetModel();
			$r = $db->rawQuery("SHOW COLUMNS FROM users LIKE 'inisial'");
			$this->users_has_ini = is_array($r) && count($r) > 0;
		}
		return $this->users_has_ini;
	}

	/**
	 * [GENC-06OKT26-INISIAL] Inisial: 2-3 huruf/angka (kolom paraf report muat 3 huruf), unik (juga terhadap daftar resmi SPV milik username lain),
	 * bukan "SYS" (dipakai approve otomatis). Dicek hanya kalau berubah - data lama yang kebetulan kembar tetap bisa disimpan.
	 */
	protected function users_validate_inisial($ini, $user, $username){
		if($ini === '' || ($user && $ini === strtoupper(trim((string) $user['inisial'])))){ return ''; }
		if(!preg_match('/^[A-Z0-9]{2,3}$/', $ini)){ return 'Inisial 2 - 3 huruf / angka, tanpa spasi (contoh: ALS). Kolom paraf di report hanya muat 3 huruf.'; }
		if($ini === 'SYS'){ return 'SYS dipakai untuk approve otomatis, pilih inisial lain.'; }
		$db = $this->GetModel();
		$db->where("UPPER(inisial)", $ini); if($user){ $db->where("id_user", $user['id_user'], "!="); }
		$other = $db->getOne("users", array("username"));
		if($other){ return 'Inisial ' . $ini . ' sudah dipakai ' . $other['username'] . '.'; }
		$owners = array();
		foreach(checklist_official_initials() as $u => $code){ if($code === $ini){ $owners[] = $u; } }
		if(!empty($owners) && !in_array(strtolower($username), $owners, true)){
			$um = checklist_users_map();
			foreach($owners as $u){ if(isset($um['by_user'][$u]) && $um['by_user'][$u]['inisial'] !== '' && $um['by_user'][$u]['inisial'] !== $ini){ continue; } return 'Inisial ' . $ini . ' adalah paraf resmi ' . $u . ' (daftar SPV). Pilih inisial lain.'; }
		}
		return '';
	}

	protected function users_validate($o, $user, $f){
		$e = array();
		$db = $this->GetModel();
		if($o['nama'] === ''){ $e['nama'] = 'Nama wajib diisi.'; }
		if($o['username'] === ''){ $e['username'] = 'Username wajib diisi.'; }
		elseif(preg_match('/\s/', $o['username'])){ $e['username'] = 'Username tidak boleh berisi spasi.'; }
		else{
			$db->where("username", $o['username']); if($user){ $db->where("id_user", $user['id_user'], "!="); }
			if($db->has("users")){ $e['username'] = 'Username ini sudah dipakai user lain.'; }
		}
		if($o['email'] === '' || !filter_var($o['email'], FILTER_VALIDATE_EMAIL)){ $e['email'] = 'Email tidak valid.'; }
		else{
			$db->where("email", $o['email']); if($user){ $db->where("id_user", $user['id_user'], "!="); }
			if($db->has("users")){ $e['email'] = 'Email ini sudah dipakai user lain.'; }
		}
		$roles = $this->users_roles();
		if(!isset($roles[$o['user_role_id']])){ $e['user_role_id'] = 'Pilih role.'; }
		if($o['account_status'] === ''){ $e['account_status'] = 'Pilih status.'; }
		if(array_key_exists('inisial', $o) && ($msg = $this->users_validate_inisial($o['inisial'], $user, $o['username'])) !== ''){ $e['inisial'] = $msg; }   // [GENC-06OKT26-INISIAL]
		$pw = (string) (isset($f['password']) ? $f['password'] : '');
		$pw2 = (string) (isset($f['confirm_password']) ? $f['confirm_password'] : '');
		if(!$user && $pw === ''){ $e['password'] = 'Password wajib diisi untuk user baru.'; }
		if($pw !== '' && strlen($pw) < 6){ $e['password'] = 'Password minimal 6 karakter.'; }
		if($pw !== '' && $pw !== $pw2){ $e['confirm_password'] = 'Ulangi password tidak sama.'; }
		if($user && (int) $user['id_user'] === (int) USER_ID){
			if(self::users_is_blocked($o['account_status'])){ $e['account_status'] = 'Tidak bisa menonaktifkan akun yang sedang Anda pakai.'; }
			if((string) $o['user_role_id'] !== (string) $user['user_role_id']){ $e['user_role_id'] = 'Role akun sendiri tidak bisa diganti di sini (supaya tidak terkunci). Minta admin lain.'; }
		}
		return $e;
	}

	protected function users_role_name($id){
		$r = $this->users_roles();
		return isset($r[(string) $id]) ? $r[(string) $id]['role_name'] : ('role ' . $id);
	}

	protected function users_form($mode, $old, $errors, $user){
		$data = array('mode' => $mode, 'old' => $old, 'errors' => $errors, 'user' => $user, 'has_inisial' => $this->users_has_inisial(),
		              'roles' => $this->users_roles(), 'statuses' => $this->users_statuses(),
		              'is_self' => $user && (int) $user['id_user'] === (int) USER_ID);
		$this->view->page_title = $mode === 'add' ? 'Tambah user' : 'Ubah user';
		return $this->render_view("users/form.php", $data);
	}

	protected function users_toast($msg, $type = 'success'){
		$icon = array('success' => 'fa-check-circle', 'danger' => 'fa-exclamation-circle', 'info' => 'fa-info-circle');
		$html = '<div class="genc-toast genc-toast--' . $type . '" role="status"><i class="fa ' . $icon[$type] . '"></i><span>' . htmlspecialchars($msg) . '</span>'
		      . '<button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>';
		$this->set_flash_msg($html, 'custom');
	}
}
