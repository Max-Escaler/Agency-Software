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

$engine['path_tracking'] = array(
					   'add_another'=>true,
					   'allow_delete'=>true,
					   'perm_delete'=>'path_admin',
					   'perm'=>'clinical',
					   'singular'=>'PATH Tracking form',
					   'rec_init_from_previous'=>true, //this will fill in the form with the clients last record (must also specify on field level)
					   'list_fields'=>array('service_date','path_contact_type_code'),
 					   'add_link_alternate'=>'path_tracking_add_link($id)',
					   'fields'=>array(
								 'service_date'=>array('default'=>'now', 
											     'valid'=>array(
													
														
														  'tier_to_project(clinical_get_tier($rec["client_id"],$x, "HOST"))== "HOST" '=>
														  'You should only fill out a PATH form for clients enrolled in HOST at the time of the DAL'
														  )
											     
											     ),
								 // 'is_first_contact'=>array('label'=>'Is this the first contact with client?'),
								 'path_contact_location_code'=>array('label'=>'Where did the contact occur?'),
									'performed_by'=>array('default'=>'$GLOBALS["UID"]'),
								 'path_contact_type_code'=>array('label'=>'How did the contact occur?'),
// 								 'is_enrolled'=>array('label'=>'Services provided to enrolled person?',
// 													  'comment'=>'I think we can drop this--if it\'s not an exit record, and they are enrolled, then yes, otherwise no?'),
								 'path_eligible_code'=>array('rec_init_from_previous_f'=>true,
												     'default'=>'YES',
												     'label'=>'Eligible for PATH?',
												     'comment'=>'If client is no longer PATH-eligible, select reason and answer the next question'),
								 'path_discharge_code'=>array('label'=>'Discharge Reason',
													'comment'=>'Only if you are exiting this client'),

								 //--------------------------------------------------------//
								 //
								 //              PATH ENROLLED QUESTIONS BELOW
								 //
								 //--------------------------------------------------------//
								 'path_housing_status_code'=>
								 array('rec_init_from_previous_f'=>true,
									 'label'=>'Housing Status at First Contact',
									 'valid'=>array('is_path_enrolled($rec["client_id"]) && !be_null($x) 
															    || !is_path_enrolled($rec["client_id"])'=>
											    'Field {$Y} Required for PATH enrolled clients')
									 ),
								 'path_housing_status_time_code'=>array('rec_init_from_previous_f'=>true,
														    'label'=>'Length of Time Outdoors or Short-term Shelter at First Contact',
														    'valid'=>array('is_path_enrolled($rec["client_id"]) && !be_null($x)
																	 && (in_array($rec["path_housing_status_code"],
																			  array("OUTDOORS","SHORTSHEL")))
															    || (!is_path_enrolled($rec["client_id"])
																  || !in_array($rec["path_housing_status_code"],
																		   array("OUTDOORS","SHORTSHEL")) )'=>
																	 'Field {$Y} Required for PATH enrolled clients initially living Outdoors or in Short Term Shelter')
														    ),
								 'jail_release_30_code'=>array('label'=>'Released from Jail (Last 30 days)',
													 'data_type'=>'lookup',
													 'lookup'=>array('table'=>'l_path_yes_no',
															     'value_field'=>'path_yes_no_code',
															     'label_field'=>'description'),
													 'valid'=>array('is_path_enrolled($rec["client_id"]) && !be_null($x) 
															    || !is_path_enrolled($rec["client_id"])'=>
															    'Field {$Y} Required for PATH enrolled clients')
													 ),
								 'psych_release_30_code'=>array('label'=>'Released from Psych. Hosp. (Last 30 days)',
													 'data_type'=>'lookup',
													 'lookup'=>array('table'=>'l_path_yes_no',
															     'value_field'=>'path_yes_no_code',
															     'label_field'=>'description'),
													 'valid'=>array('is_path_enrolled($rec["client_id"]) && !be_null($x) 
															    || !is_path_enrolled($rec["client_id"])'=>
															    'Field {$Y} Required for PATH enrolled clients')
													 ),
								 'path_principal_diagnosis_code'=>array('rec_init_from_previous_f'=>true,
														    'valid'=>array('is_path_enrolled($rec["client_id"]) && !be_null($x) 
															    || !is_path_enrolled($rec["client_id"])'=>
																	 'Field {$Y} Required for PATH enrolled clients')
														    ),
								 'co_occurring_disorder_code'=>array('rec_init_from_previous_f'=>true,
														 'label'=>'Other co-occuring disorder NOT SUBSTANCE USE',
														 'comment'=>'eg. health concerns, developmental disabilities, etc.',
													 'data_type'=>'lookup',
													 'lookup'=>array('table'=>'l_path_yes_no',
															     'value_field'=>'path_yes_no_code',
															     'label_field'=>'description'),
													 'valid'=>array('is_path_enrolled($rec["client_id"]) && !be_null($x) 
															    || !is_path_enrolled($rec["client_id"])'=>
															    'Field {$Y} Required for PATH enrolled clients')
													 ),
								 'was_outreach_services'=>array('label'=>'Outreach',
													  'comment'=>'work in ANY locations except for clinical offices'),
								 'was_screening'=>array('label'=>'Screening & Diagnostic Treatment Services',
												'comment'=>'assessing sx/dx, prescriber appts.'),
								 'was_rehabilitation'=>array('label'=>'Habilitation and Rehabilitation Services',
												     'comment'=>'teaching & reviewing social and life skills'),
								 'was_community_mh'=>array('label'=>'Community Mental Health Services',
												   'comment'=>'good default service for any work with clients'),
								 'was_substance_treatment'=>array('label'=>'Alcohol/Drug Tx Svcs/Support',
													    'comment'=>'incl. ANY efforts & discussion re: sub use'),
								 'was_case_management'=>array('label'=>'Case Management',
													'comment'=>'e.g. payeeship, any other CM services not listed'),
								 'was_supportive_residential'=>array('label'=>'Supportive Svcs in Residential Settings ',
														 'comment'=>'home visits, coord. with housing providers'),
								 'was_referral'=>array('label'=>'Referrals for primary health/dental care, job training/education, & housing services'),
								 'was_housing_planning'=>array('display'=>'hide',
													 'default'=>sql_false(),
													 'label'=>'Housing Services - Planning of housing'),
								 'was_housing_costs'=>array('label'=>'Housing Services - Costs assoc.',
												    'comment'=>'(if Flex Funds) paying for application fees, needed furnishings, other necessary items for apt.'),
								 'was_housing_technical'=>array('label'=>'Technical assistance applying for housing',
													  'comment'=>'direct/indirect work with hsg applications'),
								 'was_housing_coordination'=>array('display'=>'hide',
													     'default'=>sql_false(),
													     'label'=>'Housing Services - improving the coordination of housing services'),
								 'was_housing_security_deposit'=>array('label'=>'Housing Services - Security deposit',
														   'comment'=>'(if Flex Funds)'),
								 'was_housing_one_time_rent'=>array('label'=>'Housing Services - One-time rental payments to prevent eviction',
														'comment'=>'(if Flex Funds)'),
								 'was_housing_minor_renovations'=>array('display'=>'hide',
														    'default'=>sql_false(),
														    'label'=>'Housing Services - Minor Renovations, expansion and repair'),
								 'other_services'=>array('label'=>'Other services',
												 'data_type'=>'character',
												 'comment'=>'write SHORT description'
												 )
								 )
					   

);

?>
