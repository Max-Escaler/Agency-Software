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

$noun = 'bar';
//$noun = 'restriction';
$nouns = $noun . 's';

$Noun = ucfirst($noun);

$verb_passive = 'barred';
$verb_active = 'barring';
//$verb_passive = 'restricted';
//$verb_active = 'restricting';

$gbar = 'Reminder:  Graduated ' . $Noun;
$abrc = 'Automatic BRC';
$cl_def = get_def('client');
$cl_noun = orr($cl_def['singular'],'client'); // get_def might not work on initial load
$Cl_noun = ucfirst($cl_noun);

$engine["bar"]=array(
	'singular'=>$noun,
	'verb_passive'=>$verb_passive,
	"title" => '(be_null($rec["client_id"]) and be_null($rec["guest_id"]))
	    ? ucwords($action)."ing ' .$Noun. ' for non-client ".$rec["non_client_name_last"].", ".$rec["non_client_name_first"]
          : ucwords($action) . "ing ' .$Noun. ' for " . orr(client_link($rec["client_id"]),guest_link($rec["guest_id"]))',
	'title_list' => 'ucwords($action) . "ing ' .$Noun. ' Records for " . client_link($rec["client_id"])',
	'subtitle_eval_code' => "oline(smaller(help('BarCodes','','What do the ' . $noun.' codes mean?','',false,true)))",
	'list_fields'=>array('bar_date','bar_date_end','barred_from_summary','bar_type','flags','barred_by','comments'),
	'list_order'=>array('bar_date'=>true),
	'list_max'=>20,
	'valid_record'=>array(
				     //non-client vs guest vs client
				     '(be_null($rec["client_id"]) and be_null($rec["guest_id"]) and (!be_null($rec["non_client_name_last"]) and
										 !be_null($rec["non_client_name_first"]) and
										  !be_null($rec["non_client_description"])))
				     or
				     (be_null($rec["client_id"]) and (!be_null($rec["guest_id"])) and (be_null($rec["non_client_name_last"]) and
										   be_null($rec["non_client_name_first"]) and
										   be_null($rec["non_client_description"])))
				     or
				     (!be_null($rec["client_id"]) and be_null($rec["guest_id"]) and (be_null($rec["non_client_name_last"]) and
										   be_null($rec["non_client_name_first"]) and
										   be_null($rec["non_client_description"])))'=>
				     ucfirst($noun) . " must be for client, guest or $cl_noun.  All non-$cl_noun fields must be filled in for non-$cl_noun $nouns"
				     ),
	"fields" => array(
				'barred_by' => array( 'label' => ucfirst($verb_passive) . ' By' ),
				'bar_date' => array(
							  'label' => "$Noun Date",
							  "default" => "NOW"),
				"bar_date_end" => array(
								"label" => "$Noun End Date",
								"valid" => array(
										     '($x >= $rec["bar_date"]) || empty($x)'  
										     => "$Noun End Date must be greater than $Noun Date."
										     ),
								), 
				'bar_type'=>array( 'label' => ucfirst($noun) . ' type'),
				"description" => array(
							     "label" => "Incident Description",
							     "null_ok" => false),
				"comments" => array(
							  "label" => "Additional Comments"),
				"staff_involved" => array( 'data_type' => 'lookup_multi',
				'comment'=>'If applicable.  (use Ctrl to select multiple staff)',
             'lookup' => array('table' => 'staff',
             'value_field'=>'staff_id',
             'label_field'=>'staff_name(staff_id)')
),
			'guest_id'=>array('value'=>'guest_link($x_raw)','is_html'=>true),
				"reinstate_condition" => array(
									 "label" => "Conditions for Reinstatement",
									 "comment" => "Describe rules for reinstatement"
									 ),
				"gate_mail_date" => array( 'comment'=>'Provide mail service through this date',
											'display'=>'hide'
								  ),
				'brc_elig_date' => array('label' => 'Date Eligible for Review'),
				'brc_client_attended_date' => array('data_type' => 'date_past',
										'label' => 'Date ' .$Cl_noun. ' Attended Review'),
				'brc_resolution_code' => array('label' => 'Review Resolution'), 
                        	"appeal_elig_date" => array(
							    "label" => "Date Eligible to Appeal",
							    "valid" => array(
									     '($x >= $rec["brc_client_attended_date"]) || empty($x)'  
									     => "Date Eligible to Appeal must be greater than Date $Cl_noun Attended Review."
									     )
							    ),  
				'brc_recommendation' => array('data_type' => 'text',
									'label' => ucfirst($verb_active) . ' Staff Recommendations for Review'),
				'bar_resolution_location_code' => array('label'=>'Location for Review',
										    'comment'=>'(if applicable)',
										    'valid'=>array('(be_null($rec["bar_date_end"]) and !be_null($x))
												  or !be_null($rec["bar_date_end"])'=>'Field {$Y} required for OPEN ' . $noun),
										    'lookup_order'=>'TABLE_ORDER'
										    ),
				'bar_incident_location_code'=>array('label'=>'Where did the incident occur?',
										'lookup_order'=>'TABLE_ORDER'),
				'police_incident_number'=>array('label'=>'Police Incident Number',
								     'comment'=>'(if applicable)'),
		'barred_from_codes'=>array(
			'data_type'=>'lookup_multi',
		     'lookup' => array('table' => 'l_bar_location',
		     'value_field'=>'bar_location_code',
		     'label_field'=>'description'),
			'lookup_format'=>'checkbox_v',
			'value_format'=>'smaller($x)',
			'label'=>ucfirst($verb_passive) . ' from',
			'valid'=>array('count($x)>0'=>'You must ' . $noun . ' from at least one location')),
		'bar_reason_codes'=>array(
			'data_type'=>'lookup_multi',
		     'lookup' => array('table' => 'l_bar_reason',
		     'value_field'=>'bar_reason_code',
		     'label_field'=>'description'),
			'lookup_format'=>'checkbox_v',
			'value_format'=>'smaller($x)',
			'label'=>'Reason(s) for ' . $noun ,
			'valid'=>array('count($x)>0'=>'You must specify at least one reason')),
				'barred_from_summary'=>array(
								     'label'=>ucfirst($verb_passive) . ' From'),
 				'non_client_description'=>array('textarea_width' => 40,
									  'textarea_height' =>1 
									  ),
				'non_client_name_full'=>array('label'=>'Non-' . $Cl_noun . ' Name')
				)
		     );

?>
