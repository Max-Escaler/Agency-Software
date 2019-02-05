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

/* This script will output a AGENCY calendar in ICS format */

$MODE = 'TEXT';
$off = dirname(__FILE__).'/./';
include $off.'command_line_includes.php';
while (! ($UID=http_authenticate()))
{
	$count++;
	if ($count > 5)
	{
		return false;
	}
}

if ( ($cal_id=intval($_REQUEST['cal_id'])) == 0)
{
	return false;
}

$file = calendar_to_ics( $cal_id );

header ( "Content-type: text/calendar");
header ( "Content-Disposition: attachment; filename=calendar_$cal_id.ics" );
header ( "Content-Description: AGENCY Generated Calendar File" );
out($file );
page_close();
exit;

?>

