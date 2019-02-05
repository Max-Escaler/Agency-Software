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
 * id_card.php -- functions relating to printing ID cards
 */

function generate_client_card($id)
{
	/*
	 * This function takes a client ID and returns an open-office
	 * writer file of an ID card
	 */
	
      global $client_card_temp_filename,$client_table;

	$query="SELECT to_char(current_date,'MM/DD/YY') AS issue_date, 
              substring(name_full for ".AG_CLIENT_ID_CARD_MAX_NAME_LENGTH.") AS name, 
              client_id, client_id::text || '-'
    || lpad(issue_no::text,2,'0') AS id_string
    FROM $client_table";
	
	$rec=agency_query($query,client_filter($id));
	// generate a convert file
	$source=client_photo_filename($id);

	// FIXME: this should be a user-specific location to avoid conflicting
	// requests attempting to write to the same file
	$new = $client_card_temp_filename;

	if (!is_writable($new)) {
		log_error('Unable to write to temporary client card location ('.$client_card_temp_filename.')');
		page_close();
		exit;
	}
	clearstatcache();
	// Move somewhere else, like agency_config:
	// Should test results:
	exec("convert -contrast -modulate 110 -type grayscale $source $new",$failure);
	if ($failure)
	{
		log_error("Unable to process photo file.  Error code $failure. Giving up.");
		page_close();
		exit;
	}
	$pic=file_get_contents_url($new);

//  FIXME: these steps can be used instead of the system call above
// 	$new = $client_card_temp_filename;
// 	image_grayscale($source,$new);
// 	$pic = file_get_contents_url($new);
	
	$file=oowriter_merge($rec,AG_CLIENT_CARD_TEMPLATE,"",array("Pictures/photo.jpg"=>$pic));
	return $file;
}

function generate_staff_card($id)
{
	/*
	 * This function takes a staff ID and returns an open-office
	 * writer file of an ID card
	 */
	
	$def=get_def('staff');
	$staff_table=$def['table'];
	$query="SELECT SUBSTRING((name_first || ' ' || name_last),1,".AG_STAFF_ID_CARD_MAX_NAME_LENGTH.") AS name, staff_id::text AS id_string, to_char(current_date,'MM/DD/YY') AS issue_date,SUBSTRING(description,1,".AG_STAFF_ID_CARD_MAX_JOB_TITLE_LENGTH.") AS job_title FROM $staff_table LEFT JOIN l_staff_position USING (staff_position_code)";
	
	$rec=agency_query($query,array("staff_id"=>"$id"));
	$pic=file_get_contents_url(staff_photo_url($id,4,true));
	$file=oowriter_merge($rec,AG_STAFF_CARD_TEMPLATE,'',array("Pictures/photo.jpg"=>$pic));
	return $file;
}

function is_id_station()
{
	// checks to see if computer can print cards
	global $AG_ID_CARD_STATIONS;
	return in_array($_SERVER['REMOTE_ADDR'],orr($AG_ID_CARD_STATIONS,array())) or has_perm('STAFF_ID_CARDS');
}

function is_photo_station()
{
	// checks to see if computer can take photos 
	global $AG_PHOTO_STATIONS;
	return in_array($_SERVER['REMOTE_ADDR'],$AG_PHOTO_STATIONS);
}

function is_id_card_station_connections()
{
	return $_SERVER['REMOTE_ADDR'] === AG_ID_CARD_IP_CONNECTIONS;
}

function is_id_card_station_shelter()
{
	return $_SERVER['REMOTE_ADDR'] === AG_ID_CARD_IP_SHELTER;
}

?>
