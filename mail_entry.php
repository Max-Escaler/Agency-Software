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

$title='Mail Entry';
$quiet="Y";
include "includes.php";
include "mail.php";
include "openoffice.php";
include "zipclass.php";

if (!has_perm('mail_entry,admin')) {
	agency_top_header();
	outline(red('No Mail Entry Permissions'));
	page_close();
	exit;
}

$tmp_request_vars = array('action',
				  'mailclients',
				  'mailcomments',
				  'maildate',
				  'mailtype',
				  'QuickSearch');
foreach ($tmp_request_vars as $tmp) {
	$$tmp = $_REQUEST[$tmp];
}

// Get recent mail counts (bug 17620)
$count=sql_query('SELECT mail_date,count(*) FROM mail WHERE mail_date >= current_date -7 GROUP BY 1 ORDER BY 1 DESC LIMIT 4');
while ($c=sql_fetch_assoc($count))
{
	$cnt_tab .= row(cell(smaller(dateof($c['mail_date']))) . cell(smaller($c['count'])));
}
$cnt_tab = table(header_row('Date','Count') . $cnt_tab);

// show the search form
	$out	 .= html_no_print(oline(bigger(bold("Mail Entry Screen")))
			  .  table(row(cell(oline(formtextarea("QuickSearch","","",40,5)) 
						  . "Default mail type: " . mail_codes_to("mail_type_def",$_REQUEST["mail_type_def"])
						  . button(($QuickSearch ? "Post and " : "" ) . "Search Now")) 
					   . cell(oline(hlink($_SERVER['PHP_SELF']."?action=showlist","Show Mail List"))
						    . oline(hlink($_SERVER['PHP_SELF']."?action=showlist_signout","Show Mail List (signout)"))
						    . oline(hlink($_SERVER['PHP_SELF']."?action=showlist_posting","Show Mail List (posting)"))
						    . oline(hlink($_SERVER['PHP_SELF'].'?action=showlist_today','Show Today\'s Mail List'))
						    . oline(formcheck("show_photos",$_REQUEST["show_photos"]) . " Show Photos")
						    . oline("Default mail date: " 
								. formvartext("mail_date_def",orr($_REQUEST["mail_date_def"],dateof("now")))), 
						    "valign=top").cell(smaller($cnt_tab)))));

	// This section posts the selected mail records:
	$selected = $_REQUEST[$select_name];
	($DEBUG > 2) && $out .= "Select name = $select_name.  Selected = " . dump_array($selected);

if (is_array($selected))
{
	$types = $_REQUEST[$types_name];
	$dates = $_REQUEST[$dates_name];
	$clients = $_REQUEST[$clients_name];
	$comments = $_REQUEST[$comments_name];
	foreach ($selected as $key => $value)
	{
		$mail=array();
		$mail["client_id"]=dewebify($clients[$key]);
		$mail["mail_date"]=dewebify($dates[$key]);
		$mail["mail_type_code"]=dewebify($types[$key]);
		$mail["comment"]=dewebify($comments[$key]);
		$mail["added_by"]=$UID;
		$mail["changed_by"]=$UID;
		$DEBUG && $out .= oline("Here is my mail record: " . dump_array($mail));
		agency_query(sql_insert("tbl_$mail_table",$mail));
	}
	//adding this re-direct to prevent duplicates via back-button/reload browsing
	header('Location: mail_entry.php?QuickSearch='.preg_replace('/\n/',';',$QuickSearch)
		 .'&mail_date_def='.$_REQUEST['mail_date_def'].'&mail_type_def='.$_REQUEST['mail_type_def']);
	exit;
}

// This shows the search results
if (isset($QuickSearch))
{
	$out .= table(object_search('mail',dewebify($QuickSearch)));
}
//$_SESSION["action"]=orr($_REQUEST["action"],$_SESSION["action"]);
//$action = $_SESSION["action"];
if (substr($action,0,8)=="showlist")
{
	preg_match('/showlist_(.*)/',$action,$matches);
	$out .= show_mail_list($matches[1]);
}
elseif ( in_array($action,array("DELIVERED","RETURNED","RETURNALL")) && (dateof(orr($_REQUEST["pickup"],$_REQUEST["cutoff"]))))
{
	$out .= mark_mail_status($_REQUEST["pickup"],$action);
}

// this does the actual output

global $agency_search_url;

agency_top_header($commands);
out( formto() . $out . formend());
page_close();
?>
