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

//PULL ENGINE ARRAY OUT OF DB
$engine = get_engine_config_array();

//UNDUPLICATION STUFF
switch (AG_MAIN_OBJECT_DB) {
 case 'client':
	 $unduplication_table_list = array(
						     //---General Client---//
						     /* engine tables now automatic - if edits are allowed - if not, put in below */
						     $engine['assessment']['table_post']=> '',
						     $engine['entry']['table_post']=>'',
						     $engine['charge']['table_post']=>'',
						     
						     $engine['address']['table_post'] => '',
						     $engine['activity_evaluation']['table_post'] => '',
						     $engine['clinical_condition_at_assessment']['table_post'] => '',
						     $engine['clinical_impression']['table_post'] => '',
						     $engine['clinical_priority']['table_post'] => '',
						     $engine['clinical_reg']['table_post'] => '',
						     $engine['clinical_reg_request']['table_post'] => '',
						     $engine['cod_screening']['table_post'] => '',
						     $engine['cod_assessment']['table_post'] => '',
						     $engine['diagnosis']['table_post'] => '',
						     $engine['disability_clinical']['table_post'] => '',
						     $engine['homeless_status_clinical']['table_post'] => '',
						     $engine['medicaid']['table_post'] => '',
						     $engine['pss']['table_post'] => '',
						     $engine['referral_clinical']['table_post'] => '',
						     $engine['residence_clinical']['table_post'] => '',
						     $engine['veteran_status_clinical']['table_post'] => ''

						     //---non-engine tables---//
						     // 					    $clientref_table => '', these are engine tables now
						     // 					    $payments_table => '',
//						     $bedreg_table => 'client', // doesn't use client_id for field name
//						     $bedreghist_table => 'client'
					    );

	 //last DAL to be entered in SHAMIS
	 if (isset($engine['dal'])) {
		 define('AG_DAL_CUTOFF',call_sql_function('cg_dal_cutoff'));
	 }

	 break;
 case 'donor':
	 $unduplication_table_list = array();
}

//$DISPLAY_CLIENT_DEFAULT['show_command_box']='Y'; 

// log_browse options:
$DISPLAY_CLIENT_DEFAULT['photos']['show']=false;

?>
