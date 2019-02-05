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

$engine['shelter_reg']=array(
				     'add_link_label' => 'Register this client for the Shelter',
				     'singular'=>'Shelter Registration',
				     'list_fields'=>array(
								  'shelter_reg_date',
								  'shelter_reg_date_end',
								  'custom1',
								  'overnight_eligible',
								  'bed_rereg_code'),
				     'fields'=>array(
							   'custom1'=>array(
										  'data_type'=>'html',
										  'label_list'=>'Priority',
										  'label'=>'Priority Summary',
										  'value'=>'priority_status_f($rec["client_id"])',
										  'value_format'=>'smaller($x)'
										  ),
							   'shelter_reg_date'=>array(
											     'default'=>'NOW',
											     'label'=>'Shelter Registration Date',
											     'label_list'=>'Start'),
							   'shelter_reg_date_end'=>array(
												   'label'=>'Shelter Registration End',
												   'label_list'=>'End'),
							   'bed_rereg_code'=>array(
											   'label'=>'Will re-register for bed'),
							   'overnight_eligible'=>array(
												 'label'=>'Eligible for overnight use?'),
							   'last_residence_ownr' => array(
												     'label'=>smaller(indent('If other, describe'))
												     ),
							   'svc_need_code'=>array(
											  'label'=>'Service Needed Reason'),
							   'priority_elderly'=>array('data_type'=>'boolean'),
							   'priority_female'=>array('data_type'=>'boolean')
							   )

				     );
?>