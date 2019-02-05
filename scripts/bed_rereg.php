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

/* Reset Bednight Registration for the day.
*  All Bednights are archived.
*  Clients with Priority are automatically re-registered
*  for the same bed, if they stayed overnight
*/

// runs from the command line now...no easy way to do authentication
$MODE='TEXT';
$off = dirname(__FILE__).'/../';
include_once $off.'command_line_includes.php';
$archive_count = 0;

// get beds for re-registration
function get_beds($grp)
{
    global $bedtemp_table;
    $res = agency_query("SELECT * FROM $bedtemp_table", array("bed_group" => $grp));
    return $res;
}

function bed_rereg_auto($id)
{
	//check to see if shelter_reg record explicitly states 'yes' for rereg
	global $engine;

	//get shelter registration
	$pdef=$engine['shelter_reg'];
	$pfilt = array('client_id'=>$id,
			   array('NULL:shelter_reg_date_end'=>true,
				   '>=:shelter_reg_date_end'=>dateof('now','SQL')));
	$res = agency_query($pdef['sel_sql'],$pfilt);
	if (sql_num_rows($res)<1) {
		return false;
	}
	$a = sql_fetch_assoc($res);
	$re_reg = $a['bed_rereg_code'];

	switch ($re_reg) {
	case 'ASSESS_YES':
		if (assessment_of($id) !== '-1') {  //has assessment
			$result=false;
			break;
		}
	case 'YES':
		$result=true;
		break;
	case 'NO':
	case 'ASSESS_NO':
	case 'DEFAULT':
	default:
		$result = false;
	}
	return $result;
}

// find un-registered clients beds
function get_unregs($group)
{
    global $bedreg_table;
    $filt["~:client"] = "[^0-9]";
    $filt["NULL:removed_at"] = "";
    $filt["bed_group"] = $group;    
    $res = agency_query("SELECT * from $bedreg_table",$filt);
    return $res;
}

// create a temp table for re-registration and archiving
function temp_beds($group, $start, $end)
{
    global $bedreg_table, $sys_user, $bedtemp_table;

    // put beds in a temp table and determine night factor
    $sel = "SELECT *, date(NULL) AS bed_date, 
        set_nightfactor(added_at, removed_at, '$start', '$end') AS night_factor
        INTO TEMPORARY $bedtemp_table 
        FROM $bedreg_table";
    $filt["bed_group"] = $group;
    $filt['<:added_at'] = dateof('now','SQL') .' ' . $end.':00';
    $filt["!~:client"] = "[^0-9]"; // no unregistered clients
    $res = agency_query($sel, $filt);
    // set bed_date...client who came in late (after midnight needs yesterday
    // as bed_date
    $bdval["FIELD:bed_date"] = 
        "CASE WHEN date(added_at) = date(CURRENT_TIMESTAMP) THEN
        date(added_at - interval '1 day') ELSE date(added_at) END";
    $bdupdate = "UPDATE $bedtemp_table SET " . read_filter($bdval);
    $bdfilt = array(">:night_factor" => 0);
    $res = agency_query($bdupdate, $bdfilt);
    
    return $res;
}

// archive "raw" data from bed_reg (daily worksheet) into archive table
function arc_bed_raw($group, $night_end)
{
    global $bedreghist_table, $bedreg_table;
    $sql = "INSERT INTO $bedreghist_table
        (bed_reg_id, bed_group, bed_no, client, vol_status, comments, added_by,
            added_at, removed_by, removed_at, re_register, sys_log, bus_am, bus_pm)
        SELECT * FROM $bedreg_table";
    $filt = array("bed_group" => $group); 
    $filt['<:added_at'] = dateof('now','SQL') .' ' . $night_end.':00';
    $res=agency_query($sql, $filt);
    return $res;
}

// merge two records for the same client on the same night--should have been
// a transfer.  The new_bed is not archived.
function merge_beds($orig_bed, $new_bed)
{
    // $orig_bed is the record already in the bed table
    // $new_bed is the bed that would be archived
    // Update $orig_bed instead of inserting $new_bed
    
    // Add night_factors (nf) together (not to exceed 1.00)
    // Removed_at, removed_by, bed_no come from new_bed
    // message to sys_log
    global $beds_table,$beds_table_post;
    outline(bold("Merging bed records for the same night and client.  
        <BR>Orig bed is ") . dump_array($orig_bed) .
        bold("<BR>new bed is ") . dump_array($new_bed));

    $vals = array("FIELD:night_factor" => 
       "numeric_smaller({$orig_bed['night_factor']}
        + {$new_bed['night_factor']},1.00)",
        "FIELD:removed_at" => ($new_bed["removed_at"] 
            ? "'{$new_bed['removed_at']}'" : "NULL"),
        "FIELD:removed_by" => ($new_bed["removed_by"] 
            ? "'{$new_bed['removed_by']}'" : "NULL"),
        "bed_no" => $new_bed["bed_no"],
	    "changed_by" => $new_bed["added_by"],  //added this for tbl_bed - JH
        // using concatenator '.' so that sys_log gets message on one line
        "FIELD:sys_log" => "COALESCE(sys_log,'') || 'Merged records(' 
            || current_timestamp(0) || ') " 
            . "client is {$new_bed['client']}, " 
            . "orig. bed was {$orig_bed['bed_no']}, "
            . "orig. night_factor was {$orig_bed['night_factor']}, "
            . "new night_factor was {$new_bed['night_factor']};\n'");
    $filt = array("bed_id" => $orig_bed["bed_id"]);
    if (! agency_query("UPDATE $beds_table_post SET " . read_filter($vals,","),$filt))
    {
        log_error("Unable to merge beds.  Table:  $beds_table_post.  original bed "
             . dump_array($orig_bed) . " new bed " . dump_array($new_bed));
        return false;
    }
    return true;
}


// insert bednight into valid bed table (if it's not already in there)
function arc_bednights($bed)
{
    global $bedtemp_table, $beds_table, $beds_table_post, $archive_count;
    $filt["bed_group"] = $bed["bed_group"];
    $filt["bed_no"] = $bed["bed_no"];
    $filt["added_at"] = $bed["added_at"];
    $filt["client_id"] = $bed["client"];

    $sel_res = agency_query("SELECT * FROM $beds_table",$filt);        
    // is it already in the archive table? -- no dups
    if (sql_num_rows($sel_res) == 0)
    {
        $tran_filt["client_id"] = $bed["client"];
        $tran_filt["FIELD:bed_date"] = "date('{$bed['bed_date']}')"; 
        $sel_res = agency_query("SELECT * FROM $beds_table",$tran_filt);        
        // is the client already archived for another bed on the same night?
        if(sql_num_rows($sel_res) == 0)
        {
            // add everything to archive except bed_reg_id client is client_id
            unset($bed["bed_reg_id"]);
            $bed["client_id"] = $bed["client"];
            unset ($bed["client"]);
		unset ($bed['bus_am']);
		unset ($bed['bus_pm']);
			 
	    $bed["changed_by"] = $bed["added_by"]; //changed for tbl_bed - JH
            $res = sql_query(sql_insert($beds_table_post, $bed))
                   or log_error("Unable to archive valid bed nights: ".sql_insert($beds_table_post, $bed));
            $count = sql_affected_rows($res);
            $archive_count = $archive_count + $count;
            // TESTING 
            outline(bold("Has Bed Date...Archived:  $archive_count"));    
        }
        else
        {
            outline(bold("Found match for the same night:  merging in archive"));
            $first_bed = sql_fetch_assoc($sel_res);
            merge_beds($first_bed,$bed);
        }
    }
    else
    {
        outline(bold("Already in bed table.  Will Not Archive."));
    }
    return $res;    
}

// delete old bed history in bed_reg
function delete_beds($fil)
{    
    global $bedreg_table;
    $query = sql_delete($bedreg_table, $fil);
    $res = sql_query($query) or
        log_error("Unable to delete history records:  $query");
    return $res;
}

// insert a new record for the assigned bed; $bed is array (one record from temp table)
function set_bed($bed, $start_time)
{    
    global $bedreg_table, $sys_user;
    $bed["added_at"] = datetimeof();
    $bed["added_by"] = $sys_user;
    $bed["removed_at"] = "";
    $bed["removed_by"] = "";
    $bed["re_register"] = "f";
    unset($bed["night_factor"]);  // doesn't exist in bed_reg
    unset($bed["bed_date"]);    // ditto
    unset($bed["bed_reg_id"]);  // get a new ID
    unset($bed['comments']);    //comments don't carry over
    $query = sql_insert($bedreg_table, $bed);
    $res = sql_query($query) or
        log_error("Unable to register bed:  $query");
    return $res;
}

// set the record $bed as an historical record
function set_history_bed($bed, $end_time)
{
    global $bedreg_table, $sys_user;
    $bedhist["removed_at"] = datetimeof();
    $bedhist["removed_by"] = $sys_user;
    $bedhist['comments'] = ''; // comments don't carry over
    $bed_id = array("bed_reg_id" => $bed["bed_reg_id"]);
    $updquery = sql_update($bedreg_table,$bedhist, $bed_id);
    $res = sql_query($updquery) or
        log_error("Unable to set bed history:  $updquery");
    return $res;    
}

//$query_display = "Y";

// MAIN
// do a little testing first
$bed_group = $_SERVER["argv"][1]; // run from the command line
$bed_group = isset($_REQUEST["bed_group"]) ? $_REQUEST["bed_group"] : $bed_group;
if (empty($bed_group))
{
    outline("A bed_group is required to run this script");
    exit;
}
else
{
    // only valid bed_groups will be processed
    foreach($bed_groups as $group)
    {
        if ($group == $bed_group)
        {
            $valid_group=true;
            break;
        }
    }
    if (! $valid_group)
    {
        outline("$bed_group is invalid");
        exit;
    }
}

$grp = $bed_group_prefix . $bed_group;
$bedgrp = ${$grp}["code"];  //see array in agency_config.php
$auto_rereg = ${$grp}["rereg_all"];
$night_start = ${$grp}["night_startsat"];
$night_end = ${$grp}["night_endsat"]; 
$flag_name=$nobedreg . $bedgrp;
$priority_type = ($bed_group == "kerner") ? "KSH" : "OPEN";

// remove all for no_archive flag
if ($auto_rereg === 'NO_ARCHIVE') {

	$res = delete_beds(array('bed_group'=>$bedgrp));

	if (!$res) {
		log_error('Unable to delete all beds from '.$bedgrp);
	}
	page_close($silent=true);
	exit;
}

// if unregistered clients in beds stop and set flag
$unreg = get_unregs($bed_group);
if (sql_num_rows($unreg))
{
    outline("Clients need to be registered in order to count their bednights.");
    outline(display_recs($unreg));
    outline("Bednight Re-registration will not run until all clients are
            registered.  Go to " . hlink("bed_reg.php","Bed Registration"));
    $tdate = datetimeof();

    set_sys_flag($flag_name);
    return;  //end of script; can't save unregistered clients' bednights
}
else
{
    set_sys_flag($flag_name,"false");  //reset flag
}

// select all bednights into 'temp' table
outline(hrule() . bigger("REREG BEDS FOR $bed_group BEDS TO TEMP"));
if (! temp_beds($bed_group,$night_start, $night_end))
{
    outline(bold("Failed to create temp table...stopping re-registration."));
    log_error("bed_rereg failed for group $bed_group");
    exit;
}

// archive bed_reg data with out tampering
outline(hrule() . bigger("ALL BED_REG TO ARCHIVE"));
arc_bed_raw($bed_group, $night_end);

outline(hrule() . bigger("DELETE OLD HISTORY FROM BED_REG"));
//deletes all beds which were added before this morning.
$this_morning = dateof("now","SQL") . " $night_end:00";
$filt["<:added_at"] = $this_morning;
$filt["bed_group"] = $bed_group;
$ret = delete_beds($filt);

outline(hrule() . bold("This group has auto_rereg = $auto_rereg and night_end 
    $night_end"));

$beds = get_beds($bedgrp);
while($bed = sql_fetch_assoc($beds))
{
    set_time_limit(45); //avoid time outs
    outline(dump_array($bed)); // this is too much...cut down on display
    $clientid = $bed["client"];
    $priority = has_priority($clientid, $priority_type);  // KSH or OPEN
    $will_rereg = bed_rereg_auto($clientid);

    // Re-register & archive only valid bed records
    if (($bed["night_factor"] == 1 || sql_true($bed["re_register"])
        || $priority || $auto_rereg=="Y") && ($bed["removed_at"] == ""))
    {    
        // valid bed...try to re-register
        $bednum = $bed["bed_no"];
        // some bed groups auto re-register everyone
        if ($auto_rereg=="N")  // set in agency_config
        {
		  // verify any priority (not just open) or manually set to re-register
		  // As of 12/15/03, men in group B do not get re-registered
		  //if (has_priority($clientid) || sql_true($bed["re_register"]))
		  //removing group A/B checking from everything but the main shelter - jh 12/29/05
		  if ( ((has_priority($clientid) 
			   and (!in_array($bed_group,array('mens','womens')) //not in the main shelter
				  or (assessment_group_of($clientid) != 'Group B'))))
			 or $will_rereg 
			 or sql_true($bed["re_register"]))
		  {
                outline(bold("Priority Client:  $clientid or re_register = " 
                    . $bed["re_register"]));
                set_bed($bed, $night_end);  //insert new record for bed/client
            }
			else
            {
                outline(bold("No priority for client $clientid in bed $bednum"));
            }
        }
        else
        {            
            outline("auto_rereg on: set_bed for $bednum with for client $clientid");
            set_bed($bed, $night_end); //all clients automatically re-registered
        }
    }
    else
    {
        outline(bold("Not a valid bed_night (probably historical record), will 
            not re-register $clientid in bed $bednum added_at " 
            . datetimeof($bed["added_at"]) 
            . " and removed_at --" . datetimeof($bed["removed_at"])
            . "-- with re_register set to " . sql_true($bed["re_register"])
            . " and priority of $priority"));
    }  

    // night_factor must be more than zero  to store in bed table
    if ($bed["night_factor"] > 0)
    {
        $arc = arc_bednights($bed);
    }
    else
    {
        outline(bold("No bed_date:  will not store in bed."));
    }

    // create a history record in daily worksheet
    if (! ($bed["removed_at"] || $bed["removed_by"]) )
    {   
	    set_history_bed($bed, $night_end);
    }
    outline(""); // for better display
}
outline(hrule() . bigger("ARCHIVED BEDS WITH BED DATES"));
outline("Archived $archive_count rows.");
outline(hlink("bed_reg.php", "Start Bednight Registration"));

page_close($silent=true);
?>
