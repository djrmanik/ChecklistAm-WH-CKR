<?php
/**
 * ============================================================================
 *  Agv_table_top_liftController       [GENC-24SEP26-AGV] Generasi C, 24 Sep 2026
 *
 *  AGV Table Top Lift (5 unit: AGV TTL 6 - 10) - tab "TTL 6" ... "TTL 10" di
 *  halaman AGV. UI/UX sama dengan Conveyor, Mesin Geprek, Mesin Dumping.
 *  Semua logika di:
 *      app/controllers/_base/GencAgvBase.php         (bagian khusus 2 halaman AGV)
 *      app/controllers/_base/GencChecklistBase.php   (cetakan semua mesin Gen C, termasuk unit)
 *  tampilan: app/views/partials/_shared/genc/{list,add,view}.php
 *  isi checklist (9 item) & daftar unit: app/views/partials/_shared/machines/agv_table_top_lift.php
 *
 *  Controller Generasi B lama (phpRAD) disimpan utuh di
 *  _backup_genc_24sept26/Agv_table_top_liftController.php. Cara balik: HANDOVER_TERKINI.md.
 *
 *  Route:
 *    agv_table_top_lift?unit=agv-ttl-7        -> index()   daftar + ringkasan + export per unit
 *                                                (tanpa ?unit= -> unit pertama, TTL 6)
 *    agv_table_top_lift?unit=lainnya          -> record yang nomor unitnya tidak dikenal
 *    agv_table_top_lift/view/{id}             -> view()
 *    agv_table_top_lift/add?unit=agv-ttl-7    -> add()     (tanpa unit -> pilih unit dulu)
 *    agv_table_top_lift/edit/{id}             -> edit()
 *    agv_table_top_lift/delete/{id}           -> delete()  (GET + csrf_token)
 *    agv_table_top_lift/approve/{id}          -> approve() (POST)  <- ACL baru, database/genc_agv_24sept26.sql
 *    agv_table_top_lift/editfield             -> ditolak (edit sebaris lama)
 *  Export PDF/Print/Word: report lama, tidak berubah (?unit=<slug> / ?unit=semua).
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencAgvBase.php';

class Agv_table_top_liftController extends GencAgvBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "agv_table_top_lift";
	}

	protected function agv_conf(){
		return array(
			'profile'   => 'agv_table_top_lift',
			'title'     => 'AGV Table Top Lift',
			'area'      => 'Area Bridge',              // [FAKTA] profil report (SPV 22 Sep)
			'file_slug' => 'AGV-Table-Top-Lift',
			'contoh'    => 'Contoh: lensa laser scanner berdebu, sudah dilap &mdash; atau: bumper retak, sudah lapor ke Engineering (Pak ...).',
		);
	}
}
