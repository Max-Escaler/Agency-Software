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

$quiet="Y";
include "includes.php";
include "openoffice.php";
include "zipclass.php";
$sdate=$_REQUEST["sdate"];
$edate=$_REQUEST["edate"];
$template = 'rpt_dropin.sxw';

$out .= 
		formto()
		. oline("Enter Start Date" . formdate("sdate",$sdate))
		. oline("Enter End Date" . formdate("edate",$edate))
		. button()
		. formend();

if ($sdate || $edate)
{
	if (! ($sdate && $edate))
	{
		$out.=alert_mark("Both Start & End Date Required");
	}
	else
	{
		$range = new date_range($sdate,$edate);
		$sql="SELECT case_id,
				 name_full AS client_name,
				 dal_date,
				 description,
				 tot_min,
				 cmgrfirst || ' ' || cmgrlast AS cmgr_name
			  FROM clin_dal
			  LEFT JOIN clin_l_dal USING (dal_code)
			  LEFT JOIN clin_client USING (case_id)
			  LEFT JOIN clin_staff ON (curr_cm_id=cmgrid)
			  LEFT JOIN client ON (case_id=clinical_id)";
		$filter["BETWEEN:dal_date"]=$range;
		$filter["dal_code"]=618;
		$order = "cmgr_name,client_name,dal_date";
//$query_display="Y";
		$data=sql_query("SET DATESTYLE TO SQL"); // don't care about result
		$data=agency_query($sql,$filter,$order);
		$file=oowriter_merge($data,$template,"","","client_name");
		serve_office_doc($file,$template); //exits
	}
}
$title= "Dal Drop-in Report";
$out = oline(bigger(bold($title))) . $out;
agency_top_header();
out($out);
page_close();
?>
