<?php
/**
 * [23SEP26] app/views/partials/forklift/edit_forklift_diesel.php
 *
 * Form EDIT khusus record Forklift Diesel. Dirender oleh
 * ForkliftController::edit() -> edit_diesel_record() kalau no_forklift record
 * mengandung "Diesel". Route & hak aksesnya tetap forklift/edit - tidak ada
 * route baru, tidak ada perubahan role_permissions.
 *
 * Field & sumber pilihan = sama persis dengan add_forklift_diesel.php.
 * Nilai lama yang tidak ada di daftar pilihan tetap ditampilkan & terpilih
 * (ditandai "(nilai lama)"), supaya tidak hilang diam-diam waktu disimpan.
 */
$comp_model = new SharedController;
$page_element_id = "edit-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$data = $this->view_data;
$page_id = $this->route->page_id;
$show_header = $this->show_header;
$view_title = $this->view_title;
$redirect_to = $this->redirect_to;
if(!function_exists('forklift_diesel_edit_options')){
    /** Normalisasi daftar opsi (Menu::$x atau hasil query master_select). */
    function forklift_diesel_edit_options($options){
        $out = array();
        foreach((array) $options as $option){
            $value = (isset($option['value']) && $option['value'] !== '' ? $option['value'] : null);
            if($value === null){ continue; }
            $label = (!empty($option['label']) ? $option['label'] : $value);
            $out[] = array('value' => $value, 'label' => $label);
        }
        return $out;
    }
}
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="edit"  data-display-type="" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">Edit  Forklift Diesel</h4>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
    <div  class="">
        <div class="container">
            <div class="row ">
                <div class="col-md-7 comp-grid">
                    <?php $this :: display_page_errors(); ?>
                    <div  class="bg-light p-3 animated fadeIn page-content">
                        <form novalidate  id="" role="form" enctype="multipart/form-data"  class="form page-form form-horizontal needs-validation" action="<?php print_link("forklift/edit/$page_id/?csrf_token=$csrf_token"); ?>" method="post">
                            <div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="no_forklift">No Forklift <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-no_forklift" name="no_forklift"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['no_forklift']) ? (string) $data['no_forklift'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options(Menu :: $no_forklift2) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="jam_kerja">Jam Kerja <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <input id="ctrl-jam_kerja"  value="<?php  echo htmlspecialchars((string) $data['jam_kerja'], ENT_QUOTES, 'UTF-8'); ?>" type="text" placeholder="Enter Jam Kerja"  required="" name="jam_kerja"  class="form-control " />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="alarm_mundur">Alarm Mundur <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-alarm_mundur" name="alarm_mundur"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['alarm_mundur']) ? (string) $data['alarm_mundur'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options(Menu :: $alarm_mundur) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="klakson">Klakson <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-klakson" name="klakson"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['klakson']) ? (string) $data['klakson'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_klakson_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="lampu_forklift">Lampu Forklift <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-lampu_forklift" name="lampu_forklift"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['lampu_forklift']) ? (string) $data['lampu_forklift'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_lampu_forklift_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="tuas_naik_turun">Tuas Naik Turun <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-tuas_naik_turun" name="tuas_naik_turun"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['tuas_naik_turun']) ? (string) $data['tuas_naik_turun'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_tuas_naik_turun_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="tuas_serong_atas_bawah">Tuas Serong Atas Bawah <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-tuas_serong_atas_bawah" name="tuas_serong_atas_bawah"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['tuas_serong_atas_bawah']) ? (string) $data['tuas_serong_atas_bawah'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_tuas_serong_atas_bawah_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="garpu_rantai">Garpu Rantai <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-garpu_rantai" name="garpu_rantai"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['garpu_rantai']) ? (string) $data['garpu_rantai'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_garpu_rantai_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="pedal">Pedal <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-pedal" name="pedal"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['pedal']) ? (string) $data['pedal'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_pedal_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="roda">Roda <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-roda" name="roda"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['roda']) ? (string) $data['roda'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_roda_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="lampu_sign">Lampu Sign <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-lampu_sign" name="lampu_sign"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['lampu_sign']) ? (string) $data['lampu_sign'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_lampu_sign_option_list_2()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="oli_hidrolik_dan_rem">Oli Hidrolik Dan Rem <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-oli_hidrolik_dan_rem" name="oli_hidrolik_dan_rem"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['oli_hidrolik_dan_rem']) ? (string) $data['oli_hidrolik_dan_rem'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_oli_hidrolik_dan_rem_option_list_2()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="apar">Apar <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-apar" name="apar"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['apar']) ? (string) $data['apar'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_apar_option_list_2()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="solar">Solar <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-solar" name="solar"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['solar']) ? (string) $data['solar'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_solar_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="air_radiator">Air Radiator <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-air_radiator" name="air_radiator"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['air_radiator']) ? (string) $data['air_radiator'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_air_radiator_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="filter_gas_buang">Filter Gas Buang <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-filter_gas_buang" name="filter_gas_buang"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['filter_gas_buang']) ? (string) $data['filter_gas_buang'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_filter_gas_buang_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="label_uji_emisi">Label Uji Emisi <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-label_uji_emisi" name="label_uji_emisi"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['label_uji_emisi']) ? (string) $data['label_uji_emisi'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_label_uji_emisi_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="tuas_tangan_rem">Tuas Tangan Rem <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-tuas_tangan_rem" name="tuas_tangan_rem"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['tuas_tangan_rem']) ? (string) $data['tuas_tangan_rem'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_tuas_tangan_rem_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="sabuk_pengaman">Sabuk Pengaman <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-sabuk_pengaman" name="sabuk_pengaman"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['sabuk_pengaman']) ? (string) $data['sabuk_pengaman'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_sabuk_pengaman_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="steering">Steering <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-steering" name="steering"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['steering']) ? (string) $data['steering'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_steering_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="spion">Kaca Spion <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-spion" name="spion"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['spion']) ? (string) $data['spion'] : '');
                                                    $found = false;
                                                    foreach(forklift_diesel_edit_options($comp_model -> forklift_steering_option_list()) as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    if($selected){ $found = true; }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php
                                                    }
                                                    if(!$found && $field_value !== ''){
                                                    ?>
                                                    <option selected value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?> (nilai lama)</option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="keterangan">Keterangan </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <textarea placeholder="Enter Keterangan" id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo htmlspecialchars((string) $data['keterangan'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="kondisi">Kondisi <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-kondisi" name="kondisi"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $field_value = (isset($data['kondisi']) ? (string) $data['kondisi'] : '');
                                                    foreach(Menu :: $kondisi as $option){
                                                    $selected = ( $option['value'] == $field_value ? 'selected' : null );
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $option['value']; ?>"><?php echo $option['label']; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-ajax-status"></div>
                            <div class="form-group text-center">
                                <button class="btn btn-primary" type="submit">
                                    Update
                                    <i class="fa fa-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
