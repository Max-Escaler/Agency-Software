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


$engine['staff_remote_login'] = 
     array('singular' => 'Remote Login/Access Record',
	   'perm_list' => 'any',
	   'perm_view' => 'any',
	   'perm' => 'add_remote_access',
	   'fields'=>
	   array(
		 'access_time'=>
		 array('comment'=>'This is the time access will begin on all days staff has access. Leave at 0:00 am for all-day access'),
		 
		 'access_time_end'=>  
		 array('comment'=>'This is the time access will end on all days staff has access. Leave at 11:59 pm for all-day access. Staff will not have access during times outside of the Access Time beginning and end.'
		       
		       )
		 )
	   );


?>