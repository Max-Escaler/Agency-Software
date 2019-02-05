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
   *
   * Here's the theory, the practice may be different:
   *
   * 0) Much of this will mimic engine initialization
   *
   * 1) Build a working record template from various required tables and existing records
   *
   * 2) ...
   *
   * 3) Profit 
   */

$quiet = true;
include 'includes.php';

if (!has_perm('clinical_data_entry','RW') && !has_perm('clinical')) {

	agency_top_header();
	out(alert_mark('No permissions for this page'));
	page_close();
	exit;

}

if (!has_perm('clinical_data_entry','RW')) {

	define('AG_CLINICAL_REG_READ_ONLY',true);

} else {

	define('AG_CLINICAL_REG_READ_ONLY',false);

}

require_once SCRIPT_CONFIG_FILE_DIRECTORY.'/script_config_clinical_reg.php';


/****************** Initialization ******************/
$step = $_REQUEST['step'];

if ( ($step !== 'print_update') && AG_CLINICAL_REG_READ_ONLY ) {

	/*
	 * The only available step for read-only is "print_update"; exit if not
	 */

	agency_top_header();
	out(alert_mark('No permissions for this page'));
	page_close();
	exit;

}


/*
 * Session ID is to ensure tabbed-browsing-safe behaviour
 */
$session_id = $_REQUEST['sid'];
if (!$session_id ) {

	$session_id = clinical_reg_set_session_id();

}

if ($_REQUEST['reset']) {

	$_SESSION[$session_id.'_record_template'] = null;
	$_SESSION[$session_id.'_record_working']  = null;
	$_SESSION[$session_id.'_script_config']   = null;

}

$_SESSION['clinical_reg_'.$session_id.'_client_id'] = $client_id = 
	orr($_REQUEST['client_id'],$_SESSION['clinical_reg_'.$session_id.'_client_id']);

if (!$client_id) {

	die(__FILE__.' requires an Client ID');

}



$_SESSION['clinical_reg_'.$session_id.'_type'] = $type = 
	orr($_REQUEST['type'],$_SESSION['clinical_reg_'.$session_id.'_type']);

if (!$type) {

	die(__FILE__.' requires a type to work with');

}

/*
 * Fill in the defaults not explicitly set in the config file
 */
$script_config = clinical_reg_get_complete_config($AG_SCRIPT_CONFIG_CLINICAL_REG[$type],$session_id,$type);

$per_object_errors = array();

$REC = clinical_reg_get_working_record($client_id,$script_config,$session_id);

/****************** End Initialization ******************/


$formto = formto(); //must initialize form for various js-dependant functions

global $AG_AUTH;
if ($step == 'confirmed' and (!$AG_AUTH->reconfirm_password())) {

	/************ Incorrect Password *************/

	$message .= 'Incorrect password for '.staff_link($UID);
	$step = 'submit';

 } elseif ($step == 'confirmed' && clinical_reg_valid_records($client_id,$REC,$script_config,$per_object_errors)) {

	/******************* Posting ******************/
	if ($posted_records = clinical_reg_post_all($REC,$script_config,$client_id,$message)) {

		$out .= div(html_no_print($message),'',' class="message"')
			. clinical_reg_view_records($posted_records,$script_config)
			. clinical_reg_form_signature();

		/*
		 * Wipe out session data so repost won't happend
		 */
		$_SESSION[$session_id.'_record_template'] = null;
		$_SESSION[$session_id.'_record_working']  = null;
		$_SESSION[$session_id.'_script_config']   = null;

	} else {

		/*
		 * Failed post
		 */
		$step = 'continue';

	}

}

if ($step == 'submit' && clinical_reg_valid_records($client_id,$REC,$script_config,$per_object_errors)) {

	/******************************* Preview ****************************/
	$out = div($message,'',' class="error"')
		. alert_mark('Please review the records')
		. clinical_reg_confirm_records($REC,$script_config)
		. clinical_reg_view_records($REC,$script_config)
		. formto('','',$AG_AUTH->get_onsubmit(''))
		. oline(red('Enter password for '.staff_link($GLOBALS['UID']).' to confirm: ')
			  . $AG_AUTH->get_password_field())
		. button('Post','','','','','class="engineButton"')
		. hlink($_SERVER['PHP_SELF'].'?step=continue&sid='.$session_id,'Return to form','','class="linkButton"')
		. hiddenvar('step','confirmed')
		. hiddenvar('sid',$session_id)
		. formend();
	/**************************** End Preview ****************************/

} elseif ($step == 'print_update') {

	$edit_link = (AG_CLINICAL_REG_READ_ONLY 
			  ? '' 
			  : div(html_no_print(hlink($_SERVER['PHP_SELF'].'?step=continue&sid='.$session_id,'Edit Records','','class="linkButton"')))
			  );

	$out = $edit_link
		. div(html_print_only(smaller('Generated on '.today().' by '.staff_name($UID))))
		. clinical_reg_view_records($REC,$script_config)
		. clinical_reg_form_signature()
		. $edit_link;

} elseif (!$step || $step == 'continue' || $step == 'submit' ) {

	/************************** Form Generation **************************/

	$form = clinical_reg_form($REC,$script_config,$per_object_errors);

	if (!be_null(array_values($per_object_errors))) {

		$message .= oline('See errors below');

	}

	$out = div($message,'',' class="error"')
		. $formto . $form
		. hiddenvar('sid',$session_id)
		. hiddenvar('step','submit')
		. button('DO IT!','','','','','class="engineButton"') 
		. hlink($_SERVER['PHP_SELF'].'?reset=1&type='.$type.'&client_id='.$client_id,'Reset','','class="linkButton" onclick="'.call_java_confirm('Reset form?').'"')
		. hlink($client_page.'?id='.$client_id,'Cancel','',' class="linkButton" onclick="'.call_java_confirm('Are you sure you want to cancel and return to the client page?').'"')
		. formend();

	/************************ End Form Generation ************************/
}


/********************* Output *********************/

$style = 'table.multiAddForm input,table.multiAddForm select { font-size: 70%; }
          table.multiAddForm select { width: 120px; }
          table.multiAddForm input[type=text] { width: 50px; }
          div.multiAddForm { margin: 25px; width: 70%; }
          table.clinicalReg, table.multiAddForm { background-color: #b4babd; font-size: 10pt; }
          table.clinicalReg td { border: solid 1px #b4babd; 
                                 padding: 1px;
          }
          table.multiAddForm td { background-color: #fff; }

          .engineLabel { text-align: left; width: 15em; }

          #clinicalRegSignature div { width: 35em; border-bottom: solid 1px black; }
          #clinicalRegSignature td { text-align: right;}
          #clinicalRegSignature { margin-top 55px; font-size: 10pt; font-weight: bold; }

';
$AG_HEAD_TAG .= style($style);

$title = $script_config['title'] . ' for '.client_link($client_id);

agency_top_header();

out(html_heading_2($title));

out($out);

page_close();

/******************** Functions  ************************/

function clinical_reg_grab_record(&$script_config,$object,$client_id)
{

	$conf   = $script_config['objects'][$object];
	$def    = $conf['def'];

	switch ($object) {
	case AG_MAIN_OBJECT_DB :
		$old_rec = $rec = sql_fetch_assoc(client_get($client_id));

		/* 
		 * The old record is stored to later determine if changes have been made
		 */
		$script_config['objects'][$object]['old_rec'] = unset_system_fields_all($old_rec);
		$script_config['objects'][$object]['old_id']  = $client_id;

		break;
	case 'clinical_reg' :

		$cr_order = 'clinical_reg_date DESC, clinical_reg_id DESC';

		if ($conf['type'] == 'view') { //view current record using filter

			$filter = array_merge(client_filter($client_id),$conf['filter']);
			$res = get_generic($filter,$cr_order,'1',$def);

		} else {

			//must have an existing record, because this is an exit
			$res = get_generic(array(AG_MAIN_OBJECT_DB.'_id' => $client_id,
							 'tier_program(benefit_type_code)' => $script_config['benefit_project'],
							 '!IN:kc_authorization_status_code' => array('CX'),
							 'NULL:clinical_exit_reason_code' => ''),
						 $cr_order,'1',$def);

		}

		if (sql_num_rows($res) < 1) {
				
			log_error('Cannot perform a '.$script_config['title']. ' for '.client_link($client_id).' because there is not a current record in '.$def['singular']);
			exit;
				
		}

		$rec = array_merge(sql_fetch_assoc($res),orr($conf['rec_fixed'],array()));

		break;
	case 'clinical_reg_request' :

		if ($script_config['type'] == 'sage_cob') { //cob

/* This used to pull back the record based on highest clinical_reg_id,
   but this didn't work correctly, particularly for some people with 
   PP tiers that were not more recent, but had higher IDs.  So we're
   changing this to pull the record based on clinical_reg_date, and
   hoping it won't break anything.  See bug 24993. 
   We also had to exclude canceled tiers. (bug 25412).
*/

			$res = get_generic(array(AG_MAIN_OBJECT_DB.'_id' => $client_id,
							 'tier_program(benefit_type_code)' => $script_config['benefit_project'],
							 '<>:kc_authorization_status_code' => 'CX'),
						 'clinical_reg_date DESC','1','clinical_reg');

			if (sql_num_rows($res) < 1) {

				log_error('Cannot perform a '.$script_config['title'].' for '.client_link($client_id).' because there is no previous authorization record');
				exit;
			}

			$old_reg = sql_fetch_assoc($res);
			$conf['rec_fixed'] = array_merge($conf['rec_fixed'],array('previous_auth_id' => $old_reg['kc_authorization_id'],
												    'benefit_type_code' => $old_reg['benefit_type_code'],
												    'funding_source_code' => $old_reg['funding_source_code']));
		}
	default :
		$rec_init  = orr($conf['rec_init'],array());
		$rec_fixed = orr($conf['rec_fixed'],array());
		if ($conf['rec_init_from_previous']) {

			/*
			 * Find previous record
			 */
			$filter = is_array($conf['rec_init_from_previous']) ? $conf['rec_init_from_previous'] : array();

			/*
			 * Build order for getting most recent previous record
			 */
			if (in_array($object.'_date',array_keys($def['fields']))) {

				/*
				 * If available, use the date field
				 */
				$order = $object.'_date'.' DESC';

			} else {

				/*
				 * Next best is the primary key field
				 */
				$order = $def['id_field'].' DESC';

			}

			if ($old_rec = sql_fetch_assoc(get_generic(array_merge(client_filter($client_id),$filter),
										 $order,'1',$def))) {

				/*
				 * If a previous record exists, it must be prepared in the same fashion
				 * as the new record, so changes can be detected. This is done by blanking
				 * the system fields, and the primary key (which is stored for checking record
				 * collisions and flagging for upload if necessary).
				 */
				$old_rec = unset_system_fields_all($old_rec);
				$old_id  = $old_rec[$def['id_field']];
				unset($old_rec[$def['id_field']]);

				foreach ($old_rec as $k => $v) {

					if ($k == $conf['group_on_fields'][0]) {

						/*
						 * This is used for fetching multiple records below (for example,
						 * disabilities and diagnoses)
						 */
						$previous_event_date = $v;

					}

					/*
					 * Also unset any fields that are common to the potential new record
					 */
					if (in_array($k,array_keys($rec_init))
					    || in_array($k,array_keys($rec_fixed))
					    || in_array($k,orr($conf['group_on_fields'],array()))
					    || $def['fields'][$k]['view_field_only']
					    || $def['fields'][$k]['virtual_field']
					    ) {

						unset($old_rec[$k]);
					}

				}

				/* 
				 * The old record is stored to later determine if changes have been made
				 */
				$script_config['objects'][$object]['old_rec'] = $old_rec;
				$script_config['objects'][$object]['old_id']  = $old_id;
			}

		}

		if ($conf['many'] > 1) { 

			/*
			 * There might be more than one, it is fetched below
			 */

			$old_rec = array();

		}

		$rec_init = array_merge(client_filter($client_id),$rec_init,$rec_fixed,orr($old_rec,array()));
		$rec = blank_generic($def,$rec_init);
		unset($rec[$def['id_field']]);

	}


	/*
	 * System fields are re-attached during the posting process
	 */
	$rec = unset_system_fields_all($rec);

	foreach ($rec as $k => $v) {

		if ($def['fields'][$k]['view_field_only']
		    || $def['fields'][$k]['virtual_field']) {

			unset($rec[$k]);

		}

	}

	if ($conf['many'] > 1) {

		$old_ids = $REC = array();
		$st_i = 0;

		if ($conf['rec_init_from_previous'] && !be_null($previous_event_date)) {

			/*
			 * Retrieve all previous records grouped by $previous_event_date
			 */

			$res = get_generic(array(AG_MAIN_OBJECT_DB.'_id' => $client_id,
							 $conf['group_on_fields'][0] => $previous_event_date),
						 $def['id_field'].' ASC',$conf['many'],$def);

			while ($a = unset_system_fields_all(sql_fetch_assoc($res))) {

				$old_ids[] = $a[$def['id_field']];
				
				unset($a['export_kc_id'],$a[$def['id_field']]);

				$REC[] = array_merge($rec,$a);

				$st_i ++;
			}
			$script_config['objects'][$object]['old_rec'] = $REC;
			$script_config['objects'][$object]['old_id'] = $old_ids;
		}


		for ($i = $st_i; $i < $conf['many']; $i ++) {

			/*
			 * Continue to fill out $REC with blank template $recs
			 * until the limit $conf['many'] has been reached
			 */
			$REC[] = $rec;

		}

		if ($conf['rec_hidden']) {

			$n_rec = array_merge($rec,$conf['rec_hidden']);
			$REC[] = $n_rec;
			$script_config['objects'][$object]['many'] ++;

		}

		$rec = $REC;

	}

	return $rec;
}

function clinical_reg_get_working_record($client_id,&$script_config,$session_id)
{ 

	if ($template_rec = $_SESSION[$session_id.'_record_template']) {
		
		/*
		 * We have a template, merge with working record and return
		 */
		$working_rec = $_SESSION[$session_id.'_record_working'];

		foreach ($template_rec as $obj => $ini_data) {

			$conf = $script_config['objects'][$obj];
			$def  = $conf['def'];

			$submitted = orr($_REQUEST[$obj.'_rec'],$working_rec[$obj]);

			/*
			 * Try to grab group field (errors will be thrown if it hasn't been filled in yet)
			 */
			if ($obj == $script_config['group_object']) {

				$group_field_value = $submitted[$script_config['group_field']];

			}

			if ($conf['many'] > 1) {

				$new_rec = array();
				foreach ($submitted as $i => $sub_rec) {

					foreach ($sub_rec as $k => $v) {

						/*
						 * Fill in group on field(s)
						 */
						if (in_array($k,orr($conf['group_on_fields'],array()))) {
							$sub_rec[$k] = $group_field_value;
						}

						/*
						 * Fill in common fields from the first record
						 */
						if ($i >0 && in_array($k,orr($conf['common_fields'],array()))) {
							$sub_rec[$k] = $submitted[0][$k];
						}

					}

					process_generic($working_rec[$obj][$i],$sub_rec,$def);

					/*
					 * Complete any grouping fields
					 */

				}

			} else {

					foreach ($submitted as $k => $v) {

						/*
						 * Fill in group on field(s)
						 */
						if (in_array($k,orr($conf['group_on_fields'],array()))) {
							$submitted[$k] = $group_field_value;
						}

					}

				process_generic($working_rec[$obj],$submitted,$def);

			}

		}

		$_SESSION[$session_id.'_record_working'] = $working_rec;

		return $working_rec;
	
	}
	/*
	 * No working record template, make a new one and store it
	 */

	$REC = array();
	foreach ($script_config['objects'] as $obj => $conf) {
		
		$REC[$obj] = clinical_reg_grab_record(&$script_config,$obj,$client_id);
		$script_config['records_grabbed_at'] = datetimeof('now','SQL');
	}


	$_SESSION[$session_id.'_record_working'] = $_SESSION[$session_id.'_record_template'] = $REC;
	$_SESSION[$session_id.'_script_config'] = $script_config;

	global $step;

	if ($step !== 'print_update') { 
		/*
		 * Don't override this step, for all others, reset step progression
		 */
		$step = null;
	}

	return $REC;
}

function clinical_reg_set_session_id()
{

	/*
	 * Generate new session id
	 */

	$sid = 1 + ($_SESSION['clinical_reg_session_counter'] ++);

	return $sid;

}

function clinical_reg_confirm_records($REC,$script_config)
{

	$message = '';

	foreach ($REC as $obj => $rec) {

		$def = $script_config['objects'][$obj]['def'];
		$conf = $script_config['objects'][$obj];

		$action = $conf['update_existing'] ? 'edit' : 'add';

		if ($conf['many'] > 1) {

			foreach ($rec as $sub_rec) {

				confirm_generic($sub_rec,$def,$message,$action,$conf['old_rec']);

			}

		} else {

			confirm_generic($rec,$def,$message,$action,$conf['old_rec']);

		}

	}

	return div($message,'','class="message"');

}

function clinical_reg_valid_records($client_id,$REC,&$script_config,&$per_object_errors)
{

	/*
	 * Get template record
	 */
	$valid = true;

	$group_object = $script_config['group_object'];
	$group_field = $script_config['group_field'];
	$group_object_label = $script_config['objects'][$group_object]['def']['fields'][$script_config['group_field']]['label_add'];

	/*
	 * Verify that the group field is set, if not, return false w/o all other errors
	 */
	if (be_null($REC[$group_object][$group_field])) {

		$per_object_errors[$group_object] .= oline($group_object_label.' is required');
		return false;

	}

	/* 
	 * Verify type/client combination valid 
	 */
	
	if (!clinical_reg_verify_type_client_combination($client_id,$script_config['type'],$REC[$group_object][$group_field],$per_object_errors[$group_object])) {

		return false;
		
	}

	/*
	 * Verify benefit project
	 */
	if (($proj = $script_config['benefit_project'] )
	    && ($req_proj = orr($REC['clinical_reg']['benefit_type_code'],$REC['clinical_reg_request']['benefit_type_code']))
	    && (tier_to_project($req_proj) != $proj)) {

		$per_object_errors[$group_object] .= oline('Invalide benefit type code selected');
		return false;

	}

	foreach ($REC as $obj => $rec) {

		$def = $script_config['objects'][$obj]['def'];
		$conf = $script_config['objects'][$obj];

		if ($conf['type'] == 'view') {

			/*
			 * No changes being made, and won't post, no validity check
			 */
			continue;

		}

		if ($conf['many'] > 1) {

			foreach ($rec as $sub_rec) {

				/*
				 * Check if passed
				 */
				if (clinical_reg_record_passed($sub_rec,$conf['passed_fields'])) {

					$t_v = valid_generic($sub_rec,&$def,&$per_object_errors[$obj],($conf['update_existing'] ? 'edit' : 'add'));
					$valid = $t_v ? $valid : $t_v;

				}

			}

		} else {
			
			/*
			 * Check if passed
			 */
			if (clinical_reg_record_passed($rec,$conf['passed_fields'])) {

				$t_v = valid_generic($rec,&$def,&$per_object_errors[$obj],($conf['update_existing'] ? 'edit' : 'add'));
				$valid = $t_v ? $valid : $t_v;

			}

		}
	
		$func = 'clinical_reg_valid_'.$obj;
		if (function_exists($funcj)) {

			/*
			 * Per object checks, if function exists
			 */
			$valid = $func($rec,$script_config,$per_object_errors[$obj]) ? $valid : false;

		}

		$script_config['objects'][$obj]['def'] = $def;
	}

	return $valid;

}

function clinical_reg_get_complete_config($config,$session_id,$type)
{

	if ($script_config = $_SESSION[$session_id.'_script_config']) {

		return $script_config;

	}

	foreach ($config['objects'] as $object => $o_conf) {

		$config['objects'][$object]['def'] = orr($o_conf['def'],get_def($object));

		/*
		 * Set group field/object
		 */
		if ($group_field = $o_conf['group_field']) {
			
			$config['group_field'] = $group_field;
			$config['group_object'] = $object;

		}

		foreach (orr($config['objects'][$object]['required_fields'],array()) as $field) {

			$config['objects'][$object]['def']['fields'][$field]['null_ok'] = false;

		}

		/* 
		 * Hide client_id
		 */
		if ($config['objects'][$object]['def']['fields'][AG_MAIN_OBJECT_DB.'_id']) {

			$config['objects'][$object]['def']['fields'][AG_MAIN_OBJECT_DB.'_id']['display_add'] = 'hide';

		}

	}

	if (!$config['group_field']) {

		die(__FILE__.' cannot function without a field to group records on.');

	}

	$config['type'] = $type;

	return $config;
}

function clinical_reg_record_passed($rec,$fields)
{

	if (!$fields) {

		/*
		 * If not defined, assume all records must be complete
		 */

		return true;

	}

	foreach ($fields as $key) {

		if (!be_null($rec[$key])) { 

			/*
			 * It only takes one to pass the record
			 */

			return true;

		}

	}

	return false;

}


function clinical_reg_form($REC,$script_config,$per_object_errors)
{

	$control = array('action' => 'add');

	$form = '';

	foreach ($REC as $obj => $rec) {

		$control['object'] = $obj;
		
		$conf = $script_config['objects'][$obj];
		$def  = $conf['def'];
		
		$form .= row(cell('','colspan="2"'))
			. row(centercell(bold($def['singular']),' colspan="2"'));

		$form .= !be_null($per_object_errors[$obj]) ? row(cell($per_object_errors[$obj],'class="error" colspan="2"')) : '';
		
		if ($conf['many'] > 1) {
			
			$i = 0;
			$many_out = $header = '';

			$common_fields = orr($conf['common_fields'],array());

			foreach ($rec as $num => $sub_rec) {
				
				$row = '';
				foreach ($sub_rec as $key => $value) {

					if (in_array($key,orr($conf['group_on_fields'],array()))
					    || in_array($key,array_keys(orr($conf['rec_fixed'],array())))
					    || ($i > 0 && in_array($key,$common_fields))
					    || in_array($key,orr($conf['fixed_fields'],array()))
					    || $def['fields'][$key]['display_add'] == 'hide'
					    || ($conf['rec_hidden'] && $i==($conf['many']-1))) {
				
						/*
						 * Field is hidden
						 */
		
						$hiddens .= hiddenvar($obj.'_rec['.$num.']['.$key.']',$value);
						
					} else {
						
						$header .= $i==0 ? cell(label_generic($key,$def,'add')) : '';
						
						$row .= centercell(form_field_generic($key,$value,$def,$control,$dummy,$obj.'_rec['.$num.']'));
					}
					
				}
				$many_out .= row($row);
				
				$i ++;
			}
			
			$form .= row(cell(table(row($header).$many_out,'',' border="1" class="multiAddForm" cellspacing="0"'),'colspan="2"'));
			
		} else {

			foreach ($rec as $key => $value) {
				
				if ($def['fields'][$key]['display_add'] == 'hide'
				    || in_array($key,orr($conf['hidden_fields'],array()))) {
					$hiddens .= hiddenvar($obj.'_rec['.$key.']',$value);

					/*
					 * Field is hidden
					 */

				} elseif (in_array($key,orr($conf['group_on_fields'],array()))
					    || in_array($key,array_keys(orr($conf['rec_fixed'],array())))
					    || in_array($key,orr($conf['fixed_fields'],array()))
					    || $conf['type'] == 'view') {
					
					/*
					 * Field is read-only
					 */
					$form .= view_generic_row($key,$value,$def,'add',$rec);
					$hiddens .= hiddenvar($obj.'_rec['.$key.']',$value);
					
				} else {

					$form .= form_generic_row($key,$value,&$def,$control,$dummy,$rec,$obj.'_rec');

				}
				
			}

		}
		
	}

	return table($form,'',' class="clinicalReg" cellspacing="0"') . $hiddens;
}

function clinical_reg_view_records($REC,$script_config)
{

	$control = array('action' => 'add');

	$out = '';

	foreach ($REC as $obj => $rec) {

		$control['object'] = $obj;
		
		$conf = $script_config['objects'][$obj];
		$def  = $conf['def'];
		
		$out .= row(cell('','colspan="2"'))
			. row(centercell(bold($def['singular']),' colspan="2"'));

		if ($conf['many'] > 1) {
			
			$i = 0;
			$many_out = $header = '';

			$common_fields = orr($conf['common_fields'],array());

			foreach ($rec as $num => $sub_rec) {

				$sub_rec = unset_system_fields_all($sub_rec);
				unset($sub_rec[AG_MAIN_OBJECT_DB.'_id']);

				$row = '';
				foreach ($sub_rec as $key => $value) {

					if (in_array($key,orr($conf['group_on_fields'],array()))
					    || in_array($key,array_keys(orr($conf['rec_fixed'],array())))
					    || ($conf['rec_hidden'] && $i==($conf['many']-1))) {

						/*
						 * Hidden value
						 */

					} elseif ($i > 0 && in_array($key,$common_fields)) {
						
						/*
						 * Common fields aren't repeated in subsequent rows
						 */

						$row .= centercell('');

					} else {
						
						/*
						 * Normal output. If first row, generate headers
						 */
						$header .= $i==0 ? cell(label_generic($key,$def,'add')) : '';
						
						$row .= centercell(value_generic($value,$def,$key,'add'));
					}
					
				}
				$many_out .= row($row);
				
				$i ++;
			}
			
			$out .= row(cell(table(row($header).$many_out,'',' border="1" class="multiAddForm" cellspacing="0"'),'colspan="2"'));
			
		} else {
			
			$rec = unset_system_fields_all($rec);
			unset($rec[AG_MAIN_OBJECT_DB.'_id']);

			foreach ($rec as $key => $value) {

				if (in_array($key,orr($conf['group_on_fields'],array()))
					    || in_array($key,array_keys(orr($conf['rec_fixed'],array())))
					    || in_array($key,orr($conf['hidden_fields'],array()))) {

					/*
					 * Hidden fields, nothing to do here
					 */

				} else {

					$out .= view_generic_row($key,$value,$def,'add',$rec);

				}

			}
		}
		
	}

	return table($out,'',' class="clinicalReg" cellspacing="0"');
}

function clinical_reg_post_all($REC,$script_config,$client_id,&$message)
{

	global $UID;

	$posted_records = array();

	/*
	 * Post in a transaction.
	 */
	sql_begin();
	foreach ($REC as $obj => $rec) {

		$conf = $script_config['objects'][$obj];
		$def  = $conf['def'];

		if (in_array($conf['type'],array('virtual','view'))) {

			$posted_records[$obj] = $rec;
			continue;

		}

		if ($conf['many'] > 1) { /*** multiple record processing ***/

			/*
			 * Record changes are harder to detect here, so for now we continue in the same manner as SHAMIS
			 * this is where changes should be detected, since it's an all or nothing posting
			 */

			if (clinical_reg_records_changed_multiple($rec,$conf)) {

				/*
				 * Records changed, or new records
				 */

				foreach ($rec as $sub_rec) {
					
					if (clinical_reg_record_passed($sub_rec,$conf['passed_fields'])) {

						/*
						 * Add system fields and main_object_id
						 */
						$sub_rec[AG_MAIN_OBJECT_DB.'_id'] = $client_id;
						$sub_rec['added_by'] = $sub_rec['changed_by'] = $UID;

						/*
						 * Check for editing collision
						 */

						if (clinical_reg_record_collision($obj,$def,$conf,$script_config['records_grabbed_at'],$message)) {

							sql_abort();
							return false;

						}

						$posted_rec = post_generic($sub_rec,$def,$message);
						if (!$posted_rec) {

							sql_abort();
							log_error(__FUNCTION__.' failed posting '.$obj.' for client '.$client_id);
							return false;
						
						}

						$posted_records[$obj][] = $posted_rec;

						/*
						 * Flag kc transmission
						 */
						if (clinical_reg_send_to_kc($obj,$script_config,$REC,$client_id)) {
						
							if (!flag_kc_transmission($obj,$posted_rec[$def['id_field']],$message,$script_config,$REC)) {
								
								sql_abort();
								return false;
							
							}
						
						}

					}

		 		}

			} elseif ($conf['old_rec'] && clinical_reg_send_to_kc($obj,$script_config,$REC,$client_id)) { 

				/*
				 * Existing record, needs to be flagged for KC transmission
				 */

				$flag_date = clinical_reg_get_kc_flag_date($obj,$script_config,$REC);

				foreach ($conf['old_id'] as $old_id) {

					if (!flag_kc_transmission($obj,$old_id,$message,$script_config,$REC,$flag_date)) {

						sql_abort();
						return false;

					}

				}

				$message .= oline($def['plural'].' not posted, but flagged for KC upload');
				$posted_records[$obj] = $conf['old_rec'];

			} else {

				$message .= oline($def['plural'].' not posted or flagged for KC upload');
				$posted_records[$obj] = $conf['old_rec'];

			}  /*** end multiple record processing ***/

		} else {

			$old_rec = $conf['old_rec'];

			if (clinical_reg_record_passed($rec,$conf['passed_fields'])) {

				if (!$old_rec || clinical_reg_rec_changed($rec,$old_rec,$conf)) {
					
					/* 
					 * No previous record, or a change was made, so records are posted
					 */
					
					/*
					 * Add system fields and main_object_id
					 */
					$rec[AG_MAIN_OBJECT_DB.'_id']         = $client_id;
					$rec['changed_by'] = $UID;
					
					/*
					 * See if insert or update
					 */
					if ($conf['update_existing'] && !be_null($rec[$def['id_field']])) {
						
						$filter = array($def['id_field'] => $rec[$def['id_field']]);
						
					} else {
						
						$filter = null;
						$rec['added_by'] = $UID;
						
					}
					
					/*
					 * Check for editing collision
					 */

					if (clinical_reg_record_collision($obj,$def,$conf,$script_config['records_grabbed_at'],$message)) {
						
						sql_abort();
						return false;

					}

					$posted_rec = post_generic($rec,$def,$message,$filter);
					if (!$posted_rec) {
						
						sql_abort();
						log_error(__FUNCTION__.' failed posting '.$obj.' for client '.$client_id);
						return false;
						
					}
					$posted_records[$obj] = $posted_rec;
					
					/*
					 * Flag kc transmission
					 */
					if (clinical_reg_send_to_kc($obj,$script_config,$REC,$client_id)) {
						
						if (!flag_kc_transmission($obj,$posted_rec[$def['id_field']],$message,$script_config,$REC)) {
							
							sql_abort();
							return false;
							
						}
						
					}
					
				} elseif ($old_rec && clinical_reg_send_to_kc($obj,$script_config,$REC,$client_id)) {

					/*
					 * No changes, old record needs to be transmitted to KC
					 */
					
					$flag_date = clinical_reg_get_kc_flag_date($obj,$script_config,$REC);

					if (!flag_kc_transmission($obj,$conf['old_id'],$message,$script_config,$REC,$flag_date)) {
						
						sql_abort();
						return false;
						
					}
					
					$message .= oline($def['singular'].' not posted, but flagged for KC upload');
					$posted_rec = $rec;
					
				} else {
					
					/*
					 * No changes
					 */
					$message .= oline($def['singular'].' not posted or flagged for KC upload');
					$posted_rec = $rec;
					
				}
				
				$posted_records[$obj] = $posted_rec;
			}

		}

		/*************************** Old Record Cleanup *****************************/
		if ($conf['close_previous'] == 'on_change' && !be_null($old_rec) && clinical_reg_rec_changed($rec,$old_rec,$conf)) {

			if (!$c_res = clinical_reg_close_previous($rec,$old_rec,$conf,$message)) {

				sql_abort();
				return false;

			}

		}
	}

	/*
	 * Post Demographic if registration
	 */
	if (clinical_reg_send_to_kc(AG_MAIN_OBJECT_DB,$script_config,$REC,$client_id) 
	    && !flag_kc_transmission(AG_MAIN_OBJECT_DB,$client_id,$message,$script_config,$REC,$REC['clinical_reg_request']['assessment_date'])) {

		sql_abort();
		return false;

	}

	sql_end();
	return $posted_records;
}

function flag_kc_transmission($object,$id,&$message,$script_config,$records,$date=null)
{

	global $UID;

	if (be_null($script_config['objects'][$object]['group_on_fields'])) {

		/*
		 * Need some sort of date for posting, and if records aren't tied
		 * to the registration/update/exit via a group on field, we use 
		 * that date.
		 */

		$date = orr($date,
				$records['update']['update_date'],
				$records['clinical_reg']['clinical_reg_date_end'],
				$records['clinical_reg_request']['assessment_date']);

	}

	$flag_rec = array('object_type' => $object,
				'object_id'   => $id,
				'event_date'  => $date,
				'added_by'    => $UID,
				'changed_by'  => $UID);

	if (!agency_query(sql_insert('tbl_export_kc_transaction',$flag_rec))) {

		$message .= oline('Failed to flag '.$object.' id '.$id.' for transmission to KC');
		return false;

	}

	return true;
}

function clinical_reg_valid_diagnosis($records,$script_config,&$mesg)
{

	/*
	 * A series of valid diagnosis records requires a primary treatment
	 * focus (only one), and a GAF score (if SAGE)
	 */

	$valid = true;

	$diag_gaf = false;
	$diag_primary = 0;
	
	foreach ($records as $sub_rec) {

		$diag_gaf = !be_null($sub_rec['gaf_score']) ? true : $diag_gaf;

		if (sql_true($sub_rec['is_primary_treatment_focus'])) {

			$diag_primary ++;

		}

	}

	if (in_array($script_config['type'],array('sage','sage_exit')) && !$diag_gaf) {

		$mesg .= oline('Must have a GAF score');
		$valid = false;

	}

	if (in_array($script_config['type'],array('sage','sage_exit')) && $diag_primary !== 1) {

		$mesg .= oline('Must have only 1 primary treatment focus');
		$valid = false;

	}
	
	return $valid;
}

function clinical_reg_valid_disability_clinical($records,$script_config,&$mesg)
{
	$valid = true;

	$none_selected = false;
	$disab_count = 0;

	foreach ($records as $sub_rec) {

		$none_selected = ($sub_rec['disability_clinical_code'] == '10') ? true : $none_selected;

		if (!be_null($sub_rec['disability_clinical_code'])) {

			$disab_count ++;

		}

	}

	if ($disab_count > 1 && $none_selected) {

		$mesg .= oline('Cannot have any disabilities if "None" is selected');
		$valid = false;

	}

	return $valid;

}

function clinical_reg_valid_clinical_reg_request($rec,$script_config,&$mesg)
{

	$valid = true;

	if (!be_null($rec['benefit_type_code']) && $script_config['type'] == 'sage' && tier_to_project($rec['benefit_type_code']) != 'SAGE') {

		$valid = false;
		$mesg .= oline($rec['benefit_type_code'].' is not a valid SAGE benefit');

	}

	return $valid;

}			

function clinical_reg_send_to_kc($object,$script_config,$records,$client_id)
{

	/*
	 * First, check funding source. If not KC, then nothing is sent
	 */
	$funding_source = orr($records['clinical_reg']['funding_source_code'],
				    $records['clinical_reg_request']['funding_source_code']);

	if (!$funding_source) {
		if (sql_true(call_sql_function('export_kc_data_for_client',$client_id,enquote1($records[$script_config['group_object']][$script_config['group_field']])))) {
			$funding_source = 'KC';
		} else {
			return false;
		}
	}

	if ($funding_source != 'KC') {

		return false;

	}


	switch ($object) {

	case AG_MAIN_OBJECT_DB :

		/*
		 * This is sent for initial assessments only (on change is handled by a DB trigger)
		 */

		if (in_array($records['clinical_reg_request']['benefit_change_code'],
				 array('INITIAL','VC_NONE','VC_OTHER'))) {

			return true;

		}

		break;
	default :
		if ($script_config['objects'][$object]['send_to_kc'] === 'on_change_only') {

			if ($script_config['objects'][$object]['many'] > 1) {

				return clinical_reg_records_changed_multiple($records[$object],$script_config['objects'][$object]);

			} else {

				$rec = $records[$object];
				$old_rec = $script_config['objects'][$object]['old_rec'];
				return clinical_reg_rec_changed($old_rec,$rec,$script_config['objects'][$object]);

			}

		}

		return $script_config['objects'][$object]['send_to_kc'];

	}

	return false;

}

function clinical_reg_rec_changed($old_rec,$rec,$conf)
{

	/*
	 * Unset group_on_fields
	 */
	foreach (array_keys($conf['def']['fields']) as $k) {

		if (in_array($k,array_keys(orr($conf['rec_init'],array())))
		    || in_array($k,array_keys(orr($conf['rec_fixed'],array())))
		    || in_array($k,orr($conf['group_on_fields'],array()))) {
			
			unset($rec[$k],$old_rec[$k]);
		}
	}

	return rec_changed_generic($old_rec,$rec,$conf['def']);

}

function clinical_reg_records_changed_multiple($records,$config)
{

	/*
	 * Detecting changes to multiple records is somewhat dicey.
	 * This attempts to do so my comparing them in order. If a user were
	 * to enter the same data in a different order, this would count that as 
	 * a change. This isn't all-together a bad thing though, since certain
	 * data types, clinical disabilities for example, are sent to the county
	 * in the order entered, and the county actually places some significance on 
	 * that ordering.
	 */

	if (!$old_records = $config['old_rec']) {

		return true;

	}
	
	/*
	 * Must filter records to only those passed
	 */
	$new_records = array();

	foreach ($records as $rec) {

		if (clinical_reg_record_passed($rec,$config['passed_fields'])) {

			$new_records[] = $rec;

		}

	}


	if (count($new_records) !== count($old_records)) {

		return true;

	}

	$changed = false;

	foreach ($new_records as $i => $rec) {

		$changed = clinical_reg_rec_changed($old_records[$i],$rec,$config) ? true : $changed;

	}

	return $changed;

}

function clinical_reg_close_previous($rec,$old_rec,$conf,&$mesg)
{

	$def = $conf['def'];

	$object     = $def['object'];
	$table_post = $def['table_post'];

	$start_date_field = orr($def['active_date_field'],$object . '_date');
	$end_date_field   = orr($def['active_date_end_field'],$object . '_date_end');

	if ($start_date_field && $end_date_field) {

		global $UID;

		$new_old_rec = array();

		$new_old_rec[$end_date_field] = prev_day($rec[$start_date_field]);
		$new_old_rec['sys_log'] = sys_log_append($old_rec['sys_log'],'Automatically closing previous record');
		$new_old_rec['changed_by'] = $UID;
		$new_old_rec['FIELD:changed_at'] = 'CURRENT_TIMESTAMP';
			
		$filter = array($def['id_field'] => $conf['old_id']);

		$u_res = agency_query(sql_update($table_post,$new_old_rec,$filter));

		if (!$u_res) {

			$mesg .= oline(__FUNCTION__ . ' couldn\'t update '.$table_post);
			return false;

		}

		$mesg .= oline('Closed previous '.$def['singular'].' ID '.$conf['old_id']);

	} else {

		$mesg .= oline(__FUNCTION__ . ' couldn\'t find start or end date fields for '.$object);
		return false;

	}

	return $new_old_rec;

}

function clinical_reg_form_signature()
{
	return html_print_only(oline()
				     . table(
						 row(cell('Case Manager Signature:').cell(div('&nbsp;')))
						 . row(cell('Date:').cell(div('&nbsp')))
						 ,'','id="clinicalRegSignature"'));

}

function clinical_reg_get_kc_flag_date($obj,$script_config,$REC)
{

	return $script_config['objects']['clinical_reg']['type'] == 'view' 
		? null 
		: orr($REC['clinical_reg_request']['assessment_date'], // for registrations, use assessment date
			$REC['clinical_reg']['clinical_reg_date_end']); // for exits, use exit date, else, record date

}

function clinical_reg_record_collision($obj,$def,$conf,$records_grabbed_at,&$message)
{

	/*
	 * records_grabbed_at is set when all records are first pulled from the
	 * db. This function checks for any existing records that have changed
	 * since that time.
	 */

	if (!$old_id  = $conf['old_id']) {


		/*
		 * No previous record, no collision
		 */
		return false;

	}

	if (!is_array($old_id)) {

		/*
		 * Form an array so that multi-records can be checked in the same
		 * manner as singular records
		 */
		$old_id = array($old_id);

	}

	foreach ($old_id as $id) {

		$filter = array($def['id_field'] => $id,
				    '>:changed_at' => $records_grabbed_at);

		$res = get_generic($filter,'','',$def);

		if (sql_num_rows($res) > 0) {

			/*
			 * Collision found
			 */

			$rec = sql_fetch_assoc($res);

			$message .= oline('Fatal Error: Record collision detected for '.$def['singular'].' ID '.$id.'. See '
						. Revision_History::link_history($def['object'],$rec[$def['id_field']],'Revision History').' for details.');

			return true;

		}

	}

	return false;

}

?>
