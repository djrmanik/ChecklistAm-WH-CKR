<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("mesin_geprek/add");
$can_edit = ACL::is_allowed("mesin_geprek/edit");
$can_view = ACL::is_allowed("mesin_geprek/view");
$can_delete = ACL::is_allowed("mesin_geprek/delete");
?>
<?php
$comp_model = new SharedController;
$page_element_id = "list-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
//Page Data From Controller
$view_data = $this->view_data;
$records = $view_data->records;
$record_count = $view_data->record_count;
$total_records = $view_data->total_records;
$field_name = $this->route->field_name;
$field_value = $this->route->field_value;
$view_title = $this->view_title;
$show_header = $this->show_header;
$show_footer = $this->show_footer;
$show_pagination = $this->show_pagination;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="list"  data-display-type="table" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container-fluid">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title"></h4>
                </div>
                <div class="col-sm-3 ">
                    <?php if($can_add){ ?>
                    <a  class="btn btn btn-primary my-1" href="<?php print_link("mesin_geprek/add") ?>">
                        <i class="fa fa-plus"></i>                              
                        Add AM Mesin Geprek 
                    </a>
                    <?php } ?>
                </div>
                <div class="col-sm-4 ">
                    <form  class="search" action="<?php print_link('mesin_geprek'); ?>" method="get">
                        <div class="input-group">
                            <input value="<?php echo get_value('search'); ?>" class="form-control" type="text" name="search"  placeholder="Search" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12 comp-grid">
                        <div class="">
                            <!-- Page bread crumbs components-->
                            <?php
                            if(!empty($field_name) || !empty($_GET['search'])){
                            ?>
                            <hr class="sm d-block d-sm-none" />
                            <nav class="page-header-breadcrumbs mt-2" aria-label="breadcrumb">
                                <ul class="breadcrumb m-0 p-1">
                                    <?php
                                    if(!empty($field_name)){
                                    ?>
                                    <li class="breadcrumb-item">
                                        <a class="text-decoration-none" href="<?php print_link('mesin_geprek'); ?>">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <?php echo (get_value("tag") ? get_value("tag")  :  make_readable($field_name)); ?>
                                    </li>
                                    <li  class="breadcrumb-item active text-capitalize font-weight-bold">
                                        <?php echo (get_value("label") ? get_value("label")  :  make_readable(urldecode($field_value))); ?>
                                    </li>
                                    <?php 
                                    }   
                                    ?>
                                    <?php
                                    if(get_value("search")){
                                    ?>
                                    <li class="breadcrumb-item">
                                        <a class="text-decoration-none" href="<?php print_link('mesin_geprek'); ?>">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item text-capitalize">
                                        Search
                                    </li>
                                    <li  class="breadcrumb-item active text-capitalize font-weight-bold"><?php echo get_value("search"); ?></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </nav>
                            <!--End of Page bread crumbs components-->
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
        <div  class="">
            <div class="container-fluid">
                <div class="row ">
                    <div class="col-md-12 comp-grid">
                        <?php $this :: display_page_errors(); ?>
                        <div  class=" animated fadeIn page-content">
                            <div id="mesin_geprek-list-records">
                                <div id="page-report-body" class="table-responsive">
                                    <table class="table  table-striped table-sm text-left">
                                        <thead class="table-header bg-light">
                                            <tr>
                                                <?php if($can_delete){ ?>
                                                <th class="td-checkbox">
                                                    <label class="custom-control custom-checkbox custom-control-inline">
                                                        <input class="toggle-check-all custom-control-input" type="checkbox" />
                                                        <span class="custom-control-label"></span>
                                                    </label>
                                                </th>
                                                <?php } ?>
                                                <th class="td-sno">#</th>
                                                <th  class="td-tatakan_jumbo_bag"> Tatakan Jumbo Bag</th>
                                                <th  class="td-punch"> Punch</th>
                                                <th  class="td-body_mesin_geprek"> Body Mesin Geprek</th>
                                                <th  class="td-panel_hmi"> Panel Hmi</th>
                                                <th  class="td-sensor"> Sensor</th>
                                                <th  class="td-as_punch"> As Punch</th>
                                                <th  class="td-rantai_utama"> Rantai Utama</th>
                                                <th  class="td-roda_tatakan_jumbobag"> Roda Tatakan Jumbobag</th>
                                                <th  class="td-tombol_panel_kabel"> Tombol Panel Kabel</th>
                                                <th  class="td-mesin_compressor"> Mesin Compressor</th>
                                                <th  class="td-baut_body_mesin_geprek"> Baut Body Mesin Geprek</th>
                                                <th  class="td-putaran_tatakan_jumbobag"> Putaran Tatakan Jumbobag</th>
                                                <th  class="td-selang_hidroplik_olimotor"> Selang Hidroplik Olimotor</th>
                                                <th  class="td-baut_punch"> Baut Punch</th>
                                                <th  class="td-user_created"> User Created</th>
                                                <th  class="td-date_created"> Date Created</th>
                                                <th  class="td-approval"> Approval</th>
                                                <th  class="td-keterangan"> Keterangan</th>
                                                <th  class="td-user_approve"> User Approve</th>
                                                <th  class="td-kondisi"> Kondisi</th>
                                                <th class="td-btn"></th>
                                            </tr>
                                        </thead>
                                        <?php
                                        if(!empty($records)){
                                        ?>
                                        <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">
                                            <!--record-->
                                            <?php
                                            $counter = 0;
                                            foreach($records as $data){
                                            $rec_id = (!empty($data['id']) ? urlencode($data['id']) : null);
                                            $counter++;
                                            ?>
                                            <tr>
                                                <?php if($can_delete){ ?>
                                                <th class=" td-checkbox">
                                                    <label class="custom-control custom-checkbox custom-control-inline">
                                                        <input class="optioncheck custom-control-input" name="optioncheck[]" value="<?php echo $data['id'] ?>" type="checkbox" />
                                                            <span class="custom-control-label"></span>
                                                        </label>
                                                    </th>
                                                    <?php } ?>
                                                    <th class="td-sno"><?php echo $counter; ?></th>
                                                    <td class="td-tatakan_jumbo_bag">
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
                                                    <td class="td-punch">
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
                                                    <td class="td-body_mesin_geprek">
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
                                                    <td class="td-panel_hmi">
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
                                                    <td class="td-sensor">
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
                                                    <td class="td-as_punch">
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
                                                    <td class="td-rantai_utama">
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
                                                    <td class="td-roda_tatakan_jumbobag">
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
                                                    <td class="td-tombol_panel_kabel">
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
                                                    <td class="td-mesin_compressor">
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
                                                    <td class="td-baut_body_mesin_geprek">
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
                                                    <td class="td-putaran_tatakan_jumbobag">
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
                                                    <td class="td-selang_hidroplik_olimotor">
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
                                                    <td class="td-baut_punch">
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
                                                    <td class="td-user_created"> <?php echo $data['user_created']; ?></td>
                                                    <td class="td-date_created"> <?php echo $data['date_created']; ?></td>
                                                    <td class="td-approval"> <?php echo $data['approval']; ?></td>
                                                    <td class="td-keterangan">
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
                                                    <td class="td-user_approve"> <?php echo $data['user_approve']; ?></td>
                                                    <td class="td-kondisi">
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
                                                    <th class="td-btn">
                                                        <?php if($can_view){ ?>
                                                        <a class="btn btn-sm btn-success has-tooltip" title="View Record" href="<?php print_link("mesin_geprek/view/$rec_id"); ?>">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                        <?php } ?>
                                                        <?php if($can_edit){ ?>
                                                        <a class="btn btn-sm btn-info has-tooltip" title="Edit This Record" href="<?php print_link("mesin_geprek/edit/$rec_id"); ?>">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                        <?php } ?>
                                                        <?php if($can_delete){ ?>
                                                        <a class="btn btn-sm btn-danger has-tooltip record-delete-btn" title="Delete this record" href="<?php print_link("mesin_geprek/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>" data-prompt-msg="Are you sure you want to delete this record?" data-display-style="modal">
                                                            <i class="fa fa-times"></i>
                                                            Delete
                                                        </a>
                                                        <?php } ?>
                                                    </th>
                                                </tr>
                                                <?php 
                                                }
                                                ?>
                                                <!--endrecord-->
                                            </tbody>
                                            <tbody class="search-data" id="search-data-<?php echo $page_element_id; ?>"></tbody>
                                            <?php
                                            }
                                            ?>
                                        </table>
                                        <?php 
                                        if(empty($records)){
                                        ?>
                                        <h4 class="bg-light text-center border-top text-muted animated bounce  p-3">
                                            <i class="fa fa-ban"></i> No record found
                                        </h4>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                    if( $show_footer && !empty($records)){
                                    ?>
                                    <div class=" border-top mt-2">
                                        <div class="row justify-content-center">    
                                            <div class="col-md-auto justify-content-center">    
                                                <div class="p-3 d-flex justify-content-between">    
                                                    <?php if($can_delete){ ?>
                                                    <button data-prompt-msg="Are you sure you want to delete these records?" data-display-style="modal" data-url="<?php print_link("mesin_geprek/delete/{sel_ids}/?csrf_token=$csrf_token&redirect=$current_page"); ?>" class="btn btn-sm btn-danger btn-delete-selected d-none">
                                                        <i class="fa fa-times"></i> Delete Selected
                                                    </button>
                                                    <?php } ?>
                                                    <?php /* Filter periode (bulan/tahun) untuk tombol Export - penjelasan ada di file partial-nya. */ ?>
                                                    <?php include ROOT . 'app/views/partials/_shared/report_export_filter.php'; ?>
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
                                                                    </div>
                                                                </div>
                                                                <div class="col">   
                                                                    <?php
                                                                    if($show_pagination == true){
                                                                    $pager = new Pagination($total_records, $record_count);
                                                                    $pager->route = $this->route;
                                                                    $pager->show_page_count = true;
                                                                    $pager->show_record_count = true;
                                                                    $pager->show_page_limit =true;
                                                                    $pager->limit_count = $this->limit_count;
                                                                    $pager->show_page_number_list = true;
                                                                    $pager->pager_link_range=5;
                                                                    $pager->render();
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
