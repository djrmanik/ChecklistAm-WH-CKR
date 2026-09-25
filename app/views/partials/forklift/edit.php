<?php
$comp_model = new SharedController;
$page_element_id = "edit-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$data = $this->view_data;
//$rec_id = $data['__tableprimarykey'];
$page_id = $this->route->page_id;
$show_header = $this->show_header;
$view_title = $this->view_title;
$redirect_to = $this->redirect_to;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="edit"  data-display-type="" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">Edit  Forklift</h4>
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
                                                <select required=""  id="ctrl-no_forklift" name="no_forklift"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                    <option value="">Pilih Kondisi ..</option>
                                                    <?php
                                                    $no_forklift_options = Menu :: $no_forklift;
                                                    $field_value = $data['no_forklift'];
                                                    if(!empty($no_forklift_options)){
                                                    foreach($no_forklift_options as $option){
                                                    $value = $option['value'];
                                                    $label = $option['label'];
                                                    $selected = ( $value == $field_value ? 'selected' : null );
                                                    ?>
                                                    <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                        <?php echo $label ?>
                                                    </option>                                   
                                                    <?php
                                                    }
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
                                                <input id="ctrl-jam_kerja"  value="<?php  echo $data['jam_kerja']; ?>" type="number" placeholder="Masukan Jam Kerja Forklift" step="1"  required="" name="jam_kerja"  class="form-control " />
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
                                                    <select required=""  id="ctrl-alarm_mundur" name="alarm_mundur"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $alarm_mundur_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['alarm_mundur'];
                                                        if(!empty($alarm_mundur_options)){
                                                        foreach($alarm_mundur_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-klakson" name="klakson"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $klakson_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['klakson'];
                                                        if(!empty($klakson_options)){
                                                        foreach($klakson_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-lampu_forklift" name="lampu_forklift"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $lampu_forklift_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['lampu_forklift'];
                                                        if(!empty($lampu_forklift_options)){
                                                        foreach($lampu_forklift_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-tuas_naik_turun" name="tuas_naik_turun"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $tuas_naik_turun_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['tuas_naik_turun'];
                                                        if(!empty($tuas_naik_turun_options)){
                                                        foreach($tuas_naik_turun_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-tuas_serong_atas_bawah" name="tuas_serong_atas_bawah"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $tuas_serong_atas_bawah_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['tuas_serong_atas_bawah'];
                                                        if(!empty($tuas_serong_atas_bawah_options)){
                                                        foreach($tuas_serong_atas_bawah_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                <label class="control-label" for="tuas_maju_mundur_elektrik">Tuas Maju Mundur Elektrik <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="">
                                                    <select required=""  id="ctrl-tuas_maju_mundur_elektrik" name="tuas_maju_mundur_elektrik"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $rec = $data['tuas_maju_mundur_elektrik'];
                                                        $tuas_maju_mundur_elektrik_options = $comp_model -> forklift_tuas_maju_mundur_elektrik_option_list();
                                                        if(!empty($tuas_maju_mundur_elektrik_options)){
                                                        foreach($tuas_maju_mundur_elektrik_options as $option){
                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                        $selected = ( $value == $rec ? 'selected' : null );
                                                        ?>
                                                        <option 
                                                            <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $label; ?>
                                                        </option>
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-garpu_rantai" name="garpu_rantai"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $garpu_rantai_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['garpu_rantai'];
                                                        if(!empty($garpu_rantai_options)){
                                                        foreach($garpu_rantai_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-pedal" name="pedal"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $pedal_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['pedal'];
                                                        if(!empty($pedal_options)){
                                                        foreach($pedal_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-roda" name="roda"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $roda_options = Menu :: $alarm_mundur;
                                                        $field_value = $data['roda'];
                                                        if(!empty($roda_options)){
                                                        foreach($roda_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = ( $value == $field_value ? 'selected' : null );
                                                        ?>
                                                        <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                            <?php echo $label ?>
                                                        </option>                                   
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-lampu_sign" name="lampu_sign"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $rec = $data['lampu_sign'];
                                                        $lampu_sign_options = $comp_model -> forklift_lampu_sign_option_list();
                                                        if(!empty($lampu_sign_options)){
                                                        foreach($lampu_sign_options as $option){
                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                        $selected = ( $value == $rec ? 'selected' : null );
                                                        ?>
                                                        <option 
                                                            <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $label; ?>
                                                        </option>
                                                        <?php
                                                        }
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
                                                <label class="control-label" for="air_accu">Air Accu <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="">
                                                    <select required=""  id="ctrl-air_accu" name="air_accu"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $rec = $data['air_accu'];
                                                        $air_accu_options = $comp_model -> forklift_air_accu_option_list();
                                                        if(!empty($air_accu_options)){
                                                        foreach($air_accu_options as $option){
                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                        $selected = ( $value == $rec ? 'selected' : null );
                                                        ?>
                                                        <option 
                                                            <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $label; ?>
                                                        </option>
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-oli_hidrolik_dan_rem" name="oli_hidrolik_dan_rem"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $rec = $data['oli_hidrolik_dan_rem'];
                                                        $oli_hidrolik_dan_rem_options = $comp_model -> forklift_oli_hidrolik_dan_rem_option_list();
                                                        if(!empty($oli_hidrolik_dan_rem_options)){
                                                        foreach($oli_hidrolik_dan_rem_options as $option){
                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                        $selected = ( $value == $rec ? 'selected' : null );
                                                        ?>
                                                        <option 
                                                            <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $label; ?>
                                                        </option>
                                                        <?php
                                                        }
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
                                                    <select required=""  id="ctrl-apar" name="apar"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $rec = $data['apar'];
                                                        $apar_options = $comp_model -> forklift_apar_option_list();
                                                        if(!empty($apar_options)){
                                                        foreach($apar_options as $option){
                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                        $selected = ( $value == $rec ? 'selected' : null );
                                                        ?>
                                                        <option 
                                                            <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $label; ?>
                                                        </option>
                                                        <?php
                                                        }
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
                                                <label class="control-label" for="charger_forklift">Charger Forklift <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="">
                                                    <select required=""  id="ctrl-charger_forklift" name="charger_forklift"  placeholder="Select a value ..."    class="custom-select" >
                                                        <option value="">Select a value ...</option>
                                                        <?php
                                                        $rec = $data['charger_forklift'];
                                                        $charger_forklift_options = $comp_model -> forklift_charger_forklift_option_list();
                                                        if(!empty($charger_forklift_options)){
                                                        foreach($charger_forklift_options as $option){
                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                        $selected = ( $value == $rec ? 'selected' : null );
                                                        ?>
                                                        <option 
                                                            <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $label; ?>
                                                        </option>
                                                        <?php
                                                        }
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
                                                <label class="control-label" for="kondisi">Butuh Perawatan/Perbaikan <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="">
                                                    <?php
                                                    $kondisi_options = Menu :: $kondisi;
                                                    $field_value = $data['kondisi'];
                                                    if(!empty($kondisi_options)){
                                                    foreach($kondisi_options as $option){
                                                    $value = $option['value'];
                                                    $label = $option['label'];
                                                    //check if value is among checked options
                                                    $checked = $this->check_form_field_checked($field_value, $value);
                                                    ?>
                                                    <label class="custom-control custom-radio custom-control-inline">
                                                        <input id="ctrl-kondisi" class="custom-control-input" <?php echo $checked ?>  value="<?php echo $value ?>" type="radio" required=""   name="kondisi" />
                                                            <span class="custom-control-label"><?php echo $label ?></span>
                                                        </label>
                                                        <?php
                                                        }
                                                        }
                                                        ?>
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
                                                        <textarea placeholder="Masukan Keterangan" id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo $data['keterangan']; ?></textarea>
                                                        <!--<div class="invalid-feedback animated bounceIn text-center">Please enter text</div>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- [23SEP26] Spion: dulu input teks bebas -> sekarang dropdown OK/NOK/PR seperti form tambah. Steering: ditambahkan (dulu tidak bisa diedit). -->
                                        <div class="form-group ">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label class="control-label" for="steering">Steering <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="">
                                                        <select required=""  id="ctrl-steering" name="steering"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                            <option value="">Pilih Kondisi ..</option>
                                                            <?php
                                                            $steering_options = Menu :: $alarm_mundur;
                                                            $field_value = (isset($data['steering']) ? $data['steering'] : '');
                                                            $steering_found = false;
                                                            foreach($steering_options as $option){
                                                            $value = $option['value'];
                                                            $label = $option['label'];
                                                            $selected = ( $value == $field_value ? 'selected' : null );
                                                            if($selected){ $steering_found = true; }
                                                            ?>
                                                            <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </option>
                                                            <?php
                                                            }
                                                            // Nilai lama di luar OK/NOK/PR tetap ditampilkan (tidak hilang diam-diam)
                                                            if(!$steering_found && $field_value !== ''){
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
                                                    <label class="control-label" for="spion">Spion <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="">
                                                        <select required=""  id="ctrl-spion" name="spion"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                            <option value="">Pilih Kondisi ..</option>
                                                            <?php
                                                            $spion_options = Menu :: $alarm_mundur;
                                                            $field_value = (isset($data['spion']) ? $data['spion'] : '');
                                                            $spion_found = false;
                                                            foreach($spion_options as $option){
                                                            $value = $option['value'];
                                                            $label = $option['label'];
                                                            $selected = ( $value == $field_value ? 'selected' : null );
                                                            if($selected){ $spion_found = true; }
                                                            ?>
                                                            <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </option>
                                                            <?php
                                                            }
                                                            // Nilai lama di luar OK/NOK/PR tetap ditampilkan (tidak hilang diam-diam)
                                                            if(!$spion_found && $field_value !== ''){
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
