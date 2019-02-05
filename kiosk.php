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
 * This file is for kiosk functions.
 */

function set_kiosk_info() {

/*
 * For kiosk mode:
 *
 * AG_KIOSK_MODE must be defined as true
 *
 * AG_KIOSK_USERS_BY_MACHINE_ID can specify per-machine users
 * (Currently IP-based only, but could be extended to other forms of machine identification)
 *
 * in the format 'id'=>'user' ('127.0.0.1','localhost_kiosk')
 * (an id can be specified as '*' to match and stop at that point)
 *
 * If no match found, will fallback to the legacy AG_KIOSK_USER constant
 *
 * DEFINE('AG_KIOSK_USERS_BY_MACHINE_ID',array(
 * 		'127.0.0.1'=>'localhost_kiosk',
 *		'192.168.0.1'=>'other_kiosk',
 *		'*'=>'default_kiosk'));
 *
 * (tech note for those paying attention:
 *
 * internally, AG_KIOSK_MODE and AG_KIOSK_USER have been
 * replaced by AG_KIOSK_MODE_SET and AG_KIOSK_USER_SET to
 * maintain config file compatibility)
 *
 */


	if (!(defined('AG_KIOSK_MODE') and AG_KIOSK_MODE)) {
		define('AG_KIOSK_MODE_SET',false);
		return;
	}
	if (is_array($users=unserialize(AG_KIOSK_USER_BY_MACHINE_ID))) {
		$our_id = get_machine_id();
		foreach ($users as $id =>$user) {
			if ( ($id==$our_id) or ($id=='*') ) {
				define('AG_KIOSK_MODE_SET',true);
				define('AG_KIOSK_USER_SET',$user);
				return;
			}
		}
	}
	if (defined('AG_KIOSK_USER') and AG_KIOSK_USER) {
		define('AG_KIOSK_USER_SET',AG_KIOSK_USER);
		define('AG_KIOSK_MODE_SET',true);
		return;
	}
	// No user info found, kiosk mode is not enabled.
	define('AG_KIOSK_MODE_SET',false);
	return;
}

?>
