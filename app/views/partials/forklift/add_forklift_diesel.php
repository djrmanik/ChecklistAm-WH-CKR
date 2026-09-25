<?php
$comp_model = new SharedController;
$page_element_id = "add-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$show_header = $this->show_header;
$view_title = $this->view_title;
$redirect_to = $this->redirect_to;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="add"  data-display-type="" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">Add New Forklift</h4>
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
                        <style>
                            #img {
                            width: 150px; 
                            height: 150px; 
                            }
                            #paddingimg{
                            padding: 15px;
                            }
                            #table {
                            padding-left: 25px; 
                            padding-top: 15px;
                            }
                            #field {
                            padding-left: 50px; 
                            padding-top: 70px;
                            width: 300px;
                            }
                            .gambar2 {
                            display: flex;
                            flex-direction: column;
                            padding: 15px;
                            }
                            #table1 {
                            padding-left: 15px;
                            padding-top: 25px
                            }
                        </style>
                        <form id="forklift-add_forklift_diesel-form" role="form" novalidate enctype="multipart/form-data" class="form page-form form-horizontal needs-validation" action="<?php print_link("forklift/add_forklift_diesel?csrf_token=$csrf_token") ?>" method="post">
                            <div>
                                <div class="form-group ">
                                    <label class="control-label" for="no_forklift">No Forklift <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="">
                                                <select required=""  id="ctrl-no_forklift" name="no_forklift"  placeholder="Select a value ..."    class="custom-select" >
                                                    <option value="">Select a value ...</option>
                                                    <?php
                                                    $no_forklift_options = Menu :: $no_forklift2;
                                                    if(!empty($no_forklift_options)){
                                                    foreach($no_forklift_options as $option){
                                                    $value = $option['value'];
                                                    $label = $option['label'];
                                                    $selected = $this->set_field_selected('no_forklift', $value, "");
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
                                <div class="subheader">
                                    <div class="subheader-container" style="padding-bottom:20px; padding-top:20px;"><center><h4 class="text-primary">STANDAR PEMERIKSAAN (INSPECTION)</h4></center>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="jam_kerja">Jam Kerja <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="">
                                                <input id="ctrl-jam_kerja"  value="<?php  echo $this->set_field_value('jam_kerja',""); ?>" type="text" placeholder="Enter Jam Kerja"  required="" name="jam_kerja"  class="form-control " />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label" for="alarm_mundur">Alarm Mundur <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="column" id="paddingimg">
                                                <img src="10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/alarm mundur forklift.jpg" id="img">
                                                </div>
                                                <div class="column" id="table">
                                                    <table border="1" cellpadding="3" width="400px">
                                                        <tr>
                                                            <th>Metode</th>
                                                            <td style="width:250px">Gerakan tuas belakang forklift untuk mundur,perhatikan suara alarm mundur dalam keadaan oke/tidak </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Alat</th>
                                                            <td>Tuas Belakang Forklift, Audio Control</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Standard</th>
                                                            <td>Bersuara Jelas</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Durasi</th>
                                                            <td>1'</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Pelaksanaan</td>
                                                            <td>Setiap hari diawal shift 1</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="column" id="field">
                                                    <select required=""  id="ctrl-alarm_mundur" name="alarm_mundur"  placeholder="Select a value ..."    class="custom-select" >
                                                        <option value="">Select a value ...</option>
                                                        <?php
                                                        $alarm_mundur_options = Menu :: $alarm_mundur;
                                                        if(!empty($alarm_mundur_options)){
                                                        foreach($alarm_mundur_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = $this->set_field_selected('alarm_mundur', $value, "");
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
                                        <div class="form-group ">
                                            <label class="control-label" for="klakson">Klakson <span class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="column" id="paddingimg">
                                                    <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/klakson.jpg" id="img">
                                                    </div>
                                                    <div class="column" id="table">
                                                        <table border="1" cellpadding="3" width="400px">
                                                            <tr>
                                                                <th>Metode</th>
                                                                <td style="width:250px">Tekan Tombol Klakson, Dicek</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Alat</th>
                                                                <td>Tombol Klakson, Audio Control</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Standard</th>
                                                                <td>Bersuara Jelas</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Durasi</th>
                                                                <td>1'</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Pelaksanaan</td>
                                                                <td>Setiap hari diawal shift 1</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="column" id="field">
                                                        <select required=""  id="ctrl-klakson" name="klakson"  placeholder="Select a value ..."    class="custom-select" >
                                                            <option value="">Select a value ...</option>
                                                            <?php 
                                                            $klakson_options = $comp_model -> forklift_klakson_option_list();
                                                            if(!empty($klakson_options)){
                                                            foreach($klakson_options as $option){
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            $selected = $this->set_field_selected('klakson',$value, "");
                                                            ?>
                                                            <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                <?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                            }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group ">
                                                <label class="control-label" for="lampu_forklift">Lampu Forklift <span class="text-danger">*</span></label>
                                                <div class="row">
                                                    <div class="column" id="paddingimg">
                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/lampu forklift.jpg" id="img">
                                                        </div>
                                                        <div class="column" id="table">
                                                            <table border="1" cellpadding="3" width="400px">
                                                                <tr>
                                                                    <th>Metode</th>
                                                                    <td style="width:250px">Tekan Tombol Lampu, Dicek</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Alat</th>
                                                                    <td>Tombol Lampu, Visual Control</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Standard</th>
                                                                    <td>Lampu menyala terang</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Durasi</th>
                                                                    <td>1'</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pelaksanaan</td>
                                                                    <td>Setiap hari diawal shift 1</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="column" id="field">
                                                            <select required=""  id="ctrl-lampu_forklift" name="lampu_forklift"  placeholder="Select a value ..."    class="custom-select" >
                                                                <option value="">Select a value ...</option>
                                                                <?php 
                                                                $lampu_forklift_options = $comp_model -> forklift_lampu_forklift_option_list();
                                                                if(!empty($lampu_forklift_options)){
                                                                foreach($lampu_forklift_options as $option){
                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                $selected = $this->set_field_selected('lampu_forklift',$value, "");
                                                                ?>
                                                                <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                    <?php echo $label; ?>
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
                                                <label class="control-label" for="tuas_naik_turun">Tuas Naik Turun <span class="text-danger">*</span></label>
                                                <div class="row">
                                                    <div class="column" id="paddingimg">
                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/Tuas Garpu Naik Turun.jpg" id="img">
                                                        </div>
                                                        <div class="column" id="table">
                                                            <table border="1" cellpadding="3" width="400px">
                                                                <tr>
                                                                    <th>Metode</th>
                                                                    <td style="width:250px">Gerakan Tuas Garpu Naik/Turun</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Alat</th>
                                                                    <td>Tuas Garpu Naik/Turun, Visual Control</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Standard</th>
                                                                    <td>Tuas tidak ada kendala, Garpu Naik/Turun berjalan lancar</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Durasi</th>
                                                                    <td>1'</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pelaksanaan</td>
                                                                    <td>Setiap hari diawal shift 1</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="column" id="field">
                                                            <select required=""  id="ctrl-tuas_naik_turun" name="tuas_naik_turun"  placeholder="Select a value ..."    class="custom-select" >
                                                                <option value="">Select a value ...</option>
                                                                <?php 
                                                                $tuas_naik_turun_options = $comp_model -> forklift_tuas_naik_turun_option_list();
                                                                if(!empty($tuas_naik_turun_options)){
                                                                foreach($tuas_naik_turun_options as $option){
                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                $selected = $this->set_field_selected('tuas_naik_turun',$value, "");
                                                                ?>
                                                                <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                    <?php echo $label; ?>
                                                                </option>
                                                                <?php
                                                                }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group ">
                                                    <label class="control-label" for="tuas_serong_atas_bawah">Tuas Serong Atas Bawah <span class="text-danger">*</span></label>
                                                    <div class="row">
                                                        <div class="column" id="paddingimg">
                                                            <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/TUAS SERONG ATAS BAWAH.jpg" id="img">
                                                            </div>
                                                            <div class="column" id="table">
                                                                <table border="1" cellpadding="3" width="400px">
                                                                    <tr>
                                                                        <th>Metode</th>
                                                                        <td style="width:250px">Gerakan Tuas Garpu Serong Atas/Bawah, pastikan tuas bergerak sesuai arahan</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Alat</th>
                                                                        <td>Tuas Garpu Serong Atas/Bawah, Visual Control</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Standard</th>
                                                                        <td>Tuas tidak ada kendala, Garpu Serong Atas/Bawah berjalan lancar</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Durasi</th>
                                                                        <td>1'</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Pelaksanaan</td>
                                                                        <td>Setiap hari diawal shift 1</td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="column" id="field">
                                                                <select required=""  id="ctrl-tuas_serong_atas_bawah" name="tuas_serong_atas_bawah"  placeholder="Select a value ..."    class="custom-select" >
                                                                    <option value="">Select a value ...</option>
                                                                    <?php 
                                                                    $tuas_serong_atas_bawah_options = $comp_model -> forklift_tuas_serong_atas_bawah_option_list();
                                                                    if(!empty($tuas_serong_atas_bawah_options)){
                                                                    foreach($tuas_serong_atas_bawah_options as $option){
                                                                    $value = (!empty($option['value']) ? $option['value'] : null);
                                                                    $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                    $selected = $this->set_field_selected('tuas_serong_atas_bawah',$value, "");
                                                                    ?>
                                                                    <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                        <?php echo $label; ?>
                                                                    </option>
                                                                    <?php
                                                                    }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group ">
                                                        <label class="control-label" for="garpu_rantai">Garpu Rantai <span class="text-danger">*</span></label>
                                                        <div class="row">
                                                            <div class="gambar2" id="paddingimg">
                                                                <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/GARPU FORKLIFT.jpg" id="img">
                                                                    <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/RANTAI FORKLIFT.jpg" id="img">
                                                                    </div>
                                                                    <div class="column" id="table1">
                                                                        <table border="1" cellpadding="3" width="400px">
                                                                            <tr>
                                                                                <th>Metode</th>
                                                                                <td style="width:250px">Dicek</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Alat</th>
                                                                                <td>Visual Control</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Standard</th>
                                                                                <td>Garpu dan rantai dalam kondisi yang baik, berjalan lancar, tidak ada retak, gompal, patah, putus</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Durasi</th>
                                                                                <td>1'</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Pelaksanaan</td>
                                                                                <td>Setiap hari diawal shift 1</td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="column" id="field">
                                                                        <select required=""  id="ctrl-garpu_rantai" name="garpu_rantai"  placeholder="Select a value ..."    class="custom-select" >
                                                                            <option value="">Select a value ...</option>
                                                                            <?php 
                                                                            $garpu_rantai_options = $comp_model -> forklift_garpu_rantai_option_list();
                                                                            if(!empty($garpu_rantai_options)){
                                                                            foreach($garpu_rantai_options as $option){
                                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                            $selected = $this->set_field_selected('garpu_rantai',$value, "");
                                                                            ?>
                                                                            <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                <?php echo $label; ?>
                                                                            </option>
                                                                            <?php
                                                                            }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group ">
                                                                <label class="control-label" for="pedal">Pedal <span class="text-danger">*</span></label>
                                                                <div class="row">
                                                                    <div class="column" id="paddingimg">
                                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/PEDAL GAS DAN REM FORKLIFT.jpg" id="img">
                                                                        </div>
                                                                        <div class="column" id="table">
                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                <tr>
                                                                                    <th>Metode</th>
                                                                                    <td style="width:250px">Tekan Pedal Gas & Rem</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Alat</th>
                                                                                    <td>"Pedal Gas & Rem"</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Standard</th>
                                                                                    <td>Pedal gas dalam kondisi baik,lancar, forklift dapat berjalan sesuai. Pedal Rem dalam kondisi baik, lancar, forklift berhenti saat pedal rem di tekan</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Durasi</th>
                                                                                    <td>1'</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Pelaksanaan</td>
                                                                                    <td>Setiap hari diawal shift 1"</td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                        <div class="column" id="field">
                                                                            <select required=""  id="ctrl-pedal" name="pedal"  placeholder="Select a value ..."    class="custom-select" >
                                                                                <option value="">Select a value ...</option>
                                                                                <?php 
                                                                                $pedal_options = $comp_model -> forklift_pedal_option_list();
                                                                                if(!empty($pedal_options)){
                                                                                foreach($pedal_options as $option){
                                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                $selected = $this->set_field_selected('pedal',$value, "");
                                                                                ?>
                                                                                <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                    <?php echo $label; ?>
                                                                                </option>
                                                                                <?php
                                                                                }
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group ">
                                                                    <label class="control-label" for="roda">Roda <span class="text-danger">*</span></label>
                                                                    <div class="row">
                                                                        <div class="column" id="paddingimg">
                                                                            <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/RODA FORKLIFT.jpg" id="img">
                                                                            </div>
                                                                            <div class="column" id="table">
                                                                                <table border="1" cellpadding="3" width="400px">
                                                                                    <tr>
                                                                                        <th>Metode</th>
                                                                                        <td style="width:250px">Dicek</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Alat</th>
                                                                                        <td>Visual Control</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Standard</th>
                                                                                        <td>Tidak retak, dapat berjalan dengan baik</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Durasi</th>
                                                                                        <td>1'</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Pelaksanaan</td>
                                                                                        <td>Setiap hari diawal shift 1</td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                            <div class="column" id="field">
                                                                                <select required=""  id="ctrl-roda" name="roda"  placeholder="Select a value ..."    class="custom-select" >
                                                                                    <option value="">Select a value ...</option>
                                                                                    <?php 
                                                                                    $roda_options = $comp_model -> forklift_roda_option_list();
                                                                                    if(!empty($roda_options)){
                                                                                    foreach($roda_options as $option){
                                                                                    $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                    $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                    $selected = $this->set_field_selected('roda',$value, "");
                                                                                    ?>
                                                                                    <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                        <?php echo $label; ?>
                                                                                    </option>
                                                                                    <?php
                                                                                    }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group ">
                                                                        <label class="control-label" for="lampu_sign">Lampu Sign <span class="text-danger">*</span></label>
                                                                        <div class="row">
                                                                            <div class="column" id="paddingimg">
                                                                                <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/lampu sign.jpg" id="img">
                                                                                </div>
                                                                                <div class="column" id="table">
                                                                                    <table border="1" cellpadding="3" width="400px">
                                                                                        <tr>
                                                                                            <th>Metode</th>
                                                                                            <td style="width:250px">Tekan Tombol Lampu Sein</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Alat</th>
                                                                                            <td>Tombol Lampu Sein, Visual Control</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Standard</th>
                                                                                            <td>Lampu Menyala Terang</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Durasi</th>
                                                                                            <td>1'</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Pelaksanaan</td>
                                                                                            <td>Setiap hari diawal shift 1</td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </div>
                                                                                <div class="column" id="field">
                                                                                    <select required=""  id="ctrl-lampu_sign" name="lampu_sign"  placeholder="Select a value ..."    class="custom-select" >
                                                                                        <option value="">Select a value ...</option>
                                                                                        <?php 
                                                                                        $lampu_sign_options = $comp_model -> forklift_lampu_sign_option_list_2();
                                                                                        if(!empty($lampu_sign_options)){
                                                                                        foreach($lampu_sign_options as $option){
                                                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                        $selected = $this->set_field_selected('lampu_sign',$value, "");
                                                                                        ?>
                                                                                        <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                            <?php echo $label; ?>
                                                                                        </option>
                                                                                        <?php
                                                                                        }
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group ">
                                                                            <label class="control-label" for="oli_hidrolik_dan_rem">Oli Hidrolik Dan Rem <span class="text-danger">*</span></label>
                                                                            <div class="row">
                                                                                <div class="column" id="paddingimg">
                                                                                    <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/OLI HIDROLIK DAN REM.jpg" id="img">
                                                                                    </div>
                                                                                    <div class="column" id="table">
                                                                                        <table border="1" cellpadding="3" width="400px">
                                                                                            <tr>
                                                                                                <th>Metode</th>
                                                                                                <td style="width:250px">Dicek</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Alat</th>
                                                                                                <td>Visual Control</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Standard</th>
                                                                                                <td>Oli dalam keadaan baik, terisi, tidak bocor</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Durasi</th>
                                                                                                <td>1'</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Pelaksanaan</td>
                                                                                                <td>Setiap hari diawal shift 1</td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </div>
                                                                                    <div class="column" id="field">
                                                                                        <select required=""  id="ctrl-oli_hidrolik_dan_rem" name="oli_hidrolik_dan_rem"  placeholder="Select a value ..."    class="custom-select" >
                                                                                            <option value="">Select a value ...</option>
                                                                                            <?php 
                                                                                            $oli_hidrolik_dan_rem_options = $comp_model -> forklift_oli_hidrolik_dan_rem_option_list_2();
                                                                                            if(!empty($oli_hidrolik_dan_rem_options)){
                                                                                            foreach($oli_hidrolik_dan_rem_options as $option){
                                                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                            $selected = $this->set_field_selected('oli_hidrolik_dan_rem',$value, "");
                                                                                            ?>
                                                                                            <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                <?php echo $label; ?>
                                                                                            </option>
                                                                                            <?php
                                                                                            }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group ">
                                                                                <label class="control-label" for="apar">Apar <span class="text-danger">*</span></label>
                                                                                <div class="row">
                                                                                    <div class="column" id="paddingimg">
                                                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/APAR FORKLIFT.jpg" id="img">
                                                                                        </div>
                                                                                        <div class="column" id="table">
                                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                                <tr>
                                                                                                    <th>Metode</th>
                                                                                                    <td style="width:250px">Dicek</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Alat</th>
                                                                                                    <td>Visual Control</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Standard</th>
                                                                                                    <td>Tidak Expired, tersegel</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Durasi</th>
                                                                                                    <td>1'</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Pelaksanaan</td>
                                                                                                    <td>Setiap hari diawal shift 1</td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                        <div class="column" id="field">
                                                                                            <select required=""  id="ctrl-apar" name="apar"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                <option value="">Select a value ...</option>
                                                                                                <?php 
                                                                                                $apar_options = $comp_model -> forklift_apar_option_list_2();
                                                                                                if(!empty($apar_options)){
                                                                                                foreach($apar_options as $option){
                                                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                $selected = $this->set_field_selected('apar',$value, "");
                                                                                                ?>
                                                                                                <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                    <?php echo $label; ?>
                                                                                                </option>
                                                                                                <?php
                                                                                                }
                                                                                                }
                                                                                                ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group ">
                                                                                    <label class="control-label" for="solar">Solar <span class="text-danger">*</span></label>
                                                                                    <div class="row">
                                                                                        <div class="gambar4" id="paddingimg">
                                                                                            <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/tempat solar dan solar.jpg" id="img">
                                                                                                <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/indikator solar forklift diesel.jpg" id="img">
                                                                                                </div>
                                                                                                <div class="column" id="table3">
                                                                                                    <table border="1" cellpadding="3" width="400px">
                                                                                                        <tr>
                                                                                                            <th>Metode</th>
                                                                                                            <td style="width:250px">Dicek</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Alat</th>
                                                                                                            <td>Indikator solar pada forklift, visual control</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Standard</th>
                                                                                                            <td>Solar terisi sesuai kebutuhan</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Durasi</th>
                                                                                                            <td>1'</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Pelaksanaan</td>
                                                                                                            <td></td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </div>
                                                                                                <div class="column" id="field">
                                                                                                    <select required=""  id="ctrl-solar" name="solar"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                        <option value="">Select a value ...</option>
                                                                                                        <?php 
                                                                                                        $solar_options = $comp_model -> forklift_solar_option_list();
                                                                                                        if(!empty($solar_options)){
                                                                                                        foreach($solar_options as $option){
                                                                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                        $selected = $this->set_field_selected('solar',$value, "");
                                                                                                        ?>
                                                                                                        <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                            <?php echo $label; ?>
                                                                                                        </option>
                                                                                                        <?php
                                                                                                        }
                                                                                                        }
                                                                                                        ?>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group ">
                                                                                            <label class="control-label" for="air_radiator">Air Radiator <span class="text-danger">*</span></label>
                                                                                            <div class="row">
                                                                                                <div class="gambar3" id="paddingimg">
                                                                                                    <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/tempat air radiator forklift diesel.jpg" id="img">
                                                                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/indikator air radiator forklift diesel.jpg" id="img">
                                                                                                        </div>
                                                                                                        <div class="column" id="table2">
                                                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                                                <tr>
                                                                                                                    <th>Metode</th>
                                                                                                                    <td style="width:250px">Dicek</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Alat</th>
                                                                                                                    <td>Indikator air radiator pada forklift, visual control</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Standard</th>
                                                                                                                    <td>Air Radiator dalam keadaan baik, terisi sesuai kebutuhan</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Durasi</th>
                                                                                                                    <td>1'</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Pelaksanaan</td>
                                                                                                                    <td></td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                        <div class="column" id="field">
                                                                                                            <select required=""  id="ctrl-air_radiator" name="air_radiator"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                <option value="">Select a value ...</option>
                                                                                                                <?php 
                                                                                                                $air_radiator_options = $comp_model -> forklift_air_radiator_option_list();
                                                                                                                if(!empty($air_radiator_options)){
                                                                                                                foreach($air_radiator_options as $option){
                                                                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                $selected = $this->set_field_selected('air_radiator',$value, "");
                                                                                                                ?>
                                                                                                                <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                    <?php echo $label; ?>
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
                                                                                                <label class="control-label" for="filter_gas_buang">Filter Gas Buang <span class="text-danger">*</span></label>
                                                                                                <div class="row">
                                                                                                    <div class="column" id="paddingimg">
                                                                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/filter gas buang forklift diesel.jpg" id="img">
                                                                                                        </div>
                                                                                                        <div class="column" id="table">
                                                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                                                <tr>
                                                                                                                    <th>Metode</th>
                                                                                                                    <td style="width:250px">Dicek</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Alat</th>
                                                                                                                    <td>Visual Control</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Standard</th>
                                                                                                                    <td>Filter bersih, tidak ada hambatan, tidak ada retakan, lubang, atau korosi</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Durasi</th>
                                                                                                                    <td>1'</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <th>Pelaksanaan</td>
                                                                                                                    <td></td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                        <div class="column" id="field">
                                                                                                            <select required=""  id="ctrl-filter_gas_buang" name="filter_gas_buang"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                <option value="">Select a value ...</option>
                                                                                                                <?php 
                                                                                                                $filter_gas_buang_options = $comp_model -> forklift_filter_gas_buang_option_list();
                                                                                                                if(!empty($filter_gas_buang_options)){
                                                                                                                foreach($filter_gas_buang_options as $option){
                                                                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                $selected = $this->set_field_selected('filter_gas_buang',$value, "");
                                                                                                                ?>
                                                                                                                <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                    <?php echo $label; ?>
                                                                                                                </option>
                                                                                                                <?php
                                                                                                                }
                                                                                                                }
                                                                                                                ?>
                                                                                                            </select>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="form-group ">
                                                                                                    <label class="control-label" for="label_uji_emisi">Label Uji Emisi <span class="text-danger">*</span></label>
                                                                                                    <div class="row">
                                                                                                        <div class="column" id="paddingimg">
                                                                                                            <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/label uji emisi forklift diesel.jpg" id="img">
                                                                                                            </div>
                                                                                                            <div class="column" id="table">
                                                                                                                <table border="1" cellpadding="3" width="400px">
                                                                                                                    <tr>
                                                                                                                        <th>Metode</th>
                                                                                                                        <td style="width:250px">Dicek</td>
                                                                                                                    </tr>
                                                                                                                    <tr>
                                                                                                                        <th>Alat</th>
                                                                                                                        <td>Visual Control</td>
                                                                                                                    </tr>
                                                                                                                    <tr>
                                                                                                                        <th>Standard</th>
                                                                                                                        <td>Label uji emosi dalam keadaan baik, tidak expired</td>
                                                                                                                    </tr>
                                                                                                                    <tr>
                                                                                                                        <th>Durasi</th>
                                                                                                                        <td></td>
                                                                                                                    </tr>
                                                                                                                    <tr>
                                                                                                                        <th>Pelaksanaan</td>
                                                                                                                        <td>1'</td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </div>
                                                                                                            <div class="column" id="field">
                                                                                                                <select required=""  id="ctrl-label_uji_emisi" name="label_uji_emisi"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                    <option value="">Select a value ...</option>
                                                                                                                    <?php 
                                                                                                                    $label_uji_emisi_options = $comp_model -> forklift_label_uji_emisi_option_list();
                                                                                                                    if(!empty($label_uji_emisi_options)){
                                                                                                                    foreach($label_uji_emisi_options as $option){
                                                                                                                    $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                    $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                    $selected = $this->set_field_selected('label_uji_emisi',$value, "");
                                                                                                                    ?>
                                                                                                                    <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                        <?php echo $label; ?>
                                                                                                                    </option>
                                                                                                                    <?php
                                                                                                                    }
                                                                                                                    }
                                                                                                                    ?>
                                                                                                                </select>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="form-group ">
                                                                                                        <label class="control-label" for="tuas_tangan_rem">Tuas Tangan Rem <span class="text-danger">*</span></label>
                                                                                                        <div class="row">
                                                                                                            <div class="column" id="paddingimg">
                                                                                                                <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/tuas rem tangan forklift diesel.jpg" id="img">
                                                                                                                </div>
                                                                                                                <div class="column" id="table">
                                                                                                                    <table border="1" cellpadding="3" width="400px">
                                                                                                                        <tr>
                                                                                                                            <th>Metode</th>
                                                                                                                            <td style="width:250px">Gerakan tuas tangan rem</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <th>Alat</th>
                                                                                                                            <td>Tuas tangan rem</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <th>Standard</th>
                                                                                                                            <td>Tuas dalam keadaan baik, lancar tidak ada hambatan,berfungsi untuk pengereman dengan baik</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <th>Durasi</th>
                                                                                                                            <td>1'</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <th>Pelaksanaan</td>
                                                                                                                            <td></td>
                                                                                                                        </tr>
                                                                                                                    </table>
                                                                                                                </div>
                                                                                                                <div class="column" id="field">
                                                                                                                    <select required=""  id="ctrl-tuas_tangan_rem" name="tuas_tangan_rem"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                        <option value="">Select a value ...</option>
                                                                                                                        <?php 
                                                                                                                        $tuas_tangan_rem_options = $comp_model -> forklift_tuas_tangan_rem_option_list();
                                                                                                                        if(!empty($tuas_tangan_rem_options)){
                                                                                                                        foreach($tuas_tangan_rem_options as $option){
                                                                                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                        $selected = $this->set_field_selected('tuas_tangan_rem',$value, "");
                                                                                                                        ?>
                                                                                                                        <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                            <?php echo $label; ?>
                                                                                                                        </option>
                                                                                                                        <?php
                                                                                                                        }
                                                                                                                        }
                                                                                                                        ?>
                                                                                                                    </select>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="form-group ">
                                                                                                            <label class="control-label" for="sabuk_pengaman">Sabuk Pengaman <span class="text-danger">*</span></label>
                                                                                                            <div class="row">
                                                                                                                <div class="column" id="paddingimg">
                                                                                                                    <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/sabuk pengaman forklift diesel.jpg" id="img">
                                                                                                                    </div>
                                                                                                                    <div class="column" id="table">
                                                                                                                        <table border="1" cellpadding="3" width="400px">
                                                                                                                            <tr>
                                                                                                                                <th>Metode</th>
                                                                                                                                <td style="width:250px">Dicek</td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <th>Alat</th>
                                                                                                                                <td>Visual Control</td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <th>Standard</th>
                                                                                                                                <td>Tidak putus, berfungsi dengan baik, lancar</td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <th>Durasi</th>
                                                                                                                                <td>1</td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <th>Pelaksanaan</td>
                                                                                                                                <td></td>
                                                                                                                            </tr>
                                                                                                                        </table>
                                                                                                                    </div>
                                                                                                                    <div class="column" id="field">
                                                                                                                        <select required=""  id="ctrl-sabuk_pengaman" name="sabuk_pengaman"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                            <option value="">Select a value ...</option>
                                                                                                                            <?php 
                                                                                                                            $sabuk_pengaman_options = $comp_model -> forklift_sabuk_pengaman_option_list();
                                                                                                                            if(!empty($sabuk_pengaman_options)){
                                                                                                                            foreach($sabuk_pengaman_options as $option){
                                                                                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                            $selected = $this->set_field_selected('sabuk_pengaman',$value, "");
                                                                                                                            ?>
                                                                                                                            <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                                <?php echo $label; ?>
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
                                                                                                                    <label class="control-label" for="steering">Steering <span class="text-danger">*</span></label>
                                                                                                                </div>
                                                                                                                <div class="col-sm-8">
                                                                                                                    <div class="">
                                                                                                                        <select required=""  id="ctrl-steering" name="steering"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                            <option value="">Select a value ...</option>
                                                                                                                            <?php 
                                                                                                                            $steering_options = $comp_model -> forklift_steering_option_list();
                                                                                                                            if(!empty($steering_options)){
                                                                                                                            foreach($steering_options as $option){
                                                                                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                            $selected = $this->set_field_selected('steering',$value, "");
                                                                                                                            ?>
                                                                                                                            <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                                <?php echo $label; ?>
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
                                                                                                                    <label class="control-label" for="spion">Kaca Spion <span class="text-danger">*</span></label>
                                                                                                                </div>
                                                                                                                <div class="col-sm-8">
                                                                                                                    <div class="">
                                                                                                                        <select required=""  id="ctrl-spion" name="spion"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                                            <option value="">Select a value ...</option>
                                                                                                                            <?php 
                                                                                                                            $kaca_spion_options = $comp_model -> forklift_steering_option_list(); // [23SEP26] dulu forklift_kaca_spion_option_list() - fungsi itu TIDAK ADA -> Fatal error, tombol Submit tidak pernah tampil. Sumber opsi sama dengan field diesel lain (tabel master_select).
                                                                                                                            if(!empty($kaca_spion_options)){
                                                                                                                            foreach($kaca_spion_options as $option){
                                                                                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                                            $selected = $this->set_field_selected('spion',$value, "");
                                                                                                                            ?>
                                                                                                                            <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                                                                                                <?php echo $label; ?>
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
                                                                                                                        <textarea placeholder="Enter Keterangan" id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo $this->set_field_value('keterangan',""); ?></textarea>
                                                                                                                        <!--<div class="invalid-feedback animated bounceIn text-center">Please enter text</div>-->
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
                                                                                                                            $kondisi_options = Menu :: $kondisi;
                                                                                                                            if(!empty($kondisi_options)){
                                                                                                                            foreach($kondisi_options as $option){
                                                                                                                            $value = $option['value'];
                                                                                                                            $label = $option['label'];
                                                                                                                            $selected = $this->set_field_selected('kondisi', $value, "");
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
                                                                                                    </div>
                                                                                                    <div class="form-group form-submit-btn-holder text-center mt-3">
                                                                                                        <div class="form-ajax-status"></div>
                                                                                                        <button class="btn btn-primary" type="submit">
                                                                                                            Submit
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
