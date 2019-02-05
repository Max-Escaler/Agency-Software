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


$engine['cd_reg'] = array(
				  'singular' => 'CD Registration',
				  'perm'     => 'cd',
				  'single_active_record' => true,
				  'list_fields' => array('cd_reg_date','referral_code','cd_reg_date_end','exit_status_code'),
				  'fields'   => array(
							    'exit_status_code'=>array('label'=>'Exit Was',
												'valid'=>array('be_null($x) xor !be_null($rec["cd_reg_date_end"])'=>'{$Y} is required (ONLY) if an end date is entered')
												),
							    'exit_referral_code'=>array('data_type'=>'lookup',
												  'lookup'=>array('table'=>'l_referral',
															'value_field'=>'referral_code',
															'label_field'=>'description'),
												  'valid'=>array('be_null($x) xor !be_null($rec["cd_reg_date_end"])'=>'{$Y} is required (ONLY) if an end date is entered')),
							    'cd_reg_date'=>array('label'=>'CD Registration Date'),
							    'cd_reg_date_end'=>array('label'=>'CD Registration End Date'),
							    'exit_consumption_code' => array('label' => 'During the period of registration, client\'s consumption of drugs and alcohol',
													 'valid'=>array('be_null($x) xor !be_null($rec["cd_reg_date_end"])'=>'{$Y} is required (ONLY) if an end date is entered'),
													 ),
				  'exit_recovery_code' => array('label' => 'During the period of registration, client\'s movement towards recovery',
									  'valid'=>array('be_null($x) xor !be_null($rec["cd_reg_date_end"])'=>'{$Y} is required (ONLY) if an end date is entered'),
									  ),
							    'referred_by'=>array('staff_inactive_add' => true)

							    )
				  );
												
?>