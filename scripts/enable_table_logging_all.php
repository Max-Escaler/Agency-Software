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



  /*
   * enable_table_logging_all.php
   *
   * This script searches for all tables are of the form l_* or tbl_* that
   * don't have a trigger of the form {name}_log_chg.
   *
   * For any tables found, table logging is enabled. This entails the creation
   * of a table named {name}_log, and then an insert/update/delete or update/delete
   * trigger. The default is insert/update/delete. To force update/delete, the table 
   * should be named in the $tmp_update_delete_only array.
   *
   * If any tables should always be excluded, they should be added to
   * the $tmp_never_enable array.
   */

$MODE='TEXT';
$off = dirname(__FILE__).'/../';
$AG_FULL_INI = false;
include_once $off.'command_line_includes.php';

//never enable table logging for these tables (security or performance reasons):
$tmp_never_enable = array('tbl_user_option','tbl_staff_password','tbl_engine_config');

//log on UPDATE/DELETE ONLY tables, otherwise, default is INSERT/UPDATE/DELETE
$tmp_update_delete_only = array('tbl_bed','tbl_entry','tbl_user_login','tbl_alert');


$never_enable = array();
foreach ($tmp_never_enable as $t_ob) {
	$never_enable[] .= enquote1($t_ob);
}
$never_enable_string = implode(', ',$never_enable);


//check for table_log() function
if (sql_num_rows(agency_query('SELECT true FROM pg_catalog.pg_proc WHERE proname ~ \'^table_log$\'')) < 1) {
	outline('Couldn\'t find required database function table_log(). Verify that you have installed the table logging functionality as per install.db.sql');
	exit;
}

if (isset($_REQUEST['verbose']) || (isset($_SERVER['argv']) && in_array('verbose',$_SERVER['argv'])) ) {
	$verbose = true;
}

$query = "SELECT c.relname FROM pg_catalog.pg_class c 
     LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner
     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind IN ('r','')
      AND n.nspname NOT IN ('pg_catalog', 'pg_toast')
      AND pg_catalog.pg_table_is_visible(c.oid)
      AND (c.relname ~ '^tbl_' OR c.relname ~ '^l_') AND c.relname NOT IN (".$never_enable_string.")

/* Exclude tables where it is already enabled */
	AND (c.relname NOT IN (
            SELECT cc.relname FROM pg_trigger t
                  LEFT JOIN pg_class cc ON t.tgrelid = cc.oid
                  WHERE t.tgname ~ '_log_chg$'::text AND (NOT t.tgisconstraint OR NOT (EXISTS ( SELECT 1
                        FROM pg_depend d
                             JOIN pg_constraint c ON d.refclassid = c.tableoid AND d.refobjid = c.oid
                              WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i'::char AND c.contype = 'f'::char)))
	)) 

      AND (c.relname = 'tbl_log' OR c.relname !~ '_log$') /* Must include tbl_log, but no other tables ending in _log */

ORDER BY 1";



$res = agency_query($query);

if (sql_num_rows($res) < 1) {
	outline('Table logging is already enabled for all existing lookups (l_*) and data tables (tbl_*)');
	page_close();
	exit;
}

$enable_query="INSERT INTO tbl_db_revision_history
    (db_revision_code,
    db_revision_description,
    agency_flavor_code,
    git_sha,
    git_tag,
    applied_at,
    comment,
    added_by,
    changed_by)

     VALUES ('ENABLING_TABLE_LOG_TRIGGERS'||current_timestamp, /*UNIQUE_DB_MOD_NAME */
            'Enabling Table Log triggers', /* DESCRIPTION */
            '" . strtoupper(AG_MAIN_OBJECT_DB) ."',
            '', /* git SHA ID, if applicable */
            '', /* git tag, if applicable */
            current_timestamp, /* Applied at */
            '', /* comment */
            sys_user(),
            sys_user()
          );";
while ($a = sql_fetch_assoc($res)) {
	$table = $a['relname'];
	$verbose && outline('Enabling table logging for '.bold($table));
	$on_actions = in_array($table,$tmp_update_delete_only) ? 'UPDATE OR DELETE' : '';
	$enable_query .= "SELECT enable_table_logging('$table','$on_actions'); \n ";
}
agency_query('BEGIN; '.$enable_query.' COMMIT;');
?>
