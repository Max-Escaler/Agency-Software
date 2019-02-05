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

$quiet='Y';
include('includes.php');

$_SESSION['calendar_id'] = $id = orr($_REQUEST['id'],$_SESSION['calendar_id']);
$_SESSION['calendar_start'.$id] = $start = orr(dateof($_REQUEST['st'],'SQL'),$_SESSION['calendar_start'.$id],dateof('now','SQL'));

if (isset($_REQUEST['Menu'])) {
	$_SESSION['calendar_id'] = $id = null;
}

if ($id) {
	$cal = new Calendar($id,$start);
	$out = $cal->display();
	$commands = $cal->commands;
} else {
	$out = Calendar::default_menu();
}

$out .= html_end();

agency_top_header($commands);
out($out);
page_close();
?>
