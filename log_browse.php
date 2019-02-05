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
$l_def=get_def('log');
$log_table=$l_def['table'];
$log_types=sql_fetch_column(agency_query('SELECT log_type_code FROM l_log_type'),'log_type_code');
$c_req=$_REQUEST['control'];
if (!is_array($c_req)) {
	$c_req = unserialize_control($c_req);
}
if (!is_array($c_req['list'])) { 
	$c_req['list']=unserialize_control($c_req['list']);
}

//outline("Got array " . dump_array($log_types));
//outline("Test array: " . dump_array(array(4,5,6)));
/*
 * Basic log control page.
 * Variables passed are:
 * 
 * $action ( 
 *		show, show_client_logs -- All view log actions
 *		forward, back, first, last, browse -- All log index actions )
 * 
 * $LOG_POS ( position in log index ) (for browse) (now session variable)
 * $id ( ID # of log entry ) (for view)
 * $cid ( client ID for show_client_logs )
 */

//-------set session vars--------//

$LOG_POS     = isset($_SESSION['LOG_POS']) ? $_SESSION['LOG_POS'] : -1; // if not set, force invalid, will set to end
$LOG_FILTER  = $_SESSION['LOG_FILTER'];
$LAST_ACTION = $_SESSION['LAST_ACTION'];
$LAST_ID     = $_SESSION['LAST_ID'];
$SHOW_LOGS   = $_SESSION['SHOW_LOGS'];
$DISPLAY     = $_SESSION['DISPLAY'];
$CID         = $_SESSION['LOG_CID'] = orr($_REQUEST['cid'],$_SESSION['LOG_CID']);

//------set global requested vars-----//

$pos           = $c_req['list']['position'];

//outline("Is st? " . isset($c_req['format']) ? 'Yes' : 'No');
$_SESSION['FORMAT']= isset($c_req['format']) ? $c_req['format'] : $_SESSION['FORMAT'];
//outline("req-list: " . dump_array($c_req));
//outline("Got pos $pos");

$pick_logs     = isset($_REQUEST['pick_logs']) ? array_keys($_REQUEST['pick_logs']) : '';
$client_select = $_REQUEST['client_select'];
$valid_client  = $_REQUEST['valid_client'];
$valid_id      = $_REQUEST['valid_id'];
$staff_select  = $_REQUEST['staff_select'];
$staff_add     = $_REQUEST['staff_add'];
$valid_staff   = $_REQUEST['valid_staff'];
$action        = $c_req['action'];
$id            = $c_req['id'];
$id = trim(orr($id,$LAST_ID));
//outline("Action = $action, id = $id");

// FIXME:  I'm not sure if this is being used, or quite what it does...
//process_quick_search('N',false); // "N" to Not stop after showing results, false to not auto-forward to client page
/*
//-------add clients--------//
if ( isset($client_select) ) {

	outline( alert_mark() 
		. 'You have requested to add a reference to ' 
		. client_link($client_select) . " to this log (#{$id}).  "
		. hlink($_SERVER['PHP_SELF']."?valid_client=$client_select&valid_id={$id}&action=show&id={$id}",'Click here to confirm'),2);
	$action = 'view';

}

//-------add staff-------//
if ( isset($staff_select) && ($staff_add > 0) ) {

	outline( alert_mark() 
		. 'You have requested to add an alert for ' 
		. staff_link($staff_add) . " to this log (#{$id}).  "
		. hlink($_SERVER['PHP_SELF']."?valid_staff=$staff_add&valid_id={$id}&action=show&id={$id}",'Click here to confirm'),2);
	$action='view';

}
*/

//the way our global searches and such work, these all must be unset here.
$staff_add     = $_REQUEST['staff_add'] = null;
$client_select = $_REQUEST['client_select'] = null;
$staff_select  = $_REQUEST['staff_select'] = null;

if (isset( $_REQUEST['logs_index_count']) and intval($_REQUEST['logs_index_count'])>0) {
	$req_max=intval($_REQUEST['logs_index_count']);
}

//---- find and merge user options ----//
$USER_PREFS = $AG_USER_OPTION->get_option('LOG_DISPLAY_OPTIONS'); //get log prefs

$USER_PREFS['pick_logs']   = $pick_logs   = orr($pick_logs,$USER_PREFS['pick_logs'],null);
$USER_PREFS['show_photos'] = $show_photos = orrn($_REQUEST['show_photos'],$USER_PREFS['show_photos'],null);

$logs_per_screen = $USER_PREFS['logs_per_screen'] = $_SESSION['LOGS_PER_SCREEN'] = $LOGS_PER_SCREEN = orr($req_max,
																	    $_SESSION['LOGS_PER_SCREEN'],
																	    $USER_PREFS['logs_per_screen'],
																	    25);
// First time, select all logs for user
if (isset($pick_logs) or ($first_run=(!isset($LOG_FILTER)))) {

	// set session variable
	$_SESSION['SHOW_LOGS'] = $SHOW_LOGS = $pick_logs;
//outline("Pick logs = " . dump_array($pick_logs));
	unset( $LOG_FILTER); // start with clean copy

	// and update filter for record selection
	foreach($GLOBALS['log_types'] as $key) {
		if (in_array($key,$SHOW_LOGS) or $first_run) {
			$LOG_FILTER['ARRAY_CONTAINS:log_type_code'][]=$key;
		}
	}

	/*
	 * $SHOW_LOGS includes "flagged_only", for the was_ fields
	 * This needs to get interpreted as a filter selection
	 */
/*
	if ($SHOW_LOGS['flagged_only']) {

		$LOG_FILTER['was_dummy'] = array('was_assault_staff'  => sql_true(),
							   'was_threat_staff'   => sql_true(),
							   'was_assault_client' => sql_true(),
							   'was_police'            => sql_true(),
							   'was_medics'         => sql_true(),
							   'was_bar'            => sql_true(),
							   'was_drugs'          => sql_true());

	} else {

		unset ($LOG_FILTER['was_dummy']);

	}
*/
	// Note--no break here, as it passes through to browse.

	$DISPLAY['photos'] = $show_photos = $USER_PREFS['show_photos'] =
		orr($_REQUEST['show_photos'] ? 'Y' : '',$USER_PREFS['show_photos']);
	$_SESSION['DISPLAY']    = $DISPLAY;
	$_SESSION['LOG_FILTER'] = $LOG_FILTER;
}

/*
 * set log prefs - if changed
 */
$AG_USER_OPTION->set_option('LOG_DISPLAY_OPTIONS',$USER_PREFS);

$log_count = count_rows($log_table,$LOG_FILTER);
$logs_per_screen=min($log_count,$logs_per_screen); 

if (isset($_REQUEST['log_jump_date']) and ($tmp_jump=dateof($_REQUEST['log_jump_date'],'SQL'))) {
	$tmp_filter = $LOG_FILTER;
	$tmp_filter['<: added_at']=$tmp_jump;
	$pos = count_rows($log_table,$tmp_filter);
}

/*
if ( isset($valid_client) ) {

	post_client1($valid_id,$UID,$valid_client);
	$assigns = array_unique(get_staff_clients(array($valid_client),true));

	foreach($assigns as $assign) {

		if ($assign) {

			post_staff1($valid_id,$UID,$assign);

		}

	}

}

if ( isset($valid_staff) ) {

	post_staff1($valid_id,$UID,$valid_staff);

}
*/
$action = orr($action,$LAST_ACTION,'list');
//outline("Aection = $action");
//outline("Switching on action $action");
switch( $action ) {

	/* 
	 * view-mode actions
	 */
	 
 case 'view' : 
	 
	 $view_filter=array('log_id'=>$id);
	 $mode ='view';
	 break;
	 
 case 'show_client_logs' :
	 	
	 $view_filter=array('log_id'=>$CID);
	 $page_title = 'Displaying all logs for ' . client_link($CID) . "(ID # $CID)";
	 $mode  = 'view';
	 break;

	/*
	 *browse-mode actions
	 */
 case 'list' :
	 if (isset($pos) && $pos >=0 && $pos <= max($log_count,0) ) { 

		// if passed a _valid_ pos variable, set session var $LOG_POS to it.
		$LOG_POS = $pos;

	}

	if ( ! (($LOG_POS >=0) and ($LOG_POS < $log_count ) )) {
		// if invalid LOG_POS, set to end of log.
 		//$LOG_POS = $log_count - $logs_per_screen;
		// Set to beginning, since newest first
		$LOG_POS=0;
	}
	if ($LOG_POS-min($log_count,$logs_per_screen)  < 0) {
		$LOG_POS=0;
	}
	if ($LOG_POS > $log_count-$logs_per_screen) {
		$LOG_POS=$log_count-$logs_per_screen;
	}
//outline("In list: pos=$pos,log_count=$log_count,lps=$logs_per_screen, set LOG_POS TO $LOG_POS");

	$multi = true;
	$mode  = 'list';

	break;

case 'setoflogs' : // this is for a search set

	$page_title = 'Log Search Results';

	$mode  = 'list';
	$multi = true;

	break;

}

//outline("SWitching on mode $mode");
switch ($mode) {

 //case 'view' :
 case 'disabled view' :
	 $a = get_generic($view_filter,NULL,NULL,$l_def);
	 if ( !$a || (count($a)<1)) {

		 $commands   = array(bottomcell(log_view_navbar( $id,'bogus','bogus' )));
		 $page_title = "Requested Log ($id) Does Not Exist";
		 $out        = oline($page_title);

	} else {

		$page_title = oline(bigger(bold("Log $id")));

		$commands = $multi ? array() : array(log_view_navbar($id));
		$out .= log_show( $a, $DISPLAY['photos'] );

		$mo_def=get_def(AG_MAIN_OBJECT_DB);
		$mo_noun=$mo_def['singular'];

		$client_select = formto()
			. hiddenvar('id',$id)
			. client_selector('N','Search to add '.$mo_noun.':','N')
			. formend();
				
		$staff_select = formto()
			. hiddenvar('id',$id)
			. staff_selector('N','N')
			. formend();

		array_push($commands,bottomcell($client_select));
		array_push($commands,bottomcell($staff_select,'class="staff" style="text-align: center;"'));

	 }

	break;

case 'list' :
case 'view' :
//outline("In browse, LOG_ILTE = " . dump_array($LOG_FILTER). " viewfilter= " . dump_array($view_filter) . " a=$a");
//outline("log_pos $LOG_POS");

	$page_title= $LOG_FILTER ? 
		'Viewing log entries ' . ( $LOG_POS+1 ) .'-->' .(min($LOG_POS+$logs_per_screen,$log_count))
		: 'Select Logs to View';
	$add_link= link_engine(array('action'=>'add','object'=>'log'), 'Add new log');
	$our_title = "$page_title " . smaller("($log_count logs total) ");
	$control=array(
		'action'=>$mode,
		'object'=>'log',
		'id'=>orr($id,'list'),
		'page'=>$_SERVER['PHP_SELF'],
		'format'=>$_SESSION['FORMAT'],
		'list'=>array('filter'=>$LOG_FILTER,
					'position'=>$LOG_POS,
					'max'=>$logs_per_screen,
					'display_add_link'=>true,
					'no_controls'=>false));

	$nav_links=list_links($logs_per_screen,$LOG_POS,$log_count,$control,'control');
	$commands =array($commands,cell(div(toggle_label("settings...") . show_pick_logs(),'','class="hiddenDetail"'),'class="pick"'));
	if (count($log_types) == 0 ) {
		$lt_def=get_def('l_log_type');
		$msg = oline('The log system cannot be used until one or more log types are created.');
		$msg .= has_perm($lt_def['perm_add'],'W')
				? 'You can ' . add_link('l_log_type','add a log type record now')
				: 'You don\'t seem to have appropriate permissions to add a log type record.  Please ask your system administrator to do so.';
		$out .= alert_mark($msg);
	} elseif (!isset($LOG_FILTER)) {
		//FIXME: this shouldn't happen and can probably be removed.
		//If not set, $LOG_FILTER set to all logs earlier in code
		$out .= alert_mark('Open Settings to Select Log(s) to view');
	} elseif (isset($LOG_FILTER) && ($log_count==0)) {
		$out .=oline(alert_mark('Your selection contains no log entries')) . bigger(bold($add_link));
	} elseif ($LOG_FILTER) { // ??? (OK, if no LOG_FILTER, user needs to select logs first)

//toggle_query_display();
	$content = call_engine($control,'',true,false,$DUMMY,$DUMMY_PERMISSION);
		$out .= center(oline(bold(bigger($our_title)))
			. oline($nav_links))
			. oline( $content );
//outline("TOTAL_RECORDS=$LOG_POS");
	}

}

/*
 * Done with browse or view, create output
 */
$title = strip_tags($page_title);
agency_top_header($commands);
out( $out );

$LAST_ACTION = $action;
$LAST_ID     = $id;

page_close();

//--------setting session vars here--------//
/*
 * A just-in-case, better safe than sorry, saving of session variables
 */
$_SESSION['LOG_POS'] = $LOG_POS;
$_SESSION['LAST_ACTION'] = $LAST_ACTION;
$_SESSION['LAST_ID'] = $LAST_ID;

?>

