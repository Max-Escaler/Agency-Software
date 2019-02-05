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

global $AG_TEXT;
$engine["residence_other"] = array(
//$residence_other = array(
	'add_another' => true,
	'list_fields'=>array('residence_date','residence_date_end','facility_code'),
		"singular" => 'Non-'. org_name('short').' Living Situation',
		"plural" => 'Non-'.org_name('short').' Living Situations',
		"perm" => "any",
		"table" => "residence_other",
		"table_post" => "tbl_residence_other",
		"allow_edit" => true,
		"title" => 'ucwords($action) . "ing Non-'.org_name('short').' Living Situation for " . client_link($rec["client_id"])',
		"stamp_changes"=>true,
	    'subtitle_html' => oline(smaller(help('residence_other','','What does this mean?','',false,true))),
		"fields" => array(
				"client_id" => array( "display" => "display" ),
				"residence_id" => 
					array( "label" => 'Non-'.org_name('short').' Living Situation ID #',
							"display" => "display",
							"display_add" => "hide",
							"post_add" => false,
							"null_ok" => true
							),
				"residence_date_accuracy" => array(
					"label" => "Move-in Date is",
					"data_type" => "lookup",
					"default" => "A",
					"lookup" => array(
						"table" => "l_accuracy",
						"value_field" => "accuracy_code",
						"label_field" => "description")),
				"residence_date_end_accuracy" => array(
					"label" => "Move-out Date is",
					"data_type" => "lookup",
					"default" => "A",
					"lookup" => array(
						"table" => "l_accuracy",
						"value_field" => "accuracy_code",
						"label_field" => "description")),
				"residence_date" => array(
					"label" => "Move-in Date",
					"null_ok" => false),
				"residence_date_end" => array(
					"label" => "Move-out Date",
					"valid" => array(
							 '(dateof($x,"SQL") >= dateof($rec["residence_date"],"SQL")) || (empty($x))'
							 => "Move-out Date cannot be before Move-in Date.")
					),									   
				"moved_from_code" =>
					array( "label" => "Prior Living Situation",
				           "data_type" => "lookup",
							"null_ok" => false,
					       // 'lookup_order' => 'TABLE_ORDER',
						   "lookup" => array(
						 		"table" => "l_facility",
								"value_field" => "facility_code",
							    "label_field" => "description")),
				"moved_to_code" =>
					array( "label" => "Moved to following Situation",
				           "data_type" => "lookup",
							"valid" => array(
                            	'((empty($rec["residence_date_end"]) && (empty($x)) )
                            	|| (!empty($rec["residence_date_end"]) && (!empty($x)) ))'
	                            => "Must specify Moved-to Situation if and only if Move-out Date specified"),
					       // 'lookup_order' => 'TABLE_ORDER',
						   "lookup" => array(
						 		"table" => "l_facility",
								"value_field" => "facility_code",
							    "label_field" => "description")),
				"departure_type_code" => array( "valid" => array(
                            	'((empty($rec["residence_date_end"]) && (empty($x)) )
                            	|| (!empty($rec["residence_date_end"]) && (!empty($x)) ))'
	                            => "Must specify Departure Type if and only if Move-out Date specified")),
				"departure_reason_code" => array( "valid" => array(
                            	'((empty($rec["residence_date_end"]) && (empty($x)) )
                            	|| (!empty($rec["residence_date_end"]) && (!empty($x)) ))'
	                            => "Must specify Departure Reason if and only if Move-out Date specified")),
				"facility_code" =>
					array( "label" => "Living Situation Type",
							"null_ok" => false,
					       // 'lookup_order' => 'TABLE_ORDER',
						   "lookup" => array(
						 		"table" => "l_facility",
								"value_field" => "facility_code",
								"label_field" => "description",
								)),
				'geography_detail_code' => 
				array(
					'comment' => 'if known'),
				"zipcode" =>
					array( "label" => "Zip Code",
		   					"comment" => "if known",
							"null_ok"=>true,
							"valid" => array( '$x==zipcode_of($x,"zip5")' => 'Invalid 5 Digit Zip Code'	)),
				"geography_code" =>
					array( "label" => "Location",
							"null_ok" => false),
				"verified_method" => 
				array ( 
					 "data_type" => "lookup",
					"lookup" => array(
						"table" => "l_contact_type",
						"value_field" => "contact_type_code",
						"label_field" => "description"),
					 "valid" => array(
								'($rec["verified_date"] && $rec["verified_by"] &&($x)) || (empty($rec["verified_date"]) && empty($rec["verified_by"]) && empty($x))'
								=> "If verification date or staff are filled out, then verification method is required.")
					 ),
				"verified_date" =>
				array(
					 "valid" => array(
								'($rec["verified_method"] && $rec["verified_by"] &&($x)) || (empty($rec["verified_method"]) && empty($rec["verified_by"]) && empty($x))' 
								=> "If verification method or staff are filled out, then verification date is required.")	
				),					
				"verified_by" =>
				array(
					 "valid" => array(
								'($rec["verified_method"] && $rec["verified_date"] &&($x)) || (empty($rec["verified_method"]) && empty($rec["verified_date"]) && empty($x))' 
								=> "If verification method or date are filled out, then verifying staff is required.")
					),					
				"incentive_sent_date" =>
				array(
					 "valid" => array(
								'($rec["incentive_sent_by"] && ($x)) || (empty($rec["incentive_sent_by"]) && empty($x))'
								=> "If Incentive sent, fill out date")
					),
				"incentive_sent_by" =>
				array(
					 "valid" => array(
								'($rec["incentive_sent_date"] && ($x)) || (empty($rec["incentive_sent_date"]) && empty($x))'
								=> "If Incentive date given, fill out sender")
					),
				"comment" => array( "data_type" => "text" )
					)
					     );
//$residence_other=add_defaults($residence_other);
?>
