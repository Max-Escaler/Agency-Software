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

$engine['jail'] = array(
				'singular'=>'Jail Record',
				'enable_staff_alerts_view'=>true,
				// Uncomment custom1 here and in the fields section below if you are using the jail charge table
				'list_fields'=>array('jail_date','jail_date_end','days_in_jail','ba_number' /* ,'custom1'*/ ),
				'perm_list'=>'any',
				'perm_view'=>'any',
				'perm'=>'admin',
				'fields'=>array(
						    'days_in_jail'=>array(
										'label'=>'Days In Jail',
									   'is_html'=>true,
									   'value'=>'$rec["date_date_end"] 
                                                              ? $x
									        : red(bold($x))',
							      ),
					'jail_date_source_code'=>array(
								       'display_edit'=>'display',
								       'valid'=>array(
										      '!in_array($x, array("KC","KCJAIL"))'=>'This {$Y} is for imported records only. Please choose another {$Y}.'
										      )
								       ),
					'jail_date'=>array(
							   'display_edit'=>'display',
							   'data_type'=>'timestamp',
							   'timestamp_allow_date'=>true
							   ),
					'jail_county_code'=>array(
								  'valid'=>array(
										 '($x == "KING" && $rec[jail_facility_code] == "KCCF") or ($x == "SNOHOMISH" && $rec[jail_facility_code] == "SNOJAIL") or ($x == "PIERCE" && $rec[jail_facility_code] == "PIERCEJAIL")'=>'{$Y} must match Jail Facility.' 
										 )
								  ),
					'jail_date_end'=>array(
							       'data_type'=>'timestamp',
							       'timestamp_allow_date'=>true
							       ),
					'jail_date_accuracy'=>array(
								    'display_edit'=>'display'
								    ),
					'jail_date_end_accuracy'=>array(
									'valid'=>array(
										       '(!be_null($x) && !be_null($rec["jail_date_end"]))|| (be_null($x) && be_null($rec["jail_date_end"]))'=>'Enter {$Y} if entering end date.'
										       )
									),
					'jail_date_end_source_code'=>array(
									   'valid'=>array(
											  '(!be_null($x) && !be_null($rec["jail_date_end"]))|| (be_null($x) && be_null($rec["jail_date_end"]))'=> 'Enter source code if entering end date.',
											  'be_null($x)||($x!=="KC")||($rec_last["jail_date_end"]==$rec["jail_date_end"])'=>'Source can\'t be KC.',
											  '!in_array($x, array("KC","KCJAIL"))'=>'This {$Y} is for imported records only. Please choose another {$Y}.'								 
											  )
									   )
/*
					'custom1' => array(
							   'label' => 'Charge Detail',
							   'value' => '$rec["ba_number"] && (sql_num_rows(get_generic(array("ba_number"=>$rec["ba_number"]),"","","jail_charge"))>0) ? link_engine(array("object"=>"jail_charge","action"=>"list","list"=>array("filter"=>array("ba_number"=>$rec["ba_number"]))),"View Charge Details") : ""',
							   'display_add'=>'hide',
							   'display_edit'=>'hide',
							   'display'=>'display',
							   'is_html'=>true
							   )
*/
					)
				
			);
?>
