<?php
/**
 * MASTER SELECT - Generasi C                             [GENC-25SEP26-AUDIT]
 * [GENC-08OKT26-P3] Sekarang juga "Pilihan hasil cek" untuk SEMUA mesin: label tombol Baik / Tidak baik /
 * Perawatan bisa diganti (tabel genc_pilihan). Kode yang disimpan ke database & dicetak di report TETAP
 * OK / NOK / PR -> data lama, penilaian temuan & report tidak berubah. Simpan: POST master_select/edit/pilihan.
 * Daftar pilihan dropdown form phpRAD LAMA (form Forklift Gen A). Form Gen C memakai tombol
 * Baik / Tidak baik / Perawatan, jadi daftar ini sekarang hanya arsip + bahan membaca isian lama.
 * Tetap bisa ditambah / diubah / dihapus (route & hak akses sama: master_select/list|add|edit|delete).
 * Cadangan: _backup_genc_25sept26/Master_selectController_sebelum-audit.php
 * @category  Controller
 */
require_once __DIR__ . '/../views/partials/_shared/genc_log.php';
require_once __DIR__ . '/../views/partials/_shared/genc_registry.php';   // [GENC-08OKT26-P3] genc_reg_ensure_tables / genc_reg_clean
require_once __DIR__ . '/../views/partials/_shared/genc_helpers.php';

class Master_selectController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "master_select";
	}

	protected function ms_render($form = null){
		$db = $this->GetModel();
		$rows = $db->rawQuery("SELECT id, value, label FROM master_select ORDER BY id ASC LIMIT 1000");
		$data = array('rows' => is_array($rows) ? $rows : array(), 'form' => $form,
			'pilihan' => genc_pilihan_labels(true), 'pilihan_awal' => genc_pilihan_default(),
			'pil_old' => isset($form['pil_old']) ? $form['pil_old'] : null, 'pil_err' => isset($form['pil_err']) ? $form['pil_err'] : array(),
			'can' => array('add' => ACL::is_allowed('master_select/add'), 'edit' => ACL::is_allowed('master_select/edit'), 'delete' => ACL::is_allowed('master_select/delete')));
		$this->view->page_title = "Master Select";
		return $this->render_view("master_select/list.php", $data);
	}

	function index($fieldname = null, $fieldvalue = null){ return $this->ms_render(); }
	function view($rec_id = null, $value = null){ return $this->redirect("master_select"); }

	protected function ms_read($f){
		return array('value' => trim((string) (isset($f['value']) ? $f['value'] : '')), 'label' => trim((string) (isset($f['label']) ? $f['label'] : '')));
	}
	protected function ms_check($o){
		$e = array();
		if($o['value'] === ''){ $e['value'] = 'Nilai wajib diisi.'; }
		if($o['label'] === ''){ $e['label'] = 'Label wajib diisi.'; }
		return $e;
	}

	function add($formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		$o = array('value' => '', 'label' => ''); $e = array();
		if($formdata){
			$o = $this->ms_read($formdata); $e = $this->ms_check($o);
			if(!$e){
				$db = $this->GetModel();
				$id = $db->insert("master_select", $o);
				if($id){
					genc_app_log('master_select.add', 'master_select', $id, 'Tambah pilihan Master Select: ' . $o['value'] . ' = ' . $o['label'], $o);
					$this->ms_toast('Pilihan ditambahkan.');
					return $this->redirect("master_select");
				}
				$e['_db'] = 'Gagal menyimpan: ' . $db->getLastError();
			}
		}
		return $this->ms_render(array('mode' => 'add', 'old' => $o, 'err' => $e, 'id' => null));
	}

	function edit($rec_id = null, $formdata = null){
		if(!is_post_request() || !is_array($formdata)){ $formdata = null; }   // [GENC-25SEP26-AUDIT] segmen URL ekstra (mis. .../edit/5/x) dulu dianggap isian form
		if($rec_id === 'pilihan'){ return $this->ms_save_pilihan($formdata); }   // [GENC-08OKT26-P3]
		$db = $this->GetModel();
		$db->where("id", (int) $rec_id);
		$row = $db->getOne("master_select", array("id", "value", "label"));
		if(!$row){ return $this->redirect("master_select"); }
		$o = array('value' => $row['value'], 'label' => $row['label']); $e = array();
		if($formdata){
			$o = $this->ms_read($formdata); $e = $this->ms_check($o);
			if(!$e){
				$db->where("id", (int) $row['id']);
				if($db->update("master_select", $o)){
					genc_app_log('master_select.edit', 'master_select', $row['id'], 'Ubah pilihan Master Select #' . $row['id'] . ': ' . $row['value'] . ' = ' . $row['label'] . ' -> ' . $o['value'] . ' = ' . $o['label'], $o);
					$this->ms_toast('Pilihan diubah.');
					return $this->redirect("master_select");
				}
				$e['_db'] = 'Gagal menyimpan: ' . $db->getLastError();
			}
		}
		return $this->ms_render(array('mode' => 'edit', 'old' => $o, 'err' => $e, 'id' => (int) $row['id']));
	}

	function delete($rec_id = null){
		Csrf::cross_check();
		$db = $this->GetModel();
		$db->where("id", (int) $rec_id);
		$row = $db->getOne("master_select", array("id", "value", "label"));
		if($row){
			$db->where("id", (int) $row['id']);
			if($db->delete("master_select")){
				genc_app_log('master_select.delete', 'master_select', $row['id'], 'Hapus pilihan Master Select: ' . $row['value'] . ' = ' . $row['label'], $row);
				$this->ms_toast('Pilihan dihapus.');
			}
		}
		return $this->redirect("master_select");
	}

	/** [GENC-08OKT26-P3] Simpan label tombol hasil cek (kosong = label awal). */
	protected function ms_save_pilihan($formdata){
		if(!$formdata){ return $this->redirect("master_select"); }
		if($e = genc_reg_ensure_tables()){ $this->ms_toast('Tabel belum bisa dibuat: ' . $e); return $this->redirect("master_select"); }
		$awal = genc_pilihan_default();
		$cur = genc_pilihan_labels(true);
		$in = isset($formdata['label']) && is_array($formdata['label']) ? $formdata['label'] : array();
		$new = array(); $err = array(); $old = array();
		foreach($awal as $k => $a){
			$v = genc_reg_clean(isset($in[$k]) ? $in[$k] : '', 20);
			$old[$k] = $v;
			$new[$k] = $v === '' ? $a : $v;
		}
		$seen = array();
		foreach($new as $k => $v){
			$n = strtolower($v);
			if(isset($seen[$n])){ $err[$k] = 'Label sama dengan pilihan ' . $seen[$n] . ' - operator tidak bisa membedakan.'; }
			$seen[$n] = $k;
		}
		if($err){ return $this->ms_render(array('mode' => '', 'pil_old' => $old, 'pil_err' => $err)); }
		$diff = array();
		foreach($new as $k => $v){ if($v !== $cur[$k]){ $diff[$k] = $cur[$k] . ' -> ' . $v; } }
		if(!$diff){ $this->ms_toast('Tidak ada yang berubah.'); return $this->redirect("master_select"); }
		$pdo = genc_reg_db();
		foreach($new as $k => $v){
			if($v === $awal[$k]){ $pdo->prepare("DELETE FROM genc_pilihan WHERE kode = ?")->execute(array($k)); }
			else{ $pdo->prepare("REPLACE INTO genc_pilihan (kode, label, updated_at, updated_by) VALUES (?,?,?,?)")->execute(array($k, $v, datetime_now(), USER_NAME)); }
		}
		genc_pilihan_labels(true);
		$note = array(); foreach($diff as $k => $v){ $note[] = $k . ': ' . $v; }
		genc_app_log('master_select.pilihan', 'genc_pilihan', 0, 'Ubah label tombol hasil cek - ' . implode('; ', $note), $diff);
		$this->ms_toast('Label tombol hasil cek tersimpan - langsung dipakai di form isi, daftar & detail semua mesin. Kode di database & report tetap OK / NOK / PR.');
		return $this->redirect("master_select");
	}

	function editfield($rec_id = null, $formdata = null){ render_error("Ubah lewat halaman Master Select."); return null; }

	protected function ms_toast($msg){
		$this->set_flash_msg('<div class="genc-toast genc-toast--success" role="status"><i class="fa fa-check-circle"></i><span>' . htmlspecialchars($msg) . '</span><button type="button" class="genc-toast__close" aria-label="Tutup" onclick="this.parentNode.remove()">&times;</button></div>', 'custom');
	}
}
