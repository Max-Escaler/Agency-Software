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


$engine['staff_employment'] = array(
						'perm' => 'admin',
						'perm_list'=>'any',
						'perm_view'=>'any',
						'single_active_record' => true,
						'active_date_field' => 'hired_on',
						'active_date_end_field' => 'terminated_on',
						'list_fields'=>array('hired_on','terminated_on','staff_position_code'),
						'use_table_post_edit' => true,
						'fields'=>array(
								    'staff_id'=>array('display'=>'display'),
								    'agency_program_code' => array('null_ok'=>false),
								    'agency_project_code' => array('null_ok'=>false),
								    'supervised_by'=>array('valid'=>array('!be_null($x) or ($rec["agency_staff_type_code"]=="SITED")'=>'{$Y} is required except for sited staff')),
								    'agency_facility_code' => array('null_ok'=>false),
								    'staff_shift_code' => array('null_ok'=>false),
								    'agency_staff_type_code' => array('null_ok'=>false),
								    'staff_employment_status_code' => array('null_ok'=>false),
								    'day_off_1_code'=> array('lookup_order'=>'TABLE_ORDER'),
								    'day_off_2_code'=> array('lookup_order'=>'TABLE_ORDER'),
								    'terminated_on' => array('valid'=>array('be_null($x) or (dateof($x,"SQL")>=dateof($rec["hired_on"],"SQL"))'=>'{$Y} must be greater than or equal to Hired On')),
								    'staff_title' => array(
													'comment'=>'Leave blank except for unusual cases',
												   'add_query_modify_condition'=>array('1==1'=>'ENGINE_UNSET_FIELD'), //always unset
												   'edit_query_modify_condition'=>array('1==1'=>'ENGINE_UNSET_FIELD') //always unset
												   )

								    )
						);

?>
