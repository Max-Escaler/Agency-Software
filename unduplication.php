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

//unduplication related functions

function undup_form($dup_id="",$val_id="")
{
      global $agency_home_url;
      $cancel_url = $agency_home_url;
      $cancel_text = "Cancel";
      $cancel_button = cancel_button($cancel_url,$cancel_text);

	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];

      $form = tablestart("","border=1 cellpadding=3")
	    . formto()
		. rowrlcell('Duplicate '.ucwords($mo_noun).' ID:',formvartext("dup_id",$dup_id))
		. rowrlcell('Valid '.ucwords($mo_noun).' ID:',formvartext("val_id",$val_id))
	    . hiddenvar("step","confirm")
	    . row(cell(button("Submit"))
		  . formend()
		  . cell($cancel_button))
	    . tableend();

      return $form;

}

function newer_older_form($dup_id,$val_id,$stepto="confirm",$steptofalse="")
{
      $out_form =tablestart_blank() 
	    . rowrlcell(
			( formto()
			  . hiddenvar("dup_id",$dup_id)
			  . hiddenvar("val_id",$val_id)
			  . hiddenvar("step",$stepto)
			  . hiddenvar("newer_ok",true)
			  . button("Yes")
			  . formend() ), 
			( formto()
			  .hiddenvar("dup_id",$dup_id)
			  . hiddenvar("val_id",$val_id)
			  . hiddenvar("step",$steptofalse)
			  . hiddenvar("approved",false)
			  . button("No")
			  . formend() ) )
	    . tableend();
      return $out_form;
}

function valid_ids($dup_id,$val_id)
{
      global $undups_table, $newid_lbl, $oldid_lbl, $mesg;
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      $valid=true;
      // a few validity checks
      $ids=array(
		 "Valid"=>$val_id,
		 "Duplicate"=>$dup_id);
      foreach($ids as $label=>$id)
      {
	    if (!$id)
	    {
		  $valid=false;
		  $mesg.=oline("A $label ID must be specified.");
	    }
	    elseif (!is_numeric($id) || (intval($id)<>$id) )
	    {
		  $valid=false;
		  $mesg.=oline("$id is not a $mo_noun ID");
	    }
	    elseif (!is_client($id)) 
	    {
		  $valid=false;
		  $mesg.=oline("Couldn't find $mo_noun $id in the database.");
	    }
      }
      if ($dup_id==$val_id)
      {
	    $valid=false;
	    $mesg.=oline("Duplicate ID ($dup_id) cannot be the same as Valid ID ($val_id).");
      }
      if (!$valid) return $valid;

      // have these clients already been marked?
      $filters=array( 
		     array(
			   $newid_lbl=>$val_id,
			   $oldid_lbl=>$dup_id),
		     array(
			   $newid_lbl=>$dup_id,
			   $oldid_lbl=>$val_id));
      foreach($filters as $filter)
      {
	    $sql = 'SELECT * FROM ' . $undups_table;
	    $marked=agency_query($sql,$filter);
	    $rec=sql_fetch_assoc($marked);
	    if ($rec)
		  {
			$valid=false;
			$staff=staff_link($rec["added_by"]);
			$unduplicated=$rec["approved"];
			$newid=$filter[$newid_lbl];
			$oldid=$filter[$oldid_lbl];
			$mesg.=oline(ucwords($mo_noun)." $oldid has already been marked a duplicate of ".ucwords($mo_noun)." $newid by $staff.");
			$mesg.= oline( $unduplicated ? "The records were merged in the database on "
				       . dateof($rec["approved_at"]) . " by " . staff_link($rec["approved_by"])
				       : "The records have not been merged in the database.");
		  }
      }
      return $valid;
}

function undup($rec)
{
      // insert a record into $undups_table
      global $undups_table,$out,$mesg;
      $result = agency_query(sql_insert($undups_table,$rec));
      if (!$result)
	    {
		  $mesg .= oline("Your attempt to post a record to the $undups_table table failed.");
		  return false;
	    }
      return true;
}

function undup_table($table, $id_field, $keep_id, $remove_id, $silent="")
{
      global $UID, $script, $tdate, $event_type, $unique_constraint_tables;    

    $log_message = "$script:  Unduplicated $remove_id to $keep_id";    
    if (array_key_exists($table,$unique_constraint_tables))
    {
	    $result = agency_query("SELECT * FROM $table",array($id_field=>$keep_id));
	    $new_has_these=array();
	    while ($a=sql_fetch_assoc($result))
	    { //find records that new client already has
		    array_push($new_has_these,$a[$unique_constraint_tables[$table]]);
	    }
	    $result = agency_query("SELECT * FROM $table",array($id_field=>$remove_id));
	    while ($a=sql_fetch_assoc($result))
	    {
		    foreach($a as $key=>$value)
		    {
			    //unset NULLs
			    if (be_null($value))
			    {
				    unset($a[$key]);
			    }
		    }
		    if (in_array($a[$unique_constraint_tables[$table]],$new_has_these))
		    { //must delete record, rather than change id
			    $del=agency_query(sql_delete($table,$a,"MARK"));
			    $update=agency_query(sql_update($table,array("FIELD:sys_log"=>"COALESCE(sys_log,'') || " 
										     . enquote1(addslashes($log_message . "\n")) ),
								    array($id_field=>$remove_id,
									    "is_deleted"=>"t")));
			    $deleted++;
		    }
		    else
		    {
			    $update_rec=array($id_field=>$keep_id,
						    "FIELD:changed_at"=>"current_timestamp",
						    "changed_by"=>$UID,
						    "sys_log"=>$log_message);
			    
			    $update=agency_query(sql_update($table,$update_rec,$a));
			    $updated++;
		    }
	    }
	    $rows=$deleted+$updated;
	    if ($rows)
	    {
		    $deleted ?  outline("Marked $deleted records as deleted in table $table.") : "";
		    $updated ? outline("Updated $updated records in table $table.") : "";
	    }
	    else
	    {
		    ($silent) ? '' : outline("No rows were changed in " . bold($table));
	    }
    }
    else
    {
	  $set_fields = "$id_field = $keep_id,sys_log = COALESCE(sys_log,'') || '$tdate - $log_message by staff ID:  $UID\n'";
	  if (is_field($table,"changed_by") && is_field($table,"changed_at"))
	  {
		$set_fields .= ", changed_at=current_timestamp,changed_by=$UID ";
	  }
	  
	  $update_sql = "UPDATE $table SET $set_fields WHERE $id_field = $remove_id";
	  $update_result = sql_query($update_sql) 
		or sql_warn("Unable to update $table:  $update_sql");       
	  $rows = sql_affected_rows($update_result) // doesn't return 0 on error
		or ($silent) ? '' : outline("No rows were changed in " . bold($table));
    }

    if ($rows)
    {
        create_system_log($event_type, 
			  "$log_message --- changed $rows rows in table $table");
        // display results on screen; users feel better when they see results
        outline($log_message . " --- changed $rows records in table " . bold($table));
    }
}

function undup_db($undup_rec,$merged_record,$photo_to_keep_id)
{
      global $clientid_lbl,$newid_lbl,
	    $oldid_lbl,$script,$tdate, $out, $mesg, 
	    $UID,$client_table_post, $engine;

	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      
      $newid=$undup_rec[$newid_lbl];
      $oldid=$undup_rec[$oldid_lbl];
      
      global $unduplication_table_list;
      $tables = $unduplication_table_list;
	foreach($engine as $tab=>$def) {
		if (isset($def['fields'][$clientid_lbl]) 
		    && is_table($def['table_post']) 
		    && $def['allow_edit'] 
		    && ($tab !== AG_MAIN_OBJECT_DB)
		    && !$def['fields'][$clientid_lbl]['view_field_only']) {
			$tables[$def['table_post']]='';
		}
	}

      outline(bold(red("Merging duplicate ID $oldid into valid ID $newid")));
      if ($newid != 0)
	    {   
		  foreach($tables as $table => $field)
		  {
			undup_table($table, ($field ? $field : $clientid_lbl),$newid,$oldid);
		  }

		  //PHOTO STUFF
		  
		  if ($GLOBALS['AG_CLIENT_PHOTO_BY_FILE']) {
			  $use_old_photo=($photo_to_keep_id==$oldid);
			  $photo_res = client_photo_transfer( $newid, $oldid , $use_old_photo);
			  outline($photo_res 
				    ? "Transfered all photos for $mo_noun $oldid to $mo_noun $newid"
				    : "Failed to transfer photos for $mo_noun $oldid to $mo_noun $newid");
		  }

		  //remove old client from the $client_table_post (mark deleted)

		  $filter=array($clientid_lbl => $oldid);
		  $result = agency_query(sql_delete($client_table_post,$filter,"MARK"));
		  outline($result 
			    ? ucwords($mo_noun)." ID $oldid succesfully marked as deleted in $client_table_post."
				 : "Failed to mark $mo_noun $oldid as deleted in $client_table_post.");
		  $sys_log_mesg = ucwords($mo_noun)." is a duplicate of ".$mo_noun." $newid --- $tdate, by staff ID: $UID\n"; 
		  $sys = sql_query("UPDATE $client_table_post 
                                       SET sys_log = COALESCE(sys_log,'') || '$sys_log_mesg',
                                           deleted_comment='deleted for unduplication of $mo_noun $newid\n'
                                       WHERE ".AG_MAIN_OBJECT_DB."_id = $oldid");
		  $sys_log_mesg = "Data for a duplicate $mo_noun ($oldid) was merged into this record --- $tdate, by staff ID: $UID\n"; 
		  $sys = sql_query("UPDATE $client_table_post 
                                       SET sys_log = COALESCE(sys_log,'') || '$sys_log_mesg'
                                       WHERE ".AG_MAIN_OBJECT_DB."_id = $newid");
		  if (!$sys)
		  {
			outline("Failed to update sys_log for $mo_noun $newid");
		  }
		  $result = agency_query(sql_update($client_table_post,$merged_record,array($clientid_lbl=>$newid)));
		  outline($result ? "Merged records for $mo_noun ($oldid) and $mo_noun ($newid) into $mo_noun $newid"
			  : "Failed to merge records for $mo_noun ($oldid) and $mo_noun ($newid) into $mo_noun $newid");
	    }
      else
	    {
		  $zero_message = "New ID is zero.  No unduplication for old ID $oldid";
		  $mesg .= oline($zero_message);
		  return false;
	    }
      return true;
}

function undup_overlooked()
{
      //   This function shouldn't ever be needed, but I figured I would write it 
      //just in case, while I was thinking of it.
      //   The idea is to unduplicate clients out of tables where they may exist, but for
      //one reason or another, were late to make it to the $unduplication_table_list.
      //   It should only be called from the AGENCY Admin page.

      $starttime=getmicrotime();
      global $unduplication_table_list, $client_table_post,$mesg, 
	    $undups_table, $clientid_lbl, $newid_lbl, $oldid_lbl, $engine;
      $tables = $unduplication_table_list;
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
	foreach($engine as $tab=>$def) {
		if (isset($def['fields'][$clientid_lbl]) && is_table($def['table_post']) && $def['allow_edit'] && ($tab !== AG_MAIN_OBJECT_DB)) {
			$tables[$def['table_post']]='';
		}
	}

      $sql = "SELECT * FROM $undups_table WHERE approved='t'";
      $result=agency_query($sql);
      while($rec=sql_fetch_assoc($result))
      {
	    set_time_limit(45);
	    $val_id=$rec[$newid_lbl];
	    $dup_id=$rec[$oldid_lbl];
	    $silent=true;
	    outline("Checking tables for old $mo_noun ($dup_id) to replace with new $mo_noun $val_id");
	    foreach($tables as $table=>$field)
	    {
		  undup_table($table, ($field ? $field : $clientid_lbl),$val_id,$dup_id,$silent);
	    }
	    //remove old client from the $client_table_post (mark deleted)
	    // In the future, we can add steps to merge two records, field by field.
	    //$filter=array($clientid_lbl => $dup_id);
	    //$check = agency_query(sql_delete($client_table_post,$filter,"MARK"));
	    //outline(red($check
	    //	    ? "Client ID $dup_id succesfully marked as deleted in $client_table_post."
	    //		: "Failed to mark client $oldid as deleted in $client_table_post."));
	    
      }
      if (!$result)
      {
	    $mesg.=oline("No ".$mo_noun."s have been overlooked, and no unduplication has taken place.");
	    return false;
      }
      $endtime=getmicrotime();
      outline('Execution time: '.$endtime-$starttime);
}

function two_client_diff_display($cid1,$cid2)
{
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      if ($cid1==$cid2)
	    {
		  return oline("This is the same ".$mo_noun.".") && false;
	    }
      if (is_client($cid1))
	    {
		  $client_1 = sql_fetch_assoc(client_get($cid1));
	    }
      else 
	    {
		    $badmesg .= oline(ucwords($mo_noun)." $cid1 cannot be found.");
	    }
      if (is_client($cid2))
	    {
		  $client_2 = sql_fetch_assoc(client_get($cid2));
	    }
      else 
	    {
		    $badmesg .= oline(ucwords($mo_noun)." $cid2 cannot be found.");
	    }
      if ($badmesg) return $badmesg && false;
      $twoclients = tablestart("","border=1 cellpadding=3");
      $twoclients .= row(cell() . cell(client_link($cid1)) . cell(client_link($cid2)));

	if ($GLOBALS['AG_CLIENT_PHOTO_BY_FILE']) {
		$twoclients .= row(cell() . cell(client_photo($cid1)) . cell(client_photo($cid2)));
	}

      $def=$GLOBALS['engine'][AG_MAIN_OBJECT_DB];
      foreach ($client_1 as $key=>$value)
      {
	    $client_2_value = $client_2[$key];
	    if ($client_2_value <> $value)
		  {
			$twoclients .= row(cell($key) 
					   . cell(value_generic($value,$def,$key,'view')) 
					   . cell(value_generic($client_2_value,$def,$key,'view'))
					   );
		  }
      }
      $twoclients .= tableend();
      return $twoclients;
}

function confirm_undup($dup_id,$val_id,$stepto)
{
      global $comment,$mesg;
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      if ( (!is_client($dup_id)) || (!is_client($val_id)) )
      {
	    $mesg .= oline("Bad ".AG_MAIN_OBJECT_DB."_id information in table for $val_id & $dup_id.");
	    $out .= formto()
		  . hiddenvar("approved",false)
		  . hiddenvar("dup_id",$dup_id)
		  . hiddenvar("val_id",$val_id)
		  . hiddenvar("step",$stepto)
		  . button("Proceed to next record")
		  . formend();
	    return $out;
      }
      $out .= oline(bold("The $mo_noun on the left will be unduplicated into valid $mo_noun on the right."));
      $out .= two_client_diff_display($dup_id,$val_id);
      $out .= tablestart_blank() 
	    . formto()
	    . hiddenvar("dup_id",$dup_id)
	    . hiddenvar("val_id",$val_id)
	    . hiddenvar("step",$stepto)
	    . hiddenvar("approved",true)
	    . rowrlcell("Comments:",formtextarea( "comment",$comment,"",40,5 ))
	    . rowrlcell( button("Confirm") . formend() ,
			 (
			  formto()
			  . hiddenvar("step","")
			  . button("Cancel")
			  . formend())
			 );
      return $out;
}

function confirm_undup_db($dup_id,$val_id,$stepto)
{
      global $comment,$mesg;
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      if ( (!is_client($dup_id)) || (!is_client($val_id)) )
      {
	    $mesg .= oline("Bad ".AG_MAIN_OBJECT_DB."_id information in table for $val_id & $dup_id.");
	    $out .= formto()
		  . hiddenvar("approved",false)
		  . hiddenvar("dup_id",$dup_id)
		  . hiddenvar("val_id",$val_id)
		  . hiddenvar("step",$stepto)
		  . button("Proceed to next record")
		  . formend();
	    return $out;
      }
      $out .= oline(bold("The two $mo_noun records will be merged as follows."))
	    . para("To change the merge, simply check the box next to the field you wish to keep, the other will be discarded");
      $out .= formto() . merge_two_records($dup_id,$val_id);
      $out .= tablestart_blank() 
	    . hiddenvar("dup_id",$dup_id)
	    . hiddenvar("val_id",$val_id)
	    . hiddenvar("step",$stepto)
	    . hiddenvar("approved",true)
	    . rowrlcell("Comments:",formtextarea( "comment",$comment,"",40,5 ))
	    . rowrlcell( button("Confirm") . formend() ,
			 ( 
			  formto()
			  . hiddenvar("dup_id",$dup_id)
			  . hiddenvar("val_id",$val_id)
			  . hiddenvar("step",$stepto)
			  . hiddenvar("approved",false)
			  . button("Don't unduplicate")
			  . formend() )
			 );
      return $out;
}

function make_rec($dup_id,$val_id,$comment,$approved)
{
      global $clientid_lbl,$newid_lbl,
	    $oldid_lbl, $comment_lbl,$UID;
      $is_approved=$approved ? sql_true() : sql_false();
      $undup_rec = array($newid_lbl=>$val_id,
			 $oldid_lbl=>$dup_id,
			 $comment_lbl=>$comment,
			 "approved_by"=>$UID,
			 "changed_by"=>$UID,
			 "approved"=>$is_approved,
			 "FIELD:approved_at"=>"CURRENT_TIMESTAMP(0)",
			 "FIELD:changed_at"=>"CURRENT_TIMESTAMP(0)");
      return $undup_rec;
}

function can_undup()
{
      global $UID;
      return has_perm("admin","RW");  //can add to duplication table
}

function can_undup_db()
{
      global $UID;
      return has_perm("admin","RW");
}

function merge_two_records($oldid,$newid)
{
      global $client_table_post;
      //attempt to merge...
      $sql="SELECT * FROM $client_table_post";
      $old_rec=sql_fetch_assoc(agency_query($sql,array(AG_MAIN_OBJECT_DB.'_id'=>$oldid)));
      $new_rec=sql_fetch_assoc(agency_query($sql,array(AG_MAIN_OBJECT_DB.'_id'=>$newid)));

	if ($GLOBALS['AG_CLIENT_PHOTO_BY_FILE']) {
		$photo_stuff = photo_merge($oldid,$newid);
	}
      return tablestart("","border=1 cellpadding=3") . $photo_stuff . record_merge($old_rec,$new_rec) . tableend();
;
}

function photo_merge($cid1,$cid2)
{
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
      $photo_2 = has_photo($cid2) ? formradio("photo",$cid2,true) : ""; 
      $check_photo_1 = $photo_2 ? false : true;
      $photo_1 = has_photo($cid1) ? formradio("photo",$cid1,$check_photo_1) : ""; 
      $out=row(cell(bold(($mo_noun)) . cell() . cell(client_link($cid1)) . cell() . cell(client_link($cid2))))
	    . row(cell() . cell($photo_1) . cell(client_photo($cid1)) . cell($photo_2) . cell(client_photo($cid2)));
      return $out;
}

function record_merge($record_two,$record_one)
{
      //offers user the option to merge two records field by field
      //returns a form which will return a $merged variable when submitted
      //which contains the merged record
      $def=$GLOBALS['engine'][AG_MAIN_OBJECT_DB];
      foreach($record_one as $key=>$value_one)
      {
	    $value_two=$record_two[$key];
	    if ( ($value_one<>$value_two) && $key<>AG_MAIN_OBJECT_DB.'_id')
	    {
		  $check_1 = !is_null($value_one) ? true : false;
		  $check_2 = $check_1 ? false : true;
		  $radio_one = formradio("merged[$key]",$value_one,$check_1);
		  $radio_two = formradio("merged[$key]",$value_two,$check_2);
		  $out .= row(cell(bold($key)) 
			      . cell($radio_two) . cell(value_generic($value_two,$def,$key,'view')) 
			      . cell($radio_one) . cell(value_generic($value_one,$def,$key,'view'))
			      );
	    }
	    elseif($key==AG_MAIN_OBJECT_DB.'_id')
	    {
		  $out .= row(cell(bold($key) . cell() . cell(strike($value_two)) . cell() . cell(bold($value_one))));
	    }
	    else
	    {
		  $out .= hiddenvar("merged[$key]",$value_one);
	    }
      }
      return $out;
}
?>
