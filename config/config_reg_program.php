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


$engine['reg_program'] = array(
	'singular' => 'Program Registration',
	'perm'     => 'any',
	'list_fields' => array('program_code','reg_program_date','reg_program_date_end','program_completed_code'),
	'fields'   => array(
		'exit_status_code'=>array('label'=>'Exit Was',
			'valid'=>array('be_null($x) xor !be_null($rec["reg_program_date_end"])'=>
				'{$Y} is required (ONLY) if an end date is entered')),
		'exit_referral_code'=>array(
			'valid'=>array('be_null($x) xor !be_null($rec["reg_program_date_end"])'=>
				'{$Y} is required (ONLY) if an end date is entered')),
		'program_completed_code'=>array('label'=>'Program Completed?',
			'valid'=>array('be_null($x) xor !be_null($rec["reg_program_date_end"])'=>
				'{$Y} is required (ONLY) if an end date is entered')),
		'reg_program_date'=>array('label'=>'Start Date'),
		'reg_program_date_end'=>array('label'=>'End Date'),
		'referred_by'=>array('staff_inactive_add' => true)
	)
);
												
?>
