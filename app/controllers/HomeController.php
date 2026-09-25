<?php 
/**
 * Home Page Controller - DASHBOARD GENERASI C             [GENC-24SEP26-SHELL]
 * Dulu: halaman kosong bertuliskan "Dashboard". Sekarang: status checklist HARI INI
 * semua mesin/unit yang boleh dibuka role ini + angka bulan berjalan.
 * Data: app/controllers/_base/GencHubReader.php (aturan hitung = cetakan mesin masing-masing).
 * Cadangan: _backup_genc_24sept26/HomeController_sebelum-shell.php
 * @category  Controller
 */
require_once __DIR__ . '/_base/GencHubReader.php';

class HomeController extends SecureController{
	/**
     * Index Action
     * @return View
     */
	function index(){
		$reader = new GencHubReader();
		$data = $reader->collect();
		$data['can_approval'] = ACL::is_allowed('palletmover/approval');
		$data['can_nok']      = ACL::is_allowed('palletmover/uncompleted');
		$this->view->page_title = "Home";
		$this->render_view("home/index.php", $data, "main_layout.php");
	}
}
