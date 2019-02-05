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

require 'includes.php';

out(html_heading_1(center('Unit History Lookup')));

if (! has_perm("housing","R"))
{
	outline(bigger(bold("Sorry, you do not have permission to access this page")));
	exit;
}

$unitslist=$_REQUEST['unitslist'];
$start=$_REQUEST['start'];
$end=$_REQUEST['end'];
$showsumm=$_REQUEST['showsumm'];
$showfull=$_REQUEST['showfull'];

/*
// You can use custom subcategories, and define the matching filter
// in the switch below
$subcategories = array( 'KSH_SH'    => 'Kerner-Scott House (Safe Haven)' );
*/

foreach ($subcategories as $val => $label) {
	$sub_select .= selectitem($val,$label,$unitslist == $val);
}

out(
	formto($_SERVER['PHP_SELF'])
	. "Date Range: "
	. formvartext("start",$start)
	. formvartext("end",$end)
	. selectto("unitslist")
	. selectitem("","All Units")
	. get_project_housing_pick($unitslist)
	. $sub_select
	. do_pick_sql("SELECT housing_unit_code AS Value, housing_unit_code as Label from $units_table ORDER BY housing_unit_code",$unitslist)
	. selectend() . "<br>"
	. formcheck("showsumm",$showsumm) . " Show Unit Summaries<br>"
	. formcheck("showfull",$showfull) . " Show Unit Details<br>"
	. button("Show!")
	. formend());

if ($unitslist || $start || $end) {

	if ($start) {
		$uh_daterange=new date_range($start,$end);
	}
	if (! preg_match('/[A-Z][0-9]{3}/',$unitslist)) { //for project

		switch ($unitslist) {
			//hard-coding custom sub-categories
/*		case 'KSH_SH' :
			$filter = array('~:housing_unit_code' => '^K[2-3]');
			break;
*/
		default :
			$filter = array("LIKE:housing_project_code"=>"$unitslist%");
		}

		if (dateof($start)) {
			$filter['<=:housing_unit_date'] = orr(dateof($end,'SQL'),dateof('now','SQL'));
			$filter[] = array('>=:housing_unit_date_end'=> dateof($start,'SQL'),
						'NULL:housing_unit_date_end'=>true);
		}

		$units=get_units($filter);
		while ($ur=array_shift($units))
		{
			$unit=unit_history($ur['housing_unit_code'],$uh_daterange);
			if ($showfull) { $res .= oline($unit["Formatted"]); }
			if ($showsumm) { $res .= oline( $unit["Summary"]); }
			if ($showsumm || $showfull) { $res.= hrule(); }
			$tot_occupied += $unit["Occupied"];
			$tot_vacant += $unit["Vacant"];
		}
	}
	else
	{
		$unit=unit_history($unitslist,$uh_daterange);
		if ($showfull) { $res.= oline($unit["Formatted"]); }
		if ($showsumm) { $res.=	oline( $unit["Summary"]); }
	}
	if ($tot_occupied+$tot_vacant > 0)
	{
		outline(bigger(bold("Showing unit(s) for " . orr($subcategories[$unitslist],$unitslist,"All"))));
//		if ($uh_daterange)
//		{
			outline($uh_daterange ?
			(bold(bigger("For Time Period: " . $uh_daterange->display()) . " ("
				. $uh_daterange->days() . " days)" ))
				: bold(bigger("For displayed units: ")));
			outline(bigger(bold("Grand Total Occupied Days: " . $tot_occupied)));
			outline(bigger(bold("Grand Total Vacant Days: " . red($tot_vacant))));
			outline(bigger(bold("Grand Total Unit Days: " . ($tot_vacant+$tot_occupied) )));
			outline(bigger(bold("Grand Total Occupancy Rate: " . 
			round(($tot_occupied/($tot_occupied+$tot_vacant))*100,2) . "%." )));
			outline(bigger(bold("Grand Total Vacancy Rate: " . 
			red(round(($tot_vacant/($tot_occupied+$tot_vacant))*100,2) . "%." ))));
			outline(bigger(bold("Combined Total Occupancy & Vacancy Rate: " . 
			round((($tot_vacant+$tot_occupied)/($tot_occupied+$tot_vacant))*100,2) . "%." )));
//		}
		out(hrule());
	}
	out($res);
}

page_close();
?>
