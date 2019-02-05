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

$engine['donor_note']=array(
				    'list_fields'=>array('added_at','note','added_by'),
	 'fields'=>array(
			     'staff_id'=>array(
						     'default'=>'$GLOBALS["UID"]'),
			     'is_front_page'=>array(
							    'label'=>'Display on Donor Page?',
							    'comment'=>'Select "No" to archive this note (i.e. remove from prominent display)'),
			     'agency_project_code'=>array('null_ok'=>true,
								  'display'=>'display',
								  'display_add'=>'hide',
								  'post'=>false),
			     'agency_program_code'=>array('null_ok'=>true,
								  'display'=>'display',
								  'display_add'=>'hide',
								  'post'=>false),
			     'staff_position_code'=>array('null_ok'=>true,
								  'display'=>'display',
								  'display_add'=>'hide',
								  'post'=>false)
			     )
	 );
		
?>