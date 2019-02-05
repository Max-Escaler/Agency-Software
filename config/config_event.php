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

$engine['event'] = array(
			'perm'=>'heet',
			 'add_another'=>true,
			 'singular'=>'HEET Event',
			 'list_fields'=>array('event_date','event_quantity','event_type_code'),
			 'fields' => array(
						'event_date' => array( 'label' => 'Date of Event',
								'comment' => 'If single event, enter date here.  If multiple events, enter last date here, and enter first date in field below.'),

					   'minimum_date'=>array(
								 'label'=>'Multiple Events Start Date',
								 'comment'=>'If multiple events, enter starting date here.',
								 'valid' =>array('dateof($x,"SQL") <= dateof($rec["event_date"],"SQL")'=>
										 'Multiple Events Start Date must be less than the Event Date')
								 )
					   )
			 );
?>
