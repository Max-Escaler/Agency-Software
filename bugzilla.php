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
 * These functions are for supporting integration with Bugzilla.
 *
 */

function get_bugs( $filter, $order="", $limit="" )
{
	global $bugzilla_select_sql, $WHICH_DB, $bugzilla_bug_table, $db_server;

	if ($bugzilla_select_sql && $db_server['my']) {
		if ($WHICH_DB != 'my') {
			$OLD_DB = $WHICH_DB;
			$WHICH_DB = 'my';
		}
		$a = db_connect($bugzilla_bug_table,"my");
		if (!$a) {
			log_error("Failed to Connect to bugzilla db");
			return false;
		}
		$bugs = agency_query($bugzilla_select_sql,$filter);
		db_close($a);
		$WHICH_DB = orr($OLD_DB,$WHICH_DB);
		return $bugs;
	}
	return false;
}

function get_bug( $id )
{
// get a bug, and annotate it
// with additional array elements (is_valid,is_open,is_closed).

	global $bugzilla_select_sql;

	if ($bugzilla_select_sql) {
		$open_status=array( 'UNCONFIRMED','NEW','ASSIGNED','REOPENED');
		$closed_status=array('RESOLVED','VERIFIED','CLOSED');
		$bug=get_bugs(array("bug_id"=>$id));
		if (sql_num_rows($bug,"my")<>1)
		{
			return array("is_valid"=>sql_false());
		}
		$bug=sql_fetch_assoc($bug,NULL,"my");
		$bug["is_valid"]=sql_true();
		$bug["is_open"]=sql_true(in_array($bug["bug_status"],$open_status));
		$bug["is_closed"]=sql_true(in_array($bug["bug_status"],$closed_status));
		return $bug;
	}
}
		
function bug_link( $id, $text="" )
{
	global $show_bugzilla_url,$bugzilla_select_sql;
	$text=orr($text,"bug $id");
	if (!$show_bugzilla_url) {
		// nothing to link to, return plain text
		return $text;
	}
	if ($bugzilla_select_sql) {
		// get bug info, if query to DB enabled
		$bug=get_bug($id);
		$sum=strtoupper($bug['bug_status'] . ' ' . $bug['resolution'])
		. ' - ' .$bug['short_desc'];
	}
	if (sql_false($bug["is_valid"]))
	{
		return gray($text);
	}
	$bug_link=hlink($show_bugzilla_url . $id,$text,'','title="'.$sum.'"');
	if (sql_true($bug["is_closed"]))
	{
			return strike($bug_link);
	}
	else
	{
		return $bug_link;
	}
}

function link_bugzilla( $label = '')
{
	    global $bugzilla_url;
	    if ($bugzilla_url) {
		    $label=orr($label,$GLOBALS['AG_TEXT']['LINK_BUGZILLA'] );
		    return hlink( $bugzilla_url, $label );
	    }
	    return false;
}

function link_file_bug($label = '')
{
	    global $bugzilla_url;
	    if ($bugzilla_url) {
		    $label=orr($label,$GLOBALS['AG_TEXT']['LINK_REPORT_BUG'] );
		    return hlink( "$bugzilla_url/enter_bug.cgi?format=is&product=Agency", $label );
	    }
	    return false;
}
				
?>
