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
   * AGENCY-specific functions
   */

function agency_menu_admin()
{
	/*
	 * AGENCY Administration
	 */

	$action=$_REQUEST['action'];
	switch ($action) {
	case 'admin_view_object_def' :
		$object = $_REQUEST['object_name'];
		if ($def = get_def($object)) {
			set_time_limit(240);
			if (!$_REQUEST['inc_field_def']) {
				unset($def['fields']);
			}
			$menu['Engine Definition Array for '.$def['singular']] = oline(link_admin('Back to AGENCY Admin page'))
				. oline('Viewing engine config array for object: '.$object)
				. html_pre(print_r($def,true));
			return array($menu);
		} else {
			$error .= alert_mark('No Engine Definition Array available for '.$object);
		}
		break;
	case 'enable_table_logging_all' :
	case 'enable_changed_at_trigger' :
		if (has_perm('super_user')) {
			$verbose = true;
			include 'scripts/'.$_REQUEST['action'].'.php';
			page_close();
			exit;
		}
		break;
	case 'set_machine_id_cookie' :
		if (!has_perm('super_user')) {
			break;
		}
		$unit=$_POST['cookie_duration_unit'];
		$quant=$_POST['cookie_duration_quantity'];
		$machine=$_POST['machine_id_value'];
		$domain=$_SERVER['HTTP_HOST'];
		$path=dirname($_SERVER['REQUEST_URI']);
		$machines=unserialize(AG_MACHINE_ID_BY_COOKIE);
		if (!(is_numeric($unit) and is_numeric($quant) and is_array($machines) and in_array($machine,$machines) and is_secure_transport())) {
			break;
		}
		setcookie('AG_MACHINE_ID',AG_MACHINE_ID_COOKIE_PREFIX.array_search($machine,$machines),$unit*$quant,$path,$domain,true,true);	
		break;
	case 'staff_assign_transfer' :
	case 'staff_assign_close' :
		$sa_def=get_def('staff_assign');
		if (!(has_perm('admin') and has_perm($sa_def['perm_edit']) and has_perm($sa_def['perm_add']))) {
			$error .= oline('Insufficient permissions to transfer or close caseload');
			break;
		}
		$tr_date=dateof($_REQUEST['staff_assign_transfer_date'],'SQL');
		$tr_sid=$_REQUEST['staff_assign_transfer_staff_id'];
		if (!($tr_date and is_valid($tr_sid,'integer_db'))) {
			$error .= oline('invalid date or staff ID for transferring or closing caseload');
			break;
		}
		if ($tr_sid==$GLOBALS['SYS_USER']) {
			$error .= oline('Cannot transfer caseload from system user.  They should not have a caseload anyway!');
			break;
		}
		$tr_sid_to=$_REQUEST['staff_assign_transfer_staff_id_to'];
		if ($action=='staff_assign_transfer') {
			if (!(is_valid($tr_sid_to,'integer_db') and ($tr_sid_to!=$GLOBALS['sys_user']))) {
				$error .= oline('Invalid Staff ID to transfer caseload to');
				break;
			}
			if ($tr_sid_to==$tr_sid) {
				$error .= oline('Cannot transfer caseload to the same person!');
				break;
			}
			call_sql_function('staff_assign_transfer',$tr_sid,$tr_sid_to,$GLOBALS['UID'],enquote1($tr_date));
			break;
		} elseif ($action=='staff_assign_close') {
			if ($tr_sid_to and ($tr_sid_to!=-1)) {
				$error .= oline('Caseload close requested, but transfer to staff specified.  Maybe you meant to transfer a caseload?');
				break;
			}
			call_sql_function('staff_assign_close',$tr_sid,$GLOBALS['UID'],enquote1($tr_date));
			break;
		}
		break;
	}

	$button = button('Go','','','','',' class="agencyForm"');

	//Update Engine Config options
	$menu['Update Engine Array Stored in DB'] = para('This should be run after any database or configuration update.')
		. update_engine_control();

	//DB-mod information
	$menu['Database Modifications'] = 
	para('These modifications have been applied to the database.  Database modification files are located in the \'database/pg/db_mods\' directory.')
	. call_engine(array('object'=>'db_revision_history','action'=>'list','format'=>''),'',true,true,$perm,$tot_recs);

	//Browse AGENCY tables via Engine
	$menu['Browse AGENCY Tables via Engine'] = engine_browser_control();
	//Browse Engine Config Options Array
	$menu['Browse Engine Config Array'] = para('These pages may take some time to load')
		. formto(AG_AGENCY_ADMIN_URL)
		. oline('Enter a configured object name:')
		. form_field('text','object_name','','class="agencyForm"') . $button
		. oline() . formcheck('inc_field_def').' Include Field Definitions (these can be '.italic('long').')'
		. hiddenvar('action','admin_view_object_def')
		. formend();
	
	
	// Staff Accounts 
	$sdef = get_def('staff');

	$menu['Staff Account Administration'] = formto('display.php')
		. hiddenvar('control[action]','view')
		. hiddenvar('control[object]','staff')
		. hiddenvar('control[step]','')
		. para(link_engine(array('object'=>'staff','action'=>'add'),'Add a new staff account'))
		. pick_staff_to('control[id]')
		. button_if('View/edit this account', '', 'view_staff','','',has_perm($sdef['perm_view']))
		. formend()
		. show_all_permissions();

	// Transfer or close assignments
	if (has_perm($sdef['perm_edit']) and has_perm('admin')) {
		$menu['Transfer/Close Caseload']= 
		formto()
		. 'Transfer or close? '
		. selectto('action')
		. selectitem('','Choose...')
		. selectitem('staff_assign_transfer','Transfer a caseload')
		. selectitem('staff_assign_close','Close a caseload')
		. selectend()
		. oline()
		. oline('Transfer/Close for ' . pick_staff_to('staff_assign_transfer_staff_id'))
		. oline('Transfer caseload to ' . pick_staff_to('staff_assign_transfer_staff_id_to'))
		. oline('Date original assignment ends (new assignments will start next day) ' . formdate('staff_assign_transfer_date'))
		. button()
		. formend()
		;
	}		
	
	// Client Unduplication
	
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
	$menu[$mo_noun.' Unduplication'] = html_list(
											html_list_item(link_unduplication('Unduplicate '.$mo_noun))
											. html_list_item('This link will unduplicate '.$mo_noun.'s who have already been processed and confirmed as duplicates, in case they were in tables which have only recently been added, or were not unduplicated from the DB for another reason: '.link_unduplication('Check for/unduplicate confirmed duplicate '.$mo_noun.'s from tables','undup_overlooked')));

	// Set cookies for machine ID
	$menu['Set Cookie ID for this Machine']=set_machine_id_cookie();
	
	// Database Maintenance
	$menu['Database Maintenance'] = html_list(
								html_list_item(hlink_if($_SERVER['PHP_SELF'].'?action=enable_table_logging_all','Enable Table Logging for all tbl_* and l_* tables',has_perm('super_user')))
								.html_list_item(hlink_if($_SERVER['PHP_SELF'].'?action=enable_changed_at_trigger','Enable changed_at trigger for all tables containing a changed_at field',has_perm('super_user'))));
	
	return array($menu,$error);
}

function show_all_permissions($title='Show All Staff Permission Records')
{
      $control=array('object'=>'permission',
		     'action'=>'list',
		     'list'=>array(
				   'fields'=>array('staff_id',
							 'permission_type_code',
							 'permission_date',
							 'permission_date_end',
							 'permission_read',
							 'permission_write',
							 'permission_super'),
				   'max'=>200)
		     );
      $control['page']=$_SERVER['PHP_SELF'];
      $control['anchor']='permission';

	//      return call_engine($CONTROL,'admin_perm_control');
	$js_hide = true;
	return engine_java_wrapper($control,'admin_perm_control',$js_hide,$title);
}

function agency_menu_mip()
{
	require_once('mip.php');
	$menu['MIP Export'] = html_list(
						  html_list_item(mip_link_export())
						  . oline()
						  . mip_import_files_list()
						  );
	return array($menu);
}

?>
