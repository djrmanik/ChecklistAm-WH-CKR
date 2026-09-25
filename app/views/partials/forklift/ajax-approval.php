<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("forklift/add");
$can_edit = ACL::is_allowed("forklift/edit");
$can_view = ACL::is_allowed("forklift/view");
$can_delete = ACL::is_allowed("forklift/delete");
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
    <th class="td-checkbox">
        <label class="custom-control custom-checkbox custom-control-inline">
            <input
                class="optioncheck custom-control-input"
                name="optioncheck[]"
                value="<?php echo $data['id'] ?>"
                type="checkbox"
                />
                <span class="custom-control-label"></span>
            </label>
        </th>
        <?php } ?>
        <th class="td-sno">
            <?php echo $counter; ?>
        </th>
        <td class="td-date_created">
            <?php echo $data['date_created']; ?>
        </td>
        <td class="td-user_created">
            <?php echo $data['user_created']; ?>
        </td>
        <td class="td-no_forklift">
            <?php echo $data['no_forklift']; ?>
        </td>
        <!-- KONDISI -->
        <td class="td-kondisi">
            <span
                <?php if($can_edit){ ?>
                data-source='<?php echo json_encode_quote(Menu :: $kondisi); ?>'
                data-value="<?php echo $data['kondisi']; ?>"
                data-pk="<?php echo $data['id'] ?>"
                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>"
                data-name="kondisi"
                data-title="Enter Bagaimana kondisi forklift?"
                data-placement="left"
                data-toggle="click"
                data-type="checklist"
                data-mode="inline"
                data-showbuttons="left"
                class="is-editable"
                <?php } ?>
                >
                <?php echo $data['kondisi']; ?>
            </span>
        </td>
        <!-- KETERANGAN -->
        <td class="td-keterangan">
            <?php echo $data['keterangan']; ?>
        </td>
        <!-- APPROVAL -->
        <td class="td-approval">
            <span
                <?php if($can_edit){ ?>
                data-source='<?php echo json_encode_quote(Menu :: $approval); ?>'
                data-value="<?php echo $data['approval']; ?>"
                data-pk="<?php echo $data['id'] ?>"
                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>"
                data-name="approval"
                data-title="Select a value ..."
                data-placement="left"
                data-toggle="click"
                data-type="select"
                data-mode="inline"
                data-showbuttons="left"
                class="is-editable"
                <?php } ?>
                >
                <?php echo $data['approval']; ?>
            </span>
        </td>
        <!-- DATE UPDATE -->
        <td class="td-date_update">
            <span
                <?php if($can_edit){ ?>
                data-value="<?php echo $data['date_update']; ?>"
                data-pk="<?php echo $data['id'] ?>"
                data-url="<?php print_link("forklift/editfield/" . urlencode($data['id'])); ?>"
                data-name="date_update"
                data-title="Enter Date Update"
                data-placement="left"
                data-toggle="click"
                data-type="text"
                data-mode="inline"
                data-showbuttons="left"
                class="is-editable"
                <?php } ?>
                >
                <?php echo $data['date_update']; ?>
            </span>
        </td>
        <!-- BUTTON -->
        <th class="td-btn">
            <?php if($can_view){ ?>
            <a
                class="btn btn-sm btn-success has-tooltip page-modal"
                title="View Record"
                href="<?php print_link("forklift/view/$rec_id"); ?>"
                >
                <i class="fa fa-eye"></i> Detail
            </a>
            <?php } ?>
            <?php if($can_edit){ ?>
            <a
                class="btn btn-sm btn-info has-tooltip page-modal"
                title="Edit This Record"
                href="<?php print_link("forklift/approvalbtn/$rec_id"); ?>"
                >
                <i class="fa fa-edit"></i> Approval
            </a>
            <?php } ?>
            <?php if($can_edit){ ?>
            <a
                class="btn btn-sm btn-info has-tooltip page-modal"
                title="Edit This Record"
                href="<?php print_link("forklift/edit/$rec_id"); ?>"
                >
                <i class="fa fa-edit"></i> Update
            </a>
            <?php } ?>
            <?php if($can_delete){ ?>
            <a
                class="btn btn-sm btn-danger has-tooltip record-delete-btn"
                title="Delete this record"
                href="<?php print_link("forklift/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>"
                data-prompt-msg="Are you sure you want to delete this record?"
                data-display-style="modal"
                >
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
    