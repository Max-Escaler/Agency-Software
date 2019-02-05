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


include 'command_line_includes.php';
$js_head=link_style_sheet('agency.css') . link_style_sheet('reset_password.css');
$UID=$sys_user;

$title = agency_logo_small() . "AGENCY Password Reset Page";
$footer .= oline() . oline("Return to the " . link_agency_home('AGENCY Home Page'),3);
$div_id='passwordResetPage';
$div2='id="passwordResetPage"';
$div2=$div_id;

$email=$_REQUEST['email'];
$token=$_REQUEST['token'];
$username=$_REQUEST['username'];
$password1=$_REQUEST['password1'];
$password2=$_REQUEST['password2'];

$out .= html_heading_1($title);

if (!AG_PASSWORD_RESET_ENABLE) {
	$out .=oline('This password reset page has been disabled.');
	out(div($out.$footer,$div2));
	page_close();
	exit;
}

if (!is_secure_transport()) {
	$out .=oline('This page refuses to run insecurely.  SSL MUST be enabled');
	out(div($out.$footer,$div2));
	page_close();
	exit;
}

if ($password1 or $password2) {
	if (!verify_and_set_password_by_email($password1,$password2,$email,$token,$msg) ) {
		$out .= oline("There was a problem with your password:",2)
				. oline($msg,2);
	} else {
		$out .= oline("Your password was successfully reset.",2);
				out(div($out. $footer,$div2));
				page_close();
				exit;
	}
}

if (!$token) {
	if ($email) {
		if ( $username and (issue_token( $email,$msg,$username )) ) {
			$email_canonical = sql_assign('SELECT staff_email FROM staff',staff_filter(staff_id_from_username($username)));
			$out .= "An email has been sent to $email_canonical for resetting your password";
		} else {
			$out .= oline('There was a problem, and we were unable to process your request') .  oline("($msg)",2);
			$out .= request_token_form();
		}
	} else {
		$out .= request_token_form();
	}
} else {
	$out .= new_password_form($email,$token);
}

out($AG_HEAD_TAG . div(oline($out,2) . $footer,$div2));
page_close();
exit;
?>
