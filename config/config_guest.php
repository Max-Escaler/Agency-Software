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

// This is used for the label for the bar status field
// It will draw on what is defined for bar.
// However, if you change that in bar, and are updating
// the whole engine array, you will need to update guest
// a second time to pick up that change.
$b_def=get_def('bar');
$bar_noun=ucfirst(orr($b_def['noun'],'bar'));
$status_field=$bar_noun . ' Status';

$engine['guest'] = array(
	'use_table_post_edit' => true,
	'child_records' => array(
		'guest_identification','guest_authorization','guest_visit','bar'
	),
	//'list_fields'=>array('name_full','dob','name_alias','photo','tenants'),
	'list_fields'=>array('name_full','dob','name_alias','bar_status','photo'),
	'object_label'=>'sql_lookup_description($id,"guest","guest_id","name_full")',
	/* Registration (adding) configuration */
	'registration'=>array(
		'search_fields'=>array('name_last','name_first','dob'),
		//'match_result_order'=> '"rank_client_search_results(name_last,name_first,name_alias,ssn,dob,"'
		// confusingly, I stuffed name_alias as a dummy for nonexistent placeholder ssn
		'match_result_order'=> '"rank_client_search_results(name_last,name_first,name_alias,name_alias,dob,"'
			. '. enquote1(sqlify($rec["name_last"]))'
			. '. ","'
			. '. enquote1(sqlify($rec["name_first"]))'
			. '. ","'
			. '. enquote1(sqlify($rec["ssn"]))'
			. '. ","'
			. '. enquote1(sqlify(orr($rec["dob"],"2099-01-01")))'
			. '. ")"'
	),
	'quick_search'=>array(
		'match_fields'=>array('name_full'),
		'match_fields_date'=>array('dob')
	),
	'fields' => array(
		'guest_photo' => array(
			'data_type' => 'attachment',
			'display_view'=>'hide',
			'display_list'=>'hide'
		),
		'photo' => array(
			'value' => 'guest_photo($rec["guest_id"])',
			'value_list' => 'guest_photo($rec["guest_id"],50,50)',
			'is_html'=>true,
			'display_add' => 'hide',
			'display_edit' => 'hide'
		),
		'name_last'=>array('label'=>'Last Name'),
		'name_first'=>array('label'=>'First Name'),
		'name_alias'=>array('label'=>'Alias'),
		'name_middle'=>array('label'=>'Middle Name'),
		'name_full'=>array(
			'label'=>'Name',
			'value_format'=>'guest_link($rec["guest_id"])'
		),
		'bar_status' => array( // See note at top of file re: updating
			'label'=>$status_field,
			'value'=>'bar_status_f(array("guest_id"=>$rec["guest_id"]),"long")',
			'value_list'=>'bar_status_f(array("guest_id"=>$rec["guest_id"]))',
			'is_html'=>true
		),
		/*
		'tenants'=>array(
			'is_html'=>true,
			'data_type'=>'array',
			//'value'=>'implode(oline(),array_walk(array_fetch_column(get_generic(array("guest_id"=>$rec["guest_id"]),NULL,NULL,"guest_authorization_current"),"client_id"),"client_link"))'
			//'value'=>'array_walk(array_fetch_column(get_generic(array("guest_id"=>$rec["guest_id"]),NULL,NULL,"guest_authorization_current"),"client_id"),"client_link")'
			// FIXME: This isn't working
			'value'=>'be_null($x) ? null : implode(oline(),array_walk(array_fetch_column(get_generic(array("guest_id"=>$rec["guest_id"]),NULL,NULL,"guest_authorization_current"),"client_id"),create_function(\'&$v,$k\', \'$v = client_link($v);\')))'
		),
*/
		'client_id' => array(
			'display'=>'hide'
		),
		// Comment these three lines out if you are not keeping ID in a separate table
		'identification_number'=>array('display'=>'hide'),
		'identification_expiration_date'=>array('display'=>'hide'),
		'identification_type_code'=>array('display'=>'hide'),

		'identification_status' => array(
			// Hide this field if you are not keeping identification in a separate table
			// 'display'=>'hide',
			'value' => '$x ? elink("guest_identification",$x,"on file") : "not on file" . add_link("guest_identification","Add ID now",NULL,array("guest_id"=>$rec["guest_id"]))',
			'is_html'=>true
		)
	)
);


?>
