#!/usr/bin/php -q
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



// small script to make door list for CRP door controller
// all CRP active clients should be on list, plus all active staff
// Format is LMW ID-Issueno; LMW refer to lobby, men, women respectively
// Replace letter with "X" to indicate no access
$quiet="Y";
$MODE="TEXT";
$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$crp_reg_table=$engine['crp_reg']['table'];
$client_table=$engine['client']['table'];

$filter[] = array("NULL:crp_reg_date_end"=>"dummy",
			'FIELD>=:crp_reg_date_end'=>'CURRENT_DATE');
$query="SELECT client_id, issue_no FROM $crp_reg_table LEFT JOIN $client_table USING (client_id)";

$crp_clients = agency_query($query,$filter);

while ($cl=sql_fetch_assoc($crp_clients))
{
	$access="L"
			. (is_male($cl["client_id"])
			? "M" : "X")
			. (is_female($cl["client_id"])
			? "W" : "X");
	$list .= oline($access . " " . $cl["client_id"] . "-" . substr("0".$cl["issue_no"],-2));
}
$staff = get_generic(array("is_active"=>"true"),'','','staff');
while ($st=array_shift($staff))
{
	$list .= oline("LMW " . $st["staff_id"]);
}
out($list);
?>
