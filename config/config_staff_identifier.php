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


$engine['staff_identifier'] = 
    array(
          'perm' => 'pto_admin', 
          'list_fields'=>array('staff_identifier_type_code', 'staff_identifier_value'),
          'fields' => array(
                            'staff_identifier_type_code' => array('default' => 'PAYROLL',
                                                                  'display' => 'display'),
                            
                            'staff_id' => array(
								'display_edit' => 'display', 	
								'valid' => array(
                                                                 '($action =="edit" or sql_num_rows(get_generic(array(
"staff_id"=>$x), "", "", "staff_identifier"))==0)'
                                                                 => 'This staff already has a MIP identifier. You can edit that record from their staff page')
								
								)
				    ) 
	    );

?>