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

$engine['pss'] = array(
			     'singular'=>'Clinical PSS',
			     'plural'=>'Clinical PSS',
			     'perm' => 'clinical',
			     'perm_add' => 'clinical_data_entry',
			     'allow_edit' => false,
			     'list_fields'=>array('pss_date','functioning_summary','symptom_summary','cognitive'),
			     'label_format_list'=>'smaller($x)',
			     'fields'=>array(
						   'functioning_summary'=>array(
											  'is_html'=>true,
											  'value'=>'pss_functioning_summary($rec)',
											  'label_list'=>'functioning'),
						   'symptom_summary'=>array(
										    'is_html'=>true,
										    'value'=>'pss_symptom_summary($rec)',
										    'label_list'=>'symptom')
						   )
			     );

//validity checks
foreach (array('dangerous_behavior',
		   'socio_legal',
		   'negative_social_behavior',
		   'self_care',
		   'community_living',
		   'social_withdrawl',
		   'response_to_stress',
		   'sustained_attention',
		   'physical',
		   'health_status',
		   'depressive_symptoms',
		   'anxiety_symptoms',
		   'psychotic_symptoms',
		   'dissociative_symptoms') as $tmp_f) {

	$engine['pss']['fields'][$tmp_f]['valid'] = array('be_null($x) || ($x>=0 && $x<=5)' => '{$Y} must be between 0 and 5');

}

?>