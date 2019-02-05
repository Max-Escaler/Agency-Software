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

$engine["staff"] = array(
		"plural" => "staff",
		"perm" => "admin",
		'perm_list'=>'any',
		'perm_view'=>'any',
		'list_fields'=>array('is_active','staff_photo','staff_id','staff_summary'),
		'list_order'=>array('is_active'=>true),
		"title" => 'ucwords($action) . "ing Staff record for " . staff_link($rec["staff_id"])',
		"title_add"=>'ucwords($action) . "ing a new staff record."',
		"cancel_add_url"=>AG_ADMIN_URL,
		'child_records' => array('alert',
						 'alert_notify',
						 'permission'),
		'fn' => array('get'=>'generic'),
		'use_table_post_edit' => true,
		"fields" => array(
				'is_active' => array('label'=>'Active?'),
				"staff_id" => 
					array( "label" => "Staff (ID #)",
							"display" => "display",
							"display_add" => "hide",
							"post_add" => false,
							"null_ok" => true
							),
				"username_unix" => array(
								 'add_query_modify_condition'=>array('be_null($x)'=>'ENGINE_UNSET_FIELD'),
								 'edit_query_modify_condition'=>array('be_null($x)'=>'ENGINE_UNSET_FIELD'),
								 'valid'=>array('be_null($x) || $x !== $rec["username"]'=>'Only set {$Y} if different from username.') ),
				'staff_email'=>array(
							   'value'=>'$x ? hlink("mailto:%20".$x,$x) : $x',
							   'is_html'=>true,
							   'add_query_modify_condition'=>array('1==1'=>'ENGINE_UNSET_FIELD'), //always unset
							   'edit_query_modify_condition'=>array('1==1'=>'ENGINE_UNSET_FIELD'), //always unset
							   'valid'=>array(
/*
'!strstr($x,"@")'=>
	'The \'@\' symbol is currently not allowed in this field. Contact System Administration if you REALLY need to use it :)',
*/										'be_null($x) || ( ($x !== $rec["username_unix"]) &&($x !== $rec["username"]))'=>'Only set {$Y} if different from username_unix and username')),
				'pgp_key_public'=>array(
								'display' => 'hide'),
				'staff_photo'=>array('display'=>'hide',
							   'display_list'=>'display',
							   'label_list'=>'Staff Photo',
							   'data_type'=>'html',
							   'value'=>'staff_photo($rec["staff_id"],0.6)'),
				'staff_summary'=>array('display'=>'hide',
							     'display_list'=>'display',
							     'data_type'=>'html',
							     'value_list'=>'staff_summary($rec)'
							     )
				)
		
		);
?>
