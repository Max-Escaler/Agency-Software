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



  /*
   * The idea:
   *
   * 1) All elements {ele} in export_views correspond a view named export_kc_{ele} and 
   *    optionally, a view named export_kc_{ele}_deletes.
   *
   * 2) Records are selected from the 2 kc views and compiled into a batch file,
   *    or, in the case of DALs, an EDI file is created
   *
   * 3) During batch file compilation, primary keys for each type are stored.
   *
   * 4) After writing the file, the tables (tbl_{ele}) are updated with the export kc id.
   *    If the export views are structured correctly, this will remove the records from
   *    the view.
   *
   * 5) DALs are exported to an EDI file
   *
   * (Functions declared at bottom)
   */



$MODE = 'TEXT';

$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$UID = $GLOBALS['sys_user'];

define('AG_KING_COUNTY_EXPORT_DIRECTORY','/shared/agency_export/king_county');

//make certain that directory is writeable
if (!is_writable(AG_KING_COUNTY_EXPORT_DIRECTORY)) {
	log_error('KC Export Error: Directory '.AG_KING_COUNTY_EXPORT_DIRECTORY.' either doesn\'t exist or cannot be written to.');
	page_close($silent = true);
	exit;
}

$archive_directory = AG_KING_COUNTY_EXPORT_DIRECTORY.'/archive/'.year_of('now');
if (!is_dir($archive_directory)) {
	mkdir($archive_directory);
 }

$export_views = array(
			    'activity_evaluation',
			    'client',
			    'clinical_condition_at_assessment',
			    'clinical_priority',
			    'clinical_reg', //actually notice of exit
			    'clinical_reg_request',
 			    'cod_assessment',
 			    'cod_screening',
			    'diagnosis',
			    'disability_clinical',
			    'medicaid',
			    'pss',
			    'referral_clinical',
			    'residence_clinical',
			    'residential_facility', //actually built off of residence clinical
			    'staff',
			    'staff_assign',
			    'staff_phone',
			    'staff_qualification'
			    );

$export_file = '';
$primary_keys = array();

sql_begin();

$rec_count = 0; // at least a header and footer

foreach ($export_views as $exv) {
	
	$view = 'export_kc_'.$exv;
	$sql = 'SELECT * FROM '.$view;
	$id_field = $exv.'_id';
	$primary_keys[$exv] = array();

	if (isTableView($view.'_deletes')) {
		$sql .= ' UNION ALL SELECT * FROM '.$view.'_deletes';
	}

	$res = agency_query($sql);

	if ( sql_num_rows($res) > 0 ) {

		while ($a = sql_fetch_assoc($res)) {

			$primary_keys[$exv][] = $a[$id_field]; // store primary key for later update

			// unset primary key
			unset($a[$id_field]);

			$export_file .= implode("\t",$a)."\n"; // CRLF line-terminators for KC
			
			$rec_count ++;
		}

	}
}

if (!be_null($export_file)) {
	/*
	 * Export to be done, batch number generated
	 */

	$batch_number = sql_get_sequence('seq_king_county_batch_number');

	/*
	 * Insert record into kc export table
	 */

	$export_kc_id = sql_get_sequence('tbl_export_kc_export_kc_id_seq');

	$res = agency_query(sql_insert('tbl_export_kc',array('export_kc_id'=>$export_kc_id,
									   'FIELD:export_at'=>'CURRENT_TIMESTAMP',
									   'export_by'=>$UID,
									   'batch_number'=>$batch_number,
									   'added_by'=>$UID,
									   'changed_by'=>$UID)));

	if (!$res) {
		log_error('KC Export Error: could not insert record into tbl_export_kc. Export failed');
		page_close($silent=true);
		exit;
	}

	/*
	 * Write batch file
	 */
	$basename = '152'.lpad($batch_number,5,'0').'.bat';
	$filename = AG_KING_COUNTY_EXPORT_DIRECTORY.'/'.$basename;

	// set header and footer
	$export_file = export_kc_get_header($batch_number)
		. $export_file
		. export_kc_get_footer($batch_number,$rec_count);

 	$result = file_put_contents($filename,$export_file);

	if (!$result) {
		log_error('KC Export Error: could not write file export to '.$filename.'. Export failed.');
		page_close($silent = true);
		exit;
	}

	$archive_filename = $archive_directory.'/'.$basename;
	$result = file_put_contents($archive_filename,$export_file);
	if (!$result) {
		log_error('KC Export Error: could not archive file export to '.$archive_filename.'. Export failed.');
		page_close($silent = true);
		exit;
	}

	outline('KC Export successfully exported '.$filename);

	/*
	 * Update records with batch number
	 */
	foreach ($export_views as $exv) {

		$id_field = $exv.'_id';
		$ids = array_filter($primary_keys[$exv]);

		if (count($ids) > 0) {

			// if export field exists in native table, update
			if (is_field('tbl_'.$exv,'export_kc_id')) {
			
				$res_native = agency_query(sql_update('tbl_'.$exv,array('export_kc_id' => $export_kc_id,
													'FIELD:changed_at' => 'CURRENT_TIMESTAMP',
													'changed_by' => $UID),
										array('IN:'.$id_field=>$ids)));

				if (!$res_native) {
					log_error('KC Export Error: Failed to update exported records with kc export id. Export failed.');
					page_close($silent = true);
					sql_abort();
					exit;
				}
			}

			//also update kc transaction table
			$res = agency_query(sql_update('tbl_export_kc_transaction',array('export_kc_id' => $export_kc_id,
													   'FIELD:changed_at' => 'CURRENT_TIMESTAMP',
													   'changed_by' => $UID),
							     array('object_type' => $exv,
								     'IN:object_id' => $ids)));

			if (!$res) {

				log_error('KC Export Error: Failed to update export_kc_transaction with kc export id. Export failed.');
				page_close($silent = true);
				sql_abort();
				exit;

			}
		}

	}
}

sql_end();

/* Export DALs */
sql_begin();
if ($dal_file = export_kc_dals($batch_number)) {

	$basename = '152'.lpad($batch_number,5,'0').'.edi';
	$filename = AG_KING_COUNTY_EXPORT_DIRECTORY.'/'.$basename;

	$result = file_put_contents($filename,$dal_file);

	if (!$result) {
		log_error('KC Export Error: could not write DAL file export to '.$filename.'. Export failed.');
		sql_abort();
		page_close($silent = true);
		exit;
	}

	$archive_filename = $archive_directory.'/'.$basename;
	$result = file_put_contents($archive_filename,$dal_file);
	if (!$result) {
		log_error('KC Export Error: could not archive DAL file export to '.$archive_filename.'. Export failed.');
		sql_abort();
		page_close($silent = true);
		exit;
	}

	outline('KC Export successfully exported '.$filename);

}
sql_end();

page_close($silent = true);

function export_kc_get_header($batch_number)
{
	$header = array('000.03',                 //transaction id
			    dateof('now','NO_SEP'),   //date in YYYYMMDD format
			    '152',                    //agency id
			    lpad($batch_number,5,'0') //batch number
			    );
	return implode("\t",$header)."\n";
}

function export_kc_get_footer($batch_number,$rec_count)
{
	$footer = array('999.01',                  //transaction id
			    lpad($batch_number,5,'0'), //batch number
			    dateof('now','NO_SEP'),    //date in YYYYMMDD format
			    '152',                     //agency id
			    lpad($rec_count,5,'0'))    //rows, does _not_ include header and footer
		;
	return implode("\t",$footer)."\n";
}

function export_kc_dals(&$batch)
{
	global $UID;

	$AG_EDI_837_KC = array(
				     'usage_indicator' => 'P', //T for test, P for production
				     'sender_id'       => '',
				     'sender_name'     => org_name(),
				     'sender_ein'       => '',  //ORG EIN
				     'sender_npi'       => '', //ORG NPI
				     'sender_address'   => '',
				     'sender_city'      => '',
				     'sender_state'     => '',
				     'sender_zipcode'   => '',
				     'sender_taxonomy_code' => '', //ORG taxonomy code
				     'int_receiver_id' => '',
				     'receiver_id'     => '',
				     'receiver_name'   => '',
				     'isa_auth_info'   => '0123456789',
				     'isa_sec_info'    => '9876543210',
				     /* bht */
				     'bht_struct_code' => '0019',
				     'bht_purp_code'   => '00'
				     );

	global $off;
	require_once $off.'make_edi.php';
	

	// get DALs
	$res = agency_query("SELECT * FROM export_dal");

	if (sql_num_rows($res) < 1) {
		// no DALs to export today
		return false;
	}

	//start file
	$batch = sql_get_sequence('seq_king_county_batch_number');
	$edi = new Make_EDI_837($AG_EDI_837_KC,$batch,$batch,$batch);
	$edi_file = $edi->process_header()
		. $edi->begin_hierarchical_transaction();

	$old_action = $old_client = $old_location = '';
	while ($a = sql_fetch_assoc($res)) {
		$client = $a['client_id'];
		if ($client !== $old_client) {

			/*
			 * Starting on a new client?
			 */
			$edi_file .= $edi->set_subscriber($a)
				. $edi->set_payer($a);

			// reset action, auth id and locations
			$old_action = $old_location = '';
			
		}

		$action = $a['action_code'];
		$location = $a['location_cpt_code'];
		$auth = $a['auth_id'];

		if ($old_action !== $action
		    || $old_location !== $location
		    || $old_auth !== $auth) {

			/*
			 * Start a new claim loop
			 */
			$claim_number = sql_get_sequence('seq_edi_837_claim_number_kc');
			$edi_file .= $edi->set_claim($claim_number,$a)
				. $edi->set_service_location($a);

		}

		//update dal
		//fixme: move to tbl_export_kc
		$ures = agency_query(sql_update('tbl_dal',array('FIELD:post_date'=>'CURRENT_DATE',
									    'post_batch'=>$batch,
									    'post_claim_number'=>$claim_number,
									    'changed_by' => $UID,
									    'FIELD:changed_at'=>'CURRENT_TIMESTAMP'),
							array('dal_id' => $a['dal_id'])));

		if (!$ures) {

			log_error(__FILE__.' had troubles updating dal #'.$a['dal_id'].' so the export has been aborted. Please investigate.');
			sql_abort();
			page_close($silent = true);
			exit;
		}
		
		$edi_file .= $edi->set_service($a)
			.$edi->set_rendering_provider($a);

		//update dal with post_date, claim number and batch number

		$old_client   = $client;
		$old_action   = $action;
		$old_auth     = $auth;
		$old_location = $location;
	}

	//finish file
	$edi_file .= $edi->process_trailer();

	return $edi_file;
}


?>
