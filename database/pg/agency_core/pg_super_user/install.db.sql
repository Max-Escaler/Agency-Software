/*
 *
 * This script should be run by the db super user (most likely "postgres"):
 *
 * [root@localhost]# su postgres
 * bash-3.1$ psql {DATABASE NAME} < install.db.sql
 *
 * If the db user and db do not already exist, this script will 
 * create the db user and the db (replace {DATABASE}.
 * in this script with the desired db name, and {USER} with the 
 * desired user name, and {PASSWORD} with the desired password).
 * 
 * The alternative is to create the user using:
 *    createuser -P {USER}
 * and the database using:
 *    createdb -O {USER} {DATABASE}
 *
 * to run this script (when user and db do not already exist):
 *
 * 
 * [root@localhost agency]# su postgres
 * bash-3.00$ psql template1
 * template1=# \i install.db.sql
 * template1=# \q
 *
 * If the db and user already exist, run "psql {DATABASE}" as 
 * postgres instead of "psql template1".
 */

\i create.user_and_db.sql
\i install2.db.sql

