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


// Define production database name and server
// Note: this is used to determine operating mode (eg, is_test_db())
// not for actual database connection. This is defined in agency_config_local.php
define('AG_PRODUCTION_DATABASE_NAME','agency_client');
define('AG_PRODUCTION_DATABASE_SERVER','localhost');

define('AG_LIST_EMPTY_HIDE',false); //show empty records by default
define('AG_LIST_GROUPING_EXPANDED',true); //default child record groupings to expanded

$AG_ENGINE_TABLES=array_merge($AG_ENGINE_TABLES_CORE,array(AG_MAIN_OBJECT_DB,
				//---General Client---//
 				'address_client',
// 				'activity',
// 				'assessment',
 				'bar',
 				'bed',
//			        'chronic_homeless_status_asked',
				'client_death',
				'client_note',
				'client_protected',
				'client_ref',
// 				'contact_information',
// 				'service_ir',
				'disability',
				'ethnicity',
				'education_level',
// 				'employment_status',
 				'entry',
// 				'entry_visitor',
 				'income',
 				'jail',
// 				'jail_charge',
// 				'mail',
//				'medical_health',
// 				'sex_offender_reg',
// 				'phone',
				'staff_assign',
//  				'nicotine_distribution',
 				'elevated_concern',
 				'elevated_concern_note',
// 				'vocational_reg',
//				'sh_additional_data',
				//---Shelter---//
// 				'client_locker_assignment',
// 				'lock',
// 				'locker',
// 				'shelter_reg',
// 				'shelter_count',
// 				'safe_harbors_consent',
				//---Housing---//
// 				'charge',
// 				'client_export_id',
// 				'criminal_history',
// 				'event',
// 				'heet_reg',
// 				'heet_resource',
// 				'housing_notice',
// 				'housing_rsp',
// 				'housing_unit',
// 				'housing_unit_subsidy',
// 				'payment',
 				'residence_own',
 				'residence_other',
// 				'service_heet',
// 				'service_housing',
 				'housing_history',
// 				'application_housing',
// 				'application_housing_other',
// 				'data_gathering_1811',
// 				'payment_test',
				//---Other---//
//			        'meal_count',
				//---Mental Health---//
// 				'address',
// 				'activity_evaluation',
// 				'chart_archive',
// 				'client_description',
// 				'clinical_condition_at_assessment',
// 				'clinical_impression',
// 				'clinical_priority',
// 				'clinical_reg',
// 				'clinical_reg_request',
// 				'clinical_screening_intake',
// 				'cod_screening',
// 				'cod_assessment',
// 				'dal',
// 				'diagnosis',
// 				'disability_clinical',
// 				'homeless_status_clinical',
 				'hospital',
// 				'import_client_dshs_medicaid_lookup',
//				'conditional_release',
// 				'less_restrictive_alternative',
// 				'medicaid',
// 				'path_tracking',
// 				'pss',
// 				'referral_clinical',
// 				'residence_clinical',
// 				'spenddown',
// 				'veteran_status_clinical',
// 				'kc_authorization_response',
// 				'kc_authorization_actions_taken',
// 				'export_kc_transaction',
//				'safe_harbors_data_entry',
				//---Confidential---//
// 				'hiv',
// 				'immigrant',
				//---CD---//
// 				'cd_reg',
// 				'service_cd',
				//---Housing Guests---//
//				'guest',
//				'guest_authorization',
//				'guest_identification',
//				'guest_visit',
//				'work_order',
//				'work_order_comment',
				//---Staff & Users---//
				'address_staff',
 				'calendar',
 				'calendar_appointment',
//				'survey_2008_1night',

				));

/*
 * to add a new type of quicks search, it is assumed that there will be a corresponding CSS class of the same 
 * name to handle the coloring. Otherwise the new qs will be the default table color.
 * search.php must know how to handle the request as well.
 */

//temporarily removing i&r search option until agency data is updated. sd 8/1/08
$AG_QUICK_SEARCHES = array(AG_MAIN_OBJECT_DB=>ucwords(AG_MAIN_OBJECT),'staff'=>'Staff','log'=>'Log'/*,'iandr'=>'I &amp; R'*/);
define('AG_DEFAULT_QUICKSEARCH_TEXT','Quick Search');

// Number of characters typed before database match is
// triggered for auto complete.

// Smaller organizations might want to reduce this to 2.
// The smaller the number, the more database utilization,
// and with larger databases 2 or 3 letter matches might not
// be very meaningful or specific.
define('AG_QUICKSEARCH_AUTOCOMPLETE_MIN_LENGTH',4);

// flag table--used for bed_rereg
$flag_table = "sys_flag";
$nobedreg = "lock_bed_";


// Colors
$colors=array(
	'client'=>'#FFFFCF',
	'staff'=>'#DFFFDF',
	'staff_alert_bg'=> '#DFFFDF',
	'staff_alert_color1' => '#DFF0DF',
	'staff_alert_color2' => '#DFFFDF',
	'alert'=>'#FFc0c0',
	'addl'=>'#eff9ff',
	'text'=>'#ffffe8',
	'pick'=>'#ff8000',
	'menu'=>'#00FF00',
	'nav'=>'#CCCCFF',
	'blank'=>'white',
	'gray'=>'gray',
	'red'=>'red',
	'client_command_box'=>'#FF8100',
	'view_system_field_bg'=>'#EEEEEE',
	// generic colors for engine record display:
	'label_bg'=>'#fffff4',
	'value_bg'=>'#fdfdfd'
);

$client_select_sql = 'SELECT * FROM '.AG_MAIN_OBJECT_DB;

$entry_browse_url="entry_browse.php";
$entry_table="entry";
$entry_id_field = "client_id";
$entry_per_screen=50;

$entry_select_sql="SELECT * FROM $entry_table";
// $entry_order_sql = " ORDER BY EN.entered_at DESC ";
// $entry_last_sql = "SELECT MAX(entered_at) from $entry_table ";

$beds_select_sql = "SELECT $beds_table.*,
							name_full,
							ssn
					FROM $beds_table
					LEFT JOIN $client_table USING (client_id)";

$beds_order_sql = "bed_date DESC,bed_group,bed_no,name_full";

$iandr_select_sql = "SELECT * FROM agency as IR WHERE in_sections <> 'see' ";

//------- Main Object Registration -------//
$main_object_reg_search_fields = array('name_first','name_last','dob','ssn');
$main_object_reg_prompt = 'Please Enter name, date of birth, and social security #:';
/*
$main_object_reg_subtitle = div(oline('If you are registering a '.AG_MAIN_OBJECT.' for the shelter, please refer to the ') 
 					  . hlink('http://iww.desc.org/wiki/index.php?title=Shelter_Registration_Checklist',
 						    'Shelter Registration Checklist','','target="_blank"') . smaller(' (opens in new window)'),''
  					  ,'style=" border: solid 2px red; background-color: #efefef; margin: 10px 15px; width: 400px; padding: 10px;"');
*/

$l_volunteer_table = "l_volunteer";

$beds_table="bed";
$beds_table_post="tbl_bed";
$beds_table_id = "client_id";

$bedreg_table = "bed_reg";
$bedreghist_table = "bed_reg_hist";
$bedtemp_table = "bed_temp";

// This defines what groups of beds there are
// It should match with the l_bed_group table
$bed_groups = array( 'MENS','WOMENS','OVERFLOW' );
$bed_group_prefix = "bed_";

//matcheck times for applicable groups
$bedreg_matcheck_times = array(
					 'MENS'=>array(
							   'CURRENT'=>'not a mat-check, use current time',
							   '17:30:00'=>'5:30 pm Mat-Check',
							   '18:30:00'=>'6:30 pm Mat-Check',
							   '21:30:00'=>'9:30 pm Mat-Check',
							   '23:30:00'=>'11:30 pm Mat-Check'),
					 'WOMENS'=>array(
							     'CURRENT'=>'not a mat-check, use current time',
							     '17:30:00'=>'5:30 pm Mat-Check',
							     '18:30:00'=>'6:30 pm Mat-Check',
							     '21:30:00'=>'9:30 pm Mat-Check',
							     '23:30:00'=>'11:30 pm Mat-Check')
					 );

/* For each defined bed_group, an array of characteristics
* name of group prefixed with $bed_group_prefix
* night_startsat and endsat need to set as two-digit, military time(00:00)
* For example, 6 p.m. would be set as "18:00" and 7 a.m. would be "07:00"
* as for flags...the name of the array is the text that will be displayed
* that named array then holds the bed numbers that need that flag
* a bed can have multiple flags; to help users there's a flag legend
* for all bedgroups.
*/
//$amvolbeds = create_range_array(65,84);
//$pmvolbeds = create_range_array(85,104);

// beds that don't really exist
// add to flags in bedgroup, but not to flag_legend
/* no notbeds for phase 3 construction - JH
$notbeds = create_range_array(22,23);
$notbeds2 = create_range_array(109,139);
$notbeds3 = create_range_array(68,71);
$ofbeds = create_range_array(60,64);
*/

$flag_legend = array("AM" => "AM Volunteer",
                     "PM" => "PM Volunteer",
                     "SAT" => "Saturday Volunteer",
                     "MEAL" => "Meal Volunteer",
                     "OF" => "Waitlist Beds");

$bed_MENS = array( "code" => "MENS",
        "title" => "Men's Mats in Main Shelter",
        "color" => "FFDFDF",
        "start" => "1",
        "count" => "174",
        "genpref" => "M",
        "step" => "1",
        "night_startsat" => "21:00",
        "night_endsat" => "07:00",
			 "rereg_all" => "N",
			 //adding 50 overflow beds
			 'flags' => array('OF' => create_range_array(125,174))

			 /*
        "flags" => array(//"AM" => $amvolbeds,
                         //"PM" => $pmvolbeds,
						 "xxx1" => $notbeds,
						 "xxx2" => $notbeds2,
						 "xxx3" => $notbeds3)
			 */
);   

$bed_WOMENS = array( "code" => "WOMENS",
        "title" => "Women's Mats in Main Shelter",
        "color" => "DFDFFF",
        "start" => "1",
        "count" => "80",
        "genpref" => "F",
        "step" => "1",
        "night_startsat" => "21:00",
        "night_endsat" => "07:00",
			   "rereg_all" => "N",
			 //adding 50 overflow beds
			 'flags' => array('OF' => create_range_array(61,80))

        //"flags" => array("OF" => $ofbeds)
		);
        
$bed_OVERFLOW = array( "code" => "OVERFLOW",
        "title" => "Overflow Shelter",
        "color" => "DFFFDF",
        "start" => "1",
        "count" => "50",
        "genpref" => "M",
        "step" => "1",
        "night_startsat" => "3:00",
        "night_endsat" => "06:30",
        "rereg_all" => "N" );

// PERMISSION ARRAYS

// this is used in staff_client_position_project_clinical()
// If relevant, adjust to your position codes.
$AG_STAFF_CLIENT_CLINICAL_POSITIONS_PROJECTS = array('MGRPROJ' => "",
						     'CLINSVCCRD' => "",
						     'CSS' => "",
						     'CSSCD' => "", // 1811 positions, like CSS but CD-funded.  See bug 19706 & 15236
						     'CDPCSC' => "",
						     'SUPCSC' => "",
						     'SUPCSS' => "",
						     'RCSUP' => "", //fixme: see bug 17959
						     'ASSTPROJ' => "", // see bug 18445
						     'RC' => array('EVANS'), //see bug 24022
						     'HOUSESPEC' => "");

//these fields are tacked onto the path_tracking form
$path_tracking_dal_fields = array('dal_location_code',
					    'dal_code',
					    'contact_type_code',
					    'total_minutes',
					    'dal_focus_area_codes',
					    'progress_note'
					    );

$path_tracking_long_form_only_fields = array(
							   'path_housing_status_code',
							   'path_housing_status_time_code',
							   'jail_release_30_code',
							   'psych_release_30_code',
							   'path_principal_diagnosis_code',
							   'co_occurring_disorder_code',
							   'was_outreach_services',
							   'was_screening',
							   'was_rehabilitation',
							   'was_community_mh',
							   'was_substance_treatment',
							   'was_case_management',
							   'was_supportive_residential',
							   'was_referral',
							   'was_housing_planning',
							   'was_housing_costs',
							   'was_housing_technical',
							   'was_housing_coordination',
							   'was_housing_security_deposit',
							   'was_housing_one_time_rent',
							   'was_housing_minor_renovations',
							   'other_services'
							   );

define('AG_QUICK_DAL_PAGE','quick_dal.php');

//if changes are made to these DAL codes, the engine config array must be updated
$AG_DAL_MEDICAL_CODES = array('272','622','681','513');
$AG_DAL_FOLLOW_UP_CODES = array('820','830');
$AG_DAL_MEDICAL_ONLY_CODES = array('820','830','622','681','513','301','302','272');
$AG_DAL_PROGRESS_NOTE_OPTIONAL = array('618','260','266','267','282','110','100','INITCP','90CP','REVIEW_NC', '115');

define('AG_ID_CARD_IP_SHELTER','192.168.6.191');      // reg-office
define('AG_ID_CARD_IP_CONNECTIONS','192.168.6.193');  //connections
$AG_ID_CARD_STATIONS = array(AG_ID_CARD_IP_SHELTER,    
				     AG_ID_CARD_IP_CONNECTIONS
				     ); 

$AG_PHOTO_STATIONS = $AG_ID_CARD_STATIONS;

$AG_ID_CARD_CONFIG = array(
				   AG_ID_CARD_IP_SHELTER => array( // shelter reg-office
									    'directory'=>'lobbycam/cliphoto',
									    'link_text'=>'Please click here (Do NOT hit RELOAD) when the picture has been taken',
									    'wiki_help'=>'Shelter_Registration_Checklist#Take_Photo.'
								    ),
				   AG_ID_CARD_IP_CONNECTIONS => array( // connections
										  'directory'=>'connectcam',
										  'link_text'=>'Please click here (Do NOT hit RELOAD) once the picture has been taken and loaded to the proper directory',
										  'wiki_help'=>'Connections_Registration_Checklist#Take_Photo.'
										  )
				   );



/* URLs */

define('AG_BEDREG_URL','bed_reg.php');

//set to false to disable map links
define('AG_MAP_URL','http://maps.google.com/?q=');


//$AG_MENU_LINKS = array(//of the form 'url'=>'link text'
$AG_MENU_LINKS[]= hlink($agency_home_url,'Home');
$AG_MENU_LINKS[]= hlink(AG_LOG_URL,'Logs');
if (is_enabled('calendar')) {
	$AG_MENU_LINKS[]= hlink($calendar_url.'?Menu','Calendar');
}

if (is_enabled('entry')) {
	$AG_MENU_LINKS[]= hlink('entry_browse.php','Entry');
}
$AG_MENU_LINKS[]= hlink(AG_BEDREG_URL,'BedReg');
$AG_MENU_LINKS[]= hlink(AG_REPORTS_URL,'Reports');
$AG_MENU_LINKS[]= hlink('menu.php','Menu');


/*
 * Controls the options for menu.php
 */
$AG_MENU_TYPES = array(
/*
			  'staff' => array('title' => 'Staff Menu'),
*/
			  'housing' => array('title' => 'Housing Menu',
						   'link_text' => 'Housing Menu',
						   'perm' => 'housing'),
			  //	  'accounting' => array('title' => 'Accounting Menu',
			  //			'perm'  => 'admin'), 
			  //should be a new accounting permission?
/*
			  'clinical' => array('title' => 'Clinical Menu',
						    'perm'  => 'clinical_admin,dshs_medicaid'),
*/

			  'admin'    => array('title' => 'AGENCY Administration',
						    'perm'  => 'admin'),

/*
			  'shelter'  => array('title' => 'Shelter Menu'),

*/						    
			  );


?>
