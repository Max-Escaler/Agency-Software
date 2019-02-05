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


$engine['alert_notify'] = array(
  /* FIXME: self perm not working. Not strictly needed, as valid on staff_id enforces own only */
  //'perm'=>'self',
  'title_format' =>'bigger(bold($x)) . " (" . smaller(link_wiki_public("alert_notifications","Help with Alert Notifications")).")"',
  'singular' =>'Alert Notify Record',
  'add_another' => true,
  'list_fields' => array('alert_notify_date','alert_object','alert_notify_action_code','alert_notify_basis'),
  'fields' => array(
	  'staff_id'=>array( 
            	'row_before'=>'oline(bigger(bold("Who to notify"))) . italic("All of these conditions must be met")',
		'valid'=>array( 'has_perm("super_user") or ($x==$GLOBALS["UID"])'=>'You can only set notifications for yourself'),
		'confirm'=>array( '!be_null($x)'=>'You are setting a group alert notification.  Do so with caution',
			'be_null($x) or ($x ==$GLOBALS["UID"])'=>'You are setting an alert notification for another staff member.  Make sure this is correct')),
	  'alert_object'=>array(
            	'row_before'=>'oline(bigger(bold("Basic Setup")))',
		'data_type'=>'lookup',
		'lookup' => array(
			'table'=>'alert_notify_enabled_objects',
			'value_field'=>'alert_object_code',
			'label_field'=>'description'
			)
		),
		'alert_notify_field'=>array(
            		'row_before'=>'oline(bigger(bold("Advanced triggering conditions")))'
								  .'. oline(smaller(bold("(uses logical AND; e.g., all field/value pairs must match, or be blank)")))',
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'alert_notify_field2'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'alert_notify_field3'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'alert_notify_field4'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),

		'match_program_field' => array( 
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object'),
			'row_before' => 'oline(bold(smaller("These fields in triggering object matched with Staff record")))'),
		'match_position_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'match_project_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'match_shift_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'match_facility_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'match_supervisor_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'match_supervisees_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object')),
		'match_assignments_field'=>array(
			'valid'=>array('be_null($x) or is_field($rec["alert_object"],$x)'=>'{$x} is not a field for this object'),
        	'row_before_edit'=>'oline(bold(smaller("or ' . ucfirst(AG_MAIN_OBJECT) . ' field to match with case list")))' ),
		'alert_notify_reason'=>array('null_ok'=>false,
            		'row_before'=>'oline(bigger(bold("Purpose and Comments")))',
			'label'=>'Reason for notification?')
	)
);

?>
