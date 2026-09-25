<?php

/**
 * Info Contoller Class
 * @category  Controller
 */

class ApiController extends BaseController
{

	/**
	 * call model action to retrieve data
	 * @return json data
	 */

	function json($action, $arg1 = null, $arg2 = null)
	{
		// [GENC-25SEP26-AUDIT] dulu: tanpa login & bisa memanggil method apa pun milik SharedController
		// TERMASUK warisan BaseController (render_view, redirect, ...). Sekarang: wajib login dan hanya
		// method yang ditulis di SharedController sendiri (daftar pilihan & cek username/email).
		if (user_login_status() != true) { render_error('Login dulu', 401); }
		$allowed = false;
		if (is_string($action) && method_exists('SharedController', $action)) {
			$rm = new ReflectionMethod('SharedController', $action);
			$allowed = $rm->isPublic() && !$rm->isStatic() && $rm->getDeclaringClass()->getName() === 'SharedController';
		}
		if (!$allowed) { render_error('Aksi tidak dikenal', 404); }
		$model = new SharedController;
		$args = array($arg1, $arg2);
		$data = call_user_func_array(array($model, $action), $args);
		render_json($data);
	}
}
