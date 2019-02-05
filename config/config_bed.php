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

$engine['bed']=array(
	     'singular'=>'shelter bed night',
	     'allow_edit'=>true,
	     'allow_delete'=>true,
	     'delete_another' => true, //allow one to delete multiple records w/o being prompted for password
		'require_delete_comment'=>false,
	     'delete_another_password_cycle' => 11, 
	     'perm'=>'any',
	     'perm_add' => 'bed_adjust',
	     'perm_edit' => 'bed_adjust',
	     'perm_delete' => 'bed_adjust',
	     'list_fields' => array('bed_date','bed_group_code'),
	     'list_order' => array('bed_date'=>true), //descending order
	     'list_columns' => 4,
	     'list_max' => 100,
	     'list_hide_numbers' => true,
	     "fields" => array( 
				'bed_group_code' => array(
    	           'show_lookup_code_list' => 'CODE'),
				'night_factor' => array(
					'default' => 1 ),
				'comments' => array(
					'default' => 'Record Added Manually'),
				       'removed_at' => array(
							'display_add' => 'hide',
							'display_edit' => 'hide'),
				       'removed_by' => array(
							 'display_add' => 'hide',
							 'display_edit' => 'hide')
	));
?>
