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

$engine['housing_history'] = array(
					     'singular'=>'Living Situation History',
					     'plural'=>'Living Situation History',
					     'object_union' => array('residence_own','residence_other'),
					     'list_fields' => array(
									    'living_situation_code',
									    'residence_date',
									    'residence_date_end',
									    'housing_unit_code'),
					     'list_order'=>array(
									 'residence_date'=>true
									 ),
					     'fields' => array(
								     'housing_history_id'=>array(
															'data_type'=>'table_switch',
															'table_switch'=>array(
																		    'identifier'=>'::'
																		    ),
															'label' => '&nbsp;'
															),
								     'residence_date'=>array(
												     'label'=>'Start Date',
												     'label_list'=>'Start'),
								     'residence_date_end'=>array(
													   'label'=>'End Date',
													   'label_list'=>'End'),
								     'housing_unit_code'=>array(
													  'data_type'=>'html',
													  'value'=>'link_unit_history($x)',
													  'label'=>'Unit'),
								     'zipcode'=>array(
											    'label'=>'ZIP Code'),
								     'moved_to_code'=>array(
												    'data_type'=>'lookup',
												    'label'=>'Moved to following Situation',
												    //  'lookup_order' => 'TABLE_ORDER',
												    'lookup' => array(
															    'table' => 'l_facility',
															    'value_field' => 'facility_code',
															    'label_field' => 'description')),
								     'moved_from_code' =>array(
													 'data_type'=>'lookup',
													 'label' => 'Prior Living Situation',
													 //'lookup_order' => 'TABLE_ORDER',
													 'lookup' => array(
																 'table' => 'l_facility',
																 'value_field' => 'facility_code',
																 'label_field' => 'description')),
								     'residence_date_accuracy' => array(
														    'label' => 'Start Date is',
														    'data_type' => 'lookup',
														    'lookup' => array(
																	    'table' => 'l_accuracy',
																	    'value_field' => 'accuracy_code',
																	    'label_field' => 'description')),
								     'residence_date_end_accuracy' => array(
															  'label' => 'End Date is',
															  'data_type' => 'lookup',
															  'lookup' => array(
																		  'table' => 'l_accuracy',
																		  'value_field' => 'accuracy_code',
																		  'label_field' => 'description')),
								     )
					     );
					     
?>
