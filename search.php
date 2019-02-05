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

$quiet = 'Y';
include 'includes.php';
$qs_type = strip_tags($_REQUEST['QSType']);
$qs_text = strip_tags($_REQUEST['QuickSearch']);

$tmp_searches = array_keys($AG_QUICK_SEARCHES);
array_push($tmp_searches,'news');

if (!in_array($qs_type,$tmp_searches)) {
	agency_top_header();
	out(alert_mark(webify('Unknown Quick Search Type: '.$qs_type)));
	page_close();
	exit;
}

switch ($qs_type) {
	//custom type handling here
 default :
	$func=$qs_type.'_search';
	if (function_exists($func)) {
		$out = $func();
	} else {
		$out = object_quick_search($qs_type,$qs_text);
	}
}

agency_top_header();
out(html_heading_1('Search Results') .$out);
page_close();

?>
