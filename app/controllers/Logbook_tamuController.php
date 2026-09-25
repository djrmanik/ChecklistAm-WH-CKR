<?php 
/**
 * Logbook_tamu Page Controller
 * @category  Controller
 */
class Logbook_tamuController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "logbook_tamu";
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
		$fields = $this->fields = array("id","nama","departement_instansi","tujuan","jam_masuk","jam_keluar","approval","user_approve","date_approved","keluar","keterangan","peminjaman_apd","pengembalian_apd");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'nama' => 'required',
				'departement_instansi' => 'required',
				'tujuan' => 'required',
				'jam_masuk' => 'required',
				'jam_keluar' => 'required',
				'approval' => 'required',
				'user_approve' => 'required',
				'date_approved' => 'required',
				'keluar' => 'required',
				'keterangan' => 'required',
				'peminjaman_apd' => 'required',
				'pengembalian_apd' => 'required',
			);
			$this->sanitize_array = array(
				'nama' => 'sanitize_string',
				'departement_instansi' => 'sanitize_string',
				'tujuan' => 'sanitize_string',
				'jam_masuk' => 'sanitize_string',
				'jam_keluar' => 'sanitize_string',
				'approval' => 'sanitize_string',
				'user_approve' => 'sanitize_string',
				'date_approved' => 'sanitize_string',
				'keluar' => 'sanitize_string',
				'keterangan' => 'sanitize_string',
				'peminjaman_apd' => 'sanitize_string',
				'pengembalian_apd' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("logbook_tamu.id", $rec_id);;
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
