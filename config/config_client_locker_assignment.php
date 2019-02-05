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


$engine['client_locker_assignment'] = array(
							  'perm'=>'shelter',
					   'list_fields'=>array('client_locker_assignment_date','client_locker_assignment_date_end',
									'client_locker_code','combination'),
					   'valid_record'=>array('$action=="edit" or sql_num_rows(get_generic(client_filter($rec["client_id"]),"","","client_locker_assignment_current"))==0'=>'Client already has a locker assignment',
									 '$action=="edit" or client_locker_priority($rec["client_id"])===true'=>'Client doesn\'t have locker priority'),
					   'fields'=>array(
								 'client_locker_assignment_date'=>array('display_edit'=>'display'),
								 'client_locker_assignment_date_end'=>array(
															  'valid'=>array('days_interval($rec["client_locker_assignment_date"],$x)<=90'=>'{$Y} exceeds 90 day locker assignment limit')

															  ),
								 'renewal_date_1'=>array('display_add'=>'hide',
												 'data_type'=>'date_past',
												 'comment'=>'use date client actually renewed on, not a future date',
												 'valid'=>array('be_null($x) or (!be_null($x) and (dateof($x,"SQL") > dateof($rec["client_locker_assignment_date"],"SQL")) and dateof($x,"SQL") <= dateof($rec["client_locker_assignment_date_end"],"SQL"))'=>'{$Y} must be greater than the Locker Assignment Date (and less than the end date)')
												 ),
								 'renewal_date_2'=>array('display_add'=>'hide',
												 'data_type'=>'date_past',
												 'comment'=>'use date client actually renewed on, not a future date',
												 'valid'=>array('be_null($x) or (!be_null($x) and !be_null($rec["renewal_date_1"]) and dateof($x,"SQL") > dateof($rec["renewal_date_1"],"SQL") and dateof($x,"SQL") <= dateof($rec["client_locker_assignment_date_end"],"SQL"))'=>'{$Y} must be greater than Renewal Date 1 (and less than the end date), and Renewal Date 1 must be filled in first.')
												 )
								 )
					   );
																   
?>