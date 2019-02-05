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

function get_swipe()
{
	// read a card swipe, which is posted as a filename w/ format
	// Y-M-D_H.M.S_CLIENTID_ISSUE
	// returns array
	global $swipe_dir, $DEBUG;
	$DEBUG && outline("about to open directory");
	$dir = @opendir($swipe_dir) or log_error("Unable to access gatekeeping data area $swipe_dir.  Please contact your system administrator.");

	while (false !== ($file = readdir($dir)))
	{
		$DEBUG && outline("Found File $file");
		if (preg_match('/^([0-9]{4}[-][0-9]{2}[-][0-9]{2})' // Date
				   . '_([0-9]{2}[.][0-9]{2}[.][0-9]{2})' // Time
				   . '_([0-9]{1,7})[-]([0-9]{1,3})$/',$file,$matches))
		{

			//fixme: this needs to use client_get in order to account for unduplicated clients
			$result["entered_at"]=$matches[1] . " " . preg_replace('/[.]/',':',$matches[2]);
			$result["client_id"]=$matches[3];
			$result["issue_no"]=$matches[4];
			$DEBUG && outline("Here it be: " . dump_array($result));
			closedir($dir);
			$DEBUG && outline("returning from get_swipe");
			return $result;
		}
	}
	closedir($dir);
	return;
}

function delete_swipe( $entry )
{
	global $swipe_dir;
	// delete the file that was read by get_swipe
	$patterns = array('/:/','/ /');
	$replace = array('.','_');
	$file = "$swipe_dir/" .
		preg_replace($patterns,$replace,$entry["entered_at"])
		. "_" . $entry["client_id"]
		. "-" . $entry["issue_no"];
	return delete_file($file);
}

function play_sound($file)
{
	global $cg_sound_player;
	exec("$cg_sound_player $file");
	return;
}

function indicate_barred()
{
	global $BARRED_WAV;
	play_sound($BARRED_WAV);
	return;
}

function indicate_provisional()
{
	//fixme: needs it's own sound
	global $PROVISIONAL_WAV;
	play_sound($PROVISIONAL_WAV);
	return;
}

function indicate_client_note()
{
	global $CLIENT_NOTE_WAV;
	play_sound($CLIENT_NOTE_WAV);
	return;
}

function indicate_not_priority()
{
	global $NOT_PRIORITY_WAV;
	play_sound($NOT_PRIORITY_WAV);
	return;
}

function indicate_obsolete_card()
{
	global $OBSOLETE_CARD_WAV;
	play_sound($OBSOLETE_CARD_WAV);
	return;
}

function indicate_disaster()
{
	global $DISASTER_WAV;
	play_sound($DISASTER_WAV);
	return;
}

function indicate_successful_entry()
{
      global $SUCCESS_WAV;
      play_sound($SUCCESS_WAV);
      return;
}


function parse_entry( $line )
{
	// takes a line read from the card_read program, YYYY-MM-DD HH:MM:SS CLIENTID-ISSUE#
	// and parses it into array.

	global $DEBUG;
	if (!preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) ([0-9]{1,6})[-]([0-9]{1,2})/',$line,$matches))
	{
		$DEBUG && outline("$line is not a valid ID card & TimeStamp");
	}
	return $matches;
}

// this works now...made change in sql_layer.php
function maintain_connection()
{
	global $AG_DB_CONNECTION;
	//outline("Got Connection, with type " . gettype($AG_DB_CONNECTION));

	if (!$AG_DB_CONNECTION)
	{
		$AG_DB_CONNECTION=db_connect();
	}
	if ($AG_DB_CONNECTION)
	{
		// out(ts("Testing connection"));
		if (pg_connection_status($AG_DB_CONNECTION)==PGSQL_CONNECTION_BAD)
		{
			$res=pg_connection_reset($AG_DB_CONNECTION);
		}
	}
	// out(ts("Tested connectin"));
	return ($AG_DB_CONNECTION && (pg_connection_status($AG_DB_CONNECTION)==PGSQL_CONNECTION_OK) );
}

?>
