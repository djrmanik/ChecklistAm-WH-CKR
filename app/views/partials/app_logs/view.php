<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("app_logs/add");
$can_edit = ACL::is_allowed("app_logs/edit");
$can_view = ACL::is_allowed("app_logs/view");
$can_delete = ACL::is_allowed("app_logs/delete");
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
                    <h4 class="record-title">View  App Logs</h4>
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
                        $rec_id = (!empty($data['log_id']) ? urlencode($data['log_id']) : null);
                        $counter++;
                        ?>
                        <div id="page-report-body" class="">
                            <table class="table table-hover table-borderless table-striped">
                                <!-- Table Body Start -->
                                <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">
                                    <tr  class="td-log_id">
                                        <th class="title"> Log Id: </th>
                                        <td class="value"> <?php echo $data['log_id']; ?></td>
                                    </tr>
                                    <tr  class="td-Timestamp">
                                        <th class="title"> Timestamp: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['Timestamp']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="Timestamp" 
                                                data-title="Enter Timestamp" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="time" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['Timestamp']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-Action">
                                        <th class="title"> Action: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['Action']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="Action" 
                                                data-title="Enter Action" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['Action']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-TableName">
                                        <th class="title"> Tablename: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['TableName']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="TableName" 
                                                data-title="Enter Tablename" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['TableName']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-RecordID">
                                        <th class="title"> Recordid: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['RecordID']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="RecordID" 
                                                data-title="Enter Recordid" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['RecordID']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-SqlQuery">
                                        <th class="title"> Sqlquery: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['SqlQuery']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="SqlQuery" 
                                                data-title="Enter Sqlquery" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['SqlQuery']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-UserID">
                                        <th class="title"> Userid: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['UserID']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="UserID" 
                                                data-title="Enter Userid" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['UserID']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-ServerIP">
                                        <th class="title"> Serverip: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['ServerIP']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="ServerIP" 
                                                data-title="Enter Serverip" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['ServerIP']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-RequestUrl">
                                        <th class="title"> Requesturl: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="RequestUrl" 
                                                data-title="Enter Requesturl" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="textarea" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['RequestUrl']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-RequestData">
                                        <th class="title"> Requestdata: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="RequestData" 
                                                data-title="Enter Requestdata" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="textarea" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['RequestData']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-RequestCompleted">
                                        <th class="title"> Requestcompleted: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-value="<?php echo $data['RequestCompleted']; ?>" 
                                                data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="RequestCompleted" 
                                                data-title="Enter Requestcompleted" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="text" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['RequestCompleted']; ?> 
                                            </span>
                                        </td>
                                    </tr>
                                    <tr  class="td-RequestMsg">
                                        <th class="title"> Requestmsg: </th>
                                        <td class="value">
                                            <span <?php if($can_edit){ ?> data-pk="<?php echo $data['log_id'] ?>" 
                                                data-url="<?php print_link("app_logs/editfield/" . urlencode($data['log_id'])); ?>" 
                                                data-name="RequestMsg" 
                                                data-title="Enter Requestmsg" 
                                                data-placement="left" 
                                                data-toggle="click" 
                                                data-type="textarea" 
                                                data-mode="popover" 
                                                data-showbuttons="left" 
                                                class="is-editable" <?php } ?>>
                                                <?php echo $data['RequestMsg']; ?> 
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
                                                <a class="btn btn-sm btn-info"  href="<?php print_link("app_logs/edit/$rec_id"); ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <?php } ?>
                                                <?php if($can_delete){ ?>
                                                <a class="btn btn-sm btn-danger record-delete-btn mx-1"  href="<?php print_link("app_logs/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>" data-prompt-msg="Are you sure you want to delete this record?" data-display-style="modal">
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
