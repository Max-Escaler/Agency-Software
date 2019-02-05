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

$engine['housing_key_assign']=array(
						'singular'=>'Housing Key Assignment',
						'list_fields'=>array(
									   'key_assign_date',
									   'key_assign_date_end',
									   'agency_project_code',
									   'housing_key_code',
									   'key_assign_reason_code'
									   ),
						'fields'=>array(
								    'key_assign_date'=>array(
												     'label_list'=>'Date'
												     ),
								    'key_assign_date_end'=>array(
													   'label_list'=>'Date End'
													   )
								    )
				     );
?>