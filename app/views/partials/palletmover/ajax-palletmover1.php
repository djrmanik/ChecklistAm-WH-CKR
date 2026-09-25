<?php 
//check if current user role is allowed access to the pages
$can_add = ACL::is_allowed("palletmover/add");
$can_edit = ACL::is_allowed("palletmover/edit");
$can_view = ACL::is_allowed("palletmover/view");
$can_delete = ACL::is_allowed("palletmover/delete");
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
        <td class="td-date_created"> <?php echo $data['date_created']; ?></td>
        <td class="td-user_created"> <?php echo $data['user_created']; ?></td>
        <td class="td-no_palletmover">
            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $no_palletmover) /* [23SEP26] dulu $no_palletmover2: ada 'Pallet Mover 5', tidak ada 'Pallet Stacker' */; ?>' 
                data-value="<?php echo $data['no_palletmover']; ?>" 
                data-pk="<?php echo $data['id'] ?>" 
                data-url="<?php print_link("palletmover/editfield/" . urlencode($data['id'])); ?>" 
                data-name="no_palletmover" 
                data-title="Pilih nomor pallet mover yang digunakan" 
                data-placement="left" 
                data-toggle="click" 
                data-type="select" 
                data-mode="popover" 
                data-showbuttons="left" 
                class="is-editable" <?php } ?>>
                <?php echo $data['no_palletmover']; ?> 
            </span>
        </td>
        <td class="td-approval"> <?php echo $data['approval']; ?></td>
        <td class="td-kondisi">
            <span <?php if($can_edit){ ?> data-source='<?php echo json_encode_quote(Menu :: $kondisi); ?>' 
                data-value="<?php echo $data['kondisi']; ?>" 
                data-pk="<?php echo $data['id'] ?>" 
                data-url="<?php print_link("palletmover/editfield/" . urlencode($data['id'])); ?>" 
                data-name="kondisi" 
                data-title="Enter Bagaimana kondisi seluruh part pada pallet mover?" 
                data-placement="left" 
                data-toggle="click" 
                data-type="radiolist" 
                data-mode="popover" 
                data-showbuttons="left" 
                class="is-editable" <?php } ?>>
                <?php echo $data['kondisi']; ?> 
            </span>
        </td>
        <td class="td-keterangan">
            <span <?php if($can_edit){ ?> data-value="<?php echo $data['keterangan']; ?>" 
                data-pk="<?php echo $data['id'] ?>" 
                data-url="<?php print_link("palletmover/editfield/" . urlencode($data['id'])); ?>" 
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
    