<?php

function guest_name( $id ) {
	if (!is_valid('integer_db',$id)) {
		return false;
	}
	return sql_assign( 'SELECT name_full FROM guest WHERE guest_id='.$id);
}

function guest_link( $id ) {
	if (!is_valid($id,'integer_db')) {
		return null;
	}
	return elink( 'guest', $id, guest_name( $id ) );
}

function guest_find_client_id($filter1,&$msg,$current_id) {
//	$name_last=$_POST['name_last'];
//	$name_first=$_POST['name_first'];
	$unit=$_POST['housing_unit_code'];
	$prefix=sql_fetch_column(agency_query('SELECT unit_code_prefix FROM l_housing_project',$filter1),'unit_code_prefix');
	$prefix_regex=implode('|',$prefix);
	$dob=$_POST['dob'];
	$yob=$_POST['yob'];
	$msg1=array();

		
/*
	if (($name_last or $name_first or $dob)) {
		if (!preg_match('/[a-z _\']*$/i',$name_last)) {
			$msg1[] = 'Invalid last name';
		}
		if (!preg_match('/[a-z _\']*$/i',$name_first)) {
			$msg1[] = 'Invalid first name';
		}
*/
	if ($unit or ($dob or $yob)) {
		if (!preg_match('/^('.$prefix_regex.')?[-0-9_]*$/i',$unit,$match)) {
			$msg1[] = span('Invalid format for unit number: $unit','class="error"');
		} else if (!$match[1]) {
			// e.g., if tenant types "204", convert to "A204"
			$find_unit_filter=array('~:housing_unit_code'=>"($prefix_regex)$unit");
			$unit=sql_assign('SELECT housing_unit_code FROM residence_own_current',$find_unit_filter);
			//$unit = $prefix . $unit;
		}
/*
		if (!$dob=dateof($dob,'SQL')) {
			$msg1[] = 'Invalid Date of Birth';
		}
*/
		if ( !$yob) {
			$msg1[] = span('Missing Year of Birth','class="error"');
		} elseif ( ($yob > 0) and ($yob < 100)) {
			$yob = 1900 + $yob;
		} elseif (! (($yob > 1900) and ($yob < 2000))) {
			$msg1[] = span('Invalid Year of Birth','class="error"');
		}
		if (count($msg1) >0 ) {
			$msg[] = implode(oline(),$msg1);
			return false;
		}
		$filter['ILIKE:housing_unit_code']=$unit;
		$filter=array_merge($filter,$filter1);
		if ($dob) {
			$filter['dob']=$dob;
		}
		if ($yob) {
			$filter['>=:dob']=$yob.'-01-01';
			$filter['<=:dob']=$yob.'-12-31';
		}
		$c_def=get_def('client');
		$c_def['sel_sql']='SELECT * FROM residence_own_current LEFT JOIN client USING (client_id)'; 
		$clients=get_generic($filter,NULL,NULL,$c_def);
		if (count($clients)==0) {
			$msg[] = span('Matching tenant not found','class="error"');
			return false;
		}
		if (count($clients)>1) {
			$msg[] = span('Found multiple matching records--don\'t know what to do!  Please tell a staff person about this.','class="error"');
			return false;
		}
	} elseif ($current_id) {
		// continue current login	
		$clients=get_generic(client_filter($current_id),'','','client');
	} else {
		$msg[] = span('No information submitted.  Try again','class="error"');
		return false;
	}

//	$msg[] = 'Welcome ' . ucwords(strtolower($clients[0]['name_first'])) . ' ' . ucwords(strtolower($clients[0]['name_last']));

	return $clients[0]['client_id'];
}

function guest_select_form($unit_filter=array()) {

/*
// Uncomment this for a name-based form 
	$out = ''
	. formto()
	. html_heading_tag('Name',3)
	. 'First: '
	. formvartext('name_first')
	. oline()
	. 'Last: '
	. formvartext('name_last')
*/
	$def=get_def('housing_unit');
	$def['sel_sql']='SELECT substring(housing_unit_code FROM \'^[a-zA-Z]*([-0-9_]+)$\') AS housing_unit_code FROM residence_own_current';
	$units=get_generic($unit_filter,NULL,NULL,$def);
	$units=array_fetch_column($units,'housing_unit_code');
	$units=div(json_encode($units),'housingUnitCodes','class="serverData"');
	$out = ''
	. formto()
	. $units 
	. div(html_heading_tag("Enter your unit "
	. formvartext('housing_unit_code',NULL,'size=4 autocomplete="off"')

,2)
/*
	. selectto('housing_unit_code')
	. selectitem('','...')
	. do_pick_sql('SELECT housing_unit_code AS value, substring(housing_unit_code FROM \'^[a-zA-Z]*([0-9]+)$\') AS label FROM ' . $def['table'] . ' WHERE ' . read_filter($unit_filter) . ' ORDER BY 1') 
	. selectend()
*/
	,'','class="guestLoginUnit"')
//	. div(html_heading_tag('Date of birth: ',2)
	. div(html_heading_tag('What year were you born? '
		. formpassword('yob',NULL,'size=4'),2)
	,'','class="guestLoginSecret"')
//	. formdate('dob'),'','class="guestLoginDob"')
	. oline('',2)
	. div(button('Go','','','','','class="guestTenantSelectSubmit"'),'','class="guestLoginGo guestMenuButton"')
	. hiddenvar('menu','menu')
	. formend()
	; 
	return $out;
}


function guest_guest_select_form( $id ) {
	$list=get_generic(client_filter($id),'guest_name',NULL,'guest_visit_authorized');
	if (count($list)==0) {
		$response = div('Sorry, you have no eligible guests.','','class="guestResponseMessage"');
	} else {
		$base_url='';
		foreach($list as $item) {
			$items[] = span(hlink($base_url.'?menu=signin_selected&guest_id='.$item['guest_id'],$item['guest_name'],'','class="guestMenuLink"'),'class="guestButton"');
		}
		$response = implode('',$items);
	}
	return $response;
}

function guest_exit_select_form( $id ) {
	$list=get_generic(client_filter($id),'guest_name',NULL,'guest_visit_current');

	if (count($list)==0) {
		$response = div('You have no current guests to sign out.','','class="guestResponseMessage"');
	} else {
		$base_url='';
		foreach($list as $item) {
			$items[] = span(hlink($base_url.'?menu=signout_selected&guest_id='.$item['guest_id'],$item['guest_name'],'','class="guestMenuLink"'),'class="guestButton"');
		}
		//$response = implode(oline(),$items);
		$response = implode('',$items);
	}
	return $response;
}

function guest_verify($client_id,$guest_id,$type='visit') {
	//$type can be 'exit' or 'visit'
	$action = ($type=='visit') ? 'visiting you' : 'ending their visit';
	$action2 = ($type=='visit') ? 'signin' : 'signout';

	$g_filt = array('guest_id'=>$guest_id);
	$guest=get_generic($g_filt,NULL,NULL,'guest');
	if (count($guest) <> 1) {
		return false;
	}
	if (be_null(($ied=$guest[0]['identification_expiration_date'])) or ($ied < dateof('now','SQL')) ) {
		// Comment out this line if you don't care about ID expiration dates
		$id_warning=div('Note: No Current Identification on file for this guest','','class="idWarning"');
	}

	$client=sql_fetch_assoc(client_get($client_id));
	$client_name=$client['name_first'].' ' . $client['name_last'];
	$verify_message=oline("You are " . bold(underline($client_name)),2)
		. "and " . bold(underline($guest[0]['name_full'])) . " is $action.";
	$form = div(guest_photo($guest_id),'','class="guestPhotoContainer"') 
		. div($verify_message,'','class="guestVisitSummary"')
			. oline('',2)
			. oline(bigger(bold(italic('Is this information correct?')),2),2)
			. span(hlink('?menu=' . $action2 . '_selected_verify&guest_id='.$guest_id,'Yes, this is correct','','class="guestMenuLink"'),'class="guestMenuButton guestYesChoice"')
			. span(hlink('?menu='.$action2,'No, I want to try again','','class="guestMenuLink"'),'class="guestMenuButton guestNoChoice"')
			;
	return $id_warning . $form;
}

function guest_photo( $guest_id,$sizex=NULL,$sizey=NULL ) {
	if (!$guest_id) { return false; }
	$g_filt = array('guest_id'=>$guest_id);
	$guest=get_generic($g_filt,NULL,NULL,'guest');
	if (count($guest) <> 1) {
		return false;
	}
	if ($guest[0]['guest_photo']) {
		$tmp=link_attachment($guest[0]['guest_photo'],'dummy');
		if (preg_match('/href=\"(.*)\" /',$tmp,$matches)) {
			$photo_url=$matches[1];
			$photo = httpimage($photo_url,$sizex,$size,0,'class="guestPhoto"');
		}
	}
	return $photo;
}
	

function post_a_guest_visit($client_id,$guest_id,$msg) {
	$def=get_def('guest_visit');
	$d1 = $d2 = array();
	$rec=blank_generic($def,$d1,$d2);
	$rec['guest_id']=$guest_id;
	$rec['client_id']=$client_id;
	$rec['entered_at']=datetimeof('now','SQL');
	$rec['housing_unit_code']=unit_no($client_id);
	return post_generic($rec,$def,$msg);
}	

function post_a_guest_exit($client_id,$guest_id,$msg) {
	$def=get_def('guest_visit');
	$d1 = $d2 = array();
	$filt=array('client_id'=>$client_id,'guest_id'=>$guest_id,'NULL:exited_at'=>'dummy');
	$recs=get_generic($filt,NULL,NULL,'guest_visit_current');
	if (count($recs)==0) {
		$msg .= span('Current visit not found','class="error"');
		return false;
	}
	if (count($recs)>1) {
		$msg .= span("Multiple current visits found for $client_id,$guest_id.  Please contact system administrator.",'class="error"');
		return false;
	}
	$update_rec['exited_at']=datetimeof('now','SQL');
	$update_filter=array($def['id_field']=>$recs[0][$def['id_field']]);
	return post_generic($update_rec,$def,$msg,$update_filter);
}

?>
