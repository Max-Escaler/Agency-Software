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

$d_def=get_def('disability');
$d_lab=$d_def['plural'];
$engine['client']['multi_records'] = true;
$engine['client']['multi'] = array(
	'disability' => array(
    	'sub_title' => $d_lab,
	    'sub_sub_title'=>smaller('Check appropriate ' .$d_lab . ' based on '.AG_MAIN_OBJECT.' self-report or staff observation'),
		'multi_fields'=>'disability_date',
		'object' => 'disability',
		'field' => 'disability_code',
		'other_codes'=> array('9','44','45'),
		'allow_none'=>true,
		'confirm_none'=>true
),
	'ethnicity' => array(
		'sub_title' => 'Ethnicities',
		'sub_sub_title'=>smaller('Check appropriate ethnicities based on '.AG_MAIN_OBJECT.' self-report'),
		'object' => 'ethnicity',
		'field' => 'ethnicity_code',
		'multi_fields'=>'ethnicity_date',
		'other_codes' => array('13','11','0'), // put at end of otherwise alpha-sorted list
		'allow_none' => false
));

?>
