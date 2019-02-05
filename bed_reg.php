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
 * Provides UI for the user to choose which bed groups the user wants to view
 */

$title = 'Bednight Registration';
$quiet = true;

$commands = array();
include 'includes.php';


if ($_REQUEST['print_sheet']) {

	$print_sheet  = true;
	$print_format = orr($_REQUEST['print_format'],'terse');

}

/*
 * Assign requested variables
 */
$tmp_requested_variables = array('action',
					   'assigns',
					   'bedgrp',
					   'bednum',
					   'client_select',
					   'client_remove',
					   'permtype',
					   'QuickSearch',
					   'showhist',
					   'volstatus',
					   'show_photos');
foreach ($tmp_requested_variables as $tmp) {

	$$tmp = $_REQUEST[$tmp];

}

/*
 * Initialize bed groups defined in client_config.php
 */
create_groups( $bed_groups );

/*
 * Get user prefs
 */
$USER_PREFS = $AG_USER_OPTION->get_option('BED_REG_DISPLAY_OPTIONS'); 


if (isset($_REQUEST['pick_bed_reg'])){

	$USER_PREFS['show_photos'] = $_REQUEST['show_photos'] ? true : false;
	$USER_PREFS['bed_history'] = $_REQUEST['showhist'] ? 'all' : 'none';
	$USER_PREFS['bed_assigns'] = $_REQUEST['assigns'] ? 'Y' : 'N';

}
	
$DISPLAY['photos']      = $USER_PREFS['show_photos'];
$DISPLAY['bed_history'] = $USER_PREFS['bed_history'];
$DISPLAY['bed_assigns'] = $USER_PREFS['bed_assigns'];



//is this ever true?
if (is_numeric($_REQUEST['showhist'])) {

  	$DISPLAY['bed_history'] = $_REQUEST['showhist'];

}


$_SESSION['GENDER_CONFIRM']   = $GENDER_CONFIRM   = orr($_REQUEST['gender_ok'],$_SESSION['GENDER_CONFIRM']);
$_SESSION['BAR_CONFIRM']      = $BAR_CONFIRM      = orr($_REQUEST['bar_ok'],$_SESSION['BAR_CONFIRM']);
$_SESSION['TRANSFER_CONFIRM'] = $TRANSFER_CONFIRM = orr($_REQUEST['transfer_ok'],$_SESSION['TRANSFER_CONFIRM']);
$_SESSION['BED_TRANSFER']     = $BED_TRANSFER     = orr($_REQUEST['transfer'],$_SESSION['BED_TRANSFER']);

if (isset($client_select) ) {    

    if ($client_select != '') {

	    $$bedgrp->add( $bednum, dewebify($client_select) );

    }

}

if (isset($volstatus)) {

	$$bedgrp->set_vol($bednum, $volstatus);

}

if ($action == 'comments') {

	$$bedgrp->set_comments($bednum, $_REQUEST['comments']);
	$$bedgrp->set_bus_tickets($bednum,isset($_REQUEST['bus_am']),isset($_REQUEST['bus_pm']));

}

// Set flag here to auto-rereg a client for manana.
// fixme: either remove or fix--$rereg hasn't been set since
// register_globals was turned off
if (isset($rereg)) {

	set_rereg_flag( $rereg );

}

/*
 * Provide search results if user is searching for client
 */
if ($action == 'ClientSearch' && $QuickSearch ) {

    if ( is_numeric($QuickSearch) && is_client(intval($QuickSearch)) ) {

	    outline('Quick adding ' . client_link($QuickSearch) . " ($QuickSearch) to $bedgrp, $bednum");
	    $$bedgrp->add( $bednum, intval($QuickSearch) );

    } else {

	    agency_top_header();
	    headline('Client Search Results');
	    out(client_search('Y',false));
	    page_close();
	    exit;

    }

}

/*
 * Remove client. Transfers are handled elsewhere
 */
if (isset($client_remove) && ! isset($client_select)) {

	$$bedgrp->remove( $bednum, $client_remove ); // Can be name or #

}

if ($_REQUEST['action'] == 'REMOVE_MANY') {

	$$bedgrp->process_many(); //a process to remove multiple beds 

}

if ($_REQUEST['action'] == 'MULTI_COMMENT') {

	$$bedgrp->process_multi_comment(); //a process to edit multiple comments

}

$grp_filter = array();

//formatting: split into 2 rows
$cnt = count($bed_groups);
if ($cnt > 4) {

	$split_cnt = ceil($cnt/2);

}

$i=0;

foreach ($bed_groups as $bed_group ) {

	$i++;

	/*
	 * Set name of "View" form variable
	 */
	$formvar = 'v' . $$bed_group->code;  //e.g.  vmens
 
	if (isset($_REQUEST['pick_bed_reg'])){

		$USER_PREFS[$formvar]= $_REQUEST[$formvar];
	}

	$$formvar =  $USER_PREFS[$formvar];
 
	/*
	 * Set view to on or off
	 */
	$$bed_group->view = $$formvar;
	if (in_array($DISPLAY['bed_history'],array('all','none',$bed_group))) {

            $$bed_group->set_history($DISPLAY['bed_history']);

	}

	/*
	 * Check if printer friendly or "frozen"
	 * Oddly, this is handled by changing permission types
	 */
	if (isset($permtype)) {

		if ($bedgrp == 'all' || $bed_group == $bedgrp) {

			$$bed_group->set_permissions($permtype);

		}

	}

	/*
	 * now create an array of checkbox HTML passed to
	 * show_grp_select() below
	 */
	if ($split_cnt && ($i==$split_cnt)) {

		$break = oline();

	} else {

		$break = '';

	}

	$checks[] = span(formcheck($formvar,$$bed_group->view)
			     . ' ' . $$bed_group->code,'',' class="checkBoxSet"' ). $break . "\n";


	/*
	 * Generate display
	 */
	if ($$bed_group->view == 'on') {

		$group_links .= ' ' . seclink($bed_group);
		$beds_show .= anchor($bed_group);

		//check for lock flag
		$flag_name = $nobedreg . $bed_group;
		$reg_flag = sql_fetch_assoc(get_sys_flag($flag_name));
		$reg_is_flag = $reg_flag['is_flag'];
		
		if (sql_false($reg_is_flag)) {

			if ($print_sheet) {

				array_push($grp_filter,$bed_group); // if printing, build filter, otherwise show groups
			} else {

				$beds_show .= $$bed_group->show();
			}

		} else {

			$beds_show .= oline(bigger(bold("Bednight Registration Can Not Continue for group $bed_group"),2));
			$beds_show .= bigger(red(oline('There are unregistered clients ' .
								 'in last night\'s bednight registration.')));
			$beds_show .= bigger('You may ' .
						   hlink("bed_resolv_unreg.php?bed_group=$bed_group", 'fix the unregistered clients') .
						   ' or contact your supervisor.');

		}

		$beds_show .= seclink('top','Return to Top');
	}

} //end foreach bed group

$AG_USER_OPTION->set_option('BED_REG_DISPLAY_OPTIONS',$USER_PREFS); //set user prefs - if changed

// provide list of checkboxes for user to choose which groups to view
array_push($commands,show_grp_select($checks));

if ($print_sheet) {

	include 'openoffice.php';
	include 'zipclass.php';

	if ($print_format == 'printout') {

		if (count($grp_filter) > 1) {

			out(alert_mark('Cannot use this format if more than one group is selected.'));

		} else {

			$filter = read_filter(array('NULL:removed_at'=>'', 'bed_group'=>$grp_filter));
			$group  = $grp_filter[0];
			$config = $GLOBALS['bed_'.$group];
			$start  = $config['start'];
			$end    = $start + $config['count'] - 1;
			$filename="bednight_sheet_$print_format.sxw";

			$sheet=oowriter_merge(agency_query("SELECT DISTINCT SUBSTRING(
                                                                 (CASE WHEN c.client_id IS NULL THEN b.client::text
                                                                      ELSE (name_full) END)
                                                                 ,1,22) AS client_name,
                                           b.client,b.bed_group,generate_series AS bed_no,b.comments,b.vol_status
                                         FROM generate_series($start,$end)
                                               LEFT JOIN bed_reg b ON (b.bed_no = generate_series AND $filter)
                                               LEFT JOIN client c ON (b.client::text=c.client_id::text)",
								   '','bed_no'),$filename);
		}

	} else {

		$filter=array('NULL:removed_at'=>'dummy', 'bed_group'=>$grp_filter);

		$name_full = "c.name_last::text || ','::text || c.name_first::text || 
                         (CASE WHEN c.name_middle IS NULL THEN ''::text ELSE ' '::text || c.name_middle::text END)";
		$sheet=oowriter_merge(agency_query("SELECT DISTINCT SUBSTRING(
                                                                 (CASE WHEN c.client_id IS NULL THEN b.client::text
                                                                      ELSE ($name_full) END)
                                                                 ,1,22) AS client_name,
                                           b.bed_group,b.bed_no,b.comments
                                         FROM bed_reg b LEFT JOIN tbl_client c ON (b.client::text=c.client_id::text)",
							   $filter,'bed_group, bed_no'),$filename);
	}

	serve_office_doc($sheet,$filename); // exits

}

agency_top_header($commands);
out(anchor('top'));
outline(bigger(bold($title),2));

if ($group_links) {
	outline('Jump To ' . $group_links 
		  . smaller(' (print these sheets: '
				. hlink($_SERVER['PHP_SELF'].'?print_sheet=true&print_format=terse','terse format') 
				. ', '
				. hlink($_SERVER['PHP_SELF'].'?print_sheet=true&print_format=comments','with comments') 
				. ', '
				. hlink($_SERVER['PHP_SELF'].'?print_sheet=true&print_format=printout','simple format') 
				.')'),2) ;
}

outline($beds_show,2);
// $flag_legend is declared in agency_config.php
// currently it contains flags for all bed groups
outline(show_flag_legend($flag_legend));

//register session vars (this is hacky only because of previous dependence on phplib)
foreach ($bed_groups as $bed_group) {

	$_SESSION[$bed_group] = $$bed_group;

}

page_close();

/*
 * Bed-Reg-specific functions below
 */

function show_grp_select($boxes)
{
	global $colors,$DISPLAY;
	$output  = '';
	$output .= formto($_SERVER['PHP_SELF']);

	$output .= formcheck('show_photos',($DISPLAY['photos'] == 'Y')) . 'Show Photos' ;
	$output .= formcheck('showhist',($DISPLAY['bed_history']=='all')) . 'Show History';
	$output .= formcheck('assigns',($DISPLAY['bed_assigns']=='Y')) . 'Show Assigns';
	$output .= oline();
	$output .= 'Choose bed groups to view:&nbsp;';
	
	// set up checkboxes for each group
	$output .= span(implode("\n",$boxes),'class="checkBoxGroup"');

	$output .= hiddenvar('pick_bed_reg', '1');
	$output .= button('View','SUBMIT', 'action');
	$output .= formend();
	
	// display each group with check box plus a button to activate
	// needs to be persistent
	return bottomcell(smaller($output),'class="pick" align="center"');

}

/*
 * ??? shouldn't this function be in class bed i.e. bedgroup.php?
 * fixme: it isn't even in use, and hasn't been since register_globals,
 * was turned off (and as such, could probably be removed all together) - JH 10/4/07
 */
function set_rereg_flag( $cl )
{
	/*
	 * this toggles a flag in the bedreg table that will result
	 * in their being re-registered for the next night.
	 * $cl is client_id, prepended with "!" to remove
	 */

	$bedreg_table=$GLOBALS['bedreg_table'];

	if (substr($cl,0,1)=='!') {

		$flag = sql_false();
		$cl=substr($cl,1,strlen($cl)-1);

	} else {

		$flag = sql_true();

	}

	$values['re_register']=$flag;
	$filter_values['client']=$cl;
	$filter_values['NULL:removed_at']='';
	$query = sql_update($bedreg_table, $values, $filter_values);
    
	$res = agency_query($query);

	return $res;

}

function show_bedreg_options()
{
	/*
	 * show registration specific options 
	 * flag_legend is brought in so we can keep a consistent look for the UI.
	 * The number of rows displayed here needs to match total number of flags
	 * listed in the legend.    
	 */

	global $DISPLAY, $flag_legend;
	$url_count=0;
	$flag_count = count($flag_legend);

	for($x = 1; $x < ($flag_count - $url_count); $x++) {

		$text .= row(cell('')); // add extra rows more consistency in display

	}

	$text .= tableend();

	return $text;    

}

function show_flag_legend($legend)
{

	$text = tablestart()
		. row(cell(smaller(underline('Flag Legend')),'colspan="2"'));

	foreach ($legend as $key => $value) {

		$text .= row(cell(smaller($key)).cell(smaller($value)));

	}

	$text .= tableend();

	return $text;

}


?>
