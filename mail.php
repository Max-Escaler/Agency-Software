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

$select_name   = 'mail_selected';
$types_name    = 'mailtype';
$dates_name    = 'maildate';
$clients_name  = 'mailclients';
$comments_name = 'mailcomments';

function mail_codes_to( $var, $default='' )
{
	return selectto( $var )
			. do_pick_sql("SELECT mail_type_code as value, SUBSTRING(description FROM 0 FOR 25) as label FROM l_mail_type ORDER BY 2",$default)
			. selectend();
}

function get_mails( $filter, $order="name_full,mail_date DESC")
{
	global $mail_select_sql;
	return agency_query($mail_select_sql,$filter,$order);
}

function get_mails_waiting( $filter=array() )
{
	$filter["delivery_code"]="WAITING";
	return get_mails($filter);
}

function mark_mail_status( $pickup_date, $disposition )
{
// This will take the mail that has been marked as picked up or delivered, and put it in the DB.
	global $mail_table;
	if (!in_array($disposition,array("DELIVERED","RETURNED","RETURNALL")))
	{
		log_error("mark_mail_status: Unknown disposition code: $disposition");
		return false;
	}
	if ($disposition=="RETURNALL")
	{
		$cutoff=dateof($_REQUEST["cutoff"],"SQL");
		if ((!$cutoff) || ($cutoff>prev_day("now",7)))
		{
			outline(bigger(bold("mark_mail_status: Attempting to return mail held for less than 7 days")));
			return false;
		}
		$up_val["FIELD:delivery_date"]="current_date";
		$up_val["delivery_code"]="RETURNED";
		$up_fil["<=:mail_date"]=$_REQUEST["cutoff"];
		$up_fil["delivery_code"]="WAITING";
		return agency_query(sql_update("tbl_$mail_table",$up_val,$up_fil))
			? count($pieces) . " pieces of mail successfully marked as $disposition on " . dateof($pickup_date)
			: "ERROR.  Failed to mark mail as $disposition";
	}	
	// loop through checkboxes, named pickup_{mail_id}
	$pickup_date=dateof($pickup_date,"SQL");
	$pieces=array();
	foreach ($_REQUEST as $key=>$value)
	{
		$GLOBALS["DEBUG"] && outline("Testing piece $key");
		if (preg_match('/pickup_([0-9]{1,8})/',$key,$matches))
		{
			array_push($pieces,$matches[1]);
		}
	}
	if ($pieces<>array()) // matches found
	{
		$up_fil["IN:mail_id"]=$pieces;
		$up_val["delivery_date"]=$pickup_date;
		$up_val["delivery_code"]=$disposition;
		return agency_query(sql_update("tbl_$mail_table",$up_val,$up_fil))
			? count($pieces) . " pieces of mail successfully marked as $disposition on " . dateof($pickup_date)
			: "ERROR.  Failed to mark mail as $disposition";
	}
}

function show_mails($mails,$max="")
{
	$max=(($max) && ($max<=sql_num_rows($mails))) ? $max : sql_num_rows($mails);
	$rows = header_row("date","type","status");
	for ($x=0;$x<$max;$x++)
	{
		$m = sql_fetch_assoc($mails);
		if ($m["delivery_code"]=="DELIVERED")
		{
			$status = "Delivered on " . dateof($m["delivery_date"]);
		}
		elseif ($m["delivery_code"]=="RETURNED")
		{
			$status = "Returned on " . dateof($m["delivery_date"]);
		}
		elseif ($m["delivery_code"]=="WAITING")
		{
			$status = bold(red("Waiting for pickup"));
		}
		$rows .= row( cell(dateof($m["mail_date"]))
					. cell($m["_mail_type"])
					. cell($status)
			      . cell(smaller(link_engine(array("object"=>"mail","id"=>$m["mail_id"])))));
	}
	return table($rows,"Mail",'border="1"');
}

function show_mail_list($format="")
{
     global $mail_select_sql,$mail_table,$client_table;
     $filter["NULL:delivery_date"]="dummy";
     if ($format=="signout")
     {
/*
	     $sql = $mail_select_sql;
	     $out = header_row("id","client","received","type","signature");
//$GLOBALS["query_display"]="Y";
*/
		$set = get_mails_waiting();
		$template_file='mail_signout.sxw';
		$merged_file=oowriter_merge($set,$template_file);
		serve_office_doc($merged_file,$template_file); //exits
     }
     elseif ($format=="posting")
     {
	    $sql = "SELECT DISTINCT $mail_table.client_id,name_full,name_last from $mail_table
				LEFT JOIN $client_table USING (client_id)";
		$set=agency_query($sql,array("delivery_code"=>"WAITING"),"name_last");
		$template_file='mail_list.sxw';
		$merged_file=oowriter_merge($set,$template_file,'ucwords(strtolower($x["name_full"])) . " (" . $x["client_id"] . ")"');
//		$set=get_mails(array("delivery_code"=>"WAITING"));
//		$merged_file=oowriter_merge($set,$template_file,'$x["name_full"]');
		serve_office_doc($merged_file,$template_file); //exits
     } elseif ($format=='today') {
	     $sql = $mail_select_sql; 
	     $out = header_row('','date','return','type','client');
	     $filter['mail_date'] = dateof('now');
     }
     else
     {
	     $sql = $mail_select_sql; 
	     $out = header_row("id","","date","return","type","client");
		$cutoff=prev_day(dateof("now"),7);
		$after = oline("Mark selected as "
					. selectto("action")
					. selectitem("DELIVERED","picked up")
				 	. selectitem("RETURNED"," returned")
					. selectend() 
					. " on "
				    . formdate("pickup",dateof(prev_day()))
				    . button("Mark"),2)
					. hlink($_SERVER['PHP_SELF']."?action=RETURNALL&cutoff=$cutoff","Mark all mail received on or before " . dateof($cutoff) . " as returned");
     }
     $mail=agency_query($sql,$filter,"client_name(client_id)");
     // this should really be a show mail heads/ show mails function
     while ($m=sql_fetch_assoc($mail))
     {
	     switch ($format)
	     {
	     case "posting" :
		     $out .= row(cell(smaller($m["client_id"]),2) . cell($m["name_full"]));	
		     break;
	     case "signout" :
		     $out .= row(cell(smaller($m["client_id"]),2)
				     . cell($m["name_full"])
				     . cell(dateof($m["mail_date"]))
				     . cell($m["_mail_type"])
				     . cell("_____________________________________"));
		     break;
	     case 'today' :
		     $out .= row(cell(smaller(link_engine(array('object'=>'mail','id'=>$m['mail_id'])))) 
				   . cell(dateof($m['mail_date'])) 
				   . cell(dateof($m['returns_on']))
				   . cell($m['_mail_type']) 
				   . cell(client_link($m['client_id']). smaller(' ('. $m['client_id']. ')')));		     
		     break;
	     default : 
		   $out .= row(cell(smaller(link_engine(array("object"=>"mail","id"=>$m["mail_id"])))) 
			     . cell(formcheck("pickup_{$m["mail_id"]}")) 
			     . cell(dateof($m["mail_date"])) 
			     . cell(dateof($m["returns_on"]))
			     . cell($m["_mail_type"]) 
			     . cell(client_link($m['client_id']). smaller(' ('. $m['client_id']. ')')));
	     }
     }

     return formto() . table($out) . $after 
//			. hiddenvar("action","pickup") 
			. formend();
}

function show_client_mail_heads( $clients, $def_date="", $def_type="",$def_selected=false )
{
	global $select_name,$types_name,$dates_name,$clients_name,$comments_name;
	static $count;
	$def_date=orr($def_date,dateof("now"));
	$photos = $_REQUEST["show_photos"];
/*
	$rows = $photos
			? header_row("Name","Photo")
			: header_row("Name");
*/

	$entry_def = get_def('entry');
	
    while ($cl=sql_fetch_assoc($clients))
    {
	$count++;
	$deceased = client_death_f($cl[AG_MAIN_OBJECT_DB.'_id'],$death_date,true);
	$opts = $deceased ? ' style="background-color: #efefef; border-bottom: solid 1px #888;"' : '';
	$cl_link = oline(client_link($cl['client_id']). smaller(' ('.$cl['client_id'].')')) . ($deceased ? oline($deceased) : '');

	//add last shelter entry. bug 22443
	$entry_filter = array('client_id'=> $cl['client_id'],
				    'entry_location_code' => 'SHEL517');
	$res= array_shift(get_generic($entry_filter,'entered_at DESC', 1, $entry_def));
	
	$entry = $res ? bold(' LAST SHELTER ENTRY: ') . datetimeof($res['entered_at']) : 'No shelter entries';
	$rows .= row(
			 ($priority=has_priority($cl))
			 //has priority
			 ?  ( cell( $cl_link
					. oline(bold("PR: ") . priority_status_f($priority,"","terse"))
					. bold('HOUSING: ').housing_status_f($cl[AG_MAIN_OBJECT_DB.'_id'])
					. bold(" BAR: ") . bar_status_f($cl,"mail",$is_provisional)
					. $entry)
				. ($photos ? cell( client_photo($cl["client_id"],0.35)) : "")
				. cell(client_staff_assignments_f($cl[AG_MAIN_OBJECT_DB.'_id']))
				. cell( oline("Date: " . formvartext( "{$dates_name}[$count]",$def_date,10 )
						  . "add: " . formcheck("{$select_name}[$count]",$def_selected))
					  . oline(" Type: " . mail_codes_to( $types_name."[$count]",$def_type))
					  . "Com: " . formvartext($comments_name. "[$count]","",30)
					  . hiddenvar($clients_name . "[$count]",$cl["client_id"])
					  )
				)
			 //no priority
			 : (cell($cl_link . " (no priority)") . cell(client_staff_assignments_f($cl[AG_MAIN_OBJECT_DB.'_id'])))
			 , 'valign="top"'.$opts);
    }
    return $rows;
}

?>
