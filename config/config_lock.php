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


$engine['lock'] = array(
				'perm'=>'shelter_locker',
				'list_fields'=>array('lock_code','combination_1','combination_2','combination_3','combination_4','combination_5','dial_code'),
				'fields'=>array(
						    'combination_1'=>array(
										 'valid'=>array('preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/",$x)'=>
												    '{$Y} must be of the form xx-xx-xx where x is between 0 and 9')
										 ),
						    'combination_2'=>array(
										 'valid'=>array('preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/",$x)'=>
												    '{$Y} must be of the form xx-xx-xx where x is between 0 and 9')
										 ),
						    'combination_3'=>array(
										 'valid'=>array('preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/",$x)'=>
												    '{$Y} must be of the form xx-xx-xx where x is between 0 and 9')
										 ),
						    'combination_4'=>array(
										 'valid'=>array('preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/",$x)'=>
												    '{$Y} must be of the form xx-xx-xx where x is between 0 and 9')
										 ),
						    'combination_5'=>array(
										 'valid'=>array('preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/",$x)'=>
												    '{$Y} must be of the form xx-xx-xx where x is between 0 and 9')
										 ),
						    'dial_code'=>array(
									     'valid'=>array('preg_match("/^[A-Z]{1}-[0-9]{1,2}$/",$x)'=>
												  '{$Y} must be of the form A-1')
									     )
						    )
				);
?>
						    