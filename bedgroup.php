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

// This page is for displaying and registering clients



function which_current_bed( $client )
{
	// This function (formerly $bedgroup->where()) is not object specific
	// It finds a currently-registered (for a bed) client,
	// in any bedgroup, so I'm moving it to a more general function
	// false means not registered.
	// Conceptual complement to $bedgroup->who( $bednum )
	global $bedreg_table;
	$fields = "SELECT bed_group,bed_no FROM $bedreg_table";
	$filt = array("client" => $client, "NULL:removed_by" => "",
			  "NULL:removed_at" => "");
	return  sql_fetch_assoc(agency_query($fields, $filt));        
}

function which_current_bed_f( $client )
{
	$which=is_array($client) ? $client : which_current_bed($client);
	return $which ? 
		blue("Currently Registered for " . $which["bed_group"] . " bed #" . $which["bed_no"]) 
		: "";
}

class bed_group {

	var $classname="bed_group";
	var $persistent_slots= array( "set", "code", "title", "color", 
						"start", "count", "step", "pre", "view","genpref", "perm", "rereg_all");
	var $start;
	var $step;
	var $count;
	var $title;
	var $code;
	var $color;
	var $genpref;
	var $set=array();
	var $pre="bed_";
	var $view=""; // to view or not to view group in bed_reg.php
	var $night_startsat = 18;  // 24-hour
	var $perm = "";  // permission to register clients in beds
	var $rereg_all;
	var $client_list; //trying to cut down on queries performed

	//------remove many variables-----//
	var $var_remove_many='Bed_Rem_Many';
	var $variable_sep='XX--XX';
	var $remove_many_step='rmSTEP';

	function bed_group() {}

	function set( $code, $title, $color, $start, $count, $genpref="", $step=1,
			  $night_startsat=18, $night_endsat=7, $rereg_all="N", $perm="rw" )
	{
		$this->code  = $code;
		$this->title = $title;
		$this->color = $color;
		$this->start = $start;
		$this->count = $count;
		$this->genpref = $genpref;
		$this->step  = $step;
		$this->rereg_all =$rereg_all;
		$this->perm = $perm;
	}

	function load_whos()
	{
		// load all the occupancy status for a bedgroup, to avoid individual queries.
		global $who_list, $bedreg_table;
		$query = "SELECT bed_no,client from $bedreg_table";
		$filter["bed_group"]=$this->code;
		$filter["NULL:removed_by"]="";
		$filter["NULL:removed_at"]="";
		$whos=agency_query($query,$filter,"bed_no");
		for ($x=0;$x<sql_num_rows($whos);$x++)
		{
			$who = sql_fetch_assoc($whos);
			$who_list[$this->code.$who["bed_no"]]=$who["client"];
		}
		$who_list[$this->code."loaded"]=true;
		return true;
	}

	function load_clients()
	{
		// load all the clients for a bedgroup, to avoid individual queries.
		global $client_list, $bedreg_table, $DISPLAY,$client_table;

		//outline("Showhist = $showhist");
		$filter["bed_group"]=$this->code;
		if ($DISPLAY["bed_history"]<>"all")
		{
			$filter["NULL:removed_by"]="";
			$filter["NULL:removed_at"]="";
		}
		$which_clients=agency_query("SELECT client from $bedreg_table",$filter);
		if (sql_num_rows($which_clients)==0)
		{
			return true;
		}
		for ($y=0;$y<sql_num_rows($which_clients);$y++)
		{
			$who_temp = sql_fetch_assoc($which_clients);

			if (is_numeric($who_temp["client"]))
			{
				$who_string .= enquote1($who_temp["client"]).",";
			}
		}
		$who_string = "IN (" . substr($who_string,0,strlen($who_string)-1) . ")";
		// added _gender to the query as required for displaying assessment label - JH
		// is this a duplication of $client_select_sql?--KT
		// re: above question. It isn't, it is an optimized version to speed the page loading up (which is still SLOW)
		$clients = sql_query("SELECT CL.client_id,CL.name_full,CL.name_alias,CL.dob,CL.gender_code,GEN.Description as _gender
                                     FROM $client_table as CL LEFT JOIN l_gender as GEN on CL.gender_code=GEN.gender_code
                                     WHERE client_id $who_string"); 

		for ($x=0;$x<sql_num_rows($clients);$x++)
		{
			$client = sql_fetch_assoc($clients);
			$client_list[$client["client_id"]]=$client;
			$this->client_list[$client['client_id']]=$client;
		}
		return true;
	}

	function who( $bednum )
	{
		global $bedreg_table,$who_list;
		if ( ! $who_list[$this->code."loaded"])
		{
			$this->load_whos();
		}
		return $who_list[$this->code.$bednum];
	}

	function was_reg( $client ) {
		//determine if a client was registered at all (attempt to catch devious transfers)
		//false means not registered.
		global $bedreg_table;
		$code = enquote1($this->code);
		$order = 'removed_at DESC';
		$fields = "SELECT bed_group,bed_no FROM $bedreg_table";
		$filt = array('client' => $client, 'FIELD>=:added_at'=>'current_date','FIELD:be_null(removed_at)'=>'false','FIELD:be_null(removed_by)'=>'false');
		return sql_fetch_assoc(agency_query($fields,$filt));
	}

	// returns the flags set for a specific bed
	function get_flags($num)
	{
		$flags=$this->set[$num]["flagtext"];
		return smaller(blue("$flags"));
	}

	function link_history( $num )
	{
		$code=$this->code;
		return smaller(hlink(
					   $_SERVER['PHP_SELF'] . "?showhist=$num&bedgrp=$code" ,
					   httpimage($GLOBALS['AG_IMAGES']['PLUS_BUTTON'], "16", "16")) . "&nbsp;") . "\n";
	}

	// maybe take $perm out of arguments
	function show_vol($bedgrp, $bednum, $status)
	{
		// default perm was rw
		global $show_volunteer_pick;
		$text = "";
		$label = "";
		$sep = " | ";

		if(isset($status)) {
			$label =  bigger(bold("$status VOL"));
			$new_stat = "none";
			$link = $_SERVER['PHP_SELF'] . "?volstatus=" . $new_stat
				. "&bedgrp=$bedgrp&bednum=$bednum";
			$display = hlink($link,$label);
			if ($this->perm == "ro") {
				$display = $label;
			}
		} elseif ($this->perm == "rw") {
			// display form for setting volunteer status
			$form = formto($_SERVER['PHP_SELF']);
			$form .= hiddenvar('bedgrp', $bedgrp);
			$form .= hiddenvar('bednum', $bednum);
			$form .= $show_volunteer_pick[orr($status,"none")];
			$form .= button("VOL",'SUBMIT',null,null,null,' style="font-size: 75%;"');
			$form .= formend();
			$display = html_no_print($form).html_print_only('&nbsp;');
		}    
		else
		{        
			// if volunteer, display it, otherwise display nothing        
			$display = "&nbsp;";
		}    
		$text = $display;
		return $text;
	}

	function show_comments($comments, $bednum,$am,$pm,$single_form = false)
	{
		// default perm was ro
		$bedgrp = $this->code;
		$perm = $this->perm;
		$text = "";
    
		if ( ($perm == "ro") ) {
			// read only
			$text .= $comments;
		} elseif ($single_form) {

			$text .= formvartext('comments['.$bednum.']',$comments,' style="font-size: 75%;"');

		} else {
			// read/write
			//stuffing bus ticket stuff in here
			$bus = table(
					 row(centercell(smaller('Bus tickets: ',3),' colspan="4"'))
					 . row(cell(formcheck('bus_am',$am)).cell(smaller('am',3))
						 .cell(formcheck('bus_pm',$pm)).cell(smaller('pm',3)))
					 ,null,' cellpadding="0" cellspacing="0" class="" style="display: inline;"');
			$button = button("Comment",'SUBMIT',null,null,null,' style="font-size: 75%;"');
			$text .= html_no_print(formto($_SERVER['PHP_SELF'])
						     . table(row(cell(formvartext("comments", $comments, ' style="font-size: 75%;"'))
								     .cell($button).cell($bus)),null,' cellpadding="0" cellspacing="0" class=""')
						     . hiddenvar("action", "comments")
						     . hiddenvar("bedgrp", $bedgrp)
						     . hiddenvar("bednum", $bednum)
						     . formend())
				. html_print_only(orr($comments,'&nbsp;'));
		}
		return $text;
	}

	function show_occupied($num, $client, $row, $show_details="Y", $show_functions=true)
	{
		// default perm was ro
		global $client_list;
		$code = $this->code;
		$perm = $this->perm;

		// is it a real ID or just a name
		if ( is_numeric($client) ) // a client_id
		{
			$is_valid_id = TRUE;
			$cl_rec = (isset($this->client_list[$client]))
				? $this->client_list[$client]
				: sql_fetch_assoc(client_get($client)); // get client record
		}
		$priority_type = ($code == "kerner") ? "KSH" : "ANY";
/*
		if ($priority=has_priority($cl_rec,$priority_type))
		{
			$prtext= smaller("PR:" . priority_status_f($priority,"","terse"));
		}
		else
		{
*/
			if (sql_true($row["re_register"]))
			{
				$clid = "!" . $client;
				$msg="Will Rereg";
			}
			else
			{
				$clid=$client;
				$msg="Won't Rereg";
			}
			if ($this->rereg_all == "N")
			{
				$prtext = ($perm=="rw")
					?  smaller(hlink($_SERVER['PHP_SELF']."?rereg=$clid",$msg))
					:  smaller($msg);
			}
/*
		}
*/
		if ( ($perm == "rw") && $show_functions)
		{
			$text = "(" .
				smaller(hlink($_SERVER['PHP_SELF']."?client_remove=" . $client
						  . "&bedgrp=$bed_pre$code"
						  . "&bednum=$num","Remove" ),2) . ")\n ";
		}
		$name = substr( ($GLOBALS['AG_DEMO_MODE'] 
				     ? preg_replace('/[a-z]/','x',preg_replace('/[A-Z]/','X',trim($cl_rec['name_full'])))
				     : $cl_rec['name_full'])
				    ,0,25);
		$text .= ( $is_valid_id ) // a client_id
			//?  client_link($cl_rec, "25") . smaller("($client)",1)  //reducing number of queries required
			? client_link($cl_rec,$name) . smaller('('.$client.')',1)
//			. " | A:" . assessment_f($cl_rec,"tiny") . ($prtext ? " | $prtext" : "")
			: $client; // The text name of an unregistered client 
		if ($show_details=="Y")
		{
			// if current assignment isn't today's date, show the date
			if (dateof($row["added_at"],"US_SHORT") <> dateof("now","US_SHORT"))
			{
				$add_date = dateof($row["added_at"],"US_SHORT"); 
			}
			$text .= " " . smaller(staff_link($row["added_by"]) . "@"
						     . ($add_date ? bold("$add_date ") : "")
						     . timeof($row["added_at"]),2);
		}
		
/*
		//safe harbors consent & chronic homeless status
		if (is_numeric($client) && !in_array($code,array('connect'))) { 
		  
		        $def = get_def('safe_harbors_consent');
			$res = get_generic(client_filter($client),'','',$def);
		  
			if (sql_num_rows($res) < 1) { //no consent on file
			  $safe_harbors = oline().smaller(link_engine(array('object'=>$def['object'],'action'=>'add','rec_init'=>client_filter($client)), red('Record This Person\'s Safe Harbors Preferences')),2);
			  
		  } 
		  else {//consent on file 
		    
		    $a = sql_fetch_assoc($res);
		    $val = $a['safe_harbors_consent_status_code'];
		    $safe_harbors = oline().smaller(link_engine(array('object'=>$def['object'],'action'=>'view','rec_init'=>client_filter($client),'id'=>$a['safe_harbors_consent_id']), 'Safe Harbors Consent Form on file'),2);
		  }
		  $def = get_def('chronic_homeless_status_asked');
		  //		  toggle_query_display();
		  //this breaks
		  $res = get_generic(client_filter($client),'','',$def);
		  
		  if (sql_num_rows($res) < 1) { //no consent on file
		    
		      $chronic = smaller(link_engine(array('object'=>$def['object'],'action'=>'add','rec_init'=>client_filter($client)), red('Record Chronic Homeless Status')),2);
		      
		    } 
		  else { 
		    $a = sql_fetch_assoc($res);
		    $val = $a['chronic_homeless_status_code'];
		    $chronic = smaller(link_engine(array('object'=>$def['object'],'action'=>'view','rec_init'=>client_filter($client),'id'=>$a['chronic_homeless_status_asked_id']), 'Chronic Homeless Status on file'),2);
		    
		    }
		}		
*/
		
		
		$text = bigger($num) . ". "
		   . ($show_functions ? $this->link_history( $num ) : '')
		  . $text . $safe_harbors. ' '.$chronic;   
		// show who's there
		return $text;
	}

	function show_vacant($num)
	{
		// perm default was ro
		$code=$this->code;
		$perm=$this->perm;
		// show add dialog
		$url = $_SERVER['PHP_SELF']."?allow_other=Y&bedgrp=$bed_pre$code&bednum=" . $num;
		$text = "";
                         
		$number =  bigger($num) . ". ";
		if ($perm == "ro") {
			$text .= "$number&nbsp;empty";
		} else {
			$text .= html_no_print(formto( $url )
						     . $number
						     . $this->link_history( $num )
						     . client_quick_search("Search","NoForm")
						     . hiddenvar("select_to_url",$url)
						     . formend())
				. html_print_only($number.' empty');
		}    
		return $text;
	}

	function show_history( $bednum, $show_details="Y", $photos )
	{
		global $bedreg_table;
		$text = "";
		$code = $this->code;
		$query = "SELECT * FROM $bedreg_table
              WHERE bed_group = '$code'
              AND bed_no = $bednum
              AND removed_at IS NOT NULL";

		$result = sql_query($query) or
			sql_warn("Unable to select history records:  $query");

		while($row = sql_fetch_assoc($result))
		{
			$client = $row["client"];
			//          outline("client history:  $client");
			if ( is_numeric($client) ) // a client_id
			{
				$client_text = client_link($client, "25") . smaller("($client)");
			}
			else // The text name of an unregistered client
			{
				$client_text = $client;
			}
			// show assigns
			if ($show_details == "Y")
			{
				if (dateof($row["added_at"],"US_SHORT")!=dateof("now","US_SHORT"))
				{
					$add_date = dateof($row["added_at"],"US_SHORT"); 
					$rem_date = dateof($row["removed_at"],"US_SHORT");
				}
				$details = smaller("+"
							 . staff_link($row["added_by"]) . "@" . " $add_date "
							 . timeof($row["added_at"])
							 . "\n-" . staff_link($row["removed_by"])                  
							 . "@" . " $rem_date " . timeof($row["removed_at"]) . "\n",2);
			}
			
			$text .= rowstart();
			if ($photos){
			  $text .= cell("");
			}
			$text .= cell("");             
			$text .=   cell(smaller($bednum) . ". " . indent("$client_text", 4)
				  . indent("",2) . $details)
			          . cell($row["vol_status"] 
				  . ($row["vol_status"] ? " VOL" : ""), "align=center valign=middle")
				  . cell(""); 
			$text .=rowend();             
          
		}
		return $text;
	}

	// provide ui for freezing/unfreezing registration
	function show_freeze_options()
	{
		$bedgrp=$this->code;
		$cperm=$this->perm;    
		$url=$_SERVER['PHP_SELF']."?bedgrp=$bedgrp&permtype=";
		if ($cperm== "ro")
		{
			$text .= smaller(hlink($url . "rw", "UnFreeze Registration"));
		}
		elseif ($cperm=="rw")
		{
			$text = smaller(hlink($url . "ro", "Freeze Registration")) 
				. "&nbsp;&nbsp;";
		}    
		return $text;
	}

	function make_vol_picks()
	{
		// get all the volunteer options, and build an array
		// of the pick boxes that will be displayed.  The
		// difference in each string is which one is the default.
		global $l_volunteer_table;
		$sql="SELECT volunteer_code AS Value, volunteer_code AS Label FROM $l_volunteer_table";
		$opts=agency_query("SELECT volunteer_code FROM $l_volunteer_table UNION SELECT 'none' as volunteer_code");
		$template = selectto("volstatus",' style="font-size: 75%;"') 
			. do_pick_sql($sql)
			. selectitem("none","none") 
			. selectend();
		for ($x=0;$x<sql_num_rows($opts);$x++)
		{
			$opt = sql_fetch_assoc($opts);
			$vol_code = $opt["volunteer_code"];
			$result[$vol_code]  = preg_replace("/OPTION VALUE=\"$vol_code\">/i","OPTION VALUE=\"$vol_code\" SELECTED>",$template); 
		}
		return $result;
	}
	
	function show()
	{
		global $bed_pre, $bedreg_table, 
			$histcode, $DISPLAY, $show_volunteer_pick;
		// $DISPLAY["bed_assigns"] is the flag to show staff info

		if (! isset($show_volunteer_pick)) // only need to do this once
		{
			$show_volunteer_pick=$this->make_vol_picks();
		}
		// get the occupancy statuses
		//$this->load_whos();
		// load all clients at once
		$this->load_clients(); //sets $this->client_list as well as a global (that is probably not needed)
		$start = $this->start;
		$step = $this->step;
		$count = $this->count;
		$title = $this->title;
		$code = $this->code;
		$color = $this->color;
		$perms = $this->perm;  //read/write permissions    
		$ret = ""; // this is the string we will return
		$comments_cell = "";
		$rereg_all = $this->rereg_all;
		$date_display = " on " . dateof("NOW");
		$result = agency_query( "SELECT * FROM $bedreg_table", 
					    array("bed_group"=>$code, "NULL:removed_at"=>""),"bed_no");
		$total_recs = sql_num_rows($result);

		// if supervisor display freeze unfreeze options
		// !!! still need to check if supervisor
		$freeze = "&nbsp;&nbsp; ";
		if (has_perm("reg_bed_freeze","RW")) {
			$freeze .= html_no_print($this->show_freeze_options());
			$date_display = "";
		}

		if ($rereg_all == "Y")
		{
			$auto_note = smaller("&nbsp;&nbsp;(All clients in this group are 
            automatically re-registered)");
		}
		// create bed group heading and start table beds
		$ret .= oline(" ")
			. bigger(bold( $title . $date_display )) . $freeze . $auto_note 
			. oline(smaller(""),2)
			. oline($this->multi_comment_link())
			. oline($this->remove_many_link(),2)
			. tablestart("",
					 'bgcolor="'.$color.'" class="" border="1" cellspacing="0" cellpadding="0" width="100%"');
		// put assigned beds in array
		while ($row=sql_fetch_assoc($result))
		{
			// the bed number is also the element number or key
			$beds_asgn[$row["bed_no"]] = $row; // 2D array
		}
		// loop through all beds (even if no record in bed_reg table)
		for ($x = $start; $x<$start+$count; $x=$x+$step)
		{
			$flags = $this->get_flags($x);  // displayed to the left of the bed
			// dummy bed?  If so, skip
			if (stristr($flags,"xxx"))
			{
				continue;
			}
			if ($client = $this->who($x))
			{
				// bed assigned
				$num = $x;
				$comment = $beds_asgn[$x]["comments"];
				$vol_status = $beds_asgn[$x]["vol_status"];
				$am = $beds_asgn[$x]['bus_am'];
				$pm = $beds_asgn[$x]['bus_pm'];
				$ret .= rowstart();
				$ret .= cell($flags ? $flags : "&nbsp;");
				if ($DISPLAY["photos"]){
				  $ret .= cell(client_photo($client,.35));
				}
				$ret .= cell($this->show_occupied($num, $client, $beds_asgn[$x], $DISPLAY["bed_assigns"],$perms));
				// show volunteer status or form
				$ret .= cell($this->show_vol($code, $num, $vol_status),
						 "width=125 align=center");
				// get comments for display
				$ret .= cell($this->show_comments($comment, $num,$am,$pm), 
						 "width=230 valign=top");// valign to match vol status
				$ret .= rowend();
			}
			else
			{
				$ret .= rowstart();
				$ret .= cell($flags ? $flags : "&nbsp;");
        		        if ($DISPLAY["photos"]){
				  $ret .= cell("&nbsp");
				}

				// two blank cells for vol_status and comments
				$ret .= cell($this->show_vacant($x)) . cell("") . cell("");
				$ret .= rowend();
			}

			// need to use $x here because $num won't be set
			// if bed is currently vacant
			if ($this->set[$x]["hist"]=="Y")
			{
			  $ret .=  $this->show_history($x, $DISPLAY["bed_assigns"], $DISPLAY["photos"]);
			}
		}
		$ret .= tableend();
		return $ret;
	}

	// show unregistered client with select or remove choices.
	// can pass a client name to show just one record or show all
	function show_unregclients($name="all")
	{
		global $bedreg_table, $bed_pre;
		$code = $this->code;
		$color = $this->color;
		$title = $this->title;
		$url = "";
		if ($name == "all")
		{
			$query = "SELECT * FROM $bedreg_table "
				. "WHERE bed_group = '$code' AND removed_at IS NULL "
				. "AND client ~ '[^0-9]'";
		}
		else
		{
			// display just one record
			$query = "SELECT * FROM $bedreg_table "
				. "WHERE client='$name' "
				. "AND removed_at IS NULL ";

			$n = bold($name);
			$notes = "Is this a transfer?  If $n is now properly registered,
        then search for the registered client to add to the current bed.\n  
        If $n is no longer using the bed or you want to add a different\n
        client to the current bed, then click the " . bold("Remove") . " button.";        
		}
		// TESTING  outline("In show_unregclient():  $query");
		$res = sql_query($query) or
			sql_warn("Unable to select Unregistered Clients:  $query");

		$output = oline(bigger(bold($title), 2));
		$output .= oline($notes);
		if (sql_num_rows($res) > 0)
		{
			// create a table of unregistered clients; else no unreg. clients
			$output .= tablestart("",' class="" bgcolor="'.$color.'" border="1"');        
			while($unreg = sql_fetch_assoc($res))
			{
				$num = $unreg["bed_no"];
				$client = htmlentities($unreg["client"]); //convert for url entry
				$comms = $unreg["comments"];
				$addedat = htmlentities($unreg["added_at"]);
				$url = $_SERVER['PHP_SELF']."?bedgrp=$bed_pre$code&bednum=$num";
				// need different vars depending on where this function is called
				if ($name == "all")
				{
					// displaying all unregistered clients (for resolving
					// unregistered clients before allowing bednight registration
					// to start again
					//$url .= "&unreg=" . $client;
					$url = $url . "&client_remove=" . $client . "&transfer=Y";
				}
				else
				{
					// removing one client and need to see that we are transfering
					$url = $url . "&client_remove=" . $name . "&transfer=Y";
				}
                
				$c_search = formto( $url )
					. client_quick_search("Search","NoForm")
					. hiddenvar("select_to_url",$url) . formend();

				// button(value, type, name)
				$no_show_butt = formto( $url )
					. button("Remove") . hiddenvar("action", "remove")
					. hiddenvar("addedat", $addedat) . formend();

				// client link
				$output .= row(cell(bigger($num . ".&nbsp; " . $client))
						   . cell($c_search)
						   . cell(bigger(bold(" OR "), 2))
						   . cell($no_show_butt)
						   // not showing comments for now 10/10/02
						   //. cell($this->show_comments($comms, $num, "rw"))
						   );
			}
			$output .= tableend();
		}
		else
		{
			$output .= "No unregistered clients in this bed group.";
		}
		return $output;
	}

	function add( $num, $client )
	{
		global $bedreg_table, $UID, $GENDER_CONFIRM, $BAR_CONFIRM,
			$BED_TRANSFER, $TRANSFER_CONFIRM, $who_list;

		$code = $this->code;
		$genpref = $this->genpref;
		// make a clean string
		$client = strip_tags($client);

		// 0. Read-only?
		if (db_read_only_mode()) {
			outline(bigger(red('Can\'t add--Database is in read-only mode')));
			return false;
		}

		// 1.  Is bed assigned?  IF so, stop
		if ( $a=$this->who( $num ) && $BED_TRANSFER != "Y")
		{
			outline(bigger(red("Sorry, $code Bed $num already assigned to "
						 . client_link($a))),2);
			return false;
		}

		// 1A.  Does client have a bed already?  IF so, stop
		if ( ($a = which_current_bed( $client )) && (! $TRANSFER_CONFIRM) )
		{
			outline(bigger(red(client_link($client)
						 . " is already registered in Bed {$a["bed_no"]}")));
			outline("");
			outline(hlink($_SERVER['PHP_SELF']."?client_select=$client&bedgrp=$code&bednum=$num&transfer_ok=Y",
					  "Click here to transfer the client to $code Bed $num"),2);
			return false;
		}
		// 1A.5 Was the client already registered? IF so, stop
		if ( ($a=$this->was_reg($client)) && (! $TRANSFER_CONFIRM) ) {
			outline(bigger(red(client_link($client)
						 . 'was previously registered in '.$a['bed_group'].' Bed '.$a['bed_no'])),2);
			outline(hlink($_SERVER['PHP_SELF']."?client_select=$client&bedgrp=$code&bednum=$num&transfer_ok=Y",
					  "Click here to un-remove (add) the client to $code Bed $num"),2);
			return false;
		}

		// 1B.  Do the Genders match?
		if ( 	(! $GENDER_CONFIRM ) && (
							 ($genpref == "M" && (! is_male( $client ) ))
							 ||
							 ($genpref == "F" && (! is_female( $client ) ))) ) 
		{
			outline(alert_mark("The gender for " . client_link( $client) . 
						 " is not appropriate for the " . $this->code . " area, or the gender cannot
			be confirmed.  "));
			outline( hlink(
					   $_SERVER['PHP_SELF']."?client_select=$client&bedgrp=$code&bednum=$num&gender_ok=Y",
					   "Thanks for the warning.  I want to assign this bed anyway"),2);
			return false;
		}
/*
		// 1C.  Is the client barred?
		if ( bar_status( $client, $code ) && ! $BAR_CONFIRM)
		{
			outline(alert_mark(client_link( $client) 
						 . " is barred from $code beds.") );
       
			outline(hlink($_SERVER['PHP_SELF']."?client_select=$client&bedgrp=$code"
					  . "&bednum=$num&bar_ok=Y",
					  "Thanks for the warning.  I want to assign this bed anyway."),2);
			return false;
		}
*/
		// 1D.  Client can not be blank
		if ($client == "")
		{
			outline("Can not add client to bed.  Client name/ID is blank.");
			return false;
		}
		if ( $TRANSFER_CONFIRM )
		{
			return $this->transfer($client,$num);
		}
		$values["bed_no"] = $num;
		$values["client"] = $client;
		$values["added_by"] = $UID;
		$values["added_at"] = datetimeof();
		$values["bed_group"] = $this->code;

		// 2.  Insert record into table
		$sql = sql_insert($bedreg_table, $values);

		// transfers require only an update not an insert
		if ($BED_TRANSFER == "Y")
		{
			// transfers keep the existing record and use a valid client id
			$filt = array("bed_no"=>$num, "NULL:removed_at" => "NULL",
					  'bed_group' => ($this->code));
			$vals = array("client" => $client);
			$sql = sql_update($bedreg_table, $vals, $filt);
			$_SESSION['BED_TRANSFER']=$BED_TRANSFER = "";  // reset session var
		}												
		$result = sql_query( $sql ) or
			sql_warn( "Adding query failed for client $client, bed $num<br>"
				    . "the query was $sql" );
		$tmp_v=$this->code.$num;
		$who_list[$tmp_v]=$client;
		outline("Added " . bold(client_link($client_name) . " ($client)")  . " to bed " . bold($num));
		$_SESSION['GENDER_CONFIRM']=$GENDER_CONFIRM="";  // reset session vars
		$_SESSION['BAR_CONFIRM']=$BAR_CONFIRM="";
		return $result;
	}

	function transfer( $client, $num )
	{
		// transfers a client to a bed
		// not to be confused w/ client-morph (uses BED_TRANSFER var)
		// $num is where they are transferring to.

		global $bedreg_table,$who_list;

		unset($GLOBALS["TRANSFER_CONFIRM"]);
		$_SESSION['TRANSFER_CONFIRM']=null;
		// where are they now?
		$from = which_current_bed($client);
		if (!$from && !($a=$this->was_reg($client)))
		{
			$error="Transfer of client $client requested to {$this->code}, but client not found on any other bed.";
			outline(bigger(red($error)));
			return false;
		} elseif ($a && !$from) {
			$vals=array('bed_no'=>$num,'bed_group'=>$this->code,'FIELD:removed_at'=>'NULL','FIELD:removed_by'=>'NULL');
			$filt=array('bed_no'=>$a['bed_no'],'client'=>$client,'bed_group'=>$a['bed_group']);
			$res = sql_query(sql_update($bedreg_table,$vals,$filt));
			if (!$res) {
				outline(bigger(red('Transfer of previously registered client failed.')));
			}
			$from=$a;
		} else {
			
			$filt = array('bed_no'=>$from['bed_no'], 'NULL:removed_at' => 'NULL', 
					  'bed_group' =>$from['bed_group']);
			$vals = array('bed_no'=>$num, 'bed_group'=>$this->code,
					  'FIELD:sys_log'=>"COALESCE(sys_log,'') || 'transferred from {$from['bed_group']}, {$from['bed_no']} at ' || current_timestamp || '.\n'","FIELD:comments" => "'from {$from['bed_group']}:{$from['bed_no']}. ' || COALESCE(comments,'')"     );
			$sql = "UPDATE $bedreg_table SET " . read_filter($vals,",");        
			$res = agency_query($sql, $filt);        
			if (!$res)
			{
				outline(bigger(red("Transfer failed.")));
			}
		}
 		if (!$a) {
			$who_list[$this->code.$num]=$client; //$who_list[$from["bed_group"].$from["bed_no"]];
 			unset($who_list[$from["bed_group"].$from["bed_no"]]);
 		} else {
 			$who_list[$this->code.$num]=$client;
 		}
		outline(bigger(red(client_link($client)." transferred from {$from["bed_group"]}:{$from["bed_no"]} to {$this->code}:$num")),2);
		return $res;
	}

	function remove( $num, $client, $dtime="" )
	{
		global $bedreg_table, $UID, $BED_TRANSFER,$who_list;
    
		$dtime = ($dtime ? $dtime : datetimeof());
		if (db_read_only_mode()) {
			outline(bigger(red('Can\'t remove--Database is in read-only mode')));
			return false;
		}

		// TESTING    outline("In remove():  client is $client");
		// first, make sure client is registred for mat, so we
		// don't remove someone else.
		if ($this->who($num)==$client)
		{
			// if unregistered client try to transfer
			if ($BED_TRANSFER != "Y" && preg_match("'[^0-9]'",$client)) 
			{   
				out($this->show_unregclients($client));
				exit;  // leaving script to handle transfers!!!
			}        
			$query = "UPDATE $bedreg_table SET "
				. sqlsetify( "removed_by", $UID )
				. sqlsetify( "removed_at", $dtime, "last")
				. " WHERE bed_group = '" . $this->code . "' AND bed_no=$num"
				. " AND removed_by IS NULL AND removed_at IS NULL"
				. " AND client='$client'";
			// TESTING  outline("Query is $query");
			$result = sql_query($query) or 
				sql_warn("failed to remove client $client.<br>Query was $query");
			$_SESSION['BED_TRANSFER']=$BED_TRANSFER = "";  // transfer complete
			$tmp_v=$this->code.$num;
			unset($who_list[$tmp_v]);  //update who_list
			outline("Removed " . bold(client_link($client) . " ($client)") . " from bed " . bold($num));        
			return $result;
		}
		else
		{
			outline("Sorry, " . client_link($client) . " ($client) is not registered for number $num");    
			return "";
		}
	}

	function remove_many($list)
	{
		$time = $_REQUEST['removed_at_time'];
		if ($time=='CURRENT') {
			$ntime = timeof('now','SQL');
		} elseif ($ntime=timeof($time,'SQL')) {
		} else { return false; }
		$timestamp = dateof('now','SQL').' '.$ntime;
		foreach ($list as $num=>$client)
		{
			//outline('bed: '.$num.' client: '.$client);
			$this->remove($num,$client,$timestamp);
		}
		return true;
	}

	function process_many()
	{
		global $commands, $title;
		$var_session_remove = 'BEDREG_REMOVE_LIST_'.$this->code; //session variable
		$step=$_REQUEST[$this->remove_many_step];
		$removes=orr($_REQUEST[$this->var_remove_many],$_SESSION[$var_session_remove]);
		$_SESSION[$var_session_remove]=$removes;
		$remove_list=array();
		if ($removes) {
			foreach ($removes as $var => $value)
			{
				if ($value=='on')
				{
					list($num,$client) = $this->decipher_many_box($var);
				}
				$remove_list[$num]=$client;
			}
		}

		switch($step)
		{
		case 'remove':
			if ($this->remove_many($remove_list)) {
				$_SESSION[$var_session_remove]=array(); //unset
				break; //back to normal bed_reg page
			}
			$confirm_message=oline(red('Invalid timestamp ('.$_REQUEST['removed_at_time'].'). Failed to remove.'));
		case 'confirm':
			$confirm_message.=$this->confirm_remove_many($remove_list);
		case 'new':
		default:
			agency_top_header($commands);
			outline(bigger(bold($title),2));
			out($confirm_message);
			out($this->remove_many_form($remove_list));
			page_close();
			exit;
		}
	}

	function process_multi_comment()
	{

		global $commands, $title;

		$step = $_REQUEST['mcStep'];

		switch ($step) {
		case 'post' :
			if ($this->post_multi_comments()) {
				out(red('Comments updated'));
				break; //back to normal bed_reg page
			}
			$confirm_message .= alert_mark('Failed to post changes to comments');
		case 'new' :
		default :

			agency_top_header($commands);
			out(html_heading_2($title));
			out($confirm_message);
			out($this->multi_comment_form());
			page_close();
			exit;
		}

	}

	function post_multi_comments()
	{

		$comments = $_REQUEST['comments'];

		foreach ($comments as $bed_num => $comment) {

			if (!be_null($comment)) {
				$this->set_comments($bed_num,$comment);
			}

		}

		return true;

	}

	function multi_comment_form()
	{
		global $bed_pre, $bedreg_table, 
			$histcode, $DISPLAY, $show_volunteer_pick;
		// $DISPLAY["bed_assigns"] is the flag to show staff info

		if (! isset($show_volunteer_pick)) // only need to do this once
		{
			$show_volunteer_pick=$this->make_vol_picks();
		}
		// get the occupancy statuses
		//$this->load_whos();
		// load all clients at once
		$this->load_clients(); //sets $this->client_list as well as a global (that is probably not needed)
		$start = $this->start;
		$step = $this->step;
		$count = $this->count;
		$title = $this->title;
		$code = $this->code;
		$color = $this->color;
		$perms = $this->perm;  //read/write permissions    
		$ret = ""; // this is the string we will return
		$comments_cell = "";
		$rereg_all = $this->rereg_all;
		$date_display = " on " . dateof("NOW");
		$result = agency_query( "SELECT * FROM $bedreg_table", 
					    array("bed_group"=>$code, "NULL:removed_at"=>""),"bed_no");
		$total_recs = sql_num_rows($result);

		// create bed group heading and start table beds
		$ret .= oline(" ")
			. bigger(bold( $title . $date_display )) . $freeze . $auto_note 
			. oline(smaller(""),2)
			. oline(hlink($_SERVER['PHP_SELF'].'?bedgrp='.$this->code,'Cancel'),2)
			. formto() . tablestart("",
					 'bgcolor="'.$color.'" class="" border="1" cellspacing="0" cellpadding="0" width="100%"');
		// put assigned beds in array
		while ($row=sql_fetch_assoc($result))
		{
			// the bed number is also the element number or key
			$beds_asgn[$row["bed_no"]] = $row; // 2D array
		}
		// loop through all beds (even if no record in bed_reg table)
		for ($x = $start; $x<$start+$count; $x=$x+$step)
		{
			$flags = $this->get_flags($x);  // displayed to the left of the bed
			// dummy bed?  If so, skip
			if (stristr($flags,"xxx"))
			{
				continue;
			}
			if ($client = $this->who($x))
			{
				// bed assigned
				$num = $x;
				$comment = $beds_asgn[$x]["comments"];
				$vol_status = $beds_asgn[$x]["vol_status"];
				$am = $beds_asgn[$x]['bus_am'];
				$pm = $beds_asgn[$x]['bus_pm'];
				$ret .= rowstart();
				$ret .= cell($this->show_occupied($num, $client, $beds_asgn[$x],
									  $DISPLAY["bed_assigns"], $perms));
				// get comments for display
				$ret .= cell($this->show_comments($comment, $num,$am,$pm,$single_form = true), 
						 "width=230 valign=top");// valign to match vol status
				$ret .= rowend();
			}
			else
			{
				$ret .= rowstart();
				// two blank cells for vol_status and comments
				$ret .= cell($x.'.') . cell("");
				$ret .= rowend();
			}

			// need to use $x here because $num won't be set
			// if bed is currently vacant
			if ($this->set[$x]["hist"]=="Y")
			{
			  $ret .=  $this->show_history($x, $DISPLAY["bed_assigns"], $DISPLAY["photos"]);
			}
		}

		$ret .= row(rightcell(button('Update all comments'),'colspan="2"'))
			. tableend()
			. hiddenvar('bedgrp',$this->code)
			. hiddenvar('action','MULTI_COMMENT')
			. hiddenvar('mcStep','post')
			. formend();


		return $ret;
	}

	function confirm_remove_many($remove_list)
	{
		$confirm_button=formto()
			. button('Confirm and remove')
			. hiddenvar('bedgrp',$this->code)
			. hiddenvar('removed_at_time',$_REQUEST['removed_at_time'])
			. hiddenvar($this->remove_many_step,'remove')
			. hiddenvar('action','REMOVE_MANY')
			. formend();
		$out=oline(bigger(red('The following beds will be emptied')));
		$out.=tablestart('','border="1" bgcolor="'.$this->color.'" class=""')
			.row(cell('Bed#').cell('Client'));
		
		foreach($remove_list as $num=>$client)
		{
			$out.=row(cell(bigger($num)).cell(client_link($client)));
		}
		$out .= tableend();
		$out .= oline($confirm_button.' '.italic('Or'). ' change selection below');
		return $out;
	}

	function remove_many_form($remove_list)
	{
		global $bed_pre, $bedreg_table, 
			$histcode, $DISPLAY, $show_volunteer_pick;

		$this->load_clients();
		$start = $this->start;
		$step = $this->step;
		$count = $this->count;
		$title = $this->title;
		$code = $this->code;
		$color = $this->color;
		$perms = $this->perm;  //read/write permissions    
		$date_display = " on " . dateof("NOW");
		$result = agency_query( "SELECT * FROM $bedreg_table", 
					    array("bed_group"=>$code, "NULL:removed_at"=>""),"bed_no");
		$total_recs = sql_num_rows($result);

		$matcheck_selector = $this->generate_matcheck_selector();

		// if supervisor display freeze unfreeze options
		$ret .= oline(" ")
			. bigger(bold( $title . $date_display )) . $freeze . $auto_note 
			. oline(smaller(""),2)
			. tablestart('','bgcolor="'.$color.'" class="" border="1" cellspacing="0" cellpadding="0"')
			. $confirm
			. formto()
			. hiddenvar('action','REMOVE_MANY')
			. hiddenvar('bedgrp',$this->code)
			. row(cell().cell($this->remove_many_button()))
			. $matcheck_selector
			. hiddenvar($this->remove_many_step,'confirm');
		// put assigned beds in array
		while ($row=sql_fetch_assoc($result))
		{
			// the bed number is also the element number or key
			$beds_asgn[$row["bed_no"]] = $row; // 2D array
		}
		// loop through all beds (even if no record in bed_reg table)
		for ($x = $start; $x<$start+$count; $x=$x+$step)
		{
			$flags = $this->get_flags($x);  // displayed to the left of the bed
			// dummy bed?  If so, skip
			if (stristr($flags,"xxx"))
			{
				continue;
			}
			if ($client = $this->who($x))
			{
				// bed assigned
				$checked = isset($remove_list[$x]) ? true : false;
				$num = $x;
				$ret .= rowstart();
				$ret .= cell(center($this->remove_many_box($num,$client,$checked)));
				$ret .= cell($this->show_occupied($num, $client, $beds_asgn[$x],'Y',false));
				//	 $DISPLAY["bed_assigns"], $perms));
				$ret .= rowend();
			}
			else
			{
				$ret .= rowstart();
				// two blank cells for vol_status and comments
				$ret .= cell('');
				$ret .= cell(bigger($x.'.'));
				$ret .= rowend();
			}
		}
		$ret .= row(cell().cell($this->remove_many_button(),'colspan="2"'))
			. formend()
			. tableend();
		return $ret;
	}
		
	function generate_matcheck_selector()
	{
		global $bedreg_matcheck_times;
		//no selector for muni and ksh
		if ($times = $bedreg_matcheck_times[$this->code]) {
			foreach ($times as $time => $label) {
				$bedreg_matcheck_selector .= selectitem($time,$label,$time==$_REQUEST['removed_at_time']);
			}
			return row(cell().cell('These beds were found vacant at the '
						     . selectto('removed_at_time')
						     . selectitem('','----Choose a time----')
						     . $bedreg_matcheck_selector 
						     . selectend()));
		} else {
			return hiddenvar('removed_at_time','CURRENT');
		}
	}

	function remove_many_link()
	{
		return hlink($_SERVER['PHP_SELF'].'?bedgrp='.$this->code
				 .'&action=REMOVE_MANY&'
				 .$this->remove_many_step.'=new',smaller('Remove Multiple Clients from '.$this->title));
	}

	function multi_comment_link()
	{
		return hlink($_SERVER['PHP_SELF'].'?bedgrp='.$this->code
				 .'&action=MULTI_COMMENT&'
				 .'mcStep=new',smaller('Edit All Comments for '.$this->title));
	}

	function remove_many_box($num,$client,$checked='')
	{
		$name=$num.$this->variable_sep.$client;
		return formcheck($this->var_remove_many.'['.$name.']',$checked);
	}

	function remove_many_button()
	{
		static $d;
		if (!$d) {
			global $form_name;
			$js = '
                        function verify_remove_many_form()
				{
                              var obj = document.'.$form_name.'.removed_at_time;
                              var opt = obj.options[obj.selectedIndex].value;
					if (opt=="" || opt=="-1") {
						alert("Please select a mat-check time");
						return false;
					} else {
						return true;
					}
				}';
				
			$js = Java_Engine::get_js($js);
			out($js);
			$d = true;
		}
		$cancel= hlink($_SERVER['PHP_SELF'],smaller(italic('Cancel')));
		return button('Remove Selected Clients from '.$this->title,'submit','','','javascript: return verify_remove_many_form()').$cancel;
	}
	function decipher_many_box($var)
	{
		return explode($this->variable_sep,$var);
	}

	function set_vol($num, $status)
	{
		global $bedreg_table;
		// can't send none because postgres will turn that into zero
		//sql_update turns empty strings "" into NULL
		$values["vol_status"] = $status == "none" ? "" : $status;  
		$where["bed_group"] = $this->code;
		$where["bed_no"] = $num;
		$where["NULL:removed_at"]= "";

		$sql = sql_update($bedreg_table,$values,$where);
		$result = sql_query($sql) or
			sql_warn("Unable to update volunteer status:  $sql");

		outline(bigger("Updated Volunteer Status for bed #$num"));
	}

	function set_comments($num, $comments)
	{
		global $bedreg_table;    
		$values["comments"] = $comments;
		$filter_values["bed_group"] = $this->code;
		$filter_values["bed_no"] = $num;
		$filter_values["NULL:removed_at"] = "";
    
		$query = sql_update($bedreg_table,$values,$filter_values); 
		$result = sql_query($query) or
			sql_warn("Unable to update comments:  $query");
		outline(bigger("Updated comment for bed #$num"));
	}

	function set_bus_tickets($num,$am,$pm)
	{
		global $bedreg_table;
		$values['bus_am'] = $am;
		$values['bus_pm'] = $pm;
		$filter_values['bed_group'] = $this->code;
		$filter_values['bed_no'] = $num;
		$filter_values['NULL:removed_at'] = '';
		$query = sql_update($bedreg_table,$values,$filter_values);
		$result = sql_query($query) or
			sql_warn('Unable to update bus tickets: '.$query);
		outline('Updated bus tickets for bed #'.$num);
	}

	function set_history($bedhist)
	{
		$start = $this->start;
		$count = $this->count;
		$step = $this->step;
		if ($bedhist == "all")
		{
			// loop through all beds and show history
			for ($x = $start; $x<=$count; $x=$x+$step)
			{
				$this->set[$x]["hist"]="Y";
			}
		}
		elseif ($bedhist == "none")
		{
			// loop through all beds set - don't show history
			for ($x = $start; $x<=$count; $x=$x+$step)
			{
				$this->set[$x]["hist"] = "";             
			}
		}
		else
		{
			// set history for one bed
			$this->set[$bedhist]["hist"]="Y";
		}
	}

	/* $flags is an array of flag arrays where the 
	 *   first key is the text of the flag and the value
	 *   is an array of bed numbers e.g. $flags["v"]=array(1,2,3)
	 *   This function gets called when bed groups are first created
	 */
	function set_flags($flags)
	{
		//e.g. of $flags
		// flags["v"] = array (1,2,3)
		// flags["g"] = array (3,4,5)
		// ftext is the key , e.g. "v"
		while (list($ftext,$beds) = each($flags))
		{
			// TESTING     outline(bold("flags[$ftext]=$beds"));
			while(list($key,$bednum) = each ($beds))
			{
				// TESTING outline("flag[$key] = $bednum");
				// now set the flag text for each bed in the current
				// flag array  get_flags() gets this text and show() displays it
				$this->set[$bednum]["flagtext"].=$ftext . " ";
			}
		}
	}

	function set_permissions($perms)
	{
		//    outline("group is $this->code AND perm is $perms");
		$this->perm = $perms;    
	}

} // end of bed_group class

function create_groups( $grp_list )
{
	global $bed_group_prefix;
	foreach ($grp_list as $bed_group )
	{
            global $$bed_group;
		$$bed_group=$_SESSION[$bed_group];
            if (! isset($$bed_group) )
            {
			$$bed_group = new bed_group();
			$var = $bed_group_prefix . $bed_group;
			global $$var;
			$$bed_group->set(${$var}["code"],
					     ${$var}["title"],
					     ${$var}["color"],
					     ${$var}["start"],
					     ${$var}["count"],
					     ${$var}["genpref"],
					     ${$var}["step"],
					     ${$var}["night_startsat"],
					     ${$var}["perm"],
					     ${$var}["rereg_all"]);

			// set flags for appropriate beds
			// get array of beds to flag and
			// get flags e.g. $flags["V"]=array bednums
			$flagged = ${$var}["flags"]; //["V"];                    
			if ($flagged)
			{
				$$bed_group->set_flags($flagged);
			}
			/*  TESTING outline("Created new " . $bed_group 
			 * . "rereg_all = " . ${$var}["rereg_all"]);            
			 *. "genpref = " . ${$var}["genpref"]
			 *            . ",starting at " . ${$var}["start"]
			 *            . "; night is " . ${$var}["night_startsat"]);
			 */
            
            }
	}
}
?>
