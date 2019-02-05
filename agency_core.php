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
 * This file is for core AGENCY functions.
 * 
 * There are probably things in io.php and elsewhere that should be moved here.
 */

function is_enabled( $feature ) {
	switch ($feature) {
		// Features can be disabled here
		// FIXME:  This should be moved to a config file

		case 'some_disabled_feature' :
		case 'bar' :
		case 'residence_own' :
		case 'elevated_concern' :
		case 'entry' :
		case 'entry_visitor' :
		case 'charge_and_payment' :
		case 'family' :
//		case 'jils_import' :
			return false;
		default :
			return true;
	}
}

function get_machine_id() {
	$ip_ids=unserialize(AG_MACHINE_ID_BY_IP);
	$cookie_ids=unserialize(AG_MACHINE_ID_BY_COOKIE);
	$order=unserialize(AG_MACHINE_ID_ORDER);
	if (!is_array($order)) {
		return NULL;
	}
	foreach ($order as $type) {
		if (($type=='cookie') and is_array($cookie_ids)) {
			foreach ($cookie_ids as $k=>$v) {
				if ($_COOKIE['AG_MACHINE_ID']==(AG_MACHINE_ID_COOKIE_PREFIX . $k)) {
					return $v;
				}
			}
		}
		if (($type=='ip') and is_array($ip_ids)) {
			foreach ($ip_ids as $k=>$v) {
				if ($_SERVER['REMOTE_ADDR']==$k) {
					return $v;
				}
			}
		}
	}
	return NULL;			
}

function set_local_parameters() {

/*
 * Modeled on set_kiosk_info()
 *
 * AG_LOCAL_PARAMETERS_BY_MACHINE_ID can specify per-machine parameters
 * (Currently IP-based only, but could be extended to other forms of machine identification)
 *
 * in the format 'id'=>'val' (can be array)
 * (an id can be specified as '*' to match and stop at that point)
 *
 * When matched, AG_LOCAL_PARAMETERS will be defined
 * as a serialized version of the variable.  (To allow for arrays)
 *
 * DEFINE('AG_LOCAL_PARAMETERS_BY_MACHINE_ID',serialize(array(
 * 		'127.0.0.1'=>array('foo'=>'bar'),
 *		'*'=>array('foo'=>'bar2'))));
 *
 */


	if (is_array($params=unserialize(AG_LOCAL_PARAMETERS_BY_MACHINE_ID))) {
		$our_id = get_machine_id();
		foreach ($params as $id =>$param) {
			if ( ($id==$our_id) or ($id=='*') ) {
				define('AG_LOCAL_PARAMETERS',serialize($param));
				return;
			}
		}
	}
	define('AG_LOCAL_PARAMETERS',serialize(NULL));
	return;
}

function set_machine_id_cookie() {
	$list=unserialize(AG_MACHINE_ID_BY_COOKIE);
	if (!is_array($list)) {
		return 'No Cookie Configuration Specified.  (Can set AG_MACHINE_ID_BY_COOKIE in agency_config_local.php)';
	}
	$form = formto()
		. hiddenvar('action','set_machine_id_cookie')
		. 'Set this machine to: '
		. selectto('machine_id_value');
	$this_machine=get_machine_id();
	foreach ($list as $k=>$x) {
		if ($_COOKIE['AG_MACHINE_ID']==(AG_MACHINE_ID_COOKIE_PREFIX.$k)) {
			$current = $x;
		}
		$form .= selectitem($x,$x,($this_machine===$x));
	}
	$unit = selectto('cookie_duration_unit')
		. selectitem('0','This session only')
		. selectitem(time()+60,'Minute(s)')
		. selectitem(time()+3600,'Hour(s)',true) // default
		. selectitem(time()+3600*24,'Day(s)')
		. selectitem(time()+3600*24*7,'Week(s)')
		. selectitem(time()+3600*24*30,'Month(s)')
		. selectitem(time()+3600*24*365,'Year(s)')
		. selectend();
	$form .= selectend()
		. oline()
		. 'Cookie lasts for '
		. formvartext('cookie_duration_quantity',1,'size="3"')
		. $unit
		. oline()
		. button()
		. formend();
	$status = $current
		 ? 'This machine currently set to ' . bold($current)
		: 'No cookie currently set for this machine';
	if ($this_machine and ($this_machine != $current)) {
		$status .= oline() . 'Note: this machine ID currently set to ' . bold($this_machine).', but not by cookie.';
	}
	$note = 'Note: If the machine ID was just set, cookie will not take effect until the next page is loaded. '
			. smaller('('.hlink($_SERVER['PHP_SELF'],'Reload now'). ')');
	return oline($status,2) . $form . $note;
}

function target_date() {
	static $t_date;
	if (!$t_date) {
		$t_date=dateof(call_sql_function('target_date'));
	}
	return $t_date;
}

function target_date_effective_at() {
	static $t_date;
	if (!$t_date) {
		$t_date=dateof(call_sql_function('target_date_effective_at'));
	}
	return $t_date;
}

?>
