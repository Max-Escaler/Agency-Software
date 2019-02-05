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


$engine['criminal_history'] = array(
						'add_another'=>true,
						'add_another_and_remember'=>true,
						'perm'=>'housing',
						'list_fields'=>array('criminal_history_report_source_code','criminal_history_report_date','has_criminal_history'),
						'fields'=>array(
								    'client_id' => array('add_another_remember'=>true,
												 'add_main_objects'=>true),
								    'criminal_history_report_source_code' => array('add_another_remember'=>true),
								    'criminal_history_retrieval_law_code' => array('add_another_remember'=>true),
								    'criminal_history_report_date' => array('add_another_remember'=>true),
								    'has_criminal_history'=>array(
													    'add_another_remember'=>true,
													    'label'=>'Has Criminal History',
													    'comment'=>'(according to report)',
													    'java'=>array(
																'on_event'=>array(
																			'disable_boolean'=>
																			array(
																				'arrest_date',
																				'washington_county_code',
																				'court_code',
																				'criminal_offense_code',
																				'criminal_offense_code_other',
																				'disposition_date',
																				'criminal_disposition_code',
																				'crime_type_code',
																				'fine_amount',
																				'fine_amount_suspended',
																				'incarceration_length',
																				'incarceration_length_suspended',
																				'probation_length'
																				)
																	   )
																)
													    ),
								    'washington_county_code'=>array('lookup_order'=>'TABLE_ORDER'),
								    'fine_amount'=>array('data_type'=>'currency'),
								    'fine_amount_suspended'=>array('data_type'=>'currency'),
								    'probation_length' => array('label' => 'Probation/Community Supervision Length')
								    )
);
?>
