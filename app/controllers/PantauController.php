<?php
/**
 * ============================================================================
 *  PantauController            [GENC-16OKT26-PANTAU] Generasi C, 16 Okt 2026
 *
 *  Menu "Pantau" di sidebar: ringkasan SEMUA MESIN.
 *    pantau/approval  -> approval()  checklist yang menunggu approval, per unit, bulan terpilih
 *    pantau/temuan    -> temuan()    NOK History: temuan + status (belum beres / beres), per unit
 *
 *  Dulu dua halaman ini menumpang di PalletmoverController (palletmover/approval &
 *  palletmover/uncompleted, warisan menu phpRAD). Route lama tetap ada dan hanya
 *  mengarahkan ke sini, jadi bookmark & link lama tetap jalan.
 *
 *  Hak akses: role_permissions pantau/approval & pantau/temuan. Hak lama
 *  (palletmover/approval & palletmover/uncompleted) otomatis berlaku juga - lihat ACL::$alias.
 *  Data: app/controllers/_base/GencPantauReader.php. Tampilan: _shared/genc/hub.php & temuan.php.
 * ============================================================================
 */
require_once __DIR__ . '/_base/GencPantauReader.php';

class PantauController extends SecureController{

	/** Menu "Approval": semua mesin, jumlah checklist yang menunggu approval per unit. */
	function approval($fieldname = null, $fieldvalue = null){
		$reader = new GencPantauReader();
		$data = $reader->approval($this->request);
		$data['can_nok'] = ACL::is_allowed('pantau/temuan');
		$data['nok_open'] = $data['can_nok'] ? $reader->temuan_open_count() : 0;
		$this->view->page_title = 'Approval - semua mesin';
		return $this->render_view("_shared/genc/hub.php", $data);
	}

	/** Menu "NOK History": temuan semua mesin per unit + statusnya. */
	function temuan($fieldname = null, $fieldvalue = null){
		$reader = new GencPantauReader();
		$data = $reader->temuan($this->request);
		$data['can_approval'] = ACL::is_allowed('pantau/approval');
		$this->view->page_title = 'NOK History - temuan semua mesin';
		return $this->render_view("_shared/genc/temuan.php", $data);
	}

}
