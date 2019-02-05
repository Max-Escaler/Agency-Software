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
require 'includes.php';
require_once 'agency_admin.php';

$action = $_REQUEST['action'];

/*
 * Removing menu(s) should be done primarily through the $AG_MENU_TYPES
 * array (see {object}_config.php). Removing code further below isn't necessary once 
 * a menu has been commented out or removed from this array.
 *
 * Conversely, if a menu is added, a function of the form agency_menu_.$type must 
 * be created. This function should return an array containing, by section, content
 * and any applicable errors:
 *
 * array($menu,$errors);
 *
 * where $menu is an array of the form
 * 
 * array('sec_title_1'=>content,
 *       'sec_title_2'=>content,
 *        ...);
 *
 * WARNING: Since all menus are potentially loaded on a single page, when defining
 *          an action, it is best to prefix it with the menu type. Thus, an action 
 *          on the housing menu section would be housing{action}, etc.
 *
 */

$title = 'AGENCY Menu';

$main_menu = array();
$out = '';

foreach ($AG_MENU_TYPES as $t => $d) {

	$error = false;
	//generate main menu
	$text = orr($d['link_text'],$d['title']);
	$link = !has_perm(orr($d['perm'],'any'))
		? dead_link($text)
		: hlink($_SERVER['PHP_SELF'].'#'.$t,$text);
	$main_menu[] = $link;

	$menu = array();

	if (! has_perm(orr($AG_MENU_TYPES[$t]['perm'],'any'))) {

		// check permissions
		continue;

	} else {

		$func = 'agency_menu_'.$t;
		if (function_exists($func)) {
			list($menu,$error) = $func();
		}

	}


	$out .= html_heading_2(anchor($t).$AG_MENU_TYPES[$t]['title']);

	if ($error) {
		$out .= div($error,'',' class="error"');
	}

	foreach ($menu as $label => $content) {	
		$out .= html_fieldset($label,$content);
	}
	$out .= hlink($_SERVER['PHP_SELF'].'#','back to top');
}

agency_top_header();


// Navigate to menu sections at top and bottom
	$main_menu=implode(bold(' | '),$main_menu);
	$menu_top=div(html_heading_1($title).$main_menu, '',' class="mainMenuHeader"');
	$menu_bottom=div($main_menu, '',' class="mainMenuHeader"');

out(
	$menu_top
	. div($out)
	. $menu_bottom
);

page_close();

?>
