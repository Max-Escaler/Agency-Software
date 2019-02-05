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

//$query_display="Y";
$quiet="Y";
include "includes.php";
$object=$_REQUEST['object'];
if (!in_array($object,$AG_ENGINE_TABLES)) {
	agency_top_header();
	outline("Unknown object $object passed to object_reg.  Cannot continue");
	page_close();
	exit;
}

$def = get_def($object);

if (!(is_enabled('object_reg_search') and is_array($def['registration']))) {
	header("Location: display.php?control[action]=add&control[object]=".$def['object']);
	page_close();
	exit;
}

$title='Register a new '.$def['singular'];
$action=$_REQUEST['action'];
$rec = $_REQUEST['rec'];
foreach ($def['registration']['search_fields'] as $x) {
	$rec[$x]=dewebify($rec[$x]);
}

if ($action=='search') {
	$out .= object_reg_search_verify($object,$def,$rec);
} else {
	$out .= object_reg_form($object,$def,$rec);
}

agency_top_header();
out(bigger(bold(oline($title)),2));
out($main_object_reg_subtitle ? $main_object_reg_subtitle : oline());
out($mesg);
out($out);
page_close();


?>
