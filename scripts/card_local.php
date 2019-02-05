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
$MODE="TEXT";

require 'card_config.php';

$MESSAGE_FILE="/var/log/entry.normal.log";

//In vi: Ctrl-V, ESC to insert ESC char (shows up as ^[)
$RED   = "[1;31;40m";
$BLUE  = "[1;34;40m";
$GREEN = "[1;32;40m";
$NORM  = "[0;;40m";
$WHITE = "[1;37;40m";

// $DEBUG=true;
//$query_display="Y";
$swipe_dir=$root_install_dir.'/data/local';

//outline("Count is {$_SERVER['argc']}");
//outline("HERE IS ARG: " . dump_array($_SERVER['argv']));
$argv = $_SERVER['argv'];
$CARD_MODE=orr($argv[1],"SQL");
//outline("argv[1]= " . $argv[1]);
//outline("CARD MODE DETECTED AS $CARD_MODE");
//$CARD_MODE="SQL";
//$CARD_MODE="LOCAL";
outline("READY TO DISPLAY ENTRIES " . (($CARD_MODE=="SQL") ?
                                                   "(database mode)" : " (offline mode)"),2);

while (true)
{
        while (! ($entry = get_swipe())) {
		  $DEBUG && outline("Sleeping 1");
		  sleep(1);
        }
        $id=$entry["client_id"];
        $issue=$entry["issue_no"];

        // ??? if the client_id doesn't really exist, then we don't want
        // to run any of the db stuff either.  There are many query
        // errors right now, if you try to use an invalid client_id

        //      if (! ( $db_status=maintain_connection()) )
        if ($CARD_MODE=="LOCAL" || !($db_status=maintain_connection())) {
		  $msg .=dateof($entry["entered_at"]) . " "
			  . timeof($entry["entered_at"],"ampm","secs") . " "
			  . " (db unavailable) " . " ($id-$issue)";
		  $flag=false;
        } else {
		  // start DB stuff
		  $DEBUG && outline("beginning SQL lookups");
		  $client=sql_fetch_assoc(client_get($id));
		  $DEBUG && outline("got client lookup");
		  if (!$client["client_id"]) {
			  log_error("Card swipe for $id, Client Not Found!!");
			  indicate_disaster();
			  delete_swipe($entry);
			  continue;
		  } else {
			  $DEBUG && outline("looking up residence");
			  $residence=get_last_housing_history($client["client_id"]);
			  $DEBUG && outline("looked up residence");
			  $note_filter = array('client_id'=>$client['client_id'],
						     'ARRAY_CONTAINS:flag_entry_codes'=>array($entry_location_code));
			  $notes = get_generic($note_filter,'','','client_note');
		  }
		  $msg .=dateof($entry["entered_at"]) . " "
			  . timeof($entry["entered_at"],"ampm","secs") . " "
			  . client_name($client) . " ($id-$issue)";
		  $DEBUG && outline("Message so far = $msg");

		  if (bar_status($client,'','','','',$bar_locations)) { // client is barred
			  $bar=bar_status_f($client,'',$is_provisional);
			  $msg .= " ({$RED}BARRED$NORM)"; // add color
			  if ($is_provisional) {
				  indicate_provisional();
			  } else {
				  indicate_barred();
			  }
			  $flag=true;
		  }
		  if ($residence) { // client is housed
			  $housed =  'HOMELESS' !== sql_lookup_description($residence['living_situation_code'],'l_living_situation','','housing_status');
			  if ($residence['residence_date'] && (!$residence['residence_date_end']) && $housed) {
				  $cur_l_sit = orr(sql_lookup_description($residence['living_situation_code'],'l_living_situation'),$residence['living_situation_code']);
				  $msg .= " ({$RED}Please verify Housing History record up-to-date (current record indicates client is in $cur_l_sit)$NORM)"; // add color
				  //indicate_barred();
				  call_user_func($indicate_housed_function);
				  $flag=true;
			  }
		  }
		  if ($check_safe_harbors) {
			  global $engine;
			  $def = $engine['safe_harbors_consent'];
			  $shres = get_generic(client_filter($id),'','',$def);

			  if (count($shres) < 1) { //no safe harbors consent
				  $flag = true;
				  indicate_client_note();
				  $msg .= "{$RED}Missing Safe Harbors Consent{$NORM}";
			  }
		  }
		  if ($check_chronic_homeless) {
			  global $engine;
			  $def = $engine['chronic_homeless_status_asked'];
			  $chres = get_generic(client_filter($id),'','',$def);

			  if (count($chres) < 1) { //chronic homeless not asked
				  $flag = true;
				  indicate_client_note();
				  $msg .= "{$RED}Not yet asked about chronic homeless status{$NORM}";
			  }
		  }

		  $DEBUG && outline("Message so far = $msg");
// 		  if (!has_priority($client)) {
		  if (!call_user_func($priority_function,$client)) {
			  indicate_not_priority();
			  $msg .= " ({$RED}NO PRIORITY$NORM)"; // add color
			  $flag=true;
		  }
		  $DEBUG && outline("Message so far = $msg");
		  if ($client["client_id"] != $id) {
			  indicate_obsolete_card();
			  $msg .= "$RED Confiscate Card.  Client was unduplicated.  New ID = {$client["client_id"]}$NORM";
		  }
		  $DEBUG && outline("Message so far = $msg");
		  if ($issue<$client["issue_no"]) {
			  indicate_obsolete_card();
			  $msg .= "$BLUE Obsolete card.  Current={$client["issue_no"]}$NORM"; // add color
			  $flag=true;
		  }
		  $DEBUG && outline("Message so far = $msg");

		  // newly-issued cards take time to transfer to AGENCY,
		  // so ignore cards 1 greater than DB issue #.

		  if ($issue>($client["issue_no"]+1)) {
			  log_error("Wrong Issue #:  Client {$client["client_id"]}.  Card swipe issue #$issue.  Database issue #={$client["issue_no"]}","QUIET");
			  $msg .= " Wrong Issue #.  Current={$client["issue_no"]}";
			  $flag=true;
		  }


		  //client note display
		  if (count($notes) > 0) {
			  $out_notes = $RED."\n".'===Client has notes flagged for entry. Edit note(s) to remove from this screen in the future.==='.$NORM;
			  while ($a = array_shift($notes)) {
				  $out_notes .= $WHITE."\n client_note id ".$a['client_note_id'].' ('.dateof($a['added_at'])."):\n      "
					  .str_replace("\n","\n      ",$a['note']).$NORM;
			  }
			  $msg .= $out_notes;
			  indicate_client_note();
			  $flag = true;
		  }

		  $DEBUG && outline("Message so far = $msg");
		  // END DB STUFF
        }
        $DEBUG && outline("After switch, msg = $msg");
        if (!$flag) {
		  indicate_successful_entry();
        }
        out( $flag
             ? $msg
             : "$GREEN$msg$NORM");
        $flag=false;
        $msg="";
        delete_swipe($entry);
}

?>
