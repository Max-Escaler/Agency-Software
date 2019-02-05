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

$engine['client_note']=array(
	'allow_object_references'=>array('bar','client'),
    'list_fields'=>array('note','is_front_page','flag_entry_codes','added_by'),
	'fields'=>array(
		'is_front_page'=>array(
			'label'=>'Front Page?',
			'comment'=>'Select "No" to archive this note'."\n".'(i.e. remove from prominent display)',
			'value_format_list'=>'((!be_null($rec["front_page_until"])) and ($rec["front_page_until"] <= datetimeof("now","SQL"))) ? $x . " (expired)" : $x'
		),
		'flag_entry_codes' => array(
			'label'=>'Notify on Entry',
			'value_format_list'=>'(sql_true($rec["is_dismissed"]) and (!sql_false($rec["is_entry_dismissable"]))) ? "$x (dismissed)" : ((!be_null($rec["flag_entry_until"])) and ($rec["flag_entry_until"] <= datetimeof("now","SQL"))) ? $x . " (expired)" : $x',
			'data_type'=>'lookup_multi',
			'lookup_format'=>'checkbox_v',
			'lookup'=>array(
				'table' => 'l_entry_location',
				'value_field'=>'entry_location_code',
				'label_field'=>'description'
			),
			'comment'=>'Selecting a location will display note at that location when ' . AG_MAIN_OBJECT . ' enters.'
		),
		'flag_entry_until'=>array(
			'timestamp_allow_date'=>true,
			'default'=>'EVAL: next_day(dateof("now"),30)','comment'=>'No effect unless Entry Notification is specified'
		),
		'front_page_until'=>array(
			'timestamp_allow_date'=>true,
			'default'=>'EVAL: next_day(dateof("now"),30)',
			'comment'=>'No effect unless Front Page is set to Yes'
		),
		'is_dismissed'=>array(
			'display_add'=>'hide',
			'invalid'=>array( 'sql_true($x) and (!sql_true($rec["is_entry_dismissable"]))'=>'Record is not dismissable' )
		),
		'dismissed_at'=>array(
			'display_add'=>'hide',
			'invalid'=>array(
				'$x and (!sql_true($rec["is_dismissed"]))'=>'Record is not dismissed. {$Y} must be blank',
				'(!$x) and sql_true($rec["is_dismissed"])'=>'{$Y} must be filled in for dismissed records'
			)
		),
		'dismissed_by'=>array(
			'display_add'=>'hide',
			'invalid'=>array(
				'$x and (!sql_true($rec["is_dismissed"]))'=>'Record is not dismissed. {$Y} must be blank',
				'(!$x) and sql_true($rec["is_dismissed"])'=>'{$Y} must be filled in for dismissed records'
			)
		),
		'dismissed_comment'=>array(
			'display_add'=>'hide',
			'invalid'=>array(
				'$x and (!sql_true($rec["is_dismissed"]))'=>'Record is not dismissed. {$Y} must be blank',
				'(!$x) and sql_true($rec["is_dismissed"])'=>'{$Y} must be filled in for dismissed records'
			)
		)
	)
);
		
?>
