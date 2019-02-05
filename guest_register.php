<?php

$quiet=true;
include 'includes.php';
link_style_sheet('guest_register.css','screen',' class="styleSheetScreen"');
link_javascript('caretTo.js');
link_javascript('guest_register.js');

// onscreen keyboard numberpad
link_style_sheet('jquery.keyboard.css','screen',' class="styleSheetScreen"');
link_javascript('jquery.keyboard.min.js');

// Which housing project are we running at?
$local=unserialize(AG_LOCAL_PARAMETERS);
$housing_project_code=$local['guest_register_housing_project_code'];
$housing_project_label=$local['guest_register_housing_project_label']; // Custom label, optional, for co-located projects

if (!$housing_project_code) {
	outline('No housing project code in AG_LOCAL_PARAMETERS.  Stopping');
	outline('These are the local parameters: ' . dump_array($local));
	page_close();
	exit;
}	

if (!is_array($housing_project_code)) {
	$housing_project_code=array($housing_project_code);
}

$filter_project=array('IN:housing_project_code'=>$housing_project_code);
//toggle_query_display();
if (!$housing_project_label) {
	$housing_project_label=sql_lookup_description($housing_project_code[0],'l_housing_project','housing_project_code','description');
}

$title='Welcome to ' . $housing_project_label;

$signin_msg='Select your visiting guest';
$signin_msg='Sign a guest in';
$signout_msg='Sign the guest who is leaving';
$signout_msg='My guest is leaving';
$signout_msg='Sign a guest out';
$logout_msg='Log Out';
$refresh_msg='Cancel / Refresh';
$refresh_link=span(hlink($_SERVER['SCRIPT_NAME'],$refresh_msg,'','class="guestMenuLink"'),'class="guestMenuButton guestLogoutButton guestMenuButtonSmall"');
//$refresh_link=span(hlink($_SERVER['SCRIPT_NAME'],$refresh_msg,'','class="guestMenuLink"'),'class="guestRefreshButton"');
$logout_link=span(hlink($_SERVER['SCRIPT_NAME'],$logout_msg,'','class="guestMenuLink"'),'class="guestMenuButton guestLogoutButton"');
$menu_link=span(hlink($_SERVER['SCRIPT_NAME'].'?menu=menu','Return to Menu','','class="guestMenuLink"'),'class="guestMenuButton"');
$menu_buttons=div($menu_link . $logout_link,'','class="guestMenuButtons"');

$SESSION_ID_VAR = 'GuestTenantID'; // the variable name to use in the session

$menu=orr($_GET['menu'],$_POST['menu']);
//outline("Menu = $menu");


switch ($menu) {
	case 'signin' :
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSigninScreen" ';
		$title2 = $signin_msg;
		$form = guest_guest_select_form( $_SESSION[$SESSION_ID_VAR]);
		$out .= ''
		. div($form,'','class="visitorResults"')
		. oline()
		. $menu_buttons;
		break;
	case 'signout' :
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSignoutScreen" ';
		$title2 = 'Which guest is signing out?';
		$form = guest_exit_select_form( $_SESSION[$SESSION_ID_VAR]);
		$out .= ''
		. div($form,'','class="visitorResults"')
		. oline()
		. $menu_buttons;
		break;
	case 'signin_selected' :
		$guest_id=$_GET['guest_id'];
		if (!(is_valid('integer_db',$guest_id) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSigninSelectedScreen" ';
		$title2 = 'Verify your guest';
		$form = guest_verify($_SESSION[$SESSION_ID_VAR],$guest_id,'visit');
		$out .= $form . oline('',3) . $menu_buttons;
		break;
	case 'signout_selected' :
		$guest_id=$_GET['guest_id'];
		if (!(is_valid('integer_db',$guest_id) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSignoutSelectedScreen" ';
		$title2 = 'Verify your departing guest';
		$form = guest_verify($_SESSION[$SESSION_ID_VAR],$guest_id,'exit');
		$out .= $form . oline('',3) . $menu_buttons;
		break;
	case 'signin_selected_verify' :
		$guest_id=$_GET['guest_id'];
		$guest_filter=array('guest_id'=>$guest_id);
		if (!(is_valid('integer_db',$guest_id) and (count($guest=get_generic($guest_filter,'','','guest')) == 1) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSigninSelectedVerifyScreen" ';
		$name = $guest[0]['name_full'];
		if (count(get_generic($guest_filter,'','','guest_visit_current')) > 0) {
			$form = $name . ' is already registered as a current guest';
		} elseif (post_a_guest_visit($_SESSION[$SESSION_ID_VAR],$guest_id)) { // don't name post_guest_visit, will override post_generic
			//$form = 'Your visitor has been successfully registered.';
			$message[] = $name . ' has been successfully registered as your guest';
		} else {
			$message[] = span('There as a problem registering ' . $name . ' as your guest.','class="error"');
		}
		$menu='menu';
		break;	
	case 'signout_selected_verify' :
		$guest_id=$_GET['guest_id'];
		$guest_filter=array('guest_id'=>$guest_id);
		if (!(is_valid('integer_db',$guest_id) and (count($guest=get_generic($guest_filter,'','','guest')) == 1) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$name = $guest[0]['name_full'];
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSignoutSelectedVerifyScreen" ';
		if (post_a_guest_exit($_SESSION[$SESSION_ID_VAR],$guest_id)) {
			$message[] = "Your guest $name has been successfully exited.";
		} else {
			$message[] = span("There as a problem exiting your guest $name.",'class="error"');
		}
		$menu='menu';
		break;	
}
if ($menu=='menu') {
		if (!$id=guest_find_client_id($filter_project,$message,$_SESSION[$SESSION_ID_VAR])) {
			$menu=NULL;
		} else {
			$_SESSION[$SESSION_ID_VAR]=$id;
			$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestMenuScreen" ';
			$out .= ''
			. span(hlink($link_base . '?menu=signin', $signin_msg,'','class="tenantSigninButton guestMenuLink"'),'class="guestMenuButton"')
			. span(hlink($link_base . '?menu=signout', $signout_msg,'','class="tenantSignoutButton guestMenuLink"'),'class="guestMenuButton"')
			. oline('',2)
			. div($logout_link,'','class="guestMenuButtons"');
		}
}

if (!$menu) {
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestLoginScreen" ';
		$_SESSION[$SESSION_ID_VAR]=NULL;
		$title2 = 'Please log in';
		$form1=guest_select_form($filter_project);
		$out .= $form1;
		$refresh=div($refresh_link,'','class="guestMenuButtons"')
;
}

if ($q=$_SESSION[$SESSION_ID_VAR]) {
	$client=get_generic(client_filter($q),'','','client');
	if (count($client)==1) {
		$title .= ', ' . ucwords(strtolower($client[0]['name_first'])) . ' ' . ucwords(strtolower($client[0]['name_last']));
	}
}
//html_header($title);

//agency_top_header();
// OR
out(html_start());
//out(html_heading_tag(organization_logo_small() .span($title,'valign="bottom"'). agency_logo_small(),1));
out(html_heading_tag($title,1));

out($title2 ? html_heading_tag($title2,2) : '');
		out($message ? div(implode(oline(),$message),'','class="guestResponseMessage"') : '');
out(div($out,'','class="guestHeaderBlock"'));
out($refresh);

$footer=oline('Last refreshed at ' . datetimeof('now','US'). '.  ' . dead_link('Administration...')) . 'AGENCY Software running at ' . org_name();
//$footer=oline('Last refreshed at ' . datetimeof('now','US'). '.  ' . dead_link('Administration...')) . organization_logo_small().'AGENCY Software running at ' . org_name().agency_logo_small();
outline(div($footer,'','class="guestAGENCYFooter"'));
page_close(true);
exit;

?>
