<?php

/**
 *	Edit circuit details
 ************************/

/* functions */
require( dirname(__FILE__) . '/../../../functions/functions.php');

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database, false);
$Tools	 	= new Tools ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();

# check permissions
if(!($User->is_admin(false) || $User->user->editCircuits=="Yes")) { $Result->show("danger", _("You are not allowed to modify Circuit details"), true, true); }

# create csrf token
$csrf = $User->csrf_cookie ("create", "circuit");

# strip tags - XSS
$_POST = $User->strip_input_tags ($_POST);

# validate action
$Admin->validate_action ($_POST['action'], true);

# fetch custom fields
$custom = $Tools->fetch_custom_fields('circuits');

# ID must be numeric
if($_POST['action']!="add" && !is_numeric($_POST['circuitid']))	{ $Result->show("danger", _("Invalid ID"), true, true); }

# fetch circuit details
if( ($_POST['action'] == "edit") || ($_POST['action'] == "delete") ) {
	$circuit = $Admin->fetch_object("circuits", "id", $_POST['circuitid']);
	// false
	if ($circuit===false)                                          { $Result->show("danger", _("Invalid ID"), true, true);  }
}
// defaults
else {
	$circuit = new StdClass ();
	$circuit->provider = 0;
}

# fetch all providers, devices, locations
$circuit_providers = $Tools->fetch_all_objects("circuitProviders", "name");
$all_devices       = $Tools->fetch_all_objects("devices", "hostname");
$all_locations     = $Tools->fetch_all_objects("locations", "name");

# get types and parse from enum
$type_desc = $Database->getFieldInfo ("circuits", "type");
$all_types = explode(",", str_replace(array("enum","(",")","'"), "",$type_desc->Type));

# set readonly flag
$readonly = $_POST['action']=="delete" ? "readonly" : "";
?>

<script type="text/javascript">
$(document).ready(function(){
     if ($("[rel=tooltip]").length) { $("[rel=tooltip]").tooltip(); }
});
</script>


<!-- header -->
<div class="pHeader"><?php print ucwords(_("$_POST[action]")); ?> <?php print _('Circuit'); ?></div>


<!-- content -->
<div class="pContent">

	<form id="circuitManagementEdit">
	<table class="table table-noborder table-condensed">

	<!-- name -->
	<tr>
		<td><?php print _('Circuit ID'); ?></td>
		<td>
			<input type="text" name="cid" style='width:200px;' class="form-control input-sm" placeholder="<?php print _('ID'); ?>" value="<?php if(isset($circuit->cid)) print $circuit->cid; ?>" <?php print $readonly; ?>>
			<?php
			if( ($_POST['action'] == "edit") || ($_POST['action'] == "delete") ) {
				print '<input type="hidden" name="id" value="'. $_POST['circuitid'] .'">'. "\n";
			} ?>
			<input type="hidden" name="action" value="<?php print $_POST['action']; ?>">
			<input type="hidden" name="csrf_cookie" value="<?php print $csrf; ?>">
		</td>
	</tr>

	<!-- provider -->
	<tr>
		<td><?php print _('Provider'); ?></td>
		<td>
			<select name="provider" class="form-control input-w-auto input-sm">
				<?php
				if($circuit_providers!==false) {
					foreach ($circuit_providers as $key => $p) {
						$selected = $circuit->provider == $p->id ? "selected" : "";
						print "<option value='$p->id' $selected>$p->name</option>";
					}
				}
				?>
			</select>
		</td>
	</tr>

	<!-- type -->
	<tr>
		<td><?php print _('Circuit type'); ?></td>
		<td>
			<select name="type" class="form-control input-w-auto input-sm">
				<?php
				foreach ($all_types as $type) {
					$selected = $circuit->type == $type ? "selected" : "";
					print "<option value='$type' $selected>$type</option>";
				}
				?>
			</select>
		</td>
	</tr>

	<!-- capacity -->
	<tr>
		<td><?php print _('Capacity'); ?></td>
		<td>
			<input type="text" name="capacity" style='width:200px;'  class="form-control input-sm" placeholder="<?php print _('Capacity'); ?>" value="<?php if(isset($circuit->capacity)) print $circuit->capacity; ?>" <?php print $readonly; ?>>
		</td>
	</tr>

	<!-- Status -->
	<tr>
		<td><?php print _('Status'); ?></td>
		<td>
			<select name="status" class="form-control input-w-auto input-sm">
				<?php
				// statuses array
				$statuses = array ("Active", "Inactive", "Reserved");

				foreach ($statuses as $v) {
					$selected = $circuit->status == $v ? "selected" : "";
					print "<option value='$v' $selected>$v</option>";
				}
				?>
			</select>
		</td>
	</tr>



	<!-- devices, locations -->
	<tr>
		<td colspan="2"><hr></td>
	</tr>

	<tr>
		<td>Point A</td>
		<td>
			<select name="device1" class="form-control input-w-auto input-sm">
				<option value="0">None</option>
				<optgroup label="Devices">
					<?php
					if($all_devices!==false) {
						foreach ($all_devices as $d) {
							$selected = $circuit->device1 == $d->id ? "selected" : "";
							print "<option value='device_$d->id' $selected>$d->hostname</option>";
						}
					}
					?>
				</optgroup>
				<?php if($User->settings->enableLocations=="1") { ?>
				<optgroup label="Locations">
				<?php
				if($all_locations!==false) {
					foreach ($all_locations as $l) {
						$selected = $circuit->location1 == $l->id ? "selected" : "";
						print "<option value='location_$l->id' $selected>$l->name</option>";
					}
				}
				?>
				</optgroup>
				<?php } ?>
			</select>
		</td>
	</tr>

	<tr>
		<td>Point B</td>
		<td>
			<select name="device2" class="form-control input-w-auto input-sm">
				<option value="0">None</option>
				<optgroup label="Devices">
					<?php
					if($all_devices!==false) {
						foreach ($all_devices as $d) {
							$selected = $circuit->device2 == $d->id ? "selected" : "";
							print "<option value='device_$d->id' $selected>$d->hostname</option>";
						}
					}
					?>
				</optgroup>
				<?php if($User->settings->enableLocations=="1") { ?>
				<optgroup label="Locations">
				<?php
				if($all_locations!==false) {
					foreach ($all_locations as $l) {
						$selected = $circuit->location2 == $l->id ? "selected" : "";
						print "<option value='location_$l->id' $selected>$l->name</option>";
					}
				}
				?>
				</optgroup>
				<?php } ?>
			</select>
		</td>
	</tr>



	<!-- comment -->
	<tr>
		<td colspan="2"><hr></td>
	</tr>
	<tr>
		<td><?php print _('Comment'); ?></td>
		<td>
			<textarea name="comment" class="form-control input-sm" <?php print $readonly; ?>><?php if(isset($circuit->comment)) print $circuit->comment; ?></textarea>
		</td>
	</tr>


	<!-- Custom -->
	<?php
	if(sizeof($custom) > 0) {

		print '<tr>';
		print '	<td colspan="2"><hr></td>';
		print '</tr>';

		# count datepickers
		$timepicker_index = 0;

		# all my fields
		foreach($custom as $field) {
			// readonly
			$disabled = $readonly == "readonly" ? true : false;
    		// create input > result is array (required, input(html), timepicker_index)
    		$custom_input = $Tools->create_custom_field_input ($field, $circuit, $_POST['action'], $timepicker_index, $readonly);
    		// add datepicker index
    		$timepicker_index = $timepicker_index + $custom_input['timepicker_index'];
            // print
			print "<tr>";
			print "	<td>".ucwords($field['name'])." ".$custom_input['required']."</td>";
			print "	<td>".$custom_input['field']."</td>";
			print "</tr>";
		}
	}

	?>

	</table>
	</form>
</div>

<!-- footer -->
<div class="pFooter">
	<div class="btn-group">
		<button class="btn btn-sm btn-default hidePopups"><?php print _('Cancel'); ?></button>
		<button class="btn btn-sm btn-default submit_popup <?php if($_POST['action']=="delete") { print "btn-danger"; } else { print "btn-success"; } ?>" data-script="app/admin/circuits/edit-circuit-submit.php" data-result_div="circuitManagementEditResult" data-form='circuitManagementEdit'>
			<i class="fa <?php if($_POST['action']=="add") { print "fa-plus"; } else if ($_POST['action']=="delete") { print "fa-trash-o"; } else { print "fa-check"; } ?>"></i>
			<?php print ucwords(_($_POST['action'])); ?>
		</button>
	</div>

	<!-- result -->
	<div class='circuitManagementEditResult' id="circuitManagementEditResult"></div>
</div>