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

//AGENCY Advisory will display below main header
//$AG_AGENCY_ADVISORY = ''; 
//$AG_AGENCY_ADVISORY_STYLE = 'background-color: #faa; border: solid 1px #333; margin-top: 2px;';

/*
 * Kiosk mode (auto-login) was here, but should
 * be defined in agency_config_local.php
 */

//Agency public addresses:
$agency_public_home_url='http://agency-software.org/';

//$agency_donate_url='http://sourceforge.net/donate/index.php?group_id=281315';
$agency_donate_url='<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="8701124">
<input type="image" src="images/pay_pal_donate_button.gif" border="0" name="submit" alt="Donate to AGENCY">
</form>';

$agency_wiki_public_url='https://sourceforge.net/p/agency/wiki/';

$AG_TEXT=//General Text Array - this is a first step, the next step would be to move this into the db...
array(
      //general display...
	'ORGANIZATION_SHORT' => 'GSSO',
	'ORGANIZATION' => 'Generic Social Service Organization',
      'AGENCY_HOME_TITLE' => 'AGENCY Home Page',
	'CONFIDENTIAL_STATEMENT'=>'Add your confidentiality statement in agency_config.php',
	'COPYRIGHT_STATEMENT'=> link_agency_public_home() . ' is copyright (c) 2003-2017, by Ken Tanzer and <a href="http://www.desc.org">DESC</a>. <a href="license_info.php">More information</a>',
	'LICENSE_INFO'=>'AGENCY comes with ABSOLUTELY NO WARRANTY.  AGENCY is free software, and can be distributed under the terms of the General Public License, Version 3.  This file should have been included with AGENCY, but can also be found at <a href="http://www.gnu.org/licenses.html">www.gnu.org/licenses.html</a>',

      //LINK LABELS (set link destinations below)
      'LINK_HOME'=>'AGENCY Home',
      'LINK_ORG_HOME'=>'Organzation Home',
      'LINK_BUGZILLA'=>'Bugzilla',
      'LINK_REPORT_BUG'=>'Report a Bug',
      'CLIENT_QUICK_SEARCH'=>ucfirst(AG_MAIN_OBJECT).' Quick Search',
      'CLIENT_SEARCH_RESULTS'=>ucfirst(AG_MAIN_OBJECT).' Search Results',

      //client display stuff
      'LINK_VIEW_EDIT'=>'View/edit data record',

      //Authorization stuff
      'LOGIN_TITLE' => 'Login to AGENCY',
	  //Box (div content) that will show on login screen
	  'LOGIN_ADVISORY' => '',
      'LOGIN_SUB_TEXT' => "Welcome to AGENCY! Please enter your username and password:<br>\n",
      'AUTH_LOGOUT_MESSAGE' => 'You have been logged out.',
      'AUTH_LOGIN_BUTTON_TEXT' => 'Login',
      'AUTH_LOGOUT_LINK_TEXT' => 'Logout Now',
	'AUTH_LOGIN_TOP_BOX_LABEL' => 'Switch Login',
	'AUTH_LOGIN_TOP_BOX_BUTTON_LABEL' => 'go'
      );

// all footer formatting/styling should also be in this variable 
// as it is output directly prior to </body> tag
//$AG_PAGE_FOOTER = div('----footer----',''); 
$AG_PAGE_FOOTER = ( AG_OUTPUT_MODE == 'TEXT' )
	? oline(confidential(),2) . oline(strip_tags($AG_TEXT['COPYRIGHT_STATEMENT']))
	: div(center(confidential() . smaller(oline() . $AG_TEXT['COPYRIGHT_STATEMENT']) ),'');

//-----Mediawiki Support-----//
//comment out to disable
//Internal Wiki:
//define('AG_WIKI_BASE_URL','http://iww.desc.org/wiki/index.php?title=');
//Agency Public Wiki:
define('AG_WIKI_PUBLIC_BASE_URL',$agency_wiki_public_url);

//links
$agency_home_url= $_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])
				.((substr($agency_home_url,-1)=='/') ? '' : '/')
				.($off ? ('/'.$off) : '');
$agency_home_url=str_replace('//','/',$agency_home_url);
$agency_home_url= ( is_secure_transport() ? 'https://' : 'http://' ) . $agency_home_url;

$organization_home_url=$agency_wiki_public_url . 'Not_set_organization_home_url';
define('AG_AGENCY_ADMIN_URL','menu.php#admin');
$agency_search_url=$off.'object_query.php';
define('AG_REPORTS_URL','canned_report.php');
//define('AG_LOG_URL','display.php?control[action]=list&control[object]=log&control[id]=1');
define('AG_LOG_URL','log_browse.php');
$revision_history_url = $agency_home_url.'display_history.php';
$calendar_url = 'calendar_display.php';
define('CALENDAR_REPORT_MEDICAL_URL',AG_REPORTS_URL.'?filename=clinical/med_calendar.cfg');
define('CALENDAR_REPORT_INANIMATE_URL',AG_REPORTS_URL.'?filename=general/calendar.cfg');

define('AG_ADMIN_URL',$AG_HOME_BY_URL);
// You can disable self-serve password resets:
define('AG_PASSWORD_RESET_ENABLE',false);

// Even if you disable resets, don't comment this line out
define('AG_PASSWORD_RESET_URL','reset_password.php');

/*
 * Email Sender
 * This will be the 'From:' line in any emails sent
 *
 * n.b., Leave this line alone.  It is now set in the config_email table.
 */
$AG_EMAIL_SENDER=sql_assign('SELECT email_sender FROM config_email');

/*
 * authorization and password configuration
 */

define('AG_SHOW_AUTH_TOP_LOGIN_BOX',true); //show login form at top of every page

$AG_AUTH_DEFINITION = //authorization (and session)  configuration
array(
      'SESSION_NAME' => 'AGENCY_SESSION_'.$database[$WHICH_DB],

      //By offsetting the session expiration and the EXPIRE, one allows users to
      //recover session data for the time window determined by the offset. If
      //authentication is only reliant on the session expiration, then users will
      //loose any data they had been working on...
      'SESSION_EXPIRE' => 180, //session expires after 180 minutes of inactivity
      'EXPIRE' => 60, //authorization expires after 60 minutes of inactivity

      'TABLE' => 'tbl_staff_password',
      'VIEW' => 'staff_password_current',
      'USERNAME_FIELD' => 'username',
      'PASSWORD_FIELD' => 'staff_password',
	'PASSWORD_MD5_FIELD' => 'staff_password_md5',
	  'STAFF_TABLE' => 'staff', // really it's a view, but why quibble?
      'USER_ID_FIELD' => 'staff_id',
	'USE_MD5' => true,  // Currently only option supported.  Insecure "flipbits" has been removed.
	'DEFAULT_METHOD'=> 'MD5',
	'CASE_SENSITIVE_USERNAME' => false
      );

define('AG_AUTH_PASSWORD_MIN_LENGTH',6); //set any of these to false to skip that portion of the test
define('AG_AUTH_PASSWORD_UC_LIMIT',false);
define('AG_AUTH_PASSWORD_LC_LIMIT',1);
define('AG_AUTH_PASSWORD_NUM_LIMIT',1); //includes special characters defined below
define('AG_AUTH_ALLOWED_SPECIAL_CHARS','!@#$%^&*()');
define('AG_AUTH_PASSWORD_USERNAME_CHECK',true);

define('AG_AUTH_PASSWORD_DICT',false); //set to dictionary path (usually /usr/share/dict/words)

define('AG_AUTH_INTERNAL_ACCESS_ONLY',false);

$AG_AUTH_LEET = array('!'=>'l', //used for dictionary and username comparison checks
			    '1'=>'l',
			    '|'=>'l',
			    '$'=>'s',
			    '5'=>'s',
			    '@'=>'a',
			    '3'=>'e',
			    '0'=>'o'
			    );

$agency_style_directory = $off.'schema'; //this needs to be a relative link
$agency_javascript_directory = $off.'.';

//GRAPHICS
$AG_IMAGES=
array(
	'CLOCK'=>$off.'images/clock.png',
	'UP_TRIANGLE' => $off.'images/up_triangle.png',
	'DOWN_TRIANGLE' => $off.'images/down_triangle.png',
	'TABLE_HORZ' => $off.'images/table_horz.png',
	'TABLE_VERT' => $off.'images/table_vert.png',
	'AGENCY_LOGO_SMALL' => $off.'images/agency_logo_small.png',
	'AGENCY_LOGO_MEDIUM' => $off.'images/agency_logo_medium.png',
	'ORGANIZATION_LOGO_MEDIUM' => $off.'images/organization_logo_medium.png',
	'ORGANIZATION_LOGO_SMALL' => $off.'images/organization_logo_small.png',
	'NO_PHOTO' => $off.'images/no_photo.jpg',
	'DEMO_PHOTO' => $off.'images/demo_photo.jpg',
	'RECORD_VOIDED' => $off.'images/record_voided.png',
	'RECORD_DELETED' => $off.'images/record_deleted.png',
	'RECORD_ENABLED' => $off.'images/record_enabled.png',
	'RECORD_DISABLED' => $off.'images/record_disabled.png',

	//js buttons
	'JS_HIDE'=>$off.'images/hide_button.png',
	'JS_SHOW'=>$off.'images/show_button.png',
	//quick search images
	'page_loading_animation'=>$off.'images/loading.gif',
	//Plus button for bedreg.  FIXME: replace w/ js
	'PLUS_BUTTON'=>$off.'images/plus.gif'
	
	);

//Levenshtein/metaphone 'hit' ratio (lower is more rigid)
define('LEVENSHTEIN_ACCEPT',0.2); //20%
define('METAPHONE_MAX_LENGTH',15);

//Engine configuration
define('ENGINE_CONFIG_FILE_DIRECTORY','config');
define('AG_ENGINE_CONFIG_TABLE','tbl_engine_config');
define('AG_SESSION_ENGINE_ARRAY',true); //set to true to cache engine array as session variable (saves on bandwidth if separate db server)

//Script configuration
define('SCRIPT_CONFIG_FILE_DIRECTORY','config');

$generic_sql_security_checks = array(
						 '/DELETE[\s\n]*FROM\s/i'=>'I will not run this query because it contains DELETE FROM',
						 '/TRUNCATE/i' => 'I will not run this query because it contains TRUNCATE',
						 '/UPDATE\s.*SET/i'=>'I will not run this query because it appears to contain an update',

						 /* 
						  * Only allow inserts into tables starting as tmp_
						  * FIXME: this matches (ie, overly restrictive) on tmp_ if it is preceeded by a line break
						  */
						 '/INSERT\s+INTO\s+(?!tmp_)\S+/i' => 'INSERTs can only be performed on temporary tables, beginning with "tmp_"',

						 /* get list of all possible items that can be dropped or altered, for this database type */
						 '/CREATE[\s\n]*('.implode('|',sql_drop_alter_relations_array()).')/i'=>'Query cannot create a <i>permanent</i> table, view, function, etc.',
						 '/(DROP|ALTER)[\s\n]*('.implode('|',sql_drop_alter_relations_array()).')/i'=>'Query cannot drop or alter',

						 '/flipbits/i'=>'I will not run this query because the use of flipbits is not allowed, even though flipbits is no longer used',
						 '/password/i'=>'I will not run this query because access to passwords is forbidden.',
						 '/\s(tbl_)?staff_password(\s|\)|$)/i'=>'I will not run this query because it appears to access the password table.',
						 '/\stbl_client(\s|\)|$)/i'=>'I will not run this query&mdash;tbl_client cannot be accessed through this page.',
						 '/protected/i'=>'I will not run this query--it appears to access a protected object.'
						 );


$client_table     = AG_MAIN_OBJECT_DB;
$client_table_post= 'tbl_'.AG_MAIN_OBJECT_DB;
$client_table_id  = AG_MAIN_OBJECT_DB.'_id';
$client_page      = AG_MAIN_OBJECT_DB."_display.php";

//ID cards
$client_card_temp_filename = "$AG_CLIENT_PHOTO_BY_FILE/temp_card.jpg";
define('AG_CLIENT_ID_CARD_MAX_NAME_LENGTH',32);
define('AG_STAFF_ID_CARD_MAX_NAME_LENGTH',32);
define('AG_STAFF_ID_CARD_MAX_JOB_TITLE_LENGTH',32);

//------- revision history configuration -------//
define('REVISION_HISTORY_TABLE_SUFFIX','_log');

// Mail table stuff here:
//FIXME: bring this stuff up-to-date
$mail_table="mail";
$mail_select_sql = "SELECT $mail_table.*,
					description as _mail_type,
					client_name(client_id) AS name_full,
					mail_date + 7 AS returns_on
					FROM $mail_table
					LEFT JOIN l_mail_type USING (mail_type_code)";

$income_table="income";

$subsidy_table = 'housing_unit_subsidy';
$subsidy_select_sql = "SELECT * FROM $subsidy_table ";

// NOTE:  residency refers to own org housing, residency_other refers to non-org Housing
$residency_table="residence_own";
$residency_other_table="residence_other";
$residency_table_id="OccupancyID";
$residency_select_sql= "
	SELECT  r.*,
            lp.description AS _agency_project_code, 
            lf0.description AS _PriorSit, 
            ldr.description AS _ExitReason
	FROM 
		$residency_table as r
            LEFT JOIN l_agency_project lp ON (lp.agency_project_code=r.housing_project_code)
            LEFT JOIN l_facility lf0 ON (r.moved_from_code=lf0.facility_code)
            LEFT JOIN l_facility lf1 ON (r.moved_to_code=lf1.facility_code)
            LEFT JOIN l_departure_reason ldr USING (departure_reason_code)";
$residency_other_select_sql="SELECT $residency_other_table.*, 
									description AS _facility
							FROM $residency_other_table
							LEFT JOIN l_facility USING (facility_code)";
$charges_table = "charge";
$charges_type_table = "l_charge_type";
$payments_table = "payment";

$units_table = "housing_unit";
$units_select_sql = "SELECT * FROM $units_table ";

// These selects & filters are for calculating rents
$unit_residency_select_sql="
SELECT 
      housing_unit_code,
	client_id,
	$units_table.housing_project_code,
	residence_date,
      residence_date_end,
      move_in_type,
      moved_to_unit
FROM    $units_table
        LEFT JOIN l_housing_project USING (housing_project_code)
        LEFT JOIN $residency_table using (housing_unit_code)";

$unit_residency_filter["OVERLAPSORNULL:$residency_table.residence_date,$residency_table.residence_date_end"]="placeholder";

$placeholder_date_range=new date_range("1900-01-01","2099-12-31");
$rent_subsidy_select_sql = "
SELECT DISTINCT
	TEMP.housing_unit_subsidy_date AS contract_rent_start_date,
	TEMP.housing_unit_subsidy_date_end AS contract_rent_end_date,
	TEMP.unit_subsidy_amount AS contract_rent_amount,
	$income_table.rent_date_effective AS rent_start_date,
	$income_table.rent_date_end AS rent_end_date,
	$income_table.rent_amount_tenant AS rent_amount_tenant,
	TEMP.fund_type_code as subsidy_type_code
FROM 
	$income_table 
	LEFT JOIN residence_own USING (client_id)
	LEFT JOIN (SELECT $subsidy_table.* FROM $subsidy_table 
                   LEFT JOIN l_housing_project l USING (housing_project_code)
                     WHERE l.auto_calculate_subsidy_charges IS TRUE AND " . 
		read_filter(array('OVERLAPSORNULL:housing_unit_subsidy_date,housing_unit_subsidy_date_end'=>$placeholder_date_range)) . " ) as TEMP
	ON (TEMP.housing_unit_code=residence_own.housing_unit_code)";
//$rent_subsidy_filter["OVERLAPSORNULL:$subsidy_table.startdate,$subsidy_table.enddate"]="placeholder";
$rent_subsidy_filter["OVERLAPSORNULL:$income_table.rent_date_effective,$income_table.rent_date_end"]="placeholder";

$l_agency_project_table = "l_agency_project";
$project_select_sql = "SELECT agency_project_code AS value, description AS label
                    FROM $l_agency_project_table ";

// This array defines the fields that can be included in the client search:
$client_search_fields= array(     
				     "name_full",
				     "name_alias",
				     "name_full_alias",
				     "gender_code",
				     "dob",
				     "ssn",
				     "client_id",
				     "changed_at",
				     "clinical_id",
				     "added_by");

$logs_per_screen=25;

//--------Staff------//
define('AG_STAFF_PAGE','staff_display.php');

$alert_table = 'alert';
$alert_table_id = 'ref_id';

$clientref_table = "client_ref";

$disability_table = "disability";
$disability_lookup_table = "l_disability";
$income_lookup_table = "l_income";
$vet_lookup_table = "l_Veteran_status";
$why_lookup_table = "l_svc_need";
$resarr_lookup_table = "l_last_residence";

//unduplication stuff
//  Do you want to add another table to unduplicate client IDs on?
//  1. Go to the $unduplication_table_list array.
//  2. Add your new table as a new element in the array.
//      The table name is the key and the name of the client id field
//      is the value.  If the client id field in your table is client_id,
//      set the value to ""

$undups_url = "client_undup.php";
$undups_table = "tbl_duplication";
$undup_flag="approved_at"; //field in $undups_table to denote records that haven't been unduplicated
//until meta data can do this, enter tables below that have unique constrains (and the corresponding field as value)
//disability_client_id_key unique btree (client_id, disability_code)
$unique_constraint_tables=array("disability"=>"disability_code"); 

//-----assessment (shelter)-----//
$group_A_threshold_assessment_score=13; // Minimum score to be "Group A"

//-----Bugzilla-----//
//comment out these lines to disable Bugzilla links and queries
/*
$bugzilla_url="http://iww.desc.org/bugzilla/";
$show_bugzilla_url="$bugzilla_url/show_bug.cgi?id=";
$bugzilla_staff_table = "bugs.profiles";
$bugzilla_bug_table="bugs";
$bugzilla_select_sql = "SELECT *  FROM $bugzilla_bug_table"; //to disable bugzilla db queries, comment out this line
$bugzilla_template_account_id = 19; // a template user from which to populate email settings table for automatically created accounts
*/

//-----I & R-----//
$iandr_feed_table="agency_comment";
$iandr_feed_id="agency_id";
$iandr_page='iandr_display.php'; //adding because apparently, it was never set...JH

/*
 * Absolute maximum string length
 */
define('AG_MAXIMUM_STRING_LENGTH',128000);

/*
 * Default Telephone Area Code
 */
define('AG_DEFAULT_PHONE_AREA_CODE','206');

/*
 * Two-digit century cutoff: years less than value will be 20xx, otherwise, 19xx.
 */
define('AG_DATE_CENTURY_CUTOFF',20);

/* Report System Stuff */
define('AG_REPORTS_VARIABLE_PREFIX','crv_'); //pre-fixed to all form-variables
define('AG_TEMPLATE_DIRECTORY','templates');
define('AG_REPORTS_REGEX_TEMPLATES','/^(.*)[|](.*)([|](.*))?$/U');

/*
 * OpenOffice configuration 
 *
 *
 */

define('AG_OPEN_OFFICE_ENABLE_EXPORT',true);
define('AG_OPEN_OFFICE_DISABLED_MESSAGE','Open Office exports not enabled for this AGENCY installation');
//define('AG_OPEN_OFFICE_CALC_TEMPLATE','generic_spreadsheet.ods');
define('AG_OPEN_OFFICE_CALC_TEMPLATE','generic_spreadsheet.xlsx');
define('AG_CLIENT_CARD_TEMPLATE','client_id.sxw');
define('AG_STAFF_CARD_TEMPLATE','staff_id.sxw');

/* You need additional software (e.g., unoconv) installed for PDF and MS Office formats */
define('AG_OPEN_OFFICE_EXTERNAL_CONVERSION_ENABLED',false); //FIXME: make me a feature
define('AG_OPEN_OFFICE_PREFER_MS_FORMATS',true);
define('AG_OPEN_OFFICE_ALWAYS_PDF',false);

/*
 * OpenOffice Special Character Translation (see bugs 6009 and 18103)
 */

$AG_OPEN_OFFICE_TRANSLATIONS = array(
    chr(189) => chr(194) . chr(189),
    chr(147) => '&quot;',
    chr(148) => '&quot;',
    chr(149) => '*',
    chr(150) => '--',
    chr(194) => chr(194), // dummy, to prevent re-replacing characters (bug 6009
	chr(13).chr(10) => '<text:line-break/>',
	"\n" => '<text:line-break/>'
);

/* 
 * Error message hack for unknown characters Bug 6009, comment #9 
 */

for ($x=129; $x < 255; $x++)
{
	if (! isset($AG_OPEN_OFFICE_TRANSLATIONS[chr($x)]))
	{
		$AG_OPEN_OFFICE_TRANSLATIONS[chr($x)] =
			'</text:p><text:p></text:p><text:p>'
			. "UNKNOWN TRANSLATION:  PLEASE REPORT RECORD TYPE, ID and Character $x TO YOUR AGENCY SUPPORT STAFF OR SYSTEM ADMINISTRATOR, and/or FORWARD TO THE AGENCY MAILING LIST: agency-general@lists.sourceforge.net"
			. '</text:p><text:p></text:p><text:p>';
	}
}
		
/*
 * System variables available for merging into OO output
 */

$AG_OPENOFFICE_SYS_VARS = array(
	'confidential' => confidential('',0,'TEXT'),
	'staff_id'=>$GLOBALS['UID'],
	'org_name'=>org_name(),
	'org_name_short'=>org_name('short'));

define('AG_OPENOFFICE_GEN_PAGE',AG_REPORTS_URL . '?action=post_sql');

if (phpversion() >= '5') {

	set_postgresql_version();

}

/*
 * Setting this to true will result in a call to debug_backtrace() being
 * appended to error log messages
 */

define('AG_ERROR_LOG_BACKTRACE',false);

/*
* Define directory for attached files.
*/

define('AG_ATTACHMENT_LOCATION',$AG_HOME_BY_FILE . '/attachments/');

/*
* Define directory for document links.
*/

define('AG_HELP_DOC_LOCATION',$AG_HOME_BY_URL . '/help_docs/');

/*
 * Core tables for AGENCY
 * Additional tables defined in flavors
 * (e.g., client_config.php)
 */

$AG_ENGINE_TABLES_CORE=array(
    'alert',
    'alert_notify',
    'attachment',
    'attachment_link',
    'db_access',
    'db_revision_history', // database modifications applied
    'info_additional',
    'info_additional_type',
    'log',
    'news',
    'permission',
    'pg_catalog', // From the database
    'reference',
    'report',
	'report_block',
	'report_usage',
    'staff',
    'l_staff_position',
//  'staff_driver_authorization',
    'staff_employment',
//  'staff_identifier',
//  'staff_key_assign',
//  'staff_language',
    'staff_password',
    'staff_phone',
//  'staff_pto_rollup',
//  'staff_qualification',
    'staff_remote_login',
//  'staff_request',
//  'staff_termination',
    'user_option',
    //---Weird or Hacky Objects---//
    'config_file',
	'file_exchange',
    'def_array',
	'db_agency_functions',
    'generic_sql_query' // a pseudo object for handling generic SQL
);
?>
