<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("forklift/add");
$can_edit = ACL::is_allowed("forklift/edit");
$can_view = ACL::is_allowed("forklift/view");
$can_delete = ACL::is_allowed("forklift/delete");
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
                <div class="col comp-grid">
                    <h3 class="record-title"><div class="alert">
                        <strong>Checklist Forklift ✅ </strong>
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
    <div class="container-fluid">
        <div class="row ">
            <div class="col-md-4 comp-grid">
            </div>
            <div class="col-md-4 comp-grid">
                <?php if($can_add){ ?>
                <a  class="btn btn btn-primary my-1" href="<?php print_link("forklift/add") ?>">
                    <i class="fa fa-plus"></i>                              
                    AM Forklift Electric 
                </a>
                <?php } ?>
                <a  class="btn btn-success" href="<?php print_link("forklift/add_forklift_diesel") ?>">
                    <i class="fa fa-plus"></i>                              
                    AM Forklift Diesel 
                </a>
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
            </div>
            <div class="col-md-12 comp-grid">
                <div class="card ">
                    <div class="card-header p-0 pt-2 px-2">
                        <ul class="nav  nav-tabs   justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#TabPage-1-Page1" role="tab" aria-selected="true">
                                    <i class="fa fa-dedent "></i> 185E00370
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page2" role="tab" aria-selected="true">
                                    <i class="fa fa-dedent "></i> 131AD0216
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page3" role="tab" aria-selected="true">
                                    <i class="fa fa-dedent "></i> R2B-06835
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page4" role="tab" aria-selected="true">
                                    <i class="fa fa-dedent "></i> 131AC8606
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page5" role="tab" aria-selected="true">
                                    <i class="fa fa-dedent "></i> 131AE3297
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " data-toggle="tab" href="#TabPage-1-Page6" role="tab" aria-selected="true">
                                    <i class="fa fa-dedent "></i> Forklift Diesel
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane show active fade" id="TabPage-1-Page1" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("forklift/forklift_1/forklift.no_forklift/185E00370?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page2" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("forklift/forklift_2/forklift.no_forklift/131AD0216?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page3" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("forklift/forklift_3/forklift.no_forklift/R2B-06835?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page4" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("forklift/forklift_4/forklift.no_forklift/131AC8606?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page5" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("forklift/forklift_5/forklift.no_forklift/131AE3297?limit_count=20"); 
                                    ?>
                                </div>
                            </div>
                            <div class="tab-pane  fade" id="TabPage-1-Page6" role="tabpanel">
                                <div class=" ">
                                    <?php  
                                    $this->render_page("forklift/forklift_diesel/forklift.no_forklift/Forklift Diesel?limit_count=20"); 
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
