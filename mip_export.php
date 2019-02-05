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
 * Script for generating export files for import into MIP
 */


$quiet = true;
require_once 'includes.php';
require_once 'mip.php';

$action = $_REQUEST['action'];
switch ($action) {

 case 'dl':

	 /*
	  * User requesting download of existing file
	  */

	 mip_pass_through_download($_REQUEST['id']);
	 break;

 case 'cc_export':

	 /*
	  * Generate export of CC gifts
	  */

	 $export_type = 'gift_cc';
	 $stitle = 'Credit Card Gifts';
	 $gift_filter = $AG_MIP_GIFT_EXPORT_FILTER_CC;
	 break;

 case 'cash_export':
 default:

	 /*
	  * Generate export of gift records
	  */

	 $export_type = 'gift';
	 $stitle = 'All Gifts Excluding Credit Cards';
	 $gift_filter = $AG_MIP_GIFT_EXPORT_FILTER;
}

sql_begin();

$gdef = get_def('gift_cash');
$ddef = get_def('donor');

$title = 'MIP Gift and Donor Export ('.$stitle.')';

agency_top_header();

/*
 * 0) find gifts
 */

$res = get_generic($gift_filter,'','',$gdef['table']);

$num = $fname = $export = array();

if (count($res) < 1) {
	outline(bold('No gifts to export.'));
 } else {

	/*
	 * break out exports by year
	 */
	$archive_directory = AG_MIP_EXPORT_DIRECTORY.'/archive/'.year_of('now');
	if (!is_dir($archive_directory)) {

		mkdir($archive_directory);

	}

	/*
	 * 0.5) export any donors that haven't been exported before
	 */
	$dres = get_generic(array(),'','','export_donor_mip');
	$dcount = count($dres);

	if ($dcount > 0) {

		/*
		 * 0.5.0) create session id and add record to export table
		 */
		$d_session_id = call_sql_function('set_mip_export_session_id',enquote1('donor'));

		$res = agency_query(sql_insert('tbl_mip_export_session',array('mip_export_session_id'=>$d_session_id,
												'added_by'=>$UID,'changed_by'=>$UID)));

		/*
		 * 0.5.1) update donors with session id
		 */
		$res = agency_query(sql_update($ddef['table_post'],
						     array('mip_export_session_id' => $d_session_id,
							     'changed_by'            => $UID,
							     'FIELD:changed_at'      => 'CURRENT_TIMESTAMP',
							     'FIELD:sys_log'         => 'COALESCE(sys_log||\'\n\',\'\')||\'Exporting donor for MIP - \'||CURRENT_TIMESTAMP'),
						     array('FIELDIN:donor_id'=>'(SELECT customer_donor_id FROM export_donor_mip)','NULL:mip_export_session_id'=>'','is_deleted'=>sql_false())));
		
		/*
		 * 0.5.2) generate export file
		 */
		$export['donor'] = sql_data_export("SELECT * FROM export_donor_mip_all WHERE session_id='{$d_session_id}'",',','',$quotes=true);

		$fname['donor'] = $archive_directory.'/mip_donor_export.'.$d_session_id.'.csv';
		$fname_holding['donor'] = AG_MIP_EXPORT_DIRECTORY.'/mip_donor_export.'.$d_session_id.'.csv';

		outline('Exporting '.$dcount.' donors using MIP session ID: '.$d_session_id);

		/*
		 * 0.5.3) get donors back
		 */
		$res = get_generic(array('mip_export_session_id' => $d_session_id),'','','donor');

		$num['donor'] = count($res);
		outline('Updated '.$num['donor'].' donor records');

	}

	/*
	 * 1) create session id
	 */
	$session_id = call_sql_function('set_mip_export_session_id',enquote1($export_type));

	$res = agency_query(sql_insert('tbl_mip_export_session',array('mip_export_session_id'=>$session_id,
											'added_by'=>$UID,'changed_by'=>$UID)));
	
	outline('Using MIP session id: '.$session_id);
	
	/*
	 * 2) update gifts w/o session id
	 */
	$res = agency_query(sql_update($gdef['table_post'],
					     array('mip_export_session_id' => $session_id,
						     'changed_by'            => $UID,
						     'FIELD:changed_at'      => 'CURRENT_TIMESTAMP',
						     'FIELD:sys_log'         => 'COALESCE(sys_log||\'\n\',\'\')||\'Exporting gift for MIP - \'||CURRENT_TIMESTAMP'),
					     $gift_filter)
					     );
	
	/*
	 * 3) get gifts back -- fixme, this step could be eleminated by using the RETURNING capability of Postgresql/agency_query() function
	 */
	
	$res = get_generic(array('mip_export_session_id' => $session_id),'','',$gdef['table']);

	$num['gift'] = count($res);
	
	outline('Updated '.$num['gift'].' '.$gdef['singular'].' records');
	
	/*
	 * 4) generate export file
	 */
	
	$ex_def = get_def('export_gift_mip');
	$export['gift'] = sql_data_export('SELECT * FROM '.$ex_def['table'].' WHERE session_id = \''.$session_id.'\'',',','',$quotes=true);
	
	/*
	 * 5) write files
	 */

	$fname['gift']         = $archive_directory.'/mip_'.$export_type.'_export.'.$session_id.'.csv';
	$fname_holding['gift'] = AG_MIP_EXPORT_DIRECTORY.'/mip_'.$export_type.'_export.'.$session_id.'.csv';

	foreach (array('donor','gift') as $o) {

		if (isset($export[$o])) {

			if (file_exists($fname[$o])) {

				$error = true;
				outline(alert_mark('File '.$fname[$o].' already exists! This is a serious problem.'));

			} elseif (!file_put_contents($fname[$o],$export[$o])) {

				$error = true;
				outline(alert_mark('Couldn\'t write '.$fname[$o]));

			} else {

				chmod($fname[$o],0440);

				/*
				 * also write to holding directory
				 */
				file_put_contents($fname_holding[$o],$export[$o]);
				chmod($fname_holding[$o],0440);

				outline('Succesfully wrote '.bold($num[$o]).' '.$o.'s to '.bold($fname[$o]));
			}

		}

	}
	
	if ($error) {
		sql_abort();
	} else {
		sql_end();
	}

}

outline(mip_link_export(),2);
out(mip_import_files_list());

out(hlink('menu.php#mip','Back to AGENCY MIP Menu'));

page_close();

?>
