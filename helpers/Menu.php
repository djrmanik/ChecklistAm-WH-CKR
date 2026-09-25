<?php
/**
 * Menu Items
 * All Project Menu
 * @category  Menu List
 */

class Menu{
	
	
			public static $navbarsideleft = array(
		array(
			'path' => 'home', 
			'label' => 'Home', 
			'icon' => '<i class="fa fa-home "></i>'
		),
		
		array(
			'path' => '/', 
			'label' => 'Autonomous Maintenance', 
			'icon' => '<i class="fa fa-cog "></i>','submenu' => array(
		array(
			'path' => 'palletmover', 
			'label' => 'Pallet Mover & Stacker', 
			'icon' => ''
		), // [GENC-24SEP26-PALLETMOVER] dulu 'Palletmover' - 4 unit Pallet Mover + 1 Pallet Stacker = tab di halaman (K7)
		
		array(
			'path' => 'forklift', 
			'label' => 'Forklift', 
			'icon' => ''
		),
		
		array(
			'path' => 'agv_table_top_lift', 
			'label' => 'AGV', 
			'icon' => ''
		), // [GENC-24SEP26-AGV] dulu submenu Agv Table Top Lift / Counterbalance -> sekarang 1 entri, 8 unit = tab di halaman (K7 + K12)
		
		array(
			'path' => 'kir3p01dp001', 
			'label' => 'Mesin Dumping', 
			'icon' => ''
		), // [GENC-24SEP26-DUMPING] dulu submenu KIR3/KIR6/KIR7 -> sekarang 1 entri, KIR6 & KIR7 = tab di halaman (K12)
		
		array(
			'path' => 'mesin_geprek', 
			'label' => 'Mesin Geprek', 
			'icon' => ''
		),
		
		array(
			'path' => 'conveyor', 
			'label' => 'Conveyor', 
			'icon' => ''
		) // [23SEP26] halaman Generasi C
	)
		),
		
		array(
			'path' => 'palletmover/approval', 
			'label' => 'Approval', 
			'icon' => '<i class="fa fa-check-square-o "></i>'
		),
		
		array(
			'path' => 'palletmover/uncompleted', 
			'label' => 'NOK History', 
			'icon' => '<i class="fa fa-cogs "></i>'
		),
		
		array(
			'path' => 'users', 
			'label' => 'Users', 
			'icon' => '<i class="fa fa-users "></i>'
		),
		
		array(
			'path' => '/', 
			'label' => 'Developer Menu', 
			'icon' => '<i class="fa fa-code-fork "></i>','submenu' => array(
		array(
			'path' => 'app_logs', 
			'label' => 'App Logs', 
			'icon' => ''
		),
		
		array(
			'path' => 'role_permissions', 
			'label' => 'Role Permissions', 
			'icon' => ''
		),
		
		array(
			'path' => 'roles', 
			'label' => 'Roles', 
			'icon' => ''
		)
	)
		),
		
		array(
			'path' => 'master_select', 
			'label' => 'Master Select', 
			'icon' => ''
		)
	);
		
	
	
			public static $no_forklift = array(
		array(
			"value" => "185E00370", 
			"label" => "185E00370", 
		),
		array(
			"value" => "131AD0216", 
			"label" => "131AD0216", 
		),
		array(
			"value" => "R2B-06835", 
			"label" => "R2B-06835", 
		),
		array(
			"value" => "131AC8606", 
			"label" => "131AC8606", 
		),
		array(
			"value" => "131AE3297", 
			"label" => "131AE3297", 
		),
		array(
			"value" => "Forklift Diesel", 
			"label" => "Forklift Diesel", 
		),);
		
			public static $kondisi = array(
		array(
			"value" => "✔️", 
			"label" => "Kondisi Baik", 
		),
		array(
			"value" => "❌", 
			"label" => "Butuh Perawatan/Perbaikan", 
		),);
		
			public static $alarm_mundur = array(
		array(
			"value" => "OK", 
			"label" => "Kondisi Baik", 
		),
		array(
			"value" => "NOK", 
			"label" => "Kondisi Tidak Baik", 
		),
		array(
			"value" => "PR", 
			"label" => "Perawatan/Perbaikan", 
		),);
		
			public static $approval = array(
		array(
			"value" => "Approve", 
			"label" => "Approve", 
		),
		array(
			"value" => "Not Approve", 
			"label" => "Not Approve", 
		),);
		
			public static $no_forklift2 = array(
		array(
			"value" => "Forklift Diesel 608FD18", 
			"label" => "Forklift Diesel 608FD18", 
		),);
		
			public static $no_palletmover = array(
		array(
			"value" => "Pallet Mover 1", 
			"label" => "Pallet Mover 1", 
		),
		array(
			"value" => "Pallet Mover 2", 
			"label" => "Pallet Mover 2", 
		),
		array(
			"value" => "Pallet Mover 3", 
			"label" => "Pallet Mover 3", 
		),
		array(
			"value" => "Pallet Mover 4", 
			"label" => "Pallet Mover 4", 
		),
		array(
			"value" => "Pallet Stacker", 
			"label" => "Pallet Stacker", 
		),);
		
			public static $garpu = array(
		array(
			"value" => "OK", 
			"label" => "Kondisi Baik", 
		),
		array(
			"value" => "NOK", 
			"label" => "Kondisi Tidak Baik", 
		),
		array(
			"value" => "PR", 
			"label" => "Perawatan", 
		),);
		
			public static $hydraulic = array(
		array(
			"value" => "OK", 
			"label" => "Kondisi Baik", 
		),
		array(
			"value" => "NOK", 
			"label" => "Kondisi Tidak Baik", 
		),
		array(
			"value" => "PR", 
			"label" => "Perawatan\Perbaikan", 
		),);
		
			public static $no_palletmover2 = array(
		array(
			"value" => "Pallet Mover 1", 
			"label" => "Pallet Mover 1", 
		),
		array(
			"value" => "Pallet Mover 2", 
			"label" => "Pallet Mover 2", 
		),
		array(
			"value" => "Pallet Mover 3", 
			"label" => "Pallet Mover 3", 
		),
		array(
			"value" => "Pallet Mover 4", 
			"label" => "Pallet Mover 4", 
		),
		array(
			"value" => "Pallet Mover 5", 
			"label" => "Pallet Mover 5", 
		),);
		
			public static $forklift_no_forklift = array(
		array(
			"value" => "Forklift 1", 
			"label" => "Forklift 1", 
		),
		array(
			"value" => "Forklift 2", 
			"label" => "Forklift 2", 
		),
		array(
			"value" => "Forklift 3", 
			"label" => "Forklift 3", 
		),
		array(
			"value" => "Forklift 4", 
			"label" => "Forklift 4", 
		),
		array(
			"value" => "Forklift 5", 
			"label" => "Forklift 5", 
		),
		array(
			"value" => "Forklift Diesel", 
			"label" => "Forklift Diesel", 
		),);
		
}