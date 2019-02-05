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

global $calendar_url;
$engine['calendar_appointment'] = array(
						    'enable_staff_alerts_view'=>true,
						    'allow_delete'=>true,
					    'cancel_add_url'=>$calendar_url,
					    'post_with_transactions'=>true,
					    'verify_on_post'=>true,
					    'prepend_finished_add_eval'=>'Calendar::link_calendar("",$rec["event_start"],"Go to calendar")',
					    'list_fields'=>array('event_start','event_length','description','calendar_id','calendar_appointment_resolution_code'),
					    'list_order'=>array('event_start'=>true),
					    'add_link_show'=>false,
					    'fields'=>array(
								  'calendar_appointment_id'=>array(
													     'label'=>'Appointment ID'),
								  'calendar_id'=>array('display'=>'display',
											     'is_html'=>true,
											     'label'=>'Calendar',
											     'value'=>'Calendar::link_calendar($x)',
											     'value_format_list'=>'smaller($x,2)',
												'lookup'=>array(),
												'data_type'=>'varchar'
								),
												
								  
								  'client_id'=>array(
											   'add_main_objects'=>true,
											   'edit_main_objects'=>true,
											   'display_edit'=>'display'
											   ),
								  'event_start'=>array(
											     'label'=>'Appointment Start',
											     'label_list'=>'Start',
											     'value_format_view'=>'datetimeof($x,"US","TWO")',
											     'value_format_list'=>'datetimeof($x,"US","TWO")'),
								  'event_end'=>array(
											   'label'=>'Appointment End',
											   'label_list'=>'End',
											   'value_format_view'=>'datetimeof($x,"US","TWO")',
											   'value_format_list'=>'datetimeof($x,"US","TWO")',
											   'valid'=>array('datetimeof($x,"SQL") > datetimeof($rec["event_start"],"SQL")'
														=>'End date must be greater than start date!')
											   ),
								  'event_length'=>array(
												'value_format_list'=>'smaller($x,2)'),
								  'description'=>array('comment'=>'(optional for Medical Staff Schedules)',
											 'valid'=>array('!be_null(trim($x)) || !be_null($rec["client_id"])'
													    =>'Either enter a description or a '.AG_MAIN_OBJECT.' (or both)')
											     ),
								  'comments'=>array('display_add'=>'hide'),
	                              'event_repeat_type_code'=>array('type'=>'virtual','display'=>'hide','display_add'=>'regular'),
								  'repeat_until'=>array('type'=>'virtual','display'=>'hide','display_add'=>'regular','data_type'=>'date', 'valid'=>array('be_null($x) || (dateof($x,"SQL") > dateof($rec["event_start"],"SQL"))'=>'Repeat until date must be greater than event date','dateof($x,"SQL")<=dateof(next_month("now",12),"SQL")'=>'Can\'t schedule out more than a year from today')),
								  'calendar_appointment_resolution_code'=>array('display_add'=>'hide',
																'show_lookup_code_list'=>'CODE',
																'value_format'=>'be_null($x) ? $x : bold(red($x))',
																'label'=>'Cancelled/Missed Status'),
								   ),
								'valid_record' => array( '!(be_null($rec["event_repeat_type_code"]) xor be_null($rec["repeat_until"]))'=>'For repeating events, set both type and repeat until fields.  For one-time events, leave both fields blank'),
								'confirm_record' => array( '(be_null($rec["event_repeat_type_code"])) and (be_null($rec["repeat_until"]))'=>'You have selected to have this event repeat.  Are you sure you want to do this?')

					    );

?>
