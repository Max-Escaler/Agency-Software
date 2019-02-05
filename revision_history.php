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
 * Future work on this class includes:
 *
 *   1) Ability to 'page' through history - important as history grows - as it 
 *       is now, the entire history displays in one large grid
 *
 *   2) Advanced text diffing capability (adds/removes highlighted in savy color scheme)
 *
 *   3) ...
 *
 */

class Revision_History {

	/* private variables */
	var $id;
	var $def;
	var $sql = '';
	var $query_order = 'trigger_id DESC';
	var $filter = array('trigger_tuple'=>'old');
	var $hide_fields = array('changed_by','changed_at','trigger_mode','trigger_tuple','trigger_changed','trigger_id');

	function Revision_History($object,$id) {
		$this->id = $id;
		$this->def = get_def($object);

		if (be_null($this->def)) {
			if (has_perm('any_table') && $this->def = config_undefined_object($object)) {
				//on the fly definition
			} else {
				$this->out->add_error('No Object passed to Revision_History. Stopping.');
				return;
			}
		}

		/*
		 * The method below provides a more optimated WHERE clause
		 * than the simpler form:
		 * 	$this->filter[$this->def['id_field']] = $id;
		 */
		$this->filter = array_merge(array($this->def['id_field'] => $id), $this->filter);

		$this->table = $this->def['table_post'].REVISION_HISTORY_TABLE_SUFFIX; 
		$this->sql = 'SELECT * FROM '.$this->table;
		$this->out = new Output();
		$this->append_hide_fields();
		if (be_null($this->id)) {
			$this->out->add_error('No ID passed to Revision_History. Stopping.');
			return;
		}
	}

	/* public functions */
	function display($object,$id) {
		$rh = new Revision_History($object,$id);
		$rh->process();
		return $rh->out->get_formatted();
	}

	function link_history($object,$id)
	{
		global $revision_history_url;

		$def = get_def($object);
		$table = $def['table_post'].REVISION_HISTORY_TABLE_SUFFIX;

		if (!Revision_History::permission($object,$id)) {

			return alt(dead_link('Revision History'),'No revision history permissions for this record.');

		} elseif (Revision_History::has_history($table)) {  //object has revision history available

			//now check individual record
			$id_field = $def['id_field'];
			$res = sql_fetch_column(sql_query("SELECT COUNT(*) FROM {$table} WHERE {$id_field}='{$id}' AND trigger_tuple='old'"),'count');
			$count = $res[0];
			if ($count>0) {
				return hlink($revision_history_url.'?id='.$id.'&object='.$object,'Revision History ('.($count+1).')');
			} else {
				return alt(dead_link('Revision History (0)'),'Not enough revisions to view');
			}

		} else {

			return alt(dead_link('No Revision History Available'),'This type ('.$object.') of record does not have change logging enabled.');

		}
	}
	
	function has_history($table) {
		if (is_table($table)) {
			return true;
		}
		return false;
	}

	function permission($object,$id) {
		//verify record is not protected
		if (is_protected_generic($object,$id)) {
			return false;
		}

		// using 'view' perms for now

		$control = array('object'=>$object,'id'=>$id,'action'=>'view');
		return engine_perm($control,'R');
	}

	/* private functions */
	function process() {
		$this->out->title(ucfirst($this->def['singular']).' ID '.$this->id.' Revision History');
		//even before first, check perms
		if (!$this->permission($this->def['object'],$this->id)) {
			$this->out->add_error('You don\'t have permission for this specific revision history.');
			return;
		}
		if (is_protected_generic($this->def['object'],$this->id)) {
			// adding protected functionality - failsafe, should never get here
			$this->out->add_error('This record is protected.');
			return;
		}
		// first, determine if history exists
		if (!$this->has_history($this->table)) {
			$this->out->add_error('No Revision History Available for '.ucfirst($this->def['plural']).'.');
			return;
		}
		$this->get();
		$this->out->add_body($this->link_current());
		//adding js hide functionality
		$this->out->add_body(Java_Engine::toggle_table_row_display(alt(smaller(' (+/-)',2),'Toggle only fields w/ changes'),'rowWithChange'));
		$this->format();
	}

	function link_current() {
		return link_engine(array('object'=>$this->def['object'],'id'=>$this->id),'current version');
	}

	function get() {

		//this will get all trigger_tuple=old records, while the most recent is fetched later
		$this->res = agency_query($this->sql,$this->filter,$this->query_order);
		//for now, no offsets, just all records on 1 page
		$this->count = sql_num_rows($this->res);
	}

	function get_latest($old_recs) {

		$filter = $this->filter;
		$filter['>:trigger_id'] = $old_recs[0]['trigger_id'];
		$filter['trigger_tuple'] = 'new';

		$res = agency_query($this->sql,$filter,'',1);
		return sql_to_php_generic(sql_fetch_assoc($res),$this->def);
	}

	function format() {
		$revs = array();

		while ($a = sql_to_php_generic(sql_fetch_assoc($this->res),$this->def)) {
			array_push($revs,$a);
		}

		//get most recent rendition
		array_unshift($revs,$this->get_latest($revs));

		$tmp_count = count($revs);
		$latest_rec = $revs[0];
		for ($x=0; $x<$tmp_count; $x++) {
			$rec = $revs[$x];
 			$next_rec = orr($revs[$x+1],$rec);
			$changed_at = ($x+1)==$tmp_count 
				? orr($rec['added_at'],$next_rec['trigger_changed']) //on last record use added_at, if field exists
				: $next_rec['trigger_changed'];
			$changed_by = $rec['changed_by'];

			foreach($rec as $key=>$value) {
				if (!in_array($key,$this->hide_fields)) {
					if ($rec[$key] === $next_rec[$key]) {
						$do_formatting = $this->def['fields'][$key]['data_type'] == 'lookup_multi' ? true : false;
						$big_diffs[$key][$x] = cell(value_generic($value,$this->def,$key,'list',$do_formatting),' class="revision_history_same"');
					} else {
						$big_diffs[$key][$x] = cell($this->diff($value,$next_rec[$key],$key),' class="revision_history_diff"');
						$rows_containing_changes[$key]=true;
					}
				}
			}
			
			//---- header cells ----//
			$out .= cell(oline(datetimeof($changed_at,'US','TWO'))
					 . staff_link($changed_by)
					 ,' class="revision_history_header_col"');
		}
		
		$out = row(cell('Changed at / by',' class="revision_history_header_row"').$out);
		foreach(array_keys($latest_rec) as $key) {
			if (!in_array($key,$this->hide_fields)) {
				$tmp = cell(label_generic($key,$this->def,'list'),' class="revision_history_header_row"');
				$tmp .= implode("\n",$big_diffs[$key]);
				$out .= (!isset($rows_containing_changes[$key])) ? row($tmp,' class=rowWithChange') : row($tmp);
			}
		}

		$this->out->add_body(table($out,'',' class="revision_history"'));
	}

	function diff($new,$old,$key) {
		$type = $this->def['fields'][$key]['data_type'];
		$do_formatting = false;
		switch ($type) {
		case 'lookup_multi':
			$do_formatting = true; //implodes array with line break
		default:
			return value_generic($new,$this->def,$key,'list',$do_formatting); //value w/o formatting
		}
	}

	function append_hide_fields() {
		//security measure to hide fields that shouldn't be viewed
		if (has_perm('super_user')) { return; }
		foreach ($this->def['fields'] as $field=>$config) {
			if ($config['never_to_form'] || ($config['display_view']=='hide')) {
				array_push($this->hide_fields,$field);
			}
		}
	}
}

?>