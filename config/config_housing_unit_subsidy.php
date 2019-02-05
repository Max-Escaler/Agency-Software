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

$engine['housing_unit_subsidy']=array(
'show_add_link'=>true,
					'cancel_add_url'=>'housing_menu.php',
						  'perm'=>'housing_admin',
						  'perm_list'=>'any',
						 'list_fields'=>array(
									    'housing_unit_subsidy_date',
									    'housing_unit_subsidy_date_end',
									    'housing_unit_code',
									    'unit_subsidy_amount',
									    'fund_type_code'),
						 'list_order'=>array('housing_unit_subsidy_date'=>true,
									   'housing_unit_code'=>false),
						 'fields'=>array(
								     'housing_project_code'=>array(
														    'java'=>
														    array(
															    'on_event'=>
															    array(
																    'populate_on_select'=>
																    array('populate_field'=>'housing_unit_code',
																	    'table'=>'housing_unit_current')
																    )
															    )
														    ),
								     'housing_unit_code'=>array(
													  'data_type'=>'lookup',
													  'lookup'=>array(
																'table'=>'housing_unit',
																'value_field'=>'housing_unit_code',
																'label_field'=>'housing_unit_code'),
													  'show_lookup_code'=>'CODE',
													  'is_html'=>true,
													  'value'=>'link_unit_history($x,true,false)',
													  'label'=>'Unit'),
								     'address_1'=>array(
												'valid'=>array('($rec["housing_project_code"]!=="SCATTERED" && be_null($x))
														   || ($rec["housing_project_code"]=="SCATTERED" && !be_null($x))'=>
														   'Field address 1: required only for Scattered Site Units'
														   ),
												'comment'=>'Scattered Site Only'),
								     'address_2'=>array(
												'valid'=>array('($rec["housing_project_code"]!=="SCATTERED" && be_null($x))
														   || ($rec["housing_project_code"]=="SCATTERED")'=>
														   'Field address 2: required only for Scattered Site Units'
														   ),
												'comment'=>'Scattered Site Only'),
								     'city'=>array(
												'valid'=>array('($rec["housing_project_code"]!=="SCATTERED" && be_null($x))
														   || ($rec["housing_project_code"]=="SCATTERED" && !be_null($x))'=>
														   'Field city: required only for Scattered Site Units'
														   ),
												'comment'=>'Scattered Site Only'),
								     'state'=>array(
												'valid'=>array('($rec["housing_project_code"]!=="SCATTERED" && be_null($x))
														   || ($rec["housing_project_code"]=="SCATTERED" && !be_null($x))'=>
														   'Field state: required only for Scattered Site Units'
														   ),
												'comment'=>'Scattered Site Only'),
								     'zipcode'=>array(
												'valid'=>array('($rec["housing_project_code"]!=="SCATTERED" && be_null($x))
														   || ($rec["housing_project_code"]=="SCATTERED" && !be_null($x))'=>
														   'Field ZIP Code: required only for Scattered Site Units'
														   ),
												'comment'=>'Scattered Site Only'),
								     )
						 );
?>
