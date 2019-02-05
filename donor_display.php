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

$relog_page="index.php";
$quiet="Y";
include "includes.php";

$action = $_REQUEST['action'];
$ID=orr($_REQUEST['id'],$_REQUEST['control']['id'],$_SESSION['CLIENT_ID']);
$_SESSION['CLIENT_ID']=$ID;

// $DISPLAY_CLIENT=mergeClientEngine(); //MERGE DISPLAY_CLIENT and CONTROL_{object} ARRAYS

if (!$ID or ($ID > 2147483647)) //this is the maximum integer range in postgresql
{
	agency_top_header();
	out(alert_mark($ID ? AG_MAIN_OBJECT.' ID ('.$ID.') out of range. Stopping' : 'No ID passed to '.AG_MAIN_OBJECT_DB.'_display.  Stopping'));
	page_close();
	exit;
}

$client = client_get($ID);
if (sql_num_rows($client) == 0 )
{
	$title="No client with ID $ID.";
	$not_found_message=alert_mark($title);
	$client_not_found=true;
}

if ($action=="print_donor_profile")
{
	include "openoffice.php";
	include "zipclass.php";
	$template = $_REQUEST['donor_data_merge_template'];
	switch ($template) {
	// add new templates into the $donor_data_merge_templates array in agency_config.php
	case $donor_envelope_template:
/*
	This code should work, but doesn't, because of some problem with
	using an array instead of an sql result in oowriter_merge_new()
	See bug 10642
		$addr=address($ID);
		$addr["_address"]=$addr["address_mail"];
		$addr=array($addr);
*/
		// using this instead, for now:
		$addr=agency_query("SELECT address_mail AS _address FROM address_mail(address($ID))");
		$env=oowriter_merge_new($addr,$template);
		serve_office_doc($end,$template); //exits
		break;
	case $donor_profile_template:
		$profile=generate_donor_profile($ID);
		serve_office_doc($profile,$template); //exits
		break;
	default :
		outline('Error: donor_display not configured for selected template ('.$template.')');
	}
}
$commands = array(bottomcell(object_child_command_box_generic(AG_MAIN_OBJECT_DB,$ID),"bgcolor=\"{$colors['client_command_box']}\""));

$client = sql_fetch_assoc(client_get($ID));
$donor_data_merge_select = formto($_SERVER['PHP_SELF'].'?action=print_donor_profile&id='.$ID)
     . donor_data_merge_select_box('donor_data_merge_template',$donor_profile_template,' style="font-size: 75%;"')
     . button('go','','','','',' style="font-size: 75%;"')
     . formend();

array_unshift($commands,bottomcell(smaller(oline(link_engine(array("object"=>AG_MAIN_OBJECT_DB,
										 'format'=>'data',
										 "id"=>$client[AG_MAIN_OBJECT_DB.'_id']),
										 $AG_TEXT['LINK_VIEW_EDIT'])))
							 .$donor_data_merge_select
					     ,'class="homeMenu"'));

$name=strip_tags(client_name($ID));
$title="$name ($ID)"; 

agency_top_header($commands);
( (!$client_not_found) && client_show( $ID ));

page_close();
?>
