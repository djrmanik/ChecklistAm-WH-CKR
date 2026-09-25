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
    <div  class="">
        <div class="container-fluid">
            <div class="row ">
                <div class="col ">
                    <h3 class="record-title"><div class="alert">
                        <strong>Checklist Pallet Mover ✅ </strong>
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
if( $show_header == true ){
?>
<div  class="my-1">
    <div class="container">
        <div class="row ">
            <div class="col-md-4 comp-grid">
            </div>
            <div class="col-md-4 comp-grid">
                <?php if($can_add){ ?>
                <a  class="btn btn btn-primary" href="<?php print_link("palletmover/add") ?>">
                    <i class="fa fa-plus"></i>                              
                    AM Palletmover/Pallet Stacker 
                </a>
                <?php } ?>
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
            <div class="col-md-12 comp-grid">
                <div class="card ">
                    <div class="card-header p-0 pt-2 px-2">
                        <ul class="nav  nav-tabs   justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#TabPage-1-Page1" role="tab" aria-selected="true">
                                    <i class="fa fa-indent "></i> Pallet Mover 1
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page2" role="tab" aria-selected="true">
                                    <i class="fa fa-indent "></i> Pallet Mover 2
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page3" role="tab" aria-selected="true">
                                    <i class="fa fa-indent "></i> Pallet Mover 3
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page4" role="tab" aria-selected="true">
                                    <i class="fa fa-indent "></i> Pallet Mover 4
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page5" role="tab" aria-selected="true">
                                    <i class="fa fa-indent "></i> Pallet Stacker
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane show active fade" id="TabPage-1-Page1" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("palletmover/palletmover1/palletmover.no_palletmover/Pallet Mover 1?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page2" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("palletmover/palletmover2/palletmover.no_palletmover/Pallet Mover 2?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page3" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("palletmover/palletmover3/palletmover.no_palletmover/Pallet Mover 3?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page4" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("palletmover/palletmover4/palletmover.no_palletmover/Pallet Mover 4?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page5" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("palletmover/palletstacker/palletmover.no_palletmover/Pallet Stacker?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
