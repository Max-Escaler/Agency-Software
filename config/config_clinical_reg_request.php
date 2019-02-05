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


$engine['clinical_reg_request'] = array('singular' => 'Clinical Registration Request',
						    'perm' => 'clinical',
						    'perm_add' => 'clinical_data_entry',
						    'allow_edit' => false,
						    'list_fields' => array('benefit_type_code','assessment_date','benefit_change_code','funding_source_code'),
						    'fields' => array(
									    'benefit_type_code' => array('show_lookup_code' => 'BOTH'),
									    'benefit_change_code' => array('valid' => array('be_null($x) || ($x == "INITIAL" && be_null($rec["previous_auth_id"])) || $x != "INITIAL"' => 'Previous Auth ID should be blank for initial authorization requests',
																	    'be_null($x) || ($x != "INITIAL" && !be_null($rec["previous_auth_id"])) || be_null($rec["previous_auth_id"])'=> 'Previous Authorization ID cannot be blank for non-Initial authorization requests')
														     ),
									    'funding_source_code' => array(
														     'confirm' => array('be_null($x) || $x != "KC" || ($x == "KC" && !be_null(client_get_kcid($rec["client_id"])))' => 'By registering a client without a KCID, the client will be automatically matched by the King County system. If this is not desired, please look the client up on ECLS to determine the KCID and edit the client record accordingly.'),
														     'valid' => array(
																	    'be_null($x) || $x != "KC" || ($x == "KC" && !be_null(client_get_clinical_id($rec["client_id"])))' => 'Client needs a clinical ID for the selected funding source')
														     )
									    )
						    );

?>