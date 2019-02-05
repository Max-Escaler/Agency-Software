#!/usr/bin/php
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



function email_those_errors($error,$subject)
{
	global $mail_errors_to;
	mail((is_array($mail_errors_to) ? implode(',',$mail_errors_to) : $mail_errors_to),
		$subject,$error);
	log_error($error);
}

$MODE = 'TEXT';
$off = dirname(__FILE__).'/../';
include $off.'command_line_includes.php';

$database['pg'] = 'agency_test'; //always use test db (requires a reconnect)
db_close($AG_DB_CONNECTION);
$AG_DB_CONNECTION = db_connect();

global $AG_ENGINE_TABLES, $engine;


$args = $_SERVER['argv'];

$tables_to_check = $AG_ENGINE_TABLES;

$tables_to_exclude = array(
				   'tbl_user_option' // there is no need for logging on this table
				   
				   );

//these tables will never have table logging:
foreach ($tables_to_check as $key=>$tab) {
	$tab = $engine[$tab]['table_post'];
	if (!is_table($tab) || in_array($tab,$tables_to_exclude)) { //a view, nothing to do 
		unset($tables_to_check[$key]);
	}
}

if ($args[1]==='ALL') {
	$random_objects = $tables_to_check;
} else {
	$random_objects = array($tables_to_check[array_rand($tables_to_check)]);
}

$error = false;

foreach ($random_objects as $random_object) {
	$def = $engine[$random_object];
	$actions = array('insert','update','delete');
	$table = $def['table_post'];
	
	
	//1) is table logging enabled?
	$log_table = $table . REVISION_HISTORY_TABLE_SUFFIX;
	
	if (!Revision_History::has_history($log_table)) {
		//no history: email error and exit
		$error .= 'WARNING: Table logging is not enabled for '.$table."\n";
		continue; //the other checks are useless until table logging is enabled
	}
	
	sql_begin(); //our test changes will be rolled back
	
	//2) does insert work (not all tables log inserts, so this is tricky) FIXME: figure an easy way to do this

	//3) does update work
	
	$sample_rec = array_filter(array_shift(get_generic(array(),'','1',$table))); //get record (used to order by random(), but was slow for large tables
	$id = $sample_rec[$def['id_field']];
	
	$count_query = 'SELECT trigger_id FROM '.$log_table.' WHERE '.$def['id_field'].'=\''.$id.'\'';
	$res_1 = sql_query($count_query);
	$initial_log_count = sql_num_rows($res_1);
	
	$update_sql = sql_update($table,$sample_rec,$sample_rec);
	$res_update = sql_query($update_sql);
	if (!$res_update) {
		$error .= 'WARNING: couldn\'t test update for '.$table.' using '.$update_sql;
	}
	
	$res_2 = sql_query($count_query);
	$post_log_count = sql_num_rows($res_2);
	
	if ($initial_log_count==$post_log_count) { //logging failed
		$error .= 'WARNING: Table logging *is* enabled for '.$table.' but failed to operate during an update.'."\n";
	}
	
	//4) does delete work
	
	$sample_rec = array_filter(array_shift(get_generic(array(),'','1',$table))); //get record (used to order by random(), but was slow for large tables
	$id = $sample_rec[$def['id_field']];
	
	$count_query = 'SELECT trigger_id FROM '.$log_table.' WHERE '.$def['id_field'].'=\''.$id.'\'';
	$res_1 = sql_query($count_query);
	$initial_log_count = sql_num_rows($res_1);
	
	$delete_sql = sql_delete($table,$sample_rec);
	$res_delete = sql_query($delete_sql);
	if (!$res_delete) {
		$error .= 'WARNING: couldn\'t test delete from '.$table.' using '.$delete_sql;
	}
	
	$res_2 = sql_query($count_query);
	$post_log_count = sql_num_rows($res_2);
	
	if ($initial_log_count==$post_log_count) { //logging failed
		$error .= 'WARNING: Table logging *is* enabled for '.$table.' but failed to operate during a delete.'."\n";
	}
	
	sql_abort();
}

//done with checking, abort transaction

if ($error) {
	email_those_errors($error,'AGENCY: verify_table_logging.php found table logging errors!');
}
	
?>
