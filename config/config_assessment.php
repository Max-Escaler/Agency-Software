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

$engine['assessment'] = array(
					'singular'=>'Shelter Assessment',
		'title' => 'ucwords($action) . "ing Assessment for " . client_link($rec["client_id"])',
					'allow_edit'=>false,
		'list_fields' => array('assessed_at','total_rating','assessed_by'),
		'fields' => array(
				'assessment_id' => 
					array( 'label' => 'Assessment ID #',
							),
							
				'client_id' =>
					array('label' => 'Client Name',
							'data_type' => 'client',
							'display' => 'display',
					),
				'survival_rating' => 
					array('label' => 'Survival Skills',
							'comment' => '(0-4)',
							'data_type' => 'integer',
							'display' => 'regular',
							'valid' => array('($x<5) && ($x>=0)'=>
								'Survival Skills must be between 0 and 4')
								// uses $x for field value
				),
				'basic_rating' => 
					array(	'label' => 'Basic Needs',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=>
								'Basic Needs must be between 0 and 4')
					),
				'physical_rating' => array('label' => 'Physical/Medical',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=>
								'Physical Rating must be between 0 and 4')
				),
				'organization_rating' => array('label' => 'Organization/Orientation',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=> // uses $x for field value
								'Organization/Orientation must be between 0 and 4')
				),
				'mh_rating' => array('label' => 'Mental Health',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=> // uses $x for field value
								'Mental Health rating must be between 0 and 4')
				),
				'cd_rating' => array('label' => 'Substance Use',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=> // uses $x for field value
								'Substance Use rating must be between 0 and 4')
				),
				'communication_rating' => array('label' => 'Communication',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=> // uses $x for field value
								'Communication Rating must be between 0 and 4')
				),
				'socialization_rating' => array('label' => 'Social Behaviors',
							'data_type' => 'integer',
							'comment' => '(0-4)',
							'valid' => array('($x<5) && ($x>=0)'=> // uses $x for field value
								'Social Behaviors Rating must be between 0 and 4')
				),
				'homelessness_rating' => array('label' => 'Homelessness',
							'data_type' => 'integer',
							'comment' => '(0-2)',
							'valid' => array('($x<3) && ($x>=0)'=> // uses $x for field value
								'Homelessness Rating must be between 0 and 2')
				),
				'comments' => array( 'data_type' => 'text'),

				'assessed_by' => array('label' => 'Assessment Performed by',
						       'label_list' => 'Performed By',
								'data_type' => 'staff',
							     'valid'=>array('is_human_staff($x)'=>'Invalid staff for {$Y}')
							     ),
				'assessed_at' => array('label' => 'Date of Assessment',
						       'label_list' => 'Date',
								// date options for data_type include date, date_past & date_future
								'data_type' => 'date_past'),
								
				'sys_log' => array( 'display' => 'hide'),
				'total_rating' => array('label' => 'Total Rating', 
										'display' => 'hide',
							'display_edit' => 'display',
										'display_view' => 'display',
										'value' => 	'$rec["survival_rating"]
														+$rec["basic_rating"]
														+$rec["physical_rating"]
														+$rec["organization_rating"]
														+$rec["mh_rating"]
														+$rec["cd_rating"]
														+$rec["communication_rating"] 
														+$rec["socialization_rating"]
														+$rec["homelessness_rating"]',
										'post' => false,
										'null_ok' => true,
										'value_format' => 'bigger(bold(red($x)))'
										),
				)
		);

?>
