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

// Edit this line to change the label of disability (e.g., "Special Need")
$noun='Special Need';

$Noun=ucfirst($noun);
$engine['disability'] = array(
		'singular'=>$noun,
		'list_fields' => array('disability_code','disability_date','disability_date_end','added_by'),
		'widget'=>array(
				    'add'=>true,
				    'edit'=>true,
				    'style'=>'one_of_each',
				    'key'=>'disability_code',
				    'fixed'=>array('client_id'),
				    'required_fields'=>array('disability_date'),
				    'optional_fields'=>array('comment')
				    ),
		'fields' => array(
					'disability_id'=>array(
									 'label'=>$Noun . ' ID'),
					'disability_date'=>array(
									 'label'=>$Noun . ' Date',
								     'label_list'=>'Date',
									 'default'=>'NOW'),
					'disability_date_end'=>array(
									     'label'=>$Noun . ' End Date',
									     'label_list'=>'End Date',
									     'display_add'=>'hide'),
					'disability_code' => array(
									     'label'=>$Noun . ' Code',
									'require_comment_codes' => array('9','44','45'),
									   'display' => 'display',
									   'display_add' => 'regular',
									   ),
					'source'=>array(
							    'display'=>'display',
							    'post'=>false),
					)
		);
?>
