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


$engine['application_housing_other'] = array('singular'=>'Non-' . org_name('short') . ' Housing Application',
							   'perm_add' => 'connect,ir',
							   'perm_edit' => 'connect,ir',
							   'perm' => 'any',
							   'list_fields' => array('application_housing_other_date','application_status_code','housing_name','application_status_date'),
							   'fields' => array(
										   'application_housing_other_date' => array(
																	   'label' => 'Application Date'),
										   'referral_date' => array('valid'=>array('!be_null($x) || !be_null($rec["application_housing_other_date"])'=>'Either Application Date or {$Y} must be filled in')),
										   'application_status_code' => array('valid' => array('!be_null($rec["application_housing_other_date"]) || $x=="REFERRAL"'=>'{$Y} should be set to Referral if Application Date is blank')
																  )
										   )
							   );
							   

?>
