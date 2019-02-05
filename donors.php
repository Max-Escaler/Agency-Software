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

function generate_donor_profile( $filter, $order="" )
{
	global $donor_profile_template;
	
	$def   = get_def(AG_MAIN_OBJECT_DB);
	$table = $def['table'];
	if (is_numeric($filter))
	{
		$filter=client_filter($filter);
	}
	// CREATE SQL TO GET LAST 10 Years of donor totals
	$current=year_of(dateof("now","SQL"));
	for ($x=0;$x<10;$x++)
	{
		$subsql .= "COALESCE((SELECT gift_total FROM donor_total WHERE donor_id=donor.donor_id AND year='$current'),0) AS total_$current,";
		$current--;
	}
	$prof_sql = "
	SELECT $table.*,
	COALESCE((SELECT staff_name(staff_id) FROM staff_assign WHERE $table.donor_id=donor_id AND staff_assign_date_end IS NULL ORDER BY staff_assign_date DESC limit 1),'(none)') AS staff_assign,
	bool_f(skip_thanks) AS skip_thanks_f,
	address_mail(address_id($table.donor_id)) AS address,
	(SELECT phone_no FROM address(donor_id)) AS phone_no,
      bool_f(not is_inactive) as active_code,
	address_emails(address_id(donor_id)) AS emails,
	$subsql"."
	ds.max AS gift_biggest,
      ds.last_amount AS gift_last_amount,
      ds.last_date AS gift_last_date,
      ds.first_amount AS gift_first_amount,
      ds.first_date AS gift_first_date,
      ds.count AS gift_count,
      ds.total AS gift_total
      "
// 	"(SELECT max(gift_cash_amount) FROM gift_cash WHERE gift_cash.donor_id=donor.donor_id) AS gift_biggest,
// 	(SELECT max(gift_cash_date) FROM gift_cash WHERE gift_cash.donor_id=donor.donor_id) AS gift_last,
// 	(SELECT min(gift_cash_date) FROM gift_cash WHERE gift_cash.donor_id=donor.donor_id) AS gift_first,
// 	(SELECT count(*) FROM gift_cash WHERE gift_cash.donor_id=donor.donor_id) AS gift_count,
// 	(SELECT sum(gift_cash_amount) FROM gift_cash WHERE gift_cash.donor_id=donor.donor_id) AS gift_total"
	 
	."  FROM ".$table." LEFT JOIN address_preferred USING (donor_id) LEFT JOIN donor_stat ds USING (donor_id)";
//	$donor=client_get($id);
	$dummy=agency_query("SET DATESTYLE TO SQL");
	$donor=agency_query($prof_sql,$filter,$order);
	$gift_cash=agency_query("SELECT * FROM gift_cash",$filter,"donor_id, gift_cash_date DESC");
	$gift_inkind=agency_query("SELECT * FROM gift_inkind",$filter,"donor_id,gift_inkind_date DESC");
	$notes=agency_query("SELECT *,added_at::date AS date FROM donor_note",$filter,"donor_id, added_at DESC");
/*
	//adding gifts - not sure how to merge w/ oo ...
	$gift_sql = "
         SELECT gift_date AS gs_gift_date,
                gift_amount AS gs_gift_amount,
                gift_form AS gs_gift_form,
                response_code AS gs_response_code,
                restriction_code AS gs_restriction_code,
                skip_thanks AS gs_skip_thanks,
                gift_comment AS gs_gift_comment
         FROM gift";

	$gift=agency_query($gift_sql,client_filter($id));
*/
	$repo=oowriter_merge_new(array($donor,$gift_cash,$gift_inkind,$notes),$donor_profile_template);
	return $repo;
}

function address( $id )
{
	$a=sql_fetch_assoc(agency_query("SELECT * FROM address( $id )"));
	return $a;
}

function donor_flags_f( $id, $sep=", " )
{
	$flags=get_generic( client_filter($id),"","","donor_flag");
	while ($r=array_shift($flags))
	{
		$f[]=link_engine(array("id"=>$r["donor_flag_id"], "object"=>"donor_flag"),$r["donor_flag_type_code"]);
	}
	return implode($sep,$f);
}

function donor_stat_f ($id)
{
	$def = get_def('donor_stat');
	$rec = array_shift(get_generic(client_filter($id),'','','donor_stat'));
	unset($rec['donor_id']);
	foreach ($rec as $item=>$value) {
		$out .= oline(label_generic($item,$def,'list').': '.value_generic($value,$def,$item,'list'));
	}
	return smaller($out,2);
}
function donor_note_f ($id)
{
	$def = get_def('donor_note');
	$filter = client_filter($id);
	$filter['is_front_page'] = sql_true();
	$res = get_generic($filter,' added_at DESC','','donor_note');
	$add_link = link_engine(array('object'=>'donor_note','action'=>'add','rec_init'=>client_filter($id)),'Add a Note');
	if (count($res) < 1) {
		return $add_link;
	}
	$out = $add_link;
	while ($a = array_shift($res)) {
		$link = link_engine(array('object'=>'donor_note','id'=>$a['donor_note_id'],'action'=>'edit'),'Edit/Remove');
		$out .= row(cell(value_generic($a['added_at'],$def,'added_at','list'),' valign="top" class="donorCommentDate"')
			. cell(oline($link).webify($a['note']),' class="donorCommentNote"'));
	}
	return table($out,'',' bgcolor="" class="donorComment"');
}

function volunteer_status_f ($id)
{
	//first, find any hours
	$def = get_def('volunteer_hours');
	$filter['donor_id']=$id;
	$res = get_generic($filter,'','',$def);
	$hours = 0;
	while ($a = array_shift($res)) {
		$hours += $a['volunteer_hours'];
	}

	//regestered volunteers
	$def = get_def('volunteer_reg');
	$filter = array();
	$filter['donor_id']=$id;
	$filter['FIELD<=:volunteer_reg_date']='CURRENT_DATE';
	$filter[] = array('NULL:volunteer_reg_date_end'=>'','FIELD>=:volunteer_reg_date_end'=>'CURRENT_DATE');
	$res = get_generic($filter,' added_at DESC','1','volunteer_reg');
	if (count($res)>0) {
		$a = array_shift($res);
		$regs = $a['length_commitment_code'] 
			? value_generic($a['length_commitment_code'],$def,'length_commitment_code','list')
			: value_generic($a['minimum_hour_commitment'],$def,'minimum_hour_commitment','list').' hours';
		$out = oline('Registered volunteer ('.dateof($a['volunteer_reg_date']).')')
			.'Commitment: '.bold($regs).red(' ('.$hours.' completed hours)');
		$out .= $a['referral_source_code'] ? '<br />'.red(value_generic($a['referral_source_code'],$def,'referral_source_code','list')) : '';
	} elseif ($hours > 0) {
		$out = 'Unregistered volunteer '.red('('.$hours.' completed hours)');
	}
	return $out ? smaller($out,1) : false;
}

function donor_data_merge_select_box($varname,$default='',$options='')
{
	global $donor_data_merge_templates;
	foreach ($donor_data_merge_templates as $temp=>$label) {
		$selects .= selectitem($temp,$label,$temp==$default);
	}
	return selectto($varname,$options)
		.$selects
		.selectend();
}

function client_show( $id )
{	
	global $DISPLAY_CLIENT,$UID,$colors;
	$client=sql_fetch_assoc(client_get($id));
	$def = get_def(AG_MAIN_OBJECT_DB);
	$ID_FIELD = $def['id_field'];
	$id=$client[$ID_FIELD];
	$stat_filter=client_filter($id);
	$stat_filter["NULL:year"]="placeholder";
	$stats=array_shift(get_generic( $stat_filter,"","","donor_total"));
	$add_flag=smaller(link_engine(array("object"=>"donor_flag","action"=>"add","rec_init"=>client_filter($id)),"Add a flag"));

	$address=address($id);
	$email1=$address["name_email"];
	$email2=$address["name2_email"];
	$two=$email1 && $email2;
	$emails = ($email1 ? link_email( $email1 ) : "")
			. ($two ? ", " : "")
			. ($email2 ? link_email( $email2 ) : "");
	//staff assigns
	$other_stuff=row(rightcell(smaller(oline('Staff Assignments')
							 .link_engine(array('object'=>'staff_assign',
										  'action'=>'add',
										  'rec_init'=>array('donor_id'=>$id))
									  ,smaller('Add new staff assignment')))
					     ,' style="{valign: top; border-right: solid 1px gray; padding: 4px; }"')
				 .leftcell(client_staff_assignments_f($id),' style="vertical-align: top; padding: 4px;"')); //and here as well
	//throw other stuff under staff assigns
	$other_stuff .= row(rightcell(smaller('Donor Type: ',2),' style="vertical-align: top; border-right: solid 1px gray; padding: 4px; "')
				  . leftcell(bold(value_generic($client['donor_type_code'],$def,'donor_type_code','list'))
						   ,' style="vertical-align: top; padding: 4px;"'));
	$other_stuff .= row(rightcell(smaller('Mail Code: ',2),' style="vertical-align: top; border-right: solid 1px gray; padding: 4px; "')
				    . leftcell(bold(value_generic($client['send_mail_code'],$def,'send_mail_code','view'))
						   ,' style="vertical-align: top; padding: 4px;"'));
	$other_stuff .= row(rightcell(smaller('Ask Code: ',2),' style="vertical-align: top; border-right: solid 1px gray; padding: 4px; "')
				    . leftcell(bold(value_generic($client['ask_code'],$def,'ask_code','view'))
						   ,' style="vertical-align: top; padding: 4px;"'));
	$vol_status = volunteer_status_f($id);
	$other_stuff .= $vol_status 
		? row(rightcell(smaller('Volunteer Status: ',2),' style="{vertical-align: top; border-right: solid 1px gray; padding: 4px; }"')
				    . leftcell($vol_status
						   ,' style="{vertical-align: top; padding: 4px;}"'))
		: '';
	//gift stats if we want them here
// 	$other_stuff .= row(rightcell(smaller('Gift Statistics: ',2),' style="{vertical-align: top; border-right: solid 1px gray; padding: 4px; }"')
// 				    . leftcell(donor_stat_f($id)
// 						   ,' style="{vertical-align: top; padding: 4px;}"'));
	
	//anonymous ?
// 	$other_stuff .= row(rightcell(smaller('Anonymous? ',2),' style="{valign: top; border-right: solid 1px gray; padding: 4px; }"')
// 				    . leftcell(sql_true($client['is_anonymous']) ? bold(red('Anonymous')) : smaller('No'))
// 						   ,' style="{vertical-align: center; padding: 4px;}"');
	//-----client notes-----//
	$comments = donor_note_f($client[$ID_FIELD]);
	$client_note_hide_button = oline(right(Java_Engine::hide_show_button('DonorNote',false) . smaller('Notes',3)));

	out(div($client_note_hide_button . Java_Engine::hide_show_content($comments,'DonorNote',false),'',' class="donorComment"'));

	$other_stuff=table($other_stuff,'',' class="" cellspacing="0"');
	if (sql_true($client['is_inactive'])) {
		$inactive = oline(bold(red('INACTIVE'))); 
		$donor_name = gray(bold(bigger(client_name($client),3)));
	} else {
		$donor_name = bold(bigger(client_name($client),3));
	}
	
	/*
	$comments = be_null($client['donor_comment'])
				  ? ''
				  : div(webify($client['donor_comment']),'',
					  ' style="{border: 1px solid gray; background-color: #eff; width: 25em; font: x-small sans-serif; margin: 4px; padding: 4px;}"');
	*/
	$out .= tablestart('','bgcolor="white" class="donorTitle"')
		. row(topcell(
			  oline($donor_name 
				. (($stats["gift_count"]>0) ? " (" . bold(currency_of($stats["gift_total"])) . " in " . $stats["gift_count"] . " gifts.)" : " (no gifts.)"))
				. (sql_true($client["is_anonymous"]) ? oline(bigger(bold(red("(Anonymous)")))) : "")
			  . $inactive
			  . oline(bigger("Donor ID # " . $id,1) . " ("
				    . ( ($fl=donor_flags_f($id)) ? "Flags: $fl" : smaller("no flags"))
				    . ") $add_flag")
			  )) . row("")
		. tableend();
	out($out);

	$address_f = !be_null(array_filter($address))
		? oline(webify($address["address_mail"])) . oline($address["phone_no"]) . $emails
		: link_engine(array('object'=>'address','action'=>'add','rec_init'=>client_filter($id)),'Add an Address');
	$out = table(row(topcell(box($address_f))
			     .topcell($other_stuff)),null,' class="" width="60%"');
// 	$out .= donor_stat_f($id);

	$summary_hide_button = Java_Engine::hide_show_button('ClientSummary',false);

// 	$loading_graphic = div(oline(smaller('loading...',3)).html_image($GLOBALS['AG_IMAGES']['page_loading_animation']),'page_loading',' style="display: none;"');
// 	$s_button = Java_Engine::toggle_tag_display('div',bold('show empty records'),'childListNullData','block',true);
// 	$h_button = Java_Engine::toggle_tag_display('div',bold('hide empty records'),'childListNullData','block',true);

// 	outline($loading_graphic.Java_Engine::hide_show_buttons($s_button,$h_button,true,'inline',AG_LIST_EMPTY_HIDE)
// 		  . smaller(" (click only " . bold(italic("after")) . " page loads)",3));
	out(Java_Engine::get_js('document.getElementById(\'page_loading\').style.display="inline";'));
	out(span($summary_hide_button. '&nbsp;'.section_title('Donor Summary'),' class="childListTitle"') );
	out(Java_Engine::hide_show_content($out,'ClientSummary',false));
	//loading graphic
 	out(div(html_image($GLOBALS['AG_IMAGES']['page_loading_animation']) . ' loading '.$def['singular'].' records...','page_loading',' style="border: solid 1px black; padding: 5px; width: 17em; white-space: nowrap; background-color: #efefef; position: fixed; bottom: 2px; right: 2px; display: none;"'));
 	out(Java_Engine::get_js('document.getElementById(\'page_loading\').style.display="block";'));

	list_all_child_records(AG_MAIN_OBJECT_DB,$id,$def,true);

	//page finished loading, hide loading graphic
  	out(Java_Engine::get_js('document.getElementById(\'page_loading\').style.display="none";'
 					.'document.getElementById(\'childListJSButtons\').style.display="";')); //only display the buttons after page has loaded

}

function donor_search($allow_other="N",$auto_forward=true,$use_old=false)
{
	return client_search($allow_other,$auto_forward,$use_old);
}

function notes_search()
{
	$s = $_REQUEST['QuickSearch'];
	$filter=array('ILIKE:note'=>'%'.$s.'%');
	// this filter will match names either in "first last" or "last, first"

	$def = get_def('donor_note');
	$fields = $def['list_fields'];
	array_unshift($fields,'donor_id');

	$control = array_merge(array( 'object'=> ('donor_note'),
						'action'=>'list',
						'list'=>array('filter'=>$filter,
								  'fields'=>$fields),
						'page'=>'display.php'
						),
				     orr($_REQUEST['control'],array()));
	$result = call_engine($control,'',true,true,$TOTAL,$PERM);
	if (!$PERM) { return 'No Permissions'; }
	$sub = oline('Found '.$TOTAL.' results for '.bold($s));
	return $sub . $result;

}

function client_search($allow_other="N",$auto_forward=true,$use_old=false)
{
	// This page performs a client search based on global variables

	// The fields that can be searched on are defined in the $fields array.
	// In order for a field to be searched, the field name must be included
	// in this array.  Additionally, the variables $fieldnameText and
	// $fieldnameType (i.e., for "Gender" field "GenderText" and "GenderType"
	// must be passed.  The Text is the value to search for, and the Type
	// is the type of comparison test being made (see the switch on "type"
	// in build_where_string for the possible types.

	// the following additional variables are used:
	// $order : sort order field
	// $limit : limit number of queries
	// $revorder : boolean to reverse sort order
	// $showsql : display sql query

	// $select_to_url : if this is set, it will enable the selection
	// of displayed clients, with the results being passed to this url.

	// $QuickSearch : if this value is set, then it is evaluated numeric or text
	// 		if numeric, it does a lookup against client_id
	//		otherwise, it does a substring search on fullname

	global $order, $limit, $revorder, $showsql, $select_to_url, 
		$client_select_sql, $client_search_fields,$QuickSearch,$engine;

	$QuickSearch = trim(orr($QuickSearch,$_REQUEST['QuickSearch']));
	$fields=$client_search_fields;

	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];

	// First, check to see if this is a "quick search"
	// If so, check if we have text or a number
	// text searches on name, number on clientID
	if (isset($QuickSearch))
	{
		if ($QuickSearch=="")
		{
			return oline("You must specify a search criteria for a Quick Search");
		}
		elseif ( is_numeric( $QuickSearch )  && ($QuickSearch <= 2147483647))
		{
			global $donor_idType, $donor_idText;
			$donor_idType = "equal";
			$jump = $donor_idText = $QuickSearch;
		}
		elseif( $x = dateof($QuickSearch,"SQL") )
		{
			global $dobType, $dobText;
			$dobType = "equal";
			$dobText = $x;
		}
		elseif( $x = ssn_of($QuickSearch) )
		{
			global $ssnType, $ssnText;
		$ssnType = "equal";
		$ssnText = $x;
		}
		elseif (preg_match('/^kc(id)?[: ]*([0-9]{1,6})/i',$QuickSearch,$matches)) // match case_ID
		{
			global $king_cty_idType, $king_cty_idText;
			$king_cty_idType="equal";
			$king_cty_idText=$matches[2];
			// this is a cheat, doing the lookup here on this page
			// so we can jump directly to the id page
			$client=sql_fetch_assoc(get_clients(array("trim(king_cty_id)"=>$king_cty_idText))); //hack to get around our varchar kcids
			$jump=$client["client_id"];
		} elseif (preg_match('/a(uth)?[: ]*([0-9]{1,7})/i',$QuickSearch,$matches)) {
			$rec = array_shift(get_generic(array('auth_id'=>$matches[2]),'','','auth'));
			if ( $x = $rec["client_id"] )
			{
				global $client_idType, $client_idText;
				$client_idType = "equal";
				$jump = $client_idText = $rec["client_id"];
			}
		} elseif (preg_match('/^([a-z_]{3,}):([0-9]*)$/i',$QuickSearch,$m)) {
			if ($found = search_engine_object($m[1],$m[2])) {
				return $found;
			}
		}
		else
		{
			global $donor_nameText, $donor_nameType;
			$donor_nameType = "sub";
			$donor_nameText = $QuickSearch;
		}

		//auto redirect on 1 result
		$filter = array('ILIKE:donor_name'=>'%'.$QuickSearch.'%');
		$res = get_generic($filter,'','','donor');
		if (count($res)==1) {
			$tmp = array_shift($res);
			$jump = $tmp['donor_id'];
		}

		// hack to make client ID lookups jump to client page
		// rather than displaying 1 search result
		if ($auto_forward && $jump) 
		{//I had to make this optional so things like log and bedreg would still work - JH
			//$jump is set on any search that can only return one record - KT
			global $client_page;
			$client=sql_fetch_assoc(client_get($jump));  // This seemingly un-necessary step will
						    // pick up clients who have been unduplicated
			if ($client) {
				header("Location: $client_page?id=" . $client[AG_MAIN_OBJECT_DB.'_id']);
				page_close();
				exit;
			}
		}
	}
	// This ends the Quick Search processing.
	$empty = "No";
	foreach ($fields as $x)
	{
		$type=$x."Type";
		$text=$x."Text";
		global $$type, $$text;
		// outline("$x " .  $$type . " " . $$text);
		if ( isset( $$type ) || isset( $$text) )
		{
			$empty = "";
		}
	}
	if ($empty)
	{
		return oline("Sorry, no search criteria specified.  Maybe you forget to enter some, or maybe your session timed out.  Either way, go BACK to the previous page and retry your search.");
	}
	elseif (!$use_old)
	{
		// 	this hack directs queries to use engine
		$control_d = array( 'object'=> ('donor'),
					'action'=>'list',
					'list'=>array('filter'=>$filter)
					);
		$result_d = call_engine($control_d,'',true,true,$TOTAL,$PERM);

		if ($TOTAL > 1) {
			$dt = $TOTAL . ' Results in ';
		} elseif ($TOTAL == 1) {
			$dt = '1 Result in ';
		} else {
			$dt = 'No results in ';
		}
		$dt = html_heading_4($dt . ucwords($mo_noun));
		
		$filter_a = array(array(
						'ILIKE:address_names'=>'%'.$QuickSearch.'%',
						'ILIKE:organization'=>'%'.$QuickSearch.'%'
						));

		$control_a = array('object' => 'address',
					 'action'=>'list',
					 'list'=>array('filter'=>$filter_a));

		$result_a = call_engine($control_a,'',true,true,$TOTAL,$PERM);
		if ($TOTAL > 1) {
			$at = $TOTAL . ' Results in Address';
			$dt .= oline(seclink('address',smaller($at)),3);
		} elseif ($TOTAL == 1) {
			$at = '1 Result in Address';
			$dt .= oline(seclink('address',smaller($at)),3);
		} else {
			$at = 'No results in Address';
		}

		$result_a = anchor('address'). html_heading_4($at) . $result_a;
		$result_d = $dt . $result_d; 


		return $result_d . $result_a;
	} else {
		  		return search( $fields, $client_select_sql . ' WHERE 1=1 ', AG_MAIN_OBJECT_DB, "show_client_heads", "client", "clients",$allow_other);
	}
}

function client_name($idnum,$max_length=0,$text_only=false)
{
	if (is_array($idnum) )
	{
		$q=$idnum;
	}
	elseif (! is_numeric( $idnum ))
	{
		return $idnum;
	}
	else
	{
		$q=client_get($idnum);
		if (! ($q and (sql_num_rows($q)<2)) )
		{
			log_error("donor_name: lookup failed for $idnum.");
		}
		else
		{
			$q=sql_fetch_assoc($q);
		}
	}
	if ($q)
  	{
		$name = trim($q['donor_name']);
		$alias = trim($q["name_alias"]);
		if ($text_only) {
			$full = $name.($alias ? ' ( aka '.$alias.')' : '');
			return $max_length ? substr($full,0,$max_length) : $full;
		}
		if (!$max_length)
		{
		}
		elseif (($max_length > 0)  && (strlen($name) > ($max_length-5)))
		{
			$name=substr($name,0,$max_length);
			$alias="";
		}
		else
		{
			$alias = substr($alias,0,$max_length - 5 - strlen($name));
		}
		$name .=  $alias ? smaller(" (" . red("aka ") . $alias . ")") : "";
	}
	else
	{
		$name="ID # $idnum (not found)";
	}
	return $GLOBALS['AG_DEMO_MODE'] ? preg_replace('/[a-z]/','x',preg_replace('/[A-Z]/','X',trim($name))) : trim($name);
}

function donor_reg_search()
{
      global $def,$rec,$main_object_reg_search_fields,$engine; 
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      foreach ($main_object_reg_search_fields as $x)
	{
		    $$x=$rec[$x];
	}
      $meta_last =  levenshteinMetaphoneDistance(sqlify($donor_name),'donor_name');
	$filter=array(	
			  array(
				  'FIELD<=:'.$meta_last => LEVENSHTEIN_ACCEPT,
				  'ILIKE:donor_name'=>"%$donor_name%")
				);
      $filter=array_filter($filter); //remove blanks which crash sql searches

	//------ Highlight exact matches ------//
	foreach ($main_object_reg_search_fields as $field) {
		$engine[AG_MAIN_OBJECT_DB]['fields'][$field]['value_format_list'] = '(strtolower($x)==strtolower(\''.$rec[$field].'\')) ? bigger(bold($x)) : $x';
		$REC[$field] = $rec[$field];
	}
	$add_link_array = array('object'=>AG_MAIN_OBJECT_DB,
					     'action'=>'add',
					     'rec_init'=>$REC);

	/* FIXME: Inefficienct hack
	 * Doing query extra time (beforehand) so if 0 rows returned,
	 * can redirect past search page.
	 */

	if ( count_rows(AG_MAIN_OBJECT_DB,$filter) == 0 ) {
		// No potential matches, redirect to next screen	
		$redirect=link_engine($add_link_array,'','','url_only');
		header('Location: ' . $redirect);
		page_close();
  		exit;
	} else {
    	$title = 'Review existing '.$mo_noun.'s for a match';
		$proceed_link = 'If the '.$mo_noun.' is not already registered, proceed here';
    	$out.=oline() . oline(red('Review the following '.$mo_noun.'s to make sure '.$mo_noun.' is not already registered'));
		$control=array('object'=>AG_MAIN_OBJECT_DB,
			   'action'=>'list',
			   'list'=>array('filter'=>$filter,
					     'order'=>array('donor_name'=>false)
					     ));
		$out.=call_engine($control,'control_client_reg',$NO_TITLE=true,$NO_MESSAGES=false,$TOT,$PERM);
		$out.=oline()
			. oline(bigger(link_engine($add_link_array,$proceed_link)));
		return $out;
	}
}

function donor_home_sidebar_left()
{
    /* You could customize the client-version sidebar for the home page here */
    return generic_home_sidebar_left();
/*
	return div(html_image($GLOBALS['AG_IMAGES']['AGENCY_LOGO_MEDIUM']),'',
		     ' style="background-color: #efefef; width: 10em; height: 17em; text-align: center;"');
*/
}

function assignments_f($staff_id, $my=false) {
	
	global $colors, $UID, $AG_USER_OPTION;

	$def = get_def('staff_assign');
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];

	//these settings are stored across sessions
	$hide = $AG_USER_OPTION->show_hide('assignments_f');
	$show_hide_link = $AG_USER_OPTION->link_show_hide('assignments_f');

	if (!$hide) {

		$res=staff_client_assignments($staff_id);
		$list=array();
		while ($tmp=sql_fetch_assoc($res)) {
			$tmp2=array();
			$tmp2['client_name']           = client_name($tmp[AG_MAIN_OBJECT_DB.'_id']);
			$tmp2[AG_MAIN_OBJECT_DB.'_id'] = $tmp[AG_MAIN_OBJECT_DB.'_id'];
			$tmp2['description']           = $tmp['description'];
			$tmp2['staff_assign_id']       = $tmp['staff_assign_id'];
			$tmp2['type_code']             = $tmp['staff_assign_type_code'];
			array_push($list,$tmp2);
		}
		asort($list);
		if (count($list)>0) {
			while ($a=array_shift($list)) {
				$client=$a[AG_MAIN_OBJECT_DB.'_id']; //can either be an ID or a name
				$type=$a['description'];
				$id=$a['staff_assign_id'];
				$assigns[$client][$type]=$id;
				$types[$type] = $a['type_code'];
			}
			$cnt = count($assigns);
			foreach ($assigns as $client => $assign) {
				$t_add_links = $tmp = array();
				
				foreach ($assign as $type=>$id) {
					$formatted = alt(link_engine(array('object'=>'staff_assign','action'=>'view','id'=>$id),black($type),'',' class="fancyLink"'),
							     'Click to view staff assignment');
					array_push($tmp,$formatted);
					$t_type = $types[$type];
					switch ($t_type) {
						//Add custom staff assign formatting here.
					default:
					}
				}
				$color = $color=='1' ? '2' : '1';
				
				$add_service_links = !be_null($t_add_links)
					? div(implode(oline(),array_values($t_add_links))
						,'',' class="staffServiceLinks"')
					: '';
				
				
				$out .= row(cell($add_service_links . 
						     div(alt(smaller(client_link($client,client_name($client,$my ? 25 : null),''
											   ,' class="fancyLink"'),2),'Click to view client record')),
						     ' style="padding: 0px 4px 0px 4px; white-space: nowrap;"'),'class="generalData'.$color.'"')
					. ($activity ? row(cell(alt(smaller($activity)))) : "")
					. row(cell(smaller(implode(', ',$tmp),3),
						     ' style="padding-left: 1.2em; white-space: nowrap;"'),'class="generalData'.$color.'"');		
			}
			
		} else {
			$out = row(cell('No staff assignments',' style="padding: 0px 4px 0px 4px; white-space: nowrap;"'),'class="generalData1"');
		}
	}

	$width = $hide ? ' boxHeaderEmpty' : '';
	$title=row(cell(($my
			     ? 'My ' . ucwords($mo_noun) . ' List ('.orr($cnt,'0').')'
			     : 'Assignments ('.orr($cnt,'0').') for ' . staff_link($staff_id)).$show_hide_link
			    ,' style="color: red; " class="staff boxHeader'.$width.'"'));
	$out = table($title . $out,null,' bgcolor="" cellspacing="0" cellpadding="0" style=" border: 1px solid black;"');

	return $out;
}

function staff_links()
{
	global $UID;

	return assignments_f($UID,true);
}

?>
