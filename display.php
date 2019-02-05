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

// clean _GET and _POST
engine_control_array_security();

if (!$control = dewebify_array(unserialize_control($_REQUEST['control']))) { 
	//display.php generally uses the 'control' variable
	//if it is missing, this would indicate a generic child-record handling event
	$control = $_SESSION['LAST_CALLED_CONTROL_VARIABLE'];
} else {
	foreach ($engine['control_pass_elements'] as $key) {
		$tmp_control[$key] = $control[$key];
	}
	$_SESSION['LAST_CALLED_CONTROL_VARIABLE'] = $tmp_control;
}

/* Redirect reg-enabled objects to object_reg, where search for existing matches is performed */

if (($control['action']=='add') and is_enabled('object_reg_search')) {
	if ( ($def=get_def($control['object'])) and is_array($def['registration']) and (orr($_REQUEST['control']['step'],'new')=='new') and (!$_REQUEST['control']['no_object_reg'])) {
		foreach ($tmp_control['rec_init'] as $k=>$v) {
			$jump .= '&rec['.$k.']='.urlencode($v);
		}
		$jump='Location: object_reg.php?object='.$def['object'];
		header($jump);
		page_close();
		exit;
	}
}

if (($control['action']=='list') and $control['format']=='raw' and $control['source']=='quick_browse') {
	//moved hack from engine_list.php, and then from object_query
	//  Adding here for quick_browse, and using the non-standard option 'source' in control

	// small, probably temporary(!) hack for search results
	// if client_id exists in the record, but not in list fields,
	// and it's not in the filter (as a pure client_id=x)
	// , then add it to the list fields
	$def=get_def($control['object']);
	$rec_fields = $def['fields'];
	$tmp_fields = $def['list_fields'];
	$filter = orr($filter,array());
	foreach (array(AG_MAIN_OBJECT_DB.'_id','staff_id') as $key) {
		if (array_key_exists($key,$rec_fields) 
		    && (!in_array($key,$tmp_fields))
		    && (!array_key_exists($key,$filter)
			  || (is_array($filter[$key])))
	//	    && (!in_array($type,array('staff','client')))) 
	) {
			$def['list_fields'][]=$key;
/*
			//get photo into results hack
			$xw = substr($key,0,1);
			if ($xw=='s') {
				$engine[$type]['fields']['customXobject'.$xw] = $engine['staff']['fields']['staff_photo'];
			} else {
				$engine[$type]['fields']['customXobject'.$xw] = $engine[AG_MAIN_OBJECT_DB]['fields']['custom5'];
			}
			array_push($engine[$type]['list_fields'],'customXobject'.$xw);
			//end photo hack
*/
		}
	}
	$control['list']['fields']=$def['list_fields'];
}
// This test is copied (repeated) from engine,
// to avoid calling without required info
// Otherwise people get a (harmless) "error"
// that nonetheless has caused major confusion for users


if (!$control['object'] or !$control['action'] or (!$control['id'] and (!in_array($control['action'],array('add','list'))) )) {
	// return array('message'=>dump_array($control) . "ERROR: engine() must have an action ($action), object ($object) and id ($id) passed.");
	$title='AGENCY';
} else {
	$stuff=engine($control);
	$formatted_title = $stuff['title'];
	$commands   = $stuff['commands'];
	$message    = $stuff['message'];
	$help       = $stuff['help'];
	$output     = $stuff['output'];
	$menu       = $stuff['menu'];
	$title = strip_tags($formatted_title);
}


agency_top_header($commands);

if ($menu) {
	out(div($menu,'engineMenu'));
}

if ($message) {
//	$message = oline().box(bigger(red($message)));
	$message = div($message,'','class="engineMessage"');
}

out(div($formatted_title . $message . $help . $output,'engineMain'));

page_close();

?>
