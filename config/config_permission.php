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

$engine['permission']=array(
			'allow_delete'=>true,
			'perm'=>'super_user',
			'perm_list'=>'any',
			'list_fields' => array('permission_type_code','permission_basis','criteria','permission_date','permission_date_end','RWS'),
			'valid_record'=>array('sql_true($rec["permission_read"]) or sql_true($rec["permission_write"]) or sql_true($rec["permission_super"])'
							=>'You must select at least one permission mode (read, write or super).'),
			'widget'=>array(
					    'add'=>true,
					    'edit'=>true,
					    'style'=>'one_of_each',
					    'key'=>'permission_type_code',
					    'fixed'=>array('staff_id'),
					    'delete_no_pass_records'=>true,
					    'record_passed_eval_code' => 'sql_true($rec["permission_read"])
								     or sql_true($rec["permission_write"])
								     or sql_true($rec["permission_super"])',
					    'required_fields'=>array('permission_read','permission_write','permission_super')
					    ),
			'fields' => array( 
					  'RWS' => array(
						'value'=>'(sql_true($rec["permission_read"]) ? "R" : "") 
								 . (sql_true($rec["permission_write"]) ? "W" : "") 
								 . (sql_true($rec["permission_super"]) ? "S" : "")',
					  ),
					  'permission_date_end'=>array('label'=>'End Date'),
					  'criteria'=>array(
						'value'=>'implode(oline(),array_filter(array(
									be_null($rec["staff_id"]) ? "" : staff_link($rec["staff_id"]),
									be_null($rec["agency_project_code"]) ? "" : ("Project: " . sql_lookup_description($rec["agency_project_code"],"l_agency_project","agency_project_code")),
									be_null($rec["agency_program_code"]) ? "" : ("Program: " . sql_lookup_description($rec["agency_program_code"],"l_agency_program","agency_program_code")),
									be_null($rec["staff_position_code"]) ? "" : ("Position: " . sql_lookup_description($rec["staff_position_code"],"l_staff_position","staff_position_code"))
								)))',
						'is_html'=>true
						),
					  'permission_id' =>
					  array( 'label' => 'Permission #',
						   'display' => 'display',
						   'display_add' => 'hide',
						   'post_add' => false,
						   'null_ok' => true),
					  'permission_type_code'=>array('valid'=>array('has_perm("super_user") || ($x !== "SUPER_USER")'=>
												     'Catch-22: Only super-users can add AGENCY super-user permissions')
										  ),
					  'permission_read'=>array('boolean_form_type'=>'checkbox'),
					  'permission_write'=>array('boolean_form_type'=>'checkbox'),
					  'permission_super'=>array('boolean_form_type'=>'checkbox')
					  )
			);

?>
