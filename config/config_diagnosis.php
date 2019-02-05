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

$engine['diagnosis'] = array(
				     'perm' => 'clinical',
				     'perm_add' => 'clinical_data_entry',
				     'perm_delete' => 'clinical_data_entry',
				     'allow_edit' => false,
				     'allow_delete' => true,
				     'plural' => 'diagnoses',
				     'list_fields'=>array(
								  'diagnosis_date','gaf_score','diagnosis_code'),
				     'valid_record' => array('(be_null($rec["gaf_score"]) && !be_null($rec["is_primary_treatment_focus"]) && !be_null($rec["diagnosis_code"])) || (!be_null($rec["gaf_score"]) && be_null($rec["is_primary_treatment_focus"]) && be_null($rec["diagnosis_code"]) && $rec["diagnosis_axis_code"] == "AXIS5")' => 'For GAF scores, leave Primary Treatment Focus and Diagnosis empty and select Axis V for the axis. For diagnoses, leave GAF score blank and select Axis I or II for the axis.'),
				     'fields' => array(
							     'is_primary_treatment_focus' => array('label' => 'Primary Treatment Focus?',
														 'boolean_form_type' => 'allow_null'
														 ),
							     'gaf_score' => array('label' => 'GAF Score'),
							     'diagnosis_code' => array('show_lookup_code_add' => 'PREPEND_CODE',
												 'show_lookup_code_edit' => 'PREPEND_CODE',
												 'lookup_order' => 'CODE')
							     )
				     
				     );
?>