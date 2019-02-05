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


$engine['staff_phone'] = array('list_fields'=>array('staff_phone_date','phone_type_code','number','extension','direct_dial_number','voice_mail_number','voice_mail_extension'),
					 'fields'=>array(
							     'number'=>array('data_type'=>'phone'),
							     'direct_dial_number'=>array('data_type'=>'phone',
												   'valid'=>array(
															'be_null($x) or ($rec["phone_type_code"]=="WORK" and $rec["number"] != $x)'=>
															'Only use {$Y} for WORK numbers, if different from main number')
												   ),
							     'voice_mail_number'=>array('data_type'=>'phone',
												   'valid'=>array(
															'be_null($x) or ($rec["phone_type_code"]=="WORK" and $rec["number"] != $x)'=>
															'Only use {$Y} for WORK numbers, if different from main number')
												  )
							     )
);
?>