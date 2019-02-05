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
 *  Basic Authorization Class Auth()
 *
 *  Password is md5ed on clients-side along w/ a unique one-time hash:
 *  
 *  password = md5(md5(password) + hash);
 *
 *  this insures that even if someone is 'listening' they will never 
 *  have access to the password, or md5 of the password.
 *
 *
 *  users are re-directed to their original request using the $_SERVER['REQUEST_URI']
 *  super-global. (Requests for logout are stripped away to avoid cyclic behavior.)
 *
 *  login sessions are maintained by calculating a "big hash" value and comparing this to the
 *  stored "big hash" in the auth session array. If they match, the user is still logged in,
 *  any variance will result in the login session's termination.
 *
 * 
 *
 *                    **** WARNING: $engine _may_ not set when Auth is initialized ****
 *
 */


class Auth { 
	
      //form variable names
      var $var_username    = 'agency_auth_username';
      var $var_password    = 'agency_auth_password';
      var $var_unique_hash = 'agency_auth_unique_hash';
	var $var_user_switch = 'agency_switch_user';
	var $verbose_user_errors=false; // pass specific error messages, or below general error message
	var $general_error = 'Either your username or password are incorrect.';

	//track user login attempts and such
	var $log_user_logins = true; //if enabled, tbl_user_login must exist in db
	var $log_user_table = 'tbl_user_login';
	var $log_user_uid_field = 'staff_id';
	var $log_user_ip_address_field = 'ip_address'; //set to null if field doesn't exist, or tracking is not desired
	var $log_user_failed_attempts_field = null; //see above 
      
      //internal variables
      var $mesg;
      var $error;
	var $failed;
	 var $kiosk_active = false;
	/*
	 * use very carefully. When this is set to 
	 * true, a failed login won't exit the 
	 * script and provide a login box.
	 */
	var $suppress_login = false; 

      function Auth()
      {
		$this->error=array();
		$this->mesg=array();
      }

	function reset_kiosk()
	{
		$this->new_session();
		$hash=$this->set_hash(microtime());
	        $this->make_auth_array(AG_KIOSK_USER_SET,$hash);
		$this->kiosk_active=true;
		$_SESSION['AUTH']['kiosk_active']=true;
	}

	function kiosk_active()
	{
		return $this->kiosk_active;
	}
      
      function authenticate()
      {
		global $AG_AUTH_DEFINITION;
		//do session stuff here
		session_name($AG_AUTH_DEFINITION['SESSION_NAME']);
		session_cache_expire($AG_AUTH_DEFINITION['SESSION_EXPIRE']);
		session_start();
		if ($_SESSION['AUTH']['kiosk_active']) {
			$this->kiosk_active=true;
		}
		if (!AG_KIOSK_MODE_SET) {
			if ($this->kiosk_active()) {
				$_SESSION['AUTH']['kiosk_active']=false;
				$this->kiosk_active=false;
				$this->new_session();
			}
		}

		$last_user=$_SESSION['USER_INFO'][$AG_AUTH_DEFINITION['USER_ID_FIELD']];
		if (!$this->is_valid())
		{
			if ($this->suppress_login or AG_KIOSK_MODE_SET) {
				return false;
			}
			echo $this->login();
			exit;
		}
		if (!($last_user===$GLOBALS['UID'])) //NEW USER ($UID) is set below in make_auth_array() for new users
		{
			$tmp_auth = $_SESSION['AUTH']; //preserve newly generate authentication info
			$_SESSION = array(); //UNSET SESSION VARS
			$this->new_session(); //destroys old and starts a new session
			$_SESSION['AUTH']=$tmp_auth; //PASS ALONG VALIDATED AUTH INFO
			$_SESSION['USER_INFO']=$this->get_user_info($tmp_auth['username']);
		}
		return true;
      }


      function is_valid()
      {
	    global $AG_TEXT;
	    if ($_REQUEST['logout_agency']=='Y') {
	            $_SESSION['AUTH']=array();
		    if ($_REQUEST['destroy_session']=='Y') {
		            $this->new_session();
		    }
		    array_push($this->mesg,$AG_TEXT['AUTH_LOGOUT_MESSAGE']);
		    return false;
	    } elseif($this->new_valid_auth()) {
			if (!$this->kiosk_active()) {
				$_SESSION['auth']['kiosk_active']=false;
			}
			return true;
	    } elseif ($this->failed_user_switch()) {
	            return false;
	    } elseif ($this->existing_auth()) {
	            return true;
	    } else {
	            return false;
	    }
      }

      function login()
      {
	    global $colors, $AG_TEXT;

	    $hash = $this->set_hash(microtime());
	    $destination = htmlentities(str_replace('logout_agency=Y','',$_SERVER['REQUEST_URI']));
		if (substr($destination,0,2)=='//') { $destination=substr($destination,1); } // FIXME:  Bandaid hack to strip out double slash
	    $post_variables = $this->post_variables();
	    $advisory_box=div($AG_TEXT['LOGIN_ADVISORY'],'loginAdvisory');
	    $login_form=formto($destination,'',$this->get_onsubmit())  //this should take care of DESIRED variables coming in via GET
			    . div( $advisory_box . div(
					'Login to '.link_agency_public_home('','',' target="_blank"') . agency_logo_small() . organization_logo_small() ,'loginTitle','')
		    . div(
			    ( count($this->mesg)>0 ? div($this->format_messages(),'',' class="loginFormMessages"') : '')
				    // output a javascript warning, viewable only if js is disabled
				    . ('<noscript>' 
					 . div(red('Note: javscript must be enabled for this site in order to login, and for functionality within.'),
						 '','class="loginFormMessages"')
					 . '</noscript>'
					 )
				    . div('Username:','',' class="loginFormLabel"')
				    . div(formvartext($this->var_username,$_REQUEST[$this->var_username],' class="loginFormBox"'),'',' class="loginFormBox"')
				    . div('Password:','',' class="loginFormLabel"')
				    . div(formpassword_md5($this->var_password,false,true,$this->var_unique_hash,'class="loginFormBox"'),'',' class="loginFormBox"')
				    . div(button($AG_TEXT['AUTH_LOGIN_BUTTON_TEXT'],'submit','logbutton','',"",' class="loginFormButton"'),'',' class="loginFormButton"')
				    . ( count($this->error)>0 ? div($this->format_errors(),'',' class="loginFormMessages"') : '')
					. oline(hlink(AG_PASSWORD_RESET_URL,'I forgot my password'),2)
				    ,'loginContainer')
			    . div(oline($AG_TEXT['CONFIDENTIAL_STATEMENT'],2) . $AG_TEXT['COPYRIGHT_STATEMENT'],'loginConfidential')
			    ,'',' class="loginForm"')
		  . hiddenvar($this->var_unique_hash,$hash)
		    . $post_variables
		  . formend();
	    //set focus to username field
 	    form_field_focus($this->var_username);

	    link_style_sheet('login.css');
	    $out=html_start($AG_TEXT['LOGIN_TITLE']) . $login_form . html_end();
	    return $out;
      }

	function top_login_box()
	{
		global $AG_TEXT, $AG_HEAD_TAG;
		$hash = $this->set_hash(microtime());
		$form = div(span($AG_TEXT['AUTH_LOGIN_TOP_BOX_LABEL'])
				. formto(htmlspecialchars($_SERVER['REQUEST_URL']),'',$this->get_onsubmit())
				. formvartext($this->var_username,'Username','class="agencyTopLoginInput" onclick="this.value=\'\';"')
				. formpassword($this->var_password,'password',
							 'id="topLoginBoxPassword" autocomplete="off" class="agencyTopLoginInput" onfocus="this.value=\'\'"')
				. button($AG_TEXT['AUTH_LOGIN_TOP_BOX_BUTTON_LABEL'],'',$this->var_user_switch,'',
					  '',' class="agencyTopLoginInput"')
				. hiddenvar($this->var_unique_hash,$hash)
				. formend() . toggle_label("Login as..."),'agencyTopLoginBox','class="hiddenDetail"');
		$AG_HEAD_TAG .= md5_header_tags($this->var_password,true,$this->var_unique_hash); //this must be called after form generation to generate proper js
		return $form;
	}
      
      function logout($text='')
      {
	    $text=orr($text,$GLOBALS['AG_TEXT']['AUTH_LOGOUT_LINK_TEXT']);
	    //provide super-user/debug tool for logging out & killing session
	    if (has_perm('super_user')) {
		    $kill = hlink($_SERVER['PHP_SELF'].'?logout_agency=Y&destroy_session=Y',alt(red(smaller(' (x)')),'Logout and destroy session'));
	    }
	    return hlink($_SERVER['PHP_SELF'].'?logout_agency=Y',$text).$kill;
      }

	function new_session()
	{
		global $AG_AUTH_DEFINITION;
		@session_destroy();   //DESTROY SESSION
		@session_name($AG_AUTH_DEFINITION['SESSION_NAME']);
		@session_cache_expire($AG_AUTH_DEFINITION['SESSION_EXPIRE']);
		@session_start();    //START NEW SESSION
	}

	function post_variables()
	{
		$post = array();
		foreach($_POST as $key=>$val) {
			if (!in_array($key,array($this->var_username,$this->var_unique_hash,$this->var_password,'logbutton'))) {
				$post[$key] = dewebify_array($val);
			}
		}
		return form_encode($post,'');
	}

      function new_valid_auth()
      {
	    $password=$_REQUEST[$this->var_password];
	    $username=$_REQUEST[$this->var_username];
	    $hash=$_REQUEST[$this->var_unique_hash];
	    $this->failed =& $_SESSION['failed_login_attempts'];
	    
	    if (!$hash) {

		  return false; //new login, no errors, or existing auth

	    }

	    if (!$username) {

		  array_push($this->error,'You must enter a username');
		  return false;

	    }

	    if (!$password) {

		  array_push($this->error,'You must enter a password');
		  return false;

	    }

	    $tmp_password=$this->get_user_password($username);
	    if (!$tmp_password) {

		    $this->failed++;
		    $err = $this->verbose_user_errors 
			    ? 'No user information found for '.bold($username).', please try again'
			    : $this->general_error;
		  array_push($this->error,$err);
		  return false;
	    }

	    if (!($password===md5($tmp_password . $hash))) { //the password is md5ed together w/ the hash on the client-side

		    $this->failed++;
		    $err = $this->verbose_user_errors 
			    ? 'Invalid password for '.bold($username).', please try again.'
			    : $this->general_error;
		    array_push($this->error,$err);
		  return false;
	    }

	    //the basics have been passed, now verify the hashes and such
	    $tmp_hash=$this->get_hash();

	    if ($tmp_hash==$hash) { //This is a valid login

	            //this makes the authorization array, which should be cleared if the function then fails
	            $this->make_auth_array($username,$hash); //this creates a session var for further authentication
		    
		    //Now, check for remote access
		    if (!$this->is_internal_access()) {
		       
		            if (! ($this->staff_login_allowed() and $this->staff_remote_login_allowed())) {

			            $this->failed++;
				    $err = $this->verbose_user_errors
					    ? 'You aren\'t allowed remote access'
					    : $this->general_error;
				    $this->log_user_login('','Successful login, user not allowed remote access');
				    array_push($this->error,$err);
				    //this removes the authorization because this person shouldn't have access
				    $this->clear_auth_array();
				    return false;
				       
			    }

			    
			    //remote login allowed, now check password strength
			    if (($pwd = $this->get_raw_password($username)) //if md5, can't check password strength
				  && !is_secure_password($pwd,$username,$dummy)) {
		       
			            $this->failed++;
				    $err = $this->verbose_user_errors
				            ? 'Password not strong enough for remote access'
					    : $this->general_error;
				    $this->log_user_login('','Successful login, user password not strong enough');
				    array_push($this->error,$err);
				    //this removes the authorization because this person shouldn't have access:
				    $this->clear_auth_array();
				    return false;
				    
			    }
			    
		    }

		    $this->log_user_login();
			if ($username != AG_KIOSK_USER_SET) {
				$_SESSION['kiosk_active']=false;
				$this->kiosk_active=false;
			}
		    return true;
	    }
	    return false;
      }
      
      function failed_user_switch() {
		//user has submitted user switch form, but failed to pass new_user test above
		global $AG_TEXT;
		if ($_REQUEST[$this->var_user_switch]===$AG_TEXT['AUTH_LOGIN_TOP_BOX_BUTTON_LABEL']) {
 			$_SESSION['AUTH'] = null; //destroy current login
			return true;
		}
	}

	function existing_auth()
	{
		global $AG_AUTH_DEFINITION;
		$auth=$_SESSION['AUTH']; //should be an array
		$username=$auth['username'];
		if (!is_array($auth) || !$username) {
			return false;
		}

		if ($this->timeout($auth)) {
			return false;
		}


		$password = $this->get_user_password($username);
		$big_hash = md5($auth['username'] . $password . $auth['hash'].$_SERVER['HTTP_USER_AGENT']);


		if ($big_hash==$auth['BIG_HASH']) {

			$GLOBALS['UID'] = $_SESSION['USER_INFO'][$AG_AUTH_DEFINITION['USER_ID_FIELD']]; //set UID for returning user

			// this doesn't check for strong password if remote
			if (!($this->staff_login_allowed() and ($this->is_internal_access() or $this->staff_remote_login_allowed()))) { // re-check login allowed

				return false;


			}

			return true;

		}

		return false;

	}
	
	function get_user_password($username,$raw = false)
	{
		global $AG_AUTH_DEFINITION;

		$username = $AG_AUTH_DEFINITION['CASE_SENSITIVE_USERNAME'] ? $username : strtolower($username);

		// set password query
		if ($AG_AUTH_DEFINITION['USE_MD5']) {

			$password_query = $AG_AUTH_DEFINITION['PASSWORD_MD5_FIELD'];

		}

		$res=agency_query("SELECT {$password_query} AS password 
                                FROM {$AG_AUTH_DEFINITION['VIEW']} 
                                   LEFT JOIN {$AG_AUTH_DEFINITION['STAFF_TABLE']} USING ({$AG_AUTH_DEFINITION['USER_ID_FIELD']})
                                WHERE {$AG_AUTH_DEFINITION['USERNAME_FIELD']}=".sql_escape_literal($username)." AND login_allowed AND is_active");
		if (sql_num_rows($res)<1)
		{
			return false;
		}
		$tmp=sql_fetch_assoc($res);
		return $tmp['password'];
	}

	function get_raw_password($username)
	{
		/*
		 * Returns unencrypted password, or false if passwords are stored in md5.
		 */

		global $AG_AUTH_DEFINITION;
		
		if ($AG_AUTH_DEFINITION['USE_MD5']) {

			return false;

		}

		return $this->get_user_password($username,$raw = true);

	}	
	
	function get_user_info($username)
	{
		global $AG_AUTH_DEFINITION;
		$username= $AG_AUTH_DEFINITION['CASE_SENSITIVE_USERNAME'] ? $username : strtolower($username);
		$res=agency_query("SELECT * FROM {$AG_AUTH_DEFINITION['STAFF_TABLE']} WHERE {$AG_AUTH_DEFINITION['USERNAME_FIELD']}=".sql_escape_literal($username)." AND login_allowed AND is_active");
		if (!$res)
		{
			return false;
		}
		return sql_fetch_assoc($res);
	}
	
	function timeout($auth)
	{
		global $AG_AUTH_DEFINITION;
		$auth_length=$AG_AUTH_DEFINITION['EXPIRE'] * 60; //convert to seconds
		$start_time=$auth['auth_time'];
		$now=time();
		if ( ($now-$start_time)<$auth_length )
		{
		        $_SESSION['AUTH']['auth_time']=$now; //reset the clock
			return false; //hasn't timed out
		}
		return true;
	}
	
	function set_hash($hash)
	{
		//sets a session variable 
		$hash = $_SESSION['LOGIN_UNIQUE_HASH'] = md5($hash); //this can't be recalculated, so it is saved
		return md5($_SERVER['HTTP_USER_AGENT'] . $hash);
	}
	
	function get_hash()
	{
		return md5($_SERVER['HTTP_USER_AGENT'] . $_SESSION['LOGIN_UNIQUE_HASH']);
	}
	
	function make_auth_array($username,$hash)
	{
		global $AG_AUTH_DEFINITION;
		$password=$this->get_user_password($username);
		$_SESSION['AUTH'] = array('username'=>$username,
						  'hash'=>$hash,
						  'BIG_HASH'=>md5($username . $password . $hash . $_SERVER['HTTP_USER_AGENT']),
						  'auth_time'=>time()
						  );
		$_SESSION['USER_INFO'] = $tmp = $this->get_user_info($username);
		$GLOBALS['UID']=$tmp[$AG_AUTH_DEFINITION['USER_ID_FIELD']];
	}

	//added to prevent remote login w/insecure password
	function clear_auth_array()
	{
	        $_SESSION['AUTH'] = null;
	}

	function format_errors()
	{
	        foreach($this->error as $error)
		{
			$tmp.=red($error)."<br />\n";
		}
		return $tmp;
	}
	
	function format_messages()
	{
		foreach($this->mesg as $mesg)
		{
			$tmp.=bold($mesg)."<br />\n";
		}
		return $tmp;
	}

	function get_onsubmit($style='Hash')
	{
		return ($style=='Hash')
			? 'onsubmit="do'.$style.'ChallengeResponse(); return true;"'
			: 'class="doChallengeResponse"';
	}

	function get_password_field($auto_focus=false)
	{
		return formpassword_md5($this->var_password,$auto_focus,'','','class="passwordField"');
	}

	function reconfirm_password() //function to be used for user password re-confirmation (widget, engine, etc)
	{
		global $AUID,$UID;
		$def=get_def('staff');
		$check_pwd = $_REQUEST[$this->var_password];
		if (isset($AUID) and ($AUID !== $UID)) { //identity switching
			$rec = array_shift(get_generic(array($def['id_field']=>$UID),'','',$def));
			$username = $rec['username'];
		} else {
			$username = $_SESSION['USER_INFO']['username'];
		}
		$good_pwd = $this->get_user_password($username);
		return $check_pwd == $good_pwd;
	}

	function log_user_login($added_by='',$sys_log='')
	{
		if (!$this->log_user_logins) {
			$this->failed = 0;
			return true;
		}
		
		$success = true;
		global $UID; //user should have a UID by now
		if (!$UID) {
			log_error('User login permitted w/o UID');
			$success = false;
		}

		$log_rec = array();
		$staff_field = $this->log_user_uid_field;
		$log_rec[$staff_field] = $UID;
		if ($f = $this->log_user_ip_address_field) {
			$log_rec[$f] = $_SERVER['REMOTE_ADDR'];
		}
		if ($f = $this->log_user_failed_attempts_field) {
			$log_rec[$f] =  orr($this->failed,0);
		}

		//insert record
		if ($added_by) {
			$log_rec['added_by'] = $added_by;
		}
		if ($sys_log) {
			$log_rec['sys_log'] = $sys_log;
		}
		if (!db_read_only_mode()) {
			if (!$res = sql_query(sql_insert($this->log_user_table,$log_rec))) {
				log_error('Failed to log user login');
				$success = false;
			}
		}
		$this->failed = null;
		return $success;
	}

	function must_change_password()
	{
		return false;
	}

	function is_internal_access()
	{
		return sql_true(call_sql_function('is_internal_access',sql_escape_literal($_SERVER['REMOTE_ADDR'])));
	}

	function staff_remote_login_allowed($sid = null)
	{

		global $UID;
		$sid = orr($sid,$UID);

		return 
			( AG_AUTH_INTERNAL_ACCESS_ONLY === false )
			? true
			: sql_true(call_sql_function('staff_remote_login_allowed',sql_escape_literal($sid)));

	}

	function staff_login_allowed($sid = null)
	{
		global $UID;
		$sid = orr($sid,$UID);
		return sql_true(call_sql_function('staff_login_allowed',sql_escape_literal($sid)));
	}

} //end class Auth()

function has_perm($perm_list=null,$mode="R",$staff_id="")
{
	/*
	 * Primary function to test for various types of permissions.
	 * $perm_list is passed, can be array or string (or string list w/ commas)
	 * of the permission types you're interested in.  The function returns
	 * true if any of the permissions exist.
	 *
	 * $mode specifies what kind of access you're testing for, and can be
	 * 'R', 'W', or 'S' (Read, Write, Supervisor).
	 *
	 * This function relies on looking at the set of staff permission records, 
	 * and the staff record (particularly the staff_position_code, agency_program_code
	 * and agency_project_code fields, to determine position-based access).
	 *
	 * Super-user permissions are checked at the very beginning, and the function
	 * will always simply return true for super-users.
	 */
	global $UID;
	$staff_id=orr($staff_id,$UID);

	/*
	 * Get staff record, and cache.
	 */
	static $uid_staff_array;
//	$s_def=get_def('staff');
	if (!$staff = $uid_staff_array[$staff_id]) {
		// FIXME: had to hard code staff, as get_def was causing infinite loop
		// $staff = $uid_staff_array[$staff_id] = array_shift(get_generic(array($s_def['id_field']=>$staff_id),'','',$s_def));
		$staff = $uid_staff_array[$staff_id] = array_shift(get_generic(array('staff_id'=>$staff_id),'','','staff'));

	}

	$proj     = $staff['agency_project_code'];
	$prog     = $staff['agency_program_code'];
	$position = $staff['staff_position_code'];

	/*
	 * Get permissions out of DB
	 */
	static $db_checked = array();
	if (!$db_checked[$staff_id]) {

		/*
		 * Cache permission results to avoid 200+ queries per page
		 */

		/* this perm_filter should be used, instead of the direct query. */
		/*
		$perm_filter = array(array('staff_id'=>$staff_id,
									'agency_project_code'=>$project,
									'agency_program_code'=>$program,
									'staff_position_code'=>$position),
								array('<>:staff_id'=>$staff_id,
									'<>:agency_project_code'=>$project,
									'<>:agency_program_code'=>$program,
									'<>:staff_position_code'=>$position)
		);
		$perms = get_generic($perm_filter,'','','permission_current');
		*/
		$query="SELECT * FROM permission_current WHERE ((staff_id = '$staff_id'
			OR agency_project_code = '$proj'
			OR agency_program_code = '$prog'
			OR staff_position_code = '$position'
			)
		AND NOT ( (staff_id IS NOT NULL AND staff_id <> '$staff_id')
			OR  (agency_project_code IS NOT NULL AND agency_project_code <> '$proj')
			OR (agency_program_code IS NOT NULL AND agency_program_code <> '$prog')
			OR (staff_position_code IS NOT NULL AND staff_position_code <> '$position')
		))
		OR (staff_id IS NULL AND agency_project_code IS NULL AND agency_program_code IS NULL AND staff_position_code IS NULL)";
		$perms = sql_query($query);
		while ($perm = sql_fetch_assoc($perms)) {
			if ($perm['permission_read']) {
				$has_perms[$perm['permission_type_code']]['permission_read']=sql_true();
			}
			if ($perm['permission_write']) {
				$has_perms[$perm['permission_type_code']]['permission_write']=sql_true();
			}
			if ($perm['permission_super']) {
				$has_perms[$perm['permission_type_code']]['permission_super']=sql_true();
			}
		}
		$GLOBALS['UID_HAS_PERM_'.$staff_id] = $has_perms;
		$db_checked[$staff_id] = true;

/*

		$perms = get_generic(array('staff_id'=>$staff_id),'','','permission_current');

		while ($perm = sql_fetch_assoc($perms)) {

			$has_perms[$perm['permission_type_code']]=$perm;

		}

		$GLOBALS['UID_HAS_PERM_'.$staff_id] = $has_perms;
		$db_checked[$staff_id] = true;
*/
	} else {

		$has_perms = $GLOBALS['UID_HAS_PERM_'.$staff_id];

	}

	/*
	 * Super-user check
	 */
	if (sql_true($has_perms['SUPER_USER']['permission_super'])) {

		return true;

	} elseif ( (strtolower($perm_list)=='super_user') || (!$perm_list) ) {

		/*
		 * Return false for non-super-users
		 */
		return false;

	}

	if (! is_array($perm_list) ) {

		/*
		 * split $names into array
		 */
		$perm_list = explode(',',$perm_list);

	}


	/*
	 * Check for read-all access, and return if true
	 */
	if (($mode == 'R') && sql_true($has_perms['READ_ALL']['permission_read'])) {

		return true;

	}

	/*
	 * Assume no permission, this is changed to true if the staff has 
	 * any of the permissions in the loop
	 */
	$passed   = false;
	foreach( $perm_list as $perm_name ) {

		$perm_name = trim(strtolower($perm_name));
		switch ($perm_name) {

			/*
			 * This switch is used to test for 'group-based' conditions
			 * for approving permission.  After the switch, there is a
			 * test to look at the permission records for individual 
			 * granting of permissions
             		 *
             		 * The plan is to phase these out, and replace it with a
             		 * table instead. (Bug 28561)
			 */

		case 'any':
			$passed = true;
			break;

#		case 'shelter' :
		       /*
		        * We've removed DESC-specific permissions, but here
			* in a simple example of how you can set your own.
			*/
			/*
			 * Give I & R shelter perms
			 * (In this case, I&R is located in the shelter,
			 * but a separate project.  We want them to have
			 * shelter permissions.)
			 */
/*
			if ($proj == 'IR') {

				$passed = true;
				break;

			}
*/

		case strtolower($proj) : 

			/*
			 * If the perm-type is the same as the staff project, they are approved
			 */

			$passed=true;
			break;

		case 'generic_oo_export':

			/*
			 * Permissions to export any data as OO spreadsheet
			 */

			if (sql_true($has_perms['MANAGEMENT']['permission_read']) ||
			    in_array($position,array('CLINSUPER','SUPLDMH','MGRENTRY','MGRPROJ'))) { //bug 14984

				$passed = true;

			}
			break;

		case 'demo_mode':

			/*
			 * Run AGENCY in Demo Mode
			 */

			if (sql_true($has_perms['MANAGEMENT']['permission_read'])) {

				$passed = true;

			}
			break;

		case 'supervisor':

			/*
			 * supervisor - depends on l_staff_position being up-to-date regarding supervisor positions
			 */

			if (sql_true(sql_lookup_description($position,'l_staff_position',$v_field='',$d_field='is_supervisor'))) {

				$passed = true;

			}
			break;

		case 'ecl_admin':

			/*
			 * Elevated Concern List
			 */
			if (in_array($prog,array('CLINICAL','HOUSING')) 
			    && in_array($position,array('CLINSUPER','DIRMED','MGRCLINPRO','SUPOUT','SUPLDMH','MGRPROJ','MGRENTRY','MGRSUPP'))) {

				$passed = true;

			}
			break;

		default:

		}

		/*
		 * If they haven't passed, look at the individual permission records
		 */
		if (!$passed) {
			
			$fname = strtoupper($perm_name);
			switch ($mode) {

			case 'R' :

				if (sql_true($has_perms[$fname]['permission_read'])) { $passed = true; }

				break;

			case 'W' :

				if (sql_true($has_perms[$fname]['permission_write'])) { $passed = true; }

				break;

			case 'S' :

				if (sql_true($has_perms[$fname]['permission_super'])) { $passed = true; }

				break;

			case 'WR' :
			case 'RW' :

				if (sql_true($has_perms[$fname]['permission_read'])
				    && sql_true($has_perms[$fname]['permission_write'])) { $passed = true; }

				break;

			default :
				/*
				 * Unknown perm mode
				 */
			}

		}

	}

      return $passed;

}

function page_open()
{
	global $AG_USER_OPTION;
	$AG_USER_OPTION = new User_Option();
}

function page_close($silent=false)
{

	/*
	 * End HTML output
	 */
	if (!$silent) {

		global $AG_PAGE_FOOTER;
		if ( AG_OUTPUT_MODE == 'TEXT' ) {
			out($AG_PAGE_FOOTER);
		} else {
			out(div($AG_PAGE_FOOTER,'footer'));
			html_footer();
		}
	}

	/*
	 * No output to client should follow
	 */

	/*
	 * Post (if changed) user options
	 */
	global $AG_USER_OPTION;
	if (is_object($AG_USER_OPTION)) {

		$AG_USER_OPTION->post_array();

	}

	/*
	 * Send any errors
	 */
	global $AG_EMAIL_ERROR_CACHE,$NICK,$UID,$mail_errors_to;
	if (!be_null($AG_EMAIL_ERROR_CACHE) && isset($mail_errors_to)) {

		/*
		 * Errors occured
		 */
		$vars = get_phpinfo();
		$AG_EMAIL_ERROR_CACHE  .= "\n\n" . $vars;
		mail( is_array($mail_errors_to) ? implode($mail_errors_to,',') : $mail_errors_to,
			 "AGENCY Error: $NICK ($UID) running {$_SERVER['PHP_SELF']}",strip_tags($AG_EMAIL_ERROR_CACHE));

	}

	/*
	 * Close DB connection
	 */
	db_close();

}

function user_identity_management()
{
	global $UID, $AUID, $NICK;

	$NICK = $_SESSION['USER_INFO']['name_first'];
	$AUID = null; //initialize variable to prevent identity theft
	$EUID = $_SESSION['EUID'];

	if (has_perm('user_switch','S')) {

		/*
		 * Only authorized users can switch identity
		 */

		$assume = $_REQUEST['ASSUME_IDENTITY'];

		/*
		 * Obtain list of super-users, since one can't assume the identity
		 * of a super-user
		 */
		$super_ids = get_super_user_ids();

		// System User is not really a super-user, but their identity shouldn't be assumed anyway
		$super_ids[] = $GLOBALS['sys_user'];

		if (is_numeric($assume) && ($assume > 0)) {

			/*
			 * This doesn't validate that the UID is a real staff
			 */

			if ($assume == $UID) {

				/*
				 * Assuming themselves...
				 */

				$_SESSION['EUID'] = $EUID = null;

			} elseif (!in_array($assume,$super_ids)) {

				/*
				 * Prepare to assume identity of a non-super-user
				 */
				$_SESSION['EUID'] = $EUID = $assume;
				$new_request = true;
			}
		}

		if ($EUID) {

			/*
			 * Store true identity in AUID, switch to EUID to UID
			 */
			$AUID  = $UID;
			$UID   = $EUID;
			$def=get_def('staff');
			$staff = array_shift(get_generic(array($def['id_field']=>$UID),'','',$def));
			$NICK  = $staff['name_first'];
			$GLOBALS['AG_USER_OPTION'] = new User_Option();

		}
		
		if ($new_request) {

			/*
			 * Log identity assumptions
			 */
			global $AG_AUTH;
			$AG_AUTH->log_user_login($AUID,"User $AUID has switched to user $UID.");

		}

	}

}

function get_super_user_ids()
{
	$res = sql_fetch_column(agency_query("SELECT staff_id FROM permission_current WHERE permission_type_code = 'SUPER_USER'"),'staff_id');
	return orr($res,array());
}

?>
