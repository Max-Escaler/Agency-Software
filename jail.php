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

function jail_status_f($id)
{
	$def = get_def('jail');
	$res = get_generic(client_filter($id),'jail_date DESC','1',$def);
	if (count($res) < 1) {
		return false;
	}
	$rec = array_shift($res);
	if (be_null($rec['jail_date_end'])) {
		$days = $rec['days_in_jail'] . ($rec['days_in_jail'] > 1 ? ' days' : ' day');
		$text = 'Incarcerated since '.dateof($rec['jail_date']).' ('.$days.')';
	} elseif (days_interval($rec['jail_date_end'],'now',true) < 31 ) { //show up to 30 days after release
		$text = 'Released from jail on '.dateof($rec['jail_date_end']);
	} else {
		return false;
	}
	return oline(link_engine(array('object'=>'jail','id'=>$rec['jail_id']),
					 red($text)));

}

function upsert_jail_record( $rec, &$msg_return ) {
//outline(dump_array($rec));
	$date_range=new date_range( $rec['jail_date'],$rec['jail_date_end']);
	$ba_number=$rec['ba_number'];
	$rec_description='BA: ' . $ba_number . ', ' . $date_range->display();
	if ($rec['jail_date_end']) {
		$match_criteria=array(
			'OVERLAPSORNULL:jail_date,jail_date_end'=>$date_range
		);
	} else {
		$match_criteria=array(
			'NULL:jail_date_end'=>'dummy',
			'>=:jail_date'=>$rec['jail_date']
		);
	}
	if ($ba_number) {
		$match_criteria['ba_number']=$ba_number;
	}
	$filter=client_filter($rec['client_id']);
	$filter[]=$match_criteria;
	$match_recs=get_generic($filter,NULL,NULL,'jail');
	// No match?  Post and stop
	if (count($match_recs)==0) {
		$res = agency_query(sql_insert('tbl_jail',$rec,true));
		if ($res) {
			$msg[] = 'Successfully posted new jail record for ' . $rec_description;
		} else {
			$msg[] = red('failed to post new jail record for ' . $rec_description);
		}
	} elseif (count($match_recs)>1) {
		// Multiple overlaps?  Yikes
		$msg[]=red('Found multiple overlapping records for ' . $rec_description . '.  Giving up');
		$res =  false;
	} else {
		$mr=$match_recs[0];
//outline("FROM DB: " . dump_array($mr));
//outline("New rec" . dump_array($rec));

		if (
			(datetimeof($mr['jail_date'])==datetimeof($rec['jail_date']))
			and (
				(datetimeof($mr['jail_date_end'])==datetimeof($rec['jail_date_end']))
			)
//			and (datetimeof($mr['jail_date_end'])==datetimeof($rec['jail_date_end']))
			and ($mr['ba_number']==$rec['ba_number'])
		) {
			$msg[]='Record already exists.';
			$res=true;
		} elseif ($mr['ba_number'] and ($mr['ba_number'] !=$ba_number)) {
			$msg[]=red('BA Mismatch for ' .$rec_description.'. Giving up');
			$res=false;
		} elseif ( (datetimeof($mr['jail_date'])==datetimeof($rec['jail_date']))
			and (be_null($mr['jail_date_end']) and (!be_null($rec['jail_date_end'])) )
			) {
			if ($mr['comments'] and (!stristr($rec['comments'],$mr['comments']))) { $rec['comments']=sys_log_append($mr['comments'],$rec['comments']); }
			if ($mr['sys_log'] and (!stristr($rec['sys_log'],$mr['sys_log']))) { $rec['sys_log']=sys_log_append($mr['sys_log'],$rec['sys_log']); }
			$res = agency_query(sql_update('tbl_jail',$rec,array('jail_id'=>$mr['jail_id'])));
			if ($res) {
				if (sql_affected_rows($res)==1) {
					$msg[] = 'Successfully updated release for ' . $rec_description;
				} elseif (sql_affected_rows($res)==0) {
					$msg[] = 'Tried to update release for ' . $rec_description . ', but no record updated.';
					$res=false;
				} elseif (sql_affected_rows($res)>1) {
					$msg[] = 'Tried to update release for ' . $rec_description . '.  1 Record should have been updated, but instead ' . sql_affected_rows($res) . ' were actually updated.';
					$res=false;
				}
			} else {
				$msg[]='Failed to update release for ' . $rec_description . ' (query error)';
			}
		} else {
/*
		$jd_match=
			(dateof($mr['jail_date'])==dateof($rec['jail_date']))
			and (timeof($mr['jail_date'])==
*/
			$msg[]=red('JILS Update ability not yet fully implemented.  Can\'t process ' .$rec_description.'. Giving up');
			$res=false;
		}
	}
	$msg_return.=implode(oline(),$msg);
	return $res;
}	

?>
