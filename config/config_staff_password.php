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


$engine['staff_password'] = array('perm'         => 'super_user',
					    'perm_add'     => 'self,password,staff_new_password',
					    'perm_edit'    => 'self,password',
					    'perm_view'    => 'password',
					    'allow_add'    => false,
					    'allow_edit'   => false,
					    'allow_delete' => false,
					    'fields' => array(
								    'staff_id' => array('display' => 'display'),
								    'staff_password'     => array('never_to_form' => true,
													    'value' => 'XXXXX'),
								    'staff_password_md5' => array('never_to_form' => true,
													    'value' => 'XXXXX')
								    )
					    );

?>