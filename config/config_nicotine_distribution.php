<?php
// To avoid confusion for now, we're labeling this "patches"
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


// If we go to lozenges, we'll go back to labeling "patch/lozenge"
$engine['nicotine_distribution'] = array(
				 'singular' => 'Nicotine Distribution Record',
				 'list_fields'=>array('dispensed_on','dispensed_by','nicotine_dosage_code','nicotine_count'),
				 'rec_init_from_previous' => true,
				 'fields'=>array(
						     'nicotine_count'=>array(
										     'label'=>'# of patches distributed',
										     'valid'=>array('$x >= 0'=>'{$Y} must be a positive number')
										     ),
						     'dispensed_on'=>array('data_type' => 'date_past'),
						     'dispensed_by'=>array('default' => '$GLOBALS["UID"]'),
						     'nicotine_delivery_method_code'=>array('default'=>'PATCH','display'=>'hide'),
						     'is_client_still_smoking_code'=>array('label'=>'Is this client still smoking?',
												  'lookup_order'=>'TABLE_ORDER'),
						     'has_client_reduced_smoking_code' => array('label'=>'Has this client reduced his/her smoking?',
													 'lookup_order'=>'TABLE_ORDER'),
						     'expected_dosage_date_end'=>array('label'=>'Projected end date for this dosage',
												   'valid'=>array('be_null($x) || dateof($x,"SQL") >= dateof($rec["dispensed_on"],"SQL")'
															=>'{$Y} must be greater than or equal to Dispensed On')),
						     'expected_usage_date_end'=>array('label'=>'Projected end date patch usage',
												  'valid'=>array('be_null($x) || dateof($x,"SQL") >= dateof($rec["expected_dosage_date_end"],"SQL")'
														     =>'{$Y} must be greater than or equal to "Projected Dosage End Date"')),
						     'motivation_code'=>array('label'=>'Motivation to quit?',
											'lookup_format'=>'radio_v')
						     )
				 );

$tmp_fields_from_previous = array('nicotine_dosage_code',
					    'nicotine_count',
					    'is_client_still_smoking_code',
					    'has_client_reduced_smoking_code',
					    'expected_dosage_date_end',
					    'expected_usage_date_end');

foreach ($tmp_fields_from_previous as $tmp_f) {
	$engine['nicotine_distribution']['fields'][$tmp_f]['rec_init_from_previous_f'] = true;
}

?>
