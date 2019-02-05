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


$engine['safe_harbors_consent'] = array(
						    'fields'=>array(
									  'safe_harbors_exclude_data_codes'=>
									  array(
										  'lookup_format'=>'checkbox_v',
										  'lookup_order'=>'TABLE_ORDER',
										  'valid'=>array('(be_null($x) && $rec["safe_harbors_consent_status_code"] != "REFUSED_D") || (!be_null($x) && $rec["safe_harbors_consent_status_code"]=="REFUSED_D")'=>'{$Y} should only be filled in if client consented to Safe Harbors, but wished to exclude certain data elements',
												     '!(in_array("SSN",$x) and in_array("SSN_P",$x))'=>'{$Y} cannot have both SSN options selected.'),
										  'label'=>'Data Elements to Exclude from Safe Harbors',
										  'comment'=>'Fill in ONLY if client has consented to Safe Harbors, but wishes to exclude these elements'
										  )
									  )
						    );
										
												     
?>
