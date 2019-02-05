/* This script has all the install script stuff needed
 * except creating the user and db, so the user and db
 * create can be skipped during installation, or done
 * via alternate methods like an installation (shell)
 * script.
 */

\i create.pgsql_language.sql
\i create.pgmail_function.sql
\i create.tcl_language.sql
\i create.fuzzystrmatch_functions.sql
\i create.table_log_functions.sql
CREATE EXTENSION btree_gist;

