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

$engine['reference'] = array(
	// 'allow_add'=>false,
	'add_link_show'=>false,
	// 'list_hide_view_links' => true,
	'perm'=>'any',
	'list_fields'=>array('custom1','custom2'),
	'list_order'=>array('added_at'=>true),
	'fields'=>array(
		'custom1'=>array(
			'data_type'=>'html',
			'display'=>'hide',
			'display_list'=>'display',
			'label'=>'From',
			'value'=>'($rec["from_table"] == "client" )
				? client_link($rec["from_id"])
				: ($rec["from_table"] == "staff" )
					?  staff_link($rec["from_id"])
					: ($rec["from_table"] == "log" ) 
						?  log_link($rec["from_id"])
						: elink($rec["from_table"],$rec["from_id"],$rec["from_table"] . " " . $rec["from_id"])'),
			'custom2'=>array(
				'data_type'=>'html',
				'display'=>'hide',
				'display_list'=>'display',
				'label'=>'To',
				'value'=>'($rec["to_table"] == "client" ) 
					? client_link($rec["to_id"])
					: (($rec["to_table"] == "staff" ) 
						?  staff_link($rec["to_id"])
						: elink($rec["to_table"],$rec["to_id"],$rec["to_table"] . " " . $rec["to_id"]))')
		)
	);
?>
