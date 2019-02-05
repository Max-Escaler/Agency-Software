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



$engine['sh_additional_data'] = 
	  array(
		  'singular'=> 'Safe Harbors Additional Information',
		  'fields' => array(
					  'sh_school_status_code' =>  array('lookup_order' => 'TABLE_ORDER',
											'label' => 'School Status'),
					  'highest_education_code' => array('lookup_order' => 'TABLE_ORDER'),
					  
					  'immigrant_status_code' => array('data_type'=>'lookup',
										     'lookup' => array('table' => 'l_yes_no_client',
													     'value_field'=>'yes_no_client_code',
													     'label_field'=>'description')
										     )
					  )
		  );  


?>
