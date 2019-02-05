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

/*
 * File for weird and hacky "objects"
 *
 * Currently, config_file & def array identical and thus redundant
 * If we add more of these, they should be genericized
 */

function get_config_file($filter) {

	// This only supports view, so should only ever
	// be a simple filter, id=>object
	$object=array_pop($filter);
	if ($filename= config_object_file_name($object)
	and ($c_f = file_get_contents( $filename ))) {
		return array(array('object'=>$object,'config_file_text'=>trim($c_f)));
	} else {
		return false;
	}
}

function get_def_array($filter) {
	/* copied from get_config_object, see comments there */
	$object=array_pop($filter);
	if ($def = get_def($object) ) {
		$table=$def['table'];
		$meta=array_to_table(sql_metadata($table));
		return array(array('object'=>$object,'def_array_text'=>dump_array($def),'table_info'=>$meta));
	} else {
		return false;
	}
}

?>
