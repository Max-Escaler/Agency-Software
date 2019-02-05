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
   * enable_changed_at_trigger.php
   *
   * This script searches for all tables that have a changed_at column
   * but don't have a trigger of the form tbl_{name}_changed_at_update.
   *
   * For any tables found, this trigger is enabled. 
   *
   * If any tables should always be excluded, they should be added to
   * the $tmp_never_enable array.
   */


$MODE='TEXT';
$off = dirname(__FILE__).'/../';
$AG_FULL_INI = false;
include_once $off.'command_line_includes.php';

$tmp_never_enable = array('tbl_user_option');

$never_enable = array();
foreach ($tmp_never_enable as $t_ob) {
	$never_enable[] .= enquote1($t_ob);
}
$never_enable_string = implode(', ',$never_enable);

$query = "
SELECT DISTINCT c.relname FROM pg_catalog.pg_class c 
     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace 
     LEFT JOIN pg_catalog.pg_attribute a ON (c.oid=a.attrelid) 
WHERE c.relkind IN ('r','') 
     AND n.nspname NOT IN ('pg_catalog','pg_toast') 
     AND pg_catalog.pg_table_is_visible(c.oid) 
     AND a.attname ~ '^changed_at$' and (c.relname !~ '_log$' or c.relname='tbl_log') 
     AND c.relname NOT IN (".$never_enable_string.")

     /* Exclude tables where it is already enabled */
	AND (c.relname NOT IN (
            SELECT cc.relname FROM pg_trigger t
                  LEFT JOIN pg_class cc ON t.tgrelid = cc.oid
                  WHERE t.tgname ~ '_changed_at_update$'::text AND (NOT t.tgisconstraint OR NOT (EXISTS ( SELECT 1
                        FROM pg_depend d
                             JOIN pg_constraint c ON d.refclassid = c.tableoid AND d.refobjid = c.oid
                              WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i'::char AND c.contype = 'f'::char)))
	)) 
;";

if (isset($_REQUEST['verbose']) || (isset($_SERVER['argv']) && in_array('verbose',$_SERVER['argv'])) ) {
	$verbose = true;
}

//check for auto_changed_at_update() function
if (sql_num_rows(agency_query('SELECT true FROM pg_catalog.pg_proc WHERE proname ~ \'^auto_changed_at_update$\'')) < 1) {
	outline('Couldn\'t find required database function auto_changed_at_update(). Verify that you have installed the table logging functionality as per install.sql');
	exit;
}

$res = agency_query($query);

if (sql_num_rows($res) < 1) {
	outline('changed_at trigger is already enabled for all possible tables');
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

	 VALUES ('ENABLING_CHANGED_AT_TRIGGERS'||current_timestamp, /*UNIQUE_DB_MOD_NAME */
			'Enabling changed_at triggers', /* DESCRIPTION */
			'" . strtoupper(AG_MAIN_OBJECT). "', 
			'', /* git SHA ID, if applicable */
			'', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );";
while ($a = sql_fetch_assoc($res)) {
	$table = $a['relname'];
	$verbose && outline('Enabling changed_at for '.bold($table));
	$enable_query .= "
        --$table
        CREATE TRIGGER {$table}_changed_at_update
        BEFORE UPDATE ON {$table}
        FOR EACH ROW EXECUTE PROCEDURE auto_changed_at_update();

          ";
}
agency_query('BEGIN; '.$enable_query.' COMMIT;');
?>
