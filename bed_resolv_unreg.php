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

// Resolve unregistered clients in beds
/* Bednight Re-registration will not occur until
*  all clients assigned to a bed are registered in AGENCY.
*  This script shows each bed with an unregistered client
*  in it.  The user may select a client or "no show"
*/

$title = "Resolve Unregistered Clients in Beds";
include "includes.php";

// declare functions
function allow_rereg()
{
    global $bedreg_table, $bed_group;
    $filt['NULL:removed_at'] = '';
    $filt['~:client'] = '[^0-9]';
    $filt['bed_group'] = $bed_group;
    $query = agency_query("SELECT * FROM {$bedreg_table}", $filt);

    $num = sql_num_rows($query);

    if ($num == 0) {
	    outline(bigger("All unregistered clients have been resolved.  Please ")
			. hlink($_SERVER['PHP_SELF'].'?action=rerun_bedreg&bed_group='.$bed_group,'run Bednight Re-Registration'));
    }
    return $num;
}

$request_v = array('action','QuickSearch','client_select','bednum');
foreach ($request_v as $v) {
	$$v = $_REQUEST[$v];
}

if ($_REQUEST['action'] == 'rerun_bedreg') {

	include('scripts/bed_rereg.php');

	exit;
}

// display heading and instructions
outline(bigger(bold("Resolve Unregistered Clients in Beds"),2));

$bed_group = $_REQUEST["bed_group"];
$bed_group = isset($_REQUEST["bedgrp"]) ? $_REQUEST["bedgrp"] : $bed_group;

if (empty($bed_group)) {
	outline('A bed_group is required to run this script');
	page_close();
	exit;
}

if (!is_object($$bed_group)) {
	create_groups(array($bed_group));
}

$BED_TRANSFER = "Y"; //needed by add()
$_SESSION['BAR_CONFIRM']    = $BAR_CONFIRM    = orr($_REQUEST['bar_ok'],$_SESSION['BAR_CONFIRM']);
$_SESSION['GENDER_CONFIRM'] = $GENDER_CONFIRM = orr($_REQUEST['gender_ok'],$_SESSION['GENDER_CONFIRM']);

// add selected client
if (isset($client_select) ) {
    $$bed_group->add($bednum, $client_select);
}

// check action
if ($action=='ClientSearch' && $QuickSearch ) {
	if ( is_numeric($QuickSearch) && is_client($QuickSearch) ) {
		outline("Quick adding $QuickSearch to $bed_group, $bednum");
		$BED_TRANSFER = "Y";  // needed by add()
		$$bed_group->add($bednum, $QuickSearch);
	} else {
		headline("Client Search Results");
		out(client_search());
		page_close();
		exit;
	}
}

if ($action=='remove') {

	if (empty($unreg)){
		$unreg = $_REQUEST["client_remove"];
	}
	// set removedat to same time as addedat
	$$bed_group->remove($bednum, $unreg, $addedat);
}

// currently no comments being set...commented out in show_unregclients()
// set comments
if (isset($comments))
{
    $$bed_group->set_comments($bednum, $comments);
}

// instructions
outline("Search for the client who should be registered in the bed or mark it as
a " . bold("Remove") . " if the client never checked in for the bed.");
outline(hrule());

if ($unreg = $$bed_group->show_unregclients()) {
    //loop through each unregistered client and display
    outline($unreg);
}

allow_rereg();

html_footer();
page_close();

?>
