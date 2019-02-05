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

$engine['hospital'] = array(
				'singular'=>'Hospital Record',
				'perm'=>'admin',
				'perm_list'=>'any',
				'perm_view'=>'clinical,my_client,hospital,clinical_super',
				'perm_edit'=>'my_client_clinical,hospital,clinical_super',
				'hide_no_perm_records'=>true,
				'list_fields'=>array('hospital_date','hospital_date_end','facility','is_voluntary','days_in_hospital'),
				'fields'=>array(
						    'hospital_id'=>array('value_view'=>'$x . " (" . smaller("Download: " . hlink("http://iww.desc.org/docs/forms/clinical/psych_hospital_follow_up.pdf","Psychiatric Hospital Follow Up Form")) . ")"',
										 'is_html'=>true),
						    'days_in_hospital'=>array(
									   'is_html'=>true,
									   'value'=>'$rec["hospital_date_end"] 
                                                              ? $x
									        : red(bold($x))',
									   ),
						    'facility'=>array(
									    'data_type'=>'lookup',
									    'lookup'=>array('table'=>'l_hospital',
												  'value_field'=>'hospital_code',
												  'label_field'=>'description')
									    ),
						    'hospital_date_source_code'=>array('default'=>'AGENCY',
											'display'=>'display'),
						    'hospital_date'=>array('display_edit'=>'display'),
						    'hospital_date_accuracy'=>array('display_edit'=>'display'),
						    'hospital_date_end_source_code'=>array(
												   'valid'=>array(
															'(!be_null($x) && !be_null($rec["hospital_date_end"]))
															|| (be_null($x) && be_null($rec["hospital_date_end"]))'=>
															'Enter source code if entering end date.',
															'be_null($x)||!in_array($x,array("KC","KCJAIL"))||($rec["hospital_date_end"]==$rec_last["hospital_date_end"])'=>'Invalid source code')
												   ),
						    'hospital_date_end'=>array('valid'=>array(/*((((hospital_date_end IS NULL) AND (hospital_date_end_accuracy IS NULL)) AND (hospital_date_end_source_code IS NULL)) OR (((hospital_date_end IS NOT NULL) AND (hospital_date_end_accuracy IS NOT NULL)) AND (hospital_date_end_source_code IS NOT NULL)))*/
													    '(be_null($x) and be_null(orr($rec["hospital_date_end_accuracy"],$rec["hospital_date_end_source_code"]))) or (!be_null($x) and !be_null($rec["hospital_date_end_accuracy"]) and !be_null($rec["hospital_date_end_source_code"]))'=>
													    'All end data (data accuracy and source) must be completed ONLY if {$Y} is filled in')
											 )

						    )
				);
?>
