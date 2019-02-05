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

// FIRST, A FEW REALLY BASIC CONFIGURATION OPTIONS

$engine['text_options'] = array(
				'submit_button'=>'Submit',
				'cancel_button'=>'Cancel',
				'reset_button'=>'Reset',
				'edit_text' => 'Edit this Record',
				'clone_text' => 'Clone this Record',
				'delete_text' => 'Delete this Record',
				'void_text'=>'Void this record',
				'view_text'=> 'View/Edit Data Record',
				'add_another' => 'Post Record and Add Another',
				'post' => 'Post Record',
				'required_fields' => smaller(italic('('.red('*').') denotes required values'))
				);

//PICK SOMETHING THAT WON'T SHOW UP
$engine['no_record_flag']='5c22e4e1cf08b4a7862bff8079a1c53d';

$engine['actions']=array('add' => 'write',
			 'edit' => 'write',
			 'delete' => 'write',
			 'void' => 'write',
			 'view' => 'read',
			 'list' => 'read',
			 'download' => 'read');

$engine['control_pass_elements'] = array('object','action','id');

$engine['control_array_elements'] = array(
					  'action'=>'',
					  'id'=>'',
					  'format'=>'',
					  'list'=>array('fields',
							    'filter',
							    'limit',
							    'max', // set to -1 to list all results on a single page
							    'order',
							    'position',
							    //'reverse', not really used much--getting rid of for now
							    'show_totals',
							    'columns',
							    'horizontal',
							    'group'
							    ),
					  'object'=>'',
					  'rec_init'=>'',
					  'step'=>'',
					  'sql'=>'',
					  'sql_pre'=>array(),
					  'sql_security_override' //fixme: this isn't implemented yet
					  );

// top-level elements that can never be passed via _GET or _POST
// fixme: this isn't implemented yet
$engine['control_array_security'] = array('sql',
							'sql_security_override');

$engine['list_control_elements'] = array('max',
						     //'reverse',
						     'show_totals',
						     'columns',
						     'horizontal',
						     'fields'
						     );
$engine['global_default'] = array(
				  // COMMENTED OUT ELEMENTS ARE ADDED WITH set_engine_defaults()
				  // WHICH MUST BE MANUALLY EDITED TO INCLUDE ANY NEW ELEMENTS
				  'add_another' => null,
				  'add_another_password_cycle' => 10,
				  'add_another_and_remember' => null, //if true, when add_another is specified, rec_init 
				  //will contain values of defined fields (see field level option)
				  'add_link_absolute_html'=>null, //append html to right of add link for child list
				  'add_link_absolute_eval'=>null, //append evaluated code to right of add link for child list
				  'add_link_alternate'=>null, //execute an alternate code to generate add link
				  'add_link_label'=>null,
				  'add_link_show'=>true,
				  'add_link_always'=>null, //used to display add links for objects configured on-the-fly
				  'confirm_record'=>null,
				  'cancel_add_url'=>null,
				  'custom'=>array(), // Custom data for object
				  'custom_css'=>null, //passed to <head> tag
				  'display_client_refs'=>null,
				  'include_info_additional'=>true,
				  'delete_another' => null, //allow one to delete multiple records w/o being prompted for password
				  'delete_another_password_cycle' => 10,
				  'enable_client_refs' => null,
				  'allow_object_references' => null,
				  'object_label'=>null, // This is a snippet of code that will be eval'd.  $id, $rec and $def are available.
// 				  'enable_staff_alerts' => null, //during view //set below
// 				  'enable_staff_alerts_add' => null, //during add
				  'hidden_html_absolute' => null, //for things like hidden <div>s, <style> and <script>, output (for list) independent of existing records or not
				  'hidden_eval_absolute' => null, 
				  'singular' => null,
				  'plural' => null,
				  'verb_passive' =>null,
				  'multi_records' => false,
				  'multi' => null,
				  'multi_add' => null, //a configuration array for DAL-style quick add
				  'page_footer_html' => null, //html to throw at the bottom (prior to </body> tag) of a page - uses AG_PAGE_FOOTER
				  //client_refs added below
				  'child_records' => null,
				  'duplicate_posting_window' => 1, // Time, in hours, for which to check for duplicate records
				  'fields_pattern' => array(), // Wildcard (preg) matches on fields
				  'hide_no_perm_records' => null,
				  'id_field' => null,
				  //'list_fields' => null,
				  'list_columns' => 1,
				  'list_hide_numbers' => null,
				  'list_hide_view_links' => null,
				  'list_horizontal' => null,
				  'list_max' => 50,
				  'list_order' => null,
				  'list_position' => 0,
				  'list_reverse' => false,
				  'list_show_totals'=>null,
				  'list_control_openoffice_button'=>null,
				  'lookup_labels'=>'engine_label', // Hack.  An SQL expression to look up labels from a table.  ("SELECT field_name,label FROM engine_label WHERE table_name='tbl_membership_info'")
				  'object_union' => null, //define tables/objects (as an array) on which current object is built
				  //perm added_below
				  'parent_js_show'=>null,         //default to loading into page, but hidden via js
				  'parent_show' => true,          // will default to load on the parent table/page
				  'post_with_transactions'=>true, //records will be posted within transactions
				  'prepend_finished_add_eval' => null, //evaluate code and prepend result to finished view
				  'quick_search' => array(), // Configuration options for quick searches
				  'rec_init_from_previous'=>null,      //if true, this will fill in the form with the clients last record
				  'registration'=> null, // array to specify registration (adding) duplicate-checking
				  'require_delete_comment' => true,    //will force a comment to be entered when deleting a record
				  'require_delete_reason' => true,   // only if delete_reason_code exists in table
				  'require_void_comment' => true,    //will force a comment to be entered when voiding a record
				  'require_void_reason' => true,   // only if void_reason_code exists in table
				  'require_password' => false,
				  'sel_sql' => null,

				  'single_active_record' => null, //"only one open" functionality (income, residence_own etc)
				  'active_date_field' => null,     // set to start date field if different from {object}_date
				  'active_date_end_field' => null, // set to end date field if different from {object}_date_end

				  'stamp_adds' => false,
				  'stamp_changes' => false,
				  'stamp_deletes' => false,
				  'subtitle_html'=>null,
				  'subtitle_eval_code'=>null,
				  'table' => null,
				  'table_post' => null,
				  'use_table_post_edit'=>null, //pull data out of tbl_object rather than object view - used for tables that have data-changing views
				  'valid_record'=>null, //a record-wide validity check (has access to $rec and $rec_last)
				  'invalid_record'=>null, //a record-wide in-validity check (has access to $rec and $rec_last)
				  'verify_on_post'=>true, //re-verify record prior to posting
				  'unique_constraints'=>null, // an array (of arrays) specifying which sets of fields a record must be unique on
				  'widget'=>null, //hook for widget functionality -> see widget.php for more details
				  'allow_add' => true,
				  'allow_delete' => true,
				  'allow_void' => true,
				  'allow_edit' => true,
				  'allow_list' => true,
				  'allow_view' => true,
				  'allow_download' => true,
				  'allow_skip_confirm' => true, // If add/edit record has no confirmation warnings, post directly without review
				  //'label_format' => '$x',
				  //'value_format' => 'bold($x)', redundant!!
				  //title added below
				  'perm_delete' => 'admin',
				  'perm_void' => 'admin',
				  'title_format' => null);

// Default perms for lookup tables
$engine['lookup_default'] = array(
	'perm_delete' => 'admin',
	'perm_add' => 'admin',
	'perm_edit' => 'admin'
);

$engine['field_default'] = array(
				'add_another_remember'=>null, //set this to true for fields engine will remember on add_another
				'add_main_objects'=>null, //will display a client selector if null
				'append_only' => null, // data can only be appended, no change to existing
				'array_max_elements'=>null, //default is 10 for data type array
				'boolean_form_type'=>null, //allow different options for booleans (checkbox,allow_null)
				'attachment_use_filename_original'=>false, 
				'cell_align_label'=>null, //default is right (choose right,left,center)
				'cell_align_value'=>null, //default is left
				'edit_main_objects'=>null, //will display a client selector to change client_id (USE CAREFULLY)
				'add_query_modify_condition'=>null,
				'edit_query_modify_condition'=>null, //changes ENGINE query on edited or added records accordingly ENGINE_UNSET_FIELD doesn't use field in query (this has been superseded by RETURNING in PostgreSQL)
				'comment' => null,
				//comment_show set below
				'confirm' => null,
				'confirm_invalid' => null,
				'data_type' => null,
				//display set below
				'default' => null,
				'force_case' => null,
				//'formula' => null, REPLACED BY 'value'
				//'label' => null, 
				//'label_format' => '$x',
				'is_html'=>null, //alternate to changing data-type ( see value_generic() )
				'java'=>null,
				'length' => 65,
				'length_decimal_places' => null, //automatically set for types numeric({length},x)
				'lookup' => null,
				'lookup_order' => null, //can be one of {a field name or number},TABLE_ORDER, CODE, LABEL (default)
				'lookup_format'=>null, //default is droplist, the other supported option is radio
				'lookup_group'=>null, // SQL expression to group list items by.  Lookup table is aliased to "l" if needed to reference other tables
				'never_to_form'=>null,
				'null_ok' => null,
				'null_post_value'=>null, //if null, engine will post this value
				'order_by_instead' => null,
				//post set below
				//'show_lookup_code'=>null, set below
				'rec_init_from_previous_f'=>null,
				'require_comment_codes'=>array(),
				'require_comment_field'=>'comment', //FIXME: autoconfig for better options
				'row_before'=>null,
				'row_before_edit'=>null,
				'row_before_view'=>null,
				'row_after'=>null,
				'row_after_edit'=>null,
				'row_after_view'=>null,
				'skip_selector_control'=>null, // Right now, only for checkboxes, omits all/invert/none
				'selector_object'=>null,
				'staff_subset' => null, //defined subsets currently include staff_cm, staff_rc
				'staff_inactive_add' => null, //default is to hide inactive staff on adds, set this to true to get all staff
				'system_field' => null,
				'table_switch' => null,
				'textarea_width' => null,
				'textarea_height' => null,
				'time_drop_list' => null, //toggles between varchar and drop list - default is varchar
				'timestamp_allow_date' => null, //for timestamp fields, allow a null value in time field - defaults in db to 00:00:00
				'timestamp_format' => null, //set to "drop_list" for an html select list
				'total_value_list' => null, //an evaluated expression to modify record value for list totals - has access to $x and $rec
				'trim_whitespace' => true,
				'valid' => null,
				'invalid' => null,
				//'value' => '$x',
				//'value_format' => 'bold($x)',
				'virtual_field' => null,
				'view_field_only' => null
				);

$engine['virtual_field_options'] = array( //set options here for virtual (config_file) fields
					   'virtual_field'=>true,
					   'post_add'=>false,
					   'post_edit'=>false,
					   'post_delete'=>false,
					   'post_void'=>false,
					   'display_add'=>'hide',
					   'display_edit'=>'hide',
					   'display_delete'=>'hide',
					   'null_ok' => true
					   );
$engine['view_only_field_options'] = array( //set options here for fields that are only in the 'view'
					   'view_field_only'=>true,
					   'post_add'=>false,
					   'post_edit'=>false,
					   'post_delete'=>false,
					   'post_void'=>false,
					   'display_add'=>'hide',
					   'display_edit'=>'hide',
					   'display_delete'=>'hide',
					   'null_ok' => true
					   );					   
$engine['functions']=array(
			   'add_fields' => 'add_fields_generic',
			   'auto_close' => 'auto_close_generic',
			   'cancel_url' => 'cancel_url_generic',
			   'delete' => 'delete_void_generic',
			   'void' => 'delete_void_generic',
			   'form' => 'form_generic',
			   'form_row' => 'form_generic_row',
			   'view' => 'view_generic',
			   'view_row' => 'view_generic_row',
			   'blank' => 'blank_generic',
			   'generate_list'=>'generate_list_generic',
			   'generate_list_medium'=>'generate_list_medium_generic',
			   'generate_list_long'=>'generate_list_long_generic',
			   'get' => 'get_generic',
			   'get_active' => 'get_active_generic',
			   'valid' => 'valid_generic',
			   'confirm' => 'confirm_generic',
			   'object_merge' => 'object_merge_generic',
			   'post' => 'post_generic',
			   'process' => 'process_generic',
			   'list' => 'list_generic',
			   'rec_changed' => 'rec_changed_generic',
			   'rec_collision' => 'rec_collision_generic',
			   'show_query_row'=>'show_query_row_generic',
			   'engine_record_perm'=>'engine_record_perm_generic',
			   'title'=>'title_generic',
			   'list_title'=>'list_title_generic',
			   'process_staff_alert'=>'process_staff_alert_generic',
			   // functions for multi-add
			   'init_form' => 'init_form_generic',
			   'form_list_header' => 'form_list_header_generic',
			   'form_list_row' => 'form_list_row_generic',
			   'view_list_row' => 'view_list_row_generic',
			   'form_list' => 'form_list_generic',
			   'multi_add_title' => 'multi_add_title_generic',
			   'multi_record_passed' => 'multi_record_passed_generic',
			   'post_multi_records' => 'post_multi_records_generic',
			   'multi_record_allow_common_fields' => 'multi_record_allow_common_fields_generic',
			   'view_list' => 'view_list_generic',
			   'multi_add_after_post' => 'multi_add_after_post_generic',
			   'multi_add_blank' => 'multi_add_blank_generic',
			   'multi_hide_fields' => 'multi_hide_fields_generic'
			   );

$engine['data_types'] = array(
					'html' => array(),
					'currency' => array(),
					'phone' => array(),
					'integer' => array(
								 'int4',
								 'int8',
								 'bigint',
								 'integer',
								 'oid',
								 'smallint'),
					'interval' => array('interval'),
					'float' => array(
							     'float8',
							     'double precision',
							     'numeric',
							     'real'),
					'boolean' => array(
								 'boolean',
								 'bool'),
					'varchar' => array('cidr', //ip address
								 'varchar',
								 'name' //63-character type for storing system identifiers
								 ),
					'character' => array(
								   'character'),
					'text' => array('text'),
					'date' => array(
							    'date'),
					'date_past' => array('date_past'), //custom AGENCY Postgresql Domain - see create.domain.dates.sql
					'time' => array('time'),
					'timestamp' => array(
								   'timestamp'),
					'timestamp_past' => array('timestamp_past'), //custom AGENCY Postgresql Domain - see create.domain.dates.sql
					'array' => array(//add more here as needed
							     'varchar[]',
							     'integer[]'),
					'attachment' => array() // should this be array( 'int' )?
			      );
			      //everything else is unknown
$engine['system_fields'] = array(
//FIXME: I think all the 'display_*' vars could be removed from
//       these system field arrays, as the are handled by system_fields_f
				 'deleted_by' => array(
						       'system_field' => true,
						       'data_type' => 'staff',
						       'display_add' => 'hide',
						       'display_edit' => 'hide',
						       'display_delete' => 'display',
						       'display_view' => 'hide',
						       'display_list' => 'hide',
						       'null_ok' => true,
						       'post_add' => false,
						       'post_edit' => false,
						       'post_delete' => true,
						       'post_void' => false,
						       'label' => 'Record Deleted By'
// 						       'value_delete' => '$GLOBALS["UID"]'
						       ),
				 'deleted_at' => array( 
						       'system_field' => true,
						       'data_type' => 'timestamp',
						       'display_add' => 'hide',
						       'display_edit' => 'hide',
						       'display_delete' => 'display',
						       'display_view' => 'hide',
						       'display_list' => 'hide',
						       'post_add' => false,
						       'post_edit' => false,
						       'post_delete' => true,
						       'post_void' => false,
						       'null_ok' => true,
						       'label' => 'Record Deleted At'
						       ),
				 'is_deleted' => array(
						       'system_field' => true,
						       'data_type' => 'boolean',
						       'display_add' => 'hide',
						       'display_edit' => 'hide',
						       'display_delete' => 'hide',
						       'display_view' => 'hide',
						       'display_list' => 'hide',
						       'post_add' => false,
						       'post_edit' => false,
						       'post_delete' => true,
						       'post_void' => false,
						       'label' => 'Record Deleted'
						       ),
				 'deleted_comment' => array(
							    'system_field' => true,
							    'data_type' => 'text',
							    'display_add' => 'hide',
							    'display_edit' => 'hide',
							    'display_delete' => 'regular',
							    'display_view' => 'hide',
							    'display_list' => 'hide',
							    'post_add' => false,
							    'post_edit' => false,
							    'post_delete' => true,
						        'post_void' => false,
							    'null_ok' => true,
							    'label' => 'Deleted Comment'
							    ),
				 'voided_by' => array(
						       'system_field' => true,
						       'data_type' => 'staff',
						       'display_add' => 'hide',
						       'display_edit' => 'hide',
						       'display_delete' => 'display',
						       'display_view' => 'hide',
						       'display_list' => 'hide',
						       'null_ok' => true,
						       'default' => '$GLOBALS["UID"]',
						       'post_add' => false,
						       'post_edit' => false,
						       'post_void' => true,
						       'post_delete' => false,
						       'label' => 'Record Voided By'
// 						       'value_delete' => '$GLOBALS["UID"]'
						       ),
				 'voided_at' => array( 
						       'system_field' => true,
						       'data_type' => 'timestamp',
						       'display_add' => 'hide',
						       'display_edit' => 'hide',
						       'display_delete' => 'display',
						       'display_view' => 'hide',
						       'display_list' => 'hide',
						       'post_add' => false,
						       'post_edit' => false,
						       'post_delete' => false,
						       'post_void' => true,
						       'null_ok' => true,
						       'label' => 'Record Voided At'
						       ),
				 'is_void' => array(
						       'system_field' => true,
						       'data_type' => 'boolean',
						       'display_add' => 'hide',
						       'display_edit' => 'hide',
						       'display_delete' => 'hide',
						       'display_view' => 'hide',
						       'display_list' => 'hide',
						       'post_add' => false,
						       'post_edit' => false,
						       'post_delete' => false,
						       'post_void' => true,
						       'label' => 'Record Voided'
						       ),
				 'void_comment' => array(
							    'system_field' => true,
							    'data_type' => 'text',
							    'display_add' => 'hide',
							    'display_edit' => 'hide',
							    'display_delete' => 'regular',
							    'display_view' => 'hide',
							    'display_list' => 'hide',
							    'post_add' => false,
							    'post_edit' => false,
							    'post_delete' => false,
						        'post_void' => true,
							    'null_ok' => true,
							    'label' => 'Void Comment'
							    ),
				 'void_reason_code' => array(
							    'system_field' => true,
							    'data_type' => 'text',
							    'display_add' => 'hide',
							    'display_edit' => 'hide',
							    'display_delete' => 'regular',
							    'display_view' => 'hide',
							    'display_list' => 'hide',
							    'post_add' => false,
							    'post_edit' => false,
							    'post_delete' => false,
							    'post_void' => false,
							    'null_ok' => true,
							    'label' => 'Void Comment'
							    ),
				 'added_at' => array(
						     'system_field' => true,
						     'data_type' => 'timestamp',
						     'display_add' => 'hide',
						     'display_edit' => 'display',
						     'display_delete' => 'display',
						     'display_view' => 'display',
						     'display_list' => 'display',
						     'post_add' => false,
						     'post_edit' => false,
						     'post_delete' => false,
					         'post_void' => true,
						     'null_ok' => true,
						     'label' => 'Record Added At',
						     'label_list' => 'Added At',
						     'value_format_list'=> 'datetimeof($x,"US","TWO")'
						     ),
				 'added_by' => array(
						     'system_field' => true,
						     'data_type' => 'staff',
						     'label' => 'Record Added By',
						     'label_list' => 'Added By',
						     'default' => '$GLOBALS["UID"]',
						     'display_add' => 'display',
						     'display_edit' => 'display',
						     'display_delete' => 'display',
						     'display_view' => 'display',
						     'display_list' => 'display'
						     ),
				 'changed_at' => array(
						       'system_field' => true,
						       'data_type' => 'timestamp',
						       'display_add' => 'hide',
						       'display_edit' => 'display',
						       'display_delete' => 'display',
						       'display_view' => 'display',
						       'display_list' => 'display',
						       'post_add' => false,
						       'post_edit' => true,
						       'post_delete' => false,
						       'null_ok' => true,
						       'label' => 'Record Changed At'
						       ),
				 'changed_by' => array(
						       'system_field' => true,
						       'display_add' => 'hide',
						       'display_edit' => 'display',
						       'display_delete' => 'display',
						       'display_view' => 'display',
						       'display_list' => 'display',
						       'data_type' => 'staff',
						       'label' => 'Record Changed By',
						       'default' => '$GLOBALS["UID"]',
						       'post_add' => true,
						       'post_edit' => true,
						       'post_delete' => false
						       ),
				 'sys_log' => array(
						    'system_field' => true,
						    'data_type' => 'text',
						    'display_add' => 'display',
						    'display_edit' => 'display',
						    'display_delete' => 'display',
						    'display_view' => 'display',
						    'display_list' => 'hide',
						    'null_ok' => true,
						    'label' => 'System Log',
						    'post_delete' => false,
						    'append_only' => true
						    )
				 );
$engine['action_specific_vars']=array(
				      'global'=>array(
						      'perm'=>'any',
						      'title'=> 'ucwords("{$action}ing {$def["singular"]}")',
						      'title_format'=> 'bigger(bold($x))',
						       'subtitle_eval_code'=>NULL,
							'enable_staff_alerts' => null
						      ),
				      'fields'=>array(
						      'display'=>'regular',
							  'display_eval'=>NULL,
						      'comment_show'=>true,
						      'post'=>true,
						      'value'=>'$x',
						      'value_format'=>'$x',
						      'label'=>null,
						      'label_format'=>'$x',
							'show_lookup_code'=>null
						      )
				      );
foreach ($engine['actions'] as $tmp => $value)
{
      //global stuff
      foreach($engine['action_specific_vars']['global'] as $option=>$val)
      {
	    $engine['global_default'][$option.'_'.$tmp]=orr($engine['global_default'][$option.'_'.$tmp],$val);
      }
	if ($tmp=='list') {
		$engine['global_default']['title_'.$tmp]='ucwords("{$action}ing {$def["singular"]} records")'; //all this for a measley 's'
	}

     //field stuff
      foreach($engine['action_specific_vars']['fields'] as $option=>$val)
      {
	    $engine['field_default'][$option.'_'.$tmp]=orr($engine['field_default'][$option.'_'.$tmp],$val);
      }
      if ($value=='read') // view/list
      {
	    $engine['field_default']['comment_show_'.$tmp]=false;
	    unset($engine['field_default']['post_'.$tmp]);
      }
      else // add/edit/delete/
      {
	    if (in_array($tmp,array('delete','void'))) 
	    {
		  $engine['field_default']['comment_show_'.$tmp]=true;
	    }
	    $engine['field_default']['post_'.$tmp]=true;
      }
}				     
$engine['global_default']['title_list']='ucwords("{$action}ing {$def["singular"]} records")';

?>
