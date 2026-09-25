<?php
/**
 * ============================================================================
 *  ConveyorController                                     dibuat 23 Sep 2026
 *
 *  [GENC-24SEP26] Logika halaman DIPINDAH ke cetakan bersama
 *      app/controllers/_base/GencChecklistBase.php
 *  dan tampilan ke view bersama
 *      app/views/partials/_shared/genc/{list,add,view}.php
 *  File ini tinggal konfigurasi. Perilaku & tampilan TIDAK berubah
 *  (dibandingkan output HTML sebelum vs sesudah - lihat handover GenC-01).
 *  Versi lama utuh: _backup_genc_24sept26/ConveyorController.php
 *
 *  Isi checklist: app/views/partials/_shared/machines/conveyor.php
 *
 *  Route (semua lewat ACL role_permissions, lihat database/conveyor_23sept26.sql):
 *    conveyor              -> index()   daftar + ringkasan + export
 *    conveyor/view/{id}    -> view()
 *    conveyor/add          -> add()
 *    conveyor/edit/{id}    -> edit()
 *    conveyor/delete/{id}  -> delete()  (GET + csrf_token, pola phpRAD)
 *    conveyor/approve/{id} -> approve() (POST)
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencChecklistBase.php';

class ConveyorController extends GencChecklistBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "conveyor";
	}

	protected function genc_conf(){
		return array(
			'page'              => 'conveyor',
			'profile'           => 'conveyor',
			'title'             => 'Conveyor',
			'name_lower'        => 'conveyor',
			'area'              => 'Dumping Lt 4M',
			'file_slug'         => 'Conveyor',
			'user_field'        => 'user_created',
			'update_user_field' => 'user_update',
			'keterangan_contoh' => 'Contoh: sensor tertutup debu, sudah dibersihkan &mdash; atau: motor bunyi kasar, sudah lapor ke Engineering (Pak ...).',
		);
	}
}
