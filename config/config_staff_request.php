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


$engine['staff_request'] = array(
					   'allow_staff_alerts'=>true,
					   'perm'=>'supervisor,admin,staff_request',
					   'perm_view'=>'self,browse_staff_request',
					   'title'=>'sql_true($rec["is_transfer"]) ? ucwords($action) . "ing Staff Transfer Request" : ucwords($action) . "ing New Staff Request"',
					   'list_fields'=>array('is_transfer','staff_id','name_last','name_first','starts_on','added_by','staff_request_status_code'),
					   'list_order'=>array('staff_request_status_code'=>true,'added_at'=>true),
					   'fields'=>array(
								 'size_head'=>array('display'=>'hide'),
								 'replacing_staff'=>array('data_type'=>'staff',
												  'staff_inactive_add'=>true),
								 'home_phone'=>array('data_type'=>'phone'),
								 'prior_employee_code'=>array('label'=> 'Prior DESC Employee?'),
								 'prior_staff_id'=>array('comment'=> 'Enter only if Prior DESC Employee'),
								 'staff_request_status_code'=>array('display_add'=>'hide',
														'comment'=>'For IS use only'),
								 'hr_request_status_code'=>array('display_add'=>'hide',
													   'label' => 'HR Request Status',
													   'comment'=>'For HR use only'),
								 'staff_pay_step_code'=>array('label'=>'Starting Pay Step'),
								 'voice_mail_number'=>array('data_type'=>'phone'),
								 'day_off_1_code'=>array('lookup_order'=>'TABLE_ORDER'),
								 'day_off_2_code'=>array('lookup_order'=>'TABLE_ORDER'),
								'is_transfer'=>array('display_add'=>'hide','display_edit'=>'hide'),
								 'name_first'=>array('label' => 'First Name',
											  'valid'=>array('sql_true($rec["is_transfer"]) xor !be_null($x)'=>'{$Y} cannot be blank')),
								 'name_first_legal'=>array('label' => 'Legal First Name',
											   'comment'=> 'Enter only if different from First Name'),
								 'name_last'=>array('label' => 'Last Name',
											  'valid'=>array('sql_true($rec["is_transfer"]) xor !be_null($x)'=>'{$Y} cannot be blank')),
								 'gender_code'=>array('valid'=>array('sql_true($rec["is_transfer"]) xor !be_null($x)'=>'{$Y} cannot be blank')),
								 'comment' => array('comment' => 'For case managers, indicate CM I or CM II')
								 )
					   );

?>
