<?php 
/**
 * Barang_datang_rm Page Controller
 * @category  Controller
 */
class Barang_datang_rmController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "barang_datang_rm";
	}
	/**
     * Update single field
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function editfield($rec_id = null, $formdata = null){
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		//editable fields
		$fields = $this->fields = array("id","lokasi","type","supplier","nomor_plat","coa","nama_barang","origin","no_batch","exp_date","jumlah","wadah","baik_tidak","keterangan","user_created","date_created");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'lokasi' => 'required',
				'type' => 'required',
				'supplier' => 'required',
				'nomor_plat' => 'required',
				'coa' => 'required',
				'nama_barang' => 'required',
				'origin' => 'required',
				'no_batch' => 'required',
				'exp_date' => 'required',
				'jumlah' => 'required',
				'wadah' => 'required',
				'baik_tidak' => 'required',
				'keterangan' => 'required',
				'user_created' => 'required',
				'date_created' => 'required',
			);
			$this->sanitize_array = array(
				'lokasi' => 'sanitize_string',
				'type' => 'sanitize_string',
				'supplier' => 'sanitize_string',
				'nomor_plat' => 'sanitize_string',
				'coa' => 'sanitize_string',
				'nama_barang' => 'sanitize_string',
				'origin' => 'sanitize_string',
				'no_batch' => 'sanitize_string',
				'exp_date' => 'sanitize_string',
				'jumlah' => 'sanitize_string',
				'wadah' => 'sanitize_string',
				'baik_tidak' => 'sanitize_string',
				'keterangan' => 'sanitize_string',
				'user_created' => 'sanitize_string',
				'date_created' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("barang_datang_rm.id", $rec_id);;
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount();
				if($bool && $numRows){
					return render_json(
						array(
							'num_rows' =>$numRows,
							'rec_id' =>$rec_id,
						)
					);
				}
				else{
					if($db->getLastError()){
						$page_error = $db->getLastError();
					}
					elseif(!$numRows){
						$page_error = "No record updated";
					}
					render_error($page_error);
				}
			}
			else{
				render_error($this->view->page_error);
			}
		}
		return null;
	}
}
