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

// password.php
// Stuff for handling passwords

//FIXME: thesse functions should, at some point, be merged into (or work with) the Auth class (auth.php)

function can_change_password( $staff_id,$user_id="" )
{

	/*
	 * can user_id change password of staff_id?
	 * currently, user can change own, or people with permissions defined in config_staff_password.php
	 */

	$id = orr($id,$GLOBALS['UID']); // default to current user

	/*
	 * First, check to see if it is a password add or change,
	 * since these have different permissions
	 */

	$def = get_def('staff_password');

	$filter = array('staff_id' => $staff_id);

	$res = get_generic($filter,'','','staff_password');

	if (count($res) < 1) {

		$perm = $def['perm_add'];

	} else {

		$perm = $def['perm_edit'];

	}

	return ($id == $staff_id) || has_perm($perm) || ($id == $GLOBALS['sys_user']);

}

//function password_check($password,$type="",$id="")
// FIXME: from PHG git update.  Why was $check all and $all_in_array removed upstream, but left in code???
function password_check($password,$type="",$id="",$check_all=false)
{
// Check the supplied password against a user (defaults to current user)
// Type is the type of the supplied password. Currently MD5 is the only option

	global $UID,$AG_AUTH_DEFINITION;
	if (!$password) { // this will effectively disable blank passwords
		return false;
	}

	$type=orr($type,$AG_AUTH_DEFINITION['DEFAULT_METHOD']);
	$id=orr($id,$UID);
	$prec=get_password($id,$type,$check_all);
	//outline(webify("Found: $prec.  Supplied $password"));
	return $check_all ? in_array($password,$prec) : ($prec==$password);
}


//function get_password($id = '', $type='')
// FIXME: from PHG git update.  Why was $check all and $all_in_array removed upstream, but left in code???
function get_password($id = '', $type='',$all_in_array=false)
{
	// FIXME: for now, this will remain it's own function since it works in methods other than md5
	// Auth::get_user_password() only returns md5'ed values (fairly easy fix though)

      global $AG_AUTH_DEFINITION, $UID;

	$id = orr($id,$UID);

	$def =& $AG_AUTH_DEFINITION;

	$password_table = $all_in_array ? 'staff_password' : 'staff_password_current';
	$password_field = $def['USE_MD5'] ? $def['PASSWORD_MD5_FIELD'] : $def['PASSWORD_FIELD'];
	$rec=agency_query("SELECT $password_field FROM $password_table",
					array("staff_id"=>$id));
	if ($all_in_array) {
		while ($x=sql_fetch_assoc($rec)) {
			$passes[]=$x[$password_field];
		}
		return $passes;
	}
	if (sql_num_rows($rec) <> 1)
	{
		return false;
	}
	$rec=sql_fetch_assoc($rec);
	$type=orr($type,$def['DEFAULT_METHOD']);
	if ($type=='MD5')
	{
	      return $def['USE_MD5']
			? $rec[$password_field]
			: false;
	}
}

function password_set($new_pass,$type,$id)
{

	/*
	 * Set user $id password
	 */

	if (!can_change_password($id)) {

		log_error('No permissions to change password for staff '.$id);
		return false;

	}

      global $AG_AUTH_DEFINITION, $UID;

	$def =& $AG_AUTH_DEFINITION;

	$password_table = $def['TABLE'];
	$password_field = $def['USE_MD5'] ? $def['PASSWORD_MD5_FIELD'] : $def['PASSWORD_FIELD'];
	if (!$new_pass) {

		outline(alert_mark('Can\'t set password to blank.'));
		return false;

	}
	$type=orr($type,$def['DEFAULT_METHOD']);

	switch ($type) {

	case 'MD5':

		//nothing to do here, new_pass has already been md5'ed

		break;

	default : //unknown type

		log_error('password_set() doesn\'t know how to handle type ' . $type);
		return false;

	}

	$new_vals = array($password_field    => $new_pass, 
				'staff_id'		   => $id,
				'changed_by'       => $UID,
				'added_by'         => $UID,
				'FIELD:changed_at' => 'CURRENT_TIMESTAMP');

	$count=sql_num_rows(agency_query("SELECT $password_field FROM $password_table",$filter));

	$max=sql_assign('SELECT password_retention_count FROM config_staff_password_current');
	if ($count > $max) {
		//log_error("Found $count password records for $id");
		alert_mark("Warning: Found $count password records for $id");
		//return false;
	}

	$new_vals['staff_id'] = $id;
	return agency_query(sql_insert($password_table,$new_vals));
}

function is_secure_password($password,$username=false,&$mesg)
{
	$spec = preg_quote(AG_AUTH_ALLOWED_SPECIAL_CHARS);
	if (preg_match_all('/([A-Z]){1,}|([a-z]){1}|([0-9'.$spec.']){1,}/',$password,$m)) {

		$pw_uc  = array_unique(array_filter($m[1]));
		$pw_lc  = array_unique(array_filter($m[2]));
		$pw_num = array_unique(array_filter($m[3],'not_be_null')); //this is required so that '0' passes as a digit

		/* Length */
		if (AG_AUTH_PASSWORD_MIN_LENGTH and (strlen($password) < AG_AUTH_PASSWORD_MIN_LENGTH) ) {
			$mesg .= oline('Password must be at least '.AG_AUTH_PASSWORD_MIN_LENGTH.' characters long');
			return false;
		}

		/* Correct number of characters */
		if (AG_AUTH_PASSWORD_UC_LIMIT and (count($pw_uc) < AG_AUTH_PASSWORD_UC_LIMIT) ) {
			$mesg .= oline('Password must contain at least '.AG_AUTH_PASSWORD_UC_LIMIT. ' uppercase character(s)');
			return false;
		}

		if (AG_AUTH_PASSWORD_LC_LIMIT and (count($pw_lc) < AG_AUTH_PASSWORD_LC_LIMIT) ) {
			$mesg .= oline('Password must contain at least '.AG_AUTH_PASSWORD_LC_LIMIT. ' lowercase character(s)');
			return false;
		}
		
		if (AG_AUTH_PASSWORD_NUM_LIMIT and (count($pw_num) < AG_AUTH_PASSWORD_NUM_LIMIT) ) {
			$mesg .= oline('Password must contain at least '.AG_AUTH_PASSWORD_NUM_LIMIT. ' number or special character');
			return false;
		}

		/* password variants */
		$password_lc = strtolower($password);
		global $AG_AUTH_LEET;
		$password_leet = strtr($password_lc,$AG_AUTH_LEET);
		$password_strip = preg_replace('/[0-9]/','',$password_lc);

		/* Username Check */
		if ($username and AG_AUTH_PASSWORD_USERNAME_CHECK) {
			$username_lc = strtolower($username);
			if (in_array($username_lc,array($password_lc,$password_leet,$password_lc,
								  strrev($password_lc),strrev($password_leet),strrev($password_strip)))) {
				$mesg .= oline('Password is based on username');
				return false;
			}
		}

		/* Dictionary Check */
		if (AG_AUTH_PASSWORD_DICT) {
			if (is_readable(AG_AUTH_PASSWORD_DICT)
			    and ($dict = fopen(AG_AUTH_PASSWORD_DICT,'r'))) {
				$matching = false;
				while (!$matching and !feof($dict)) {
					$word = strtolower(trim(fgets($dict,1024)));
					if (strlen($word) >= strlen($password_strip)
					    and (preg_match('/'.preg_quote($password_lc).'/',$word)
						   or preg_match('/'.preg_quote($password_leet).'/',$word)
						   or preg_match('/'.preg_quote($password_strip).'/',$word))
					    ) {
						$mesg .= oline('Password is based on a dictionary word.');
						$matching = true;
					}
				}
				fclose($dict);
				if ($matching) { 
					return false;
				}
					
			} else {
				$mesg .= log_error('is_secure_password(): Unable to read password dictionary ('.AG_AUTH_PASSWORD_DICT.')');
				return false;
			}
		}
		return true;
	}

	$mesg = oline('Password is composed of unrecognized characters');
	return false;
}

function link_password_change($id=null,$link_text='',$options='')
{

	global $UID;

	$id        = orr($id,$UID);
	$link_text = orr($link_text,'Click to change password');

	return hlink_if(AG_STAFF_PAGE . '?id='.$id.'&action=change_password',
			    $link_text,can_change_password($id),'',$options);

}

function is_secure_transport()
{
    return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off'));
}

function http_authenticate()
{
	// 8/2015--Temporarily (permanently) disabling this.
	// FIXME:  This function should be fixed or removed.
	return false;

    // Authenticate using HTTP
    // Adding for calendar support
    if (! is_secure_transport())
    {
        /*
           Run this over SSL only, as it is HORRIBLY insecure otherwise!
        */
        $url = 'https://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $msg = oline('HTTP authentication can only be done over SSL.')
                . oline('Try this url instead: ' . $url );
        out($msg);
        return false;
    }

    $USER=$_SERVER['PHP_AUTH_USER'];
    $PASS=$_SERVER['PHP_AUTH_PW'];
    $UID=staff_id_from_username($USER);

    // FIXME:  will need some kind of logic to work with MD5
    if (!password_check(flipbits($PASS),'flipbits',$UID))
    {
         // Bad or no username/password.
         // Send HTTP 401 error to make the
         // browser prompt the user.
         header("WWW-Authenticate: " .
            'Basic realm=\”Protected Page: ' .
            "Enter your username and password " .
            'for access.”');
     header('HTTP/1.0 401 Unauthorized');
     return false;
    }
    else
    {
         return $UID;
    }
}

function get_password_expires_info( $id ) {
	$filter=array('staff_id'=>orr($id,$GLOBALS['UID']));
	//return sql_fetch_assoc(agency_query('SELECT staff_password_date_end,expiration_warn_on FROM staff_password_current',$filter));
	if (!($x=sql_fetch_assoc(agency_query('SELECT staff_password_date_end,expiration_warn_on,CASE WHEN COALESCE(staff_password_date_end,current_date)>=current_date THEN \'CURRENT\' ELSE \'EXPIRED\' END AS password_status FROM staff_password',$filter,'staff_password_date DESC',1)))) {
		return array('has_password'=>false);
	} else {
		$x['has_password']=true;
	}
	return $x;
}

function password_expires_on( $id ) {
	$info=get_password_expires_info($id);
	return dateof($info['staff_password_date_end'],'SQL');
}

function password_expires_on_f( $id, $format_warning=false ) {
	$info = get_password_expires_info( $id );
	$exp=$info['staff_password_date_end'];
	$warn=$info['expiration_warn_on'];
	$status=$info['password_status']=='CURRENT' ? 'Expires' : 'Expired';
	$msg = (!$info['has_password']) ? 'No password set'
			:
			($exp
			? "$status on " . dateof($exp)
			: 'Does not expire')
			;
	if (!$format_warning) {
		return $msg;
	}
	if ($exp and $warn and ( dateof($warn,'SQL') <= dateof('now','SQL') ) ) {
		$msg = 'Your Password ' . $msg . ' (' . link_password_change($id,'Change now') . ')';
		return $msg;
	}
	return ''; 
}

?>
