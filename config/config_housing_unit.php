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

$engine['housing_unit']=array(
					'cancel_add_url'=>'housing_menu.php',
					'perm'=>'housing_admin,housing_scattered',
					'perm_list'=>'any',
					'object_label'=>'sql_lookup_description($id,"housing_unit","housing_unit_id","housing_unit_code")',
					'list_fields'=>array(
								   'housing_unit_code',
								   'housing_unit_date',
								   'housing_unit_date_end',
								   'unit_type_code',
								   'unit_size',
								   'housing_project_code'),
					'confirm_record'=>array('be_null($rec_last) 
									or ($rec_last["address_1"]==$rec["address_1"]
									    and $rec_last["address_2"]==$rec["address_2"]
									    and $rec_last["city"]==$rec["city"]
									    and $rec_last["state"]==$rec["state"]
									    and $rec_last["zipcode"]==$rec["zipcode"])'
									=>'Are you sure you want to change this address?<br />If this is a new unit, please enter a new housing unit record.'),
					'fields'=>array(
							    'housing_project_code'=>array(),
							    'unit_size'=>array('comment'=>'Sq. Ft.'),
							    'housing_unit_code'=>array(
												 'add_query_modify_condition'=>array('be_null($x)'=>'ENGINE_UNSET_FIELD'),
												 'is_html'=>true,
												 'display_add'=>'regular',
												 'null_ok'=>true,
												 'display'=>'display',
												 'valid'=>array('(($action=="edit") || (($rec["housing_project_code"]=="SCATTERED") && be_null($x))) 
                                                                                         ||(($rec["housing_project_code"]!=="SCATTERED") && !be_null($x))'=>
														    'Unit No is required except for scattered site units',
														    'substr($x,0,1)!="Z" or preg_match("/^Z[A-Z]{1}0[A-Z0-9]{3}$/",$x)===1'=>'Leased Scattered Site unit numbers should take the form ZX0xxx where X is a letter denoting a specific building, and xxx is a 3-digit number, or combination of letters and numbers',
														    'be_null($x) || unit_no_of($x)'=>'Not a valid unit number', //double-check
														    '($action=="edit" and $rec_last["housing_unit_code"]==$x) or be_null($x) or (sql_num_rows(get_generic(array("housing_unit_code"=>$x),
															  "","","tbl_housing_unit"))<1)'=>'This unit already exists.'),
												 'value'=>'link_unit_history($x,false,true)',
												 'label'=>'Unit'),
							    'address_1'=>array(
											 'row_before'=>"bigger(bold('Address')) . ' (for scattered sites units only)'",
										     'valid'=>array('be_null($x) xor in_array($rec["housing_project_code"],array("SCATTERED","LEASED"))'
													  =>'{$Y} required ONLY for Scattered Site Units')
										     ),

							    'address_2'=>array(
										     'valid'=>array('be_null($x) or in_array($rec["housing_project_code"],array("SCATTERED","LEASED"))'
													  =>'{$Y} optional ONLY for Scattered Site Units')
										     ),

							    'city'=>array(
										     'valid'=>array('be_null($x) xor in_array($rec["housing_project_code"],array("SCATTERED","LEASED"))'
													  =>'{$Y} required ONLY for Scattered Site Units')
										     ),

							    'state'=>array(
										     'valid'=>array('be_null($x) xor in_array($rec["housing_project_code"],array("SCATTERED","LEASED"))'
													  =>'{$Y} required ONLY for Scattered Site Units',
													  /* 'be_null($x) or $x=="WA"'=>'{$Y} must be set to Washington (WA).' */)
										     ),

							    'zipcode'=>array(
										     'valid'=>array('be_null($x) xor in_array($rec["housing_project_code"],array("SCATTERED","LEASED"))'
													  =>'{$Y} required ONLY for Scattered Site Units',
													  'be_null($x) || ($x==zipcode_of($x,"zip5"))'=>'Invalid Zipcode')
										     ),

							    'landlord_contact'=>array(
										     'valid'=>array('(be_null($x) && ($rec["housing_project_code"]!=="SCATTERED"))
													  ||(!be_null($x) && ($rec["housing_project_code"]=="SCATTERED"))'
													  =>'{$Y} required ONLY for Scattered Site Units (non-Leased project)')
										     )
							    )
					);
?>
