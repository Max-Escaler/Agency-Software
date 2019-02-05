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


$engine['clinical_screening_intake'] = array(
							   'singular' => 'Clinical Screening/Intake Tracking Record',
							   'list_fields' => array('referral_date','initial_screening_date','intake_date','screening_intake_outcome_date','screening_intake_outcome_code'),
							   'fields' => array(
										   'referral_date' => array('label' => 'Referral/Request for Services Date'),
										   'referral_code' => array('data_type' => 'lookup',
														    'lookup' => array('table' => 'l_referral_clinical',
																	    'value_field' => 'referral_clinical_code',
																	    'label_field' => 'description'),
														    'lookup_order' => 'TABLE_ORDER'
														    ),
										   /*** initial request for services ***/
										   'referral_dal_id' => array('label' => 'Referral/Initial Request for Services DAL',
															'is_html' => true,
															'value' => 'be_null($x) ? link_engine(array("object" => "dal","action"=>"add","rec_init"=>array("client_id"=>$rec["client_id"],"dal_code"=>"262","dal_date"=>$rec["referral_date"])),"Add Referral/Request for Services DAL") : elink("dal",$x,"View DAL")'),
										   'referral_location_code' => array('data_type' => 'lookup',
																 'lookup' => array('table' => 'l_dal_location',
																			 'value_field' => 'dal_location_code',
																			 'label_field' => 'description'),
																 'label' => 'Referral/Initial Request for Services Location'),
										   /*** initial screening ***/
										   'initial_screening_calendar_id' => array('label'=>'Initial Screening Appointment',
																	  'is_html'=>true,
																	  'value'=>'be_null($x) ? Calendar::link_calendar("INTAKE",$rec["initial_screening_date"],"Add Initial Screening Appointment") : elink("calendar_appointment",$x,"View Initial Screening Appointment")'),
										   'initial_screening_dal_id' => array('label'=>'Initial Screening DAL',
																   'is_html'=>true,
																   'value'=>'be_null($x) ? link_engine(array("object"=>"dal","action"=>"add","rec_init"=>array("client_id"=>$rec["client_id"],"dal_code"=>"350","dal_date"=>$rec["initial_screening_date"],"performed_by"=>$rec["initial_screening_by"])),"Add Initial Screening DAL") : elink("dal",$x,"View DAL")'),
										   'initial_screening_location_code' => array('data_type' => 'lookup',
																	    'lookup' => array('table' => 'l_dal_location',
																				    'value_field' => 'dal_location_code',
																				    'label_field' => 'description'),
																	    'label' => 'Initial Screening Location'),
										   /*** intake ***/
										   'intake_calendar_id' => array('label'=>'Intake Appointment',
															   'is_html'=>true,
															   'value'=>'be_null($x) ? Calendar::link_calendar("INTAKE",$rec["intake_date"],"Add Intake Appointment") : elink("calendar_appointment",$x,"View Intake Appointment")'),
										   'intake_dal_id' => array('label'=>'Intake DAL',
														    'is_html'=>true,
														    'value'=>'be_null($x) ? link_engine(array("object"=>"dal","action"=>"add","rec_init"=>array("client_id"=>$rec["client_id"],"dal_code"=>"264","dal_date"=>$rec["intake_date"],"performed_by"=>$rec["intake_by"])),"Add Intake DAL") : elink("dal",$x,"View DAL")'),
										   'intake_location_code' => array('data_type' => 'lookup',
															     'lookup' => array('table' => 'l_dal_location',
																		     'value_field' => 'dal_location_code',
																		     'label_field' => 'description'),
															     'label' => 'Intake Location'),
										   /*** Outcome info ***/
										   'screening_intake_outcome_date' => array('label' => 'Screening/Intake Outcome Date',
																	  'valid' => array('be_null(orr($x,$rec["screening_intake_outcome_code"],$rec["screening_intake_outcome_notes"])) || (!be_null($x) && !be_null($rec["screening_intake_outcome_code"]))' => 'Fill in all fields for Screening/Intake Outcome, or leave them blank if not completing this record')),
										   'screening_intake_outcome_code' => array('label' => 'Screening/Intake Outcome',
																	  ''),
										   'screening_intake_outcome_notes' => array('label' => 'Screening/Intake Outcome Notes',
																	   'comment' => 'Required if Outcome is anything other than Eligible',
																	   'valid' => array('be_null($rec["screening_intake_outcome_code"]) || ($rec["screening_intake_outcome_code"] == "ELIGIBLE") || (!be_null($x) && $rec["screening_intake_outcome_code"] != "ELIGIBLE")' => '{$Y} is required for the outcome you have selected'))
										   
										   )
							   );
?>
