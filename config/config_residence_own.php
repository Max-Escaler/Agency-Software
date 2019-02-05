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

$engine['residence_own']=array(
					  'enable_staff_alerts_view'=>true,
					  'singular'=> org_name('short') . ' Residence',
					  'plural'=>org_name('short') . 'Residencies',
					  'perm_add'=>'housing',
					  'perm_edit'=>'housing',
					  'list_fields'=>array('residence_date','residence_date_end','housing_project_code','housing_unit_code'),
					  'list_order'=>array(
								    'residence_date'=>true
								    ),
					  'fields'=>array(
								'residence_date'=>array(
												'label'=>'Move-in date',
												'label_list'=>'Move-in'
												),
								'residence_date_end'=>array(
												    'label'=>'Move-out date',
												    'label_list'=>'Move-out',
												    'java'=>array(
															'on_event'=>array(
																		'disable_on_null'=>
																		array('moved_to_code',
																			'departure_type_code',
																			'departure_reason_code',
																			'move_out_was_code')
																		)
															)
												    ),
								'housing_project_code'=>array(
													     'label'=>'Housing Project',
													     'label_list'=>'Project',
													     'display_edit'=>'display',
													     'java'=>
													     array(
														     'on_event'=>
														     array(
															     'populate_on_select'=>
															     array('populate_field'=>'housing_unit_code',
																     'table'=>'housing_unit_current')
															     //, as of now, java_engine can't handle multiple js events...
															     // but when it can, use this :)
															     //'enable_on_value'=>array('SCATTERED','lease_on_file')
															     
															     )
														     )
													     ),
								'housing_unit_code'=>array(
												   'data_type'=>'lookup',
												   'display_edit'=>'display',
												   'lookup'=>array(
															 'table'=>'housing_unit',
															 'value_field'=>'housing_unit_code',
															 'label_field'=>'housing_unit_code'),
												   'show_lookup_code'=>'CODE',
												   'label'=>'Unit',
												   'is_html'=>true,
												   'value'=>'link_unit_history($x,true,false)',
												   'valid' => array('be_null($x) || can_occupy_residence_own($rec["client_id"],$rec["housing_unit_code"],$rec["residence_date"])'=>'This unit is already fully occupied')
												   ),
								'chronic_homeless_status_code'=>array('label'=>'Chronic Homeless Status at time of Move-in',
														  'lookup_order'=>'TABLE_ORDER',
														  'comment'=>'(NOTE: full living situation must still be entered into AGENCY)'),
								'moved_from_code'=>array(
												 'data_type'=>'lookup',
												 'label'=>'Moved From',
												 // 'lookup_order' => 'TABLE_ORDER',
												 'lookup'=>array('table'=>'l_facility',
														     'value_field'=>'facility_code',
														     'label_field'=>'description')
												 ),
								'lease_on_file' => array('valid'=>array( '(be_null($x) and $rec["housing_project_code"]<>"SCATTERED")
														     or (!be_null($x) and $rec["housing_project_code"]=="SCATTERED")'
														     =>'{$Y} is required for scattered site units only'),
												 'boolean_form_type'=>'allow_null'
												 ),
								'moved_to_code'=>array(
												 'label'=>'Moved To',
												 'valid'=>array('$x || be_null($rec["residence_date_end"])'=>
														    'Field Moved To: Must specify for move-outs')
												 ),
								'departure_type_code'=>array(
												     'valid'=>array('$x || be_null($rec["residence_date_end"])'=>
															  'Field Departure Type: Must specify for move-outs')
												     ),
								'departure_reason_code'=>array(
													 'valid'=>array('$x || be_null($rec["residence_date_end"])'=>
															    'Field Departure Reason: Must specify for move-outs')
												 ),
								'move_out_was_code'=>array(
												   'valid'=>array('$x || be_null($rec["residence_date_end"])'=>
															'Field Move Out Was: Must specify for move-outs')
												   ),
								'returned_homeless'=>array('label'=>'Returned to homelessness?',
												   'valid'=>array('be_null($x) or !be_null($rec["residence_date_end"])'=>'{$Y} must be blank for current records.'),
												   'confirm'=>array('!be_null($x) or be_null($rec["residence_date_end"])'=>
															  'If known, field {$Y} should be specified for move-outs'),
												   'boolean_form_type'=>'allow_null'
												   ),
								'moved_to_unit'=>array(
// 											     'data_type'=>'lookup',
// 											     'lookup'=>array(
// 														   'table'=>'housing_unit',
// 														   'value_field'=>'housing_unit_code',
// 														   'label_field'=>'housing_unit_code'),
											     'show_lookup_code'=>'CODE',
											     'is_html'=>true,
											     'value'=>'link_unit_history($x)'
											     ),
								)
					  );
?>
