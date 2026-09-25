<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("kir6p01dp001/add");
$can_edit = ACL::is_allowed("kir6p01dp001/edit");
$can_view = ACL::is_allowed("kir6p01dp001/view");
$can_delete = ACL::is_allowed("kir6p01dp001/delete");
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
                    <h4 class="record-title">View  Kir6p01dp001</h4>
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
                                    <tr  class="td-id">
                                        <th class="title"> Id: </th>
                                        <td class="value"> <?php echo $data['id']; ?></td>
                                    </tr>
                                    <tr  class="td-iris_valve">
                                        <th class="title"> Iris Valve: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['iris_valve']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="iris_valve" 
                                                data-title="Enter Iris Valve" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['iris_valve']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-hopper_bbds">
                                        <th class="title"> Hopper Bbds: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['hopper_bbds']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="hopper_bbds" 
                                                data-title="Enter Hopper Bbds" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['hopper_bbds']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-body_mesin_dumping">
                                        <th class="title"> Body Mesin Dumping: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['body_mesin_dumping']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="body_mesin_dumping" 
                                                data-title="Enter Body Mesin Dumping" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['body_mesin_dumping']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-panel_hmi">
                                        <th class="title"> Panel Hmi: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['panel_hmi']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="panel_hmi" 
                                                data-title="Enter Panel Hmi" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['panel_hmi']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-sensor">
                                        <th class="title"> Sensor: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['sensor']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="sensor" 
                                                data-title="Enter Sensor" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['sensor']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-jumbo_bag">
                                        <th class="title"> Jumbo Bag: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['jumbo_bag']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="jumbo_bag" 
                                                data-title="Enter Jumbo Bag" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['jumbo_bag']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-baut_massage_piringannya">
                                        <th class="title"> Baut Massage Piringannya: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['baut_massage_piringannya']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="baut_massage_piringannya" 
                                                data-title="Enter Baut Massage Piringannya" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['baut_massage_piringannya']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-baut_motor_vibrator">
                                        <th class="title"> Baut Motor Vibrator: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['baut_motor_vibrator']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="baut_motor_vibrator" 
                                                data-title="Enter Baut Motor Vibrator" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['baut_motor_vibrator']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-iris_valve_inspection">
                                        <th class="title"> Iris Valve Inspection: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['iris_valve_inspection']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="iris_valve_inspection" 
                                                data-title="Enter Iris Valve Inspection" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['iris_valve_inspection']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-kabel_grounding">
                                        <th class="title"> Kabel Grounding: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['kabel_grounding']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="kabel_grounding" 
                                                data-title="Enter Kabel Grounding" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['kabel_grounding']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-jalur_udara">
                                        <th class="title"> Jalur Udara: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['jalur_udara']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="jalur_udara" 
                                                data-title="Enter Jalur Udara" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['jalur_udara']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-tuas_kran">
                                        <th class="title"> Tuas Kran: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['tuas_kran']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="tuas_kran" 
                                                data-title="Enter Tuas Kran" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['tuas_kran']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-klem_fleksibel_dust_collector">
                                        <th class="title"> Klem Fleksibel Dust Collector: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['klem_fleksibel_dust_collector']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="klem_fleksibel_dust_collector" 
                                                data-title="Enter Klem Fleksibel Dust Collector" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['klem_fleksibel_dust_collector']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-fleksibel_klem_pipa">
                                        <th class="title"> Fleksibel Klem Pipa: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['fleksibel_klem_pipa']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="fleksibel_klem_pipa" 
                                                data-title="Enter Fleksibel Klem Pipa" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['fleksibel_klem_pipa']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-seal_corong_dumping">
                                        <th class="title"> Seal Corong Dumping: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['seal_corong_dumping']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="seal_corong_dumping" 
                                                data-title="Enter Seal Corong Dumping" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['seal_corong_dumping']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-keterangan">
                                        <th class="title"> Keterangan: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['keterangan']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="keterangan" 
                                                data-title="Enter Keterangan" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['keterangan']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-date_created">
                                        <th class="title"> Date Created: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-flatpickr="{ minDate: '', maxDate: ''}" 
                                                data-value="<?php echo $data['date_created']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="date_created" 
                                                data-title="Enter Date Created" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="flatdatetimepicker" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['date_created']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-user_create">
                                        <th class="title"> User Create: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['user_create']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="user_create" 
                                                data-title="Enter User Create" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['user_create']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-date_update">
                                        <th class="title"> Date Update: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-flatpickr="{ minDate: '', maxDate: ''}" 
                                                data-value="<?php echo $data['date_update']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="date_update" 
                                                data-title="Enter Date Update" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="flatdatetimepicker" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['date_update']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-user_approve">
                                        <th class="title"> User Approve: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['user_approve']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="user_approve" 
                                                data-title="Enter User Approve" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['user_approve']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-approval">
                                        <th class="title"> Approval: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['approval']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="approval" 
                                                data-title="Enter Approval" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['approval']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-perubahan">
                                        <th class="title"> Perubahan: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['perubahan']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="perubahan" 
                                                data-title="Enter Perubahan" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['perubahan']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-user_perubah">
                                        <th class="title"> User Perubah: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['user_perubah']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="user_perubah" 
                                                data-title="Enter User Perubah" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['user_perubah']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-date_perubahan">
                                        <th class="title"> Date Perubahan: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-flatpickr="{ minDate: '', maxDate: ''}" 
                                                data-value="<?php echo $data['date_perubahan']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("kir6p01dp001/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="date_perubahan" 
                                                data-title="Enter Date Perubahan" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="flatdatetimepicker" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['date_perubahan']; ?> 
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
                                                <a class="btn btn-sm btn-info"  href="<?php print_link("kir6p01dp001/edit/$rec_id"); ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <?php } ?>
                                                <?php if($can_delete){ ?>
                                                <a class="btn btn-sm btn-danger record-delete-btn mx-1"  href="<?php print_link("kir6p01dp001/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>" data-prompt-msg="Are you sure you want to delete this record?" data-display-style="modal">
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
