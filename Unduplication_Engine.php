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

//------------------------Process() Explained--------------------------//
//
// This takes the user through a series of steps, in the following order:
//
// 1) new - (or invalid) - an input form is displayed
//
// 2) pre-confirm - for client unduplication only, the user is prompted to verify the
//    unduplication. Records are displayed side by side.
//
// 3) pre-process - a record is added to the appropriate unduplication table
//     for clients only, record is marked as not approved, user is allowed to enter a comment
//     this ends the cycle, and the user is returned to
//     step (1) discribed above
//
// 4) for staff, and DB client unduplication. Records are displayed side-by-side with
//     radio buttons to choose the desired value for the record merge.
//
// 5) process - a) child tables are unduplicated
//              b) merged record is inserted into the valid ID
//              c) duplicate ID is marked as deleted
//
//---------------------------------------------------------------------//

class Unduplication_Engine {

	var $permissions = array('staff'=>'admin',  
					 AG_MAIN_OBJECT_DB=>'admin'); //client undup should probably get their own permission type

	var $control = array('object'=>AG_MAIN_OBJECT_DB, //for now...normally default would be client
				   'step'=>'new',
				   'action'=>'regular'); //other actions include DB unduplication & Backlog unduplication (both for client only)

	var $unduplication_table = array('staff'=>'tbl_duplication_staff',
						   AG_MAIN_OBJECT_DB=>'tbl_duplication');

 	var $special_tables = array( //tables requiring special unduplication action
					    'user_option'=>'', //FIX ME has a unique constraint, not worth fiddling with at the moment (for clients, this won't suffice)
 					    'log'=>'unduplicate_log',
					    'charge'=>'', //charges cannot be changed
					    'staff_password'=>''
 					    );
	var $ignore_fields = array(
					   'staff' => array('username_unix','staff_email'),
					   AG_MAIN_OBJECT_DB=> array()
					   );

	var $title = null;
	var $mesg = array();
	var $errors = array();
	var $output = null;

	function Unduplication_Engine() {

		global $engine;

		$this->control=array_merge($this->control,$_SESSION['undupControl'],$_REQUEST['undupControl']);
		$this->object = $this->control['object'];
		$this->title = ucwords($this->object.' unduplication');
		$this->IDs = orr($_REQUEST['undup_ids'],$_SESSION['undup_ids']);
	}

	function process() {

		if (!$this->perms()) {
			array_push($this->mesg,'You don\'t have proper permissions to unduplicate '.$this->object.' records.');
			return;
		}
		switch ($this->control['step']) {
		case 'preprocess':
			$this->add_record_table(); //adds a record to the appropriate table
			if ($this->object==AG_MAIN_OBJECT_DB) {
				$this->output=$this->form();
				$this->IDs = null;
				break;
			}
		case 'process':
			$this->merged_record = $_REQUEST['merged'];
			$this->undup_db();
			$this->update_record_table(); //for clients, flags the unduplication as approved
			$this->control['step'] = 'new';
			$this->IDs = null;
			$this->output .= $this->form();
			break;
		case 'confirm':
			if ($this->valid_submission()) {
				$_SESSION['undup_ids'] = $this->IDs;
				$this->records = $this->get_records();
				$this->output .= $this->merge_form();
				break;
			} 
		case 'new':
		default:
			$_SESSION['undup_ids']= null; //reset session vars
			$this->output = $this->form();
		}
		$_SESSION['undupControl']=$this->control;
	}

	function display() {

		outline(bigger(bold($this->title)),2);
		$this->send_errors();
		$this->send_mesg();
		out($this->output);
	}

	function perms() {
		return has_perm($this->permissions[$this->object],'W');
	}

	function N_record_form($records,$object) {
		//takes an arbitrary number of records, embedded in an array, and returns a form for merging
		//offers user the option to merge two records field by field
		//returns a form which will return a $merged variable when submitted
		//which contains the merged record
		global $engine;
		
		$def = $engine[$object];
		$count = count($records);
		$i=1;
		if ($count < 1 || !is_array($records)) {
			return false;
		}
		$checked=array();
		$out=array();
		foreach ($records as $rec_name => $rec) {
			foreach ($rec as $key => $value) {
				if ($i==1) { //first time around
					$checked[$key]=true;
					$out[$key]=array();
				}
				$check = !is_null($value) ? $checked[$key] : false;  //if it hasn't been checked yet, check it
				if ($i==$count) { //last time around
					$check=$checked[$key]; //check any that have yet to be checked
				}
				$checked[$key] = $check ? false : $checked[$key];
				array_push($out[$key],cell(formradio('merged['.$key.']',$value,$check)).cell(value_generic($value,$def,$key,'view')));
			}
			$i++;
		}

		$rec_titles = array_keys($records);
		for ($i=0;$i<$count;$i++) {
			$header .= cell(bold($rec_titles[$i]), 'colspan="2"');
		}
		$output .= row(cell().$header);
		foreach ($out as $key => $rec_array) {
			$output .= row(cell(label_generic($key,$def,'view')).implode(' ',$rec_array));
		}
		return $output;
	}

	function valid_submission() {
		$VALID=false;
		if (!empty($this->IDs)) {
			$valid=true;
			foreach ($this->IDs as $which => $id) {
				$tmp[]=$id;
				if (!is_numeric($id) || $id < 1) {
					$valid=false;
					array_push($this->errors,'Please enter a numeric ID for the '.ucfirst($this->object). ' you wish to '.$which);
				} elseif (!call_user_func('is_'.$this->object,$id)) {
					$valid=false;
					array_push($this->errors,ucfirst($this->object). ' ID '.$id.' was not found.');
				} else {
				}
			}
			if ($tmp[0]==$tmp[1]) { //entered the same ID
				array_push($this->errors,'Can\'t unduplicate the same '.$this->object.'!');
				$valid=false;
			}
			$VALID = $valid ? true : false;
			return $VALID;
		}
		array_push($this->errors,'ID numbers are required for the '.$this->object.' you wish to unduplicate.');
		return $VALID;
	}

	function form() {

		global $agency_home_url;
		$cancel_url = $agency_home_url;
		$cancel_button = button_link($cancel_url,'Cancel');

		$form = tablestart('','border="1" cellpadding="3"')
			. formto()
			. rowrlcell('Valid '.ucfirst($this->object).' ID:',formvartext('undup_ids[keep]',$this->IDs['keep']))
			. rowrlcell('Duplicate '.ucfirst($this->object).' ID:',formvartext('undup_ids[unduplicate]',$this->IDs['unduplicate']))
			. hiddenvar('undupControl[step]','confirm')
			. row(cell(button('Submit'))
				. formend()
				. cell($cancel_button))
			. tableend();
		return $form;
	}
	
	function send_errors() {
		foreach ($this->errors as $error) {
			outline(red($error));
		}
		$this->errors=array();
	}

	function send_mesg() {
		foreach ($this->mesg as $message) {
			outline($message);
		}
		$this->mesg=array();
	}

	function get_records() {
		
		global $engine;
		$records = array();
		foreach ($this->IDs as $which => $id) {
			$filter = array($this->object.'_id'=>$id);
			$res = get_generic($filter,'','',$engine[$this->object]);
			$records[$which] = array_shift($res);
			foreach ($this->ignore_fields[$this->object] as $field) {
				unset ($records[$which][$field]);
			}
		}
		return $records;
	}

	function merge_form() {
		
		$keepID = $this->IDs['keep'];
		$undupID = $this->IDs['unduplicate'];

		return 
			tablestart() . formto()  
			. row( cell('photos') 
				 . cell(formradio("undup_photo",$keepID,true)).cell(call_user_func($this->object.'_photo',$keepID)) 
				 . cell(formradio("undup_photo",$undupID,false)).cell(call_user_func($this->object.'_photo',$undupID))) 
			. $this->N_record_form($this->records,$this->object)
			. row(cell(button('Submit'),' rowspan="2"'))
			. hiddenvar('undupControl[step]','preprocess')
			. formend() 					
			. row(cell(cancel_button($_SERVER['PHP_SELF'].'?undupControl[step]=new','Cancel')))
				 . tableend();
	}

	function get_tables() {

		global $engine, $AG_ENGINE_TABLES;

		// create an array of all tables and staff/client fields
		$TABLES = array();
		foreach ($AG_ENGINE_TABLES as $obj) {
			if (in_array($obj,array_keys($this->special_tables))  //these will be handled elsewhere
			    || is_view($engine[$obj]['table_post'])  //don't care about views
			    || $obj == $this->object ){  //the parent table requires special handling
				if (function_exists($this->special_tables[$obj].'_'.$this->object)) { //this should go somewhere else but it works fine here for now.
					call_user_func($this->special_tables[$obj].'_'.$this->object,$this->IDs['unduplicate'],$this->IDs['keep']);
				}
				continue;
			}
			$table = $engine[$obj]['table_post'];
			$TABLES[$table]=array();
			$fields =& orr($engine[$obj]['fields'],array());
			foreach ($fields as $field_name => $info) {
				//there must surely be a better way to do this via array searching... (not as of PHP 4 or 5...)
				$type = $info['data_type'];
				if ($type == $this->object 
				    && !$info['view_field_only']
				    && !in_array($field_name,$this->ignore_fields[$this->object]) ) {
					array_push($TABLES[$table],$field_name);
				}
			}
		}
		return array_filter($TABLES);
	}


	function undup_db()
	{
		global $engine, $UID;
		
		$mo_def=get_def(AG_MAIN_OBJECT_DB);
		$mo_noun=$mo_def['singular'];

		$newid=$this->IDs['keep'];
		$oldid=$this->IDs['unduplicate'];
		
		$tables = $this->get_tables();
		
		outline(bigger(bold('Unduplicating '.$this->object.' '
					  .call_user_func($this->object.'_link',$oldid).' into '
					  .call_user_func($this->object.'_link',$newid))));
		$res=sql_query('BEGIN');
		foreach($tables as $table => $field_list)
		{
			$this->undup_table($table,$field_list);
			$this->send_errors();
			$this->send_mesg();
		}

		//PHOTO STUFF
		if ($this->object == AG_MAIN_OBJECT_DB ) {
// 			$use_old_photo = ($_REQUEST['undup_photo']==$oldid);
			
// 			$photo_res=client_photo_transfer( $newid, $oldid , $use_old_photo);
 			outline($photo_res 
 				  ? "Transfered all photos for ".$mo_noun." $oldid to ".$mo_noun." $newid"
 				  : "Failed to transfer photos for ".$mo_noun." $oldid to ".$mo_noun." $newid");
		} else {
			$use_old_photo = ($_REQUEST['undup_photo']==$oldid);
			$photo_res=$this->staff_photo_transfer($newid,$oldid,$use_old_photo);
		}

		//Mark old ID as deleted
		$table = $engine[$this->object]['table_post'];
		$filter=array($this->object.'_id' => $oldid);
		$result = agency_query(sql_delete($table,$filter,"MARK"));
		if ($result) {
			array_push($this->mesg, ucfirst($this->object)." ID $oldid succesfully marked as deleted in $table.");
		} else {
			array_push($this->errors,"Failed to mark ".$this->object." $oldid as deleted in $table.");
		}
		//set sys log in old record to be deleted
		$sys_log_mesg = ucfirst($this->object)." is a duplicate of $newid --- '||CURRENT_DATE||' by staff ID: $UID\n"; 
		$sys = sql_query("UPDATE $table
                                       SET sys_log = COALESCE(sys_log,'') || '$sys_log_mesg',
                                           deleted_comment='deleted for unduplication of {$this->object} $newid\n'
                                       WHERE {$this->object}_id = $oldid");
		if (!$sys) {
			array_push($this->error,"Failed to update sys_log for {$this->object} $oldid");
		}
		//set syslog in merged record
		$this->merged_record['sys_log'] = $this->merged_record['sys_log']
			."Data for a duplicate {$this->object} ($oldid) was merged into this record --- ".dateof('NOW','SQL')." by staff ID: $UID\n"; 
		//merge records
		$result = agency_query(sql_update($table,$this->merged_record,array($this->object.'_id'=>$newid)));
		if ($result) {
			array_push($this->mesg,"Merged records for {$this->object} ($oldid) and {$this->object} ($newid).");
		} else {
			array_push($this->errors, "Failed to merge records for {$this->object} ($oldid) and {$this->object} ($newid).");
		}
		$res=sql_query('END');
		$this->send_errors();
		$this->send_mesg();
		return;
	}

	function undup_table($table,$field_list) {

		global $UID;
		$keepID = $this->IDs['keep'];
		$undupID = $this->IDs['unduplicate'];

		$message = 'Unduplicating '.$this->object.' ID from '.$undupID.' to '.$keepID;

		foreach ($field_list as $field) {
			$set = "$field = $keepID, sys_log = COALESCE(sys_log,'') || CURRENT_TIMESTAMP ||' - $message by staff ID: $UID\n'";
			$sql = "UPDATE $table SET $set WHERE $field = $undupID";
			$res = sql_query($sql);
			if (!$res) {
				array_push($this->errors,"Unable to update $field in $table:  $update_sql");
			}   
			$rows = sql_affected_rows($res); // doesn't return 0 on error
			if ($rows) {
				$ROWS += $rows;
			}

		}
		if ($ROWS > 0) {
			create_system_log($this->object.' unduplication', //event type 
						"$message --- changed $ROWS rows in table $table");
			array_push($this->mesg,indent()."--- changed $ROWS records in table ".bold($table));
		} else {
			array_push($this->mesg,indent().smaller('--- No rows were changed in table '.$table));
		}
	}

	function add_record_table() { //adds a record to the appropriate table

		global $UID;
		$record=array();
		$mo_def=get_def(AG_MAIN_OBJECT_DB);
		$mo_noun=$mo_def['singular'];
		switch($this->object) {
		case AG_MAIN_OBJECT_DB:
			$record['approved'] = 'false';
		case 'staff':
			$record['approved'] = 'true';
			$record['FIELD:approved_at'] = 'CURRENT_TIMESTAMP';
			$record['approved_by'] = $UID;
		}
		$record[$this->object.'_id']=$this->IDs['keep'];
		$record[$this->object.'_id_old']=$this->IDs['unduplicate'];
		$record['added_by']=$record['changed_by']=$UID;

		$table = $this->unduplication_table[$this->object];
		$res = sql_query(sql_insert($table,$record));
		if (!$res) {
			array_push($this->errors,'Failed to insert a record in the unduplication table ('.$table.').');
		} elseif ($this->object==AG_MAIN_OBJECT_DB) {
			array_push($this->mesg,$mo_noun.' '.$this->IDs['unduplicate'].' flagged for unduplication');
		}
	}

	function update_record_table() { //for clients, flags the unduplication as approved

	}

	function staff_photo_transfer($newid,$oldid,$use_old_photo) {
		global $AG_STAFF_PHOTO_BY_FILE;
		$base = $AG_STAFF_PHOTO_BY_FILE.'/';

		$file = 'st_';
		$ext = '.jpg';

		$old_photo_file = $base.$file.$oldid.$ext;
		$cur_photo_file = $base.$file.$newid.$ext;
		if ($use_old_photo and is_readable($cur_photo_file)) {
			$res0 = rename($old_photo_file,$base.$file.$newid.'.old'.$ext);
			if (!$res0) {
				array_push($this->errors,'Failed to rename old photo.');
			}
			$res = rename($base.$file.$oldid.$ext,$base.$file.$newid.$ext);
		} elseif (is_readable($old_photo_file)) {
			$res = rename($old_photo_file,$base.$file.$newid.'.old'.$ext);
		}
		if (!$res) {
			array_push($this->errors,'Failed to transfer photos.');
		}

	}
}
?>
