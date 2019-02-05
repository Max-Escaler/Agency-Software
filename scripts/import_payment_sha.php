#!/usr/bin/php
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



$MODE = 'TEXT';
$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$UID = $GLOBALS['sys_user'];

$def = get_def('import_sha_subsidy_payment');

//maybe move agency_config
$archive_directory = '/shared/agency_import/sha/archive/';
			
$csv_file =  $_SERVER['argv'][1];
$orig_file =  $_SERVER['argv'][2];

if (is_readable($csv_file)){
  $handle = fopen($csv_file, "r");				      
}

$date= $payee_info = $check_number = FALSE;

$header_read = FALSE;

if ($handle){

  while (($header_read == FALSE) && (($data = fgetcsv($handle)) !== FALSE)){
    // print_r($data);
  
    if (preg_match('/check number:\s*([0-9]+)/i', $data[0], $matches)){   
      $check_number = $matches[1];
    }

    elseif (preg_match('/item date:\s*([0-9.\/-]+)/i', $data[0], $matches)){  
      $date = $matches[1];
    }

    elseif (preg_match('/payee.*:\s*(.+)\s*address.*/i', $data[0], $matches)){
      $payee_info = $matches[1];
    }

    if ($payee_info && $date && $check_number){
      $header_read = TRUE;
    }
    
  } //end while header_read

  $need_index = TRUE;
  while (($need_index) && (($data = fgetcsv($handle)) !== FALSE)){

    if (!strlen($data[0]) == 0){
      $c = 0;
      foreach($data as $field){
      
	if (preg_match('/resident name.*/i', $field)){ 
	  $name_address_index = $c;
	}
	elseif (preg_match('/resident id.*/i', $field)){ 
	  $sha_id_index = $c; 
	}
	elseif (preg_match('/amount.*/i', $field)){ 
	  $amount_index = $c;
	}

	elseif (preg_match('/description.*/i', $field)){ 
	  $description_index = $c;
	}    
	$c++;
	//print_r($data);
	$need_index = FALSE;   
      }//end foreach
    }
  } //end need_index


  sql_begin();
  

  $test_res = get_generic(array('check_number' => $check_number), '', '', $def);
  
  if (count($test_res) > 0) {
    log_error("Payments for check number ".$check_number. " have already been imported.");
    page_close($silent=true);
    exit;
  }

  toggle_query_display(); 
  while (($data = fgetcsv($handle)) !== FALSE){
    //echo "adding to database";
    if(strlen($data[$name_address_index])>0 && strlen($data[$sha_id_index])>0 && strlen($data[$amount_index])>0){

      $vals = Array('resident_name_address' => $data[$name_address_index], 
		  'sha_resident_id' => $data[$sha_id_index],
		  'amount' => $data[$amount_index],
		  'agency_project' => $payee_info,
		  'item_date' => $date,
                  'description_adjustment_reason' => $data[$description_index],
                  'check_number' => $check_number, 
		  'added_by' => $UID,
		  'changed_by' => $UID);
    
      agency_query(sql_insert($def['table_post'], $vals));     
   //      $res = agency_query("Select * from {$def['table_post']}", $vals);
    }
  }

  

  $archive_directory .= year_of('now');
  if (!is_dir($archive_directory)) {
	mkdir($archive_directory);
  }
//set month

$archive_directory .= '/'.lpad(month_of('now'),2,'0');
if (!is_dir($archive_directory)) {
	mkdir($archive_directory);
}

 if (!rename($orig_file, $archive_directory.'/'.basename($orig_file))){
   $error .= oline('Failed to archive original: '.$orig_file);
 }
	
 if (!rename($csv_file, $archive_directory.'/'.basename($csv_file))){
   $error .= oline('Failed to archive csv: '.$csv_file);
 }

 chmod($archive_directory.'/'.basename($orig_file), 0400);
 chmod($archive_directory.'/'.basename($csv_file), 0400);




  //to commit transation:
 sql_end();

 }

page_close($silent=true);
fclose($handle);    
//table export_client_ids
//will want header



?>

