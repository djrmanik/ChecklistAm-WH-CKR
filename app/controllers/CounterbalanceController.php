<?php
/**
 * ============================================================================
 *  CounterbalanceController           [GENC-24SEP26-AGV] Generasi C, 24 Sep 2026
 *
 *  AGV Counterbalance (3 unit: AGV CB 1 - 3) - tab "CB 1" ... "CB 3" di
 *  halaman AGV. UI/UX sama dengan Conveyor, Mesin Geprek, Mesin Dumping.
 *  Semua logika di:
 *      app/controllers/_base/GencAgvBase.php         (bagian khusus 2 halaman AGV)
 *      app/controllers/_base/GencChecklistBase.php   (cetakan semua mesin Gen C, termasuk unit)
 *  tampilan: app/views/partials/_shared/genc/{list,add,view}.php
 *  isi checklist (10 item) & daftar unit: app/views/partials/_shared/machines/counterbalance.php
 *
 *  Controller Generasi B lama (phpRAD) disimpan utuh di
 *  _backup_genc_24sept26/CounterbalanceController.php. Cara balik: HANDOVER_TERKINI.md.
 *
 *  Route:
 *    counterbalance?unit=agv-cb-2        -> index()   daftar + ringkasan + export per unit
 *                                           (tanpa ?unit= -> unit pertama, CB 1)
 *    counterbalance?unit=lainnya         -> record yang nomor unitnya tidak dikenal (mis. 'AGV TTL 1')
 *    counterbalance/view/{id}            -> view()
 *    counterbalance/add?unit=agv-cb-2    -> add()     (tanpa unit -> pilih unit dulu)
 *    counterbalance/edit/{id}            -> edit()
 *    counterbalance/delete/{id}          -> delete()  (GET + csrf_token)
 *    counterbalance/approve/{id}         -> approve() (POST)  <- ACL baru, database/genc_agv_24sept26.sql
 *    counterbalance/editfield            -> ditolak (edit sebaris lama)
 *  Export PDF/Print/Word: report lama, tidak berubah (?unit=<slug> / ?unit=semua).
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencAgvBase.php';

class CounterbalanceController extends GencAgvBase{

	function __construct(){
		parent::__construct();
		$this->tablename = "counterbalance";
	}

	protected function agv_conf(){
		return array(
			'profile'   => 'counterbalance',
			'title'     => 'AGV Counterbalance',
			'area'      => 'Warehouse 1 - Warehouse 2', // [FAKTA] profil report (SPV 22 Sep)
			'file_slug' => 'AGV-Counterbalance',
			'contoh'    => 'Contoh: panel HMI berdebu, sudah dilap &mdash; atau: socket kabel longgar, sudah lapor ke Engineering (Pak ...).',
		);
	}
}
