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

function log_link( $idnum, $label="lookup" )
{        // Doesn't do Validate or Match
	if (!$idnum) { return false; }
	$def=get_def('log');
	$log_table=$def['table'];
	$id_field=$def['id_field'];
	if ($label=="lookup")
	{
		//$label =sql_assign("SELECT SUBSTRING(COALESCE(subject,log_text) FROM 0 FOR 50) FROM $log_table",array($log_table . "_id" => $idnum));
			$label =object_label('log',$idnum);
	}
	return elink('log',$idnum,$label);
}

/*
function engine_record_perm_log($control,$rec,$def) {

	global $UID;

	if ($control['action']=='add') { return true; }
	if (isset($rec['_client_links'])) {
		$clients = sql_to_php_array($rec['_client_links']);
	} else {
		$clients = get_clients_for_log( $rec['log_id'],'NF');
	}

	$clients = array_filter($clients);

	if (isset($rec['_case_mgr_id'])) {
		$cm = sql_to_php_array($rec['_case_mgr_id']);
	} else {
		$cm = get_staff_clients($clients);
	}

	if (isset($rec['_staff_alert_ids'])) {
		$staff_alert=sql_to_php_array($rec['_staff_alert_ids']);
	} else {
		$staff_alert=get_alerts_for_log( $rec['log_id'],'NF' );
	}

	$in_logs=which_logs($rec,'log_');

	$perm = (!$in_logs) // client_only entry
			|| has_perm($in_logs,'R') // log_specific permission
			|| in_array($UID,$staff_alert)  // Flagged to user's attention
			|| in_array($UID,$cm); //staff assigned to client
	return $perm;
}
/*
function db_links_array($arr)
{
	foreach ($arr as $key=>$link)
	{
		$arr[$key]=stripslashes($link);
	}
	return $arr;
}
*/
/* log/engine functions */

/*
function generate_list_medium_log($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	return generate_list_long_log($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num);
}
*/

function generate_list_long_log($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	if (!in_array($control['format'],array('long','medium'))) {
		return generate_list_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,$rec_num);
	}

	$pos=$control['list']['position'];
      $mx=$control['list']['max'];

      while ( $x<$mx and $pos<$total) {
		$a = sql_to_php_generic(sql_fetch_assoc($result,$pos),$def);
		$link = link_engine(array('object'=>$def['object'],'id'=>$a[$def['id_field']]),'View');
		$out .= div(view_log($a,$def,'view',$control) . html_heading_6($link)); 
		$pos++;
		$x++;
	}
	return $out . list_links($max,$position,$total,$control,$control_array_variable);
}
/* end log/engine functions */

function show_log_types($picks="",$formvar="",$sep="")
{
// create options to choose which logs to view
	$formvar=orr($formvar,"pick_logs");
	$picks=orr($picks,array());
	return do_checkbox_sql('SELECT log_type_code AS value,description AS label FROM l_log_type',$formvar,$picks);
}

function show_pick_logs()
{
// allow user to select which logs to view
	global $SHOW_LOGS,$logs_per_screen,$DISPLAY;
	$output = formto($_SERVER['PHP_SELF'])
		. table(row(leftcell(smaller("Jump to date: " . formvartext('log_jump_date','','size=7')))
		. cell(  
					     oline(smaller(hard_space("Logs per page "))
					     . formvartext("logs_index_count",$logs_per_screen,"size=5"))
					     . rightcell(formcheck("show_photos",$DISPLAY["photos"]) 
					     . smaller(hard_space(" Show Photos"))))
//					     . formcheck("pick_logs[flagged_only]",$SHOW_LOGS["flagged_only"]) 
//					     . smaller(hard_space(' "Red-flagged" Only'))
						,' style="white-space: nowrap;"')
		//. smaller(hard_space("Logs to view: ") . show_log_types($SHOW_LOGS))
		. row(cell(''))
		. row(cell(smaller(hard_space("Logs to view: ") . show_log_types($SHOW_LOGS))
		. hiddenvar('$control[action]',"pick_logs")
		. button("View","SUBMIT") . smaller(help("Logs","","Help!",'class="fancyLink"')) 
		. formend(),'colspan=2'))
			  ,'',' cellpadding="0" cellspacing="0" width=100% class="pick"');
	return $output;
}

function view_log($rec,$def,$action,$control='',$control_array_variable='control')
{
	foreach ($rec as $key => $value) {
		if (in_array($key,array('log_text','subject'))) {
			$def['fields'][$key]['data_type'] = 'text';
			$$key = value_generic($value,$def,$key,'view');
		}
	}


	//post times
	$added_at    = dateof($rec['added_at']).' '.timeof($rec['added_at']);
	$post_times = 'Posted at '.bold($added_at);

	if ($occurred_at = $rec['occurred_at']) {
		$occurred_at = dateof($occurred_at).' '.timeof($occurred_at);
		$post_times .= oline().'Event time was '.bold($occurred_at);
	}
	$c_def=get_def(AG_MAIN_OBJECT_DB);
	$client_refs = ($a=object_references_f('log',$rec['log_id'],NULL,'','to',array('client')))
	? oline($c_def['plural'] . ':') . $a
	: '';

	$staff_alerts = ($a=staff_alerts_f('log',$rec['log_id']))
		? oline(bold('Staff Alerts:')).$a
		: '';

	$in_logs = smaller(implode(oline(),$rec['log_type_code']));
/*
	foreach( $rec['log_type_code'] as $a) {
		$in_logs.=html_list_item($a);
	}
	$in_logs = $in_logs ? html_list($in_logs) : '';
*/
	$title = (($action=='list')
			 	? bold(bigger('Log '.$rec['log_id'].' ',2)) 
				: '' )
			 .'(logs: '.$in_logs.')';
	$out = //oline($title,2)
		//refs/author info
		  table(row(
				cell($in_logs)
				. cell(oline('Posted by '.staff_link($rec['added_by'])) . $post_times,'class="info"')
				. ($client_refs ? cell($client_refs,'class="client"') : '')
				. ($staff_alerts ? cell($staff_alerts,'class="staff"') : '')
				),'','class="logHeader"')

		. div(html_heading_2($subject,' class="logSubject"')
			. $log_text,'','class="logText generalTable"');
	return $out;
}

?>
