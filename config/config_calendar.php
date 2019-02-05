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

$engine['calendar'] = array(
				    'perm'=>'calendar_admin',
					'child_records'=>array('calendar_appointment'),
				    'fields'=>array(
							  'calendar_id'=>array(
										     'display'=>'display',
										     'display_add'=>'hide',
										     'value'=>'Calendar::link_calendar($x)',
										     'is_html'=>true
										     ),
							  'staff_id'=>array(
										  'display_edit'=>'display'),
							  'inanimate_item_code'=>array(
												 'display_edit'=>'display',
												 'valid'=>array('(!be_null($x) && be_null($rec["staff_id"]))
														    ||(be_null($x) && !be_null($rec["staff_id"]))'=>
														   'Choose <i>either</i> staff or inanimate item.')
												 ),
							  'standard_lunch_hour_start'=>array('time_drop_list'=>true,
													 'comment'=>'set start=end for no standard lunch block'),
							  'standard_lunch_hour_end'=>array('time_drop_list'=>true),
							  'day_0_start'=>array('time_drop_list'=>true,
										     'label'=>'Sunday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_0_end'=>array('time_drop_list'=>true,
										   'label'=>'Sunday End',
										   'valid'=>array('$x>=$rec["day_0_start"]'=>'{$Y} must be greater or equal to day_0_start')),
							  'day_1_start'=>array('time_drop_list'=>true,
										     'label'=>'Monday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_1_end'=>array('time_drop_list'=>true,
										   'label'=>'Monday End',
										   'valid'=>array('$x>=$rec["day_1_start"]'=>'{$Y} must be greater or equal to day_1_start')),
							  'day_2_start'=>array('time_drop_list'=>true,
										     'label'=>'Tuesday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_2_end'=>array('time_drop_list'=>true,
										   'label'=>'Tuesday End',
										   'valid'=>array('$x>=$rec["day_2_start"]'=>'{$Y} must be greater or equal to day_2_start')),
							  'day_3_start'=>array('time_drop_list'=>true,
										     'label'=>'Wednesday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_3_end'=>array('time_drop_list'=>true,
										   'label'=>'Wednesday End',
										   'valid'=>array('$x>=$rec["day_3_start"]'=>'{$Y} must be greater or equal to day_3_start')),
							  'day_4_start'=>array('time_drop_list'=>true,
										     'label'=>'Thursday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_4_end'=>array('time_drop_list'=>true,
										   'label'=>'Thursday End',
										   'valid'=>array('$x>=$rec["day_4_start"]'=>'{$Y} must be greater or equal to day_4_start')),
							  'day_5_start'=>array('time_drop_list'=>true,
										     'label'=>'Friday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_5_end'=>array('time_drop_list'=>true,
										   'label'=>'Friday End',
										   'valid'=>array('$x>=$rec["day_5_start"]'=>'{$Y} must be greater or equal to day_5_start')),
							  'day_6_start'=>array('time_drop_list'=>true,
										     'label'=>'Saturday start',
										     'comment'=>'set start=end to block day completely'),
							  'day_6_end'=>array('time_drop_list'=>true,
										   'label'=>'Saturday End',
										   'valid'=>array('$x>=$rec["day_6_start"]'=>'{$Y} must be greater or equal to day_6_start'))
							  )
				    );

?>
