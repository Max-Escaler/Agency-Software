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

$engine['elevated_concern_note'] = array(
						  'perm' => 'clinical,my_client_project',
						  'object_union' => array('log','client_note','service_heet','service_cd','service_ir','service_housing'),
						  'list_fields' => array('elevated_concern_note_id','elevated_concern_note_date','note_by','note_text'),
						  'list_order' => array('elevated_concern_note_date'=>true),
						  'list_hide_view_links'=>true,
						  'add_link_show' => false,
						  'fields' => array(
									  'elevated_concern_note_id'=>array(
														   'data_type'=>'table_switch',
														   'table_switch'=>array(
																		 'identifier'=>'::'
																		 ),
														   'label' => 'Type',
														   'value_format_list' => 'smaller($x,2)'
														   ),
									  'elevated_concern_note_date' => array('data_type' => 'timestamp',
															 'value_format_list' => 'smaller($x)'),
									  'note_text' => array('is_html' => true,
												     'value' => 'elevated_concern_note_text($x,$rec)')
									  
									  )
						  );
?>
