#!/usr/bin/php -q
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



   //fixme: this script can be removed

log_error('This script is no longer needed. The logic has been implemented in import_king_county_hospital()');

exit;

$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$UID = $GLOBALS['sys_user'];

$db_table="tbl_hospital";
$db_start_field="hospital_date";
$db_end_field="hospital_date_end";
$facil_field="facility";
$vol_field="is_voluntary";

//$hospitals= array_unique(file('/dev/stdin')); -- this screws up the voluntary stuff - takes longer w/o but more reliable :(
$hospitals= file('/dev/stdin');
// echo count(array_unique($hospitals));
// exit;
$voluntary = false;
$old_vol = $cur_vol = 'INV';
while ($hospital = array_shift($hospitals))
{
	set_time_limit(30);
	$hospital=rtrim($hospital);
	if ($hospital)
	{
		$new_rec=array();
//		$rec = explode('\w',$hospital);
		preg_match('/^(INV|VOL)? *([0-9]+) *([0-9]{2}\/[0-9]{2}\/[0-9]{4}) (([0-9]{2}\/[0-9]{2}\/[0-9]{4})| {10}) (.*)$/',$hospital,$rec);
//  		out(dump_array($rec));
//		$old_vol = is_null($rec[1]) ? $cur_vol : $old_vol;
//		$cur_vol = orrn($rec[1],$old_vol);
//		$voluntary = $cur_vol==$old_vol ? $voluntary : !$voluntary;
		$cur_vol = be_null($rec[1]) ? $old_vol : $rec[1];
		$voluntary = $cur_vol == 'VOL';
		$old_vol = $cur_vol;
// 		out( $rec[1].'  '.($voluntary ? 'v ' : 'i ') . $rec[2]. '|  '. ($cur_vol=='VOL' ? 'v ':'i '). ($old_vol=='VOL' ? 'v ':'i '));
//    		continue;

		$case_id = $rec[2];
		$date_in = $rec[3];
		$date_out = $rec[5];
		$facility = $rec[6];

		//find client_id
		$client = get_generic(array('clinical_id'=>$case_id),'','','client');
		if (count($client) !== 1) {
			$error = 'There is no corresponding client_id for case_id '.$case_id.' in tbl_client';
			$page_errors .= $error."\n";
			log_error($error);
			continue;
		}
		$client = array_shift($client);
		$client_id = $client['client_id'];
		$filter = array("client_id"=>$client_id, $db_start_field=>$date_in);
		$existing = get_generic($filter,'','','hospital');
		// already in DB?
		// 0 Rows = no
		// 1 Rows = yes, check whether end date is missing (and gets added)
		//          or if it exists, in which case it should match
		// 2 Rows = problem!
		if (count($existing)>1)
		{
			log_error("Found more than one record for Case ID $client_id on $date_in.");
			continue;
		}
		elseif (count($existing)==1)
		{
			$existing = array_shift($existing);
			if ($existing[$db_end_field]) // release record already exists
			{
				if (!$date_out)
				{
				// admit record, release already posted
				}
				elseif (dateof($date_out)<>dateof($existing[$db_end_field]))
				{
					log_error("Hospital Import: Conflicting release dates for Case ID $client_id on $date_in\n"
						    ."Existing date: ".dateof($existing[$db_end_field])."\n"
						    ."Auto-import date: ".dateof($date_out));
					continue;
				}
			}
			// no errors, post release if not already
			elseif ($date_out)
			{
				// update, set release date
				$new_rec[$db_end_field]=dateof($date_out,"SQL");
				$new_rec['hospital_date_end_source_code']='KC';
				$new_rec['hospital_date_end_accuracy']='E';
				$new_rec['changed_by']=$GLOBALS['sys_user'];
				$new_rec['changed_at']=dateof('now','SQL');
				$result = sql_query(sql_update($db_table,$new_rec,array("client_id"=>$client_id,$db_start_field=>$date_in)));
//  				echo("Updating record, case id $client_id\n");
			}
		}
		else // record doesn't yet exist in DB
		{
			// post record
			$new_rec=array("client_id"=>$client_id,
					   $db_start_field=>dateof($date_in,"SQL"),
					   $db_end_field=>dateof($date_out,"SQL"),
					   $vol_field=>$voluntary ? sql_true() : sql_false(),
					   $facil_field=>$facility,
					   'added_by'=>$GLOBALS['sys_user'],
					   'changed_by'=>$GLOBALS['sys_user']);
			if (!be_null($new_rec[$db_end_field])) {
				$new_rec['hospital_date_end_source_code'] = 'KC';
				$new_rec['hospital_date_end_accuracy']='E';
			}
			$result = sql_query(sql_insert($db_table,$new_rec));
//  			echo("Posting record, case id $client_id\n");
		}
	}
}
			
if ($page_errors) {
	global $mail_errors_to;
	mail((is_array($mail_errors_to) ? implode(',',$mail_errors_to) : $mail_errors_to).',cedsinger@desc.org,vwhissiel@desc.org',
	     'KC HOSPITAL REPORT IMPORT ERROR',$page_errors);
}
page_close($silent=true);

?>
