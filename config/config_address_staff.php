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

$engine['address_staff'] = array(
	'singular' => 'Staff Address',
	'table_post' => 'tbl_address',
	'list_fields' => array('address_date','address_summary','address_email','address_date_end'),
	'fields' => array(
		'client_id' => array( 'display'=>'hide'),
		'address_email'=>array(
			'label'=>'Email'
		),
		'address_email2'=>array(
			'label'=>'Email2'
		)
	)
);

?>
