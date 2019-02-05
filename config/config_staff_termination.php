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


$engine['staff_termination'] = array(
						 'perm'=>'supervisor,admin,staff_request',
						 'perm_view'=>'self,browse_staff_request',
						 'list_fields'=>array('staff_id','termination_date','staff_termination_status_code','added_by'),
						 'list_order'=>array('staff_termination_status_code'=>true,'added_at'=>true),
						 'fields'=>array(
								     'staff_termination_status_code'=>array('display_add'=>'hide',
															  'comment'=>'For IS use only')
								     )
						 );

foreach (array('email_forwarding','disposition_of_email','disposition_of_files') as $tmp_f) {
	$engine['staff_termination']['fields'][$tmp_f]['comment'] = 'Leave blank if you don\'t know what this means';
}

?>
