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

$engine['staff_key_assign'] = array(
						'list_fields'=>array('agency_key_code',
									   'staff_key_assign_date',
									   'staff_key_assign_date_end',
									   'key_disposition_code'),
						'fields'=>array(
								    'key_serial_number'=>array(
													 'comment'=>'Required for Key Card assignments',
													 'valid'=>array('(substr($rec["agency_key_code"],0,4)=="CARD" && !be_null($x)) ||
															    (substr($rec["agency_key_code"],0,4)!=="CARD" && be_null($x))'=>
															    'Key Serial Number: Specify a serial number only for Key Card assignments')
													 ),
								    'agency_key_code'=>array(
												   'label'=>'Key Type',
												   'show_lookup_code'=>'BOTH'
												   ),
								    'staff_key_assign_date'=>array(
													     'label'=>'Issue Date'),
								    'staff_key_assign_date_end'=>array(
														   'label'=>'Disposition Date'),
								    'assigned_by'=>array(
												 'default'=>'$GLOBALS["UID"]')
								    )
						);




?>
