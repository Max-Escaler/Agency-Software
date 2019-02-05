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


$title = 'Import/Export DSHS Medicaid Lookup Files';
require 'includes.php';

out(html_heading_1($title));

out(help('',html_list(html_list_item(hlink('https://wamedweb.acs-inc.com/wa/secure/home.do','WAMed Site','','target="_blank"'))),'',true));

$AG_EDI_270_DSHS = array(
				 'usage_indicator' => 'P',
				 'sender_id'       => 8026270,
				 'int_receiver_id' => '100000',
				 'receiver_id'     => '77045',
				 /* bht */
				 'bht_struct_code' => '0022',
				 'bht_purp_code'   => '13',
				 'max_subscribers' => 99,
				 'file_path'       => '/shared/agency_export/dshs'
);

function clinical_medicaid_make_edi($start,$end)
{
	require_once 'make_edi.php';
	
	global $AG_EDI_270_DSHS;

	// get clients
	$res = agency_query("SELECT * FROM export_client_dshs_medicaid_lookup WHERE client_id NOT IN 
                                (SELECT client_id FROM import_client_dshs_medicaid_lookup
                                    WHERE data_request_date = '{$start}' AND data_request_date_end = '{$end}')");

	if (sql_num_rows($res) < 1) {
		log_error('Couldn\'t find any SAGE clients that haven\'t been looked up for the current month');
		return false;
	}

	$edi_files = array();
	$i = 0;
	sql_begin();
	while ($a = sql_fetch_assoc($res)) {
		if (is_object($edi) && ($i === $edi->config['max_subscribers'])) { // finish file
			$i = 0;
			$cur_edi_file .= $edi->process_trailer();
			$edi_files[$batch] = $cur_edi_file;
		}

		if ($i == 0) { //start new file
			$batch = sql_get_sequence('seq_edi_270_interchange_control_number_dshs');

			$edi = new Make_EDI_270($AG_EDI_270_DSHS,$batch,$batch,$batch);

			$cur_edi_file = $edi->process_header()
				
				/* Beginning Hierarchical Transaction */
				. $edi->begin_hierarchical_transaction();
		}

		// insert a file
		global $UID;
		$imp_rec = array('client_id'             => $a['client_id'],
				     'data_request_id'       => $batch,
				     'data_request_date'     => $start,
				     'data_request_date_end' => $end,
				     'data_request_file'     => 'dshs_270.'.str_replace(' ','_',dateof($start,'SQL')).'.'.lpad($batch,6,'0').'.edi',
				     'data_request_by'       => $UID,
				     'added_by'              => $UID,
				     'changed_by'            => $UID
				     ); 

		$res_i = agency_query(sql_insert('tbl_import_client_dshs_medicaid_lookup',$imp_rec));
		if (!$res_i) {
			log_error('Couldn\'t insert record into tbl_import_client_dshs_medicaid_lookup for client '.$a['client_id'].'. This client will '.bold('not').' be included in the Medicaid lookup batch file.');
			continue;
		}

		$cur_edi_file .= $edi->set_subscriber($a,$start,$end);
		$i++;
	}
	sql_end();

	//finish last transaction
	if (!be_null($cur_edi_file)) {
		$cur_edi_file .= $edi->process_trailer();
		$edi_files[$batch] = $cur_edi_file;
	}

	//check for directories
	$file_year  = year_of('now');
	$file_month = lpad(month_of('now'),2,'0');
	if (!is_dir($edi->config['file_path'].'/'.$file_year)) {
		mkdir($edi->config['file_path'].'/'.$file_year);
		chmod($edi->config['file_path'].'/'.$file_year,0770);
	}
	if (!is_dir($edi->config['file_path'].'/'.$file_year.'/'.$file_month)) {
		mkdir($edi->config['file_path'].'/'.$file_year.'/'.$file_month);
		chmod($edi->config['file_path'].'/'.$file_year.'/'.$file_month,0770);
	}

	//write files to holding and archive
	$hold_name = $edi->config['file_path'].'/dshs_270.'.str_replace(' ','_',dateof($start,'SQL').'.');
	$arch_name = $edi->config['file_path'].'/'.$file_year.'/'.$file_month.'/dshs_270.'.str_replace(' ','_',dateof($start,'SQL').'.');
	foreach ($edi_files as $key=>$file) {
		$fhold_name = $hold_name . lpad($key,6,'0') . '.edi';
		$farch_name = $arch_name . lpad($key,6,'0') . '.edi';
		$out .= oline('Writing '.bold($fhold_name));
		//holding file
		file_put_contents($fhold_name,$file);
		chmod($fhold_name,0440);
		//archive file
		file_put_contents($farch_name,$file);
		chmod($farch_name,0440);
	}

	out($out);

}

function clinical_medicaid_valid_edi($string_file)
{
	if (preg_match('/^ISA\*00\*\s*\*00\*\s*\*ZZ\*100000\s*\*ZZ\*([0-9]*)\s*\*([0-9]{6})\*([0-9]{4})\*U\*00401\*([0-9]*)\*0\*P/',$string_file,$m)) {
		return $m;
	}
	return false;
}

function clinical_medicaid_parse_edi($string_file,$filename)
{
	// extract batch, date of inquiry and staff dshs id from ISA header
	if ($m = clinical_medicaid_valid_edi($string_file)) {
		global $UID;
		$rec_template = array('data_import_by' => $UID,
					    'data_import_generated_at' => dateof(date_iso_to_sql2($m[2]),'SQL').' '.time_iso_to_sql($m[3]),
					    'data_import_id' => $m[4],
					    'data_import_file' => $filename
					    );
	} else {
		//invalid EDI file
		outline(span('Invalid file '.$filename));
		return false;
	}

	$subscribers = explode('HL',$string_file);
		
	$recs = array();
	foreach ($subscribers as $client) {
		//match subscriber loop 2000c (of the form *3*2*22*0~TRN) or continue
		if (!preg_match('/^\*\d+\*\d+\*\d+\*\d+~TRN/',$client)) {
			continue;
		}
		
		$rec = $rec_template;
		$rec['raw_edi_2000c_loop'] = trim(str_replace('~',"~\n",$client)); // archive portion of edi file with line-breaks
		$c_arr = explode('~',$client);

		foreach ($c_arr as $cl) {
			
			if (preg_match('/^TRN\*2\*([0-9]+)\*[0-9]+$/',$cl,$m)) { // kcid
				
				$rec['client_id'] = client_get_id_from_kcid($m[1]);
				
			} elseif ( preg_match('/^NM1\*IL\*1\*([A-Z]*)\*([A-Z]*)\*([A-Z]*)\*.*MI\*(.*)$/',$cl,$m) ) { // name
				$rec['name_last']   = $m[1];
				$rec['name_first']  = $m[2];
				$rec['name_middle'] = $m[3];
				$rec['pic_number']         = $m[4];
				
			} elseif ( preg_match('/^REF\*SY\*([0-9]{9})$/',$cl,$m) ) { //ssn
				
				$rec['ssn'] = $m[1];
				
			} elseif ( preg_match('/^REF\*[A-Z0-9]{2}\*([A-Z0-9]*)\*(.*)$/',$cl,$m) ) { // a bunch of id's--ignoring for now
				
				$rec['messages'][] = $m[2].': '.$m[1];
				
			} elseif ( preg_match('/^DMG\*D8\*([0-9]{8})\*([MF]{1})$/',$cl,$m) ) { //dob and gender
				
				$rec['dob']    = date_iso_to_sql($m[1]);
				$rec['gender'] = $m[2];
				
			} elseif ( preg_match('/^DTP\*307\*RD8\*([0-9]{8})-([0-9]{8})$/',$cl,$m) ) { //range of inquiry

				$rec['data_request_date'] = date_iso_to_sql($m[1]);
				$rec['data_request_date_end'] = date_iso_to_sql($m[2]);
				
			} elseif ( preg_match('/^EB\*([A-Z0-9]{1,2})\*([A-Z]{3})?\*30\*(.*)\*(.*)$/',$cl,$m) ) { // eligibility

				$rec['eligibility'] = $m[1];
				$rec['eligibility_type'] = $m[3];
				$rec['eligibility_coverage_level'] = $m[4];
				
			} elseif ( preg_match('/^DTP\*356\*D8\*([0-9]{8})$/',$cl,$m) ) { // coverage start

				$rec['coverage_date'] = date_iso_to_sql($m[1]);
				
			} elseif ( preg_match('/^DTP\*357\*D8\*([0-9]{8})$/',$cl,$m) ) { // coverage end
				
				$rec['coverage_date_end'] = date_iso_to_sql($m[1]);
				
			} elseif ( preg_match('/^MSG\*(.*)$/',$cl,$m) ) {
				
				$rec['messages'][] = $m[1];
				
			}
			
			
		}
		
		//set default dates if not found
		if (!be_null($rec['client_id'])) {
			$rec['data_request_date'] = orr($rec['data_request_date'],start_of_month(today())); //assume current month
			$rec['data_request_date_end'] = orr($rec['data_request_date_end'],end_of_month(today())); //assume current month
		}

		$recs[] = $rec;
		
	}
	
	return $recs;
}

function clinical_medicaid_lookup_import($recs)
{
	sql_begin();
	$holding_files = array();
	foreach ($recs as $rec) {
		$filter = array();
		outline('Importing data for client '.$rec['client_id']);
		foreach (array('client_id','data_request_date','data_request_date_end') as $el) {
			$filter[$el] = $rec[$el];
			unset($rec[$el]);
		}

		$rec['messages'] = implode("\n",orr($rec['messages'],array()));
		
		// verify record exists
		$res = get_generic($filter,'','','import_client_dshs_medicaid_lookup');
		if (count($res) <1) {
			log_error('No record exists in import_client_dshs_medicaid_lookup for client '.$rec['client_id'].'. This data has not been imported: '
				    . print_r($rec,true));
			continue;
		} elseif (count($res) > 1) {
			log_error('This client ('.$rec['client_id'].') has more than one record in import_client_dshs_medicaid_lookup for this time period.');
		} else {
			//get record back to find holding file name
			$exp_rec = array_shift($res);
			$t_filename = $exp_rec['data_request_file'];
			if (!in_array($t_filename,$holding_files)) {
				$holding_files[] = $t_filename;
			}
		}
		
		// update record
		$res_u = agency_query(sql_update('tbl_import_client_dshs_medicaid_lookup',$rec,$filter));
		if (!$res_u) {
			log_error('Couldn\'t update tbl_import_client_dshs_medicaid_lookup for client '.$rec['client_id'].'. Here is the record: '.print_r($rec,true));
		}
	}
	sql_end();

	// delete holding files
	global $AG_EDI_270_DSHS;
	$path = $AG_EDI_270_DSHS['file_path'];
	foreach ($holding_files as $file) {
		if (!unlink($path.'/'.$file)) {
			log_error('Unable to delete holding file '.$path.'/'.$file);
		} else {
			outline('Removing holding file '.bold($path.'/'.$file));
		}
	}
}

function clinical_medicaid_process_upload_files()
{
	$files = array();
	foreach ($_FILES as $var => $file_info) {

		switch ($file_info['error']) {
		case UPLOAD_ERR_OK : // successful upload
			if ($file_info['size'] == 0) { //attempted to upload
				$error .= oline('Upload failed for '.$file_info['name']);
				continue 2;
			}
			if (!is_uploaded_file($file_info['tmp_name'])) {
				log_error('Possible file upload attack: '.$file_info['tmp_name']);
				continue 2;
			}

			break;
		case UPLOAD_ERR_NO_FILE : //this is okay, since we provide ten upload slots
			continue 2;
		case UPLOAD_ERR_INI_SIZE :
		case UPLOAD_ERR_FORM_SIZE :
			$error .= oline('File '.$file_info['name'].' exceeded maximum file size. Contact your system administrator.');
			continue 2;
		default:
			$error .= oline('An error (error code '.$file_info['error'].') occurred uploading file '.$file_info['name'].'.');
			continue 2;
		}

		//verify file
		$tmp_file = file_get_contents($file_info['tmp_name']);
		if (!clinical_medicaid_valid_edi($tmp_file)) {
			$error .= $file_info['name'].' is not a valid 270 EDI file.';
			continue;
		}

		// archive file
		global $AG_EDI_270_DSHS;

		//create directories if needed
		$path = $AG_EDI_270_DSHS['file_path'].'/import/'.year_of('now');
		if (!is_dir($path)) {
			mkdir($path);
			chmod($path,0770);
		}
 		$path .= '/'.month_of('now');
		if (!is_dir($path)) {
			mkdir($path);
			chmod($path,0770);
		}

 		$name = $path.'/'.$file_info['name'];
  		if (is_file($name)) {
  			log_error($name.' already exists and has already been imported.');
  			continue;
  		}

  		move_uploaded_file($file_info['tmp_name'],$name);

		$file = file_get_contents($name);

		$files[$file_info['name']] = $file;
	}

	outline(span($error,' class="error"'));

	return $files;
}

$action = $_REQUEST['action'];

if (!has_perm('dshs_medicaid')) {
	$fail_mesg = 'You don\'t have permissions for this';
}

if ($fail_mesg) {
	out(alert_mark($fail_mesg));
	page_close();
	exit;
}

switch ($action) {
 case 'export' :

	 clinical_medicaid_make_edi(start_of_month(today()),end_of_month(today()));

	 break;

 case 'import' :
	 if ($files = clinical_medicaid_process_upload_files()) { //an array of files

		 foreach ($files as $name => $file) {
			 $recs = clinical_medicaid_parse_edi($file,$name);
			 clinical_medicaid_lookup_import($recs);
		 }
		 break;
	 } else {
		 outline(span('No files uploaded or processed',' class="error"'));
	 }
 default :
	 //menu
	 for ($i=0; $i < 10; $i++) {
		 $ffile .= oline(formfile('userfile'.$i));
	 }

	 out(html_fieldset('Export',hlink($_SERVER['PHP_SELF'].'?action=export','Generate DSHS batch files for upload'))
	     . html_fieldset('Import download files',
				   formto($_SERVER['PHP_SELF'].'?action=import','file_form','enctype=multipart/form-data')
				   . hiddenvar('MAX_FILE_SIZE',3000000)
				   . $ffile
				   . button('Upload File(s)')
				   . formend()
				   )
	     );

}

page_close();
?>
