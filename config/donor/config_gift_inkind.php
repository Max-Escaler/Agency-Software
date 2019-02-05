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

$engine['gift_inkind']=
array(
	'allow_delete' =>true,
	'singular'=>'inkind gift',
	'list_fields'=>array('gift_inkind_date','inkind_item_code','quantity','value_total'),
	'list_order'=>array('gift_inkind_date'=>true),
	'fields'=>array(
			    'donor_id' => array(
							'add_main_objects'=>true,
							'edit_main_objects'=>true,
							'display'=>'regular'),
			    'value_total'=>array('data_type'=>'currency')
			    )
);
?>
