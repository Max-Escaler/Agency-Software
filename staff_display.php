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

// Display a staff member
// id passed as variable

$quiet="Y";
$relog_page="index.php";
include "includes.php";

/*
 * Bug 23526. 
 */

$id = orr($_REQUEST['id'],$_REQUEST['control']['id']);
$email = $_REQUEST['email'];

if ($email &&  $id) {
	outline('Cannot pass both id and email address');
	page_close();
	exit;
 }

if (preg_match('/(.*)@desc.org$/i', $email, $matches)) {
	$email = $matches[1];
 }

$id = orr($id, staff_id_from_username($email));

/*
 * Use last view staff ID if neither staff ID nor Email were passed via URL. 
 * Bug 23526.
 * Or just default to your own page
 */

if (!is_valid($id,'integer_db')) {
	$id = orr($_SESSION['STAFF_DISPLAY_PAGE_ID'],$UID);
 }

if (!engine_perm(array('action'=>'view','object'=>'staff','id'=>$id))) {
	agency_top_header();
	outline(alert_mark('You do not have permissions to view this page'));
	page_close();
	exit;
}

$def=get_def('staff');
$filter=array($def['id_field']=>$id);

if (count(($staff_rec=get_generic($filter,'','',$def))) <> 1) {
	agency_top_header();
	outline(alert_mark('No Staff found'));
	page_close();
	exit; 
 }

$staff_rec=array_shift($staff_rec);


$_SESSION['STAFF_DISPLAY_PAGE_ID']=$id;

$action = orr($_REQUEST['action'],$_REQUEST['control']['action']);

/*
 * password section
 */
if ($action == 'set_password') {

	$pwd_def = get_def('staff_password');

	$pass_old  = $_REQUEST["password_old"];
	$pass_new  = $_REQUEST["password_new"];
	$pass_new1 = $_REQUEST["password_new1"];
	
	if ($AG_AUTH_DEFINITION['USE_MD5']) {

		$pass_old  = md5($pass_old);
		$pass_new  = md5($pass_new);
		$pass_new1 = md5($pass_new1);

		$hash_method = 'MD5';

	}

	if (can_change_password($id) || password_check($pass_old,$hash_method,$id)) {

		if ($pass_new != $pass_new1) {

			$msg .= alert_mark('Sorry, new passwords do not match');
			$action = 'change_password';

		} elseif (!is_secure_password($_REQUEST['password_new'],$staff_rec['username'],$msg) ) {

			$msg = alert_mark($msg);
			$action = 'change_password';

		} elseif (password_check($pass_new,$hash_method,$id,true)) {
			$msg = alert_mark('You have already used that password.  Pick a different one.');
			$action = 'change_password';

		} else {

			$msg .= alert_mark( 
						 password_set($pass_new,$hash_method,$id)
						 ?	oline('Password successfully updated.')
						 :	'Updating password failed.');
		}

	} else {

		$msg .= alert_mark('Current password incorrect.');

	}

}

if ($action == 'change_password') {

	$commands = array(cell(formto()

				     . (can_change_password($id)
					  ? '' // user has permissions, don't need old password
					  : oline(indent('Enter old password: ',7) . formpassword('password_old')))

				     . oline(indent('Enter new password: ',5) . formpassword('password_new'))
				     . 'Re-enter new password: ' . formpassword('password_new1')
				     . hiddenvar('action','set_password')
				     . hiddenvar('id',$id)
				     . button('Change Password')
				     . formend()));

}
/*
 * End password section
 */

if ($action=="print_staff_id")
{
    include "openoffice.php";
    include "zipclass.php";
    $card=generate_staff_card($id);
	serve_office_doc($card,'staff_idcard.pdf'); //exits
}

if ($action=='reg_kc' && has_perm('clinical_data_entry','RW')) {

	//set old mh id, trigger on staff does the rest
	
	// 1) verify mh id is blank
	$res = get_generic(array('staff_id'=>$id,'NULL:old_mh_id' => ''),'','','staff');
	if (count($res) == 1) {
		sql_begin();
		$old_mh_id = sql_get_sequence('seq_staff_king_county_linkage_id');

		$res = agency_query(sql_update('tbl_staff',array('old_mh_id' => $old_mh_id,
									     'changed_by' => $UID,
									     'FIELD:changed_at' => 'CURRENT_TIMESTAMP'),
						     array('staff_id'=>$id,
							     'NULL:old_mh_id' => '')));

		if ($res) {
			$msg .= alert_mark('Setting King County Linkage ID to '.$old_mh_id.'. Staff record will be sent to the county tonight.');
			sql_end();
		} else {
			log_error('Couldn\'t set old_mh_id for '.$id);
			sql_abort();
		}
	} else {

		$msg .= alert_mark('old_mh_id is already set for staff '.$id);

	}

}
$commands=orr($commands,array(bottomcell(link_engine(array("object"=>"staff","id"=>$id,"format"=>"data"),"View/edit Data Record")),
					bottomcell(object_child_command_box_generic('staff',$id),'class="pick"')));

$out = oline($msg) . view_staff($staff_rec,$def,'view');

$name=strip_tags(staff_name($id));
$title="$name ($id)";
agency_top_header($commands);
out($out);
page_close();

?>
