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

$engine['income']=array(
				'singular'=>'Income Record',
 				'single_active_record'=>true,
				'list_fields'=>array('annual_income',
							   'income_primary_code',
							   'income_certification_type_code',
							   'income_date',
							   'income_date_end',
							   'rent_amount_tenant',
							   'rent_date_effective'),
				'list_order'=>array('income_date'=>true),
				'label_format_list'=>'smaller($x)',
				'valid_record'=>array('be_null(current_residence_own($rec["client_id"])) || has_perm("housing")' => 'This client is housed, so housing permissions are required to add/edit income information.'), //housing permissions required for currently housed clients
				'fields'=>array(
						    'is_income_certification'=>array('label'=>'Housing Income Certification?',
												 'java'=>array(
														   'on_event'=>array(
																	   'disable_boolean'=>
																	   array(
																		   'is_sha_income_certification',
																		   'income_certification_type_code',
																		   'rent_date_effective',
																		   'rent_amount_tenant',
																		   'housing_unit_code',
																		   'fund_type_code',
																		   'rent_amount_total',
																		   'grant_number')
																	   )
														   )
												 ),
						    'is_sha_income_certification'=>array(
												     'label'=>'SHA Income Certification?'), 
						    'income_date'=>array(
										 'label_list'=>'Inc. Date',
										 'confirm'=>array('be_null($ox) || days_interval($x,$ox) < 30'=>//$ox is old value
													'You have made a large change to {$Y}. Unless you are correcting a mistake, you should probably add a new income record instead.')
										 ),
						    'income_date_end'=>array(
										     'label'=>'Income Date End',
										     'label_list'=>'End Date',
										     'confirm'=>array('be_null($x) || be_null($ox) || days_interval($x,$ox) < 30'=>//$ox is old value
													    'You have made a large change to {$Y}. Unless you are correcting a mistake, you should probably add a new income record instead.')
										     ),
						    'monthly_income_primary'=>array(
												'label'=>'Monthly Income (Primary)',
												'data_type'=>'currency',
												'confirm'=>array('be_null($ox) || $ox == $x'=>'You have changed {$Y} - unless you are correcting a mistake, you should probably add a new income record instead.')
												),
						    'monthly_income_secondary'=>array(
												  'label'=>'Monthly Income (Secondary)',
												  'data_type'=>'currency'
												  ),
						    'monthly_interest_income'=>array(
												 'data_type'=>'currency'
												 ),
						    'income_primary_code'=>array('show_lookup_code_list'=>'DESCRIPTION',
											   'label'=>'Income Source (Primary)',
											   'label_list'=>'Primary',
											   'data_type'=>'lookup',
											   'lookup'=>array(
														 'table' => 'l_income',
														 'value_field' => 'income_code',
														 'label_field' => 'description'
														 ),
											   'confirm'=>array('be_null($ox) || $ox == $x'=>'You have changed {$Y} - unless you are correcting a mistake, you should probably add a new income record instead.')
											   ),
						    'income_secondary_code'=>array('show_lookup_code_list'=>'DESCRIPTION',
											     'label'=>'Income Source (Secondary)',
											     'data_type'=>'lookup',
											     'lookup'=>array(
														   'table' => 'l_income',
														   'value_field' => 'income_code',
														   'label_field' => 'description'
														   )
											     ),
						    'other_assistance_1_code'=>array(
												 'label'=>'Other Assistance 1',
												 'data_type'=>'lookup',
												 'lookup'=>array(
														     'table' => 'l_other_assistance',
														     'value_field' => 'other_assistance_code',
														     'label_field' => 'description'
														     )
												 ),
						    'other_assistance_2_code'=>array(
												 'label'=>'Other Assistance 2',
												 'data_type'=>'lookup',
												 'lookup'=>array(
														     'table' => 'l_other_assistance',
														     'value_field' => 'other_assistance_code',
														     'label_field' => 'description'
														     )
												 ),
						    /*
						     * "income_certification_check" 
						     * ((((((is_income_certification AND (income_certification_type_code IS NOT NULL)) AND 
						     * (is_sha_income_certification IS NOT NULL)) AND (rent_amount_tenant IS NOT NULL)) AND 
						     * (housing_unit_code IS NOT NULL)) AND (rent_date_effective IS NOT NULL)) 
						     * OR 
						     * ((((((NOT is_income_certification) AND (income_certification_type_code IS NULL)) 
						     * AND (is_sha_income_certification IS NULL)) AND (rent_amount_tenant IS NULL)) 
						     * AND (housing_unit_code IS NULL)) AND (rent_date_effective IS NULL)))
						    */
						    'income_certification_type_code'=>array(
													  'comment'=>'Required for Housing Cert.',
													  'label_list'=>'Cert. Type',
													  'valid'=>array(
															     'sql_false($rec["is_income_certification"]) || !be_null($x)'=>
															     'Certification Type required for Income Certifications')
													  ),
						    'rent_amount_tenant'=>array(
										 'data_type'=>'currency',
										 'comment'=>'Required for Housing Cert.',
										 'valid'=>array(
												    'sql_false($rec["is_income_certification"]) || !be_null($x)'=>
												    'Rent Amount Tenant required for Income Certifications')
										 ),
						    'rent_date_effective'=>array(
											   'comment'=>'Required for Housing Cert.',
											   'valid'=>array(
														'sql_false($rec["is_income_certification"]) || !be_null($x)'=>
														'Rent Date Effective required for Income Certifications')
											   ),
						    'is_sha_income_certification'=>array('valid'=>array(
														'sql_false($rec["is_income_certification"]) || !be_null($x)'=>
														'{$Y} required for Income Certifications')),
						    'rent_date_end'=>array('data_type'=>'date'),
						    'annual_income'=>array( 'data_type'=>'currency'),
						    'housing_unit_code'=>array(
											 'comment'=>'Required for Housing Cert.',
											 'data_type'=>'lookup',
											 'lookup'=>array(
													     'table'=>'housing_unit_current',
													     'value_field'=>'housing_unit_code',
													     'label_field'=>'housing_unit_code'),
											 'show_lookup_code'=>'CODE',
											 'is_html'=>true,
											 'display_edit'=>'display',
											 'value'=>'link_unit_history($x)',
											 'valid'=>array('sql_false($rec["is_income_certification"]) || !be_null($x)'=>
													    '{$Y} required for Income Certifications',
													    '$action=="add" or ($ox==$x)'=>'{$Y} cannot be changed'),
											 'label'=>'Unit'),
						    'fund_type_code'=>array(
										    'comment'=>'(Only for Scattered Site)',
										    'valid'=>array('(be_null($x) && (strpos($rec["housing_unit_code"],"S")===false))
													 ||(!be_null($x) && !(strpos($rec["housing_unit_code"],"S")===false))'
													 =>'Fund Type required ONLY for Scattered Site')
										    ),
						    'rent_amount_total'=>array(
										 	'data_type'=>'currency',
										    'comment'=>'(Only for Scattered Site)',
										    'valid'=>array('(be_null($x) && (strpos($rec["housing_unit_code"],"S")===false))
													 ||(!be_null($x) && !(strpos($rec["housing_unit_code"],"S")===false))'
													 =>'Rent Amount Total required ONLY for Scattered Site')
										    ),
						    'grant_number'=>array(
										    'comment'=>'(Only for Scattered Site)',
										    'valid'=>array('(be_null($x) && (strpos($rec["housing_unit_code"],"S")===false))
													 ||(!(strpos($rec["housing_unit_code"],"S")===false))'
													 =>'Grant Number allowed ONLY for Scattered Site')
										    )
						    )
				);
?>
