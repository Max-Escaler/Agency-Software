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


$engine['elevated_concern'] = array(
					   'perm' => 'my_client_project,clinical,ecl_admin',
					   'perm_list' => 'any',
					   'perm_edit' => 'my_client_project_clinical,clinical,ecl_admin',
					   'perm_add'  => 'ecl_admin',
					   'singular' => 'Elevated Concern List Record',
					   'list_fields' => array('client_id','custom1','custom2','custom3','current_status_code','elevated_concern_reason_codes','elevated_concern_date','elevated_concern_date_end','next_meeting_date'),
					   'fields' => array(
								   'point_person' => array('data_type'=>'staff',
												   'show_lookup_code_list' => 'DESCRIPTION'),
								   'ecl_point_case_manager' => array('data_type'=>'staff',
														 'show_lookup_code_list' => 'DESCRIPTION',
														 'label' => 'ECL Point Case Manager'),
								   'other_team_members'       => array('data_type'=>'staff_list',
													     'show_lookup_code_list' => 'DESCRIPTION'),
								   'elevated_concern_reason_codes' => array('lookup_format'=>'checkbox_v',
														     'label' => 'Reasons'),
								   'elevated_concern_reason_detail' => array('label' => 'Detailed Reasons'),
								   'elevated_concern_date' => array('label' => 'Date',
													    'display_add' => 'hide',
													    'display_edit' => 'display'),							
								   'elevated_concern_date_end' => array(
														 'label' => 'Removed Date',
														 'display_add' => 'hide',
														 'valid' => array('(be_null($x) && $rec["current_status_code"] != "REMOVED" && be_null($rec["reasons_for_removal"]))|| (!be_null($x) && $rec["current_status_code"]=="REMOVED" && !be_null($rec["reasons_for_removal"]))' => 'If removing a client, an end date must be set, and status must be set to "Removed" and Reasons for Removal should be filled in. Otherwise, these fields should be blank (and the status should be something other than "Removed")')
														 ),
								   'past_meetings' => array('value'=>'elevated_concern_past_meetings($rec)',
												    'value_add' => '$x',
												    'value_edit' => '$x',
												    'value_delete' => '$x',
												    'is_html' => true),

								   'next_meeting_date' => array('value'=>'be_null($x) ? "" : link_engine(array("object"=>"elevated_concern","id"=>$rec["elevated_concern_id"],"action"=>"edit"),dateof($x),"","class=\"fancyLink\" title=\"click to edit next meeting date\"")',
													  'value_add' => '$x',
													  'value_edit' => '$x',
													  'is_html'=>true),
								   'reasons_for_removal' => array('display_add' => 'hide'),

								   //photo
								   'custom1' => array(
											    'display'=>'hide',
											    'display_list'=>'regular',
											    'value'=>'client_photo( $rec["client_id"], 0.5 )',
											    'is_html'=>true,
											    'label_format_list'=>'smaller($x)',
											    'label_list'=>'Picture'),
								   //staff involved
								   'custom2' => array(
											    'label' => 'Staff',
											    'is_html' => true,
											    'value' => 'elevated_concern_all_team_members($rec)',
											    'display' => 'hide',
											    'display_list' => 'regular'
											    ),
								   'custom3' => array(
											    'label' => 'Additional Staff',
											    'is_html' => true,
											    'value' => 'elevated_concern_additional_team_members($rec)',
											    'display' => 'hide',
											    'display_view' => 'regular',
											    'display_list' => 'regular'
											    )
								   )
					   );
?>
