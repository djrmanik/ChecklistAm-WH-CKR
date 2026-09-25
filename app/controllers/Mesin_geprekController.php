<?php
/**
 * ============================================================================
 *  Mesin_geprekController                  [GENC-24SEP26] Generasi C, 24 Sep 2026
 *
 *  Halaman Mesin Geprek sekarang GENERASI C - UI/UX sama dengan Conveyor.
 *  Logika halaman ada di cetakan bersama:
 *      app/controllers/_base/GencChecklistBase.php
 *  tampilan di view bersama:
 *      app/views/partials/_shared/genc/{list,add,view}.php
 *  isi checklist (14 item, foto, standar) di:
 *      app/views/partials/_shared/machines/mesin_geprek.php
 *
 *  Controller Generasi A lama (index/add/edit/view phpRAD, approvalbtn,
 *  approval, editfield, report lama render_checklist_report*, geprek_debug_log)
 *  disimpan UTUH di _backup_genc_24sept26/Mesin_geprekController.php
 *  (di luar app/controllers supaya tidak jadi route). Cara balik: handover
 *  HANDOVER_GenC_01-geprek_24sept26.md bagian 10.
 *
 *  Route:
 *    mesin_geprek              -> index()   daftar + ringkasan + export
 *    mesin_geprek/view/{id}    -> view()
 *    mesin_geprek/add          -> add()
 *    mesin_geprek/edit/{id}    -> edit()
 *    mesin_geprek/delete/{id}  -> delete()  (GET + csrf_token)
 *    mesin_geprek/approve/{id} -> approve() (POST)   <- ACL baru, lihat
 *                                  database/genc_mesin_geprek_24sept26.sql
 *    Route lama (tetap ada supaya link/bookmark/menu lama tidak error):
 *    mesin_geprek/approval     -> diarahkan ke daftar, filter "Menunggu approval"
 *    mesin_geprek/approvalbtn/{id} -> diarahkan ke detail record (tombol Approve di sana)
 *    mesin_geprek/editfield    -> ditolak (edit sebaris lama tidak dipakai lagi)
 *
 *  Trigger DB `approval_mesin_geprek` (BEFORE INSERT) TIDAK diubah: checklist
 *  yang semua item-nya Baik otomatis Approved oleh 'PAN'. `kondisi` sekarang
 *  dihitung server, jadi trigger ini cocok dengan isi item.
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencChecklistBase.php';

class Mesin_geprekController extends GencChecklistBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "mesin_geprek";
	}

	protected function genc_conf(){
		return array(
			'page'              => 'mesin_geprek',
			'profile'           => 'mesin_geprek',
			'title'             => 'Mesin Geprek',
			'name_lower'        => 'mesin geprek',
			'area'              => '4M',
			'file_slug'         => 'Mesin-Geprek',
			'user_field'        => 'user_created',
			'update_user_field' => 'user_update',
			'keterangan_contoh' => 'Contoh: rantai utama kering, sudah dilumasi &mdash; atau: selang hidrolik rembes, sudah lapor ke Engineering (Pak ...).',
		);
	}

	// =====================================================================
	//  ROUTE LAMA (Generasi A) - diarahkan ke halaman Gen C   [GENC-24SEP26]
	// =====================================================================

	/** Halaman "Approval" lama -> daftar Gen C dengan filter Menunggu approval. */
	function approval($fieldname = null, $fieldvalue = null){
		return $this->redirect("mesin_geprek?status=menunggu");
	}

	/** Form approve lama per record -> halaman detail (tombol Approve ada di sana). */
	function approvalbtn($rec_id = null, $formdata = null){
		$id = (int) $rec_id;
		return $this->redirect($id > 0 ? "mesin_geprek/view/" . $id : "mesin_geprek?status=menunggu");
	}

	/** Edit sebaris (inline) daftar lama - tidak dipakai lagi: ubah lewat form supaya kondisi dihitung ulang. */
	function editfield($rec_id = null, $formdata = null){
		render_error("Ubah data lewat halaman Ubah checklist.");
		return null;
	}
}
