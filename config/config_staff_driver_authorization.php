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


$engine['staff_driver_authorization'] = array('singular'=>'Driver Authorization Record',
					      'single_active_record'=>true,
					      'list_fields'=>array('drivers_license_expiration_date',
								   'current_drivers_license_on_file',
								   'insurance_expiration_date',
								   'current_insurance_on_file'),
					      'fields'=>array(
							      'staff_id'=>array('display'=>'display'),
							      'drivers_license_on_file'=>array(
											       'valid'=>array('sql_true($x) 
																		  xor be_null($rec["drivers_license_expiration_date"])'
																		  =>'Drivers license must be on file if an expiration date is set'
																		  )
															     ),
										  'drivers_license_expiration_date'=>array(
																	 'comment'=>'Must match date of driver\'s license on file.'),
										  'insurance_on_file'=>array('valid'=>array('sql_true($x)
																	  xor be_null($rec["insurance_expiration_date"])'
																	  =>'Insurance must be on file if expiration date is set')
														     ),
										  'insurance_expiration_date'=>array(
																 'comment'=>'Must match date of insurance on file.'
																 ),
										  'staff_driver_authorization_date_end'=>array('display_add' => 'hide')
										  )
);

?>



