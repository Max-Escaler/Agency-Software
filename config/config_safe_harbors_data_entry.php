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


$engine['safe_harbors_data_entry'] = array(
	'singular'=>'Safe Harbors Data Entry Form',
	'subtitle'=>'Note: this is information that HUD requires us to put into Safe Harbors',
  'fields'=> array(
	'diag_dd'=>array(
		'label'=>'Developmental Disability',
		'row_before'=>'bigger(bold(oline("Have you been diagnosed with the following,"). "and did you receive services in the past year?"))',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_mi'=>array(
		'label'=>'Mental Illness',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_alcohol'=>array(
		'label'=>'Problem with Alcohol',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_drugs'=>array(
		'label'=>'Problem with Drugs',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_physdis'=>array(
		'label'=>'Physical Disability',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_hiv'=>array(
		'label'=>'AIDS/HIV',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_health'=>array(
		'label'=>'Chronic Health Condition',
		'row_after'=>'smaller(\'Definition of Chronic Health Condition: "A chronic health condition is a diagnosed condition that is more than three months in duration and either not curable, or has residual effects that limit daily living and require adaptation in function or special assistance."  Some examples of chronic health conditions include heart disease, severe asthma, disabilities, adult onset cognitive impairments and stroke.\')',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_other'=>array(
		'label'=>'Other Special Needs',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'diag_other_comment'=>array(
		'label'=>'Describe other special need',
		'valid'=>array(
			'($rec["diag_other"]=="NO" and be_null($x))'
			.'or ($rec["diag_other"]!="NO" and !be_null($x))'
				=>'Other special need must be described if and only if diagnosed with special needs')),
	'rcv_food_stamps'=>array(
		'label'=>'Food Stamps',
		'row_before'=>('bigger(bold("Do you receive the following at this time?"))'),
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'rcv_medicaid'=>array(
		'label'=>'Medicaid',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'rcv_medicare'=>array(
		'label'=>'Medicare',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'rcv_va_health'=>array(
		'label'=>'VA Health care benefits',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'domestic_violence'=>array(
		'value_format'=>'((strtoupper($x) != "NO") ? bold(red($x . " (DE-IDENTIFY FOR SAFE HARBORS)")) : $x)',
		'comment'=>'If yes, personal information will be "de-identified" when sending to Safe Harbors',
		'label'=>'Domestic Violence',
		'row_before'=>'bigger(bold(oline("Are you experiencing domestic violence at this time,") .oline("or are you afraid of spouse, partner/boyfriend or"). "girlfriend who abused you previously?"))',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'armed_forces'=>array(
	'row_before'=>'bigger(bold(oline("Have you ever served in any branch of the armed forces of the United States?")))."(Including the National Guard, the Coast Guard, and the Armed Forces Reserve)"',
		'label'=>'Armed Forces',
		'lookup_order'=>'TABLE_ORDER',
		'lookup_format'=>'radio'),
	'comment'=>array('row_before'=>'bold("Comments or notes")')
	)
);

?>
