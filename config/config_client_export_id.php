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


$engine['client_export_id'] = array(
	'singular'=>'ID Number',
	'require_password'=>false,
    'list_fields' => array('export_organization_code', 'export_id'),
	'unique_constraints'=>array(
		array('client_id','export_organization_code'),
		array('export_id','export_organization_code'),
	),
    'fields'=> array(
		'export_id'=>array('label'=>'ID Number'),
		'export_organization_code' => array(
			'label'=>'ID Type',
			'invalid'=> array(
			    '($x == "HCH") and (!has_perm("ADMIN"))'=>'Admin perms needed for HCH IDs'
		    )
	    )
   	) 
);

?>
