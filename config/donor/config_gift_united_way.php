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

$engine['gift_united_way'] = array(
					     'singular'=>'United Way Gift',
					     'fields'=>array(
								   'donor_id' => array(
											     'add_main_objects'=>true,
											     'edit_main_objects'=>true,
											     'display'=>'regular',
											     'add_another_remember'=>true),
								   'gift_united_way_form_code'=>array('data_type'=>'lookup',
														  'lookup'=>array('table'=>'l_gift_cash_form',
																	'value_field'=>'gift_cash_form_code',
																	'label_field'=>'description'))
								   )
					     );

?>
