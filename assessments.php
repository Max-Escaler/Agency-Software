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

// functions for accessing assessments for shelter clients
function assessment_of( $client )
{
	if (is_array($client))
	{
		$client = $client["client_id"];
	}
	elseif (is_numeric($client))
	{
	}
	else
	{
		$client=sql_fetch_assoc($client);
		$client= $client["client_id"];
	}
// 	$ass = get_assessment( array("client_id"=>$client),"assessed_at DESC, added_at DESC LIMIT 1");
	$ass = get_generic( client_filter($client),'assessed_at DESC, added_at DESC','1','assessment');
	if (count($ass)==0)
	{
		return -1; //indicate no assessment
	}
	$ass=array_shift($ass);
	return $ass["total_rating"];
}	

function assessment_group_of( $client, $assessment='' )
{
	global $group_A_threshold_assessment_score;
	if (is_array($assessment) ) {
		$ass = $assessment['total_rating'];  //reduce number of queries (for bed_reg mainly)
	} else {
		$ass = assessment_of($client);
	}
	if ($ass == -1)
	{
		$ass_text = "none";
	}
	elseif (is_male($client))
	{
		$ass_text = ($ass >= $group_A_threshold_assessment_score) ? "Group A":"Group B";
	}
	else
	{
		$ass_text = "NA";
	}
	return $ass_text;
}

function assessment_f( $client, $format="normal" )
{
	if (is_array($client))
	{
		$cl_rec = $client;
		$client = $client["client_id"];
	}
	elseif (is_numeric($client))
	{
	}
	else
	{
		$client=sql_fetch_assoc($client);
		$client= $client["client_id"];
	}
// 	$ass_rec = get_assessment( array("client_id"=>$client),"assessed_at DESC, added_at DESC LIMIT 1");
	$ass_rec = get_generic( client_filter($client),'assessed_at DESC, added_at DESC','1','assessment');
	if (count($ass_rec)==0)
	{
		return smaller("No assessments");
	}
	else
	{
		$ass_rec=array_shift($ass_rec);
		$ass_date=$ass_rec["assessed_at"];
		$ass_old = days_interval($ass_date,today())>365;
		$ass_text = assessment_group_of( (is_array($cl_rec) ? $cl_rec : $client) ,$ass_rec);
	}
	if ($format=="tiny")
	{
	      return bold(blue(link_engine(array("object"=>"assessment","id"=>$ass_rec["assessment_id"]),$ass_text),2));
	}
	return bold(blue(link_engine(array("object"=>"assessment","id"=>$ass_rec["assessment_id"]),$ass_text),2)
		. ($ass_old ? red(" Needs New Assessment!!") : ""))
		. (($format=="normal") 
		   ? smaller(" by " . staff_link($ass_rec["assessed_by"]) . " " 
				 . dateof($ass_rec["assessed_at"] . " "),2)
		   : "");
}

?>
