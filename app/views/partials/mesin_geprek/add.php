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
                    </style></h3>
                </div>
                <div class="col-md-8 comp-grid">
                </div>
            </div>
        </div>
    </div>
    <div  class="">
        <div class="container">
            <div class="row my-auto">
                <div class="col-md-12 comp-grid">
                    <?php $this :: display_page_errors(); ?>
                    <div  class="bg-white animated fadeIn page-content">
                        <style>
                            #img {
                            width: 150px; 
                            height: 150px; 
                            }
                            #paddingimg{
                            padding: 12px;
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
                        <form id="mesin_geprek-add-form" role="form" novalidate enctype="multipart/form-data" class="form page-form form-horizontal needs-validation" action="<?php print_link("mesin_geprek/add?csrf_token=$csrf_token") ?>" method="post">
                            <div>
                                <div class="subheader">
                                    <div class="subheader-container" style="padding-bottom:20px; padding-top:20px;"><center><h4 class="text-primary">STANDAR PEMBERSIHAN (CLEANING)</h4></center>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="tatakan_jumbo_bag">Tatakan Jumbo Bag <span class="text-danger">*</span></label>
                                </div>
                                <div class="row">
                                    <div class="column" id="paddingimg">
                                        <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/TATAKAN%20JUMBO%20BAG.png" id="img">
                                        </div>
                                        <div class="column" id="table">
                                            <table border="1" cellpadding="3" width="400px">
                                                <tr>
                                                    <th>Metode</th>
                                                    <td style="width:250px">Bersihkan tatakan dengan menggunakan lap basah tanpa serat </td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Lap tanpa serat</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Bersih, Tidak berdebu</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>2'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Setiap hari diawal shift 1</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="column" id="field">
                                            <select required=""  id="ctrl-tatakan_jumbo_bag" name="tatakan_jumbo_bag"  placeholder="Pilih kondisi.."    class="custom-select" >
                                                <option value="">Pilih kondisi..</option>
                                                <?php
                                                $tatakan_jumbo_bag_options = Menu :: $garpu;
                                                if(!empty($tatakan_jumbo_bag_options)){
                                                foreach($tatakan_jumbo_bag_options as $option){
                                                $value = $option['value'];
                                                $label = $option['label'];
                                                $selected = $this->set_field_selected('tatakan_jumbo_bag', $value, "");
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
                                    <label class="control-label" for="punch">Punch <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="gambar2" id="paddingimg">
                                            <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/PUNCH.png" id="img">
                                                <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/UKURAN%20PUNCH.jpeg" id="img">
                                                </div>
                                                <div class="column" id="table">
                                                    <table border="1" cellpadding="3" width="400px">
                                                        <tr>
                                                            <th>Metode</th>
                                                            <td style="width:250px">Dilakukan pembersihan dengan lap pada punch hingga kondisi bersih serta bila perlu di beri 4WD, lihat ukuran tekanan punch pastikan ada pada ukuran tekanan normal </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Alat</th>
                                                            <td>Quiltec (lap bebas serat)+alkohol 70%</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Standard</th>
                                                            <td>Tidak berdebu,Bersih ,tidak ada bunyi , ukuran tekanan punch sensor 1= 20 cm, sensor 2= 28 cm, sensor 3 = 55 cm</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Durasi</th>
                                                            <td>2 menit</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Pelaksanaan</td>
                                                            <td>Setiap hari diawal shift 1</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="column" id="field">
                                                    <select required=""  id="ctrl-punch" name="punch"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                        <option value="">Pilih Kondisi..</option>
                                                        <?php
                                                        $punch_options = Menu :: $garpu;
                                                        if(!empty($punch_options)){
                                                        foreach($punch_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = $this->set_field_selected('punch', $value, "");
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
                                            <label class="control-label" for="body_mesin_geprek">Body Mesin Geprek <span class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="column" id="paddingimg">
                                                    <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/BODY%20MESIN.png" id="img">
                                                    </div>
                                                    <div class="column" id="table">
                                                        <table border="1" cellpadding="3" width="400px">
                                                            <tr>
                                                                <th>Metode</th>
                                                                <td style="width:250px">Dilakukan pembersihan dengan lap pada seluruh bagian body mesin hingga bersih </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Alat</th>
                                                                <td>Quiltec (lap bebas serat)+alkohol 70%</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Standard</th>
                                                                <td>body mesin press dalam kondisi bersih tidak berderbu dan tidak ada oli hydrolic yang berceceran</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Durasi</th>
                                                                <td>3 menit</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Pelaksanaan</td>
                                                                <td>Setiap hari diawal shift 1</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="column" id="field">
                                                        <select required=""  id="ctrl-body_mesin_geprek" name="body_mesin_geprek"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                            <option value="">Pilih Kondisi..</option>
                                                            <?php
                                                            $body_mesin_geprek_options = Menu :: $garpu;
                                                            if(!empty($body_mesin_geprek_options)){
                                                            foreach($body_mesin_geprek_options as $option){
                                                            $value = $option['value'];
                                                            $label = $option['label'];
                                                            $selected = $this->set_field_selected('body_mesin_geprek', $value, "");
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
                                                <label class="control-label" for="panel_hmi">Panel Hmi <span class="text-danger">*</span></label>
                                                <div class="row">
                                                    <div class="column" id="paddingimg">
                                                        <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/PANEL%20HMI.png" id="img">
                                                        </div>
                                                        <div class="column" id="table">
                                                            <table border="1" cellpadding="3" width="400px">
                                                                <tr>
                                                                    <th>Metode</th>
                                                                    <td style="width:250px">Dilakukan pembersihan dengan lap pada panel hmi hingga bersih </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Alat</th>
                                                                    <td>Quiltec (lap bebas serat)+alkohol 70%</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Standard</th>
                                                                    <td>HMI dalam kondisi hidup dan normal saat di operasionalkan</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Durasi</th>
                                                                    <td>2 menit</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pelaksanaan</td>
                                                                    <td>Setiap hari diawal shift 1</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="column" id="field">
                                                            <select required=""  id="ctrl-panel_hmi" name="panel_hmi"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                <option value="">Pilih Kondisi..</option>
                                                                <?php
                                                                $panel_hmi_options = Menu :: $garpu;
                                                                if(!empty($panel_hmi_options)){
                                                                foreach($panel_hmi_options as $option){
                                                                $value = $option['value'];
                                                                $label = $option['label'];
                                                                $selected = $this->set_field_selected('panel_hmi', $value, "");
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
                                                    <label class="control-label" for="sensor">Sensor <span class="text-danger">*</span></label>
                                                    <div class="row">
                                                        <div class="column" id="paddingimg">
                                                            <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/SENSOR%20PUNCH.png" id="img">
                                                            </div>
                                                            <div class="column" id="table">
                                                                <table border="1" cellpadding="3" width="400px">
                                                                    <tr>
                                                                        <th>Metode</th>
                                                                        <td style="width:250px">Dilakukan pembersihan dengan lap pada sensor </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Alat</th>
                                                                        <td>Quiltec (lap bebas serat)+alkohol 70%</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Standard</th>
                                                                        <td>Bersih, Tidak berdebu,Sensor berfungsi dengan baik</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Durasi</th>
                                                                        <td>2menit</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Pelaksanaan</td>
                                                                        <td>Setiap hari diawal shift 1</td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="column" id="field">
                                                                <select required=""  id="ctrl-sensor" name="sensor"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                    <option value="">Pilih Kondisi..</option>
                                                                    <?php
                                                                    $sensor_options = Menu :: $garpu;
                                                                    if(!empty($sensor_options)){
                                                                    foreach($sensor_options as $option){
                                                                    $value = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = $this->set_field_selected('sensor', $value, "");
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
                                                    <div class="subheader">
                                                        <div class="subheader-container" style="padding-bottom:20px; padding-top:20px;"><center><h4 class="text-primary">STANDAR PELUMASAN (LUBRICATING)</h4></center>
                                                        </div>
                                                    </div> 
                                                    <div class="form-group ">
                                                        <label class="control-label" for="as_punch">As Punch <span class="text-danger">*</span></label>
                                                        <div class="row">
                                                            <div class="gambar3" id="paddingimg">
                                                                <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/AS%20PUNCH.png" id="img">
                                                                    <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/BUSHING.png" id="img">
                                                                    </div>
                                                                    <div class="column" id="table">
                                                                        <table border="1" cellpadding="3" width="400px">
                                                                            <tr>
                                                                                <th>Metode</th>
                                                                                <td style="width:250px">Cek bushing pada punch tidak kering, dan tidak longgar atau goyang,  stoper sensor dipunch tidak berubah , dan tidak berbunyi  </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Alat</th>
                                                                                <td>Visual&Auditori</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Standard</th>
                                                                                <td>bushing tidak goyang, stoper tidak terdapat bunyi saat pengoperasian mesin</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Durasi</th>
                                                                                <td>2 menit</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Pelaksanaan</td>
                                                                                <td>Setiap hari diawal shift 1</td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="column" id="field">
                                                                        <select required=""  id="ctrl-as_punch" name="as_punch"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                            <option value="">Pilih Kondisi..</option>
                                                                            <?php
                                                                            $as_punch_options = Menu :: $garpu;
                                                                            if(!empty($as_punch_options)){
                                                                            foreach($as_punch_options as $option){
                                                                            $value = $option['value'];
                                                                            $label = $option['label'];
                                                                            $selected = $this->set_field_selected('as_punch', $value, "");
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
                                                                <label class="control-label" for="rantai_utama">Rantai Utama <span class="text-danger">*</span></label>
                                                                <div class="row">
                                                                    <div class="column" id="paddingimg">
                                                                        <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/RANTAI%20UTAMA.png" id="img">
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
                                                                            <select required=""  id="ctrl-rantai_utama" name="rantai_utama"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                <option value="">Pilih Kondisi..</option>
                                                                                <?php
                                                                                $rantai_utama_options = Menu :: $garpu;
                                                                                if(!empty($rantai_utama_options)){
                                                                                foreach($rantai_utama_options as $option){
                                                                                $value = $option['value'];
                                                                                $label = $option['label'];
                                                                                $selected = $this->set_field_selected('rantai_utama', $value, "");
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
                                                                <div class="subheader">
                                                                    <div class="subheader-container" style="padding-bottom:20px; padding-top:20px;"><center><h4 class="text-primary">STANDAR PENGECEKAN (INSPECTION)</h4></center>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group ">
                                                                    <label class="control-label" for="roda_tatakan_jumbobag">Roda Tatakan Jumbobag <span class="text-danger">*</span></label>
                                                                    <div class="row">
                                                                        <div class="column" id="paddingimg">
                                                                            <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/RODA%20TATAKAN.png" id="img">
                                                                            </div>
                                                                            <div class="column" id="table">
                                                                                <table border="1" cellpadding="3" width="400px">
                                                                                    <tr>
                                                                                        <th>Metode</th>
                                                                                        <td style="width:250px">Jalankan mesin, lihat kondisi roda saat dijalankan pada kondisi normal,tidak macet, tidak berkarat, tidak berbunyi kasar</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Alat</th>
                                                                                        <td>Visual&auditori</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Standard</th>
                                                                                        <td>Roda tidak berkarat dan tidak macet/susah di gerakan</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Durasi</th>
                                                                                        <td>2 menit</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Pelaksanaan</td>
                                                                                        <td>Setiap hari diawal shift 1</td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                            <div class="column" id="field">
                                                                                <select required=""  id="ctrl-roda_tatakan_jumbobag" name="roda_tatakan_jumbobag"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                    <option value="">Pilih Kondisi..</option>
                                                                                    <?php
                                                                                    $roda_tatakan_jumbobag_options = Menu :: $garpu;
                                                                                    if(!empty($roda_tatakan_jumbobag_options)){
                                                                                    foreach($roda_tatakan_jumbobag_options as $option){
                                                                                    $value = $option['value'];
                                                                                    $label = $option['label'];
                                                                                    $selected = $this->set_field_selected('roda_tatakan_jumbobag', $value, "");
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
                                                                    <label class="control-label" for="tombol_panel_kabel">Tombol Panel Kabel <span class="text-danger">*</span></label>
                                                                    <div class="row">
                                                                        <div class="column" id="paddingimg">
                                                                            <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/TOMBOL%20PANEL%20KABEL.png" id="img">
                                                                            </div>
                                                                            <div class="column" id="table">
                                                                                <table border="1" cellpadding="3" width="400px">
                                                                                    <tr>
                                                                                        <th>Metode</th>
                                                                                        <td style="width:250px">lihat seluruh bagian kabel, pastikan kabel tidak ada yang putus serta panel dalam kondisi ON, nyalakan panel dan tekan tombol sesuai fungsi,pastikan tombol bebrfungsi dengan baik </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Alat</th>
                                                                                        <td>Visual</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Standard</th>
                                                                                        <td>HMI menyala, kabel tidak ada yang putus ataupun rusak tombol berfungsi saat ditekan</td>
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
                                                                                <select required=""  id="ctrl-tombol_panel_kabel" name="tombol_panel_kabel"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                    <option value="">Pilih Kondisi..</option>
                                                                                    <?php
                                                                                    $tombol_panel_kabel_options = Menu :: $garpu;
                                                                                    if(!empty($tombol_panel_kabel_options)){
                                                                                    foreach($tombol_panel_kabel_options as $option){
                                                                                    $value = $option['value'];
                                                                                    $label = $option['label'];
                                                                                    $selected = $this->set_field_selected('tombol_panel_kabel', $value, "");
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
                                                                        <label class="control-label" for="mesin_compressor">Mesin Compressor <span class="text-danger">*</span></label>
                                                                        <div class="row">
                                                                            <div class="column" id="paddingimg">
                                                                                <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/MESIN%20DAN%20COMPRESOR.png" id="img">
                                                                                </div>
                                                                                <div class="column" id="table">
                                                                                    <table border="1" cellpadding="3" width="400px">
                                                                                        <tr>
                                                                                            <th>Metode</th>
                                                                                            <td style="width:250px">nyalakan mesin, cek tekanan hidrolik pada layar indikator,pastikan jarum indikator bergerak sesuai tekanan & mesin dalam kondisi hidup </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Alat</th>
                                                                                            <td>Visual&Auditori</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Standard</th>
                                                                                            <td>Tekanan normal,Mesin bunyi halus, Compresor berfugnsi dengan baik</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Durasi</th>
                                                                                            <td>2 menit</td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <th>Pelaksanaan</td>
                                                                                            <td>Setiap hari diawal shift 1</td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </div>
                                                                                <div class="column" id="field">
                                                                                    <select required=""  id="ctrl-mesin_compressor" name="mesin_compressor"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                        <option value="">Pilih Kondisi..</option>
                                                                                        <?php
                                                                                        $mesin_compressor_options = Menu :: $garpu;
                                                                                        if(!empty($mesin_compressor_options)){
                                                                                        foreach($mesin_compressor_options as $option){
                                                                                        $value = $option['value'];
                                                                                        $label = $option['label'];
                                                                                        $selected = $this->set_field_selected('mesin_compressor', $value, "");
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
                                                                            <label class="control-label" for="baut_body_mesin_geprek">Body Mesin Geprek <span class="text-danger">*</span></label>
                                                                            <div class="row">
                                                                                <div class="column" id="paddingimg">
                                                                                    <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/BODY%20MESIN.png" id="img">
                                                                                    </div>
                                                                                    <div class="column" id="table">
                                                                                        <table border="1" cellpadding="3" width="400px">
                                                                                            <tr>
                                                                                                <th>Metode</th>
                                                                                                <td style="width:250px">Cek baut body mesin press dengan cara goyangkan baut pastikan tidak ada yang goyang,kendor,patah,dan mur tidak terlepas dari baut </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Alat</th>
                                                                                                <td>Visual&Auditori</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Standard</th>
                                                                                                <td>Baut dalam kondisi baik tidak patah,goyang & tidak kendor</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Durasi</th>
                                                                                                <td>2 menit</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <th>Pelaksanaan</td>
                                                                                                <td>Setiap hari diawal shift 1</td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </div>
                                                                                    <div class="column" id="field">
                                                                                        <select required=""  id="ctrl-baut_body_mesin_geprek" name="baut_body_mesin_geprek"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                            <option value="">Pilih Kondisi..</option>
                                                                                            <?php
                                                                                            $baut_body_mesin_geprek_options = Menu :: $garpu;
                                                                                            if(!empty($baut_body_mesin_geprek_options)){
                                                                                            foreach($baut_body_mesin_geprek_options as $option){
                                                                                            $value = $option['value'];
                                                                                            $label = $option['label'];
                                                                                            $selected = $this->set_field_selected('baut_body_mesin_geprek', $value, "");
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
                                                                                <label class="control-label" for="putaran_tatakan_jumbobag">Putaran Tatakan Jumbobag <span class="text-danger">*</span></label>
                                                                                <div class="row">
                                                                                    <div class="column" id="paddingimg">
                                                                                        <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/Putaran%20tatakan%20jumbo%20bag.png" id="img">
                                                                                        </div>
                                                                                        <div class="column" id="table">
                                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                                <tr>
                                                                                                    <th>Metode</th>
                                                                                                    <td style="width:250px">nyalakan mesin, cek tatakan berputar normal 180 derajat, tidak macet, tidak mengeluarkan bunyi abnormal </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Alat</th>
                                                                                                    <td>Visual&Auditori</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Standard</th>
                                                                                                    <td>Motor mesin tidak mati,tatakan berputar normal 180 derajat, tidak macet, tidak mengeluarkan bunyi abnormal</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Durasi</th>
                                                                                                    <td>2 menit</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <th>Pelaksanaan</td>
                                                                                                    <td>Setiap hari diawal shift 1</td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                        <div class="column" id="field">
                                                                                            <select required=""  id="ctrl-putaran_tatakan_jumbobag" name="putaran_tatakan_jumbobag"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                                <option value="">Pilih Kondisi..</option>
                                                                                                <?php
                                                                                                $putaran_tatakan_jumbobag_options = Menu :: $garpu;
                                                                                                if(!empty($putaran_tatakan_jumbobag_options)){
                                                                                                foreach($putaran_tatakan_jumbobag_options as $option){
                                                                                                $value = $option['value'];
                                                                                                $label = $option['label'];
                                                                                                $selected = $this->set_field_selected('putaran_tatakan_jumbobag', $value, "");
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
                                                                                    <label class="control-label" for="selang_hidroplik_olimotor">Selang Hidroplik Olimotor <span class="text-danger">*</span></label>
                                                                                    <div class="row">
                                                                                        <div class="column" id="paddingimg">
                                                                                            <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/Putaran%20tatakan%20jumbo%20bag.png" id="img">
                                                                                            </div>
                                                                                            <div class="column" id="table">
                                                                                                <table border="1" cellpadding="3" width="400px">
                                                                                                    <tr>
                                                                                                        <th>Metode</th>
                                                                                                        <td style="width:250px">cek selang hydrolic dengan melihat seluruh bagian selang dan pastikan  tidak ada yang bocor </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <th>Alat</th>
                                                                                                        <td>Visuali</td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <th>Standard</th>
                                                                                                        <td>Selang tidak bocor, body selang tidak getas</td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <th>Durasi</th>
                                                                                                        <td>1 menit</td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <th>Pelaksanaan</td>
                                                                                                        <td>Setiap hari diawal shift 1</td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                            </div>
                                                                                            <div class="column" id="field">
                                                                                                <select required=""  id="ctrl-selang_hidroplik_olimotor" name="selang_hidroplik_olimotor"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                                    <option value="">Pilih Kondisi..</option>
                                                                                                    <?php
                                                                                                    $selang_hidroplik_olimotor_options = Menu :: $garpu;
                                                                                                    if(!empty($selang_hidroplik_olimotor_options)){
                                                                                                    foreach($selang_hidroplik_olimotor_options as $option){
                                                                                                    $value = $option['value'];
                                                                                                    $label = $option['label'];
                                                                                                    $selected = $this->set_field_selected('selang_hidroplik_olimotor', $value, "");
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
                                                                                        <label class="control-label" for="baut_punch">Baut Punch <span class="text-danger">*</span></label>
                                                                                        <div class="row">
                                                                                            <div class="column" id="paddingimg">
                                                                                                <img src="http://10.127.8.29/checklistamwarehouseckr/assets/images/AMMESINGEPREK/Putaran%20tatakan%20jumbo%20bag.png" id="img">
                                                                                                </div>
                                                                                                <div class="column" id="table">
                                                                                                    <table border="1" cellpadding="3" width="400px">
                                                                                                        <tr>
                                                                                                            <th>Metode</th>
                                                                                                            <td style="width:250px">goyangkan baut, pastikan baut tidak patah,tidak goyang,mur tidak terlepas dari baut </td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Alat</th>
                                                                                                            <td>Taktil&visual</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Standard</th>
                                                                                                            <td>Baut kencang dan berada di tempatnya,mur tidak terlepas dari baut</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Durasi</th>
                                                                                                            <td>2 menit</td>
                                                                                                        </tr>
                                                                                                        <tr>
                                                                                                            <th>Pelaksanaan</td>
                                                                                                            <td>Setiap hari diawal shift 1</td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </div>
                                                                                                <div class="column" id="field">
                                                                                                    <select required=""  id="ctrl-baut_punch" name="baut_punch"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                                                        <option value="">Pilih Kondisi..</option>
                                                                                                        <?php
                                                                                                        $baut_punch_options = Menu :: $garpu;
                                                                                                        if(!empty($baut_punch_options)){
                                                                                                        foreach($baut_punch_options as $option){
                                                                                                        $value = $option['value'];
                                                                                                        $label = $option['label'];
                                                                                                        $selected = $this->set_field_selected('baut_punch', $value, "");
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
                                                                                                    <label class="control-label" for="keterangan">Keterangan </label>
                                                                                                </div>
                                                                                                <div class="col-sm-8">
                                                                                                    <div class="">
                                                                                                        <textarea placeholder="Masukan keterangan.." id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo $this->set_field_value('keterangan',""); ?></textarea>
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
