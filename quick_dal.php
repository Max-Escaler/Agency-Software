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

$qdid = $_REQUEST['qdid']; //a unique id to facilitate tabbed browsing
$_SESSION[$qdid.'_multi_add_object'] = $object = orr($_REQUEST['object'],$_SESSION[$qdid.'_multi_add_object']);
if (!$def = get_def($object)) {
	log_error('No object passed to '.__FILE__);
	exit;
}
$title = 'Quick '.$def['singular'].' Entry';

if (!engine_perm(array('object'=>$def['object'],'action'=>'add'))) {

	/*
	 * Check permissions
	 */
	$out = alert_mark('You don\'t have the proper permissions for '.$title);
	agency_top_header();
	out(html_heading_1($title).$out);
	page_close();
	exit;

}

function generate_multi_add_id()
{
	$_SESSION['current_multi_add_id']++;
	return $_SESSION['current_multi_add_id'];
}

$rec_init_tmp = $_REQUEST['rec_init'];

//if ALL fields have been selected, only 1 record is presented
// FIXME: this used to work, but the counts get messed up by timestamp fields being broken into 2 fields
// if (is_array($rec_init_tmp) and (count(array_keys(array_filter($rec_init_tmp))) == count($def['multi_add']['init_fields']))) {
//  	$def['multi_add']['number_of_records'] = 1;
// }

/*
 * Merge working versions of $rec_init
 */
$def['fn']['process']($rec_init,$rec_init_tmp,$def);

$reset = $_REQUEST['reset'];
$step  = $_REQUEST['step'];

if ($reset or be_null($qdid)) {

	/*
	 * Reset to initial form
	 */
	$qdid = generate_multi_add_id();
	$rec_init = $_SESSION['qDAL_rec_init'.$qdid] = array();
	$records = $_SESSION['RECS'.$qdid] = array();

} else {

	$_SESSION['qDAL_rec_init'.$qdid] = $rec_init = orr(array_filter(orr($rec_init,array())),$_SESSION['qDAL_rec_init'.$qdid],array());
	
	$def = $def['fn']['multi_hide_fields']($def,$rec_init);
 	$m_add = $def['multi_add'];
 	if ($m_add['common_fields_required']) {
 		foreach ($m_add['common_fields'] as $f ) {
 			$def['fields'][$f]['null_ok']=false;
 			}
 		}

	/*
	 * Merge working versions
	 */
	$_SESSION['RECS'.$qdid] = $records = multi_record_process_generic($def,$qdid);

}

$errors = array();
$control = array('action' => 'add');

global $AG_AUTH;
if ($step=='confirmed' and (!$AG_AUTH->reconfirm_password())) {

	/*
	 *Failed password check
	 */

	$message .= 'Incorrect password for '.staff_link($UID);
	$step = 'submit';

} elseif ($step=='confirmed' and valid_multi_record_generic($records,$def,$message,$errors,$rec_init)) {

	if ($records = $def['fn']['post_multi_records']($records,$def,$message,$rec_init)) {

		$new_ids = array();
		foreach ($records as $rec) {

			/*
			 * Grab primary keys from new records to fetch back in list box
			 */
			$new_ids[] = $rec[$def['id_field']];

		}

		$control = array('action'=>'list',
				     'object'=>$def['object'],
				     'page'=>'display.php',
				     'list'=>array('filter'=>array('IN:'.$def['id_field']=>$new_ids),
							 'fields'=>array_merge($def['list_fields'],array('client_id'))));
		$ocount = count($records);
		$message = html_heading_3('Posted '.$ocount . ' '.($ocount > 1 ? $def['plural'] : $def['singular']).':')
			. call_engine($control,$control_array_variable='control',$NO_TITLE=true,$NO_MESSAGES=true,$TOTAL_RECORDS,$PERM);

		/*
		 * Starting again, clearing out session variables and keys
		 */
		$_REQUEST['rec_init_defaults'] = $old_rec_init = $rec_init;
		$rec_init = $_SESSION['qDAL_rec_init'.$qdid] = array();
		$_SESSION['RECS'.$qdid] = array();
		$step = null;
		$control=array('action'=>'add');


		/*
		 * call any after-post output (DAL calls a PATH tracking form, and exits)
		 */
		$message .= $def['fn']['multi_add_after_post']($message,$rec,$def,$old_rec_init);

		$message .= oline('',5);

	}

}

if ($step=='submit' and valid_multi_record_generic($records,$def,$message,$errors,$rec_init)) {

	/*
	 * Review records
	 */

	$out = ($message ? div($message,'',' class="error"') : '')
		. html_heading_3($def['fn']['multi_add_title']($def,$rec_init)) 
		. html_heading_4('Please review these records carefully. Once they are posted, they cannot be edited.','class="error"')
		. $def['fn']['view_list']($records,$def,$control,$rec_init)
		. formto('','',$AG_AUTH->get_onsubmit(''))
		. oline(red('Enter password for '.staff_link($GLOBALS['UID']).' to confirm: ')
			  . $AG_AUTH->get_password_field())
		. button('Confirm and post','','','','','class="engineButton"')
		. hiddenvar('step','confirmed')
		. hiddenvar('qdid',$qdid)
		. hiddenvar('object',$object)
		. hlink($_SERVER['PHP_SELF'].'?step=continue&object='.$object.'&qdid='.$qdid,'Return to form','','class="linkButton"')
		. formend();

 } elseif ($step=='continue' or (!be_null($rec_init) && valid_generic($rec_init,$def,$message,'add') && $reset==false)) {
	
	 /*
	  * Generate form based off choices from initial form
	  */

	if (!$records) {

		/*
		 * Create default records merged with rec_init from initial form
		 */
		$records = $_SESSION['RECS'.$qdid] = $def['fn']['multi_add_blank']($def,$rec_init);

	}
	
	$total = count($records);
	

	foreach ($rec_init as $field=>$value) {

		$ini_reset_query .= '&rec_init_defaults['.$field.']='.$value;

	}

	$out = div($message,'',' class="error"')
		. html_heading_3($def['fn']['multi_add_title']($def,$rec_init))
		. formto()
		. $def['fn']['form_list']($records,$def,$control,$errors,$rec_init)
		. button('Submit','','','','','class="engineButton"')
		. hlink($_SERVER['PHP_SELF'].'?reset=1&object='.$object.$ini_reset_query,'Reset','','class="linkButton"')
		. hiddenvar('step','submit')
		. hiddenvar('qdid',$qdid)
		. hiddenvar('object',$object)
		. formend();

} else {

	/*
	 * Present initial form
	 */
	$defaults = orr($_REQUEST['rec_init_defaults'],$rec_init);
	$out = formto() 
		. div($message,'',' class="error"')
		. $def['fn']['init_form']($def,$defaults,$control)
		. hiddenvar('qdid',$qdid)
		. hiddenvar('object',$object)
		. button('Submit','','','','','class="engineButton"')
		. formend();

}

agency_top_header();
out(html_heading_1($title).$out);

page_close();

?>
