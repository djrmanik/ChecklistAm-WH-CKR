<?php
/**
 * ============================================================================
 *  Kir7p01dp001Controller            [GENC-24SEP26-DUMPING] Generasi C, 24 Sep 2026
 *
 *  Mesin Dumping KIR7P01DP001 - tab "KIR7" di halaman Mesin Dumping.
 *  UI/UX sama dengan Conveyor & Mesin Geprek. Semua logika di:
 *      app/controllers/_base/GencDumpingBase.php     (bagian khusus 3 mesin dumping)
 *      app/controllers/_base/GencChecklistBase.php   (cetakan semua mesin Gen C)
 *  tampilan: app/views/partials/_shared/genc/{list,add,view}.php
 *  isi checklist (15 item, foto): app/views/partials/_shared/machines/dumping.php
 *
 *  Controller Generasi B lama (phpRAD) disimpan utuh di
 *  _backup_genc_24sept26/Kir7p01dp001Controller.php. Cara balik: HANDOVER_TERKINI.md.
 *
 *  Route:
 *    kir7p01dp001              -> index()   daftar + ringkasan + export (PDF/Print/Word = report lama, tidak berubah)
 *    kir7p01dp001/view/{id}    -> view()
 *    kir7p01dp001/add          -> add()
 *    kir7p01dp001/edit/{id}    -> edit()
 *    kir7p01dp001/delete/{id}  -> delete()  (GET + csrf_token)
 *    kir7p01dp001/approve/{id} -> approve() (POST)  <- ACL baru, database/genc_mesin_dumping_24sept26.sql
 *    kir7p01dp001/editfield    -> ditolak (edit sebaris lama)
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencDumpingBase.php';

class Kir7p01dp001Controller extends GencDumpingBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "kir7p01dp001";
	}

	protected function dumping_code(){
		return 'KIR7';
	}
}
