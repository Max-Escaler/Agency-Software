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

function cell2_title( $string,$offset=0 )
{
		$string=strtoupper($string);
		return cell(bold(color(substr($string,0,$offset),'#40ff40'))
			. bigger(bold(substr($string,$offset,1)),2)
			. color(substr($string,$offset+1),'#ffa0a0') );

}

global $colors, $AG_TEXT;

$quiet="Y";
include "includes.php"; 
$commands=array();

// Home page top commands box
$news_control = array('object'=>'news','action'=>'list');
$links_func='agency_home_links';
$links=span($links_func(),'class="homeLinks"');
$links_sidebar_func=AG_MAIN_OBJECT_DB.'_home_sidebar_left';
$news=div(link_engine($news_control,'What\'s<br />New?','','class="fancyLink"'),'','class="newsLink"');
$title=span($AG_TEXT['AGENCY_HOME_TITLE'],'class="homePageTitle"');
$org_info=span('running at ' . span(link_organization_home(org_name()),'class="homePageOrgName"'),'class="homePageOrgInfo"');
$title=para($title.oline().$org_info);
//$spacer = oline('',3);
array_push($commands,
	     //bottomcell(html_heading_1(
	     cell(div($title . $news . $links . $spacer,'','style="position: relative;" '),'id="homeMenu" class="homeMenu"'));

$def=get_def(AG_MAIN_OBJECT_DB);
$all_clients = hlink('display.php?control[action]=list&control[object]=' . AG_MAIN_OBJECT_DB . '&control[id]=list','Show all ' . $def['plural']);

// Show a "My Clients" box
$my_links =
	oline($all_clients)
	. oline()
	. my_staff_links();
	
$table = show_alerts($UID,25);

$out =tablestart('',' class="" bgcolor="" width="100%" cellspacing="0"')
	. row(topcell($links_sidebar_func())
	     . topcell($table,"rowspan=\"9\" bgcolor=\"{$colors['blank']}\" align=\"center\"")
	     . topcell($my_links,' align="right" rowspan="9" bgcolor="'.$colors['blank'].'"')
	     )
     . tableend();
     
agency_top_header($commands);
out($out);
page_close();
html_footer();
?>
