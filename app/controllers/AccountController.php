<?php
/**
 * AKUN SAYA - Generasi C                                 [GENC-24SEP26-SHELL]
 *
 * Dulu (cadangan: _backup_genc_24sept26/AccountController_sebelum-shell.php):
 *  - form "Edit" berisi Role & Account Status -> operator bisa menjadikan dirinya Administrator/Developer
 *  - ganti data diri wajib ketik password baru (hash lama ditampilkan di form)
 * Sekarang:
 *  - Nama & email boleh diubah sendiri. Username, role, status HANYA lewat menu Users (admin).
 *  - Ganti password wajib password lama; minimal 6 karakter.
 *  - Dicatat di App Logs.
 * Route: account (lihat), account/edit (POST data diri), account/change_password (POST).
 * @category  Controller
 */
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';

class AccountController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "users";
	}

	protected function acc_user(){
		$db = $this->GetModel();
		$db->where("id_user", USER_ID);
		return $db->getOne("users", array("id_user", "nama", "email", "username", "account_status", "user_role_id", "password"));
	}

	protected function acc_render($user, $errors = array(), $old = array()){
		unset($user['password']);
		$db = $this->GetModel();
		$role = $db->rawQueryValue("SELECT role_name FROM roles WHERE role_id = ?", array($user['user_role_id']));
		if(is_array($role)){ $role = reset($role); }
		$n = $db->rawQueryValue("SELECT COUNT(*) FROM role_permissions WHERE role_id = ?", array($user['user_role_id']));
		if(is_array($n)){ $n = reset($n); }
		$this->view->page_title = "Akun saya";
		return $this->render_view("account/view.php", array('user' => $user, 'role' => (string) $role, 'n_perm' => (int) $n, 'errors' => $errors, 'old' => $old));
	}

	function index(){
		$user = $this->acc_user();
		if(!$user){ return $this->redirect("index/logout?csrf_token=" . Csrf::$token); }
		return $this->acc_render($user);
	}

	/** Ubah nama & email sendiri. */
	function edit($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$user = $this->acc_user();
		if(!$user){ return $this->redirect(""); }
		if(!$formdata){ return $this->redirect("account"); }
		$nama = trim((string) (isset($formdata['nama']) ? $formdata['nama'] : ''));
		$email = trim((string) (isset($formdata['email']) ? $formdata['email'] : ''));
		$e = array();
		if($nama === ''){ $e['nama'] = 'Nama wajib diisi.'; }
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ $e['email'] = 'Email tidak valid.'; }
		else{
			$db = $this->GetModel();
			$db->where("email", $email)->where("id_user", $user['id_user'], "!=");
			if($db->has("users")){ $e['email'] = 'Email ini sudah dipakai akun lain.'; }
		}
		if($e){ return $this->acc_render($user, $e, array('nama' => $nama, 'email' => $email)); }
		if($nama !== $user['nama'] || $email !== $user['email']){
			$db = $this->GetModel();
			$db->where("id_user", $user['id_user']);
			$db->update("users", array('nama' => $nama, 'email' => $email));
			$sess = get_session("user_data");
			if(is_array($sess)){ $sess['nama'] = $nama; $sess['email'] = $email; set_session("user_data", $sess); }
			genc_app_log('users.edit', 'users', $user['id_user'], 'Ubah data diri ' . $user['username'] . ' (lewat Akun saya)', array('nama' => $user['nama'] . ' -> ' . $nama, 'email' => $user['email'] . ' -> ' . $email));
		}
		$this->acc_toast('Data diri tersimpan.');
		return $this->redirect("account");
	}

	/** Ganti password sendiri (wajib password lama). */
	function change_password($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$user = $this->acc_user();
		if(!$user){ return $this->redirect(""); }
		if(!$formdata){ return $this->redirect("account"); }
		$cur = (string) (isset($formdata['current_password']) ? $formdata['current_password'] : '');
		$new = (string) (isset($formdata['new_password']) ? $formdata['new_password'] : '');
		$new2 = (string) (isset($formdata['confirm_password']) ? $formdata['confirm_password'] : '');
		$e = array();
		if(!password_verify($cur, (string) $user['password'])){ $e['current_password'] = 'Password lama salah.'; }
		if(strlen($new) < 6){ $e['new_password'] = 'Password baru minimal 6 karakter.'; }
		elseif($new !== $new2){ $e['confirm_password'] = 'Ulangi password tidak sama.'; }
		if($e){ return $this->acc_render($user, $e); }
		$db = $this->GetModel();
		$db->where("id_user", $user['id_user']);
		$db->update("users", array('password' => password_hash($new, PASSWORD_DEFAULT)));
		genc_app_log('users.edit', 'users', $user['id_user'], 'Ganti password ' . $user['username'] . ' (lewat Akun saya)', array('password' => 'diganti'));
		$this->acc_toast('Password diganti. Pakai password baru saat login berikutnya.');
		return $this->redirect("account");
	}

	/** Halaman lama "ganti email" -> digabung ke Akun saya. */
	function change_email($formdata = null){ return $this->redirect("account"); }

	protected function acc_toast($msg){
		$this->set_flash_msg('<div class="genc-toast genc-toast--success" role="status"><i class="fa fa-check-circle"></i><span>' . htmlspecialchars($msg) . '</span><button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>', 'custom');
	}
}
