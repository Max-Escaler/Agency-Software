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


$engine['meal_count'] = array(
			      'list_fields' =>array('served_at','meal_type_code','servings_first','servings_second','housing_project_code'),
			      'fields' => array(
					       'served_at'=>array('label'=> 'Meal Served At'),
					       'housing_project_code'=>array('label'=> 'Housing Project'),
					       'meal_type_code'=>array('lookup_order'=>'TABLE_ORDER',
								       'valid'=>array('$x == "OTHER" or be_null($rec["meal_type_other"])'
								       => '{$Y} must be Other if Other Meal Type is filled in')
								       ),
					       'meal_type_other'=>array('label'=> 'Other Meal Type',
									'comment'=> 'Please describe if Other is selected for Meal Type',
								       	'valid'=>array('$rec["meal_type_code"] != "OTHER" or !be_null($x)'
									=> '{$Y} required if Meal Type is Other')
									),
					       'food_percentage_nwh'=>array('label'=> 'Percentage of Food from Northwest Harvest',
									    'comment'=> 'KSH, 1811 and the Union ONLY',
									    'valid'=>array('(in_array($rec["housing_project_code"],array("KSH","1811","UNION"))) or (be_null($x))'
											   =>'{$Y} allowed only for KSH, 1811 and the Union',
											    '(!(in_array($rec["housing_project_code"],array("KSH","1811","UNION")))) or (!be_null($x))'
											   =>'{$Y} required for KSH, 1811 and the Union',
											   '($x<=100)'
											   =>'{$Y} must be 100 percent or less'
											   )
									    ),
					       'servings_first'=>array('label'=> 'Servings, Firsts'),
					       'servings_second'=>array('label'=> 'Servings, Seconds'),
					       'servings_hopwa'=>array('label'=> 'Servings for HOPWA Clients',
								       'comment'=> 'Lyon Building ONLY',
								       'valid'=> array('be_null($x) or $rec["housing_project_code"] == "LYON"'
										       => '{$Y} allowed only for the Lyon Building',
										       '!be_null($x) or $rec["housing_project_code"] != "LYON"'
										       => '{$Y} required for the Lyon Building',
										       '$x<=($rec["servings_first"]+$rec["servings_second"])'
										       => '{$Y} must be less than the total of first and second servings'
										       )
								       ),
					       'servings_farestart'=>array('label'=> 'Servings Provided by Farestart',
									   'comment' => '1811 and KSH ONLY',
									   'valid'=>array('(in_array($rec["housing_project_code"],array("KSH","1811"))) or (be_null($x))'
											   =>'{$Y} allowed only for KSH and 1811',
											    '(!(in_array($rec["housing_project_code"],array("KSH","1811")))) or (!be_null($x))'
											  =>'{$Y} required for KSH and 1811',
											   '$x<=($rec["servings_first"]+$rec["servings_second"])'
										       => '{$Y} must be less than the total of first and second servings'
											   )
									   ),
					       'servings_llaa'=>array('label'=> 'Servings Provided by Lifelong AIDS Alliance',
								      'comment'=> 'Lyon Building ONLY',
								      'valid'=> array('be_null($x) or $rec["housing_project_code"] == "LYON"'
										       => '{$Y} allowed only for the Lyon Building',
										       '!be_null($x) or $rec["housing_project_code"] != "LYON"'
										      => '{$Y} required for the Lyon Building',
										      '$x<=($rec["servings_first"]+$rec["servings_second"])'
										       => '{$Y} must be less than the total of first and second servings'
										       )
)
						)
			      );
?>
