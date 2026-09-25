<?php
/**
 * ROLE PERMISSIONS - matriks hak akses per role (Generasi C)   [GENC-24SEP26-SHELL]
 *
 * Dulu: daftar mentah ±800 baris (role_id, page_name, action_name) yang diisi satu per satu.
 * Sekarang: pilih role -> satu layar centang per halaman (Lihat / Detail / Isi / Ubah / Hapus /
 * Approve + aksi lain). Data yang disimpan TETAP baris role_permissions yang sama - ACL phpRAD,
 * sidebar, dan semua halaman membacanya seperti dulu.
 *
 * Aturan simpan:
 *  - hanya baris yang berbeda yang ditambah / dihapus (baris lain tidak disentuh)
 *  - mesin Checklist AM: aksi apa pun otomatis ikut "Lihat" (tanpa list = menu hilang & 403, K11)
 *  - role milik akun yang sedang login tidak bisa kehilangan role_permissions/list & /edit (anti terkunci)
 *  - perubahan dicatat di App Logs
 * Route: role_permissions (matriks), role_permissions/edit/<role_id> (POST simpan).
 * Cadangan: _backup_genc_24sept26/Role_permissionsController_sebelum-shell.php
 * @category  Controller
 */
require_once __DIR__ . '/../views/partials/_shared/genc_perm_catalog.php';
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';

class Role_permissionsController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "role_permissions";
	}

	protected function rp_roles(){
		$db = $this->GetModel();
		$rows = $db->rawQuery("SELECT r.role_id, r.role_name,
			(SELECT COUNT(*) FROM role_permissions p WHERE p.role_id = r.role_id) AS n_perm,
			(SELECT COUNT(*) FROM users u WHERE u.user_role_id = r.role_id) AS n_user
			FROM roles r ORDER BY r.role_name");
		$out = array();
		foreach((array) $rows as $r){ $out[(string) $r['role_id']] = $r; }
		return $out;
	}

	/** Susunan matriks + scope (semua pasangan page/action yang tampil = yang boleh diubah form). */
	protected function rp_model($role_id){
		$db = $this->GetModel();
		$all = array();
		foreach((array) $db->rawQuery("SELECT DISTINCT page_name, action_name FROM role_permissions") as $r){
			$all[strtolower($r['page_name'])][strtolower($r['action_name'])] = 1;
		}
		$have = array();
		foreach((array) $db->rawQuery("SELECT page_name, action_name FROM role_permissions WHERE role_id = ?", array($role_id)) as $r){
			$have[strtolower($r['page_name']) . '/' . strtolower($r['action_name'])] = 1;
		}
		$sections = array(); $scope = array(); $known_pages = array(); $taken = array();
		foreach(genc_perm_catalog() as $sec){
			foreach($sec['rows'] as $row){ foreach($row['actions'] as $a){ $taken[$row['page'] . '/' . $a] = 1; } $known_pages[$row['page']] = 1; }
		}
		foreach(genc_perm_catalog() as $sec){
			$rows = array();
			foreach($sec['rows'] as $row){
				foreach($row['actions'] as $a){ $scope[$row['page'] . '/' . $a] = 1; }
				$row['extras'] = array();
				if(empty($row['single']) && isset($all[$row['page']])){
					foreach(array_keys($all[$row['page']]) as $a){
						if(!isset($taken[$row['page'] . '/' . $a])){ $row['extras'][] = $a; $scope[$row['page'] . '/' . $a] = 1; }
					}
					sort($row['extras']);
				}
				$rows[] = $row;
			}
			$sections[] = array('title' => $sec['title'], 'rows' => $rows);
		}
		$other = array();
		ksort($all);
		foreach($all as $page => $acts){
			if(isset($known_pages[$page])){ continue; }
			$a = array_keys($acts); sort($a);
			foreach($a as $x){ $scope[$page . '/' . $x] = 1; }
			$other[] = array('page' => $page, 'name' => $page, 'actions' => array(), 'extras' => $a);
		}
		if($other){ $sections[] = array('title' => 'Lainnya (halaman lama / bawaan phpRAD)', 'rows' => $other, 'other' => true); }
		return array('sections' => $sections, 'have' => $have, 'scope' => $scope);
	}

	function index($fieldname = null, $fieldvalue = null){
		$roles = $this->rp_roles();
		$role = isset($this->request->role) ? (string) $this->request->role : '';
		if(!isset($roles[$role])){ $role = isset($roles[(string) USER_ROLE]) ? (string) USER_ROLE : (string) key($roles); }
		$data = array(
			'roles' => $roles, 'role' => $role,
			'model' => $role !== '' ? $this->rp_model($role) : array('sections' => array(), 'have' => array(), 'scope' => array()),
			'can_edit' => ACL::is_allowed('role_permissions/edit'),
			'is_own' => (string) USER_ROLE === $role,
			'locked' => array('role_permissions/list', 'role_permissions/edit'),
		);
		$this->view->page_title = "Role Permissions";
		return $this->render_view("role_permissions/list.php", $data);
	}

	/** Simpan matriks satu role (POST). GET -> kembali ke matriks. */
	function edit($rec_id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$roles = $this->rp_roles();
		$role = (string) (int) $rec_id;
		if(!$formdata || !isset($roles[$role])){ return $this->redirect("role_permissions" . (isset($roles[$role]) ? "?role=" . $role : "")); }
		$m = $this->rp_model($role);
		$want = array();
		foreach((array) (isset($formdata['p']) ? $formdata['p'] : array()) as $pa){
			$pa = strtolower(trim((string) $pa));
			if(isset($m['scope'][$pa])){ $want[$pa] = 1; }
		}
		// mesin: aksi apa pun -> ikut "Lihat" (list)
		foreach(genc_perm_catalog() as $sec){
			foreach($sec['rows'] as $row){
				if(empty($row['machine'])){ continue; }
				foreach($want as $pa => $_){
					list($pg) = explode('/', $pa);
					if($pg === $row['page']){ $want[$row['page'] . '/list'] = 1; break; }
				}
			}
		}
		if($role === (string) USER_ROLE){ $want['role_permissions/list'] = 1; $want['role_permissions/edit'] = 1; }
		$add = array(); $del = array();
		foreach($want as $pa => $_){ if(!isset($m['have'][$pa])){ $add[] = $pa; } }
		foreach($m['have'] as $pa => $_){ if(isset($m['scope'][$pa]) && !isset($want[$pa])){ $del[] = $pa; } }
		$db = $this->GetModel();
		foreach($add as $pa){
			list($pg, $ac) = explode('/', $pa, 2);
			$db->insert("role_permissions", array('role_id' => $role, 'page_name' => $pg, 'action_name' => $ac));
		}
		foreach($del as $pa){
			list($pg, $ac) = explode('/', $pa, 2);
			$db->where("role_id", $role)->where("page_name", $pg)->where("action_name", $ac);
			$db->delete("role_permissions");
		}
		$name = $roles[$role]['role_name'];
		if($add || $del){
			genc_app_log('permissions.save', 'role_permissions', $role, 'Ubah hak akses role ' . $name . ': +' . count($add) . ' / -' . count($del),
				array('role' => $name, 'ditambah' => implode(', ', $add), 'dicabut' => implode(', ', $del)));
			$msg = 'Hak akses ' . $name . ' tersimpan: ' . count($add) . ' ditambah, ' . count($del) . ' dicabut. Berlaku saat user role ini membuka halaman berikutnya.';
			$type = 'success';
		}
		else{ $msg = 'Tidak ada perubahan hak akses.'; $type = 'info'; }
		$icon = array('success' => 'fa-check-circle', 'info' => 'fa-info-circle');
		$this->set_flash_msg('<div class="genc-toast genc-toast--' . $type . '" role="status"><i class="fa ' . $icon[$type] . '"></i><span>' . htmlspecialchars($msg) . '</span><button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>', 'custom');
		return $this->redirect("role_permissions?role=" . $role);
	}

	/** Form lama per baris diganti matriks. */
	function add($formdata = null){ return $this->redirect("role_permissions"); }
	function view($rec_id = null, $value = null){ return $this->redirect("role_permissions"); }
	function delete($rec_id = null){ return $this->redirect("role_permissions"); }
	function editfield($rec_id = null, $formdata = null){ render_error("Ubah hak akses lewat matriks Role Permissions."); return null; }
}
