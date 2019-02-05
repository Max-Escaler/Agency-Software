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

/* A class for setting,placing and retrieving behind-the-scenes (cross-session) 
 * user options out of the DB. These 'options' are stored in a serialized 
 * associative array.
 *
 * (not yet) The class is initiated to the AGENCY global
 * $AG_USER_OPTION in the function page_open().
 * The user options array is pulled out of the database and unserialized
 * when the class is initiated.
 * To add a new option, simply use (at some point in the script AFTER
 * the class has been initiated):
 *
 * $AG_USER_OPTION->set_option('option_name',$option);
 *
 * The option will be saved when the page_close() function is called at the end of the script.
 *
 * To retrieve an option, use:
 *
 * $option = $AG_USER_OPTION->get_option('option_name');
 *
 * That's it.
 */

class User_Option {

	var $user; //defaults to global $UID
	var $table; //$engine['user_option']['table_post']
	var $options_array; //the actual unserialized options array

	//-----Static Internal Variables----//
	var $user_field = 'staff_id';
	var $array_field = 'options_array';
	var $var_session = 'USER_OPTION_ARRAY';

	function User_Option($uid='')
	{
		global $engine;
		$this->set_user($uid);
		$this->table=orr($engine['user_option']['table_post'],'user_option'); // FIXME: first init was failing without the fallback
		$this->options_array = $this->get_array();
	}

	function set_user($uid='')
	{
		$this->user=orr($uid,$GLOBALS['UID']);
	}

	function get_option($option_name)
	{
		return $this->options_array[$option_name];
	}

	function set_option($option_name,$option)
	{
		$this->options_array[$option_name]=$option;
	}

	function get_array()
	{
		if (isset($_SESSION[$this->var_session]))
		{
			return $_SESSION[$this->var_session];
		}
		//for a new session, we pull out of the database
		$res=agency_query('SELECT '.$this->array_field.' FROM '.$this->table,array($this->user_field=>$this->user));
		if (!$res)
		{
			//do something
		}
		$tmp_array = sql_fetch_assoc($res);
		$_SESSION[$this->var_session]=$tmp=unserialize($tmp_array[$this->array_field]);
		return $tmp;
	}

	function post_array()
	{
		//if changed, we post to DB
		if ($this->options_array!==$_SESSION[$this->var_session]) {

			$_SESSION[$this->var_session]=$this->options_array; //update session
			if (db_read_only_mode()) {
				return true;
			}
			$tmp=serialize(array_filter($this->options_array)); //remove empty array elements
 			$res=sql_query(sql_update($this->table,
 							  array($this->array_field=>$tmp),
 							  array($this->user_field=>$this->user)));
			if ($res) {

				return true;
			}
			return false;
		}
		return true; //no changes, nothing to do.
	}

	function show_hide($option)
	{
		//a wrapper to easily set show/hide options
		//true indicates that the object will be hidden

		$new = $current = $this->get_option('hide_'.$option);
		$request = $_REQUEST['cguseropt_'.$option];

		if (isset($request)) {
			$new = $request=='hide';
		}

		$this->set_option('hide_'.$option,$new);

		return $new;
	}

	function link_show_hide($option)
	{
		if ($this->show_hide($option)) {
			//currently set to hide, so we return show
			$show_hide='show';
		} else {
			$show_hide='hide';
		}
			
		return right(hlink($_SERVER['PHP_SELF'].'?cguseropt_'.$option.'='.$show_hide,smaller($show_hide,2),'',' class="fancyLink"'));
	}

} //end class User_Option
?>
