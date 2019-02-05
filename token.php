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

function get_token_rec( $token, &$msg ) {
	$filter=array('token'=>$token);
	$t_query=agency_query("SELECT * FROM auth_token_current",$filter);
	if (sql_num_rows($t_query) <> 1) {
		$msg .= 'could not find a unique matching token.';
		return false;
	} else {
		return sql_fetch_assoc($t_query);
	}
}

function verify_and_set_password_by_email($password1,$password2,$email,$token,&$msg) {

	if ($password1 != $password2) {
		$msg .= "Sorry, the two passwords do not match";
		return false;
	}
	if (!is_secure_password($password1,false,$msg)) {
		return false;
	}
	if (!$token) {
		$msg .= 'No token passed to verify_and_set_password_by_email';
		return false;
	}
	if (!$t_rec=get_token_rec( $token, $msg )) {
		$msg .= 'No valid token found';
		return false;
	}		
	if (!($staff_id=staff_id_from_email($email))) {
		$msg .= "Could not find a valid user for $email";
		return false;
	}
	if (!password_set(md5($password1),'MD5',$staff_id)) {
		$msg .= 'There was an error setting your password.';
		return false;
	}
	$update_vals=array('was_used'=>sql_true());
	foreach ($t_rec as $k=>$v) {
		if (be_null($v)) {
			unset( $t_rec[$k] );
		}
	}

	if (!agency_query(sql_update('tbl_auth_token',$update_vals,$t_rec))) {
		$msg .= oline('Warning:  Unable to mark token as used');
	}
	$msg .= 'Your password was successfully reset';
	return true;
}

function generate_token() {
	// FIXME:  Better way to do this?
	return md5(microtime() * mt_rand() - mt_rand());
}

function reset_password_link( $token,$email) {
	return 'https://'
		. $_SERVER['SERVER_NAME'] 
		. $GLOBALS['AG_HOME_BY_URL']
		. '/'
		. AG_PASSWORD_RESET_URL
		. "?email=$email&token=$token";
}


function issue_token( $email, &$msg,$username=NULL ) {
	$staff_id=staff_id_from_email($email);
	if (!($staff_id and ( (!$username) or (staff_id_from_username($username)==$staff_id)))) {
		$msg .='Sorry, no account found matching those parameters';
		return false;
	}
	$token =generate_token();
	$token_rec=array('email_address'=>$email,
				'token'=>$token,
				'staff_id'=>$staff_id,
				'added_by'=>$GLOBALS["sys_user"],
				'changed_by'=>$GLOBALS["sys_user"]);
	if (!agency_query(sql_insert('tbl_auth_token',$token_rec))) {
		$msg .= 'Error adding token to database';
		return false;
	}
/*
	$text="You have requested to reset your AGENCY Password\n"
		. "\n"
		. "Use this link to do so: \n"
		. "\n"
		. reset_password_link($token,$email) . "\n";
*/
/*
	$text=div(
  		link_agency_public_home(html_image('http://agency-software.org/agency_logo_medium.png'))
		.html_heading_tag('You have requested to reset your AGENCY Password',1)
		. oline('Use this link to do so:')
		. oline()
		. bigger(reset_password_link($token,$email))
		,'','style="background-color: #DDDDFF;"');
*/
	$text=sql_assign('SELECT template_password_reset FROM config_email ORDER BY config_email_id LIMIT 1');
	$text=str_replace('$reset_password_url',reset_password_link($token,$email),$text);
	return send_email($email,'Reset your AGENCY Password',$text);
}

function request_token_form() {
	return formto()
			. oline("Enter your Username: ")
			. oline(form_field('text','username'),2)
			. oline("Enter your email address: ")
			. oline(form_field('text','email'),2)
			. button("Reset my password")
			. formend();
}

function is_valid_token($token) {
	return true;
}

function new_password_form($email,$token) {
	$valid=false;
	if (!is_valid_token($token)) {
		$message .= 'Sorry, token is invalid';
	} else {
		$filter=array('token'=>$token);
		$t_query=agency_query("SELECT * FROM auth_token_current",$filter);
		if (sql_num_rows($t_query) <> 1) {
			$message .= 'could not find a unique matching token.';
		} else {
			$t_rec=sql_fetch_assoc($t_query);
			if (!sql_true($t_rec['is_valid'])) {
				$message .= "Sorry, that token is no longer valid";
			} elseif (sql_true($t_rec['was_used'])) {
				$message .= "Sorry, that token was already used successfully";
			} elseif ($t_rec['email_address'] != $email) {
				$message .= "Sorry, email address doesn't match for token and request.";
			} else {
				$valid = true;
			}
		}
	}
	if ($valid) {
		$dialog=formto()
			. oline('Password: ' . form_field('password','password1'))
			. oline('Confirm: ' . form_field('password','password2'),2)
			. hiddenvar('token',$token)
			. hiddenvar('email',$email)
			. button('Update','','id="resetPasswordSubmit"')
			. formend();
	}	
	return ($message ? oline(alert_mark($message),2) : '' ) . $dialog;
}

// FIXME: These functions called token are not like the tokens above
// 

function tokenize( $val, $context='*' ) {
	// FIXME: duplicated
	$t_var='AG_TOKENIZED_VALS';
	if (($is_array=is_array($val))) {
		$val=serialize($val);
	}
	if (array_key_exists($val,$_SESSION[$t_var])) {
		$token=array_shift(array_keys($_SESSION[$t_var][$val]));
		if ( (!in_array($context,$_SESSION[$t_var][$val][$token])) ) {
			$_SESSION[$t_var][$val][$token][]=$context;
		}
		return $token;
	}
	foreach ($_SESSION[$t_var] as $v) {
		$tokens[]=array_shift(array_keys($v));
	}
	while (in_array(($token=rand(1,1000000)),$tokens)) {
		//
	}
	$t_array=array();
	$t_array[$token]=array($context);
	if ($is_array) {
		$t_array[$token][]='is_array';
	}
	$_SESSION[$t_var][$val]=$t_array;
	return $token;
}

function detokenize( $token, $context='*' ) {
	// FIXME: duplicated
	$t_var='AG_TOKENIZED_VALS';
	foreach ($_SESSION[$t_var] as $k=>$v) {
		if ($token==array_shift(array_keys($v))) {
			if (in_array($context,$v[$token])) {
				return in_array('is_array',$v[$token]) ? unserialize($k) : $k;
			}
		}
	}
	return NULL;
}

?>
