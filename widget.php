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

class Widget {

	/*
	 * The Widget currently only works with one-of-each types...expansion should be fairly easy
	 *
	 * The object def array needs to include a 'widget' array at the global level.
	 *
	 * This array must include, at a minimum, the following:
	 *
	 *      * add, edit --> booleans (currently not functional...)
	 *      * style --> one_of_each (currently not functional...)
	 *      * key --> repeater field (eg, permission_type_code, donor_flag_type_code, disability_code)
	 *      * fixed --> the constant between records, can be an array (eg, staff_id, client_id, donor_id)
	 *      * required_fields --> fields that will appear on the form (eg, permission_read,write etc, disability_date)
	 * 
	 * The following are optional
	 *
	 *      * label_position --> defaults to right
	 *      * delete_no_pass_records --> required for record types w/o date fields (eg, permissions)
	 *      * record_passed_eval_code --> an expression that if evaluated to true, indicates an existing record (see config_permission for example)
	 *                    again, this is required for record types w/o date fields
	 *      * required_fields_end --> object_date_end is the default, if it exists. Otherwise, 'end record' fields must be specified in an array
	 *      * optional_fields --> a list of fields hidden initially that a user may fill in (eg,comment for disability)
	 *      * title --> an alternate title (currently not functional...)
	 *
	 */


	var $config,$control,$def,$step;
	var $fixed,$key;
	var $out = array(),$recs = array(),$recs_last = array(), $rec_init = array();
	var $errors = array(),$warnings = array(),$messages = array();
	var $explicit_passed_recs = false, $using_form = false;
	var $required_fields = array(),$required_fields_end = array(), $optional_fields = array(),$additional_field_links = array();

	function Widget(&$control,$def) 
	{
		//we leave the $control processing to Engine
		$this->control =& $control;
		$this->step = $control['step'];
		$this->object = $control['object'];
		$this->def = $def;
		$this->rec_init = $control['rec_init'];
		$this->configure();
		$this->get_keys();
		$this->fixed = $this->config['fixed'];
	}

	function get_engine_output(&$control,$def)
	{
		if (!in_array('widget',array_keys($def))) {
			return array('message'=>'No Widget Configuration for '.$def['object'].' objects. Cannot Proceed.');
		}
		$w = new Widget($control,$def);
		$w->process();
		return $w->out;
	}

	function configure()
	{
		$config = $this->def['widget'];
		$fields = array_keys($this->def['fields']);
		$object = $this->def['object'];
		if (!isset($config['required_fields_end']) and in_array($object.'_date_end',$fields)) {
			$this->required_fields_end = $config['required_fields_end'] = array($object.'_date_end');
		}
		$this->optional_fields = orr($config['optional_fields'],array());
		$this->config = $config;
	}

	function process()
	{
		$this->audit();
		$this->merge_records();
		$this->required_fields();
		$this->title();

		switch ($this->step) {
		case 'confirmed':
			global $AG_AUTH,$UID; //need to check password
			if ($AG_AUTH->reconfirm_password()) {
				//passed password check
				if ($this->out['output'] = $this->post_changes()) {
				} else {
					$this->using_form=true;
					$this->out['output'] = $this->form();
				}
				break;
			} else {
				$this->out['message'] .= 'Incorrect password for '.staff_link($UID);
			}
		case 'confirm':
			if ($this->valid()) {
				$this->using_form=false;
				$this->confirm();
				if ($this->changes_made()) {
					$this->out['output'] = $this->confirm_process_buttons() . $this->view();
				} else {
					$this->out['output'] = $this->view() . $this->initiate_change_buttons();
				}
				break;
			}
		case 'new':
		case 'continued':
		default:
			$this->using_form=true;
			$form =  $this->form(); //this must run prior to get_additional_field_links()
			$this->out['output'] = $form;
		}
		$this->out['menu'] = $this->get_menu();
		return true;
	}

	function audit()
	{
		/*
		 * Verify internal consistency
		 *
		 * Object and rec_init should remain constant
		 */

		if ($old_audit = $_SESSION['widget_old_audit']) {
			$o_object = $old_audit['object'];
			$o_rec_init = $old_audit['rec_init'];
			if ( ($o_object === $this->object) and ($o_rec_init === $this->rec_init) ) {
				return true;
			} else {
				//verbose error handling here
				if ($o_object === $this->object) {
					$this->out['message'] .= 'Primary field ('.$this->fixed[0].') has changed (old: '.$o_rec_init[$this->fixed[0]]
						.', new: '.$this->rec_init[$this->fixed[0]].'). Resetting Form';
				} else {
					$this->out['message'] .= 'Object change detected (old: '.$o_object.', new: '.$this->object.'). Resetting Form.';
				}
			}
		}
		$this->step = 'new';
		$_SESSION['widget_recs'] = $_SESSION['widget_recs_last'] = $_SESSION['widget_old_audit'] = null;

		$_SESSION['widget_old_audit'] = array('object'=>$this->object,
								  'rec_init'=>$this->rec_init);
		return true;
	}

	function merge_records()
	{
		$formrecs = $_REQUEST['rec'];
		$RECS = $_SESSION['widget_recs'];
		$recs_last = $_SESSION['widget_recs_last'];
		if (!isset($this->config['record_passed_eval_code'])) {
			$passed_records = orr($_REQUEST['pass_record'],$_SESSION['widget_passed_records']);
			$this->explicit_passed_recs = true;
		}

		foreach ($this->keys as $val => $label) {

			if ($this->step !== 'new') { //work with form
				$REC = $RECS[$val];
				$rec = $formrecs[$val];
				call_user_func($this->def['fn']['process'],&$REC,&$rec,$this->def);
				$recs[$val] = $REC;
			} else {
				$active_filter = $this->get_active_filter($val);
				if ( ($res = $this->def['fn']['get_active']($active_filter,$rec=array(),$this->def))
				     and (count($res)===1) ) {//record exists
					$recs[$val] = array_shift($res);
					$passed_records[$val] = true;
					$recs_last[$val] = $recs[$val];
				} else {
					$passed_records[$val] = false;
					$recs[$val] = call_user_func($this->def['fn']['blank'],$this->def,$this->rec_init);
				}
			}
			foreach ($this->fixed as $field) {
				if ( be_null($this->rec_init[$field])
				    or (!be_null($recs[$val][$field]) and !($recs[$val][$field] == $this->rec_init[$field])) ) {
					$this->out['message'] .= oline('Bad rec_init passed to Widget');
					return false;
				}
			}
			$recs[$val][$this->key] = $val;
		}
		$_SESSION['widget_passed_records'] = $this->passed_records = $passed_records;
		$_SESSION['widget_recs'] = $this->recs = $recs;
		$_SESSION['widget_recs_last'] = $this->recs_last = $recs_last;
	}

	function post_changes()
	{
		sql_begin();
		if (!$this->valid()) {
			sql_abort();
			return false;
		}

		$SUCCESS = true;
		$returned_recs = array();
		foreach ($this->recs as $key => $rec) {
			
			/*
			 * one of 5 possibilities:
			 *
			 * 0) record collision (doesn't count in '5' total :)
			 * 0a) record collision II
			 * 1) existing record changed, still 'active --> update
			 * 2) existing record changed, no-pass --> end-date ? update : delete
			 * 3) new record --> insert
			 * 4) existing record, no change --> nothing
			 * 5) record not selected at all --> nothing
			 */
			$active_filter = $this->get_active_filter($key);
			$rec_last = $this->recs_last[$key];
			$interim_record = count($this->def['fn']['get_active']($active_filter,$rec=array(),$this->def)); 
			$success = true;
			$errors = $mesg = '';
			if ( ($rec_last and ($old = rec_collision_generic($rec,$rec_last,$this->def,'edit',&$errors)))
			     or (!$rec_last and $interim_record>0)) {
				$this->errors[$key] .= orr($errors,'A record has been added since starting this process');
				$success = false;
			} elseif ($rec_last and rec_changed_generic($rec,$rec_last) and $this->record_passed($rec)) {
				//update
				$filter = array($this->def['id_field']=>$rec[$this->def['id_field']]);
				$success = call_user_func($this->def['fn']['post'],$rec,$this->def,&$mesg,$filter);
				if ($success) {
					$returned_recs[$key] = $success;
					$this->messages[$key] .= $mesg;
				} else {
					$this->errors[$key] .= $mesg;
				}
			} elseif ($rec_last and rec_changed_generic($rec,$rec_last)) {
				/*
				 * Update or Delete?
				 *
				 * Delete will only be allowed if:
				 * a) specifically allowed in widget AND
				 * b) date fields don't exist
				 */
				$filter = array($this->def['id_field']=>$rec[$this->def['id_field']]);
				if ($this->config['delete_no_pass_records'] and !(in_array($this->object.'_date_end',array_keys($this->def['fields'])))) { //delete
					$success = call_user_func($this->def['fn']['delete'],$filter,$this->def,'Widget deletion');
					$mesg = $success ? 'Deleted record' : 'Failed to delete record';
					$success = $success ? $rec : false;
				} else { //update
					$success = call_user_func($this->def['fn']['post'],$rec,$this->def,&$mesg,$filter);
				}
				if ($success) {
					$returned_recs[$key] = $success;
					$this->messages[$key] .= $mesg;
				} else {
					$this->errors[$key] .= $mesg;
				}
			} elseif ($rec_last) {
				//no change, nothing
				$returned_recs[$key] = $rec;
			} elseif ($this->record_passed($rec)) {
				//insert
				$success = call_user_func($this->def['fn']['post'],$rec,$this->def,&$mesg);
				if ($success) {
					$returned_recs[$key] = $success;
					$this->messages[$key] .= $mesg;
				} else {
					$this->errors[$key] .= $mesg;
				}
			} else {
				//nothing
				$returned_recs[$key] = $rec;
			}
			$SUCCESS = $success ? $SUCCESS : $success;
		}

		if ($SUCCESS) {
			sql_end();
			$this->recs = $returned_recs;
			$_SESSION['widget_recs'] = $_SESSION['widget_recs_last'] = $_SESSION['widget_old_audit'] = null;
			return $this->view() . $this->initiate_change_buttons();
		}
		sql_abort();
		return false;
	}

	function required_fields()
	{
		// use recs and def combination to determine what fields are required
		$this->required_fields = $this->config['required_fields'];
	}

	function view()
	{
		$out = tablestart('','class="engineForm"');
		$out .= $this->form_header_row('view');
		foreach ($this->recs as $rec) {
			$out .= $this->view_row($rec);
		}
		$out .= tableend();
		return $out;
	}

	function confirm_process_buttons()
	{
		global $AG_AUTH, $UID;
		$out = formto('','',$AG_AUTH->get_onsubmit(''));
		$out .= 'Enter password for '.staff_link($UID).': '.$AG_AUTH->get_password_field($auto_focus=true);
		$return_button = hlink($_SERVER['PHP_SELF'].'?control[step]=continued','Return to Edit','','class="linkButton"');
		$out .= div(button('Process Changes','','','','','class="engineButton"') . $return_button,'','');
		$out .= hiddenvar('control[step]','confirmed');
		$control = $this->control;
		unset($control['step']);
		$out .= form_encode($control,'control');
		$out .= formend();
		return $out;
	}

	function initiate_change_buttons()
	{
		$this->out['commands'] = array(cell(div(hlink($_SERVER['PHP_SELF'].'?control[step]=new','Make Changes'))));
		return $out;
	}

	function get_additional_field_links()
	{
		if (!be_null($this->additional_field_links)) {
			return span('Additional Fields Available').div(implode(oline(),$this->additional_field_links));
		}
		return false;
	}

	function get_color_key()
	{
		foreach (array('Changed'=>'','NoChange'=>'No Change','New'=>'','None'=>'') as $style=>$label) {
			$css = ' width: 30px; height: 15px; border: solid 1px black; float: left; margin-right: 3px; ';
			$color_key .= div(div('&nbsp;','',' style="'.$css.'" class="widget'.$style.'"').div(orr($label,$style),'')
						,'',' style="clear: both; margin: 3px 0px"');
		}
		return span('Key').div($color_key);
	}

	function get_menu()
	{
		if ($this->using_form) {
			$additional = $this->get_additional_field_links();
		}
		$key  = $this->get_color_key();
		return $key . $additional;
	}


	function form()
	{
		//buttons
		global $engine;
		$cancel_url = call_user_func($this->def['fn']['cancel_url'],$rec,$this->def,'add','control');
		$cancel_button = hlink($cancel_url,$engine['text_options']['cancel_button'],''
					     ,' class="linkButton" onclick="'.call_java_confirm('Are you sure you want cancel?').'"');
		$reset_button = hlink($_SERVER['PHP_SELF'].'?control[step]=new',$engine['text_options']['reset_button'],''
					    ,' class="linkButton" onclick="'.call_java_confirm('Are you sure you want to reset the form?').'"');
		$buttons = div(button($engine['text_options']['submit_button'],'','','','','class="engineButton"') . $reset_button . $cancel_button,'','');
		//end buttons

		$out = formto() . $buttons;
		$out .= tablestart('','class="engineForm"');
		$out .= $this->form_header_row();

		unset($this->control['step']);

		foreach ($this->recs as $rec) {
			$out .= $this->form_row($rec);
		}
		$out .= tableend();

		$out .= $buttons;

		$out .= hiddenvar('control[step]','confirm');
		$out .= form_encode($this->control,'control');
		$out .= formend();
		return $out;
	}

	function form_row($rec) {
		$out = $hidden_vars = '';
		foreach ($rec as $key => $value) {
			if (!be_null($this->required_fields_end) and in_array($key,$this->required_fields) and isset($this->recs_last[$rec[$this->key]])) {
				$out .= cell(value_generic($value,$this->def,$key,'list'));
				$hidden_vars .= hiddenvar('rec['.$rec[$this->key].']['.$key.']',$value);
				
			} elseif (in_array($key,$this->required_fields)) {
				//to form
				$out .= cell(form_field_generic($key,$value,&$this->def,$this->control,&$Java_Engine,'rec['.$rec[$this->key].']'));
			} elseif (isset($this->recs_last[$rec[$this->key]]) and in_array($key,$this->required_fields_end)) {
				$out .= cell(form_field_generic($key,$value,&$this->def,$this->control,&$Java_Engine,'rec['.$rec[$this->key].']'));
			} elseif (in_array($key,$this->required_fields_end)) {
				$out .= cell('');
				$hidden_vars .= hiddenvar('rec['.$rec[$this->key].']['.$key.']',$value);
			} elseif (in_array($key,$this->optional_fields)) {
				$out .= cell(form_field_generic($key,$value,&$this->def,$this->control,&$Java_Engine,'rec['.$rec[$this->key].']')
						 ,'class="widgetOptField'.$key.'" style="display: none;"');
			} else {
				$hidden_vars .= hiddenvar('rec['.$rec[$this->key].']['.$key.']',$value);
				//hidden var
			}
		}

		if ($this->explicit_passed_recs) {
			$pass_check = cell(formcheck('pass_record['.$rec[$this->key].']',$this->passed_records[$rec[$this->key]]));
		}
		$class = $this->determine_record_status($rec);
		$label = $this->keys[$rec[$this->key]];
		$out .= cell( orr($this->get_errors($rec[$this->key]),'&nbsp;') . $hidden_vars); //pass hidden vars in cell
		$out = ($this->config['label_position']=='left')
			? cell($label).$out
			: $out.cell($label);
		return row($pass_check . $out,'class="'.$class.'"');
	}

	function view_row($rec)
	{
		$fields = array_merge($this->required_fields,$this->required_fields_end);
		foreach ($rec as $key => $value) {
			if (in_array($key,$fields)) {
				$out .= cell(value_generic($value,$this->def,$key,'add'));
			}
		}
		$class = $this->determine_record_status($rec);
		$label = $this->keys[$rec[$this->key]];
		$out .= cell(orr($this->get_warnings($rec[$this->key]),$this->get_messages($rec[$this->key])));
		$out = ($this->config['label_position']=='left')
			? cell($label).$out
			: $out.cell($label);
		return row($out,'class="'.$class.'"');
	}

	function determine_record_status($rec)
	{
		$status = $this->record_passed($rec);
		$rec_last = $this->recs_last[$rec[$this->key]];
		if (!be_null($rec_last)) { //existing record
			if (rec_changed_generic($rec,$rec_last)) {
				$class = 'widgetChanged';
			} else {
				$class= 'widgetNoChange';
			}
		} elseif ($status) { //new record
			$class = 'widgetNew';
		} else { //no action
			$class = 'widgetNone';
		}
		return $class;
	}


	function form_header_row($style='')
	{
		$fields = array_merge($this->required_fields,$this->required_fields_end,$this->optional_fields);
		foreach ($fields as $key) {
			if (in_array($key,$this->optional_fields)) {
				$class = 'widgetOptField'.$key;
				$opts = 'class="'.$class.'" style="display: none;"';
				$lab = label_generic($key,$this->def,'list',false);
				$this->additional_field_links[$key] = Java_Engine::toggle_table_cell_display(alt(smaller($lab.' (+/-)',2),'Click to view'),$class);
			}
			$out .=h_cell(label_generic($key,$this->def,'list'),$opts);
		}
		if (!be_null($this->errors)) {
			$lab = 'Errors';
		} elseif (!be_null($this->warnings)) { 
			$lab = 'Warnings';
		} elseif (!be_null($this->messages) and ($style=='view')) { 
			$lab = 'Messages';
		} else {
			$lab = '';
		}
		if ( $this->explicit_passed_recs and !($style == 'view') ) {
			$pass_check = h_cell();
		}
		
		$out = ($this->config['label_position']=='left')
			? h_cell().h_cell($lab).$out
			: $out.h_cell($lab).h_cell();
		return row($pass_check . $out);
	}

	function get_keys()
	{
		$this->key = $this->config['key'];
		$pr = $this->def['fields'][$this->key];
		switch ($pr['data_type']) {
		case 'lookup':
			$query = build_lookup_query($pr,'');
			$res = agency_query($query);
			$recs = sql_fetch_to_array($res);
			foreach ($recs as $tmp) {
				$this->keys[$tmp['value']] = $tmp['label'];
			}
			break;
		default:
			$this->out['message'] .= 'Widget->get_keys() cannot handle type '.$pr['data_type'];
		}
	} 

	function get_active_filter($value)
	{
		$active_filter = array();
		foreach ($this->fixed as $field) {
			$active_filter[$field] = $this->rec_init[$field];
		}
		$active_filter[$this->key] = $value;
		return $active_filter;
	}

	function valid()
	{
		$VALID = true;
		foreach ($this->recs as $key => $rec) {
			$mesg = '';
			$def = $this->def;
			if ($this->record_passed($rec)) {

				foreach ($this->required_fields_end as $field) {
					// here, we require end fields to be blank, since record is still 'passed'
					$ov = $def['fields'][$field]['valid'];
					$def['fields'][$field]['valid'] = array_merge($ov,array('be_null($x)'=>'{$Y} must be blank if leaving record open'));
				}
				$valid = call_user_func($this->def['fn']['valid'],$rec,$def,&$mesg,'add');
				if (!$valid) {
					$this->errors[$key] = $mesg;
				}
			} elseif (!be_null($this->required_fields_end) and isset($this->recs_last[$key])) {
				foreach ($this->required_fields_end as $field) {
					// here, end fields are required, as the existing record is no longer passed
					$def['fields'][$field]['valid'] = array_merge($ov,array('!be_null($x)'=>'{$Y} cannot be blank if closing record'));
				}
				$valid = call_user_func($this->def['fn']['valid'],$rec,$def,&$mesg,'edit');
				if (!$valid) {
					$this->errors[$key] = $mesg;
				}
			} else {
				$valid = true;
			}
			$VALID = $valid ? $VALID : $valid;
		}
		return $VALID;
	}

	function changes_made()
	{
		$changes_made = false;
		foreach ($this->recs as $key => $rec) {
			$rec_last = $this->recs_last[$key];
			if ( ($rec_last and rec_changed_generic($rec,$rec_last)) or (!$rec_last and $this->record_passed($rec)) ) {
				$changes_made = true;
			}
		}
		$this->out['message'] .= $changes_made ? '' : 'No changes made. Nothing to do.';
		return $changes_made;
	}

	function confirm()
	{
		$CONFIRMED = true;
		foreach ($this->recs as $key => $rec) {
			$mesg = '';
 			if ($this->record_passed($rec)) {
				$confirmed = call_user_func($this->def['fn']['confirm'],$rec,$this->def,&$mesg,'add');
				if (!$confirmed) {
					$this->warnings[$key] = $mesg;
				}
				$CONFIRMED = $confirmed ? $CONFIRMED : $confirmed;
 			}
		}
		return $CONFIRMED;
	}
	
	function get_errors($key)
	{
		return $this->errors[$key] ? span(smaller($this->errors[$key]),' class="error"') : false;
	}

	function get_warnings($key)
	{
		return $this->warnings[$key] ? span(smaller($this->warnings[$key]),' class="warning"') : false;
	}

	function get_messages($key)
	{
		return $this->messages[$key] ? span(smaller($this->messages[$key])) : false;
	}

	function record_passed($rec)
	{
		/*
		 * Determine if the record was submitted with intent to create/modify
		 * tricky business, this.
		 *
		 * A user shows 'intent' if:
		 *
		 * a) the 'record_passed_eval_code' is set and evaluates to true
		 *  _or_
		 * b) the checkbox for the record type is selected
		 *
		 */


		if ($eval = $this->config['record_passed_eval_code']) {
			return eval('return '.$eval.';');
		} else {
			return $this->passed_records[$rec[$this->key]];
		}
	}
	
	function title()
	{
		if ($t = $this->config['title']) {
			//process passed title
		} else {
			//attempt to derive a title
			$key = $this->fixed[0];
			if ($t = $this->rec_init[$key]) {
				//$label = label_generic($key,$this->def,'list',false); //no formatting
				$value = value_generic($t,$this->def,$key,'list',false); //no formatting
			}
			$this->out['title'] = head($this->def['plural'].' for '.$value);
		}
	}
}

?>
