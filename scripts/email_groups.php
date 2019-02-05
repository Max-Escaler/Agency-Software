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



$MODE='TEXT';
$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$UID=$GLOBALS["sys_user"];

/*
Script to make email groups for mail server
*/

$destination = "mail:/etc/groups";
$source = "/etc/groups";
$file_prefix = "$source/aliases.";
$copy_command = "scp -r $source/* $destination";

function get_staff_list( $filter )
{
	$def=get_def('staff');
	$list = get_generic($filter,"username_unix",'',$def);
	while ( $rec = array_shift($list) )
	{
		$group[]=$rec["username_unix"];
	}
	return $group;
}

function write_list( $filter, $filename )
{
	$NL="\n"; // For some reason, global $NL wasn't working
	$group=get_staff_list($filter);
	return file_put_contents( $filename,implode($group,$NL) . $NL);
}

$s_def=get_def($staff);
$staff_table=$s_def['table'];
$group_list = array( 
		"all_test" => array(),
		"is_test" => array("$staff_table.agency_project_code"=>"IS")
		);

$default_filter=array("is_active"=>sql_true(), "login_allowed"=>sql_true(), "!NULL:staff_email"=>"dummy");
foreach ($group_list as $name=>$filter)
{
	$filter=array_merge($filter,$default_filter);
	write_list($filter, "$file_prefix$name");
}
/* Transfer to mail here */
exec( $copy_command );
page_close($silent = true);
exit;


?>
