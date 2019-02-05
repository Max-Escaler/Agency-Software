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

require 'card_config.php';

$swipe_dir=$root_install_dir.'/data/sql';

//$DEBUG=true;
// $connection=db_connect();

while (true)
{
	$posted=false;
	while (! ($entry = get_swipe())) 
	{ 
		sleep(1); 
	}
	if (!maintain_connection()) { continue; }
	$entry['source']='X';
	$entry['entry_location_code']=$entry_location_code;
	$DEBUG && outline("Read Entry: " . dump_array($entry));

	// look for already-existing record
	// post_entry same as entry, except client_id might  be unduplicated
	$post_entry = $look_filter = $entry;

	if ($client = sql_fetch_assoc(client_get($entry[AG_MAIN_OBJECT_DB.'_id']))) {

		$post_entry[AG_MAIN_OBJECT_DB.'_id'] = $look_filter[AG_MAIN_OBJECT_DB.'_id'] = $client[AG_MAIN_OBJECT_DB.'_id'];

		// look for entries posted in current minute
		$enter_min = substr($entry["entered_at"],0,strlen($entry["entered_at"])-3);
		$look_filter["BETWEEN:entered_at"]="'$enter_min:00' AND '$enter_min:59'";
		unset($look_filter["entered_at"]);

		$look=agency_query($entry_select_sql,$look_filter);

		if (sql_num_rows($look)==0) // not posted yet
		{
			$res=agency_query(sql_insert($entry_table,$post_entry));
			if ($res)
			{
				$DEBUG && outline("Record successfully posted");
			}
			else
			{
				log_error("Failed to post entry record: " . dump_array($post_entry));
			}

			$posted=true;
			$look=agency_query($entry_select_sql,$post_entry);
		}

		if (sql_num_rows($look)<1)
		{
			log_error("Unable to successfully retrieve entry record.  Query Error.");
		}
		else
		{
			$DEBUG && outline("Record successfully verified.  "
				. ($posted
				? "(record posted)"
				: "(record already existed)"));
			if (!delete_swipe($entry))
			{
				log_error("Unable to delete swipe file: " . dump_array($entry));
			}	
		}
	} else {
	
                     if (!delete_swipe($entry))
                        {
                         log_error("Unable to delete swipe file: " . dump_array($entry));
                        }
        
	}
}
?>
