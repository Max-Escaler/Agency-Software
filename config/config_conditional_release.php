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



$engine['conditional_release'] = array(
				       'perm'   => 'clinical,my_client_project_clinical',
				       'singular'=>'Less Restrictive Alternative/Conditional Release',
				       'fields' => array(
							 'conditional_release_type_code' =>array('label'=> 'Less Restrictive Alternative or Conditional Release?'),
							 'reference_number'=>array('label'=> 'Less Restrictive Alternative/Conditional Release Reference Number'),
							 'conditional_release_date'=>array('label'=> 'Less Restrictive Alternative/Conditional Release Start Date'),
							 'conditional_release_date_accuracy_code'=>array('label'=>'Less Restrictive Alternative/Conditional Release Date Accuracy'),
							 'conditional_release_date_end'=>array('label'=> 'Less Restrictive Alternative/Conditional Release End Date'),
							 'conditional_release_date_end_accuracy_code'=>array('label'=>'Less Restrictive Alternative/Conditional Release Date End Accuracy'),
							 'county_code'=>array('lookup_order'=>'TABLE_ORDER'),
							 'paperwork_in_chart_code'=>array('label'=> 'Copy of Less Restrictive Alternative/Conditional Release in client\'s chart?'),
							 'client_rights_in_chart_code'=>array('label'=> 'Did client receive a copy of Involuntary Client Rights and is verification on file?'),
							 'continuation_of_previous_code'=>array('label'=> 'Is this a continuation of a previous Less Restrictive Alternative/Conditional Release?'),
							 'previous_reference_number'=>array('label'=> 'If yes, list previous record number'),
							 'requirement_residence'=>array('label'=> 'A: Client will reside at:'),
							 'compliance_residence_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_residence'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
							 'requirement_appointment'=>array('label'=> 'B: Client will attend all appointments at and with:'),
							 'compliance_appointment_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_appointment'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
							 'compliance_medication_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_medication'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
					 	 	 'compliance_substance_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_substance'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
							 'compliance_threat_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_threat'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
							 'compliance_firearm_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_firearm'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
							 'requirement_other'=>array('label'=> 'G: Client will comply with the following additional requirements:'), 
							 'compliance_other_code'=>array('label'=> 'Is client in compliance?'),
							 'compliance_plan_other'=>array('label'=> 'What is the plan for getting into or staying in compliance?'),
							 'transition_plan'=>array('label'=> 'What is the plan for transitioning from involuntary to voluntary treatment?'),
							 'comment'=>array('label'=> 'Additional Comments'),
							 'requirements_met_code'=>array('label'=> 'Did client complete requirements of Less Restrictive Alternative/Conditional Release?'),
							 'voluntary_treatment_transition_code'=>array('label'=> 'Did client transition to voluntary treatment?'),
							 'conditional_release_extension_code'=>array('label'=> 'Was an extension requested?'),
							 'client_redetained_code'=>array('label'=> 'Was client re-detained/re-hospitalized?')
							 )
				       );							
?>
