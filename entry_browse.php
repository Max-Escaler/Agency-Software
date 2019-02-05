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

// FIXME: if search term is entered, should go to QuickSearch
//        currently is just silently ignored

$quiet='Y';
include 'includes.php';
$visitor_fields=array('visitor_name','visit_purpose','visit_type_code','visiting_who','comment');
$def=get_def('entry');
$ev_def=get_def('entry_visitor');
$ef_def=get_def(AG_MAIN_OBJECT_DB);

$c_req=$_REQUEST['control'];
if (!is_array($c_req)) {
    $c_req = unserialize_control($c_req);
}
if ($c_req['list'] and (!is_array($c_req['list']))) {
    $c_req['list']=unserialize_control($c_req['list']);
}
foreach ($engine['control_pass_elements'] as $key) {
        $tmp_control[$key] = $control[$key];
}
$_SESSION['LAST_CALLED_CONTROL_VARIABLE'] = $tmp_control;

/* find current entry locations */
$e_locations=get_generic(array('is_current'=>sql_true()),NULL,NULL,get_def('l_entry_location'));
while ($loc=array_shift($e_locations)) {
	$AG_ENTRY_LOCATIONS[$loc['entry_location_code']]=$loc['description'];
}

//---- find and merge user options ----//
foreach (array_keys($AG_ENTRY_LOCATIONS) as $tmp_key) {
	$tmp_entry_defaults[$tmp_key] = true;
}

$USER_PREFS = $AG_USER_OPTION->get_option('ENTRY_DISPLAY_OPTIONS'); //get entry prefs
$USER_PREFS['entry_location'] = orr($_REQUEST['entry_location'],$USER_PREFS['entry_location'],$tmp_entry_defaults);
$AG_USER_OPTION->set_option('ENTRY_DISPLAY_OPTIONS',$USER_PREFS); //set entry prefs - if changed
$this_entry_location=$USER_PREFS['entry_location'];
if (!$this_entry_location) {
	$temp=$def['fields']['entry_location_code']['lookup'];
	$this_entry_location=sql_assign('SELECT ' . $temp['value_field'] . ' FROM ' . $temp['table'] . ' WHERE is_current LIMIT 1');
}
$this_entry_location_label=$AG_ENTRY_LOCATIONS[$this_entry_location];
if (isset($_REQUEST['enterClientUndo'])) {
	// Delete most recent post
	$d_c=$_REQUEST['enterClientUndo'];
	$d_filter=$_SESSION['ENTRY_BROWSE_UNDO_RECORD'];
	if ($d_c==$d_filter[$_SESSION['ENTRY_BROWSE_UNDO_FIELD']]) {
		if (delete_void_generic($d_filter,$def,'delete',$delete_message,array('deleted_comment'=>'Auto-deleted by entry_browse undo'))) {
			$delete_result = 'Deleted entry record for ' . client_link($d_c) . '@' . datetimeof($d_filter['entered_at']);
		} else {
			$delete_result = oline('Failed to delete entry record for ' . client_link($d_c) . '@' . datetimeof($d_filter['entered_at'])) . 'Got message: ' . $delete_message;
		}
	} else {
		$delete_result=oline('Undo mismatch for ' . client_link($d_c))
		. oline('Session undo_field: ' . $_SESSION['ENTRY_BROWSE_UNDO_FIELD'])
		. 'Entry record: ' . $_SESSION['ENTRY_BROWSE_UNDO_RECORD'];
	}
	unset($_SESSION['ENTRY_BROWSE_UNDO_RECORD']);
	unset($_SESSION['ENTRY_BROWSE_UNDO_FIELD']);
}

if (is_enabled('entry_visitor') and isset($_REQUEST['enterVisitorUndo'])) {
	// Delete most recent post
	$d_c=$_REQUEST['enterVisitorUndo'];
	$d_filter=$_SESSION['ENTRY_VISITOR_BROWSE_UNDO_RECORD'];
	if ($d_c==$d_filter[$_SESSION['ENTRY_VISITOR_BROWSE_UNDO_FIELD']]) {
		if (delete_void_generic($d_filter,$ev_def,$delete_message,array('deleted_comment'=>'Auto-deleted by entry_browse undo'))) {
			$delete_visitor_result = 'Deleted visitor record for ' . $d_filter['visitor_name'] .'@'. datetimeof($d_filter['entered_at']);
		} else {
			$delete_visitor_result = oline('Failed to delete visitor record for ' . $d_filter['visitor_name'] . '@' . datetimeof($d_filter['entered_at'])) . 'Got message: ' . $delete_message;
		}
	}
	unset($_SESSION['ENTRY_VISITOR_BROWSE_UNDO_RECORD']);
	unset($_SESSION['ENTRY_VISITOR_BROWSE_UNDO_FIELD']);
}

if (isset($_REQUEST['enterClient'])) {
	// Client has been entered and sent from previous page
	$e_c=$_REQUEST['enterClient'];
	$id_field=$_REQUEST['enterClientField'];
	$skip=false;
	if (is_numeric($e_c) and $e_c==intval($e_c) and preg_match('/^([a-z_0-9])+$/i',$id_field)
		// don't allow same client to be entered twice, consecutively
		and($_SESSION['ENTRY_BROWSE_LAST_RECORD'][$id_field] != $e_c)) {
		// Find and show client notes flagged for this location	
		$note_filter=array(
			$ef_def['id_field']=>$e_c,
			'FIELD>=:COALESCE(flag_entry_until,current_timestamp)'=>'current_timestamp',
			'ARRAY_CONTAINS:flag_entry_codes'=>array($this_entry_location));
		$notes=get_generic($note_filter,'','','client_note');
		while ($note=array_shift($notes)) {
			$note_f[]=$note['note'] . smaller(' ('.elink('client_note',$note['client_note_id'],'view record','target="_blank"').')');
		}
		$note_f=$note_f
				? (html_heading_1('There are message(s) regarding this ' .$ef_def['singular'] .':')
					. div(oline().implode(oline() . hrule() . oline(),$note_f).toggle_label('View message...'),'','class="hiddenDetail"'))
				: '';
		$rec=array();
		$cont=array('action'=>'add','object'=>'entry','id'=>'dummy');
		blank_generic($def, $rec,$cont);
		$rec[$id_field]=$e_c;
		$rec['entered_at']=datetimeof('now','SQL');
		$rec['entry_location_code']=$this_entry_location;
		$rec['added_by']=$rec['changed_by']=$UID;
		$rec['sys_log']='auto-added by entry_browse';
//outline("Trying $e_c, override =" . $_REQUEST['enterClientOverride']. ", ineligible? " . call_sql_function('entry_ineligible',$e_c));
		if ((call_sql_function('entry_ineligible',$e_c)==sql_true()) and (!$_REQUEST['enterClientOverride']=='Y') ) {
			$override_result=
				oline(client_link($e_c)) 
				.oline(' Please see a staff person before entering.')
				.oline( ($a=enrollments_f(client_filter($e_c)))
					? ('Enrolled in '. $a)
					: 'No recovery circle')
			.' ('
			.hlink($_SERVER['PHP_SELF'] .'?enterClient='.$e_c.'&enterClientField='.$id_field.'&enterClientOverride=Y','Staff only:  Click to override') . ')';
//outline("I am asking for override");
			$skip=true;
		}
		if (post_generic($rec,$def,$message) and (!$skip)) {
			$_SESSION['ENTRY_BROWSE_UNDO_RECORD']=$_SESSION['ENTRY_BROWSE_LAST_RECORD']=$rec;
			$_SESSION['ENTRY_BROWSE_UNDO_FIELD']=$id_field;
			$undo_link=hlink($_SERVER['PHP_SELF'].'?enterClientUndo='.$e_c,smaller(' (Undo)'));
			$rec=sql_fetch_assoc(get_generic(client_filter($e_c),'','',$ef_def));
			$post_result=oline('Posted ' . $def['singular'] .' for ' . client_link($e_c) . ' ' . $undo_link,2)
						. bigger("Welcome " . $rec['name_first'].'!!');
		} elseif (!$skip) {
			unset($_SESSION['ENTRY_BROWSE_UNDO_RECORD']);
			unset($_SESSION['ENTRY_BROWSE_UNDO_FIELD']);
			$post_result='Failed to post ' . $def['singular'] . 'for ' . client_link($e_c);
		}		
	} else {
		unset($_SESSION['ENTRY_BROWSE_UNDO_RECORD']);
		unset($_SESSION['ENTRY_BROWSE_UNDO_FIELD']);
	}
}

if (is_enabled('entry_visitor') and isset($_REQUEST['rec'])) {
// Enter Visitor
	$ev_id_field=$ev_def['id_field'];
	foreach( $visitor_fields as $x) {
		$ev_rec[$x]=dewebify($_REQUEST['rec'][$x]);
	}
	if ($ev_rec['visiting_who']=='-1') {
		unset($ev_rec['visiting_who']);
	}
	$ev_rec['entered_at']=datetimeof('now','SQL');
	$ev_rec['added_by']=$rec['changed_by']=$UID;
	$ev_rec['sys_log']='auto-added by entry_browse';
	$ev_rec['entry_location_code']=$this_entry_location;
	if (!valid_generic($ev_rec,$ev_def,$message,'add')) {
		$ev_post_result .= $message;
		$continue_ev_rec=$ev_rec;
		unset($continue_ev_rec['sys_log']);
	} elseif ($new_ev_rec=post_generic($ev_rec,$ev_def,$message) ) {
			$e_v=$ev_rec['entry_visitor_id']=$new_ev_rec['entry_visitor_id'];
			$_SESSION['ENTRY_VISITOR_BROWSE_UNDO_RECORD']=$_SESSION['ENTRY_VISITOR_BROWSE_LAST_RECORD']=$ev_rec;
			$_SESSION['ENTRY_VISITOR_BROWSE_UNDO_FIELD']=$ev_id_field;
			$ev_undo_link=hlink($_SERVER['PHP_SELF'].'?enterVisitorUndo='.$e_v,smaller(' (Undo)'));
			$rec=sql_fetch_assoc(get_generic(array('entry_visitor_id'=>$e_v),'','','entry_visitor'));
			$ev_post_result=oline('Posted ' . $def['singular'] .' for ' . $ev_rec['visitor_name'] . ' ' . $ev_undo_link,2)
						. bigger("Welcome " . $ev_rec['visitor_name'].'!!');
		} else {
			unset($_SESSION['ENTRY_VISITOR_BROWSE_UNDO_RECORD']);
			unset($_SESSION['ENTRY_VISITOR_BROWSE_UNDO_FIELD']);
			$post_result='Failed to post ' . $def['singular'] . 'for ' . $ev_rec['visitor_name'];
	}
//	outline(dump_array($ev_rec));
}	
	
$control=array(
	'action'=>'list',
	'object'=>$def['object'],
	'id'=>'list',
	'title'=>'Listing recent ' . $def['plural'],
	'page'=>$_SERVER['PHP_SELF'],
	'list'=>array('filter'=>array('IN:entry_location_code'=>array_keys($USER_PREFS['entry_location'])),
	'fields'=>array('entered_at','entry_location_code','client_id'),
	'display_add_link'=>false,
	'no_controls'=>false));

foreach ($c_req['list'] as $key=>$value) {
	$control['list'][$key]=$value;
}

$ef_step=orr($ef_step,'continue');  // don't think this does anything?
$ef_rec=orr($ef_rec,array('client_id'=>NULL));
$ef_control=array('action'=>'add','object'=>'client','id'=>'dummy');
engine_process_quicksearch($ef_step,$ef_rec,$ef_control);
$entry_form='Record a ' . $ef_def['singular'] . '\'s '.$def['singular'] .': '.formvartext('enterClient','','class="enterClient"').hiddenvar('enterClientField','client_id') . button('submit');
$entry_form='Record a ' . $ef_def['singular'] . '\'s '.$def['singular'] .': '.formvartext('enterClient','','class="enterClient"').hiddenvar('enterClientField','client_id') . button('submit');
$entry_form=form($entry_form,'','','class="enterClient"');

if (is_enabled('entry_visitor')) {
	$ev_def=get_def('entry_visitor');
	$ev_control=array('action'=>'add');
	$ev_dummy=array(); // for rec-init
	$ev_rec=blank_generic($ev_def, $ev_dummy,$ev_control);
	foreach($continue_ev_rec as $k=>$v){
		$ev_rec[$k]=$v;
	}
	foreach($ev_def['fields'] as $k=>$dummy) {
		if (!in_array($k,$visitor_fields)) {
			$ev_def['fields'][$k]['display_add']='hide';
		}
	}
	$ev_form=form_generic($ev_rec,$ev_def,$ev_control);

	$entry_visitor_link=oline('OR, ',2) .hlink('#','Record a Visitor','','id="enterVisitorLink"');
	$entry_visitor_form=$ev_form . button('submit');
	$entry_visitor_form=oline($entry_visitor_link) . form($entry_visitor_form,'','','class="enterVisitor" id="enterVisitorForm"');
}
$entries=call_engine($control,'',true,false,$DUMMY,&$PERMISSION);
//$title = 'Viewing ' . ucfirst($def['plural']) . ' for ' . implode(', ',array_keys($USER_PREFS['entry_location']));
//$title = 'Viewing Front Door ' . ucfirst($def['plural']);
$title = 'Welcome to ' . org_name() . ' ('.$this_entry_location_label.')';
$commands=array(cell(show_pick_entry($AG_ENTRY_LOCATIONS,$USER_PREFS['entry_location']),'class="pick"'));
agency_top_header($commands);	
$out .= html_heading_1($title)
	. ($post_result ? div($post_result,'','id="postClientResult"') : '')
	. ($delete_result ? div($delete_result,'','id="deleteClientResult"') : '')
	. ($ev_post_result ? div($ev_post_result,'','id="postVisitorResult"') : '')
	. ($delete_visitor_result ? div($delete_visitor_result,'','id="deleteVisitorResult"') : '')
	. ($override_result ? div($override_result,'','id="overrideClientResult"') : '')
	. ($note_f ? div($note_f,'','id="clientNote"') : '')
	. div($entry_form,'','id="enterClientForm"')
	. $entry_visitor_form
	// comment out line below to not display recent entries
	//. oline() . $entries
;
out($out);
page_close();

?>

