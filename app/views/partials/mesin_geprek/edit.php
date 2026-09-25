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
                    <h4 class="record-title">Edit  Mesin Geprek</h4>
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
                        <form novalidate  id="" role="form" enctype="multipart/form-data"  class="form page-form form-horizontal needs-validation" action="<?php print_link("mesin_geprek/edit/$page_id/?csrf_token=$csrf_token"); ?>" method="post">
                            <div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="tatakan_jumbo_bag">Tatakan Jumbo Bag <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-tatakan_jumbo_bag" name="tatakan_jumbo_bag"  placeholder="Pilih kondisi.."    class="custom-select" >
                                                    <option value="">Pilih kondisi..</option>
                                                    <?php
                                                    $tatakan_jumbo_bag_options = Menu :: $garpu;
                                                    $field_value = $data['tatakan_jumbo_bag'];
                                                    if(!empty($tatakan_jumbo_bag_options)){
                                                    foreach($tatakan_jumbo_bag_options as $option){
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
                                            <label class="control-label" for="punch">Punch <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-punch" name="punch"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $punch_options = Menu :: $garpu;
                                                    $field_value = $data['punch'];
                                                    if(!empty($punch_options)){
                                                    foreach($punch_options as $option){
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
                                            <label class="control-label" for="body_mesin_geprek">Body Mesin Geprek <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-body_mesin_geprek" name="body_mesin_geprek"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $body_mesin_geprek_options = Menu :: $garpu;
                                                    $field_value = $data['body_mesin_geprek'];
                                                    if(!empty($body_mesin_geprek_options)){
                                                    foreach($body_mesin_geprek_options as $option){
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
                                            <label class="control-label" for="panel_hmi">Panel Hmi <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-panel_hmi" name="panel_hmi"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $panel_hmi_options = Menu :: $garpu;
                                                    $field_value = $data['panel_hmi'];
                                                    if(!empty($panel_hmi_options)){
                                                    foreach($panel_hmi_options as $option){
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
                                            <label class="control-label" for="sensor">Sensor <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-sensor" name="sensor"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $sensor_options = Menu :: $garpu;
                                                    $field_value = $data['sensor'];
                                                    if(!empty($sensor_options)){
                                                    foreach($sensor_options as $option){
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
                                            <label class="control-label" for="as_punch">As Punch <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-as_punch" name="as_punch"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $as_punch_options = Menu :: $garpu;
                                                    $field_value = $data['as_punch'];
                                                    if(!empty($as_punch_options)){
                                                    foreach($as_punch_options as $option){
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
                                            <label class="control-label" for="rantai_utama">Rantai Utama <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-rantai_utama" name="rantai_utama"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $rantai_utama_options = Menu :: $garpu;
                                                    $field_value = $data['rantai_utama'];
                                                    if(!empty($rantai_utama_options)){
                                                    foreach($rantai_utama_options as $option){
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
                                            <label class="control-label" for="roda_tatakan_jumbobag">Roda Tatakan Jumbobag <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-roda_tatakan_jumbobag" name="roda_tatakan_jumbobag"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $roda_tatakan_jumbobag_options = Menu :: $garpu;
                                                    $field_value = $data['roda_tatakan_jumbobag'];
                                                    if(!empty($roda_tatakan_jumbobag_options)){
                                                    foreach($roda_tatakan_jumbobag_options as $option){
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
                                            <label class="control-label" for="tombol_panel_kabel">Tombol Panel Kabel <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-tombol_panel_kabel" name="tombol_panel_kabel"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $tombol_panel_kabel_options = Menu :: $garpu;
                                                    $field_value = $data['tombol_panel_kabel'];
                                                    if(!empty($tombol_panel_kabel_options)){
                                                    foreach($tombol_panel_kabel_options as $option){
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
                                            <label class="control-label" for="mesin_compressor">Mesin Compressor <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-mesin_compressor" name="mesin_compressor"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $mesin_compressor_options = Menu :: $garpu;
                                                    $field_value = $data['mesin_compressor'];
                                                    if(!empty($mesin_compressor_options)){
                                                    foreach($mesin_compressor_options as $option){
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
                                            <label class="control-label" for="baut_body_mesin_geprek">Body Mesin Geprek <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-baut_body_mesin_geprek" name="baut_body_mesin_geprek"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $baut_body_mesin_geprek_options = Menu :: $garpu;
                                                    $field_value = $data['baut_body_mesin_geprek'];
                                                    if(!empty($baut_body_mesin_geprek_options)){
                                                    foreach($baut_body_mesin_geprek_options as $option){
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
                                            <label class="control-label" for="putaran_tatakan_jumbobag">Putaran Tatakan Jumbobag <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-putaran_tatakan_jumbobag" name="putaran_tatakan_jumbobag"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $putaran_tatakan_jumbobag_options = Menu :: $garpu;
                                                    $field_value = $data['putaran_tatakan_jumbobag'];
                                                    if(!empty($putaran_tatakan_jumbobag_options)){
                                                    foreach($putaran_tatakan_jumbobag_options as $option){
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
                                            <label class="control-label" for="selang_hidroplik_olimotor">Selang Hidroplik Olimotor <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-selang_hidroplik_olimotor" name="selang_hidroplik_olimotor"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $selang_hidroplik_olimotor_options = Menu :: $garpu;
                                                    $field_value = $data['selang_hidroplik_olimotor'];
                                                    if(!empty($selang_hidroplik_olimotor_options)){
                                                    foreach($selang_hidroplik_olimotor_options as $option){
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
                                            <label class="control-label" for="baut_punch">Baut Punch <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required=""  id="ctrl-baut_punch" name="baut_punch"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $baut_punch_options = Menu :: $garpu;
                                                    $field_value = $data['baut_punch'];
                                                    if(!empty($baut_punch_options)){
                                                    foreach($baut_punch_options as $option){
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
                                            <label class="control-label" for="keterangan">Keterangan </label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <textarea placeholder="Masukan keterangan.." id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo $data['keterangan']; ?></textarea>
                                                <!--<div class="invalid-feedback animated bounceIn text-center">Please enter text</div>-->
                                            </div>
                                            <small class="form-text">contoh: oli pada rantai kering, body gompal, baut goyang</small>
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
