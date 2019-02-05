<?php
/*
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
 *
 *  schema / print version
 *
 *
 */
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: text/css');

$version = $_REQUEST['inan']==='Y' ? 'inanimate' : 'staff';


switch ($version) {
 case 'staff':
?>
/* orangy blue? */
table.calendar { background-color: #83aeac; }
td.calendar, td.calendarHalf { background-color: #d5dede; border-bottom-color: #83aeac; border-right-color: #83aeac;}
a.calendar:hover, a.calendar:active, a.calendarHalf:hover, a.calendarHalf:active,
td.calendar:hover, td.calendar:active, td.calendarHalf:hover, td.calendarHalf:active
{ 
	color: #f00;
	background-color: #ffa15a;
	text-decoration: underline; }
td.calendarH, td.calendarT { background-color: #ffd27b; 
	border: 1px outset #dfb96c; }
td.calendarB { /* blocker cell */
	color: #fff;
	background-color: #6c716c; 
	border: 1px outset #3a3d3a;
}
td.calendarE { 
	color: #fff;
	background-color: #ffa15a; 
	border: 1px outset #e66832;
}
a.calendarE { color: #000; text-decoration: none; }
a.calendarE:hover, a.calendarE:active { color: #00f; text-decoration: underline; }
a.calendarConBox, a.navCalendar,div.navCalendar { color: #fff; background-color: #444; }
<?php
	  break;
	  case 'inanimate':
?>
/* bluish... */
table.calendar { background-color: #dedebd; }
td.calendar, td.calendarHalf { background-color: #eefaff; border-bottom-color: #dedebd; border-right-color: #dedebd;}
a.calendar:hover, a.calendar:active, a.calendarHalf:hover, a.calendarHalf:active,
td.calendar:hover, td.calendar:active, td.calendarHalf:hover, td.calendarHalf:active
{ 
	color: #f00;
	background-color: #9cb6d5;
	text-decoration: underline; }
td.calendarH, td.calendarT { background-color: #1786d5; 
	border: 1px outset #105e96; }
td.calendarB { /* blocker cell */
	color: #fff;
	background-color: #6c716c; 
	border: 1px outset #3a3d3a;
}
td.calendarE { 
	color: #fff;
	background-color: #9cb6d5; 
	border: 1px outset #7d92ab;
}
a.calendarE { color: #000; text-decoration: none; }
a.calendarE:hover, a.calendarE:active { color: #00f; text-decoration: underline; }
a.calendarConBox, a.navCalendar,div.navCalendar { color: #fff; background-color: #444; }
<?php
	  break;
	  case 'staff2':
?>
/* AGENCY staff alert theme */
table.calendar { background-color: #deffde; }
td.calendar { background-color: #f6f6e6; }
a.calendar:hover, a.calendar:active, td.calendar:hover, td.calendar:active {
        color: #f00;
        background-color: #ccccb6;
        text-decoration: underline; }
td.calendarH, td.calendarT { background-color: #acb7ac; }
td.calendarE { background-color: #ccccb6; }
a.calendarConBox, a.navCalendar,div.navCalendar { color: #444; background-color: #ccccb6; }
		  
<?php
}
?>
