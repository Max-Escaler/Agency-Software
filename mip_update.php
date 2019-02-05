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
   * Script for confirming an export file has been imported into MIP
   */

$quiet = true;
require_once 'includes.php';
require_once 'mip.php';

$type       = $_REQUEST['t'];
$step       = $_REQUEST['step'];
$session_id = $_REQUEST['session_id'];
$use_date   = dateof($_REQUEST['gift_cash_date'],'SQL');

$password_ok = $AG_AUTH->reconfirm_password();

/* 
 * 1) find donors/gifts needing deposit date
 */

switch ($type) {

 case 'gift_cc':
 case 'gift' :

	 /*
	  * CX000000FDG00 is the ID assigned to all gifts that existed prior to the AGENCY-->MIP data flow
	  */
	 $res = agency_query('SELECT mip_export_session_id,count(*) FROM gift_cash WHERE mip_export_session_id IS NOT NULL AND mip_export_session_id != \'CX000000FDG00\' AND gift_cash_date IS NULL GROUP BY 1 ORDER BY 1');
	 $title = 'Batch-update deposit date';
	 break;

 case 'donor' :

	 $res = agency_query('SELECT mip_export_session_id,count(*) FROM donor LEFT JOIN mip_export_session USING (mip_export_session_id) WHERE donor.mip_export_session_id IS NOT NULL AND mip_import_confirmed_by IS NULL GROUP BY 1 ORDER BY 1');
	 $title = 'Confirm donor import into MIP';
	 break;

 default :
	 agency_top_header();
	 out(alert_mark('Error: Unknown type ('.$type.') passed to '.__FILE__));
	 page_close();
	 exit;
}




if (sql_num_rows($res)>0 || $step) {

	if ($step=='confirmed') {

		if ($password_ok) {
			sql_begin();

			/*
			 * verify that archive exists and remove holding file
			 */
			$filebase = mip_filename_from_id($session_id);
			$filehold = AG_MIP_EXPORT_DIRECTORY.'/'.$filebase;
			$filearch = AG_MIP_EXPORT_DIRECTORY.'/archive/'.mip_year_from_id($session_id).'/'.$filebase;

			if (is_readable($filearch)) {

				if (unlink($filehold)) {
					$out .= oline('Removed holding file '.$filehold);
				} else {
					$out .= alert_mark('Failed to remove holding file '.$filehold);
				}

			} else {

				$out .= alert_mark('Couldn\'t find archive file '.$filearch.', so holding file '.$filehold.' was not removed.');

			}

			/*
			 * update export session
			 */
			$resu = agency_query(sql_update('tbl_mip_export_session',array('mip_import_confirmed_by'=>$UID,
													 'FIELD:mip_import_confirmed_at'=>'CURRENT_TIMESTAMP',
													 'changed_by'=>$UID,
													 'FIELD:changed_at'=>'CURRENT_TIMESTAMP'),
								array('mip_export_session_id'=>$session_id)));

			if (!$resu) {

				$out .= alert_mark('Failed to update mip_export_session');
				sql_abort();

			} else {
				switch ($type) {
				case 'gift_cc':
				case 'gift' :

					/*
					 * update gifts with the deposit date
					 */
					$res = agency_query(sql_update('tbl_gift_cash',array('gift_cash_date'=>$use_date,
													   'changed_by'=>$UID,
													   'FIELD:changed_at'=>'CURRENT_TIMESTAMP',
													   'FIELD:sys_log'=>'COALESCE(sys_log||\'\n\',\'\')||\'Batch-updating deposit_date for MIP session '.$session_id.'\''),
									     array('mip_export_session_id'=>$session_id,
										     'NULL:gift_cash_date'=>'',
										     'is_deleted'=>sql_false())));
					if (!$res) {

						$out .= alert_mark('Failed to update records');
						sql_abort();

					} else {

						/*
						 * Get records back and display
						 */
						$eng = call_engine(array('object'=>'gift_cash',
										 'action'=>'list',
										 'list'=>array('filter'=>array('mip_export_session_id'=>$session_id,
															 'FIELD:changed_at'=>'CURRENT_TIMESTAMP::timestamp(0)',
															 'gift_cash_date'=>$use_date))),'',true,true,$tot,$perm);
						$out .= oline('Updated '.bold($tot).' records:',6).$eng;
						
						sql_end();

						$step = '';
						$res = agency_query('SELECT mip_export_session_id,count(*) FROM gift_cash WHERE mip_export_session_id IS NOT NULL AND mip_export_session_id != \'CX000000FDG00\' AND gift_cash_date IS NULL GROUP BY 1 ORDER BY 1');
						
					}
					break;
				case 'donor' :

					/*
					 * The individual records don't need to be updated
					 */
					$out .= 'Donor import to MIP confirmed.';
					$step = '';
					$res = agency_query('SELECT mip_export_session_id,count(*) FROM donor LEFT JOIN mip_export_session USING (mip_export_session_id) WHERE donor.mip_export_session_id IS NOT NULL AND mip_import_confirmed_by IS NULL GROUP BY 1 ORDER BY 1');
					sql_end();
					break;

				}

			}

		} else {

			$message .= 'Incorrect password for '.staff_name($UID);
			$step = 'confirm';

		}

	}
 
	if ($step == 'confirm' && $use_date && $session_id) {

		$out .= oline('You have requested to set the deposit date to '.bold(dateof($use_date)).' for all gifts with '.bold($session_id));
		$out .= formto($_SERVER['PHP_SELF'].'?t='.$type,'',$AG_AUTH->get_onsubmit(''))
			. red('Enter password for '.staff_link($GLOBALS['UID']).' to confirm '.$action.' ')
			. $AG_AUTH->get_password_field($auto_focus=true)
			.button('Confirm','','','','','class="engineButton"'). hlink('','Cancel','','class="linkButton"')
			.hiddenvar('step','confirmed')
			.hiddenvar('session_id',$session_id)
			.hiddenvar('gift_cash_date',$use_date)
			.formend();

	}

	if (!$step || !$use_date || !$session_id) {

		$pics = selectitem('','(choose one)');
		$def_id = $_REQUEST['id'];

		while ($a = sql_fetch_assoc($res)) {

			$pics .= selectitem($a['mip_export_session_id'],$a['mip_export_session_id']
						  . ' ('.$a['count'].str_plural(' record',$a['count']).' to update)',$def_id==$a['mip_export_session_id']);

		}

		$out .= formto($_SERVER['PHP_SELF'].'?t='.$type)
			. table(
				  rowrlcell('Choose a batch to update: ',selectto('session_id')
						. $pics
						. selectend())
				  . rowrlcell('Use this date: ',formdate('gift_cash_date')))
			. button()
			. hiddenvar('step','confirm')
			. formend();
	}

 } else {

	$out .= alert_mark('No gifts to update');
	
}

agency_top_header();

out(html_heading_1($title));

out(div($message,'','class="error"')
    . $out);

out(hlink('menu.php#mip','Back to AGENCY MIP Menu'));

page_close();

?>
