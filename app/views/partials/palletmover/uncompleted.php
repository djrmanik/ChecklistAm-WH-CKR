<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("palletmover/add");
$can_edit = ACL::is_allowed("palletmover/edit");
$can_view = ACL::is_allowed("palletmover/view");
$can_delete = ACL::is_allowed("palletmover/delete");
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
<section class="page ajax-page" id="<?php echo $page_element_id; ?>" data-page-type="list"  data-display-type="table" data-page-url="<?php print_link($current_page); ?>">
    <div  class="my-3">
        <div class="container">
            <div class="row ">
                <div class="col comp-grid">
                    <h3 class="record-title"><div class="alert">
                        <strong>HISTORY NOK LIST</strong>
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
        </div>
    </div>
</div>
<?php
if( $show_header == true ){
?>
<div  class="my-3">
    <div class="container-fluid">
        <div class="row ">
            <div class="col-md-4 comp-grid">
                <a  class="btn btn btn-secondary disabled" href="<?php print_link("palletmover/uncompleted") ?>">
                    Pallet Mover 
                </a>
                <a  class="btn btn-primary" href="<?php print_link("forklift/nok_history") ?>">
                    Forklift 
                </a>
            </div>
            <div class="col-sm-3 comp-grid">
            </div>
            <div class="col-md-2 comp-grid">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-sort-numeric-asc "></i> Nomor Palletmover
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php 
                        $option_list = $comp_model->palletmover_palletmoverno_palletmover_option_list();
                        if(!empty($option_list)){
                        foreach($option_list as $option){
                        $value = (!empty($option['value']) ? $option['value'] : null);
                        $label = (!empty($option['label']) ? $option['label'] : $value);
                        $nav_link = $this->set_current_page_link(array('palletmover_no_palletmover' => $value , 'palletmover_no_palletmoverlabel' => $label) , false);
                        ?>
                        <a class="dropdown-item <?php echo is_active_link('palletmover_no_palletmover', $value); ?>" href="<?php print_link($nav_link) ?>">
                            <i class="fa fa-sort-numeric-asc "></i> <?php echo $label; ?>
                        </a>
                        <?php
                        }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 ">
                <input autocomplete="off" data-page-id="<?php echo $page_element_id ?>" data-page="<?php print_link($current_page); ?>" value="<?php echo get_value('search'); ?>" class="form-control ajax-page-search" type="text" name="search"  placeholder="Cari" />
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
                                    <a class="text-decoration-none" href="<?php print_link('palletmover'); ?>">
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
                                    <a class="text-decoration-none" href="<?php print_link('palletmover'); ?>">
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
                    <div class="filter-tags mb-2">
                        <?php
                        if(!empty(get_value('palletmover_no_palletmover'))){
                        ?>
                        <div class="filter-chip card bg-light">
                            <b>Nomor Palletmover :</b> 
                            <?php 
                            if(get_value('palletmover_no_palletmoverlabel')){
                            echo get_value('palletmover_no_palletmoverlabel');
                            }
                            else{
                            echo get_value('palletmover_no_palletmover');
                            }
                            $remove_link = unset_get_value('palletmover_no_palletmover', $this->route->page_url);
                            ?>
                            <a href="<?php print_link($remove_link); ?>" class="close-btn">
                                &times;
                            </a>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div  class=" animated fadeIn page-content">
                        <div id="palletmover-uncompleted-records">
                            <div id="page-report-body" class="table-responsive">
                                <?php Html::ajaxpage_spinner(); ?>
                                <table class="table  table-sm text-left">
                                    <thead class="table-header bg-secondary">
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
                                            <th  <?php echo (get_value('orderby')=='date_created' ? 'class="sortedby td-date_created"' : null); ?>>
                                                <?php Html :: get_field_order_link('date_created', "Date Created"); ?>
                                            </th>
                                            <th  <?php echo (get_value('orderby')=='user_created' ? 'class="sortedby td-user_created"' : null); ?>>
                                                <?php Html :: get_field_order_link('user_created', "User Created"); ?>
                                            </th>
                                            <th  <?php echo (get_value('orderby')=='no_palletmover' ? 'class="sortedby td-no_palletmover"' : null); ?>>
                                                <?php Html :: get_field_order_link('no_palletmover', "Nomor Palletmover"); ?>
                                            </th>
                                            <th  class="td-approval"> Approval</th>
                                            <th  class="td-date_update"> Date Update</th>
                                            <th  class="td-kondisi"> Kondisi</th>
                                            <th  class="td-keterangan"> Keterangan</th>
                                            <th  class="td-user_update"> User Update</th>
                                            <th  class="td-air_aki"> Air Aki</th>
                                            <th  class="td-charger_palletmover"> Charger Palletmover</th>
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
                                                <td class="td-date_created"> <?php echo $data['date_created']; ?></td>
                                                <td class="td-user_created"> <?php echo $data['user_created']; ?></td>
                                                <td class="td-no_palletmover"> <?php echo $data['no_palletmover']; ?></td>
                                                <td class="td-approval">
                                                    <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $approval); ?>' 
                                                        data-value="<?php echo $data['approval']; ?>" 
                                                        data-pk="<?php echo $data['id'] ?>" 
                                                        data-url="<?php print_link("palletmover/editfield/" . urlencode($data['id'])); ?>" 
                                                        data-name="approval" 
                                                        data-title="Select a value ..." 
                                                        data-placement="left" 
                                                        data-toggle="click" 
                                                        data-type="select" 
                                                        data-mode="popover" 
                                                        data-showbuttons="left" 
                                                        class="is-editable" <?php } ?>>
                                                        <?php echo $data['approval']; ?> 
                                                    </span>
                                                </td>
                                                <td class="td-date_update">
                                                    <span <?php if($can_edit){ ?> data-value="<?php echo $data['date_update']; ?>" 
                                                        data-pk="<?php echo $data['id'] ?>" 
                                                        data-url="<?php print_link("palletmover/editfield/" . urlencode($data['id'])); ?>" 
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
                                                <td class="td-kondisi">
                                                    <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $kondisi); ?>' 
                                                        data-value="<?php echo $data['kondisi']; ?>" 
                                                        data-pk="<?php echo $data['id'] ?>" 
                                                        data-url="<?php print_link("palletmover/editfield/" . urlencode($data['id'])); ?>" 
                                                        data-name="kondisi" 
                                                        data-title="Enter Bagaimana kondisi seluruh part pada pallet mover?" 
                                                        data-placement="left" 
                                                        data-toggle="click" 
                                                        data-type="checklist" 
                                                        data-mode="popover" 
                                                        data-showbuttons="left" 
                                                        class="is-editable" <?php } ?>>
                                                        <?php echo $data['kondisi']; ?> 
                                                    </span>
                                                </td>
                                                <td class="td-keterangan"> <?php echo $data['keterangan']; ?></td>
                                                <td class="td-user_update"> <?php echo $data['user_update']; ?></td>
                                                <td class="td-air_aki"> <?php echo $data['air_aki']; ?></td>
                                                <td class="td-charger_palletmover"> <?php echo $data['charger_palletmover']; ?></td>
                                                <th class="td-btn">
                                                    <?php if($can_view){ ?>
                                                    <a class="btn btn-sm btn-success has-tooltip page-modal" title="View Record" href="<?php print_link("palletmover/view/$rec_id"); ?>">
                                                        <i class="fa fa-eye"></i> Detail
                                                    </a>
                                                    <?php } ?>
                                                    <?php if($can_edit){ ?>
                                                    <a class="btn btn-sm btn-info has-tooltip page-modal" title="Edit This Record" href="<?php print_link("palletmover/edit/$rec_id"); ?>">
                                                        <i class="fa fa-edit"></i> Update
                                                    </a>
                                                    <?php } ?>
                                                    <?php if($can_delete){ ?>
                                                    <a class="btn btn-sm btn-danger has-tooltip record-delete-btn" title="Delete this record" href="<?php print_link("palletmover/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>" data-prompt-msg="Are you sure you want to delete this record?" data-display-style="modal">
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
                                        <i class="fa fa-ban"></i> Tidak ada data yang disimpan
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
                                                <button data-prompt-msg="Are you sure you want to delete these records?" data-display-style="modal" data-url="<?php print_link("palletmover/delete/{sel_ids}/?csrf_token=$csrf_token&redirect=$current_page"); ?>" class="btn btn-sm btn-danger btn-delete-selected d-none">
                                                    <i class="fa fa-times"></i> Delete Selected
                                                </button>
                                                <?php } ?>
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
                                                                $pager->ajax_page = true;
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
