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
   * Functions for interfacing AGENCY data with MIP
   *
   */

define('AG_MIP_EXPORT_DIRECTORY','/shared/agency_export/mip');

global $AG_MIP_GIFT_CASH_EXCLUDE, $AG_MIP_GIFT_CASH_CC_CODES,
	$AG_MIP_GIFT_EXPORT_FILTER, $AG_MIP_GIFT_EXPORT_FILTER_CC;
	
/*
 * Gifts with these codes won't be exported
 */

$AG_MIP_GIFT_CASH_EXCLUDE = array('INDI','SECU','INKI');

/*
 * These codes define credit card transactions
 */

$AG_MIP_GIFT_CASH_CC_CODES = array('VISA','MC');

/*
 * Filters for exporting gifts and credit card gifts
 */

$AG_MIP_GIFT_EXPORT_FILTER = array('NULL:mip_export_session_id'=>'',
					     '!IN:gift_cash_form_code' => array_merge($AG_MIP_GIFT_CASH_EXCLUDE,$AG_MIP_GIFT_CASH_CC_CODES),
					     'is_deleted'=>sql_false());

$AG_MIP_GIFT_EXPORT_FILTER_CC = array('NULL:mip_export_session_id'=>'',
						  'IN:gift_cash_form_code' => $AG_MIP_GIFT_CASH_CC_CODES,
						  'is_deleted'=>sql_false());


function mip_import_files_list()
{
	/*
	 * Generate a list of files waiting to be exported (from AGENCY)/imported (to MIP)
	 */

	$gres = agency_query('SELECT mip_export_session_id,count(*) FROM gift_cash WHERE mip_export_session_id IS NOT NULL AND mip_export_session_id != \'CX000000FDG00\' AND gift_cash_date IS NULL GROUP BY 1 ORDER BY 1');
	$dres = agency_query('SELECT mip_export_session_id,count(*) FROM donor LEFT JOIN mip_export_session USING (mip_export_session_id) WHERE donor.mip_export_session_id IS NOT NULL AND mip_import_confirmed_by IS NULL GROUP BY 1 ORDER BY 1');

	if (sql_num_rows($gres) > 0) {

		$files = true;
		while ($a = sql_fetch_assoc($gres)) {

			$out .= html_list_item(mip_link_import_file($a['mip_export_session_id']).' ('.$a['count'].str_plural(' gift',$a['count']).')'.' '
						     . mip_link_import($a['mip_export_session_id']).' | '
						     . link_report('accounting/mip_gift.cfg','print_report',array('session_id' => $a['mip_export_session_id'])));

		}

	} else {

		$out .= html_list_item(dead_link(smaller('No gift files to import')));

	}

	if (sql_num_rows($dres) > 0) {

		while ($a = sql_fetch_assoc($dres)) {

			$out .= html_list_item(mip_link_import_file($a['mip_export_session_id']).' ('.$a['count'].str_plural(' donor',$a['count']).')'.' '
						     . mip_link_import($a['mip_export_session_id']));

		}

		$files = true;

	} else {

		$out .= html_list_item(dead_link(smaller('No donor files to import')));

	}

	return $files ? div(red('Files awaiting MIP import:')).html_list($out) : blue('No files available for MIP import');

}

function mip_parse_id($id)
{
	/*
	 * Take a MIP import ID and decode it, returning an array
	 * consisting of year and type
	 */

	if (preg_match('/^CX([0-9]{4})[0-9]{2}(FDG|FDD|FDCC)/',$id,$m)) {

		switch($m[2]) {

		case 'FDG':
			$t = 'gift';
			break;

		case 'FDCC':
			$t = 'gift_cc';
			break;

		case 'FDD':
			$t = 'donor';

		}
		return array('year'=>$m[1],
				 'type'=>$t);
	}
	return false;
}

function mip_type_from_id($id)
{

	/*
	 * Determine type from the ID
	 */

	$pid = mip_parse_id($id);
	return $pid['type'];
}

function mip_year_from_id($id)
{
	/*
	 * Determine year from ID
	 */

	$pid = mip_parse_id($id);
	return $pid['year'];

}

function mip_link_import_file($mip_id)
{

	/*
	 * Generate a download link to the file sitting on the filesystem
	 */

	if (mip_parse_id($mip_id)) {

		/*
		 * gift or donor
		 */

		$filename = mip_filename_from_id($mip_id);

	} else {

		return dead_link('Bad mip_id: '.$mip_id);

	}

	/*
	 * see if file exists
	 */
	$path = AG_MIP_EXPORT_DIRECTORY.'/'.$filename;

	if (is_readable($path)) {

		return hlink('mip_export.php?action=dl&id='.$mip_id,$filename);

	} else {

		return dead_link(red('Warning: no file found for corresponding export id: '.$mip_id));

	}

}

function mip_link_export()
{

	/*
	 * Makes a list of links for generating export files
	 */

	global $AG_MIP_GIFT_EXPORT_FILTER, $AG_MIP_GIFT_EXPORT_FILTER_CC;

	$gdef = get_def('gift_cash');

	/*
	 * Grab non-CC gifts awaiting export
	 */
	$gres = get_generic($AG_MIP_GIFT_EXPORT_FILTER,'','',$gdef['table']);
	$gnum = sql_num_rows($gres);

	/*
	 * Grab CC gifts awaiting export
	 */
	$gres_cc = get_generic($AG_MIP_GIFT_EXPORT_FILTER_CC,'','',$gdef['table']);
	$gnum_cc = sql_num_rows($gres_cc);

	if ($gnum > 0 || $gnum_cc > 0) {
		
		/*
		 * check for donors awaiting export
		 */
		$dres   = get_generic(array(),'','','export_donor_mip');
		$dcount = sql_num_rows($dres);

		if ($dcount > 0) {

			$extra = oline().'('.$dcount.str_plural(' donor',$dcount).' will be exported.)';

		}

		$links = array();
		if ($gnum) {

			$links[] = hlink('mip_export.php?action=cash_export','Generate gift export file ('.$gnum.str_plural(' gift',$gnum).')');
			$g_numbers[] = $gnum.' gifts';

		}

		if ($gnum_cc) {

			$links[] = hlink('mip_export.php?action=cc_export','Generate credit-card gift export file ('.$gnum_cc.str_plural(' credit-card gift',$gnum_cc).')');
			$g_numbers[] = $gnum_cc.' credit-card gifts';

		}

		return oline('There are '.implode(' and ',$g_numbers).' waiting to be exported.').implode(oline(),$links) . $extra;
		
	} else {

		return dead_link('No gifts to export');

	}
}

function mip_link_import($sess_id)
{
	/*
	 * Make link for confirming a file has been imported to MIP
	 */

	$type = mip_type_from_id($sess_id);

	return hlink('mip_update.php?t='.$type.'&id='.$sess_id,red('confirm import'));
}

function mip_filename_from_id($id)
{
	/*
	 * Construct a filename from a MIP export ID
	 */

	return 'mip_'.mip_type_from_id($id).'_export.'.$id.'.csv';
}

function mip_pass_through_download($id)
{
	/*
	 * Output the contents of a MIP file for download
	 */

	if (be_null($id)) {
		return '';
	}

	$basename = mip_filename_from_id($id);

	$file = AG_MIP_EXPORT_DIRECTORY.'/'.$basename;

	if(is_readable($file)) {

		//pass file on to browser
		header("Content-Type: text; charset=ISO-8859-1");
		header('Content-Disposition: attachment; filename="'.$basename.'"');
		readfile($file);
		exit;
	} else {
		log_error('Couldn\'t read requested file '.$file);
		exit;
	}
}

?>
