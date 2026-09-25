<?php 
/**
 * Pencarian_dokumen Page Controller
 * @category  Controller
 */
class Pencarian_dokumenController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "pencarian_dokumen";
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
		$fields = $this->fields = array("id","date_input","jenis_dokumen","nama_dokumen","lokasi_dokumen","keterangan");
		$page_error = null;
		if($formdata){
			$postdata = array();
			$fieldname = $formdata['name'];
			$fieldvalue = $formdata['value'];
			$postdata[$fieldname] = $fieldvalue;
			$postdata = $this->format_request_data($postdata);
			$this->rules_array = array(
				'date_input' => 'required',
				'jenis_dokumen' => 'required',
				'nama_dokumen' => 'required',
				'lokasi_dokumen' => 'required',
				'keterangan' => 'required',
			);
			$this->sanitize_array = array(
				'date_input' => 'sanitize_string',
				'jenis_dokumen' => 'sanitize_string',
				'nama_dokumen' => 'sanitize_string',
				'lokasi_dokumen' => 'sanitize_string',
				'keterangan' => 'sanitize_string',
			);
			$this->filter_rules = true; //filter validation rules by excluding fields not in the formdata
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("pencarian_dokumen.id", $rec_id);;
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
