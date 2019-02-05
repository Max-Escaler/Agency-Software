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
define('AG_PRODUCTION_DATABASE_NAME','donor');
define('AG_PRODUCTION_DATABASE_SERVER','localhost');

define('AG_LIST_EMPTY_HIDE',false); //show empty records by default

$AG_TEXT['AGENCY_HOME_TITLE'] = 'Donor Database Home Page';
$AG_TEXT['LINK_HOME'] = 'Donor DB Home';

$AG_IMAGES['AGENCY_LOGO_MEDIUM'] = $off.'images/donor_logo_medium.png';

$AG_MENU_LINKS=array(
	hlink($agency_home_url,'Home'),
	hlink('client_reg.php','Add ' . AG_MAIN_OBJECT),
	hlink('menu.php','Menu'),
	hlink(AG_REPORTS_URL,'Reports'));

$AG_ENGINE_TABLES=array_merge($AG_ENGINE_TABLES_CORE,array(AG_MAIN_OBJECT_DB,
				'address',
				'donor_extended',
				'gift',
				'gift_cash',
				'gift_extended',
				'gift_inkind',
				'gift_united_way',
				'donor_total',
				'donor_stat',
				'donor_flag',
				'donor_link',
				'donor_note',
				'sent_mail',
				'staff_assign',
				'volunteer_reg',
				'volunteer_hours',
				'proposal'
//				'export_gift_mip',
//				'export_donor_mip'
));

/*
 * to add a new type of quicks search, it is assumed that there will be a corresponding CSS class of the same 
 * name to handle the coloring. Otherwise the new qs will be the default table color.
 * search.php must know how to handle the request as well.
 */
$AG_QUICK_SEARCHES = array(AG_MAIN_OBJECT_DB=>ucwords(AG_MAIN_OBJECT),'staff'=>'','notes'=>'');
define('AG_DEFAULT_QUICKSEARCH_TEXT','Quick Search');

// Number of characters typed before database match is
// triggered for auto complete.

// Smaller organizations might want to reduce this to 2.
// The smaller the number, the more database utilization,
// and with larger databases 2 or 3 letter matches might not
// be very meaningful or specific.
define('AG_QUICKSEARCH_AUTOCOMPLETE_MIN_LENGTH',4);


$colors=array(
    "client"=>"#FFFFCF",
    "staff"=>"#DFFFDF",
    'staff_alert_bg'=> '#DFFFDF',
    'staff_alert_color1' => '#DFF0DF',
    'staff_alert_color2' => '#DFFFDF',
    "alert"=>"#FFc0c0",
    "addl"=>"#eff9ff",
    "text"=>"#ffffe8",
    "pick"=>'#83ba8b',
    "menu"=>"#889999",
    "nav"=>"#CCCCFF",
    "blank"=>"white",
    "gray"=>"gray",
    "red"=>"red",
    'client_command_box'=>'#82BB8C',
    'view_system_field_bg'=>'#EEEEEE',
    // generic colors for engine record display:
    'label_bg'=>'#fffff4',
    'value_bg'=>'#fdfdfd'
);

$client_select_sql="SELECT *,address_names(address_id(donor_id)) AS names FROM ".AG_MAIN_OBJECT_DB;

$client_search_fields= array(     
				     'donor_name',
				     'donor_id',
				     'added_by');

//OpenOffice Stuff Here:
$donor_profile_template  = 'donor_profile.sxw';
$donor_envelope_template = 'envelope_plain.sxw';

$donor_data_merge_templates = array($donor_profile_template=>'Print Donor Profile',
						$donor_envelope_template=>'Print Donor Envelope');

//------- Main Object Registration -------//
$main_object_reg_search_fields = array('donor_name');
$main_object_reg_prompt = 'Please Enter ' .ucfirst(AG_MAIN_OBJECT). ' Name';

//eliminate photo support
$AG_CLIENT_PHOTO_BY_FILE='';

/*
 * Controls the options for menu.php
 */
$AG_MENU_TYPES = array(
/*
			  'mip'   => array('title' => 'MIP',
						 'perm'  => 'mip_export'),
*/
			  'admin'    => array('title' => 'AGENCY Administration',
						    'perm'  => 'admin')
			  );


?>
