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

$engine['service_cd'] = array(
					'table_post'=>'tbl_service',
					'perm'=>'cd,clinical_super',
					'perm_add'=>'cd',
					'perm_edit'=>'cd_admin',
					'add_another'=>true,
					'enable_staff_alerts_view'=>true,
					'singular'=>'CD Service',
					'subtitle_eval_code'=>'link_report("CD/cd_service.cfg","Print CD Service Notes",array("cid" => $id))',
					'list_fields'=>array('service_date','contact_type_code','service_code','service_minutes','service_by','asam_dimension_summary'),
					'fn' => array('generate_list_long' => 'generate_list_long_service',
							  'view' => 'view_service'),
					'valid_record'=>array('sql_true($rec["asam_dimension_1"])
							   or sql_true($rec["asam_dimension_2"])
							   or sql_true($rec["asam_dimension_3"])
							   or sql_true($rec["asam_dimension_4"])
							   or sql_true($rec["asam_dimension_5"])
							   or sql_true($rec["asam_dimension_6"])
                                             or ($rec["service_code"]=="CD_TPR")'=>
							   'At least one of the ASAM Dimensions must be specified, or, service must be Treatment Plan Review'
							   ),
					'fields'=>array(
							    'service_code'=>array(
											  'data_type'=>'lookup',
											  'lookup'=>array('table' => 'l_service_cd',
														'value_field'=>'service_cd_code',
														'label_field'=>'description',
														'data_type'=>'varchar',
														'length'=>10
														),
											  'default'=>'CD_IND',
											  'valid'=>array('(!in_array($x,array("CD_CM","CD_TPR"))) 
													     or (in_array($x,array("CD_CM","CD_TPR")) 
														   and ($rec["contact_type_code"]!=="FACE2FACE"))'=>
													     'Invalid Contact Type/{$Y} combination')
											  ),								 	
							    'service_date' => array( 'default' => 'NOW',
											     'data_type' => 'timestamp_past',
											     'value_format_list'=>'datetimeof($x,"US","TWO")'),
							    'contact_type_code' => array('default' => 'FACE2FACE'),
							    'service_by' => array( 'default' => '$GLOBALS["UID"]' ),
							    'service_project_code' => array('default'=>'CD',
													'display'=>'hide'),
							    'progress_note' => array('null_ok' => false),
							    'service_progress_note_id' => array('display_add' => 'hide',
													    'display' => 'display',
													    'label' => 'Associated Progress Note ID'),
							    /*
							    Dimension  1: Acute intoxication and/or withdrawal potential
							    Dimension  2: Biomedical conditions and complications
							    Dimension  3: Emotional/Behavioral/Cognitive conditions and complications
							    Dimension  4: Readiness To Change
							    Dimension  5: Relapse/Continued use potential
							    Dimension  6: Recovery Environment
							    */
							    'asam_dimension_1' => array(
												  'comment'=>'Acute intoxication and/or withdrawal potential',
												  'comment_show_view'=>true,
												  'label'=>'ASAM Dimension 1'),
							    'asam_dimension_2' => array(
												  'comment'=>'Biomedical conditions and complications',
												  'comment_show_view'=>true,
												  'label'=>'ASAM Dimension 2'),
							    'asam_dimension_3' => array(
												  'comment'=>'Emotional/Behavioral/Cognitive conditions and complications',
												  'comment_show_view'=>true,
												  'label'=>'ASAM Dimension 3'),
							    'asam_dimension_4' => array(
												  'comment'=>'Readiness To Change',
												  'comment_show_view'=>true,
												  'label'=>'ASAM Dimension 4'),
							    'asam_dimension_5' => array(
												  'comment'=>'Relapse/Continued use potential',
												  'comment_show_view'=>true,
												  'label'=>'ASAM Dimension 5'),
							    'asam_dimension_6' => array(
												  'comment'=>'Recovery Environment',
												  'comment_show_view'=>true,
												  'label'=>'ASAM Dimension 6'),
							    'asam_dimension_summary'=>array(
													'label'=>'ASAM Dimension Summary')
							    )
					);
?>
