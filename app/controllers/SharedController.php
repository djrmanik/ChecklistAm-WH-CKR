<?php 

/**
 * SharedController Controller
 * @category  Controller / Model
 */
class SharedController extends BaseController{
	
	/**
     * forklift_charger_forklift_option_list Model Action
     * @return array
     */
	function forklift_charger_forklift_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_lampu_sign_option_list Model Action
     * @return array
     */
	function forklift_lampu_sign_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_air_accu_option_list Model Action
     * @return array
     */
	function forklift_air_accu_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_oli_hidrolik_dan_rem_option_list Model Action
     * @return array
     */
	function forklift_oli_hidrolik_dan_rem_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_apar_option_list Model Action
     * @return array
     */
	function forklift_apar_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_tuas_maju_mundur_elektrik_option_list Model Action
     * @return array
     */
	function forklift_tuas_maju_mundur_elektrik_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select ORDER BY id ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_klakson_option_list Model Action
     * @return array
     */
	function forklift_klakson_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_lampu_forklift_option_list Model Action
     * @return array
     */
	function forklift_lampu_forklift_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_tuas_naik_turun_option_list Model Action
     * @return array
     */
	function forklift_tuas_naik_turun_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_tuas_serong_atas_bawah_option_list Model Action
     * @return array
     */
	function forklift_tuas_serong_atas_bawah_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_garpu_rantai_option_list Model Action
     * @return array
     */
	function forklift_garpu_rantai_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_pedal_option_list Model Action
     * @return array
     */
	function forklift_pedal_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_roda_option_list Model Action
     * @return array
     */
	function forklift_roda_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_lampu_sign_option_list_2 Model Action
     * @return array
     */
	function forklift_lampu_sign_option_list_2(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_oli_hidrolik_dan_rem_option_list_2 Model Action
     * @return array
     */
	function forklift_oli_hidrolik_dan_rem_option_list_2(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_apar_option_list_2 Model Action
     * @return array
     */
	function forklift_apar_option_list_2(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_solar_option_list Model Action
     * @return array
     */
	function forklift_solar_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_air_radiator_option_list Model Action
     * @return array
     */
	function forklift_air_radiator_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_filter_gas_buang_option_list Model Action
     * @return array
     */
	function forklift_filter_gas_buang_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_label_uji_emisi_option_list Model Action
     * @return array
     */
	function forklift_label_uji_emisi_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_tuas_tangan_rem_option_list Model Action
     * @return array
     */
	function forklift_tuas_tangan_rem_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_sabuk_pengaman_option_list Model Action
     * @return array
     */
	function forklift_sabuk_pengaman_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * forklift_steering_option_list Model Action
     * @return array
     */
	function forklift_steering_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT value AS value,label AS label FROM master_select";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * users_user_role_id_option_list Model Action
     * @return array
     */
	function users_user_role_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT role_id AS value, role_name AS label FROM roles";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * users_email_value_exist Model Action
     * @return array
     */
	function users_email_value_exist($val){
		$db = $this->GetModel();
		$db->where("email", $val);
		$exist = $db->has("users");
		return $exist;
	}

	/**
     * users_username_value_exist Model Action
     * @return array
     */
	function users_username_value_exist($val){
		$db = $this->GetModel();
		$db->where("username", $val);
		$exist = $db->has("users");
		return $exist;
	}

	/**
     * palletmover_palletmoverno_palletmover_option_list Model Action
     * @return array
     */
	function palletmover_palletmoverno_palletmover_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT no_palletmover AS value,no_palletmover AS label FROM palletmover ORDER BY no_palletmover ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

}
