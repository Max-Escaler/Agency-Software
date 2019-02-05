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
global $AG_TEXT;
$engine['staff_assign']=array(
					'singular'=>'Staff Assignment',
					'list_fields'=>array(
								   'staff_id_name',
								   'staff_assign_type_code',
								   'staff_assign_date',
								   'staff_assign_date_end'),
					'list_order'=>array(
								  'staff_assign_date'=>true),
					'fields'=>array(
							    'staff_id'=>array(
								    'java'=>array(
									    'on_event'=>array(
										    'disable_on_select'=>array('name_last','name_first','agency_code','phone_1','phone_2')
										    )
									    ),
								    'comment'=>'required for '.$AG_TEXT['ORGANIZATION_SHORT'].' staff',
								    'display_edit'=>'display'
								    ),
							    'staff_id_name'=>array(
											   'label'=>'Staff',
											   'data_type'=>'staff',
											   'value_format_list'=>'smaller($x)',
											   ),
							    'staff_assign_type_code'=>array(
													'label_list'=>'Type',
													'label'=>'Staff Assignment Type',
													'show_lookup_code_list'=>'CODE',
													'display_edit'=>'display',
													'add_query_modify_condition'=>array('$x=="CM_PAYEE"'=>'CM_PRIMARY'),
													'valid'=>array('$x !== "UNKNOWN"'=>'Cannot assign type UNKNOWN',
															   '$x !== "CM_OTHER" || be_null($rec["staff_id"])'=>
															   'Other CMs can only be assigned to non-' . org_name('short') .' staff',
															   '$x!=="CM_PAYEE" || !be_null($rec["staff_id"])'=>
															   '{$Y}: The joint CM & Payee code can only be used for '.$AG_TEXT['ORGANIZATION_SHORT'].' staff',
															   '!in_array($x,array("CM_MH","CM_MH_PP","CM_MH_PB"))||has_perm("CM_MH_ASSIGNS")
                                                                                               ||be_null($rec["staff_id"])'=>
															   'You are not allowed to make MH CM assignments')
													),
							    'comment'=>array(
										   'comment'=>'(required for \'Just Monitoring...\' assignments)',
										   'valid'=>array('!be_null($x) || $rec["staff_assign_type_code"] !=="MONITOR"'=>
													'Must add a comment to use \'Just Monitoring...\' assignment type<br />'
													.smaller(indent().'(ie, why you want to monitor this '.AG_MAIN_OBJECT.')')
													)
										   ),
							    'name_last'=>array(
										     'valid'=>array(
													  '!be_null($rec["staff_id"]) || !be_null($x)'=>
													  'Field Name Last is required for non-'.$AG_TEXT['ORGANIZATION_SHORT'].' staff'
													  ),
										     'comment'=>'required for non-'.$AG_TEXT['ORGANIZATION_SHORT'].' staff'
										     ),
							    'agency_code'=>array(
											 'label'=>'Organization', 
										     'valid'=>array(
													  '!be_null($rec["staff_id"]) || !be_null($x)'=>
													  'Field Agency is required for non-'.$AG_TEXT['ORGANIZATION_SHORT'].' staff'
													  ),
										     'comment'=>'required for non-'.$AG_TEXT['ORGANIZATION_SHORT'].' staff'
										     ),
							    'staff_assign_date'=>array(
												 'label'=>'Start Date',
												 'label_list'=>'Start'),
							    'staff_assign_date_end'=>array(
												 'label'=>'End Date',
												 'label_list'=>'End'),
							    'send_alert'=>array(
											'label'=>'Get alerts for this '.AG_MAIN_OBJECT.'?',
											'label_list'=>'Send alert?',
											'valid'=>array('sql_false($x) || !be_null($rec["staff_id"])'=>
													   'Can\'t send alerts for non-'.$AG_TEXT['ORGANIZATION_SHORT'].' staff.')
											)
							    )
					);
?>
