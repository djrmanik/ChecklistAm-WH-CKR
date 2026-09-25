<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("forklift/add");
$can_edit = ACL::is_allowed("forklift/edit");
$can_view = ACL::is_allowed("forklift/view");
$can_delete = ACL::is_allowed("forklift/delete");
?>
<?php
$comp_model = new SharedController;
$page_element_id = "view-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
//Page Data Information from Controller
$data = $this->view_data;
//$rec_id = $data['__tableprimarykey'];
$page_id = $this->route->page_id; //Page id from url
$view_title = $this->view_title;
$show_header = $this->show_header;
$show_edit_btn = $this->show_edit_btn;
$show_delete_btn = $this->show_delete_btn;
$show_export_btn = $this->show_export_btn;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="view"  data-display-type="table" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">View  Forklift</h4>
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
                    <div  class="card animated fadeIn page-content">
                        <?php
                        $counter = 0;
                        if(!empty($data)){
                        $rec_id = (!empty($data['id']) ? urlencode($data['id']) : null);
                        $counter++;
                        ?>
                        <div id="page-report-body" class="">
                            <table class="table table-hover table-borderless table-striped">
                                <!-- Table Body Start -->
                                <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">
                                    <tr  class="td-date_created">
                                        <th class="title"> Date Created: </th>
                                        <td class="value"> <?php echo $data['date_created']; ?></td>
                                    </tr>
                                    <tr  class="td-user_created">
                                        <th class="title"> User Created: </th>
                                        <td class="value"> <?php echo $data['user_created']; ?></td>
                                    </tr>
                                    <tr  class="td-no_forklift">
                                        <th class="title"> No Forklift: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $no_forklift); ?>' 
                                                data-value="<?php echo $data['no_forklift']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="no_forklift" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['no_forklift']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-jam_kerja">
                                        <th class="title"> Jam Kerja: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['jam_kerja']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="jam_kerja" 
                                                data-title="Masukan Jam Kerja Forklift" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="number" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['jam_kerja']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-alarm_mundur">
                                        <th class="title"> Alarm Mundur: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['alarm_mundur']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="alarm_mundur" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['alarm_mundur']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-klakson">
                                        <th class="title"> Klakson: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['klakson']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="klakson" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['klakson']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-lampu_forklift">
                                        <th class="title"> Lampu Forklift: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['lampu_forklift']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="lampu_forklift" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['lampu_forklift']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-tuas_naik_turun">
                                        <th class="title"> Tuas Naik Turun: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['tuas_naik_turun']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="tuas_naik_turun" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['tuas_naik_turun']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-tuas_serong_atas_bawah">
                                        <th class="title"> Tuas Serong Atas Bawah: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['tuas_serong_atas_bawah']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="tuas_serong_atas_bawah" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['tuas_serong_atas_bawah']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-garpu_rantai">
                                        <th class="title"> Garpu Rantai: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['garpu_rantai']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="garpu_rantai" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['garpu_rantai']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-pedal">
                                        <th class="title"> Pedal: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['pedal']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="pedal" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['pedal']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-roda">
                                        <th class="title"> Roda: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $alarm_mundur); ?>' 
                                                data-value="<?php echo $data['roda']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="roda" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['roda']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-lampu_sign">
                                        <th class="title"> Lampu Sign: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php print_link('api/json/forklift_lampu_sign_option_list'); ?>' 
                                                data-value="<?php echo $data['lampu_sign']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="lampu_sign" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['lampu_sign']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-air_accu">
                                        <th class="title"> Air Accu: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php print_link('api/json/forklift_air_accu_option_list'); ?>' 
                                                data-value="<?php echo $data['air_accu']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="air_accu" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['air_accu']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-oli_hidrolik_dan_rem">
                                        <th class="title"> Oli Hidrolik Dan Rem: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php print_link('api/json/forklift_oli_hidrolik_dan_rem_option_list'); ?>' 
                                                data-value="<?php echo $data['oli_hidrolik_dan_rem']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="oli_hidrolik_dan_rem" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['oli_hidrolik_dan_rem']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-apar">
                                        <th class="title"> Apar: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php print_link('api/json/forklift_apar_option_list'); ?>' 
                                                data-value="<?php echo $data['apar']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="apar" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['apar']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-tuas_maju_mundur_elektrik">
                                        <th class="title"> Tuas Maju Mundur Elektrik: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php print_link('api/json/forklift_tuas_maju_mundur_elektrik_option_list'); ?>' 
                                                data-value="<?php echo $data['tuas_maju_mundur_elektrik']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="tuas_maju_mundur_elektrik" 
                                                data-title="Pilih Kondisi .." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['tuas_maju_mundur_elektrik']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-solar">
                                        <th class="title"> Solar: </th>
                                        <td class="value"> <?php echo $data['solar']; ?></td>
                                    </tr>
                                    <tr  class="td-air_radiator">
                                        <th class="title"> Air Radiator: </th>
                                        <td class="value"> <?php echo $data['air_radiator']; ?></td>
                                    </tr>
                                    <tr  class="td-filter_gas_buang">
                                        <th class="title"> Filter Gas Buang: </th>
                                        <td class="value"> <?php echo $data['filter_gas_buang']; ?></td>
                                    </tr>
                                    <tr  class="td-label_uji_emisi">
                                        <th class="title"> Label Uji Emisi: </th>
                                        <td class="value"> <?php echo $data['label_uji_emisi']; ?></td>
                                    </tr>
                                    <tr  class="td-tuas_tangan_rem">
                                        <th class="title"> Tuas Tangan Rem: </th>
                                        <td class="value"> <?php echo $data['tuas_tangan_rem']; ?></td>
                                    </tr>
                                    <tr  class="td-sabuk_pengaman">
                                        <th class="title"> Sabuk Pengaman: </th>
                                        <td class="value"> <?php echo $data['sabuk_pengaman']; ?></td>
                                    </tr>
                                    <tr  class="td-charger_forklift">
                                        <th class="title"> Charger Forklift: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php print_link('api/json/forklift_charger_forklift_option_list'); ?>' 
                                                data-value="<?php echo $data['charger_forklift']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="charger_forklift" 
                                                data-title="Select a value ..." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['charger_forklift']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-kondisi">
                                        <th class="title"> Kondisi: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $kondisi); ?>' 
                                                data-value="<?php echo $data['kondisi']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="kondisi" 
                                                data-title="Enter Butuh Perawatan/Perbaikan" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="radiolist" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['kondisi']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-keterangan">
                                        <th class="title"> Keterangan: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="keterangan" 
                                                data-title="Masukan Keterangan" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="textarea" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['keterangan']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-approval">
                                        <th class="title"> Approval: </th>
                                        <td class="value"> <?php echo $data['approval']; ?></td>
                                    </tr>
                                    <tr  class="td-user_approve">
                                        <th class="title"> User Approve: </th>
                                        <td class="value"> <?php echo $data['user_approve']; ?></td>
                                    </tr>
                                    <tr  class="td-date_update">
                                        <th class="title"> Date Update: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['date_update']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="date_update" 
                                                data-title="Enter Date Update" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['date_update']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-user_update">
                                        <th class="title"> User Update: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['user_update']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="user_update" 
                                                data-title="Enter User Update" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['user_update']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-steering">
                                        <th class="title"> Steering: </th>
                                        <td class="value"> <?php echo $data['steering']; ?></td>
                                    </tr>
                                    <tr  class="td-spion">
                                        <th class="title"> Spion: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['spion']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="spion" 
                                                data-title="Enter Spion" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['spion']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <!-- Table Body End -->
                            </table>
                        </div>
                        <div class="p-3 d-flex">
                            <div class="dropup export-btn-holder mx-1">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-save"></i> Export
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <?php $export_print_link = $this->set_current_page_link(array('format' => 'print')); ?>
                                    <a class="dropdown-item export-link-btn" data-format="print" href="<?php print_link($export_print_link); ?>" target="_blank">
                                        <img src="<?php print_link('assets/images/print.png') ?>" class="mr-2" /> PRINT
                                        </a>
                                        <?php $export_pdf_link = $this->set_current_page_link(array('format' => 'pdf')); ?>
                                        <a class="dropdown-item export-link-btn" data-format="pdf" href="<?php print_link($export_pdf_link); ?>" target="_blank">
                                            <img src="<?php print_link('assets/images/pdf.png') ?>" class="mr-2" /> PDF
                                            </a>
                                            <?php $export_word_link = $this->set_current_page_link(array('format' => 'word')); ?>
                                            <a class="dropdown-item export-link-btn" data-format="word" href="<?php print_link($export_word_link); ?>" target="_blank">
                                                <img src="<?php print_link('assets/images/doc.png') ?>" class="mr-2" /> WORD
                                                </a>
                                                <?php $export_csv_link = $this->set_current_page_link(array('format' => 'csv')); ?>
                                                <a class="dropdown-item export-link-btn" data-format="csv" href="<?php print_link($export_csv_link); ?>" target="_blank">
                                                    <img src="<?php print_link('assets/images/csv.png') ?>" class="mr-2" /> CSV
                                                    </a>
                                                    <?php $export_excel_link = $this->set_current_page_link(array('format' => 'excel')); ?>
                                                    <a class="dropdown-item export-link-btn" data-format="excel" href="<?php print_link($export_excel_link); ?>" target="_blank">
                                                        <img src="<?php print_link('assets/images/xsl.png') ?>" class="mr-2" /> EXCEL
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php if($can_edit){ ?>
                                                <a class="btn btn-sm btn-info"  href="<?php print_link("forklift/edit/$rec_id"); ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <?php } ?>
                                                <?php if($can_delete){ ?>
                                                <a class="btn btn-sm btn-danger record-delete-btn mx-1"  href="<?php print_link("forklift/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>" data-prompt-msg="Are you sure you want to delete this record?" data-display-style="modal">
                                                    <i class="fa fa-times"></i> Delete
                                                </a>
                                                <?php } ?>
                                            </div>
                                            <?php
                                            }
                                            else{
                                            ?>
                                            <!-- Empty Record Message -->
                                            <div class="text-muted p-3">
                                                <i class="fa fa-ban"></i> No Record Found
                                            </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
