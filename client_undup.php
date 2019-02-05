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

// a user interface for client unduplication
  // much of the work done here could be done
  // by engine at some point in the future if things
  // become too complicated.
  //$query_display="Y";
$quiet = true;
include 'includes.php';
$mo_def=get_def(AG_MAIN_OBJECT_DB);
$mo_noun=$mo_def['singular'];
$title = ucwords($mo_noun).' Unduplication';
agency_top_header();

foreach(array('step','dup_id','val_id','newer_ok','approved','comment') as $x) {
      $$x=$_REQUEST[$x];
}

$undup_link_text='Unduplicate '.ucwords($mo_noun).'s From Database';
$undup_db_perms=can_undup_db();  //permissions to unduplicate
$undup_perms=can_undup();
$clientid_lbl = AG_MAIN_OBJECT_DB.'_id';
$newid_lbl = AG_MAIN_OBJECT_DB.'_id';
$oldid_lbl = AG_MAIN_OBJECT_DB.'_id_old';
$comment_lbl = 'comment';
$script = basename($_SERVER['PHP_SELF']);
$event_type = 'Unduplication';
$tdate = dateof(time(), 'SQL');

$undup_sql = "SELECT * FROM $undups_table WHERE $undup_flag IS NULL";
$dups_in_db = sql_num_rows(agency_query($undup_sql));

if ($step=="undup_overlooked") {
      if ($undup_db_perms) {
		$mesg .= oline('Are you sure? This process could take awhile.');
		$out .= yesno_form('step','yes_do_overlooked',link_admin('No','Button'));
      } else {
		$mesg.=oline("You don't have the proper permissions to unduplicate the database.");
      }
} elseif ($step=="yes_do_overlooked") {
      if ($undup_db_perms) {
		$result=undup_overlooked();
      } else {
	    $mesg.=oline("You don't have the proper permissions to unduplicate the database.");
      }
}

if ($step=="process_undup") {
      if ($undup_db_perms) {
		  //construct an unduplication record to post
		$undup_rec=make_rec($dup_id,$val_id,$comment,$approved);
		if ($approved) {
			$merged_record = $_REQUEST["merged"];
			$photo_to_keep_id = $_REQUEST["photo"];
			$undup_tabs=undup_db($undup_rec,$merged_record,$photo_to_keep_id);
		}

		if (!$undup_tabs && $approved) {
			outline(red('There was a '.$mo_noun.' record to unduplicate, but undup_db() failed to unduplicate.'));
		} else {
			$filter=array(
					  $newid_lbl=>$val_id,
					  $oldid_lbl=>$dup_id);
			$result = agency_query(sql_update($undups_table,$undup_rec,$filter));
			if (!$result) {
				$mesg .= oline("Failed to insert undup_rec into $undups_table:")
					. dump_array($undup_rec);
			}

			if ($approved) {
				outline("Unduplication successfull:");
				outline("\tAll data for ".$mo_noun." $dup_id has been merged into ".$mo_noun." $val_id.");
			} else { 
				outline(red("Duplicate record has been marked as false in the $undups_table table."));
			}
			$step ="undup"; //continue to next undup.
		}
	} else {
		$mesg .= oline(bigger('You don\'t have permission to unduplicate '.$mo_noun.'s in the database'));
      }	
      
}

if ($step=="undup") {
      $title = 'Unduplicating '.ucwords($mo_noun).'s From AGENCY Database';
      if ($undup_db_perms) {
		$result = agency_query($undup_sql);
		if (!sql_num_rows($result)) {
			$nomore=true;
		}

		if ($nomore) {
			$out .= oline(bigger(red("All ".$mo_noun."s in table $undups_table have been unduplicated.")));
		} else {
			$undup_rec = sql_fetch_assoc($result);
			$dup_id = $undup_rec[$oldid_lbl];
			$val_id = $undup_rec[$newid_lbl];
			$comment = $undup_rec[$comment_lbl];

			if ( ($dup_id < $val_id) ) {
				$mesg .= oline(bigger(bold("Warning: ")) . "The duplicate ID is older than the valid ID");
			}

			$title = hrule() . $title;
			$out .= oline(smaller("Unconfirmed records remaining in unduplication table: " . $dups_in_db)); 
			$out .=  para("This record was added to the unduplication table by " 
					  . staff_link($undup_rec["added_by"]) . " at " 
					  . timeof($undup_rec["added_at"]) 
					  . " on " . dateof($undup_rec["added_at"],"US") );
			$out .= confirm_undup_db($dup_id,$val_id,"process_undup");
		}
	} else {
		$mesg .= oline(bigger('You don\'t have permission to unduplicate '.$mo_noun.'s in the database'));
      }	
} elseif ($step=="process") {
      if($undup_perms) {
		global $UID;
		//passed checks so we unduplicate
		
		//construct an array for posting
		$rec = array(
				 AG_MAIN_OBJECT_DB.'_id'=>$val_id,
				 AG_MAIN_OBJECT_DB.'_id_old'=>$dup_id,
				 'comment'=>$comment,
				 'added_by'=>$UID,
				 'changed_by'=>$UID);
		
		if (undup($rec)) {
			$out .= oline(ucwords($mo_noun)." $dup_id has been marked a duplicate of ".ucwords($mo_noun)." $val_id in the duplication table");
			$out .= oline() . oline(bigger("Unduplicate Another ".ucwords($mo_noun)));
			$out .= undup_form();
			$dups_in_db++;
			$out .= oline()
			      . oline(ucwords($mo_noun)."s marked for duplication, but not unduplicated from DB: " . red($dups_in_db))  
			      . oline(link_unduplication($undup_link_text,"undup"));

		} else {
			$mesg .= oline("Unduplication failed.");
		}
      } else {
		$mesg.=oline('You don\'t have permission to unduplicate '.$mo_noun.'s.');
      }
} elseif($step=="confirm") {
      if($undup_perms) {
	    
	    if (!valid_ids($dup_id,$val_id)) {
		    $out .= undup_form($dup_id,$val_id);
	    } elseif (($dup_id < $val_id) && (!$newer_ok)) {
		    $mesg .= oline("The ID ($dup_id) you have marked as a duplicate is older than the ID ($val_id) you have opted to keep.") 
			    . oline("Are you sure you want destroy the older record?");
		    $out .=  newer_older_form($dup_id,$val_id);
	    } else {
		    $title = "Confirm unduplication";
		    $out .= oline('Below are all the fields that differ between the selected '.$mo_noun.'s.')
			    . oline("Please review these and then confirm at the bottom.");
		    $out .= confirm_undup($dup_id,$val_id,"process");
	    }
      } else {
		$mesg.=oline('You don\'t have permission to unduplicate '.$mo_noun.'s.');
      }
} elseif(!$step) {
      //unduplication entry form
      if ($undup_perms) {
		$out .= undup_form($dup_id,$val_id);
		$out .= oline()
			. oline(gray(ucwords($mo_noun)."s marked for duplication, but not unduplicated from DB: " . $dups_in_db))  
			. oline(link_unduplication($undup_link_text,"undup"));
	} else {
		$mesg.=oline('You don\'t have permission to unduplicate '.$mo_noun.'s.');
	}
}

// output to html and page.
out(bigger(bold(oline($title))));
out(red($mesg));
out($out);
page_close();

?>
