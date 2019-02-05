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

//check for command-line running
if (PHP_SAPI=='cli') {
	$off = dirname(__FILE__).'/';
	ini_set('memory_limit','64M');
	$AG_FULL_INI = false;
	include 'command_line_includes.php';
	$res = update_engine_array(false,$_SERVER['argv'][1]);
	if ($res) {
		outline('SUCCESS!');
		outline(center('Go to '. link_agency_home()));
	} else {
		outline('FAILED TO UPDATE ENGINE ARRAY');
	}

} else {

	$AG_FULL_INI = false;
	include 'includes.php';

	if ($table=$_REQUEST['UPDATE_ENGINE_OBJECT'])
	{
		$_SESSION['UPDATE_ENGINE_OBJECT']=$table;
		if ($table=='UPDATE_ALL')
		{
			$table=null;
		} else {	
			$table=array($table);
		}
	}	

	//GENERATE ENGINE ARRAY FROM CONFIG FILES
	$res = update_engine_array(true,NULL,$table);
	
	if ($res) {

		$start=getmicrotime();
		$tmp=sql_fetch_assoc(agency_query('SELECT * FROM '.AG_ENGINE_CONFIG_TABLE,array('val_name'=>AG_ENGINE_CONFIG_ARRAY)));
		$new_engine=unserialize($tmp['value']);
		$end=getmicrotime();
		outline();
		outline('Execution time to load new array from DB: '. ($end-$start));
		outline ('Engine Array Succesfully Updated.');
		
		outline(center(bigger(red(bold('SUCCESS!')),8)));
		outline(center(link_admin() .' or '. link_agency_home()));

	} else {

		outline('Update Failed.');

	}

	page_close();
}
?>
