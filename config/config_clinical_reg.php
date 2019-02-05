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


$engine['clinical_reg'] = array('singular' => 'Clinical Registration',
					  'perm' => 'clinical',
					  'perm_add' => 'clinical_data_entry',
					  'perm_edit' => 'clinical_data_entry',
					  'list_fields' => array('clinical_reg_date','clinical_reg_date_end','benefit_type_code','kc_authorization_status_code','kc_authorization_id','clinical_exit_reason_code'),
					  'fields' => array(
								  'kc_authorization_id' => array('is_html' => true,
													   'value' => '$x',
													   'value_view' => 'clinical_link_kc_authorization_response($x)'),
 								  'clinical_exit_reason_code' => array('valid' => array('be_null($x) xor !be_null($rec["clinical_reg_date_end"])' => '{$Y} is required for exits',
															'be_null($x) || clinical_valid_exit_reason_combination($rec)' => 'Invalid Exit Reason/Benefit Combination'),
												       'lookup' => array('table' => '(SELECT * FROM l_clinical_exit_reason WHERE is_current) cur', //'cur' is the alias postgres needs to correctly process the query, it is otherwise meaningless.
															 'label_field' => 'description',
															 'value_field' => 'clinical_exit_reason_code' ))
								  )
					  
								  

					  );

?>
