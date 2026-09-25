<?php
/**
 * ROLES - Generasi C                                     [GENC-24SEP26-SHELL]
 * Kartu per role: jumlah user & hak akses, tautan ke matriks hak akses & daftar user.
 * Tambah role bisa menyalin hak akses role lain (mis. "SPV Shift 2" = salinan Manager & Supervisor).
 * Hapus role hanya kalau tidak ada user yang memakainya; hak akses role itu ikut dihapus.
 * Route sama: roles/list, roles/add, roles/edit, roles/delete.
 * Cadangan: _backup_genc_24sept26/RolesController_sebelum-shell.php
 * @category  Controller
 */
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';

class RolesController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "roles";
	}

	protected function roles_all(){
		$db = $this->GetModel();
		$rows = $db->rawQuery("SELECT r.role_id, r.role_name,
			(SELECT COUNT(*) FROM role_permissions p WHERE p.role_id = r.role_id) AS n_perm,
			(SELECT COUNT(*) FROM users u WHERE u.user_role_id = r.role_id) AS n_user,
			(SELECT COUNT(DISTINCT p.page_name) FROM role_permissions p WHERE p.role_id = r.role_id AND p.action_name = 'approve') AS n_approve,
			(SELECT COUNT(DISTINCT p.page_name) FROM role_permissions p WHERE p.role_id = r.role_id AND p.action_name = 'add') AS n_add
			FROM roles r ORDER BY r.role_name");
		$out = array();
		foreach((array) $rows as $r){ $out[(string) $r['role_id']] = $r; }
		return $out;
	}

	function index($fieldname = null, $fieldvalue = null){
		$data = array('roles' => $this->roles_all(),
			'can' => array('add' => ACL::is_allowed('roles/add'), 'edit' => ACL::is_allowed('roles/edit'), 'delete' => ACL::is_allowed('roles/delete'),
			               'perm' => ACL::is_allowed('role_permissions/list'), 'users' => ACL::is_allowed('users/list')),
			'form' => null);
		$this->view->page_title = "Roles";
		return $this->render_view("roles/list.php", $data);
	}

	function view($rec_id = null, $value = null){ return $this->redirect("roles"); }

	function add($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$roles = $this->roles_all();
		$old = array('role_name' => '', 'copy_from' => ''); $err = '';
		if($formdata){
			$old['role_name'] = trim((string) (isset($formdata['role_name']) ? $formdata['role_name'] : ''));
			$old['copy_from'] = (string) (isset($formdata['copy_from']) ? $formdata['copy_from'] : '');
			$err = $this->roles_check_name($old['role_name'], null, $roles);
			if($err === ''){
				$db = $this->GetModel();
				$id = $db->insert("roles", array('role_name' => $old['role_name']));
				if($id){
					$n = 0;
					if($old['copy_from'] !== '' && isset($roles[$old['copy_from']])){
						foreach((array) $db->rawQuery("SELECT page_name, action_name FROM role_permissions WHERE role_id = ?", array($old['copy_from'])) as $p){
							$db->insert("role_permissions", array('role_id' => $id, 'page_name' => $p['page_name'], 'action_name' => $p['action_name'])); $n++;
						}
					}
					genc_app_log('roles.add', 'roles', $id, 'Tambah role ' . $old['role_name'] . ($n ? ' (salin ' . $n . ' hak akses dari ' . $roles[$old['copy_from']]['role_name'] . ')' : ''), array('role' => $old['role_name']));
					$this->roles_toast('Role ' . $old['role_name'] . ' dibuat' . ($n ? ' dengan ' . $n . ' hak akses.' : '. Atur hak aksesnya sekarang.'));
					return $this->redirect(ACL::is_allowed('role_permissions/list') ? "role_permissions?role=" . $id : "roles");
				}
				$err = 'Gagal menyimpan: ' . $db->getLastError();
			}
		}
		return $this->roles_form('add', $old, $err, $roles, null);
	}

	function edit($rec_id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$roles = $this->roles_all();
		$id = (string) (int) $rec_id;
		if(!isset($roles[$id])){ return $this->redirect("roles"); }
		$old = array('role_name' => $roles[$id]['role_name']); $err = '';
		if($formdata){
			$old['role_name'] = trim((string) (isset($formdata['role_name']) ? $formdata['role_name'] : ''));
			$err = $this->roles_check_name($old['role_name'], $id, $roles);
			if($err === ''){
				$db = $this->GetModel();
				$db->where("role_id", $id);
				if($db->update("roles", array('role_name' => $old['role_name']))){
					if($old['role_name'] !== $roles[$id]['role_name']){
						genc_app_log('roles.edit', 'roles', $id, 'Ganti nama role ' . $roles[$id]['role_name'] . ' -> ' . $old['role_name'], array('lama' => $roles[$id]['role_name'], 'baru' => $old['role_name']));
					}
					$this->roles_toast('Nama role tersimpan.');
					return $this->redirect("roles");
				}
				$err = 'Gagal menyimpan: ' . $db->getLastError();
			}
		}
		return $this->roles_form('edit', $old, $err, $roles, $id);
	}

	function delete($rec_id = null){
		Csrf::cross_check();
		$roles = $this->roles_all();
		$id = (string) (int) $rec_id;
		if(isset($roles[$id])){
			$r = $roles[$id];
			if((int) $r['n_user'] > 0){ $this->roles_toast('Role ' . $r['role_name'] . ' masih dipakai ' . $r['n_user'] . ' user. Pindahkan user-nya dulu.', 'danger'); }
			elseif($id === (string) USER_ROLE){ $this->roles_toast('Role akun Anda sendiri tidak bisa dihapus.', 'danger'); }
			else{
				$db = $this->GetModel();
				$db->where("role_id", $id); $db->delete("role_permissions");
				$db->where("role_id", $id);
				if($db->delete("roles")){
					genc_app_log('roles.delete', 'roles', $id, 'Hapus role ' . $r['role_name'] . ' (' . $r['n_perm'] . ' hak akses ikut dihapus)', array('role' => $r['role_name']));
					$this->roles_toast('Role ' . $r['role_name'] . ' dihapus.');
				}
			}
		}
		return $this->redirect("roles");
	}

	function editfield($rec_id = null, $formdata = null){ render_error("Ubah role lewat halaman Roles."); return null; }

	protected function roles_check_name($name, $id, $roles){
		if($name === ''){ return 'Nama role wajib diisi.'; }
		foreach($roles as $rid => $r){ if((string) $rid !== (string) $id && strcasecmp(trim($r['role_name']), $name) === 0){ return 'Nama role ini sudah ada.'; } }
		return '';
	}

	protected function roles_form($mode, $old, $err, $roles, $id){
		$data = array('roles' => $roles,
			'can' => array('add' => ACL::is_allowed('roles/add'), 'edit' => ACL::is_allowed('roles/edit'), 'delete' => ACL::is_allowed('roles/delete'),
			               'perm' => ACL::is_allowed('role_permissions/list'), 'users' => ACL::is_allowed('users/list')),
			'form' => array('mode' => $mode, 'old' => $old, 'err' => $err, 'id' => $id));
		$this->view->page_title = $mode === 'add' ? "Tambah role" : "Ubah role";
		return $this->render_view("roles/list.php", $data);
	}

	protected function roles_toast($msg, $type = 'success'){
		$icon = array('success' => 'fa-check-circle', 'danger' => 'fa-exclamation-circle', 'info' => 'fa-info-circle');
		$this->set_flash_msg('<div class="genc-toast genc-toast--' . $type . '" role="status"><i class="fa ' . $icon[$type] . '"></i><span>' . htmlspecialchars($msg) . '</span><button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>', 'custom');
	}
}
