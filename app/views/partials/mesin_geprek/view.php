<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("mesin_geprek/add");
$can_edit = ACL::is_allowed("mesin_geprek/edit");
$can_view = ACL::is_allowed("mesin_geprek/view");
$can_delete = ACL::is_allowed("mesin_geprek/delete");
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
                    <h4 class="record-title">View  Mesin Geprek</h4>
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
                                    <tr  class="td-tatakan_jumbo_bag">
                                        <th class="title"> Tatakan Jumbo Bag: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['tatakan_jumbo_bag']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="tatakan_jumbo_bag" 
                                                data-title="Pilih kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['tatakan_jumbo_bag']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-punch">
                                        <th class="title"> Punch: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['punch']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="punch" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['punch']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-body_mesin_geprek">
                                        <th class="title"> Body Mesin Geprek: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['body_mesin_geprek']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="body_mesin_geprek" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['body_mesin_geprek']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-panel_hmi">
                                        <th class="title"> Panel Hmi: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['panel_hmi']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="panel_hmi" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
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
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['sensor']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="sensor" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['sensor']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-as_punch">
                                        <th class="title"> As Punch: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['as_punch']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="as_punch" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['as_punch']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-rantai_utama">
                                        <th class="title"> Rantai Utama: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['rantai_utama']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="rantai_utama" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['rantai_utama']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-roda_tatakan_jumbobag">
                                        <th class="title"> Roda Tatakan Jumbobag: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['roda_tatakan_jumbobag']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="roda_tatakan_jumbobag" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['roda_tatakan_jumbobag']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-tombol_panel_kabel">
                                        <th class="title"> Tombol Panel Kabel: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['tombol_panel_kabel']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="tombol_panel_kabel" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['tombol_panel_kabel']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-mesin_compressor">
                                        <th class="title"> Mesin Compressor: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['mesin_compressor']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="mesin_compressor" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['mesin_compressor']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-baut_body_mesin_geprek">
                                        <th class="title"> Baut Body Mesin Geprek: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['baut_body_mesin_geprek']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="baut_body_mesin_geprek" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['baut_body_mesin_geprek']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-putaran_tatakan_jumbobag">
                                        <th class="title"> Putaran Tatakan Jumbobag: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['putaran_tatakan_jumbobag']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="putaran_tatakan_jumbobag" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['putaran_tatakan_jumbobag']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-selang_hidroplik_olimotor">
                                        <th class="title"> Selang Hidroplik Olimotor: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['selang_hidroplik_olimotor']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="selang_hidroplik_olimotor" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['selang_hidroplik_olimotor']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-baut_punch">
                                        <th class="title"> Baut Punch: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $garpu); ?>' 
                                                data-value="<?php echo $data['baut_punch']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="baut_punch" 
                                                data-title="Pilih Kondisi.." 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="select" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['baut_punch']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-user_created">
                                        <th class="title"> User Created: </th>
                                        <td class="value"> <?php echo $data['user_created']; ?></td>
                                    </tr>
                                    <tr  class="td-date_created">
                                        <th class="title"> Date Created: </th>
                                        <td class="value"> <?php echo $data['date_created']; ?></td>
                                    </tr>
                                    <tr  class="td-approval">
                                        <th class="title"> Approval: </th>
                                        <td class="value"> <?php echo $data['approval']; ?></td>
                                    </tr>
                                    <tr  class="td-keterangan">
                                        <th class="title"> Keterangan: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="keterangan" 
                                                data-title="Masukan keterangan.." 
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
                                    <tr  class="td-user_approve">
                                        <th class="title"> User Approve: </th>
                                        <td class="value"> <?php echo $data['user_approve']; ?></td>
                                    </tr>
                                    <tr  class="td-kondisi">
                                        <th class="title"> Kondisi: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $kondisi); ?>' 
                                                data-value="<?php echo $data['kondisi']; ?>" 
                                                data-pk="<?php echo $data['id'] ?>" 
                                                data-url="<?php print_link("mesin_geprek/editfield/" . urlencode($data['id'])); ?>" 
                                                data-name="kondisi" 
                                                data-title="Enter Kondisi" 
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
                                                <a class="btn btn-sm btn-info"  href="<?php print_link("mesin_geprek/edit/$rec_id"); ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <?php } ?>
                                                <?php if($can_delete){ ?>
                                                <a class="btn btn-sm btn-danger record-delete-btn mx-1"  href="<?php print_link("mesin_geprek/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>" data-prompt-msg="Are you sure you want to delete this record?" data-display-style="modal">
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
