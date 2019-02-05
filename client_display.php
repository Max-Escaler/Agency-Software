<?php
/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2017 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency-software.org/
For more information about DESC, see http://www.desc.org/.

AGENCY is free software: you can redistribute it and/or modify
it under the terms of version 3 of the GNU General Public License
as published by the Free Software Foundation.

AGENCY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with AGENCY.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

$quiet = true;
include 'includes.php';
include 'mail.php';

$action = $_REQUEST['action'];
$ID     = $_SESSION['CLIENT_ID'] = orr($_REQUEST['id'],$_REQUEST['control']['id'],$_SESSION['CLIENT_ID']);

$mo_def=get_def(AG_MAIN_OBJECT_DB);
$mo_id_label=$mo_def['fields'][$mo_def['id_field']]['label_view'];
$mo_noun=$mo_def['singular'];

if (!$ID or ($ID > AG_POSTGRESQL_MAX_INT) or (!is_numeric($ID))) {

	agency_top_header();
	out(alert_mark($ID ? $mo_id_label . ' ('.$ID.') out of range. Stopping' : 'No ID passed to '.$_SERVER['PHP_SELF'] . '.  Stopping'));
	page_close();
	exit;

}

$client = client_get($ID);

if (sql_num_rows($client) == 0 ) {

	$title="No $mo_noun with ID $ID.";
	$not_found_message=alert_mark($title);
	$client_not_found=true;

}

$commands = array(bottomcell(object_child_command_box_generic(AG_MAIN_OBJECT_DB,$ID),"bgcolor=\"{$colors['client_command_box']}\""));

if (!$client_not_found) {

	$client = sql_fetch_assoc($client);

	switch ($action) {	
	case 'print_client_id':

		include 'openoffice.php';
		include 'zipclass.php';

		$card = generate_client_card($ID);
		serve_office_doc($card,'idcard.pdf'); //exits

	case 'set_clinical_id':

		if (has_perm('clinical_data_entry','RW')) {

			// clinical id must be blank
			if (be_null($client['clinical_id'])) {

				sql_begin();
				$clinical_id = sql_get_sequence('seq_client_clinical_id');
				$res = agency_query(sql_update('tbl_client',array('clinical_id' => $clinical_id,
												'changed_by' => $UID,
												'FIELD:changed_at' => 'CURRENT_TIMESTAMP'),
								     array('client_id' => $ID,
									     'NULL:clinical_id' => ''),'*'));
				if (sql_num_rows($res) == 1) {
					$message .= div('Clinical ID set to '.$clinical_id,'',' class="message"');
					sql_end();
				} else {
					$message .= alert_mark('Failed to set clinical ID');
					sql_abort();
				}

			} else {

				$message .= alert_mark('Clinical ID is already set to '.$client['clinical_id']);

			}

		} else {

			$message .= alert_mark('No permissions for setting Clinical ID');

		}
		break;
	}




	array_unshift($commands,bottomcell(smaller(
								 oline(hlink_if($_SERVER['PHP_SELF']."?action=print_client_id&id={$client['client_id']}",
										    "Print ID Card", is_id_station()))
								. oline(hlink_if("jils_import.php?client_id=$ID",'Import JILS Data',is_enabled('jils_import') and has_perm('jils_import'),NULL,'target="_blank"'))
//								 . oline(hlink_if("get_photo.php?client_id={$client['client_id']}",
//											(is_photo_station() ? "Take" : "Upload") . " new photo",true))
								 . link_engine(array('object'=>AG_MAIN_OBJECT_DB,
												'format'=>'data',
											   'id'=>$client[AG_MAIN_OBJECT_DB.'_id']),
										   $AG_TEXT['LINK_VIEW_EDIT']) ),'bgcolor="#ffff80"'));

	$name=strip_tags(client_name($ID));
	$title="$name ($ID)"; 
}

agency_top_header($commands);

out($message ? div($message,'','class="engineMessage"') : '');
($client_not_found && out($not_found_message)) || client_show( $ID );

page_close();
?>
