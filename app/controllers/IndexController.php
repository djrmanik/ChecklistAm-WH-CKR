<?php 
/**
 * Index Page Controller
 * @category  Controller
 */
class IndexController extends BaseController{
	function __construct(){
		parent::__construct(); 
		$this->tablename = "users";
	}
	/**
     * Index Action 
     * @return null
     */
	function index(){
		if(user_login_status() == true){
			$this->redirect(HOME_PAGE);
		}
		else{
			$this->view->page_title = "Masuk - " . SITE_NAME;   // [GENC-24SEP26-SHELL]
			$this->render_view("index/index.php");
		}
	}
	private function login_user($username , $password_text, $rememberme = false){
		$db = $this->GetModel();
		$username = filter_var($username, FILTER_SANITIZE_STRING);
		$db->where("username", $username)->orWhere("email", $username);
		$tablename = $this->tablename;
		$user = $db->getOne($tablename);
		require_once ROOT . 'app/views/partials/_shared/genc_log.php';   // [GENC-24SEP26-SHELL] app_logs
		if(!empty($user) && in_array(strtolower(trim((string) $user['account_status'])), array('nonaktif', 'non aktif', 'inactive', 'disabled', 'blocked', 'diblokir'), true)){
			// [GENC-24SEP26-SHELL] status akun "Nonaktif" (diatur admin di menu Users) memblokir login
			genc_app_log('userlogin.blocked', 'users', $user['id_user'], 'Login ditolak (akun nonaktif): ' . $user['username'], array('username' => $user['username']), $user['id_user']);
			return $this->login_fail("Akun ini sedang dinonaktifkan. Hubungi admin / SPV warehouse.");
		}
		if(!empty($user)){
			//Verify User Password Text With DB Password Hash Value.
			//Uses PHP password_verify() function with default options
			$password_hash = $user['password'];
			$this->modeldata['password'] = $password_hash; //update the modeldata with the password hash
			if(password_verify($password_text,$password_hash)){
        		unset($user['password']); //Remove user password. No need to store it in the session
				set_session("user_data", $user); // Set active user data in a sessions
				genc_app_log('userlogin', 'users', $user['id_user'], 'Login: ' . $user['username'], array('username' => $user['username'], 'ingat_saya' => ($rememberme ? 'ya' : 'tidak')), $user['id_user']);
				//if Remeber Me, Set Cookie
				if($rememberme == true){
					$sessionkey = time().random_str(20); // Generate a session key for the user
					//Update user session info in database with the session key
					$db->where("id_user", $user['id_user']);
					$res = $db->update($tablename, array("login_session_key" => hash_value($sessionkey)));
					if(!empty($res)){
						set_cookie("login_session_key", $sessionkey); // save user login_session_key in a Cookie
					}
				}
				else{
					clear_cookie("login_session_key");// Clear any previous set cookie
				}
				$redirect_url = get_session("login_redirect_url");// Redirect to user active page
				if(!empty($redirect_url)){
					clear_session("login_redirect_url");
					return $this->redirect($redirect_url);
				}
				else{
					return $this->redirect(HOME_PAGE);
				}
			}
			else{
				//password is not correct
				genc_app_log('userlogin.failed', 'users', $user['id_user'], 'Login gagal (password salah): ' . $user['username'], array('username' => $user['username']), $user['id_user']);
				return $this->login_fail("Username or password not correct");
			}
		}
		else{
			//user is not registered
			return $this->login_fail("Username or password not correct");
		}
	}
	/**
     * Display login page with custom message when login fails
     * @return BaseView
     */
	private function login_fail($page_error = null){
		$this->set_page_error($page_error);
		$this->view->page_title = "Masuk - " . SITE_NAME;
		$this->render_view("index/login.php");
	}
	/**
     * Login Action
     * If Not $_POST Request, Display Login Form View
     * @return View
     */
	function login($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		if(user_login_status() == true){ return $this->redirect(HOME_PAGE); }   // [GENC-25SEP26-AUDIT] sudah login -> tidak perlu halaman masuk/daftar
		if($formdata){
			$modeldata = $this->modeldata = $formdata;
			$username = trim($modeldata['username']);
			$password = $modeldata['password'];
			$rememberme = (!empty($modeldata['rememberme']) ? $modeldata['rememberme'] : false);
			$this->login_user($username, $password, $rememberme);
		}
		else{
			$this->set_page_error("Invalid request");
			$this->render_view("index/login.php");
		}
	}
	/**
     * Insert new record into the user table
	 * @param $formdata array from $_POST
     * @return BaseView
     */
	function register($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		if(user_login_status() == true){ return $this->redirect(HOME_PAGE); }   // [GENC-25SEP26-AUDIT] sudah login -> tidak perlu halaman masuk/daftar
		if($formdata){
			$request = $this->request;
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$fields = $this->fields = array("nama","email","username","password","account_status","user_role_id"); //registration fields
			$postdata = $this->format_request_data($formdata);
			$cpassword = $postdata['confirm_password'];
			$password = $postdata['password'];
			if($cpassword != $password){
				$this->view->page_error[] = "Ulangi password tidak sama.";
			}
			// [GENC-24SEP26-SHELL] daftar sendiri = SELALU Operator/Staff & Active (dulu role dipilih bebas dari form -> bisa jadi Administrator)
			$postdata['user_role_id'] = $this->genc_operator_role_id($db);
			$postdata['account_status'] = 'Active';
			$this->rules_array = array(
				'nama' => 'required',
				'email' => 'required|valid_email',
				'username' => 'required',
				'password' => 'required',
				'account_status' => 'required',
				'user_role_id' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'email' => 'sanitize_string',
				'username' => 'sanitize_string',
				'account_status' => 'sanitize_string',
				'user_role_id' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			$password_text = $modeldata['password'];
			//update modeldata with the password hash
			$modeldata['password'] = $this->modeldata['password'] = password_hash($password_text , PASSWORD_DEFAULT);
			//Check if Duplicate Record Already Exit In The Database
			$db->where("email", $modeldata['email']);
			if($db->has($tablename)){
				$this->view->page_error[] = "Email " . htmlspecialchars($modeldata['email']) . " sudah terdaftar.";
			}
			//Check if Duplicate Record Already Exit In The Database
			$db->where("username", $modeldata['username']);
			if($db->has($tablename)){
				$this->view->page_error[] = "Username " . htmlspecialchars($modeldata['username']) . " sudah dipakai.";
			}
			if(strlen((string) $password_text) < 6){ $this->view->page_error[] = "Password minimal 6 karakter."; }
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					require_once ROOT . 'app/views/partials/_shared/genc_log.php';
					genc_app_log('users.register', 'users', $rec_id, 'Daftar akun baru (Operator/Staff): ' . $modeldata['username'], array('username' => $modeldata['username'], 'nama' => $modeldata['nama']), $rec_id);
					$this->login_user($modeldata['email'] , $password_text);
					return;
				}
				else{
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Daftar akun - " . SITE_NAME;
		return $this->render_view("index/register.php");
	}
	/**
     * Logout Action
     * Destroy All Sessions And Cookies
     * @return View
     */
	/**
	 * [GENC-24SEP26-SHELL] role untuk akun yang mendaftar sendiri: role bernama "Operator..."
	 * (di data kantor: 8 = Operator/Staff). Tidak ketemu -> role dengan hak akses paling sedikit.
	 */
	private function genc_operator_role_id($db){
		$id = $db->rawQueryValue("SELECT role_id FROM roles WHERE role_name LIKE 'Operator%' ORDER BY role_id LIMIT 1");
		if(is_array($id)){ $id = reset($id); }
		if(!$id){
			$id = $db->rawQueryValue("SELECT r.role_id FROM roles r LEFT JOIN role_permissions p ON p.role_id = r.role_id GROUP BY r.role_id ORDER BY COUNT(p.permission_id) ASC LIMIT 1");
			if(is_array($id)){ $id = reset($id); }
		}
		return (string) $id;
	}

	function logout($arg=null){
		Csrf::cross_check();
		if(USER_NAME){ require_once ROOT . 'app/views/partials/_shared/genc_log.php'; genc_app_log('userlogout', 'users', USER_ID, 'Keluar: ' . USER_NAME); }   // [GENC-24SEP26-SHELL]
		session_destroy();
		clear_cookie("login_session_key");
		$this->redirect("?pesan=keluar");
	}
}
