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

$engine['entry'] = array(
				 'singular'=>'entry',
				 'plural'=>'entries',
				 'list_fields' => array('entered_at','entry_location_code'),
				 'list_order' => array('entered_at'=>true),
				 'list_max'=>20,
				 'list_columns'=>2,
				 'fields' => array(
							 'entered_at'=> array(
										    'value_format_list' => 'datetimeof($x,"US","TWO")',
										    'label_format_list'=>'smaller($x,2)'
										    ),
							 'entry_location_code'=>array(
												  'label_format_list'=>'smaller($x,2)',
												  'value_format_list'=>'smaller($x,2)')
							 )
				 );

?>
