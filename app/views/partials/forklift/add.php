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
    <div  class="my-3">
        <div class="container-fluid">
            <div class="row ">
                <div class="col ">
                    <h3 class="record-title"><div class="alert">
                        <strong>Form input checklist AM ✅ </strong>
                    </div>
                    <style>
                        .alert {
                        padding: 4px;
                        background-color: DodgerBlue;
                        color: white;
                        text-align: CENTER;
                        }
                    </style>
                </h3>
            </div>
            <div class="col-md-8 comp-grid">
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
            <div class="col comp-grid">
                <?php $this :: display_page_errors(); ?>
                <div  class="bg-white animated fadeIn page-content">
                    $db->where("approval.id", $rec_id);;<style>
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
                        .gambar3 {
                        display: flex;
                        flex-direction: column;
                        padding: 15px;
                        }
                        #table2 {
                        padding-left: 15px;
                        padding-top: 25px
                        }
                        .gambar4 {
                        display: flex;
                        flex-direction: column;
                        padding: 15px;
                        }
                        #table3 {
                        padding-left: 15px;
                        padding-top: 25px
                        }
                    </style>
                    <form id="forklift-add-form" role="form" novalidate enctype="multipart/form-data" class="form page-form form-horizontal needs-validation" action="<?php print_link("forklift/add?csrf_token=$csrf_token") ?>" method="post">
                        <div>
                            <div class="form-group ">
                                <label class="control-label" for="no_forklift">No Forklift <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="">
                                            <select required=""  id="ctrl-no_forklift" name="no_forklift"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                <option value="">Pilih Kondisi ..</option>
                                                <?php
                                                $no_forklift_options = Menu :: $no_forklift;
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
                                            <input id="ctrl-jam_kerja"  value="<?php  echo $this->set_field_value('jam_kerja',""); ?>" type="number" placeholder="Masukan Jam Kerja Forklift" step="1"  required="" name="jam_kerja"  class="form-control " />
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
                                                <select required=""  id="ctrl-alarm_mundur" name="alarm_mundur"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                    <option value="">Pilih Kondisi ..</option>
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
                                                    <select required=""  id="ctrl-klakson" name="klakson"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                        <option value="">Pilih Kondisi ..</option>
                                                        <?php
                                                        $klakson_options = Menu :: $alarm_mundur;
                                                        if(!empty($klakson_options)){
                                                        foreach($klakson_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = $this->set_field_selected('klakson', $value, "");
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
                                                        <select required=""  id="ctrl-lampu_forklift" name="lampu_forklift"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                            <option value="">Pilih Kondisi ..</option>
                                                            <?php
                                                            $lampu_forklift_options = Menu :: $alarm_mundur;
                                                            if(!empty($lampu_forklift_options)){
                                                            foreach($lampu_forklift_options as $option){
                                                            $value = $option['value'];
                                                            $label = $option['label'];
                                                            $selected = $this->set_field_selected('lampu_forklift', $value, "");
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
                                                            <select required=""  id="ctrl-tuas_naik_turun" name="tuas_naik_turun"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                <option value="">Pilih Kondisi ..</option>
                                                                <?php
                                                                $tuas_naik_turun_options = Menu :: $alarm_mundur;
                                                                if(!empty($tuas_naik_turun_options)){
                                                                foreach($tuas_naik_turun_options as $option){
                                                                $value = $option['value'];
                                                                $label = $option['label'];
                                                                $selected = $this->set_field_selected('tuas_naik_turun', $value, "");
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
                                                                <select required=""  id="ctrl-tuas_serong_atas_bawah" name="tuas_serong_atas_bawah"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                    <option value="">Pilih Kondisi ..</option>
                                                                    <?php
                                                                    $tuas_serong_atas_bawah_options = Menu :: $alarm_mundur;
                                                                    if(!empty($tuas_serong_atas_bawah_options)){
                                                                    foreach($tuas_serong_atas_bawah_options as $option){
                                                                    $value = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = $this->set_field_selected('tuas_serong_atas_bawah', $value, "");
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
                                                        <label class="control-label" for="tuas_maju_mundur_elektrik">Tuas Maju Mundur Elektrik <span class="text-danger">*</span></label>
                                                        <div class="row">
                                                            <div class="column" id="paddingimg">
                                                                <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/TUAS MAJU MUNDUR FORKLIFT.jpg" id="img">
                                                                </div>
                                                                <div class="column" id="table">
                                                                    <table border="1" cellpadding="3" width="400px">
                                                                        <tr>
                                                                            <th>Metode</th>
                                                                            <td style="width:250px">Gerakan Tuas Garpu Maju/Mundur</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Alat</th>
                                                                            <td>Tuas Garpu Maju/Mundur</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Standard</th>
                                                                            <td>Tuas tidak ada kendala,Garpu Maju/Mundur berjalan lancar, dan berfungsi dengan baik</td>
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
                                                                    <select required=""  id="ctrl-tuas_maju_mundur_elektrik" name="tuas_maju_mundur_elektrik"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                        <option value="">Pilih Kondisi ..</option>
                                                                        <?php 
                                                                        $tuas_maju_mundur_elektrik_options = $comp_model -> forklift_tuas_maju_mundur_elektrik_option_list();
                                                                        if(!empty($tuas_maju_mundur_elektrik_options)){
                                                                        foreach($tuas_maju_mundur_elektrik_options as $option){
                                                                        $value = (!empty($option['value']) ? $option['value'] : null);
                                                                        $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                        $selected = $this->set_field_selected('tuas_maju_mundur_elektrik',$value, "");
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
                                                                            <select required=""  id="ctrl-garpu_rantai" name="garpu_rantai"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                <option value="">Pilih Kondisi ..</option>
                                                                                <?php
                                                                                $garpu_rantai_options = Menu :: $alarm_mundur;
                                                                                if(!empty($garpu_rantai_options)){
                                                                                foreach($garpu_rantai_options as $option){
                                                                                $value = $option['value'];
                                                                                $label = $option['label'];
                                                                                $selected = $this->set_field_selected('garpu_rantai', $value, "");
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
                                                                                <select required=""  id="ctrl-pedal" name="pedal"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                    <option value="">Pilih Kondisi ..</option>
                                                                                    <?php
                                                                                    $pedal_options = Menu :: $alarm_mundur;
                                                                                    if(!empty($pedal_options)){
                                                                                    foreach($pedal_options as $option){
                                                                                    $value = $option['value'];
                                                                                    $label = $option['label'];
                                                                                    $selected = $this->set_field_selected('pedal', $value, "");
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
                                                                                    <select required=""  id="ctrl-roda" name="roda"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                        <option value="">Pilih Kondisi ..</option>
                                                                                        <?php
                                                                                        $roda_options = Menu :: $alarm_mundur;
                                                                                        if(!empty($roda_options)){
                                                                                        foreach($roda_options as $option){
                                                                                        $value = $option['value'];
                                                                                        $label = $option['label'];
                                                                                        $selected = $this->set_field_selected('roda', $value, "");
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
                                                                                        <select required=""  id="ctrl-lampu_sign" name="lampu_sign"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                            <option value="">Pilih Kondisi ..</option>
                                                                                            <?php 
                                                                                            $lampu_sign_options = $comp_model -> forklift_lampu_sign_option_list();
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
                                                                                <label class="control-label" for="air_accu">Air Accu <span class="text-danger">*</span></label>
                                                                                <div class="row">
                                                                                    <div class="column" id="paddingimg">
                                                                                        <img src="http://10.127.12.130/warehousecikarang/assets/images/CEKLIS%20AM/AIR ACCU FORKLIFT.jpg" id="img">
                                                                                        </div>
                                                                                        <div class="column" id="table">
                                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                                <tr>
                                                                                                    <th>Metode</th>
                                                                                                    <td style="width:250px">Dicek</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Alat</th>
                                                                                                    <td>Visual Control, indikator air accu pada forklift</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Standard</th>
                                                                                                    <td>Air accu terisi sesuai kebutuhan</td>
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
                                                                                            <select required=""  id="ctrl-air_accu" name="air_accu"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                                <option value="">Pilih Kondisi ..</option>
                                                                                                <?php 
                                                                                                $air_accu_options = $comp_model -> forklift_air_accu_option_list();
                                                                                                if(!empty($air_accu_options)){
                                                                                                foreach($air_accu_options as $option){
                                                                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                $selected = $this->set_field_selected('air_accu',$value, "");
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
                                                                                                <select required=""  id="ctrl-oli_hidrolik_dan_rem" name="oli_hidrolik_dan_rem"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                                    <option value="">Pilih Kondisi ..</option>
                                                                                                    <?php 
                                                                                                    $oli_hidrolik_dan_rem_options = $comp_model -> forklift_oli_hidrolik_dan_rem_option_list();
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
                                                                                                    <select required=""  id="ctrl-apar" name="apar"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                                        <option value="">Pilih Kondisi ..</option>
                                                                                                        <?php 
                                                                                                        $apar_options = $comp_model -> forklift_apar_option_list();
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
                                                                                            <label class="control-label" for="charger_forklift">Charger Forklift <span class="text-danger">*</span></label>
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
                                                                                                                <td>Kelistrikan normal, berfungsi dalam pengisian baterai, Layar dan indikator charger menyala</td>
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
                                                                                                        <select required=""  id="ctrl-charger_forklift" name="charger_forklift"  placeholder="Select a value ..."    class="custom-select" >
                                                                                                            <option value="">Select a value ...</option>
                                                                                                            <?php 
                                                                                                            $charger_forklift_options = $comp_model -> forklift_charger_forklift_option_list();
                                                                                                            if(!empty($charger_forklift_options)){
                                                                                                            foreach($charger_forklift_options as $option){
                                                                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                                                                            $selected = $this->set_field_selected('charger_forklift',$value, "");
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
                                                                                        <!-- [23SEP26] Input Steering DITAMBAHKAN. Controller add() mewajibkan field ini
                                                                                             (rules_array), tapi dulu input-nya tidak ada di form -> validasi selalu
                                                                                             gagal dan data tidak pernah tersimpan. Pilihan = Menu::$alarm_mundur
                                                                                             (OK / NOK / PR), sama dengan item electric lainnya. Tabel Metode/Alat/
                                                                                             Standard belum ada isinya dari SPV, jadi sengaja belum ditampilkan. -->
                                                                                        <div class="form-group ">
                                                                                            <label class="control-label" for="steering">Steering <span class="text-danger">*</span></label>
                                                                                            <div class="row">
                                                                                                <div class="column" id="field">
                                                                                                    <select required=""  id="ctrl-steering" name="steering"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                                        <option value="">Pilih Kondisi ..</option>
                                                                                                        <?php
                                                                                                        $steering_options = Menu :: $alarm_mundur;
                                                                                                        if(!empty($steering_options)){
                                                                                                        foreach($steering_options as $option){
                                                                                                        $value = $option['value'];
                                                                                                        $label = $option['label'];
                                                                                                        $selected = $this->set_field_selected('steering', $value, "");
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
                                                                                        <!-- [23SEP26] Input Spion DITAMBAHKAN. Controller add() mewajibkan field ini
                                                                                             (rules_array), tapi dulu input-nya tidak ada di form -> validasi selalu
                                                                                             gagal dan data tidak pernah tersimpan. Pilihan = Menu::$alarm_mundur
                                                                                             (OK / NOK / PR), sama dengan item electric lainnya. Tabel Metode/Alat/
                                                                                             Standard belum ada isinya dari SPV, jadi sengaja belum ditampilkan. -->
                                                                                        <div class="form-group ">
                                                                                            <label class="control-label" for="spion">Spion <span class="text-danger">*</span></label>
                                                                                            <div class="row">
                                                                                                <div class="column" id="field">
                                                                                                    <select required=""  id="ctrl-spion" name="spion"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                                        <option value="">Pilih Kondisi ..</option>
                                                                                                        <?php
                                                                                                        $spion_options = Menu :: $alarm_mundur;
                                                                                                        if(!empty($spion_options)){
                                                                                                        foreach($spion_options as $option){
                                                                                                        $value = $option['value'];
                                                                                                        $label = $option['label'];
                                                                                                        $selected = $this->set_field_selected('spion', $value, "");
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
                                                                                            <div class="row">
                                                                                                <div class="col-sm-4">
                                                                                                    <label class="control-label" for="kondisi">Butuh Perawatan/Perbaikan <span class="text-danger">*</span></label>
                                                                                                </div>
                                                                                                <div class="col-sm-8">
                                                                                                    <div class="">
                                                                                                        <?php
                                                                                                        $kondisi_options = Menu :: $kondisi;
                                                                                                        if(!empty($kondisi_options)){
                                                                                                        foreach($kondisi_options as $option){
                                                                                                        $value = $option['value'];
                                                                                                        $label = $option['label'];
                                                                                                        //check if current option is checked option
                                                                                                        $checked = $this->set_field_checked('kondisi', $value, "");
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
                                                                                                <label class="control-label" for="keterangan">Keterangan </label>
                                                                                                <div class="row">
                                                                                                    <div class="col-sm-12">
                                                                                                        <div class="">
                                                                                                            <textarea placeholder="Masukan Keterangan" id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo $this->set_field_value('keterangan',""); ?></textarea>
                                                                                                            <!--<div class="invalid-feedback animated bounceIn text-center">Please enter text</div>-->
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
