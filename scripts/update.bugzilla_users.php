#!/usr/bin/php
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



  /*
   * This script updates bugzilla profiles table for staff
   */

$MODE='TEXT';
$off = dirname(__FILE__).'/../';
$AG_FULL_INI = false;
include $off.'command_line_includes.php';

function bugzilla_crypt($pwd)
{
	/*
	 * mimicking functionality of Util::bz_crypt from Bugzilla 2.22.1
	 */

	$saltchars = array_merge(range(0,9),
					 range('A','Z'),
					 range('a','z'),
					 array('.','/'));

	$salt = '';
	for ($i=0; $i < 8; $i++) {
		$salt .= $saltchars[array_rand($saltchars)];
	}

	$cryptpassword = crypt($pwd,$salt);

	return $cryptpassword;

}

function update_bugzilla( $staff )
{
	/*
	 * If Bugzilla entry does not exist, add it for this staff person...
	 */

	global $bugzilla_staff_table,$bugzilla_template_account_id;

	/*
	 * 1.  Does this entry exist?
	 */

	$login = $staff['login_name'];

	$res = agency_query('SELECT * FROM '.$bugzilla_staff_table,array('login_name' => $login));

	$query = "SELECT * FROM $bugzilla_staff_table WHERE " . sqlsetify( 'login_name',$login,"last" );
	$check = mysql_query($query) or sql_warn( "unable to query bugzilla table with $query");

	if (mysql_num_rows($check) > 0) {

		return;

	}

	$staff['cryptpassword'] = bugzilla_crypt($staff['cryptpassword']);
	
	$update = agency_query(sql_insert($bugzilla_staff_table,$staff));
	
	if ($update) {

		//get back new id
		$res = sql_fetch_column(agency_query('SELECT * FROM '.$bugzilla_staff_table,array('login_name'=>$login)),'userid');
		$userid = $res[0];

		// new user created, now populate email_settings from template account
		$email_query = agency_query('INSERT INTO bugs.email_setting SELECT '.$userid.',relationship,event FROM email_setting WHERE user_id = '.$bugzilla_template_account_id);

		if ($email_query) {
			outline("Bugzilla account created for $login");
		} else {
			outline('Created Bugzilla account for '.$login.', but failed to insert email preferences.');
		}

	} else {

		sql_warn("Couldn't query to create bugzilla account for $login.<br>Query was $create_query");

	}
	return;
}

// get list of active staff


$staff_array = get_generic('','','','export_staff_bugzilla');

//connect to mysql

$WHICH_DB = 'my';
$database['my'] = 'bugs';
db_connect();

foreach ($staff_array as $staff) {

	update_bugzilla($staff);

}

?>
