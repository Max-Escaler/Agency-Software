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


$engine['service_ir'] = array(
					'multi_add' => array('number_of_records'=>10,
								   'init_fields' => array('service_by','contact_type_code','service_date'),
								   'common_fields' => array('progress_note'),
								   'common_fields_required' => true,
								   'reference_id_field' => 'service_progress_note_id'),
					'fn' => array('generate_list_long' => 'generate_list_long_service',
							  'view' => 'view_service'),
					'table_post'=>'tbl_service',
					'add_link_show'=>false, //force "multi-add" style
					'perm'=>'any',
					'perm_edit'=>'ir_admin',
					'perm_add'=>'ir',
					'singular'=>'I&R Service',
					'subtitle_eval_code'=>'link_multi_add("service_ir",smaller("Add I&R Service(s)"),array("client_id"=>$id,"service_by"=>$GLOBALS["UID"]))." ".link_report("entry_services/shelter/ir_service.cfg",smaller("Print I&R Service Notes"),array("cid"=>$id))',
					'list_fields'=>array('service_date','contact_type_code','service_code','service_minutes','service_by','custom1'),
					'list_order'=>array('service_date'=>true,'service_id'=>false),
					'fields'=>array(
							    'service_date' => array( 'default' => 'NOW',
											     'data_type' => 'timestamp_past',
// 											     'timestamp_allow_date' => true,
											     'value_format_list'=>'datetimeof($x,"US","TWO")'),
							    'service_by' => array( 'default' => '$GLOBALS["UID"]' ),
							    'service_project_code' => array('default'=>'IR',
													'display'=>'hide'),
							    'service_code' => array('data_type'=>'lookup',
											    'lookup'=>array('table' => 'l_service_ir',
														  'value_field'=>'service_ir_code',
														  'label_field'=>'description',
														  'data_type'=>'varchar',
														  'length'=>10)
											    ),
							    'service_progress_note_id' => array('display_add' => 'hide',
													    'display' => 'display',
													    'label' => 'Associated Progress Note ID'),
							    /* only used in cd table */
							    'asam_dimension_1' => array('post'=>false,'display'=>'hide'),
							    'asam_dimension_2' => array('post'=>false,'display'=>'hide'),
							    'asam_dimension_3' => array('post'=>false,'display'=>'hide'),
							    'asam_dimension_4' => array('post'=>false,'display'=>'hide'),
							    'asam_dimension_5' => array('post'=>false,'display'=>'hide'),
							    'asam_dimension_6' => array('post'=>false,'display'=>'hide'),
							    
							    'custom1'=>array('label'=>'Progress Note Status',
										   'display'=>'hide',
										   'display_view'=>'display',
										   'display_list'=>'display',
										   'is_html'=>true,
										   'value'=>'service_progress_note_summary($rec,$def)',
										   'value_format'=>'smaller($x)'
										   )
							    
							    
							    )
					);
?>
