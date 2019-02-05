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

$engine['sex_offender_reg'] = array(
			    'singular'=>'Sex Offender Registration',
			    'list_fields'=>array('has_registration_requirement',
							 'reoffense_risk_code',
							 'victim_type_code',
							 'reg_required_length_code'),
				'fields' => array( 
							'has_registration_requirement'=>array(
													  'label'=>'Is Registered?'),
					'police_bulletin_no' => array( 'label' => 'Police Bulletin Number',
												'comment' => '(if known)'),
							'classifying_jurisdiction' => array( 'data_type' => 'varchar' ),
							'reoffense_risk_code'=>array(
											     'valid'=>array(
														  '(sql_true($rec["has_registration_requirement"]) && $x!=="NA") ||
														  (sql_false($rec["has_registration_requirement"]) && $x=="NA") || !$x'
														  =>'Field "Reoffense Risk": Use "NA" only if not registered')),
							'victim_type_code'=>array(
											     'valid'=>array(
														  '(sql_true($rec["has_registration_requirement"]) && $x!=="NA") ||
														  (sql_false($rec["has_registration_requirement"]) && $x=="NA") || !$x'
														  =>'Field "Victim Type": Use "NA" only if not registered')),
							'reg_required_length_code'=>array(
											     'valid'=>array(
														  '(sql_true($rec["has_registration_requirement"]) && $x!=="NA") ||
														  (sql_false($rec["has_registration_requirement"]) && $x=="NA") || !$x'
														  =>'Field "Reg Required Length": Use "NA" only if not registered'))

				)

//		       'list_fields'=>array('referral_date','referral_code','referred_by','follow_up_code','follow_up_reported_by')
		       );
?>
