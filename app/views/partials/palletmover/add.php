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
                    </style></h3>
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
                <div class="col-md-12 comp-grid">
                    <?php $this :: display_page_errors(); ?>
                    <div  class=" animated fadeIn page-content">
                        <style>
                            #img {
                            width: 150px; 
                            height: 150px; 
                            }
                            #paddingimg{
                            padding: 15px;
                            }
                            #table {
                            padding-left: 15px; 
                            padding-top: 15px;
                            }
                            #field {
                            padding-left: 20px; 
                            padding-top: 70px;
                            width: 300px;
                            }
                        </style>
                        <form id="palletmover-add-form" role="form" novalidate enctype="multipart/form-data" class="form page-form form-horizontal needs-validation" action="<?php print_link("palletmover/add?csrf_token=$csrf_token") ?>" method="post">
                            <div>
                                <div class="form-group ">
                                    <label class="control-label" for="no_palletmover">Nomor Palletmover <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="">
                                                <select required=""  id="ctrl-no_palletmover" name="no_palletmover"  placeholder="Pilih nomor pallet mover yang digunakan"    class="custom-select" >
                                                    <option value="">Pilih nomor pallet mover yang digunakan</option>
                                                    <?php
                                                    $no_palletmover_options = Menu :: $no_palletmover;
                                                    if(!empty($no_palletmover_options)){
                                                    foreach($no_palletmover_options as $option){
                                                    $value = $option['value'];
                                                    $label = $option['label'];
                                                    $selected = $this->set_field_selected('no_palletmover', $value, "");
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
                                    <label class="control-label" for="garpu">Garpu <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
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
                                                        <td>kondisi baik, berjalan lancar, tidak ada retak, gompal, patah, tidak bengkok</td>
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
                                                <select required=""  id="ctrl-garpu" name="garpu"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                    <option value="">Pilih Kondisi..</option>
                                                    <?php
                                                    $garpu_options = Menu :: $garpu;
                                                    if(!empty($garpu_options)){
                                                    foreach($garpu_options as $option){
                                                    $value = $option['value'];
                                                    $label = $option['label'];
                                                    $selected = $this->set_field_selected('garpu', $value, "");
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
                                        <label class="control-label" for="handle">Handle <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="column" id="paddingimg">
                                                <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
                                                </div>
                                                <div class="column" id="table">
                                                    <table border="1" cellpadding="3" width="400px">
                                                        <tr>
                                                            <th>Metode</th>
                                                            <td style="width:250px">Dicek</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Alat</th>
                                                            <td>Handle, Visual Control</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Standard</th>
                                                            <td>Berfungsi dengan baik dan tidak ada kendala, tidak longgar</td>
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
                                                    <select required=""  id="ctrl-handle" name="handle"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                        <option value="">Pilih Kondisi..</option>
                                                        <?php
                                                        $handle_options = Menu :: $garpu;
                                                        if(!empty($handle_options)){
                                                        foreach($handle_options as $option){
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        $selected = $this->set_field_selected('handle', $value, "");
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
                                                    <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
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
                                                                <td>Tidak retak, tidak longgar, dapat berjalan dengan baik</td>
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
                                                        <select required=""  id="ctrl-roda" name="roda"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                            <option value="">Pilih Kondisi..</option>
                                                            <?php
                                                            $roda_options = Menu :: $garpu;
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
                                                <label class="control-label" for="hydraulic">Hydraulic <span class="text-danger">*</span></label>
                                                <div class="row">
                                                    <div class="column" id="paddingimg">
                                                        <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
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
                                                                    <td>Berfungsi dengan baik, tidak ada kebocoran, tidak ada hambatan pada saat proses hidrolik</td>
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
                                                            <select required=""  id="ctrl-hydraulic" name="hydraulic"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                <option value="">Pilih Kondisi..</option>
                                                                <?php
                                                                $hydraulic_options = Menu :: $hydraulic;
                                                                if(!empty($hydraulic_options)){
                                                                foreach($hydraulic_options as $option){
                                                                $value = $option['value'];
                                                                $label = $option['label'];
                                                                $selected = $this->set_field_selected('hydraulic', $value, "");
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
                                                    <label class="control-label" for="body">Body <span class="text-danger">*</span></label>
                                                    <div class="row">
                                                        <div class="column" id="paddingimg">
                                                            <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu path laptop C:\Users\HMI08\... */ ?>
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
                                                                        <td>Bersih,Tidak ada retakan, gompal</td>
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
                                                                <select required=""  id="ctrl-body" name="body"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                    <option value="">Pilih Kondisi..</option>
                                                                    <?php
                                                                    $body_options = Menu :: $hydraulic;
                                                                    if(!empty($body_options)){
                                                                    foreach($body_options as $option){
                                                                    $value = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = $this->set_field_selected('body', $value, "");
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
                                                        <label class="control-label" for="tuas">Tuas <span class="text-danger">*</span></label>
                                                        <div class="row">
                                                            <div class="column" id="paddingimg">
                                                                <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
                                                                </div>
                                                                <div class="column" id="table">
                                                                    <table border="1" cellpadding="3" width="400px">
                                                                        <tr>
                                                                            <th>Metode</th>
                                                                            <td style="width:250px">Dicek, Gerakan tuas</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Alat</th>
                                                                            <td>Tuas, visual control</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Standard</th>
                                                                            <td>Tuas berfungsi dengan baik sesuai arahan, tidak ada retakan, tidak longgar</td>
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
                                                                    <select required=""  id="ctrl-tuas" name="tuas"  placeholder="Pilih Kondisi.."    class="custom-select" >
                                                                        <option value="">Pilih Kondisi..</option>
                                                                        <?php
                                                                        $tuas_options = Menu :: $hydraulic;
                                                                        if(!empty($tuas_options)){
                                                                        foreach($tuas_options as $option){
                                                                        $value = $option['value'];
                                                                        $label = $option['label'];
                                                                        $selected = $this->set_field_selected('tuas', $value, "");
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
                                                            <label class="control-label" for="air_aki">Air Aki <span class="text-danger">*</span></label>
                                                            <div class="row">
                                                                <div class="column" id="paddingimg">
                                                                    <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
                                                                    </div>
                                                                    <div class="column" id="table">
                                                                        <table border="1" cellpadding="3" width="400px">
                                                                            <tr>
                                                                                <th>Metode</th>
                                                                                <td style="width:250px">Dicek,visual control </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Alat</th>
                                                                                <td>Indikator air aki</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Standard</th>
                                                                                <td>Indikator air aki ada pada level ketinggian yang full, pastikan bahwa air aki tidak bocor</td>
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
                                                                        <select required=""  id="ctrl-air_aki" name="air_aki"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                            <option value="">Pilih Kondisi ..</option>
                                                                            <?php
                                                                            $air_aki_options = Menu :: $alarm_mundur;
                                                                            if(!empty($air_aki_options)){
                                                                            foreach($air_aki_options as $option){
                                                                            $value = $option['value'];
                                                                            $label = $option['label'];
                                                                            $selected = $this->set_field_selected('air_aki', $value, "");
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
                                                                <label class="control-label" for="charger_palletmover">Charger Palletmover <span class="text-danger">*</span></label>
                                                                <div class="row">
                                                                    <div class="column" id="paddingimg">
                                                                        <img src="<?php print_link('assets/images/no-image-available.png'); ?>" id="img" alt="Foto belum tersedia"> <?php /* [23SEP26] dulu src kosong */ ?>
                                                                        </div>
                                                                        <div class="column" id="table">
                                                                            <table border="1" cellpadding="3" width="400px">
                                                                                <tr>
                                                                                    <th>Metode</th>
                                                                                    <td style="width:250px">Dicek,visual control </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Alat</th>
                                                                                    <td>Indikator charger</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Standard</th>
                                                                                    <td>Indikator charger menyala ketika dihubungkan ke aki/baterai, berbunyi dan melakukan pengisian baterai jika di tekan tombol power, tombol stop berfungsi ketika di tekan</td>
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
                                                                            <select required=""  id="ctrl-charger_palletmover" name="charger_palletmover"  placeholder="Pilih Kondisi .."    class="custom-select" >
                                                                                <option value="">Pilih Kondisi ..</option>
                                                                                <?php
                                                                                $charger_palletmover_options = Menu :: $garpu;
                                                                                if(!empty($charger_palletmover_options)){
                                                                                foreach($charger_palletmover_options as $option){
                                                                                $value = $option['value'];
                                                                                $label = $option['label'];
                                                                                $selected = $this->set_field_selected('charger_palletmover', $value, "");
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
                                                                    <label class="control-label" for="keterangan">Keterangan </label>
                                                                    <div class="row">
                                                                        <textarea placeholder="Enter Keterangan" id="ctrl-keterangan"  rows="5" name="keterangan" class=" form-control"><?php  echo $this->set_field_value('keterangan',""); ?></textarea>
                                                                        <!--<div class="invalid-feedback animated bounceIn text-center">Please enter text</div>-->
                                                                    </div>
                                                                </div>
                                                                <div class="form-group ">
                                                                    <div class="row">
                                                                        <div class="col-sm-4">
                                                                            <label class="control-label" for="kondisi">Bagaimana kondisi seluruh part pada pallet mover? <span class="text-danger">*</span></label>
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
