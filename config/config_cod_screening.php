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


$engine['cod_screening'] = array('singular'=>'COD Screening',
					   'perm'=>'clinical,cd',
					   'allow_edit'=>false,
					   'list_fields' => array('screening_date','screening_by','ids_score','eds_score','sds_score'),
					   'cell_align_label' => 'left',
					   'fields'=>array(
								 'screening_date' => array('data_type' => 'date_past'),
								 'screening_by'   => array('default' => '$GLOBALS["UID"]'),
								 'ids_score' => array('label'=>'IDS Score'),
								 'eds_score' => array('label'=>'EDS Score'),
								 'sds_score' => array('label'=>'SDS Score'),

								 'ids_question_a_code' => array('label' => 'a. with feeling very trapped, lonely, sad, blue, depressed, or hopeless about the future?'),
								 'ids_question_b_code' => array('label' => 'b. with sleep trouble, such as bad dreams, sleeping restlessly or falling asleep during the day?'),
								 'ids_question_c_code' => array('label' => 'c. with feeling very anxious, nervous, tense, scared, panicked or like something bad was going to happen?'),
								 'ids_question_d_code' => array('label' => 'd. when something reminded you of the past, you became very distressed and upset?'),
								 'ids_question_e_code' => array('label' => 'e. with thinking about ending your life or committing suicide?'),


								 'eds_question_a_code' => array('label' => 'a. Lie or con to get things you wanted or to avoid having to do something?'),
								 'eds_question_b_code' => array('label' => 'b. Have a hard time paying attention at school, work or home?'),
								 'eds_question_c_code' => array('label' => 'c. Have a hard time listening to instructions at school, work or home?'),
								 'eds_question_d_code' => array('label' => 'd. Been a bully or threatened other people?'),
								 'eds_question_e_code' => array('label' => 'e. Start fights with other people?'),


								 'sds_question_a_code' => array('label' => 'a. you use alcohol or drugs weekly?'),
								 'sds_question_b_code' => array('label' => 'b. you spend a lot of time either getting alcohol or drugs, using alcohol or drugs, or feeling the effects of alcohol or drugs (high, sick)?'),
								 'sds_question_c_code' => array('label' => ' c. you keep using alcohol or drugs even though it was causing social problems, leading to fights, or getting you into trouble with other people?'),
								 'sds_question_d_code' => array('label' => 'd. your use of alcohol or drugs cause you to give up, reduce or have problems at important activities at work, school, home or social events?'),
								 'sds_question_e_code' => array('label' => 'e. you have withdrawal problems from alcohol or drugs like shaking hands, throwing up, having trouble sitting still or sleeping, or use any alcohol or drugs to stop being sick or avoid withdrawal problems?'),

								 'dal_id'=> array('is_html'=>true,
											  'value'=>'be_null($x) ? link_engine(array("object"=>"dal","action"=>"add","rec_init"=>array("client_id"=>$rec["client_id"],"dal_code"=>"630","dal_date"=>$rec["screening_date"],"performed_by"=>$rec["screening_by"])),"Add COD Screening DAL") : elink("dal",$x,"View DAL")'),
								 'export_kc_id'=>array('display'=>'display',
											     'display_add'=>'hide')
								 )
					   );
					   
foreach (array('ids'=>'1','eds'=>'2','sds'=>'3') as $tmp_sec => $num) {
	$tmp_valid_a = 'be_null($x) ';
	$tmp_valid_b = '!be_null($x) ';

	foreach (array('a','b','c','d','e') as $tmp_q) {
		$engine['cod_screening']['fields'][$tmp_sec.'_question_'.$tmp_q.'_code']['lookup_format'] = 'radio';
		$engine['cod_screening']['fields'][$tmp_sec.'_question_'.$tmp_q.'_code']['lookup_format'] = 'radio';
		$engine['cod_screening']['fields'][$tmp_sec.'_question_'.$tmp_q.'_code']['lookup_order'] = 'yes_no_code DESC';
		
		$tmp_valid_a .= '&& !be_null($rec["'.$tmp_sec.'_question_'.$tmp_q.'_code"])';
		$tmp_valid_b .= '&& be_null($rec["'.$tmp_sec.'_question_'.$tmp_q.'_code"])';
	}

	$engine['cod_screening']['fields'][$tmp_sec.'_data_status_code']['lookup_format'] = 'radio';
	$engine['cod_screening']['fields'][$tmp_sec.'_data_status_code']['valid'] = array('('.$tmp_valid_a.') || ('.$tmp_valid_b.')' => 'Select Refused or Not Completed ONLY if all the questions in section '.$num.' are blank.');

}

?>