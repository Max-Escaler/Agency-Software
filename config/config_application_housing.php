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

$engine['application_housing'] = array(
		'perm'=>'housing',
		'perm_view'=>'any',
		'perm_list'=>'any',
		'singular' => 'Housing Application',
		'list_fields'=>array('application_date','housing_project_code','application_status_code','referral_source_code'),
//		'title' => 'ucwords($action) . "ing Housing Application for " . client_link($rec["client_id"])',
//		'subtitle_eval_code' => 'return "For housing program use only!"',
		'fields' => array(
				'housing_project_code' => array(
                            'show_lookup_code_list'=>'DESCRIPTION',
                            'label'=>'Applying to which project?',
                                               'label_list'=>'Project',
                                               'data_type'=>'lookup',
                                               'lookup'=>array(
                                                         'table' => 'l_housing_project',
                                                         'value_field' => 'housing_project_code',
                                                         'label_field' => 'description'
                                                         )
                                               ),
				'sha_approval_code' => array(
                            'show_lookup_code_list'=>'DESCRIPTION',
                            'label'=>'Application status with SHA?',
                                               'label_list'=>'SHA Status',
                                               'data_type'=>'lookup',
                                               'lookup'=>array(
                                                         'table' => 'l_approval',
                                                         'value_field' => 'approval_code',
                                                         'label_field' => 'description'
                                                         )
                                               ),

				'needs_physical_accommodation' => array( 'label' => 'Needs Physical Accommodations?',
										     'boolean_form_type'=>'allow_null'),
				'is_homeless' => array( 'label' => 'Is Applicant Homeless?',
								'boolean_form_type'=>'allow_null' ),
				'is_substance_use_housing_issue' => array( 'label' => 'Has substance use interfered with housing in the past?',
											 'boolean_form_type'=>'allow_null' ),
				'substance_use_housing_issue_text' => array( 'label' => '(If appropriate, explain why substance use is/is not an issue)' ),
				'uses_drugs' => array( 'label' => 'Does Applicant currently use drugs?',
							     'boolean_form_type'=>'allow_null' ),
				'uses_alcohol' => array( 'label' => 'Does Applicant currently use alcohol?',
								 'boolean_form_type'=>'allow_null' ),
				'is_willing_to_talk' => array( 'label' => 'Is Applicant willing to talk with staff?',
									 'boolean_form_type'=>'allow_null' ),
				'had_substance_use_treatment' => array( 'label' => 'Has applicant had substance use treatment?',
										    'boolean_form_type'=>'allow_null' ),
				'has_criminal_history' => array( 'label' => 'Does Applicant have a criminal history?',
									   'boolean_form_type'=>'allow_null' ),
				'was_evicted' => array( 'label' => 'Has applicant been evicted?',
								'boolean_form_type'=>'allow_null' ),
				'owes_sha' => array( 'label' => 'Does Applicant owe money to SHA?',
							   'boolean_form_type'=>'allow_null' ),
				'owes_landlord' => array( 'label' => 'Does Applicant owe money to other landlord?',
								  'boolean_form_type'=>'allow_null' ),
				'why_appropriate_per_cm' => array( 'label' => 'Why does Case Manager think referral is appropriate?' ),
				'is_currently_on_probation'=>array('boolean_form_type'=>'allow_null'),
				'has_other_applications_pending'=>array('boolean_form_type'=>'allow_null'),
				'is_sex_offender'=>array('boolean_form_type'=>'allow_null'),
				'has_violent_history'=>array('boolean_form_type'=>'allow_null'),
				'legal_issues'=>array('label'=>'Legal Issues/Comments'),
				'behavioral_issues'=>array('label'=>'Behavioral Issues/Independent Living Skills'),
				'assessment_id' => array('value' => 'be_null($x) ? "No Assessments" : elink("assessment",$x,"View Assessment")',
								 'is_html' => true,
								 'label' => 'Most Recent Assessment')
				)
		);


?>
