<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("mesin_geprek/add");
$can_edit = ACL::is_allowed("mesin_geprek/edit");
$can_view = ACL::is_allowed("mesin_geprek/view");
$can_delete = ACL::is_allowed("mesin_geprek/delete");
?>
<?php
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$field_name = $this->route->field_name;
$field_value = $this->route->field_value;
$view_data = $this->view_data;
$records = $view_data->records;
$record_count = $view_data->record_count;
$total_records = $view_data->total_records;
if (!empty($records)) {
?>
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
                data-mode="inline" 
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
                data-mode="inline" 
                data-showbuttons="left" 
                class="is-editable" <?php } ?>>
                <?php echo $data['kondisi']; ?> 
            </span>
        </td>
        <th class="td-btn">
            <?php if($can_view){ ?>
            <a class="btn btn-sm btn-success has-tooltip page-modal" title="View Record" href="<?php print_link("mesin_geprek/view/$rec_id"); ?>">
                <i class="fa fa-eye"></i> Detail
            </a>
            <?php } ?>
            <?php if($can_edit){ ?>
            <a class="btn btn-sm btn-info has-tooltip page-modal" title="Edit This Record" href="<?php print_link("mesin_geprek/approvalbtn/$rec_id"); ?>">
                <i class="fa fa-edit"></i> Approval
            </a>
            <?php } ?>
            <?php if($can_edit){ ?>
            <a class="btn btn-sm btn-info has-tooltip page-modal" title="Edit This Record" href="<?php print_link("mesin_geprek/edit/$rec_id"); ?>">
                <i class="fa fa-edit"></i> Update
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
    <?php
    } else {
    ?>
    <td class="no-record-found col-12" colspan="100">
        <h4 class="text-muted text-center ">
            No Record Found
        </h4>
    </td>
    <?php
    }
    ?>
    